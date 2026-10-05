<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;

class ProjectExtra extends Model
{
    protected $table = 'hrforms.project_extras';
    protected $guarded = [];

    protected $casts = [
        'project_id' => 'integer',
        'work_order_date' => 'date',
        'warranty_expiry' => 'date',
        'approved_headcount' => 'integer',
        'extra_data' => 'array',
    ];
}
