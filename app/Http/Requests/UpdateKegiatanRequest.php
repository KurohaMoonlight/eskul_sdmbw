<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal'            => 'required|date',
            'materi_kegiatan'    => 'required|string',
            'catatan_pembimbing' => 'nullable|string',
        ];
    }
}