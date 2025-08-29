<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KuisMenjodohkan extends Model
{
    use HasFactory, HasUuids;

    // Nama tabel perlu didefinisikan secara eksplisit
    protected $table = 'kuis_menjodohkan';

    protected $fillable = [
        'kelas_id',
        'judul',
        'deskripsi',
        'status',
    ];

    /**
     * Kuis ini memiliki banyak Item Pertanyaan.
     */
    public function itemPertanyaans(): HasMany
    {
        return $this->hasMany(ItemPertanyaan::class, 'kuis_id');
    }

    /**
     * Kuis ini memiliki banyak Histori Pengerjaan.
     */
    public function historiKuis(): HasMany
    {
        return $this->hasMany(HistoriKuis::class, 'kuis_id');
    }
}
