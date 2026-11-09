<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Room;
use App\Models\User;
use App\Notifications\AssignPicNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        }

        // Filter status perbaikan (?status=...). Nilai di luar daftar diabaikan = tampil semua.
        $daftarStatus = ['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'];
        $filter = $request->query('status');
        if (!in_array($filter, $daftarStatus, true)) {
            $filter = null;
        }

        // Jumlah laporan per status (selalu dari seluruh data, supaya angka di tombol filter tetap utuh)
        $jumlah = Complaint::selectRaw('status_repair, COUNT(*) as total')
            ->groupBy('status_repair')
            ->pluck('total', 'status_repair');

        $pengaduans = Complaint::with(['room', 'reporter', 'handler'])
            ->when($filter, function ($query) use ($filter) {
                $query->where('status_repair', $filter);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        $ruangans = Room::all();
        $pics = User::where('role_id', 3)->orderBy('fullname')->get();
        
        return view('waka.pengaduan.index', compact('pengaduans', 'ruangans', 'pics', 'daftarStatus', 'filter', 'jumlah'));
    }

    public function store(Request $request)
    {
        if (!\in_array(auth()->user()->getAttribute('role_id'), [1, 2, 4])) {
            return redirect()->back()->with('error', 'Akses ditolak. Anda tidak memiliki izin mengirim pengaduan.');
        }

        $validated = $request->validate([
            'room_id'     => 'required|exists:rooms,id',
            'description' => 'required|string',
            'photo_path'  => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ], [
            'room_id.required'     => 'Ruangan wajib dipilih.',
            'description.required' => 'Deskripsi kerusakan wajib diisi.',
            'photo_path.required'  => 'Foto bukti kerusakan wajib diunggah.',
            'photo_path.image'     => 'File harus berupa gambar.',
            'photo_path.mimes'     => 'Format foto harus JPG atau PNG.',
            'photo_path.max'       => 'Ukuran foto maksimal 2MB.',
        ]);

        $fotoPath = $request->file('photo_path')->store('complaints', 'public');

        $complaint = Complaint::create([
            'room_id'       => $validated['room_id'],
            'reporter_id'   => auth()->id(), 
            'description'   => $validated['description'],
            'photo_path'    => $fotoPath,
            'status_repair' => 'Belum Diperbaiki' 
        ]);

        $pesan = $complaint->handler_id
            ? 'Laporan kerusakan berhasil dikirim dan otomatis diteruskan ke PIC ruangan.'
            : 'Laporan kerusakan berhasil dikirim. Ruangan ini belum punya PIC, hubungi Waka Sarpras.';

        return redirect()->back()->with('success', $pesan);
    }

    public function update(Request $request, $id)
    {
        $roleId = (int) auth()->user()->role_id;
        if ($roleId === 1) {
            $complaint = Complaint::findOrFail($id);
        } elseif ($roleId === 3) {
            $complaint = Complaint::where('handler_id', auth()->id())->findOrFail($id);
        } else {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah data ini.');
        }

        if ($roleId === 1) {
            $validated = $request->validate([
                'handler_id' => ['required', Rule::exists('users', 'id')->where('role_id', 3)],
            ], [
                'handler_id.required' => 'PIC wajib dipilih.',
                'handler_id.exists' => 'PIC yang dipilih tidak valid.',
            ]);

            $handlerChanged = (int) $complaint->handler_id !== (int) $validated['handler_id'];
            $complaint->update(['handler_id' => $validated['handler_id']]);

            if ($handlerChanged) {
                $complaint->loadMissing('room');
                User::findOrFail($validated['handler_id'])->notify(new AssignPicNotification($complaint));
            }

            return redirect()->back()->with('success', 'PIC pengaduan berhasil ditugaskan.');
        }

        if ($roleId === 3) {
            // Sudah diperbaiki + bukti sudah dikirim -> status terkunci
            if ($complaint->isFinished()) {
                return redirect()->back()->with('error', 'Pengaduan ini sudah selesai diperbaiki dan statusnya tidak dapat diubah lagi.');
            }

            // Foto bukti: WAJIB jika status "Sudah Diperbaiki", DILARANG untuk status lainnya
            $validated = $request->validate([
                'status_repair'         => 'required|in:Belum Diperbaiki,Menunggu Spare Part,Sudah Diperbaiki',
                'completion_photo_path' => [
                    'required_if:status_repair,Sudah Diperbaiki',
                    'prohibited_unless:status_repair,Sudah Diperbaiki',
                    'nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048',
                ],
            ], [
                'completion_photo_path.required_if'       => 'Foto bukti perbaikan wajib diunggah saat status Sudah Diperbaiki.',
                'completion_photo_path.prohibited_unless' => 'Foto bukti perbaikan hanya boleh diunggah saat status Sudah Diperbaiki.',
                'completion_photo_path.image'             => 'File bukti perbaikan harus berupa gambar.',
                'completion_photo_path.mimes'             => 'Format foto harus JPG atau PNG.',
                'completion_photo_path.max'               => 'Ukuran foto maksimal 2MB.',
            ]);

            $data = ['status_repair' => $validated['status_repair']];

            if ($request->hasFile('completion_photo_path')) {
                $previousPhoto = $complaint->getAttribute('completion_photo_path');
                $data['completion_photo_path'] = $request->file('completion_photo_path')->store('complaints_fixed', 'public');
                if ($previousPhoto) {
                    Storage::disk('public')->delete($previousPhoto);
                }
            }

            $complaint->update($data);

            return redirect()->back()->with('success', 'Status perbaikan berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah data ini.');
    }

    public function destroy($id)
    {
        if (auth()->user()->getAttribute('role_id') != 1) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $complaint = Complaint::findOrFail($id);
        $complaint->delete();

        return redirect()->back()->with('success', 'Laporan kerusakan berhasil dihapus.');
    }
}