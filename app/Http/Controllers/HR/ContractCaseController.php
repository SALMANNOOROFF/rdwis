<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HrCtrCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractCaseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));

        if ($request->has('mode')) {
            $mode = $request->query('mode') === 's' ? 's' : 'm';
            session(['hr_mode' => $mode]);
        } else {
            $defaultMode = in_array($area, ['fin', 'hr', 'nrdi', 'rdw', 'hqs']) ? 'm' : 's';
            $mode = session('hr_mode', $defaultMode);
        }

        $query = HrCtrCase::whereNotIn('ctc_status', ['Draft', 'Returned']);

        if ($mode === 's' && $user) {
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
            $query->whereBetween('ctc_divisionid', [$lower, $upper]);
        }

        $cases = $query->orderBy('ctc_id', 'desc')->get();

        $actionReqCases = $cases->where('ctc_status', 'Under HR Scrutiny');
        $initiatedCases = $cases->whereIn('ctc_status', ['Under Finance Scrutiny', 'Under Approval']);
        $completedCases = $cases->whereIn('ctc_status', ['Approved', 'Rejected', 'Closed']);

        return view('hr.contract-cases.index', compact('cases', 'actionReqCases', 'initiatedCases', 'completedCases', 'mode'));
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
