<?php

namespace App\Services\HrForms\Extractors;

use App\Models\HrCtrCase;
use App\Models\HrForms\ProjectExtra;
use App\Services\FinancialIntelligenceService;
use App\Services\HrForms\ApprovalRoutingService;
use App\Services\HrForms\SalaryBandService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Annex A: In-Principle Approval for Hiring of HR (RDW/HR/F-01)
 *
 * Extracts live financial allocations, project metadata, and Annex K salary bands.
 */
class AnnexAExtractor implements FormExtractorInterface
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
        // 1. Division & Unit info
        $division = $case->division ?? $case->unit;
        $divisionName = $case->division_name ?? 'Headquarters / Directorate';
        $directorName = $division->unt_leadname ?? ($division->unt_leadtitle ?? 'Project Director');

        // 2. Project & Head info
        $headId = $case->ctc_prj_id ?? ($case->casePlans->first()?->ccp_hed_id ?? 0);
        $head = $headId ? DB::table('cen.heads')->where('hed_id', $headId)->first() : null;
        $prjId = $head->hed_prj_id ?? 0;
        $project = $prjId ? DB::table('prj.projects')->where('prj_id', $prjId)->first() : null;

        $projectTitle = $project->prj_title ?? ($head->hed_name ?? $case->project_name ?? null);
        $projectCode = $project->prj_code ?? ($head->hed_code ?? $case->project_code ?? null);
        $projectStart = $project->prj_startdt ?? $case->ctc_newstartdt;
        $projectEnd = $project->prj_estenddt ?? ($project->prj_enddt ?? $case->ctc_newenddt);
        
        $approvedBudget = ($project && $project->prj_aprvcost !== null) ? (float) $project->prj_aprvcost : null;
        $budgetAvailable = ($approvedBudget !== null);

        // 3. Subhead allocations from FinancialIntelligenceService / fin.subheads (indexed array parsing)
        $hrAlloc = null;
        $equipAlloc = null;
        $miscAlloc = null;
        $allocationsAvailable = false;

        if ($headId) {
            $breakdown = $this->finService->getSubheadBreakdown($headId);
            if (is_array($breakdown) && !empty($breakdown)) {
                $allocationsAvailable = true;
                foreach ($breakdown as $item) {
                    $itemArr = (array) $item;
                    $name = $itemArr['name'] ?? null;
                    if ($name === 'HR') $hrAlloc = (float) ($itemArr['allocation'] ?? 0);
                    elseif ($name === 'Equipment') $equipAlloc = (float) ($itemArr['allocation'] ?? 0);
                    elseif ($name === 'Misc') $miscAlloc = (float) ($itemArr['allocation'] ?? 0);
                }
            }
        }

        // 4. Annex K Salary Band lookup & evaluation
        $grade = $case->ctc_newgrade;
        $salary = (float) $case->ctc_newsalary;
        $bandEval = $this->salaryBandService->evaluateSalary($grade, $salary);
        $salaryRangeStr = $bandEval['has_band']
            ? 'Rs. ' . number_format($bandEval['min']) . ' - ' . number_format($bandEval['max'])
            : ($salary > 0 ? 'Rs. ' . number_format($salary) : null);

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

        // 5. Read project_extras (if saved earlier)
        $projectExtra = $prjId ? ProjectExtra::where('project_id', $prjId)->first() : null;

        // 6. Forecasted salaries & tenure calculation (FIX 9)
        $months = max(1, $case->casePlans->count() ?: 12);
        $totalDays = null;
        if ($case->ctc_newstartdt && $case->ctc_newenddt) {
            $totalDays = Carbon::parse($case->ctc_newstartdt)->diffInDays(Carbon::parse($case->ctc_newenddt)) + 1;
        }

        $isDailyProrated = !empty($case->ctc_price) && $case->casePlans->count() > 0;
        $totalForecast = (float) ($case->ctc_price ?: ($salary * $months));
        $forecastBasis = $isDailyProrated ? 'case_plans_daily_prorated' : 'nominal_monthly';

        // 7. Live layer
        $live = [
            'division_name'          => $divisionName,
            'director_name'          => $directorName,
            'project_name'           => $projectTitle,
            'project_code'           => $projectCode,
            'project_start_date'     => $projectStart,
            'project_end_date'       => $projectEnd,
            'approved_budget'        => $approvedBudget,
            'project_budget_available'=> $budgetAvailable,
            'hr_allocated'           => $hrAlloc,
            'hardware_equipment'     => $equipAlloc,
            'consultancy_misc'       => $miscAlloc,
            'allocations_available'  => $allocationsAvailable,
            'proposed_grade'         => $grade,
            'proposed_qty'           => 1,
            'proposed_salary'        => $salary > 0 ? $salary : null,
            'salary_band_range'      => $salaryRangeStr,
            'forecasted_per_month'   => $salary > 0 ? $salary : null,
            'total_forecast'         => $totalForecast > 0 ? $totalForecast : null,
            'forecast_basis'         => $forecastBasis,
            'tenure_months'          => $months,
            'tenure_days'            => $totalDays,
            'approval_chain'         => $this->routingService->getChain('RDW/HR/F-01', $grade),
            'final_approver'         => 'DG NRDI',
        ];

        // 8. Manual layer (cost fields default to null per A2)
        $manual = [
            'work_order_no'              => $existingManualData['work_order_no'] ?? ($projectExtra->work_order_no ?? null),
            'work_order_date'            => $existingManualData['work_order_date'] ?? ($projectExtra?->work_order_date?->format('Y-m-d') ?? null),
            'warranty_expiry_date'       => $existingManualData['warranty_expiry_date'] ?? ($projectExtra?->warranty_expiry?->format('Y-m-d') ?? null),
            'service_charges_taxes'      => $existingManualData['service_charges_taxes'] ?? null,
            'infrastructure_development' => $existingManualData['infrastructure_development'] ?? null,
            'overheads'                  => $existingManualData['overheads'] ?? null,
            'other_cost_heads'           => $existingManualData['other_cost_heads'] ?? null,
            'qualification_required'     => $existingManualData['qualification_required'] ?? ($case->ctc_jd ?? null),
            'experience_required'        => $existingManualData['experience_required'] ?? null,
        ];

        // 9. Missing mandatory fields for Annex A
        $missingFields = [];
        if (empty($manual['work_order_no'])) $missingFields[] = 'work_order_no';
        if (empty($manual['work_order_date'])) $missingFields[] = 'work_order_date';
        if (empty($manual['warranty_expiry_date'])) $missingFields[] = 'warranty_expiry_date';

        return [
            'live'           => $live,
            'manual'         => $manual,
            'warnings'       => $warnings,
            'missing_fields' => $missingFields,
            'is_complete'    => empty($missingFields),
        ];
    }
}
