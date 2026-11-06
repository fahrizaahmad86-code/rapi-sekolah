<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->string('completion_photo_path')->nullable()->after('photo_path');
        });
    }

    public function down()
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn('completion_photo_path');
        });
    }
};