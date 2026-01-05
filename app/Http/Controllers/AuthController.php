<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // Login Page Show Karna
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Login Process Karna
    public function login(Request $request)
    {
        // 1. Validation
        $request->validate([
            'username' => 'required',
            'userpass' => 'required',
        ]);

        // 2. Credentials check karna
        // Note: Hum yahan assume kar rahe hain ke DB mein password Hashed (Bcrypt) hai.
        // Agar DB mein password plain text hai, to logic badalni padegi.
        $credentials = [
            'acc_username' => $request->username,
            'password' => $request->userpass // Laravel internal check ke liye 'password' key mangta hai
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // Login successful -> Dashboard/Projects pe bhejo
            return redirect()->route('dashboard');
        }

        // 3. Login Failed
        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ]);
    }

    // Logout Function
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}