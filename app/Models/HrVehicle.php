<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrVehicle extends Model
{
    protected $table = 'hr.vehicles';
    protected $primaryKey = 'vcl_id';
    public $timestamps = false;

    protected $fillable = [
        'vcl_emp_id',
        'vcl_type',
        'vcl_maker',
        'vcl_variant',
        'vcl_year',
        'vcl_regis',
        'vcl_color',
    ];
}
