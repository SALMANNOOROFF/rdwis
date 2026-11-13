<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Maintenance for the syslog audit trail.
 *
 * Design: docs/SYSTEM_LOGS_SCHEMA_DESIGN.md §8
 *
 *   php artisan audit:partitions --months=3   monthly  (create future partitions)
 *   php artisan audit:seal                    hourly   (seal + chain)
 *   php artisan audit:verify                  daily    (recompute, alert on break)
 *   php artisan audit:coverage                deploy   (fail if a table is unaudited)
 *   php artisan audit:protect-check           monthly  (the audit of the audit)
 *   php artisan audit:attach --wave=1         rollout  (attach triggers, wave by wave)
 */
class AuditMaintenanceCommand extends Command
{
    protected $signature = 'audit:run
        {task : partitions|seal|verify|coverage|protect-check|attach|detach}
        {--months=3 : how many months ahead to create partitions}
        {--wave= : which attach wave (1..4), or "all"}
        {--table= : schema.table for a single attach/detach}';

    protected $description = 'Maintain the syslog audit trail (partitions, seals, verification, coverage).';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->error('The audit trail requires PostgreSQL.');
            return self::FAILURE;
        }

        return match ($this->argument('task')) {
            'partitions'    => $this->partitions(),
            'seal'          => $this->seal(),
            'verify'        => $this->verify(),
            'coverage'      => $this->coverage(),
            'protect-check' => $this->protectCheck(),
            'attach'        => $this->attach(),
            'detach'        => $this->detach(),
            default         => $this->invalid(),
        };
    }

    private function invalid(): int
    {
        $this->error('Unknown task. Use: partitions, seal, verify, coverage, protect-check, attach, detach');
        return self::FAILURE;
    }

    // -------------------------------------------------------------- partitions

    private function partitions(): int
    {
        $months = max(1, (int) $this->option('months'));

        $created = DB::select('SELECT created FROM syslog.ensure_partitions(?::integer, 1)', [$months]);

        if ($created) {
            $this->info('Created ' . count($created) . ' partition(s):');
            foreach ($created as $c) {
                $this->line('  ' . $c->created);
            }
        } else {
            $this->info('All partitions already exist for the next ' . $months . ' month(s).');
        }

        // The single most likely cause of an outage: no partition for the current month
        // means every audited write fails -- and the business write with it.
        $future = DB::selectOne(
            "SELECT count(*) AS n
               FROM pg_class c
               JOIN pg_inherits i ON i.inhrelid = c.oid
              WHERE i.inhparent = 'syslog.events'::regclass
                AND pg_get_expr(c.relpartbound, c.oid) >
                    format('FOR VALUES FROM (''%s'')', date_trunc('month', now())::date)"
        );

        $n = (int) ($future->n ?? 0);
        if ($n < 2) {
            $this->error("ALERT: only {$n} future partition(s) for syslog.events. Create more NOW.");
            return self::FAILURE;
        }

        $this->info("{$n} future partition(s) ready.");
        return self::SUCCESS;
    }

    // -------------------------------------------------------------------- seal

    private function seal(): int
    {
        // Seal up to a minute ago, not to the last hour boundary. The unsealed
        // window is the only period in which a tampered row would not break the
        // chain, so keep it short. The one-minute lag keeps in-flight transactions
        // out of the sealed range.
        $row = DB::selectOne("SELECT syslog.seal_range(now() - interval '1 minute') AS id");

        if ($row->id === null) {
            $this->info('Nothing new to seal.');
            return self::SUCCESS;
        }

        $seal = DB::selectOne('SELECT * FROM syslog.seals WHERE sel_id = ?', [$row->id]);
        $this->info(sprintf(
            'Seal #%d: %d events, %d row changes, %d field changes (%s .. %s)',
            $seal->sel_id, $seal->sel_event_count, $seal->sel_rc_count,
            $seal->sel_fc_count, $seal->sel_from_dtg, $seal->sel_to_dtg
        ));
        $this->line('  hash: ' . $seal->sel_hash);

        $this->export((int) $row->id);

        return self::SUCCESS;
    }

    /** Append-only off-box copy, so a tampered database diverges from a copy it cannot reach. */
    private function export(int $sealId): void
    {
        try {
            $dir = storage_path('app/private/audit-export');
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $seal = DB::selectOne('SELECT * FROM syslog.seals WHERE sel_id = ?', [$sealId]);
            // Same exclusion as the seal itself: backfilled rows are imported data,
            // not events this system witnessed, and they are not part of the chain.
            $events = DB::select(
                "SELECT * FROM syslog.events
                   WHERE sev_dtg >= ? AND sev_dtg < ? AND sev_source <> 'backfill'
                   ORDER BY sev_dtg, sev_id",
                [$seal->sel_from_dtg, $seal->sel_to_dtg]
            );

            $file = $dir . DIRECTORY_SEPARATOR . 'audit-' . now()->format('Y-m') . '.ndjson';
            $fh = fopen($file, 'a');
            if (! $fh) {
                $this->warn('Could not open the export file: ' . $file);
                return;
            }
            fwrite($fh, json_encode(['seal' => $seal], JSON_UNESCAPED_SLASHES) . PHP_EOL);
            foreach ($events as $e) {
                fwrite($fh, json_encode(['event' => $e], JSON_UNESCAPED_SLASHES) . PHP_EOL);
            }
            fclose($fh);

            // syslog.seals is append-only, so export status goes in its own table.
            DB::statement(
                'INSERT INTO syslog.seal_exports (sxp_sel_id, sxp_path, sxp_events)
                 VALUES (?, ?, ?)
                 ON CONFLICT (sxp_sel_id) DO UPDATE
                   SET sxp_path = excluded.sxp_path, sxp_events = excluded.sxp_events,
                       sxp_dtg = clock_timestamp()',
                [$sealId, $file, count($events)]
            );

            $this->info('  exported ' . count($events) . ' event(s) to ' . $file);
        } catch (\Throwable $e) {
            $this->warn('Export failed (the seal itself is written): ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ verify

    private function verify(): int
    {
        $row = DB::selectOne("SELECT * FROM syslog.verify_seals('scheduled')");

        if (($row->status ?? '') === 'ok') {
            $this->info("Chain intact: {$row->seals_checked} seal(s) verified, no event modified.");
        } else {
            $this->error('CHAIN BROKEN at seal/event ' . ($row->first_bad ?? '?'));
            $this->error((string) ($row->detail ?? ''));
        }

        // A non-empty trigger_failures table means the trail has a hole.
        $holes = DB::selectOne('SELECT count(*) AS n FROM syslog.trigger_failures WHERE NOT tf_seen');
        if ((int) $holes->n > 0) {
            $this->error('ALERT: ' . (int) $holes->n . ' unacknowledged trail hole(s) in syslog.trigger_failures.');
            return self::FAILURE;
        }

        // Writes nobody owns -- legacy app, a script, or direct SQL. Should be explainable.
        $unowned = DB::selectOne(
            'SELECT count(*) AS n FROM syslog.v_unowned_writes WHERE sev_dtg >= current_date - 1'
        );
        if ((int) $unowned->n > 0) {
            $this->warn((int) $unowned->n . ' unowned write(s) in the last 24h (legacy app / script / direct SQL).');
        }

        return ($row->status ?? '') === 'ok' ? self::SUCCESS : self::FAILURE;
    }

    // ---------------------------------------------------------------- coverage

    private function coverage(): int
    {
        $gaps = DB::select(
            'SELECT table_schema, table_name, registered, enabled, trigger_always_on
               FROM syslog.v_coverage
              WHERE NOT registered OR NOT enabled OR NOT trigger_always_on
              ORDER BY table_schema, table_name'
        );

        if (! $gaps) {
            $this->info('Coverage complete: every business table has an always-on audit trigger.');
        } else {
            $this->error(count($gaps) . ' table(s) are NOT being audited:');
            $this->table(
                ['Schema', 'Table', 'Registered', 'Enabled', 'Trigger always on'],
                array_map(fn ($g) => [
                    $g->table_schema, $g->table_name,
                    $g->registered ? 'yes' : 'NO',
                    $g->enabled ? 'yes' : 'NO',
                    $g->trigger_always_on ? 'yes' : 'NO',
                ], $gaps)
            );
        }

        $drift = DB::select('SELECT * FROM syslog.v_composition_drift');
        if ($drift) {
            $this->warn(count($drift) . ' case child table(s) missing from syslog.case_composition:');
            foreach ($drift as $d) {
                $this->line('  ' . $d->child_table . ' -> ' . $d->case_table);
            }
        }

        return $gaps ? self::FAILURE : self::SUCCESS;
    }

    // ----------------------------------------------------------- protect-check

    private function protectCheck(): int
    {
        $fail = false;

        $notAlways = DB::selectOne(
            "SELECT count(*) AS n FROM pg_trigger g
               JOIN pg_class c ON c.oid = g.tgrelid
               JOIN pg_namespace n ON n.oid = c.relnamespace
              WHERE n.nspname = 'syslog' AND g.tgname LIKE 'aa_immutable%' AND g.tgenabled <> 'A'"
        );
        if ((int) $notAlways->n > 0) {
            $this->error('FAIL: ' . (int) $notAlways->n . ' deny-trigger(s) are not ENABLE ALWAYS.');
            $fail = true;
        } else {
            $this->info('OK: every syslog deny-trigger is ENABLE ALWAYS.');
        }

        $auditNotAlways = DB::selectOne(
            "SELECT count(*) AS n FROM pg_trigger WHERE tgname LIKE 'zz_audit%' AND tgenabled <> 'A'"
        );
        if ((int) $auditNotAlways->n > 0) {
            $this->error('FAIL: ' . (int) $auditNotAlways->n . ' audit trigger(s) are not ENABLE ALWAYS.');
            $fail = true;
        } else {
            $this->info('OK: every audit trigger is ENABLE ALWAYS.');
        }

        $evt = DB::select("SELECT evtname, evtenabled FROM pg_event_trigger WHERE evtname LIKE 'ev_syslog%'");
        if (count($evt) < 2) {
            $this->error('FAIL: the DDL event triggers are missing (schema changes would go unrecorded).');
            $fail = true;
        } else {
            $this->info('OK: DDL event triggers present (' . count($evt) . ').');
        }

        $writes = DB::select(
            "SELECT grantee, table_name, privilege_type
               FROM information_schema.role_table_grants
              WHERE table_schema = 'syslog'
                AND privilege_type IN ('INSERT','UPDATE','DELETE','TRUNCATE')
                AND grantee NOT IN ('postgres', CURRENT_USER)
                AND table_name NOT IN ('watched_tables','case_composition','categories',
                                       'verify_runs','trigger_failures','snapshot_runs',
                                       'backfill_marks','seal_exports')"
        );
        if ($writes) {
            $this->error('FAIL: unexpected write grants on the trail:');
            foreach ($writes as $w) {
                $this->line("  {$w->grantee} -> {$w->table_name} ({$w->privilege_type})");
            }
            $fail = true;
        } else {
            $this->info('OK: no unexpected write access to the trail.');
        }

        $roles = DB::select('SELECT rolname, rolsuper FROM pg_roles WHERE rolcanlogin ORDER BY rolname');
        $this->line('');
        $this->line('Login roles on this server:');
        foreach ($roles as $r) {
            $this->line('  ' . $r->rolname . ($r->rolsuper ? '  [SUPERUSER]' : ''));
            if ($r->rolsuper && $r->rolname === 'postgres') {
                $this->warn('    the app connects as a superuser if DB_USERNAME=postgres -- see §6.1');
            }
        }

        return $fail ? self::FAILURE : self::SUCCESS;
    }

    // ------------------------------------------------------------------ attach

    private function attach(): int
    {
        if ($t = $this->option('table')) {
            [$schema, $table] = array_pad(explode('.', $t, 2), 2, null);
            if (! $table) {
                $this->error('Use --table=schema.table');
                return self::FAILURE;
            }
            DB::statement('SELECT syslog.attach_audit(?::name, ?::name)', [$schema, $table]);
            $this->info("Attached the audit trigger to {$schema}.{$table}");
            return self::SUCCESS;
        }

        $wave = (string) $this->option('wave');
        if ($wave === '') {
            $this->error('Use --wave=1..4 (see §8A.8 for the order) or --wave=all, or --table=schema.table');
            return self::FAILURE;
        }

        $rows = $wave === 'all'
            ? DB::select('SELECT attached FROM syslog.attach_all()')
            : DB::select('SELECT attached FROM syslog.attach_wave(?::integer)', [(int) $wave]);

        $failed = 0;
        foreach ($rows as $r) {
            if (str_contains($r->attached, 'FAILED')) {
                $this->error('  ' . $r->attached);
                $failed++;
            } else {
                $this->line('  ' . $r->attached);
            }
        }

        $this->info(count($rows) - $failed . ' table(s) attached' . ($failed ? ", {$failed} failed" : '') . '.');
        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function detach(): int
    {
        if ($t = $this->option('table')) {
            [$schema, $table] = array_pad(explode('.', $t, 2), 2, null);
            DB::statement('SELECT syslog.detach_audit(?::name, ?::name)', [$schema, $table]);
            $this->info("Detached {$schema}.{$table}. Logs collected so far are kept.");
            return self::SUCCESS;
        }

        if (! $this->confirm('Detach the audit trigger from EVERY table? Logs already collected are kept.')) {
            return self::SUCCESS;
        }

        $rows = DB::select('SELECT detached FROM syslog.detach_all()');
        $this->info(count($rows) . ' table(s) detached.');
        return self::SUCCESS;
    }
}
