<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnggotaRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan membuat request ini.
     */
    public function authorize(): bool
    {
        return true; // Izinkan semua user yang sudah lolos middleware auth
    }

    /**
     * Aturan validasi untuk menyimpan anggota baru.
     */
    public function rules(): array
    {
        return [
            'nama_lengkap'  => 'required|string|max:100',
            'tingkat_kelas' => 'required|string|max:20',
            'jenis_kelamin' => 'required|in:L,P',
            'id_eskul'      => 'required|exists:eskul,id_eskul',
            'tahun_ajaran'  => 'required|string|max:10',
        ];
    }
}