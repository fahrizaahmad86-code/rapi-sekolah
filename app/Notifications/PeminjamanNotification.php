<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PeminjamanNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Event $event,
        private string $title,
        private string $message
    ) {
    }

    public static function statusChanged(Event $event): self
    {
        $status = $event->status === 'Approved' ? 'disetujui' : 'ditolak';
        $message = 'Peminjaman kegiatan "' . $event->event_name . '" telah ' . $status . '.';

        if ($event->status === 'Rejected' && $event->move_reason) {
            $message .= ' Alasan: ' . $event->move_reason;
        }

        return new self($event, 'Status Peminjaman Ruangan', $message);
    }

    public static function moved(Event $event): self
    {
        $event->loadMissing('room');
        $roomName = $event->room->room_name ?? 'ruangan baru';
        $message = 'Peminjaman kegiatan "' . $event->event_name . '" dipindahkan ke '
            . $roomName . ', ' . \App\Support\SesiPeminjaman::labelDenganJam((int) $event->start_session, (int) $event->end_session) . '.';

        if ($event->move_reason) {
            $message .= ' Alasan: ' . $event->move_reason;
        }

        return new self($event, 'Peminjaman Ruangan Dipindahkan', $message);
    }

    public function via($notifiable): array
    {
        return $notifiable->role_id == 1 ? [] : ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'event_id' => $this->event->id,
        ];
    }
}
