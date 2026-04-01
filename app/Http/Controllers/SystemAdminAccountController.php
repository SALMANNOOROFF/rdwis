<?php

namespace App\Http\Controllers;

use App\Models\CenAccount;
use App\Models\Role;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $roles = Role::where('rol_unt_id', $request->unit_id)
            ->orderBy('rol_level')
            ->get(['rol_level', 'rol_desig', 'rol_desigshort', 'rol_access', 'rol_auth', 'rol_desigtype', 'rol_reportlevel']);

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

        $role = Role::where('rol_unt_id', $unit->unt_id)
            ->where('rol_level', $request->role_level)
            ->first();

        if (!$role) {
            return back()->withErrors([
                'role_level' => 'Selected unit + level combination has no role template.',
            ])->withInput();
        }

        $acc_lowerm = $unit->unt_lowerm;
        $acc_upperm = $unit->unt_upperm;
        $acc_lowers = $unit->unt_lowers;
        $acc_uppers = $unit->unt_uppers;

        if (in_array($role->rol_desigshort, ['MD', 'DG', 'SME', 'DP&C'], true)) {
            $acc_lowers = 0;
            $acc_uppers = 0;
        }

        $area = $unit->unt_area;
        if ($role->rol_desigshort === 'SO R&D') {
            $area = 'rdwprj';
        }

        $account = new CenAccount();
        $account->acc_unt_id      = $unit->unt_id;
        $account->acc_level       = $request->role_level;
        $account->acc_type        = 'Standard';
        $account->acc_reportlevel = $role->rol_reportlevel ?? null;
        $account->acc_username    = $request->username;
        $account->acc_pass        = CenAccount::hashPassword($request->username, (string) config('auth.default_password', '12345'));
        $account->acc_startdt     = now();
        $account->acc_status      = 'Active';
        $account->acc_name        = $request->name;
        $account->acc_title       = $request->title;
        $account->acc_rank        = $request->rank;
        $account->acc_desig       = $role->rol_desig;
        $account->acc_desigshort  = $role->rol_desigshort;
        $account->acc_desigtype   = $role->rol_desigtype ?? 'specific';
        $account->acc_lowerm      = $acc_lowerm;
        $account->acc_upperm      = $acc_upperm;
        $account->acc_access      = $role->rol_access;
        $account->acc_lowers      = $acc_lowers;
        $account->acc_uppers      = $acc_uppers;
        $account->acc_untname     = $unit->unt_name;
        $account->acc_untnamesh   = $unit->unt_namesh;
        $account->acc_unttype     = $unit->unt_type;
        $account->acc_auth        = strtolower(trim((string) ($role->rol_auth ?? 'viewer')));
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
        $account->acc_pass = CenAccount::hashPassword((string) $account->acc_username, (string) config('auth.default_password', '12345'));
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

    public function cryptoTest(Request $request)
    {
        $this->middleware(['area:it', 'approver']);

        $username = (string) $request->query('username', '');
        $password = (string) $request->query('password', '');

        if ($username === '' || $password === '') {
            return response()->json(['error' => 'username and password are required'], 400);
        }

        $acc = CenAccount::where('acc_username', $username)->first();
        if (! $acc) {
            return response()->json(['error' => 'user not found'], 404);
        }

        $stored = (string) $acc->acc_pass;

        $roundCandidates = [5, 4, 3, 6, 7, 8, 2, 9, 10];
        $nameCandidates = [
            $username,
            strtolower($username),
            strtoupper($username),
            trim($username),
            strtolower(trim($username)),
        ];

        $hit = false;
        $hitRound = null;
        $hitName = null;
        foreach ($roundCandidates as $r) {
            $decrypted = \App\Models\CenAccount::verifyPassword($username, $password, $stored, $r);
            if ($decrypted) {
                $hit = true;
                $hitRound = $r;
                $hitName = $username;
                break;
            }
        }

        return response()->json([
            'username' => $username,
            'stored_sample' => substr($stored, 0, 8) . '...' . substr($stored, -8),
            'match' => $hit,
            'matched_round' => $hitRound,
            'matched_name_variant' => $hitName,
        ]);
    }
}
