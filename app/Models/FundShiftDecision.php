<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundShiftDecision extends Model
{
    protected $table = 'fin.fund_shift_decisions';
    protected $primaryKey = 'fsd_id';
    public $timestamps = false;

    protected $fillable = [
        'fsd_fsh_id',
        'fsd_acc_id',
        'fsd_user_name',
        'fsd_user_rank',
        'fsd_user_desig',
        'fsd_role',
        'fsd_action',
        'fsd_from_stage',
        'fsd_to_stage',
        'fsd_remarks',
        'fsd_dtg',
    ];

    protected $casts = [
        'fsd_dtg' => 'datetime',
    ];

    public function fundShift(): BelongsTo
    {
        return $this->belongsTo(FundShift::class, 'fsd_fsh_id', 'fsh_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fsd_acc_id', 'acc_id');
    }
}
