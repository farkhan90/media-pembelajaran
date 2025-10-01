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
        // Tabel untuk Modul (Pulau)
        Schema::create('moduls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->string('nama_pulau')->unique(); // 'sumatera', 'jawa', dll.
            $table->string('gambar_pulau')->nullable();
            $table->integer('urutan');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // Tabel untuk Langkah di dalam Modul
        Schema::create('langkahs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('modul_id')->constrained('moduls')->cascadeOnDelete();
            $table->string('judul');
            $table->enum('tipe', ['video', 'audio', 'canva', 'pdf', 'soal_esai', 'penilaian_akhir']);
            $table->text('konten_path')->nullable(); // URL atau path file
            $table->text('konten_teks')->nullable(); // Pertanyaan esai
            $table->foreignUuid('ujian_id')->nullable()->constrained('ujians')->onDelete('set null');
            $table->foreignUuid('kuis_menjodohkan_id')->nullable()->constrained('kuis_menjodohkan')->onDelete('set null');
            $table->json('kondisi_selesai')->nullable(); // Untuk menyimpan aturan seperti durasi
            $table->integer('urutan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('langkahs');
        Schema::dropIfExists('moduls');
    }
};
