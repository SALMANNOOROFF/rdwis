<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrCtrCasePlan extends Model
{
    protected $table = 'hr.ctrcaseplans';
    protected $primaryKey = 'ccp_id';
    public $timestamps = false;

    protected $fillable = [
        'ccp_ctc_id',
        'ccp_startdt',
        'ccp_enddt',
        'ccp_hed_id'
    ];

    public function case()
    {
        return $this->belongsTo(HrCtrCase::class, 'ccp_ctc_id', 'ctc_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'ccp_hed_id', 'prj_id');
    }
}
