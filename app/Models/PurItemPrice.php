<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurItemPrice extends Model
{
    protected $table = 'puritems.prices';
    protected $primaryKey = 'prc_id';
    public $timestamps = false;
    protected $fillable = [
        'prc_itm_id','prc_base','prc_gst','prc_sst','prc_gross','prc_qty','prc_qtyunit','effective_date'
    ];

    public function item()
    {
        return $this->belongsTo(PurItem::class, 'prc_itm_id', 'itm_id');
    }
}

