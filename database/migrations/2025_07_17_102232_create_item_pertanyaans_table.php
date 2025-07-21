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
        Schema::create('item_pertanyaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('kuis_id')->constrained('kuis_menjodohkan')->cascadeOnDelete();
            $table->enum('tipe_item', ['Teks', 'Gambar']);
            $table->text('konten');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_pertanyaans');
    }
};
