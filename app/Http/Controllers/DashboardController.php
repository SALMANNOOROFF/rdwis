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

        $unitId = $user->acc_unt_id;

        // 1. Project Stats
        $projects = Project::where('prj_unt_id', $unitId)->get();
        $totalProjects = $projects->count();
        $hucProjects = $projects->where('prj_status', 'Completed')->count(); // HUC = Completed
        
        // 2. Resource Stats (Books & Licenses)
        // Check purchase cases for specific remarks or types
        $booksCount = Purchase::where('pcs_unt_id', $unitId)
            ->where(function($q) {
                $q->where('pcs_type', 'book')
                  ->orWhere('pcs_remarks', 'LIKE', '%"resource_type":"book"%');
            })->count();

        $licenseCount = Purchase::where('pcs_unt_id', $unitId)
            ->where(function($q) {
                $q->where('pcs_type', 'license')
                  ->orWhere('pcs_remarks', 'LIKE', '%"resource_type":"license"%');
            })->count();

        // 3. Procurement Cases
        $activeCases = Purchase::where('pcs_unt_id', $unitId)
            ->whereIn('pcs_status', ['Draft', 'Under Scrutiny', 'In Progress'])
            ->count();
        
        $totalCases = Purchase::where('pcs_unt_id', $unitId)->count();

        // 4. Financials (Project-wise Spent vs Approved)
        $financials = DB::table('prj.projects as p')
            ->leftJoin('pur.purcases as pc', 'pc.pcs_hed_id', '=', 'p.prj_id')
            ->select(
                'p.prj_title',
                'p.prj_aprvcost as approved',
                DB::raw('COALESCE(SUM(pc.pcs_price), 0) as spent')
            )
            ->where('p.prj_unt_id', $unitId)
            ->groupBy('p.prj_id', 'p.prj_title', 'p.prj_aprvcost')
            ->get();

        // 5. Employees per Project
        $employeeStats = DB::table('cen.heads as h')
            ->leftJoin('hr.emps as e', 'e.emp_hed_id', '=', 'h.hed_id')
            ->select('h.hed_code', DB::raw('COUNT(e.emp_id) as emp_count'))
            ->where('h.hed_unt_id', $unitId)
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

        [$lower, $upper] = $user->acc_lowers == 0
            ? [$user->acc_lowerm, $user->acc_upperm]
            : [$user->acc_lowers, $user->acc_uppers];

        $divisionParam = $request->query('division', 'all');
        $projectParam = $request->query('project_id');

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
            } else {
                $divisionParam = 'all';
                $selectedUnitIds = $allDivisions->pluck('unt_id')->map(fn ($v) => (int) $v)->values()->all();
            }
        }

        $closedProjectStatuses = ['Closed', 'Completed'];
        $pendingCaseStatuses = ['Draft', 'Under Scrutiny', 'In Progress'];
        $reviewedCaseStatuses = ['Approved', 'Fulfilled', 'Completed'];

        $budgetReceivedQuery = DB::table('prj.projects');
        $utilizedQuery = DB::table('pur.purcases');
        $creditQuery = DB::table('pur.purcases');

        if ($selectedProject) {
            $budgetReceivedQuery->where('prj_id', $selectedProject['id']);
            $utilizedQuery->where('pcs_hed_id', $selectedProject['id']);
            $creditQuery->where('pcs_hed_id', $selectedProject['id']);
        } else {
            $budgetReceivedQuery->whereIn('prj_unt_id', $selectedUnitIds);
            $utilizedQuery->whereIn('pcs_unt_id', $selectedUnitIds);
            $creditQuery->whereIn('pcs_unt_id', $selectedUnitIds);
        }

        $budgetReceived = (float) $budgetReceivedQuery->sum('prj_aprvcost');
        $utilized = (float) $utilizedQuery->sum('pcs_price');

        $remaining = max(0, $budgetReceived - $utilized);

        $creditTaken = (float) $creditQuery
            ->where('pcs_noloan', false)
            ->sum('pcs_intprice');

        if ($selectedProject) {
            $employeesTotal = (int) DB::table('cen.heads as h')
                ->leftJoin('hr.emps as e', 'e.emp_hed_id', '=', 'h.hed_id')
                ->where('h.hed_prj_id', $selectedProject['id'])
                ->count('e.emp_id');

            $employeesHired = (int) DB::table('cen.heads as h')
                ->leftJoin('hr.emps as e', 'e.emp_hed_id', '=', 'h.hed_id')
                ->where('h.hed_prj_id', $selectedProject['id'])
                ->where('e.emp_joindt', '>=', now()->subYear()->toDateString())
                ->count('e.emp_id');
        } else {
            $employeesTotal = (int) DB::table('hr.emps')
                ->whereIn('emp_unt_id', $selectedUnitIds)
                ->count();

            $employeesHired = (int) DB::table('hr.emps')
                ->whereIn('emp_unt_id', $selectedUnitIds)
                ->where('emp_joindt', '>=', now()->subYear()->toDateString())
                ->count();
        }

        $projectsTotalQuery = DB::table('prj.projects');
        if ($selectedProject) {
            $projectsTotalQuery->where('prj_id', $selectedProject['id']);
        } else {
            $projectsTotalQuery->whereIn('prj_unt_id', $selectedUnitIds);
        }
        $projectsTotal = (int) $projectsTotalQuery->count();

        $projectsCompletedQuery = DB::table('prj.projects')->whereIn('prj_status', $closedProjectStatuses);
        if ($selectedProject) {
            $projectsCompletedQuery->where('prj_id', $selectedProject['id']);
        } else {
            $projectsCompletedQuery->whereIn('prj_unt_id', $selectedUnitIds);
        }
        $projectsCompleted = (int) $projectsCompletedQuery->count();

        $today = now()->toDateString();
        $projectsDelayedQuery = DB::table('prj.projects')
            ->whereNotIn('prj_status', $closedProjectStatuses)
            ->whereNotNull('prj_estenddt')
            ->where('prj_estenddt', '<', $today);
        if ($selectedProject) {
            $projectsDelayedQuery->where('prj_id', $selectedProject['id']);
        } else {
            $projectsDelayedQuery->whereIn('prj_unt_id', $selectedUnitIds);
        }
        $projectsDelayed = (int) $projectsDelayedQuery->count();

        $projectsOngoing = max(0, $projectsTotal - $projectsCompleted);

        $pendingCasesQuery = DB::table('pur.purcases')->whereIn('pcs_status', $pendingCaseStatuses);
        if ($selectedProject) {
            $pendingCasesQuery->where('pcs_hed_id', $selectedProject['id']);
        } else {
            $pendingCasesQuery->whereIn('pcs_unt_id', $selectedUnitIds);
        }
        $pendingCases = (int) $pendingCasesQuery->count();

        $reviewedCasesQuery = DB::table('pur.purcases')->whereIn('pcs_status', $reviewedCaseStatuses);
        if ($selectedProject) {
            $reviewedCasesQuery->where('pcs_hed_id', $selectedProject['id']);
        } else {
            $reviewedCasesQuery->whereIn('pcs_unt_id', $selectedUnitIds);
        }
        $reviewedCases = (int) $reviewedCasesQuery->count();

        $casesPerDivisionQuery = DB::table('pur.purcases as c')
            ->join('cen.units as u', 'u.unt_id', '=', 'c.pcs_unt_id')
            ->where('u.unt_type', 'Division')
            ->select('u.unt_namesh as division', DB::raw('COUNT(*) as cnt'))
            ->groupBy('u.unt_namesh')
            ->orderByDesc('cnt');

        if ($selectedProject) {
            $casesPerDivisionQuery->where('c.pcs_hed_id', $selectedProject['id']);
        } else {
            $casesPerDivisionQuery->whereIn('c.pcs_unt_id', $selectedUnitIds);
        }

        $casesPerDivision = $casesPerDivisionQuery->get()
            ->map(fn ($r) => ['division' => (string) $r->division, 'count' => (int) $r->cnt])
            ->values();

        $employeesPerDivision = DB::table('hr.emps as e')
            ->join('cen.units as u', 'u.unt_id', '=', 'e.emp_unt_id')
            ->whereIn('e.emp_unt_id', $selectedUnitIds)
            ->where('u.unt_type', 'Division')
            ->select('u.unt_namesh as division', DB::raw('COUNT(*) as cnt'))
            ->groupBy('u.unt_namesh')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn ($r) => ['division' => (string) $r->division, 'count' => (int) $r->cnt])
            ->values();

        $projectsPerDivision = DB::table('prj.projects as p')
            ->join('cen.units as u', 'u.unt_id', '=', 'p.prj_unt_id')
            ->whereIn('p.prj_unt_id', $selectedUnitIds)
            ->where('u.unt_type', 'Division')
            ->select('u.unt_namesh as division', DB::raw('COUNT(*) as cnt'))
            ->groupBy('u.unt_namesh')
            ->orderByDesc('cnt')
            ->get()
            ->map(fn ($r) => ['division' => (string) $r->division, 'count' => (int) $r->cnt])
            ->values();

        $budgetByDivision = DB::table('prj.projects as p')
            ->join('cen.units as u', 'u.unt_id', '=', 'p.prj_unt_id')
            ->whereIn('p.prj_unt_id', $selectedUnitIds)
            ->where('u.unt_type', 'Division')
            ->select('u.unt_namesh as division', DB::raw('SUM(p.prj_aprvcost) as budget'))
            ->groupBy('u.unt_namesh')
            ->pluck('budget', 'division');

        $utilByDivision = DB::table('pur.purcases as c')
            ->join('cen.units as u', 'u.unt_id', '=', 'c.pcs_unt_id')
            ->whereIn('c.pcs_unt_id', $selectedUnitIds)
            ->where('u.unt_type', 'Division')
            ->select('u.unt_namesh as division', DB::raw('SUM(c.pcs_price) as utilized'))
            ->groupBy('u.unt_namesh')
            ->pluck('utilized', 'division');

        $budgetVsUtilPerDivision = $allDivisions
            ->filter(fn ($d) => in_array((int) $d->unt_id, $selectedUnitIds, true))
            ->map(function ($d) use ($budgetByDivision, $utilByDivision) {
                $key = (string) $d->unt_namesh;
                return [
                    'division' => $key,
                    'budget' => (float) ($budgetByDivision[$key] ?? 0),
                    'utilized' => (float) ($utilByDivision[$key] ?? 0),
                ];
            })
            ->values();

        $monthlyPeriods = collect(range(0, 11))->reverse()->map(function ($i) {
            return now()->subMonths($i)->format('Y-m');
        })->values();

        if ($selectedProject) {
            $budgetByMonth = DB::table('prj.projects')
                ->where('prj_id', $selectedProject['id'])
                ->whereNotNull('prj_aprvdt')
                ->select(DB::raw("to_char(prj_aprvdt, 'YYYY-MM') as ym"), DB::raw('SUM(prj_aprvcost) as budget'))
                ->groupBy(DB::raw("to_char(prj_aprvdt, 'YYYY-MM')"))
                ->pluck('budget', 'ym');

            $utilByMonth = DB::table('pur.purcases')
                ->where('pcs_hed_id', $selectedProject['id'])
                ->whereNotNull('pcs_date')
                ->select(DB::raw("to_char(pcs_date, 'YYYY-MM') as ym"), DB::raw('SUM(pcs_price) as utilized'))
                ->groupBy(DB::raw("to_char(pcs_date, 'YYYY-MM')"))
                ->pluck('utilized', 'ym');
        } else {
            $budgetByMonth = DB::table('prj.projects')
                ->whereIn('prj_unt_id', $selectedUnitIds)
                ->whereNotNull('prj_aprvdt')
                ->select(DB::raw("to_char(prj_aprvdt, 'YYYY-MM') as ym"), DB::raw('SUM(prj_aprvcost) as budget'))
                ->groupBy(DB::raw("to_char(prj_aprvdt, 'YYYY-MM')"))
                ->pluck('budget', 'ym');

            $utilByMonth = DB::table('pur.purcases')
                ->whereIn('pcs_unt_id', $selectedUnitIds)
                ->whereNotNull('pcs_date')
                ->select(DB::raw("to_char(pcs_date, 'YYYY-MM') as ym"), DB::raw('SUM(pcs_price) as utilized'))
                ->groupBy(DB::raw("to_char(pcs_date, 'YYYY-MM')"))
                ->pluck('utilized', 'ym');
        }

        $monthlySeries = $monthlyPeriods->map(function ($ym) use ($budgetByMonth, $utilByMonth) {
            return [
                'period' => $ym,
                'budget' => (float) ($budgetByMonth[$ym] ?? 0),
                'utilized' => (float) ($utilByMonth[$ym] ?? 0),
            ];
        })->values();

        $quarterPeriods = collect(range(0, 7))->reverse()->map(function ($i) {
            $d = now()->subQuarters($i);
            $q = (int) ceil(((int) $d->format('n')) / 3);
            return $d->format('Y') . '-Q' . $q;
        })->values();

        if ($selectedProject) {
            $budgetByQuarter = DB::table('prj.projects')
                ->where('prj_id', $selectedProject['id'])
                ->whereNotNull('prj_aprvdt')
                ->select(
                    DB::raw("concat(extract(year from prj_aprvdt)::int, '-Q', extract(quarter from prj_aprvdt)::int) as yq"),
                    DB::raw('SUM(prj_aprvcost) as budget')
                )
                ->groupBy(DB::raw("concat(extract(year from prj_aprvdt)::int, '-Q', extract(quarter from prj_aprvdt)::int)"))
                ->pluck('budget', 'yq');

            $utilByQuarter = DB::table('pur.purcases')
                ->where('pcs_hed_id', $selectedProject['id'])
                ->whereNotNull('pcs_date')
                ->select(
                    DB::raw("concat(extract(year from pcs_date)::int, '-Q', extract(quarter from pcs_date)::int) as yq"),
                    DB::raw('SUM(pcs_price) as utilized')
                )
                ->groupBy(DB::raw("concat(extract(year from pcs_date)::int, '-Q', extract(quarter from pcs_date)::int)"))
                ->pluck('utilized', 'yq');
        } else {
            $budgetByQuarter = DB::table('prj.projects')
                ->whereIn('prj_unt_id', $selectedUnitIds)
                ->whereNotNull('prj_aprvdt')
                ->select(
                    DB::raw("concat(extract(year from prj_aprvdt)::int, '-Q', extract(quarter from prj_aprvdt)::int) as yq"),
                    DB::raw('SUM(prj_aprvcost) as budget')
                )
                ->groupBy(DB::raw("concat(extract(year from prj_aprvdt)::int, '-Q', extract(quarter from prj_aprvdt)::int)"))
                ->pluck('budget', 'yq');

            $utilByQuarter = DB::table('pur.purcases')
                ->whereIn('pcs_unt_id', $selectedUnitIds)
                ->whereNotNull('pcs_date')
                ->select(
                    DB::raw("concat(extract(year from pcs_date)::int, '-Q', extract(quarter from pcs_date)::int) as yq"),
                    DB::raw('SUM(pcs_price) as utilized')
                )
                ->groupBy(DB::raw("concat(extract(year from pcs_date)::int, '-Q', extract(quarter from pcs_date)::int)"))
                ->pluck('utilized', 'yq');
        }

        $quarterlySeries = $quarterPeriods->map(function ($yq) use ($budgetByQuarter, $utilByQuarter) {
            return [
                'period' => $yq,
                'budget' => (float) ($budgetByQuarter[$yq] ?? 0),
                'utilized' => (float) ($utilByQuarter[$yq] ?? 0),
            ];
        })->values();

        $projRowsQuery = DB::table('prj.projects as p')
            ->join('cen.units as u', 'u.unt_id', '=', 'p.prj_unt_id')
            ->leftJoin('pur.purcases as c', 'c.pcs_hed_id', '=', 'p.prj_id')
            ->select(
                'p.prj_id',
                'p.prj_code',
                'p.prj_title',
                'p.prj_status',
                'p.prj_startdt',
                'p.prj_estenddt',
                'u.unt_namesh as division',
                DB::raw('COALESCE(p.prj_aprvcost, 0) as budget'),
                DB::raw('COALESCE(SUM(c.pcs_price), 0) as utilized'),
                DB::raw("COALESCE(SUM(CASE WHEN c.pcs_noloan = false THEN c.pcs_intprice ELSE 0 END), 0) as credit")
            )
            ->groupBy(
                'p.prj_id',
                'p.prj_code',
                'p.prj_title',
                'p.prj_status',
                'p.prj_startdt',
                'p.prj_estenddt',
                'u.unt_namesh',
                'p.prj_aprvcost'
            )
            ->orderByDesc('p.prj_id')
            ->limit(500)
            ;

        if ($selectedProject) {
            $projRowsQuery->where('p.prj_id', $selectedProject['id']);
        } else {
            $projRowsQuery->whereIn('p.prj_unt_id', $selectedUnitIds);
        }

        $projRows = $projRowsQuery->get();

        $employeesByProject = DB::table('cen.heads as h')
            ->join('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
            ->leftJoin('hr.emps as e', 'e.emp_hed_id', '=', 'h.hed_id')
            ->whereIn('p.prj_unt_id', $selectedUnitIds)
            ->select('p.prj_id', DB::raw('COUNT(e.emp_id) as emp_count'))
            ->groupBy('p.prj_id')
            ->pluck('emp_count', 'prj_id');

        $projectProgressTop = $projRows
            ->filter(fn ($p) => ! in_array((string) $p->prj_status, $closedProjectStatuses, true))
            ->map(function ($p) use ($closedProjectStatuses) {
                $progress = 0;
                if (in_array((string) $p->prj_status, $closedProjectStatuses, true)) {
                    $progress = 100;
                } elseif ($p->prj_startdt && $p->prj_estenddt) {
                    $start = \Carbon\Carbon::parse($p->prj_startdt);
                    $end = \Carbon\Carbon::parse($p->prj_estenddt);
                    if ($end->greaterThan($start)) {
                        $progress = (int) round(min(100, max(0, $start->floatDiffInDays(now()) / max(1, $start->floatDiffInDays($end)) * 100)));
                    }
                }

                return [
                    'code' => (string) $p->prj_code,
                    'percent' => $progress,
                ];
            })
            ->sortByDesc('percent')
            ->take(10)
            ->values();

        $tableProjects = $projRows->map(function ($p) use ($closedProjectStatuses, $employeesByProject) {
            $budget = (float) $p->budget;
            $used = (float) $p->utilized;
            $remaining = max(0, $budget - $used);

            $derivedStatus = 'Ongoing';
            $isClosed = in_array((string) $p->prj_status, $closedProjectStatuses, true);
            if ($isClosed) {
                $derivedStatus = 'Completed';
            } elseif ($p->prj_estenddt && (string) $p->prj_estenddt < now()->toDateString()) {
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
                'employees' => (int) ($employeesByProject[(int) $p->prj_id] ?? 0),
            ];
        })->values();

        $codesByDivision = collect($tableProjects)
            ->groupBy('division')
            ->map(function ($rows) {
                return collect($rows)
                    ->map(fn ($p) => ['id' => $p['id'], 'code' => $p['code'], 'status' => $p['status']])
                    ->values();
            })
            ->toArray();

        $employeesPerProjectTop = collect($tableProjects)
            ->map(fn ($p) => ['code' => $p['code'], 'count' => (int) ($p['employees'] ?? 0)])
            ->sortByDesc('count')
            ->take(12)
            ->values();

        return response()->json([
            'divisions' => $divisions,
            'selectedProject' => $selectedProject,
            'kpis' => [
                'budgetReceived' => $budgetReceived,
                'utilized' => $utilized,
                'remaining' => $remaining,
                'creditTaken' => $creditTaken,
                'employeesTotal' => $employeesTotal,
                'employeesHired' => $employeesHired,
                'projectsTotal' => $projectsTotal,
                'reviewedCases' => $reviewedCases,
                'pendingCases' => $pendingCases,
            ],
            'charts' => [
                'finance' => [
                    'monthly' => $monthlySeries,
                    'quarterly' => $quarterlySeries,
                ],
                'casesPerDivision' => $casesPerDivision,
                'employeesPerDivision' => $employeesPerDivision,
                'projectsPerDivision' => $projectsPerDivision,
                'budgetVsUtilPerDivision' => $budgetVsUtilPerDivision,
                'projectStatus' => [
                    'completed' => $projectsCompleted,
                    'ongoing' => $projectsOngoing,
                    'delayed' => $projectsDelayed,
                ],
                'projectProgress' => $projectProgressTop,
                'employeesPerProject' => $employeesPerProjectTop,
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
            ->leftJoin('hr.emps as e', 'e.emp_hed_id', '=', 'h.hed_id')
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

        // Determine Mode
        $mode = $request->query('mode', 'm'); // 'm' for Module (All Data), 's' for Section (My Dept)

        // Set Ranges Based on Mode
        if ($mode === 's') {
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
            $varModeStr = 'approver-s';
        } else {
            $lower = $user->acc_lowerm;
            $upper = $user->acc_upperm;
            $varModeStr = 'approver-m';
        }

        // Determine Auth Status (approver, viewer, editor itc.)
        $userAuth = (string) ($user->acc_auth ?? 'viewer');

        // 1. Projects
        $projects = Project::whereBetween('prj_unt_id', [$lower, $upper])->get();
        $totalProjects = $projects->count();
        $totalProjectsBudget = $projects->sum('prj_aprvcost');

        // 2. Active Procurement Cases
        $activeCases = Purchase::whereBetween('pcs_unt_id', [$lower, $upper])
            ->whereIn('pcs_status', ['Draft', 'Under Scrutiny', 'In Progress'])
            ->count();
        $totalCases = Purchase::whereBetween('pcs_unt_id', [$lower, $upper])->count();
        $totalSpent = Purchase::whereBetween('pcs_unt_id', [$lower, $upper])->sum('pcs_price');

        // 3. Employee List & Headcount
        $employeesQuery = DB::table('cen.heads as h')
            ->join('hr.emps as e', 'e.emp_hed_id', '=', 'h.hed_id')
            ->join('cen.units as u', 'u.unt_id', '=', 'e.emp_unt_id')
            ->select('e.emp_name', 'e.emp_title as emp_joinrank', 'h.hed_name', 'u.unt_namesh as unit_name')
            ->whereBetween('h.hed_unt_id', [$lower, $upper]);

        // If Mode S (My Dept), filter out divisions explicitly as requested
        if ($mode === 's') {
            $employeesQuery->where('u.unt_type', '!=', 'Division');
        }

        // Ordered by name and paginate/limit so the UI doesn't crash if it's thousands
        $employeesList = $employeesQuery->orderBy('e.emp_name')->take(200)->get();
        $totalHeadCount = $employeesList->count(); // In bounded constraints

        return view('fin.dashboard', compact(
            'mode', 'lower', 'upper', 'varModeStr', 'userAuth',
            'totalProjects', 'totalProjectsBudget', 
            'activeCases', 'totalCases', 'totalSpent', 
            'totalHeadCount', 'employeesList'
        ));
    }
}
