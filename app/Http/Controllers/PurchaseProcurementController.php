<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\PurchaseApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseProcurementController extends Controller
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
        
        // DProc only sees cases with 'Under Scrutiny' status
        $targetStatus = 'Under Scrutiny';
        $pageTitle = 'Director Procurement | Scrutiny Hub';

        $pending = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->where('pcs_status', 'Under Scrutiny')
            ->orderBy('pcs_id', 'desc')
            ->get();

        $open = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->whereHas('decisions', function($q) use ($user) {
                $q->where('pdec_acc_id', $user->acc_id);
            })
            ->whereNotIn('pcs_status', ['Under Scrutiny', 'Approved', 'Rejected']) 
            ->orderBy('pcs_id', 'desc')
            ->get();

        $closed = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->whereHas('decisions', function($q) use ($user) {
                $q->where('pdec_acc_id', $user->acc_id);
            })
            ->whereIn('pcs_status', ['Approved', 'Rejected'])
            ->orderBy('pcs_id', 'desc')
            ->limit(20)
            ->get();

        // Metrics
        $totalVolume = $pending->sum('pcs_price');
        $caseCount = $pending->count();
        $openCount = $open->count();
        $closedCount = $closed->count();

        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $detailsRouteName = 'nrdi.purchase_cases_new.procurement.show';

        return view('nrdi.purchase_cases_new.index', compact(
            'pending', 'open', 'closed', 'pageTitle', 'totalVolume', 'caseCount', 'openCount', 'closedCount', 'unitNameMap', 'detailsRouteName'
        ));
    }

    /**
     * Show the detailed view for DProc scrutiny
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
        $canApprove = $this->approvalService->canApprove('proc', $purchase->pcs_price);
        $pageTitle = 'DProc Scrutiny: ' . $purchase->pcs_title;
        $area = 'proc';

        return view('nrdi.purchase_cases_new.show', compact(
            'purchase', 'head', 'canApprove', 'area', 'pageTitle', 'divisionName'
        ));
    }
}
