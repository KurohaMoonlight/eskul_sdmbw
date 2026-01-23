<?php

namespace App\Services;

use App\Models\Eskul;
use App\Models\Jadwal;
use App\Models\AnggotaEskul;
use Inertia\Inertia;

class EskulService
{
    /**
     * Menyimpan data eskul baru.
     */
    public function createEskul(array $data)
    {
        return Eskul::create($data);
    }

    /**
     * Memperbarui data eskul.
     */
    public function updateEskul(Eskul $eskul, array $data)
    {
        return $eskul->update($data);
    }

    /**
     * Menghapus eskul.
     */
    public function deleteEskul(Eskul $eskul)
    {
        return $eskul->delete();
    }

    /**
     * Mendapatkan detail eskul lengkap dengan relasinya untuk halaman detail admin.
     */
    public function getEskulDetail(int $id)
    {
        $eskul = Eskul::with('pembimbing')->findOrFail($id);
        
        $jadwal = Jadwal::where('id_eskul', $eskul->id_eskul)->get();
        
        $anggota = AnggotaEskul::with('peserta')
                    ->where('id_eskul', $eskul->id_eskul)
                    ->get();

        return [
            'eskul'   => $eskul,
            'jadwal'  => $jadwal,
            'anggota' => $anggota,
        ];
    }
}