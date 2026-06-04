<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrContractPlan extends Model
{
    protected $table = 'hr.contractplans';
    protected $primaryKey = 'cpn_id';

    protected $guarded = [];

    public function contractCase()
    {
        return $this->belongsTo(HrCtrCase::class, 'ctc_id', 'ctc_id');
    }
}
