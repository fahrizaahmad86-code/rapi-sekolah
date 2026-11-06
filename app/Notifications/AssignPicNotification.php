<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssignPicNotification extends Notification
{
    use Queueable;

    protected $complaint;

    public function __construct($complaint)
    {
        $this->complaint = $complaint;
    }

    public function via($notifiable)
    {
        return ['database']; 
    }

    public function toArray($notifiable)
    {
        return [
            'title'        => 'Tugas Perbaikan Baru',
            'message'      => 'Anda ditugaskan membenarkan fasilitas di ' . ($this->complaint->room->room_name ?? 'Ruangan'),
            'complaint_id' => $this->complaint->id,
        ];
    }
}