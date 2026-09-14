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
        'pci_pcs_id', 'pci_emp_id', 'pci_serial', 'pci_desc', 'pci_qty', 
        'pci_qtyunit', 'pci_price', 'pci_type', 'pci_subtype',
        'pci_type2', 'pci_category', 'pci_subhead', 'pci_estprice', 'pci_fulfilment'
    ];

    public function getTypeNameAttribute(): string
    {
        return match((int)$this->pci_type) {
            7, 1 => 'Permanent',
            2 => 'Consumable',
            3 => 'Service',
            default => !empty($this->pci_type) ? (string)$this->pci_type : 'Permanent',
        };
    }

    public function getType2NameAttribute(): string
    {
        return match((int)$this->pci_type2) {
            5 => 'Inventory',
            6 => 'Asset',
            default => !empty($this->pci_type2) ? (string)$this->pci_type2 : 'Inventory',
        };
    }

    // Case ke saath wapsi ka link (Optional but good)
    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'pci_pcs_id', 'pcs_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'pci_emp_id', 'emp_id');
    }
}