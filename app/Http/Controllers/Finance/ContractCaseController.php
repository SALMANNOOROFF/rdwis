<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\HrCtrCase;
use Illuminate\Http\Request;

class ContractCaseController extends Controller
{
    public function index()
    {
        $cases = HrCtrCase::whereIn('ctc_status', ['Under Finance Scrutiny', 'Under Approval', 'Approved', 'Rejected', 'Closed'])
            ->orderBy('ctc_id', 'desc')
            ->get();

        $actionReqCases = $cases->where('ctc_status', 'Under Finance Scrutiny');
        $initiatedCases = $cases->whereIn('ctc_status', ['Under Approval']);
        $completedCases = $cases->whereIn('ctc_status', ['Approved', 'Rejected', 'Closed']);

        return view('finance.contract-cases.index', compact('cases', 'actionReqCases', 'initiatedCases', 'completedCases'));
    }

    public function show($id)
    {
        $case = HrCtrCase::findOrFail($id);
        return view('finance.contract-cases.show', compact('case'));
    }

    public function forward($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $case->ctc_status = 'Under Approval';
        $case->save();
        return response()->json(['success' => true, 'message' => 'Forwarded to MD']);
    }

    public function return($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $case->ctc_status = 'Under Revision';
        $case->save();
        return response()->json(['success' => true, 'message' => 'Returned']);
    }
}
