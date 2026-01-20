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
        // 1. Validasi Input Utama
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // 2. Ambil Nilai Checkbox 'Remember Me'
        // $request->boolean() akan mengonversi input menjadi true/false.
        // Jika checkbox tidak dicentang (tidak dikirim), otomatis dianggap false.
        $remember = $request->boolean('remember');

        // 3. Cek Login sebagai ADMIN
        // Parameter kedua ($remember) dimasukkan ke fungsi attempt
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        // 4. Cek Login sebagai PEMBIMBING
        // Parameter kedua ($remember) dimasukkan ke fungsi attempt
        if (Auth::guard('pembimbing')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('pembimbing.dashboard'));
        }

        // 5. Jika Login Gagal
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
