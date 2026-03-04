<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurRfq extends Model
{
    protected $table = 'puritems.rfqs';
    protected $primaryKey = 'rfq_id';
    public $timestamps = false;
    protected $fillable = ['rfq_title','rfq_unt_id','rfq_created_by','rfq_status','rfq_total'];

    public function lines()
    {
        return $this->hasMany(PurRfqItem::class, 'rfi_rfq_id', 'rfq_id');
    }
}

