<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatasetDistribusi extends Model
{
    protected $table = 'dataset_distribusi';
    protected $primaryKey = 'kode_penyakit';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode_penyakit',
        'jumlah_citra',
    ];

    public function penyakit(): BelongsTo
    {
        return $this->belongsTo(Penyakit::class, 'kode_penyakit', 'kode_penyakit');
    }
}
