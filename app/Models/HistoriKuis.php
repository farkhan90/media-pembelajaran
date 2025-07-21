<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoriKuis extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'histori_kuis';

    protected $fillable = [
        'kuis_id',
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
     * Histori ini milik Kuis mana.
     */
    public function kuis(): BelongsTo
    {
        return $this->belongsTo(KuisMenjodohkan::class, 'kuis_id');
    }

    /**
     * Histori ini milik User (Siswa) mana.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Histori ini memiliki banyak detail pasangan jawaban siswa.
     */
    public function jawabanJodohSiswas(): HasMany
    {
        return $this->hasMany(JawabanJodohSiswa::class);
    }
}
