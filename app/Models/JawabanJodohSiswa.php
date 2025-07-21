<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanJodohSiswa extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jawaban_jodoh_siswas';

    protected $fillable = [
        'histori_kuis_id',
        'item_pertanyaan_id',
        'item_jawaban_id',
    ];

    /**
     * Jawaban ini bagian dari Histori Kuis mana.
     */
    public function historiKuis(): BelongsTo
    {
        return $this->belongsTo(HistoriKuis::class);
    }

    /**
     * Jawaban ini untuk Item Pertanyaan mana.
     */
    public function itemPertanyaan(): BelongsTo
    {
        return $this->belongsTo(ItemPertanyaan::class);
    }

    /**
     * Jawaban ini dipasangkan dengan Item Jawaban mana.
     */
    public function itemJawaban(): BelongsTo
    {
        return $this->belongsTo(ItemJawaban::class);
    }
}
