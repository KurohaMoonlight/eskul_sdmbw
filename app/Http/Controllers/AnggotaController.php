<?php

namespace App\Http\Controllers;

use App\Models\AnggotaEskul;
use App\Http\Requests\StoreAnggotaRequest;
use App\Http\Requests\UpdateAnggotaRequest;
use App\Services\AnggotaService;
use Illuminate\Http\RedirectResponse;

class AnggotaController extends Controller
{
    protected $anggotaService;

    /**
     * Inject AnggotaService melalui constructor.
     * Dependency Injection ini membuat controller lebih testable dan decoupled.
     */
    public function __construct(AnggotaService $anggotaService)
    {
        $this->anggotaService = $anggotaService;
    }

    /**
     * Menyimpan data anggota baru.
     * * @param StoreAnggotaRequest $request Request yang sudah tervalidasi otomatis
     * @return RedirectResponse
     */
    public function store(StoreAnggotaRequest $request): RedirectResponse
    {
        // Panggil service untuk menangani logika simpan
        $this->anggotaService->createAnggota($request->validated());

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    /**
     * Memperbarui data anggota.
     * * @param UpdateAnggotaRequest $request Request validasi update
     * @param int $id ID Anggota Eskul
     * @return RedirectResponse
     */
    public function update(UpdateAnggotaRequest $request, $id): RedirectResponse
    {
        $anggota = AnggotaEskul::findOrFail($id);
        
        // Panggil service untuk menangani logika update
        $this->anggotaService->updateAnggota($anggota, $request->validated());

        return back()->with('success', 'Data anggota berhasil diperbarui.');
    }

    /**
     * Menghapus data anggota.
     * * @param int $id ID Anggota Eskul
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        $anggota = AnggotaEskul::findOrFail($id);

        // Panggil service untuk menangani logika hapus
        $this->anggotaService->deleteAnggota($anggota);

        return back()->with('success', 'Anggota berhasil dihapus.');
    }
}