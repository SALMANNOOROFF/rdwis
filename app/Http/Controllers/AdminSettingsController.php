<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\PurchaseApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSettingsController extends Controller
{
    protected PurchaseApprovalService $approvalService;

    public function __construct(PurchaseApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Check administrative authorization
     */
    protected function checkAdminAuthorization(): void
    {
        $user = Auth::user();
        $isSuperAdmin = ($user?->acc_username === 'superadminrdw') || session('impersonated_by_god');

        if (!$isSuperAdmin && !in_array(strtolower(trim($user?->acc_untarea ?? '')), ['admin', 'it', 'nrdi'])) {
            abort(403, 'Unauthorized access to RDWIS System Settings.');
        }
    }

    /**
     * PAGE 1: Financial & HR Authority Limits Matrix
     */
    public function financialSettings()
    {
        $this->checkAdminAuthorization();

        // Purchase Financial Limits
        $mdThreshold = SystemSetting::get('pur_md_threshold', '400000');
        $ddgThreshold = SystemSetting::get('pur_ddg_threshold', '1000000');
        $thresholdBasis = SystemSetting::get('pur_threshold_basis', 'without_gst');
        $listAmountBasis = SystemSetting::get('pur_list_amount_basis', 'without_gst');

        // HR Hiring & Contract Authority Limits
        $hrMdGrade = SystemSetting::get('hr_md_grade', 'SPS-7');
        $hrMdSalary = SystemSetting::get('hr_md_salary_limit', '150000');
        $hrDdgGrade = SystemSetting::get('hr_ddg_grade', 'SPS-8');
        $hrDdgSalary = SystemSetting::get('hr_ddg_salary_limit', '300000');

        $gradesList = [
            'SPS-1' => 'SPS-01 / BPS-01 to 04 (Support Staff)',
            'SPS-2' => 'SPS-02 / BPS-05 to 07 (Junior Staff)',
            'SPS-3' => 'SPS-03 / BPS-08 to 10 (Technical Assistant)',
            'SPS-4' => 'SPS-04 / BPS-11 to 13 (Senior Technician)',
            'SPS-5' => 'SPS-05 / BPS-14 to 15 (Assistant / Supervisor)',
            'SPS-6' => 'SPS-06 / BPS-16 (Junior Officer / Engineer)',
            'SPS-7' => 'SPS-07 / BPS-17 (Research Officer / Ast. Manager)',
            'SPS-8' => 'SPS-08 / BPS-18 (Senior RO / Deputy Manager)',
            'SPS-9' => 'SPS-09 / BPS-19 (Principal RO / Manager)',
            'SPS-10' => 'SPS-10 / BPS-20 (Chief RO / Director)',
        ];

        return view('admin.financial_settings', compact(
            'mdThreshold',
            'ddgThreshold',
            'thresholdBasis',
            'listAmountBasis',
            'hrMdGrade',
            'hrMdSalary',
            'hrDdgGrade',
            'hrDdgSalary',
            'gradesList'
        ));
    }

    /**
     * Update Financial & HR Authority Limits
     */
    public function updateFinancialSettings(Request $request)
    {
        $this->checkAdminAuthorization();

        $request->validate([
            'pur_md_threshold' => 'required|numeric|min:0',
            'pur_ddg_threshold' => 'required|numeric|min:0',
            'pur_threshold_basis' => 'required|in:without_gst,with_gst',
            'pur_list_amount_basis' => 'required|in:without_gst,with_gst',
            'hr_md_grade' => 'required|string',
            'hr_md_salary_limit' => 'required|numeric|min:0',
            'hr_ddg_grade' => 'required|string',
            'hr_ddg_salary_limit' => 'required|numeric|min:0',
        ]);

        // Purchase Limits
        SystemSetting::set('pur_md_threshold', $request->pur_md_threshold, 'MD (R&D) Financial Approval Limit (in PKR)');
        SystemSetting::set('pur_ddg_threshold', $request->pur_ddg_threshold, 'DDG Financial Approval Limit (in PKR)');
        SystemSetting::set('pur_threshold_basis', $request->pur_threshold_basis, 'Approval Limit Evaluation Basis (without_gst or with_gst)');
        SystemSetting::set('pur_list_amount_basis', $request->pur_list_amount_basis, 'Hub Table Case Amount Display Basis (without_gst or with_gst)');

        // HR Limits
        SystemSetting::set('hr_md_grade', $request->hr_md_grade, 'MD (R&D) Max Hiring Grade Authority');
        SystemSetting::set('hr_md_salary_limit', $request->hr_md_salary_limit, 'MD (R&D) Max Monthly Salary Approval Limit');
        SystemSetting::set('hr_ddg_grade', $request->hr_ddg_grade, 'DDG Max Hiring Grade Authority');
        SystemSetting::set('hr_ddg_salary_limit', $request->hr_ddg_salary_limit, 'DDG Max Monthly Salary Approval Limit');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Financial Authority Limits & HR Hiring Limits saved successfully in real-time!'
            ]);
        }

        return redirect()->back()->with('success', 'Financial Authority & HR Hiring Limits updated successfully!');
    }

    /**
     * PAGE 2: Dynamic Workflow: Purchase Cases
     */
    public function workflowSettings()
    {
        $this->checkAdminAuthorization();

        // Load workflow matrices specifically for the 3 actual case types
        $psMatrix = $this->approvalService->getWorkflowMatrix('PS');
        $ptMatrix = $this->approvalService->getWorkflowMatrix('PT');
        $rbMatrix = $this->approvalService->getWorkflowMatrix('RB');

        $stageOptions = [
            'Division' => 'Division (Initiating Unit)',
            'DProc'    => 'Director Procurement',
            'DFinance' => 'Director Finance',
            'MD'       => 'MD (R&D) Office',
            'DDG'      => 'DDG Office',
            'DG'       => 'Director General (NRDI)',
            'Approved' => 'Final Approval (Terminal)',
        ];

        // Master List of all Purchase Case Categories
        $availableCaseTypes = [
            'mat'   => ['label' => 'Material / Goods Procurement', 'icon' => 'fas fa-boxes', 'desc' => 'Physical goods, raw materials, equipment & store items'],
            'lic'   => ['label' => 'IT Software & Hardware Licenses', 'icon' => 'fas fa-laptop-code', 'desc' => 'IT equipment, network hardware, software licenses'],
            'stat'  => ['label' => 'Stationery & Office Supplies', 'icon' => 'fas fa-pencil-ruler', 'desc' => 'Printing papers, pens, daily office consumables'],
            'book'  => ['label' => 'Books & Publications', 'icon' => 'fas fa-book', 'desc' => 'Research journals, library books, scientific publications'],
            'cons'  => ['label' => 'Consultancy & Advisory', 'icon' => 'fas fa-user-tie', 'desc' => 'Expert consultants, technical advisory services'],
            'serv'  => ['label' => 'General & Technical Services', 'icon' => 'fas fa-tools', 'desc' => 'Outsourced services, equipment repair & maintenance'],
            'civ'   => ['label' => 'Civil & Electrical Maintenance', 'icon' => 'fas fa-building', 'desc' => 'Civil repairs, office infrastructure maintenance'],
            'tran'  => ['label' => 'Transport & Logistics', 'icon' => 'fas fa-truck', 'desc' => 'Fuel, vehicle hire, transport & shipping costs'],
            'tada'  => ['label' => 'TA / DA & Travel Claims', 'icon' => 'fas fa-plane-departure', 'desc' => 'Official travel expenses, daily allowances'],
            'net'   => ['label' => 'Internet & Communications', 'icon' => 'fas fa-wifi', 'desc' => 'Bandwidth charges, ISP recurring subscriptions'],
            'trn'   => ['label' => 'Training & Capacity Building', 'icon' => 'fas fa-graduation-cap', 'desc' => 'Workshops, employee training, seminars'],
        ];

        return view('admin.workflow_settings', compact(
            'psMatrix',
            'ptMatrix',
            'rbMatrix',
            'stageOptions',
            'availableCaseTypes'
        ));
    }

    /**
     * Update Dynamic Workflow: Purchase Cases
     */
    public function updateWorkflowSettings(Request $request)
    {
        $this->checkAdminAuthorization();

        if ($request->has('workflows') && is_array($request->workflows)) {
            $workflowMatrix = [];
            foreach (['PS', 'PT', 'RB'] as $typeKey) {
                if (isset($request->workflows[$typeKey])) {
                    $rawType = $request->workflows[$typeKey];
                    
                    // Assigned sub-types
                    $assignedTypes = (isset($rawType['assigned_types']) && is_array($rawType['assigned_types'])) 
                        ? array_values($rawType['assigned_types']) 
                        : (($typeKey === 'PS') ? ['mat', 'lic', 'stat', 'book', 'cons', 'serv'] : (($typeKey === 'PT') ? ['stat', 'tran', 'tada', 'mat'] : ['civ', 'serv', 'net', 'lic']));

                    $workflowMatrix[$typeKey] = [
                        'assigned_types' => $assignedTypes,
                        'forward_chain' => [
                            'Division' => ['next' => $rawType['forward']['Division'] ?? ($typeKey === 'PS' ? 'DProc' : 'DFinance')],
                            'DProc'    => ['next' => $rawType['forward']['DProc'] ?? 'Division'],
                            'DFinance' => ['next' => $rawType['forward']['DFinance'] ?? 'MD'],
                            'MD'       => ['next' => $rawType['forward']['MD'] ?? 'DDG'],
                            'DDG'      => ['next' => $rawType['forward']['DDG'] ?? 'DG'],
                            'DG'       => ['next' => 'Approved'],
                        ],
                        'return_chain' => [
                            'DProc'    => $rawType['return']['DProc'] ?? 'Division',
                            'DFinance' => $rawType['return']['DFinance'] ?? 'Division',
                            'MD'       => $rawType['return']['MD'] ?? 'DFinance',
                            'DDG'      => $rawType['return']['DDG'] ?? 'MD',
                            'DG'       => $rawType['return']['DG'] ?? 'DDG',
                        ],
                        'return_policy' => $rawType['return_policy'] ?? 'historical',
                    ];
                }
            }
            // Set DEFAULT to match PS flow as universal fallback
            $workflowMatrix['DEFAULT'] = $workflowMatrix['PS'] ?? $workflowMatrix['PT'] ?? [];

            SystemSetting::set('pur_workflow_matrix', json_encode($workflowMatrix), 'Dynamic Workflow Matrix for Purchase Cases');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase Cases Workflow Routes saved successfully in real-time!'
            ]);
        }

        return redirect()->back()->with('success', 'Purchase Cases Workflow Routes saved successfully!');
    }

    /**
     * PAGE 3: Dynamic Workflow: Monthly Progress Reports (MPR)
     */
    public function mprWorkflowSettings()
    {
        $this->checkAdminAuthorization();

        $rawMatrix = SystemSetting::get('mpr_workflow_matrix', null);
        $defaults = [
            'forward_chain' => [
                'ProjectOfficer' => ['next' => 'SORD'],
                'SORD'           => ['next' => 'Accepted'], // Default current: finalized by SO R&D (or forward to MD)
                'MD'             => ['next' => 'Accepted'],
            ],
            'return_chain' => [
                'SORD' => 'ProjectOfficer',
                'MD'   => 'SORD',
            ],
            'return_policy' => 'historical',
        ];

        $matrix = $rawMatrix ? (is_string($rawMatrix) ? json_decode($rawMatrix, true) : $rawMatrix) : $defaults;

        $mprStages = [
            'ProjectOfficer' => 'Project Investigator (PI / Division Initiator)',
            'SORD'           => 'Staff Officer R&D (SO R&D - Coordinator)',
            'MD'             => 'Managing Director (MD R&D Office - Executive)',
            'Accepted'       => 'Accepted & Finalized (Terminal Archive)',
        ];

        return view('admin.mpr_workflow_settings', compact('matrix', 'mprStages'));
    }

    /**
     * Update Dynamic Workflow: Monthly Progress Reports (MPR)
     */
    public function updateMprWorkflowSettings(Request $request)
    {
        $this->checkAdminAuthorization();

        if ($request->has('forward') && is_array($request->forward)) {
            $mprMatrix = [
                'forward_chain' => [
                    'ProjectOfficer' => ['next' => $request->forward['ProjectOfficer'] ?? 'SORD'],
                    'SORD'           => ['next' => $request->forward['SORD'] ?? 'Accepted'],
                    'MD'             => ['next' => $request->forward['MD'] ?? 'Accepted'],
                ],
                'return_chain' => [
                    'SORD' => $request->return['SORD'] ?? 'ProjectOfficer',
                    'MD'   => $request->return['MD'] ?? 'SORD',
                ],
                'return_policy' => $request->return_policy ?? 'historical',
            ];
            SystemSetting::set('mpr_workflow_matrix', json_encode($mprMatrix), 'Dynamic Workflow Matrix for MPR Reports');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'MPR Workflow Routes & Return Pathways saved successfully in real-time!'
            ]);
        }

        return redirect()->back()->with('success', 'MPR Workflow Routes saved successfully!');
    }

    /**
     * PAGE 4: Dynamic Workflow: Hiring & Contract Cases
     */
    public function hrWorkflowSettings()
    {
        $this->checkAdminAuthorization();

        $rawMatrix = SystemSetting::get('hr_workflow_matrix', null);
        $defaults = [
            'forward_chain' => [
                'Division' => ['next' => 'HRDirectorate'],
                'HRDirectorate' => ['next' => 'DFinance'],
                'DFinance' => ['next' => 'MD'],
                'MD'       => ['next' => 'DDG'],
                'DDG'      => ['next' => 'DG'],
                'DG'       => ['next' => 'Approved'],
            ],
            'return_chain' => [
                'HRDirectorate' => 'Division',
                'DFinance' => 'HRDirectorate',
                'MD'       => 'DFinance',
                'DDG'      => 'MD',
                'DG'       => 'DDG',
            ],
            'return_policy' => 'historical',
        ];

        $matrix = $rawMatrix ? (is_string($rawMatrix) ? json_decode($rawMatrix, true) : $rawMatrix) : $defaults;

        $hrStages = [
            'Division'      => 'Division (Demand Initiator)',
            'HRDirectorate' => 'Director HR / Scrutiny',
            'DFinance'      => 'Director Finance (Budget Sanction)',
            'MD'            => 'MD (R&D) Office',
            'DDG'           => 'DDG Office',
            'DG'            => 'Director General (NRDI)',
            'Approved'      => 'Appointment Sanctioned (Terminal)',
        ];

        return view('admin.hr_workflow_settings', compact('matrix', 'hrStages'));
    }

    /**
     * Update Dynamic Workflow: Hiring & Contract Cases
     */
    public function updateHrWorkflowSettings(Request $request)
    {
        $this->checkAdminAuthorization();

        if ($request->has('forward') && is_array($request->forward)) {
            $hrMatrix = [
                'forward_chain' => [
                    'Division'      => ['next' => $request->forward['Division'] ?? 'HRDirectorate'],
                    'HRDirectorate' => ['next' => $request->forward['HRDirectorate'] ?? 'DFinance'],
                    'DFinance'      => ['next' => $request->forward['DFinance'] ?? 'MD'],
                    'MD'            => ['next' => $request->forward['MD'] ?? 'DDG'],
                    'DDG'           => ['next' => $request->forward['DDG'] ?? 'DG'],
                    'DG'            => ['next' => 'Approved'],
                ],
                'return_chain' => [
                    'HRDirectorate' => $request->return['HRDirectorate'] ?? 'Division',
                    'DFinance'      => $request->return['DFinance'] ?? 'HRDirectorate',
                    'MD'            => $request->return['MD'] ?? 'DFinance',
                    'DDG'           => $request->return['DDG'] ?? 'MD',
                    'DG'            => $request->return['DG'] ?? 'DDG',
                ],
                'return_policy' => $request->return_policy ?? 'historical',
            ];
            SystemSetting::set('hr_workflow_matrix', json_encode($hrMatrix), 'Dynamic Workflow Matrix for HR & Contract Cases');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'HR Hiring & Contract Workflow Routes saved successfully in real-time!'
            ]);
        }

        return redirect()->back()->with('success', 'HR Hiring & Contract Workflow Routes saved successfully!');
    }
}
