<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CenHead extends Model
{
    protected $table = 'cen.heads';
    protected $primaryKey = 'hed_id';
    public $timestamps = false;

    protected $fillable = [
        'hed_name',
        'hed_type',
        'hed_code',
        'hed_opendt',
        'hed_closedt',
        'hed_transtype',
        'hed_unt_id',
        'hed_prj_id',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'hed_unt_id', 'unt_id');
    }
}
