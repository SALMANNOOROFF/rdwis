<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;

class SalaryBand extends Model
{
    protected $table = 'hrforms.salary_bands';
    protected $guarded = [];

    protected $casts = [
        'sub_grades' => 'array',
        'min_salary' => 'float',
        'max_salary' => 'float',
        'is_active' => 'boolean',
    ];
}
