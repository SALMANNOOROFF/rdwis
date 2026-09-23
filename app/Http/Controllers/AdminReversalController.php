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
     * Accessible to non-IT division/department users. SO IT accounts do not view drafts.
     */
    public function draft(Request $request)
    {
        $this->authorize('viewAny', AudRev::class);

        $user = auth()->user();
        $context = UserAccessContext::forUser($user);
        $userUnitId = (int) ($user->acc_unt_id ?? 0);
        $userArea = (string) ($user->acc_untarea ?? '');
        $isItStaff = ($userUnitId === 860000 || AreaDefinition::isIt($userArea) || in_array($context->getRoleSlug(), ['IT_ADMIN', 'IT_OFFICER'], true));

        // SO IT does not view drafts - redirect to open
        if ($isItStaff) {
            return redirect()->route('admin.reversals.open');
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

        $user = auth()->user();
        $context = UserAccessContext::forUser($user);
        $userUnitId = (int) ($user->acc_unt_id ?? 0);
        $userArea = (string) ($user->acc_untarea ?? '');
        $isItStaff = ($userUnitId === 860000 || AreaDefinition::isIt($userArea) || in_array($context->getRoleSlug(), ['IT_ADMIN', 'IT_OFFICER'], true));

        return view('admin.reversals.show', compact('rev', 'isItStaff'));
    }

    /**
     * Update reversal details (e.g. Reason while in Draft or Under Revision).
     */
    public function update(Request $request, AudRev $rev)
    {
        $this->authorize('view', $rev);

        if (! $rev->isDraft() && ! $rev->isUnderRevision()) {
            return redirect()->route('admin.reversals.show', $rev->rev_id)
                ->withErrors(['update' => 'Only Draft or Under Revision cases can be edited.']);
        }

        $request->validate([
            'rev_reason' => 'required|string|max:1000',
        ]);

        $rev->rev_reason = trim($request->input('rev_reason'));
        $rev->save();

        return redirect()->route('admin.reversals.show', $rev->rev_id)
            ->with('status', 'Reason updated successfully.');
    }

    /**
     * Release a reversal case to IT for execution.
     */
    public function release(Request $request, AudRev $rev, DataRevisionService $revisionService)
    {
        $this->authorize('release', $rev);

        if ($request->filled('rev_reason')) {
            $rev->rev_reason = trim($request->input('rev_reason'));
            $rev->save();
        }

        if (empty(trim((string) $rev->rev_reason))) {
            return redirect()->route('admin.reversals.show', $rev->rev_id)
                ->withErrors(['release' => 'Please enter reason for data revision before releasing.']);
        }

        try {
            $revisionService->release($rev);
        } catch (\Throwable $e) {
            return redirect()->route('admin.reversals.show', $rev->rev_id)
                ->withErrors(['release' => $e->getMessage()]);
        }

        return redirect()->route('admin.reversals.show', $rev->rev_id)
            ->with('status', "The data revision case has been released.");
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
        $isGlobal = ($context->isSuperAdmin() || $context->isCommand());

        // SO IT does not view drafts - redirect to open
        if ($isItStaff && $tab === 'draft') {
            return redirect()->route('admin.reversals.open');
        }

        $canViewDraft = ! $isItStaff;

        $baseQuery = AudRev::query();

        $divisionBreakdown = [];
        $selectedDivision = $request->query('division', $request->query('unit_id', ''));

        if ($isItStaff) {
            // SO IT sees all Open and Closed cases across all divisions/departments, but never Drafts
            $baseQuery->where('rev_status', '!=', 'Draft');
            $reversalsDraftCount = 0;
            $reversalsOpenCount = AudRev::whereIn('rev_status', ['In Process', 'Under Revision'])->count();
            $reversalsClosedCount = AudRev::whereIn('rev_status', ['Fulfilled', 'Cancelled'])->count();
            $reversalsFulfilledCount = AudRev::where('rev_status', 'Fulfilled')->count();
            $reversalsCancelledCount = AudRev::where('rev_status', 'Cancelled')->count();

            // Calculate division-wise breakdown for current tab
            $divStatsQuery = AudRev::query();
            if ($tab === 'closed') {
                $divStatsQuery->whereIn('rev_status', ['Fulfilled', 'Cancelled']);
            } else {
                $divStatsQuery->whereIn('rev_status', ['In Process', 'Under Revision']);
            }

            $rawCounts = (clone $divStatsQuery)
                ->selectRaw('COALESCE(rev_unt_id, rev_intunt_id) as unit_id, count(*) as count')
                ->groupBy('unit_id')
                ->pluck('count', 'unit_id');

            if ($rawCounts->isNotEmpty()) {
                $units = \App\Models\Unit::whereIn('unt_id', $rawCounts->keys())
                    ->get(['unt_id', 'unt_namesh', 'unt_name']);

                foreach ($units as $u) {
                    $divisionBreakdown[] = [
                        'unit_id' => $u->unt_id,
                        'name' => $u->unt_namesh ?? $u->unt_name,
                        'count' => (int) ($rawCounts[$u->unt_id] ?? 0),
                    ];
                }
                // Sort by count descending
                usort($divisionBreakdown, fn($a, $b) => $b['count'] <=> $a['count']);
            }
        } else {
            // Division / Department user: strictly scoped to their division / department
            if (! $isGlobal) {
                $scopeService = app(DataScopeService::class);
                $baseQuery->where(function ($q) use ($user, $scopeService) {
                    $q->where(function ($sq) use ($user, $scopeService) {
                        $scopeService->applyScope($sq, $user, 'rev_intunt_id');
                    })->orWhere(function ($sq) use ($user, $scopeService) {
                        $scopeService->applyScope($sq, $user, 'rev_unt_id');
                    });
                });
            }

            // Calculate tab counts scoped to division
            $countsQuery = clone $baseQuery;
            $reversalsDraftCount = (clone $countsQuery)->where('rev_status', 'Draft')->count();
            $reversalsOpenCount = (clone $countsQuery)->whereIn('rev_status', ['In Process', 'Under Revision'])->count();
            $reversalsClosedCount = (clone $countsQuery)->whereIn('rev_status', ['Fulfilled', 'Cancelled'])->count();
            $reversalsFulfilledCount = (clone $countsQuery)->where('rev_status', 'Fulfilled')->count();
            $reversalsCancelledCount = (clone $countsQuery)->where('rev_status', 'Cancelled')->count();
        }

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

        // Apply division filter if requested
        if ($selectedDivision !== '' && $selectedDivision !== null) {
            $divId = (int) $selectedDivision;
            if ($divId > 0) {
                $query->where(function ($q) use ($divId) {
                    $q->where('rev_unt_id', $divId)
                      ->orWhere('rev_intunt_id', $divId);
                });
            }
        }

        $reversals = $query
            ->with(['unit', 'initiatingUnit', 'attachments'])
            ->orderByDesc('rev_id')
            ->paginate(25)
            ->withQueryString();

        $status = $tab;

        return view('admin.reversals.index', compact(
            'reversals',
            'tab',
            'status',
            'isItStaff',
            'canViewDraft',
            'divisionBreakdown',
            'selectedDivision',
            'reversalsDraftCount',
            'reversalsOpenCount',
            'reversalsClosedCount',
            'reversalsFulfilledCount',
            'reversalsCancelledCount'
        ));
    }
}
