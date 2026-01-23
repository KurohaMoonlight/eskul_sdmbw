<?php

namespace App\Services;

use App\Models\Eskul;
use App\Models\Peserta;
use Illuminate\Support\Facades\Storage;

class EskulService
{
    public function getAllEskuls()
    {
        return Eskul::with('pembimbing')->get();
    }

    public function createEskul(array $data)
    {
        return Eskul::create($data);
    }

    /**
     * PERBAIKAN UTAMA ADA DI SINI
     */
    public function getEskulDetail($id)
    {
        // Pastikan 'anggota_eskul.peserta' ada di dalam array with()
        // Ini akan memuat relasi: Eskul -> AnggotaEskul -> Peserta (Siswa)
        $eskul = Eskul::with([
            'pembimbing', 
            'jadwal', 
            'anggota_eskul.peserta' 
        ])->findOrFail($id);

        // Ambil data siswa untuk dropdown tambah anggota
        $allPeserta = Peserta::orderBy('nama_lengkap', 'asc')->get();

        return [
            'eskul' => $eskul,
            'allPeserta' => $allPeserta,
        ];
    }

    public function updateEskul(Eskul $eskul, array $data)
    {
        return $eskul->update($data);
    }

    public function deleteEskul(Eskul $eskul)
    {
        return $eskul->delete();
    }
}