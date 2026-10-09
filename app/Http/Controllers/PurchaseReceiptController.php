<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReceiptController extends Controller
{
    /**
     * Display a list of purchase receipts (Closed vs Draft tabs).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        [$lower, $upper] = $this->getUserHorizon($user);

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isProc = in_array($userArea, ['proc', 'prc'], true) || (method_exists($user, 'isProcurement') && $user->isProcurement());
        $isCommand = method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg();
        $isFinance = in_array($userArea, ['fin', 'finance'], true);
        $isDivision = method_exists($user, 'isDivision') ? $user->isDivision() : (!$isProc && !$isCommand && !$isFinance);

        // Requested tab: 'closed' (Finalized) or 'draft'
        $activeTab = $request->get('tab', 'closed');

        // Security rule: Only Division users can view Draft receipts; Procurement, Finance & Command only view Closed
        if (!$isDivision && $activeTab === 'draft') {
            $activeTab = 'closed';
        }

        $search = trim($request->get('search', ''));
        $unitFilter = $request->get('unit_id', 'All');

        // Base receipts query builder
        $baseReceiptsQuery = function ($tab) use ($user, $isDivision, $lower, $upper) {
            $q = DB::table('pur.purreceipts as r')
                ->leftJoin('pur.purcases as p', 'r.prt_pcs_id', '=', 'p.pcs_id')
                ->leftJoin('cen.heads as h', 'r.prt_prj_id', '=', 'h.hed_id')
                ->leftJoin('cen.units as u', 'r.prt_unt_id', '=', 'u.unt_id')
                ->leftJoin('frm.firmz as f', 'p.pcs_frm_id', '=', 'f.frm_id');

            // Legacy pur_purreceipts_u_closed includes both 'Finalized' and 'Cancelled'
            if ($tab === 'draft') {
                $q->where('r.prt_status', 'Draft');
            } else {
                $q->whereIn('r.prt_status', ['Finalized', 'Cancelled']);
            }

            if ($isDivision) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('r.prt_unt_id', $user->acc_unt_id)
                        ->orWhere('p.pcs_intunt_id', $user->acc_unt_id)
                        ->orWhere('p.pcs_unt_id', $user->acc_unt_id);
                });
            } else {
                $q->whereBetween('r.prt_unt_id', [$lower, $upper]);
            }

            return $q;
        };

        // Tab counts
        $closedCount = (clone $baseReceiptsQuery('closed'))->count();
        $draftCount = $isDivision ? (clone $baseReceiptsQuery('draft'))->count() : 0;

        // Active query
        $query = $baseReceiptsQuery($activeTab)
            ->select(
                'r.*',
                'p.pcs_id',
                'p.pcs_title',
                'p.pcs_minute',
                'p.pcs_date as pcs_date',
                'p.pcs_price as pcs_case_price',
                'h.hed_code',
                'h.hed_name',
                'u.unt_namesh',
                'u.unt_name',
                'f.frm_name'
            );

        if ($unitFilter !== 'All' && is_numeric($unitFilter)) {
            $query->where('r.prt_unt_id', (int)$unitFilter);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('p.pcs_title', 'ILIKE', "%{$search}%")
                  ->orWhere('f.frm_name', 'ILIKE', "%{$search}%")
                  ->orWhere('h.hed_code', 'ILIKE', "%{$search}%")
                  ->orWhere('h.hed_name', 'ILIKE', "%{$search}%")
                  ->orWhere('u.unt_namesh', 'ILIKE', "%{$search}%");

                if (is_numeric($search)) {
                    $q->orWhere('r.prt_id', (int)$search)
                      ->orWhere('p.pcs_id', (int)$search)
                      ->orWhere('p.pcs_minute', (int)$search);
                }
            });
        }

        $receipts = $query->orderBy('r.prt_id', 'desc')->paginate(20);

        // For division users, count approved purchase cases eligible for goods receipt
        $pendingCasesCount = 0;
        if ($isDivision) {
            $pendingCasesCount = DB::table('pur.purcases')
                ->whereIn('pcs_status', ['Approved', 'Partially Fulfilled'])
                ->where(function ($q) use ($user) {
                    $q->where('pcs_intunt_id', $user->acc_unt_id)
                      ->orWhere('pcs_unt_id', $user->acc_unt_id);
                })
                ->where(function ($q) {
                    $q->whereNull('pcs_fulfillment_status')
                      ->orWhere('pcs_fulfillment_status', '!=', 'Fully Received');
                })
                ->count();
        }

        // Units within horizon for dropdown
        $units = DB::table('cen.units')
            ->whereBetween('unt_id', [$lower, $upper])
            ->orderBy('unt_namesh')
            ->get();

        return view('purchase.receipts.index', compact(
            'receipts',
            'activeTab',
            'closedCount',
            'draftCount',
            'pendingCasesCount',
            'search',
            'unitFilter',
            'units',
            'isDivision',
            'isProc',
            'isFinance',
            'isCommand'
        ));
    }

    /**
     * Display a specific purchase receipt detail (Replica of legacy pur_purreceipts_detail).
     */
    public function show($prt_id)
    {
        $user = Auth::user();
        $receipt = DB::table('pur.purreceipts as r')
            ->leftJoin('pur.purcases as p', 'r.prt_pcs_id', '=', 'p.pcs_id')
            ->leftJoin('cen.heads as h', 'r.prt_prj_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'r.prt_unt_id', '=', 'u.unt_id')
            ->leftJoin('frm.firmz as f', 'p.pcs_frm_id', '=', 'f.frm_id')
            ->where('r.prt_id', $prt_id)
            ->select(
                'r.*',
                'p.pcs_id',
                'p.pcs_date',
                'p.pcs_minute',
                'p.pcs_title',
                'p.pcs_status',
                'p.pcs_price',
                'p.pcs_midprice',
                'p.pcs_intprice',
                'p.pcs_midtax',
                'p.pcs_remarks',
                'p.pcs_unt_id',
                'p.pcs_intunt_id',
                'p.pcs_type',
                'h.hed_code',
                'h.hed_name',
                'u.unt_namesh',
                'u.unt_name',
                'f.frm_name'
            )
            ->firstOrFail();

        $items = DB::table('pur.purreceiptitems')
            ->where('pti_prt_id', $prt_id)
            ->orderBy('pti_serial', 'asc')
            ->get();

        // Calculate financial components matching legacy purchase receipt
        $pcsPrice = (float)($receipt->pcs_midprice ?: ($receipt->pcs_intprice ?: $receipt->pcs_price));
        $pcsGst = (float)($receipt->pcs_midtax ?: 0);
        $pcsSst = 0.00;
        $pcsTotal = (float)($receipt->pcs_price ?: ($pcsPrice + $pcsGst));

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isProc = in_array($userArea, ['proc', 'prc'], true) || (method_exists($user, 'isProcurement') && $user->isProcurement());
        $isCommand = method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg();
        $isConcernedDivision = !$isCommand && !$isProc && ($receipt->pcs_intunt_id == $user->acc_unt_id || $receipt->pcs_unt_id == $user->acc_unt_id || $receipt->prt_unt_id == $user->acc_unt_id);
        $canManageDraft = ($isConcernedDivision || $isCommand) && ($receipt->prt_status === 'Draft');
        
        // Acceptance Form button is strictly visible ONLY for Procurement Department on Finalized receipts
        $canViewAcceptance = $isProc && ($receipt->prt_status === 'Finalized');

        return view('purchase.receipts.show', compact(
            'receipt',
            'items',
            'pcsPrice',
            'pcsGst',
            'pcsSst',
            'pcsTotal',
            'canViewAcceptance',
            'canManageDraft',
            'isProc'
        ));
    }

    /**
     * Display printable official Acceptance Form (Replica of legacy Purchase Case Items Acceptance report).
     */
    public function acceptance($prt_id)
    {
        $user = Auth::user();
        $receipt = DB::table('pur.purreceipts as r')
            ->leftJoin('pur.purcases as p', 'r.prt_pcs_id', '=', 'p.pcs_id')
            ->leftJoin('cen.heads as h', function($join) {
                $join->on('r.prt_prj_id', '=', 'h.hed_id')
                     ->orOn('p.pcs_hed_id', '=', 'h.hed_id');
            })
            ->leftJoin('cen.units as u', 'r.prt_unt_id', '=', 'u.unt_id')
            ->leftJoin('cen.units as iu', 'p.pcs_intunt_id', '=', 'iu.unt_id')
            ->leftJoin('frm.firmz as f', 'p.pcs_frm_id', '=', 'f.frm_id')
            ->where('r.prt_id', $prt_id)
            ->select(
                'r.*',
                'p.pcs_id',
                'p.pcs_date',
                'p.pcs_minute',
                'p.pcs_title',
                'p.pcs_status',
                'p.pcs_price',
                'p.pcs_midprice',
                'p.pcs_intprice',
                'p.pcs_inttax',
                'p.pcs_midtax',
                'p.pcs_remarks',
                'p.pcs_unt_id',
                'p.pcs_intunt_id',
                'p.pcs_type',
                'h.hed_code',
                'h.hed_name',
                'u.unt_namesh',
                'u.unt_name',
                'iu.unt_namesh as int_unt_namesh',
                'iu.unt_name as int_unt_name',
                'f.frm_name'
            )
            ->firstOrFail();

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isProc = in_array($userArea, ['proc', 'prc'], true) || (method_exists($user, 'isProcurement') && $user->isProcurement());
        $isCommand = method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg();

        // Must be Finalized receipt and user must have procurement/command authority
        if ($receipt->prt_status !== 'Finalized') {
            return back()->with('error', 'Acceptance form is only generated for Finalized receipts.');
        }

        if (!$isProc && !$isCommand) {
            return back()->with('error', 'Only Procurement Department is authorized to access the Acceptance Form.');
        }

        $items = DB::table('pur.purreceiptitems')
            ->where('pti_prt_id', $prt_id)
            ->orderBy('pti_serial', 'asc')
            ->get();

        $pcsPrice = (float)($receipt->pcs_intprice ?: ($receipt->pcs_midprice ?: $receipt->pcs_price));
        $pcsSst   = (float)($receipt->pcs_inttax ?? 0);
        $pcsGst   = (float)($receipt->pcs_midtax ?? 0);
        $pcsTotal = (float)($receipt->pcs_price ?: ($pcsPrice + $pcsSst + $pcsGst));

        // Effective receipt value
        $receiptValue = (float)($receipt->prt_value ?: 0);
        if ($receiptValue <= 0 && $items->isNotEmpty()) {
            $receiptValue = (float)$items->sum(function($item) {
                return (float)$item->pti_qty * (float)($item->pti_price ?? 0);
            });
        }

        return view('purchase.receipts.acceptance', compact(
            'receipt',
            'items',
            'pcsPrice',
            'pcsGst',
            'pcsSst',
            'pcsTotal',
            'receiptValue'
        ));
    }

    /**
     * Form to receive items for an approved purchase case.
     */
    public function create($pcs_id)
    {
        $user = auth()->user();
        $purchase = DB::table('pur.purcases as p')
            ->leftJoin('cen.heads as h', 'p.pcs_hed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'p.pcs_unt_id', '=', 'u.unt_id')
            ->leftJoin('cen.units as iu', 'p.pcs_intunt_id', '=', 'iu.unt_id')
            ->leftJoin('frm.firmz as f', 'p.pcs_frm_id', '=', 'f.frm_id')
            ->where('p.pcs_id', $pcs_id)
            ->whereIn('p.pcs_status', ['Approved', 'Fulfilled', 'Partially Fulfilled'])
            ->select('p.*', 'h.hed_code', 'h.hed_name', 'u.unt_namesh', 'iu.unt_namesh as int_unt_namesh', 'f.frm_name')
            ->firstOrFail();

        $isCommand = method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg();
        $isProc = in_array(strtolower(trim($user->acc_untarea ?? '')), ['proc', 'prc'], true);
        $isConcernedDivision = !$isCommand && !$isProc && ($purchase->pcs_intunt_id == $user->acc_unt_id || $purchase->pcs_unt_id == $user->acc_unt_id);
        $canReceive = $isConcernedDivision && ($purchase->pcs_fulfillment_status !== 'Fully Received') && in_array($purchase->pcs_status, ['Approved', 'Partially Fulfilled']);

        $items = DB::table('pur.purcaseitems')
            ->where('pci_pcs_id', $pcs_id)
            ->orderBy('pci_serial', 'asc')
            ->get();

        $existingDraft = DB::table('pur.purreceipts')
            ->where('prt_pcs_id', $pcs_id)
            ->where('prt_status', 'Draft')
            ->first();

        $previousReceipts = DB::table('pur.purreceipts')
            ->where('prt_pcs_id', $pcs_id)
            ->where('prt_status', 'Finalized')
            ->orderBy('prt_id', 'desc')
            ->get();

        // Attach receipt items to previous receipts
        foreach ($previousReceipts as $pr) {
            $pr->items = DB::table('pur.purreceiptitems')
                ->where('pti_prt_id', $pr->prt_id)
                ->orderBy('pti_serial', 'asc')
                ->get();
        }

        return view('purchase.receipts.create', compact('purchase', 'items', 'previousReceipts', 'existingDraft', 'canReceive', 'isConcernedDivision'));
    }

    /**
     * Create a Draft goods receipt for an approved purchase case.
     */
    public function store(Request $request, $pcs_id)
    {
        $user = auth()->user();
        $purchase = DB::table('pur.purcases')->where('pcs_id', $pcs_id)->firstOrFail();

        $isCommand = method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg();
        $isProc = in_array(strtolower(trim($user->acc_untarea ?? '')), ['proc', 'prc'], true);
        $isConcernedDivision = !$isCommand && !$isProc && ($purchase->pcs_intunt_id == $user->acc_unt_id || $purchase->pcs_unt_id == $user->acc_unt_id);

        if (!$isConcernedDivision && !$isCommand) {
            return back()->with('error', 'Only the concerned division holding the purchase items is authorized to create goods receipts.');
        }

        // Check if a Draft receipt already exists for this case (Legacy ReceivePCItems constraint)
        $existingDraft = DB::table('pur.purreceipts')
            ->where('prt_pcs_id', $pcs_id)
            ->where('prt_status', 'Draft')
            ->first();

        if ($existingDraft) {
            return redirect()->route('purchase.receipts.show', $existingDraft->prt_id)
                ->with('error', 'A draft receipt (#' . $existingDraft->prt_id . ') already exists for this case. Please finalize or cancel it before creating another receipt.');
        }

        $request->validate([
            'prt_date' => 'nullable|date|before_or_equal:today',
            'items' => 'required|array',
            'items.*.received_qty' => 'nullable|numeric|min:0',
        ]);

        $prtDate = $request->filled('prt_date') ? $request->prt_date : now()->toDateString();

        // Validation 1: Re-verify case is Approved or Partially Fulfilled
        if (!in_array($purchase->pcs_status, ['Approved', 'Partially Fulfilled'])) {
            return back()->with('error', 'Only Approved or active purchase cases are eligible for goods receipt.');
        }

        // Validation 2: Block receipt if case is already Fully Received
        if (($purchase->pcs_fulfillment_status ?? '') === 'Fully Received') {
            return back()->with('error', 'This purchase case has already been Fully Received.');
        }

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

        $prt_id = DB::transaction(function () use ($pcs_id, $purchase, $caseItems, $request, $prtDate) {
            // Calculate total financial value of this receipt batch (prt_value)
            $totalReceiptValue = 0;
            foreach ($request->items as $pci_id => $data) {
                $receivedQty = (float)($data['received_qty'] ?? 0);
                if ($receivedQty <= 0) continue;
                $item = $caseItems->get($pci_id);
                if (!$item) continue;
                $itemPrice = (float)($item->pci_price ?? 0);
                $totalReceiptValue += ($receivedQty * $itemPrice);
            }

            // 1. Create Draft Receipt master record
            $prt_id = DB::table('pur.purreceipts')->insertGetId([
                'prt_date'   => $prtDate,
                'prt_unt_id' => $purchase->pcs_unt_id,
                'prt_prj_id' => $purchase->pcs_hed_id,
                'prt_status' => 'Draft',
                'prt_pcs_id' => $pcs_id,
                'prt_dtg'    => now(),
                'prt_value'  => $totalReceiptValue,
            ], 'prt_id');

            $serial = 1;

            foreach ($request->items as $pci_id => $data) {
                $receivedQty = (float)($data['received_qty'] ?? 0);
                if ($receivedQty <= 0) continue;

                $item = $caseItems->get($pci_id);
                if (!$item) continue;

                // Check received quantity does not exceed remaining ordered quantity
                $orderedQty = (float)($item->pci_qty ?? 0);
                $alreadyFulfilled = (float)($item->pci_fulfilment ?? 0);
                $remainingOrdered = max(0, $orderedQty - $alreadyFulfilled);

                if ($receivedQty > ($remainingOrdered + 0.001)) {
                    throw new \Exception("Received quantity ({$receivedQty}) exceeds remaining ordered quantity ({$remainingOrdered}) for item: {$item->pci_desc}");
                }

                // 2. Insert receipt item
                DB::table('pur.purreceiptitems')->insert([
                    'pti_prt_id'  => $prt_id,
                    'pti_desc'    => $item->pci_desc,
                    'pti_qty'     => $receivedQty,
                    'pti_qtyunit' => $item->pci_qtyunit,
                    'pti_pci_id'  => $pci_id,
                    'pti_serial'  => $serial++,
                ]);
            }

            return $prt_id;
        });

        return redirect()->route('purchase.receipts.show', $prt_id)
            ->with('success', 'Draft Goods Receipt #' . $prt_id . ' created! Please confirm receipt date and click Finalize.');
    }

    /**
     * Finalize a Draft purchase receipt, update case fulfillment, and populate inventory assets.
     */
    public function finalizeReceipt(Request $request, $prt_id)
    {
        $user = auth()->user();
        $receipt = DB::table('pur.purreceipts')->where('prt_id', $prt_id)->firstOrFail();

        if ($receipt->prt_status !== 'Draft') {
            return back()->with('error', 'Only Draft receipts can be finalized.');
        }

        $purchase = DB::table('pur.purcases')->where('pcs_id', $receipt->prt_pcs_id)->firstOrFail();

        $isCommand = method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg();
        $isProc = in_array(strtolower(trim($user->acc_untarea ?? '')), ['proc', 'prc'], true);
        $isConcernedDivision = !$isCommand && !$isProc && ($purchase->pcs_intunt_id == $user->acc_unt_id || $purchase->pcs_unt_id == $user->acc_unt_id || $receipt->prt_unt_id == $user->acc_unt_id);

        if (!$isConcernedDivision && !$isCommand) {
            return back()->with('error', 'Only the concerned division is authorized to finalize this receipt.');
        }

        $request->validate([
            'prt_date' => 'required|date|before_or_equal:today',
        ], [
            'prt_date.required' => 'Please enter receipt date.',
            'prt_date.before_or_equal' => 'Future date is not allowed.',
        ]);

        $prtDate = $request->prt_date;

        DB::transaction(function () use ($prt_id, $receipt, $purchase, $prtDate) {
            $receiptItems = DB::table('pur.purreceiptitems')->where('pti_prt_id', $prt_id)->get();
            $caseItems = DB::table('pur.purcaseitems')->where('pci_pcs_id', $purchase->pcs_id)->get()->keyBy('pci_id');

            // 1. Update case items fulfilment and populate inventory assets for non-service items (pci_type != 3)
            foreach ($receiptItems as $ri) {
                $item = $caseItems->get($ri->pti_pci_id);
                if (!$item) continue;

                $receivedQty = (float)$ri->pti_qty;

                // Increment fulfilment in pur_purcaseitems
                $newFulfilment = (float)($item->pci_fulfilment ?? 0) + $receivedQty;
                DB::table('pur.purcaseitems')
                    ->where('pci_id', $item->pci_id)
                    ->update(['pci_fulfilment' => $newFulfilment]);

                // Non-service items (pci_type != 3) are taken on store inventory charge
                if ((int)($item->pci_type ?? 1) !== 3) {
                    $ias_id = DB::table('ina.invats')->insertGetId([
                        'ias_pcs_id'     => $purchase->pcs_id,
                        'ias_pci_id'     => $item->pci_id,
                        'ias_desc'       => $item->pci_desc,
                        'ias_qty'        => $receivedQty,
                        'ias_qtyunit'    => $item->pci_qtyunit,
                        'ias_unt_id'     => $purchase->pcs_unt_id,
                        'ias_prj_id'     => $purchase->pcs_hed_id,
                        'ias_effhed_id'  => $purchase->pcs_effhed_id,
                        'ias_chargedate' => $prtDate,
                        'ias_price'      => $item->pci_price ?? 0,
                        'ias_type'       => (string)($item->pci_type ?? '1'),
                        'ias_subtype'    => (string)($item->pci_subtype ?? 'General'),
                        'ias_type2'      => $item->pci_type2 ?? null,
                        'ias_dtg'        => now(),
                    ], 'ias_id');

                    DB::table('ina.invatcomps')->insert([
                        'iac_ias_id'  => $ias_id,
                        'iac_qty'     => $receivedQty,
                        'iac_qtyunit' => $item->pci_qtyunit,
                        'iac_status'  => 'Held', // Custody status: Store On-Charge
                        'iac_dtg'     => now(),
                    ]);
                }
            }

            // 2. Mark receipt as Finalized
            DB::table('pur.purreceipts')
                ->where('prt_id', $prt_id)
                ->update([
                    'prt_date'   => $prtDate,
                    'prt_status' => 'Finalized',
                ]);

            // 3. Update purchase case fulfillment status
            $unfulfilledItemsCount = DB::table('pur.purcaseitems')
                ->where('pci_pcs_id', $purchase->pcs_id)
                ->whereRaw('COALESCE(pci_fulfilment, 0) < pci_qty')
                ->count();

            if ($unfulfilledItemsCount === 0) {
                // All items fulfilled
                DB::table('pur.purcases')
                    ->where('pcs_id', $purchase->pcs_id)
                    ->update([
                        'pcs_status'             => 'Fulfilled',
                        'pcs_closedtg'           => now(),
                        'pcs_fulfillment_status' => 'Fully Received',
                    ]);
            } else {
                // Partially fulfilled
                DB::table('pur.purcases')
                    ->where('pcs_id', $purchase->pcs_id)
                    ->update([
                        'pcs_status'             => 'Approved',
                        'pcs_fulfillment_status' => 'Partially Received',
                    ]);
            }
        });

        return redirect()->route('purchase.receipts.show', $prt_id)
            ->with('success', 'Purchase Receipt #' . $prt_id . ' finalized successfully! Non-service items have been taken on store charge (Held).');
    }

    /**
     * Cancel a Draft purchase receipt.
     */
    public function cancelDraftReceipt(Request $request, $prt_id)
    {
        $user = auth()->user();
        $receipt = DB::table('pur.purreceipts')->where('prt_id', $prt_id)->firstOrFail();

        if ($receipt->prt_status !== 'Draft') {
            return back()->with('error', 'Only Draft receipts can be cancelled.');
        }

        $purchase = DB::table('pur.purcases')->where('pcs_id', $receipt->prt_pcs_id)->firstOrFail();

        $isCommand = method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg();
        $isProc = in_array(strtolower(trim($user->acc_untarea ?? '')), ['proc', 'prc'], true);
        $isConcernedDivision = !$isCommand && !$isProc && ($purchase->pcs_intunt_id == $user->acc_unt_id || $purchase->pcs_unt_id == $user->acc_unt_id || $receipt->prt_unt_id == $user->acc_unt_id);

        if (!$isConcernedDivision && !$isCommand) {
            return back()->with('error', 'Only the concerned division is authorized to cancel this receipt.');
        }

        DB::table('pur.purreceipts')
            ->where('prt_id', $prt_id)
            ->update(['prt_status' => 'Cancelled']);

        return redirect()->route('purchase.receipts.index', ['tab' => 'draft'])
            ->with('success', 'Draft Goods Receipt #' . $prt_id . ' has been cancelled successfully.');
    }

    /**
     * Cancel an approved or partially fulfilled purchase case (CancelPC equivalent).
     */
    public function cancelCase(Request $request, $pcs_id)
    {
        $user = auth()->user();
        $purchase = DB::table('pur.purcases')->where('pcs_id', $pcs_id)->firstOrFail();

        $isCommand = method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg();
        $isProc = in_array(strtolower(trim($user->acc_untarea ?? '')), ['proc', 'prc'], true);
        $isConcernedDivision = !$isCommand && !$isProc && ($purchase->pcs_intunt_id == $user->acc_unt_id || $purchase->pcs_unt_id == $user->acc_unt_id);

        if (!$isConcernedDivision) {
            return back()->with('error', 'Only the concerned division is authorized to cancel a purchase case.');
        }

        // Check for any draft receipts for this case
        $draftReceipt = DB::table('pur.purreceipts')
            ->where('prt_pcs_id', $pcs_id)
            ->where('prt_status', 'Draft')
            ->first();

        if ($draftReceipt) {
            return back()->with('error', 'A draft purchase receipt exists for this case. It cannot be cancelled until draft receipts are finalized or cancelled.');
        }

        // Check if any finalized receipts exist
        $hasFinalizedReceipts = DB::table('pur.purreceipts')
            ->where('prt_pcs_id', $pcs_id)
            ->where('prt_status', 'Finalized')
            ->exists();

        DB::transaction(function () use ($pcs_id, $hasFinalizedReceipts) {
            // Reversal of requisition fulfilment for linked case items (CancelPC equivalent)
            $caseItems = DB::table('pur.purcaseitems')
                ->where('pci_pcs_id', $pcs_id)
                ->whereNotNull('pci_pri_id')
                ->get();

            $reqIds = [];
            foreach ($caseItems as $item) {
                $qty = (float) $item->pci_qty;
                $fulfilled = (float) ($item->pci_fulfilment ?? 0);
                $delta = $qty - $fulfilled;

                if ($delta > 0) {
                    DB::statement(
                        'UPDATE pur.purreqitems SET pri_fulfilment = GREATEST(0, COALESCE(pri_fulfilment, 0) - ?) WHERE pri_id = ?',
                        [$delta, $item->pci_pri_id]
                    );

                    $reqItem = DB::table('pur.purreqitems')
                        ->where('pri_id', $item->pci_pri_id)
                        ->first(['pri_prq_id']);

                    if ($reqItem && $reqItem->pri_prq_id) {
                        $reqIds[$reqItem->pri_prq_id] = true;
                    }
                }
            }

            if (!empty($reqIds)) {
                DB::table('pur.purreqs')
                    ->whereIn('prq_id', array_keys($reqIds))
                    ->update(['prq_fulfilled' => false]);
            }

            if ($hasFinalizedReceipts) {
                // Partially fulfilled history -> closed as Partially Fulfilled
                DB::table('pur.purcases')
                    ->where('pcs_id', $pcs_id)
                    ->update([
                        'pcs_status'   => 'Partially Fulfilled',
                        'pcs_closedtg' => now(),
                    ]);
            } else {
                // No receipts -> Cancelled
                DB::table('pur.purcases')
                    ->where('pcs_id', $pcs_id)
                    ->update([
                        'pcs_status'   => 'Cancelled',
                        'pcs_closedtg' => now(),
                    ]);
            }
        });

        $statusMsg = $hasFinalizedReceipts
            ? 'The remaining unfulfilled balance of this purchase case has been cancelled (marked Partially Fulfilled).'
            : 'The purchase case has been cancelled successfully.';

        return back()->with('success', $statusMsg);
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
                'a.ias_unt_id',
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
        $user = auth()->user();
        $asset = DB::table('ina.invatcomps as c')
            ->join('ina.invats as a', 'c.iac_ias_id', '=', 'a.ias_id')
            ->where('c.iac_id', $iac_id)
            ->select('c.*', 'a.ias_unt_id')
            ->first();

        if (!$asset) {
            return back()->with('error', 'Asset component not found.');
        }

        $isCommand = method_exists($user, 'isMdDdgDg') && $user->isMdDdgDg();
        $isProc = in_array(strtolower(trim($user->acc_untarea ?? '')), ['proc', 'prc'], true);
        $isConcernedDivision = !$isCommand && !$isProc && ($asset->ias_unt_id == $user->acc_unt_id);

        if (!$isConcernedDivision) {
            return back()->with('error', 'Only the concerned division holding this asset is authorized to perform status transitions.');
        }

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

    /**
     * Helper to resolve the logged-in user's horizon lower and upper bounds.
     */
    private function getUserHorizon($user): array
    {
        if (!$user) {
            return [0, 99999999];
        }

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHqOrFinance = in_array($userArea, ['fin', 'rdw', 'hqs', 'nrdi', 'it', 'rdwprj', 'prjrdw', 'proc', 'prc'], true);

        if ($isHqOrFinance) {
            $lower = (int)($user->acc_lowerm ?: 0);
            $upper = (int)($user->acc_upperm ?: 99999999);
        } else {
            $lower = (int)$user->acc_lowers;
            $upper = (int)$user->acc_uppers;

            if ($lower === 0 && $upper === 0) {
                $lower = (int)$user->acc_lowerm;
                $upper = (int)$user->acc_upperm;
            }

            if ($lower === 0 && $upper === 0) {
                $lower = (int)$user->acc_unt_id;
                $upper = (int)$user->acc_unt_id;
            }
        }

        if ($lower === 0 && $upper === 0) {
            return [0, 99999999];
        }

        return [$lower, $upper];
    }
}
