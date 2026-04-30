<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\PurchaseApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    protected $approvalService;

    public function __construct(PurchaseApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }
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
    public function unifiedCreate(Request $request, $type = 'material')
    {
        // If type is empty or generic, default to material
        if (!$type || $type == 'all') $type = 'material';

        $maxId = DB::table('pur.purcases')->max('pcs_id');
        $nextId = $maxId ? ($maxId + 1) : 1;

        $heads = DB::table('cen.heads')
                    ->select('hed_id', 'hed_name', 'hed_code')
                    ->orderBy('hed_name', 'asc')
                    ->get();

        $firms = DB::table('frm.firmz')->select('frm_id', 'frm_name')->orderBy('frm_name')->get();
        
        // Use the refined split-view form for consultancy and services (Outsourcing)
        if (in_array($type, ['consultancy', 'services'])) {
            return view('purchase.new_case.consultancy_form', compact('nextId', 'heads', 'type', 'firms'));
        }
                    
        return view('purchase.new_case.unified_form', compact('nextId', 'heads', 'type', 'firms'));
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
            'pcs_type' => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            $userUnitId = Auth::user()->acc_unt_id;

            $pcs = new Purchase();
            $pcs->pcs_date = $request->pcs_date;
            $pcs->pcs_title = $request->pcs_title;
            $pcs->pcs_minute = $request->pcs_minute;
            
            // Map long types to 5-char codes for DB varchar(5) limit
            $typeMap = [
                'material'    => 'mat',
                'consultancy' => 'cons',
                'services'    => 'serv',
                'civil'       => 'civ',
                'training'    => 'trn',
                'tada'        => 'tada',
                'transport'   => 'tran',
                'books'       => 'book',
                'license'     => 'lic',
                'internet'    => 'net',
                'publishing'  => 'pub',
                'stationery'  => 'stat',
            ];
            $pcs->pcs_type = $typeMap[$request->pcs_type] ?? substr($request->pcs_type, 0, 5);
            
            if ($request->has('remarks_JSON')) {
                $pcs->pcs_remarks = json_encode($request->remarks_JSON);
            }

            $pcs->pcs_status = 'Draft';
            $pcs->pcs_unt_id = $userUnitId; 
            $pcs->pcs_effunt_id = $userUnitId; 
            $pcs->pcs_intunt_id = $userUnitId;
            $pcs->pcs_hed_id = $request->pcs_hed_id;
            $pcs->pcs_effhed_id = $request->pcs_hed_id;
            $pcs->pcs_price = 0;
            $pcs->pcs_midprice = 0;
            $pcs->pcs_inttax = 0;
            $pcs->pcs_midtax = 0;
            $pcs->pcs_transtype = 1;
            $pcs->pcs_noloan = false;
            
            // Build item index map for quotation price lookup
            $itemsInput = $request->input('items', []);
            $quotationsInput = $request->input('quotations', []); // [firm_id => [item_idx => price]]
            
            // Calculate total price from quotations (lowest firm wins) or from legacy remarks_JSON
            $totalPrice = 0;
            $firmTotals = [];
            
            if (!empty($quotationsInput)) {
                foreach ($quotationsInput as $firmId => $itemPrices) {
                    $firmTotal = 0;
                    foreach ($itemPrices as $idx => $price) {
                        $firmTotal += (float)($price ?? 0);
                    }
                    $firmTotals[$firmId] = $firmTotal;
                }
                // Lowest firm total = case price
                if (!empty($firmTotals)) {
                    $totalPrice = min($firmTotals);
                }
            } elseif ($request->has('remarks_JSON.items')) {
                foreach ($request->input('remarks_JSON.items') as $item) {
                    $qty = (float)($item['qty'] ?? ($item['amount'] ?? 0));
                    $rate = (float)($item['rate'] ?? 1);
                    $totalPrice += ($qty * $rate);
                }
            } elseif ($request->has('remarks_JSON.milestones')) {
                foreach ($request->input('remarks_JSON.milestones') as $m) {
                    $totalPrice += (float)($m['amount'] ?? 0);
                }
            }
            
            $pcs->pcs_price = $totalPrice;
            $pcs->save();

            $itemIds = [];
            if (!empty($itemsInput)) {
                $itemsInput = array_values($itemsInput); // standardize to 0-index
                $serial = 1;
                foreach ($itemsInput as $idx => $item) {
                    $desc = $item['desc'] ?? '';
                    if (empty(trim($desc))) continue;
                    
                    $qty = (float)($item['qty'] ?? 1);
                    $unit = $item['unit'] ?? 'num';
                    
                    // Item price = winning firm's price for this item (if quotations exist)
                    $itemPrice = 0;
                    if (!empty($firmTotals)) {
                        $winningFirmId = array_keys($firmTotals, min($firmTotals))[0];
                        $itemPrice = (float)($quotationsInput[$winningFirmId][$idx] ?? 0);
                    }
                    
                    $pci_id = DB::table('pur.purcaseitems')->insertGetId([
                        'pci_pcs_id' => $pcs->pcs_id,
                        'pci_serial' => $serial++,
                        'pci_desc' => $desc,
                        'pci_qty' => $qty,
                        'pci_qtyunit' => $unit,
                        'pci_price' => $itemPrice,
                        'pci_type' => 1,
                        'pci_subtype' => 1,
                    ], 'pci_id');
                    
                    $itemIds[] = $pci_id;
                }
            }

            // --- Save Quotations to quotes ---
            if (!empty($quotationsInput)) {
                $quoteNum = 1;
                foreach ($quotationsInput as $firmId => $itemPrices) {
                    $firmTotal = (float)($firmTotals[$firmId] ?? 0);
                    if ($firmTotal <= 0) continue;
                    
                    $firmName = DB::table('frm.firmz')->where('frm_id', $firmId)->value('frm_name') ?? 'Unknown';
                    
                    $qte_id = DB::table('pur.quotes')->insertGetId([
                        'qte_pcs_id' => $pcs->pcs_id,
                        'qte_frm_id' => $firmId,
                        'qte_firmname' => $firmName,
                        'qte_price' => $firmTotal,
                        'qte_num' => $quoteNum++,
                        'qte_date' => $request->pcs_date,
                        'qte_techaccept' => true,
                    ], 'qte_id');
                    
                    // Save individual prices to quoteitems for Comparative Statement
                    if (is_array($itemPrices)) {
                        $itemPrices = array_values($itemPrices); // standardize
                        foreach ($itemPrices as $idx => $price) {
                            if (isset($itemIds[$idx])) {
                                DB::table('pur.quoteitems')->insert([
                                    'qti_qte_id' => $qte_id,
                                    'qti_pci_id' => $itemIds[$idx],
                                    'qti_price' => (float)$price,
                                    'qti_qty' => (float)($itemsInput[$idx]['qty'] ?? 1),
                                    'qti_serial' => $idx + 1,
                                    'qti_desc' => $itemsInput[$idx]['desc'] ?? 'Item',
                                    'qti_qtyunit' => $itemsInput[$idx]['unit'] ?? 'num',
                                    'qti_pcsdesc' => $itemsInput[$idx]['desc'] ?? 'Item'
                                ]);
                            }
                        }
                    }
                }
            }

            // Handle Direct Release logic
            if ($request->has('release_directly') && $request->release_directly == '1') {
                $approvalService = app(\App\Services\PurchaseApprovalService::class);
                $initiationRemarks = $request->initiation_remarks ?: '<ol start="1"><li>Case Initiated and forwarded.</li></ol>';
                $approvalService->processDecision($pcs, 'forward', $initiationRemarks);
                
                return redirect()->route('purchase.initiation.index')
                    ->with('success', 'Case #'.$pcs->pcs_id.' Created and Released to HQ successfully!');
            }

            return redirect()->route('purchase.initiation.index')
                ->with('success', 'Case #'. $pcs->pcs_id .' Created as Draft in your Unit!');
        });
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

    public function minuteView($id)
    {
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'project', 'attachments', 'decisions.account'])->findOrFail($id);
        
        // Live Financials
        $project = $purchase->project;
        if ($project) {
            $totalSpent = Purchase::where('pcs_hed_id', $project->prj_id)
                ->where('pcs_status', 'Approved')
                ->where('pcs_id', '<>', $purchase->pcs_id)
                ->sum('pcs_price');
            $project->hed_balance = ($project->prj_aprvcost ?? 0) - $totalSpent;
        }
        $head = $project;
        
        return view('purchase.initiation.minute_view', compact('purchase', 'head'));
    }

    public function caseDetail($id)
    {
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'project', 'attachments', 'decisions.account'])->findOrFail($id);
        
        // Live Financials
        $project = $purchase->project;
        if ($project) {
            $totalSpent = Purchase::where('pcs_hed_id', $project->prj_id)
                ->where('pcs_status', 'Approved')
                ->where('pcs_id', '<>', $purchase->pcs_id)
                ->sum('pcs_price');
            $project->hed_balance = ($project->prj_aprvcost ?? 0) - $totalSpent;
        }
        $head = $project;
        
        return view('purchase.initiation.case_detail', compact('purchase', 'head'));
    }

    public function marketResearch($id)
    {
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'noQuotes.firm', 'project', 'decisions.account'])->findOrFail($id);
        return view('purchase.initiation.market_research', compact('purchase'));
    }

    public function csFormal($id)
    {
        $purchase = Purchase::with(['unit', 'quotes.firm', 'project'])->findOrFail($id);
        return view('purchase.initiation.cs_formal', compact('purchase'));
    }

    public function updateCore(Request $request, $id)
    {
        $request->validate([
            'pcs_title' => 'required|string|max:500',
        ]);

        $purchase = Purchase::findOrFail($id);
        
        if ($purchase->pcs_unt_id != Auth::user()->acc_unt_id) {
            return back()->with('error', 'Unauthorized access.');
        }

        if (!in_array($purchase->pcs_status, ['Draft', 'Returned'])) {
            return back()->with('error', 'Case cannot be edited in current status.');
        }

        $purchase->pcs_title = $request->pcs_title;
        $purchase->save();

        return back()->with('success', 'Case details updated successfully.');
    }

    public function releaseCase(Request $request, $id)
    {
        $pcs = Purchase::findOrFail($id);
        $remarks = $request->input('remarks', 'Case released by Division.');

        try {
            // Use the service to handle the transition logic, decision log, and notification
            $this->approvalService->processDecision($pcs, 'forward', $remarks);
            
            return redirect()->route('purchase.initiation.index')->with('success', 'Case has been released and is now with Director Procurement for scrutiny.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error releasing case: ' . $e->getMessage());
        }
    }

    /**
     * Revert case status to Draft from Under Scrutiny (Hold Feature)
     */
    public function holdCase($id)
    {
        $purchase = Purchase::findOrFail($id);
        
        // Authorization: Only initiator can hold their own case
        if ($purchase->pcs_unt_id != Auth::user()->acc_unt_id) {
            return back()->with('error', 'Unauthorized access.');
        }

        // Only allow holding if it's currently with DProc (Initiator -> DProc flow)
        if ($purchase->pcs_status != 'Under Scrutiny') {
            return back()->with('error', 'Case cannot be held as it has already been processed by HQ.');
        }

        return DB::transaction(function () use ($purchase) {
            $purchase->pcs_status = 'Draft';
            $purchase->save();

            // Record the "Hold" action in the trail
            DB::table('pur.purdecisions')->insert([
                'pdec_pcs_id' => $purchase->pcs_id,
                'pdec_role' => 'Initiator',
                'pdec_acc_id' => Auth::id(),
                'pdec_action' => 'hold',
                'pdec_remarks' => 'Case held by Division for internal review/corrections.',
                'pdec_from_status' => 'Under Scrutiny',
                'pdec_to_status' => 'Draft',
                'created_at' => now()
            ]);

            return redirect()->route('purchase.initiation.show', $purchase->pcs_id)
                ->with('success', 'Case #'.$purchase->pcs_id.' is now back in HOLD (Draft) mode.');
        });
    }

    /**
     * AJAX: Select a different winning firm for a case
     */
    public function selectFirm(Request $request, $id)
    {
        $purchase = Purchase::with('quotes')->findOrFail($id);
        $quoteId = $request->input('quote_id');
        
        $selectedQuote = $purchase->quotes->firstWhere('qte_id', $quoteId);
        if (!$selectedQuote) {
            return response()->json(['error' => 'Quote not found'], 404);
        }

        $purchase->pcs_price = $selectedQuote->qte_price;
        $purchase->save();

        return response()->json([
            'success' => true,
            'new_price' => $selectedQuote->qte_price,
            'firm_name' => $selectedQuote->firm?->frm_name ?? $selectedQuote->qte_firmname,
        ]);
    }
}
