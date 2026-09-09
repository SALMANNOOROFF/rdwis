<?php

namespace App\Services;

use App\Models\FinCommitment;
use App\Models\FinSalOrder;
use App\Models\FinSalOrderShd;
use App\Models\HrEmployee;
use App\Models\HrSalReq;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

class SalaryGenerationService
{
    public function __construct(
        protected FinancialIntelligenceService $fis,
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Preview salary generation for a given month and scope.
     * Evaluates all candidates against the 7 legacy exclusion checks.
     *
     * @param string $salMonth YYYY-MM or YYYY-MM-DD
     * @param int|null $unitScope
     * @param Authenticatable|null $user
     * @return array
     */
    public function previewSalary(string $salMonth, ?int $unitScope = null, ?Authenticatable $user = null): array
    {
        $salMonthDate = Carbon::parse($salMonth)->endOfMonth()->toDateString();
        $firstDayOfMonth = Carbon::parse($salMonth)->startOfMonth()->toDateString();

        // Check 1: Future month guard
        $isFutureMonth = Carbon::parse($salMonthDate)->isAfter(Carbon::now()->endOfMonth());

        // Get candidates: active or last date in current month
        $query = DB::table('hr.emps as e')
            ->select('e.emp_id', 'e.emp_name', 'e.emp_title', 'e.emp_rank', 'e.emp_unt_id', 'e.emp_status', 'e.emp_lastdt', 'e.emp_hed_id');

        if ($user) {
            $isMultiple = ($user->acc_access ?? '') === 'multiple' || strtolower(trim((string)($user->acc_untarea ?? ''))) === 'fin';
            $userLower = $isMultiple ? (int)($user->acc_lowerm ?? 100000) : (int)($user->acc_lowers ?? 100000);
            $userUpper = $isMultiple ? (int)($user->acc_upperm ?? 999999) : (int)($user->acc_uppers ?? 999999);

            if ($unitScope !== null) {
                if ($unitScope < $userLower || $unitScope > $userUpper) {
                    abort(403, "Access denied. Unit {$unitScope} is outside your assigned division scope.");
                }
                $query->where('e.emp_unt_id', $unitScope);
            } else {
                $query->whereBetween('e.emp_unt_id', [$userLower, $userUpper]);
            }
        } elseif ($unitScope !== null) {
            $query->where('e.emp_unt_id', $unitScope);
        }

        $query->where(function ($q) use ($firstDayOfMonth) {
            $q->where('e.emp_status', 'ILIKE', 'Active%')
              ->orWhere('e.emp_lastdt', '>=', $firstDayOfMonth);
        });

        $candidates = $query->orderBy('e.emp_id')->get();

        $included = [];
        $excluded = [];

        foreach ($candidates as $emp) {
            if ($isFutureMonth) {
                $excluded[] = [
                    'employee' => $emp,
                    'reason'   => 'Future Month',
                ];
                continue;
            }

            // Check 2: Already Generated duplicate guard
            $alreadyExists = DB::table('hr.salreqs')
                ->where('srq_emp_id', $emp->emp_id)
                ->where('srq_month', $salMonthDate)
                ->whereIn('srq_status', ['Draft', 'In Process', 'Fulfilled'])
                ->exists();

            if ($alreadyExists) {
                $excluded[] = [
                    'employee' => $emp,
                    'reason'   => 'Already Generated',
                ];
                continue;
            }

            // Check 3: Active contract/plan check (via FIS getSalaryMatrix)
            $matrix = $this->fis->getSalaryMatrix($emp->emp_id, $salMonthDate);
            if (empty($matrix) || !isset($matrix[11])) {
                $excluded[] = [
                    'employee' => $emp,
                    'reason'   => 'No Contract/Plan',
                ];
                continue;
            }

            // Check group rows (8, 9, ...)
            $groupRows = [];
            for ($i = 8; $i <= 10; $i++) {
                if (isset($matrix[$i])) {
                    $groupRows[$i] = $matrix[$i];
                }
            }

            if (empty($groupRows)) {
                $excluded[] = [
                    'employee' => $emp,
                    'reason'   => 'No Contract/Plan',
                ];
                continue;
            }

            // Check 4: Salary Head check
            $hasMissingHead = false;
            foreach ($groupRows as $g) {
                if (empty($g['effhed_id']) || $g['effhed_id'] === 'NULL') {
                    $hasMissingHead = true;
                    break;
                }
            }
            if ($hasMissingHead) {
                $excluded[] = [
                    'employee' => $emp,
                    'reason'   => 'Salary Head Not Set',
                ];
                continue;
            }

            // Check 5: Contract Verification (fin.contractsverif)
            $unverified = false;
            $allCtrIds = [];
            foreach ($groupRows as $g) {
                if (!empty($g['ctr_id'])) {
                    $ids = array_filter(array_map('trim', explode(',', (string)$g['ctr_id'])));
                    $allCtrIds = array_merge($allCtrIds, $ids);
                }
            }
            $allCtrIds = array_unique($allCtrIds);

            if (!empty($allCtrIds)) {
                $verifRows = DB::table('fin.contractsverif')
                    ->whereIn('cvf_ctr_id', $allCtrIds)
                    ->pluck('cvf_verif');

                if ($verifRows->isEmpty()) {
                    $unverified = true;
                } else {
                    foreach ($verifRows as $v) {
                        if (!$v) {
                            $unverified = true;
                            break;
                        }
                    }
                }
            }

            if ($unverified) {
                $excluded[] = [
                    'employee' => $emp,
                    'reason'   => 'Contract Not Verified',
                ];
                continue;
            }

            // Check 6: Multiple Bank Accounts (hr.bnkaccounts where bac_selforpay = true)
            $bankAccounts = DB::table('hr.bnkaccounts')
                ->where('bac_emp_id', $emp->emp_id)
                ->where('bac_selforpay', true)
                ->get();

            if ($bankAccounts->count() > 1) {
                $excluded[] = [
                    'employee' => $emp,
                    'reason'   => 'Multiple Bank Accounts',
                ];
                continue;
            }

            // Calculate dues and payment breakdown
            $dues = (float) $this->fis->calculateArrDues($emp->emp_id, $salMonthDate);

            // Fetch already paid and payment in process
            $paidAlready = (float) DB::table('hr.salreqs')
                ->where('srq_emp_id', $emp->emp_id)
                ->where('srq_month', $salMonthDate)
                ->where('srq_status', 'Fulfilled')
                ->sum('srq_fulfilment');

            $inProcess = (float) DB::table('fin.salorders')
                ->where('sor_emp_id', $emp->emp_id)
                ->where('sor_month', $salMonthDate)
                ->whereIn('sor_status', ['Draft', 'In Process'])
                ->sum('sor_salary');

            $totalNetSalary = 0;
            $breakdown = [];

            foreach ($groupRows as $idx => $g) {
                $gross = (float) round($g['prorated_salary'], 0);
                $underwork = (float) $g['underwork'];
                $overwork = 0.0;
                $award = 0.0;
                $penalty = 0.0;
                $withheld = 0.0;
                $loaned = 0.0;

                $arrears = ($idx === 8 && $dues > 0) ? $dues : 0.0;
                $dueDeduction = ($idx === 8 && $dues < 0) ? abs($dues) : 0.0;
                $paidAlr = ($idx === 8) ? $paidAlready : 0.0;
                $inProc = ($idx === 8) ? $inProcess : 0.0;

                $net = $gross + $overwork - $underwork + $award - $penalty + $arrears - $dueDeduction - $paidAlr - $inProc;
                $finalSal = $net - $withheld + $loaned;

                $totalNetSalary += $finalSal;

                // Bank formatting
                $bankDetail = '(Pay by Cheque)';
                $bankTitle = strtoupper($emp->emp_name);

                if ($bankAccounts->count() === 1) {
                    $bac = $bankAccounts->first();
                    $bankTitle = $bac->bac_acctitle;
                    if ($bac->bac_bnkname === 'Meezan Bank Ltd') {
                        $bankDetail = trim($bac->bac_accnum . ' (' . $bac->bac_bchcode . ')');
                    }
                }

                // Head parsing
                $headParts = explode(' ', trim((string)$g['effhed_id']), 2);
                $effHedId = (int) ($headParts[0] ?? 0);
                $sudoHed = $headParts[1] ?? null;

                $effUntId = (int) DB::table('cen.heads')->where('hed_id', $effHedId)->value('hed_unt_id');

                $breakdown[] = [
                    'group_idx'       => $idx,
                    'ctr_id'          => $g['ctr_id'],
                    'start'           => $g['start'],
                    'end'             => $g['end'],
                    'salary'          => (int) round($finalSal, 0),
                    'ctrsalary'       => (int) round($g['salary'], 0),
                    'grosalary'       => (int) round($gross, 0),
                    'netsalary'       => (int) round($net, 0),
                    'unpaiddays'      => (float) ($g['absent'] + $g['unpaid']),
                    'underwork'       => (int) round($underwork, 0),
                    'overwork'        => (int) round($overwork, 0),
                    'award'           => (int) round($award, 0),
                    'penalty'         => (int) round($penalty, 0),
                    'loaned'          => (int) round($loaned, 0),
                    'withheld'        => (int) round($withheld, 0),
                    'arrears'         => (int) round($arrears, 0),
                    'dues'            => (int) round($dueDeduction, 0),
                    'paidalready'     => (int) round($paidAlr, 0),
                    'effhed_id'       => $effHedId,
                    'effunt_id'       => $effUntId,
                    'sudohed'         => $sudoHed,
                    'hed_id'          => $g['head_id'] ?? $emp->emp_hed_id,
                    'empnamecomp'     => trim(trim(($emp->emp_title ?? '') . ' ' . ($emp->emp_rank ?? '')) . ' ' . $emp->emp_name) ?: $emp->emp_name,
                    'bnkaccdetail'    => $bankDetail,
                    'bnkacctitle'     => $bankTitle,
                    'absent'          => $g['absent'],
                    'unpaid'          => $g['unpaid'],
                ];
            }

            // Check 7: Net salary <= 0
            if ($totalNetSalary <= 0) {
                $excluded[] = [
                    'employee' => $emp,
                    'reason'   => 'Net Salary Zero or Negative',
                ];
                continue;
            }

            $included[] = [
                'employee'         => $emp,
                'total_salary'     => (int) round($totalNetSalary, 0),
                'breakdown'        => $breakdown,
                'matrix_summary'   => $matrix[11],
            ];
        }

        return [
            'month'     => $salMonthDate,
            'included'  => $included,
            'excluded'  => $excluded,
            'counts'    => [
                'total_candidates' => count($candidates),
                'eligible'         => count($included),
                'excluded'         => count($excluded),
            ],
        ];
    }

    /**
     * Generate salary requisitions in hr.salreqs for selected employees.
     *
     * @param string $salMonth
     * @param array $empIds
     * @param Authenticatable $user
     * @return array
     */
    public function generateSalary(string $salMonth, array $empIds, Authenticatable $user): array
    {
        $isMultiple = ($user->acc_access ?? '') === 'multiple' || strtolower(trim((string)($user->acc_untarea ?? ''))) === 'fin';
        $userLower = $isMultiple ? (int)($user->acc_lowerm ?? 100000) : (int)($user->acc_lowers ?? 100000);
        $userUpper = $isMultiple ? (int)($user->acc_upperm ?? 999999) : (int)($user->acc_uppers ?? 999999);

        $outOfBounds = DB::table('hr.emps')
            ->whereIn('emp_id', $empIds)
            ->where(function ($q) use ($userLower, $userUpper) {
                $q->where('emp_unt_id', '<', $userLower)
                  ->orWhere('emp_unt_id', '>', $userUpper);
            })
            ->exists();

        if ($outOfBounds) {
            abort(403, 'Unauthorized. One or more selected employees are outside your division unit scope.');
        }

        $preview = $this->previewSalary($salMonth, null, $user);
        $eligibleMap = collect($preview['included'])->keyBy(fn($item) => $item['employee']->emp_id);

        $createdRequisitions = [];

        DB::transaction(function () use ($empIds, $eligibleMap, $preview, &$createdRequisitions) {
            foreach ($empIds as $empId) {
                if (!$eligibleMap->has($empId)) {
                    continue;
                }

                $item = $eligibleMap->get($empId);
                $emp = $item['employee'];
                $breakdowns = $item['breakdown'];

                foreach ($breakdowns as $b) {
                    $remarks = $this->buildRemarksString($b);

                    $req = HrSalReq::create([
                        'srq_emp_id'       => $emp->emp_id,
                        'srq_unt_id'       => $emp->emp_unt_id,
                        'srq_hed_id'       => $b['hed_id'],
                        'srq_effhed_id'    => $b['effhed_id'],
                        'srq_effunt_id'    => $b['effunt_id'],
                        'srq_month'        => $preview['month'],
                        'srq_status'       => 'Draft',
                        'srq_salary'       => $b['salary'],
                        'srq_empnamecomp'  => $b['empnamecomp'] ?? $emp->emp_name,
                        'srq_ctrsalary'    => $b['ctrsalary'],
                        'srq_grosalary'    => $b['grosalary'],
                        'srq_netsalary'    => $b['netsalary'],
                        'srq_contracts'    => (string) $b['ctr_id'],
                        'srq_bnkaccdetail' => $b['bnkaccdetail'],
                        'srq_bnkacctitle'  => $b['bnkacctitle'],
                        'srq_remarks'      => $remarks ?: null,
                        'srq_unpaiddays'   => $b['unpaiddays'],
                        'srq_paidholidays' => 0,
                        'srq_underwork'    => $b['underwork'],
                        'srq_overwork'     => $b['overwork'],
                        'srq_award'        => $b['award'],
                        'srq_penalty'      => $b['penalty'],
                        'srq_loaned'       => $b['loaned'],
                        'srq_withheld'     => $b['withheld'],
                        'srq_arrears'      => $b['arrears'],
                        'srq_dues'         => $b['dues'],
                        'srq_paidalready'  => $b['paidalready'],
                        'srq_sudohed'      => $b['sudohed'],
                        'srq_fulfilment'   => null,
                        'srq_parent'       => null,
                    ]);

                    $createdRequisitions[] = $req;
                }
            }
        });

        return [
            'month'     => $preview['month'],
            'generated' => count($createdRequisitions),
            'items'     => $createdRequisitions,
        ];
    }

    /**
     * Release salary requisition group (srq_id or srq_parent = srq_id) -> 'In Process'.
     * Resolves to group root parent so child-initiated release cascades to parent and siblings.
     */
    public function releaseRequisitions(int $srqId): int
    {
        $req = HrSalReq::findOrFail($srqId);
        $targetId = ($req->srq_parent && $req->srq_parent > 0) ? (int)$req->srq_parent : (int)$req->srq_id;

        return DB::transaction(function () use ($targetId) {
            return HrSalReq::where(function ($q) use ($targetId) {
                $q->where('srq_id', $targetId)
                  ->orWhere('srq_parent', $targetId);
            })->update([
                'srq_status'      => 'In Process',
                'srq_releasedtg' => now(),
            ]);
        });
    }

    /**
     * Create salary orders from requisition group (Salary.bas AddSalaryOrderGroup).
     */
    public function createSalaryOrders(int $srqId): array
    {
        return DB::transaction(function () use ($srqId) {
            $reqs = HrSalReq::where(function ($q) use ($srqId) {
                $q->where('srq_id', $srqId)
                  ->orWhere('srq_parent', $srqId);
            })->orderBy('srq_parent', 'asc')->get();

            if ($reqs->isEmpty()) {
                throw new \InvalidArgumentException("Salary requisition {$srqId} not found.");
            }

            $createdOrders = [];
            $parentSorId = null;

            foreach ($reqs as $rf) {
                $head = DB::table('cen.heads')->where('hed_id', $rf->srq_effhed_id)->first();
                $transType = $head ? ($head->hed_transtype ?? 1) : 1;
                $noLoan = is_null($rf->srq_hed_id) ? true : false;

                $sorParent = null;
                if ($rf->srq_parent !== null) {
                    $sorParent = ($rf->srq_parent > 0) ? $parentSorId : $rf->srq_parent;
                }

                $order = FinSalOrder::create([
                    'sor_srq_id'       => $rf->srq_id,
                    'sor_type'         => 'Sa',
                    'sor_emp_id'       => $rf->srq_emp_id,
                    'sor_empnamecomp'  => $rf->srq_empnamecomp,
                    'sor_hed_id'       => $rf->srq_hed_id,
                    'sor_unt_id'       => $rf->srq_unt_id,
                    'sor_effhed_id'    => $rf->srq_effhed_id,
                    'sor_effunt_id'    => $rf->srq_effunt_id,
                    'sor_month'        => $rf->srq_month,
                    'sor_ctrsalary'    => $rf->srq_ctrsalary,
                    'sor_netsalary'    => $rf->srq_netsalary,
                    'sor_salary'       => $rf->srq_salary,
                    'sor_bnkacctitle'  => $rf->srq_bnkacctitle,
                    'sor_bnkaccdetail' => $rf->srq_bnkaccdetail,
                    'sor_contracts'    => $rf->srq_contracts,
                    'sor_status'       => 'Draft',
                    'sor_remarks'      => $rf->srq_remarks,
                    'sor_remarks2'     => null,
                    'sor_transtype'    => $transType,
                    'sor_grosalary'    => $rf->srq_grosalary,
                    'sor_arrears'      => $rf->srq_arrears,
                    'sor_dues'         => $rf->srq_dues,
                    'sor_paidalready'  => $rf->srq_paidalready,
                    'sor_overwork'     => $rf->srq_overwork,
                    'sor_underwork'    => $rf->srq_underwork,
                    'sor_loaned'       => $rf->srq_loaned,
                    'sor_withheld'     => $rf->srq_withheld,
                    'sor_award'        => $rf->srq_award,
                    'sor_penalty'      => $rf->srq_penalty,
                    'sor_sudohed'      => $rf->srq_sudohed,
                    'sor_parent'       => $sorParent,
                    'sor_noloan'       => $noLoan,
                    'sor_releasedtg'   => now(),
                ]);

                if ($rf->srq_parent === 0) {
                    $parentSorId = $order->sor_id;
                }

                // Subhead ratio for project employees (when srq_hed_id is not null)
                if (!is_null($rf->srq_hed_id)) {
                    FinSalOrderShd::create([
                        'sod_sor_id'  => $order->sor_id,
                        'sod_type'    => 'Sa',
                        'sod_subhead' => 'HR',
                        'sod_ratio'   => 1.0,
                    ]);
                }

                // Mark requisition fulfillment to 0
                $rf->srq_fulfilment = 0;
                $rf->save();

                $createdOrders[] = $order;
            }

            return $createdOrders;
        });
    }

    /**
     * Approve salary order group (Salary.bas ApproveSalOrderGroup).
     * Creates negative commitment in fin.commitments and updates status to 'Approved'.
     *
     * FIX ITEM 4: Resolves to group root parent so child-initiated approval cascades to parent and all siblings.
     * FIX ITEM 3: Second contract-verification gate at approval time against fin.contractsverif (legacy fin_salorders_u.bas:88-102).
     */
    public function approveSalaryOrders(int $sorId, Authenticatable $user): array
    {
        $initialOrder = FinSalOrder::findOrFail($sorId);
        $targetId = ($initialOrder->sor_parent && $initialOrder->sor_parent > 0) ? (int)$initialOrder->sor_parent : (int)$initialOrder->sor_id;

        return DB::transaction(function () use ($targetId, $user) {
            $orders = FinSalOrder::where(function ($q) use ($targetId) {
                $q->where('sor_id', $targetId)
                  ->orWhere('sor_parent', $targetId);
            })->get();

            if ($orders->isEmpty()) {
                throw new \InvalidArgumentException("Salary order {$targetId} not found.");
            }

            // FIX ITEM 3: Contract verification check across entire group
            $allCtrIds = [];
            foreach ($orders as $o) {
                $rawContracts = trim((string)($o->sor_contracts ?? ''));
                if ($rawContracts === '') {
                    abort(422, 'Cannot approve: contract verification required for employee(s) in this order group.');
                }
                $ids = array_filter(array_map('trim', explode(',', $rawContracts)));
                if (empty($ids)) {
                    abort(422, 'Cannot approve: contract verification required for employee(s) in this order group.');
                }
                foreach ($ids as $id) {
                    $allCtrIds[] = (int)$id;
                }
            }

            $uniqueCtrIds = array_values(array_unique($allCtrIds));
            $verifRows = DB::table('fin.contractsverif')
                ->whereIn('cvf_ctr_id', $uniqueCtrIds)
                ->get()
                ->keyBy('cvf_ctr_id');

            // Legacy EOF check (GoTo NotVerified): If any contract is missing from fin.contractsverif
            if ($verifRows->count() < count($uniqueCtrIds)) {
                abort(422, 'Cannot approve: contract verification required for employee(s) in this order group.');
            }

            // Legacy allVerified check: If any contract has cvf_verif != true
            foreach ($uniqueCtrIds as $cid) {
                $v = $verifRows->get($cid);
                if (!$v || !$v->cvf_verif) {
                    abort(422, 'Cannot approve: contract verification required for employee(s) in this order group.');
                }
            }

            $commitments = [];

            foreach ($orders as $order) {
                // Ensure negative amount
                $amount = -1 * abs((float)$order->sor_salary);

                // Create commitment
                $cmt = FinCommitment::create([
                    'cmt_docid'     => $order->sor_id,
                    'cmt_type'      => 'Sa',
                    'cmt_date'      => now()->toDateString(),
                    'cmt_amount'    => $amount,
                    'cmt_status'    => 'Awaited',
                    'cmt_effhed_id' => $order->sor_effhed_id,
                    'cmt_effunt_id' => $order->sor_effunt_id,
                    'cmt_hed_id'    => $order->sor_hed_id,
                    'cmt_unt_id'    => $order->sor_unt_id,
                    'cmt_sudohed'   => $order->sor_sudohed,
                    'cmt_remarks'   => null,
                ]);

                $order->sor_status = 'Approved';
                $order->save();

                $commitments[] = $cmt;
            }

            return [
                'orders'      => $orders,
                'commitments' => $commitments,
            ];
        });
    }

    /**
     * Cancel salary requisition group (Salary.bas CancelSalaryReqGroup).
     * Resolves to group root parent so child-initiated cancellation cascades to parent and siblings.
     */
    public function cancelRequisition(int $srqId): int
    {
        $initialReq = HrSalReq::findOrFail($srqId);
        $targetId = ($initialReq->srq_parent && $initialReq->srq_parent > 0) ? (int)$initialReq->srq_parent : (int)$initialReq->srq_id;

        return DB::transaction(function () use ($targetId) {
            $reqs = HrSalReq::where(function ($q) use ($targetId) {
                $q->where('srq_id', $targetId)
                  ->orWhere('srq_parent', $targetId);
            })->get();

            if ($reqs->isEmpty()) {
                throw new \InvalidArgumentException("Salary requisition {$targetId} not found.");
            }

            foreach ($reqs as $req) {
                if ($req->srq_status === 'Fulfilled') {
                    abort(422, "Cannot cancel a fulfilled salary requisition.");
                }

                // Check linked orders
                $linkedOrders = FinSalOrder::where('sor_srq_id', $req->srq_id)->get();
                foreach ($linkedOrders as $lo) {
                    if (in_array($lo->sor_status, ['Approved', 'Fulfilled'], true)) {
                        abort(422, "Cannot cancel requisition with approved or fulfilled salary orders.");
                    }
                }
            }

            return HrSalReq::where(function ($q) use ($targetId) {
                $q->where('srq_id', $targetId)
                  ->orWhere('srq_parent', $targetId);
            })->update([
                'srq_status'   => 'Cancelled',
                'srq_closedtg' => now(),
            ]);
        });
    }

    /**
     * Cancel salary order group (Salary.bas CancelSalOrderGroup + Safety Commitment Cancellation).
     *
     * FIX ITEM 4: Resolves to group root parent so child-initiated cancellation cascades to parent and siblings.
     * ALL-OR-NOTHING GROUP CHECK: Pre-flight check rejects entire group with 422 if ANY member is Fulfilled or has a Paid commitment.
     */
    public function cancelOrder(int $sorId): array
    {
        $initialOrder = FinSalOrder::findOrFail($sorId);
        $targetId = ($initialOrder->sor_parent && $initialOrder->sor_parent > 0) ? (int)$initialOrder->sor_parent : (int)$initialOrder->sor_id;

        return DB::transaction(function () use ($targetId) {
            $orders = FinSalOrder::where(function ($q) use ($targetId) {
                $q->where('sor_id', $targetId)
                  ->orWhere('sor_parent', $targetId);
            })->get();

            if ($orders->isEmpty()) {
                throw new \InvalidArgumentException("Salary order {$targetId} not found.");
            }

            // CRITICAL ALL-OR-NOTHING PRE-FLIGHT CHECK:
            // If ANY order in the group is Fulfilled or linked commitment is Paid, reject entire group with 422.
            foreach ($orders as $order) {
                if ($order->sor_status === 'Fulfilled') {
                    abort(422, "Cannot cancel salary order group: Cannot cancel a fulfilled salary order (order #{$order->sor_id} is already fulfilled).");
                }

                $paidCommitment = FinCommitment::where('cmt_docid', $order->sor_id)
                    ->where('cmt_type', 'Sa')
                    ->where('cmt_status', 'Paid')
                    ->first();

                if ($paidCommitment) {
                    abort(422, "Cannot cancel salary order group: Cannot cancel an order whose commitment has already been paid (commitment #{$paidCommitment->cmt_id} for order #{$order->sor_id} is paid).");
                }
            }

            $cancelledCommitments = [];

            foreach ($orders as $order) {
                // 1. Reset requisition fulfillment
                if ($order->sor_srq_id) {
                    HrSalReq::where('srq_id', $order->sor_srq_id)->update([
                        'srq_fulfilment' => null,
                    ]);
                }

                // 2. Mark order as cancelled
                $order->sor_status = 'Cancelled';
                $order->sor_closedtg = now();
                $order->save();

                // 3. Deliberate enhancement: cancel existing awaited commitment if any
                $commitment = FinCommitment::where('cmt_docid', $order->sor_id)
                    ->where('cmt_type', 'Sa')
                    ->where('cmt_status', 'Awaited')
                    ->first();

                if ($commitment) {
                    $commitment->cmt_status = 'Cancelled';
                    $commitment->save();
                    $cancelledCommitments[] = $commitment;
                }
            }

            return [
                'orders'                => $orders,
                'cancelled_commitments' => $cancelledCommitments,
            ];
        });
    }

    /**
     * Manually adjust draft salary order salary (fin_salorders_u.bas:57-74).
     * Finance approver can only DECREASE salary (sor_salary <= sor_netsalary).
     * Automatically handles "Pending - {diff}." remark suffix.
     * Operates per-order-row (not cascaded to siblings, matching legacy datasheet behavior).
     */
    public function adjustOrderSalary(int $sorId, float $newSalary, Authenticatable $user): FinSalOrder
    {
        // 1. Authorization: Finance approver role required
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        if (($user->acc_auth ?? '') !== 'approver' || $userArea !== 'fin') {
            abort(403, 'Finance approver authorization required to adjust salary.');
        }

        $order = FinSalOrder::findOrFail($sorId);

        // 2. Unit horizon check
        $isMultiple = ($user->acc_access ?? '') === 'multiple' || $userArea === 'fin';
        $lower = $isMultiple ? ($user->acc_lowerm ?? 100000) : ($user->acc_lowers ?? 100000);
        $upper = $isMultiple ? ($user->acc_upperm ?? 999999) : ($user->acc_uppers ?? 999999);
        if ($order->sor_unt_id < $lower || $order->sor_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
        }

        // 3. Status guard: Only Draft orders can be adjusted
        if ($order->sor_status !== 'Draft') {
            abort(422, "Cannot adjust salary for order #{$sorId} with status '{$order->sor_status}'. Only 'Draft' orders can be adjusted.");
        }

        // 4. Ceiling check: sor_salary_BeforeUpdate rejects increase
        if ($newSalary > (float)$order->sor_netsalary) {
            abort(422, 'Salary cannot be increased manually.');
        }

        if ($newSalary < 0) {
            abort(422, 'Salary cannot be negative.');
        }

        // 5. Remarks handling: exact match for legacy sor_salary_AfterUpdate
        // intPos = InStr(Nz(Me.sor_remarks, 0), "Pending")
        // If intPos > 0 Then Me.sor_remarks = Trim(Left(Me.sor_remarks, intPos - 1))
        // If Me.sor_salary.Value < Me.sor_netsalary Then
        //     Me.sor_remarks = Trim(Me.sor_remarks & " Pending - " & Me.sor_netsalary - Me.sor_salary.Value & ".")
        $remarks = (string)($order->sor_remarks ?? '');
        $pos = stripos($remarks, 'Pending');
        if ($pos !== false) {
            $remarks = trim(substr($remarks, 0, $pos));
        }

        $diff = (float)$order->sor_netsalary - $newSalary;
        if ($diff > 0) {
            $diffFormatted = (int) round($diff);
            $remarks = trim($remarks . " Pending - {$diffFormatted}.");
        }

        $order->sor_salary = (int) round($newSalary);
        $order->sor_remarks = $remarks !== '' ? $remarks : null;
        $order->save();

        return $order;
    }

    /**
     * Get salary slip data matching legacy fin_salary_slip layout.
     *
     * @param int $sorId
     * @param Authenticatable $user
     * @return array
     */
    public function getSalarySlipData(int $sorId, Authenticatable $user): array
    {
        // Enforce user untarea: HR cannot view salary orders/slips
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        if ($userArea === 'hr') {
            abort(403, 'Unauthorized. HR does not have access to salary orders or pay slips.');
        }

        $order = FinSalOrder::with(['unit'])->findOrFail($sorId);

        // Unit horizon check (support multiple / enterprise scope for finance and HQ)
        $isMultiple = ($user->acc_access ?? '') === 'multiple' || $userArea === 'fin';
        $lower = $isMultiple ? ($user->acc_lowerm ?? 100000) : ($user->acc_lowers ?? 100000);
        $upper = $isMultiple ? ($user->acc_upperm ?? 999999) : ($user->acc_uppers ?? 999999);
        if ($order->sor_unt_id < $lower || $order->sor_unt_id > $upper) {
            abort(403, 'Unauthorized unit access for salary order pay slip.');
        }

        // Linked requisition (if any)
        $requisition = $order->sor_srq_id ? HrSalReq::find($order->sor_srq_id) : null;

        // Employee demographics from hr.emps and hr.empsextb
        $emp = DB::table('hr.emps')->where('emp_id', $order->sor_emp_id)->first();
        $empExt = DB::table('hr.empsextb')->where('empextb_emp_id', $order->sor_emp_id)->first();

        // Contract details
        $contract = DB::table('hr.contracts')
            ->where('ctr_num', $order->sor_emp_id)
            ->orderBy('ctr_id', 'desc')
            ->first()
            ?? DB::table('hr.contracts')
                ->where('ctr_unt_id', $order->sor_unt_id)
                ->orderBy('ctr_id', 'desc')
                ->first();

        // Bank account details
        $bank = DB::table('hr.bnkaccounts')->where('bac_emp_id', $order->sor_emp_id)->first();

        // Unit details
        $unit = DB::table('cen.units')->where('unt_id', $order->sor_unt_id)->first();

        // Earnings breakdown
        $baseSalary = (int) ($order->sor_ctrsalary ?: ($order->sor_grosalary ?: 0));
        $arrears = (int) ($order->sor_arrears ?? 0);
        $overwork = (int) ($order->sor_overwork ?? 0);
        $award = (int) ($order->sor_award ?? 0);
        $totalEarnings = (int) ($order->sor_grosalary + $arrears + $overwork + $award);

        // Deductions breakdown
        $underwork = (int) ($order->sor_underwork ?? 0);
        $dues = (int) ($order->sor_dues ?? 0);
        $withheld = (int) ($order->sor_withheld ?? 0);
        $penalty = (int) ($order->sor_penalty ?? 0);
        $totalDeductions = (int) ($underwork + $dues + $withheld + $penalty);

        // Net payable
        $netPayable = (int) ($order->sor_salary);

        // Payment method string matching legacy:
        // IIf([sor_bnkaccdetail]="(Pay by Cheque)","Paid by cheque.","Deposited in a/c: " & [bac_accnum] & " (" & [bac_bnkname] & ")")
        $bankString = '';
        if (trim($order->sor_bnkaccdetail ?? '') === '(Pay by Cheque)' || empty($order->sor_bnkaccdetail)) {
            $bankString = 'Paid by cheque.';
        } else {
            $accNum = $bank->bac_accnum ?? $order->sor_bnkaccdetail;
            $bnkName = $bank->bac_bnkname ?? ($order->sor_bnkacctitle ?: 'Bank Account');
            $bankString = "Deposited in a/c: {$accNum} ({$bnkName})";
        }

        // Employment type string matching legacy: "Contract (" & ctr_type & ")"
        $contractTypeStr = 'Contract';
        if ($contract && isset($contract->ctr_type)) {
            $contractTypeStr = $contract->ctr_type == 1 ? 'Contract (Standard)' : 'Contract (Special)';
        }

        return [
            'order'            => $order,
            'requisition'      => $requisition,
            'emp'              => $emp,
            'empExt'           => $empExt,
            'contract'         => $contract,
            'bank'             => $bank,
            'unit'             => $unit,
            'bankString'       => $bankString,
            'contractTypeStr'  => $contractTypeStr,
            'earnings'         => [
                'base'     => $baseSalary,
                'arrears'  => $arrears,
                'overwork' => $overwork,
                'award'    => $award,
                'total'    => $totalEarnings,
            ],
            'deductions'       => [
                'underwork' => $underwork,
                'dues'      => $dues,
                'withheld'  => $withheld,
                'penalty'   => $penalty,
                'total'     => $totalDeductions,
            ],
            'netPayable'       => $netPayable,
        ];
    }

    /**
     * Build legacy-faithful remarks string.
     */
    protected function buildRemarksString(array $b): string
    {
        $remarks = [];

        if ($b['arrears'] > 0) {
            $remarks[] = "Arrears - " . number_format($b['arrears']) . ".";
        }
        if ($b['dues'] > 0) {
            $remarks[] = "Dues - " . number_format($b['dues']) . ".";
        }
        if ($b['underwork'] > 0) {
            $parts = [];
            if (!empty($b['absent']) && $b['absent'] > 0) {
                $parts[] = "absents " . $b['absent'];
            }
            if (!empty($b['unpaid']) && $b['unpaid'] > 0) {
                $parts[] = "unpaid leave " . $b['unpaid'];
            }
            $paren = !empty($parts) ? " (" . implode(", ", $parts) . ")" : "";
            $remarks[] = "Deduction - " . number_format($b['underwork']) . "{$paren}.";
        }
        if ($b['overwork'] > 0) {
            $remarks[] = "Overtime - " . number_format($b['overwork']) . ".";
        }
        if ($b['award'] > 0) {
            $remarks[] = "Award - " . number_format($b['award']) . ".";
        }
        if ($b['penalty'] > 0) {
            $remarks[] = "Penalty - " . number_format($b['penalty']) . ".";
        }
        if ($b['loaned'] > 0) {
            $remarks[] = "Paid in advance - " . number_format($b['loaned']) . ".";
        }
        if ($b['withheld'] > 0) {
            $remarks[] = "Withheld - " . number_format($b['withheld']) . ".";
        }

        return trim(implode(' ', $remarks));
    }

    /**
     * Get paginated salary requisitions scoped to user's unit bounds.
     * Supports exact srq_status and month filters.
     */
    public function getRequisitions(Authenticatable $user, ?string $month = null, ?string $status = null, int $perPage = 25)
    {
        $isMultiple = ($user->acc_access ?? '') === 'multiple' || strtolower(trim((string)($user->acc_untarea ?? ''))) === 'fin';
        $lower = $isMultiple ? ($user->acc_lowerm ?? 100000) : ($user->acc_lowers ?? 100000);
        $upper = $isMultiple ? ($user->acc_upperm ?? 999999) : ($user->acc_uppers ?? 999999);

        $query = HrSalReq::whereBetween('srq_unt_id', [$lower, $upper])
            ->with(['employee', 'unit', 'head', 'effectiveHead', 'order.commitment']);

        if (!empty($month)) {
            $monthDate = Carbon::parse($month)->endOfMonth()->toDateString();
            $query->where('srq_month', $monthDate);
        }

        if (!empty($status)) {
            if ($status === 'Open') {
                $query->where('srq_status', 'In Process');
            } elseif ($status === 'Closed') {
                $query->whereIn('srq_status', ['Fulfilled', 'Cancelled']);
            } elseif (in_array($status, ['Draft', 'In Process', 'Fulfilled', 'Cancelled'], true)) {
                $query->where('srq_status', $status);
            }
        }

        return $query->orderBy('srq_id', 'desc')->paginate($perPage);
    }

    /**
     * Get paginated salary orders scoped to user's unit bounds.
     * Supports exact sor_status and month filters.
     */
    public function getOrders(Authenticatable $user, ?string $month = null, ?string $status = null, int $perPage = 25)
    {
        $isMultiple = ($user->acc_access ?? '') === 'multiple' || strtolower(trim((string)($user->acc_untarea ?? ''))) === 'fin';
        $lower = $isMultiple ? ($user->acc_lowerm ?? 100000) : ($user->acc_lowers ?? 100000);
        $upper = $isMultiple ? ($user->acc_upperm ?? 999999) : ($user->acc_uppers ?? 999999);

        $query = FinSalOrder::where(function ($q) use ($lower, $upper) {
            $q->whereBetween('sor_unt_id', [$lower, $upper])
              ->orWhereBetween('sor_effunt_id', [$lower, $upper]);
        })->with(['employee', 'unit', 'effectiveUnit', 'head', 'effectiveHead', 'commitment', 'subheads']);

        if (!empty($month)) {
            $monthDate = Carbon::parse($month)->endOfMonth()->toDateString();
            $query->where('sor_month', $monthDate);
        }

        if (!empty($status)) {
            if ($status === 'Open') {
                $query->where('sor_status', 'Approved');
            } elseif ($status === 'Closed') {
                $query->whereIn('sor_status', ['Fulfilled', 'Cancelled']);
            } elseif (in_array($status, ['Draft', 'Approved', 'Cancelled', 'Fulfilled'], true)) {
                $query->where('sor_status', $status);
            }
        }

        return $query->orderBy('sor_id', 'desc')->paginate($perPage);
    }

    /**
     * Get single salary order detail with relations, enforcing unit scope.
     */
    public function getOrderDetail(int $sorId, Authenticatable $user): ?FinSalOrder
    {
        $isMultiple = ($user->acc_access ?? '') === 'multiple' || strtolower(trim((string)($user->acc_untarea ?? ''))) === 'fin';
        $lower = $isMultiple ? ($user->acc_lowerm ?? 100000) : ($user->acc_lowers ?? 100000);
        $upper = $isMultiple ? ($user->acc_upperm ?? 999999) : ($user->acc_uppers ?? 999999);

        return FinSalOrder::where('sor_id', $sorId)
            ->where(function ($q) use ($lower, $upper) {
                $q->whereBetween('sor_unt_id', [$lower, $upper])
                  ->orWhereBetween('sor_effunt_id', [$lower, $upper]);
            })
            ->with(['employee', 'unit', 'effectiveUnit', 'subheads', 'commitment', 'requisition'])
            ->first();
    }

    /**
     * Audit salary commitments against Approved salary orders.
     * Matches the verification data shape of VerifySalaryCommitmentsCommand.
     */
    public function getCommitmentVerifications(?string $month = null): array
    {
        $query = DB::table('fin.salorders as s')
            ->leftJoin('fin.commitments as c', function ($join) {
                $join->on('s.sor_id', '=', 'c.cmt_docid')
                     ->where('c.cmt_type', '=', 'Sa');
            })
            ->where('s.sor_status', 'Approved');

        if (!empty($month)) {
            $monthDate = Carbon::parse($month)->endOfMonth()->toDateString();
            $query->where('s.sor_month', $monthDate);
        }

        $allApproved = $query->select(
            's.sor_id',
            's.sor_emp_id',
            's.sor_empnamecomp',
            's.sor_month',
            's.sor_salary',
            's.sor_status',
            's.sor_releasedtg',
            'c.cmt_id',
            'c.cmt_amount',
            'c.cmt_status',
            'c.cmt_date'
        )->orderBy('s.sor_id', 'desc')->get();

        $verified = [];
        $missing = [];

        foreach ($allApproved as $row) {
            if ($row->cmt_id !== null) {
                $verified[] = $row;
            } else {
                $missing[] = $row;
            }
        }

        return [
            'total_approved' => count($allApproved),
            'verified_count' => count($verified),
            'missing_count'  => count($missing),
            'verified'       => $verified,
            'missing'        => $missing,
        ];
    }
}
