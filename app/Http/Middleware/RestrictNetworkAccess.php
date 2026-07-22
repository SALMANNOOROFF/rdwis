<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RestrictNetworkAccess
{
    public function handle(Request $request, Closure $next)
    {
        $allowedIpsRaw = config('app.allowed_ips', '');
        $allowedIps = array_filter(array_map('trim', explode(',', (string) $allowedIpsRaw)));
        $clientIp = $request->ip();

        // Allow localhost/loopback by default
        if (in_array($clientIp, ['127.0.0.1', '::1', 'localhost'], true)) {
            return $next($request);
        }

        if (! empty($allowedIps) && ! in_array($clientIp, $allowedIps, true)) {
            Log::warning('Blocked access attempt from unauthorized IP', [
                'ip' => $clientIp,
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);
            
            abort(403, 'Access denied: Your IP address is not authorized to access this application.');
        }

        return $next($request);
    }
}
