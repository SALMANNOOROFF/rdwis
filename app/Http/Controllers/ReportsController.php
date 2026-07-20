<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function index() {
        return view('purchase.reports.index');
    }

    public function reportsCenter() {
        return view('reports.index');
    }

    public function hrIncompleteData() {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        $employees = DB::table('hr.emps as e')
            ->leftJoin('hr.empsexta as ea', 'e.emp_id', '=', 'ea.empexta_emp_id')
            ->leftJoin('hr.empsextb as eb', 'e.emp_id', '=', 'eb.empextb_emp_id')
            ->leftJoin('hr.empsextc as ec', 'e.emp_id', '=', 'ec.empextc_emp_id')
            ->leftJoin('cen.heads as h', 'e.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'e.emp_unt_id', '=', 'u.unt_id')
            ->whereRaw("LOWER(e.emp_status) IN ('active', 'current')")
            ->whereBetween('e.emp_unt_id', [$lower, $upper])
            ->select([
                'e.emp_id',
                'e.emp_name',
                'e.emp_cnic',
                'e.emp_joindt',
                'h.hed_name as department',
                'u.unt_name as unit',
                // exta fields
                'ea.emp_qualif',
                'ea.emp_discip',
                'ea.emp_father',
                'ea.emp_gender',
                'ea.emp_dob',
                'ea.emp_pob',
                'ea.emp_ntnlty',
                'ea.emp_marital',
                'ea.emp_email',
                'ea.emp_mobile',
                'ea.emp_taddress',
                'ea.emp_paddress',
                // extb fields
                'eb.emp_idmark',
                'eb.emp_height',
                'eb.emp_caste',
                'eb.emp_religion',
                'eb.emp_police',
                'eb.emp_political',
                'eb.emp_nokcnic',
                'eb.emp_nokname',
                'eb.emp_nokrelation',
                'eb.emp_emername',
                'eb.emp_emerrelation',
                'eb.emp_emermobile',
                // extc fields
                'ec.emp_cnum',
                'ec.emp_cissuedt',
                'ec.emp_cexpdt',
                'ec.emp_secclear',
            ])
            ->orderBy('h.hed_name')
            ->orderBy('e.emp_name')
            ->get();

        // Scope linked-table lookups to only these employees for performance
        $empIds = $employees->pluck('emp_id')->toArray();

        $hasQualifs  = DB::table('hr.qualifs')
            ->whereIn('qlf_emp_id', $empIds)
            ->distinct()->pluck('qlf_emp_id')->toArray();

        $hasVehicles = DB::table('hr.vehicles')
            ->whereIn('vcl_emp_id', $empIds)
            ->distinct()->pluck('vcl_emp_id')->toArray();

        $hasBanks = [];
        try {
            $hasBanks = DB::table('hr.bankaccounts')
                ->whereIn('bacc_emp_id', $empIds)
                ->distinct()->pluck('bacc_emp_id')->toArray();
        } catch (\Exception $e) {
            // Table might not exist
        }

        $employees = $employees->map(function ($emp) use ($hasQualifs, $hasVehicles, $hasBanks) {
            $emp->has_qualifs  = in_array($emp->emp_id, $hasQualifs);
            $emp->has_vehicles = in_array($emp->emp_id, $hasVehicles);
            $emp->has_banks    = in_array($emp->emp_id, $hasBanks);
            return $emp;
        });

        return view('reports.hr.incomplete_data', compact('employees'));
    }

    public function hrGrades() {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        // Get all active employees with their grade and head code
        // Join latest contract per employee to use ctr_grade as fallback when emp_rank is empty
        $rows = DB::table('hr.emps as e')
            ->leftJoin('cen.heads as h', 'e.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin(
                DB::raw("(SELECT DISTINCT ON (ctr_num) ctr_num, ctr_grade FROM hr.contracts ORDER BY ctr_num, ctr_enddt DESC) c"),
                'c.ctr_num', '=', 'e.emp_id'
            )
            ->whereRaw("LOWER(e.emp_status) IN ('active', 'current')")
            ->whereBetween('e.emp_unt_id', [$lower, $upper])
            ->select([
                DB::raw("COALESCE(NULLIF(TRIM(e.emp_rank), ''), NULLIF(TRIM(c.ctr_grade), ''), 'Unknown') as grade"),
                DB::raw("COALESCE(NULLIF(TRIM(h.hed_code), ''), 'N/A') as hed_code"),
                DB::raw('COUNT(*) as cnt'),
            ])
            ->groupBy('grade', 'hed_code')
            ->orderBy('grade')
            ->get();

        // All unique department head codes (columns) — sorted
        $headCodes = $rows->pluck('hed_code')->unique()->sort()->values();

        // All unique grades (rows) — custom sort order
        $gradeOrder = ['SRO','RO','SRT','RT','LA','Internee'];
        $grades = $rows->pluck('grade')->unique()->sortBy(function ($g) use ($gradeOrder) {
            $idx = array_search($g, $gradeOrder);
            return $idx === false ? 999 : $idx;
        })->values();

        // Build pivot: grade → [hed_code → count]
        $pivot = [];
        foreach ($rows as $r) {
            $pivot[$r->grade][$r->hed_code] = (int) $r->cnt;
        }

        // Column totals
        $colTotals = [];
        foreach ($headCodes as $hc) {
            $colTotals[$hc] = array_sum(array_column(array_map(fn($g) => [$hc => $pivot[$g][$hc] ?? 0], $grades->toArray()), $hc));
        }

        // Row totals
        $rowTotals = [];
        foreach ($grades as $g) {
            $rowTotals[$g] = array_sum($pivot[$g] ?? []);
        }

        $grandTotal = array_sum($rowTotals);

        return view('reports.hr.grades', compact(
            'grades', 'headCodes', 'pivot', 'rowTotals', 'colTotals', 'grandTotal'
        ));
    }

    public function hrQualifications() {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        $qualMapCase = "CASE TRIM(CAST(ea.emp_qualif AS TEXT))
            WHEN '80' THEN 'PhD'
            WHEN '75' THEN 'Masters'
            WHEN '70' THEN 'Masters'
            WHEN '60' THEN 'Bachelors'
            WHEN '50' THEN 'Associate (Diploma)'
            WHEN '35' THEN 'Certificate'
            WHEN '30' THEN 'Intermediate/A level'
            WHEN '28' THEN 'Middle'
            WHEN '25' THEN 'Primary'
            WHEN '20' THEN 'Matric/O level'
            ELSE COALESCE(NULLIF(TRIM(CAST(ea.emp_qualif AS TEXT)), ''), 'Unknown')
        END";

        // Get all active employees with their qualification and head code
        $rows = DB::table('hr.emps as e')
            ->leftJoin('cen.heads as h', 'e.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin('hr.empsexta as ea', 'e.emp_id', '=', 'ea.empexta_emp_id')
            ->whereRaw("LOWER(e.emp_status) IN ('active', 'current')")
            ->whereBetween('e.emp_unt_id', [$lower, $upper])
            ->select([
                DB::raw("($qualMapCase) as qualification"),
                DB::raw("COALESCE(NULLIF(TRIM(CAST(h.hed_code AS TEXT)), ''), 'N/A') as hed_code"),
                DB::raw('COUNT(*) as cnt'),
            ])
            ->groupBy(DB::raw($qualMapCase), 'hed_code')
            ->orderBy('qualification')
            ->get();

        // All unique department head codes (columns) — sorted
        $headCodes = $rows->pluck('hed_code')->unique()->sort()->values();

        // All unique qualifications (rows) — custom sort order (Masters, Bachelors, Associate (Diploma), Intermediate/A level, Matric/O level)
        $qualOrder = ['Masters', 'Bachelors', 'Associate (Diploma)', 'Intermediate/A level', 'Matric/O level'];
        $qualifications = $rows->pluck('qualification')->unique()->sortBy(function ($q) use ($qualOrder) {
            $idx = array_search($q, $qualOrder);
            return $idx === false ? 999 : $idx;
        })->values();

        // Build pivot: qualification → [hed_code → count]
        $pivot = [];
        foreach ($rows as $r) {
            $pivot[$r->qualification][$r->hed_code] = (int) $r->cnt;
        }

        // Column totals
        $colTotals = [];
        foreach ($headCodes as $hc) {
            $colTotals[$hc] = array_sum(array_column(array_map(fn($q) => [$hc => $pivot[$q][$hc] ?? 0], $qualifications->toArray()), $hc));
        }

        // Row totals
        $rowTotals = [];
        foreach ($qualifications as $q) {
            $rowTotals[$q] = array_sum($pivot[$q] ?? []);
        }

        $grandTotal = array_sum($rowTotals);

        return view('reports.hr.qualifications', compact(
            'qualifications', 'headCodes', 'pivot', 'rowTotals', 'colTotals', 'grandTotal'
        ));
    }

    public function hrCurrentEmployees() {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        $employees = DB::table('hr.emps as e')
            ->leftJoin('cen.units as u', 'e.emp_unt_id', '=', 'u.unt_id')
            ->leftJoin('cen.heads as h', 'e.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
            ->whereRaw("LOWER(e.emp_status) IN ('active', 'current')")
            ->whereBetween('e.emp_unt_id', [$lower, $upper])
            ->select(
                'e.emp_id',
                'e.emp_name',
                'e.emp_title as designation',
                'e.emp_rank as grade',
                'e.emp_joindt',
                'u.unt_name as division_name',
                'h.hed_code as department_code',
                DB::raw("COALESCE(p.prj_title, h.hed_code, '-') as project_name")
            )
            ->orderBy('u.unt_name')
            ->orderBy('e.emp_name')
            ->get();

        $empIds = $employees->pluck('emp_id')->toArray();

        $latestContracts = DB::table('hr.contracts as c')
            ->whereIn('c.ctr_num', $empIds)
            ->select('c.ctr_num', 'c.ctr_startdt', 'c.ctr_enddt', 'c.ctr_grade', 'c.ctr_jobtitle')
            ->orderBy('c.ctr_enddt', 'desc')
            ->get()
            ->groupBy('ctr_num')
            ->map(function($group) { return $group->first(); });

        $latestSalaries = DB::table('fin.salorders as s')
            ->whereIn('s.sor_emp_id', $empIds)
            ->select('s.sor_emp_id', 's.sor_grosalary')
            ->orderBy('s.sor_month', 'desc')
            ->get()
            ->groupBy('sor_emp_id')
            ->map(function($group) { return $group->first(); });

        $employees = $employees->map(function ($emp) use ($latestContracts, $latestSalaries) {
            $emp->contract = $latestContracts[$emp->emp_id] ?? null;
            $emp->salary = $latestSalaries[$emp->emp_id] ?? null;
            return $emp;
        });

        $groupedEmployees = $employees->groupBy(function($item) {
            return $item->division_name ?: 'Unknown Division';
        });

        return view('reports.hr.current_employees', compact('groupedEmployees'));
    }

    public function finPcsAwaitingPayment()
    {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        // Awaiting Ps commitments joined with purchase cases and firm
        $rows = DB::select("
            SELECT
                pc.pcs_id                           AS pc_id,
                pc.pcs_date                         AS pc_date,
                pc.pcs_minute                       AS minute,
                pc.pcs_title                        AS title,
                COALESCE(q.qte_firmname, '-')       AS firm,
                ABS(c.cmt_amount)                   AS cost,
                ABS(COALESCE(paid.total_paid, 0))   AS paid,
                ABS(c.cmt_amount) - ABS(COALESCE(paid.total_paid, 0)) AS balance,
                h.hed_code                          AS head
            FROM fin.commitments c
            LEFT JOIN cen.heads h ON h.hed_id = c.cmt_effhed_id
            LEFT JOIN pur.purcases pc ON pc.pcs_id = c.cmt_docid
            LEFT JOIN (
                SELECT qte_pcs_id, qte_firmname
                FROM pur.quotes
                WHERE qte_recomm = true
                LIMIT 1
            ) q ON q.qte_pcs_id = pc.pcs_id
            LEFT JOIN (
                SELECT trn_cmt_id, SUM(trn_amount1) AS total_paid
                FROM fin.transactions
                GROUP BY trn_cmt_id
            ) paid ON paid.trn_cmt_id = c.cmt_id
            WHERE c.cmt_type = 'Ps'
              AND c.cmt_status = 'Awaited'
              AND c.cmt_effunt_id BETWEEN :lower AND :upper
            ORDER BY h.hed_code, pc.pcs_date
        ", ['lower' => $lower, 'upper' => $upper]);

        // Group by head
        $grouped = collect($rows)->groupBy('head');

        return view('reports.finance.pcs_awaiting_payment', compact('grouped'));
    }

    public function finCsrfStatus()
    {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        $rows = DB::select("
            SELECT
                h.hed_code                                          AS head,
                sha.sha_cf                                          AS allocation,
                ABS(COALESCE(recv.received, 0))                     AS received,
                ABS(COALESCE(exp.expenditure, 0))                   AS expenditure,
                ABS(COALESCE(comm.commitments, 0))                  AS commitments,
                ABS(COALESCE(inp.in_process, 0))                    AS in_process,
                sha.sha_cf
                    - ABS(COALESCE(exp.expenditure, 0))
                    - ABS(COALESCE(comm.commitments, 0))            AS available,
                sha.sha_cf
                    - ABS(COALESCE(exp.expenditure, 0))             AS remaining
            FROM cen.heads h
            JOIN fin.sharesalloc sha ON sha.sha_hed_id = h.hed_id
            JOIN fin.commitments ci  ON sha.sha_ficmt_id = ci.cmt_id
            -- CSRF Received (FI type)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(t.trn_amount1) AS received
                FROM fin.commitments c
                JOIN fin.transactions t ON t.trn_cmt_id = c.cmt_id
                WHERE c.cmt_type = 'FI'
                GROUP BY c.cmt_effhed_id
            ) recv ON recv.cmt_effhed_id = h.hed_id
            -- CSRF Expenditure (Ps paid, charged to CSRF)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(t.trn_amount1) AS expenditure
                FROM fin.commitments c
                JOIN fin.transactions t ON t.trn_cmt_id = c.cmt_id
                WHERE c.cmt_type = 'Ps' AND c.cmt_status = 'Paid'
                  AND c.cmt_hed_id IN (
                      SELECT sha2.sha_focmt_id FROM fin.sharesalloc sha2
                  )
                GROUP BY c.cmt_effhed_id
            ) exp ON exp.cmt_effhed_id = h.hed_id
            -- CSRF Commitments (Ps awaited)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(c.cmt_amount) AS commitments
                FROM fin.commitments c
                WHERE c.cmt_type = 'Ps' AND c.cmt_status = 'Awaited'
                GROUP BY c.cmt_effhed_id
            ) comm ON comm.cmt_effhed_id = h.hed_id
            -- In Process
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(c.cmt_amount) AS in_process
                FROM fin.commitments c
                WHERE c.cmt_type = 'Pt'
                GROUP BY c.cmt_effhed_id
            ) inp ON inp.cmt_effhed_id = h.hed_id
            WHERE sha.sha_cf > 0
              AND ci.cmt_effunt_id BETWEEN :lower AND :upper
            ORDER BY h.hed_code
        ", ['lower' => $lower, 'upper' => $upper]);

        // Totals
        $total = [
            'allocation'   => collect($rows)->sum('allocation'),
            'received'     => collect($rows)->sum('received'),
            'expenditure'  => collect($rows)->sum('expenditure'),
            'commitments'  => collect($rows)->sum('commitments'),
            'in_process'   => collect($rows)->sum('in_process'),
            'available'    => collect($rows)->sum('available'),
            'remaining'    => collect($rows)->sum('remaining'),
        ];

        return view('reports.finance.csrf_status', compact('rows', 'total'));
    }

    public function finSubheadsStatus()
    {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        $rows = DB::select("
            SELECT
                h.hed_code                          AS head,
                sbh.sbh_name                        AS subhead,
                sbh.sbh_alloc                       AS allocation,
                ABS(COALESCE(exp.expenditure, 0))   AS expenditure,
                ABS(COALESCE(comm.commitments, 0))  AS commitments,
                ABS(COALESCE(inp.in_process, 0))    AS in_process,
                sbh.sbh_alloc
                    - ABS(COALESCE(exp.expenditure, 0))
                    - ABS(COALESCE(comm.commitments, 0)) AS remaining
            FROM fin.subheads sbh
            JOIN cen.heads h ON h.hed_id = sbh.sbh_hed_id
            JOIN fin.sharesalloc sha ON sha.sha_hed_id = h.hed_id
            JOIN fin.commitments ci ON sha.sha_ficmt_id = ci.cmt_id
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(t.trn_amount1) AS expenditure
                FROM fin.commitments c
                JOIN fin.transactions t ON t.trn_cmt_id = c.cmt_id
                WHERE c.cmt_type = 'Ps' AND c.cmt_status = 'Paid'
                GROUP BY c.cmt_effhed_id
            ) exp ON exp.cmt_effhed_id = h.hed_id
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(c.cmt_amount) AS commitments
                FROM fin.commitments c
                WHERE c.cmt_type = 'Ps' AND c.cmt_status = 'Awaited'
                GROUP BY c.cmt_effhed_id
            ) comm ON comm.cmt_effhed_id = h.hed_id
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(c.cmt_amount) AS in_process
                FROM fin.commitments c
                WHERE c.cmt_type = 'Pt'
                GROUP BY c.cmt_effhed_id
            ) inp ON inp.cmt_effhed_id = h.hed_id
            WHERE ci.cmt_effunt_id BETWEEN :lower AND :upper
            ORDER BY h.hed_code, sbh.sbh_name
        ", ['lower' => $lower, 'upper' => $upper]);

        // Group by head, then subhead
        $grouped = collect($rows)->groupBy('head');

        $total = [
            'allocation'  => collect($rows)->sum('allocation'),
            'expenditure' => collect($rows)->sum('expenditure'),
            'commitments' => collect($rows)->sum('commitments'),
            'in_process'  => collect($rows)->sum('in_process'),
            'remaining'   => collect($rows)->sum('remaining'),
        ];

        return view('reports.finance.subheads_status', compact('grouped', 'total'));
    }

    public function finProjSharesStatus()
    {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        $rows = DB::select("
            SELECT
                h.hed_code                                              AS head,
                sha.sha_pcc                                             AS project_share,
                ABS(COALESCE(recv.received, 0))                         AS received,
                ABS(COALESCE(exp.expenditure, 0))                       AS expenditure,
                ABS(COALESCE(inp.in_process, 0))                        AS in_process,
                ABS(COALESCE(comm.commitments, 0))                      AS commitments,
                sha.sha_pcc
                    - ABS(COALESCE(exp.expenditure, 0))
                    - ABS(COALESCE(comm.commitments, 0))                AS available,
                COALESCE(ms.milestone_total, 0)                         AS completed_milestones,
                sha.sha_pcc
                    - ABS(COALESCE(exp.expenditure, 0))
                    - ABS(COALESCE(comm.commitments, 0))
                    + COALESCE(ms.milestone_total, 0)                   AS available_after_milestones,
                sha.sha_pcc
                    - ABS(COALESCE(exp.expenditure, 0))                 AS remaining
            FROM cen.heads h
            JOIN fin.sharesalloc sha ON sha.sha_hed_id = h.hed_id
            JOIN fin.commitments ci  ON sha.sha_ficmt_id = ci.cmt_id
            -- Received (FI transactions)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(t.trn_amount1) AS received
                FROM fin.commitments c
                JOIN fin.transactions t ON t.trn_cmt_id = c.cmt_id
                WHERE c.cmt_type = 'FI'
                GROUP BY c.cmt_effhed_id
            ) recv ON recv.cmt_effhed_id = h.hed_id
            -- Expenditure (Ps paid)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(t.trn_amount1) AS expenditure
                FROM fin.commitments c
                JOIN fin.transactions t ON t.trn_cmt_id = c.cmt_id
                WHERE c.cmt_type = 'Ps' AND c.cmt_status = 'Paid'
                GROUP BY c.cmt_effhed_id
            ) exp ON exp.cmt_effhed_id = h.hed_id
            -- Commitments (Ps awaited)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(c.cmt_amount) AS commitments
                FROM fin.commitments c
                WHERE c.cmt_type = 'Ps' AND c.cmt_status = 'Awaited'
                GROUP BY c.cmt_effhed_id
            ) comm ON comm.cmt_effhed_id = h.hed_id
            -- In Process (Pt)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(c.cmt_amount) AS in_process
                FROM fin.commitments c
                WHERE c.cmt_type = 'Pt'
                GROUP BY c.cmt_effhed_id
            ) inp ON inp.cmt_effhed_id = h.hed_id
            -- Milestones (sharesinstall)
            LEFT JOIN (
                SELECT shi_hed_id, SUM(COALESCE(shi_pcc, 0)) AS milestone_total
                FROM fin.sharesinstall
                WHERE shi_msn_idd IS NOT NULL
                GROUP BY shi_hed_id
            ) ms ON ms.shi_hed_id = h.hed_id
            WHERE sha.sha_pcc > 0
              AND ci.cmt_effunt_id BETWEEN :lower AND :upper
            ORDER BY h.hed_code
        ", ['lower' => $lower, 'upper' => $upper]);

        $total = [
            'project_share'              => collect($rows)->sum('project_share'),
            'received'                   => collect($rows)->sum('received'),
            'expenditure'                => collect($rows)->sum('expenditure'),
            'in_process'                 => collect($rows)->sum('in_process'),
            'commitments'                => collect($rows)->sum('commitments'),
            'available'                  => collect($rows)->sum('available'),
            'completed_milestones'       => collect($rows)->sum('completed_milestones'),
            'available_after_milestones' => collect($rows)->sum('available_after_milestones'),
            'remaining'                  => collect($rows)->sum('remaining'),
        ];

        return view('reports.finance.proj_shares_status', compact('rows', 'total'));
    }

    public function finAccountStatus()
    {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        $rows = DB::select("
            SELECT
                h.hed_code                                              AS head,
                ci.cmt_amount                                           AS allocation,
                (-co.cmt_amount)                                        AS mtss_share,
                (ci.cmt_amount + co.cmt_amount)                         AS rdw_share,
                ABS(COALESCE(recv.received, 0))                         AS received,
                ABS(COALESCE(exp.expenditure, 0))                       AS expenditure,
                ABS(COALESCE(inp.in_process, 0))                        AS in_process,
                ABS(COALESCE(comm.commitments, 0))                      AS commitments,
                (ci.cmt_amount + co.cmt_amount)
                    - ABS(COALESCE(exp.expenditure, 0))
                    - ABS(COALESCE(comm.commitments, 0))                AS available,
                (ci.cmt_amount + co.cmt_amount)
                    - ABS(COALESCE(exp.expenditure, 0))                 AS remaining
            FROM cen.heads h
            JOIN fin.sharesalloc sha ON sha.sha_hed_id = h.hed_id
            JOIN fin.commitments ci  ON sha.sha_ficmt_id = ci.cmt_id
            JOIN fin.commitments co  ON sha.sha_focmt_id = co.cmt_id
            -- Received (FI transactions)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(t.trn_amount1) AS received
                FROM fin.commitments c
                JOIN fin.transactions t ON t.trn_cmt_id = c.cmt_id
                WHERE c.cmt_type = 'FI'
                GROUP BY c.cmt_effhed_id
            ) recv ON recv.cmt_effhed_id = h.hed_id
            -- Expenditure (Ps paid)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(t.trn_amount1) AS expenditure
                FROM fin.commitments c
                JOIN fin.transactions t ON t.trn_cmt_id = c.cmt_id
                WHERE c.cmt_type = 'Ps' AND c.cmt_status = 'Paid'
                GROUP BY c.cmt_effhed_id
            ) exp ON exp.cmt_effhed_id = h.hed_id
            -- In Process (Pt)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(c.cmt_amount) AS in_process
                FROM fin.commitments c
                WHERE c.cmt_type = 'Pt'
                GROUP BY c.cmt_effhed_id
            ) inp ON inp.cmt_effhed_id = h.hed_id
            -- Commitments (Ps awaited)
            LEFT JOIN (
                SELECT c.cmt_effhed_id, SUM(c.cmt_amount) AS commitments
                FROM fin.commitments c
                WHERE c.cmt_type = 'Ps' AND c.cmt_status = 'Awaited'
                GROUP BY c.cmt_effhed_id
            ) comm ON comm.cmt_effhed_id = h.hed_id
            WHERE sha.sha_id IS NOT NULL
              AND ci.cmt_effunt_id BETWEEN :lower AND :upper
            ORDER BY h.hed_code
        ", ['lower' => $lower, 'upper' => $upper]);

        $total = [
            'allocation'  => collect($rows)->sum('allocation'),
            'mtss_share'  => collect($rows)->sum('mtss_share'),
            'rdw_share'   => collect($rows)->sum('rdw_share'),
            'received'    => collect($rows)->sum('received'),
            'expenditure' => collect($rows)->sum('expenditure'),
            'in_process'  => collect($rows)->sum('in_process'),
            'commitments' => collect($rows)->sum('commitments'),
            'available'   => collect($rows)->sum('available'),
            'remaining'   => collect($rows)->sum('remaining'),
        ];

        return view('reports.finance.account_status', compact('rows', 'total'));
    }

    public function finAllocationStatus()
    {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        // ── Project-level rows ─────────────────────────────────────────────
        $projectRows = DB::select("
            SELECT
                h.hed_id,
                h.hed_code                                   AS head,
                h.hed_unt_id                                 AS unt_id,
                ci.cmt_amount                                AS allocation,
                (-co.cmt_amount)                             AS mtss_share,
                (ci.cmt_amount + co.cmt_amount)              AS rdw_share,
                sha.sha_cf                                   AS csrf_share,
                sha.sha_pcc                                  AS project_share,
                sha.sha_prj                                  AS max_limit,
                sha.sha_prj_sal                              AS hr_alloc,
                sha.sha_prj_pur                              AS eqp_alloc,
                COALESCE(sbh_eqp.eqp_total, 0)              AS eqp_sbh,
                COALESCE(sbh_hr.hr_total,  0)                AS hr_sbh,
                COALESCE(sbh_misc.misc_total, 0)             AS misc_sbh
            FROM cen.heads h
            LEFT JOIN fin.sharesalloc sha  ON sha.sha_hed_id  = h.hed_id
            LEFT JOIN fin.commitments ci   ON sha.sha_ficmt_id = ci.cmt_id
            LEFT JOIN fin.commitments co   ON sha.sha_focmt_id = co.cmt_id
            -- subheads pivot
            LEFT JOIN (
                SELECT sbh_hed_id, SUM(sbh_alloc) AS eqp_total
                FROM fin.subheads WHERE sbh_name = 'Equipment' GROUP BY sbh_hed_id
            ) sbh_eqp  ON sbh_eqp.sbh_hed_id  = h.hed_id
            LEFT JOIN (
                SELECT sbh_hed_id, SUM(sbh_alloc) AS hr_total
                FROM fin.subheads WHERE sbh_name = 'HR' GROUP BY sbh_hed_id
            ) sbh_hr   ON sbh_hr.sbh_hed_id   = h.hed_id
            LEFT JOIN (
                SELECT sbh_hed_id, SUM(sbh_alloc) AS misc_total
                FROM fin.subheads WHERE sbh_name = 'Misc' GROUP BY sbh_hed_id
            ) sbh_misc ON sbh_misc.sbh_hed_id = h.hed_id
            WHERE sha.sha_id IS NOT NULL
              AND h.hed_unt_id BETWEEN :lower AND :upper
            ORDER BY h.hed_unt_id, h.hed_code
        ", ['lower' => $lower, 'upper' => $upper]);

        // ── Group by unit ───────────────────────────────────────────────────
        $units = DB::table('cen.units as u')
            ->whereBetween('u.unt_id', [$lower, $upper])
            ->orderBy('u.unt_id')
            ->select('u.unt_id', 'u.unt_name', 'u.unt_namesh')
            ->get()
            ->keyBy('unt_id');

        // Map rows by unt_id
        $grouped = collect($projectRows)->groupBy('unt_id');

        // Build unit summaries
        $unitSummaries = [];
        foreach ($grouped as $untId => $rows) {
            $unitSummaries[$untId] = [
                'allocation'    => $rows->sum('allocation'),
                'mtss_share'    => $rows->sum('mtss_share'),
                'rdw_share'     => $rows->sum('rdw_share'),
                'csrf_share'    => $rows->sum('csrf_share'),
                'project_share' => $rows->sum('project_share'),
                'max_limit'     => $rows->sum('max_limit'),
                'eqp_sbh'       => $rows->sum('eqp_sbh'),
                'hr_sbh'        => $rows->sum('hr_sbh'),
                'misc_sbh'      => $rows->sum('misc_sbh'),
            ];
        }

        return view('reports.finance.allocation_status', compact(
            'grouped', 'units', 'unitSummaries'
        ));
    }

    public function hrExServicemen() {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        $employees = DB::table('hr.emps as e')
            ->leftJoin('cen.heads as h', 'e.emp_hed_id', '=', 'h.hed_id')
            ->leftJoin('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
            ->whereRaw("LOWER(e.emp_status) IN ('active', 'current')")
            ->where(function($q) {
                $q->where('e.emp_name', 'ILIKE', '%(Retd)%')
                  ->orWhere('e.emp_title', 'ILIKE', '%(Retd)%');
            })
            ->whereBetween('e.emp_unt_id', [$lower, $upper])
            ->select(
                'e.emp_id',
                'e.emp_name',
                'e.emp_title as designation',
                'e.emp_joindt',
                'h.hed_code as department_code',
                DB::raw("COALESCE(p.prj_title, h.hed_code, '-') as project_name")
            )
            ->orderBy('e.emp_name')
            ->get();

        return view('reports.hr.ex_servicemen', compact('employees'));
    }

    public function hrSalariesPaid(Request $request) {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        // Get available months for this unit range (for the month selector)
        $availableMonths = DB::table('fin.salorders')
            ->whereBetween('sor_unt_id', [$lower, $upper])
            ->selectRaw("TO_CHAR(sor_month, 'YYYY-MM') as mon")
            ->distinct()
            ->orderByRaw("mon DESC")
            ->limit(24)
            ->pluck('mon');

        // Selected month — default to latest available
        $selectedMonth = $request->input('month', $availableMonths->first());

        $salaries = DB::table('fin.salorders as s')
            ->leftJoin('cen.heads as h', 'h.hed_id', '=', 's.sor_effhed_id')
            ->leftJoin('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 's.sor_effunt_id')
            ->leftJoin('cen.heads as uh', 'uh.hed_id', '=', DB::raw('s.sor_hed_id'))
            ->whereBetween('s.sor_unt_id', [$lower, $upper])
            ->whereRaw("TO_CHAR(s.sor_month, 'YYYY-MM') = ?", [$selectedMonth])
            ->select(
                's.sor_emp_id',
                's.sor_empnamecomp as emp_name',
                's.sor_grosalary as salary',
                's.sor_month',
                'u.unt_name as division_name',
                DB::raw("COALESCE(NULLIF(TRIM(uh.hed_code),''), 'N/A') as dept_code"),
                DB::raw("COALESCE(NULLIF(TRIM(h.hed_code),''), 'N/A') as project_code"),
                DB::raw("COALESCE(NULLIF(TRIM(h.hed_code),''), 'N/A') as paid_from")
            )
            ->orderBy('u.unt_name')
            ->orderBy('s.sor_empnamecomp')
            ->get();

        $grouped = $salaries->groupBy(function($item) {
            return $item->division_name ?: 'Unknown Division';
        });

        $grandTotal = $salaries->sum('salary');

        return view('reports.hr.salaries_paid', compact(
            'grouped', 'grandTotal', 'availableMonths', 'selectedMonth'
        ));
    }

    public function hrCustom(Request $request) {
        $user  = auth()->user();
        $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
        $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;

        // All available fields definition
        $allFields = [
            // Basic
            'emp_id'          => ['label' => 'Employee ID',       'col' => 'e.emp_id',          'group' => 'Basic'],
            'emp_cnic'        => ['label' => 'CNIC',              'col' => 'e.emp_cnic',         'group' => 'Basic'],
            'emp_name'        => ['label' => 'Name',              'col' => 'e.emp_name',         'group' => 'Basic'],
            'emp_namecomp'    => ['label' => 'Name Full',         'col' => 'e.emp_name',         'group' => 'Basic'],
            'emp_rank'        => ['label' => 'Ex Rank',           'col' => 'e.emp_rank',         'group' => 'Basic'],
            'emp_title'       => ['label' => 'Title',             'col' => 'e.emp_title',        'group' => 'Basic'],
            'hed_name'        => ['label' => 'Department Name',   'col' => 'h.hed_name',         'group' => 'Basic'],
            'hed_code'        => ['label' => 'Department',        'col' => 'h.hed_code',         'group' => 'Basic'],
            'emp_joindt'      => ['label' => 'Joining Date',      'col' => 'e.emp_joindt',       'group' => 'Basic'],
            'emp_lastdt'      => ['label' => 'Last Date',         'col' => 'e.emp_lastdt',       'group' => 'Basic'],
            'emp_status'      => ['label' => 'Status',            'col' => 'e.emp_status',       'group' => 'Basic'],
            'emp_remarks'     => ['label' => 'Remarks',           'col' => 'e.emp_remarks',      'group' => 'Basic'],
            // Official
            'emp_cissuedt'    => ['label' => 'Entry Card Issue Date',   'col' => 'ec.emp_cissuedt',  'group' => 'Official'],
            'emp_cexpdt'      => ['label' => 'Entry Card Expiry Date',  'col' => 'ec.emp_cexpdt',    'group' => 'Official'],
            'emp_cnum'        => ['label' => 'Entry Card Number',       'col' => 'ec.emp_cnum',      'group' => 'Official'],
            'emp_secclear'    => ['label' => 'Security Clearance',      'col' => 'ec.emp_secclear',  'group' => 'Official'],
            // Contract
            'ctr_date'        => ['label' => 'Contract Signing Date',    'col' => 'c.ctr_date',      'group' => 'Contract'],
            'ctr_type'        => ['label' => 'Contract Type',            'col' => 'c.ctr_type',      'group' => 'Contract'],
            'ctr_startdt'     => ['label' => 'Contract Start Date',      'col' => 'c.ctr_startdt',   'group' => 'Contract'],
            'ctr_enddt'       => ['label' => 'Contract Effective End',   'col' => 'c.ctr_enddt',     'group' => 'Contract'],
            'ctr_period'      => ['label' => 'Contract Period (Months)', 'col' => "ROUND(EXTRACT(EPOCH FROM (c.ctr_enddt - c.ctr_startdt)) / 2592000)::int", 'group' => 'Contract', 'raw' => true],
            'ctr_grade'       => ['label' => 'Grade',                    'col' => 'c.ctr_grade',     'group' => 'Contract'],
            'ctr_grade_full'  => ['label' => 'Grade Full',               'col' => 'c.ctr_grade',     'group' => 'Contract'],
            'ctr_jobtitle'    => ['label' => 'Job Title',                'col' => 'c.ctr_jobtitle',  'group' => 'Contract'],
            'ctr_salary'      => ['label' => 'Salary',                   'col' => 'c.ctr_salary',    'group' => 'Contract'],
            'ctr_prob'        => ['label' => 'Probation Period (Months)','col' => 'c.ctr_prob',      'group' => 'Contract'],
            'ctr_probsal'     => ['label' => 'Probation Salary',         'col' => 'c.ctr_probsal',   'group' => 'Contract'],
            // Qualification
            'emp_qualif'      => ['label' => 'Qualification',       'col' => 'ea.emp_qualif',    'group' => 'Qualification'],
            'emp_qualgrade'   => ['label' => 'Qualification Grade', 'col' => 'ea.emp_qualif',    'group' => 'Qualification'],
            'emp_discip'      => ['label' => 'Discipline',          'col' => 'ea.emp_discip',    'group' => 'Qualification'],
            'emp_spec'        => ['label' => 'Speciality',          'col' => 'ea.emp_spec',      'group' => 'Qualification'],
            // Personal
            'emp_gender'      => ['label' => 'Gender',                   'col' => 'ea.emp_gender',     'group' => 'Personal'],
            'emp_ntnlty'      => ['label' => 'Nationality',              'col' => 'ea.emp_ntnlty',     'group' => 'Personal'],
            'emp_marital'     => ['label' => 'Marital Status',           'col' => 'ea.emp_marital',    'group' => 'Personal'],
            'emp_caste'       => ['label' => 'Caste',                    'col' => 'eb.emp_caste',      'group' => 'Personal'],
            'emp_height'      => ['label' => 'Height',                   'col' => 'eb.emp_height',     'group' => 'Personal'],
            'emp_idmark'      => ['label' => 'ID Mark',                  'col' => 'eb.emp_idmark',     'group' => 'Personal'],
            'emp_religion'    => ['label' => 'Religion',                 'col' => 'eb.emp_religion',   'group' => 'Personal'],
            'emp_sect'        => ['label' => 'Sect',                     'col' => 'eb.emp_sect',       'group' => 'Personal'],
            'emp_political'   => ['label' => 'Political Affiliation',    'col' => 'eb.emp_political',  'group' => 'Personal'],
            'emp_dob'         => ['label' => 'Date of Birth',            'col' => 'ea.emp_dob',        'group' => 'Personal'],
            'emp_pob'         => ['label' => 'Place of Birth',           'col' => 'ea.emp_pob',        'group' => 'Personal'],
            'emp_landline'    => ['label' => 'Landline',                 'col' => 'ea.emp_landline',   'group' => 'Personal'],
            'emp_mobile'      => ['label' => 'Mobile',                   'col' => 'ea.emp_mobile',     'group' => 'Personal'],
            'emp_mobile2'     => ['label' => 'Mobile 2',                 'col' => 'ea.emp_mobile2',    'group' => 'Personal'],
            'emp_email'       => ['label' => 'Email',                    'col' => 'ea.emp_email',      'group' => 'Personal'],
            'emp_taddress'    => ['label' => 'Temporary Address',        'col' => 'ea.emp_taddress',   'group' => 'Personal'],
            'emp_paddress'    => ['label' => 'Permanent Address',        'col' => 'ea.emp_paddress',   'group' => 'Personal'],
            'emp_police'      => ['label' => 'Police Station',           'col' => 'eb.emp_police',     'group' => 'Personal'],
            'emp_emername'    => ['label' => 'Emergency Contact',        'col' => 'eb.emp_emername',   'group' => 'Personal'],
            'emp_emermobile'  => ['label' => 'Emergency Contact Mobile', 'col' => 'eb.emp_emermobile', 'group' => 'Personal'],
            'emp_emerrelation'=> ['label' => 'Emergency Contact Relation','col' => 'eb.emp_emerrelation','group' => 'Personal'],
            'emp_father'      => ['label' => 'Father Name',              'col' => 'ea.emp_father',     'group' => 'Personal'],
            'emp_father_cnic' => ['label' => 'Father CNIC',             'col' => 'ea.emp_father_cnic','group' => 'Personal'],
            'emp_ntnlty_other'=> ['label' => 'Father Nationality Other', 'col' => 'ea.emp_ntnlty_other','group' => 'Personal'],
            'emp_nokcnic'     => ['label' => 'Next of Kin CNIC',         'col' => 'eb.emp_nokcnic',    'group' => 'Personal'],
            'emp_nokname'     => ['label' => 'Next of Kin Name',         'col' => 'eb.emp_nokname',    'group' => 'Personal'],
            'emp_nokrelation' => ['label' => 'Next of Kin Relation',     'col' => 'eb.emp_nokrelation','group' => 'Personal'],
            // Bank
            'bac_bnkname'     => ['label' => 'Bank Name',   'col' => 'ba.bac_bnkname',  'group' => 'Bank'],
            'bac_bchname'     => ['label' => 'Branch Name', 'col' => 'ba.bac_bchname',  'group' => 'Bank'],
            'bac_bchcode'     => ['label' => 'Branch Code', 'col' => 'ba.bac_bchcode',  'group' => 'Bank'],
            'bac_bchcity'     => ['label' => 'Branch City', 'col' => 'ba.bac_bchcity',  'group' => 'Bank'],
            'bac_accnum'      => ['label' => 'Account Number','col' => 'ba.bac_accnum', 'group' => 'Bank'],
            'bac_acctitle'    => ['label' => 'Account Title', 'col' => 'ba.bac_acctitle','group' => 'Bank'],
        ];

        $selectedFields = $request->input('fields', []);
        $statusFilter   = $request->input('status', 'current');
        $results        = collect();
        $submitted      = $request->has('fields');

        if ($submitted && count($selectedFields) > 0) {
            // Build select columns
            $selects = ['e.emp_id as _emp_id']; // always include emp_id for row key
            foreach ($selectedFields as $key) {
                if (!isset($allFields[$key])) continue;
                $f = $allFields[$key];
                if (!empty($f['raw'])) {
                    $selects[] = DB::raw("({$f['col']}) as {$key}");
                } else {
                    $selects[] = DB::raw("{$f['col']} as {$key}");
                }
            }

            $query = DB::table('hr.emps as e')
                ->leftJoin('cen.heads as h',         'e.emp_hed_id',       '=', 'h.hed_id')
                ->leftJoin('hr.empsexta as ea',      'e.emp_id',           '=', 'ea.empexta_emp_id')
                ->leftJoin('hr.empsextb as eb',      'e.emp_id',           '=', 'eb.empextb_emp_id')
                ->leftJoin('hr.empsextc as ec',      'e.emp_id',           '=', 'ec.empextc_emp_id')
                ->leftJoin('hr.bnkaccounts as ba',   'e.emp_id',           '=', 'ba.bac_emp_id')
                ->leftJoin(DB::raw("(SELECT DISTINCT ON (ctr_num) * FROM hr.contracts ORDER BY ctr_num, ctr_enddt DESC) c"), 'e.emp_id', '=', 'c.ctr_num')
                ->whereBetween('e.emp_unt_id', [$lower, $upper]);

            // Status filter
            if ($statusFilter === 'current') {
                $query->whereRaw("LOWER(e.emp_status) IN ('active','current')");
            } elseif ($statusFilter === 'previous') {
                $query->whereRaw("LOWER(e.emp_status) NOT IN ('active','current')");
            }

            $results = $query->select($selects)->orderBy('e.emp_name')->get();
        }

        return view('reports.hr.custom', compact('allFields', 'selectedFields', 'statusFilter', 'results', 'submitted'));
    }

    public function generateComparative(Request $request) {
        return view('purchase.reports.comparative_pdf', $request->all());
    }

    public function generateITLetter(Request $request) {
        return view('purchase.reports.it_letter_pdf', $request->all());
    }

    public function invSharedAssets()
    {
        $assets = DB::select("
            SELECT
                ias.ias_desc AS description,
                iac.iac_qty AS qty,
                u.unt_namesh AS division,
                iac.iac_location AS location
            FROM ina.invatcomps iac
            JOIN ina.invats ias ON iac.iac_ias_id = ias.ias_id
            LEFT JOIN cen.units u ON u.unt_id = ias.ias_unt_id
            WHERE iac.iac_shared = true
            ORDER BY ias.ias_desc
        ");

        return view('reports.inventory.shared_assets', compact('assets'));
    }
}