<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\PurchaseApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseCaseController extends Controller
{
    protected $approvalService;

    public function __construct(PurchaseApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display the approval dashboard for the current role
     */
    public function index()
    {
        $user = Auth::user();
        $area = strtolower(trim($user->acc_untarea));
        
        // Define which statuses to show on each dashboard
        $statusMap = [
            'proc' => ['Under Scrutiny', 'Draft', 'Returned'],

            'fin'  => ['With DFinance'],
            'rdw'  => ['With MD'],
            'hqs'  => ['With DDG'],
            'nrdi' => ['With DG'],
        ];

        // Title Mapping
        $titleMap = [
            'proc' => 'Director Procurement Queue',
            'fin'  => 'Director Finance Queue',
            'rdw'  => 'MD Approval Portal',
            'hqs'  => 'DDG Approval Portal',
            'nrdi' => 'DG Approval Dashboard',
        ];

        $targetStatuses = $statusMap[$area] ?? [];
        $pageTitle = $titleMap[$area] ?? 'Purchase Scrutiny Hub';

        // 1. Pending Queue (Cases currently at this user's level)
        $pendingQuery = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->whereIn('pcs_status', $targetStatuses);

        // For DProc, also filter by their jurisdiction (Master Unit Range)
        if ($area === 'proc') {
            $lower = $user->acc_lowerm;
            $upper = $user->acc_upperm;
            $pendingQuery->whereBetween('pcs_unt_id', [$lower, $upper]);
        }


        $pending = $pendingQuery->orderBy('pcs_id', 'desc')->get();


        // 2. Action Taken (Cases already processed by this user)
        $processed = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->whereHas('decisions', function($q) use ($user) {
                $q->where('pdec_acc_id', $user->acc_id);
            })
            ->whereNotIn('pcs_status', $targetStatuses) 
            ->orderBy('pcs_id', 'desc')
            ->get();

        // Split processed into Open and Closed
        if ($area === 'proc') {
            // For DProc, Open means participated but not yet final
            $open = Purchase::with(['unit', 'project', 'latestDecision.account'])
                ->whereBetween('pcs_unt_id', [$user->acc_lowerm, $user->acc_upperm])
                ->whereNotIn('pcs_status', ['Approved', 'Rejected', 'Cancelled', 'Draft', 'Returned', 'Under Scrutiny'])
                ->whereHas('decisions', function($q) { $q->where('pdec_action', 'dproc_save'); })
                ->orderBy('pcs_id', 'desc')->get();
            
            $closed = Purchase::with(['unit', 'project', 'latestDecision.account'])
                ->whereHas('decisions', function($q) use ($user) { $q->where('pdec_acc_id', $user->acc_id); })
                ->whereIn('pcs_status', ['Approved', 'Rejected', 'Cancelled'])
                ->orderBy('pcs_id', 'desc')->get();
        } else {
            $open = $processed->whereNotIn('pcs_status', ['Approved', 'Rejected', 'Cancelled']);
            $closed = $processed->whereIn('pcs_status', ['Approved', 'Rejected', 'Cancelled']);
        }


        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $detailsRouteName = 'nrdi.purchase_cases_new.show';

        // Metrics
        $totalVolume = $pending->sum('pcs_price');
        $caseCount = $pending->count();
        $openCount = $open->count();
        $closedCount = $closed->count();

        return view('nrdi.purchase_cases_new.index', compact(
            'pending', 'open', 'closed', 'unitNameMap', 'area', 'pageTitle', 
            'detailsRouteName', 'totalVolume', 'caseCount', 'openCount', 'closedCount'
        ));
    }

    /**
     * Show the detailed view for approval
     */
    public function show($id)
    {
        $user = Auth::user();
        $area = strtolower(trim($user->acc_untarea));
        
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account'])
            ->findOrFail($id);

        // Fetch Live Financials from cen.heads
        $project = $purchase->project;
        if ($project) {
            $totalSpent = Purchase::where('pcs_hed_id', $project->prj_id)
                ->where('pcs_status', 'Approved')
                ->sum('pcs_price');
            $project->hed_balance = ($project->prj_aprvcost ?? 0) - $totalSpent;
        }
        $head = $project;

        // Load division name
        $divisionName = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_name');

        // Check if user is authorized to approve
        $canApprove = $this->approvalService->canApprove($area, $purchase->pcs_price);
        $firms = \App\Models\Firm::orderBy('frm_name')->get();


        // Titles for show view
        $titleMap = [
            'proc' => 'DProc Scrutiny Case',
            'fin'  => 'DFin Financial Review',
            'rdw'  => 'MD Approval Case',
            'hqs'  => 'DDG Approval Case',
            'nrdi' => 'DG Approval Case',
        ];
        $pageTitle = $titleMap[$area] ?? 'Purchase Case Details';

        $canEdit = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);

        return view('nrdi.purchase_cases_new.show', compact('purchase', 'head', 'canApprove', 'area', 'pageTitle', 'divisionName', 'canEdit', 'firms'));
    }



    /**
     * Process decision (Forward, Return, Approve, Reject)
     */
    public function action(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:forward,forward_negative,return,approve,reject',
            'remarks' => 'nullable|string',
            'target_status' => 'nullable|string',
        ]);

        $purchase = Purchase::findOrFail($id);
        $remarks = $request->remarks ?: 'No remarks provided.';
        
        try {
            $this->approvalService->processDecision(
                $case = $purchase, 
                $action = $request->action, 
                $remarks = $remarks, 
                $targetStatus = $request->target_status
            );
            return redirect()->route('nrdi.purchase_cases_new.index')->with('success', 'Action completed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
