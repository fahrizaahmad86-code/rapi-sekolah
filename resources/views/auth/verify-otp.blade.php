@extends('layouts.auth')
@section('title', 'Verifikasi OTP')
@section('subtitle', 'Kode 6 digit dikirim ke ' . $maskedEmail)

@section('content')
<form action="{{ route('password.otp.verify') }}" method="POST">
    @csrf
    <div class="mb-2">
        <label for="otp" class="form-label">Kode OTP</label>
        <input type="text" name="otp" id="otp" inputmode="numeric" autocomplete="one-time-code"
               maxlength="6" pattern="\d{6}" placeholder="••••••"
               class="form-control otp-input @error('otp') is-invalid @enderror" required autofocus>
        @error('otp')
            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
        @enderror
    </div>
    <p class="text-muted small mb-4">Kode berlaku {{ $expires }} menit.</p>

    <button type="submit" class="btn btn-primary-custom w-100">Verifikasi</button>
</form>

<form action="{{ route('password.otp.resend') }}" method="POST" class="text-center mt-3">
    @csrf
    <button type="submit" id="btnResend" class="btn btn-link link-back p-0" {{ $resendWait > 0 ? 'disabled' : '' }}>
        Kirim ulang kode<span id="countdown">{{ $resendWait > 0 ? " ({$resendWait}d)" : '' }}</span>
    </button>
</form>

<div class="text-center mt-3">
    <a href="{{ route('password.request') }}" class="link-back"><i class="bi bi-arrow-left me-1"></i> Ganti email</a>
</div>

<script>
    // Hitung mundur tombol kirim ulang
    let wait = {{ (int) $resendWait }};
    const btn = document.getElementById('btnResend');
    const label = document.getElementById('countdown');
    if (wait > 0) {
        const t = setInterval(() => {
            wait--;
            if (wait <= 0) { clearInterval(t); btn.disabled = false; label.textContent = ''; }
            else { label.textContent = ` (${wait}d)`; }
        }, 1000);
    }
    // Hanya angka
    document.getElementById('otp').addEventListener('input', e => e.target.value = e.target.value.replace(/\D/g, ''));
</script>
@endsection
