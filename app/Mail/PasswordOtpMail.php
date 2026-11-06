<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fullname,
        public string $otp,
        public int $expiresInMinutes
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Kode OTP Reset Password - RAPI-Sekolah');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-otp');
    }
}
