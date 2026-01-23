<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnggotaRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk memperbarui data anggota.
     */
    public function rules(): array
    {
        return [
            'nama_lengkap'  => 'required|string|max:100',
            'tingkat_kelas' => 'required|string|max:20',
            'jenis_kelamin' => 'required|in:L,P',
            'tahun_ajaran'  => 'required|string|max:10',
            'status_aktif'  => 'required|boolean',
        ];
    }
}