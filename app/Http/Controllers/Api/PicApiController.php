<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PicApiController extends Controller
{
    private function authorizePic(Request $request): void
    {
        abort_unless($request->user()?->role_id == 3, 403, 'Akses khusus PIC Ruangan.');
    }

    public function dashboard(Request $request)
    {
        $this->authorizePic($request);
        $assigned = Complaint::where('handler_id', $request->user()->id);

        return response()->json([
            'total_tasks' => (clone $assigned)->count(),
            'waiting_tasks' => (clone $assigned)
                ->whereIn('status_repair', ['Belum Diperbaiki', 'Menunggu Spare Part'])
                ->count(),
            'completed_tasks' => (clone $assigned)
                ->where('status_repair', 'Sudah Diperbaiki')
                ->count(),
            'recent_tasks' => $this->assignedComplaints($request)->take(5)->get()
                ->map(fn (Complaint $complaint) => $this->withPhotoUrl($complaint)),
            'unread_notifications' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function complaints(Request $request)
    {
        $this->authorizePic($request);

        return response()->json([
            'complaints' => $this->assignedComplaints($request)->get()
                ->map(fn (Complaint $complaint) => $this->withPhotoUrl($complaint)),
        ]);
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $this->authorizePic($request);
        $complaint = Complaint::where('handler_id', $request->user()->id)
            ->findOrFail($complaint->id);

        if ($complaint->isFinished()) {
            return response()->json([
                'message' => 'Pengaduan ini sudah selesai diperbaiki dan statusnya tidak dapat diubah lagi.',
            ], 422);
        }

        $data = $request->validate([
            'status_repair' => [
                'required',
                Rule::in(['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki']),
            ],
            // Foto bukti: WAJIB jika "Sudah Diperbaiki", DILARANG untuk status lainnya
            'completion_photo_path' => [
                'required_if:status_repair,Sudah Diperbaiki',
                'prohibited_unless:status_repair,Sudah Diperbaiki',
                'nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048',
            ],
        ], [
            'completion_photo_path.required_if'       => 'Foto bukti perbaikan wajib diunggah saat status Sudah Diperbaiki.',
            'completion_photo_path.prohibited_unless' => 'Foto bukti perbaikan hanya boleh diunggah saat status Sudah Diperbaiki.',
        ]);
        $previousPhoto = $complaint->completion_photo_path;
        if ($request->hasFile('completion_photo_path')) {
            $data['completion_photo_path'] = $request->file('completion_photo_path')
                ->store('complaints/completed', 'public');
        }
        $complaint->update($data);
        if (isset($data['completion_photo_path']) && $previousPhoto) {
            Storage::disk('public')->delete($previousPhoto);
        }

        return response()->json([
            'message' => 'Status perbaikan berhasil diperbarui.',
            'complaint' => $this->withPhotoUrl(
                $complaint->fresh()->load(['room:id,room_name', 'reporter:id,fullname'])
            ),
        ]);
    }

    private function assignedComplaints(Request $request)
    {
        return Complaint::with(['room:id,room_name', 'reporter:id,fullname'])
            ->where('handler_id', $request->user()->id)
            ->orderByDesc('updated_at');
    }

    private function withPhotoUrl(Complaint $complaint): array
    {
        return $complaint->toArray() + [
            'photo_url' => $complaint->photo_path
                ? route('public.storage', ['path' => $complaint->photo_path])
                : null,
            'completion_photo_url' => $complaint->completion_photo_path
                ? route('public.storage', ['path' => $complaint->completion_photo_path])
                : null,
        ];
    }
}
