<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_name',
        'capacity',
        'location',
        'pic_id',
        'is_active'
    ];

    // 3. Relasi ke tabel Users (Siapa Penanggung Jawab / PIC ruangan ini)
    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    // 4. Relasi ke tabel Events (Satu ruangan bisa memiliki banyak jadwal kegiatan)
    public function events()
    {
        return $this->hasMany(Event::class, 'room_id');
    }

    // 5. Relasi ke tabel Complaints (Satu ruangan bisa memiliki banyak laporan kerusakan)
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'room_id');
    }
}