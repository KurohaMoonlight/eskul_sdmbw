<?php

namespace App\Http\Controllers;

use App\Models\Nilai;
use App\Models\AnggotaEskul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Eskul; // Import Eskul
use App\Exports\NilaiExport; // Import Export Class
use Maatwebsite\Excel\Facades\Excel; // Import Excel Facade

class NilaiController extends Controller
{
    /**
     * Generate Nilai Awal (0) untuk semua anggota aktif
     * Dipanggil saat tombol "Buka Periode Penilaian" diklik
     */
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

        // Ambil semua anggota aktif di eskul ini pada tahun ajaran tersebut
        // (Asumsi: anggota_eskul juga punya kolom tahun_ajaran yang cocok, atau kita ambil yang aktif saja)
        $anggotaAktif = AnggotaEskul::where('id_eskul', $idEskul)
            ->where('status_aktif', true)
            ->where('tahun_ajaran', $tahunAjaran) 
            ->get();

        if ($anggotaAktif->isEmpty()) {
            return back()->withErrors(['message' => 'Tidak ada anggota aktif untuk dinilai pada tahun ajaran ini.']);
        }

        DB::transaction(function () use ($anggotaAktif, $idEskul, $semester, $tahunAjaran) {
            foreach ($anggotaAktif as $anggota) {
                // Cek apakah nilai sudah ada biar gak duplikat (safety check)
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

    /**
     * Update Nilai secara Massal (Bulk Update)
     * Dipanggil saat tombol "Simpan Perubahan" diklik
     */
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

        // Bersihkan nama file dari karakter aneh
        $cleanSemester = str_replace('/', '-', $request->tahun_ajaran); // 2025/2026 -> 2025-2026
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
}