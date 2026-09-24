<?php

namespace App\Services;

use App\Enums\RevType;
use App\Models\AudRev;
use App\Models\CenHead;
use App\Models\FinContractVerif;
use App\Models\FinEmpEffHead;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FinanceVerificationService
{
    public function __construct(
        protected DataRevisionService $revisionService
    ) {}

    // =========================================================================
    // PART A: SALARY CONTRACT VERIFICATION (fin.contractsverif)
    // =========================================================================

    /**
     * Get contracts for verification list.
     * Joined to the latest contract per employee query:
     * WHERE ctr_id IN (SELECT MAX(ctr_id) FROM hr.contracts GROUP BY ctr_num)
     *
     * @param bool $verified false for Unverified list, true for Verified list
     * @return Collection
     */
    public function getContractsVerificationList(bool $verified = false): Collection
    {
        $query = DB::table('fin.contractsverif as cvf')
            ->join('hr.contracts as c', 'cvf.cvf_ctr_id', '=', 'c.ctr_id')
            ->join('hr.emps as e', 'c.ctr_num', '=', 'e.emp_id')
            ->leftJoin('cen.units as u', 'c.ctr_unt_id', '=', 'u.unt_id')
            ->leftJoin('cen.heads as h', 'c.ctr_hed_id', '=', 'h.hed_id')
            ->whereIn('c.ctr_id', function ($sub) {
                $sub->select(DB::raw('MAX(ctr_id)'))
                    ->from('hr.contracts')
                    ->groupBy('ctr_num');
            })
            ->where('cvf.cvf_verif', '=', $verified);

        if ($verified) {
            $query->orderBy('cvf.cvf_dtg', 'desc');
        } else {
            $query->orderBy('e.emp_name', 'asc');
        }

        return $query->select(
            'c.ctr_id',
            'c.ctr_num',
            'e.emp_name',
            'e.emp_title',
            'e.emp_rank',
            'c.ctr_grade',
            'c.ctr_unt_id',
            'u.unt_name',
            'u.unt_namesh',
            'c.ctr_hed_id',
            'h.hed_name',
            'h.hed_code',
            'c.ctr_salary',
            'c.ctr_prob',
            'c.ctr_probsal',
            'c.ctr_startdt',
            'c.ctr_enddt',
            'cvf.cvf_verif',
            'cvf.cvf_dtg'
        )->get();
    }

    /**
     * Verify a contract salary.
     * Stamps cvf_verif = true, cvf_dtg = now().
     *
     * Note on Audit Gate Deviation:
     * Legacy RecordBusDataAudit is a generic cross-form field-diff logger with no RDWIS equivalent yet;
     * full reimplementation is out of scope for this module and should be a separate cross-cutting initiative.
     *
     * @param int $ctrId
     * @return void
     */
    public function verifyContract(int $ctrId): void
    {
        $updated = DB::table('fin.contractsverif')
            ->where('cvf_ctr_id', $ctrId)
            ->update([
                'cvf_verif' => true,
                'cvf_dtg'   => now(),
            ]);

        if (!$updated) {
            // In case record was not pre-inserted
            DB::table('fin.contractsverif')->insert([
                'cvf_ctr_id' => $ctrId,
                'cvf_verif'  => true,
                'cvf_dtg'    => now(),
            ]);
        }
    }

    // =========================================================================
    // PART B: SALARY HEADS (fin.empeffheads)
    // =========================================================================

    /**
     * Get scoped salary heads list based on current-month contract plan date logic.
     *
     * Scoped to employees whose CURRENT MONTH contract plan starts this month,
     * ends this month, or has no contract plan at all:
     * cp.cpn_startdt = FirstDateThisMonth OR cp.cpn_enddt = LastDateThisMonth
     * OR (cp.cpn_startdt IS NULL AND cp.cpn_enddt IS NULL)
     *
     * @param string $status 'Open' or 'Closed'
     * @return Collection
     */
    public function getSalaryHeadsList(string $status = 'Open'): Collection
    {
        $firstDay = now()->startOfMonth()->toDateString();
        $lastDay = now()->endOfMonth()->toDateString();

        $currentMonthCtrs = DB::table('hr.contracts as c')
            ->join('hr.emps as e', 'c.ctr_num', '=', 'e.emp_id')
            ->select(
                'c.ctr_id',
                'c.ctr_num',
                DB::raw("CASE WHEN e.emp_joindt > c.ctr_startdt THEN e.emp_joindt ELSE c.ctr_startdt END as effstartdt"),
                DB::raw("COALESCE(c.ctr_termindt, c.ctr_enddt) as effenddt")
            )
            ->whereRaw("CASE WHEN e.emp_joindt > c.ctr_startdt THEN e.emp_joindt ELSE c.ctr_startdt END <= ?", [$lastDay])
            ->whereRaw("COALESCE(c.ctr_termindt, c.ctr_enddt) >= ?", [$firstDay]);

        return DB::table('fin.empeffheads as eeh')
            ->join('hr.emps as e', 'eeh.eeh_emp_id', '=', 'e.emp_id')
            ->leftJoinSub($currentMonthCtrs, 'cmc', function ($join) {
                $join->on('eeh.eeh_emp_id', '=', 'cmc.ctr_num');
            })
            ->leftJoin('hr.contractplans as cp', 'cmc.ctr_id', '=', 'cp.cpn_ctr_id')
            ->leftJoin('cen.heads as h', 'eeh.eeh_emphed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'e.emp_unt_id', '=', 'u.unt_id')
            ->where('eeh.eeh_status', '=', $status)
            ->where(function ($q) use ($firstDay, $lastDay) {
                $q->whereDate('cp.cpn_startdt', '=', $firstDay)
                  ->orWhereDate('cp.cpn_enddt', '=', $lastDay)
                  ->orWhere(function ($sub) {
                      $sub->whereNull('cp.cpn_startdt')
                          ->whereNull('cp.cpn_enddt');
                  });
            })
            ->select(
                'e.emp_id',
                'e.emp_name',
                'e.emp_title',
                'e.emp_rank',
                'e.emp_unt_id',
                'u.unt_name',
                'u.unt_namesh',
                'e.emp_status',
                'eeh.eeh_status',
                'eeh.eeh_emphed_id',
                'eeh.eeh_sudohed',
                'eeh.eeh_remarks',
                'eeh.eeh_dtg',
                'h.hed_name',
                'h.hed_code',
                'cp.cpn_hed_id',
                'cp.cpn_startdt',
                'cp.cpn_enddt',
                DB::raw("CASE WHEN e.emp_unt_id >= 800000 THEN e.emp_unt_id - 1000000 ELSE e.emp_unt_id END as sorter")
            )
            ->orderBy('sorter', 'asc')
            ->orderBy('cp.cpn_hed_id', 'asc')
            ->get();
    }

    /**
     * Check if head-assignment fields are visible for the employee.
     * VISIBLE unless (employee's unit is Project [200000–799999] AND salhead_applicable is false).
     * Central employees (unit NOT in 200000–799999) always see these fields.
     *
     * @param int|null $unitId
     * @return bool
     */
    public function isHeadAssignmentApplicable(?int $unitId): bool
    {
        $gvar = DB::table('cen.globalvars')
            ->where('gvar_name', 'salhead_applicable')
            ->value('gvar_value');
        $salheadApplicable = filter_var($gvar, FILTER_VALIDATE_BOOLEAN);

        $isProject = ($unitId !== null && $unitId >= 200000 && $unitId < 800000);

        if ($isProject && !$salheadApplicable) {
            return false;
        }

        return true;
    }

    /**
     * Update salary head assignment and status.
     * On assignment: auto-sets eeh_sudohed = "CHRF", else null.
     * Stamps eeh_dtg on any change.
     *
     * @param string $empId
     * @param int|null $headId
     * @param string|null $remarks
     * @param string|null $status
     * @return void
     */
    public function updateSalaryHead(string $empId, ?int $headId, ?string $remarks = null, ?string $status = null): void
    {
        $sudohed = !empty($headId) ? 'CHRF' : null;

        $payload = [
            'eeh_emphed_id' => $headId ?: null,
            'eeh_sudohed'   => $sudohed,
            'eeh_remarks'   => $remarks,
            'eeh_dtg'       => now(),
        ];

        if ($status && in_array($status, ['Open', 'Closed'], true)) {
            $payload['eeh_status'] = $status;
        }

        DB::table('fin.empeffheads')
            ->where('eeh_emp_id', $empId)
            ->update($payload);
    }

    /**
     * Mark an Open salary head as Closed (plain field save, no Data Revision).
     *
     * @param string $empId
     * @return void
     */
    public function closeSalaryHead(string $empId): void
    {
        DB::table('fin.empeffheads')
            ->where('eeh_emp_id', $empId)
            ->update([
                'eeh_status' => 'Closed',
                'eeh_dtg'    => now(),
            ]);
    }

    /**
     * Initiate reverse action for Closed salary head via DataRevisionService.
     * Creates a Draft aud.revs entry (rev_obj: 'Salary Head'),
     * routing to admin.reversals.show flow.
     *
     * @param string $empId
     * @param mixed $user
     * @param string|null $reason
     * @return AudRev
     */
    public function reverseSalaryHead(string $empId, $user, ?string $reason = null): AudRev
    {
        $effHead = DB::table('fin.empeffheads')->where('eeh_emp_id', $empId)->first();
        if (!$effHead) {
            throw new InvalidArgumentException("Salary head record for Employee {$empId} not found.");
        }

        if (strcasecmp($effHead->eeh_status, 'Closed') !== 0) {
            throw new InvalidArgumentException("Reversal can only be initiated for Closed salary head records.");
        }

        $emp = DB::table('hr.emps')->where('emp_id', $empId)->first(['emp_unt_id']);
        $unitId = (int) ($emp->emp_unt_id ?? ($user->acc_unt_id ?? 800000));

        return $this->revisionService->createDataRevision(
            revObject: 'Salary Head',
            objectId: $empId,
            unitId: $unitId,
            revType: RevType::FULL_CASCADE,
            revRef: null,
            revObjectExt: null,
            intUnitId: (int) ($user->acc_unt_id ?? $unitId),
            revReason: $reason ?: "Reversal request for Salary Head of employee {$empId}"
        );
    }
}
