<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Klasifikasi extends Model
{
    use HasUuids;

    protected $table = 'klasifikasi';
    protected $primaryKey = 'id_klasifikasi';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_pohon',
        'kode_penyakit',
        'path_citra',
        'label_prediksi',
        'confidence_score',
        'waktu_klasifikasi',
    ];


    protected $casts = [
        'waktu_klasifikasi' => 'datetime',
        'confidence_score' => 'double',
    ];

    public function pohon(): BelongsTo
    {
        return $this->belongsTo(Pohon::class, 'id_pohon', 'id_pohon');
    }

    public function penyakit(): BelongsTo
    {
        return $this->belongsTo(Penyakit::class, 'kode_penyakit', 'kode_penyakit');
    }

    public function detailProbabilitas(): HasMany
    {
        return $this->hasMany(DetailProbabilitas::class, 'id_klasifikasi', 'id_klasifikasi');
    }

    public function konsultasiChat(): HasMany
    {
        return $this->hasMany(KonsultasiChat::class, 'id_klasifikasi', 'id_klasifikasi');
    }
}
