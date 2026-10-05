<?php

namespace App\Services\HrForms;

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use App\Models\HrForms\CaseForm;
use App\Models\HrForms\CaseMilestone;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProgressTrackerService
{
    protected FormGenerationService $generationService;

    public function __construct(FormGenerationService $generationService)
    {
        $this->generationService = $generationService;
    }

    /**
     * Get ordered steps and their statuses for a case.
     */
    public function getTrackerSteps(HrCtrCase $case): array
    {
        $hiringType = $this->generationService->resolveHiringType($case);
        $applicableCodes = $this->getApplicableStepCodes($hiringType);

        // Fetch milestone overrides
        $milestones = CaseMilestone::where('case_id', $case->ctc_id)->get()->keyBy('step_code');
        $caseForms = CaseForm::where('case_id', $case->ctc_id)->get()->keyBy(fn($f) => "{$f->form_code}_{$f->instance_key}");
        $caseExtra = CaseExtra::where('case_id', $case->ctc_id)->first();

        $allStepDefinitions = [
            'in_principle_approval' => [
                'code'  => 'in_principle_approval',
                'title' => 'In-Principle Approval',
                'form'  => in_array($hiringType, ['Extension', 'Renewal']) ? 'Annex N (F-09)' : 'Annex A (F-01)',
            ],
            'jd_formulation' => [
                'code'  => 'jd_formulation',
                'title' => 'JD Formulated',
                'form'  => null,
            ],
            'advertisement' => [
                'code'  => 'advertisement',
                'title' => 'Advertisement',
                'form'  => null,
            ],
            'initial_screening' => [
                'code'  => 'initial_screening',
                'title' => 'Initial Screening',
                'form'  => 'Annex J (F-07)',
            ],
            'selection_board' => [
                'code'  => 'selection_board',
                'title' => 'Selection Board',
                'form'  => 'Annex B (F-02) / Annex T',
            ],
            'competent_authority_approval' => [
                'code'  => 'competent_authority_approval',
                'title' => ($hiringType === 'Internship_Extension') ? 'MD RDW Approval (Para 31g)' : 'MD/DG Approval',
                'form'  => null,
            ],
            'secler_clearance' => [
                'code'  => 'secler_clearance',
                'title' => 'SECLER',
                'form'  => null,
            ],
            'contract_signing' => [
                'code'  => 'contract_signing',
                'title' => 'Contract Signing',
                'form'  => 'Contract',
            ],
            'joining' => [
                'code'  => 'joining',
                'title' => 'Joining',
                'form'  => 'Annex U (F-11)',
            ],
        ];

        $steps = [];

        foreach ($applicableCodes as $code) {
            $def = $allStepDefinitions[$code];
            $ms = $milestones->get($code);
            $derived = $this->deriveStepStatus($code, $case, $caseForms, $caseExtra, $ms, $hiringType);

            $steps[] = [
                'code'        => $code,
                'title'       => $def['title'],
                'form'        => $def['form'],
                'status'      => $derived['status'],
                'source'      => $derived['source'],
                'event_date'  => $derived['event_date'],
                'note'        => $derived['note'],
                'can_edit'    => $derived['can_edit'],
            ];
        }

        // Advertisement 14-day warning check
        $adWarning = $this->checkAdvertisementWarning($case, $milestones, $caseExtra);

        return [
            'hiring_type' => $hiringType,
            'steps'       => $steps,
            'ad_warning'  => $adWarning,
        ];
    }

    /**
     * Determine which steps apply to the specific hiring type.
     */
    public function getApplicableStepCodes(string $hiringType): array
    {
        return match ($hiringType) {
            'Fresh' => [
                'in_principle_approval',
                'jd_formulation',
                'advertisement',
                'initial_screening',
                'selection_board',
                'competent_authority_approval',
                'secler_clearance',
                'contract_signing',
                'joining',
            ],
            'Rehiring' => [
                'in_principle_approval',
                'selection_board',
                'competent_authority_approval',
                'contract_signing',
                'joining',
            ],
            'Extension', 'Renewal' => [
                'in_principle_approval',
                'competent_authority_approval',
                'contract_signing',
                'joining',
            ],
            'Internship' => [
                'selection_board',
                'competent_authority_approval',
                'joining',
            ],
            'Internship_Extension' => [
                'competent_authority_approval',
            ],
            default => [
                'in_principle_approval',
                'competent_authority_approval',
                'contract_signing',
                'joining',
            ],
        };
    }

    /**
     * Derive status, source, and date for each tracker step.
     */
    protected function deriveStepStatus(
        string $code,
        HrCtrCase $case,
        $caseForms,
        ?CaseExtra $caseExtra,
        ?CaseMilestone $ms,
        string $hiringType
    ): array {
        // Milestone override takes priority if explicitly set to Completed or Skipped
        if ($ms && in_array($ms->status, ['Completed', 'Skipped'])) {
            return [
                'status'     => $ms->status,
                'source'     => 'hrforms.case_milestones',
                'event_date' => $ms->event_date?->format('Y-m-d'),
                'note'       => $ms->note,
                'can_edit'   => true,
            ];
        }

        switch ($code) {
            case 'in_principle_approval':
                $formCode = in_array($hiringType, ['Extension', 'Renewal']) ? 'RDW/HR/F-09_main' : 'RDW/HR/F-01_main';
                $form = $caseForms->get($formCode);
                if ($form && $form->status === 'Submitted') {
                    return [
                        'status'     => 'Completed',
                        'source'     => "{$form->form_code} (Submitted)",
                        'event_date' => $form->submitted_at?->format('Y-m-d'),
                        'note'       => 'Submitted for approval',
                        'can_edit'   => false,
                    ];
                }
                if ($form && in_array($form->status, ['Ready', 'Pending Input', 'Draft'])) {
                    return [
                        'status'     => 'In Progress',
                        'source'     => "{$form->form_code} ({$form->status})",
                        'event_date' => null,
                        'note'       => null,
                        'can_edit'   => false,
                    ];
                }
                return [
                    'status'     => 'Pending',
                    'source'     => 'Auto-derived from form',
                    'event_date' => null,
                    'note'       => null,
                    'can_edit'   => false,
                ];

            case 'jd_formulation':
                if ($ms) {
                    return [
                        'status'     => $ms->status,
                        'source'     => 'hrforms.case_milestones',
                        'event_date' => $ms->event_date?->format('Y-m-d'),
                        'note'       => $ms->note,
                        'can_edit'   => true,
                    ];
                }
                // If case is saved with newjobtitle, JD is in progress
                return [
                    'status'     => !empty($case->ctc_newjobtitle) ? 'In Progress' : 'Pending',
                    'source'     => 'Case Job Title / Milestone',
                    'event_date' => null,
                    'note'       => null,
                    'can_edit'   => true,
                ];

            case 'advertisement':
                if ($ms) {
                    return [
                        'status'     => $ms->status,
                        'source'     => 'hrforms.case_milestones',
                        'event_date' => $ms->event_date?->format('Y-m-d'),
                        'note'       => $ms->note,
                        'can_edit'   => true,
                    ];
                }
                return [
                    'status'     => 'Pending',
                    'source'     => 'hrforms.case_milestones',
                    'event_date' => null,
                    'note'       => null,
                    'can_edit'   => true,
                ];

            case 'initial_screening':
                $jForms = $caseForms->filter(fn($f) => str_starts_with($f->form_code, 'RDW/HR/F-07'));
                if ($jForms->isNotEmpty()) {
                    $allSubmitted = $jForms->every(fn($f) => $f->status === 'Submitted');
                    if ($allSubmitted) {
                        return [
                            'status'     => 'Completed',
                            'source'     => 'RDW/HR/F-07 (All Candidates Screened)',
                            'event_date' => $jForms->first()->submitted_at?->format('Y-m-d'),
                            'note'       => null,
                            'can_edit'   => false,
                        ];
                    }
                    return [
                        'status'     => 'In Progress',
                        'source'     => 'RDW/HR/F-07 (Screening in Progress)',
                        'event_date' => null,
                        'note'       => null,
                        'can_edit'   => false,
                    ];
                }
                return [
                    'status'     => 'Pending',
                    'source'     => 'RDW/HR/F-07 / Milestone',
                    'event_date' => null,
                    'note'       => null,
                    'can_edit'   => true,
                ];

            case 'selection_board':
                $bForm = $caseForms->get('RDW/HR/F-02_main');
                if ($bForm && $bForm->status === 'Submitted') {
                    return [
                        'status'     => 'Completed',
                        'source'     => 'RDW/HR/F-02 (Board Selection Submitted)',
                        'event_date' => $bForm->submitted_at?->format('Y-m-d'),
                        'note'       => null,
                        'can_edit'   => false,
                    ];
                }
                if ($caseExtra?->interview_date) {
                    $now = now()->startOfDay();
                    $intDate = Carbon::parse($caseExtra->interview_date)->startOfDay();
                    $isPast = $now->gte($intDate);
                    return [
                        'status'     => $isPast ? 'In Progress' : 'Pending',
                        'source'     => 'Interview Scheduled (' . $intDate->format('d M Y') . ')',
                        'event_date' => $intDate->format('Y-m-d'),
                        'note'       => null,
                        'can_edit'   => true,
                    ];
                }
                return [
                    'status'     => 'Pending',
                    'source'     => 'RDW/HR/F-02 / Milestone',
                    'event_date' => null,
                    'note'       => null,
                    'can_edit'   => true,
                ];

            case 'competent_authority_approval':
                $statusUpper = strtoupper((string)$case->ctc_status);
                if (in_array($statusUpper, ['APPROVED', 'OFFER ISSUED', 'JOINED', 'COMPLETED'])) {
                    return [
                        'status'     => 'Completed',
                        'source'     => "hr.ctrcases (Status: {$case->ctc_status})",
                        'event_date' => $case->updated_at?->format('Y-m-d'),
                        'note'       => 'Competent authority approval granted',
                        'can_edit'   => false,
                    ];
                }
                if ($statusUpper === 'SUBMITTED') {
                    return [
                        'status'     => 'In Progress',
                        'source'     => "hr.ctrcases (Submitted for approval)",
                        'event_date' => null,
                        'note'       => 'Pending authority approval',
                        'can_edit'   => false,
                    ];
                }
                return [
                    'status'     => 'Pending',
                    'source'     => 'hr.ctrcases (Draft)',
                    'event_date' => null,
                    'note'       => null,
                    'can_edit'   => false,
                ];

            case 'secler_clearance':
                if ($ms) {
                    return [
                        'status'     => $ms->status,
                        'source'     => 'hrforms.case_milestones',
                        'event_date' => $ms->event_date?->format('Y-m-d'),
                        'note'       => $ms->note,
                        'can_edit'   => true,
                    ];
                }
                return [
                    'status'     => 'Pending',
                    'source'     => 'hrforms.case_milestones',
                    'event_date' => null,
                    'note'       => null,
                    'can_edit'   => true,
                ];

            case 'contract_signing':
                $contract = DB::table('hr.contracts')->where('ctr_ctc_id', $case->ctc_id)->first();
                if ($contract) {
                    return [
                        'status'     => 'Completed',
                        'source'     => "hr.contracts (Contract #{$contract->ctr_id})",
                        'event_date' => $contract->ctr_startdt ? Carbon::parse($contract->ctr_startdt)->format('Y-m-d') : null,
                        'note'       => 'Contract generated and signed',
                        'can_edit'   => false,
                    ];
                }
                if (in_array(strtoupper((string)$case->ctc_status), ['APPROVED', 'OFFER ISSUED'])) {
                    return [
                        'status'     => 'In Progress',
                        'source'     => 'Case Approved, awaiting signing',
                        'event_date' => null,
                        'note'       => null,
                        'can_edit'   => false,
                    ];
                }
                return [
                    'status'     => 'Pending',
                    'source'     => 'hr.contracts',
                    'event_date' => null,
                    'note'       => null,
                    'can_edit'   => false,
                ];

            case 'joining':
                if (strtoupper((string)$case->ctc_status) === 'JOINED') {
                    return [
                        'status'     => 'Completed',
                        'source'     => 'hr.ctrcases (Status: Joined)',
                        'event_date' => $case->updated_at?->format('Y-m-d'),
                        'note'       => 'Employee reported and joined',
                        'can_edit'   => false,
                    ];
                }
                $uForm = $caseForms->get('RDW/HR/F-11_main');
                if ($uForm && $uForm->status === 'Submitted') {
                    return [
                        'status'     => 'Completed',
                        'source'     => 'RDW/HR/F-11 (Submitted)',
                        'event_date' => $uForm->submitted_at?->format('Y-m-d'),
                        'note'       => 'Joining data recorded',
                        'can_edit'   => false,
                    ];
                }
                return [
                    'status'     => 'Pending',
                    'source'     => 'hr.ctrcases / Annex U',
                    'event_date' => null,
                    'note'       => null,
                    'can_edit'   => false,
                ];

            default:
                return [
                    'status'     => 'Pending',
                    'source'     => 'Unknown',
                    'event_date' => null,
                    'note'       => null,
                    'can_edit'   => true,
                ];
        }
    }

    /**
     * Check 14-day advertisement rule between advertisement start and interview date.
     */
    public function checkAdvertisementWarning(HrCtrCase $case, $milestones, ?CaseExtra $caseExtra): ?array
    {
        $adMilestone = $milestones->get('advertisement');
        $adDateStr = $adMilestone?->event_date?->format('Y-m-d') ?? ($caseExtra->extra_data['advertisement_start_date'] ?? null);
        $intDateStr = $caseExtra?->interview_date?->format('Y-m-d');

        if (!$adDateStr || !$intDateStr) {
            return null;
        }

        $adDate = Carbon::parse($adDateStr);
        $intDate = Carbon::parse($intDateStr);

        $diffDays = $adDate->diffInDays($intDate, false);

        $hasExemption = (bool) ($caseExtra->advertisement_exemption ?? false);
        $exemptionJustification = $caseExtra->advertisement_exemption_justification ?? null;

        if ($diffDays < 14) {
            if ($hasExemption) {
                return [
                    'has_warning'  => false,
                    'is_exempted'   => true,
                    'days'         => $diffDays,
                    'justification'=> $exemptionJustification,
                    'message'      => "Advertisement period was {$diffDays} days (< 14 days required), but an approved exemption is recorded.",
                ];
            }

            return [
                'has_warning'  => true,
                'is_exempted'   => false,
                'days'         => $diffDays,
                'justification'=> null,
                'message'      => "Policy requires at least 14 days between advertisement start and interview date (Para 29). Currently {$diffDays} days. Record an exemption with justification to clear this warning.",
            ];
        }

        return null;
    }
}
