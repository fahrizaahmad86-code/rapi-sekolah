<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Room;
use App\Support\SesiPeminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomSearchController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(
            in_array((int) $request->user()?->role_id, [1, 2, 4], true),
            403,
            'Anda tidak punya akses ke menu Cari Ruangan.'
        );

        $filters = $request->validate([
            'date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['semua', 'kosong', 'dipinjam'])],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $date = Carbon::parse($filters['date'] ?? Carbon::today())->toDateString();
        $status = $filters['status'] ?? 'semua';
        $keyword = trim($filters['q'] ?? '');

        $rooms = Room::with('pic:id,fullname')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('room_name', 'like', "%{$keyword}%")
                        ->orWhere('location', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('room_name')
            ->get();

        $schedules = Event::with('user:id,fullname')
            ->whereDate('date', $date)
            ->where('status', '!=', 'Rejected')
            ->whereIn('room_id', $rooms->pluck('id'))
            ->orderBy('start_session')
            ->get()
            ->groupBy('room_id');

        $roomData = $rooms->map(function (Room $room) use ($schedules) {
            $roomSchedules = $schedules->get($room->id, collect());
            $sessions = collect(range(1, SesiPeminjaman::JUMLAH))->map(
                function (int $number) use ($roomSchedules) {
                    $event = $roomSchedules->first(
                        fn (Event $event) => $event->start_session <= $number
                            && $event->end_session >= $number
                    );

                    return [
                        'number' => $number,
                        'status' => $event?->status,
                        'time' => substr(SesiPeminjaman::mulai($number), 0, 5)
                            .' - '.substr(SesiPeminjaman::selesai($number), 0, 5),
                    ];
                }
            );

            return [
                'id' => $room->id,
                'room_name' => $room->room_name,
                'capacity' => $room->capacity,
                'location' => $room->location,
                'is_active' => (bool) $room->is_active,
                'pic' => $room->pic ? [
                    'id' => $room->pic->id,
                    'fullname' => $room->pic->fullname,
                ] : null,
                'kosong' => $room->is_active && $roomSchedules->isEmpty(),
                'sessions' => $sessions,
                'schedules' => $roomSchedules->map(fn (Event $event) => [
                    'id' => $event->id,
                    'event_name' => $event->event_name,
                    'status' => $event->status,
                    'start_session' => $event->start_session,
                    'end_session' => $event->end_session,
                    'user' => $event->user ? ['fullname' => $event->user->fullname] : null,
                ])->values(),
            ];
        });

        $summary = [
            'total' => $roomData->count(),
            'kosong' => $roomData->where('kosong', true)->count(),
            'dipinjam' => $roomData->where('kosong', false)->count(),
        ];

        if ($status === 'kosong') {
            $roomData = $roomData->where('kosong', true)->values();
        } elseif ($status === 'dipinjam') {
            $roomData = $roomData->where('kosong', false)->values();
        }

        return response()->json([
            'date' => $date,
            'status' => $status,
            'query' => $keyword,
            'summary' => $summary,
            'rooms' => $roomData->values(),
        ]);
    }
}