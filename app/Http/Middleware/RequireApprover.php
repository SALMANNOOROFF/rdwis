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

        $area = strtolower(trim((string) ($user?->acc_untarea ?? '')));
        $desigShort = strtoupper(trim((string) ($user?->acc_desigshort ?? '')));

        if ($area === 'nrdi' && in_array($desigShort, ['DG', 'MD'], true)) {
            abort(403, 'This action is not available in command view.');
        }

        $auth = strtolower(trim((string) ($user?->acc_auth ?? '')));
        if (! $user || ! in_array($auth, ['approver', 'editor'], true)) {
            abort(403, 'This action requires approver-level access.');
        }

        return $next($request);
    }
}
