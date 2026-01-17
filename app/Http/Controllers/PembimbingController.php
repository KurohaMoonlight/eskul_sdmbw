<?php

namespace App\Http\Controllers;

use App\Models\Pembimbing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PembimbingController extends Controller
{
    /**
     * Menyimpan data pembimbing baru.
     */
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
            'password'     => Hash::make($validated['password']), // Hash password
        ]);

        return back();
    }

    /**
     * Memperbarui data pembimbing.
     */
    public function update(Request $request, $id)
    {
        $pembimbing = Pembimbing::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            // Validasi unik kecuali untuk diri sendiri (id_pembimbing)
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

        // Hanya update password jika input tidak kosong
        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        $pembimbing->update($dataToUpdate);

        return back();
    }

    /**
     * Menghapus data pembimbing.
     */
    public function destroy($id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $pembimbing->delete();

        return back();
    }
}