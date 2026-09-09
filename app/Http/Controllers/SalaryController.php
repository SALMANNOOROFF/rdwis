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
        $isMultiple = ($user->acc_access ?? '') === 'multiple' || strtolower(trim((string)($user->acc_untarea ?? ''))) === 'fin';
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

        $month = $request->query('month', now()->format('Y-m'));
        $unitId = $request->query('unit_id');

        [$lower, $upper] = $this->getUserBounds($user);

        $units = DB::table('cen.units')
            ->whereBetween('unt_id', [$lower, $upper])
            ->orderBy('unt_namesh')
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

        $request->validate([
            'month'   => ['required', 'date_format:Y-m'],
            'unit_id' => ['nullable', 'integer'],
        ]);

        $month = $request->input('month');
        $unitScope = $request->filled('unit_id') ? (int) $request->input('unit_id') : null;

        $preview = $this->salaryService->previewSalary($month, $unitScope, $user);

        return response()->json($preview);
    }

    /**
     * 4. Generate Requisitions POST endpoint.
     * Catches duplicate guard and surfaces exact conflicting employees and periods.
     */
    public function generateRequisitions(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'month'     => ['required', 'date_format:Y-m'],
            'emp_ids'   => ['required', 'array', 'min:1'],
            'emp_ids.*' => ['required', 'string'],
        ]);

        $month = $request->input('month');
        $empIds = $request->input('emp_ids');

        // Pre-flight duplicate check: surface exact conflicting employee IDs and period
        $preview = $this->salaryService->previewSalary($month, null, $user);
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

        if (strtolower(trim((string) ($user->acc_untarea ?? ''))) === 'hr') {
            abort(403, 'Unauthorized. HR does not have access to Salary Orders. Salary Orders are managed by Finance.');
        }

        $order = $this->salaryService->getOrderDetail($sorId, $user);
        if (!$order) {
            abort(404, "Salary order #{$sorId} not found or unauthorized.");
        }

        return view('hr.salary.orders.show', compact('order'));
    }

    /**
     * 10. Approve Salary Order POST endpoint (Draft -> Approved, creates negative liability commitment).
     */
    public function approveOrder(Request $request, int $sorId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (($user->acc_auth ?? '') !== 'approver' || strtolower(trim((string) ($user->acc_untarea ?? ''))) !== 'fin') {
            abort(403, 'Finance approver authorization required to approve salary orders.');
        }

        $order = FinSalOrder::findOrFail($sorId);
        if ($order->sor_status !== 'Draft') {
            return back()->with('error', "Cannot approve order #{$sorId} with status '{$order->sor_status}'. Only 'Draft' orders can be approved.");
        }

        $this->salaryService->approveSalaryOrders($sorId, $user);

        return redirect()->route('divhr.salary.orders.show', $sorId)
            ->with('success', "Salary order #{$sorId} approved and negative liability commitment created successfully.");
    }

    /**
     * 11. Cancel Salary Order POST endpoint (High-friction cancellation).
     */
    public function cancelOrder(Request $request, int $sorId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $order = FinSalOrder::findOrFail($sorId);
        [$lower, $upper] = $this->getUserBounds($user);

        if ($order->sor_unt_id < $lower || $order->sor_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
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
     * 14. Manual Salary Override POST/PATCH endpoint (Draft Orders only).
     */
    public function updateSalary(Request $request, int $sorId)
    {
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
