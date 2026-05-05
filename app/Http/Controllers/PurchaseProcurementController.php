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
        $lower = $user->acc_lowerm;
        $upper = $user->acc_upperm;

        
        $pageTitle = 'Director Procurement | Collaboration Hub';

        // 1. Pending: Draft cases from divisions within range that DProc hasn't added quotes to yet
        $pending = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->whereIn('pcs_status', ['Draft', 'Returned'])
            ->whereDoesntHave('decisions', function($q) {
                $q->where('pdec_action', 'dproc_save');
            })
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 2. Open: Cases where DProc HAS collaborated, but are not yet finalized
        $open = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->whereNotIn('pcs_status', ['Approved', 'Rejected', 'Cancelled'])
            ->whereHas('decisions', function($q) {
                $q->where('pdec_action', 'dproc_save');
            })
            ->orderBy('pcs_id', 'desc')
            ->get();


        // 3. Closed: Finalized cases processed by this user
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
        $area = 'proc';

        return view('nrdi.purchase_cases_new.index', compact(
            'pending', 'open', 'closed', 'pageTitle', 'totalVolume', 'caseCount', 'openCount', 'closedCount', 'unitNameMap', 'detailsRouteName', 'area'
        ));
    }


    /**
     * Show the detailed view for DProc scrutiny
     */
    public function show($id)
    {
        $user = Auth::user();
        $lower = $user->acc_lowerm;
        $upper = $user->acc_upperm;
        
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account'])

            ->whereBetween('pcs_unt_id', [$lower, $upper])
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
        $firms = \App\Models\Firm::orderBy('frm_name')->get();

        $pageTitle = 'DProc Scrutiny: ' . $purchase->pcs_title;
        $area = 'proc';

        // Explicitly define canEdit for view if needed before @php block
        $canEdit = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);

        return view('nrdi.purchase_cases_new.show', compact(
            'purchase', 'head', 'canApprove', 'area', 'pageTitle', 'divisionName', 'canEdit', 'firms'
        ));
    }


}
