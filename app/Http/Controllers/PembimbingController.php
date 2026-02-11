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
     * Helper Range Tanggal
     */
    private function getSemesterDateRange($semester, $tahunAjaran)
    {
        $years = explode('/', $tahunAjaran);
        if (count($years) < 2) {
             $y = date('Y');
             $years = [$y, $y+1];
        }
        $startYear = (int)$years[0];
        $endYear = (int)$years[1];

        if ($semester === 'Ganjil') {
            return [
                Carbon::create($startYear, 7, 1)->startOfDay()->format('Y-m-d'),
                Carbon::create($startYear, 12, 31)->endOfDay()->format('Y-m-d')
            ];
        } else {
            return [
                Carbon::create($endYear, 1, 1)->startOfDay()->format('Y-m-d'),
                Carbon::create($endYear, 6, 30)->endOfDay()->format('Y-m-d')
            ];
        }
    }

    public function dashboard()
    {
        $idPembimbing = Auth::guard('pembimbing')->id();
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
        // Mengambil semua kegiatan untuk kalender (tidak dipaginate)
        $allKegiatan = Kegiatan::where('id_eskul', $eskul->id_eskul)->orderBy('tanggal', 'desc')->get();

        // ---------------------------------------------------------
        // LOGIC REKAP ABSENSI & NILAI (FIXED)
        // ---------------------------------------------------------
        $queryLog = Absensi::with(['peserta', 'kegiatan', 'nilai_harian'])
            ->whereHas('kegiatan', function($q) use ($id) {
                $q->where('id_eskul', $id);
            });
        
        // Filter Search
        if ($request->search) {
            $queryLog->whereHas('peserta', function($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
            });
        }
        // Filter Status
        if ($request->status) {
            $queryLog->where('status', $request->status);
        }
        // Filter Tanggal
        if ($request->start_date && $request->end_date) {
            $queryLog->whereHas('kegiatan', function($q) use ($request) {
                $q->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            });
        }

        // Logic Sorting / Score Mode
        if ($request->score_mode) {
            $queryLog->leftJoin('nilai_harian', 'absensi.id_absensi', '=', 'nilai_harian.id_absensi')
                     ->select('absensi.*'); // Penting agar select tidak ambigu

            $rawAvgScore = '(COALESCE(nilai_harian.skor_teknik, 0) + COALESCE(nilai_harian.skor_disiplin, 0) + COALESCE(nilai_harian.skor_kerjasama, 0)) / 3';

            if ($request->score_mode === 'highest') {
                $queryLog->orderByRaw("$rawAvgScore DESC");
            } elseif ($request->score_mode === 'lowest') {
                $queryLog->where('absensi.status', 'Hadir')->orderByRaw("$rawAvgScore ASC");
            } elseif ($request->score_mode === 'under_70') {
                $queryLog->where('absensi.status', 'Hadir')->whereRaw("$rawAvgScore < 70")->orderByRaw("$rawAvgScore ASC");
            }
        } else {
            // Default Sorting: Join agar bisa order by tanggal kegiatan
            $queryLog->join('kegiatan', 'absensi.id_kegiatan', '=', 'kegiatan.id_kegiatan')
                     ->select('absensi.*') // Penting!
                     ->orderBy('kegiatan.tanggal', 'desc');
        }

        // --- FIX UTAMA: CLONE QUERY UNTUK SUMMARY ---
        // Kita clone queryLog agar eksekusi get() tidak mengganggu paginate() di bawahnya
        $summaryQuery = clone $queryLog;
        $allLogsData = $summaryQuery->get(); 

        $totalData = $allLogsData->count();
        $totalHadir = $allLogsData->where('status', 'Hadir')->count();
        
        $summary = [
            'total_pertemuan' => $allLogsData->pluck('id_kegiatan')->unique()->count(),
            'avg_kehadiran'   => $totalData > 0 ? round(($totalHadir / $totalData) * 100, 1) : 0,
            'total_sakit'     => $allLogsData->where('status', 'Sakit')->count(),
            'total_izin'      => $allLogsData->where('status', 'Izin')->count(),
            'total_alpha'     => $allLogsData->where('status', 'Alpha')->count(),
        ];

        // Eksekusi Pagination (menggunakan builder asli yang belum di-get)
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
                // Hitung total pertemuan berdasarkan kehadiran siswa tersebut
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
        foreach ($kegiatanHariIni as $k) { // Gunakan $k
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
                // Pastikan format jam sama (H:i)
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
            'kegiatan'   => $allKegiatan, // Pastikan ini Array lengkap
            'logs'       => $logs,
            'logSummary' => $summary,
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