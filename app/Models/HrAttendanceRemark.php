<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAttendanceRemark extends Model
{
    protected $table = 'hr.attendanceremarks';
    protected $primaryKey = 'atr_id';
    public $timestamps = false;

    protected $fillable = [
        'atr_att_id',
        'atr_remarks',
        'atr_attday',
    ];

    protected $casts = [
        'atr_att_id' => 'integer',
        'atr_attday' => 'integer',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(HrAttendance::class, 'atr_att_id', 'att_id');
    }
}
