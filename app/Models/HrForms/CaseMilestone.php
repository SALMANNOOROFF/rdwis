<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;

class CaseMilestone extends Model
{
    protected $table = 'hrforms.case_milestones';
    protected $guarded = [];

    protected $casts = [
        'case_id'    => 'integer',
        'event_date' => 'date',
        'updated_by' => 'integer',
    ];
}
