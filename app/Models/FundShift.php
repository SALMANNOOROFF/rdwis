<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FundShift extends Model
{
    protected $table = 'fin.fund_shifts';
    protected $primaryKey = 'fsh_id';

    protected $fillable = [
        'fsh_rev_id',
        'fsh_prj_id',
        'fsh_hed_id',
        'fsh_unt_id',
        'fsh_from_sbh_id',
        'fsh_from_sbh_name',
        'fsh_to_sbh_id',
        'fsh_to_sbh_name',
        'fsh_proposed_amount',
        'fsh_approved_amount',
        'fsh_from_alloc_before',
        'fsh_from_alloc_after',
        'fsh_to_alloc_before',
        'fsh_to_alloc_after',
        'fsh_current_stage',
        'fsh_current_acc_id',
        'fsh_current_holder',
        'fsh_status',
        'fsh_purpose',
        'fsh_created_by',
        'fsh_created_name',
        'fsh_created_rank',
        'fsh_created_desig',
        'fsh_implemented_by',
        'fsh_implemented_at',
        'fsh_implementation_notes',
    ];

    protected $casts = [
        'fsh_proposed_amount' => 'decimal:2',
        'fsh_approved_amount' => 'decimal:2',
        'fsh_from_alloc_before' => 'decimal:2',
        'fsh_from_alloc_after' => 'decimal:2',
        'fsh_to_alloc_before' => 'decimal:2',
        'fsh_to_alloc_after' => 'decimal:2',
        'fsh_implemented_at' => 'datetime',
    ];

    public function getCaseNumberAttribute(): string
    {
        return '#FS-' . ($this->fsh_rev_id ?: $this->fsh_id);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'fsh_prj_id', 'prj_id');
    }

    public function head(): BelongsTo
    {
        return $this->belongsTo(CenHead::class, 'fsh_hed_id', 'hed_id');
    }

    public function fromSubhead(): BelongsTo
    {
        return $this->belongsTo(FinSubhead::class, 'fsh_from_sbh_id', 'sbh_id');
    }

    public function toSubhead(): BelongsTo
    {
        return $this->belongsTo(FinSubhead::class, 'fsh_to_sbh_id', 'sbh_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'fsh_unt_id', 'unt_id');
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(AudRev::class, 'fsh_rev_id', 'rev_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fsh_created_by', 'acc_id');
    }

    public function implementer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fsh_implemented_by', 'acc_id');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(FundShiftDecision::class, 'fsd_fsh_id', 'fsh_id')->orderBy('fsd_dtg', 'desc');
    }

    public function decisionsAsc(): HasMany
    {
        return $this->hasMany(FundShiftDecision::class, 'fsd_fsh_id', 'fsh_id')->orderBy('fsd_dtg', 'asc');
    }
}
