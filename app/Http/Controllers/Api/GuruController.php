<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    private function authorizeGuru(Request $request): void
    {
        abort_unless(in_array((int) $request->user()?->role_id, [2, 4], true), 403, 'Akses khusus Guru atau Organisasi.');
    }

    public function dashboard(Request $request)
    {
        $this->authorizeGuru($request);

        return response()->json([
            'notifications' => Event::with('room:id,room_name')
                ->where('user_id', $request->user()->id)
                ->whereNotNull('move_reason')
                ->orderByDesc('updated_at')
                ->get([
                    'id',
                    'room_id',
                    'event_name',
                    'date',
                    'start_session',
                    'end_session',
                    'status',
                    'move_reason',
                    'updated_at',
                ]),
            'pending_events' => Event::where('user_id', $request->user()->id)
                ->where('status', 'Pending')
                ->count(),
            'my_complaints' => Complaint::where('reporter_id', $request->user()->id)->count(),
            'unread_notifications' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function events(Request $request)
    {
        $this->authorizeGuru($request);

        return response()->json([
            'events' => Event::with('room:id,room_name')
                ->where('user_id', $request->user()->id)
                ->orderByDesc('date')
                ->orderByDesc('start_session')
                ->get()
                ->map(fn (Event $event) => $event->toArray() + [
                    'document_url' => $event->document_path ? url('storage/'.$event->document_path) : null,
                ]),
            'rooms' => Room::where('is_active', true)
                ->orderBy('room_name')
                ->get(['id', 'room_name', 'capacity']),
        ]);
    }

    public function storeEvent(Request $request)
    {
        $this->authorizeGuru($request);
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'event_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'start_session' => ['required', 'integer', 'min:1', 'max:10'],
            'end_session' => ['required', 'integer', 'min:1', 'max:10', 'gte:start_session'],
            'document_path' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $conflictExists = Event::where('room_id', $data['room_id'])
            ->where('date', $data['date'])
            ->where('status', '!=', 'Rejected')
            ->where('start_session', '<=', $data['end_session'])
            ->where('end_session', '>=', $data['start_session'])
            ->exists();
        if ($conflictExists) {
            return response()->json([
                'message' => 'Ruangan sudah dipinjam atau diajukan pada sesi tersebut.',
            ], 409);
        }

        $data['user_id'] = $request->user()->id;
        $data['document_path'] = $request->file('document_path')->store('peminjaman', 'public');
        $data['status'] = 'Pending';
        $event = Event::create($data);

        return response()->json([
            'message' => 'Pengajuan peminjaman berhasil dikirim.',
            'event' => $event->load('room:id,room_name'),
        ], 201);
    }

    public function destroyEvent(Request $request, Event $event)
    {
        $this->authorizeGuru($request);
        abort_unless($event->user_id === $request->user()->id, 403, 'Anda hanya dapat menghapus pengajuan sendiri.');
        $event->delete();

        return response()->json(['message' => 'Pengajuan peminjaman berhasil dihapus.']);
    }

    public function complaints(Request $request)
    {
        $this->authorizeGuru($request);

        return response()->json([
            'complaints' => Complaint::with(['room:id,room_name', 'handler:id,fullname'])
                ->where('reporter_id', $request->user()->id)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (Complaint $complaint) => $complaint->toArray() + [
                    'photo_url' => $complaint->photo_path ? url('storage/'.$complaint->photo_path) : null,
                    'completion_photo_url' => $complaint->completion_photo_path
                        ? url('storage/'.$complaint->completion_photo_path)
                        : null,
                ]),
            'rooms' => Room::where('is_active', true)
                ->orderBy('room_name')
                ->get(['id', 'room_name']),
        ]);
    }

    public function storeComplaint(Request $request)
    {
        $this->authorizeGuru($request);
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'description' => ['required', 'string'],
            'photo_path' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);
        $data['reporter_id'] = $request->user()->id;
        $data['photo_path'] = $request->file('photo_path')->store('complaints', 'public');
        $data['status_repair'] = 'Belum Diperbaiki';
        $complaint = Complaint::create($data);

        return response()->json([
            'message' => 'Laporan kerusakan berhasil dikirim.',
            'complaint' => $complaint->load('room:id,room_name'),
        ], 201);
    }
}
