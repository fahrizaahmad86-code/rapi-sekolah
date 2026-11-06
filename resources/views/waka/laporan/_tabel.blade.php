{{-- Dipakai oleh halaman laporan (layar) dan halaman cetak (PDF).
     Variabel: $mode, $peminjaman, $pengaduan --}}
@php
    use App\Support\SesiPeminjaman;
    $tampilTanggal = $mode === 'bulanan';
    $badgeEvent = ['Pending' => 'warning text-dark', 'Approved' => 'success', 'Rejected' => 'danger'];
    $labelEvent = ['Pending' => 'Menunggu', 'Approved' => 'Disetujui', 'Rejected' => 'Ditolak'];
    $badgeRepair = ['Belum Diperbaiki' => 'danger', 'Menunggu Spare Part' => 'warning text-dark', 'Sudah Diperbaiki' => 'success'];
@endphp

<h5 class="fw-bold mt-4 mb-2 tabel-judul">A. Peminjaman Ruangan <small class="text-muted fw-normal">({{ $peminjaman->count() }} data)</small></h5>
<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0 tabel-laporan">
        <thead class="table-light">
            <tr>
                <th style="width:40px">No</th>
                @if($tampilTanggal)<th>Tanggal</th>@endif
                <th>Nama Kegiatan</th>
                <th>Ruangan</th>
                <th>Pemohon</th>
                <th>Sesi / Waktu</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peminjaman as $i => $e)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    @if($tampilTanggal)<td class="text-nowrap">{{ \Carbon\Carbon::parse($e->date)->format('d/m/Y') }}</td>@endif
                    <td>{{ $e->event_name }}</td>
                    <td>{{ $e->room->room_name ?? '-' }}</td>
                    <td>{{ $e->user->fullname ?? '-' }}</td>
                    <td class="text-nowrap">
                        {{ SesiPeminjaman::label($e->start_session, $e->end_session) }}<br>
                        <small class="text-muted">{{ substr(SesiPeminjaman::mulai($e->start_session), 0, 5) }} - {{ substr(SesiPeminjaman::selesai($e->end_session), 0, 5) }}</small>
                    </td>
                    <td><span class="badge bg-{{ $badgeEvent[$e->status] ?? 'secondary' }}">{{ $labelEvent[$e->status] ?? $e->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="{{ $tampilTanggal ? 7 : 6 }}" class="text-center text-muted py-3">Tidak ada peminjaman ruangan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<h5 class="fw-bold mt-4 mb-2 tabel-judul">B. Pengaduan Kerusakan / Fasilitas <small class="text-muted fw-normal">({{ $pengaduan->count() }} data)</small></h5>
<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0 tabel-laporan">
        <thead class="table-light">
            <tr>
                <th style="width:40px">No</th>
                <th>{{ $tampilTanggal ? 'Tanggal' : 'Jam' }}</th>
                <th>Ruangan</th>
                <th>Pelapor</th>
                <th>Deskripsi Kerusakan</th>
                <th>Penanggung Jawab</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengaduan as $i => $c)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($c->reported_at ?? $c->created_at)->format($tampilTanggal ? 'd/m/Y' : 'H:i') }}</td>
                    <td>{{ $c->room->room_name ?? '-' }}</td>
                    <td>{{ $c->reporter->fullname ?? '-' }}</td>
                    <td>{{ $c->description }}</td>
                    <td>{{ $c->handler->fullname ?? '-' }}</td>
                    <td><span class="badge bg-{{ $badgeRepair[$c->status_repair] ?? 'secondary' }}">{{ $c->status_repair }}</span></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada pengaduan kerusakan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
