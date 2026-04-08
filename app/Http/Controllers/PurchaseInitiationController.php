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

        $releasedCases = $purchases->filter(function($p) {
            return !in_array(strtolower(trim($p->pcs_status)), ['draft', 'returned']);
        });
        
        $pendingCases = $purchases->filter(function($p) {
            return in_array(strtolower(trim($p->pcs_status)), ['draft', 'returned']);
        });

        // Explicit counts for status clarity (Active cases at HQ)
        $atHqCount = $purchases->filter(function($p) {
            $status = strtolower(trim($p->pcs_status));
            return !in_array($status, ['draft', 'returned', 'approved', 'rejected']);
        })->count();

        $releasedTotal = $releasedCases->sum('pcs_price');
        $pendingTotal = $pendingCases->sum('pcs_price');

        $pageTitle = "PC Initiation Hub";
        $detailsRouteName = 'purchase.initiation.show'; // New dedicated route

        return view('purchase.initiation.index', compact(
            'purchases', 'pageTitle', 'detailsRouteName', 'unitId',
            'releasedTotal', 'pendingTotal', 'releasedCases', 'pendingCases', 'atHqCount'
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

        // Budget Head Balance Calculation (similar to HQ logic)
        $head = null;
        if($purchase->project) {
            $totalBudget = (float) $purchase->project->prj_cost;
            
            // Total of all APPROVED purchase cases for this project
            $approvedSpent = Purchase::where('pcs_hed_id', $purchase->pcs_hed_id)
                ->where('pcs_status', 'Approved')
                ->sum('pcs_price');

            $head = (object) [
                'hed_balance' => $totalBudget - $approvedSpent
            ];
        }

        $firms = \App\Models\Firm::orderBy('frm_name')->get();
        $canEdit = in_array($purchase->pcs_status, ['Draft', 'Returned']);
        $pageTitle = "Initiation Details: " . $purchase->pcs_title;

        return view('purchase.initiation.show', compact('purchase', 'head', 'firms', 'pageTitle', 'canEdit'));
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
