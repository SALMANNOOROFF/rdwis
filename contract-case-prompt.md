# Contract Case Management System — Full Cursor/AI Prompt

> **Project**: RDWIS (Laravel 12, PHP 8.2+, Blade/Bootstrap 4, AdminLTE 3.x, jQuery, PostgreSQL)
> **Module**: HR → Contract Cases (Division-side entry + Full Approval Workflow)
> **Prepared for**: Cursor AI / Antigravity autonomous execution

---

## SECTION A — EXISTING SYSTEM DOCUMENTATION (Context for AI)

> Read this section fully before writing any code. Understand the existing database and workflow before touching any file.

---

### A1. Workflow & Statuses

Contract case ka safar Division se shuru hota hai aur Fulfilled tak mukhtalif marahil se guzarta hai:

| Status | Action | Kaun karta hai? | Kya hota hai? |
|:---|:---|:---|:---|
| `Draft` | Initiate Case | Division / Editor | Case create hota hai. Proposed salary, grade, dates enter ki jati hain. |
| `Under HR Scrutiny` | Release Case | Division / Unit | Case HR ko forward hota hai verification ke liye. |
| `Under Finance Scrutiny` | Forward by HR | HR Manager | HR check ke baad Finance ko bhejta hai budget verify karne ke liye. |
| `Under Approval` | Forward by Finance | Finance Manager | Finance ke baad case MD / Final Approver ke paas jata hai. |
| `Approved` | Approve | MD / Approver | Final terms (Approved Salary / Grade) lock hoti hain. |
| `Fulfilled` | Fulfill | System (Auto) / HR | Case close hota hai. `hr_contracts` mein naya active contract banta hai. |
| `Under Revision` | Return | HR / Finance / MD | Corrections ke liye case wapis bheja jata hai. |
| `Cancelled / Rejected` | Cancel / Reject | Any Manager | Case bina contract banaye khatam ho jata hai. |

---

### A2. Case Types (Database Codes in `ctc_type`)

| Code | Name | Description |
|:---|:---|:---|
| `Hg` | Fresh Hiring | Pehli baar naya employee hire kiya ja raha ho. Max 1 saal. |
| `Ce` | Contract Extension | Maujooda contract ko thode waqt ke liye extend karna. Same salary/designation. Max 1 saal. |
| `Cr` | Contract Renewal | 1 saal ke liye renewal, performance appraisal ya promotion ke baad. |
| `Rh` | Rehiring | Purana employee jo pehle chhod gaya tha, dobara hire kiya ja raha ho. Max 1 saal. |

> **Note for AI**: Database mein `ctc_type` field ki existing values confirm karo pehle. Agar `Rh` (Rehiring) column nahi hai toh migration create karo.

---

### A3. User Roles & Access Control (`varMode` / `varUnitArea`)

| Role | `varUnitArea` | Kya kar sakta hai? |
|:---|:---|:---|
| Division / Editor | division | Draft create, edit, release karna |
| HR Manager | hr | Forward / Return from HR Scrutiny. Fulfill karna (after MD approval). |
| Finance Manager | fin | Forward / Return from Finance Scrutiny. |
| MD / Approver | md | Final approve karna, approved salary/grade set karna. |

---

### A4. Key Database Tables (Existing — DO NOT DROP)

**Step 1 for AI: Run `\d+ hr_ctrcases` and `\d+ hr_contracts` on the PostgreSQL DB and read column names/types before writing any query or migration.**

| Table | Purpose |
|:---|:---|
| `hr_ctrcases` | Main cases table. Every case from Draft to Fulfilled lives here. |
| `hr_contracts` | Active contracts. Created when a case is `Fulfilled`. |
| `hr_ctrcaseminutes` | Minute sheet / approval document data. |
| `hr_contractplans` | Financial schedule / contract plan details. |
| `hr_employees` | Employee master data. Referenced by `emp_id`. |

**Fields to confirm in `hr_ctrcases` before coding:**

```
ctc_id, ctc_type, ctc_status, ctc_empid, ctc_empnamecomp,
ctc_newjobtitle, ctc_newgrade, ctc_newsalary, ctc_newprob,
ctc_startdate, ctc_enddate, ctc_divisionid, ctc_projectcode,
ctc_releasedby, ctc_releasedat, ctc_createdby, ctc_createdat
```

> If any field is missing, create an Artisan migration to add it. Never hardcode column names without verifying.

---

### A5. Technical Functions (Existing VBA/PHP Logic to Port)

| Function | What it does |
|:---|:---|
| `AddCtrCase()` | Naya case banata hai, purane contract se data copy karta hai. |
| `ReleaseCC()` | Status → "Under HR Scrutiny" |
| `ForwardCC()` | Status stage-wise agay karta hai. |
| `ReturnCC()` | Status stage-wise peeche karta hai (Under Revision). |
| `FulfillCC()` | Transaction: purana contract close karo → naya `hr_contracts` insert karo → case Fulfilled mark karo. |

---

---

## SECTION B — NEW FEATURE: Division-Side Contract Initiation UI

> Yeh naya feature hai. Is section mein puri UI, form logic, aur backend ka detailed specification hai.

---

### B1. Sidebar Menu Change

**File**: `resources/views/layouts/sidebar.blade.php` (ya relevant AdminLTE sidebar partial)

**Kya karna hai**:

Sidebar mein `HR` dropdown mein:
- `Current` ko rename karo → `Employees`
- Uske neeche naya item add karo: `Initiate Contract Cases`
- Route: `division.contract-cases.index`

```blade
{{-- HR Dropdown --}}
<li class="nav-item has-treeview">
  <a href="#" class="nav-link">
    <i class="nav-icon fas fa-users"></i>
    <p>HR <i class="right fas fa-angle-left"></i></p>
  </a>
  <ul class="nav nav-treeview">
    <li class="nav-item">
      <a href="{{ route('division.employees.index') }}" class="nav-link">
        <i class="far fa-circle nav-icon"></i>
        <p>Employees</p>
      </a>
    </li>
    <li class="nav-item">
      <a href="{{ route('division.contract-cases.index') }}" class="nav-link">
        <i class="far fa-circle nav-icon"></i>
        <p>Initiate Contract Cases</p>
      </a>
    </li>
  </ul>
</li>
```

---

### B2. Contract Cases Index Page (`division.contract-cases.index`)

**Route**: `GET /division/contract-cases`
**Controller**: `Division\ContractCaseController@index`
**View**: `resources/views/division/contract-cases/index.blade.php`

---

#### B2.1 — Top: 4 Summary Cards

Page ke upar 4 Bootstrap cards honge (1 row, 4 columns). Yeh sirf informational/definition cards hain, neeche click action bhi denge.

```
[Fresh Hiring]     [Contract Extension]   [Contract Renewal]    [Rehiring]
```

**Card Content** (exact text use karo):

| Card | Title | Body Text |
|:---|:---|:---|
| 1 | Fresh Hiring | "The said term is used for 'New Hiring'. If an employee is being hired for the very first time for a period up to 1 Year." |
| 2 | Contract Extension | "The said term is used if the contract is being extended up to 1 Year on the same salary and designation." |
| 3 | Contract Renewal | "The said term is used if contract is being renewed for 01 x year based on performance appraisal or promotion." |
| 4 | Rehiring | "The said term is used if a former employee is being hired again for a period of 1 Year." |

**Styling**:
- Project ke existing CSS variables use karo (VMS/RDWIS theme se match karo).
- Har card pe ek icon ho (e.g. FontAwesome: `fa-user-plus`, `fa-calendar-plus`, `fa-sync-alt`, `fa-undo`).
- Cards clickable hain. Click karne par neeche wali list filter ho jaye (ya initiate modal/page khule — B3 mein detail hai).
- Active/selected card ko highlight karo (border ya background change).

---

#### B2.2 — Bottom: Division's Contract Cases List

Cards ke neeche ek responsive table/list ho jo **logged-in division ki apni cases** dikhaye.

**Data Source**: `hr_ctrcases` WHERE `ctc_divisionid = auth()->user()->division_id`

**Columns**:

| Column | Field | Notes |
|:---|:---|:---|
| Case # | `ctc_id` | — |
| Type | `ctc_type` | Show label: Fresh Hiring / Extension / Renewal / Rehiring |
| Candidate / Employee | `ctc_empnamecomp` | — |
| Status | `ctc_status` | Badge with color coding (below) |
| Proposed Salary | `ctc_newsalary` | — |
| Start Date | `ctc_startdate` | — |
| End Date | `ctc_enddate` | — |
| Actions | — | View / Edit (only if Draft or Under Revision) / Release |

**Status Badge Colors**:

| Status | Badge Color |
|:---|:---|
| Draft | `badge-secondary` |
| Under HR Scrutiny | `badge-info` |
| Under Finance Scrutiny | `badge-warning` |
| Under Approval | `badge-primary` |
| Approved | `badge-success` |
| Fulfilled | `badge-dark` |
| Under Revision | `badge-warning` |
| Cancelled / Rejected | `badge-danger` |

**Filter tabs** (optional but preferred): All / Draft / Active (In Progress) / Fulfilled / Cancelled

---

### B3. Fresh Hiring Form (Main New Feature)

**Trigger**: Click on "Fresh Hiring" card → opens a **dedicated page** (not modal, form is too large).

**Route**: `GET /division/contract-cases/create?type=Hg`
**Controller**: `Division\ContractCaseController@create`
**View**: `resources/views/division/contract-cases/create.blade.php`

**All updates on this form are AJAX / live (no full page reload).**

---

#### B3.1 — Form Sections (in order, top to bottom)

---

##### Section 1: Candidate Information

| Field | Input Type | Validation | Notes |
|:---|:---|:---|:---|
| Candidate Full Name | Text | Required, max 200 | `ctc_empnamecomp` |
| Employee Type | Select | Required | Options: Civilian / Gazetted / Non-Gazetted / Contract — match existing `hr_employees.emp_type` values |
| Proposed Designation | Text | Required | `ctc_newjobtitle` |
| Proposed Grade | Select/Text | Required | `ctc_newgrade` — pull from existing grades if lookup table exists |
| Proposed Salary (PKR) | Number | Required, min 0 | `ctc_newsalary` — live formatting with commas |

---

##### Section 2: Contract Duration

| Field | Input Type | Validation | Notes |
|:---|:---|:---|:---|
| Probation Period (Months) | Number | Required, min 1, max 6 | `ctc_newprob` |
| Contract Start Date | Date | Required | `ctc_startdate` |
| Contract End Date | Date | Required, max = start + 12 months | `ctc_enddate` — auto-calculate but editable |
| Duration (Live Display) | Read-only text | — | Auto-calculate: "X months Y days" from start/end date. Update on every date change via JS. |

> **JS Logic**: Jaise hi start date ya end date change ho, automatically duration calculate karo aur display karo. End date ko max = start + 365 days enforce karo.

---

##### Section 3: JD / Documents

| Field | Input Type | Notes |
|:---|:---|:---|
| Job Description | Textarea | `ctc_jd` — Rich text ya plain textarea |
| CV Upload | File Input | `ctc_cv_path` — store in `storage/app/contract-cases/cv/` |
| CNIC Number | Text (masked: `XXXXX-XXXXXXX-X`) | `ctc_cnic` — validation: 13 digits |
| Contact Number | Text | `ctc_contact` — Division user fills this |

---

##### Section 4: Project Assignment (Tab-based toggle)

Yahan do tabs hain. Sirf EK active hoga at a time:

**Tab A — Single Project (Whole Contract)**

| Field | Input Type | Notes |
|:---|:---|:---|
| Project Code | Select | Pull from `projects` table filtered by `division_id` of logged-in user. Show: `project_code — project_name` |

**Tab B — Monthly Project Allocation**

Jab yeh tab active ho, ek dynamic table dikhao:

| Month-Year | Project Code |
|:---|:---|
| Jul 2025 | [Select: project list] |
| Aug 2025 | [Select: project list] |
| ... | ... |

- Rows **auto-generate** based on contract start/end date. Jitne months contract ka hoga utni rows hongi.
- Jab start/end date change ho, table auto-update ho (AJAX ya pure JS).
- Har row mein project dropdown wahi filtered list hogi (division ke projects).

> **DB**: Yeh data `hr_contractplans` mein save hoga. Ek row per month per case.

---

##### Section 5: Form Actions

| Button | Action |
|:---|:---|
| Save as Draft | AJAX POST → save with `ctc_status = 'Draft'`. Success toast dikhao. |
| Release | AJAX POST → validate sab required fields → save → `ctc_status = 'Under HR Scrutiny'` → comment modal kholo. Comment optional ya required (confirm with team). |
| Cancel | List page par wapis jao. |

---

### B4. Release Modal (Comment Box)

Jab "Release" dabaya jaye:

1. Ek Bootstrap modal khule.
2. Usmein ek textarea ho: "Comments / Remarks (optional)"
3. "Confirm Release" button → AJAX call → status update → success → redirect to list.

---

### B5. HR View — Division Employee Strength (Read-only Detail)

**Ye HR ke liye extra view hai.**

Jab HR Manager kisi case ko `Under HR Scrutiny` mein dekhe, ek additional section/panel dikhao:

**Title**: "Division Employee Strength"

**Content** (text-based, no charts needed):

Pull karo `hr_employees` ya `hr_contracts` se:

```
Division Name: [Division Name]
Total Active Employees: XX
  - Civilian: X
  - Gazetted: X
  - Non-Gazetted: X
  - Contract: X

Current Active Contracts:
  [List: emp_name | designation | grade | contract_end_date | status]

Upcoming Expirations (next 30 days): X employees
```

Query:
```sql
SELECT e.emp_type, COUNT(*) as total
FROM hr_contracts c
JOIN hr_employees e ON e.emp_id = c.emp_id
WHERE c.division_id = [case_division_id]
  AND c.ctc_status = 'Active'
GROUP BY e.emp_type;
```

> Confirm exact column names from DB before writing this query.

---

---

## SECTION C — COMPLETE APPROVAL WORKFLOW (After Division Release)

> Yeh existing workflow hai jo extend ho raha hai. Reference only — port to Laravel controllers properly.

---

### C1. HR Scrutiny Stage

**Who**: `varUnitArea = 'hr'`
**Page**: `hr.contract-cases.show`

Actions available:
- **Forward** → status = `Under Finance Scrutiny` + comment required
- **Return** → status = `Under Revision` + comment required

HR can also view Division Employee Strength panel (B5).

---

### C2. Finance Scrutiny Stage

**Who**: `varUnitArea = 'fin'`

Actions:
- **Forward** → status = `Under Approval` + comment
- **Return** → status = `Under Revision` + comment

Finance verifies: proposed salary, allowances, budget availability.

---

### C3. MD / Approver Stage

**Who**: `varUnitArea = 'md'`

Fields editable by MD:
- Approved Salary (`ctc_approvedsalary`)
- Approved Grade (`ctc_approvedgrade`)
- Approved Probation Terms (`ctc_approvedprob`)

Actions:
- **Approve** → status = `Approved`
- **Return** → status = `Under Revision`

---

### C4. Fulfill Stage

**Who**: HR Manager (after MD approval)
**Triggered**: status = `Approved` → HR clicks "Fulfill"

**`FulfillCC()` Transaction Logic** (use DB::transaction):

```php
DB::transaction(function () use ($case) {
    // Step 1: Close previous contract if overlapping
    // Step 2: Insert new record in hr_contracts
    // Step 3: Update hr_ctrcases.ctc_status = 'Fulfilled'
    // Step 4: If Hiring (Hg) case: emp_id must exist first
    //         If emp_id not yet created, show "Add Employee" button first
    //         Use cmdEmpAdd flow → create hr_employees record → then fulfill
});
```

---

---

## SECTION D — FILE STRUCTURE (New Files to Create)

```
app/Http/Controllers/Division/ContractCaseController.php
app/Http/Controllers/HR/ContractCaseController.php
app/Http/Controllers/Finance/ContractCaseController.php
app/Http/Controllers/MD/ContractCaseController.php

app/Models/HrCtrCase.php
app/Models/HrContract.php
app/Models/HrContractPlan.php

resources/views/division/contract-cases/index.blade.php
resources/views/division/contract-cases/create.blade.php
resources/views/division/contract-cases/show.blade.php
resources/views/hr/contract-cases/index.blade.php
resources/views/hr/contract-cases/show.blade.php
resources/views/finance/contract-cases/show.blade.php
resources/views/md/contract-cases/show.blade.php

routes/web.php  (add new route groups)
database/migrations/xxxx_add_rehiring_and_extra_fields_to_hr_ctrcases.php
```

---

## SECTION E — ROUTES

```php
// Division
Route::prefix('division')->middleware(['auth', 'role:division'])->group(function () {
    Route::get('/contract-cases', [Division\ContractCaseController::class, 'index'])
         ->name('division.contract-cases.index');
    Route::get('/contract-cases/create', [Division\ContractCaseController::class, 'create'])
         ->name('division.contract-cases.create');
    Route::post('/contract-cases', [Division\ContractCaseController::class, 'store'])
         ->name('division.contract-cases.store');
    Route::get('/contract-cases/{id}', [Division\ContractCaseController::class, 'show'])
         ->name('division.contract-cases.show');
    Route::put('/contract-cases/{id}', [Division\ContractCaseController::class, 'update'])
         ->name('division.contract-cases.update');
    Route::post('/contract-cases/{id}/release', [Division\ContractCaseController::class, 'release'])
         ->name('division.contract-cases.release');
});

// HR
Route::prefix('hr')->middleware(['auth', 'role:hr'])->group(function () {
    Route::get('/contract-cases', [HR\ContractCaseController::class, 'index'])
         ->name('hr.contract-cases.index');
    Route::post('/contract-cases/{id}/forward', [HR\ContractCaseController::class, 'forward'])
         ->name('hr.contract-cases.forward');
    Route::post('/contract-cases/{id}/return', [HR\ContractCaseController::class, 'return'])
         ->name('hr.contract-cases.return');
    Route::post('/contract-cases/{id}/fulfill', [HR\ContractCaseController::class, 'fulfill'])
         ->name('hr.contract-cases.fulfill');
});

// Finance
Route::prefix('finance')->middleware(['auth', 'role:finance'])->group(function () {
    Route::post('/contract-cases/{id}/forward', [Finance\ContractCaseController::class, 'forward'])
         ->name('finance.contract-cases.forward');
    Route::post('/contract-cases/{id}/return', [Finance\ContractCaseController::class, 'return'])
         ->name('finance.contract-cases.return');
});

// MD
Route::prefix('md')->middleware(['auth', 'role:md'])->group(function () {
    Route::post('/contract-cases/{id}/approve', [MD\ContractCaseController::class, 'approve'])
         ->name('md.contract-cases.approve');
    Route::post('/contract-cases/{id}/return', [MD\ContractCaseController::class, 'return'])
         ->name('md.contract-cases.return');
});
```

---

## SECTION F — MIGRATIONS CHECKLIST

**Before running any migration, check existing schema:**

```bash
php artisan db:show --database=pgsql
# OR in psql:
\d hr_ctrcases
\d hr_contracts
\d hr_contractplans
\d hr_employees
```

**Fields to add if missing** (create one migration file):

```php
Schema::table('hr_ctrcases', function (Blueprint $table) {
    // Add only if not exists:
    $table->string('ctc_type', 5)->nullable();           // Hg, Ce, Cr, Rh
    $table->string('ctc_empnamecomp', 200)->nullable();   // Candidate name
    $table->string('ctc_newjobtitle', 150)->nullable();   // Proposed designation
    $table->string('ctc_newgrade', 50)->nullable();
    $table->decimal('ctc_newsalary', 12, 2)->nullable();
    $table->integer('ctc_newprob')->nullable();           // Probation months
    $table->date('ctc_startdate')->nullable();
    $table->date('ctc_enddate')->nullable();
    $table->text('ctc_jd')->nullable();                   // Job description
    $table->string('ctc_cv_path', 500)->nullable();       // CV file path
    $table->string('ctc_cnic', 20)->nullable();
    $table->string('ctc_contact', 20)->nullable();
    $table->string('ctc_projectcode', 50)->nullable();    // Single project tab
    $table->string('ctc_status', 50)->default('Draft');
    $table->unsignedBigInteger('ctc_divisionid')->nullable();
    $table->unsignedBigInteger('ctc_createdby')->nullable();
    $table->timestamp('ctc_createdat')->nullable();
    $table->unsignedBigInteger('ctc_releasedby')->nullable();
    $table->timestamp('ctc_releasedat')->nullable();
    $table->decimal('ctc_approvedsalary', 12, 2)->nullable();
    $table->string('ctc_approvedgrade', 50)->nullable();
    $table->integer('ctc_approvedprob')->nullable();
});
```

**`hr_contractplans`** (monthly project allocation):

```php
// Confirm if table exists. If not, create:
Schema::create('hr_contractplans', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('ctc_id');       // FK → hr_ctrcases
    $table->string('plan_month', 7);            // Format: 2025-07
    $table->string('plan_projectcode', 50);
    $table->timestamps();
});
```

---

## SECTION G — JAVASCRIPT / AJAX BEHAVIOR

All AJAX calls use jQuery `.ajax()` with CSRF token in headers.

### G1. Live Duration Calculator

```javascript
function updateDuration() {
    const start = new Date($('#ctc_startdate').val());
    const end = new Date($('#ctc_enddate').val());
    if (start && end && end > start) {
        const diffMs = end - start;
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        const months = Math.floor(diffDays / 30);
        const days = diffDays % 30;
        $('#duration-display').text(months + ' months, ' + days + ' days');
    }
}
$('#ctc_startdate, #ctc_enddate').on('change', updateDuration);
```

### G2. Max End Date Enforcement

```javascript
$('#ctc_startdate').on('change', function () {
    const start = new Date(this.value);
    const maxEnd = new Date(start);
    maxEnd.setFullYear(maxEnd.getFullYear() + 1);
    const maxEndStr = maxEnd.toISOString().split('T')[0];
    $('#ctc_enddate').attr('max', maxEndStr);
    updateMonthlyProjectRows(); // Regenerate project rows
});
```

### G3. Monthly Project Rows (Tab B)

```javascript
function updateMonthlyProjectRows() {
    const start = new Date($('#ctc_startdate').val());
    const end = new Date($('#ctc_enddate').val());
    if (!start || !end) return;

    const tbody = $('#monthly-project-table tbody');
    tbody.empty();

    let current = new Date(start.getFullYear(), start.getMonth(), 1);
    const endMonth = new Date(end.getFullYear(), end.getMonth(), 1);

    while (current <= endMonth) {
        const label = current.toLocaleString('default', { month: 'long', year: 'numeric' });
        const key = current.getFullYear() + '-' + String(current.getMonth() + 1).padStart(2, '0');
        tbody.append(`
            <tr>
                <td>${label}</td>
                <td>
                    <select name="monthly_project[${key}]" class="form-control form-control-sm">
                        ${projectOptionsHtml} {{-- Rendered from Blade --}}
                    </select>
                </td>
            </tr>
        `);
        current.setMonth(current.getMonth() + 1);
    }
}
```

### G4. Save as Draft (AJAX)

```javascript
$('#btn-save-draft').on('click', function () {
    const formData = new FormData($('#contract-case-form')[0]);
    formData.append('ctc_status', 'Draft');
    $.ajax({
        url: '{{ route("division.contract-cases.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (res) {
            toastr.success('Case saved as Draft.');
            // If new case, update URL with case ID
        },
        error: function (err) {
            toastr.error('Error saving case.');
        }
    });
});
```

### G5. Release (with Comment Modal)

```javascript
$('#btn-release').on('click', function () {
    $('#release-modal').modal('show');
});

$('#btn-confirm-release').on('click', function () {
    const comment = $('#release-comment').val();
    const caseId = $('#case-id-field').val();
    $.ajax({
        url: `/division/contract-cases/${caseId}/release`,
        method: 'POST',
        data: { comment: comment, _token: $('meta[name="csrf-token"]').attr('content') },
        success: function () {
            toastr.success('Case released to HR.');
            window.location.href = '{{ route("division.contract-cases.index") }}';
        }
    });
});
```

---

## SECTION H — STYLING NOTES

- Use project's existing CSS variables (Bootstrap 4 + AdminLTE 3.x).
- Cards: use `card card-outline card-primary` / `card-info` etc. to match theme.
- Status badges: use Bootstrap `badge` classes.
- Form sections: use `card` wrappers with `card-header` titles for each section.
- CNIC field: use `inputmask` or manual JS mask: `XXXXX-XXXXXXX-X`.
- Salary field: use `number-format.js` or simple JS to show comma-separated live preview.
- Tab toggle (Single Project vs Monthly): use Bootstrap `nav-tabs` + `tab-content`.
- Toast notifications: use `toastr.js` (already available in AdminLTE).

---

## SECTION I — EXECUTION ORDER FOR CURSOR

1. **Read DB schema first.** Run describe/`\d+` on all 4 tables. Extract column names. Do not assume.
2. **Create migration** to add missing fields only.
3. **Create Models** with correct `$fillable` and relationships.
4. **Create Routes** in `web.php`.
5. **Create Controllers** (Division first, then HR, Finance, MD).
6. **Create Blade Views** — Index page first (cards + list), then create/form page.
7. **Add JS** to create.blade.php (duration, monthly rows, AJAX save, release modal).
8. **Test** release flow end-to-end from Division → HR → Finance → MD → Fulfill.

---

*End of Prompt — Version 1.0 | RDWIS Contract Case Module*
