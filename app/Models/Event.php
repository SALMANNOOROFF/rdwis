<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'prj.events';
    protected $primaryKey = 'evt_id';
    public $timestamps = false;

    protected $fillable = [
        'evt_name', 'evt_doer', 'evt_dtg', 'evt_xprj_id'
    ];
}
