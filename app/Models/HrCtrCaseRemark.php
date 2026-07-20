<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrCtrCaseRemark extends Model
{
    protected $table = 'hr.ctrcaseremarks';
    protected $primaryKey = 'crr_id';
    public $timestamps = false;

    protected $fillable = [
        'crr_ctc_id',
        'crr_username',
        'crr_user_rank',
        'crr_user_desig',
        'crr_remarks',
        'crr_status',
        'crr_dtg'
    ];

    public function case()
    {
        return $this->belongsTo(HrCtrCase::class, 'crr_ctc_id', 'ctc_id');
    }
}
