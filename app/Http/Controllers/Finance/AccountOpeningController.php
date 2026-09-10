<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountOpeningController extends Controller
{
    /**
     * Display Open / Closed Project Financial Accounts (cen_heads_pa_u).
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'open'); // 'open' or 'closed'
        if (!in_array($status, ['open', 'closed'])) {
            $status = 'open';
        }
        $division = $request->query('division');
        $search = trim((string) $request->query('search', ''));

        // Counts for tabs
        $openCount = DB::table('cen.heads')
            ->where('hed_type', 'Project')
            ->whereNull('hed_closedt')
            ->count();

        $closedCount = DB::table('cen.heads')
            ->where('hed_type', 'Project')
            ->whereNotNull('hed_closedt')
            ->count();

        // Query heads joined with projects, units, shares, and initial funding transfers
        $query = DB::table('cen.heads as h')
            ->leftJoin('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'h.hed_unt_id')
            ->leftJoin('fin.sharesalloc as sa', 'sa.sha_hed_id', '=', 'h.hed_id')
            ->leftJoin('fin.transfers as t', function ($join) {
                $join->on('t.trf_tohed', '=', 'h.hed_id')
                     ->where('t.trf_type', '=', 'FI');
            })
            ->where('h.hed_type', 'Project');

        if ($status === 'closed') {
            $query->whereNotNull('h.hed_closedt');
        } else {
            $query->whereNull('h.hed_closedt');
        }

        if (!empty($division) && $division !== 'all') {
            $query->where('h.hed_unt_id', $division);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('h.hed_code', 'ILIKE', "%{$search}%")
                  ->orWhere('p.prj_title', 'ILIKE', "%{$search}%")
                  ->orWhere('u.unt_name', 'ILIKE', "%{$search}%")
                  ->orWhere('u.unt_namesh', 'ILIKE', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('h.hed_id', (int) $search);
                }
            });
        }

        $accounts = $query->select(
            'h.hed_id',
            'h.hed_code',
            'h.hed_name',
            'h.hed_opendt',
            'h.hed_closedt',
            'h.hed_transtype',
            'h.hed_unt_id',
            'u.unt_namesh',
            'u.unt_name',
            'p.prj_id',
            'p.prj_title',
            'p.prj_status',
            'sa.sha_pcc',
            't.trf_amount as alloc'
        )
        ->orderByDesc('h.hed_id')
        ->paginate(20)
        ->withQueryString();

        // Divisions for filter dropdown
        $divisions = DB::table('cen.units')
            ->select('unt_id', 'unt_name', 'unt_namesh')
            ->where('unt_lowers', '>', 0)
            ->orderBy('unt_id')
            ->get();

        return view('finance.accounts.index', compact(
            'accounts',
            'status',
            'openCount',
            'closedCount',
            'divisions',
            'division',
            'search'
        ));
    }

    /**
     * Display the Create New Account form.
     */
    public function create()
    {
        // All eligible projects that do not have an opened account in cen.heads
        $projects = DB::table('prj.projects as p')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'p.prj_unt_id')
            ->whereRaw('p.prj_id NOT IN (SELECT hed_prj_id FROM cen.heads WHERE hed_prj_id IS NOT NULL)')
            ->whereNotIn('p.prj_status', ['Completed', 'Cancelled'])
            ->select('p.prj_id', 'p.prj_code', 'p.prj_title', 'p.prj_unt_id', 'p.prj_status', 'u.unt_namesh', 'u.unt_name')
            ->orderBy('p.prj_code')
            ->get();

        return view('finance.accounts.create', compact('projects'));
    }

    /**
     * AJAX: Get eligible projects for a selected division.
     * Filter: prj_unt_id = unitId, prj_id NOT IN (SELECT hed_prj_id FROM cen.heads WHERE hed_prj_id IS NOT NULL),
     * and prj_status NOT IN ('Completed', 'Cancelled').
     */
    public function getProjectsByUnit($unitId)
    {
        $projects = DB::table('prj.projects')
            ->where('prj_unt_id', $unitId)
            ->whereRaw('prj_id NOT IN (SELECT hed_prj_id FROM cen.heads WHERE hed_prj_id IS NOT NULL)')
            ->whereNotIn('prj_status', ['Completed', 'Cancelled'])
            ->select('prj_id', 'prj_code', 'prj_title', 'prj_unt_id', 'prj_status')
            ->orderBy('prj_code')
            ->get();

        return response()->json($projects);
    }

    /**
     * AJAX: Get project details and compute tentative Head ID and Head Code.
     *
     * Business Logic (cen_heads_add.bas, Misc.bas:235-256):
     * Default hed_id = prj_id.
     * If cen.heads already has a row with hed_id = prj_id:
     *   new hed_id = MAX(prj.projects.prj_id UNION cen.heads.hed_id WHERE hed_type='Project')
     *   within (unt_lowers, unt_uppers] for that division, + 1. If none in range, unt_lowers + 1.
     * If hed_code already exists in cen.heads -> append "-" to force uniqueness.
     */
    public function getProjectDetails($prjId)
    {
        $project = DB::table('prj.projects')
            ->where('prj_id', $prjId)
            ->first();

        if (!$project) {
            return response()->json(['error' => 'Project not found.'], 404);
        }

        $tentativeHedId = (int) $project->prj_id;
        $idCollision = DB::table('cen.heads')->where('hed_id', $tentativeHedId)->exists();

        if ($idCollision) {
            $unit = DB::table('cen.units')->where('unt_id', $project->prj_unt_id)->first();
            $lowers = $unit ? (int) $unit->unt_lowers : 0;
            $uppers = $unit ? (int) $unit->unt_uppers : 0;

            if ($lowers > 0 && $uppers > $lowers) {
                $maxRow = DB::selectOne("
                    SELECT MAX(num) AS max_num FROM (
                        SELECT prj_id AS num FROM prj.projects WHERE prj_id > ? AND prj_id <= ?
                        UNION
                        SELECT hed_id AS num FROM cen.heads WHERE hed_type = 'Project' AND hed_id > ? AND hed_id <= ?
                    ) t
                ", [$lowers, $uppers, $lowers, $uppers]);

                $tentativeHedId = ($maxRow && $maxRow->max_num) ? ((int) $maxRow->max_num + 1) : ($lowers + 1);
            }
        }

        $tentativeHedCode = (string) $project->prj_code;
        $codeCollision = DB::table('cen.heads')->where('hed_code', $tentativeHedCode)->exists();
        if ($codeCollision) {
            $tentativeHedCode .= '-';
        }

        $milestoneCount = DB::table('prj.milestones')
            ->where('msn_xprj_id', $prjId)
            ->count();

        return response()->json([
            'prj_id'             => $project->prj_id,
            'prj_code'           => $project->prj_code,
            'prj_title'          => $project->prj_title,
            'prj_unt_id'         => $project->prj_unt_id,
            'prj_status'         => $project->prj_status,
            'tentative_hed_id'   => $tentativeHedId,
            'tentative_hed_code' => $tentativeHedCode,
            'id_collision'       => $idCollision,
            'code_collision'     => $codeCollision,
            'milestone_count'    => $milestoneCount,
        ]);
    }

    /**
     * Store the newly opened account with single DB transaction across 6 tables.
     */
    public function store(Request $request)
    {
        // 1. Base input validation
        $validated = $request->validate([
            'project_id'       => ['required', 'integer', 'exists:pgsql.prj.projects,prj_id'],
            'hed_id'           => ['required', 'integer', 'unique:pgsql.cen.heads,hed_id'],
            'hed_code'         => ['required', 'string', 'max:50', 'unique:pgsql.cen.heads,hed_code'],
            'hed_opendt'       => ['required', 'date'],
            'hed_transtype'    => ['required', 'in:1,2'],
            'alloc'            => ['required', 'numeric', 'min:0'],
            'mtss_share'       => ['required', 'numeric', 'min:0'],
            'sha_cf'           => ['required', 'numeric', 'min:0'],
            'subheads'         => ['required', 'array', 'min:1', 'max:5'],
            'subheads.*.name'  => ['required', 'string', 'max:100'],
            'subheads.*.alloc' => ['required', 'numeric', 'min:0'],
        ]);

        $hedCode = trim($request->hed_code);

        // Validation Rule D: hed_code must not end with "-" or "_"
        if (str_ends_with($hedCode, '-') || str_ends_with($hedCode, '_')) {
            return back()
                ->withErrors(['hed_code' => 'Account code must not end with "-" or "_". Please edit the code to a final alphanumeric identifier.'])
                ->withInput();
        }

        // Validation Rule A: Project must not already have an open head
        $alreadyOpened = DB::table('cen.heads')
            ->where('hed_prj_id', $request->project_id)
            ->exists();
        if ($alreadyOpened) {
            return back()
                ->withErrors(['project_id' => 'This project already has an assigned financial account in cen.heads.'])
                ->withInput();
        }

        // Fetch project to retrieve confirmed division (prj_unt_id)
        $project = DB::table('prj.projects')
            ->where('prj_id', $request->project_id)
            ->first();
        if (!$project) {
            return back()->withErrors(['project_id' => 'Selected project could not be found.'])->withInput();
        }
        $hedUntId = (int) $project->prj_unt_id;

        // Share Calculations
        $alloc = (float) $request->alloc;
        $mtssShare = (float) $request->mtss_share;
        $shaCf = (float) $request->sha_cf;
        $rdwShare = $alloc - $mtssShare;
        $shaPcc = $rdwShare - $shaCf;

        if ($shaPcc < 0) {
            return back()
                ->withErrors(['alloc' => 'Project Share (alloc - mtss_share - sha_cf) cannot be negative. Please check allocation figures.'])
                ->withInput();
        }

        // Subhead name normalization (Rule E) & validation
        $nameMap = [
            'equipment'    => 'Equipment',
            'construction' => 'Construction',
            'training'     => 'Training',
            'software'     => 'Software',
            'hr'           => 'HR',
            'misc'         => 'Misc',
        ];

        $normalizedSubheads = [];
        $subheadNamesSeen = [];
        $subheadSum = 0.0;

        foreach ($request->subheads as $sbh) {
            $rawName = trim($sbh['name'] ?? '');
            if ($rawName === '') {
                continue;
            }

            $lowerName = strtolower($rawName);
            $normName = $nameMap[$lowerName] ?? $rawName;

            // Check duplicate subhead name under this head
            if (in_array(strtolower($normName), $subheadNamesSeen, true)) {
                return back()
                    ->withErrors(['subheads' => "Duplicate subhead '{$normName}' is not allowed under the same account."])
                    ->withInput();
            }
            $subheadNamesSeen[] = strtolower($normName);

            $sbhAlloc = (float) ($sbh['alloc'] ?? 0);
            $subheadSum += $sbhAlloc;

            $normalizedSubheads[] = [
                'name'  => $normName,
                'alloc' => $sbhAlloc,
            ];
        }

        if (count($normalizedSubheads) === 0) {
            return back()
                ->withErrors(['subheads' => 'At least one valid subhead allocation is required.'])
                ->withInput();
        }

        if (count($normalizedSubheads) > 5) {
            return back()
                ->withErrors(['subheads' => 'Maximum of 5 subheads allowed per account.'])
                ->withInput();
        }

        // Validation Rule D: SUM(subhead allocations) MUST equal sha_pcc (Project Share)
        if (abs($subheadSum - $shaPcc) > 0.01) {
            return back()
                ->withErrors([
                    'subheads' => sprintf(
                        'Total subhead allocations (PKR %s) must exactly equal Project Share (PKR %s). Current difference: PKR %s.',
                        number_format($subheadSum, 2),
                        number_format($shaPcc, 2),
                        number_format($shaPcc - $subheadSum, 2)
                    )
                ])
                ->withInput();
        }

        $hedId = (int) $request->hed_id;
        $hedOpenDt = $request->hed_opendt;
        $hedTransType = (int) $request->hed_transtype;
        $prjId = (int) $request->project_id;

        // Execute all 6 inserts in a single atomic DB transaction
        try {
            DB::transaction(function () use (
                $hedId,
                $hedCode,
                $hedOpenDt,
                $hedTransType,
                $hedUntId,
                $prjId,
                $alloc,
                $mtssShare,
                $shaCf,
                $shaPcc,
                $normalizedSubheads
            ) {
                // 1. cen.heads
                DB::table('cen.heads')->insert([
                    'hed_id'        => $hedId,
                    'hed_name'      => 'xxx', // Legacy fidelity
                    'hed_type'      => 'Project',
                    'hed_code'      => $hedCode,
                    'hed_opendt'    => $hedOpenDt,
                    'hed_closedt'   => null,
                    'hed_transtype' => $hedTransType,
                    'hed_unt_id'    => $hedUntId,
                    'hed_prj_id'    => $prjId,
                ]);

                // 2. fin.transfers (2 rows)
                // 2a. Project Funding (FI) from 990001/990000 to new head
                $fiTrfId = DB::table('fin.transfers')->insertGetId([
                    'trf_date'    => $hedOpenDt,
                    'trf_type'    => 'FI',
                    'trf_title'   => 'Project Funding',
                    'trf_amount'  => (int) round($alloc),
                    'trf_fromhed' => 990001,
                    'trf_fromunt' => 990000,
                    'trf_tohed'   => $hedId,
                    'trf_tount'   => $hedUntId,
                    'trf_status'  => 'Awaited',
                ], 'trf_id');

                // 2b. MTSS Share (FO) from new head to 990011/990000
                $foTrfId = DB::table('fin.transfers')->insertGetId([
                    'trf_date'    => $hedOpenDt,
                    'trf_type'    => 'FO',
                    'trf_title'   => 'MTSS Share',
                    'trf_amount'  => (int) round($mtssShare),
                    'trf_fromhed' => $hedId,
                    'trf_fromunt' => $hedUntId,
                    'trf_tohed'   => 990011,
                    'trf_tount'   => 990000,
                    'trf_status'  => 'Awaited',
                ], 'trf_id');

                // 3. fin.commitments (2 rows)
                // 3a. FI Commitment linked to FI transfer
                $fiCmtId = DB::table('fin.commitments')->insertGetId([
                    'cmt_docid'     => $fiTrfId,
                    'cmt_type'      => 'FI',
                    'cmt_date'      => $hedOpenDt,
                    'cmt_amount'    => $alloc,
                    'cmt_status'    => 'Awaited',
                    'cmt_effhed_id' => $hedId,
                    'cmt_effunt_id' => $hedUntId,
                    'cmt_hed_id'    => 990001,
                    'cmt_unt_id'    => 990000,
                ], 'cmt_id');

                // 3b. FO Commitment linked to FO transfer (NEGATIVE amount)
                $foCmtId = DB::table('fin.commitments')->insertGetId([
                    'cmt_docid'     => $foTrfId,
                    'cmt_type'      => 'FO',
                    'cmt_date'      => $hedOpenDt,
                    'cmt_amount'    => -$mtssShare,
                    'cmt_status'    => 'Awaited',
                    'cmt_effhed_id' => $hedId,
                    'cmt_effunt_id' => $hedUntId,
                    'cmt_hed_id'    => 990011,
                    'cmt_unt_id'    => 990000,
                ], 'cmt_id');

                // 4. fin.sharesalloc (1 row)
                $shaId = DB::table('fin.sharesalloc')->insertGetId([
                    'sha_hed_id'    => $hedId,
                    'sha_ficmt_id'  => $fiCmtId,
                    'sha_focmt_id'  => $foCmtId,
                    'sha_pcc'       => $shaPcc,
                    'sha_prj'       => $shaPcc,
                    'sha_cf'        => $shaCf,
                    'sha_transtype' => $hedTransType,
                ], 'sha_id');

                // 5. fin.subheads (1 row per subhead)
                foreach ($normalizedSubheads as $sbh) {
                    DB::table('fin.subheads')->insertGetId([
                        'sbh_hed_id' => $hedId,
                        'sbh_name'   => $sbh['name'],
                        'sbh_alloc'  => $sbh['alloc'],
                    ], 'sbh_id');
                }

                // 6. fin.msncosts (1 row per milestone in prj.milestones for this project)
                $milestones = DB::table('prj.milestones')
                    ->where('msn_xprj_id', $prjId)
                    ->orderBy('msn_id')
                    ->get();

                foreach ($milestones as $msn) {
                    DB::table('fin.msncosts')->updateOrInsert(
                        [
                            'mct_prj_id'  => $prjId,
                            'mct_msn_id'  => $msn->msn_id,
                        ],
                        [
                            'mct_hed_id'  => $hedId,
                            'mct_msn_idd' => $msn->msn_idd,
                            'mct_cost'    => 0,
                        ]
                    );
                }
            });

            Log::info("Account created successfully. Head ID: {$hedId}, Code: {$hedCode}, Project ID: {$prjId}");

            return redirect()
                ->route('finance.accounts.create')
                ->with('success', "Account '{$hedCode}' (ID: {$hedId}) opened successfully with all funding transfers, commitments, share allocations, subheads, and milestone costs initialized.");

        } catch (\Throwable $e) {
            Log::error("Failed to create account for project {$prjId}: " . $e->getMessage(), [
                'exception' => $e
            ]);

            return back()
                ->withErrors(['general' => 'Account creation failed: ' . $e->getMessage() . '. No data was saved (transaction rolled back).'])
                ->withInput();
        }
    }

    /**
     * Close an account by saving closing date (cen_heads_rev).
     */
    public function closeAccount(Request $request, $hedId)
    {
        $validated = $request->validate([
            'hed_closedt' => ['required', 'date'],
        ]);

        $head = DB::table('cen.heads')->where('hed_id', $hedId)->first();
        if (!$head) {
            return back()->withErrors(['general' => 'Account not found.']);
        }

        DB::table('cen.heads')
            ->where('hed_id', $hedId)
            ->update([
                'hed_closedt' => $validated['hed_closedt'],
            ]);

        Log::info("Account {$head->hed_code} (ID: {$hedId}) closed on {$validated['hed_closedt']}.");

        return back()->with('success', "Account '{$head->hed_code}' closed successfully with closing date {$validated['hed_closedt']}. It is now in Closed Accounts.");
    }

    /**
     * Reopen a closed account.
     */
    public function reopenAccount($hedId)
    {
        $head = DB::table('cen.heads')->where('hed_id', $hedId)->first();
        if (!$head) {
            return back()->withErrors(['general' => 'Account not found.']);
        }

        DB::table('cen.heads')
            ->where('hed_id', $hedId)
            ->update([
                'hed_closedt' => null,
            ]);

        Log::info("Account {$head->hed_code} (ID: {$hedId}) reopened.");

        return back()->with('success', "Account '{$head->hed_code}' reopened successfully. It is now in Open Accounts.");
    }
}
