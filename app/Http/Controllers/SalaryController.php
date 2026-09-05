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
        $status = $request->query('status');

        $requisitions = $this->salaryService->getRequisitions($user, $month, $status);

        return view('hr.salary.requisitions.index', compact('requisitions', 'month', 'status'));
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

        $lower = $user->acc_lowers ?? 100000;
        $upper = $user->acc_uppers ?? 999999;

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
        $lower = $user->acc_lowers ?? 100000;
        $upper = $user->acc_uppers ?? 999999;

        if ($req->srq_unt_id < $lower || $req->srq_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
        }

        if ($req->srq_status !== 'Draft') {
            return back()->with('error', "Cannot release requisition #{$srqId} with status '{$req->srq_status}'. Only 'Draft' requisitions can be released.");
        }

        $this->salaryService->releaseRequisitions($srqId);

        return back()->with('success', "Salary requisition #{$srqId} released to 'In Process' successfully.");
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
        $lower = $user->acc_lowers ?? 100000;
        $upper = $user->acc_uppers ?? 999999;

        if ($req->srq_unt_id < $lower || $req->srq_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
        }

        if ($req->srq_status === 'Cancelled' || $req->srq_status === 'Fulfilled') {
            return back()->with('error', "Requisition #{$srqId} is already {$req->srq_status} and cannot be cancelled.");
        }

        Log::info("Requisition #{$srqId} cancelled by {$user->acc_username}. Reason: {$request->input('reason')}");

        $this->salaryService->cancelRequisition($srqId);

        return back()->with('success', "Salary requisition #{$srqId} cancelled successfully.");
    }

    /**
     * 7. Salary Orders Dashboard.
     * Filterable by exact sor_status (Draft, Approved, Cancelled) and month.
     */
    public function ordersIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $month = $request->query('month');
        $status = $request->query('status');

        $orders = $this->salaryService->getOrders($user, $month, $status);

        return view('hr.salary.orders.index', compact('orders', 'month', 'status'));
    }

    /**
     * 8. Create Orders from Requisition Group (In Process -> Draft Orders).
     */
    public function createOrders(Request $request, int $srqId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $req = HrSalReq::findOrFail($srqId);
        $lower = $user->acc_lowers ?? 100000;
        $upper = $user->acc_uppers ?? 999999;

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

        if (($user->acc_auth ?? '') !== 'approver' && !in_array(strtolower($user->acc_untarea ?? ''), ['hr', 'nrdi', 'fin', 'it'], true)) {
            abort(403, 'Approver authorization required to approve salary orders.');
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
        $lower = $user->acc_lowers ?? 100000;
        $upper = $user->acc_uppers ?? 999999;

        if ($order->sor_unt_id < $lower || $order->sor_unt_id > $upper) {
            abort(403, 'Unauthorized unit access.');
        }

        if ($order->sor_status === 'Cancelled') {
            return back()->with('error', "Salary order #{$sorId} is already cancelled.");
        }

        Log::info("Salary order #{$sorId} cancelled by {$user->acc_username}. Reason: {$request->input('reason')}");

        $this->salaryService->cancelOrder($sorId);

        return redirect()->route('divhr.salary.orders.index')
            ->with('success', "Salary order #{$sorId} and associated commitments cancelled successfully.");
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
}
