<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HrCtrCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractCaseController extends Controller
{
    public function index()
    {
        $cases = HrCtrCase::whereNotIn('ctc_status', ['Draft', 'Returned'])
            ->orderBy('ctc_id', 'desc')
            ->get();

        $actionReqCases = $cases->where('ctc_status', 'Under HR Scrutiny');
        $initiatedCases = $cases->whereIn('ctc_status', ['Under Finance Scrutiny', 'Under Approval']);
        $completedCases = $cases->whereIn('ctc_status', ['Approved', 'Rejected', 'Closed']);

        return view('hr.contract-cases.index', compact('cases', 'actionReqCases', 'initiatedCases', 'completedCases'));
    }

    public function show($id)
    {
        $case = HrCtrCase::with('casePlans')->findOrFail($id);
        
        $strength = []; // Strength stats disabled due to missing columns

        return view('hr.contract-cases.show', compact('case', 'strength'));
    }

    public function forward($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $case->ctc_status = 'Under Finance Scrutiny';
        $case->save();
        return response()->json(['success' => true, 'message' => 'Forwarded to Finance']);
    }

    public function return($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $case->ctc_status = 'Under Revision';
        $case->save();
        return response()->json(['success' => true, 'message' => 'Returned to Division']);
    }

    public function fulfill($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        // Here we'd map fields to hr.contracts and save
        $case->ctc_status = 'Fulfilled';
        $case->ctc_closedtg = now();
        $case->save();

        return response()->json(['success' => true, 'message' => 'Case Fulfilled!']);
    }
}
