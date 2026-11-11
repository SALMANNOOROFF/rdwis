<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Merge "Ex System Division" (unit 450000) completely into
 * "Command, Control and Communication (C3)" (unit 200000).
 *
 * WHY THIS IS NOT A ONE-LINE UPDATE
 * ---------------------------------
 * Division ownership in RDWIS is expressed in two independent ways:
 *
 *   1. Every scoped table carries its own *_unt_id column. DataHorizonMiddleware
 *      gives each account a numeric window (C3 = 200000..249999,
 *      Ex System = 450000..499999) and HorizonScoped turns it into
 *      "where <unt column> between lower and upper". A record is visible to a
 *      division because of its OWN unt column, not because of the project it
 *      belongs to.
 *
 *   2. prj.projects.prj_id and cen.heads.hed_id live inside the division's id
 *      block (C3 projects are 200001..200023, Ex System 450001..450004), and
 *      hed_id == prj_id for project heads. ProjectController::store() derives a
 *      new id from max(prj_id) + 1 inside the caller's horizon, so an id left
 *      in the wrong block would make two divisions compute the same next id.
 *
 * So the merge has to do three separate things:
 *   a) renumber the 4 projects + their heads into the free C3 slots,
 *   b) re-point every row that references those old ids,
 *   c) re-stamp every *_unt_id in the 450000..499999 window to 200000,
 *      then retire the division head and the unit row itself.
 *
 * ROLLBACK
 * --------
 * up() first copies every row it is about to touch into the schema named by
 * self::BK, together with the old id -> new id map. down() restores from those
 * snapshots, so the merge is reversible as long as that schema is kept.
 * Nothing outside the 450000..499999 window is read or written.
 */
return new class extends Migration
{
    /** Source: Ex System Division. */
    private const SRC_UNIT  = 450000;
    private const SRC_LOWER = 450000;
    private const SRC_UPPER = 499999;

    /** Destination: Command, Control and Communication (C3). */
    private const DST_UNIT  = 200000;
    private const DST_LOWER = 200000;
    private const DST_UPPER = 249999;

    /** Snapshot + id-map schema. Keep it until the merge is signed off. */
    private const BK = 'merge_exsys_c3';

    /**
     * Every table that carries a division stamp.
     *
     *   pk     - primary key columns, used to restore the rows in down()
     *   cols   - the *_unt_id columns to move to DST_UNIT
     *   select - rows to snapshot (defaults to cols); only needed where a
     *            column is restored but must not drive row selection
     *
     * Tables with no Ex System rows today are listed on purpose: they cost one
     * empty UPDATE and keep the migration correct if data lands there before
     * it is run.
     */
    private const UNIT_COLUMNS = [
        'aud.revs'         => ['pk' => ['rev_id'],                'cols' => ['rev_unt_id', 'rev_intunt_id']],
        'cen.accounts'     => ['pk' => ['acc_id'],                'cols' => ['acc_unt_id', 'acc_lowers', 'acc_uppers'], 'select' => ['acc_unt_id']],
        'cen.roles'        => ['pk' => ['rol_desig'],             'cols' => ['rol_unt_id']],
        'fin.commitments'  => ['pk' => ['cmt_docid', 'cmt_type'], 'cols' => ['cmt_unt_id', 'cmt_effunt_id']],
        'fin.extcompenses' => ['pk' => ['ecp_id'],                'cols' => ['ecp_unt_id']],
        'fin.fund_shifts'  => ['pk' => ['fsh_id'],                'cols' => ['fsh_unt_id']],
        'fin.salorders'    => ['pk' => ['sor_id'],                'cols' => ['sor_unt_id', 'sor_effunt_id']],
        'gen.cases'        => ['pk' => ['gcs_id'],                'cols' => ['gcs_unt_id']],
        'hr.applicants'    => ['pk' => ['apl_id'],                'cols' => ['apl_unt_id']],
        'hr.attendance'    => ['pk' => ['att_id'],                'cols' => ['att_unt_id']],
        'hr.contracts'     => ['pk' => ['ctr_id'],                'cols' => ['ctr_unt_id']],
        'hr.ctrcases'      => ['pk' => ['ctc_id'],                'cols' => ['ctc_unt_id', 'ctc_newunt_id', 'ctc_approvedunt_id']],
        'hr.emps'          => ['pk' => ['emp_id'],                'cols' => ['emp_unt_id']],
        // hr.leaves has no primary key at all, so down() matches on its natural key.
        'hr.leaves'        => ['pk' => ['lve_emp_id', 'lve_from', 'lve_type'], 'cols' => ['lve_unt_id']],
        'hr.salreqs'       => ['pk' => ['srq_id'],                'cols' => ['srq_unt_id', 'srq_effunt_id']],
        'ina.invatlocs'    => ['pk' => ['ial_id'],                'cols' => ['ial_unt_id']],
        'ina.invats'       => ['pk' => ['ias_id'],                'cols' => ['ias_unt_id']],
        'ina.inventory'    => ['pk' => ['inv_id'],                'cols' => ['inv_unt_id']],
        'pur.purcases'     => ['pk' => ['pcs_id'],                'cols' => ['pcs_unt_id', 'pcs_effunt_id', 'pcs_intunt_id']],
        'pur.purreceipts'  => ['pk' => ['prt_id'],                'cols' => ['prt_unt_id']],
        'pur.purreqs'      => ['pk' => ['prq_id'],                'cols' => ['prq_unt_id', 'prq_intunt_id']],
        'puritems.items'   => ['pk' => ['itm_id'],                'cols' => ['itm_unt_id']],
        'puritems.rfqs'    => ['pk' => ['rfq_id'],                'cols' => ['rfq_unt_id']],
        'purnew.items'     => ['pk' => ['item_id'],               'cols' => ['unt_id']],
        'purnew.rfq'       => ['pk' => ['rfq_id'],                'cols' => ['pcs_unt_id', 'pcs_effunt_id']],
        'sup.tickets'      => ['pk' => ['tkt_id'],                'cols' => ['tkt_unt_id']],

        // syslog.events is deliberately NOT in this list. Its partitions are
        // append-only (trigger syslog.fn_deny_change raises on UPDATE), so the
        // ~1.1k historical events stamped with unit 450000 stay exactly as they
        // were recorded. That is the point of the audit trail: those actions
        // really did happen under Ex System Division. Nothing references
        // cen.units by FK from syslog, so retiring the unit row does not break
        // them; old entries simply no longer resolve to a live division.
    ];

    /** Handled by hand in up() because their columns are not all plain stamps. */
    private const SPECIAL_UNIT_UPDATE = ['cen.accounts'];

    /**
     * Every column that points at cen.heads.hed_id or prj.projects.prj_id and
     * therefore has to follow the renumbering.
     *
     * prj.milestones and prj.prghistory are left out on purpose: their FKs are
     * ON UPDATE CASCADE, so Postgres moves them with the parent row.
     */
    private const ID_REF_COLUMNS = [
        'doc.documents'             => ['prj_id'],
        'fin.commitments'           => ['cmt_hed_id', 'cmt_effhed_id'],
        'fin.empeffheads'           => ['eeh_emphed_id'],
        'fin.fund_shifts'           => ['fsh_hed_id', 'fsh_prj_id'],
        'fin.msncosts'              => ['mct_hed_id', 'mct_prj_id'],
        'fin.salorders'             => ['sor_hed_id', 'sor_effhed_id'],
        'fin.sharesalloc'           => ['sha_hed_id'],
        'fin.sharesinstall'         => ['shi_hed_id'],
        'fin.subheads'              => ['sbh_hed_id'],
        'fin.subheads_zzz'          => ['sbh_hed_id'],
        'hr.contractplans'          => ['cpn_hed_id'],
        'hr.contracts'              => ['ctr_hed_id'],
        'hr.ctrcaseplans'           => ['ccp_hed_id'],
        'hr.ctrcases'               => ['ctc_prj_id'],
        'hr.emps'                   => ['emp_hed_id'],
        'hr.salreqs'                => ['srq_hed_id', 'srq_effhed_id'],
        'ina.invats'                => ['ias_prj_id', 'ias_effhed_id'],
        'ina.inventory'             => ['inv_prj_id'],
        'prj.events'                => ['evt_xprj_id'],
        'public.project_activities' => ['pja_prj_id'],
        'pur.purcases'              => ['pcs_hed_id', 'pcs_effhed_id'],
        'pur.purreceipts'           => ['prt_prj_id'],
        'pur.purreqs'               => ['prq_hed_id', 'prq_effhed_id', 'prq_appeffhed_id'],
        'puritems.items'            => ['itm_hed_id'],
        'purnew.rfq'                => ['pcs_hed_id', 'pcs_effhed_id'],
    ];

    public function up(): void
    {
        if (! DB::table('cen.units')->where('unt_id', self::SRC_UNIT)->exists()) {
            $this->say('Unit ' . self::SRC_UNIT . ' does not exist - merge already applied, nothing to do.');

            return;
        }

        if (! DB::table('cen.units')->where('unt_id', self::DST_UNIT)->exists()) {
            throw new RuntimeException('Destination unit ' . self::DST_UNIT . ' (C3) not found.');
        }

        DB::transaction(function () {
            DB::statement('CREATE SCHEMA IF NOT EXISTS ' . self::BK);

            // ---------------------------------------------------------------
            // 1. Work out the new ids inside the free part of the C3 block.
            // ---------------------------------------------------------------
            $oldProjects = DB::table('prj.projects')
                ->whereBetween('prj_unt_id', [self::SRC_LOWER, self::SRC_UPPER])
                ->orderBy('prj_id')
                ->get(['prj_id', 'prj_code']);

            $next = max(
                (int) DB::table('cen.heads')->whereBetween('hed_id', [self::DST_LOWER, self::DST_UPPER])->max('hed_id'),
                (int) DB::table('prj.projects')->whereBetween('prj_id', [self::DST_LOWER, self::DST_UPPER])->max('prj_id')
            ) + 1;

            $map = [];
            foreach ($oldProjects as $p) {
                if ($next > self::DST_UPPER) {
                    throw new RuntimeException('C3 id block is exhausted; cannot allocate an id for project ' . $p->prj_id);
                }
                $map[(int) $p->prj_id] = $next++;
            }

            foreach ($map as $old => $new) {
                foreach ([['cen.heads', 'hed_id'], ['prj.projects', 'prj_id']] as [$t, $c]) {
                    if (DB::table($t)->where($c, $new)->exists()) {
                        throw new RuntimeException("Target id {$new} is already taken in {$t}.{$c}.");
                    }
                }
            }

            // prj.projects is the parent of doc.documents through a plain FK (no
            // ON UPDATE CASCADE) and doc.documents.prj_id is NOT NULL, so a
            // referencing row would block the renumbering.
            $blocking = DB::table('doc.documents')->whereIn('prj_id', array_keys($map))->count();
            if ($blocking > 0) {
                throw new RuntimeException("doc.documents has {$blocking} row(s) on the moving projects; retarget them before merging.");
            }

            DB::statement('DROP TABLE IF EXISTS ' . self::BK . '.id_map');
            DB::statement('CREATE TABLE ' . self::BK . '.id_map (old_id integer primary key, new_id integer not null unique, code text, merged_at timestamp default now())');
            foreach ($oldProjects as $p) {
                DB::table(self::BK . '.id_map')->insert([
                    'old_id' => (int) $p->prj_id,
                    'new_id' => $map[(int) $p->prj_id],
                    'code'   => $p->prj_code,
                ]);
            }

            // ---------------------------------------------------------------
            // 2. Snapshot everything the merge is about to touch.
            // ---------------------------------------------------------------
            foreach (self::UNIT_COLUMNS as $table => $spec) {
                $this->snapshot($table, $spec['select'] ?? $spec['cols']);
            }
            $this->snapshotWhere('prj.projects', 'prj_id in (' . implode(',', array_keys($map)) . ')');
            $this->snapshotWhere('cen.heads', 'hed_id between ' . self::SRC_LOWER . ' and ' . self::SRC_UPPER);
            $this->snapshotWhere('cen.units', 'unt_id = ' . self::SRC_UNIT);

            $report = [];

            // prj_id_old still carries the legacy MS Access ids (3 digits) and is
            // a smallint, which cannot hold a 6-digit RDWIS id. Widen it so the
            // pre-merge id stays visible in the table itself and not only in the
            // snapshot schema. Nothing in the application reads this column.
            $type = DB::table('information_schema.columns')
                ->where('table_schema', 'prj')->where('table_name', 'projects')
                ->where('column_name', 'prj_id_old')->value('data_type');
            if ($type === 'smallint') {
                DB::statement('ALTER TABLE prj.projects ALTER COLUMN prj_id_old TYPE integer');
            }

            // ---------------------------------------------------------------
            // 3. Renumber the projects and their heads into the C3 block.
            //    prj.milestones / prj.prghistory follow via ON UPDATE CASCADE.
            // ---------------------------------------------------------------
            foreach ($map as $old => $new) {
                DB::table('prj.projects')->where('prj_id', $old)->update([
                    'prj_id'     => $new,
                    'prj_unt_id' => self::DST_UNIT,
                    'prj_id_old' => DB::raw('coalesce(prj_id_old, ' . $old . ')'),
                ]);

                DB::table('cen.heads')->where('hed_id', $old)->update([
                    'hed_id'     => $new,
                    'hed_unt_id' => self::DST_UNIT,
                    'hed_prj_id' => $new,
                ]);
            }
            $report['projects renumbered'] = count($map);

            // ---------------------------------------------------------------
            // 4. Re-point every row that referenced the old ids.
            // ---------------------------------------------------------------
            foreach (self::ID_REF_COLUMNS as $table => $columns) {
                foreach ($columns as $column) {
                    $moved = 0;
                    foreach ($map as $old => $new) {
                        $moved += DB::table($table)->where($column, $old)->update([$column => $new]);
                    }
                    if ($moved > 0) {
                        $report["{$table}.{$column}"] = $moved;
                    }
                }
            }

            // ---------------------------------------------------------------
            // 5. Retire the division head (nothing may reference it any more).
            // ---------------------------------------------------------------
            if (DB::table('cen.heads')->where('hed_id', self::SRC_UNIT)->exists()) {
                $stillUsed = 0;
                foreach (self::ID_REF_COLUMNS as $table => $columns) {
                    foreach ($columns as $column) {
                        $stillUsed += DB::table($table)->where($column, self::SRC_UNIT)->count();
                    }
                }
                if ($stillUsed > 0) {
                    throw new RuntimeException('Division head ' . self::SRC_UNIT . " is still referenced by {$stillUsed} row(s); merge aborted.");
                }

                DB::table('cen.heads')->where('hed_id', self::SRC_UNIT)->delete();
                $report['division head deleted'] = 1;
            }

            // ---------------------------------------------------------------
            // 6. Re-stamp every division column onto C3.
            // ---------------------------------------------------------------
            foreach (self::UNIT_COLUMNS as $table => $spec) {
                if (in_array($table, self::SPECIAL_UNIT_UPDATE, true)) {
                    continue;
                }
                foreach ($spec['cols'] as $column) {
                    $n = DB::table($table)
                        ->whereBetween($column, [self::SRC_LOWER, self::SRC_UPPER])
                        ->update([$column => self::DST_UNIT]);
                    if ($n > 0) {
                        $report["{$table}.{$column}"] = ($report["{$table}.{$column}"] ?? 0) + $n;
                    }
                }
            }

            // Accounts also carry their own horizon window, which must become C3's.
            $report['cen.accounts moved'] = DB::table('cen.accounts')
                ->whereBetween('acc_unt_id', [self::SRC_LOWER, self::SRC_UPPER])
                ->update([
                    'acc_unt_id' => self::DST_UNIT,
                    'acc_lowers' => self::DST_LOWER,
                    'acc_uppers' => self::DST_UPPER,
                ]);

            // ---------------------------------------------------------------
            // 7. Drop the unit row itself (FKs from accounts/roles are clear now).
            // ---------------------------------------------------------------
            DB::table('cen.units')->where('unt_id', self::SRC_UNIT)->delete();
            $report['cen.units deleted'] = 1;

            $this->say('Ex System Division (' . self::SRC_UNIT . ') merged into C3 (' . self::DST_UNIT . ').');
            foreach ($map as $old => $new) {
                $this->say("  project {$old} -> {$new}");
            }
            foreach ($report as $what => $count) {
                $this->say('  ' . str_pad((string) $what, 44) . $count);
            }
            $this->say('  snapshots kept in schema "' . self::BK . '" (needed by down()).');

            Log::info('Ex System -> C3 merge applied', ['id_map' => $map, 'report' => $report]);
        });
    }

    public function down(): void
    {
        if (! $this->schemaExists(self::BK)) {
            throw new RuntimeException('Snapshot schema "' . self::BK . '" is gone; this merge cannot be rolled back automatically.');
        }

        $map = DB::table(self::BK . '.id_map')->orderBy('old_id')->get();
        if ($map->isEmpty()) {
            throw new RuntimeException('Snapshot schema "' . self::BK . '" holds no id map.');
        }

        DB::transaction(function () use ($map) {
            // 1. The unit row comes back first: accounts and roles have FKs to it.
            DB::statement('INSERT INTO cen.units SELECT * FROM ' . $this->bk('cen.units') . ' ON CONFLICT DO NOTHING');

            // 2. Give the projects and their heads their old ids back.
            foreach ($map as $row) {
                DB::table('prj.projects')->where('prj_id', $row->new_id)->update(['prj_id' => $row->old_id]);
                DB::table('cen.heads')->where('hed_id', $row->new_id)->update(['hed_id' => $row->old_id]);
            }

            // Restore the project and head rows (incl. the deleted division head).
            DB::statement('UPDATE prj.projects p SET prj_unt_id = b.prj_unt_id, prj_id_old = b.prj_id_old FROM ' . $this->bk('prj.projects') . ' b WHERE p.prj_id = b.prj_id');
            DB::statement('INSERT INTO cen.heads SELECT * FROM ' . $this->bk('cen.heads') . ' ON CONFLICT (hed_id) DO UPDATE SET hed_name = excluded.hed_name, hed_code = excluded.hed_code, hed_unt_id = excluded.hed_unt_id, hed_prj_id = excluded.hed_prj_id');

            // 3. Reverse the id references (those new ids belong to this merge only).
            foreach (self::ID_REF_COLUMNS as $table => $columns) {
                foreach ($columns as $column) {
                    foreach ($map as $row) {
                        DB::table($table)->where($column, $row->new_id)->update([$column => $row->old_id]);
                    }
                }
            }

            // 4. Restore every division stamp from its snapshot, matched on PK.
            foreach (self::UNIT_COLUMNS as $table => $spec) {
                $bk = $this->bk($table);
                $set = implode(', ', array_map(fn ($c) => "{$c} = b.{$c}", $spec['cols']));
                $on = implode(' and ', array_map(fn ($k) => "t.{$k} = b.{$k}", $spec['pk']));
                DB::statement("UPDATE {$table} t SET {$set} FROM {$bk} b WHERE {$on}");
            }

            $this->say('Ex System Division restored from snapshot schema "' . self::BK . '".');
            Log::info('Ex System -> C3 merge rolled back');
        });

        DB::statement('DROP SCHEMA IF EXISTS ' . self::BK . ' CASCADE');
    }

    /** Copy the rows a division column selects into the snapshot schema. */
    private function snapshot(string $table, array $selectColumns): void
    {
        $where = implode(' or ', array_map(
            fn ($c) => sprintf('%s between %d and %d', $c, self::SRC_LOWER, self::SRC_UPPER),
            $selectColumns
        ));

        $this->snapshotWhere($table, $where);
    }

    private function snapshotWhere(string $table, string $where): void
    {
        $bk = $this->bk($table);
        DB::statement("DROP TABLE IF EXISTS {$bk}");
        DB::statement("CREATE TABLE {$bk} AS SELECT * FROM {$table} WHERE {$where}");
    }

    private function bk(string $table): string
    {
        return self::BK . '.' . str_replace('.', '__', $table);
    }

    private function schemaExists(string $schema): bool
    {
        return DB::table('information_schema.schemata')->where('schema_name', $schema)->exists();
    }

    private function say(string $line): void
    {
        if (app()->runningInConsole()) {
            echo $line . PHP_EOL;
        }
    }
};
