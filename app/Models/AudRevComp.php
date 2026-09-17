<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudRevComp extends Model
{
    protected $table = 'aud.revcomps';
    protected $primaryKey = 'rvc_id';
    public $timestamps = false;

    protected $fillable = [
        'rvc_rev_id',
        'rvc_table',
        'rvc_detail',
        'rvc_rowid',
        'rvc_action',
        'rvc_type',
    ];

    public function revision(): BelongsTo
    {
        return $this->belongsTo(AudRev::class, 'rvc_rev_id', 'rev_id');
    }
}
