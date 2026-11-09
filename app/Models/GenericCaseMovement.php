<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GenericCaseMovement extends Model
{
    protected $table = 'gen.case_movements';
    protected $primaryKey = 'gcm_id';
    public $timestamps = false;

    protected $fillable = [
        'gcm_case_id',
        'gcm_actor_id',
        'gcm_actor_name',
        'gcm_actor_rank',
        'gcm_actor_desig',
        'gcm_action',
        'gcm_from_stage',
        'gcm_to_stage',
        'gcm_from_holder_id',
        'gcm_to_holder_id',
        'gcm_remarks',
        'gcm_dtg',
    ];

    protected $casts = [
        'gcm_dtg' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(GenericCase::class, 'gcm_case_id', 'gcs_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'gcm_actor_id', 'acc_id');
    }

    public function fromHolder(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'gcm_from_holder_id', 'acc_id');
    }

    public function toHolder(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'gcm_to_holder_id', 'acc_id');
    }
}

