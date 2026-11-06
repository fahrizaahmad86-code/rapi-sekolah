<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfilController extends Controller
{
    /** Halaman profil milik user yang sedang login (semua role). */
    public function show()
    {
        $user = auth()->user()->load('role');

        return view('profil.index', compact('user'));
    }

    /** Ubah data diri: nama lengkap, username, email. Role tidak bisa diubah dari sini. */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
        ], [
            'fullname.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username ini sudah dipakai pengguna lain.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email ini sudah dipakai pengguna lain.',
        ]);

        $user->update([
            'fullname' => $validated['fullname'],
            'username' => $validated['username'],
            'email'    => strtolower($validated['email']),
        ]);

        return redirect()->route('profil.show')->with('success', 'Data profil berhasil diperbarui.');
    }
}
