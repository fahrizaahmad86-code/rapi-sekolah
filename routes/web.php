<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PicController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\OrganisasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\CariRuanganController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\PublicStorageController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/lupa-password', [ForgotPasswordController::class, 'showEmailForm'])->name('password.request');
    Route::post('/lupa-password', [ForgotPasswordController::class, 'sendOtp'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/lupa-password/verifikasi', [ForgotPasswordController::class, 'showOtpForm'])->name('password.otp');
    Route::post('/lupa-password/verifikasi', [ForgotPasswordController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('password.otp.verify');
    Route::post('/lupa-password/kirim-ulang', [ForgotPasswordController::class, 'resendOtp'])->middleware('throttle:5,1')->name('password.otp.resend');
    Route::get('/lupa-password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/lupa-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});


Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');


Route::middleware('auth')->group(function () {
    
    Route::get('/waka/dashboard', function () { 
        return view('waka.dashboard'); 
    })->name('waka.dashboard');

    Route::get('/guru/dashboard', [GuruController::class, 'guruDashboard'])->name('guru.dashboard');

    Route::get('/pic/dashboard', [PicController::class, 'dashboard'])->name('pic.dashboard');

    Route::get('/organisasi/dashboard', [OrganisasiController::class, 'index'])->name('organisasi.dashboard');

    // Profil (semua role): lihat & ubah data diri sendiri (password lewat fitur Lupa Password di halaman login)
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

    // Cari Ruangan (Waka, Guru, Organisasi/OSIS) - cek ruangan kosong / sudah dipinjam
    Route::get('/cari-ruangan', [CariRuanganController::class, 'index'])->name('cari-ruangan.index');

    Route::resource('/ruangan', RoomController::class)->names([
        'index'   => 'ruangan.index',
        'store'   => 'ruangan.store',
        'update'  => 'ruangan.update',
        'destroy' => 'ruangan.destroy',
    ]);

    Route::resource('/peminjaman', EventController::class)->names([
        'index'   => 'peminjaman.index',
        'store'   => 'peminjaman.store',
        'update'  => 'peminjaman.update',
        'destroy' => 'peminjaman.destroy',
    ]);

    Route::resource('/pengaduan', ComplaintController::class)->names([
        'index'   => 'pengaduan.index',
        'store'   => 'pengaduan.store',
        'update'  => 'pengaduan.update',
        'destroy' => 'pengaduan.destroy',
    ]);

    Route::get('/user/template-guru', [UserController::class, 'templateGuru'])->name('user.template-guru');
    Route::post('/user/import-guru', [UserController::class, 'importGuru'])->name('user.import-guru');

    // Laporan Waka/Admin (bisa dicetak ke PDF)
    Route::get('/laporan/harian', [LaporanController::class, 'harian'])->name('laporan.harian');
    Route::get('/laporan/harian/cetak', [LaporanController::class, 'cetakHarian'])->name('laporan.harian.cetak');
    Route::get('/laporan/harian/pdf', [LaporanController::class, 'pdfHarian'])->name('laporan.harian.pdf');
    Route::get('/laporan/bulanan/pdf', [LaporanController::class, 'pdfBulanan'])->name('laporan.bulanan.pdf');
    Route::get('/laporan/bulanan', [LaporanController::class, 'bulanan'])->name('laporan.bulanan');
    Route::get('/laporan/bulanan/cetak', [LaporanController::class, 'cetakBulanan'])->name('laporan.bulanan.cetak');

    Route::resource('/user', UserController::class)->names([
        'index'   => 'user.index',
        'store'   => 'user.store',
        'destroy' => 'user.destroy',
    ]);
    
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/media/{path}', [PublicStorageController::class, 'show'])->where('path', '.*')->name('public.storage');
Route::get('/notifications/{id}/read', function ($id) {
    $notification = auth()->user()->notifications()->findOrFail($id);
    $notification->markAsRead();

    return isset($notification->data['event_id'])
        ? redirect()->route('peminjaman.index')
        : redirect()->route('pengaduan.index');
})->name('notifications.read');