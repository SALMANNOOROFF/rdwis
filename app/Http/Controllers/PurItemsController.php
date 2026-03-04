<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PurItem;
use App\Models\PurCategory;
use App\Models\PurSubcategory;
use App\Models\PurItemPrice;
use App\Models\PurRfq;
use App\Models\PurRfqItem;

class PurItemsController extends Controller
{
    public function setup()
    {
        $path = base_path('DB RDWIS/DB/puritems-schema.sql');
        $sql = file_get_contents($path);
        DB::unprepared($sql);
        return redirect()->route('puritems.index');
    }

    public function populate()
    {
        $path = base_path('DB RDWIS/DB/puritems-import.sql');
        $sql = file_get_contents($path);
        DB::unprepared($sql);
        return redirect()->route('puritems.index');
    }

    public function index(Request $request)
    {
        $q = PurItem::query();
        if ($request->filled('cat')) $q->where('itm_cat_id', $request->integer('cat'));
        if ($request->filled('sub')) $q->where('itm_sub_id', $request->integer('sub'));
        if ($request->filled('term')) $q->whereRaw('LOWER(itm_title) LIKE ?', ['%'.strtolower((string)$request->input('term')).'%']);
        if (Auth::user() && Auth::user()->unit) $q->where('itm_unt_id', Auth::user()->unit->unt_id);
        if ($request->boolean('mine') && Auth::user()) $q->where('itm_acc_id', Auth::user()->acc_id);
        $items = $q->orderBy('itm_title')->limit(300)->with('latestPrice')->get();
        $cats = PurCategory::orderBy('cat_name')->get();
        $subs = PurSubcategory::orderBy('sub_name')->get();
        $filters = [
            'term' => (string)$request->input('term', ''),
            'cat' => $request->input('cat'),
            'sub' => $request->input('sub'),
            'mine' => $request->boolean('mine')
        ];
        return view('puritems.index', compact('items','cats','subs','filters'));
    }

    public function createItem(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'desc' => 'nullable|string',
            'qtyunit' => 'required|string',
            'cat_id' => 'nullable|integer',
            'sub_id' => 'nullable|integer',
            'base' => 'required|numeric',
            'gst' => 'nullable|numeric',
            'sst' => 'nullable|numeric'
        ]);
        $u = Auth::user();
        $item = null;
        DB::transaction(function () use ($data, $u, &$item) {
            $item = PurItem::firstOrCreate(
                [
                    'itm_title' => strtolower(trim($data['title'])),
                    'itm_qtyunit' => $data['qtyunit'],
                    'itm_unt_id' => $u && $u->unit ? $u->unit->unt_id : null,
                    'itm_hed_id' => null,
                    'itm_acc_id' => $u ? $u->acc_id : null
                ],
                [
                    'itm_desc' => $data['desc'] ?? $data['title'],
                    'itm_cat_id' => $data['cat_id'] ?? null,
                    'itm_sub_id' => $data['sub_id'] ?? null
                ]
            );
            $gst = $data['gst'] ?? 0;
            $sst = $data['sst'] ?? 0;
            $gross = ($data['base'] + $gst + $sst);
            PurItemPrice::create([
                'prc_itm_id' => $item->itm_id,
                'prc_base' => $data['base'],
                'prc_gst' => $gst,
                'prc_sst' => $sst,
                'prc_gross' => $gross,
                'prc_qty' => 1,
                'prc_qtyunit' => $data['qtyunit'],
                'effective_date' => now()->toDateString(),
            ]);
        });
        $item->load('latestPrice');
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'item' => [
                    'itm_id' => $item->itm_id,
                    'title' => $item->itm_title,
                    'qtyunit' => $item->itm_qtyunit,
                    'price' => $item->latestPrice ? (float)$item->latestPrice->prc_gross : 0
                ]
            ]);
        }
        return redirect()->route('puritems.index');
    }

    public function rfqPreview(Request $request)
    {
        $payload = $request->input('items', []);
        $total = 0;
        $rows = [];
        foreach ($payload as $row) {
            $item = PurItem::find($row['itm_id']);
            $price = null;
            if (!empty($row['prc_id'])) $price = PurItemPrice::find($row['prc_id']);
            if (!$price) $price = PurItemPrice::where('prc_itm_id', $row['itm_id'])->orderByDesc('effective_date')->first();
            $qty = isset($row['qty']) ? (float)$row['qty'] : 1;
            $priceOverride = isset($row['price']) ? (float)$row['price'] : null;
            $unit = $priceOverride !== null ? $priceOverride : ($price ? (float)$price->prc_gross : 0);
            $line = $unit * $qty;
            $total += $line;
            $rows[] = [
                'item'=>$item,
                'price'=>$price,
                'qty'=>$qty,
                'line'=>$line,
                'unit'=>$unit,
                'priceOverride'=>$priceOverride
            ];
        }
        return view('puritems.rfq_preview', compact('rows','total'));
    }

    public function rfqSubmit(Request $request)
    {
        $payload = $request->input('items', []);
        $u = Auth::user();
        $rfq = PurRfq::create([
            'rfq_title' => $request->input('title','Draft RFQ'),
            'rfq_unt_id' => $u && $u->unit ? $u->unit->unt_id : null,
            'rfq_created_by' => $u ? $u->acc_id : null,
            'rfq_status' => 'draft',
            'rfq_total' => 0
        ]);
        $total = 0;
        foreach ($payload as $row) {
            $price = null;
            if (!empty($row['prc_id'])) $price = PurItemPrice::find($row['prc_id']);
            $qty = isset($row['qty']) ? (float)$row['qty'] : 1;
            $item = PurItem::find($row['itm_id']);
            if (!$price) {
                if (isset($row['price'])) {
                    $gross = (float)$row['price'];
                    $price = PurItemPrice::create([
                        'prc_itm_id' => $item->itm_id,
                        'prc_base' => $gross,
                        'prc_gst' => 0,
                        'prc_sst' => 0,
                        'prc_gross' => $gross,
                        'prc_qty' => 1,
                        'prc_qtyunit' => $item ? $item->itm_qtyunit : 'unit',
                        'effective_date' => now()->toDateString(),
                    ]);
                } else {
                    $price = PurItemPrice::where('prc_itm_id', $row['itm_id'])->orderByDesc('effective_date')->first();
                }
            }
            $unit = isset($row['price']) ? (float)$row['price'] : ($price ? (float)$price->prc_gross : 0);
            $line = $unit * $qty;
            PurRfqItem::create([
                'rfi_rfq_id' => $rfq->rfq_id,
                'rfi_itm_id' => $row['itm_id'],
                'rfi_price_id' => $price ? $price->prc_id : null,
                'rfi_qty' => $qty,
                'rfi_total' => $line
            ]);
            $total += $line;
        }
        $rfq->rfq_total = $total;
        $rfq->save();
        return redirect()->route('puritems.index');
    }

    public function rfqList(Request $request)
    {
        $u = Auth::user();
        $q = PurRfq::query();
        if ($u && $u->unit) {
            $q->where('rfq_unt_id', $u->unit->unt_id);
        }
        if ($request->filled('term')) {
            $term = '%'.strtolower((string)$request->input('term')).'%';
            $q->whereRaw('LOWER(rfq_title) LIKE ?', [$term]);
        }
        $rfqs = $q->orderByDesc('rfq_id')->limit(200)->get();
        return view('puritems.rfq_list', compact('rfqs'));
    }
}
