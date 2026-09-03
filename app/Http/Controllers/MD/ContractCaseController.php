<?php

namespace App\Http\Controllers\MD;

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

        // 1. Action Required: Cases currently held by MD
        $actionReqCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return $stage === 'MD' && !in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        // 2. Open / In Pipeline: Cases forwarded to DDG, DG, or Approved
        $initiatedCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['DDG', 'DG', 'Approved'])
                && !in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        // 3. Completed: Finalized cases
        $completedCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['Fulfilled', 'Not Approved', 'Cancelled'])
                || in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        return view('md.contract-cases.index', compact('cases', 'actionReqCases', 'initiatedCases', 'completedCases'));
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

        $authorityRole = 'MD';
        $authDetails = $this->approvalService->getApprovalAuthorityDetails($case);
        $canApprove = $this->approvalService->canApprove('MD', $case);

        return view('md.contract-cases.show', compact('case', 'authorityRole', 'authDetails', 'canApprove'));
    }

    public function approve($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();

        if (!$this->approvalService->canApprove('MD', $case)) {
            $required = $this->approvalService->getRequiredAuthority($case);
            return response()->json([
                'success' => false,
                'message' => "Case terms exceed MD delegated approval authority. This case must be forwarded to {$required} for approval."
            ], 403);
        }

        $remarks = $request->input('remarks', 'Approved by Managing Director under delegated authority.');

        $this->approvalService->approve($case, $user, $request->all(), $remarks);

        return response()->json([
            'success' => true,
            'message' => 'Contract Case approved by MD under delegated authority.'
        ]);
    }

    public function forward($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks', 'Forwarded to DDG for review.');

        $nextStage = $this->approvalService->forward($case, $user, $remarks, 'DDG');

        return response()->json([
            'success' => true,
            'message' => "Case forwarded to {$nextStage} successfully.",
            'next_stage' => $nextStage
        ]);
    }

    public function return($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks', 'Returned by MD.');
        $targetStage = $request->input('target_stage', 'Finance');

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
        $remarks = $request->input('remarks', 'Case rejected by MD.');

        $this->approvalService->reject($case, $user, $remarks);

        return response()->json([
            'success' => true,
            'message' => 'Case marked as Not Approved.'
        ]);
    }
}
