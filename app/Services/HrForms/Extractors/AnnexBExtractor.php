<?php

namespace App\Services\HrForms\Extractors;

use App\Models\HrCtrCase;
use App\Models\HrForms\CaseExtra;
use App\Models\HrForms\ProjectExtra;
use App\Services\FinancialIntelligenceService;
use App\Services\HrForms\ApprovalRoutingService;
use App\Services\HrForms\SalaryBandService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Annex B: Hiring Board for Selection of Contract Employee (RDW/HR/F-02)
 *
 * Extracts board composition, financial head commitments, and candidate screening details.
 */
class AnnexBExtractor implements FormExtractorInterface
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
        // 1. Division & Project
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

        // 2. Active employees already hired on this project head
        $alreadyHired = null;
        if ($headId) {
            $alreadyHired = DB::table('hr.contractplans')
                ->where('cpn_hed_id', $headId)
                ->where('cpn_enddt', '>=', now()->toDateString())
                ->distinct('cpn_ctr_id')
                ->count('cpn_ctr_id');
        }

        // 3. Financial Metrics from FinancialIntelligenceService (indexed array parsing)
        $hrAlloc = null;
        $hrUtilized = null;
        $hrRemaining = null;
        $hrCommitted = null;
        $hrAvailable = false;

        if ($headId) {
            $breakdown = $this->finService->getSubheadBreakdown($headId);
            if (is_array($breakdown)) {
                foreach ($breakdown as $item) {
                    $itemArr = (array) $item;
                    if (($itemArr['name'] ?? null) === 'HR') {
                        $hrAlloc = (float) ($itemArr['allocation'] ?? 0);
                        $hrUtilized = (float) ($itemArr['expenditure'] ?? 0);
                        $hrRemaining = (float) ($itemArr['remaining'] ?? 0);
                        $hrCommitted = (float) ($itemArr['commitments'] ?? 0);
                        $hrAvailable = true;
                        break;
                    }
                }
            }
        }

        $casePrice = (float) ($case->ctc_price ?: ($case->ctc_newsalary * $case->tenure_months));
        $hrCommittedTotal = $hrAvailable ? ($hrCommitted + $casePrice) : null;

        // 4. Case designation details
        $grade = $case->ctc_newgrade;
        $salary = (float) $case->ctc_newsalary;
        $jobTitle = $case->ctc_newjobtitle;
        $jobType = $case->ctc_emp_type ?: ($case->ctc_newctrtype == 2 ? 'Part Time' : 'Full Time');

        $startFmt = $case->ctc_newstartdt ? Carbon::parse($case->ctc_newstartdt)->format('d M Y') : null;
        $endFmt = $case->ctc_newenddt ? Carbon::parse($case->ctc_newenddt)->format('d M Y') : null;
        $months = $case->tenure_months;
        $durationStr = ($startFmt && $endFmt) ? "{$startFmt} to {$endFmt} ({$months} Months)" : null;

        // 5. Salary evaluation against Annex K
        $bandEval = $this->salaryBandService->evaluateSalary($grade, $salary);
        $warnings = [];
        if (!empty($bandEval['warning'])) {
            $warnings[] = $bandEval['warning'];
        }

        // Project dates mismatch warning (A4)
        if ($projectEnd && $case->ctc_newstartdt) {
            if (Carbon::parse($projectEnd)->lt(Carbon::parse($case->ctc_newstartdt))) {
                $warnings[] = "Project estimated end date (" . Carbon::parse($projectEnd)->format('d M Y') . ") is before the contract start date (" . Carbon::parse($case->ctc_newstartdt)->format('d M Y') . "). Contract extends beyond project life cycle.";
            }
        }

        // 6. Extras (project_extras and case_extras)
        $projectExtra = $prjId ? ProjectExtra::where('project_id', $prjId)->first() : null;
        $caseExtra = CaseExtra::where('case_id', $case->ctc_id)->first();

        // Principal candidate details: read from hr.applicants / applicqualifs / applicjobs when linked
        $principalCandidateName = $case->ctc_empnamecomp ?: null;
        $principalCnic = $case->candidate_cnic ?: null;
        $principalQual = null;
        $principalInst = null;
        $principalExp = null;

        if ($principalCnic) {
            $applicant = DB::table('hr.applicants')->where('apl_cnic', $principalCnic)->first();
            if ($applicant) {
                // Check highest qualification from hr.applicqualifs
                $qualif = DB::table('hr.applicqualifs')
                    ->where('apq_apl_id', $applicant->apl_id)
                    ->orderBy('apq_level', 'desc')
                    ->first();
                if ($qualif) {
                    $principalQual = $qualif->apq_name;
                    $principalInst = $qualif->apq_inst;
                } else {
                    $principalQual = ($applicant->apl_discip ? $applicant->apl_discip . ($applicant->apl_spec ? " ({$applicant->apl_spec})" : '') : null);
                }

                if ($applicant->apl_experience !== null) {
                    $principalExp = "{$applicant->apl_experience} Years";
                }
            }
        }

        // 7. Live layer
        $live = [
            'project_title'               => $projectTitle,
            'project_code'                => $projectCode,
            'division_name'               => $divisionName,
            'start_date'                  => $projectStart,
            'estimated_end_date'          => $projectEnd,
            'already_hired_count'         => $alreadyHired,
            'total_project_budget'        => $totalProjectBudget,
            'project_budget_available'    => $budgetAvailable,
            'hr_allocated_budget'         => $hrAlloc,
            'hr_budget_utilized_to_date'  => $hrUtilized,
            'hr_budget_remaining'         => $hrRemaining,
            'hr_budget_committed_total'   => $hrCommittedTotal,
            'hr_budget_available'         => $hrAvailable,
            'work_order_no'               => $projectExtra?->work_order_no ?? null,
            'work_order_date'             => $projectExtra?->work_order_date?->format('Y-m-d') ?? null,
            'warranty_expiry_date'        => $projectExtra?->warranty_expiry?->format('Y-m-d') ?? null,
            'case_financial_impact'       => $casePrice,
            'job_title'                   => $jobTitle,
            'job_type'                    => $jobType,
            'grade'                       => $grade,
            'gross_pay'                   => $salary > 0 ? $salary : null,
            'duration'                    => $durationStr,
            'tenure_months'               => $months,
            'principal_candidate'         => [
                'name'             => $principalCandidateName,
                'cnic'             => $principalCnic,
                'qualification'    => $principalQual,
                'qualification_source' => $principalQual ? 'hr.applicants' : 'manual',
                'institute'        => $principalInst,
                'institute_source' => $principalInst ? 'hr.applicqualifs' : 'manual',
                'field_experience' => $principalExp,
                'experience_source'=> $principalExp ? 'hr.applicants' : 'manual',
            ],
            'approval_chain'              => $this->routingService->getChain('RDW/HR/F-02', $grade),
            'final_approver'              => $this->routingService->getFinalApprover('RDW/HR/F-02', $grade),
        ];

        // 8. Manual layer (interview_time and interview_venue source tracking per FIX 4b)
        $interviewDateVal = $existingManualData['interview_date'] ?? ($caseExtra?->interview_date?->format('Y-m-d') ?? null);
        $interviewTimeVal = $existingManualData['interview_time'] ?? ($caseExtra->interview_time ?? null);
        $interviewVenueVal = $existingManualData['interview_venue'] ?? ($caseExtra->interview_venue ?? null);

        $manual = [
            'approved_hr_count'       => $existingManualData['approved_hr_count'] ?? ($projectExtra->approved_headcount ?? null),
            'interview_date'          => $interviewDateVal,
            'interview_time'          => $interviewTimeVal,
            'interview_time_source'   => ($caseExtra && $caseExtra->interview_time) ? 'case_extras.interview_time' : 'manual',
            'interview_venue'         => $interviewVenueVal,
            'interview_venue_source'  => ($caseExtra && $caseExtra->interview_venue) ? 'case_extras.interview_venue' : 'manual',
            'standby_candidate_name'  => $existingManualData['standby_candidate_name'] ?? ($caseExtra->standby_candidate_name ?? null),
            'shortlisted_candidates'  => $existingManualData['shortlisted_candidates'] ?? ($caseExtra->shortlisted_candidates ?? []),
            'board_recommendations'   => $existingManualData['board_recommendations'] ?? null,
        ];

        // 9. Missing mandatory checks
        $missingFields = [];
        if (empty($manual['approved_hr_count'])) $missingFields[] = 'approved_hr_count';
        if (empty($manual['interview_date'])) $missingFields[] = 'interview_date';

        return [
            'live'           => $live,
            'manual'         => $manual,
            'warnings'       => $warnings,
            'missing_fields' => $missingFields,
            'is_complete'    => empty($missingFields),
        ];
    }
}
