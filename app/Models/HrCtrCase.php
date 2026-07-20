<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrCtrCase extends Model
{
    protected $table = 'hr.ctrcases';
    protected $primaryKey = 'ctc_id';
    public $timestamps = false; // Manually managing timestamps for createdat/releasedat

    protected $guarded = []; // Mass assignment allowed

    public function employee()
    {
        return $this->belongsTo(HrEmployee::class, 'ctc_emp_id', 'emp_id');
    }

    public function casePlans()
    {
        return $this->hasMany(HrCtrCasePlan::class, 'ccp_ctc_id', 'ctc_id');
    }

    public function attachments()
    {
        return $this->hasMany(HrCtrCaseAttachment::class, 'cat_objid', 'ctc_id')->where('cat_objtype', 'HrCtrCase');
    }

    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'ctc_unt_id', 'unt_id');
    }
}
