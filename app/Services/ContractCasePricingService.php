<?php

namespace App\Services;

use App\Models\HrCtrCase;
use App\Models\HrCtrCasePlan;
use App\Models\HrContractPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContractCasePricingService
{
    /**
     * Generate or regenerate monthly contract case plans (hr_ctrcaseplans)
     *
     * @param int $caseId
     * @param string $startDate
     * @param string $endDate
     * @param array|null $monthlyHeadMap Map of 'YYYY-MM' => headId
     * @param int|null $singleHeadId
     * @return void
     */
    public function generatePlans(int $caseId, string $startDate, string $endDate, ?array $monthlyHeadMap = null, ?int $singleHeadId = null): void
    {
        // Delete previous plans for this case
        HrCtrCasePlan::where('ccp_ctc_id', $caseId)->delete();

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->gt($end)) {
            return;
        }

        $current = $start->copy();

        while ($current->lte($end)) {
            $monthStart = $current->copy();
            $monthEnd = $current->copy()->endOfMonth();

            if ($monthEnd->gt($end)) {
                $monthEnd = $end->copy();
            }

            $monthKey = $current->format('Y-m');
            $hedId = null;

            if ($monthlyHeadMap && isset($monthlyHeadMap[$monthKey])) {
                $hedId = !empty($monthlyHeadMap[$monthKey]) ? (int)$monthlyHeadMap[$monthKey] : null;
            } elseif ($singleHeadId) {
                $hedId = (int)$singleHeadId;
            }

            HrCtrCasePlan::create([
                'ccp_ctc_id'  => $caseId,
                'ccp_startdt' => $monthStart->format('Y-m-d'),
                'ccp_enddt'   => $monthEnd->format('Y-m-d'),
                'ccp_hed_id'  => $hedId,
            ]);

            // Move to first day of next month
            $current = $current->copy()->addMonth()->startOfMonth();
        }
    }

    /**
     * Calculate and persist the exact contract case price (ctc_price)
     * factoring in monthly days proration and probation salary.
     *
     * @param HrCtrCase $case
     * @return float
     */
    public function calculatePrice(HrCtrCase $case): float
    {
        $plans = HrCtrCasePlan::where('ccp_ctc_id', $case->ctc_id)->orderBy('ccp_startdt')->get();

        $salary = (float)($case->ctc_newsalary ?? 0);
        $probMonths = (int)($case->ctc_newprob ?? 0);
        $probSalary = (float)($case->ctc_newprobsal ?? $salary);
        $startDate = $case->ctc_newstartdt ? Carbon::parse($case->ctc_newstartdt) : null;

        // If probSalary is not set or 0, default to normal salary
        if ($probSalary <= 0) {
            $probSalary = $salary;
        }

        // Probation cutoff date
        $probEndDate = ($startDate && $probMonths > 0)
            ? $startDate->copy()->addMonths($probMonths)->subDay()
            : null;

        $totalPrice = 0.0;

        if ($plans->isNotEmpty()) {
            foreach ($plans as $plan) {
                $pStart = Carbon::parse($plan->ccp_startdt);
                $pEnd = Carbon::parse($plan->ccp_enddt);

                $daysInSlice = $pStart->diffInDays($pEnd) + 1;
                $daysInMonth = $pStart->daysInMonth;

                // Determine applicable salary for this slice
                $sliceAmount = 0.0;
                if ($probEndDate && $pStart->lte($probEndDate)) {
                    if ($pEnd->lte($probEndDate)) {
                        // Entire slice is in probation
                        $sliceAmount = ($probSalary / $daysInMonth) * $daysInSlice;
                    } else {
                        // Slice crosses probation cutoff
                        $probDays = $pStart->diffInDays($probEndDate) + 1;
                        $regularDays = $daysInSlice - $probDays;
                        $sliceAmount = (($probSalary / $daysInMonth) * $probDays) + (($salary / $daysInMonth) * $regularDays);
                    }
                } else {
                    // Regular slice
                    $sliceAmount = ($salary / $daysInMonth) * $daysInSlice;
                }

                $totalPrice += round($sliceAmount, 2);
            }
        } elseif ($case->ctc_newstartdt && $case->ctc_newenddt) {
            // Fallback calculation if no plans generated yet
            $start = Carbon::parse($case->ctc_newstartdt);
            $end = Carbon::parse($case->ctc_newenddt);
            $current = $start->copy();

            while ($current->lte($end)) {
                $mStart = $current->copy();
                $mEnd = $current->copy()->endOfMonth();
                if ($mEnd->gt($end)) {
                    $mEnd = $end->copy();
                }

                $daysInSlice = $mStart->diffInDays($mEnd) + 1;
                $daysInMonth = $mStart->daysInMonth;

                if ($probEndDate && $mStart->lte($probEndDate)) {
                    if ($mEnd->lte($probEndDate)) {
                        $sliceAmount = ($probSalary / $daysInMonth) * $daysInSlice;
                    } else {
                        $probDays = $mStart->diffInDays($probEndDate) + 1;
                        $regularDays = $daysInSlice - $probDays;
                        $sliceAmount = (($probSalary / $daysInMonth) * $probDays) + (($salary / $daysInMonth) * $regularDays);
                    }
                } else {
                    $sliceAmount = ($salary / $daysInMonth) * $daysInSlice;
                }

                $totalPrice += round($sliceAmount, 2);
                $current = $current->copy()->addMonth()->startOfMonth();
            }
        }

        $roundedPrice = round($totalPrice, 0);

        // Persist to database without firing events
        DB::table('hr.ctrcases')
            ->where('ctc_id', $case->ctc_id)
            ->update(['ctc_price' => $roundedPrice]);

        $case->ctc_price = $roundedPrice;
        return $roundedPrice;
    }

    /**
     * Adjust existing contract plans for a contract (Extension / Ce)
     * Ports legacy AdjustPlan("Contract", ctr_id)
     *
     * @param int $contractId
     * @param string $newEndDate
     * @return void
     */
    public function adjustContractPlans(int $contractId, string $newEndDate): void
    {
        $newEnd = Carbon::parse($newEndDate);

        // Delete plans starting strictly after new end date
        HrContractPlan::where('cpn_ctr_id', $contractId)
            ->where('cpn_startdt', '>', $newEnd->format('Y-m-d'))
            ->delete();

        // Update plan spanning across new end date
        HrContractPlan::where('cpn_ctr_id', $contractId)
            ->where('cpn_startdt', '<=', $newEnd->format('Y-m-d'))
            ->where('cpn_enddt', '>', $newEnd->format('Y-m-d'))
            ->update(['cpn_enddt' => $newEnd->format('Y-m-d')]);

        // Check current max end date in plans
        $latestPlan = HrContractPlan::where('cpn_ctr_id', $contractId)
            ->orderBy('cpn_enddt', 'desc')
            ->first();

        if ($latestPlan && Carbon::parse($latestPlan->cpn_enddt)->lt($newEnd)) {
            $lastEnd = Carbon::parse($latestPlan->cpn_enddt);
            $nextStart = $lastEnd->copy()->addDay();
            $lastHedId = $latestPlan->cpn_hed_id;

            $current = $nextStart->copy();
            while ($current->lte($newEnd)) {
                $mStart = $current->copy();
                $mEnd = $current->copy()->endOfMonth();
                if ($mEnd->gt($newEnd)) {
                    $mEnd = $newEnd->copy();
                }

                HrContractPlan::create([
                    'cpn_ctr_id'  => $contractId,
                    'cpn_startdt' => $mStart->format('Y-m-d'),
                    'cpn_enddt'   => $mEnd->format('Y-m-d'),
                    'cpn_hed_id'  => $lastHedId,
                ]);

                $current = $current->copy()->addMonth()->startOfMonth();
            }
        }
    }
}
