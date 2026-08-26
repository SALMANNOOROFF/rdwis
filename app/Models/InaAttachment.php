<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InaAttachment extends Model
{
    protected $table = 'ina.inaattachments';
    protected $primaryKey = 'iat_id';
    public $timestamps = false;

    protected $fillable = [
        'iat_objtype',
        'iat_objid',
        'iat_type',
        'iat_path',
    ];
}
