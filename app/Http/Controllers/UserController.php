<?php

namespace App\Http\Controllers;

use App\Exports\TemplateGuruExport;
use App\Imports\GuruImport;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        
        if (auth()->user()->role_id != 1) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        $keyword = $request->get('q');

        $users = User::with('role')
         ->when($keyword, function ($query, $keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('fullname', 'like', "%{$keyword}%")
                  ->orWhere('username', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        })
        ->orderBy('fullname', 'asc')
        ->get();

        $roles = Role::all();
        return view('waka.user.index', compact('users', 'roles', 'keyword'));
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
            'phone_number' => $this->phoneRules(),
            'role_id'      => 'required|exists:roles,id'
        ], [
            'phone_number.required_if' => 'Nomor WhatsApp wajib diisi untuk PIC Ruangan.',
            'phone_number.regex'       => 'Nomor WhatsApp tidak valid. Contoh: 081234567890 atau +6281234567890.',
        ]);

        User::create([
            'fullname'     => $request->fullname,
            'username'     => $request->username,
            'password'     => Hash::make($request->password),
            'email'        => strtolower($request->email),
            'phone_number' => $request->phone_number,
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
            'phone_number' => $this->phoneRules(),
            'role_id'      => 'required|exists:roles,id'
        ], [
            'phone_number.required_if' => 'Nomor WhatsApp wajib diisi untuk PIC Ruangan.',
            'phone_number.regex'       => 'Nomor WhatsApp tidak valid. Contoh: 081234567890 atau +6281234567890.',
        ]);

        $data = [
            'fullname'     => $request->fullname,
            'username'     => $request->username,
            'email'        => strtolower($request->email),
            'phone_number' => $request->phone_number,
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

    public function importGuru(Request $request)
    {
        if (auth()->user()->role_id != 1) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'file_guru' => ['required', 'file', 'mimes:xlsx,csv', 'max:2048'],
        ], [
            'file_guru.required' => 'Pilih file data guru terlebih dahulu.',
            'file_guru.mimes' => 'File harus berformat .xlsx atau .csv.',
            'file_guru.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        $import = new GuruImport();
        $import->import(
            $validated['file_guru']->getRealPath(),
            $validated['file_guru']->getClientOriginalExtension()
        );

        $redirect = redirect()->back();

        if ($import->berhasil > 0) {
            $redirect->with('success', "{$import->berhasil} guru berhasil diimpor.");
        }

        if ($import->gagal !== []) {
            $redirect->with('import_errors', $import->gagal);
            if ($import->berhasil === 0) {
                $redirect->with('error', 'Tidak ada data yang berhasil diimpor.');
            }
        }

        return $redirect;
    }

    public function templateGuru()
    {
        if (auth()->user()->role_id != 1) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $path = (new TemplateGuruExport())->downloadPath();

        return response()->download($path, 'template-import-guru.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Nomor WhatsApp: opsional untuk semua role, WAJIB untuk PIC Ruangan (role 3).
     * Boleh diketik 08..., +628..., atau 628... (spasi/strip diabaikan).
     */
    private function phoneRules(): array
    {
        return [
            'required_if:role_id,3',
            'nullable',
            'regex:/^(\+62|62|0)\s*8[0-9\s\-]{7,15}$/',
        ];
    }
}
