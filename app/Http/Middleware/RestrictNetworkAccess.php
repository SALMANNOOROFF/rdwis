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

        if (! empty($allowedIps)) {
            $isAllowed = false;
            foreach ($allowedIps as $allowedPattern) {
                if ($this->ipMatches($clientIp, $allowedPattern)) {
                    $isAllowed = true;
                    break;
                }
            }

            if (! $isAllowed) {
                Log::warning('Blocked access attempt from unauthorized IP', [
                    'ip' => $clientIp,
                    'url' => $request->fullUrl(),
                    'user_agent' => $request->userAgent(),
                    'timestamp' => now(),
                ]);
                
                abort(403, 'Access denied: Your IP address is not authorized to access this application.');
            }
        }

        return $next($request);
    }

    /**
     * Check if a client IP matches an allowed IP pattern (exact, range, or CIDR).
     *
     * @param string $clientIp
     * @param string $pattern
     * @return bool
     */
    private function ipMatches(string $clientIp, string $pattern): bool
    {
        if ($clientIp === $pattern) {
            return true;
        }

        // Handle IP range (e.g., 10.120.29.1-10.120.29.200)
        if (str_contains($pattern, '-')) {
            [$start, $end] = array_map('trim', explode('-', $pattern, 2));

            // If the end IP is just a single number (e.g., 10.120.29.1-200), construct the full IP
            if (!filter_var($end, FILTER_VALIDATE_IP) && is_numeric($end)) {
                $lastDot = strrpos($start, '.');
                if ($lastDot !== false) {
                    $end = substr($start, 0, $lastDot + 1) . $end;
                }
            }

            if (filter_var($start, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
                filter_var($end, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
                filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                
                $clientLong = ip2long($clientIp);
                $startLong = ip2long($start);
                $endLong = ip2long($end);

                if ($clientLong !== false && $startLong !== false && $endLong !== false) {
                    $clientFloat = (float) sprintf('%u', $clientLong);
                    $startFloat = (float) sprintf('%u', $startLong);
                    $endFloat = (float) sprintf('%u', $endLong);

                    if ($startFloat > $endFloat) {
                        [$startFloat, $endFloat] = [$endFloat, $startFloat];
                    }

                    return $clientFloat >= $startFloat && $clientFloat <= $endFloat;
                }
            }
        }

        // Handle CIDR notation (e.g., 10.120.29.0/24)
        if (str_contains($pattern, '/')) {
            [$subnet, $bits] = array_map('trim', explode('/', $pattern, 2));

            if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
                filter_var($clientIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
                is_numeric($bits) && $bits >= 0 && $bits <= 32) {
                
                $bits = (int) $bits;
                if ($bits === 0) {
                    return true;
                }

                $clientLong = ip2long($clientIp);
                $subnetLong = ip2long($subnet);

                if ($clientLong !== false && $subnetLong !== false) {
                    return ($clientLong >> (32 - $bits)) === ($subnetLong >> (32 - $bits));
                }
            }
        }

        return false;
    }
}
