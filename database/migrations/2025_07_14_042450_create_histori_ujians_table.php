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
        Schema::create('histori_ujians', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ujian_id')->constrained('ujians')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('waktu_mulai');
            $table->timestamp('waktu_selesai')->nullable();
            $table->decimal('skor_akhir', 5, 2)->nullable(); // 5 digit total, 2 di belakang koma (cth: 100.00)
            $table->enum('status', ['Mengerjakan', 'Selesai', 'Waktu Habis']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('histori_ujians');
    }
};
