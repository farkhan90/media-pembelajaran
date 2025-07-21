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
        Schema::create('jawaban_jodoh_siswas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('histori_kuis_id')->constrained('histori_kuis')->cascadeOnDelete();
            $table->foreignUuid('item_pertanyaan_id')->constrained('item_pertanyaans')->cascadeOnDelete();
            $table->foreignUuid('item_jawaban_id')->constrained('item_jawabans')->cascadeOnDelete();
            $table->unique(['histori_kuis_id', 'item_pertanyaan_id']);
            $table->unique(['histori_kuis_id', 'item_jawaban_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_jodoh_siswas');
    }
};
