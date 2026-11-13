<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class DocMprController extends Controller
{
    // --- 1. VIEW MPR ---
    public function view($projectId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($projectId);
        
        $document = null;
        $previousMprs = collect();

        if (\Illuminate\Support\Facades\Schema::hasTable('doc.documents')) {
            // The MPR that is still travelling; otherwise the most recent one.
            $document = $this->openDocumentFor($projectId)
                ?: Document::where('prj_id', $projectId)
                           ->where('doc_type', 'MPR')
                           ->orderByDesc('doc_period')->orderByDesc('doc_seq')->orderByDesc('doc_id')
                           ->first();

            if ($document) {
                $document->load([
                    'versions' => function ($q) { $q->orderBy('ver_id', 'desc'); },
                    'versions.actor.role', 'currentOwner.role', 'creator.role',
                    'history.fromUser.role', 'history.toUser.role',
                ]);
            }

            // Everything raised before - the legacy monthly MPRs included.
            $previousMprs = Document::where('prj_id', $projectId)
                ->where('doc_type', 'MPR')
                ->when($document, fn ($q) => $q->where('doc_id', '<>', $document->doc_id))
                ->orderByDesc('doc_period')->orderByDesc('doc_seq')->orderByDesc('doc_id')
                ->limit(60)
                ->get();
        }

        // Permissions & Organizational Seat Logic
        $context = \App\Services\Auth\UserAccessContext::forUser($user);
        $isViewer = $context->isViewer();
        $isSeatOwner = ($user->acc_unt_id == $project->prj_unt_id);

        $isEditable = false;
        $isReturned = false;
        $isFinalized = false;

        if ($document) {
            // Document is editable by current owner OR active seat holder for the project unit
            if (($document->current_owner_id == $user->acc_id || $isSeatOwner) && !$isViewer) {
                if ($document->status === 'Draft' || $document->status === 'Returned') {
                    $isEditable = true;
                }
            }

            if ($document->status === 'Returned') {
                $isReturned = true;
            }

            if (in_array($document->status, ['Approved', 'Forwarded to MD', 'Finalized'])) {
                $isEditable = false;
                $isFinalized = true;
            }
        } elseif ($isSeatOwner && !$isViewer) {
            // New draft MPR can be created by seat holder
            $isEditable = true;
        }

        // A fresh MPR may be started once nothing is travelling - this is what allows
        // a second (or third) MPR inside the same month.
        $canStartNew = $isSeatOwner && ! $isViewer
            && $this->mprAllowedForProject($project)
            && (! $document || ! $document->isOpen());

        $blockedReason = $this->mprAllowedForProject($project)
            ? null
            : 'This project is closed. MPR entry for closed projects is switched off in Admin → Settings → Workflows (MPR).';

        return view('projects.viewmpr', compact(
            'project', 'document', 'isEditable', 'isReturned', 'isFinalized',
            'previousMprs', 'canStartNew', 'blockedReason'
        ));
    }

    // --- 2. SORD REVIEW ---
    public function sordReview($projectId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($projectId);

        $document = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('doc.documents')) {
            $document = Document::where('prj_id', $projectId)
                                ->with('creator.role', 'versions.actor.role', 'currentOwner')
                                ->first();
        }

        if (!$document) {
             return redirect()->route('sord.all_projects')->with('error', 'Document System Offline or Document not found.');
        }

        // EDITABLE CHECK: Sirf tab edit hoga jab file mere paas ho
        $isEditable = ($document->current_owner_id == $user->acc_id);

        $versions = $document->versions()->orderBy('ver_id', 'desc')->take(2)->get();
        $latestVersion = $versions->first(); 
        $divisionVersion = $versions->count() > 1 ? $versions->get(1) : $versions->first();

        return view('sord.review_mpr', compact('project', 'document', 'latestVersion', 'divisionVersion', 'isEditable'));
    }

    // --- 3. STORE: stage aware MPR workflow (legacy prj_prghistory behaviour) ---
    public function store(Request $request, $projectId)
    {
        $user = Auth::user();
        $context = \App\Services\Auth\UserAccessContext::forUser($user);
        if ($context->isViewer()) {
            abort(403, 'Unauthorized. Viewer accounts cannot submit or modify MPRs.');
        }

        $project = Project::findOrFail($projectId);

        if (! $this->mprAllowedForProject($project)) {
            return redirect()->back()->with('error', 'This project is closed and MPR entry for closed projects is switched off.');
        }

        $request->validate([
            'action'      => 'required|string|max:30',
            'mpr_content' => 'nullable|string',
            'remarks'     => 'nullable|string|max:2000',
            'mpr_month'   => 'nullable|string|max:10',
        ]);

        $action = strtolower(trim((string) $request->input('action')));
        $actorName = $this->nameOf($user);

        DB::beginTransaction();

        try {
            $document = $this->openDocumentFor($projectId);

            // Nothing travelling? Then this starts a brand new MPR. The period comes from
            // the month picker, and a second MPR inside the same month simply gets seq 2.
            if (! $document) {
                $period = $this->periodFromRequest($request);
                $seq = $this->nextSeq($projectId, $period);

                $document = Document::create([
                    'prj_id'           => $projectId,
                    'doc_type'         => 'MPR',
                    'doc_ref_no'       => 'MPR/' . ($project->prj_code ?: $projectId) . '/' . $period . '/' . $seq,
                    'creator_id'       => $user->acc_id,
                    'current_owner_id' => $user->acc_id,
                    'status'           => 'Draft',
                    'doc_period'       => $period,
                    'doc_period_label' => Carbon::parse($period . '-01')->format('M y'),
                    'doc_seq'          => $seq,
                    'doc_stage'        => 'ProjectOfficer',
                    'doc_unt_id'       => $project->prj_unt_id,
                    'doc_creator_name' => $actorName,
                    'doc_owner_name'   => $actorName,
                ]);
            }

            $stage = $document->doc_stage ?: 'ProjectOfficer';

            // 1. Every submit is a version, exactly like the legacy "copy on edit".
            $lastVer = $document->versions()->orderBy('ver_id', 'desc')->first();
            $newVerNo = number_format((float) ($lastVer->version_no ?? 0) + 0.1, 1);

            DocumentVersion::create([
                'doc_id'          => $document->doc_id,
                'version_no'      => $newVerNo,
                'content_data'    => $request->except(['_token', 'action', 'remarks']),
                'remarks'         => $request->remarks,
                'action_by'       => $user->acc_id,
                'action_date'     => now(),
                'ver_stage'       => $stage,
                'ver_action'      => ucfirst($action),
                'ver_status'      => $document->status,
                'ver_author_name' => $actorName,
            ]);

            // 2. Move the file.
            $update = ['updated_at' => now(), 'doc_owner_name' => $actorName];
            $note = $request->remarks;

            switch ($action) {
                case 'save':
                    $update['current_owner_id'] = $user->acc_id;
                    $this->logHistory($document->doc_id, $user->acc_id, $user->acc_id, 'Saved', $note ?: 'Draft saved', $stage, $actorName, $actorName);
                    break;

                case 'forward':
                    if ($stage === 'ProjectOfficer') {
                        $sordUser = app(\App\Services\Auth\OfficerReplacementService::class)->activeSordOccupant()
                            ?? $this->getSordUser();
                        if (! $sordUser) {
                            throw new \Exception('SORD user not found.');
                        }
                        $update['current_owner_id'] = $sordUser->acc_id;
                        $update['status'] = 'Pending Review';
                        $update['doc_stage'] = 'SORD';
                        $update['doc_owner_name'] = $this->nameOf($sordUser);
                        $this->logHistory($document->doc_id, $user->acc_id, $sordUser->acc_id, 'Forwarded', $note ?: 'Sent to SORD', 'SORD', $actorName, $this->nameOf($sordUser));
                    } elseif ($stage === 'SORD' && $this->mdStageEnabled()) {
                        $mdUser = $this->getMdUser();
                        $update['current_owner_id'] = $mdUser->acc_id ?? $user->acc_id;
                        $update['status'] = 'Forwarded to MD';
                        $update['doc_stage'] = 'MD';
                        $update['doc_owner_name'] = $mdUser ? $this->nameOf($mdUser) : $actorName;
                        $this->logHistory($document->doc_id, $user->acc_id, $mdUser->acc_id ?? $user->acc_id, 'Forwarded MD', $note ?: 'Forwarded to MD', 'MD', $actorName, $mdUser ? $this->nameOf($mdUser) : 'MD');
                    } else {
                        $update = array_merge($update, $this->finalizePayload($document, $project, $request, $user));
                        $this->logHistory($document->doc_id, $user->acc_id, $user->acc_id, 'Finalized', $note ?: 'MPR finalized', 'Accepted', $actorName, $actorName);
                    }
                    break;

                case 'return':
                    if ($stage === 'MD') {
                        $sordUser = app(\App\Services\Auth\OfficerReplacementService::class)->activeSordOccupant() ?? $this->getSordUser();
                        $update['current_owner_id'] = $sordUser->acc_id ?? $document->creator_id;
                        $update['status'] = 'Pending Review';
                        $update['doc_stage'] = 'SORD';
                        $update['doc_owner_name'] = $sordUser ? $this->nameOf($sordUser) : 'SORD';
                        $this->logHistory($document->doc_id, $user->acc_id, $sordUser->acc_id ?? $document->creator_id, 'Returned', $note ?: 'Returned to SORD', 'SORD', $actorName, $sordUser ? $this->nameOf($sordUser) : 'SORD');
                    } else {
                        $update['current_owner_id'] = $document->creator_id;
                        $update['status'] = 'Returned';
                        $update['doc_stage'] = 'ProjectOfficer';
                        $update['doc_owner_name'] = $document->doc_creator_name;
                        $this->logHistory($document->doc_id, $user->acc_id, $document->creator_id, 'Returned', $note ?: 'Returned to Division', 'ProjectOfficer', $actorName, $document->doc_creator_name ?: 'Division');
                    }
                    break;

                case 'finalize':
                    if ($stage === 'SORD' && $this->mdStageEnabled()) {
                        $mdUser = $this->getMdUser();
                        $update['current_owner_id'] = $mdUser->acc_id ?? $user->acc_id;
                        $update['status'] = 'Forwarded to MD';
                        $update['doc_stage'] = 'MD';
                        $update['doc_owner_name'] = $mdUser ? $this->nameOf($mdUser) : $actorName;
                        $this->logHistory($document->doc_id, $user->acc_id, $mdUser->acc_id ?? $user->acc_id, 'Forwarded MD', $note ?: 'Forwarded to MD', 'MD', $actorName, $mdUser ? $this->nameOf($mdUser) : 'MD');
                    } else {
                        $update = array_merge($update, $this->finalizePayload($document, $project, $request, $user));
                        $this->logHistory($document->doc_id, $user->acc_id, $user->acc_id, 'Finalized', $note ?: 'MPR finalized', 'Accepted', $actorName, $actorName);
                    }
                    break;

                case 'cancel':
                    $update['status'] = 'Cancelled';
                    $update['doc_stage'] = 'ProjectOfficer';
                    $this->logHistory($document->doc_id, $user->acc_id, $user->acc_id, 'Cancelled', $note ?: 'MPR cancelled', 'ProjectOfficer', $actorName, $actorName);
                    break;

                default:
                    throw new \Exception('Unknown action: ' . $action);
            }

            DB::table('doc.documents')->where('doc_id', $document->doc_id)->update($update);

            DB::commit();

            return redirect()->back()->with('success', 'Action processed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
        }
    }

    // =====================================================================
    // MPR WORKFLOW HELPERS
    // =====================================================================

    /** The MPR of this project that is still travelling (Draft / Returned / with SORD / with MD). */
    private function openDocumentFor($projectId): ?Document
    {
        return Document::where('prj_id', $projectId)
            ->where('doc_type', 'MPR')
            ->where(function ($q) {
                $q->whereNull('doc_is_legacy')->orWhere('doc_is_legacy', false);
            })
            ->whereNotIn('status', ['Finalized', 'Accepted', 'Cancelled', 'Closed'])
            ->orderByDesc('doc_period')->orderByDesc('doc_seq')->orderByDesc('doc_id')
            ->first();
    }

    /** Second / third MPR of the same month simply gets the next sequence. */
    private function nextSeq($projectId, string $period): int
    {
        return ((int) Document::where('prj_id', $projectId)
            ->where('doc_type', 'MPR')
            ->where('doc_period', $period)
            ->max('doc_seq')) + 1;
    }

    private function periodFromRequest(Request $request): string
    {
        $month = trim((string) $request->input('mpr_month'));
        if ($month !== '') {
            try {
                return Carbon::parse($month . '-01')->format('Y-m');
            } catch (\Throwable $e) {
                // fall through
            }
        }
        return now()->format('Y-m');
    }

    /** Legacy parity: the finalized progress text goes back to the project remarks. */
    private function finalizePayload(Document $document, Project $project, Request $request, $user): array
    {
        $text = trim((string) $request->input('mpr_content'));
        if ($text === '') {
            $latest = $document->versions()->orderBy('ver_id', 'desc')->first();
            $text = trim((string) (($latest->content_data['mpr_content'] ?? null) ?: ($latest->content_data['progress'] ?? '')));
        }

        if ($text !== '') {
            DB::table('prj.projects')->where('prj_id', $project->prj_id)->update(['prj_rem' => $text]);
        }

        return [
            'status'           => 'Finalized',
            'doc_stage'        => 'Accepted',
            'doc_finalized_at' => now(),
            'current_owner_id' => $user->acc_id,
        ];
    }

    private function mdStageEnabled(): bool
    {
        return (bool) \App\Models\SystemSetting::get('mpr_md_stage_enabled', false);
    }

    private function mprAllowedForProject(Project $project): bool
    {
        $status = strtolower(trim((string) $project->prj_status));
        $isClosed = in_array($status, ['completed', 'cancelled', 'closed'], true);

        if (! $isClosed) {
            return true;
        }

        return (bool) \App\Models\SystemSetting::get('mpr_allow_closed_projects', false);
    }

    private function getMdUser()
    {
        return User::whereRaw("upper(trim(coalesce(acc_desigshort,''))) in ('MD','MD RDW')")
            ->orWhereRaw("upper(coalesce(acc_desig,'')) like '%MANAGING DIRECTOR%'")
            ->first();
    }

    private function nameOf($user): string
    {
        if (! $user) {
            return 'System';
        }
        return (string) ($user->acc_desigshort ?: ($user->acc_name ?: ($user->acc_username ?? 'User')));
    }

    private function getSordUser() {
        return User::whereHas('unit', function($q){ $q->where('unt_area', 'prjrdw'); })->first();
    }

    private function logHistory($docId, $from, $to, $action, $notes, $stage = null, $fromName = null, $toName = null) {
        DB::table('doc.document_history')->insert([
            'doc_id' => $docId, 'from_user_id' => $from, 'to_user_id' => $to,
            'action_type' => $action, 'notes' => $notes, 'created_at' => now(),
            'stage' => $stage, 'from_name' => $fromName, 'to_name' => $toName,
        ]);
    }

    public function sordInbox() {
        $user = Auth::user();
        $documents = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('doc.documents')) {
            $documents = Document::where('current_owner_id', $user->acc_id)
                                 ->where('status', '!=', 'Approved') 
                                 ->with(['project', 'creator.role', 'creator.unit']) 
                                 ->orderBy('updated_at', 'desc')
                                 ->get();
        }
        return view('sord.mpr_inbox', compact('documents'));
    }
}