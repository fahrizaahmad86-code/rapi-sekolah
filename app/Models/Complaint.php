<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\AssignPicNotification;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'reporter_id',
        'handler_id',
        'description',
        'photo_path',
        'completion_photo_path',
        'status_repair',
        'reported_at'
    ];

    /**
     * Saat pengaduan dibuat, PIC otomatis diambil dari data ruangan yang dilaporkan,
     * lalu PIC tersebut dikirimi notifikasi tugas. Jika ruangan belum punya PIC,
     * handler_id dibiarkan kosong.
     */
    protected static function booted()
    {
        static::creating(function (Complaint $complaint) {
            if (empty($complaint->handler_id) && $complaint->room_id) {
                $picId = Room::where('id', $complaint->room_id)->value('pic_id');
                $complaint->handler_id = $picId
                    ? User::where('id', $picId)->where('role_id', 3)->value('id')
                    : null;
            }
        });

        static::created(function (Complaint $complaint) {
            if ($complaint->handler_id && ($pic = User::find($complaint->handler_id))) {
                $complaint->loadMissing('room');
                $pic->notify(new AssignPicNotification($complaint));
            }
        });
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handler_id');
    }

    /**
     * Pengaduan dianggap selesai (terkunci) jika statusnya "Sudah Diperbaiki"
     * DAN foto bukti perbaikan sudah dikirim. Setelah itu status tidak boleh diubah lagi.
     */
    public function isFinished(): bool
    {
        return $this->status_repair === 'Sudah Diperbaiki' && !empty($this->completion_photo_path);
    }
}
