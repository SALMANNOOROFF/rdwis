<?php

namespace App\Http\Controllers\Division;

use App\Http\Controllers\Controller;
use App\Models\HrCtrCase;
use App\Models\HrCtrCasePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractCaseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $divisionId = $user->acc_lowers ?: $user->acc_lowerm; // Default division mapping

        $cases = HrCtrCase::where('ctc_divisionid', $divisionId)
            ->orWhere('ctc_unt_id', $divisionId)
            ->orderBy('ctc_id', 'desc')
            ->get();

        $actionReqCases = $cases->whereIn('ctc_status', ['Draft', 'Returned']);
        $initiatedCases = $cases->whereIn('ctc_status', ['Under HR Scrutiny', 'Under Finance Scrutiny', 'Under Approval']);
        $completedCases = $cases->whereIn('ctc_status', ['Approved', 'Rejected', 'Closed']);

        return view('division.contract-cases.index', compact('cases', 'actionReqCases', 'initiatedCases', 'completedCases'));
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'Hg');
        $user = Auth::user();
        $divisionId = $user->acc_lowers ?: $user->acc_lowerm;

        $division = DB::table('cen.units')->where('unt_id', $divisionId)->first();
        $divisionName = $division ? $division->unt_name : 'Unknown Division';

        // Fetch projects for division
        $projects = DB::table('prj.projects')
            ->where('prj_unt_id', $divisionId)
            ->select('prj_id', 'prj_code', 'prj_title')
            ->get();

        return view('division.contract-cases.create', compact('type', 'projects', 'divisionName'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $divisionId = $user->acc_lowers ?: $user->acc_lowerm;

        $validated = $request->validate([
            'ctc_type' => 'required',
            'ctc_empnamecomp' => 'required|max:200',
            'ctc_newjobtitle' => 'required',
            'ctc_newgrade' => 'required',
            'ctc_emp_type' => 'required',
            'ctc_newsalary' => 'required|numeric',
            'ctc_newstartdt' => 'required|date',
            'ctc_newenddt' => 'required|date|after:ctc_newstartdt',
            'ctc_jd' => 'nullable',
            'ctc_cnic' => 'nullable|max:15',
            'ctc_contact' => 'nullable|max:20',
            'ctc_status' => 'nullable'
        ]);

        $status = $request->input('ctc_status', 'Draft');

        $case = new HrCtrCase();
        $case->fill($validated);
        $case->ctc_divisionid = $divisionId;
        $case->ctc_unt_id = $divisionId;
        $case->ctc_newunt_id = $divisionId;
        $case->ctc_date = now();
        $case->ctc_status = $status;
        $case->ctc_createdby = $user->acc_id; 
        $case->ctc_createdat = now();
        $case->ctc_remarks = $request->input('remarks');

        // Temporary hardcoded IDs for NOT NULL fields if needed
        $case->ctc_ctr_id = 0;
        $case->ctc_newctrtype = 0;
        $case->ctc_approvedunt_id = 0;
        $case->ctc_approvedstartdt = '1900-01-01';
        $case->ctc_approvedenddt = '1900-01-01';
        $case->ctc_approvedgrade = '';
        $case->ctc_approvedjobtitle = '';
        $case->ctc_approvedsalary = 0;
        $case->ctc_approvedctrtype = 0;
        $case->save();

        // Handle CV Upload
        if ($request->hasFile('cv_file')) {
            $path = $request->file('cv_file')->store('contract-cases/cv', 'local');
            DB::table('hr.ctrcaseattachments')->insert([
                'cat_objtype' => 'HrCtrCase',
                'cat_objid' => $case->ctc_id,
                'cat_type' => 'CV',
                'cat_path' => $path
            ]);
        }

        // Project allocations
        $mode = $request->input('project_mode', 'single');
        if ($mode == 'single') {
            $projId = $request->input('ctc_projectcode');
            HrCtrCasePlan::create([
                'ccp_ctc_id' => $case->ctc_id,
                'ccp_startdt' => $case->ctc_newstartdt,
                'ccp_enddt' => $case->ctc_newenddt,
                'ccp_hed_id' => $projId ? $projId : null
            ]);
        } else if ($request->has('monthly_project')) {
            foreach ($request->input('monthly_project') as $month => $projectId) {
                if ($projectId) {
                    HrCtrCasePlan::create([
                        'ccp_ctc_id' => $case->ctc_id,
                        'ccp_startdt' => \Carbon\Carbon::parse($month . '-01')->format('Y-m-d'),
                        'ccp_enddt' => \Carbon\Carbon::parse($month . '-01')->endOfMonth()->format('Y-m-d'),
                        'ccp_hed_id' => $projectId ? $projectId : null
                    ]);
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Case saved successfully.', 'case_id' => $case->ctc_id]);
    }

    public function show($id)
    {
        $case = HrCtrCase::findOrFail($id);
        return view('division.contract-cases.show', compact('case'));
    }

    public function release($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $case->ctc_status = 'Under HR Scrutiny';
        $case->ctc_releasedby = Auth::user()->acc_id;
        $case->ctc_releasedtg = now();
        
        // Ensure not null fields don't cause error if we didn't save them
        if (!$case->ctc_ctr_id) $case->ctc_ctr_id = 0;

        $case->save();

        return response()->json(['success' => true, 'message' => 'Case released to HR.']);
    }
}
