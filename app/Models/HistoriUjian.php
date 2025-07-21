<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoriUjian extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'histori_ujians';

    protected $fillable = [
        'ujian_id',
        'user_id',
        'waktu_mulai',
        'waktu_selesai',
        'skor_akhir',
        'status',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    /**
     * Histori ini milik Ujian mana.
     */
    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class);
    }

    /**
     * Histori ini milik User (Siswa) mana.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Histori ini memiliki banyak detail jawaban siswa.
     */
    public function jawabanSiswas(): HasMany
    {
        return $this->hasMany(JawabanSiswa::class);
    }
}
