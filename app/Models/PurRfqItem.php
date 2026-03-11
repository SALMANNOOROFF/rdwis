<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurRfqItem extends Model
{
    protected $table = 'purnew.rfq_items';
    protected $primaryKey = 'rfq_item_id';
    public $timestamps = false;
    protected $fillable = ['rfq_id','item_id','est_price','price'];

    public function rfq()
    {
        return $this->belongsTo(PurRfq::class, 'rfq_id', 'rfq_id');
    }
}
