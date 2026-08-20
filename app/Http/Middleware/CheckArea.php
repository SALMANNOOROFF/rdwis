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

        if ($user->acc_username === 'superadminrdw') {
            return $next($request);
        }

        $userArea = strtolower(trim((string) ($user->acc_untarea ?? '')));
        $userAreas = [$userArea];

        // RDW / SORD Mapping
        if (in_array($userArea, ['rdw', 'rdwprj', 'prjrdw'], true)) {
            $userAreas = ['rdw', 'prj', 'rdwprj', 'prjrdw'];
        }

        // DG / NRDI Mapping (Full Access)
        if ($userArea === 'nrdi') {
            $userAreas = ['nrdi', 'prj', 'hr', 'fin', 'rdw', 'rdwprj', 'prjrdw', 'proc', 'prc', 'hqs'];
        }

        // Procurement Department Mapping (both proc and prc)
        if (in_array($userArea, ['proc', 'prc'], true)) {
            $userAreas = ['proc', 'prc', 'prj'];
        }

        // Finance Department Mapping
        if ($userArea === 'fin') {
            $userAreas = ['fin', 'prj'];
        }

        // HQs / DDG Mapping
        if ($userArea === 'hqs') {
            $userAreas = ['hqs', 'prj', 'rdw'];
        }

        // HR Department Mapping
        if ($userArea === 'hr') {
            $userAreas = ['hr', 'prj'];
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
