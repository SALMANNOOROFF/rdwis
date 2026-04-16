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

        [$lower, $upper] = $user->acc_lowers == 0
            ? [$user->acc_lowerm, $user->acc_upperm]
            : [$user->acc_lowers, $user->acc_uppers];

        $status = $request->query('status', 'open');

        $baseQuery = DB::table('hr.ctrcases as c')
            ->leftJoin('cen.units as u', 'u.unt_id', '=', 'c.ctc_unt_id')
            ->leftJoin('cen.units as nu', 'nu.unt_id', '=', 'c.ctc_newunt_id')
            ->select([
                'c.ctc_id',
                'c.ctc_type',
                'c.ctc_date',
                'c.ctc_status',
                'c.ctc_emp_id',
                'c.ctc_empnamecomp',
                'c.ctc_unt_id',
                'u.unt_name as unit_name',
                'c.ctc_newunt_id',
                'nu.unt_name as new_unit_name',
                'c.ctc_newjobtitle',
                'c.ctc_newgrade',
                'c.ctc_newsalary',
                'c.ctc_newstartdt',
                'c.ctc_newenddt',
                'c.ctc_releasedtg',
                'c.ctc_closedtg',
            ])
            ->where(function ($q) use ($lower, $upper) {
                $q->whereBetween('c.ctc_unt_id', [$lower, $upper])
                    ->orWhereBetween('c.ctc_newunt_id', [$lower, $upper]);
            });

        if ($status === 'closed') {
            $baseQuery->whereNotNull('c.ctc_closedtg');
        } else {
            $status = 'open';
            $baseQuery->whereNull('c.ctc_closedtg');
        }

        $cases = (clone $baseQuery)
            ->orderByDesc('c.ctc_date')
            ->orderByDesc('c.ctc_id')
            ->paginate(50);

        $cases->appends(['status' => $status]);

        $openCount = DB::table('hr.ctrcases as c')
            ->where(function ($q) use ($lower, $upper) {
                $q->whereBetween('c.ctc_unt_id', [$lower, $upper])
                    ->orWhereBetween('c.ctc_newunt_id', [$lower, $upper]);
            })
            ->whereNull('c.ctc_closedtg')
            ->count();

        $closedCount = DB::table('hr.ctrcases as c')
            ->where(function ($q) use ($lower, $upper) {
                $q->whereBetween('c.ctc_unt_id', [$lower, $upper])
                    ->orWhereBetween('c.ctc_newunt_id', [$lower, $upper]);
            })
            ->whereNotNull('c.ctc_closedtg')
            ->count();

        return view('nrdi.contract_cases_new.index', compact('cases', 'status', 'openCount', 'closedCount'));
    }
}
