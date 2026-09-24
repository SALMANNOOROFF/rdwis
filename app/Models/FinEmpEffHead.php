<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinEmpEffHead extends Model
{
    protected $table = 'fin.empeffheads';
    protected $primaryKey = 'eeh_emp_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'eeh_emp_id',
        'eeh_emphed_id',
        'eeh_dtg',
        'eeh_status',
        'eeh_remarks',
        'eeh_sudohed',
    ];

    protected $casts = [
        'eeh_dtg' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'eeh_emp_id', 'emp_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(CenHead::class, 'eeh_emphed_id', 'hed_id');
    }
}
