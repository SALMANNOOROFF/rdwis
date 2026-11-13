<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\MprRouteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * MPR exactly as the legacy Access system ran it: same table (prj.prghistory),
 * same columns, same statuses (Draft / Under Finalization / Under Review /
 * Finalized / Edited / Cancelled) and the same route from cen.globalvars.
 *
 * The one addition is prj.prghistory_trail: every hand-over, return and its
 * remarks is preserved as its own row instead of only the pgh_trail string.
 */
class MprLegacyController extends Controller
{
    /** Statuses that mean the MPR is no longer travelling. */
    private const CLOSED = ['finalized', 'cancelled', 'edited'];

    public function __construct(private MprRouteService $route) {}

    // =====================================================================
    // SCREEN
    // =====================================================================

    public function view($projectId)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $project = Project::withoutGlobalScope('published')->where('prj_id', $projectId)->firstOrFail();

        $current = $this->openRow($projectId);

        $history = DB::table('prj.prghistory')
            ->where('pgh_xprj_id', $projectId)
            ->when($current, fn ($q) => $q->where('pgh_id', '<>', $current->pgh_id))
            ->orderByDesc('pgh_dtg')->orderByDesc('pgh_id')
            ->limit(60)
            ->get();

        $trail = DB::table('prj.prghistory_trail')
            ->where('pgt_prj_id', $projectId)
            ->orderByDesc('pgt_dtg')->orderByDesc('pgt_id')
            ->limit(120)
            ->get();

        $rights = $this->rights($user, $project, $current);

        return view('projects.viewmpr', [
            'project'      => $project,
            'current'      => $current,
            'history'      => $history,
            'trail'        => $trail,
            'rights'       => $rights,
            'routeService' => $this->route,
            'myLevel'      => (int) ($user->acc_level ?? 0),
            'group'        => $this->route->currentGroup(),
        ]);
    }

    // =====================================================================
    // ACTIONS  (legacy cmdCreate / cmdEdit / cmdCancel / Forward / Return / Finalize)
    // =====================================================================

    public function action(Request $request, $projectId)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate([
            'action'       => 'required|string|max:20',
            'pgh_progress' => 'nullable|string',
            'remarks'      => 'nullable|string|max:2000',
        ]);

        $project = Project::withoutGlobalScope('published')->where('prj_id', $projectId)->firstOrFail();
        $action  = strtolower(trim((string) $request->input('action')));
        $remarks = trim((string) $request->input('remarks')) ?: null;

        $current = $this->openRow($projectId);
        $rights  = $this->rights($user, $project, $current);

        if (! ($rights[$action] ?? false)) {
            return back()->with('error', 'This action is not available to you for this MPR.');
        }

        try {
            DB::transaction(function () use ($action, $request, $user, $project, $current, $remarks) {
                match ($action) {
                    'create'   => $this->doCreate($user, $project, $remarks),
                    'save'     => $this->doSave($user, $current, $request, $remarks),
                    'edit'     => $this->doEdit($user, $current, $remarks),
                    'cancel'   => $this->doClose($user, $current, 'Cancelled', $remarks),
                    'finalize' => $this->doFinalize($user, $project, $current, $request, $remarks),
                    'forward'  => $this->doMove($user, $current, 'Forward', $request, $remarks),
                    'return'   => $this->doMove($user, $current, 'Return', $request, $remarks),
                    default    => throw new \RuntimeException('Unknown action'),
                };
            });
        } catch (Throwable $e) {
            return back()->with('error', 'MPR action failed: ' . $e->getMessage());
        }

        return redirect()->route('mpr.view', $project->prj_id)
            ->with('success', 'MPR ' . $action . ' completed.');
    }

    // =====================================================================
    // LEGACY OPERATIONS
    // =====================================================================

    /** cmdCreate_Click + prj_prghistory_create_new */
    private function doCreate($user, Project $project, ?string $remarks): void
    {
        $level = (int) ($user->acc_level ?? $this->route->first());

        $pghId = DB::table('prj.prghistory')->insertGetId([
            'pgh_xprj_id'   => $project->prj_id,
            'pgh_dtg'       => now(),
            'pgh_progress'  => mb_substr((string) ($project->prj_rem ?? ''), 0, 2000),
            'pgh_author'    => $this->desigOf($user),
            'pgh_status'    => 'Draft',
            'pgh_level'     => $level,
            'pgh_group'     => $this->route->currentGroup(),
            'pgh_trail'     => '-' . $level . '-',
            'pgh_underedit' => true,
        ], 'pgh_id');

        $this->trail($pghId, $project->prj_id, 'Created', null, $level, $user, null, 'Draft', $remarks);
    }

    /** Text edit while the row is under edit at my level. */
    private function doSave($user, $current, Request $request, ?string $remarks): void
    {
        DB::table('prj.prghistory')->where('pgh_id', $current->pgh_id)->update([
            'pgh_progress' => (string) $request->input('pgh_progress'),
            'pgh_dtg'      => now(),
        ]);

        $this->trail($current->pgh_id, $current->pgh_xprj_id, 'Saved', $current->pgh_level, $current->pgh_level, $user, $current->pgh_status, $current->pgh_status, $remarks);
    }

    /** cmdEdit_Click: close this one as "Edited" and continue on a fresh copy. */
    private function doEdit($user, $current, ?string $remarks): void
    {
        $oldStatus = $current->pgh_status;

        DB::table('prj.prghistory')->where('pgh_id', $current->pgh_id)->update([
            'pgh_status'    => 'Edited',
            'pgh_closedtg'  => now(),
            'pgh_underedit' => false,
        ]);

        $newId = DB::table('prj.prghistory')->insertGetId([
            'pgh_xprj_id'        => $current->pgh_xprj_id,
            'pgh_dtg'            => now(),
            'pgh_progress'       => $current->pgh_progress,
            'pgh_author'         => $this->desigOf($user),
            'pgh_status'         => $oldStatus,
            'pgh_level'          => $current->pgh_level,
            'pgh_group'          => $current->pgh_group,
            'pgh_trail'          => $current->pgh_trail,
            'pgh_trailinherited' => $current->pgh_trail,
            'pgh_underedit'      => true,
        ], 'pgh_id');

        $this->trail($newId, $current->pgh_xprj_id, 'Edited', $current->pgh_level, $current->pgh_level, $user, $oldStatus, $oldStatus, $remarks);
    }

    /** cmdCancel_Click */
    private function doClose($user, $current, string $status, ?string $remarks): void
    {
        DB::table('prj.prghistory')->where('pgh_id', $current->pgh_id)->update([
            'pgh_status'    => $status,
            'pgh_closedtg'  => now(),
            'pgh_underedit' => false,
        ]);

        $this->trail($current->pgh_id, $current->pgh_xprj_id, $status, $current->pgh_level, $current->pgh_level, $user, $current->pgh_status, $status, $remarks);
    }

    /** cmdFinalize_Click - also copies the text back into the project remarks. */
    private function doFinalize($user, Project $project, $current, Request $request, ?string $remarks): void
    {
        $text = $request->filled('pgh_progress') ? (string) $request->input('pgh_progress') : (string) $current->pgh_progress;

        DB::table('prj.prghistory')->where('pgh_id', $current->pgh_id)->update([
            'pgh_progress'  => $text,
            'pgh_status'    => 'Finalized',
            'pgh_closedtg'  => now(),
            'pgh_underedit' => false,
        ]);

        DB::table('prj.projects')->where('prj_id', $project->prj_id)->update(['prj_rem' => $text]);

        $this->trail($current->pgh_id, $current->pgh_xprj_id, 'Finalized', $current->pgh_level, $current->pgh_level, $user, $current->pgh_status, 'Finalized', $remarks);
    }

    /** MoveProgress("Forward" | "Return") */
    private function doMove($user, $current, string $move, Request $request, ?string $remarks): void
    {
        $from = (int) $current->pgh_level;
        $to   = $move === 'Forward' ? $this->route->next($from) : $this->route->prev($from);

        if (! $to) {
            throw new \RuntimeException('There is no ' . strtolower($move) . ' stop on the MPR route.');
        }

        $status = $move === 'Forward' ? 'Under Finalization' : 'Under Review';

        $update = [
            'pgh_status'    => $status,
            'pgh_level'     => $to,
            'pgh_trail'     => rtrim((string) $current->pgh_trail, '-') . '-' . $to . '-',
            'pgh_underedit' => false,
        ];

        if ($current->pgh_underedit) {
            $update['pgh_dtg'] = now();
        }
        if ($request->filled('pgh_progress')) {
            $update['pgh_progress'] = (string) $request->input('pgh_progress');
        }

        DB::table('prj.prghistory')->where('pgh_id', $current->pgh_id)->update($update);

        $this->trail($current->pgh_id, $current->pgh_xprj_id, $move === 'Forward' ? 'Forwarded' : 'Returned', $from, $to, $user, $current->pgh_status, $status, $remarks);
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    /** The MPR of this project that is still travelling. */
    private function openRow($projectId)
    {
        return DB::table('prj.prghistory')
            ->where('pgh_xprj_id', $projectId)
            ->whereRaw('LOWER(TRIM(pgh_status)) NOT IN (?, ?, ?)', self::CLOSED)
            ->orderByDesc('pgh_dtg')->orderByDesc('pgh_id')
            ->first();
    }

    /**
     * Legacy SetupForm(): which buttons this user gets, based on the route stop
     * they sit on, the stop the MPR sits on, and whether they are an approver.
     */
    private function rights($user, Project $project, $current): array
    {
        $myLevel  = (int) ($user->acc_level ?? 0);
        $auth     = strtolower(trim((string) ($user->acc_auth ?? '')));
        $approver = in_array($auth, ['approver'], true);
        $editor   = in_array($auth, ['approver', 'editor'], true);
        $seat     = ((int) $user->acc_unt_id === (int) $project->prj_unt_id);

        $rights = [
            'create' => false, 'save' => false, 'edit' => false, 'cancel' => false,
            'forward' => false, 'return' => false, 'finalize' => false,
            'at_my_level' => false, 'is_first' => $this->route->isFirst($myLevel),
            'is_last' => $this->route->isLast($myLevel), 'on_route' => $this->route->isOnRoute($myLevel),
        ];

        if (! $editor) {
            return $rights;
        }

        // Nothing travelling: the first stop of the route may raise a new MPR,
        // unless the project is closed and that is switched off in settings.
        if (! $current) {
            $rights['create'] = $this->route->isFirst($myLevel) && $seat && $this->mprAllowedFor($project);
            return $rights;
        }

        if ((int) $current->pgh_level !== $myLevel) {
            return $rights;   // the file is not on my desk
        }

        $rights['at_my_level'] = true;
        $rights['save'] = (bool) $current->pgh_underedit;
        $rights['edit'] = ! $current->pgh_underedit;

        if ($this->route->isFirst($myLevel)) {
            $rights['cancel'] = true;
        }

        if ($approver) {
            $rights['return']   = ! $this->route->isFirst($myLevel);
            $rights['forward']  = ! $this->route->isLast($myLevel);
            $rights['finalize'] = $this->route->isLast($myLevel);
        }

        return $rights;
    }

    private function trail($pghId, $prjId, string $action, ?int $fromLevel, ?int $toLevel, $user, ?string $before, ?string $after, ?string $remarks): void
    {
        DB::table('prj.prghistory_trail')->insert([
            'pgt_pgh_id'        => $pghId,
            'pgt_prj_id'        => $prjId,
            'pgt_group'         => $this->route->currentGroup(),
            'pgt_action'        => $action,
            'pgt_from_level'    => $fromLevel,
            'pgt_from_desig'    => $fromLevel ? $this->desigOf($user) : null,
            'pgt_from_acc_id'   => $user->acc_id ?? null,
            'pgt_to_level'      => $toLevel,
            'pgt_to_desig'      => ($toLevel === $fromLevel)
                                        ? $this->desigOf($user)
                                        : ($this->route->desigForLevel($toLevel) ?: ('level ' . $toLevel)),
            'pgt_to_acc_id'     => null,
            'pgt_status_before' => $before,
            'pgt_status_after'  => $after,
            'pgt_remarks'       => $remarks,
            'pgt_dtg'           => now(),
        ]);
    }

    /** Completed / cancelled projects only get an MPR when settings allow it. */
    private function mprAllowedFor(Project $project): bool
    {
        $status = strtolower(trim((string) $project->prj_status));
        if (! in_array($status, ['completed', 'cancelled', 'closed'], true)) {
            return true;
        }

        return (bool) \App\Models\SystemSetting::get('mpr_allow_closed_projects', false);
    }

    private function desigOf($user): string
    {
        return (string) ($user->acc_desigshort ?: ($user->acc_name ?: ($user->acc_username ?? 'User')));
    }
}
