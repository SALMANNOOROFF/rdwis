<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    // Aapke diagram ke mutabiq schema 'pur' aur table 'purcaseitems'
    protected $table = 'pur.purcaseitems';

    // Diagram mein pci_id unique lag raha hai
    protected $primaryKey = 'pci_id'; 

    public $timestamps = false;

    protected $fillable = [
        'pci_pcs_id', 'pci_serial', 'pci_desc', 'pci_qty', 
        'pci_qtyunit', 'pci_price', 'pci_type', 'pci_subtype'
    ];

    // Case ke saath wapsi ka link (Optional but good)
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'pci_pcs_id', 'pcs_id');
    }
}