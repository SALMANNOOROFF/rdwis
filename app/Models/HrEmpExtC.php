<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEmpExtC extends Model
{
    protected $table = 'hr.empsextc';
    protected $primaryKey = 'empextc_emp_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'empextc_emp_id',
        'emp_cnum',
        'emp_cissuedt',
        'emp_cexpdt',
        'emp_secclear',
    ];
}
