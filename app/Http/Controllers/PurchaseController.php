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
}    public function showItemEntryForm()
{
    // 1. Database se aakhri dale huye 10 items mangwayein
    $items = DB::table('pur.purcaseitems')
                ->orderBy('pci_id', 'desc') // Taki naya item sab se upar nazar aaye
                ->limit(10)
                ->get();

    // 2. 'items' variable ko view ko pass karein (compact use karke)
    return view('purchase.items.entry', compact('items'));
}

    // 2. Data save karne ke liye (Without Price logic)
    public function storeItemStandalone(Request $request)
    {
        $request->validate([
            'pci_desc' => 'required',
            'pci_qty' => 'required|numeric',
        ]);

        try {
            DB::table('pur.purcaseitems')->insert([
                'pci_serial'    => $request->pci_serial ?? 1,
                'pci_pcs_id'    => $request->pci_pcs_id ?? 0,
                'pci_desc'      => $request->pci_desc,
                'pci_qty'       => $request->pci_qty,
                'pci_qtyunit'   => $request->pci_qtyunit,
                'pci_estprice'  => 0, // No price as requested
                'pci_price'     => 0, // No price as requested
                'pci_type'      => $request->pci_type ?? 0,
                'pci_subtype'   => $request->pci_subtype,
                'pci_category'  => $request->pci_category ?? 0,
                'pci_subhead'   => $request->pci_subhead,
                'pci_fulfilment'=> 0,
                'pci_type2'     => 0,
                'pci_pri_id'    => 0,
            ]);

            return back()->with('success', 'Item saved successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Database Error: ' . $e->getMessage());
        }
    }

    public function listItems()
{
    // Database table 'pur.purcaseitems' se saara data uthana
    $items = DB::table('pur.purcaseitems')
                ->orderBy('pci_id', 'desc') // Naye items sab se upar aayenge
                ->get();

    // View file ka rasta: resources/views/purchase/items/list.blade.php
    return view('purchase.items.list', compact('items'));
}

// Form dikhane ke liye
public function createMasterItem()
{
    // Pehle se mojud items ki list bhi mangwa letay hain niche dikhane ke liye
    $masterItems = DB::table('pur.items')->orderBy('id', 'desc')->get();
    return view('purchase.items.master_entry', compact('masterItems'));
}

// Data save karne ke liye
public function storeMasterItem(Request $request)
{
    $request->validate([
        'name' => 'required|max:255',
    ]);

    DB::table('pur.items')->insert([
        'name' => $request->name,
        'is_active' => $request->has('is_active') ? true : false,
        'created_at' => now(),
    ]);

    return back()->with('success', 'Master Item saved successfully!');
}

public function checkName(Request $request)
{
    $query = $request->get('q', '');
    
    if(!$query) return response()->json([]);
    
    // Get items that are similar to the input (partial match)
    $items = \App\Models\Purchase::where('name', 'LIKE', "%{$query}%")->get();
    
    return response()->json($items);
}

public function createBulkItem()
{
    $heads = DB::table('cen.heads')->get();
    $masterItems = DB::table('pur.items')->where('is_active', true)->orderBy('name', 'asc')->get();
    return view('purchase.items.bulk_entry', compact('heads', 'masterItems'));
}

public function storeBulkItem(Request $request)
{
    // Debugging ke liye aapka dd yahan hai
    // dd($request->all()); 

    $request->validate([
        'group_title' => 'required',
        'items' => 'required|array',
    ]);

    $userUnitId = Auth::user()->acc_unt_id;

    DB::beginTransaction();
    try {
        // Aakhri Minute Number nikalna
        $lastMinute = DB::table('pur.purreqs')
                        ->where('prq_unt_id', $userUnitId)
                        ->max('prq_minute');
        
        $nextMinute = $lastMinute ? ($lastMinute + 1) : 1;

        // 1. Save Group Master (pur.purreqs)
        $groupId = DB::table('pur.purreqs')->insertGetId([
            'prq_date'          => $request->group_date ?? now(),
            'prq_desc'          => $request->group_title,
            'prq_status'        => 'Draft',
            'prq_fulfilled'     => false,
            'prq_unt_id'        => $userUnitId,
            'prq_hed_id'        => 1,           
            'prq_effhed_id'     => 1,           
            'prq_appeffhed_id'  => 1,           
            'prq_intunt_id'     => $userUnitId, 
            'prq_dtg'           => now(),       
            'prq_minute'        => $nextMinute, 
        ], 'prq_id');

      // ... Group save karne ke baad ...

if ($request->has('items')) {
    $serialCounter = 1;

    foreach ($request->items as $item) {
        // Sirf un rows ko skip karein jo bilkul khali hain
        if (isset($item['qty']) && $item['qty'] > 0) {
            
            // Agar Description frontend se nahi aayi toh DB se uthao
            $itemName = $item['desc'] ?? null;
            if (empty($itemName) && !empty($item['item_id'])) {
                $itemName = DB::table('pur.items')->where('id', $item['item_id'])->value('name');
            }

            DB::table('pur.purreqitems')->insert([
                'pri_prq_id'   => $groupId, 
                'pri_serial'   => $serialCounter, 
                'pri_desc'     => $itemName ?? 'Manual Entry', // Fallback
                'pri_qty'      => $item['qty'],
                'pri_qtyunit'  => 'num',
                'pri_price'    => 0,
                'pri_type'     => 7, 
                'pri_category' => 1, 
                'pri_subtype'  => 'General',
                'pri_fulfilment'=> 0,
            ]);

            $serialCounter++;
        }
    }
}

        DB::commit();
        return redirect()->route('items.batch.list')->with('success', 'Demand items saved successfully!');

    } catch (\Exception $e) {
        DB::rollback();
        // Error debugging
        dd($e->getMessage()); 
    }
}

public function listBatches()
{
    // pur.purreqs table se saare groups uthana
    $batches = DB::table('pur.purreqs')
                ->orderBy('prq_id', 'desc')
                ->get();

    return view('purchase.items.batch_list', compact('batches'));
}

// 2. Kisi specific batch ke items dekhne ke liye (AJAX ya Page load)
public function viewBatch($id)
{
    // Group details uthana
    $batch = DB::table('pur.purreqs')->where('prq_id', $id)->first();
    
    // ASLI FIX: Ab items pur.purreqitems table se uthayen kyunke save wahan huye hain
    $items = DB::table('pur.purreqitems')
                ->where('pri_prq_id', $id) // Link column ka naam yahan pri_prq_id hai
                ->get();

    return view('purchase.items.batch_details', compact('batch', 'items'));
}

public function itemsHub()
{
    // Hub page ko load karne ke liye kisi data ki zaroorat nahi, sirf view return karein
    return view('purchase.items.hub');
}


public function deleteBatch($id)
{
    DB::beginTransaction();
    try {
        // 1. Pehle is batch se linked saare items delete karein
        DB::table('pur.purreqitems')->where('pri_prq_id', $id)->delete();

        // 2. Phir asal batch header delete karein
        DB::table('pur.purreqs')->where('prq_id', $id)->delete();

        DB::commit();
        return back()->with('success', 'Batch and its items deleted successfully!');

    } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', 'Delete failed: ' . $e->getMessage());
    }
}
}