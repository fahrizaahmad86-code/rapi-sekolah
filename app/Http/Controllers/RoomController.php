<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Room;
use App\Models\User;
use App\Notifications\AssignPicNotification;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $ruangans = Room::with('pic')->get();
        $pics = User::where('role_id', 3)->get(); 
        return view('waka.ruangan.index', compact('ruangans', 'pics'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            'room_name' => 'required|string|max:255',
            'capacity'  => 'required|numeric',
            'location'  => 'required|string',
        ]);

        $room = Room::findOrFail($id);
        $room->update($request->all());

        // Laporan kerusakan lama di ruangan ini yang belum punya PIC & belum selesai -> tugaskan ke PIC ruangan
        if ($room->pic_id && $room->pic) {
            Complaint::with('room')
                ->where('room_id', $room->id)
                ->whereNull('handler_id')
                ->where('status_repair', '!=', 'Sudah Diperbaiki')
                ->get()
                ->each(function ($complaint) use ($room) {
                    $complaint->update(['handler_id' => $room->pic_id]);
                    $room->pic->notify(new AssignPicNotification($complaint));
                });
        }

        return redirect()->back()->with('success', 'Data ruangan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);
        $room->delete();

        return redirect()->back()->with('success', 'Ruangan berhasil dihapus');
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_name' => 'required',
            'capacity'  => 'required|numeric',
            'location'  => 'required',
            'pic_id'    => 'required'
        ]);

        Room::create([
            'room_name' => $request->room_name,
            'capacity'  => $request->capacity,
            'location'  => $request->location,
            'pic_id'    => $request->pic_id,
            'is_active' => 1
        ]);

        return redirect()->back()->with('success', 'Ruangan berhasil ditambahkan');
    }
}