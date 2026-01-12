<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    // Schema + table name
    protected $table = 'pur.purcases';

    protected $primaryKey = 'pcs_id';
    public $timestamps = false;

    protected $fillable = [
        'pcs_title',
        'pcs_date',
        'pcs_status',
        'pcs_type',
        'pcs_unt_id'
    ];

    /**
     * Purchase ke items
     */
    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'pci_pcs_id', 'pcs_id');
    }

    /**
     * Purchase ke quotes
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class, 'qte_pcs_id', 'pcs_id');
    }

    /**
     * Purchase ke no-quote records
     */
    public function noQuotes()
    {
        return $this->hasMany(NoQuote::class, 'nqt_pcs_id', 'pcs_id');
    }
}
