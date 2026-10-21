<?php

namespace App\Http\Controllers;

use App\Models\HrSalReq;
use App\Models\FinSalOrder;
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

        return view('hr.salary.requisitions.index', compact('requisitions', 'month', 'status'));
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

        // Strictly enforce current month and user division scope
        $month = now()->format('Y-m');
        $unitScope = (int) $user->acc_unt_id;

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

        $month = now()->format('Y-m');
        $empIds = $request->input('emp_ids');

        // Pre-flight duplicate check: surface exact conflicting employee IDs and period
        $preview = $this->salaryService->previewSalary($month, (int) $user->acc_unt_id, $user);
        $excludedMap = collect($preview['excluded'])->keyBy(fn($x) => $x['employee']->emp_id);

        $conflicts = [];
        foreach ($empIds as $eid) {
            if ($excludedMap->has($eid) && $excludedMap->get($eid)['reason'] === 'Already Generated') {
                $cand = $excludedMap->get($eid);
                $conflicts[] = [
                    'emp_id' => $eid,
                    'name'   => $cand['employee']->emp_name,
                    'period' => $preview['month'],
                    'reason' => 'Requisition already generated in Draft, In Process, or Fulfilled status',
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

        // Pre-Release Budget Check (Legacy AccountChecks.bas AnyObjectionsOnExp)
        $groupReqs = HrSalReq::where('srq_id', $srqId)
            ->orWhere('srq_parent', $srqId)
            ->get();

        $objections = [];
        foreach ($groupReqs as $gr) {
            $obj = $this->salaryService->checkObjectionsOnExp(
                (int)$gr->srq_effhed_id,
                $gr->srq_hed_id ? (int)$gr->srq_hed_id : null,
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

        $this->salaryService->releaseRequisitions($srqId);
        $orders = $this->salaryService->createSalaryOrders($srqId);

        return back()->with('success', "Salary requisition #{$srqId} released to In Process and " . count($orders) . " Draft Salary Order(s) created for Finance.");
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

        $orders = $this->salaryService->getOrders($user, $month, $status);

        return view('hr.salary.orders.index', compact('orders', 'month', 'status'));
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
            $msg = 'This requisition cannot be cancelled directly. Please cancel the parent requisition.';
            if ($request->expectsJson()) {
                return response()->json(['error' => $msg, 'message' => $msg], 422);
            }
            abort(422, $msg);
        }

        if ($order->sor_status === 'Cancelled') {
            return back()->with('error', "Salary order #{$sorId} is already cancelled.");
        }

        Log::info("Salary order #{$sorId} cancelled by {$user->acc_username}. Reason: {$request->input('reason')}");

        try {
            $this->salaryService->cancelOrder($sorId);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
            }
            throw $e;
        }

        return redirect()->route('divhr.salary.orders.index')
            ->with('success', "Salary order #{$sorId} and associated commitments cancelled successfully.");
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
}
