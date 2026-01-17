<?php

namespace App\Http\Controllers;

use App\Models\Eskul;
use Illuminate\Http\Request;
use Inertia\Inertia; // Wajib import Inertia untuk method show()\
use App\Models\Pembimbing;

class EskulController extends Controller
{
    /**
     * Menyimpan data eskul baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_eskul'        => 'required|string|max:50',
            'id_pembimbing'     => 'required|exists:pembimbing,id_pembimbing',
            'deskripsi'         => 'nullable|string',
            'jenjang_kelas_min' => 'required|in:1,2,3,4,5,6',
            'jenjang_kelas_max' => 'required|in:1,2,3,4,5,6',
        ]);

        Eskul::create($validated);

        return back();
    }

    /**
     * Menampilkan Detail Eskul (Page Baru).
     */
   public function show($id)
    {
        $eskul = Eskul::with(['pembimbing', 'jadwal', 'anggota_eskul.peserta'])
            ->findOrFail($id);

        // Ambil list pembimbing untuk dropdown edit
        $pembimbings = Pembimbing::orderBy('nama_lengkap', 'asc')->get();

        return Inertia::render('Admin/Eskul/Detail', [
            'eskul' => $eskul,
            'pembimbings' => $pembimbings // Kirim ke Vue
        ]);
    }
    /**
     * Memperbarui data eskul.
     */
    public function update(Request $request, $id)
    {
        $eskul = Eskul::findOrFail($id);

        $validated = $request->validate([
            'nama_eskul'        => 'required|string|max:50',
            'id_pembimbing'     => 'required|exists:pembimbing,id_pembimbing',
            'deskripsi'         => 'nullable|string',
            'jenjang_kelas_min' => 'required|in:1,2,3,4,5,6',
            'jenjang_kelas_max' => 'required|in:1,2,3,4,5,6',
        ]);

        $eskul->update($validated);

        return back();
    }

    /**
     * Menghapus data eskul.
     */
    public function destroy($id)
    {
        $eskul = Eskul::findOrFail($id);
        $eskul->delete();

        // Jika referer berasal dari halaman detail, redirect ke dashboard
        if (request()->headers->get('referer') && str_contains(request()->headers->get('referer'), 'admin/eskul/')) {
            return to_route('admin.dashboard');
        }

        return back();
    }
}