<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\CenAccount;
use Illuminate\Support\Facades\Auth;

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
        if (CenAccount::verifyPassword((string) ($user?->acc_username ?? ''), $defaultPassword, (string) ($user->acc_pass ?? ''))) {
            if ($request->isMethod('get') && ! $request->session()->has('password_change.intended')) {
                $request->session()->put('password_change.intended', $request->fullUrl());
            }

            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
