<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PurItem;
use App\Models\PurRfq;
use App\Models\PurRfqItem;

class PurItemsController extends Controller
{
    public function setupPurnew()
    {
        $path = base_path('DB RDWIS/DB/Individual Sql Files/purnew.sql');
        if (file_exists($path)) {
            $sql = file_get_contents($path);
            DB::unprepared($sql);
        }
        return redirect()->route('purnew.create');
    }

    public function index(Request $request)
    {
        return redirect()->route('purnew.create');
    }
    
    public function indexLayout(Request $request)
    {
        $term = (string)$request->input('term', '');
        $unitId = Auth::user() && Auth::user()->unit ? Auth::user()->unit->unt_id : null;
        $q = DB::table('purnew.items as t')
            ->selectRaw(
                't.*,
                (SELECT i.price
                   FROM purnew.rfq_items i
                   JOIN purnew.rfq r ON r.rfq_id=i.rfq_id
                  WHERE i.item_id=t.item_id ' . ($unitId ? 'AND r.pcs_unt_id = '.$unitId : '') . '
                  ORDER BY r.pcs_date DESC, r.rfq_id DESC
                  LIMIT 1) AS last_price,
                (
                  COALESCE((
                    SELECT SUM(inv.inv_qty)
                      FROM ina.inventory inv
                     WHERE LOWER(inv.inv_desc)=LOWER(t.title) ' . ($unitId ? 'AND inv.inv_unt_id = '.$unitId : '') . '
                  ),0)
                  -
                  COALESCE((
                    SELECT SUM(ii.ini_qty)
                      FROM ina.invenitems ii
                     WHERE ii.ini_inv_id IN (
                       SELECT inv.inv_id FROM ina.inventory inv
                        WHERE LOWER(inv.inv_desc)=LOWER(t.title) ' . ($unitId ? 'AND inv.inv_unt_id = '.$unitId : '') . '
                     )
                       AND ii.ini_dispdate IS NOT NULL
                  ),0)
                ) AS stock_qty'
            );
        if ($term !== '') {
            $q->whereRaw('LOWER(t.title) LIKE ?', ['%'.strtolower($term).'%']);
        }
        if ($unitId) {
            $q->where('t.unt_id', $unitId);
        }
        $items = $q->orderBy('t.title')->limit(300)->get();
        $filters = [
            'term' => (string)$request->input('term', ''),
            'cat' => null,
            'sub' => null,
            'mine' => false
        ];
        return view('puritems.index_layout', compact('items','filters'));
    }

    public function createItem(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'type' => 'nullable|integer',
            'subtype' => 'nullable|string',
            'serial' => 'nullable|integer'
        ]);
        $u = Auth::user();
        $item = null;
        DB::transaction(function () use ($data, $u, &$item) {
            $item = PurItem::firstOrCreate(
                [
                    'title' => strtolower(trim($data['title'])),
                    'unt_id' => $u && $u->unit ? $u->unit->unt_id : null
                ],
                [
                    'type' => $data['type'] ?? null,
                    'subtype' => $data['subtype'] ?? null,
                    'serial' => $data['serial'] ?? null
                ]
            );
        });
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'item' => [
                    'item_id' => $item->item_id,
                    'title' => $item->title
                ]
            ]);
        }
        return redirect()->route('purnew.create');
    }

    public function rfqPreview(Request $request)
    {
        $payload = $request->input('items', []);
        $total = 0;
        $rows = [];
        foreach ($payload as $row) {
            $item = PurItem::find($row['item_id']);
            $price = isset($row['price']) ? (float)$row['price'] : 0;
            $total += $price;
            $rows[] = [
                'item'=>$item,
                'price'=>$price
            ];
        }
        return view('puritems.rfq_preview', compact('rows','total'));
    }

    public function rfqSubmit(Request $request)
    {
        $payload = $request->input('items', []);
        $u = Auth::user();
        $nextId = ((int) DB::table('purnew.rfq')->max('rfq_id')) + 1;
        $rfq = PurRfq::create([
            'rfq_id' => $nextId,
            'pcs_date' => now()->toDateString(),
            'pcs_title' => $request->input('title','Draft RFQ'),
            'pcs_unt_id' => $u && $u->unit ? $u->unit->unt_id : null,
            'pcs_hed_id' => null,
            'pcs_effhed_id' => null,
            'pcs_effunt_id' => null
        ]);
        $total = 0;
        foreach ($payload as $row) {
            $price = isset($row['price']) ? (float)$row['price'] : 0;
            PurRfqItem::create([
                'rfq_id' => $rfq->rfq_id,
                'item_id' => $row['item_id'],
                'est_price' => $price,
                'price' => $price
            ]);
            $total += $price;
        }
        return redirect()->route('purnew.groups');
    }

    public function rfqList(Request $request)
    {
        return redirect()->route('purnew.groups');
    }
    
    public function rfqListLayout(Request $request)
    {
        $u = Auth::user();
        $q = DB::table('purnew.rfq as r')
            ->leftJoin('purnew.rfq_items as i', 'i.rfq_id', '=', 'r.rfq_id')
            ->select('r.*', DB::raw('COALESCE(SUM(i.price),0) as total'))
            ->groupBy('r.rfq_id','r.pcs_date','r.pcs_title','r.pcs_unt_id','r.pcs_hed_id','r.pcs_effhed_id','r.pcs_effunt_id');
        if ($u && $u->unit) {
            $q->where('r.pcs_unt_id', $u->unit->unt_id);
        }
        if ($request->filled('term')) {
            $term = '%'.strtolower((string)$request->input('term')).'%';
            $q->whereRaw('LOWER(r.pcs_title) LIKE ?', [$term]);
        }
        $rfqs = $q->orderByDesc('r.rfq_id')->limit(200)->get();
        $firms = DB::table('frm.firmz')->orderBy('frm_name')->get();
        return view('puritems.rfq_list_layout', compact('rfqs', 'firms'));
    }

    public function rfqShowLayout($id)
    {
        $rfq = PurRfq::findOrFail($id);
        $items = DB::table('purnew.rfq_items as i')
            ->leftJoin('purnew.items as t', 'i.item_id', '=', 't.item_id')
            ->select('i.*','t.title')
            ->where('i.rfq_id', $id)
            ->get();
        $total = $items->sum('price');
        return view('puritems.rfq_show_layout', compact('rfq','items','total'));
    }

    // =============================================
    // QUOTATION SYSTEM METHODS
    // =============================================

    public function rfqItemsJson($rfqId)
    {
        $items = DB::table('purnew.rfq_items as i')
            ->leftJoin('purnew.items as t', 'i.item_id', '=', 't.item_id')
            ->select('i.rfq_item_id', 'i.rfq_id', 'i.item_id', 'i.est_price', 'i.price', 't.title')
            ->where('i.rfq_id', $rfqId)
            ->get();

        return response()->json($items);
    }

    public function deleteGroup($id)
    {
        try {
            DB::beginTransaction();

            // First delete all quotes related to items of this RFQ
            // The constraint is on rfq_item_id and rfq_id, so we can delete by rfq_id directly
            DB::table('purnew.rfq_quotes')->where('rfq_id', $id)->delete();
            
            // Delete items
            DB::table('purnew.rfq_items')->where('rfq_id', $id)->delete();
            
            // Delete the RFQ itself
            DB::table('purnew.rfq')->where('rfq_id', $id)->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Group deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error deleting group: ' . $e->getMessage()], 500);
        }
    }

    public function groupDetailsJson($id) 
    {
        // Get all items in the RFQ
        $items = DB::table('purnew.rfq_items as i')
            ->leftJoin('purnew.items as t', 'i.item_id', '=', 't.item_id')
            ->select('i.rfq_item_id', 'i.rfq_id', 'i.item_id', 'i.est_price', 'i.price as accepted_price', 't.title')
            ->where('i.rfq_id', $id)
            ->get();
            
        // Get all quotes to populate the item dropdowns
        $allQuotes = DB::table('purnew.rfq_quotes as q')
            ->join('frm.firmz as f', 'q.frm_id', '=', 'f.frm_id')
            ->where('q.rfq_id', $id)
            ->select('q.rfq_item_id', 'q.frm_id', 'f.frm_name as vendor_name', 'q.quoted_price', 'q.is_accepted')
            ->get();

        // Attach vendor info to items
        foreach ($items as $item) {
            $itemQuotes = $allQuotes->where('rfq_item_id', $item->rfq_item_id)->values();
            $item->available_quotes = $itemQuotes;
            
            $acceptedQuote = $itemQuotes->firstWhere('is_accepted', true);
            if ($acceptedQuote) {
                $item->vendor_name = $acceptedQuote->vendor_name;
                $item->vendor_id = $acceptedQuote->frm_id;
                $item->accepted_price = $acceptedQuote->quoted_price;
                $item->has_accepted_quote = true;
            } else {
                $item->vendor_name = null;
                $item->vendor_id = null;
                $item->has_accepted_quote = false;
            }
        }

        return response()->json($items);
    }

    public function getQuotationData($rfqId)
    {
        $quotes = DB::table('purnew.rfq_quotes as q')
            ->join('frm.firmz as f', 'f.frm_id', '=', 'q.frm_id')
            ->join('purnew.rfq_items as ri', 'ri.rfq_item_id', '=', 'q.rfq_item_id')
            ->select('q.*', 'f.frm_name', 'ri.item_id')
            ->where('q.rfq_id', $rfqId)
            ->orderBy('q.frm_id')
            ->get();

        return response()->json($quotes);
    }

    public function saveQuotation(Request $request)
    {
        $request->validate([
            'rfq_id' => 'required|integer',
            'rfq_item_id' => 'required|integer',
            'frm_id' => 'required|integer',
            'quoted_price' => 'required|numeric',
        ]);

        DB::statement("
            INSERT INTO purnew.rfq_quotes (rfq_id, rfq_item_id, frm_id, quoted_price, created_at)
            VALUES (?, ?, ?, ?, NOW())
            ON CONFLICT (rfq_id, rfq_item_id, frm_id)
            DO UPDATE SET quoted_price = EXCLUDED.quoted_price
        ", [
            $request->rfq_id,
            $request->rfq_item_id,
            $request->frm_id,
            $request->quoted_price,
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteQuotationColumn(Request $request)
    {
        $request->validate([
            'rfq_id' => 'required|integer',
            'frm_id' => 'required|integer',
        ]);

        DB::table('purnew.rfq_quotes')
            ->where('rfq_id', $request->rfq_id)
            ->where('frm_id', $request->frm_id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function acceptQuote(Request $request)
    {
        $request->validate([
            'rfq_id' => 'required|integer',
            'frm_id' => 'required|integer',
        ]);

        // Reset all accepted flags for this RFQ
        DB::table('purnew.rfq_quotes')
            ->where('rfq_id', $request->rfq_id)
            ->update(['is_accepted' => false]);

        // Set accepted for chosen vendor
        DB::table('purnew.rfq_quotes')
            ->where('rfq_id', $request->rfq_id)
            ->where('frm_id', $request->frm_id)
            ->update(['is_accepted' => true]);

        // Update rfq_items.price with the accepted vendor's quoted_price
        $acceptedQuotes = DB::table('purnew.rfq_quotes')
            ->where('rfq_id', $request->rfq_id)
            ->where('frm_id', $request->frm_id)
            ->get();

        foreach ($acceptedQuotes as $q) {
            DB::table('purnew.rfq_items')
                ->where('rfq_item_id', $q->rfq_item_id)
                ->update(['price' => $q->quoted_price]);
        }

        return response()->json(['success' => true]);
    }

    public function acceptItemQuote(Request $request)
    {
        $request->validate([
            'rfq_item_id' => 'required|integer',
            'frm_id' => 'required|integer',
        ]);

        $rfqItemId = $request->rfq_item_id;
        $rfqId = DB::table('purnew.rfq_items')->where('rfq_item_id', $rfqItemId)->value('rfq_id');

        if (!$rfqId) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        // Reset all accepted flags for this specific item
        DB::table('purnew.rfq_quotes')
            ->where('rfq_id', $rfqId)
            ->where('rfq_item_id', $rfqItemId)
            ->update(['is_accepted' => false]);

        if ($request->frm_id > 0) {
            DB::table('purnew.rfq_quotes')
                ->where('rfq_id', $rfqId)
                ->where('rfq_item_id', $rfqItemId)
                ->where('frm_id', $request->frm_id)
                ->update(['is_accepted' => true]);

            $quotedPrice = DB::table('purnew.rfq_quotes')
                ->where('rfq_id', $rfqId)
                ->where('rfq_item_id', $rfqItemId)
                ->where('frm_id', $request->frm_id)
                ->value('quoted_price');

            DB::table('purnew.rfq_items')
                ->where('rfq_item_id', $rfqItemId)
                ->update(['price' => $quotedPrice]);
        } else {
            // Unassigned fallback back to 0
             DB::table('purnew.rfq_items')
                ->where('rfq_item_id', $rfqItemId)
                ->update(['price' => 0]);
        }

        return response()->json(['success' => true]);
    }
}
