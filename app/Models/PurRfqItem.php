<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurRfqItem extends Model
{
    protected $table = 'puritems.rfq_items';
    protected $primaryKey = 'rfi_id';
    public $timestamps = false;
    protected $fillable = ['rfi_rfq_id','rfi_itm_id','rfi_price_id','rfi_qty','rfi_total'];

    public function rfq()
    {
        return $this->belongsTo(PurRfq::class, 'rfi_rfq_id', 'rfq_id');
    }
}

