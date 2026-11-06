@extends('layouts.auth')
@section('title', 'Lupa Password')
@section('subtitle', 'Masukkan username dan email akun Anda')

@section('content')
<form action="{{ route('password.email') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
               placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
        @error('username')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
               placeholder="contoh@email.com" value="{{ old('email') }}" required>
        @error('email')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary-custom w-100 mb-3">Kirim Kode OTP</button>

    <div class="text-center">
        <a href="{{ route('login') }}" class="link-back"><i class="bi bi-arrow-left me-1"></i> Kembali ke Login</a>
    </div>
</form>
@endsection
