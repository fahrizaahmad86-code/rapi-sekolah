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

    /**
     * Ambil rentang tanggal dari ?dari=YYYY-MM-DD&sampai=YYYY-MM-DD.
     * Kompatibel dengan ?tanggal=YYYY-MM-DD (satu hari). Default: hari ini.
     * Jika "sampai" lebih awal dari "dari", keduanya ditukar. Maksimal 366 hari.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function ambilRentang(Request $request): array
    {
        $baca = function (string $kunci) use ($request): ?Carbon {
            try {
                $t = Carbon::createFromFormat('Y-m-d', (string) $request->query($kunci));
                return $t ? $t->startOfDay() : null;
            } catch (\Throwable $e) {
                return null;
            }
        };

        $dari   = $baca('dari') ?? $baca('tanggal');
        $sampai = $baca('sampai') ?? $baca('tanggal');

        $dari   = $dari ?? $sampai;
        $sampai = $sampai ?? $dari;

        if (!$dari) {
            $dari = $sampai = Carbon::today();
        }

        if ($sampai->lt($dari)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        if ((int) $dari->diffInDays($sampai) > 366) {
            $sampai = $dari->copy()->addDays(366);
        }

        return [$dari->locale('id'), $sampai->locale('id')];
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
        [$dari, $sampai] = $this->ambilRentang($request);
        $satuHari = $dari->isSameDay($sampai);

        return [
            'mode'       => $satuHari ? 'harian' : 'rentang',
            'tanggal'    => $dari,
            'dari'       => $dari,
            'sampai'     => $sampai,
            'judul'      => 'Laporan Harian',
            'periode'    => $satuHari
                ? $dari->isoFormat('dddd, D MMMM YYYY')
                : $dari->isoFormat('D MMMM YYYY') . ' s/d ' . $sampai->isoFormat('D MMMM YYYY'),
            'peminjaman' => $this->dataPeminjaman($dari, $sampai),
            'pengaduan'  => $this->dataPengaduan($dari, $sampai),
        ];
    }

    public function harian(Request $request)
    {
        $this->pastikanWaka();
        return view('waka.laporan.index', $this->payloadHarian($request));
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
        $nama = $data['dari']->isSameDay($data['sampai'])
            ? $data['dari']->format('Y-m-d')
            : $data['dari']->format('Y-m-d') . '_sd_' . $data['sampai']->format('Y-m-d');
        return $this->unduh($data, 'laporan-harian-' . $nama . '.pdf');
    }

    public function pdfBulanan(Request $request)
    {
        $this->pastikanWaka();
        $data = $this->payloadBulanan($request);
        return $this->unduh($data, 'laporan-bulanan-' . $data['bulan']->format('Y-m') . '.pdf');
    }
}
