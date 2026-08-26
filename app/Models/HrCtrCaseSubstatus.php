<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrCtrCaseSubstatus extends Model
{
    protected $table = 'hr.ctrcase_substatus';
    protected $primaryKey = 'css_id';
    public $timestamps = false;

    protected $fillable = [
        'css_ctc_id',
        'css_stage',
        'css_is_current',
        'css_since',
        'css_until',
    ];

    protected $casts = [
        'css_is_current' => 'boolean',
        'css_since'      => 'datetime',
        'css_until'      => 'datetime',
    ];

    /**
     * Human-readable display names for each stage
     */
    const STAGE_DISPLAY_NAMES = [
        'Division'     => 'Division (Initiator)',
        'HR'           => 'HR Scrutiny',
        'Finance'      => 'Director Finance',
        'MD'           => 'MD Office',
        'DDG'          => 'DDG Office',
        'DG'           => 'Director General',
        'Approved'     => 'Approved (Ready to Fulfill)',
        'Fulfilled'    => 'Fulfilled',
        'Not Approved' => 'Not Approved',
        'Cancelled'    => 'Cancelled',
    ];

    /**
     * Map stage name → area code used in cen.accounts.acc_untarea
     */
    const STAGE_TO_AREA = [
        'Division' => 'prj',
        'HR'       => 'hr',
        'Finance'  => 'fin',
        'MD'       => 'rdw',
        'DDG'      => 'hqs',
        'DG'       => 'nrdi',
        'Approved' => 'hr',
    ];

    /**
     * Map area code → stage name
     */
    const AREA_TO_STAGE = [
        'prj'    => 'Division',
        'rdwprj' => 'Division',
        'hr'     => 'HR',
        'fin'    => 'Finance',
        'rdw'    => 'MD',
        'hqs'    => 'DDG',
        'nrdi'   => 'DG',
    ];

    /**
     * Relationship: The contract case this substatus belongs to
     */
    public function case()
    {
        return $this->belongsTo(HrCtrCase::class, 'css_ctc_id', 'ctc_id');
    }

    /**
     * Get display name for this stage
     */
    public function getDisplayNameAttribute(): string
    {
        return self::STAGE_DISPLAY_NAMES[$this->css_stage] ?? $this->css_stage;
    }
}
