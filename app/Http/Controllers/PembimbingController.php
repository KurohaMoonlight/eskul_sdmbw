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
use Illuminate\Support\Facades\DB; 

class PembimbingController extends Controller
{
    /**
     * Helper untuk menentukan Semester Ganjil/Genap saat ini
     */
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

    /**
     * Helper untuk mendapatkan Range Tanggal (Start & End) berdasarkan Semester & Tahun Ajaran
     * Digunakan untuk filter query statistik kehadiran
     */
    private function getSemesterDateRange($semester, $tahunAjaran)
    {
        $years = explode('/', $tahunAjaran);
        
        // Validasi format tahun (misal: "2025/2026"), jika error return tahun ini
        if (count($years) < 2) {
             $y = date('Y');
             $years = [$y, $y+1];
        }
        
        $startYear = (int)$years[0];
        $endYear = (int)$years[1];

        if ($semester === 'Ganjil') {
            // Semester Ganjil: 1 Juli - 31 Desember (Tahun Awal)
            return [
                Carbon::create($startYear, 7, 1)->startOfDay()->format('Y-m-d'),
                Carbon::create($startYear, 12, 31)->endOfDay()->format('Y-m-d')
            ];
        } else {
            // Semester Genap: 1 Januari - 30 Juni (Tahun Akhir)
            return [
                Carbon::create($endYear, 1, 1)->startOfDay()->format('Y-m-d'),
                Carbon::create($endYear, 6, 30)->endOfDay()->format('Y-m-d')
            ];
        }
    }

    public function dashboard()
    {
        $idPembimbing = Auth::guard('pembimbing')->id();
        
        // Gunakan whereHas untuk relasi many-to-many pembimbings
        $listEskul = Eskul::whereHas('pembimbings', function($q) use ($idPembimbing) {
            $q->where('pembimbing.id_pembimbing', $idPembimbing);
        })->get();

        return Inertia::render('Pembimbing/Dashboard', [
            'eskul_list' => $listEskul
        ]);
    }

        public function show(Request $request, $id)
    {
        $idPembimbing = Auth::guard('pembimbing')->id();
        
        $eskul = Eskul::with('pembimbings')
            ->where('id_eskul', $id)
            ->whereHas('pembimbings', function($q) use ($idPembimbing) {
                $q->where('pembimbing.id_pembimbing', $idPembimbing);
            })
            ->firstOrFail();
        
        $jadwal = Jadwal::where('id_eskul', $eskul->id_eskul)->get();
        $anggota = AnggotaEskul::with('peserta')->where('id_eskul', $eskul->id_eskul)->get();
        // Simpan semua kegiatan di variabel ini
        $allKegiatan = Kegiatan::where('id_eskul', $eskul->id_eskul)->orderBy('tanggal', 'desc')->get();

        // --- Filter Log Absensi & Nilai ---
        $queryLog = Absensi::with(['peserta', 'kegiatan', 'nilai_harian'])
            ->whereHas('kegiatan', function($q) use ($id) {
                $q->where('id_eskul', $id);
            });
        
        // ... (logika filter log tetap sama) ...

        $logs = $queryLog->paginate(10)->withQueryString();
        $prestasi = Prestasi::with('peserta')->where('id_eskul', $eskul->id_eskul)->orderBy('tanggal_lomba', 'desc')->get();

        // --- UPDATE LOGIC NILAI ---
        $currentSemesterInfo = $this->getCurrentSemesterInfo();
        $filterSemester = $request->input('semester', $currentSemesterInfo['semester']);
        $filterTahun = $request->input('tahun_ajaran', $currentSemesterInfo['tahun_ajaran']);
        $dateRange = $this->getSemesterDateRange($filterSemester, $filterTahun);

        $idKegiatanSemester = Kegiatan::where('id_eskul', $id)
            ->whereBetween('tanggal', $dateRange)
            ->pluck('id_kegiatan');
        
        $nilai = Nilai::with(['anggota_eskul.peserta'])
            ->where('id_eskul', $eskul->id_eskul)
            ->where('semester', $filterSemester)
            ->where('tahun_ajaran', $filterTahun)
            ->get()
            ->map(function ($item) use ($idKegiatanSemester) {
                $idPeserta = $item->anggota_eskul->id_peserta;
                $totalPertemuan = Absensi::whereIn('id_kegiatan', $idKegiatanSemester)
                    ->where('id_peserta', $idPeserta)
                    ->count();
                $hadir = Absensi::whereIn('id_kegiatan', $idKegiatanSemester)
                    ->where('id_peserta', $idPeserta)
                    ->where('status', 'Hadir')
                    ->count();

                $item->statistik_hadir = $hadir;
                $item->total_pertemuan = $totalPertemuan;
                $item->persentase_hadir = $totalPertemuan > 0 ? round(($hadir / $totalPertemuan) * 100) : 0;
                return $item;
            });

        // --- FETCH ABSENSI HARI INI ---
        $today = Carbon::today()->format('Y-m-d');
        $kegiatanHariIni = Kegiatan::where('id_eskul', $eskul->id_eskul)->where('tanggal', $today)->get();
        
        $existingAbsensi = [];
        // PERBAIKAN: Ganti $kegiatan menjadi $k agar tidak menabrak $allKegiatan
        foreach ($kegiatanHariIni as $k) {
            $absensis = Absensi::with('nilai_harian')->where('id_kegiatan', $k->id_kegiatan)->get();
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
            
            $matchedJadwal = $jadwal->first(function($j) use ($k) {
                return substr($j->jam_mulai, 0, 5) === substr($k->jam_mulai, 0, 5);
            });

            if ($matchedJadwal) {
                $existingAbsensi[$matchedJadwal->id_jadwal] = $formattedData;
            }
        }

        return Inertia::render('Pembimbing/EskulCardDetail', [
            'eskul'      => $eskul,
            'jadwal'     => $jadwal,
            'anggota'    => $anggota,
            'kegiatan'   => $allKegiatan, // Gunakan variabel yang benar
            'logs'       => $logs,
            'logSummary' => $summary ?? [],
            'filters'    => $request->only(['search', 'start_date', 'end_date', 'status', 'score_mode']),
            'prestasi'   => $prestasi,
            'nilai'      => $nilai,
            'currentSemesterInfo' => [ 
                'semester' => $currentSemesterInfo['semester'],
                'tahun' => $currentSemesterInfo['tahun_ajaran']
            ],
            'activeNilaiFilter' => [
                'semester' => $filterSemester,
                'tahun_ajaran' => $filterTahun
            ],
            'existingAbsensi' => $existingAbsensi
        ]);
    }

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
            'username'     => ['required', 'string', 'max:50', Rule::unique('pembimbing')->ignore($pembimbing->id_pembimbing, 'id_pembimbing')],
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
        Pembimbing::findOrFail($id)->delete();
        return back();
    }
}