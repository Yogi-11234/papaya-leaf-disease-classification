<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pohon extends Model
{
    use HasUuids;

    protected $table = 'pohon';
    protected $primaryKey = 'id_pohon';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_pohon',
        'grup',
    ];

    public function klasifikasi(): HasMany
    {
        return $this->hasMany(Klasifikasi::class, 'id_pohon', 'id_pohon');
    }

    public function konsultasiChat(): HasMany
    {
        return $this->hasMany(KonsultasiChat::class, 'id_pohon', 'id_pohon');
    }
}
