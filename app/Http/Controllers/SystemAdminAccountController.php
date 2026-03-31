<?php

namespace App\Http\Controllers;

use App\Models\CenAccount;
use App\Models\Role;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SystemAdminAccountController extends Controller
{
    public function dashboard()
    {
        $accountsOpenCount = CenAccount::where('acc_status', 'Active')->count();
        $accountsClosedCount = CenAccount::where('acc_status', '!=', 'Active')->count();

        $reversalsOpenCount = DB::table('aud.revs')->whereNull('rev_closedtg')->count();
        $reversalsClosedCount = DB::table('aud.revs')->whereNotNull('rev_closedtg')->count();

        $reversalsPendingCount = DB::table('aud.revs')
            ->whereNull('rev_closedtg')
            ->whereNotIn('rev_status', ['Cancelled', 'Fulfilled'])
            ->count();

        $reversalsFulfilledCount = DB::table('aud.revs')->where('rev_status', 'Fulfilled')->count();
        $reversalsCancelledCount = DB::table('aud.revs')->where('rev_status', 'Cancelled')->count();

        $latestReversals = DB::table('aud.revs')
            ->orderByDesc('rev_id')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'accountsOpenCount',
            'accountsClosedCount',
            'reversalsOpenCount',
            'reversalsClosedCount',
            'reversalsPendingCount',
            'reversalsFulfilledCount',
            'reversalsCancelledCount',
            'latestReversals'
        ));
    }

    public function index(Request $request)
    {
        $status = strtolower(trim((string) $request->query('status', 'open')));

        $query = CenAccount::query();
        if ($status === 'closed') {
            $query->where('acc_status', '!=', 'Active');
        } elseif ($status === 'all') {
        } else {
            $status = 'open';
            $query->where('acc_status', 'Active');
        }

        $accounts = $query
            ->orderBy('acc_status', 'desc')
            ->orderBy('acc_unt_id')
            ->orderBy('acc_username')
            ->paginate(25);

        $accounts->appends(['status' => $status]);

        $accountsOpenCount = CenAccount::where('acc_status', 'Active')->count();
        $accountsClosedCount = CenAccount::where('acc_status', '!=', 'Active')->count();

        return view('admin.accounts.index', compact('accounts', 'status', 'accountsOpenCount', 'accountsClosedCount'));
    }

    public function create()
    {
        $units = Unit::orderBy('unt_name')->get();

        return view('admin.accounts.create', compact('units'));
    }

    public function fetchRoles(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|integer',
        ]);

        $roles = Role::where('rol_xunt_id', $request->unit_id)
            ->orderBy('rol_level')
            ->get(['rol_level', 'rol_desig', 'rol_desigshort', 'rol_access', 'rol_authprj']);

        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_id'       => 'required|integer|exists:pgsql.cen.units,unt_id',
            'role_level'    => 'required|integer',
            'username'      => 'required|string|max:50|unique:pgsql.cen.accounts,acc_username',
            'name'          => 'required|string|max:150',
            'rank'          => 'nullable|string|max:100',
            'title'         => 'nullable|string|max:20',
        ]);

        $unit = Unit::findOrFail($request->unit_id);

        $role = Role::where('rol_xunt_id', $unit->unt_id)
            ->where('rol_level', $request->role_level)
            ->first();

        if (!$role) {
            return back()->withErrors([
                'role_level' => 'Selected unit + level combination has no role template.',
            ])->withInput();
        }

        $acc_lowers = $unit->unt_nlowerlimit;
        $acc_uppers = $unit->unt_nupperlimit;

        if ($unit->unt_type === 'Institute') {
            $acc_lowerm = 100000;
            $acc_upperm = 999999;
        } elseif ($unit->unt_type === 'Wing') {
            $acc_lowerm = 160000;
            $acc_upperm = 999999;
        } elseif (in_array($unit->unt_type, ['Division', 'Department'], true)) {
            $acc_lowerm = 160000;
            $acc_upperm = 999999;
        } else {
            $acc_lowerm = $acc_lowers;
            $acc_upperm = $acc_uppers;
        }

        if (in_array($role->rol_desigshort, ['MD', 'DG', 'SME', 'DP&C'], true)) {
            $acc_lowers = 0;
            $acc_uppers = 0;
        }

        $area = $unit->unt_area;
        if ($role->rol_desigshort === 'SO R&D') {
            $area = 'rdwprj';
        }

        $account = new CenAccount();
        $nextId = ((int) (CenAccount::max('acc_id') ?? 0)) + 1;
        $account->acc_id        = $nextId;
        $account->acc_unt_id      = $unit->unt_id;
        $account->acc_level       = $request->role_level;
        $account->acc_type        = 'Standard';
        $account->acc_reportlevel = $role->rol_reportlevel ?? null;
        $account->acc_username    = $request->username;
        $account->acc_pass        = Hash::make((string) config('auth.default_password', '12345'));
        $account->acc_startdt     = now();
        $account->acc_status      = 'Active';
        $account->acc_name        = $request->name;
        $account->acc_title       = $request->title;
        $account->acc_rank        = $request->rank;
        $account->acc_desig       = $role->rol_desig;
        $account->acc_desigshort  = $role->rol_desigshort;
        $account->acc_desigtype   = 'staff';
        $account->acc_lowerm      = $acc_lowerm;
        $account->acc_upperm      = $acc_upperm;
        $account->acc_access      = $role->rol_access;
        $account->acc_lowers      = $acc_lowers;
        $account->acc_uppers      = $acc_uppers;
        $account->acc_untname     = $unit->unt_name;
        $account->acc_untnamesh   = $unit->unt_namesh;
        $account->acc_unttype     = $unit->unt_type;
        $account->acc_auth        = $role->rol_authprj === 'editor' ? 'approver' : 'viewer';
        $account->acc_untarea     = strtolower(trim((string) $area));
        $account->save();

        return redirect()->route('admin.accounts.index')
            ->with('status', 'Account created successfully.');
    }

    public function close(CenAccount $account)
    {
        $account->acc_status = 'Closed';
        $account->acc_enddt = now();
        $account->save();

        return redirect()->route('admin.accounts.index')
            ->with('status', 'Account closed successfully.');
    }

    public function reopen(CenAccount $account)
    {
        $account->acc_status = 'Active';
        $account->acc_enddt = null;
        $account->save();

        return redirect()->route('admin.accounts.index', ['status' => 'open'])
            ->with('status', 'Account re-opened successfully.');
    }

    public function resetPassword(CenAccount $account)
    {
        $account->acc_pass = Hash::make((string) config('auth.default_password', '12345'));
        $account->save();

        return redirect()->back()->with('status', 'Password reset to default.');
    }

    public function reversalsIndex(Request $request)
    {
        $status = strtolower(trim((string) $request->query('status', 'open')));

        $query = DB::table('aud.revs');
        if ($status === 'closed') {
            $query->whereNotNull('rev_closedtg');
        } elseif ($status === 'all') {
        } else {
            $status = 'open';
            $query->whereNull('rev_closedtg');
        }

        $reversals = $query
            ->orderByDesc('rev_id')
            ->paginate(25);

        $reversals->appends(['status' => $status]);

        $reversalsOpenCount = DB::table('aud.revs')->whereNull('rev_closedtg')->count();
        $reversalsClosedCount = DB::table('aud.revs')->whereNotNull('rev_closedtg')->count();
        $reversalsFulfilledCount = DB::table('aud.revs')->where('rev_status', 'Fulfilled')->count();
        $reversalsCancelledCount = DB::table('aud.revs')->where('rev_status', 'Cancelled')->count();

        return view('admin.reversals.index', compact(
            'reversals',
            'status',
            'reversalsOpenCount',
            'reversalsClosedCount',
            'reversalsFulfilledCount',
            'reversalsCancelledCount'
        ));
    }
}
