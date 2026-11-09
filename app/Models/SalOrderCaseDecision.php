<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalOrderCaseDecision extends Model
{
    protected $table = 'fin.sal_order_case_decisions';
    protected $primaryKey = 'scd_id';
    public $timestamps = false;

    protected $fillable = [
        'scd_soc_id',
        'scd_acc_id',
        'scd_user_name',
        'scd_user_rank',
        'scd_user_desig',
        'scd_role',
        'scd_action',
        'scd_from_stage',
        'scd_to_stage',
        'scd_remarks',
        'scd_dtg',
    ];

    protected $casts = [
        'scd_dtg' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(SalOrderCase::class, 'scd_soc_id', 'soc_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'scd_acc_id', 'acc_id');
    }
}
