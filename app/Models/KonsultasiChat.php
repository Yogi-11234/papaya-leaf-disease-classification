<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KonsultasiChat extends Model
{
    use HasUuids;

    protected $table = 'konsultasi_chat';
    protected $primaryKey = 'id_chat';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_klasifikasi',
        'id_pohon',
        'role',
        'pesan',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function klasifikasi(): BelongsTo
    {
        return $this->belongsTo(Klasifikasi::class, 'id_klasifikasi', 'id_klasifikasi');
    }

    public function pohon(): BelongsTo
    {
        return $this->belongsTo(Pohon::class, 'id_pohon', 'id_pohon');
    }
}
