<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Menangani proses login user.
     */
    public function authenticate(Request $request)
    {
        // 1. Validasi Input
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 2. Cek Login sebagai ADMIN
        // Fungsi attempt() otomatis mengenkripsi password input dengan Bcrypt
        // dan mencocokkannya dengan hash yang ada di tabel admin.
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        // 3. Cek Login sebagai PEMBIMBING (Jika Admin Gagal)
        // Sama, attempt() otomatis menangani verifikasi Bcrypt untuk pembimbing.
        if (Auth::guard('pembimbing')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('pembimbing.dashboard'));
        }

        // 4. Jika keduanya gagal, lempar error validasi
        throw ValidationException::withMessages([
            'username' => 'Username atau password salah.',
        ]);
    }

    /**
     * Menangani proses logout user.
     */
    public function logout(Request $request)
    {
        // Cek guard mana yang sedang login, lalu logout
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        } elseif (Auth::guard('pembimbing')->check()) {
            Auth::guard('pembimbing')->logout();
        }

        // Hapus sesi agar aman
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Kembali ke halaman login
        return redirect()->route('login');
    }
}