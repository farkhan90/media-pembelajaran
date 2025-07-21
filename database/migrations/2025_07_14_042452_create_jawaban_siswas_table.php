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
        Schema::create('jawaban_siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('histori_ujian_id')->constrained('histori_ujians')->cascadeOnDelete();
            $table->foreignUuid('soal_id')->constrained('soals')->cascadeOnDelete();
            $table->foreignUuid('opsi_jawaban_id')->constrained('opsi_jawabans')->cascadeOnDelete();
            $table->boolean('is_ragu')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_siswas');
    }
};
