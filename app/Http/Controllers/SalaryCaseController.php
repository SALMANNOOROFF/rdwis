<?php

namespace App\Http\Controllers;

use App\Models\SalOrderCase;
use App\Models\CenAccount;
use App\Services\SalaryCaseService;
use App\Services\Auth\UserAccessContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Throwable;

class SalaryCaseController extends Controller
{
    public function __construct(
        protected SalaryCaseService $salaryCaseService
    ) {}

    /**
     * Display hub / listing of Salary Order Verification Cases with Pending Action / Open / Closed tabs.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $userAccId = (int) ($user->acc_id ?? 0);
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $tab = $request->query('tab', 'pending');
        $search = trim((string) $request->query('search', ''));
        $monthFilter = $request->query('month');

        $context = UserAccessContext::forUser($user);
        $isFinance = (method_exists($user, 'isFinance') && $user->isFinance()) || in_array($userArea, ['fin', 'finance'], true) || ((int)($user->acc_unt_id ?? 0) === 800000);
        $isMd = (method_exists($user, 'isMd') && $user->isMd()) || $context->isMd() || $userAccId === 100 || $userArea === 'rdw';
        $isDdg = $context->isDdg() || $userAccId === 108 || $userArea === 'hqs';
        $isDg = (method_exists($user, 'isDg') && $user->isDg()) || $userAccId === 107 || $userArea === 'nrdi';
        $isDlog = (method_exists($user, 'isDlog') && $user->isDlog()) || in_array($userArea, ['log', 'dlog'], true) || $userAccId === 106;

        $baseQuery = SalOrderCase::with(['creator', 'currentHolder', 'decisions']);

        if ($monthFilter) {
            $baseQuery->where('soc_month', $monthFilter);
        }

        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('soc_title', 'ilike', "%{$search}%")
                  ->orWhere('soc_minute_no', 'ilike', "%{$search}%")
                  ->orWhere('soc_month', 'ilike', "%{$search}%")
                  ->orWhere('soc_current_holder', 'ilike', "%{$search}%");
            });
        }

        $allCases = $baseQuery->orderBy('soc_id', 'desc')->get();

        // Classify each case for THIS user
        $isPendingForUser = function(SalOrderCase $c) use ($userAccId, $isFinance, $isMd, $isDdg, $isDg, $isDlog) {
            $status = strtolower(trim((string)$c->soc_status));
            $stage = strtoupper(trim((string)$c->soc_current_stage));

            // Finalized cases (approved, closed, cancelled) are never pending
            if (in_array($status, ['approved', 'closed', 'cancelled']) || $stage === 'CLOSED') {
                return false;
            }

            // Any active case at Finance stage or in draft / pending action / returned belongs to Finance
            if ($isFinance && ($stage === 'FINANCE' || in_array($status, ['draft', 'pending action', 'returned']))) {
                return true;
            }

            // If specifically assigned to this user
            if (!empty($c->soc_current_acc_id) && (int)$c->soc_current_acc_id === $userAccId) {
                return true;
            }

            // Stage-based matching
            if ($stage === 'MD') {
                return $isMd;
            } elseif ($stage === 'DDG') {
                return $isDdg;
            } elseif ($stage === 'DG') {
                return $isDg;
            } elseif ($stage === 'DLOG') {
                return $isDlog;
            }

            return false;
        };

        $pendingCases = $allCases->filter(fn(SalOrderCase $c) => $isPendingForUser($c))->values();
        $openCases = $allCases->filter(fn(SalOrderCase $c) => !$isPendingForUser($c) && !in_array(strtolower(trim($c->soc_status)), ['approved', 'closed', 'cancelled']) && in_array(strtolower($c->soc_status), ['draft', 'under review', 'under approval', 'open']))->values();
        $closedCases = $allCases->filter(fn(SalOrderCase $c) => in_array(strtolower(trim($c->soc_status)), ['approved', 'closed', 'cancelled']) || strtoupper(trim((string)$c->soc_current_stage)) === 'CLOSED')->values();

        $displayCases = match($tab) {
            'open' => $openCases,
            'closed' => $closedCases,
            default => $pendingCases,
        };

        $pendingCount = $pendingCases->count();
        $openCount = $openCases->count();
        $closedCount = $closedCases->count();

        // Find latest active month for salary orders
        $maxMonthDate = DB::table('fin.salorders')->max('sor_month');
        $latestMonth = $maxMonthDate ? Carbon::parse($maxMonthDate)->format('Y-m') : now()->format('Y-m');

        // Check all unassigned active orders (Draft, Approved, Open) up to latestMonth
        $unassignedOrders = DB::table('fin.salorders')
            ->whereIn('sor_status', ['Draft', 'Approved', 'Open'])
            ->whereNull('sor_soc_id')
            ->where(function($q) use ($latestMonth) {
                $q->whereRaw("to_char(sor_month, 'YYYY-MM') <= ?", [$latestMonth]);
            })
            ->get();

        // Also check if there is an active draft/pending case for latestMonth whose orders are linked
        $activeCase = SalOrderCase::where('soc_month', $latestMonth)
            ->whereNotIn('soc_status', ['Cancelled', 'Approved', 'Closed'])
            ->latest('soc_id')
            ->first();

        $activeOrders = collect();
        if ($activeCase) {
            $activeOrders = DB::table('fin.salorders')
                ->where('sor_soc_id', $activeCase->soc_id)
                ->whereNotIn('sor_status', ['Cancelled', 'Fulfilled'])
                ->get();
        }

        $combinedOrders = $unassignedOrders->concat($activeOrders)->unique('sor_id');

        $availableMonths = collect();
        if ($combinedOrders->isNotEmpty()) {
            $orderCount = $combinedOrders->count();
            $empCount = $combinedOrders->pluck('sor_emp_id')->unique()->count();
            $totalAmount = (float)$combinedOrders->sum('sor_salary');

            $availableMonths->push((object)[
                'm'               => $latestMonth,
                'cnt'             => $orderCount,
                'emp_cnt'         => $empCount,
                'total'           => $totalAmount,
                'has_active_case' => !empty($activeCase),
                'case_id'         => $activeCase?->soc_id,
                'minute_no'       => $activeCase?->soc_minute_no,
            ]);
        }

        return view('fin.salary_cases.index', compact(
            'displayCases', 'tab', 'pendingCount', 'openCount', 'closedCount',
            'isFinance', 'isMd', 'isDdg', 'isDg', 'isDlog', 'availableMonths', 'monthFilter', 'search'
        ));
    }

    /**
     * Show case verification view with Left (Minute Sheet) + Right (Send Case / Decisions).
     */
    public function show($id): View
    {
        /** @var CenAccount $user */
        $user = auth()->user();
        $userAccId = (int) ($user->acc_id ?? 0);
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));

        $case = SalOrderCase::with(['decisions.account', 'creator', 'currentHolder'])
            ->findOrFail($id);

        $context = UserAccessContext::forUser($user);
        $isFinance = (method_exists($user, 'isFinance') && $user->isFinance()) || in_array($userArea, ['fin', 'finance'], true) || ((int)($user->acc_unt_id ?? 0) === 800000);
        $isMd = (method_exists($user, 'isMd') && $user->isMd()) || $context->isMd() || $userAccId === 100 || $userArea === 'rdw';
        $isDdg = $context->isDdg() || $userAccId === 108 || $userArea === 'hqs';
        $isDg = (method_exists($user, 'isDg') && $user->isDg()) || $userAccId === 107 || $userArea === 'nrdi';
        $isDlog = (method_exists($user, 'isDlog') && $user->isDlog()) || in_array($userArea, ['log', 'dlog'], true) || $userAccId === 106;
        $isCommand = $isMd || $isDdg || $isDg || $isDlog;

        // Is case currently with authenticated user
        $stage = strtoupper(trim((string)$case->soc_current_stage));
        $status = strtolower(trim((string)$case->soc_status));
        $isCaseWithMe = false;

        // Finalized cases (approved, closed, cancelled) are completely locked
        if (!in_array($status, ['approved', 'closed', 'cancelled']) && $stage !== 'CLOSED') {
            if ($isFinance && ($stage === 'FINANCE' || in_array($status, ['draft', 'pending action', 'returned']))) {
                $isCaseWithMe = true;
            } elseif (!empty($case->soc_current_acc_id)) {
                $isCaseWithMe = ((int)$case->soc_current_acc_id === $userAccId);
            } else {
                $isCaseWithMe = match($stage) {
                    'FINANCE' => $isFinance,
                    'MD' => $isMd,
                    'DDG' => $isDdg,
                    'DG' => $isDg,
                    'DLOG' => $isDlog,
                    default => false,
                };
            }
        }

        $canEdit = $isFinance && !in_array($status, ['approved', 'closed', 'cancelled']) && $stage !== 'CLOSED' && (in_array($status, ['draft', 'pending action', 'returned']) || $stage === 'FINANCE');
        $canApprove = $isCaseWithMe && ($isCommand || $isFinance);

        // If case is in Draft / editable mode, automatically sync any new/approved salary orders for this month
        if ($canEdit) {
            $case = $this->salaryCaseService->syncCaseOrders($case);
        }

        // Get table data grouped by Center, Comm, Enab, NWS, Sensors, SoSE, Sys
        $minuteTable = $this->salaryCaseService->getMinuteTableData($case);

        // Routing destinations
        $destinations = $this->salaryCaseService->getAvailableDestinations($user, $case);

        return view('fin.salary_cases.show', compact(
            'case', 'minuteTable', 'destinations', 'canEdit', 'canApprove',
            'isCaseWithMe', 'isFinance', 'isCommand', 'isMd', 'isDdg', 'isDg', 'isDlog'
        ));
    }

    /**
     * Initiate a new Salary Order Case for a given month.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        /** @var CenAccount $user */
        $user = auth()->user();

        try {
            $case = $this->salaryCaseService->initiateCase($request->input('month'), $user);
            return redirect()->route('salary_cases.show', $case->soc_id)
                ->with('success', "Salary Order Case #{$case->soc_minute_no} for {$case->soc_month} initiated successfully!");
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Failed to initiate case: ' . $e->getMessage());
        }
    }

    /**
     * Update Draft Minute details (Minute No, Enclosure, Para 2, etc.)
     */
    public function updateDraft(Request $request, $id): RedirectResponse|JsonResponse
    {
        $case = SalOrderCase::findOrFail($id);
        /** @var CenAccount $user */
        $user = auth()->user();

        try {
            $data = $request->all();

            // If rows submitted from editable table, structure and group them
            if ($request->has('table_rows') && is_array($request->input('table_rows'))) {
                $groupedTable = [];
                foreach ($request->input('table_rows') as $row) {
                    $group = trim((string)($row['division_group'] ?? 'Center'));
                    if (empty($group)) $group = 'Center';
                    $groupedTable[$group][] = [
                        'head_id'   => !empty($row['head_id']) ? (int)$row['head_id'] : 0,
                        'paid_from' => trim((string)($row['paid_from'] ?? '')),
                        'payments'  => (int)($row['payments'] ?? 0),
                        'amount'    => (float)str_replace(',', '', (string)($row['amount'] ?? 0)),
                        'remaining' => (float)str_replace(',', '', (string)($row['remaining'] ?? 0)),
                        'remarks'   => trim((string)($row['remarks'] ?? '')),
                    ];
                }
                $data['soc_table_data'] = $groupedTable;
            }

            $this->salaryCaseService->updateDraft($case, $data, $user);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Minute details updated successfully!']);
            }

            return redirect()->back()->with('success', 'Draft Minute details saved successfully.');
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Send / Forward case to next authority.
     */
    public function sendCase(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'target_destination' => 'required|string',
            'remarks' => 'required|string',
        ]);

        $case = SalOrderCase::findOrFail($id);
        /** @var CenAccount $user */
        $user = auth()->user();

        try {
            $this->salaryCaseService->sendCase($case, $user, $request->input('target_destination'), $request->input('remarks'));
            return redirect()->route('salary_cases.show', $case->soc_id)
                ->with('success', "Case forwarded successfully to destination!");
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Forwarding failed: ' . $e->getMessage());
        }
    }

    /**
     * Approve case.
     */
    public function approve(Request $request, $id): RedirectResponse
    {
        $case = SalOrderCase::findOrFail($id);
        /** @var CenAccount $user */
        $user = auth()->user();

        try {
            $this->salaryCaseService->approveCase($case, $user, $request->input('remarks'));
            return redirect()->route('salary_cases.show', $case->soc_id)
                ->with('success', "Salary Order Case approved successfully!");
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel case.
     */
    public function cancel(Request $request, $id): RedirectResponse
    {
        $case = SalOrderCase::findOrFail($id);
        /** @var CenAccount $user */
        $user = auth()->user();

        try {
            $this->salaryCaseService->cancelCase($case, $user, $request->input('remarks'));
            return redirect()->route('salary_cases.show', $case->soc_id)
                ->with('success', "Salary Order Case returned successfully. Requisitions reverted to divisions in Draft status and funds restored to Remaining balance.");
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Action failed: ' . $e->getMessage());
        }
    }

    /**
     * Printable view of the official Minute Sheet.
     */
    public function printMinute($id): View
    {
        $case = SalOrderCase::with(['decisionsAsc.account'])->findOrFail($id);
        $minuteTable = $this->salaryCaseService->getMinuteTableData($case);

        return view('fin.salary_cases.print_minute', compact('case', 'minuteTable'));
    }

    /**
     * Download Minute Sheet as editable Microsoft Word Document (.doc).
     */
    public function exportWord($id)
    {
        $case = SalOrderCase::with(['decisionsAsc.account'])->findOrFail($id);
        $minuteTable = $this->salaryCaseService->getMinuteTableData($case);

        $html = view('fin.salary_cases.print_minute', compact('case', 'minuteTable'))->render();

        $cleanMinuteNo = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)($case->soc_minute_no ?: 'Salary_Verification_' . $case->soc_month));
        $fileName = "Minute_{$cleanMinuteNo}.doc";

        return response($html)
            ->header('Content-Type', 'application/msword; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Delete a Salary Order Case and release linked salary orders.
     */
    public function destroy($id): RedirectResponse
    {
        /** @var CenAccount $user */
        $user = auth()->user();
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isFinance = (method_exists($user, 'isFinance') && $user->isFinance()) || in_array($userArea, ['fin', 'finance'], true) || ((int)($user->acc_unt_id ?? 0) === 800000);

        if (!$isFinance) {
            return redirect()->back()->with('error', 'Only Directorate of Finance can delete salary order cases.');
        }

        $case = SalOrderCase::findOrFail($id);

        if (in_array(strtolower($case->soc_status), ['approved', 'closed'])) {
            return redirect()->back()->with('error', 'Approved salary order cases cannot be deleted.');
        }

        DB::transaction(function() use ($case) {
            // Release any linked salary orders back to draft
            \App\Models\FinSalOrder::where('sor_soc_id', $case->soc_id)
                ->update(['sor_soc_id' => null]);

            // Delete decisions for this case
            \App\Models\SalOrderCaseDecision::where('scd_soc_id', $case->soc_id)->delete();

            // Delete the case
            $case->delete();
        });

        return redirect()->route('salary_cases.index')
            ->with('success', "Salary Order Verification Case #{$case->soc_minute_no} deleted successfully.");
    }

    /**
     * Re-sync case with all salary orders of that month and reset custom table.
     */
    public function syncOrders(Request $request, $id): RedirectResponse
    {
        $case = SalOrderCase::findOrFail($id);
        $this->salaryCaseService->syncCaseOrders($case, true);

        return redirect()->route('salary_cases.show', $case->soc_id)
            ->with('success', "Case successfully synchronized with all salary orders for {$case->soc_month}!");
    }
}
