<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAttendance extends Model
{
    protected $table = 'hr.attendance';
    protected $primaryKey = 'att_id';
    public $timestamps = false;

    protected $fillable = [
        'att_emp_id',
        'att_empnamecomp',
        'att_unt_id',
        'att_startdt',
        'att_enddt',
        'att_firstdt',
        'att_1',  'att_2',  'att_3',  'att_4',  'att_5',  'att_6',  'att_7',  'att_8',  'att_9',  'att_10',
        'att_11', 'att_12', 'att_13', 'att_14', 'att_15', 'att_16', 'att_17', 'att_18', 'att_19', 'att_20',
        'att_21', 'att_22', 'att_23', 'att_24', 'att_25', 'att_26', 'att_27', 'att_28', 'att_29', 'att_30',
        'att_31',
        'att_locked1',
        'att_locked2',
        'att_eahreplace',
        'att_locked',
    ];

    protected $casts = [
        'att_startdt'    => 'date',
        'att_enddt'      => 'date',
        'att_firstdt'    => 'date',
        'att_locked1'    => 'boolean',
        'att_locked2'    => 'boolean',
        'att_eahreplace' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'att_emp_id', 'emp_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'att_unt_id', 'unt_id');
    }

    public function remarks(): HasMany
    {
        return $this->hasMany(HrAttendanceRemark::class, 'atr_att_id', 'att_id');
    }
}
