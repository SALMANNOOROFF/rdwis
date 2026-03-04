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
        $unitId = $user ? $user->acc_unt_id : null;

        $q = Employee::query()
            ->leftJoin('cen.heads as h', 'hr.emps.emp_hed_id', '=', 'h.hed_id')
            ->select('hr.emps.*', 'h.hed_code');
        if ($unitId) {
            $q->where('emp_unt_id', $unitId);
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'Current') {
                $q->whereRaw('LOWER(emp_status) IN (?, ?)', ['active','current']);
            } elseif ($status === 'Previous') {
                $q->whereRaw('LOWER(emp_status) NOT IN (?, ?)', ['active','current']);
            }
        }

        if ($request->filled('term')) {
            $t = strtolower($request->term);
            $q->whereRaw('LOWER(emp_name) LIKE ? OR LOWER(emp_id) LIKE ?', ["%$t%", "%$t%"]);
        }

        $employees = $q->orderBy('emp_name', 'asc')->get();
        $activeCount = $employees->filter(function($e){
            return in_array(strtolower($e->emp_status), ['active','current']);
        })->count();
        $previousCount = $employees->count() - $activeCount;
        return view('divhr.employelist', compact('employees','activeCount','previousCount'));
    }

    // Employee detail page (ID from URL)
    public function employeedetail($id)
    {
        $emp = Employee::query()
            ->leftJoin('cen.heads as h', 'hr.emps.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'hr.emps.emp_unt_id', '=', 'u.unt_id')
            ->select('hr.emps.*', 'h.hed_code', 'u.unt_name')
            ->where('emp_id', $id)
            ->first();

        $base = DB::table('hr.salreqs as s')
            ->leftJoin('hr.emps as e', 'e.emp_id', '=', 's.srq_emp_id')
            ->leftJoin('cen.units as u', function ($join) {
                $join->on('u.unt_id', '=', DB::raw('COALESCE(s.srq_effunt_id, e.emp_unt_id)'));
            })
            ->leftJoin('cen.heads as eh', 'eh.hed_id', '=', 's.srq_effhed_id')
            ->leftJoin('prj.projects as p', 'p.prj_id', '=', 'eh.hed_prj_id')
            ->leftJoin('fin.salorders as o', 'o.sor_srq_id', '=', 's.srq_id')
            ->where('s.srq_emp_id', $id)
            ->orderBy('s.srq_month', 'desc')
            ->select(
                's.*',
                'o.sor_id',
                'o.sor_status',
                'o.sor_salary',
                'o.sor_grosalary',
                'o.sor_netsalary',
                'o.sor_effunt_id',
                'o.sor_effhed_id',
                'u.unt_name as eff_unit_name',
                'eh.hed_code as eff_hed_code',
                'eh.hed_name as eff_hed_name',
                'p.prj_title as eff_prj_title'
            )
            ->first();

        $subheads = [];
        if ($base && $base->sor_id) {
            $subheads = DB::table('fin.salorders_shd')
                ->where('sod_sor_id', $base->sor_id)
                ->select('sod_subhead', 'sod_ratio')
                ->orderBy('sod_subhead')
                ->get();
        }

        $monthRef = $base ? $base->srq_month : Carbon::now()->toDateString();
        $currentContract = DB::table('hr.contracts as c')
            ->leftJoin('cen.heads as ch', 'ch.hed_id', '=', 'c.ctr_hed_id')
            ->where('c.ctr_num', $id)
            ->whereRaw('? between c.ctr_startdt and c.ctr_enddt', [$monthRef])
            ->orderBy('c.ctr_enddt', 'desc')
            ->select('c.*', 'ch.hed_code as ctr_hed_code', 'ch.hed_name as ctr_hed_name')
            ->first();

        $contractsHistory = DB::table('hr.contracts as c')
            ->leftJoin('cen.heads as ch', 'ch.hed_id', '=', 'c.ctr_hed_id')
            ->where('c.ctr_num', $id)
            ->orderBy('c.ctr_startdt', 'asc')
            ->select('c.*', 'ch.hed_code as ctr_hed_code', 'ch.hed_name as ctr_hed_name')
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

        $salaryProgression = DB::table('fin.salorders')
            ->where('sor_emp_id', $id)
            ->selectRaw('EXTRACT(YEAR FROM sor_month)::int as yr, SUM(sor_netsalary)::bigint as total')
            ->groupBy('yr')
            ->orderBy('yr')
            ->get();

        $degrees = DB::table('hr.qualifs')
            ->where('qlf_emp_id', $id)
            ->where('qlf_type', 'Degree')
            ->orderBy('qlf_enddt', 'desc')
            ->get();

        $certs = DB::table('hr.qualifs')
            ->where('qlf_emp_id', $id)
            ->where('qlf_type', '<>', 'Degree')
            ->orderBy('qlf_enddt', 'desc')
            ->get();

        $vehicles = DB::table('hr.vehicles')
            ->where('vcl_emp_id', $id)
            ->orderBy('vcl_year', 'desc')
            ->get();

        $jobs = DB::table('hr.jobs')
            ->where('job_emp_id', $id)
            ->orderByRaw('COALESCE(job_to, CURRENT_DATE) DESC')
            ->get();

        $yearsInService = null;
        if ($emp && $emp->emp_joindt) {
            $yearsInService = round(Carbon::parse($emp->emp_joindt)->floatDiffInYears(Carbon::now()), 1);
        }

        return view('divhr.employee-details', compact(
            'id',
            'emp',
            'base',
            'subheads',
            'currentContract',
            'contractsHistory',
            'salaryProgression',
            'degrees',
            'certs',
            'vehicles',
            'jobs',
            'yearsInService'
        ));
    }
}
