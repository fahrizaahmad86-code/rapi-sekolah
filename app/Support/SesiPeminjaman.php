<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Jadwal sesi peminjaman ruangan (1 sesi = 40 menit, ada jeda istirahat).
 * Ubah isi konstanta SESI di bawah jika jadwal sekolah berubah.
 */
class SesiPeminjaman
{
    public const JUMLAH = 10;

    /** nomor sesi => [jam mulai, jam selesai] */
    public const SESI = [
        1  => ['07:15', '07:55'],
        2  => ['07:55', '08:35'],
        3  => ['08:35', '09:15'],
        4  => ['09:30', '10:10'],
        5  => ['10:10', '10:50'],
        6  => ['10:50', '11:30'],
        7  => ['12:30', '13:10'],
        8  => ['13:10', '13:50'],
        9  => ['13:50', '14:30'],
        10 => ['14:30', '15:10'],
    ];

    /** Jam mulai sebuah sesi, format H:i:s */
    public static function mulai(int $sesi): string
    {
        return self::SESI[$sesi][0] . ':00';
    }

    /** Jam selesai sebuah sesi, format H:i:s */
    public static function selesai(int $sesi): string
    {
        return self::SESI[$sesi][1] . ':00';
    }

    /** "07:15 - 07:55" */
    public static function jam(int $sesi): string
    {
        return self::SESI[$sesi][0] . ' - ' . self::SESI[$sesi][1];
    }

    /** Teks untuk dropdown: "Sesi 1 (07:15-07:55)" */
    public static function labelDropdown(int $sesi): string
    {
        return "Sesi {$sesi} (" . self::SESI[$sesi][0] . '-' . self::SESI[$sesi][1] . ')';
    }

    /** Teks tampilan: "Sesi 1" atau "Sesi 1 - Sesi 2" */
    public static function label(?int $mulai, ?int $selesai): ?string
    {
        if (!$mulai || !$selesai) {
            return null;
        }
        return $mulai === $selesai ? "Sesi {$mulai}" : "Sesi {$mulai} - Sesi {$selesai}";
    }

    /** Teks lengkap dengan jam: "Sesi 1 - Sesi 2 (07:15-08:35)" */
    public static function labelDenganJam(?int $mulai, ?int $selesai): ?string
    {
        if (!$mulai || !$selesai) {
            return null;
        }
        return self::label($mulai, $selesai) . ' (' . self::SESI[$mulai][0] . '-' . self::SESI[$selesai][1] . ')';
    }

    /** Daftar semua sesi untuk dikirim ke JavaScript / dropdown. */
    public static function daftar(): array
    {
        $hasil = [];
        foreach (self::SESI as $no => [$mulai, $selesai]) {
            $hasil[] = [
                'no'      => $no,
                'mulai'   => $mulai,
                'selesai' => $selesai,
                'label'   => self::labelDropdown($no),
            ];
        }
        return $hasil;
    }

    /**
     * Apakah sesi pada tanggal tertentu sudah terlewat (jam mulainya sudah lewat
     * dari waktu sekarang)? Sesi yang sudah berjalan juga dianggap terlewat
     * sehingga tidak bisa dipinjam lagi.
     */
    public static function sudahLewat(string $tanggal, int $sesi): bool
    {
        if (!isset(self::SESI[$sesi])) {
            return true;
        }
        $mulai = Carbon::parse($tanggal)->setTimeFromTimeString(self::mulai($sesi));
        return $mulai->lessThanOrEqualTo(Carbon::now());
    }
}
