# Contract Cases Fix & Synchronization Guide (Deployment on Other PC)

This document outlines the step-by-step instructions to deploy the Contract Cases fixes (Approved Salary discrepancy, Early Renewal date mismatch, and fulfillment remarks) to another PC or production server.

---

## 1. Overview of Fixes

| Issue | Root Cause | Solution Applied |
| :--- | :--- | :--- |
| **Salary Mismatch (#CC-344, #CC-242)** | Proposed salary was copied into `hr.contracts` during fulfillment instead of MD/DG approved salary. | Code updated to prioritize `ctc_approvedsalary` in fulfillment, price calculation, and views. |
| **Early Termination Error (#CC-209)** | Renewal started before prior contract ended; system blocked with missing remarks exception. | Service now auto-populates fallback remarks; Renewal form now includes Date Mismatch remarks field. |
| **Display Bug & Lost Remarks (#CC-207)** | View hardcoded `$case->ctc_newsalary`; fulfillment modal did not pass entered remarks. | View updated to show approved salary with proposed strike-through; fulfillment captures `#decisionRemarks`. |

---

## 2. Step 1: Code Files Deployment

Ensure the following 8 files are updated on the target PC (either via `git pull` or by copying them directly):

1. **`app/Services/ContractCaseFulfillmentService.php`**
   - Recalculates `ctc_price` based on `ctc_approvedsalary * 12`.
   - Auto-generates early renewal fallback remarks if missing.
   - Captures and stores user fulfillment remarks in `hr.ctrcaseremarks`.
2. **`app/Services/ContractCaseApprovalService.php`**
   - Saves approved salary directly when approving cases.
3. **`app/Services/ContractCasePricingService.php`**
   - Updates `calculatePrice()` to prioritize `ctc_approvedsalary` and approved dates.
4. **`app/Services/FinancialIntelligenceService.php`**
   - Fixed PHP 31-day month date calculation bug in `calculateArrDues` and `getAttMonthStartDate` which previously caused false arrears loop multiplication.
5. **`app/Http/Controllers/DG/ContractCaseController.php`**
   - Fixed undefined `$user` variable in approval workflow.
6. **`resources/views/md/contract-cases/show.blade.php`**
   - Displays approved salary (with proposed salary struck through).
   - Approval button uses normal confirmation (no salary popup for approving authorities).
   - HR fulfillment box provides the editable **Approved Monthly Salary (Rs.)** field.
   - Added Early Renewal / Date Mismatch field in Renewal card.
   - Passes decision remarks during fulfillment.
7. **`resources/views/hr/salary/requisitions/index.blade.php`**
   - Displays **Project Code** (`hed_code`, e.g. `MWDC`, `NDB`) instead of title `xxx`.
8. **`resources/views/hr/salary/orders/index.blade.php` & `show.blade.php`**
   - Displays **Project Code** instead of title across Salary Orders list and Order Summary.
9. **`resources/views/division/contract-cases/create.blade.php` & `app/Http/Controllers/Division/ContractCaseController.php`**
   - Candidate CNIC is enforced as mandatory for new hiring (`Hg`) cases.
10. **`app/Models/HrCtrCase.php` & `resources/views/md/contract-cases/show.blade.php` (Add Employee Modal & Fulfillment)**
   - **Hg Add Employee Modal**: Pre-fills CNIC automatically from case initiation / applicant record.
   - **Hg Department Lock**: Department dropdown removed; permanently locked to the approved case division/unit so employee cannot be assigned to wrong department.
   - **Cr (Renewal) & Ce (Extension) Fulfillment**: All contract terms (Monthly Salary, Start Date, End Date, Designation / Job Title, Grade / Rank, Contract Type, Remarks) are visible and editable prior to contract issuance, defaulting to approved terms.
   - **`ContractCaseFulfillmentService.php`**: Synchronizes all custom updated terms to `hr.ctrcases` and issues/extends `hr.contracts` with exact terms.

---

## 3. Step 2: Database SQL Updates

Open **pgAdmin**, **DBeaver**, or **psql** on the target PC, connect to the database, and execute the following SQL blocks:

### A. Fix Specific Reported Cases

```sql
BEGIN;

-- 1. Case #CC-344 (Muhammad Suffiyan): Approved salary 84,000/- (20% increment)
UPDATE hr.ctrcases 
SET ctc_approvedsalary = 84000, 
    ctc_price = 84000 * 12 
WHERE ctc_id = 344;

UPDATE hr.contracts 
SET ctr_salary = 84000 
WHERE ctr_ctc_id = 344;


-- 2. Case #CC-242 (Syed Awais Ahmed): Approved salary 174,000/- (20% increment)
UPDATE hr.ctrcases 
SET ctc_approvedsalary = 174000, 
    ctc_price = 174000 * 12 
WHERE ctc_id = 242;

UPDATE hr.contracts 
SET ctr_salary = 174000 
WHERE ctr_ctc_id = 242;


-- 3. Case #CC-209 (Muhammad Waseem): Early renewal date mismatch resolution
UPDATE hr.ctrcases 
SET ctc_terminremarks = 'Contract renewed early with effect from 01-May-2026 as approved by DG NRDI.' 
WHERE ctc_id = 209;


-- 4. Case #CC-207 (Syed M Hashir Iqbal): Correct total price & remarks
UPDATE hr.ctrcases 
SET ctc_approvedsalary = 125000, 
    ctc_price = 125000 * 12 
WHERE ctc_id = 207;

UPDATE hr.contracts 
SET ctr_salary = 125000 
WHERE ctr_ctc_id = 207;

UPDATE hr.ctrcaseremarks 
SET crr_remarks = 'Contract Case fulfilled successfully (New Contract #971 against approved salary of Rs. 125,000/- as approved by DG NRDI)' 
WHERE crr_ctc_id = 207 AND crr_status = 'Fulfilled';


-- 5. Fix Plan 5205 Missing Head ID & Recalculate Requisition #6131
UPDATE hr.contractplans 
SET cpn_hed_id = 200014 
WHERE cpn_id = 5205;

UPDATE hr.salreqs 
SET srq_ctrsalary = 84000, 
    srq_grosalary = 73161, 
    srq_arrears = 81667, 
    srq_netsalary = 154828, 
    srq_salary = 154828, 
    srq_remarks = 'Arrears - 81,667.' 
WHERE srq_id = 6131;

COMMIT;
```

---

### B. Global Synchronization (For All Other Cases)

To ensure that any other historical or active cases where approved salary was specified have their contracts and prices matching:

```sql
BEGIN;

-- Sync contract salaries with approved case salaries
UPDATE hr.contracts c
SET ctr_salary = cs.ctc_approvedsalary
FROM hr.ctrcases cs
WHERE c.ctr_ctc_id = cs.ctc_id
  AND cs.ctc_approvedsalary IS NOT NULL
  AND cs.ctc_approvedsalary > 0
  AND c.ctr_salary != cs.ctc_approvedsalary;

-- Recalculate annual case price for approved cases
UPDATE hr.ctrcases
SET ctc_price = ctc_approvedsalary * 12
WHERE ctc_approvedsalary IS NOT NULL
  AND ctc_approvedsalary > 0
  AND (ctc_price != ctc_approvedsalary * 12 OR ctc_price IS NULL);

COMMIT;
```

---

## 4. Step 3: Clear Laravel Cache

After transferring the files, open a terminal in the project directory (`RDWIS APP 2.0`) on the target PC and run:

```bash
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
```

---

## 5. Step 4: Verification

### A. Run Database Check Query

```sql
SELECT 
    cs.ctc_id AS "Case ID",
    cs.ctc_emp_id AS "Emp ID",
    cs.ctc_type AS "Type",
    cs.ctc_status AS "Status",
    cs.ctc_newsalary AS "Proposed Salary",
    cs.ctc_approvedsalary AS "Approved Salary",
    cs.ctc_price AS "Total Price",
    c.ctr_id AS "Contract ID",
    c.ctr_salary AS "Contract Salary"
FROM hr.ctrcases cs
LEFT JOIN hr.contracts c ON c.ctr_ctc_id = cs.ctc_id
WHERE cs.ctc_id IN (344, 242, 209, 207)
ORDER BY cs.ctc_id DESC;
```

Expected Values:
- **Case #344:** Approved Salary = `84000`, Total Price = `1008000`, Contract Salary = `84000`.
- **Case #242:** Approved Salary = `174000`, Total Price = `2088000`, Contract Salary = `174000`.
- **Case #209:** Status = `Approved`, Ready to click **"Fulfill & Issue Renewal"** without date mismatch error.
- **Case #207:** Approved Salary = `125000`, Total Price = `1500000`, Contract Salary = `125000`, Probation Salary = `125000` (1 Month).

### B. Database Fix for Case #207 (Probation Salary Alignment)

If Case #207 was already fulfilled before this update, run this query on the production database:
```sql
-- Align Case #207 Probation fields to Approved Salary (125,000)
UPDATE hr.ctrcases 
SET ctc_approvedsalary  = 125000,
    ctc_approvedprobsal = 125000,
    ctc_approvedprob    = 1,
    ctc_newprobsal      = 125000
WHERE ctc_id = 207;

-- Align Contract #971 (Emp 11-26-05-6351)
UPDATE hr.contracts 
SET ctr_salary  = 125000,
    ctr_probsal = 125000,
    ctr_prob    = 1
WHERE ctr_ctc_id = 207 OR ctr_id = 971;
```

### C. Verify in Web Browser

1. Navigate to `http://<ip>/hr/contract-cases/344` & `.../242` & `.../207`.
2. Confirm the **Case Financials** card displays the **Approved Salary** as the main amount (with the proposed amount displayed with a strikethrough if different).
3. For Case #207, verify **Probation Period** is `1 Month` and **Probation Salary** displays `Rs. 125,000`.
4. When fulfilling any pending **Hiring (HG)** or **Renewal (CR)** case in the MD portal:
   - Notice the editable **Probation Period (Months)** and **Probation Salary (Rs.)** inputs.
   - Editing the Monthly Salary automatically syncs the Probation Salary unless manually customized.
   - Submitting fulfillment records the exact probation terms to both `hr.ctrcases` and `hr.contracts`.
