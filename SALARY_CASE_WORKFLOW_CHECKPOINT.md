# RDWIS 2.0 - Comprehensive Work Checkpoint & Salary Case Blueprint
**Saved On:** 27 October 2026  
**File Location:** `SALARY_CASE_WORKFLOW_CHECKPOINT.md`

---

## Part 1: Tasks Completed Today (Fully Verified & Tested)

### 1. Attendance Dynamic Cutoff Locking (26th to 25th Cycle)
- **Problem:** Days 26 to 31 were prematurely locking before the next month's salary was paid.
- **Solution:** 
  - Dynamic payroll cutoff cell locking in `AttendanceService.php` and `EmployeeCreationService.php`.
  - Migration executed: `2026_10_27_000001_fix_premature_att_locked2_for_unfulfilled_next_salary.php`.
  - All 21 attendance tests passing (110 assertions).

### 2. Employee Transfer to Procurement
- **Employee:** `19-25-12-6183` (Mudassir Muttaqi PN).
- **Migration:** `2026_10_27_000002_transfer_emp_19_25_12_6183_to_procurement.php`.
- **Transferred:** HR (`820000`) ➔ Inventory & Procurement Department (`810000`) across `hr.emps`, `hr.contracts`, and `hr.attendance`.

### 3. Central CSRF Heads & Salary Requisition Authorization
- Central employees (`unt_id >= 800000`) now view active CSRF heads suffixed with `(CSRF)` (e.g. `25GUN (CSRF)`).
- Multiple salary heads per month displayed in badges.
- `403 Not Authorized` resolved by granting `SALARY_VIEW` and `SALARY_GENERATE` to HR roles and relaxing division bounds for central staff.

### 4. DLOG Visibility in Fund Shifting
- In `PurchaseApprovalService.php` and `FundShiftingService.php`, DLOG (`106` Khalid Mahmood) can now view and forward cases to DDG (`108` M MAQBOOL) and DG (`107` M Mazhar Malik).

### 5. Fund Shift Decision String Truncation Fix
- Migration executed: `2026_10_27_000003_widen_fund_shift_decisions_columns.php`.
- Widened `fin.fund_shift_decisions.fsd_action` from `varchar(50)` to `varchar(255)`.
- Widened role, stage, rank, and holder fields to prevent truncation errors.

### 6. TA/DA Purchase Case Rebranding & Redesign
- Rebranded to `TA/DA / Bonus / Award / Honorarium`.
- Added Unit selector: `Days` vs `Quantity (Qty)`.
- Expanded Types and Sub-Types.
- In `nrdi/purchase_cases_new/show.blade.php`: Yellow payment channel banner removed, Payment Mode removed, Total Amount (PKR) renamed, Type & Sub-type columns added.

### 7. Storage Backup Python Scripts
- `storage_backup.py`: Fast multi-threaded Robocopy backup of `storage` folder to local drive (`D:\storagebackup2.0`).
- `stopragebackup_share.py` & `storagebackup_share.py`: Multi-destination & network share backup (`\\10.120.29.99\rdwiws2.0\`).
  - Isolated try/catch per target (if one destination fails, others continue).
  - Automatically creates subfolder `storagerdwis2.0` if not present.
  - Source path clearly configured at the top: `SOURCE_STORAGE_DIR = r"H:\RDWIS 2.0\RDWIS APP 2.0\storage"`.

---

## Part 2: Salary Order Verification Case Workflow (Minute Sheet)

### Target Concept & User Reference Image:
Based on official Navy/RDWIS **Minute 41 (Sep 26)** reference document:

```
                                  Minute 41

1. 72 employees are due for salary. Salaries of 65 employees are being paid for 
the month of Sep 26 against projects mentioned below. 3 salaries are split into 8 
payments. Salary sheet is placed at Encl 40-A/F.

Paid From            Payments       Amount        Remaining     Remarks
-----------------------------------------------------------------------
Center
SSMLP-4-CSRF            1           47,837            0
STLT-CSRF               4          379,012      465,742
BSE-F-CSRF              1           88,950            0
SSMLP-3-CSRF            1           58,701            0
Comm
MWDC                    8        1,309,204   12,743,338
IGS                     2          210,000       -8,740
SSMLP-4                 1          140,000    1,482,000
Enab
VRLY80M                 2           65,000   18,700,965
VR-HAR                  2          115,000    5,198,058
VRLY80OPS               9        1,243,200    3,347,475
NWS
ROV-PH2                 1          139,272      696,388
ROVPWR                  3          302,450    2,666,625
PAP104                  3          350,750    2,157,258
Sensors
GSE                     5          438,143            0
DECOY-I                 9          868,825    1,823,752
LU103                   1           29,032            0
OMS-II                 11          998,500    1,977,726
SoSE
CHAMELEON               2          100,000    3,115,484
Sys
MESMA                   4          517,000   13,600,756
-----------------------------------------------------------------------
Total                  70        7,400,876

2. Salaries of 05 x employees of Comm Div and 01x employee of Sensors Div have 
been held due to contract expiry.

3. Foregoing in view, approval of Rs. 7,400,876 (Rupees Seven Million Four 
Hundred Thousand Eight Hundred Seventy-Six Only) for salaries of 65 employees 
for month of Sep 26 may please be accorded for release of the payment through M/s MTSS.
```

---

### UI & Architecture Specifications

#### 1. Left Side: Official Minute Sheet
- **Header:** `Minute <Number>` (e.g. `Minute 41` — editable in Draft).
- **Paragraph 1:** Auto-calculated summary from `fin.salorders`:
  - Total due employees
  - Total paid employees
  - Month & Year string
  - Split salaries & split payments count
  - Enclosure reference (editable in Draft)
- **Grouped Table:**
  - Grouped by Division: `Center`, `Comm`, `Enab`, `NWS`, `Sensors`, `SoSE`, `Sys` (mapped from `cen.units`).
  - Columns: `Paid From`, `Payments`, `Amount`, `Remaining`, `Remarks`.
  - Total Row: Total payments and total PKR.
- **Paragraph 2 (Editable in Draft):**
  - Held salaries explanation (e.g. contract expiry, pending verification).
  - Finance user can freely edit this text in Draft stage.
- **Paragraph 3:**
  - Formal approval recommendation with auto amount-in-words (`Rupees Seven Million Four Hundred Thousand Eight Hundred Seventy-Six Only`) and payment channel (`M/s MTSS`).

#### 2. Right Side: Case Action & Decisions Panel (Matching Purchase & Fund Shifting Cases)
- **Send Case Button:**
  - `[SEND CASE]` button (prominent primary action).
  - Searchable destination dropdown: `MD`, `DDG`, `DG`, `DLOG`, `Finance`.
  - Remarks / Minutes textarea.
- **Decision Buttons:**
  - `[APPROVE CASE]` (for Finance & Command).
  - `[CANCEL / REJECT CASE]`.
- **Minutes Timeline:**
  - Official cards displaying previous minutes, officer photo, rank, designation, action, timestamp, and notes.

#### 3. Initiation & Permissions
- Button `[+ Initiate Salary Case]` in Finance sidebar / orders page.
- Finance enters Month (e.g. `2026-09` or `2026-10`).
- System gathers all salary orders for that month, generates draft minute, and opens Case View.

---

### Database State (Already Migrated & Ready)

1. **Migration Executed:**
   `database/migrations/2026_10_27_000004_create_fin_sal_order_cases_tables.php`
2. **Tables Created:**
   - `fin.sal_order_cases` (all case fields, paragraphs, totals, stages, holders).
   - `fin.sal_order_case_decisions` (audit trail, actions, minutes, timestamps).
   - `fin.salorders.sor_soc_id` (foreign key linking salary orders to cases).
3. **Models Created:**
   - `app/Models/SalOrderCase.php`
   - `app/Models/SalOrderCaseDecision.php`

---

## Part 3: Immediate Next Steps for Tomorrow

When resuming tomorrow, execute in this order:
1. **Create `SalaryCaseService.php`**:
   - `initiateCase($month, $user)`
   - `getMinuteTableData($case)` (aggregates and groups by unit & project head)
   - `updateDraft($case, $data)`
   - `sendCase($case, $user, $destination, $remarks)`
   - `approveCase($case, $user, $remarks)`
   - `cancelCase($case, $user, $remarks)`
2. **Create `SalaryCaseController.php`**:
   - `index` (Tabs: `Pending Action`, `Open`, `Closed`)
   - `show` (Dual pane: Left Minute Sheet + Right Send Case panel)
   - `store` (Initiate case)
   - `updateDraft` (Save edits to Para 2, Minute No, Encl)
   - `sendCase`, `approve`, `cancel`
3. **Create Blade Views**:
   - `resources/views/fin/salary_cases/index.blade.php`
   - `resources/views/fin/salary_cases/show.blade.php`
4. **Register Routes & Sidebar Menu**:
   - Add routes in `routes/web.php` under `salary_cases.*`.
   - Add `Salary Cases` menu item with pending action blinking red badge in `resources/views/welcome.blade.php`.

---
*Ready to resume directly from Part 3 tomorrow.*
