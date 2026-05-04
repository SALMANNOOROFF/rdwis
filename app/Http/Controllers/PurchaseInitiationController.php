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
        $purchases = Purchase::with(['project', 'latestDecision.account'])
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

        return view('purchase.initiation.index', compact(
            'purchases', 'pageTitle', 'detailsRouteName', 'unitId',
            'initiatedCases', 'actionReqCases', 'completedCases'
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

        $service = app(\App\Services\PurchaseApprovalService::class);
        $currentAuthority = $service->getStatusDisplayName($purchase->pcs_status);
        $nextAuthority = $service->getNextAuthorityName($purchase, 'prj'); // prj is Division

        // Budget Head Balance Calculation (similar to HQ logic)
        $head = null;
        if($purchase->project) {
            $totalBudget = (float) $purchase->project->prj_aprvcost;
            
            // Total of all APPROVED purchase cases for this project
            $approvedSpent = Purchase::where('pcs_hed_id', $purchase->pcs_hed_id)
                ->where('pcs_status', 'Approved')
                ->sum('pcs_price');

            $head = (object) [
                'prj_aprvcost' => $totalBudget,
                'hed_balance' => $totalBudget - $approvedSpent
            ];
        }

        $firms = \App\Models\Firm::orderBy('frm_name')->get();
        $canEdit = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);
        $pageTitle = "Initiation Details: " . $purchase->pcs_title;
        $area = 'prj';

        return view('nrdi.purchase_cases_new.show', compact('purchase', 'head', 'firms', 'pageTitle', 'canEdit', 'currentAuthority', 'nextAuthority', 'area'));
    }

    /**
     * Pull back a case from HQ to Division (Hold/Revert)
     */
    public function holdCase($id)
    {
        $purchase = Purchase::findOrFail($id);
        
        // Security check
        if ($purchase->pcs_unt_id != Auth::user()->acc_unt_id) {
            return back()->with('error', 'Unauthorized access.');
        }

        // Only allow if not yet approved/rejected
        if (in_array(strtolower($purchase->pcs_status), ['approved', 'rejected'])) {
             return back()->with('error', 'Approved/Rejected cases cannot be held.');
        }

        $purchase->pcs_status = 'Draft';
        $purchase->save();

        return back()->with('success', 'Case has been pulled back to Draft and is now editable.');
    }

    public function save(Request $request, $id)
    {
        $op = (string) $request->input('op', '');
        $rules = [
            'op' => 'required|in:save_title,save_remarks,add_files,add_item,delete_item,add_quote,delete_quote',
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
        } elseif ($op === 'delete_item') {
            $rules['pci_id'] = 'required|integer';
        } elseif ($op === 'add_quote') {
            $rules['firm_name'] = 'required|string|max:255';
            $rules['item_prices'] = 'array';
            $rules['item_prices.*'] = 'numeric|min:0';
        } elseif ($op === 'delete_quote') {
            $rules['qte_id'] = 'required|integer';
        }

        $request->validate($rules);

        $user = Auth::user();
        $unitId = $user->acc_unt_id;

        $purchase = Purchase::where('pcs_unt_id', $unitId)->findOrFail($id);
        $status = strtolower(trim((string) $purchase->pcs_status));
        if (!in_array($status, ['draft', 'returned'])) {
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

                $pciId = DB::table('pur.purcaseitems')->insertGetId([
                    'pci_pcs_id' => $purchase->pcs_id,
                    'pci_serial' => $nextSerial,
                    'pci_desc' => $desc,
                    'pci_qty' => $qty,
                    'pci_qtyunit' => 'num',
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
                        'qti_qtyunit' => 'num',
                        'qti_pcsdesc' => $desc,
                    ]);
                }

                $this->recalcCasePricing($purchase->pcs_id);
                return ['ok' => true, 'message' => 'Item added.', 'pcsId' => (int) $purchase->pcs_id];
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
                $firmName = trim((string) $request->input('firm_name'));
                $itemPrices = (array) $request->input('item_prices', []);
                $items = DB::table('pur.purcaseitems')->where('pci_pcs_id', $purchase->pcs_id)->orderBy('pci_serial')->get(['pci_id', 'pci_serial', 'pci_desc', 'pci_qty', 'pci_qtyunit']);

                $maxNum = (int) (DB::table('pur.quotes')->where('qte_pcs_id', $purchase->pcs_id)->max('qte_num') ?? 0);
                $nextNum = $maxNum + 1;

                $total = 0.0;
                foreach ($items as $it) {
                    $total += (float) ($itemPrices[$it->pci_id] ?? 0);
                }

                $firm = DB::table('cen.firms')->where('frm_name', $firmName)->first();
                $firmId = $firm ? $firm->frm_id : null;

                $qteId = DB::table('pur.quotes')->insertGetId([
                    'qte_pcs_id' => $purchase->pcs_id,
                    'qte_frm_id' => $firmId,
                    'qte_firmname' => $firmName,
                    'qte_price' => $total,
                    'qte_num' => $nextNum,
                    'qte_date' => $purchase->pcs_date,
                    'qte_techaccept' => true,
                ], 'qte_id');

                foreach ($items as $it) {
                    DB::table('pur.quoteitems')->insert([
                        'qti_qte_id' => $qteId,
                        'qti_pci_id' => $it->pci_id,
                        'qti_price' => (float) ($itemPrices[$it->pci_id] ?? 0),
                        'qti_qty' => (float) $it->pci_qty,
                        'qti_serial' => (int) $it->pci_serial,
                        'qti_desc' => (string) $it->pci_desc,
                        'qti_qtyunit' => (string) $it->pci_qtyunit,
                        'qti_pcsdesc' => (string) $it->pci_desc,
                    ]);
                }

                $this->recalcCasePricing($purchase->pcs_id);
                return ['ok' => true, 'message' => 'Quotation saved.', 'pcsId' => (int) $purchase->pcs_id];
            }

            if ($op === 'delete_quote') {
                $qteId = (int) $request->input('qte_id');
                $quote = DB::table('pur.quotes')->where('qte_pcs_id', $purchase->pcs_id)->where('qte_id', $qteId)->first();
                if (!$quote) {
                    return ['ok' => false, 'message' => 'Quotation not found.', 'pcsId' => (int) $purchase->pcs_id];
                }
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

        $quotes = $purchase->quotes->values()->map(fn($q) => [
            'qte_id' => (int) $q->qte_id,
            'qte_num' => (int) ($q->qte_num ?? 0),
            'firm_name' => (string) ($q->firm?->frm_name ?? $q->qte_firmname),
            'qte_price' => (float) ($q->qte_price ?? 0),
        ])->values();

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
            'pat_filename' => (string) ($a->pat_filename ?? ''),
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
            DB::table('pur.purcases')->where('pcs_id', $pcsId)->update(['pcs_price' => 0]);
            DB::table('pur.purcaseitems')->where('pci_pcs_id', $pcsId)->update(['pci_price' => 0]);
            return;
        }

        $totals = [];
        foreach ($quoteIds as $qteId) {
            $sum = (float) (DB::table('pur.quoteitems')->where('qti_qte_id', $qteId)->sum('qti_price') ?? 0);
            DB::table('pur.quotes')->where('qte_id', $qteId)->update(['qte_price' => $sum]);
            $totals[$qteId] = $sum;
        }

        asort($totals);
        $winnerId = (int) array_key_first($totals);
        $winnerTotal = (float) ($totals[$winnerId] ?? 0);

        DB::table('pur.purcases')->where('pcs_id', $pcsId)->update(['pcs_price' => $winnerTotal]);

        $winnerItems = DB::table('pur.quoteitems')
            ->where('qti_qte_id', $winnerId)
            ->get(['qti_pci_id', 'qti_price']);

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
