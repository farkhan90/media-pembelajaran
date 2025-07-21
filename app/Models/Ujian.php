<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ujian extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'kelas_id',
        'judul',
        'slug',
        'deskripsi',
        'waktu_menit',
        'status',
    ];

    public function getRouteKeyName()
    {
        return 'slug'; // Pastikan ini benar
    }

    /**
     * Ujian ini milik Kelas mana.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Ujian ini memiliki banyak Soal.
     */
    public function soals(): HasMany
    {
        return $this->hasMany(Soal::class);
    }

    /**
     * Ujian ini memiliki banyak Histori Pengerjaan.
     */
    public function historiUjians(): HasMany
    {
        return $this->hasMany(HistoriUjian::class);
    }
}
