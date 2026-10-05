<?php

namespace App\Models\HrForms;

use Illuminate\Database\Eloquent\Model;

class FormAuditLog extends Model
{
    protected $table = 'hrforms.audit_log';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'case_id' => 'integer',
        'form_id' => 'integer',
        'user_id' => 'integer',
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Enforce INSERT-ONLY rule in application code
        static::updating(function () {
            throw new \RuntimeException('CRITICAL: hrforms.audit_log is insert-only. Updates are strictly forbidden.');
        });

        static::deleting(function () {
            throw new \RuntimeException('CRITICAL: hrforms.audit_log is insert-only. Deletes are strictly forbidden.');
        });
    }

    public static function logAction(
        ?int $caseId,
        ?int $formId,
        string $action,
        ?string $details = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $user = auth()->user();
        return self::create([
            'case_id' => $caseId,
            'form_id' => $formId,
            'action' => $action,
            'user_id' => $user->acc_id ?? null,
            'user_name' => $user->acc_name ?? ($user->acc_username ?? 'System'),
            'details' => $details,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip() ?? null,
            'created_at' => now(),
        ]);
    }
}
