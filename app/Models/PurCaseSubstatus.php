<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurCaseSubstatus extends Model
{
    protected $table = 'pur.purcase_substatus';
    protected $primaryKey = 'pss_id';
    public $timestamps = false;

    protected $fillable = [
        'pss_pcs_id',
        'pss_stage',
        'pss_is_current',
        'pss_since',
        'pss_until',
    ];

    protected $casts = [
        'pss_is_current' => 'boolean',
        'pss_since'      => 'datetime',
        'pss_until'      => 'datetime',
    ];

    /**
     * Human-readable display names for each stage
     */
    const STAGE_DISPLAY_NAMES = [
        'Division'  => 'Division (Initiator)',
        'DFinance'  => 'Director Finance',
        'MD'        => 'MD Office',
        'DDG'       => 'DDG Office',
        'DG'        => 'Director General',
    ];

    /**
     * Map stage name → area code used in cen.accounts.acc_untarea
     */
    const STAGE_TO_AREA = [
        'Division'  => 'prj',
        'DFinance'  => 'fin',
        'MD'        => 'rdw',
        'DDG'       => 'hqs',
        'DG'        => 'nrdi',
    ];

    /**
     * Map area code → stage name (reverse of STAGE_TO_AREA)
     */
    const AREA_TO_STAGE = [
        'prj'  => 'Division',
        'fin'  => 'DFinance',
        'rdw'  => 'MD',
        'hqs'  => 'DDG',
        'nrdi' => 'DG',
    ];

    /**
     * Relationship: The purchase case this substatus belongs to
     */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'pss_pcs_id', 'pcs_id');
    }

    /**
     * Get display name for this stage
     */
    public function getDisplayNameAttribute(): string
    {
        return self::STAGE_DISPLAY_NAMES[$this->pss_stage] ?? $this->pss_stage;
    }
}
