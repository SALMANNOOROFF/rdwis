<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (in_array($routeName, ['password.change', 'password.update', 'logout', 'debug.user'], true)) {
            return $next($request);
        }

        $defaultPassword = (string) config('auth.default_password', '12345');
        if (Hash::check($defaultPassword, (string) ($user->acc_pass ?? ''))) {
            if ($request->isMethod('get') && ! $request->session()->has('password_change.intended')) {
                $request->session()->put('password_change.intended', $request->fullUrl());
            }

            return redirect()->route('password.change');
        }

        return $next($request);
    }
}

