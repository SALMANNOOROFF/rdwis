<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'userpass' => 'required',
        ]);

        $credentials = [
            'acc_username' => $request->username,
            'password' => $request->userpass 
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();

            // --- DB BASED REDIRECTION ---
            
            // Agar unit area 'prjrdw' hai
            if ($user->isSORD()) {
                return redirect()->route('sord.dashboard');
            }

            // Agar unit area 'prj' hai (ya koi aur)
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'username' => 'Invalid Credentials.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}