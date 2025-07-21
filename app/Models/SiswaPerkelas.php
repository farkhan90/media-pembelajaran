<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SiswaPerkelas extends Model
{

    protected $table = 'siswa_perkelas';

    protected $fillable = [
        'user_id',
        'kelas_id',
    ];
}
