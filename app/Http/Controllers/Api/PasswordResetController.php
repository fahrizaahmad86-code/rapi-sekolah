<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    private const OTP_EXPIRES_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const RESET_WINDOW_MINUTES = 10;

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);
        $email = strtolower($data['email']);
        $user = User::where('username', $data['username'])
            ->where('email', $email)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Username dan email tidak cocok dengan data akun.',
            ], 422);
        }

        $cooldownKey = 'password-otp-cooldown:'.hash('sha256', $email);
        $retryAfter = Cache::get($cooldownKey);
        if (is_int($retryAfter) && $retryAfter > now()->timestamp) {
            return response()->json([
                'message' => 'Tunggu sebelum meminta kode OTP baru.',
                'retry_after' => $retryAfter - now()->timestamp,
            ], 429);
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        DB::table('password_otps')->updateOrInsert(
            ['email' => $email],
            [
                'otp' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(self::OTP_EXPIRES_MINUTES),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        try {
            Mail::to($email)->send(
                new PasswordOtpMail($user->fullname, $otp, self::OTP_EXPIRES_MINUTES)
            );
        } catch (\Throwable $exception) {
            DB::table('password_otps')->where('email', $email)->delete();
            Log::error('Gagal mengirim OTP reset password melalui API: '.$exception->getMessage());

            return response()->json([
                'message' => 'Email OTP gagal dikirim. Silakan coba lagi nanti.',
            ], 500);
        }

        Cache::put(
            $cooldownKey,
            now()->addSeconds(self::RESEND_COOLDOWN_SECONDS)->timestamp,
            now()->addSeconds(self::RESEND_COOLDOWN_SECONDS)
        );

        return response()->json([
            'message' => 'Kode OTP telah dikirim ke email Anda.',
            'masked_email' => $this->maskEmail($email),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);
        $email = strtolower($data['email']);
        $userExists = User::where('username', $data['username'])
            ->where('email', $email)
            ->exists();
        if (!$userExists) {
            return response()->json(['message' => 'Data akun tidak valid.'], 422);
        }

        $otpRow = DB::table('password_otps')->where('email', $email)->first();
        if (!$otpRow || now()->greaterThan($otpRow->expires_at)) {
            DB::table('password_otps')->where('email', $email)->delete();
            return response()->json([
                'message' => 'Kode OTP kedaluwarsa atau tidak valid. Kirim ulang kode.',
            ], 422);
        }
        if ($otpRow->attempts >= self::MAX_ATTEMPTS) {
            DB::table('password_otps')->where('email', $email)->delete();
            return response()->json([
                'message' => 'Terlalu banyak percobaan. Kirim ulang kode OTP.',
            ], 429);
        }
        if (!Hash::check($data['otp'], $otpRow->otp)) {
            DB::table('password_otps')->where('email', $email)->increment('attempts');
            $attemptsLeft = self::MAX_ATTEMPTS - ($otpRow->attempts + 1);
            return response()->json([
                'message' => "Kode OTP salah. Sisa percobaan: {$attemptsLeft}.",
            ], 422);
        }

        DB::table('password_otps')->where('email', $email)->delete();
        $resetToken = Str::random(64);
        Cache::put(
            $this->resetTokenKey($resetToken),
            $email,
            now()->addMinutes(self::RESET_WINDOW_MINUTES)
        );

        return response()->json([
            'message' => 'Kode OTP berhasil diverifikasi.',
            'reset_token' => $resetToken,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'reset_token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $cacheKey = $this->resetTokenKey($data['reset_token']);
        $email = Cache::pull($cacheKey);
        if (!is_string($email)) {
            return response()->json([
                'message' => 'Sesi verifikasi habis. Silakan ulangi reset password.',
            ], 422);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'Akun tidak ditemukan.'], 404);
        }

        $user->password = $data['password'];
        $user->save();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password berhasil diganti. Silakan login dengan password baru.',
        ]);
    }

    private function resetTokenKey(string $token): string
    {
        return 'password-reset-token:'.hash('sha256', $token);
    }

    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email, 2);
        $visible = mb_substr($name, 0, min(2, mb_strlen($name)));

        return $visible.str_repeat('*', max(1, mb_strlen($name) - 2)).'@'.$domain;
    }
}
