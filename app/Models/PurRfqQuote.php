<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurRfqQuote extends Model
{
    protected $table = 'purnew.rfq_quotes';
    protected $primaryKey = 'rq_id';
    public $timestamps = false;

    protected $fillable = [
        'rfq_id', 'rfq_item_id', 'frm_id', 'quoted_price', 'is_accepted', 'created_at'
    ];

    protected $casts = [
        'is_accepted' => 'boolean',
    ];

    public function rfq()
    {
        return $this->belongsTo(PurRfq::class, 'rfq_id', 'rfq_id');
    }

    public function rfqItem()
    {
        return $this->belongsTo(PurRfqItem::class, 'rfq_item_id', 'rfq_item_id');
    }

    public function firm()
    {
        return $this->belongsTo(Firm::class, 'frm_id', 'frm_id');
    }
}
