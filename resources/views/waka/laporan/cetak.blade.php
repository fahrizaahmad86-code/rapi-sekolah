<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }} - {{ $periode }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #fff; color: #000; font-size: 12px; }
        .halaman { max-width: 1000px; margin: 0 auto; padding: 24px; }
        .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 16px; }
        .kop h4 { margin: 0; font-weight: 700; }
        .kop p { margin: 0; }
        .tabel-laporan th, .tabel-laporan td { font-size: 11.5px; }
        .tabel-laporan thead th { background: #e9ecef !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .ttd { margin-top: 40px; width: 100%; }
        .ttd td { border: 0 !important; text-align: center; vertical-align: top; }
        tr { page-break-inside: avoid; }
        .tabel-judul { page-break-after: avoid; }
        @page { size: A4 landscape; margin: 14mm; }
        @media print { .no-print { display: none !important; } .halaman { padding: 0; max-width: none; } }
    </style>
</head>
<body>
<div class="halaman">
    <div class="no-print d-flex justify-content-between align-items-center mb-3 p-2 bg-light border rounded">
        <span class="small text-muted">Pada jendela cetak, pilih tujuan <strong>"Simpan sebagai PDF"</strong> (Save as PDF), lalu klik Simpan.</span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">Cetak / Simpan PDF</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.close()">Tutup</button>
        </div>
    </div>

    <div class="kop">
        <h4>RAPI-SEKOLAH</h4>
        <p>Sistem Peminjaman Ruangan &amp; Pengaduan Kerusakan Fasilitas</p>
        <h5 class="fw-bold mt-2 mb-0">{{ strtoupper($judul) }}</h5>
        <p>Periode: <strong>{{ $periode }}</strong></p>
    </div>

    @include('waka.laporan._tabel')

    <table class="ttd">
        <tr>
            <td style="width:60%"></td>
            <td>
                Dicetak pada {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}<br>
                Waka Sarpras
                <br><br><br><br>
                ( {{ auth()->user()->fullname ?? '.....................' }} )
            </td>
        </tr>
    </table>
</div>
<script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 500); });</script>
</body>
</html>
