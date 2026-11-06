<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuruController;
use App\Http\Controllers\Api\PicApiController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\WakaController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoomSearchController;
use App\Http\Controllers\EventController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- ROUTE PUBLIK (Bisa diakses tanpa login) ---
Route::post('/login', [AuthController::class, 'login']);
Route::get('/events', [EventController::class, 'publicIndex']);
Route::post('/password/otp', [PasswordResetController::class, 'sendOtp'])->middleware('throttle:5,1');
Route::post('/password/otp/verify', [PasswordResetController::class, 'verifyOtp'])->middleware('throttle:10,1');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:5,1');

// --- ROUTE DIPROTEKSI (Butuh Token Sanctum / Sudah Login) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::get('/room-search', [RoomSearchController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('waka')->controller(WakaController::class)->group(function () {
        Route::get('/dashboard', 'dashboard');
        Route::get('/reports/daily', [ReportController::class, 'daily']);
        Route::get('/reports/monthly', [ReportController::class, 'monthly']);
        Route::get('/users', 'users');
        Route::post('/users', 'storeUser');
        Route::put('/users/{user}', 'updateUser');
        Route::delete('/users/{user}', 'destroyUser');
        Route::get('/rooms', 'rooms');
        Route::post('/rooms', 'storeRoom');
        Route::put('/rooms/{room}', 'updateRoom');
        Route::delete('/rooms/{room}', 'destroyRoom');
        Route::get('/events', 'events');
        Route::post('/events', 'storeEvent');
        Route::put('/events/{event}', 'updateEvent');
        Route::delete('/events/{event}', 'destroyEvent');
        Route::get('/complaints', 'complaints');
        Route::post('/complaints', 'storeComplaint');
        Route::put('/complaints/{complaint}/assign', 'assignComplaint');
        Route::delete('/complaints/{complaint}', 'destroyComplaint');
    });

    Route::prefix('guru')->controller(GuruController::class)->group(function () {
        Route::get('/dashboard', 'dashboard');
        Route::get('/events', 'events');
        Route::post('/events', 'storeEvent');
        Route::delete('/events/{event}', 'destroyEvent');
        Route::get('/complaints', 'complaints');
        Route::post('/complaints', 'storeComplaint');
    });

    Route::prefix('pic')->controller(PicApiController::class)->group(function () {
        Route::get('/dashboard', 'dashboard');
        Route::get('/complaints', 'complaints');
        Route::put('/complaints/{complaint}/status', 'updateStatus');
    });
});