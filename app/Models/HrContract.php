<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrContract extends Model
{
    protected $table = 'hr.contracts';
    protected $primaryKey = 'ctr_id';
    public $timestamps = false;

    protected $fillable = [
        'ctr_unt_id',
        'ctr_num',
        'ctr_startdt',
        'ctr_enddt',
        'ctr_date',
        'ctr_jobtitle',
        'ctr_grade',
        'ctr_salary',
        'ctr_hed_id',
        'ctr_prob',
        'ctr_probsal',
        'ctr_termindt',
        'ctr_remarks',
        'ctr_path',
        'ctr_type',
        'ctr_path2',
        'ctr_ctc_id'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'ctr_num', 'emp_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'ctr_unt_id', 'unt_id');
    }

    public function case()
    {
        return $this->belongsTo(HrCtrCase::class, 'ctr_ctc_id', 'ctc_id');
    }
}
