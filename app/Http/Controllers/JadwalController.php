<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Http\Requests\StoreJadwalRequest;
use App\Http\Requests\UpdateJadwalRequest;
use App\Services\JadwalService;
use Illuminate\Http\RedirectResponse;

class JadwalController extends Controller
{
    protected $jadwalService;

    // Inject Service melalui Constructor
    public function __construct(JadwalService $jadwalService)
    {
        $this->jadwalService = $jadwalService;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJadwalRequest $request): RedirectResponse
    {
        // Data sudah tervalidasi otomatis oleh StoreJadwalRequest
        $this->jadwalService->createJadwal($request->validated());

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJadwalRequest $request, Jadwal $jadwal): RedirectResponse
    {
        // Menggunakan Route Model Binding (Jadwal $jadwal), tidak perlu findOrFail manual
        // Pastikan route parameter di web.php konsisten (misal: /jadwal/{jadwal}) 
        // Jika di route pakai {id}, ubah parameter jadi ($id) lalu findOrFail di dalam.
        // Tapi best practice Laravel modern pakai Model Binding.
        
        // Asumsi route: Route::put('/admin/jadwal/{id}', ...) -> maka parameter di sini ($id)
        // Jika ingin pakai Model Binding, ubah route jadi: Route::put('/admin/jadwal/{jadwal}', ...)
        
        // KARENA ROUTE ANDA: Route::put('/admin/jadwal/{id}'...), SAYA GUNAKAN CARA MANUAL:
        
        // $jadwal = Jadwal::findOrFail($request->route('id')); // Atau terima $id di parameter
        
        $this->jadwalService->updateJadwal($jadwal, $request->validated());

        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }
    
    // Versi method update jika parameter route-nya adalah $id (integer)
    /*
    public function update(UpdateJadwalRequest $request, $id): RedirectResponse
    {
        $jadwal = Jadwal::findOrFail($id);
        $this->jadwalService->updateJadwal($jadwal, $request->validated());
        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }
    */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $jadwal = Jadwal::findOrFail($id);
        $this->jadwalService->deleteJadwal($jadwal);

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }
}