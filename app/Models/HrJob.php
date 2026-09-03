<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrJob extends Model
{
    protected $table = 'hr.jobs';
    protected $primaryKey = 'job_id';
    public $timestamps = false;

    protected $fillable = [
        'job_company',
        'job_jobtitle',
        'job_repto',
        'job_team',
        'job_from',
        'job_to',
        'job_resp',
        'job_ach',
        'job_emp_id',
        'job_city',
    ];
}
