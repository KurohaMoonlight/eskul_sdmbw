<?php

namespace App\Services;

use App\Models\Jadwal;

class JadwalService
{
    /**
     * Membuat jadwal baru. 
     * Menerima array hari dari form checklist.
     */
    public function createJadwal(array $data)
    {
        // Jika user memilih banyak hari (misal Senin & Kamis)
        // Kita loop dan buat jadwal untuk masing-masing hari
        if (isset($data['hari']) && is_array($data['hari'])) {
            foreach ($data['hari'] as $hari) {
                // Copy data master dan ganti harinya
                $jadwalItem = $data;
                $jadwalItem['hari'] = $hari;
                
                Jadwal::create($jadwalItem);
            }
        } else {
            // Fallback jika dikirim single string (legacy support)
            Jadwal::create($data);
        }
    }

    /**
     * Update jadwal.
     */
    public function updateJadwal(Jadwal $jadwal, array $data)
    {
        // Saat edit, user mengedit 1 ID spesifik.
        // Jika checklist hari dikirim sebagai array, kita ambil elemen pertama saja
        // Karena kita tidak bisa mengubah 1 baris ID menjadi banyak baris (ambigu).
        if (isset($data['hari']) && is_array($data['hari'])) {
            $data['hari'] = $data['hari'][0] ?? $jadwal->hari;
        }

        $jadwal->update($data);
        
        return $jadwal;
    }

    /**
     * Hapus jadwal.
     */
    public function deleteJadwal(Jadwal $jadwal)
    {
        return $jadwal->delete();
    }
}