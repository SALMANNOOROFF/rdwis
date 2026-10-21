<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinSubhead extends Model
{
    protected $table = 'fin.subheads';
    protected $primaryKey = 'sbh_id';
    public $timestamps = false;

    protected $fillable = [
        'sbh_hed_id',
        'sbh_name',
        'sbh_alloc',
    ];

    protected $casts = [
        'sbh_alloc' => 'decimal:2',
    ];

    public function head(): BelongsTo
    {
        return $this->belongsTo(CenHead::class, 'sbh_hed_id', 'hed_id');
    }
}
