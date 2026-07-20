<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrCtrCaseAttachment extends Model
{
    protected $table = 'hr.ctrcaseattachments';
    protected $primaryKey = 'cat_id';
    public $timestamps = false;

    protected $fillable = [
        'cat_objtype',
        'cat_objid',
        'cat_type',
        'cat_path'
    ];
}
