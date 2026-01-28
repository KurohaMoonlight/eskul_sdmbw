<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Eskul;
use App\Models\Jadwal;
use App\Models\Kegiatan;
use App\Models\NilaiHarian; // Import Model NilaiHarian
use App\Exports\AbsensiExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'tanggal'      => 'required|date',
            'id_jadwal'    => 'required|exists:jadwal,id_jadwal',
            'data_absensi' => 'required|array', 
            'data_nilai'   => 'nullable|array', // Validasi data nilai (opsional)
        ]);

        $tanggal = $request->tanggal;
        $idJadwal = $request->id_jadwal;
        $dataAbsensi = $request->data_absensi;
        $dataNilai = $request->data_nilai ?? []; // Default array kosong jika tidak ada

        DB::transaction(function () use ($tanggal, $idJadwal, $dataAbsensi, $dataNilai) {
            $jadwal = Jadwal::findOrFail($idJadwal);

            $kegiatan = Kegiatan::firstOrCreate(
                [
                    'id_eskul' => $jadwal->id_eskul,
                    'tanggal'  => $tanggal,
                ],
                [
                    'materi_kegiatan' => 'Latihan Rutin (Sesi ' . $jadwal->jam_mulai . ')',
                    'catatan_pembimbing' => 'Absensi dibuat otomatis dari jadwal.',
                ]
            );

            foreach ($dataAbsensi as $idPeserta => $status) {
                // 1. Simpan Absensi
                $absensi = Absensi::updateOrCreate(
                    [
                        'id_kegiatan' => $kegiatan->id_kegiatan,
                        'id_peserta'  => $idPeserta,
                    ],
                    [
                        'status' => $status
                    ]
                );

                // 2. Simpan Nilai Harian (Hanya jika status Hadir dan ada data nilai)
                if ($status === 'Hadir' && isset($dataNilai[$idPeserta])) {
                    $nilai = $dataNilai[$idPeserta];
                    
                    NilaiHarian::updateOrCreate(
                        ['id_absensi' => $absensi->id_absensi],
                        [
                            'skor_teknik' => $nilai['teknik'] ?? 0,
                            'skor_disiplin' => $nilai['disiplin'] ?? 0,
                            'skor_kerjasama' => $nilai['kerjasama'] ?? 0,
                            'catatan_harian' => $nilai['catatan_harian'] ?? null,
                        ]
                    );
                } else {
                    // Jika tidak hadir, hapus nilai harian jika ada (agar tidak mengganggu rata-rata)
                    NilaiHarian::where('id_absensi', $absensi->id_absensi)->delete();
                }
            }
        });

        return back()->with('success', 'Absensi dan Nilai Harian berhasil disimpan.');
    }


    /**
     * Menampilkan Halaman Cetak Log Absensi
     */
   public function exportExcel(Request $request)
    {
        $request->validate([
            'id_eskul' => 'required|exists:eskul,id_eskul',
        ]);

        $eskul = Eskul::find($request->id_eskul);
        $filters = $request->only(['search', 'status', 'start_date', 'end_date']);
        
        // Nama file yang rapi
        $fileName = 'Laporan_Absensi_' . str_replace(' ', '_', $eskul->nama_eskul) . '_' . date('d-m-Y') . '.xlsx';

        // Panggil class export dan download using fully qualified name to avoid namespace issues
        return \Maatwebsite\Excel\Facades\Excel::download(new AbsensiExport($request->id_eskul, $filters, $eskul->nama_eskul), $fileName);
    }
}