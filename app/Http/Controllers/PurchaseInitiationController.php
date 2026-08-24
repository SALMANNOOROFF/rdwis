<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $purchases = Purchase::with(['project', 'latestDecision.account', 'items', 'quotes.firm'])
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

        // Financial Intelligence Summary
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $head = DB::table('cen.heads')->where('hed_unt_id', $unitId)->first();
        $finSummary = null;
        if ($head) {
            $s = $finService->getHeadStatus($head->hed_id);
            $finSummary = [
                'received' => $s->received,
                'expenditure' => $s->expenditure,
                'commitments' => $s->commitments,
                'in_process' => $s->in_process,
                'balance' => $s->balance,
                'available' => $s->available
            ];
        }

        return view('purchase.initiation.index', compact(
            'purchases', 'pageTitle', 'detailsRouteName', 'unitId',
            'initiatedCases', 'actionReqCases', 'completedCases', 'finSummary'
        ));
    }

    /**
     * Show the detailed view for Division Initiation (Editable if Draft)
     */
    public function show($id)
    {
        $user = Auth::user();
        $isDProc = str_contains(strtolower(trim($user->acc_untarea)), 'proc') || str_contains(strtolower(trim($user->acc_untarea)), 'prc');
        
        $query = Purchase::with(['items', 'quotes.firm', 'noQuotes', 'project', 'attachments', 'decisions.account']);
        
        if ($isDProc) {
            $lower = $user->acc_lowerm;
            $upper = $user->acc_upperm;
            $query->whereBetween('pcs_unt_id', [$lower, $upper]);
        } else {

            $query->where('pcs_unt_id', $user->acc_unt_id);
        }


        $purchase = $query->findOrFail($id);


        $service = app(\App\Services\PurchaseApprovalService::class);
        $currentAuthority = $service->getStatusDisplayName($purchase->pcs_status);
        $nextAuthority = $service->getNextAuthorityName($purchase, 'prj'); // prj is Division

        // Financial Intelligence (Legacy Logic)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $fin = $finService->getHeadStatus($purchase->pcs_hed_id);
        $subheads = $finService->getSubheadBreakdown($purchase->pcs_hed_id);
        $head = $fin;


        $firms = \App\Models\Firm::orderBy('frm_name')->get();

        $canEdit = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);
        $pageTitle = "Initiation Details: " . $purchase->pcs_title;
        $area = 'prj';

        // Recent Approved Cases for the same project/head
        $recentApproved = Purchase::withCount('items')
            ->where('pcs_hed_id', $purchase->pcs_hed_id)
            ->where(function($q) {
                $q->whereRaw('LOWER(pcs_status) = ?', ['approved'])
                  ->orWhere('pcs_status', 'Approved');
            })
            ->where('pcs_id', '!=', $purchase->pcs_id)
            ->orderBy('pcs_date', 'desc')
            ->limit(10)
            ->get();

        // Fallback: If no approved cases exist for this specific project head, show recent approved cases of any project
        if ($recentApproved->isEmpty()) {
            $recentApproved = Purchase::withCount('items')
                ->where(function($q) {
                    $q->whereRaw('LOWER(pcs_status) = ?', ['approved'])
                      ->orWhere('pcs_status', 'Approved');
                })
                ->where('pcs_id', '!=', $purchase->pcs_id)
                ->orderBy('pcs_date', 'desc')
                ->limit(10)
                ->get();
        }

        return view('nrdi.purchase_cases_new.show', compact('purchase', 'head', 'firms', 'pageTitle', 'canEdit', 'currentAuthority', 'nextAuthority', 'area', 'subheads', 'recentApproved'));

    }

    /**
     * Pull back a case from HQ to Division (Hold/Revert)
     */
    public function holdCase($id)
    {
        $purchase = Purchase::with('currentSubstatus')->findOrFail($id);
        
        // Security check
        if ($purchase->pcs_unt_id != Auth::user()->acc_unt_id) {
            return back()->with('error', 'Unauthorized access.');
        }

        // Fix #3: Gate on substatus stage, not pcs_status.
        // Only allow hold if case is at DFinance (first post-Division stage)
        $currentStage = $purchase->currentSubstatus?->pss_stage;
        if ($currentStage !== 'DFinance') {
            return back()->with('error', 'Case cannot be held — it has already been processed beyond Finance.');
        }

        return DB::transaction(function () use ($purchase, $currentStage) {
            $purchase->pcs_status = 'Draft';
            $purchase->save();

            // Transition substatus back to Division
            \App\Models\PurCaseSubstatus::where('pss_pcs_id', $purchase->pcs_id)
                ->where('pss_is_current', true)
                ->update(['pss_is_current' => false, 'pss_until' => now()]);

            \App\Models\PurCaseSubstatus::create([
                'pss_pcs_id'    => $purchase->pcs_id,
                'pss_stage'     => 'Division',
                'pss_is_current'=> true,
                'pss_since'     => now(),
            ]);

            // Record the hold action in the decision trail
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

            return back()->with('success', 'Case has been pulled back to Draft and is now editable.');
        });
    }

    public function save(Request $request, $id)
    {
        $op = (string) $request->input('op', '');
        $rules = [
            'op' => 'required|in:save_title,save_remarks,add_files,add_item,edit_item,delete_item,add_quote,delete_quote,upload_quote_file',
        ];

        if ($op === 'save_title') {
            $rules['pcs_title'] = 'required|string|max:500';
        } elseif ($op === 'save_remarks') {
            $rules['pcs_remarks'] = 'nullable|string';
        } elseif ($op === 'add_files') {
            $rules['attachments'] = 'required|array';
            $rules['attachments.*'] = 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240';
        } elseif ($op === 'add_item') {
            $rules['item_desc'] = 'required|string|max:2000';
            $rules['item_qty'] = 'required|numeric|min:0.0001';
        } elseif ($op === 'edit_item') {
            $rules['pci_id'] = 'required|integer';
            $rules['item_desc'] = 'required|string|max:2000';
            $rules['item_qty'] = 'required|numeric|min:0.0001';
        } elseif ($op === 'delete_item') {
            $rules['pci_id'] = 'required|integer';
        } elseif ($op === 'add_quote') {
            $rules['firm_name'] = 'required|string|max:255';
            $rules['item_prices'] = 'array';
            $rules['item_prices.*'] = 'numeric|min:0';
            $rules['quote_file'] = 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:15360';
        } elseif ($op === 'delete_quote') {
            $rules['qte_id'] = 'required|integer';
        } elseif ($op === 'upload_quote_file') {
            $rules['qte_id'] = 'required|integer';
            $rules['quote_file'] = 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:15360';
        }

        $request->validate($rules);

        $user = Auth::user();
        $isDProc = str_contains(strtolower(trim($user->acc_untarea)), 'proc') || str_contains(strtolower(trim($user->acc_untarea)), 'prc');

        $query = Purchase::query();
        if ($isDProc) {
            $lower = $user->acc_lowerm;
            $upper = $user->acc_upperm;
            $query->whereBetween('pcs_unt_id', [$lower, $upper]);
        } else {
            $query->where('pcs_unt_id', $user->acc_unt_id);
        }

        $purchase = $query->findOrFail($id);

        $status = strtolower(trim((string) $purchase->pcs_status));
        if (!in_array($status, ['draft', 'returned']) && !$isDProc) {
            return $this->respond($request, (int) $purchase->pcs_id, false, 'Case cannot be edited in current status.');
        }

        $result = DB::transaction(function () use ($request, $purchase, $op) {
            if ($op === 'save_title') {
                $purchase->pcs_title = $request->input('pcs_title');
                $purchase->save();
                return ['ok' => true, 'message' => 'Saved.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'save_remarks') {
                $purchase->pcs_remarks = $request->input('pcs_remarks');
                $purchase->save();
                return ['ok' => true, 'message' => 'Saved.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'add_files') {
                $files = $request->file('attachments', []);
                foreach ($files as $file) {
                    if (!$file) continue;
                    $ext = strtolower($file->getClientOriginalExtension() ?: 'file');
                    $base = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $base = Str::slug($base) ?: ('pcs-' . $purchase->pcs_id);
                    $filename = $base . '-' . now()->format('YmdHis') . '-' . Str::random(6) . '.' . $ext;
                    $stored = $file->storeAs('purchase', $filename, 'public');
                    DB::table('pur.purattachments')->insert([
                        'pat_objtype' => 'pcs',
                        'pat_objid' => $purchase->pcs_id,
                        'pat_type' => 'Attachment',
                        'pat_path' => $stored,
                    ]);
                }
                return ['ok' => true, 'message' => 'Uploaded.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'add_item') {
                $maxSerial = (int) (DB::table('pur.purcaseitems')->where('pci_pcs_id', $purchase->pcs_id)->max('pci_serial') ?? 0);
                $nextSerial = $maxSerial + 1;
                $desc = trim((string) $request->input('item_desc'));
                $qty = (float) $request->input('item_qty');
                $unit = trim((string) $request->input('item_qtyunit', 'num')) ?: 'num';

                $pciId = DB::table('pur.purcaseitems')->insertGetId([
                    'pci_pcs_id' => $purchase->pcs_id,
                    'pci_serial' => $nextSerial,
                    'pci_desc' => $desc,
                    'pci_qty' => $qty,
                    'pci_qtyunit' => $unit,
                    'pci_price' => 0,
                    'pci_type' => 1,
                    'pci_subtype' => 1,
                ], 'pci_id');

                $quotes = DB::table('pur.quotes')->where('qte_pcs_id', $purchase->pcs_id)->get(['qte_id', 'qte_num']);
                foreach ($quotes as $q) {
                    DB::table('pur.quoteitems')->insert([
                        'qti_qte_id' => $q->qte_id,
                        'qti_pci_id' => $pciId,
                        'qti_price' => 0,
                        'qti_qty' => $qty,
                        'qti_serial' => $nextSerial,
                        'qti_desc' => $desc,
                        'qti_qtyunit' => $unit,
                        'qti_pcsdesc' => $desc,
                    ]);
                }

                $this->recalcCasePricing($purchase->pcs_id);
                return ['ok' => true, 'message' => 'Item added.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'edit_item') {
                $pciId = (int) $request->input('pci_id');
                $desc = trim((string) $request->input('item_desc'));
                $qty = (float) $request->input('item_qty');
                $unit = trim((string) $request->input('item_qtyunit', 'num')) ?: 'num';

                $item = DB::table('pur.purcaseitems')
                    ->where('pci_pcs_id', $purchase->pcs_id)
                    ->where('pci_id', $pciId)
                    ->first();

                if (!$item) {
                    return ['ok' => false, 'message' => 'Item not found.', 'pcsId' => (int) $purchase->pcs_id];
                }

                DB::table('pur.purcaseitems')
                    ->where('pci_pcs_id', $purchase->pcs_id)
                    ->where('pci_id', $pciId)
                    ->update([
                        'pci_desc' => $desc,
                        'pci_qty' => $qty,
                        'pci_qtyunit' => $unit
                    ]);

                DB::table('pur.quoteitems')
                    ->where('qti_pci_id', $pciId)
                    ->update([
                        'qti_desc' => $desc,
                        'qti_qty' => $qty,
                        'qti_qtyunit' => $unit,
                        'qti_pcsdesc' => $desc
                    ]);

                $this->recalcCasePricing($purchase->pcs_id);
                return ['ok' => true, 'message' => 'Item updated successfully.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'delete_item') {
                $pciId = (int) $request->input('pci_id');
                $item = DB::table('pur.purcaseitems')->where('pci_pcs_id', $purchase->pcs_id)->where('pci_id', $pciId)->first();
                if (!$item) {
                    return ['ok' => false, 'message' => 'Item not found.', 'pcsId' => (int) $purchase->pcs_id];
                }
                DB::table('pur.quoteitems')->where('qti_pci_id', $pciId)->delete();
                DB::table('pur.purcaseitems')->where('pci_pcs_id', $purchase->pcs_id)->where('pci_id', $pciId)->delete();
                $this->recalcCasePricing($purchase->pcs_id);
                return ['ok' => true, 'message' => 'Item deleted.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'add_quote') {
                $qteId = $request->input('qte_id');
                $firmId = $request->input('qte_frm_id'); 
                $firmName = trim((string) $request->input('firm_name'));
                $qteNum = $request->input('qte_num');
                if ($qteNum === null || $qteNum === '') {
                    if ($qteId) {
                        $qteNum = DB::table('pur.quotes')->where('qte_id', $qteId)->value('qte_num');
                    }
                    if ($qteNum === null || $qteNum === '') {
                        $qteNum = (int) (DB::table('pur.quotes')->where('qte_pcs_id', $purchase->pcs_id)->max('qte_num') ?? 0) + 1;
                    }
                }
                $qteDate = $request->input('qte_date') ?: $purchase->pcs_date;
                
                // Tax parameters
                $taxType = strtoupper(trim((string) $request->input('tax_type', 'GST')));
                $taxPercent = (float) $request->input('tax_percent', 18);

                // Nested items from the modal OR flat array
                $inputItems = (array) $request->input('items', []);
                $flatItemPrices = (array) $request->input('item_prices', []);
                
                // Fetch case items to ensure consistency
                $caseItems = DB::table('pur.purcaseitems')->where('pci_pcs_id', $purchase->pcs_id)->get();
                
                $subtotal = 0.0;
                foreach ($caseItems as $it) {
                    $price = 0.0;
                    if (isset($inputItems[$it->pci_id]['price'])) {
                        $price = (float) $inputItems[$it->pci_id]['price'];
                    } elseif (isset($flatItemPrices[$it->pci_id])) {
                        $price = (float) $flatItemPrices[$it->pci_id];
                    }
                    $subtotal += ($price * (float)$it->pci_qty);
                }

                $taxAmount = $subtotal * ($taxPercent / 100);
                $total = $subtotal + $taxAmount;

                // Identify Firm
                if (!$firmId && $firmName) {
                    $firm = DB::table('frm.firmz')->where('frm_name', $firmName)->first();
                    $firmId = $firm ? $firm->frm_id : null;
                } elseif ($firmId) {
                    $firm = DB::table('frm.firmz')->where('frm_id', $firmId)->first();
                    $firmName = $firm ? $firm->frm_name : $firmName;
                }

                if ($qteId) {
                    // Update existing quote
                    DB::table('pur.quotes')->where('qte_id', $qteId)->update([
                        'qte_frm_id' => $firmId,
                        'qte_firmname' => $firmName,
                        'qte_price' => $total,
                        'qte_intprice' => $subtotal,
                        'qte_inttax' => $taxAmount,
                        'qte_midprice' => $total,
                        'qte_midtax' => $taxAmount,
                        'qte_num' => $qteNum,
                        'qte_date' => $qteDate,
                    ]);
                    DB::table('pur.quoteitems')->where('qti_qte_id', $qteId)->delete();
                } else {
                    // Insert new quote
                    $qteId = DB::table('pur.quotes')->insertGetId([
                        'qte_pcs_id' => $purchase->pcs_id,
                        'qte_frm_id' => $firmId,
                        'qte_firmname' => $firmName,
                        'qte_price' => $total,
                        'qte_intprice' => $subtotal,
                        'qte_inttax' => $taxAmount,
                        'qte_midprice' => $total,
                        'qte_midtax' => $taxAmount,
                        'qte_num' => $qteNum,
                        'qte_date' => $qteDate,
                        'qte_techaccept' => true,
                    ], 'qte_id');
                }

                // Handle Scanned Quote Document Upload
                if ($request->hasFile('quote_file')) {
                    $qFile = $request->file('quote_file');
                    if ($qFile && $qFile->isValid()) {
                        $unitName = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_namesh') 
                            ?: (DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_name') ?: ('div-' . $purchase->pcs_unt_id));
                        $divSlug = Str::slug($unitName) ?: ('div-' . $purchase->pcs_unt_id);
                        $ext = strtolower($qFile->getClientOriginalExtension() ?: 'pdf');
                        $base = Str::slug($firmName) ?: ('qte-' . $qteId);
                        $filename = 'quote-' . $purchase->pcs_id . '-' . $qteId . '-' . $base . '-' . now()->format('YmdHis') . '.' . $ext;
                        $stored = $qFile->storeAs("purchase/quotes/{$divSlug}/{$purchase->pcs_id}", $filename, 'public');

                        // Remove existing attachment for this quote
                        DB::table('pur.purattachments')
                            ->where('pat_objtype', 'qte')
                            ->where('pat_objid', $qteId)
                            ->delete();

                        DB::table('pur.purattachments')->insert([
                            'pat_objtype' => 'qte',
                            'pat_objid' => $qteId,
                            'pat_type' => 'Quotation Document',
                            'pat_path' => $stored,
                        ]);
                    }
                }

                foreach ($caseItems as $it) {
                    $price = 0.0;
                    if (isset($inputItems[$it->pci_id]['price'])) {
                        $price = (float) $inputItems[$it->pci_id]['price'];
                    } elseif (isset($flatItemPrices[$it->pci_id])) {
                        $price = (float) $flatItemPrices[$it->pci_id];
                    }
                    
                    DB::table('pur.quoteitems')->insert([
                        'qti_qte_id' => $qteId,
                        'qti_pci_id' => $it->pci_id,
                        'qti_price' => $price,
                        'qti_qty' => (float) $it->pci_qty,
                        'qti_serial' => (int) $it->pci_serial,
                        'qti_desc' => (string) $it->pci_desc,
                        'qti_qtyunit' => (string) $it->pci_qtyunit,
                        'qti_pcsdesc' => (string) $it->pci_desc,
                    ]);
                }

                $this->recalcCasePricing($purchase->pcs_id);

                return ['ok' => true, 'message' => 'Quotation saved successfully.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'upload_quote_file') {
                $qteId = (int) $request->input('qte_id');
                $quote = DB::table('pur.quotes')->where('qte_pcs_id', $purchase->pcs_id)->where('qte_id', $qteId)->first();
                if (!$quote) {
                    return ['ok' => false, 'message' => 'Quotation not found.', 'pcsId' => (int) $purchase->pcs_id];
                }

                if ($request->hasFile('quote_file')) {
                    $qFile = $request->file('quote_file');
                    if ($qFile && $qFile->isValid()) {
                        $unitName = DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_namesh') 
                            ?: (DB::table('cen.units')->where('unt_id', $purchase->pcs_unt_id)->value('unt_name') ?: ('div-' . $purchase->pcs_unt_id));
                        $divSlug = Str::slug($unitName) ?: ('div-' . $purchase->pcs_unt_id);
                        $ext = strtolower($qFile->getClientOriginalExtension() ?: 'pdf');
                        $base = Str::slug($quote->qte_firmname) ?: ('qte-' . $qteId);
                        $filename = 'quote-' . $purchase->pcs_id . '-' . $qteId . '-' . $base . '-' . now()->format('YmdHis') . '.' . $ext;
                        $stored = $qFile->storeAs("purchase/quotes/{$divSlug}/{$purchase->pcs_id}", $filename, 'public');

                        DB::table('pur.purattachments')
                            ->where('pat_objtype', 'qte')
                            ->where('pat_objid', $qteId)
                            ->delete();

                        DB::table('pur.purattachments')->insert([
                            'pat_objtype' => 'qte',
                            'pat_objid' => $qteId,
                            'pat_type' => 'Quotation Document',
                            'pat_path' => $stored,
                        ]);

                        return ['ok' => true, 'message' => 'Quotation document uploaded successfully.', 'pcsId' => (int) $purchase->pcs_id];
                    }
                }
                return ['ok' => false, 'message' => 'Invalid document file.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'delete_quote') {
                $qteId = (int) $request->input('qte_id');
                $quote = DB::table('pur.quotes')->where('qte_pcs_id', $purchase->pcs_id)->where('qte_id', $qteId)->first();
                if (!$quote) {
                    return ['ok' => false, 'message' => 'Quotation not found.', 'pcsId' => (int) $purchase->pcs_id];
                }
                DB::table('pur.purattachments')->where('pat_objtype', 'qte')->where('pat_objid', $qteId)->delete();
                DB::table('pur.quoteitems')->where('qti_qte_id', $qteId)->delete();
                DB::table('pur.quotes')->where('qte_id', $qteId)->delete();
                $this->recalcCasePricing($purchase->pcs_id);
                return ['ok' => true, 'message' => 'Quotation deleted.', 'pcsId' => (int) $purchase->pcs_id];
            }

            return ['ok' => false, 'message' => 'Invalid operation.', 'pcsId' => (int) $purchase->pcs_id];
        });

        return $this->respond($request, (int) ($result['pcsId'] ?? $purchase->pcs_id), (bool) ($result['ok'] ?? false), (string) ($result['message'] ?? ''));
    }

    protected function respond(Request $request, int $pcsId, bool $ok, string $message)
    {
        if ($request->expectsJson() || $request->ajax()) {
            $status = $ok ? 200 : 422;
            return response()->json([
                'ok' => $ok,
                'message' => $message,
                'data' => $ok ? $this->snapshot($pcsId) : null,
            ], $status);
        }

        return back()->with($ok ? 'success' : 'error', $message);
    }

    protected function snapshot(int $pcsId): array
    {
        $purchase = Purchase::with(['items', 'quotes.firm', 'attachments'])->findOrFail($pcsId);

        $items = $purchase->items->sortBy('pci_serial')->values()->map(fn($i) => [
            'pci_id' => (int) $i->pci_id,
            'pci_serial' => (int) $i->pci_serial,
            'pci_desc' => (string) $i->pci_desc,
            'pci_qty' => (float) $i->pci_qty,
            'pci_qtyunit' => (string) $i->pci_qtyunit,
            'pci_price' => (float) ($i->pci_price ?? 0),
        ])->values();

        $quoteAttachments = DB::table('pur.purattachments')
            ->where('pat_objtype', 'qte')
            ->whereIn('pat_objid', $purchase->quotes->pluck('qte_id')->toArray())
            ->get()
            ->keyBy('pat_objid');

        $quotes = $purchase->quotes->values()->map(function($q) use ($quoteAttachments) {
            $att = $quoteAttachments->get($q->qte_id);
            $filePath = $att ? (string) $att->pat_path : null;
            $fileName = $filePath ? basename(str_replace('\\', '/', $filePath)) : null;
            return [
                'qte_id' => (int) $q->qte_id,
                'qte_num' => (int) ($q->qte_num ?? 0),
                'firm_name' => (string) ($q->firm?->frm_name ?? $q->qte_firmname),
                'qte_price' => (float) ($q->qte_price ?? 0),
                'qte_subtotal' => (float) ($q->qte_intprice ?? 0),
                'qte_tax' => (float) ($q->qte_inttax ?? 0),
                'attachment_path' => $filePath,
                'attachment_name' => $fileName ?? 'Quote Document',
            ];
        })->values();

        $quoteIds = $quotes->pluck('qte_id')->toArray();
        $quoteItems = [];
        if (count($quoteIds) > 0) {
            $rows = DB::table('pur.quoteitems')
                ->whereIn('qti_qte_id', $quoteIds)
                ->get(['qti_qte_id', 'qti_pci_id', 'qti_price']);
            foreach ($rows as $r) {
                $qid = (string) $r->qti_qte_id;
                $pid = (string) $r->qti_pci_id;
                if (!isset($quoteItems[$qid])) $quoteItems[$qid] = [];
                $quoteItems[$qid][$pid] = (float) $r->qti_price;
            }
        }

        $attachments = $purchase->attachments->values()->map(fn($a) => [
            'pat_id' => (int) $a->pat_id,
            'pat_path' => (string) $a->pat_path,
            'pat_filename' => basename(str_replace('\\', '/', (string)($a->pat_path ?? ''))),
        ])->values();

        return [
            'pcs_id' => (int) $purchase->pcs_id,
            'pcs_title' => (string) $purchase->pcs_title,
            'pcs_remarks' => (string) ($purchase->pcs_remarks ?? ''),
            'pcs_price' => (float) ($purchase->pcs_price ?? 0),
            'items' => $items,
            'quotes' => $quotes,
            'attachments' => $attachments,
            'quote_items' => $quoteItems,
        ];
    }

    protected function recalcCasePricing(int $pcsId): void
    {
        $quoteIds = DB::table('pur.quotes')->where('qte_pcs_id', $pcsId)->pluck('qte_id')->toArray();
        if (count($quoteIds) === 0) {
            $items = DB::table('pur.purcaseitems')->where('pci_pcs_id', $pcsId)->get();
            $itemsTotal = 0;
            foreach ($items as $it) {
                $qty = (float)($it->pci_qty ?? 1);
                $rate = (float)($it->pci_price ?: ($it->pci_rate ?: ($it->pci_estcost ?: ($it->pci_estprice ?? 0))));
                $itemsTotal += ($rate > 0 ? $rate * ($it->pci_price ? 1 : $qty) : 0);
            }
            DB::table('pur.purcases')->where('pcs_id', $pcsId)->update([
                'pcs_price' => $itemsTotal,
                'pcs_midprice' => $itemsTotal,
                'pcs_intprice' => $itemsTotal
            ]);
            return;
        }

        $totals = [];
        $subtotals = [];
        $taxes = [];
        foreach ($quoteIds as $qteId) {
            $quote = DB::table('pur.quotes')->where('qte_id', $qteId)->first();
            $items = DB::table('pur.quoteitems')->where('qti_qte_id', $qteId)->get();
            $sub = 0;
            foreach ($items as $qi) {
                $qty = (float)($qi->qti_qty ?? 1);
                $unitPrice = (float)($qi->qti_price ?? 0);
                $sub += ($unitPrice * $qty);
            }
            
            $taxPercent = (float)($quote->qte_taxpercent ?? ($quote->qte_tax ?? 18));
            $taxAmount = $sub * ($taxPercent / 100);
            $grandTotal = $sub + $taxAmount;

            DB::table('pur.quotes')->where('qte_id', $qteId)->update([
                'qte_price' => $grandTotal,
                'qte_intprice' => $sub,
                'qte_inttax' => $taxAmount
            ]);
            $totals[$qteId] = $grandTotal;
            $subtotals[$qteId] = $sub;
            $taxes[$qteId] = $taxAmount;
        }

        asort($totals);
        reset($totals);
        $winnerId = (int) key($totals);
        $winnerTotal = (float) ($totals[$winnerId] ?? 0);

        DB::table('pur.purcases')->where('pcs_id', $pcsId)->update([
            'pcs_price' => $winnerTotal,
            'pcs_midprice' => $winnerTotal,
            'pcs_intprice' => (float)($subtotals[$winnerId] ?? $winnerTotal),
            'pcs_inttax' => (float)($taxes[$winnerId] ?? 0),
            'pcs_midtax' => (float)($taxes[$winnerId] ?? 0),
        ]);

        $winnerItems = DB::table('pur.quoteitems')
            ->where('qti_qte_id', $winnerId)
            ->get(['qti_pci_id', 'qti_price', 'qti_qty']);

        $map = [];
        foreach ($winnerItems as $row) {
            $map[(int) $row->qti_pci_id] = (float) $row->qti_price;
        }

        $items = DB::table('pur.purcaseitems')->where('pci_pcs_id', $pcsId)->get(['pci_id']);
        foreach ($items as $it) {
            $pciId = (int) $it->pci_id;
            DB::table('pur.purcaseitems')->where('pci_pcs_id', $pcsId)->where('pci_id', $pciId)->update([
                'pci_price' => (float) ($map[$pciId] ?? 0),
            ]);
        }
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
