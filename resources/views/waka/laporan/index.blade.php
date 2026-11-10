@extends('layouts.app')

@section('content')
@php
    $harian = in_array($mode, ['harian', 'rentang']);
    $nilaiDari = $harian ? $dari->format('Y-m-d') : null;
    $nilaiSampai = $harian ? $sampai->format('Y-m-d') : null;
    $panjangHari = $harian ? ((int) $dari->diffInDays($sampai) + 1) : 1;
    $tahunAktif = $harian ? null : (int) $bulan->format('Y');
    $bulanAktif = $harian ? null : (int) $bulan->format('n');
    $namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $urlPdf = $harian
        ? route('laporan.harian.pdf', ['dari' => $nilaiDari, 'sampai' => $nilaiSampai])
        : route('laporan.bulanan.pdf', ['bulan' => $bulan->format('Y-m')]);
@endphp

<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">{{ $judul }}</h3>
            <div class="text-muted">Periode: <strong>{{ $periode }}</strong></div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($harian)
                {{-- Pilih rentang tanggal (Dari - Sampai). Jika sama = laporan satu hari. --}}
                <form method="GET" action="{{ route('laporan.harian') }}" id="formPeriode" class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('laporan.harian', ['dari' => $dari->copy()->subDays($panjangHari)->format('Y-m-d'), 'sampai' => $sampai->copy()->subDays($panjangHari)->format('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm" title="Periode sebelumnya"><i class="bi bi-chevron-left"></i></a>
                    <label for="inputDari" class="small text-muted mb-0">Dari</label>
                    <input type="date" name="dari" id="inputDari" value="{{ $nilaiDari }}" max="{{ $nilaiSampai }}" class="form-control form-control-sm" style="width:auto; cursor:pointer;" onclick="try{this.showPicker()}catch(e){}">
                    <label for="inputSampai" class="small text-muted mb-0">Sampai</label>
                    <input type="date" name="sampai" id="inputSampai" value="{{ $nilaiSampai }}" min="{{ $nilaiDari }}" class="form-control form-control-sm" style="width:auto; cursor:pointer;" onclick="try{this.showPicker()}catch(e){}">
                    <button type="submit" class="btn text-white fw-semibold btn-sm px-3" style="background-color:#0EA5E9; border-radius:8px;">Terapkan</button>
                    <a href="{{ route('laporan.harian', ['dari' => $dari->copy()->addDays($panjangHari)->format('Y-m-d'), 'sampai' => $sampai->copy()->addDays($panjangHari)->format('Y-m-d')]) }}" class="btn btn-outline-secondary btn-sm" title="Periode berikutnya"><i class="bi bi-chevron-right"></i></a>
                    <a href="{{ route('laporan.harian') }}" class="btn btn-outline-primary btn-sm">Hari Ini</a>
                </form>
            @else
                {{-- Kalender bulan: klik tombol untuk memunculkan pilihan bulan & tahun --}}
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('laporan.bulanan', ['bulan' => $bulan->copy()->subMonth()->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm" title="Bulan sebelumnya"><i class="bi bi-chevron-left"></i></a>

                    <div class="dropdown">
                        <button class="btn btn-outline-dark btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <i class="bi bi-calendar3 me-1"></i> {{ $periode }}
                        </button>
                        <div class="dropdown-menu p-3 shadow" style="width: 260px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <button type="button" class="btn btn-sm btn-light" id="tahunMundur"><i class="bi bi-chevron-left"></i></button>
                                <strong id="labelTahun">{{ $tahunAktif }}</strong>
                                <button type="button" class="btn btn-sm btn-light" id="tahunMaju"><i class="bi bi-chevron-right"></i></button>
                            </div>
                            <div class="row g-2" id="gridBulan">
                                @foreach($namaBulan as $i => $nm)
                                    <div class="col-4">
                                        <button type="button" class="btn btn-sm w-100 pilih-bulan {{ ($i + 1) === $bulanAktif ? 'btn-primary' : 'btn-outline-secondary' }}" data-bulan="{{ $i + 1 }}">{{ substr($nm, 0, 3) }}</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('laporan.bulanan', ['bulan' => $bulan->copy()->addMonth()->format('Y-m')]) }}" class="btn btn-outline-secondary btn-sm" title="Bulan berikutnya"><i class="bi bi-chevron-right"></i></a>
                    <a href="{{ route('laporan.bulanan') }}" class="btn btn-outline-primary btn-sm">Bulan Ini</a>
                </div>
            @endif

            <a href="{{ $urlPdf }}" class="btn text-white fw-semibold btn-sm px-3" style="background-color:#0EA5E9; border-radius:8px;">
                 Unduh PDF
            </a>
        </div>
    </div>

    <div class="row g-3 mb-2">
        <div class="col-sm-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body py-3"><div class="text-muted small">Total Peminjaman</div><div class="fs-4 fw-bold">{{ $peminjaman->count() }}</div></div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body py-3"><div class="text-muted small">Peminjaman Disetujui</div><div class="fs-4 fw-bold text-success">{{ $peminjaman->where('status', 'Approved')->count() }}</div></div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body py-3"><div class="text-muted small">Total Pengaduan</div><div class="fs-4 fw-bold">{{ $pengaduan->count() }}</div></div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body py-3"><div class="text-muted small">Pengaduan Sudah Diperbaiki</div><div class="fs-4 fw-bold text-success">{{ $pengaduan->where('status_repair', 'Sudah Diperbaiki')->count() }}</div></div></div></div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @php($scrollTables = true)
            @include('waka.laporan._tabel')
        </div>
    </div>
</div>

@if($harian)
<script>
    (function () {
        var dari = document.getElementById('inputDari');
        var sampai = document.getElementById('inputSampai');
        if (!dari || !sampai) return;
        dari.addEventListener('change', function () {
            sampai.min = dari.value;
            if (sampai.value && sampai.value < dari.value) sampai.value = dari.value;
        });
        sampai.addEventListener('change', function () {
            dari.max = sampai.value;
            if (dari.value && dari.value > sampai.value) dari.value = sampai.value;
        });
    })();
</script>
@else
<script>
    (function () {
        var tahun = {{ $tahunAktif }};
        var label = document.getElementById('labelTahun');
        document.getElementById('tahunMundur').addEventListener('click', function () { tahun--; label.textContent = tahun; });
        document.getElementById('tahunMaju').addEventListener('click', function () { tahun++; label.textContent = tahun; });
        document.querySelectorAll('.pilih-bulan').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var b = String(btn.dataset.bulan).padStart(2, '0');
                window.location.href = "{{ route('laporan.bulanan') }}?bulan=" + tahun + "-" + b;
            });
        });
    })();
</script>
@endif
@endsection
