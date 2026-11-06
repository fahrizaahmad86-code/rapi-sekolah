@php
    use App\Support\SesiPeminjaman;
    $tampilTanggal = $mode === 'bulanan';
    $labelEvent = ['Pending' => 'Menunggu', 'Approved' => 'Disetujui', 'Rejected' => 'Ditolak'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $judul }} - {{ $periode }}</title>
<style>
    @page { margin: 28px 32px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
    .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px; }
    .kop h2 { margin: 0; font-size: 18px; }
    .kop p { margin: 2px 0; }
    h4 { margin: 14px 0 5px; font-size: 12px; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th, table.data td { border: 1px solid #000; padding: 4px 5px; vertical-align: top; }
    table.data th { background: #e5e7eb; text-align: left; }
    tr { page-break-inside: avoid; }
    .kosong { text-align: center; color: #555; padding: 8px; }
    .ttd { margin-top: 30px; width: 100%; }
    .ttd td { border: 0; text-align: center; }
</style>
</head>
<body>
<div class="kop">
    <h2>RAPI-SEKOLAH</h2>
    <p>Sistem Peminjaman Ruangan &amp; Pengaduan Kerusakan Fasilitas</p>
    <p><strong>{{ strtoupper($judul) }}</strong></p>
    <p>Periode: <strong>{{ $periode }}</strong></p>
</div>

<h4>A. Peminjaman Ruangan ({{ $peminjaman->count() }} data)</h4>
<table class="data">
    <thead><tr>
        <th width="4%">No</th>
        @if($tampilTanggal)<th>Tanggal</th>@endif
        <th>Nama Kegiatan</th><th>Ruangan</th><th>Pemohon</th><th>Sesi / Waktu</th><th>Status</th>
    </tr></thead>
    <tbody>
    @forelse($peminjaman as $i => $e)
        <tr>
            <td>{{ $i + 1 }}</td>
            @if($tampilTanggal)<td>{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}</td>@endif
            <td>{{ $e->event_name }}</td>
            <td>{{ $e->room->room_name ?? '-' }}</td>
            <td>{{ $e->user->fullname ?? '-' }}</td>
            <td>{{ SesiPeminjaman::label($e->start_session, $e->end_session) }} ({{ substr(SesiPeminjaman::mulai($e->start_session), 0, 5) }}-{{ substr(SesiPeminjaman::selesai($e->end_session), 0, 5) }})</td>
            <td>{{ $labelEvent[$e->status] ?? $e->status }}</td>
        </tr>
    @empty
        <tr><td colspan="{{ $tampilTanggal ? 7 : 6 }}" class="kosong">Tidak ada peminjaman ruangan pada periode ini.</td></tr>
    @endforelse
    </tbody>
</table>

<h4>B. Pengaduan Kerusakan / Fasilitas ({{ $pengaduan->count() }} data)</h4>
<table class="data">
    <thead><tr>
        <th width="4%">No</th><th>{{ $tampilTanggal ? 'Tanggal' : 'Jam' }}</th><th>Ruangan</th><th>Pelapor</th><th>Deskripsi Kerusakan</th><th>Penanggung Jawab</th><th>Status</th>
    </tr></thead>
    <tbody>
    @forelse($pengaduan as $i => $c)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ \Carbon\Carbon::parse($c->reported_at ?? $c->created_at)->format($tampilTanggal ? 'd/m/Y' : 'H:i') }}</td>
            <td>{{ $c->room->room_name ?? '-' }}</td>
            <td>{{ $c->reporter->fullname ?? '-' }}</td>
            <td>{{ $c->description }}</td>
            <td>{{ $c->handler->fullname ?? '-' }}</td>
            <td>{{ $c->status_repair }}</td>
        </tr>
    @empty
        <tr><td colspan="7" class="kosong">Tidak ada pengaduan kerusakan pada periode ini.</td></tr>
    @endforelse
    </tbody>
</table>

<table class="ttd"><tr>
    <td width="60%"></td>
    <td>Dicetak pada {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}<br>Waka Sarpras<br><br><br><br>( {{ auth()->user()->fullname ?? '.....................' }} )</td>
</tr></table>
</body>
</html>
