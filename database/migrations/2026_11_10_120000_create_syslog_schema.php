<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Installs the syslog schema -- the system-wide immutable audit trail.
 *
 * Design: docs/SYSTEM_LOGS_SCHEMA_DESIGN.md
 * SQL:    database/sql/syslog/*.sql  (the SQL is the source of truth; this
 *         migration only runs it, so the same files can be applied by hand
 *         to a staging database such as rdw03 with psql -f).
 *
 * This migration installs the schema, functions, views, seed config and grants.
 * It deliberately does NOT attach the audit triggers to business tables -- that
 * is done wave by wave with `php artisan audit:attach --wave=N` (see §8A.8),
 * so a problem is contained and the legacy app is touched last.
 */
return new class extends Migration
{
    /** Files applied in order. 08 (attach) is deliberately excluded. */
    private array $files = [
        '01_schema_tables.sql',
        '02_partitions_immutability.sql',
        '03_functions.sql',
        '04_snapshots_seals.sql',
        '05_views.sql',
        '06_seed_config.sql',
        '08_attach_triggers.sql',   // defines attach_wave()/attach_all(), does not call them
        '09_reconstruct.sql',       // snapshot-diff recovery helpers
        '10_backfill.sql',          // historical partitions + backfill_event()
        '11_backfill_sources.sql',  // the backfill sources; run via `php artisan audit:backfill`

        // Grants go LAST: they are issued by function name over whatever exists in
        // the schema, so every function from 03..11 has to be created first.
        '07_roles_grants.sql',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('The syslog audit trail requires PostgreSQL.');
        }

        foreach ($this->files as $file) {
            $path = database_path('sql/syslog/' . $file);
            if (! is_file($path)) {
                throw new RuntimeException("Missing syslog SQL file: {$path}");
            }
            DB::unprepared(file_get_contents($path));
        }
    }

    public function down(): void
    {
        // Detach every audit trigger first, then drop the schema.
        // NOTE: this destroys the audit trail. Export it before rolling back.
        DB::unprepared(<<<'SQL'
            DO $blk$
            BEGIN
              IF EXISTS (SELECT 1 FROM pg_proc p JOIN pg_namespace n ON n.oid = p.pronamespace
                          WHERE n.nspname = 'syslog' AND p.proname = 'detach_all') THEN
                PERFORM syslog.detach_all();
              END IF;
            END $blk$;

            DROP EVENT TRIGGER IF EXISTS ev_syslog_ddl;
            DROP EVENT TRIGGER IF EXISTS ev_syslog_drop;
            DROP SCHEMA IF EXISTS syslog CASCADE;
        SQL);
    }
};
