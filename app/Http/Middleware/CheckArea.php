<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckArea
{
    public function handle(Request $request, Closure $next, string ...$areas)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $userAreas = [$userArea];

        if (in_array($userArea, ['rdwprj', 'prjrdw'], true)) {
            $userAreas = ['rdw', 'prj', 'rdwprj', 'prjrdw'];
        }

        if ($userArea === 'nrdi') {
            $userAreas = ['nrdi', 'prj', 'hr', 'fin', 'rdw', 'rdwprj', 'prjrdw'];
        }

        foreach ($areas as $area) {
            $areaNorm = strtolower(trim($area));
            if (in_array($areaNorm, $userAreas, true)) {
                return $next($request);
            }
        }

        abort(403, 'Access denied. Your account is not authorized for this area.');
    }
}
