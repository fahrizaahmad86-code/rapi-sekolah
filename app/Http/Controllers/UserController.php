<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        
        if (auth()->user()->role_id != 1) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $users = User::with('role')->get();
        $roles = Role::all();
        return view('waka.user.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role_id != 1) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'fullname'     => 'required|string|max:255',
            'username'     => 'required|string|unique:users,username',
            'password'     => 'required|string|min:6',
            'email'        => 'required|email|unique:users,email',
            'role_id'      => 'required|exists:roles,id'
        ]);

        User::create([
            'fullname'     => $request->fullname,
            'username'     => $request->username,
            'password'     => Hash::make($request->password),
            'email'        => strtolower($request->email),
            'role_id'      => $request->role_id,
        ]);

        return redirect()->back()->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

   
    public function update(Request $request, $id)
    {
        if (auth()->user()->role_id != 1) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'fullname'     => 'required|string|max:255',
            'username'     => 'required|string|unique:users,username,' . $id,
            'email'        => 'required|email|unique:users,email,' . $id,
            'role_id'      => 'required|exists:roles,id'
        ]);

        $data = [
            'fullname'     => $request->fullname,
            'username'     => $request->username,
            'email'        => strtolower($request->email),
            'role_id'      => $request->role_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy($id)
    {
        if (auth()->user()->role_id != 1) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $user = User::findOrFail($id);
        
        if ($user->id == auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
    }
}