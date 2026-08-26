<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudAttachment extends Model
{
    protected $table = 'aud.audattachments';
    protected $primaryKey = 'aat_id';
    public $timestamps = false;

    protected $fillable = [
        'aat_objtype',
        'aat_objid',
        'aat_type',
        'aat_path',
    ];
}
