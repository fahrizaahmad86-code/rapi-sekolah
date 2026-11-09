<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'fullname',
        'email',
        'phone_number',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Apa pun format yang diketik (0812-3456-7890, +62 812..., 62812...)
     * disimpan seragam sebagai 62812xxxxxxx agar bisa dipakai link WhatsApp.
     */
    public function setPhoneNumberAttribute($value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if ($digits === '') {
            $this->attributes['phone_number'] = null;
            return;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        $this->attributes['phone_number'] = $digits;
    }

    /** Tampilan nomor lokal, contoh: 081234567890 */
    public function getPhoneLocalAttribute(): ?string
    {
        $nomor = $this->attributes['phone_number'] ?? null;

        return $nomor ? '0' . substr($nomor, 2) : null;
    }

    /** Link chat WhatsApp (wa.me) dengan teks pesan yang sudah terisi. */
    public function waLink(?string $pesan = null): ?string
    {
        $nomor = $this->attributes['phone_number'] ?? null;
        if (!$nomor) {
            return null;
        }

        return 'https://wa.me/' . $nomor . ($pesan ? '?text=' . rawurlencode($pesan) : '');
    }

    /** Link telepon biasa (tel:). */
    public function telLink(): ?string
    {
        $nomor = $this->attributes['phone_number'] ?? null;

        return $nomor ? 'tel:+' . $nomor : null;
    }
}
