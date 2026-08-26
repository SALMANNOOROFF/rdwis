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
}
