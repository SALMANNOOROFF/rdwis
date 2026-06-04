<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractCaseController extends Controller
{
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
        } else {
            // Default to Division Initiator dashboard
            return redirect()->route('division.contract-cases.index');
        }
    }
}
