<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\AnggotaEskul;
use App\Models\Eskul;
use App\Models\NilaiHarian;
use App\Exports\NilaiExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NilaiController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'id_eskul' => 'required|exists:eskul,id_eskul',
            'semester' => 'required|string',
            'tahun_ajaran' => 'required|string',
        ]);

        $idEskul = $request->id_eskul;
        $semester = $request->semester;
        $tahunAjaran = $request->tahun_ajaran;

        $anggotaAktif = AnggotaEskul::where('id_eskul', $idEskul)
            ->where('status_aktif', true)
            ->get();

        if ($anggotaAktif->isEmpty()) {
            return back()->withErrors(['message' => 'Gagal: Tidak ditemukan anggota dengan status AKTIF di eskul ini. Silakan cek data anggota.']);
        }

        DB::transaction(function () use ($anggotaAktif, $idEskul, $semester, $tahunAjaran) {
            foreach ($anggotaAktif as $anggota) {
                $exists = Nilai::where('id_anggota', $anggota->id_anggota)
                    ->where('semester', $semester)
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->exists();

                if (!$exists) {
                    Nilai::create([
                        'id_anggota' => $anggota->id_anggota,
                        'id_eskul' => $idEskul,
                        'nilai_disiplin' => 0,
                        'nilai_teknik' => 0,
                        'nilai_kerjasama' => 0,
                        'catatan_rapor' => '-',
                        'semester' => $semester,
                        'tahun_ajaran' => $tahunAjaran,
                    ]);
                }
            }
        });

        return back()->with('success', 'Periode penilaian berhasil dibuka.');
    }

    public function updateBulk(Request $request)
    {
        $request->validate([
            'nilai_data' => 'required|array',
            'nilai_data.*.id_nilai' => 'required|exists:nilai,id_nilai',
            'nilai_data.*.nilai_disiplin' => 'required|integer|min:0|max:100',
            'nilai_data.*.nilai_teknik' => 'required|integer|min:0|max:100',
            'nilai_data.*.nilai_kerjasama' => 'required|integer|min:0|max:100',
            'nilai_data.*.catatan_rapor' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->nilai_data as $data) {
                Nilai::where('id_nilai', $data['id_nilai'])->update([
                    'nilai_disiplin' => $data['nilai_disiplin'],
                    'nilai_teknik' => $data['nilai_teknik'],
                    'nilai_kerjasama' => $data['nilai_kerjasama'],
                    'catatan_rapor' => $data['catatan_rapor'],
                ]);
            }
        });

        return back()->with('success', 'Data nilai berhasil diperbarui.');
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'id_eskul' => 'required|exists:eskul,id_eskul',
            'semester' => 'required|string',
            'tahun_ajaran' => 'required|string',
        ]);

        $eskul = Eskul::with('pembimbing')->find($request->id_eskul);
        $pembimbingName = $eskul->pembimbing ? $eskul->pembimbing->nama_lengkap : '.........................';

        $cleanSemester = str_replace('/', '-', $request->tahun_ajaran);
        $fileName = 'Nilai_' . str_replace(' ', '_', $eskul->nama_eskul) . '_' . $cleanSemester . '_' . $request->semester . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new NilaiExport(
                $request->id_eskul, 
                $request->semester, 
                $request->tahun_ajaran, 
                $eskul->nama_eskul, 
                $pembimbingName
            ), 
            $fileName
        );
    }

    /**
     * Hitung Rata-rata dari Nilai Harian
     */
    public function syncFromDaily(Request $request)
    {
        $request->validate([
            'id_eskul' => 'required|exists:eskul,id_eskul',
            'semester' => 'required|string',
            'tahun_ajaran' => 'required|string',
        ]);

        $idEskul = $request->id_eskul;
        $semester = $request->semester;
        $tahunAjaran = $request->tahun_ajaran;

        // Logika Bulan Semester
        $startMonth = ($semester === 'Ganjil') ? 7 : 1;
        $endMonth = ($semester === 'Ganjil') ? 12 : 6;
        
        $years = explode('/', $tahunAjaran);
        $startYear = ($semester === 'Ganjil') ? $years[0] : $years[1];
        
        // Query Nilai Rapor yang sudah ada
        $dataNilaiRapor = Nilai::where('id_eskul', $idEskul)
            ->where('semester', $semester)
            ->where('tahun_ajaran', $tahunAjaran)
            ->get();

        if ($dataNilaiRapor->isEmpty()) {
            return back()->withErrors(['message' => 'Data rapor belum dibuat. Silakan klik "Buka Periode Penilaian" terlebih dahulu.']);
        }

        DB::transaction(function () use ($dataNilaiRapor, $startYear, $startMonth, $endMonth) {
            foreach ($dataNilaiRapor as $rapor) {
                // Cari ID Anggota -> ID Peserta
                $anggota = AnggotaEskul::find($rapor->id_anggota);
                if (!$anggota) continue;

                // Cari semua nilai harian peserta ini di rentang waktu semester
                $nilaiHarian = NilaiHarian::whereHas('absensi', function($q) use ($anggota, $startYear, $startMonth, $endMonth) {
                    $q->where('id_peserta', $anggota->id_peserta)
                      ->whereHas('kegiatan', function($k) use ($startYear, $startMonth, $endMonth) {
                          $k->whereYear('tanggal', $startYear)
                            ->whereMonth('tanggal', '>=', $startMonth)
                            ->whereMonth('tanggal', '<=', $endMonth);
                      });
                })->get();

                if ($nilaiHarian->count() > 0) {
                    // Hitung Rata-rata
                    $avgTeknik = $nilaiHarian->avg('skor_teknik');
                    $avgDisiplin = $nilaiHarian->avg('skor_disiplin');
                    $avgKerjasama = $nilaiHarian->avg('skor_kerjasama');
                    $count = $nilaiHarian->count();

                    // Update Rapor
                    $rapor->update([
                        'nilai_teknik' => round($avgTeknik),
                        'nilai_disiplin' => round($avgDisiplin),
                        'nilai_kerjasama' => round($avgKerjasama),
                        'catatan_rapor' => "Nilai dihitung otomatis dari $count pertemuan harian.",
                    ]);
                }
            }
        });

        return back()->with('success', 'Nilai berhasil disinkronisasi dari data harian.');
    }
}