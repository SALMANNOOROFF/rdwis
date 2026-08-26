<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HrCtrCase;
use App\Services\ContractCaseApprovalService;
use App\Services\ContractCaseFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ContractCaseController extends Controller
{
    protected ContractCaseApprovalService $approvalService;
    protected ContractCaseFulfillmentService $fulfillmentService;

    public function __construct(
        ContractCaseApprovalService $approvalService,
        ContractCaseFulfillmentService $fulfillmentService
    ) {
        $this->approvalService = $approvalService;
        $this->fulfillmentService = $fulfillmentService;
    }

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

        $query = HrCtrCase::with(['casePlans.project', 'currentSubstatus', 'employee', 'previousContract', 'newContract'])
            ->whereNotIn('ctc_status', ['Draft']);

        if ($mode === 's' && $user) {
            $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
            $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
            if ($lower > 0 && $upper > 0) {
                $query->whereBetween('ctc_divisionid', [$lower, $upper]);
            }
        }

        $cases = $query->orderBy('ctc_id', 'desc')->get();

        // 1. Action Required: Cases held by HR (HR Scrutiny) OR Approved cases ready for fulfillment
        $actionReqCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['HR', 'Approved'])
                && !in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        // 2. Open / In Pipeline: Cases currently undergoing scrutiny with other authorities (Finance, MD, DDG, DG)
        $initiatedCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['Finance', 'MD', 'DDG', 'DG'])
                && !in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        // 3. Completed: Finalized cases
        $completedCases = $cases->filter(function ($c) {
            $stage = $c->current_stage;
            return in_array($stage, ['Fulfilled', 'Not Approved', 'Cancelled'])
                || in_array($c->ctc_status, ['Fulfilled', 'Closed', 'Rejected', 'Not Approved', 'Cancelled']);
        });

        return view('hr.contract-cases.index', compact('cases', 'actionReqCases', 'initiatedCases', 'completedCases', 'mode'));
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

        $strength = [];

        return view('hr.contract-cases.show', compact('case', 'strength'));
    }

    public function forward($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks', 'Forwarded to Finance for financial scrutiny.');

        $nextStage = $this->approvalService->forward($case, $user, $remarks);

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
        $remarks = $request->input('remarks', 'Returned to Division for revision.');

        $destStage = $this->approvalService->return($case, $user, $remarks, 'Division');

        return response()->json([
            'success' => true,
            'message' => "Case returned to {$destStage} for revision."
        ]);
    }

    public function fulfill($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();

        try {
            $this->fulfillmentService->fulfill($case, $user, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Contract Case fulfilled successfully! Contract has been created/updated.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function reject($id, Request $request)
    {
        $case = HrCtrCase::findOrFail($id);
        $user = Auth::user();
        $remarks = $request->input('remarks', 'Case rejected by HR.');

        $this->approvalService->reject($case, $user, $remarks);

        return response()->json([
            'success' => true,
            'message' => 'Case marked as Not Approved.'
        ]);
    }
}
