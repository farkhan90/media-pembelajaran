<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemJawaban extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'item_jawabans';

    protected $fillable = [
        'item_pertanyaan_id',
        'tipe_item',
        'konten',
    ];

    /**
     * Item jawaban ini adalah pasangan dari Item Pertanyaan mana.
     */
    public function itemPertanyaan(): BelongsTo
    {
        return $this->belongsTo(ItemPertanyaan::class);
    }
}
