<?php

namespace App\Http\Controllers\HrForms;

use App\Http\Controllers\Controller;
use App\Models\HrForms\ApprovalChain;
use App\Models\HrForms\HiringTypeMap;
use App\Models\HrForms\SalaryBand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller
{
    /**
     * Show HR Forms configuration settings page.
     */
    public function index()
    {
        $salaryBands = SalaryBand::orderBy('id')->get();
        $approvalChains = ApprovalChain::orderBy('form_code')->orderBy('grade_level')->orderBy('sequence')->get();
        $hiringTypeMaps = HiringTypeMap::orderBy('id')->get();

        return view('hrforms.settings', compact('salaryBands', 'approvalChains', 'hiringTypeMaps'));
    }

    /**
     * Update settings (salary bands, approval chains, hiring type mappings).
     */
    public function update(Request $request): JsonResponse
    {
        $type = $request->input('setting_type');

        if ($type === 'salary_band') {
            $id = $request->input('id');
            $band = SalaryBand::findOrFail($id);
            $band->min_salary = (float) $request->input('min_salary', $band->min_salary);
            $band->max_salary = (float) $request->input('max_salary', $band->max_salary);
            if ($request->has('approval_level')) {
                $band->approval_level = $request->input('approval_level');
            }
            $band->save();

            return response()->json(['success' => true, 'message' => "Salary band for {$band->designation} updated successfully.", 'band' => $band]);
        }

        if ($type === 'approval_chain') {
            $id = $request->input('id');
            $step = ApprovalChain::findOrFail($id);
            $step->role_title = $request->input('role_title', $step->role_title);
            $step->action_type = $request->input('action_type', $step->action_type);
            $step->approver_role = $request->input('approver_role', $step->approver_role);
            $step->save();

            return response()->json(['success' => true, 'message' => "Approval step {$step->form_code} seq {$step->sequence} updated successfully.", 'step' => $step]);
        }

        if ($type === 'hiring_type_map') {
            $id = $request->input('id');
            $map = HiringTypeMap::findOrFail($id);
            $map->hiring_type = $request->input('hiring_type', $map->hiring_type);
            $map->note = $request->input('note', $map->note);
            $map->is_active = (bool) $request->input('is_active', true);
            $map->save();

            return response()->json(['success' => true, 'message' => "Mapping for '{$map->ctc_type}' updated successfully.", 'map' => $map]);
        }

        return response()->json(['success' => false, 'message' => 'Unknown setting type.'], 400);
    }
}
