<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class GuruController extends Controller
{
    public function guruDashboard()
    {
        $notifikasiPindah = Event::where('user_id', auth()->id())
            ->whereNotNull('move_reason')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('guru.dashboard', compact('notifikasiPindah'));
    }
}
