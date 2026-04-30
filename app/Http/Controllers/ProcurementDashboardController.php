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
        $pageTitle = 'Procurement Dashboard';
        
        // DProc sees cases based on their current status lifecycle
        
        // 1. Pending Action: Under Scrutiny OR Returned
        $pending = Purchase::with(['project', 'latestDecision.account'])
            ->whereIn('pcs_status', ['Under Scrutiny', 'Returned'])
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 2. Open: Active cases with others (DFinance, MD, DDG, DG)
        $open = Purchase::with(['project', 'latestDecision.account'])
            ->whereIn('pcs_status', ['With DFinance', 'With MD', 'With DDG', 'With DG'])
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 3. Close: Cases that are already Approved
        $closed = Purchase::with(['project', 'latestDecision.account'])
            ->where('pcs_status', 'Approved')
            ->orderBy('pcs_id', 'desc')
            ->get();

        // Metrics for compatibility if needed
        $caseCount = $pending->count();
        $processedCount = $open->count() + $closed->count();
        $totalVolume = $pending->sum('pcs_price') + $open->sum('pcs_price') + $closed->sum('pcs_price');

        // Division filters with Pending counts
        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $unitPendingMap = $pending->groupBy('pcs_unt_id')->map->count();

        $detailsRouteName = 'nrdi.procurement.purchase_cases.show';

        return view('nrdi.purchase_cases.index', compact(
            'pending', 'open', 'closed', 'pageTitle', 'totalVolume', 'caseCount', 'processedCount', 'unitNameMap', 'unitPendingMap', 'detailsRouteName'
        ));
    }

    /**
     * Directly close/approve a case from the dashboard
     */
    public function closeCase($id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $purchase->pcs_status = 'Approved';
            $purchase->save();
            
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
        $purchase = Purchase::with(['items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account'])
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
