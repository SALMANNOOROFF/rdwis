<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfSord
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isSORD()) {
            return redirect()->route('sord.dashboard');
        }
        return $next($request);
    }
}
