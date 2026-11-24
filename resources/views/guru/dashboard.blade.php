@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-3">Selamat Datang, Guru!</h4>
            <p class="text-muted mb-4">
                Ini adalah halaman panel kendali utama. Anda masuk dengan hak akses sebagai <strong>Guru</strong>. Anda dapat mencari ruangan, mengajukan peminjaman ruang, dan melaporkan kerusakan fasilitas di sini.
            </p>
        </div>
    </div>

    @if(isset($notifikasiPindah) && $notifikasiPindah->count() > 0)
        <div class="card alert alert-dismissible fade show shadow-sm border-0 p-0 mb-3" role="alert">
            <div class="card-header bg-white fw-bold text-dark d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bell-fill text-dark"></i>
                    <span>Peringatan Sistem: Perubahan Jadwal / Alasan Pemindahan</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="this.closest('.card').remove()"></button>
            </div>

            <div class="card-body p-0">
                <ul class="list-group list-group-flush border-0">
                    @foreach($notifikasiPindah as $notif)
                        <li class="list-group-item p-3 border-0">
                            <div class="fw-bold text-dark">{{ $notif->event_name }}</div>
                            <small class="text-muted">Tanggal Kegiatan: {{ \Carbon\Carbon::parse($notif->date)->format('d-m-Y') }}</small>
                            <div class="text-danger fw-semibold mt-1">
                                Alasan Pemindahan: {{ $notif->move_reason }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

        </div>
    @endif
</div>
@endsection