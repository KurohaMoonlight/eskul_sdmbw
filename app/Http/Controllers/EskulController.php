<?php

namespace App\Http\Controllers;

use App\Models\Eskul;
use App\Http\Requests\StoreEskulRequest;
use App\Http\Requests\UpdateEskulRequest;
use App\Services\EskulService;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class EskulController extends Controller
{
    protected $eskulService;

    public function __construct(EskulService $eskulService)
    {
        $this->eskulService = $eskulService;
    }

    /**
     * Menyimpan data eskul baru.
     */
    public function store(StoreEskulRequest $request): RedirectResponse
    {
        $this->eskulService->createEskul($request->validated());

        return back()->with('success', 'Eskul berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail eskul (Admin View).
     */
    public function show($id)
    {
        $data = $this->eskulService->getEskulDetail($id);

        return Inertia::render('Admin/Eskul/Detail', $data);
    }

    /**
     * Memperbarui data eskul.
     */
    public function update(UpdateEskulRequest $request, $id): RedirectResponse
    {
        $eskul = Eskul::findOrFail($id);
        $this->eskulService->updateEskul($eskul, $request->validated());

        return back()->with('success', 'Data eskul diperbarui.');
    }

    /**
     * Menghapus data eskul.
     */
    public function destroy($id): RedirectResponse
    {
        $eskul = Eskul::findOrFail($id);
        $this->eskulService->deleteEskul($eskul);

        return back()->with('success', 'Eskul dihapus.');
    }
}