<?php

namespace App\Http\Controllers;

use App\Models\Eskul;
use Illuminate\Http\Request;
use Inertia\Inertia; 
use App\Models\Pembimbing;
use Illuminate\Support\Facades\DB;

class EskulController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_eskul'        => 'required|string|max:50',
            'pembimbings'       => 'required|array|min:1', // Validasi Array
            'pembimbings.*'     => 'exists:pembimbing,id_pembimbing',
            'deskripsi'         => 'nullable|string',
            'jenjang_kelas_min' => 'required|in:1,2,3,4,5,6',
            'jenjang_kelas_max' => 'required|in:1,2,3,4,5,6',
        ]);

        DB::transaction(function () use ($validated) {
            $eskul = Eskul::create([
                'nama_eskul' => $validated['nama_eskul'],
                'deskripsi' => $validated['deskripsi'],
                'jenjang_kelas_min' => $validated['jenjang_kelas_min'],
                'jenjang_kelas_max' => $validated['jenjang_kelas_max'],
            ]);

            // Gunakan sync untuk menyimpan relasi many-to-many
            $eskul->pembimbings()->sync($validated['pembimbings']);
        });

        return back();
    }

   public function show($id)
    {
        // PERBAIKAN: Gunakan 'pembimbings' (plural)
        $eskul = Eskul::with(['pembimbings', 'jadwal', 'anggota_eskul.peserta'])
            ->findOrFail($id);

        $pembimbings = Pembimbing::orderBy('nama_lengkap', 'asc')->get();

        return Inertia::render('Admin/Eskul/Detail', [
            'eskul' => $eskul,
            'pembimbings' => $pembimbings 
        ]);
    }

    public function update(Request $request, $id)
    {
        $eskul = Eskul::findOrFail($id);

        $validated = $request->validate([
            'nama_eskul'        => 'required|string|max:50',
            'pembimbings'       => 'required|array|min:1',
            'pembimbings.*'     => 'exists:pembimbing,id_pembimbing',
            'deskripsi'         => 'nullable|string',
            'jenjang_kelas_min' => 'required|in:1,2,3,4,5,6',
            'jenjang_kelas_max' => 'required|in:1,2,3,4,5,6',
        ]);

        DB::transaction(function () use ($eskul, $validated) {
            $eskul->update([
                'nama_eskul' => $validated['nama_eskul'],
                'deskripsi' => $validated['deskripsi'],
                'jenjang_kelas_min' => $validated['jenjang_kelas_min'],
                'jenjang_kelas_max' => $validated['jenjang_kelas_max'],
            ]);

            // Sync relasi pembimbing
            $eskul->pembimbings()->sync($validated['pembimbings']);
        });

        return back();
    }

    public function destroy($id)
    {
        $eskul = Eskul::findOrFail($id);
        $eskul->delete();

        if (request()->headers->get('referer') && str_contains(request()->headers->get('referer'), 'admin/eskul/')) {
            return to_route('admin.dashboard');
        }

        return back();
    }
}