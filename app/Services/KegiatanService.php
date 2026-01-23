<?php

namespace App\Services;

use App\Models\Kegiatan;

class KegiatanService
{
    /**
     * Menyimpan data kegiatan baru.
     */
    public function createKegiatan(array $data)
    {
        return Kegiatan::create($data);
    }

    /**
     * Memperbarui data kegiatan.
     */
    public function updateKegiatan(Kegiatan $kegiatan, array $data)
    {
        return $kegiatan->update($data);
    }

    /**
     * Menghapus data kegiatan.
     */
    public function deleteKegiatan(Kegiatan $kegiatan)
    {
        return $kegiatan->delete();
    }
}