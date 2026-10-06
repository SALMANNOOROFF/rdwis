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
use App\Services\HrForms\ApprovalRoutingService;
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
        $mergedManual = array_merge($oldManual, $manualInput);

        // Update with extractor if available to refresh calculations, warnings, and missing_fields
        $extractor = $this->getExtractorInstance($form->form_code);
        if ($extractor) {
            $extracted = $extractor->extract($case, $form->instance_key, $mergedManual);
            $formData['manual'] = array_merge($mergedManual, $extracted['manual'] ?? []);
            $formData['warnings'] = $extracted['warnings'] ?? [];
            $formData['missing_fields'] = $extracted['missing_fields'] ?? [];
        } else {
            $formData['manual'] = $mergedManual;
            $formData = $this->applyAutoCalculations($form->form_code, $formData);
        }

        // Recompute completeness status
        $eval = $this->completenessService->evaluateStatus($form->form_code, $formData, $form->status);
        $formData['is_complete'] = $eval['is_complete'];
        $formData['missing_fields'] = $eval['missing_fields'];

        $oldStatus = $form->status;
        $form->status = $eval['status'];
        $form->form_data = $formData;
        $form->filled_by = Auth::id();
        $form->save();

        // Auto-generate and save draft PDF in private storage
        try {
            app(\App\Services\HrForms\FormFileStorageService::class)->generateAndSavePdf($form, Auth::id(), false);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Draft PDF auto-save failed for form {$form->id}: " . $e->getMessage());
        }

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
        $freshData['manual'] = array_merge($existingManual, $freshData['manual'] ?? []);

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

        // Auto-generate and save FINAL immutable PDF in private storage
        try {
            app(\App\Services\HrForms\FormFileStorageService::class)->generateAndSavePdf($form, Auth::id(), true);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Final PDF generation failed for form {$form->id}: " . $e->getMessage());
        }

        FormAuditLog::logAction(
            $case->ctc_id,
            $form->id,
            'form_submitted',
            "Form {$form->form_code} ({$form->instance_key}) submitted and locked with full snapshot.",
            ['status' => 'Ready'],
            ['status' => 'Submitted', 'submitted_at' => $form->submitted_at]
        );

        // If Annex B (Selection Board) is submitted, candidate is selected -> trigger joining forms (Annex D & U)
        if ($form->form_code === 'RDW/HR/F-02') {
            try {
                app(\App\Services\HrForms\FormGenerationService::class)->triggerJoiningForms($case);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Trigger joining forms on Annex B submit failed: " . $e->getMessage());
            }
        }

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

        // Keep: restore previous status recorded in audit_log when flagged as Pending Removal
        $lastLog = FormAuditLog::where('form_id', $form->id)
            ->where('action', 'status_changed')
            ->where('new_values->status', 'Pending Removal')
            ->latest('id')
            ->first();

        $restoredStatus = $lastLog?->old_values['status'] ?? 'Draft';
        // If restoredStatus was Pending Removal or empty, fallback to Draft
        if (empty($restoredStatus) || $restoredStatus === 'Pending Removal') {
            $restoredStatus = 'Draft';
        }

        $form->status = $restoredStatus;
        $form->save();

        FormAuditLog::logAction(
            $case->ctc_id,
            $form->id,
            'pending_removal_retained',
            "User decided to retain form {$form->form_code} ({$form->instance_key}) despite hiring type change. Restored status to {$restoredStatus}.",
            ['status' => 'Pending Removal'],
            ['status' => $restoredStatus]
        );

        return response()->json([
            'success' => true,
            'message' => "Form retained with restored status ({$restoredStatus}).",
            'status'  => $restoredStatus,
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

    /**
     * Download or view single form PDF (B1-B4).
     */
    public function downloadFormPdf(int $formId, Request $request)
    {
        if (!config('hrforms.enabled', false)) {
            abort(404, 'HR Forms feature is disabled.');
        }

        $form = CaseForm::findOrFail($formId);
        $case = HrCtrCase::findOrFail($form->case_id);
        Gate::authorize('view', $case);

        $isSubmitted = $form->isSubmitted();
        $sourceData = $isSubmitted ? ($form->snapshot_data ?? $form->form_data) : $form->form_data;
        $live = $sourceData['live'] ?? [];
        $manual = $sourceData['manual'] ?? [];
        $warnings = $isSubmitted ? [] : ($sourceData['warnings'] ?? []); // B4: warnings only on DRAFT

        $routingService = app(ApprovalRoutingService::class);
        $chain = $routingService->getChain($form->form_code, $case->ctc_newgrade);

        $viewName = match ($form->form_code) {
            'RDW/HR/F-01' => 'hrforms.pdf.annex_a',
            'RDW/HR/F-02' => 'hrforms.pdf.annex_b',
            'RDW/HR/F-07' => 'hrforms.pdf.annex_j',
            'ANNEX-T'     => 'hrforms.pdf.annex_t',
            'RDW/HR/F-09' => 'hrforms.pdf.annex_n',
            'RDW/HR/F-08' => 'hrforms.pdf.annex_m',
            'RDW/HR/F-03' => 'hrforms.pdf.annex_d',
            'RDW/HR/F-11' => 'hrforms.pdf.annex_u',
            default       => 'hrforms.pdf.layout',
        };

        $viewData = [
            'case'         => $case,
            'form'         => $form,
            'formCode'     => $form->form_code,
            'annex'        => $form->annex,
            'formTitle'    => $form->form_title,
            'subtitle'     => $form->instance_key !== 'main' ? "Candidate Screening: {$form->instance_key}" : null,
            'live'         => $live,
            'manual'       => $manual,
            'chain'        => $chain,
            'warnings'     => $warnings,
            'isSubmitted'  => $isSubmitted,
            'submittedAt'  => $form->submitted_at?->format('d M Y H:i'),
            'submittedBy'  => $form->submitter?->acc_name ?? 'Authorized Officer',
        ];

        // Audit log download (insert-only)
        FormAuditLog::logAction(
            $case->ctc_id,
            $form->id,
            'pdf_downloaded',
            "Downloaded PDF for {$form->form_code} ({$form->instance_key})",
            ['status' => $form->status]
        );

        return app(\App\Services\HrForms\FormFileStorageService::class)->downloadPdfResponse($form);
    }

    /**
     * Download merged case file dossier with cover page (B3).
     */
    public function downloadCaseDossier(int $caseId, Request $request)
    {
        if (!config('hrforms.enabled', false)) {
            abort(404, 'HR Forms feature is disabled.');
        }

        $case = HrCtrCase::findOrFail($caseId);
        Gate::authorize('view', $case);

        // Fetch active forms excluding archived Pending Removal and Scheduled-empty forms
        $forms = CaseForm::where('case_id', $caseId)
            ->where('status', '!=', 'Pending Removal (Archived)')
            ->where(function($q) {
                $q->where('status', '!=', 'Scheduled')
                  ->orWhereNotNull('submitted_at');
            })
            ->get();

        // Sort in order: A, B, J (cand_1..3), T, N, M, D, U
        $orderMap = [
            'RDW/HR/F-01' => 1,
            'RDW/HR/F-02' => 2,
            'RDW/HR/F-07' => 3,
            'ANNEX-T'     => 4,
            'RDW/HR/F-09' => 5,
            'RDW/HR/F-08' => 6,
            'RDW/HR/F-03' => 7,
            'RDW/HR/F-11' => 8,
        ];

        $sortedForms = $forms->sortBy(function($f) use ($orderMap) {
            $baseOrder = $orderMap[$f->form_code] ?? 99;
            $inst = $f->instance_key;
            return sprintf('%02d_%s', $baseOrder, $inst);
        });

        $enclosedList = $sortedForms->map(function($f) {
            return [
                'form_code'    => $f->form_code,
                'annex'        => $f->annex,
                'title'        => $f->form_title,
                'instance_key' => $f->instance_key,
                'status'       => $f->status,
            ];
        })->toArray();

        $hiringType = app(FormGenerationService::class)->resolveHiringType($case);

        // Log dossier download in audit_log
        FormAuditLog::logAction(
            $case->ctc_id,
            null,
            'dossier_downloaded',
            "Downloaded consolidated case file dossier (" . count($enclosedList) . " enclosed forms)"
        );

        $dossierPdf = app(\App\Services\HrForms\FormFileStorageService::class)->renderDossierContent($caseId);
        $fileName = "CaseFile_CC-{$case->ctc_id}.pdf";

        return response($dossierPdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Content-Length'      => strlen($dossierPdf),
        ]);
    }
}
