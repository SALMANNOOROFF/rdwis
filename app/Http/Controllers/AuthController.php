<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CenAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showLogin()
    {
        return $this->showLoginForm();
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'userpass' => 'required|string',
        ]);

        $credentials = [
            'username' => $request->input('username'),
            'password' => $request->input('userpass'),
        ];

        // Authenticate against cen.accounts using acc_username; no remember_token column used
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            session([
                'user_id'       => $user->acc_id,
                'username'      => $user->acc_username,
                'name'          => $user->acc_name,
                'rank'          => $user->acc_rank,
                'desig'         => $user->acc_desig,
                'desig_short'   => $user->acc_desigshort,
                'desig_type'    => $user->acc_desigtype,
                'unit_id'       => $user->acc_unt_id,
                'unit_name'     => $user->acc_untname,
                'unit_name_sh'  => $user->acc_untnamesh,
                'unit_type'     => $user->acc_unttype,
                'unit_area'     => $user->acc_untarea,
                'level'         => $user->acc_level,
                'access'        => $user->acc_access,
                'auth'          => $user->acc_auth,
                'range_lower_s' => $user->acc_lowers,
                'range_upper_s' => $user->acc_uppers,
                'range_lower_m' => $user->acc_lowerm,
                'range_upper_m' => $user->acc_upperm,
            ]);

            return redirect()->intended($this->redirectBasedOnArea());
        }

        return back()->withErrors([
            'username' => 'Invalid credentials or account is not active.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showChangePasswordForm()
    {
        return view('auth.change_password');
    }

    public function changePassword(Request $request)
    {
        $defaultPassword = (string) config('auth.default_password', '12345');

        $request->validate([
            'password' => ['required', 'string', 'min:5', 'confirmed', Rule::notIn([$defaultPassword])],
        ]);

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $user->acc_pass = CenAccount::hashPassword((string) ($user?->acc_username ?? ''), $request->input('password'));
        $user->save();

        $intended = $request->session()->pull('password_change.intended');
        if (is_string($intended) && $intended !== '') {
            return redirect($intended);
        }

        return redirect()->to($this->redirectBasedOnArea());
    }

    private function redirectBasedOnArea(): string
    {
        $user = Auth::user();

        if (method_exists($user, 'isSORD') && $user->isSORD()) {
            return route('sord.dashboard');
        }

        $area = strtolower(trim((string) ($user?->acc_untarea ?? '')));

        return match ($area) {
            'hr'     => route('divhr.employelist'),
            'fin'    => route('viewpurchasecase'),
            'nrdi'   => route('nrdi.dashboard'),
            'prj'    => route('dashboard'),
            'rdwprj', 'prjrdw' => route('sord.dashboard'),
            'it'     => route('admin.dashboard'),
            default  => route('dashboard'),
        };
    }
}
