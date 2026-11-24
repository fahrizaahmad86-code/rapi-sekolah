<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Notifications\AssignPicNotification;
use App\Notifications\PeminjamanNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class WakaController extends Controller
{
    private function authorizeWaka(Request $request): void
    {
        abort_unless($request->user()?->role_id == 1, 403, 'Akses khusus Waka Sarpras.');
    }

    public function dashboard(Request $request)
    {
        $this->authorizeWaka($request);

        return response()->json([
            'users' => User::count(),
            'rooms' => Room::count(),
            'pending_events' => Event::where('status', 'Pending')->count(),
            'open_complaints' => Complaint::where('status_repair', '!=', 'Sudah Diperbaiki')->count(),
        ]);
    }

    public function users(Request $request)
    {
        $this->authorizeWaka($request);

        return response()->json([
            'users' => User::with('role')->orderBy('fullname')->get(),
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    public function storeUser(Request $request)
    {
        $this->authorizeWaka($request);
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);
        $data['email'] = strtolower($data['email']);
        $data['password'] = Hash::make($data['password']);

        return response()->json([
            'message' => 'Pengguna berhasil ditambahkan.',
            'user' => User::create($data)->load('role'),
        ], 201);
    }

    public function updateUser(Request $request, User $user)
    {
        $this->authorizeWaka($request);
        $data = $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role_id' => ['required', 'exists:roles,id'],
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $data['email'] = strtolower($data['email']);
        $user->update($data);

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui.',
            'user' => $user->fresh()->load('role'),
        ]);
    }

    public function destroyUser(Request $request, User $user)
    {
        $this->authorizeWaka($request);
        abort_if($user->is($request->user()), 422, 'Akun yang sedang digunakan tidak dapat dihapus.');
        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }

    public function rooms(Request $request)
    {
        $this->authorizeWaka($request);

        return response()->json([
            'rooms' => Room::with('pic:id,fullname')->orderBy('room_name')->get(),
            'pics' => User::where('role_id', 3)->orderBy('fullname')->get(['id', 'fullname']),
        ]);
    }

    public function storeRoom(Request $request)
    {
        $this->authorizeWaka($request);
        $data = $request->validate([
            'room_name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'location' => ['required', 'string', 'max:255'],
            'pic_id' => ['nullable', Rule::exists('users', 'id')->where('role_id', 3)],
        ]);
        $room = Room::create($data + ['is_active' => true]);

        return response()->json([
            'message' => 'Ruangan berhasil ditambahkan.',
            'room' => $room->load('pic:id,fullname'),
        ], 201);
    }

    public function updateRoom(Request $request, Room $room)
    {
        $this->authorizeWaka($request);
        $data = $request->validate([
            'room_name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'location' => ['required', 'string', 'max:255'],
            'pic_id' => ['nullable', Rule::exists('users', 'id')->where('role_id', 3)],
        ]);
        $room->update($data);

        return response()->json([
            'message' => 'Data ruangan berhasil diperbarui.',
            'room' => $room->fresh()->load('pic:id,fullname'),
        ]);
    }

    public function destroyRoom(Request $request, Room $room)
    {
        $this->authorizeWaka($request);
        abort_if($room->events()->exists() || $room->complaints()->exists(), 422, 'Ruangan masih memiliki agenda atau pengaduan.');
        $room->delete();

        return response()->json(['message' => 'Ruangan berhasil dihapus.']);
    }

    public function events(Request $request)
    {
        $this->authorizeWaka($request);

        return response()->json([
            'events' => Event::with(['room:id,room_name', 'user:id,fullname'])
                ->orderByDesc('date')
                ->orderByDesc('start_session')
                ->get()
                ->map(fn (Event $event) => $event->toArray() + [
                    'document_url' => $event->document_path ? url('storage/'.$event->document_path) : null,
                ]),
            'rooms' => Room::where('is_active', true)->orderBy('room_name')->get(['id', 'room_name', 'capacity']),
        ]);
    }

    public function storeEvent(Request $request)
    {
        $this->authorizeWaka($request);
        $data = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'event_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'start_session' => ['required', 'integer', 'min:1', 'max:10'],
            'end_session' => ['required', 'integer', 'min:1', 'max:10', 'gte:start_session'],
            'document_path' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'new_room_id' => ['nullable', 'exists:rooms,id'],
            'move_reason' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($request, $data) {
            $conflict = Event::where('room_id', $data['room_id'])
                ->where('date', $data['date'])
                ->where('status', '!=', 'Rejected')
                ->where('start_session', '<=', $data['end_session'])
                ->where('end_session', '>=', $data['start_session'])
                ->lockForUpdate()
                ->first();

            if ($conflict) {
                if (empty($data['new_room_id']) || empty($data['move_reason'])) {
                    abort(422, 'Ruangan bentrok. Pilih ruangan pengganti dan isi alasan pemindahan.');
                }
                if ((int) $data['new_room_id'] === (int) $data['room_id']) {
                    abort(422, 'Ruangan pengganti harus berbeda dari ruangan yang sedang bentrok.');
                }
                $replacementBusy = Event::where('room_id', $data['new_room_id'])
                    ->where('date', $data['date'])
                    ->where('status', '!=', 'Rejected')
                    ->where('id', '!=', $conflict->id)
                    ->where('start_session', '<=', $conflict->end_session)
                    ->where('end_session', '>=', $conflict->start_session)
                    ->exists();
                if ($replacementBusy) {
                    abort(422, 'Ruangan pengganti juga bentrok pada sesi tersebut.');
                }
                $conflict->update([
                    'room_id' => $data['new_room_id'],
                    'move_reason' => $data['move_reason'],
                ]);
                $conflict->refresh()->load('room');
                $conflict->user->notify(PeminjamanNotification::moved($conflict));
            }

            $data['document_path'] = $request->file('document_path')->store('peminjaman', 'public');
            $data['user_id'] = $request->user()->id;
            $data['status'] = 'Approved';
            unset($data['new_room_id']);
            unset($data['move_reason']);
            $event = Event::create($data);

            return response()->json([
                'message' => $conflict
                    ? 'Agenda disetujui dan jadwal yang bentrok dipindahkan.'
                    : 'Agenda Waka berhasil dibuat dan disetujui.',
                'event' => $event->load(['room:id,room_name', 'user:id,fullname']),
            ], 201);
        });
    }

    public function updateEvent(Request $request, Event $event)
    {
        $this->authorizeWaka($request);
        $data = $request->validate([
            'status' => ['required', Rule::in(['Pending', 'Approved', 'Rejected'])],
            'room_id' => ['required', 'exists:rooms,id'],
            'start_session' => ['required', 'integer', 'min:1', 'max:10'],
            'end_session' => ['required', 'integer', 'min:1', 'max:10', 'gte:start_session'],
            'move_reason' => ['nullable', 'string'],
        ]);
        if (
            ($event->room_id != $data['room_id']
                || $event->start_session != $data['start_session']
                || $event->end_session != $data['end_session'])
            && empty(trim($data['move_reason'] ?? ''))
        ) {
            abort(422, 'Isi alasan jika ruangan atau sesi kegiatan dipindahkan.');
        }
        $wasMoved = (int) $event->room_id !== (int) $data['room_id']
            || (int) $event->start_session !== (int) $data['start_session']
            || (int) $event->end_session !== (int) $data['end_session'];
        $statusChanged = $event->status !== $data['status'];

        // Status yang sudah Approved bersifat final: tidak boleh diubah kembali ke Pending/Rejected
        if ($event->status === 'Approved' && $data['status'] !== 'Approved') {
            abort(422, 'Peminjaman yang sudah disetujui (Approved) tidak dapat diubah kembali ke Pending atau Rejected.');
        }

        if ($data['status'] === 'Approved') {
            $approvedConflict = Event::where('room_id', $data['room_id'])
                ->where('date', $event->date)
                ->where('id', '!=', $event->id)
                ->where('status', 'Approved')
                ->where('start_session', '<=', $data['end_session'])
                ->where('end_session', '>=', $data['start_session'])
                ->exists();
            if ($approvedConflict) {
                abort(422, 'Ruangan dan sesi tujuan sudah digunakan oleh kegiatan yang disetujui.');
            }
            $conflictingPendingEvents = Event::with('user')
                ->where('room_id', $data['room_id'])
                ->where('date', $event->date)
                ->where('id', '!=', $event->id)
                ->where('status', 'Pending')
                ->where('start_session', '<=', $data['end_session'])
                ->where('end_session', '>=', $data['start_session'])
                ->get();

            foreach ($conflictingPendingEvents as $conflictingPendingEvent) {
                $conflictingPendingEvent->update([
                    'status' => 'Rejected',
                    'move_reason' => 'Dibatalkan otomatis karena jadwal lain telah disetujui oleh Waka/Admin',
                ]);
                $conflictingPendingEvent->user->notify(
                    PeminjamanNotification::statusChanged($conflictingPendingEvent)
                );
            }
        }
        $event->update($data);
        $event->refresh()->load('room');
        if ($wasMoved) {
            $event->user->notify(PeminjamanNotification::moved($event));
        }
        if ($statusChanged && in_array($event->status, ['Approved', 'Rejected'], true)) {
            $event->user->notify(PeminjamanNotification::statusChanged($event));
        }

        return response()->json([
            'message' => 'Status peminjaman berhasil divalidasi.',
            'event' => $event->fresh()->load(['room:id,room_name', 'user:id,fullname']),
        ]);
    }

    public function destroyEvent(Request $request, Event $event)
    {
        $this->authorizeWaka($request);
        $event->delete();

        return response()->json(['message' => 'Peminjaman berhasil dihapus.']);
    }

    public function complaints(Request $request)
    {
        $this->authorizeWaka($request);

        return response()->json([
            'complaints' => Complaint::with([
                'room:id,room_name',
                'reporter:id,fullname',
                'handler:id,fullname',
            ])->orderByDesc('created_at')->get()->map(fn (Complaint $complaint) => $complaint->toArray() + [
                'photo_url' => $complaint->photo_path ? url('storage/'.$complaint->photo_path) : null,
                'completion_photo_url' => $complaint->completion_photo_path
                    ? url('storage/'.$complaint->completion_photo_path)
                    : null,
            ]),
            'rooms' => Room::orderBy('room_name')->get(['id', 'room_name']),
            'pics' => User::where('role_id', 3)->orderBy('fullname')->get(['id', 'fullname']),
        ]);
    }

    public function storeComplaint(Request $request)
    {
        $this->authorizeWaka($request);
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
            'complaint' => $complaint->load(['room:id,room_name', 'reporter:id,fullname']),
        ], 201);
    }

    public function assignComplaint(Request $request, Complaint $complaint)
    {
        $this->authorizeWaka($request);
        $data = $request->validate([
            'handler_id' => ['required', Rule::exists('users', 'id')->where('role_id', 3)],
        ]);
        $complaint->update([
            'handler_id' => $data['handler_id'],
            'status_repair' => 'Belum Diperbaiki',
        ]);
        $pic = User::findOrFail($data['handler_id']);
        $pic->notify(new AssignPicNotification($complaint->load('room')));

        return response()->json([
            'message' => 'PIC ruangan berhasil ditugaskan.',
            'complaint' => $complaint->fresh()->load(['room:id,room_name', 'reporter:id,fullname', 'handler:id,fullname']),
        ]);
    }

    public function destroyComplaint(Request $request, Complaint $complaint)
    {
        $this->authorizeWaka($request);
        $complaint->delete();

        return response()->json(['message' => 'Pengaduan berhasil dihapus.']);
    }
}
