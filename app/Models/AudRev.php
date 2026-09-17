<?php

namespace App\Models;

use App\Enums\RevType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AudRev extends Model
{
    protected $table = 'aud.revs';
    protected $primaryKey = 'rev_id';
    public $timestamps = false;

    protected $fillable = [
        'rev_obj',
        'rev_releasedtg',
        'rev_closedtg',
        'rev_objid',
        'rev_reason',
        'rev_status',
        'rev_unt_id',
        'rev_date',
        'rev_type',
        'rev_intunt_id',
        'rev_ref',
        'rev_objext',
    ];

    protected $casts = [
        'rev_type' => RevType::class,
        'rev_releasedtg' => 'datetime',
        'rev_closedtg' => 'datetime',
        'rev_date' => 'date',
    ];

    public function comps(): HasMany
    {
        return $this->hasMany(AudRevComp::class, 'rvc_rev_id', 'rev_id');
    }

    public function data(): HasMany
    {
        return $this->hasMany(AudRevData::class, 'rvd_rev_id', 'rev_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AudAttachment::class, 'aat_objid', 'rev_id')->where('aat_objtype', 'rev');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'rev_unt_id', 'unt_id');
    }

    public function initiatingUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'rev_intunt_id', 'unt_id');
    }

    public function isDraft(): bool
    {
        return strcasecmp(trim((string) $this->rev_status), 'Draft') === 0;
    }

    public function isInProcess(): bool
    {
        $status = strtolower(str_replace('-', ' ', trim((string) $this->rev_status)));
        return in_array($status, ['in process', 'released'], true);
    }

    public function isFulfilled(): bool
    {
        return strcasecmp(trim((string) $this->rev_status), 'Fulfilled') === 0;
    }

    public function isCancelled(): bool
    {
        return strcasecmp(trim((string) $this->rev_status), 'Cancelled') === 0;
    }

    public function isUnderRevision(): bool
    {
        $status = strtolower(str_replace('-', ' ', trim((string) $this->rev_status)));
        return $status === 'under revision';
    }
}
