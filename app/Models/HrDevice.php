<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrDevice extends Model
{
    protected $table = 'hr.devices';
    protected $primaryKey = 'dvc_id';
    public $timestamps = false;

    protected $fillable = [
        'dvc_emp_id',
        'dvc_type',
        'dvc_brand',
        'dvc_model',
        'dvc_imei1',
        'dvc_imei2',
    ];
}
