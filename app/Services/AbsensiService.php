<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Kegiatan;
use App\Models\Eskul;
use Illuminate\Support\Facades\DB;

class AbsensiService
{
    /**
     * Menyimpan data absensi.
     * Mencari atau membuat kegiatan, lalu menyimpan absensi peserta.
     *
     * @param string $tanggal
     * @param int $idJadwal
     * @param array $dataAbsensi
     * @return void
     */
    public function storeAbsensi(string $tanggal, int $idJadwal, array $dataAbsensi)
    {
        DB::transaction(function () use ($tanggal, $idJadwal, $dataAbsensi) {
            // Ambil jadwal untuk referensi id_eskul
            $jadwal = Jadwal::findOrFail($idJadwal);

            // Cari atau buat kegiatan untuk tanggal & eskul ini
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

            // Simpan absensi per peserta
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
    }

    /**
     * Mendapatkan data log absensi untuk keperluan export.
     *
     * @param int $idEskul
     * @param array $filters
     * @return array Mengembalikan array berisi 'eskul', 'logs', dan 'summary'
     */
    public function getExportData(int $idEskul, array $filters)
    {
        // Ambil data Eskul
        $eskul = Eskul::with('pembimbing')->find($idEskul);

        // Query dasar Log Absensi
        $queryLog = Absensi::with(['peserta', 'kegiatan'])
            ->whereHas('kegiatan', function($q) use ($idEskul) {
                $q->where('id_eskul', $idEskul);
            });

        // Filter berdasarkan Nama Peserta
        if (!empty($filters['search'])) {
            $queryLog->whereHas('peserta', function($q) use ($filters) {
                $q->where('nama_lengkap', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Filter berdasarkan Status
        if (!empty($filters['status'])) {
            $queryLog->where('status', $filters['status']);
        }

        // Filter berdasarkan Rentang Tanggal
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $queryLog->whereHas('kegiatan', function($q) use ($filters) {
                $q->whereBetween('tanggal', [$filters['start_date'], $filters['end_date']]);
            });
        }

        // Ambil data log yang sudah diurutkan
        $logs = $queryLog->join('kegiatan', 'absensi.id_kegiatan', '=', 'kegiatan.id_kegiatan')
            ->orderBy('kegiatan.tanggal', 'desc')
            ->select('absensi.*')
            ->get();

        // Hitung Ringkasan Data
        $summary = [
            'total_pertemuan' => $logs->pluck('id_kegiatan')->unique()->count(),
            'hadir' => $logs->where('status', 'Hadir')->count(),
            'sakit' => $logs->where('status', 'Sakit')->count(),
            'izin'  => $logs->where('status', 'Izin')->count(),
            'alpha' => $logs->where('status', 'Alpha')->count(),
        ];

        return [
            'eskul' => $eskul,
            'logs' => $logs,
            'summary' => $summary,
        ];
    }
}