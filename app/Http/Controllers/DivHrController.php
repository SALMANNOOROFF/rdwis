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

        $empA = DB::table('hr.empsexta')->where('empexta_emp_id', $id)->first();
        $authUnit = null;
        if (Auth::user() && (Auth::user()->acc_unt_id ?? null)) {
            $authUnit = DB::table('cen.units')->where('unt_id', Auth::user()->acc_unt_id)->first();
        }

        return view('divhr.employee-details', compact(
            'id',
            'emp',
            'empA',
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
            'previousProjects',
            'degrees',
            'certs',
            'vehicles',
            'jobs',
            'yearsInService'
        ));
    }

    public function attendance(Request $request)
    {
        $monthStr = $request->input('month', Carbon::now()->format('Y-m'));
        $first = Carbon::parse($monthStr.'-01')->startOfMonth();
        $last = $first->copy()->endOfMonth();
        $days = (int) $first->daysInMonth;
        $unitId = Auth::user() ? (Auth::user()->acc_unt_id ?? (Auth::user()->unit->unt_id ?? null)) : null;
        // Pull all division employees and LEFT JOIN latest attendance row overlapping the month
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
            WHERE e.emp_unt_id = COALESCE(?::int, e.emp_unt_id)
            ORDER BY e.emp_name ASC
        ", [$last->toDateString(), $first->toDateString(), $unitId]));

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
}
