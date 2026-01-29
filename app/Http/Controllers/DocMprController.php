<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Project;
use App\Models\User;

class DocMprController extends Controller
{
    // --- 1. DIVISION: VIEW MPR ---
    public function view($projectId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($projectId);
        
        $document = Document::where('prj_id', $projectId)
                            ->with(['versions' => function($q){
                                $q->orderBy('ver_id', 'desc');
                            }, 'versions.actor.role', 'currentOwner.role', 'creator.role', 'history.sender.role'])
                            ->first();

        // --- PERMISSION LOGIC FOR DIVISION ---
        $isEditable = true;
        $isReturned = false;
        $isFinalized = false;

        if ($document) {
            // Case 1: Agar file Mere paas nahi hai -> LOCKED
            if ($document->current_owner_id != $user->acc_id) {
                $isEditable = false;
            }
            
            // Case 2: Agar Status Returned hai -> OPEN FOR EDIT (Notification Flag)
            if ($document->status == 'Returned' && $document->current_owner_id == $user->acc_id) {
                $isEditable = true;
                $isReturned = true;
            }

            // Case 3: Finalize ho chuka hai -> LOCKED
            if (in_array($document->status, ['Approved', 'Forwarded to MD'])) {
                $isEditable = false;
                $isFinalized = true;
            }
        }

        return view('projects.viewmpr', compact('project', 'document', 'isEditable', 'isReturned', 'isFinalized'));
    }

    // --- 2. SORD: REVIEW PAGE ---
    public function sordReview($projectId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($projectId);

        $document = Document::where('prj_id', $projectId)
                            ->with('creator.role', 'versions.actor.role', 'currentOwner')
                            ->first();

        if (!$document) {
             return redirect()->route('sord.inbox')->with('error', 'Document not found.');
        }

        // --- NEW LOGIC: STAY ON PAGE BUT LOCK ---
        // Agar file mere paas hai -> Editable = True
        // Agar file wapis bhej di hai -> Editable = False (Lekin page khulega)
        $isEditable = ($document->current_owner_id == $user->acc_id);

        // Versions for Split View
        $versions = $document->versions()->orderBy('ver_id', 'desc')->take(2)->get();
        $latestVersion = $versions->first(); 
        $divisionVersion = $versions->count() > 1 ? $versions->get(1) : $versions->first();

        return view('sord.review_mpr', compact('project', 'document', 'latestVersion', 'divisionVersion', 'isEditable'));
    }

    // --- 3. MAIN ACTION HANDLER ---
    public function store(Request $request, $projectId)
    {
        $user = Auth::user();
        
        DB::beginTransaction();

        try {
            $document = Document::firstOrCreate(
                ['prj_id' => $projectId],
                ['doc_type' => 'MPR', 'creator_id' => $user->acc_id, 'current_owner_id' => $user->acc_id, 'status' => 'Draft']
            );

            // Version Logic
            $lastVer = $document->versions()->orderBy('ver_id', 'desc')->first();
            $newVerNo = number_format((float)($lastVer->version_no ?? 0) + 0.1, 1);

            DocumentVersion::create([
                'doc_id' => $document->doc_id,
                'version_no' => $newVerNo,
                'content_data' => $request->except(['_token', 'action', 'remarks']),
                'remarks' => $request->remarks,
                'action_by' => $user->acc_id,
                'action_date' => now()
            ]);

            // --- ACTIONS ---
            
            // 1. FORWARD TO SORD
            if ($request->action == 'forward') {
                $sordUser = $this->getSordUser();
                if (!$sordUser) {
                    // Fallback to specific user if Unit logic fails (Testing)
                    $sordUser = User::where('acc_username', 'nislam2')->first();
                }
                
                if($sordUser) {
                    $document->current_owner_id = $sordUser->acc_id;
                    $document->status = 'Pending Review';
                    $document->save();
                    $this->logHistory($document->doc_id, $user->acc_id, $sordUser->acc_id, 'Forwarded', 'Sent to SORD');
                }
            }

            // 2. RETURN TO DIVISION
            elseif ($request->action == 'return') {
                $document->current_owner_id = $document->creator_id; // Wapis Division
                $document->status = 'Returned';
                $document->save();
                $this->logHistory($document->doc_id, $user->acc_id, $document->creator_id, 'Returned', 'Returned to Division');
            }

            // 3. FINALIZE / FORWARD MD
            elseif ($request->action == 'finalize') {
                $document->current_owner_id = 0; // Lock
                $document->status = 'Forwarded to MD'; 
                $document->save();
                $this->logHistory($document->doc_id, $user->acc_id, 0, 'Forwarded MD', 'Forwarded to MD');
            }

            DB::commit();

            // --- REDIRECT BACK (Stay on same page) ---
            if ($user->isSORD()) {
                // SORD wahi rahega taake dekh sake ke "Returned" ho gaya hai
                return redirect()->back()->with('success', 'Action processed: ' . $document->status);
            } else {
                // Division wapis projects pe ja sakta hai ya wahi reh sakta hai
                return redirect()->route('view-projects')->with('success', 'MPR Status Updated.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function getSordUser() {
        return User::whereHas('unit', function($q){ $q->where('unt_area', 'prjrdw'); })->first();
    }

    private function logHistory($docId, $from, $to, $action, $notes) {
        DB::table('doc.document_history')->insert([
            'doc_id' => $docId, 'from_user_id' => $from, 'to_user_id' => $to,
            'action_type' => $action, 'notes' => $notes, 'created_at' => now()
        ]);
    }

    public function sordInbox() {
        $user = Auth::user();
        // Inbox mein wo dikhao jo mere paas hain
        $documents = Document::where('current_owner_id', $user->acc_id)
                             ->where('status', '!=', 'Approved') 
                             ->with(['project', 'creator.role', 'creator.unit']) 
                             ->orderBy('updated_at', 'desc')
                             ->get();
        return view('sord.mpr_inbox', compact('documents'));
    }
}