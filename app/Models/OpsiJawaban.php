<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpsiJawaban extends Model
{
    use HasFactory, HasUuids;

    // Nama tabel perlu didefinisikan secara eksplisit karena nama modelnya jamak
    protected $table = 'opsi_jawabans';

    protected $fillable = [
        'soal_id',
        'teks_opsi',
        'gambar_opsi',
        'is_benar',
    ];

    /**
     * The attributes that should be cast.
     * Mengubah tipe data 'is_benar' menjadi boolean (true/false)
     */
    protected $casts = [
        'is_benar' => 'boolean',
    ];

    /**
     * Opsi ini milik Soal mana.
     */
    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }
}
