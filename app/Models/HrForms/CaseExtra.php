<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;

class CaseExtra extends Model
{
    protected $table = 'hrforms.case_extras';
    protected $guarded = [];

    protected $casts = [
        'case_id' => 'integer',
        'headcount_in_proposal' => 'boolean',
        'interview_date' => 'date',
        'shortlisted_candidates' => 'array',
        'shift_amount' => 'float',
        'ex_post_facto' => 'boolean',
        'single_candidate_mode' => 'boolean',
        'advertisement_exemption' => 'boolean',
        'extra_data' => 'array',
    ];
}
