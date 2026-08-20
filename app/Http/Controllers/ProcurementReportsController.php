<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\FinancialIntelligenceService;

class ProcurementReportsController extends Controller
{
    protected $finService;

    public function __construct(FinancialIntelligenceService $finService)
    {
        $this->finService = $finService;
    }

    /**
     * Display the Procurement & Inventory Reports Hub.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Fetch divisions/units for selection (All Divisions)
        $units = DB::table('cen.units')
            ->where('unt_type', 'Division')
            ->orderBy('unt_name')
            ->get(['unt_id', 'unt_name', 'unt_namesh']);

        // Fetch projects for selection
        $projects = DB::table('prj.projects')
            ->orderBy('prj_code')
            ->get(['prj_id', 'prj_code', 'prj_title', 'prj_unt_id']);

        // Fetch all firms for selection
        $firms = DB::table('frm.firmz')
            ->where('frm_id', '>', 0)
            ->where('frm_name', 'not like', '%< Select%')
            ->where('frm_name', 'not like', '%<Select%')
            ->orderBy('frm_name')
            ->get(['frm_id', 'frm_name']);

        // Custom columns available for Inventory & Assets Report
        $customAssetColumns = [
            'item_id'             => 'Item ID',
            'description'         => 'Description',
            'dept'                => 'Department / Division',
            'dept_details'        => 'Dept Details',
            'qty'                 => 'Quantity',
            'denomination'        => 'Denomination',
            'charge_qty'          => 'Charge Quantity',
            'charge_denomination' => 'Charge Denomination',
            'charge_date'         => 'Charge Date',
            'price'               => 'Price w/o Tax (Rs)',
            'asset_inventory'     => 'Asset / Inventory',
            'subtype'             => 'Subtype',
            'parent_item'         => 'Parent Item',
            'location'            => 'Location',
            'custodian_group'     => 'Custodian Group',
            'custodian_person'    => 'Custodian Person',
            'shared'              => 'Shared',
            'is_parent'           => 'Is Parent',
            'is_assembly'         => 'Is Assembly',
            'disposal_date'       => 'Disposal Date',
            'status'              => 'Status',
            'remarks'             => 'Remarks',
            'purchase_case_id'    => 'Purchase Case ID',
            'project'             => 'Project'
        ];

        return view('nrdi.procurement.reports', compact('units', 'projects', 'firms', 'customAssetColumns'));
    }

    /**
     * Retrieve live preview data for the selected report.
     */
    public function getReportData(Request $request)
    {
        try {
            $limit = $request->filled('limit') ? (int)$request->query('limit') : null;
            $data = $this->queryReport($request, $limit);

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => count($data)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export report data as Excel-compatible CSV file with UTF-8 BOM.
     */
    public function exportExcel(Request $request)
    {
        $type = $request->query('type', 'procurement_report');
        $fileName = 'procurement_' . $type . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $callback = function() use ($request, $type) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            $data = $this->queryReport($request, null);

            if (empty($data)) {
                fputcsv($file, ['No data found matching selected filters']);
                fclose($file);
                return;
            }

            // Headers map
            $allHeadersMap = $this->getHeadersMap($type);

            $selectedCols = $request->input('columns', []);
            if (is_string($selectedCols)) {
                $selectedCols = array_filter(explode(',', $selectedCols));
            }
            if (empty($selectedCols)) {
                $firstRow = (array)$data[0];
                $selectedCols = array_keys($firstRow);
            }

            // Build CSV column titles
            $csvHeaders = [];
            foreach ($selectedCols as $col) {
                $csvHeaders[] = $allHeadersMap[$col] ?? ucwords(str_replace('_', ' ', $col));
            }
            fputcsv($file, $csvHeaders);

            // Write data rows
            foreach ($data as $row) {
                $rowArr = (array)$row;
                $csvRow = [];
                foreach ($selectedCols as $col) {
                    $val = $rowArr[$col] ?? '';
                    
                    // Format monetary amounts if applicable
                    if (in_array($col, ['price', 'total_value', 'allocation', 'mtss_share', 'received', 'commitments', 'expenditure', 'cf_share', 'cf_expenditure', 'cf_received', 'cf_balance', 'cf_commitments', 'cf_in_process', 'cf_available', 'balance', 'available', 'pcs_price', 'pcs_intprice', 'pcs_midprice', 'sha_cf', 'sha_pcc', 'sha_prj', 'sha_prj_sal', 'sha_prj_pur', 'sbh_alloc', 'prj_aprvcost', 'total_awarded'], true)) {
                        if (is_numeric($val)) {
                            $val = 'Rs ' . number_format((float)$val, 2);
                        }
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
     * Map headers for all report types.
     */
    private function getHeadersMap($type): array
    {
        return [
            // Custom Inventory & Assets
            'item_id'             => 'Item ID',
            'description'         => 'Description',
            'dept'                => 'Department / Division',
            'dept_details'        => 'Dept Details',
            'qty'                 => 'Quantity',
            'denomination'        => 'Denomination',
            'charge_qty'          => 'Charge Quantity',
            'charge_denomination' => 'Charge Denomination',
            'charge_date'         => 'Charge Date',
            'price'               => 'Price w/o Tax (Rs)',
            'asset_inventory'     => 'Asset / Inventory',
            'subtype'             => 'Subtype',
            'parent_item'         => 'Parent Item',
            'location'            => 'Location',
            'custodian_group'     => 'Custodian Group',
            'custodian_person'    => 'Custodian Person',
            'shared'              => 'Shared',
            'is_parent'           => 'Is Parent',
            'is_assembly'         => 'Is Assembly',
            'disposal_date'       => 'Disposal Date',
            'status'              => 'Status',
            'remarks'             => 'Remarks',
            'purchase_case_id'    => 'Purchase Case ID',
            'project'             => 'Project',

            // Purchase cases
            'pcs_id'              => 'Case ID',
            'pcs_title'           => 'Case Title',
            'pcs_date'            => 'Case Date',
            'pcs_price'           => 'Case Amount (Rs)',
            'pcs_status'          => 'Case Status',
            'frm_name'            => 'Firm / Supplier',
            'frm_type'            => 'Firm Type',
            'division'            => 'Division / Unit',
            'prj_code'            => 'Project Code',
            'prj_title'           => 'Project Title',
            'pcs_intprice'        => 'Initiated Price (Rs)',
            'pcs_midprice'        => 'Mid Price (Rs)',

            // Financial & Status
            'hed_code'            => 'Budget Head Code',
            'hed_name'            => 'Budget Head Name',
            'project_code'        => 'Project Code',
            'allocation'          => 'Allocation (Rs)',
            'mtss_share'          => 'MTSS Share (Rs)',
            'rdw_share'           => 'RDW Share (Rs)',
            'csrf_share'          => 'CSRF Share (Rs)',
            'equipment_share'     => 'Equipment Share (Rs)',
            'hr_share'            => 'HR Share (Rs)',
            'misc_share'          => 'Misc Share (Rs)',
            'received'            => 'Received (Rs)',
            'commitments'         => 'Commitments (Rs)',
            'expenditure'         => 'Expenditure (Rs)',
            'balance'             => 'Balance (Rs)',
            'available'           => 'Available (Rs)',
            'cf_share'            => 'CSRF Share (Rs)',
            'cf_received'         => 'CSRF Received (Rs)',
            'cf_expenditure'      => 'CSRF Spent (Rs)',
            'cf_balance'          => 'CSRF Balance (Rs)',
            'cf_commitments'      => 'CSRF Commitments (Rs)',
            'cf_in_process'       => 'CSRF In Process (Rs)',
            'cf_available'        => 'CSRF Available (Rs)',
            'sha_cf'              => 'CSRF Share (Rs)',
            'sha_pcc'             => 'PCC Share (Rs)',
            'sha_prj'             => 'Project Share (Rs)',
            'sha_prj_sal'         => 'Salary Share (Rs)',
            'sha_prj_pur'         => 'Purchase Share (Rs)',
            'sbh_id'              => 'Subhead ID',
            'sbh_name'            => 'Subhead Name',
            'sbh_alloc'           => 'Subhead Allocation (Rs)',
            'head_code'           => 'Head Code',
            'head_name'           => 'Head Name'
        ];
    }

    /**
     * Core query builder handling all 10 report types.
     */
    private function queryReport(Request $request, $limit = null)
    {
        $type = $request->query('type', 'inventory_assets_custom');
        $category = $request->query('category', 'All');
        $status = $request->query('status', 'All');
        $unitId = $request->query('unit_id', 'All');
        $projectId = $request->query('project_id', 'All');
        $firmId = $request->query('firm_id', 'All');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search', '');

        // 1. INVENTORY & ASSETS CUSTOM REPORT
        if ($type === 'inventory_assets_custom' || $type === 'inventory_assets') {
            $query = DB::table('ina.invatcomps as c')
                ->join('ina.invats as a', 'c.iac_ias_id', '=', 'a.ias_id')
                ->leftJoin('pur.purcases as p', 'a.ias_pcs_id', '=', 'p.pcs_id')
                ->leftJoin('cen.units as u', 'a.ias_unt_id', '=', 'u.unt_id')
                ->leftJoin('cen.heads as h', 'a.ias_effhed_id', '=', 'h.hed_id')
                ->leftJoin('prj.projects as prj', 'h.hed_prj_id', '=', 'prj.prj_id')
                ->select(
                    'c.iac_id as item_id',
                    'a.ias_desc as description',
                    'u.unt_namesh as dept',
                    'a.ias_details as dept_details',
                    'c.iac_qty as qty',
                    'c.iac_qtyunit as denomination',
                    'a.ias_qty as charge_qty',
                    'a.ias_qtyunit as charge_denomination',
                    'a.ias_chargedate as charge_date',
                    'a.ias_price as price',
                    'a.ias_type',
                    'a.ias_subtype as subtype',
                    'c.iac_parent_id as parent_item',
                    'c.iac_location as location',
                    'c.iac_group as custodian_group',
                    'c.iac_person as custodian_person',
                    'c.iac_shared as shared',
                    'c.iac_isparent as is_parent',
                    'c.iac_isassembly as is_assembly',
                    'c.iac_dispdate as disposal_date',
                    'c.iac_status as status',
                    'c.iac_remarks as remarks',
                    'a.ias_pcs_id as purchase_case_id',
                    'prj.prj_code as project_code',
                    'prj.prj_title as project_title'
                );

            // Category filter
            if ($category === 'Assets' || $category === 'Asset') {
                $query->where('a.ias_type', 7);
            } elseif ($category === 'Inventory') {
                $query->where('a.ias_type', '!=', 7);
            }

            // Status filter
            if ($status === 'On Charge' || $status === 'OnCharge') {
                $query->whereIn('c.iac_status', ['Untagged', 'Tagged', 'Held']);
            } elseif ($status === 'Charged Off' || $status === 'ChargedOff' || $status === 'OffCharge') {
                $query->whereIn('c.iac_status', ['Issued to User', 'Installed', 'Consumed', 'Written Off']);
            } elseif ($status !== 'All' && !empty($status)) {
                $query->where('c.iac_status', $status);
            }

            // Division filter
            if ($unitId !== 'All' && !empty($unitId)) {
                $query->where('a.ias_unt_id', (int)$unitId);
            }

            // Project filter
            if ($projectId !== 'All' && !empty($projectId)) {
                $query->where('prj.prj_id', (int)$projectId);
            }

            // Date Range
            if (!empty($startDate)) {
                $query->where('a.ias_chargedate', '>=', $startDate);
            }
            if (!empty($endDate)) {
                $query->where('a.ias_chargedate', '<=', $endDate);
            }

            // Search Keyword
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('a.ias_desc', 'ILIKE', "%{$search}%")
                      ->orWhere('c.iac_person', 'ILIKE', "%{$search}%")
                      ->orWhere('c.iac_location', 'ILIKE', "%{$search}%")
                      ->orWhere('c.iac_remarks', 'ILIKE', "%{$search}%")
                      ->orWhere('a.ias_details', 'ILIKE', "%{$search}%");
                });
            }

            if ($limit) $query->limit($limit);

            $records = $query->orderBy('c.iac_id', 'desc')->get();

            return $records->map(function($r) {
                return [
                    'item_id'             => (int)$r->item_id,
                    'description'         => $r->description ?: 'N/A',
                    'dept'                => $r->dept ?: 'General',
                    'dept_details'        => $r->dept_details ?: 'N/A',
                    'qty'                 => (float)$r->qty,
                    'denomination'        => $r->denomination ?: 'Nos',
                    'charge_qty'          => (float)$r->charge_qty,
                    'charge_denomination' => $r->charge_denomination ?: 'Nos',
                    'charge_date'         => $r->charge_date ?: 'N/A',
                    'price'               => (float)$r->price,
                    'asset_inventory'     => (int)$r->ias_type === 7 ? 'Asset' : 'Inventory',
                    'subtype'             => $r->subtype ?: 'General',
                    'parent_item'         => $r->parent_item ? '#' . $r->parent_item : 'None',
                    'location'            => $r->location ?: 'Main Store',
                    'custodian_group'     => $r->custodian_group ?: 'N/A',
                    'custodian_person'    => $r->custodian_person ?: 'Store Custody',
                    'shared'              => $r->shared ? 'Yes' : 'No',
                    'is_parent'           => $r->is_parent ? 'Yes' : 'No',
                    'is_assembly'         => $r->is_assembly ? 'Yes' : 'No',
                    'disposal_date'       => $r->disposal_date ?: 'N/A',
                    'status'              => $r->status ?: 'Untagged',
                    'remarks'             => $r->remarks ?: 'N/A',
                    'purchase_case_id'    => $r->purchase_case_id ? '#' . $r->purchase_case_id : 'Direct Entry',
                    'project'             => $r->project_code ? $r->project_code . ' - ' . $r->project_title : 'General HQ'
                ];
            })->all();

        // 2. PURCHASE CASES BY FIRMS
        } elseif ($type === 'pcs_by_firms') {
            $query = DB::table('pur.purcases as pc')
                ->leftJoin('frm.firmz as f', 'pc.pcs_frm_id', '=', 'f.frm_id')
                ->leftJoin('cen.units as u', 'pc.pcs_unt_id', '=', 'u.unt_id')
                ->select(
                    'pc.pcs_id',
                    'pc.pcs_title',
                    'pc.pcs_date',
                    'pc.pcs_price',
                    'f.frm_name',
                    'f.frm_type',
                    'pc.pcs_status',
                    'u.unt_namesh as division'
                );

            if ($unitId !== 'All' && !empty($unitId)) $query->where('pc.pcs_unt_id', (int)$unitId);
            if ($status !== 'All' && !empty($status)) $query->where('pc.pcs_status', $status);
            if (!empty($startDate)) $query->where('pc.pcs_date', '>=', $startDate);
            if (!empty($endDate)) $query->where('pc.pcs_date', '<=', $endDate);

            if ($limit) $query->limit($limit);

            return $query->orderBy('pc.pcs_id', 'desc')->get()->map(function($r) {
                return [
                    'pcs_id'     => '#' . $r->pcs_id,
                    'pcs_title'  => $r->pcs_title,
                    'pcs_date'   => $r->pcs_date ?: 'N/A',
                    'pcs_price'  => (float)$r->pcs_price,
                    'frm_name'   => $r->frm_name ?: 'N/A',
                    'frm_type'   => $r->frm_type ?: 'General',
                    'pcs_status' => $r->pcs_status,
                    'division'   => $r->division ?: 'HQ'
                ];
            })->all();

        // 3. PURCHASE CASES BY FIRMS AND PROJECTS
        } elseif ($type === 'pcs_by_firms_projects') {
            $query = DB::table('pur.purcases as pc')
                ->leftJoin('frm.firmz as f', 'pc.pcs_frm_id', '=', 'f.frm_id')
                ->leftJoin('cen.heads as h', 'pc.pcs_effhed_id', '=', 'h.hed_id')
                ->leftJoin('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                ->leftJoin('cen.units as u', 'pc.pcs_unt_id', '=', 'u.unt_id')
                ->select(
                    'pc.pcs_id',
                    'pc.pcs_title',
                    'pc.pcs_date',
                    'pc.pcs_price',
                    'f.frm_name',
                    'p.prj_code',
                    'p.prj_title',
                    'pc.pcs_status',
                    'u.unt_namesh as division'
                );

            if ($unitId !== 'All' && !empty($unitId)) $query->where('pc.pcs_unt_id', (int)$unitId);
            if ($projectId !== 'All' && !empty($projectId)) $query->where('p.prj_id', (int)$projectId);
            if ($status !== 'All' && !empty($status)) $query->where('pc.pcs_status', $status);
            if (!empty($startDate)) $query->where('pc.pcs_date', '>=', $startDate);
            if (!empty($endDate)) $query->where('pc.pcs_date', '<=', $endDate);

            if ($limit) $query->limit($limit);

            return $query->orderBy('pc.pcs_id', 'desc')->get()->map(function($r) {
                return [
                    'pcs_id'     => '#' . $r->pcs_id,
                    'pcs_title'  => $r->pcs_title,
                    'pcs_date'   => $r->pcs_date ?: 'N/A',
                    'pcs_price'  => (float)$r->pcs_price,
                    'frm_name'   => $r->frm_name ?: 'N/A',
                    'prj_code'   => $r->prj_code ?: 'N/A',
                    'prj_title'  => $r->prj_title ?: 'N/A',
                    'pcs_status' => $r->pcs_status,
                    'division'   => $r->division ?: 'HQ'
                ];
            })->all();

        // 4. PURCHASE CASES BY SINGLE FIRM
        } elseif ($type === 'pcs_by_single_firm') {
            $query = DB::table('pur.purcases as pc')
                ->leftJoin('frm.firmz as f', 'pc.pcs_frm_id', '=', 'f.frm_id')
                ->leftJoin('cen.units as u', 'pc.pcs_unt_id', '=', 'u.unt_id')
                ->select(
                    'pc.pcs_id',
                    'pc.pcs_title',
                    'pc.pcs_date',
                    'pc.pcs_price',
                    'f.frm_name',
                    'pc.pcs_status',
                    'u.unt_namesh as division'
                );

            if ($firmId !== 'All' && !empty($firmId)) {
                $query->where('pc.pcs_frm_id', (int)$firmId);
            }
            if ($unitId !== 'All' && !empty($unitId)) $query->where('pc.pcs_unt_id', (int)$unitId);
            if ($status !== 'All' && !empty($status)) $query->where('pc.pcs_status', $status);
            if (!empty($startDate)) $query->where('pc.pcs_date', '>=', $startDate);
            if (!empty($endDate)) $query->where('pc.pcs_date', '<=', $endDate);

            if ($limit) $query->limit($limit);

            return $query->orderBy('pc.pcs_id', 'desc')->get()->map(function($r) {
                return [
                    'pcs_id'     => '#' . $r->pcs_id,
                    'pcs_title'  => $r->pcs_title,
                    'pcs_date'   => $r->pcs_date ?: 'N/A',
                    'pcs_price'  => (float)$r->pcs_price,
                    'frm_name'   => $r->frm_name ?: 'N/A',
                    'pcs_status' => $r->pcs_status,
                    'division'   => $r->division ?: 'HQ'
                ];
            })->all();

        // 5. ALLOCATION STATUS
        } elseif ($type === 'allocations_status') {
            $query = DB::table('fin.sharesalloc as sa')
                ->join('cen.heads as h', 'sa.sha_hed_id', '=', 'h.hed_id')
                ->leftJoin('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                ->select(
                    'h.hed_id',
                    'h.hed_code',
                    'h.hed_name',
                    'p.prj_code as project_code',
                    'sa.sha_cf as csrf_share',
                    'sa.sha_pcc as equipment_share',
                    'sa.sha_prj as rdw_share',
                    'sa.sha_prj_sal as hr_share',
                    'sa.sha_prj_pur as misc_share'
                );

            if ($unitId !== 'All' && !empty($unitId)) $query->where('h.hed_unt_id', (int)$unitId);
            if ($projectId !== 'All' && !empty($projectId)) $query->where('p.prj_id', (int)$projectId);

            if ($limit) $query->limit($limit);

            $heads = $query->orderBy('h.hed_id', 'desc')->get();
            $results = [];
            foreach ($heads as $h) {
                $allocation = DB::table('fin.transfers')
                    ->where('trf_type', 'FI')
                    ->where('trf_title', 'Project Funding')
                    ->where('trf_tohed', $h->hed_id)
                    ->sum('trf_amount');

                $mtss_share = DB::table('fin.transfers')
                    ->where('trf_type', 'FO')
                    ->where('trf_title', 'MTSS Share')
                    ->where('trf_fromhed', $h->hed_id)
                    ->sum('trf_amount');

                $results[] = [
                    'hed_code'        => $h->hed_code,
                    'hed_name'        => $h->hed_name,
                    'project_code'    => $h->project_code ?: 'N/A',
                    'allocation'      => (float)$allocation,
                    'mtss_share'      => (float)$mtss_share,
                    'rdw_share'       => (float)($h->rdw_share ?? 0),
                    'csrf_share'      => (float)($h->csrf_share ?? 0),
                    'equipment_share' => (float)($h->equipment_share ?? 0),
                    'hr_share'        => (float)($h->hr_share ?? 0),
                    'misc_share'      => (float)($h->misc_share ?? 0)
                ];
            }
            return $results;

        // 6. ACCOUNT STATUS
        } elseif ($type === 'accounts_status') {
            $query = DB::table('cen.heads as h')
                ->leftJoin('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                ->select('h.hed_id', 'h.hed_code', 'h.hed_name', 'p.prj_code')
                ->where('h.hed_type', 'Project');

            if ($unitId !== 'All' && !empty($unitId)) $query->where('h.hed_unt_id', (int)$unitId);
            if ($projectId !== 'All' && !empty($projectId)) $query->where('p.prj_id', (int)$projectId);

            if ($limit) $query->limit($limit);

            $heads = $query->orderBy('h.hed_id', 'desc')->get();
            $results = [];
            foreach ($heads as $h) {
                $fin = $this->finService->getHeadStatus($h->hed_id);
                $results[] = [
                    'hed_code'       => $h->hed_code,
                    'hed_name'       => $h->hed_name,
                    'project_code'   => $h->prj_code ?: 'N/A',
                    'allocation'     => (float)($fin->allocation ?? 0),
                    'mtss_share'     => (float)($fin->mtss_share ?? 0),
                    'received'       => (float)($fin->acc_received ?? 0),
                    'commitments'    => (float)($fin->acc_commitments ?? 0),
                    'expenditure'    => (float)abs($fin->acc_expenditure ?? 0),
                    'cf_share'       => (float)($fin->cf_share ?? 0),
                    'cf_expenditure' => (float)abs($fin->cf_expenditure ?? 0),
                    'balance'        => (float)($fin->balance ?? 0),
                    'available'      => (float)($fin->available ?? 0)
                ];
            }
            return $results;

        // 7. PROJECT SHARES STATUS
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

            if ($unitId !== 'All' && !empty($unitId)) $query->where('h.hed_unt_id', (int)$unitId);
            if ($projectId !== 'All' && !empty($projectId)) $query->where('p.prj_id', (int)$projectId);

            if ($limit) $query->limit($limit);

            return $query->orderBy('p.prj_id', 'desc')->get()->map(function($r) {
                return [
                    'prj_code'     => $r->prj_code,
                    'prj_title'    => $r->prj_title,
                    'prj_aprvcost' => (float)$r->prj_aprvcost,
                    'sha_cf'       => (float)$r->sha_cf,
                    'sha_pcc'      => (float)$r->sha_pcc,
                    'sha_prj'      => (float)$r->sha_prj,
                    'sha_prj_sal'  => (float)$r->sha_prj_sal,
                    'sha_prj_pur'  => (float)$r->sha_prj_pur
                ];
            })->all();

        // 8. SUBHEADS STATUS
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

            if ($unitId !== 'All' && !empty($unitId)) $query->where('h.hed_unt_id', (int)$unitId);
            if ($projectId !== 'All' && !empty($projectId)) $query->where('h.hed_prj_id', (int)$projectId);

            if ($limit) $query->limit($limit);

            return $query->orderBy('s.sbh_id', 'desc')->get()->map(function($r) {
                return [
                    'sbh_id'    => $r->sbh_id,
                    'sbh_name'  => $r->sbh_name,
                    'sbh_alloc' => (float)$r->sbh_alloc,
                    'head_code' => $r->head_code,
                    'head_name' => $r->head_name
                ];
            })->all();

        // 9. CSRF STATUS
        } elseif ($type === 'csrf_status') {
            $query = DB::table('cen.heads as h')
                ->join('prj.projects as p', 'h.hed_prj_id', '=', 'p.prj_id')
                ->select('h.hed_id', 'h.hed_code', 'p.prj_code', 'p.prj_title');

            if ($unitId !== 'All' && !empty($unitId)) $query->where('h.hed_unt_id', (int)$unitId);
            if ($projectId !== 'All' && !empty($projectId)) $query->where('p.prj_id', (int)$projectId);

            if ($limit) $query->limit($limit);

            $heads = $query->orderBy('h.hed_id', 'desc')->get();
            $results = [];
            foreach ($heads as $h) {
                $fin = $this->finService->getHeadStatus($h->hed_id);
                $results[] = [
                    'prj_code'       => $h->prj_code,
                    'prj_title'      => $h->prj_title,
                    'hed_code'       => $h->hed_code,
                    'cf_share'       => (float)($fin->cf_share ?? 0),
                    'cf_received'    => (float)($fin->cf_received ?? 0),
                    'cf_expenditure' => (float)abs($fin->cf_expenditure ?? 0),
                    'cf_balance'     => (float)($fin->cf_balance ?? 0),
                    'cf_commitments' => (float)($fin->cf_commitments ?? 0),
                    'cf_in_process'  => (float)($fin->cf_in_process ?? 0),
                    'cf_available'   => (float)($fin->cf_available ?? 0)
                ];
            }
            return $results;

        // 10. CASES WITHOUT ITEMS REPORT
        } elseif ($type === 'cases_without_items') {
            $query = DB::table('pur.purcases as pc')
                ->leftJoin('frm.firmz as f', 'pc.pcs_frm_id', '=', 'f.frm_id')
                ->leftJoin('cen.units as u', 'pc.pcs_unt_id', '=', 'u.unt_id')
                ->whereNotExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('pur.purcaseitems as pci')
                        ->whereColumn('pci.pci_pcs_id', 'pc.pcs_id');
                })
                ->select(
                    'pc.pcs_id',
                    'pc.pcs_title',
                    'pc.pcs_date',
                    'pc.pcs_status',
                    'pc.pcs_price',
                    'pc.pcs_intprice',
                    'pc.pcs_midprice',
                    'u.unt_namesh as division',
                    'f.frm_name as firm_name'
                );

            if ($unitId !== 'All' && !empty($unitId)) $query->where('pc.pcs_unt_id', (int)$unitId);
            if ($status !== 'All' && !empty($status)) $query->where('pc.pcs_status', $status);
            if (!empty($startDate)) $query->where('pc.pcs_date', '>=', $startDate);
            if (!empty($endDate)) $query->where('pc.pcs_date', '<=', $endDate);

            if ($limit) $query->limit($limit);

            return $query->orderBy('pc.pcs_id', 'desc')->get()->map(function($r) {
                return [
                    'pcs_id'       => '#' . $r->pcs_id,
                    'pcs_title'    => $r->pcs_title,
                    'pcs_date'     => $r->pcs_date ?: 'N/A',
                    'pcs_status'   => $r->pcs_status,
                    'pcs_price'    => (float)$r->pcs_price,
                    'pcs_intprice' => (float)$r->pcs_intprice,
                    'pcs_midprice' => (float)$r->pcs_midprice,
                    'division'     => $r->division ?: 'HQ',
                    'firm_name'    => $r->firm_name ?: 'None Assigned'
                ];
            })->all();
        }

        return [];
    }
}
