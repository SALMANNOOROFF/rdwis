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
        if (in_array($area, ['proc', 'prc'], true)) $area = 'proc';
        
        // Define which substatus stages to show on each dashboard
        $stageMap = [
            'proc' => null,            // DProc uses pcs_status-based queries, not stages
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

        $procTypes = [
            'ps', 'mat', 'material', 'eqp', 'equipment', 'cons', 'consultancy', 'serv', 'services'
        ];
        $excludedTypes = [
            'tada', 't.a/d.a', 'ta/da', 'civ', 'civil', 'book', 'books', 
            'stat', 'stationery', 'lic', 'license', 'licen', 'net', 'internet', 
            'pub', 'publishing', 'tran', 'transport', 'trn', 'training', 'pt'
        ];

        // 1. Pending Queue
        $pendingQuery = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus']);

        if ($area === 'proc') {
            // DProc: ONLY material / equipment (PS) cases floated by division where DProc has not yet saved quotes/remarks
            $lower = $user->acc_lowerm;
            $upper = $user->acc_upperm;
            $pendingQuery->whereBetween('pcs_unt_id', [$lower, $upper])
                         ->where(function($q) use ($procTypes, $excludedTypes) {
                             $q->whereIn(DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'ps\')))'), $procTypes)
                               ->whereNotIn(DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'\')))'), $excludedTypes);
                         })
                         ->where(function($q) {
                             $q->where(function($sub) {
                                 $sub->whereIn('pcs_status', ['Draft', 'Returned'])
                                     ->whereHas('decisions', function($d) {
                                         $d->where('pdec_action', 'float_to_proc');
                                     })
                                     ->whereDoesntHave('decisions', function($d) {
                                         $d->where('pdec_action', 'dproc_save');
                                     });
                             })->orWhere('pcs_status', 'Under Scrutiny');
                         });
        } else {
            // All other roles: query by substatus stage
            $pendingQuery->atStage($targetStages);
        }

        $pending = $pendingQuery->orderBy('pcs_id', 'desc')->get();

        // 2. Action Taken (Cases already processed by this user)
        $processedQuery = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
            ->where(function($q) use ($user, $area) {
                $q->whereHas('decisions', function($d) use ($user) {
                    $d->where('pdec_acc_id', $user->acc_id);
                });
                if ($area === 'proc') {
                    $q->orWhereHas('decisions', function($d) {
                        $d->where('pdec_action', 'dproc_save');
                    });
                }
            });

        if ($area === 'proc') {
            $processedQuery->where(function($q) use ($procTypes, $excludedTypes) {
                $q->whereIn(DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'ps\')))'), $procTypes)
                  ->whereNotIn(DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'\')))'), $excludedTypes);
            });
        }

        $processed = $processedQuery->orderBy('pcs_id', 'desc')->get();

        // Filter out cases currently pending at this user's stage
        $pendingIds = $pending->pluck('pcs_id')->toArray();
        $processed = $processed->whereNotIn('pcs_id', $pendingIds);

        // Split processed into Open and Closed
        if ($area === 'proc') {
            // For DProc, Open means participated but not yet final
            $open = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
                ->whereBetween('pcs_unt_id', [$user->acc_lowerm, $user->acc_upperm])
                ->where(function($q) use ($procTypes, $excludedTypes) {
                    $q->whereIn(DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'ps\')))'), $procTypes)
                      ->whereNotIn(DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'\')))'), $excludedTypes);
                })
                ->whereNotIn('pcs_status', ['Approved', 'Rejected', 'Cancelled', 'Draft', 'Returned', 'Under Scrutiny'])
                ->whereHas('decisions', function($q) { $q->where('pdec_action', 'dproc_save'); })
                ->orderBy('pcs_id', 'desc')->get();
            
            $closed = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
                ->where(function($q) use ($procTypes, $excludedTypes) {
                    $q->whereIn(DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'ps\')))'), $procTypes)
                      ->whereNotIn(DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'\')))'), $excludedTypes);
                })
                ->whereHas('decisions', function($q) use ($user) { $q->where('pdec_acc_id', $user->acc_id); })
                ->whereIn('pcs_status', ['Approved', 'Rejected', 'Cancelled'])
                ->orderBy('pcs_id', 'desc')->get();
        } else {
            $open = $processed->whereNotIn('pcs_status', ['Approved', 'Rejected', 'Cancelled']);
            $closed = $processed->whereIn('pcs_status', ['Approved', 'Rejected', 'Cancelled']);
        }

        $actionTaken = $processed;
        $actionTakenCount = $processed->count();

        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $detailsRouteName = 'nrdi.purchase_cases_new.show';

        // Metrics
        $totalVolume = $pending->sum('pcs_price');
        $caseCount = $pending->count();
        $openCount = $open->count();
        $closedCount = $closed->count();

        return view('nrdi.purchase_cases_new.index', compact(
            'pending', 'open', 'closed', 'actionTaken', 'unitNameMap', 'area', 'pageTitle', 
            'detailsRouteName', 'totalVolume', 'caseCount', 'openCount', 'closedCount', 'actionTakenCount'
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
        if (in_array($area, ['proc', 'prc'], true)) $area = 'proc';
        
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account', 'currentSubstatus'])
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

        // Financial Intelligence (Legacy Logic)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $head = $finService->getHeadStatus($purchase->pcs_hed_id);
        $subheads = $finService->getSubheadBreakdown($purchase->pcs_hed_id);

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

        $canEdit = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);

        return view('nrdi.purchase_cases_new.show', compact(
            'purchase', 'head', 'canApprove', 'area', 'pageTitle', 
            'divisionName', 'canEdit', 'firms', 'subheads', 'currentAuthority', 'nextAuthority', 'recentApproved'
        ));
    }



    /**
     * Process decision (Forward, Return, Approve, Reject)
     */
    public function action(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:forward,forward_negative,return,approve,reject,save_draft,dproc_save',
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
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Remarks saved successfully!']);
            }

            return redirect()->route('nrdi.purchase_cases_new.index')->with('success', 'Action completed successfully!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
