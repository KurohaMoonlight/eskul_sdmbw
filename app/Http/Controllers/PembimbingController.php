<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use App\Models\Eskul;
use App\Models\Jadwal;
use App\Models\AnggotaEskul;
use App\Models\Kegiatan; // Tambahkan Import Model Kegiatan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PembimbingController extends Controller
{
    /**
     * Menampilkan Dashboard Pembimbing.
     */
    public function dashboard()
    {
        $idPembimbing = Auth::guard('pembimbing')->id();
        $listEskul = Eskul::where('id_pembimbing', $idPembimbing)->get();

        return Inertia::render('Pembimbing/Dashboard', [
            'eskul_list' => $listEskul
        ]);
    }

    /**
     * Menampilkan Detail Eskul (Jadwal, Anggota, & Kegiatan).
     */
    public function show($id)
    {
        $idPembimbing = Auth::guard('pembimbing')->id();

        // 1. Cari Eskul (Pastikan milik pembimbing yang login)
        $eskul = Eskul::where('id_eskul', $id)
                      ->where('id_pembimbing', $idPembimbing)
                      ->firstOrFail();

        // 2. Ambil Jadwal
        $jadwal = Jadwal::where('id_eskul', $eskul->id_eskul)->get();

        // 3. Ambil Anggota + Data Peserta
        $anggota = AnggotaEskul::with('peserta')
                    ->where('id_eskul', $eskul->id_eskul)
                    ->get();

        // 4. Ambil Kegiatan (Terbaru diatas)
        $kegiatan = Kegiatan::where('id_eskul', $eskul->id_eskul)
                    ->orderBy('tanggal', 'desc')
                    ->get();

        return Inertia::render('Pembimbing/EskulCardDetail', [
            'eskul'    => $eskul,
            'jadwal'   => $jadwal,
            'anggota'  => $anggota,
            'kegiatan' => $kegiatan, // Kirim data kegiatan ke Vue
        ]);
    }

    // --- Method CRUD Pembimbing (jika diperlukan untuk manajemen user) ---

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