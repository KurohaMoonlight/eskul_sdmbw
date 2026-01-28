<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PembimbingController;
use App\Http\Controllers\EskulController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\AbsensiController; // Import AbsensiController
use App\Http\Controllers\PrestasiController; // <--- PASTIKAN INI ADA
use App\Http\Controllers\NilaiController; // Tambahkan Import Controller Baru
use App\Models\Pembimbing;
use App\Models\Eskul;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Group untuk TAMU (Belum Login)
Route::middleware(['guest:admin,pembimbing'])->group(function () {
    
    Route::get('/login', function () {
        return Inertia::render('Auth/Login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'authenticate']);
});

// 2. Group Khusus ADMIN
Route::middleware(['auth:admin'])->group(function () {
    
    // Dashboard Admin
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard', [
            // Gunakan map untuk memformat last_login
            'pembimbings' => Pembimbing::orderBy('nama_lengkap', 'asc')->get()->map(function ($pembimbing) {
                return [
                    'id_pembimbing' => $pembimbing->id_pembimbing,
                    'nama_lengkap' => $pembimbing->nama_lengkap,
                    'username' => $pembimbing->username,
                    // Format tanggal: "23 Jan 2026 14:30" atau gunakan diffForHumans()
                    'last_login' => $pembimbing->last_login 
                        ? $pembimbing->last_login->format('d M Y H:i') // Contoh: 23 Jan 2026 14:30
                        : null,
                ];
            }),
            'eskuls' => Eskul::with('pembimbing')->get(),
        ]);
    })->name('admin.dashboard');

    // CRUD Pembimbing
    Route::post('/admin/pembimbing', [PembimbingController::class, 'store']);
    Route::put('/admin/pembimbing/{id}', [PembimbingController::class, 'update']);
    Route::delete('/admin/pembimbing/{id}', [PembimbingController::class, 'destroy']);

    // CRUD Eskul
    Route::post('/admin/eskul', [EskulController::class, 'store']);
    Route::get('/admin/eskul/{id}', [EskulController::class, 'show'])->name('admin.eskul.show');
    Route::put('/admin/eskul/{id}', [EskulController::class, 'update']);
    Route::delete('/admin/eskul/{id}', [EskulController::class, 'destroy']);

});

// 3. Group Khusus PEMBIMBING
Route::middleware(['auth:pembimbing'])->group(function () {
    
    Route::get('/pembimbing/dashboard', [PembimbingController::class, 'dashboard'])->name('pembimbing.dashboard');
    
    Route::get('/pembimbing/eskul/{id}', [PembimbingController::class, 'show'])->name('pembimbing.eskul.detail');
});

// 4. Group AKSES BERSAMA (Admin & Pembimbing)
// Route ini bisa diakses oleh siapa saja yang login sebagai admin ATAU pembimbing
Route::middleware(['auth:admin,pembimbing'])->group(function () {
    
    // CRUD Jadwal
    Route::post('/admin/jadwal', [JadwalController::class, 'store']);
    Route::put('/admin/jadwal/{id}', [JadwalController::class, 'update']);
    Route::delete('/admin/jadwal/{id}', [JadwalController::class, 'destroy']);

    // CRUD Anggota
    Route::post('/admin/anggota', [AnggotaController::class, 'store']);
    Route::put('/admin/anggota/{id}', [AnggotaController::class, 'update']);
    Route::delete('/admin/anggota/{id}', [AnggotaController::class, 'destroy']);

    // CRUD Kegiatan
    Route::post('/admin/kegiatan', [KegiatanController::class, 'store']);
    Route::put('/admin/kegiatan/{id}', [KegiatanController::class, 'update']);
    Route::delete('/admin/kegiatan/{id}', [KegiatanController::class, 'destroy']);

    // Route Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Absensi
    Route::post('/admin/absensi', [AbsensiController::class, 'store']);
    Route::get('/admin/absensi/print', [AbsensiController::class, 'exportExcel'])->name('absensi.print');

    // CRUD Prestasi (Route Baru)
    Route::post('/admin/prestasi', [PrestasiController::class, 'store']);
    Route::put('/admin/prestasi/{id}', [PrestasiController::class, 'update']);
    Route::delete('/admin/prestasi/{id}', [PrestasiController::class, 'destroy']);

    // CRUD Nilai (Route Baru)
    Route::post('/admin/nilai/generate', [NilaiController::class, 'generate'])->name('nilai.generate');
    Route::put('/admin/nilai/update-bulk', [NilaiController::class, 'updateBulk'])->name('nilai.update_bulk');
    Route::get('/admin/nilai/export', [NilaiController::class, 'exportExcel'])->name('nilai.export');
    Route::post('/admin/nilai/sync-daily', [NilaiController::class, 'syncFromDaily'])->name('nilai.sync');
});


// 5. Redirect root '/'
Route::get('/', function () {
    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    if (Auth::guard('pembimbing')->check()) {
        return redirect()->route('pembimbing.dashboard');
    }
    return redirect()->route('login');
});