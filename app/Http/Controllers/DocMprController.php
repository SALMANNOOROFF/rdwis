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
    // --- 1. VIEW MPR ---
    public function view($projectId)
    {
        $user = Auth::user();
        $project = Project::findOrFail($projectId);
        
        $document = Document::where('prj_id', $projectId)
                            ->with(['versions' => function($q){
                                $q->orderBy('ver_id', 'desc');
                            }, 'versions.actor.role', 'currentOwner.role', 'creator.role', 'history.fromUser.role'])
                            ->first();

        // Permissions
        $isEditable = true;
        $isReturned = false;
        $isFinalized = false;

        if ($document) {
            // Case 1: Agar file mere paas nahi hai
            if ($document->current_owner_id != $user->acc_id) {
                $isEditable = false;
            }
            
            // Case 2: Status Returned Check
            if ($document->status == 'Returned' && $document->current_owner_id == $user->acc_id) {
                $isEditable = true;
                $isReturned = true;
            }

            // Case 3: Finalized
            if (in_array($document->status, ['Approved', 'Forwarded to MD'])) {
                $isEditable = false;
                $isFinalized = true;
            }
        }

        return view('projects.viewmpr', compact('project', 'document', 'isEditable', 'isReturned', 'isFinalized'));
    }

    // --- 2. SORD REVIEW ---
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

        // EDITABLE CHECK: Sirf tab edit hoga jab file mere paas ho
        $isEditable = ($document->current_owner_id == $user->acc_id);

        $versions = $document->versions()->orderBy('ver_id', 'desc')->take(2)->get();
        $latestVersion = $versions->first(); 
        $divisionVersion = $versions->count() > 1 ? $versions->get(1) : $versions->first();

        return view('sord.review_mpr', compact('project', 'document', 'latestVersion', 'divisionVersion', 'isEditable'));
    }

    // --- 3. STORE (DIRECT DB UPDATE FIX) ---
    public function store(Request $request, $projectId)
    {
        $user = Auth::user();
        
        // Debugging ke liye: Agar DB update na ho to neeche wali line uncomment karein
        // dd($request->all()); 

        DB::beginTransaction();

        try {
            // 1. Get or Create Document
            $document = Document::firstOrCreate(
                ['prj_id' => $projectId],
                ['doc_type' => 'MPR', 'creator_id' => $user->acc_id, 'current_owner_id' => $user->acc_id, 'status' => 'Draft']
            );

            // 2. Create Version
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

            // 3. HANDLE ACTIONS (USING DIRECT DB UPDATE)
            
            // --- ACTION: FORWARD TO SORD ---
            if ($request->action == 'forward') {
                $sordUser = $this->getSordUser();
                
                if(!$sordUser) $sordUser = User::where('acc_username', 'nislam2')->first(); // Fallback

                if ($sordUser) {
                    // FORCE UPDATE
                    DB::table('doc.documents')
                        ->where('doc_id', $document->doc_id)
                        ->update([
                            'current_owner_id' => $sordUser->acc_id,
                            'status' => 'Pending Review',
                            'updated_at' => now()
                        ]);

                    $this->logHistory($document->doc_id, $user->acc_id, $sordUser->acc_id, 'Forwarded', 'Sent to SORD');
                } else {
                    throw new \Exception("SORD User not found.");
                }
            }

            // --- ACTION: RETURN TO DIVISION ---
            elseif ($request->action == 'return') {
                
                $originalCreator = $document->creator_id;

                // FORCE UPDATE
                DB::table('doc.documents')
                    ->where('doc_id', $document->doc_id)
                    ->update([
                        'current_owner_id' => $originalCreator,
                        'status' => 'Returned',
                        'updated_at' => now()
                    ]);

                $this->logHistory($document->doc_id, $user->acc_id, $originalCreator, 'Returned', 'Returned to Division');
            }

            // --- ACTION: FINALIZE ---
            elseif ($request->action == 'finalize') {
                
                // FORCE UPDATE
                // FIX: Avoiding '0' for foreign key constraint. Setting to SORD User (Current)
                DB::table('doc.documents')
                    ->where('doc_id', $document->doc_id)
                    ->update([
                        'current_owner_id' => $user->acc_id, 
                        'status' => 'Forwarded to MD',
                        'updated_at' => now()
                    ]);

                // For history, we also avoid 0 if possible, or use nullable if schema supports. 
                // Assuming keeping it valid is safer.
                $this->logHistory($document->doc_id, $user->acc_id, $user->acc_id, 'Forwarded MD', 'Forwarded to MD');
            }

            DB::commit();

            return redirect()->back()->with('success', 'Action Processed Successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'System Error: ' . $e->getMessage());
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
        $documents = Document::where('current_owner_id', $user->acc_id)
                             ->where('status', '!=', 'Approved') 
                             ->with(['project', 'creator.role', 'creator.unit']) 
                             ->orderBy('updated_at', 'desc')
                             ->get();
        return view('sord.mpr_inbox', compact('documents'));
    }
}