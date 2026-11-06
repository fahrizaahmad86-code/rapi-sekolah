@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-3">Selamat Datang, {{ auth()->check() ? auth()->user()->fullname : 'Pengguna' }}!</h4>
            <p class="text-muted mb-0">
                Ini adalah halaman panel kendali utama. Anda masuk dengan hak akses sebagai <strong>PIC Ruangan / Teknisi</strong>. Pantau dan perbarui status perbaikan fasilitas sekolah yang ditugaskan kepada Anda.
            </p>
        </div>
    </div>
</div>
@endsection