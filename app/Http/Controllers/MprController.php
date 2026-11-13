<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\DocumentHistory;
use App\Models\User;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class MprController extends Controller
{
    // 1. SORD Inbox (UPDATED: Organizational seat + Returned files)
    public function sordInbox()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $context = \App\Services\Auth\UserAccessContext::forUser($user);
        if (!$context->isSuperAdmin() && !$context->isSord()) {
            abort(403, 'Unauthorized. SORD access required.');
        }

        return $this->levelInbox($user, 'MPR Inbox — SO R&D');
    }

    /**
     * MPR inbox for whichever route stop the officer sits on (legacy engine).
     * Lists the prj.prghistory rows parked at this level.
     */
    private function levelInbox($user, string $title)
    {
        $route = app(\App\Services\MprRouteService::class);
        $myLevel = (int) ($user->acc_level ?? 0);

        $rows = DB::table('prj.prghistory as h')
            ->join('prj.projects as p', 'p.prj_id', '=', 'h.pgh_xprj_id')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'p.prj_unt_id')
            ->where('h.pgh_level', $myLevel)
            ->whereRaw("LOWER(TRIM(h.pgh_status)) NOT IN ('finalized','cancelled','edited')")
            ->orderByDesc('h.pgh_dtg')->orderByDesc('h.pgh_id')
            ->select([
                'h.pgh_id', 'h.pgh_xprj_id', 'h.pgh_group', 'h.pgh_status', 'h.pgh_author',
                'h.pgh_dtg', 'h.pgh_trail', 'h.pgh_progress',
                'p.prj_code', 'p.prj_title', 'u.unt_namesh',
            ])
            ->get();

        $trail = DB::table('prj.prghistory_trail')
            ->whereIn('pgt_pgh_id', $rows->pluck('pgh_id'))
            ->orderByDesc('pgt_dtg')
            ->get()
            ->groupBy('pgt_pgh_id');

        return view('SORD.mpr_inbox_legacy', [
            'rows'    => $rows,
            'trail'   => $trail,
            'title'   => $title,
            'myLevel' => $myLevel,
            'route'   => $route,
        ]);
    }

    // 2. Review Page (UPDATED: Locking Logic)
    public function reviewMpr($doc_id)
    {
        $document = Document::with(['project', 'versions', 'history.fromUser', 'history.toUser', 'creator', 'creator.unit'])->findOrFail($doc_id);
        
        // Fetch absolute latest version to show current state
        $latestVersion = $document->versions()->orderBy('ver_id', 'desc')->first();

        $divisionVersion = $document->versions()
                                    ->where('action_by', $document->creator_id)
                                    ->orderBy('ver_id', 'desc')
                                    ->first();

        $sordVersion = $document->versions()
                                ->where('action_by', Auth::id())
                                ->orderBy('ver_id', 'desc')
                                ->first();

        // LOCKING LOGIC:
        // Agar status 'Returned' hai ya 'Finalized' hai, to page LOCKED (false) hoga
        $isEditable = true;
        if ($document->status == 'Returned' || $document->status == 'Finalized' || $document->status == 'Forwarded to MD') {
            $isEditable = false; 
        }

        return view('SORD.review_mpr', compact('document', 'divisionVersion', 'sordVersion', 'latestVersion', 'isEditable'));
    }

    // 3. Action Handler (With Server-side Permission Check)
    public function sordAction(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $docId = $request->doc_id;
        $action = $request->action;

        $docForCheck = Document::find($docId);
        $atMdStage = $docForCheck && ($docForCheck->doc_stage === 'MD' || $docForCheck->status === 'Forwarded to MD');

        $context = \App\Services\Auth\UserAccessContext::forUser($user);
        $mayAct = $context->isSuperAdmin()
            || \App\Services\Auth\RolePermissionMap::hasPermission($user, \App\Services\Auth\PermissionRegistry::MPR_REVIEW)
            || ($atMdStage && $context->isMd());

        if (! $mayAct) {
            abort(403, 'Unauthorized to perform this MPR action.');
        }

        return DB::transaction(function () use ($request, $user, $docId, $action) {
            
            $document = Document::findOrFail($docId);
            
            // Version Create
            $lastVer = DocumentVersion::where('doc_id', $docId)->orderBy('ver_id', 'desc')->first();
            $newVerNo = $lastVer ? $lastVer->version_no + 0.1 : 1.0;

            // FIX: Preserve existing content if SORD is just adding remarks/status change
            // SORD actions typically don't edit the progress fields, so $request->physical_progress might be null.
            // We must merge with previous data to avoid overwriting with nulls.
            $existingContent = $lastVer ? $lastVer->content_data : [];

            // Preserve all existing content (like mpr_month) and update mpr_content if provided
            $contentData = $existingContent;
            if ($request->has('mpr_content')) {
                $contentData['mpr_content'] = $request->mpr_content;
            }

            // Filter out nulls so we don't save {"mpr_content": null} if it's empty
            $contentData = array_filter($contentData, function($v) { return !is_null($v) && $v !== ''; });

            DocumentVersion::create([
                'doc_id' => $docId,
                'version_no' => $newVerNo,
                'content_data' => $contentData,
                'remarks' => $request->remarks,
                'action_by' => $user->acc_id,
                'action_date' => now()
            ]);

            $stage = $document->doc_stage ?: 'SORD';
            $mdEnabled = (bool) \App\Models\SystemSetting::get('mpr_md_stage_enabled', false);

            if ($action == 'save') {
                $document->status = ($stage === 'MD') ? 'Forwarded to MD' : 'Under Review by SORD';
                $document->save();
                $msg = 'Draft saved successfully.';
            }
            elseif ($action == 'forward' && $stage === 'SORD' && $mdEnabled) {
                // MD stage is switched on: hand the file to MD instead of finalizing here.
                $md = \App\Models\User::whereRaw("upper(trim(coalesce(acc_desigshort,''))) in ('MD','MD RDW')")
                        ->orWhereRaw("upper(coalesce(acc_desig,'')) like '%MANAGING DIRECTOR%'")
                        ->first();

                $document->current_owner_id = $md->acc_id ?? $user->acc_id;
                $document->status = 'Forwarded to MD';
                $document->doc_stage = 'MD';
                $document->save();

                DocumentHistory::create([
                    'doc_id' => $docId,
                    'from_user_id' => $user->acc_id,
                    'to_user_id' => $md->acc_id ?? $user->acc_id,
                    'action_type' => 'Forwarded MD',
                    'notes' => $request->remarks,
                    'created_at' => now(),
                    'stage' => 'MD',
                ]);
                $msg = 'MPR forwarded to MD.';
            }
            elseif ($action == 'forward') {
                // Forward Logic...
                // FIX: Don't set owner to 0 as it violates FK. Keep it as current user (SORD) or use NULL if allowed.
                // Assuming keeping it as SORD user is safer for now, status 'Finalized' will prevent edits.
                $document->current_owner_id = $user->acc_id;
                $document->status = 'Finalized';
                $document->doc_stage = 'Accepted';
                $document->doc_finalized_at = now();
                $document->save();

                // Legacy parity: the finalized progress goes back into the project remarks.
                $finalText = trim((string) ($contentData['mpr_content'] ?? ''));
                if ($finalText !== '') {
                    DB::table('prj.projects')->where('prj_id', $document->prj_id)->update(['prj_rem' => $finalText]);
                }
                
                DocumentHistory::create([
                    'doc_id' => $docId,
                    'from_user_id' => $user->acc_id,
                    'to_user_id' => $user->acc_id, // Self (Finalized)
                    'action_type' => 'Finalized', // Changed from Forwarded to match status logic
                    'notes' => $request->remarks,
                    'created_at' => now()
                ]);
                $msg = 'MPR Finalized.';
            } 
            elseif ($action == 'return') {
                // Return Logic
                $document->current_owner_id = $document->creator_id; // Wapis Division ko
                $document->status = 'Returned'; // Status set
                $document->doc_stage = 'ProjectOfficer';
                $document->save();

                DocumentHistory::create([
                    'doc_id' => $docId,
                    'from_user_id' => $user->acc_id,
                    'to_user_id' => $document->creator_id,
                    'action_type' => 'Returned',
                    'notes' => $request->remarks,
                    'created_at' => now()
                ]);

                $msg = 'MPR Returned to Division.';
            }

            return redirect()->route('sord.all_projects')->with('success', $msg);
        });
    }

    public function handleSordAction(Request $request) {
    DB::beginTransaction();
    try {
        $user = Auth::user();
        $document = Document::findOrFail($request->doc_id);
        $action = $request->input('action'); // 'return' or 'finalize'

        if ($action == 'return') {
            // Flow: SORD -> Division (Creator)
            $document->status = 'Returned';
            $document->current_owner_id = $document->creator_id; 
            
            DocumentHistory::create([
                'doc_id' => $document->doc_id,
                'from_user_id' => $user->acc_id,
                'to_user_id' => $document->creator_id,
                'action_type' => 'Returned',
                'notes' => $request->sord_remarks, // Correction instructions
                'created_at' => now()
            ]);
        } 
        elseif ($action == 'forward') {
            $document->status = 'Finalized';
            $document->current_owner_id = $user->acc_id; // Keep with SORD, status locks it
            
            DocumentHistory::create([
                'doc_id' => $document->doc_id,
                'from_user_id' => $user->acc_id,
                'to_user_id' => $user->acc_id,
                'action_type' => 'Finalized',
                'notes' => 'MPR Approved and Finalized',
                'created_at' => now()
            ]);
        }

        $document->save();
        DB::commit();
        return redirect()->route('sord.inbox')->with('success', 'Action recorded');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}

// --- 4. COMPILE REPORT FUNCTION ---
    public function compileMprReport()
    {
        $user = Auth::user();

        // 1. Fetch Data (Same logic as inbox)
        $documents = Document::with(['project', 'creator.unit', 'latestVersion', 'currentOwner', 'history' => function($q) {
                                 $q->orderBy('created_at', 'desc');
                             }])
                             ->where(function($query) use ($user) {
                                 $query->whereIn('status', ['Finalized', 'Forwarded to MD', 'Returned']);
                             })
                             ->orderBy('updated_at', 'desc')
                             ->get();

        // 2. Initialize PHPWord
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Title & Timestamp
        $section->addText('SORD Monthly Progress Report (MPR)', ['bold' => true, 'size' => 16], ['align' => 'center']);
        $section->addText('Generated: ' . now()->format('d F Y, h:i A'), ['italic' => true, 'size' => 10], ['align' => 'center']);
        $section->addTextBreak(1);

        if ($documents->isEmpty()) {
            $section->addText("No Finalized or Returned MPRs found for this period.", ['bold' => true, 'color' => 'FF0000'], ['align' => 'center']);
        } else {
            // 3. Loop through MPRs
            foreach($documents as $doc) {
                $projectName = $doc->project->prj_title ?? 'Unknown Project';
                $projectCode = $doc->project->prj_code ?? 'N/A';
                $divisionName = $doc->creator->unit->unt_name ?? 'Unknown Division';
                $status = $doc->status;
                
                // --- Extract MPR Content ---
                $content = "No content available.";
                if($doc->latestVersion && isset($doc->latestVersion->content_data)) {
                    $d = $doc->latestVersion->content_data;
                    // Try to get 'mpr_content' first, fallback to 'physical_progress' if old data
                    $content = $d['mpr_content'] ?? ($d['physical_progress'] ?? 'No content.');
                }

                // --- Extract Return Remarks (if applicable) ---
                $returnRemarks = null;
                if ($status == 'Returned') {
                    // Find the last 'Returned' action in history
                    $lastReturn = $doc->history->firstWhere('action_type', 'Returned');
                    if ($lastReturn) {
                        $returnRemarks = $lastReturn->notes;
                    }
                }

                // --- Write to Word ---
                
                // Header Block (Project Title)
                $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80, 'width' => 100 * 50]);
                $table->addRow();
                $table->addCell(9000)->addText($projectCode . ' - ' . $projectName, ['bold' => true, 'size' => 11, 'color' => '1F497D']);
                
                // Meta Info
                $section->addTextBreak(0);
                $textrun = $section->addTextRun();
                $textrun->addText("Division: ", ['bold' => true]);
                $textrun->addText($divisionName . "  |  ");
                $textrun->addText("Status: ", ['bold' => true]);
                $textrun->addText($status, ['bold' => true, 'color' => ($status == 'Returned' ? 'FF0000' : '008000')]);

                $section->addTextBreak(1);
                
                // CONTENT (Single Block)
                $section->addText("MPR Report / Progress:", ['bold' => true, 'underline' => 'single', 'size' => 10]);
                $lines = explode("\n", $content);
                foreach($lines as $line) $section->addText(trim($line), ['size' => 10]);
                
                // --- SORD REMARKS (Only if Returned) ---
                if ($status == 'Returned' && $returnRemarks) {
                    $section->addTextBreak(1);
                    $table = $section->addTable(['borderSize' => 6, 'borderColor' => 'FF0000', 'cellMargin' => 80, 'width' => 100 * 50]);
                    $table->addRow();
                    $cell = $table->addCell(9000, ['bgColor' => 'FFEBEB']);
                    $cell->addText("SORD REJECTION / RETURN REMARKS:", ['bold' => true, 'color' => 'FF0000', 'size' => 10]);
                    
                    $lines = explode("\n", $returnRemarks);
                    foreach($lines as $line) {
                        $cell->addText(trim($line), ['italic' => true, 'size' => 10]);
                    }
                }

                // Separator
                $section->addTextBreak(1);
                $section->addText("________________________________________________________________________________", ['color' => 'E0E0E0']);
                $section->addTextBreak(1);
            }
        }

        // 4. Download
        $fileName = 'MPR_Report_' . now()->format('Ymd_His') . '.docx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }



    // 5. Global MPR Log (New)
    /**
     * MD inbox - only when the MD stage is switched on in Admin -> Settings -> Workflows (MPR).
     */
    /**
     * MD inbox - MD only sees MPRs when its level is part of the route
     * (cen.globalvars.mpr_route, e.g. "-19-25-(29)-").
     */
    public function mdInbox()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $context = \App\Services\Auth\UserAccessContext::forUser($user);
        if (! $context->isSuperAdmin() && ! $context->isMd()) {
            abort(403, 'Unauthorized. MD access required.');
        }

        $route = app(\App\Services\MprRouteService::class);
        if (! $route->isOnRoute((int) ($user->acc_level ?? 0))) {
            return redirect('/')->with('error', 'MD is not part of the MPR route (' . $route->describe() . '). Add the MD level to mpr_route in settings to switch this on.');
        }

        return $this->levelInbox($user, 'MPR Inbox — MD');
    }

    public function sordLog()
    {
        // Fetch all history related to MPRs (Forwarded or Returned)
        $logs = DocumentHistory::with(['document.project', 'fromUser.unit', 'toUser'])
                               ->whereIn('action_type', ['Forwarded', 'Returned', 'Finalized'])
                               ->orderBy('created_at', 'desc')
                               ->paginate(20);

        return view('SORD.mpr_log', compact('logs'));
    }
}