<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEmpExtB extends Model
{
    protected $table = 'hr.empsextb';
    protected $primaryKey = 'empextb_emp_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'empextb_emp_id',
        'emp_nokname',
        'emp_nokrelation',
        'emp_nokcnic',
        'emp_emername',
        'emp_emerrelation',
        'emp_emermobile',
        'emp_idmark',
        'emp_height',
        'emp_caste',
        'emp_religion',
        'emp_sect',
        'emp_police',
        'emp_political',
    ];
}
