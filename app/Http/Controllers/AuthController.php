<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validasi inputan
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // Cek apakah username dan password cocok dengan di database
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Cek role_id untuk mengarahkan ke halaman yang sesuai
            $role_id = Auth::user()->role_id;

            if ($role_id == 1) {
                return redirect()->intended('/waka/dashboard'); // Waka/Admin
            } elseif ($role_id == 2) {
                return redirect()->intended('/guru/dashboard'); // Guru
            } elseif ($role_id == 3) {
                return redirect()->intended('/pic/dashboard'); // PIC Ruang
            } elseif ($role_id == 4) {
                return redirect()->intended('/organisasi/dashboard'); // Organisasi
            } else {
                return redirect()->intended('/'); // Guest/Siswa diarahkan ke Kalender Publik
            }
        }

        // Jika login gagal, kembalikan ke halaman login dengan pesan error
        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    // Memproses Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}