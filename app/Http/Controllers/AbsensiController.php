<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Eskul;
use App\Models\Jadwal;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\AbsensiExport; // Import Export Class
use Maatwebsite\Excel\Facades\Excel; // Import Facade Excel

class AbsensiController extends Controller
{
    /**
     * Menyimpan data absensi dari BoxAbsensi.vue
     */
    public function store(Request $request)
    {
        // ... (Kode store yang sudah ada sebelumnya biarkan tetap sama) ...
        // Validasi Input
        $request->validate([
            'tanggal'      => 'required|date',
            'id_jadwal'    => 'required|exists:jadwal,id_jadwal',
            'data_absensi' => 'required|array', 
        ]);

        $tanggal = $request->tanggal;
        $idJadwal = $request->id_jadwal;
        $dataAbsensi = $request->data_absensi;

        DB::transaction(function () use ($tanggal, $idJadwal, $dataAbsensi) {
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
                Absensi::updateOrCreate(
                    [
                        'id_kegiatan' => $kegiatan->id_kegiatan,
                        'id_peserta'  => $idPeserta,
                    ],
                    [
                        'status' => $status
                    ]
                );
            }
        });

        return back()->with('success', 'Absensi berhasil disimpan.');
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