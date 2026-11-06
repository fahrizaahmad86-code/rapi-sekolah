<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Pengaturan sesi peminjaman ruangan.
 * Sesi 1 mulai pukul JAM_MULAI, tiap sesi berdurasi DURASI_MENIT, berurutan tanpa jeda.
 * Contoh (default): Sesi 1 = 07:00-07:45, Sesi 2 = 07:45-08:30, ... Sesi 10 = 13:45-14:30.
 * Ubah konstanta di bawah ini jika jadwal sekolah berbeda.
 */
class SesiPeminjaman
{
    public const JUMLAH       = 10;
    public const DURASI_MENIT = 45;
    public const JAM_MULAI    = '07:00';

    /** Jam mulai sebuah sesi, format H:i:s */
    public static function mulai(int $sesi): string
    {
        return Carbon::createFromFormat('H:i', self::JAM_MULAI)
            ->addMinutes(($sesi - 1) * self::DURASI_MENIT)
            ->format('H:i:s');
    }

    /** Jam selesai sebuah sesi, format H:i:s */
    public static function selesai(int $sesi): string
    {
        return Carbon::createFromFormat('H:i', self::JAM_MULAI)
            ->addMinutes($sesi * self::DURASI_MENIT)
            ->format('H:i:s');
    }

    /** Teks tampilan: "Sesi 1" atau "Sesi 1 - Sesi 2" */
    public static function label(?int $mulai, ?int $selesai): ?string
    {
        if (!$mulai || !$selesai) {
            return null;
        }
        return $mulai === $selesai ? "Sesi {$mulai}" : "Sesi {$mulai} - Sesi {$selesai}";
    }
}
