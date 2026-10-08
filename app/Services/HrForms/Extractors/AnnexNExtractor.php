<?php

namespace App\Services\HrForms\Extractors;

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use App\Models\HrForms\CaseForm;
use App\Services\FinancialIntelligenceService;
use App\Services\HrForms\ApprovalRoutingService;
use App\Services\HrForms\SalaryBandService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Annex N: Renewal / Extension of Contract(s) (RDW/HR/F-09)
 *
 * Extracts contract renewal metrics, salary increment percentage checks (Para 67),
 * subhead allocations, and shift amount recommendations.
 */
class AnnexNExtractor implements FormExtractorInterface
{
    protected SalaryBandService $salaryBandService;
    protected ApprovalRoutingService $routingService;
    protected FinancialIntelligenceService $finService;

    public function __construct(
        SalaryBandService $salaryBandService,
        ApprovalRoutingService $routingService,
        FinancialIntelligenceService $finService
    ) {
        $this->salaryBandService = $salaryBandService;
        $this->routingService = $routingService;
        $this->finService = $finService;
    }

    public function extract(HrCtrCase $case, string $instanceKey = 'main', array $existingManualData = []): array
    {
        // 1. Division & Project Details
        $divisionName = $case->division_name ?? 'Headquarters / Directorate';
        $headId = $case->ctc_prj_id ?? ($case->casePlans->first()?->ccp_hed_id ?? 0);
        $head = $headId ? DB::table('cen.heads')->where('hed_id', $headId)->first() : null;
        $prjId = $head->hed_prj_id ?? 0;
        $project = $prjId ? DB::table('prj.projects')->where('prj_id', $prjId)->first() : null;

        $projectTitle = $project->prj_title ?? ($head->hed_name ?? $case->project_name ?? null);
        $projectCode = $project->prj_code ?? ($head->hed_code ?? $case->project_code ?? null);
        $projectStart = $project->prj_startdt ?? $case->ctc_newstartdt;
        $projectEnd = $project->prj_estenddt ?? ($project->prj_enddt ?? $case->ctc_newenddt);
        
        $totalProjectBudget = ($project && $project->prj_aprvcost !== null) ? (float) $project->prj_aprvcost : null;
        $budgetAvailable = ($totalProjectBudget !== null);

        // 2. Subhead metrics from FinancialIntelligenceService (indexed array parsing per FIX 7)
        $hrAlloc = null;
        $hrUtilized = null;
        $hrRemaining = null;
        $hrCommitted = null;
        $hrAvailable = false;

        $equipAlloc = null;
        $equipUtilized = null;
        $equipRemaining = null;
        $equipCommitted = null;
        $equipAvailable = false;

        if ($headId) {
            $breakdown = $this->finService->getSubheadBreakdown($headId);
            if (is_array($breakdown)) {
                foreach ($breakdown as $item) {
                    $itemArr = (array) $item;
                    $name = $itemArr['name'] ?? null;
                    if ($name === 'HR') {
                        $hrAlloc = (float) ($itemArr['allocation'] ?? 0);
                        $hrUtilized = (float) ($itemArr['expenditure'] ?? 0);
                        $hrRemaining = (float) ($itemArr['remaining'] ?? 0);
                        $hrCommitted = (float) ($itemArr['commitments'] ?? 0);
                        $hrAvailable = true;
                    } elseif ($name === 'Equipment') {
                        $equipAlloc = (float) ($itemArr['allocation'] ?? 0);
                        $equipUtilized = (float) ($itemArr['expenditure'] ?? 0);
                        $equipRemaining = (float) ($itemArr['remaining'] ?? 0);
                        $equipCommitted = (float) ($itemArr['commitments'] ?? 0);
                        $equipAvailable = true;
                    }
                }
            }
        }

        // Case cost
        $casePrice = (float) ($case->ctc_price ?: ($case->ctc_newsalary * $case->tenure_months));
        $hrCommittedTotal = $hrAvailable ? ($hrCommitted + $casePrice) : null;
        $isHrDeficit = $hrAvailable ? ($hrRemaining < $casePrice) : true;
        $suggestedShortfall = ($hrAvailable && $isHrDeficit) ? max(0.0, round($casePrice - $hrRemaining, 2)) : null;

        // 3. Employee details & prior contract
        $emp = $case->employee;
        $empName = $case->ctc_empnamecomp ?: ($emp->emp_name ?? null);
        $grade = $case->ctc_newgrade;
        $prevContract = $case->effective_previous_contract;
        $expiryDate = $prevContract?->ctr_enddt ?? null;
        $currentSalary = (float) ($case->getAttributes()['previous_salary'] ?? $prevContract?->ctr_salary ?? $case->previous_salary ?? 0);
        $proposedSalary = (float) $case->ctc_newsalary;

        $startDate = $case->ctc_newstartdt;
        $endDate = $case->ctc_newenddt;

        $isRenewal = \App\Models\HrForms\HiringTypeMap::resolveHiringType($case->ctc_type) === 'Renewal';

        $warnings = [];

        // Project dates mismatch warning (A4)
        if ($projectEnd && $case->ctc_newstartdt) {
            if (Carbon::parse($projectEnd)->lt(Carbon::parse($case->ctc_newstartdt))) {
                $warnings[] = "Project estimated end date (" . Carbon::parse($projectEnd)->format('d M Y') . ") is before the contract start date (" . Carbon::parse($case->ctc_newstartdt)->format('d M Y') . "). Contract extends beyond project life cycle.";
            }
        }

        // 4. Increment check per A1: when no Annex M marks exist, do NOT quote 20%
        $maxAllowed = 'appraisal pending';

        // Check if Annex M exists on this case
        $mForm = CaseForm::where('case_id', $case->ctc_id)
            ->where('form_code', 'RDW/HR/F-08')
            ->first();
        $mMarks = $mForm?->form_data['manual']['marks'] ?? null;
        $mPercentage = $mForm?->form_data['manual']['percentage'] ?? null;
        $hasAppraisalMarks = is_array($mMarks) && !empty(array_filter($mMarks, fn($v) => $v !== null));

        if ($isRenewal && $currentSalary > 0) {
            $diff = $proposedSalary - $currentSalary;
            $pct = round(($diff / $currentSalary) * 100, 1);

            if (!$hasAppraisalMarks || $mPercentage === null) {
                $maxAllowed = 'appraisal pending';
                if ($pct > 0.0) {
                    $warnings[] = "Appraisal pending, increment limit cannot be checked (Para 67).";
                }
            } else {
                $maxAllowed = ($mPercentage >= 70.0) ? 10 : 0;
                if ($pct > 20.0) {
                    $warnings[] = "Exceeds the policy maximum (Para 67). Proposed increment is {$pct}%, but maximum allowable is 20%.";
                } elseif ($pct > 10.0) {
                    $warnings[] = "Proposed increment is {$pct}% (> 10%), which requires exceptional-performance citation (Para 67).";
                } elseif ($pct > $maxAllowed) {
                    $warnings[] = "Proposed increment is {$pct}%, but appraisal score ({$mPercentage}%) allows a maximum of {$maxAllowed}%.";
                }
            }
        }

        // 5. Live layer
        $live = [
            'division_name'               => $divisionName,
            'project_name'                => $projectTitle,
            'project_code'                => $projectCode,
            'project_start_date'          => $projectStart,
            'estimated_end_date'          => $projectEnd,
            'total_project_budget'        => $totalProjectBudget,
            'project_budget_available'    => $budgetAvailable,
            'hr_allocated_budget'         => $hrAlloc,
            'hr_budget_utilized_to_date'  => $hrUtilized,
            'hr_budget_remaining'         => $hrRemaining,
            'hr_budget_committed_existing'=> $hrCommitted,
            'case_cost'                   => $casePrice,
            'hr_budget_committed_total'   => $hrCommittedTotal,
            'hr_budget_available'         => $hrAvailable,
            'hr_balance_sufficient'       => !$isHrDeficit,
            'suggested_shortfall'         => $suggestedShortfall,
            'equipment_allocated'         => $equipAlloc,
            'equipment_utilized'          => $equipUtilized,
            'equipment_remaining'         => $equipRemaining,
            'equipment_committed'         => $equipCommitted,
            'equipment_available'         => $equipAvailable,
            'employee'                    => [
                'name'                  => $empName,
                'grade'                 => $grade,
                'contract_expiry'       => $expiryDate,
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'current_salary'        => $currentSalary > 0 ? $currentSalary : null,
                'proposed_salary'       => $isRenewal ? $proposedSalary : null,
                'max_allowed_increment' => $maxAllowed,
            ],
            'approval_chain'              => $this->routingService->getChain('RDW/HR/F-09', $grade),
            'final_approver'              => $this->routingService->getFinalApprover('RDW/HR/F-09', $grade),
        ];

        // 6. Manual layer (shift_amount must default to NULL, never prefill)
        $manual = [
            'shift_amount'                => isset($existingManualData['shift_amount']) && $existingManualData['shift_amount'] !== null ? (float) $existingManualData['shift_amount'] : null,
            'shift_justification'         => $existingManualData['shift_justification'] ?? null,
            'director_remarks'            => $existingManualData['director_remarks'] ?? null,
        ];

        // 7. Missing mandatory fields
        $missingFields = [];
        if ($isHrDeficit) {
            if ($manual['shift_amount'] === null) {
                $missingFields[] = 'shift_amount';
            }
            if (empty($manual['shift_justification'])) {
                $missingFields[] = 'shift_justification';
            }
        }

        return [
            'live'           => $live,
            'manual'         => $manual,
            'warnings'       => $warnings,
            'missing_fields' => $missingFields,
            'is_complete'    => empty($missingFields),
        ];
    }
}
