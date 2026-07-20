<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MprGroup extends Model
{
    use HasFactory;

    protected $table = 'prj.mprgroup';
    protected $primaryKey = 'mgp_id';
    public $timestamps = false;

    protected $fillable = [
        'mgp_name', 'mgp_dtg', 'mgp_status'
    ];
}
