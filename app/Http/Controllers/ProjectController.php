<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\PrgHistory; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Str;
use App\Models\PrjAttachment;

class ProjectController extends Controller
{
   // --- 1. VIEW PROJECTS (With Filters) ---
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');
        
        $query = Project::where('prj_unt_id', $user->acc_unt_id);

        // Filter Logic
        if ($request->has('status') && $request->status != 'All') {
            $query->where('prj_status', $request->status);
        }

        $projects = $query->orderBy('prj_id', 'desc')->get();
        
        return view('projects.viewprojects', compact('projects'));
    }

   public function show($id)
    {
        $project = Project::with('milestones', 'attachments')->where('prj_id', $id)->firstOrFail();
        
        // 1. Calculate Total Spent (From Transactions)
        $totalSpent = DB::table('fin.transactions')
            ->join('fin.commitments', 'fin.transactions.trn_cmt_id', '=', 'fin.commitments.cmt_id')
            ->where('fin.commitments.cmt_docid', $id)
            ->sum('fin.transactions.trn_amount1');

        // 2. Calculate Balance
        $balance = $project->prj_propcost - $totalSpent;
        $spentPercentage = $project->prj_propcost > 0 ? round(($totalSpent / $project->prj_propcost) * 100, 1) : 0;

        // 3. Category Data (Mock Logic - Replace with actual Head IDs later)
        // Filhal hum Spent amount ko divide kar rahe hain graph dikhane ke liye
        $finData = [
            'equip' => $totalSpent * 0.45, // 45% Example
            'hr'    => $totalSpent * 0.35, // 35% Example
            'misc'  => $totalSpent * 0.20  // 20% Example
        ];

        return view('projects.openprojectdetails', compact('project', 'totalSpent', 'balance', 'spentPercentage', 'finData'));
    }
    // --- 2. CREATE PROJECT PAGE (Smart Logic) ---
    // Agar Draft ID mili to wahi khulega, warna naya form
    public function create(Request $request)
    {
        $project = null;
        $step = 1;

        // Agar user 'Draft' project ko continue karne aya hai
        if ($request->has('draft_id')) {
            $project = Project::find($request->draft_id);
            if($project && $project->prj_status == 'Draft') {
                $step = 2; // Direct Phase 2 khulega
            }
        }

        return view('projects.addnewproject', compact('project', 'step'));
    }

    // --- STORE (Phase 1) ---
   public function store(Request $request)
    {
        $connection = config('database.default'); 
        
        $request->validate([
            'prj_code' => ['required', 'string', 'max:20', Rule::unique("$connection.prj.projects", 'prj_code')],
            'prj_title' => 'required|string|max:255',
            'prj_aprvdt' => 'required|date', // Approval Date Phase 1 mein hi le rahe hain
        ]);

        $maxId = Project::max('prj_id');
        $nextId = $maxId ? $maxId + 1 : 1;

        $project = new Project();
        $project->prj_id = $nextId;
        $project->prj_code = $request->prj_code;
        $project->prj_title = $request->prj_title;
        $project->prj_sponsor = $request->prj_sponsor;
        $project->prj_propcost = $request->prj_propcost;
        
        // --- NEW FIELDS ADDED HERE ---
        $project->prj_scope = $request->prj_scope;       // Scope of Work
        $project->prj_propdt = $request->prj_propdt;     // Proposal Date
        $project->prj_assigndt = $request->prj_assigndt; // Assigned Date
        // -----------------------------

        $project->prj_aprvdt = $request->prj_aprvdt;     // Approval Date
        $project->prj_status = 'Draft';
        $project->prj_unt_id = Auth::check() ? Auth::user()->acc_unt_id : 200000;
        $project->prj_rcptdt = now();
        
        $project->save();

        // Handle Files
        $this->handleUpload($request, $project, 'doc_ppf', 'PPF');
        $this->handleUpload($request, $project, 'doc_urd', 'URD');

        return redirect()->route('addnewproject', ['draft_id' => $project->prj_id])
                         ->with('success', 'Phase 1 Saved! Proceed to Work Order details.');
    }
    // --- FINALIZE (Phase 2) ---
    public function finalizeProject(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'prj_startdt' => 'required|date',
            'prj_estenddt' => 'required|date|after:prj_startdt',
        ]);

        $project->prj_startdt = $request->prj_startdt;
        $project->prj_estenddt = $request->prj_estenddt;
        $project->prj_status = 'Open'; 
        $project->save();

        // Handle Work Order File
        $this->handleUpload($request, $project, 'doc_workorder', 'Work Order');

        // ... (Milestone logic same rahegi) ...
        if ($request->has('milestones')) {
            foreach ($request->milestones as $msData) {
                if (!empty($msData['desc'])) {
                    $mId = Milestone::max('msn_id') + 1;
                    $ms = new Milestone();
                    $ms->msn_id = $mId;
                    $ms->msn_xprj_id = $project->prj_id;
                    $ms->msn_desc = $msData['desc'];
                    $ms->msn_targetdt = $msData['date'];
                    $ms->msn_status = 'Pending';
                    $ms->msn_type = 'Technical';
                    $ms->save();
                }
            }
        }

        return redirect()->route('projects.show', $project->prj_id)
                         ->with('success', 'Project Initiated Successfully!');
    }

    // --- NEW FILE UPLOAD LOGIC (Folder & Rename) ---
   // --- NEW FILE UPLOAD LOGIC (Folder & Rename) ---
  // --- 1. VIEW ATTACHMENT (Fixed Path) ---
    public function viewAttachment($id)
    {
        $attachment = PrjAttachment::findOrFail($id);
        
        // Step A: DB mein 'attachments' hai, lekin folder 'attachements' hai.
        // Hum spelling fix kar rahe hain taaki path match kare.
        $dbPath = $attachment->jat_path; // e.g., attachments/Projects/code/file.pdf
        $folderPath = str_replace('attachments', 'attachements', $dbPath); 
        
        // Step B: Storage Path use karein (storage/app/public/...)
        // Final Path: E:\RDWIS\storage\app\public\attachements\Projects\...\file.pdf
        $fullPath = storage_path('app/public/' . $folderPath);

        // Windows Slashes Fix
        $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath);

        // Step C: Check & Serve
        if (!file_exists($fullPath)) {
            return response()->json([
                'error' => 'File nahi mili.',
                'looking_at' => $fullPath, // Ye apko batayega kahan dhund raha hai
                'db_record' => $attachment->jat_path
            ], 404);
        }

        $fileContents = file_get_contents($fullPath);
        $mimeType = mime_content_type($fullPath);

        return response($fileContents, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($fullPath) . '"');
    }

    // --- 2. UPLOAD LOGIC (Taaki new files bhi wahin jayen) ---
    private function handleUpload($request, $project, $inputName, $docType)
    {
        if ($request->hasFile($inputName)) {
            $file = $request->file($inputName);
            
            // Folder Name: attachements/Projects/CODE (Note spelling 'attachements')
            $folderName = 'attachements/Projects/' . Str::slug($project->prj_code);
            
            $extension = $file->getClientOriginalExtension();
            $fileName = $docType . '.' . $extension; 

            // Save to: storage/app/public/attachements/...
            $path = $file->storeAs($folderName, $fileName, 'public'); 
            
            // DB Entry: Hum standard 'attachments' hi rakhte hain DB mein consistency ke liye
            // ya agar apko 'attachements' hi rakhna hai to wo save karein.
            // Filhal main wohi save kar raha hun jo actual path hai taaki baad mein masla na ho.
            
            $att = new PrjAttachment();
            $att->jat_objid = $project->prj_id;
            $att->jat_objtype = 'Project';
            $att->jat_type = $docType; 
            
            // DB mein save: attachments/Projects/... (Standard spelling for DB)
            // View function isay khud handle kar lega.
            $att->jat_path = 'attachments/Projects/' . Str::slug($project->prj_code) . '/' . $fileName; 
            
            $att->save();
        }
    }


    // --- UPLOAD OTHER DOCUMENT (Custom Name) ---
    public function storeOtherAttachment(Request $request, $id)
    {
        $request->validate([
            'custom_name' => 'required|string|max:50',
            'doc_file' => 'required|file|mimes:pdf,jpg,png,doc,docx|max:10240',
        ]);

        $project = Project::findOrFail($id);
        
        // Use existing handleUpload logic but with custom type
        // Hum 'jat_type' mein user ka diya hua naam save karenge
        $this->handleUpload($request, $project, 'doc_file', $request->custom_name);

        return redirect()->back()->with('success', 'Document added successfully!');
    }

    // --- SINGLE FILE UPLOAD (For Detail Page) ---
    public function uploadSingleAttachment(Request $request, $id)
    {
        $request->validate([
            'single_file' => 'required|file',
            'doc_type' => 'required|string'
        ]);

        $project = Project::findOrFail($id);
        
        // Reuse handleUpload logic
        $this->handleUpload($request, $project, 'single_file', $request->doc_type);

        return redirect()->back()->with('success', $request->doc_type . ' Uploaded Successfully!');
    }

    // --- DELETE ATTACHMENT ---
    public function deleteAttachment($id)
    {
        $attachment = PrjAttachment::findOrFail($id);
        
        // Physical file delete (Optional but good practice)
        // Note: Humara view logic path change kar deta hai, isliye path ko carefully handle karein
        // Filhal sirf DB se ura rahe hain taaki error na aaye
        
        $attachment->delete();

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }
    // --- Milestones ---
    public function createMilestone($id)
    {
        $project = Project::where('prj_id', $id)->firstOrFail();
        return view('projects.addmilestonepr', compact('project'));
    }

    public function storeMilestone(Request $request, $id)
    {
        $request->validate([
            'msn_desc' => 'required',
            'msn_targetdt' => 'required|date',
            'msn_type' => 'required',
            'msn_status' => 'required'
        ]);

        $maxId = Milestone::max('msn_id');
        $nextId = $maxId ? $maxId + 1 : 1;

        $milestone = new Milestone();
        $milestone->msn_id = $nextId;
        $milestone->msn_xprj_id = $id;
        $milestone->msn_desc = $request->msn_desc;
        $milestone->msn_targetdt = $request->msn_targetdt;
        $milestone->msn_type = $request->msn_type;
        $milestone->msn_status = $request->msn_status;
        $milestone->save();

        return redirect()->route('projects.show', $id)->with('success', 'Milestone Added!');
    }

    public function editMilestone($id)
    {
        $milestone = Milestone::where('msn_id', $id)->firstOrFail();
        $project = Project::where('prj_id', $milestone->msn_xprj_id)->first();
        
        return view('projects.editmilestone', compact('milestone', 'project'));
    }

    public function updateMilestone(Request $request, $id)
    {
        $request->validate([
            'msn_desc' => 'required',
            'msn_targetdt' => 'required|date',
            'msn_type' => 'required',
            'msn_status' => 'required'
        ]);

        $milestone = Milestone::where('msn_id', $id)->firstOrFail();
        
        $milestone->msn_desc = $request->msn_desc;
        $milestone->msn_targetdt = $request->msn_targetdt;
        $milestone->msn_type = $request->msn_type;
        $milestone->msn_status = $request->msn_status;
        
        $milestone->save();

        return redirect()->route('projects.show', $milestone->msn_xprj_id)
                         ->with('success', 'Milestone Updated Successfully!');
    }

    public function deleteMilestone($id)
    {
        $milestone = Milestone::where('msn_id', $id)->firstOrFail();
        $projectId = $milestone->msn_xprj_id;
        
        $milestone->delete();

        return redirect()->route('projects.show', $projectId)
                         ->with('success', 'Milestone Deleted Successfully!');
    }

    // --- MPR (Reports) ---
    public function mprProjectList()
    {
        $user = Auth::user();
        $projects = Project::where('prj_unt_id', $user->acc_unt_id)->get();
        return view('projects.openmprs', compact('projects'));
    }

    public function mprProjectView($id)
    {
        $project = Project::where('prj_id', $id)->firstOrFail();
        
        // 1. Get ALL History ordered by date desc (Recent first)
        $mprHistory = PrgHistory::where('pgh_xprj_id', $id)
                                ->orderBy('pgh_dtg', 'desc')
                                ->get();

        // 2. Get Current Active Milestone (Pending or In Progress, sorted by date)
        $currentMilestone = Milestone::where('msn_xprj_id', $id)
                                     ->whereIn('msn_status', ['Pending', 'In Progress'])
                                     ->orderBy('msn_targetdt', 'asc')
                                     ->first();

        return view('projects.viewmpr', compact('project', 'mprHistory', 'currentMilestone'));
    }

    public function storeMpr(Request $request, $id)
    {
        $request->validate([
            'pgh_dtg' => 'required|date',
            'pgh_progress' => 'required|string',
        ]);

        $mpr = new PrgHistory();
        $mpr->pgh_xprj_id = $id;
        $mpr->pgh_dtg = $request->pgh_dtg;
        $mpr->pgh_progress = $request->pgh_progress;
        
        // --- AUTHOR LOGIC ---
        // User ka Designation Short Code (e.g., "AD-IT") uthayega
        if (Auth::check()) {
            // Ensure User model has 'role' relationship defined
            $author = Auth::user()->role->rol_desigshort ?? Auth::user()->acc_username;
            $mpr->pgh_author = $author;
            $mpr->pgh_level = Auth::user()->acc_level;
        } else {
            $mpr->pgh_author = 'System';
            $mpr->pgh_level = 1;
        }

        $mpr->pgh_status = 'Submitted';
        $mpr->pgh_underedit = true; 
        
        $mpr->save();

        return redirect()->route('mpr.view', $id)->with('success', 'Progress Report Added Successfully!');
    }

    // --- FINANCIAL SPENDINGS ---
    public function projectSpendings($id)
    {
        // 1. Project Basic Info
        $project = Project::where('prj_id', $id)->firstOrFail();

        // 2. TOTAL ALLOCATED BUDGET (From fin.msncosts)
        $totalBudget = DB::table('fin.msncosts')
            ->where('mct_prj_id', $id)
            ->sum('mct_cost');

        // 3. ACTUAL SPENDING (Specific Project Spendings)
        // Hum 'cmt_docid' use kar rahe hain jo Project ID store karta hai (Specific Project Logic)
        $totalSpent = DB::table('fin.transactions')
            ->join('fin.commitments', 'fin.transactions.trn_cmt_id', '=', 'fin.commitments.cmt_id')
            ->where('fin.commitments.cmt_docid', $id) // Specific Project ID check
            ->sum('fin.transactions.trn_amount1');

        // 4. BUDGET BREAKDOWN (Graph Data - Head Wise)
        $budgetBreakdown = DB::table('fin.msncosts')
            ->select('mct_hed_id', DB::raw('SUM(mct_cost) as total_cost'))
            ->where('mct_prj_id', $id)
            ->groupBy('mct_hed_id')
            ->get();

        // 5. Calculate Remaining
        $balance = $totalBudget - $totalSpent;
        $percentageSpent = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        // Charts Data Preparation
        $chartLabels = $budgetBreakdown->pluck('mct_hed_id')->toArray();
        $chartData = $budgetBreakdown->pluck('total_cost')->toArray();

        return view('projects.spendings', compact(
            'project', 'totalBudget', 'totalSpent', 'balance', 'percentageSpent', 'chartLabels', 'chartData'
        ));
    }
}