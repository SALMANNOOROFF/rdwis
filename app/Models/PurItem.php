<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurItem extends Model
{
    protected $table = 'purnew.items';
    protected $primaryKey = 'item_id';
    public $timestamps = false;
    protected $fillable = [
        'title','serial','unt_id','type','subtype'
    ];
}
