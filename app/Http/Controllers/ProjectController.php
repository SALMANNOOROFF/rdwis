<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\PrgHistory; 
use App\Models\Unit; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Str;
use App\Models\PrjAttachment;

class ProjectController extends Controller
{
    // --- 1. VIEW PROJECTS (With Filters) ---
   public function index(Request $request)
{
    $user = Auth::user();
    if (!$user) return redirect()->route('login');
    
    $query = Project::where('prj_unt_id', $user->acc_unt_id)->with('milestones');
    if (\Illuminate\Support\Facades\Schema::hasTable('doc.documents')) {
        $query->with('document');
    }

    // Filter Logic
    if ($request->has('status') && $request->status != 'All') {
        $query->where('prj_status', $request->status);
    }

    $projects = $query->orderBy('prj_id', 'desc')->get();
    
    // Attach active employee counts for each project
    $this->attachProjectEmployeeCounts($projects);

    // Fetch live financial expenditures
    $finService = app(\App\Services\FinancialIntelligenceService::class);
    foreach ($projects as $project) {
        $headRecord = DB::table('cen.heads')->where('hed_prj_id', $project->prj_id)->first();
        $spent = 0;
        if ($headRecord) {
            $headStatus = $finService->getHeadStatus($headRecord->hed_id);
            $spent = $headStatus->expenditure ?? 0;
        }
        $project->setAttribute('spent', $spent);
    }
    
    return view('projects.viewprojects', compact('projects'));
}

   public function nrdiIndex(Request $request)
{
    $user = Auth::user();
    if (! $user) {
        return redirect()->route('login');
    }

    $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
    $isHqOrFin = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw', 'fin'], true);

    if ($isHqOrFin) {
        $lower = 0;
        $upper = 99999999;
    } else {
        [$lower, $upper] = $user->acc_lowers == 0
            ? [$user->acc_lowerm, $user->acc_upperm]
            : [$user->acc_lowers, $user->acc_uppers];
    }

    $closedStatuses = ['Closed', 'Completed', 'Cancelled'];

    $query = Project::with('unit')
        ->whereBetween('prj_unt_id', [$lower, $upper]);

    $status = (string) $request->query('status', 'open');
    if ($status === 'closed') {
        $query->whereIn('prj_status', $closedStatuses);
    } elseif ($status === 'all') {
        $status = 'all';
    } else {
        $status = 'open';
        $query->whereNotIn('prj_status', $closedStatuses);
    }

    $divisionId = $request->query('division');
    if (is_numeric($divisionId) && (int) $divisionId > 0) {
        $query->where('prj_unt_id', (int) $divisionId);
    }

    $term = trim((string) $request->query('term', ''));
    if ($term !== '') {
        $query->where(function ($q) use ($term) {
            $q->where('prj_code', 'ILIKE', '%' . $term . '%')
              ->orWhere('prj_title', 'ILIKE', '%' . $term . '%');
        });
    }

    $projects = $query->orderByDesc('prj_id')->paginate(50);
    $projects->appends([
        'status' => $status,
        'division' => $divisionId,
        'term' => $term,
    ]);

    $this->attachProjectEmployeeCounts($projects);

    $divisions = Unit::where('unt_type', 'Division')
        ->whereBetween('unt_id', [$lower, $upper])
        ->orderBy('unt_name', 'asc')
        ->get();

    return view('nrdi.projects.index', compact('projects', 'divisions', 'status', 'divisionId', 'term'));
}

    public function nrdiShow($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHqOrFin = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw', 'fin'], true);

        if ($isHqOrFin) {
            $lower = 0;
            $upper = 99999999;
        } else {
            [$lower, $upper] = $user->acc_lowers == 0
                ? [$user->acc_lowerm, $user->acc_upperm]
                : [$user->acc_lowers, $user->acc_uppers];
        }

        $project = Project::with('milestones', 'attachments', 'unit')
            ->where('prj_id', $id)
            ->whereBetween('prj_unt_id', [$lower, $upper])
            ->firstOrFail();

        // Financial Intelligence (Legacy Logic Integration)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $headRecord = DB::table('cen.heads')->where('hed_prj_id', $id)->first();
        
        $head = null;
        $subheads = [];
        if ($headRecord) {
            $head = $finService->getHeadStatus($headRecord->hed_id);
            $subheads = $finService->getSubheadBreakdown($headRecord->hed_id);
        }

        $totalSpent = $head->expenditure ?? 0;
        $balance = $head->balance ?? (($project->prj_propcost ?? 0) - $totalSpent);
        $spentPercentage = ($project->prj_propcost ?? 0) > 0 ? round(($totalSpent / $project->prj_propcost) * 100, 1) : 0;

        $equipSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'equip') !== false);
        $hrSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'hr') !== false || stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'personnel') !== false);
        $miscSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'misc') !== false);

        $equipExp = (float)(is_array($equipSh) ? ($equipSh['expenditure'] ?? 0) : ($equipSh->expenditure ?? 0));
        $equipAlloc = (float)(is_array($equipSh) ? ($equipSh['allocation'] ?? 0) : ($equipSh->allocation ?? 0));
        $equipPct = $equipAlloc > 0 ? round(($equipExp / $equipAlloc) * 100) : ($totalSpent > 0 ? round(($equipExp / $totalSpent) * 100) : 0);

        $hrExp = (float)(is_array($hrSh) ? ($hrSh['expenditure'] ?? 0) : ($hrSh->expenditure ?? 0));
        $hrAlloc = (float)(is_array($hrSh) ? ($hrSh['allocation'] ?? 0) : ($hrSh->allocation ?? 0));
        $hrPct = $hrAlloc > 0 ? round(($hrExp / $hrAlloc) * 100) : ($totalSpent > 0 ? round(($hrExp / $totalSpent) * 100) : 0);

        $miscExp = (float)(is_array($miscSh) ? ($miscSh['expenditure'] ?? 0) : ($miscSh->expenditure ?? 0));
        $miscAlloc = (float)(is_array($miscSh) ? ($miscSh['allocation'] ?? 0) : ($miscSh->allocation ?? 0));
        $miscPct = $miscAlloc > 0 ? round(($miscExp / $miscAlloc) * 100) : ($totalSpent > 0 ? round(($miscExp / $totalSpent) * 100) : 0);

        $finData = [
            'equip' => $equipExp ?: ($totalSpent * 0.45),
            'equip_pct' => min(100, max(0, $equipPct ?: ($totalSpent > 0 ? 45 : 0))),
            'hr'    => $hrExp ?: ($totalSpent * 0.35),
            'hr_pct' => min(100, max(0, $hrPct ?: ($totalSpent > 0 ? 35 : 0))),
            'misc'  => $miscExp ?: ($totalSpent * 0.20),
            'misc_pct' => min(100, max(0, $miscPct ?: ($totalSpent > 0 ? 20 : 0))),
        ];

        $mprsSubmitted = PrgHistory::where('pgh_xprj_id', $id)->count();

        $startDate = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : \Carbon\Carbon::now();
        $endDate = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : \Carbon\Carbon::now();

        $totalMonths = $startDate->diffInMonths($endDate);
        if ($totalMonths < 1) $totalMonths = 1;
        $mprsLeft = max(0, $totalMonths - $mprsSubmitted);

        $readOnly = true;

        $showProjectActualSection = false;
        $showPrjShareValue = false;
        $today = \Carbon\Carbon::today()->toDateString();
        $heads = DB::table('cen.heads')->get()->keyBy('hed_id');

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

        $teamEmpIds = [];
        foreach ($activeEmployees as $emp) {
            $currentHeadId = $activePlans->has($emp->emp_id)
                ? $activePlans[$emp->emp_id]->cpn_hed_id
                : $emp->emp_hed_id;

            $h = $heads->get($currentHeadId);
            $prjId = null;
            if ($h) {
                $prjId = $h->hed_prj_id ?: $h->hed_id;
            }

            if ($prjId == $id) {
                $teamEmpIds[] = $emp->emp_id;
            }
        }

        $team = \App\Models\Employee::whereIn('hr.emps.emp_id', $teamEmpIds)
            ->leftJoin('hr.empsexta', 'hr.emps.emp_id', '=', 'hr.empsexta.empexta_emp_id')
            ->select('hr.emps.*', 'hr.empsexta.emp_email', 'hr.empsexta.emp_mobile')
            ->get();

        if ($head) {
            $showProjectActualSection = !(round($head->pcc_expenditure, 2) == round($head->pcc_own_exp, 2) && round($head->others_loans_taken, 2) == 0.0);
            $showPrjShareValue = (round($head->prj_share, 2) != round($head->pcc_share, 2));
        }

        return view('projects.openprojectdetails', compact(
            'project',
            'totalSpent',
            'balance',
            'spentPercentage',
            'finData',
            'mprsSubmitted',
            'mprsLeft',
            'totalMonths',
            'readOnly',
            'head',
            'subheads',
            'showProjectActualSection',
            'showPrjShareValue',
            'team'
        ));
    }


    public function show($id)
    {
        $project = Project::with('milestones', 'attachments')->where('prj_id', $id)->firstOrFail();

        // Financial Intelligence (Legacy Logic Integration)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $headRecord = DB::table('cen.heads')->where('hed_prj_id', $id)->first();
        
        $head = null;
        $subheads = [];
        if ($headRecord) {
            $head = $finService->getHeadStatus($headRecord->hed_id);
            $subheads = $finService->getSubheadBreakdown($headRecord->hed_id);
        }

        $totalSpent = $head->expenditure ?? 0;
        $balance = $head->balance ?? ($project->prj_propcost - $totalSpent);
        $spentPercentage = $project->prj_propcost > 0 ? round(($totalSpent / $project->prj_propcost) * 100, 1) : 0;

        $equipSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'equip') !== false);
        $hrSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'hr') !== false || stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'personnel') !== false);
        $miscSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'misc') !== false);

        $equipExp = (float)(is_array($equipSh) ? ($equipSh['expenditure'] ?? 0) : ($equipSh->expenditure ?? 0));
        $equipAlloc = (float)(is_array($equipSh) ? ($equipSh['allocation'] ?? 0) : ($equipSh->allocation ?? 0));
        $equipPct = $equipAlloc > 0 ? round(($equipExp / $equipAlloc) * 100) : ($totalSpent > 0 ? round(($equipExp / $totalSpent) * 100) : 0);

        $hrExp = (float)(is_array($hrSh) ? ($hrSh['expenditure'] ?? 0) : ($hrSh->expenditure ?? 0));
        $hrAlloc = (float)(is_array($hrSh) ? ($hrSh['allocation'] ?? 0) : ($hrSh->allocation ?? 0));
        $hrPct = $hrAlloc > 0 ? round(($hrExp / $hrAlloc) * 100) : ($totalSpent > 0 ? round(($hrExp / $totalSpent) * 100) : 0);

        $miscExp = (float)(is_array($miscSh) ? ($miscSh['expenditure'] ?? 0) : ($miscSh->expenditure ?? 0));
        $miscAlloc = (float)(is_array($miscSh) ? ($miscSh['allocation'] ?? 0) : ($miscSh->allocation ?? 0));
        $miscPct = $miscAlloc > 0 ? round(($miscExp / $miscAlloc) * 100) : ($totalSpent > 0 ? round(($miscExp / $totalSpent) * 100) : 0);

        $finData = [
            'equip' => $equipExp ?: ($totalSpent * 0.45),
            'equip_pct' => min(100, max(0, $equipPct ?: ($totalSpent > 0 ? 45 : 0))),
            'hr'    => $hrExp ?: ($totalSpent * 0.35),
            'hr_pct' => min(100, max(0, $hrPct ?: ($totalSpent > 0 ? 35 : 0))),
            'misc'  => $miscExp ?: ($totalSpent * 0.20),
            'misc_pct' => min(100, max(0, $miscPct ?: ($totalSpent > 0 ? 20 : 0))),
        ];

        $mprsSubmitted = PrgHistory::where('pgh_xprj_id', $id)->count();

        $startDate = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : \Carbon\Carbon::now();
        $endDate = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : \Carbon\Carbon::now();
        
        $totalMonths = $startDate->diffInMonths($endDate);
        if($totalMonths < 1) $totalMonths = 1;

        $mprsLeft = max(0, $totalMonths - $mprsSubmitted);

        $showProjectActualSection = false;
        $showPrjShareValue = false;
        $today = \Carbon\Carbon::today()->toDateString();
        $heads = DB::table('cen.heads')->get()->keyBy('hed_id');
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

        $teamEmpIds = [];
        foreach ($activeEmployees as $emp) {
            $currentHeadId = $activePlans->has($emp->emp_id)
                ? $activePlans[$emp->emp_id]->cpn_hed_id
                : $emp->emp_hed_id;

            $h = $heads->get($currentHeadId);
            $prjId = null;
            if ($h) {
                $prjId = $h->hed_prj_id ?: $h->hed_id;
            }

            if ($prjId == $id) {
                $teamEmpIds[] = $emp->emp_id;
            }
        }

        $team = \App\Models\Employee::whereIn('hr.emps.emp_id', $teamEmpIds)
            ->leftJoin('hr.empsexta', 'hr.emps.emp_id', '=', 'hr.empsexta.empexta_emp_id')
            ->select('hr.emps.*', 'hr.empsexta.emp_email', 'hr.empsexta.emp_mobile')
            ->get();

        if ($head) {
            $showProjectActualSection = !(round($head->pcc_expenditure, 2) == round($head->pcc_own_exp, 2) && round($head->others_loans_taken, 2) == 0.0);
            $showPrjShareValue = (round($head->prj_share, 2) != round($head->pcc_share, 2));
        }

        return view('projects.openprojectdetails', compact(
            'project', 'totalSpent', 'balance', 'spentPercentage', 'finData', 
            'mprsSubmitted', 'mprsLeft', 'totalMonths', 'head', 'subheads',
            'showProjectActualSection', 'showPrjShareValue', 'team'
        ));
    }

    /**
     * Dedicated Full Page for Financial Intelligence Report
     */
    public function financialView($id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $project = Project::with('milestones', 'attachments', 'unit')->where('prj_id', $id)->first();
        if (!$project) {
            $headForPrj = DB::table('cen.heads')->where('hed_id', $id)->first();
            if ($headForPrj && $headForPrj->hed_prj_id) {
                $project = Project::with('milestones', 'attachments', 'unit')->where('prj_id', $headForPrj->hed_prj_id)->first();
            }
        }
        if (!$project) {
            abort(404, 'Project not found');
        }

        // Financial Intelligence (Legacy Logic Integration)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $headRecord = DB::table('cen.heads')->where('hed_prj_id', $project->prj_id)->first();
        if (!$headRecord && is_numeric($id)) {
            $headRecord = DB::table('cen.heads')->where('hed_id', $id)->first();
        }
        
        $head = null;
        $subheads = [];
        $loans = null;
        $milestones = collect();
        $installments = collect();
        $transfers = collect();

        if ($headRecord) {
            $head = $finService->getHeadStatus($headRecord->hed_id);
            $subheads = $finService->getSubheadBreakdown($headRecord->hed_id);
            $loans = $finService->getLoans($headRecord->hed_id);

            $milestones = DB::table('prj.milestones as m')
                ->leftJoin('fin.msncosts as mc', function ($join) use ($headRecord) {
                    $join->on('m.msn_idd', '=', 'mc.mct_msn_idd')
                         ->where('mc.mct_hed_id', '=', $headRecord->hed_id);
                })
                ->where('m.msn_xprj_id', $project->prj_id)
                ->select(
                    'm.msn_id',
                    'm.msn_idd',
                    'm.msn_type',
                    'm.msn_desc',
                    'm.msn_status',
                    'm.msn_cost',
                    'm.msn_startdt',
                    'm.msn_targetdt',
                    'm.msn_achvdt',
                    'mc.mct_cost'
                )
                ->orderBy('m.msn_id')
                ->get();

            $installments = DB::table('fin.sharesinstall as si')
                ->leftJoin('fin.transactions as t', 't.trn_id', '=', 'si.shi_fitrn_id')
                ->leftJoin('prj.milestones as m', 'm.msn_idd', '=', 'si.shi_msn_idd')
                ->where('si.shi_hed_id', $headRecord->hed_id)
                ->select(
                    'si.shi_id',
                    'si.shi_hed_id',
                    'si.shi_pcc',
                    'si.shi_cf',
                    'si.shi_prj',
                    't.trn_date',
                    'm.msn_desc'
                )
                ->orderBy('si.shi_id')
                ->get();

            $transfers = DB::table('fin.transfers')
                ->where('trf_tohed', $headRecord->hed_id)
                ->orWhere('trf_fromhed', $headRecord->hed_id)
                ->orderBy('trf_id')
                ->get();
        }

        $allAttachments = \App\Models\PrjAttachment::where('jat_objid', $project->prj_id)->get();
        if ($allAttachments->isEmpty() && $project->attachments) {
            $allAttachments = $project->attachments;
        }

        $totalSpent = $head->expenditure ?? 0;
        $balance = $head->balance ?? ($project->prj_propcost - $totalSpent);
        $spentPercentage = $project->prj_propcost > 0 ? round(($totalSpent / $project->prj_propcost) * 100, 1) : 0;

        $equipSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'equip') !== false);
        $hrSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'hr') !== false || stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'personnel') !== false);
        $miscSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'misc') !== false);

        $equipExp = (float)(is_array($equipSh) ? ($equipSh['expenditure'] ?? 0) : ($equipSh->expenditure ?? 0));
        $equipAlloc = (float)(is_array($equipSh) ? ($equipSh['allocation'] ?? 0) : ($equipSh->allocation ?? 0));
        $equipPct = $equipAlloc > 0 ? round(($equipExp / $equipAlloc) * 100) : ($totalSpent > 0 ? round(($equipExp / $totalSpent) * 100) : 0);

        $hrExp = (float)(is_array($hrSh) ? ($hrSh['expenditure'] ?? 0) : ($hrSh->expenditure ?? 0));
        $hrAlloc = (float)(is_array($hrSh) ? ($hrSh['allocation'] ?? 0) : ($hrSh->allocation ?? 0));
        $hrPct = $hrAlloc > 0 ? round(($hrExp / $hrAlloc) * 100) : ($totalSpent > 0 ? round(($hrExp / $totalSpent) * 100) : 0);

        $miscExp = (float)(is_array($miscSh) ? ($miscSh['expenditure'] ?? 0) : ($miscSh->expenditure ?? 0));
        $miscAlloc = (float)(is_array($miscSh) ? ($miscSh['allocation'] ?? 0) : ($miscSh->allocation ?? 0));
        $miscPct = $miscAlloc > 0 ? round(($miscExp / $miscAlloc) * 100) : ($totalSpent > 0 ? round(($miscExp / $totalSpent) * 100) : 0);

        $finData = [
            'equip' => $equipExp ?: ($totalSpent * 0.45),
            'equip_alloc' => $equipAlloc,
            'equip_pct' => min(100, max(0, $equipPct ?: ($totalSpent > 0 ? 45 : 0))),
            'hr'    => $hrExp ?: ($totalSpent * 0.35),
            'hr_alloc' => $hrAlloc,
            'hr_pct' => min(100, max(0, $hrPct ?: ($totalSpent > 0 ? 35 : 0))),
            'misc'  => $miscExp ?: ($totalSpent * 0.20),
            'misc_alloc' => $miscAlloc,
            'misc_pct' => min(100, max(0, $miscPct ?: ($totalSpent > 0 ? 20 : 0))),
        ];

        $showProjectActualSection = false;
        $showPrjShareValue = false;
        if ($head) {
            $showProjectActualSection = !(round($head->pcc_expenditure, 2) == round($head->pcc_own_exp, 2) && round($head->others_loans_taken, 2) == 0.0);
            $showPrjShareValue = (round($head->prj_share, 2) != round($head->pcc_share, 2));
        }

        // Back navigation URL
        $backUrl = url()->previous();
        if (!$backUrl || str_contains($backUrl, 'financial-view')) {
            $backUrl = route('projects.show', $project->prj_id);
        }

        return view('projects.financial_view', compact(
            'project', 'totalSpent', 'balance', 'spentPercentage', 'finData', 
            'head', 'subheads', 'loans', 'milestones', 'installments', 'transfers',
            'headRecord', 'showProjectActualSection', 'showPrjShareValue', 'backUrl',
            'allAttachments'
        ));
    }

    // --- 2. CREATE PROJECT PAGE (Smart Logic) ---
    public function create(Request $request)
    {
        $project = null;
        $step = 1;

        if ($request->has('draft_id')) {
            $project = Project::find($request->draft_id);
            if($project && $project->prj_status == 'Draft') {
                $step = 2; // Direct Phase 2
            }
        }

        return view('projects.addnewproject', compact('project', 'step'));
    }

    // --- STORE (Phase 1) ---
    public function store(Request $request)
    {
        $connection = config('database.default'); 
        
        $request->validate([
            'prj_code' => ['required', 'string', 'max:20', Rule::unique("$connection.prj.projects", 'prj_code')],
            'prj_title' => 'required|string|max:255',
            'prj_aprvdt' => 'required|date',
        ]);

        $maxId = Project::max('prj_id');
        $nextId = $maxId ? $maxId + 1 : 1;

        $project = new Project();
        $project->prj_id = $nextId;
        $project->prj_code = $request->prj_code;
        $project->prj_title = $request->prj_title;
        $project->prj_sponsor = $request->prj_sponsor;
        $project->prj_propcost = $request->prj_propcost;
        
        $project->prj_scope = $request->prj_scope;
        $project->prj_propdt = $request->prj_propdt;
        $project->prj_assigndt = $request->prj_assigndt;
        $project->prj_aprvdt = $request->prj_aprvdt;
        
        $project->prj_status = 'Draft';
        $project->prj_unt_id = Auth::check() ? Auth::user()->acc_unt_id : 200000;
        $project->prj_rcptdt = now();
        
        $project->save();

        // LOGGING ADDED HERE
        $this->logActivity($project->prj_id, 'Initiation', 'Project Initiated with Draft status');

        // Handle Files
        $this->handleUpload($request, $project, 'doc_ppf', 'PPF');
        $this->handleUpload($request, $project, 'doc_urd', 'URD');

        return redirect()->route('addnewproject', ['draft_id' => $project->prj_id])
                         ->with('success', 'Phase 1 Saved! Proceed to Work Order details.');
    }

    // --- FINALIZE (Phase 2) ---
    public function finalizeProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'prj_startdt' => 'required|date',
            'prj_estenddt' => 'required|date|after:prj_startdt',
        ]);

        $project->prj_startdt = $request->prj_startdt;
        $project->prj_estenddt = $request->prj_estenddt;
        $project->prj_status = 'Open'; 
        $project->save();

        // LOGGING ADDED HERE
        $this->logActivity($id, 'Execution', 'Project Finalized & Work Order Uploaded');

        // Handle Files (Including Approval Letter Fix)
        $this->handleUpload($request, $project, 'doc_workorder', 'Work Order');
        $this->handleUpload($request, $project, 'doc_approval_letter', 'Approval Letter');

        // Milestone Logic
        if ($request->has('milestones')) {
            foreach ($request->milestones as $msData) {
                if (!empty($msData['desc'])) {
                    $mId = Milestone::max('msn_id') + 1;
                    $ms = new Milestone();
                    $ms->msn_id = $mId;
                    $ms->msn_xprj_id = $project->prj_id;
                    $ms->msn_desc = $msData['desc'];
                    $ms->msn_targetdt = $msData['date'];
                    $ms->msn_status = 'Pending';
                    $ms->msn_type = 'Technical';
                    $ms->save();
                    
                    // Log each milestone creation
                    $this->logActivity($id, 'Milestone', "Created Milestone: {$msData['desc']}");
                }
            }
        }

        return redirect()->route('projects.show', $project->prj_id)
                         ->with('success', 'Project Initiated Successfully!');
    }

    // --- FILE UPLOAD LOGIC ---
    private function handleUpload($request, $project, $inputName, $docType)
    {
        if ($request->hasFile($inputName)) {
            $file = $request->file($inputName);
            
            $prefix = match (strtolower(trim($docType))) {
                'ppf' => 'ppf-prj-',
                'urd' => 'urd-prj-',
                'project proposal', 'ppr' => 'ppr-prj-',
                'work order', 'wo' => 'wo-prj-',
                'approval letter', 'approval' => 'apl-prj-',
                'milestone completion certificate', 'mcc' => 'mcc-prj-',
                default => 'mx-prj-',
            };

            $path = app(\App\Services\FileStorageService::class)->store(
                $file,
                'prj',
                $prefix,
                (string) $project->prj_id
            );
            
            $att = PrjAttachment::where('jat_objid', $project->prj_id)
                ->whereIn('jat_objtype', ['prj', 'Project'])
                ->where('jat_type', $docType)
                ->first();

            if (!$att) {
                $att = new PrjAttachment();
                $att->jat_objid = $project->prj_id;
                $att->jat_objtype = 'prj';
                $att->jat_type = $docType;
            }
            
            $att->jat_path = $path;
            $att->save();

            // LOGGING ADDED HERE
            $this->logActivity($project->prj_id, 'Attachment', "Uploaded Document: $docType");
        }
    }

    // --- VIEW ATTACHMENT ---
    public function viewAttachment($id)
    {
        $attachment = PrjAttachment::findOrFail($id);
        return app(\App\Services\FileStorageService::class)->response($attachment->jat_path);
    }

    // --- UPLOAD OTHER DOCUMENT ---
    public function storeOtherAttachment(Request $request, $id)
    {
        $request->validate([
            'custom_name' => 'required|string|max:50',
            'doc_file' => 'required|file|mimes:pdf,jpg,png,doc,docx|max:10240',
        ]);

        $project = Project::findOrFail($id);
        $this->handleUpload($request, $project, 'doc_file', $request->custom_name);

        return redirect()->back()->with('success', 'Document added successfully!');
    }

    // --- SINGLE FILE UPLOAD ---
    public function uploadSingleAttachment(Request $request, $id)
    {
        $request->validate([
            'single_file' => 'required|file',
            'doc_type' => 'required|string'
        ]);

        $project = Project::findOrFail($id);
        $this->handleUpload($request, $project, 'single_file', $request->doc_type);

        return redirect()->back()->with('success', $request->doc_type . ' Uploaded Successfully!');
    }

    // --- DELETE ATTACHMENT ---
    public function deleteAttachment($id)
    {
        $attachment = PrjAttachment::findOrFail($id);
        if (!empty($attachment->jat_path)) {
            app(\App\Services\FileStorageService::class)->delete($attachment->jat_path);
        }
        $attachment->delete();
        
        // Log deletion
        $this->logActivity($attachment->jat_objid, 'Attachment', "Deleted Document: {$attachment->jat_type}");

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    // --- MILESTONES ---
    public function createMilestone($id)
    {
        $project = Project::where('prj_id', $id)->firstOrFail();
        return view('projects.addmilestonepr', compact('project'));
    }

    public function storeMilestone(Request $request, $id)
    {
        $request->validate([
            'msn_desc' => 'required',
            'msn_targetdt' => 'required|date',
            'msn_type' => 'required',
            'msn_status' => 'required'
        ]);

        $maxId = Milestone::max('msn_id');
        $nextId = $maxId ? $maxId + 1 : 1;

        $milestone = new Milestone();
        $milestone->msn_id = $nextId;
        $milestone->msn_xprj_id = $id;
        $milestone->msn_desc = $request->msn_desc;
        $milestone->msn_targetdt = $request->msn_targetdt;
        $milestone->msn_type = $request->msn_type;
        $milestone->msn_status = $request->msn_status;
        $milestone->save();

        // LOGGING ADDED HERE
        $this->logActivity($id, 'Milestone', "Added Milestone: {$request->msn_desc}");

        return redirect()->route('projects.show', $id)->with('success', 'Milestone Added!');
    }

    public function editMilestone($id)
    {
        $milestone = Milestone::where('msn_id', $id)->firstOrFail();
        $project = Project::where('prj_id', $milestone->msn_xprj_id)->first();
        return view('projects.editmilestone', compact('milestone', 'project'));
    }

    public function updateMilestone(Request $request, $id)
    {
        $request->validate([
            'msn_desc' => 'required',
            'msn_targetdt' => 'required|date',
            'msn_type' => 'required',
            'msn_status' => 'required',
            'completion_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $milestone = Milestone::where('msn_id', $id)->firstOrFail();
        
        $milestone->msn_desc = $request->msn_desc;
        $milestone->msn_targetdt = $request->msn_targetdt;
        $milestone->msn_type = $request->msn_type;
        $milestone->msn_status = $request->msn_status;

        if ($request->hasFile('completion_certificate')) {
            if (!empty($milestone->msn_cc_path)) {
                app(\App\Services\FileStorageService::class)->delete($milestone->msn_cc_path);
            }
            $targetId = (string) ($milestone->msn_idd ?? $milestone->msn_id);
            $path = app(\App\Services\FileStorageService::class)->store(
                $request->file('completion_certificate'),
                'prj',
                'mcc-msn-',
                $targetId
            );
            $milestone->msn_cc_path = $path;
        }

        $milestone->save();

        // LOGGING ADDED HERE
        $this->logActivity($milestone->msn_xprj_id, 'Milestone', "Updated Milestone: {$request->msn_desc}");

        return redirect()->route('projects.show', $milestone->msn_xprj_id)
                         ->with('success', 'Milestone Updated Successfully!');
    }

    public function markMilestoneComplete(Request $request)
    {
        $request->validate([
            'msn_id' => ['required', Rule::exists(Milestone::class, 'msn_id')],
            'achieved_date' => 'required|date',
            'completion_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $milestone = Milestone::find($request->msn_id);
        if($milestone) {
            $milestone->msn_achvdt = $request->achieved_date;
            $milestone->msn_status = 'Completed'; // Status auto-update

            if ($request->hasFile('completion_certificate')) {
                if (!empty($milestone->msn_cc_path)) {
                    app(\App\Services\FileStorageService::class)->delete($milestone->msn_cc_path);
                }
                $targetId = (string) ($milestone->msn_idd ?? $milestone->msn_id);
                $path = app(\App\Services\FileStorageService::class)->store(
                    $request->file('completion_certificate'),
                    'prj',
                    'mcc-msn-',
                    $targetId
                );
                $milestone->msn_cc_path = $path;
            }

            $milestone->save();
        }

        return redirect()->back()->with('success', 'Milestone marked as Completed!');
    }

    public function deleteMilestone($id)
    {
        $milestone = Milestone::where('msn_id', $id)->firstOrFail();
        $projectId = $milestone->msn_xprj_id;
        $desc = $milestone->msn_desc;

        if (!empty($milestone->msn_cc_path)) {
            app(\App\Services\FileStorageService::class)->delete($milestone->msn_cc_path);
        }
        
        $milestone->delete();

        // LOGGING ADDED HERE
        $this->logActivity($projectId, 'Milestone', "Deleted Milestone: $desc");

        return redirect()->route('projects.show', $projectId)
                         ->with('success', 'Milestone Deleted Successfully!');
    }

    // --- MPR (Reports) ---
    public function mprProjectList()
    {
        $user = Auth::user();
        $projects = Project::where('prj_unt_id', $user->acc_unt_id)->get();
        return view('projects.openmprs', compact('projects'));
    }

    public function mprProjectView($id)
    {
        $project = Project::where('prj_id', $id)->firstOrFail();
        $mprHistory = PrgHistory::where('pgh_xprj_id', $id)->orderBy('pgh_dtg', 'desc')->get();
        $currentMilestone = Milestone::where('msn_xprj_id', $id)
                                     ->whereIn('msn_status', ['Pending', 'In Progress'])
                                     ->orderBy('msn_targetdt', 'asc')
                                     ->first();

        return view('projects.viewmpr', compact('project', 'mprHistory', 'currentMilestone'));
    }

    public function storeMpr(Request $request, $id)
    {
        $request->validate([
            'pgh_dtg' => 'required|date',
            'pgh_progress' => 'required|string',
            'progress_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $mpr = new PrgHistory();
        $mpr->pgh_xprj_id = $id;
        $mpr->pgh_dtg = $request->pgh_dtg;
        $mpr->pgh_progress = $request->pgh_progress;
        
        if (Auth::check()) {
            $author = Auth::user()->role->rol_desigshort ?? Auth::user()->acc_username;
            $mpr->pgh_author = $author;
            $mpr->pgh_level = Auth::user()->acc_level;
        } else {
            $mpr->pgh_author = 'System';
            $mpr->pgh_level = 1;
        }

        $mpr->pgh_status = 'Submitted';
        $mpr->pgh_underedit = true; 
        $mpr->save();

        if ($request->hasFile('progress_file')) {
            $path = app(\App\Services\FileStorageService::class)->store(
                $request->file('progress_file'),
                'prj',
                'prg-pgh-',
                (string) $mpr->pgh_id
            );
            $mpr->pgh_path = $path;
            $mpr->save();
        }
        
        // Log MPR
        $this->logActivity($id, 'MPR', "Submitted Monthly Progress Report");

        return redirect()->route('mpr.view', $id)->with('success', 'Progress Report Added Successfully!');
    }

    // --- FINANCIAL SPENDINGS ---
    public function projectSpendings($id)
    {
        $project = Project::where('prj_id', $id)->firstOrFail();

        $totalBudget = DB::table('fin.msncosts')->where('mct_prj_id', $id)->sum('mct_cost');

        $totalSpent = DB::table('fin.transactions')
            ->join('fin.commitments', 'fin.transactions.trn_cmt_id', '=', 'fin.commitments.cmt_id')
            ->where('fin.commitments.cmt_docid', $id)
            ->sum('fin.transactions.trn_amount1');

        $budgetBreakdown = DB::table('fin.msncosts')
            ->select('mct_hed_id', DB::raw('SUM(mct_cost) as total_cost'))
            ->where('mct_prj_id', $id)
            ->groupBy('mct_hed_id')
            ->get();

        $balance = $totalBudget - $totalSpent;
        $percentageSpent = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        $chartLabels = $budgetBreakdown->pluck('mct_hed_id')->toArray();
        $chartData = $budgetBreakdown->pluck('total_cost')->toArray();

        return view('projects.spendings', compact(
            'project', 'totalBudget', 'totalSpent', 'balance', 'percentageSpent', 'chartLabels', 'chartData'
        ));
    }

    // --- GLOBAL PROJECT HISTORY (New Function) ---
   public function projectHistory(Request $request)
{
    // Agar URL mein ?project_id=123 hai, to sirf uski MPRs dikhao
    if ($request->has('project_id')) {
        $projectId = $request->project_id;
        $project = Project::find($projectId);

        // Fetch MPRs for this project
        $mprHistory = PrgHistory::where('pgh_xprj_id', $projectId)
            ->orderBy('pgh_dtg', 'desc')
            ->get();
            
        $viewType = 'mpr_list'; // View ko batane ke liye ke ye MPRs hain

        return view('projects.projecthistory', compact('mprHistory', 'project', 'viewType'));
    }

    // Warna purana Global Audit Log dikhao
    if (\Illuminate\Support\Facades\Schema::hasTable('project_activities')) {
        $activities = DB::table('project_activities')
            ->join('prj.projects', 'project_activities.pja_prj_id', '=', 'prj.projects.prj_id')
            ->select('project_activities.*', 'prj.projects.prj_title', 'prj.projects.prj_code')
            ->orderBy('created_at', 'desc')
            ->get();
    } else {
        $activities = collect([]);
    }

    $viewType = 'global_log';

    return view('projects.projecthistory', compact('activities', 'viewType'));
}
    // --- HELPER: LOG ACTIVITY ---
    private function logActivity($projectId, $action, $details)
    {
        // Ensure table 'project_activities' exists before inserting
        if (\Illuminate\Support\Facades\Schema::hasTable('project_activities')) {
            DB::table('project_activities')->insert([
                'pja_prj_id' => $projectId,
                'pja_action' => $action,
                'pja_details' => $details,
                'pja_user' => Auth::check() ? Auth::user()->acc_username : 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }


   public function sordIndex()
{
    // 1. Saari Divisions uthao ('prj' area wali) dropdown ke liye
    $divisions = Unit::where('unt_area', 'prj')->orderBy('unt_name', 'asc')->get();

    // 2. Saare Projects uthao (Unit relation ke sath taake naam dikha sakein)
    $projects = Project::with('unit')->orderBy('prj_id', 'desc')->get();

    $this->attachProjectEmployeeCounts($projects);

    // 3. Naye View par bhejo
    return view('sord.projects', compact('projects', 'divisions'));
}

    protected function attachProjectEmployeeCounts($projects)
    {
        $today = \Carbon\Carbon::today()->toDateString();

        // 1. Get all active employees
        $activeEmployees = DB::table('hr.emps')
            ->whereRaw("LOWER(emp_status) IN ('active','current')")
            ->get(['emp_id', 'emp_hed_id']);

        // 2. Active contract plans for today
        $activePlans = DB::table('hr.contractplans as cp')
            ->join('hr.contracts as c', 'c.ctr_id', '=', 'cp.cpn_ctr_id')
            ->whereRaw('? between cp.cpn_startdt and cp.cpn_enddt', [$today])
            ->whereNotNull('cp.cpn_hed_id')
            ->select('c.ctr_num as emp_id', 'cp.cpn_hed_id')
            ->get()
            ->keyBy('emp_id');

        // 3. Map head to project
        $heads = DB::table('cen.heads')->get()->keyBy('hed_id');

        $projectStaffCounts = [];
        foreach ($activeEmployees as $emp) {
            $currentHeadId = $activePlans->has($emp->emp_id)
                ? $activePlans[$emp->emp_id]->cpn_hed_id
                : $emp->emp_hed_id;

            $head = $heads->get($currentHeadId);
            $prjId = null;
            if ($head) {
                $prjId = $head->hed_prj_id ?: $head->hed_id;
            }

            if ($prjId) {
                $projectStaffCounts[$prjId] = ($projectStaffCounts[$prjId] ?? 0) + 1;
            }
        }

        foreach ($projects as $p) {
            $p->emp_count = (int) ($projectStaffCounts[$p->prj_id] ?? 0);
        }

        return $projects;
    }

   // --- SORD READ-ONLY PROJECT DETAILS ---
   public function sordShow($id)
   {
       $project = Project::with('milestones', 'attachments')->where('prj_id', $id)->firstOrFail();
       
       // 1. Get Financial Intelligence using proper service
       $finService = app(\App\Services\FinancialIntelligenceService::class);
       $headRecord = DB::table('cen.heads')->where('hed_prj_id', $id)->first();
       
       $totalSpent = 0;
       $balance = $project->prj_propcost;
       $spentPercentage = 0;
       $finData = ['equip' => 0, 'hr' => 0, 'misc' => 0];
       $head = null;
       $subheads = [];
       
       if ($headRecord) {
           $head = $finService->getHeadStatus($headRecord->hed_id);
           $totalSpent = $head->expenditure ?? 0;
           $balance = $head->balance ?? ($project->prj_propcost - $totalSpent);
           $spentPercentage = $project->prj_propcost > 0 ? round(($totalSpent / $project->prj_propcost) * 100, 1) : 0;
           
           $subheads = $finService->getSubheadBreakdown($headRecord->hed_id);
           $equipSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'equip') !== false);
           $hrSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'hr') !== false || stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'personnel') !== false);
           $miscSh = collect($subheads)->first(fn($s) => stripos(is_array($s) ? ($s['name'] ?? '') : ($s->name ?? ''), 'misc') !== false);

           $equipExp = (float)(is_array($equipSh) ? ($equipSh['expenditure'] ?? 0) : ($equipSh->expenditure ?? 0));
           $equipAlloc = (float)(is_array($equipSh) ? ($equipSh['allocation'] ?? 0) : ($equipSh->allocation ?? 0));
           $equipPct = $equipAlloc > 0 ? round(($equipExp / $equipAlloc) * 100) : ($totalSpent > 0 ? round(($equipExp / $totalSpent) * 100) : 0);

           $hrExp = (float)(is_array($hrSh) ? ($hrSh['expenditure'] ?? 0) : ($hrSh->expenditure ?? 0));
           $hrAlloc = (float)(is_array($hrSh) ? ($hrSh['allocation'] ?? 0) : ($hrSh->allocation ?? 0));
           $hrPct = $hrAlloc > 0 ? round(($hrExp / $hrAlloc) * 100) : ($totalSpent > 0 ? round(($hrExp / $totalSpent) * 100) : 0);

           $miscExp = (float)(is_array($miscSh) ? ($miscSh['expenditure'] ?? 0) : ($miscSh->expenditure ?? 0));
           $miscAlloc = (float)(is_array($miscSh) ? ($miscSh['allocation'] ?? 0) : ($miscSh->allocation ?? 0));
           $miscPct = $miscAlloc > 0 ? round(($miscExp / $miscAlloc) * 100) : ($totalSpent > 0 ? round(($miscExp / $totalSpent) * 100) : 0);

           $finData = [
               'equip' => $equipExp ?: ($totalSpent * 0.45),
               'equip_pct' => min(100, max(0, $equipPct ?: ($totalSpent > 0 ? 45 : 0))),
               'hr'    => $hrExp ?: ($totalSpent * 0.35),
               'hr_pct' => min(100, max(0, $hrPct ?: ($totalSpent > 0 ? 35 : 0))),
               'misc'  => $miscExp ?: ($totalSpent * 0.20),
               'misc_pct' => min(100, max(0, $miscPct ?: ($totalSpent > 0 ? 20 : 0))),
           ];
       }

       // --- MPR STATISTICS --
       $mprsSubmitted = PrgHistory::where('pgh_xprj_id', $id)->count();
       $startDate = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : \Carbon\Carbon::now();
       $endDate = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : \Carbon\Carbon::now();
       $totalMonths = $startDate->diffInMonths($endDate);
       if($totalMonths < 1) $totalMonths = 1;
       $mprsLeft = max(0, $totalMonths - $mprsSubmitted);

       $showProjectActualSection = false;
       $showPrjShareValue = false;
       if ($head) {
           $showProjectActualSection = !(round($head->pcc_expenditure, 2) == round($head->pcc_own_exp, 2) && round($head->others_loans_taken, 2) == 0.0);
           $showPrjShareValue = (round($head->prj_share, 2) != round($head->pcc_share, 2));
       }

       return view('SORD.project_details', compact(
           'project', 'totalSpent', 'balance', 'spentPercentage', 'finData', 
           'mprsSubmitted', 'mprsLeft', 'totalMonths', 'head',
           'showProjectActualSection', 'showPrjShareValue'
       ));
   }
}
