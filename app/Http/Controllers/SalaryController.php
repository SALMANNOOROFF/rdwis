<?php

namespace App\Http\Controllers;

use App\Models\HrSalReq;
use App\Models\FinSalOrder;
use App\Models\CenHead;
use App\Services\SalaryGenerationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalaryController extends Controller
{
    public function __construct(
        protected SalaryGenerationService $salaryService
    ) {}

    /**
     * 1. Requisition Dashboard.
     * Filterable by exact srq_status (Draft, In Process, Fulfilled, Cancelled) and month.
     */
    public function requisitionsIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $month = $request->query('month');
        $status = $request->query('status', 'Draft');

        
        $requisitions = $this->salaryService->getRequisitions($user, $month, $status);

        $isCentralMyDept = method_exists($user, 'isCentralDepartment') && $user->isCentralDepartment() && $user->isMyDepartmentMode();
        $isCentral = (method_exists($user, 'isCentralDepartment') && $user->isCentralDepartment()) || ((int)($user->acc_unt_id ?? 0) >= 800000);

        // 1. Central Departments: CSRF-funded project heads
        $csrfHeads = DB::table('cen.heads as h')
            ->join('fin.sharesalloc as s', 's.sha_hed_id', '=', 'h.hed_id')
            ->where('s.sha_cf', '>', 0)
            ->whereNull('h.hed_closedt')
            ->select('h.hed_id', 'h.hed_code', 'h.hed_name', 'h.hed_type', 'h.hed_unt_id')
            ->orderBy('h.hed_code')
            ->distinct()
            ->get();

        // 2. Operational Divisions: Strictly user's division heads
        [$lower, $upper] = $this->getUserBounds($user);
        $divHeads = CenHead::where(function($q) use ($user, $lower, $upper) {
                $q->whereBetween('hed_unt_id', [$lower, $upper])
                  ->orWhere('hed_unt_id', $user->acc_unt_id);
            })
            ->whereNull('hed_closedt')
            ->orderBy('hed_code')
            ->select('hed_id', 'hed_code', 'hed_name', 'hed_type', 'hed_unt_id')
            ->get();

        $heads = ($isCentralMyDept || ($isCentral && method_exists($user, 'isMyDepartmentMode') && $user->isMyDepartmentMode())) ? $csrfHeads : $divHeads;

        return view('hr.salary.requisitions.index', compact('requisitions', 'month', 'status', 'heads', 'csrfHeads', 'divHeads', 'isCentral'));
    }

    protected function getUserBounds($user): array
    {
        $isMyDept = method_exists($user, 'isMyDepartmentMode') && $user->isMyDepartmentMode();
        $isMultiple = (($user->acc_access ?? '') === 'multiple' || strtolower(trim((string)($user->acc_untarea ?? ''))) === 'fin') && !$isMyDept;
        $lower = $isMultiple ? ($user->acc_lowerm ?? 100000) : ($user->acc_lowers ?? 100000);
        $upper = $isMultiple ? ($user->acc_upperm ?? 999999) : ($user->acc_uppers ?? 999999);
        return [(int)$lower, (int)$upper];
    }

    /**
     * 2. Requisition Generation Page.
     */
    public function requisitionsCreate(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $month = now()->format('Y-m');
        $unitId = $user->acc_unt_id;

        [$lower, $upper] = $this->getUserBounds($user);

        $units = DB::table('cen.units')
            ->where('unt_id', $unitId)
            ->get();

        return view('hr.salary.requisitions.create', compact('month', 'unitId', 'units'));
    }

    /**
     * 3. Salary Preview AJAX endpoint.
     * Evaluates candidates against all 7 legacy exclusion checks.
     */
    public function preview(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Enforce month and appropriate unit scope
        $month = $request->input('month', now()->format('Y-m'));
        $userArea = strtolower(trim((string)($user->acc_untarea ?? '')));
        $isCentralUser = in_array($userArea, ['hr', 'fin', 'adm', 'admin', 'is', 'it', 'hqs', 'rdw', 'nrdi'], true) || ((int)($user->acc_unt_id ?? 0) >= 800000);
        $unitScope = $request->filled('unit_id') ? (int) $request->input('unit_id') : (int) $user->acc_unt_id;

        $preview = $this->salaryService->previewSalary($month, $unitScope, $user);

        return response()->json($preview);
    }

    /**
     * 4. Generate Requisitions POST endpoint.
     * Catches duplicate guard and surfaces exact conflicting employees and periods.
     */
    public function generateRequisitions(Request $request)
    {
        $this->authorize('generate', FinSalOrder::class);
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'month'     => ['required', 'date_format:Y-m'],
            'emp_ids'   => ['required', 'array', 'min:1'],
            'emp_ids.*' => ['required', 'string'],
        ]);

        $month = $request->input('month', now()->format('Y-m'));
        $empIds = $request->input('emp_ids');

        $userArea = strtolower(trim((string)($user->acc_untarea ?? '')));
        $isCentralUser = in_array($userArea, ['hr', 'fin', 'adm', 'admin', 'is', 'it', 'hqs', 'rdw', 'nrdi'], true) || ((int)($user->acc_unt_id ?? 0) >= 800000);
        $preflightScope = $isCentralUser ? null : (int) $user->acc_unt_id;

        // Pre-flight duplicate check: surface exact conflicting employee IDs and period
        $preview = $this->salaryService->previewSalary($month, $preflightScope, $user);
        $excludedMap = collect($preview['excluded'])->keyBy(fn($x) => $x['employee']->emp_id);

        $conflicts = [];
        foreach ($empIds as $eid) {
            if ($excludedMap->has($eid) && str_starts_with($excludedMap->get($eid)['reason'] ?? '', 'Already Generated')) {
                $cand = $excludedMap->get($eid);
                $conflicts[] = [
                    'emp_id' => $eid,
                    'name'   => $cand['employee']->emp_name,
                    'period' => $preview['month'],
                    'reason' => $cand['reason'] ?? 'Requisition already generated in Draft, In Process, or Fulfilled status',
                ];
            }
        }

        if (!empty($conflicts)) {
            return response()->json([
                'error'     => 'Duplicate requisition detected for ' . count($conflicts) . ' employee(s).',
                'conflicts' => $conflicts,
            ], 422);
        }

        $result = $this->salaryService->generateSalary($month, $empIds, $user);

        return response()->json([
            'success'   => true,
            'generated' => $result['generated'],
            'message'   => "Successfully generated {$result['generated']} salary requisition(s).",
        ]);
    }

    /**
     * 5. Release Requisition Group POST endpoint (Draft -> In Process).
     */
    public function releaseRequisition(Request $request, int $srqId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $req = HrSalReq::findOrFail($srqId);
        [$lower, $upper] = $this->getUserBounds($user);

        if ($req->srq_unt_id < $lower || $req->srq_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
        }

        if ($req->srq_status !== 'Draft') {
            return back()->with('error', "Cannot release requisition #{$srqId} with status '{$req->srq_status}'. Only 'Draft' requisitions can be released.");
        }

        // Resolves to group root parent so child-initiated release cascades to parent and siblings
        $targetId = ($req->srq_parent && $req->srq_parent > 0) ? (int)$req->srq_parent : (int)$req->srq_id;

        // Pre-Release Budget Check (Legacy AccountChecks.bas AnyObjectionsOnExp) across all members of group
        $groupReqs = HrSalReq::where('srq_id', $targetId)
            ->orWhere('srq_parent', $targetId)
            ->get();

        $objections = [];
        foreach ($groupReqs as $gr) {
            $effectiveHeadId = (int)($gr->srq_effhed_id ?: $gr->srq_hed_id);
            $obj = $this->salaryService->checkObjectionsOnExp(
                $effectiveHeadId,
                $effectiveHeadId,
                $gr->srq_sudohed,
                (float)$gr->srq_salary
            );
            if ($obj) {
                $objections[] = "Employee {$gr->srq_empnamecomp} ({$gr->srq_emp_id}): {$obj}";
            }
        }

        if (!empty($objections)) {
            return back()->with('error', "Pre-Release Budget Warning! " . implode(' | ', $objections));
        }

        $this->salaryService->releaseRequisitions($targetId);
        $orders = $this->salaryService->createSalaryOrders($targetId);

        return back()->with('success', "Salary requisition #{$targetId} and split parts released to In Process and " . count($orders) . " Draft Salary Order(s) created for Finance.");
    }

    /**
     * 6. Cancel Requisition Group POST endpoint (Draft -> Cancelled).
     */
    public function cancelRequisition(Request $request, int $srqId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $req = HrSalReq::findOrFail($srqId);
        [$lower, $upper] = $this->getUserBounds($user);

        if ($req->srq_unt_id < $lower || $req->srq_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
        }

        $this->salaryService->cancelRequisition($srqId);

        return back()->with('success', "Salary requisition #{$srqId} cancelled successfully.");
    }

    /**
     * 6b. Split Requisition POST endpoint (Legacy hr_salreq_split.bas).
     */
    public function splitRequisition(Request $request, int $srqId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $req = HrSalReq::findOrFail($srqId);
        [$lower, $upper] = $this->getUserBounds($user);

        if ($req->srq_unt_id < $lower || $req->srq_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
        }

        $validated = $request->validate([
            'parent_salary'      => ['required', 'numeric', 'min:1'],
            'splits'             => ['required', 'array', 'min:1'],
            'splits.*.effhed_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!\App\Models\CenHead::where('hed_id', $value)->exists()) {
                        $fail("Selected head (ID: {$value}) is invalid.");
                    }
                }
            ],
            'splits.*.salary'    => ['required', 'numeric', 'min:1'],
        ]);

        try {
            $result = $this->salaryService->splitRequisition(
                $srqId,
                (float)$validated['parent_salary'],
                $validated['splits'],
                $user
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Salary requisition #{$srqId} splitted successfully.",
                    'data'    => $result,
                ]);
            }

            return back()->with('success', "Salary requisition #{$srqId} splitted successfully into " . (count($result['children']) + 1) . " parts.");
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * AJAX endpoint to fetch real-time available head balance for salary split.
     */
    public function getHeadBalance(Request $request, int $hedId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $head = CenHead::find($hedId);
        if (!$head) {
            return response()->json(['error' => 'Head not found'], 404);
        }

        $isCsrf = filter_var($request->query('is_csrf', false), FILTER_VALIDATE_BOOLEAN);
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $status = $finService->getHeadStatus($hedId);

        $available = $isCsrf 
            ? (float)($status->cf_available ?? ($status['cf_available'] ?? 0)) 
            : (float)($status->pcc_available ?? ($status->available ?? ($status['pcc_available'] ?? ($status['available'] ?? 0))));

        return response()->json([
            'success'          => true,
            'head_id'          => $hedId,
            'head_code'        => $head->hed_code,
            'is_csrf'          => $isCsrf,
            'available'        => round($available, 2),
            'available_format' => number_format($available, 2),
        ]);
    }

    /**
     * 7. Salary Orders Dashboard.
     * Legacy fin_salorders_u.bas: Approvers and Monitors manage orders by tab.
     */
    public function ordersIndex(Request $request)
    {
        $this->authorize('viewAny', FinSalOrder::class);
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (strtolower(trim((string) ($user->acc_untarea ?? ''))) === 'hr') {
            abort(403, 'Unauthorized. HR does not have access to Salary Orders. Salary Orders are managed by Finance.');
        }

        $month = $request->query('month');
        $status = $request->query('status', 'Draft');
        $search = $request->query('search');
        $colFilters = $request->only([
            'col_order', 'col_emp', 'col_dept', 'col_cont', 'col_calc',
            'col_payable', 'col_account', 'col_remarks', 'col_remarks2',
            'col_status', 'col_cmt'
        ]);

        $orders = $this->salaryService->getOrders($user, $month, $status, 25, $search, $colFilters);

        return view('hr.salary.orders.index', compact('orders', 'month', 'status', 'search', 'colFilters'));
    }

    /**
     * AJAX Toggle Checked (Verification) State for Salary Order.
     * Persists in fin.salorders.sor_checked.
     */
    public function toggleCheck(Request $request, int $sorId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'error' => 'Unauthenticated'], 401);
        }

        try {
            $order = FinSalOrder::findOrFail($sorId);
            $checked = filter_var($request->input('checked'), FILTER_VALIDATE_BOOLEAN);

            $order->sor_checked = $checked;
            $order->save();

            return response()->json([
                'success' => true,
                'sor_id'  => $sorId,
                'checked' => (bool)$order->sor_checked,
                'message' => $order->sor_checked ? "Order #{$sorId} marked as scrutinized." : "Order #{$sorId} unmarked.",
            ]);
        } catch (\Throwable $e) {
            Log::error("toggleCheck error on order #{$sorId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX endpoint to get remaining active employees who do not have a draft salary order.
     * STRICT: Excludes Released and Terminated employees.
     */
    public function remainingEmployees(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $month = $request->query('month') ?: now()->format('Y-m');
        $employees = $this->salaryService->getRemainingEmployees($month, $user);

        return response()->json([
            'success'   => true,
            'month'     => $month,
            'count'     => count($employees),
            'employees' => $employees,
        ]);
    }

    /**
     * 8. Create Orders from Requisition Group (In Process -> Draft Orders).
     * Restricted to Finance only.
     */
    public function createOrders(Request $request, int $srqId)
    {
        $this->authorize('generate', FinSalOrder::class);
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (strtolower(trim((string) ($user->acc_untarea ?? ''))) !== 'fin') {
            abort(403, 'Unauthorized. Only Finance can create or manage Salary Orders.');
        }

        $req = HrSalReq::findOrFail($srqId);
        [$lower, $upper] = $this->getUserBounds($user);

        if ($req->srq_unt_id < $lower || $req->srq_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
        }

        if ($req->srq_status !== 'In Process') {
            return back()->with('error', "Cannot create salary orders for requisition #{$srqId} with status '{$req->srq_status}'. Must be 'In Process'.");
        }

        $orders = $this->salaryService->createSalaryOrders($srqId);

        return redirect()->route('divhr.salary.orders.index')
            ->with('success', "Created " . count($orders) . " salary order(s) for requisition #{$srqId} successfully.");
    }

    /**
     * 9. Salary Order Detail View.
     * Displays financial summary, exact subhead breakdown, commitment status, and payment settlement.
     */
    public function orderShow(Request $request, int $sorId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $order = $this->salaryService->getOrderDetail($sorId, $user);
        if (!$order) {
            abort(404, "Salary order #{$sorId} not found or unauthorized.");
        }

        $this->authorize('view', $order);

        return view('hr.salary.orders.show', compact('order'));
    }

    /**
     * 10. Approve Salary Order POST endpoint (Draft -> Approved, creates negative liability commitment).
     */
    public function approveOrder(Request $request, int $sorId)
    {
        $order = FinSalOrder::findOrFail($sorId);
        $this->authorize('approve', $order);
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (($user->acc_auth ?? '') !== 'approver' || strtolower(trim((string) ($user->acc_untarea ?? ''))) !== 'fin') {
            abort(403, 'Finance approver authorization required to approve salary orders.');
        }

        if ($order->sor_status !== 'Draft') {
            return back()->with('error', "Cannot approve order #{$sorId} with status '{$order->sor_status}'. Only 'Draft' orders can be approved.");
        }

        $this->salaryService->approveSalaryOrders($sorId, $user);

        return back()->with('success', "Salary order #{$sorId} approved and commitment created successfully.");
    }

    /**
     * Bulk Approve Salary Orders POST endpoint (Draft -> Approved).
     */
    public function bulkApproveOrders(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (($user->acc_auth ?? '') !== 'approver' || strtolower(trim((string) ($user->acc_untarea ?? ''))) !== 'fin') {
            abort(403, 'Finance approver authorization required to approve salary orders.');
        }

        $request->validate([
            'sor_ids'   => ['required', 'array', 'min:1'],
            'sor_ids.*' => ['required', 'integer'],
        ]);

        $sorIds = $request->input('sor_ids');
        $approvedCount = 0;

        DB::transaction(function () use ($sorIds, $user, &$approvedCount) {
            foreach ($sorIds as $sorId) {
                $order = FinSalOrder::find($sorId);
                if ($order && $order->sor_status === 'Draft') {
                    $this->salaryService->approveSalaryOrders($sorId, $user);
                    $approvedCount++;
                }
            }
        });

        return back()->with('success', "Successfully approved {$approvedCount} salary order(s) and created commitments.");
    }

    /**
     * 11. Cancel Salary Order POST endpoint (High-friction cancellation).
     */
    public function cancelOrder(Request $request, int $sorId)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $order = FinSalOrder::findOrFail($sorId);
        $this->authorize('override', $order);
        $user = Auth::user();
        [$lower, $upper] = $this->getUserBounds($user);

        if ($order->sor_unt_id < $lower || $order->sor_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
        }

        if ($order->sor_parent && $order->sor_parent > 0) {
            $msg = 'This order cannot be returned directly. Please return the parent order.';
            if ($request->expectsJson()) {
                return response()->json(['error' => $msg, 'message' => $msg], 422);
            }
            abort(422, $msg);
        }

        if ($order->sor_status === 'Cancelled') {
            return back()->with('error', "Salary order #{$sorId} is already returned/cancelled.");
        }

        $reason = $request->input('reason');
        Log::info("Salary order #{$sorId} returned to division draft by {$user->acc_username}. Reason: {$reason}");

        try {
            $this->salaryService->cancelOrder($sorId, $reason);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
            }
            throw $e;
        }

        return redirect()->route('divhr.salary.orders.index')
            ->with('success', "Salary order #{$sorId} returned to division draft successfully. Funds released from In-Process back to Remaining balance.");
    }

    /**
     * Initiate a data revision for a Salary Order.
     * Legacy fin_salorders_u.bas:166.
     * Distinct from cancelOrder (Mechanism A vs Mechanism B).
     */
    public function reverseOrder(Request $request, int $sorId, \App\Services\DataRevisionService $revisionService)
    {
        \Illuminate\Support\Facades\Gate::authorize('initiate', \App\Models\AudRev::class);

        $user = Auth::user();
        $order = FinSalOrder::findOrFail($sorId);

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isFinOrAdmin = in_array($userArea, ['fin', 'rdw', 'hqs', 'it'], true) || ($user->acc_username === 'superadminrdw');

        if (!$isFinOrAdmin) {
            if (!app(\App\Services\Auth\DataScopeService::class)->canAccessUnit($user, (int) $order->sor_unt_id)) {
                abort(403, 'Unauthorized. Salary order is outside your unit scope.');
            }
        }

        $reason = $request->input('rev_reason') ?: $request->input('reason');

        $revision = $revisionService->createDataRevision(
            revObject: 'Salary Order',
            objectId: $order->sor_id,
            unitId: (int) $order->sor_unt_id,
            revType: \App\Enums\RevType::FULL_CASCADE,
            revRef: null,
            revObjectExt: null,
            intUnitId: (int) ($user->acc_unt_id ?? $order->sor_unt_id),
            revReason: $reason
        );

        return redirect()->route('admin.reversals.show', $revision->rev_id)
            ->with('success', "Data revision draft #{$revision->rev_id} for Salary Order #{$sorId} created successfully.");
    }

    /**
     * Initiate a data revision for a Salary Requisition (via linked Salary Order).
     * Legacy hr_salreqs_u.bas:109,116.
     */
    public function reverseRequisition(Request $request, int $srqId, \App\Services\DataRevisionService $revisionService)
    {
        \Illuminate\Support\Facades\Gate::authorize('initiate', \App\Models\AudRev::class);

        $user = Auth::user();
        $srq = HrSalReq::findOrFail($srqId);

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isHrOrFinOrAdmin = in_array($userArea, ['fin', 'hr', 'rdw', 'hqs', 'it'], true) || ($user->acc_username === 'superadminrdw');

        if (!$isHrOrFinOrAdmin) {
            if (!app(\App\Services\Auth\DataScopeService::class)->canAccessUnit($user, (int) $srq->srq_unt_id)) {
                abort(403, 'Unauthorized. Salary requisition is outside your unit scope.');
            }
        }

        // Resolve linked Salary Order (Audit.bas / hr_salreqs_u.bas:109)
        $order = DB::table('fin.salorders')->where('sor_srq_id', $srqId)->first();
        if (!$order) {
            return back()->with('error', "No linked Salary Order exists for Requisition #{$srqId}. Un-ordered requisitions should be cancelled or amended directly.");
        }

        $reason = $request->input('rev_reason') ?: $request->input('reason');

        $revision = $revisionService->createDataRevision(
            revObject: 'Salary Order',
            objectId: $order->sor_id,
            unitId: (int) $srq->srq_unt_id,
            revType: \App\Enums\RevType::FULL_CASCADE,
            revRef: null,
            revObjectExt: null,
            intUnitId: (int) ($user->acc_unt_id ?? $srq->srq_unt_id),
            revReason: $reason
        );

        return redirect()->route('admin.reversals.show', $revision->rev_id)
            ->with('success', "Data revision draft #{$revision->rev_id} for Salary Order #{$order->sor_id} (Requisition #{$srqId}) created successfully.");
    }


    /**
     * 14. Manual Salary Override POST/PATCH endpoint (Draft Orders only).
     */
    public function updateSalary(Request $request, int $sorId)
    {
        $order = FinSalOrder::findOrFail($sorId);
        $this->authorize('override', $order);
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'sor_salary' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $order = $this->salaryService->adjustOrderSalary($sorId, (float) $request->input('sor_salary'), $user);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'     => true,
                'sor_id'      => $order->sor_id,
                'sor_salary'  => $order->sor_salary,
                'sor_remarks' => $order->sor_remarks,
                'message'     => "Salary for order #{$sorId} updated to PKR " . number_format($order->sor_salary) . ".",
            ]);
        }

        return redirect()->route('divhr.salary.orders.show', $sorId)
            ->with('success', "Salary for order #{$sorId} updated to PKR " . number_format($order->sor_salary) . ".");
    }

    /**
     * 12. Commitment Verification View (Read-Only Audit).
     * Surfaces VerifySalaryCommitmentsCommand output comparing Approved orders against fin.commitments.
     */
    public function verifyCommitments(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $month = $request->query('month');
        $audit = $this->salaryService->getCommitmentVerifications($month);

        return view('hr.salary.commitments.verify', compact('audit', 'month'));
    }

    /**
     * Bank Batch Payment Scroll (Legacy fin_salorders_u_doc2).
     * Grouped by effhed_code and division with Meezan Bank Accounts vs Cheque totals.
     */
    public function bankPaymentScroll(Request $request)
    {
        $this->authorize('viewAny', FinSalOrder::class);
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (strtolower(trim((string) ($user->acc_untarea ?? ''))) === 'hr') {
            abort(403, 'Unauthorized. HR does not have access to Bank Payment Scrolls.');
        }

        [$lower, $upper] = $this->getUserBounds($user);

        // Month handling: if not specified, default to latest available order month
        $month = $request->query('month');
        if (empty($month)) {
            $latestMonth = DB::table('fin.salorders')->max('sor_month');
            $month = $latestMonth ? Carbon::parse($latestMonth)->format('Y-m') : now()->format('Y-m');
        }

        $monthDate = Carbon::parse($month)->endOfMonth()->toDateString();
        $status = $request->query('status', 'All');

        $latestContractsSub = DB::table('hr.contracts')
            ->select('ctr_num', DB::raw('MAX(ctr_id) as max_ctr_id'))
            ->groupBy('ctr_num');

        $query = DB::table('fin.salorders as sor')
            ->join('cen.heads as effhed', 'effhed.hed_id', '=', 'sor.sor_effhed_id')
            ->leftJoin('cen.units as effunt', 'effunt.unt_id', '=', 'effhed.hed_unt_id')
            ->leftJoin('cen.heads as hed', 'hed.hed_id', '=', 'sor.sor_hed_id')
            ->leftJoin('cen.units as unt', 'unt.unt_id', '=', 'sor.sor_unt_id')
            ->leftJoin('hr.emps as emp', 'emp.emp_id', '=', 'sor.sor_emp_id')
            ->leftJoinSub($latestContractsSub, 'lc', function($join) {
                $join->on('lc.ctr_num', '=', 'sor.sor_emp_id');
            })
            ->leftJoin('hr.contracts as ctr', 'ctr.ctr_id', '=', 'lc.max_ctr_id')
            ->whereBetween('sor.sor_unt_id', [$lower, $upper])
            ->where('sor.sor_month', $monthDate);

        if ($status === 'Draft') {
            $query->where('sor.sor_status', 'Draft');
        } elseif (in_array($status, ['Open', 'Approved'], true)) {
            $query->whereIn('sor.sor_status', ['Open', 'Approved', 'In Process']);
        } elseif (in_array($status, ['Closed', 'Fulfilled'], true)) {
            $query->whereIn('sor.sor_status', ['Closed', 'Fulfilled']);
        }

        $rows = $query->select(
            'sor.*',
            'effhed.hed_code as effhed_code',
            'effhed.hed_name as effhed_name',
            'effunt.unt_namesh as effunt_namesh',
            'effunt.unt_name as effunt_name',
            'hed.hed_code as hed_code',
            'hed.hed_name as hed_name',
            'unt.unt_namesh as unt_namesh',
            'unt.unt_name as unt_name',
            'emp.emp_cnic',
            'emp.emp_joindt',
            'emp.emp_rank',
            'emp.emp_title',
            'ctr.ctr_jobtitle',
            'ctr.ctr_grade',
            'ctr.ctr_startdt',
            'ctr.ctr_enddt'
        )
        ->orderBy('effhed.hed_code')
        ->orderBy('sor.sor_id')
        ->get();

        // Group rows by effhed_id (matching GroupHeader0 of legacy report)
        $grouped = $rows->groupBy('sor_effhed_id')->map(function ($groupRows) {
            $first = $groupRows->first();
            $effhedCode = $first->effhed_code ?? 'N/A';
            $effuntNamesh = $first->effunt_namesh ?? 'Central';
            $title = "To be paid From {$effhedCode} account ({$effuntNamesh} Division)";

            return [
                'effhed_id'     => $first->sor_effhed_id,
                'effhed_code'   => $effhedCode,
                'effunt_namesh' => $effuntNamesh,
                'header_title'  => $title,
                'orders'        => $groupRows,
                'person_count'  => $groupRows->count(),
                'total_salary'  => $groupRows->sum('sor_salary'),
            ];
        });

        $totalPersons = $rows->count();
        $grandTotalSalary = $rows->sum('sor_salary');

        // Available months for quick filtering
        $availableMonths = DB::table('fin.salorders')
            ->distinct()
            ->orderBy('sor_month', 'desc')
            ->pluck('sor_month')
            ->map(fn($m) => Carbon::parse($m)->format('Y-m'))
            ->unique();

        return view('hr.salary.orders.bank_scroll', compact(
            'grouped',
            'rows',
            'month',
            'monthDate',
            'status',
            'totalPersons',
            'grandTotalSalary',
            'availableMonths'
        ));
    }

    /**
     * 13. Printable Salary Slip View (M/S MTSS PAY SLIP).
     */
    public function slip(Request $request, int $sorId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $slipData = $this->salaryService->getSalarySlipData($sorId, $user);

        return view('hr.salary.slip', $slipData);
    }

    /**
     * 14. Update srq_remarks2 (Additional Remarks) for Salary Requisition.
     */
    public function updateRemarks2(Request $request, int $srqId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $req = HrSalReq::findOrFail($srqId);
        [$lower, $upper] = $this->getUserBounds($user);

        if ($req->srq_unt_id < $lower || $req->srq_unt_id > $upper) {
            return response()->json(['error' => 'Unauthorized unit access.'], 403);
        }

        $req->srq_remarks2 = $request->input('remarks2');
        $req->save();

        return response()->json(['success' => true, 'remarks2' => $req->srq_remarks2]);
    }

    /**
     * 15. Update sor_remarks2 (Additional Remarks) for Salary Order.
     */
    public function updateOrderRemarks2(Request $request, int $sorId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $order = FinSalOrder::findOrFail($sorId);
        [$lower, $upper] = $this->getUserBounds($user);

        if ($order->sor_unt_id < $lower || $order->sor_unt_id > $upper) {
            return response()->json(['error' => 'Unauthorized unit access.'], 403);
        }

        $order->sor_remarks2 = $request->input('remarks2');
        $order->save();

        return response()->json(['success' => true, 'remarks2' => $order->sor_remarks2]);
    }

    /**
     * 16. Print Contract Employees Salary Requisitions sheet (Legacy Parity).
     * Grouped by Project Head with subtotals.
     */
    public function requisitionsPrintSheet(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $month = $request->query('month');
        if (empty($month)) {
            $latestMonth = DB::table('hr.salreqs')->max('srq_month');
            $month = $latestMonth ? Carbon::parse($latestMonth)->format('Y-m') : now()->format('Y-m');
        }

        $status = $request->query('status');

        [$lower, $upper] = $this->getUserBounds($user);

        $query = HrSalReq::whereBetween('srq_unt_id', [$lower, $upper])
            ->leftJoin('hr.emps as e', 'e.emp_id', '=', 'hr.salreqs.srq_emp_id')
            ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'hr.salreqs.srq_effhed_id')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'hr.salreqs.srq_unt_id')
            ->select(
                'hr.salreqs.*',
                'e.emp_name',
                'e.emp_cnic',
                'e.emp_rank',
                'e.emp_title',
                'h.hed_code as eff_hed_code',
                'h.hed_name as eff_hed_name',
                'u.unt_name',
                'u.unt_namesh'
            );

        if (!empty($month)) {
            $monthDate = Carbon::parse($month)->endOfMonth()->toDateString();
            $query->where('srq_month', $monthDate);
        }

        if (!empty($status)) {
            if (in_array($status, ['Open', 'In Process'], true)) {
                $query->whereIn('srq_status', ['In Process', 'Open']);
            } elseif ($status === 'Closed') {
                $query->whereIn('srq_status', ['Fulfilled', 'Cancelled']);
            } elseif (in_array($status, ['Draft', 'In Process', 'Fulfilled', 'Cancelled'], true)) {
                $query->where('srq_status', $status);
            }
        } else {
            $query->whereIn('srq_status', ['In Process', 'Open']);
        }

        $requisitions = $query->orderBy('h.hed_code')->orderBy('hr.salreqs.srq_id')->get();

        $divisionName = $requisitions->first()?->unt_name ?? ($user->acc_untname ?? 'Division');
        $displayMonth = !empty($month) ? Carbon::parse($month)->format('M y') : now()->format('M y');
        $fullMonth = !empty($month) ? Carbon::parse($month)->format('F Y') : now()->format('F Y');

        // Group by effective project head code
        $grouped = $requisitions->groupBy(function ($r) {
            return $r->eff_hed_code ?: 'General';
        });

        $totalCount = $requisitions->count();
        $totalPayable = (float)$requisitions->sum('srq_salary');

        return view('hr.salary.requisitions.print_sheet', compact(
            'requisitions',
            'grouped',
            'divisionName',
            'displayMonth',
            'fullMonth',
            'totalCount',
            'totalPayable'
        ));
    }

    /**
     * Dedicated Order Pay Slip endpoint for /divhr/salary/orders/{sor_id}/slip.
     */
    public function orderPaySlip(Request $request, int $sor_id)
    {
        return $this->payslip($request, 'order', $sor_id);
    }

    /**
     * 17. Employee Pay Slip View (Single employee printable pay slip).
     */
    public function payslip(Request $request, string $type, int $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($type === 'order') {
            $record = FinSalOrder::findOrFail($id);
            $empId = $record->sor_emp_id;
            $month = $record->sor_month;
            $salary = (float)$record->sor_salary;
            $ctrSalary = (float)($record->sor_ctrsalary ?: $salary);
            $gross = (float)($record->sor_grosalary ?: $salary);
            $net = (float)($record->sor_netsalary ?: $salary);
            $arrears = (float)($record->sor_arrears ?? 0);
            $dues = (float)($record->sor_dues ?? 0);
            $tax = (float)($record->sor_withheld ?? 0);
            $unpaid = (float)($record->sor_underwork ?? 0);
            $bankAcc = $record->sor_bnkaccdetail;
            $bankTitle = $record->sor_bnkacctitle;
            $untId = $record->sor_unt_id;
            $remarks = $record->sor_remarks;
        } else {
            $record = HrSalReq::findOrFail($id);
            $empId = $record->srq_emp_id;
            $month = $record->srq_month;
            $salary = (float)$record->srq_salary;
            $ctrSalary = (float)($record->srq_ctrsalary ?: $salary);
            $gross = (float)($record->srq_grosalary ?: $salary);
            $net = (float)($record->srq_netsalary ?: $salary);
            $arrears = (float)($record->srq_arrears ?? 0);
            $dues = (float)($record->srq_dues ?? 0);
            $tax = (float)($record->srq_withheld ?? 0);
            $unpaid = (float)($record->srq_unpaiddays ?? ($record->srq_underwork ?? 0));
            $bankAcc = $record->srq_bnkaccdetail;
            $bankTitle = $record->srq_bnkacctitle;
            $untId = $record->srq_unt_id;
            $remarks = $record->srq_remarks;
        }

        $emp = DB::table('hr.emps as e')
            ->leftJoin('hr.empsextb as eb', 'eb.empextb_emp_id', '=', 'e.emp_id')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'e.emp_unt_id')
            ->where('e.emp_id', $empId)
            ->select('e.*', 'eb.emp_nokname', 'u.unt_name')
            ->first();

        $contract = DB::table('hr.contracts')
            ->where('ctr_num', $empId)
            ->orderBy('ctr_startdt', 'desc')
            ->first();

        $baseSalary = $ctrSalary > 0 ? $ctrSalary : max(0, $gross - $arrears);
        $totalDeductions = $tax + $unpaid + $dues;

        return view('hr.salary.payslip', compact(
            'record',
            'emp',
            'contract',
            'month',
            'baseSalary',
            'arrears',
            'gross',
            'tax',
            'unpaid',
            'dues',
            'totalDeductions',
            'net',
            'salary',
            'bankAcc',
            'bankTitle',
            'remarks'
        ));
    }

    /**
     * 18. Consolidated Salary Orders Sheet for Finance (Legacy Parity).
     */
    public function ordersPrintSheet(Request $request)
    {
        $this->authorize('viewAny', FinSalOrder::class);
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $month = $request->query('month');
        // Do NOT default to max month if not provided - if empty, include all active orders matching status (parity with screen)
        $status = $request->query('status', 'Open');

        [$lower, $upper] = $this->getUserBounds($user);

        $query = FinSalOrder::where(function ($q) use ($lower, $upper) {
                $q->whereBetween('sor_unt_id', [$lower, $upper])
                  ->orWhereBetween('sor_effunt_id', [$lower, $upper]);
            })
            ->leftJoin('hr.emps as e', 'e.emp_id', '=', 'fin.salorders.sor_emp_id')
            ->leftJoin('cen.heads as h', 'h.hed_id', '=', 'fin.salorders.sor_effhed_id')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'fin.salorders.sor_unt_id')
            ->select(
                'fin.salorders.*',
                'e.emp_name',
                'e.emp_cnic',
                'e.emp_rank',
                'e.emp_title',
                'h.hed_code as eff_hed_code',
                'h.hed_name as eff_hed_name',
                'u.unt_name',
                'u.unt_namesh'
            );

        if (!empty($month)) {
            $monthDate = Carbon::parse($month)->endOfMonth()->toDateString();
            $query->where('sor_month', $monthDate);
        }

        if (!empty($status)) {
            if (in_array($status, ['Open', 'Approved'], true)) {
                $query->whereIn('sor_status', ['Approved', 'Open']);
            } elseif ($status === 'Closed') {
                $query->whereIn('sor_status', ['Fulfilled', 'Cancelled']);
            } elseif (in_array($status, ['Draft', 'Approved', 'Fulfilled', 'Cancelled'], true)) {
                $query->where('sor_status', $status);
            }
        } else {
            $query->whereIn('sor_status', ['Approved', 'Open']);
        }

        $orders = $query->orderBy('sor_id')->get();

        // Attach active contract to each order for grade, job title, and contract dates
        $empIds = $orders->pluck('sor_emp_id')->unique()->all();
        $contracts = DB::table('hr.contracts')
            ->whereIn('ctr_num', $empIds)
            ->orderBy('ctr_startdt', 'desc')
            ->get()
            ->groupBy('ctr_num');

        foreach ($orders as $o) {
            $empCtrs = $contracts->get($o->sor_emp_id);
            $o->contract = $empCtrs ? $empCtrs->first() : null;
        }

        $totalCount = $orders->count();
        $totalPayable = (float)$orders->sum('sor_salary');
        if (!empty($month)) {
            $displayMonth = strtoupper(Carbon::parse($month)->format('F Y'));
        } else {
            $distinctMonths = $orders->pluck('sor_month')->map(fn($m) => Carbon::parse($m)->format('F Y'))->unique()->values();
            if ($distinctMonths->count() === 1) {
                $displayMonth = strtoupper($distinctMonths->first());
            } elseif ($distinctMonths->count() > 1) {
                $displayMonth = strtoupper($distinctMonths->last());
            } else {
                $displayMonth = strtoupper(now()->format('F Y'));
            }
        }

        return view('hr.salary.orders.print_sheet', compact(
            'orders',
            'totalCount',
            'totalPayable',
            'displayMonth'
        ));
    }

    /**
     * Delete a Draft Salary Requisition.
     * When deleted, the employee is immediately unblocked and can be regenerated.
     */
    public function destroyRequisition(Request $request, int $srqId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $req = HrSalReq::findOrFail($srqId);
        [$lower, $upper] = $this->getUserBounds($user);

        if ($req->srq_unt_id < $lower || $req->srq_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
        }

        if ($req->srq_status !== 'Draft') {
            return back()->with('error', "Cannot delete requisition #{$srqId} with status '{$req->srq_status}'. Only 'Draft' requisitions can be deleted.");
        }

        DB::transaction(function () use ($req) {
            $srqId = $req->srq_id;

            // 1. Delete linked draft salary orders if any
            FinSalOrder::where(function ($q) use ($srqId) {
                $q->where('sor_srq_id', $srqId)
                  ->orWhere('sor_parent', $srqId);
            })->where('sor_status', 'Draft')->delete();

            // 2. Delete child splits if this is a parent split record
            HrSalReq::where('srq_parent', $srqId)->delete();

            // 3. Delete the requisition itself
            $req->delete();
        });

        return back()->with('success', "Draft salary requisition #{$srqId} deleted successfully. You can now re-generate salary for this employee.");
    }

    /**
     * Export Salary Orders sheet as editable Word Document (.doc).
     */
    public function ordersExportWord(Request $request)
    {
        $view = $this->ordersPrintSheet($request);
        if ($view instanceof RedirectResponse) {
            return $view;
        }

        $html = $view->render();
        $month = $request->query('month', now()->format('Y-m'));
        $fileName = 'Salary_Orders_' . str_replace('-', '_', $month) . '.doc';

        return response($html)
            ->header('Content-Type', 'application/msword; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}
