<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\PurchaseApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceDashboardController extends Controller
{
    protected $approvalService;

    public function __construct(PurchaseApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display the Finance Approval Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $pageTitle = 'Director Finance | Budget Hub';

        // 1. Pending Action: Cases currently at DFinance stage
        $pending = Purchase::with(['project', 'latestDecision.account', 'currentSubstatus'])
            ->atStage('DFinance')
            ->whereNotIn('pcs_status', ['Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->orderBy('pcs_id', 'desc')
            ->get();

        $pendingIds = $pending->pluck('pcs_id')->toArray();

        // 2. Action Taken tracker (for metrics compatibility)
        $processed = Purchase::with(['project', 'latestDecision.account', 'currentSubstatus'])
            ->whereHas('decisions', function($q) use ($user) {
                $q->where('pdec_acc_id', $user->acc_id);
            })
            ->whereNotIn('pcs_id', $pendingIds)
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 3. Open: Active cases forwarded to HQ approval chain (MD, DDG, DG, Approved)
        $open = Purchase::with(['project', 'latestDecision.account', 'currentSubstatus'])
            ->whereNotIn('pcs_status', ['Draft', 'Returned', 'Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->whereNotIn('pcs_id', $pendingIds)
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 4. Closed: Finalized cases (Fulfilled / Partially Fulfilled / Completed / Cancelled / Rejected)
        $closed = Purchase::with(['project', 'latestDecision.account', 'currentSubstatus'])
            ->whereIn('pcs_status', ['Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->orderBy('pcs_id', 'desc')
            ->limit(50)
            ->get();

        // Metrics for Finance
        $totalVolume = $pending->sum('pcs_price') + $open->sum('pcs_price') + $closed->sum('pcs_price');
        $caseCount = $pending->count();
        $processedCount = $processed->count();
        
        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $detailsRouteName = 'nrdi.finance.purchase_cases.show';

        return view('nrdi.purchase_cases.index', compact(
            'pending', 'open', 'processed', 'closed', 'pageTitle', 'totalVolume', 'caseCount', 'processedCount', 'unitNameMap', 'detailsRouteName'
        ));
    }

    /**
     * Show the detailed view for Finance review
     */
    public function show($id)
    {
        $purchase = Purchase::with(['items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account', 'currentSubstatus'])
            ->findOrFail($id);

        // Fetch Live Financials
        $project = $purchase->project;
        if ($project) {
            $totalSpent = Purchase::where('pcs_hed_id', $project->prj_id)
                ->where('pcs_status', 'Approved')
                ->sum('pcs_price');
            $project->hed_balance = ($project->prj_aprvcost ?? 0) - $totalSpent;
        }
        $head = $project;

        $divisionName = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_name');
        $canApprove = $this->approvalService->canApprove('fin', (float)($purchase->pcs_price ?? 0), $purchase);
        $pageTitle = 'DFin Review: ' . $purchase->pcs_title;
        $area = 'fin';

        return view('nrdi.purchase_cases.show', compact(
            'purchase', 'head', 'canApprove', 'area', 'pageTitle', 'divisionName'
        ));
    }
}
