<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanSiswa extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'jawaban_siswas';

    protected $fillable = [
        'histori_ujian_id',
        'soal_id',
        'opsi_jawaban_id',
        'is_ragu',
    ];

    protected $casts = [
        'is_ragu' => 'boolean',
    ];

    /**
     * Jawaban ini bagian dari Histori Ujian mana.
     */
    public function historiUjian(): BelongsTo
    {
        return $this->belongsTo(HistoriUjian::class);
    }

    /**
     * Jawaban ini untuk Soal mana.
     */
    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class);
    }

    /**
     * Opsi mana yang dipilih.
     */
    public function opsiJawaban(): BelongsTo
    {
        return $this->belongsTo(OpsiJawaban::class);
    }
}
