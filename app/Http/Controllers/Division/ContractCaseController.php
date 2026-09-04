<?php

namespace App\Http\Controllers\Division;

use App\Http\Controllers\Controller;
use App\Models\HrCtrCase;
use App\Models\HrCtrCasePlan;
use App\Models\HrContract;
use App\Models\HrEmployee;
use App\Services\ContractCaseApprovalService;
use App\Services\ContractCasePricingService;
use App\Services\FileStorageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractCaseController extends Controller
{
    protected ContractCaseApprovalService $approvalService;
    protected ContractCasePricingService $pricingService;

    public function __construct(
        ContractCaseApprovalService $approvalService,
        ContractCasePricingService $pricingService
    ) {
        $this->approvalService = $approvalService;
        $this->pricingService = $pricingService;
    }

    public function index()
    {
        $user = Auth::user();
        $divisionId = $user->acc_lowers ?: ($user->acc_lowerm ?: 0);

        $query = HrCtrCase::with(['casePlans.project', 'currentSubstatus', 'employee'])
            ->where(function ($q) use ($divisionId) {
                if ($divisionId > 0) {
                    $q->where('ctc_divisionid', $divisionId)
                      ->orWhere('ctc_unt_id', $divisionId);
                }
            })
            ->orderBy('ctc_id', 'desc');

        $cases = $query->get();

        // 1. Action Required: Cases held by Division (Drafts & Returned for Revision)
        $actionReqCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return $stage === 'Division' || in_array($c->ctc_status, ['Draft', 'Under Revision']);
        });

        // 2. Open / In Progress: Cases currently in HQ Scrutiny & Approval Pipeline
        $initiatedCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['HR', 'Finance', 'MD', 'DDG', 'DG', 'Approved'])
                && !in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        // 3. Completed / Archive: Finalized cases
        $completedCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['Fulfilled', 'Not Approved', 'Cancelled'])
                || in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        return view('division.contract-cases.index', compact('cases', 'actionReqCases', 'initiatedCases', 'completedCases'));
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'Hg');
        $user = Auth::user();
        $divisionId = $user->acc_lowers ?: ($user->acc_lowerm ?: 0);

        $division = DB::table('cen.units')->where('unt_id', $divisionId)->first();
        $divisionName = $division ? $division->unt_name : 'Unknown Division';

        // Fetch projects for division
        $projects = DB::table('prj.projects')
            ->where(function ($q) use ($divisionId) {
                if ($divisionId > 0) {
                    $q->where('prj_unt_id', $divisionId);
                }
            })
            ->select('prj_id', 'prj_code', 'prj_title')
            ->orderBy('prj_code')
            ->get();

        // Fetch existing employees in this division based on case type
        $employees = collect();
        $typeUpper = strtoupper(trim((string)$type));

        if (in_array($typeUpper, ['CR', 'CE'])) {
            // Cr (Renewal) and Ce (Extension): ONLY Active employees
            $employees = DB::table('hr.emps')
                ->where(function ($q) use ($divisionId) {
                    if ($divisionId > 0) {
                        $q->where('emp_unt_id', $divisionId);
                    }
                })
                ->where('emp_status', 'Active')
                ->select('emp_id', 'emp_name', 'emp_cnic', 'emp_rank', 'emp_title', 'emp_status')
                ->orderBy('emp_name')
                ->get();
        } elseif ($typeUpper === 'RH') {
            // Rh (Rehiring): ONLY Released or Terminated employees
            $employees = DB::table('hr.emps')
                ->where(function ($q) use ($divisionId) {
                    if ($divisionId > 0) {
                        $q->where('emp_unt_id', $divisionId);
                    }
                })
                ->whereIn('emp_status', ['Released', 'Terminated'])
                ->select('emp_id', 'emp_name', 'emp_cnic', 'emp_rank', 'emp_title', 'emp_status')
                ->orderBy('emp_name')
                ->get();
        }

        // If a specific employee is targeted via URL query, ensure they are present in the dropdown
        $preselectedEmpId = trim((string)$request->query('emp_id', ''));
        if (!empty($preselectedEmpId) && !$employees->contains('emp_id', $preselectedEmpId)) {
            $preEmp = DB::table('hr.emps')
                ->where('emp_id', $preselectedEmpId)
                ->select('emp_id', 'emp_name', 'emp_cnic', 'emp_rank', 'emp_title', 'emp_status')
                ->first();
            if ($preEmp) {
                $employees->prepend($preEmp);
            }
        }

        return view('division.contract-cases.create', compact('type', 'projects', 'divisionName', 'employees'));
    }

    /**
     * AJAX endpoint: Get employee contract details and check duplicate active cases
     */
    public function getEmployeeContractDetails($empId)
    {
        $empId = trim((string)$empId);

        // 1. Check for duplicate active case
        $activeCase = HrCtrCase::where('ctc_emp_id', $empId)
            ->whereNotIn('ctc_status', ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled'])
            ->first();

        if ($activeCase) {
            return response()->json([
                'success'           => false,
                'has_active_case'   => true,
                'active_case_id'    => $activeCase->ctc_id,
                'active_status'     => $activeCase->ctc_status,
                'message'           => "An active contract case (CC-{$activeCase->ctc_id}) is currently in '{$activeCase->ctc_status}' status for this employee. A new case cannot be raised until the open case is finalized.",
            ]);
        }

        // 2. Fetch employee details
        $emp = DB::table('hr.emps')->where('emp_id', $empId)->first();
        if (!$emp) {
            return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
        }

        // 3. Fetch latest contract
        $lastContract = HrContract::where('ctr_num', $empId)->orderBy('ctr_id', 'desc')->first();

        $contractData = null;
        if ($lastContract) {
            $effectiveEnd = $lastContract->ctr_termindt ? Carbon::parse($lastContract->ctr_termindt) : Carbon::parse($lastContract->ctr_enddt);
            $suggestedCrStart = $effectiveEnd->copy()->addDay()->format('Y-m-d');
            $suggestedCrEnd = $effectiveEnd->copy()->addDay()->addYear()->subDay()->format('Y-m-d');
            $suggestedCeEnd = $effectiveEnd->copy()->addMonth()->format('Y-m-d');
            $suggestedRhStart = $effectiveEnd->copy()->addDay()->format('Y-m-d');
            $suggestedRhEnd = $effectiveEnd->copy()->addDay()->addYear()->subDay()->format('Y-m-d');

            $contractData = [
                'ctr_id'             => $lastContract->ctr_id,
                'ctr_jobtitle'       => $lastContract->ctr_jobtitle,
                'ctr_grade'          => $lastContract->ctr_grade,
                'ctr_salary'         => $lastContract->ctr_salary,
                'ctr_type'           => $lastContract->ctr_type == 2 ? 'Part Time' : 'Full Time',
                'ctr_startdt'        => $lastContract->ctr_startdt,
                'ctr_enddt'          => $lastContract->ctr_enddt,
                'ctr_termindt'       => $lastContract->ctr_termindt,
                'effective_enddt'    => $effectiveEnd->format('Y-m-d'),
                'suggested_cr_start' => $suggestedCrStart,
                'suggested_cr_end'   => $suggestedCrEnd,
                'suggested_ce_end'   => $suggestedCeEnd,
                'suggested_rh_start' => $suggestedRhStart,
                'suggested_rh_end'   => $suggestedRhEnd,
            ];
        } else {
            $suggestedRhStart = now()->format('Y-m-d');
            $suggestedRhEnd = now()->copy()->addYear()->subDay()->format('Y-m-d');
            $contractData = [
                'ctr_id'             => null,
                'ctr_jobtitle'       => $emp->emp_title,
                'ctr_grade'          => $emp->emp_rank,
                'ctr_salary'         => null,
                'ctr_type'           => 'Full Time',
                'ctr_startdt'        => $suggestedRhStart,
                'ctr_enddt'          => $suggestedRhEnd,
                'ctr_termindt'       => null,
                'effective_enddt'    => null,
                'suggested_cr_start' => $suggestedRhStart,
                'suggested_cr_end'   => $suggestedRhEnd,
                'suggested_ce_end'   => null,
                'suggested_rh_start' => $suggestedRhStart,
                'suggested_rh_end'   => $suggestedRhEnd,
            ];
        }

        return response()->json([
            'success'         => true,
            'has_active_case' => false,
            'employee'        => [
                'emp_id'     => $emp->emp_id,
                'emp_name'   => $emp->emp_name,
                'emp_cnic'   => $emp->emp_cnic,
                'emp_status' => $emp->emp_status,
            ],
            'last_contract'   => $contractData,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $divisionId = $user->acc_lowers ?: ($user->acc_lowerm ?: 0);

        $validated = $request->validate([
            'ctc_type'          => 'required|string',
            'ctc_empnamecomp'   => 'required|string|max:200',
            'ctc_newjobtitle'   => 'required|string|max:255',
            'ctc_newgrade'      => 'required|string|max:100',
            'ctc_emp_type'      => 'required|string|max:50',
            'ctc_newsalary'     => 'required|numeric|min:0',
            'ctc_newstartdt'    => 'required|date',
            'ctc_newenddt'      => 'required|date|after_or_equal:ctc_newstartdt',
            'ctc_newprob'       => 'nullable|numeric|min:0|max:12',
            'ctc_newprobsal'    => 'nullable|numeric|min:0',
            'ctc_jd'            => 'nullable|string',
            'ctc_cnic'          => 'nullable|string|max:20',
            'ctc_contact'       => 'nullable|string|max:50',
            'ctc_emp_id'        => 'nullable|string|max:50',
            'ctc_ctr_id'        => 'nullable|integer',
            'ctc_terminremarks' => 'nullable|string',
            'remarks'           => 'nullable|string',
        ]);

        $type = strtoupper(trim($validated['ctc_type']));

        // 1-Year Maximum Validity Cap (applies to ALL types: Hg, Cr, Ce, Rh)
        $startDt = Carbon::parse($validated['ctc_newstartdt']);
        $maxEndDt = $startDt->copy()->addYear()->subDay();
        if (Carbon::parse($validated['ctc_newenddt'])->gt($maxEndDt)) {
            return response()->json([
                'success' => false,
                'message' => 'Contract duration cannot exceed 1 year from the start date.'
            ], 422);
        }

        // Ce validation: extension remarks mandatory
        if ($type === 'CE' && empty(trim((string)($validated['ctc_terminremarks'] ?? '')))) {
            return response()->json([
                'success' => false,
                'message' => 'Reason for contract extension (ctc_terminremarks) is required for extension cases.'
            ], 422);
        }

        // Duplicate case check if emp_id provided
        if (!empty($validated['ctc_emp_id'])) {
            $activeCase = HrCtrCase::where('ctc_emp_id', $validated['ctc_emp_id'])
                ->whereNotIn('ctc_status', ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled'])
                ->first();

            if ($activeCase) {
                return response()->json([
                    'success' => false,
                    'message' => "An active contract case (CC-{$activeCase->ctc_id}) is already in progress for this employee."
                ], 422);
            }
        }

        $case = DB::transaction(function () use ($request, $validated, $user, $divisionId, $type) {
            $case = new HrCtrCase();
            $case->ctc_type = $validated['ctc_type'];
            $case->ctc_empnamecomp = $validated['ctc_empnamecomp'];
            $case->ctc_newjobtitle = $validated['ctc_newjobtitle'];
            $case->ctc_newgrade = $validated['ctc_newgrade'];
            $case->ctc_emp_type = $validated['ctc_emp_type'];
            $case->ctc_newsalary = (float)$validated['ctc_newsalary'];
            $case->ctc_newstartdt = $validated['ctc_newstartdt'];
            $case->ctc_newenddt = $validated['ctc_newenddt'];
            $case->ctc_newprob = isset($validated['ctc_newprob']) ? (int)$validated['ctc_newprob'] : 0;
            $case->ctc_newprobsal = isset($validated['ctc_newprobsal']) ? (float)$validated['ctc_newprobsal'] : null;
            $case->ctc_terminremarks = $validated['ctc_terminremarks'] ?? null;
            $case->ctc_emp_id = $validated['ctc_emp_id'] ?? null;
            $case->ctc_ctr_id = !empty($validated['ctc_ctr_id']) ? (int)$validated['ctc_ctr_id'] : 0;
            $case->ctc_cnic = $validated['ctc_cnic'] ?? null;
            $case->ctc_contact = $validated['ctc_contact'] ?? null;
            $case->ctc_remarks = $validated['remarks'] ?? null;

            $case->ctc_divisionid = $divisionId;
            $case->ctc_unt_id = $divisionId;
            $case->ctc_newunt_id = $divisionId;
            $case->ctc_date = now();
            $case->ctc_status = 'Draft';
            $case->ctc_createdby = $user->acc_id ?? null;
            $case->ctc_newctrtype = ($validated['ctc_emp_type'] === 'Part Time') ? 2 : 1;

            // Default values for approved columns
            $case->ctc_approvedunt_id = $divisionId;
            $case->ctc_approvedstartdt = $case->ctc_newstartdt;
            $case->ctc_approvedenddt = $case->ctc_newenddt;
            $case->ctc_approvedgrade = $case->ctc_newgrade;
            $case->ctc_approvedjobtitle = $case->ctc_newjobtitle;
            $case->ctc_approvedsalary = $case->ctc_newsalary;
            $case->ctc_approvedctrtype = $case->ctc_newctrtype;
            $case->ctc_approvedprob = $case->ctc_newprob;
            $case->ctc_approvedprobsal = $case->ctc_newprobsal;

            $case->save();

            // Handle CV Upload
            if ($request->hasFile('cv_file')) {
                $path = app(FileStorageService::class)->store(
                    $request->file('cv_file'),
                    'hr',
                    'mx-ctc-',
                    (string) $case->ctc_id
                );

                DB::table('hr.ctrcaseattachments')->insert([
                    'cat_objtype' => 'ctc',
                    'cat_objid'   => $case->ctc_id,
                    'cat_type'    => 'CV',
                    'cat_path'    => $path,
                ]);

                $case->ctc_cv_path = $path;
                $case->save();
            }

            // Project Plan Allocations
            $mode = $request->input('project_mode', 'single');
            $monthlyMap = null;
            $singleProjId = null;

            if ($mode === 'single') {
                $singleProjId = $request->input('ctc_projectcode');
                $case->ctc_prj_id = $singleProjId ? (int)$singleProjId : null;
                $case->save();
            } elseif ($request->has('monthly_project')) {
                $monthlyMap = $request->input('monthly_project');
            }

            $this->pricingService->generatePlans(
                $case->ctc_id,
                $case->ctc_newstartdt,
                $case->ctc_newenddt,
                $monthlyMap,
                $singleProjId ? (int)$singleProjId : null
            );

            // Legacy GetContractCaseProject logic: case-level ctc_prj_id reflects the first month's assignment
            $firstPlan = HrCtrCasePlan::where('ccp_ctc_id', $case->ctc_id)->orderBy('ccp_startdt', 'asc')->first();
            $case->ctc_prj_id = $firstPlan ? $firstPlan->ccp_hed_id : ($singleProjId ? (int)$singleProjId : null);
            $case->save();

            // Calculate exact price
            $this->pricingService->calculatePrice($case);

            // Initialize Sub-Status holder row as Division
            DB::table('hr.ctrcase_substatus')->insert([
                'css_ctc_id'     => $case->ctc_id,
                'css_stage'      => 'Division',
                'css_is_current' => true,
                'css_since'      => now(),
                'css_until'      => null,
            ]);

            return $case;
        });

        return response()->json([
            'success' => true,
            'message' => 'Contract Case draft created successfully.',
            'case_id' => $case->ctc_id
        ]);
    }

    public function edit($id)
    {
        $case = HrCtrCase::with([
            'casePlans.project',
            'attachments',
            'remarksHistory',
            'currentSubstatus',
            'previousContract',
            'employee'
        ])->findOrFail($id);

        $user = Auth::user();
        $divisionId = $user->acc_lowers ?: ($user->acc_lowerm ?: 0);

        // Security check: Only editable if currently in Division stage AND status is 'Under Revision' or 'Draft'
        $currentStage = $case->current_stage;
        if ($currentStage !== 'Division' && !in_array($case->ctc_status, ['Draft', 'Under Revision'])) {
            return redirect()->route('division.contract-cases.show', $id)
                ->with('error', 'This case is currently locked and undergoing scrutiny. It cannot be edited unless returned to Division.');
        }

        // Division verification
        if ($divisionId > 0 && $case->ctc_divisionid > 0 && $case->ctc_divisionid != $divisionId) {
            abort(403, 'Unauthorized access to this contract case.');
        }

        $division = DB::table('cen.units')->where('unt_id', $case->ctc_divisionid ?: $divisionId)->first();
        $divisionName = $division ? $division->unt_name : 'Division';

        // Fetch projects for division
        $projects = DB::table('prj.projects')
            ->where(function ($q) use ($divisionId, $case) {
                $div = $case->ctc_divisionid ?: $divisionId;
                if ($div > 0) {
                    $q->where('prj_unt_id', $div);
                }
            })
            ->select('prj_id', 'prj_code', 'prj_title')
            ->orderBy('prj_code')
            ->get();

        // Get latest return remark (if returned for revision)
        $latestReturnRemark = $case->remarksHistory->first(function($r) {
            return in_array($r->crr_status, ['Under Revision', 'Not Approved']);
        }) ?? $case->remarksHistory->first();

        // Existing monthly plan map
        $monthlyPlanMap = [];
        foreach ($case->casePlans as $cp) {
            $monthKey = Carbon::parse($cp->ccp_startdt)->format('Y-m');
            $monthlyPlanMap[$monthKey] = $cp->ccp_hed_id;
        }

        return view('division.contract-cases.edit', compact('case', 'projects', 'divisionName', 'latestReturnRemark', 'monthlyPlanMap'));
    }

    public function update($id, Request $request)
    {
        $case = HrCtrCase::with(['currentSubstatus', 'previousContract'])->findOrFail($id);
        $user = Auth::user();
        $divisionId = $user->acc_lowers ?: ($user->acc_lowerm ?: 0);

        // Security check
        $currentStage = $case->current_stage;
        if ($currentStage !== 'Division' && !in_array($case->ctc_status, ['Draft', 'Under Revision'])) {
            return response()->json([
                'success' => false,
                'message' => 'This contract case is currently locked for review and cannot be modified.'
            ], 403);
        }

        $validated = $request->validate([
            'ctc_empnamecomp'   => 'required|string|max:200',
            'ctc_newjobtitle'   => 'required|string|max:255',
            'ctc_newgrade'      => 'required|string|max:100',
            'ctc_emp_type'      => 'required|string|max:50',
            'ctc_newsalary'     => 'required|numeric|min:0',
            'ctc_newstartdt'    => 'required|date',
            'ctc_newenddt'      => 'required|date|after_or_equal:ctc_newstartdt',
            'ctc_newprob'       => 'nullable|numeric|min:0|max:12',
            'ctc_newprobsal'    => 'nullable|numeric|min:0',
            'ctc_jd'            => 'nullable|string',
            'ctc_cnic'          => 'nullable|string|max:20',
            'ctc_contact'       => 'nullable|string|max:50',
            'ctc_terminremarks' => 'nullable|string',
            'remarks'           => 'nullable|string',
        ]);

        $type = strtoupper(trim($case->ctc_type));

        // 1-Year Maximum Validity Cap (applies to ALL types: Hg, Cr, Ce, Rh)
        $startDt = Carbon::parse($validated['ctc_newstartdt']);
        $maxEndDt = $startDt->copy()->addYear()->subDay();
        if (Carbon::parse($validated['ctc_newenddt'])->gt($maxEndDt)) {
            return response()->json([
                'success' => false,
                'message' => 'Contract duration cannot exceed 1 year from the start date.'
            ], 422);
        }

        // Ce validation: enforce immutable fields server-side
        if ($type === 'CE') {
            if (empty(trim((string)($validated['ctc_terminremarks'] ?? '')))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reason for contract extension (ctc_terminremarks) is required for extension cases.'
                ], 422);
            }

            // Restore/Enforce locked fields from previous contract / original values
            if ($case->previousContract) {
                $validated['ctc_newjobtitle'] = $case->previousContract->ctr_jobtitle;
                $validated['ctc_newgrade'] = $case->previousContract->ctr_grade;
                $validated['ctc_newsalary'] = $case->previousContract->ctr_salary;
                $validated['ctc_newstartdt'] = $case->previousContract->ctr_startdt;
                $validated['ctc_emp_type'] = $case->previousContract->ctr_type == 2 ? 'Part Time' : 'Full Time';
            }
        }

        DB::transaction(function () use ($case, $request, $validated, $user, $divisionId, $type) {
            $case->ctc_empnamecomp = $validated['ctc_empnamecomp'];
            $case->ctc_newjobtitle = $validated['ctc_newjobtitle'];
            $case->ctc_newgrade = $validated['ctc_newgrade'];
            $case->ctc_emp_type = $validated['ctc_emp_type'];
            $case->ctc_newsalary = (float)$validated['ctc_newsalary'];
            $case->ctc_newstartdt = $validated['ctc_newstartdt'];
            $case->ctc_newenddt = $validated['ctc_newenddt'];
            $case->ctc_newprob = isset($validated['ctc_newprob']) ? (int)$validated['ctc_newprob'] : 0;
            $case->ctc_newprobsal = isset($validated['ctc_newprobsal']) ? (float)$validated['ctc_newprobsal'] : null;
            $case->ctc_terminremarks = $validated['ctc_terminremarks'] ?? $case->ctc_terminremarks;
            $case->ctc_cnic = $validated['ctc_cnic'] ?? $case->ctc_cnic;
            $case->ctc_contact = $validated['ctc_contact'] ?? $case->ctc_contact;
            $case->ctc_remarks = $validated['remarks'] ?? $case->ctc_remarks;
            $case->ctc_newctrtype = ($validated['ctc_emp_type'] === 'Part Time') ? 2 : 1;

            // Also keep proposed approved columns in sync
            $case->ctc_approvedstartdt = $case->ctc_newstartdt;
            $case->ctc_approvedenddt = $case->ctc_newenddt;
            $case->ctc_approvedgrade = $case->ctc_newgrade;
            $case->ctc_approvedjobtitle = $case->ctc_newjobtitle;
            $case->ctc_approvedsalary = $case->ctc_newsalary;
            $case->ctc_approvedctrtype = $case->ctc_newctrtype;
            $case->ctc_approvedprob = $case->ctc_newprob;
            $case->ctc_approvedprobsal = $case->ctc_newprobsal;

            $case->save();

            // Handle new CV Upload if provided
            if ($request->hasFile('cv_file')) {
                $path = app(FileStorageService::class)->store(
                    $request->file('cv_file'),
                    'hr',
                    'mx-ctc-',
                    (string) $case->ctc_id
                );

                DB::table('hr.ctrcaseattachments')->updateOrInsert(
                    ['cat_objtype' => 'ctc', 'cat_objid' => $case->ctc_id, 'cat_type' => 'CV'],
                    ['cat_path' => $path]
                );

                $case->ctc_cv_path = $path;
                $case->save();
            }

            // Regenerate Project Plan Allocations
            $mode = $request->input('project_mode', 'single');
            $monthlyMap = null;
            $singleProjId = null;

            if ($mode === 'single') {
                $singleProjId = $request->input('ctc_projectcode');
                $case->ctc_prj_id = $singleProjId ? (int)$singleProjId : null;
                $case->save();
            } elseif ($request->has('monthly_project')) {
                $monthlyMap = $request->input('monthly_project');
                $case->ctc_prj_id = null;
                $case->save();
            }

            $this->pricingService->generatePlans(
                $case->ctc_id,
                $case->ctc_newstartdt,
                $case->ctc_newenddt,
                $monthlyMap,
                $singleProjId ? (int)$singleProjId : null
            );

            // Legacy GetContractCaseProject logic: case-level ctc_prj_id reflects the first month's assignment
            $firstPlan = HrCtrCasePlan::where('ccp_ctc_id', $case->ctc_id)->orderBy('ccp_startdt', 'asc')->first();
            $case->ctc_prj_id = $firstPlan ? $firstPlan->ccp_hed_id : ($singleProjId ? (int)$singleProjId : null);
            $case->save();

            // Recalculate price
            $this->pricingService->calculatePrice($case);
        });

        return response()->json([
            'success' => true,
            'message' => 'Contract case revision saved successfully.',
            'case_id' => $case->ctc_id
        ]);
    }

    public function show($id)
    {
        $case = HrCtrCase::with([
            'casePlans.project',
            'attachments',
            'remarksHistory',
            'currentSubstatus',
            'previousContract',
            'newContract',
            'employee',
            'unit'
        ])->findOrFail($id);

        $authorityRole = 'Division';
        $authDetails = $this->approvalService->getApprovalAuthorityDetails($case);
        $canApprove = false;

        return view('md.contract-cases.show', compact('case', 'authorityRole', 'authDetails', 'canApprove'));
    }

    public function release($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);

        // Enforce candidate CNIC validation before release
        $cnic = $case->candidate_cnic;
        if (empty($cnic) || trim($cnic) === '' || strtolower(trim($cnic)) === 'n/a') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot release case: Candidate CNIC number is required before releasing to HR Scrutiny. Please edit the case and provide a valid CNIC.'
            ], 422);
        }

        $user = Auth::user();
        $remarks = $request->input('remarks');
        if (empty(trim($remarks ?? ''))) {
            return response()->json(['success' => false, 'message' => 'Remarks are mandatory.'], 422);
        }

        $this->approvalService->release($case, $user, $remarks);

        return response()->json([
            'success' => true,
            'message' => 'Contract Case released to HR Scrutiny successfully.'
        ]);
    }

    public function forward($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks');
        if (empty(trim($remarks ?? ''))) {
            return response()->json(['success' => false, 'message' => 'Remarks are mandatory.'], 422);
        }

        $targetStage = $request->input('target_destination') ?? $request->input('target_stage') ?? 'HR';

        if ($targetStage === 'HR') {
            $cnic = $case->candidate_cnic;
            if (empty($cnic) || trim($cnic) === '' || strtolower(trim($cnic)) === 'n/a') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot release case: Candidate CNIC number is required before releasing to HR Scrutiny. Please edit the case and provide a valid CNIC.'
                ], 422);
            }
            $this->approvalService->release($case, $user, $remarks);
            return response()->json([
                'success' => true,
                'message' => 'Contract Case released to HR Scrutiny successfully.'
            ]);
        }

        $nextStage = $this->approvalService->forward($case, $user, $remarks, $targetStage);

        return response()->json([
            'success' => true,
            'message' => "Case forwarded to {$nextStage} successfully.",
            'next_stage' => $nextStage
        ]);
    }

    public function cancel($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks', 'Cancelled by division');

        $this->approvalService->cancel($case, $user, $remarks);

        return response()->json([
            'success' => true,
            'message' => 'Contract Case has been cancelled.'
        ]);
    }
}
