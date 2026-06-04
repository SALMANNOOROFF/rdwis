<?php

namespace App\Http\Controllers\MD;

use App\Http\Controllers\Controller;
use App\Models\HrCtrCase;
use Illuminate\Http\Request;

class ContractCaseController extends Controller
{
    public function index()
    {
        $cases = HrCtrCase::whereIn('ctc_status', ['Under Approval', 'Approved', 'Rejected', 'Closed'])
            ->orderBy('ctc_id', 'desc')
            ->get();

        $actionReqCases = $cases->where('ctc_status', 'Under Approval');
        $initiatedCases = collect();
        $completedCases = $cases->whereIn('ctc_status', ['Approved', 'Rejected', 'Closed']);

        return view('md.contract-cases.index', compact('cases', 'actionReqCases', 'initiatedCases', 'completedCases'));
    }

    public function show($id)
    {
        $case = HrCtrCase::findOrFail($id);
        return view('md.contract-cases.show', compact('case'));
    }

    public function approve($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $case->ctc_status = 'Approved';
        
        if ($request->has('ctc_approvedsalary')) {
            $case->ctc_approvedsalary = $request->input('ctc_approvedsalary');
            $case->ctc_approvedgrade = $request->input('ctc_approvedgrade');
            $case->ctc_approvedprob = $request->input('ctc_approvedprob');
        }

        $case->save();
        return response()->json(['success' => true, 'message' => 'Approved']);
    }

    public function return($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $case->ctc_status = 'Under Revision';
        $case->save();
        return response()->json(['success' => true, 'message' => 'Returned']);
    }
}
