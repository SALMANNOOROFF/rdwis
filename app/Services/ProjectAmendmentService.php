<?php

namespace App\Services;

use App\Models\CenAccount;
use App\Models\GenericCase;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Project Details Amendment Workflow.
 *
 * Project particulars (title, status, cost, key dates, scope, remarks) are no longer
 * saved directly from the "Edit Project Details" modal. Instead an e-office Case File
 * (gen.cases) is auto-raised with a system generated justification. The case travels
 * the normal note-sheet route (Division -> Finance -> MD ...) and the moment it is
 * approved, the proposed values are written to prj.projects automatically.
 */
class ProjectAmendmentService
{
    /** Key used inside gen.cases.gcs_metadata */
    public const META_KEY = 'project_amendment';

    /** Case category used for these amendment cases */
    public const CATEGORY = 'General';

    /**
     * Whitelist of project columns that may be amended through this workflow.
     * Nothing outside this list is ever written to prj.projects.
     */
    public const FIELDS = [
        'prj_title'    => ['label' => 'Project Title',          'type' => 'text'],
        'prj_status'   => ['label' => 'Project Status',         'type' => 'status'],
        'prj_sponsor'  => ['label' => 'Sponsor / Agency',       'type' => 'text'],
        'prj_propcost' => ['label' => 'Proposed Cost (Rs.)',    'type' => 'money'],
        'prj_aprvcost' => ['label' => 'Approved Cost (Rs.)',    'type' => 'money'],
        'prj_scope'    => ['label' => 'Scope of Work',          'type' => 'textarea'],
        'prj_rcptdt'   => ['label' => 'Received Date',          'type' => 'date'],
        'prj_assigndt' => ['label' => 'Assigned Date',          'type' => 'date'],
        'prj_propdt'   => ['label' => 'Proposal Date',          'type' => 'date'],
        'prj_aprvdt'   => ['label' => 'Approval Date',          'type' => 'date'],
        'prj_startdt'  => ['label' => 'Start Date',             'type' => 'date'],
        'prj_estenddt' => ['label' => 'EDC (Est. End Date)',    'type' => 'date'],
        'prj_enddt'    => ['label' => 'Actual Completion Date', 'type' => 'date'],
        'prj_rem'      => ['label' => 'Remarks',                'type' => 'textarea'],
    ];

    /** Status options offered for the prj_status field */
    public const STATUSES = [
        'Under assignment',
        'Under feasibility',
        'Approval awaited',
        'Funds awaited',
        'Work in progress',
        'Warranty in progress',
        'Final Acceptance Awaited',
        'Completed',
        'Cancelled',
    ];

    public function __construct(
        protected GenericCaseService $caseService
    ) {}

    // =====================================================================
    // VALUE HELPERS
    // =====================================================================

    /**
     * Normalise a raw value to the canonical form stored in metadata / DB.
     */
    public function normalize(string $type, $value)
    {
        if ($value === null) {
            return null;
        }

        if ($type === 'date') {
            $raw = trim((string) $value);
            if ($raw === '') {
                return null;
            }
            try {
                return Carbon::parse($raw)->format('Y-m-d');
            } catch (Throwable $e) {
                return null;
            }
        }

        if ($type === 'money') {
            $raw = trim((string) $value);
            if ($raw === '') {
                return null;
            }
            $raw = preg_replace('/[^0-9\.\-]/', '', $raw);
            if ($raw === '' || !is_numeric($raw)) {
                return null;
            }
            return (int) round((float) $raw);
        }

        $raw = trim((string) $value);
        return $raw === '' ? null : $raw;
    }

    /**
     * Human readable rendering of a stored value.
     */
    public function displayValue(string $type, $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if ($type === 'date') {
            try {
                return Carbon::parse($value)->format('d M, Y');
            } catch (Throwable $e) {
                return (string) $value;
            }
        }

        if ($type === 'money') {
            return 'Rs. ' . number_format((float) $value);
        }

        return (string) $value;
    }

    // =====================================================================
    // DIFF + JUSTIFICATION
    // =====================================================================

    /**
     * Compare the submitted values against the live project record.
     * Only fields actually present in $submitted are considered.
     *
     * @return array<string, array{label:string,type:string,old:mixed,new:mixed}>
     */
    public function diff(Project $project, array $submitted): array
    {
        $changes = [];

        foreach (self::FIELDS as $col => $def) {
            if (!array_key_exists($col, $submitted)) {
                continue;
            }

            $old = $this->normalize($def['type'], $project->{$col} ?? null);
            $new = $this->normalize($def['type'], $submitted[$col]);

            if ($old === $new) {
                continue;
            }

            $changes[$col] = [
                'label' => $def['label'],
                'type'  => $def['type'],
                'old'   => $old,
                'new'   => $new,
            ];
        }

        return $changes;
    }

    /**
     * Auto-generated note-sheet justification for the amendment case.
     */
    public function buildJustification(Project $project, array $changes): string
    {
        $code = trim((string) ($project->prj_code ?? '')) ?: ('PRJ-' . $project->prj_id);
        $title = trim((string) ($project->prj_title ?? 'Project'));

        $lines = [];
        $lines[] = "1. Reference Project {$code} — {$title}.";
        $lines[] = '';
        $lines[] = '2. Amendment in the project particulars is proposed for kind approval please. Details are as under:-';

        $letters = range('a', 'z');
        $i = 0;
        foreach ($changes as $col => $c) {
            $letter = $letters[$i] ?? (string) ($i + 1);
            $old = $this->displayValue($c['type'], $c['old']);
            $new = $this->displayValue($c['type'], $c['new']);
            $lines[] = "    {$letter}. {$c['label']} — Existing: {$old} | Proposed: {$new}";
            $i++;
        }

        $lines[] = '';
        $lines[] = '3. The amendment is required to keep the project record aligned with the latest position on ground.';
        $lines[] = '';
        $lines[] = '4. On approval, the above amendment will be incorporated in the project record automatically by the system. Approval is solicited please.';

        return implode("\n", $lines);
    }

    // =====================================================================
    // CASE CREATION / LOOKUP
    // =====================================================================

    /**
     * Raise a new amendment case file for the given project.
     */
    public function createAmendmentCase(Project $project, CenAccount $user, array $changes): GenericCase
    {
        return DB::transaction(function () use ($project, $user, $changes) {
            $case = $this->caseService->initiateDraftCase($user);

            $code = trim((string) ($project->prj_code ?? '')) ?: ('PRJ-' . $project->prj_id);
            $description = $this->buildJustification($project, $changes);

            $case->update([
                'gcs_title'         => "Amendment in Project Details — {$code}",
                'gcs_category'      => self::CATEGORY,
                'gcs_subject'       => "Proposed amendment in particulars of project {$code} ({$project->prj_title})",
                'gcs_description'   => $description,
                'gcs_linkable_type' => Project::class,
                'gcs_linkable_id'   => (int) $project->prj_id,
                'gcs_metadata'      => [
                    self::META_KEY => [
                        'project_id'       => (int) $project->prj_id,
                        'project_code'     => $code,
                        'project_title'    => (string) $project->prj_title,
                        'fields'           => $changes,
                        'status'           => 'pending',
                        'created_by'       => (int) $user->acc_id,
                        'created_at'       => now()->toDateTimeString(),
                        'applied_at'       => null,
                        'applied_by'       => null,
                        'applied_by_name'  => null,
                        'auto_description' => $description,
                    ],
                ],
            ]);

            $this->logProjectActivity(
                $project->prj_id,
                'Project Update',
                "Amendment case {$case->gcs_file_no} raised for " . count($changes) . ' field(s): ' . implode(', ', array_keys($changes))
            );

            return $case->fresh();
        });
    }

    /**
     * The amendment payload of a case, or null when the case is not an amendment case.
     */
    public function amendmentFor(?GenericCase $case): ?array
    {
        if (!$case) {
            return null;
        }
        $meta = $case->gcs_metadata;
        if (!is_array($meta) || empty($meta[self::META_KEY]) || !is_array($meta[self::META_KEY])) {
            return null;
        }
        return $meta[self::META_KEY];
    }

    /**
     * An open (not approved / closed / cancelled / rejected) amendment case for a project.
     */
    public function pendingCaseFor($projectId): ?GenericCase
    {
        return GenericCase::where('gcs_linkable_type', Project::class)
            ->where('gcs_linkable_id', (int) $projectId)
            ->whereNotIn('gcs_status', ['Approved', 'Closed', 'Cancelled', 'Rejected'])
            ->whereRaw("gcs_metadata->'" . self::META_KEY . "' IS NOT NULL")
            ->orderBy('gcs_id', 'desc')
            ->first();
    }

    // =====================================================================
    // REVISING VALUES AFTER APPROVAL
    // =====================================================================

    /**
     * Is this amendment case approved (and therefore open for revision by an
     * approving authority)? Before approval the proposed values are frozen.
     */
    public function isApproved(GenericCase $case): bool
    {
        return strcasecmp(trim((string) $case->gcs_status), 'Approved') === 0;
    }

    /**
     * Revise the values of an APPROVED amendment and write them straight to the
     * project record. Only ever called for approved cases by an approving authority.
     *
     * A value set back to its original (pre-amendment) figure reverts that field on
     * the project and is dropped from the amendment list.
     */
    public function applyRevisedValues(GenericCase $case, array $values, ?CenAccount $actor = null): bool
    {
        $payload = $this->amendmentFor($case);
        if (!$payload || !$this->isApproved($case)) {
            return false;
        }

        $project = Project::where('prj_id', (int) ($payload['project_id'] ?? 0))->first();
        if (!$project) {
            return false;
        }

        $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
        $update = [];
        $touched = [];

        foreach ($values as $col => $val) {
            if (!array_key_exists($col, self::FIELDS)) {
                continue; // hard whitelist
            }
            $def = self::FIELDS[$col];
            $new = $this->normalize($def['type'], $val);

            $old = array_key_exists($col, $fields)
                ? ($fields[$col]['old'] ?? null)
                : $this->normalize($def['type'], $project->{$col} ?? null);

            $current = $this->normalize($def['type'], $project->{$col} ?? null);
            if ($new !== $current) {
                $touched[] = $def['label'];
            }

            $update[$col] = $new;

            if ($new === $old) {
                // Back to the original figure - the field is no longer an amendment.
                unset($fields[$col]);
            } else {
                $fields[$col] = [
                    'label' => $def['label'],
                    'type'  => $def['type'],
                    'old'   => $old,
                    'new'   => $new,
                ];
            }
        }

        if (empty($update)) {
            return false;
        }

        DB::transaction(function () use ($project, $update, $case, $payload, $fields, $actor, $touched) {
            DB::table('prj.projects')->where('prj_id', $project->prj_id)->update($update);

            $payload['fields'] = $fields;
            $payload['status'] = 'applied';
            $payload['applied_at'] = $payload['applied_at'] ?? now()->toDateTimeString();
            $payload['revised_at'] = now()->toDateTimeString();
            $payload['revised_by'] = $actor ? (int) $actor->acc_id : null;
            $payload['revised_by_name'] = $actor ? ($actor->acc_name ?? $actor->acc_username ?? null) : null;

            $meta = is_array($case->gcs_metadata) ? $case->gcs_metadata : [];
            $case->update(['gcs_metadata' => array_merge($meta, [self::META_KEY => $payload])]);

            if (!empty($touched)) {
                $this->logProjectActivity(
                    $project->prj_id,
                    'Project Update',
                    "Approved case {$case->gcs_file_no}: revised values saved to project — " . implode(', ', $touched)
                );
            }
        });

        return true;
    }

    // =====================================================================
    // APPLY ON APPROVAL
    // =====================================================================

    /**
     * Apply an approved amendment to prj.projects. Safe to call for any case:
     * returns false when the case is not an approved, unapplied amendment case.
     */
    public function applyApprovedAmendment(GenericCase $case, ?CenAccount $actor = null): bool
    {
        $payload = $this->amendmentFor($case);
        if (!$payload) {
            return false;
        }

        if (($payload['status'] ?? 'pending') === 'applied') {
            return false;
        }

        if (strcasecmp(trim((string) $case->gcs_status), 'Approved') !== 0) {
            return false;
        }

        $project = Project::where('prj_id', (int) ($payload['project_id'] ?? 0))->first();
        if (!$project) {
            return false;
        }

        $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
        $update = [];
        $applied = [];

        foreach ($fields as $col => $c) {
            if (!array_key_exists($col, self::FIELDS)) {
                continue; // hard whitelist
            }
            $def = self::FIELDS[$col];
            $update[$col] = $this->normalize($def['type'], $c['new'] ?? null);
            $applied[] = $def['label'];
        }

        if (empty($update)) {
            return false;
        }

        DB::transaction(function () use ($project, $update, $case, $payload, $actor, $applied) {
            DB::table('prj.projects')->where('prj_id', $project->prj_id)->update($update);

            $payload['status'] = 'applied';
            $payload['applied_at'] = now()->toDateTimeString();
            $payload['applied_by'] = $actor ? (int) $actor->acc_id : null;
            $payload['applied_by_name'] = $actor ? ($actor->acc_name ?? $actor->acc_username ?? null) : null;

            $meta = is_array($case->gcs_metadata) ? $case->gcs_metadata : [];
            $case->update(['gcs_metadata' => array_merge($meta, [self::META_KEY => $payload])]);

            $this->logProjectActivity(
                $project->prj_id,
                'Project Update',
                "Approved amendment case {$case->gcs_file_no} applied: " . implode(', ', $applied)
            );
        });

        return true;
    }

    // =====================================================================
    // INTERNAL
    // =====================================================================

    protected function logProjectActivity($projectId, string $action, string $details): void
    {
        try {
            if (Schema::hasTable('project_activities')) {
                DB::table('project_activities')->insert([
                    'pja_prj_id'  => $projectId,
                    'pja_action'  => $action,
                    'pja_details' => $details,
                    'pja_user'    => Auth::check() ? Auth::user()->acc_username : 'System',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        } catch (Throwable $e) {
            // Activity logging must never break the workflow.
        }
    }
}
