<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'hr.emps';
    protected $primaryKey = 'emp_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'emp_id',
        'emp_cnic',
        'emp_name',
        'emp_joindt',
        'emp_locked',
        'emp_rank',
        'emp_status',
        'emp_remarks',
        'emp_unt_id',
        'emp_hed_id',
        'emp_lastdt',
        'emp_title',
        'emp_photodest'
    ];
}

