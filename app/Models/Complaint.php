<?php

namespace App\Models;

use App\Notifications\AssignPicNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'last_progress_at',
        'reported_at'
    ];

    protected $casts = [
        'last_progress_at' => 'datetime',
    ];

    
    public const BATAS_HARI_RESPON = 3;

    protected static function booted(): void
    {
        static::creating(function (Complaint $complaint) {
            if (empty($complaint->handler_id) && $complaint->room_id) {
                $picId = Room::whereKey($complaint->room_id)->value('pic_id');
                $complaint->handler_id = $picId
                    ? User::whereKey($picId)->where('role_id', 3)->value('id')
                    : null;
            }

            if ($complaint->handler_id) {
                $complaint->last_progress_at = now();
            }
        });

        static::created(function (Complaint $complaint) {
            if ($complaint->handler_id && ($pic = User::find($complaint->handler_id))) {
                $pic->notify(new AssignPicNotification($complaint->loadMissing('room')));
            }
        });

        // Berlaku untuk web maupun API mobile: setiap PIC ditugaskan/diganti
        // atau status perbaikan berubah, hitungan 3 hari dimulai ulang.
        static::updating(function (Complaint $complaint) {
            if ($complaint->isDirty(['handler_id', 'status_repair'])) {
                $complaint->last_progress_at = now();
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

    /** Titik awal hitungan: perubahan status terakhir (atau penugasan PIC). */
    public function progressSince(): \Illuminate\Support\Carbon
    {
        return $this->last_progress_at ?? $this->updated_at ?? $this->created_at;
    }

    /**
     * Tombol WhatsApp untuk Waka muncul jika PIC sudah ditugaskan dan status
     * masih "Belum Diperbaiki" selama >= 3 hari (tanpa perubahan status).
     */
    public function isOverdue(): bool
    {
        if ($this->handler_id === null || $this->status_repair !== 'Belum Diperbaiki') {
            return false;
        }

        $lastProgressTimestamp = $this->progressSince()->getTimestamp();
        $overdueTimestamp = now()->subDays(self::BATAS_HARI_RESPON)->getTimestamp();

        return $lastProgressTimestamp <= $overdueTimestamp;
    }
}
