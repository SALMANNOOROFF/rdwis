<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfDivision
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isDivision()) {
            return redirect()->route('dashboard');
        }
        return $next($request);
    }
}
