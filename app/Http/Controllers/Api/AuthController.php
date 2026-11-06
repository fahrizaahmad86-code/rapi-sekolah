<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'message' => 'Username atau password salah'
            ], 401);
        }

        $user = User::where('username', $request->username)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token'  => $token,
            'user'   => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()?->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}