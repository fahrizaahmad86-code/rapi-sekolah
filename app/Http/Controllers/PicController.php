<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use Illuminate\Http\Request;

class PicController extends Controller
{
    public function dashboard()
    {
        $userId = auth()->id();
        
        $totalTugas = Complaint::where('handler_id', $userId)->count();
        
        $menunggu = Complaint::where('handler_id', $userId)
                        ->whereIn('status_repair', ['Belum Diperbaiki', 'Menunggu Spare Part'])
                        ->count();
                        
        $selesai = Complaint::where('handler_id', $userId)
                        ->where('status_repair', 'Sudah Diperbaiki')
                        ->count();

        $tugasTerbaru = Complaint::with(['room', 'reporter'])
                        ->where('handler_id', $userId)
                        ->orderBy('updated_at', 'desc')
                        ->take(5)
                        ->get();

        return view('pic.dashboard', compact('totalTugas', 'menunggu', 'selesai', 'tugasTerbaru'));
    }
}