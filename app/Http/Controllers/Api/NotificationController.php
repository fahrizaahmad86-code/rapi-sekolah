<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'unread_count' => $notifications->count(),
            'notifications' => $notifications->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notifikasi',
                'message' => $notification->data['message'] ?? '',
                'created_at' => $notification->created_at,
                'event_id' => $notification->data['event_id'] ?? null,
                'complaint_id' => $notification->data['complaint_id'] ?? null,
            ])->values(),
        ]);
    }

    public function markRead(Request $request, string $notification)
    {
        $record = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();
        $record->markAsRead();

        return response()->json([
            'message' => 'Notifikasi ditandai sudah dibaca.',
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}