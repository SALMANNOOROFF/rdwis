<?php

namespace App\Services;

use App\Models\HrCtrCase;
use App\Models\HrCtrCaseSubstatus;
use App\Models\HrCtrCaseRemark;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ContractCaseApprovalService
{
    /**
     * Map holder stage name → legacy ctc_status value
     */
    const STAGE_TO_LEGACY_STATUS = [
        'Division'     => 'Draft',
        'HR'           => 'Under HR Scrutiny',
        'Finance'      => 'Under Finance Scrutiny',
        'MD'           => 'Under Approval',
        'DDG'          => 'Under Approval',
        'DG'           => 'Under Approval',
        'Approved'     => 'Approved',
        'Fulfilled'    => 'Fulfilled',
        'Not Approved' => 'Not Approved',
        'Cancelled'    => 'Cancelled',
    ];

    /**
     * Transition substatus: close current active row, open a new active row,
     * and dual-write the corresponding legacy ctc_status into hr.ctrcases.
     *
     * @param HrCtrCase $case
     * @param string $newStage
     * @param string|null $legacyStatus
     * @return void
     */
    public function transitionSubstatus(HrCtrCase $case, string $newStage, ?string $legacyStatus = null): void
    {
        $status = $legacyStatus ?? (self::STAGE_TO_LEGACY_STATUS[$newStage] ?? 'Draft');

        // Close current active substatus
        HrCtrCaseSubstatus::where('css_ctc_id', $case->ctc_id)
            ->where('css_is_current', true)
            ->update([
                'css_is_current' => false,
                'css_until'      => now(),
            ]);

        // Open new active substatus
        HrCtrCaseSubstatus::create([
            'css_ctc_id'     => $case->ctc_id,
            'css_stage'      => $newStage,
            'css_is_current' => true,
            'css_since'      => now(),
            'css_until'      => null,
        ]);

        // Dual-write legacy ctc_status
        DB::table('hr.ctrcases')
            ->where('ctc_id', $case->ctc_id)
            ->update(['ctc_status' => $status]);

        $case->ctc_status = $status;
    }

    /**
     * Close substatus for terminal states (Fulfilled, Not Approved, Cancelled)
     *
     * @param HrCtrCase $case
     * @param string $terminalStage
     * @param string $legacyStatus
     * @return void
     */
    public function closeSubstatus(HrCtrCase $case, string $terminalStage, string $legacyStatus): void
    {
        // Close previous active substatus
        HrCtrCaseSubstatus::where('css_ctc_id', $case->ctc_id)
            ->where('css_is_current', true)
            ->update([
                'css_is_current' => false,
                'css_until'      => now(),
            ]);

        // Insert terminal marker row
        HrCtrCaseSubstatus::create([
            'css_ctc_id'     => $case->ctc_id,
            'css_stage'      => $terminalStage,
            'css_is_current' => true,
            'css_since'      => now(),
            'css_until'      => now(),
        ]);

        DB::table('hr.ctrcases')
            ->where('ctc_id', $case->ctc_id)
            ->update([
                'ctc_status'   => $legacyStatus,
                'ctc_closedtg' => now(),
            ]);

        $case->ctc_status = $legacyStatus;
        $case->ctc_closedtg = now();
    }

    /**
     * Add a record into hr.ctrcaseremarks
     */
    public function recordRemark(HrCtrCase $case, $user, string $remarks, string $status): void
    {
        if (empty(trim($remarks))) {
            return;
        }

        HrCtrCaseRemark::create([
            'crr_ctc_id'    => $case->ctc_id,
            'crr_username'  => $user->acc_name ?? $user->acc_user ?? 'System',
            'crr_user_rank' => $user->acc_rank ?? '',
            'crr_user_desig'=> $user->acc_desig ?? '',
            'crr_remarks'   => $remarks,
            'crr_status'    => $status,
            'crr_dtg'       => now(),
        ]);
    }

    /**
     * Release case from Division to HR Scrutiny
     */
    public function release(HrCtrCase $case, $user, ?string $remarks = null): void
    {
        DB::transaction(function () use ($case, $user, $remarks) {
            $this->transitionSubstatus($case, 'HR', 'Under HR Scrutiny');

            DB::table('hr.ctrcases')
                ->where('ctc_id', $case->ctc_id)
                ->update([
                    'ctc_releasedby' => $user->acc_id ?? null,
                    'ctc_releasedtg' => now(),
                ]);

            if (!empty($remarks)) {
                $this->recordRemark($case, $user, $remarks, 'Under HR Scrutiny');
            }
        });
    }

    /**
     * Forward case to next authority in the pipeline
     */
    public function forward(HrCtrCase $case, $user, ?string $remarks = null, ?string $targetStage = null): string
    {
        return DB::transaction(function () use ($case, $user, $remarks, $targetStage) {
            $currentStage = $case->currentSubstatus->css_stage ?? 'Division';
            $nextStage = $targetStage;

            if (!$nextStage) {
                switch ($currentStage) {
                    case 'Division':
                        $nextStage = 'HR';
                        break;
                    case 'HR':
                        $nextStage = 'Finance';
                        break;
                    case 'Finance':
                        $nextStage = 'MD';
                        break;
                    case 'MD':
                        $nextStage = 'DDG';
                        break;
                    case 'DDG':
                        $nextStage = 'DG';
                        break;
                    default:
                        $nextStage = 'HR';
                        break;
                }
            }

            $legacyStatus = self::STAGE_TO_LEGACY_STATUS[$nextStage] ?? 'Under Approval';
            $this->transitionSubstatus($case, $nextStage, $legacyStatus);

            if (!empty($remarks)) {
                $this->recordRemark($case, $user, $remarks, $legacyStatus);
            }

            return $nextStage;
        });
    }

    /**
     * Approve case (by MD / DDG / DG)
     */
    public function approve(HrCtrCase $case, $user, array $data = [], ?string $remarks = null): void
    {
        DB::transaction(function () use ($case, $user, $data, $remarks) {
            // Update approved terms if submitted
            $updates = [
                'ctc_approvedstartdt' => $data['ctc_approvedstartdt'] ?? $case->ctc_newstartdt,
                'ctc_approvedenddt'   => $data['ctc_approvedenddt'] ?? $case->ctc_newenddt,
                'ctc_approvedsalary'  => $data['ctc_approvedsalary'] ?? $case->ctc_newsalary,
                'ctc_approvedgrade'   => $data['ctc_approvedgrade'] ?? $case->ctc_newgrade,
                'ctc_approvedjobtitle'=> $data['ctc_approvedjobtitle'] ?? $case->ctc_newjobtitle,
                'ctc_approvedprob'    => $data['ctc_approvedprob'] ?? $case->ctc_newprob,
                'ctc_approvedprobsal' => $data['ctc_approvedprobsal'] ?? $case->ctc_newprobsal,
                'ctc_approvedctrtype' => $data['ctc_approvedctrtype'] ?? $case->ctc_newctrtype,
                'ctc_approvedunt_id'  => $data['ctc_approvedunt_id'] ?? $case->ctc_newunt_id ?? $case->ctc_unt_id,
            ];

            DB::table('hr.ctrcases')->where('ctc_id', $case->ctc_id)->update($updates);

            // Transition substatus to Approved (Ready for Fulfillment)
            $this->transitionSubstatus($case, 'Approved', 'Approved');

            // Create attachment slot for Minute
            $exists = DB::table('hr.ctrcaseattachments')
                ->where('cat_objtype', 'ctc')
                ->where('cat_objid', $case->ctc_id)
                ->where('cat_type', 'Minute')
                ->exists();

            if (!$exists) {
                DB::table('hr.ctrcaseattachments')->insert([
                    'cat_objtype' => 'ctc',
                    'cat_objid'   => $case->ctc_id,
                    'cat_type'    => 'Minute',
                    'cat_path'    => null,
                ]);
            }

            if (!empty($remarks)) {
                $this->recordRemark($case, $user, $remarks, 'Approved');
            }
        });
    }

    /**
     * Return case to previous stage or Division (Under Revision)
     */
    public function return(HrCtrCase $case, $user, string $remarks, ?string $targetStage = null): string
    {
        return DB::transaction(function () use ($case, $user, $remarks, $targetStage) {
            $currentStage = $case->currentSubstatus->css_stage ?? 'HR';
            $destStage = $targetStage;

            if (!$destStage) {
                switch ($currentStage) {
                    case 'HR':
                    case 'Finance':
                        $destStage = 'Division';
                        break;
                    case 'MD':
                        $destStage = 'Finance';
                        break;
                    case 'DDG':
                        $destStage = 'MD';
                        break;
                    case 'DG':
                        $destStage = 'DDG';
                        break;
                    default:
                        $destStage = 'Division';
                        break;
                }
            }

            $legacyStatus = ($destStage === 'Division') ? 'Under Revision' : (self::STAGE_TO_LEGACY_STATUS[$destStage] ?? 'Under Revision');
            $this->transitionSubstatus($case, $destStage, $legacyStatus);
            $this->recordRemark($case, $user, $remarks, $legacyStatus);

            return $destStage;
        });
    }

    /**
     * Reject case (Not Approved)
     */
    public function reject(HrCtrCase $case, $user, string $remarks): void
    {
        DB::transaction(function () use ($case, $user, $remarks) {
            $this->closeSubstatus($case, 'Not Approved', 'Not Approved');
            $this->recordRemark($case, $user, $remarks, 'Not Approved');
        });
    }

    /**
     * Cancel case
     */
    public function cancel(HrCtrCase $case, $user, string $remarks = 'Case cancelled'): void
    {
        DB::transaction(function () use ($case, $user, $remarks) {
            $this->closeSubstatus($case, 'Cancelled', 'Cancelled');
            $this->recordRemark($case, $user, $remarks, 'Cancelled');
        });
    }

    // ── FINANCIAL & GRADE POWERS / THRESHOLD LOGIC (GOD MODE) ────────

    /**
     * Parse SPS/BPS grade string to rank integer (1-10) for scale comparisons
     */
    public static function parseGradeLevel(?string $grade): int
    {
        if (empty($grade)) return 1;
        $grade = strtoupper(trim((string)$grade));

        // SPS Scale (SPS-01 to SPS-10)
        if (preg_match('/SPS[- ]?0?(\d+)/i', $grade, $m)) {
            return min(10, max(1, (int) $m[1]));
        }

        // BPS Scale (BPS-01 to BPS-22 mapped to 1-10 equivalents)
        if (preg_match('/BPS[- ]?0?(\d+)/i', $grade, $m)) {
            $bps = (int) $m[1];
            if ($bps <= 4) return 1;
            if ($bps <= 7) return 2;
            if ($bps <= 10) return 3;
            if ($bps <= 13) return 4;
            if ($bps <= 15) return 5;
            if ($bps == 16) return 6;
            if ($bps == 17) return 7;
            if ($bps == 18) return 8;
            if ($bps == 19) return 9;
            return 10;
        }

        // Plain numbers
        if (preg_match('/^(\d+)$/', $grade, $m)) {
            return min(10, max(1, (int) $m[1]));
        }

        // Senior titles
        if (preg_match('/(DIRECTOR|CHIEF|PRINCIPAL|CONSULTANT|MP-)/i', $grade)) {
            return 10;
        }

        return 5;
    }

    public function getMdSalaryThreshold(): float
    {
        return (float) SystemSetting::get('hr_md_salary_limit', '150000');
    }

    public function getMdGradeThreshold(): string
    {
        return (string) SystemSetting::get('hr_md_grade', 'SPS-7');
    }

    public function getDdgSalaryThreshold(): float
    {
        return (float) SystemSetting::get('hr_ddg_salary_limit', '300000');
    }

    public function getDdgGradeThreshold(): string
    {
        return (string) SystemSetting::get('hr_ddg_grade', 'SPS-8');
    }

    /**
     * Determine the terminal approval authority required for this case based on God Mode limits.
     *
     * @param HrCtrCase $case
     * @return string 'MD' | 'DDG' | 'DG'
     */
    public function getRequiredAuthority(HrCtrCase $case): string
    {
        $salary = (float) ($case->ctc_approvedsalary ?: ($case->ctc_newsalary ?? 0));
        $grade = (string) ($case->ctc_approvedgrade ?: ($case->ctc_newgrade ?? ''));
        $caseGradeLevel = self::parseGradeLevel($grade);

        $mdSalaryLimit = $this->getMdSalaryThreshold();
        $mdGradeLimit = self::parseGradeLevel($this->getMdGradeThreshold());

        $ddgSalaryLimit = $this->getDdgSalaryThreshold();
        $ddgGradeLimit = self::parseGradeLevel($this->getDdgGradeThreshold());

        // Check if within MD's delegated authority
        if ($salary <= $mdSalaryLimit && $caseGradeLevel <= $mdGradeLimit) {
            return 'MD';
        }

        // Check if within DDG's delegated authority
        if ($salary <= $ddgSalaryLimit && $caseGradeLevel <= $ddgGradeLimit) {
            return 'DDG';
        }

        // Exceeds DDG limits -> Requires DG Terminal Approval
        return 'DG';
    }

    /**
     * Check if a specific role is authorized to approve this case terminally
     */
    public function canApprove(string $role, HrCtrCase $case): bool
    {
        $role = strtoupper(trim($role));
        if (in_array($role, ['DG', 'NRDI'])) {
            return true;
        }

        $required = $this->getRequiredAuthority($case);

        if (in_array($role, ['DDG', 'HQS'])) {
            return in_array($required, ['MD', 'DDG']);
        }

        if (in_array($role, ['MD', 'RDW'])) {
            return $required === 'MD';
        }

        return false;
    }

    /**
     * Detailed breakdown of limits and authority for display in show views
     */
    public function getApprovalAuthorityDetails(HrCtrCase $case): array
    {
        $salary = (float) ($case->ctc_approvedsalary ?: ($case->ctc_newsalary ?? 0));
        $grade = (string) ($case->ctc_approvedgrade ?: ($case->ctc_newgrade ?? 'SPS-1'));
        $caseGradeLevel = self::parseGradeLevel($grade);

        $mdSalaryLimit = $this->getMdSalaryThreshold();
        $mdGradeStr = $this->getMdGradeThreshold();
        $mdGradeLimit = self::parseGradeLevel($mdGradeStr);

        $ddgSalaryLimit = $this->getDdgSalaryThreshold();
        $ddgGradeStr = $this->getDdgGradeThreshold();
        $ddgGradeLimit = self::parseGradeLevel($ddgGradeStr);

        $required = $this->getRequiredAuthority($case);

        return [
            'salary'           => $salary,
            'grade'            => $grade,
            'case_grade_lvl'   => $caseGradeLevel,
            'md_salary_limit'  => $mdSalaryLimit,
            'md_grade_limit'   => $mdGradeStr,
            'md_grade_lvl'     => $mdGradeLimit,
            'ddg_salary_limit' => $ddgSalaryLimit,
            'ddg_grade_limit'  => $ddgGradeStr,
            'ddg_grade_lvl'    => $ddgGradeLimit,
            'required_stage'   => $required,
            'can_md_approve'   => ($salary <= $mdSalaryLimit && $caseGradeLevel <= $mdGradeLimit),
            'can_ddg_approve'  => ($salary <= $ddgSalaryLimit && $caseGradeLevel <= $ddgGradeLimit),
            'can_dg_approve'   => true,
        ];
    }

    /**
     * Get dynamic pipeline stepper steps based on required authority (MD, DDG, or DG)
     */
    public function getWorkflowSteps(HrCtrCase $case): array
    {
        $req = $this->getRequiredAuthority($case);

        $steps = [
            ['id' => 'Division', 'label' => 'Initiated', 'icon' => 'fa-edit'],
            ['id' => 'HR', 'label' => 'HR Scrutiny', 'icon' => 'fa-user-check'],
            ['id' => 'Finance', 'label' => 'Finance', 'icon' => 'fa-coins'],
        ];

        if ($req === 'MD') {
            $steps[] = ['id' => 'MD', 'label' => 'MD Approval', 'icon' => 'fa-file-signature'];
        } elseif ($req === 'DDG') {
            $steps[] = ['id' => 'MD', 'label' => 'MD Review', 'icon' => 'fa-file-signature'];
            $steps[] = ['id' => 'DDG', 'label' => 'DDG Approval', 'icon' => 'fa-stamp'];
        } else {
            $steps[] = ['id' => 'MD', 'label' => 'MD Review', 'icon' => 'fa-file-signature'];
            $steps[] = ['id' => 'DDG', 'label' => 'DDG Review', 'icon' => 'fa-stamp'];
            $steps[] = ['id' => 'DG', 'label' => 'DG Approval', 'icon' => 'fa-gavel'];
        }

        $steps[] = ['id' => 'Approved', 'label' => 'Ready Fulfill', 'icon' => 'fa-check-double'];
        $steps[] = ['id' => 'Fulfilled', 'label' => 'Fulfilled', 'icon' => 'fa-award'];

        return $steps;
    }
}

