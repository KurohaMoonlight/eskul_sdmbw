<?php

namespace App\Services;

use App\Models\Jadwal;
use Illuminate\Support\Facades\DB;

class JadwalService
{
    /**
     * Menyimpan jadwal, mendukung multi-hari sekaligus.
     */
    public function createJadwal(array $data)
    {
        return DB::transaction(function () use ($data) {
            $created = [];
            foreach ($data['hari'] as $hari) {
                $created[] = Jadwal::create([
                    'id_eskul'    => $data['id_eskul'],
                    'hari'        => $hari,
                    'jam_mulai'   => $data['jam_mulai'],
                    'jam_selesai' => $data['jam_selesai'],
                    'lokasi'      => $data['lokasi'] ?? null,
                    'kelas_min'   => $data['kelas_min'],
                    'kelas_max'   => $data['kelas_max'],
                ]);
            }
            return $created;
        });
    }

    /**
     * Update satu jadwal.
     */
    public function updateJadwal(Jadwal $jadwal, array $data)
    {
        // Ambil hari pertama dari array checklist karena update per ID row
        $hariDipilih = $data['hari'][0] ?? $jadwal->hari;

        return $jadwal->update([
            'hari'        => $hariDipilih,
            'jam_mulai'   => $data['jam_mulai'],
            'jam_selesai' => $data['jam_selesai'],
            'lokasi'      => $data['lokasi'] ?? null,
            'kelas_min'   => $data['kelas_min'],
            'kelas_max'   => $data['kelas_max'],
        ]);
    }

    /**
     * Hapus jadwal.
     */
    public function deleteJadwal(Jadwal $jadwal)
    {
        return $jadwal->delete();
    }
}