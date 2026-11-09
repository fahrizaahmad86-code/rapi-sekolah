<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'user_id',
        'event_name',
        'agenda_type',
        'date',
        'start_session',
        'end_session',
        'document_path',
        'status',
        'move_reason'
    ];

    public const TIPE_INTERNAL = 'internal';
    public const TIPE_KHUSUS   = 'khusus';
    public const TIPE_UMUM     = 'umum';
    public const TIPE_GURU     = 'guru';

    public const JENIS_AGENDA = [
        self::TIPE_INTERNAL => [
            'label'      => 'Internal',
            'warna'      => '#2563EB', 
            'keterangan' => 'Terbatas, hanya kelompok peminjam yang terlibat',
            'contoh'     => 'Kelas meminjam ruang lain karena butuh proyektor',
        ],
        self::TIPE_KHUSUS => [
            'label'      => 'Khusus',
            'warna'      => '#16A34A', 
            'keterangan' => 'Seluruh siswa terlibat',
            'contoh'     => 'Classmeet',
        ],
        self::TIPE_GURU => [
            'label'      => 'Guru',
            'warna'      => '#7C3AED', 
            'keterangan' => 'Khusus guru',
            'contoh'     => 'Rapat guru / workshop guru',
        ],
        self::TIPE_UMUM => [
            'label'      => 'Umum',
            'warna'      => '#F97316', 
            'keterangan' => 'Seluruh warga sekolah terlibat',
            'contoh'     => 'Upacara Hari Pahlawan',
        ],
    ];

    public function getJenisAttribute(): array
    {
        return self::JENIS_AGENDA[$this->agenda_type] ?? self::JENIS_AGENDA[self::TIPE_INTERNAL];
    }

    public function getWarnaAttribute(): string
    {
        return $this->jenis['warna'];
    }

    public function getLabelJenisAttribute(): string
    {
        return $this->jenis['label'];
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}