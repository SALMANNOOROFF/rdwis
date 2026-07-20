<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of commitments.
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'Awaited');

        $query = DB::table('fin.commitments as c')
            ->leftJoin('pur.purcases as p', 'c.cmt_docid', '=', 'p.pcs_id')
            ->leftJoin('cen.heads as h', 'c.cmt_effhed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'c.cmt_unt_id', '=', 'u.unt_id')
            ->select(
                'c.*',
                'p.pcs_title',
                'p.pcs_price',
                'p.pcs_transtype',
                'p.pcs_noloan',
                'h.hed_code',
                'h.hed_name',
                'u.unt_namesh'
            );

        if ($status !== 'All') {
            $query->where('c.cmt_status', $status);
        }

        $commitments = $query->orderBy('c.cmt_id', 'desc')->paginate(20);

        return view('finance.payments.index', compact('commitments', 'status'));
    }

    /**
     * Show detail and payment entry form for a commitment.
     */
    public function show($cmt_id)
    {
        $commitment = DB::table('fin.commitments as c')
            ->leftJoin('pur.purcases as p', 'c.cmt_docid', '=', 'p.pcs_id')
            ->leftJoin('cen.heads as h', 'c.cmt_effhed_id', '=', 'h.hed_id')
            ->leftJoin('cen.units as u', 'c.cmt_unt_id', '=', 'u.unt_id')
            ->select(
                'c.*',
                'p.pcs_title',
                'p.pcs_price',
                'p.pcs_transtype',
                'p.pcs_noloan',
                'h.hed_code',
                'h.hed_name',
                'u.unt_namesh'
            )
            ->where('c.cmt_id', $cmt_id)
            ->firstOrFail();

        $transactions = DB::table('fin.transactions')
            ->where('trn_cmt_id', $cmt_id)
            ->orderBy('trn_seq', 'asc')
            ->get();

        $totalPaid = $transactions->sum(function ($t) {
            return abs($t->trn_amount2);
        });

        $commitmentAmount = abs($commitment->cmt_amount);
        $remainingAmount = max(0, $commitmentAmount - $totalPaid);

        return view('finance.payments.show', compact('commitment', 'transactions', 'totalPaid', 'commitmentAmount', 'remainingAmount'));
    }

    /**
     * Record a payment transaction against a commitment.
     */
    public function storeTransaction(Request $request, $cmt_id)
    {
        $request->validate([
            'trn_date' => 'required|date',
            'amount'   => 'required|numeric|gt:0',
            'tax'      => 'nullable|numeric|gte:0',
            'is_complete' => 'nullable|boolean',
        ]);

        $amount = (float) $request->amount;
        $tax = (float) ($request->tax ?? 0);
        $isComplete = $request->boolean('is_complete');

        DB::transaction(function () use ($cmt_id, $request, $amount, $tax, $isComplete) {
            $commitment = DB::table('fin.commitments')->where('cmt_id', $cmt_id)->first();
            if (!$commitment) {
                throw new \Exception('Commitment record not found.');
            }

            $purchase = DB::table('pur.purcases')->where('pcs_id', $commitment->cmt_docid)->first();

            $lastSeq = DB::table('fin.transactions')
                ->where('trn_cmt_id', $cmt_id)
                ->max('trn_seq') ?? 0;

            DB::table('fin.transactions')->insert([
                'trn_cmt_id'    => $cmt_id,
                'trn_date'      => $request->trn_date,
                'trn_amount1'   => -1 * $amount,
                'trn_tax1'      => -1 * $tax,
                'trn_amount2'   => -1 * ($amount + $tax),
                'trn_balance'   => 0,
                'trn_seq'       => $lastSeq + 1,
                'trn_transtype' => $purchase->pcs_transtype ?? 1,
                'trn_noloan'    => $purchase->pcs_noloan ?? false,
            ]);

            if ($isComplete) {
                DB::table('fin.commitments')
                    ->where('cmt_id', $cmt_id)
                    ->update(['cmt_status' => 'Paid']);
            }
        });

        return redirect()->route('fin.payments.show', $cmt_id)->with('success', 'Payment transaction recorded successfully!');
    }
}
