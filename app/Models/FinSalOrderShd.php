<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinSalOrderShd extends Model
{
    protected $table = 'fin.salorders_shd';

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'sod_sor_id',
        'sod_type',
        'sod_subhead',
        'sod_ratio',
    ];

    protected $casts = [
        'sod_sor_id' => 'integer',
        'sod_ratio'  => 'float',
    ];

    public function salOrder()
    {
        return $this->belongsTo(FinSalOrder::class, 'sod_sor_id', 'sor_id');
    }
}
