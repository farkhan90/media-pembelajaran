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
        Schema::create('progres_pulau_siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            // Kita tidak perlu kelas_id di sini, karena progres terikat pada user, bukan kelas
            $table->string('nama_pulau');
            $table->timestamp('waktu_selesai');
            $table->foreignUuid('histori_ujian_id')->nullable()->constrained('histori_ujians')->onDelete('set null');
            $table->foreignUuid('histori_kuis_id')->nullable()->constrained('histori_kuis')->onDelete('set null');
            $table->decimal('skor_akumulasi', 5, 2)->nullable();
            $table->text('jawaban_refleksi')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'nama_pulau']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progres_pulau_siswas');
    }
};
