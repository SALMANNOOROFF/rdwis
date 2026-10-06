<?php

namespace App\Services\HrForms;

use App\Models\HrForms\ApprovalChain;

class ApprovalRoutingService
{
    protected ?SalaryBandService $salaryBandService = null;

    protected function getSalaryBandService(): SalaryBandService
    {
        if ($this->salaryBandService === null) {
            $this->salaryBandService = app(SalaryBandService::class);
        }
        return $this->salaryBandService;
    }

    /**
     * Determine whether grade is RO and Above (DG NRDI) or RT and Below (MD RDW)
     * Reads configured approval_level from hrforms.salary_bands.
     */
    public function getGradeTier(?string $grade): string
    {
        if (empty($grade)) {
            return 'RO_AND_ABOVE';
        }

        // 1. Read approval_level from hrforms.salary_bands configuration
        $band = $this->getSalaryBandService()->resolveBand($grade);
        if ($band && !empty($band->approval_level)) {
            return ($band->approval_level === 'DG_NRDI') ? 'RO_AND_ABOVE' : 'RT_AND_BELOW';
        }

        // 2. Fallback heuristic for unconfigured grades from config (Para 6)
        $g = strtoupper(trim($grade));
        $rtAndBelow = config('hrforms.rt_and_below_grades', [
            'SRT', 'RT', 'JRT',
            'LA', 'LAB ATTENDANT',
            'RA', 'RESEARCH AIDE',
            'EA', 'ENGINEERING AIDE',
            'SS', 'SUPPORT STAFF',
            'LABOR', 'WORKER', 'GARDENER', 'NAIB QASID', 'DIVER', 'MAALI',
            'JA', 'JUNIOR ASSISTANT',
            'INTERN', 'INTERNEE'
        ]);

        foreach ($rtAndBelow as $pattern) {
            if ($g === $pattern || str_starts_with($g, $pattern . '-') || str_starts_with($g, $pattern . ' ')) {
                return 'RT_AND_BELOW';
            }
        }

        return 'RO_AND_ABOVE';
    }

    /**
     * Get the final approving authority role for a form and grade.
     */
    public function getFinalApprover(string $formCode, ?string $grade): string
    {
        $chain = $this->getChain($formCode, $grade);
        if (!empty($chain)) {
            $lastStep = end($chain);
            if (!empty($lastStep['approver_role'])) {
                return $lastStep['approver_role'];
            }
        }

        if ($formCode === 'RDW/HR/F-01') {
            return 'DG NRDI';
        }

        $tier = $this->getGradeTier($grade);
        return ($tier === 'RO_AND_ABOVE') ? 'DG NRDI' : 'MD RDW';
    }

    /**
     * Get the approval chain steps for a form and grade.
     */
    public function getChain(string $formCode, ?string $grade): array
    {
        if ($formCode === 'RDW/HR/F-01') {
            return ApprovalChain::where('form_code', 'RDW/HR/F-01')
                ->where('grade_level', 'ALL')
                ->orderBy('sequence')
                ->get()
                ->toArray();
        }

        $tier = $this->getGradeTier($grade);

        return ApprovalChain::where('form_code', $formCode)
            ->where('grade_level', $tier)
            ->orderBy('sequence')
            ->get()
            ->toArray();
    }
}
