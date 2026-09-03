<?php

namespace App\Services;

use App\Models\HrCtrCase;
use App\Models\HrContract;
use App\Models\HrContractPlan;
use App\Models\HrCtrCasePlan;
use App\Models\HrEmployee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ContractCaseFulfillmentService
{
    protected ContractCaseApprovalService $approvalService;
    protected ContractCasePricingService $pricingService;

    public function __construct(
        ContractCaseApprovalService $approvalService,
        ContractCasePricingService $pricingService
    ) {
        $this->approvalService = $approvalService;
        $this->pricingService = $pricingService;
    }

    /**
     * Fulfill a contract case according to its type (Hg, Cr, Ce, Rh)
     *
     * @param HrCtrCase $case
     * @param mixed $user
     * @param array $data Contains ctc_newsigndt, ctc_terminremarks, etc.
     * @return HrCtrCase
     * @throws InvalidArgumentException
     */
    public function fulfill(HrCtrCase $case, $user, array $data = []): HrCtrCase
    {
        $type = strtoupper(trim((string)($case->ctc_type ?? 'HG')));
        $signDate = !empty($data['ctc_newsigndt']) ? Carbon::parse($data['ctc_newsigndt'])->format('Y-m-d') : ($case->ctc_newsigndt ? Carbon::parse($case->ctc_newsigndt)->format('Y-m-d') : null);
        $terminRemarks = trim((string)($data['ctc_terminremarks'] ?? ($case->ctc_terminremarks ?? '')));

        // 1. Common Validation: Signing date required for new contracts (Hg, Cr, Rh)
        if (in_array($type, ['HG', 'CR', 'RH']) && empty($signDate)) {
            throw new InvalidArgumentException('Please enter signing date of the new contract (ctc_newsigndt).');
        }

        // 2. Hg Validation: Employee must be added before closing/fulfilling case
        if ($type === 'HG' && empty($case->ctc_emp_id)) {
            throw new InvalidArgumentException('Please add employee before closing the case.');
        }

        // 3. Ce Validation: Extension reason required
        if ($type === 'CE' && empty($terminRemarks)) {
            throw new InvalidArgumentException('Reason for contract extension is required (ctc_terminremarks).');
        }

        // 4. Cr Early Termination Validation:
        if ($type === 'CR' && $case->ctc_ctr_id) {
            $oldCtr = HrContract::find($case->ctc_ctr_id);
            if ($oldCtr) {
                $oldEnd = $oldCtr->ctr_termindt ? Carbon::parse($oldCtr->ctr_termindt) : Carbon::parse($oldCtr->ctr_enddt);
                $newStart = Carbon::parse($case->ctc_approvedstartdt ?: $case->ctc_newstartdt);
                $expectedStart = $oldEnd->copy()->addDay();

                if (!$newStart->isSameDay($expectedStart) && empty($terminRemarks)) {
                    throw new InvalidArgumentException('Reason for early termination / date mismatch of last contract is required.');
                }
            }
        }

        return DB::transaction(function () use ($case, $user, $type, $signDate, $terminRemarks) {
            $newContractId = null;

            if (in_array($type, ['HG', 'CR', 'RH'])) {
                // ── PATH A: Create New Contract (Hg, Cr, Rh) ─────────

                // If Cr and early termination occurred, update old contract
                if ($type === 'CR' && $case->ctc_ctr_id) {
                    $oldCtr = HrContract::find($case->ctc_ctr_id);
                    if ($oldCtr) {
                        $oldEnd = $oldCtr->ctr_termindt ? Carbon::parse($oldCtr->ctr_termindt) : Carbon::parse($oldCtr->ctr_enddt);
                        $newStart = Carbon::parse($case->ctc_approvedstartdt ?: $case->ctc_newstartdt);
                        $expectedStart = $oldEnd->copy()->addDay();

                        if (!$newStart->isSameDay($expectedStart)) {
                            $newTerminDt = $newStart->copy()->subDay()->format('Y-m-d');
                            $existingRemarks = trim((string)($oldCtr->ctr_remarks ?? ''));
                            $updatedRemarks = $this->appendRemarks($existingRemarks, $terminRemarks);

                            DB::table('hr.contracts')
                                ->where('ctr_id', $oldCtr->ctr_id)
                                ->update([
                                    'ctr_termindt' => $newTerminDt,
                                    'ctr_remarks'  => $updatedRemarks,
                                ]);

                            // Adjust old contract plan
                            $this->pricingService->adjustContractPlans($oldCtr->ctr_id, $newTerminDt);
                        }
                    }
                }

                // Resolve values for new contract
                $unitId = $case->ctc_approvedunt_id ?: ($case->ctc_newunt_id ?: ($case->ctc_unt_id ?: 1));

                // 1. Check existing emp_id
                $empId = $case->ctc_emp_id;
                if (empty($empId) && !empty($case->ctc_cnic)) {
                    $existingEmp = DB::table('hr.emps')->where('emp_cnic', $case->ctc_cnic)->first();
                    if ($existingEmp) {
                        $empId = $existingEmp->emp_id;
                    }
                }

                // 2. If fresh employee not in hr.emps, register candidate (emp_id fits in varchar 13)
                if (empty($empId)) {
                    $empId = "E" . date('ym') . "-" . str_pad((string)$case->ctc_id, 6, '0', STR_PAD_LEFT);
                }

                $empId = substr($empId, 0, 13);
                $uniqueCnic = $case->ctc_cnic ?: ('99999-' . str_pad((string)$case->ctc_id, 7, '0', STR_PAD_LEFT) . '-1');

                $firstPlanHeadId = HrCtrCasePlan::where('ccp_ctc_id', $case->ctc_id)->whereNotNull('ccp_hed_id')->value('ccp_hed_id');
                $headId = $case->ctc_prj_id ?: $firstPlanHeadId;

                $empExists = DB::table('hr.emps')->where('emp_id', $empId)->exists();
                if (!$empExists) {
                    DB::table('hr.emps')->insert([
                        'emp_id'     => $empId,
                        'emp_name'   => $case->ctc_empnamecomp ?: 'New Employee',
                        'emp_cnic'   => $uniqueCnic,
                        'emp_unt_id' => $unitId,
                        'emp_hed_id' => $headId,
                        'emp_title'  => $case->ctc_approvedjobtitle ?: $case->ctc_newjobtitle,
                        'emp_rank'   => $case->ctc_approvedgrade ?: $case->ctc_newgrade,
                        'emp_joindt' => $case->ctc_approvedstartdt ?: $case->ctc_newstartdt,
                        'emp_status' => 'Active',
                    ]);
                } else if ($headId) {
                    DB::table('hr.emps')->where('emp_id', $empId)->update([
                        'emp_hed_id' => $headId,
                    ]);
                }

                $contractType = (int)($case->ctc_approvedctrtype ?: ($case->ctc_newctrtype ?: 1));
                if ($contractType <= 0) {
                    $contractType = 1; // Default to Full Time
                }

                // Create new hr.contracts row
                $contractData = [
                    'ctr_num'      => $empId,
                    'ctr_date'     => $signDate,
                    'ctr_unt_id'   => $unitId,
                    'ctr_hed_id'   => $headId,
                    'ctr_startdt'  => $case->ctc_approvedstartdt ?: $case->ctc_newstartdt,
                    'ctr_enddt'    => $case->ctc_approvedenddt ?: $case->ctc_newenddt,
                    'ctr_salary'   => $case->ctc_approvedsalary ?: $case->ctc_newsalary,
                    'ctr_jobtitle' => $case->ctc_approvedjobtitle ?: $case->ctc_newjobtitle,
                    'ctr_grade'    => $case->ctc_approvedgrade ?: $case->ctc_newgrade,
                    'ctr_type'     => $contractType,
                    'ctr_ctc_id'   => $case->ctc_id,
                ];

                if ($type === 'HG') {
                    $contractData['ctr_prob'] = $case->ctc_approvedprob ?? $case->ctc_newprob;
                    $contractData['ctr_probsal'] = $case->ctc_approvedprobsal ?? $case->ctc_newprobsal;
                }

                $newContractId = DB::table('hr.contracts')->insertGetId($contractData, 'ctr_id');

                // Copy hr.ctrcaseplans → hr.contractplans
                $casePlans = HrCtrCasePlan::where('ccp_ctc_id', $case->ctc_id)->orderBy('ccp_startdt')->get();
                if ($casePlans->isNotEmpty()) {
                    foreach ($casePlans as $cp) {
                        DB::table('hr.contractplans')->insert([
                            'cpn_ctr_id'  => $newContractId,
                            'cpn_startdt' => $cp->ccp_startdt,
                            'cpn_enddt'   => $cp->ccp_enddt,
                            'cpn_hed_id'  => $cp->ccp_hed_id,
                        ]);
                    }
                } else {
                    // Fallback: generate default contract plans
                    $this->pricingService->generatePlans($case->ctc_id, $contractData['ctr_startdt'], $contractData['ctr_enddt'], null, $case->ctc_prj_id);
                    $newCasePlans = HrCtrCasePlan::where('ccp_ctc_id', $case->ctc_id)->get();
                    foreach ($newCasePlans as $cp) {
                        DB::table('hr.contractplans')->insert([
                            'cpn_ctr_id'  => $newContractId,
                            'cpn_startdt' => $cp->ccp_startdt,
                            'cpn_enddt'   => $cp->ccp_enddt,
                            'cpn_hed_id'  => $cp->ccp_hed_id,
                        ]);
                    }
                }

                // Add fin.contractsverif record (cvf_verif = 0)
                $verifExists = DB::table('fin.contractsverif')->where('cvf_ctr_id', $newContractId)->exists();
                if (!$verifExists) {
                    DB::table('fin.contractsverif')->insert([
                        'cvf_ctr_id' => $newContractId,
                        'cvf_verif'  => '0',
                        'cvf_dtg'    => null,
                    ]);
                }

            } elseif ($type === 'CE') {
                // ── PATH B: Extension (Ce) — Update Existing Contract Only ─────────

                $existingCtrId = $case->ctc_ctr_id;
                if ($existingCtrId) {
                    $oldCtr = HrContract::find($existingCtrId);
                    if ($oldCtr) {
                        $existingRemarks = trim((string)($oldCtr->ctr_remarks ?? ''));
                        $updatedRemarks = $this->appendRemarks($existingRemarks, $terminRemarks);
                        $extensionEndDate = $case->ctc_approvedenddt ?: $case->ctc_newenddt;

                        DB::table('hr.contracts')
                            ->where('ctr_id', $existingCtrId)
                            ->update([
                                'ctr_termindt' => $extensionEndDate,
                                'ctr_remarks'  => $updatedRemarks,
                            ]);

                        // Adjust plans for existing contract
                        $this->pricingService->adjustContractPlans($existingCtrId, $extensionEndDate);
                    }
                }
            }

            // ── Attachments & Status Update ───────────────────────

            // Create attachment slot for Minute if not present
            $hasMinuteSlot = DB::table('hr.ctrcaseattachments')
                ->where('cat_objtype', 'ctc')
                ->where('cat_objid', $case->ctc_id)
                ->where('cat_type', 'Minute')
                ->exists();

            if (!$hasMinuteSlot) {
                DB::table('hr.ctrcaseattachments')->insert([
                    'cat_objtype' => 'ctc',
                    'cat_objid'   => $case->ctc_id,
                    'cat_type'    => 'Minute',
                    'cat_path'    => null,
                ]);
            }

            // Update case table
            DB::table('hr.ctrcases')
                ->where('ctc_id', $case->ctc_id)
                ->update([
                    'ctc_emp_id'         => $empId ?? $case->ctc_emp_id,
                    'ctc_newctr_id'      => $newContractId,
                    'ctc_newsigndt'      => $signDate,
                    'ctc_terminremarks'  => $terminRemarks ?: $case->ctc_terminremarks,
                    'ctc_status'         => 'Fulfilled',
                    'ctc_closedtg'       => now(),
                ]);

            // Close substatus to terminal Fulfilled
            $this->approvalService->closeSubstatus($case, 'Fulfilled', 'Fulfilled');

            // Record fulfillment remark
            $this->approvalService->recordRemark(
                $case,
                $user,
                'Contract Case fulfilled successfully' . ($newContractId ? " (New Contract #{$newContractId})" : " (Contract Extended)"),
                'Fulfilled'
            );

            $case->refresh();
            return $case;
        });
    }

    /**
     * Helper to append remarks in legacy format: dot + space if not already ending in period
     */
    protected function appendRemarks(string $existing, string $newRemarks): string
    {
        $existing = trim($existing);
        $newRemarks = trim($newRemarks);

        if (empty($newRemarks)) {
            return $existing;
        }

        if (empty($existing)) {
            return $newRemarks;
        }

        if (str_ends_with($existing, '.')) {
            return $existing . ' ' . $newRemarks;
        }

        return $existing . '. ' . $newRemarks;
    }
}
