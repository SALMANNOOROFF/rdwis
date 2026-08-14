<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\FinancialIntelligenceService;

class FinanceReportsController extends Controller
{
    protected $finService;

    public function __construct(FinancialIntelligenceService $finService)
    {
        $this->finService = $finService;
    }

    /**
     * Display the Finance Reports Hub.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Determine Mode & Bounds
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHqUser = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw', 'it', 'fin'], true);

        if ($isHqUser) {
            $lower = $user->acc_lowerm ?: 0;
            $upper = $user->acc_upperm ?: 99999999;
        } else {
            $lower = (int) $user->acc_lowers;
            $upper = (int) $user->acc_uppers;

            if ($lower === 0 && $upper === 0) {
                $lower = (int) $user->acc_lowerm;
                $upper = (int) $user->acc_upperm;
            }

            if ($lower === 0 && $upper === 0) {
                $lower = (int) $user->acc_unt_id;
                $upper = (int) $user->acc_unt_id;
            }
        }

        $isDivisionUser = $user->isDivision();

        // Fetch projects for selection (Filtered by division for division users)
        $projectsQuery = DB::table('prj.projects')->orderBy('prj_code');
        if ($isDivisionUser && $user->acc_unt_id) {
            $projectsQuery->where('prj_unt_id', $user->acc_unt_id);
        }
        $projects = $projectsQuery->get(['prj_id', 'prj_code', 'prj_title', 'prj_unt_id']);

        // Fetch divisions/units for selection (Filtered by division for division users)
        $unitsQuery = DB::table('cen.units')->where('unt_type', 'Division')->orderBy('unt_name');
        if ($isDivisionUser && $user->acc_unt_id) {
            $unitsQuery->where('unt_id', $user->acc_unt_id);
        }
        $units = $unitsQuery->get(['unt_id', 'unt_name']);

        // Fetch all firms for selection
        $firms = DB::table('frm.firmz')
            ->orderBy('frm_name')
            ->get(['frm_id', 'frm_name']);

        // All columns with their display labels
        $columns = [
            'id' => 'Item ID',
            'desc' => 'Description',
            'category' => 'Category (Asset/Inventory)',
            'subtype' => 'Subtype',
            'qty' => 'Quantity',
            'price' => 'Unit Price (Rs)',
            'total_value' => 'Total Value (Rs)',
            'status' => 'Status',
            'person' => 'Custodian / Issued To',
            'location' => 'Location',
            'division' => 'Division / Unit',
            'head_code' => 'Project Head Code',
            'purchase_case' => 'Purchase Case Title',
            'charge_date' => 'Charge Date',
            'disposal_date' => 'Disposal Date',
            'remarks' => 'Remarks'
        ];

        return view('fin.reports', compact('projects', 'units', 'columns', 'firms'));
    }

    /**
     * Retrieve data preview for the reports generator.
     */
    public function getReportData(Request $request)
    {
        try {
            $type = $request->query('type');
            $limit = (int)$request->query('limit', 10);
            
            $data = $this->queryReport($request, $limit);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export the full report data as an Excel-compatible CSV file.
     */
    public function exportExcel(Request $request)
    {
        $type = $request->query('type');
        $fileName = 'finance_' . ($type ?: 'report') . '_' . date('Ymd_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($request) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM to ensure Excel opens with correct encoding (Urdu and special chars)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            $data = $this->queryReport($request, null);
            
            if (empty($data)) {
                fputcsv($file, ['No data found for the selected filters']);
                fclose($file);
                return;
            }
            
            // Retrieve selected columns from query/body parameters
            $selectedCols = $request->input('columns', []);
            
            $allColumnsMap = [
                // General & Inventory / Assets
                'id' => 'ID / Item ID',
                'desc' => 'Description',
                'category' => 'Category',
                'subtype' => 'Subtype',
                'qty' => 'Quantity',
                'price' => 'Unit Price',
                'total_value' => 'Total Value',
                'status' => 'Status',
                'person' => 'Custodian / Issued To',
                'location' => 'Location',
                'division' => 'Division / Unit',
                'head_code' => 'Project Head Code',
                'purchase_case' => 'Purchase Case Title',
                'charge_date' => 'Charge Date',
                'disposal_date' => 'Disposal Date',
                'remarks' => 'Remarks',
                
                // Project Financial & Allocations / Shares
                'code' => 'Project Code',
                'title' => 'Project Title',
                'approved_budget' => 'Approved Budget',
                'allocation' => 'Allocation',
                'spent_prj' => 'Spent (Prj)',
                'spent_cf' => 'Spent (CF)',
                'total_spent' => 'Total Spent',
                'remaining' => 'Remaining Balance',
                'commitments' => 'Commitments',
                'in_process' => 'In Process',
                'team_size' => 'Team Size',
                'head_name' => 'Head Name',
                'project_code' => 'Project Code',
                'sha_cf' => 'CF Share (Rs)',
                'sha_pcc' => 'PCC Share (Rs)',
                'sha_prj' => 'Project Share (Rs)',
                'sha_prj_sal' => 'Salary Share (Rs)',
                'sha_prj_pur' => 'Purchase Share (Rs)',
                
                // Accounts Status
                'acc_id' => 'Account ID',
                'acc_name' => 'Full Name',
                'acc_username' => 'Username',
                'acc_title' => 'Title',
                'acc_desig' => 'Designation',
                'acc_desigshort' => 'Short Designation',
                'acc_status' => 'Account Status',
                'acc_untname' => 'Unit Name',
                'acc_untarea' => 'Unit Area',
                'acc_level' => 'Level',
                'acc_startdt' => 'Start Date',
                'acc_enddt' => 'End Date',

                // Subheads Status
                'sbh_id' => 'Subhead ID',
                'sbh_name' => 'Subhead Name',
                'sbh_alloc' => 'Allocation (Rs)',

                // CSRF Status
                'cf_share' => 'CF Share (Rs)',
                'cf_received' => 'CF Received (Rs)',
                'cf_expenditure' => 'CF Spent (Rs)',
                'cf_balance' => 'CF Balance (Rs)',
                'cf_commitments' => 'CF Commitments (Rs)',
                'cf_in_process' => 'CF In Process (Rs)',
                'cf_available' => 'CF Available (Rs)',

                // Monthly Return & Fund Shifting / Transfers
                'trn_id' => 'Transaction ID',
                'trn_date' => 'Transaction Date',
                'trn_amount1' => 'Amount 1 (Rs)',
                'trn_amount2' => 'Amount 2 (Rs)',
                'trn_tax1' => 'Tax 1 (Rs)',
                'trn_balance' => 'Balance (Rs)',
                'trn_transtype' => 'Transaction Type',
                'date' => 'Date',
                'amount' => 'Amount',
                'type_label' => 'Transfer Type',
                'from_code' => 'From Head Code',
                'from_name' => 'From Head Name',
                'from_division' => 'From Division',
                'from_old_val' => 'From Old Value',
                'from_new_val' => 'From New Value',
                'to_code' => 'To Head Code',
                'to_name' => 'To Head Name',
                'to_division' => 'To Division',
                'to_old_val' => 'To Old Value',
                'to_new_val' => 'To New Value',

                // PCs Awaiting Payment
                'pcs_id' => 'PC ID',
                'pcs_title' => 'PC Title',
                'pcs_date' => 'PC Date',
                'pcs_status' => 'PC Status',
                'pcs_price' => 'PC Price (Rs)',
                'pcs_intprice' => 'Int Price (Rs)',
                'pcs_midprice' => 'Mid Price (Rs)',

                // Employees
                'emp_id' => 'Employee ID',
                'emp_name' => 'Employee Name',
                'emp_title' => 'Employee Title',
                'emp_rank' => 'Employee Rank',
                'emp_status' => 'Employee Status',
                'emp_joindt' => 'Join Date',
                'emp_lastdt' => 'Last Date',
                'emp_remarks' => 'Employee Remarks',

                // Firms
                'frm_name' => 'Firm Name',
                'frm_type' => 'Firm Type',
                
                // Head History & Shifting
                'hed_opendt' => 'Opened Date',
                'reversals_count' => 'Reversals Filed',
                'shifts_count' => 'Fund Shifts',
                'last_shift_amount' => 'Last Shift Amount',
                'last_shift_date' => 'Last Shift Date',
                'balance_before' => 'Balance Before Shift',
                'balance_after' => 'Balance After Shift',

                // Attachments
                'pat_id' => 'Attachment ID',
                'pat_objtype' => 'Object Type',
                'pat_objid' => 'Object ID',
                'pat_type' => 'Attachment Type',
                'pat_path' => 'File Path',
                'source_type' => 'Attachment Source',
                'file_type' => 'File Type',
                'file_path' => 'File Path'
            ];
            
            // Retrieve selected columns from query/body parameters
            $selectedCols = $request->input('columns', []);
            
            $allColumnsMap = [
                // General & Inventory / Assets
                'id' => 'ID / Item ID',
                'desc' => 'Description',
                'category' => 'Category',
                'subtype' => 'Subtype',
                'qty' => 'Quantity',
                'price' => 'Unit Price',
                'total_value' => 'Total Value',
                'status' => 'Status',
                'person' => 'Custodian / Issued To',
                'location' => 'Location',
                'division' => 'Division / Unit',
                'head_code' => 'Project Head Code',
                'purchase_case' => 'Purchase Case Title',
                'charge_date' => 'Charge Date',
                'disposal_date' => 'Disposal Date',
                'remarks' => 'Remarks',
                
                // Project Financial & Allocations / Shares
                'code' => 'Project Code',
                'title' => 'Project Title',
                'approved_budget' => 'Approved Budget',
                'allocation' => 'Allocation',
                'mtss_share' => 'MTSS Share',
                'rdw_share' => 'RDW Share',
                'csrf_share' => 'CSRF Share',
                'equipment_share' => 'Equipment',
                'hr_share' => 'Utilized: HR',
                'misc_share' => 'Misc',
                'spent_prj' => 'Spent (Prj)',
                'spent_cf' => 'Spent (CF)',
                'total_spent' => 'Total Spent',
                'remaining' => 'Remaining Balance',
                'commitments' => 'Commitments',
                'in_process' => 'In Process',
                'team_size' => 'Team Size',
                'head_name' => 'Head Name',
                'project_code' => 'Project Code',
                
                // Accounts Status / Project Head Status
                'hed_code' => 'Head Code',
                'hed_name' => 'Head Name',
                'received' => 'Received',
                'expenditure' => 'Expenditure',
                'cf_share' => 'CSRF Share',
                'cf_expenditure' => 'CSRF Spent',
                'balance' => 'Balance',
                'available' => 'Available',
                
                // Accounts General
                'acc_id' => 'Account ID',
                'acc_name' => 'Full Name',
                'acc_username' => 'Username',
                'acc_title' => 'Title',
                'acc_desig' => 'Designation',
                'acc_desigshort' => 'Short Designation',
                'acc_status' => 'Account Status',
                'acc_untname' => 'Unit Name',
                'acc_untarea' => 'Unit Area',
                'acc_level' => 'Level',
                'acc_startdt' => 'Start Date',
                'acc_enddt' => 'End Date',

                // Subheads Status
                'sbh_id' => 'Subhead ID',
                'sbh_name' => 'Subhead Name',
                'sbh_alloc' => 'Allocation (Rs)',

                // Monthly Return & Fund Shifting / Transfers
                'trn_id' => 'Transaction ID',
                'trn_date' => 'Transaction Date',
                'trn_amount1' => 'Amount 1 (Rs)',
                'trn_amount2' => 'Amount 2 (Rs)',
                'trn_tax1' => 'Tax 1 (Rs)',
                'trn_balance' => 'Balance (Rs)',
                'trn_transtype' => 'Transaction Type',
                'date' => 'Date',
                'amount' => 'Amount',
                'type_label' => 'Transfer Type',
                'from_code' => 'From Head Code',
                'from_name' => 'From Head Name',
                'from_division' => 'From Division',
                'from_old_val' => 'From Old Value',
                'from_new_val' => 'From New Value',
                'to_code' => 'To Head Code',
                'to_name' => 'To Head Name',
                'to_division' => 'To Division',
                'to_old_val' => 'To Old Value',
                'to_new_val' => 'To New Value',

                // PCs Awaiting Payment
                'pcs_id' => 'PC ID',
                'pcs_title' => 'PC Title',
                'pcs_date' => 'PC Date',
                'pcs_status' => 'PC Status',
                'pcs_price' => 'PC Price (Rs)',
                'pcs_intprice' => 'Int Price (Rs)',
                'pcs_midprice' => 'Mid Price (Rs)',

                // Employees
                'emp_id' => 'Employee ID',
                'emp_cnic' => 'CNIC',
                'emp_name' => 'Employee Name',
                'emp_title' => 'Employee Title',
                'emp_rank' => 'Employee Rank',
                'emp_status' => 'Employee Status',
                'emp_joindt' => 'Join Date',
                'emp_lastdt' => 'Last Date',
                'emp_remarks' => 'Employee Remarks',
                'emp_locked' => 'Locked',
                'emp_cleared' => 'Cleared',
                'emp_photodest' => 'Photo Path',

                // Firms
                'frm_name' => 'Firm Name',
                'frm_type' => 'Firm Type',
                
                // Head History & Shifting
                'hed_opendt' => 'Opened Date',
                'reversals_count' => 'Reversals Filed',
                'shifts_count' => 'Fund Shifts',
                'last_shift_amount' => 'Last Shift Amount',
                'last_shift_date' => 'Last Shift Date',
                'balance_before' => 'Balance Before Shift',
                'balance_after' => 'Balance After Shift',

                // Attachments
                'pat_id' => 'Attachment ID',
                'pat_objtype' => 'Object Type',
                'pat_objid' => 'Object ID',
                'pat_type' => 'Attachment Type',
                'pat_path' => 'File Path',
                'source_type' => 'Attachment Source',
                'file_type' => 'File Type',
                'file_path' => 'File Path'
            ];
            
            if (empty($selectedCols)) {
                $selectedCols = array_keys($allColumnsMap);
            }
            
            // Build CSV Headers
            $csvHeaders = [];
            foreach ($selectedCols as $col) {
                if (isset($allColumnsMap[$col])) {
                    $csvHeaders[] = $allColumnsMap[$col];
                }
            }
            fputcsv($file, $csvHeaders);
            
            // Write Rows
            foreach ($data as $row) {
                $csvRow = [];
                foreach ($selectedCols as $col) {
                    $val = is_array($row) ? ($row[$col] ?? '') : ($row->$col ?? '');
                    
                    // Format monetary amounts if outputting raw numeric prices
                    if (in_array($col, ['price', 'total_value', 'approved_budget', 'allocation', 'spent_prj', 'spent_cf', 'total_spent', 'remaining', 'commitments', 'in_process', 'sha_cf', 'sha_pcc', 'sha_prj', 'sha_prj_sal', 'sha_prj_pur', 'sbh_alloc', 'cf_share', 'cf_received', 'cf_expenditure', 'cf_balance', 'cf_commitments', 'cf_in_process', 'cf_available', 'trn_amount1', 'trn_amount2', 'trn_tax1', 'trn_balance', 'amount', 'from_old_val', 'from_new_val', 'to_old_val', 'to_new_val', 'pcs_price', 'pcs_intprice', 'pcs_midprice', 'received', 'expenditure', 'cf_expenditure', 'balance', 'available', 'csrf_share', 'equipment_share', 'rdw_share', 'hr_share', 'misc_share'], true)) {
                        $val = 'Rs ' . number_format((float)$val, 0);
                    }
                    
                    $csvRow[] = $val;
                }
                fputcsv($file, $csvRow);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Internal helper to query report data based on parameters.
     */
    private function queryReport(Request $request, $limit = null)
    {
        $type = $request->query('type');
        if (empty($type)) {
            return [];
        }

        $user = Auth::user();
        
        // 1. Determine division/unit bounds for filtering
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHqUser = in_array($userArea, ['rdw', 'hqs', 'nrdi', 'rdwprj', 'prjrdw', 'it', 'fin'], true);

        if ($user->isDivision() && $user->acc_unt_id) {
            $lower = (int) $user->acc_unt_id;
            $upper = (int) $user->acc_unt_id;
            $unitId = (string) $user->acc_unt_id;
        } else if ($isHqUser) {
            $lower = $user->acc_lowerm ?: 0;
            $upper = $user->acc_upperm ?: 99999999;
            $unitId = $request->query('unit_id', 'All');
        } else {
            $lower = (int) $user->acc_lowers;
            $upper = (int) $user->acc_uppers;

            if ($lower === 0 && $upper === 0) {
                $lower = (int) $user->acc_lowerm;
                $upper = (int) $user->acc_upperm;
            }

            if ($lower === 0 && $upper === 0) {
                $lower = (int) $user->acc_unt_id;
                $upper = (int) $user->acc_unt_id;
            }
            $unitId = $request->query('unit_id', 'All');
        }
        $projectId = $request->query('project_id');
        $status = $request->query('status', 'All');
        $category = $request->query('category', 'All');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $firmId = $request->query('firm_id', 'All');

        if ($type === 'project_financial') {
            $projectId = $request->query('project_id');
            
            $query = DB::table('prj.projects as p')
                ->join('cen.units as u', 'p.prj_unt_id', '=', 'u.unt_id')
                ->whereBetween('p.prj_unt_id', [$lower, $upper])
                ->select('p.prj_id', 'p.prj_code', 'p.prj_title', 'p.prj_status', 'p.prj_aprvcost', 'u.unt_namesh');
                
            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('p.prj_id', (int)$projectId);
            }
            
            if ($limit) {
                $query->limit($limit);
            }
            
            $projects = $query->orderBy('p.prj_id', 'desc')->get();
            $results = [];
            
            foreach ($projects as $p) {
                // Find associated head
                $head = DB::table('cen.heads')->where('hed_prj_id', $p->prj_id)->first();
                
                $allocation = 0;
                $spentPrj = 0;
                $spentCf = 0;
                $totalSpent = 0;
                $remaining = 0;
                $commitments = 0;
                $inProcess = 0;
                $teamSize = 0;
                
                if ($head) {
                    $fin = $this->finService->getHeadStatus($head->hed_id);
                    $allocation = $fin->allocation ?? 0;
                    $spentPrj = $fin->prj_expenditure ?? 0;
                    $spentCf = $fin->cf_expenditure ?? 0;
                    $totalSpent = abs($spentPrj) + abs($spentCf);
                    $remaining = $fin->remaining ?? ($allocation - $totalSpent);
                    $commitments = $fin->prj_commitments ?? 0;
                    $inProcess = $fin->prj_in_process ?? 0;
                    
                    $teamSize = DB::table('hr.emps')
                        ->where('emp_hed_id', $head->hed_id)
                        ->whereRaw('LOWER(emp_status) IN (?, ?)', ['active', 'current'])
                        ->count();
                }
                
                $results[] = [
                    'code' => $p->prj_code,
                    'title' => $p->prj_title,
                    'division' => $p->unt_namesh,
                    'status' => $p->prj_status,
                    'approved_budget' => (float)$p->prj_aprvcost,
                    'allocation' => (float)$allocation,
                    'spent_prj' => (float)abs($spentPrj),
                    'spent_cf' => (float)abs($spentCf),
                    'total_spent' => (float)$totalSpent,
                    'remaining' => (float)$remaining,
                    'commitments' => (float)$commitments,
                    'in_process' => (float)$inProcess,
                    'team_size' => (int)$teamSize
                ];
            }
            
            return $results;
            
        } elseif ($type === 'inventory_assets') {
            $query = DB::table('ina.invatcomps as c')
                ->join('ina.invats as a', 'c.iac_ias_id', '=', 'a.ias_id')
                ->leftJoin('pur.purcases as p', 'a.ias_pcs_id', '=', 'p.pcs_id')
                ->leftJoin('cen.units as u', 'a.ias_unt_id', '=', 'u.unt_id')
                ->leftJoin('cen.heads as h', 'a.ias_effhed_id', '=', 'h.hed_id')
                ->select(
                    'c.iac_id',
                    'a.ias_desc',
                    'a.ias_type',
                    'a.ias_subtype',
                    'c.iac_qty',
                    'a.ias_price',
                    'c.iac_status',
                    'c.iac_person',
                    'c.iac_location',
                    'u.unt_namesh as division',
                    'h.hed_code as head_code',
                    'p.pcs_title as purchase_case',
                    'a.ias_chargedate as charge_date',
                    'c.iac_dispdate as disposal_date',
                    'c.iac_remarks as remarks'
                );
                
            // Unit bounds check
            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('a.ias_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('a.ias_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('a.ias_unt_id', (int)$unitId);
                }
            }
            
            // Category filter
            if ($category === 'Assets') {
                $query->where('a.ias_type', '7');
            } elseif ($category === 'Inventory') {
                $query->where('a.ias_type', '!=', '7');
            }
            
            // Status filter (Multiple Statuses supported)
            if (!empty($status) && $status !== 'All') {
                $statuses = is_array($status) ? $status : explode(',', $status);
                $query->whereIn('c.iac_status', $statuses);
            }
            
            // Project filter
            if (!empty($projectId) && $projectId !== 'All') {
                $headId = DB::table('cen.heads')->where('hed_prj_id', (int)$projectId)->value('hed_id');
                if ($headId) {
                    $query->where('a.ias_effhed_id', $headId);
                } else {
                    $query->where('a.ias_effhed_id', -1);
                }
            }
            
            if ($limit) {
                $query->limit($limit);
            }
            
            $items = $query->orderBy('c.iac_id', 'desc')->get();
            
            return $items->map(function($item) {
                return [
                    'id' => $item->iac_id,
                    'desc' => $item->ias_desc,
                    'category' => (int)$item->ias_type === 7 ? 'Asset' : 'Inventory',
                    'subtype' => $item->ias_subtype,
                    'qty' => (float)$item->iac_qty,
                    'price' => (float)$item->ias_price,
                    'total_value' => (float)($item->iac_qty * $item->ias_price),
                    'status' => $item->iac_status,
                    'person' => $item->iac_person ?: 'N/A',
                    'location' => $item->iac_location ?: 'N/A',
                    'division' => $item->division,
                    'head_code' => $item->head_code ?: 'N/A',
                    'purchase_case' => $item->purchase_case ?: 'N/A',
                    'charge_date' => $item->charge_date,
                    'disposal_date' => $item->disposal_date ?: 'N/A',
                    'remarks' => $item->remarks ?: 'N/A'
                ];
            })->all();
            
        } elseif ($type === 'fund_shifting') {
            $projectId = $request->query('project_id');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            
            $query = DB::table('fin.transfers as t')
                ->leftJoin('cen.heads as fh', 't.trf_fromhed', '=', 'fh.hed_id')
                ->leftJoin('cen.heads as th', 't.trf_tohed', '=', 'th.hed_id')
                ->leftJoin('cen.units as fu', 't.trf_fromunt', '=', 'fu.unt_id')
                ->leftJoin('cen.units as tu', 't.trf_tount', '=', 'tu.unt_id')
                ->select(
                    't.trf_id',
                    't.trf_date as date',
                    't.trf_title as title',
                    't.trf_amount as amount',
                    't.trf_type as type',
                    't.trf_status as status',
                    't.trf_fromhed',
                    't.trf_tohed',
                    'fh.hed_code as from_code',
                    'fh.hed_name as from_name',
                    'fu.unt_namesh as from_division',
                    'th.hed_code as to_code',
                    'th.hed_name as to_name',
                    'tu.unt_namesh as to_division'
                );
                
            $query->where(function($q) use ($lower, $upper) {
                $q->whereBetween('t.trf_fromunt', [$lower, $upper])
                  ->orWhereBetween('t.trf_tount', [$lower, $upper])
                  ->orWhereNull('t.trf_fromunt')
                  ->orWhereNull('t.trf_tount');
            });
            
            if (!empty($projectId) && $projectId !== 'All') {
                $headId = DB::table('cen.heads')->where('hed_prj_id', (int)$projectId)->value('hed_id');
                if ($headId) {
                    $query->where(function($q) use ($headId) {
                        $q->where('t.trf_fromhed', $headId)
                          ->orWhere('t.trf_tohed', $headId);
                    });
                } else {
                    $query->where('t.trf_id', -1);
                }
            }
            
            if (!empty($startDate)) {
                $query->where('t.trf_date', '>=', $startDate);
            }
            if (!empty($endDate)) {
                $query->where('t.trf_date', '<=', $endDate);
            }
            
            if ($limit) {
                $query->limit($limit);
            }
            
            $transfers = $query->orderBy('t.trf_id', 'desc')->get();
            $results = [];
            
            foreach ($transfers as $t) {
                $typeLabel = match($t->type) {
                    'FI' => 'Fund In (Project Funding)',
                    'FO' => 'Fund Out (MTSS Share)',
                    'T' => 'Transfer between Heads',
                    default => 'Transfer (' . $t->type . ')'
                };
                
                $fromOldVal = 0;
                $fromNewVal = 0;
                $toOldVal = 0;
                $toNewVal = 0;
                
                if ($t->trf_fromhed) {
                    $fromInflows = DB::table('fin.transfers')->where('trf_tohed', $t->trf_fromhed)->where('trf_id', '<=', $t->trf_id)->sum('trf_amount');
                    $fromOutflows = DB::table('fin.transfers')->where('trf_fromhed', $t->trf_fromhed)->where('trf_id', '<=', $t->trf_id)->sum('trf_amount');
                    $fromNewVal = $fromInflows - $fromOutflows;
                    $fromOldVal = $fromNewVal + $t->amount;
                }
                
                if ($t->trf_tohed) {
                    $toInflows = DB::table('fin.transfers')->where('trf_tohed', $t->trf_tohed)->where('trf_id', '<=', $t->trf_id)->sum('trf_amount');
                    $toOutflows = DB::table('fin.transfers')->where('trf_fromhed', $t->trf_tohed)->where('trf_id', '<=', $t->trf_id)->sum('trf_amount');
                    $toNewVal = $toInflows - $toOutflows;
                    $toOldVal = $toNewVal - $t->amount;
                }
                
                $results[] = [
                    'id' => $t->trf_id,
                    'date' => $t->date,
                    'title' => $t->title,
                    'amount' => (float)$t->amount,
                    'type_label' => $typeLabel,
                    'status' => $t->status ?: 'N/A',
                    'from_code' => $t->from_code ?: 'N/A',
                    'from_name' => $t->from_name ?: 'N/A',
                    'from_division' => $t->from_division ?: 'N/A',
                    'from_old_val' => (float)$fromOldVal,
                    'from_new_val' => (float)$fromNewVal,
                    'to_code' => $t->to_code ?: 'N/A',
                    'to_name' => $t->to_name ?: 'N/A',
                    'to_division' => $t->to_division ?: 'N/A',
                    'to_old_val' => (float)$toOldVal,
                    'to_new_val' => (float)$toNewVal
                ];
            }
            
            return $results;

        } elseif ($type === 'allocations_status') {
            $query = DB::table('fin.sharesalloc as sa')
                ->join('cen.heads as h', 'sa.sha_hed_id', '=', 'h.hed_id')
                ->leftJoin('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                ->select(
                    'h.hed_id',
                    'sa.sha_id as id',
                    'h.hed_code as head_code',
                    'h.hed_name as head_name',
                    'p.prj_code as project_code',
                    'sa.sha_cf as csrf_share',
                    'sa.sha_pcc as equipment_share',
                    'sa.sha_prj as rdw_share',
                    'sa.sha_prj_sal as hr_share',
                    'sa.sha_prj_pur as misc_share'
                );

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('h.hed_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            }

            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('p.prj_id', (int)$projectId);
            }

            if ($limit) {
                $query->limit($limit);
            }

            $heads = $query->orderBy('sa.sha_id', 'desc')->get();
            $results = [];
            foreach ($heads as $h) {
                // Total Allocation (Project Funding)
                $allocation = DB::table('fin.transfers')
                    ->where('trf_type', 'FI')
                    ->where('trf_title', 'Project Funding')
                    ->where('trf_tohed', $h->hed_id)
                    ->sum('trf_amount');
                
                // MTSS Share
                $mtss_share = DB::table('fin.transfers')
                    ->where('trf_type', 'FO')
                    ->where('trf_title', 'MTSS Share')
                    ->where('trf_fromhed', $h->hed_id)
                    ->sum('trf_amount');
                
                $results[] = [
                    'id' => $h->id,
                    'head_code' => $h->head_code,
                    'head_name' => $h->head_name,
                    'project_code' => $h->project_code ?: 'N/A',
                    'allocation' => (float)$allocation,
                    'mtss_share' => (float)$mtss_share,
                    'rdw_share' => (float)($h->rdw_share ?? 0),
                    'csrf_share' => (float)($h->csrf_share ?? 0),
                    'equipment_share' => (float)($h->equipment_share ?? 0),
                    'hr_share' => (float)($h->hr_share ?? 0),
                    'misc_share' => (float)($h->misc_share ?? 0)
                ];
            }
            return $results;

        } elseif ($type === 'accounts_status') {
            $query = DB::table('cen.heads as h')
                ->leftJoin('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                ->select(
                    'h.hed_id',
                    'h.hed_code',
                    'h.hed_name',
                    'p.prj_code'
                )
                ->where('h.hed_type', 'Project');

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('h.hed_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            }

            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('p.prj_id', (int)$projectId);
            }

            if ($limit) {
                $query->limit($limit);
            }

            $heads = $query->orderBy('h.hed_id', 'desc')->get();
            $results = [];
            foreach ($heads as $h) {
                $fin = $this->finService->getHeadStatus($h->hed_id);
                $results[] = [
                    'hed_code' => $h->hed_code,
                    'hed_name' => $h->hed_name,
                    'project_code' => $h->prj_code ?: 'N/A',
                    'allocation' => (float)($fin->allocation ?? 0),
                    'mtss_share' => (float)($fin->mtss_share ?? 0),
                    'received' => (float)($fin->acc_received ?? 0),
                    'commitments' => (float)($fin->acc_commitments ?? 0),
                    'expenditure' => (float)abs($fin->acc_expenditure ?? 0),
                    'cf_share' => (float)($fin->cf_share ?? 0),
                    'cf_expenditure' => (float)abs($fin->cf_expenditure ?? 0),
                    'balance' => (float)($fin->balance ?? 0),
                    'available' => (float)($fin->available ?? 0)
                ];
            }
            return $results;

        } elseif ($type === 'proj_shares_status') {
            $query = DB::table('fin.sharesalloc as sa')
                ->join('cen.heads as h', 'sa.sha_hed_id', '=', 'h.hed_id')
                ->join('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                ->select(
                    'p.prj_code',
                    'p.prj_title',
                    'p.prj_aprvcost',
                    'sa.sha_cf',
                    'sa.sha_pcc',
                    'sa.sha_prj',
                    'sa.sha_prj_sal',
                    'sa.sha_prj_pur'
                );

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('h.hed_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            }

            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('p.prj_id', (int)$projectId);
            }

            if ($limit) {
                $query->limit($limit);
            }

            return $query->orderBy('p.prj_id', 'desc')->get()->toArray();

        } elseif ($type === 'subheads_status') {
            $query = DB::table('fin.subheads as s')
                ->join('cen.heads as h', 's.sbh_hed_id', '=', 'h.hed_id')
                ->select(
                    's.sbh_id',
                    's.sbh_name',
                    's.sbh_alloc',
                    'h.hed_code as head_code',
                    'h.hed_name as head_name'
                );

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('h.hed_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            }

            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('h.hed_prj_id', (int)$projectId);
            }

            if ($limit) {
                $query->limit($limit);
            }

            return $query->orderBy('s.sbh_id', 'desc')->get()->toArray();

        } elseif ($type === 'csrf_status') {
            $query = DB::table('cen.heads as h')
                ->join('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                ->select(
                    'h.hed_id',
                    'h.hed_code',
                    'p.prj_code',
                    'p.prj_title'
                );

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('h.hed_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            }

            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('p.prj_id', (int)$projectId);
            }

            if ($limit) {
                $query->limit($limit);
            }

            $heads = $query->orderBy('h.hed_id', 'desc')->get();
            $results = [];
            foreach ($heads as $h) {
                $fin = $this->finService->getHeadStatus($h->hed_id);
                $results[] = [
                    'prj_code' => $h->prj_code,
                    'prj_title' => $h->prj_title,
                    'hed_code' => $h->hed_code,
                    'cf_share' => (float)($fin->cf_share ?? 0),
                    'cf_received' => (float)($fin->cf_received ?? 0),
                    'cf_expenditure' => (float)abs($fin->cf_expenditure ?? 0),
                    'cf_balance' => (float)($fin->cf_balance ?? 0),
                    'cf_commitments' => (float)($fin->cf_commitments ?? 0),
                    'cf_in_process' => (float)($fin->cf_in_process ?? 0),
                    'cf_available' => (float)($fin->cf_available ?? 0)
                ];
            }
            return $results;

        } elseif ($type === 'monthly_return') {
            $query = DB::table('fin.transactions as t')
                ->join('fin.commitments as c', 't.trn_cmt_id', '=', 'c.cmt_id')
                ->join('cen.heads as h', 'c.cmt_hed_id', '=', 'h.hed_id')
                ->select(
                    't.trn_id',
                    't.trn_date',
                    't.trn_amount1',
                    't.trn_amount2',
                    't.trn_tax1',
                    't.trn_balance',
                    't.trn_transtype',
                    'h.hed_code as head_code',
                    'h.hed_name as head_name'
                );

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('h.hed_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            }

            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('h.hed_prj_id', (int)$projectId);
            }

            if (!empty($startDate)) {
                $query->where('t.trn_date', '>=', $startDate);
            }
            if (!empty($endDate)) {
                $query->where('t.trn_date', '<=', $endDate);
            }

            if ($limit) {
                $query->limit($limit);
            }

            return $query->orderBy('t.trn_date', 'desc')->get()->toArray();

        } elseif ($type === 'pcs_awaiting_payment') {
            $query = DB::table('pur.purcases as pc')
                ->leftJoin('fin.commitments as c', function($join) {
                    $join->on('pc.pcs_id', '=', 'c.cmt_docid')
                         ->on('pc.pcs_type', '=', 'c.cmt_type');
                })
                ->leftJoin('cen.heads as h', 'c.cmt_effhed_id', '=', 'h.hed_id')
                ->leftJoin('frm.firmz as f', 'pc.pcs_frm_id', '=', 'f.frm_id')
                ->select(
                    'pc.pcs_id',
                    'pc.pcs_title',
                    'pc.pcs_date',
                    'pc.pcs_status',
                    'pc.pcs_price',
                    'pc.pcs_intprice',
                    'pc.pcs_midprice',
                    'h.hed_code as head_code',
                    'f.frm_name as frm_name'
                )
                ->where('c.cmt_status', 'Awaited');

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('pc.pcs_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('pc.pcs_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('pc.pcs_unt_id', (int)$unitId);
                }
            }

            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('pc.pcs_effhed_id', function($q) use ($projectId) {
                    $q->select('hed_id')->from('cen.heads')->where('hed_prj_id', (int)$projectId);
                });
            }

            if ($limit) {
                $query->limit($limit);
            }

            return $query->orderBy('pc.pcs_id', 'desc')->get()->toArray();

        } elseif ($type === 'current_employees') {
            $query = DB::table('hr.emps as e')
                ->leftJoin('cen.units as u', 'e.emp_unt_id', '=', 'u.unt_id')
                ->leftJoin('cen.heads as h', 'e.emp_hed_id', '=', 'h.hed_id')
                ->select(
                    'e.emp_id',
                    'e.emp_cnic',
                    'e.emp_name',
                    'e.emp_joindt',
                    'e.emp_locked',
                    'e.emp_rank',
                    'e.emp_status',
                    'e.emp_remarks',
                    'e.emp_lastdt',
                    'e.emp_title',
                    'e.emp_photodest',
                    'e.emp_cleared',
                    'u.unt_namesh as division',
                    'h.hed_code as head_code'
                )
                ->whereIn('e.emp_status', ['Active', 'active']);

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('e.emp_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('e.emp_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('e.emp_unt_id', (int)$unitId);
                }
            }

            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('e.emp_hed_id', function($q) use ($projectId) {
                    $q->select('hed_id')->from('cen.heads')->where('hed_prj_id', (int)$projectId);
                });
            }

            if ($limit) {
                $query->limit($limit);
            }

            return $query->orderBy('e.emp_id', 'desc')->get()->toArray();

        } elseif ($type === 'pcs_by_firms') {
            $query = DB::table('pur.purcases as pc')
                ->leftJoin('frm.firmz as f', 'pc.pcs_frm_id', '=', 'f.frm_id')
                ->select(
                    'pc.pcs_id',
                    'pc.pcs_title',
                    'pc.pcs_date',
                    'pc.pcs_price',
                    'f.frm_name',
                    'f.frm_type as frm_type',
                    'pc.pcs_status'
                );

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('pc.pcs_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('pc.pcs_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('pc.pcs_unt_id', (int)$unitId);
                }
            }

            if (!empty($status) && $status !== 'All') {
                $statuses = is_array($status) ? $status : explode(',', $status);
                $query->whereIn('pc.pcs_status', $statuses);
            }

            if ($limit) {
                $query->limit($limit);
            }

            return $query->orderBy('pc.pcs_id', 'desc')->get()->toArray();

        } elseif ($type === 'pcs_by_firms_projects') {
            $query = DB::table('pur.purcases as pc')
                ->leftJoin('frm.firmz as f', 'pc.pcs_frm_id', '=', 'f.frm_id')
                ->leftJoin('cen.heads as h', 'pc.pcs_effhed_id', '=', 'h.hed_id')
                ->leftJoin('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                ->select(
                    'pc.pcs_id',
                    'pc.pcs_title',
                    'pc.pcs_date',
                    'pc.pcs_price',
                    'f.frm_name',
                    'p.prj_code',
                    'p.prj_title',
                    'pc.pcs_status'
                );

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('pc.pcs_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('pc.pcs_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('pc.pcs_unt_id', (int)$unitId);
                }
            }

            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('p.prj_id', (int)$projectId);
            }

            if (!empty($status) && $status !== 'All') {
                $statuses = is_array($status) ? $status : explode(',', $status);
                $query->whereIn('pc.pcs_status', $statuses);
            }

            if ($limit) {
                $query->limit($limit);
            }

            return $query->orderBy('pc.pcs_id', 'desc')->get()->toArray();

        } elseif ($type === 'pcs_by_single_firm') {
            $query = DB::table('pur.purcases as pc')
                ->leftJoin('frm.firmz as f', 'pc.pcs_frm_id', '=', 'f.frm_id')
                ->select(
                    'pc.pcs_id',
                    'pc.pcs_title',
                    'pc.pcs_date',
                    'pc.pcs_price',
                    'f.frm_name',
                    'pc.pcs_status'
                );

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('pc.pcs_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('pc.pcs_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('pc.pcs_unt_id', (int)$unitId);
                }
            }

            if ($firmId !== 'All' && !empty($firmId)) {
                $query->where('pc.pcs_frm_id', (int)$firmId);
            }

            if (!empty($status) && $status !== 'All') {
                $statuses = is_array($status) ? $status : explode(',', $status);
                $query->whereIn('pc.pcs_status', $statuses);
            }

            if ($limit) {
                $query->limit($limit);
            }

            return $query->orderBy('pc.pcs_id', 'desc')->get()->toArray();

        } elseif ($type === 'attachment_summary') {
            $purQuery = DB::table('pur.purattachments as pa')
                ->leftJoin('pur.purcases as pc', function($join) {
                    $join->on('pa.pat_objid', '=', 'pc.pcs_id')
                         ->where('pa.pat_objtype', '=', 'pcs');
                })
                ->select(
                    DB::raw("'Purchase' as source_type"),
                    'pa.pat_id as id',
                    'pa.pat_objtype as obj_type',
                    DB::raw("CAST(pa.pat_objid as varchar) as obj_id"),
                    'pa.pat_type as file_type',
                    'pa.pat_path as file_path',
                    'pc.pcs_title as purchase_case'
                );

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $purQuery->where('pc.pcs_unt_id', (int)$unitId);
                }
            } else {
                $purQuery->whereBetween('pc.pcs_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $purQuery->where('pc.pcs_unt_id', (int)$unitId);
                }
            }

            $inaQuery = DB::table('ina.inaattachments as ia')
                ->select(
                    DB::raw("'Inventory/Asset' as source_type"),
                    'ia.iat_id as id',
                    'ia.iat_objtype as obj_type',
                    DB::raw("CAST(ia.iat_objid as varchar) as obj_id"),
                    'ia.iat_type as file_type',
                    'ia.iat_path as file_path',
                    DB::raw("NULL as purchase_case")
                );

            $hrQuery = DB::table('hr.empattachments as ea')
                ->select(
                    DB::raw("'Employee' as source_type"),
                    'ea.eat_id as id',
                    'ea.eat_objtype as obj_type',
                    'ea.eat_objid as obj_id',
                    'ea.eat_type as file_type',
                    'ea.eat_path as file_path',
                    DB::raw("NULL as purchase_case")
                );

            $unionQuery = $purQuery->unionAll($inaQuery)->unionAll($hrQuery);

            $results = DB::table(DB::raw("({$unionQuery->toSql()}) as att"))
                ->mergeBindings($unionQuery);

            if ($limit) {
                $results->limit($limit);
            }

            return $results->orderBy('id', 'desc')->get()->toArray();

        } elseif ($type === 'reversals_shifting_history') {
            $query = DB::table('cen.heads as h')
                ->leftJoin('cen.units as u', 'h.hed_unt_id', '=', 'u.unt_id')
                ->leftJoin('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                ->select(
                    'h.hed_id',
                    'h.hed_code',
                    'h.hed_name',
                    'h.hed_opendt',
                    'u.unt_namesh as division',
                    'p.prj_code as project_code'
                )
                ->where('h.hed_type', 'Project');

            if ($isHqUser) {
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            } else {
                $query->whereBetween('h.hed_unt_id', [$lower, $upper]);
                if ($unitId !== 'All') {
                    $query->where('h.hed_unt_id', (int)$unitId);
                }
            }

            if (!empty($projectId) && $projectId !== 'All') {
                $query->where('p.prj_id', (int)$projectId);
            }

            if ($limit) {
                $query->limit($limit);
            }

            $heads = $query->orderBy('h.hed_id', 'desc')->get();
            $results = [];
            
            foreach ($heads as $h) {
                // Count reversals in aud.revs
                $reversalsCount = DB::table('aud.revs')
                    ->where(function($q) use ($h) {
                        $q->where('rev_ref', $h->hed_code)
                          ->orWhere(function($sq) use ($h) {
                              $sq->where('rev_objid', $h->hed_id)
                                ->whereIn('rev_obj', ['Account', 'Allocation', 'Funding']);
                          });
                    })
                    ->count();

                // Count shifts (transfers)
                $shiftsCount = DB::table('fin.transfers')
                    ->where(function($q) use ($h) {
                        $q->where('trf_fromhed', $h->hed_id)
                          ->orWhere('trf_tohed', $h->hed_id);
                    })
                    ->count();

                // Get latest shift details
                $latestShift = DB::table('fin.transfers')
                    ->where(function($q) use ($h) {
                        $q->where('trf_fromhed', $h->hed_id)
                          ->orWhere('trf_tohed', $h->hed_id);
                    })
                    ->orderBy('trf_id', 'desc')
                    ->first();

                $lastShiftAmount = 0;
                $lastShiftDate = 'N/A';
                $balanceBefore = 0;
                $balanceAfter = 0;

                if ($latestShift) {
                    $lastShiftAmount = (float)$latestShift->trf_amount;
                    $lastShiftDate = $latestShift->trf_date;

                    // Calculate balance before and after for the head
                    $inflows = DB::table('fin.transfers')->where('trf_tohed', $h->hed_id)->where('trf_id', '<=', $latestShift->trf_id)->sum('trf_amount');
                    $outflows = DB::table('fin.transfers')->where('trf_fromhed', $h->hed_id)->where('trf_id', '<=', $latestShift->trf_id)->sum('trf_amount');
                    $balanceAfter = $inflows - $outflows;

                    // Was it inflow or outflow?
                    if ($latestShift->trf_tohed == $h->hed_id) {
                        $balanceBefore = $balanceAfter - $lastShiftAmount;
                    } else {
                        $balanceBefore = $balanceAfter + $lastShiftAmount;
                    }
                }

                $results[] = [
                    'head_code' => $h->hed_code,
                    'head_name' => $h->hed_name,
                    'project_code' => $h->project_code ?: 'N/A',
                    'division' => $h->division ?: 'N/A',
                    'hed_opendt' => $h->hed_opendt ?: 'N/A',
                    'reversals_count' => (int)$reversalsCount,
                    'shifts_count' => (int)$shiftsCount,
                    'last_shift_amount' => (float)$lastShiftAmount,
                    'last_shift_date' => $lastShiftDate,
                    'balance_before' => (float)$balanceBefore,
                    'balance_after' => (float)$balanceAfter
                ];
            }

            return $results;
        }
        
        return [];
    }
}
