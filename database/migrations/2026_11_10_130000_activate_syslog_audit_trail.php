<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Activates the audit trail: partitions, triggers on every business table, and
 * the historical backfill.
 *
 * The previous migration (2026_11_10_120000) only INSTALLS the syslog schema and
 * its functions — it deliberately attaches nothing. This one switches it on, so
 * that a fresh machine needs nothing but:
 *
 *     php artisan migrate
 *
 * Order matters and is deliberate:
 *   1. historical + monthly partitions   -- a missing partition fails every audited
 *                                           write, and the business write with it
 *   2. attach the triggers, wave by wave -- Laravel-only schemas first, the three
 *                                           tables the legacy Access app writes to last
 *   3. backfill the past                 -- BEFORE any seal exists, because backfilled
 *                                           rows carry historical dates
 *
 * Design: docs/SYSTEM_LOGS_SCHEMA_DESIGN.md §8A.8 · docs/SYSLOG_IMPLEMENTATION.md
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('The syslog audit trail requires PostgreSQL.');
        }

        // ---------------------------------------------------------- 1. partitions
        $hist = DB::select('SELECT created FROM syslog.ensure_history_partitions(2015)');
        $month = DB::select('SELECT created FROM syslog.ensure_partitions(6, 12)');
        $this->say(sprintf('partitions: %d yearly + %d monthly created',
            count($hist), count($month)));

        // Refuse to continue without a partition for the current month — otherwise
        // the first audited write after this migration would fail.
        $future = DB::selectOne(
            "SELECT count(*) AS n FROM pg_class c
               JOIN pg_inherits i ON i.inhrelid = c.oid
              WHERE i.inhparent = 'syslog.events'::regclass
                AND pg_get_expr(c.relpartbound, c.oid) >
                    format('FOR VALUES FROM (''%s'')', date_trunc('month', now())::date)"
        );
        if ((int) ($future->n ?? 0) < 1) {
            throw new RuntimeException(
                'No future partition exists for syslog.events. Refusing to attach triggers: '
                . 'every write would fail. Run syslog.ensure_partitions() first.'
            );
        }

        // ------------------------------------------------- 2. attach, wave by wave
        $total = 0;
        $failed = [];
        foreach ([1, 2, 3, 4] as $wave) {
            foreach (DB::select('SELECT attached FROM syslog.attach_wave(?::integer)', [$wave]) as $r) {
                if (str_contains($r->attached, 'FAILED')) {
                    $failed[] = $r->attached;
                } else {
                    $total++;
                }
            }
        }
        $this->say("audit triggers attached to {$total} table(s)");

        foreach ($failed as $f) {
            // Not fatal: an unaudited table is a gap `audit:coverage` will report,
            // which is better than leaving the database half-migrated.
            $this->say('  WARNING, not attached: ' . $f);
        }

        // ----------------------------------------------------------- 3. backfill
        $events = 0;
        foreach (DB::select('SELECT * FROM syslog.backfill_all()') as $r) {
            $events += (int) $r->events;
            $this->say(sprintf('  backfill %-10s %s', $r->source, number_format((int) $r->events)));
        }
        $this->say(number_format($events) . ' historical event(s) reconstructed');

        // --------------------------------------------------------- 4. sanity check
        $gaps = DB::selectOne(
            'SELECT count(*) AS n FROM syslog.v_coverage WHERE NOT trigger_always_on'
        );
        $this->say(((int) $gaps->n === 0)
            ? 'coverage complete: every business table has an always-on audit trigger'
            : 'coverage gaps: ' . (int) $gaps->n . ' table(s) — run `php artisan audit:coverage`');
    }

    public function down(): void
    {
        // Detach the triggers and forget the backfill marks, but KEEP the trail.
        // The schema itself is dropped by the 120000 migration's down().
        DB::unprepared(<<<'SQL'
            DO $blk$
            BEGIN
              IF EXISTS (SELECT 1 FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace
                          WHERE n.nspname = 'syslog' AND p.proname = 'detach_all') THEN
                PERFORM syslog.detach_all();
              END IF;
              IF to_regclass('syslog.backfill_marks') IS NOT NULL THEN
                DELETE FROM syslog.backfill_marks;
              END IF;
            END $blk$;
        SQL);
    }

    private function say(string $msg): void
    {
        if (app()->runningInConsole() && class_exists(\Illuminate\Support\Facades\Log::class)) {
            echo '  ' . $msg . PHP_EOL;
        }
    }
};
