# RDWIS 2.0 — `syslog` Audit Trail: Implementation Notes

**LIVE on `rdw04` since 10 November 2026. Also installed on `rdw03` (staging).**

Installing on a new machine is one command — see §2.

Design: [SYSTEM_LOGS_SCHEMA_DESIGN.md](SYSTEM_LOGS_SCHEMA_DESIGN.md)
Discovery: [AUDIT_TRAIL_PLAN.md](AUDIT_TRAIL_PLAN.md)

---

## 1. What was built

### SQL — `database/sql/syslog/` (the source of truth; idempotent, re-runnable)

| File | Contents |
|---|---|
| `01_schema_tables.sql` | `syslog` schema, 5 sequences, **11 tables**, all indexes, lz4 on snapshots |
| `02_partitions_immutability.sql` | `fn_deny_change()`, `protect()`, `ensure_partitions()`; builds the monthly partitions |
| `03_functions.sql` | `event_hash()`, `begin_event()`, `end_event()`, `ensure_event()`, **`fn_audit_row()`**, `fn_audit_truncate()`, `attach_audit()`, `detach_audit()`, DDL event triggers |
| `04_snapshots_seals.sql` | `take_case_snapshot()`, `snapshot_doc()`, `snapshot_diff()`, `seal_range()`, `verify_seals()`, `restore_row()` |
| `05_views.sql` | `categories` table + `v_timeline`, `v_category_activity`, `record_history()`, `case_trail()`, `v_case_custody`, `v_current_pendency`, `v_stage_duration`, `v_coverage`, `v_composition_drift`, `v_legacy_busdata`, `v_unowned_writes` |
| `06_seed_config.sql` | auto-registers every business table, then sets object / snapshot mode / severity / redaction / **human labels**; seeds `case_composition` and `categories` |
| `07_roles_grants.sql` | `rdwis_audit_owner`, `rdwis_app`, `rdwis_auditor` (created **NOLOGIN, no password** on purpose) and the grants |
| `08_attach_triggers.sql` | `attach_wave(1..4)`, `attach_all()`, `detach_all()` |
| `09_reconstruct.sql` | snapshot-diff recovery: `reconstruct_event()`, `snapshot_runs`, `v_deletion_evidence` (see [SYSLOG_HISTORY_RECOVERY.md](SYSLOG_HISTORY_RECOVERY.md)) |
| `10_backfill.sql` | `ensure_history_partitions()`, `safe_dtg()`, `backfill_event()`, `backfill_marks` |
| `11_backfill_sources.sql` | the eight backfill sources + `backfill_all()` |

### Tables created (11)

`events` · `row_changes` · `field_changes` · `case_snapshots` · `file_events` (all five
partitioned monthly) · `watched_tables` · `case_composition` · `categories` · `ddl_events` ·
`seals` · `verify_runs` · `trigger_failures`

### PHP

```
app/Http/Middleware/AuditContext.php              request identity -> PG session vars
app/Services/Syslog/Audit.php                     log(), forward(), file(), login(), denied(), end()
app/Services/Syslog/LogQueryService.php           every read query, keyset paginated
app/Services/Syslog/LogPresenter.php              labels, money/date/bool formatting, chips
app/Http/Controllers/Admin/SystemLogController.php  home/category/record/event/print/export/verify
app/Console/Commands/AuditMaintenanceCommand.php  audit:run <task>
database/migrations/2026_11_10_120000_create_syslog_schema.php
```

Modified: `bootstrap/app.php` (middleware), `routes/web.php` (7 GET routes),
`routes/console.php` (aliases + schedule), `app/Services/Auth/PermissionRegistry.php`
(`syslog.*`), `app/Services/Auth/RolePermissionMap.php` (IT_ADMIN, IT_OFFICER),
`resources/views/welcome.blade.php` (sidebar link under System Admin).

### UI — `resources/views/admin/syslog/`

`_styles.blade.php` · `_event_row.blade.php` · `home.blade.php` (Level 1 tiles) ·
`category.blade.php` (Level 2 list + filters) · `record.blade.php` (Level 3, six tabs) ·
`event.blade.php` · `legacy.blade.php` · `health.blade.php` · `print.blade.php`

Built on the project's own theme: `@extends('welcome')`, AdminLTE/Bootstrap 4 classes,
slate palette, `#2563eb` accent, 8px radius, `#e2e8f0` borders, Font Awesome 5.
**No new front-end dependency.**

### Permissions

`syslog.view` · `syslog.view_all` · `syslog.export` · `syslog.verify` · `syslog.restore`
Granted to `SUPERADMIN` (via `PermissionRegistry::all()`), `IT_ADMIN` (all five),
`IT_OFFICER` (`view`, `view_all`, `export`).

---

## 1A. Backfill — the past, rebuilt from what the business tables already hold

The trail only records from the day its triggers go live. But the business tables were already full
of real, dated history that had never been collected anywhere. `audit:backfill` turns it into events
with their **real dates**, marked `sev_source = 'backfill'`.

```bash
php artisan audit:backfill            # all sources
php artisan audit:backfill --source=purchase
```

### Result on `rdw03` — **48,962 events, 2015 → 2026, in 5.7 seconds**

| Source | Events | Built from |
|---|---|---|
| `salary` | 21,849 | `hr.salreqs` + `fin.salorders` release/close + `sal_order_case_decisions` |
| `finance` | 15,943 | `cen.heads` open/close · `fin.commitments` · `fin.transactions` · `fin.transfers` · `fund_shift_decisions` |
| `purchase` | 5,992 | `pcs_date` / `releasedtg` / `approvedtg` / `closedtg` + `purdecisions` + `purcase_substatus` |
| `misc` | 1,741 | `cen.accounts` · `prj.projects` · `prghistory` · `gen.case_movements` · `ticket_activities` · `document_history` |
| `legacy` | 1,380 | `aud.busdata` — real old/new field values, 2021 → 2026 |
| `contract` | 869 | `ctc_date` / `releasedtg` / `closedtg` / `termindt` (**with `ctc_createdby` / `ctc_releasedby`**) + `ctrcaseremarks` + `ctrcase_substatus` |
| `hr` | 664 | `hr.emps` join/last date + `fin.empeffheads` head changes |
| `revisions` | 524 | `aud.revs` + `aud.revdata` — authorised revisions with old/new values |

### By year

```
2015: 557*  2016: 3     2018: 305   2019: 1,300  2020: 3,813  2021: 6,878
2022: 6,754 2023: 7,260 2024: 7,880 2025: 8,264  2026: 6,045
```

\* The 2015 bucket is **clamped bad data, not real history**. `syslog.safe_dtg()` pulls impossible
dates into range and records the original in the event's remark. What it caught:

| Original date | Rows | Source |
|---|---|---|
| **1011-01-01** | **524** | `pur.purcases.pcs_approvedtg` |
| 2001-01-01 | 32 | `cen.heads.hed_opendt` |
| 2010-04-17 | 1 | `prj.projects` |

**524 purchase cases carry an approval date of year 1011 in the live data.** That is a real
data-quality problem in the business tables, found by the backfill and worth fixing at source.

### Honest limits of backfilled history

- **The actor is known for only 1,671 of 49,045 rows (3.4%).** Everything else says
  `(not recorded)` — because it never was. The sources that *do* carry an actor are
  `hr.ctrcases.ctc_createdby/ctc_releasedby`, `pur.purdecisions.pdec_acc_id`,
  `fin.*_decisions.*_acc_id`, `gen.case_movements.gcm_actor_id`, `sup.ticket_activities.act_user_id`
  and `aud.busdata.bdt_username`.
- **No IP address, ever** — it was never recorded before this system.
- **Only the events the source tables happen to hold.** An edit that left no timestamp and no trail
  row is simply not there.
- **Backfilled rows are outside the hash chain.** `seal_range()` and the chain check in
  `verify_seals()` exclude `sev_source = 'backfill'` — they are imported data, not evidence the
  system witnessed. Their **per-event hash is still verified**, so a backfilled row edited after
  import is still detected.
- **Run `audit:backfill` BEFORE the first `audit:seal`.** Backfilled rows carry past dates, so
  importing them after sealing drops rows into already-sealed windows.

### Partitions for history

`syslog.ensure_history_partitions(2015)` creates **yearly** partitions for 2015–2025 and
`ensure_partitions()` keeps **monthly** ones from this year on; a partitioned table may mix bound
widths. 140 table partitions in total on `rdw03`. Planning cost is ~130 ms per query against the
partitioned parent, execution ~170 ms for the landing page — acceptable, and narrowing the date
filter prunes immediately.

---

## 2. Install / rollback

### Install on a new machine — one command

```bash
php artisan migrate
```

That is the whole thing. Two migrations do it:

| Migration | What it does |
|---|---|
| `2026_11_10_120000_create_syslog_schema` | runs `database/sql/syslog/01..11` — schema, 12 tables, functions, views, seed config, grants. **Attaches nothing**, so running it changes no behaviour. |
| `2026_11_10_130000_activate_syslog_audit_trail` | 1. yearly + monthly partitions, and **refuses to continue** if no partition covers the current month · 2. attaches the triggers wave by wave (Laravel-only schemas → identity + money → hr → the 3 legacy-touched tables last) · 3. runs the backfill — **before any seal exists**, which is the required order · 4. prints the coverage result |

Nothing has to be done in the database by hand. Afterwards, confirm:

```bash
php artisan audit:coverage           # "Coverage complete"
```

```bash
php artisan audit:protect-check      # four OKs
```

Or apply the SQL directly, which is what was done on `rdw03`:

```bash
for f in database/sql/syslog/0*.sql; do psql -h 127.0.0.1 -p 5433 -U postgres -d rdw03 -f "$f"; done
```

### Live run on `rdw04` — 10 November 2026, 7.9 seconds

```
partitions: 55 yearly + 45 monthly created
audit triggers attached to 128 table(s)
  backfill purchase   6,633      backfill hr          670
  backfill contract   1,097      backfill misc      1,795
  backfill salary    22,199      backfill legacy    1,380
  backfill finance   16,266      backfill revisions   535
50,575 historical event(s) reconstructed
coverage complete: every business table has an always-on audit trigger
```

A `pg_dump` of `rdw04` was taken immediately before the migration.

| Category | Events | From | To |
|---|---|---|---|
| Salary | 22,199 | 2018-03-31 | 2026-11-05 |
| Finance / Money | 16,203 | 2015-01-01* | 2026-11-02 |
| Purchase Cases | 6,633 | 2015-01-01* | 2026-11-05 |
| Projects | 1,640 | 2015-01-01* | 2026-11-02 |
| System & Access (legacy) | 1,380 | 2021-01-20 | 2026-10-02 |
| Hiring / Contract Cases | 1,097 | 2025-02-12 | 2026-11-05 |
| Employees | 670 | 2018-08-01 | 2026-11-10 |
| Reversals | 535 | 2022-04-01 | 2026-11-05 |
| Accounts & Roles | 95 | 2020-05-12 | 2026-10-26 |
| Fund Shifting | 63 | 2026-10-21 | 2026-11-05 |
| Support Tickets | 56 | 2026-09-02 | 2026-11-04 |

\* clamped bad source dates — see §1A.

### Rollback (instant, keeps the logs already collected)

```bash
php artisan audit:detach --table=fin.contractsverif   # one table
php artisan audit:detach                              # every table
```

---

## 3. Staging test results (`rdw03`, 10 Nov 2026)

| Check | Result |
|---|---|
| Install 01–08 | clean |
| Partitions created | **40** (1 month back, 6 ahead, 5 tables) |
| Tables registered | **127 of 127** business tables |
| `audit:coverage` | **Coverage complete** — every table has an always-on trigger |
| `audit:protect-check` | deny-triggers ALWAYS ✓ · audit triggers ALWAYS ✓ · DDL event triggers present ✓ · no unexpected write grants ✓ |
| Deny-triggers | **84**, all `ENABLE ALWAYS` |
| Field diffs with human labels | ✓ `Allocation: 20,08,513 → 50,08,513`, `Case Status: Draft → Approved` |
| Delete capture | ✓ full row kept (`pci_pcs_id=2417,pci_serial=7`, 323 bytes) and **restored byte-for-byte with its original key** |
| Composite primary keys | ✓ stored as JSONB (`pci_pcs_id=2417,pci_serial=7`) |
| Case snapshot | ✓ composed 8 tables, 2 levels deep — 7 items, 1 quote, 7 quote items, 1 attachment, **4,630 bytes** |
| Case trail | ✓ stage from→to, sent-to officer, remarks, authority ref, IP |
| File event | ✓ original filename `Scan_Minute_Sheet_Case2417_signed.pdf` kept beside the stored path `/pur/min-pcs-2417.pdf` with sha256 |
| Auth events | ✓ `login`, `login_failed`, `denied` |
| Immutability | ✓ `UPDATE syslog.events` **refused even as the `postgres` superuser** |
| Tamper detection | ✓ trigger disabled → row edited → trigger re-enabled → `audit:verify` reported **"event 1 was modified: stored hash … recomputed …"**, and the two `ALTER TABLE` statements were in `ddl_events` |
| Seal + verify | ✓ 2 seals, chain intact |
| Trail holes | **0** |
| All 15 views render | ✓ home, 5 categories, 6 record tabs, snapshot diff, print sheet, event detail |
| Trail size after the test | 8 MB (mostly the 2,584 `ddl_events` rows from installing 254 triggers) |
| Staging business data | reverted to original — **and the revert is itself in the trail** |

### Four bugs found and fixed during the test

1. **`restore_row` failed on `GENERATED ALWAYS AS IDENTITY` columns.** Added
   `OVERRIDING SYSTEM VALUE`, so a restore puts the **original** key back rather than a fresh
   one — otherwise every child row that referenced it would be orphaned.
2. **Tampering was not detected.** The first hash covered only a handful of columns, so editing
   `sev_remarks` left it valid. Replaced with `syslog.event_hash(e)` = sha256 over
   `to_jsonb(e) - 'sev_hash'`, so **every column** is covered, and `verify_seals()` now
   recomputes each event's hash before checking the chain.
3. **`hr.jobs` was silently unaudited.** The exclusion list matched on table name, so
   `public.jobs` (the Laravel queue) also excluded `hr.jobs` (a business table). Now
   schema-qualified.
4. **Implicit events carried no record id**, so a delete did not appear on its record's
   timeline. `ensure_event()` now receives the row's primary key and reference column.

---

## 4. Current configuration on `rdw03`

- **127 tables audited.** 22 keep full before/after snapshots (`cen.accounts`, `cen.roles`,
  `cen.role_permissions`, `cen.heads`, `fin.subheads`, `fin.transactions`, `fin.transfers`,
  `fin.commitments`, `fin.salorders`, …). The rest keep field diffs plus a full snapshot on
  delete.
- **5 tables are fail-open** (`wt_fail_open = true`): `fin.contractsverif`,
  `fin.empeffheads`, `cen.globalvars` — the three the legacy Access app still writes to
  (§8A.2) — plus `hr.attendance` and `hr.attendanceremarks` as high-volume/low-risk.
  An audit error there records a row in `syslog.trigger_failures` and lets the business write
  proceed. **Switch these to fail-closed after a clean month.**
- `cen.accounts.acc_pass` is in `wt_redact_cols`: the log records *that* the password changed
  and who changed it, never the hash.
- `hr.leaves` has **no primary key**, so it is set to `always` snapshot. Adding a surrogate key
  would make its history linkable.
- `pur.purcaseminutes` is **excluded from snapshots**: `pcm_purcases` is a varchar list, not an
  integer FK (§5B.1).

---

## 5. Before putting this on `rdw04`

1. **`.env` is currently pointed at `rdw03`.** Switch back with `DB_DATABASE=rdw04` and
   `php artisan config:clear`. A copy of the original is at `.env.backup-before-syslog`.
2. `rdw03` was missing the `gen` schema; `php artisan migrate` created it there, so staging is
   now a closer match to `rdw04`.
3. **Exercise the legacy Access app against `rdw03`** before touching `rdw04` — point its
   connection string at `rdw03` and run contract verification, employee effective-head detail
   and salary global variables, plus every read-only form. This is the one thing the code cannot
   answer (§8A.4, open questions 23–24).
4. On `rdw04`, follow the wave order in §8A.8 and watch `syslog.trigger_failures` for a week
   after wave 4.
5. The app still connects as the `postgres` **superuser**. Until that changes (§6.1), the
   deny-triggers and hash chain make tampering *detectable*, not *impossible*. Enable the roles
   when ready:
   ```sql
   ALTER ROLE rdwis_app     WITH LOGIN PASSWORD '<strong>';
   ALTER ROLE rdwis_auditor WITH LOGIN PASSWORD '<strong>';
   ```
   then set `DB_USERNAME=rdwis_app`.
6. Add `log_connections = on` and `log_statement = 'ddl'` to `postgresql.conf` — a cheap,
   independent second record (§8.9).

---

## 6. Scheduled jobs (already registered in `routes/console.php`)

| Command | Schedule | Purpose |
|---|---|---|
| `audit:seal` | hourly | seal the trail + append-only export to `storage/app/private/audit-export/` |
| `audit:verify` | daily 02:10 | recompute every event hash and seal; non-zero exit on a break |
| `audit:partitions --months=3` | 1st of month, 01:30 | **critical** — a missing current-month partition fails every audited write, and the business write with it |
| `audit:protect-check` | 1st of month, 01:45 | triggers, grants and login roles |

These only run if Laravel's scheduler is running (`php artisan schedule:work`, or a Windows
Task Scheduler entry calling `php artisan schedule:run` every minute). **Verify that is set up** —
without it the seals never happen and the chain is never checked.
