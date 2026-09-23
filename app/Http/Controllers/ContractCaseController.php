<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractCaseController extends Controller
{
    public function projectPanel(Request $request, int $id, int $headId)
    {
        $case = \App\Models\HrCtrCase::with('casePlans')->findOrFail($id);
        $this->authorize('view', $case);
        $allocation = app(\App\Services\ContractCaseProjectService::class)
            ->allocations($case)->firstWhere('hed_id', $headId);
        abort_unless($allocation, 404, 'This project is not allocated to this contract case.');

        $section = $request->query('section', 'financial');
        abort_unless(in_array($section, ['financial', 'attachments', 'milestones'], true), 422);
        $financial = null;
        $attachments = collect();
        $milestones = collect();
        if ($section === 'financial') {
            $financial = app(\App\Services\FinancialIntelligenceService::class)->getHeadStatus($headId);
        } elseif ($allocation->hed_prj_id && $section === 'attachments') {
            $attachments = DB::table('prj.prjattachments')->where('jat_objid', $allocation->hed_prj_id)
                ->whereIn('jat_objtype', ['prj', 'Project'])->whereNotNull('jat_path')
                ->where('jat_path', '<>', '')->get();
        } elseif ($allocation->hed_prj_id && $section === 'milestones') {
            $milestones = DB::table('prj.milestones')->where('msn_xprj_id', $allocation->hed_prj_id)
                ->orderBy('msn_id')->get();
        }

        return response()->json([
            'allocation' => $allocation,
            'section' => $section,
            'financial' => $financial,
            'attachments' => $attachments,
            'milestones' => $milestones,
        ])->header('Cache-Control', 'no-store');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $area = strtolower(trim((string) ($user->acc_untarea ?? '')));

        // Route to the specialized dashboards built for Contract Cases
        if ($area === 'hr') {
            return redirect()->route('hr.contract-cases.index');
        } elseif ($area === 'fin') {
            return redirect()->route('finance.contract-cases.index');
        } elseif ($area === 'rdw') {
            return redirect()->route('md.contract-cases.index');
        } elseif ($area === 'hqs') {
            return redirect()->route('ddg.contract-cases.index');
        } elseif ($area === 'nrdi') {
            return redirect()->route('dg.contract-cases.index');
        } else {
            // Default to Division Initiator dashboard
            return redirect()->route('division.contract-cases.index');
        }
    }
}
