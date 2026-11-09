<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GenericCase extends Model
{
    protected $table = 'gen.cases';
    protected $primaryKey = 'gcs_id';

    protected $fillable = [
        'gcs_file_no',
        'gcs_title',
        'gcs_category',
        'gcs_subject',
        'gcs_description',
        'gcs_priority',
        'gcs_status',
        'gcs_current_stage',
        'gcs_current_holder_id',
        'gcs_created_by',
        'gcs_unt_id',
        'gcs_metadata',
        'gcs_linkable_type',
        'gcs_linkable_id',
    ];

    protected $casts = [
        'gcs_metadata' => 'array',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * Relationship: Sequential paragraphs in official note-sheet
     */
    public function notes(): HasMany
    {
        return $this->hasMany(GenericCaseNote::class, 'gcn_case_id', 'gcs_id')->orderBy('gcn_para_no', 'asc');
    }

    /**
     * Relationship: Full movement / routing decision log
     */
    public function movements(): HasMany
    {
        return $this->hasMany(GenericCaseMovement::class, 'gcm_case_id', 'gcs_id')->orderBy('gcm_dtg', 'desc');
    }

    public function movementsAsc(): HasMany
    {
        return $this->hasMany(GenericCaseMovement::class, 'gcm_case_id', 'gcs_id')->orderBy('gcm_dtg', 'asc');
    }

    /**
     * Relationship: Case Attachments
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(GenericCaseAttachment::class, 'gca_case_id', 'gcs_id')->orderBy('created_at', 'desc');
    }

    /**
     * Relationship: Officer currently holding this case file
     */
    public function currentHolder(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'gcs_current_holder_id', 'acc_id');
    }

    /**
     * Relationship: Officer who initiated this case
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(CenAccount::class, 'gcs_created_by', 'acc_id');
    }

    /**
     * Relationship: Originating Unit / Division
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'gcs_unt_id', 'unt_id');
    }

    /**
     * Polymorphic relation to any existing entity (Purchase, Hiring, etc.)
     */
    public function linkable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'gcs_linkable_type', 'gcs_linkable_id');
    }

    /**
     * Helper to check if file is pending with a given user
     */
    public function isPendingForUser(?CenAccount $user): bool
    {
        if (!$user) return false;
        if (in_array(strtolower($this->gcs_status), ['approved', 'closed', 'rejected'])) {
            return false;
        }
        return (int) $this->gcs_current_holder_id === (int) $user->acc_id;
    }
}

