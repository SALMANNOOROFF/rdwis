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

        // 3. MTSS & CSRF Shares
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
        $status['csrf_share'] = (float) ($shares->sha_cf ?? 0);
        $status['pcc_share'] = (float) ($shares->sha_pcc ?? 0);
        $status['prj_share'] = (float) ($shares->sha_prj ?? 0);

        // 4. Received (Cash Inflow) - Matching 'fin_sharesinstall' pattern
        $received = DB::table('fin.sharesinstall')
            ->where('shi_hed_id', $headId)
            ->whereNotNull('shi_fitrn_id') // Only if actually paid/received
            ->sum(DB::raw('COALESCE(shi_pcc,0) + COALESCE(shi_prj,0)'));
        
        if ($received == 0) {
            $received = DB::table('fin.transfers')
                ->where('trf_tohed', $headId)
                ->where('trf_status', 'Paid')
                ->sum('trf_amount');
        }
        
        $status['received'] = (float) $received;

        // 5. Expenditure (Actual Spending) - Use trn_amount1 (without GST)
        $expenditure = DB::table('fin.transactions')
            ->join('fin.commitments', 'trn_cmt_id', '=', 'cmt_id')
            ->where('cmt_hed_id', $headId)
            ->sum('trn_amount1');
        
        $status['expenditure'] = abs((float) $expenditure);

        // 6. Outstanding Commitments - Only approved cases
        $commitments = DB::table('fin.commitments')
            ->join('pur.purcases', 'fin.commitments.cmt_docid', '=', 'pur.purcases.pcs_id')
            ->where('fin.commitments.cmt_hed_id', $headId)
            ->where(DB::raw('LOWER(pur.purcases.pcs_status)'), 'approved')
            ->sum('fin.commitments.cmt_amount');
        
        $status['commitments'] = (float) $commitments;

        // 7. In Process (IPC) - Cases released by case division that are with finance
        $inProcess = DB::table('pur.purcases')
            ->where('pcs_hed_id', $headId)
            ->where(function($query) {
                $query->where(DB::raw('LOWER(pcs_status)'), 'finance')
                      ->orWhere(DB::raw('LOWER(pcs_status)'), 'with finance')
                      ->orWhere(DB::raw('LOWER(pcs_status)'), 'with dfinance')
                      ->orWhere(DB::raw('LOWER(pcs_status)'), 'audit')
                      ->orWhere(DB::raw('LOWER(pcs_status)'), 'command')
                      ->orWhere(DB::raw('LOWER(pcs_status)'), 'approved_pending_commit');
            })
            ->sum('pcs_price');
        
        $status['in_process'] = (float) $inProcess;

        // 8. Derived Calculations (Matching Report 1:1)
        $status['balance'] = $status['received'] - $status['expenditure'];
        $status['available'] = $status['balance'] - $status['commitments'] - $status['in_process'];
        $status['yet_to_be_received'] = $status['rdw_share'] - $status['csrf_share'] - $status['received'];
        $status['remaining'] = $status['rdw_share'] - $status['expenditure'] - $status['commitments'] - $status['in_process'] - $status['csrf_share'];

        // 9. Receivables (Milestone Based)
        $achievedMilestonesCost = DB::table('prj.milestones')
            ->where('msn_xprj_id', $status['hed_prj_id'])
            ->where('msn_status', 'Achieved')
            ->sum('msn_cost');
        
        $status['receivable_completed'] = (float) $achievedMilestonesCost;
        $status['receivable_current'] = 0;
        $status['available_after_receivables'] = $status['available'] + $status['receivable_completed'];

        // 10. Expenditure Sources (Restored for View Compatibility)
        $status['exp_this_account'] = $status['expenditure'];
        $status['exp_other_accounts'] = 0; // Placeholder for future cross-head tracking
        $status['others_exp_this_account'] = 0; // Placeholder

        // 11. Percentage Tracking
        $status['pct_utilized'] = $status['allocation'] > 0 ? ($status['expenditure'] / $status['allocation']) * 100 : 0;
        $status['pct_committed'] = $status['allocation'] > 0 ? ($status['commitments'] / $status['allocation']) * 100 : 0;

        return (object) $status;
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
                ->where('cmt_hed_id', $headId)
                ->where('pcd_subhead', $sh->sbh_name)
                ->sum(DB::raw('trn_amount1 * pcd_ratio'));

            // B. Salary Expenditure (Ratio Based)
            $salExp = DB::table('fin.salorders')
                ->join('fin.salorders_shd', 'sor_id', '=', 'sod_sor_id')
                ->where('sor_hed_id', $headId)
                ->where('sod_subhead', $sh->sbh_name)
                ->sum(DB::raw('sor_netsalary * sod_ratio'));

            // C. Commitments (Ratio Based) - Only approved cases
            $purCom = DB::table('fin.commitments')
                ->join('pur.purcases_shd', 'fin.commitments.cmt_docid', '=', 'pur.purcases_shd.pcd_pcs_id')
                ->join('pur.purcases', 'fin.commitments.cmt_docid', '=', 'pur.purcases.pcs_id')
                ->where('fin.commitments.cmt_hed_id', $headId)
                ->where(DB::raw('LOWER(pur.purcases.pcs_status)'), 'approved')
                ->where('pur.purcases_shd.pcd_subhead', $sh->sbh_name)
                ->sum(DB::raw('fin.commitments.cmt_amount * pur.purcases_shd.pcd_ratio'));

            // D. In Process (Ratio Based) - Cases released by case division that are with finance
            $purIPC = DB::table('pur.purcases')
                ->join('pur.purcases_shd', 'pur.purcases.pcs_id', '=', 'pur.purcases_shd.pcd_pcs_id')
                ->where('pur.purcases.pcs_hed_id', $headId)
                ->where(function($query) {
                    $query->where(DB::raw('LOWER(pur.purcases.pcs_status)'), 'finance')
                          ->orWhere(DB::raw('LOWER(pur.purcases.pcs_status)'), 'with finance')
                          ->orWhere(DB::raw('LOWER(pur.purcases.pcs_status)'), 'with dfinance')
                          ->orWhere(DB::raw('LOWER(pur.purcases.pcs_status)'), 'audit')
                          ->orWhere(DB::raw('LOWER(pur.purcases.pcs_status)'), 'command')
                          ->orWhere(DB::raw('LOWER(pur.purcases.pcs_status)'), 'approved_pending_commit');
                })
                ->where('pur.purcases_shd.pcd_subhead', $sh->sbh_name)
                ->sum(DB::raw('pur.purcases.pcs_price * pur.purcases_shd.pcd_ratio'));

            $totalExp = abs((float) $purExp) + abs((float) $salExp);
            $totalCom = (float) $purCom;
            $totalIPC = (float) $purIPC;

            $result[] = [
                'name' => $sh->sbh_name,
                'allocation' => (float) $sh->sbh_alloc,
                'expenditure' => $totalExp,
                'commitments' => $totalCom,
                'in_process' => $totalIPC,
                'remaining' => (float) $sh->sbh_alloc - $totalExp - $totalCom - $totalIPC
            ];
        }

        return $result;
    }

}
