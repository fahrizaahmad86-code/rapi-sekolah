<?php
// Tambahkan di routes/web.php, di dalam group middleware('auth'),
// SEBELUM baris Route::resource('/user', ...) supaya "/user/template-guru"
// tidak dianggap sebagai /user/{id}.

Route::get('/user/template-guru', [UserController::class, 'templateGuru'])->name('user.template-guru');
Route::post('/user/import-guru', [UserController::class, 'importGuru'])->name('user.import-guru');
