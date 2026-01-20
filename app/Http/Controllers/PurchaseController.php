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

    // Yahan 'project' lazmi load karein taake ID ke bajaye naam mil sakay
    $purchases = Purchase::with(['project']) 
                        ->where('pcs_unt_id', $userUnitId)
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

    // Yahan 'project' relationship ko load karna zaroori hai
    $purchase = Purchase::with(['items', 'quotes.firm', 'noQuotes', 'project']) 
                        ->where('pcs_id', $id)
                        ->where('pcs_unt_id', $userUnitId)
                        ->firstOrFail();

    $firms = DB::table('frm.firmz')->select('frm_id as id', 'frm_name as name')->get();

    return view('purchase.new_case.purchasecasedetails', compact('purchase', 'firms'));
}

    /**
     * Show create new purchase case form
     */
    public function create()
    {
        $maxId = DB::table('pur.purcases')->max('pcs_id');
        $nextId = $maxId ? ($maxId + 1) : 1;

        $heads = DB::table('cen.heads')
                    ->select('hed_id', 'hed_name', 'hed_code')
                    ->orderBy('hed_name', 'asc')
                    ->get();

        $units = DB::table('cen.units')
                    ->select('unt_id', 'unt_name')
                    ->orderBy('unt_name', 'asc')
                    ->get();

        return view('purchase.new_case.createnewcase', compact('nextId', 'heads', 'units'));
    }

    /**
     * Store a new purchase case
     */
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'pcs_title' => 'required',
            'pcs_hed_id' => 'required',
            'pcs_date' => 'required',
            'pcs_minute' => 'required',
        ]);

        // 2. Get Login User Unit ID
        $userUnitId = Auth::user()->acc_unt_id;

        $pcs = new Purchase();
        $pcs->pcs_date = $request->pcs_date;
        $pcs->pcs_title = $request->pcs_title;
        $pcs->pcs_minute = $request->pcs_minute;
        $pcs->pcs_status = 'Draft';

        // SETTING UNIT ID FROM LOGIN USER
        // Ab yeh case hamesha usi bande ke account mein show hoga jo login hai
        $pcs->pcs_unt_id = $userUnitId; 
        $pcs->pcs_effunt_id = $userUnitId; 
        $pcs->pcs_intunt_id = $userUnitId;

        // Baki Mandatory fields
        $pcs->pcs_hed_id = $request->pcs_hed_id;
        $pcs->pcs_effhed_id = $request->pcs_hed_id;

        // Default Numeric values for NOT NULL constraints
        $pcs->pcs_price = 0;
        $pcs->pcs_midprice = 0;
        $pcs->pcs_inttax = 0;
        $pcs->pcs_midtax = 0;

        // Other required fields from diagram
        $pcs->pcs_transtype = 1;
        $pcs->pcs_noloan = false;
        $pcs->pcs_type = 'pt';

        try {
            $pcs->save();
            return redirect()->route('viewpurchasecase')->with('success', 'Case Created Successfully in your Unit!');
        } catch (\Exception $e) {
            return back()->with('error', 'Database Error: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: Get next minute number for a specific head
     */
    public function getNextMinuteNumber($headId)
    {
        $maxMinute = DB::table('pur.purcases')
                        ->where('pcs_hed_id', $headId)
                        ->max('pcs_minute');

        return response()->json([
            'next_minute' => $maxMinute ? ($maxMinute + 1) : 1,
            'last_minute' => $maxMinute ?? 0
        ]);
    }

    public function releaseCase($id)
{
    // 1. Find the case by ID
    $pcs = Purchase::findOrFail($id);

    // 2. Update status
    $pcs->pcs_status = 'Under Scrutiny';
    
    // 3. Save to database
    $pcs->save();

    // 4. Redirect with success message
    return redirect()->route('viewpurchasecase')->with('success', 'Case has been released and is now Under Scrutiny.');
}
}