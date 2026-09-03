<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employee;

class DivHrController extends Controller
{
    // Employee list page
    public function employeelist(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Determine Mode with Session Persistence
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isGlobalHrViewer = in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs', 'proc', 'prc', 'it'])
            || session('impersonated_by_god')
            || strtolower($user->acc_username ?? '') === 'superadminrdw';

        if ($request->has('mode')) {
            $mode = $request->query('mode') === 's' ? 's' : 'm';
            session(['hr_mode' => $mode]);
        } else {
            $defaultMode = $isGlobalHrViewer ? 'm' : 's';
            $mode = session('hr_mode', $defaultMode);
        }

        $userAuth = (string) ($user->acc_auth ?? 'viewer');

        $q = Employee::query()
            ->leftJoin('cen.heads as h', 'hr.emps.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin('prj.projects as p', function ($join) {
                $join->on('p.prj_id', '=', 'h.hed_prj_id')
                     ->orOn('p.prj_id', '=', 'hr.emps.emp_hed_id')
                     ->orOn('p.prj_id', '=', 'h.hed_id');
            })
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'hr.emps.emp_unt_id')
            ->select('hr.emps.*', 'h.hed_code', 'h.hed_name', 'p.prj_title', 'p.prj_code', 'u.unt_name', 'u.unt_namesh');

        if (!$isGlobalHrViewer) {
            // For Division users, strictly lock to their division units
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
            $varModeStr = 'approver-s';
            $q->whereBetween('emp_unt_id', [$lower, $upper]);
        } else {
            // Global view (MD, DDG, DG, HR, Finance, Procurement)
            if ($mode === 's') {
                $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
                $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
                $varModeStr = 'approver-s';
                $q->whereBetween('emp_unt_id', [$lower, $upper]);
            } else {
                $lower = 0;
                $upper = 99999999;
                $varModeStr = 'approver-m';
                if ($request->filled('unit_id') && $request->unit_id !== 'all') {
                    $q->where('emp_unt_id', $request->unit_id);
                }
            }
        }

        // Available divisions/units with employees for dropdown filter
        $divisions = DB::table('cen.units as u')
            ->whereExists(function($sq) {
                $sq->select(DB::raw(1))
                   ->from('hr.emps as e')
                   ->whereColumn('e.emp_unt_id', 'u.unt_id');
            })
            ->select('u.unt_id', 'u.unt_name', 'u.unt_namesh')
            ->orderBy('u.unt_name')
            ->get();

        if ($request->filled('term')) {
            $t = strtolower($request->term);
            $q->whereRaw('LOWER(emp_name) LIKE ? OR LOWER(emp_id) LIKE ?', ["%$t%", "%$t%"]);
        }

        if ($request->filled('head_code')) {
            $q->where('h.hed_code', $request->head_code);
        }

        // Get status and compute counts before status filter is applied
        $status = $request->query('status', 'Current');
        
        $countQuery = clone $q;
        $allRecords = $countQuery->get();
        $activeCount = $allRecords->filter(function($e){
            return in_array(strtolower($e->emp_status ?? ''), ['active','current']);
        })->count();
        $previousCount = $allRecords->count() - $activeCount;

        if ($status === 'Current') {
            $q->whereRaw('LOWER(emp_status) IN (?, ?)', ['active','current']);
        } elseif ($status === 'Previous') {
            $q->whereRaw('LOWER(emp_status) NOT IN (?, ?)', ['active','current']);
        }

        $employees = $q->orderBy('emp_name', 'asc')->get();

        $empIds = $employees->pluck('emp_id')->filter()->toArray();
        $latestContracts = [];
        if (!empty($empIds)) {
            $today = Carbon::today()->toDateString();

            // 1. Fetch contracts for all employees in list
            $contractsRaw = DB::table('hr.contracts as c')
                ->leftJoin('cen.heads as ch', 'c.ctr_hed_id', '=', 'ch.hed_id')
                ->leftJoin('prj.projects as cp', function ($join) {
                    $join->on('cp.prj_id', '=', 'ch.hed_prj_id')
                         ->orOn('cp.prj_id', '=', 'c.ctr_hed_id')
                         ->orOn('cp.prj_id', '=', 'ch.hed_id');
                })
                ->whereIn('c.ctr_num', $empIds)
                ->select(
                    'c.ctr_id',
                    'c.ctr_num', 
                    'c.ctr_jobtitle', 
                    'c.ctr_salary', 
                    'c.ctr_grade',
                    'ch.hed_code', 
                    'ch.hed_name',
                    'cp.prj_title', 
                    'cp.prj_code',
                    'c.ctr_startdt', 
                    'c.ctr_enddt',
                    'c.ctr_termindt'
                )
                ->orderBy('c.ctr_startdt', 'desc')
                ->orderBy('c.ctr_id', 'desc')
                ->get();

            $groupedContracts = $contractsRaw->groupBy('ctr_num');
            $activeContractIds = [];
            $latestContracts = [];

            foreach ($groupedContracts as $empId => $empContracts) {
                // Find active contract covering today, otherwise most recent
                $activeCtr = $empContracts->first(function ($c) use ($today) {
                    $end = $c->ctr_termindt ?: $c->ctr_enddt;
                    return $c->ctr_startdt <= $today && (!$end || $end >= $today);
                }) ?: $empContracts->first();

                if ($activeCtr) {
                    $latestContracts[$empId] = $activeCtr;
                    $activeContractIds[] = $activeCtr->ctr_id;
                }
            }

            // 2. Resolve current month plan & distinct project count from hr.contractplans
            if (!empty($activeContractIds)) {
                $allPlans = DB::table('hr.contractplans as p')
                    ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'p.cpn_hed_id')
                    ->leftJoin('prj.projects as prj', function ($join) {
                        $join->on('prj.prj_id', '=', 'h.hed_prj_id')
                             ->orOn('prj.prj_id', '=', 'p.cpn_hed_id')
                             ->orOn('prj.prj_id', '=', 'h.hed_id');
                    })
                    ->whereIn('p.cpn_ctr_id', $activeContractIds)
                    ->select(
                        'p.cpn_ctr_id',
                        'p.cpn_startdt',
                        'p.cpn_enddt',
                        'p.cpn_hed_id',
                        'h.hed_code',
                        'h.hed_name',
                        'prj.prj_code',
                        'prj.prj_title'
                    )
                    ->orderBy('p.cpn_startdt', 'asc')
                    ->get()
                    ->groupBy('cpn_ctr_id');

                foreach ($latestContracts as $empId => $ctr) {
                    $plans = $allPlans[$ctr->ctr_id] ?? collect();
                    
                    // Legacy hr_contractplans_current_u logic: Find current month's plan row
                    $currentPlan = $plans->first(function ($p) use ($today) {
                        return $today >= $p->cpn_startdt && $today <= $p->cpn_enddt;
                    });

                    if (!$currentPlan && $plans->isNotEmpty()) {
                        $currentPlan = $plans->first();
                    }

                    $distinctHeads = $plans->pluck('cpn_hed_id')->filter()->unique();
                    $distinctCount = $distinctHeads->count();

                    $ctr->current_head_code = $currentPlan?->hed_code ?: ($currentPlan?->prj_code ?: ($ctr->hed_code ?: ($ctr->prj_code ?? null)));
                    $ctr->current_prj_title = $currentPlan?->prj_title ?: ($currentPlan?->hed_name ?: ($ctr->prj_title ?: ($ctr->hed_name ?? null)));
                    $ctr->distinct_count = $distinctCount;

                    // Group contiguous monthly slices by project into clean From-To spans
                    $projectSpans = [];
                    $currentSpan = null;
                    foreach ($plans as $p) {
                        $headId = $p->cpn_hed_id;
                        $pCode = $p->hed_code ?: ($p->prj_code ?: 'Unassigned');
                        $pTitle = $p->prj_title ?: ($p->hed_name ?: $pCode);

                        if ($currentSpan === null || $currentSpan['head_id'] !== $headId) {
                            if ($currentSpan !== null) {
                                $projectSpans[] = $currentSpan;
                            }
                            $currentSpan = [
                                'head_id'      => $headId,
                                'code'         => $pCode,
                                'title'        => $pTitle,
                                'start_dt'     => $p->cpn_startdt,
                                'end_dt'       => $p->cpn_enddt,
                                'start_label'  => Carbon::parse($p->cpn_startdt)->format('M Y'),
                                'end_label'    => Carbon::parse($p->cpn_enddt)->format('M Y'),
                                'months_count' => 1,
                                'is_current'   => ($today >= $p->cpn_startdt && $today <= $p->cpn_enddt),
                            ];
                        } else {
                            $currentSpan['end_dt'] = $p->cpn_enddt;
                            $currentSpan['end_label'] = Carbon::parse($p->cpn_enddt)->format('M Y');
                            $currentSpan['months_count']++;
                            if ($today >= $p->cpn_startdt && $today <= $p->cpn_enddt) {
                                $currentSpan['is_current'] = true;
                            }
                        }
                    }
                    if ($currentSpan !== null) {
                        $projectSpans[] = $currentSpan;
                    }

                    $ctr->plans_list = $projectSpans;
                    $ctr->project_spans = $projectSpans;
                }
            }
        }
        
        $canEdit = $this->checkCanEditEmployee($user);
        
        return view('divhr.employelist', compact('employees','activeCount','previousCount', 'mode', 'lower', 'upper', 'varModeStr', 'userAuth', 'latestContracts', 'isGlobalHrViewer', 'divisions', 'canEdit'));
    }

    // Employee detail page (ID from URL)
    public function employeedetail($id)
    {
        $user = Auth::user();
        $isGlobalHrViewer = $this->isGlobalHrViewer($user);

        // Security authorization
        if (!$isGlobalHrViewer) {
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
            $empCheck = DB::table('hr.emps')
                ->where('emp_id', $id)
                ->whereBetween('emp_unt_id', [$lower, $upper])
                ->first();
            if (!$empCheck) {
                abort(403, 'Unauthorized access to this employee profile.');
            }
        }

        $emp = Employee::query()
            ->leftJoin('cen.heads as h', 'hr.emps.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin('prj.projects as p', function ($join) {
                $join->on('p.prj_id', '=', 'h.hed_prj_id')
                     ->orOn('p.prj_id', '=', 'hr.emps.emp_hed_id')
                     ->orOn('p.prj_id', '=', 'h.hed_id');
            })
            ->leftJoin('cen.units as u', 'hr.emps.emp_unt_id', '=', 'u.unt_id')
            ->select('hr.emps.*', 'h.hed_code', 'h.hed_name', 'p.prj_title', 'p.prj_code', 'u.unt_name')
            ->where('emp_id', $id)
            ->first();

        $base = DB::table('hr.salreqs as s')
            ->leftJoin('hr.emps as e', 'e.emp_id', '=', 's.srq_emp_id')
            ->leftJoin('cen.units as u', function ($join) {
                $join->on('u.unt_id', '=', DB::raw('COALESCE(s.srq_effunt_id, e.emp_unt_id)'));
            })
            ->select(
                's.*',
                'u.unt_name as eff_unit_name'
            )
            ->where('s.srq_emp_id', $id)
            ->orderBy('s.srq_month', 'desc')
            ->first();

        $subheads = [];
        if ($base) {
            $salOrder = DB::table('fin.salorders')
                ->where(function($q) use ($base, $id) {
                    if (!empty($base->srq_id)) {
                        $q->where('sor_srq_id', $base->srq_id);
                    }
                    $q->orWhere(function($sub) use ($base, $id) {
                        $sub->where('sor_emp_id', $id)
                            ->where('sor_month', $base->srq_month);
                    });
                })
                ->orderBy('sor_month', 'desc')
                ->first();

            $sorId = $salOrder->sor_id ?? ($base->sor_id ?? null);
            if ($sorId) {
                $subheads = DB::table('fin.salorders_shd')
                    ->where('sod_sor_id', $sorId)
                    ->select('sod_subhead', 'sod_ratio')
                    ->orderBy('sod_subhead')
                    ->get();
            }
        }

        $monthRef = $base ? $base->srq_month : Carbon::now()->toDateString();
        $currentContract = DB::table('hr.contracts as c')
            ->leftJoin('cen.heads as ch', 'ch.hed_id', '=', 'c.ctr_hed_id')
            ->leftJoin('prj.projects as cp', function ($join) {
                $join->on('cp.prj_id', '=', 'ch.hed_prj_id')
                     ->orOn('cp.prj_id', '=', 'c.ctr_hed_id')
                     ->orOn('cp.prj_id', '=', 'ch.hed_id');
            })
            ->where('c.ctr_num', $id)
            ->whereRaw('? between c.ctr_startdt and c.ctr_enddt', [$monthRef])
            ->orderBy('c.ctr_enddt', 'desc')
            ->select(
                'c.*', 
                'ch.hed_code as ctr_hed_code', 
                'ch.hed_name as ctr_hed_name',
                'cp.prj_title as ctr_prj_title',
                'cp.prj_code as ctr_prj_code'
            )
            ->first();

        // If not found in current active window, get the latest contract
        if (!$currentContract) {
            $currentContract = DB::table('hr.contracts as c')
                ->leftJoin('cen.heads as ch', 'ch.hed_id', '=', 'c.ctr_hed_id')
                ->leftJoin('prj.projects as cp', function ($join) {
                    $join->on('cp.prj_id', '=', 'ch.hed_prj_id')
                         ->orOn('cp.prj_id', '=', 'c.ctr_hed_id')
                         ->orOn('cp.prj_id', '=', 'ch.hed_id');
                })
                ->where('c.ctr_num', $id)
                ->orderBy('c.ctr_startdt', 'desc')
                ->orderBy('c.ctr_id', 'desc')
                ->select(
                    'c.*', 
                    'ch.hed_code as ctr_hed_code', 
                    'ch.hed_name as ctr_hed_name',
                    'cp.prj_title as ctr_prj_title',
                    'cp.prj_code as ctr_prj_code'
                )
                ->first();
        }

        $currentContractPlans = collect();
        $distinctPlanCount = 0;
        if ($currentContract) {
            $today = Carbon::today()->toDateString();
            $plansRaw = DB::table('hr.contractplans as p')
                ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'p.cpn_hed_id')
                ->leftJoin('prj.projects as prj', function ($join) {
                    $join->on('prj.prj_id', '=', 'h.hed_prj_id')
                         ->orOn('prj.prj_id', '=', 'p.cpn_hed_id')
                         ->orOn('prj.prj_id', '=', 'h.hed_id');
                })
                ->where('p.cpn_ctr_id', $currentContract->ctr_id)
                ->select(
                    'p.*',
                    'h.hed_code',
                    'h.hed_name',
                    'prj.prj_code',
                    'prj.prj_title'
                )
                ->orderBy('p.cpn_startdt', 'asc')
                ->get();

            $distinctHeads = $plansRaw->pluck('cpn_hed_id')->filter()->unique();
            $distinctPlanCount = $distinctHeads->count();

            $currentPlan = $plansRaw->first(function ($p) use ($today) {
                return $today >= $p->cpn_startdt && $today <= $p->cpn_enddt;
            }) ?: $plansRaw->first();

            if ($currentPlan) {
                $currentContract->ctr_hed_code = $currentPlan->hed_code ?: ($currentPlan->prj_code ?: $currentContract->ctr_hed_code);
                $currentContract->ctr_prj_title = $currentPlan->prj_title ?: ($currentPlan->hed_name ?: $currentContract->ctr_prj_title);
            }

            // Group contiguous monthly slices by project into clean From-To spans
            $projectSpans = [];
            $currentSpan = null;
            foreach ($plansRaw as $p) {
                $headId = $p->cpn_hed_id;
                $pCode = $p->hed_code ?: ($p->prj_code ?: 'Unassigned');
                $pTitle = $p->prj_title ?: ($p->hed_name ?: $pCode);

                if ($currentSpan === null || $currentSpan['head_id'] !== $headId) {
                    if ($currentSpan !== null) {
                        $projectSpans[] = $currentSpan;
                    }
                    $currentSpan = [
                        'head_id'       => $headId,
                        'display_code'  => $pCode,
                        'display_title' => $pTitle,
                        'start_dt'      => $p->cpn_startdt,
                        'end_dt'        => $p->cpn_enddt,
                        'start_label'   => Carbon::parse($p->cpn_startdt)->format('M Y'),
                        'end_label'     => Carbon::parse($p->cpn_enddt)->format('M Y'),
                        'months_count'  => 1,
                        'is_current'    => ($today >= $p->cpn_startdt && $today <= $p->cpn_enddt),
                    ];
                } else {
                    $currentSpan['end_dt'] = $p->cpn_enddt;
                    $currentSpan['end_label'] = Carbon::parse($p->cpn_enddt)->format('M Y');
                    $currentSpan['months_count']++;
                    if ($today >= $p->cpn_startdt && $today <= $p->cpn_enddt) {
                        $currentSpan['is_current'] = true;
                    }
                }
            }
            if ($currentSpan !== null) {
                $projectSpans[] = $currentSpan;
            }

            $currentContractPlans = collect($projectSpans);
        }

        $contractsHistory = DB::table('hr.contracts as c')
            ->leftJoin('cen.heads as ch', 'ch.hed_id', '=', 'c.ctr_hed_id')
            ->leftJoin('prj.projects as cp', function ($join) {
                $join->on('cp.prj_id', '=', 'ch.hed_prj_id')
                     ->orOn('cp.prj_id', '=', 'c.ctr_hed_id')
                     ->orOn('cp.prj_id', '=', 'ch.hed_id');
            })
            ->where('c.ctr_num', $id)
            ->orderBy('c.ctr_startdt', 'desc')
            ->orderBy('c.ctr_id', 'desc')
            ->select(
                'c.*', 
                'ch.hed_code as ctr_hed_code', 
                'ch.hed_name as ctr_hed_name',
                'cp.prj_title as ctr_prj_title',
                'cp.prj_code as ctr_prj_code'
            )
            ->get()
            ->map(function ($row) {
                $today = Carbon::today();
                if ($today->between(Carbon::parse($row->ctr_startdt), Carbon::parse($row->ctr_enddt))) {
                    $row->status_label = 'Active';
                } elseif (Carbon::parse($row->ctr_enddt)->lt($today)) {
                    $row->status_label = 'Completed';
                } else {
                    $row->status_label = 'Future';
                }
                return $row;
            });

        $firstContract = DB::table('hr.contracts')
            ->where('ctr_num', $id)
            ->orderBy('ctr_startdt', 'asc')
            ->first();
        $lastContract = DB::table('hr.contracts')
            ->where('ctr_num', $id)
            ->orderBy('ctr_startdt', 'desc')
            ->first();

        $ext = DB::table('hr.empsextb')
            ->where('empextb_emp_id', $id)
            ->first();
        $empB = $ext;
        $kin = null;
        $emer = null;
        $kinSame = false;
        if ($ext) {
            $kin = [
                'name' => $ext->emp_nokname ?? null,
                'relation' => $ext->emp_nokrelation ?? null,
                'cnic' => $ext->emp_nokcnic ?? null,
            ];
            $emer = [
                'name' => $ext->emp_emername ?? null,
                'relation' => $ext->emp_emerrelation ?? null,
                'mobile' => $ext->emp_emermobile ?? null,
            ];
            if (!empty($kin['name']) && !empty($emer['name'])) {
                $kinSame = strtolower(trim($kin['name'])) === strtolower(trim($emer['name']))
                    && strtolower(trim($kin['relation'] ?? '')) === strtolower(trim($emer['relation'] ?? ''));
            }
        }

        $empC = DB::table('hr.empsextc')->where('empextc_emp_id', $id)->first();

        $salaryProgression = DB::table('fin.salorders')
            ->where('sor_emp_id', $id)
            ->selectRaw('EXTRACT(YEAR FROM sor_month)::int as yr, SUM(sor_netsalary)::bigint as total')
            ->groupBy('yr')
            ->orderBy('yr')
            ->get();

        $previousProjects = DB::table('hr.contracts as c')
            ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'c.ctr_hed_id')
            ->leftJoin('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
            ->where('c.ctr_num', $id)
            ->whereNotNull('h.hed_prj_id')
            ->select('p.prj_title', 'c.ctr_startdt', 'c.ctr_enddt')
            ->orderBy('c.ctr_startdt', 'desc')
            ->get();

        $degrees = DB::table('hr.qualifs')
            ->where('qlf_emp_id', $id)
            ->whereIn('qlf_type', ['Degree', 'Education'])
            ->orderBy('qlf_enddt', 'desc')
            ->get();

        $certs = DB::table('hr.qualifs')
            ->where('qlf_emp_id', $id)
            ->where('qlf_type', 'Course')
            ->orderBy('qlf_enddt', 'desc')
            ->get();

        $vehicles = DB::table('hr.vehicles')
            ->where('vcl_emp_id', $id)
            ->orderBy('vcl_year', 'desc')
            ->get();

        $devices = DB::table('hr.devices')
            ->where('dvc_emp_id', $id)
            ->orderBy('dvc_id', 'asc')
            ->get();

        $bankAccounts = DB::table('hr.bnkaccounts')
            ->where('bac_emp_id', $id)
            ->orderBy('bac_id', 'asc')
            ->get();

        $jobs = DB::table('hr.jobs')
            ->where('job_emp_id', $id)
            ->orderByRaw('COALESCE(job_to, CURRENT_DATE) DESC')
            ->get();

        $yearsInService = null;
        if ($emp && $emp->emp_joindt) {
            $yearsInService = round(Carbon::parse($emp->emp_joindt)->floatDiffInYears(Carbon::now()), 1);
        }

        $empA = DB::table('hr.empsexta')->where('empexta_emp_id', $id)->first();
        $authUnit = null;
        if (Auth::user() && (Auth::user()->acc_unt_id ?? null)) {
            $authUnit = DB::table('cen.units')->where('unt_id', Auth::user()->acc_unt_id)->first();
        }

        $attachments = DB::table('hr.empattachments')->where('eat_objid', $id)->get();
        $canEdit = $this->checkCanEditEmployee(Auth::user());

        $salaryContracts = DB::table('hr.contracts as c')
            ->where('c.ctr_num', $id)
            ->whereNotNull('c.ctr_startdt')
            ->orderBy('c.ctr_startdt', 'asc')
            ->orderBy('c.ctr_id', 'asc')
            ->get();

        $salaryTimeline = [];
        foreach ($salaryContracts as $sc) {
            $yr = Carbon::parse($sc->ctr_startdt)->format('Y');
            $salaryTimeline[$yr] = [
                'year'     => $yr,
                'salary'   => (float)$sc->ctr_salary,
                'jobtitle' => $sc->ctr_jobtitle ?? '—',
                'date'     => $sc->ctr_startdt,
            ];
        }

        if (count($salaryTimeline) === 1) {
            $firstPoint = reset($salaryTimeline);
            $lastSc = $salaryContracts->last();
            $endYr = $lastSc->ctr_enddt ? Carbon::parse($lastSc->ctr_enddt)->format('Y') : (string)((int)$firstPoint['year'] + 1);
            if ($endYr !== $firstPoint['year']) {
                $salaryTimeline[$endYr] = [
                    'year'     => $endYr,
                    'salary'   => $firstPoint['salary'],
                    'jobtitle' => $firstPoint['jobtitle'],
                    'date'     => $lastSc->ctr_enddt,
                ];
            }
        }
        $salaryTimeline = array_values($salaryTimeline);

        return view('divhr.employee-details', compact(
            'id',
            'emp',
            'empA',
            'empB',
            'empC',
            'authUnit',
            'base',
            'subheads',
            'currentContract',
            'contractsHistory',
            'firstContract',
            'lastContract',
            'kin',
            'emer',
            'kinSame',
            'salaryProgression',
            'salaryTimeline',
            'previousProjects',
            'degrees',
            'certs',
            'vehicles',
            'devices',
            'bankAccounts',
            'jobs',
            'yearsInService',
            'attachments',
            'canEdit',
            'currentContractPlans',
            'distinctPlanCount'
        ));
    }

    public function employeeEdit($id)
    {
        $this->authorizeEmployeeEdit(Auth::user());

        $emp = DB::table('hr.emps')->where('emp_id', $id)->first();
        if (!$emp) {
            abort(404, 'Employee not found');
        }

        $empA = DB::table('hr.empsexta')->where('empexta_emp_id', $id)->first();
        $empB = DB::table('hr.empsextb')->where('empextb_emp_id', $id)->first();
        $empC = DB::table('hr.empsextc')->where('empextc_emp_id', $id)->first();

        $degrees = DB::table('hr.qualifs')
            ->where('qlf_emp_id', $id)
            ->whereIn('qlf_type', ['Degree', 'Education'])
            ->orderBy('qlf_enddt', 'desc')
            ->get();

        $certs = DB::table('hr.qualifs')
            ->where('qlf_emp_id', $id)
            ->where('qlf_type', 'Course')
            ->orderBy('qlf_enddt', 'desc')
            ->get();

        $jobs = DB::table('hr.jobs')
            ->where('job_emp_id', $id)
            ->orderBy('job_from', 'desc')
            ->get();

        $vehicles = DB::table('hr.vehicles')
            ->where('vcl_emp_id', $id)
            ->orderBy('vcl_year', 'desc')
            ->get();

        $devices = DB::table('hr.devices')
            ->where('dvc_emp_id', $id)
            ->orderBy('dvc_id', 'asc')
            ->get();

        $bankAccounts = DB::table('hr.bnkaccounts')
            ->where('bac_emp_id', $id)
            ->orderBy('bac_id', 'asc')
            ->get();

        $departments = DB::table('cen.units')->orderBy('unt_name')->get();
        $heads = DB::table('cen.heads as h')
            ->leftJoin('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
            ->select('h.hed_id', 'h.hed_code', 'h.hed_name', 'h.hed_unt_id', 'p.prj_title', 'p.prj_code')
            ->orderBy('h.hed_code')
            ->get();

        $today = \Carbon\Carbon::today()->toDateString();
        $activePlan = DB::table('hr.contractplans as cp')
            ->join('hr.contracts as c', 'c.ctr_id', '=', 'cp.cpn_ctr_id')
            ->where('c.ctr_num', $id)
            ->whereRaw('? between cp.cpn_startdt and cp.cpn_enddt', [$today])
            ->whereNotNull('cp.cpn_hed_id')
            ->select('cp.cpn_hed_id')
            ->first();

        $currentHeadId = $activePlan ? $activePlan->cpn_hed_id : $emp->emp_hed_id;
        $currentHead = DB::table('cen.heads as h')
            ->leftJoin('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
            ->where('h.hed_id', $currentHeadId)
            ->select('h.hed_id', 'h.hed_code', 'h.hed_name', 'p.prj_title', 'p.prj_code')
            ->first();

        return view('divhr.employee-edit', compact(
            'id',
            'emp',
            'empA',
            'empB',
            'empC',
            'degrees',
            'certs',
            'jobs',
            'vehicles',
            'devices',
            'bankAccounts',
            'departments',
            'heads',
            'currentHead'
        ));
    }

    public function employeeUpdate(Request $request, $id)
    {
        $this->authorizeEmployeeEdit(Auth::user());

        $emp = DB::table('hr.emps')->where('emp_id', $id)->first();
        if (!$emp) {
            abort(404, 'Employee not found');
        }

        $validated = $request->validate([
            // Core
            'emp_name'     => 'required|string|max:200',
            'emp_cnic'     => 'required|string|max:20',
            'emp_joindt'   => 'required|date',
            'emp_unt_id'   => 'required|integer',
            'emp_hed_id'   => 'nullable|integer',
            'emp_status'   => 'required|string|max:50',
            'emp_rank'     => 'nullable|string|max:100',
            'emp_title'    => 'nullable|string|max:255',
            'emp_lastdt'   => 'nullable|date',
            'emp_remarks'  => 'nullable|string',

            // Personal 1 (empsexta)
            'emp_discip'         => 'nullable|string|max:255',
            'emp_qualif'         => 'nullable|integer',
            'emp_spec'           => 'nullable|string|max:255',
            'emp_paddress'       => 'nullable|string|max:500',
            'emp_dob'            => 'nullable|date',
            'emp_marital'        => 'nullable|string|max:50',
            'emp_ntnlty'         => 'nullable|string|max:100',
            'emp_ntnlty_other'   => 'nullable|string|max:100',
            'emp_pob'            => 'nullable|string|max:100',
            'emp_taddress'       => 'nullable|string|max:500',
            'emp_mobile'         => 'nullable|string|max:20',
            'emp_mobile2'        => 'nullable|string|max:20',
            'emp_landline'       => 'nullable|string|max:20',
            'emp_gender'         => 'nullable|string|max:20',
            'emp_email'          => 'nullable|string|max:150',
            'emp_father'         => 'nullable|string|max:200',
            'emp_father_cnic'    => 'nullable|string|max:20',

            // Personal 2 (empsextb)
            'emp_nokname'        => 'nullable|string|max:200',
            'emp_nokrelation'    => 'nullable|string|max:100',
            'emp_nokcnic'        => 'nullable|string|max:20',
            'emp_emername'       => 'nullable|string|max:200',
            'emp_emerrelation'   => 'nullable|string|max:100',
            'emp_emermobile'     => 'nullable|string|max:20',
            'emp_idmark'         => 'nullable|string|max:255',
            'emp_height'         => 'nullable|numeric',
            'emp_caste'          => 'nullable|string|max:100',
            'emp_religion'       => 'nullable|string|max:100',
            'emp_sect'           => 'nullable|string|max:100',
            'emp_police'         => 'nullable|string|max:200',
            'emp_political'      => 'nullable|string|max:200',

            // Official (empsextc)
            'emp_cnum'           => 'nullable|string|max:100',
            'emp_cissuedt'       => 'nullable|date',
            'emp_cexpdt'         => 'nullable|date',
            'emp_secclear'       => 'nullable|string|max:100',

            // Multi-row arrays
            'degrees'            => 'nullable|array',
            'certs'              => 'nullable|array',
            'jobs'               => 'nullable|array',
            'vehicles'           => 'nullable|array',
            'devices'            => 'nullable|array',
            'bank_accounts'      => 'nullable|array',
        ]);

        DB::transaction(function () use ($id, $request, $validated) {
            $cleanCnic = function (?string $val): ?string {
                if (empty($val)) return null;
                $val = trim($val);
                $digits = preg_replace('/\D/', '', $val);
                if (strlen($digits) === 13) {
                    return substr($digits, 0, 5) . '-' . substr($digits, 5, 7) . '-' . substr($digits, 12, 1);
                }
                return mb_substr($val, 0, 15);
            };

            $cleanPhone = function (?string $val, int $max = 13): ?string {
                if (empty($val)) return null;
                return mb_substr(trim($val), 0, $max);
            };

            // 1. Update Core (hr.emps)
            DB::table('hr.emps')->where('emp_id', $id)->update([
                'emp_name'     => mb_substr($validated['emp_name'], 0, 200),
                'emp_cnic'     => $cleanCnic($validated['emp_cnic']) ?? mb_substr($validated['emp_cnic'], 0, 15),
                'emp_joindt'   => $validated['emp_joindt'],
                'emp_unt_id'   => (int)$validated['emp_unt_id'],
                'emp_hed_id'   => $emp->emp_hed_id,
                'emp_status'   => $validated['emp_status'],
                'emp_rank'     => !empty($validated['emp_rank']) ? mb_substr($validated['emp_rank'], 0, 100) : null,
                'emp_title'    => !empty($validated['emp_title']) ? mb_substr($validated['emp_title'], 0, 255) : null,
                'emp_lastdt'   => !empty($validated['emp_lastdt']) ? $validated['emp_lastdt'] : null,
                'emp_remarks'  => $validated['emp_remarks'] ?? null,
                'emp_locked'   => $request->has('emp_locked') ? true : false,
                'emp_cleared'  => $request->has('emp_cleared') ? true : false,
            ]);

            // 2. Upsert Personal 1 (hr.empsexta)
            DB::table('hr.empsexta')->updateOrInsert(
                ['empexta_emp_id' => $id],
                [
                    'emp_discip'         => $validated['emp_discip'] ?? '',
                    'emp_qualif'         => (int)($validated['emp_qualif'] ?? 0),
                    'emp_spec'           => $validated['emp_spec'] ?? null,
                    'emp_paddress'       => $validated['emp_paddress'] ?? '',
                    'emp_dob'            => !empty($validated['emp_dob']) ? $validated['emp_dob'] : '1990-01-01',
                    'emp_marital'        => $validated['emp_marital'] ?? 'Single',
                    'emp_ntnlty'         => $validated['emp_ntnlty'] ?? 'Pakistani',
                    'emp_ntnlty_other'   => $validated['emp_ntnlty_other'] ?? null,
                    'emp_pob'            => $validated['emp_pob'] ?? '',
                    'emp_taddress'       => $validated['emp_taddress'] ?? '',
                    'emp_mobile'         => $cleanPhone($validated['emp_mobile'] ?? null, 13) ?? '',
                    'emp_mobile2'        => $cleanPhone($validated['emp_mobile2'] ?? null, 13),
                    'emp_landline'       => $cleanPhone($validated['emp_landline'] ?? null, 13),
                    'emp_gender'         => $validated['emp_gender'] ?? 'Male',
                    'emp_email'          => $validated['emp_email'] ?? '',
                    'emp_father'         => $validated['emp_father'] ?? '',
                    'emp_father_cnic'    => $cleanCnic($validated['emp_father_cnic'] ?? null),
                ]
            );

            // 3. Upsert Personal 2 (hr.empsextb)
            DB::table('hr.empsextb')->updateOrInsert(
                ['empextb_emp_id' => $id],
                [
                    'emp_nokname'        => $validated['emp_nokname'] ?? '',
                    'emp_nokrelation'    => $validated['emp_nokrelation'] ?? '',
                    'emp_nokcnic'        => $cleanCnic($validated['emp_nokcnic'] ?? null) ?? '',
                    'emp_emername'       => $validated['emp_emername'] ?? '',
                    'emp_emerrelation'   => $validated['emp_emerrelation'] ?? '',
                    'emp_emermobile'     => $cleanPhone($validated['emp_emermobile'] ?? null, 20) ?? '',
                    'emp_idmark'         => $validated['emp_idmark'] ?? '',
                    'emp_height'         => (float)($validated['emp_height'] ?? 0),
                    'emp_caste'          => $validated['emp_caste'] ?? '',
                    'emp_religion'       => $validated['emp_religion'] ?? '',
                    'emp_sect'           => $validated['emp_sect'] ?? '',
                    'emp_police'         => $validated['emp_police'] ?? '',
                    'emp_political'      => $validated['emp_political'] ?? '',
                ]
            );

            // 4. Upsert Official (hr.empsextc)
            DB::table('hr.empsextc')->updateOrInsert(
                ['empextc_emp_id' => $id],
                [
                    'emp_cnum'           => $validated['emp_cnum'] ?? null,
                    'emp_cissuedt'       => !empty($validated['emp_cissuedt']) ? $validated['emp_cissuedt'] : null,
                    'emp_cexpdt'         => !empty($validated['emp_cexpdt']) ? $validated['emp_cexpdt'] : null,
                    'emp_secclear'       => $validated['emp_secclear'] ?? null,
                ]
            );

            // 5. Sync Education (Degrees)
            DB::table('hr.qualifs')->where('qlf_emp_id', $id)->whereIn('qlf_type', ['Degree', 'Education'])->delete();
            if (!empty($validated['degrees'])) {
                foreach ($validated['degrees'] as $deg) {
                    if (!empty($deg['qlf_name']) || !empty($deg['qlf_inst'])) {
                        DB::table('hr.qualifs')->insert([
                            'qlf_emp_id'   => $id,
                            'qlf_type'     => 'Degree',
                            'qlf_level'    => (int)($deg['qlf_level'] ?? 0),
                            'qlf_name'     => $deg['qlf_name'] ?? '',
                            'qlf_inst'     => $deg['qlf_inst'] ?? '',
                            'qlf_duration' => (float)($deg['qlf_duration'] ?? 1),
                            'qlf_unit'     => $deg['qlf_unit'] ?? 'Years',
                            'qlf_enddt'    => !empty($deg['qlf_enddt']) ? $deg['qlf_enddt'] : date('Y-m-d'),
                            'qlf_grade'    => $deg['qlf_grade'] ?? null,
                            'qlf_license'  => $deg['qlf_license'] ?? null,
                            'qlf_spec'     => $deg['qlf_spec'] ?? null,
                        ]);
                    }
                }
            }

            // 6. Sync Courses & Certifications
            DB::table('hr.qualifs')->where('qlf_emp_id', $id)->where('qlf_type', 'Course')->delete();
            if (!empty($validated['certs'])) {
                foreach ($validated['certs'] as $ct) {
                    if (!empty($ct['qlf_name']) || !empty($ct['qlf_inst'])) {
                        DB::table('hr.qualifs')->insert([
                            'qlf_emp_id'   => $id,
                            'qlf_type'     => 'Course',
                            'qlf_level'    => (int)($ct['qlf_level'] ?? 0),
                            'qlf_name'     => $ct['qlf_name'] ?? '',
                            'qlf_inst'     => $ct['qlf_inst'] ?? '',
                            'qlf_duration' => (float)($ct['qlf_duration'] ?? 1),
                            'qlf_unit'     => $ct['qlf_unit'] ?? 'Months',
                            'qlf_enddt'    => !empty($ct['qlf_enddt']) ? $ct['qlf_enddt'] : date('Y-m-d'),
                            'qlf_grade'    => $ct['qlf_grade'] ?? null,
                            'qlf_license'  => $ct['qlf_license'] ?? null,
                            'qlf_spec'     => $ct['qlf_spec'] ?? null,
                        ]);
                    }
                }
            }

            // 7. Sync Career (hr.jobs)
            DB::table('hr.jobs')->where('job_emp_id', $id)->delete();
            if (!empty($validated['jobs'])) {
                foreach ($validated['jobs'] as $jb) {
                    if (!empty($jb['job_company']) || !empty($jb['job_jobtitle'])) {
                        DB::table('hr.jobs')->insert([
                            'job_emp_id'   => $id,
                            'job_company'  => $jb['job_company'] ?? '',
                            'job_jobtitle' => $jb['job_jobtitle'] ?? '',
                            'job_repto'    => $jb['job_repto'] ?? null,
                            'job_team'     => !empty($jb['job_team']) ? (int)$jb['job_team'] : null,
                            'job_from'     => !empty($jb['job_from']) ? $jb['job_from'] : date('Y-m-d'),
                            'job_to'       => !empty($jb['job_to']) ? $jb['job_to'] : null,
                            'job_resp'     => $jb['job_resp'] ?? null,
                            'job_ach'      => $jb['job_ach'] ?? null,
                            'job_city'     => $jb['job_city'] ?? 'Karachi',
                        ]);
                    }
                }
            }

            // 8. Sync Vehicles (hr.vehicles)
            DB::table('hr.vehicles')->where('vcl_emp_id', $id)->delete();
            if (!empty($validated['vehicles'])) {
                foreach ($validated['vehicles'] as $v) {
                    if (!empty($v['vcl_maker']) || !empty($v['vcl_regis'])) {
                        DB::table('hr.vehicles')->insert([
                            'vcl_emp_id'  => $id,
                            'vcl_type'    => $v['vcl_type'] ?? 'Car',
                            'vcl_maker'   => $v['vcl_maker'] ?? '',
                            'vcl_variant' => $v['vcl_variant'] ?? '',
                            'vcl_year'    => (int)($v['vcl_year'] ?? date('Y')),
                            'vcl_regis'   => $v['vcl_regis'] ?? '',
                            'vcl_color'   => $v['vcl_color'] ?? '',
                        ]);
                    }
                }
            }

            // 9. Sync Devices (hr.devices)
            DB::table('hr.devices')->where('dvc_emp_id', $id)->delete();
            if (!empty($validated['devices'])) {
                foreach ($validated['devices'] as $d) {
                    if (!empty($d['dvc_brand']) || !empty($d['dvc_imei1'])) {
                        DB::table('hr.devices')->insert([
                            'dvc_emp_id' => $id,
                            'dvc_type'   => $d['dvc_type'] ?? 'Mobile Phone',
                            'dvc_brand'  => $d['dvc_brand'] ?? '',
                            'dvc_model'  => $d['dvc_model'] ?? '',
                            'dvc_imei1'  => mb_substr($d['dvc_imei1'] ?? '', 0, 18),
                            'dvc_imei2'  => !empty($d['dvc_imei2']) ? mb_substr($d['dvc_imei2'], 0, 18) : null,
                        ]);
                    }
                }
            }

            // 10. Sync Bank Accounts (hr.bnkaccounts)
            DB::table('hr.bnkaccounts')->where('bac_emp_id', $id)->delete();
            if (!empty($validated['bank_accounts'])) {
                foreach ($validated['bank_accounts'] as $ba) {
                    if (!empty($ba['bac_bnkname']) || !empty($ba['bac_accnum'])) {
                        DB::table('hr.bnkaccounts')->insert([
                            'bac_emp_id'    => $id,
                            'bac_bnkname'   => $ba['bac_bnkname'] ?? '',
                            'bac_bchname'   => $ba['bac_bchname'] ?? '',
                            'bac_bchcode'   => $ba['bac_bchcode'] ?? null,
                            'bac_acctitle'  => $ba['bac_acctitle'] ?? '',
                            'bac_accnum'    => $ba['bac_accnum'] ?? '',
                            'bac_bchcity'   => $ba['bac_bchcity'] ?? 'Karachi',
                            'bac_selforpay' => !empty($ba['bac_selforpay']) ? true : false,
                        ]);
                    }
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Employee profile updated successfully.',
            'redirect_url' => route('divhr.employeedetail', $id),
        ]);
    }

    public function checkCanEditEmployee($user): bool
    {
        if (!$user) return false;
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHr = in_array($area, ['hr']) || (method_exists($user, 'canAccessArea') && $user->canAccessArea('hr'));
        $isDivision = $area === 'prj' || (method_exists($user, 'isDivision') && $user->isDivision());

        return $isHr || $isDivision;
    }

    public function authorizeEmployeeEdit($user)
    {
        if (!$this->checkCanEditEmployee($user)) {
            abort(403, 'Unauthorized access. Only Division and HR personnel have edit access to Employee Profiles.');
        }
    }

    /**
     * Upload / update employee photo (hr.emps.emp_photodest).
     */
    public function uploadPhoto(Request $request, $id)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp,gif,jfif|max:10240',
        ]);

        $emp = Employee::withoutGlobalScopes()->where('emp_id', $id)->first();
        if (!$emp) {
            $emp = DB::table('hr.emps')->where('emp_id', $id)->first();
            if (!$emp) {
                abort(404, 'Employee not found');
            }
        }

        $oldPath = $emp->emp_photodest ?? null;
        if (!empty($oldPath)) {
            app(\App\Services\FileStorageService::class)->delete($oldPath);
        }

        // Legacy format: pht-emp-{emp_id}.jpg in hr/photos/
        $path = app(\App\Services\FileStorageService::class)->store(
            $request->file('photo'),
            'hr/photos',
            'pht-emp-',
            (string) ($emp->emp_id ?? $id)
        );

        DB::table('hr.emps')->where('emp_id', $id)->update(['emp_photodest' => $path]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Employee photo updated successfully.',
                'path' => $path,
                'url' => \App\Facades\FileStorage::url($path),
            ]);
        }

        return redirect()->back()->with('success', 'Employee photo updated successfully.');
    }

    public function attendance(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        
        // Mode handling with Session Persistence
        if ($request->has('mode')) {
            $mode = $request->query('mode') === 's' ? 's' : 'm';
            session(['hr_mode' => $mode]);
        } else {
            $defaultMode = in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs']) ? 'm' : 's';
            $mode = session('hr_mode', $defaultMode);
        }

        if ($mode === 's') {
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
        } else {
            $lower = 0;
            $upper = 99999999;
        }

        $monthStr = $request->input('month', Carbon::now()->format('Y-m'));
        $first = Carbon::parse($monthStr.'-01')->startOfMonth();
        $last = $first->copy()->endOfMonth();
        $days = (int) $first->daysInMonth;

        // Pull active employees bounded by mode
        $rows = collect(DB::select("
            SELECT e.emp_id, e.emp_name, 
                   a.att_id, a.att_emp_id, a.att_empnamecomp, a.att_unt_id,
                   a.att_startdt, a.att_enddt,
                   a.att_1, a.att_2, a.att_3, a.att_4, a.att_5, a.att_6, a.att_7, a.att_8, a.att_9, a.att_10,
                   a.att_11, a.att_12, a.att_13, a.att_14, a.att_15, a.att_16, a.att_17, a.att_18, a.att_19, a.att_20,
                   a.att_21, a.att_22, a.att_23, a.att_24, a.att_25, a.att_26, a.att_27, a.att_28, a.att_29, a.att_30, a.att_31
            FROM hr.emps e
            LEFT JOIN LATERAL (
                SELECT *
                FROM hr.attendance a
                WHERE a.att_emp_id = e.emp_id::text
                  AND a.att_startdt <= ?::date
                  AND a.att_enddt >= ?::date
                ORDER BY a.att_startdt DESC, a.att_id DESC
                LIMIT 1
            ) a ON TRUE
            WHERE e.emp_unt_id BETWEEN ? AND ?
              AND LOWER(e.emp_status) IN ('active', 'current')
            ORDER BY e.emp_name ASC
        ", [$last->toDateString(), $first->toDateString(), $lower, $upper]));

        $data = $rows->map(function($r) use ($days) {
            $vals = [];
            for ($d=1;$d<=31;$d++) {
                $col = 'att_'.$d;
                $vals[$d] = isset($r->$col) && $r->$col !== null ? strtoupper($r->$col) : null;
            }
            $present = 0;
            for ($d=1;$d<=$days;$d++) {
                $v = $vals[$d];
                if ($v === 'P') $present++;
            }
            return [
                'emp_id' => $r->emp_id,
                'name' => $r->emp_name,
                'vals' => $vals,
                'present' => $present,
                'percent' => $days>0 ? round($present*100/$days) : 0
            ];
        });
        $weekdays = [];
        for ($d=1;$d<=$days;$d++) {
            $weekdays[$d] = $first->copy()->addDays($d-1)->format('D');
        }
        return view('divhr.attendance', [
            'mode' => $mode,
            'month' => $first->format('Y-m'),
            'first' => $first->toDateString(),
            'last' => $last->toDateString(),
            'days' => $days,
            'list' => $data,
            'weekdays' => $weekdays
        ]);
    }

    public function attendanceSave(Request $request)
    {
        $monthStr = $request->input('month');
        if (!$monthStr) return redirect()->route('divhr.attendance');
        $first = Carbon::parse($monthStr.'-01')->startOfMonth();
        $last = $first->copy()->endOfMonth();
        $payload = json_decode((string)$request->input('payload_json','[]'), true) ?: [];
        $unitId = Auth::user() ? (Auth::user()->acc_unt_id ?? (Auth::user()->unit->unt_id ?? null)) : null;
        $byEmp = [];
        foreach ($payload as $row) {
            $eid = (string)($row['emp_id'] ?? '');
            $day = (int)($row['day'] ?? 0);
            $val = strtoupper((string)($row['val'] ?? ''));
            if ($val === 'CL') $val = 'C';
            if ($eid === '' || $day < 1 || $day > 31) continue;
            if (!isset($byEmp[$eid])) $byEmp[$eid] = [];
            $byEmp[$eid][$day] = ($val === '' ? null : substr($val,0,1));
        }
        if (empty($byEmp)) return redirect()->route('divhr.attendance', ['month'=>$monthStr]);
        foreach ($byEmp as $eid => $changes) {
            $emp = DB::table('hr.emps')->where('emp_id', $eid)->first();
            if (!$emp) continue;
            $row = DB::table('hr.attendance')
                ->where('att_emp_id', (string)$eid)
                ->where('att_startdt', $first->toDateString())
                ->where('att_enddt', $last->toDateString())
                ->first();
            if (!$row) {
                DB::table('hr.attendance')->insert([
                    'att_emp_id' => (string)$eid,
                    'att_empnamecomp' => $emp->emp_name,
                    'att_unt_id' => $unitId ?? $emp->emp_unt_id,
                    'att_startdt' => $first->toDateString(),
                    'att_enddt' => $last->toDateString()
                ]);
            }
            $upd = [];
            foreach ($changes as $d=>$v) {
                $upd['att_'.$d] = $v;
            }
            if (!empty($upd)) {
                DB::table('hr.attendance')
                    ->where('att_emp_id', (string)$eid)
                    ->where('att_startdt', $first->toDateString())
                    ->where('att_enddt', $last->toDateString())
                    ->update($upd);
            }
        }
        return redirect()->route('divhr.attendance', ['month'=>$monthStr]);
    }

    public function initiateContract()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        return view('divhr.initiate-contract');
    }

    // ====================================================
    // HR REPORTS (Unified Single Page - like Finance Reports)
    // ====================================================

    private function getEmployeeBaseQuery($unitId = 'All')
    {
        $user = Auth::user();

        $query = Employee::query()
            ->leftJoin('cen.heads as h', 'hr.emps.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'hr.emps.emp_unt_id')
            ->leftJoin('hr.empsexta as ea', 'ea.empexta_emp_id', '=', 'hr.emps.emp_id')
            ->leftJoin('hr.empsextb as eb', 'eb.empextb_emp_id', '=', 'hr.emps.emp_id')
            ->select(
                'hr.emps.*',
                'h.hed_code',
                'u.unt_name',
                'ea.emp_discip', 'ea.emp_qualif', 'ea.emp_spec',
                'ea.emp_paddress', 'ea.emp_dob', 'ea.emp_marital',
                'ea.emp_ntnlty', 'ea.emp_pob', 'ea.emp_taddress',
                'ea.emp_mobile', 'ea.emp_landline', 'ea.emp_gender',
                'ea.emp_mobile2', 'ea.emp_email', 'ea.emp_father',
                'ea.emp_father_cnic', 'ea.emp_ntnlty_other',
                'eb.emp_nokname', 'eb.emp_nokrelation', 'eb.emp_nokcnic',
                'eb.emp_emername', 'eb.emp_emerrelation', 'eb.emp_emermobile',
                'eb.emp_idmark', 'eb.emp_height', 'eb.emp_caste',
                'eb.emp_religion', 'eb.emp_sect', 'eb.emp_police', 'eb.emp_political'
            );

        if ($unitId && $unitId !== 'All') {
            $query->where('emp_unt_id', $unitId);
        }

        return $query->orderBy('emp_name', 'asc');
    }

    public function hrReportsIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        if ($request->has('mode')) {
            $mode = $request->query('mode') === 's' ? 's' : 'm';
            session(['hr_mode' => $mode]);
        } else {
            $defaultMode = in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs']) ? 'm' : 's';
            $mode = session('hr_mode', $defaultMode);
        }

        if ($mode === 's') {
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
            $units = DB::table('cen.units')
                ->whereBetween('unt_id', [$lower, $upper])
                ->orderBy('unt_name')
                ->get(['unt_id', 'unt_name']);
        } else {
            $units = DB::table('cen.units')
                ->where('unt_type', 'Division')
                ->orderBy('unt_name')
                ->get(['unt_id', 'unt_name']);
        }

        return view('divhr.reports', compact('units', 'mode'));
    }

    public function hrReportsData(Request $request)
    {
        $user = Auth::user();
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));

        if ($request->has('mode')) {
            $mode = $request->query('mode') === 's' ? 's' : 'm';
            session(['hr_mode' => $mode]);
        } else {
            $defaultMode = in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs']) ? 'm' : 's';
            $mode = session('hr_mode', $defaultMode);
        }

        $type = $request->query('type');
        $status = $request->query('status', 'Current');
        $unitId = $request->query('unit_id', 'All');

        $q = $this->getEmployeeBaseQuery($unitId);

        if ($mode === 's' && ($unitId === 'All' || empty($unitId)) && $user) {
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
            $q->whereBetween('hr.emps.emp_unt_id', [$lower, $upper]);
        }

        if ($status === 'Current') {
            $q->whereRaw("LOWER(emp_status) IN ('active','current')");
        } elseif ($status === 'Previous') {
            $q->whereRaw("LOWER(emp_status) NOT IN ('active','current')");
        }

        $employees = $q->get();
        $empIds = $employees->pluck('emp_id')->toArray();

        $data = [];

        $today = Carbon::today()->toDateString();
        // Helper to fetch latest/active contracts for employees and resolve current project assignment
        $getLatestContracts = function() use ($empIds, $today) {
            $rawContracts = DB::table('hr.contracts as c')
                ->leftJoin('cen.heads as ch', 'ch.hed_id', '=', 'c.ctr_hed_id')
                ->leftJoin('prj.projects as cp', function ($join) {
                    $join->on('cp.prj_id', '=', 'ch.hed_prj_id')
                         ->orOn('cp.prj_id', '=', 'c.ctr_hed_id')
                         ->orOn('cp.prj_id', '=', 'ch.hed_id');
                })
                ->whereIn('c.ctr_num', $empIds)
                ->select(
                    'c.*', 
                    'ch.hed_code as ctr_hed_code',
                    'ch.hed_name as ctr_hed_name',
                    'cp.prj_code as ctr_prj_code',
                    'cp.prj_title as ctr_prj_title'
                )
                ->orderBy('c.ctr_startdt', 'desc')
                ->orderBy('c.ctr_id', 'desc')
                ->get();

            $grouped = $rawContracts->groupBy('ctr_num');
            $latest = collect();
            $activeIds = [];

            foreach ($grouped as $num => $ctrs) {
                $act = $ctrs->first(function($c) use ($today) {
                    $end = $c->ctr_termindt ?: $c->ctr_enddt;
                    return $c->ctr_startdt <= $today && (!$end || $end >= $today);
                }) ?: $ctrs->first();

                if ($act) {
                    $latest->put($num, $act);
                    $activeIds[] = $act->ctr_id;
                }
            }

            if (!empty($activeIds)) {
                $plans = DB::table('hr.contractplans as p')
                    ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'p.cpn_hed_id')
                    ->leftJoin('prj.projects as prj', function ($join) {
                        $join->on('prj.prj_id', '=', 'h.hed_prj_id')
                             ->orOn('prj.prj_id', '=', 'p.cpn_hed_id')
                             ->orOn('prj.prj_id', '=', 'h.hed_id');
                    })
                    ->whereIn('p.cpn_ctr_id', $activeIds)
                    ->select('p.*', 'h.hed_code', 'h.hed_name', 'prj.prj_code', 'prj.prj_title')
                    ->get()
                    ->groupBy('cpn_ctr_id');

                foreach ($latest as $num => $ctr) {
                    $cPlans = $plans->get($ctr->ctr_id, collect());
                    $curPlan = $cPlans->first(fn($p) => $today >= $p->cpn_startdt && $today <= $p->cpn_enddt) ?: $cPlans->first();
                    if ($curPlan) {
                        $ctr->ctr_hed_code = $curPlan->hed_code ?: ($curPlan->prj_code ?: $ctr->ctr_hed_code);
                        $ctr->ctr_prj_title = $curPlan->prj_title ?: ($curPlan->hed_name ?: $ctr->ctr_prj_title);
                    }
                }
            }

            return $latest;
        };

        switch ($type) {
            case 'incomplete_data':
                $requiredFields = [
                    'emp_cnic' => 'CNIC', 'emp_joindt' => 'Join Date', 'emp_dob' => 'Date of Birth',
                    'emp_mobile' => 'Mobile', 'emp_email' => 'Email', 'emp_father' => 'Father Name',
                    'emp_paddress' => 'Permanent Address', 'emp_taddress' => 'Temporary Address',
                    'emp_gender' => 'Gender', 'emp_marital' => 'Marital Status', 'emp_ntnlty' => 'Nationality',
                    'emp_pob' => 'Place of Birth', 'emp_discip' => 'Discipline',
                    'emp_nokname' => 'Next of Kin', 'emp_nokcnic' => 'NOK CNIC',
                    'emp_emername' => 'Emergency Contact', 'emp_emermobile' => 'Emergency Mobile',
                ];
                foreach ($employees as $emp) {
                    $missing = [];
                    foreach ($requiredFields as $f => $l) {
                        $v = $emp->$f ?? null;
                        if (is_null($v) || trim((string)$v) === '' || trim((string)$v) === 'N/A') $missing[] = $l;
                    }
                    if (count($missing) > 0) {
                        $data[] = [
                            'emp_name' => $emp->emp_name,
                            'emp_id' => $emp->emp_id,
                            'emp_status' => in_array(strtolower($emp->emp_status ?? ''), ['active','current']) ? 'Current' : 'Previous',
                            'emp_joindt' => $emp->emp_joindt,
                            'missing_count' => count($missing),
                            'missing_fields' => implode(', ', $missing),
                        ];
                    }
                }
                usort($data, fn($a, $b) => $b['missing_count'] - $a['missing_count']);
                break;

            case 'grades':
                $contracts = $getLatestContracts();

                foreach ($employees as $emp) {
                    $ctr = $contracts->get($emp->emp_id);
                    $data[] = [
                        'emp_name' => $emp->emp_name,
                        'emp_id' => $emp->emp_id,
                        'emp_status' => in_array(strtolower($emp->emp_status ?? ''), ['active','current']) ? 'Current' : 'Previous',
                        'emp_joindt' => $emp->emp_joindt,
                        'grade' => $ctr->ctr_grade ?? '—',
                        'salary' => $ctr && $ctr->ctr_salary ? number_format($ctr->ctr_salary) : '—',
                        'job_title' => $ctr->ctr_jobtitle ?? '—',
                        'ctr_start' => $ctr ? $ctr->ctr_startdt : null,
                        'ctr_end' => $ctr ? $ctr->ctr_enddt : null,
                        'head_code' => $ctr->ctr_hed_code ?? ($emp->hed_code ?? '—'),
                    ];
                }
                break;

            case 'qualifications':
                $qualifications = DB::table('hr.qualifs')
                    ->whereIn('qlf_emp_id', $empIds)
                    ->orderBy('qlf_emp_id')->orderBy('qlf_enddt', 'desc')
                    ->get()->groupBy('qlf_emp_id');

                foreach ($employees as $emp) {
                    $qlfs = $qualifications->get($emp->emp_id, collect());
                    $qlfList = [];
                    foreach ($qlfs as $q) {
                        $qlfList[] = [
                            'qlf_type' => $q->qlf_type ?? '—',
                            'qlf_name' => $q->qlf_name ?? '—',
                            'qlf_inst' => $q->qlf_inst ?? '—',
                            'qlf_duration' => ($q->qlf_duration ?? '—') . ' ' . ($q->qlf_unit ?? ''),
                            'qlf_grade' => $q->qlf_grade ?? '—',
                            'qlf_enddt' => $q->qlf_enddt ?? null,
                        ];
                    }

                    $data[] = [
                        'emp_name' => $emp->emp_name,
                        'emp_id' => $emp->emp_id,
                        'emp_status' => in_array(strtolower($emp->emp_status ?? ''), ['active','current']) ? 'Current' : 'Previous',
                        'emp_joindt' => $emp->emp_joindt,
                        'total_qualifs' => count($qlfList),
                        'qualifications_list' => $qlfList,
                    ];
                }
                break;

            case 'current_employees':
                $contracts = $getLatestContracts();

                foreach ($employees as $emp) {
                    $ctr = $contracts->get($emp->emp_id);
                    $data[] = [
                        'emp_name' => $emp->emp_name,
                        'emp_id' => $emp->emp_id,
                        'emp_cnic' => $emp->emp_cnic,
                        'emp_status' => in_array(strtolower($emp->emp_status ?? ''), ['active','current']) ? 'Current' : 'Previous',
                        'emp_joindt' => $emp->emp_joindt,
                        'emp_lastdt' => $emp->emp_lastdt,
                        'grade' => $ctr->ctr_grade ?? '—',
                        'salary' => $ctr && $ctr->ctr_salary ? number_format($ctr->ctr_salary) : '—',
                        'ctr_start' => $ctr ? $ctr->ctr_startdt : null,
                        'ctr_end' => $ctr ? $ctr->ctr_enddt : null,
                        'job_title' => $ctr->ctr_jobtitle ?? '—',
                        'head_code' => $ctr->ctr_hed_code ?? ($emp->hed_code ?? '—'),
                        'unit' => $emp->unt_name ?? '—',
                    ];
                }
                break;

            case 'retired_servicemen':
                $lastContracts = $getLatestContracts();

                foreach ($employees as $emp) {
                    $ctr = $lastContracts->get($emp->emp_id);
                    $data[] = [
                        'emp_name' => $emp->emp_name,
                        'emp_id' => $emp->emp_id,
                        'emp_status' => ucfirst($emp->emp_status ?? '—'),
                        'emp_joindt' => $emp->emp_joindt,
                        'emp_lastdt' => $emp->emp_lastdt,
                        'last_grade' => $ctr->ctr_grade ?? '—',
                        'last_salary' => $ctr && $ctr->ctr_salary ? number_format($ctr->ctr_salary) : '—',
                        'job_title' => $ctr->ctr_jobtitle ?? '—',
                        'ctr_start' => $ctr ? $ctr->ctr_startdt : null,
                        'ctr_end' => $ctr ? $ctr->ctr_enddt : null,
                        'remarks' => $emp->emp_remarks ?? '—',
                    ];
                }
                break;

            case 'mobphones':
                foreach ($employees as $emp) {
                    $data[] = [
                        'emp_name' => $emp->emp_name,
                        'emp_id' => $emp->emp_id,
                        'emp_status' => in_array(strtolower($emp->emp_status ?? ''), ['active','current']) ? 'Current' : 'Previous',
                        'emp_joindt' => $emp->emp_joindt,
                        'mobile1' => $emp->emp_mobile ?? '—',
                        'mobile2' => $emp->emp_mobile2 ?? '—',
                        'landline' => $emp->emp_landline ?? '—',
                        'email' => $emp->emp_email ?? '—',
                    ];
                }
                break;

            case 'custom':
                $allContracts = DB::table('hr.contracts as c')
                    ->leftJoin('cen.heads as ch', 'ch.hed_id', '=', 'c.ctr_hed_id')
                    ->leftJoin('prj.projects as cp', function ($join) {
                        $join->on('cp.prj_id', '=', 'ch.hed_prj_id')
                             ->orOn('cp.prj_id', '=', 'c.ctr_hed_id')
                             ->orOn('cp.prj_id', '=', 'ch.hed_id');
                    })
                    ->whereIn('c.ctr_num', $empIds)
                    ->select(
                        'c.*', 
                        'ch.hed_code as ctr_hed_code',
                        'ch.hed_name as ctr_hed_name',
                        'cp.prj_code as ctr_prj_code',
                        'cp.prj_title as ctr_prj_title'
                    )
                    ->orderBy('c.ctr_num')->orderBy('c.ctr_startdt', 'asc')
                    ->get();

                $contractsByEmp = [];
                foreach ($allContracts as $ctr) {
                    $contractsByEmp[$ctr->ctr_num][] = $ctr;
                }

                foreach ($contractsByEmp as $empId => &$ctrs) {
                    $prev = null;
                    foreach ($ctrs as &$c) {
                        if ($prev && $prev->ctr_salary > 0) {
                            $c->pct = round((($c->ctr_salary - $prev->ctr_salary) / $prev->ctr_salary) * 100, 1);
                        } else {
                            $c->pct = null;
                        }
                        $prev = $c;
                    }
                }
                unset($ctrs, $c);

                foreach ($employees as $emp) {
                    $ctrs = $contractsByEmp[$emp->emp_id] ?? [];
                    $totalCtrs = count($ctrs);
                    $latestCtr = !empty($ctrs) ? end($ctrs) : null;

                    $ctrList = [];
                    foreach ($ctrs as $ci => $c) {
                        $ctrList[] = [
                            'no' => ($ci + 1) . ' of ' . $totalCtrs,
                            'grade' => $c->ctr_grade ?? '—',
                            'salary' => $c->ctr_salary ? number_format($c->ctr_salary) : '—',
                            'pct_increase' => $c->pct !== null ? ($c->pct >= 0 ? '+' : '') . $c->pct . '%' : '—',
                            'ctr_start' => $c->ctr_startdt,
                            'ctr_end' => $c->ctr_enddt,
                            'ctr_jobtitle' => $c->ctr_jobtitle ?? '—',
                            'head_code' => $c->ctr_hed_code ?: ($c->ctr_prj_code ?: '—'),
                        ];
                    }

                    $data[] = [
                        'emp_name' => $emp->emp_name,
                        'emp_id' => $emp->emp_id,
                        'emp_cnic' => $emp->emp_cnic,
                        'emp_status' => in_array(strtolower($emp->emp_status ?? ''), ['active','current']) ? 'Current' : 'Previous',
                        'emp_joindt' => $emp->emp_joindt,
                        'emp_lastdt' => $emp->emp_lastdt,
                        'unit' => $emp->unt_name ?? '—',
                        'total_contracts' => $totalCtrs,
                        'current_grade' => $latestCtr ? ($latestCtr->ctr_grade ?? '—') : '—',
                        'current_salary' => $latestCtr && $latestCtr->ctr_salary ? number_format($latestCtr->ctr_salary) : '—',
                        'contracts_history' => $ctrList,
                    ];
                }
                break;

            default:
                return response()->json(['data' => [], 'error' => 'Unknown report type'], 400);
        }

        return response()->json(['data' => $data, 'count' => count($data)]);
    }

    protected function isGlobalHrViewer($user): bool
    {
        if (!$user) return false;
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        return in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs', 'proc', 'prc', 'it'])
            || session('impersonated_by_god')
            || strtolower($user->acc_username ?? '') === 'superadminrdw';
    }
}
