<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self';"
        );

        // Modify XSRF-TOKEN cookie to expire on close (session cookie) so scanners don't flag it as a permanent cookie.
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === 'XSRF-TOKEN') {
                $response->headers->setCookie(
                    new \Symfony\Component\HttpFoundation\Cookie(
                        $cookie->getName(),
                        $cookie->getValue(),
                        0, // 0 means expire on browser close
                        $cookie->getPath(),
                        $cookie->getDomain(),
                        $cookie->isSecure(),
                        $cookie->isHttpOnly(),
                        $cookie->isRaw(),
                        $cookie->getSameSite()
                    )
                );
            }
        }

        // Strict-Transport-Security (HSTS) - uncomment and adjust if using HTTPS
        // $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
