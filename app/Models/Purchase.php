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
    'pcs_minute',
    // Legacy two-stage tax cascade (see App\Services\PurchasePricingService):
    // intprice + inttax = midprice, midprice + midtax = price
    'pcs_intprice',
    'pcs_inttax',
    'pcs_midprice',
    'pcs_midtax',
    'pcs_quotetype',
    'pcs_frm_id',
    'pcs_recomm',
    'pcs_intunt_id',
    'pcs_transtype',
    'pcs_noloan'
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
     * Accessor: tax-inclusive case value (legacy pcs_price).
     *
     * Reads the stored cascade first - historical rates differ from today's
     * (GST has been 17% and 18%, SST 10/13/15%), so a recomputation would show a
     * figure the file never carried. Quotes and item lines are only a fallback for
     * cases that have not been priced yet.
     */
    public function getLiveValueAttribute(): float
    {
        $total = (float) ($this->pcs_price ?? 0);
        if ($total > 0) {
            return $total;
        }

        // Rebuild from whatever part of the cascade is present
        $base = (float) ($this->pcs_intprice ?? 0);
        $sst  = (float) ($this->pcs_inttax ?? 0);
        $mid  = (float) ($this->pcs_midprice ?? 0) ?: ($base + $sst);
        $total = $mid + (float) ($this->pcs_midtax ?? 0);
        if ($total > 0) {
            return $total;
        }

        // Still unpriced: the selected quote, then the item lines
        $quote = $this->winning_quote;
        if ($quote) {
            $qTotal = (float) ($quote->qte_price ?: ($quote->qte_midprice ?: ($quote->qte_intprice ?? 0)));
            if ($qTotal > 0) {
                return $qTotal;
            }
        }

        return $this->itemsBaseTotal();
    }

    /**
     * Accessor: base value before SST and GST (legacy pcs_intprice).
     *
     * For a quotetype 1 case this is exactly the selected quote's amount - the
     * quote holds base prices and the case adds the tax - which is why the list
     * column and the case page now agree.
     */
    public function getWithoutGstPriceAttribute(): float
    {
        $base = (float) ($this->pcs_intprice ?? 0);
        if ($base > 0) {
            return $base;
        }

        $mid = (float) ($this->pcs_midprice ?? 0);
        $sst = (float) ($this->pcs_inttax ?? 0);
        if ($mid > 0) {
            return max(0, $mid - $sst);
        }

        $total = (float) ($this->pcs_price ?? 0);
        if ($total > 0) {
            return max(0, $total - $sst - (float) ($this->pcs_midtax ?? 0));
        }

        $quote = $this->winning_quote;
        if ($quote) {
            $qBase = (float) ($quote->qte_intprice ?: 0);
            if ($qBase > 0) {
                return $qBase;
            }
            $qTotal = (float) ($quote->qte_price ?? 0);
            if ($qTotal > 0) {
                return max(0, $qTotal - (float) ($quote->qte_inttax ?? 0) - (float) ($quote->qte_midtax ?? 0));
            }
        }

        return $this->itemsBaseTotal();
    }

    /**
     * Sum of the case's own item lines (price x qty), tax free.
     */
    public function itemsBaseTotal(): float
    {
        if (!$this->relationLoaded('items') && !$this->exists) {
            return 0.0;
        }

        return (float) $this->items->sum(function ($item) {
            $qty = (float) ($item->pci_qty ?? 1);
            if ($qty <= 0) {
                $qty = 1;
            }
            $rate = (float) ($item->pci_price ?: ($item->pci_estprice ?? 0));

            return $rate > 0 ? $rate * $qty : 0;
        });
    }

    /**
     * Accessor: Base / SST / GST / Total split for display, from the stored fields.
     *
     * @return array{base: float, sst: float, mid: float, gst: float, total: float,
     *               tax: float, sst_pct: ?float, gst_pct: ?float, has_tax: bool,
     *               derived: bool, balanced: bool}
     */
    public function getTaxBreakdownAttribute(): array
    {
        $bases = null;

        // Only when the items are already in memory - never add a query per row
        if ($this->relationLoaded('items')) {
            $service = 0.0;
            $goods   = 0.0;
            foreach ($this->items as $item) {
                $qty  = (float) ($item->pci_qty ?? 1) ?: 1;
                $line = (float) ($item->pci_price ?? 0) * $qty;
                if ((int) $item->pci_type === \App\Services\PurchasePricingService::TYPE_SERVICE) {
                    $service += $line;
                } else {
                    $goods += $line;
                }
            }
            $bases = ['service' => $service, 'goods' => $goods];
        }

        return \App\Services\PurchasePricingService::breakdown($this, $bases);
    }

    /**
     * Accessor: SST + GST carried on this case.
     */
    public function getTotalTaxAttribute(): float
    {
        return round((float) ($this->pcs_inttax ?? 0) + (float) ($this->pcs_midtax ?? 0), 2);
    }

    /**
     * Accessor: Get price used for authority limit evaluation (dynamically based on RDWIS settings)
     */
    public function getEffectiveEvaluationPriceAttribute(): float
    {
        $basis = \App\Models\SystemSetting::get('pur_threshold_basis', 'without_gst');
        return ($basis === 'with_gst') ? $this->live_value : $this->without_gst_price;
    }

    /**
     * Accessor: Get price displayed in Hub tables / lists (dynamically based on RDWIS settings)
     */
    public function getDisplayPriceAttribute(): float
    {
        $basis = \App\Models\SystemSetting::get('pur_list_amount_basis', 'without_gst');
        return ($basis === 'with_gst') ? $this->live_value : $this->without_gst_price;
    }

    /**
     * Accessor: the selected quote.
     *
     * Selection is data, not arithmetic: legacy writes qte_recomm = 1 on exactly one
     * quote per case (Queries/pur_quotes_recomm.sql), so never infer it by comparing
     * prices. Only a case whose quotes have not been compared yet falls back to the
     * cheapest technically acceptable offer, which is what legacy would have picked.
     */
    public function getWinningQuoteAttribute()
    {
        $quotes = $this->quotes;
        if (!$quotes || $quotes->isEmpty()) {
            return null;
        }

        $selected = $quotes->first(fn($q) => (bool) $q->qte_recomm);
        if ($selected) {
            return $selected;
        }

        $acceptable = $quotes->filter(fn($q) => (bool) $q->qte_techaccept);

        return ($acceptable->isNotEmpty() ? $acceptable : $quotes)
            ->sortBy(fn($q) => (float) ($q->qte_price ?: ($q->qte_midprice ?: ($q->qte_intprice ?? 0))))
            ->first();
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
     * The single selected quote (legacy qte_recomm). Eager-loadable counterpart of
     * the winning_quote accessor.
     */
    public function recommendedQuote()
    {
        return $this->hasOne(Quote::class, 'qte_pcs_id', 'pcs_id')->where('qte_recomm', true);
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

