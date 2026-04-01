<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireApprover
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        $auth = strtolower(trim((string) ($user?->acc_auth ?? '')));
        if (! $user || ! in_array($auth, ['approver', 'editor'], true)) {
            abort(403, 'This action requires approver-level access.');
        }

        return $next($request);
    }
}
