<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurItem extends Model
{
    protected $table = 'puritems.items';
    protected $primaryKey = 'itm_id';
    public $timestamps = false;
    protected $fillable = [
        'itm_title','itm_desc','itm_qtyunit','itm_cat_id','itm_sub_id','itm_unt_id','itm_hed_id','itm_acc_id'
    ];

    public function prices()
    {
        return $this->hasMany(PurItemPrice::class, 'prc_itm_id', 'itm_id');
    }

    public function latestPrice()
    {
        return $this->hasOne(PurItemPrice::class, 'prc_itm_id', 'itm_id')->orderByDesc('effective_date');
    }
}
