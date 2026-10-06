<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModelMetricsFold extends Model
{
    protected $table = 'model_metrics_fold';
    protected $primaryKey = 'fold';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fold',
        'accuracy',
        'precision',
        'recall',
        'f1',
    ];

    protected $casts = [
        'accuracy' => 'double',
        'precision' => 'double',
        'recall' => 'double',
        'f1' => 'double',
    ];
}
