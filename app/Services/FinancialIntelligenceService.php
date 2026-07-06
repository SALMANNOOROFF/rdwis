<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Purchase;

class FinancialIntelligenceService
{
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
        $status['csrf_share'] = (float) ($shares->sha_cf ?? 0); // Alias for view compatibility
        $status['prj_share'] = (float) ($shares->sha_prj ?? 0);

        // 4. Received (Cash Inflow) - Exact VBA logic
        $pccReceived = DB::table('fin.sharesinstall')
            ->where('shi_hed_id', $headId)
            ->sum(DB::raw('COALESCE(shi_pcc,0)'));
        $cfReceived = DB::table('fin.sharesinstall')
            ->where('shi_hed_id', $headId)
            ->sum(DB::raw('COALESCE(shi_cf,0)'));
        $status['pcc_received'] = (float) $pccReceived;
        $status['cf_received'] = (float) $cfReceived;
        $status['received'] = $status['pcc_received'] + $status['cf_received'];

        // 5. Project Expenditure (Actual Spending) - Use trn_amount1 (without GST)
        $prjExpenditure = DB::table('fin.transactions')
            ->join('fin.commitments', 'trn_cmt_id', '=', 'cmt_id')
            ->where('cmt_hed_id', $headId)
            ->sum('trn_amount1');
        
        $status['prj_expenditure'] = abs((float) $prjExpenditure);

        // 6. CF Expenditure
        $cfExpenditure = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->where('c.cmt_effhed_id', $headId)
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb', 'Sa'])
            ->whereNotNull('c.cmt_sudohed')
            ->where('c.cmt_sudohed', '<>', '')
            ->select(DB::raw('SUM(CASE WHEN t.trn_transtype = 1 THEN t.trn_amount1 ELSE t.trn_amount2 END) as total'))
            ->value('total');

        $status['cf_expenditure'] = round(abs((float) ($cfExpenditure ?? 0)), 2);

        // Combined Expenditure
        $status['expenditure'] = $status['prj_expenditure'] + $status['cf_expenditure'];

        // 7. Inter-Project Netting Engine
        $nettingResult = $this->calculateInterProjectNetting($headId);
        $status['exp_this_account'] = $status['expenditure'];
        $status['exp_other_accounts'] = $nettingResult['exp_other_accounts'];
        $status['others_exp_this_account'] = $nettingResult['others_exp_this_account'];

        // 8. Outstanding Commitments - Pipelines
        $outstanding = $this->calculateOutstandingCommitments($headId);
        $status['prj_commitments'] = $outstanding['purchases'] + $outstanding['salaries'] + $outstanding['transfers'];
        $status['cf_commitments'] = 0; // CF commitments already handled in above
        $status['commitments'] = $status['prj_commitments'];

        // 9. Project In Process (IPC) - Cases released by case division that are with finance
        $prjInProcess = DB::table('pur.purcases')
            ->where('pcs_hed_id', $headId)
            ->where(function($query) {
                $query->where(DB::raw('LOWER(pcs_status)'), 'finance')
                      ->orWhere(DB::raw('LOWER(pcs_status)'), 'with finance')
                      ->orWhere(DB::raw('LOWER(pcs_status)'), 'with dfinance')
                      ->orWhere(DB::raw('LOWER(pcs_status)'), 'audit')
                      ->orWhere(DB::raw('LOWER(pcs_status)'), 'command')
                      ->orWhere(DB::raw('LOWER(pcs_status)'), 'approved_pending_commit');
            })
            ->whereNotNull('pur.purcases.pcs_frm_id')
            ->sum('pcs_price');
        
        $status['prj_in_process'] = (float) $prjInProcess;

        // 10. CF In Process - Implement fin_docs_ipc UNION logic with case-insensitive status handling
        $cfInProcessRaw = DB::select(
            'WITH fin_docs_ipc AS (
                SELECT 
                    CASE WHEN pcs_transtype = 1 THEN pcs_intprice ELSE pcs_price END AS amount
                FROM pur.purcases
                WHERE 
                    pcs_effhed_id = ?
                    AND pcs_sudohed IS NOT NULL
                    AND pcs_sudohed <> \'\'
                    AND LOWER(pcs_status) IN (
                        \'finance\', 
                        \'with finance\', 
                        \'with dfinance\', 
                        \'audit\', 
                        \'command\', 
                        \'approved_pending_commit\'
                    )
                
                UNION ALL
                
                SELECT 
                    CASE WHEN sor_transtype = 1 THEN sor_salary ELSE sor_netsalary END AS amount
                FROM fin.salorders
                WHERE 
                    sor_effhed_id = ?
                    AND sor_sudohed IS NOT NULL
                    AND sor_sudohed <> \'\'
                    AND LOWER(sor_status) IN (\'in process\', \'in-process\')
                
                UNION ALL
                
                SELECT 
                    srq_netsalary AS amount
                FROM hr.salreqs
                WHERE 
                    srq_effhed_id = ?
                    AND srq_sudohed IS NOT NULL
                    AND srq_sudohed <> \'\'
                    AND LOWER(srq_status) IN (\'in process\', \'in-process\')
            )
            SELECT SUM(amount) AS total FROM fin_docs_ipc',
            [$headId, $headId, $headId]
        );

        $cfInProcessSum = $cfInProcessRaw[0]->total ?? 0;
        $status['cf_in_process'] = round(abs((float) ($cfInProcessSum ?? 0)), 2);

        // Combined In Process
        $status['in_process'] = $status['prj_in_process'] + $status['cf_in_process'];

        // 11. Derived Calculations (Exact VBA formulas with scopes separated)
        // Project-specific metrics
        $status['prj_received'] = $status['pcc_received'];
        $status['prj_balance'] = round($status['prj_received'] - $status['prj_expenditure'], 2);
        $status['prj_available'] = round($status['prj_balance'] - $status['prj_commitments'] - $status['prj_in_process'], 2);
        $status['prj_yet_to_be_received'] = round($status['prj_share'] - $status['prj_received'], 2);
        
        // CSRF-specific metrics
        $status['cf_balance'] = round($status['cf_received'] - $status['cf_expenditure'], 2);
        $status['cf_available'] = round($status['cf_balance'] - $status['cf_commitments'] - $status['cf_in_process'], 2);
        $status['cf_yet_to_be_received'] = round($status['cf_share'] - $status['cf_received'], 2);
        
        // Combined/Global metrics
        $status['balance'] = round($status['received'] - $status['expenditure'], 2);
        $status['available'] = $status['prj_available'] + $status['cf_available'];
        $status['yet_to_be_received'] = $status['prj_yet_to_be_received'] + $status['cf_yet_to_be_received'];
        $status['can_be_spent'] = round($status['acc_share'] - $status['expenditure'] - $status['commitments'] - $status['in_process'], 2);
        $status['remaining'] = round($status['rdw_share'] - $status['expenditure'] - $status['commitments'] - $status['in_process'], 2);

        // 12. Receivables (Mission Based) - Using fin.msncosts instead of prj.milestones
        $receivables = $this->calculateMilestoneReceivables($headId);
        $status['receivable_completed'] = $receivables['completed'];
        $status['receivable_current'] = $receivables['current'];
        $status['available_after_receivables'] = $status['available'] + $status['receivable_completed'];

        // 14. Percentage Tracking
        $status['pct_utilized'] = $status['allocation'] > 0 ? ($status['expenditure'] / $status['allocation']) * 100 : 0;
        $status['pct_committed'] = $status['allocation'] > 0 ? ($status['commitments'] / $status['allocation']) * 100 : 0;

        // 15. Loans
        $loans = $this->getLoans($headId);
        $status['pcc_loans_given'] = $loans->pcc_loans_given;
        $status['others_loans_taken'] = $loans->others_loans_taken;

        return (object) $status;
    }

    private function calculateInterProjectNetting($headId)
    {
        // Query the netting transactions
        $nettingRows = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->whereIn('c.cmt_type', ['Ps', 'Rb', 'Sa', 'LO', 'TO'])
            ->where('t.trn_noloan', '0')
            ->where(function($query) use ($headId) {
                $query->where('c.cmt_effhed_id', $headId)
                      ->orWhere('c.cmt_hed_id', $headId);
            })
            ->where(DB::raw('COALESCE(c.cmt_effhed_id, 0)'), '!=', DB::raw('COALESCE(c.cmt_hed_id, 160001)'))
            ->select(
                'c.cmt_hed_id',
                'c.cmt_effhed_id',
                't.trn_amount2'
            )
            ->get();

        $expOtherAccounts = 0;
        $othersExpThisAccount = 0;

        foreach ($nettingRows as $row) {
            if ($row->cmt_hed_id == $headId && $row->cmt_effhed_id != $headId) {
                // Project exp from other accounts (addition)
                $expOtherAccounts += (float) $row->trn_amount2;
            } elseif ($row->cmt_effhed_id == $headId && $row->cmt_hed_id != $headId) {
                // Other's exp from this account (subtraction)
                $othersExpThisAccount += (float) $row->trn_amount2 * (-1);
            }
        }

        return [
            'exp_other_accounts' => round($expOtherAccounts, 2),
            'others_exp_this_account' => round($othersExpThisAccount, 2)
        ];
    }

    private function calculateOutstandingCommitments($headId)
    {
        // Purchases Pipeline: cmt_type IN ("Ps", "Pt", "Rb")
        $purchases = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->where('c.cmt_status', 'Awaited')
            ->whereIn('c.cmt_type', ['Ps', 'Pt', 'Rb'])
            ->where('c.cmt_effhed_id', $headId)
            ->sum('t.trn_amount2');

        // Salaries Pipeline: cmt_type IN ("Sa")
        $salaries = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->where('c.cmt_status', 'Awaited')
            ->where('c.cmt_type', 'Sa')
            ->where('c.cmt_effhed_id', $headId)
            ->sum('t.trn_amount2');

        // Transfers Pipeline: cmt_type IN ("FI", "FO", "LI", "LO", "TI", "TO")
        $transfers = DB::table('fin.commitments as c')
            ->join('fin.transactions as t', 'c.cmt_id', '=', 't.trn_cmt_id')
            ->where('c.cmt_status', 'Awaited')
            ->whereIn('c.cmt_type', ['FI', 'FO', 'LI', 'LO', 'TI', 'TO'])
            ->where('c.cmt_effhed_id', $headId)
            ->sum('t.trn_amount2');

        return [
            'purchases' => round(abs((float) $purchases), 2),
            'salaries' => round(abs((float) $salaries), 2),
            'transfers' => round(abs((float) $transfers), 2)
        ];
    }

    private function calculateMilestoneReceivables($headId)
    {
        // Calculate milestone receivables (using existing columns from fin.msncosts directly)
        $msnCosts = DB::table('fin.msncosts')
            ->where('mct_hed_id', $headId)
            ->sum('mct_cost');

        // For now, return all as current since we don't have status
        return [
            'completed' => 0,
            'current' => round((float)$msnCosts, 2)
        ];
    }

    public function getSubheadBreakdown($headId)
    {
        $subheads = DB::table('fin.subheads')
            ->where('sbh_hed_id', $headId)
            ->orderBy('sbh_id')
            ->get();

        $result = [];
        foreach ($subheads as $sh) {
            // A. Purchase Expenditure (Ratio Based)
            $purExp = DB::table('fin.transactions')
                ->join('fin.commitments', 'trn_cmt_id', '=', 'cmt_id')
                ->join('pur.purcases_shd', 'cmt_docid', '=', 'pcd_pcs_id')
                ->join('pur.purcases', 'cmt_docid', '=', 'pcs_id')
                ->where('cmt_hed_id', $headId)
                ->where('pcd_subhead', $sh->sbh_name)
                ->where('pcs_type', DB::raw('pcd_type'))
                ->sum(DB::raw('trn_amount1 * pcd_ratio'));

            // B. Salary Expenditure (Ratio Based)
            $salExp = DB::table('fin.salorders')
                ->join('fin.salorders_shd', 'sor_id', '=', 'sod_sor_id')
                ->where('sor_hed_id', $headId)
                ->where('sod_subhead', $sh->sbh_name)
                ->sum(DB::raw('sor_netsalary * sod_ratio'));

            // C. Commitments (Ratio Based) - Net of payments, sign-flipped
            $paidByCommitment = DB::table('fin.transactions')
                ->select('trn_cmt_id', DB::raw('SUM(trn_amount1) as paid'))
                ->groupBy('trn_cmt_id');

            $subheadCommitments = DB::table('fin.commitments as c')
                ->join('pur.purcases_shd as shd', 'c.cmt_docid', '=', 'shd.pcd_pcs_id')
                ->join('pur.purcases as pcs', 'c.cmt_docid', '=', 'pcs.pcs_id')
                ->leftJoinSub($paidByCommitment, 'paid_sub', function ($join) {
                    $join->on('c.cmt_id', '=', 'paid_sub.trn_cmt_id');
                })
                ->where('c.cmt_hed_id', $headId)
                ->where('shd.pcd_subhead', $sh->sbh_name)
                ->where('pcs.pcs_type', DB::raw('shd.pcd_type'))
                ->select(DB::raw('SUM((c.cmt_amount - COALESCE(paid_sub.paid, 0)) * shd.pcd_ratio) as net_outstanding'))
                ->value('net_outstanding');

            // D. In Process (Ratio Based) - Cases released by case division that are with finance
            $purIPC = DB::table('pur.purcases')
                ->join('pur.purcases_shd', 'pur.purcases.pcs_id', '=', 'pur.purcases_shd.pcd_pcs_id')
                ->where('pur.purcases.pcs_hed_id', $headId)
                ->where(function ($query) {
                    $query->where(DB::raw('LOWER(pur.purcases.pcs_status)'), 'finance')
                        ->orWhere(DB::raw('LOWER(pur.purcases.pcs_status)'), 'with finance')
                        ->orWhere(DB::raw('LOWER(pur.purcases.pcs_status)'), 'with dfinance')
                        ->orWhere(DB::raw('LOWER(pur.purcases.pcs_status)'), 'audit')
                        ->orWhere(DB::raw('LOWER(pur.purcases.pcs_status)'), 'command')
                        ->orWhere(DB::raw('LOWER(pur.purcases.pcs_status)'), 'approved_pending_commit');
                })
                ->whereNotNull('pur.purcases.pcs_frm_id')
                ->where('pur.purcases_shd.pcd_subhead', $sh->sbh_name)
                ->where('pur.purcases.pcs_type', DB::raw('pur.purcases_shd.pcd_type'))
                ->sum(DB::raw('ROUND(pcs_price * pcd_ratio, 0)'));

            $totalExp = abs((float) $purExp) + abs((float) $salExp);
            $totalCom = round(abs((float) ($subheadCommitments ?? 0)), 2);
            $totalIPC = (float) $purIPC;

            $result[] = [
                'name' => $sh->sbh_name,
                'allocation' => (float) $sh->sbh_alloc,
                'expenditure' => $totalExp,
                'commitments' => $totalCom,
                'in_process' => $totalIPC,
                'remaining' => round((float) $sh->sbh_alloc - $totalExp - $totalCom - $totalIPC, 2),
                'can_be_spent' => round((float) $sh->sbh_alloc - $totalExp - $totalCom - $totalIPC, 2)
            ];
        }

        return $result;
    }

    /**
     * Calculate loans given and taken for a head
     */
    public function getLoans($headId)
    {
        $loans = [];
        
        // Pcc Loans Given (lad_from = HeadId)
        $loansGiven = DB::table('fin.loanadjustments')
            ->where('lad_from', $headId)
            ->sum('lad_amount');
        $loans['pcc_loans_given'] = abs((float) $loansGiven); // Sign flipped per VBA
        
        // Others Loans Taken (lad_to = HeadId)
        $loansTaken = DB::table('fin.loanadjustments')
            ->where('lad_to', $headId)
            ->sum('lad_amount');
        $loans['others_loans_taken'] = abs((float) $loansTaken); // Sign flipped per VBA
        
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
}
