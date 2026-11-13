# RDWIS 2.0 — `syslog` Schema: System-Wide Immutable Audit Trail

**Status: IMPLEMENTED AND INSTALLED ON `rdw03` (staging). Not yet on `rdw04`.**

Date: 10 November 2026

> **What is built** — see [SYSLOG_IMPLEMENTATION.md](SYSLOG_IMPLEMENTATION.md) for the file list,
> the install/rollback commands and the staging test results.
>
> - SQL: `database/sql/syslog/01..08*.sql` — schema, 11 tables, 40+ partitions, functions,
>   triggers, views, seed config, roles/grants, attach waves.
> - Migration: `database/migrations/2026_11_10_120000_create_syslog_schema.php`.
> - PHP: `AuditContext` middleware, `App\Services\Syslog\{Audit,LogQueryService,LogPresenter}`,
>   `Admin\SystemLogController`, `audit:*` artisan commands, `syslog.*` permissions.
> - UI: `resources/views/admin/syslog/*` — 3 levels, 6 tabs, print sheet, Trail Health.
> - **127 of 127 business tables audited** on `rdw03`, every trigger `ENABLE ALWAYS`.
>
> Still open from this design: phases 12 / 17 (backfill), 16 (print from snapshot), 19 (the
> optional `ALTER`s), and the `Audit::log()` / `Audit::file()` calls inside the existing module
> controllers (phases 18 and 20) — the triggers already record those writes, but without the
> authority reference and original filename that only PHP can supply.
Codebase: `H:\RDWIS 2.0\RDWIS APP 2.0` — Laravel 12, PHP 8.2
Target database: `rdw04` on `127.0.0.1:5433` — **PostgreSQL 18.4**, currently 53 MB
Companion document: [AUDIT_TRAIL_PLAN.md](AUDIT_TRAIL_PLAN.md) (discovery of what exists today)

---

## 0. Scope and decisions taken

| Decision | Choice | Why |
|---|---|---|
| Where does it live? | **New schema `syslog`** in the **same database** `rdw04` | The `aud` schema stays untouched for the Reversal / Data-Revision workflow. A new schema keeps the two concepts apart, while the same database allows the audit row to be written **in the same transaction** as the change (PostgreSQL has no cross-database transactions). |
| Schema name | `syslog` | `aud` is taken by reversals. Plain `log` is ambiguous in this project — `cen.accounts.acc_untarea` literally has the value `log` for DLOG. `audit` reads too close to `aud`. |
| Capture mechanism | **PostgreSQL row triggers** as the backbone | Catches every write: Eloquent, the 970 raw `DB::table()` calls, the legacy Access app, cron scripts, manual `psql`. A PHP-only solution misses two-thirds of writes. |
| Deletes | **Full row snapshot in JSONB** before deletion | "Agar kuch delete bhi ho tab bhi aaye" — the deleted record is fully reconstructible from the log. |
| Immutability | Privileges + `ENABLE ALWAYS` deny-triggers + periodic hash seals + off-box copy | See §6. Honest limits stated in §6.6. |
| Retention | Never delete. Monthly partitions, old ones detached to an archive tablespace but kept attached read-only | §8. |
| Legacy `aud.busdata` | Left alone, exposed through a read-only union view | 1,380 rows of 2021→2026 history stay visible on the same screen. |
| Case trail ("kisne kisko bheja, kya remarks") | Unified in `syslog.events`; the six existing per-module trail tables keep working and are themselves audited | §5A. It exists today in six different shapes, and covers only **13 of 2,004** purchase cases. |
| Case snapshot at each handover | `syslog.case_snapshots` — the whole composed case as JSONB at every custody change, hash-de-duplicated | §5B. Measured cost: **~7–12 MB/year**. A case is 8 tables deep, so the composition is declared in `syslog.case_composition`. |
| File uploads / downloads | `syslog.file_events` — original filename, stored path, size, MIME, sha256, written from PHP | §5C. **Six of seven attachment tables store only a path** — the user's filename is lost today. 7.5 GB of files, no download record. |
| Project / money / account / employee / salary events | Catalogued as named `sev_action` + `sev_object` values on top of the generic triggers | §5D. **Only 4 tables in the whole database record who created a row**; `fin.subheads.sbh_alloc` has no history at all. |
| UI | One read-only **System Logs** section for IT Admin / Superadmin: category tiles → event list → one record with six tabs, printable | §5E. Built on the project's existing `welcome` layout and `case_print_layout`; no new front-end dependency. |
| Legacy app safety | The audit trail needs **zero `ALTER TABLE`**; triggers do not change table shape. Optional columns are staged on `rdw03` first | §8A. The legacy app's live write footprint is **three tables**, proven from `aud.busdata.bdt_formname`. |

---

## 1. What the schema must answer

Every row in `syslog.events` must be able to answer all nine questions:

| Question | Column(s) |
|---|---|
| **Kab?** (when) | `sev_dtg` (`timestamptz`, microsecond) |
| **Kisne?** (who) | `sev_acc_id`, `sev_username`, `sev_name`, `sev_rank` |
| **Kis authority pe?** | `sev_desig`, `sev_role_slug`, `sev_auth`, `sev_level`, `sev_unt_id`, `sev_untname`, `sev_untarea`, `sev_authority_ref` |
| **Kahan se?** (where from) | `sev_ip`, `sev_fwd_ip`, `sev_user_agent`, `sev_hostname`, `sev_session_id` |
| **Kya kiya?** (what) | `sev_action`, `sev_object`, `sev_objid`, `sev_objref`, `sev_stage_from`, `sev_stage_to` |
| **Kyun?** (why) | `sev_remarks`, `sev_reason_code` |
| **Kaunsi value badli?** | `syslog.field_changes` — per column old → new |
| **Poora record kya tha?** | `syslog.row_changes.rc_old_row` / `rc_new_row` (JSONB) |
| **Kya ye log chhera gaya?** | `sev_hash` + `syslog.seals` (§6.4) |

---

## 2. Table map

```
syslog.events          ──┬──  syslog.row_changes   ──  syslog.field_changes
 (header, 1 per action)  │     (1 per affected DB row)   (1 per changed column)
                         │
                         └──  (auth / export / print / denied events have no children)

                         ├──  syslog.case_snapshots  (whole case as JSONB, at each handover — §5B)
                         └──  syslog.file_events     (uploads/downloads: original name, hash — §5C)

syslog.watched_tables   -- control table: which tables are audited, how, what is redacted
syslog.case_composition -- control table: which child tables make up each kind of case
syslog.ddl_events       -- schema changes: CREATE / ALTER / DROP / trigger disable
syslog.seals            -- periodic tamper-evidence seals (the hash chain)
syslog.verify_runs      -- result of every verification run
```

Five tables are **partitioned monthly** by date (`events`, `row_changes`, `field_changes`,
`case_snapshots`, `file_events`). Five are small, unpartitioned control/evidence tables.

---

## 3. DDL — schema and tables

### 3.1 Schema

```sql
CREATE SCHEMA syslog;
COMMENT ON SCHEMA syslog IS
  'System-wide immutable audit trail. APPEND-ONLY. Do not UPDATE or DELETE. '
  'Separate from the aud schema, which holds the Data Revision / Reversal workflow.';
```

### 3.2 `syslog.events` — the header (one row per action)

```sql
CREATE TABLE syslog.events (
  sev_id            bigint GENERATED ALWAYS AS IDENTITY,
  sev_dtg           timestamptz   NOT NULL DEFAULT clock_timestamp(),

  ----------------------------------------------------------------- WHO (snapshot)
  sev_acc_id        integer,                      -- cen.accounts.acc_id; NULL = legacy/console/db
  sev_username      varchar(255)  NOT NULL DEFAULT '(unknown)',
  sev_name          varchar(255),
  sev_rank          varchar(255),

  ----------------------------------------------------------------- AUTHORITY (snapshot)
  sev_desig         varchar(255),   -- designation AT THE MOMENT OF THE ACTION
  sev_desigshort    varchar(255),
  sev_role_slug     varchar(60),    -- COMMAND_DG / COMMAND_DLOG / IT_ADMIN / SORD / ...
  sev_auth          varchar(30),    -- approver | staff
  sev_level         smallint,
  sev_unt_id        integer,
  sev_untname       varchar(255),
  sev_untarea       varchar(50),
  sev_authority_ref varchar(255),   -- sanction / letter no / aud.revs.rev_id that authorised this

  ----------------------------------------------------------------- WHAT
  sev_action        varchar(40)   NOT NULL,
  sev_object        varchar(60),    -- purcase | ctrcase | salorder | gencase | employee | account | role
  sev_objid         varchar(100),   -- business key of the subject
  sev_objref        varchar(200),   -- human-readable reference, e.g. 'PUR-2026-114'
  sev_stage_from    varchar(60),
  sev_stage_to      varchar(60),

  ----------------------------------------------------------------- CASE HANDOVER (§5A)
  sev_from_holder_id integer,               -- case kis ke paas thi
  sev_from_holder    varchar(255),          -- name + desig snapshot
  sev_to_holder_id   integer,               -- kis ko bheji
  sev_to_holder      varchar(255),
  sev_minute_no      smallint,              -- noting-sheet minute (pcs_minute / pcm_minute)
  sev_case_unt_id    integer,               -- owning unit of the case, for unit-wise reports

  ----------------------------------------------------------------- WHY
  sev_reason_code   varchar(40),
  sev_remarks       text,

  ----------------------------------------------------------------- WHERE FROM
  sev_ip            inet,
  sev_fwd_ip        varchar(255),   -- X-Forwarded-For chain (the app sits behind Caddy)
  sev_user_agent    varchar(500),
  sev_hostname      varchar(255),
  sev_route         varchar(200),
  sev_method        varchar(10),
  sev_url           varchar(1000),
  sev_session_id    varchar(100),
  sev_request_id    uuid,           -- correlates with storage/logs/laravel.log

  ----------------------------------------------------------------- CLASSIFICATION
  sev_source        varchar(20)   NOT NULL DEFAULT 'app',  -- app | legacy | console | db | job
  sev_result        varchar(20)   NOT NULL DEFAULT 'ok',   -- ok | failed | denied
  sev_severity      smallint      NOT NULL DEFAULT 0,      -- 0 normal, 1 sensitive, 2 critical
  sev_implicit      boolean       NOT NULL DEFAULT false,  -- true = header created by the trigger,
                                                           -- not by the application
  ----------------------------------------------------------------- TAMPER EVIDENCE
  sev_hash          char(64),       -- sha256 of this row's immutable payload (§6.4)

  CONSTRAINT events_pk PRIMARY KEY (sev_dtg, sev_id),
  CONSTRAINT events_action_ck CHECK (sev_action IN (
      'created','updated','deleted','truncated','restored',
      'approved','rejected','returned','released','cancelled','forwarded','reopened',
      'login','login_failed','logout','password_changed','password_forced',
      'denied','permission_changed','role_changed','account_created','account_disabled',
      'uploaded','downloaded','deleted_file','exported','printed','viewed_sensitive',
      'imported','job_ran','setting_changed','ddl'
  )),
  CONSTRAINT events_result_ck CHECK (sev_result IN ('ok','failed','denied')),
  CONSTRAINT events_source_ck CHECK (sev_source IN ('app','legacy','console','db','job'))
) PARTITION BY RANGE (sev_dtg);

CREATE INDEX idx_events_dtg      ON syslog.events (sev_dtg DESC);
CREATE INDEX idx_events_acc      ON syslog.events (sev_acc_id, sev_dtg DESC);
CREATE INDEX idx_events_obj      ON syslog.events (sev_object, sev_objid, sev_dtg DESC);
CREATE INDEX idx_events_action   ON syslog.events (sev_action, sev_dtg DESC);
CREATE INDEX idx_events_ip       ON syslog.events (sev_ip);
CREATE INDEX idx_events_req      ON syslog.events (sev_request_id);
CREATE INDEX idx_events_bad      ON syslog.events (sev_dtg DESC) WHERE sev_result <> 'ok';
CREATE INDEX idx_events_holder    ON syslog.events (sev_to_holder_id, sev_dtg DESC);
CREATE INDEX idx_events_case      ON syslog.events (sev_object, sev_objid, sev_dtg)
       WHERE sev_action IN ('created','forwarded','approved','rejected','returned',
                            'released','cancelled','reopened');
```

**Note on the primary key.** PostgreSQL requires the partition key to be part of every unique
constraint, so the PK is `(sev_dtg, sev_id)` rather than `sev_id` alone. `sev_id` is still globally
unique because it comes from one identity sequence — children reference `(sev_dtg, sev_id)`.

**No foreign key to `cen.accounts`.** Deliberate. If an account row is ever deleted the audit row
must survive untouched, and `cen.accounts.acc_desig` has an `ON UPDATE CASCADE` FK to `cen.roles`
that would retroactively rewrite designations. Everything about the actor is **copied in as a frozen
snapshot**. Joining to the live account for display is done in the view, by `sev_acc_id`, as a
convenience only.

### 3.3 `syslog.row_changes` — one row per affected database row

```sql
CREATE TABLE syslog.row_changes (
  rc_id        bigint GENERATED ALWAYS AS IDENTITY,
  rc_dtg       timestamptz  NOT NULL,            -- copied from the event, for partition pruning
  rc_sev_id    bigint       NOT NULL,
  rc_seq       integer      NOT NULL,            -- order within the event

  rc_schema    varchar(30)  NOT NULL,            -- 'pur'
  rc_table     varchar(60)  NOT NULL,            -- 'purcases'
  rc_op        char(1)      NOT NULL,            -- I | U | D | T  (T = truncate)

  rc_pk        jsonb,                            -- {"pcs_id": 1143}
                                                 -- composite: {"pci_pcs_id":1143,"pci_serial":3}
                                                 -- NULL only for a PK-less table (hr.leaves)
  rc_pk_text   varchar(300),                     -- 'pcs_id=1143' — for indexing and display
  rc_ctid      varchar(30),                      -- physical locator, fallback for PK-less tables

  rc_old_row   jsonb,                            -- FULL row before. Always present for U and D.
  rc_new_row   jsonb,                            -- FULL row after.  Always present for I and U.
  rc_changed   text[],                           -- names of columns that actually changed (U only)

  CONSTRAINT row_changes_pk PRIMARY KEY (rc_dtg, rc_id),
  CONSTRAINT row_changes_fk FOREIGN KEY (rc_dtg, rc_sev_id)
      REFERENCES syslog.events (sev_dtg, sev_id),
  CONSTRAINT row_changes_op_ck CHECK (rc_op IN ('I','U','D','T'))
) PARTITION BY RANGE (rc_dtg);

CREATE INDEX idx_rc_event ON syslog.row_changes (rc_sev_id);
CREATE INDEX idx_rc_row   ON syslog.row_changes (rc_schema, rc_table, rc_pk_text, rc_dtg DESC);
CREATE INDEX idx_rc_del   ON syslog.row_changes (rc_schema, rc_table, rc_dtg DESC) WHERE rc_op = 'D';
CREATE INDEX idx_rc_pk_gin ON syslog.row_changes USING gin (rc_pk);
```

**This is the table that makes deletes safe.** On `DELETE`, `rc_old_row` holds the entire row as
JSONB — every column, exactly as it was. Restoring a wrongly deleted record is then:

```sql
INSERT INTO pur.purcases
SELECT * FROM jsonb_populate_record(NULL::pur.purcases, rc_old_row)
FROM syslog.row_changes
WHERE rc_schema='pur' AND rc_table='purcases' AND rc_pk_text='pcs_id=1143' AND rc_op='D';
```

(The restore itself is of course logged, as `sev_action='restored'`.)

### 3.4 `syslog.field_changes` — one row per changed column

```sql
CREATE TABLE syslog.field_changes (
  fc_id        bigint GENERATED ALWAYS AS IDENTITY,
  fc_dtg       timestamptz  NOT NULL,
  fc_sev_id    bigint       NOT NULL,
  fc_rc_id     bigint       NOT NULL,

  fc_schema    varchar(30)  NOT NULL,
  fc_table     varchar(60)  NOT NULL,
  fc_pk_text   varchar(300),
  fc_column    varchar(60)  NOT NULL,
  fc_label     varchar(150),           -- 'Case Status' — human label from watched_tables
  fc_datatype  varchar(40),
  fc_old       text,                   -- '3'
  fc_new       text,                   -- '4'
  fc_old_label text,                   -- 'In Process'  (resolved display value, optional)
  fc_new_label text,                   -- 'Approved'
  fc_redacted  boolean NOT NULL DEFAULT false,

  CONSTRAINT field_changes_pk PRIMARY KEY (fc_dtg, fc_id),
  CONSTRAINT field_changes_fk FOREIGN KEY (fc_dtg, fc_sev_id)
      REFERENCES syslog.events (sev_dtg, sev_id)
) PARTITION BY RANGE (fc_dtg);

CREATE INDEX idx_fc_event  ON syslog.field_changes (fc_sev_id);
CREATE INDEX idx_fc_field  ON syslog.field_changes (fc_schema, fc_table, fc_column, fc_dtg DESC);
CREATE INDEX idx_fc_row    ON syslog.field_changes (fc_schema, fc_table, fc_pk_text, fc_dtg DESC);
```

Strictly this is **derived** from `row_changes` — the same facts could be computed from the two JSONB
blobs. It exists because the real questions are per-field: *"`pcs_cost` kis kis ne badla aur kab?"*
Answering that from JSONB across a year of logs means a full scan; with this table it is one index
lookup. The duplication is a deliberate, documented trade (§7 sizes it).

### 3.5 `syslog.watched_tables` — the control table

This is the single place that decides **what is audited and how**. Maintenance (§8) is mostly
editing this table.

```sql
CREATE TABLE syslog.watched_tables (
  wt_schema        varchar(30)  NOT NULL,
  wt_table         varchar(60)  NOT NULL,
  wt_enabled       boolean      NOT NULL DEFAULT true,
  wt_pk_cols       text[],                 -- {'pci_pcs_id','pci_serial'} — composite supported
  wt_object        varchar(60),            -- logical module name for sev_object
  wt_ref_expr      text,                   -- SQL snippet giving a human reference, e.g. 'pcs_caseno'
  wt_snapshot_mode varchar(20)  NOT NULL DEFAULT 'delete_only',
                                           -- always | delete_only | never
  wt_redact_cols   text[]       NOT NULL DEFAULT '{}',   -- values replaced with '***'
  wt_ignore_cols   text[]       NOT NULL DEFAULT '{}',   -- changes here produce no field_changes
  wt_labels        jsonb        NOT NULL DEFAULT '{}',   -- {"pcs_status":"Case Status", ...}
  wt_severity      smallint     NOT NULL DEFAULT 0,
  wt_note          text,
  wt_added_dtg     timestamptz  NOT NULL DEFAULT now(),
  wt_added_by      varchar(100),
  CONSTRAINT watched_tables_pk PRIMARY KEY (wt_schema, wt_table),
  CONSTRAINT wt_snapshot_ck CHECK (wt_snapshot_mode IN ('always','delete_only','never'))
);
```

`wt_snapshot_mode` is the main size lever:

| Mode | `rc_old_row` / `rc_new_row` written | Use for |
|---|---|---|
| `always` | on I, U and D | `cen.accounts`, `cen.role_permissions`, `fin.transactions`, `fin.commitments` — anything where a full before/after picture is worth the bytes |
| `delete_only` *(default)* | on I and D only; `U` keeps field diffs only | everything else — this is where ~70% of the storage saving comes from |
| `never` | never (field diffs only) | high-churn, low-value tables |

Seed values to start with:

```sql
INSERT INTO syslog.watched_tables (wt_schema, wt_table, wt_pk_cols, wt_object, wt_snapshot_mode,
                                   wt_redact_cols, wt_severity) VALUES
-- WAVE 1 — identity, authority and money. Full snapshots.
('cen','accounts',          '{acc_id}',   'account',  'always', '{acc_pass}', 2),
('cen','roles',             '{rol_desig}','role',     'always', '{}',         2),
('cen','role_permissions',  '{id}',       'role',     'always', '{}',         2),
('cen','system_settings',   '{set_key}',  'setting',  'always', '{}',         2),
('cen','globalvars',        '{gvar_name}','setting',  'always', '{}',         2),
('cen','units',             '{unt_id}',   'unit',     'always', '{}',         1),
('fin','transactions',      '{trn_id}',   'transaction','always','{}',        2),
('fin','commitments',       '{cmt_docid,cmt_type}','commitment','always','{}',2),
('fin','transfers',         '{trf_id}',   'transfer', 'always', '{}',         2),
('fin','fund_shifts',       '{fsh_id}',   'fundshift','always', '{}',         2),
('fin','salorders',         '{sor_id}',   'salorder', 'always', '{}',         2),
-- WAVE 2 — cases and their workflow
('pur','purcases',          '{pcs_id}',   'purcase',  'delete_only','{}',     1),
('pur','purcaseitems',      '{pci_pcs_id,pci_serial}','purcase','delete_only','{}',1),
('pur','purdecisions',      '{pdec_id}',  'purcase',  'always', '{}',         1),
('hr','ctrcases',           '{ctc_id}',   'ctrcase',  'delete_only','{}',     1),
('hr','contracts',          '{ctr_id}',   'contract', 'always', '{}',         1),
('hr','emps',               '{emp_id}',   'employee', 'delete_only','{}',     1),
('hr','salreqs',            '{srq_id}',   'salreq',   'delete_only','{}',     1),
('hr','attendance',         '{att_id}',   'attendance','delete_only','{}',    0),
('gen','cases',             '{gcs_id}',   'gencase',  'delete_only','{}',     1),
('prj','projects',          '{prj_id}',   'project',  'delete_only','{}',     1),
('prj','milestones',        '{msn_idd}',  'project',  'delete_only','{}',     0),
('doc','documents',         '{doc_id}',   'document', 'delete_only','{}',     1),
('fin','contractsverif',    '{cvf_ctr_id}','contract','always', '{}',         1),
-- PK-less table: no stable identifier, so snapshot everything until a PK is added
('hr','leaves',             NULL,         'leave',    'always', '{}',         1);
```

> **`hr.leaves` has no primary key.** Confirmed from `information_schema`. A trigger can still
> snapshot the full row, but nothing can link two events to "the same leave record". **Recommend
> adding a surrogate PK to `hr.leaves` before Wave 2.** Until then it is set to `always` snapshot
> and `rc_ctid` is recorded as a weak locator.

> **Composite primary keys are normal here**, not an edge case: `fin.commitments`,
> `fin.salorders_shd`, `fin.subheads`, `fin.sharesalloc`, `fin.sharesinstall`, `fin.salary_tax`,
> `pur.purcaseitems`, `pur.purcases_shd`, `pur.purreqitems`, `pur.purreceiptitems`, `pur.quoteitems`,
> `pur.purcaseminuterefs`, `hr.bdapps`, `hr.bdmems`, `hr.ctrcaseminuterefs`, `ina.invatspecs`,
> `frm.facils`, `frm.info`, `frm.projects` and more. That is why `rc_pk` is **JSONB**, not an
> integer.

> **`cen.accounts.acc_pass` must never appear in a log.** It is in `wt_redact_cols`; the trigger
> writes `'***'` and sets `fc_redacted = true`, so the audit still records *that* the password
> changed, and who changed it, without storing the hash.

### 3.6 `syslog.ddl_events` — schema and trigger changes

This is what catches *"someone dropped the audit trigger"*.

```sql
CREATE TABLE syslog.ddl_events (
  dde_id       bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  dde_dtg      timestamptz NOT NULL DEFAULT clock_timestamp(),
  dde_db_user  name        NOT NULL,     -- the PostgreSQL role, not the app user
  dde_app_user varchar(255),             -- from rdwis.username, when set
  dde_ip       inet,
  dde_tag      text        NOT NULL,     -- 'ALTER TABLE' / 'DROP TRIGGER' / 'CREATE TABLE'
  dde_objtype  text,
  dde_objid    text,
  dde_schema   name,
  dde_command  text,                     -- current_query()
  dde_client   inet,                     -- inet_client_addr()
  dde_backend  integer                   -- pg_backend_pid()
);
CREATE INDEX idx_ddl_dtg ON syslog.ddl_events (dde_dtg DESC);
```

### 3.7 `syslog.seals` — periodic tamper-evidence

```sql
CREATE TABLE syslog.seals (
  sel_id          bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  sel_dtg         timestamptz NOT NULL DEFAULT clock_timestamp(),
  sel_from_dtg    timestamptz NOT NULL,     -- range sealed (half-open)
  sel_to_dtg      timestamptz NOT NULL,
  sel_first_sev   bigint,
  sel_last_sev    bigint,
  sel_event_count bigint      NOT NULL,
  sel_rc_count    bigint      NOT NULL,
  sel_fc_count    bigint      NOT NULL,
  sel_prev_hash   char(64),                 -- previous seal's sel_hash — this is the chain
  sel_hash        char(64)    NOT NULL,
  sel_exported    boolean     NOT NULL DEFAULT false,
  sel_export_path text
);
```

### 3.8 `syslog.verify_runs` — audit of the audit

```sql
CREATE TABLE syslog.verify_runs (
  vrn_id        bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  vrn_dtg       timestamptz NOT NULL DEFAULT clock_timestamp(),
  vrn_kind      varchar(20) NOT NULL,     -- scheduled | manual
  vrn_from_seal bigint,
  vrn_to_seal   bigint,
  vrn_seals_ok  integer     NOT NULL DEFAULT 0,
  vrn_status    varchar(20) NOT NULL,     -- ok | broken | error
  vrn_first_bad bigint,                   -- first seal id that failed
  vrn_detail    text,
  vrn_by        varchar(100)
);
```

---

## 4. Functions

### 4.1 Actor context — how the trigger knows *who*

The application sets five PostgreSQL session variables once per request, in middleware. Everything
else flows from them.

```php
// app/Http/Middleware/AuditContext.php   (design — not implemented)
public function handle(Request $request, Closure $next)
{
    $u = auth()->user();
    DB::statement(
        "SELECT set_config('rdwis.acc_id',     ?, false),
                set_config('rdwis.username',   ?, false),
                set_config('rdwis.ip',         ?, false),
                set_config('rdwis.fwd_ip',     ?, false),
                set_config('rdwis.user_agent', ?, false),
                set_config('rdwis.route',      ?, false),
                set_config('rdwis.method',     ?, false),
                set_config('rdwis.url',        ?, false),
                set_config('rdwis.session_id', ?, false),
                set_config('rdwis.request_id', ?, false),
                set_config('rdwis.source',     'app', false)",
        [
            (string) ($u->acc_id ?? ''),  (string) ($u->acc_username ?? ''),
            (string) $request->ip(),      (string) $request->header('X-Forwarded-For', ''),
            substr((string) $request->userAgent(), 0, 500),
            (string) optional($request->route())->getName(),
            $request->method(),           substr($request->fullUrl(), 0, 1000),
            (string) $request->session()->getId(),
            (string) Str::uuid(),
        ]
    );
    return $next($request);
}
```

Three points that matter:

- **`false` as the third argument of `set_config`, not `true`.** `true` means "local to the current
  transaction", and the value would vanish when the transaction ends. Laravel's connection is reused
  per request, so session-level is correct. The middleware must also **clear** them at the end of the
  request (or set them fresh at the start of every request, as above) so a pooled connection never
  attributes one user's write to another.
- `php artisan` commands and queue jobs set `rdwis.source = 'console'` / `'job'` in a bootstrapper.
- If nothing is set, the trigger records `sev_source='db'`, `sev_acc_id=NULL`,
  `sev_username='(unknown)'` and `sev_implicit=true` — a write nobody owns is itself a finding, and
  it is visible rather than silently attributed.

### 4.2 `syslog.begin_event()` — the application's entry point

Called by `Audit::log()` at meaningful business moments (approve / return / release / cancel /
login / export). It inserts the header and parks its id in a session variable so the row triggers
attach their diffs to it.

```sql
CREATE FUNCTION syslog.begin_event(
    p_action        varchar,
    p_object        varchar DEFAULT NULL,
    p_objid         varchar DEFAULT NULL,
    p_objref        varchar DEFAULT NULL,
    p_stage_from    varchar DEFAULT NULL,
    p_stage_to      varchar DEFAULT NULL,
    p_remarks       text    DEFAULT NULL,
    p_reason_code   varchar DEFAULT NULL,
    p_authority_ref varchar DEFAULT NULL,
    p_result        varchar DEFAULT 'ok',
    p_severity      smallint DEFAULT 0
) RETURNS bigint
LANGUAGE plpgsql SECURITY DEFINER SET search_path = syslog, cen, pg_temp AS $fn$
DECLARE
  v_acc  integer := nullif(current_setting('rdwis.acc_id', true), '')::integer;
  v_id   bigint;
  v_a    cen.accounts;
BEGIN
  IF v_acc IS NOT NULL THEN
     SELECT * INTO v_a FROM cen.accounts WHERE acc_id = v_acc;
  END IF;

  INSERT INTO syslog.events (
      sev_acc_id, sev_username, sev_name, sev_rank,
      sev_desig, sev_desigshort, sev_auth, sev_level,
      sev_unt_id, sev_untname, sev_untarea, sev_authority_ref,
      sev_action, sev_object, sev_objid, sev_objref, sev_stage_from, sev_stage_to,
      sev_reason_code, sev_remarks,
      sev_ip, sev_fwd_ip, sev_user_agent, sev_route, sev_method, sev_url,
      sev_session_id, sev_request_id, sev_source, sev_result, sev_severity, sev_implicit)
  VALUES (
      v_acc,
      coalesce(nullif(current_setting('rdwis.username', true), ''), v_a.acc_username, '(unknown)'),
      v_a.acc_name, v_a.acc_rank,
      v_a.acc_desig, v_a.acc_desigshort, v_a.acc_auth, v_a.acc_level,
      v_a.acc_unt_id, v_a.acc_untname, v_a.acc_untarea, p_authority_ref,
      p_action, p_object, p_objid, p_objref, p_stage_from, p_stage_to,
      p_reason_code, p_remarks,
      nullif(current_setting('rdwis.ip', true), '')::inet,
      nullif(current_setting('rdwis.fwd_ip', true), ''),
      nullif(current_setting('rdwis.user_agent', true), ''),
      nullif(current_setting('rdwis.route', true), ''),
      nullif(current_setting('rdwis.method', true), ''),
      nullif(current_setting('rdwis.url', true), ''),
      nullif(current_setting('rdwis.session_id', true), ''),
      nullif(current_setting('rdwis.request_id', true), '')::uuid,
      coalesce(nullif(current_setting('rdwis.source', true), ''), 'db'),
      p_result, p_severity, false)
  RETURNING sev_id INTO v_id;

  PERFORM set_config('rdwis.event_id', v_id::text, false);
  RETURN v_id;
END $fn$;
```

`sev_role_slug` is filled by the application (it is resolved in PHP by `UserAccessContext`, not in
SQL) via a second small call, or passed in as an extra parameter.

### 4.3 `syslog.ensure_event()` — when nobody opened one

```sql
CREATE FUNCTION syslog.ensure_event(p_action varchar, p_object varchar)
RETURNS bigint LANGUAGE plpgsql SECURITY DEFINER AS $fn$
DECLARE v_id bigint := nullif(current_setting('rdwis.event_id', true), '')::bigint;
BEGIN
  IF v_id IS NOT NULL THEN RETURN v_id; END IF;
  v_id := syslog.begin_event(p_action, p_object);
  UPDATE syslog.events SET sev_implicit = true WHERE sev_id = v_id;  -- allowed: see §6.2
  RETURN v_id;
END $fn$;
```

> The `UPDATE` above would be blocked by the immutability trigger. In the real implementation
> `sev_implicit` is passed into `begin_event` as a parameter instead — written here only to show the
> intent. **No code path ever updates `syslog.events`.**

### 4.4 `syslog.fn_audit_row()` — the workhorse

One generic `AFTER INSERT OR UPDATE OR DELETE ... FOR EACH ROW` trigger function, attached to every
watched table. Pseudo-SQL, with the real logic spelled out:

```sql
CREATE FUNCTION syslog.fn_audit_row() RETURNS trigger
LANGUAGE plpgsql SECURITY DEFINER SET search_path = syslog, pg_temp AS $fn$
DECLARE
  v_cfg     syslog.watched_tables;
  v_sev     bigint;
  v_rc      bigint;
  v_old     jsonb := CASE WHEN TG_OP IN ('UPDATE','DELETE') THEN to_jsonb(OLD) END;
  v_new     jsonb := CASE WHEN TG_OP IN ('INSERT','UPDATE') THEN to_jsonb(NEW) END;
  v_op      char(1) := left(TG_OP, 1);          -- I / U / D
  v_pk      jsonb;
  v_changed text[] := '{}';
  v_col     text;
  v_action  varchar;
BEGIN
  SELECT * INTO v_cfg FROM syslog.watched_tables
   WHERE wt_schema = TG_TABLE_SCHEMA AND wt_table = TG_TABLE_NAME;
  IF NOT found OR NOT v_cfg.wt_enabled THEN RETURN NULL; END IF;

  -- 1. redact before anything is stored
  FOREACH v_col IN ARRAY v_cfg.wt_redact_cols LOOP
     IF v_old ? v_col THEN v_old := jsonb_set(v_old, ARRAY[v_col], '"***"'); END IF;
     IF v_new ? v_col THEN v_new := jsonb_set(v_new, ARRAY[v_col], '"***"'); END IF;
  END LOOP;

  -- 2. which columns actually changed (ignoring wt_ignore_cols)
  IF v_op = 'U' THEN
     SELECT coalesce(array_agg(key), '{}') INTO v_changed
       FROM jsonb_each(v_new) n
      WHERE n.value IS DISTINCT FROM (v_old -> n.key)
        AND NOT (n.key = ANY (v_cfg.wt_ignore_cols));
     IF cardinality(v_changed) = 0 THEN RETURN NULL; END IF;  -- no-op UPDATE: log nothing
  END IF;

  -- 3. primary key as JSONB (handles composite keys and PK-less tables)
  IF v_cfg.wt_pk_cols IS NOT NULL THEN
     SELECT jsonb_object_agg(c, coalesce(v_new, v_old) -> c)
       INTO v_pk FROM unnest(v_cfg.wt_pk_cols) c;
  END IF;

  -- 4. header: reuse the request's event, or open an implicit one
  v_action := CASE v_op WHEN 'I' THEN 'created' WHEN 'U' THEN 'updated' ELSE 'deleted' END;
  v_sev := syslog.ensure_event(v_action, v_cfg.wt_object);

  -- 5. row-level record. Snapshots per wt_snapshot_mode; DELETE always keeps the full old row.
  INSERT INTO syslog.row_changes (
      rc_dtg, rc_sev_id, rc_seq, rc_schema, rc_table, rc_op,
      rc_pk, rc_pk_text, rc_ctid, rc_old_row, rc_new_row, rc_changed)
  SELECT e.sev_dtg, v_sev,
         coalesce((SELECT max(rc_seq) FROM syslog.row_changes
                    WHERE rc_sev_id = v_sev AND rc_dtg = e.sev_dtg), 0) + 1,
         TG_TABLE_SCHEMA, TG_TABLE_NAME, v_op,
         v_pk,
         (SELECT string_agg(k || '=' || (v_pk ->> k), ',' ORDER BY k)
            FROM jsonb_object_keys(v_pk) k),
         CASE WHEN v_cfg.wt_pk_cols IS NULL
              THEN coalesce(OLD.ctid, NEW.ctid)::text END,
         CASE WHEN v_op = 'D' THEN v_old                                  -- ALWAYS on delete
              WHEN v_cfg.wt_snapshot_mode = 'always' THEN v_old END,
         CASE WHEN v_op = 'I' AND v_cfg.wt_snapshot_mode <> 'never' THEN v_new
              WHEN v_cfg.wt_snapshot_mode = 'always' THEN v_new END,
         nullif(v_changed, '{}')
    FROM syslog.events e WHERE e.sev_id = v_sev
  RETURNING rc_id INTO v_rc;

  -- 6. one field_changes row per changed column
  IF v_op = 'U' THEN
     INSERT INTO syslog.field_changes (fc_dtg, fc_sev_id, fc_rc_id, fc_schema, fc_table,
            fc_pk_text, fc_column, fc_label, fc_old, fc_new, fc_redacted)
     SELECT (SELECT sev_dtg FROM syslog.events WHERE sev_id = v_sev), v_sev, v_rc,
            TG_TABLE_SCHEMA, TG_TABLE_NAME,
            (SELECT string_agg(k || '=' || (v_pk ->> k), ',' ORDER BY k)
               FROM jsonb_object_keys(v_pk) k),
            c, v_cfg.wt_labels ->> c,
            v_old ->> c, v_new ->> c,
            c = ANY (v_cfg.wt_redact_cols)
       FROM unnest(v_changed) c;
  END IF;

  RETURN NULL;   -- AFTER trigger: return value is ignored
END $fn$;
```

Deliberate behaviours:

- **`SECURITY DEFINER`.** The function is owned by a role that *can* insert into `syslog`; the
  calling app role cannot insert directly. So the only way a row reaches `syslog` is through this
  function — the application cannot forge or skip a log entry.
- **A no-op `UPDATE` writes nothing** (step 2), so `->save()` on an unchanged model does not pollute
  the log.
- **`DELETE` always writes the full old row**, whatever `wt_snapshot_mode` says.
- **`RETURN NULL` in an `AFTER` trigger** is correct and cheap.
- One `INSERT ... SELECT max(rc_seq)` per row is the only sub-query cost; at this system's volume
  (§7) it is immaterial, and it can be dropped in favour of an in-session counter if it ever matters.

### 4.5 `syslog.fn_audit_truncate()`

```sql
CREATE FUNCTION syslog.fn_audit_truncate() RETURNS trigger
LANGUAGE plpgsql SECURITY DEFINER AS $fn$
DECLARE v_sev bigint; v_n bigint;
BEGIN
  EXECUTE format('SELECT count(*) FROM %I.%I', TG_TABLE_SCHEMA, TG_TABLE_NAME) INTO v_n;
  v_sev := syslog.begin_event('truncated', NULL, NULL,
             format('%s.%s (%s rows)', TG_TABLE_SCHEMA, TG_TABLE_NAME, v_n),
             NULL, NULL, 'TRUNCATE issued', NULL, NULL, 'ok', 2::smallint);
  INSERT INTO syslog.row_changes (rc_dtg, rc_sev_id, rc_seq, rc_schema, rc_table, rc_op)
  SELECT sev_dtg, v_sev, 1, TG_TABLE_SCHEMA, TG_TABLE_NAME, 'T'
    FROM syslog.events WHERE sev_id = v_sev;
  RETURN NULL;
END $fn$;
```

A `TRUNCATE` is `severity = 2` and should raise an alert. Note the honest limit: a statement-level
truncate trigger records *that* it happened and the row count, but cannot snapshot the rows.
**Therefore the app role is denied `TRUNCATE` on business tables entirely** (§6.1) — a truncate
should only ever be a deliberate DBA act.

### 4.6 `syslog.fn_ddl_log()` — event triggers

```sql
CREATE FUNCTION syslog.fn_ddl_log() RETURNS event_trigger
LANGUAGE plpgsql SECURITY DEFINER AS $fn$
DECLARE r record;
BEGIN
  FOR r IN SELECT * FROM pg_event_trigger_ddl_commands() LOOP
    INSERT INTO syslog.ddl_events (dde_db_user, dde_app_user, dde_ip, dde_tag, dde_objtype,
                                   dde_objid, dde_schema, dde_command, dde_client, dde_backend)
    VALUES (session_user, nullif(current_setting('rdwis.username', true), ''),
            nullif(current_setting('rdwis.ip', true), '')::inet,
            r.command_tag, r.object_type, r.object_identity, r.schema_name,
            current_query(), inet_client_addr(), pg_backend_pid());
  END LOOP;
END $fn$;

CREATE EVENT TRIGGER ev_ddl_log  ON ddl_command_end EXECUTE FUNCTION syslog.fn_ddl_log();
CREATE EVENT TRIGGER ev_drop_log ON sql_drop        EXECUTE FUNCTION syslog.fn_sql_drop_log();
```

This is what records `ALTER TABLE ... DISABLE TRIGGER`, `DROP TRIGGER`, a new column appearing, or
someone dropping a whole table.

### 4.7 `syslog.attach_audit()` / `detach_audit()`

The one command used in day-to-day maintenance:

```sql
CREATE FUNCTION syslog.attach_audit(p_schema name, p_table name) RETURNS void
LANGUAGE plpgsql AS $fn$
BEGIN
  -- register with the PK read from the catalog if not already registered
  INSERT INTO syslog.watched_tables (wt_schema, wt_table, wt_pk_cols, wt_added_by)
  SELECT p_schema, p_table,
         (SELECT array_agg(a.attname ORDER BY k.ord)
            FROM pg_constraint c
            JOIN unnest(c.conkey) WITH ORDINALITY k(attnum, ord) ON true
            JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = k.attnum
           WHERE c.conrelid = format('%I.%I', p_schema, p_table)::regclass
             AND c.contype = 'p'),
         session_user
  ON CONFLICT (wt_schema, wt_table) DO NOTHING;

  EXECUTE format('DROP TRIGGER IF EXISTS zz_audit_row ON %I.%I', p_schema, p_table);
  EXECUTE format($q$CREATE TRIGGER zz_audit_row AFTER INSERT OR UPDATE OR DELETE ON %I.%I
                    FOR EACH ROW EXECUTE FUNCTION syslog.fn_audit_row()$q$, p_schema, p_table);
  EXECUTE format('ALTER TABLE %I.%I ENABLE ALWAYS TRIGGER zz_audit_row', p_schema, p_table);

  EXECUTE format('DROP TRIGGER IF EXISTS zz_audit_trunc ON %I.%I', p_schema, p_table);
  EXECUTE format($q$CREATE TRIGGER zz_audit_trunc AFTER TRUNCATE ON %I.%I
                    FOR EACH STATEMENT EXECUTE FUNCTION syslog.fn_audit_truncate()$q$,
                 p_schema, p_table);
  EXECUTE format('ALTER TABLE %I.%I ENABLE ALWAYS TRIGGER zz_audit_trunc', p_schema, p_table);
END $fn$;
```

Two details that are easy to get wrong:

- **The `zz_` name prefix.** Triggers fire in alphabetical order. A `zz_` name makes the audit
  trigger fire **last**, after any business trigger has finished modifying the row, so the log
  records the final state.
- **`ENABLE ALWAYS`, not the default `ENABLE`.** A default-enabled trigger is skipped when a session
  sets `session_replication_role = 'replica'` — which is exactly the one-line trick someone would use
  to write without being logged. `ALWAYS` closes that door.

---

## 5. Views for the application

```sql
-- One-line timeline for the System Logs screen
CREATE VIEW syslog.v_timeline AS
SELECT e.sev_id, e.sev_dtg, e.sev_username, e.sev_name, e.sev_desig, e.sev_untname,
       e.sev_role_slug, e.sev_auth, e.sev_action, e.sev_object, e.sev_objref,
       e.sev_stage_from, e.sev_stage_to, e.sev_remarks, e.sev_authority_ref,
       e.sev_ip, e.sev_route, e.sev_source, e.sev_result, e.sev_severity,
       (SELECT count(*) FROM syslog.row_changes   r WHERE r.rc_sev_id = e.sev_id) AS rows_touched,
       (SELECT count(*) FROM syslog.field_changes f WHERE f.fc_sev_id = e.sev_id) AS fields_changed
  FROM syslog.events e;

-- Full history of one business record, for the "Record history" tab
CREATE FUNCTION syslog.record_history(p_schema name, p_table name, p_pk_text varchar)
RETURNS TABLE (dtg timestamptz, who varchar, desig varchar, ip inet, action varchar,
               col varchar, label varchar, old_val text, new_val text, remarks text)
LANGUAGE sql STABLE AS $fn$
  SELECT e.sev_dtg, e.sev_username, e.sev_desig, e.sev_ip, e.sev_action,
         f.fc_column, f.fc_label, f.fc_old, f.fc_new, e.sev_remarks
    FROM syslog.row_changes r
    JOIN syslog.events e ON e.sev_id = r.rc_sev_id
    LEFT JOIN syslog.field_changes f ON f.fc_rc_id = r.rc_id
   WHERE r.rc_schema = p_schema AND r.rc_table = p_table AND r.rc_pk_text = p_pk_text
   ORDER BY e.sev_dtg, f.fc_id;
$fn$;

-- Coverage / drift detector: business tables NOT being audited
CREATE VIEW syslog.v_coverage AS
SELECT t.table_schema, t.table_name,
       (w.wt_schema IS NOT NULL)                        AS registered,
       coalesce(w.wt_enabled, false)                    AS enabled,
       EXISTS (SELECT 1 FROM pg_trigger g
                WHERE g.tgrelid = format('%I.%I', t.table_schema, t.table_name)::regclass
                  AND g.tgname = 'zz_audit_row'
                  AND g.tgenabled = 'A')                AS trigger_always_on
  FROM information_schema.tables t
  LEFT JOIN syslog.watched_tables w
         ON w.wt_schema = t.table_schema AND w.wt_table = t.table_name
 WHERE t.table_type = 'BASE TABLE'
   AND t.table_schema IN ('cen','pur','hr','fin','prj','doc','sup','gen',
                          'frm','ina','puritems','purnew');

-- Legacy history in the same shape, so one screen shows 2021 -> today
CREATE VIEW syslog.v_legacy_busdata AS
SELECT b.bdt_dtg            AS sev_dtg,
       b.bdt_username       AS sev_username,
       b.bdt_empname        AS sev_name,
       b.bdt_empdesig       AS sev_desig,
       NULL::inet           AS sev_ip,
       lower(b.bdt_action)  AS sev_action,
       b.bdt_formname       AS source_form,
       b.bdt_fieldname      AS fc_column,
       b.btd_oldvalue       AS fc_old,
       b.btd_newvalue       AS fc_new,
       b.bdt_recordid       AS rc_pk_text,
       'legacy'::varchar    AS sev_source
  FROM aud.busdata b;
```

---

## 5A. Case trail — "kisne kisko bheja, kya remarks likhe"

This is the register an officer actually asks for: one case, and against it every hand, in order —
**kab, kisne, kis stage se kis stage pe, kis ke paas se kis ke paas, kya remarks, kis authority pe.**

### 5A.1 What exists today — module by module

Verified from `rdw04` / `rdw02`. This part of the system **does exist**, but as six separate,
differently-shaped implementations.

| Module | Case table | Rows | "Currently with whom" | Decision / remarks table | Rows | Stage history | from → to holder | Actor FK | IP |
|---|---|---|---|---|---|---|---|---|---|
| Purchase | `pur.purcases` | 2,004 | ✗ none | `pur.purdecisions` — `acc_id, role, action, from_status, to_status, remarks, amount, created_at` | **48** | `pur.purcase_substatus` (stage, is_current, since, until) — 107 | ✗ | ✓ `pdec_acc_id` | ✗ |
| Contract (HR) | `hr.ctrcases` | 275 | ✗ none | `hr.ctrcaseremarks` — `username, user_rank, user_desig, remarks, status, dtg` | **75** | `hr.ctrcase_substatus` — 346 | ✗ | **✗ username string only** | ✗ |
| Generic cases | `gen.cases` | — | ✓ `gcs_current_holder_id` → FK accounts | `gen.case_notes` | — | `gen.case_movements` | **✓ `gcm_from_holder_id` / `gcm_to_holder_id`** | ✓ | ✗ |
| Fund shifting | `fin.fund_shifts` | 3 | ⚠ `fsh_current_holder` **varchar**, no FK | `fin.fund_shift_decisions` — `acc_id, user_name, rank, desig, role, action, from_stage, to_stage, remarks, dtg` | 15 | in decisions | ✗ | ✓ | ✗ |
| Salary order | `fin.sal_order_cases` | 4 | ⚠ `soc_current_holder` **varchar**, no FK | `fin.sal_order_case_decisions` — same shape | 4 | in decisions | ✗ | ✓ | ✗ |
| Support | `sup.tickets` | 11 | ✗ | `sup.ticket_activities` — `user_id, user_name, user_role, action, message, attachment` | 14 | ✗ | ✗ | ✓ | ✗ |
| Noting sheet | `pur.purcaseminutes` (1,202), `hr.ctrcaseminutes` (172) | | | formal minute text `texta`…`texte`, `minute` no., `date` | | | | ✗ no actor at all | ✗ |

`gen.case_movements` is the best of the six — it is the only one with **from-holder → to-holder** and
a full actor snapshot (id, name, rank, designation). It should be the model for everything else.

### 5A.2 The number that matters

```
cases with at least one decision row:   13 of 2,004 purchase cases   (0.6 %)
cases with at least one remark row:     19 of   275 contract cases   (6.9 %)
```

So the mechanism is built, but **the trail is effectively empty**. `pur.purdecisions` and
`hr.ctrcaseremarks` are recent additions; the 2,004 purchase cases and 275 contract cases that make
up the actual history have no trail at all. For ~99% of purchase cases the system cannot answer
"kisne kisko bheja aur kya likha".

Five further problems beyond coverage:

1. **Six different shapes.** `purdecisions` uses `from_status`/`to_status`, the `fin` tables use
   `from_stage`/`to_stage`, `ctrcaseremarks` uses a single `status`, `ticket_activities` uses
   `action` + `message`. One screen cannot show all six without six queries and six mappings.
2. **"Kisko bheja" is missing in 4 of 6 modules.** Only `gen.case_movements` records the receiving
   officer. Elsewhere you can infer the stage but not the person.
3. **`hr.ctrcaseremarks` has no `acc_id`** — only `crr_username`, `crr_user_rank`, `crr_user_desig`
   as free text. Rename a designation and the historical remark becomes unverifiable.
4. **`fsh_current_holder` and `soc_current_holder` are `varchar`, not FKs.** "Case kis ke paas hai"
   is free text in those two modules.
5. **All six are ordinary mutable tables.** No trigger, no privilege restriction. A remark can be
   edited or deleted and nothing records it — which defeats the purpose of a remark being a
   record of what an officer said.

### 5A.3 What the design adds

`syslog.events` now carries the six columns added in §3.2:

| Column | Answers |
|---|---|
| `sev_from_holder_id` + `sev_from_holder` | case kis ke paas thi (id + frozen name/desig snapshot) |
| `sev_to_holder_id` + `sev_to_holder` | **kis ko bheji** |
| `sev_minute_no` | noting-sheet minute number (`pcs_minute` / `pcm_minute` / `ccm_minute`) |
| `sev_case_unt_id` | owning unit, for unit-wise registers |

Combined with what was already there — `sev_object`, `sev_objid`, `sev_objref`, `sev_stage_from`,
`sev_stage_to`, `sev_remarks`, `sev_reason_code`, `sev_authority_ref`, plus the actor snapshot and
`sev_ip` — one row now answers the whole question, **in one shape, for every module**.

The `forwarded` action is what the modules call "sent to": the application calls

```sql
SELECT syslog.begin_event(
  p_action        := 'forwarded',
  p_object        := 'purcase',
  p_objid         := '1143',
  p_objref        := 'PUR-2026-114',
  p_stage_from    := 'DD Proc',
  p_stage_to      := 'Director Log',
  p_remarks       := 'Recommended for approval. Rates verified against RFQ-882.',
  p_authority_ref := 'NRDI/LOG/114'
);
-- then one small UPDATE-free follow-up call sets the holder columns, or they are passed in
-- as additional parameters of begin_event (the real signature takes them).
```

**Nothing is deleted.** `pur.purdecisions`, `hr.ctrcaseremarks`, `gen.case_movements`,
`fin.*_decisions` and `sup.ticket_activities` keep working exactly as they do — each module's own
screens keep reading their own table. The difference is that they now also get **audited**, because
they are in `watched_tables` with `wt_snapshot_mode='always'`, so editing or deleting a remark is
itself logged. `syslog` becomes the single cross-module register on top, not a replacement.

### 5A.4 The noting-sheet view

```sql
CREATE FUNCTION syslog.case_trail(p_object varchar, p_objid varchar)
RETURNS TABLE (
  sr           bigint,
  dtg          timestamptz,
  minute_no    smallint,
  action       varchar,
  by_name      varchar,
  by_rank      varchar,
  by_desig     varchar,
  by_unit      varchar,
  on_authority varchar,
  stage_from   varchar,
  stage_to     varchar,
  sent_from    varchar,
  sent_to      varchar,
  remarks      text,
  from_ip      inet,
  result       varchar
) LANGUAGE sql STABLE AS $fn$
  SELECT row_number() OVER (ORDER BY e.sev_dtg, e.sev_id),
         e.sev_dtg, e.sev_minute_no, e.sev_action,
         e.sev_name, e.sev_rank, e.sev_desig, e.sev_untname,
         e.sev_authority_ref,
         e.sev_stage_from, e.sev_stage_to,
         e.sev_from_holder, e.sev_to_holder,
         e.sev_remarks, e.sev_ip, e.sev_result
    FROM syslog.events e
   WHERE e.sev_object = p_object
     AND e.sev_objid  = p_objid
   ORDER BY e.sev_dtg, e.sev_id;
$fn$;
```

`SELECT * FROM syslog.case_trail('purcase','1143');` produces exactly the register:

```
sr | dtg              | action    | by_desig   | stage_from | stage_to     | sent_to      | remarks
---+------------------+-----------+------------+------------+--------------+--------------+------------------------
 1 | 2026-11-02 09:14 | created   | SO Proc    |            | Initiator    |              | Case opened, PR-882
 2 | 2026-11-02 11:40 | forwarded | SO Proc    | Initiator  | DD Proc      | Maj Ahmed    | Quotes attached
 3 | 2026-11-03 15:22 | returned  | DD Proc    | DD Proc    | Initiator    | SO Proc      | Rate sheet missing
 4 | 2026-11-04 10:05 | forwarded | SO Proc    | Initiator  | DD Proc      | Maj Ahmed    | Rate sheet attached
 5 | 2026-11-05 08:30 | forwarded | DD Proc    | DD Proc    | Director Log | Brig Salman  | Recommended
 6 | 2026-11-06 12:10 | approved  | DLOG       | Director Log| Approved    |              | Approved Rs 2.4 M
```

And because every data change is also in `syslog`, the same case's **field-level** history sits
beside it — `pcs_price` 2,350,000 → 2,400,000, changed by whom, from which IP, with the remark that
accompanied it. Today those two things live in different tables with no link; here both hang off the
same `sev_id`.

Two companion views for the UI:

```sql
-- "mere paas kaunse cases hain" / pendency register
CREATE VIEW syslog.v_current_pendency AS
SELECT DISTINCT ON (sev_object, sev_objid)
       sev_object, sev_objid, sev_objref, sev_dtg AS since,
       sev_stage_to AS stage, sev_to_holder_id, sev_to_holder, sev_remarks
  FROM syslog.events
 WHERE sev_action IN ('created','forwarded','returned','reopened')
 ORDER BY sev_object, sev_objid, sev_dtg DESC;

-- "is case ne har stage pe kitna waqt liya" — bottleneck report
CREATE VIEW syslog.v_stage_duration AS
SELECT sev_object, sev_objid, sev_objref, sev_stage_to AS stage,
       sev_to_holder, sev_dtg AS entered,
       lead(sev_dtg) OVER w AS left_at,
       lead(sev_dtg) OVER w - sev_dtg AS held_for
  FROM syslog.events
 WHERE sev_action IN ('created','forwarded','returned','approved','rejected','reopened')
WINDOW w AS (PARTITION BY sev_object, sev_objid ORDER BY sev_dtg);
```

`v_stage_duration` is a side-effect worth noting: once movement is recorded uniformly, "kaunsa case
kis ke paas kitne din atka raha" becomes a one-query report. No module can produce that today.

### 5A.5 Backfill — the existing history is not lost

The six trail tables hold **259 real rows** of genuine history (48 + 75 + 107 + 346 + 15 + 4 + 14,
minus overlap) plus 1,374 noting-sheet minutes. A one-time migration maps them into
`syslog.events` with `sev_source = 'backfill'`, so the new screen is useful on day one instead of
starting empty. Example for purchase decisions:

```sql
INSERT INTO syslog.events (
    sev_dtg, sev_acc_id, sev_username, sev_name, sev_rank, sev_desig, sev_untname,
    sev_role_slug, sev_action, sev_object, sev_objid, sev_objref,
    sev_stage_from, sev_stage_to, sev_remarks, sev_source, sev_implicit)
SELECT d.created_at, d.pdec_acc_id, a.acc_username, a.acc_name, a.acc_rank, a.acc_desig,
       a.acc_untname, d.pdec_role,
       CASE lower(d.pdec_action)
         WHEN 'approve' THEN 'approved'  WHEN 'reject' THEN 'rejected'
         WHEN 'return'  THEN 'returned'  WHEN 'forward' THEN 'forwarded'
         ELSE 'updated' END,
       'purcase', d.pdec_pcs_id::varchar, c.pcs_title,
       d.pdec_from_status, d.pdec_to_status, d.pdec_remarks,
       'backfill', true
  FROM pur.purdecisions d
  LEFT JOIN cen.accounts a ON a.acc_id = d.pdec_acc_id
  LEFT JOIN pur.purcases c ON c.pcs_id = d.pdec_pcs_id
 ORDER BY d.created_at;
```

Honest limits of the backfill, which must be stated on the screen so nobody over-trusts it:

- **No IP, ever** — those tables never stored it. `sev_ip` stays `NULL`.
- **No "sent to"** — only `gen.case_movements` had it; for the rest `sev_to_holder` stays `NULL`.
- **Actor snapshot is today's, not then's** — the join to `cen.accounts` gives the *current*
  designation. For `hr.ctrcaseremarks` only the stored strings are available, and there is no
  `acc_id` to join at all.
- **It is not chain-protected for its own past.** Backfilled rows are sealed from the moment they are
  inserted onward; they are not evidence of what happened in 2024, only a faithful copy of what the
  old tables say. Hence `sev_source='backfill'` and `sev_implicit=true` — the UI must label these
  rows visibly as imported history.

### 5A.6 The three fixes this exposes in the existing schema

Independent of `syslog`, the discovery above found three things worth fixing in the business tables
themselves:

1. **`hr.ctrcaseremarks` has no `crr_acc_id`.** Add the column and an FK to `cen.accounts`,
   backfilled from `crr_username` where it resolves. Without it, contract-case remarks can never be
   tied to a verified identity.
2. **`fin.fund_shifts.fsh_current_holder` and `fin.sal_order_cases.soc_current_holder` are
   `varchar`.** Add `*_holder_id integer REFERENCES cen.accounts(acc_id)` alongside, as
   `gen.cases.gcs_current_holder_id` already does correctly.
3. **`pur.purcases` and `hr.ctrcases` have no current-holder column at all.** Pendency is derived
   from `*_substatus.stage`, which says *where* the case is but not *with whom*. Adding
   `pcs_current_holder_id` / `ctc_current_holder_id` would make the two biggest modules match the
   generic-case design — and would make `syslog.v_current_pendency` agree with the live tables
   instead of being the only source of that answer.

These are recommendations, not part of the audit schema. They can be done before, after, or never —
`syslog` works either way, it just records less for the modules that store less.

---

## 5B. Case snapshot at every handover

**Requirement:** jab case ek user se dusre ko jati hai, us lamhe ka **poora case ka snapshot** mehfooz
ho — taake baad mein dekha ja sake "jab ye case Maj Ahmed ke paas pohanchi to kya thi, aur jab unhone
aage bheji to kya thi". Yani har custody change pe case ki point-in-time state.

### 5B.1 First problem: "poora case" ek row nahi hai

A purchase case is spread over **eight tables, two levels deep** — verified from the foreign keys in
`rdw04`:

```
pur.purcases                (header, 1 row)
├── pur.purcaseitems        (pci_pcs_id)        avg 7.7 rows,  max 291
├── pur.purcases_shd        (pcd_pcs_id)        avg 1.0 rows,  max 2
├── pur.purcase_substatus   (pss_pcs_id)        stage history
├── pur.quotes              (qte_pcs_id)        avg 2.7 rows,  max 6
│   └── pur.quoteitems      (qti_qte_id)        avg 9.3 per quote, max 126
├── pur.noquotes            (nqt_pcs_id)
├── pur.pur_it_letters      (pit_pcs_id)
├── pur.purdecisions        (pdec_pcs_id — logical link, no FK)
├── pur.purattachments      (pat_objtype='pcs', pat_objid — polymorphic, no FK)  avg 2.6, max 5
└── pur.purcaseminutes      (linked by pcm_purcases varchar — see the warning below)
```

Contract case: `hr.ctrcases` + `ctrcase_substatus`, `ctrcaseattachments`, `ctrcaseplans`,
`ctrcaseremarks`, and `ctrcaseminutes` via `ctrcaseminuterefs`.
Generic case: `gen.cases` + `case_attachments`, `case_movements`, `case_notes`.

So a snapshot is not `to_jsonb(NEW)` of one row — it is a **composed document**. That composition has
to be declared somewhere, which is the next table.

> **Warning found while tracing this.** `pur.purcaseminutes` links to cases through
> `pcm_purcases character varying` with a UNIQUE constraint on it — not an integer FK. It appears to
> hold a case-id *list* as text. That cannot be joined reliably, so the minute sheet is listed in the
> composition as "best effort" and flagged in §5B.8. Worth fixing independently.

### 5B.2 `syslog.case_composition` — declaring what makes up a case

```sql
CREATE TABLE syslog.case_composition (
  cc_object    varchar(60)  NOT NULL,   -- 'purcase'
  cc_seq       smallint     NOT NULL,   -- order in the snapshot document
  cc_part      varchar(40)  NOT NULL,   -- 'items' | 'quotes' | 'attachments'
  cc_schema    varchar(30)  NOT NULL,
  cc_table     varchar(60)  NOT NULL,
  cc_fk_col    varchar(60),             -- column that points at the parent
  cc_parent    varchar(40),             -- NULL = hangs off the case header;
                                        -- 'quotes' = nested under the quotes part
  cc_parent_key varchar(60),            -- parent's key column for the nested join
  cc_filter    text,                    -- extra predicate, e.g. "pat_objtype = 'pcs'"
  cc_order_by  varchar(120),
  cc_enabled   boolean      NOT NULL DEFAULT true,
  cc_note      text,
  CONSTRAINT case_composition_pk PRIMARY KEY (cc_object, cc_seq)
);

INSERT INTO syslog.case_composition
  (cc_object, cc_seq, cc_part, cc_schema, cc_table, cc_fk_col, cc_parent, cc_parent_key, cc_filter, cc_order_by) VALUES
('purcase', 1,'items',      'pur','purcaseitems',     'pci_pcs_id', NULL,     NULL,      NULL,                    'pci_serial'),
('purcase', 2,'subheads',   'pur','purcases_shd',     'pcd_pcs_id', NULL,     NULL,      NULL,                    'pcd_subhead'),
('purcase', 3,'stages',     'pur','purcase_substatus','pss_pcs_id', NULL,     NULL,      NULL,                    'pss_since'),
('purcase', 4,'quotes',     'pur','quotes',           'qte_pcs_id', NULL,     NULL,      NULL,                    'qte_id'),
('purcase', 5,'quoteitems', 'pur','quoteitems',       'qti_qte_id', 'quotes', 'qte_id',  NULL,                    'qti_serial'),
('purcase', 6,'noquotes',   'pur','noquotes',         'nqt_pcs_id', NULL,     NULL,      NULL,                    'nqt_id'),
('purcase', 7,'letters',    'pur','pur_it_letters',   'pit_pcs_id', NULL,     NULL,      NULL,                    'pit_id'),
('purcase', 8,'decisions',  'pur','purdecisions',     'pdec_pcs_id',NULL,     NULL,      NULL,                    'pdec_id'),
('purcase', 9,'attachments','pur','purattachments',   'pat_objid',  NULL,     NULL,      'pat_objtype = ''pcs''', 'pat_id'),
('ctrcase', 1,'stages',     'hr','ctrcase_substatus', 'css_ctc_id', NULL,     NULL,      NULL,                    'css_since'),
('ctrcase', 2,'plans',      'hr','ctrcaseplans',      'ccp_ctc_id', NULL,     NULL,      NULL,                    'ccp_id'),
('ctrcase', 3,'remarks',    'hr','ctrcaseremarks',    'crr_ctc_id', NULL,     NULL,      NULL,                    'crr_dtg'),
('ctrcase', 4,'attachments','hr','ctrcaseattachments','cat_ctc_id', NULL,     NULL,      NULL,                    'cat_id'),
('gencase', 1,'movements',  'gen','case_movements',   'gcm_case_id',NULL,     NULL,      NULL,                    'gcm_dtg'),
('gencase', 2,'notes',      'gen','case_notes',       'gcn_case_id',NULL,     NULL,      NULL,                    'gcn_id'),
('gencase', 3,'attachments','gen','case_attachments', 'gca_case_id',NULL,     NULL,      NULL,                    'gca_id');
```

Adding a module later is one `INSERT` here — no code change, because the snapshot function is generic
and reads this table.

### 5B.3 `syslog.case_snapshots`

```sql
CREATE TABLE syslog.case_snapshots (
  cs_id          bigint GENERATED ALWAYS AS IDENTITY,
  cs_dtg         timestamptz NOT NULL,
  cs_sev_id      bigint      NOT NULL,    -- the handover event that caused this snapshot
  cs_object      varchar(60) NOT NULL,    -- 'purcase'
  cs_objid       varchar(100) NOT NULL,   -- '1143'
  cs_objref      varchar(200),            -- 'PUR-2026-114'
  cs_seq         integer     NOT NULL,    -- 1,2,3... per case: the handover serial

  ------------------------------------------------- custody at this instant
  cs_stage       varchar(60),
  cs_holder_id   integer,                 -- kis ke paas aa rahi hai
  cs_holder      varchar(255),
  cs_prev_holder_id integer,              -- kis ke paas se aa rahi hai
  cs_prev_holder varchar(255),
  cs_reason      varchar(40),             -- forwarded | returned | approved | ...
  cs_remarks     text,

  ------------------------------------------------- the snapshot itself
  cs_doc         jsonb,                   -- the composed case document
  cs_doc_hash    char(64)    NOT NULL,    -- sha256 of the canonical document
  cs_same_as     bigint,                  -- de-dup: points at an earlier cs_id with the
                                          -- identical hash; cs_doc is then NULL
  cs_row_counts  jsonb       NOT NULL,    -- {"items":7,"quotes":3,"quoteitems":28,...}
  cs_bytes       integer     NOT NULL,    -- uncompressed document size
  cs_files       jsonb,                   -- attachment manifest: path, size, sha256, mtime
  cs_partial     boolean     NOT NULL DEFAULT false,
  cs_partial_why text,                    -- e.g. 'purcaseminutes link unresolvable'

  CONSTRAINT case_snapshots_pk PRIMARY KEY (cs_dtg, cs_id),
  CONSTRAINT case_snapshots_fk FOREIGN KEY (cs_dtg, cs_sev_id)
      REFERENCES syslog.events (sev_dtg, sev_id)
) PARTITION BY RANGE (cs_dtg);

CREATE INDEX idx_cs_case  ON syslog.case_snapshots (cs_object, cs_objid, cs_seq);
CREATE INDEX idx_cs_event ON syslog.case_snapshots (cs_sev_id);
CREATE INDEX idx_cs_hash  ON syslog.case_snapshots (cs_object, cs_objid, cs_doc_hash);
CREATE INDEX idx_cs_holder ON syslog.case_snapshots (cs_holder_id, cs_dtg DESC);

-- JSONB compresses well here; lz4 is faster than the default pglz at similar ratio
ALTER TABLE syslog.case_snapshots ALTER COLUMN cs_doc SET COMPRESSION lz4;

-- append-only, like every other syslog table
CREATE TRIGGER aa_immutable BEFORE UPDATE OR DELETE ON syslog.case_snapshots
  FOR EACH ROW EXECUTE FUNCTION syslog.fn_deny_change();
ALTER TABLE syslog.case_snapshots ENABLE ALWAYS TRIGGER aa_immutable;
```

### 5B.4 One snapshot per handover serves both sides

A point worth being explicit about, because it halves the storage and removes all ambiguity:

**The snapshot taken at handover N is simultaneously "what the sender forwarded" and "what the
receiver received".** They are the same instant. So the system does not need an "as received" and an
"as forwarded" snapshot per person — it needs one snapshot per movement.

```
snapshot #2  ──  SO Proc ne forward ki  ──►  Maj Ahmed ko mili
                        (jo Maj Ahmed ko mila = snapshot #2)
      ... Maj Ahmed ke paas rahi, unhone kuch tabdeeliyan ki ...
snapshot #3  ──  Maj Ahmed ne forward ki  ──►  Brig Salman ko mili

diff(#2, #3)  =  Maj Ahmed ne apni custody mein kya kiya
```

So **"Maj Ahmed ne kya kiya"** is `snapshot_diff(2, 3)` — and that is the question that actually gets
asked in a review. It is not stored; it is computed, which means it can never disagree with the
snapshots.

### 5B.5 `syslog.take_case_snapshot()`

```sql
CREATE FUNCTION syslog.take_case_snapshot(
    p_sev_id bigint, p_object varchar, p_objid varchar
) RETURNS bigint
LANGUAGE plpgsql SECURITY DEFINER SET search_path = syslog, pg_temp AS $fn$
DECLARE
  v_e      syslog.events;
  v_doc    jsonb := '{}'::jsonb;
  v_part   record;
  v_sql    text;
  v_rows   jsonb;
  v_counts jsonb := '{}'::jsonb;
  v_hash   char(64);
  v_prev   bigint;
  v_seq    integer;
  v_id     bigint;
BEGIN
  SELECT * INTO v_e FROM syslog.events WHERE sev_id = p_sev_id;

  -- 1. the header row
  SELECT w.wt_schema, w.wt_table, w.wt_pk_cols INTO v_part
    FROM syslog.watched_tables w WHERE w.wt_object = p_object AND w.wt_pk_cols IS NOT NULL LIMIT 1;
  EXECUTE format('SELECT to_jsonb(t) FROM %I.%I t WHERE %I = $1',
                 v_part.wt_schema, v_part.wt_table, v_part.wt_pk_cols[1])
     INTO v_rows USING p_objid;
  v_doc := jsonb_build_object('header', v_rows);

  -- 2. each declared part, in cc_seq order, redacting as watched_tables says
  FOR v_part IN
      SELECT * FROM syslog.case_composition
       WHERE cc_object = p_object AND cc_enabled ORDER BY cc_seq
  LOOP
     IF v_part.cc_parent IS NULL THEN
        v_sql := format('SELECT jsonb_agg(to_jsonb(t) ORDER BY %s) FROM %I.%I t WHERE t.%I = $1 %s',
                        coalesce(v_part.cc_order_by, '1'), v_part.cc_schema, v_part.cc_table,
                        v_part.cc_fk_col,
                        CASE WHEN v_part.cc_filter IS NOT NULL
                             THEN ' AND ' || v_part.cc_filter ELSE '' END);
        EXECUTE v_sql INTO v_rows USING p_objid;
     ELSE
        -- nested part (quoteitems under quotes): join through the parent part
        v_sql := format($q$SELECT jsonb_agg(to_jsonb(t) ORDER BY %s)
                             FROM %I.%I t
                            WHERE t.%I IN (SELECT (x->>%L)::text::int
                                             FROM jsonb_array_elements($2->%L) x)$q$,
                        coalesce(v_part.cc_order_by,'1'), v_part.cc_schema, v_part.cc_table,
                        v_part.cc_fk_col, v_part.cc_parent_key, v_part.cc_parent);
        EXECUTE v_sql INTO v_rows USING p_objid, v_doc;
     END IF;

     v_doc    := jsonb_set(v_doc, ARRAY[v_part.cc_part], coalesce(v_rows, '[]'::jsonb));
     v_counts := v_counts || jsonb_build_object(v_part.cc_part,
                              coalesce(jsonb_array_length(v_rows), 0));
  END LOOP;

  v_hash := encode(sha256(convert_to(v_doc::text, 'UTF8')), 'hex');

  -- 3. de-duplicate: unchanged case => store a pointer, not the document again
  SELECT cs_id INTO v_prev FROM syslog.case_snapshots
   WHERE cs_object = p_object AND cs_objid = p_objid AND cs_doc_hash = v_hash
     AND cs_same_as IS NULL
   ORDER BY cs_id LIMIT 1;

  SELECT coalesce(max(cs_seq), 0) + 1 INTO v_seq FROM syslog.case_snapshots
   WHERE cs_object = p_object AND cs_objid = p_objid;

  INSERT INTO syslog.case_snapshots (
      cs_dtg, cs_sev_id, cs_object, cs_objid, cs_objref, cs_seq,
      cs_stage, cs_holder_id, cs_holder, cs_prev_holder_id, cs_prev_holder,
      cs_reason, cs_remarks,
      cs_doc, cs_doc_hash, cs_same_as, cs_row_counts, cs_bytes)
  VALUES (
      v_e.sev_dtg, p_sev_id, p_object, p_objid, v_e.sev_objref, v_seq,
      v_e.sev_stage_to, v_e.sev_to_holder_id, v_e.sev_to_holder,
      v_e.sev_from_holder_id, v_e.sev_from_holder,
      v_e.sev_action, v_e.sev_remarks,
      CASE WHEN v_prev IS NULL THEN v_doc END,      -- NULL when de-duplicated
      v_hash, v_prev, v_counts, octet_length(v_doc::text))
  RETURNING cs_id INTO v_id;

  RETURN v_id;
END $fn$;
```

Called **in the same transaction as the handover**, so a movement can never exist without its
snapshot:

```sql
SELECT syslog.begin_event(
  p_action := 'forwarded', p_object := 'purcase', p_objid := '1143',
  p_stage_from := 'DD Proc', p_stage_to := 'Director Log',
  p_remarks := 'Recommended for approval',
  p_snapshot := true          -- begin_event calls take_case_snapshot() before returning
);
```

### 5B.6 Attachments — manifest, not bytes

`storage/app` is currently **7.5 GB**. Copying files into a snapshot is out of the question, and
`pur.purattachments` only stores a path anyway (`pat_path`, e.g. `\pur\min-pcs-231.pdf`).

So `cs_files` holds a **manifest**, written by the application (PHP has the filesystem; PostgreSQL
does not):

```json
[{"part":"attachments","type":"Minute","path":"/pur/min-pcs-231.pdf",
  "bytes":284416,"sha256":"9f2c…","mtime":"2026-11-02T11:38:52Z","exists":true}]
```

What this gives you: if the PDF attached at handover #2 is later replaced with a different file at the
same path, the hash at #2 and #3 differ, so the substitution is **provable** — even though the old
bytes are gone. If the file is deleted, `exists:false` at the next snapshot shows when.

What it does not give you: the old file back. For that, attachments would need content-addressed
storage (file stored under its own hash, never overwritten) — a separate, bigger decision, noted in
§5B.9 as question 17.

### 5B.7 `syslog.snapshot_diff()` — "is user ne kya kiya"

```sql
CREATE FUNCTION syslog.snapshot_diff(p_from bigint, p_to bigint)
RETURNS TABLE (part varchar, row_key text, change varchar, column_name text,
               old_value text, new_value text)
LANGUAGE plpgsql STABLE AS $fn$
DECLARE a jsonb; b jsonb;
BEGIN
  SELECT syslog.snapshot_doc(p_from) INTO a;   -- resolves cs_same_as pointers
  SELECT syslog.snapshot_doc(p_to)   INTO b;

  -- header: column-by-column
  RETURN QUERY
  SELECT 'header'::varchar, NULL::text, 'modified'::varchar, k.key,
         (a->'header'->>k.key), (b->'header'->>k.key)
    FROM jsonb_object_keys(b->'header') k(key)
   WHERE (a->'header'->k.key) IS DISTINCT FROM (b->'header'->k.key);

  -- child parts: rows added, removed, modified (matched on their primary key)
  -- ... (one generic block per part, keyed from case_composition + watched_tables.wt_pk_cols)
END $fn$;
```

Output reads like a review note:

```
part        | row_key          | change   | column        | old       | new
------------+------------------+----------+---------------+-----------+-----------
header      |                  | modified | pcs_price     | 2350000   | 2400000
header      |                  | modified | pcs_recomm    | (null)    | Approved at mid rate
items       | serial=4         | added    |               |           |
items       | serial=2         | modified | pci_qty       | 10        | 12
quotes      | qte_id=881       | removed  |               |           |
attachments | pat_id=4120      | added    |               |           | /pur/fs-pcs-1143.pdf
```

So: *"case Maj Ahmed ke paas 3 din rahi; unhone rate 23.5 lakh se 24 lakh kiya, ek item barha, aik
quote hataya, aur financial-status attach kiya — phir Brig Salman ko bheji."* One function call.

Related helpers:

```sql
-- "us waqt ka case" — reconstruct the full document for any handover
SELECT syslog.snapshot_doc(cs_id) FROM syslog.case_snapshots
 WHERE cs_object='purcase' AND cs_objid='1143' AND cs_seq=2;

-- custody register with what changed in each hand
CREATE VIEW syslog.v_case_custody AS
SELECT cs_object, cs_objid, cs_objref, cs_seq,
       cs_prev_holder AS received_from, cs_holder AS held_by, cs_stage,
       cs_dtg AS received_at,
       lead(cs_dtg) OVER w - cs_dtg AS held_for,
       lead(cs_holder) OVER w AS forwarded_to,
       lead(cs_reason) OVER w AS how_it_left,
       (cs_doc_hash <> lead(cs_doc_hash) OVER w) AS changed_anything,
       cs_row_counts
  FROM syslog.case_snapshots
WINDOW w AS (PARTITION BY cs_object, cs_objid ORDER BY cs_seq);
```

`changed_anything` is the cheap tell: a `false` means that officer held the case and altered nothing —
answerable from one hash comparison, without reading either document.

### 5B.8 Sizing — measured, not estimated

Built the real composed document for **all 2,004 purchase cases** in `rdw02` and measured it:

```
cases   avg document   max document   all 2,004 at once
2,004   7,410 bytes    139 kB         14 MB
```

Fan-out behind those numbers: 7.7 items (max 291), 2.7 quotes (max 6) × 9.3 quote-items (max 126),
1.0 subhead, 2.6 attachments (max 5).

| | Calculation | Result |
|---|---|---|
| Avg document, stored as `jsonb` + lz4 | 7.4 KB × ~0.4 | **~3 KB** |
| One case's full life (assume 8 handovers) | 8 × 3 KB | ~24 KB |
| New cases per year (≈500 across all modules, from 2,004 purchase cases over the ~5 years `aud.busdata` spans) | 500 × 24 KB | **~12 MB / year** |
| With hash de-duplication (§5B.5) — forwards that change nothing store a pointer | ×~0.6 | **~7 MB / year** |
| One-time backfill: current state of every existing case, one snapshot each | 2,004 × 3 KB + contract/generic | **~8 MB once** |
| Worst single case (291 items, 139 KB × 8) | | ~1.1 MB |

**Conclusion: snapshots add roughly 7–12 MB per year** on top of the ~350 MB/year from §7. The feature
is essentially free at this system's scale. The database is 53 MB today.

Cost on the write path: composing ~35 rows into JSONB, hashing it, one insert — single-digit
milliseconds, and only on handover events (a handful per case), not on every edit.

Two guards worth adding anyway:

```sql
-- alert if any snapshot exceeds 1 MB — usually means a composition part is mis-joined
SELECT cs_object, cs_objid, cs_seq, pg_size_pretty(cs_bytes::bigint)
  FROM syslog.case_snapshots WHERE cs_bytes > 1048576 ORDER BY cs_bytes DESC;

-- monthly: how much of the snapshot table is de-duplicated pointers
SELECT count(*) total, count(cs_same_as) deduped,
       pg_size_pretty(sum(cs_bytes)::bigint) AS logical_size
  FROM syslog.case_snapshots;
```

### 5B.9 Honest limits

1. **Snapshots give net state; `field_changes` gives every step.** If an officer changes
   `pcs_price` to 30 lakh and back to 23.5 lakh before forwarding, `snapshot_diff` shows **no
   change** — while `syslog.field_changes` shows both edits with timestamps. The two are
   complementary and the UI must show both; the snapshot tab alone can mislead.
2. **It is a snapshot of the database, not of the screen.** Computed totals, PDF renderings and
   anything assembled in PHP at display time are not in it. If "us waqt ka case print karo" must
   reproduce the printed sheet exactly, the renderer has to be driven from `snapshot_doc()` rather
   than from live tables — which is a real piece of work and is listed as phase 16.
3. **Attachment bytes are not kept** (§5B.6) — only path, size and hash. Substitution is provable,
   recovery is not.
4. **`pur.purcaseminutes` cannot be joined reliably** because of the `pcm_purcases varchar` link, so
   the minute sheet is marked `cs_partial = true` with a reason until that link is fixed.
5. **Backfilled snapshots are "now", not "then".** The one-time backfill captures each existing
   case's *current* state as snapshot #1. It is not the state at any past handover — that data does
   not exist anywhere and cannot be reconstructed. Label them clearly, same as §5A.5.
6. **A snapshot is only as trustworthy as `case_composition`.** If a module adds a child table and
   nobody adds the row here, snapshots silently stop including it. Covered by the drift check in
   §8.4, extended to `case_composition`:

```sql
-- child tables with an FK to a case table that are NOT in case_composition
SELECT con.conrelid::regclass AS missing_from_composition
  FROM pg_constraint con
 WHERE con.contype = 'f'
   AND con.confrelid IN ('pur.purcases'::regclass,'hr.ctrcases'::regclass,'gen.cases'::regclass)
   AND con.conrelid::regclass::text NOT IN (
       SELECT cc_schema || '.' || cc_table FROM syslog.case_composition WHERE cc_enabled);
```

---

## 5C. File uploads — path, original name, kisne kab upload ki

### 5C.1 What exists today

Checked all seven attachment tables in `rdw04`. **Six of them store nothing but a path.**

| Table | Columns | Original filename? | Size / MIME? | Uploader? | When? |
|---|---|---|---|---|---|
| `pur.purattachments` | `pat_objtype, pat_objid, pat_type, pat_path, pat_id` | ✗ | ✗ | ✗ | ✗ |
| `hr.ctrcaseattachments` | `cat_objtype, cat_objid, cat_type, cat_path, cat_id` | ✗ | ✗ | ✗ | ✗ |
| `hr.empattachments` | `eat_objtype, eat_objid, eat_type, eat_path, eat_id` | ✗ | ✗ | ✗ | ✗ |
| `prj.prjattachments` | `jat_objtype, jat_objid, jat_type, jat_path, jat_id` | ✗ | ✗ | ✗ | ✗ |
| `ina.inaattachments` | `iat_objtype, iat_objid, iat_type, iat_path, iat_id` | ✗ | ✗ | ✗ | ✗ |
| `aud.audattachments` | `aat_objtype, aat_objid, aat_type, aat_path, aat_id` | ✗ | ✗ | ✗ | ✗ |
| **`gen.case_attachments`** | `gca_title, gca_filename, gca_path, gca_size, gca_mime, gca_uploader_id, created_at` | **✓** | **✓** | **✓** | **✓** |

And the stored paths are **generated names**, not the user's:

```
Minute            | \pur\min-pcs-231.pdf
Financial Status  | \pur\fs-pcs-89.pdf
Financial Status  | \pur\fs-pcs-143.pdf
```

So the file the officer actually uploaded — `Scan_Minute_Sheet_Case114_signed.pdf` — **its name is gone
the moment it is saved**. It exists nowhere in the database. `gen.case_attachments` (the newest
module) does it correctly and is the model for the rest.

`storage/app` is **7.5 GB**, and nothing records who uploaded any of it, when, or who has downloaded
it since.

### 5C.2 `syslog.file_events`

Uploads, downloads and deletions each get an `events` row (`uploaded` / `downloaded` /
`deleted_file`) plus one child row here. A separate table rather than extra `events` columns, because
the natural queries are file-shaped: *"is file ka kya hua"*, *"ye PDF kis kis ne download ki"*,
*"kaunsi do files ek hi hain"* (same hash, different name).

```sql
CREATE TABLE syslog.file_events (
  fe_id         bigint GENERATED ALWAYS AS IDENTITY,
  fe_dtg        timestamptz  NOT NULL,
  fe_sev_id     bigint       NOT NULL,

  fe_action     varchar(20)  NOT NULL,   -- uploaded | replaced | downloaded | deleted | moved | viewed

  ------------------------------------------------- the two names, kept apart
  fe_orig_name  varchar(500),            -- EXACTLY what the user's file was called
  fe_stored_path varchar(1000),          -- what the system saved it as: /pur/min-pcs-231.pdf
  fe_disk       varchar(40),             -- 'private' | 'public'  (storage/app/...)

  ------------------------------------------------- identity of the content
  fe_bytes      bigint,
  fe_mime       varchar(120),
  fe_ext        varchar(20),
  fe_sha256     char(64),                -- same file re-uploaded under a new name is detectable

  ------------------------------------------------- what it is attached to
  fe_object     varchar(60),             -- purcase | ctrcase | employee | project | gencase
  fe_objid      varchar(100),
  fe_objref     varchar(200),
  fe_attach_tbl varchar(60),             -- 'pur.purattachments'
  fe_attach_id  varchar(60),             -- pat_id
  fe_attach_type varchar(120),           -- pat_type: 'Minute' / 'Financial Status'

  ------------------------------------------------- replacement chain
  fe_replaces_fe_id bigint,              -- previous file at the same path
  fe_prev_sha256    char(64),

  fe_result     varchar(20)  NOT NULL DEFAULT 'ok',   -- ok | failed | rejected | virus
  fe_note       text,

  CONSTRAINT file_events_pk PRIMARY KEY (fe_dtg, fe_id),
  CONSTRAINT file_events_fk FOREIGN KEY (fe_dtg, fe_sev_id)
      REFERENCES syslog.events (sev_dtg, sev_id)
) PARTITION BY RANGE (fe_dtg);

CREATE INDEX idx_fe_obj   ON syslog.file_events (fe_object, fe_objid, fe_dtg DESC);
CREATE INDEX idx_fe_path  ON syslog.file_events (fe_stored_path, fe_dtg DESC);
CREATE INDEX idx_fe_hash  ON syslog.file_events (fe_sha256);
CREATE INDEX idx_fe_name  ON syslog.file_events (lower(fe_orig_name));
CREATE INDEX idx_fe_dl    ON syslog.file_events (fe_dtg DESC) WHERE fe_action = 'downloaded';

CREATE TRIGGER aa_immutable BEFORE UPDATE OR DELETE ON syslog.file_events
  FOR EACH ROW EXECUTE FUNCTION syslog.fn_deny_change();
ALTER TABLE syslog.file_events ENABLE ALWAYS TRIGGER aa_immutable;
```

**This must be written from PHP, not from a trigger.** The database never sees the original filename
or the file bytes — only the application does. So the upload path calls:

```php
Audit::file('uploaded', [
    'orig_name'   => $request->file('doc')->getClientOriginalName(),   // the user's name
    'stored_path' => $storedPath,
    'bytes'       => $request->file('doc')->getSize(),
    'mime'        => $request->file('doc')->getMimeType(),
    'sha256'      => hash_file('sha256', $request->file('doc')->getRealPath()),
    'object'      => 'purcase', 'objid' => $case->pcs_id,
    'attach_tbl'  => 'pur.purattachments', 'attach_type' => 'Minute',
]);
```

The `pur.purattachments` row insert is still logged by the trigger as usual — so the two halves
(what the DB stored, what the user actually sent) sit under the same `sev_id`.

### 5C.3 What this makes answerable

```sql
-- "is case ki saari files, asli naam ke sath, kisne kab upload ki"
SELECT f.fe_dtg, e.sev_name, e.sev_desig, e.sev_ip,
       f.fe_action, f.fe_attach_type, f.fe_orig_name, f.fe_stored_path,
       pg_size_pretty(f.fe_bytes), f.fe_sha256
  FROM syslog.file_events f JOIN syslog.events e ON e.sev_id = f.fe_sev_id
 WHERE f.fe_object = 'purcase' AND f.fe_objid = '1143'
 ORDER BY f.fe_dtg;

-- "ye file kis kis ne download ki" — 7.5 GB of attachments, currently nobody knows
SELECT e.sev_dtg, e.sev_name, e.sev_desig, e.sev_untname, e.sev_ip
  FROM syslog.file_events f JOIN syslog.events e ON e.sev_id = f.fe_sev_id
 WHERE f.fe_stored_path = '/pur/min-pcs-231.pdf' AND f.fe_action = 'downloaded'
 ORDER BY e.sev_dtg DESC;

-- "kaunsi file chupke se badal di gayi" — same path, different hash
SELECT fe_stored_path, fe_dtg, fe_orig_name, fe_prev_sha256, fe_sha256, fe_note
  FROM syslog.file_events WHERE fe_action = 'replaced' ORDER BY fe_dtg DESC;

-- "ek hi file do jagah alag naam se" — same content, different names
SELECT fe_sha256, count(DISTINCT fe_orig_name) names, array_agg(DISTINCT fe_orig_name)
  FROM syslog.file_events WHERE fe_action = 'uploaded'
 GROUP BY 1 HAVING count(DISTINCT fe_orig_name) > 1;
```

### 5C.4 Recommended fix to the six legacy attachment tables

Separate from the audit trail, and worth doing on its own: give the six tables the shape
`gen.case_attachments` already has.

```sql
ALTER TABLE pur.purattachments
  ADD COLUMN pat_origname    varchar(500),
  ADD COLUMN pat_bytes       bigint,
  ADD COLUMN pat_mime        varchar(120),
  ADD COLUMN pat_sha256      char(64),
  ADD COLUMN pat_uploader_id integer REFERENCES cen.accounts(acc_id),
  ADD COLUMN pat_dtg         timestamptz DEFAULT now();
-- same for ctrcaseattachments, empattachments, prjattachments, inaattachments, audattachments
```

Without this, the module screens still cannot show the original filename — `syslog` would know it but
the case screen would not. **Existing 7.5 GB of files cannot be backfilled**: their original names
were never stored anywhere and are unrecoverable. Size and hash *can* be backfilled by walking the
disk; the name cannot. That is a permanent gap in the historical record, and the sooner the columns
exist, the smaller it stays.

---

## 5D. Business event catalogue — project, paisa, account, head, employee, salary

Everything in this section is a logged event in the design. This is the catalogue that turns raw
`created / updated / deleted` rows into language an officer reads.

### 5D.1 First, the uncomfortable finding

Searched every business table for a column that records *who* acted. **Nine tables out of 122 do:**

```
doc.documents.creator_id / current_owner_id   doc.document_versions.action_by
doc.document_history.from_user_id / to_user_id
fin.fund_shifts.fsh_created_by / fsh_implemented_by
fin.sal_order_cases.soc_created_by            gen.cases.gcs_created_by
hr.ctrcases.ctc_createdby / ctc_releasedby    puritems.rfqs.rfq_created_by
sup.tickets.tkt_user_id / tkt_solved_by       sup.ticket_activities.act_user_id
```

They all belong to recently-built modules. The tables that carry the actual institutional record have
**neither a creator nor a record timestamp**:

| Table | What it holds | `created_by` | `created_at` | What it does have |
|---|---|---|---|---|
| `prj.projects` | the project itself | ✗ | ✗ | `prj_startdt`, `prj_propdt`, `prj_aprvdt`, `prj_enddt` — *business* dates, not record dates |
| `cen.heads` | **head account** | ✗ | ✗ | `hed_opendt`, `hed_closedt` — dates only, no actor |
| `cen.accounts` | user accounts | ✗ | ✗ | `acc_startdt`, `acc_enddt`, `acc_status` — dates only, no actor |
| `hr.emps` | employees | ✗ | ✗ | `emp_joindt`, `emp_lastdt`, `emp_status`, `emp_locked`, `emp_cleared` |
| `fin.subheads` | **allocation values** | ✗ | ✗ | `sbh_alloc` — current value only, **no history at all** |
| `fin.commitments` | money committed | ✗ | ✗ | `cmt_date`, `cmt_status`, `cmt_amount` |
| `fin.transactions` | money paid out | ✗ | ✗ | `trn_date`, `trn_amount1`, `trn_balance` |
| `fin.transfers` | money moved | ✗ | ✗ | `trf_fromhed → trf_tohed`, `trf_fromunt → trf_tount`, `trf_status` |
| `fin.salorders` | salary orders | ✗ | ✗ | `sor_releasedtg`, `sor_closedtg` |
| `hr.salreqs` | salary requests | ✗ | ✗ | `srq_releasedtg`, `srq_closedtg` |
| `pur.purcases` | purchase cases | ✗ | ✗ | `pcs_date`, `pcs_releasedtg`, `pcs_approvedtg`, `pcs_closedtg` |

So today, for every question in this section, the answer is the same: **the event happened, the
resulting value is visible, and who did it is nowhere.** `fin.subheads.sbh_alloc` is the sharpest
example — an allocation figure can be changed from 50 lakh to 80 lakh and the database keeps only
"80 lakh". There is no before-value, no actor, no timestamp.

One table does it right and is worth copying: **`fin.empeffheads`** — `eeh_emp_id, eeh_emphed_id,
eeh_dtg, eeh_status, eeh_remarks`. It is an append-style history of which head an employee is
charged to, with timestamps. It still has no actor, but the shape is correct.

> **What `syslog` fixes, and what it cannot.** From the day the triggers go live, every one of these
> gets a full record — actor, authority, IP, before-value, after-value — automatically, without
> adding a single column to the business tables. What it **cannot** do is recover the past: the
> 2,004 purchase cases, 2,000-odd commitments and every existing head account have no creator
> recorded anywhere, and no amount of design makes that information appear. §5A.5 backfill brings in
> what the old tables do hold; the rest is simply absent.

### 5D.2 Group A — Project lifecycle

| Event | `sev_action` | `sev_object` | Trigger point | Captured |
|---|---|---|---|---|
| Project daala gaya | `created` | `project` | `prj.projects` INSERT | full row, actor, IP, unit |
| Proposed cost / approved cost badli | `updated` | `project` | `prj.projects` UPDATE | `prj_propcost`, `prj_aprvcost`, `prj_cfycost` before → after |
| Status badla | `updated` | `project` | `prj.projects.prj_status` | old → new + remarks |
| Dates badli | `updated` | `project` | `prj_startdt`, `prj_aprvdt`, `prj_estenddt`, `prj_enddt` | each field separately |
| Project band hua | `updated` | `project` | `prj_status` + `prj_enddt` | closed by whom, on what authority |
| Scope / sponsor / notes badle | `updated` | `project` | `prj_scope`, `prj_sponsor`, `prj_rem`, `prj_notes` | full text before → after |
| Milestone add / change / delete | `created`/`updated`/`deleted` | `project` | `prj.milestones` | with `msn_idd`, full row on delete |
| Progress likha gaya | `created` | `project` | `prj.prghistory` (1,385 rows today) | author, text, status, level |
| Head account project se link hua | `updated` | `head` | `cen.heads.hed_prj_id` | old head → new head |
| Project unit badli | `updated` | `project` | `prj.projects.prj_unt_id` | **kis unit se kis unit ko gaya** |
| Document project se juda | `created` | `document` | `doc.documents.prj_id` | owner, creator, type, ref no |
| File attach hui | `uploaded` | `project` | `prj.prjattachments` + §5C | **asli filename**, size, hash, uploader |

### 5D.3 Group B — Paisa: head account, allocation, commitment, transaction, transfer

This is the chain the user described as *"kab paisa aya, kab nikla, kahan gaya"*. In this schema the
money flows through five tables, and every step becomes an event:

```
cen.heads          head account khula / band hua          (hed_opendt / hed_closedt)
   └── fin.subheads        allocation value set/changed   (sbh_alloc)        ← NO history today
        └── fin.commitments   paisa commit hua            (cmt_amount, cmt_status)
             └── fin.transactions  paisa nikla            (trn_amount1, trn_balance)
        fin.transfers        head se head paisa gaya      (trf_fromhed → trf_tohed)
        fin.sharesalloc / sharesinstall   share distribute hua (cf / pcc / prj / prj_sal / prj_pur)
        fin.fund_shifts      fund shifting case           (already has created_by + decisions)
```

| Event | `sev_action` | `sev_object` | Source | Captured |
|---|---|---|---|---|
| **Head account khola** | `created` | `head` | `cen.heads` INSERT | name, type, code, `hed_opendt`, unit, linked project, **by whom** |
| **Head account band kiya** | `updated` | `head` | `cen.heads.hed_closedt` | close date, actor, authority ref, reason |
| Head ka naam / code badla | `updated` | `head` | `hed_name`, `hed_code`, `hed_type` | old → new |
| Head project se de-linked | `updated` | `head` | `hed_prj_id` | old project → new / null |
| **Subhead allocation value set** | `created` | `subhead` | `fin.subheads` INSERT | head, subhead name, `sbh_alloc` |
| **Allocation value badli** | `updated` | `subhead` | `fin.subheads.sbh_alloc` | **50,00,000 → 80,00,000, kisne, kab, kis authority pe** — today completely untracked |
| Commitment bani | `created` | `commitment` | `fin.commitments` INSERT | amount, head, eff head, unit, date, remarks |
| Commitment amount badla | `updated` | `commitment` | `cmt_amount` | before → after |
| Commitment status badla | `updated` | `commitment` | `cmt_status` | e.g. Open → Fulfilled |
| Commitment cancel / delete | `deleted` | `commitment` | `fin.commitments` DELETE | **poora row JSONB mein mehfooz** (§3.3) |
| **Paisa nikla (payment)** | `created` | `transaction` | `fin.transactions` INSERT | `trn_amount1`, `trn_tax1`, `trn_balance`, commitment, date, seq |
| Transaction badli / reverse hui | `updated` / `deleted` | `transaction` | `fin.transactions` | before → after, full row on delete |
| **Transfer: head se head paisa gaya** | `created` | `transfer` | `fin.transfers` INSERT | `trf_fromhed → trf_tohed`, `trf_fromunt → trf_tount`, amount, type, title |
| Transfer status badla | `updated` | `transfer` | `trf_status` | pending → approved → executed |
| Share allocation badli | `created`/`updated` | `shares` | `fin.sharesalloc` | `sha_cf`, `sha_pcc`, `sha_prj`, `sha_prj_sal`, `sha_prj_pur` |
| Share installment | `created` | `shares` | `fin.sharesinstall` | `shi_cf`, `shi_pcc`, `shi_prj`, milestone |
| Fund shift raise / decide | `created`/`approved`/`returned` | `fundshift` | `fin.fund_shifts` + `fin.fund_shift_decisions` | already has actor; now also IP + authority |
| Contract verification | `updated` | `contract` | `fin.contractsverif.cvf_verif` | this is what the legacy app logs into `aud.busdata` today (§1.1) — now logged properly |

All `fin` money tables are set to `wt_snapshot_mode = 'always'` in §3.5, so every money row keeps a
full before **and** after picture, not just field diffs.

Ready-made report:

```sql
-- "is project ka poora paisa trail, shuru se aakhir tak"
SELECT e.sev_dtg, e.sev_action, e.sev_object, e.sev_name, e.sev_desig,
       e.sev_authority_ref, e.sev_remarks, e.sev_ip,
       f.fc_column, f.fc_old, f.fc_new
  FROM syslog.events e
  LEFT JOIN syslog.field_changes f ON f.fc_sev_id = e.sev_id
 WHERE e.sev_object IN ('project','head','subhead','commitment','transaction','transfer','shares')
   AND (e.sev_objid = '41' OR e.sev_objref ILIKE '%PRJ-41%')
 ORDER BY e.sev_dtg;

-- "is head account ka poora itihas: khulne se band hone tak"
SELECT * FROM syslog.record_history('cen','heads','hed_id=118');

-- "kis kis ne allocation values badli, pichle 6 mahine mein"
SELECT e.sev_dtg, e.sev_name, e.sev_desig, e.sev_ip, e.sev_authority_ref,
       f.fc_pk_text AS subhead, f.fc_old AS old_alloc, f.fc_new AS new_alloc
  FROM syslog.field_changes f JOIN syslog.events e ON e.sev_id = f.fc_sev_id
 WHERE f.fc_schema='fin' AND f.fc_table='subheads' AND f.fc_column='sbh_alloc'
   AND e.sev_dtg >= now() - interval '6 months'
 ORDER BY e.sev_dtg DESC;
```

### 5D.4 Group C — User account lifecycle

`cen.accounts` is `wt_snapshot_mode = 'always'`, `wt_redact_cols = {acc_pass}`,
`wt_severity = 2` (§3.5). Every one of these is an event:

| Event | `sev_action` | Source field |
|---|---|---|
| **Account bana — kiska, kisne banaya** | `account_created` | `cen.accounts` INSERT — full row, creating officer, IP |
| **Account close / disable hua** | `account_disabled` | `acc_status`, `acc_enddt` |
| Account dobara khola | `updated` | `acc_status`, `acc_enddt` → null |
| **Naam badla** | `updated` | `acc_name`, `acc_title` |
| **Designation badli** | `role_changed` | `acc_desig`, `acc_desigshort`, `acc_desigtype` — **severity 2** |
| Unit badli | `updated` | `acc_unt_id`, `acc_untname`, `acc_untarea` |
| Authority level badla | `permission_changed` | `acc_level`, `acc_auth`, `acc_access`, `acc_lowerm`, `acc_upperm`, `acc_lowers`, `acc_uppers` — **severity 2** |
| Rank badla | `updated` | `acc_rank` |
| Password badla | `password_changed` | `acc_pass` — value **never stored**, only the fact (§3.5) |
| Password force-change laga | `password_forced` | application event |
| Role ki permissions badli | `permission_changed` | `cen.role_permissions` INSERT/UPDATE/DELETE — **severity 2** |
| Role bana / mita | `role_changed` | `cen.roles` |
| Login / failed login / logout | `login` / `login_failed` / `logout` | `AuthController` (§8 phase 7) — today **nothing is recorded** (G-3) |
| Permission denied | `denied` | policy / middleware rejection, with the route attempted |

Note `acc_desig` has an `ON UPDATE CASCADE` FK to `cen.roles` (G-8): renaming a designation silently
rewrites it on every account. With `always` snapshots, the log keeps the pre-rename value, so historical
authority stays provable even after the rename.

### 5D.5 Group D — Employee lifecycle

| Event | `sev_action` | `sev_object` | Source |
|---|---|---|---|
| **Employee add hua** | `created` | `employee` | `hr.emps` INSERT — `emp_id`, CNIC, name, rank, join date, unit, head |
| Details badli | `updated` | `employee` | `hr.emps` — name, CNIC, rank, title, status, unit |
| Extra details daali / badli | `updated` | `employee` | `hr.empsexta`, `empsextb`, `empsextc` |
| **Head change hua** | `updated` | `employee` | `hr.emps.emp_hed_id` **and** `fin.empeffheads` (`eeh_emphed_id`, `eeh_dtg`, `eeh_status`) — both sides logged |
| Employee lock / unlock | `updated` | `employee` | `emp_locked` |
| Employee cleared | `updated` | `employee` | `emp_cleared` |
| Employee chhoda / status badla | `updated` | `employee` | `emp_status`, `emp_lastdt` |
| Employee record delete hua | `deleted` | `employee` | **poora row JSONB mein** — recoverable (§3.3) |
| Qualification / job / board add | `created` | `employee` | `hr.qualifs`, `hr.jobs`, `hr.bdapps`, `hr.bdmems` |
| Bank account add / badla | `updated` | `employee` | `hr.bnkaccounts` — **severity 1** |
| Contract bana / badla / khatam | `created`/`updated` | `contract` | `hr.contracts`, `hr.contractplans` |
| Contract case ki movement | `forwarded` etc. | `ctrcase` | `hr.ctrcases` + `ctrcaseremarks` (§5A) |
| Attendance entry / badli / unlock | `created`/`updated` | `attendance` | `hr.attendance`, `hr.attendanceremarks`, `hr.attendance_unlocks` |
| Vehicle / device / SIM issue | `created`/`updated` | `employee` | `hr.vehicles`, `hr.devices`, `hr.sims` |
| Photo / document attach | `uploaded` | `employee` | `hr.empattachments` + §5C |

### 5D.6 Group E — Salary cycle

The chain is `hr.salreqs → fin.salorders → fin.commitments → fin.transactions`, and every step is an
event:

| Event | `sev_action` | `sev_object` | Source | Captured |
|---|---|---|---|---|
| **Salary request bani** | `created` | `salreq` | `hr.salreqs` INSERT | emp, month, salary, gross, net, unit, head, eff head |
| Salary request badli | `updated` | `salreq` | `srq_salary`, `srq_arrears`, `srq_dues`, `srq_penalty`, `srq_award`, `srq_withheld`, `srq_loaned`, `srq_unpaiddays` — **each field separately** |
| Salary request release / close | `released` | `salreq` | `srq_releasedtg`, `srq_closedtg`, `srq_status` | actor, authority |
| Checked flag laga | `updated` | `salreq` | `srq_checked` | kisne verify kiya |
| **Salary order generate hui** | `created` | `salorder` | `fin.salorders` INSERT | emp, month, net salary, bank account title + detail, head, eff head |
| Salary order badli | `updated` | `salorder` | all `sor_*` amount fields | before → after |
| Salary order release / close | `released` | `salorder` | `sor_releasedtg`, `sor_closedtg`, `sor_status` | |
| Bank detail badla | `updated` | `salorder` | `sor_bnkacctitle`, `sor_bnkaccdetail` | **severity 2** — payee details |
| Salary order subhead split | `created`/`updated` | `salorder` | `fin.salorders_shd` | per-subhead amounts |
| Salary order case movement | `forwarded` etc. | `salordercase` | `fin.sal_order_cases` + `sal_order_case_decisions` (§5A) | |
| **Commitment gayi** | `created` | `commitment` | `fin.commitments` | amount, head, eff head, status |
| **Payment nikla** | `created` | `transaction` | `fin.transactions` | amount, tax, running balance |
| Salary tax slab badla | `updated` | `setting` | `fin.salary_tax` | **severity 2** — affects everyone |
| Loan adjustment / remark | `created`/`updated` | `salorder` | `fin.loanadjustments`, `fin.loanremarks` | |
| Extra compensation | `created` | `salorder` | `fin.extcompenses` | |

Ready-made report:

```sql
-- "is employee ka poora itihas: add hone se aaj tak, har tabdeeli"
SELECT e.sev_dtg, e.sev_action, e.sev_object, e.sev_name AS by_whom, e.sev_desig,
       e.sev_ip, e.sev_remarks, f.fc_table, f.fc_column, f.fc_old, f.fc_new
  FROM syslog.events e
  LEFT JOIN syslog.field_changes f ON f.fc_sev_id = e.sev_id
 WHERE (e.sev_object = 'employee' AND e.sev_objid = 'E-1042')
    OR (f.fc_schema IN ('hr','fin') AND f.fc_pk_text LIKE '%emp_id=E-1042%')
 ORDER BY e.sev_dtg;

-- "ek mahine ki salary kisne generate ki, kisne release ki, kitna paisa nikla"
SELECT e.sev_dtg, e.sev_action, e.sev_name, e.sev_desig, e.sev_authority_ref,
       e.sev_objref, f.fc_column, f.fc_old, f.fc_new
  FROM syslog.events e LEFT JOIN syslog.field_changes f ON f.fc_sev_id = e.sev_id
 WHERE e.sev_object IN ('salreq','salorder','commitment','transaction')
   AND e.sev_dtg >= '2026-11-01' AND e.sev_dtg < '2026-12-01'
 ORDER BY e.sev_dtg;
```

### 5D.7 `sev_object` — the full list

`purcase`, `ctrcase`, `gencase`, `salordercase`, `fundshift`, `project`, `head`, `subhead`,
`commitment`, `transaction`, `transfer`, `shares`, `employee`, `contract`, `salreq`, `salorder`,
`attendance`, `account`, `role`, `unit`, `document`, `firm`, `item`, `rfq`, `quote`, `inventory`,
`ticket`, `setting`, `leave`, `reversal`.

Kept as a plain `varchar` with a `watched_tables.wt_object` mapping rather than an enum, so adding a
module is an `INSERT`, not a migration — and an unmapped table still logs (as `sev_object = NULL`)
instead of failing.

### 5D.8 What this group needs beyond the triggers

The triggers give **what changed, by whom, from where**. Three things they cannot give, which need
application-side `Audit::log()` calls (phase 8):

1. **`sev_authority_ref`** — the sanction letter / approval reference. The database sees
   `sbh_alloc: 5000000 → 8000000`; only the app knows it was *"on authority of NRDI/FIN/2026/88"*.
2. **`sev_remarks` / `sev_reason_code`** — why. Especially for the money events; see open question 12
   on making this mandatory.
3. **Grouping** — "salary generate hui" is one business act that writes to `hr.salreqs`,
   `fin.salorders`, `fin.salorders_shd`, `fin.commitments` and `fin.transactions`. Calling
   `begin_event('released', 'salorder', ...)` once before the batch attaches all of it to **one**
   event, so the log reads as one act rather than forty unrelated row changes.

---

## 5E. System Logs UI — read-only, categorised, printable

### 5E.1 Access

One route group, one new permission family, following the project's existing
`module.action` convention from `PermissionRegistry`:

```php
// app/Services/Auth/PermissionRegistry.php  (additions)
public const SYSLOG_VIEW    = 'syslog.view';      // see the logs
public const SYSLOG_VIEW_ALL = 'syslog.view_all'; // across all units, not just own
public const SYSLOG_EXPORT  = 'syslog.export';    // CSV / PDF extract
public const SYSLOG_VERIFY  = 'syslog.verify';    // run chain verification
public const SYSLOG_RESTORE = 'syslog.restore';   // rebuild a deleted row (§3.3)
```

```php
// routes/web.php
Route::middleware(['auth', 'can:syslog.view'])
     ->prefix('admin/system-logs')->name('admin.syslog.')->group(function () {
    Route::get('/',                        [SystemLogController::class, 'home'])->name('home');
    Route::get('/{category}',              [SystemLogController::class, 'category'])->name('category');
    Route::get('/{category}/{object}/{id}',[SystemLogController::class, 'record'])->name('record');
    Route::get('/event/{sevId}',           [SystemLogController::class, 'event'])->name('event');
    Route::get('/{category}/{object}/{id}/print', [SystemLogController::class,'print'])->name('print');
    Route::get('/export/{category}',       [SystemLogController::class,'export'])
         ->middleware('can:syslog.export')->name('export');
});
```

`RolePermissionMap`: `SUPERADMIN` already receives `PermissionRegistry::all()`, so it gets these
automatically. `IT_ADMIN` gets all five; `IT_OFFICER` gets `syslog.view` + `syslog.export` only.
Nobody else gets anything unless explicitly granted — see open question 3.

Sidebar entry goes under the existing **`<li class="nav-header">System Admin</li>`** block in
`resources/views/welcome.blade.php` (line ~1533), beside `admin.accounts.index` and
`admin.reversals.index`, so it sits where an IT user already looks.

### 5E.2 Information architecture — three levels

```
LEVEL 1   System Logs  (landing)
          ─ category tiles, each with today's / this week's activity count
                │
LEVEL 2   Purchase Cases   (one category)
          ─ filterable event list: date, user, action, case, remarks, IP
                │
LEVEL 3   PUR-2026-114     (one record — "case kholun to uska sab")
          ─ six tabs, everything about this one case in one place
```

### 5E.3 Level 1 — the categories

Driven by `watched_tables.wt_object`, so a new module appears without a code change. Each tile shows
a count, the last activity time, and a severity dot if anything with `sev_severity = 2` happened in
the window.

| Tile | `sev_object` values behind it | What it covers |
|---|---|---|
| **Purchase Cases** | `purcase`, `quote`, `rfq`, `item` | cases, items, quotes, RFQs, receipts |
| **Hiring / Contract Cases** | `ctrcase`, `contract` | contract cases, contracts, plans |
| **Projects** | `project`, `document` | projects, milestones, progress, documents |
| **Employees** | `employee` | add, details, head change, bank, lock, clear |
| **Attendance** | `attendance` | entries, remarks, unlocks |
| **Salary** | `salreq`, `salorder`, `salordercase` | requests, orders, generation, release |
| **Finance / Money** | `head`, `subhead`, `commitment`, `transaction`, `transfer`, `shares` | head accounts, allocations, commitments, payments, transfers |
| **Fund Shifting** | `fundshift` | shifting cases and decisions |
| **Accounts & Roles** | `account`, `role`, `unit` | account created/closed, designation, permissions |
| **General Cases** | `gencase` | generic case files |
| **Inventory** | `inventory`, `item` | inventory and stock |
| **Firms** | `firm` | firms, offices, persons, specs |
| **Support** | `ticket` | helpdesk tickets |
| **Settings** | `setting` | system settings, global vars, tax slabs |
| **Reversals** | `reversal` | the `aud` schema workflow (unchanged, now also logged) |
| **System** | — | logins, failed logins, denied access, file uploads/downloads, DDL changes |
| **Legacy (2021–2026)** | — | read-only `aud.busdata` view (§5.v_legacy_busdata) |

### 5E.4 Level 2 — a category's event list

One table, dense but calm. Columns:

```
Date & time │ Who (name + desig) │ Unit │ Action │ Record │ What changed │ Remarks │ IP │
```

- **Action** is a coloured chip, not raw text: `created` green, `updated` blue, `deleted` red,
  `approved` green-solid, `returned` amber, `forwarded` grey, `denied` / `login_failed` red outline.
- **What changed** is a summary, not a dump: *"3 fields"*, *"Rs 23,50,000 → Rs 24,00,000"*,
  *"status: In Process → Approved"*. Click to expand inline.
- **Record** links to Level 3.
- Filters across the top: date range (default last 30 days), user, unit, action, severity, free text
  on case reference / remarks, and IP. All filters are in the query string so a filtered view is a
  shareable link.
- Default sort newest first; server-side pagination (keyset on `(sev_dtg, sev_id)`, not `OFFSET`, so
  deep pages stay fast).
- A "Severity" toggle: **Normal / Sensitive / Critical** — mapping to `sev_severity` 0/1/2, so an IT
  user can jump straight to money and permission changes.

### 5E.5 Level 3 — one record, six tabs

The page that matters. Header strip shows the record's identity and current state; the tabs hold
everything else.

```
┌──────────────────────────────────────────────────────────────────────────┐
│ PUR-2026-114 · Purchase Case · Rs 24,00,000 · Approved · DLOG            │
│ Opened 02 Nov 2026 by SO Proc (Maj Bilal) · 6 movements · 14 changes     │
│                                              [ Print ]  [ Export CSV ]   │
├──────────────────────────────────────────────────────────────────────────┤
│  Timeline │ Changes │ Custody │ Snapshots │ Files │ People & Access      │
└──────────────────────────────────────────────────────────────────────────┘
```

**Tab 1 — Timeline.** Every event for this record in order, as a vertical noting sheet. One entry per
event: time, actor with rank + designation, action chip, stage from → to, who it was sent to,
remarks, authority reference, IP. This is `syslog.case_trail()` (§5A.4) rendered.

**Tab 2 — Changes.** Field-level history, grouped by event. Each row: field label (human, from
`wt_labels`), old value → new value, by whom, when, IP. A per-field filter so you can ask "`pcs_price`
ki poori history" on one case. Values are formatted: money with separators, dates in the project's
format, booleans as Yes/No, foreign keys resolved to names where a label is available.

**Tab 3 — Custody.** `syslog.v_case_custody` (§5B.7): received from → held by → forwarded to, how long
each officer held it, and the **`changed anything`** flag. One line per hand. This is the "kis ke paas
kitna atka" view.

**Tab 4 — Snapshots.** One row per handover (§5B). Each row opens two panes side by side:
*"jab mila"* and *"jab aage bheja"*, with the differences highlighted — `snapshot_diff()` rendered as
a visual diff, additions green, removals red, modifications amber. A "view full case as it was then"
button renders the whole snapshot document read-only.

**Tab 5 — Files.** `syslog.file_events` (§5C): **asli filename**, stored path, size, type, uploaded by
whom and when, sha256, and every download with who and from which IP. Replaced files show the chain
with both hashes.

**Tab 6 — People & Access.** Everyone who has touched or even *opened* this record, with counts and
last access; plus any `denied` attempts against it. Answers "is case ko kis kis ne dekha".

### 5E.6 Read-only — enforced at four levels, not just hidden

A read-only screen that is only read-only in the template is not read-only. Four layers:

1. **Routes** — only `GET` routes exist in the group. No `POST`, `PUT`, `PATCH` or `DELETE` route is
   defined, so there is nothing to call.
2. **Permission** — `can:syslog.view` on the group; export and verify have their own permissions.
3. **Database** — the `rdwis_app` role has `SELECT` only on `syslog` (§6.1). Even if someone added a
   write route, the database refuses.
4. **Table** — the `aa_immutable` deny-triggers (§6.2) refuse `UPDATE` / `DELETE` regardless.

The UI carries no edit affordance anywhere: no pencil icon, no delete button, no inline editing, no
bulk actions. The only buttons are **Print**, **Export**, and (for `syslog.verify`) **Verify chain**.

### 5E.7 Sleek and understandable — the actual rules

"Sleek" is not decoration; it is a short list of decisions:

- **Plain language, not column names.** The screen says *"Case Status: In Process → Approved"*, never
  `pcs_status: 3 → 4`. This comes from `watched_tables.wt_labels` (§3.5), which is why that column
  exists. Any field without a label falls back to the column name, and the coverage check in §8.4
  can report unlabelled fields so the gap closes over time.
- **Money formatted as money.** `2400000` is rendered `Rs 24,00,000`. Dates in the project's existing
  format. Booleans as Yes/No. Nulls as a grey *"— not set —"*, never the word "null".
- **One accent colour per action class**, used consistently across all three levels, so an IT user
  learns the palette once.
- **Density with air.** Monospace only for hashes, IPs and paths. 13px body, generous row height,
  sticky table header, zebra off (borders instead). No modal stacking — Level 3 is a page, not a
  dialog, so it can be linked, bookmarked and printed.
- **Nothing hidden behind hover.** Every value is readable without interaction; expansion only adds
  detail, never reveals something essential.
- **The empty state says why.** "No events in this range" with the active filters listed and a
  one-click widen — not a blank table.
- **Honest labels on imported rows.** Backfilled rows (§5A.5) and snapshot #1 rows carry a grey
  *"imported history"* chip and no IP column, so nobody mistakes them for chain-protected evidence.

Built with what the project already uses: `@extends('welcome')`, `@yield('content')`,
`@stack('scripts')`, the existing Bootstrap/AdminLTE classes with Tailwind 4 for the new components,
and `chart.js` (already a dependency) for the optional activity sparkline on Level 1. No new
front-end dependency.

### 5E.8 Printable

- A dedicated `print` route rendering `resources/views/admin/syslog/print.blade.php`, extending
  `partials.case_print_layout` — the layout the project already uses for case print sheets, so a
  printed log looks like the rest of the paperwork.
- Print header: record reference, category, the filter range, **who printed it and when**, and the
  chain-verification status at time of printing.
- Print body: Timeline and Changes in full; Custody as a table; Files as a list with hashes.
  Snapshots print as the diff summary, with a separate "print full snapshot" action for one chosen
  handover.
- `@media print` CSS: tabs flattened into sequential sections, chips become text, colours become
  weights and borders, `page-break-inside: avoid` on each event block, repeating table headers,
  "Page X of Y" footer.
- Every print and export is itself logged (`sev_action = 'printed'` / `'exported'`) with the filter
  used — so the log of who inspected the log exists too.

### 5E.9 Keeping it fast

| Concern | Measure |
|---|---|
| Level 1 counts | a small `syslog.v_category_activity` materialised view refreshed every 10 minutes, not `count(*)` on every page load |
| Level 2 paging | keyset pagination on `(sev_dtg, sev_id)`; the partition pruning from §3.2 means a 30-day filter touches one or two partitions |
| Level 3 | all six tabs are indexed lookups by `(sev_object, sev_objid)` — `idx_events_obj`, `idx_rc_row`, `idx_fc_row`, `idx_cs_case`, `idx_fe_obj` |
| Snapshot diffs | computed on demand for the two snapshots being compared, never precomputed; `cs_doc_hash` equality short-circuits an unchanged pair without reading either document |
| Export | streamed CSV, never loaded into memory; the PDF path caps at a stated row count and tells the user to narrow the filter instead of timing out |

### 5E.10 Files to create

```
app/Http/Controllers/Admin/SystemLogController.php
app/Services/Syslog/LogQueryService.php        -- filters, keyset paging
app/Services/Syslog/LogPresenter.php           -- labels, money/date/bool formatting, chips
app/Services/Syslog/SnapshotDiffPresenter.php  -- renders snapshot_diff() as a visual diff
resources/views/admin/syslog/home.blade.php        -- Level 1 tiles
resources/views/admin/syslog/category.blade.php    -- Level 2 list + filters
resources/views/admin/syslog/record.blade.php      -- Level 3 shell + tabs
resources/views/admin/syslog/_tab_timeline.blade.php
resources/views/admin/syslog/_tab_changes.blade.php
resources/views/admin/syslog/_tab_custody.blade.php
resources/views/admin/syslog/_tab_snapshots.blade.php
resources/views/admin/syslog/_tab_files.blade.php
resources/views/admin/syslog/_tab_people.blade.php
resources/views/admin/syslog/print.blade.php
resources/views/admin/syslog/_event_row.blade.php  -- shared by Level 2 and print
```

### 5E.11 "Ek dot bhi change ho to save ho" — what that means exactly

The guarantee the triggers give: **every `INSERT`, `UPDATE` and `DELETE` on a registered table is
recorded, down to a single character in a single field**, with actor, authority, IP and before/after
value — whether it came from Eloquent, one of the 970 raw `DB::table()` calls, the legacy Access app,
a cron job, or a manual `psql` session (§4.4, §4.7).

Four honest exceptions, each with its mitigation:

| Not captured | Why | Mitigation |
|---|---|---|
| A change to a table **not registered** in `watched_tables` | the trigger is per-table | `audit:coverage` (§8.4) fails the build if any business table is unregistered |
| A no-op `UPDATE` that sets a field to the value it already had | step 2 of `fn_audit_row` skips it deliberately | nothing changed, so there is nothing to record — and this is what stops `->save()` on an untouched model from flooding the log |
| A column listed in `wt_ignore_cols` | explicitly excluded as noise (recomputed totals) | the exclusion list is itself in a logged table, so adding to it is recorded |
| Changes made with the trigger disabled or dropped | only a superuser can do this | the DDL is recorded in `ddl_events` and the hash seal breaks (§6.6) — detectable, not preventable |

So: not "everything is impossible to change", but **"nothing changes without a record, and any attempt
to remove the record leaves its own trace."** That is the honest form of the guarantee, and it is the
strongest one a database can give.

---

## 6. Immutability — how "uneditable" is actually achieved

Five mechanisms. Each one closes a hole the previous one leaves open, and §6.6 states plainly what
cannot be closed inside the database.

### 6.1 Privileges — the app must stop being superuser

**This is the prerequisite for everything else.** Today the only login role on the server is
`postgres` (`rolsuper = t`), and that is what `.env` uses. While that is true, nothing below is real.

```sql
-- the role the application connects as
CREATE ROLE rdwis_app LOGIN PASSWORD '<strong>';

-- business schemas: normal read/write, but NO truncate
GRANT USAGE ON SCHEMA cen, pur, hr, fin, prj, doc, sup, gen, frm, ina, puritems, purnew, public
  TO rdwis_app;
GRANT SELECT, INSERT, UPDATE, DELETE
  ON ALL TABLES IN SCHEMA cen, pur, hr, fin, prj, doc, sup, gen, frm, ina, puritems, purnew, public
  TO rdwis_app;
GRANT USAGE ON ALL SEQUENCES IN SCHEMA cen, pur, hr, fin, prj, doc, sup, gen, frm, ina, puritems, purnew, public
  TO rdwis_app;

-- aud schema: reversal workflow, unchanged behaviour
GRANT USAGE ON SCHEMA aud TO rdwis_app;
GRANT SELECT, INSERT, UPDATE ON aud.revs, aud.revdata, aud.revcomps, aud.audattachments TO rdwis_app;
GRANT SELECT ON aud.busdata TO rdwis_app;          -- legacy archive: read-only from the new app

-- syslog: the app can READ its own logs and nothing else.
-- Writing happens ONLY through the SECURITY DEFINER functions.
GRANT USAGE ON SCHEMA syslog TO rdwis_app;
GRANT SELECT ON syslog.events, syslog.row_changes, syslog.field_changes,
                 syslog.ddl_events, syslog.seals, syslog.verify_runs,
                 syslog.v_timeline, syslog.v_coverage, syslog.v_legacy_busdata TO rdwis_app;
GRANT EXECUTE ON FUNCTION syslog.begin_event(varchar,varchar,varchar,varchar,varchar,varchar,
                                             text,varchar,varchar,varchar,smallint) TO rdwis_app;
GRANT EXECUTE ON FUNCTION syslog.record_history(name,name,varchar) TO rdwis_app;

-- explicitly deny everything else
REVOKE INSERT, UPDATE, DELETE, TRUNCATE
  ON ALL TABLES IN SCHEMA syslog FROM rdwis_app;
REVOKE CREATE ON SCHEMA syslog FROM rdwis_app, PUBLIC;
REVOKE ALL ON SCHEMA syslog FROM PUBLIC;

-- future tables in syslog inherit the same stance
ALTER DEFAULT PRIVILEGES IN SCHEMA syslog
  GRANT SELECT ON TABLES TO rdwis_app;

-- a separate read-only role for auditors / IT review, with no write anywhere
CREATE ROLE rdwis_auditor LOGIN PASSWORD '<strong>';
GRANT USAGE ON SCHEMA syslog TO rdwis_auditor;
GRANT SELECT ON ALL TABLES IN SCHEMA syslog TO rdwis_auditor;
```

Then `.env` becomes `DB_USERNAME=rdwis_app`, and the `postgres` password is held only by the DBA.

The `syslog` tables are owned by a third role (say `rdwis_audit_owner`, `NOLOGIN`) which owns the
`SECURITY DEFINER` functions. So: the app can call the function, the function can write, the app
cannot write. That is the core of it.

### 6.2 Deny-triggers — append-only even for a privileged mistake

```sql
CREATE FUNCTION syslog.fn_deny_change() RETURNS trigger
LANGUAGE plpgsql AS $fn$
BEGIN
  RAISE EXCEPTION
    'syslog.% is APPEND-ONLY. % is forbidden. (db_user=%, app_user=%, pid=%)',
    TG_TABLE_NAME, TG_OP, session_user,
    coalesce(nullif(current_setting('rdwis.username', true), ''), '-'), pg_backend_pid();
END $fn$;

-- applied to each of the three append-only tables, and to each partition
CREATE TRIGGER aa_immutable BEFORE UPDATE OR DELETE ON syslog.events
  FOR EACH ROW EXECUTE FUNCTION syslog.fn_deny_change();
ALTER TABLE syslog.events ENABLE ALWAYS TRIGGER aa_immutable;
-- ... same for syslog.row_changes, syslog.field_changes, syslog.ddl_events, syslog.seals
```

Details worth knowing:

- Row triggers created on a **partitioned parent are propagated to partitions** automatically in
  PostgreSQL 13+ — but `ENABLE ALWAYS` must be re-applied per partition. The monthly partition job
  (§8.3) does this, and §8.6 verifies it.
- `TRUNCATE` is not caught by a `BEFORE UPDATE OR DELETE` row trigger. It is blocked by the
  privilege revoke in §6.1, plus a `BEFORE TRUNCATE` statement trigger using the same function.
- `syslog.watched_tables` is deliberately **not** append-only — it is configuration. But it is itself
  registered in `watched_tables`, so every change to it is logged into `syslog.events` with
  `severity = 2`.

### 6.3 Why the hash is not a per-row chain

A per-row chain (`row N's hash includes row N−1's hash`) means every insert must read the previous
row under a lock, which serialises all audit writes. At 57 users it would work, but it buys a
contention risk for little gain.

The design instead uses:

- **`sev_hash` per event** — a self-hash of that event's own immutable payload. No lock, no ordering
  dependency. Detects a modified event.
- **`syslog.seals` as the chain** — a scheduled job hashes a whole time range *in order* and chains
  seal to seal. Detects a modified, inserted, **deleted or truncated** event, because the counts and
  the range digest both change.

### 6.4 `syslog.seal_range()` — the chain, computed off the write path

```sql
CREATE FUNCTION syslog.seal_range(p_to timestamptz DEFAULT date_trunc('hour', now()))
RETURNS bigint LANGUAGE plpgsql SECURITY DEFINER AS $fn$
DECLARE
  v_prev    syslog.seals;
  v_from    timestamptz;
  v_digest  text;
  v_id      bigint;
BEGIN
  SELECT * INTO v_prev FROM syslog.seals ORDER BY sel_id DESC LIMIT 1;
  v_from := coalesce(v_prev.sel_to_dtg, '-infinity'::timestamptz);
  IF p_to <= v_from THEN RETURN NULL; END IF;        -- nothing new to seal

  SELECT encode(sha256(convert_to(
           coalesce(string_agg(payload, '|' ORDER BY ord), ''), 'UTF8')), 'hex')
    INTO v_digest
    FROM (
      SELECT e.sev_id AS ord,
             e.sev_id || ':' || e.sev_dtg || ':' || e.sev_username || ':' || e.sev_action
                       || ':' || coalesce(e.sev_object,'') || ':' || coalesce(e.sev_objid,'')
                       || ':' || coalesce(host(e.sev_ip),'') || ':' || e.sev_hash
                       || ':' || coalesce((SELECT string_agg(r.rc_op || r.rc_pk_text || md5(
                                    coalesce(r.rc_old_row::text,'') ||
                                    coalesce(r.rc_new_row::text,'')), ','
                                    ORDER BY r.rc_id)
                                  FROM syslog.row_changes r
                                 WHERE r.rc_sev_id = e.sev_id), '') AS payload
        FROM syslog.events e
       WHERE e.sev_dtg >= v_from AND e.sev_dtg < p_to
    ) s;

  INSERT INTO syslog.seals (sel_from_dtg, sel_to_dtg, sel_first_sev, sel_last_sev,
                            sel_event_count, sel_rc_count, sel_fc_count,
                            sel_prev_hash, sel_hash)
  SELECT v_from, p_to,
         min(sev_id), max(sev_id), count(*),
         (SELECT count(*) FROM syslog.row_changes   WHERE rc_dtg >= v_from AND rc_dtg < p_to),
         (SELECT count(*) FROM syslog.field_changes WHERE fc_dtg >= v_from AND fc_dtg < p_to),
         v_prev.sel_hash,
         encode(sha256(convert_to(coalesce(v_prev.sel_hash,'') || v_digest, 'UTF8')), 'hex')
    FROM syslog.events WHERE sev_dtg >= v_from AND sev_dtg < p_to
  RETURNING sel_id INTO v_id;

  RETURN v_id;
END $fn$;
```

`syslog.verify_seals()` recomputes every seal from the data and compares. Its result goes into
`syslog.verify_runs`, and the System Logs screen shows a green / red badge from the latest run.

**Recommendation: seal hourly, not daily.** The unsealed window is the only period in which a
tampered row would not break the chain, so keep it short.

### 6.5 Off-box append-only copy

The hourly job, after sealing, exports the new rows plus the new seal to an append-only file on the
backup share (the path already used by `storage_backup.py` / `storagebackup_share.py`), and stamps
`sel_exported = true`, `sel_export_path`. The file carries the seal hash, so a restored or rebuilt
database can be checked against a copy that the database server itself cannot reach back and edit.

### 6.6 What cannot be guaranteed — stated plainly

A PostgreSQL superuser can, in principle, `ALTER TABLE ... DISABLE TRIGGER ALL`, drop an event
trigger, or edit `syslog` directly. **No in-database design prevents that.** What this design does is
make it *impossible to do silently*:

| Attack | Caught by |
|---|---|
| App or SQL-injection tries to edit a log | §6.1 privileges (no UPDATE/DELETE grant at all) |
| `session_replication_role = replica` to skip triggers | `ENABLE ALWAYS` (§4.7, §6.2) |
| Edit a log row as superuser | deny-trigger (§6.2); if the trigger was dropped first, the drop is in `ddl_events` and the seal breaks |
| Drop the audit trigger, write, re-create it | `ddl_events` records both DDL statements; the writes in between have no log, so the row's history has a gap |
| Delete log rows or truncate a partition | seal counts and range digest change → `verify_seals` fails |
| Drop the event trigger too, then do all of the above | the hourly off-box export (§6.5) still holds the last good seal, so the divergence is detectable from outside |
| Someone with OS access to the data directory edits files | nothing in the database. Requires OS-level control: who can log in to the server, file permissions on the data directory, and the off-box copy. |

So the real security boundary is: **(a) the app is not superuser, (b) the `postgres` password is held
by one or two named people, (c) OS login to the DB server is restricted, (d) an off-box append-only
copy exists.** The schema handles (a) and (d); (b) and (c) are administrative and must be written
into the SOP.

---

## 7. Sizing — what this will cost

Measured starting point: `rdw04` is **53 MB** today. Largest tables: `pur.quoteitems` 29,589 rows,
`puritems.prices` 23,856, `pur.purcaseitems` 14,903, `fin.transactions` 8,123, `fin.commitments`
7,705, `fin.salorders` 6,083, `hr.attendance` 5,321. 120 business tables, **1,263 columns** total,
**57 accounts**.

Estimate at 300 write-actions per working day (deliberately generous for 57 users):

| Table | Rows/day | Avg row | Per day |
|---|---|---|---|
| `syslog.events` | 300 | ~700 B (many varchars, mostly short) | ~0.2 MB |
| `syslog.row_changes` | 400 | ~400 B with `delete_only`; ~2 KB where `always` | ~0.4 MB |
| `syslog.field_changes` | 900 | ~200 B | ~0.2 MB |
| **Total heap** | | | **~0.8 MB/day** |
| With indexes (≈ ×1.8) | | | **~1.4 MB/day** |

Plus **case snapshots (§5B.8): ~7–12 MB/year**, measured from the real data rather than estimated.

→ **~360 MB per working year**, ~3.6 GB over ten years. The audit trail will be an order of magnitude
larger than the business data — that is normal and expected, and it is still a small database.

If `wt_snapshot_mode` were set to `always` everywhere it would be roughly **3× that**. That single
column is the cost dial; `delete_only` as the default is why the number above is modest.

Write overhead per business statement: one `watched_tables` lookup (tiny, cached), one JSONB diff
over ~12 columns, and 1–4 inserts. Expect **single-digit percent** added latency on write paths at
this scale. The reads that matter are all index-covered.

---

## 8. Maintenance — how to run this day to day

This is the part that keeps the trail trustworthy. Nine routines.

### 8.1 Hourly (automated) — seal and export

```bash
php artisan audit:seal      # calls syslog.seal_range(), then exports new rows + seal off-box
```

Scheduled in `app/Console/Kernel.php` (`->hourly()`), and it must **alert on failure** — a seal job
that silently stops is the same as having no chain. The alert goes to IT Admin.

### 8.2 Daily (automated) — verify and report

```bash
php artisan audit:verify    # recomputes every seal; writes syslog.verify_runs; non-zero exit on break
```

Dashboard tile: last verify time, status, first bad seal if any.

Daily sanity query — writes nobody owns:

```sql
SELECT sev_dtg, sev_source, sev_action, sev_object, sev_objid, sev_route
  FROM syslog.events
 WHERE sev_dtg >= current_date - 1
   AND (sev_acc_id IS NULL OR sev_implicit)
 ORDER BY sev_dtg DESC;
```

A row here means something wrote to the database without passing through the app's audit context —
the legacy Access app, a script, or a manual `psql`. It should be explainable every single time.

### 8.3 Monthly (automated) — partitions

```bash
php artisan audit:partitions --months=3   # create the next 3 months if missing
```

For each of the three partitioned tables it runs, idempotently:

```sql
CREATE TABLE IF NOT EXISTS syslog.events_2026_12
  PARTITION OF syslog.events FOR VALUES FROM ('2026-12-01') TO ('2027-01-01');

-- re-apply ALWAYS on the propagated deny-trigger (this step is easy to forget)
ALTER TABLE syslog.events_2026_12 ENABLE ALWAYS TRIGGER aa_immutable;
REVOKE INSERT, UPDATE, DELETE, TRUNCATE ON syslog.events_2026_12 FROM rdwis_app;
GRANT  SELECT ON syslog.events_2026_12 TO rdwis_app, rdwis_auditor;
```

**Run it ahead of time, never at the boundary.** If the partition for the current month is missing,
every audited write fails — and because the trigger is in the same transaction, **the business write
fails too**. Keep three months ahead and alert if fewer than two future partitions exist:

```sql
SELECT count(*) FROM pg_class c
  JOIN pg_inherits i ON i.inhrelid = c.oid
 WHERE i.inhparent = 'syslog.events'::regclass
   AND pg_get_expr(c.relpartbound, c.oid) > format('FOR VALUES FROM (''%s'')', date_trunc('month', now()));
```

(If `pg_partman` is available on this Windows build, it replaces this command; otherwise the artisan
command is the way.)

### 8.4 Whenever a table or column is added — the drift check

This is the routine most likely to be skipped, and the one that quietly destroys coverage.

```bash
php artisan audit:coverage     # reads syslog.v_coverage, exits non-zero if any gap
```

```sql
SELECT * FROM syslog.v_coverage
 WHERE NOT registered OR NOT enabled OR NOT trigger_always_on
 ORDER BY table_schema, table_name;
```

Make it part of the deployment checklist, and ideally a CI step so a migration that adds a table
fails review until the table is either registered or explicitly listed as excluded.

When a module adds a table:

```sql
SELECT syslog.attach_audit('hr', 'newtable');
UPDATE syslog.watched_tables
   SET wt_object = 'employee', wt_snapshot_mode = 'delete_only',
       wt_labels = '{"nt_status":"Status"}'::jsonb
 WHERE wt_schema = 'hr' AND wt_table = 'newtable';
```

When a column is added, nothing breaks — `to_jsonb(NEW)` picks it up automatically. Only two
optional follow-ups: add a human label to `wt_labels`, and add it to `wt_redact_cols` if it is
sensitive. New columns show up here:

```sql
SELECT dde_dtg, dde_db_user, dde_tag, dde_objid FROM syslog.ddl_events
 WHERE dde_dtg >= current_date - 7 ORDER BY dde_dtg DESC;
```

### 8.5 Weekly — size and growth

```sql
SELECT c.relname,
       pg_size_pretty(pg_total_relation_size(c.oid)) AS total,
       (SELECT count(*) FROM pg_inherits WHERE inhparent = c.oid) AS partitions
  FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace
 WHERE n.nspname = 'syslog' AND c.relkind IN ('r','p')
 ORDER BY pg_total_relation_size(c.oid) DESC;
```

Compare against §7. If growth is well above the estimate, find the noisy table and move it from
`always` to `delete_only`, or add columns to `wt_ignore_cols`:

```sql
SELECT rc_schema, rc_table, rc_op, count(*)
  FROM syslog.row_changes WHERE rc_dtg >= now() - interval '7 days'
 GROUP BY 1,2,3 ORDER BY 4 DESC LIMIT 20;
```

### 8.6 Monthly — protection audit (the audit of the audit)

Four checks. Any failure is an incident, not a maintenance item.

```sql
-- 1. every audited table has an ALWAYS-enabled trigger
SELECT * FROM syslog.v_coverage WHERE registered AND NOT trigger_always_on;

-- 2. the deny-triggers are present and ALWAYS on every partition
SELECT c.relname, g.tgname, g.tgenabled
  FROM pg_trigger g JOIN pg_class c ON c.oid = g.tgrelid
  JOIN pg_namespace n ON n.oid = c.relnamespace
 WHERE n.nspname = 'syslog' AND g.tgname = 'aa_immutable' AND g.tgenabled <> 'A';

-- 3. the event triggers still exist
SELECT evtname, evtenabled FROM pg_event_trigger;

-- 4. nobody but the owner has write access to syslog
SELECT grantee, table_name, privilege_type
  FROM information_schema.role_table_grants
 WHERE table_schema = 'syslog' AND privilege_type IN ('INSERT','UPDATE','DELETE','TRUNCATE');

-- 5. no unexpected superuser or login role has appeared
SELECT rolname, rolsuper, rolcanlogin FROM pg_roles WHERE rolcanlogin OR rolsuper;
```

Check 5 matters because today the answer is a single superuser row; after Phase 0 it should be
`postgres` (super), `rdwis_app` (not super), `rdwis_auditor` (not super). Anything else is a finding.

### 8.7 Quarterly — access review and restore drill

- Who holds `rdwis_auditor` and the `postgres` password? Named people, written down, reviewed.
- Who has the System Logs permission in `cen.role_permissions`? Compare against the approved list.
- **Restore drill:** take a wrongly-deleted test record and restore it from `rc_old_row` end to end
  (§3.3). A backup that has never been restored is a hope, not a backup — the same is true here.
- Verify one exported seal file off-box against `syslog.seals`.

### 8.8 Yearly — archive, never delete

Nothing is ever deleted. After the retention horizon (recommend 7 years, to be confirmed):

```sql
-- move the old partition to cheaper storage but keep it attached and readable
ALTER TABLE syslog.events_2019_01 SET TABLESPACE audit_archive;
-- or detach and keep as a standalone read-only table, still in the same database
ALTER TABLE syslog.events DETACH PARTITION syslog.events_2019_01;
ALTER TABLE syslog.events_2019_01 OWNER TO rdwis_audit_owner;
```

Then `VACUUM (FREEZE, ANALYZE)` the archived partition once and leave it alone.

### 8.9 Incident response — "verify failed", what now

1. **Do not touch `syslog`.** Not even to look with a write-capable connection.
2. `audit:verify` names the first bad seal → gives you a time window.
3. Pull the off-box export for that window (§6.5) and diff it against the live table. The difference
   *is* the tampering.
4. Read `syslog.ddl_events` for that window: `DROP TRIGGER`, `DISABLE TRIGGER`, `ALTER` — with
   `dde_db_user`, `dde_client` (IP) and `dde_backend`.
5. Cross-check PostgreSQL's own server log for connections as `postgres` in that window. (Which
   means `log_connections = on` and `log_statement = 'ddl'` should be set in `postgresql.conf` as
   part of Phase 0 — a cheap, independent second record.)
6. Report, then re-seal from the last known-good seal with a `syslog.verify_runs` entry recording
   what was found.

### 8.10 Operational special cases

| Situation | What to do |
|---|---|
| **Bulk import** (attendance file, item master) | Call `syslog.begin_event('imported', ...)` once before the loop. All rows then attach to **one** event header, so the UI shows "imported 5,321 attendance rows" rather than 5,321 separate entries. Row-level detail is still in `row_changes`. |
| **Data fix by DBA** | Set the session vars manually first: `SELECT set_config('rdwis.username','dba:asalman',false), set_config('rdwis.source','console',false);` then `syslog.begin_event('updated', ..., p_remarks := 'ticket #123')`. A DBA fix with no reason recorded should be treated as unacceptable by policy. |
| **Legacy Access app keeps writing** | It will be logged automatically with `sev_source='db'` and `sev_acc_id=NULL`. Better: have it issue one `set_config('rdwis.username', <its login>, false)` after connecting, and those writes become properly attributed as `sev_source='legacy'`. |
| **Restoring from backup** | The restore itself is outside the trail. Record it as a manual `syslog.events` row (via `begin_event`, `action='restored'`), re-run `audit:verify`, and expect a chain discontinuity at the restore point — document it in `verify_runs`. |
| **A migration rewrites a whole table** | Expect a large log burst. If it is a pure structural change with no business meaning, temporarily `UPDATE syslog.watched_tables SET wt_enabled=false` for that table, run the migration, re-enable — and note that the toggle itself is logged, so the gap is explained rather than hidden. |
| **Performance complaint on a write path** | Check `syslog.field_changes` volume for that table first; it is almost always a wide `UPDATE` touching many columns. Fix with `wt_ignore_cols` for noise columns (e.g. recomputed totals), not by disabling the trigger. |

### 8.11 Artisan commands to build (summary)

| Command | Schedule | Purpose |
|---|---|---|
| `audit:seal` | hourly | `syslog.seal_range()` + off-box export |
| `audit:verify` | daily + on demand | recompute all seals, write `verify_runs`, alert |
| `audit:partitions --months=3` | monthly | create future partitions, re-apply `ALWAYS` + grants |
| `audit:coverage` | on deploy + monthly | fail if any business table is unaudited |
| `audit:protect-check` | monthly | the five queries in §8.6 |
| `audit:export --from= --to=` | on demand | signed CSV/JSON extract for an inquiry (logged as `exported`) |
| `audit:restore-row` | on demand, IT Admin only | rebuild a deleted row from `rc_old_row`, logged as `restored` |

---

## 8A. Legacy safety — kya purana system crash ho jayega?

Short answer: **the audit system itself requires zero changes to any existing table.** The risk is
confined to the *optional* convenience columns, and even there the blast radius turns out to be three
tables, not a hundred and twenty.

### 8A.1 First, separate the two kinds of change

| Change | Touches existing tables? | Needed for the audit trail? |
|---|---|---|
| New `syslog` schema + its 10 tables | **No** — brand new schema | Yes |
| Trigger functions, event triggers, seals | **No** — new objects | Yes |
| `AuditContext` middleware, `Audit::log()` calls | **No** — PHP only | Yes |
| `zz_audit_row` trigger attached to a table | **No schema change** — a trigger does not alter a table's shape, columns, types or order | Yes |
| New DB roles + privileges (§6.1) | No schema change; grants only | Yes |
| **§5C.4** — `*_origname`, `*_bytes`, `*_mime`, `*_sha256`, `*_uploader_id`, `*_dtg` on 6 attachment tables | **Yes, `ALTER TABLE`** | **No — optional** |
| **§5D.1 / Q22** — `created_by` / `created_at` on 9 legacy tables | **Yes, `ALTER TABLE`** | **No — optional** |
| **§5A.6** — `crr_acc_id`, `*_holder_id` columns | **Yes, `ALTER TABLE`** | **No — optional** |

So if the legacy app is fragile, **the whole audit trail can go live without a single `ALTER TABLE`**
and you lose only: original filenames shown on module screens, and `created_by` visible in module
reports. The audit log itself still records all of it. That is the safe default, and it is the
recommended way to sequence this.

### 8A.2 What the legacy app actually touches — evidence, not guesswork

`aud.busdata.bdt_formname` is a direct record of which legacy forms write to the database. Queried:

```
       bdt_formname       | count | first_seen | last_seen
--------------------------+-------+------------+------------
 fin_contractsverif       |   716 | 2021-01-21 | 2026-10-02   ← ACTIVE
 fin_empeffheads_detail   |   193 | 2023-07-21 | 2026-09-29   ← ACTIVE
 fin_globalvars_sal       |     2 | 2026-06-30 | 2026-06-30   ← ACTIVE
 fin_empeffheads_u_active |   351 | 2021-02-25 | 2023-07-19   ← dormant since Jul 2023
 hr_contracts_edit        |    88 | 2021-01-20 | 2022-05-27   ← dormant since May 2022
 hr_emps_edit             |    30 | 2021-01-20 | 2022-05-12   ← dormant since May 2022
```

**The legacy app's live write footprint is three tables:** `fin.contractsverif`,
`fin.empeffheads`, `cen.globalvars`. `hr.contracts` and `hr.emps` have not been written from the
legacy app since 2022 — Laravel took those over.

**None of the six attachment tables appear at all.** So the §5C.4 column additions — the ones most
worth doing — do not touch anything the legacy app has written in six years.

> **Limit of this evidence.** `busdata` records what the legacy app *audits*, which is not necessarily
> everything it *writes* — a form without audit code, or a read-only form, leaves no trace here. So
> this is strong evidence of what **is** touched and weaker evidence of what **is not**. It narrows
> the testing surface; it does not replace testing (§8A.5).

### 8A.3 What `ALTER TABLE ADD COLUMN` actually does in PostgreSQL 18

- A nullable column with no default, or with a **constant** default, is **metadata-only**: the table
  is not rewritten, no data is moved, and it completes in milliseconds regardless of table size.
- It takes a brief `ACCESS EXCLUSIVE` lock — long enough to be blocked by a long-running query, so
  run it in a maintenance window with `SET lock_timeout = '3s'` so it fails fast rather than queuing
  behind a report and blocking every reader.
- `ADD COLUMN ... REFERENCES cen.accounts(acc_id)` adds a constraint that is validated against
  existing rows — but all existing values are NULL, so validation is trivial.
- `DROP COLUMN` is equally instant, which is what makes the rollback in §8A.6 cheap.

**So PostgreSQL is not the risk.** The risk is entirely in the legacy client.

### 8A.4 The four ways a legacy client actually breaks

| Failure mode | Why it happens | Does it apply here? | Fix |
|---|---|---|---|
| **`INSERT INTO t VALUES (...)` without a column list** | adding a column changes the expected value count → immediate error on every insert | **The real danger.** Must be checked for the three active tables. | Add the column list in the legacy app, or do not add columns to that table |
| **Access linked tables cache the schema** | MS Access stores the column list when the table is linked; a new column makes the cached definition stale → "field not found" or silently missing data | **Applies if the legacy app is Access.** | Linked Table Manager → Refresh, once, after the ALTER. A one-time, two-minute operation per client machine. |
| **Delphi `TQuery` / `TTable` with persistent fields** | the field list is compiled into the form; `SELECT *` returning an extra column can raise a field-mismatch error | **Applies if it is Delphi/BDE.** | Change the form's query to name columns explicitly, or do not add columns to that table |
| **Fixed-width grids / reports bound to `SELECT *`** | an extra column shifts layout or appears as a blank column | cosmetic, not a crash | Name columns in the query |

Which of these applies cannot be determined from this side — it depends on how the legacy app was
written, and that code is not in this repository. **That is the one open unknown, and it is what the
staging test in §8A.5 is for.**

### 8A.5 Safe rollout — you already have the perfect test database

`rdw03` is a **byte-for-byte equivalent copy**: 135 tables, the same 13 schemas, identical shape to
`rdw02`. (`rdw01` and `rdw` are older copies.) So:

1. **Point the legacy app at `rdw03`** — connection string only, nothing else changes.
2. Apply the **full** change set there: new `syslog` schema, all triggers, all optional `ALTER`s.
3. **Exercise every legacy form that writes**, starting with the three active ones:
   - contract verification (`fin_contractsverif`) — the one with 716 audited edits
   - employee effective head detail (`fin_empeffheads_detail`)
   - salary global variables (`fin_globalvars_sal`)
   - then the three dormant forms, in case they are still reachable: `fin_empeffheads_u_active`,
     `hr_contracts_edit`, `hr_emps_edit`
   - then every *read-only* legacy screen, since `busdata` would not have recorded those (§8A.2 limit)
4. Confirm each write **succeeds** and **produced a `syslog.events` row** with
   `sev_source = 'db'` (or `'legacy'` once the legacy app sets `rdwis.username`).
5. Only then repeat on `rdw04`.

This costs a day and removes the entire guess. It also validates the Laravel side at the same time.

### 8A.6 The real risk is not the ALTER — it is a failing trigger

A trigger does not change a table's shape, so it cannot break a client's column expectations. But it
runs **inside the writer's transaction**, so:

> **If `syslog.fn_audit_row()` raises an error, the business `INSERT`/`UPDATE` fails too.**

That is the actual way this project could "crash" the old system — and the new one. Three concrete
causes and their guards:

| Cause | Guard |
|---|---|
| **The month's partition does not exist** (§8.3) — every audited write fails | `audit:partitions --months=3` kept three months ahead, with an alert when fewer than two future partitions exist. This is the single most likely cause of an outage, and §8.3 already flags it. |
| A bug in the trigger function | staged on `rdw03` first (§8A.5); attached in waves (§9 phases 4 and 6), not all 120 tables at once |
| `watched_tables` row missing or malformed | `fn_audit_row` returns `NULL` early if the table is not registered — a missing row means "not audited", never an error |

And the design decision that has to be made explicitly:

**Fail-closed vs fail-open.**

```sql
-- fail-open wrapper, used only for tables marked wt_fail_open = true
EXCEPTION WHEN OTHERS THEN
    INSERT INTO syslog.trigger_failures (tf_dtg, tf_schema, tf_table, tf_op,
                                         tf_sqlstate, tf_message, tf_db_user, tf_app_user)
    VALUES (clock_timestamp(), TG_TABLE_SCHEMA, TG_TABLE_NAME, TG_OP,
            SQLSTATE, SQLERRM, session_user,
            nullif(current_setting('rdwis.username', true), ''));
    RETURN NULL;   -- business write proceeds, audit row is lost but the failure is recorded
```

| Mode | Behaviour | Use for |
|---|---|---|
| **Fail-closed** (default) | audit error ⇒ business write fails | `cen.accounts`, `cen.role_permissions`, all `fin` money tables — where an unlogged change is worse than a blocked one |
| **Fail-open** (`wt_fail_open = true`) | audit error ⇒ write proceeds, failure logged to `syslog.trigger_failures` and alerted | the three legacy-touched tables during the first month, and high-volume low-risk tables like `hr.attendance` |

Add to `watched_tables`:

```sql
ALTER TABLE syslog.watched_tables
  ADD COLUMN wt_fail_open boolean NOT NULL DEFAULT false;

CREATE TABLE syslog.trigger_failures (
  tf_id       bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  tf_dtg      timestamptz NOT NULL DEFAULT clock_timestamp(),
  tf_schema   varchar(30), tf_table varchar(60), tf_op varchar(10),
  tf_sqlstate varchar(10), tf_message text,
  tf_db_user  name, tf_app_user varchar(255)
);
```

A non-empty `syslog.trigger_failures` is an **alert, not a log line** — it means the trail has a hole.
Checked by `audit:verify` daily (§8.2).

**Recommended for the legacy tables specifically:** mark `fin.contractsverif`,
`fin.empeffheads` and `cen.globalvars` as `wt_fail_open = true` for the first month, so a surprise
from the legacy client degrades the audit instead of stopping salary and contract work. Then switch
them to fail-closed once a month of clean operation proves it.

### 8A.7 Rollback — one command, instant

Every piece of this is removable without touching business data:

```sql
-- stop auditing one table (instant, no lock beyond the trigger drop)
SELECT syslog.detach_audit('fin','contractsverif');

-- stop auditing everything, keep all logs collected so far
DO $$ DECLARE r record; BEGIN
  FOR r IN SELECT wt_schema, wt_table FROM syslog.watched_tables LOOP
    PERFORM syslog.detach_audit(r.wt_schema, r.wt_table);
  END LOOP;
END $$;

-- remove an optional column added in §5C.4 (metadata-only, instant)
ALTER TABLE pur.purattachments DROP COLUMN pat_origname;
```

The `syslog` schema itself is never dropped — the logs already collected stay, and are still valid
evidence for the period they cover.

### 8A.8 The order that minimises risk

1. `rdw03` staging: everything, including the optional `ALTER`s. Test all six legacy forms. *(§8A.5)*
2. `rdw04`: create `syslog`, functions, partitions, roles, grants. **No triggers yet.** Nothing is
   audited, nothing can break. Verify the app and the legacy app both still work.
3. Attach triggers to **Laravel-only tables first** — `gen.*`, `sup.*`, `pur.*`, `prj.*`,
   `cen.role_permissions`. The legacy app cannot be affected by these.
4. Attach to `cen.accounts` and the `fin` money tables, fail-closed.
5. Attach to the **three legacy-touched tables last**, `wt_fail_open = true`, and watch
   `syslog.trigger_failures` for a week.
6. Optional `ALTER`s, one table per maintenance window, with the Access/Delphi refresh step done on
   every client machine immediately after each one.
7. Switch the legacy tables to fail-closed after a clean month.

Steps 2 and 3 are reversible in seconds and carry essentially no risk to the legacy app. The real
decision point is step 5, and by then the staging test has already answered it.

---

## 9. Build order

| Phase | Work | Depends on |
|---|---|---|
| **0** | `rdwis_app` + `rdwis_auditor` roles, privileges, `.env` switch, `log_connections`/`log_statement='ddl'` in `postgresql.conf`, regression test every module | — |
| **1** | `CREATE SCHEMA syslog`; the seven tables; partitions for the next 3 months; deny-triggers; `fn_deny_change` | 0 |
| **2** | `begin_event`, `ensure_event`, `fn_audit_row`, `fn_audit_truncate`, `attach_audit`, `detach_audit`; `watched_tables` seed | 1 |
| **3** | `AuditContext` middleware + `Audit` facade in PHP; console/job bootstrappers | 2 |
| **4** | Attach Wave 1 (`cen.*`, `fin.*` money tables) and test every write path | 3 |
| **5** | Event triggers (`ddl_events`); `seal_range`, `verify_seals`; `audit:seal` + `audit:verify` + off-box export | 2 |
| **6** | Attach Wave 2 (cases: `pur`, `hr`, `gen`, `prj`, `doc`) | 4 |
| **7** | Auth and access events: login, login_failed, logout, denied, password_changed, exported, printed, downloaded | 3 |
| **8** | `Audit::log()` calls at approval/decision points so `sev_authority_ref`, `sev_remarks`, `sev_stage_*`, `sev_to_holder_id` are populated — the "kis authority pe" and "kisko bheja" layer (§5A.3) | 3, 6 |
| **9** | System Logs UI, per-record "Record history" tab, legacy `busdata` tab, verify badge | 5, 6 |
| **10** | `audit:partitions`, `audit:coverage`, `audit:protect-check` + schedules and alerts | 1, 5 |
| **11** | **Case trail**: `case_trail()`, `v_current_pendency`, `v_stage_duration`; case-trail tab on every case screen (§5A.4) | 8 |
| **12** | **Backfill** the six existing trail tables into `syslog.events` as `source='backfill'` (§5A.5) | 11 |
| **13** | *(optional, separate from audit)* the three schema fixes in §5A.6 — `crr_acc_id`, `*_holder_id` on fund shifts and salary orders, current-holder on `purcases` / `ctrcases` | — |
| **14** | **Case snapshots**: `case_composition`, `case_snapshots`, `take_case_snapshot()`, hash de-duplication; `p_snapshot` on `begin_event` (§5B) | 11 |
| **15** | `snapshot_diff()`, `snapshot_doc()`, `v_case_custody`; attachment manifest written from PHP; custody tab on case screens showing "is officer ne kya kiya" | 14 |
| **16** | *(optional)* drive the case print/render from `snapshot_doc()` so "us waqt ka case" prints exactly as it was (§5B.9 limit 2) | 15 |
| **17** | One-time snapshot backfill: current state of every existing case as snapshot #1, clearly labelled | 14 |
| **18** | **File events** (§5C): `syslog.file_events`; `Audit::file()` on every upload / download / delete path so the **original filename**, size, MIME and sha256 are recorded | 3 |
| **19** | *(optional, separate from audit)* add `*_origname`, `*_bytes`, `*_mime`, `*_sha256`, `*_uploader_id`, `*_dtg` to the six legacy attachment tables so module screens can show them too (§5C.4) | — |
| **20** | **Business event catalogue** (§5D): map every table to `wt_object`, add the lifecycle `sev_action` values, and place `Audit::log()` calls on the project / head / allocation / commitment / transaction / transfer / account / employee / salary paths so `sev_authority_ref` and `sev_remarks` are populated and multi-table acts are grouped under one event | 4, 6 |
| **21** | **System Logs UI** (§5E) — replaces the smaller phase 9: permissions, route group, three levels, six tabs, print layout, presenters. Phase 9's record-history tab is the Level-3 Changes tab | 5, 6, 11, 14, 18 |
| **S** | **Staging rehearsal on `rdw03`** (§8A.5) — the full change set, every legacy form exercised. Runs **before** phase 4 and again before any optional `ALTER` | 1, 2 |

> Phase ordering for safety is set out in **§8A.8**, not here: `syslog` and its functions first with
> no triggers, then Laravel-only tables, then money tables, then the three legacy-touched tables with
> `wt_fail_open`, and the optional `ALTER`s last. Phase **S** is a gate, not a step.

Phases 0–2 are the foundation and are worth doing carefully; from Phase 4 onward each wave is
independently testable and independently useful.

---

## 10. Open questions to settle before Phase 1

1. **Schema name** — `syslog`, or something else by preference?
2. **Retention** — 7 years then archive-in-place, or truly forever?
3. **Who may read the trail** — IT Admin + Superadmin only, or also DG / MD / DLOG? Should a user be
   able to see their *own* history?
4. **Scope** — all 120 business tables, or Waves 1–2 only? (Excluded in any case: `cache`,
   `cache_locks`, `sessions`, `jobs`, `job_batches`, `failed_jobs`, `migrations`.)
5. **Forced reason** — which actions must refuse to save until the user types a reason, so
   `sev_remarks` is never empty? Suggested minimum: delete, any `fin` amount change, role/permission
   change, account enable/disable.
6. **`hr.leaves` has no primary key.** Add a surrogate key, or accept snapshot-only auditing there?
7. **Legacy Access app** — can it be changed to issue one `set_config('rdwis.username', ...)` after
   connecting? That one line turns all its writes from anonymous into attributed.
8. **Seal interval** — hourly (recommended) or daily?
9. **Off-box location** — reuse the existing backup share from `storagebackup_share.py`, or a
   separate write-once location?
10. **`aud.busdata`** — freeze it as a read-only archive now that the trail replaces it, or keep
    letting the legacy app append to it until that app is retired?
11. **Case trail: one register or six?** Should the module screens keep reading their own tables
    (`purdecisions`, `ctrcaseremarks`, …) with `syslog` as a cross-module register on top — the
    design's assumption — or should the modules be migrated to read `syslog.case_trail()` and the old
    tables frozen? The second is cleaner but touches every case screen.
12. **Forwarding without remarks** — should "forward / send to" refuse to submit unless remarks are
    typed? Given 13 of 2,004 purchase cases have any trail at all (§5A.2), making remarks mandatory
    is the only thing that will actually populate the register going forward.
13. **Backfill labelling** — how should imported rows be shown so nobody mistakes them for
    chain-protected evidence? Suggested: a grey "imported history" chip and no IP column for them.
14. **Snapshot trigger points** — only on custody change (`forwarded`, `returned`, `approved`,
    `rejected`, `released`, `cancelled`, `reopened`), which is the design's assumption, or also at a
    daily close, or on demand from the UI?
15. **Which modules get snapshots?** All six, or start with purchase + contract + generic (the three
    with real case volume: 2,004 / 275 / —) and add `fin` later?
16. **Snapshot retention** — keep every handover snapshot forever (≈7–12 MB/year, §5B.8 says this is
    affordable), or keep first + last + changed-only after N years?
17. **Attachment bytes** — accept "hash proves substitution, bytes are not recoverable" (§5B.6), or
    move to content-addressed attachment storage so the old file is also kept? The second is a
    bigger change and affects 7.5 GB of existing files.
18. **`pur.purcaseminutes.pcm_purcases`** is a `varchar` link with a UNIQUE constraint, not an
    integer FK (§5B.1). Fix it to a proper FK, or leave the minute sheet out of snapshots?
19. **Download logging** — log every attachment download (7.5 GB of files, currently nobody knows who
    has opened what), or only downloads of files on severity-1/2 objects? Logging all is cheap and
    recommended.
20. **Original filenames** — add the six columns in §5C.4 to the legacy attachment tables, or let
    `syslog.file_events` be the only place the original name lives? If the latter, the case screens
    can never display it.
21. **Mandatory authority reference for money events** — should changing `fin.subheads.sbh_alloc`, or
    creating a transfer or transaction, refuse to save without `sev_authority_ref` (sanction / letter
    number)? Recommended yes for allocation changes and transfers.
22. **The nine legacy tables with no `created_by` / `created_at`** (§5D.1) — add those columns as a
    separate exercise so the business tables themselves carry it, or rely entirely on `syslog`?
    `syslog` is sufficient for audit; the columns only help module screens and reports.
23. **How was the legacy app written — MS Access or Delphi/BDE?** (§8A.4) This is the one unknown that
    cannot be answered from this repository, and it decides whether the optional `ALTER`s need a
    Linked Table Manager refresh on each client machine, a query change in the legacy forms, or
    nothing at all. The `rdw03` rehearsal (§8A.5) answers it empirically either way.
24. **Do the three active legacy forms use `INSERT ... VALUES` without a column list?** (§8A.4) If
    yes, no column may be added to `fin.contractsverif`, `fin.empeffheads` or `cen.globalvars` until
    the legacy query is changed. The audit trail does not need those columns, so this blocks nothing
    essential.
25. **Who may see the System Logs section?** (§5E.1) `SUPERADMIN` + `IT_ADMIN` + `IT_OFFICER` as
    proposed, or should command officers (DG / MD / DDG / DLOG) also have `syslog.view`? And should a
    user be able to see their own activity?
26. **Fail-open period for the legacy tables** (§8A.6) — one month as recommended, or longer? And who
    receives the `syslog.trigger_failures` alert?
