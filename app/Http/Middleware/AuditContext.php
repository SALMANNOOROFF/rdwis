<?php

namespace App\Http\Middleware;

use App\Services\Auth\UserAccessContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pushes the request's identity into PostgreSQL session variables so the
 * syslog row triggers know WHO is writing, from WHERE, and on WHICH route.
 *
 * Without this, writes are still logged -- but as sev_source='db' with a null
 * actor, which the System Logs screen flags as an unowned write (§8.2).
 *
 * Design: docs/SYSTEM_LOGS_SCHEMA_DESIGN.md §4.1
 */
class AuditContext
{
    /** Keys set on every request, so a pooled connection never leaks context. */
    private const KEYS = [
        'acc_id', 'username', 'role_slug', 'ip', 'fwd_ip', 'user_agent',
        'hostname', 'route', 'method', 'url', 'session_id', 'request_id',
        'source', 'event_id', 'event_dtg', 'rc_seq',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $this->apply($request);

        return $next($request);
    }

    private function apply(Request $request): void
    {
        try {
            if (DB::connection()->getDriverName() !== 'pgsql') {
                return;
            }

            $user = $request->user();
            $roleSlug = null;

            if ($user) {
                try {
                    $roleSlug = UserAccessContext::forUser($user)->getRoleSlug();
                } catch (\Throwable) {
                    $roleSlug = null;
                }
            }

            $values = [
                'acc_id'     => (string) ($user->acc_id ?? ''),
                'username'   => (string) ($user->acc_username ?? ''),
                'role_slug'  => (string) ($roleSlug ?? ''),
                'ip'         => (string) ($request->ip() ?? ''),
                'fwd_ip'     => Str::limit((string) $request->header('X-Forwarded-For', ''), 250, ''),
                'user_agent' => Str::limit((string) $request->userAgent(), 490, ''),
                'hostname'   => Str::limit((string) $request->getHost(), 250, ''),
                'route'      => (string) (optional($request->route())->getName() ?? ''),
                'method'     => $request->method(),
                'url'        => Str::limit($request->fullUrl(), 990, ''),
                'session_id' => $request->hasSession() ? (string) $request->session()->getId() : '',
                'request_id' => (string) Str::uuid(),
                'source'     => 'app',
                // cleared per request so one act's diffs never attach to a previous event
                'event_id'   => '',
                'event_dtg'  => '',
                'rc_seq'     => '0',
            ];

            $parts = [];
            $bindings = [];
            foreach (self::KEYS as $key) {
                $parts[] = "set_config('rdwis.{$key}', ?, false)";
                $bindings[] = $values[$key] ?? '';
            }

            DB::statement('SELECT ' . implode(', ', $parts), $bindings);
        } catch (\Throwable $e) {
            // Never let audit context break a request. A missing context means the
            // write is logged as unowned, which is visible rather than silent.
            report($e);
        }
    }
}
