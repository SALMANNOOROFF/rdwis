<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrCtrCase extends Model
{
    protected $table = 'hr.ctrcases';
    protected $primaryKey = 'ctc_id';
    public $timestamps = false;

    protected $guarded = []; // Mass assignment allowed

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->ctc_date)) {
                $model->ctc_date = now();
            }
            if (!isset($model->ctc_ctr_id)) {
                $model->ctc_ctr_id = 0;
            }
            if (!isset($model->ctc_unt_id)) {
                $model->ctc_unt_id = $model->ctc_divisionid ?: 1;
            }
            if (!isset($model->ctc_newunt_id)) {
                $model->ctc_newunt_id = $model->ctc_unt_id;
            }
            if (!isset($model->ctc_newctrtype)) {
                $model->ctc_newctrtype = ($model->ctc_emp_type === 'Part Time') ? 2 : 1;
            }
            if (!isset($model->ctc_status)) {
                $model->ctc_status = 'Draft';
            }
            // Auto-populate approved fields defaults from proposed terms
            if (!isset($model->ctc_approvedunt_id)) {
                $model->ctc_approvedunt_id = $model->ctc_newunt_id;
            }
            if (!isset($model->ctc_approvedstartdt)) {
                $model->ctc_approvedstartdt = $model->ctc_newstartdt;
            }
            if (!isset($model->ctc_approvedenddt)) {
                $model->ctc_approvedenddt = $model->ctc_newenddt;
            }
            if (!isset($model->ctc_approvedgrade)) {
                $model->ctc_approvedgrade = $model->ctc_newgrade;
            }
            if (!isset($model->ctc_approvedjobtitle)) {
                $model->ctc_approvedjobtitle = $model->ctc_newjobtitle;
            }
            if (!isset($model->ctc_approvedsalary)) {
                $model->ctc_approvedsalary = $model->ctc_newsalary;
            }
            if (!isset($model->ctc_approvedctrtype)) {
                $model->ctc_approvedctrtype = $model->ctc_newctrtype;
            }
        });
    }

    public function employee()
    {
        return $this->belongsTo(HrEmployee::class, 'ctc_emp_id', 'emp_id');
    }

    public function previousContract()
    {
        return $this->belongsTo(HrContract::class, 'ctc_ctr_id', 'ctr_id');
    }

    public function newContract()
    {
        return $this->belongsTo(HrContract::class, 'ctc_newctr_id', 'ctr_id');
    }

    public function casePlans()
    {
        return $this->hasMany(HrCtrCasePlan::class, 'ccp_ctc_id', 'ctc_id');
    }

    public function attachments()
    {
        return $this->hasMany(HrCtrCaseAttachment::class, 'cat_objid', 'ctc_id')
                    ->whereIn('cat_objtype', ['ctc', 'HrCtrCase']);
    }

    public function unit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'ctc_unt_id', 'unt_id');
    }

    public function division()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'ctc_divisionid', 'unt_id');
    }

    public function newUnit()
    {
        return $this->belongsTo(\App\Models\Unit::class, 'ctc_newunt_id', 'unt_id');
    }

    public function getDivisionNameAttribute(): string
    {
        if ($this->division && !empty($this->division->unt_name)) {
            return $this->division->unt_name;
        }
        if ($this->unit && !empty($this->unit->unt_name)) {
            return $this->unit->unt_name;
        }
        if ($this->newUnit && !empty($this->newUnit->unt_name)) {
            return $this->newUnit->unt_name;
        }
        if (!empty($this->ctc_divisionid)) {
            $u = \App\Models\Unit::find($this->ctc_divisionid);
            if ($u && !empty($u->unt_name)) return $u->unt_name;
        }
        if (!empty($this->ctc_unt_id)) {
            $u = \App\Models\Unit::find($this->ctc_unt_id);
            if ($u && !empty($u->unt_name)) return $u->unt_name;
        }
        return 'Headquarters / Directorate';
    }

    public function getDivisionShortAttribute(): string
    {
        if ($this->division && !empty($this->division->unt_namesh)) {
            return $this->division->unt_namesh;
        }
        if ($this->unit && !empty($this->unit->unt_namesh)) {
            return $this->unit->unt_namesh;
        }
        if ($this->newUnit && !empty($this->newUnit->unt_namesh)) {
            return $this->newUnit->unt_namesh;
        }
        if (!empty($this->ctc_divisionid)) {
            $u = \App\Models\Unit::find($this->ctc_divisionid);
            if ($u && !empty($u->unt_namesh)) return $u->unt_namesh;
        }
        if (!empty($this->ctc_unt_id)) {
            $u = \App\Models\Unit::find($this->ctc_unt_id);
            if ($u && !empty($u->unt_namesh)) return $u->unt_namesh;
        }
        return '';
    }

    public function remarksHistory()
    {
        return $this->hasMany(HrCtrCaseRemark::class, 'crr_ctc_id', 'ctc_id')->orderBy('crr_id', 'desc');
    }

    // ── Sub-Status Relationships ──────────────────────────────

    /**
     * The current routing substatus (which authority currently holds this case)
     */
    public function currentSubstatus()
    {
        return $this->hasOne(HrCtrCaseSubstatus::class, 'css_ctc_id', 'ctc_id')
                    ->where('css_is_current', true);
    }

    /**
     * Full substatus history (most recent first)
     */
    public function substatusHistory()
    {
        return $this->hasMany(HrCtrCaseSubstatus::class, 'css_ctc_id', 'ctc_id')
                    ->orderBy('css_id', 'desc');
    }

    /**
     * Query scope: filter cases by their current substatus stage.
     * Usage: HrCtrCase::atStage('Finance')->get()
     *        HrCtrCase::atStage(['MD', 'DDG', 'DG'])->get()
     */
    public function scopeAtStage($query, $stage)
    {
        $stages = is_array($stage) ? $stage : [$stage];
        return $query->whereHas('currentSubstatus', function ($q) use ($stages) {
            $q->whereIn('css_stage', $stages);
        });
    }

    /**
     * Accessor: Get current holder stage name
     */
    public function getCurrentStageAttribute(): ?string
    {
        return $this->currentSubstatus->css_stage ?? null;
    }
}
