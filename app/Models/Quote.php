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
        'qte_frm_id' // Add this if you want to assign foreign key too
    ];

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
