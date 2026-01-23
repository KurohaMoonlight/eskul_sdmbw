<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEskulRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_eskul'        => 'required|string|max:50',
            'id_pembimbing'     => 'nullable|exists:pembimbing,id_pembimbing',
            'deskripsi'         => 'nullable|string',
            'jenjang_kelas_min' => 'nullable|in:1,2,3,4,5,6',
            'jenjang_kelas_max' => 'nullable|in:1,2,3,4,5,6',
        ];
    }
}