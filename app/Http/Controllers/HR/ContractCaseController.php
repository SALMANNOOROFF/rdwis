<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HrCtrCase;
use App\Services\ContractCaseApprovalService;
use App\Services\ContractCaseFulfillmentService;
use App\Services\EmployeeCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class ContractCaseController extends Controller
{
    protected ContractCaseApprovalService $approvalService;
    protected ContractCaseFulfillmentService $fulfillmentService;
    protected EmployeeCreationService $employeeCreationService;

    public function __construct(
        ContractCaseApprovalService $approvalService,
        ContractCaseFulfillmentService $fulfillmentService,
        EmployeeCreationService $employeeCreationService
    ) {
        $this->approvalService = $approvalService;
        $this->fulfillmentService = $fulfillmentService;
        $this->employeeCreationService = $employeeCreationService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));

        if ($request->has('mode')) {
            $mode = $request->query('mode') === 's' ? 's' : 'm';
            session(['hr_mode' => $mode]);
        } else {
            $defaultMode = in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs']) ? 'm' : 's';
            $mode = session('hr_mode', $defaultMode);
        }

        $query = HrCtrCase::with(['casePlans.project', 'currentSubstatus', 'employee', 'previousContract', 'newContract'])
            ->whereNotIn('ctc_status', ['Draft']);

        if ($mode === 's' && $user) {
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
            if ($lower > 0 && $upper > 0) {
                $query->whereBetween('ctc_divisionid', [$lower, $upper]);
            }
        }

        $cases = $query->orderBy('ctc_id', 'desc')->get();

        // 1. Action Required: Cases held by HR (HR Scrutiny) OR Approved cases ready for fulfillment
        $actionReqCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['HR', 'Approved'])
                && !in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        // 2. Open / In Pipeline: Cases currently undergoing scrutiny with other authorities (Finance, MD, DDG, DG)
        $initiatedCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['Finance', 'MD', 'DDG', 'DG'])
                && !in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        // 3. Completed: Finalized cases
        $completedCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['Fulfilled', 'Not Approved', 'Cancelled'])
                || in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        return view('hr.contract-cases.index', compact('cases', 'actionReqCases', 'initiatedCases', 'completedCases', 'mode'));
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

        $authorityRole = 'HR';
        $authDetails = $this->approvalService->getApprovalAuthorityDetails($case);
        $canApprove = false;
        $strength = [];
        $deptMap = EmployeeCreationService::getDepartmentMap();
        $departments = DB::table('cen.units')->orderBy('unt_name')->get();

        return view('md.contract-cases.show', compact('case', 'authorityRole', 'authDetails', 'canApprove', 'strength', 'deptMap', 'departments'));
    }

    public function addEmployee($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();

        // Server-side authorization check: Only HR administrators can register new employees
        $userArea = strtolower(trim((string)($user->acc_untarea ?? '')));
        if (!in_array($userArea, ['hr', 'rdw', 'nrdi', 'hqs']) && (!$user || !$user->canAccessArea('hr'))) {
            abort(403, 'Unauthorized access. Only HR administrators can register new employees for contract cases.');
        }

        $validated = $request->validate([
            'emp_cnic'   => ['required', 'string', 'regex:/^(\d{5}-\d{7}-\d{1}|\d{13})$/'],
            'emp_joindt' => 'required|date',
            'emp_name'   => 'required|string|max:200',
            'emp_unt_id' => 'required|integer',
            'emp_title'  => 'nullable|string|max:255',
            'emp_rank'   => 'nullable|string|max:100',
        ], [
            'emp_cnic.required'   => 'Please enter CNIC.',
            'emp_cnic.regex'      => 'Please enter a valid CNIC (e.g. 42101-1234567-1 or 13 digits).',
            'emp_joindt.required' => 'Please enter joining date.',
            'emp_name.required'   => 'Please enter employee name.',
            'emp_unt_id.required' => 'Please select department.',
        ]);

        try {
            $employee = $this->employeeCreationService->addEmployeeForContractCase($case, $validated, $user);

            return response()->json([
                'success'  => true,
                'message'  => "Employee {$employee->emp_id} successfully created and linked to the contract case!",
                'emp_id'   => $employee->emp_id,
                'employee' => $employee
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function previewEmployeeId(Request $request)
    {
        $unitId = (int)$request->query('emp_unt_id');
        $joinDate = (string)$request->query('emp_joindt', date('Y-m-d'));
        $cnic = (string)$request->query('emp_cnic', '');

        try {
            $info = $this->employeeCreationService->generateEmpId($unitId, $joinDate, $cnic);
            return response()->json([
                'success' => true,
                'data'    => $info
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function forward($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks');
        if (empty(trim($remarks ?? ''))) {
            return response()->json(['success' => false, 'message' => 'Remarks are mandatory.'], 422);
        }
        $targetStage = $request->input('target_destination') ?? $request->input('target_stage') ?? 'Finance';

        $nextStage = $this->approvalService->forward($case, $user, $remarks, $targetStage);

        return response()->json([
            'success' => true,
            'message' => "Case forwarded to {$nextStage} successfully.",
            'next_stage' => $nextStage
        ]);
    }

    public function return($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks');
        if (empty(trim($remarks ?? ''))) {
            return response()->json(['success' => false, 'message' => 'Remarks are mandatory.'], 422);
        }
        $targetStage = $request->input('target_destination') ?? $request->input('target_stage') ?? 'Division';

        $destStage = $this->approvalService->return($case, $user, $remarks, $targetStage);

        return response()->json([
            'success' => true,
            'message' => "Case returned to {$destStage} for revision."
        ]);
    }

    public function fulfill($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();

        try {
            $this->fulfillmentService->fulfill($case, $user, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Contract Case fulfilled successfully! Contract has been created/updated.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function reject($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks', 'Case rejected by HR.');
        if (empty(trim($remarks ?? ''))) {
            return response()->json(['success' => false, 'message' => 'Remarks are mandatory.'], 422);
        }

        $this->approvalService->reject($case, $user, $remarks);

        return response()->json([
            'success' => true,
            'message' => 'Case marked as Not Approved.'
        ]);
    }
}
