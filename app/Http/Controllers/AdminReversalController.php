<?php

namespace App\Http\Controllers;

use App\Models\AudRev;
use App\Services\Auth\AreaDefinition;
use App\Services\Auth\DataScopeService;
use App\Services\Auth\PermissionRegistry;
use App\Services\Auth\RolePermissionMap;
use App\Services\Auth\UserAccessContext;
use App\Services\DataRevisionService;
use Illuminate\Http\Request;

class AdminReversalController extends Controller
{
    /**
     * Display default reversals list (Open cases).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', AudRev::class);

        $tab = strtolower(trim((string) $request->query('tab', $request->query('status', 'open'))));

        if ($tab === 'draft') {
            return $this->draft($request);
        }

        if ($tab === 'closed') {
            return $this->closed($request);
        }

        return $this->open($request);
    }

    /**
     * Draft revisions tab.
     * Accessible only to roles with reversal.initiate or reversal.release (non-IT originators).
     */
    public function draft(Request $request)
    {
        $this->authorize('viewAny', AudRev::class);

        $user = auth()->user();
        $context = UserAccessContext::forUser($user);
        $userUnitId = (int) ($user->acc_unt_id ?? 0);
        $userArea = (string) ($user->acc_untarea ?? '');
        $isItStaff = ($userUnitId === 860000 || AreaDefinition::isIt($userArea) || in_array($context->getRoleSlug(), ['IT_ADMIN', 'IT_OFFICER'], true));

        // IT accounts do not originate drafts (matching legacy start_it_multiple.bas vs start_fin_multiple.bas)
        $canAccessDraft = ! $isItStaff && (
            $context->isSuperAdmin() ||
            RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_INITIATE) ||
            RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_RELEASE)
        );

        if (! $canAccessDraft) {
            abort(403, 'Unauthorized. Draft revisions are restricted to initiating units.');
        }

        return $this->renderListing($request, 'draft');
    }

    /**
     * Open revisions tab (In Process and Under Revision).
     */
    public function open(Request $request)
    {
        $this->authorize('viewAny', AudRev::class);

        return $this->renderListing($request, 'open');
    }

    /**
     * Closed revisions tab (Fulfilled and Cancelled).
     */
    public function closed(Request $request)
    {
        $this->authorize('viewAny', AudRev::class);

        return $this->renderListing($request, 'closed');
    }

    /**
     * Detailed view of a specific reversal case.
     */
    public function show(AudRev $rev)
    {
        $this->authorize('view', $rev);

        $rev->load([
            'unit',
            'initiatingUnit',
            'comps' => fn ($q) => $q->orderBy('rvc_id'),
            'data' => fn ($q) => $q->orderBy('rvd_id'),
            'attachments',
        ]);

        return view('admin.reversals.show', compact('rev'));
    }

    /**
     * Release a reversal case to IT for execution.
     */
    public function release(AudRev $rev, DataRevisionService $revisionService)
    {
        $this->authorize('release', $rev);

        try {
            $revisionService->release($rev);
        } catch (\Throwable $e) {
            return redirect()->route('admin.reversals.show', $rev->rev_id)
                ->withErrors(['release' => $e->getMessage()]);
        }

        return redirect()->route('admin.reversals.show', $rev->rev_id)
            ->with('status', "Reversal #{$rev->rev_id} released successfully to IT for execution.");
    }

    /**
     * Execute a reversal case and mutate database records.
     * Idempotent-safe via isFulfilled check and policy guard.
     */
    public function execute(AudRev $rev, DataRevisionService $revisionService)
    {
        $this->authorize('execute', $rev);

        try {
            $revisionService->executeDataRevision($rev);
        } catch (\LogicException $e) {
            return redirect()->route('admin.reversals.show', $rev->rev_id)
                ->withErrors(['execution' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return redirect()->route('admin.reversals.show', $rev->rev_id)
                ->withErrors(['execution' => 'Execution failed: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.reversals.show', $rev->rev_id)
            ->with('status', "Reversal #{$rev->rev_id} executed successfully and marked as Fulfilled.");
    }

    /**
     * Return a reversal case back to the initiating unit.
     */
    public function return(Request $request, AudRev $rev, DataRevisionService $revisionService)
    {
        $this->authorize('return', $rev);

        $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $revisionService->return($rev, $request->input('remarks'));
        } catch (\Throwable $e) {
            return redirect()->route('admin.reversals.show', $rev->rev_id)
                ->withErrors(['return' => $e->getMessage()]);
        }

        return redirect()->route('admin.reversals.show', $rev->rev_id)
            ->with('status', "Reversal #{$rev->rev_id} returned to initiating unit.");
    }

    /**
     * Cancel a reversal case.
     * Hard-deletes if Draft; marks Cancelled if Under Revision / In Process.
     */
    public function cancel(Request $request, AudRev $rev, DataRevisionService $revisionService)
    {
        $this->authorize('cancel', $rev);

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $isDraft = $rev->isDraft();
        $revId = $rev->rev_id;

        try {
            $revisionService->cancel($rev, $request->input('reason'));
        } catch (\Throwable $e) {
            return redirect()->route('admin.reversals.show', $revId)
                ->withErrors(['cancel' => $e->getMessage()]);
        }

        if ($isDraft) {
            return redirect()->route('admin.reversals.draft')
                ->with('status', "Draft reversal #{$revId} was permanently deleted.");
        }

        return redirect()->route('admin.reversals.show', $revId)
            ->with('status', "Reversal #{$revId} has been cancelled.");
    }

    /**
     * Shared listing builder for draft, open, closed, and all tabs.
     */
    protected function renderListing(Request $request, string $tab)
    {
        $user = auth()->user();
        $context = UserAccessContext::forUser($user);
        $userUnitId = (int) ($user->acc_unt_id ?? 0);
        $userArea = (string) ($user->acc_untarea ?? '');

        // Global visibility: SuperAdmin, Command, IT Staff (unit 860000 / IT roles)
        $isItStaff = ($userUnitId === 860000 || AreaDefinition::isIt($userArea) || in_array($context->getRoleSlug(), ['IT_ADMIN', 'IT_OFFICER'], true));
        $isGlobal = ($context->isSuperAdmin() || $context->isCommand() || $isItStaff);

        // IT accounts do not originate drafts (matching legacy start_it_multiple.bas vs start_fin_multiple.bas)
        $canViewDraft = ! $isItStaff && (
            $context->isSuperAdmin() ||
            RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_INITIATE) ||
            RolePermissionMap::hasPermission($user, PermissionRegistry::REVERSAL_RELEASE)
        );

        $baseQuery = AudRev::query();

        if (! $isGlobal) {
            $scopeService = app(DataScopeService::class);
            $baseQuery->where(function ($q) use ($user, $scopeService, $userArea) {
                $q->where(function ($sq) use ($user, $scopeService) {
                    $scopeService->applyScope($sq, $user, 'rev_unt_id');
                })->orWhere(function ($sq) use ($user, $scopeService) {
                    $scopeService->applyScope($sq, $user, 'rev_intunt_id');
                });

                if (AreaDefinition::isFinance($userArea)) {
                    $q->orWhereIn('rev_obj', ['Salary', 'Salary Order', 'Commitment', 'Transaction', 'Payment', 'Allocation', 'Funding', 'Transfer', 'FinContract']);
                }

                if (AreaDefinition::isHr($userArea)) {
                    $q->orWhereIn('rev_obj', ['Employee', 'Contract', 'Attendance', 'Salary Requisition']);
                }

                if (AreaDefinition::isProcurement($userArea)) {
                    $q->orWhereIn('rev_obj', ['Purchase Case', 'Purchase Receipt', 'Purchase Attachment', 'Quotation']);
                }
            });
        }

        // Calculate tab counts
        $countsQuery = clone $baseQuery;
        $reversalsDraftCount = (clone $countsQuery)->where('rev_status', 'Draft')->count();
        $reversalsOpenCount = (clone $countsQuery)->whereIn('rev_status', ['In Process', 'Under Revision'])->count();
        $reversalsClosedCount = (clone $countsQuery)->whereIn('rev_status', ['Fulfilled', 'Cancelled'])->count();
        $reversalsFulfilledCount = (clone $countsQuery)->where('rev_status', 'Fulfilled')->count();
        $reversalsCancelledCount = (clone $countsQuery)->where('rev_status', 'Cancelled')->count();

        $query = clone $baseQuery;

        switch ($tab) {
            case 'draft':
                $query->where('rev_status', 'Draft');
                break;
            case 'closed':
                $query->whereIn('rev_status', ['Fulfilled', 'Cancelled']);
                break;
            case 'all':
                break;
            case 'open':
            default:
                $tab = 'open';
                $query->whereIn('rev_status', ['In Process', 'Under Revision']);
                break;
        }

        $reversals = $query
            ->with(['unit', 'initiatingUnit'])
            ->orderByDesc('rev_id')
            ->paginate(25);

        $status = $tab;

        return view('admin.reversals.index', compact(
            'reversals',
            'tab',
            'status',
            'canViewDraft',
            'reversalsDraftCount',
            'reversalsOpenCount',
            'reversalsClosedCount',
            'reversalsFulfilledCount',
            'reversalsCancelledCount'
        ));
    }
}
