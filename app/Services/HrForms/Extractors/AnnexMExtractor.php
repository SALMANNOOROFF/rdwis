<?php

namespace App\Services\HrForms\Extractors;

use App\Models\HrCtrCase;
use App\Services\HrForms\ApprovalRoutingService;
use App\Services\HrForms\SalaryBandService;
use Carbon\Carbon;

/**
 * Annex M: Performance Appraisal Form (RDW/HR/F-08)
 *
 * Extracts employee history, 6 appraisal criteria (10 marks each, 60 total),
 * prior contract review period, and permissible increment limits per Para 67.
 */
class AnnexMExtractor implements FormExtractorInterface
{
    protected SalaryBandService $salaryBandService;
    protected ApprovalRoutingService $routingService;

    public function __construct(
        SalaryBandService $salaryBandService,
        ApprovalRoutingService $routingService
    ) {
        $this->salaryBandService = $salaryBandService;
        $this->routingService = $routingService;
    }

    public function extract(HrCtrCase $case, string $instanceKey = 'main', array $existingManualData = []): array
    {
        $divisionName = $case->division_name ?? 'Headquarters / Directorate';
        $projectTitle = $case->project_name ?? 'R&D Project';

        // 1. Employee & Prior Contract Info
        $emp = $case->employee;
        $empName = $case->ctc_empnamecomp ?: ($emp->emp_name ?? null);
        $empNo = $emp->emp_no ?? ($emp->emp_id ?? null);
        $grade = $case->ctc_newgrade;
        $jobTitle = $case->ctc_newjobtitle;

        // Review period MUST be the PRIOR contract (ctr_startdt to ctr_enddt)
        $prevContract = $case->effective_previous_contract;
        $currentSalary = (float) ($prevContract?->ctr_salary ?? $case->previous_salary ?? 0);
        $proposedSalary = (float) $case->ctc_newsalary;

        $startFmt = $prevContract?->ctr_startdt ? Carbon::parse($prevContract->ctr_startdt)->format('d M Y') : null;
        $endFmt = $prevContract?->ctr_enddt ? Carbon::parse($prevContract->ctr_enddt)->format('d M Y') : null;
        $periodStr = ($startFmt && $endFmt) ? "{$startFmt} to {$endFmt}" : null;

        // 2. Six appraisal criteria, 10 marks each, total 60
        $criteriaKeys = [
            'technical_expertise_skills',
            'timely_completion_quality_of_work',
            'reliability_dependability',
            'response_under_pressure',
            'team_work_collaboration',
            'code_of_conduct', // Integrity, Discipline, Attendance, Attire, Punctuality
        ];

        $marks = [];
        $totalMarks = 0;
        $allMarked = true;

        foreach ($criteriaKeys as $k) {
            $val = $existingManualData['marks'][$k] ?? null;
            if ($val !== null && is_numeric($val)) {
                $m = max(0, min(10, (int) $val));
                $marks[$k] = $m;
                $totalMarks += $m;
            } else {
                $marks[$k] = null;
                $allMarked = false;
            }
        }

        // 3. Scoring Key & Increment rules (Para 67)
        // below 30 Needs Improvement, 31-40 Average, 41-50 Above Average, 51-55 Very Good, 56-60 Outstanding
        $percentage = $allMarked ? round(($totalMarks / 60) * 100, 1) : null;
        $rating = null;
        $maxAllowedIncrement = 0;

        if ($allMarked) {
            if ($totalMarks >= 56) {
                $rating = 'Outstanding';
            } elseif ($totalMarks >= 51) {
                $rating = 'Very Good';
            } elseif ($totalMarks >= 41) {
                $rating = 'Above Average';
            } elseif ($totalMarks >= 31) {
                $rating = 'Average';
            } else {
                $rating = 'Needs Improvement';
            }

            // max_allowed_increment_pct: 10 if percentage >= 70, else 0. Up to 20 only with exceptional-performance citation (Para 67).
            $hasCitation = !empty(trim((string)($existingManualData['exceptional_performance_citation'] ?? '')));
            if ($percentage >= 70.0) {
                $maxAllowedIncrement = $hasCitation ? 20.0 : 10.0;
            } else {
                $maxAllowedIncrement = 0.0;
            }
        }

        $warnings = [];
        $isRenewal = \App\Models\HrForms\HiringTypeMap::resolveHiringType($case->ctc_type) === 'Renewal';

        if ($allMarked && $isRenewal && $currentSalary > 0) {
            $diff = $proposedSalary - $currentSalary;
            $pct = round(($diff / $currentSalary) * 100, 1);
            if ($pct > 20.0) {
                $warnings[] = "Exceeds the policy maximum (Para 67). Proposed increment is {$pct}%, but maximum allowable is 20%.";
            } elseif ($pct > 10.0) {
                $warnings[] = "Proposed increment is {$pct}% (> 10%), which requires exceptional-performance citation (Para 67).";
            } elseif ($pct > $maxAllowedIncrement && $maxAllowedIncrement === 0) {
                $warnings[] = "Proposed increment is {$pct}%, but appraisal score ({$totalMarks}/60 - {$percentage}%) does not meet the 70% threshold required for an increment.";
            }
        }

        $criteriaDefs = [
            ['key' => 'technical_expertise_skills', 'label' => 'Technical Expertise & Skills', 'max_score' => 10],
            ['key' => 'timely_completion_quality_of_work', 'label' => 'Timely Completion & Quality of Work', 'max_score' => 10],
            ['key' => 'reliability_dependability', 'label' => 'Reliability & Dependability', 'max_score' => 10],
            ['key' => 'response_under_pressure', 'label' => 'Response under Pressure', 'max_score' => 10],
            ['key' => 'team_work_collaboration', 'label' => 'Team Work & Collaboration', 'max_score' => 10],
            ['key' => 'code_of_conduct', 'label' => 'Code of Conduct (Integrity, Discipline, Attendance, Attire, Punctuality)', 'max_score' => 10],
        ];

        // 4. Live layer
        $live = [
            'evaluation_criteria' => $criteriaDefs,
            'maximum_score'       => 60,
            'employee_name'       => $empName,
            'employee_id'         => $empNo,
            'designation'         => $jobTitle,
            'grade'               => $grade,
            'division_name'       => $divisionName,
            'project_name'        => $projectTitle,
            'current_salary'      => $currentSalary > 0 ? $currentSalary : null,
            'proposed_salary'     => $proposedSalary > 0 ? $proposedSalary : null,
            'review_period'       => $periodStr,
            'prior_contract_start'=> $startFmt,
            'prior_contract_end'  => $endFmt,
            'approval_chain'      => $this->routingService->getChain('RDW/HR/F-08', $grade),
            'final_approver'      => $this->routingService->getFinalApprover('RDW/HR/F-08', $grade),
        ];

        // 5. Manual layer
        $manual = [
            'marks'                             => $marks,
            'total_marks'                       => $allMarked ? $totalMarks : null,
            'percentage'                        => $percentage,
            'performance_rating'                => $rating ?? ($existingManualData['performance_rating'] ?? null),
            'max_allowed_increment_pct'         => $maxAllowedIncrement,
            'recommended_increment_pct'         => $existingManualData['recommended_increment_pct'] ?? null,
            'exceptional_performance_citation'  => $existingManualData['exceptional_performance_citation'] ?? null,
            'remarks_by_concerned_dir'          => $existingManualData['remarks_by_concerned_dir'] ?? null,
        ];

        // 6. Missing mandatory fields
        $missingFields = [];
        if (!$allMarked) {
            $missingFields[] = 'marks';
        }
        if (empty($manual['remarks_by_concerned_dir'])) {
            $missingFields[] = 'remarks_by_concerned_dir';
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
