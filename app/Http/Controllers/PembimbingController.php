<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use App\Models\Eskul;
use App\Models\Jadwal;
use App\Models\AnggotaEskul; // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PembimbingController extends Controller
{
    /**
     * Menampilkan Dashboard Pembimbing dengan Jadwal Eskul yang diampu.
     */
    public function dashboard()
    {
        $idPembimbing = Auth::guard('pembimbing')->id();

        // Cari Eskul yang diampu oleh pembimbing ini
        $eskul = Eskul::where('id_pembimbing', $idPembimbing)->first();

        $jadwal = [];
        $anggota = [];

        if ($eskul) {
            // Ambil jadwal
            $jadwal = Jadwal::where('id_eskul', $eskul->id_eskul)->get();
            
            // Ambil anggota beserta data peserta
            $anggota = AnggotaEskul::with('peserta')
                ->where('id_eskul', $eskul->id_eskul)
                ->get();
        }

        // Perhatikan path 'Pembimbing/Dashboard', pastikan file Vue ada di folder resources/js/Pages/Pembimbing/Dashboard.vue
        // Jika file ada di root Pages, ganti jadi 'Dashboard'
        return Inertia::render('Pembimbing/Dashboard', [
            'eskul'   => $eskul,   // <-- Wajib dikirim agar props.eskul tidak null
            'jadwal'  => $jadwal,
            'anggota' => $anggota, // <-- Dikirim untuk list anggota
        ]);
    }

    // ... method store, update, destroy biarkan tetap sama ...
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => 'required|string|max:50|unique:pembimbing,username',
            'password'     => 'required|string|min:6',
        ]);

        Pembimbing::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'username'     => $validated['username'],
            'password'     => Hash::make($validated['password']),
        ]);

        return back();
    }

    public function update(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'username'     => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('pembimbing')->ignore($pembimbing->id_pembimbing, 'id_pembimbing')
            ],
            'password'     => 'nullable|string|min:6',
        ]);

        $dataToUpdate = [
            'nama_lengkap' => $validated['nama_lengkap'],
            'username'     => $validated['username'],
        ];

        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        $pembimbing->update($dataToUpdate);

        return back();
    }

    public function destroy($id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->delete();

        return back();
    }
}