<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\PurchaseApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcurementDashboardController extends Controller
{
    protected $approvalService;

    public function __construct(PurchaseApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display the Procurement Scrutiny Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHqOrProc = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw', 'fin', 'proc', 'prc'], true);

        if ($isHqOrProc) {
            $lower = 0;
            $upper = 99999999;
        } else {
            [$lower, $upper] = $user->acc_lowers == 0
                ? [$user->acc_lowerm, $user->acc_upperm]
                : [$user->acc_lowers, $user->acc_uppers];
        }

        $pageTitle = 'Procurement Command Dashboard';

        // 1. Pending Action: ONLY PS cases floated/reshared by divisions to Procurement that DProc has not finalized yet
        $pending = Purchase::with(['project', 'items', 'quotes.firm', 'latestDecision.account', 'currentSubstatus'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->whereRaw("LOWER(TRIM(COALESCE(pcs_type, 'ps'))) = 'ps'")
            ->where(function($q) {
                $q->where(function($sub) {
                    $sub->whereHas('decisions', function($d) {
                        $d->whereIn('pdec_action', ['float_to_proc', 'reshare_to_proc']);
                    })->whereDoesntHave('decisions', function($d) {
                        $d->where('pdec_action', 'dproc_save');
                    });
                })
                ->orWhere('pcs_status', 'Under Scrutiny')
                ->orWhereHas('currentSubstatus', function($s) {
                    $s->where('pss_stage', 'DProc');
                });
            })
            ->whereNotIn('pcs_status', ['Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 2. Open / Pipeline: Active PS cases released to HQ pipeline (Finance, MD, DDG, DG, Approved, Partially Fulfilled)
        $open = Purchase::with(['project', 'items', 'quotes.firm', 'latestDecision.account', 'currentSubstatus'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->whereRaw("LOWER(TRIM(COALESCE(pcs_type, 'ps'))) = 'ps'")
            ->whereNotIn('pcs_status', ['Draft', 'Returned', 'Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->whereNotIn('pcs_id', $pending->pluck('pcs_id')->toArray())
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 3. Close: Cases that are truly closed (PS only: Fulfilled / Completed / Cancelled / Rejected)
        $closed = Purchase::with(['project', 'items', 'quotes.firm', 'latestDecision.account', 'currentSubstatus'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->whereRaw("LOWER(TRIM(COALESCE(pcs_type, 'ps'))) = 'ps'")
            ->whereIn('pcs_status', ['Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 4. Registered Suppliers / Firms
        $firms = DB::table('frm.firmz')
            ->where('frm_id', '>', 0)
            ->where('frm_name', 'not like', '%< Select%')
            ->where('frm_name', 'not like', '%<Select%')
            ->orderBy('frm_name')
            ->get(['frm_id', 'frm_name', 'frm_type', 'frm_entity', 'frm_ntn', 'frm_gst', 'frm_black']);

        // Metrics & KPI calculations
        $pendingCount = $pending->count();
        $pendingVolume = $pending->sum('pcs_price');
        $openCount = $open->count();
        $openVolume = $open->sum('pcs_price');
        $closedCount = $closed->count();
        $closedVolume = $closed->sum('pcs_price');
        $totalVolume = $pendingVolume + $openVolume + $closedVolume;
        $totalCases = $pendingCount + $openCount + $closedCount;
        $firmsCount = $firms->count();

        // Division-wise Procurement Analytics (PS Cases only)
        $divisionStats = DB::table('pur.purcases as pc')
            ->join('cen.units as u', 'pc.pcs_unt_id', '=', 'u.unt_id')
            ->whereRaw("LOWER(TRIM(COALESCE(pc.pcs_type, 'ps'))) = 'ps'")
            ->select(
                'u.unt_namesh as division',
                DB::raw('COUNT(pc.pcs_id) as total_cases'),
                DB::raw('COALESCE(SUM(pc.pcs_price), 0) as total_volume'),
                DB::raw('SUM(CASE WHEN pc.pcs_status = \'Approved\' THEN 1 ELSE 0 END) as approved_cases')
            )
            ->groupBy('u.unt_namesh')
            ->orderBy('total_volume', 'desc')
            ->get();

        // Status Distribution (PS Cases only)
        $statusBreakdown = DB::table('pur.purcases')
            ->whereRaw("LOWER(TRIM(COALESCE(pcs_type, 'ps'))) = 'ps'")
            ->select('pcs_status', DB::raw('COUNT(*) as count'), DB::raw('COALESCE(SUM(pcs_price), 0) as volume'))
            ->groupBy('pcs_status')
            ->orderBy('count', 'desc')
            ->get();

        // Monthly Trend (Last 6 Months, PS Cases only)
        $monthlyTrend = DB::table('pur.purcases')
            ->whereRaw("LOWER(TRIM(COALESCE(pcs_type, 'ps'))) = 'ps'")
            ->whereNotNull('pcs_date')
            ->select(
                DB::raw("TO_CHAR(pcs_date, 'Mon YYYY') as month_label"),
                DB::raw("DATE_TRUNC('month', pcs_date) as sort_month"),
                DB::raw('COUNT(*) as count'),
                DB::raw('COALESCE(SUM(pcs_price), 0) as volume')
            )
            ->groupBy(DB::raw("TO_CHAR(pcs_date, 'Mon YYYY')"), DB::raw("DATE_TRUNC('month', pcs_date)"))
            ->orderBy('sort_month', 'asc')
            ->limit(12)
            ->get();

        // Top 5 Suppliers by Approved Cases Volume
        $topSuppliers = DB::table('pur.purcases as pc')
            ->join('frm.firmz as f', 'pc.pcs_frm_id', '=', 'f.frm_id')
            ->where('pc.pcs_status', 'Approved')
            ->select('f.frm_name', DB::raw('COUNT(pc.pcs_id) as cases_count'), DB::raw('COALESCE(SUM(pc.pcs_price), 0) as total_awarded'))
            ->groupBy('f.frm_name')
            ->orderBy('total_awarded', 'desc')
            ->limit(5)
            ->get();

        $units = DB::table('cen.units')->where('unt_type', 'Division')->orderBy('unt_name')->get(['unt_id', 'unt_name', 'unt_namesh']);
        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $unitPendingMap = $pending->groupBy('pcs_unt_id')->map->count();

        $detailsRouteName = 'nrdi.purchase_cases_new.procurement.show';

        return view('nrdi.purchase_cases.procurement_index', compact(
            'pending', 'open', 'closed', 'firms', 'pageTitle', 
            'pendingCount', 'pendingVolume', 'openCount', 'openVolume', 
            'closedCount', 'closedVolume', 'totalVolume', 'totalCases', 'firmsCount',
            'units', 'unitNameMap', 'unitPendingMap', 'detailsRouteName',
            'divisionStats', 'statusBreakdown', 'monthlyTrend', 'topSuppliers'
        ));
    }

    /**
     * Directly close/approve a case from the dashboard.
     * FIXED: Delegates to PurchaseApprovalService to ensure commitment creation + substatus handling.
     */
    public function closeCase(Request $request, $id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $this->approvalService->processDecision(
                $purchase,
                'approve',
                $request->input('remarks', 'Closed/Approved from Procurement Dashboard.')
            );
            
            return redirect()->back()->with('success', 'Case marked as Closed (Approved) successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error closing case: ' . $e->getMessage());
        }
    }

    /**
     * Show the detailed view for DProc scrutiny
     */
    public function show($id)
    {
        $purchase = Purchase::with(['items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account', 'currentSubstatus'])
            ->findOrFail($id);

        // Fetch Live Financials using FinancialIntelligenceService
        $project = $purchase->project;
        if ($project) {
            $finService = app(\App\Services\FinancialIntelligenceService::class);
            $headRecord = DB::table('cen.heads')->where('hed_prj_id', $project->prj_id)->first();
            if ($headRecord) {
                $headStatus = $finService->getHeadStatus($headRecord->hed_id);
                $project->hed_balance = $headStatus->available ?? 0;
            } else {
                $project->hed_balance = ($project->prj_aprvcost ?? 0) - 0;
            }
        }
        $head = $project;

        $divisionName = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_name');
        $canApprove = $this->approvalService->canApprove('proc', (float)($purchase->pcs_price ?? 0), $purchase);
        $pageTitle = 'DProc Scrutiny: ' . $purchase->pcs_title;
        $area = 'proc';

        // Recent 8 approved cases for sidebar
        $recentApproved = Purchase::where('pcs_status', 'Approved')
            ->withCount('items')
            ->orderByDesc('pcs_date')
            ->limit(8)
            ->get(['pcs_id', 'pcs_title', 'pcs_price', 'pcs_date', 'pcs_type']);

        return view('nrdi.purchase_cases.show', compact(
            'purchase', 'head', 'canApprove', 'area', 'pageTitle', 'divisionName', 'recentApproved'
        ));
    }
}
