<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Services\Auth\AreaDefinition;
use App\Services\Auth\UserAccessContext;

class CheckArea
{
    public function handle(Request $request, Closure $next, string ...$areas)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (UserAccessContext::forUser($user)->isSuperAdmin()) {
            return $next($request);
        }

        $userArea = (string) ($user->acc_untarea ?? '');
        $allowedAreas = AreaDefinition::getAllowedRouteAreas($userArea);

        foreach ($areas as $area) {
            $areaNorm = strtolower(trim($area));
            if (in_array($areaNorm, $allowedAreas, true)) {
                return $next($request);
            }
        }

        abort(403, 'Access denied. Your account is not authorized for this area.');
    }
}
