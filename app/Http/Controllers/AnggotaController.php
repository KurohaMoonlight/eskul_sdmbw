<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnggotaRequest;
use App\Http\Requests\UpdateAnggotaRequest;
use App\Services\AnggotaService;
use App\Models\AnggotaEskul;
use Illuminate\Http\RedirectResponse;

class AnggotaController extends Controller
{
    protected $anggotaService;

    public function __construct(AnggotaService $anggotaService)
    {
        $this->anggotaService = $anggotaService;
    }

    /**
     * Simpan anggota baru.
     */
    public function store(StoreAnggotaRequest $request): RedirectResponse
    {
        $this->anggotaService->createAnggota($request->validated());

        // Redirect back() akan otomatis me-refresh halaman Detail Eskul
        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    /**
     * Update data anggota.
     */
    public function update(UpdateAnggotaRequest $request, $id): RedirectResponse
    {
        $anggota = AnggotaEskul::findOrFail($id);
        $this->anggotaService->updateAnggota($anggota, $request->validated());

        return back()->with('success', 'Data anggota diperbarui.');
    }

    /**
     * Hapus anggota (keluarkan siswa dari eskul).
     */
    public function destroy($id): RedirectResponse
    {
        $anggota = AnggotaEskul::findOrFail($id);
        $this->anggotaService->deleteAnggota($anggota);

        return back()->with('success', 'Anggota berhasil dikeluarkan.');
    }
}