<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrjAttachment extends Model
{
    use HasFactory;

    protected $table = 'prj.prjattachments';
    protected $primaryKey = 'jat_id';
    public $timestamps = false;

    protected $fillable = [
        'jat_objtype', 'jat_objid', 'jat_type', 'jat_path'
    ];
}