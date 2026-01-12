<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Show list of purchases for logged-in user's unit
     */
    public function index()
    {
        $userUnitId = Auth::user()->acc_unt_id;

        $purchases = Purchase::where('pcs_unt_id', $userUnitId)
                            ->orderBy('pcs_id', 'desc')
                            ->get();

        return view('purchase.new_case.viewpurchasecase', compact('purchases'));
    }

    /**
     * Show single purchase case details
     */
    public function show($id)
    {
        $userUnitId = Auth::user()->acc_unt_id;

        // 1. Get the purchase case with relationships
        $purchase = Purchase::with(['items', 'quotes.firm', 'noQuotes'])
                            ->where('pcs_id', $id)
                            ->where('pcs_unt_id', $userUnitId)
                            ->firstOrFail();

        // 2. Get firms / vendors from frm.firmz table
        $firms = DB::table('frm.firmz')
                    ->select('frm_id as id', 'frm_name as name')
                    ->orderBy('frm_name', 'asc')
                    ->get();

        // 3. Send data to view
        return view(
            'purchase.new_case.purchasecasedetails',
            compact('purchase', 'firms')
        );
    }
}
