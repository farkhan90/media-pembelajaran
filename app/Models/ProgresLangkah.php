<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresLangkah extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo
    {
        // Eloquent akan secara otomatis mencari kolom 'user_id' di tabel ini.
        return $this->belongsTo(User::class);
    }

    public function langkah(): BelongsTo
    {
        return $this->belongsTo(Langkah::class);
    }

    /**
     * Mendapatkan data histori ujian pilihan ganda yang terkait (jika ada).
     */
    public function historiUjian(): BelongsTo
    {
        return $this->belongsTo(HistoriUjian::class, 'histori_ujian_id');
    }

    /**
     * Mendapatkan data histori kuis menjodohkan yang terkait (jika ada).
     */
    public function historiKuis(): BelongsTo
    {
        return $this->belongsTo(HistoriKuis::class, 'histori_kuis_id');
    }
}
