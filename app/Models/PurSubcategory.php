<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurSubcategory extends Model
{
    protected $table = 'puritems.subcategories';
    protected $primaryKey = 'sub_id';
    public $timestamps = false;
    protected $fillable = ['sub_name','sub_cat_id'];
}

