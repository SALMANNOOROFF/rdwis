<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrEmpAttachment extends Model
{
    protected $table = 'hr.empattachments';
    protected $primaryKey = 'eat_id';
    public $timestamps = false;

    protected $fillable = [
        'eat_objtype',
        'eat_objid',
        'eat_type',
        'eat_path',
    ];
}
