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
            if (method_exists($user, 'isMyDepartmentMode') && $user->isMyDepartmentMode()) {
                [$lower, $upper] = [(int)$user->acc_unt_id, (int)$user->acc_unt_id];
            } else {
                [$lower, $upper] = $user->acc_lowers == 0
                    ? [$user->acc_lowerm, $user->acc_upperm]
                    : [$user->acc_lowers, $user->acc_uppers];
            }
            $purCount = Purchase::where(function($q) use ($lower, $upper, $user) {
                    $q->whereBetween('pcs_unt_id', [$lower, $upper])
                      ->orWhere('pcs_intunt_id', $user->acc_unt_id);
                })
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
        } elseif ((method_exists($user, 'isDlog') && $user->isDlog()) || in_array($area, ['log', 'dlog', 'logistics'], true)) {
            $purCount = Purchase::atStage('DLOG')
                ->whereNotIn('pcs_status', ['Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->count();
        } elseif ($area === 'it') {
            $purCount = Purchase::atStage('IT')
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
            if (method_exists($user, 'isMyDepartmentMode') && $user->isMyDepartmentMode()) {
                [$lower, $upper] = [(int)$user->acc_unt_id, (int)$user->acc_unt_id];
            } else {
                [$lower, $upper] = $user->acc_lowers == 0
                    ? [$user->acc_lowerm, $user->acc_upperm]
                    : [$user->acc_lowers, $user->acc_uppers];
            }
            $ctrCount = DB::table('hr.ctrcases')
                ->where(function($q) use ($lower, $upper, $user) {
                    $q->whereBetween('ctc_unt_id', [$lower, $upper])
                      ->orWhere('ctc_newunt_id', $user->acc_unt_id);
                })
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
        } elseif ((method_exists($user, 'isDlog') && $user->isDlog()) || in_array($area, ['log', 'dlog', 'logistics'], true)) {
            $ctrCount = \App\Models\HrCtrCase::atStage('DLOG')
                ->whereNotIn('ctc_status', ['Draft', 'Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled'])
                ->count();
        } elseif ($area === 'it') {
            $ctrCount = \App\Models\HrCtrCase::atStage('IT')
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
                if (method_exists($user, 'isMyDepartmentMode') && $user->isMyDepartmentMode()) {
                    [$lower, $upper] = [(int)$user->acc_unt_id, (int)$user->acc_unt_id];
                } else {
                    [$lower, $upper] = $user->acc_lowers == 0
                        ? [$user->acc_lowerm, $user->acc_upperm]
                        : [$user->acc_lowers, $user->acc_uppers];
                }
                $expiringQuery->whereBetween('e.emp_unt_id', [$lower, $upper]);
            }

            $hrCount = $expiringQuery->distinct('c.ctr_num')->count('c.ctr_num');
        }

        // 4. GENERIC CASE FILES (E-OFFICE) PENDING COUNT (Action Required for this user / authority)
        $gcsCount = 0;
        $uAccId = (int) $user->acc_id;
        try {
            $desigShort = strtoupper(trim((string) ($user->acc_desigshort ?? '')));
            $desig = strtoupper(trim((string) ($user->acc_desig ?? '')));

            $gcsCount = \App\Models\GenericCase::where(function ($q) use ($user, $uAccId, $area, $desigShort, $desig) {
                // Drafts created by this user
                $q->where(function ($dq) use ($uAccId) {
                    $dq->where('gcs_created_by', $uAccId)->where('gcs_status', 'Draft');
                })
                // OR Cases currently assigned directly to this user
                ->orWhere(function ($fq) use ($uAccId) {
                    $fq->where('gcs_current_holder_id', $uAccId)
                       ->whereNotIn('gcs_status', ['Draft', 'Approved', 'Closed', 'Cancelled', 'Rejected']);
                })
                // OR Cases forwarded to this officer's authority stage (MD, DDG, DG, DLOG, Finance)
                ->orWhere(function ($sq) use ($user, $uAccId, $area, $desigShort, $desig) {
                    $sq->whereNotIn('gcs_status', ['Draft', 'Approved', 'Closed', 'Cancelled', 'Rejected']);
                    // The case must still be in my hands: either nobody holds it
                    // explicitly, or I am the current holder. Without this a case
                    // forwarded onward kept blinking for the officer who sent it.
                    $sq->where(function ($hq) use ($uAccId) {
                        $hq->whereNull('gcs_current_holder_id')
                           ->orWhere('gcs_current_holder_id', $uAccId);
                    });
                    $sq->where(function ($stq) use ($user, $area, $desigShort, $desig) {
                        if ($user->isFinance() || in_array($area, ['fin', 'finance'], true) || ((int)($user->acc_unt_id ?? 0) === 800000)) {
                            $stq->whereIn('gcs_current_stage', ['DFin', 'Finance', 'DFIN', 'FINANCE']);
                        } elseif (in_array($desigShort, ['MD', 'MD RDW'], true) || in_array($area, ['rdw', 'md'], true) || str_contains($desig, 'MANAGING DIRECTOR')) {
                            $stq->whereIn('gcs_current_stage', ['MD', 'MD RDW']);
                        } elseif (in_array($desigShort, ['DDG', 'DDG NRDI', 'DDG NRD'], true) || $area === 'hqs' || str_contains($desig, 'DEPUTY DIRECTOR GENERAL')) {
                            $stq->whereIn('gcs_current_stage', ['DDG', 'DDG NRDI']);
                        } elseif (in_array($desigShort, ['DG', 'DG NRDI'], true) || in_array($area, ['nrdi', 'dg'], true) || str_contains($desig, 'DIRECTOR GENERAL')) {
                            $stq->whereIn('gcs_current_stage', ['DG', 'DG NRDI']);
                        } elseif ($user->isDlog() || in_array($desigShort, ['DLOG'], true) || in_array($area, ['log', 'dlog'], true)) {
                            $stq->whereIn('gcs_current_stage', ['DLOG', 'DIR LOG']);
                        } else {
                            $stq->whereRaw('1=0');
                        }
                    });
                });
            })->count();
        } catch (\Throwable $e) {}

        // 5. FUND SHIFTING CASES (Pending action for this user only)
        $fshCount = 0;
        try {
            $myStage = self::authorityStage($user);
            if ($myStage) {
                $fshCount = \App\Models\FundShift::query()
                    ->whereNotIn(DB::raw("LOWER(TRIM(COALESCE(fsh_status, '')))"), ['implemented', 'cancelled', 'rejected', 'closed'])
                    ->where(function ($w) use ($uAccId, $myStage, $user) {
                        // Case handed to me personally
                        $w->where('fsh_current_acc_id', $uAccId);
                        // Or parked at my own stage without a named officer
                        $w->orWhere(function ($sq) use ($myStage, $user, $uAccId) {
                            $sq->whereNull('fsh_current_acc_id')
                               ->whereRaw("UPPER(TRIM(COALESCE(fsh_current_stage, ''))) = ?", [strtoupper($myStage)]);
                            if ($myStage === 'Division') {
                                $sq->where(function ($dq) use ($user, $uAccId) {
                                    $dq->where('fsh_unt_id', (int) ($user->acc_unt_id ?? 0))
                                       ->orWhere('fsh_created_by', $uAccId);
                                });
                            }
                        });
                    })
                    // If my own signature is the latest one on the file, it has
                    // already left my desk - never count it as pending for me.
                    ->whereRaw(
                        "NOT EXISTS (SELECT 1 FROM fin.fund_shift_decisions d2"
                        . " WHERE d2.fsd_fsh_id = fsh_id AND d2.fsd_acc_id = ?"
                        . " AND d2.fsd_id = (SELECT MAX(d3.fsd_id) FROM fin.fund_shift_decisions d3 WHERE d3.fsd_fsh_id = fsh_id))",
                        [$uAccId]
                    )
                    ->count();
            }
        } catch (\Throwable $e) {}

        // 6. SALARY ORDER CASES (Pending action for this user only)
        $socCount = 0;
        try {
            $myStage = self::authorityStage($user);
            $socCount = \App\Models\SalOrderCase::query()
                ->whereNotIn(DB::raw("LOWER(TRIM(COALESCE(soc_status, '')))"), ['approved', 'cancelled', 'closed'])
                ->where(function ($w) use ($uAccId, $myStage) {
                    $w->where('soc_current_acc_id', $uAccId);
                    if ($myStage) {
                        $w->orWhere(function ($sq) use ($myStage) {
                            $sq->whereNull('soc_current_acc_id')
                               ->whereRaw("UPPER(TRIM(COALESCE(soc_current_stage, ''))) = ?", [strtoupper($myStage)]);
                        });
                    }
                })
                ->count();

            // Finance also owns the salary orders that arrive from the back end:
            // every month that still has unassigned orders needs a case opened.
            if ($myStage === 'Finance') {
                $socCount += (int) DB::table('fin.salorders')
                    ->whereIn(DB::raw("LOWER(TRIM(COALESCE(sor_status, '')))"), ['draft', 'approved', 'open'])
                    ->whereNull('sor_soc_id')
                    ->distinct()
                    ->count(DB::raw("to_char(sor_month, 'YYYY-MM')"));
            }
        } catch (\Throwable $e) {}

        return [
            'pur' => (int) $purCount,
            'ctr' => (int) $ctrCount,
            'hr'  => (int) $hrCount,
            'gcs' => (int) $gcsCount,
            'fsh' => (int) $fshCount,
            'soc' => (int) $socCount,
        ];
    }

    /**
     * The ONE workflow stage this user is responsible for.
     *
     * Badges must never be built from a list of stages (e.g. MD + DDG + DG):
     * that is why a case forwarded by DDG to DG kept showing on the DDG
     * sidebar. Returns null for areas that own no stage (HR, Procurement,
     * Admin ...) so they get no pending-action badge at all.
     */
    private static function authorityStage($user): ?string
    {
        if (!$user) {
            return null;
        }

        $ctx  = \App\Services\Auth\UserAccessContext::forUser($user);
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));

        if ($ctx->isMd())   return 'MD';
        if ($ctx->isDdg())  return 'DDG';
        if ($ctx->isDg())   return 'DG';
        if ($ctx->isDlog()) return 'DLOG';

        if ((method_exists($user, 'isFinance') && $user->isFinance())
            || in_array($area, ['fin', 'finance'], true)
            || (int) ($user->acc_unt_id ?? 0) === 800000) {
            return 'Finance';
        }

        if (in_array($area, ['it', 'is'], true)) {
            return 'IT';
        }

        if (method_exists($user, 'isDivision') && $user->isDivision()
            && !(method_exists($user, 'isCentralDepartment') && $user->isCentralDepartment())) {
            return 'Division';
        }

        return null;
    }
}

