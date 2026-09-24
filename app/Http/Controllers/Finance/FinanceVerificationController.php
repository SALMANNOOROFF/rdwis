<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\CenHead;
use App\Services\FinanceVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Exception;

class FinanceVerificationController extends Controller
{
    public function __construct(
        protected FinanceVerificationService $verificationService
    ) {}

    /**
     * Enforce strict Finance user access.
     */
    protected function authorizeFinance(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isSuperAdmin = ($user->acc_username === 'superadminrdw') || session('impersonated_by_god');

        if ($area !== 'fin' && !$isSuperAdmin) {
            abort(403, 'Access denied. Only Finance department users are authorized to access this section.');
        }
    }

    // =========================================================================
    // PART A: SALARY VERIFICATION
    // =========================================================================

    /**
     * List view of contracts for salary verification.
     * Default tab: unverified (Not Verified contracts).
     * Toggle button swaps to verified contracts ordered by cvf_dtg DESC.
     */
    public function salaryVerificationIndex(Request $request): View
    {
        $this->authorizeFinance();

        $tab = $request->query('tab', 'unverified');
        $isVerifiedTab = ($tab === 'verified');

        $contracts = $this->verificationService->getContractsVerificationList($isVerifiedTab);

        return view('finance.verification.salary_verification', compact('contracts', 'isVerifiedTab', 'tab'));
    }

    /**
     * Verify a contract's salary.
     */
    public function verifyContract(Request $request, int $ctrId): JsonResponse|RedirectResponse
    {
        $this->authorizeFinance();

        try {
            $this->verificationService->verifyContract($ctrId);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contract salary successfully verified.',
                ]);
            }

            return redirect()->back()->with('success', 'Contract salary successfully verified.');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // =========================================================================
    // PART B: SALARY HEADS ASSIGNMENT
    // =========================================================================

    /**
     * List view of employees scoped to current-month contract plans.
     * Default tab: Open.
     * Toggle button swaps to Closed.
     */
    public function salaryHeadsIndex(Request $request): View
    {
        $this->authorizeFinance();

        $tab = strtolower($request->query('tab', 'open'));
        $status = ($tab === 'closed') ? 'Closed' : 'Open';

        $records = $this->verificationService->getSalaryHeadsList($status);

        // Heads dropdown for detail panel assignment
        $heads = CenHead::orderBy('hed_code')->orderBy('hed_name')->get();

        // Check applicability for each record
        foreach ($records as $record) {
            $record->is_applicable = $this->verificationService->isHeadAssignmentApplicable($record->emp_unt_id);
        }

        return view('finance.verification.salary_heads', compact('records', 'status', 'tab', 'heads'));
    }

    /**
     * Save salary head assignment for an employee.
     */
    public function updateSalaryHead(Request $request, string $empId): JsonResponse|RedirectResponse
    {
        $this->authorizeFinance();

        $request->validate([
            'eeh_emphed_id' => 'nullable|integer',
            'eeh_remarks'   => 'nullable|string|max:255',
            'eeh_status'    => 'nullable|string|in:Open,Closed',
        ]);

        try {
            $headId = $request->input('eeh_emphed_id') ? (int) $request->input('eeh_emphed_id') : null;
            $remarks = $request->input('eeh_remarks');
            $status = $request->input('eeh_status');

            $this->verificationService->updateSalaryHead($empId, $headId, $remarks, $status);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Salary head updated successfully.',
                ]);
            }

            return redirect()->back()->with('success', 'Salary head updated successfully.');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Set salary head status to Closed (direct plain field save, no Data Revision).
     */
    public function closeSalaryHead(Request $request, string $empId): JsonResponse|RedirectResponse
    {
        $this->authorizeFinance();

        try {
            $this->verificationService->closeSalaryHead($empId);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Salary head status successfully set to Closed.',
                ]);
            }

            return redirect()->back()->with('success', 'Salary head status successfully set to Closed.');
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reverse action for Closed salary head.
     * Calls DataRevisionService to create a Draft aud.revs entry,
     * then routes to admin.reversals.show.
     */
    public function reverseSalaryHead(Request $request, string $empId): JsonResponse|RedirectResponse
    {
        $this->authorizeFinance();

        try {
            $reason = $request->input('rev_reason');
            $revision = $this->verificationService->reverseSalaryHead($empId, Auth::user(), $reason);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => "Data revision draft #{$revision->rev_id} created for Salary Head of employee {$empId}.",
                    'redirect_url' => route('admin.reversals.show', $revision->rev_id),
                ]);
            }

            return redirect()->route('admin.reversals.show', $revision->rev_id)
                ->with('success', "Data revision draft #{$revision->rev_id} for Salary Head of employee {$empId} created successfully.");
        } catch (Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
