<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Purchase;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        return view('index');
    }

    public function divisionData()
    {
        $user = Auth::user();
        if (!$user) return response()->json(['error' => 'Unauthenticated'], 401);

        $unitId = $user->acc_unt_id;
        $finService = app(\App\Services\FinancialIntelligenceService::class);

        $isCentralMyDept = method_exists($user, 'isCentralDepartment') && $user->isCentralDepartment() && $user->isMyDepartmentMode();

        // 1. Fetch Heads & Projects
        if ($isCentralMyDept) {
            // Central Department in My Department mode: funded via CSRF (Central Service Resource Fund)
            $heads = DB::table('cen.heads as h')
                ->join('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
                ->join('fin.sharesalloc as s', 's.sha_hed_id', '=', 'h.hed_id')
                ->where('s.sha_cf', '>', 0)
                ->select(
                    'h.hed_id',
                    'h.hed_prj_id',
                    'h.hed_code',
                    'p.prj_title',
                    'p.prj_status',
                    'p.prj_aprvdt',
                    's.sha_cf'
                )
                ->orderBy('p.prj_aprvdt', 'desc')
                ->get();

            $prjIds = $heads->pluck('hed_prj_id')->unique()->filter()->values()->all();
            $projects = Project::whereIn('prj_id', $prjIds)->get();
        } else {
            $projects = Project::where('prj_unt_id', $unitId)->get();
            $heads = DB::table('cen.heads as h')
                ->join('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
                ->where('h.hed_unt_id', $unitId)
                ->select('h.hed_id', 'h.hed_prj_id', 'h.hed_code', 'p.prj_title', 'p.prj_status', 'p.prj_aprvdt')
                ->get();
        }

        $totalProjects = $projects->count();
        $approvedProjects = $projects->where('prj_status', 'Approved')->count();
        $completedProjects = $projects->where('prj_status', 'Completed')->count();
        $inProcessProjects = $totalProjects - $approvedProjects - $completedProjects;
        if($inProcessProjects < 0) $inProcessProjects = 0;
        
        $projectsByStage = [
            'open' => $approvedProjects,
            'in_process' => $inProcessProjects,
            'completed' => $completedProjects
        ];

        $unit = DB::table('cen.units')->where('unt_id', $unitId)->select('unt_id', 'unt_name', 'unt_namesh')->first();

        $financials = [];
        $projectsList = [];
        $totalAmount = 0;
        $totalSpent = 0;
        $totalCommitments = 0;
        $totalInProcess = 0;

        foreach ($heads as $h) {
            $fin = $finService->getHeadStatus($h->hed_id);
            
            if ($isCentralMyDept) {
                $appr = (float)($fin->csrf_share ?? $fin->cf_share ?? $h->sha_cf ?? 0);
                $spent = (float)($fin->cf_expenditure ?? 0);
                $rem = (float)($fin->cf_remaining ?? max(0, $appr - abs($spent)));
                $comm = (float)($fin->cf_commitments ?? 0);
                $inp = (float)($fin->cf_in_process ?? 0);
            } else {
                $appr = (float)($fin->prj_share ?? 0);
                $spent = (float)($fin->prj_expenditure ?? 0);
                $rem = (float)($fin->prj_remaining ?? 0);
                $comm = (float)($fin->prj_commitments ?? 0);
                $inp = (float)($fin->prj_in_process ?? 0);
            }

            $totalAmount += $appr;
            $totalSpent += $spent;
            $totalCommitments += $comm;
            $totalInProcess += $inp;

            // Collect detailed financials for interactive panel
            $financials[] = [
                'head_id' => $h->hed_id,
                'head_code' => $h->hed_code,
                'title' => $h->prj_title,
                
                // Top Level
                'approved' => $appr,
                'spent' => $spent,
                'remaining' => $rem,
                'commitments' => $comm,
                
                // Details (Project Scope)
                'prj_share' => $fin->prj_share ?? 0,
                'prj_expenditure' => $fin->prj_expenditure ?? 0,
                'prj_commitments' => $fin->prj_commitments ?? 0,
                'prj_in_process' => $fin->prj_in_process ?? 0,
                
                // Details (CSRF Scope)
                'csrf_share' => $fin->csrf_share ?? $fin->cf_share ?? 0,
                'cf_expenditure' => $fin->cf_expenditure ?? 0,
                'cf_commitments' => $fin->cf_commitments ?? 0,
                'cf_in_process' => $fin->cf_in_process ?? 0,
                'cf_can_be_spent' => $fin->cf_can_be_spent ?? 0,
            ];

            // For the table (Count only active/current employees)
            $teamCount = DB::table('hr.emps')
                ->where('emp_hed_id', $h->hed_id)
                ->whereRaw('LOWER(emp_status) IN (?, ?)', ['active', 'current'])
                ->count();
            
            // Milestones logic
            $totalMilestones = DB::table('prj.milestones')->where('msn_xprj_id', $h->hed_prj_id)->count();
            $completedMilestones = DB::table('prj.milestones')->where('msn_xprj_id', $h->hed_prj_id)->where('msn_status', 'Completed')->count();
            $milestonesPct = $totalMilestones > 0 ? round(($completedMilestones / $totalMilestones) * 100) : 0;
            $burnRate = $appr > 0 ? round((abs($spent) / $appr) * 100, 1) : 0;
            
            $projectsList[] = [
                'prj_id' => $h->hed_prj_id,
                'title' => $h->prj_title,
                'team_count' => $teamCount,
                'milestones' => "$completedMilestones / $totalMilestones",
                'milestones_total' => $totalMilestones,
                'milestones_completed' => $completedMilestones,
                'milestones_pct' => $milestonesPct,
                'created_on' => $h->prj_aprvdt ? \Carbon\Carbon::parse($h->prj_aprvdt)->format('M d, Y') : 'N/A',
                'status' => $h->prj_status,
                'head_code' => $h->hed_code,
                'approved' => $appr,
                'spent' => abs($spent),
                'commitments' => $comm,
                'remaining' => $rem,
                'burn_rate' => $burnRate
            ];
        }

        // For central department, include active departmental employees in team count
        if ($isCentralMyDept) {
            $deptStaffCount = DB::table('hr.emps')
                ->where('emp_unt_id', $unitId)
                ->whereRaw('LOWER(emp_status) IN (?, ?)', ['active', 'current'])
                ->count();
            if ($deptStaffCount > 0 && !empty($projectsList)) {
                $projectsList[0]['team_count'] += $deptStaffCount;
            }
        }

        $totalRemaining = $totalAmount - abs($totalSpent);
        $utilizationRate = $totalAmount > 0 ? round((abs($totalSpent) / $totalAmount) * 100, 1) : 0;

        // 3. Purchase Cases Breakdown
        $casesBreakdown = Purchase::where(function($q) use ($unitId) {
                $q->where('pcs_unt_id', $unitId)
                  ->orWhere('pcs_intunt_id', $unitId);
            })
            ->select('pcs_status', DB::raw('count(*) as total'))
            ->groupBy('pcs_status')
            ->pluck('total', 'pcs_status')
            ->toArray();
        
        $totalCases = array_sum($casesBreakdown);

        // 4. Recent Purchase Cases
        $recentPurchases = DB::table('pur.purcases as p')
            ->leftJoin('cen.heads as h', function($join) {
                $join->on('p.pcs_effhed_id', '=', 'h.hed_id')
                    ->orOn('p.pcs_hed_id', '=', 'h.hed_id');
            })
            ->where(function($q) use ($unitId) {
                $q->where('p.pcs_unt_id', $unitId)
                  ->orWhere('p.pcs_intunt_id', $unitId);
            })
            ->orderBy('p.pcs_id', 'desc')
            ->limit(5)
            ->select('p.pcs_id', 'p.pcs_title', 'p.pcs_status', 'p.pcs_price', 'p.pcs_date', 'h.hed_code')
            ->get();

        // 5. Assets and Inventory Stats
        $assetStats = DB::table('ina.invatcomps as c')
            ->join('ina.invats as a', 'c.iac_ias_id', '=', 'a.ias_id')
            ->where('a.ias_unt_id', $unitId)
            ->selectRaw("
                COUNT(c.iac_id) as total_items,
                COUNT(CASE WHEN c.iac_status IN ('Untagged', 'Tagged', 'Held') THEN 1 END) as on_charge_count,
                COUNT(CASE WHEN c.iac_status NOT IN ('Untagged', 'Tagged', 'Held') THEN 1 END) as off_charge_count,
                COALESCE(SUM(CASE WHEN c.iac_status IN ('Untagged', 'Tagged', 'Held') THEN c.iac_qty * a.ias_price ELSE 0 END), 0) as on_charge_val,
                COALESCE(SUM(CASE WHEN c.iac_status NOT IN ('Untagged', 'Tagged', 'Held') THEN c.iac_qty * a.ias_price ELSE 0 END), 0) as off_charge_val
            ")->first();

        // 6. Hiring Cases 
        $hiringCases = DB::table('hr.ctrcases')
            ->where(function($q) use ($unitId) {
                $q->where('ctc_divisionid', $unitId)
                  ->orWhere('ctc_unt_id', $unitId)
                  ->orWhere('ctc_approvedunt_id', $unitId);
            })
            ->whereNotIn(DB::raw('LOWER(ctc_status)'), ['closed', 'completed', 'cancelled', 'rejected'])
            ->count();

        return response()->json([
            'unit' => $unit,
            'totalProjects' => $totalProjects,
            'projectsByStage' => $projectsByStage,
            'financials' => $financials,
            'projectsList' => $projectsList,
            'finSummary' => [
                'total' => $totalAmount,
                'spent' => abs($totalSpent),
                'commitments' => $totalCommitments,
                'in_process' => $totalInProcess,
                'remaining' => $totalRemaining,
                'utilization_rate' => $utilizationRate
            ],
            'purchaseStats' => [
                'breakdown' => $casesBreakdown,
                'total' => $totalCases,
                'recent' => $recentPurchases
            ],
            'assetStats' => [
                'total_items' => (int)($assetStats->total_items ?? 0),
                'on_charge_count' => (int)($assetStats->on_charge_count ?? 0),
                'off_charge_count' => (int)($assetStats->off_charge_count ?? 0),
                'on_charge_val' => (float)($assetStats->on_charge_val ?? 0),
                'off_charge_val' => (float)($assetStats->off_charge_val ?? 0),
            ],
            'hiringStats' => [
                'active' => $hiringCases
            ]
        ]);
    }

    public function nrdiDashboard()
    {
        return view('nrdi.dashboard');
    }

    public function nrdiDashboardData(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isDlog = (method_exists($user, 'isDlog') && $user->isDlog()) || in_array($userArea, ['log', 'dlog', 'logistics'], true);
        $isHqUser = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw'], true) || $isDlog;

        if ($isHqUser) {
            $lower = 0;
            $upper = 99999999;
        } else {
            $mode = $request->query('mode');
            if ($mode === 's') {
                [$lower, $upper] = $user->acc_lowers == 0
                    ? [$user->acc_lowerm, $user->acc_upperm]
                    : [$user->acc_lowers, $user->acc_uppers];
            } elseif ($mode === 'm') {
                $lower = $user->acc_lowerm;
                $upper = $user->acc_upperm;
            } else {
                [$lower, $upper] = $user->acc_lowers == 0
                    ? [$user->acc_lowerm, $user->acc_upperm]
                    : [$user->acc_lowers, $user->acc_uppers];
            }
        }

        $divisionParam = $request->query('division', 'all');
        $projectParam = $request->query('project_id');
        $statusScope = strtolower(trim((string) $request->query('status_scope', 'all'))); // 'all' | 'active' | 'completed'

        $allDivisions = DB::table('cen.units')
            ->where('unt_type', 'Division')
            ->whereBetween('unt_id', [$lower, $upper])
            ->orderBy('unt_name')
            ->get(['unt_id', 'unt_name', 'unt_namesh']);

        $divisions = $allDivisions->map(function ($d) {
            $label = (string) $d->unt_name;
            $label = preg_replace('/\s+Division$/i', '', $label) ?: (string) $d->unt_name;
            return [
                'id' => (int) $d->unt_id,
                'key' => (string) ($d->unt_namesh ?? ''),
                'label' => $label,
                'name' => (string) $d->unt_name,
            ];
        })->values();

        $selectedUnitIds = [];
        $selectedProject = null;

        if (is_numeric($projectParam)) {
            $projectId = (int) $projectParam;
            $proj = DB::table('prj.projects')
                ->where('prj_id', $projectId)
                ->whereBetween('prj_unt_id', [$lower, $upper])
                ->first(['prj_id', 'prj_unt_id', 'prj_code', 'prj_title', 'prj_aprvcost', 'prj_aprvdt', 'prj_status', 'prj_startdt', 'prj_estenddt']);

            if ($proj) {
                $selectedProject = [
                    'id' => (int) $proj->prj_id,
                    'unit_id' => (int) $proj->prj_unt_id,
                    'code' => (string) $proj->prj_code,
                    'title' => (string) $proj->prj_title,
                ];
                $selectedUnitIds = [(int) $proj->prj_unt_id];
            }
        }

        if (! $selectedProject) {
            if (is_numeric($divisionParam)) {
                $selectedUnitIds = [(int) $divisionParam];
            } elseif ($divisionParam !== 'all' && !empty($divisionParam)) {
                $matchedDiv = $allDivisions->first(function($d) use ($divisionParam) {
                    return strcasecmp((string)$d->unt_namesh, (string)$divisionParam) === 0 || strcasecmp((string)$d->unt_name, (string)$divisionParam) === 0;
                });
                if ($matchedDiv) {
                    $selectedUnitIds = [(int) $matchedDiv->unt_id];
                } else {
                    $selectedUnitIds = $allDivisions->pluck('unt_id')->map(fn ($v) => (int) $v)->values()->all();
                }
            } else {
                $divisionParam = 'all';
                $selectedUnitIds = $allDivisions->pluck('unt_id')->map(fn ($v) => (int) $v)->values()->all();
            }
        }

        // Fetch Heads and Projects in the current scope
        $headsQuery = DB::table('cen.heads as h')
            ->join('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'h.hed_unt_id')
            ->whereBetween('h.hed_unt_id', [$lower, $upper]);

        if ($selectedProject) {
            $headsQuery->where('p.prj_id', $selectedProject['id']);
        } elseif (!empty($selectedUnitIds)) {
            $headsQuery->whereIn('h.hed_unt_id', $selectedUnitIds);
        }

        if ($statusScope === 'active') {
            $headsQuery->whereNotIn(DB::raw('LOWER(TRIM(COALESCE(p.prj_status, \'\')))'), ['closed', 'completed']);
        } elseif ($statusScope === 'completed') {
            $headsQuery->whereIn(DB::raw('LOWER(TRIM(COALESCE(p.prj_status, \'\')))'), ['closed', 'completed']);
        }

        $heads = $headsQuery->select(
            'h.hed_id', 'h.hed_prj_id', 'h.hed_code', 'h.hed_unt_id',
            'p.prj_title', 'p.prj_code', 'p.prj_status', 'p.prj_startdt', 'p.prj_estenddt', 'p.prj_aprvcost',
            'u.unt_namesh', 'u.unt_name'
        )->get();

        $headIds = $heads->pluck('hed_id')->toArray();
        $prjIds = $heads->pluck('hed_prj_id')->unique()->toArray();

        // 1. Allocations & MTSS
        $allocations = (float) DB::table('fin.transfers')
            ->where('trf_type', 'FI')
            ->where('trf_title', 'Project Funding')
            ->whereIn('trf_tohed', $headIds)
            ->sum('trf_amount');

        $mtss = (float) DB::table('fin.transfers')
            ->where('trf_type', 'FO')
            ->where('trf_title', 'MTSS Share')
            ->whereIn('trf_fromhed', $headIds)
            ->sum('trf_amount');

        $mtssReceived = (float) DB::table('fin.sharesinstall as si')
            ->join('fin.transactions as t', 't.trn_id', '=', 'si.shi_fotrn_id')
            ->whereIn('si.shi_hed_id', $headIds)
            ->sum(DB::raw('COALESCE(t.trn_amount2, 0) * -1'));

        $mtssPending = max(0, $mtss - $mtssReceived);

        $shares = DB::table('fin.sharesalloc')->whereIn('sha_hed_id', $headIds)->select(
            DB::raw('COALESCE(SUM(sha_cf), 0) as total_cf'),
            DB::raw('COALESCE(SUM(sha_pcc), 0) as total_pcc'),
            DB::raw('COALESCE(SUM(sha_prj), 0) as total_prj')
        )->first();

        $totalAllocation = $allocations > 0 ? $allocations : (float) $heads->sum('prj_aprvcost');
        $totalMtss = $mtss;
        $totalRdw = max(0, $totalAllocation - $totalMtss);
        $csrfShare = (float) ($shares->total_cf ?? 0);
        $pccShare = (float) ($shares->total_pcc ?? 0);
        if ($pccShare <= 0) $pccShare = $totalRdw;

        // 2. Expenditures
        $expenditures = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->whereIn('c.cmt_effhed_id', $headIds)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO'])
            ->select(
                DB::raw("COALESCE(SUM(CASE WHEN (c.cmt_sudohed IS NULL OR c.cmt_sudohed = '') THEN (CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) ELSE 0 END) * -1, 0) as pcc_exp"),
                DB::raw("COALESCE(SUM(CASE WHEN (c.cmt_sudohed IS NOT NULL AND c.cmt_sudohed <> '' AND c.cmt_type <> 'TO') THEN (CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) ELSE 0 END) * -1, 0) as cf_exp"),
                DB::raw("COALESCE(SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) * -1, 0) as total_exp")
            )->first();

        // 3. Outstanding Commitments
        $paidSub = DB::table('fin.transactions as t')
            ->select('t.trn_cmt_id', DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN COALESCE(t.trn_amount1, 0) ELSE COALESCE(t.trn_amount2, 0) END) as paid'))
            ->groupBy('t.trn_cmt_id');

        $commitments = DB::table('fin.commitments as c')
            ->leftJoinSub($paidSub, 'p', 'c.cmt_id', '=', 'p.trn_cmt_id')
            ->whereIn('c.cmt_effhed_id', $headIds)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa'])
            ->where('c.cmt_status', 'Awaited')
            ->select(
                DB::raw("COALESCE(SUM(CASE WHEN (c.cmt_sudohed IS NULL OR c.cmt_sudohed = '') THEN (c.cmt_amount - COALESCE(p.paid, 0)) ELSE 0 END) * -1, 0) as pcc_cmt"),
                DB::raw("COALESCE(SUM(CASE WHEN (c.cmt_sudohed IS NOT NULL AND c.cmt_sudohed <> '') THEN (c.cmt_amount - COALESCE(p.paid, 0)) ELSE 0 END) * -1, 0) as cf_cmt"),
                DB::raw("COALESCE(SUM(c.cmt_amount - COALESCE(p.paid, 0)) * -1, 0) as total_cmt")
            )->first();

        // 4. In Process (from fin.docs_ipc)
        $inProc = DB::table('fin.docs_ipc')
            ->whereIn('effhed_id', $headIds)
            ->select(
                DB::raw("COALESCE(SUM(CASE WHEN (sudohed IS NULL OR sudohed = '') THEN (CASE WHEN transtype = 1 THEN amount1 ELSE amount2 END) ELSE 0 END), 0) as pcc_in_process"),
                DB::raw("COALESCE(SUM(CASE WHEN (sudohed IS NOT NULL AND sudohed <> '') THEN (CASE WHEN transtype = 1 THEN amount1 ELSE amount2 END) ELSE 0 END), 0) as cf_in_process"),
                DB::raw("COALESCE(SUM(CASE WHEN transtype = 1 THEN amount1 ELSE amount2 END), 0) as total_in_process")
            )->first();

        // 5. Staff Statistics
        if ($statusScope === 'completed') {
            $divStaff = (int) DB::table('hr.emps')
                ->whereIn('emp_hed_id', $headIds)
                ->whereRaw("LOWER(emp_status) IN ('active','current')")
                ->count();
            $csrfStaff = 0;
            $totalStaff = $divStaff;
        } elseif ($statusScope === 'active') {
            $completedHeadIds = DB::table('cen.heads as h')
                ->join('prj.projects as p', function ($j) {
                    $j->on('h.hed_prj_id', '=', 'p.prj_id')->orOn('h.hed_id', '=', 'p.prj_id');
                })
                ->whereIn(DB::raw('LOWER(TRIM(COALESCE(p.prj_status, \'\')))'), ['closed', 'completed'])
                ->pluck('h.hed_id')->toArray();

            $divStaff = (int) DB::table('hr.emps')
                ->whereIn('emp_unt_id', $selectedUnitIds)
                ->whereNotIn('emp_hed_id', $completedHeadIds)
                ->whereRaw("LOWER(emp_status) IN ('active','current')")
                ->count();

            if ($divisionParam === 'all' && empty($selectedProject)) {
                $csrfStaff = (int) DB::table('hr.emps as e')
                    ->join('cen.units as u', 'u.unt_id', '=', 'e.emp_unt_id')
                    ->whereRaw("LOWER(e.emp_status) IN ('active','current')")
                    ->where(function ($q) {
                        $q->where('e.emp_unt_id', '>=', 800000)
                          ->orWhere('u.unt_type', 'Department')
                          ->orWhereIn(DB::raw('LOWER(u.unt_area)'), ['hr', 'fin', 'adm', 'admin', 'is', 'it', 'hqs', 'rdw', 'nrdi', 'csrf']);
                    })
                    ->count();
                $totalStaff = $divStaff + $csrfStaff;
            } else {
                $csrfStaff = 0;
                $totalStaff = $divStaff;
            }
        } else {
            // 'all'
            $divStaff = (int) DB::table('hr.emps')
                ->whereIn('emp_unt_id', $selectedUnitIds)
                ->whereRaw("LOWER(emp_status) IN ('active','current')")
                ->count();

            if ($divisionParam === 'all' && empty($selectedProject)) {
                $csrfStaff = (int) DB::table('hr.emps as e')
                    ->join('cen.units as u', 'u.unt_id', '=', 'e.emp_unt_id')
                    ->whereRaw("LOWER(e.emp_status) IN ('active','current')")
                    ->where(function ($q) {
                        $q->where('e.emp_unt_id', '>=', 800000)
                          ->orWhere('u.unt_type', 'Department')
                          ->orWhereIn(DB::raw('LOWER(u.unt_area)'), ['hr', 'fin', 'adm', 'admin', 'is', 'it', 'hqs', 'rdw', 'nrdi', 'csrf']);
                    })
                    ->count();
                $totalStaff = $divStaff + $csrfStaff;
            } else {
                $csrfStaff = 0;
                $totalStaff = $divStaff;
            }
        }

        // 6. Trend Timeline (Monthly & Quarterly Inflow vs Expenditure)
        $monthlyInward = DB::table('fin.sharesinstall as si')
            ->join('fin.transactions as t', 't.trn_id', '=', 'si.shi_fitrn_id')
            ->whereIn('si.shi_hed_id', $headIds)
            ->whereNotNull('t.trn_date')
            ->select(
                DB::raw("to_char(t.trn_date, 'YYYY-MM') as ym"),
                DB::raw("COALESCE(SUM(si.shi_pcc), 0) as rdw_rcv"),
                DB::raw("COALESCE(SUM(si.shi_cf), 0) as csrf_rcv"),
                DB::raw("COALESCE(SUM(si.shi_pcc + si.shi_cf), 0) as total_rcv")
            )
            ->groupBy(DB::raw("to_char(t.trn_date, 'YYYY-MM')"))
            ->get()
            ->keyBy('ym');

        $monthlyExp = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->whereIn('c.cmt_effhed_id', $headIds)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO'])
            ->whereNotNull('t.trn_date')
            ->select(
                DB::raw("to_char(t.trn_date, 'YYYY-MM') as ym"),
                DB::raw("COALESCE(SUM(CASE WHEN (c.cmt_sudohed IS NULL OR c.cmt_sudohed = '') THEN (CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) ELSE 0 END) * -1, 0) as rdw_exp"),
                DB::raw("COALESCE(SUM(CASE WHEN (c.cmt_sudohed IS NOT NULL AND c.cmt_sudohed <> '' AND c.cmt_type <> 'TO') THEN (CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) ELSE 0 END) * -1, 0) as csrf_exp"),
                DB::raw("COALESCE(SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) * -1, 0) as total_exp")
            )
            ->groupBy(DB::raw("to_char(t.trn_date, 'YYYY-MM')"))
            ->get()
            ->keyBy('ym');

        $monthlyPeriods = collect(range(0, 11))->reverse()->map(function ($i) {
            return now()->subMonths($i)->format('Y-m');
        })->values();

        $monthlySeries = $monthlyPeriods->map(function ($ym) use ($monthlyInward, $monthlyExp, $totalRdw, $totalAllocation, $csrfShare) {
            return [
                'period' => $ym,
                'rdw_rcv' => (float)($monthlyInward[$ym]->rdw_rcv ?? 0),
                'rdw_exp' => (float)($monthlyExp[$ym]->rdw_exp ?? 0),
                'csrf_rcv' => (float)($monthlyInward[$ym]->csrf_rcv ?? 0),
                'csrf_exp' => (float)($monthlyExp[$ym]->csrf_exp ?? 0),
                'total_rcv' => (float)($monthlyInward[$ym]->total_rcv ?? 0),
                'total_exp' => (float)($monthlyExp[$ym]->total_exp ?? 0),
                'rdw_budget' => $totalRdw,
                'total_budget' => $totalAllocation,
                'csrf_budget' => $csrfShare,
            ];
        })->values();

        $quarterlyInward = DB::table('fin.sharesinstall as si')
            ->join('fin.transactions as t', 't.trn_id', '=', 'si.shi_fitrn_id')
            ->whereIn('si.shi_hed_id', $headIds)
            ->whereNotNull('t.trn_date')
            ->select(
                DB::raw("concat(extract(year from t.trn_date)::int, '-Q', extract(quarter from t.trn_date)::int) as yq"),
                DB::raw("COALESCE(SUM(si.shi_pcc), 0) as rdw_rcv"),
                DB::raw("COALESCE(SUM(si.shi_cf), 0) as csrf_rcv"),
                DB::raw("COALESCE(SUM(si.shi_pcc + si.shi_cf), 0) as total_rcv")
            )
            ->groupBy(DB::raw("concat(extract(year from t.trn_date)::int, '-Q', extract(quarter from t.trn_date)::int)"))
            ->get()
            ->keyBy('yq');

        $quarterlyExp = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->whereIn('c.cmt_effhed_id', $headIds)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO'])
            ->whereNotNull('t.trn_date')
            ->select(
                DB::raw("concat(extract(year from t.trn_date)::int, '-Q', extract(quarter from t.trn_date)::int) as yq"),
                DB::raw("COALESCE(SUM(CASE WHEN (c.cmt_sudohed IS NULL OR c.cmt_sudohed = '') THEN (CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) ELSE 0 END) * -1, 0) as rdw_exp"),
                DB::raw("COALESCE(SUM(CASE WHEN (c.cmt_sudohed IS NOT NULL AND c.cmt_sudohed <> '' AND c.cmt_type <> 'TO') THEN (CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) ELSE 0 END) * -1, 0) as csrf_exp"),
                DB::raw("COALESCE(SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) * -1, 0) as total_exp")
            )
            ->groupBy(DB::raw("concat(extract(year from t.trn_date)::int, '-Q', extract(quarter from t.trn_date)::int)"))
            ->get()
            ->keyBy('yq');

        $quarterPeriods = collect(range(0, 7))->reverse()->map(function ($i) {
            $d = now()->subQuarters($i);
            $q = (int) ceil(((int) $d->format('n')) / 3);
            return $d->format('Y') . '-Q' . $q;
        })->values();

        $quarterlySeries = $quarterPeriods->map(function ($yq) use ($quarterlyInward, $quarterlyExp, $totalRdw, $totalAllocation, $csrfShare) {
            return [
                'period' => $yq,
                'rdw_rcv' => (float)($quarterlyInward[$yq]->rdw_rcv ?? 0),
                'rdw_exp' => (float)($quarterlyExp[$yq]->rdw_exp ?? 0),
                'csrf_rcv' => (float)($quarterlyInward[$yq]->csrf_rcv ?? 0),
                'csrf_exp' => (float)($quarterlyExp[$yq]->csrf_exp ?? 0),
                'total_rcv' => (float)($quarterlyInward[$yq]->total_rcv ?? 0),
                'total_exp' => (float)($quarterlyExp[$yq]->total_exp ?? 0),
                'rdw_budget' => $totalRdw,
                'total_budget' => $totalAllocation,
                'csrf_budget' => $csrfShare,
            ];
        })->values();

        // 7. Projects Table & Projects Categorized (Delayed, Ongoing, Completed)
        $closedProjectStatuses = ['Closed', 'Completed'];
        $today = now()->toDateString();

        $projRowsQuery = DB::table('prj.projects as p')
            ->join('cen.units as u', 'u.unt_id', '=', 'p.prj_unt_id')
            ->leftJoin('cen.heads as h', function($j) {
                $j->on('h.hed_prj_id', '=', 'p.prj_id')->orOn('h.hed_id', '=', 'p.prj_id');
            })
            ->leftJoin('fin.sharesalloc as sa', 'sa.sha_hed_id', '=', 'h.hed_id')
            ->leftJoin('pur.purcases as c', 'c.pcs_hed_id', '=', 'p.prj_id')
            ->select(
                'p.prj_id',
                'p.prj_code',
                'p.prj_title',
                'p.prj_status',
                'p.prj_startdt',
                'p.prj_estenddt',
                'u.unt_namesh as division',
                DB::raw('COALESCE(NULLIF(p.prj_aprvcost, 0), sa.sha_prj, sa.sha_pcc, 0) as budget'),
                DB::raw('COALESCE(SUM(c.pcs_price), 0) as utilized'),
                DB::raw("COALESCE(SUM(CASE WHEN c.pcs_noloan = false THEN c.pcs_intprice ELSE 0 END), 0) as credit")
            )
            ->groupBy(
                'p.prj_id', 'p.prj_code', 'p.prj_title', 'p.prj_status',
                'p.prj_startdt', 'p.prj_estenddt', 'u.unt_namesh', 'p.prj_aprvcost', 'sa.sha_prj', 'sa.sha_pcc'
            )
            ->orderByDesc('p.prj_id')
            ->limit(500);

        if ($selectedProject) {
            $projRowsQuery->where('p.prj_id', $selectedProject['id']);
        } else {
            $projRowsQuery->whereIn('p.prj_unt_id', $selectedUnitIds);
        }

        if ($statusScope === 'active') {
            $projRowsQuery->whereNotIn(DB::raw('LOWER(TRIM(COALESCE(p.prj_status, \'\')))'), ['closed', 'completed']);
        } elseif ($statusScope === 'completed') {
            $projRowsQuery->whereIn(DB::raw('LOWER(TRIM(COALESCE(p.prj_status, \'\')))'), ['closed', 'completed']);
        }

        $projRows = $projRowsQuery->get();

        // Staff count per project
        $activeEmployees = DB::table('hr.emps')
            ->whereRaw("LOWER(emp_status) IN ('active','current')")
            ->get(['emp_id', 'emp_hed_id']);

        $activePlans = DB::table('hr.contractplans as cp')
            ->join('hr.contracts as c', 'c.ctr_id', '=', 'cp.cpn_ctr_id')
            ->whereRaw('? between cp.cpn_startdt and cp.cpn_enddt', [$today])
            ->whereNotNull('cp.cpn_hed_id')
            ->select('c.ctr_num as emp_id', 'cp.cpn_hed_id')
            ->get()
            ->keyBy('emp_id');

        $headsMap = DB::table('cen.heads')->get()->keyBy('hed_id');
        $projectStaffCounts = [];
        foreach ($activeEmployees as $emp) {
            $currentHeadId = $activePlans->has($emp->emp_id)
                ? $activePlans[$emp->emp_id]->cpn_hed_id
                : $emp->emp_hed_id;

            $h = $headsMap->get($currentHeadId);
            $pId = $h ? ($h->hed_prj_id ?: $h->hed_id) : null;
            if ($pId) {
                $projectStaffCounts[$pId] = ($projectStaffCounts[$pId] ?? 0) + 1;
            }
        }

        $tableProjects = $projRows->map(function ($p) use ($closedProjectStatuses, $projectStaffCounts, $today) {
            $budget = (float) $p->budget;
            $used = (float) $p->utilized;
            $remaining = max(0, $budget - $used);

            $derivedStatus = 'Ongoing';
            $isClosed = in_array((string) $p->prj_status, $closedProjectStatuses, true);
            if ($isClosed) {
                $derivedStatus = 'Completed';
            } elseif ($p->prj_estenddt && (string) $p->prj_estenddt < $today) {
                $derivedStatus = 'Delayed';
            }

            $progress = 0;
            if ($isClosed) {
                $progress = 100;
            } elseif ($p->prj_startdt && $p->prj_estenddt) {
                $start = \Carbon\Carbon::parse($p->prj_startdt);
                $end = \Carbon\Carbon::parse($p->prj_estenddt);
                if ($end->greaterThan($start)) {
                    $progress = (int) round(min(100, max(0, $start->floatDiffInDays(now()) / max(1, $start->floatDiffInDays($end)) * 100)));
                }
            }

            return [
                'id' => (int) $p->prj_id,
                'code' => (string) $p->prj_code,
                'name' => (string) $p->prj_title,
                'division' => (string) $p->division,
                'budget' => $budget,
                'utilized' => $used,
                'remaining' => $remaining,
                'credit' => (float) $p->credit,
                'progress' => $progress,
                'status' => $derivedStatus,
                'statusRaw' => (string) $p->prj_status,
                'est_end' => $p->prj_estenddt ? \Carbon\Carbon::parse($p->prj_estenddt)->format('d-M-Y') : null,
                'employees' => (int) ($projectStaffCounts[$p->prj_id] ?? 0),
            ];
        })->values();

        $delayedProjects = $tableProjects->where('status', 'Delayed')->values();
        $ongoingProjects = $tableProjects->where('status', 'Ongoing')->values();
        $completedProjects = $tableProjects->where('status', 'Completed')->values();

        $codesByDivision = [];
        $allProjectsList = DB::table('prj.projects as p')
            ->join('cen.units as u', 'u.unt_id', '=', 'p.prj_unt_id')
            ->whereBetween('p.prj_unt_id', [$lower, $upper])
            ->select('p.prj_id', 'p.prj_code', 'p.prj_title', 'p.prj_status', 'p.prj_unt_id', 'u.unt_namesh')
            ->orderBy('p.prj_code')
            ->get();

        foreach ($allDivisions as $d) {
            $matching = $allProjectsList->where('prj_unt_id', $d->unt_id)->map(function($p) {
                return [
                    'id' => (int) $p->prj_id,
                    'code' => (string) ($p->prj_code ?: $p->prj_title),
                    'title' => (string) $p->prj_title,
                    'status' => (string) ($p->prj_status ?: 'Ongoing')
                ];
            })->values()->all();

            $codesByDivision[$d->unt_id] = $matching;
            if (!empty($d->unt_namesh)) {
                $codesByDivision[$d->unt_namesh] = $matching;
            }
        }

        // Total Counts for Status Filter Badges (unfiltered by statusScope)
        $badgeProjects = $allProjectsList;
        if ($selectedProject) {
            $badgeProjects = $badgeProjects->where('prj_id', $selectedProject['id']);
        } elseif (!empty($selectedUnitIds)) {
            $badgeProjects = $badgeProjects->whereIn('prj_unt_id', $selectedUnitIds);
        }
        $badgeAll = $badgeProjects->count();
        $badgeCompleted = $badgeProjects->filter(function($p) use ($closedProjectStatuses) {
            return in_array((string)$p->prj_status, $closedProjectStatuses, true);
        })->count();
        $badgeActive = max(0, $badgeAll - $badgeCompleted);

        // 8. 5-Year Staff Hiring & Turnover History (Last 5 Years)
        $historyYears = collect(range(0, 4))->reverse()->map(fn($i) => (int) now()->subYears($i)->format('Y'))->values();
        $staffHistory = $historyYears->map(function($yr) use ($selectedUnitIds, $divisionParam) {
            $joinedQ = DB::table('hr.emps')->whereYear('emp_joindt', $yr);
            $releasedQ = DB::table('hr.emps')->whereYear('emp_lastdt', $yr);

            if ($divisionParam !== 'all') {
                $joinedQ->whereIn('emp_unt_id', $selectedUnitIds);
                $releasedQ->whereIn('emp_unt_id', $selectedUnitIds);
            }
            $joined = (int) $joinedQ->count();
            $released = (int) $releasedQ->count();

            $endOfYear = "{$yr}-12-31";
            if ($yr === (int) now()->format('Y')) {
                $activeQ = DB::table('hr.emps')->whereRaw("LOWER(emp_status) IN ('active','current')");
                if ($divisionParam !== 'all') {
                    $activeQ->whereIn('emp_unt_id', $selectedUnitIds);
                }
                $active = (int) $activeQ->count();
            } else {
                $activeQ = DB::table('hr.emps')
                    ->where('emp_joindt', '<=', $endOfYear)
                    ->where(function($q) use ($endOfYear) {
                        $q->whereNull('emp_lastdt')
                          ->orWhere('emp_lastdt', '>', $endOfYear);
                    });
                if ($divisionParam !== 'all') {
                    $activeQ->whereIn('emp_unt_id', $selectedUnitIds);
                }
                $active = (int) $activeQ->count();
            }

            return [
                'year' => (string) $yr,
                'joined' => $joined,
                'released' => $released,
                'active' => $active
            ];
        })->values();

        // 9. Staff Distribution by Division (Active personnel)
        $staffPerDivRows = DB::table('cen.units as u')
            ->leftJoin('hr.emps as e', function($j) {
                $j->on('e.emp_unt_id', '=', 'u.unt_id')
                  ->whereRaw("LOWER(e.emp_status) IN ('active','current')");
            })
            ->where('u.unt_type', 'Division')
            ->groupBy('u.unt_id', 'u.unt_name', 'u.unt_namesh')
            ->select(
                'u.unt_id',
                'u.unt_namesh as division',
                'u.unt_name as full_name',
                DB::raw('COUNT(e.emp_id) as count')
            )
            ->orderByDesc('count')
            ->get();

        $staffDistribution = $staffPerDivRows->map(function($d) {
            return [
                'label' => (string) ($d->division ?: $d->full_name),
                'count' => (int) $d->count,
            ];
        })->values();

        if ($csrfStaff > 0) {
            $staffDistribution->push([
                'label' => 'CSRF Central',
                'count' => (int) $csrfStaff,
            ]);
        }

        return response()->json([
            'divisions' => $divisions,
            'selectedProject' => $selectedProject,
            'kpis' => [
                'rdw' => [
                    'share' => (float)$totalRdw,
                    'expenditure' => (float)($expenditures->pcc_exp ?? 0),
                    'commitments' => (float)($commitments->pcc_cmt ?? 0),
                    'utilized' => (float)(($expenditures->pcc_exp ?? 0) + ($commitments->pcc_cmt ?? 0)),
                    'in_process' => (float)($inProc->pcc_in_process ?? 0),
                    'remaining' => max(0, (float)$totalRdw - (($expenditures->pcc_exp ?? 0) + ($commitments->pcc_cmt ?? 0) + ($inProc->pcc_in_process ?? 0)))
                ],
                'total' => [
                    'share' => (float)$totalAllocation,
                    'expenditure' => (float)($expenditures->total_exp ?? 0),
                    'commitments' => (float)($commitments->total_cmt ?? 0),
                    'utilized' => (float)(($expenditures->total_exp ?? 0) + ($commitments->total_cmt ?? 0)),
                    'in_process' => (float)($inProc->total_in_process ?? 0),
                    'remaining' => max(0, (float)$totalAllocation - (($expenditures->total_exp ?? 0) + ($commitments->total_cmt ?? 0) + ($inProc->total_in_process ?? 0)))
                ],
                'csrf' => [
                    'share' => (float)$csrfShare,
                    'expenditure' => (float)($expenditures->cf_exp ?? 0),
                    'commitments' => (float)($commitments->cf_cmt ?? 0),
                    'utilized' => (float)(($expenditures->cf_exp ?? 0) + ($commitments->cf_cmt ?? 0)),
                    'in_process' => (float)($inProc->cf_in_process ?? 0),
                    'remaining' => max(0, (float)$csrfShare - (($expenditures->cf_exp ?? 0) + ($commitments->cf_cmt ?? 0) + ($inProc->cf_in_process ?? 0)))
                ],
                'mtss' => [
                    'share' => (float)$totalMtss,
                    'received' => (float)$mtssReceived,
                    'pending' => (float)$mtssPending
                ],
                'staff' => [
                    'total' => $totalStaff,
                    'csrf' => $csrfStaff,
                    'division' => $divStaff
                ]
            ],
            'status_scope' => $statusScope,
            'status_counts' => [
                'all' => (int) $badgeAll,
                'active' => (int) $badgeActive,
                'completed' => (int) $badgeCompleted
            ],
            'status_scope' => $statusScope,
            'status_counts' => [
                'all' => (int) $badgeAll,
                'active' => (int) $badgeActive,
                'completed' => (int) $badgeCompleted
            ],
            'charts' => [
                'finance' => [
                    'monthly' => $monthlySeries,
                    'quarterly' => $quarterlySeries,
                ],
                'staff_history' => $staffHistory,
                'staff_distribution' => $staffDistribution,
                'projects_status' => [
                    'ongoing' => $ongoingProjects->count(),
                    'delayed' => $delayedProjects->count(),
                    'completed' => $completedProjects->count(),
                    'total' => $tableProjects->count(),
                ]
            ],
            'projects_by_status' => [
                'delayed' => $delayedProjects,
                'ongoing' => $ongoingProjects,
                'completed' => $completedProjects,
            ],
            'table' => [
                'projects' => $tableProjects,
            ],
            'lists' => [
                'codesByDivision' => $codesByDivision,
            ],
        ]);
    }

    public function sord()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        [$lower, $upper] = $user->acc_lowers == 0
            ? [$user->acc_lowerm, $user->acc_upperm]
            : [$user->acc_lowers, $user->acc_uppers];

        // 1. Project Stats (range-based)
        $projects = Project::whereBetween('prj_unt_id', [$lower, $upper])->get();
        $totalProjects = $projects->count();
        $hucProjects = $projects->where('prj_status', 'Completed')->count();

        // 2. Resource Stats (Books & Licenses)
        $booksCount = Purchase::whereBetween('pcs_unt_id', [$lower, $upper])
            ->where(function ($q) {
                $q->where('pcs_type', 'book')
                    ->orWhere('pcs_remarks', 'LIKE', '%"resource_type":"book"%');
            })->count();

        $licenseCount = Purchase::whereBetween('pcs_unt_id', [$lower, $upper])
            ->where(function ($q) {
                $q->where('pcs_type', 'license')
                    ->orWhere('pcs_remarks', 'LIKE', '%"resource_type":"license"%');
            })->count();

        // 3. Procurement Cases
        $activeCases = Purchase::whereBetween('pcs_unt_id', [$lower, $upper])
            ->whereIn('pcs_status', ['Draft', 'Under Scrutiny', 'In Progress'])
            ->count();

        $totalCases = Purchase::whereBetween('pcs_unt_id', [$lower, $upper])->count();

        // 4. Financials
        $financials = DB::table('prj.projects as p')
            ->leftJoin('pur.purcases as pc', 'pc.pcs_hed_id', '=', 'p.prj_id')
            ->select(
                'p.prj_title',
                'p.prj_aprvcost as approved',
                DB::raw('COALESCE(SUM(pc.pcs_price), 0) as spent')
            )
            ->whereBetween('p.prj_unt_id', [$lower, $upper])
            ->groupBy('p.prj_id', 'p.prj_title', 'p.prj_aprvcost')
            ->get();

        // 5. Employees per Project (heads within range)
        $employeeStats = DB::table('cen.heads as h')
            ->leftJoin('hr.emps as e', function($join) {
                $join->on('e.emp_hed_id', '=', 'h.hed_id')
                     ->whereRaw('LOWER(e.emp_status) IN (?, ?)', ['active', 'current']);
            })
            ->select('h.hed_code', DB::raw('COUNT(e.emp_id) as emp_count'))
            ->whereBetween('h.hed_unt_id', [$lower, $upper])
            ->groupBy('h.hed_id', 'h.hed_code')
            ->having(DB::raw('COUNT(e.emp_id)'), '>', 0)
            ->get();

        // 6. Overall Amount
        $totalAmount = $projects->sum('prj_aprvcost');
        $totalSpent = $financials->sum('spent');

        return view('index', compact(
            'totalProjects', 'hucProjects', 'booksCount', 'licenseCount',
            'activeCases', 'totalCases', 'financials', 'employeeStats',
            'totalAmount', 'totalSpent'
        ));
    }

    public function contractCases(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        [$lower, $upper] = $user->acc_lowers == 0
            ? [$user->acc_lowerm, $user->acc_upperm]
            : [$user->acc_lowers, $user->acc_uppers];

        $status = $request->query('status', 'open');

        $baseQuery = DB::table('hr.ctrcases as c')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'c.ctc_unt_id')
            ->leftJoin('cen.units as nu', 'nu.unt_id', '=', 'c.ctc_newunt_id')
            ->select([
                'c.ctc_id',
                'c.ctc_type',
                'c.ctc_date',
                'c.ctc_status',
                'c.ctc_emp_id',
                'c.ctc_empnamecomp',
                'c.ctc_unt_id',
                'u.unt_name as unit_name',
                'c.ctc_newunt_id',
                'nu.unt_name as new_unit_name',
                'c.ctc_newjobtitle',
                'c.ctc_newgrade',
                'c.ctc_newsalary',
                'c.ctc_newstartdt',
                'c.ctc_newenddt',
                'c.ctc_releasedtg',
                'c.ctc_closedtg',
            ])
            ->where(function ($q) use ($lower, $upper) {
                $q->whereBetween('c.ctc_unt_id', [$lower, $upper])
                    ->orWhereBetween('c.ctc_newunt_id', [$lower, $upper]);
            });

        if ($status === 'closed') {
            $baseQuery->whereNotNull('c.ctc_closedtg');
        } else {
            $status = 'open';
            $baseQuery->whereNull('c.ctc_closedtg');
        }

        $cases = (clone $baseQuery)
            ->orderByDesc('c.ctc_date')
            ->orderByDesc('c.ctc_id')
            ->paginate(50);

        $cases->appends(['status' => $status]);

        $openCount = DB::table('hr.ctrcases as c')
            ->where(function ($q) use ($lower, $upper) {
                $q->whereBetween('c.ctc_unt_id', [$lower, $upper])
                    ->orWhereBetween('c.ctc_newunt_id', [$lower, $upper]);
            })
            ->whereNull('c.ctc_closedtg')
            ->count();

        $closedCount = DB::table('hr.ctrcases as c')
            ->where(function ($q) use ($lower, $upper) {
                $q->whereBetween('c.ctc_unt_id', [$lower, $upper])
                    ->orWhereBetween('c.ctc_newunt_id', [$lower, $upper]);
            })
            ->whereNotNull('c.ctc_closedtg')
            ->count();

        return view('nrdi.contract_cases.index', compact('cases', 'status', 'openCount', 'closedCount'));
    }

    public function finDashboard(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Determine Mode ('m' = Macro/All, 's' = Section/Department)
        $mode = $request->query('mode', 'm');

        if ($mode === 's') {
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
            $varModeStr = 'approver-s';
        } else {
            $lower = $user->acc_lowerm ?: 0;
            $upper = $user->acc_upperm ?: 99999999;
            $varModeStr = 'approver-m';
        }

        $userAuth = (string) ($user->acc_auth ?? 'viewer');

        // Fetch Divisions list for interactive dropdown
        $divisions = DB::table('cen.units')
            ->where('unt_type', 'Division')
            ->orderBy('unt_name')
            ->select('unt_id', 'unt_name', 'unt_namesh')
            ->get();

            // Cache key version bump to flush old structures
            $cacheKey = "fin_dashboard_data_v3_{$mode}_{$lower}_{$upper}";
            if ($request->has('refresh')) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
            }

            $dashboardData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 90, function () use ($lower, $upper) {
                $finService = app(\App\Services\FinancialIntelligenceService::class);

                // 1. Fetch Head & Project Financial Statuses
                $heads = DB::table('cen.heads as h')
                    ->leftJoin('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                    ->leftJoin('cen.units as u', 'h.hed_unt_id', '=', 'u.unt_id')
                    ->where('h.hed_type', 'Project')
                    ->whereBetween('h.hed_unt_id', [$lower, $upper])
                    ->select(
                        'h.hed_id',
                        'h.hed_code',
                        'h.hed_name',
                        'h.hed_unt_id',
                        'p.prj_id',
                        'p.prj_code',
                        'p.prj_title',
                        'u.unt_id',
                        'u.unt_name',
                        'u.unt_namesh as division'
                    )
                    ->orderBy('h.hed_code')
                    ->get();

                $headStatuses = [];
                $totAlloc = 0; $totRec = 0; $totExp = 0; $totBal = 0;
                $totCmt = 0; $totInp = 0; $totAvail = 0; $totSpent = 0;

                foreach ($heads as $h) {
                    $st = $finService->getHeadStatus($h->hed_id);
                    $alloc = (float) ($st->allocation ?? 0);
                    $rec   = (float) ($st->received ?? 0);
                    $exp   = (float) ($st->expenditure ?? 0);
                    $bal   = (float) ($st->balance ?? 0);
                    $cmt   = (float) ($st->commitments ?? 0);
                    $inp   = (float) ($st->in_process ?? 0);
                    $avail = (float) ($st->available ?? 0);
                    $spent = (float) ($st->can_be_spent ?? 0);

                    $totAlloc += $alloc;
                    $totRec   += $rec;
                    $totExp   += $exp;
                    $totBal   += $bal;
                    $totCmt   += $cmt;
                    $totInp   += $inp;
                    $totAvail += $avail;
                    $totSpent += $spent;

                    $headStatuses[] = [
                        'hed_id'                => $h->hed_id,
                        'hed_code'              => $h->hed_code,
                        'hed_name'              => $h->hed_name,
                        'prj_id'                => $h->prj_id,
                        'prj_code'              => $h->prj_code ?: $h->hed_code,
                        'prj_title'             => $h->prj_title ?: $h->hed_name,
                        'unt_id'                => $h->unt_id,
                        'division'              => $h->division ?: 'General',
                        'division_full'         => $h->unt_name ?: ($h->division ?: 'General'),
                        'allocation'            => $alloc,
                        'received'              => $rec,
                        'expenditure'           => $exp,
                        'balance'               => $bal,
                        'commitments'           => $cmt,
                        'in_process'            => $inp,
                        'available'             => $avail,
                        'can_be_spent'          => $spent,
                        'pct_utilized'          => $alloc > 0 ? round(($exp / $alloc) * 100, 1) : 0,
                        'expenditure_drilldown' => route('division.finance-of-project.drilldown', [$h->hed_id, 'pcc', 'expenditure']),
                        'commitments_drilldown' => route('division.finance-of-project.drilldown', [$h->hed_id, 'pcc', 'commitments']),
                        'in_process_drilldown'  => route('division.finance-of-project.drilldown', [$h->hed_id, 'pcc', 'in-process']),
                        'full_report_url'       => route('projects.financial_view', $h->hed_id),
                        'project_details_url'   => $h->prj_id ? route('projects.show', $h->prj_id) : '#',
                    ];
                }

                $macroTotals = [
                    'allocation'      => $totAlloc,
                    'received'        => $totRec,
                    'expenditure'     => $totExp,
                    'balance'         => $totBal,
                    'commitments'     => $totCmt,
                    'in_process'      => $totInp,
                    'available'       => $totAvail,
                    'can_be_spent'    => $totSpent,
                    'utilization_pct' => $totAlloc > 0 ? round(($totExp / $totAlloc) * 100, 1) : 0,
                    'released_pct'    => $totAlloc > 0 ? round(($totRec / $totAlloc) * 100, 1) : 0,
                ];

                // 2. Commitments & Disbursement Pipeline Detailed Groups (for reactive filtering)
                $cmtDetailedGroups = DB::table('fin.commitments as c')
                    ->leftJoin('cen.heads as h', 'c.cmt_hed_id', '=', 'h.hed_id')
                    ->leftJoin('cen.units as u', DB::raw('coalesce(c.cmt_unt_id, h.hed_unt_id)'), '=', 'u.unt_id')
                    ->selectRaw("
                        coalesce(c.cmt_unt_id, h.hed_unt_id) as unt_id,
                        coalesce(u.unt_namesh, u.unt_name, '') as division_namesh,
                        coalesce(u.unt_name, u.unt_namesh, '') as division_name,
                        c.cmt_hed_id as hed_id,
                        (CASE WHEN c.cmt_type = 'Sa' THEN 'salary' ELSE 'purchase' END) as kind,
                        c.cmt_status as status,
                        count(*) as cnt,
                        coalesce(sum(abs(c.cmt_amount)), 0) as amt
                    ")
                    ->where(function($q) use ($lower, $upper) {
                        $q->whereBetween('c.cmt_unt_id', [$lower, $upper])
                          ->orWhereBetween('h.hed_unt_id', [$lower, $upper]);
                    })
                    ->groupBy(DB::raw('coalesce(c.cmt_unt_id, h.hed_unt_id)'), 'u.unt_namesh', 'u.unt_name', 'c.cmt_hed_id', DB::raw("CASE WHEN c.cmt_type = 'Sa' THEN 'salary' ELSE 'purchase' END"), 'c.cmt_status')
                    ->get()
                    ->toArray();

                $cmtPurAwaited = DB::table('fin.commitments')
                    ->where('cmt_type', '!=', 'Sa')
                    ->where('cmt_status', 'Awaited')
                    ->whereBetween('cmt_unt_id', [$lower, $upper])
                    ->selectRaw('count(*) as cnt, sum(abs(cmt_amount)) as amt')
                    ->first();

                $cmtPurPaid = DB::table('fin.commitments')
                    ->where('cmt_type', '!=', 'Sa')
                    ->where('cmt_status', 'Paid')
                    ->whereBetween('cmt_unt_id', [$lower, $upper])
                    ->selectRaw('count(*) as cnt, sum(abs(cmt_amount)) as amt')
                    ->first();

                $cmtSalAwaited = DB::table('fin.commitments')
                    ->where('cmt_type', '=', 'Sa')
                    ->where('cmt_status', 'Awaited')
                    ->whereBetween('cmt_unt_id', [$lower, $upper])
                    ->selectRaw('count(*) as cnt, sum(abs(cmt_amount)) as amt')
                    ->first();

                $cmtSalPaid = DB::table('fin.commitments')
                    ->where('cmt_type', '=', 'Sa')
                    ->where('cmt_status', 'Paid')
                    ->whereBetween('cmt_unt_id', [$lower, $upper])
                    ->selectRaw('count(*) as cnt, sum(abs(cmt_amount)) as amt')
                    ->first();

                $commitmentsStats = [
                    'pur_awaited_cnt'   => (int) ($cmtPurAwaited->cnt ?? 0),
                    'pur_awaited_amt'   => (float) ($cmtPurAwaited->amt ?? 0),
                    'pur_paid_cnt'      => (int) ($cmtPurPaid->cnt ?? 0),
                    'pur_paid_amt'      => (float) ($cmtPurPaid->amt ?? 0),
                    'sal_awaited_cnt'   => (int) ($cmtSalAwaited->cnt ?? 0),
                    'sal_awaited_amt'   => (float) ($cmtSalAwaited->amt ?? 0),
                    'sal_paid_cnt'      => (int) ($cmtSalPaid->cnt ?? 0),
                    'sal_paid_amt'      => (float) ($cmtSalPaid->amt ?? 0),
                    'total_awaited_cnt' => (int) (($cmtPurAwaited->cnt ?? 0) + ($cmtSalAwaited->cnt ?? 0)),
                    'total_awaited_amt' => (float) (($cmtPurAwaited->amt ?? 0) + ($cmtSalAwaited->amt ?? 0)),
                ];

                // 3. Contracts & Salary Verification Stats & Detailed Groups
                $verifDetailedGroups = DB::table('hr.contracts as c')
                    ->leftJoin('cen.heads as h', 'c.ctr_hed_id', '=', 'h.hed_id')
                    ->leftJoin('cen.units as u', DB::raw('coalesce(c.ctr_unt_id, h.hed_unt_id)'), '=', 'u.unt_id')
                    ->leftJoin('fin.contractsverif as v', 'c.ctr_id', '=', 'v.cvf_ctr_id')
                    ->where(function($q) use ($lower, $upper) {
                        $q->whereBetween('c.ctr_unt_id', [$lower, $upper])
                          ->orWhereBetween('h.hed_unt_id', [$lower, $upper]);
                    })
                    ->selectRaw("
                        coalesce(c.ctr_unt_id, h.hed_unt_id) as unt_id,
                        coalesce(u.unt_namesh, u.unt_name, '') as division_namesh,
                        coalesce(u.unt_name, u.unt_namesh, '') as division_name,
                        c.ctr_hed_id as hed_id,
                        count(*) as total_cnt,
                        count(case when v.cvf_verif = true then 1 end) as verified_cnt,
                        count(case when v.cvf_ctr_id is null or v.cvf_verif = false then 1 end) as pending_cnt
                    ")
                    ->groupBy(DB::raw('coalesce(c.ctr_unt_id, h.hed_unt_id)'), 'u.unt_namesh', 'u.unt_name', 'c.ctr_hed_id')
                    ->get()
                    ->toArray();

                $effHeadDetailedGroups = DB::table('fin.empeffheads as eh')
                    ->leftJoin('hr.emps as e', 'eh.eeh_emp_id', '=', 'e.emp_id')
                    ->leftJoin('cen.heads as h', 'eh.eeh_emphed_id', '=', 'h.hed_id')
                    ->leftJoin('cen.units as u', DB::raw('coalesce(e.emp_unt_id, h.hed_unt_id)'), '=', 'u.unt_id')
                    ->where(function($q) use ($lower, $upper) {
                        $q->whereBetween('e.emp_unt_id', [$lower, $upper])
                          ->orWhereBetween('h.hed_unt_id', [$lower, $upper]);
                    })
                    ->selectRaw("
                        coalesce(e.emp_unt_id, h.hed_unt_id) as unt_id,
                        coalesce(u.unt_namesh, u.unt_name, '') as division_namesh,
                        coalesce(u.unt_name, u.unt_namesh, '') as division_name,
                        eh.eeh_emphed_id as hed_id,
                        count(*) as cnt
                    ")
                    ->groupBy(DB::raw('coalesce(e.emp_unt_id, h.hed_unt_id)'), 'u.unt_namesh', 'u.unt_name', 'eh.eeh_emphed_id')
                    ->get()
                    ->toArray();

                $verifTotal = DB::table('hr.contracts as c')
                    ->leftJoin('cen.heads as h', 'c.ctr_hed_id', '=', 'h.hed_id')
                    ->where(function($q) use ($lower, $upper) {
                        $q->whereBetween('c.ctr_unt_id', [$lower, $upper])
                          ->orWhereBetween('h.hed_unt_id', [$lower, $upper]);
                    })
                    ->count();

                $verifDone = DB::table('fin.contractsverif as v')
                    ->join('hr.contracts as c', 'v.cvf_ctr_id', '=', 'c.ctr_id')
                    ->leftJoin('cen.heads as h', 'c.ctr_hed_id', '=', 'h.hed_id')
                    ->where('v.cvf_verif', true)
                    ->where(function($q) use ($lower, $upper) {
                        $q->whereBetween('c.ctr_unt_id', [$lower, $upper])
                          ->orWhereBetween('h.hed_unt_id', [$lower, $upper]);
                    })
                    ->count();

                $verifPending = DB::table('hr.contracts as c')
                    ->leftJoin('cen.heads as h', 'c.ctr_hed_id', '=', 'h.hed_id')
                    ->leftJoin('fin.contractsverif as v', 'c.ctr_id', '=', 'v.cvf_ctr_id')
                    ->where(function($q) use ($lower, $upper) {
                        $q->whereBetween('c.ctr_unt_id', [$lower, $upper])
                          ->orWhereBetween('h.hed_unt_id', [$lower, $upper]);
                    })
                    ->where(function ($q) {
                        $q->whereNull('v.cvf_ctr_id')->orWhere('v.cvf_verif', false);
                    })
                    ->count();

                $effHeadsCount = DB::table('fin.empeffheads as eh')
                    ->leftJoin('hr.emps as e', 'eh.eeh_emp_id', '=', 'e.emp_id')
                    ->leftJoin('cen.heads as h', 'eh.eeh_emphed_id', '=', 'h.hed_id')
                    ->where(function($q) use ($lower, $upper) {
                        $q->whereBetween('e.emp_unt_id', [$lower, $upper])
                          ->orWhereBetween('h.hed_unt_id', [$lower, $upper]);
                    })
                    ->count();

                $verificationStats = [
                    'total_contracts'      => $verifTotal,
                    'verified_contracts'   => $verifDone,
                    'pending_contracts'    => $verifPending,
                    'effective_heads_count'=> $effHeadsCount,
                    'verified_pct'         => $verifTotal > 0 ? round(($verifDone / $verifTotal) * 100, 1) : 0,
                ];

                // 4. Monthly Disbursement Trends (Last 12 Months)
                $monthlyRows = DB::table('fin.transactions')
                    ->selectRaw("TO_CHAR(trn_date, 'YYYY-MM') as ym, sum(abs(trn_amount2)) as total_disbursed, count(*) as tx_count")
                    ->whereNotNull('trn_date')
                    ->where('trn_date', '>=', now()->subMonths(11)->startOfMonth()->toDateString())
                    ->groupBy(DB::raw("TO_CHAR(trn_date, 'YYYY-MM')"))
                    ->orderBy('ym', 'asc')
                    ->get();

                $monthlyTrends = $monthlyRows->map(function ($row) {
                    return [
                        'ym'     => $row->ym,
                        'label'  => \Carbon\Carbon::createFromFormat('Y-m', $row->ym)->format('M Y'),
                        'amount' => (float) $row->total_disbursed,
                        'count'  => (int) $row->tx_count,
                    ];
                })->values()->toArray();

                // 5. Recent Disbursed Financial Transactions (Live Ledger Preview with Division and Head attributes)
                $recentTransactions = DB::table('fin.transactions as t')
                    ->join('fin.commitments as c', 't.trn_cmt_id', '=', 'c.cmt_id')
                    ->leftJoin('cen.heads as h', 'c.cmt_hed_id', '=', 'h.hed_id')
                    ->leftJoin('cen.units as u', DB::raw('coalesce(c.cmt_unt_id, h.hed_unt_id)'), '=', 'u.unt_id')
                    ->leftJoin('pur.purcases as p', function ($join) {
                        $join->on('c.cmt_docid', '=', 'p.pcs_id')
                             ->where('c.cmt_type', '!=', 'Sa');
                    })
                    ->leftJoin('fin.salorders as s', function ($join) {
                        $join->on('c.cmt_docid', '=', 's.sor_id')
                             ->where('c.cmt_type', '=', 'Sa');
                    })
                    ->leftJoin('frm.firmz as f', 'p.pcs_frm_id', '=', 'f.frm_id')
                    ->where(function($q) use ($lower, $upper) {
                        $q->whereBetween('c.cmt_unt_id', [$lower, $upper])
                          ->orWhereBetween('h.hed_unt_id', [$lower, $upper]);
                    })
                    ->orderBy('t.trn_id', 'desc')
                    ->take(30)
                    ->select([
                        't.trn_id',
                        't.trn_date',
                        't.trn_amount1',
                        't.trn_tax1',
                        't.trn_amount2',
                        'c.cmt_id',
                        'c.cmt_type',
                        'c.cmt_status',
                        'c.cmt_unt_id',
                        'h.hed_unt_id',
                        'c.cmt_hed_id',
                        'u.unt_name as division_name',
                        'u.unt_namesh as division_namesh',
                        'h.hed_code',
                        'p.pcs_id',
                        'p.pcs_title',
                        'f.frm_name',
                        's.sor_id',
                        's.sor_empnamecomp',
                        's.sor_month',
                    ])
                    ->get()
                    ->map(function ($r) {
                        $desc = $r->cmt_type === 'Sa'
                            ? 'Salary: ' . ($r->sor_empnamecomp ?: 'Employee #' . $r->sor_id) . ' (' . $r->sor_month . ')'
                            : ($r->pcs_title ?: ($r->frm_name ?: 'Purchase Case #' . $r->pcs_id));
                        return [
                            'trn_id'          => $r->trn_id,
                            'cmt_id'          => $r->cmt_id,
                            'date'            => $r->trn_date,
                            'type'            => $r->cmt_type === 'Sa' ? 'Salary' : 'Purchase',
                            'division'        => $r->division_namesh ?: ($r->division_name ?: ''),
                            'division_full'   => $r->division_name ?: ($r->division_namesh ?: ''),
                            'division_id'     => $r->cmt_unt_id ?: $r->hed_unt_id,
                            'hed_id'          => $r->cmt_hed_id,
                            'head'            => $r->hed_code ?: 'N/A',
                            'desc'            => $desc,
                            'amount'          => abs((float) $r->trn_amount2),
                            'tax'             => abs((float) $r->trn_tax1),
                            'status'          => $r->cmt_status,
                        ];
                    })->toArray();

                return [
                    'headStatuses'          => $headStatuses,
                    'macroTotals'           => $macroTotals,
                    'commitmentsStats'      => $commitmentsStats,
                    'verificationStats'     => $verificationStats,
                    'monthlyTrends'         => $monthlyTrends,
                    'recentTransactions'    => $recentTransactions,
                    'cmtDetailedGroups'     => $cmtDetailedGroups,
                    'verifDetailedGroups'   => $verifDetailedGroups,
                    'effHeadDetailedGroups' => $effHeadDetailedGroups,
                ];
            });

        return view('fin.dashboard', [
            'mode'                  => $mode,
            'lower'                 => $lower,
            'upper'                 => $upper,
            'varModeStr'            => $varModeStr,
            'userAuth'              => $userAuth,
            'divisions'             => $divisions,
            'headStatuses'          => $dashboardData['headStatuses'],
            'macroTotals'           => $dashboardData['macroTotals'],
            'commitmentsStats'      => $dashboardData['commitmentsStats'],
            'verificationStats'     => $dashboardData['verificationStats'],
            'monthlyTrends'         => $dashboardData['monthlyTrends'],
            'recentTransactions'    => $dashboardData['recentTransactions'],
            'cmtDetailedGroups'     => $dashboardData['cmtDetailedGroups'] ?? [],
            'verifDetailedGroups'   => $dashboardData['verifDetailedGroups'] ?? [],
            'effHeadDetailedGroups' => $dashboardData['effHeadDetailedGroups'] ?? [],
        ]);
    }

    public function hrDashboard(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Determine Mode ('m' = All Dept / Global, 's' = My Dept / Section) with Session Persistence
        if ($request->has('mode')) {
            $mode = $request->query('mode') === 's' ? 's' : 'm';
            session(['hr_mode' => $mode]);
        } else {
            $mode = session('hr_mode', 'm');
        }

        if ($mode === 's') {
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
        } else {
            $lower = $user->acc_lowerm ?? 0;
            $upper = $user->acc_upperm ?? 99999999;
            if ($lower == 0 && $upper == 0) {
                $lower = 0;
                $upper = 99999999;
            }
        }

        // 1. Employee Key Metrics (Bounded by mode lower/upper)
        $allEmployees = Employee::query()
            ->leftJoin('cen.heads as h', 'hr.emps.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'hr.emps.emp_unt_id', '=', 'u.unt_id')
            ->whereBetween('hr.emps.emp_unt_id', [$lower, $upper])
            ->select('hr.emps.*', 'h.hed_code', 'u.unt_name', 'u.unt_namesh')
            ->get();

        $activeEmployees = $allEmployees->filter(function($e) {
            return in_array(strtolower($e->emp_status ?? ''), ['active', 'current']);
        });
        $activeCount = $activeEmployees->count();
        $previousCount = $allEmployees->count() - $activeCount;
        $totalEmployees = $allEmployees->count();

        // 2. Unit/Division Breakdown
        $unitBreakdown = DB::table('hr.emps as e')
            ->join('cen.units as u', 'u.unt_id', '=', 'e.emp_unt_id')
            ->whereBetween('e.emp_unt_id', [$lower, $upper])
            ->select(
                'u.unt_id',
                'u.unt_name',
                'u.unt_namesh',
                DB::raw("COUNT(CASE WHEN LOWER(e.emp_status) IN ('active', 'current') THEN 1 END) as active_count"),
                DB::raw("COUNT(*) as total_count")
            )
            ->groupBy('u.unt_id', 'u.unt_name', 'u.unt_namesh')
            ->orderBy('active_count', 'desc')
            ->get();

        // 3. Contracts Overview
        $totalContracts = DB::table('hr.contracts as c')
            ->join('hr.emps as e', 'e.emp_id', '=', 'c.ctr_num')
            ->whereBetween('e.emp_unt_id', [$lower, $upper])
            ->count();

        $now = now();
        $expiringContracts = DB::table('hr.contracts as c')
            ->join('hr.emps as e', 'e.emp_id', '=', 'c.ctr_num')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'e.emp_unt_id')
            ->whereBetween('e.emp_unt_id', [$lower, $upper])
            ->where('c.ctr_enddt', '>=', $now->copy()->subDays(15))
            ->where('c.ctr_enddt', '<=', $now->copy()->addDays(90))
            ->select('c.*', 'e.emp_name', 'u.unt_namesh as unit_name')
            ->orderBy('c.ctr_enddt', 'asc')
            ->take(10)
            ->get();

        // 4. Contract Cases (Pipeline)
        $contractCases = \App\Models\HrCtrCase::query()
            ->orderBy('ctc_id', 'desc')
            ->take(10)
            ->get();

        $pendingCasesCount = \App\Models\HrCtrCase::where('ctc_status', 'Under HR Scrutiny')->count();
        $inApprovalCasesCount = \App\Models\HrCtrCase::whereIn('ctc_status', ['Under Finance Scrutiny', 'Under Approval'])->count();
        $approvedCasesCount = \App\Models\HrCtrCase::where('ctc_status', 'Approved')->count();

        // 5. Recent Joiners (Last 8 Employees)
        $recentEmployees = $activeEmployees->sortByDesc('emp_joindt')->take(8);

        return view('hr.dashboard', compact(
            'mode',
            'activeCount',
            'previousCount',
            'totalEmployees',
            'unitBreakdown',
            'totalContracts',
            'expiringContracts',
            'contractCases',
            'pendingCasesCount',
            'inApprovalCasesCount',
            'approvedCasesCount',
            'recentEmployees'
        ));
    }
}
