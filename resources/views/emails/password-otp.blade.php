<!DOCTYPE html>
<html lang="id">
<body style="margin:0;padding:24px;background:#F8FAFC;font-family:'Segoe UI',Tahoma,Arial,sans-serif;color:#1E293B;">
    <div style="max-width:480px;margin:0 auto;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:16px;padding:32px;">
        <h2 style="margin:0 0 4px;font-weight:700;">RAPI-Sekolah</h2>
        <p style="margin:0 0 24px;color:#64748B;font-size:14px;">Permintaan reset password</p>

        <p>Halo <strong>{{ $fullname }}</strong>,</p>
        <p>Gunakan kode OTP berikut untuk mengganti password akun Anda:</p>

        <div style="text-align:center;margin:24px 0;">
            <span style="display:inline-block;font-size:34px;font-weight:700;letter-spacing:10px;color:#0EA5E9;background:#F0F9FF;border-radius:12px;padding:14px 24px;">{{ $otp }}</span>
        </div>

        <p style="font-size:14px;">Kode berlaku selama <strong>{{ $expiresInMinutes }} menit</strong>. Jangan bagikan kode ini kepada siapa pun, termasuk pihak sekolah.</p>
        <p style="font-size:13px;color:#64748B;">Jika Anda tidak merasa meminta reset password, abaikan email ini. Password Anda tidak akan berubah.</p>
    </div>
</body>
</html>
