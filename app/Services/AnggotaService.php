<?php

namespace App\Services;

use App\Models\Peserta;
use App\Models\AnggotaEskul;
use Illuminate\Support\Facades\DB;

class AnggotaService
{
    /**
     * Membuat data Peserta baru dan menghubungkannya sebagai Anggota Eskul.
     * Menggunakan Database Transaction untuk integritas data.
     * * @param array $data Data yang sudah divalidasi
     * @return AnggotaEskul
     */
    public function createAnggota(array $data)
    {
        return DB::transaction(function () use ($data) {
            // 1. Buat Data Master Peserta (Siswa)
            $peserta = Peserta::create([
                'nama_lengkap'  => $data['nama_lengkap'],
                'tingkat_kelas' => $data['tingkat_kelas'],
                'jenis_kelamin' => $data['jenis_kelamin'],
            ]);

            // 2. Daftarkan Peserta ke Eskul (Tabel Relasi)
            return AnggotaEskul::create([
                'id_eskul'     => $data['id_eskul'],
                'id_peserta'   => $peserta->id_peserta,
                'tahun_ajaran' => $data['tahun_ajaran'],
                'status_aktif' => true, // Default aktif saat mendaftar
            ]);
        });
    }

    /**
     * Memperbarui data Anggota Eskul dan data Peserta terkait.
     * * @param AnggotaEskul $anggota Model Anggota yang akan diupdate
     * @param array $data Data baru
     * @return void
     */
    public function updateAnggota(AnggotaEskul $anggota, array $data)
    {
        DB::transaction(function () use ($anggota, $data) {
            // 1. Update Data Master Peserta
            $anggota->peserta->update([
                'nama_lengkap'  => $data['nama_lengkap'],
                'tingkat_kelas' => $data['tingkat_kelas'],
                'jenis_kelamin' => $data['jenis_kelamin'],
            ]);

            // 2. Update Data Keanggotaan
            $anggota->update([
                'tahun_ajaran' => $data['tahun_ajaran'],
                'status_aktif' => $data['status_aktif'],
            ]);
        });
    }

    /**
     * Menghapus Anggota Eskul beserta data Peserta-nya.
     * * @param AnggotaEskul $anggota Model Anggota yang akan dihapus
     * @return void
     */
    public function deleteAnggota(AnggotaEskul $anggota)
    {
        $peserta = $anggota->peserta;

        DB::transaction(function () use ($anggota, $peserta) {
            // Hapus data keanggotaan terlebih dahulu (child)
            $anggota->delete();

            // Hapus data master peserta jika ada (parent)
            // Catatan: Jika peserta bisa ikut banyak eskul, logika ini harus disesuaikan
            // agar tidak menghapus peserta yang masih aktif di eskul lain.
            // Namun untuk struktur saat ini, kita asumsikan hard delete.
            if ($peserta) {
                $peserta->delete();
            }
        });
    }
}