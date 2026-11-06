<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::with(['room.pic'])
            ->whereIn('status', ['Disetujui', 'Approved'])
            ->orderBy('date', 'asc')
            ->orderBy('start_session', 'asc')
            ->get();

        $calendarEvents = [];
        foreach ($events as $event) {
            $calendarEvents[] = [
                'title' => $event->event_name,
                'start' => $event->date,
                'extendedProps' => [
                    'ruang'         => $event->room ? $event->room->room_name : 'Tidak diketahui',
                    'waktu'         => 'Sesi ' . $event->start_session . ' - Sesi ' . $event->end_session,
                    'start_session' => $event->start_session,
                    'end_session'   => $event->end_session,
                    'pic'           => ($event->room && $event->room->pic) ? $event->room->pic->fullname : 'Belum ada PIC'
                ]
            ];
        }

        $upcomingEvents = Event::with(['room'])
            ->whereIn('status', ['Disetujui', 'Approved'])
            ->whereDate('date', '>=', Carbon::today())
            ->orderBy('date', 'asc')
            ->orderBy('start_session', 'asc')
            ->get();

        return view('welcome', compact('calendarEvents', 'upcomingEvents'));
    }
}