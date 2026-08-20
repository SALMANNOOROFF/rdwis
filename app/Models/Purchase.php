<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use \App\Traits\HorizonScoped;

    // Table and primary key
    protected $table = 'pur.purcases';
    protected $primaryKey = 'pcs_id';
    public $timestamps = false;

    public function getHorizonColumn()
    {
        return 'pcs_unt_id';
    }

    // Fillable fields for mass assignment
 protected $fillable = [
    'pcs_title',
    'pcs_date',
    'pcs_status',
    'pcs_type',
    'pcs_unt_id',
    'pcs_hed_id',
    'pcs_effhed_id',
    'pcs_effunt_id',
    'pcs_price',
    'pcs_remarks',
    'pcs_subject',
    'pcs_minute'
];


    /**
     * Relationship: Purchase belongs to a Project (Head)
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'pcs_hed_id', 'prj_id');
    }

    /**
     * Get the head or project name/code associated with this purchase case
     */
    public function getHeadDisplayAttribute()
    {
        $headId = $this->pcs_hed_id ?: $this->pcs_effhed_id;
        if (!$headId) {
            return !empty($this->pcs_sudohed) ? $this->pcs_sudohed : 'N/A';
        }

        // 1. Try cen.heads
        $headRec = \Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $headId)->first();
        if ($headRec) {
            if (!empty($headRec->hed_code) && $headRec->hed_code !== '000' && $headRec->hed_name === 'xxx') {
                return $headRec->hed_code;
            } elseif (!empty($headRec->hed_name) && $headRec->hed_name !== 'xxx') {
                return $headRec->hed_name . (!empty($headRec->hed_code) && $headRec->hed_code !== '000' ? " ({$headRec->hed_code})" : '');
            } elseif (!empty($headRec->hed_code) && $headRec->hed_code !== '000') {
                return $headRec->hed_code;
            } else {
                return $headRec->hed_name ?: ('Head #' . $headId);
            }
        }

        // 2. Try prj.projects
        $prj = \Illuminate\Support\Facades\DB::table('prj.projects')->where('prj_id', $headId)->first();
        if ($prj) {
            return $prj->prj_code ?: ($prj->prj_title ?: ('Project #' . $headId));
        }

        // 3. Fallback to sudohed or Head #ID
        return !empty($this->pcs_sudohed) ? $this->pcs_sudohed : ('Head #' . $headId);
    }

    /**
     * Purchase items
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'pci_pcs_id', 'pcs_id');
    }

    /**
     * Purchase quotes
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class, 'qte_pcs_id', 'pcs_id');
    }

    /**
     * Purchase no-quote records
     */
    public function noQuotes()
    {
        return $this->hasMany(NoQuote::class, 'nqt_pcs_id', 'pcs_id');
    }

    /**
     * Purchase attachments
     */
    public function attachments()
    {
        return $this->hasMany(PurAttachment::class, 'pat_objid', 'pcs_id');
    }

    /**
     * Approval trail decisions
     */
    public function decisions()
    {
        return $this->hasMany(PurDecision::class, 'pdec_pcs_id', 'pcs_id')->orderBy('pdec_id', 'desc');
    }

    /**
     * The single most recent decision for quick status context
    */
    public function latestDecision()
    {
        return $this->hasOne(PurDecision::class, 'pdec_pcs_id', 'pcs_id')->latestOfMany('pdec_id');
    }

    /**
     * Purchase belongs to a Unit (Division)
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'pcs_unt_id', 'unt_id');
    }

    /**
     * Purchase notifications
     */
    public function notifications()
    {
        return $this->hasMany(PurNotification::class, 'pnt_pcs_id', 'pcs_id');
    }

    /**
     * Relationship: Saved IT / RFQ Letter & Annex custom data
     */
    public function itLetter()
    {
        return $this->hasOne(PurItLetter::class, 'pit_pcs_id', 'pcs_id');
    }

    // ── Sub-Status Relationships ──────────────────────────────

    /**
     * The current routing substatus (which authority holds this case)
     */
    public function currentSubstatus()
    {
        return $this->hasOne(PurCaseSubstatus::class, 'pss_pcs_id', 'pcs_id')
                    ->where('pss_is_current', true);
    }

    /**
     * Full substatus history (most recent first)
     */
    public function substatusHistory()
    {
        return $this->hasMany(PurCaseSubstatus::class, 'pss_pcs_id', 'pcs_id')
                    ->orderBy('pss_id', 'desc');
    }

    /**
     * Query scope: filter cases by their current substatus stage.
     * Usage: Purchase::atStage('DFinance')->get()
     *        Purchase::atStage(['MD', 'DDG', 'DG'])->get()
     */
    public function scopeAtStage($query, $stage)
    {
        $stages = is_array($stage) ? $stage : [$stage];
        return $query->whereHas('currentSubstatus', function ($q) use ($stages) {
            $q->whereIn('pss_stage', $stages);
        });
    }

    /**
     * Accessor: Get the current stage name (e.g. 'DFinance', 'MD')
     * Returns null for terminal cases with no current substatus.
     */
    public function getCurrentStageAttribute(): ?string
    {
        return $this->currentSubstatus?->pss_stage;
    }

    /**
     * Accessor: Get human-readable display name for the current stage.
     * Returns null for terminal cases.
     */
    public function getCurrentStageDisplayAttribute(): ?string
    {
        return $this->currentSubstatus?->display_name;
    }
}

