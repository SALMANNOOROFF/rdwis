# RDWIS 2.0 — System Audit Trail (System Logs)

**Discovery + proposed design**
Date: 10 November 2026

> **Superseded in part.** Part 3 of this document (the design) proposed new tables inside the
> existing `aud` schema. That has been replaced by a dedicated new schema, designed in full in
> [SYSTEM_LOGS_SCHEMA_DESIGN.md](SYSTEM_LOGS_SCHEMA_DESIGN.md) — `aud` stays untouched for the
> Reversal / Data-Revision workflow. **Parts 1 and 2 below (what exists today, and the gaps) remain
> the current findings** and are the evidence base for that design.
Codebase: `H:\RDWIS 2.0\RDWIS APP 2.0` — Laravel 12, PHP 8.2
Databases inspected: PostgreSQL on `127.0.0.1:5433` — `rdw02`, `rdw03`, `rdw04`, `rdw01`, `liverdw`, `rdw`

> **Which DB is live?** `.env` has `DB_DATABASE=rdw04`, and the RBAC report (9 Nov 2026) also names
> `rdw04` as live. `rdw02` is an older copy: 135 tables, missing the `gen` schema (generic cases)
> that `rdw04` has (140). Figures below are from `rdw02` unless marked; the schema shape is
> identical in both.

---

## PART 1 — What exists today (verified)

### 1.1 `aud.busdata` — legacy field-level audit trail (NOT used by Laravel)

| Column | Type | Note |
|---|---|---|
| `bdt_id` | integer identity | |
| `bdt_username` | varchar NOT NULL | who (username string, no FK) |
| `bdt_dtg` | timestamp(0) | when |
| `bdt_action` | varchar NOT NULL | `Edit` etc. |
| `bdt_formname` | varchar NOT NULL | `fin_contractsverif` |
| `bdt_controlname` | varchar NOT NULL | |
| `bdt_fieldname` | varchar NOT NULL | `cvf_verif` |
| `bdt_recordid` | varchar NOT NULL | `966` |
| `btd_oldvalue` | varchar NOT NULL | `0` |
| `btd_newvalue` | varchar NOT NULL | `1` |
| `bdt_empname` | varchar NOT NULL | authority snapshot |
| `bdt_empdesig` | varchar NOT NULL | authority snapshot |

- **1,380 rows**, range `2021-01-20 16:43` → `2026-10-02 10:56`.
- Written by the **legacy Access/Delphi app**, still in use for contract verification.
- The Laravel app writes **zero** rows to it. The only mention in the whole codebase is a comment at
  `app/Services/FinanceVerificationService.php:81` — *"Legacy RecordBusDataAudit is a generic
  cross-form field-diff logger with no RDWIS equivalent yet"*.
- Has **no IP**, **no FK to `cen.accounts`**, and **no immutability** (plain table, no trigger).

### 1.2 `aud.revs` / `aud.revdata` / `aud.revcomps` — Data Revision (Reversal) workflow

A **formal authorised-change workflow**, not automatic logging.

| Table | Rows | Holds |
|---|---|---|
| `aud.revs` | 527 | header: object, object id, reason, status (Draft / In Process / Fulfilled / Cancelled), owning unit, initiating unit, date, type, reference |
| `aud.revdata` | 1,252 | field diffs: table, row id, attribute, **old value, new value**, datatype, conversion |
| `aud.revcomps` | — | cascade components for full/linked reversals |
| `aud.audattachments` | — | supporting documents |

Code: `app/Models/AudRev*.php`, `app/Services/DataRevisionService.php`,
`app/Http/Controllers/AdminReversalController.php`, `app/Enums/RevType.php`
(FULL_CASCADE / FIELD_LEVEL / LINKED_CASCADE), `app/Policies/DataRevisionPolicy.php`.

So "on whose authority was data changed" **is** captured — but **only when someone deliberately
raises a reversal request**. A normal day-to-day edit leaves no trace here.

### 1.3 Per-module activity / history tables (timeline, not field diffs)

| Table | Rows | What it records | IP? | Old/new values? |
|---|---|---|---|---|
| `sup.ticket_activities` | 14 | actor id + name + **role**, action, message, attachment, created_at | ✗ | ✗ |
| `gen.case_movements` *(rdw04)* | — | actor id/name/rank/desig, action, from/to stage, from/to holder, remarks, dtg | ✗ | ✗ |
| `gen.case_notes` *(rdw04)* | — | notes on generic cases | ✗ | ✗ |
| `prj.prghistory` | 1,385 | progress entries, author (string), status, level, trail | ✗ | ✗ |
| `doc.document_history` | 11 | document actions | ✗ | ✗ |
| `doc.document_versions` | — | versions, `action_by` → FK `cen.accounts` | ✗ | partial (whole file) |
| `public.project_activities` | **0** | action, details, user (string) — built, never used | ✗ | ✗ |
| `pur.purdecisions`, `fin.fund_shift_decisions`, `fin.sal_order_case_decisions`, `hr.ctrcaseremarks`, `hr.ctrcaseminutes`, `*_substatus` | — | approval decisions / minutes per module | ✗ | ✗ |

These are **good workflow histories** — they answer "where did the case move and who sent it".
They do **not** answer "what was this field's value, what is it now, and from which IP was it
changed".

### 1.4 `hrforms.audit_log` — written in code, **does not exist in any database**

`app/Models/HrForms/FormAuditLog.php` is actually a well-designed audit model: insert-only guards
(`updating` / `deleting` throw `RuntimeException`), `old_values` / `new_values` JSON, `ip_address`,
`user_id`, `user_name`, `created_at`.

But the schema is missing everywhere:

```
schema 'hrforms' table count —  rdw01: 0   rdw02: 0   rdw03: 0   rdw04: 0
```

There is no migration and no `.sql` file for it. So this is **planned / dead code** — 20 files
reference `hrforms.*` and would fail at runtime.

---

## PART 2 — The gaps

| # | Gap | Evidence |
|---|---|---|
| G-1 | **No system-wide change log.** No table that an ordinary edit writes to. | §1.1–1.4 |
| G-2 | **IP is not recorded anywhere.** Only 3 files in `app/` call `request()->ip()`: `PurchaseController.php`, `RestrictNetworkAccess.php` (network allow-list, not a log), and `FormAuditLog.php` (dead). No table has an IP column. | `grep -rn "request()->ip()" app/` |
| G-3 | **No login / logout / failed-login log.** `AuthController::login()` calls `Auth::attempt()` and writes nothing. `public.sessions` has 0 rows. | `app/Http/Controllers/AuthController.php:22-77` |
| G-4 | **Nothing is immutable.** `information_schema.triggers` = **0 rows**. `pg_rules` = **0 rows**. Even `aud.busdata` can be freely `UPDATE`d / `DELETE`d. | query output |
| G-5 | **App connects as superuser.** The only login role on the server is `postgres` (`rolsuper = t`), and that is what `.env` uses. Any SQL injection, stray script, or anyone with the password can rewrite history. **This is the single biggest blocker to "uneditable".** | `pg_roles`, `.env` |
| G-6 | **Write paths bypass Eloquent.** 319 Eloquent mutations vs **970 raw `DB::table()` / `DB::statement()` writes** across 57 controllers + services. A PHP-only (model-event) solution would miss two-thirds of all writes. | `grep -c` over `app/Http/Controllers`, `app/Services` |
| G-7 | **No `updated_at` on business tables.** Most models set `public $timestamps = false;` — the system cannot even say when a row was last touched. | `app/Models/*.php` |
| G-8 | **Authority at time of action is not frozen.** `cen.accounts.acc_desig` is an FK to `cen.roles.rol_desig` **ON UPDATE CASCADE** — renaming a designation silently rewrites who held what authority, retroactively. | `\d cen.accounts` |
| G-9 | **Legacy app keeps writing.** `aud.busdata` has rows up to Oct 2026, so two applications write the same data. Any PHP-only audit is blind to the legacy one. | §1.1 |

**Bottom line:** `aud.revs` / `aud.revdata` gives *approved* changes. `aud.busdata` gives a *legacy*
field-diff log the new app doesn't feed. Module activity tables give *case movement*. What is
missing is exactly the thing asked for: **an automatic, uneditable log of every change — timestamp +
IP + user + authority + old value + new value.**

---

## PART 3 — Proposed design

### 3.1 New database? — **No.**

Same database (`rdw04`), inside the **existing `aud` schema**. Reasons:

1. The audit row must be written **in the same transaction** as the change. If the business write
   rolls back but the log doesn't (or vice versa), the log is a lie. PostgreSQL has no cross-database
   transactions — a separate DB would need 2PC or a queue, and a queue means logs can be lost.
2. FK integrity to `cen.accounts` / `cen.units` only works inside one database.
3. Reporting joins ("show me every change on case PUR-2026-114") need one database.
4. Immutability does **not** come from physical separation — it comes from **privileges + triggers +
   a hash chain**. A separate database the app can still write to is no safer.

What *does* go outside: a **nightly append-only export** of new audit rows to the backup share
(alongside the existing `storage_backup.py` / `storagebackup_share.py`), so there is an off-box copy.

### 3.2 Tables

```sql
CREATE TABLE aud.trail (
  atr_id            bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  atr_dtg           timestamptz   NOT NULL DEFAULT clock_timestamp(),

  -- WHO (snapshot - frozen, never cascades)
  atr_acc_id        integer       REFERENCES cen.accounts(acc_id),  -- null = system/cron/legacy
  atr_username      varchar(255)  NOT NULL,
  atr_name          varchar(255),
  atr_rank          varchar(255),
  atr_desig         varchar(255),     -- designation AT THE TIME (fixes G-8)
  atr_desigshort    varchar(255),

  -- AUTHORITY (snapshot)
  atr_unt_id        integer,
  atr_untname       varchar(255),
  atr_untarea       varchar(50),
  atr_level         smallint,
  atr_role_slug     varchar(60),      -- COMMAND_DG / IT_ADMIN / ...
  atr_auth          varchar(30),      -- approver / staff
  atr_authority_ref varchar(255),     -- sanction / letter no / aud.revs.rev_id

  -- WHAT
  atr_action        varchar(40)   NOT NULL,  -- created|updated|deleted|approved|returned|
                                             -- released|cancelled|login|login_failed|logout|
                                             -- denied|exported|printed|downloaded|uploaded
  atr_object        varchar(60)   NOT NULL,  -- purcase|ctrcase|salorder|gencase|employee|account|role
  atr_objid         varchar(100),            -- business key
  atr_objref        varchar(200),            -- human-readable case no
  atr_stage_from    varchar(60),
  atr_stage_to      varchar(60),
  atr_remarks       text,                    -- the "why", as typed by the user

  -- WHERE FROM
  atr_ip            inet,
  atr_fwd_ip        varchar(255),            -- X-Forwarded-For chain (behind Caddy)
  atr_user_agent    varchar(500),
  atr_route         varchar(200),
  atr_method        varchar(10),
  atr_url           varchar(1000),
  atr_session_id    varchar(100),
  atr_request_id    uuid,                    -- correlates with laravel.log
  atr_source        varchar(20)   NOT NULL DEFAULT 'app',  -- app|legacy|console|db

  atr_result        varchar(20)   NOT NULL DEFAULT 'ok',   -- ok|failed|denied

  -- TAMPER EVIDENCE
  atr_prev_hash     char(64),
  atr_hash          char(64)
);

CREATE INDEX idx_trail_dtg    ON aud.trail (atr_dtg DESC);
CREATE INDEX idx_trail_acc    ON aud.trail (atr_acc_id, atr_dtg DESC);
CREATE INDEX idx_trail_obj    ON aud.trail (atr_object, atr_objid, atr_dtg DESC);
CREATE INDEX idx_trail_action ON aud.trail (atr_action);
CREATE INDEX idx_trail_ip     ON aud.trail (atr_ip);

CREATE TABLE aud.trail_data (
  atd_id       bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  atd_atr_id   bigint NOT NULL REFERENCES aud.trail(atr_id),
  atd_schema   varchar(30)  NOT NULL,   -- pur
  atd_table    varchar(60)  NOT NULL,   -- purcases
  atd_pk       varchar(60)  NOT NULL,   -- pcs_id
  atd_rowid    varchar(100) NOT NULL,   -- 1143
  atd_column   varchar(60)  NOT NULL,   -- pcs_status
  atd_label    varchar(150),            -- "Case Status" (for the UI)
  atd_old      text,
  atd_new      text,
  atd_datatype varchar(30),
  atd_op       char(1) NOT NULL         -- I | U | D
);

CREATE INDEX idx_trail_data_hdr ON aud.trail_data (atd_atr_id);
CREATE INDEX idx_trail_data_row ON aud.trail_data (atd_schema, atd_table, atd_rowid);
```

One header row per action (per HTTP request), N detail rows — one per changed field. A delete writes
every column as `atd_old` with `atd_op = 'D'`, so the record is fully reconstructible.

Login / logout / failed-login / permission-denied / export / print events go in `aud.trail` with no
detail rows — no separate table needed, and they then appear in the same timeline as data changes.

### 3.3 Immutability — four layers (this is the "uneditable" part)

**Layer 1 — Privileges (fixes G-5, must come first).** Create a non-superuser login role for the app
and switch `.env` to it:

```sql
CREATE ROLE rdwis_app LOGIN PASSWORD '...';
GRANT USAGE ON SCHEMA aud, cen, pur, hr, fin, prj, doc, sup, gen, frm, ina, puritems, purnew, public TO rdwis_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA cen, pur, hr, fin, prj, doc, sup, gen, frm, ina, puritems, purnew, public TO rdwis_app;

-- audit tables: append-only
GRANT SELECT, INSERT ON aud.trail, aud.trail_data TO rdwis_app;
REVOKE UPDATE, DELETE, TRUNCATE ON aud.trail, aud.trail_data FROM rdwis_app;
```

Without this, nothing else is real — a superuser connection can undo any protection.

**Layer 2 — Triggers (blocks even a privileged mistake).**

```sql
CREATE FUNCTION aud.no_change() RETURNS trigger LANGUAGE plpgsql AS $fn$
BEGIN
  RAISE EXCEPTION 'aud.% is append-only: % is forbidden', TG_TABLE_NAME, TG_OP;
END
$fn$;

CREATE TRIGGER trail_immutable BEFORE UPDATE OR DELETE ON aud.trail
  FOR EACH ROW EXECUTE FUNCTION aud.no_change();
ALTER TABLE aud.trail ENABLE ALWAYS TRIGGER trail_immutable;  -- survives session_replication_role
-- same for aud.trail_data
```

**Layer 3 — Hash chain (detects tampering done *around* layers 1–2).** Every row stores
`atr_hash = sha256(atr_prev_hash || canonical_payload)`. If somebody drops the trigger, edits a row
and recreates the trigger, the chain breaks at that row. `php artisan audit:verify` walks the chain
and reports the first broken link plus a count. A verification badge on the System Logs screen shows
green, or "chain broken at #N".

**Layer 4 — Off-box WORM copy.** A nightly job exports rows added since the last export to an
append-only file on the backup share, recording the last hash. Restores can be compared against it.

### 3.4 How every change gets captured (fixes G-6 and G-9)

Two mechanisms, deliberately — neither alone is enough.

**(a) PostgreSQL row triggers = the backbone.** One generic trigger function attached to each
business table. It catches *every* write: Laravel Eloquent, the 970 raw `DB::table()` calls, the
legacy Access app, cron scripts, and manual `psql`. The "who" comes from session variables the app
sets at the start of each request:

```php
// app/Http/Middleware/AuditContext.php  (one middleware, whole app)
DB::statement(
    "SELECT set_config('rdwis.acc_id', ?, false), set_config('rdwis.username', ?, false),
            set_config('rdwis.ip', ?, false), set_config('rdwis.route', ?, false),
            set_config('rdwis.request_id', ?, false)",
    [...]
);
```

```sql
-- inside the trigger
v_acc := nullif(current_setting('rdwis.acc_id', true), '')::int;  -- null => legacy/console, logged as such
```

Attach in waves, highest value first:

- `cen.accounts`, `cen.roles`, `cen.role_permissions`, `cen.system_settings`
- `pur.purcases`, `pur.purcaseitems`, `pur.purdecisions`
- `hr.ctrcases`, `hr.contracts`, `hr.emps`, `hr.salreqs`, `hr.attendance`
- `fin.salorders`, `fin.commitments`, `fin.transactions`, `fin.fund_shifts`, `fin.contractsverif`
- `gen.cases`, `prj.projects`, `prj.milestones`, `doc.documents`

**(b) Application-level `Audit::log()` = the business meaning.** The database can see
`pcs_status: 3 → 4`; it cannot know this was *"approved by DLOG on authority of sanction letter
NRDI/LOG/114"*. So at every approval / return / release / cancel point, the service layer writes an
`aud.trail` header with `atr_action`, `atr_authority_ref`, `atr_remarks`, and stage from/to — and the
trigger's field diffs attach to that same header via `atr_request_id`.

An `Auditable` trait on Eloquent models gives the same for ordinary create / update / delete without
touching each controller.

### 3.5 Admin UI — "System Logs"

- Route under IT Admin / Superadmin only (`cen.role_permissions`), read-only — **no delete button
  anywhere in the UI**, also enforced by layers 1–2.
- Filters: date range, user, unit/area, module, action, case reference, IP, result.
- Each row expands into its field diffs (old → new, with human labels).
- A "Record history" tab on each case / employee screen: every `aud.trail` row for that object, in
  order — the audit trail an officer actually wants to see.
- Export to CSV / PDF, and the export itself is logged (`atr_action = 'exported'`).
- Chain-verification badge from `audit:verify`.
- A read-only **Legacy** tab mapping `aud.busdata` into the same view, so 2021→2026 history stays
  visible in one place.

### 3.6 Volume

57 accounts, 2,004 purchase cases, 1,380 legacy audit rows in ~5 years. Even at 500 logged actions
per working day with 5 field diffs each, that is ~130k header + ~650k detail rows per year — trivial
for PostgreSQL. Monthly partitioning on `aud.trail` is optional and can be added later; indexing
`atr_dtg` is enough for now. Nothing is ever deleted.

---

## PART 4 — Phased plan

| Phase | Work | Risk |
|---|---|---|
| **0** | Create `rdwis_app` non-superuser role, grant per-schema privileges, switch `.env`, regression-test | Medium — must verify every module still writes. **Do this first; everything else depends on it.** |
| **1** | Migration: `aud.trail`, `aud.trail_data`, immutability triggers, hash-chain function, `audit:verify` command | Low — additive |
| **2** | `AuditContext` middleware (request id, IP, user, route → PG session vars) + `Audit::log()` service | Low |
| **3** | Generic PG trigger function, attached to wave-1 tables (`cen.*`, `pur.purcases`, `fin.salorders`, `hr.ctrcases`, `gen.cases`) | Medium — test each write path |
| **4** | Auth events: login, logout, failed login, forced password change, permission denied | Low |
| **5** | Attach trigger to remaining tables (waves 2–3) | Medium |
| **6** | `Auditable` trait + explicit `Audit::log()` at approval / decision points for reason + authority ref | Low |
| **7** | System Logs UI + per-case "Record history" tab + legacy `busdata` view | Low |
| **8** | Nightly WORM export + `audit:verify` in the nightly job | Low |

### Decisions needed before Phase 1

1. **Retention** — never delete (recommended), or archive-and-detach after N years?
2. **Read access** — IT Admin + Superadmin only, or should DG / MD / DLOG also see the full log?
3. **Scope of "every change"** — all 140 tables, or business tables only (excluding `cache`,
   `sessions`, `jobs`, `failed_jobs`)? Recommended: business tables only.
4. **Reason-on-edit** — should sensitive edits *force* the user to type a reason before saving, so
   `atr_remarks` is never empty? Which modules?
5. **`hrforms` schema** — is that module being built? Its `FormAuditLog` should be folded into
   `aud.trail` rather than kept as a parallel audit table.
6. **Legacy app** — will it keep writing to `rdw04` after this? If yes, Phase-3 triggers will log it
   as `atr_source = 'legacy'` with a null `acc_id` (username still resolvable if the legacy app also
   sets the session variable).
