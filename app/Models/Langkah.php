<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Langkah extends Model
{
    use HasUuids;
    protected $guarded = [];
    protected $casts = ['kondisi_selesai' => 'array'];

    public function modul(): BelongsTo
    {
        return $this->belongsTo(Modul::class);
    }

    public function ujian(): BelongsTo
    {
        return $this->belongsTo(Ujian::class, 'ujian_id');
    }

    public function kuisMenjodohkan(): BelongsTo
    {
        return $this->belongsTo(KuisMenjodohkan::class, 'kuis_menjodohkan_id');
    }
}
