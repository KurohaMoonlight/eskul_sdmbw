<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\AnggotaEskul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnggotaController extends Controller
{
    /**
     * Menyimpan Peserta Baru sekaligus mendaftarkan ke Eskul
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap'  => 'required|string|max:100',
            'tingkat_kelas' => 'required|string|max:20',
            'jenis_kelamin' => 'required|in:L,P',
            'id_eskul'      => 'required|exists:eskul,id_eskul',
            'tahun_ajaran'  => 'required|string|max:10',
        ]);

        DB::transaction(function () use ($request) {
            $peserta = Peserta::create([
                'nama_lengkap'  => $request->nama_lengkap,
                'tingkat_kelas' => $request->tingkat_kelas,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            AnggotaEskul::create([
                'id_eskul'     => $request->id_eskul,
                'id_peserta'   => $peserta->id_peserta,
                'tahun_ajaran' => $request->tahun_ajaran,
                'status_aktif' => true,
            ]);
        });

        return back();
    }

    /**
     * Update Data Anggota & Peserta
     */
    public function update(Request $request, $id)
    {
        $anggota = AnggotaEskul::findOrFail($id);
        
        $request->validate([
            'nama_lengkap'  => 'required|string|max:100',
            'tingkat_kelas' => 'required|string|max:20',
            'jenis_kelamin' => 'required|in:L,P',
            'tahun_ajaran'  => 'required|string|max:10',
            'status_aktif'  => 'required|boolean',
        ]);

        DB::transaction(function () use ($request, $anggota) {
            // 1. Update Data Peserta (Master Siswa)
            $anggota->peserta->update([
                'nama_lengkap'  => $request->nama_lengkap,
                'tingkat_kelas' => $request->tingkat_kelas,
                'jenis_kelamin' => $request->jenis_kelamin,
            ]);

            // 2. Update Data Keanggotaan
            $anggota->update([
                'tahun_ajaran' => $request->tahun_ajaran,
                'status_aktif' => $request->status_aktif,
            ]);
        });

        return back();
    }

    /**
     * Hapus Anggota dari Eskul DAN Hapus Data Peserta
     */
    public function destroy($id)
    {
        $anggota = AnggotaEskul::findOrFail($id);
        
        // Ambil data peserta terkait sebelum menghapus anggota
        $peserta = $anggota->peserta;

        DB::transaction(function () use ($anggota, $peserta) {
            // Hapus data keanggotaan dulu (karena ada foreign key constraint)
            $anggota->delete();

            // Hapus data master peserta jika ada
            if ($peserta) {
                $peserta->delete();
            }
        });

        return back();
    }
}