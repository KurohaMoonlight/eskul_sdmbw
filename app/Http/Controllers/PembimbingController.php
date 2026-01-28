<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use App\Models\Eskul;
use App\Models\Jadwal;
use App\Models\AnggotaEskul;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Absensi;
use App\Models\Prestasi;
use App\Models\Nilai;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PembimbingController extends Controller
{
    private function getCurrentSemesterInfo()
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        if ($month >= 7) {
            $semester = 'Ganjil';
            $tahunAjaran = $year . '/' . ($year + 1);
        } else {
            $semester = 'Genap';
            $tahunAjaran = ($year - 1) . '/' . $year;
        }

        return [
            'semester' => $semester,
            'tahun_ajaran' => $tahunAjaran
        ];
    }

    public function dashboard()
    {
        $idPembimbing = Auth::guard('pembimbing')->id();
        $listEskul = Eskul::where('id_pembimbing', $idPembimbing)->get();

        return Inertia::render('Pembimbing/Dashboard', [
            'eskul_list' => $listEskul
        ]);
    }

    public function show(Request $request, $id)
    {
        $idPembimbing = Auth::guard('pembimbing')->id();
        $eskul = Eskul::where('id_eskul', $id)->where('id_pembimbing', $idPembimbing)->firstOrFail();
        
        $jadwal = Jadwal::where('id_eskul', $eskul->id_eskul)->get();
        $anggota = AnggotaEskul::with('peserta')->where('id_eskul', $eskul->id_eskul)->get();
        $kegiatan = Kegiatan::where('id_eskul', $eskul->id_eskul)->orderBy('tanggal', 'desc')->get();

        // --- Filter Log Absensi ---
        $queryLog = Absensi::with(['peserta', 'kegiatan'])
            ->whereHas('kegiatan', function($q) use ($id) {
                $q->where('id_eskul', $id);
            });
        
        if ($request->search) {
            $queryLog->whereHas('peserta', function($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->status) {
            $queryLog->where('status', $request->status);
        }
        if ($request->start_date && $request->end_date) {
            $queryLog->whereHas('kegiatan', function($q) use ($request) {
                $q->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            });
        }

        $allLogs = $queryLog->get(); 
        $totalData = $allLogs->count();
        $totalHadir = $allLogs->where('status', 'Hadir')->count();
        
        $summary = [
            'total_pertemuan' => $allLogs->pluck('id_kegiatan')->unique()->count(),
            'avg_kehadiran'   => $totalData > 0 ? round(($totalHadir / $totalData) * 100, 1) : 0,
            'total_sakit'     => $allLogs->where('status', 'Sakit')->count(),
            'total_izin'      => $allLogs->where('status', 'Izin')->count(),
            'total_alpha'     => $allLogs->where('status', 'Alpha')->count(),
        ];

        $logs = (clone $queryLog)
            ->join('kegiatan', 'absensi.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->orderBy('kegiatan.tanggal', 'desc')
            ->select('absensi.*')
            ->paginate(10)
            ->withQueryString();

        $prestasi = Prestasi::with('peserta')->where('id_eskul', $eskul->id_eskul)->orderBy('tanggal_lomba', 'desc')->get();

        $semesterInfo = $this->getCurrentSemesterInfo();
        $idKegiatanSemester = Kegiatan::where('id_eskul', $id)->pluck('id_kegiatan');

        $nilai = Nilai::with(['anggota_eskul.peserta'])
            ->where('id_eskul', $eskul->id_eskul)
            ->where('semester', $semesterInfo['semester'])
            ->where('tahun_ajaran', $semesterInfo['tahun_ajaran'])
            ->get()
            ->map(function ($item) use ($idKegiatanSemester) {
                $idPeserta = $item->anggota_eskul->id_peserta;
                $hadir = Absensi::whereIn('id_kegiatan', $idKegiatanSemester)
                    ->where('id_peserta', $idPeserta)
                    ->where('status', 'Hadir')
                    ->count();
                $totalPertemuan = $idKegiatanSemester->count();

                $item->statistik_hadir = $hadir;
                $item->total_pertemuan = $totalPertemuan;
                $item->persentase_hadir = $totalPertemuan > 0 ? round(($hadir / $totalPertemuan) * 100) : 0;

                return $item;
            });

        // --- FETCH DATA ABSENSI HARI INI (AGAR TIDAK RESET SAAT REFRESH) ---
        $today = Carbon::today()->format('Y-m-d');
        // Cari kegiatan hari ini di eskul ini
        $kegiatanHariIni = Kegiatan::where('id_eskul', $eskul->id_eskul)
            ->where('tanggal', $today)
            ->first();

        $existingAbsensi = [];
        if ($kegiatanHariIni) {
            // Ambil absensi beserta nilai hariannya
            $absensis = Absensi::with('nilai_harian')
                ->where('id_kegiatan', $kegiatanHariIni->id_kegiatan)
                ->get();
            
            // Format data agar mudah dibaca di frontend: { id_peserta: { status, nilai } }
            $formattedData = [];
            foreach ($absensis as $ab) {
                $formattedData[$ab->id_peserta] = [
                    'status' => $ab->status,
                    'nilai' => $ab->nilai_harian ? [
                        'skor_teknik' => $ab->nilai_harian->skor_teknik,
                        'skor_disiplin' => $ab->nilai_harian->skor_disiplin,
                        'skor_kerjasama' => $ab->nilai_harian->skor_kerjasama,
                        'catatan_harian' => $ab->nilai_harian->catatan_harian,
                    ] : null
                ];
            }

            // Distribusikan data ke semua jadwal hari ini 
            // (Karena struktur DB kita berbasis Tanggal, bukan Jadwal ID spesifik, jadi semua jadwal di hari itu share data yang sama)
            foreach ($jadwal as $j) {
                $existingAbsensi[$j->id_jadwal] = $formattedData;
            }
        }

        return Inertia::render('Pembimbing/EskulCardDetail', [
            'eskul'      => $eskul,
            'jadwal'     => $jadwal,
            'anggota'    => $anggota,
            'kegiatan'   => $kegiatan,
            'logs'       => $logs,
            'logSummary' => $summary,
            'filters'    => $request->only(['search', 'start_date', 'end_date', 'status']),
            'prestasi'   => $prestasi,
            'nilai'      => $nilai,
            'currentSemesterInfo' => [ 
                'semester' => $semesterInfo['semester'],
                'tahun' => $semesterInfo['tahun_ajaran']
            ],
            'existingAbsensi' => $existingAbsensi // Kirim data ini ke Vue
        ]);
    }
    
    // ... (method store, update, destroy tetap sama) ...
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pembimbing,username',
            'password'     => 'required|string|min:6',
        ]);

        Pembimbing::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'username'     => $validated['username'],
            'password'     => Hash::make($validated['password']),
        ]);

        return back();
    }

    public function update(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('pembimbing')->ignore($pembimbing->id_pembimbing, 'id_pembimbing')
            ],
            'password'     => 'nullable|string|min:6',
        ]);

        $dataToUpdate = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'username'     => $validated['username'],
        ];

        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        $pembimbing->update($dataToUpdate);

        return back();
    }

    public function destroy($id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->delete();

        return back();
    }
}