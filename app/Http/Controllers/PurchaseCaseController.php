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
        
        $isDivision = in_array($area, ['prj', 'rdwprj', 'division', 'initiation'], true) || (method_exists($user, 'isDivision') && $user->isDivision());

        if ($isDivision) {
            $pageTitle = 'Division Purchase Cases Hub';
            [$lower, $upper] = $user->acc_lowers == 0
                ? [$user->acc_lowerm, $user->acc_upperm]
                : [$user->acc_lowers, $user->acc_uppers];
            $psTypes = app(\App\Services\PurchaseApprovalService::class)->getAssignedCaseTypes('PS');

            if ($area === 'prj') {
                // Division Initiator View
                // 1. Pending: Action Required by Division
                // - Cases Returned to Division
                // - Non-PS draft cases (directly releasable)
                // - PS draft cases: not floated yet, OR DProc has saved (dproc_save) and returned to Division
                $pending = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus', 'decisions'])
                    ->whereBetween('pcs_unt_id', [$lower, $upper])
                    ->where(function($q) use ($psTypes) {
                        $q->where('pcs_status', 'Returned')
                          ->orWhere(function($sub) use ($psTypes) {
                              $sub->where('pcs_status', 'Draft')
                                  ->where(function($s2) use ($psTypes) {
                                      $s2->whereNotIn(\Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(pcs_type, 'ps')))"), $psTypes)
                                         ->orWhereDoesntHave('decisions', function($d) {
                                             $d->whereIn('pdec_action', ['float_to_proc', 'reshare_to_proc']);
                                         })
                                         ->orWhereHas('decisions', function($d) {
                                             $d->where('pdec_action', 'dproc_save');
                                         });
                                  });
                          });
                    })
                    ->whereNotIn('pcs_status', ['Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                    ->orderBy('pcs_id', 'desc')
                    ->get();
            } else {
                // 1. Pending (Action Required):
                // - Returned cases
                // - Non-PS draft cases
                // - PS draft cases: not floated yet, OR DProc has saved (dproc_save) and returned to Division
                $pending = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus', 'decisions'])
                    ->whereBetween('pcs_unt_id', [$lower, $upper])
                    ->where(function($q) {
                        $q->where('pcs_status', 'Returned')
                          ->orWhere(function($sub) {
                              $sub->where('pcs_status', 'Draft')
                                  ->where(function($s2) {
                                      $s2->whereRaw("LOWER(TRIM(COALESCE(pcs_type, 'ps'))) != 'ps'")
                                         ->orWhereDoesntHave('decisions', function($d) {
                                             $d->whereIn('pdec_action', ['float_to_proc', 'reshare_to_proc']);
                                         })
                                         ->orWhereHas('decisions', function($d) {
                                             $d->where('pdec_action', 'dproc_save');
                                         });
                                  });
                          });
                    })
                    ->whereNotIn('pcs_status', ['Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                    ->orderBy('pcs_id', 'desc')
                    ->get();
            }

            $pendingIds = $pending->pluck('pcs_id')->toArray();

            // 2. Open: Active cases currently in pipeline (waiting with DProc or moving through HQ)
            $open = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus', 'decisions'])
                ->whereBetween('pcs_unt_id', [$lower, $upper])
                ->whereNotIn('pcs_status', ['Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->whereNotIn('pcs_id', $pendingIds)
                ->orderBy('pcs_id', 'desc')
                ->get();

            // 3. Closed: Truly finalized cases
            $closed = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
                ->whereBetween('pcs_unt_id', [$lower, $upper])
                ->whereIn('pcs_status', ['Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->orderBy('pcs_id', 'desc')
                ->get();

            $actionTaken = $open;
            $actionTakenCount = $open->count();

        } elseif ($area === 'proc') {
            $lower = 0;
            $upper = 99999999;
            $psTypes = app(\App\Services\PurchaseApprovalService::class)->getAssignedCaseTypes('PS');
            $pending = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus', 'decisions'])
                ->whereBetween('pcs_unt_id', [$lower, $upper])
                ->whereIn(\Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(pcs_type, 'ps')))"), $psTypes)
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

            $pendingIds = $pending->pluck('pcs_id')->toArray();

            // 1. Action Taken: PS cases finalized by DProc (dproc_save) still at Division in Draft
            $actionTaken = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
                ->whereBetween('pcs_unt_id', [0, 99999999])
                ->whereIn(\Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(pcs_type, 'ps')))"), $psTypes)
                ->whereHas('decisions', function($d) {
                    $d->where('pdec_action', 'dproc_save');
                })
                ->whereIn('pcs_status', ['Draft', 'Returned'])
                ->whereNotIn('pcs_id', $pendingIds)
                ->orderBy('pcs_id', 'desc')->get();

            // 2. Open: Active PS cases released to HQ pipeline (Finance, MD, DDG, DG, Approved, Partially Fulfilled)
            $open = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
                ->whereBetween('pcs_unt_id', [0, 99999999])
                ->whereIn(\Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(pcs_type, 'ps')))"), $psTypes)
                ->whereNotIn('pcs_status', ['Draft', 'Returned', 'Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->whereNotIn('pcs_id', $pendingIds)
                ->whereNotIn('pcs_id', $actionTaken->pluck('pcs_id')->toArray())
                ->orderBy('pcs_id', 'desc')->get();
            
            // 3. Closed: Truly finalized PS cases
            $closed = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
                ->whereBetween('pcs_unt_id', [0, 99999999])
                ->whereIn(\Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(pcs_type, 'ps')))"), $psTypes)
                ->whereIn('pcs_status', ['Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->orderBy('pcs_id', 'desc')->get();

            $actionTakenCount = $actionTaken->count();
        } else {
            // Other HQ Authorities (DFinance, MD, DDG, DG)
            $stageMap = [
                'fin'  => ['DFinance'],
                'rdw'  => ['MD'],
                'hqs'  => ['DDG'],
                'nrdi' => ['DG'],
            ];
            $titleMap = [
                'fin'  => 'Director Finance Queue',
                'rdw'  => 'MD Approval Portal',
                'hqs'  => 'DDG Approval Portal',
                'nrdi' => 'DG Approval Dashboard',
            ];
            $targetStages = $stageMap[$area] ?? [];
            $pageTitle = $titleMap[$area] ?? 'Purchase Scrutiny Hub';

            $pending = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
                ->atStage($targetStages)
                ->whereNotIn('pcs_status', ['Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->orderBy('pcs_id', 'desc')
                ->get();

            $pendingIds = $pending->pluck('pcs_id')->toArray();

            // 1. Action Taken: Cases specifically processed by this user
            $actionTaken = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
                ->whereHas('decisions', function($d) use ($user) {
                    $d->where('pdec_acc_id', $user->acc_id);
                })
                ->whereNotIn('pcs_id', $pendingIds)
                ->orderBy('pcs_id', 'desc')
                ->get();

            // 2. Open: Active pipeline cases (forwarded to MD, DDG, DG, Approved)
            $open = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
                ->whereNotIn('pcs_status', ['Draft', 'Returned', 'Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
                ->whereNotIn('pcs_id', $pendingIds)
                ->orderBy('pcs_id', 'desc')
                ->get();

            // 3. Closed: Finalized cases
            $closed = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
                ->whereIn('pcs_status', ['Fulfilled', 'Partially Fulfilled', 'Completed', 'Cancelled', 'Not Approved', 'Rejected'])
                ->orderBy('pcs_id', 'desc')
                ->get();

            $actionTakenCount = $actionTaken->count();
        }

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
        $canApprove = $this->approvalService->canApprove($area, (float)($purchase->pcs_price ?? 0), $purchase);
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
            'action' => 'required|in:forward,forward_negative,return,approve,reject,not_approved,cancel,save_draft,dproc_save,float_to_proc,reshare_to_proc',
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
