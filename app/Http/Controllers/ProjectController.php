<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Milestone;
use App\Models\PrgHistory; 
use App\Models\Unit; 
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
    
    // --- FIX: 'with(\'document\')' add kiya hai taake status dashboard par dikhe ---
    $query = Project::where('prj_unt_id', $user->acc_unt_id);
    if (\Illuminate\Support\Facades\Schema::hasTable('doc.documents')) {
        $query->with('document');
    }

    // Filter Logic
    if ($request->has('status') && $request->status != 'All') {
        $query->where('prj_status', $request->status);
    }

    $projects = $query->orderBy('prj_id', 'desc')->get();
    
    return view('projects.viewprojects', compact('projects'));
}

   public function nrdiIndex(Request $request)
{
    $user = Auth::user();
    if (! $user) {
        return redirect()->route('login');
    }

    [$lower, $upper] = $user->acc_lowers == 0
        ? [$user->acc_lowerm, $user->acc_upperm]
        : [$user->acc_lowers, $user->acc_uppers];

    $closedStatuses = ['Closed', 'Completed'];

    $query = Project::with('unit')
        ->whereBetween('prj_unt_id', [$lower, $upper]);

    $status = (string) $request->query('status', 'open');
    if ($status === 'closed') {
        $query->whereIn('prj_status', $closedStatuses);
    } elseif ($status === 'all') {
        $status = 'all';
    } else {
        $status = 'open';
        $query->whereNotIn('prj_status', $closedStatuses);
    }

    $divisionId = $request->query('division');
    if (is_numeric($divisionId) && (int) $divisionId > 0) {
        $query->where('prj_unt_id', (int) $divisionId);
    }

    $term = trim((string) $request->query('term', ''));
    if ($term !== '') {
        $query->where(function ($q) use ($term) {
            $q->where('prj_code', 'ILIKE', '%' . $term . '%')
              ->orWhere('prj_title', 'ILIKE', '%' . $term . '%');
        });
    }

    $projects = $query->orderByDesc('prj_id')->paginate(50);
    $projects->appends([
        'status' => $status,
        'division' => $divisionId,
        'term' => $term,
    ]);

    $divisions = Unit::where('unt_type', 'Division')
        ->whereBetween('unt_id', [$lower, $upper])
        ->orderBy('unt_name', 'asc')
        ->get();

    return view('nrdi.projects.index', compact('projects', 'divisions', 'status', 'divisionId', 'term'));
}

    public function nrdiShow($id)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        [$lower, $upper] = $user->acc_lowers == 0
            ? [$user->acc_lowerm, $user->acc_upperm]
            : [$user->acc_lowers, $user->acc_uppers];

        $project = Project::with('milestones', 'attachments', 'unit')
            ->where('prj_id', $id)
            ->whereBetween('prj_unt_id', [$lower, $upper])
            ->firstOrFail();

        // Financial Intelligence (Legacy Logic Integration)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $headRecord = DB::table('cen.heads')->where('hed_prj_id', $id)->first();
        
        $head = null;
        $subheads = [];
        if ($headRecord) {
            $head = $finService->getHeadStatus($headRecord->hed_id);
            $subheads = $finService->getSubheadBreakdown($headRecord->hed_id);
        }

        $totalSpent = $head->expenditure ?? 0;
        $balance = $head->balance ?? (($project->prj_propcost ?? 0) - $totalSpent);
        $spentPercentage = ($project->prj_propcost ?? 0) > 0 ? round(($totalSpent / $project->prj_propcost) * 100, 1) : 0;

        $finData = [
            'equip' => $totalSpent * 0.45,
            'hr'    => $totalSpent * 0.35,
            'misc'  => $totalSpent * 0.20,
        ];

        $mprsSubmitted = PrgHistory::where('pgh_xprj_id', $id)->count();

        $startDate = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : \Carbon\Carbon::now();
        $endDate = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : \Carbon\Carbon::now();

        $totalMonths = $startDate->diffInMonths($endDate);
        if ($totalMonths < 1) $totalMonths = 1;
        $mprsLeft = max(0, $totalMonths - $mprsSubmitted);

        $readOnly = true;

        return view('projects.openprojectdetails', compact(
            'project',
            'totalSpent',
            'balance',
            'spentPercentage',
            'finData',
            'mprsSubmitted',
            'mprsLeft',
            'totalMonths',
            'readOnly',
            'head',
            'subheads'
        ));
    }


    public function show($id)
    {
        $project = Project::with('milestones', 'attachments')->where('prj_id', $id)->firstOrFail();

        // Financial Intelligence (Legacy Logic Integration)
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $headRecord = DB::table('cen.heads')->where('hed_prj_id', $id)->first();
        
        $head = null;
        $subheads = [];
        if ($headRecord) {
            $head = $finService->getHeadStatus($headRecord->hed_id);
            $subheads = $finService->getSubheadBreakdown($headRecord->hed_id);
        }

        $totalSpent = $head->expenditure ?? 0;
        $balance = $head->balance ?? ($project->prj_propcost - $totalSpent);
        $spentPercentage = $project->prj_propcost > 0 ? round(($totalSpent / $project->prj_propcost) * 100, 1) : 0;

        $finData = [
            'equip' => $totalSpent * 0.45,
            'hr'    => $totalSpent * 0.35,
            'misc'  => $totalSpent * 0.20
        ];

        $mprsSubmitted = PrgHistory::where('pgh_xprj_id', $id)->count();

        $startDate = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : \Carbon\Carbon::now();
        $endDate = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : \Carbon\Carbon::now();
        
        $totalMonths = $startDate->diffInMonths($endDate);
        if($totalMonths < 1) $totalMonths = 1;

        $mprsLeft = max(0, $totalMonths - $mprsSubmitted);

        return view('projects.openprojectdetails', compact(
            'project', 'totalSpent', 'balance', 'spentPercentage', 'finData', 
            'mprsSubmitted', 'mprsLeft', 'totalMonths', 'head', 'subheads'
        ));
    }

    // --- 2. CREATE PROJECT PAGE (Smart Logic) ---
    public function create(Request $request)
    {
        $project = null;
        $step = 1;

        if ($request->has('draft_id')) {
            $project = Project::find($request->draft_id);
            if($project && $project->prj_status == 'Draft') {
                $step = 2; // Direct Phase 2
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
            'prj_aprvdt' => 'required|date',
        ]);

        $maxId = Project::max('prj_id');
        $nextId = $maxId ? $maxId + 1 : 1;

        $project = new Project();
        $project->prj_id = $nextId;
        $project->prj_code = $request->prj_code;
        $project->prj_title = $request->prj_title;
        $project->prj_sponsor = $request->prj_sponsor;
        $project->prj_propcost = $request->prj_propcost;
        
        $project->prj_scope = $request->prj_scope;
        $project->prj_propdt = $request->prj_propdt;
        $project->prj_assigndt = $request->prj_assigndt;
        $project->prj_aprvdt = $request->prj_aprvdt;
        
        $project->prj_status = 'Draft';
        $project->prj_unt_id = Auth::check() ? Auth::user()->acc_unt_id : 200000;
        $project->prj_rcptdt = now();
        
        $project->save();

        // LOGGING ADDED HERE
        $this->logActivity($project->prj_id, 'Initiation', 'Project Initiated with Draft status');

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

        // LOGGING ADDED HERE
        $this->logActivity($id, 'Execution', 'Project Finalized & Work Order Uploaded');

        // Handle Files (Including Approval Letter Fix)
        $this->handleUpload($request, $project, 'doc_workorder', 'Work Order');
        $this->handleUpload($request, $project, 'doc_approval_letter', 'Approval Letter');

        // Milestone Logic
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
                    
                    // Log each milestone creation
                    $this->logActivity($id, 'Milestone', "Created Milestone: {$msData['desc']}");
                }
            }
        }

        return redirect()->route('projects.show', $project->prj_id)
                         ->with('success', 'Project Initiated Successfully!');
    }

    // --- NEW FILE UPLOAD LOGIC ---
    private function handleUpload($request, $project, $inputName, $docType)
    {
        if ($request->hasFile($inputName)) {
            $file = $request->file($inputName);
            
            // Folder Name: Standard spelling 'attachments' recommended
            $folderName = 'attachments/Projects/' . Str::slug($project->prj_code);
            $extension = $file->getClientOriginalExtension();
            $fileName = $docType . '.' . $extension; 

            $path = $file->storeAs($folderName, $fileName, 'public'); 
            
            $att = new PrjAttachment();
            $att->jat_objid = $project->prj_id;
            $att->jat_objtype = 'Project';
            $att->jat_type = $docType; 
            $att->jat_path = $path;
            
            $att->save();

            // LOGGING ADDED HERE
            $this->logActivity($project->prj_id, 'Attachment', "Uploaded Document: $docType");
        }
    }

    // --- VIEW ATTACHMENT ---
    public function viewAttachment($id)
    {
        $attachment = PrjAttachment::findOrFail($id);
        
        $dbPath = $attachment->jat_path; 
        
        // Handling both spellings just in case
        if (str_contains($dbPath, 'attachements')) {
             $folderPath = $dbPath; 
        } else {
             // Standardize
             $folderPath = str_replace('attachments', 'attachements', $dbPath); 
        }

        $fullPath = storage_path('app/public/' . $folderPath);
        $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath);

        if (!file_exists($fullPath)) {
            // Fallback try with correct spelling
            $altPath = str_replace('attachements', 'attachments', $fullPath);
            if(file_exists($altPath)){
                $fullPath = $altPath;
            } else {
                return response()->json(['error' => 'File nahi mili.'], 404);
            }
        }

        $fileContents = file_get_contents($fullPath);
        $mimeType = mime_content_type($fullPath);

        return response($fileContents, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($fullPath) . '"');
    }

    // --- UPLOAD OTHER DOCUMENT ---
    public function storeOtherAttachment(Request $request, $id)
    {
        $request->validate([
            'custom_name' => 'required|string|max:50',
            'doc_file' => 'required|file|mimes:pdf,jpg,png,doc,docx|max:10240',
        ]);

        $project = Project::findOrFail($id);
        $this->handleUpload($request, $project, 'doc_file', $request->custom_name);

        return redirect()->back()->with('success', 'Document added successfully!');
    }

    // --- SINGLE FILE UPLOAD ---
    public function uploadSingleAttachment(Request $request, $id)
    {
        $request->validate([
            'single_file' => 'required|file',
            'doc_type' => 'required|string'
        ]);

        $project = Project::findOrFail($id);
        $this->handleUpload($request, $project, 'single_file', $request->doc_type);

        return redirect()->back()->with('success', $request->doc_type . ' Uploaded Successfully!');
    }

    // --- DELETE ATTACHMENT ---
    public function deleteAttachment($id)
    {
        $attachment = PrjAttachment::findOrFail($id);
        $attachment->delete();
        
        // Log deletion
        $this->logActivity($attachment->jat_objid, 'Attachment', "Deleted Document: {$attachment->jat_type}");

        return redirect()->back()->with('success', 'Document deleted successfully.');
    }

    // --- MILESTONES ---
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

        // LOGGING ADDED HERE
        $this->logActivity($id, 'Milestone', "Added Milestone: {$request->msn_desc}");

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

        // LOGGING ADDED HERE
        $this->logActivity($milestone->msn_xprj_id, 'Milestone', "Updated Milestone: {$request->msn_desc}");

        return redirect()->route('projects.show', $milestone->msn_xprj_id)
                         ->with('success', 'Milestone Updated Successfully!');
    }
// ProjectController.php ke andar add karein

public function markMilestoneComplete(Request $request)
    {
        $request->validate([
            // Fix: Use the Model class instead of the raw string to handle schema correctly
            'msn_id' => ['required', Rule::exists(Milestone::class, 'msn_id')],
            'achieved_date' => 'required|date'
        ]);

        $milestone = Milestone::find($request->msn_id);
        if($milestone) {
            $milestone->msn_achvdt = $request->achieved_date;
            $milestone->msn_status = 'Completed'; // Status auto-update
            $milestone->save();
        }

        return redirect()->back()->with('success', 'Milestone marked as Completed!');
    }
    public function deleteMilestone($id)
    {
        $milestone = Milestone::where('msn_id', $id)->firstOrFail();
        $projectId = $milestone->msn_xprj_id;
        $desc = $milestone->msn_desc;
        
        $milestone->delete();

        // LOGGING ADDED HERE
        $this->logActivity($projectId, 'Milestone', "Deleted Milestone: $desc");

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
        $mprHistory = PrgHistory::where('pgh_xprj_id', $id)->orderBy('pgh_dtg', 'desc')->get();
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
        
        if (Auth::check()) {
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
        
        // Log MPR
        $this->logActivity($id, 'MPR', "Submitted Monthly Progress Report");

        return redirect()->route('mpr.view', $id)->with('success', 'Progress Report Added Successfully!');
    }

    // --- FINANCIAL SPENDINGS ---
    public function projectSpendings($id)
    {
        $project = Project::where('prj_id', $id)->firstOrFail();

        $totalBudget = DB::table('fin.msncosts')->where('mct_prj_id', $id)->sum('mct_cost');

        $totalSpent = DB::table('fin.transactions')
            ->join('fin.commitments', 'fin.transactions.trn_cmt_id', '=', 'fin.commitments.cmt_id')
            ->where('fin.commitments.cmt_docid', $id)
            ->sum('fin.transactions.trn_amount1');

        $budgetBreakdown = DB::table('fin.msncosts')
            ->select('mct_hed_id', DB::raw('SUM(mct_cost) as total_cost'))
            ->where('mct_prj_id', $id)
            ->groupBy('mct_hed_id')
            ->get();

        $balance = $totalBudget - $totalSpent;
        $percentageSpent = $totalBudget > 0 ? round(($totalSpent / $totalBudget) * 100, 1) : 0;

        $chartLabels = $budgetBreakdown->pluck('mct_hed_id')->toArray();
        $chartData = $budgetBreakdown->pluck('total_cost')->toArray();

        return view('projects.spendings', compact(
            'project', 'totalBudget', 'totalSpent', 'balance', 'percentageSpent', 'chartLabels', 'chartData'
        ));
    }

    // --- GLOBAL PROJECT HISTORY (New Function) ---
   public function projectHistory(Request $request)
{
    // Agar URL mein ?project_id=123 hai, to sirf uski MPRs dikhao
    if ($request->has('project_id')) {
        $projectId = $request->project_id;
        $project = Project::find($projectId);

        // Fetch MPRs for this project
        $mprHistory = PrgHistory::where('pgh_xprj_id', $projectId)
            ->orderBy('pgh_dtg', 'desc')
            ->get();
            
        $viewType = 'mpr_list'; // View ko batane ke liye ke ye MPRs hain

        return view('projects.projecthistory', compact('mprHistory', 'project', 'viewType'));
    }

    // Warna purana Global Audit Log dikhao
    if (\Illuminate\Support\Facades\Schema::hasTable('project_activities')) {
        $activities = DB::table('project_activities')
            ->join('prj.projects', 'project_activities.pja_prj_id', '=', 'prj.projects.prj_id')
            ->select('project_activities.*', 'prj.projects.prj_title', 'prj.projects.prj_code')
            ->orderBy('created_at', 'desc')
            ->get();
    } else {
        $activities = collect([]);
    }

    $viewType = 'global_log';

    return view('projects.projecthistory', compact('activities', 'viewType'));
}
    // --- HELPER: LOG ACTIVITY ---
    private function logActivity($projectId, $action, $details)
    {
        // Ensure table 'project_activities' exists before inserting
        if (\Illuminate\Support\Facades\Schema::hasTable('project_activities')) {
            DB::table('project_activities')->insert([
                'pja_prj_id' => $projectId,
                'pja_action' => $action,
                'pja_details' => $details,
                'pja_user' => Auth::check() ? Auth::user()->acc_username : 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }


   public function sordIndex()
{
    // 1. Saari Divisions uthao ('prj' area wali) dropdown ke liye
    $divisions = Unit::where('unt_area', 'prj')->orderBy('unt_name', 'asc')->get();

    // 2. Saare Projects uthao (Unit relation ke sath taake naam dikha sakein)
    // Hum 'paginate' nahi use kar rahe kyunki aapka existing page JS filtering use karta hai
    $projects = Project::with('unit')->orderBy('prj_id', 'desc')->get();

    // 3. Naye View par bhejo
    return view('sord.projects', compact('projects', 'divisions'));
}

   // --- SORD READ-ONLY PROJECT DETAILS ---
   public function sordShow($id)
   {
       $project = Project::with('milestones', 'attachments')->where('prj_id', $id)->firstOrFail();
       
       // 1. Calculate Total Spent
       $totalSpent = DB::table('fin.transactions')
           ->join('fin.commitments', 'fin.transactions.trn_cmt_id', '=', 'fin.commitments.cmt_id')
           ->where('fin.commitments.cmt_docid', $id)
           ->sum('fin.transactions.trn_amount1');

       // 2. Balance
       $balance = $project->prj_propcost - $totalSpent;
       $spentPercentage = $project->prj_propcost > 0 ? round(($totalSpent / $project->prj_propcost) * 100, 1) : 0;

       // 3. Category Data
       $finData = [
           'equip' => $totalSpent * 0.45,
           'hr'    => $totalSpent * 0.35,
           'misc'  => $totalSpent * 0.20
       ];

       // --- MPR STATISTICS --
       $mprsSubmitted = PrgHistory::where('pgh_xprj_id', $id)->count();
       $startDate = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : \Carbon\Carbon::now();
       $endDate = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : \Carbon\Carbon::now();
       $totalMonths = $startDate->diffInMonths($endDate);
       if($totalMonths < 1) $totalMonths = 1;
       $mprsLeft = max(0, $totalMonths - $mprsSubmitted);

       return view('SORD.project_details', compact(
           'project', 'totalSpent', 'balance', 'spentPercentage', 'finData', 
           'mprsSubmitted', 'mprsLeft', 'totalMonths'
       ));
   }
}
