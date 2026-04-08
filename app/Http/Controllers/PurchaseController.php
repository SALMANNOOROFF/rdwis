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

    $detailsRouteName = 'purchasecasedetails';
    $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
    return view('purchase.new_case.viewpurchasecase', compact('purchases', 'detailsRouteName', 'unitNameMap'));
}

    public function nrdiIndex()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        [$lower, $upper] = $user->acc_lowers == 0
            ? [$user->acc_lowerm, $user->acc_upperm]
            : [$user->acc_lowers, $user->acc_uppers];

        $purchases = Purchase::with(['project'])
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->orderBy('pcs_id', 'desc')
            ->get();

        $detailsRouteName = 'nrdi.purchase_cases.show';
        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $groupedPurchases = $purchases->groupBy('pcs_unt_id');
        
        // Return new DG specific index
        return view('nrdi.purchase_cases.index', compact('purchases', 'detailsRouteName', 'unitNameMap', 'groupedPurchases'));
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

    public function nrdiShow($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        [$lower, $upper] = $user->acc_lowers == 0
            ? [$user->acc_lowerm, $user->acc_upperm]
            : [$user->acc_lowers, $user->acc_uppers];

        $purchase = Purchase::with(['items', 'quotes.firm', 'noQuotes', 'project', 'attachments'])
            ->where('pcs_id', $id)
            ->whereBetween('pcs_unt_id', [$lower, $upper])
            ->firstOrFail();

        // Load account head info (which budget head is being charged)
        $head    = DB::table('cen.heads')->where('hed_id', $purchase->pcs_hed_id)->first();
        $effHead = $purchase->pcs_effhed_id && $purchase->pcs_effhed_id != $purchase->pcs_hed_id
                   ? DB::table('cen.heads')->where('hed_id', $purchase->pcs_effhed_id)->first()
                   : null;

        // Load division name
        $divisionName = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_name');

        // Dummy History/Approval Trail. For production this should come from a history table.
        // Assuming $purchase->history exists or we generate mock data
        // Recent first
        $approvalTrail = collect([
            (object)['actor' => 'Director', 'action' => 'Forwarded', 'date' => now()->subDays(2), 'comment' => 'Forwarded to SORD/DG'],
            (object)['actor' => 'Division Officer', 'action' => 'Initiated', 'date' => $purchase->created_at ?? $purchase->pcs_date, 'comment' => 'Created the purchase case'],
        ]);

        return view('nrdi.purchase_cases.show', compact('purchase', 'approvalTrail', 'head', 'effHead', 'divisionName'));
    }

    public function nrdiAction(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user) return redirect()->route('login');

        // Note: For full safety, ensure this case actually belongs to DG's jurisdiction.
        $purchase = Purchase::findOrFail($id);
        $action = $request->input('action');
        $remarks = $request->input('remarks');

        if ($action == 'approve') {
            $purchase->pcs_status = 'Approved';
        } elseif ($action == 'return') {
            $purchase->pcs_status = 'Returned';
        } elseif ($action == 'reject') {
            $purchase->pcs_status = 'Rejected';
        }

        // Save DG remarks somewhere (e.g. into a history table). For now just onto the case remarks or handle later.
        $purchase->save();

        return redirect()->route('nrdi.purchase_cases.index')->with('success', 'Case has been ' . $purchase->pcs_status);
    }

    /**
     * Show create new purchase case form
     */
    public function select()
    {
        return view('purchase.new_case.select');
    }

    /**
     * unified dynamic creation view
     */
    public function unifiedCreate(Request $request, $type)
    {
        $maxId = DB::table('pur.purcases')->max('pcs_id');
        $nextId = $maxId ? ($maxId + 1) : 1;

        $heads = DB::table('cen.heads')
                    ->select('hed_id', 'hed_name', 'hed_code')
                    ->orderBy('hed_name', 'asc')
                    ->get();
        
        // Use the refined split-view form for consultancy and services (Outsourcing)
        if (in_array($type, ['consultancy', 'services'])) {
            return view('purchase.new_case.consultancy_form', compact('nextId', 'heads', 'type'));
        }
                    
        return view('purchase.new_case.unified_form', compact('nextId', 'heads', 'type'));
    }

    /**
     * Store a new purchase case (Unified logic support)
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
        
        // Handle remarks_JSON to pcs_remarks
        if ($request->has('remarks_JSON')) {
            $pcs->pcs_remarks = json_encode($request->remarks_JSON);
        }

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
