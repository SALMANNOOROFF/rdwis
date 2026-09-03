<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    // Table schema + name
    protected $table = 'pur.quotes';

    // Primary key
    protected $primaryKey = 'qte_id';

    // Disable timestamps if your table doesn't have created_at/updated_at
    public $timestamps = false;

    // Mass assignable fields
    protected $fillable = [
        'qte_pcs_id',
        'qte_date',
        'qte_firmname',
        'qte_price',
        'qte_num',
        'qte_techaccept',
        'qte_frm_id', // Add this if you want to assign foreign key too
        // Legacy two-stage price cascade: intprice + inttax = midprice, + midtax = price
        'qte_intprice',
        'qte_inttax',
        'qte_midprice',
        'qte_midtax',
        'qte_recomm',
        'qte_quotetype',
    ];

    protected $casts = [
        'qte_recomm'     => 'boolean',
        'qte_techaccept' => 'boolean',
    ];

    /**
     * The one quote legacy marked as selected (Queries/pur_quotes_recomm.sql).
     */
    public function scopeRecommended($query)
    {
        return $query->where('qte_recomm', true);
    }

    /**
     * Amount this quote was offered at. Base-only when the case is quotetype 1
     * (the case then carries the tax), tax-inclusive when quotetype 2.
     */
    public function getOfferedAmountAttribute(): float
    {
        return (float) ($this->qte_price ?: ($this->qte_midprice ?: ($this->qte_intprice ?? 0)));
    }

    /**
     * Define relationship to Firm model
     */
    public function firm()
    {
        // Quote belongs to a Firm
        // Foreign key: qte_frm_id, Owner key: frm_id
        return $this->belongsTo(Firm::class, 'qte_frm_id', 'frm_id');
    }
}
