@extends('layouts.app')

@section('content')
<style>
    .sesi-strip { display: flex; gap: 3px; }
    .sesi-strip .sesi-box {
        flex: 1; height: 22px; border-radius: 4px;
        font-size: 0.65rem; line-height: 22px; text-align: center;
        font-weight: 600; user-select: none;
    }
    .sesi-kosong    { background: #DCFCE7; color: #166534; }
    .sesi-approved  { background: #FEE2E2; color: #991B1B; }
    .sesi-pending   { background: #FEF3C7; color: #92400E; }
    .legend-dot { display: inline-block; width: 12px; height: 12px; border-radius: 3px; vertical-align: -1px; margin-right: 4px; }
    .room-card { border-radius: 12px; }
</style>

<div class="container-fluid">
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">Cari Ruangan</h3>
        <p class="text-muted mb-0">Cek ruangan mana yang masih kosong atau sudah dipinjam pada tanggal yang kamu mau.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- ===================== FILTER ===================== --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('cari-ruangan.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Status Ruangan</label>
                    <select name="status" class="form-select">
                        <option value="semua"    {{ $status === 'semua' ? 'selected' : '' }}>Semua ruangan</option>
                        <option value="kosong"   {{ $status === 'kosong' ? 'selected' : '' }}>Hanya yang kosong</option>
                        <option value="dipinjam" {{ $status === 'dipinjam' ? 'selected' : '' }}>Hanya yang sudah dipinjam</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Nama / Lokasi Ruangan</label>
                    <input type="text" name="q" class="form-control" placeholder="Contoh: Lab, Aula, Lantai 1" value="{{ $keyword }}">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn text-white fw-semibold flex-grow-1" style="background-color: #0EA5E9; border-radius: 8px;">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="{{ route('cari-ruangan.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== RINGKASAN ===================== --}}
    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
        <div class="fw-semibold text-dark">
            {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y') }}
        </div>
        <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">{{ $ringkasan['kosong'] }} kosong</span>
        <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">{{ $ringkasan['dipinjam'] }} sudah dipinjam</span>
        <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle">{{ $ringkasan['total'] }} ruangan</span>
    </div>

    <div class="small text-muted mb-3">
        <span class="me-3"><span class="legend-dot sesi-kosong"></span>Kosong</span>
        <span class="me-3"><span class="legend-dot sesi-pending"></span>Diajukan (menunggu persetujuan)</span>
        <span><span class="legend-dot sesi-approved"></span>Sudah dipinjam (disetujui)</span>
    </div>

    {{-- ===================== DAFTAR RUANGAN ===================== --}}
    <div class="row g-3" data-pager="grid">
        @forelse($rooms as $room)
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-sm border-0 h-100 room-card">
                    <div class="card-body d-flex flex-column">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0">{{ $room->room_name }}</h5>
                            @if(!$room->is_active)
                                <span class="badge bg-secondary">Tidak aktif</span>
                            @elseif($room->kosong)
                                <span class="badge bg-success">Kosong</span>
                            @else
                                <span class="badge bg-danger">Sudah dipinjam</span>
                            @endif
                        </div>

                        <ul class="list-unstyled small text-muted mb-3">
                            <li><i class="bi bi-people me-2"></i>Kapasitas {{ $room->capacity }} orang</li>
                            <li><i class="bi bi-geo-alt me-2"></i>{{ $room->location }}</li>
                            <li><i class="bi bi-person-badge me-2"></i>PIC: {{ $room->pic ? $room->pic->fullname : 'Belum ditentukan' }}</li>
                        </ul>

                        {{-- Peta 10 sesi pada tanggal terpilih --}}
                        <div class="small fw-semibold text-dark mb-1">Pemakaian pada tanggal ini</div>
                        <div class="sesi-strip mb-3">
                            @foreach($room->peta_sesi as $no => $st)
                                @php
                                    $kelas = $st === 'Approved' ? 'sesi-approved' : ($st === 'Pending' ? 'sesi-pending' : 'sesi-kosong');
                                    $ket = $st === 'Approved' ? 'dipinjam' : ($st === 'Pending' ? 'diajukan' : 'kosong');
                                @endphp
                                <div class="sesi-box {{ $kelas }}" title="Sesi {{ $no }} ({{ $daftarSesi[$no] }}): {{ $ket }}">{{ $no }}</div>
                            @endforeach
                        </div>

                        {{-- Daftar peminjaman pada tanggal ini --}}
                        @if($room->jadwal_hari_ini->isNotEmpty())
                            <div class="small fw-semibold text-dark mb-1">Jadwal peminjaman</div>
                            <ul class="list-unstyled small mb-3">
                                @foreach($room->jadwal_hari_ini as $event)
                                    <li class="mb-2 p-2 rounded" style="background: #F8FAFC;">
                                        <div class="fw-semibold">{{ $event->event_name }}</div>
                                        <div class="text-muted">
                                            {{ \App\Support\SesiPeminjaman::label($event->start_session, $event->end_session) }}
                                            ({{ substr(\App\Support\SesiPeminjaman::mulai($event->start_session), 0, 5) }}
                                            - {{ substr(\App\Support\SesiPeminjaman::selesai($event->end_session), 0, 5) }})
                                        </div>
                                        <div class="text-muted">Peminjam: {{ $event->user ? $event->user->fullname : '-' }}</div>
                                        @if($event->status === 'Pending')
                                            <span class="badge bg-warning text-dark mt-1">Menunggu persetujuan</span>
                                        @else
                                            <span class="badge bg-danger mt-1">Disetujui</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @elseif($room->is_active)
                            <div class="small text-success mb-3">
                                <i class="bi bi-check-circle me-1"></i>Belum ada peminjaman pada tanggal ini.
                            </div>
                        @endif

                        <div class="mt-auto">
                            @if($room->kosong)
                                <a href="{{ route('peminjaman.index') }}" class="btn btn-sm text-white fw-semibold w-100" style="background-color: #0EA5E9; border-radius: 8px;">
                                    Ajukan Peminjaman
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        Tidak ada ruangan yang cocok dengan filter ini.
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
