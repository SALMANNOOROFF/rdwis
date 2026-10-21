<?php

namespace App\Http\Controllers\DLOG;

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
        $user = Auth::user();

        // All non-draft contract cases
        $allCases = HrCtrCase::with(['casePlans.project', 'currentSubstatus', 'employee', 'previousContract', 'newContract', 'remarksHistory'])
            ->whereNotIn('ctc_status', ['Draft'])
            ->orderBy('ctc_id', 'desc')
            ->get();

        // 1. Action Required: Strictly cases currently at stage 'DLOG' (forwarded to DLOG by MD / DDG / DG)
        // If not sent to DLOG by MD/DDG/DG, queue remains completely empty (0)
        $actionReqCases = $allCases->filter(function ($c) {
            $stage = $c->current_stage;
            return $stage === 'DLOG' && !in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        // 2. Open / In Pipeline: Cases that were routed through DLOG and currently with other authorities
        $actionReqIds = $actionReqCases->pluck('ctc_id')->toArray();
        $initiatedCases = $allCases->filter(function ($c) use ($actionReqIds, $user) {
            if (in_array($c->ctc_id, $actionReqIds) || in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled'])) {
                return false;
            }
            // Must have passed through DLOG
            return $c->remarksHistory->contains(function($r) use ($user) {
                return (int)$r->crmk_acc_id === (int)$user->acc_id || stripos($r->crmk_role ?? '', 'DLOG') !== false;
            });
        });

        // 3. Completed: Finalized cases that were routed through DLOG
        $completedCases = $allCases->filter(function ($c) use ($user) {
            $stage = $c->current_stage;
            $isFinal = in_array($stage, ['Fulfilled', 'Not Approved', 'Cancelled'])
                || in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
            if (!$isFinal) return false;
            return $c->remarksHistory->contains(function($r) use ($user) {
                return (int)$r->crmk_acc_id === (int)$user->acc_id || stripos($r->crmk_role ?? '', 'DLOG') !== false;
            });
        });

        $cases = $actionReqCases->concat($initiatedCases)->concat($completedCases);
        $pageTitle = 'Director Logistics (DLOG) Approval Portal';

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

        $this->authorize('view', $case);

        $authorityRole = 'DLOG';
        $authDetails = $this->approvalService->getApprovalAuthorityDetails($case);
        $canApprove = false;

        return view('md.contract-cases.show', compact('case', 'authorityRole', 'authDetails', 'canApprove'));
    }

    public function approve($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $this->authorize('processAction', [$case, 'approve']);
        $user = Auth::user();

        if (!$this->approvalService->canApprove('DLOG', $case)) {
            $required = $this->approvalService->getRequiredAuthority($case);
            return response()->json([
                'success' => false,
                'message' => "Case terms exceed delegated approval authority. This case must be forwarded to {$required} for approval."
            ], 403);
        }

        $remarks = $request->input('remarks');
        if (empty(trim($remarks ?? ''))) {
            return response()->json(['success' => false, 'message' => 'Remarks are mandatory.'], 422);
        }

        $this->approvalService->approve($case, $user, $request->all(), $remarks);

        return response()->json([
            'success' => true,
            'message' => 'Contract Case approved under delegated authority.'
        ]);
    }

    public function forward($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $this->authorize('processAction', [$case, 'forward']);
        $user = Auth::user();
        $remarks = $request->input('remarks');
        if (empty(trim($remarks ?? ''))) {
            return response()->json(['success' => false, 'message' => 'Remarks are mandatory.'], 422);
        }
        $targetStage = $request->input('target_destination') ?? $request->input('target_stage') ?? 'MD';

        $nextStage = $this->approvalService->forward($case, $user, $remarks, $targetStage);

        return response()->json([
            'success' => true,
            'message' => "Case forwarded to {$nextStage} successfully.",
            'next_stage' => $nextStage
        ]);
    }

    public function return($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $this->authorize('processAction', [$case, 'return']);
        $user = Auth::user();
        $remarks = $request->input('remarks');
        if (empty(trim($remarks ?? ''))) {
            return response()->json(['success' => false, 'message' => 'Remarks are mandatory.'], 422);
        }
        $targetStage = $request->input('target_destination') ?? $request->input('target_stage') ?? 'MD';

        $destStage = $this->approvalService->return($case, $user, $remarks, $targetStage);

        return response()->json([
            'success' => true,
            'message' => "Case returned to {$destStage}."
        ]);
    }

    public function reject($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $this->authorize('processAction', [$case, 'reject']);
        $user = Auth::user();
        $remarks = $request->input('remarks', 'Case returned / rejected by DLOG.');
        if (empty(trim($remarks ?? ''))) {
            return response()->json(['success' => false, 'message' => 'Remarks are mandatory.'], 422);
        }

        $this->approvalService->reject($case, $user, $remarks);

        return response()->json([
            'success' => true,
            'message' => 'Case marked as Not Approved.'
        ]);
    }
}
