<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurRfq extends Model
{
    protected $table = 'purnew.rfq';
    protected $primaryKey = 'rfq_id';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;
    protected $fillable = ['rfq_id','pcs_date','pcs_title','pcs_unt_id','pcs_hed_id','pcs_effhed_id','pcs_effunt_id'];

    public function lines()
    {
        return $this->hasMany(PurRfqItem::class, 'rfq_id', 'rfq_id');
    }
}
