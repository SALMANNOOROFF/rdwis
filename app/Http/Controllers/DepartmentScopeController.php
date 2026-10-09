<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentScopeController extends Controller
{
    /**
     * Toggle active department scope between 'all' (Global Directorate) and 'my' (Departmental/Divisional).
     */
    public function toggleScope(Request $request)
    {
        $user = Auth::user();
        if (!$user || !method_exists($user, 'isCentralDepartment') || !$user->isCentralDepartment()) {
            return back()->with('error', 'Unauthorized scope toggle.');
        }

        $targetScope = $request->input('scope');
        if (!in_array($targetScope, ['all', 'my'], true)) {
            $currentScope = session('active_dept_scope', 'all');
            $targetScope = ($currentScope === 'my') ? 'all' : 'my';
        }

        session(['active_dept_scope' => $targetScope]);

        $deptName = $user->acc_untname ?? $user->acc_untnamesh ?? 'Department';

        if ($targetScope === 'my') {
            $msg = "Switched to My Department mode ({$deptName}). Your views and actions are now scoped strictly to your department.";
            return redirect()->route('dashboard')->with('success', $msg);
        } else {
            $msg = "Switched to All Departments (Global Directorate) mode.";
            
            $untId = (int) ($user->acc_unt_id ?? 0);
            $area = strtolower(trim((string) ($user->acc_untarea ?? '')));

            $targetRoute = 'dashboard';
            if (in_array($area, ['proc', 'prc']) || $untId === 810000) {
                $targetRoute = 'nrdi.procurement.purchase_cases.index';
            } elseif (in_array($area, ['fin', 'finance']) || $untId === 800000) {
                $targetRoute = 'fin.dashboard';
            } elseif ($area === 'hr' || $untId === 820000) {
                $targetRoute = 'hr.dashboard';
            } elseif (in_array($area, ['is', 'it']) || in_array($untId, [860000, 880000])) {
                $targetRoute = 'nrdi.dashboard';
            }

            return redirect()->route($targetRoute)->with('success', $msg);
        }
    }
}
