<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Menyimpan jadwal baru (Support Multi-Hari / Checklist).
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_eskul'    => 'required|exists:eskul,id_eskul',
            'hari'        => 'required|array', // Menerima array karena checklist
            'hari.*'      => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'lokasi'      => 'nullable|string|max:100',
            'kelas_min'   => 'required|integer',
            'kelas_max'   => 'required|integer',
        ]);

        // Loop setiap hari yang dicentang untuk membuat baris data terpisah
        foreach ($request->hari as $h) {
            Jadwal::create([
                'id_eskul'    => $request->id_eskul,
                'hari'        => $h,
                'jam_mulai'   => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'lokasi'      => $request->lokasi,
                'kelas_min'   => $request->kelas_min,
                'kelas_max'   => $request->kelas_max,
            ]);
        }

        return back();
    }

    /**
     * Update jadwal (Hanya update 1 baris).
     */
    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);

        $request->validate([
            'hari'        => 'required|array',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'lokasi'      => 'nullable|string|max:100',
            'kelas_min'   => 'required|integer',
            'kelas_max'   => 'required|integer',
        ]);

        // Ambil hari pertama dari array checklist (karena update per ID row)
        $hariDipilih = $request->hari[0] ?? $jadwal->hari;

        $jadwal->update([
            'hari'        => $hariDipilih,
            'jam_mulai'   => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'lokasi'      => $request->lokasi,
            'kelas_min'   => $request->kelas_min,
            'kelas_max'   => $request->kelas_max,
        ]);

        return back();
    }

    /**
     * Hapus jadwal.
     */
    public function destroy($id)
    {
        Jadwal::findOrFail($id)->delete();
        return back();
    }
}