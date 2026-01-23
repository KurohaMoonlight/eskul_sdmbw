<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJadwalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id_eskul' => ['required', 'exists:eskul,id_eskul'],
            // Terima array ['Senin', 'Selasa'] dari Vue
            'hari' => ['required', 'array'], 
            'hari.*' => ['string'], 
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'lokasi' => ['required', 'string', 'max:100'],
            'kelas_min' => ['required', 'integer', 'min:1', 'max:6'],
            'kelas_max' => ['required', 'integer', 'min:1', 'max:6', 'gte:kelas_min'],
        ];
    }
}