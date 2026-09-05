<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Ensure only authenticated Finance Directorate users can access Commitments & Payments.
     */
    private function ensureFinanceAuthorized(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        if ($userArea !== 'fin' && $user->acc_username !== 'superadminrdw') {
            abort(403, 'Unauthorized. Only Finance Directorate is authorized to access Commitments & Payments.');
        }
    }

    /**
     * Commitments Landing (Redirects to main Purchase Case Commitments list).
     */
    public function landing()
    {
        $this->ensureFinanceAuthorized();
        return redirect()->route('fin.payments.index');
    }

    /**
     * Display a listing of purchase case commitments (fin_commitments_u_pcs equivalent).
     * Two tabs: Open (Awaited) and Closed (Paid, Cancelled).
     */
    public function index(Request $request)
    {
        $this->ensureFinanceAuthorized();
        $user = Auth::user();
        [$lower, $upper] = $this->getUserHorizon($user);

        $tab = $request->get('tab', 'Open'); // 'Open' or 'Closed'
        $unitFilter = $request->get('unit_id', 'All');
        $search = trim($request->get('search', ''));

        $typeFilter = $request->get('type', 'purchase'); // 'purchase' or 'salary'

        $query = DB::table('fin.commitments as c');

        if ($typeFilter === 'salary') {
            $query->join('fin.salorders as s', function ($join) {
                $join->on('c.cmt_docid', '=', 's.sor_id')
                     ->where('c.cmt_type', '=', 'Sa');
            })
            ->leftJoin('cen.heads as eh', 'c.cmt_effhed_id', '=', 'eh.hed_id')
            ->leftJoin('cen.units as eu', 'eh.hed_unt_id', '=', 'eu.unt_id')
            ->leftJoin('cen.heads as fh', 'c.cmt_hed_id', '=', 'fh.hed_id')
            ->leftJoin('cen.units as fu', 'fh.hed_unt_id', '=', 'fu.unt_id')
            ->leftJoin('cen.units as su', 's.sor_effunt_id', '=', 'su.unt_id')
            ->where(function ($w) use ($lower, $upper) {
                $w->whereBetween('s.sor_effunt_id', [$lower, $upper])
                  ->orWhereBetween('s.sor_unt_id', [$lower, $upper]);
            })
            ->select(
                'c.cmt_id',
                'c.cmt_docid',
                'c.cmt_type',
                'c.cmt_date',
                'c.cmt_amount',
                'c.cmt_status',
                'c.cmt_effhed_id',
                'c.cmt_hed_id',
                'c.cmt_remarks',
                's.sor_id as pcs_id',
                DB::raw("'Salary: ' || s.sor_empnamecomp || ' (' || to_char(s.sor_month, 'Mon-YYYY') || ')' as pcs_title"),
                DB::raw("'Sa' as pcs_type"),
                's.sor_id as pcs_minute',
                's.sor_month as pcs_date',
                's.sor_salary as pcs_intprice',
                DB::raw("0 as pcs_inttax"),
                's.sor_salary as pcs_midprice',
                DB::raw("0 as pcs_midtax"),
                's.sor_salary as pcs_price',
                's.sor_transtype as pcs_transtype',
                's.sor_noloan as pcs_noloan',
                's.sor_effunt_id as pcs_intunt_id',
                'eh.hed_code as eff_hed_code',
                'eh.hed_name as eff_hed_name',
                'eu.unt_namesh as eff_unt_namesh',
                'fh.hed_code as for_hed_code',
                'fh.hed_name as for_hed_name',
                'fu.unt_namesh as for_unt_namesh',
                'su.unt_namesh as int_unt_namesh',
                's.sor_bnkacctitle as frm_name'
            );
        } else {
            $query->join('pur.purcases as p', function ($join) {
                $join->on('c.cmt_docid', '=', 'p.pcs_id')
                     ->on('c.cmt_type', '=', 'p.pcs_type');
            })
            ->leftJoin('cen.heads as eh', 'c.cmt_effhed_id', '=', 'eh.hed_id')
            ->leftJoin('cen.units as eu', 'eh.hed_unt_id', '=', 'eu.unt_id')
            ->leftJoin('cen.heads as fh', 'c.cmt_hed_id', '=', 'fh.hed_id')
            ->leftJoin('cen.units as fu', 'fh.hed_unt_id', '=', 'fu.unt_id')
            ->leftJoin('cen.units as iu', 'p.pcs_intunt_id', '=', 'iu.unt_id')
            ->leftJoin('frm.firmz as f', 'p.pcs_frm_id', '=', 'f.frm_id')
            ->whereBetween('p.pcs_intunt_id', [$lower, $upper])
            ->select(
                'c.cmt_id',
                'c.cmt_docid',
                'c.cmt_type',
                'c.cmt_date',
                'c.cmt_amount',
                'c.cmt_status',
                'c.cmt_effhed_id',
                'c.cmt_hed_id',
                'c.cmt_remarks',
                'p.pcs_id',
                'p.pcs_title',
                'p.pcs_type',
                'p.pcs_minute',
                'p.pcs_date',
                'p.pcs_intprice',
                'p.pcs_inttax',
                'p.pcs_midprice',
                'p.pcs_midtax',
                'p.pcs_price',
                'p.pcs_transtype',
                'p.pcs_noloan',
                'p.pcs_intunt_id',
                'eh.hed_code as eff_hed_code',
                'eh.hed_name as eff_hed_name',
                'eu.unt_namesh as eff_unt_namesh',
                'fh.hed_code as for_hed_code',
                'fh.hed_name as for_hed_name',
                'fu.unt_namesh as for_unt_namesh',
                'iu.unt_namesh as int_unt_namesh',
                'f.frm_name'
            );
        }

        // Tab filter: Open = Awaited, Closed = Paid + Cancelled
        if ($tab === 'Closed') {
            $query->whereIn('c.cmt_status', ['Paid', 'Cancelled']);
        } else {
            $tab = 'Open';
            $query->where('c.cmt_status', 'Awaited');
        }

        // Division / Unit filter (interactive narrowing within user's horizon)
        if ($unitFilter !== 'All' && is_numeric($unitFilter)) {
            if ($typeFilter === 'salary') {
                $query->where(function ($q) use ($unitFilter) {
                    $q->where('s.sor_effunt_id', (int)$unitFilter)
                      ->orWhere('s.sor_unt_id', (int)$unitFilter);
                });
            } else {
                $query->where('p.pcs_intunt_id', (int)$unitFilter);
            }
        }

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search, $typeFilter) {
                if ($typeFilter === 'salary') {
                    $q->where('s.sor_empnamecomp', 'ILIKE', "%{$search}%")
                      ->orWhere('s.sor_emp_id', 'ILIKE', "%{$search}%")
                      ->orWhere('s.sor_bnkacctitle', 'ILIKE', "%{$search}%");
                } else {
                    $q->where('p.pcs_title', 'ILIKE', "%{$search}%")
                      ->orWhere('f.frm_name', 'ILIKE', "%{$search}%");
                }
                $q->orWhere('eh.hed_code', 'ILIKE', "%{$search}%")
                  ->orWhere('eh.hed_name', 'ILIKE', "%{$search}%")
                  ->orWhere('fh.hed_code', 'ILIKE', "%{$search}%")
                  ->orWhere('fh.hed_name', 'ILIKE', "%{$search}%");

                if (is_numeric($search)) {
                    if ($typeFilter === 'salary') {
                        $q->orWhere('s.sor_id', (int)$search);
                    } else {
                        $q->orWhere('p.pcs_id', (int)$search)
                          ->orWhere('p.pcs_minute', (int)$search);
                    }
                    $q->orWhere('c.cmt_id', (int)$search);
                }
            });
        }

        // Counts for tab badges
        if ($typeFilter === 'salary') {
            $openCount = DB::table('fin.commitments as c')
                ->join('fin.salorders as s', function ($join) {
                    $join->on('c.cmt_docid', '=', 's.sor_id')
                         ->where('c.cmt_type', '=', 'Sa');
                })
                ->where(function ($w) use ($lower, $upper) {
                    $w->whereBetween('s.sor_effunt_id', [$lower, $upper])
                      ->orWhereBetween('s.sor_unt_id', [$lower, $upper]);
                })
                ->where('c.cmt_status', 'Awaited')
                ->count();

            $closedCount = DB::table('fin.commitments as c')
                ->join('fin.salorders as s', function ($join) {
                    $join->on('c.cmt_docid', '=', 's.sor_id')
                         ->where('c.cmt_type', '=', 'Sa');
                })
                ->where(function ($w) use ($lower, $upper) {
                    $w->whereBetween('s.sor_effunt_id', [$lower, $upper])
                      ->orWhereBetween('s.sor_unt_id', [$lower, $upper]);
                })
                ->whereIn('c.cmt_status', ['Paid', 'Cancelled'])
                ->count();
        } else {
            $openCount = DB::table('fin.commitments as c')
                ->join('pur.purcases as p', function ($join) {
                    $join->on('c.cmt_docid', '=', 'p.pcs_id')
                         ->on('c.cmt_type', '=', 'p.pcs_type');
                })
                ->whereBetween('p.pcs_intunt_id', [$lower, $upper])
                ->where('c.cmt_status', 'Awaited')
                ->count();

            $closedCount = DB::table('fin.commitments as c')
                ->join('pur.purcases as p', function ($join) {
                    $join->on('c.cmt_docid', '=', 'p.pcs_id')
                         ->on('c.cmt_type', '=', 'p.pcs_type');
                })
                ->whereBetween('p.pcs_intunt_id', [$lower, $upper])
                ->whereIn('c.cmt_status', ['Paid', 'Cancelled'])
                ->count();
        }

        // Available units within horizon for dropdown
        $units = DB::table('cen.units')
            ->whereBetween('unt_id', [$lower, $upper])
            ->orderBy('unt_namesh')
            ->get();

        $commitments = $query->orderBy('c.cmt_date', 'desc')
                             ->orderBy('c.cmt_id', 'desc')
                             ->paginate(20);

        return view('finance.payments.index', compact(
            'commitments',
            'tab',
            'unitFilter',
            'search',
            'units',
            'openCount',
            'closedCount'
        ));
    }

    /**
     * Show detail and payment entry form for a commitment (fin_commitments_u_pcs_detail equivalent).
     */
    public function show($cmt_id)
    {
        $this->ensureFinanceAuthorized();
        $commitment = DB::table('fin.commitments as c')
            ->leftJoin('pur.purcases as p', function ($join) {
                $join->on('c.cmt_docid', '=', 'p.pcs_id')
                     ->on('c.cmt_type', '=', 'p.pcs_type');
            })
            ->leftJoin('fin.salorders as s', function ($join) {
                $join->on('c.cmt_docid', '=', 's.sor_id')
                     ->where('c.cmt_type', '=', 'Sa');
            })
            ->leftJoin('cen.heads as eh', 'c.cmt_effhed_id', '=', 'eh.hed_id')
            ->leftJoin('cen.units as eu', 'eh.hed_unt_id', '=', 'eu.unt_id')
            ->leftJoin('cen.heads as fh', 'c.cmt_hed_id', '=', 'fh.hed_id')
            ->leftJoin('cen.units as fu', 'fh.hed_unt_id', '=', 'fu.unt_id')
            ->leftJoin('cen.units as iu', 'p.pcs_intunt_id', '=', 'iu.unt_id')
            ->leftJoin('cen.units as su', 's.sor_effunt_id', '=', 'su.unt_id')
            ->leftJoin('frm.firmz as f', 'p.pcs_frm_id', '=', 'f.frm_id')
            ->select(
                'c.*',
                DB::raw("COALESCE(p.pcs_id, s.sor_id) as pcs_id"),
                DB::raw("COALESCE(p.pcs_title, 'Salary: ' || s.sor_empnamecomp || ' (' || to_char(s.sor_month, 'Mon-YYYY') || ')') as pcs_title"),
                DB::raw("COALESCE(p.pcs_minute, s.sor_id) as pcs_minute"),
                DB::raw("COALESCE(p.pcs_date, s.sor_month) as pcs_date"),
                DB::raw("COALESCE(p.pcs_type, s.sor_type, c.cmt_type) as pcs_type"),
                DB::raw("COALESCE(p.pcs_transtype, s.sor_transtype, 1) as pcs_transtype"),
                DB::raw("COALESCE(p.pcs_noloan, s.sor_noloan, false) as pcs_noloan"),
                DB::raw("COALESCE(p.pcs_intprice, s.sor_salary) as pcs_intprice"),
                DB::raw("COALESCE(p.pcs_inttax, 0) as pcs_inttax"),
                DB::raw("COALESCE(p.pcs_midprice, s.sor_salary) as pcs_midprice"),
                DB::raw("COALESCE(p.pcs_midtax, 0) as pcs_midtax"),
                DB::raw("COALESCE(p.pcs_price, s.sor_salary) as pcs_price"),
                'eh.hed_code as eff_hed_code',
                'eh.hed_name as eff_hed_name',
                'eu.unt_namesh as eff_unt_namesh',
                'fh.hed_code as for_hed_code',
                'fh.hed_name as for_hed_name',
                'fu.unt_namesh as for_unt_namesh',
                DB::raw("COALESCE(iu.unt_namesh, su.unt_namesh) as int_unt_namesh"),
                DB::raw("COALESCE(f.frm_name, s.sor_bnkacctitle) as frm_name")
            )
            ->where('c.cmt_id', $cmt_id)
            ->first();

        if (!$commitment) {
            return redirect()->route('fin.payments.index')->with('error', 'Commitment record not found.');
        }

        // Transactions list for this commitment
        $transactions = DB::table('fin.transactions')
            ->where('trn_cmt_id', $cmt_id)
            ->orderBy('trn_seq', 'asc')
            ->get();

        // Sanctioned case figures. Legacy's canonical document mapping (Queries/fin_docs_ipc1.sql)
        // is pcs_midprice => amount1, pcs_midtax => tax1, pcs_price => amount2 — the same triple
        // fin.transactions stores. pcs_intprice/pcs_inttax are the INITIATION estimate (tax is
        // usually still 0 there), never the sanctioned amount, so they must not be used here.
        $pa = (float)($commitment->pcs_midprice ?? 0);
        $pt = (float)($commitment->pcs_midtax ?? 0);
        $pat = (float)($commitment->pcs_price ?: ($pa + $pt));

        // Sum already paid (absolute values since stored negative)
        $aa = (float)$transactions->sum(fn($t) => abs((float)$t->trn_amount1));
        $at = (float)$transactions->sum(fn($t) => abs((float)$t->trn_tax1));
        $aat = (float)$transactions->sum(fn($t) => abs((float)$t->trn_amount2));

        // Remaining 1
        $ra1 = max(0, $pa - $aa);
        $rt1 = max(0, $pt - $at);
        $rat1 = max(0, $pat - $aat);

        return view('finance.payments.show', compact(
            'commitment',
            'transactions',
            'pa',
            'pt',
            'pat',
            'aa',
            'at',
            'aat',
            'ra1',
            'rt1',
            'rat1'
        ));
    }

    /**
     * Record a payment transaction and/or close commitment (cmdSettle in legacy).
     */
    public function storeTransaction(Request $request, $cmt_id)
    {
        $this->ensureFinanceAuthorized();
        $commitment = DB::table('fin.commitments as c')
            ->leftJoin('pur.purcases as p', function ($join) {
                $join->on('c.cmt_docid', '=', 'p.pcs_id')
                     ->on('c.cmt_type', '=', 'p.pcs_type');
            })
            ->leftJoin('fin.salorders as s', function ($join) {
                $join->on('c.cmt_docid', '=', 's.sor_id')
                     ->where('c.cmt_type', '=', 'Sa');
            })
            ->where('c.cmt_id', $cmt_id)
            ->select(
                'c.*',
                DB::raw("COALESCE(p.pcs_transtype, s.sor_transtype, 1) as pcs_transtype"),
                DB::raw("COALESCE(p.pcs_noloan, s.sor_noloan, false) as pcs_noloan"),
                DB::raw("COALESCE(p.pcs_intprice, s.sor_salary) as pcs_intprice"),
                DB::raw("COALESCE(p.pcs_inttax, 0) as pcs_inttax"),
                DB::raw("COALESCE(p.pcs_midprice, s.sor_salary) as pcs_midprice"),
                DB::raw("COALESCE(p.pcs_midtax, 0) as pcs_midtax"),
                DB::raw("COALESCE(p.pcs_price, s.sor_salary) as pcs_price")
            )
            ->first();

        if (!$commitment) {
            return back()->with('error', 'Commitment record not found.');
        }

        $request->validate([
            'trn_date'     => 'nullable|date|before_or_equal:today',
            'amount'       => 'nullable|numeric|min:0',
            'tax'          => 'nullable|numeric|min:0',
            'is_complete'  => 'nullable|boolean',
            'cmt_remarks'  => 'nullable|string',
        ], [
            'trn_date.before_or_equal' => 'The disbursement date cannot be in the future.',
            'trn_date.date'            => 'Please enter a valid disbursement date.',
        ], [
            'trn_date' => 'disbursement date',
            'amount'   => 'payment amount',
            'tax'      => 'tax amount',
        ]);

        $hasAmount = $request->filled('amount') && (float)$request->amount > 0;
        $hasTax = $request->filled('tax') && (float)$request->tax > 0;
        $isComplete = $request->boolean('is_complete');
        $remarks = $request->input('cmt_remarks');

        // Check if nothing to do
        if (!$hasAmount && !$hasTax && !$isComplete && $remarks === $commitment->cmt_remarks) {
            return back()->with('error', 'Please enter a payment amount or select Close Commitment.');
        }

        // If payment values are provided, date is required
        if (($hasAmount || $hasTax) && !$request->filled('trn_date')) {
            return back()->with('error', 'Disbursement date is required when adding a payment installment.');
        }

        $amount = (float)($request->amount ?? 0);
        $tax = (float)($request->tax ?? 0);
        $installmentTotal = $amount + $tax;

        // Remaining balance check. The sanctioned total is TAX-INCLUSIVE (pcs_price =
        // pcs_midprice + pcs_midtax), whereas cmt_amount is PRE-tax for transtype 1
        // (Queries/aud_chk_purcases-commitments.sql). Capping an amount+tax installment
        // against cmt_amount would reject every legitimate full settlement of a taxed case.
        $currentTransactions = DB::table('fin.transactions')->where('trn_cmt_id', $cmt_id)->get();
        $totalAlreadyPaid = (float)$currentTransactions->sum(fn($t) => abs((float)$t->trn_amount2));
        $sanctionedTotal = (float)($commitment->pcs_price
            ?: (abs((float)$commitment->cmt_amount) + (float)($commitment->pcs_midtax ?? 0)));
        $remainingBalance = max(0, $sanctionedTotal - $totalAlreadyPaid);

        // Legacy never blocked an over-payment; Queries/aud_chk_purcases-transactions.sql only
        // *flags* anything outside ±10% of the sanctioned figure, and real history has 112
        // commitments settled above pcs_price (extra withholding tax, roundings). So allow up
        // to 110% with a warning and block only beyond that.
        $hardCeiling = $sanctionedTotal * 1.10;
        $paidAfterThis = $totalAlreadyPaid + $installmentTotal;

        if ($installmentTotal > 0 && $sanctionedTotal > 0 && $paidAfterThis > ($hardCeiling + 0.50)) {
            return back()->with('error', 'Payment of PKR ' . number_format($installmentTotal, 2)
                . ' would take the total paid to PKR ' . number_format($paidAfterThis, 2)
                . ', beyond 110% of the sanctioned total (PKR ' . number_format($sanctionedTotal, 2)
                . '). Correct the amount or raise a Data Revision on the case.');
        }

        $overSanction = $installmentTotal > 0 && $sanctionedTotal > 0
            && $paidAfterThis > ($sanctionedTotal + 0.50);

        DB::transaction(function () use ($cmt_id, $commitment, $request, $amount, $tax, $installmentTotal, $isComplete, $remarks) {
            // 1. Insert transaction if amount/tax was entered
            if ($installmentTotal > 0) {
                $lastSeq = DB::table('fin.transactions')
                    ->where('trn_cmt_id', $cmt_id)
                    ->max('trn_seq') ?? 0;

                DB::table('fin.transactions')->insert([
                    'trn_cmt_id'    => $cmt_id,
                    'trn_date'      => $request->trn_date,
                    'trn_amount1'   => -1 * abs($amount),
                    'trn_tax1'      => -1 * abs($tax),
                    'trn_amount2'   => -1 * abs($installmentTotal),
                    'trn_balance'   => 0,
                    'trn_seq'       => $lastSeq + 1,
                    'trn_transtype' => $commitment->pcs_transtype ?? 1,
                    'trn_noloan'    => $commitment->pcs_noloan ?? false,
                ]);
            }

            // 2. Update commitment status and remarks
            $updates = [];
            if ($isComplete) {
                $updates['cmt_status'] = 'Paid';
            }
            if ($remarks !== null) {
                $updates['cmt_remarks'] = $remarks;
            }

            if (!empty($updates)) {
                DB::table('fin.commitments')
                    ->where('cmt_id', $cmt_id)
                    ->update($updates);
            }

            // 3. If a salary commitment is settled as Paid, sync fin.salorders and hr.salreqs
            // (Legacy: fin_commitments_u_so.bas:70-85)
            if ($isComplete && $commitment->cmt_type === 'Sa') {
                $sor = DB::table('fin.salorders')->where('sor_id', $commitment->cmt_docid)->first();
                if ($sor) {
                    DB::table('fin.salorders')->where('sor_id', $sor->sor_id)->update([
                        'sor_status'   => 'Fulfilled',
                        'sor_closedtg' => now(),
                    ]);
                    if ($sor->sor_srq_id) {
                        DB::table('hr.salreqs')->where('srq_id', $sor->sor_srq_id)->update([
                            'srq_fulfilment' => $sor->sor_salary,
                            'srq_status'     => 'Fulfilled',
                            'srq_closedtg'   => now(),
                        ]);
                    }
                }
            }
        });

        $msg = $installmentTotal > 0
            ? 'Payment transaction of PKR ' . number_format($installmentTotal, 2) . ' recorded successfully.'
            : 'Commitment updated successfully.';

        if ($isComplete) {
            $msg .= ' Commitment marked as Paid.';
        }

        if ($overSanction) {
            $msg .= ' NOTE: total paid (PKR ' . number_format($paidAfterThis, 2)
                . ') now exceeds the sanctioned total (PKR ' . number_format($sanctionedTotal, 2) . ').';
        }

        return redirect()->route('fin.payments.show', $cmt_id)->with('success', $msg);
    }

    /**
     * Screen for Salary Order Commitments (equivalent to legacy fin_commitments_u_so).
     */
    public function salaryPlaceholder(Request $request)
    {
        $this->ensureFinanceAuthorized();
        return redirect()->route('fin.payments.index', array_merge($request->all(), ['type' => 'salary']));
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
        $isHqOrFinance = in_array($userArea, ['fin', 'rdw', 'hqs', 'nrdi', 'it', 'rdwprj', 'prjrdw'], true);

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
