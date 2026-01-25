<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    /**
     * Menyimpan data kegiatan baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_eskul'           => 'required|exists:eskul,id_eskul',
            'tanggal'            => 'required|date',
            'materi_kegiatan'    => 'required|string',
            'catatan_pembimbing' => 'nullable|string',
        ]);

        Kegiatan::create($validated);

        return back();
    }

    /**
     * Memperbarui data kegiatan.
     */
    public function update(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);

        $validated = $request->validate([
            'tanggal'            => 'required|date',
            'materi_kegiatan'    => 'required|string',
            'catatan_pembimbing' => 'nullable|string',
        ]);

        $kegiatan->update($validated);

        return back();
    }

    /**
     * Menghapus data kegiatan.
     */
    public function destroy($id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $kegiatan->delete();

        return back();
    }
}