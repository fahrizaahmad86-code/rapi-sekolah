<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Event;
use App\Models\Room;
use App\Models\User;

class WakaController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'menunggu'  => Event::where('status', 'Pending')->count(),
            'aktif'     => Complaint::where('status_repair', '!=', 'Sudah Diperbaiki')->count(),
            'ruangan'   => Room::count(),
            'pengguna'  => User::count(),
        ];

        $pengajuan = Event::with(['room', 'user'])
            ->where('status', 'Pending')
            ->orderBy('date')
            ->orderBy('start_session')
            ->take(5)
            ->get();

        $laporan = Complaint::with(['room', 'handler'])
            ->where('status_repair', '!=', 'Sudah Diperbaiki')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('waka.dashboard', compact('stats', 'pengajuan', 'laporan'));
    }
}
