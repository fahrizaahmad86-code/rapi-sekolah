<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\Request;

class OrganisasiController extends Controller
{
    public function index()
    {
        $myEvents = Event::with('room')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        $ruangans = Room::all();

        return view('organisasi.dashboard', compact('myEvents', 'ruangans'));
    }
}