<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\SupportTicketActivity;
use App\Models\Unit;
use App\Services\FileStorageService;
use App\Facades\FileStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupportTicketController extends Controller
{
    protected FileStorageService $storage;

    public function __construct(FileStorageService $storage)
    {
        $this->storage = $storage;
    }

    /**
     * User / Resolver / Apex index view
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $userAuth = strtolower(trim((string) ($user->acc_auth ?? '')));
        
        $isResolver = in_array($userArea, ['it', 'gomoe', 'godmode']) || session('impersonated_by_god');
        $isApex = in_array($userArea, ['md', 'ddg', 'dg', 'rdw']) || in_array($userAuth, ['md', 'ddg', 'dg']);

        $userRoleLabel = $this->getUserRoleLabel($user);
        $userUnitName = $user->unit?->unt_name ?? ($user->unit?->unt_namesh ?? 'Headquarters / Directorate');

        $departments = Unit::orderBy('unt_name')->get();

        // 1. RESOLVER VIEW (SO IT & GOMOE)
        if ($isResolver) {
            $query = SupportTicket::with(['user', 'unit', 'solver', 'activities']);

            if ($request->filled('unt_id') && $request->unt_id != 'all') {
                $query->where('tkt_unt_id', $request->unt_id);
            }
            if ($request->filled('module') && $request->module != 'all') {
                $query->where('tkt_module', $request->module);
            }
            if ($request->filled('priority') && $request->priority != 'all') {
                $query->where('tkt_priority', $request->priority);
            }
            if ($request->filled('type') && $request->type != 'all') {
                $query->where('tkt_type', $request->type);
            }
            if ($request->filled('search')) {
                $s = trim($request->search);
                $query->where(function ($q) use ($s) {
                    $q->where('tkt_ref', 'ILIKE', "%{$s}%")
                      ->orWhere('tkt_subject', 'ILIKE', "%{$s}%")
                      ->orWhere('tkt_description', 'ILIKE', "%{$s}%")
                      ->orWhere('tkt_user_name', 'ILIKE', "%{$s}%")
                      ->orWhere('tkt_unt_name', 'ILIKE', "%{$s}%");
                });
            }

            $allTickets = (clone $query)->orderBy('tkt_id', 'desc')->get();

            // Pinned Apex Directives
            $apexDirectives = SupportTicket::apexDirectives()
                ->active()
                ->with(['user', 'unit', 'activities'])
                ->orderBy('tkt_id', 'desc')
                ->get();

            // Active vs Resolved
            $activeTickets = $allTickets->whereIn('tkt_status', ['Open', 'In Progress', 'Returned']);
            $resolvedTickets = $allTickets->whereIn('tkt_status', ['Resolved', 'Rejected', 'Closed']);

            // Metrics
            $stats = [
                'total'       => SupportTicket::count(),
                'open'        => SupportTicket::where('tkt_status', 'Open')->count(),
                'in_progress' => SupportTicket::where('tkt_status', 'In Progress')->count(),
                'returned'    => SupportTicket::where('tkt_status', 'Returned')->count(),
                'resolved'    => SupportTicket::whereIn('tkt_status', ['Resolved', 'Closed'])->count(),
                'apex'        => SupportTicket::apexDirectives()->active()->count(),
            ];

            return view('support.tickets.index', compact(
                'user',
                'isResolver',
                'isApex',
                'userRoleLabel',
                'userUnitName',
                'departments',
                'apexDirectives',
                'activeTickets',
                'resolvedTickets',
                'stats'
            ));
        }

        // 2. APEX VIEW (MD, DDG, DG)
        if ($isApex) {
            // Apex User's own tickets / directives
            $activeTickets = SupportTicket::where('tkt_user_id', $user->acc_id)
                ->active()
                ->with(['activities', 'solver'])
                ->orderBy('tkt_id', 'desc')
                ->get();

            $resolvedTickets = SupportTicket::where('tkt_user_id', $user->acc_id)
                ->resolved()
                ->with(['activities', 'solver'])
                ->orderBy('tkt_solved_at', 'desc')
                ->get();

            // Directorate Overview (Department-wise complaints summary)
            $deptStats = DB::table('sup.tickets as t')
                ->leftJoin('cen.units as u', 'u.unt_id', '=', 't.tkt_unt_id')
                ->select(
                    DB::raw("COALESCE(u.unt_name, t.tkt_unt_name, 'General / Other') as department_name"),
                    DB::raw("COUNT(*) as total_count"),
                    DB::raw("COUNT(CASE WHEN t.tkt_status IN ('Open', 'In Progress', 'Returned') THEN 1 END) as active_count"),
                    DB::raw("COUNT(CASE WHEN t.tkt_status IN ('Resolved', 'Closed') THEN 1 END) as resolved_count"),
                    DB::raw("COUNT(CASE WHEN t.tkt_is_apex THEN 1 END) as apex_count")
                )
                ->groupBy(DB::raw("COALESCE(u.unt_name, t.tkt_unt_name, 'General / Other')"))
                ->orderByDesc('total_count')
                ->get();

            $recentDeptTickets = SupportTicket::with(['unit', 'user', 'solver'])
                ->orderBy('tkt_id', 'desc')
                ->limit(25)
                ->get();

            return view('support.tickets.index', compact(
                'user',
                'isResolver',
                'isApex',
                'userRoleLabel',
                'userUnitName',
                'departments',
                'activeTickets',
                'resolvedTickets',
                'deptStats',
                'recentDeptTickets'
            ));
        }

        // 3. REGULAR USER VIEW (Division, HR, Finance, SORD, etc.)
        $activeTickets = SupportTicket::where('tkt_user_id', $user->acc_id)
            ->active()
            ->with(['activities', 'solver'])
            ->orderBy('tkt_id', 'desc')
            ->get();

        $resolvedTickets = SupportTicket::where('tkt_user_id', $user->acc_id)
            ->resolved()
            ->with(['activities', 'solver'])
            ->orderBy('tkt_solved_at', 'desc')
            ->get();

        return view('support.tickets.index', compact(
            'user',
            'isResolver',
            'isApex',
            'userRoleLabel',
            'userUnitName',
            'departments',
            'activeTickets',
            'resolvedTickets'
        ));
    }

    /**
     * Store new complaint / suggestion ticket
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'tkt_type'        => 'required|string|in:Complaint,Suggestion,Bug Report,Feature Request',
            'tkt_module'      => 'required|string|max:60',
            'tkt_subject'     => 'required|string|max:255',
            'tkt_description' => 'required|string',
            'tkt_priority'    => 'nullable|string|in:Normal,High,Urgent',
            'attachment'      => 'nullable|file|max:20480|mimes:pdf,doc,docx,jpg,jpeg,png,webp,xlsx,csv,zip',
        ]);

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $userAuth = strtolower(trim((string) ($user->acc_auth ?? '')));
        $isApex = in_array($userArea, ['md', 'ddg', 'dg', 'rdw']) || in_array($userAuth, ['md', 'ddg', 'dg']);

        // Priority logic: Apex users get High/Urgent by default
        $priority = $isApex ? 'Urgent' : ($validated['tkt_priority'] ?? 'Normal');

        // Generate Reference: TKT-YYYY-XXXX
        $year = date('Y');
        $maxSeq = DB::table('sup.tickets')
            ->where('tkt_ref', 'LIKE', "TKT-{$year}-%")
            ->count() + 1;
        $ref = sprintf("TKT-%s-%04d", $year, $maxSeq);

        // Unit info
        $unitName = $user->unit?->unt_name ?? ($user->unit?->unt_namesh ?? 'Headquarters');

        DB::beginTransaction();
        try {
            $ticket = new SupportTicket();
            $ticket->tkt_ref         = $ref;
            $ticket->tkt_type        = $validated['tkt_type'];
            $ticket->tkt_module      = $validated['tkt_module'];
            $ticket->tkt_subject     = $validated['tkt_subject'];
            $ticket->tkt_description = $validated['tkt_description'];
            $ticket->tkt_priority    = $priority;
            $ticket->tkt_status      = 'Open';
            $ticket->tkt_user_id     = $user->acc_id;
            $ticket->tkt_user_name   = $user->acc_username;
            $ticket->tkt_user_role   = $this->getUserRoleLabel($user);
            $ticket->tkt_unt_id      = $user->acc_unt_id;
            $ticket->tkt_unt_name    = $unitName;
            $ticket->tkt_is_apex     = $isApex;
            $ticket->save();

            // Handle file upload
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $this->storage->store($file, 'sup', 'tkt-', (string) $ticket->tkt_id);
                $ticket->tkt_attachment = $path;
                $ticket->save();
            }

            // Create initial activity trail
            SupportTicketActivity::create([
                'act_tkt_id'     => $ticket->tkt_id,
                'act_user_id'    => $user->acc_id,
                'act_user_name'  => $user->acc_username,
                'act_user_role'  => $this->getUserRoleLabel($user),
                'act_action'     => 'Created',
                'act_message'    => "Ticket created with priority '{$priority}' regarding '{$ticket->tkt_module}'.",
                'act_attachment' => $ticket->tkt_attachment,
                'act_created_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('support.tickets.index')->with('success', "Ticket [{$ref}] has been submitted successfully. SO IT / Support Team has been notified.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error submitting ticket: ' . $e->getMessage());
        }
    }

    /**
     * Show ticket details & activity timeline (Modal / Detail view)
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $ticket = SupportTicket::with(['activities.user', 'solver', 'unit', 'user'])->findOrFail($id);

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isResolver = in_array($userArea, ['it', 'gomoe', 'godmode']) || session('impersonated_by_god');
        $isApex = in_array($userArea, ['md', 'ddg', 'dg', 'rdw']);

        // Check view permission (own ticket, or resolver, or apex)
        if (!$isResolver && !$isApex && $ticket->tkt_user_id !== $user->acc_id) {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'ticket'       => $ticket,
                'activities'   => $ticket->activities,
                'canAct'       => $isResolver,
                'canReply'     => ($ticket->tkt_user_id === $user->acc_id || $isResolver),
                'downloadUrl'  => $ticket->tkt_attachment ? FileStorage::url($ticket->tkt_attachment) : null,
            ]);
        }

        return view('support.tickets.show', compact('ticket', 'isResolver', 'isApex'));
    }

    /**
     * Post a reply / clarification on an open or returned ticket
     */
    public function reply(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $ticket = SupportTicket::findOrFail($id);

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isResolver = in_array($userArea, ['it', 'gomoe', 'godmode']) || session('impersonated_by_god');
        $isApex = in_array($userArea, ['md', 'ddg', 'dg', 'rdw']);

        if (!$isResolver && !$isApex && $ticket->tkt_user_id !== $user->acc_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'message'    => 'required|string',
            'attachment' => 'nullable|file|max:20480|mimes:pdf,doc,docx,jpg,jpeg,png,webp,xlsx,csv,zip',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $this->storage->store($request->file('attachment'), 'sup', 'reply-', (string) $ticket->tkt_id);
        }

        // If regular user clarifies a Returned ticket, mark it back as In Progress
        if ($ticket->tkt_status === 'Returned' && $ticket->tkt_user_id === $user->acc_id) {
            $ticket->tkt_status = 'In Progress';
            $ticket->save();
        }

        SupportTicketActivity::create([
            'act_tkt_id'     => $ticket->tkt_id,
            'act_user_id'    => $user->acc_id,
            'act_user_name'  => $user->acc_username,
            'act_user_role'  => $this->getUserRoleLabel($user),
            'act_action'     => ($ticket->tkt_user_id === $user->acc_id) ? 'Clarified' : 'Commented',
            'act_message'    => $validated['message'],
            'act_attachment' => $attachmentPath,
            'act_created_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Message / Clarification posted successfully.');
    }

    /**
     * SO IT & GOMOE Resolver Action Handler
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $isResolver = in_array($userArea, ['it', 'gomoe', 'godmode']) || session('impersonated_by_god');

        if (!$isResolver) {
            abort(403, 'Only SO IT and System Administrators can update ticket resolution status.');
        }

        $validated = $request->validate([
            'status'          => 'required|string|in:In Progress,Returned,Resolved,Rejected,Closed',
            'resolution_note' => 'required|string|min:3',
            'attachment'      => 'nullable|file|max:20480|mimes:pdf,doc,docx,jpg,jpeg,png,webp,xlsx,csv,zip',
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $oldStatus = $ticket->tkt_status;
        $newStatus = $validated['status'];

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $this->storage->store($request->file('attachment'), 'sup', 'res-', (string) $ticket->tkt_id);
        }

        DB::beginTransaction();
        try {
            $ticket->tkt_status = $newStatus;

            if (in_array($newStatus, ['Resolved', 'Closed', 'Rejected'])) {
                $ticket->tkt_solved_by       = $user->acc_id;
                $ticket->tkt_solved_by_name  = $user->acc_username;
                $ticket->tkt_solved_at       = now();
                $ticket->tkt_resolution_note = $validated['resolution_note'];
            }

            $ticket->save();

            SupportTicketActivity::create([
                'act_tkt_id'     => $ticket->tkt_id,
                'act_user_id'    => $user->acc_id,
                'act_user_name'  => $user->acc_username,
                'act_user_role'  => 'SO IT / Support Desk',
                'act_action'     => $newStatus,
                'act_message'    => $validated['resolution_note'],
                'act_attachment' => $attachmentPath,
                'act_created_at' => now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', "Ticket [{$ticket->tkt_ref}] status successfully updated to [{$newStatus}].");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update ticket: ' . $e->getMessage());
        }
    }

    /**
     * Helper to get user friendly role label
     */
    private function getUserRoleLabel($user): string
    {
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $auth = strtolower(trim((string) ($user->acc_auth ?? '')));

        if (session('impersonated_by_god') || $area === 'gomoe' || $area === 'godmode') return 'GOMOE Administrator';
        if ($area === 'it') return 'SO IT';
        if ($area === 'dg' || $auth === 'dg') return 'Director General (DG)';
        if ($area === 'ddg' || $auth === 'ddg') return 'Deputy Director General (DDG)';
        if ($area === 'md' || $auth === 'md') return 'Managing Director (MD)';
        if ($area === 'hr') return 'HR Directorate';
        if ($area === 'fin') return 'Finance Directorate';
        if ($area === 'sord') return 'SORD Directorate';
        if ($user->unit) return $user->unit->unt_name . ' (' . ($user->unit->unt_namesh ?? 'Division') . ')';

        return 'User (' . strtoupper($user->acc_username) . ')';
    }
}
