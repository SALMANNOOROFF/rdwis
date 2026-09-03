<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrQualif extends Model
{
    protected $table = 'hr.qualifs';
    protected $primaryKey = 'qlf_id';
    public $timestamps = false;

    protected $fillable = [
        'qlf_type',
        'qlf_level',
        'qlf_name',
        'qlf_inst',
        'qlf_duration',
        'qlf_unit',
        'qlf_enddt',
        'qlf_emp_id',
        'qlf_grade',
        'qlf_license',
        'qlf_spec',
    ];
}
