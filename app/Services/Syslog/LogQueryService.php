<?php

namespace App\Services\Syslog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Read-only queries behind the System Logs screen.
 *
 * Every method here only ever SELECTs. The trail is append-only at the database
 * level too (syslog deny-triggers + privileges), so there is nothing to write.
 *
 * Design: docs/SYSTEM_LOGS_SCHEMA_DESIGN.md §5E.4, §5E.9
 */
class LogQueryService
{
    public const PER_PAGE = 60;

    /** Level 1 -- category tiles with activity counts. */
    public function categories(): array
    {
        return DB::select(
            'SELECT cat_key, cat_label, cat_icon, cat_order, cat_objects,
                    total_30d, today, week, critical_30d, failed_30d,
                    first_activity, last_activity
               FROM syslog.v_category_activity
              ORDER BY cat_order'
        );
    }

    public function category(string $key): ?object
    {
        return DB::selectOne(
            'SELECT cat_key, cat_label, cat_icon, cat_objects
               FROM syslog.categories WHERE cat_key = ? AND cat_enabled',
            [$key]
        );
    }

    /** Overall counters for the landing page header. */
    public function overview(): object
    {
        $row = DB::selectOne(
            "SELECT (SELECT count(*) FROM syslog.events)                                       AS events_total,
                    (SELECT count(*) FROM syslog.events WHERE sev_dtg >= current_date)          AS events_today,
                    (SELECT count(*) FROM syslog.events WHERE sev_dtg >= current_date - 7)      AS events_week,
                    (SELECT count(*) FROM syslog.events WHERE sev_severity = 2)                 AS critical_30d,
                    (SELECT count(*) FROM syslog.events WHERE sev_result <> 'ok')               AS failed_30d,
                    (SELECT min(sev_dtg) FROM syslog.events)                                    AS first_event,
                    (SELECT count(*) FROM syslog.v_unowned_writes
                        WHERE sev_dtg >= current_date - 7)                                      AS unowned_week,
                    (SELECT count(*) FROM syslog.watched_tables WHERE wt_enabled)               AS tables_watched,
                    (SELECT count(*) FROM syslog.v_coverage
                        WHERE NOT trigger_always_on)                                            AS coverage_gaps,
                    (SELECT count(*) FROM syslog.trigger_failures WHERE NOT tf_seen)            AS trail_holes,
                    (SELECT count(*) FROM syslog.case_snapshots)                                AS snapshots,
                    (SELECT count(*) FROM syslog.file_events)                                   AS file_events,
                    (SELECT count(*) FROM aud.busdata)                                          AS legacy_rows"
        );

        $verify = DB::selectOne(
            'SELECT vrn_dtg, vrn_status, vrn_seals_ok, vrn_first_bad, vrn_detail
               FROM syslog.verify_runs ORDER BY vrn_id DESC LIMIT 1'
        );

        $row->verify = $verify;

        return $row;
    }

    /**
     * Level 2 -- the filtered event list. Keyset paginated on (sev_dtg, sev_id)
     * so deep pages stay fast and partition pruning still applies.
     */
    public function events(Request $request, ?array $objects = null): array
    {
        $w = ['1=1'];
        $b = [];

        if ($objects !== null && $objects !== []) {
            $w[] = 'sev_object = ANY(?::text[])';
            $b[] = '{' . implode(',', array_map(fn ($o) => '"' . $o . '"', $objects)) . '}';
        }

        [$from, $to] = $this->dateRange($request);
        $w[] = 'sev_dtg >= ?'; $b[] = $from;
        $w[] = 'sev_dtg <  ?'; $b[] = $to;

        if ($v = trim((string) $request->query('user'))) {
            $w[] = '(sev_username ILIKE ? OR sev_name ILIKE ? OR sev_desig ILIKE ?)';
            $b[] = "%$v%"; $b[] = "%$v%"; $b[] = "%$v%";
        }
        if ($v = trim((string) $request->query('unit'))) {
            $w[] = 'sev_untname ILIKE ?'; $b[] = "%$v%";
        }
        if ($v = trim((string) $request->query('action'))) {
            $w[] = 'sev_action = ?'; $b[] = $v;
        }
        if ($v = trim((string) $request->query('object'))) {
            $w[] = 'sev_object = ?'; $b[] = $v;
        }
        if ($v = trim((string) $request->query('ip'))) {
            $w[] = 'host(sev_ip) ILIKE ?'; $b[] = "%$v%";
        }
        if ($v = trim((string) $request->query('source'))) {
            $w[] = 'sev_source = ?'; $b[] = $v;
        }
        if (($v = $request->query('severity')) !== null && $v !== '') {
            $w[] = 'sev_severity >= ?'; $b[] = (int) $v;
        }
        if ($request->boolean('failed_only')) {
            $w[] = "sev_result <> 'ok'";
        }
        if ($request->boolean('unowned_only')) {
            $w[] = '(sev_acc_id IS NULL OR sev_implicit)';
        }
        if ($v = trim((string) $request->query('q'))) {
            $w[] = '(sev_objref ILIKE ? OR sev_objid ILIKE ? OR sev_remarks ILIKE ?
                     OR sev_authority_ref ILIKE ? OR sev_route ILIKE ?)';
            foreach (range(1, 5) as $_) { $b[] = "%$v%"; }
        }

        // the filter set without the paging cursor, so the total is the whole result
        $filterWhere = implode(' AND ', $w);
        $filterBind = $b;

        // keyset cursor: "<dtg>|<id>"
        if ($cur = (string) $request->query('after')) {
            $parts = explode('|', $cur);
            if (count($parts) === 2) {
                $w[] = '(sev_dtg, sev_id) < (?::timestamptz, ?::bigint)';
                $b[] = $parts[0];
                $b[] = $parts[1];
            }
        }

        $where = implode(' AND ', $w);
        $limit = self::PER_PAGE + 1;

        $rows = DB::select(
            "SELECT * FROM syslog.v_timeline
              WHERE $where
              ORDER BY sev_dtg DESC, sev_id DESC
              LIMIT $limit",
            $b
        );

        $hasMore = count($rows) > self::PER_PAGE;
        if ($hasMore) {
            array_pop($rows);
        }

        $last = $rows ? end($rows) : null;

        return [
            'rows'    => $rows,
            'hasMore' => $hasMore,
            'next'    => $last ? $last->sev_dtg . '|' . $last->sev_id : null,
            'from'    => $from,
            'to'      => $to,
            'total'   => $this->countEvents($filterWhere, $filterBind),
        ];
    }

    private function countEvents(string $where, array $bindings): int
    {
        $row = DB::selectOne("SELECT count(*) AS c FROM syslog.events WHERE $where", $bindings);
        return (int) ($row->c ?? 0);
    }

    /**
     * Default to ALL TIME, not a rolling window.
     *
     * The backfilled history runs from 2015 to today, so a 30-day default would
     * hide almost everything the trail knows. Partition pruning still applies the
     * moment the user picks a narrower range.
     */
    private function dateRange(Request $request): array
    {
        $from = trim((string) $request->query('from'));
        $to   = trim((string) $request->query('to'));

        $from = $from === ''
            ? '1900-01-01 00:00:00'
            : $from . ' 00:00:00';

        $to = $to === ''
            ? now()->addDay()->startOfDay()->toDateTimeString()
            : date('Y-m-d 00:00:00', strtotime($to . ' +1 day'));

        return [$from, $to];
    }

    // ------------------------------------------------------------------ Level 3

    public function event(int $sevId): ?object
    {
        return DB::selectOne('SELECT * FROM syslog.v_timeline WHERE sev_id = ?', [$sevId]);
    }

    /** The record header: first/last activity, counts, current pendency. */
    public function recordHeader(string $object, string $objid): ?object
    {
        return DB::selectOne(
            'SELECT ? ::varchar AS object, ?::varchar AS objid,
                    max(sev_objref)                     AS objref,
                    min(sev_dtg)                        AS opened_at,
                    max(sev_dtg)                        AS last_at,
                    count(*)                            AS events,
                    count(*) FILTER (WHERE sev_action IN
                      (\'forwarded\',\'returned\',\'approved\',\'rejected\',\'released\')) AS movements,
                    max(sev_stage_to)                   AS last_stage,
                    max(sev_severity)                   AS max_severity
               FROM syslog.events WHERE sev_object = ? AND sev_objid = ?',
            [$object, $objid, $object, $objid]
        );
    }

    public function trail(string $object, string $objid): array
    {
        return DB::select('SELECT * FROM syslog.case_trail(?::varchar, ?::varchar)', [$object, $objid]);
    }

    /** Field changes for a whole object (all its tables), newest first. */
    public function changes(string $object, string $objid, ?string $column = null): array
    {
        $b = [$object, $objid];
        $extra = '';
        if ($column) {
            $extra = ' AND f.fc_column = ?';
            $b[] = $column;
        }

        return DB::select(
            "SELECT f.*, e.sev_dtg, e.sev_username, e.sev_name, e.sev_rank, e.sev_desig,
                    e.sev_ip, e.sev_action, e.sev_remarks, e.sev_authority_ref, e.sev_source
               FROM syslog.field_changes f
               JOIN syslog.events e ON e.sev_id = f.fc_sev_id
              WHERE e.sev_object = ? AND e.sev_objid = ? $extra
              ORDER BY f.fc_dtg DESC, f.fc_id DESC
              LIMIT 500",
            $b
        );
    }

    /** Row-level changes, including deletes with their full old row. */
    public function rowChanges(string $object, string $objid): array
    {
        return DB::select(
            "SELECT r.rc_id, r.rc_dtg, r.rc_schema, r.rc_table, r.rc_op, r.rc_pk_text,
                    r.rc_changed, (r.rc_old_row IS NOT NULL) AS has_old,
                    (r.rc_new_row IS NOT NULL) AS has_new,
                    e.sev_username, e.sev_desig, e.sev_ip, e.sev_action
               FROM syslog.row_changes r
               JOIN syslog.events e ON e.sev_id = r.rc_sev_id
              WHERE e.sev_object = ? AND e.sev_objid = ?
              ORDER BY r.rc_dtg DESC, r.rc_id DESC
              LIMIT 500",
            [$object, $objid]
        );
    }

    public function custody(string $object, string $objid): array
    {
        return DB::select(
            'SELECT * FROM syslog.v_case_custody
              WHERE cs_object = ? AND cs_objid = ? ORDER BY cs_seq',
            [$object, $objid]
        );
    }

    public function snapshots(string $object, string $objid): array
    {
        return DB::select(
            'SELECT cs_id, cs_seq, cs_dtg, cs_stage, cs_holder, cs_prev_holder, cs_reason,
                    cs_remarks, cs_doc_hash, cs_same_as, cs_row_counts, cs_bytes,
                    cs_partial, cs_partial_why
               FROM syslog.case_snapshots
              WHERE cs_object = ? AND cs_objid = ? ORDER BY cs_seq',
            [$object, $objid]
        );
    }

    public function snapshotDoc(int $csId): ?array
    {
        $row = DB::selectOne('SELECT syslog.snapshot_doc(?::bigint) AS doc', [$csId]);
        return $row && $row->doc ? (json_decode($row->doc, true) ?: null) : null;
    }

    public function snapshotDiff(int $from, int $to): array
    {
        return DB::select('SELECT * FROM syslog.snapshot_diff(?::bigint, ?::bigint)', [$from, $to]);
    }

    public function files(string $object, string $objid): array
    {
        return DB::select(
            'SELECT f.*, e.sev_dtg AS at, e.sev_username, e.sev_name, e.sev_desig, e.sev_ip
               FROM syslog.file_events f
               JOIN syslog.events e ON e.sev_id = f.fe_sev_id
              WHERE f.fe_object = ? AND f.fe_objid = ?
              ORDER BY f.fe_dtg DESC LIMIT 300',
            [$object, $objid]
        );
    }

    /** Tab 6 -- everyone who touched this record. */
    public function people(string $object, string $objid): array
    {
        return DB::select(
            "SELECT sev_acc_id, sev_username, sev_name, sev_rank, sev_desig, sev_untname,
                    count(*) AS events, min(sev_dtg) AS first_at, max(sev_dtg) AS last_at,
                    count(*) FILTER (WHERE sev_result = 'denied') AS denied,
                    array_agg(DISTINCT host(sev_ip)) FILTER (WHERE sev_ip IS NOT NULL) AS ips
               FROM syslog.events
              WHERE sev_object = ? AND sev_objid = ?
              GROUP BY 1,2,3,4,5,6
              ORDER BY max(sev_dtg) DESC",
            [$object, $objid]
        );
    }

    /** Legacy aud.busdata rows, for the Legacy tab. */
    public function legacy(Request $request): array
    {
        $w = ['1=1'];
        $b = [];
        if ($v = trim((string) $request->query('q'))) {
            $w[] = '(sev_username ILIKE ? OR source_form ILIKE ? OR fc_column ILIKE ? OR rc_pk_text ILIKE ?)';
            foreach (range(1, 4) as $_) { $b[] = "%$v%"; }
        }
        $where = implode(' AND ', $w);

        return DB::select(
            "SELECT * FROM syslog.v_legacy_busdata WHERE $where
              ORDER BY sev_dtg DESC LIMIT 300",
            $b
        );
    }

    /** Distinct object values actually present, for the filter dropdown. */
    public function knownObjects(?array $objects = null): array
    {
        if ($objects) {
            return $objects;
        }
        return array_map(
            fn ($r) => $r->wt_object,
            DB::select('SELECT DISTINCT wt_object FROM syslog.watched_tables
                         WHERE wt_object IS NOT NULL ORDER BY 1')
        );
    }

    /** Coverage / protection health, for the System tab. */
    public function health(): array
    {
        return [
            'gaps' => DB::select(
                'SELECT table_schema, table_name, registered, enabled, trigger_always_on
                   FROM syslog.v_coverage
                  WHERE NOT registered OR NOT enabled OR NOT trigger_always_on
                  ORDER BY table_schema, table_name'
            ),
            'drift' => DB::select('SELECT * FROM syslog.v_composition_drift'),
            'holes' => DB::select(
                'SELECT * FROM syslog.trigger_failures ORDER BY tf_dtg DESC LIMIT 50'
            ),
            'seals' => DB::select(
                'SELECT s.sel_id, s.sel_dtg, s.sel_from_dtg, s.sel_to_dtg, s.sel_event_count,
                        s.sel_rc_count, s.sel_fc_count,
                        (x.sxp_sel_id IS NOT NULL) AS sel_exported, x.sxp_path
                   FROM syslog.seals s
                   LEFT JOIN syslog.seal_exports x ON x.sxp_sel_id = s.sel_id
                  ORDER BY s.sel_id DESC LIMIT 20'
            ),
            'verifies' => DB::select(
                'SELECT * FROM syslog.verify_runs ORDER BY vrn_id DESC LIMIT 10'
            ),
            'ddl' => DB::select(
                'SELECT * FROM syslog.ddl_events ORDER BY dde_id DESC LIMIT 50'
            ),
        ];
    }
}
