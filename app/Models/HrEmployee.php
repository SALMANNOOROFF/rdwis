<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEmployee extends Model
{
    protected $table = 'hr.emps';
    protected $primaryKey = 'emp_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    public function contractCases()
    {
        return $this->hasMany(HrCtrCase::class, 'ctc_emp_id', 'emp_id');
    }
}
