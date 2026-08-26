<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\PurchaseApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseFinanceController extends Controller
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

        // 1. Pending: Cases currently at DFinance stage
        $pending = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
            ->atStage('DFinance')
            ->whereNotIn('pcs_status', ['Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->orderBy('pcs_id', 'desc')
            ->get();

        $pendingIds = $pending->pluck('pcs_id')->toArray();

        // 2. Action Taken (Cases already processed by this user)
        $actionTaken = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
            ->whereHas('decisions', function($q) use ($user) {
                $q->where('pdec_acc_id', $user->acc_id);
            })
            ->whereNotIn('pcs_id', $pendingIds)
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 3. Open: Active cases forwarded to HQ approval chain (MD, DDG, DG, Approved)
        $open = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
            ->whereNotIn('pcs_status', ['Draft', 'Returned', 'Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->whereNotIn('pcs_id', $pendingIds)
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 4. Closed: Finalized cases (Fulfilled / Partially Fulfilled / Completed / Cancelled / Rejected)
        $closed = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
            ->whereIn('pcs_status', ['Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->orderBy('pcs_id', 'desc')
            ->limit(50)
            ->get();

        // Metrics for Finance
        $totalVolume = $pending->sum('pcs_price');
        $caseCount = $pending->count();
        $openCount = $open->count();
        $closedCount = $closed->count();
        $actionTakenCount = $actionTaken->count();
        
        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $detailsRouteName = 'nrdi.purchase_cases_new.finance.show';
        $area = 'fin';

        return view('nrdi.purchase_cases_new.index', compact(
            'pending', 'open', 'closed', 'actionTaken', 'pageTitle', 'totalVolume', 'caseCount', 
            'openCount', 'closedCount', 'actionTakenCount', 'unitNameMap', 'detailsRouteName', 'area'
        ));
    }

    /**
     * Show the detailed view for Finance review
     */
    public function show($id)
    {
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account', 'currentSubstatus'])
            ->findOrFail($id);

        // Financial Intelligence (Legacy Logic)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $fin = $finService->getHeadStatus($purchase->pcs_hed_id);
        $subheads = $finService->getSubheadBreakdown($purchase->pcs_hed_id);
        $head = $fin;
        
        $divisionName = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_name');
        $canApprove = $this->approvalService->canApprove('fin', (float)($purchase->pcs_price ?? 0), $purchase);
        $pageTitle = 'DFin Review: ' . $purchase->pcs_title;
        $area = 'fin';

        $firms = \App\Models\Firm::orderBy('frm_name')->get();
        $canEdit = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);

        return view('nrdi.purchase_cases_new.show', compact(
            'purchase', 'head', 'canApprove', 'area', 'pageTitle', 'divisionName', 'firms', 'canEdit', 'subheads'
        ));

    }

}
