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

    public function getIsHrAdminAttribute(): bool
    {
        $divId = (int)($this->ctc_divisionid ?: ($this->ctc_unt_id ?: ($this->ctc_newunt_id ?: 0)));
        if (in_array($divId, [820000, 840000, 880000, 10000, 100000, 800000, 810000, 860000])) {
            return true;
        }
        $name = strtolower($this->division_name ?? '');
        $short = strtolower($this->division_short ?? '');
        return str_contains($name, 'hr') || str_contains($name, 'human') ||
               str_contains($name, 'admin') || str_contains($name, 'information system') ||
               str_contains($name, 'is department') || str_contains($name, 'head quarter') ||
               in_array($short, ['hr', 'admin', 'is', 'hqs', 'it', 'ext']);
    }

    public function getDefaultProjectCodeAttribute(): string
    {
        return $this->is_hr_admin ? 'CSRF' : 'Core';
    }

    public function getDefaultProjectNameAttribute(): string
    {
        return $this->is_hr_admin ? 'Center Special Research Fund (CSRF)' : 'Institutional Core Budget';
    }

    public function getProjectCodeAttribute(): string
    {
        if ($this->is_hr_admin) {
            return 'CSRF';
        }
        $plan = $this->casePlans->first();
        if ($plan && $plan->project && !empty($plan->project->prj_code)) {
            return $plan->project->prj_code;
        }
        if ($plan && !empty($plan->ccp_hed_id)) {
            $hedCode = \Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $plan->ccp_hed_id)->value('hed_code');
            if ($hedCode) return $hedCode;
        }
        return 'Core';
    }

    public function getProjectNameAttribute(): string
    {
        if ($this->is_hr_admin) {
            return 'Center Special Research Fund (CSRF)';
        }
        $plan = $this->casePlans->first();
        if ($plan && $plan->project && !empty($plan->project->prj_name)) {
            return $plan->project->prj_name;
        }
        if ($plan && !empty($plan->ccp_hed_id)) {
            $hedName = \Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $plan->ccp_hed_id)->value('hed_name');
            if ($hedName) return $hedName;
        }
        return 'Institutional Core Budget';
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

    public function getCurrentOfficeNameAttribute(): string
    {
        $stage = $this->current_stage ?? 'Division';
        return match($stage) {
            'Division'     => 'Division Office' . ($this->division_short ? ' (' . $this->division_short . ')' : ''),
            'HR'           => 'HR Directorate (Scrutiny)',
            'Finance'      => 'Finance Directorate (Scrutiny)',
            'MD'           => 'MD Office (Under Review)',
            'DDG'          => 'DDG Office (Under Review)',
            'DG'           => 'DG Office (Final Review)',
            'Approved'     => 'Approved by Competent Authority',
            'Fulfilled'    => 'Completed / Fulfilled',
            'Not Approved' => 'Not Approved',
            'Cancelled'    => 'Cancelled',
            default        => $stage . ' Office'
        };
    }

    public function getEffectivePreviousContractAttribute()
    {
        if ($this->previousContract) {
            return $this->previousContract;
        }
        if (!empty($this->ctc_ctr_id)) {
            $c = HrContract::find($this->ctc_ctr_id);
            if ($c) return $c;
        }
        if (!empty($this->ctc_emp_id)) {
            return HrContract::where('ctr_num', $this->ctc_emp_id)
                ->where('ctr_id', '!=', $this->ctc_newctr_id ?? 0)
                ->orderBy('ctr_enddt', 'desc')
                ->first();
        }
        return null;
    }

    public function getPreviousSalaryAttribute(): ?float
    {
        $prevCtr = $this->effective_previous_contract;
        if ($prevCtr && isset($prevCtr->ctr_salary)) {
            return (float)$prevCtr->ctr_salary;
        }
        return null;
    }

    public function getPreviousGradeAttribute(): ?string
    {
        $prevCtr = $this->effective_previous_contract;
        if ($prevCtr && !empty($prevCtr->ctr_grade)) {
            return $prevCtr->ctr_grade;
        }
        if ($this->employee && !empty($this->employee->emp_grade)) {
            return $this->employee->emp_grade;
        }
        return null;
    }

    public function getPreviousJobtitleAttribute(): ?string
    {
        $prevCtr = $this->effective_previous_contract;
        if ($prevCtr && !empty($prevCtr->ctr_jobtitle)) {
            return $prevCtr->ctr_jobtitle;
        }
        if ($this->employee && !empty($this->employee->emp_desig)) {
            return $this->employee->emp_desig;
        }
        return null;
    }

    public function getPreviousStartdtAttribute(): ?string
    {
        $prevCtr = $this->effective_previous_contract;
        return $prevCtr?->ctr_startdt ?? null;
    }

    public function getPreviousEnddtAttribute(): ?string
    {
        $prevCtr = $this->effective_previous_contract;
        return $prevCtr?->ctr_enddt ?? null;
    }

    public function getFatherNameAttribute(): ?string
    {
        if (!empty($this->ctc_emp_id)) {
            $val = \Illuminate\Support\Facades\DB::table('hr.empsexta')->where('empexta_emp_id', $this->ctc_emp_id)->value('emp_father');
            if (!empty($val)) return $val;
        }
        if (!empty($this->ctc_cnic)) {
            $val = \Illuminate\Support\Facades\DB::table('hr.applicants')->where('apl_cnic', $this->ctc_cnic)->value('apl_father');
            if (!empty($val)) return $val;
        }
        if ($this->employee && !empty($this->employee->emp_fathername)) {
            return $this->employee->emp_fathername;
        }
        return null;
    }

    public function getCandidateCnicAttribute(): ?string
    {
        if (!empty($this->ctc_cnic)) {
            return $this->ctc_cnic;
        }
        if ($this->employee && !empty($this->employee->emp_cnic)) {
            return $this->employee->emp_cnic;
        }
        if (!empty($this->ctc_emp_id)) {
            $val = \Illuminate\Support\Facades\DB::table('hr.emps')->where('emp_id', $this->ctc_emp_id)->value('emp_cnic');
            if (!empty($val)) return $val;
        }
        return null;
    }

    public function getCandidateMobileAttribute(): ?string
    {
        if (!empty($this->ctc_contact)) {
            return $this->ctc_contact;
        }
        if (!empty($this->ctc_emp_id)) {
            $val = \Illuminate\Support\Facades\DB::table('hr.empsexta')->where('empexta_emp_id', $this->ctc_emp_id)->value('emp_mobile');
            if (!empty($val)) return $val;
        }
        if (!empty($this->ctc_cnic)) {
            $val = \Illuminate\Support\Facades\DB::table('hr.applicants')->where('apl_cnic', $this->ctc_cnic)->value('apl_mobile');
            if (!empty($val)) return $val;
        }
        return null;
    }

    public function getCandidateEmailAttribute(): ?string
    {
        if (!empty($this->ctc_emp_id)) {
            $val = \Illuminate\Support\Facades\DB::table('hr.empsexta')->where('empexta_emp_id', $this->ctc_emp_id)->value('emp_email');
            if (!empty($val)) return $val;
        }
        if (!empty($this->ctc_cnic)) {
            $val = \Illuminate\Support\Facades\DB::table('hr.applicants')->where('apl_cnic', $this->ctc_cnic)->value('apl_email');
            if (!empty($val)) return $val;
        }
        return null;
    }
}

