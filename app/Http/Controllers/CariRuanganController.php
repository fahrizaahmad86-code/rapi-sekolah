<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Room;
use App\Support\SesiPeminjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CariRuanganController extends Controller
{
    /**
     * Role yang boleh memakai menu ini:
     * 1 = Waka Sarpras, 2 = Guru, 4 = Organisasi (OSIS).
     */
    private const ROLE_BOLEH = [1, 2, 4];

    public function index(Request $request)
    {
        if (!in_array((int) auth()->user()->role_id, self::ROLE_BOLEH, true)) {
            abort(403, 'Anda tidak punya akses ke menu Cari Ruangan.');
        }

        $request->validate([
            'tanggal'       => 'nullable|date',
            'status'        => 'nullable|in:semua,kosong,dipinjam',
            'q'             => 'nullable|string|max:100',
        ]);

        // ---- Nilai filter (ada nilai bawaan supaya halaman langsung berguna) ----
        $tanggal      = $request->input('tanggal') ?: Carbon::today()->toDateString();
        $status       = $request->input('status', 'semua');
        $keyword      = trim((string) $request->input('q', ''));

        // ---- Ambil ruangan (filter nama/lokasi dilakukan di database) ----
        $rooms = Room::with('pic')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('room_name', 'like', "%{$keyword}%")
                      ->orWhere('location', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('room_name')
            ->get();

        // ---- Ambil semua jadwal pada tanggal itu (Ditolak tidak dihitung, sama seperti aturan bentrok di peminjaman) ----
        $jadwalPerRuangan = Event::with('user:id,fullname')
            ->where('date', $tanggal)
            ->where('status', '!=', 'Rejected')
            ->whereIn('room_id', $rooms->pluck('id'))
            ->orderBy('start_session')
            ->get()
            ->groupBy('room_id');

        // ---- Hitung keadaan tiap ruangan ----
        $rooms = $rooms->map(function ($room) use ($jadwalPerRuangan) {
            $jadwal = $jadwalPerRuangan->get($room->id, collect());

            // Peta 10 sesi: kosong / Pending / Approved
            $peta = [];
            for ($s = 1; $s <= SesiPeminjaman::JUMLAH; $s++) {
                $pakai = $jadwal->first(function ($event) use ($s) {
                    return $event->start_session <= $s && $event->end_session >= $s;
                });
                $peta[$s] = $pakai ? $pakai->status : null;
            }

            $room->jadwal_hari_ini = $jadwal;
            $room->peta_sesi       = $peta;
            // Kosong = aktif dan tidak ada peminjaman sama sekali pada tanggal itu
            $room->kosong          = $room->is_active && $jadwal->isEmpty();

            return $room;
        });

        // ---- Ringkasan (dihitung sebelum filter status supaya angkanya tetap utuh) ----
        $ringkasan = [
            'total'    => $rooms->count(),
            'kosong'   => $rooms->where('kosong', true)->count(),
            'dipinjam' => $rooms->where('kosong', false)->count(),
        ];

        if ($status === 'kosong') {
            $rooms = $rooms->where('kosong', true)->values();
        } elseif ($status === 'dipinjam') {
            $rooms = $rooms->where('kosong', false)->values();
        }

        // ---- Daftar sesi + jamnya untuk dropdown dan legenda ----
        $daftarSesi = [];
        for ($s = 1; $s <= SesiPeminjaman::JUMLAH; $s++) {
            $daftarSesi[$s] = substr(SesiPeminjaman::mulai($s), 0, 5) . ' - ' . substr(SesiPeminjaman::selesai($s), 0, 5);
        }

        return view('ruangan.cari', compact(
            'rooms', 'ringkasan', 'daftarSesi', 'tanggal', 'status', 'keyword'
        ));
    }
}
