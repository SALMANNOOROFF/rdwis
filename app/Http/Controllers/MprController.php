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
    // 1. SORD Inbox (UPDATED: Ab Returned files bhi dikhayega)
    public function sordInbox()
    {
        $user = Auth::user();
        
        // Logic: Wo documents dikhao jo:
        // 1. SORD (Mere) paas hain (current_owner_id = me)
        // 2. YA jinka status 'Returned' hai (Taake wo inbox se gayab na hon)
        $documents = Document::with(['project', 'creator.unit', 'latestVersion'])
                             ->where(function($query) use ($user) {
                                 $query->where('current_owner_id', $user->acc_id)
                                       ->orWhere('status', 'Returned'); // <--- YE LINE ADD KI HAI
                             })
                             ->orderBy('updated_at', 'desc')
                             ->get();

        return view('SORD.mpr_inbox', compact('documents'));
    }

    // 2. Review Page (UPDATED: Locking Logic)
    public function reviewMpr($doc_id)
    {
        $document = Document::with(['project', 'versions'])->findOrFail($doc_id);
        
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

        return view('SORD.review_mpr', compact('document', 'divisionVersion', 'sordVersion', 'isEditable'));
    }

    // 3. Action Handler (Same as before)
    public function sordAction(Request $request)
    {
        $user = Auth::user();
        $docId = $request->doc_id;
        $action = $request->action;

        return DB::transaction(function () use ($request, $user, $docId, $action) {
            
            $document = Document::findOrFail($docId);
            
            $contentData = [
                'physical_progress' => $request->physical_progress,
                'financial_progress' => $request->financial_progress,
                'issues' => $request->issues,
            ];

            // Version Create
            $lastVer = DocumentVersion::where('doc_id', $docId)->orderBy('ver_id', 'desc')->first();
            $newVerNo = $lastVer ? $lastVer->version_no + 0.1 : 1.0;

            DocumentVersion::create([
                'doc_id' => $docId,
                'version_no' => $newVerNo,
                'content_data' => $contentData,
                'remarks' => $request->remarks,
                'action_by' => $user->acc_id,
                'action_date' => now()
            ]);

            if ($action == 'save') {
                $document->status = 'Under Review by SORD';
                $document->save();
                $msg = 'Draft saved successfully.';
            } 
            elseif ($action == 'forward') {
                // Forward Logic...
                $document->current_owner_id = 0; // Or MD ID
                $document->status = 'Finalized';
                $document->save();
                
                DocumentHistory::create([
                    'doc_id' => $docId,
                    'from_user_id' => $user->acc_id,
                    'to_user_id' => 0,
                    'action_type' => 'Forwarded',
                    'notes' => $request->remarks,
                    'created_at' => now()
                ]);
                $msg = 'MPR Forwarded/Finalized.';
            } 
            elseif ($action == 'return') {
                // Return Logic
                $document->current_owner_id = $document->creator_id; // Wapis Division ko
                $document->status = 'Returned'; // Status set
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

            return redirect()->route('sord.inbox')->with('success', $msg);
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
            $document->current_owner_id = 0; // Lock forever
            
            DocumentHistory::create([
                'doc_id' => $document->doc_id,
                'from_user_id' => $user->acc_id,
                'to_user_id' => 0,
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

// --- 4. COMPILE REPORT FUNCTION (YE MISSING THA) ---
    public function compileMprReport()
    {
        $user = Auth::user();

        // 1. Fetch Data (Same logic as inbox)
        $documents = Document::with(['project', 'creator.unit', 'latestVersion', 'currentOwner'])
                             ->where(function($query) use ($user) {
                                 $query->where('current_owner_id', $user->acc_unt_id)
                                       ->orWhere('status', 'Returned');
                             })
                             ->orderBy('updated_at', 'desc')
                             ->get();

        if ($documents->isEmpty()) {
            return redirect()->back()->with('error', 'No MPRs found to compile.');
        }

        // 2. Initialize PHPWord
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Title
        $section->addText('Compiled Monthly Progress Report', ['bold' => true, 'size' => 16], ['align' => 'center']);
        $section->addText('Generated on: ' . now()->format('d M, Y'), ['italic' => true, 'size' => 10], ['align' => 'center']);
        $section->addTextBreak(1);

        // 3. Loop through MPRs
        foreach($documents as $doc) {
            $projectName = $doc->project->prj_title ?? 'Unknown Project';
            $divisionName = $doc->creator->unit->unt_name ?? 'Unknown Division';
            $status = $doc->status;
            
            // Content extract karo
            $content = "No content available.";
            if($doc->latestVersion && isset($doc->latestVersion->content_data['mpr_content'])) {
                $content = $doc->latestVersion->content_data['mpr_content'];
            }

            // --- Write to Word ---
            
            // Project Name
            $section->addText($projectName, ['bold' => true, 'size' => 14, 'color' => '1F497D']);
            
            // Details Table
            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 50]);
            $table->addRow();
            $table->addCell(4000)->addText("Division:", ['bold' => true]);
            $table->addCell(5000)->addText($divisionName);
            
            $table->addRow();
            $table->addCell(4000)->addText("Status:", ['bold' => true]);
            $table->addCell(5000)->addText($status);

            $section->addTextBreak(1);
            
            // Content
            $section->addText("Description / Progress:", ['bold' => true, 'underline' => 'single']);
            
            // Line breaks handle karna
            $lines = explode("\n", $content);
            foreach($lines as $line) {
                $section->addText(trim($line));
            }

            // Separator
            $section->addTextBreak(1);
            $section->addText("----------------------------------------------------------------", ['color' => 'CCCCCC']);
            $section->addTextBreak(1);
        }

        // 4. Download
        $fileName = 'MPR_Report_' . now()->format('Ymd_His') . '.docx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }



}