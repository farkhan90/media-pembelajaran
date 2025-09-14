<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('progres_pulau_siswas', function (Blueprint $table) {
            $table->text('jawaban_pemantik')->nullable()->before('jawaban_refleksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progres_pulau_siswas', function (Blueprint $table) {
            $table->dropColumn('jawaban_pemantik');
        });
    }
};
