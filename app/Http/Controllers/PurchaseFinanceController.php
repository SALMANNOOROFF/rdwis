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
        $targetStatus = 'With DFinance';
        $pageTitle = 'Director Finance | Budget Hub';

        $pending = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->where('pcs_status', $targetStatus)
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 2. Action Taken (Cases already processed by this user)
        $processed = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->whereHas('decisions', function($q) use ($user) {
                $q->where('pdec_acc_id', $user->acc_id);
            })
            ->where('pcs_status', '!=', $targetStatus) 
            ->orderBy('pcs_id', 'desc')
            ->get();

        // Split processed into Open and Closed
        $open = $processed->whereNotIn('pcs_status', ['Approved', 'Rejected', 'Cancelled']);
        $closed = $processed->whereIn('pcs_status', ['Approved', 'Rejected', 'Cancelled']);

        // Metrics for Finance
        $totalVolume = $pending->sum('pcs_price');
        $caseCount = $pending->count();
        $openCount = $open->count();
        $closedCount = $closed->count();
        
        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $detailsRouteName = 'nrdi.purchase_cases_new.finance.show';

        return view('nrdi.purchase_cases_new.index', compact(
            'pending', 'open', 'closed', 'pageTitle', 'totalVolume', 'caseCount', 'openCount', 'closedCount', 'unitNameMap', 'detailsRouteName'
        ));
    }

    /**
     * Show the detailed view for Finance review
     */
    public function show($id)
    {
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account'])
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
        $canApprove = $this->approvalService->canApprove('fin', $purchase->pcs_price);
        $pageTitle = 'DFin Review: ' . $purchase->pcs_title;
        $area = 'fin';

        $firms = \App\Models\Firm::orderBy('frm_name')->get();
        $canEdit = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);

        return view('nrdi.purchase_cases_new.show', compact(
            'purchase', 'head', 'canApprove', 'area', 'pageTitle', 'divisionName', 'firms', 'canEdit'
        ));
    }

}
