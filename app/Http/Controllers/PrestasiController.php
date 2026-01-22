<?php

namespace App\Http\Controllers;

use App\Models\Prestasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PrestasiController extends Controller
{
    /**
     * Menyimpan data prestasi baru
     * Mendukung multi-peserta (Tim) dengan memecah string id_peserta
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_eskul'      => 'required|exists:eskul,id_eskul',
            // id_peserta bisa string "1,2,3" atau null
            'id_peserta'    => 'nullable', 
            'nama_lomba'    => 'required|string|max:150',
            'tingkat'       => 'required|in:Kecamatan,Kabupaten,Provinsi,Nasional',
            'juara_ke'      => 'required|string|max:50',
            'tanggal_lomba' => 'required|date',
            'foto_prestasi' => 'nullable|image|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('foto_prestasi')) {
            $path = $request->file('foto_prestasi')->store('prestasi', 'public');
        }

        // Pecah string ID peserta menjadi array
        // Jika null/kosong, jadikan array dengan satu elemen null
        $pesertaIds = $request->id_peserta ? explode(',', $request->id_peserta) : [null];

        DB::transaction(function () use ($validated, $pesertaIds, $path) {
            foreach ($pesertaIds as $idPeserta) {
                // Bersihkan ID dari spasi
                $cleanId = trim($idPeserta);
                
                // Jika ID kosong (misal input kosong) dan ada lebih dari 1 input, skip
                // Tapi jika hanya ada 1 input dan kosong (prestasi tim tanpa nama), tetap simpan sebagai null
                if ($cleanId === '' && count($pesertaIds) > 1) continue; 

                Prestasi::create([
                    'id_eskul'      => $validated['id_eskul'],
                    'id_peserta'    => $cleanId ?: null, // Simpan null jika string kosong
                    'nama_lomba'    => $validated['nama_lomba'],
                    'tingkat'       => $validated['tingkat'],
                    'juara_ke'      => $validated['juara_ke'],
                    'tanggal_lomba' => $validated['tanggal_lomba'],
                    'foto_prestasi' => $path,
                ]);
            }
        });

        return back()->with('success', 'Prestasi berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $prestasi = Prestasi::findOrFail($id);

        $validated = $request->validate([
            'id_peserta'    => 'nullable', // Saat edit, biasanya hanya edit satu siswa
            'nama_lomba'    => 'required|string|max:150',
            'tingkat'       => 'required|in:Kecamatan,Kabupaten,Provinsi,Nasional',
            'juara_ke'      => 'required|string|max:50',
            'tanggal_lomba' => 'required|date',
            'foto_prestasi' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto_prestasi')) {
            if ($prestasi->foto_prestasi) {
                Storage::disk('public')->delete($prestasi->foto_prestasi);
            }
            $validated['foto_prestasi'] = $request->file('foto_prestasi')->store('prestasi', 'public');
        }

        // Khusus Edit: Kita tidak mengubah banyak peserta sekaligus karena id_prestasi merujuk ke satu baris.
        // Ambil ID pertama saja jika frontend mengirim string koma
        $pesertaIds = $request->id_peserta ? explode(',', $request->id_peserta) : [null];
        $validated['id_peserta'] = trim($pesertaIds[0]) ?: null;

        $prestasi->update($validated);

        return back()->with('success', 'Data prestasi diperbarui.');
    }

    public function destroy($id)
    {
        $prestasi = Prestasi::findOrFail($id);
        if ($prestasi->foto_prestasi) {
            Storage::disk('public')->delete($prestasi->foto_prestasi);
        }
        $prestasi->delete();
        return back()->with('success', 'Prestasi dihapus.');
    }
}