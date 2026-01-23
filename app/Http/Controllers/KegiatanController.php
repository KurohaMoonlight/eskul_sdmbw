<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Http\Requests\StoreKegiatanRequest;
use App\Http\Requests\UpdateKegiatanRequest;
use App\Services\KegiatanService;
use Illuminate\Http\RedirectResponse;

class KegiatanController extends Controller
{
    protected $kegiatanService;

    public function __construct(KegiatanService $kegiatanService)
    {
        $this->kegiatanService = $kegiatanService;
    }

    /**
     * Menyimpan data kegiatan baru.
     */
    public function store(StoreKegiatanRequest $request): RedirectResponse
    {
        $this->kegiatanService->createKegiatan($request->validated());

        return back()->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    /**
     * Memperbarui data kegiatan.
     */
    public function update(UpdateKegiatanRequest $request, $id): RedirectResponse
    {
        $kegiatan = Kegiatan::findOrFail($id);
        
        $this->kegiatanService->updateKegiatan($kegiatan, $request->validated());

        return back()->with('success', 'Kegiatan berhasil diperbarui.');
    }

    /**
     * Menghapus data kegiatan.
     */
    public function destroy($id): RedirectResponse
    {
        $kegiatan = Kegiatan::findOrFail($id);
        
        $this->kegiatanService->deleteKegiatan($kegiatan);

        return back()->with('success', 'Kegiatan berhasil dihapus.');
    }
}2