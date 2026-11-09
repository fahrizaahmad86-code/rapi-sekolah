<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nomor WhatsApp, disimpan format internasional tanpa '+', contoh: 6281234567890
            $table->string('phone_number', 20)->nullable()->after('email');
        });

        Schema::table('complaints', function (Blueprint $table) {
            // Kapan terakhir PIC ditugaskan / status perbaikan terakhir berubah.
            // Dipakai untuk menghitung "3 hari tanpa perubahan status".
            $table->timestamp('last_progress_at')->nullable()->after('status_repair');
        });

        // Pengaduan lama yang sudah punya PIC: hitung dari waktu update terakhirnya
        DB::table('complaints')
            ->whereNotNull('handler_id')
            ->update(['last_progress_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn('last_progress_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_number');
        });
    }
};
