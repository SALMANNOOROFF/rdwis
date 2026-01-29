<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Project;
use App\Models\User;
// use App\Models\DocumentHistory; // Uncomment if you made this model

class DocMprController extends Controller
{
    // --- 1. VIEW MPR ---
    public function view($projectId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($projectId);

        // Document aur uski Versions load karein
        $document = Document::where('prj_id', $projectId)
                            ->with(['versions' => function($q){
                                $q->orderBy('ver_id', 'desc');
                            }, 'versions.actor', 'creator', 'currentOwner'])
                            ->first();

        // Permission Logic:
        $isEditable = true;
        
        // Agar document hai aur Owner main nahi hun, to Edit OFF
        if ($document && $document->current_owner_id != $user->acc_id) {
            $isEditable = false;
        }

        return view('projects.viewmpr', compact('project', 'document', 'isEditable'));
    }

    // --- 2. STORE / ACTION HANDLER ---
    public function store(Request $request, $projectId)
    {
        $user = Auth::user();
        
        DB::transaction(function () use ($request, $projectId, $user) {
            
            // A. Document Find or Create
            $document = Document::firstOrCreate(
                ['prj_id' => $projectId],
                [
                    'doc_type' => 'MPR',
                    'creator_id' => $user->acc_id,
                    'current_owner_id' => $user->acc_id,
                    'status' => 'Draft'
                ]
            );

            // Security Check
            if ($document->current_owner_id != $user->acc_id) {
                abort(403, 'File is currently with ' . ($document->currentOwner->acc_name ?? 'Someone else'));
            }

            // B. Calculate New Version Number (e.g. 1.0 -> 1.1)
            $lastVerDoc = $document->versions()->orderBy('ver_id', 'desc')->first();
            $lastVer = $lastVerDoc ? $lastVerDoc->version_no : '0.0';
            $newVer = number_format((float)$lastVer + 0.1, 1);

            // C. Save Copy (Snapshot) in Versions Table
            $contentData = $request->except(['_token', 'action', 'remarks']); 
            
            DocumentVersion::create([
                'doc_id' => $document->doc_id,
                'version_no' => $newVer,
                'content_data' => $contentData, 
                'remarks' => $request->remarks ?? 'Action by ' . $user->acc_username,
                'action_by' => $user->acc_id,
                'action_date' => now()
            ]);

            // D. Handle Workflow Actions
            
            // 1. FORWARD (Division -> SORD)
            if ($request->action == 'forward') {
                $sordUser = User::where('acc_username', 'nislam2')->first(); // Target SORD User
                
                if ($sordUser) {
                    $document->current_owner_id = $sordUser->acc_id;
                    $document->status = 'Pending Review';
                    $document->save();

                    $this->logHistory($document->doc_id, $user->acc_id, $sordUser->acc_id, 'Forwarded', 'Sent to SORD');
                }
            }
            
            // 2. RETURN (SORD -> Division)
            elseif ($request->action == 'return') {
                $originalCreator = $document->creator_id;

                $document->current_owner_id = $originalCreator;
                $document->status = 'Returned'; 
                $document->save();

                $this->logHistory($document->doc_id, $user->acc_id, $originalCreator, 'Returned', 'Returned to Division for correction');
            }

            // 3. SAVE DRAFT (No Movement)
            // Version create ho gaya hai, owner change nahi hoga.
        });

        return redirect()->route(Auth::user()->isSORD() ? 'sord.inbox' : 'view-projects')
                         ->with('success', 'Document updated successfully');
    }

    // Helper for History
    private function logHistory($docId, $from, $to, $action, $notes) {
        DB::table('doc.document_history')->insert([
            'doc_id' => $docId,
            'from_user_id' => $from,
            'to_user_id' => $to,
            'action_type' => $action,
            'notes' => $notes,
            'created_at' => now()
        ]);
    }

    // SORD Inbox
    public function sordInbox()
    {
        $user = Auth::user();
        $documents = Document::where('current_owner_id', $user->acc_id)
                             ->with('project.unit', 'creator')
                             ->orderBy('updated_at', 'desc')
                             ->get();
                             
        return view('sord.mpr_inbox', compact('documents'));
    }

    // --- 4. NEW: SORD REVIEW PAGE ---
    public function sordReview($projectId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($projectId);

        // Document Load karein
        $document = Document::where('prj_id', $projectId)
                            ->with(['versions' => function($q){
                                $q->orderBy('ver_id', 'desc');
                            }, 'versions.actor', 'creator', 'currentOwner'])
                            ->first();

        // Check Permissions (Sirf SORD Owner hi edit kar sake)
        $isEditable = false;
        if ($document && $document->current_owner_id == $user->acc_id) {
            $isEditable = true;
        }

        // Return the NEW VIEW (sord.review_mpr)
        return view('sord.review_mpr', compact('project', 'document', 'isEditable'));
    }
}