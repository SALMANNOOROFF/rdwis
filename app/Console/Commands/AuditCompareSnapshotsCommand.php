<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Recovers past history by diffing two full-database snapshots.
 *
 * PostgreSQL kept no audit log of the past on this server: track_commit_timestamp
 * is off, archive_mode is off and log_statement is none. What does exist is a
 * series of dated database copies and pg_dump files. Comparing two of them shows
 * exactly which rows appeared, disappeared and changed between those two dates.
 *
 * It cannot recover WHO made a change or WHEN within the window -- that
 * information was never written down. It can recover WHAT changed, in full.
 *
 *   php artisan audit:snapshots                            list what is available
 *   php artisan audit:compare --from=rdw02 --to=rdw04       summary per table
 *   php artisan audit:compare --from=rdw02 --to=rdw04 --table=fin.transactions --detail
 *   php artisan audit:compare --from=rdw02 --to=rdw04 --import
 *
 * Design: docs/SYSTEM_LOGS_SCHEMA_DESIGN.md, docs/SYSLOG_HISTORY_RECOVERY.md
 */
class AuditCompareSnapshotsCommand extends Command
{
    protected $signature = 'audit:compare
        {--from= : the older database (snapshot) name}
        {--to= : the newer database name; defaults to the current connection}
        {--table= : limit to one schema.table}
        {--detail : show the actual rows and field diffs}
        {--limit=15 : how many example rows to print per table}
        {--import : write the recovered changes into the trail as source=backfill}
        {--list : just list the snapshots available on this server}';

    protected $description = 'Recover past history by comparing two database snapshots.';

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listSnapshots();
        }

        $from = (string) $this->option('from');
        $to = (string) ($this->option('to') ?: Config::get('database.connections.pgsql.database'));

        if ($from === '') {
            $this->error('Use --from=<older database>. Run with --list to see what is available.');
            return self::FAILURE;
        }
        if ($from === $to) {
            $this->error('--from and --to are the same database.');
            return self::FAILURE;
        }

        $this->line("Comparing <fg=yellow>{$from}</> (older) with <fg=green>{$to}</> (newer)");

        $pdoFrom = $this->connect($from);
        $pdoTo = $this->connect($to);
        if (! $pdoFrom || ! $pdoTo) {
            return self::FAILURE;
        }

        $eraFrom = $this->era($pdoFrom);
        $eraTo = $this->era($pdoTo);
        $this->line("  {$from}: newest data {$eraFrom}");
        $this->line("  {$to}: newest data {$eraTo}");
        $this->newLine();

        $tables = $this->tablesToCompare($pdoFrom, $pdoTo);
        if (! $tables) {
            $this->error('No comparable tables found (both snapshots need the same table with a primary key).');
            return self::FAILURE;
        }

        $rows = [];
        $tot = ['added' => 0, 'deleted' => 0, 'changed' => 0, 'imported' => 0];
        $detail = [];

        foreach ($tables as $t) {
            $r = $this->compareTable($pdoFrom, $pdoTo, $t, $eraFrom, $eraTo, $from, $to);
            if ($r === null) {
                continue;
            }

            $tot['added'] += $r['added'];
            $tot['deleted'] += $r['deleted'];
            $tot['changed'] += $r['changed'];
            $tot['imported'] += $r['imported'];

            $schemaNote = '';
            if ($r['table']['cols_added'] ?? []) {
                $schemaNote .= '+' . implode(',+', $r['table']['cols_added']);
            }
            if ($r['table']['cols_removed'] ?? []) {
                $schemaNote .= ($schemaNote ? ' ' : '') . '-' . implode(',-', $r['table']['cols_removed']);
            }

            if ($r['added'] || $r['deleted'] || $r['changed'] || $schemaNote !== '') {
                $rows[] = [
                    $t['schema'] . '.' . $t['table'],
                    number_format($r['from_rows']),
                    number_format($r['to_rows']),
                    $r['added'] ?: '',
                    $r['deleted'] ?: '',
                    $r['changed'] ?: '',
                    \Illuminate\Support\Str::limit($schemaNote, 34),
                ];
                if ($r['added'] || $r['deleted'] || $r['changed']) {
                    $detail[$t['schema'] . '.' . $t['table']] = $r;
                }
            }
        }

        $this->table(
            ['Table', "rows in {$from}", "rows in {$to}", 'Appeared', 'Disappeared', 'Changed', 'Schema change'],
            $rows
        );

        $this->newLine();
        $this->info(sprintf(
            'Recovered: %s rows appeared, %s DISAPPEARED, %s changed, across %d table(s).',
            number_format($tot['added']), number_format($tot['deleted']),
            number_format($tot['changed']), count($rows)
        ));

        if ($this->option('detail')) {
            $this->printDetail($detail, (int) $this->option('limit'));
        }

        if ($this->option('import')) {
            $this->info($tot['imported'] . ' change(s) written into the trail as source=backfill.');
            $this->warn('These rows carry no actor and no exact time -- that was never recorded. '
                . 'The System Logs screen labels them "imported history".');
        } else {
            $this->line('');
            $this->line('Nothing was written. Re-run with --import to put these into the trail,');
            $this->line('or --detail to see the actual rows and field diffs.');
        }

        try {
            DB::insert(
                'INSERT INTO syslog.snapshot_runs
                   (snr_from_db, snr_to_db, snr_from_era, snr_to_era, snr_tables,
                    snr_added, snr_deleted, snr_changed, snr_imported)
                 VALUES (?,?,?::timestamptz,?::timestamptz,?,?,?,?,?)',
                [$from, $to, $eraFrom ?: null, $eraTo ?: null, count($rows),
                 $tot['added'], $tot['deleted'], $tot['changed'], $tot['imported']]
            );
        } catch (\Throwable $e) {
            $this->warn('Could not record the run in syslog.snapshot_runs: ' . $e->getMessage());
        }

        return self::SUCCESS;
    }

    // ---------------------------------------------------------------- snapshots

    private function listSnapshots(): int
    {
        $cfg = Config::get('database.connections.pgsql');
        $pdo = $this->connect($cfg['database']);
        if (! $pdo) {
            return self::FAILURE;
        }

        $dbs = $pdo->query(
            "SELECT datname FROM pg_database
              WHERE datistemplate = false AND datname <> 'postgres'
              ORDER BY datname"
        )->fetchAll(\PDO::FETCH_COLUMN);

        $rows = [];
        foreach ($dbs as $db) {
            $p = $this->connect($db, true);
            if (! $p) {
                $rows[] = [$db, 'not readable', '', '', '', ''];
                continue;
            }
            $era = $this->era($p);
            $c = fn (string $q) => (int) ($p->query($q)->fetchColumn() ?: 0);
            try {
                $rows[] = [
                    $db,
                    $era ?: '?',
                    number_format($c('SELECT count(*) FROM pur.purcases')),
                    number_format($c('SELECT count(*) FROM fin.transactions')),
                    number_format($c('SELECT count(*) FROM hr.emps')),
                    $c("SELECT count(*) FROM information_schema.tables
                         WHERE table_schema NOT IN ('pg_catalog','information_schema')"),
                ];
            } catch (\Throwable) {
                $rows[] = [$db, $era ?: '?', '—', '—', '—', '—'];
            }
        }

        usort($rows, fn ($a, $b) => strcmp((string) $a[1], (string) $b[1]));

        $this->info('Database snapshots on this server, oldest data first:');
        $this->table(['Database', 'Newest data', 'Purchase cases', 'Transactions', 'Employees', 'Tables'], $rows);
        $this->newLine();
        $this->line('Compare any two:  php artisan audit:compare --from=<older> --to=<newer>');
        $this->line('pg_dump files can be restored into a scratch database first:');
        $this->line('  createdb -h 127.0.0.1 -p 5433 -U postgres snap_2026_07_06');
        $this->line('  psql -h 127.0.0.1 -p 5433 -U postgres -d snap_2026_07_06 -f rdw05jul26.sql');

        return self::SUCCESS;
    }

    // ------------------------------------------------------------- connections

    private function connect(string $database, bool $quiet = false): ?\PDO
    {
        $c = Config::get('database.connections.pgsql');
        try {
            return new \PDO(
                "pgsql:host={$c['host']};port={$c['port']};dbname={$database}",
                $c['username'],
                $c['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        } catch (\Throwable $e) {
            if (! $quiet) {
                $this->error("Cannot connect to '{$database}': " . $e->getMessage());
            }
            return null;
        }
    }

    /** Date a snapshot by its newest business data. */
    private function era(\PDO $pdo): string
    {
        try {
            $v = $pdo->query(
                "SELECT to_char(greatest(
                    (SELECT max(bdt_dtg)::timestamptz FROM aud.busdata),
                    (SELECT max(pcs_date)::timestamptz FROM pur.purcases),
                    (SELECT max(trn_date)::timestamptz FROM fin.transactions)
                 ), 'YYYY-MM-DD')"
            )->fetchColumn();
            return (string) ($v ?: '');
        } catch (\Throwable) {
            return '';
        }
    }

    // ---------------------------------------------------------------- compare

    /** Tables present in BOTH snapshots, with a primary key, preferring watched_tables. */
    private function tablesToCompare(\PDO $a, \PDO $b): array
    {
        if ($one = (string) $this->option('table')) {
            [$s, $t] = array_pad(explode('.', $one, 2), 2, null);
            if (! $t) {
                $this->error('Use --table=schema.table');
                return [];
            }
            $pk = $this->primaryKey($b, $s, $t) ?: $this->primaryKey($a, $s, $t);
            if (! $pk) {
                $this->error("{$one} has no primary key, so its rows cannot be matched between snapshots.");
                return [];
            }
            return [['schema' => $s, 'table' => $t, 'pk' => $pk, 'object' => $this->objectOf($s, $t)]];
        }

        $sql = "SELECT table_schema, table_name
                  FROM information_schema.tables
                 WHERE table_type = 'BASE TABLE'
                   AND table_schema IN ('cen','pur','hr','fin','prj','doc','sup','gen',
                                        'frm','ina','puritems','purnew','aud')
                 ORDER BY table_schema, table_name";

        $inA = [];
        foreach ($a->query($sql)->fetchAll(\PDO::FETCH_NUM) as [$s, $t]) {
            $inA["$s.$t"] = true;
        }

        $out = [];
        foreach ($b->query($sql)->fetchAll(\PDO::FETCH_NUM) as [$s, $t]) {
            if (! isset($inA["$s.$t"])) {
                continue;
            }
            $pk = $this->primaryKey($b, $s, $t);
            if (! $pk) {
                continue;
            }
            $out[] = ['schema' => $s, 'table' => $t, 'pk' => $pk, 'object' => $this->objectOf($s, $t)];
        }

        return $out;
    }

    private function primaryKey(\PDO $pdo, string $schema, string $table): ?array
    {
        try {
            $st = $pdo->prepare(
                "SELECT a.attname
                   FROM pg_constraint c
                   JOIN unnest(c.conkey) WITH ORDINALITY AS k(attnum, ord) ON true
                   JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = k.attnum
                  WHERE c.conrelid = format('%I.%I', ?::text, ?::text)::regclass
                    AND c.contype = 'p'
                  ORDER BY k.ord"
            );
            $st->execute([$schema, $table]);
            $cols = $st->fetchAll(\PDO::FETCH_COLUMN);
            return $cols ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private array $objectCache = [];

    private function objectOf(string $schema, string $table): ?string
    {
        if (! $this->objectCache) {
            try {
                foreach (DB::select('SELECT wt_schema, wt_table, wt_object FROM syslog.watched_tables') as $r) {
                    $this->objectCache[$r->wt_schema . '.' . $r->wt_table] = $r->wt_object;
                }
            } catch (\Throwable) {
                $this->objectCache = ['__none__' => null];
            }
        }
        return $this->objectCache[$schema . '.' . $table] ?? null;
    }

    /** Column names of a table in one snapshot. */
    private function columns(\PDO $pdo, string $schema, string $table): array
    {
        try {
            $st = $pdo->prepare(
                'SELECT column_name FROM information_schema.columns
                  WHERE table_schema = ? AND table_name = ? ORDER BY column_name'
            );
            $st->execute([$schema, $table]);
            return $st->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Pull pk => md5(row) from one snapshot, hashing ONLY the columns that exist
     * in both snapshots.
     *
     * This matters: a migration that added a column between the two snapshots would
     * otherwise change to_jsonb(t) for every single row, and the whole table would
     * look "changed" when no data moved at all. Columns added or dropped are reported
     * separately as a schema change.
     */
    private function fingerprints(\PDO $pdo, array $t, array $cols): ?array
    {
        $pkExpr = implode(" || ',' || ", array_map(
            fn ($c) => sprintf("%s || '=' || coalesce(t.%s::text,'')", "'" . $c . "'", '"' . $c . '"'),
            $t['pk']
        ));

        $pairs = implode(', ', array_map(
            fn ($c) => sprintf("'%s', t.%s::text", $c, '"' . $c . '"'),
            $cols
        ));

        $sql = sprintf(
            'SELECT %s AS pk, md5(jsonb_build_object(%s)::text) AS h FROM %s.%s t',
            $pkExpr, $pairs, '"' . $t['schema'] . '"', '"' . $t['table'] . '"'
        );

        try {
            $out = [];
            foreach ($pdo->query($sql) as $row) {
                $out[$row['pk']] = $row['h'];
            }
            return $out;
        } catch (\Throwable $e) {
            $this->warn("  skipped {$t['schema']}.{$t['table']}: " . $e->getMessage());
            return null;
        }
    }

    private function compareTable(\PDO $a, \PDO $b, array $t, string $eraA, string $eraB,
                                  string $dbA, string $dbB): ?array
    {
        $colsA = $this->columns($a, $t['schema'], $t['table']);
        $colsB = $this->columns($b, $t['schema'], $t['table']);
        $common = array_values(array_intersect($colsA, $colsB));

        if (! $common) {
            return null;
        }

        $t['cols_added']   = array_values(array_diff($colsB, $colsA));
        $t['cols_removed'] = array_values(array_diff($colsA, $colsB));

        $fa = $this->fingerprints($a, $t, $common);
        $fb = $this->fingerprints($b, $t, $common);
        if ($fa === null || $fb === null) {
            return null;
        }

        $added = array_diff_key($fb, $fa);
        $deleted = array_diff_key($fa, $fb);
        $changed = [];
        foreach ($fa as $pk => $h) {
            if (isset($fb[$pk]) && $fb[$pk] !== $h) {
                $changed[$pk] = true;
            }
        }

        $r = [
            'from_rows' => count($fa),
            'to_rows'   => count($fb),
            'added'     => count($added),
            'deleted'   => count($deleted),
            'changed'   => count($changed),
            'imported'  => 0,
            'table'     => $t,
            'deleted_pks' => array_keys($deleted),
            'added_pks'   => array_keys($added),
            'changed_pks' => array_keys($changed),
        ];

        if ($this->option('import') && ($r['added'] || $r['deleted'] || $r['changed'])) {
            $r['imported'] = $this->import($a, $b, $t, $r, $dbA, $dbB, $eraA, $eraB);
        }

        return $r;
    }

    /** Fetch full rows for a set of pk strings. */
    private function fetchRows(\PDO $pdo, array $t, array $pks): array
    {
        if (! $pks) {
            return [];
        }

        $pkExpr = implode(" || ',' || ", array_map(
            fn ($c) => sprintf("'%s' || '=' || coalesce(t.%s::text,'')", $c, '"' . $c . '"'),
            $t['pk']
        ));

        $out = [];
        foreach (array_chunk($pks, 500) as $chunk) {
            $in = implode(',', array_fill(0, count($chunk), '?'));
            $sql = sprintf(
                'SELECT %s AS pk, to_jsonb(t)::text AS j FROM %s.%s t WHERE %s IN (%s)',
                $pkExpr, '"' . $t['schema'] . '"', '"' . $t['table'] . '"', $pkExpr, $in
            );
            try {
                $st = $pdo->prepare($sql);
                $st->execute($chunk);
                foreach ($st as $row) {
                    $out[$row['pk']] = $row['j'];
                }
            } catch (\Throwable $e) {
                $this->warn('  row fetch failed: ' . $e->getMessage());
            }
        }

        return $out;
    }

    private function import(\PDO $a, \PDO $b, array $t, array $r,
                            string $dbA, string $dbB, string $eraA, string $eraB): int
    {
        $labelA = $dbA . ($eraA ? " ({$eraA})" : '');
        $labelB = $dbB . ($eraB ? " ({$eraB})" : '');

        $oldRows = $this->fetchRows($a, $t, array_merge($r['deleted_pks'], $r['changed_pks']));
        $newRows = $this->fetchRows($b, $t, array_merge($r['added_pks'], $r['changed_pks']));

        $n = 0;
        $write = function (string $op, string $pk) use ($t, $oldRows, $newRows, $labelA, $labelB, &$n) {
            try {
                DB::selectOne(
                    'SELECT syslog.reconstruct_event(
                        ?::varchar, ?::varchar, ?::varchar, ?::char, ?::varchar, ?::varchar,
                        ?::varchar, ?::jsonb, ?::jsonb, ?::varchar, ?::varchar) AS id',
                    [
                        $t['object'], $pk, null, $op, $t['schema'], $t['table'], $pk,
                        $oldRows[$pk] ?? null, $newRows[$pk] ?? null, $labelA, $labelB,
                    ]
                );
                $n++;
            } catch (\Throwable $e) {
                $this->warn("  import failed for {$t['schema']}.{$t['table']} {$pk}: " . $e->getMessage());
            }
        };

        foreach ($r['deleted_pks'] as $pk) { $write('D', $pk); }
        foreach ($r['added_pks'] as $pk)   { $write('I', $pk); }
        foreach ($r['changed_pks'] as $pk) { $write('U', $pk); }

        return $n;
    }

    // ----------------------------------------------------------------- detail

    private function printDetail(array $detail, int $limit): void
    {
        foreach ($detail as $name => $r) {
            $t = $r['table'];
            $this->newLine();
            $this->line("<fg=cyan>=== {$name} ===</>");

            if ($r['deleted']) {
                $this->error("  DISAPPEARED ({$r['deleted']}):");
                $pks = array_slice($r['deleted_pks'], 0, $limit);
                $rows = $this->fetchRows($this->connect((string) $this->option('from'), true), $t, $pks);
                foreach ($pks as $pk) {
                    $this->line('    ' . $pk);
                    if (isset($rows[$pk])) {
                        $d = json_decode($rows[$pk], true) ?: [];
                        $this->line('      ' . $this->shortRow($d));
                    }
                }
                if ($r['deleted'] > $limit) {
                    $this->line('    ... and ' . ($r['deleted'] - $limit) . ' more');
                }
            }

            if ($r['changed']) {
                $this->warn("  CHANGED ({$r['changed']}):");
                $pks = array_slice($r['changed_pks'], 0, $limit);
                $old = $this->fetchRows($this->connect((string) $this->option('from'), true), $t, $pks);
                $new = $this->fetchRows($this->connect((string) ($this->option('to')
                        ?: Config::get('database.connections.pgsql.database')), true), $t, $pks);
                foreach ($pks as $pk) {
                    $o = json_decode($old[$pk] ?? '{}', true) ?: [];
                    $w = json_decode($new[$pk] ?? '{}', true) ?: [];
                    $diffs = [];
                    foreach ($w as $k => $v) {
                        if (($o[$k] ?? null) !== $v) {
                            $diffs[] = sprintf('%s: %s -> %s', $k,
                                $this->val($o[$k] ?? null), $this->val($v));
                        }
                    }
                    $this->line('    ' . $pk . '   ' . implode(' | ', array_slice($diffs, 0, 6)));
                }
                if ($r['changed'] > $limit) {
                    $this->line('    ... and ' . ($r['changed'] - $limit) . ' more');
                }
            }

            if ($r['added']) {
                $this->info("  APPEARED ({$r['added']}): " .
                    implode(', ', array_slice($r['added_pks'], 0, $limit)) .
                    ($r['added'] > $limit ? ' ... +' . ($r['added'] - $limit) : ''));
            }
        }
    }

    private function shortRow(array $d): string
    {
        $bits = [];
        foreach ($d as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $bits[] = $k . '=' . $this->val($v);
            if (count($bits) >= 8) {
                break;
            }
        }
        return implode(' ', $bits);
    }

    private function val($v): string
    {
        if ($v === null) {
            return '(null)';
        }
        $s = is_scalar($v) ? (string) $v : json_encode($v);
        return strlen($s) > 40 ? substr($s, 0, 37) . '...' : $s;
    }
}
