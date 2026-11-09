<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // internal = hanya kelompok peminjam yang terlibat   (badge biru)
            // khusus   = seluruh siswa terlibat                  (badge hijau)
            // guru     = khusus guru                             (badge ungu)
            // umum     = seluruh warga sekolah                   (badge oranye)
            $table->string('agenda_type', 10)->default('internal')->after('event_name');
        });

        // Bersihkan kolom dari versi sebelumnya (jika migration lama sudah sempat dijalankan)
        foreach (['audience', 'target_class'] as $kolom) {
            if (Schema::hasColumn('events', $kolom)) {
                Schema::table('events', fn (Blueprint $table) => $table->dropColumn($kolom));
            }
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('agenda_type');
        });
    }
};
