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
        if (!$user) return redirect()->route('login');

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHqOrProc = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw', 'fin', 'proc', 'prc'], true);

        if ($isHqOrProc) {
            $lower = 0;
            $upper = 99999999;
        } else {
            [$lower, $upper] = $user->acc_lowers == 0
                ? [$user->acc_lowerm, $user->acc_upperm]
                : [$user->acc_lowers, $user->acc_uppers];
        }

        $pageTitle = 'Director Procurement | Collaboration Hub';
        $psTypes = app(\App\Services\PurchaseApprovalService::class)->getAssignedCaseTypes('PS');

        // 1. Pending: ONLY PS cases floated/reshared by divisions to Procurement that DProc has not finalized yet
        $pending = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
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

        // 2. Action Taken: PS cases finalized by DProc (dproc_save) that are back with Division in Draft awaiting release
        $actionTaken = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->whereIn(\Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(pcs_type, 'ps')))"), $psTypes)
            ->whereHas('decisions', function($d) {
                $d->where('pdec_action', 'dproc_save');
            })
            ->whereIn('pcs_status', ['Draft', 'Returned'])
            ->whereNotIn('pcs_id', $pending->pluck('pcs_id')->toArray())
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 3. Open: PS cases released to HQ pipeline (Finance, MD, DDG, DG, Approved, Partially Fulfilled)
        $open = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->whereIn(\Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(pcs_type, 'ps')))"), $psTypes)
            ->whereNotIn('pcs_status', ['Draft', 'Returned', 'Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->whereNotIn('pcs_id', $pending->pluck('pcs_id')->toArray())
            ->whereNotIn('pcs_id', $actionTaken->pluck('pcs_id')->toArray())
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 4. Closed: Truly finalized PS cases (Fulfilled / Completed / Cancelled / Rejected)
        $closed = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->whereIn(\Illuminate\Support\Facades\DB::raw("LOWER(TRIM(COALESCE(pcs_type, 'ps')))"), $psTypes)
            ->whereIn('pcs_status', ['Fulfilled', 'Completed', 'Cancelled', 'Rejected'])
            ->orderBy('pcs_id', 'desc')
            ->limit(50)
            ->get();

        // Metrics
        $totalVolume = $pending->sum('pcs_price');
        $caseCount = $pending->count();
        $openCount = $open->count();
        $closedCount = $closed->count();
        $actionTakenCount = $actionTaken->count();

        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $detailsRouteName = 'nrdi.purchase_cases_new.procurement.show';
        $area = 'proc';

        return view('nrdi.purchase_cases_new.index', compact(
            'pending', 'open', 'closed', 'actionTaken', 'pageTitle', 'totalVolume', 
            'caseCount', 'openCount', 'closedCount', 'actionTakenCount', 'unitNameMap', 'detailsRouteName', 'area'
        ));
    }


    /**
     * Show the detailed view for DProc scrutiny
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHqOrProc = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw', 'fin', 'proc', 'prc'], true);

        if ($isHqOrProc) {
            $lower = 0;
            $upper = 99999999;
        } else {
            [$lower, $upper] = $user->acc_lowers == 0
                ? [$user->acc_lowerm, $user->acc_upperm]
                : [$user->acc_lowers, $user->acc_uppers];
        }
        
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->findOrFail($id);

        // Financial Intelligence (Legacy Logic)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $fin = $finService->getHeadStatus($purchase->pcs_hed_id);
        $subheads = $finService->getSubheadBreakdown($purchase->pcs_hed_id);
        $head = $fin;
        
        $divisionName = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_name');
        $canApprove = $this->approvalService->canApprove('proc', (float)($purchase->pcs_price ?? 0), $purchase);
        $firms = \App\Models\Firm::orderBy('frm_name')->get();

        $pageTitle = 'DProc Scrutiny: ' . $purchase->pcs_title;
        $area = 'proc';

        // Explicitly define canEdit for view if needed before @php block
        $canEdit = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);

        return view('nrdi.purchase_cases_new.show', compact(
            'purchase', 'head', 'canApprove', 'area', 'pageTitle', 'divisionName', 'canEdit', 'firms', 'subheads'
        ));

    }


}
