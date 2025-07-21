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

        Schema::table('siswa_perkelas', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->primary(['user_id', 'kelas_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa_perkelas', function (Blueprint $table) {
            $table->dropPrimary(['user_id', 'kelas_id']);
            $table->uuid('id')->first()->primary();
        });
    }
};
