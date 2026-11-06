<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Event;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /** Hanya Waka/Admin (role_id = 1) yang boleh membuka laporan. */
    private function pastikanWaka(): void
    {
        abort_unless(auth()->check() && auth()->user()->role_id == 1, 403, 'Halaman ini hanya untuk Waka/Admin.');
    }

    /** Ambil tanggal dari ?tanggal=YYYY-MM-DD, default hari ini. */
    private function ambilTanggal(Request $request): Carbon
    {
        try {
            $t = Carbon::createFromFormat('Y-m-d', (string) $request->query('tanggal'));
            if ($t === false) {
                throw new \Exception();
            }
            return $t->startOfDay()->locale('id');
        } catch (\Throwable $e) {
            return Carbon::today()->locale('id');
        }
    }

    /** Ambil bulan dari ?bulan=YYYY-MM, default bulan ini. */
    private function ambilBulan(Request $request): Carbon
    {
        try {
            $b = Carbon::createFromFormat('!Y-m', (string) $request->query('bulan'));
            if ($b === false) {
                throw new \Exception();
            }
            return $b->startOfMonth()->locale('id');
        } catch (\Throwable $e) {
            return Carbon::today()->startOfMonth()->locale('id');
        }
    }

    private function dataPeminjaman(Carbon $dari, Carbon $sampai)
    {
        return Event::with(['room', 'user'])
            ->whereBetween('date', [$dari->toDateString(), $sampai->toDateString()])
            ->orderBy('date')
            ->orderBy('start_session')
            ->get();
    }

    private function dataPengaduan(Carbon $dari, Carbon $sampai)
    {
        return Complaint::with(['room', 'reporter', 'handler'])
            ->whereBetween('reported_at', [$dari->copy()->startOfDay(), $sampai->copy()->endOfDay()])
            ->orderBy('reported_at')
            ->get();
    }

    // ---------- HARIAN ----------

    private function payloadHarian(Request $request): array
    {
        $tanggal = $this->ambilTanggal($request);

        return [
            'mode'       => 'harian',
            'tanggal'    => $tanggal,
            'judul'      => 'Laporan Harian',
            'periode'    => $tanggal->isoFormat('dddd, D MMMM YYYY'),
            'peminjaman' => $this->dataPeminjaman($tanggal, $tanggal),
            'pengaduan'  => $this->dataPengaduan($tanggal, $tanggal),
        ];
    }

    public function harian(Request $request)
    {
        $this->pastikanWaka();
        return view('waka.laporan.index', $this->payloadHarian($request));
    }

    public function cetakHarian(Request $request)
    {
        $this->pastikanWaka();
        return view('waka.laporan.cetak', $this->payloadHarian($request));
    }

    // ---------- BULANAN ----------

    private function payloadBulanan(Request $request): array
    {
        $bulan   = $this->ambilBulan($request);
        $akhir   = $bulan->copy()->endOfMonth();

        return [
            'mode'       => 'bulanan',
            'bulan'      => $bulan,
            'judul'      => 'Laporan Bulanan',
            'periode'    => $bulan->isoFormat('MMMM YYYY'),
            'peminjaman' => $this->dataPeminjaman($bulan, $akhir),
            'pengaduan'  => $this->dataPengaduan($bulan, $akhir),
        ];
    }

    public function bulanan(Request $request)
    {
        $this->pastikanWaka();
        return view('waka.laporan.index', $this->payloadBulanan($request));
    }

    public function cetakBulanan(Request $request)
    {
        $this->pastikanWaka();
        return view('waka.laporan.cetak', $this->payloadBulanan($request));
    }

    // ---------- UNDUH PDF LANGSUNG ----------

    private function unduh(array $data, string $namaFile)
    {
        return Pdf::loadView('waka.laporan.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($namaFile);
    }

    public function pdfHarian(Request $request)
    {
        $this->pastikanWaka();
        $data = $this->payloadHarian($request);
        return $this->unduh($data, 'laporan-harian-' . $data['tanggal']->format('Y-m-d') . '.pdf');
    }

    public function pdfBulanan(Request $request)
    {
        $this->pastikanWaka();
        $data = $this->payloadBulanan($request);
        return $this->unduh($data, 'laporan-bulanan-' . $data['bulan']->format('Y-m') . '.pdf');
    }
}
