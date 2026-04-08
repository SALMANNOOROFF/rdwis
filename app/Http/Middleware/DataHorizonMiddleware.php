<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class DataHorizonMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Intercept URL mode parameter or default to 'm'
            // Keep in session to persist across pages if needed, or just per request.
            $mode = $request->query('mode', session('global_horizon_mode', 'm'));
            
            // Save to session so it persists across modules without constant URL parameters
            session(['global_horizon_mode' => $mode]);

            // Calculate active limits mathematically based on MS Access rules
            if ($mode === 's') {
                $lower = $user->acc_lowers == 0 ? $user->acc_lowerm : $user->acc_lowers;
                $upper = $user->acc_lowers == 0 ? $user->acc_upperm : $user->acc_uppers;
                $varModeStr = 'approver-s';
            } else {
                $lower = $user->acc_lowerm;
                $upper = $user->acc_upperm;
                $varModeStr = 'approver-m';
            }

            // DG specific override support:
            // If the user visits a specific division, some DG dashboards might pass ?division=
            if ($request->has('division')) {
                // Here you would theoretically lookup the division limits if it was dynamically loaded
                // As an example, if the DG intercepts it, the console logs the override.
                $varModeStr .= ' (DG Override active)';
            }

            $userAuth = (string) ($user->acc_auth ?? 'viewer');

            // Share these globally to ALL blade templates
            View::share('global_mode', $mode);
            View::share('global_lower', $lower);
            View::share('global_upper', $upper);
            View::share('global_varModeStr', $varModeStr);
            View::share('global_userAuth', $userAuth);
        }

        return $next($request);
    }
}
