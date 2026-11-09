<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAttendanceUnlock extends Model
{
    protected $table = 'hr.attendance_unlocks';
    protected $primaryKey = 'aul_id';
    public $timestamps = true;

    protected $fillable = [
        'aul_att_id',
        'aul_emp_id',
        'aul_date',
        'aul_day',
        'aul_status',
        'aul_case_id',
        'aul_case_file_no',
        'aul_remarks',
        'aul_acc_id',
        'aul_username',
        'aul_is_active',
        'aul_unlocked_at',
        'aul_relocked_at',
    ];

    protected $casts = [
        'aul_att_id'      => 'integer',
        'aul_day'         => 'integer',
        'aul_case_id'     => 'integer',
        'aul_acc_id'      => 'integer',
        'aul_is_active'   => 'boolean',
        'aul_date'        => 'date',
        'aul_unlocked_at' => 'datetime',
        'aul_relocked_at' => 'datetime',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(HrAttendance::class, 'aul_att_id', 'att_id');
    }

    public function caseFile(): BelongsTo
    {
        return $this->belongsTo(GenericCase::class, 'aul_case_id', 'gcs_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'aul_acc_id', 'acc_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmp::class, 'aul_emp_id', 'emp_id');
    }
}

