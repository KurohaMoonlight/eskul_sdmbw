<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKegiatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_eskul'           => 'required|exists:eskul,id_eskul',
            'tanggal'            => 'required|date',
            'materi_kegiatan'    => 'required|string',
            'catatan_pembimbing' => 'nullable|string',
        ];
    }
}