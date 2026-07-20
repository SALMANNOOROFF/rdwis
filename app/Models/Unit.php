<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'cen.units';
    protected $primaryKey = 'unt_id';
    public $timestamps = false;

    protected $fillable = ['unt_name', 'unt_type'];
}