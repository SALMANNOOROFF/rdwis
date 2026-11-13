<?php

namespace App\Services\Syslog;

use Illuminate\Support\Facades\DB;

/**
 * The application's entry point into the audit trail.
 *
 * The database triggers already record WHAT changed. This class records what the
 * database cannot know: the business meaning of the act, the authority it was
 * done under, the reason given, and who the case was handed to.
 *
 * Call log() BEFORE the writes it describes, inside the same transaction, so the
 * trigger-written field diffs attach to this event header.
 *
 * Design: docs/SYSTEM_LOGS_SCHEMA_DESIGN.md §4.2, §5A.3, §5D.8
 */
class Audit
{
    /** Open an event header. Returns the sev_id, or null if the trail is unavailable. */
    public static function log(string $action, array $opts = []): ?int
    {
        try {
            $row = DB::selectOne(
                'SELECT syslog.begin_event(
                    ?::varchar, ?::varchar, ?::varchar, ?::varchar, ?::varchar, ?::varchar,
                    ?::text, ?::varchar, ?::varchar, ?::varchar, ?::smallint,
                    ?::integer, ?::integer, ?::smallint, ?::integer, ?::varchar,
                    ?::boolean, ?::boolean) AS id',
                [
                    $action,
                    $opts['object']        ?? null,
                    isset($opts['objid']) ? (string) $opts['objid'] : null,
                    $opts['objref']        ?? null,
                    $opts['stage_from']    ?? null,
                    $opts['stage_to']      ?? null,
                    $opts['remarks']       ?? null,
                    $opts['reason_code']   ?? null,
                    $opts['authority_ref'] ?? null,
                    $opts['result']        ?? 'ok',
                    $opts['severity']      ?? 0,
                    $opts['from_holder_id'] ?? null,
                    $opts['to_holder_id']   ?? null,
                    $opts['minute_no']      ?? null,
                    $opts['case_unt_id']    ?? null,
                    $opts['role_slug']      ?? null,
                    false,
                    (bool) ($opts['snapshot'] ?? false),
                ]
            );

            return $row?->id !== null ? (int) $row->id : null;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /**
     * A case handover: "kisne kisko bheja, kya remarks likhe" plus a full
     * point-in-time snapshot of the case at that instant.
     */
    public static function forward(string $object, int|string $objid, array $opts = []): ?int
    {
        return self::log($opts['action'] ?? 'forwarded', array_merge($opts, [
            'object'   => $object,
            'objid'    => $objid,
            'snapshot' => $opts['snapshot'] ?? true,
        ]));
    }

    /** Close the current event so the next act in this request gets its own header. */
    public static function end(): void
    {
        try {
            DB::statement('SELECT syslog.end_event()');
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Take a case snapshot against an existing event. */
    public static function snapshot(int $sevId, string $object, int|string $objid): ?int
    {
        try {
            $row = DB::selectOne(
                'SELECT syslog.take_case_snapshot(?::bigint, ?::varchar, ?::varchar) AS id',
                [$sevId, $object, (string) $objid]
            );
            return $row?->id !== null ? (int) $row->id : null;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /**
     * Record a file event. The ORIGINAL filename the user uploaded only exists in
     * PHP -- the database never sees it -- so this must be called from the upload,
     * download and delete paths. (§5C.2)
     */
    public static function file(string $action, array $f): ?int
    {
        try {
            $sev = self::log(match ($action) {
                'uploaded', 'replaced' => 'uploaded',
                'downloaded'           => 'downloaded',
                'deleted'              => 'deleted_file',
                default                => 'uploaded',
            }, [
                'object'   => $f['object']   ?? null,
                'objid'    => $f['objid']    ?? null,
                'objref'   => $f['objref']   ?? null,
                'remarks'  => $f['remarks']  ?? null,
                'severity' => $f['severity'] ?? 0,
            ]);

            if ($sev === null) {
                return null;
            }

            DB::insert(
                'INSERT INTO syslog.file_events (
                    fe_id, fe_dtg, fe_sev_id, fe_action,
                    fe_orig_name, fe_stored_path, fe_disk,
                    fe_bytes, fe_mime, fe_ext, fe_sha256,
                    fe_object, fe_objid, fe_objref,
                    fe_attach_tbl, fe_attach_id, fe_attach_type,
                    fe_replaces_fe_id, fe_prev_sha256, fe_result, fe_note)
                 SELECT nextval(\'syslog.file_events_seq\'), e.sev_dtg, e.sev_id, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                   FROM syslog.events e WHERE e.sev_id = ?',
                [
                    $action,
                    $f['orig_name']   ?? null,
                    $f['stored_path'] ?? null,
                    $f['disk']        ?? null,
                    $f['bytes']       ?? null,
                    $f['mime']        ?? null,
                    $f['ext']         ?? null,
                    $f['sha256']      ?? null,
                    $f['object']      ?? null,
                    isset($f['objid']) ? (string) $f['objid'] : null,
                    $f['objref']      ?? null,
                    $f['attach_tbl']  ?? null,
                    isset($f['attach_id']) ? (string) $f['attach_id'] : null,
                    $f['attach_type'] ?? null,
                    $f['replaces']    ?? null,
                    $f['prev_sha256'] ?? null,
                    $f['result']      ?? 'ok',
                    $f['note']        ?? null,
                    $sev,
                ]
            );

            return $sev;
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    /** Convenience wrappers for the auth events the app has never recorded (G-3). */
    public static function login(?string $username = null): ?int
    {
        return self::log('login', ['object' => 'account', 'objref' => $username]);
    }

    public static function loginFailed(string $username): ?int
    {
        return self::log('login_failed', [
            'object' => 'account', 'objref' => $username,
            'result' => 'failed', 'severity' => 1,
        ]);
    }

    public static function logout(?string $username = null): ?int
    {
        return self::log('logout', ['object' => 'account', 'objref' => $username]);
    }

    public static function denied(string $what, ?string $why = null): ?int
    {
        return self::log('denied', [
            'object' => 'account', 'objref' => $what, 'remarks' => $why,
            'result' => 'denied', 'severity' => 1,
        ]);
    }
}
