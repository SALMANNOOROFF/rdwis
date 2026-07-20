<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurCategory extends Model
{
    protected $table = 'puritems.categories';
    protected $primaryKey = 'cat_id';
    public $timestamps = false;
    protected $fillable = ['cat_name'];
}

