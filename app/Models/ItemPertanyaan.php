<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ItemPertanyaan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'item_pertanyaans';

    protected $fillable = [
        'kuis_id',
        'tipe_item',
        'konten',
    ];

    /**
     * Item ini milik Kuis mana.
     */
    public function kuis(): BelongsTo
    {
        return $this->belongsTo(KuisMenjodohkan::class, 'kuis_id');
    }

    /**
     * Setiap item pertanyaan memiliki TEPAT SATU item jawaban yang benar.
     */
    public function itemJawaban(): HasOne
    {
        return $this->hasOne(ItemJawaban::class);
    }
}
