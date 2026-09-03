<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEmpExtA extends Model
{
    protected $table = 'hr.empsexta';
    protected $primaryKey = 'empexta_emp_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'empexta_emp_id',
        'emp_discip',
        'emp_qualif',
        'emp_spec',
        'emp_paddress',
        'emp_dob',
        'emp_marital',
        'emp_ntnlty',
        'emp_ntnlty_other',
        'emp_pob',
        'emp_taddress',
        'emp_mobile',
        'emp_mobile2',
        'emp_landline',
        'emp_gender',
        'emp_email',
        'emp_father',
        'emp_father_cnic',
    ];
}
