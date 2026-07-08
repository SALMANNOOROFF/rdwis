<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CenAccount;

class GodModeController extends Controller
{
    public function index()
    {
        if (Auth::user()->acc_username !== 'superadminrdw') {
            abort(403, 'Unauthorized access.');
        }

        $users = CenAccount::orderBy('acc_status', 'desc')
            ->orderBy('acc_unt_id')
            ->get();

        return view('godmode.index', compact('users'));
    }

    public function impersonate($id)
    {
        // Allow if logged in as superadminrdw OR if currently impersonating (re-impersonation)
        $godId = session('impersonated_by_god');
        if (Auth::user()->acc_username !== 'superadminrdw' && !$godId) {
            abort(403, 'Unauthorized access.');
        }

        // If re-impersonating, keep the original god ID
        if (!$godId) {
            $godId = Auth::id();
        }

        $targetUser = CenAccount::findOrFail($id);

        Auth::loginUsingId($targetUser->acc_id);
        $request = request();
        $request->session()->regenerate();

        session([
            'user_id'       => $targetUser->acc_id,
            'username'      => $targetUser->acc_username,
            'name'          => $targetUser->acc_name,
            'rank'          => $targetUser->acc_rank,
            'desig'         => $targetUser->acc_desig,
            'desig_short'   => $targetUser->acc_desigshort,
            'desig_type'    => $targetUser->acc_desigtype,
            'unit_id'       => $targetUser->acc_unt_id,
            'unit_name'     => $targetUser->acc_untname,
            'unit_name_sh'  => $targetUser->acc_untnamesh,
            'unit_type'     => $targetUser->acc_unttype,
            'unit_area'     => $targetUser->acc_untarea,
            'level'         => $targetUser->acc_level,
            'access'        => $targetUser->acc_access,
            'auth'          => $targetUser->acc_auth,
            'range_lower_s' => $targetUser->acc_lowers,
            'range_upper_s' => $targetUser->acc_uppers,
            'range_lower_m' => $targetUser->acc_lowerm,
            'range_upper_m' => $targetUser->acc_upperm,
            'impersonated_by_god' => $godId
        ]);

        return redirect('/')->with('success', 'Controlling: ' . $targetUser->acc_name);
    }

    public function leaveImpersonation()
    {
        $godId = session('impersonated_by_god');
        if (!$godId) {
            return redirect('/dashboard');
        }

        $godUser = CenAccount::findOrFail($godId);
        Auth::loginUsingId($godId);

        $request = request();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        session([
            'user_id'       => $godUser->acc_id,
            'username'      => $godUser->acc_username,
            'name'          => $godUser->acc_name,
            'rank'          => $godUser->acc_rank,
            'desig'         => $godUser->acc_desig,
            'desig_short'   => $godUser->acc_desigshort,
            'desig_type'    => $godUser->acc_desigtype,
            'unit_id'       => $godUser->acc_unt_id,
            'unit_name'     => $godUser->acc_untname,
            'unit_name_sh'  => $godUser->acc_untnamesh,
            'unit_type'     => $godUser->acc_unttype,
            'unit_area'     => $godUser->acc_untarea,
            'level'         => $godUser->acc_level,
            'access'        => $godUser->acc_access,
            'auth'          => $godUser->acc_auth,
            'range_lower_s' => $godUser->acc_lowers,
            'range_upper_s' => $godUser->acc_uppers,
            'range_lower_m' => $godUser->acc_lowerm,
            'range_upper_m' => $godUser->acc_upperm,
        ]);

        return redirect('/')->with('success', 'Returned to God Mode');
    }
}
