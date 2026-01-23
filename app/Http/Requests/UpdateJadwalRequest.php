<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ubah jadi true agar bisa dipakai
    }

    public function rules(): array
    {
        return [
            'id_eskul'    => 'required|exists:eskul,id_eskul',
            'hari'        => 'required|array',
            'hari.*'      => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'lokasi'      => 'nullable|string|max:100',
            'kelas_min'   => 'required|integer|min:1|max:6',
            'kelas_max'   => 'required|integer|min:1|max:6|gte:kelas_min', // Validasi tambahan: max >= min
        ];
    }
}