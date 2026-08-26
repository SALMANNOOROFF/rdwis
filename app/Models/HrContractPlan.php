<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrContractPlan extends Model
{
    protected $table = 'hr.contractplans';
    protected $primaryKey = 'cpn_id';
    public $timestamps = false;

    protected $fillable = [
        'cpn_ctr_id',
        'cpn_startdt',
        'cpn_enddt',
        'cpn_hed_id',
    ];

    public function contract()
    {
        return $this->belongsTo(HrContract::class, 'cpn_ctr_id', 'ctr_id');
    }

    public function head()
    {
        return $this->belongsTo(Project::class, 'cpn_hed_id', 'prj_id');
    }
}
