<?php

namespace App\Http\Controllers;

use App\Mail\PasswordOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    private const OTP_EXPIRES_MINUTES = 10;   // masa berlaku OTP
    private const MAX_ATTEMPTS        = 5;    // maksimal salah input OTP
    private const RESEND_COOLDOWN     = 60;   // detik jeda kirim ulang
    private const RESET_WINDOW        = 10;   // menit untuk ganti password setelah OTP benar

    /* ---------- Langkah 1: input email ---------- */

    public function showEmailForm()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'email'    => ['required', 'email'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
        ]);
        $email = strtolower($data['email']);

        // Email harus terdaftar DAN milik username yang diisi
        $user = User::where('username', $data['username'])->where('email', $email)->first();
        if (!$user) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Username dan email tidak cocok dengan data akun. OTP tidak dikirim.']);
        }

        $this->issueOtp($email);

        session([
            'reset_email'     => $email,
            'otp_resend_at'   => now()->addSeconds(self::RESEND_COOLDOWN)->timestamp,
        ]);
        session()->forget(['reset_verified_email', 'reset_verified_at']);

        return redirect()->route('password.otp')
            ->with('success', 'Kode OTP telah dikirim ke email Anda. Silakan cek inbox/spam.');
    }

    /* ---------- Langkah 2: verifikasi OTP ---------- */

    public function showOtpForm()
    {
        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $wait = max(0, (int) session('otp_resend_at', 0) - now()->timestamp);

        return view('auth.verify-otp', [
            'maskedEmail' => $this->maskEmail($email),
            'resendWait'  => $wait,
            'expires'     => self::OTP_EXPIRES_MINUTES,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $request->validate(['otp' => ['required', 'digits:6']], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits'   => 'Kode OTP harus 6 digit angka.',
        ]);

        $row = DB::table('password_otps')->where('email', $email)->first();

        if (!$row || now()->greaterThan($row->expires_at)) {
            DB::table('password_otps')->where('email', $email)->delete();
            return back()->withErrors(['otp' => 'Kode OTP kedaluwarsa atau tidak valid. Silakan kirim ulang kode.']);
        }

        if ($row->attempts >= self::MAX_ATTEMPTS) {
            DB::table('password_otps')->where('email', $email)->delete();
            return back()->withErrors(['otp' => 'Terlalu banyak percobaan salah. Silakan kirim ulang kode OTP.']);
        }

        if (!Hash::check($request->otp, $row->otp)) {
            DB::table('password_otps')->where('email', $email)->increment('attempts');
            $left = self::MAX_ATTEMPTS - ($row->attempts + 1);
            return back()->withErrors(['otp' => "Kode OTP salah. Sisa percobaan: {$left}."]);
        }

        // OTP benar -> hapus OTP (sekali pakai) dan buka izin ganti password
        DB::table('password_otps')->where('email', $email)->delete();
        session([
            'reset_verified_email' => $email,
            'reset_verified_at'    => now()->timestamp,
        ]);

        return redirect()->route('password.reset');
    }

    public function resendOtp()
    {
        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $wait = (int) session('otp_resend_at', 0) - now()->timestamp;
        if ($wait > 0) {
            return back()->withErrors(['otp' => "Tunggu {$wait} detik sebelum mengirim ulang kode."]);
        }

        $this->issueOtp($email);
        session(['otp_resend_at' => now()->addSeconds(self::RESEND_COOLDOWN)->timestamp]);

        return back()->with('success', 'Kode OTP baru telah dikirim ke email Anda.');
    }

    /* ---------- Langkah 3: password baru ---------- */

    public function showResetForm()
    {
        if (!$this->verifiedEmail()) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi verifikasi habis. Silakan ulangi proses lupa password.']);
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $email = $this->verifiedEmail();
        if (!$email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi verifikasi habis. Silakan ulangi proses lupa password.']);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required'  => 'Password baru wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Akun tidak ditemukan.']);
        }

        $user->password = $request->password;   // otomatis di-hash oleh cast 'hashed'
        $user->save();

        // Cabut token API (aplikasi mobile) lama agar sesi lama tidak tetap aktif
        $user->tokens()->delete();

        session()->forget(['reset_email', 'otp_resend_at', 'reset_verified_email', 'reset_verified_at']);

        return redirect()->route('login')
            ->with('success', 'Password berhasil diganti. Silakan login dengan password baru.');
    }

    /* ---------- Helper ---------- */

    private function issueOtp(string $email): void
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return; // tidak ada akun, diam saja
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_otps')->updateOrInsert(
            ['email' => $email],
            [
                'otp'        => Hash::make($otp),
                'attempts'   => 0,
                'expires_at' => now()->addMinutes(self::OTP_EXPIRES_MINUTES),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        try {
            Mail::to($email)->send(new PasswordOtpMail($user->fullname, $otp, self::OTP_EXPIRES_MINUTES));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim OTP reset password: ' . $e->getMessage());
        }
    }

    private function verifiedEmail(): ?string
    {
        $email = session('reset_verified_email');
        $at    = (int) session('reset_verified_at', 0);

        if (!$email || now()->timestamp - $at > self::RESET_WINDOW * 60) {
            return null;
        }
        return $email;
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email) + [1 => ''];
        $visible = mb_substr($name, 0, min(2, mb_strlen($name)));
        return $visible . str_repeat('*', max(1, mb_strlen($name) - 2)) . '@' . $domain;
    }
}
