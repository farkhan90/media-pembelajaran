<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Soal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'ujian_id',
        'pertanyaan',
        'gambar_soal',
    ];

    /**
     * Soal ini milik Ujian mana.
     */
    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    /**
     * Soal ini memiliki banyak Opsi Jawaban.
     */
    public function opsiJawabans(): HasMany
    {
        return $this->hasMany(OpsiJawaban::class);
    }

    /**
     * Shortcut untuk mendapatkan satu jawaban yang benar dari soal ini.
     */
    public function jawabanBenar(): HasOne
    {
        return $this->hasOne(OpsiJawaban::class)->where('is_benar', true);
    }
}
