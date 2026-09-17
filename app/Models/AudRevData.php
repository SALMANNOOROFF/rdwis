<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudRevData extends Model
{
    protected $table = 'aud.revdata';
    protected $primaryKey = 'rvd_id';
    public $timestamps = false;

    protected $fillable = [
        'rvd_rev_id',
        'rvd_table',
        'rvd_rowid',
        'rvd_attrib',
        'rvd_oldvalue',
        'rvd_newvalue',
        'rvd_datatype',
        'rvd_type',
        'rvd_conversion',
        'rvd_colname',
        'rvd_alias',
    ];

    public function revision(): BelongsTo
    {
        return $this->belongsTo(AudRev::class, 'rvd_rev_id', 'rev_id');
    }
}
