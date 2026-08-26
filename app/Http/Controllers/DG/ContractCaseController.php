<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Models\HrCtrCase;
use App\Services\ContractCaseApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractCaseController extends Controller
{
    protected ContractCaseApprovalService $approvalService;

    public function __construct(ContractCaseApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    public function index()
    {
        $cases = HrCtrCase::with(['casePlans.project', 'currentSubstatus', 'employee', 'previousContract', 'newContract'])
            ->whereNotIn('ctc_status', ['Draft'])
            ->orderBy('ctc_id', 'desc')
            ->get();

        // 1. Action Required: Cases currently held by DG (Final Authority)
        $actionReqCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return $stage === 'DG' && !in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        // 2. Open / Approved: Cases approved and waiting for HR fulfillment
        $initiatedCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return $stage === 'Approved'
                && !in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        // 3. Completed: Finalized cases
        $completedCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['Fulfilled', 'Not Approved', 'Cancelled'])
                || in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        $pageTitle = 'Director General Approval Dashboard';
        return view('md.contract-cases.index', compact('cases', 'actionReqCases', 'initiatedCases', 'completedCases', 'pageTitle'));
    }

    public function show($id)
    {
        $case = HrCtrCase::with([
            'casePlans.project',
            'attachments',
            'remarksHistory',
            'currentSubstatus',
            'previousContract',
            'newContract',
            'employee',
            'unit'
        ])->findOrFail($id);

        $authorityRole = 'DG';
        return view('md.contract-cases.show', compact('case', 'authorityRole'));
    }

    public function approve($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks', 'Final approval granted by Director General (DG).');

        $this->approvalService->approve($case, $user, $request->all(), $remarks);

        return response()->json([
            'success' => true,
            'message' => 'Contract Case final approval granted by DG. Case is now ready for HR fulfillment.'
        ]);
    }

    public function return($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks', 'Returned by DG.');
        $targetStage = $request->input('target_stage', 'DDG');

        $destStage = $this->approvalService->return($case, $user, $remarks, $targetStage);

        return response()->json([
            'success' => true,
            'message' => "Case returned to {$destStage}."
        ]);
    }

    public function reject($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks', 'Case rejected by Director General (DG).');

        $this->approvalService->reject($case, $user, $remarks);

        return response()->json([
            'success' => true,
            'message' => 'Case marked as Not Approved.'
        ]);
    }
}
