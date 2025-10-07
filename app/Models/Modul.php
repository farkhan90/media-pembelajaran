<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modul extends Model
{
    use HasUuids;
    protected $guarded = [];

    public function getRouteKeyName()
    {
        return 'nama_pulau';
    }

    public function langkahs(): HasMany
    {
        return $this->hasMany(Langkah::class)->orderBy('urutan');
    }
}
