<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penyakit extends Model
{
    protected $table = 'penyakit';
    protected $primaryKey = 'kode_penyakit';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_penyakit',
        'nama_penyakit',
        'deskripsi',
        'rekomendasi_penanganan',
    ];

    public function klasifikasi(): HasMany
    {
        return $this->hasMany(Klasifikasi::class, 'kode_penyakit', 'kode_penyakit');
    }

    public function datasetDistribusi(): HasMany
    {
        return $this->hasMany(DatasetDistribusi::class, 'kode_penyakit', 'kode_penyakit');
    }
}
