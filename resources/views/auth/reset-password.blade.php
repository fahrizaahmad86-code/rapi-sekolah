@extends('layouts.auth')
@section('title', 'Password Baru')
@section('subtitle', 'Buat password baru untuk akun Anda')

@section('content')
<form action="{{ route('password.update') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="password" class="form-label">Password Baru</label>
        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
               placeholder="Minimal 6 karakter" required autofocus>
        @error('password')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
               placeholder="Ulangi password baru" required>
    </div>

    <button type="submit" class="btn btn-primary-custom w-100">Simpan Password Baru</button>
</form>
@endsection
