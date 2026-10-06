<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailProbabilitas extends Model
{
    use HasUuids;

    protected $table = 'detail_probabilitas';
    protected $primaryKey = 'id_detail';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_klasifikasi',
        'kode_penyakit',
        'nilai_probabilitas',
    ];

    protected $casts = [
        'nilai_probabilitas' => 'double',
    ];

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(Klasifikasi::class, 'id_klasifikasi', 'id_klasifikasi');
    }

    public function penyakit(): BelongsTo
    {
        return $this->belongsTo(Penyakit::class, 'kode_penyakit', 'kode_penyakit');
    }
}
