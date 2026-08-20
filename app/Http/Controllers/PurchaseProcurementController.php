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
        
        $procTypes = [
            'ps', 'mat', 'material', 'eqp', 'equipment', 'cons', 'consultancy', 'serv', 'services'
        ];
        $excludedTypes = [
            'tada', 't.a/d.a', 'ta/da', 'civ', 'civil', 'book', 'books', 
            'stat', 'stationery', 'lic', 'license', 'licen', 'net', 'internet', 
            'pub', 'publishing', 'tran', 'transport', 'trn', 'training', 'pt'
        ];

        // 1. Pending: Cases floated by divisions to Procurement that DProc has not saved quotes/remarks to yet
        $pending = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->where(function($q) use ($procTypes, $excludedTypes) {
                $q->whereIn(\DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'ps\')))'), $procTypes)
                  ->whereNotIn(\DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'\')))'), $excludedTypes);
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
            })
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 2. Open: Cases where DProc HAS collaborated, or cases currently under HQ approval
        $open = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->where(function($q) use ($procTypes, $excludedTypes) {
                $q->whereIn(\DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'ps\')))'), $procTypes)
                  ->whereNotIn(\DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'\')))'), $excludedTypes);
            })
            ->whereNotIn('pcs_status', ['Approved', 'Rejected', 'Cancelled'])
            ->where(function($q) {
                $q->whereHas('decisions', function($d) {
                    $d->where('pdec_action', 'dproc_save');
                })->orWhereNotIn('pcs_status', ['Draft', 'Returned']);
            })
            ->orderBy('pcs_id', 'desc')
            ->get();

        // 3. Closed: Finalized cases processed by this user
        $closed = Purchase::with(['unit', 'project', 'latestDecision.account'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->where(function($q) use ($procTypes, $excludedTypes) {
                $q->whereIn(\DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'ps\')))'), $procTypes)
                  ->whereNotIn(\DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'\')))'), $excludedTypes);
            })
            ->whereHas('decisions', function($q) use ($user) {
                $q->where('pdec_acc_id', $user->acc_id);
            })
            ->whereIn('pcs_status', ['Approved', 'Rejected'])
            ->orderBy('pcs_id', 'desc')
            ->limit(20)
            ->get();

        // 4. Action Taken
        $actionTaken = Purchase::with(['unit', 'project', 'latestDecision.account', 'currentSubstatus'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->where(function($q) use ($procTypes, $excludedTypes) {
                $q->whereIn(\DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'ps\')))'), $procTypes)
                  ->whereNotIn(\DB::raw('LOWER(TRIM(COALESCE(pcs_type, \'\')))'), $excludedTypes);
            })
            ->where(function($q) use ($user) {
                $q->whereHas('decisions', function($d) use ($user) {
                    $d->where('pdec_acc_id', $user->acc_id);
                })->orWhereHas('decisions', function($d) {
                    $d->where('pdec_action', 'dproc_save');
                });
            })
            ->whereNotIn('pcs_id', $pending->pluck('pcs_id')->toArray())
            ->orderBy('pcs_id', 'desc')
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
        $lower = $user->acc_lowerm;
        $upper = $user->acc_upperm;
        
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account'])

            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->findOrFail($id);

        // Financial Intelligence (Legacy Logic)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $fin = $finService->getHeadStatus($purchase->pcs_hed_id);
        $subheads = $finService->getSubheadBreakdown($purchase->pcs_hed_id);
        $head = $fin;
        
        $divisionName = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_name');
        $canApprove = $this->approvalService->canApprove('proc', $purchase->pcs_price);
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
