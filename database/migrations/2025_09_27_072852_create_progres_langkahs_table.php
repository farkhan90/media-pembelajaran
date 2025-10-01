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
        Schema::create('progres_langkahs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('langkah_id')->constrained('langkahs')->cascadeOnDelete();
            $table->enum('status', ['selesai'])->default('selesai');
            $table->text('jawaban_teks')->nullable();
            $table->foreignUuid('histori_ujian_id')->nullable()->constrained('histori_ujians')->onDelete('cascade');
            $table->foreignUuid('histori_kuis_id')->nullable()->constrained('histori_kuis')->onDelete('cascade');
            $table->timestamp('waktu_selesai');
            $table->timestamps();
            $table->unique(['user_id', 'langkah_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progres_langkahs');
    }
};
