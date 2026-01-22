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
    /**
     * Helper: Menentukan Tahun Ajaran & Semester saat ini
     */
    private function getCurrentSemesterInfo()
    {
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        if ($month >= 7) {
            // Juli - Desember: Semester Ganjil, Tahun Ajaran = Tahun Ini / Tahun Depan
            $semester = 'Ganjil';
            $tahunAjaran = $year . '/' . ($year + 1);
        } else {
            // Januari - Juni: Semester Genap, Tahun Ajaran = Tahun Lalu / Tahun Ini
            $semester = 'Genap';
            $tahunAjaran = ($year - 1) . '/' . $year;
        }

        return [
            'semester' => $semester,
            'tahun_ajaran' => $tahunAjaran
        ];
    }

    /**
     * Menampilkan Dashboard Pembimbing.
     */
    public function dashboard()
    {
        $idPembimbing = Auth::guard('pembimbing')->id();
        $listEskul = Eskul::where('id_pembimbing', $idPembimbing)->get();

        return Inertia::render('Pembimbing/Dashboard', [
            'eskul_list' => $listEskul
        ]);
    }

    /**
     * Menampilkan Detail Eskul (Jadwal, Anggota, & Kegiatan).
     */
    public function show(Request $request, $id)
    {
        $idPembimbing = Auth::guard('pembimbing')->id();

        // 1. Validasi Kepemilikan Eskul
        $eskul = Eskul::where('id_eskul', $id)
                      ->where('id_pembimbing', $idPembimbing)
                      ->firstOrFail();

        // 2. Data Dasar
        $jadwal = Jadwal::where('id_eskul', $eskul->id_eskul)->get();
        $anggota = AnggotaEskul::with('peserta')
                    ->where('id_eskul', $eskul->id_eskul)
                    ->get();
        $kegiatan = Kegiatan::where('id_eskul', $eskul->id_eskul)
                    ->orderBy('tanggal', 'desc')
                    ->get();

        // 3. LOGIKA FILTER LOG ABSENSI (DIPERBAIKI)
        $queryLog = Absensi::with(['peserta', 'kegiatan'])
            ->whereHas('kegiatan', function($q) use ($id) {
                $q->where('id_eskul', $id);
            });

        // Filter: Search Nama Siswa
        if ($request->search) {
            $queryLog->whereHas('peserta', function($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
            });
        }

        // Filter: Status
        if ($request->status) {
            $queryLog->where('status', $request->status);
        }

        // Filter: Rentang Tanggal
        if ($request->start_date && $request->end_date) {
            $queryLog->whereHas('kegiatan', function($q) use ($request) {
                $q->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            });
        }

        // Clone query untuk statistik (mengambil semua data tanpa limit pagination)
        $allLogs = $queryLog->get(); 
        
        // Paginasi Log untuk Tabel (Limit 10)
        $logs = (clone $queryLog)
            ->join('kegiatan', 'absensi.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->orderBy('kegiatan.tanggal', 'desc')
            ->select('absensi.*') // Pastikan select tabel utama agar tidak tertimpa join
            ->paginate(10)
            ->withQueryString();

        // Hitung Statistik (DIPERBAIKI)
        $totalData = $allLogs->count();
        $totalHadir = $allLogs->where('status', 'Hadir')->count();
        
        $summary = [
            'total_pertemuan' => $allLogs->pluck('id_kegiatan')->unique()->count(),
            'avg_kehadiran'   => $totalData > 0 ? round(($totalHadir / $totalData) * 100, 1) : 0,
            'total_sakit'     => $allLogs->where('status', 'Sakit')->count(),
            'total_izin'      => $allLogs->where('status', 'Izin')->count(),
            'total_alpha'     => $allLogs->where('status', 'Alpha')->count(),
        ];

        // 4. AMBIL DATA PRESTASI
        $prestasi = Prestasi::with('peserta')
            ->where('id_eskul', $eskul->id_eskul)
            ->orderBy('tanggal_lomba', 'desc')
            ->get();

        // 5. AMBIL DATA NILAI
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

        return Inertia::render('Pembimbing/EskulCardDetail', [
            'eskul'      => $eskul,
            'jadwal'     => $jadwal,
            'anggota'    => $anggota,
            'kegiatan'   => $kegiatan,
            'logs'       => $logs,
            'logSummary' => $summary, // Kirim data summary yang sudah dihitung
            'filters'    => $request->only(['search', 'start_date', 'end_date', 'status']),
            'prestasi'   => $prestasi,
            'nilai'      => $nilai, 
            'currentSemesterInfo' => [ 
                'semester' => $semesterInfo['semester'],
                'tahun' => $semesterInfo['tahun_ajaran']
            ]
        ]);
    }

    // --- Method CRUD Pembimbing (Sama seperti sebelumnya) ---

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