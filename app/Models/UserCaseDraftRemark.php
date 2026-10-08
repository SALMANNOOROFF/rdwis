<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCaseDraftRemark extends Model
{
    protected $table = 'cen.user_case_draft_remarks';

    protected $fillable = [
        'acc_id',
        'case_type',
        'case_id',
        'draft_remarks',
    ];

    /**
     * Get or create a draft for the current user and case.
     */
    public static function getDraft(int $accId, string $caseType, int $caseId): ?string
    {
        return static::where('acc_id', $accId)
            ->where('case_type', $caseType)
            ->where('case_id', $caseId)
            ->value('draft_remarks');
    }

    /**
     * Save/upsert draft remarks for the current user and case.
     */
    public static function saveDraft(int $accId, string $caseType, int $caseId, ?string $remarks): self
    {
        return static::updateOrCreate(
            [
                'acc_id' => $accId,
                'case_type' => $caseType,
                'case_id' => $caseId,
            ],
            [
                'draft_remarks' => $remarks,
            ]
        );
    }

    /**
     * Clear draft remarks for the current user and case upon sending/forwarding.
     */
    public static function clearDraft(int $accId, string $caseType, int $caseId): void
    {
        static::where('acc_id', $accId)
            ->where('case_type', $caseType)
            ->where('case_id', $caseId)
            ->delete();
    }
}
