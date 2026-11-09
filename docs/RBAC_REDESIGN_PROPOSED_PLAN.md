# RDWIS 2.0 — Proposed RBAC Redesign Plan

**Status:** proposal only. Nothing in this document has been executed.
**Date:** 9 November 2026
**Companion:** [RBAC_DISCOVERY_REPORT.md](RBAC_DISCOVERY_REPORT.md) — the evidence this plan is built on.

## What this plan commits to

Four goals, taken from the requirement as stated:

1. **Nothing existing is damaged.** No column is dropped, no column is renamed, no existing row is
   rewritten. The legacy Microsoft Access application keeps working unchanged.
2. **Roles become department-scoped with sub-roles.** Each department gets its own role family, for
   example `FIN` as the head seat with `FIN-1` through `FIN-4` beneath it, capped at 5 seats.
3. **A case goes to one person.** Whoever it is sent to is the only one who sees it in their inbox.
4. **You control the rules, not the code.** Roles, permissions, seats and scopes live in database
   rows that an admin screen edits. No deployment needed to change who can do what.

## The core design decision

Everything new lives in **one new schema called `rbac`**. Not one existing table is altered.

This single decision is what makes goal 1 achievable. The discovery report established that
`cen.accounts` and the AES password logic are shared with the legacy Access application
([report, Part 4](RBAC_DISCOVERY_REPORT.md)), so they cannot be modified. By putting the new model
in a separate schema that *references* `cen.accounts.acc_id` rather than changing it, the old
application never sees a difference, and a rollback is a single `DROP SCHEMA rbac CASCADE` plus
turning a flag off.

Consequence worth stating plainly: for a while you will have two sources of truth. The plan handles
that with shadow mode (Phase 2) rather than by pretending it is not a cost.

---

# Part 1 — Why a side table instead of new columns

The obvious way to make a case belong to a person is to add `pss_holder_acc_id` to
`pur.purcase_substatus` and `css_holder_acc_id` to `hr.ctrcase_substatus`. This plan deliberately
does **not** do that. Reasons:

- Those tables sit in the `pur` and `hr` schemas that the legacy application reads. A new nullable
  column is usually harmless, but Access recordsets and saved queries sometimes enumerate columns,
  and "usually harmless" is not the standard this plan is held to.
- Two columns on two tables solves two modules. The same problem exists for every future module.
- Rolling back means an `ALTER TABLE ... DROP COLUMN` on a production table that the other
  application is actively using.

Instead: **one assignment table that serves every module.**

```sql
-- PROPOSED. Not executed.
CREATE SCHEMA IF NOT EXISTS rbac;

CREATE TABLE rbac.case_assignments (
    ca_id          bigserial    PRIMARY KEY,
    ca_module      varchar(32)  NOT NULL,   -- 'purchase' | 'contract' | 'fundshift' | 'salordercase' | 'gencase'
    ca_case_id     integer      NOT NULL,   -- pcs_id / ctc_id / fsh_id / soc_id / gcs_id
    ca_stage       varchar(30)  NOT NULL,   -- mirrors pss_stage / css_stage, kept for fallback + audit
    ca_holder_acc_id integer    NOT NULL REFERENCES cen.accounts(acc_id) ON DELETE RESTRICT,
    ca_position_id integer          NULL REFERENCES rbac.positions(pos_id) ON DELETE SET NULL,
    ca_assigned_by integer      NOT NULL REFERENCES cen.accounts(acc_id) ON DELETE RESTRICT,
    ca_assigned_at timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ca_released_at timestamp        NULL,
    ca_is_current  boolean      NOT NULL DEFAULT true,
    ca_note        varchar(500)     NULL
);

-- At most one live holder per case, enforced by the database, not by application code.
CREATE UNIQUE INDEX uq_rbac_case_assignment_current
    ON rbac.case_assignments (ca_module, ca_case_id) WHERE ca_is_current;

-- The inbox query's access path.
CREATE INDEX idx_rbac_ca_holder_current
    ON rbac.case_assignments (ca_holder_acc_id, ca_module) WHERE ca_is_current;

CREATE INDEX idx_rbac_ca_case
    ON rbac.case_assignments (ca_module, ca_case_id);
```

The `ca_module` plus `ca_case_id` pair is a soft reference, so there is no foreign key to
`pur.purcases` or `hr.ctrcases`. That is intentional: it keeps `rbac` droppable without touching
those schemas. The cost is that orphan rows are possible if a case is hard-deleted; Part 7 covers
the reconciliation job that catches them.

## Why this already works with your existing UI

The discovery report found that the sender **already picks a specific person**. The destination list
is built at `app/Services/PurchaseApprovalService.php:307-318` with keys of the form `acc_<id>`,
carrying a real `acc_id` and the person's name. That code parses it back at `:417-418`. The chosen
account id is then written **only** into a notification row at `:471`, `:508`, `:520`, `:532`, and
`transitionSubstatus()` at `:786-790` persists nothing but `pss_stage`.

The request parameter is already plumbed through the controllers as `target_destination` /
`target_stage`, for example `app/Http/Controllers/DDG/ContractCaseController.php:109` and
`app/Http/Controllers/DG/ContractCaseController.php:106`. The Blade side already renders it from
`resources/views/approvals_new/_action_box.blade.php:9`.

So the person is chosen, transmitted, parsed, and then thrown away one line before it would have
been useful. **This plan's Phase 3 is essentially "stop discarding it".**

---

# Part 2 — The seat model

## 2.1 What goes wrong today

`cen.accounts` fuses three separate ideas into one row:

| Idea | Should answer | Today |
|---|---|---|
| Person | who is this human | `acc_name` free text only |
| Login | how do they sign in | `acc_username`, `acc_pass` |
| Seat | which chair do they occupy | `acc_unt_id`, `acc_desig`, `acc_level`, the four range columns |

Because the seat is baked into the login, one person who holds two chairs needs two logins. That is
already happening: seven people have two or three accounts, and **Aleem Mushtaq holds two
simultaneously active accounts**, `amushtaq` as C3 division director and `amushtaq1` as Ex Systems
director. Nothing in the database records that these are one human.

## 2.2 Three new tables

```sql
-- PROPOSED. Not executed.

-- A human being. One row per person, ever.
CREATE TABLE rbac.persons (
    per_id        serial       PRIMARY KEY,
    per_full_name varchar(255) NOT NULL,
    per_rank      varchar(100)     NULL,
    per_cnic      varchar(20)      NULL,
    -- hr.emps has a real primary key on emp_id (constraint emps_pk), so this can be a true
    -- foreign key. This single column is the user-to-employee link the system has never had.
    per_emp_id    varchar(50)      NULL REFERENCES hr.emps(emp_id) ON UPDATE CASCADE ON DELETE SET NULL,
    per_active    boolean      NOT NULL DEFAULT true,
    per_created_at timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX uq_rbac_persons_cnic   ON rbac.persons (per_cnic)   WHERE per_cnic   IS NOT NULL;
CREATE UNIQUE INDEX uq_rbac_persons_emp_id ON rbac.persons (per_emp_id) WHERE per_emp_id IS NOT NULL;

-- A chair in an organisational unit. This is the "role" you asked for.
CREATE TABLE rbac.positions (
    pos_id        serial       PRIMARY KEY,
    pos_code      varchar(32)  NOT NULL,   -- 'FIN', 'FIN-1', 'C3-2'
    pos_title     varchar(255) NOT NULL,   -- 'Finance Officer 1'
    pos_unt_id    integer      NOT NULL REFERENCES cen.units(unt_id),
    pos_parent_id integer          NULL REFERENCES rbac.positions(pos_id) ON DELETE RESTRICT,
    pos_seat_no   smallint     NOT NULL,   -- 0 = head, 1..4 = officers
    pos_is_head   boolean      NOT NULL DEFAULT false,
    pos_stage_key varchar(30)      NULL,   -- which workflow stage this seat answers for: 'DFinance', 'DProc', ...
    pos_active    boolean      NOT NULL DEFAULT true
);
CREATE UNIQUE INDEX uq_rbac_positions_code ON rbac.positions (pos_code);
CREATE UNIQUE INDEX uq_rbac_positions_seat ON rbac.positions (pos_unt_id, pos_seat_no);
-- Hard cap of 5 seats per unit, enforced by the database.
ALTER TABLE rbac.positions ADD CONSTRAINT ck_rbac_positions_seat_cap CHECK (pos_seat_no BETWEEN 0 AND 4);
-- Exactly one head per unit.
CREATE UNIQUE INDEX uq_rbac_positions_one_head ON rbac.positions (pos_unt_id) WHERE pos_is_head;

-- Which login currently sits in which chair. This is the join that replaces the fused account row.
CREATE TABLE rbac.position_assignments (
    pa_id        serial    PRIMARY KEY,
    pa_pos_id    integer   NOT NULL REFERENCES rbac.positions(pos_id) ON DELETE RESTRICT,
    pa_acc_id    integer   NOT NULL REFERENCES cen.accounts(acc_id) ON DELETE RESTRICT,
    pa_per_id    integer       NULL REFERENCES rbac.persons(per_id) ON DELETE SET NULL,
    pa_from      date      NOT NULL DEFAULT CURRENT_DATE,
    pa_to        date          NULL,
    pa_is_current boolean  NOT NULL DEFAULT true
);
-- One live occupant per chair.
CREATE UNIQUE INDEX uq_rbac_pa_pos_current ON rbac.position_assignments (pa_pos_id) WHERE pa_is_current;
-- One live chair per login, so an account is never ambiguous.
CREATE UNIQUE INDEX uq_rbac_pa_acc_current ON rbac.position_assignments (pa_acc_id) WHERE pa_is_current;
```

Three things worth noticing in that DDL.

- **The 5-seat cap is a database constraint**, `ck_rbac_positions_seat_cap`. It cannot be exceeded
  by a bug in application code.
- **`pos_parent_id` gives you the hierarchy** you asked for. `FIN-1` points at `FIN`. The discovery
  report found `cen.roles.rol_reportlevel` and `acc_reportlevel` already exist and are populated but
  are read by nothing except the admin screen that copies them, so the hierarchy idea is already in
  your data model and simply unused. This replaces it with something queryable.
- **`pa_acc_id` is unique among current rows**, so one login maps to exactly one seat at a time.
  A person with two chairs keeps two logins for now, both pointing at the same `per_id`. Phase 5
  is what lets them collapse to one login with a seat switcher. Deferring that keeps Phase 1 safe.

## 2.3 The concrete seat list

Derived from `cen.units` (17 rows) and the live account distribution. Head seat titles reuse the
designations already in `cen.roles` so the admin screen looks familiar.

### Central departments

| Unit | Dept | Seat 0 (head) | Seats 1 to 4 | Stage key | Accounts today |
|---|---|---|---|---|---|
| 800000 | Finance | `FIN` Director Finance | `FIN-1` … `FIN-4` | `DFinance` | 6 (2 active) |
| 810000 | Procurement | `PROC` Director Inventory & Procurement | `PROC-1` … `PROC-4` | `DProc` | 1 |
| 820000 | HR | `HR` Director HR | `HR-1` … `HR-4` | `HR` | 4 |
| 840000 | Admin | `ADM` Director Admin | `ADM-1` … `ADM-4` | `Admin` | 7 |
| 860000 | IT | `IT` Staff Officer IT&CYS | `IT-1` … `IT-4` | `IT` | 4 |
| 880000 | IS | `IS` Manager IS | `IS-1` … `IS-4` | `IS` | 1 |

### Project divisions

| Unit | Division | Seat 0 (head) | Seats 1 to 4 | Stage key | Accounts today |
|---|---|---|---|---|---|
| 200000 | C3 | `C3` Director C3 | `C3-1` … `C3-4` | `Division` | 1 |
| 250000 | AI & Immersive | `AIE` Director AI & Emers | `AIE-1` … `AIE-4` | `Division` | 1 |
| 300000 | Under Water Tech | `UWT` Director UWT | `UWT-1` … `UWT-4` | `Division` | 1 |
| 350000 | Sensors & UAV | `SUAV` Director S&UAV | `SUAV-1` … `SUAV-4` | `Division` | 4 |
| 400000 | Radar & EW | `REW` Director R&EW | `REW-1` … `REW-4` | `Division` | 3 |
| 450000 | Ex Systems | `EXS` Director Ex Systems | `EXS-1` … `EXS-4` | `Division` | 3 |
| 180000 | Projects / SORD | `SORD` Staff Officer R&D | `SORD-1` … `SORD-4` | `Division` | 2 |

### Command and external

| Unit | Office | Seat 0 (head) | Extra seats | Stage key | Accounts today |
|---|---|---|---|---|---|
| 160000 | RDW | `MD` Managing Director RDW | `MD-1`, `MD-2` | `MD` | 9 |
| 100000 | NRDI | `DG` Director General NRDI | `DG-1` | `DG` | 7 |
| 10000 | HQs NRD | `DDG` Deputy Director General | `DDG-1` | `DDG` | 1 at 100000 |
| 100000 | Logistics | `DLOG` Director Logistics | `DLOG-1` | `DLOG` | 1 |
| 990000 | MTSS | `MTSS` MTSS Representative | `MTSS-1` | `MTSS` | 1 |

Two notes on the awkward cases. `DDG` and `DLOG` accounts currently sit on unit 100000 even though
`cen.units` has a separate 10000 row for HQs, and their `acc_untarea` values (`hqs`, `log`) already
contradict the unit's `unt_area` of `nrdi` — one of the six live drifts in the discovery report.
Seeding uses `acc_untarea` as the authority, matching what every area middleware already does, and
the drift gets recorded in the seed notes rather than silently resolved.

RDW has 9 accounts against 3 seats and Admin has 7 against 5. Most are `Closed`. Part 6 covers what
happens to accounts that do not fit.

---

# Part 3 — Permissions you control from the database

## 3.1 Use the table that already exists

`cen.role_permissions` was created by
`database/migrations/2026_09_10_100000_create_cen_role_permissions_table.php` with exactly the right
shape, `role_slug` plus `permission`, and a unique constraint on the pair. The discovery report
found it holds **zero rows**, and the loader `RolePermissionMap::loadDbOverrides()` at
`app/Services/Auth/RolePermissionMap.php:347` already reads it. The wiring is built and unused.

This plan fills it rather than replacing it, which means Phase 2 needs no new read path.

## 3.2 Per-seat overrides

Department defaults go in `cen.role_permissions`. Individual seats sometimes need to differ, for
example only `FIN-1` may settle commitments. That goes in a small override table:

```sql
-- PROPOSED. Not executed.
CREATE TABLE rbac.position_permissions (
    pp_id      serial       PRIMARY KEY,
    pp_pos_id  integer      NOT NULL REFERENCES rbac.positions(pos_id) ON DELETE CASCADE,
    pp_permission varchar(100) NOT NULL,   -- must match PermissionRegistry
    pp_mode    varchar(5)   NOT NULL,      -- 'grant' | 'deny'
    pp_note    varchar(255)     NULL
);
CREATE UNIQUE INDEX uq_rbac_pp ON rbac.position_permissions (pp_pos_id, pp_permission);
ALTER TABLE rbac.position_permissions ADD CONSTRAINT ck_rbac_pp_mode CHECK (pp_mode IN ('grant','deny'));
```

Resolution order, most specific last:

```
1. cen.role_permissions  for the seat's role slug      (department default)
2. rbac.position_permissions  pp_mode = 'grant'        (add for this seat)
3. rbac.position_permissions  pp_mode = 'deny'         (remove for this seat)
4. viewer strip, if the account is a viewer            (see fix below)
```

`deny` is evaluated last so a deny always wins. That makes "give FIN-2 everything except cancel" a
one-row change.

## 3.3 Two existing bugs this must fix on the way

Both are confirmed in the discovery report and both will silently corrupt the new model if carried
forward.

**The viewer strip misses approval actions.** `RolePermissionMap.php:394-417` filters by suffix and
the action permissions end in `_action`, not `.action`. Live result: `mimran`, whose row says
`acc_auth = 'viewer'`, holds `purchase.md_action` and `contract.md_action`. The new resolver must
filter against an explicit list of mutating permission names, not a suffix pattern.

**Command officers bypass viewer entirely.** `UserAccessContext.php:263` returns `false` from
`isViewer()` for any command user, so the strip never runs for MD, DG, DDG or DLOG. Under the new
model `acc_auth` must be honoured for everyone, with any genuine exception expressed as an explicit
`grant` row that an admin can see.

---

# Part 4 — Per-seat data scope

Today scope is a numeric unit window shared by the whole department: every Finance account carries
`800000..809999`. Two active Finance accounts have four identical range columns. There is no
mechanism to give `FIN-1` and `FIN-2` different data, which is the root of the hardcoding.

```sql
-- PROPOSED. Not executed.
CREATE TABLE rbac.position_scopes (
    ps_id       serial      PRIMARY KEY,
    ps_pos_id   integer     NOT NULL REFERENCES rbac.positions(pos_id) ON DELETE CASCADE,
    ps_kind     varchar(16) NOT NULL,   -- 'unit' | 'unit_range' | 'project' | 'head' | 'all'
    ps_ref_id   integer         NULL,   -- unt_id, prj_id or hed_id depending on kind
    ps_range_lo integer         NULL,   -- for kind='unit_range'
    ps_range_hi integer         NULL,
    ps_mode     varchar(7)  NOT NULL DEFAULT 'include'  -- 'include' | 'exclude'
);
ALTER TABLE rbac.position_scopes ADD CONSTRAINT ck_rbac_ps_kind
    CHECK (ps_kind IN ('unit','unit_range','project','head','all'));
ALTER TABLE rbac.position_scopes ADD CONSTRAINT ck_rbac_ps_range
    CHECK (ps_kind <> 'unit_range' OR (ps_range_lo IS NOT NULL AND ps_range_hi IS NOT NULL AND ps_range_hi >= ps_range_lo));
```

That last constraint is deliberate. `cen.units` row 180000 currently holds
`unt_lowers = 180000, unt_uppers = 179999`, an inverted range that matches nothing, and the only
live SORD account inherits it. The new table refuses to store that shape.

Seeding reproduces today's behaviour exactly: every seat in a unit gets one `unit_range` row equal
to that unit's current `acc_lowers..acc_uppers`. **Day one behaviour is therefore identical.**
Narrowing `FIN-2` to three projects afterwards is adding rows, not changing code.

Scope is attached to the **seat**, not the login, so moving a person moves their access with the
chair.

---

# Part 5 — The inbox, which is what you actually asked for

## 5.1 The rule

```
A case appears in my inbox if:
    there is a current assignment and I am the holder
  OR
    there is no current assignment at all, and the case's stage maps to my seat
```

The second clause is the whole safety story. Every case that exists today has no assignment row, so
it keeps behaving exactly as it does now. Only cases forwarded after the flag is turned on become
person-specific. **No existing case changes behaviour, and no existing case is hidden from anyone.**

## 5.2 What the query becomes

Today, `app/Services/SidebarBadgeService.php:74-77`:

```php
} elseif ($area === 'fin') {
    $purCount = Purchase::atStage('DFinance')
        ->whereNotIn('pcs_status', [...])->count();
}
```

No user predicate at all. Proposed replacement, as a new scope beside the existing
`scopeAtStage()` at `app/Models/Purchase.php:469` rather than a modification of it:

```php
// PROPOSED addition. scopeAtStage() is left exactly as it is.
public function scopeInboxOf($query, CenAccount $user, string $stage)
{
    if (! config('rbac.assignment_enabled', false)) {
        return $query->atStage($stage);          // unchanged legacy behaviour
    }

    return $query->where(function ($q) use ($user, $stage) {
        $q->whereExists(function ($sub) use ($user) {
            $sub->selectRaw('1')->from('rbac.case_assignments')
                ->whereColumn('ca_case_id', 'pur.purcases.pcs_id')
                ->where('ca_module', 'purchase')
                ->where('ca_is_current', true)
                ->where('ca_holder_acc_id', $user->acc_id);
        })
        ->orWhere(function ($q2) use ($stage) {
            $q2->whereNotExists(function ($sub) {
                    $sub->selectRaw('1')->from('rbac.case_assignments')
                        ->whereColumn('ca_case_id', 'pur.purcases.pcs_id')
                        ->where('ca_module', 'purchase')
                        ->where('ca_is_current', true);
                })
                ->atStage($stage);
        });
    });
}
```

The head seat needs to see the whole department, otherwise a director cannot supervise. That is one
extra clause driven by data, not a special case in code:

```
OR (I occupy a head seat for this stage AND the holder is any account in my unit)
```

## 5.3 Where the assignment gets written

One call added to the forward path. `transitionSubstatus()` at
`app/Services/PurchaseApprovalService.php:778` keeps writing `pss_stage` exactly as it does, and a
sibling call records the holder:

```php
// PROPOSED, inside forward() where $targetAcc is already resolved at :417-418
if (config('rbac.assignment_enabled', false) && $targetAcc) {
    app(\App\Services\Rbac\CaseAssignmentService::class)
        ->assign('purchase', $case->pcs_id, $newStage, $targetAcc->acc_id, $user->acc_id);
}
```

`assign()` closes the prior current row and opens a new one in a transaction, which the partial
unique index `uq_rbac_case_assignment_current` guarantees cannot produce two live holders.

This is the dual-write pattern your codebase already uses: `ContractCaseApprovalService.php:44-71`
writes the new substatus row *and* the legacy `ctc_status`, so the old application keeps working.
The same discipline applies here, with `rbac.case_assignments` as the new side and `pss_stage` as
the legacy side.

## 5.4 When there is no specific person

Three cases, all of which must be handled or cases will vanish:

- **Sender picks a department, not a person.** The destination builder already emits role-level
  entries with `acc_id => null` at `PurchaseApprovalService.php:332-343` for DLOG and DDG. For
  those, write no assignment row. The case falls to the stage fallback and the whole department
  sees it, which is today's behaviour and is correct here.
- **The holder's account is closed.** 37 of 58 accounts are `Closed`, and
  `CenAccountUserProvider.php:18` blocks them from logging in. A case assigned to a closed account
  would be invisible to everyone. The reconciliation job in Part 7 releases those assignments back
  to stage level.
- **The holder goes on leave.** `rbac.position_assignments` already carries `pa_from` and `pa_to`,
  so a temporary occupant inherits the chair and therefore the inbox, with no case-level edits.

---

# Part 6 — Phased rollout

Each phase is independently reversible. The flags live in a new `config/rbac.php` modelled on
`config/hrforms.php:13`, which already proves this pattern in your codebase.

```php
// PROPOSED config/rbac.php
return [
    'enabled'            => env('RBAC_ENABLED', false),             // master switch
    'shadow_mode'        => env('RBAC_SHADOW_MODE', true),          // compute new, obey old, log differences
    'permissions_enabled'=> env('RBAC_PERMISSIONS_ENABLED', false), // Phase 2
    'assignment_enabled' => env('RBAC_ASSIGNMENT_ENABLED', false),  // Phase 3
    'assignment_modules' => explode(',', env('RBAC_ASSIGNMENT_MODULES', '')), // 'purchase', then 'contract'
    'scope_enabled'      => env('RBAC_SCOPE_ENABLED', false),       // Phase 4
];
```

## Phase 0 — Schema only, zero behaviour change

Create the `rbac` schema and its five tables. Nothing reads them. All flags off.

Proposed migration, following your existing naming convention:

```
database/migrations/2026_11_10_000001_create_rbac_schema_tables.php
```

Exit criteria: full test suite behaves exactly as before, and
`SELECT count(*) FROM rbac.positions` returns 0.

Rollback: `DROP SCHEMA rbac CASCADE`.

## Phase 1 — Seed seats and map existing accounts

Insert the 76 seats from Part 2.3 (30 central, 35 project including SORD, 9 command, 2 MTSS), then map each of the 58 accounts to a seat, then create
`rbac.position_scopes` rows that copy each account's current range verbatim.

Still no reads. The deliverable is a **comparison report**, not a behaviour change: for every
active account, the seat-derived permission set beside the current code-derived set, with
differences listed. The discovery report already produced the current-state baseline for all 21
active accounts, so this is a direct diff.

Expect these differences, because they are existing bugs the new model refuses to reproduce:

| Account | Today | Under seats | Why |
|---|---|---|---|
| `sajidali` | `DIV_DIRECTOR`, 26 perms | `ADM` head, Admin perms | Admin dept was falling into the Projects branch at `UserAccessContext.php:380` |
| `arehman` | `DIV_DIRECTOR`, 26 perms | `REW-1` officer | "Deputy Director" matched `str_contains($desig,'DIRECTOR')` at `:381` |
| `mimran` | 13 perms incl. `purchase.md_action` | viewer, read-only | `acc_auth='viewer'` honoured; see Part 3.3 |
| `Mmazhar` | `approver` | needs a decision | account says `approver`, `cen.roles` says `viewer` |

**Each row above is a policy question, not a technical one.** They should be signed off before
Phase 2, because Phase 2 is when they start taking effect.

Rollback: `TRUNCATE` the `rbac` tables.

## Phase 2 — Permissions, in shadow mode first

Fill `cen.role_permissions` from the signed-off matrix. Add the new resolver, but run it with
`shadow_mode = true`: it computes the new answer, **obeys the old answer**, and logs every
disagreement with the account, the permission and the route.

Run shadow mode through at least one full monthly cycle so salary generation, attendance locking and
the 26-to-25 payroll window are all exercised. When the log is quiet, set `shadow_mode = false` and
`permissions_enabled = true`.

This phase is the direct answer to "it should not break everything". You see every difference before
any of them bites.

Rollback: `permissions_enabled = false`.

## Phase 3 — Case assignment, one module at a time

This is the phase that delivers the requirement. Purchase first, because it has the most traffic and
the destination picker is already the most complete.

Volume to migrate, measured today:

| Module | Total cases | Open cases needing nothing |
|---|---|---|
| Purchase | 2,048 | 50 |
| Contract | 284 | 18 |

**No backfill is required.** Open cases are left with no assignment row and keep using the stage
fallback from Part 5.1. Only newly forwarded cases become person-specific. 50 and 18 are small
enough that if you later *want* to assign them explicitly, an admin can do it by hand through the
Part 8 screen.

Current open-case distribution, for reference when verifying:

```
 purchase: Division 26 | DProc 9 | DFinance 7 | MD 7 | DLOG 1
 contract: Division 11 | HR 4 | DG 3
```

Set `assignment_modules = purchase`, confirm for a week, then add `contract`.

Rollback: remove the module from `assignment_modules`. Assignment rows stay in place, harmless,
and the stage fallback resumes for everyone.

## Phase 4 — Narrow the scopes

Only now start editing `rbac.position_scopes` to give `FIN-1` and `FIN-2` genuinely different data.
Until this phase every seat still carries its department's original range, so nothing has narrowed
and nobody has lost access.

Do one department at a time and keep the head seat at department-wide scope.

Rollback: `scope_enabled = false`, or delete the narrowing rows.

## Phase 5 — One login per person

The last and largest phase, deliberately deferred because nothing above depends on it. Populate
`rbac.persons`, link the 7 people who currently hold 2 or 3 accounts, and add a seat switcher so
Aleem Mushtaq signs in once and chooses between the C3 chair and the Ex Systems chair.

The old usernames keep working throughout, because `rbac.position_assignments` maps every one of
them to a seat. This phase adds a better path; it does not close the old one.

---

# Part 7 — Safety mechanisms

These are the things that stop the failure modes rather than hoping they do not happen.

**Database-enforced invariants.** Four of them, listed with the problem each prevents:

| Constraint | Prevents |
|---|---|
| `uq_rbac_case_assignment_current` | two people holding one case |
| `uq_rbac_pa_pos_current` | two logins in one chair |
| `uq_rbac_pa_acc_current` | one login in two chairs, which would make the inbox ambiguous |
| `ck_rbac_positions_seat_cap` | a sixth seat in a department |
| `uq_rbac_positions_one_head` | two heads, so supervision is never ambiguous |

**A nightly reconciliation command.** Proposed as
`app/Console/Commands/RbacReconcileCommand.php`, scheduled in `routes/console.php` beside the
existing `rdwis:lock-daily-attendance` entry at `:41`. It reports and optionally repairs:

- assignments whose holder account is now `Closed`, released back to stage level
- assignments whose case no longer exists, the orphan risk from the soft reference in Part 1
- cases whose `ca_stage` disagrees with the live `pss_stage` or `css_stage`
- seats with no occupant, and accounts with no seat
- `ca_stage` values not present in any `pos_stage_key`

**A visible holder on every case screen.** If a case shows "currently with: Tanveer Ahmed
(FIN-1)", a wrong assignment is caught by the user in minutes instead of being discovered when
something is overdue. This matters more than any automated check.

**Escape hatch.** A permission such as `rbac.assignment.override` lets the department head reassign
any case in their unit without a developer. Without this, every mis-send becomes a support ticket.

## The risk register

| Risk | Likelihood | Mitigation |
|---|---|---|
| Case assigned to a closed account becomes invisible | Medium | nightly reconciliation; validate target is `Active` at assign time |
| Phase 2 removes a permission someone needed | Medium | shadow mode for a full month before enforcing |
| Legacy Access app disturbed | Low | nothing it reads is altered; `rbac` is a separate schema |
| Two sources of truth diverge during Phases 2 to 4 | High by design | shadow mode plus reconciliation; each phase is short |
| Head seat cannot supervise | Medium | head-seat clause in Part 5.2 from day one |
| 5-seat cap too small for Admin or RDW | Medium | see the note below |

On that last row: RDW has 9 accounts and Admin has 7, against 3 and 5 seats. Most are `Closed`, so
the live fit is tighter than it looks, but **this needs your decision before Phase 1**. Three
options: close the stale accounts, raise the cap for those two units, or let several accounts share
one seat by relaxing `uq_rbac_pa_pos_current`. The third option is the one this plan recommends
against, because it reintroduces exactly the ambiguity you are trying to remove.

---

# Part 8 — The admin screens you need

Without these the model is still code-driven and the fourth goal is unmet. All are new files; no
existing screen is modified.

| Screen | Purpose | Proposed route |
|---|---|---|
| Seat manager | create and retire seats, set parent, cap enforced | `/admin/rbac/positions` |
| Seat assignment | put a login in a chair, with dates for leave cover | `/admin/rbac/assignments` |
| Permission matrix | tick permissions per role slug and per seat | `/admin/rbac/permissions` |
| Scope editor | add or remove units, projects, heads per seat | `/admin/rbac/scopes` |
| Case reassignment | move a stuck case to another holder | `/admin/rbac/case-assignments` |
| Shadow diff viewer | what Phase 2 would change, before it changes | `/admin/rbac/shadow-report` |

Guard them with a real permission, not the hardcoded username. `AdminSettingsController.php:25`
currently compares against the literal `'superadminrdw'` and its area list at `:27` includes
`'admin'` while the live value is `adm`, so Admin users are not actually admitted today.

`PermissionRegistry` today has only `admin.settings.manage` at `:63`, `admin.users.view` at `:64`
and `admin.users.manage` at `:65`. These screens need **two new constants added** to it:

```php
// PROPOSED additions to app/Services/Auth/PermissionRegistry.php
public const ADMIN_RBAC_VIEW   = 'admin.rbac.view';    // read the seat and permission model
public const ADMIN_RBAC_MANAGE = 'admin.rbac.manage';  // edit seats, permissions, scopes
```

Adding a constant is additive and safe: `AppServiceProvider.php:62-69` loops over
`PermissionRegistry::all()` and defines a Gate for each, so a new constant becomes a working ability
with no other change. Grant `admin.rbac.manage` to `SUPERADMIN` and `IT_ADMIN` only at first.

---

# Part 9 — Files touched, and what is left alone

## New files only

```
database/migrations/2026_11_10_000001_create_rbac_schema_tables.php
database/migrations/2026_11_10_000002_seed_rbac_positions.php
database/migrations/2026_11_10_000003_seed_rbac_position_scopes_from_current.php
config/rbac.php
app/Models/Rbac/Position.php
app/Models/Rbac/PositionAssignment.php
app/Models/Rbac/CaseAssignment.php
app/Models/Rbac/Person.php
app/Services/Rbac/CaseAssignmentService.php
app/Services/Rbac/SeatResolver.php          # account -> seat -> permissions -> scope
app/Services/Rbac/ShadowComparisonService.php
app/Http/Controllers/Rbac/*Controller.php   # the six screens
app/Console/Commands/RbacReconcileCommand.php
resources/views/admin/rbac/*.blade.php
tests/Feature/Rbac/*Test.php
```

## Existing files with additive changes only

| File | Change | Guarded by |
|---|---|---|
| `app/Models/Purchase.php` | add `scopeInboxOf()`; leave `scopeAtStage():469` untouched | `assignment_enabled` |
| `app/Models/HrCtrCase.php` | same | `assignment_enabled` |
| `app/Services/PurchaseApprovalService.php` | one `assign()` call in `forward()`; `transitionSubstatus():778` unchanged | `assignment_enabled` |
| `app/Services/ContractCaseApprovalService.php` | same | `assignment_enabled` |
| `app/Services/SidebarBadgeService.php` | `atStage()` becomes `inboxOf()` at the five stage branches | `assignment_enabled` |
| `app/Services/Auth/RolePermissionMap.php` | new resolver beside `defaultMatrix():20`, which stays as the fallback | `permissions_enabled` |
| `app/Services/Auth/DataScopeService.php` | consult `rbac.position_scopes` first, fall through to ranges | `scope_enabled` |
| `routes/web.php` | new `/admin/rbac` group | always safe, new routes |
| `routes/console.php` | schedule the reconcile command | always safe |

Every one of these returns the current code path when its flag is off. That is the property that
makes the rollout reversible at any point.

## Explicitly not touched

- `cen.accounts`, including every `acc_*` column and the AES logic in `CenAccount.php:122-456`
- `cen.roles`, `cen.units`, `cen.levels`, `cen.globalvars`
- `pur.purcase_substatus`, `hr.ctrcase_substatus` and every other existing table
- `config/auth.php` and `app/Auth/CenAccountUserProvider.php`
- Any existing row in any existing table

---

# Part 10 — How to verify each phase

| Phase | Check | Pass condition |
|---|---|---|
| 0 | run the suite, then `SELECT count(*) FROM rbac.positions` | suite unchanged, count 0 |
| 1 | seat-derived vs code-derived permissions for all 21 active accounts | every difference explained and signed off |
| 2 | shadow log over one full month | no unexplained denials |
| 3 | forward a purchase case to `FIN-1`; check both inboxes | `FIN-1` sees it, `FIN-2` does not, `FIN` head does |
| 3 | open the 50 pre-existing open purchase cases | all still visible to the right department |
| 4 | narrow `FIN-2` to one project, re-check | `FIN-2` sees only that project, others unaffected |
| 5 | one login, two chairs | switching changes inbox and scope, no new username |

The Phase 3 row is the acceptance test for the original requirement. Note it has **two** parts: the
new case must be person-specific *and* the 50 old cases must be unchanged.

One existing test needs attention first.
`tests/Feature/AdminReversalsControllerTest.php:87` expects 403 while
`AdminReversalController.php:62-64` deliberately redirects, a pre-existing failure from commit
`8f850c9d`. Fix or retire it before Phase 2 so the suite is a trustworthy signal. Also note
`tests/` is in `.gitignore:34`, so the suite is not version-controlled; that should be reconsidered
before a change of this size.

---

# Part 11 — Decisions needed from you

Nothing can start until these five are settled. Each one changes the shape of Phase 1.

1. **Is 5 seats enough for RDW and Admin?** They hold 9 and 7 accounts today. Close the stale ones,
   raise the cap for those units, or allow seat sharing. The plan recommends the first.
2. **Is `Mmazhar` (DG NRDI) an approver or a viewer?** The account says `approver`, `cen.roles` says
   `viewer`, and the account currently wins because nothing reads `cen.roles`.
3. **Should MD, DG, DDG and DLOG lose their viewer bypass?** Honouring `acc_auth` for everyone is
   more correct, but it will remove approval actions from any command account currently marked
   `viewer`, which today includes `mimran`.
4. **Should Deputy Directors be officers or directors?** `cen.roles` records all five as `staff`;
   the code currently promotes them to `DIV_DIRECTOR` by substring match. The seat model follows
   `cen.roles` unless you say otherwise.
5. **Should the Admin department keep division-level powers?** `sajidali` can currently create and
   submit purchase cases, create contract cases and generate salary, as a side effect of the branch
   at `UserAccessContext.php:380`. The seat model gives Admin its own smaller permission set.

Two smaller ones that can wait until Phase 3 and Phase 4 respectively: whether the 50 open purchase
cases should be explicitly assigned or left on stage fallback, and whether `cen.units` row 180000's
inverted range should be corrected at source or only in the new scope table.

---

*Proposal only. No file in this repository was modified to produce it, and no database write was
performed. The `rbac` schema described here does not exist yet.*
