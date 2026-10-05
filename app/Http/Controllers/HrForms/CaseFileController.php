<?php

namespace App\Http\Controllers\HrForms;

use App\Http\Controllers\Controller;
use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use App\Models\HrForms\CaseForm;
use App\Models\HrForms\CaseMilestone;
use App\Models\HrForms\FormAuditLog;
use App\Models\HrForms\ProjectExtra;
use App\Services\HrForms\Extractors\AnnexAExtractor;
use App\Services\HrForms\Extractors\AnnexBExtractor;
use App\Services\HrForms\Extractors\AnnexDExtractor;
use App\Services\HrForms\Extractors\AnnexJExtractor;
use App\Services\HrForms\Extractors\AnnexMExtractor;
use App\Services\HrForms\Extractors\AnnexNExtractor;
use App\Services\HrForms\Extractors\AnnexTExtractor;
use App\Services\HrForms\Extractors\AnnexUExtractor;
use App\Services\HrForms\FormCompletenessService;
use App\Services\HrForms\FormGenerationService;
use App\Services\HrForms\ProgressTrackerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CaseFileController extends Controller
{
    protected FormGenerationService $generationService;
    protected FormCompletenessService $completenessService;
    protected ProgressTrackerService $trackerService;

    public function __construct(
        FormGenerationService $generationService,
        FormCompletenessService $completenessService,
        ProgressTrackerService $trackerService
    ) {
        $this->generationService = $generationService;
        $this->completenessService = $completenessService;
        $this->trackerService = $trackerService;
    }

    /**
     * Get Case File tab data (forms list, progress tracker, audit log, project extras).
     */
    public function index(int $caseId): JsonResponse
    {
        if (!config('hrforms.enabled', false)) {
            return response()->json(['success' => false, 'message' => 'HR Forms feature is disabled.'], 403);
        }

        $case = HrCtrCase::with(['casePlans.project', 'employee'])->findOrFail($caseId);
        Gate::authorize('view', $case);

        $forms = CaseForm::where('case_id', $caseId)
            ->orderBy('id', 'asc')
            ->get();

        $tracker = $this->trackerService->getTrackerSteps($case);

        $auditLogs = FormAuditLog::where('case_id', $caseId)
            ->orderBy('id', 'desc')
            ->take(50)
            ->get();

        // Project extras
        $firstPlan = $case->casePlans->first();
        $projectId = $firstPlan?->project?->prj_id ?? ($firstPlan?->ccp_prj_id ?? ($case->ctc_prj_id ?? null));
        $projectExtras = null;
        if ($projectId) {
            $projectExtras = ProjectExtra::where('project_id', $projectId)->first();
        }

        $caseExtra = CaseExtra::where('case_id', $caseId)->first();

        // Format forms list with requirement labels and missing fields summary
        $formattedForms = $forms->map(function ($form) {
            $data = $form->form_data ?? [];
            $missing = $form->isSubmitted() ? [] : ($data['missing_fields'] ?? []);
            $warnings = $data['warnings'] ?? [];

            $requirementLabel = match (true) {
                in_array($form->form_code, ['RDW/HR/F-03', 'RDW/HR/F-11']) => 'at joining',
                !$form->is_required => 'optional',
                default => 'required',
            };

            return [
                'id'                => $form->id,
                'form_code'         => $form->form_code,
                'annex'             => $form->annex,
                'instance_key'      => $form->instance_key,
                'form_title'        => $form->form_title,
                'requirement_level' => $requirementLabel,
                'is_required'       => $form->is_required,
                'status'            => $form->status,
                'is_submitted'      => $form->isSubmitted(),
                'missing_fields'    => $missing,
                'warnings'          => $warnings,
                'submitted_at'      => $form->submitted_at?->format('Y-m-d H:i'),
                'updated_at'        => $form->updated_at?->format('Y-m-d H:i'),
            ];
        });

        return response()->json([
            'success'        => true,
            'case_id'        => $case->ctc_id,
            'hiring_type'    => $tracker['hiring_type'],
            'tracker'        => $tracker,
            'forms'          => $formattedForms,
            'audit_history'  => $auditLogs,
            'project_extras' => $projectExtras,
            'case_extra'     => $caseExtra,
        ]);
    }

    /**
     * Get specific form data for view / edit.
     * Submitted forms render from snapshot_data only.
     */
    public function showForm(int $formId): JsonResponse
    {
        if (!config('hrforms.enabled', false)) {
            return response()->json(['success' => false, 'message' => 'HR Forms feature is disabled.'], 403);
        }

        $form = CaseForm::findOrFail($formId);
        $case = HrCtrCase::findOrFail($form->case_id);
        Gate::authorize('view', $case);

        if ($form->isSubmitted()) {
            return response()->json([
                'success'        => true,
                'form'           => $form,
                'is_submitted'   => true,
                'rendered_data'  => $form->snapshot_data ?? $form->form_data,
                'message'        => 'Rendering locked snapshot data.',
            ]);
        }

        return response()->json([
            'success'        => true,
            'form'           => $form,
            'is_submitted'   => false,
            'rendered_data'  => $form->form_data,
        ]);
    }

    /**
     * Save manual input for a form.
     * Writes only to hrforms.case_forms (manual layer) and hrforms.case_extras.
     */
    public function updateForm(int $formId, Request $request): JsonResponse
    {
        if (!config('hrforms.enabled', false)) {
            return response()->json(['success' => false, 'message' => 'HR Forms feature is disabled.'], 403);
        }

        $form = CaseForm::findOrFail($formId);
        $case = HrCtrCase::findOrFail($form->case_id);
        Gate::authorize('update', $case);

        if ($form->isSubmitted()) {
            return response()->json([
                'success' => false,
                'message' => 'Form is already submitted and locked. Modifications are not allowed.',
            ], 422);
        }

        $manualInput = $request->input('manual', []);
        $caseExtraInput = $request->input('case_extra', []);

        // Server-side validation per annex rules
        $validationError = $this->validateManualInput($form->form_code, $manualInput, $form->form_data['live'] ?? []);
        if ($validationError) {
            return response()->json(['success' => false, 'message' => $validationError], 422);
        }

        // Merge manual layer while preserving live layer strictly untouched
        $formData = $form->form_data ?? ['live' => [], 'manual' => [], 'warnings' => [], 'missing_fields' => []];
        $oldManual = $formData['manual'] ?? [];
        $formData['manual'] = array_merge($oldManual, $manualInput);

        // Calculate auto totals and scoring summaries
        $formData = $this->applyAutoCalculations($form->form_code, $formData);

        // Recompute completeness status
        $eval = $this->completenessService->evaluateStatus($form->form_code, $formData, $form->status);
        $formData['is_complete'] = $eval['is_complete'];
        $formData['missing_fields'] = $eval['missing_fields'];

        $oldStatus = $form->status;
        $form->status = $eval['status'];
        $form->form_data = $formData;
        $form->filled_by = Auth::id();
        $form->save();

        // Update hrforms.case_extras if provided
        if (!empty($caseExtraInput)) {
            CaseExtra::updateOrCreate(
                ['case_id' => $case->ctc_id],
                $caseExtraInput
            );
        }

        // Audit log
        FormAuditLog::logAction(
            $case->ctc_id,
            $form->id,
            'manual_data_saved',
            "Updated manual layer for {$form->form_code} ({$form->instance_key}). New status: {$form->status}",
            ['manual_keys' => array_keys($oldManual), 'status' => $oldStatus],
            ['manual_keys' => array_keys($formData['manual']), 'status' => $form->status]
        );

        return response()->json([
            'success'   => true,
            'message'   => 'Form manual inputs saved successfully.',
            'form'      => $form,
            'eval'      => $eval,
        ]);
    }

    /**
     * Refresh live extraction layer (Draft or Pending Input only).
     * Never changes the manual layer.
     */
    public function refreshLive(int $formId): JsonResponse
    {
        if (!config('hrforms.enabled', false)) {
            return response()->json(['success' => false, 'message' => 'HR Forms feature is disabled.'], 403);
        }

        $form = CaseForm::findOrFail($formId);
        $case = HrCtrCase::findOrFail($form->case_id);
        Gate::authorize('update', $case);

        if ($form->isSubmitted()) {
            return response()->json(['success' => false, 'message' => 'Submitted forms cannot be refreshed.'], 422);
        }

        if (!in_array($form->status, ['Draft', 'Pending Input'], true)) {
            return response()->json([
                'success' => false,
                'message' => "Live refresh is only allowed in Draft or Pending Input status (Current: {$form->status}).",
            ], 422);
        }

        $extractor = $this->getExtractorInstance($form->form_code);
        if (!$extractor) {
            return response()->json(['success' => false, 'message' => 'No extractor registered for this form code.'], 400);
        }

        $existingManual = $form->form_data['manual'] ?? [];
        $freshData = $extractor->extract($case, $form->instance_key, $existingManual);

        $eval = $this->completenessService->evaluateStatus($form->form_code, $freshData, $form->status);
        $freshData['is_complete'] = $eval['is_complete'];

        $form->form_data = $freshData;
        $form->status = $eval['status'];
        $form->save();

        FormAuditLog::logAction(
            $case->ctc_id,
            $form->id,
            'live_data_refreshed',
            "User triggered live extraction refresh for {$form->form_code} ({$form->instance_key})"
        );

        return response()->json([
            'success' => true,
            'message' => 'Live extraction data refreshed successfully. Manual data preserved.',
            'form'    => $form,
        ]);
    }

    /**
     * Mark form as Submitted.
     * Allowed only when status is 'Ready'.
     * Captures snapshot_data and locks the form.
     */
    public function submitForm(int $formId): JsonResponse
    {
        if (!config('hrforms.enabled', false)) {
            return response()->json(['success' => false, 'message' => 'HR Forms feature is disabled.'], 403);
        }

        $form = CaseForm::findOrFail($formId);
        $case = HrCtrCase::findOrFail($form->case_id);
        Gate::authorize('update', $case);

        if ($form->isSubmitted()) {
            return response()->json(['success' => false, 'message' => 'Form is already submitted.'], 422);
        }

        if ($form->status !== 'Ready') {
            $missing = $form->form_data['missing_fields'] ?? [];
            return response()->json([
                'success'        => false,
                'message'        => 'Cannot submit form: All mandatory fields must be completed. Status must be Ready.',
                'missing_fields' => $missing,
            ], 422);
        }

        // Capture immutable snapshot
        $snapshot = $form->form_data;
        $snapshot['submitted_snapshot_timestamp'] = now()->toISOString();
        $snapshot['submitted_by_user_id'] = Auth::id();
        $snapshot['submitted_by_user_name'] = Auth::user()?->acc_name ?? 'System User';

        $form->snapshot_data = $snapshot;
        $form->submitted_at = now();
        $form->submitted_by = Auth::id();
        $form->status = 'Submitted';
        $form->save();

        FormAuditLog::logAction(
            $case->ctc_id,
            $form->id,
            'form_submitted',
            "Form {$form->form_code} ({$form->instance_key}) submitted and locked with full snapshot.",
            ['status' => 'Ready'],
            ['status' => 'Submitted', 'submitted_at' => $form->submitted_at]
        );

        return response()->json([
            'success' => true,
            'message' => 'Form marked as Submitted and locked successfully.',
            'form'    => $form,
        ]);
    }

    /**
     * Decision action for Pending Removal forms ('remove' or 'keep').
     * Never hard-deletes; logs to audit.
     */
    public function pendingAction(int $formId, Request $request): JsonResponse
    {
        if (!config('hrforms.enabled', false)) {
            return response()->json(['success' => false, 'message' => 'HR Forms feature is disabled.'], 403);
        }

        $form = CaseForm::findOrFail($formId);
        $case = HrCtrCase::findOrFail($form->case_id);
        Gate::authorize('update', $case);

        if ($form->status !== 'Pending Removal') {
            return response()->json(['success' => false, 'message' => 'Form is not in Pending Removal status.'], 422);
        }

        $action = $request->input('decision'); // 'remove' or 'keep'
        if (!in_array($action, ['remove', 'keep'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid decision. Must be remove or keep.'], 422);
        }

        if ($action === 'remove') {
            $form->status = 'Pending Removal (Archived)';
            $form->is_required = false;
            $form->save();

            FormAuditLog::logAction(
                $case->ctc_id,
                $form->id,
                'pending_removal_archived',
                "User decided to archive Pending Removal form {$form->form_code} ({$form->instance_key}). Data preserved in audit log.",
                ['status' => 'Pending Removal'],
                ['status' => 'Pending Removal (Archived)']
            );

            return response()->json([
                'success' => true,
                'message' => 'Form removed from active list. Data preserved in audit log.',
            ]);
        }

        // Keep
        $form->status = 'Draft';
        $form->save();

        FormAuditLog::logAction(
            $case->ctc_id,
            $form->id,
            'pending_removal_retained',
            "User decided to retain form {$form->form_code} ({$form->instance_key}) despite hiring type change.",
            ['status' => 'Pending Removal'],
            ['status' => 'Draft']
        );

        return response()->json([
            'success' => true,
            'message' => 'Form retained as active Draft.',
        ]);
    }

    /**
     * Update progress milestone (Annex C tracker).
     */
    public function updateMilestone(int $caseId, Request $request): JsonResponse
    {
        if (!config('hrforms.enabled', false)) {
            return response()->json(['success' => false, 'message' => 'HR Forms feature is disabled.'], 403);
        }

        $case = HrCtrCase::findOrFail($caseId);
        Gate::authorize('update', $case);

        $stepCode = $request->input('step_code');
        $status = $request->input('status', 'Completed');
        $eventDate = $request->input('event_date') ? Carbon::parse($request->input('event_date'))->format('Y-m-d') : now()->format('Y-m-d');
        $note = $request->input('note');

        $milestone = CaseMilestone::updateOrCreate(
            ['case_id' => $caseId, 'step_code' => $stepCode],
            [
                'status'     => $status,
                'event_date' => $eventDate,
                'note'       => $note,
                'updated_by' => Auth::id(),
            ]
        );

        // If advertisement milestone updated, record date in CaseExtra as well
        if ($stepCode === 'advertisement') {
            $caseExtra = CaseExtra::firstOrCreate(['case_id' => $caseId]);
            $extraData = $caseExtra->extra_data ?? [];
            $extraData['advertisement_start_date'] = $eventDate;
            $caseExtra->extra_data = $extraData;
            $caseExtra->save();
        }

        FormAuditLog::logAction(
            $caseId,
            null,
            'milestone_updated',
            "Updated milestone '{$stepCode}' to {$status} ({$eventDate})",
            [],
            ['step_code' => $stepCode, 'status' => $status, 'event_date' => $eventDate, 'note' => $note]
        );

        return response()->json([
            'success'   => true,
            'message'   => "Milestone '{$stepCode}' updated successfully.",
            'milestone' => $milestone,
        ]);
    }

    /**
     * Save project extras (work_order_no, work_order_date, warranty_expiry, approved_headcount).
     * Reused across Annex A and Annex B for this project.
     */
    public function updateProjectExtras(int $caseId, Request $request): JsonResponse
    {
        if (!config('hrforms.enabled', false)) {
            return response()->json(['success' => false, 'message' => 'HR Forms feature is disabled.'], 403);
        }

        $case = HrCtrCase::with('casePlans.project')->findOrFail($caseId);
        Gate::authorize('update', $case);

        $firstPlan = $case->casePlans->first();
        $projectId = $firstPlan?->project?->prj_id ?? ($firstPlan?->ccp_prj_id ?? ($case->ctc_prj_id ?? null));

        if (!$projectId) {
            return response()->json(['success' => false, 'message' => 'Case has no assigned project to associate extras.'], 422);
        }

        $data = $request->only(['work_order_no', 'work_order_date', 'warranty_expiry', 'approved_headcount']);

        $projectExtra = ProjectExtra::updateOrCreate(
            ['project_id' => $projectId],
            $data
        );

        FormAuditLog::logAction(
            $caseId,
            null,
            'project_extras_updated',
            "Updated project extras for Project #{$projectId} (WO: " . ($data['work_order_no'] ?? 'N/A') . ")",
            [],
            $data
        );

        return response()->json([
            'success'       => true,
            'message'       => 'Project extras saved successfully and linked to project forms.',
            'project_extra' => $projectExtra,
        ]);
    }

    /**
     * Validate manual input according to annex business rules.
     */
    protected function validateManualInput(string $formCode, array $manual, array $live): ?string
    {
        // 1. Annex J Validation: 12 criteria, integer 1-5
        if ($formCode === 'RDW/HR/F-07') {
            if (isset($manual['scores']) && is_array($manual['scores'])) {
                foreach ($manual['scores'] as $key => $score) {
                    if ($score !== null && $score !== '') {
                        if (!is_numeric($score) || (int)$score < 1 || (int)$score > 5) {
                            return "Scoring criterion '{$key}' must be an integer between 1 and 5.";
                        }
                    }
                }
            }
        }

        // 2. Annex M Validation: 6 criteria, integer 0-10
        if ($formCode === 'RDW/HR/F-08') {
            if (isset($manual['marks']) && is_array($manual['marks'])) {
                foreach ($manual['marks'] as $key => $mark) {
                    if ($mark !== null && $mark !== '') {
                        if (!is_numeric($mark) || (int)$mark < 0 || (int)$mark > 10) {
                            return "Evaluation mark for '{$key}' must be an integer between 0 and 10.";
                        }
                    }
                }
            }
        }

        // 3. Annex N Validation: shift_amount mandatory when hr_balance_sufficient is false
        if ($formCode === 'RDW/HR/F-09') {
            $hrBalanceSufficient = $live['hr_balance_sufficient'] ?? true;
            if (!$hrBalanceSufficient) {
                if (!isset($manual['shift_amount']) || $manual['shift_amount'] === null || $manual['shift_amount'] === '') {
                    return 'Shift amount is required because the HR budget balance is insufficient.';
                }
                if (empty(trim((string)($manual['shift_justification'] ?? '')))) {
                    return 'Shift justification is required because the HR budget balance is insufficient.';
                }
            }
        }

        // 4. Annex B Validation: Candidate count rule (3, or 1 in single-candidate mode with justification)
        if ($formCode === 'RDW/HR/F-02') {
            $isSingleMode = (bool)($manual['single_candidate_mode'] ?? false);
            $shortlist = $manual['shortlisted_candidates'] ?? [];
            if ($isSingleMode) {
                if (empty(trim((string)($manual['single_candidate_justification'] ?? '')))) {
                    return 'Single candidate mode requires a justification.';
                }
            }
        }

        return null;
    }

    /**
     * Apply automatic calculations on manual scoring inputs.
     */
    protected function applyAutoCalculations(string $formCode, array $formData): array
    {
        // Annex J Auto Total
        if ($formCode === 'RDW/HR/F-07') {
            $scores = $formData['manual']['scores'] ?? [];
            if (is_array($scores)) {
                $total = 0;
                $allFilled = count($scores) >= 12;
                foreach ($scores as $s) {
                    if ($s !== null && is_numeric($s)) {
                        $total += (int) $s;
                    } else {
                        $allFilled = false;
                    }
                }
                $formData['manual']['total_score'] = $allFilled ? $total : null;
            }
        }

        // Annex M Auto Total & Rating
        if ($formCode === 'RDW/HR/F-08') {
            $marks = $formData['manual']['marks'] ?? [];
            if (is_array($marks)) {
                $total = 0;
                $allFilled = count($marks) >= 6;
                foreach ($marks as $m) {
                    if ($m !== null && is_numeric($m)) {
                        $total += (int) $m;
                    } else {
                        $allFilled = false;
                    }
                }
                if ($allFilled) {
                    $pct = round(($total / 60) * 100, 1);
                    $formData['manual']['total_marks'] = $total;
                    $formData['manual']['percentage'] = $pct;

                    if ($total >= 56) {
                        $rating = 'Outstanding';
                    } elseif ($total >= 51) {
                        $rating = 'Very Good';
                    } elseif ($total >= 41) {
                        $rating = 'Above Average';
                    } elseif ($total >= 31) {
                        $rating = 'Average';
                    } else {
                        $rating = 'Needs Improvement';
                    }
                    $formData['manual']['performance_rating'] = $rating;

                    $hasCitation = !empty(trim((string)($formData['manual']['exceptional_performance_citation'] ?? '')));
                    if ($pct >= 70.0) {
                        $formData['manual']['max_allowed_increment_pct'] = $hasCitation ? 20.0 : 10.0;
                    } else {
                        $formData['manual']['max_allowed_increment_pct'] = 0.0;
                    }
                } else {
                    $formData['manual']['total_marks'] = null;
                    $formData['manual']['percentage'] = null;
                    $formData['manual']['performance_rating'] = null;
                    $formData['manual']['max_allowed_increment_pct'] = 0.0;
                }
            }
        }

        return $formData;
    }

    /**
     * Resolve extractor instance.
     */
    protected function getExtractorInstance(string $code)
    {
        return match ($code) {
            'RDW/HR/F-01' => app(AnnexAExtractor::class),
            'RDW/HR/F-02' => app(AnnexBExtractor::class),
            'RDW/HR/F-07' => app(AnnexJExtractor::class),
            'ANNEX-T'     => app(AnnexTExtractor::class),
            'RDW/HR/F-09' => app(AnnexNExtractor::class),
            'RDW/HR/F-08' => app(AnnexMExtractor::class),
            'RDW/HR/F-03' => app(AnnexDExtractor::class),
            'RDW/HR/F-11' => app(AnnexUExtractor::class),
            default       => null,
        };
    }
}
