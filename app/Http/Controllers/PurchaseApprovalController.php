<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\PurchaseApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseApprovalController extends Controller
{
    protected $approvalService;

    public function __construct(PurchaseApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Display the approval dashboard for the current role
     */
    public function dashboard()
    {
        $user = Auth::user();
        $area = strtolower(trim($user->acc_untarea));
        
        // Define which substatus stages to show on each dashboard
        $stageMap = [
            'proc' => ['DFinance'],    // DProc sees cases at DFinance (for collaboration view)
            'fin'  => ['DFinance'],
            'rdw'  => ['MD'],
            'hqs'  => ['DDG'],
            'nrdi' => ['DG'],
        ];

        // Title Mapping
        $titleMap = [
            'proc' => 'Director Procurement Queue',
            'fin'  => 'Director Finance Queue',
            'rdw'  => 'MD Approval Portal',
            'hqs'  => 'DDG Approval Portal',
            'nrdi' => 'DG Approval Dashboard',
        ];

        $targetStages = $stageMap[$area] ?? [];
        $pageTitle = $titleMap[$area] ?? 'Purchase Scrutiny Hub';

        // 1. Pending Queue (Cases currently at this user's stage)
        $purchases = Purchase::with(['project', 'latestDecision.account', 'currentSubstatus'])
            ->atStage($targetStages)
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 2. Action Taken (Cases already processed by this user)
        $processed = Purchase::with(['project', 'latestDecision.account', 'currentSubstatus'])
            ->whereHas('decisions', function($q) use ($user) {
                $q->where('pdec_acc_id', $user->acc_id);
            })
            ->where(function($q) use ($targetStages) {
                // Exclude cases currently at this user's stage
                $q->whereDoesntHave('currentSubstatus', function($sq) use ($targetStages) {
                    $sq->whereIn('pss_stage', $targetStages);
                });
            })
            ->orderBy('pcs_id', 'desc')
            ->limit(15)
            ->get();

        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $groupedPurchases = $purchases->groupBy('pcs_unt_id');
        $detailsRouteName = 'approvals.show';

        // Metrics
        $totalVolume = $purchases->sum('pcs_price');
        $caseCount = $purchases->count();
        $processedCount = $processed->count();

        $pending = $purchases;
        $open = collect([]); // Empty for approval hub
        $closed = $processed;
        $unitPendingMap = $pending->groupBy('pcs_unt_id')->map->count();

        return view('nrdi.purchase_cases.index', compact(
            'purchases', 'processed', 'unitNameMap', 'area', 'pageTitle', 
            'groupedPurchases', 'detailsRouteName', 'totalVolume', 'caseCount', 'processedCount',
            'pending', 'open', 'closed', 'unitPendingMap'
        ));
    }

    /**
     * Show the detailed view for approval
     */
    public function show($id)
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (\Exception $e) {}

        $user = Auth::user();
        $area = strtolower(trim($user->acc_untarea));
        
        $purchase = Purchase::with(['items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account', 'currentSubstatus'])
            ->findOrFail($id);

        // Financial Intelligence (Legacy Logic)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $head = $finService->getHeadStatus($purchase->pcs_hed_id);
        $subheads = $finService->getSubheadBreakdown($purchase->pcs_hed_id);

        // Load division name
        $divisionName = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_name');

        // Check if user is authorized to approve
        $canApprove = $this->approvalService->canApprove($area, $purchase->pcs_price);

        // Titles for show view
        $titleMap = [
            'proc' => 'DProc Scrutiny Case',
            'fin'  => 'DFin Financial Review',
            'rdw'  => 'MD Approval Case',
            'hqs'  => 'DDG Approval Case',
            'nrdi' => 'DG Approval Case',
        ];
        $pageTitle = $titleMap[$area] ?? 'Purchase Case Details';

        // Recent Approved Cases for the same project/head
        $recentApproved = Purchase::withCount('items')
            ->where('pcs_hed_id', $purchase->pcs_hed_id)
            ->where(function($q) {
                $q->whereRaw('LOWER(pcs_status) = ?', ['approved'])
                  ->orWhere('pcs_status', 'Approved');
            })
            ->where('pcs_id', '!=', $purchase->pcs_id)
            ->orderBy('pcs_date', 'desc')
            ->limit(10)
            ->get();

        // Fallback: If no approved cases exist for this specific project head, show recent approved cases of any project
        if ($recentApproved->isEmpty()) {
            $recentApproved = Purchase::withCount('items')
                ->where(function($q) {
                    $q->whereRaw('LOWER(pcs_status) = ?', ['approved'])
                      ->orWhere('pcs_status', 'Approved');
                })
                ->where('pcs_id', '!=', $purchase->pcs_id)
                ->orderBy('pcs_date', 'desc')
                ->limit(10)
                ->get();
        }

        $currentAuthority = $purchase->current_stage_display ?? $this->approvalService->getStatusDisplayName($purchase->pcs_status);
        $nextAuthority = $this->approvalService->getNextAuthorityName($purchase, $area);

        return view('nrdi.purchase_cases.show', compact(
            'purchase', 'head', 'canApprove', 'area', 'pageTitle', 
            'divisionName', 'subheads', 'recentApproved', 'currentAuthority', 'nextAuthority'
        ));
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
                $targetStage = $request->target_status
            );

            $user = Auth::user();
            $area = strtolower(trim($user?->acc_untarea ?? ''));

            // Intelligent Redirection:
            // If the user processed a case in the "New" system or belongs to those roles, 
            // keep them in the New system dashboard.
            if ($area === 'proc') {
                return redirect()->route('nrdi.purchase_cases_new.procurement.index')->with('success', 'Case processed successfully!');
            } elseif ($area === 'fin') {
                return redirect()->route('nrdi.purchase_cases_new.finance.index')->with('success', 'Budget review completed!');
            }

            // Fallback for others (MD, DDG, DG)
            return redirect()->route('nrdi.purchase_cases.index')->with('success', 'Action completed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
