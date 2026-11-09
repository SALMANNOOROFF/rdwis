<?php

namespace App\Services;

use App\Models\CenHead;
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

        // Get candidates: active, last date in current month, or expired contracts within past 12 months (for terminal clearance)
        $query = DB::table('hr.emps as e')
            ->leftJoin('cen.units as u', 'e.emp_unt_id', '=', 'u.unt_id')
            ->select(
                'e.emp_id',
                'e.emp_name',
                'e.emp_title',
                'e.emp_rank',
                'e.emp_unt_id',
                'e.emp_status',
                'e.emp_lastdt',
                'e.emp_hed_id',
                'u.unt_name as dept_name',
                'u.unt_namesh as dept_short'
            );

        if ($user) {
            $userArea = strtolower(trim((string)($user->acc_untarea ?? '')));
            $isCentralUser = in_array($userArea, ['hr', 'fin', 'adm', 'admin', 'is', 'it', 'hqs', 'rdw', 'nrdi'], true) || ((int)($user->acc_unt_id ?? 0) >= 800000);
            $isMultiple = ($user->acc_access ?? '') === 'multiple' || $isCentralUser;
            $userLower = $isCentralUser ? 100000 : ($isMultiple ? (int)($user->acc_lowerm ?? 100000) : (int)($user->acc_lowers ?? 100000));
            $userUpper = $isCentralUser ? 999999 : ($isMultiple ? (int)($user->acc_upperm ?? 999999) : (int)($user->acc_uppers ?? 999999));

            if ($unitScope !== null) {
                if (!$isCentralUser && ($unitScope < $userLower || $unitScope > $userUpper)) {
                    abort(403, "Access denied. Unit {$unitScope} is outside your assigned division scope.");
                }
                $query->where('e.emp_unt_id', $unitScope);
            } else {
                $query->whereBetween('e.emp_unt_id', [$userLower, $userUpper]);
            }
        } elseif ($unitScope !== null) {
            $query->where('e.emp_unt_id', $unitScope);
        }

        $query->where(function ($q) use ($firstDayOfMonth, $salMonthDate) {
            $q->where('e.emp_status', 'ILIKE', 'Active%')
              ->orWhere('e.emp_lastdt', '>=', $firstDayOfMonth)
              ->orWhereExists(function ($sub) use ($salMonthDate) {
                  $pastWindow = Carbon::parse($salMonthDate)->subMonths(12)->toDateString();
                  $sub->select(DB::raw(1))
                      ->from('hr.contracts as ctr')
                      ->join('hr.contractplans as cpn', 'ctr.ctr_id', '=', 'cpn.cpn_ctr_id')
                      ->whereColumn('ctr.ctr_num', 'e.emp_id')
                      ->where('cpn.cpn_enddt', '>=', $pastWindow);
              });
        });

        $candidates = $query->orderBy('e.emp_id')->get();

        $included = [];
        $excluded = [];

        foreach ($candidates as $emp) {
            $emp->emp_grade = DB::table('hr.contracts')
                ->where('ctr_num', $emp->emp_id)
                ->orderByDesc('ctr_id')
                ->value('ctr_grade') ?: ($emp->emp_rank ?: '-');

            if ($isFutureMonth) {
                $excluded[] = [
                    'employee' => $emp,
                    'reason'   => 'Future Month',
                ];
                continue;
            }

            // Check 2: Already Generated duplicate guard (checks direct requisitions and past months settled in arrears)
            $paidMonths = app(\App\Services\AttendanceService::class)->getPaidMonthsForEmployee($emp->emp_id);
            $targetMonthStr = \Carbon\Carbon::parse($salMonthDate)->format('Y-m');

            $regularAlreadyExists = in_array($targetMonthStr, $paidMonths, true) || DB::table('hr.salreqs')
                ->where('srq_emp_id', $emp->emp_id)
                ->where('srq_month', $salMonthDate)
                ->whereIn('srq_status', ['Draft', 'In Process', 'Fulfilled'])
                ->where(function($q) {
                    $q->where('srq_grosalary', '>', 0)
                      ->orWhere(function($sub) {
                          $sub->whereNull('srq_remarks2')
                              ->orWhereNotIn('srq_remarks2', ['ARREARS_ONLY', 'CLEARANCE']);
                      });
                })
                ->exists();

            if ($regularAlreadyExists) {
                $excluded[] = [
                    'employee' => $emp,
                    'reason'   => 'Already Generated',
                ];
                continue;
            }

            // Check 3: Active contract/plan check (via FIS getSalaryMatrix)
            $matrix = $this->fis->getSalaryMatrix($emp->emp_id, $salMonthDate);
            $isExpiredClearance = false;
            $groupRows = [];

            if (!empty($matrix)) {
                for ($i = 8; $i <= 10; $i++) {
                    if (isset($matrix[$i])) {
                        $groupRows[$i] = $matrix[$i];
                    }
                }
            }

            // If employee has NO active contract rows in the current month, check if they have past pending dues (Expired Contract Clearance)
            if (empty($groupRows)) {
                $duesCheck = (float) $this->fis->calculateArrDues($emp->emp_id, $salMonthDate);
                if ($duesCheck > 0) {
                    $lastContract = DB::table('hr.contracts as ctr')
                        ->leftJoin('hr.contractplans as cpn', 'ctr.ctr_id', '=', 'cpn.cpn_ctr_id')
                        ->where('ctr.ctr_num', $emp->emp_id)
                        ->orderByDesc('cpn.cpn_enddt')
                        ->orderByDesc('ctr.ctr_id')
                        ->select('ctr.ctr_id', 'ctr.ctr_salary', 'ctr.ctr_grade', 'cpn.cpn_hed_id')
                        ->first();

                    if ($lastContract) {
                        $effHead = DB::table('fin.empeffheads')->where('eeh_emp_id', $emp->emp_id)->first();
                        $effHedId = ($effHead && $effHead->eeh_emphed_id) ? (int)$effHead->eeh_emphed_id : ($lastContract->cpn_hed_id ?: (int)$emp->emp_hed_id);
                        $sudoHed = ($effHead && !empty($effHead->eeh_sudohed)) ? $effHead->eeh_sudohed : null;
                        $effHedStr = $effHedId . ($sudoHed ? ' ' . $sudoHed : '');

                        $groupRows = [
                            8 => [
                                'type'            => 1,
                                'ctr_id'          => $lastContract->ctr_id,
                                'effhed_id'       => $effHedStr,
                                'head_id'         => $lastContract->cpn_hed_id ?: (int)$emp->emp_hed_id,
                                'salary'          => (float)$lastContract->ctr_salary,
                                'prorated_salary' => 0.0,
                                'start'           => null,
                                'end'             => null,
                                'underwork'       => 0.0,
                                'absent'          => 0,
                                'unpaid'          => 0,
                            ]
                        ];
                        $isExpiredClearance = true;
                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
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

            // Bank Accounts: retrieve all accounts for employee, prioritizing primary selection
            $allBankAccounts = DB::table('hr.bnkaccounts')
                ->where('bac_emp_id', $emp->emp_id)
                ->orderByDesc('bac_selforpay')
                ->orderByDesc('bac_id')
                ->get();

            // Select primary if flagged, otherwise fallback to first available account
            $selectedBank = $allBankAccounts->firstWhere('bac_selforpay', true) ?? $allBankAccounts->first();

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

            // In-month standalone arrears requisitions (already created in draft/in process/fulfilled in hr.salreqs)
            $existingInMonthArrears = (float) DB::table('hr.salreqs')
                ->where('srq_emp_id', $emp->emp_id)
                ->where('srq_month', $salMonthDate)
                ->whereIn('srq_status', ['Draft', 'In Process', 'Fulfilled'])
                ->where(function($q) {
                    $q->whereIn('srq_remarks2', ['ARREARS_ONLY', 'CLEARANCE'])
                      ->orWhereIn('srq_sudohed', ['ARREAR', 'CLEARANCE']);
                })
                ->sum('srq_salary');

            $netArrearsDue = max(0.0, $dues - $existingInMonthArrears);

            // Attendance Completeness Evaluation (Days 1..25)
            $unmarkedDetails = [];
            $totalEmptyCount = 0;
            $joinDt = !empty($emp->emp_joindt) ? Carbon::parse($emp->emp_joindt) : null;

            if (!$isExpiredClearance) {
                // a. Check previous month (26th to end of month)
                $prevMonthDate = Carbon::parse($firstDayOfMonth)->subMonth()->format('Y-m-01');
                $prevDaysInMonth = (int) Carbon::parse($prevMonthDate)->daysInMonth;

                if (!$joinDt || $joinDt->lte(Carbon::parse($prevMonthDate)->endOfMonth())) {
                    $prevAttRecord = DB::table('hr.attendance')
                        ->where('att_emp_id', $emp->emp_id)
                        ->where('att_startdt', $prevMonthDate)
                        ->first();

                    if ($prevAttRecord) {
                        $prevEmptyDays = [];
                        $isPrevJoinMonth = $joinDt && ($joinDt->format('Y-m') === Carbon::parse($prevMonthDate)->format('Y-m'));
                        $prevJoinDay = $isPrevJoinMonth ? (int)$joinDt->format('j') : 1;

                        for ($d = 26; $d <= $prevDaysInMonth; $d++) {
                            $dayDt = Carbon::parse($prevMonthDate)->copy()->addDays($d - 1);
                            if ($isPrevJoinMonth && $d < $prevJoinDay) continue;
                            if ($dayDt->isWeekend()) continue;
                            $col = 'att_' . $d;
                            $val = isset($prevAttRecord->$col) && $prevAttRecord->$col !== null ? trim($prevAttRecord->$col) : '';
                            if ($val === '' || $val === 'Q') {
                                $prevEmptyDays[] = $d;
                            }
                        }

                        if (!empty($prevEmptyDays)) {
                            $prevMonthName = Carbon::parse($prevMonthDate)->format('M');
                            $unmarkedDetails[] = $this->formatDayRanges($prevEmptyDays, $prevMonthName);
                            $totalEmptyCount += count($prevEmptyDays);
                        }
                    }
                }

                // b. Check current month (1st through 25th cutoff)
                $attRecord = DB::table('hr.attendance')
                    ->where('att_emp_id', $emp->emp_id)
                    ->where('att_startdt', $firstDayOfMonth)
                    ->first();

                if ($attRecord) {
                    $emptyDayNumbers = [];
                    $cutoffDay = min(25, (int) Carbon::parse($salMonthDate)->daysInMonth);
                    $isJoiningMonth = $joinDt && ($joinDt->format('Y-m') === Carbon::parse($firstDayOfMonth)->format('Y-m'));
                    $joinDayOfMonth = $isJoiningMonth ? (int)$joinDt->format('j') : 1;

                    for ($d = 1; $d <= $cutoffDay; $d++) {
                        $dayDt = Carbon::parse($firstDayOfMonth)->copy()->addDays($d - 1);
                        if ($isJoiningMonth && $d < $joinDayOfMonth) continue;
                        if ($dayDt->isWeekend()) continue;
                        $col = 'att_' . $d;
                        $val = isset($attRecord->$col) && $attRecord->$col !== null ? trim($attRecord->$col) : '';
                        if ($val === '' || $val === 'Q') {
                            $emptyDayNumbers[] = $d;
                        }
                    }

                    if (!empty($emptyDayNumbers)) {
                        $currMonthName = Carbon::parse($salMonthDate)->format('M');
                        $unmarkedDetails[] = $this->formatDayRanges($emptyDayNumbers, $currMonthName);
                        $totalEmptyCount += count($emptyDayNumbers);
                    }
                } else {
                    $totalEmptyCount += 25;
                    $unmarkedDetails[] = "01-25 " . Carbon::parse($salMonthDate)->format('M');
                }
            }

            // AUTOMATIC MODE RESOLUTION:
            if ($isExpiredClearance) {
                // Expired contract with pending arrears => Terminal Clearance
                if (round($netArrearsDue, 0) <= 0) {
                    continue;
                }
                $isArrearsRun = true;
                $candRemarks2 = 'CLEARANCE';
            } elseif ($totalEmptyCount > 0) {
                // Attendance is incomplete (before 25th):
                // If they have positive arrears => automatically qualify for Arrears-Only!
                if (round($netArrearsDue, 0) > 0) {
                    $arrearsReqOpen = DB::table('hr.salreqs')
                        ->where('srq_emp_id', $emp->emp_id)
                        ->where('srq_month', $salMonthDate)
                        ->whereIn('srq_status', ['Draft', 'In Process'])
                        ->whereIn('srq_remarks2', ['ARREARS_ONLY', 'CLEARANCE'])
                        ->exists();

                    if ($arrearsReqOpen) {
                        $excluded[] = [
                            'employee' => $emp,
                            'reason'   => 'Already Generated (Arrears Requisition Open)',
                        ];
                        continue;
                    }

                    $isArrearsRun = true;
                    $candRemarks2 = 'ARREARS_ONLY';
                } else {
                    // No arrears to pay early, and attendance incomplete => Excluded
                    $dateRanges = implode(', ', $unmarkedDetails);
                    $dayWord = $totalEmptyCount === 1 ? 'day' : 'days';
                    $excluded[] = [
                        'employee' => $emp,
                        'reason'   => "Attendance Incomplete ({$totalEmptyCount} {$dayWord} unmarked: {$dateRanges})",
                    ];
                    continue;
                }
            } else {
                // Attendance is complete (25th reached!) => Regular Monthly Payroll
                $isArrearsRun = false;
                $candRemarks2 = null;
            }

            $totalNetSalary = 0;
            $breakdown = [];

            foreach ($groupRows as $idx => $g) {
                if ($isArrearsRun) {
                    $gross = 0.0;
                    $underwork = 0.0;
                    $overwork = 0.0;
                    $award = 0.0;
                    $penalty = 0.0;
                    $withheld = 0.0;
                    $loaned = 0.0;
                    $arrears = ($idx === 8) ? $netArrearsDue : 0.0;
                    $dueDeduction = 0.0;
                    $paidAlr = 0.0;
                    $inProc = 0.0;
                    $net = $arrears;
                    $finalSal = $net;
                    $candRemarks2 = $isExpiredClearance ? 'CLEARANCE' : 'ARREARS_ONLY';
                } else {
                    $gross = (float) round($g['prorated_salary'], 0);
                    $underwork = (float) $g['underwork'];
                    $overwork = 0.0;
                    $award = 0.0;
                    $penalty = 0.0;
                    $withheld = 0.0;
                    $loaned = 0.0;

                    $arrears = ($idx === 8 && $netArrearsDue > 0) ? $netArrearsDue : 0.0;
                    $dueDeduction = ($idx === 8 && $dues < 0) ? abs($dues) : 0.0;
                    $paidAlr = ($idx === 8) ? $paidAlready : 0.0;
                    $inProc = ($idx === 8) ? $inProcess : 0.0;

                    $net = $gross + $overwork - $underwork + $award - $penalty + $arrears - $dueDeduction - $paidAlr - $inProc;
                    $finalSal = $net - $withheld + $loaned;
                    $candRemarks2 = null;
                }

                $totalNetSalary += $finalSal;

                // Bank formatting
                $bankDetail = '(Pay by Cheque)';
                $bankTitle = strtoupper($emp->emp_name);

                if ($selectedBank) {
                    if (!empty($selectedBank->bac_acctitle)) {
                        $bankTitle = trim($selectedBank->bac_acctitle);
                    }
                    $accNum = trim((string)($selectedBank->bac_accnum ?? ''));
                    $bchCode = trim((string)($selectedBank->bac_bchcode ?? ''));

                    if (!empty($accNum)) {
                        if (!empty($bchCode)) {
                            $bankDetail = "{$accNum} ({$bchCode})";
                        } else {
                            $bankDetail = $accNum;
                        }
                    }
                }

                // Head parsing
                $headParts = explode(' ', trim((string)$g['effhed_id']), 2);
                $effHedId = (int) ($headParts[0] ?? 0);
                $sudoHed = $headParts[1] ?? null;

                if (empty($sudoHed) && ((int)($emp->emp_unt_id ?? 0) >= 800000 || in_array((int)($emp->emp_unt_id ?? 0), [800000, 810000, 820000, 840000, 860000, 880000], true))) {
                    $sudoHed = 'CHRF';
                }

                $effUntId = (int) DB::table('cen.heads')->where('hed_id', $effHedId)->value('hed_unt_id');

                $forHedId = !empty($g['head_id']) ? (int)$g['head_id'] : ($effHedId ?: (int)$emp->emp_hed_id);
                $objection = $this->checkObjectionsOnExp($effHedId, $forHedId, $sudoHed, $finalSal);

                $ctrGrade = !empty($g['ctr_id']) 
                    ? (DB::table('hr.contracts')->where('ctr_id', (int)$g['ctr_id'])->value('ctr_grade') ?: $emp->emp_grade)
                    : $emp->emp_grade;

                $ctrRow = !empty($g['ctr_id']) ? DB::table('hr.contracts')->where('ctr_id', (int)$g['ctr_id'])->first() : null;
                $isProbation = false;
                $probSal = null;
                if ($ctrRow && !empty($ctrRow->ctr_prob) && !empty($ctrRow->ctr_startdt)) {
                    $probMonths = (int)$ctrRow->ctr_prob;
                    $probEnd = Carbon::parse($ctrRow->ctr_startdt)->addMonths($probMonths)->subDay()->toDateString();
                    if ($salMonthDate <= $probEnd || (Carbon::parse($salMonthDate)->startOfMonth()->toDateString() <= $probEnd)) {
                        $isProbation = true;
                        $probSal = !empty($ctrRow->ctr_probsal) ? (float)$ctrRow->ctr_probsal : null;
                    }
                }

                $breakdown[] = [
                    'group_idx'       => $idx,
                    'ctr_id'          => $g['ctr_id'],
                    'grade'           => $ctrGrade ?: '-',
                    'start'           => $g['start'],
                    'end'             => $g['end'],
                    'salary'          => (int) round($finalSal, 0),
                    'ctrsalary'       => (int) round($g['salary'], 0),
                    'grosalary'       => (int) round($gross, 0),
                    'netsalary'       => (int) round($net, 0),
                    'is_probation'    => $isProbation,
                    'probsalary'      => $probSal,
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
                    'hed_id'          => !empty($g['head_id']) ? (int)$g['head_id'] : ($effHedId ?: $emp->emp_hed_id),
                    'empnamecomp'     => $emp->emp_name,
                    'bnkaccdetail'    => $bankDetail,
                    'bnkacctitle'     => $bankTitle,
                    'absent'          => $g['absent'],
                    'unpaid'          => $g['unpaid'],
                    'budget_objection'=> $objection,
                    'remarks2'        => $candRemarks2,
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

            $candObjections = array_filter(array_column($breakdown, 'budget_objection'));

            $included[] = [
                'employee'         => $emp,
                'total_salary'     => (int) round($totalNetSalary, 0),
                'breakdown'        => $breakdown,
                'budget_objection' => !empty($candObjections) ? implode('; ', $candObjections) : null,
                'matrix_summary'   => $matrix[11] ?? null,
                'generation_type'  => $isExpiredClearance ? 'clearance' : ($isArrearsRun ? 'arrears_only' : 'regular'),
                'is_arrears_only'  => $isArrearsRun,
            ];
        }

        $budgetWarningsCount = count(array_filter($included, fn($x) => !empty($x['budget_objection'])));

        return [
            'month'     => $salMonthDate,
            'included'  => $included,
            'excluded'  => $excluded,
            'counts'    => [
                'total_candidates' => count($candidates),
                'eligible'         => count($included),
                'excluded'         => count($excluded),
                'budget_warnings'  => $budgetWarningsCount,
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
        $userArea = strtolower(trim((string)($user->acc_untarea ?? '')));
        $isCentralUser = in_array($userArea, ['hr', 'fin', 'adm', 'admin', 'is', 'it', 'hqs', 'rdw', 'nrdi'], true) || ((int)($user->acc_unt_id ?? 0) >= 800000);
        $isMultiple = ($user->acc_access ?? '') === 'multiple' || $isCentralUser;
        $userLower = $isCentralUser ? 100000 : ($isMultiple ? (int)($user->acc_lowerm ?? 100000) : (int)($user->acc_lowers ?? 100000));
        $userUpper = $isCentralUser ? 999999 : ($isMultiple ? (int)($user->acc_upperm ?? 999999) : (int)($user->acc_uppers ?? 999999));

        if (!$isCentralUser) {
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
                $isSplit = count($breakdowns) > 1;
                $parentSrqId = null;

                foreach ($breakdowns as $bIdx => $b) {
                    $remarks = $this->buildRemarksString($b);

                    $srqParent = null;
                    if ($isSplit) {
                        $srqParent = ($bIdx === 0) ? 0 : $parentSrqId;
                    }

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
                        'srq_remarks2'     => $b['remarks2'] ?? null,
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
                        'srq_parent'       => $srqParent,
                    ]);

                    if ($isSplit && $bIdx === 0) {
                        $parentSrqId = $req->srq_id;
                    }

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

    protected array $headStatusCache = [];

    /**
     * Evaluate AnyObjectionsOnExp (Legacy AccountChecks.bas:5).
     * Checks whether the proposed expenditure exceeds the budget limits of the project or effective head / CSRF.
     *
     * @param int $fromHeadId Effective spending head (srq_effhed_id)
     * @param int|null $forHeadId Project head (srq_hed_id)
     * @param string|null $sudoHead Sudohead indicator ('CHRF' or null)
     * @param float $amount Amount to spend (salary)
     * @return string|null Returns objection message if exceeded, or null if budget is sufficient
     */
    public function checkObjectionsOnExp(int $fromHeadId, ?int $forHeadId, ?string $sudoHead, float $amount): ?string
    {
        $objections = [];

        // 1. Check For Head (Project Budget Limit)
        if (empty($sudoHead) && !empty($forHeadId) && (int)$forHeadId > 0) {
            $forHeadId = (int)$forHeadId;
            if (!isset($this->headStatusCache[$forHeadId])) {
                $this->headStatusCache[$forHeadId] = $this->fis->getHeadStatus($forHeadId);
            }
            $forStatus = $this->headStatusCache[$forHeadId];
            $prjCanBeSpent = (float)($forStatus->prj_can_be_spent ?? 0);
            if ($amount > $prjCanBeSpent) {
                $over = round($amount - $prjCanBeSpent, 2);
                $headCode = $forStatus->head_code ?? ('Head #' . $forHeadId);
                $objections[] = "Rs. " . number_format($over) . " above max spent limit for the project ({$headCode})";
            }
        }

        // 2. Check From Head (Effective Head Budget Limit: PCC or CSRF)
        if (!empty($fromHeadId) && (int)$fromHeadId > 0) {
            $fromHeadId = (int)$fromHeadId;
            if (!isset($this->headStatusCache[$fromHeadId])) {
                $this->headStatusCache[$fromHeadId] = $this->fis->getHeadStatus($fromHeadId);
            }
            $fromStatus = $this->headStatusCache[$fromHeadId];
            $headCode = $fromStatus->head_code ?? ('Head #' . $fromHeadId);

            if (!empty($sudoHead)) {
                // CSRF / CHRF
                $cfCanBeSpent = (float)($fromStatus->cf_can_be_spent ?? 0);
                if ($amount > $cfCanBeSpent) {
                    $over = round($amount - $cfCanBeSpent, 2);
                    $objections[] = "Rs. " . number_format($over) . " above max spent limit from CSRF ({$headCode})";
                }
            } else {
                // PCC / Project Account
                $pccCanBeSpent = (float)($fromStatus->pcc_can_be_spent ?? 0);
                if ($amount > $pccCanBeSpent) {
                    $over = round($amount - $pccCanBeSpent, 2);
                    $objections[] = "Rs. " . number_format($over) . " above max spent limit from the project ({$headCode})";
                }
            }
        }

        return !empty($objections) ? implode(" | ", $objections) : null;
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
        $rootReq = HrSalReq::findOrFail($srqId);
        $targetId = ($rootReq->srq_parent && $rootReq->srq_parent > 0) ? (int)$rootReq->srq_parent : (int)$rootReq->srq_id;

        return DB::transaction(function () use ($targetId) {
            $reqs = HrSalReq::where(function ($q) use ($targetId) {
                $q->where('srq_id', $targetId)
                  ->orWhere('srq_parent', $targetId);
            })->orderBy('srq_parent', 'asc')->orderBy('srq_id', 'asc')->get();

            if ($reqs->isEmpty()) {
                throw new \InvalidArgumentException("Salary requisition {$targetId} not found.");
            }

            $createdOrders = [];
            $parentSorId = null;

            // Check if master parent order already exists in Draft
            $existingParentOrder = FinSalOrder::where('sor_srq_id', $targetId)
                ->where('sor_status', 'Draft')
                ->first();
            if ($existingParentOrder) {
                $parentSorId = $existingParentOrder->sor_id;
            }

            foreach ($reqs as $rf) {
                // If a draft order already exists for this requisition, reuse it to prevent duplicates
                $existingOrder = FinSalOrder::where('sor_srq_id', $rf->srq_id)
                    ->where('sor_status', 'Draft')
                    ->first();
                if ($existingOrder) {
                    if ($rf->srq_parent === 0 || $rf->srq_parent === null) {
                        $parentSorId = $existingOrder->sor_id;
                    }
                    $createdOrders[] = $existingOrder;
                    continue;
                }

                $head = DB::table('cen.heads')->where('hed_id', $rf->srq_effhed_id)->first();
                $transType = $head ? ($head->hed_transtype ?? 1) : 1;
                $noLoan = (is_null($rf->srq_hed_id) || $rf->srq_sudohed === 'CHRF') ? true : false;

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
                    'sor_remarks2'     => $rf->srq_remarks2,
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

                // Subhead ratio strictly for project employees (never for CSRF/CHRF salaries)
                if (!is_null($rf->srq_hed_id) && $rf->srq_sudohed !== 'CHRF') {
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
    public function cancelOrder(int $sorId, ?string $reason = null): array
    {
        $initialOrder = FinSalOrder::findOrFail($sorId);

        if ($initialOrder->sor_parent && $initialOrder->sor_parent > 0) {
            abort(422, 'This requisition cannot be returned directly. Please return the parent requisition.');
        }

        $targetId = (int) $initialOrder->sor_id;

        return DB::transaction(function () use ($targetId, $reason) {
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
                    abort(422, "Cannot return salary order group: Cannot return a fulfilled salary order (order #{$order->sor_id} is already fulfilled).");
                }

                $paidCommitment = FinCommitment::where('cmt_docid', $order->sor_id)
                    ->where('cmt_type', 'Sa')
                    ->where('cmt_status', 'Paid')
                    ->first();

                if ($paidCommitment) {
                    abort(422, "Cannot return salary order group: Cannot return an order whose commitment has already been paid (commitment #{$paidCommitment->cmt_id} for order #{$order->sor_id} is paid).");
                }
            }

            $cancelledCommitments = [];

            foreach ($orders as $order) {
                // 1. Revert requisition back to division in Draft status
                // This clears it from fin.docs_ipc (In-Process) and restores funds to Remaining balance
                if ($order->sor_srq_id) {
                    HrSalReq::where(function($q) use ($order) {
                        $q->where('srq_id', $order->sor_srq_id)
                          ->orWhere('srq_parent', $order->sor_srq_id);
                    })->update([
                        'srq_status'     => 'Draft',
                        'srq_fulfilment' => null,
                        'srq_releasedtg' => null,
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

        if (!empty($b['remarks2'])) {
            if ($b['remarks2'] === 'ARREARS_ONLY') {
                $remarks[] = "Arrears Settlement: Past Unpaid Period (Current month regular salary excluded).";
            } elseif ($b['remarks2'] === 'CLEARANCE') {
                $remarks[] = "Terminal Arrears Clearance: Contract Ended.";
            }
        }

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
        if (!empty($b['is_probation'])) {
            $probSalPart = !empty($b['probsalary']) ? " (Rs. " . number_format($b['probsalary']) . ")" : "";
            $remarks[] = "Probation Period{$probSalPart}.";
        }

        return trim(implode(' ', $remarks));
    }

    /**
     * Get paginated salary requisitions scoped to user's unit bounds.
     * Supports exact srq_status and month filters.
     */
    public function getRequisitions(Authenticatable $user, ?string $month = null, ?string $status = null, int $perPage = 25)
    {
        $isMyDept = method_exists($user, 'isMyDepartmentMode') && $user->isMyDepartmentMode();
        $isMultiple = (($user->acc_access ?? '') === 'multiple' || strtolower(trim((string)($user->acc_untarea ?? ''))) === 'fin') && !$isMyDept;
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
    public function getOrders(Authenticatable $user, ?string $month = null, ?string $status = null, int $perPage = 25, ?string $search = null, array $colFilters = [])
    {
        $isMyDept = method_exists($user, 'isMyDepartmentMode') && $user->isMyDepartmentMode();
        $isMultiple = (($user->acc_access ?? '') === 'multiple' || strtolower(trim((string)($user->acc_untarea ?? ''))) === 'fin') && !$isMyDept;
        $lower = $isMultiple ? ($user->acc_lowerm ?? 100000) : ($user->acc_lowers ?? 100000);
        $upper = $isMultiple ? ($user->acc_upperm ?? 999999) : ($user->acc_uppers ?? 999999);

        $query = FinSalOrder::where(function ($q) use ($lower, $upper) {
            $q->whereBetween('sor_unt_id', [$lower, $upper])
              ->orWhereBetween('sor_effunt_id', [$lower, $upper])
              ->orWhereExists(function ($sub) use ($lower, $upper) {
                  $sub->select(DB::raw(1))
                      ->from('fin.salorders as p')
                      ->whereColumn('p.sor_id', 'fin.salorders.sor_parent')
                      ->where(function ($pq) use ($lower, $upper) {
                          $pq->whereBetween('p.sor_unt_id', [$lower, $upper])
                            ->orWhereBetween('p.sor_effunt_id', [$lower, $upper]);
                      });
              })
              ->orWhereExists(function ($sub) use ($lower, $upper) {
                  $sub->select(DB::raw(1))
                      ->from('fin.salorders as c')
                      ->whereColumn('c.sor_parent', 'fin.salorders.sor_id')
                      ->where(function ($cq) use ($lower, $upper) {
                          $cq->whereBetween('c.sor_unt_id', [$lower, $upper])
                            ->orWhereBetween('c.sor_effunt_id', [$lower, $upper]);
                      });
              });
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

        // Global Search across all relevant columns and relationships
        if (!empty($search)) {
            $s = trim($search);
            $query->where(function ($q) use ($s) {
                if (is_numeric($s)) {
                    $q->where('sor_id', (int)$s);
                }
                $q->orWhere('sor_emp_id', 'ilike', "%{$s}%")
                  ->orWhere('sor_empnamecomp', 'ilike', "%{$s}%")
                  ->orWhere('sor_bnkacctitle', 'ilike', "%{$s}%")
                  ->orWhere('sor_bnkaccdetail', 'ilike', "%{$s}%")
                  ->orWhere('sor_remarks', 'ilike', "%{$s}%")
                  ->orWhere('sor_remarks2', 'ilike', "%{$s}%")
                  ->orWhereHas('employee', function ($eq) use ($s) {
                      $eq->where('emp_name', 'ilike', "%{$s}%");
                  })
                  ->orWhereHas('head', function ($hq) use ($s) {
                      $hq->where('hed_code', 'ilike', "%{$s}%")
                         ->orWhere('hed_name', 'ilike', "%{$s}%");
                  })
                  ->orWhereHas('effectiveHead', function ($ehq) use ($s) {
                      $ehq->where('hed_code', 'ilike', "%{$s}%")
                          ->orWhere('hed_name', 'ilike', "%{$s}%");
                  })
                  ->orWhereHas('unit', function ($uq) use ($s) {
                      $uq->where('unt_name', 'ilike', "%{$s}%")
                         ->orWhere('unt_namesh', 'ilike', "%{$s}%");
                  });
            });
        }

        // Column-level Server-side Filters
        if (!empty($colFilters['col_order'])) {
            $val = trim($colFilters['col_order']);
            $query->where(function ($q) use ($val) {
                if (is_numeric($val)) {
                    $q->where('sor_id', (int)$val);
                } else {
                    $q->whereRaw("CAST(sor_id AS TEXT) ILIKE ?", ["%{$val}%"]);
                }
            });
        }
        if (!empty($colFilters['col_emp'])) {
            $val = trim($colFilters['col_emp']);
            $query->where(function ($q) use ($val) {
                $q->where('sor_emp_id', 'ilike', "%{$val}%")
                  ->orWhere('sor_empnamecomp', 'ilike', "%{$val}%")
                  ->orWhereHas('employee', function ($eq) use ($val) {
                      $eq->where('emp_name', 'ilike', "%{$val}%");
                  });
            });
        }
        if (!empty($colFilters['col_dept'])) {
            $val = trim($colFilters['col_dept']);
            $query->where(function ($q) use ($val) {
                $q->whereHas('head', function ($hq) use ($val) {
                    $hq->where('hed_code', 'ilike', "%{$val}%")->orWhere('hed_name', 'ilike', "%{$val}%");
                })->orWhereHas('effectiveHead', function ($ehq) use ($val) {
                    $ehq->where('hed_code', 'ilike', "%{$val}%")->orWhere('hed_name', 'ilike', "%{$val}%");
                })->orWhereHas('unit', function ($uq) use ($val) {
                    $uq->where('unt_namesh', 'ilike', "%{$val}%")->orWhere('unt_name', 'ilike', "%{$val}%");
                });
            });
        }
        if (!empty($colFilters['col_cont'])) {
            $val = str_replace([',', ' '], '', trim($colFilters['col_cont']));
            if (is_numeric($val)) {
                $query->where(function ($q) use ($val) {
                    $q->where('sor_ctrsalary', (float)$val)->orWhere('sor_salary', (float)$val);
                });
            }
        }
        if (!empty($colFilters['col_calc'])) {
            $val = str_replace([',', ' '], '', trim($colFilters['col_calc']));
            if (is_numeric($val)) {
                $query->where('sor_netsalary', (float)$val);
            }
        }
        if (!empty($colFilters['col_payable'])) {
            $val = str_replace([',', ' '], '', trim($colFilters['col_payable']));
            if (is_numeric($val)) {
                $query->where('sor_salary', (float)$val);
            }
        }
        if (!empty($colFilters['col_account'])) {
            $val = trim($colFilters['col_account']);
            $query->where(function ($q) use ($val) {
                $q->where('sor_bnkaccdetail', 'ilike', "%{$val}%")
                  ->orWhere('sor_bnkacctitle', 'ilike', "%{$val}%");
            });
        }
        if (!empty($colFilters['col_remarks'])) {
            $val = trim($colFilters['col_remarks']);
            $query->where('sor_remarks', 'ilike', "%{$val}%");
        }
        if (!empty($colFilters['col_remarks2'])) {
            $val = trim($colFilters['col_remarks2']);
            $query->where('sor_remarks2', 'ilike', "%{$val}%");
        }
        if (!empty($colFilters['col_status'])) {
            $val = trim($colFilters['col_status']);
            $query->where('sor_status', 'ilike', "%{$val}%");
        }
        if (!empty($colFilters['col_cmt'])) {
            $val = trim($colFilters['col_cmt']);
            $query->whereHas('commitment', function ($cq) use ($val) {
                $cq->where('cmt_status', 'ilike', "%{$val}%");
            });
        }

        // Sorting: Group split orders of the same employee together consecutively
        return $query->orderBy('sor_month', 'desc')
                     ->orderBy('sor_emp_id', 'asc')
                     ->orderByRaw("COALESCE(NULLIF(sor_parent, 0), sor_id) ASC")
                     ->orderBy('sor_parent', 'asc')
                     ->orderBy('sor_id', 'asc')
                     ->paginate($perPage);
    }

    /**
     * Get remaining active employees who do NOT have a Draft salary order in Finance for the month.
     * STRICT REQUIREMENT: Exclude Released and Terminated employees (emp_status must be 'Active').
     */
    public function getRemainingEmployees(string $month, Authenticatable $user): array
    {
        $isMyDept = method_exists($user, 'isMyDepartmentMode') && $user->isMyDepartmentMode();
        $isMultiple = (($user->acc_access ?? '') === 'multiple' || strtolower(trim((string)($user->acc_untarea ?? ''))) === 'fin') && !$isMyDept;
        $lower = $isMultiple ? ($user->acc_lowerm ?? 100000) : ($user->acc_lowers ?? 100000);
        $upper = $isMultiple ? ($user->acc_upperm ?? 999999) : ($user->acc_uppers ?? 999999);

        $monthDate = Carbon::parse($month)->endOfMonth()->toDateString();
        $mStart = Carbon::parse($month)->startOfMonth()->toDateString();
        $cutoffDate = Carbon::parse($month)->startOfMonth()->addDays(24)->toDateString(); // 25th of month

        // 1. Strictly ACTIVE employees only (never Released or Terminated)
        $activeEmployees = DB::table('hr.emps as e')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'e.emp_unt_id')
            ->whereRaw("LOWER(TRIM(e.emp_status)) = 'active'")
            ->whereBetween('e.emp_unt_id', [$lower, $upper])
            ->select('e.emp_id', 'e.emp_name', 'e.emp_unt_id', 'u.unt_name', 'u.unt_namesh')
            ->orderBy('e.emp_name')
            ->get();

        // 2. Fetch existing Salary Orders for this month in Finance
        $orders = FinSalOrder::where('sor_month', $monthDate)
            ->where(function ($q) use ($lower, $upper) {
                $q->whereBetween('sor_unt_id', [$lower, $upper])
                  ->orWhereBetween('sor_effunt_id', [$lower, $upper]);
            })
            ->get()
            ->groupBy('sor_emp_id');

        // 3. Fetch existing Requisitions for this month at Division
        $reqs = HrSalReq::where('srq_month', $monthDate)
            ->where(function ($q) use ($lower, $upper) {
                $q->whereBetween('srq_unt_id', [$lower, $upper])
                  ->orWhereBetween('srq_effunt_id', [$lower, $upper]);
            })
            ->get()
            ->groupBy('srq_emp_id');

        $remaining = [];

        foreach ($activeEmployees as $emp) {
            $empOrders = $orders->get($emp->emp_id);
            $hasDraftOrder = false;

            if ($empOrders && $empOrders->isNotEmpty()) {
                foreach ($empOrders as $ord) {
                    if ($ord->sor_status === 'Draft') {
                        $hasDraftOrder = true;
                        break;
                    }
                }
            }

            // If employee already has a Draft order in Finance, they are NOT remaining!
            if ($hasDraftOrder) {
                continue;
            }

            // Determine specific reason/status for this active employee
            $reason = 'Requisition pending generation at Division HR';
            $category = 'Pending Generation';
            $badgeClass = 'badge-secondary';

            $firstOrder = $empOrders ? $empOrders->first() : null;
            $empReqs = $reqs->get($emp->emp_id);
            $firstReq = $empReqs ? $empReqs->first() : null;

            if ($firstOrder) {
                if ($firstOrder->sor_status === 'Approved') {
                    $category = 'Already Approved';
                    $reason = "Order #{$firstOrder->sor_id} Approved in Finance (Open Tab)";
                    $badgeClass = 'badge-info';
                } elseif ($firstOrder->sor_status === 'Fulfilled') {
                    $category = 'Already Fulfilled';
                    $reason = "Order #{$firstOrder->sor_id} Fulfilled / Paid (Closed Tab)";
                    $badgeClass = 'badge-success';
                } elseif ($firstOrder->sor_status === 'Cancelled') {
                    $category = 'Cancelled in Finance';
                    $reason = "Order #{$firstOrder->sor_id} Cancelled in Finance";
                    $badgeClass = 'badge-danger';
                }
            } elseif ($firstReq) {
                if ($firstReq->srq_status === 'Draft') {
                    $category = 'Pending Division Release';
                    $reason = "Requisition #{$firstReq->srq_id} Draft at Division HR (Not Released yet)";
                    $badgeClass = 'badge-warning text-dark';
                } elseif ($firstReq->srq_status === 'In Process') {
                    $category = 'Orders Pending Creation';
                    $reason = "Requisition #{$firstReq->srq_id} In Process (Finance orders not created yet)";
                    $badgeClass = 'badge-primary';
                } elseif ($firstReq->srq_status === 'Cancelled') {
                    $category = 'Requisition Cancelled';
                    $reason = "Requisition #{$firstReq->srq_id} Cancelled by Division HR";
                    $badgeClass = 'badge-dark';
                }
            } else {
                // No requisition generated yet; check contract expiry, attendance, and head status
                $contract = DB::table('hr.contracts')
                    ->where('ctr_num', $emp->emp_id)
                    ->orderBy('ctr_enddt', 'desc')
                    ->first();

                if (!$contract) {
                    $category = 'No Contract';
                    $reason = 'No active service contract found';
                    $badgeClass = 'badge-danger';
                } elseif (!empty($contract->ctr_enddt) && Carbon::parse($contract->ctr_enddt)->lt(Carbon::parse($mStart))) {
                    $category = 'Contract Expired';
                    $reason = 'Contract expired on ' . Carbon::parse($contract->ctr_enddt)->format('d M Y');
                    $badgeClass = 'badge-danger';
                } else {
                    // Check attendance marked between 1st and 25th
                    $attRecord = DB::table('hr.attendance')
                        ->where('att_emp_id', $emp->emp_id)
                        ->where('att_startdt', $mStart)
                        ->first();

                    $unmarked = 0;
                    if (!$attRecord) {
                        $unmarked = 25;
                    } else {
                        for ($d = 1; $d <= 25; $d++) {
                            $col = 'att_' . $d;
                            if (empty($attRecord->$col)) {
                                $unmarked++;
                            }
                        }
                    }

                    if ($unmarked > 0) {
                        $category = 'Attendance Incomplete';
                        $reason = "Attendance incomplete ({$unmarked} unmarked days between 1st & 25th)";
                        $badgeClass = 'badge-warning text-dark';
                    } else {
                        // Check salary head
                        $hasHead = DB::table('fin.empeffheads')->where('eeh_emp_id', $emp->emp_id)->exists()
                            || ($contract && !empty($contract->ctr_hed_id));
                        if (!$hasHead) {
                            $category = 'Head Not Set';
                            $reason = 'Project Salary Head not assigned';
                            $badgeClass = 'badge-dark';
                        }
                    }
                }
            }

            $remaining[] = [
                'emp_id'      => $emp->emp_id,
                'name'        => $emp->emp_name,
                'designation' => $emp->emp_desig ?? 'Staff',
                'division'    => $emp->unt_namesh ?? ($emp->unt_name ?? 'Division'),
                'category'    => $category,
                'reason'      => $reason,
                'badge_class' => $badgeClass,
            ];
        }

        return $remaining;
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

    /**
     * Split salary requisition across heads (Legacy hr_salreq_split.bas).
     *
     * @param int $srqId
     * @param float $parentSalary Adjusted salary for the parent requisition
     * @param array $splits Array of ['effhed_id' => int, 'salary' => float]
     * @param Authenticatable $user
     * @return array
     */
    public function splitRequisition(int $srqId, float $parentSalary, array $splits, Authenticatable $user): array
    {
        $parentReq = HrSalReq::findOrFail($srqId);

        if ($parentReq->srq_status !== 'Draft') {
            abort(422, "Cannot split requisition #{$srqId} with status '{$parentReq->srq_status}'. Only 'Draft' requisitions can be split.");
        }

        if ($parentReq->srq_parent !== null && (int)$parentReq->srq_parent > 0) {
            abort(422, "Cannot split a child requisition directly. Only parent requisitions can be split.");
        }

        if (empty($splits)) {
            abort(422, "At least one split head and amount is required.");
        }

        $totalOriginal = (float)$parentReq->srq_salary;
        $splitSum = 0.0;
        foreach ($splits as $s) {
            $amt = (float)($s['salary'] ?? 0);
            if ($amt <= 0) {
                abort(422, "Each split amount must be greater than zero.");
            }
            if (empty($s['effhed_id'])) {
                abort(422, "Head selection is mandatory for each split row.");
            }
            $splitSum += $amt;
        }

        if ($parentSalary <= 0) {
            abort(422, "Remaining amount for the parent requisition must be greater than zero.");
        }

        if (abs(($parentSalary + $splitSum) - $totalOriginal) > 0.01) {
            abort(422, "The sum of parent amount and split portions must equal the original payable amount (" . number_format($totalOriginal, 2) . ").");
        }

        $isCentral = ($parentReq->srq_unt_id >= 800000 || in_array((int)$parentReq->srq_unt_id, [800000, 810000, 820000, 840000, 860000, 880000], true));
        $finService = app(\App\Services\FinancialIntelligenceService::class);

        // Pre-validate heads, CSRF eligibility, and available balances
        foreach ($splits as $s) {
            $headId = (int)$s['effhed_id'];
            $amt = (float)($s['salary'] ?? 0);
            $head = CenHead::find($headId);
            if (!$head) {
                abort(422, "Invalid project/head selected (ID: {$headId}).");
            }

            if ($isCentral) {
                $hasCsrf = DB::table('fin.sharesalloc')->where('sha_hed_id', $headId)->where('sha_cf', '>', 0)->exists();
                if (!$hasCsrf) {
                    abort(422, "Central Department employee salaries can only be split into CSRF-funded heads. Head [{$head->hed_code}] does not have CSRF allocation.");
                }
            }

            $headStatus = $finService->getHeadStatus($headId);
            $avail = $isCentral 
                ? (float)($headStatus->cf_available ?? ($headStatus['cf_available'] ?? 0)) 
                : (float)($headStatus->pcc_available ?? ($headStatus->available ?? ($headStatus['pcc_available'] ?? ($headStatus['available'] ?? 0))));

            if ($amt > $avail) {
                abort(422, "Insufficient funds in head [{$head->hed_code}]. Available balance is Rs. " . number_format($avail, 2) . ", but requested split is Rs. " . number_format($amt, 2) . ".");
            }
        }

        return DB::transaction(function () use ($parentReq, $parentSalary, $splits, $isCentral) {
            // 1. Update Parent Requisition
            $parentReq->srq_salary = (int)round($parentSalary);
            $parentReq->srq_parent = 0; // Legacy convention: 0 indicates parent of split
            $existingRemarks = trim((string)$parentReq->srq_remarks);
            $splitRemark = $isCentral ? 'Split Salary. (CSRF)' : 'Split Salary.';
            $parentReq->srq_remarks = $existingRemarks !== '' 
                ? (str_contains($existingRemarks, 'Split Salary.') ? $existingRemarks : $existingRemarks . ' ' . $splitRemark)
                : $splitRemark;
            $parentReq->save();

            // 2. Insert Child Requisitions
            $createdChildren = [];
            foreach ($splits as $s) {
                $headId = (int)$s['effhed_id'];
                $childAmt = (int)round((float)$s['salary']);

                $head = CenHead::find($headId);
                $effUnitId = $head ? ($head->hed_unt_id ?: 160000) : 160000;
                $hedId = !empty($parentReq->srq_hed_id) ? $headId : null;

                $child = HrSalReq::create([
                    'srq_emp_id'       => $parentReq->srq_emp_id,
                    'srq_unt_id'       => $parentReq->srq_unt_id,
                    'srq_hed_id'       => $hedId,
                    'srq_effhed_id'    => $headId,
                    'srq_effunt_id'    => $effUnitId,
                    'srq_month'        => $parentReq->srq_month,
                    'srq_status'       => 'Draft',
                    'srq_salary'       => $childAmt,
                    'srq_empnamecomp'  => $parentReq->srq_empnamecomp,
                    'srq_ctrsalary'    => $parentReq->srq_ctrsalary,
                    'srq_grosalary'    => $parentReq->srq_grosalary,
                    'srq_netsalary'    => $parentReq->srq_netsalary,
                    'srq_contracts'    => $parentReq->srq_contracts,
                    'srq_bnkaccdetail' => $parentReq->srq_bnkaccdetail,
                    'srq_bnkacctitle'  => $parentReq->srq_bnkacctitle,
                    'srq_remarks'      => $isCentral ? 'Split Salary. (CSRF)' : 'Split Salary.',
                    'srq_unpaiddays'   => $parentReq->srq_unpaiddays,
                    'srq_paidholidays' => $parentReq->srq_paidholidays,
                    'srq_underwork'    => $parentReq->srq_underwork,
                    'srq_overwork'     => $parentReq->srq_overwork,
                    'srq_award'        => $parentReq->srq_award,
                    'srq_penalty'      => $parentReq->srq_penalty,
                    'srq_loaned'       => $parentReq->srq_loaned,
                    'srq_withheld'     => $parentReq->srq_withheld,
                    'srq_arrears'      => $parentReq->srq_arrears,
                    'srq_dues'         => $parentReq->srq_dues,
                    'srq_paidalready'  => $parentReq->srq_paidalready,
                    'srq_sudohed'      => $parentReq->srq_sudohed,
                    'srq_fulfilment'   => null,
                    'srq_parent'       => $parentReq->srq_id, // Link to parent
                    'srq_remarks2'     => null,
                ]);

                $createdChildren[] = $child;
            }

            return [
                'parent'   => $parentReq,
                'children' => $createdChildren,
            ];
        });
    }

    /**
     * Format list of day numbers into human-readable date ranges.
     * E.g. [1, 2, 3, 4, 5, 10, 15, 16] -> "1-5 May, 10 May, 15-16 May"
     *
     * @param array<int> $days
     * @param string $monthName
     * @return string
     */
    public function formatDayRanges(array $days, string $monthName): string
    {
        if (empty($days)) {
            return '';
        }
        sort($days);
        $ranges = [];
        $start = $days[0];
        $prev = $days[0];

        $count = count($days);
        for ($i = 1; $i < $count; $i++) {
            if ($days[$i] === $prev + 1) {
                $prev = $days[$i];
            } else {
                $ranges[] = ($start === $prev) ? "{$start} {$monthName}" : "{$start}-{$prev} {$monthName}";
                $start = $days[$i];
                $prev = $days[$i];
            }
        }
        $ranges[] = ($start === $prev) ? "{$start} {$monthName}" : "{$start}-{$prev} {$monthName}";

        return implode(', ', $ranges);
    }
}
