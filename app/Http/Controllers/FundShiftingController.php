<?php

namespace App\Http\Controllers;

use App\Models\FundShift;
use App\Models\User;
use App\Services\FundShiftingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class FundShiftingController extends Controller
{
    public function __construct(
        protected FundShiftingService $shiftingService
    ) {}

    /**
     * Display listing / hub of fund shifting cases.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = auth()->user();
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $userAccId = (int) ($user->acc_id ?? 0);
        $tab = $request->query('tab', 'pending');
        $search = trim((string) $request->query('search', ''));
        $projectId = $request->query('project_id');

        $baseQuery = FundShift::with(['project', 'head', 'creator', 'decisions', 'unit']);

        // 1. Data Visibility Scope:
        if ($user->isDivision() && !$user->isMyDepartmentMode() && !$user->isCentralDepartment()) {
            $baseQuery->where(function ($q) use ($user, $userAccId) {
                // Initiated by user
                $q->where('fsh_created_by', $userAccId);

                // Or belonging to user's unit
                if ($user->acc_unt_id) {
                    $q->orWhere('fsh_unt_id', $user->acc_unt_id);
                }

                // Or specifically assigned to user
                $q->orWhere('fsh_current_acc_id', $userAccId);

                // Or user participated in decision trail
                $q->orWhereHas('decisions', function ($dq) use ($userAccId) {
                    $dq->where('fsd_acc_id', $userAccId);
                });
            });
        } elseif ($userArea === 'it' && !$user->isCentralDepartment()) {
            // IT sees cases currently in IT, or approved/implemented, or where IT participated
            $baseQuery->where(function ($q) use ($userAccId) {
                $q->where('fsh_current_stage', 'IT')
                    ->orWhereIn('fsh_status', ['Approved', 'Implemented'])
                    ->orWhere('fsh_current_acc_id', $userAccId)
                    ->orWhereHas('decisions', function ($dq) use ($userAccId) {
                        $dq->where('fsd_acc_id', $userAccId);
                    });
            });
        }
        // Finance, Command (MD/DDG/DG), and Central Department oversee all fund shift cases

        if ($projectId) {
            $baseQuery->where('fsh_prj_id', $projectId);
        }

        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('fsh_id', 'like', "%{$search}%")
                    ->orWhere('fsh_rev_id', 'like', "%{$search}%")
                    ->orWhere('fsh_from_sbh_name', 'ilike', "%{$search}%")
                    ->orWhere('fsh_to_sbh_name', 'ilike', "%{$search}%")
                    ->orWhereHas('project', function ($pq) use ($search) {
                        $pq->where('prj_title', 'ilike', "%{$search}%")
                            ->orWhere('prj_code', 'ilike', "%{$search}%");
                    });
            });
        }

        $allCases = $baseQuery->orderBy('fsh_id', 'desc')->get();

        // 2. Classify each case into Pending Action, Open, or Closed for THIS user:
        $isPendingForUser = function (FundShift $c) use ($user, $userAccId, $userArea) {
            if (in_array(strtolower(trim($c->fsh_status)), ['implemented', 'cancelled', 'rejected'])) {
                return false;
            }

            // Check who took the latest action
            $latestDecision = $c->decisions->sortByDesc('fsd_id')->first();
            $iTookLatestAction = $latestDecision && ((int) $latestDecision->fsd_acc_id === $userAccId);
            if ($iTookLatestAction) {
                // If I took the latest action, it has moved forward out of my hands!
                return false;
            }

            // If specifically assigned to this user
            if (!empty($c->fsh_current_acc_id)) {
                return (int) $c->fsh_current_acc_id === $userAccId || $user->isCentralDepartment();
            }

            // Stage-based matching
            $stage = strtoupper(trim((string) $c->fsh_current_stage));
            if ($stage === 'DIVISION') {
                return $user->isDivision() && ((int) $c->fsh_unt_id === (int) $user->acc_unt_id || (int) $c->fsh_created_by === $userAccId);
            } elseif ($stage === 'FINANCE') {
                return $user->isFinance();
            } elseif ($stage === 'IT') {
                return in_array($userArea, ['it', 'is'], true) || $user->isCentralDepartment();
            } elseif ($stage === 'MD') {
                // isMd()/isDdg()/isDg() live on UserAccessContext, not on the
                // CenAccount model - calling them on $user threw a
                // BadMethodCallException for stage-only (unassigned) cases.
                return \App\Services\Auth\UserAccessContext::forUser($user)->isMd();
            } elseif ($stage === 'DDG') {
                return \App\Services\Auth\UserAccessContext::forUser($user)->isDdg();
            } elseif ($stage === 'DG') {
                return \App\Services\Auth\UserAccessContext::forUser($user)->isDg();
            } elseif ($stage === 'DLOG') {
                return (method_exists($user, 'isDlog') && $user->isDlog()) || in_array($userArea, ['log', 'dlog']);
            }

            return $user->isCentralDepartment();
        };

        // Filter into collections:
        $pending = $allCases->filter(fn($c) => $isPendingForUser($c))->values();
        $closed = $allCases->filter(fn($c) => in_array(strtolower(trim($c->fsh_status)), ['implemented', 'cancelled', 'rejected']))->values();
        
        $pendingIds = $pending->pluck('fsh_id')->toArray();
        $closedIds = $closed->pluck('fsh_id')->toArray();
        
        // Open Cases: Not closed and not in pending action for THIS user (i.e. sent forward or under review by another authority)
        $open = $allCases->filter(fn($c) => !in_array($c->fsh_id, $pendingIds) && !in_array($c->fsh_id, $closedIds))->values();

        // Counts
        $caseCount = $pending->count();
        $openCount = $open->count();
        $closedCount = $closed->count();

        $unitNameMap = \Illuminate\Support\Facades\DB::table('cen.units')->pluck('unt_namesh', 'unt_id');
        $isCommandOrFinance = $user->isMdDdgDg() || $user->isFinance() || $user->isCentralDepartment() || $user->isAllDepartmentsMode();

        $projects = $this->shiftingService->getAvailableProjects($user);

        return view('fund_shifting.index', compact(
            'pending',
            'open',
            'closed',
            'caseCount',
            'openCount',
            'closedCount',
            'unitNameMap',
            'isCommandOrFinance',
            'tab',
            'projects',
            'search',
            'projectId'
        ));
    }

    /**
     * Show form to initiate a fund shifting request.
     */
    public function create(): View
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->isDivision() && ! $user->isFinance()) {
            abort(403, 'Only Division officers and Finance Department can initiate fund shifting requests.');
        }

        $projects = $this->shiftingService->getAvailableProjects($user);

        return view('fund_shifting.create', compact('projects'));
    }

    /**
     * API endpoint to get subhead details with smart indicators for a head.
     */
    public function getSubheads(int $headId): JsonResponse
    {
        try {
            $subheads = $this->shiftingService->getSubheadsForHead($headId);
            return response()->json([
                'success' => true,
                'subheads' => $subheads,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Store draft fund shifting case.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->isDivision() && ! $user->isFinance()) {
            abort(403, 'Only Division officers and Finance Department can initiate fund shifting requests.');
        }

        $request->validate([
            'prj_id' => 'required|integer',
            'hed_id' => 'required|integer',
            'from_sbh_id' => 'required|integer',
            'to_sbh_id' => 'required|integer|different:from_sbh_id',
            'proposed_amount' => 'required|numeric|min:1',
            'purpose' => 'nullable|string|max:2000',
        ]);

        try {
            $shift = $this->shiftingService->createDraft($user, $request->all());

            return redirect()->route('fund_shifting.show', $shift->fsh_id)
                ->with('success', "Fund Shifting Case File {$shift->case_number} created successfully as draft.");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display Case File show view.
     */
    public function show(int $id): View
    {
        $shift = FundShift::with(['project', 'head', 'unit', 'creator', 'implementer', 'decisions'])
            ->findOrFail($id);

        $subheads = $this->shiftingService->getSubheadsForHead($shift->fsh_hed_id);
        $pastShifts = $this->shiftingService->getPastFundShifts($shift->fsh_prj_id);

        // From and To subhead current live data
        $fromLive = collect($subheads)->firstWhere('sbh_id', $shift->fsh_from_sbh_id);
        $toLive = collect($subheads)->firstWhere('sbh_id', $shift->fsh_to_sbh_id);

        $user = auth()->user();
        $destinations = $this->shiftingService->getAvailableDestinations($user, $shift);

        $attachments = \Illuminate\Support\Facades\DB::table('ina.inaattachments')
            ->where('iat_objid', $shift->fsh_id)
            ->get();

        return view('fund_shifting.show', compact(
            'shift',
            'subheads',
            'pastShifts',
            'fromLive',
            'toLive',
            'destinations',
            'attachments'
        ));
    }

    /**
     * Render printable official A4 case sheet for Fund Shifting case.
     */
    public function printCase(int $id): View
    {
        $shift = FundShift::with(['project.unit', 'head', 'unit', 'creator', 'implementer', 'decisionsAsc'])
            ->findOrFail($id);

        $subheads = $this->shiftingService->getSubheadsForHead($shift->fsh_hed_id);
        $attachments = \Illuminate\Support\Facades\DB::table('ina.inaattachments')
            ->where('iat_objid', $shift->fsh_id)
            ->orderBy('iat_id')
            ->get();

        // Chronological seat-wise remarks trail
        $remarksList = $shift->decisionsAsc
            ->where('fsd_action', '!=', 'Draft Created')
            ->values()
            ->map(function ($d) {
                $actLower = strtolower(trim((string) $d->fsd_action));
                $tone = 'normal';
                if (str_contains($actLower, 'approv') || str_contains($actLower, 'implement')) {
                    $tone = 'approve';
                } elseif (str_contains($actLower, 'return')) {
                    $tone = 'return';
                } elseif (str_contains($actLower, 'reject') || str_contains($actLower, 'cancel')) {
                    $tone = 'reject';
                }

                $actionLabel = $d->fsd_action . (!empty($d->fsd_to_stage) && !in_array($actLower, ['implemented by it', 'cancelled'], true) ? " → {$d->fsd_to_stage}" : '');

                return [
                    'actor'     => $d->fsd_user_name ?: 'Authorized Officer',
                    'seat'      => strtoupper(trim((string) ($d->fsd_role ?: ($d->fsd_user_desig ?: 'Officer')))),
                    'action'    => $actionLabel,
                    'tone'      => $tone,
                    'timestamp' => $d->fsd_dtg ? $d->fsd_dtg->format('d M Y, h:i A') : '—',
                    'remarks'   => $d->fsd_remarks ?? '',
                ];
            })
            ->all();

        // Enclosures from ina.inaattachments
        $enclosuresList = [];
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        foreach ($attachments->filter(fn($a) => !empty(trim((string)$a->iat_path))) as $att) {
            $normPath = str_replace('\\', '/', (string)$att->iat_path);
            $fn = basename($normPath);
            $ext = strtolower(pathinfo($normPath, PATHINFO_EXTENSION));
            $label = trim((string)($att->iat_type ?: '')) ?: $fn;
            $uploadedBy = null;
            if (!empty($att->iat_acc_id)) {
                $uploadedBy = \Illuminate\Support\Facades\DB::table('cen.accounts')->where('acc_id', $att->iat_acc_id)->value('acc_name');
            }

            $enclosuresList[] = [
                'title'       => $label,
                'filename'    => $fn,
                'source'      => 'Fund Shifting Attachment',
                'uploaded_by' => $uploadedBy ?: ($shift->fsh_created_name ?: ($shift->creator->acc_name ?? 'Initiator')),
                'meta'        => strtoupper($ext ?: 'FILE'),
                'url'         => route('universal.attachment.view', ['module' => 'ina', 'id' => $att->iat_id]),
                'is_image'    => in_array($ext, $imageExts, true),
                'is_pdf'      => $ext === 'pdf',
            ];
        }

        return view('fund_shifting.print', compact('shift', 'subheads', 'remarksList', 'enclosuresList'));
    }

    /**
     * Forward case to next stage or account.
     */
    public function forward(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'remarks' => 'required|string|max:2000',
            'target_destination' => 'nullable|string|max:100',
            'target_stage' => 'nullable|string|max:100',
            'edited_amount' => 'nullable|numeric|min:0.01',
        ]);

        $target = $request->target_destination ?: $request->target_stage;

        $shift = FundShift::findOrFail($id);

        try {
            $this->shiftingService->forward($shift, auth()->user(), $request->remarks, $target, $request->edited_amount);

            return redirect()->route('fund_shifting.index')
                ->with('success', 'Case file successfully forwarded.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve case by Finance or Command (routes case to IT for implementation).
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'remarks' => 'nullable|string|max:2000',
            'edited_amount' => 'nullable|numeric|min:0.01',
        ]);

        $shift = FundShift::findOrFail($id);

        try {
            $this->shiftingService->approve($shift, auth()->user(), $request->remarks, $request->edited_amount);

            return redirect()->route('fund_shifting.index')
                ->with('success', 'Fund shifting case approved and routed to IT for implementation.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Return case back to previous stage.
     */
    public function return(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'remarks' => 'required|string|max:2000',
            'target_stage' => 'nullable|string|max:100',
        ]);

        $shift = FundShift::findOrFail($id);

        try {
            $this->shiftingService->return($shift, auth()->user(), $request->remarks, $request->target_stage);

            return redirect()->route('fund_shifting.index')
                ->with('success', 'Case file returned.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel case.
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'remarks' => 'nullable|string|max:2000',
        ]);

        $shift = FundShift::findOrFail($id);

        try {
            $this->shiftingService->cancel($shift, auth()->user(), $request->remarks);

            return redirect()->route('fund_shifting.index')
                ->with('success', 'Fund shifting case cancelled.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Implement approved fund shifting by IT (or authorized admin).
     */
    public function implement(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'remarks' => 'nullable|string|max:2000',
        ]);

        $shift = FundShift::findOrFail($id);
        $user = auth()->user();

        $isIt = in_array(strtolower(trim((string)$user->acc_untarea)), ['it', 'is'], true) || $user->isCentralDepartment();

        if (!$isIt && !$user->isFinance()) {
            return back()->with('error', 'Only authorized IT officers can execute fund shifting implementation.');
        }

        try {
            $this->shiftingService->implement(
                $shift,
                $user,
                null,
                $request->remarks
            );

            return redirect()->route('fund_shifting.show', $shift->fsh_id)
                ->with('success', "Fund shifting implemented successfully by IT! Subhead allocations updated and Data Revision record #FS-{$shift->fsh_rev_id} fulfilled.");
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * API to fetch past revisions / history for modal.
     */
    public function history(int $projectId): JsonResponse
    {
        try {
            $pastShifts = $this->shiftingService->getPastFundShifts($projectId);
            return response()->json([
                'success' => true,
                'shifts' => $pastShifts,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
