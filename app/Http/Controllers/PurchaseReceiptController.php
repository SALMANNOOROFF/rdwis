<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReceiptController extends Controller
{
    /**
     * Display a list of approved purchase cases eligible for goods receipt.
     */
    public function index(Request $request)
    {
        $fulfillmentFilter = $request->get('fulfillment', 'All');

        $query = DB::table('pur.purcases as p')
            ->leftJoin('cen.heads as h', 'p.pcs_hed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'p.pcs_unt_id', '=', 'u.unt_id')
            ->where('p.pcs_status', 'Approved')
            ->select(
                'p.*',
                'h.hed_code',
                'h.hed_name',
                'u.unt_namesh'
            );

        if ($fulfillmentFilter !== 'All') {
            $query->where('p.pcs_fulfillment_status', $fulfillmentFilter);
        }

        $purchases = $query->orderBy('p.pcs_id', 'desc')->paginate(20);

        return view('purchase.receipts.index', compact('purchases', 'fulfillmentFilter'));
    }

    /**
     * Form to receive items for an approved purchase case.
     */
    public function create($pcs_id)
    {
        $purchase = DB::table('pur.purcases as p')
            ->leftJoin('cen.heads as h', 'p.pcs_hed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'p.pcs_unt_id', '=', 'u.unt_id')
            ->where('p.pcs_id', $pcs_id)
            ->where('p.pcs_status', 'Approved')
            ->select('p.*', 'h.hed_code', 'h.hed_name', 'u.unt_namesh')
            ->firstOrFail();

        $items = DB::table('pur.purcaseitems')
            ->where('pci_pcs_id', $pcs_id)
            ->orderBy('pci_serial', 'asc')
            ->get();

        $previousReceipts = DB::table('pur.purreceipts')
            ->where('prt_pcs_id', $pcs_id)
            ->orderBy('prt_id', 'desc')
            ->get();

        return view('purchase.receipts.create', compact('purchase', 'items', 'previousReceipts'));
    }

    /**
     * Finalize receipt and populate inventory assets.
     */
    public function store(Request $request, $pcs_id)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.received_qty' => 'nullable|numeric|min:0',
        ]);

        $purchase = DB::table('pur.purcases')->where('pcs_id', $pcs_id)->firstOrFail();
        $caseItems = DB::table('pur.purcaseitems')->where('pci_pcs_id', $pcs_id)->get()->keyBy('pci_id');

        $hasReceivedQty = false;
        foreach ($request->items as $pci_id => $data) {
            if (!empty($data['received_qty']) && (float)$data['received_qty'] > 0) {
                $hasReceivedQty = true;
                break;
            }
        }

        if (!$hasReceivedQty) {
            return back()->with('error', 'Please enter at least one positive received quantity.');
        }

        DB::transaction(function () use ($pcs_id, $purchase, $caseItems, $request) {
            // 1. Create Receipt master record
            $prt_id = DB::table('pur.purreceipts')->insertGetId([
                'prt_date'   => now()->toDateString(),
                'prt_unt_id' => $purchase->pcs_unt_id,
                'prt_prj_id' => $purchase->pcs_hed_id,
                'prt_status' => 'Finalized',
                'prt_pcs_id' => $pcs_id,
                'prt_dtg'    => now(),
            ], 'prt_id');

            $serial = 1;

            foreach ($request->items as $pci_id => $data) {
                $receivedQty = (float)($data['received_qty'] ?? 0);
                if ($receivedQty <= 0) continue;

                $item = $caseItems->get($pci_id);
                if (!$item) continue;

                // 2. Insert receipt item
                DB::table('pur.purreceiptitems')->insert([
                    'pti_prt_id'  => $prt_id,
                    'pti_desc'    => $item->pci_desc,
                    'pti_qty'     => $receivedQty,
                    'pti_qtyunit' => $item->pci_qtyunit,
                    'pti_pci_id'  => $pci_id,
                    'pti_serial'  => $serial++,
                ]);

                // 3. Update item fulfilment count
                $newFulfilment = ((float)($item->pci_fulfilment ?? 0)) + $receivedQty;
                DB::table('pur.purcaseitems')
                    ->where('pci_id', $pci_id)
                    ->update(['pci_fulfilment' => $newFulfilment]);

                // 4. Populate Inventory Assets (ina.invats & ina.invatcomps)
                $ias_id = DB::table('ina.invats')->insertGetId([
                    'ias_pcs_id'    => $pcs_id,
                    'ias_pci_id'    => $pci_id,
                    'ias_desc'      => $item->pci_desc,
                    'ias_qty'       => $receivedQty,
                    'ias_qtyunit'   => $item->pci_qtyunit,
                    'ias_unt_id'    => $purchase->pcs_unt_id,
                    'ias_effhed_id' => $purchase->pcs_effhed_id,
                    'ias_chargedate'=> now()->toDateString(),
                    'ias_price'     => $item->pci_price ?? 0,
                    'ias_type'      => (string)($item->pci_type ?? '1'),
                    'ias_subtype'   => (string)($item->pci_subtype ?? 'General'),
                    'ias_dtg'       => now(),
                ], 'ias_id');

                DB::table('ina.invatcomps')->insert([
                    'iac_ias_id'  => $ias_id,
                    'iac_qty'     => $receivedQty,
                    'iac_qtyunit' => $item->pci_qtyunit,
                    'iac_status'  => 'Held', // Store custody (On Charge)
                    'iac_dtg'     => now(),
                ]);
            }

            // 5. Check total fulfillment status for the case
            $updatedItems = DB::table('pur.purcaseitems')->where('pci_pcs_id', $pcs_id)->get();
            $fullyReceived = true;
            $partiallyReceived = false;

            foreach ($updatedItems as $it) {
                $ordered = (float)($it->pci_qty ?? 0);
                $fulfilled = (float)($it->pci_fulfilment ?? 0);
                if ($fulfilled < $ordered) {
                    $fullyReceived = false;
                }
                if ($fulfilled > 0) {
                    $partiallyReceived = true;
                }
            }

            $fulfillmentStatus = 'Pending Receipt';
            if ($fullyReceived) {
                $fulfillmentStatus = 'Fully Received';
            } elseif ($partiallyReceived) {
                $fulfillmentStatus = 'Partially Received';
            }

            DB::table('pur.purcases')
                ->where('pcs_id', $pcs_id)
                ->update(['pcs_fulfillment_status' => $fulfillmentStatus]);
        });

        return redirect()->route('purchase.receipts.index')->with('success', 'Goods receipt finalized and inventory items updated successfully!');
    }

    /**
     * Display Inventory Assets and manage On-Charge / Off-Charge status transitions with filters.
     */
    public function assetsIndex(Request $request)
    {
        $user = auth()->user();
        $isDivision = $user && method_exists($user, 'isDivision') && $user->isDivision();
        
        $category = $request->get('category', 'All'); // Assets, Inventory, All
        $statusGroup = $request->get('group', 'All'); // OnCharge, OffCharge, All
        $status = $request->get('status', 'All');
        $headId = $request->get('head_id', 'All');
        $search = $request->get('search', '');

        // Division users are strictly locked to their own division unit
        if ($isDivision) {
            $unitId = (string) $user->acc_unt_id;
        } else {
            $unitId = $request->get('unit_id', 'All');
        }

        $query = DB::table('ina.invatcomps as c')
            ->join('ina.invats as a', 'c.iac_ias_id', '=', 'a.ias_id')
            ->leftJoin('pur.purcases as p', 'a.ias_pcs_id', '=', 'p.pcs_id')
            ->leftJoin('cen.units as u', 'a.ias_unt_id', '=', 'u.unt_id')
            ->leftJoin('cen.heads as h', 'a.ias_effhed_id', '=', 'h.hed_id')
            ->select(
                'c.*',
                'a.ias_desc',
                'a.ias_pcs_id',
                'a.ias_price',
                'a.ias_type',
                'a.ias_subtype',
                'a.ias_chargedate',
                'p.pcs_title',
                'u.unt_namesh',
                'u.unt_name',
                'h.hed_code',
                'h.hed_name'
            );

        // Category filter (Assets = ias_type 7, Inventory = ias_type != 7)
        if ($category === 'Assets') {
            $query->where('a.ias_type', '7');
        } elseif ($category === 'Inventory') {
            $query->where('a.ias_type', '!=', '7');
        }

        // Group filter (OnCharge vs OffCharge)
        if ($statusGroup === 'OnCharge') {
            $query->whereIn('c.iac_status', ['Untagged', 'Tagged', 'Held']);
        } elseif ($statusGroup === 'OffCharge') {
            $query->whereIn('c.iac_status', ['Issued to User', 'Installed', 'Consumed', 'Written Off']);
        }

        // Specific status filter
        if ($status !== 'All') {
            $query->where('c.iac_status', $status);
        }

        // Unit / Division filter
        if ($unitId !== 'All') {
            $query->where('a.ias_unt_id', $unitId);
        }

        // Project / Budget head filter
        if ($headId !== 'All') {
            $query->where('a.ias_effhed_id', $headId);
        }

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('a.ias_desc', 'ILIKE', "%{$search}%")
                  ->orWhere('c.iac_person', 'ILIKE', "%{$search}%")
                  ->orWhere('c.iac_location', 'ILIKE', "%{$search}%")
                  ->orWhere('c.iac_remarks', 'ILIKE', "%{$search}%")
                  ->orWhere('p.pcs_title', 'ILIKE', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('a.ias_pcs_id', (int)$search)
                      ->orWhere('c.iac_id', (int)$search);
                }
            });
        }

        // Summary Calculations (On-Charge vs Off-Charge Totals for Assets & Inventory)
        $summaryQuery = DB::table('ina.invatcomps as c')
            ->join('ina.invats as a', 'c.iac_ias_id', '=', 'a.ias_id');

        if ($unitId !== 'All') {
            $summaryQuery->where('a.ias_unt_id', $unitId);
        }
        if ($headId !== 'All') {
            $summaryQuery->where('a.ias_effhed_id', $headId);
        }

        $assetsOnCharge = (clone $summaryQuery)->where('a.ias_type', '7')->whereIn('c.iac_status', ['Untagged', 'Tagged', 'Held'])
            ->selectRaw('COUNT(c.iac_id) as total_count, COALESCE(SUM(c.iac_qty * a.ias_price), 0) as total_value')->first();
        $assetsOffCharge = (clone $summaryQuery)->where('a.ias_type', '7')->whereIn('c.iac_status', ['Issued to User', 'Installed', 'Consumed', 'Written Off'])
            ->selectRaw('COUNT(c.iac_id) as total_count, COALESCE(SUM(c.iac_qty * a.ias_price), 0) as total_value')->first();

        $invOnCharge = (clone $summaryQuery)->where('a.ias_type', '!=', '7')->whereIn('c.iac_status', ['Untagged', 'Tagged', 'Held'])
            ->selectRaw('COUNT(c.iac_id) as total_count, COALESCE(SUM(c.iac_qty * a.ias_price), 0) as total_value')->first();
        $invOffCharge = (clone $summaryQuery)->where('a.ias_type', '!=', '7')->whereIn('c.iac_status', ['Issued to User', 'Installed', 'Consumed', 'Written Off'])
            ->selectRaw('COUNT(c.iac_id) as total_count, COALESCE(SUM(c.iac_qty * a.ias_price), 0) as total_value')->first();

        $assets = $query->orderBy('c.iac_id', 'desc')->paginate(25);

        // Dropdown data
        $units = DB::table('cen.units')->orderBy('unt_namesh')->get();
        
        $headsQuery = DB::table('cen.heads');
        if ($unitId !== 'All') {
            $headsQuery->whereExists(function ($sub) use ($unitId) {
                $sub->select(DB::raw(1))
                    ->from('pur.purcases')
                    ->whereColumn('purcases.pcs_hed_id', 'heads.hed_id')
                    ->where('purcases.pcs_unt_id', $unitId);
            });
        }
        $heads = $headsQuery->orderBy('hed_code')->get();

        return view('inventory.assets.index', compact(
            'assets',
            'category',
            'statusGroup',
            'status',
            'unitId',
            'headId',
            'search',
            'units',
            'heads',
            'isDivision',
            'assetsOnCharge',
            'assetsOffCharge',
            'invOnCharge',
            'invOffCharge'
        ));
    }

    /**
     * Update asset component status (Transition from On-Charge to Off-Charge).
     */
    public function updateAssetStatus(Request $request, $iac_id)
    {
        $request->validate([
            'iac_status'   => 'required|string|in:Untagged,Tagged,Held,Issued to User,Installed,Consumed,Written Off',
            'iac_person'   => 'nullable|string',
            'iac_location' => 'nullable|string',
            'iac_remarks'  => 'nullable|string',
        ]);

        DB::table('ina.invatcomps')
            ->where('iac_id', $iac_id)
            ->update([
                'iac_status'   => $request->iac_status,
                'iac_person'   => $request->iac_person,
                'iac_location' => $request->iac_location,
                'iac_remarks'  => $request->iac_remarks,
                'iac_dispdate' => now()->toDateString(),
                'iac_dispdtg'  => now(),
            ]);

        return back()->with('success', 'Asset status updated successfully!');
    }
}
