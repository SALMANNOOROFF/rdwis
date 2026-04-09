<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseInitiationController extends Controller
{
    /**
     * Display the Division Initiation Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $unitId = $user->acc_unt_id;

        // Fetch all cases initiated by this unit with rich context
        $purchases = Purchase::with(['project', 'latestDecision.account'])
            ->where('pcs_unt_id', $unitId)
            ->orderBy('pcs_id', 'desc')
            ->get();

        $initiatedCases = $purchases->filter(function($p) {
            $status = strtolower(trim($p->pcs_status));
            return !in_array($status, ['draft', 'returned', 'approved', 'rejected']);
        });
        
        $actionReqCases = $purchases->filter(function($p) {
            return in_array(strtolower(trim($p->pcs_status)), ['draft', 'returned']);
        });

        $completedCases = $purchases->filter(function($p) {
            return in_array(strtolower(trim($p->pcs_status)), ['approved', 'rejected']);
        });

        $pageTitle = "PC Initiation Hub";
        $detailsRouteName = 'purchase.initiation.show'; // New dedicated route

        return view('purchase.initiation.index', compact(
            'purchases', 'pageTitle', 'detailsRouteName', 'unitId',
            'initiatedCases', 'actionReqCases', 'completedCases'
        ));
    }

    /**
     * Show the detailed view for Division Initiation (Editable if Draft)
     */
    public function show($id)
    {
        $user = Auth::user();
        $unitId = $user->acc_unt_id;

        $purchase = Purchase::with(['items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account'])
            ->where('pcs_unt_id', $unitId)
            ->findOrFail($id);

        $service = app(\App\Services\PurchaseApprovalService::class);
        $currentAuthority = $service->getStatusDisplayName($purchase->pcs_status);
        $nextAuthority = $service->getNextAuthorityName($purchase, 'prj'); // prj is Division

        // Budget Head Balance Calculation (similar to HQ logic)
        $head = null;
        if($purchase->project) {
            $totalBudget = (float) $purchase->project->prj_aprvcost;
            
            // Total of all APPROVED purchase cases for this project
            $approvedSpent = Purchase::where('pcs_hed_id', $purchase->pcs_hed_id)
                ->where('pcs_status', 'Approved')
                ->sum('pcs_price');

            $head = (object) [
                'prj_aprvcost' => $totalBudget,
                'hed_balance' => $totalBudget - $approvedSpent
            ];
        }

        $firms = \App\Models\Firm::orderBy('frm_name')->get();
        $canEdit = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);
        $pageTitle = "Initiation Details: " . $purchase->pcs_title;
        $area = 'prj';

        return view('purchase.initiation.show', compact('purchase', 'head', 'firms', 'pageTitle', 'canEdit', 'currentAuthority', 'nextAuthority', 'area'));
    }

    /**
     * Pull back a case from HQ to Division (Hold/Revert)
     */
    public function holdCase($id)
    {
        $purchase = Purchase::findOrFail($id);
        
        // Security check
        if ($purchase->pcs_unt_id != Auth::user()->acc_unt_id) {
            return back()->with('error', 'Unauthorized access.');
        }

        // Only allow if not yet approved/rejected
        if (in_array(strtolower($purchase->pcs_status), ['approved', 'rejected'])) {
             return back()->with('error', 'Approved/Rejected cases cannot be held.');
        }

        $purchase->pcs_status = 'Draft';
        $purchase->save();

        return back()->with('success', 'Case has been pulled back to Draft and is now editable.');
    }

    /**
     * JSON endpoint for JQuery polling of statuses
     */
    public function getStatuses()
    {
        $user = Auth::user();
        $unitId = $user->acc_unt_id;

        $statuses = Purchase::where('pcs_unt_id', $unitId)
            ->select('pcs_id', 'pcs_status')
            ->get()
            ->pluck('pcs_status', 'pcs_id');

        return response()->json($statuses);
    }
}
