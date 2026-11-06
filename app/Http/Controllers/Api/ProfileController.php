<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['user' => $request->user()->load('role')]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'fullname.required' => 'Nama lengkap wajib diisi.',
            'username.unique' => 'Username ini sudah dipakai pengguna lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai pengguna lain.',
        ]);

        $data['email'] = strtolower($data['email']);
        $user->update($data);

        return response()->json([
            'message' => 'Data profil berhasil diperbarui.',
            'user' => $user->fresh()->load('role'),
        ]);
    }
}