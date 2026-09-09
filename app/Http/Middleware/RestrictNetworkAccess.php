<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RestrictNetworkAccess
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Get IPs from config/allowed_ips.php or app.allowed_ips (for testing)
        $appAllowed = config('app.allowed_ips');
        if ($appAllowed !== null) {
            $allowedIps = is_array($appAllowed) ? $appAllowed : array_map('trim', explode(',', (string) $appAllowed));
            $blockedIps = [];
        } else {
            $ipsConfigFile = config('allowed_ips', []);
            if (isset($ipsConfigFile['allowed']) || isset($ipsConfigFile['blocked'])) {
                $allowedIps = $ipsConfigFile['allowed'] ?? [];
                $blockedIps = $ipsConfigFile['blocked'] ?? [];
            } else {
                $allowedIps = is_array($ipsConfigFile) ? $ipsConfigFile : [];
                $blockedIps = [];
            }
        }
        
        $allowedIps = array_unique(array_filter($allowedIps));
        $blockedIps = array_unique(array_filter($blockedIps));
        
        $rawClientIp = $request->ip() ?: '127.0.0.1';
        // Strip IPv6-mapped IPv4 prefix (e.g. ::ffff:10.120.29.158 -> 10.120.29.158)
        $clientIp = preg_replace('/^::ffff:/i', '', trim($rawClientIp));

        // Allow localhost/loopback by default
        if (in_array($clientIp, ['127.0.0.1', '::1', 'localhost'], true)) {
            return $next($request);
        }

        // Check if IP is explicitly blocked
        if (! empty($blockedIps)) {
            foreach ($blockedIps as $blockedPattern) {
                if ($this->ipMatches($clientIp, $blockedPattern)) {
                    Log::warning('Blocked access attempt from explicitly blocked IP', [
                        'ip' => $clientIp,
                        'url' => $request->fullUrl(),
                        'user_agent' => $request->userAgent(),
                        'timestamp' => now(),
                    ]);
                    
                    abort(403, 'Access denied: Your IP address has been explicitly blocked.');
                }
            }
        }

        if (! empty($allowedIps)) {
            // Wildcard allow all
            if (in_array('*', $allowedIps, true)) {
                return $next($request);
            }

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
                
                abort(403, "Access denied: Your IP address ({$clientIp}) is not authorized to access this application. (Please add {$clientIp} to config/allowed_ips.php)");
            }
        }

        return $next($request);
    }

    /**
     * Check if a client IP matches an allowed IP pattern (exact, wildcard, range, or CIDR).
     *
     * @param string $clientIp
     * @param string $pattern
     * @return bool
     */
    private function ipMatches(string $clientIp, string $pattern): bool
    {
        $clientIp = preg_replace('/^::ffff:/i', '', trim($clientIp));
        $pattern = trim($pattern);

        if ($pattern === '*' || $clientIp === $pattern) {
            return true;
        }

        // Handle wildcard pattern (e.g., 10.* or 192.168.*)
        if (str_contains($pattern, '*') && fnmatch($pattern, $clientIp)) {
            return true;
        }

        // Handle IP range (e.g., 10.120.29.1-10.120.29.200 or 10.120.29.5-20)
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
                
                $clientLong = (float) sprintf('%u', ip2long($clientIp));
                $startLong = (float) sprintf('%u', ip2long($start));
                $endLong = (float) sprintf('%u', ip2long($end));

                if ($startLong > $endLong) {
                    [$startLong, $endLong] = [$endLong, $startLong];
                }

                return $clientLong >= $startLong && $clientLong <= $endLong;
            }
        }

        // Handle CIDR notation (e.g., 10.120.29.0/24 or 10.0.0.0/8)
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
                    $mask = -1 << (32 - $bits);
                    return ($clientLong & $mask) === ($subnetLong & $mask);
                }
            }
        }

        return false;
    }
}
