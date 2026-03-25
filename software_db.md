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

## 4. Deep Schema Mapping

### 4.1 Schema: `cen` (Central/Core)

#### Table: `cen.accounts`
**Purpose:** User/Authentication accounts. Used as Laravel Auth model.

| Column           | Data Type                   | Constraints                     |
|------------------|-----------------------------|---------------------------------|
| `acc_id`         | `INTEGER`                   | **PK**, NOT NULL                |
| `acc_unt_id`     | `INTEGER`                   | NOT NULL, **FK → cen.units(unt_id)** |
| `acc_level`      | `SMALLINT`                  | NOT NULL, **FK → cen.levels(lvl_id)** |
| `acc_type`       | `VARCHAR(255)`              | NOT NULL                        |
| `acc_reportlevel`| `INTEGER`                   | Nullable                        |
| `acc_username`   | `VARCHAR(255)`              | NOT NULL, **UNIQUE**            |
| `acc_pass`       | `VARCHAR(255)`              | NOT NULL                        |
| `acc_startdt`    | `TIMESTAMP WITH TIME ZONE`  | NOT NULL                        |
| `acc_status`     | `VARCHAR(255)`              | NOT NULL                        |
| `acc_enddt`      | `TIMESTAMP WITH TIME ZONE`  | Nullable                        |
| `acc_name`       | `VARCHAR(255)`              | NOT NULL                        |
| `acc_title`      | `VARCHAR(255)`              | Nullable                        |
| `acc_rank`       | `VARCHAR(255)`              | Nullable                        |
| `acc_desig`      | `VARCHAR(255)`              | NOT NULL, **FK → cen.roles(rol_desig)** |
| `acc_desigshort` | `VARCHAR(255)`              | NOT NULL                        |
| `acc_desigtype`  | `VARCHAR(10)`               | NOT NULL                        |
| `acc_lowerm`     | `INTEGER`                   | NOT NULL                        |
| `acc_upperm`     | `INTEGER`                   | NOT NULL                        |
| `acc_access`     | `VARCHAR`                   | NOT NULL                        |
| `acc_lowers`     | `INTEGER`                   | NOT NULL                        |
| `acc_uppers`     | `INTEGER`                   | NOT NULL                        |
| `acc_untname`    | `VARCHAR`                   | NOT NULL                        |
| `acc_untnamesh`  | `VARCHAR`                   | NOT NULL                        |
| `acc_unttype`    | `VARCHAR`                   | NOT NULL                        |
| `acc_auth`       | `VARCHAR`                   | NOT NULL                        |
| `acc_untarea`    | `VARCHAR`                   | NOT NULL                        |

**Eloquent Model:** `App\Models\User` (extends Authenticatable)
- `timestamps = false`
- **Relationships:** `role()` → BelongsTo Role, `unit()` → BelongsTo Unit
- **Custom Methods:** `isSORD()`, `isDivision()` — Role-based access logic

---

#### Table: `cen.units`
**Purpose:** Organizational units (Divisions, Departments, Wings).

| Column              | Data Type      | Constraints       |
|---------------------|----------------|--------------------|
| `unt_id`            | `INTEGER`      | **PK**, NOT NULL   |
| `unt_type`          | `VARCHAR(255)` | NOT NULL           |
| `unt_name`          | `VARCHAR(255)` | NOT NULL           |
| `unt_namesh`        | `VARCHAR(255)` | NOT NULL           |
| `unt_alowerlimit`   | `INTEGER`      | NOT NULL           |
| `unt_aupperlimit`   | `INTEGER`      | NOT NULL           |
| `unt_nlowerlimit`   | `INTEGER`      | NOT NULL           |
| `unt_nupperlimit`   | `INTEGER`      | NOT NULL           |
| `unt_area`          | `VARCHAR`      | Nullable           |
| `unt_leadname`      | `VARCHAR`      | Nullable           |
| `unt_leadtitle`     | `VARCHAR`      | Nullable           |
| `unt_leadrank`      | `VARCHAR`      | Nullable           |
| `unt_leaddesig`     | `VARCHAR`      | Nullable           |
| `unt_leaddesigshort`| `VARCHAR`      | Nullable           |

**Eloquent Model:** `App\Models\Unit`
- `timestamps = false`, fillable: `unt_name`, `unt_type`

---

#### Table: `cen.roles`
**Purpose:** Designation/role definitions linked to units.

| Column           | Data Type      | Constraints                          |
|------------------|----------------|--------------------------------------|
| `rol_desig`      | `VARCHAR(255)` | **PK** (String, non-incrementing)    |
| `rol_xunt_id`    | `INTEGER`      | NOT NULL, **FK → cen.units(unt_id)** |
| `rol_level`      | `SMALLINT`     | NOT NULL                             |
| `rol_desigshort` | `VARCHAR(255)` | NOT NULL                             |
| `rol_type`       | `VARCHAR(255)` | NOT NULL                             |
| `rol_reportlevel`| `SMALLINT`     | Nullable                             |
| `rol_access`     | `VARCHAR`      | NOT NULL                             |
| `rol_authprj`    | `VARCHAR`      | Nullable                             |

**Eloquent Model:** `App\Models\Role`
- `timestamps = false`, `incrementing = false`, `keyType = string`

---

#### Table: `cen.heads`
**Purpose:** Financial/organizational heads linking to units and projects.

| Column          | Data Type      | Constraints    |
|-----------------|----------------|----------------|
| `hed_id`        | `INTEGER`      | **PK**         |
| `hed_name`      | `VARCHAR`      | NOT NULL       |
| `hed_type`      | `VARCHAR`      | NOT NULL       |
| `hed_code`      | `VARCHAR`      | NOT NULL, **UNIQUE** |
| `hed_opendt`    | `DATE`         | Nullable       |
| `hed_closedt`   | `DATE`         | Nullable       |
| `hed_transtype` | `SMALLINT`     | Nullable       |
| `hed_unt_id`    | `INTEGER`      | NOT NULL       |
| `hed_prj_id`    | `INTEGER`      | Nullable       |

---

#### Table: `cen.globalvars`
**Purpose:** System-wide configuration variables. PK: `gvar_name`.

| Column       | Data Type      | Constraints |
|--------------|----------------|-------------|
| `gvar_name`  | `VARCHAR(255)` | **PK**      |
| `gvar_value` | `VARCHAR(255)` | Nullable    |
| `gvar_type`  | `VARCHAR`      | Nullable    |
| `gvar_remarks`| `VARCHAR`     | Nullable    |

---

#### Table: `cen.levels`
**Purpose:** Hierarchical level categories. PK: `lvl_id`.

| Column    | Data Type      | Constraints |
|-----------|----------------|-------------|
| `lvl_id`  | `SMALLINT`     | **PK**      |
| `lvl_cat` | `VARCHAR(255)` | Nullable    |

---

#### Table: `cen.routes`
**Purpose:** Document routing step definitions. PK: `rte_doc`.

| Column     | Data Type      | Constraints |
|------------|----------------|-------------|
| `rte_doc`  | `VARCHAR(255)` | **PK**      |
| `rte_steps`| `VARCHAR(255)` | NOT NULL    |

---

#### Table: `cen.version`
**Purpose:** Database version tracking. PK: `ver_version`.

| Column       | Data Type | Constraints |
|--------------|-----------|-------------|
| `ver_version`| `NUMERIC` | **PK**      |
| `ver_compat` | `NUMERIC` | Nullable    |

---

### 4.2 Schema: `prj` (Projects)

#### Table: `prj.projects`
**Purpose:** Core R&D project records.

| Column         | Data Type  | Constraints                         |
|----------------|------------|-------------------------------------|
| `prj_id`       | `INTEGER`  | **PK**                              |
| `prj_title`    | `VARCHAR`  | NOT NULL                            |
| `prj_cost`     | `NUMERIC`  | Nullable                            |
| `prj_startdt`  | `DATE`     | Nullable                            |
| `prj_enddt`    | `DATE`     | Nullable                            |
| `prj_unt_id`   | `INTEGER`  | **FK → cen.units(unt_id)**          |
| `prj_scope`    | `TEXT`     | Nullable                            |
| `prj_sponsor`  | `VARCHAR`  | Nullable                            |
| `prj_status`   | `VARCHAR`  | Nullable                            |
| `prj_reporting`| `VARCHAR`  | Nullable                            |
| `prj_code`     | `VARCHAR`  | Nullable                            |
| `prj_propcost` | `NUMERIC`  | Nullable                            |
| `prj_aprvcost` | `NUMERIC`  | Nullable                            |

**Eloquent Model:** `App\Models\Project`
- `timestamps = false`
- **Relationships:**
  - `unit()` → BelongsTo Unit
  - `milestones()` → HasMany Milestone
  - `history()` → HasMany PrgHistory
  - `attachments()` → HasMany PrjAttachment (where `jat_objtype = 'Project'`)
  - `document()` → HasOne Document (MPR)

---

#### Table: `prj.milestones`
**Purpose:** Physical/Financial milestones per project.

| Column         | Data Type  | Constraints                          |
|----------------|------------|--------------------------------------|
| `msn_id`       | `INTEGER`  | **PK**                               |
| `msn_xprj_id`  | `INTEGER`  | **FK → prj.projects(prj_id)**        |
| `msn_type`     | `VARCHAR`  | Physical / Financial                 |
| `msn_desc`     | `TEXT`     | Description                          |
| `msn_targetdt` | `DATE`     | Target date                          |
| `msn_status`   | `VARCHAR`  | Pending / Completed                  |
| `msn_cost`     | `NUMERIC`  | Nullable                             |
| `msn_startdt`  | `DATE`     | Nullable                             |
| `msn_achvdt`   | `DATE`     | Achievement date, Nullable           |
| `msn_comp`     | `VARCHAR`  | Nullable                             |
| `msn_rem`      | `TEXT`     | Remarks, Nullable                    |

**Eloquent Model:** `App\Models\Milestone`
- **Relationship:** `project()` → BelongsTo Project

---

#### Table: `prj.prghistory`
**Purpose:** Progress history entries for projects (used for tracking progress updates).

| Column          | Data Type  | Constraints                          |
|-----------------|------------|--------------------------------------|
| `pgh_id`        | `INTEGER`  | **PK**                               |
| `pgh_xprj_id`   | `INTEGER`  | **FK → prj.projects(prj_id)**        |
| `pgh_dtg`       | `TIMESTAMP`| Date/Time Group                      |
| `pgh_progress`  | `TEXT`     | Progress description                 |
| `pgh_author`    | `VARCHAR`  | NOT NULL                             |
| `pgh_status`    | `VARCHAR`  | NOT NULL                             |
| `pgh_level`     | `VARCHAR`  | NOT NULL                             |
| `pgh_underedit` | `BOOLEAN`  | Nullable                             |

**Eloquent Model:** `App\Models\PrgHistory`
- **Relationship:** `project()` → BelongsTo Project

---

#### Table: `prj.comments`
**Purpose:** Comments on progress history entries.

| Column        | Data Type  | Constraints                             |
|---------------|------------|-----------------------------------------|
| `cmt_id`      | `INTEGER`  | **PK**                                  |
| `cmt_xpgh_id` | `INTEGER`  | **FK → prj.prghistory(pgh_id)**         |
| `cmt_dtg`     | `TIMESTAMP`| Date/Time Group                         |
| `cmt_comment` | `TEXT`     | Nullable                                |
| `cmt_author`  | `VARCHAR`  | Nullable                                |
| `cmt_status`  | `VARCHAR`  | Nullable                                |

**Eloquent Model:** `App\Models\Comment`
- **Relationship:** `history()` → BelongsTo PrgHistory

---

#### Table: `prj.events`
**Purpose:** Project events timeline.

| Column       | Data Type  | Constraints                        |
|--------------|------------|------------------------------------|
| `evt_id`     | `INTEGER`  | **PK**                             |
| `evt_name`   | `VARCHAR`  | Nullable                           |
| `evt_doer`   | `VARCHAR`  | Nullable                           |
| `evt_dtg`    | `TIMESTAMP`| Nullable                           |
| `evt_xprj_id`| `INTEGER`  | **FK → prj.projects(prj_id)**      |

**Eloquent Model:** `App\Models\Event`

---

#### Table: `prj.prjattachments`
**Purpose:** File attachments linked to projects (polymorphic by `jat_objtype`).

| Column       | Data Type  | Constraints  |
|--------------|------------|--------------|
| `jat_id`     | `INTEGER`  | **PK**       |
| `jat_objtype`| `VARCHAR`  | e.g. 'Project' |
| `jat_objid`  | `INTEGER`  | Object ID    |
| `jat_type`   | `VARCHAR`  | Nullable     |
| `jat_path`   | `VARCHAR`  | File path    |

**Eloquent Model:** `App\Models\PrjAttachment`

---

#### Table: `prj.mprgroup`
**Purpose:** MPR (Monthly Progress Report) grouping for batch compilation.

| Column      | Data Type  | Constraints |
|-------------|------------|-------------|
| `mgp_id`    | `INTEGER`  | **PK**      |
| `mgp_name`  | `VARCHAR`  | Nullable    |
| `mgp_dtg`   | `TIMESTAMP`| Nullable    |
| `mgp_status`| `VARCHAR`  | Nullable    |

**Eloquent Model:** `App\Models\MprGroup`

---

### 4.3 Schema: `doc` (Document Management / MPR Workflow)

#### Table: `doc.documents`
**Purpose:** MPR documents linked to projects, tracking ownership and workflow status.

| Column            | Data Type    | Constraints                        |
|-------------------|--------------|------------------------------------|
| `doc_id`          | `INTEGER`    | **PK**                             |
| `prj_id`          | `INTEGER`    | **FK → prj.projects(prj_id)**      |
| `doc_type`        | `VARCHAR`    | e.g. 'MPR'                        |
| `doc_ref_no`      | `VARCHAR`    | Reference number                   |
| `current_owner_id`| `INTEGER`    | **FK → cen.accounts(acc_id)**      |
| `creator_id`      | `INTEGER`    | **FK → cen.accounts(acc_id)**      |
| `status`          | `VARCHAR`    | Workflow status                    |
| `created_at`      | `TIMESTAMP`  | Laravel managed                    |
| `updated_at`      | `TIMESTAMP`  | Laravel managed                    |

**Eloquent Model:** `App\Models\Document`
- **Relationships:**
  - `project()` → BelongsTo Project
  - `currentOwner()` → BelongsTo User
  - `creator()` → BelongsTo User
  - `versions()` → HasMany DocumentVersion (ordered by `ver_id` desc)
  - `latestVersion()` → HasOne DocumentVersion (latest)
  - `history()` → HasMany DocumentHistory (ordered by `created_at` desc)

---

#### Table: `doc.document_versions`
**Purpose:** Versioned content snapshots for each document (JSON content).

| Column        | Data Type  | Constraints                        |
|---------------|------------|------------------------------------|
| `ver_id`      | `INTEGER`  | **PK**                             |
| `doc_id`      | `INTEGER`  | **FK → doc.documents(doc_id)**     |
| `version_no`  | `INTEGER`  | Version counter                    |
| `content_data`| `JSON`     | MPR content (auto-cast to Array)   |
| `remarks`     | `TEXT`     | Nullable                           |
| `action_by`   | `INTEGER`  | **FK → cen.accounts(acc_id)**      |
| `action_date` | `TIMESTAMP`| Manually managed                   |

**Eloquent Model:** `App\Models\DocumentVersion`
- `$casts['content_data'] = 'array'`
- **Relationship:** `actor()` → BelongsTo User

---

#### Table: `doc.document_history`
**Purpose:** Audit trail for document workflow actions (submit, approve, return, etc.).

| Column        | Data Type  | Constraints                        |
|---------------|------------|------------------------------------|
| `hist_id`     | `INTEGER`  | **PK**                             |
| `doc_id`      | `INTEGER`  | **FK → doc.documents(doc_id)**     |
| `from_user_id`| `INTEGER`  | **FK → cen.accounts(acc_id)**      |
| `to_user_id`  | `INTEGER`  | **FK → cen.accounts(acc_id)**      |
| `action_type` | `VARCHAR`  | e.g. 'submitted', 'approved', 'returned' |
| `notes`       | `TEXT`     | Nullable                           |
| `created_at`  | `TIMESTAMP`| Manually managed                   |

**Eloquent Model:** `App\Models\DocumentHistory`
- `timestamps = false`
- **Relationships:** `fromUser()`, `toUser()` → BelongsTo User, `document()` → BelongsTo Document

---

### 4.4 Schema: `pur` (Procurement/Purchase)

#### Table: `pur.purcases`
**Purpose:** Purchase cases (procurement requisitions).

| Column        | Data Type  | Constraints                        |
|---------------|------------|------------------------------------|
| `pcs_id`      | `INTEGER`  | **PK**                             |
| `pcs_title`   | `VARCHAR`  | Nullable                           |
| `pcs_date`    | `DATE`     | Nullable                           |
| `pcs_status`  | `VARCHAR`  | Nullable                           |
| `pcs_type`    | `VARCHAR`  | Nullable                           |
| `pcs_unt_id`  | `INTEGER`  | Unit ID                            |
| `pcs_hed_id`  | `INTEGER`  | **FK → cen.heads(hed_id)**         |
| `pcs_effhed_id`| `INTEGER` | Effective Head ID                  |
| `pcs_effunt_id`| `INTEGER` | Effective Unit ID                  |
| `pcs_price`   | `NUMERIC`  | Nullable                           |
| `pcs_remarks` | `TEXT`     | Nullable                           |
| `pcs_subject` | `VARCHAR`  | Nullable                           |
| `pcs_minute`  | `VARCHAR`  | Nullable                           |

**Eloquent Model:** `App\Models\Purchase`
- **Relationships:**
  - `project()` → BelongsTo Project (via `pcs_hed_id`)
  - `items()` → HasMany PurchaseItem
  - `quotes()` → HasMany Quote
  - `noQuotes()` → HasMany NoQuote
  - `attachments()` → HasMany PurAttachment

---

#### Table: `pur.purcaseitems`
**Purpose:** Line items for each purchase case.

| Column       | Data Type  | Constraints                         |
|--------------|------------|-------------------------------------|
| `pci_id`     | `INTEGER`  | **PK**                              |
| `pci_pcs_id` | `INTEGER`  | **FK → pur.purcases(pcs_id)**       |
| `pci_serial` | `INTEGER`  | Serial/order number                 |
| `pci_desc`   | `TEXT`     | Item description                    |
| `pci_qty`    | `NUMERIC`  | Quantity                            |
| `pci_qtyunit`| `VARCHAR`  | Unit of measure                     |
| `pci_price`  | `NUMERIC`  | Price                               |
| `pci_type`   | `VARCHAR`  | Nullable                            |
| `pci_subtype`| `VARCHAR`  | Nullable                            |

**Eloquent Model:** `App\Models\PurchaseItem`
- **Relationship:** `purchase()` → BelongsTo Purchase

---

#### Table: `pur.quotes`
**Purpose:** Vendor price quotations for purchase cases.

| Column          | Data Type  | Constraints                        |
|-----------------|------------|------------------------------------|
| `qte_id`        | `INTEGER`  | **PK**                             |
| `qte_pcs_id`    | `INTEGER`  | **FK → pur.purcases(pcs_id)**      |
| `qte_date`      | `DATE`     | Nullable                           |
| `qte_firmname`  | `VARCHAR`  | Firm name (denormalized)           |
| `qte_price`     | `NUMERIC`  | Quoted price                       |
| `qte_num`       | `VARCHAR`  | Quote reference number             |
| `qte_techaccept`| `VARCHAR`  | Technical acceptance status        |
| `qte_frm_id`    | `INTEGER`  | **FK → frm.firmz(frm_id)**        |

**Eloquent Model:** `App\Models\Quote`
- **Relationship:** `firm()` → BelongsTo Firm

---

#### Table: `pur.noquotes`
**Purpose:** Records for firms that didn't provide quotes.

| Column       | Data Type  | Constraints                   |
|--------------|------------|-------------------------------|
| `nqt_id`     | `INTEGER`  | **PK**                        |
| `nqt_pcs_id` | `INTEGER`  | FK → pur.purcases(pcs_id)    |

**Eloquent Model:** `App\Models\NoQuote`

---

#### Table: `pur.purattachments`
**Purpose:** Attachments for purchase cases.

| Column     | Data Type  | Constraints                    |
|------------|------------|--------------------------------|
| `pat_id`   | `INTEGER`  | **PK**                         |
| `pat_objid`| `INTEGER`  | FK → pur.purcases(pcs_id)     |

**Eloquent Model:** `App\Models\PurAttachment`

---

### 4.5 Schema: `hr` (Human Resources)

#### Table: `hr.emps`
**Purpose:** Employee records.

| Column          | Data Type  | Constraints                |
|-----------------|------------|----------------------------|
| `emp_id`        | `VARCHAR`  | **PK** (String, non-incrementing) |
| `emp_cnic`      | `VARCHAR`  | National ID                |
| `emp_name`      | `VARCHAR`  | Full name                  |
| `emp_joindt`    | `DATE`     | Join date                  |
| `emp_locked`    | `BOOLEAN`  | Nullable                   |
| `emp_rank`      | `VARCHAR`  | Nullable                   |
| `emp_status`    | `VARCHAR`  | Active/Closed              |
| `emp_remarks`   | `TEXT`     | Nullable                   |
| `emp_unt_id`    | `INTEGER`  | Unit ID                    |
| `emp_hed_id`    | `INTEGER`  | Head ID                    |
| `emp_lastdt`    | `DATE`     | Nullable                   |
| `emp_title`     | `VARCHAR`  | Nullable                   |
| `emp_photodest` | `VARCHAR`  | Photo file path            |

**Eloquent Model:** `App\Models\Employee`
- `timestamps = false`, `incrementing = false`, `keyType = string`

---

### 4.6 Schema: `frm` (Firms/Vendors)

#### Table: `frm.firmz`
**Purpose:** Vendor/firm registry for procurement.

| Column        | Data Type  | Constraints |
|---------------|------------|-------------|
| `frm_id`      | `INTEGER`  | **PK**      |
| `frm_name`    | `VARCHAR`  | Firm name   |
| `frm_address` | `TEXT`     | Nullable    |
| `frm_contact` | `VARCHAR`  | Nullable    |

**Eloquent Model:** `App\Models\Firm`
- **Relationship:** `quotes()` → HasMany Quote

---

### 4.7 Schema: `puritems` (Item Registry — Real Tables)

#### Table: `puritems.categories`

| Column     | Data Type      | Constraints              |
|------------|----------------|--------------------------|
| `cat_id`   | `SERIAL`       | **PK**                   |
| `cat_name` | `VARCHAR(120)` | NOT NULL, **UNIQUE**     |

**Eloquent Model:** `App\Models\PurCategory`

---

#### Table: `puritems.subcategories`

| Column       | Data Type      | Constraints                                    |
|--------------|----------------|------------------------------------------------|
| `sub_id`     | `SERIAL`       | **PK**                                         |
| `sub_name`   | `VARCHAR(120)` | NOT NULL                                       |
| `sub_cat_id` | `INTEGER`      | NOT NULL, **FK → puritems.categories(cat_id)** |

**UNIQUE Constraint:** `(sub_cat_id, sub_name)`

**Eloquent Model:** `App\Models\PurSubcategory`

---

#### Table: `puritems.items`

| Column       | Data Type      | Constraints                                         |
|--------------|----------------|-----------------------------------------------------|
| `itm_id`     | `SERIAL`       | **PK**                                              |
| `itm_title`  | `TEXT`         | NOT NULL                                            |
| `itm_desc`   | `TEXT`         | Nullable                                            |
| `itm_qtyunit`| `VARCHAR(32)`  | NOT NULL, DEFAULT 'unit'                            |
| `itm_cat_id` | `INTEGER`      | **FK → puritems.categories(cat_id)**, ON DELETE SET NULL |
| `itm_sub_id` | `INTEGER`      | **FK → puritems.subcategories(sub_id)**, ON DELETE SET NULL |
| `itm_unt_id` | `INTEGER`      | Nullable                                            |
| `itm_hed_id` | `INTEGER`      | Nullable                                            |
| `itm_acc_id` | `INTEGER`      | Nullable                                            |
| `created_at` | `TIMESTAMP`    | DEFAULT CURRENT_TIMESTAMP                           |

**Unique Index:** `puritems_items_uq_idx` on `(itm_title, itm_qtyunit, COALESCE(itm_unt_id,0), COALESCE(itm_hed_id,0), COALESCE(itm_acc_id,0))`

**Eloquent Model:** `App\Models\PurItem` (accessed via `purnew.items` view)

---

#### Table: `puritems.prices`

| Column          | Data Type      | Constraints                                    |
|-----------------|----------------|------------------------------------------------|
| `prc_id`        | `SERIAL`       | **PK**                                         |
| `prc_itm_id`    | `INTEGER`      | NOT NULL, **FK → puritems.items(itm_id)**      |
| `prc_base`      | `NUMERIC`      | NOT NULL, DEFAULT 0                            |
| `prc_gst`       | `NUMERIC`      | NOT NULL, DEFAULT 0                            |
| `prc_sst`       | `NUMERIC`      | NOT NULL, DEFAULT 0                            |
| `prc_gross`     | `NUMERIC`      | NOT NULL, DEFAULT 0                            |
| `prc_qty`       | `NUMERIC`      | NOT NULL, DEFAULT 1                            |
| `prc_qtyunit`   | `VARCHAR(32)`  | NOT NULL, DEFAULT 'unit'                       |
| `effective_date` | `DATE`        | NOT NULL, DEFAULT CURRENT_DATE                 |

**Index:** `puritems_prices_item_idx` on `(prc_itm_id)`

**Eloquent Model:** `App\Models\PurItemPrice` (accessed via `purnew.prices` view)
- **Relationship:** `item()` → BelongsTo PurItem

---

#### Table: `puritems.rfqs`

| Column           | Data Type      | Constraints                   |
|------------------|----------------|-------------------------------|
| `rfq_id`         | `SERIAL`       | **PK**                        |
| `rfq_title`      | `VARCHAR(180)` | NOT NULL                      |
| `rfq_unt_id`     | `INTEGER`      | Nullable                      |
| `rfq_created_by` | `INTEGER`      | Nullable                      |
| `rfq_status`     | `VARCHAR(24)`  | NOT NULL, DEFAULT 'draft'     |
| `rfq_total`      | `NUMERIC`      | NOT NULL, DEFAULT 0           |
| `created_at`     | `TIMESTAMP`    | DEFAULT CURRENT_TIMESTAMP     |

---

#### Table: `puritems.rfq_items`

| Column        | Data Type  | Constraints                                      |
|---------------|------------|--------------------------------------------------|
| `rfi_id`      | `SERIAL`   | **PK**                                           |
| `rfi_rfq_id`  | `INTEGER`  | NOT NULL, **FK → puritems.rfqs(rfq_id)**         |
| `rfi_itm_id`  | `INTEGER`  | NOT NULL, **FK → puritems.items(itm_id)**        |
| `rfi_price_id`| `INTEGER`  | **FK → puritems.prices(prc_id)**, ON DELETE SET NULL |
| `rfi_qty`     | `NUMERIC`  | NOT NULL, DEFAULT 1                              |
| `rfi_total`   | `NUMERIC`  | NOT NULL, DEFAULT 0                              |

---

### 4.8 Schema: `purnew` (Views Over `puritems`)

> ⚠️ **All tables in `purnew` are PostgreSQL VIEWS** pointing to `puritems` tables.

| View Name        | Source Table           |
|------------------|------------------------|
| `purnew.items`   | `puritems.items`       |
| `purnew.prices`  | `puritems.prices`      |
| `purnew.rfqs`    | `puritems.rfqs`        |
| `purnew.rfq_items`| `puritems.rfq_items`  |

**Eloquent Models using these views:**
- `PurItem` → `purnew.items`
- `PurItemPrice` → `purnew.prices`
- `PurRfq` → `purnew.rfq` (custom table, see model)
- `PurRfqItem` → `purnew.rfq_items`

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
