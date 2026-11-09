<?php

namespace App\Services;

use App\Models\SalOrderCase;
use App\Models\SalOrderCaseDecision;
use App\Models\FinSalOrder;
use App\Models\CenAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryCaseService
{
    public function __construct(
        protected FinancialIntelligenceService $financialService
    ) {}

    /**
     * Map a unit ID and head code/name to one of the standard minute divisions:
     * Center, Comm, Enab, NWS, Sensors, SoSE, Sys
     */
    public function getDivisionGroup(?int $untId, ?string $headCode, ?string $headName): string
    {
        $code = strtoupper(trim((string)($headCode ?? '')));
        $name = strtoupper(trim((string)($headName ?? '')));

        if (str_contains($code, 'CSRF') || str_contains($name, 'CSRF') || ($untId !== null && $untId >= 800000)) {
            return 'Center';
        }

        return match((int)$untId) {
            200000 => 'Command, Control and Communication (C3)',
            250000 => 'AI and Immersive Technology Division',
            300000 => 'Under Water Tech Division',
            350000 => 'Sensors and UAV Technology Division',
            400000 => 'Radar and EW Division',
            450000 => 'Ex System Division',
            default => 'Center'
        };
    }

    /**
     * Initiate or retrieve an existing Salary Order Verification Case for a given month (YYYY-MM).
     */
    public function initiateCase(string $month, CenAccount $user): SalOrderCase
    {
        $cleanMonth = substr(trim($month), 0, 7); // YYYY-MM

        // Query ALL unassigned active salary orders (current month AND any prior pending unassigned months)
        $orders = FinSalOrder::where(function($q) use ($cleanMonth) {
                $q->whereRaw("to_char(sor_month, 'YYYY-MM') <= ?", [$cleanMonth]);
            })
            ->whereIn('sor_status', ['Draft', 'Approved', 'Open'])
            ->whereNull('sor_soc_id')
            ->get();

        // Check if there is an existing pending case for this month to open
        $existing = SalOrderCase::where('soc_month', $cleanMonth)
            ->whereNotIn('soc_status', ['Cancelled', 'Approved', 'Closed'])
            ->latest('soc_id')
            ->first();

        if ($existing) {
            return $this->syncCaseOrders($existing);
        }

        if ($orders->isEmpty()) {
            throw new \RuntimeException("No unassigned salary orders found for {$cleanMonth}.");
        }

        $totalPayments = $orders->count();
        $totalAmount = (float) $orders->sum('sor_salary');

        // Total distinct employees being paid
        $paidEmpIds = $orders->pluck('sor_emp_id')->filter()->unique()->values();
        $totalPaidEmps = $paidEmpIds->count();

        // Split salaries: employees having more than 1 salary order in this month
        $empOrderCounts = $orders->groupBy('sor_emp_id')->map->count();
        $splitEmployees = $empOrderCounts->filter(fn($c) => $c > 1);
        $splitSalariesCount = $splitEmployees->count();
        $splitPaymentsCount = $splitEmployees->sum();

        // Total active employees due for salary in this month
        $totalDueEmps = DB::table('hr.emps')
            ->where('emp_status', 'ILIKE', 'Active%')
            ->whereExists(function($q) {
                $q->select(DB::raw(1))
                  ->from('hr.contracts')
                  ->whereColumn('hr.contracts.ctr_num', 'hr.emps.emp_id');
            })
            ->count();

        if ($totalDueEmps < $totalPaidEmps) {
            $totalDueEmps = $totalPaidEmps;
        }

        // Held / Remaining employees
        $heldEmpCount = max(0, $totalDueEmps - $totalPaidEmps);

        // Determine Minute Number
        $caseCount = SalOrderCase::count();
        $minuteNo = "Minute " . (41 + $caseCount);

        // Format Month string (e.g. "Sep 26" or "October 2026")
        $carbonMonth = Carbon::createFromFormat('Y-m', $cleanMonth);
        $monthShort = $carbonMonth->format('M y'); // e.g. "Sep 26"

        // Paragraph 1: Auto Summary
        $para1 = sprintf(
            "1. %d employees are due for salary. Salaries of %d employees are being paid for the month of %s against projects mentioned below. %d salaries are split into %d payments. Salary sheet is placed at Encl 40-A/F.",
            $totalDueEmps,
            $totalPaidEmps,
            $monthShort,
            $splitSalariesCount,
            $splitPaymentsCount
        );

        // Paragraph 2: Held Salaries (Editable by user in Draft)
        $para2HeldSummary = [];
        if ($heldEmpCount > 0) {
            // Find active employees without salary orders in this month, grouped by division
            $heldRows = DB::table('hr.emps as e')
                ->join('cen.units as u', 'u.unt_id', '=', 'e.emp_unt_id')
                ->where('e.emp_status', 'ILIKE', 'Active%')
                ->whereNotIn('e.emp_id', $paidEmpIds->all())
                ->select('u.unt_id', 'u.unt_name', DB::raw('count(*) as cnt'))
                ->groupBy('u.unt_id', 'u.unt_name')
                ->get();

            foreach ($heldRows as $hr) {
                $divShort = match((int)$hr->unt_id) {
                    200000 => 'Command, Control and Communication (C3)',
                    250000 => 'AI and Immersive Technology Div',
                    300000 => 'Under Water Tech Div',
                    350000 => 'Sensors and UAV Technology Div',
                    400000 => 'Radar and EW Div',
                    450000 => 'Ex System Div',
                    default => 'Center'
                };
                $para2HeldSummary[] = sprintf("%02dx employees of %s", $hr->cnt, $divShort);
            }
        }

        $heldTextPart = !empty($para2HeldSummary) 
            ? implode(" and ", $para2HeldSummary) 
            : sprintf("%02dx employees", $heldEmpCount);

        $para2 = $heldEmpCount > 0
            ? sprintf("2. Salaries of %s have been held due to contract expiry / attendance verification.", $heldTextPart)
            : "2. Salaries of all eligible contract employees have been processed in full.";

        // Paragraph 3: Recommendation with Amount in Words
        $amountInWords = $this->numberToWords((int)$totalAmount);
        $para3 = sprintf(
            "3. Foregoing in view, approval of Rs. %s (%s) for salaries of %d employees for month of %s may please be accorded for release of the payment through M/s MTSS.",
            number_format($totalAmount),
            $amountInWords,
            $totalPaidEmps,
            $monthShort
        );

        return DB::transaction(function() use (
            $cleanMonth, $carbonMonth, $minuteNo, $para1, $para2, $para3,
            $totalDueEmps, $totalPaidEmps, $splitSalariesCount, $splitPaymentsCount,
            $totalPayments, $totalAmount, $user, $orders
        ) {
            $case = SalOrderCase::create([
                'soc_month' => $cleanMonth,
                'soc_minute_no' => $minuteNo,
                'soc_title' => "Salary Orders Verification Case - " . $carbonMonth->format('F Y'),
                'soc_encl_ref' => 'Encl 40-A/F',
                'soc_para1_text' => $para1,
                'soc_para2_text' => $para2,
                'soc_para3_text' => $para3,
                'soc_payment_channel' => 'M/s MTSS',
                'soc_total_due_emps' => $totalDueEmps,
                'soc_total_paid_emps' => $totalPaidEmps,
                'soc_split_salaries_count' => $splitSalariesCount,
                'soc_split_payments_count' => $splitPaymentsCount,
                'soc_total_payments' => $totalPayments,
                'soc_total_amount' => $totalAmount,
                'soc_current_stage' => 'Finance',
                'soc_current_acc_id' => $user->acc_id,
                'soc_current_holder' => self::getOfficialDesignation($user),
                'soc_status' => 'Pending Action',
                'soc_created_by' => $user->acc_id,
                'soc_created_name' => self::getOfficialDesignation($user),
                'soc_created_desig' => self::getOfficialDesignation($user),
            ]);

            // Link ONLY these unassigned draft salary orders to this case
            $orderIds = $orders->pluck('sor_id')->filter()->all();
            if (!empty($orderIds)) {
                FinSalOrder::whereIn('sor_id', $orderIds)->update(['sor_soc_id' => $case->soc_id]);
            }

            return $case;
        });
    }

    /**
     * Get structured minute table data grouped by division (Center, Comm, Enab, NWS, Sensors, SoSE, Sys).
     * Center salaries show Project Code + CSRF (e.g. STLT-CSRF) with project's specific CSRF balance.
     * Division salaries show clean Project name without CSRF and pure project balance.
     */
    public function getMinuteTableData(SalOrderCase $case): array
    {
        // If customized table data was previously saved in draft mode, return it
        if (!empty($case->soc_table_data) && is_array($case->soc_table_data)) {
            return $case->soc_table_data;
        }

        $orders = DB::table('fin.salorders as o')
            ->leftJoin('cen.heads as h', 'h.hed_id', '=', DB::raw('COALESCE(o.sor_effhed_id, o.sor_hed_id)'))
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'o.sor_unt_id')
            ->where('o.sor_soc_id', '=', $case->soc_id)
            ->whereNotIn('o.sor_status', ['Cancelled', 'Fulfilled'])
            ->select(
                'o.sor_id',
                'o.sor_emp_id',
                'o.sor_salary',
                'o.sor_unt_id',
                'u.unt_name',
                DB::raw('COALESCE(o.sor_effhed_id, o.sor_hed_id) as head_id'),
                'h.hed_code',
                'h.hed_name'
            )
            ->get();

        $groupOrder = [
            'Center',
            'Command, Control and Communication (C3)',
            'AI and Immersive Technology Division',
            'Under Water Tech Division',
            'Sensors and UAV Technology Division',
            'Radar and EW Division',
            'Ex System Division',
        ];
        $grouped = [];

        foreach ($orders as $o) {
            $group = $this->getDivisionGroup($o->sor_unt_id, $o->hed_code, $o->hed_name);
            $headKey = $o->head_id ?: 0;

            $isCenter = ($group === 'Center') || ((int)($o->sor_unt_id ?? 0) >= 800000 && (int)($o->sor_unt_id ?? 0) < 900000);
            $headCode = trim((string)($o->hed_code ?: $o->hed_name));
            $cleanProjectCode = preg_replace('/\-?CSRF$/i', '', $headCode);
            if (empty($cleanProjectCode)) {
                $cleanProjectCode = $headCode ?: 'Project Head';
            }

            if ($isCenter) {
                // For Center employees, Paid From is Project Code + CSRF (e.g. STLT-CSRF)
                $displayName = $cleanProjectCode . '-CSRF';
            } else {
                // For Divisions, strictly show project head WITHOUT "-CSRF"
                $displayName = $cleanProjectCode;
            }

            if (!isset($grouped[$group][$headKey])) {
                // Get head remaining balance via financialService
                $remainingBalance = 0;
                if ($o->head_id) {
                    try {
                        $hStatus = $this->financialService->getHeadStatus($o->head_id);
                        if ($isCenter) {
                            // CSRF remaining balance for that specific project:
                            $remainingBalance = (float)($hStatus->cf_can_be_spent ?? ($hStatus->cf_available ?? ($hStatus->cf_balance ?? 0)));
                        } else {
                            // Pure project remaining balance:
                            $remainingBalance = (float)($hStatus->pcc_can_be_spent ?? ($hStatus->acc_remaining ?? ($hStatus->remaining ?? 0)));
                        }
                    } catch (\Throwable $e) {
                        $remainingBalance = 0;
                    }
                }

                $grouped[$group][$headKey] = [
                    'head_id'   => $o->head_id,
                    'paid_from' => $displayName,
                    'payments'  => 0,
                    'amount'    => 0.0,
                    'remaining' => $remainingBalance,
                    'remarks'   => '',
                ];
            }

            $grouped[$group][$headKey]['payments']++;
            $grouped[$group][$headKey]['amount'] += (float)$o->sor_salary;
        }

        // Sort groups by official order
        $sortedGrouped = [];
        foreach ($groupOrder as $g) {
            if (!empty($grouped[$g])) {
                $sortedGrouped[$g] = array_values($grouped[$g]);
            }
        }
        foreach ($grouped as $g => $items) {
            if (!isset($sortedGrouped[$g])) {
                $sortedGrouped[$g] = array_values($items);
            }
        }

        return $sortedGrouped;
    }

    /**
     * Synchronize unassigned salary orders of the month with the case,
     * updating total payments, total amount, and summary paragraphs.
     */
    public function syncCaseOrders(SalOrderCase $case, bool $forceParas = false): SalOrderCase
    {
        $status = strtolower(trim((string)$case->soc_status));
        $stage = strtoupper(trim((string)$case->soc_current_stage));
        if (in_array($status, ['approved', 'closed', 'cancelled'], true) || $stage === 'CLOSED') {
            return $case;
        }

        $cleanMonth = substr(trim($case->soc_month), 0, 7);

        // 1. Link ANY unassigned active salary orders (current month or prior pending months) to this case
        $unlinkedOrders = FinSalOrder::where(function($q) use ($cleanMonth) {
                $q->whereRaw("to_char(sor_month, 'YYYY-MM') <= ?", [$cleanMonth]);
            })
            ->whereIn('sor_status', ['Draft', 'Approved', 'Open'])
            ->whereNull('sor_soc_id')
            ->get();

        if ($unlinkedOrders->isNotEmpty()) {
            FinSalOrder::whereIn('sor_id', $unlinkedOrders->pluck('sor_id')->all())
                ->update(['sor_soc_id' => $case->soc_id]);
            // If new orders are linked, force refresh table and paras
            $forceParas = true;
        }

        // 2. Query all active orders currently linked to this case
        $allOrders = FinSalOrder::where('sor_soc_id', $case->soc_id)
            ->whereNotIn('sor_status', ['Cancelled', 'Fulfilled'])
            ->get();

        if ($allOrders->isEmpty()) {
            return $case;
        }

        $totalPayments = $allOrders->count();
        $totalAmount = (float) $allOrders->sum('sor_salary');

        $paidEmpIds = $allOrders->pluck('sor_emp_id')->filter()->unique()->values();
        $totalPaidEmps = $paidEmpIds->count();

        $empOrderCounts = $allOrders->groupBy('sor_emp_id')->map->count();
        $splitEmployees = $empOrderCounts->filter(fn($c) => $c > 1);
        $splitSalariesCount = $splitEmployees->count();
        $splitPaymentsCount = $splitEmployees->sum();

        $totalDueEmps = DB::table('hr.emps')
            ->where('emp_status', 'ILIKE', 'Active%')
            ->whereExists(function($q) {
                $q->select(DB::raw(1))
                  ->from('hr.contracts')
                  ->whereColumn('hr.contracts.ctr_num', 'hr.emps.emp_id');
            })
            ->count();

        if ($totalDueEmps < $totalPaidEmps) {
            $totalDueEmps = $totalPaidEmps;
        }

        $heldEmpCount = max(0, $totalDueEmps - $totalPaidEmps);
        $carbonMonth = Carbon::createFromFormat('Y-m', $cleanMonth);
        $monthShort = $carbonMonth->format('M y');

        $para1 = sprintf(
            "1. %d employees are due for salary. Salaries of %d employees are being paid for the month of %s against projects mentioned below. %d salaries are split into %d payments. Salary sheet is placed at Encl 40-A/F.",
            $totalDueEmps,
            $totalPaidEmps,
            $monthShort,
            $splitSalariesCount,
            $splitPaymentsCount
        );

        $para2HeldSummary = [];
        if ($heldEmpCount > 0) {
            $heldRows = DB::table('hr.emps as e')
                ->join('cen.units as u', 'u.unt_id', '=', 'e.emp_unt_id')
                ->where('e.emp_status', 'ILIKE', 'Active%')
                ->whereNotIn('e.emp_id', $paidEmpIds->all())
                ->select('u.unt_id', 'u.unt_name', DB::raw('count(*) as cnt'))
                ->groupBy('u.unt_id', 'u.unt_name')
                ->get();

            foreach ($heldRows as $hr) {
                $divShort = match((int)$hr->unt_id) {
                    200000 => 'Command, Control and Communication (C3)',
                    250000 => 'AI and Immersive Technology Div',
                    300000 => 'Under Water Tech Div',
                    350000 => 'Sensors and UAV Technology Div',
                    400000 => 'Radar and EW Div',
                    450000 => 'Ex System Div',
                    default => 'Center'
                };
                $para2HeldSummary[] = sprintf("%02dx employees of %s", $hr->cnt, $divShort);
            }
        }

        $heldTextPart = !empty($para2HeldSummary) 
            ? implode(" and ", $para2HeldSummary) 
            : sprintf("%02dx employees", $heldEmpCount);

        $para2 = $heldEmpCount > 0
            ? sprintf("2. Salaries of %s have been held due to contract expiry / attendance verification.", $heldTextPart)
            : "2. Salaries of all eligible contract employees have been processed in full.";

        $amountInWords = $this->numberToWords((int)$totalAmount);
        $para3 = sprintf(
            "3. Foregoing in view, approval of Rs. %s (%s) for salaries of %d employees for month of %s may please be accorded for release of the payment through M/s MTSS.",
            number_format($totalAmount),
            $amountInWords,
            $totalPaidEmps,
            $monthShort
        );

        $updateData = [
            'soc_total_due_emps'       => $totalDueEmps,
            'soc_total_paid_emps'      => $totalPaidEmps,
            'soc_split_salaries_count' => $splitSalariesCount,
            'soc_split_payments_count' => $splitPaymentsCount,
            'soc_total_payments'       => $totalPayments,
            'soc_total_amount'         => $totalAmount,
        ];

        // If table data was not manually saved by user, keep paragraphs and table synced to current orders
        if (empty($case->soc_table_data) || $forceParas) {
            $updateData['soc_para1_text'] = $para1;
            $updateData['soc_para2_text'] = $para2;
            $updateData['soc_para3_text'] = $para3;
            if ($forceParas) {
                $updateData['soc_table_data'] = null; // Clear cached table so getMinuteTableData regenerates from orders
            }
        }

        $case->update($updateData);

        return $case->fresh();
    }

    /**
     * Update draft details (Minute No, Encl Ref, Para 1, Table Box Data, Para 2, Para 3, Payment Channel).
     */
    public function updateDraft(SalOrderCase $case, array $data, CenAccount $user): SalOrderCase
    {
        $status = strtolower(trim((string)$case->soc_status));
        $stage = strtoupper(trim((string)$case->soc_current_stage));

        if (in_array($status, ['approved', 'closed', 'cancelled'], true) || $stage === 'CLOSED') {
            throw new \RuntimeException('Case is finalized and cannot be edited directly.');
        }

        if (!in_array($status, ['draft', 'pending action', 'returned'], true) && $stage !== 'FINANCE') {
            throw new \RuntimeException('Case is not in Draft / Editable mode and cannot be edited directly.');
        }

        $tableData = $data['soc_table_data'] ?? null;
        if (is_string($tableData)) {
            $tableData = json_decode($tableData, true);
        }

        $totalPayments = $case->soc_total_payments;
        $totalAmount = $case->soc_total_amount;

        // If table data was updated, recalculate total payments and total amount
        if (is_array($tableData)) {
            $sumPayments = 0;
            $sumAmount = 0.0;
            foreach ($tableData as $group => $rows) {
                if (is_array($rows)) {
                    foreach ($rows as $r) {
                        $sumPayments += (int)($r['payments'] ?? 0);
                        $sumAmount += (float)($r['amount'] ?? 0);
                    }
                }
            }
            if ($sumPayments > 0 || $sumAmount > 0) {
                $totalPayments = $sumPayments;
                $totalAmount = $sumAmount;
            }
        }

        $case->update([
            'soc_minute_no'       => $data['soc_minute_no'] ?? $case->soc_minute_no,
            'soc_encl_ref'        => $data['soc_encl_ref'] ?? $case->soc_encl_ref,
            'soc_para1_text'      => $data['soc_para1_text'] ?? $case->soc_para1_text,
            'soc_para2_text'      => $data['soc_para2_text'] ?? $case->soc_para2_text,
            'soc_para3_text'      => $data['soc_para3_text'] ?? $case->soc_para3_text,
            'soc_payment_channel' => $data['soc_payment_channel'] ?? $case->soc_payment_channel,
            'soc_table_data'      => $tableData ?? $case->soc_table_data,
            'soc_total_payments'  => $totalPayments,
            'soc_total_amount'    => $totalAmount,
        ]);

        return $case->fresh();
    }

    /**
    /**
     * Get official designation / desk title for an account.
     */
    public static function getOfficialDesignation(CenAccount|int|null $user): string
    {
        $accId = $user instanceof CenAccount ? (int) $user->acc_id : (int) $user;

        $map = [
            100 => 'Managing Director (MD)',
            101 => 'Managing Director (MD)',
            108 => 'Deputy Director General (DDG)',
            107 => 'Director General (DG)',
            106 => 'Director Logistics (DLOG)',
            45  => 'Director Finance',
            54  => 'SO Finance',
        ];

        if (isset($map[$accId])) {
            return $map[$accId];
        }

        if ($user instanceof CenAccount) {
            if ($user->acc_desig) {
                return $user->acc_desig;
            }
            $area = strtolower(trim((string)$user->acc_untarea));
            if (in_array($area, ['fin', 'finance'], true)) {
                return 'Director Finance';
            }
            return $user->acc_username ?: 'Reviewer';
        }

        if ($accId > 0) {
            $acc = CenAccount::find($accId);
            if ($acc) {
                return self::getOfficialDesignation($acc);
            }
        }

        return 'Reviewer';
    }

    /**
     * Get available routing destinations for forwarding the case.
     * Strictly includes: MD (acc_100), DDG (acc_108), DG (acc_107), DLOG (acc_106),
     * Director Finance (acc_45), SO Finance (acc_54).
     */
    public function getAvailableDestinations(?CenAccount $user = null, ?SalOrderCase $case = null): array
    {
        $dests = [];
        $currentAccId = (int) ($user?->acc_id ?? 0);

        // Defined routing authorities map
        $authorities = [
            100 => [
                'name'  => 'Managing Director (MD)',
                'dept'  => 'Managing Director (MD Office)',
                'badge' => 'MD',
                'stage' => 'MD',
            ],
            108 => [
                'name'  => 'Deputy Director General (DDG)',
                'dept'  => 'Deputy Director General (DDG Office)',
                'badge' => 'DDG',
                'stage' => 'DDG',
            ],
            107 => [
                'name'  => 'Director General (DG)',
                'dept'  => 'Director General (DG Office)',
                'badge' => 'DG',
                'stage' => 'DG',
            ],
            106 => [
                'name'  => 'Director Logistics (DLOG)',
                'dept'  => 'Directorate of Logistics (DLOG)',
                'badge' => 'DLOG',
                'stage' => 'DLOG',
            ],
            45 => [
                'name'  => 'Director Finance',
                'dept'  => 'Directorate of Finance (DFin)',
                'badge' => 'DFin',
                'stage' => 'Finance',
            ],
            54 => [
                'name'  => 'SO Finance',
                'dept'  => 'Finance Department (SO Finance)',
                'badge' => 'SO Finance',
                'stage' => 'Finance',
            ],
        ];

        foreach ($authorities as $accId => $meta) {
            if ($accId === $currentAccId) {
                continue; // Do not include current user in forward destinations
            }

            $acc = CenAccount::find($accId);
            if ($acc) {
                $code = 'acc_' . $acc->acc_id;
                $dests[$code] = [
                    'code'     => $code,
                    'acc_id'   => $acc->acc_id,
                    'dept'     => $meta['dept'],
                    'name'     => $meta['name'],
                    'desig'    => $meta['name'],
                    'badge'    => $meta['badge'],
                    'stage'    => $meta['stage'],
                ];
            }
        }

        return $dests;
    }

    /**
     * Send / Forward case to next destination with minutes remarks.
     */
    public function sendCase(SalOrderCase $case, CenAccount $user, string $targetDest, string $remarks): SalOrderCase
    {
        $dests = $this->getAvailableDestinations($user, $case);

        $targetAccId = null;
        $targetHolder = null;
        $nextStage = 'MD';

        if (str_starts_with($targetDest, 'acc_')) {
            $targetAccId = (int) str_replace('acc_', '', $targetDest);
            $targetHolder = self::getOfficialDesignation($targetAccId);
            if ($targetAccId === 107) $nextStage = 'DG';
            elseif ($targetAccId === 108) $nextStage = 'DDG';
            elseif (in_array($targetAccId, [100, 101], true)) $nextStage = 'MD';
            elseif ($targetAccId === 106) $nextStage = 'DLOG';
            elseif (in_array($targetAccId, [45, 54], true)) $nextStage = 'Finance';
            else $nextStage = 'Reviewer';
        } elseif ($targetDest === 'fin_desk') {
            $nextStage = 'Finance';
            $targetHolder = 'Director Finance';
            $targetAccId = 45;
        } else {
            $nextStage = $dests[$targetDest]['stage'] ?? 'MD';
            $targetHolder = $dests[$targetDest]['name'] ?? ($dests[$targetDest]['dept'] ?? $nextStage);
            $targetAccId = $dests[$targetDest]['acc_id'] ?? null;
        }

        $senderDesignation = self::getOfficialDesignation($user);
        $currentStage = $case->soc_current_stage;
        $action = "Forwarded to " . ($targetHolder ?: $nextStage) . " for Approval";

        return DB::transaction(function() use ($case, $user, $senderDesignation, $currentStage, $nextStage, $targetAccId, $targetHolder, $action, $remarks) {
            $case->update([
                'soc_current_stage' => $nextStage,
                'soc_current_acc_id' => $targetAccId,
                'soc_current_holder' => $targetHolder,
                'soc_status' => 'Under Approval',
            ]);

            SalOrderCaseDecision::create([
                'scd_soc_id' => $case->soc_id,
                'scd_acc_id' => $user->acc_id,
                'scd_user_name' => $senderDesignation,
                'scd_user_rank' => $user->acc_rank ?? '',
                'scd_user_desig' => $senderDesignation,
                'scd_role' => $user->acc_badge ?: (strtoupper($user->acc_untarea ?: 'FIN')),
                'scd_action' => $action,
                'scd_from_stage' => $currentStage,
                'scd_to_stage' => $nextStage,
                'scd_remarks' => $remarks,
                'scd_dtg' => now(),
            ]);

            return $case->fresh();
        });
    }

    /**
     * Approve Salary Order Case.
     */
    public function approveCase(SalOrderCase $case, CenAccount $user, ?string $remarks = null): SalOrderCase
    {
        $userDesignation = self::getOfficialDesignation($user);

        return DB::transaction(function() use ($case, $user, $userDesignation, $remarks) {
            $case->update([
                'soc_status' => 'Approved',
                'soc_current_holder' => 'Approved by ' . $userDesignation,
            ]);

            // Update associated salary orders to Approved
            FinSalOrder::where('sor_soc_id', $case->soc_id)
                ->where('sor_status', 'Draft')
                ->update(['sor_status' => 'Approved']);

            SalOrderCaseDecision::create([
                'scd_soc_id' => $case->soc_id,
                'scd_acc_id' => $user->acc_id,
                'scd_user_name' => $userDesignation,
                'scd_user_rank' => $user->acc_rank ?? '',
                'scd_user_desig' => $userDesignation,
                'scd_role' => $user->acc_badge ?: 'Approving Authority',
                'scd_action' => 'Approved',
                'scd_from_stage' => $case->soc_current_stage,
                'scd_to_stage' => 'Approved',
                'scd_remarks' => $remarks ?: 'Salary orders verification case approved for payment release.',
                'scd_dtg' => now(),
            ]);

            return $case->fresh();
        });
    }

    /**
     * Cancel / Reject Salary Order Case.
     */
    public function cancelCase(SalOrderCase $case, CenAccount $user, ?string $remarks = null): SalOrderCase
    {
        $userDesignation = self::getOfficialDesignation($user);

        return DB::transaction(function() use ($case, $user, $userDesignation, $remarks) {
            $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
            $isFinance = (method_exists($user, 'isFinance') && $user->isFinance()) || in_array($userArea, ['fin', 'finance'], true) || ((int)($user->acc_unt_id ?? 0) === 800000);

            if (!$isFinance && in_array(strtoupper(trim((string)$case->soc_current_stage)), ['MD', 'DDG', 'DG', 'DLOG'])) {
                // Command authority returns case back to Finance desk for revision
                $case->update([
                    'soc_status' => 'Pending Action',
                    'soc_current_stage' => 'Finance',
                    'soc_current_acc_id' => 45,
                    'soc_current_holder' => 'Director Finance (Returned by ' . $userDesignation . ')',
                ]);

                SalOrderCaseDecision::create([
                    'scd_soc_id' => $case->soc_id,
                    'scd_acc_id' => $user->acc_id,
                    'scd_user_name' => $userDesignation,
                    'scd_user_rank' => $user->acc_rank ?? '',
                    'scd_user_desig' => $userDesignation,
                    'scd_role' => 'Reviewing Authority',
                    'scd_action' => 'Returned to Finance',
                    'scd_from_stage' => $case->soc_current_stage,
                    'scd_to_stage' => 'Finance',
                    'scd_remarks' => $remarks ?: 'Case returned to Finance desk for revision.',
                    'scd_dtg' => now(),
                ]);
            } else {
                // Case is Cancelled! Moves to Closed tab, leaving Pending Action
                $case->update([
                    'soc_status' => 'Cancelled',
                    'soc_current_stage' => 'Closed',
                    'soc_current_holder' => 'Cancelled by ' . $userDesignation,
                ]);

                // Decouple linked salary orders: release them back to unassigned draft so a fresh case can be made anytime
                FinSalOrder::where('sor_soc_id', $case->soc_id)
                    ->update(['sor_soc_id' => null]);

                SalOrderCaseDecision::create([
                    'scd_soc_id' => $case->soc_id,
                    'scd_acc_id' => $user->acc_id,
                    'scd_user_name' => $userDesignation,
                    'scd_user_rank' => $user->acc_rank ?? '',
                    'scd_user_desig' => $userDesignation,
                    'scd_role' => 'Finance Authority',
                    'scd_action' => 'Cancelled',
                    'scd_from_stage' => $case->soc_current_stage,
                    'scd_to_stage' => 'Cancelled',
                    'scd_remarks' => $remarks ?: 'Verification case cancelled. Salary orders freed back to draft.',
                    'scd_dtg' => now(),
                ]);
            }

            return $case->fresh();
        });
    }

    /**
     * Convert integer/float number to Words (Millions, Thousands, Hundreds).
     */
    public function numberToWords(float|int $number): string
    {
        $num = (int) round($number);
        if ($num === 0) {
            return 'Zero Rupees Only';
        }

        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four',
            5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen',
            15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
        ];

        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        ];

        $convertGroup = function($n) use ($ones, $tens) {
            $out = '';
            if ($n >= 100) {
                $out .= $ones[(int)($n / 100)] . ' Hundred ';
                $n %= 100;
            }
            if ($n >= 20) {
                $out .= $tens[(int)($n / 10)] . ' ';
                $n %= 10;
            }
            if ($n > 0) {
                $out .= $ones[$n] . ' ';
            }
            return trim($out);
        };

        $words = '';

        if ($num >= 1000000000) {
            $billions = (int)($num / 1000000000);
            $words .= $convertGroup($billions) . ' Billion ';
            $num %= 1000000000;
        }

        if ($num >= 1000000) {
            $millions = (int)($num / 1000000);
            $words .= $convertGroup($millions) . ' Million ';
            $num %= 1000000;
        }

        if ($num >= 1000) {
            $thousands = (int)($num / 1000);
            $words .= $convertGroup($thousands) . ' Thousand ';
            $num %= 1000;
        }

        if ($num > 0) {
            $words .= $convertGroup($num) . ' ';
        }

        return 'Rupees ' . trim($words) . ' Only';
    }
}
