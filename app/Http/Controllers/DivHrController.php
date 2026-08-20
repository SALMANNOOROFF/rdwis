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
            $varModeStr = 'approver-s';
        } else {
            $lower = 0;
            $upper = 99999999;
            $varModeStr = 'approver-m';
        }
        $userAuth = (string) ($user->acc_auth ?? 'viewer');

        $q = Employee::query()
            ->leftJoin('cen.heads as h', 'hr.emps.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'hr.emps.emp_unt_id')
            ->select('hr.emps.*', 'h.hed_code');
        
        $q->whereBetween('emp_unt_id', [$lower, $upper]);

        // Check if the logged-in user unit is of type Division
        $userUnit = DB::table('cen.units')->where('unt_id', $user->acc_unt_id)->first();
        $isDivisionUser = $userUnit && $userUnit->unt_type === 'Division';

        // If Mode S (My Dept), filter out divisions explicitly, unless the logged-in user is a Division user itself
        if ($mode === 's' && !$isDivisionUser) {
            $q->where('u.unt_type', '!=', 'Division');
        }

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
        
        return view('divhr.employelist', compact('employees','activeCount','previousCount', 'mode', 'lower', 'upper', 'varModeStr', 'userAuth'));
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

        // Helper to fetch latest contracts for employees without date restrictions
        $getLatestContracts = function() use ($empIds) {
            return DB::table('hr.contracts as c')
                ->leftJoin('cen.heads as ch', 'ch.hed_id', '=', 'c.ctr_hed_id')
                ->whereIn('c.ctr_num', $empIds)
                ->select('c.*', 'ch.hed_code as ctr_hed_code')
                ->orderBy('c.ctr_enddt', 'desc')
                ->get()
                ->unique('ctr_num')
                ->keyBy('ctr_num');
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
                    ->whereIn('c.ctr_num', $empIds)
                    ->select('c.*', 'ch.hed_code as ctr_hed_code')
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
                            'head_code' => $c->ctr_hed_code ?? '—',
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
}
