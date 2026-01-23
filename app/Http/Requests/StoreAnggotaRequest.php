<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnggotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_eskul' => ['required', 'exists:eskul,id_eskul'],
            'id_peserta' => ['required', 'exists:peserta,id_peserta'],
            'tahun_ajaran' => ['required', 'string', 'max:10'],
            'status_aktif' => ['boolean'],
        ];
    }
}