# RDWIS 2.0 — Recovering the Past: What Can and Cannot Be Known

**Question:** purani history — jo add, enter, delete hui — uska record mil sakta hai?
**Answer: partly, and more than expected.** Not a real audit log, but a dated series of
full-database snapshots that recovers **what** changed. Not **who**, and not the exact **when**.

Date: 10 November 2026 · Companion to [SYSTEM_LOGS_SCHEMA_DESIGN.md](SYSTEM_LOGS_SCHEMA_DESIGN.md)
and [SYSLOG_IMPLEMENTATION.md](SYSLOG_IMPLEMENTATION.md)

---

## 1. What PostgreSQL itself kept — almost nothing

Every forensic source the server could have held was checked:

| Source | Setting / state | Usable? |
|---|---|---|
| `track_commit_timestamp` | **off** | ✗ No per-row last-modified timestamp exists. Had this been on, every row would carry the time of its last change. |
| `archive_mode` | **off** | ✗ No WAL archive. Nothing older than what is still on disk. |
| WAL on disk | 5 files, 80 MB, **2026-11-05 → 2026-11-10 only** | ~5 days, and raw tuple data without column names. A heavy forensic exercise for very little. |
| `log_statement` | **none** | ✗ No SQL was ever logged. |
| Server logs | `logging_collector = on`, but tiny files from 5 Nov | ✗ Connections and errors only, no DML. |
| `pg_stat_user_tables` lifetime ins/upd/del | **counters empty** (stats reset) | ✗ Would have given a count of rows ever deleted per table. Gone. |
| Table-level triggers / rules | **0** in the whole database | ✗ |
| `aud.busdata` | **1,380 rows, 2021-01-20 → 2026-10-02** | ✓ Real field-level history — but only for 6 legacy forms (§1.1 of the design doc). |
| `aud.revs` / `revdata` | **527 revisions, 1,252 field diffs** | ✓ Real old/new values — but only for changes deliberately put through the reversal workflow. |
| Module trail tables | `prghistory` 1,385 · `ctrcase_substatus` 346 · `purcase_substatus` 107 · `ctrcaseremarks` 75 · `purdecisions` 48 · `ticket_activities` 14 · `document_history` 11 | ✓ Workflow movement, no field values. |
| Row timestamp columns | `pcs_date/releasedtg/approvedtg/closedtg`, `srq_*dtg`, `sor_*dtg`, `hed_opendt/closedt`, `acc_startdt/enddt`, `emp_joindt/lastdt`, `eeh_dtg` | ✓ A **lifecycle timeline** per record, even without a log. |
| `hr.ctrcases.ctc_createdby` / `ctc_releasedby` | populated | ✓ The one place an actor was recorded for cases. |

**Conclusion:** the database kept no general audit log, and that cannot be undone. But two other
things turned out to be real evidence.

---

## 2. Evidence #1 — sequence gaps prove deletions happened

Every identity sequence remembers how many ids it ever handed out. Compare that with how many rows
are still there and the gap is rows that once existed.

`SELECT * FROM syslog.v_deletion_evidence ORDER BY max_rows_gone DESC;` on `rdw04`:

| Table | ids ever issued | rows now | **max rows gone** |
|---|---|---|---|
| `pur.quoteitems` | 39,853 | 29,589 | **10,264** |
| `ina.invats` | 17,669 | 12,071 | **5,598** |
| `pur.purcaseitems` | 18,229 | 14,903 | **3,326** |
| `hr.attendance` | 7,071 | 5,321 | **1,750** |
| `pur.quotes` | 4,754 | 3,201 | **1,553** |
| `hr.contractplans` | 7,055 | 5,687 | 1,368 |
| `hr.salreqs` | 6,305 | 5,166 | 1,139 |
| `hr.qualifs` | 1,662 | 702 | 960 |
| `hr.ctrcaseplans` | 3,790 | 3,004 | 786 |
| **`fin.commitments`** | 8,173 | 7,705 | **468** |
| `hr.contracts` | 1,117 | 709 | 408 |
| `hr.bnkaccounts` | 645 | 284 | 361 |
| **`fin.transactions`** | 8,317 | 8,123 | **194** |
| `aud.revs` | 694 | 535 | 159 |
| `fin.salorders` | 6,211 | 6,083 | 128 |

> **Honest limit.** This is an **upper bound**, not an exact count. A rolled-back `INSERT` and an
> `ON CONFLICT DO NOTHING` both consume a sequence value without leaving a row. So some of each gap
> is failed inserts rather than deletions. At these magnitudes — 10,264 on one table — it is still
> strong evidence that a great many rows were removed over the years. It tells you **that** and
> roughly **how many**, never **what** or **who**.

---

## 3. Evidence #2 — the snapshots. This is the real recovery.

There is a series of dated, complete copies of the database. Each one is a photograph of the whole
system on a particular day. Diffing two photographs recovers exactly what changed between them.

### Already restored and queryable on this server — `php artisan audit:snapshots`

| Database | Newest data | Purchase cases | Transactions | Employees | Tables |
|---|---|---|---|---|---|
| `liverdw` | 2026-05-07 | 1,816 | 7,547 | 252 | 93 |
| `rdw` | 2026-08-19 | 1,244 | 5,255 | 192 | 122 |
| `updatedrdwV1` | 2026-09-16 | 1,931 | 7,864 | 273 | 141 |
| `rdw01` | 2026-10-05 | 1,997 | 8,050 | 272 | 130 |
| `accessdev` | 2026-10-09 | 1,931 | 7,862 | 264 | 93 |
| `rdw02` | 2026-10-27 | 2,004 | 8,125 | 276 | 135 |
| **`rdw04`** | 2026-11-05 | 2,048 | 8,123 | 275 | 140 |

(`rdw` and `liverdw` have fewer rows despite their dates — they are a different lineage, not a
straight predecessor. Compare within a lineage for clean results.)

### pg_dump files, restorable into a scratch database

| Date | File |
|---|---|
| 2026-07-06 | `rdw05jul26.sql` |
| 2026-07-09 | `rdwrdw-202607082124.sql` |
| 2026-07-17 | `New folder/rdwrdw-202607170350.sql`, `New folder/rdwliverdw-202607170350.sql` |
| 2026-07-21 | `New folder/rdwrdw-202607211224.sql` |
| 2026-08-11 | `db/liverdw-202608110936.sql`, `db/rdw-202608110937.sql` |
| 2026-09-02 | `New folder/updatedrdwV1-202609020959.sql` |
| 2026-09-10 | `accessdev-202609101726.sql`, `updatedrdwV1-202609101726.sql` |
| 2026-10-01/02 | `D:\databackup2.0\2026-10-0*` (updatedrdwV1, several same-day runs) |
| 2026-10-06 | `2026-10-06_14-53-13/rdw01.sql` |

```bash
createdb -h 127.0.0.1 -p 5433 -U postgres snap_2026_07_06
psql -h 127.0.0.1 -p 5433 -U postgres -d snap_2026_07_06 -f rdw05jul26.sql
php artisan audit:compare --from=snap_2026_07_06 --to=rdw04
```

> **Check `E:\databackup2.0`.** The 6 October backup log says it wrote there
> (`E:\databackup2.0\2026-10-06_14-53-13`), and `db_backup.py` points at `D:\databackup2.0`. The
> E: drive is not reachable from this machine. **If it holds the full dated series, the timeline
> gets much denser** — possibly a snapshot every few days instead of every few weeks.

---

## 4. The tool

```bash
php artisan audit:snapshots                                     # what is available, as a timeline
php artisan audit:compare --from=rdw01 --to=rdw02                # summary, every table
php artisan audit:compare --from=rdw01 --to=rdw02 --table=fin.commitments --detail
php artisan audit:compare --from=rdw01 --to=rdw02 --import       # write it into the trail
```

How it works: for each table present in both snapshots with a primary key, it hashes every row
(`md5(jsonb_build_object(...))`) and compares the two sets — giving rows that **appeared**,
**disappeared** and **changed**. For changed and deleted rows it then pulls the full rows and diffs
them column by column.

**It hashes only the columns present in BOTH snapshots.** This matters: a migration that added a
column between the two dates would otherwise make every row look changed. Columns added or dropped
are reported separately in a "Schema change" column. Before this correction the run below reported
14,198 changed rows; the true figure is 2,863.

`--import` writes the findings through `syslog.reconstruct_event()` as `sev_source = 'backfill'`,
actor `(reconstructed)`, severity 2 for deletions, with the snapshot window in
`sev_stage_from`/`sev_stage_to` and the **full deleted row in `rc_old_row`**. The System Logs screen
labels these rows "Imported History". Each run is recorded in `syslog.snapshot_runs`.

---

## 5. Proven result — `rdw01` (5 Oct) vs `rdw02` (27 Oct), 22 days

```
Recovered: 932 rows appeared, 120 DISAPPEARED, 2,863 changed, across 50 tables.
```

Highlights from that window:

| Table | Appeared | Disappeared | Changed |
|---|---|---|---|
| `fin.transactions` | 75 | — | — |
| `fin.commitments` | 81 | **2** | — |
| `pur.purcases` | 7 | — | 13 |
| `pur.quoteitems` | 81 | **3** | 4 |
| `ina.invats` | — | **18** | — |
| `ina.invatcomps` | — | **18** | — |
| `pur.purreceiptitems` | — | **24** | — |
| `hr.ctrcaseplans` | 82 | **37** | 6 |
| `hr.emps` | 4 | — | 14 |
| `hr.attendance` | 19 | — | 643 |
| `hr.ctrcaseremarks` | 58 | — | 10 |
| `pur.purdecisions` | 41 | — | 7 |

### The two deleted commitments, recovered in full

```
cmt_id=7927  cmt_date=2026-09-16  cmt_type=Ps  cmt_docid=2369
             cmt_amount=-397,810  cmt_hed_id=350055  cmt_status=Awaited  cmt_unt_id=350000

cmt_id=7937  cmt_date=2026-10-09  cmt_type=Ps  cmt_docid=2397
             cmt_amount=-80,860   cmt_hed_id=200019  cmt_status=Awaited  cmt_unt_id=200000
```

### And from `rdw02` (27 Oct) vs `rdw04` (live) — two payments gone

```
trn_id=8304  trn_date=2026-10-21  amount=-40,000    balance=0  commitment=7938
trn_id=8305  trn_date=2026-10-21  amount=-154,828   balance=0  commitment=7937
```

Their commitments (`cmt_docid` 6026 and 6027, both `Sa` = salary, status **Paid**, heads 400003 and
200010) were deleted too — `SELECT` on `rdw04` returns 0 rows for them.

**Worth checking:** `aud.revs` and `aud.revdata` on `rdw04` contain **no entry** for ids 6026, 6027,
7937, 7938, 8304 or 8305. So these removals were not recorded through the Data Revision workflow.
That may simply mean the workflow references them by another key — but it is exactly the kind of
question that, from now on, the trail answers by itself.

Also recovered in the same window: **65 purchase cases changed status or value**, including
case 2109 whose `pcs_price` went from **80,500 to 0** while its status stayed "Under Revision".

---

## 6. What this does and does not give you

| | Recoverable from snapshots |
|---|---|
| **What** changed — every field, old and new value | ✓ in full |
| A **deleted row's complete contents** | ✓ in full |
| Rows that were **added** | ✓ in full |
| **Schema** changes (columns added/dropped) | ✓ |
| **When** — to the nearest snapshot window (days or weeks) | ~ approximate only |
| **Who** made the change | ✗ **never recorded, cannot be recovered** |
| **Why** — the reason or authority | ✗ except where `aud.revs` has it |
| The **IP address** | ✗ never recorded anywhere before now |
| Changes that happened **and were undone** inside one window | ✗ invisible — the snapshot only shows net state |
| Anything before the **oldest snapshot** (May 2026) | ✗ except `aud.busdata` back to Jan 2021 |

So the honest summary: **"kya badla" ka record mil sakta hai, "kisne badla" ka nahi.** The actor was
never written down. That is precisely the gap the new `syslog` trail closes from the day it goes
live — and why the recovered rows are labelled "Imported History" rather than presented as evidence.

---

## 7. Recommended next steps

1. **Check `E:\databackup2.0`** for the full dated series. More snapshots means a tighter timeline.
2. **Stop overwriting backups.** Keep every dated dump from now on — each one is a point in the
   history. The existing `db_backup.py` already writes dated folders; make sure nothing prunes them.
3. **Run the comparison over the whole chain** and import it, so the System Logs screen carries what
   history does exist:
   ```bash
   php artisan audit:compare --from=liverdw      --to=rdw           --import
   php artisan audit:compare --from=rdw          --to=updatedrdwV1  --import
   php artisan audit:compare --from=updatedrdwV1 --to=rdw01         --import
   php artisan audit:compare --from=rdw01        --to=rdw02         --import
   php artisan audit:compare --from=rdw02        --to=rdw04         --import
   ```
   Do this on a staging database first (it writes thousands of `backfill` events), and only once —
   re-running creates duplicates. `syslog.snapshot_runs` records what was already done.
4. **Investigate the two deleted salary payments** (§5) and the two deleted commitments. They are
   the only findings here that may need an answer rather than just a record.
5. **Turn on `track_commit_timestamp`** in `postgresql.conf` (needs a restart). It costs very little
   and gives every row a real last-modified timestamp — a second, independent record alongside the
   trail.
6. Add `log_connections = on` and `log_statement = 'ddl'` while you are in that file (§8.9).
