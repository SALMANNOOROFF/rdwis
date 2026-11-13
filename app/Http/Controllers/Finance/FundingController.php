<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Finance: funding receipts and milestone costs of a project head.
 *
 * Port of the legacy Access forms:
 *   - fin_sharesinstall_add  (record a funding installment / receipt)
 *   - fin_msncosts_plus      (milestone-wise cost break-up of the allocation)
 *
 * Tables and figures are exactly the legacy ones:
 *   alloc       = fin.commitments(cmt_id = sha_ficmt_id).cmt_amount
 *   mtss_share  = -1 * fin.commitments(cmt_id = sha_focmt_id).cmt_amount
 *   installment = fin.transactions (FI) + fin.transactions (FO, negative) + fin.sharesinstall
 */
class FundingController extends Controller
{
    /**
     * Finance department (or super admin) only.
     */
    protected function guard(): void
    {
        $user = Auth::user();
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));

        $isFinance = in_array($area, ['fin', 'finance'], true)
            || (method_exists($user, 'isFinance') && $user->isFinance())
            || (int) ($user->acc_unt_id ?? 0) === 800000
            || ($user->acc_username ?? '') === 'superadminrdw';

        if (! $isFinance) {
            abort(403, 'Only the Finance department can record fundings.');
        }
    }

    // =====================================================================
    // SCREEN
    // =====================================================================

    public function index($hedId)
    {
        $this->guard();

        $data = $this->headData((int) $hedId);

        return view('finance.fundings.index', $data);
    }

    /**
     * Everything the screen (and the validations) need for one head.
     */
    protected function headData(int $hedId): array
    {
        $head = DB::table('cen.heads')->where('hed_id', $hedId)->first();
        if (! $head) {
            abort(404, 'Account head not found.');
        }

        $projectId = (int) ($head->hed_prj_id ?: $hedId);
        $project = DB::table('prj.projects')->where('prj_id', $projectId)->first();

        $shares = DB::table('fin.sharesalloc')->where('sha_hed_id', $hedId)->first();

        $alloc = 0.0;
        $mtssShare = 0.0;
        if ($shares) {
            $alloc = (float) DB::table('fin.commitments')->where('cmt_id', $shares->sha_ficmt_id)->value('cmt_amount');
            if (! empty($shares->sha_focmt_id)) {
                $mtssShare = -1 * (float) DB::table('fin.commitments')->where('cmt_id', $shares->sha_focmt_id)->value('cmt_amount');
            }
        }

        $accShare = $alloc - $mtssShare;
        $pccShare = (float) ($shares->sha_pcc ?? 0);
        $cfShare  = (float) ($shares->sha_cf ?? 0);

        // Legacy fin_sharesinstall_plus
        $installments = DB::table('fin.sharesinstall as shi')
            ->join('fin.transactions as t', 't.trn_id', '=', 'shi.shi_fitrn_id')
            ->leftJoin('fin.transactions as t2', 't2.trn_id', '=', 'shi.shi_fotrn_id')
            ->leftJoin('fin.msncosts as mc', 'mc.mct_msn_idd', '=', 'shi.shi_msn_idd')
            ->leftJoin('prj.milestones as m', 'm.msn_idd', '=', 'shi.shi_msn_idd')
            ->where('shi.shi_hed_id', $hedId)
            ->orderBy('t.trn_date')
            ->orderBy('mc.mct_msn_id')
            ->select([
                'shi.shi_id', 'shi.shi_pcc', 'shi.shi_cf', 'shi.shi_msn_idd',
                't.trn_date', 't.trn_amount2 as inward',
                DB::raw('COALESCE(-1 * t2.trn_amount2, 0) as mtss_share'),
                'mc.mct_msn_id', 'mc.mct_cost',
                'm.msn_desc', 'm.msn_status',
            ])
            ->get();

        $inwardSum = (float) $installments->sum(fn ($r) => (float) $r->inward);
        $mtssSum   = (float) $installments->sum(fn ($r) => (float) $r->mtss_share);
        $pccSum    = (float) $installments->sum(fn ($r) => (float) $r->shi_pcc);
        $cfSum     = (float) $installments->sum(fn ($r) => (float) $r->shi_cf);
        $accSum    = $inwardSum - $mtssSum;

        // Milestone-wise costs (legacy fin_msncosts_plus)
        $milestones = DB::table('prj.milestones as m')
            ->leftJoin('fin.msncosts as mc', function ($join) use ($hedId) {
                $join->on('mc.mct_msn_idd', '=', 'm.msn_idd')->where('mc.mct_hed_id', '=', $hedId);
            })
            ->where('m.msn_xprj_id', $projectId)
            ->orderBy('m.msn_id')
            ->select(['m.msn_idd', 'm.msn_id', 'm.msn_desc', 'm.msn_status', 'm.msn_targetdt', 'mc.mct_cost'])
            ->get();

        $costTotal = (float) $milestones->sum(fn ($m) => (float) $m->mct_cost);
        $costsLocked = $milestones->contains(fn ($m) => (float) $m->mct_cost > 0);

        return [
            'head'         => $head,
            'project'      => $project,
            'shares'       => $shares,
            'alloc'        => $alloc,
            'mtssShare'    => $mtssShare,
            'accShare'     => $accShare,
            'pccShare'     => $pccShare,
            'cfShare'      => $cfShare,
            'inwardSum'    => $inwardSum,
            'mtssSum'      => $mtssSum,
            'accSum'       => $accSum,
            'pccSum'       => $pccSum,
            'cfSum'        => $cfSum,
            'ovrYtbr'      => $alloc - $inwardSum,
            'accYtbr'      => $accShare - $accSum,
            'pccYtbr'      => $pccShare - $pccSum,
            'cfYtbr'       => $cfShare - $cfSum,
            'installments' => $installments,
            'milestones'   => $milestones,
            'costTotal'    => $costTotal,
            'costsLocked'  => $costsLocked,
        ];
    }

    // =====================================================================
    // ADD FUNDING  (legacy cmdSettle_Click)
    // =====================================================================

    public function store(Request $request, $hedId)
    {
        $this->guard();

        $hedId = (int) $hedId;
        $d = $this->headData($hedId);

        if (! $d['shares']) {
            return back()->with('error', 'This head has no share allocation yet. Open the account first.');
        }

        $request->validate([
            'trn_date'   => 'required|date|before_or_equal:today',
            'ovr_amount' => 'required|numeric',
            'acc_amount' => 'required|numeric',
            'pcc_amount' => 'nullable|numeric',
            'cf_amount'  => 'nullable|numeric',
            'milestone'  => 'nullable|integer',
        ]);

        $ovr = (float) $request->input('ovr_amount');
        $acc = (float) $request->input('acc_amount');
        $pcc = (float) $request->input('pcc_amount', 0);
        $cf  = (float) $request->input('cf_amount', 0);
        $msnIdd = $request->filled('milestone') ? (int) $request->input('milestone') : null;

        // Legacy: negatives are not allowed
        if ($ovr < 0 || $acc < 0 || $pcc < 0 || $cf < 0) {
            return back()->withInput()->with('error', 'Negative numbers are not allowed.');
        }

        // Legacy: nothing may exceed what is yet to be received
        if (round($ovr, 2) > round($d['ovrYtbr'], 2)
            || round($acc, 2) > round($d['accYtbr'], 2)
            || round($pcc, 2) > round($d['pccYtbr'], 2)
            || round($cf, 2)  > round($d['cfYtbr'], 2)) {
            return back()->withInput()->with('error', 'Received amounts cannot be greater than the yet-to-be-received amounts.');
        }

        // Legacy: a receipt booked against a milestone may not exceed that milestone's cost
        if ($msnIdd) {
            $msnCost = (float) DB::table('fin.msncosts')
                ->where('mct_msn_idd', $msnIdd)
                ->where('mct_hed_id', $hedId)
                ->value('mct_cost');

            if ($msnCost > 0 && round($ovr, 2) > round($msnCost, 2)) {
                return back()->withInput()->with('error', 'Received amount is greater than the milestone cost.');
            }
        }

        $mtss = round($ovr - $acc, 2);
        if ($mtss < 0) {
            return back()->withInput()->with('error', 'RDW share cannot be greater than the received amount.');
        }

        try {
            DB::transaction(function () use ($request, $d, $hedId, $ovr, $acc, $pcc, $cf, $mtss, $msnIdd) {
                $shares = $d['shares'];
                $date = $request->input('trn_date');
                $transType = (int) ($shares->sha_transtype ?? 1);

                // 1. Funding transaction (money in)
                $fiTrnId = DB::table('fin.transactions')->insertGetId([
                    'trn_cmt_id'    => $shares->sha_ficmt_id,
                    'trn_date'      => $date,
                    'trn_amount1'   => $ovr,
                    'trn_tax1'      => 0,
                    'trn_amount2'   => $ovr,
                    'trn_balance'   => 0,
                    'trn_seq'       => 1,
                    'trn_transtype' => $transType,
                    'trn_noloan'    => false,
                ], 'trn_id');

                // 2. MTSS share transaction (money out)
                $foTrnId = null;
                if (! empty($shares->sha_focmt_id)) {
                    $foTrnId = DB::table('fin.transactions')->insertGetId([
                        'trn_cmt_id'    => $shares->sha_focmt_id,
                        'trn_date'      => $date,
                        'trn_amount1'   => -1 * $mtss,
                        'trn_tax1'      => 0,
                        'trn_amount2'   => -1 * $mtss,
                        'trn_balance'   => 0,
                        'trn_seq'       => 1,
                        'trn_transtype' => $transType,
                        'trn_noloan'    => false,
                    ], 'trn_id');
                }

                // 3. The installment itself
                DB::table('fin.sharesinstall')->insert([
                    'shi_hed_id'   => $hedId,
                    'shi_fitrn_id' => $fiTrnId,
                    'shi_fotrn_id' => $foTrnId,
                    'shi_pcc'      => $pcc,
                    'shi_cf'       => $cf,
                    'shi_msn_idd'  => $msnIdd,
                ]);
            });
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'The funding could not be added: ' . $e->getMessage());
        }

        return redirect()->route('finance.fundings.index', $hedId)
                         ->with('success', 'Funding of Rs. ' . number_format($ovr) . ' recorded successfully.');
    }

    // =====================================================================
    // MILESTONE COSTS  (legacy fin_msncosts_plus)
    // =====================================================================

    public function storeMilestoneCosts(Request $request, $hedId)
    {
        $this->guard();

        $hedId = (int) $hedId;
        $d = $this->headData($hedId);

        if ($d['costsLocked']) {
            return back()->with('error', 'Milestone costs are already set. Reverse them first to change the break-up.');
        }

        $request->validate([
            'costs'   => 'required|array',
            'costs.*' => 'nullable|numeric|min:0',
        ]);

        $costs = $request->input('costs', []);
        $total = 0.0;
        foreach ($costs as $value) {
            $total += (float) $value;
        }

        // Legacy: the break-up must add up to the project allocation.
        // mct_cost is an integer column while an allocation can carry paisa, so a
        // difference of less than one rupee is accepted.
        if (abs($total - (float) $d['alloc']) >= 1.0) {
            return back()->withInput()->with(
                'error',
                'The sum of milestone costs (Rs. ' . number_format($total) . ') is not equal to the project allocation (Rs. '
                . number_format((float) $d['alloc']) . '). Costs not updated.'
            );
        }

        $projectId = (int) ($d['head']->hed_prj_id ?: $hedId);

        try {
            DB::transaction(function () use ($costs, $d, $hedId, $projectId) {
                foreach ($d['milestones'] as $milestone) {
                    $value = (int) round((float) ($costs[$milestone->msn_idd] ?? 0));

                    DB::table('fin.msncosts')->updateOrInsert(
                        [
                            'mct_prj_id' => $projectId,
                            'mct_msn_id' => $milestone->msn_id,
                        ],
                        [
                            'mct_hed_id'  => $hedId,
                            'mct_msn_idd' => $milestone->msn_idd,
                            'mct_cost'    => $value,
                        ]
                    );
                }
            });
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Milestone costs could not be saved: ' . $e->getMessage());
        }

        return redirect()->route('finance.fundings.index', $hedId)
                         ->with('success', 'Milestone costs saved (total Rs. ' . number_format($total) . ').');
    }
}
