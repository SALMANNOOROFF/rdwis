<?php

namespace App\Http\Controllers;

use App\Models\GenericCase;
use App\Models\CenAccount;
use App\Services\GenericCaseService;
use App\Services\Auth\UserAccessContext;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class GenericCaseController extends Controller
{
    public function __construct(
        protected GenericCaseService $caseService
    ) {}

    /**
     * Categories list available across the system.
     */
    protected function getCategories(): array
    {
        return [
            'General'               => 'General Administrative Case',
            'Procurement'           => 'Procurement / Purchase Note',
            'HR & Hiring'           => 'Human Resource / Staffing',
            'Maintenance & Works'   => 'Repair, Works & Maintenance',
            'Finance & Sanction'    => 'Financial Requisition / Sanction',
            'IT & Tech'             => 'IT Infrastructure & Software',
            'Policy & Rules'        => 'Policy, SOP & Directive',
            'Legal & Inquiry'       => 'Legal, Audit & Disciplinary',
        ];
    }

    /**
     * Check if user is Command Authority (MD, DDG, DG, DLOG).
     */
    protected function isCommandAuthority(CenAccount $user): bool
    {
        $context = UserAccessContext::forUser($user);
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $userAccId = (int) ($user->acc_id ?? 0);

        return $context->isCommand()
            || $context->isMd()
            || $context->isDdg()
            || $context->isDg()
            || $context->isDlog()
            || in_array($userAccId, [100, 101, 106, 107, 108], true)
            || in_array($userArea, ['rdw', 'hqs', 'nrdi', 'log', 'dlog'], true);
    }

    /**
     * Check if user is Approving Authority (Finance / MD / DG).
     */
    protected function canApprove(CenAccount $user): bool
    {
        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $context = UserAccessContext::forUser($user);

        return (method_exists($user, 'isFinance') && $user->isFinance())
            || in_array($userArea, ['fin', 'finance'], true)
            || ((int)($user->acc_unt_id ?? 0) === 800000)
            || $context->isMd()
            || $context->isDg()
            || in_array((int)$user->acc_id, [100, 101, 107], true);
    }

    /**
     * Display Case Initiation Hub (Index).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $userAccId = (int) ($user->acc_id ?? 0);

        $tab = $request->query('tab', 'action_required');
        $search = trim((string) $request->query('search', ''));
        $category = $request->query('category');

        $baseQuery = GenericCase::with(['creator', 'currentHolder', 'notes']);

        if (!empty($category)) {
            $baseQuery->where('gcs_category', $category);
        }

        if ($search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('gcs_file_no', 'ilike', "%{$search}%")
                  ->orWhere('gcs_title', 'ilike', "%{$search}%")
                  ->orWhere('gcs_category', 'ilike', "%{$search}%");
            });
        }

        $isApexCommand = $this->isCommandAuthority($user);

        $uArea = strtolower(trim((string)($user->acc_untarea ?? '')));
        $desigShort = strtoupper(trim((string)($user->acc_desigshort ?? '')));
        $desig = strtoupper(trim((string)($user->acc_desig ?? '')));

        // Reusable scope for Action Required / Pending Action:
        // 1. User's own Draft cases
        // 2. OR Cases currently assigned directly to this user
        // 3. OR Cases forwarded to this user's authority stage (MD, DDG, DG, DLOG, Finance)
        $applyActionRequiredScope = function ($q) use ($user, $userAccId, $uArea, $desigShort, $desig) {
            $q->where(function ($dq) use ($userAccId) {
                $dq->where('gcs_created_by', $userAccId)->where('gcs_status', 'Draft');
            })->orWhere(function ($fq) use ($userAccId) {
                $fq->where('gcs_current_holder_id', $userAccId)
                   ->whereNotIn('gcs_status', ['Draft', 'Approved', 'Closed', 'Cancelled', 'Rejected']);
            })->orWhere(function ($sq) use ($user, $uArea, $desigShort, $desig) {
                $sq->whereNotIn('gcs_status', ['Draft', 'Approved', 'Closed', 'Cancelled', 'Rejected']);
                $sq->where(function ($stq) use ($user, $uArea, $desigShort, $desig) {
                    if ($user->isFinance() || in_array($uArea, ['fin', 'finance'], true) || ((int)($user->acc_unt_id ?? 0) === 800000)) {
                        $stq->whereIn('gcs_current_stage', ['DFin', 'Finance', 'DFIN', 'FINANCE']);
                    } elseif (in_array($desigShort, ['MD', 'MD RDW'], true) || in_array($uArea, ['rdw', 'md'], true) || str_contains($desig, 'MANAGING DIRECTOR')) {
                        $stq->whereIn('gcs_current_stage', ['MD', 'MD RDW']);
                    } elseif (in_array($desigShort, ['DDG', 'DDG NRDI', 'DDG NRD'], true) || $uArea === 'hqs' || str_contains($desig, 'DEPUTY DIRECTOR GENERAL')) {
                        $stq->whereIn('gcs_current_stage', ['DDG', 'DDG NRDI']);
                    } elseif (in_array($desigShort, ['DG', 'DG NRDI'], true) || in_array($uArea, ['nrdi', 'dg'], true) || str_contains($desig, 'DIRECTOR GENERAL')) {
                        $stq->whereIn('gcs_current_stage', ['DG', 'DG NRDI']);
                    } elseif ($user->isDlog() || in_array($desigShort, ['DLOG'], true) || in_array($uArea, ['log', 'dlog'], true)) {
                        $stq->whereIn('gcs_current_stage', ['DLOG', 'DIR LOG']);
                    } else {
                        $stq->whereRaw('1=0');
                    }
                });
            });
        };

        // 1. Pending Action Cases
        $pending = GenericCase::with(['creator', 'currentHolder', 'unit', 'movements'])
            ->where($applyActionRequiredScope)
            ->orderBy('gcs_id', 'desc')
            ->get();

        $pendingIds = $pending->pluck('gcs_id')->toArray();

        // 2. Open Cases (Active pipeline cases not currently pending on this user)
        $openQuery = GenericCase::with(['creator', 'currentHolder', 'unit', 'movements'])
            ->whereNotIn('gcs_status', ['Draft', 'Approved', 'Closed', 'Cancelled', 'Rejected'])
            ->whereNotIn('gcs_id', $pendingIds);

        if (!$isApexCommand) {
            $openQuery->where(function ($q) use ($userAccId) {
                $q->where('gcs_created_by', $userAccId)
                  ->orWhereHas('movements', function ($mq) use ($userAccId) {
                      $mq->where('gcm_actor_id', $userAccId);
                  });
            });
        }
        $open = $openQuery->orderBy('gcs_id', 'desc')->get();

        // 3. Closed Cases (Finalized cases)
        $closedQuery = GenericCase::with(['creator', 'currentHolder', 'unit', 'movements'])
            ->whereIn('gcs_status', ['Approved', 'Closed', 'Cancelled', 'Rejected']);

        if (!$isApexCommand) {
            $closedQuery->where(function ($q) use ($userAccId) {
                $q->where('gcs_created_by', $userAccId)
                  ->orWhereHas('movements', function ($mq) use ($userAccId) {
                      $mq->where('gcm_actor_id', $userAccId);
                  });
            });
        }
        $closed = $closedQuery->orderBy('gcs_id', 'desc')->get();

        $unitNameMap = DB::table('cen.units')->pluck('unt_namesh', 'unt_id');

        $caseCount = $pending->count();
        $openCount = $open->count();
        $closedCount = $closed->count();

        return view('cases.index', [
            'pending'             => $pending,
            'open'                => $open,
            'closed'              => $closed,
            'unitNameMap'         => $unitNameMap,
            'caseCount'           => $caseCount,
            'openCount'           => $openCount,
            'closedCount'         => $closedCount,
            'actionRequiredCount' => $caseCount,
            'closeCount'          => $closedCount,
            'tab'                 => $tab,
            'search'              => $search,
            'categoryFilter'      => $category,
            'categories'          => $this->getCategories(),
            'isApexCommand'       => $isApexCommand,
            'user'                => $user,
        ]);
    }

    /**
     * Direct Case Initiation (NO POPUP - Immediately creates draft and redirects to edit).
     */
    public function initiate(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Apex Command (MD, DDG, DG, DLOG) do not initiate
        if ($this->isCommandAuthority($user)) {
            return back()->with('error', 'Command Headquarters does not initiate direct cases.');
        }

        try {
            $case = $this->caseService->initiateDraftCase($user);
            return redirect()->route('cases.show', $case->gcs_id);
        } catch (Throwable $e) {
            return back()->with('error', 'Error opening case file: ' . $e->getMessage());
        }
    }

    /**
     * Display Case Detail / Draft View.
     */
    public function show($id): View
    {
        $user = Auth::user();
        $case = GenericCase::with([
            'creator',
            'currentHolder',
            'notes.author',
            'movements' => function ($q) {
                $q->orderBy('gcm_dtg', 'desc');
            },
            'movements.actor',
            'movements.toHolder',
            'attachments.uploader',
            'unit'
        ])->findOrFail($id);

        $isCurrentHolder = ((int) $case->gcs_current_holder_id === (int) ($user->acc_id ?? 0));
        $isDraft = (strtolower($case->gcs_status) === 'draft');
        $isClosed = in_array(strtolower($case->gcs_status), ['approved', 'closed', 'cancelled', 'rejected']);
        $isInitiator = ((int) $case->gcs_created_by === (int) ($user->acc_id ?? 0));
        $canApprove = $this->canApprove($user);

        // Check if case is at this officer's authority stage (MD, DDG, DG, DLOG, Finance)
        if (!$isCurrentHolder && !$isDraft && !$isClosed) {
            $uArea = strtolower(trim((string)($user->acc_untarea ?? '')));
            $desigShort = strtoupper(trim((string)($user->acc_desigshort ?? '')));
            $desig = strtoupper(trim((string)($user->acc_desig ?? '')));
            $stage = $case->gcs_current_stage;
            if (
                ($user->isFinance() && in_array($stage, ['DFin', 'Finance', 'DFIN', 'FINANCE'])) ||
                ((in_array($desigShort, ['MD', 'MD RDW'], true) || in_array($uArea, ['rdw', 'md'], true) || str_contains($desig, 'MANAGING DIRECTOR')) && in_array($stage, ['MD', 'MD RDW'])) ||
                ((in_array($desigShort, ['DDG', 'DDG NRDI'], true) || $uArea === 'hqs' || str_contains($desig, 'DEPUTY DIRECTOR GENERAL')) && in_array($stage, ['DDG', 'DDG NRDI'])) ||
                ((in_array($desigShort, ['DG', 'DG NRDI'], true) || in_array($uArea, ['nrdi', 'dg'], true) || str_contains($desig, 'DIRECTOR GENERAL')) && in_array($stage, ['DG', 'DG NRDI'])) ||
                (($user->isDlog() || in_array($desigShort, ['DLOG'], true) || in_array($uArea, ['log', 'dlog'], true)) && in_array($stage, ['DLOG', 'DIR LOG']))
            ) {
                $isCurrentHolder = true;
            }
        }

        // Only the initiating Division/Officer can edit metadata (title, category, notes/summary)
        // AND only when the case is currently held by them
        $canEditCaseDetails = $isCurrentHolder && ($isInitiator || $isDraft || ($user && method_exists($user, 'isDivision') && $user->isDivision()));

        // Continuous Paragraph Numbering for movements & next remark
        $allMovementsAsc = $case->movements->where('gcm_action', '!=', 'Initiated')->sortBy('gcm_dtg');
        $currentParaNum = 1;
        foreach ($allMovementsAsc as $m) {
            $m->para_number = $currentParaNum;
            $raw = trim($m->gcm_remarks ?? '');
            if (!empty($raw)) {
                $lines = explode("\n", $raw);
                $c = 0;
                foreach ($lines as $l) {
                    if (!empty(trim($l))) $c++;
                }
                $currentParaNum += max(1, $c);
            }
        }
        $nextRemarkNumber = $currentParaNum;

        // Available destinations for Search Dropdown
        $destinations = $this->caseService->getAvailableDestinations($user);

        // Project Details Amendment payload (null for ordinary cases)
        $amendment = app(\App\Services\ProjectAmendmentService::class)->amendmentFor($case);

        // Latest decisions on top
        $decisions = $allMovementsAsc->sortByDesc('gcm_dtg');

        // Recent cases for bottom table
        $recentCases = GenericCase::where('gcs_id', '!=', $case->gcs_id)
            ->whereNotIn('gcs_status', ['Draft', 'Cancelled'])
            ->orderBy('gcs_id', 'desc')
            ->take(5)
            ->get();

        return view('cases.show', [
            'case'               => $case,
            'isCurrentHolder'    => $isCurrentHolder,
            'isDraft'            => $isDraft,
            'isClosed'           => $isClosed,
            'isInitiator'        => $isInitiator,
            'canEditCaseDetails' => $canEditCaseDetails,
            'canApprove'         => $canApprove,
            'nextRemarkNumber'   => $nextRemarkNumber,
            'destinations'       => $destinations,
            'decisions'          => $decisions,
            'recentCases'        => $recentCases,
            'categories'         => $this->getCategories(),
            'amendment'          => $amendment,
            'user'               => $user,
        ]);
    }

    /**
     * Save / Update Draft (Title, Category, Notes/Summary).
     */
    public function updateDraft(Request $request, $id)
    {
        $user = Auth::user();
        $case = GenericCase::findOrFail($id);

        $isInitiator = ((int) $case->gcs_created_by === (int) ($user->acc_id ?? 0));
        $isCurrentHolder = ((int) $case->gcs_current_holder_id === (int) ($user->acc_id ?? 0));
        $isDraft = (strtolower($case->gcs_status) === 'draft');

        if (!$isCurrentHolder || (!$isInitiator && !$isDraft && !($user && method_exists($user, 'isDivision') && $user->isDivision()))) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Only the initiating division can edit case notes and details.'], 403);
            }
            return back()->with('error', 'Unauthorized. Only the initiating division can edit case notes and details.');
        }

        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'category'    => 'nullable|string|max:100',
            'subject'     => 'nullable|string|max:500',
            'description' => 'nullable|string',
        ]);

        try {
            $case = $this->caseService->updateDraft($case, $user, $validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Draft saved successfully.',
                    'case'    => $case
                ]);
            }

            return back()->with('success', 'Case draft saved successfully.');
        } catch (Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error saving draft: ' . $e->getMessage());
        }
    }

    /**
     * Send / Forward Case (Right Side Minute Panel Button).
     */
    public function sendCase(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $case = GenericCase::findOrFail($id);

        $request->validate([
            'target_destination' => 'required|string',
            'remarks'            => 'required|string',
            'title'              => 'nullable|string|max:255',
            'category'           => 'nullable|string|max:100',
            'description'        => 'nullable|string',
        ]);

        try {
            $isInitiator = ((int) $case->gcs_created_by === (int) ($user->acc_id ?? 0));
            // Only update particulars if current user is the initiator/division
            if ($isInitiator || strtolower($case->gcs_status) === 'draft') {
                $updateData = [];
                if ($request->filled('title')) $updateData['title'] = $request->input('title');
                if ($request->filled('category')) $updateData['category'] = $request->input('category');
                if ($request->filled('description')) $updateData['description'] = $request->input('description');
                if (!empty($updateData)) {
                    $this->caseService->updateDraft($case, $user, $updateData);
                }
            }

            $this->caseService->sendCase($case, $user, $request->only(['target_destination', 'remarks']));

            return redirect()->route('cases.index')
                ->with('success', "Case {$case->gcs_file_no} sent successfully.");
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to send case: ' . $e->getMessage());
        }
    }

    /**
     * Approve Case (Finance / Approver action).
     */
    public function approve(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $case = GenericCase::findOrFail($id);

        if (!$this->canApprove($user)) {
            return back()->with('error', 'Unauthorized. Only Finance / Approving Authority can approve cases.');
        }

        try {
            $this->caseService->approveCase($case, $user, $request->input('remarks'));

            $message = "Case {$case->gcs_file_no} approved successfully.";

            // Auto-apply linked Project Details amendment (no effect on ordinary cases).
            $applied = app(\App\Services\ProjectAmendmentService::class)
                ->applyApprovedAmendment($case->fresh(), $user);

            if ($applied) {
                $message .= ' Approved changes have been applied to the project record automatically.';
            }

            return redirect()->route('cases.index')->with('success', $message);
        } catch (Throwable $e) {
            return back()->with('error', 'Error approving case: ' . $e->getMessage());
        }
    }

    /**
     * Revise the values of a linked Project Details amendment AFTER approval.
     *
     * Before approval the values are frozen (what is approved is exactly what was
     * put up). Once the case is approved, an approving authority may correct the
     * figures here and the project record is updated immediately.
     */
    public function updateAmendment(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $case = GenericCase::findOrFail($id);

        $service = app(\App\Services\ProjectAmendmentService::class);
        $payload = $service->amendmentFor($case);

        if (!$payload) {
            return back()->with('error', 'This case file does not carry a project amendment.');
        }

        if (!$service->isApproved($case)) {
            return back()->with('error', 'Values are locked until the case is approved.');
        }

        if (!$this->canApprove($user)) {
            return back()->with('error', 'Unauthorized. Only the approving authority can revise an approved amendment.');
        }

        $request->validate([
            'values'   => 'required|array',
            'values.*' => 'nullable|string|max:1000',
        ]);

        try {
            $done = $service->applyRevisedValues($case, $request->input('values', []), $user);

            return $done
                ? back()->with('success', 'Revised values saved and applied to the project record.')
                : back()->with('error', 'Nothing to save for this amendment.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error saving revised values: ' . $e->getMessage());
        }
    }

    /**
     * Cancel / Delete / Reject Case.
     */
    public function cancel(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $case = GenericCase::findOrFail($id);

        $userAccId = (int) ($user->acc_id ?? 0);
        $isCreator = ((int) $case->gcs_created_by === $userAccId);
        $isHolder = ((int) $case->gcs_current_holder_id === $userAccId);
        $canApprove = $this->canApprove($user);

        if (!$isCreator && !$isHolder && !$canApprove) {
            return back()->with('error', 'Unauthorized to cancel this case.');
        }

        try {
            // If it's a draft owned by creator and has no movements, permanently delete or cancel
            if ($case->gcs_status === 'Draft' && $isCreator) {
                $fileNo = $case->gcs_file_no;
                $case->delete();
                return redirect()->route('cases.index')->with('success', "Draft case {$fileNo} deleted.");
            }

            // Otherwise record rejection/cancellation
            $this->caseService->rejectCase($case, $user, $request->input('remarks', 'Case cancelled.'));
            return redirect()->route('cases.index')->with('success', "Case {$case->gcs_file_no} cancelled.");
        } catch (Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Upload an attachment to this case.
     */
    public function uploadAttachment(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $case = GenericCase::findOrFail($id);

        $request->validate([
            'attachment' => 'required|file|max:25600',
            'title'      => 'nullable|string|max:255',
        ]);

        try {
            $this->caseService->storeAttachment($case, $user, $request->file('attachment'), $request->input('title'));
            return back()->with('success', 'Document attached successfully.');
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to attach file: ' . $e->getMessage());
        }
    }

    /**
     * Official Printable Note Sheet.
     */
    public function print($id): View
    {
        $case = GenericCase::with([
            'creator',
            'currentHolder',
            'notes.author',
            'movements' => function ($q) {
                $q->orderBy('gcm_dtg', 'asc');
            },
            'movements.actor',
            'movements.toHolder',
            'attachments.uploader',
            'unit'
        ])->findOrFail($id);

        // Seat-wise movement & remarks trail
        $remarksList = $case->movements->map(function ($mov) {
            $actLower = strtolower(trim((string) $mov->gcm_action));
            $tone = 'normal';
            if (str_contains($actLower, 'approv') || str_contains($actLower, 'clos') || str_contains($actLower, 'fulfill')) {
                $tone = 'approve';
            } elseif (str_contains($actLower, 'return')) {
                $tone = 'return';
            } elseif (str_contains($actLower, 'reject') || str_contains($actLower, 'cancel')) {
                $tone = 'reject';
            }

            $seat = trim((string) ($mov->actor->acc_desigshort ?? ($mov->gcm_from_stage ?: ($mov->actor->acc_desig ?? 'Officer'))));
            $toLabel = $mov->toHolder->acc_name ?? $mov->gcm_to_stage;
            $actionLabel = $mov->gcm_action . (!empty($toLabel) && !in_array($actLower, ['approved', 'closed', 'rejected', 'cancelled'], true) ? " → {$toLabel}" : '');

            return [
                'actor'     => $mov->gcm_actor_name ?: ($mov->actor->acc_name ?? 'Officer'),
                'seat'      => strtoupper($seat),
                'action'    => $actionLabel,
                'tone'      => $tone,
                'timestamp' => $mov->gcm_dtg ? $mov->gcm_dtg->format('d M Y, h:i A') : '—',
                'remarks'   => $mov->gcm_remarks ?? '',
            ];
        })->all();

        // Attached files register from gen.case_attachments
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $enclosuresList = $case->attachments->map(function ($att) use ($imageExts) {
            $fn = $att->gca_filename ?: basename((string) $att->gca_path);
            $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
            $sizeKb = $att->gca_size ? number_format($att->gca_size / 1024, 1) . ' KB' : strtoupper($ext ?: 'FILE');
            $uploader = $att->uploader->acc_name ?? 'User';
            $dt = $att->created_at ? \Carbon\Carbon::parse($att->created_at)->format('d M Y') : '';

            return [
                'title'       => $att->gca_title ?: $fn,
                'filename'    => $fn,
                'source'      => 'E-Office Enclosure',
                'uploaded_by' => $uploader,
                'meta'        => trim("{$sizeKb}" . ($dt ? " • {$dt}" : '')),
                'url'         => !empty($att->gca_path) ? asset('storage/' . ltrim($att->gca_path, '/')) : null,
                'is_image'    => in_array($ext, $imageExts, true),
                'is_pdf'      => $ext === 'pdf',
            ];
        })->all();

        return view('cases.print', compact('case', 'remarksList', 'enclosuresList'));
    }
}
