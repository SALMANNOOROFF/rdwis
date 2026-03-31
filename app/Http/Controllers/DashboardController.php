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
}
