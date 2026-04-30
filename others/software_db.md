# 📊 RDWIS — Master Database & Architecture Ledger

> **Last Updated:** 2026-03-25 | **Maintained By:** Senior Lead Developer & DB Architect

---

## 1. Database Engine

| Property          | Value                          |
|-------------------|--------------------------------|
| **DBMS**          | PostgreSQL 18.1                |
| **Database Name** | `newdev`                       |
| **Host**          | `127.0.0.1`                    |
| **Port**          | `5432`                         |
| **Schemas**       | `cen`, `prj`, `doc`, `pur`, `hr`, `frm`, `puritems`, `purnew` |

---

## 2. Tech Stack

| Component             | Detail                                             |
|-----------------------|----------------------------------------------------|
| **Framework**         | Laravel 12 (`laravel/framework: ^12.0`)            |
| **PHP Version**       | `>= 8.2`                                           |
| **ORM**               | Eloquent (27 Models)                               |
| **Session Driver**    | `database`                                         |
| **Cache Store**       | `database`                                         |
| **Queue Connection**  | `database`                                         |
| **Key Packages**      | `phpoffice/phpword ^1.1`, `laravel/tinker ^2.10.1` |
| **Frontend Build**    | Vite (`vite.config.js`)                            |
| **Auth Model**        | `App\Models\User` → `cen.accounts`                 |

---

## 3. Environment Config (Non-Sensitive)

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
APP_LOCALE=en

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=newdev

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log
MAIL_MAILER=log
BCRYPT_ROUNDS=12
VITE_APP_NAME="${APP_NAME}"
```

---

## 4. Schema Overview (acessdev.sql)

### 4.1 Schema: `cen` (Central/Core)
- `cen.accounts` — User login accounts with designation, unit, levels, access mask, area and status.
- `cen.units` — Organizational units with type, short name, hierarchical lower/upper numeric bounds, and area.
- `cen.roles` — Designation definitions per unit and level, with short name, type, access mask and authorization flags.
- `cen.levels` — Master list of hierarchical levels/categories.
- `cen.heads` — Financial/organizational heads/cost centers; optionally linked to projects.
- `cen.routes` — Workflow routing steps by document type.
- `cen.globalvars` — System-wide configuration key/value pairs and metadata.
- `cen.version` — Database version and compatibility.

### 4.2 Schema: `prj` (Projects)
- `prj.projects` — Project master records with codes, dates, unit, status and narrative fields.
- `prj.milestones` — Project milestones with types, costs, target/achieved dates and completion status.
- `prj.prghistory` — Project progress log entries, levels and edit trail flags.
- `prj.comments` — Comments attached to progress history entries.
- `prj.events` — Timeline/events tied to projects and their histories.
- `prj.prjattachments` — File attachments linked to project objects.
- `prj.mprgroup` — Groups for compiling Monthly Progress Reports (MPR).

### 4.3 Schema: `pur` (Procurement/Purchase)
- `pur.purcases` — Procurement cases with unit/head linkage, financial totals, types, routing flags and lifecycle timestamps.
- `pur.purcaseitems` — Line items under a purchase case with quantity, unit, estimated and final prices, categories and subheads.
- `pur.quotes` — Vendor quotations for a case with quote number, prices, acceptance and recommendation flags.
- `pur.quoteitems` — Quoted line-items mapped to case items/descriptions.
- `pur.purreqs` — Internal purchase requisitions with unit/head linkage and fulfillment state.
- `pur.purreqitems` — Requisition line items with quantities, prices and categories.
- `pur.purreceipts` — Goods receipt notes for delivered items against cases.
- `pur.purreceiptitems` — Items received per receipt and their link to case items.
- `pur.purattachments` — Attachments related to purchase cases and sub-objects.
- `pur.purcaseminutes` — Committee minutes text and financial dues for cases.
- `pur.purcaseminuterefs` — Reference letters/metadata attached to minutes.
- `pur.purcases_shd` — Subhead distribution ratios for a purchase case.
- `pur.noquotes` — Registry of firms that did not quote for a case.

### 4.4 Schema: `frm` (Firms/Vendors)
- `frm.firmz` — Vendors/firm registry with entity type, group, flags and identifiers (NTN/GST).
- `frm.offices` — Firm offices/branches, locations and types.
- `frm.persons` — Contact persons for firms with roles/departments.
- `frm.facils` — Facilities/capabilities offered by a firm.
- `frm.projects` — Firm project portfolio and awards, status and cost.
- `frm.info` — Generic key/value information records for firm-linked entities.
- `frm.specs` — Technical capability/specification entries for firms.

### 4.5 Schema: `hr` (Human Resources)
- `hr.emps` — Employee master records with unit, status and identity details.
- `hr.empsexta` — Extended personal profile (discipline, qualification, addresses).
- `hr.empsextb` — Next-of-kin and emergency contact details.
- `hr.empsextc` — Security card and clearance details.
- `hr.attendance` — Monthly attendance sheets with per-day markers and lock flags.
- `hr.attendanceremarks` — Remarks tied to specific attendance days.
- `hr.jobs` — Prior employment history for employees.
- `hr.applicants` — Job applicants’ master data and application lifecycle.
- `hr.applicjobs` — Applicants’ previous job records.
- `hr.applicqualifs` — Applicants’ qualifications and certifications.
- `hr.qualifs` — Employees’ qualifications and certifications.
- `hr.hirings` — Hiring requests/vacancies with grades and salary bands.
- `hr.hrheads` — HR departments/heads master.
- `hr.contracts` — Employment contracts, salary, grade, term and file paths.
- `hr.contractplans` — Planned periods and heads for contracts.
- `hr.ctrcases` — Contract cases (renewal/transfer/termination) with approvals.
- `hr.ctrcaseplans` — Plans attached to contract cases.
- `hr.ctrcaseminutes` — Minutes text and financial dues for contract cases.
- `hr.ctrcaseminuterefs` — Reference letters/flags for case minutes.
- `hr.empattachments` — Attachments linked to employee records.
- `hr.bnkaccounts` — Employee bank account details for payroll.
- `hr.sims` — SIM cards issued to employees.
- `hr.devices` — Devices issued to employees.
- `hr.vehicles` — Vehicles registered to or used by employees.
- `hr.salreqs` — Salary requests per employee per month with computed components.
- `hr.bdmems`, `hr.boards` — Board composition and sessions for hiring/HR decisions.
- `hr.bdapps` — Board-to-applicant mapping.

### 4.6 Schema: `puritems` (Item Registry — Base Tables)
- `puritems.categories` — Item categories.
- `puritems.subcategories` — Item subcategories under a category.
- `puritems.items` — Item catalog with optional unit/head/account scoping.
- `puritems.prices` — Item price history with base/tax/gross breakdowns and effectivity date.
- `puritems.rfqs` — Request-for-Quotation headers created by units/users.
- `puritems.rfq_items` — Items included in an RFQ with quantity and price snapshot.

### 4.7 Schema: `purnew` (Views over `puritems`)
- `purnew.items`, `purnew.prices`, `purnew.rfqs`, `purnew.rfq_items` — Read-friendly views mapped onto corresponding `puritems` tables.

### 4.8 Schema: `aud` (Audit & Data Reversal)
- `aud.revs` — Data reversal/change case headers with reasons, status and effective units/dates.
- `aud.revdata` — Attribute-level before/after snapshots for reversal items.
- `aud.revcomps` — Row-level actions and composite change descriptors.
- `aud.audattachments` — Attachments for audit/reversal cases.
- `aud.busdata` — UI/business change logs at field level with user and form context.

### 4.9 Schema: `ina` (Inventory/Assets)
- `ina.inventory` — Inventory items charged to units/projects with specs and values.
- `ina.invats` — Inventory-attribution records with quantities and units.
- `ina.invatcomps` — Components/assemblies, locations and custody with status and remarks.
- `ina.invatlocs` — Location/custody movements for inventory-attribution.
- `ina.invatspecs` — Name–value specification pairs for inventory-attribution.
- `ina.invenitems` — Inventory issuance/allocation items with lifecycle state.
- `ina.inaattachments` — Attachments for inventory objects.

### 4.10 Schema: `fin` (Finance)
- `fin.commitments` — Financial commitments against heads/units with amounts and status.
- `fin.transactions` — Transactions posted against commitments with balances and taxes.
- `fin.transfers` — Budget transfers between heads/units with status.
- `fin.sharesalloc` — Allocation of shares across heads/commitments.
- `fin.sharesinstall` — Installments applied to share allocations.
- `fin.subheads`, `fin.subheads_zzz` — Subhead allocations and legacy/staging subheads.
- `fin.extcompenses` — External compensations paid with dates and amounts.
- `fin.loanadjustments`, `fin.loanremarks` — Loan adjustments and associated remarks.
- `fin.contractsverif` — Finance department’s verification flags/timestamps for contracts.

---

## 5. Relationship Map (ERD Summary)

```
cen.units ─────┬──── 1:N ──── cen.accounts
               ├──── 1:N ──── cen.roles
               └──── 1:N ──── prj.projects

cen.accounts ──┬──── FK ───── cen.roles (acc_desig → rol_desig)
               ├──── FK ───── cen.units (acc_unt_id → unt_id)
               └──── FK ───── cen.levels (acc_level → lvl_id)

prj.projects ──┬──── 1:N ──── prj.milestones
               ├──── 1:N ──── prj.prghistory
               ├──── 1:N ──── prj.prjattachments
               ├──── 1:N ──── prj.events
               └──── 1:1 ──── doc.documents

prj.prghistory ──── 1:N ──── prj.comments

doc.documents ─┬──── 1:N ──── doc.document_versions
               ├──── 1:N ──── doc.document_history
               ├──── FK ───── cen.accounts (current_owner_id)
               └──── FK ───── cen.accounts (creator_id)

pur.purcases ──┬──── 1:N ──── pur.purcaseitems
               ├──── 1:N ──── pur.quotes
               ├──── 1:N ──── pur.noquotes
               └──── 1:N ──── pur.purattachments

pur.quotes ────────── FK ───── frm.firmz (qte_frm_id → frm_id)

puritems.categories ── 1:N ── puritems.subcategories
puritems.items ────── 1:N ── puritems.prices
puritems.rfqs ─────── 1:N ── puritems.rfq_items
```

---

## 6. Controllers & Route Architecture

### Controllers

| Controller            | File                          | Purpose                                            |
|-----------------------|-------------------------------|-----------------------------------------------------|
| `AuthController`      | `AuthController.php`          | Login/Logout authentication                         |
| `ProjectController`   | `ProjectController.php` (20KB)| CRUD projects, milestones, attachments, spendings   |
| `DocMprController`    | `DocMprController.php` (7KB)  | MPR document view, store, report generation         |
| `MprController`       | `MprController.php` (13KB)    | SORD MPR inbox, review, actions, compiled report    |
| `PurchaseController`  | `PurchaseController.php` (5KB)| Purchase cases, quotes, attachments                 |
| `PurItemsController`  | `PurItemsController.php`      | New item registry (purnew) CRUD, RFQ management, Quotation System UI (Add Quotes backend APIs, delete RFQ, JSON details, etc.) |
| `DivHrController`     | `DivHrController.php` (13KB)  | Employee list, details, attendance management       |
| `ReportsController`   | `ReportsController.php`       | Comparative/IT letter report generation             |

### Route Groups

| Group      | Prefix  | Access Control      | Key Features                           |
|------------|---------|----------------------|----------------------------------------|
| **Guest**  | `/`     | No auth required     | Login / Logout                         |
| **Division** | `/`   | `isDivision()` check | Projects, MPR, Purchase, HR, Items     |
| **SORD**   | `/sord` | `isSORD()` check     | All Projects, MPR Inbox, Review, Compile|

---

## 7. Additional Database Schemas (Not Used in Laravel)

Based on SQL dumps, these schemas exist in the database but have **no Eloquent models**:

| Schema | SQL Dump File   | Purpose (Inferred)                 |
|--------|-----------------|------------------------------------|
| `aud`  | `dump-aud.sql`  | Audit trails                       |
| `fin`  | `dump-fin.sql`  | Finance/Salary management          |
| `ina`  | `dump-ina.sql`  | Inventory/Assets                   |

---

> **⚡ Note:** This document is auto-maintained. Jab bhi koi naya feature, table, or column discuss ho, yahan update hoga.
