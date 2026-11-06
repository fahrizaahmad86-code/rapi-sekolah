<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $this->authorizeWaka($request);
        $filters = $request->validate(['date' => ['nullable', 'date']]);
        $date = Carbon::parse($filters['date'] ?? Carbon::today())->startOfDay();

        return response()->json($this->report($date, $date, 'harian'));
    }

    public function monthly(Request $request)
    {
        $this->authorizeWaka($request);
        $filters = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);
        $month = isset($filters['month'])
            ? Carbon::createFromFormat('!Y-m', $filters['month'])
            : Carbon::today()->startOfMonth();
        $month->startOfMonth();

        return response()->json($this->report($month, $month->copy()->endOfMonth(), 'bulanan'));
    }

    private function report(Carbon $from, Carbon $to, string $mode): array
    {
        $bookings = Event::with(['room:id,room_name', 'user:id,fullname'])
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->orderBy('start_session')
            ->get();
        $complaints = Complaint::with([
            'room:id,room_name',
            'reporter:id,fullname',
            'handler:id,fullname',
        ])
            ->whereBetween('reported_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->orderBy('reported_at')
            ->get();

        $period = $mode === 'harian'
            ? $from->copy()->locale('id')->isoFormat('dddd, D MMMM YYYY')
            : $from->copy()->locale('id')->isoFormat('MMMM YYYY');

        return [
            'mode' => $mode,
            'period' => $period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'summary' => [
                'total_bookings' => $bookings->count(),
                'approved_bookings' => $bookings->where('status', 'Approved')->count(),
                'total_complaints' => $complaints->count(),
                'repaired_complaints' => $complaints
                    ->where('status_repair', 'Sudah Diperbaiki')->count(),
            ],
            'bookings' => $bookings,
            'complaints' => $complaints,
        ];
    }

    private function authorizeWaka(Request $request): void
    {
        abort_unless($request->user()?->role_id == 1, 403, 'Laporan hanya dapat diakses Waka/Admin.');
    }
}