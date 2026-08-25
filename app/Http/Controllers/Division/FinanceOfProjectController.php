<?php

namespace App\Http\Controllers\Division;

use App\Http\Controllers\Controller;
use App\Services\FinancialIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceOfProjectController extends Controller
{
    protected $finService;

    public function __construct(FinancialIntelligenceService $finService)
    {
        $this->finService = $finService;
    }

    /**
     * Division-level "Finance of Project" hub.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $unitId = $user->acc_unt_id;

        // Fetch all heads belonging to this division
        $heads = DB::table('cen.heads as h')
            ->join('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
            ->where('h.hed_unt_id', $unitId)
            ->select(
                'h.hed_id',
                'h.hed_prj_id',
                'h.hed_code',
                'p.prj_title',
                'p.prj_status',
                'p.prj_aprvdt'
            )
            ->orderBy('h.hed_code')
            ->get();

        // Determine selected head and active tab
        $selectedHeadId = $request->query('head_id');
        $activeTab = $request->query('tab', 'status');

        if (!$selectedHeadId && $heads->isNotEmpty() && $activeTab !== 'overview') {
            $selectedHeadId = $heads->first()->hed_id;
        }

        // 1. Compute overview list for all projects in division
        $projects = [];
        foreach ($heads as $h) {
            $fin = $this->finService->getHeadStatus($h->hed_id);

            $projects[] = [
                'head_id'    => $h->hed_id,
                'prj_id'     => $h->hed_prj_id,
                'head_code'  => $h->hed_code,
                'title'      => $h->prj_title,
                'status'     => $h->prj_status,

                // Allocation / Share
                'pcc_share'  => $fin->pcc_share ?? 0,
                'cf_share'   => $fin->cf_share ?? 0,
                'rdw_share'  => $fin->rdw_share ?? 0,
                'allocation' => $fin->allocation ?? 0,

                // Pcc Scope — 4 figures
                'pcc_received'     => $fin->pcc_received ?? 0,
                'pcc_expenditure'  => $fin->pcc_expenditure ?? 0,
                'pcc_commitments'  => $fin->pcc_commitments ?? 0,
                'pcc_in_process'   => $fin->pcc_in_process ?? 0,
                'pcc_can_be_spent' => $fin->pcc_can_be_spent ?? 0,

                // CSRF Scope — 4 figures
                'cf_received'      => $fin->cf_received ?? 0,
                'cf_expenditure'   => $fin->cf_expenditure ?? 0,
                'cf_commitments'   => $fin->cf_commitments ?? 0,
                'cf_in_process'    => $fin->cf_in_process ?? 0,
                'cf_can_be_spent'  => $fin->cf_can_be_spent ?? 0,
            ];
        }

        // 2. Compute detailed status for selected head
        $selectedHead = null;
        $finStatus = null;
        $subheadBreakdown = [];
        $loans = null;
        $milestones = collect();
        $installments = collect();
        $transfers = collect();

        if ($selectedHeadId) {
            $selectedHead = DB::table('cen.heads as h')
                ->join('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
                ->where('h.hed_id', $selectedHeadId)
                ->select(
                    'h.hed_id',
                    'h.hed_prj_id',
                    'h.hed_code',
                    'h.hed_name',
                    'h.hed_transtype',
                    'p.prj_title',
                    'p.prj_status',
                    'p.prj_aprvdt',
                    'p.prj_startdt',
                    'p.prj_enddt',
                    'p.prj_aprvcost'
                )
                ->first();

            if ($selectedHead) {
                // Core financials
                $finStatus = $this->finService->getHeadStatus($selectedHeadId);

                // Subheads breakdown (HR, Equipment, Operations, Misc, etc.)
                $subheadBreakdown = $this->finService->getSubheadBreakdown($selectedHeadId);

                // Loans (inter-project netting)
                $loans = $this->finService->getLoans($selectedHeadId);

                // Milestones & cost allocations
                $milestones = DB::table('prj.milestones as m')
                    ->leftJoin('fin.msncosts as mc', function ($join) use ($selectedHeadId) {
                        $join->on('m.msn_idd', '=', 'mc.mct_msn_idd')
                             ->where('mc.mct_hed_id', '=', $selectedHeadId);
                    })
                    ->where('m.msn_xprj_id', $selectedHead->hed_prj_id)
                    ->select(
                        'm.msn_id',
                        'm.msn_idd',
                        'm.msn_type',
                        'm.msn_desc',
                        'm.msn_status',
                        'm.msn_cost',
                        'm.msn_startdt',
                        'm.msn_targetdt',
                        'm.msn_achvdt',
                        'mc.mct_cost'
                    )
                    ->orderBy('m.msn_id')
                    ->get();

                // Installments / Fundings history
                $installments = DB::table('fin.sharesinstall')
                    ->where('shi_hed_id', $selectedHeadId)
                    ->orderBy('shi_id')
                    ->get();

                // Transfers history
                $transfers = DB::table('fin.transfers')
                    ->where('trf_tohed', $selectedHeadId)
                    ->orWhere('trf_fromhed', $selectedHeadId)
                    ->orderBy('trf_id')
                    ->get();
            }
        }

        return view('division.finance-of-project.index', compact(
            'heads',
            'projects',
            'selectedHeadId',
            'selectedHead',
            'finStatus',
            'subheadBreakdown',
            'loans',
            'milestones',
            'installments',
            'transfers',
            'activeTab'
        ));
    }

    /**
     * Complete live drill-down breakdown for any figure or subhead.
     *
     * @param int    $headId   The head (project account) ID
     * @param string $scope    'pcc', 'csrf', 'acc', 'prj', 'loans', 'subhead'
     * @param string $figure   'received', 'expenditure', 'commitments', 'in-process', etc.
     * @param string $subhead  Optional subhead name
     */
    public function drillDown($headId, $scope, $figure, $subhead = null)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Fetch head + project info for display
        $head = DB::table('cen.heads as h')
            ->join('prj.projects as p', 'p.prj_id', '=', 'h.hed_prj_id')
            ->where('h.hed_id', $headId)
            ->select('h.hed_id', 'h.hed_code', 'h.hed_name', 'h.hed_transtype', 'p.prj_title', 'p.prj_id')
            ->first();

        if (!$head) abort(404, 'Head not found');

        // Get the overall financial status for this head
        $fin = $this->finService->getHeadStatus($headId);

        $figureKey = ($scope === 'pcc' ? 'pcc_' : ($scope === 'csrf' ? 'cf_' : ($scope === 'acc' ? 'acc_' : 'prj_'))) . str_replace('-', '_', $figure);
        $currentValue = $fin->$figureKey ?? 0;

        // Human-readable labels
        $scopeLabels = [
            'pcc'     => 'Project Scope (Pcc)',
            'csrf'    => 'CSRF Scope (CF)',
            'acc'     => 'Account Scope',
            'prj'     => 'Project Scope',
            'loans'   => 'Inter-Project Loans',
            'subhead' => 'Subhead: ' . ($subhead ?: 'General'),
        ];
        $scopeLabel = $scopeLabels[$scope] ?? ucfirst($scope);
        $figureLabel = ucwords(str_replace(['-', '_'], ' ', $figure));
        if ($subhead) {
            $figureLabel .= ' (' . $subhead . ')';
        }

        $items = [];
        $breakdownType = $figure;

        // ========================================================
        // 1. RECEIVED BREAKDOWN
        // ========================================================
        if ($figure === 'received') {
            $installments = DB::table('fin.sharesinstall as shi')
                ->leftJoin('prj.milestones as m', 'm.msn_idd', '=', 'shi.shi_msn_idd')
                ->where('shi.shi_hed_id', $headId)
                ->select(
                    'shi.shi_id',
                    'shi.shi_pcc',
                    'shi.shi_cf',
                    'shi.shi_prj',
                    'shi.shi_msn_idd',
                    'm.msn_desc',
                    'm.msn_type'
                )
                ->orderBy('shi.shi_id')
                ->get();

            foreach ($installments as $idx => $inst) {
                $pccVal = (float) ($inst->shi_pcc ?? 0);
                $cfVal = (float) ($inst->shi_cf ?? 0);
                $totalVal = $pccVal + $cfVal;

                $amt = ($scope === 'pcc' ? $pccVal : ($scope === 'csrf' ? $cfVal : $totalVal));

                $items[] = (object) [
                    'id'          => $inst->shi_id,
                    'ref_no'      => 'INST-' . str_pad($inst->shi_id, 4, '0', STR_PAD_LEFT),
                    'date'        => 'Installment #' . ($idx + 1),
                    'title'       => 'Funding Installment #' . ($idx + 1) . ($inst->msn_desc ? ' (' . $inst->msn_desc . ')' : ''),
                    'subhead'     => 'Funding / Cash Inflow',
                    'vendor'      => 'Finance Directorate',
                    'pcc_amount'  => $pccVal,
                    'cf_amount'   => $cfVal,
                    'amount'      => $amt,
                    'tax'         => 0,
                    'total'       => $amt,
                    'status'      => 'Received',
                ];
            }
        }

        // ========================================================
        // 2. EXPENDITURE BREAKDOWN
        // ========================================================
        elseif ($figure === 'expenditure' || $figure === 'ownexp') {
            $expQuery = DB::table('fin.commitments as c')
                ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
                ->leftJoin('pur.purcases as pcs', function($join) {
                    $join->on('pcs.pcs_id', '=', 'c.cmt_docid')
                         ->where('c.cmt_type', '=', 'Ps');
                })
                ->leftJoin('frm.firmz as frm', 'pcs.pcs_frm_id', '=', 'frm.frm_id')
                ->leftJoin('fin.salorders as sor', function($join) {
                    $join->on('sor.sor_id', '=', 'c.cmt_docid')
                         ->where('c.cmt_type', '=', 'Sa');
                })
                ->leftJoin('fin.docs_shd as shd', function ($join) {
                    $join->on('c.cmt_docid', '=', 'shd.doc_id')
                         ->on('c.cmt_type', '=', 'shd.doc_type');
                });

            // Apply scope filtering
            if ($scope === 'pcc') {
                $expQuery->where('c.cmt_effhed_id', $headId)
                         ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO'])
                         ->where(function($q) {
                             $q->whereNull('c.cmt_sudohed')->orWhere('c.cmt_sudohed', '=', '');
                         });
            } elseif ($scope === 'csrf') {
                $expQuery->where('c.cmt_effhed_id', $headId)
                         ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa'])
                         ->whereNotNull('c.cmt_sudohed')
                         ->where('c.cmt_sudohed', '<>', '');
            } elseif ($scope === 'prj') {
                $expQuery->where('c.cmt_hed_id', $headId)
                         ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO']);
            } elseif ($scope === 'subhead') {
                $expQuery->where('c.cmt_hed_id', $headId)
                         ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO']);
                if ($subhead && $subhead !== 'Misc') {
                    $expQuery->where('shd.subhead', $subhead);
                }
            } elseif ($figure === 'ownexp') {
                $expQuery->where('c.cmt_effhed_id', $headId)
                         ->where('c.cmt_hed_id', $headId)
                         ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO']);
            } else {
                // Account level
                $expQuery->where('c.cmt_effhed_id', $headId)
                         ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO']);
            }

            $rawExp = $expQuery->select(
                't.trn_id',
                't.trn_date',
                'c.cmt_id',
                'c.cmt_docid',
                'c.cmt_type',
                'c.cmt_sudohed',
                'pcs.pcs_title',
                'sor.sor_empnamecomp',
                'sor.sor_month',
                'frm.frm_name',
                'shd.subhead',
                'shd.ratio',
                't.trn_amount1',
                't.trn_tax1',
                't.trn_amount2',
                't.trn_transtype'
            )
            ->orderByDesc('t.trn_date')
            ->get();

            foreach ($rawExp as $row) {
                $title = $row->pcs_title;
                if ($row->cmt_type === 'Sa') {
                    $title = 'Monthly Salary: ' . ($row->sor_empnamecomp ?: 'Staff') . ($row->sor_month ? ' (' . $row->sor_month . ')' : '');
                } elseif (!$title) {
                    $title = ($row->cmt_type === 'TO' ? 'Transfer Out' : ($row->cmt_type === 'Pt' ? 'Petty Cash' : 'Expenditure Document #' . $row->cmt_docid));
                }

                $ratio = (float) ($row->ratio ?? 1.0);
                $amt1 = abs((float) $row->trn_amount1) * $ratio;
                $tax1 = abs((float) ($row->trn_tax1 ?? 0)) * $ratio;
                $amt2 = abs((float) ($row->trn_amount2 ?: $row->trn_amount1)) * $ratio;

                $items[] = (object) [
                    'id'          => $row->trn_id,
                    'ref_no'      => $row->cmt_type . '-' . $row->cmt_docid,
                    'date'        => $row->trn_date ? \Carbon\Carbon::parse($row->trn_date)->format('d M Y') : '-',
                    'title'       => $title,
                    'subhead'     => $row->subhead ?: ($row->cmt_sudohed ?: 'General'),
                    'vendor'      => $row->frm_name ?: ($row->cmt_type === 'Sa' ? 'Employee Payroll' : '-'),
                    'amount'      => round($amt1, 2),
                    'tax'         => round($tax1, 2),
                    'total'       => round($amt2, 2),
                    'status'      => 'Paid / Debited',
                ];
            }
        }

        // ========================================================
        // 3. COMMITMENTS BREAKDOWN
        // ========================================================
        elseif ($figure === 'commitments') {
            $paidSub = DB::table('fin.transactions as t')
                ->select('t.trn_cmt_id', DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN COALESCE(t.trn_amount1, 0) ELSE COALESCE(t.trn_amount2, 0) END) as paid'))
                ->groupBy('t.trn_cmt_id');

            $cmtQuery = DB::table('fin.commitments as c')
                ->leftJoinSub($paidSub, 'p', 'c.cmt_id', '=', 'p.trn_cmt_id')
                ->leftJoin('pur.purcases as pcs', function($join) {
                    $join->on('pcs.pcs_id', '=', 'c.cmt_docid')
                         ->where('c.cmt_type', '=', 'Ps');
                })
                ->leftJoin('frm.firmz as frm', 'pcs.pcs_frm_id', '=', 'frm.frm_id')
                ->leftJoin('fin.salorders as sor', function($join) {
                    $join->on('sor.sor_id', '=', 'c.cmt_docid')
                         ->where('c.cmt_type', '=', 'Sa');
                })
                ->leftJoin('fin.docs_shd as shd', function ($join) {
                    $join->on('c.cmt_docid', '=', 'shd.doc_id')
                         ->on('c.cmt_type', '=', 'shd.doc_type');
                })
                ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa'])
                ->where('c.cmt_status', 'Awaited');

            // Apply scope filtering
            if ($scope === 'pcc') {
                $cmtQuery->where('c.cmt_effhed_id', $headId)
                         ->where(function($q) {
                             $q->whereNull('c.cmt_sudohed')->orWhere('c.cmt_sudohed', '=', '');
                         });
            } elseif ($scope === 'csrf') {
                $cmtQuery->where('c.cmt_effhed_id', $headId)
                         ->whereNotNull('c.cmt_sudohed')
                         ->where('c.cmt_sudohed', '<>', '');
            } elseif ($scope === 'prj') {
                $cmtQuery->where('c.cmt_hed_id', $headId);
            } elseif ($scope === 'subhead') {
                $cmtQuery->where('c.cmt_hed_id', $headId);
                if ($subhead && $subhead !== 'Misc') {
                    $cmtQuery->where('shd.subhead', $subhead);
                }
            } else {
                // Account level
                $cmtQuery->where('c.cmt_effhed_id', $headId);
            }

            $rawCmts = $cmtQuery->select(
                'c.cmt_id',
                'c.cmt_docid',
                'c.cmt_type',
                'c.cmt_date',
                'c.cmt_amount',
                'c.cmt_status',
                'c.cmt_sudohed',
                'p.paid',
                'pcs.pcs_title',
                'sor.sor_empnamecomp',
                'sor.sor_month',
                'frm.frm_name',
                'shd.subhead',
                'shd.ratio'
            )
            ->orderByDesc('c.cmt_date')
            ->get();

            foreach ($rawCmts as $row) {
                $title = $row->pcs_title;
                if ($row->cmt_type === 'Sa') {
                    $title = 'Committed Salary: ' . ($row->sor_empnamecomp ?: 'Staff') . ($row->sor_month ? ' (' . $row->sor_month . ')' : '');
                } elseif (!$title) {
                    $title = 'Commitment Case #' . $row->cmt_docid . ' (' . $row->cmt_type . ')';
                }

                $ratio = (float) ($row->ratio ?? 1.0);
                $cmtAmt = abs((float) $row->cmt_amount) * $ratio;
                $paidAmt = abs((float) ($row->paid ?? 0)) * $ratio;
                $outstanding = max(0, $cmtAmt - $paidAmt);

                $items[] = (object) [
                    'id'          => $row->cmt_id,
                    'ref_no'      => $row->cmt_type . '-' . $row->cmt_docid,
                    'date'        => $row->cmt_date ? \Carbon\Carbon::parse($row->cmt_date)->format('d M Y') : '-',
                    'title'       => $title,
                    'subhead'     => $row->subhead ?: ($row->cmt_sudohed ?: 'General'),
                    'vendor'      => $row->frm_name ?: ($row->cmt_type === 'Sa' ? 'Employee' : '-'),
                    'committed'   => round($cmtAmt, 2),
                    'paid'        => round($paidAmt, 2),
                    'amount'      => round($outstanding, 2),
                    'total'       => round($outstanding, 2),
                    'status'      => $row->cmt_status ?: 'Awaited',
                ];
            }
        }

        // ========================================================
        // 4. IN PROCESS BREAKDOWN
        // ========================================================
        elseif ($figure === 'in-process') {
            $ipcQuery = DB::table('fin.docs_ipc as ipc')
                ->leftJoin('pur.purcases as pcs', function($join) {
                    $join->on('pcs.pcs_id', '=', 'ipc.docid')
                         ->whereIn('ipc.doctype', ['Ps', 'pt', 'mat', 'pur']);
                })
                ->leftJoin('fin.docs_shd as shd', function ($join) {
                    $join->on('ipc.doctype', '=', 'shd.doc_type')
                         ->on('ipc.docid', '=', 'shd.doc_id');
                });

            // Scope filtering
            if ($scope === 'pcc') {
                $ipcQuery->where('ipc.effhed_id', $headId)
                         ->where(function($q) {
                             $q->whereNull('ipc.sudohed')->orWhere('ipc.sudohed', '=', '');
                         });
            } elseif ($scope === 'csrf') {
                $ipcQuery->where('ipc.effhed_id', $headId)
                         ->whereNotNull('ipc.sudohed')
                         ->where('ipc.sudohed', '<>', '');
            } elseif ($scope === 'prj') {
                $ipcQuery->where('ipc.hed_id', $headId);
            } elseif ($scope === 'subhead') {
                $ipcQuery->where('ipc.hed_id', $headId);
                if ($subhead && $subhead !== 'Misc') {
                    $ipcQuery->where('shd.subhead', $subhead);
                }
            } else {
                // Account level
                $ipcQuery->where('ipc.effhed_id', $headId);
            }

            $rawIpc = $ipcQuery->select(
                'ipc.docid',
                'ipc.doctype',
                'ipc.rdate',
                'ipc.title',
                'ipc.sudohed',
                'ipc.amount1',
                'ipc.tax1',
                'ipc.amount2',
                'ipc.transtype',
                'pcs.pcs_status',
                'shd.subhead',
                'shd.ratio'
            )
            ->orderByDesc('ipc.rdate')
            ->get();

            foreach ($rawIpc as $row) {
                $ratio = (float) ($row->ratio ?? 1.0);
                $amt1 = abs((float) ($row->amount1 ?: $row->amount2)) * $ratio;
                $amt2 = abs((float) ($row->amount2 ?: $row->amount1)) * $ratio;

                $items[] = (object) [
                    'id'          => $row->docid,
                    'ref_no'      => strtoupper($row->doctype) . '-' . $row->docid,
                    'date'        => $row->rdate ? \Carbon\Carbon::parse($row->rdate)->format('d M Y') : '-',
                    'title'       => $row->title ?: 'In-Process Case #' . $row->docid,
                    'subhead'     => $row->subhead ?: ($row->sudohed ?: 'General'),
                    'vendor'      => 'Internal / Pipeline',
                    'amount'      => round($amt1, 2),
                    'tax'         => abs((float) ($row->tax1 ?? 0)),
                    'total'       => round($amt2, 2),
                    'status'      => $row->pcs_status ?: 'Under Approval / Processing',
                ];
            }
        }

        // ========================================================
        // 5. LOANS GIVEN / LOANS TAKEN BREAKDOWN
        // ========================================================
        elseif ($figure === 'loansgiven' || $figure === 'loanstaken') {
            $loansQuery = DB::table('fin.commitments as c')
                ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
                ->whereIn('c.cmt_type', ['Ps', 'Rb', 'Sa', 'LO', 'TO'])
                ->where('t.trn_noloan', false);

            if ($figure === 'loansgiven') {
                $loansQuery->where('c.cmt_effhed_id', $headId)
                           ->where(function($q) use ($headId) {
                               $q->whereNull('c.cmt_hed_id')->orWhere('c.cmt_hed_id', '<>', $headId);
                           })
                           ->leftJoin('cen.heads as target_h', 'target_h.hed_id', '=', 'c.cmt_hed_id')
                           ->select(
                               't.trn_id', 't.trn_date', 'c.cmt_id', 'c.cmt_docid', 'c.cmt_type',
                               'target_h.hed_code as related_head', 't.trn_amount1', 't.trn_amount2'
                           );
            } else {
                $loansQuery->where('c.cmt_hed_id', $headId)
                           ->where('c.cmt_effhed_id', '<>', $headId)
                           ->leftJoin('cen.heads as source_h', 'source_h.hed_id', '=', 'c.cmt_effhed_id')
                           ->select(
                               't.trn_id', 't.trn_date', 'c.cmt_id', 'c.cmt_docid', 'c.cmt_type',
                               'source_h.hed_code as related_head', 't.trn_amount1', 't.trn_amount2'
                           );
            }

            $rawLoans = $loansQuery->orderByDesc('t.trn_date')->get();

            foreach ($rawLoans as $row) {
                $amt = abs((float) ($row->trn_amount1 ?: $row->trn_amount2));
                $items[] = (object) [
                    'id'          => $row->trn_id,
                    'ref_no'      => $row->cmt_type . '-' . $row->cmt_docid,
                    'date'        => $row->trn_date ? \Carbon\Carbon::parse($row->trn_date)->format('d M Y') : '-',
                    'title'       => ($figure === 'loansgiven' ? 'Loan Provided to Project: ' : 'Loan Borrowed from Project: ') . ($row->related_head ?: 'Other Head'),
                    'subhead'     => 'Inter-Project Netting',
                    'vendor'      => $row->related_head ?: 'Inter-Project',
                    'amount'      => round($amt, 2),
                    'tax'         => 0,
                    'total'       => round($amt, 2),
                    'status'      => 'Reconciled',
                ];
            }
        }

        // Summary calculations
        $totalItems = count($items);
        $totalSum = 0;
        foreach ($items as $it) {
            $totalSum += (float) ($it->total ?? ($it->amount ?? 0));
        }

        return view('division.finance-of-project.drilldown', compact(
            'head', 'scope', 'figure', 'subhead', 'scopeLabel', 'figureLabel',
            'currentValue', 'items', 'totalItems', 'totalSum', 'breakdownType'
        ));
    }
}
