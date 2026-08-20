<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurCaseSubstatus;
use App\Models\PurItLetter;
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

        $purchase = Purchase::findOrFail($id);
        $action = $request->input('action');
        $remarks = $request->input('remarks', 'No remarks provided.');

        // Delegate to the approval service (fixes bypass bug — old code set pcs_status directly
        // without creating commitments or updating substatus)
        try {
            $this->approvalService->processDecision($purchase, $action, $remarks);
            return redirect()->route('nrdi.purchase_cases.index')->with('success', 'Case has been processed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
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

        $user = auth()->user();
        $unitId = $user->acc_unt_id ?? null;

        $headsQuery = DB::table('cen.heads')
                    ->select('hed_id', 'hed_name', 'hed_code')
                    ->orderBy('hed_name', 'asc');
        
        if ($unitId) {
            $headsQuery->where('hed_unt_id', $unitId);
        }

        $heads = $headsQuery->get();

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
            $taxType = strtoupper(trim((string) $request->input('tax_type', 'GST')));
            $taxPercent = (float) $request->input('tax_percent', 18);
            
            // Calculate total price from quotations (lowest firm wins) or from legacy remarks_JSON
            $totalPrice = 0;
            $firmTotals = [];
            $firmSubtotals = [];
            $firmTaxes = [];
            
            if (!empty($quotationsInput)) {
                foreach ($quotationsInput as $firmId => $itemPrices) {
                    $firmSub = 0;
                    foreach ($itemPrices as $idx => $price) {
                        $qty = (float)($itemsInput[$idx]['qty'] ?? 1);
                        $firmSub += ((float)($price ?? 0) * $qty);
                    }
                    $firmTax = $firmSub * ($taxPercent / 100);
                    $firmTot = $firmSub + $firmTax;

                    $firmSubtotals[$firmId] = $firmSub;
                    $firmTaxes[$firmId] = $firmTax;
                    $firmTotals[$firmId] = $firmTot;
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
            $pcs->pcs_midprice = $totalPrice;
            $pcs->pcs_intprice = $totalPrice;
            if (!empty($firmTotals)) {
                $winningFirmId = array_keys($firmTotals, min($firmTotals))[0];
                $pcs->pcs_frm_id = $winningFirmId;
                $pcs->pcs_inttax = (float)($firmTaxes[$winningFirmId] ?? 0);
                $pcs->pcs_midtax = (float)($firmTaxes[$winningFirmId] ?? 0);
            }
            $pcs->save();

            // Create initial substatus row: case starts at Division (fix #1)
            PurCaseSubstatus::create([
                'pss_pcs_id'    => $pcs->pcs_id,
                'pss_stage'     => 'Division',
                'pss_is_current'=> true,
                'pss_since'     => now(),
            ]);

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
                    $firmSub = (float)($firmSubtotals[$firmId] ?? 0);
                    $firmTx = (float)($firmTaxes[$firmId] ?? 0);
                    if ($firmTotal <= 0 && $firmSub <= 0) continue;
                    
                    $firmName = DB::table('frm.firmz')->where('frm_id', $firmId)->value('frm_name') ?? 'Unknown';
                    
                    $qte_id = DB::table('pur.quotes')->insertGetId([
                        'qte_pcs_id' => $pcs->pcs_id,
                        'qte_frm_id' => $firmId,
                        'qte_firmname' => $firmName,
                        'qte_price' => $firmTotal,
                        'qte_intprice' => $firmSub,
                        'qte_inttax' => $firmTx,
                        'qte_midprice' => $firmTotal,
                        'qte_midtax' => $firmTx,
                        'qte_num' => $quoteNum++,
                        'qte_date' => $request->pcs_date,
                        'qte_techaccept' => true,
                    ], 'qte_id');

                    // Check if quote document scan is uploaded for this firm
                    if ($request->hasFile("quote_files.{$firmId}")) {
                        $qFile = $request->file("quote_files.{$firmId}");
                        if ($qFile && $qFile->isValid()) {
                            $unitName = DB::table('cen.units')->where('unt_id', $pcs->pcs_unt_id)->value('unt_namesh') 
                                ?: (DB::table('cen.units')->where('unt_id', $pcs->pcs_unt_id)->value('unt_name') ?: ('div-' . $pcs->pcs_unt_id));
                            $divSlug = \Illuminate\Support\Str::slug($unitName) ?: ('div-' . $pcs->pcs_unt_id);
                            $ext = strtolower($qFile->getClientOriginalExtension() ?: 'pdf');
                            $base = \Illuminate\Support\Str::slug($firmName) ?: ('qte-' . $qte_id);
                            $filename = 'quote-' . $pcs->pcs_id . '-' . $qte_id . '-' . $base . '-' . now()->format('YmdHis') . '.' . $ext;
                            $stored = $qFile->storeAs("purchase/quotes/{$divSlug}/{$pcs->pcs_id}", $filename, 'public');

                            DB::table('pur.purattachments')->insert([
                                'pat_objtype' => 'qte',
                                'pat_objid' => $qte_id,
                                'pat_type' => 'Quotation Document',
                                'pat_path' => $stored,
                            ]);
                        }
                    }
                    
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
        
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $headStatus = $finService->getHeadStatus($purchase->pcs_hed_id);
        $subheads = $finService->getSubheadBreakdown($purchase->pcs_hed_id);
        
        return view('purchase.initiation.minute_view', compact('purchase', 'headStatus', 'subheads'));
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
        $purchase = Purchase::with(['unit', 'items', 'quotes.firm', 'noQuotes', 'project', 'decisions.account'])->findOrFail($id);
        return view('purchase.initiation.market_research', compact('purchase'));
    }

    public function csFormal($id)
    {
        $purchase = Purchase::with(['unit', 'quotes.firm', 'project'])->findOrFail($id);
        return view('purchase.initiation.cs_formal', compact('purchase'));
    }

    public function itAnnex($id)
    {
        $purchase = Purchase::with(['unit', 'items', 'project', 'quotes.firm', 'noQuotes', 'itLetter'])->findOrFail($id);

        // Fetch all active firms in the system with their primary office address and contacts
        $allFirms = DB::table('frm.firmz as f')
            ->where('f.frm_id', '>', 0)
            ->where('f.frm_name', 'not like', '%< Select%')
            ->where('f.frm_name', 'not like', '%<Select%')
            ->orderBy('f.frm_name', 'asc')
            ->get();

        $offices = DB::table('frm.offices')->get()->groupBy('off_xfrm_id');
        $contacts = DB::table('frm.info')->get()->groupBy('inf_xmsc_id');

        // Identify firms associated with this case
        $caseFirmIds = collect();
        $caseFirmNames = collect();
        foreach ($purchase->quotes as $q) {
            if ($q->qte_frm_id) $caseFirmIds->push((int)$q->qte_frm_id);
            if ($q->firm?->frm_name) $caseFirmNames->push(strtolower(trim($q->firm->frm_name)));
        }
        foreach ($purchase->noQuotes as $nq) {
            if (!empty($nq->nqt_firmname)) $caseFirmNames->push(strtolower(trim($nq->nqt_firmname)));
        }
        $caseFirmIds = $caseFirmIds->unique()->values();

        $firmsDirectory = $allFirms->map(function ($f) use ($offices, $contacts, $caseFirmIds, $caseFirmNames) {
            $offGroup = $offices->get($f->frm_id);
            $bestOff = null;
            if ($offGroup) {
                $bestOff = $offGroup->first(fn($o) => !empty($o->off_address)) ?: $offGroup->first();
            }

            $addr = '';
            if ($bestOff && !empty($bestOff->off_address)) {
                $addr = trim($bestOff->off_address . (!empty($bestOff->off_city) ? ', ' . $bestOff->off_city : ''));
            } elseif (!empty($f->frm_notes)) {
                $addr = trim($f->frm_notes);
            }

            $conGroup = $contacts->get($f->frm_id);
            $phone = '';
            if ($conGroup) {
                $phone = $conGroup->first(function ($c) {
                    return in_array(strtolower($c->inf_type ?? ''), ['phone', 'mobile', 'tel', 'cell', 'landline', 'telephone']);
                })?->inf_value ?: ($conGroup->first()?->inf_value ?: '');
            }

            $name = trim((string)$f->frm_name);
            if (!preg_match('/^(m\/s|m\/s\.)/i', $name)) {
                $displayName = 'M/s ' . strtoupper($name);
            } else {
                $displayName = strtoupper($name);
            }

            $isCaseFirm = $caseFirmIds->contains((int)$f->frm_id) || $caseFirmNames->contains(strtolower($name));

            return [
                'id'           => $f->frm_id,
                'raw_name'     => $name,
                'name'         => $displayName,
                'address'      => $addr,
                'city'         => $bestOff?->off_city ?: '',
                'tel'          => $phone,
                'ntn'          => $f->frm_ntn ?: '',
                'gst'          => $f->frm_gst ?: '',
                'type'         => $f->frm_type ?: '',
                'entity'       => $f->frm_entity ?: '',
                'is_case_firm' => $isCaseFirm,
            ];
        })->values();

        $savedLetter = $purchase->itLetter;

        // Default letter parameters
        $refNo = $savedLetter?->pit_refno ?: ('R&D/Projects/Proc/' . $purchase->pcs_id);
        $letterDate = $savedLetter?->pit_date ?: date('d F Y', strtotime($purchase->pcs_date ?: 'now'));
        $deadlineDate = date('d F Y', strtotime(($purchase->pcs_date ?: 'now') . ' + 14 days'));
        $subject = $savedLetter?->pit_subject ?: 'REQUEST FOR QUOTATION';

        // Default Paragraphs (dynamic case title with indented sub-bullets)
        $itemTitle = $purchase->pcs_title ?: 'required items';
        $defaultPara1 = "1.\tR&D Wing NRDI at PNS JAUHAR is interested for the procurement of " . $itemTitle . ". In this regard, quotation are to be submitted to MD R&D at NRDI by " . $deadlineDate . ".";
        $defaultPara2 = "2.\tQuotation will be opened on same day at 11:00 hrs in the presence of all participants or their representatives and will be accepted at lowest quotations rate basis. However, It is apprised that MD (R&D) reserves the right to reject/ accept any quotation or invite new quotation without assigning any reason.";
        $defaultPara3 = "3.\tFollowing terms and condition would apply:\n\n\ta.\tItems are to be delivered within 15 days after issuance of purchase order.\n\tb.\tPayment will be processed / made after delivery and acceptance by user.\n\tc.\tPart Delivery / Partial shall not be entertained.\n\td.\tWarrantee / Guarantee of one year is required.";

        if (!empty($savedLetter?->pit_paragraphs) && is_array($savedLetter->pit_paragraphs)) {
            $paragraphs = $savedLetter->pit_paragraphs;
        } else {
            $paragraphs = [
                $savedLetter?->pit_para1 ?: $defaultPara1,
                $savedLetter?->pit_para2 ?: $defaultPara2,
                $savedLetter?->pit_para3 ?: $defaultPara3,
            ];
        }

        // Default Signatory
        $signatoryName = $savedLetter?->pit_signatory_name ?: 'MUHAMMAD MUDASSIR';
        $signatoryRank = $savedLetter?->pit_signatory_rank ?: 'Cdr (R) Pakistan Navy';
        $signatoryDept = $savedLetter?->pit_signatory_dept ?: 'R&D Wing, NRDI';

        // Default Selected Firms
        if (!is_null($savedLetter) && !empty($savedLetter->pit_firms)) {
            $selectedFirms = $savedLetter->pit_firms;
        } else {
            // Pre-populate with case quoted firms if any
            $caseFirmsList = $firmsDirectory->where('is_case_firm', true)->values()->toArray();
            $selectedFirms = !empty($caseFirmsList) ? $caseFirmsList : [];
        }

        // Default / Saved Annex A Items (S No, Item / specification, Qty + Denomination)
        $unitMap = [
            'num'    => fn($q) => $q == 1 ? 'No' : 'Nos',
            'nos'    => fn($q) => $q == 1 ? 'No' : 'Nos',
            'no'     => fn($q) => $q == 1 ? 'No' : 'Nos',
            'set'    => fn($q) => $q == 1 ? 'Set' : 'Sets',
            'sets'   => fn($q) => $q == 1 ? 'Set' : 'Sets',
            'job'    => fn($q) => 'Job',
            'rol'    => fn($q) => $q == 1 ? 'Roll' : 'Rolls',
            'roll'   => fn($q) => $q == 1 ? 'Roll' : 'Rolls',
            'ft'     => fn($q) => 'Ft',
            'feet'   => fn($q) => 'Feet',
            'kg'     => fn($q) => 'Kg',
            'pkt'    => fn($q) => $q == 1 ? 'Pkt' : 'Pkts',
            'packet' => fn($q) => $q == 1 ? 'Pkt' : 'Pkts',
            'mtr'    => fn($q) => $q == 1 ? 'Mtr' : 'Mtrs',
            'meter'  => fn($q) => $q == 1 ? 'Mtr' : 'Mtrs',
            'ltr'    => fn($q) => $q == 1 ? 'Ltr' : 'Ltrs',
            'liter'  => fn($q) => $q == 1 ? 'Ltr' : 'Ltrs',
            'pair'   => fn($q) => $q == 1 ? 'Pair' : 'Pairs',
            'pairs'  => fn($q) => $q == 1 ? 'Pair' : 'Pairs',
        ];

        if (!empty($savedLetter?->pit_items) && is_array($savedLetter->pit_items)) {
            $annexItems = $savedLetter->pit_items;
            foreach ($annexItems as $idx => &$sItem) {
                if (isset($sItem['qty'])) {
                    $rawQty = trim((string)$sItem['qty']);
                    if (is_numeric($rawQty)) {
                        $qVal = (float)$rawQty;
                        $matchingPci = $purchase->items[$idx] ?? null;
                        $rawUnit = strtolower(trim((string)($matchingPci?->pci_qtyunit ?? 'num')));
                        $uName = isset($unitMap[$rawUnit]) ? $unitMap[$rawUnit]($qVal) : (!empty($rawUnit) ? ucfirst($rawUnit) : 'Nos');
                        $qFormatted = ($qVal < 10 && $qVal > 0 && (int)$qVal == $qVal) ? sprintf('%02d', $qVal) : $rawQty;
                        $sItem['qty'] = trim($qFormatted . ' x ' . $uName);
                    } elseif (preg_match('/^(\d+(?:\.\d+)?)\s*(?:x\s*)?([a-zA-Z]+)$/i', $rawQty, $matches)) {
                        $qVal = (float)$matches[1];
                        $qFormatted = ($qVal < 10 && $qVal > 0 && (int)$qVal == $qVal) ? sprintf('%02d', $qVal) : $matches[1];
                        $sItem['qty'] = $qFormatted . ' x ' . ucfirst($matches[2]);
                    }
                }
            }
            unset($sItem);
        } else {
            $annexItems = [];
            foreach ($purchase->items as $idx => $item) {
                $qtyVal = (float)$item->pci_qty == (int)$item->pci_qty ? (int)$item->pci_qty : $item->pci_qty;
                $rawUnit = strtolower(trim((string)$item->pci_qtyunit));
                $unitDisplay = isset($unitMap[$rawUnit]) ? $unitMap[$rawUnit]($qtyVal) : (!empty($rawUnit) ? ucfirst($rawUnit) : 'Nos');
                $qtyFormatted = ($qtyVal < 10 && $qtyVal > 0 && (int)$qtyVal == $qtyVal) ? sprintf('%02d', $qtyVal) : $qtyVal;
                $qtyWithDenom = trim($qtyFormatted . ' x ' . $unitDisplay);

                $annexItems[] = [
                    'serial' => $idx + 1,
                    'desc'   => $item->pci_desc,
                    'qty'    => $qtyWithDenom,
                    'unit'   => $unitDisplay,
                ];
            }
        }

        // Ref No Suffix (number after Proc/)
        $refSuffix = preg_replace('/^.*?Proc\//i', '', $refNo);
        if (empty($refSuffix) || $refSuffix === $refNo) {
            $refSuffix = (string)$purchase->pcs_id;
            $refNo = 'R&D/Projects/Proc/' . $refSuffix;
        }

        $seeDistribution = $savedLetter?->pit_distribution_label ?: 'See distribution';

        return view('purchase.initiation.it_annex', compact(
            'purchase',
            'firmsDirectory',
            'savedLetter',
            'refNo',
            'refSuffix',
            'letterDate',
            'deadlineDate',
            'subject',
            'seeDistribution',
            'paragraphs',
            'signatoryName',
            'signatoryRank',
            'signatoryDept',
            'selectedFirms',
            'annexItems'
        ));
    }

    public function saveItLetter(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        $validated = $request->validate([
            'ref_no'                 => 'nullable|string|max:255',
            'letter_date'            => 'nullable|string|max:100',
            'subject'                => 'nullable|string|max:255',
            'see_distribution'       => 'nullable|string|max:255',
            'paragraphs'             => 'nullable|array',
            'para1'                  => 'nullable|string',
            'para2'                  => 'nullable|string',
            'para3'                  => 'nullable|string',
            'signatory_name'         => 'nullable|string|max:255',
            'signatory_rank'         => 'nullable|string|max:255',
            'signatory_dept'         => 'nullable|string|max:255',
            'firms'                  => 'nullable|array',
            'items'                  => 'nullable|array',
        ]);

        $paragraphs = $validated['paragraphs'] ?? [
            $validated['para1'] ?? null,
            $validated['para2'] ?? null,
            $validated['para3'] ?? null,
        ];

        $itLetter = PurItLetter::updateOrCreate(
            ['pit_pcs_id' => $purchase->pcs_id],
            [
                'pit_refno'              => $validated['ref_no'] ?? ('R&D/Projects/Proc/' . $purchase->pcs_id),
                'pit_date'               => $validated['letter_date'] ?? date('d F Y'),
                'pit_subject'            => $validated['subject'] ?? 'REQUEST FOR QUOTATION',
                'pit_distribution_label' => $validated['see_distribution'] ?? 'See distribution',
                'pit_para1'              => $paragraphs[0] ?? null,
                'pit_para2'              => $paragraphs[1] ?? null,
                'pit_para3'              => $paragraphs[2] ?? null,
                'pit_paragraphs'         => $paragraphs,
                'pit_signatory_name'     => $validated['signatory_name'] ?? 'MUHAMMAD MUDASSIR',
                'pit_signatory_rank'     => $validated['signatory_rank'] ?? 'Cdr (R) Pakistan Navy',
                'pit_signatory_dept'     => $validated['signatory_dept'] ?? 'R&D Wing, NRDI',
                'pit_firms'              => $validated['firms'] ?? [],
                'pit_items'              => $validated['items'] ?? [],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'IT Letter & Annex saved successfully.',
            'data'    => $itLetter,
        ]);
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
        $action = $request->input('action', 'forward');

        try {
            // Use the service to handle the transition logic, decision log, and notification
            $this->approvalService->processDecision($pcs, $action, $remarks);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Remarks saved successfully!']);
            }

            return redirect()->route('purchase.initiation.index')->with('success', 'Case has been released and is now with Director Procurement for scrutiny.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error releasing case: ' . $e->getMessage());
        }
    }

    /**
     * Revert case status to Draft from Under Scrutiny (Hold Feature)
     */
    public function holdCase($id)
    {
        $purchase = Purchase::with('currentSubstatus')->findOrFail($id);
        
        // Authorization: Only initiator can hold their own case
        if ($purchase->pcs_unt_id != Auth::user()->acc_unt_id) {
            return back()->with('error', 'Unauthorized access.');
        }

        // Fix #3: Gate on substatus stage, not pcs_status.
        // Only allow hold if case is at DFinance (first post-Division stage)
        // — once it passes beyond Finance, it cannot be pulled back.
        $currentStage = $purchase->currentSubstatus?->pss_stage;
        if ($currentStage !== 'DFinance') {
            return back()->with('error', 'Case cannot be held — it has already been processed beyond Finance.');
        }

        return DB::transaction(function () use ($purchase, $currentStage) {
            $purchase->pcs_status = 'Draft';
            $purchase->save();

            // Transition substatus back to Division
            PurCaseSubstatus::where('pss_pcs_id', $purchase->pcs_id)
                ->where('pss_is_current', true)
                ->update(['pss_is_current' => false, 'pss_until' => now()]);

            PurCaseSubstatus::create([
                'pss_pcs_id'    => $purchase->pcs_id,
                'pss_stage'     => 'Division',
                'pss_is_current'=> true,
                'pss_since'     => now(),
            ]);

            // Record the "Hold" action in the trail
            DB::table('pur.purdecisions')->insert([
                'pdec_pcs_id' => $purchase->pcs_id,
                'pdec_role' => 'Initiator',
                'pdec_acc_id' => Auth::id(),
                'pdec_action' => 'hold',
                'pdec_remarks' => 'Case held by Division for internal review/corrections.',
                'pdec_from_status' => $currentStage,
                'pdec_to_status' => 'Division',
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
        $purchase->pcs_midprice = $selectedQuote->qte_price;
        $purchase->save();

        return response()->json([
            'success' => true,
            'new_price' => $selectedQuote->qte_price,
            'firm_name' => $selectedQuote->firm?->frm_name ?? $selectedQuote->qte_firmname,
        ]);
    }

    /**
     * View or stream a quote attachment directly with fallbacks
     */
    public function viewQuoteAttachment(Request $request, $id)
    {
        return $this->serveQuoteAttachment($request, $id, false);
    }

    /**
     * Force download a quote attachment
     */
    public function downloadQuoteAttachment(Request $request, $id)
    {
        return $this->serveQuoteAttachment($request, $id, true);
    }

    /**
     * Detailed Offline Diagnostics for Quote Attachment
     */
    public function diagnoseQuoteAttachment(Request $request, $id)
    {
        $diag = $this->getAttachmentDiagnostics($request, $id);
        return response()->json($diag, $diag['file_found'] ? 200 : 404);
    }

    private function serveQuoteAttachment(Request $request, $id, bool $download = false)
    {
        $diag = $this->getAttachmentDiagnostics($request, $id);

        if (!$diag['file_found'] || empty($diag['matched_path'])) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Quote attachment file could not be found on disk.',
                    'diagnostics' => $diag
                ], 404);
            }

            return response()->view('errors.attachment_diagnostic', [
                'diagnostics' => $diag,
                'id' => $id,
            ], 404);
        }

        $fullPath = $diag['matched_path'];
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        $mimeMap = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'  => 'application/msword',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls'  => 'application/vnd.ms-excel',
            'csv'  => 'text/csv',
            'txt'  => 'text/plain',
        ];

        $mimeType = $mimeMap[$ext] ?? (mime_content_type($fullPath) ?: 'application/octet-stream');
        $fileName = basename($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => ($download ? 'attachment' : 'inline') . '; filename="' . $fileName . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    private function getAttachmentDiagnostics(Request $request, $id): array
    {
        $att = DB::table('pur.purattachments')
            ->where('pat_id', $id)
            ->first();

        if (!$att) {
            $att = DB::table('pur.purattachments')
                ->where('pat_objtype', 'qte')
                ->where('pat_objid', $id)
                ->orderBy('pat_id', 'desc')
                ->first();
        }

        $clientIp = $request->ip();
        $user = Auth::user();
        $dbPath = $att?->pat_path ?? '';
        $normalizedPath = str_replace('\\', '/', ltrim($dbPath, '/\\'));

        $testedPaths = [];
        $matchedPath = null;

        if (!empty($normalizedPath)) {
            $candidates = [
                storage_path('app/public/' . $normalizedPath),
                public_path('storage/' . $normalizedPath),
                storage_path('app/' . $normalizedPath),
                public_path($normalizedPath),
                base_path($normalizedPath),
            ];

            foreach ($candidates as $cand) {
                $standard = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $cand);
                $exists = file_exists($standard) && is_file($standard);
                $testedPaths[] = [
                    'path' => $standard,
                    'exists' => $exists,
                    'readable' => $exists ? is_readable($standard) : false,
                    'size' => $exists ? filesize($standard) : 0,
                ];

                if ($exists && !$matchedPath) {
                    $matchedPath = $standard;
                }
            }
        }

        return [
            'timestamp' => now()->toIso8601String(),
            'client_ip' => $clientIp,
            'user' => [
                'id' => $user?->acc_id,
                'name' => $user?->acc_name,
                'area' => $user?->acc_untarea,
            ],
            'attachment_record' => $att,
            'db_path' => $dbPath,
            'tested_paths' => $testedPaths,
            'matched_path' => $matchedPath,
            'file_found' => !is_null($matchedPath),
            'allowed_ips_config' => config('allowed_ips', []),
        ];
    }
}
