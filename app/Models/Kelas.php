<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasUuids;

    protected $table = 'kelas'; // Eksplisit mendefinisikan nama tabel

    protected $fillable = [
        'nama',
        'sekolah_id',
        'guru_pengampu_id',
    ];

    /**
     * Sebuah Kelas dimiliki oleh satu Sekolah.
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function guruPengampu(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_pengampu_id');
    }

    /**
     * Sebuah Kelas memiliki banyak Siswa (User).
     */
    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'siswa_perkelas', 'kelas_id', 'user_id');
    }

    public function ujians(): HasMany
    {
        return $this->hasMany(Ujian::class);
    }

    public function kuisMenjodohkan(): HasMany
    {
        return $this->hasMany(KuisMenjodohkan::class);
    }
}
