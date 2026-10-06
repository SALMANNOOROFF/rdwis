<?php

namespace App\Services\HrForms;

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use App\Models\HrForms\CaseForm;
use App\Models\HrForms\FormAuditLog;
use App\Models\HrForms\FormMatrix;
use App\Services\HrForms\Extractors\AnnexAExtractor;
use App\Services\HrForms\Extractors\AnnexBExtractor;
use App\Services\HrForms\Extractors\AnnexDExtractor;
use App\Services\HrForms\Extractors\AnnexJExtractor;
use App\Services\HrForms\Extractors\AnnexMExtractor;
use App\Services\HrForms\Extractors\AnnexNExtractor;
use App\Services\HrForms\Extractors\AnnexTExtractor;
use App\Services\HrForms\Extractors\AnnexUExtractor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FormGenerationService
{
    protected FormCompletenessService $completenessService;

    public function __construct(FormCompletenessService $completenessService)
    {
        $this->completenessService = $completenessService;
    }

    /**
     * Map case's hiring type to Fresh / Extension / Renewal / Rehiring / Internship
     */
    public function resolveHiringType(HrCtrCase $case): string
    {
        $grade = strtoupper(trim((string)($case->ctc_newgrade ?? '')));
        $jobTitle = strtolower(trim((string)($case->ctc_newjobtitle ?? '')));
        $rawType = (string)($case->ctc_type ?? '');

        // Configurable intern keywords
        $internGrades = config('hrforms.intern_keywords.grades', ['INTERNEE', 'INTERN']);
        $internTitles = config('hrforms.intern_keywords.job_titles', ['intern', 'internee', 'trainee']);

        $isIntern = in_array($grade, array_map('strtoupper', $internGrades), true);
        if (!$isIntern) {
            foreach ($internTitles as $t) {
                if (str_contains($jobTitle, strtolower($t))) {
                    $isIntern = true;
                    break;
                }
            }
        }

        $baseType = \App\Models\HrForms\HiringTypeMap::resolveHiringType($rawType);

        if ($isIntern) {
            // Para 31(g): Fresh intern requires board forms (Internship), but intern extension/renewal requires only MD RDW approval
            if (in_array($baseType, ['Renewal', 'Extension'], true)) {
                return 'Internship_Extension';
            }
            return 'Internship';
        }

        return $baseType;
    }

    /**
     * Resolve required forms for a case based on matrix and condition rules.
     */
    public function resolveRequiredForms(HrCtrCase $case): array
    {
        $hiringType = $this->resolveHiringType($case);

        // Para 31(g): Intern extension or renewal needs MD RDW approval only - generate NO board forms
        if ($hiringType === 'Internship_Extension' && config('hrforms.intern_extension_no_board_forms', true)) {
            return [];
        }
        $caseExtra = CaseExtra::where('case_id', $case->ctc_id)->first();

        // Evaluate condition rule values
        $isSingleCandidateMode = false;
        if ($hiringType === 'Internship') {
            $isSingleCandidateMode = true;
        } elseif ($hiringType === 'Rehiring') {
            $isSingleCandidateMode = config('hrforms.rehiring_single_candidate', true)
                || ($caseExtra->single_candidate_mode ?? false);
        } else {
            $isSingleCandidateMode = ($caseExtra->single_candidate_mode ?? false);
        }

        $rulesState = [
            'headcount_not_in_proposal' => $caseExtra ? (!$caseExtra->headcount_in_proposal) : true,
            'single_candidate_allowed'  => $isSingleCandidateMode,
            'single_candidate_internee' => ($hiringType === 'Internship'),
            'relaxed_candidate_min'     => $isSingleCandidateMode || ($hiringType === 'Internship'),
            'attach_last_appraisal'     => (bool) ($caseExtra->extra_data['attach_last_appraisal'] ?? false),
        ];

        $matrixRows = FormMatrix::where('hiring_type', $hiringType)
            ->where('is_active', true)
            ->orderBy('default_order')
            ->get();

        $formTitles = [
            'RDW/HR/F-01' => ['annex' => 'A', 'title' => 'In-Principle Approval for Hiring of HR (F-01)'],
            'RDW/HR/F-02' => ['annex' => 'B', 'title' => 'Hiring Board for Selection of Contract Employee (F-02)'],
            'RDW/HR/F-07' => ['annex' => 'J', 'title' => 'Initial Interview / Screening Form (F-07)'],
            'ANNEX-T'     => ['annex' => 'T', 'title' => 'Comparison Matrix of Shortlisted Candidates'],
            'RDW/HR/F-09' => ['annex' => 'N', 'title' => 'Renewal / Extension of Contract(s) (F-09)'],
            'RDW/HR/F-08' => ['annex' => 'M', 'title' => 'Performance Appraisal Form (F-08)'],
            'RDW/HR/F-03' => ['annex' => 'D', 'title' => 'Non-Disclosure Agreement (F-03)'],
            'RDW/HR/F-11' => ['annex' => 'U', 'title' => 'Personal Data Form (F-11)'],
        ];

        $resolved = [];

        foreach ($matrixRows as $row) {
            $code = $row->form_code;
            $req = $row->requirement;
            $rule = $row->condition_rule;

            if ($req === 'not_required') {
                continue;
            }

            $effectiveReq = $req;
            $isRequired = ($req === 'required');

            if ($rule && isset($rulesState[$rule])) {
                $rulePassed = $rulesState[$rule];
                if ($req === 'conditional') {
                    if (!$rulePassed) {
                        continue; // Condition failed, form not required
                    }
                    $isRequired = true;
                    $effectiveReq = 'required';
                } elseif ($req === 'optional') {
                    if (!$rulePassed) {
                        continue;
                    }
                }
            }

            $meta = $formTitles[$code] ?? ['annex' => $code, 'title' => $code];

            $resolved[] = [
                'form_code'          => $code,
                'annex'              => $meta['annex'],
                'form_title'         => $meta['title'],
                'hiring_type'        => $hiringType,
                'requirement'        => $effectiveReq,
                'is_required'        => $isRequired,
                'is_single_candidate'=> $isSingleCandidateMode,
            ];
        }

        return $resolved;
    }

    /**
     * Idempotent sync of forms for a contract case.
     */
    public function syncForms(HrCtrCase $case): array
    {
        // Fail-safe feature flag: if disabled, complete no-op (zero hrforms queries)
        if (!config('hrforms.enabled', false)) {
            return [
                'success' => true,
                'synced'  => 0,
                'message' => 'HR Forms feature is disabled (no-op).',
            ];
        }

        // Only generate for Draft / Under Revision cases
        $allowedStatuses = ['Draft', 'Under Revision'];
        if (!in_array($case->ctc_status, $allowedStatuses, true)) {
            return [
                'success' => true,
                'synced'  => 0,
                'message' => "Case is in '{$case->ctc_status}' status. Forms cannot be modified.",
            ];
        }

        $hiringType = $this->resolveHiringType($case);
        $requiredForms = $this->resolveRequiredForms($case);
        $desiredKeys = [];
        $isSingleCandidate = false;

        foreach ($requiredForms as $rf) {
            $code = $rf['form_code'];
            $isSingleCandidate = $rf['is_single_candidate'];

            if ($code === 'RDW/HR/F-07') {
                // Annex J: 3 instances normally, 1 in single-candidate mode
                if ($isSingleCandidate) {
                    $instances = ['cand_1'];
                } else {
                    $instances = ['cand_1', 'cand_2', 'cand_3'];
                }
            } else {
                $instances = ['main'];
            }

            foreach ($instances as $inst) {
                $key = "{$code}_{$inst}";
                $desiredKeys[$key] = array_merge($rf, ['instance_key' => $inst]);
            }
        }

        // Fetch existing active forms for this case
        $existingForms = CaseForm::where('case_id', $case->ctc_id)->get()->keyBy(function ($f) {
            return "{$f->form_code}_{$f->instance_key}";
        });

        $pendingRemovalForms = [];
        $syncedCount = 0;

        // 1. Handle existing forms that are no longer required
        foreach ($existingForms as $key => $form) {
            if ($form->isSubmitted()) {
                continue; // Never alter a submitted form
            }

            if (!isset($desiredKeys[$key])) {
                if ($this->completenessService->hasManualData($form->form_data ?? [])) {
                    // Form has manual data: mark as 'Pending Removal' and do NOT delete
                    if ($form->status !== 'Pending Removal') {
                        $oldStatus = $form->status;
                        $form->status = 'Pending Removal';
                        $form->save();

                        FormAuditLog::logAction(
                            $case->ctc_id,
                            $form->id,
                            'status_changed',
                            "Marked as Pending Removal on hiring type change (contains manual user data)",
                            ['status' => $oldStatus],
                            ['status' => 'Pending Removal']
                        );
                    }
                    $pendingRemovalForms[] = $form;
                } else {
                    // No manual data: soft-delete it
                    $formId = $form->id;
                    $form->delete();

                    FormAuditLog::logAction(
                        $case->ctc_id,
                        $formId,
                        'form_removed',
                        "Soft-deleted unused form {$form->form_code} ({$form->instance_key})"
                    );
                }
            }
        }

        // 2. Add missing or refresh live data in existing forms
        foreach ($desiredKeys as $key => $target) {
            $code = $target['form_code'];
            $inst = $target['instance_key'];
            $req = $target['requirement'];
            $existing = $existingForms->get($key);

            if ($existing) {
                if ($existing->isSubmitted()) {
                    continue; // Never modify submitted form
                }

                if ($req === 'at_joining') {
                    // Joining forms (Annex D and U) remain Scheduled with no pre-fill until joining trigger
                    continue;
                }

                // Refresh live data while preserving user manual layer
                $extractor = $this->getExtractor($code);
                if ($extractor) {
                    $existingManual = $existing->form_data['manual'] ?? [];
                    $freshData = $extractor->extract($case, $inst, $existingManual);
                    
                    // Evaluate new status
                    $eval = $this->completenessService->evaluateStatus($code, $freshData, $existing->status);
                    $freshData['is_complete'] = $eval['is_complete'];
                    
                    $existing->form_data = $freshData;
                    $existing->status = $eval['status'];
                    $existing->is_required = $target['is_required'];
                    $existing->save();

                    // Ensure draft PDF exists in private storage
                    if (!\App\Models\HrForms\FormFile::where('case_form_id', $existing->id)->exists()) {
                        try {
                            app(\App\Services\HrForms\FormFileStorageService::class)->generateAndSavePdf($existing, null, false);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning("Draft PDF auto-generation on sync failed for form {$existing->id}: " . $e->getMessage());
                        }
                    }

                    FormAuditLog::logAction(
                        $case->ctc_id,
                        $existing->id,
                        'live_data_refreshed',
                        "Refreshed live extraction layer for {$code} ({$inst})"
                    );
                    $syncedCount++;
                }
            } else {
                // Create new form
                $form = new CaseForm([
                    'case_id'      => $case->ctc_id,
                    'form_code'    => $code,
                    'annex'        => $target['annex'],
                    'instance_key' => $inst,
                    'form_title'   => $target['form_title'],
                    'is_required'  => $target['is_required'],
                ]);

                if ($req === 'at_joining') {
                    // Annex D and U created as Scheduled and NOT prefilled at draft time
                    $form->status = 'Scheduled';
                    $form->form_data = [
                        'live'           => [],
                        'manual'         => [],
                        'warnings'       => [],
                        'missing_fields' => ['joining_not_triggered'],
                        'is_complete'    => false,
                    ];
                } else {
                    $extractor = $this->getExtractor($code);
                    if ($extractor) {
                        $extracted = $extractor->extract($case, $inst, []);
                        $eval = $this->completenessService->evaluateStatus($code, $extracted, 'Draft');
                        $extracted['is_complete'] = $eval['is_complete'];

                        $form->status = $eval['status'];
                        $form->form_data = $extracted;
                    } else {
                        $form->status = 'Draft';
                        $form->form_data = [];
                    }
                }

                $form->save();

                // Auto-generate and save draft PDF in private storage
                try {
                    app(\App\Services\HrForms\FormFileStorageService::class)->generateAndSavePdf($form, null, false);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Initial PDF generation failed for form {$form->id}: " . $e->getMessage());
                }

                FormAuditLog::logAction(
                    $case->ctc_id,
                    $form->id,
                    'form_created',
                    "Initialized form {$code} ({$inst}) in status {$form->status}"
                );
                $syncedCount++;
            }
        }

        $note = ($hiringType === 'Internship_Extension') ? 'MD RDW approval, Para 31g' : null;

        return [
            'success'                => true,
            'synced'                 => $syncedCount,
            'hiring_type'            => $hiringType,
            'note'                   => $note,
            'pending_removal_forms'  => $pendingRemovalForms,
            'message'                => $note ?? 'HR Forms synchronized successfully.',
        ];
    }

    /**
     * Trigger joining forms (Annex D and Annex U) when candidate is selected / case approved.
     */
    public function triggerJoiningForms(HrCtrCase $case): array
    {
        $joiningCodes = ['RDW/HR/F-03', 'RDW/HR/F-11'];
        $forms = CaseForm::where('case_id', $case->ctc_id)
            ->whereIn('form_code', $joiningCodes)
            ->get();

        $triggered = 0;
        foreach ($forms as $form) {
            $extractor = $this->getExtractor($form->form_code);
            if ($extractor) {
                $manual = $form->form_data['manual'] ?? [];
                $extracted = $extractor->extract($case, $form->instance_key, $manual);
                $eval = $this->completenessService->evaluateStatus($form->form_code, $extracted, 'Draft');

                $form->status = $eval['status'];
                $form->form_data = $extracted;
                $form->save();

                FormAuditLog::logAction(
                    $case->ctc_id,
                    $form->id,
                    'status_changed',
                    "Triggered joining form {$form->form_code} from Scheduled to {$form->status}"
                );
                $triggered++;
            }
        }

        return ['success' => true, 'triggered' => $triggered];
    }

    /**
     * Resolve extractor instance for a form code.
     */
    protected function getExtractor(string $code)
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
