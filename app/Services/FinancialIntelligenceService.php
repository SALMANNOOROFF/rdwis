<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Purchase;

class FinancialIntelligenceService
{
    // Request-level cache to prevent timeouts in deep loops
    private $contractsCache = [];
    private $attendanceCache = [];
    private $leavesCache = [];
    private $effHeadsCache = [];
    private $globalVarsCache = [];
    private $salOrdersCache = [];
    private $extExpensesCache = [];

    private function getGlobalVar($name)
    {
        if (!isset($this->globalVarsCache[$name])) {
            $this->globalVarsCache[$name] = DB::table('cen.globalvars')
                ->where('gvar_name', $name)
                ->value('gvar_value');
        }
        return $this->globalVarsCache[$name];
    }

    private function getEmpContracts($empId)
    {
        if (!isset($this->contractsCache[$empId])) {
            $this->contractsCache[$empId] = DB::table('hr.contracts as ctr')
                ->join('hr.emps as emp', 'ctr.ctr_num', '=', 'emp.emp_id')
                ->leftJoin('hr.contractplans as cpn', 'ctr.ctr_id', '=', 'cpn.cpn_ctr_id')
                ->where('ctr.ctr_num', $empId)
                ->where(function($query) {
                    $query->whereRaw('cpn.cpn_enddt >= emp.emp_joindt')
                          ->orWhereNull('cpn.cpn_enddt');
                })
                ->select(
                    'ctr.ctr_id',
                    'ctr.ctr_startdt',
                    'ctr.ctr_enddt',
                    'ctr.ctr_termindt',
                    'ctr.ctr_salary',
                    'ctr.ctr_prob',
                    'ctr.ctr_probsal',
                    'ctr.ctr_unt_id',
                    'emp.emp_joindt',
                    'cpn.cpn_startdt',
                    'cpn.cpn_enddt',
                    'cpn.cpn_hed_id'
                )
                ->get()
                ->toArray();
        }
        return $this->contractsCache[$empId];
    }

    private function getEmpEffHead($empId)
    {
        if (!isset($this->effHeadsCache[$empId])) {
            $this->effHeadsCache[$empId] = DB::table('fin.empeffheads')
                ->where('eeh_emp_id', $empId)
                ->first();
        }
        return $this->effHeadsCache[$empId];
    }

    private function getEmpLeaves($empId)
    {
        if (!isset($this->leavesCache[$empId])) {
            $this->leavesCache[$empId] = DB::table('hr.leaves')
                ->where('lve_emp_id', $empId)
                ->get()
                ->toArray();
        }
        return $this->leavesCache[$empId];
    }

    private function getEmpAttendance($empId)
    {
        if (!isset($this->attendanceCache[$empId])) {
            $this->attendanceCache[$empId] = DB::table('hr.attendance')
                ->where('att_emp_id', $empId)
                ->get()
                ->toArray();
        }
        return $this->attendanceCache[$empId];
    }

    private function getEmpSalOrders($empId)
    {
        if (!isset($this->salOrdersCache[$empId])) {
            $this->salOrdersCache[$empId] = DB::table('fin.salorders')
                ->where('sor_emp_id', $empId)
                ->where('sor_status', 'Fulfilled')
                ->select('sor_month', 'sor_award', 'sor_penalty', 'sor_withheld', 'sor_loaned', 'sor_salary')
                ->get()
                ->toArray();
        }
        return $this->salOrdersCache[$empId];
    }

    private function getEmpExtExpenses($empId)
    {
        if (!isset($this->extExpensesCache[$empId])) {
            $this->extExpensesCache[$empId] = DB::table('fin.extcompenses')
                ->where('ecp_emp_id', $empId)
                ->where('ecp_type', 'Salary')
                ->select('ecp_month', 'ecp_amount')
                ->get()
                ->toArray();
        }
        return $this->extExpensesCache[$empId];
    }

    /**
     * Get the full financial status of a head (Project)
     */
    public function getHeadStatus($headId)
    {
        $status = [];

        // 1. Basic Information
        $head = DB::table('cen.heads')->where('hed_id', $headId)->first();
        $status['head_id'] = $headId;
        $status['head_name'] = $head->hed_name ?? 'Unknown';
        $status['head_code'] = $head->hed_code ?? '';
        $status['trans_type'] = $head->hed_transtype ?? 1;
        $status['hed_prj_id'] = $head->hed_prj_id ?? 0;

        // 2. Allocation (Project Funding) - trf_type 'FI' = Fund In
        $allocation = DB::table('fin.transfers')
            ->where('trf_type', 'FI')
            ->where('trf_title', 'Project Funding')
            ->where('trf_tohed', $headId)
            ->sum('trf_amount');
        
        $status['allocation'] = (float) $allocation;

        // 3. MTSS & Shares
        // MTSS Share = trf_type 'FO' = Fund Out
        $mtssShare = DB::table('fin.transfers')
            ->where('trf_type', 'FO')
            ->where('trf_title', 'MTSS Share')
            ->where('trf_fromhed', $headId)
            ->sum('trf_amount');
        
        $status['mtss_share'] = (float) $mtssShare;
        
        $shares = DB::table('fin.sharesalloc')
            ->where('sha_hed_id', $headId)
            ->first();

        $status['rdw_share'] = $status['allocation'] - $status['mtss_share'];
        $status['acc_share'] = $status['rdw_share'];
        $status['pcc_share'] = (float) ($shares->sha_pcc ?? 0);
        $status['cf_share'] = (float) ($shares->sha_cf ?? 0);
        $status['csrf_share'] = $status['cf_share']; // Alias for view compatibility
        $status['prj_share'] = (float) ($shares->sha_prj ?? 0);

        // 4. Received (Cash Inflow)
        $pccReceived = DB::table('fin.sharesinstall')
            ->where('shi_hed_id', $headId)
            ->sum(DB::raw('COALESCE(shi_pcc,0)'));
        $cfReceived = DB::table('fin.sharesinstall')
            ->where('shi_hed_id', $headId)
            ->sum(DB::raw('COALESCE(shi_cf,0)'));
        $status['pcc_received'] = (float) $pccReceived;
        $status['cf_received'] = (float) $cfReceived;
        $status['received'] = $status['pcc_received'] + $status['cf_received'];

        // Tier 2: Account level received
        $accReceived = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->where('c.cmt_effhed_id', $headId)
            ->whereIn('c.cmt_type', ['FI', 'FO', 'TI'])
            ->select(DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) as total'))
            ->value('total');
        $status['acc_received'] = round((float) ($accReceived ?? 0), 2);

        // Tier 2: Account level expenditure
        $accExpenditure = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->where('c.cmt_effhed_id', $headId)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO'])
            ->select(DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) as total'))
            ->value('total');
        $status['acc_expenditure'] = round(((float) ($accExpenditure ?? 0)) * -1, 2);

        // 5. PCC/CF/PRJ Expenditures with scoping corrections & GST branching
        // PCC Expenditure: cmt_effhed_id, sudohed IS NULL or empty
        $pccExpenditure = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->where('c.cmt_effhed_id', $headId)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO'])
            ->where(function($query) {
                $query->whereNull('c.cmt_sudohed')
                      ->orWhere('c.cmt_sudohed', '=', '');
            })
            ->select(DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) as total'))
            ->value('total');
        $status['pcc_expenditure'] = round(((float) ($pccExpenditure ?? 0)) * -1, 2);

        // CF Expenditure: cmt_effhed_id, sudohed NOT NULL and non-empty
        $cfExpenditure = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->where('c.cmt_effhed_id', $headId)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa'])
            ->whereNotNull('c.cmt_sudohed')
            ->where('c.cmt_sudohed', '<>', '')
            ->select(DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) as total'))
            ->value('total');
        $status['cf_expenditure'] = round(((float) ($cfExpenditure ?? 0)) * -1, 2);

        // PRJ Expenditure: cmt_hed_id (parent)
        $prjExpenditure = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->where('c.cmt_hed_id', $headId)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO'])
            ->select(DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) as total'))
            ->value('total');
        $status['prj_expenditure'] = round(((float) ($prjExpenditure ?? 0)) * -1, 2);

        // Combined Expenditure
        $status['expenditure'] = $status['pcc_expenditure'] + $status['cf_expenditure'];
        $status['exp_this_account'] = $status['expenditure'];

        // 7. Inter-Project Netting Engine
        $nettingResult = $this->calculateInterProjectNetting($headId);
        $status['exp_other_accounts'] = $nettingResult['exp_other_accounts'];
        $status['others_exp_this_account'] = $nettingResult['others_exp_this_account'];

        // 8. Outstanding Commitments - Pipelines
        $status['acc_commitments'] = $this->calculateOutstandingCommitments('c.cmt_effhed_id', $headId, false);
        $status['pcc_commitments'] = $this->calculateOutstandingCommitments('c.cmt_effhed_id', $headId, true, false);
        $status['cf_commitments'] = $this->calculateOutstandingCommitments('c.cmt_effhed_id', $headId, true, true);
        $status['prj_commitments'] = $this->calculateOutstandingCommitments('c.cmt_hed_id', $headId, false);
        
        $status['commitments'] = $status['pcc_commitments'];

        // 9. In Process (IPC) - Queries built view fin.docs_ipc
        $status['acc_in_process'] = $this->calculateInProcess('effhed_id', $headId, false);
        $status['pcc_in_process'] = $this->calculateInProcess('effhed_id', $headId, true, false);
        $status['cf_in_process'] = $this->calculateInProcess('effhed_id', $headId, true, true);
        $status['prj_in_process'] = $this->calculateInProcess('hed_id', $headId, false);
        
        $status['in_process'] = $status['pcc_in_process'] + $status['cf_in_process'];

        // 10. Derived Calculations
        // PCC-specific metrics
        $status['pcc_balance'] = round($status['pcc_received'] - $status['pcc_expenditure'], 2);
        $status['pcc_available'] = round($status['pcc_balance'] - $status['pcc_commitments'] - $status['pcc_in_process'], 2);
        $status['pcc_yet_to_be_received'] = round($status['pcc_share'] - $status['pcc_received'], 2);
        $status['pcc_can_be_spent'] = round($status['pcc_share'] - $status['pcc_expenditure'] - $status['pcc_commitments'] - $status['pcc_in_process'], 2);
        
        // CSRF-specific metrics
        $status['cf_balance'] = round($status['cf_received'] - $status['cf_expenditure'], 2);
        $status['cf_available'] = round($status['cf_balance'] - $status['cf_commitments'] - $status['cf_in_process'], 2);
        $status['cf_yet_to_be_received'] = round($status['cf_share'] - $status['cf_received'], 2);
        $status['cf_can_be_spent'] = round($status['cf_share'] - $status['cf_expenditure'] - $status['cf_commitments'] - $status['cf_in_process'], 2);
        
        // Combined/Global metrics (Account Scope)
        $status['balance'] = round($status['acc_received'] - $status['acc_expenditure'], 2);
        $status['available'] = round($status['acc_received'] - $status['acc_expenditure'] - $status['acc_commitments'] - $status['acc_in_process'], 2);
        $status['yet_to_be_received'] = round($status['acc_share'] - $status['acc_received'], 2);
        $status['can_be_spent'] = round($status['acc_share'] - $status['acc_expenditure'] - $status['acc_commitments'] - $status['acc_in_process'], 2);
        $status['acc_remaining'] = $status['pcc_can_be_spent'];
        
        // Project metrics (PrjCanBeSpent)
        $status['prj_can_be_spent'] = round($status['prj_share'] - $status['prj_expenditure'] - $status['prj_commitments'] - $status['prj_in_process'], 2);
        $status['prj_remaining'] = $status['prj_can_be_spent'];

        // 11. Receivables (Mission Based)
        $status['receivable_completed'] = $this->calculateMilestoneReceivableCompleted($headId);
        $status['receivable_current'] = $this->calculateMilestoneReceivableCurrent($headId);
        $status['available_after_receivables'] = $status['pcc_available'] + $status['receivable_completed'] + $status['receivable_current'];

        // 14. Percentage Tracking
        $status['pct_utilized'] = $status['allocation'] > 0 ? ($status['expenditure'] / $status['allocation']) * 100 : 0;
        $status['pct_committed'] = $status['allocation'] > 0 ? ($status['commitments'] / $status['allocation']) * 100 : 0;

        // 15. Loans
        $loans = $this->getLoans($headId);
        $status['pcc_loans_given'] = $loans->pcc_loans_given;
        $status['others_loans_taken'] = $loans->others_loans_taken;
        $status['pcc_own_exp'] = $loans->pcc_own_exp;

        return (object) $status;
    }

    private function calculateInterProjectNetting($headId)
    {
        // Query the netting transactions
        $nettingRows = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->whereIn('c.cmt_type', ['Ps', 'Rb', 'Sa', 'LO', 'TO'])
            ->where('t.trn_noloan', false)
            ->where(function($query) use ($headId) {
                $query->where('c.cmt_effhed_id', $headId)
                      ->orWhere('c.cmt_hed_id', $headId);
            })
            ->where(DB::raw('COALESCE(c.cmt_effhed_id, 0)'), '!=', DB::raw('COALESCE(c.cmt_hed_id, 160001)'))
            ->select(
                'c.cmt_hed_id',
                'c.cmt_effhed_id',
                DB::raw('CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END as amount')
            )
            ->get();

        $expOtherAccounts = 0;
        $othersExpThisAccount = 0;

        foreach ($nettingRows as $row) {
            if ($row->cmt_hed_id == $headId && $row->cmt_effhed_id != $headId) {
                // Project exp from other accounts (addition)
                $expOtherAccounts += (float) $row->amount;
            } elseif ($row->cmt_effhed_id == $headId && $row->cmt_hed_id != $headId) {
                // Other's exp from this account (subtraction)
                $othersExpThisAccount += (float) $row->amount * (-1);
            }
        }

        return [
            'exp_other_accounts' => round($expOtherAccounts, 2),
            'others_exp_this_account' => round($othersExpThisAccount, 2)
        ];
    }

    private function calculateOutstandingCommitments($scopeColumn, $headId, $checkSudohed = false, $sudohedNotNull = false)
    {
        $paidSub = DB::table('fin.transactions as t')
            ->select('t.trn_cmt_id', DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN COALESCE(t.trn_amount1, 0) ELSE COALESCE(t.trn_amount2, 0) END) as paid'))
            ->groupBy('t.trn_cmt_id');

        $query = DB::table('fin.commitments as c')
            ->leftJoinSub($paidSub, 'p', 'c.cmt_id', '=', 'p.trn_cmt_id')
            ->where($scopeColumn, $headId)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa'])
            ->where('c.cmt_status', 'Awaited');

        if ($checkSudohed) {
            if ($sudohedNotNull) {
                $query->whereNotNull('c.cmt_sudohed')
                      ->where('c.cmt_sudohed', '<>', '');
            } else {
                $query->where(function($q) {
                    $q->whereNull('c.cmt_sudohed')
                      ->orWhere('c.cmt_sudohed', '=', '');
                });
            }
        }

        $result = $query->select(DB::raw('SUM(c.cmt_amount - COALESCE(p.paid, 0)) as total'))->value('total');

        return round(((float) ($result ?? 0)) * -1, 2);
    }

    private function calculateInProcess($scopeColumn, $headId, $checkSudohed = false, $sudohedNotNull = false)
    {
        $query = DB::table('fin.docs_ipc')
            ->where($scopeColumn, $headId);

        if ($checkSudohed) {
            if ($sudohedNotNull) {
                $query->whereNotNull('sudohed')
                      ->where('sudohed', '<>', '');
            } else {
                $query->where(function($q) {
                    $q->whereNull('sudohed')
                      ->orWhere('sudohed', '=', '');
                });
            }
        }

        $result = $query->select(DB::raw('SUM(CASE WHEN transtype = 1 THEN amount1 ELSE amount2 END) as total'))->value('total');

        return round((float) ($result ?? 0), 2);
    }

    private function calculateMilestoneReceivableCompleted($headId)
    {
        $costcmp = DB::table('fin.msncosts as m')
            ->join('prj.milestones as p', 'm.mct_msn_idd', '=', 'p.msn_idd')
            ->where('m.mct_hed_id', $headId)
            ->where('p.msn_status', 'Completed')
            ->sum('m.mct_cost');

        if ($costcmp == 0) {
            return 0.0;
        }

        $sharesPlus = DB::table('fin.sharesalloc as s')
            ->join('fin.commitments as c1', 's.sha_ficmt_id', '=', 'c1.cmt_id')
            ->leftJoin('fin.commitments as c2', 's.sha_focmt_id', '=', 'c2.cmt_id')
            ->where('s.sha_hed_id', $headId)
            ->select('c1.cmt_amount as alloc', 'c2.cmt_amount as mtss_share')
            ->first();

        $alloc = (float) ($sharesPlus->alloc ?? 0);
        $mtssShare = -1 * (float) ($sharesPlus->mtss_share ?? 0);

        $costcmp_acc = $alloc > 0 ? round($costcmp * ($alloc - $mtssShare) / $alloc, 0) : 0;

        $rec_acc = DB::table('fin.sharesinstall')
            ->where('shi_hed_id', $headId)
            ->sum(DB::raw('COALESCE(shi_pcc, 0) + COALESCE(shi_cf, 0)'));

        return round(max(0.0, $costcmp_acc - $rec_acc), 2);
    }

    private function calculateMilestoneReceivableCurrent($headId)
    {
        $currentRows = DB::table('fin.msncosts as m')
            ->join('prj.milestones as p', 'm.mct_msn_idd', '=', 'p.msn_idd')
            ->join('fin.sharesalloc as s', 'm.mct_hed_id', '=', 's.sha_hed_id')
            ->join('fin.commitments as c', 's.sha_ficmt_id', '=', 'c.cmt_id')
            ->where('m.mct_hed_id', $headId)
            ->where('p.msn_status', 'In progress')
            ->select('m.mct_cost', 's.sha_cf', 's.sha_pcc', 'c.cmt_amount')
            ->get();

        $receivableCurrent = 0;
        foreach ($currentRows as $row) {
            $cmtAmount = (float) $row->cmt_amount;
            if ($cmtAmount > 0) {
                $receivableCurrent += round((float)$row->mct_cost * (((float)$row->sha_cf + (float)$row->sha_pcc) / $cmtAmount), 0);
            }
        }

        return round($receivableCurrent, 2);
    }

    public function getSubheadBreakdown($headId)
    {
        // Alphabetical except "Misc" sorted last
        $subheads = DB::table('fin.subheads')
            ->where('sbh_hed_id', $headId)
            ->orderBy(DB::raw("CASE WHEN sbh_name = 'Misc' THEN 'zzz' ELSE sbh_name END"))
            ->get();

        // Subhead Expenditures split (Ps, Pt, Rb, Sa, TO transactions ratio-split)
        $subheadExpendituresRows = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->leftJoin('fin.docs_shd as shd', function ($join) {
                $join->on('c.cmt_docid', '=', 'shd.doc_id')
                     ->on('c.cmt_type', '=', 'shd.doc_type');
            })
            ->where('c.cmt_hed_id', $headId)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO'])
            ->select(
                'shd.subhead',
                DB::raw('SUM(ROUND((CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) * COALESCE(shd.ratio, 1.0), 2)) as total')
            )
            ->groupBy('shd.subhead')
            ->get();

        $subheadExpenditures = [];
        foreach ($subheadExpendituresRows as $row) {
            $key = $row->subhead ?? 'NULL';
            $subheadExpenditures[$key] = round(abs((float) $row->total), 2);
        }

        // Subhead Commitments split
        $paidSubhead = DB::table('fin.transactions as t')
            ->join('fin.commitments as c', 't.trn_cmt_id', '=', 'c.cmt_id')
            ->leftJoin('fin.docs_shd as shd', function ($join) {
                $join->on('c.cmt_docid', '=', 'shd.doc_id')
                     ->on('c.cmt_type', '=', 'shd.doc_type');
            })
            ->where('c.cmt_hed_id', $headId)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa'])
            ->where('c.cmt_status', 'Awaited')
            ->select(
                'c.cmt_id',
                'shd.subhead',
                DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN COALESCE(t.trn_amount1, 0) ELSE COALESCE(t.trn_amount2, 0) END) as paid')
            )
            ->groupBy('c.cmt_id', 'shd.subhead')
            ->get();

        $commitSubhead = DB::table('fin.commitments as c')
            ->leftJoin('fin.docs_shd as shd', function ($join) {
                $join->on('c.cmt_docid', '=', 'shd.doc_id')
                     ->on('c.cmt_type', '=', 'shd.doc_type');
            })
            ->where('c.cmt_hed_id', $headId)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa'])
            ->where('c.cmt_status', 'Awaited')
            ->select('c.cmt_id', 'shd.subhead', 'c.cmt_amount', 'shd.ratio')
            ->get();

        $subheadCommitments = [];
        foreach ($commitSubhead as $cmt) {
            $ratio = (float) ($cmt->ratio ?? 1.0);
            $subhead = $cmt->subhead;
            
            $paid = 0;
            foreach ($paidSubhead as $p) {
                if ($p->cmt_id == $cmt->cmt_id && $p->subhead == $subhead) {
                    $paid = (float) $p->paid;
                    break;
                }
            }
            
            $net = round((float)$cmt->cmt_amount * $ratio, 2) - round($paid * $ratio, 2);
            $key = $subhead ?? 'NULL';
            if (!isset($subheadCommitments[$key])) {
                $subheadCommitments[$key] = 0.0;
            }
            $subheadCommitments[$key] += $net;
        }

        // Subhead In Process split
        $subheadInProcessRows = DB::table('fin.docs_ipc as ipc')
            ->leftJoin('fin.docs_shd as shd', function ($join) {
                $join->on('ipc.doctype', '=', 'shd.doc_type')
                     ->on('ipc.docid', '=', 'shd.doc_id');
            })
            ->where('ipc.hed_id', $headId)
            ->select(
                'shd.subhead',
                DB::raw('SUM(ROUND((CASE WHEN ipc.transtype = 1 THEN ipc.amount1 ELSE ipc.amount2 END) * COALESCE(shd.ratio, 1.0), 2)) as total')
            )
            ->groupBy('shd.subhead')
            ->get();

        $subheadInProcesses = [];
        foreach ($subheadInProcessRows as $row) {
            $key = $row->subhead ?? 'NULL';
            $subheadInProcesses[$key] = round(abs((float) $row->total), 2);
        }

        // Known subhead names
        $knownSubheads = $subheads->pluck('sbh_name')->toArray();

        $result = [];
        foreach ($subheads as $sh) {
            $name = $sh->sbh_name;
            
            // Calculate values
            if ($name === 'Misc') {
                // Sum Misc and any unmapped (NULL) or non-matching keys
                $exp = ($subheadExpenditures['Misc'] ?? 0.0) + ($subheadExpenditures['NULL'] ?? 0.0);
                foreach ($subheadExpenditures as $k => $v) {
                    if ($k !== 'Misc' && $k !== 'NULL' && !in_array($k, $knownSubheads)) {
                        $exp += $v;
                    }
                }
                
                $com = ($subheadCommitments['Misc'] ?? 0.0) + ($subheadCommitments['NULL'] ?? 0.0);
                foreach ($subheadCommitments as $k => $v) {
                    if ($k !== 'Misc' && $k !== 'NULL' && !in_array($k, $knownSubheads)) {
                        $com += $v;
                    }
                }

                $ipcVal = ($subheadInProcesses['Misc'] ?? 0.0) + ($subheadInProcesses['NULL'] ?? 0.0);
                foreach ($subheadInProcesses as $k => $v) {
                    if ($k !== 'Misc' && $k !== 'NULL' && !in_array($k, $knownSubheads)) {
                        $ipcVal += $v;
                    }
                }
            } else {
                $exp = $subheadExpenditures[$name] ?? 0.0;
                $com = $subheadCommitments[$name] ?? 0.0;
                $ipcVal = $subheadInProcesses[$name] ?? 0.0;
            }

            $exp = round(abs($exp), 2);
            $com = round(abs($com), 2);
            $ipcVal = round(abs($ipcVal), 2);

            // HR Subhead row replacement with forecast
            if ($name === 'HR') {
                $forecast = $this->getPrjSalForecast($headId);
                $result[] = [
                    'name' => $name,
                    'allocation' => (float) $sh->sbh_alloc,
                    'expenditure' => $exp,
                    'commitments' => 0.0,
                    'in_process' => 0.0,
                    'forecast' => $forecast,
                    'remaining' => round((float) $sh->sbh_alloc - $exp - $forecast, 2),
                    'can_be_spent' => round((float) $sh->sbh_alloc - $exp - $forecast, 2)
                ];
            } else {
                $result[] = [
                    'name' => $name,
                    'allocation' => (float) $sh->sbh_alloc,
                    'expenditure' => $exp,
                    'commitments' => $com,
                    'in_process' => $ipcVal,
                    'forecast' => null,
                    'remaining' => round((float) $sh->sbh_alloc - $exp - $com - $ipcVal, 2),
                    'can_be_spent' => round((float) $sh->sbh_alloc - $exp - $com - $ipcVal, 2)
                ];
            }
        }

        return $result;
    }

    /**
     * Calculate loans given, taken and own expenditure using commitments/transactions netting logic
     */
    public function getLoans($headId)
    {
        $loans = [];
        
        // Pcc Loans Given
        $loansGiven = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->whereIn('c.cmt_type', ['Ps', 'Rb', 'Sa', 'LO', 'TO'])
            ->where('c.cmt_effhed_id', $headId)
            ->where('t.trn_noloan', false)
            ->where(function($query) use ($headId) {
                $query->whereNull('c.cmt_hed_id')
                      ->orWhere('c.cmt_hed_id', '<>', $headId);
            })
            ->select(DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) as total'))
            ->value('total');
        $loans['pcc_loans_given'] = round(((float) ($loansGiven ?? 0)) * -1, 2);
        
        // Others Loans Taken
        $loansTaken = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->whereIn('c.cmt_type', ['Ps', 'Rb', 'Sa', 'LO', 'TO'])
            ->where('c.cmt_hed_id', $headId)
            ->where('c.cmt_effhed_id', '<>', $headId)
            ->select(DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) as total'))
            ->value('total');
        $loans['others_loans_taken'] = round(((float) ($loansTaken ?? 0)) * -1, 2);

        // Pcc Own Exp
        $pccOwnExp = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa', 'TO'])
            ->where('c.cmt_effhed_id', $headId)
            ->where('c.cmt_hed_id', $headId)
            ->select(DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) as total'))
            ->value('total');
        $loans['pcc_own_exp'] = round(((float) ($pccOwnExp ?? 0)) * -1, 2);
        
        return (object) $loans;
    }

    /**
     * Calculate salary tax using fin.salary_tax table
     */
    public function calculateSalaryTax($ctrSalary, $baseSalary)
    {
        if ($ctrSalary <= 0) {
            return 0;
        }

        $annualSalary = $ctrSalary * 12;
        
        // Find the correct tax slab
        $taxSlab = DB::table('fin.salary_tax')
            ->where('slt_from', '<=', $annualSalary)
            ->where('slt_to', '>=', $annualSalary)
            ->first();
        
        if (!$taxSlab) {
            return 0;
        }

        // Exact VBA formula
        $tax = ($taxSlab->slt_inttax + ($annualSalary - $taxSlab->slt_midamount) * $taxSlab->slt_midtax / 100) / 12 * ($baseSalary / $ctrSalary);
        
        return $tax;
    }

    /**
     * Check if a contract is verified
     */
    public function isContractVerified($contractId)
    {
        $verification = DB::table('fin.contractsverif')
            ->where('cvf_ctr_id', $contractId)
            ->where('cvf_verif', true)
            ->first();
        
        return $verification !== null;
    }

    // Dynamic month date helpers
    private function getAttMonthStartDate($date, $attStartDay)
    {
        $dt = new \DateTime($date);
        if ($attStartDay >= 21 && $attStartDay <= 27) {
            $prev = clone $dt;
            $prev->modify('-1 month');
            return $prev->format("Y-m-") . sprintf("%02d", $attStartDay);
        } else {
            return $dt->format("Y-m-01");
        }
    }

    private function getAttMonthEndDate($date, $attStartDay)
    {
        $dt = new \DateTime($date);
        if ($attStartDay >= 21 && $attStartDay <= 27) {
            return $dt->format("Y-m-") . sprintf("%02d", $attStartDay - 1);
        } else {
            return $dt->format("Y-m-01");
        }
    }

    public function getEmpAttendanceSummary($empId, $startDate, $endDate)
    {
        $dStart = new \DateTime($startDate);
        $dEnd = new \DateTime($endDate);
        
        $records = $this->getEmpAttendance($empId);
        
        // Filter attendance records overlapping with [startDate, endDate]
        $filteredRecords = array_filter($records, function($r) use ($startDate, $endDate) {
            return $r->att_startdt <= $endDate && $r->att_enddt >= $startDate;
        });

        $daysMap = [];
        foreach ($filteredRecords as $r) {
            $rStart = new \DateTime($r->att_startdt);
            $rEnd = new \DateTime($r->att_enddt);
            
            $cur = clone $rStart;
            while ($cur <= $rEnd) {
                $dayNum = (int)$cur->format('j');
                $col = 'att_' . $dayNum;
                $char = $r->$col ?? 'Q';
                $daysMap[$cur->format('Y-m-d')] = $char;
                $cur->modify('+1 day');
            }
        }

        // Apply leaves override
        $leaves = $this->getEmpLeaves($empId);
        $filteredLeaves = array_filter($leaves, function($l) use ($startDate, $endDate) {
            return $l->lve_from <= $endDate && $l->lve_to >= $startDate;
        });

        foreach ($filteredLeaves as $l) {
            $lFrom = new \DateTime($l->lve_from);
            $lTo = new \DateTime($l->lve_to);
            $cur = clone $lFrom;
            while ($cur <= $lTo) {
                $daysMap[$cur->format('Y-m-d')] = $l->lve_type;
                $cur->modify('+1 day');
            }
        }

        $arr = [];
        $cur = clone $dStart;
        while ($cur <= $dEnd) {
            $dayStr = $cur->format('Y-m-d');
            $arr[] = [
                'date' => $dayStr,
                'char' => $daysMap[$dayStr] ?? 'Q'
            ];
            $cur->modify('+1 day');
        }

        $activeChars = ['A', 'L', 'U', 'T'];
        foreach ($activeChars as $x) {
            $len = count($arr);
            $i = 0;
            while ($i < $len) {
                if ($arr[$i]['char'] === $x) {
                    $j = $i + 1;
                    while ($j < $len && $arr[$j]['char'] === 'Z') {
                        $j++;
                    }
                    if ($j < $len && $arr[$j]['char'] === $x) {
                        for ($k = $i + 1; $k < $j; $k++) {
                            $arr[$k]['char'] = $x;
                        }
                        $i = $j - 1;
                    }
                }
                $i++;
            }
        }

        $counts = ['P' => 0, 'W' => 0, 'T' => 0, 'A' => 0, 'L' => 0, 'U' => 0, 'N' => 0, 'Z' => 0, 'Q' => 0, 'X' => 0];
        foreach ($arr as $item) {
            $c = $item['char'];
            if (!isset($counts[$c])) {
                $counts[$c] = 0;
            }
            $counts[$c]++;
        }

        return $counts;
    }

    public function getSalaryMatrix($empId, $salMonth)
    {
        $attStartDay = (int) $this->getGlobalVar('attstart_for_pay');
        $dtAttFrom = $this->getAttMonthStartDate($salMonth, $attStartDay);
        $dtAttTo = $this->getAttMonthEndDate($salMonth, $attStartDay);
        $dtFrom = (new \DateTime($salMonth))->format('Y-m-01');
        $dtTo = (new \DateTime($salMonth))->format('Y-m-t');

        $contracts = $this->getEmpContracts($empId);

        $relevantContracts = [];
        foreach ($contracts as $c) {
            $ctrEffStartdt = $c->emp_joindt > $c->ctr_startdt && $c->emp_joindt <= $c->ctr_enddt ? $c->emp_joindt : $c->ctr_startdt;
            $ctrEffEnddt = $c->ctr_termindt ?? $c->ctr_enddt;
            
            $startSign = strcmp($c->ctr_startdt, $dtTo) <= 0 ? -1 : 1;
            $endSign = strcmp($dtAttFrom, $ctrEffEnddt) <= 0 ? -1 : 1;
            if ($startSign * $endSign >= 0) {
                $relevantContracts[] = [
                    'ctr_id' => $c->ctr_id,
                    'ctr_salary' => (float) $c->ctr_salary,
                    'ctr_prob' => $c->ctr_prob,
                    'ctr_probsal' => $c->ctr_probsal,
                    'ctr_unt_id' => $c->ctr_unt_id,
                    'cpn_startdt' => $c->cpn_startdt,
                    'cpn_enddt' => $c->cpn_enddt,
                    'cpn_hed_id' => $c->cpn_hed_id,
                    'ctr_effstartdt' => $ctrEffStartdt,
                    'ctr_effenddt' => $ctrEffEnddt
                ];
            }
        }

        if (empty($relevantContracts)) {
            return [];
        }

        $tempPeriods = [];
        foreach ($relevantContracts as $c) {
            $isProject = ($c['ctr_unt_id'] >= 200000 && $c['ctr_unt_id'] < 800000);
            $start = $isProject ? $c['cpn_startdt'] : $c['ctr_effstartdt'];
            $end = $isProject ? $c['cpn_enddt'] : $c['ctr_effenddt'];
            
            $tempPeriods[] = [
                'type' => 3,
                'ctr_id' => $c['ctr_id'],
                'start' => $start,
                'end' => $end,
                'salary' => $c['ctr_salary'],
                'head_id' => $c['cpn_hed_id'],
                'effhed_id' => $c['cpn_hed_id']
            ];
        }

        $splitPeriods = function(&$periods, $splitStart, $splitEnd, $periodType, $altSalary = null) {
            $newPeriods = [];
            foreach ($periods as $p) {
                $pStart = $p['start'];
                $pEnd = $p['end'];
                
                if ($splitStart > $pStart && $splitStart <= $pEnd) {
                    $newPeriods[] = array_merge($p, [
                        'end' => (new \DateTime($splitStart))->modify('-1 day')->format('Y-m-d')
                    ]);
                    $newPeriods[] = array_merge($p, [
                        'start' => $splitStart
                    ]);
                } else {
                    $newPeriods[] = $p;
                }
            }
            $periods = $newPeriods;

            $newPeriods = [];
            foreach ($periods as $p) {
                $pStart = $p['start'];
                $pEnd = $p['end'];
                if ($splitEnd >= $pStart && $splitEnd < $pEnd) {
                    $newPeriods[] = array_merge($p, [
                        'end' => $splitEnd
                    ]);
                    $newPeriods[] = array_merge($p, [
                        'start' => (new \DateTime($splitEnd))->modify('+1 day')->format('Y-m-d')
                    ]);
                } else {
                    $newPeriods[] = $p;
                }
            }
            $periods = $newPeriods;

            foreach ($periods as &$p) {
                if ($p['start'] >= $splitStart && $p['start'] <= $splitEnd) {
                    $p['type'] = $periodType;
                    if ($altSalary !== null) {
                        $p['salary'] = $altSalary;
                    }
                }
            }
        };

        $probDone = [];
        foreach ($relevantContracts as $c) {
            if (!in_array($c['ctr_id'], $probDone)) {
                if ($c['ctr_prob'] && $c['ctr_probsal'] != $c['ctr_salary']) {
                    $probEnd = (new \DateTime($c['ctr_effstartdt']))->modify('+' . $c['ctr_prob'] . ' month')->modify('-1 day')->format('Y-m-d');
                    $splitPeriods($tempPeriods, $c['ctr_effstartdt'], $probEnd, 2, $c['ctr_probsal']);
                }
                $probDone[] = $c['ctr_id'];
            }
        }

        $splitPeriods($tempPeriods, $dtAttFrom, (new \DateTime($dtFrom))->modify('-1 day')->format('Y-m-d'), 1);
        $splitPeriods($tempPeriods, (new \DateTime($dtAttTo))->modify('+1 day')->format('Y-m-d'), $dtTo, 4);

        foreach ($tempPeriods as &$p) {
            if (!($p['start'] >= $dtAttFrom && $p['end'] <= $dtTo)) {
                $p['type'] = 0;
            }
        }

        $salheadApplicable = $this->getGlobalVar('salhead_applicable') === 'True';
        $isProject = false;
        if (!empty($relevantContracts)) {
            $isProject = ($relevantContracts[0]['ctr_unt_id'] >= 200000 && $relevantContracts[0]['ctr_unt_id'] < 800000);
        }
        
        if (!($salheadApplicable === false && $isProject)) {
            $effHead = $this->getEmpEffHead($empId);
            if ($effHead && $effHead->eeh_emphed_id) {
                $sudoPart = $effHead->eeh_sudohed ? ' ' . $effHead->eeh_sudohed : '';
                $headStr = $effHead->eeh_emphed_id . $sudoPart;
                foreach ($tempPeriods as &$p) {
                    if ($p['type'] != 0) {
                        $p['effhed_id'] = $headStr;
                    }
                }
            }
        }

        $arrSalMat = [];
        $idx = 1;
        foreach ($tempPeriods as $p) {
            if ($p['type'] != 0) {
                $absent = 0;
                $unpaid = 0;
                if ($p['type'] != 4) {
                    $attSummary = $this->getEmpAttendanceSummary($empId, $p['start'], $p['end']);
                    $absent = $attSummary['A'];
                    $unpaid = $attSummary['U'];
                }

                $calDays = (int) (new \DateTime($dtTo))->diff(new \DateTime($dtFrom))->days + 1;
                $periodDays = (int) (new \DateTime($p['end']))->diff(new \DateTime($p['start']))->days + 1;
                
                $proratedSal = 0.0;
                if ($p['type'] != 1) {
                    $proratedSal = ($p['salary'] / $calDays) * $periodDays;
                }

                $underwork = ($p['salary'] / $calDays) * ($absent + $unpaid);

                $arrSalMat[$idx] = [
                    'type' => $p['type'],
                    'ctr_id' => $p['ctr_id'],
                    'start' => $p['start'],
                    'end' => $p['end'],
                    'salary' => $p['salary'],
                    'absent' => $absent,
                    'unpaid' => $unpaid,
                    'prorated_salary' => $proratedSal,
                    'underwork' => $underwork,
                    'head_id' => $p['head_id'],
                    'effhed_id' => $p['effhed_id']
                ];
                $idx++;
                if ($idx > 7) break;
            }
        }

        $groups = [];
        for ($n = 1; $n < $idx; $n++) {
            $p = $arrSalMat[$n];
            if ($p['type'] == 1) continue;
            
            $effhed = $p['effhed_id'] ?? 'NULL';
            if (!isset($groups[$effhed])) {
                $groups[$effhed] = [
                    'ctr_ids' => [],
                    'start' => $p['start'],
                    'end' => $p['end'],
                    'salary' => $p['salary'],
                    'absent' => 0,
                    'unpaid' => 0,
                    'prorated_salary' => 0.0,
                    'underwork' => 0.0
                ];
            }
            $groups[$effhed]['ctr_ids'][] = $p['ctr_id'];
            $groups[$effhed]['start'] = min($groups[$effhed]['start'], $p['start']);
            $groups[$effhed]['end'] = max($groups[$effhed]['end'], $p['end']);
            $groups[$effhed]['absent'] += $p['absent'];
            $groups[$effhed]['unpaid'] += $p['unpaid'];
            $groups[$effhed]['prorated_salary'] += $p['prorated_salary'];
            $groups[$effhed]['underwork'] += $p['underwork'];
        }

        $rowIdx = 8;
        foreach ($groups as $effhed => $g) {
            $arrSalMat[$rowIdx] = [
                'type' => 7,
                'ctr_id' => implode(',', array_unique($g['ctr_ids'])),
                'start' => $g['start'],
                'end' => $g['end'],
                'salary' => $g['salary'],
                'absent' => $g['absent'],
                'unpaid' => $g['unpaid'],
                'prorated_salary' => $g['prorated_salary'],
                'underwork' => $g['underwork'],
                'head_id' => null,
                'effhed_id' => $effhed === 'NULL' ? null : $effhed
            ];
            $rowIdx++;
            if ($rowIdx > 10) break;
        }

        $totalProrated = 0.0;
        $totalUnderwork = 0.0;
        $totalAbsent = 0;
        $totalUnpaid = 0;
        $ctrIds = [];
        $gStart = null;
        $gEnd = null;
        $sal = 0.0;

        for ($n = 8; $n < $rowIdx; $n++) {
            $g = $arrSalMat[$n];
            $totalProrated += $g['prorated_salary'];
            $totalUnderwork += $g['underwork'];
            $totalAbsent += $g['absent'];
            $totalUnpaid += $g['unpaid'];
            $ctrIds[] = $g['ctr_id'];
            $gStart = $gStart ? min($gStart, $g['start']) : $g['start'];
            $gEnd = $gEnd ? max($gEnd, $g['end']) : $g['end'];
            $sal = $g['salary'];
        }

        $arrSalMat[11] = [
            'type' => 9,
            'ctr_id' => implode(',', array_unique(explode(',', implode(',', $ctrIds)))),
            'start' => $gStart,
            'end' => $gEnd,
            'salary' => $sal,
            'absent' => $totalAbsent,
            'unpaid' => $totalUnpaid,
            'prorated_salary' => $totalProrated,
            'underwork' => $totalUnderwork,
            'head_id' => null,
            'effhed_id' => null,
            'net_toBePaid' => $totalProrated - $totalUnderwork
        ];

        return $arrSalMat;
    }

    public function calculateArrDues($empId, $salMonth)
    {
        $dues = 0;
        $dtMonth = (new \DateTime($salMonth))->modify('-1 month')->format('Y-m-t');
        
        $salOrders = $this->getEmpSalOrders($empId);
        $extExpenses = $this->getEmpExtExpenses($empId);

        $loopCount = 0;
        while (true) {
            $loopCount++;
            if ($loopCount > 36) {
                break; // Safety limit to prevent execution timeout on sparse databases
            }

            $matrix = $this->getSalaryMatrix($empId, $dtMonth);
            if (empty($matrix)) {
                break;
            }
            
            $toBePaid = (float) $matrix[11]['net_toBePaid'];
            $paid = 0.0;
            
            // Filter salorders in memory
            $matchedOrders = array_filter($salOrders, function($o) use ($dtMonth) {
                return $o->sor_month === $dtMonth;
            });

            foreach ($matchedOrders as $order) {
                $toBePaid += (float)$order->sor_award - (float)$order->sor_penalty + (float)$order->sor_withheld - (float)$order->sor_loaned;
                $paid += (float)$order->sor_salary;
            }

            // Filter extcompenses in memory
            $matchedExt = array_filter($extExpenses, function($e) use ($dtMonth) {
                return $e->ecp_month === $dtMonth;
            });

            foreach ($matchedExt as $ext) {
                $paid += (float)$ext->ecp_amount;
            }

            $prevMonthEnd = (new \DateTime($dtMonth))->modify('-1 month')->format('Y-m-t');
            $prevMonthDays = (int)(new \DateTime($prevMonthEnd))->format('d');
            
            if ((int)$matrix[11]['unpaid'] === $prevMonthDays && $paid == 0.0) {
                $toBePaid = 0.0;
            }
            
            $diff = $toBePaid - $paid;
            $dues += $diff;
            
            if (round($toBePaid, 0) == round($paid, 0)) {
                break;
            }
            
            $dtMonth = (new \DateTime($dtMonth))->modify('-1 month')->format('Y-m-t');
        }

        return $dues;
    }

    public function getPrjSalForecast($headId)
    {
        $lastSalMonth = DB::table('fin.salorders')
            ->where('sor_status', 'Fulfilled')
            ->where('sor_hed_id', $headId)
            ->max('sor_month');
            
        $dtLastSalMonth = $lastSalMonth ?? '1900-01-01';

        $contracts = DB::table('hr.contracts as ctr')
            ->join('hr.emps as emp', 'ctr.ctr_num', '=', 'emp.emp_id')
            ->join('fin.empeffheads as eeh', 'ctr.ctr_num', '=', 'eeh.eeh_emp_id')
            ->join('hr.contractplans as cpn', 'ctr.ctr_id', '=', 'cpn.cpn_ctr_id')
            ->where('eeh.eeh_status', 'Open')
            ->where('cpn.cpn_enddt', '>', $dtLastSalMonth)
            ->where('cpn.cpn_hed_id', $headId)
            ->select('ctr.ctr_num', 'cpn.cpn_enddt')
            ->get();

        $totalForecast = 0.0;
        foreach ($contracts as $c) {
            $matrix = $this->getSalaryMatrix($c->ctr_num, $c->cpn_enddt);
            if (empty($matrix)) continue;
            
            $s = 0;
            for ($n = 8; $n <= 10; $n++) {
                if (!isset($matrix[$n])) break;
                
                $effhedStr = $matrix[$n]['effhed_id'];
                if (!$effhedStr) continue;
                
                $effhedParts = explode(' ', $effhedStr);
                $effhedId = (int) $effhedParts[0];
                
                if ($effhedId == $headId) {
                    $lngSal = (float) $matrix[$n]['prorated_salary'];
                    $s++;
                    if ($s == 1) {
                        $lngSal += $this->calculateArrDues($c->ctr_num, $c->cpn_enddt);
                    }
                    $totalForecast += $lngSal;
                }
            }
        }

        return round($totalForecast, 2);
    }

    public function getUaSalForecast($unitId)
    {
        $contracts = DB::table('hr.contracts as ctr')
            ->join('hr.emps as emp', 'ctr.ctr_num', '=', 'emp.emp_id')
            ->join('fin.empeffheads as eeh', 'ctr.ctr_num', '=', 'eeh.eeh_emp_id')
            ->join('hr.contractplans as cpn', 'ctr.ctr_id', '=', 'cpn.cpn_ctr_id')
            ->where('ctr.ctr_unt_id', $unitId)
            ->where('eeh.eeh_status', 'Open')
            ->whereNull('cpn.cpn_hed_id')
            ->select('ctr.ctr_num', 'cpn.cpn_enddt')
            ->get();

        $totalForecast = 0.0;
        foreach ($contracts as $c) {
            $matrix = $this->getSalaryMatrix($c->ctr_num, $c->cpn_enddt);
            if (empty($matrix)) continue;
            
            $s = 0;
            for ($n = 8; $n <= 10; $n++) {
                if (!isset($matrix[$n])) break;
                
                $effhedStr = $matrix[$n]['effhed_id'];
                if (!$effhedStr) {
                    $lngSal = (float) $matrix[$n]['prorated_salary'];
                    $s++;
                    if ($s == 1) {
                        $lngSal += $this->calculateArrDues($c->ctr_num, $c->cpn_enddt);
                    }
                    $totalForecast += $lngSal;
                }
            }
        }

        return round($totalForecast, 2);
    }

    public function getPrjSalUnderway($headId)
    {
        $contracts = DB::table('hr.emps as emp')
            ->join('hr.ctrcases as ctc', 'emp.emp_id', '=', 'ctc.ctc_emp_id')
            ->join('hr.ctrcaseplans as ccp', 'ctc.ctc_id', '=', 'ccp.ccp_ctc_id')
            ->where('ccp.ccp_hed_id', $headId)
            ->where('ctc.ctc_status', 'like', 'Under%')
            ->select('ctc.ctc_id', 'ctc.ctc_emp_id', 'ctc.ctc_newsalary', 'ccp.ccp_startdt', 'ccp.ccp_enddt', 'ccp.ccp_hed_id')
            ->get();

        $totalUnderway = 0.0;
        foreach ($contracts as $c) {
            $dtStart = new \DateTime($c->ccp_startdt);
            $dtEnd = new \DateTime($c->ccp_enddt);
            
            $calStart = (new \DateTime($c->ccp_startdt))->format('Y-m-01');
            $calEnd = (new \DateTime($c->ccp_startdt))->format('Y-m-t');
            
            $calDays = (int) (new \DateTime($calEnd))->diff(new \DateTime($calStart))->days + 1;
            $periodDays = (int) $dtEnd->diff($dtStart)->days + 1;
            
            $prorated = ((float) $c->ctc_newsalary / $calDays) * $periodDays;
            $totalUnderway += $prorated;
        }

        return round($totalUnderway, 2);
    }

    public function getCfSalForecast()
    {
        $lastSalMonth = DB::table('fin.salorders')
            ->where('sor_status', 'Fulfilled')
            ->whereNull('sor_hed_id')
            ->max('sor_month');
            
        $dtLastSalMonth = $lastSalMonth ?? '1900-01-01';

        $contracts = DB::table('hr.contracts as ctr')
            ->join('hr.emps as emp', 'ctr.ctr_num', '=', 'emp.emp_id')
            ->join('fin.empeffheads as eeh', 'ctr.ctr_num', '=', 'eeh.eeh_emp_id')
            ->where(function($query) {
                $query->where('ctr.ctr_unt_id', '<', 200000)
                      ->orWhere('ctr.ctr_unt_id', '>=', 800000);
            })
            ->where('eeh.eeh_status', 'Open')
            ->where(DB::raw("COALESCE(ctr.ctr_termindt, ctr.ctr_enddt)"), '>=', (new \DateTime($dtLastSalMonth))->format('Y-m-01'))
            ->select('ctr.ctr_id', 'ctr.ctr_num', 'ctr.ctr_startdt', 'ctr.ctr_enddt', 'ctr.ctr_termindt', 'ctr.ctr_salary')
            ->get();

        $totalForecast = 0.0;
        foreach ($contracts as $c) {
            $ctrEffEnddt = $c->ctr_termindt ?? $c->ctr_enddt;
            
            $dtMonth = (new \DateTime($dtLastSalMonth))->format('Y-m-01');
            $s = 0;
            while ($dtMonth <= $ctrEffEnddt) {
                $matrix = $this->getSalaryMatrix($c->ctr_num, $dtMonth);
                if (!empty($matrix)) {
                    $lngSal = (float) $matrix[11]['prorated_salary'];
                    $s++;
                    if ($s == 1) {
                        $lngSal += $this->calculateArrDues($c->ctr_num, $dtMonth);
                    }
                    $totalForecast += $lngSal;
                }
                $dtMonth = (new \DateTime($dtMonth))->modify('+1 month')->format('Y-m-01');
            }
        }

        return round($totalForecast, 2);
    }

    public function getCfSalUnderway()
    {
        $contracts = DB::table('hr.emps as emp')
            ->join('hr.ctrcases as ctc', 'emp.emp_id', '=', 'ctc.ctc_emp_id')
            ->join('hr.ctrcaseplans as ccp', 'ctc.ctc_id', '=', 'ccp.ccp_ctc_id')
            ->where(function($query) {
                $query->where('ctc.ctc_unt_id', '<', 200000)
                      ->orWhere('ctc.ctc_unt_id', '>=', 800000);
            })
            ->where('ctc.ctc_status', 'like', 'Under%')
            ->select('ctc.ctc_id', 'ctc.ctc_emp_id', 'ctc.ctc_newsalary', 'ccp.ccp_startdt', 'ccp.ccp_enddt', 'ccp.ccp_hed_id')
            ->get();

        $totalUnderway = 0.0;
        foreach ($contracts as $c) {
            $dtStart = new \DateTime($c->ccp_startdt);
            $dtEnd = new \DateTime($c->ccp_enddt);
            
            $calStart = (new \DateTime($c->ccp_startdt))->format('Y-m-01');
            $calEnd = (new \DateTime($c->ccp_startdt))->format('Y-m-t');
            
            $calDays = (int) (new \DateTime($calEnd))->diff(new \DateTime($calStart))->days + 1;
            $periodDays = (int) $dtEnd->diff($dtStart)->days + 1;
            
            $prorated = ((float) $c->ctc_newsalary / $calDays) * $periodDays;
            $totalUnderway += $prorated;
        }

        return round($totalUnderway, 2);
    }
}
