<?php

namespace App\Services;

use App\Models\AnggotaEskul;

class AnggotaService
{
    public function createAnggota(array $data)
    {
        // Cek apakah siswa sudah ada di eskul ini pada tahun ajaran yang sama?
        // (Opsional: Mencegah duplikasi data)
        $exists = AnggotaEskul::where('id_eskul', $data['id_eskul'])
            ->where('id_peserta', $data['id_peserta'])
            ->where('tahun_ajaran', $data['tahun_ajaran'])
            ->exists();

        if ($exists) {
            // Jika ingin strict, bisa throw error. 
            // Tapi untuk sekarang kita biarkan atau return null agar tidak error fatal.
            return null; 
        }

        return AnggotaEskul::create($data);
    }

    public function updateAnggota(AnggotaEskul $anggota, array $data)
    {
        // Pastikan id_peserta tidak diubah sembarangan lewat edit (biasanya di-lock di frontend)
        return $anggota->update($data);
    }

    public function deleteAnggota(AnggotaEskul $anggota)
    {
        return $anggota->delete();
    }
}