@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="fw-bold text-dark mb-4">Profil Saya</h3>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- ============ Ringkasan akun ============ --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white mb-3"
                         style="width: 88px; height: 88px; font-size: 2rem; background-color: #0EA5E9;">
                        {{ strtoupper(mb_substr($user->fullname, 0, 1)) }}
                    </div>
                    <h5 class="fw-bold mb-1">{{ $user->fullname }}</h5>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle mb-3">
                        {{ $user->role->role_name ?? 'Role ID: ' . $user->role_id }}
                    </span>

                    <ul class="list-unstyled text-start small mb-0">
                        <li class="mb-2"><span class="text-muted d-block">Username</span><span class="fw-semibold">{{ $user->username }}</span></li>
                        <li class="mb-2"><span class="text-muted d-block">Email</span><span class="fw-semibold">{{ $user->email ?? '-' }}</span></li>
                        <li><span class="text-muted d-block">Terdaftar sejak</span>
                            <span class="fw-semibold">{{ $user->created_at ? $user->created_at->format('d-m-Y') : '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            {{-- ============ Ubah data diri ============ --}}
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Ubah Data Diri</h5>

                    <form action="{{ route('profil.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="fullname" class="form-control @error('fullname') is-invalid @enderror"
                                   value="{{ old('fullname', $user->fullname) }}" required>
                            @error('fullname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Username</label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username', $user->username) }}" required>
                                @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Dipakai untuk login.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Dipakai untuk kode OTP lupa password.</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Peran</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->role->role_name ?? '-' }}" readonly>
                            <small class="text-muted">Peran hanya bisa diubah oleh Waka Sarpras lewat menu Kelola Pengguna. Lupa password? Gunakan fitur Lupa Password di halaman login.</small>
                        </div>

                        <button type="submit" class="btn text-white fw-semibold px-4" style="background-color: #0EA5E9; border-radius: 8px;">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
