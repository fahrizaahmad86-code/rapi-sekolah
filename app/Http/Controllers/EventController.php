<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Room;
use App\Notifications\PeminjamanNotification;
use App\Support\SesiPeminjaman;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function publicIndex()
    {
        $events = Event::with('room:id,room_name')
            ->where('status', 'Approved')
            ->orderBy('date')
            ->orderBy('start_session')
            ->get([
                'id',
                'room_id',
                'event_name',
                'agenda_type',
                'date',
                'start_session',
                'end_session',
                'status',
            ]);

        return response()->json(['events' => $events]);
    }

    public function index()
    {
        $events = Event::with(['room.pic', 'user'])->orderBy('created_at', 'desc')->get();
        $ruangans = Room::all();
        return view('waka.peminjaman.index', compact('events', 'ruangans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_name'    => 'required|string|max:255',
            'agenda_type'   => 'required|in:internal,khusus,guru,umum',
            'room_id'       => 'required|exists:rooms,id',
            'date'          => 'required|date|after_or_equal:today',
            'start_session' => 'required|integer|min:1|max:10',
            'end_session'   => 'required|integer|min:1|max:10|gte:start_session',
            'document_path' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'new_room_id'   => 'nullable|exists:rooms,id',
            'move_reason'   => 'nullable|string',
        ], [
            'date.after_or_equal' => 'Tanggal peminjaman harus hari ini atau setelahnya.',
            'agenda_type.required' => 'Pilih jenis agenda (internal, khusus, guru, atau umum).',
            'end_session.gte' => 'Sesi selesai harus lebih besar atau sama dengan sesi mulai.',
        ]);

        // Sesi yang jamnya sudah terlewat tidak boleh dipinjam
        if (SesiPeminjaman::sudahLewat($validated['date'], (int) $validated['start_session'])) {
            return redirect()->back()->withInput()->with('error', 'Gagal diajukan! ' . SesiPeminjaman::labelDropdown((int) $validated['start_session']) . ' sudah terlewat. Pilih sesi yang belum dimulai.');
        }

        $isWaka = auth()->user()->getAttribute('role_id') == 1;

        // 1. Cek bentrok jadwal di ruangan dan tanggal/sesi yang sama
        $conflictingEvent = Event::where('room_id', $validated['room_id'])
            ->where('date', $validated['date'])
            ->where('status', '!=', 'Rejected')
            ->where(function ($query) use ($validated) {
                $query->where('start_session', '<=', $validated['end_session'])
                      ->where('end_session', '>=', $validated['start_session']);
            })
            ->first();

        // 2. Jika bukan Waka dan terjadi bentrok -> Tolak
        if (!$isWaka && $conflictingEvent) {
            return redirect()->back()->withInput()->with('error', 'Gagal diajukan! Ruangan sudah dipinjam/diajukan pada sesi tersebut.');
        }

        // 3. Jika Waka dan terjadi bentrok -> Relokasi / Pemindahan Ruangan
        if ($isWaka && $conflictingEvent) {
            if (!$request->filled('new_room_id') || !$request->filled('move_reason')) {
                return redirect()->back()->withInput()->with('error', 'Ruangan ini sudah dipinjam (' . $conflictingEvent->getAttribute('event_name') . '). Harap pilih Ruangan Pengganti dan isi Alasan Pemindahan.');
            }

            // Cek apakah ruangan pengganti juga sedang terpakai pada sesi tersebut
            $isNewRoomBusy = Event::where('room_id', $validated['new_room_id'])
                ->where('date', $validated['date'])
                ->where('status', '!=', 'Rejected')
                ->where('id', '!=', $conflictingEvent->getAttribute('id'))
                ->where(function ($query) use ($conflictingEvent) {
                    $query->where('start_session', '<=', $conflictingEvent->getAttribute('end_session'))
                          ->where('end_session', '>=', $conflictingEvent->getAttribute('start_session'));
                })
                ->exists();

            if ($isNewRoomBusy) {
                return redirect()->back()->withInput()->with('error', 'Ruangan pengganti yang Anda pilih juga sedang dipinjam pada sesi tersebut. Silakan pilih ruangan lain.');
            }

            // Pindahkan peminjaman sebelumnya ke ruangan baru beserta alasannya
            $conflictingEvent->update([
                'room_id'     => $validated['new_room_id'],
                'move_reason' => $validated['move_reason'],
            ]);
            $conflictingEvent->refresh()->load('room');
            $conflictingEvent->user->notify(PeminjamanNotification::moved($conflictingEvent));
        }

        $suratPath = $request->file('document_path')->store('peminjaman', 'public');

        Event::create([
            'room_id'       => $validated['room_id'],
            'user_id'       => auth()->id(),
            'event_name'    => $validated['event_name'],
            'agenda_type'   => $validated['agenda_type'],
            'date'          => $validated['date'],
            'start_session' => $validated['start_session'],
            'end_session'   => $validated['end_session'],
            'document_path' => $suratPath,
            'status'        => $isWaka ? 'Approved' : 'Pending',
        ]);

        $pesanSukses = ($isWaka && $conflictingEvent)
            ? 'Agenda Waka berhasil dibuat, dan peminjaman (' . $conflictingEvent->getAttribute('event_name') . ') telah dipindahkan ke ruangan pengganti.'
            : ($isWaka 
                ? 'Agenda Waka berhasil dibuat dan langsung disetujui.' 
                : 'Pengajuan peminjaman berhasil dibuat.');

        return redirect()->back()->with('success', $pesanSukses);
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->getAttribute('role_id') != 1) {
            return redirect()->back()->with('error', 'Hanya Admin yang dapat memvalidasi pengajuan.');
        }

        $request->validate([
            'status'        => 'required|in:Pending,Approved,Rejected',
            'agenda_type'   => 'nullable|in:internal,khusus,guru,umum',
            'room_id'       => 'required|exists:rooms,id',
            'start_session' => 'nullable|integer|min:1|max:10',
            'end_session'   => 'nullable|integer|min:1|max:10|gte:start_session',
            'move_reason'   => 'nullable|string'
        ], [
            'end_session.gte' => 'Sesi selesai harus lebih besar atau sama dengan sesi mulai.',
        ]);

        $event = Event::with('user')->findOrFail($id);

        $startSession = $request->input('start_session', $event->getAttribute('start_session'));
        $endSession   = $request->input('end_session', $event->getAttribute('end_session'));
        $roomId = $request->input('room_id');
        $status = $request->input('status');
        $wasMoved = (int) $event->room_id !== (int) $roomId
            || (int) $event->start_session !== (int) $startSession
            || (int) $event->end_session !== (int) $endSession;
        $statusChanged = $event->status !== $status;

        // Jika status Approved, cek apakah ruangan & sesi tujuan bentrok dengan acara lain yang Approved
        if ($status == 'Approved') {
            $isTargetBusy = Event::where('room_id', $roomId)
                ->where('date', $event->getAttribute('date'))
                ->where('id', '!=', $event->getAttribute('id'))
                ->where('status', 'Approved')
                ->where(function ($query) use ($startSession, $endSession) {
                    $query->where('start_session', '<=', $endSession)
                          ->where('end_session', '>=', $startSession);
                })
                ->exists();

            if ($isTargetBusy) {
                return redirect()->back()->with('error', 'Gagal memproses! Ruangan & sesi tujuan sedang dipinjam (Approved) oleh kegiatan lain.');
            }

            // Batalkan otomatis pengajuan berstatus Pending lainnya yang bentrok di ruangan & sesi tujuan
            $conflictingPendingEvents = Event::with('user')
                ->where('room_id', $roomId)
                ->where('date', $event->getAttribute('date'))
                ->where('id', '!=', $event->getAttribute('id'))
                ->where('status', 'Pending')
                ->where(function ($query) use ($startSession, $endSession) {
                    $query->where('start_session', '<=', $endSession)
                          ->where('end_session', '>=', $startSession);
                })
                ->get();

            foreach ($conflictingPendingEvents as $conflictingPendingEvent) {
                $conflictingPendingEvent->update([
                    'status'      => 'Rejected',
                    'move_reason' => 'Dibatalkan otomatis karena jadwal lain pada ruangan dan sesi ini telah disetujui oleh Waka/Admin'
                ]);
                $conflictingPendingEvent->user->notify(
                    PeminjamanNotification::statusChanged($conflictingPendingEvent)
                );
            }
        }

        $event->update([
            'room_id'       => $roomId,
            'start_session' => $startSession,
            'end_session'   => $endSession,
            'status'        => $status,
            'move_reason'   => $request->input('move_reason'),
            'agenda_type'   => $request->input('agenda_type', $event->getAttribute('agenda_type')),
        ]);

        $event->refresh()->load('room');
        if ($wasMoved) {
            $event->user->notify(PeminjamanNotification::moved($event));
        }
        if ($statusChanged && in_array($status, ['Approved', 'Rejected'], true)) {
            $event->user->notify(PeminjamanNotification::statusChanged($event));
        }

        return redirect()->back()->with('success', 'Data peminjaman & lokasi ruangan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        if (auth()->user()->getAttribute('role_id') != 1 && $event->getAttribute('user_id') != auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk menghapus peminjaman ini.');
        }

        $event->delete();
        return redirect()->back()->with('success', 'Data peminjaman berhasil dihapus.');
    }
}