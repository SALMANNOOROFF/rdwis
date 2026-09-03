<?php

namespace App\Services;

use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SidebarBadgeService
{
    public static function getBadgesForUser($user = null)
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return ['pur' => 0, 'ctr' => 0, 'hr' => 0];
        }

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        if (in_array($area, ['proc', 'prc'], true)) $area = 'proc';
        $isDivision = in_array($area, ['prj', 'rdwprj', 'division', 'initiation'], true)
            || (method_exists($user, 'isDivision') && $user->isDivision());

        // 1. PURCHASE CASES BADGE (Only cases requiring pending action for this user)
        $purCount = 0;
        $psTypes = app(\App\Services\PurchaseApprovalService::class)->getAssignedCaseTypes('PS');

        if ($isDivision) {
            [$lower, $upper] = $user->acc_lowers == 0
                ? [$user->acc_lowerm, $user->acc_upperm]
                : [$user->acc_lowers, $user->acc_uppers];
            $purCount = Purchase::whereBetween('pcs_unt_id', [$lower, $upper])
                ->where(function($q) use ($psTypes) {
                    $q->where('pcs_status', 'Returned')
                      ->orWhere(function($sub) use ($psTypes) {
                          $sub->where('pcs_status', 'Draft')
                              ->where(function($s2) use ($psTypes) {
                                  $s2->whereNotIn(\Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(pcs_type, 'ps')))"), $psTypes)
                                     ->orWhereDoesntHave('decisions', function($d) {
                                         $d->whereIn('pdec_action', ['float_to_proc', 'reshare_to_proc']);
                                     })
                                     ->orWhereHas('decisions', function($d) {
                                         $d->where('pdec_action', 'dproc_save');
                                     });
                              });
                      });
                })
                ->whereNotIn('pcs_status', ['Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->count();
        } elseif ($area === 'proc') {
            $purCount = Purchase::whereIn(\Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(pcs_type, 'ps')))"), $psTypes)
                ->where(function($q) {
                    $q->where(function($sub) {
                        $sub->whereHas('decisions', function($d) {
                            $d->whereIn('pdec_action', ['float_to_proc', 'reshare_to_proc']);
                        })->whereDoesntHave('decisions', function($d) {
                            $d->where('pdec_action', 'dproc_save');
                        });
                    })
                    ->orWhere('pcs_status', 'Under Scrutiny')
                    ->orWhereHas('currentSubstatus', function($s) {
                        $s->where('pss_stage', 'DProc');
                    });
                })
                ->whereNotIn('pcs_status', ['Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->count();
        } elseif ($area === 'fin') {
            $purCount = Purchase::atStage('DFinance')
                ->whereNotIn('pcs_status', ['Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->count();
        } elseif (in_array($area, ['rdw', 'hqs', 'nrdi'])) {
            $stageMap = [
                'rdw'  => 'MD',
                'hqs'  => 'DDG',
                'nrdi' => 'DG',
            ];
            $stage = $stageMap[$area] ?? null;
            $purCount = $stage ? Purchase::atStage($stage)
                ->whereNotIn('pcs_status', ['Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->count() : 0;
        } else {
            $purCount = 0;
        }

        // 2. CONTRACT CASES BADGE (Only cases requiring pending action for this user)
        $ctrCount = 0;
        if ($isDivision) {
            [$lower, $upper] = $user->acc_lowers == 0
                ? [$user->acc_lowerm, $user->acc_upperm]
                : [$user->acc_lowers, $user->acc_uppers];
            $ctrCount = DB::table('hr.ctrcases')
                ->whereBetween('ctc_unt_id', [$lower, $upper])
                ->whereIn('ctc_status', ['Draft', 'Returned', 'Under Revision'])
                ->count();
        } elseif ($area === 'hr') {
            $ctrCount = \App\Models\HrCtrCase::atStage('HR')
                ->whereNotIn('ctc_status', ['Draft', 'Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled'])
                ->count();
            if ($ctrCount === 0) {
                $ctrCount = DB::table('hr.ctrcases')->whereIn('ctc_status', ['Under HR Scrutiny'])->count();
            }
        } elseif ($area === 'fin') {
            $ctrCount = \App\Models\HrCtrCase::atStage('Finance')
                ->whereNotIn('ctc_status', ['Draft', 'Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled'])
                ->count();
            if ($ctrCount === 0) {
                $ctrCount = DB::table('hr.ctrcases')->whereIn('ctc_status', ['Under Finance Scrutiny'])->count();
            }
        } elseif ($area === 'rdw') {
            $ctrCount = \App\Models\HrCtrCase::atStage('MD')
                ->whereNotIn('ctc_status', ['Draft', 'Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled'])
                ->count();
        } elseif ($area === 'hqs') {
            $ctrCount = \App\Models\HrCtrCase::atStage('DDG')
                ->whereNotIn('ctc_status', ['Draft', 'Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled'])
                ->count();
        } elseif ($area === 'nrdi') {
            $ctrCount = \App\Models\HrCtrCase::atStage('DG')
                ->whereNotIn('ctc_status', ['Draft', 'Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled'])
                ->count();
        } else {
            $ctrCount = 0;
        }

        // 3. HUMAN RESOURCES & HIRED EMPLOYEES (Expiring Contracts)
        $hrCount = 0;
        if ($area !== 'proc') {
            $threshold = Carbon::today()->addDays(45);
            $expiringQuery = DB::table('hr.contracts as c')
                ->join('hr.emps as e', 'e.emp_id', '=', 'c.ctr_num')
                ->whereIn(DB::raw('LOWER(e.emp_status)'), ['active', 'current'])
                ->where('c.ctr_enddt', '<=', $threshold->toDateString());

            if ($isDivision) {
                [$lower, $upper] = $user->acc_lowers == 0
                    ? [$user->acc_lowerm, $user->acc_upperm]
                    : [$user->acc_lowers, $user->acc_uppers];
                $expiringQuery->whereBetween('e.emp_unt_id', [$lower, $upper]);
            }

            $hrCount = $expiringQuery->distinct('c.ctr_num')->count('c.ctr_num');
        }

        return [
            'pur' => (int) $purCount,
            'ctr' => (int) $ctrCount,
            'hr'  => (int) $hrCount,
        ];
    }
}

