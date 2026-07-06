# Accounting Module ↔ Database Compatibility Audit Report
Date: July 5, 2026

---

## Step 1: Schema Existence Check

All tables referenced in the audit are present in the database. Below is the table/column confirmation:

| Table               | Columns Checked                                                                 | Status |
|---------------------|---------------------------------------------------------------------------------|--------|
| `fin.transfers`     | `trf_id`, `trf_date`, `trf_type`, `trf_title`, `trf_amount`, `trf_fromhed`, `trf_fromunt`, `trf_tohed`, `trf_tount`, `trf_status` | ✅ Present |
| `fin.sharesalloc`   | `sha_hed_id`, `sha_ficmt_id`, `sha_focmt_id`, `sha_transtype`, `sha_id`, `sha_cf`, `sha_pcc`, `sha_prj`, `sha_prj_sal`, `sha_prj_pur` | ✅ Present |
| `fin.sharesinstall` | `shi_hed_id`, `shi_fitrn_id`, `shi_fotrn_id`, `shi_id`, `shi_cf`, `shi_pcc`, `shi_msn_idd`, `shi_prj` | ✅ Present |
| `fin.transactions`  | `trn_id`, `trn_cmt_id`, `trn_date`, `trn_amount1`, `trn_balance`, `trn_seq`, `trn_tax1`, `trn_amount2`, `trn_transtype`, `trn_noloan` | ✅ Present |
| `fin.commitments`   | `cmt_id`, `cmt_docid`, `cmt_type`, `cmt_date`, `cmt_amount`, `cmt_status`, `cmt_effhed_id`, `cmt_effunt_id`, `cmt_hed_id`, `cmt_unt_id`, `cmt_sudohed`, `cmt_remarks` | ✅ Present |
| `fin.loanadjustments` | `lad_string`, `lad_amount`, `lad_remarks`, `lad_id`, `lad_dtg`, `lad_from`, `lad_to` | ✅ Present |
| `fin.msncosts`      | `mct_prj_id`, `mct_hed_id`, `mct_msn_id`, `mct_cost`, `mct_msn_idd` | ✅ Present |
| `fin.subheads`      | `sbh_hed_id`, `sbh_name`, `sbh_alloc`, `sbh_id` | ✅ Present |
| `fin.salorders`     | `sor_id`, `sor_hed_id`, `sor_releasedtg`, `sor_status`, `sor_remarks`, `sor_srq_id`, `sor_closedtg`, `sor_unt_id`, `sor_month`, `sor_netsalary`, `sor_salary`, `sor_emp_id`, `sor_effhed_id`, `sor_empnamecomp`, `sor_bnkacctitle`, `sor_effunt_id`, `sor_bnkaccdetail`, `sor_ctrsalary`, `sor_checked`, `sor_contracts`, `sor_noloan`, `sor_transtype`, `sor_sudohed`, `sor_remarks2`, `sor_type`, `sor_grosalary`, `sor_arrears`, `sor_dues`, `sor_overwork`, `sor_underwork`, `sor_loaned`, `sor_withheld`, `sor_award`, `sor_penalty`, `sor_paidalready`, `sor_parent` | ✅ Present |
| `fin.salorders_shd` | `sod_sor_id`, `sod_type`, `sod_subhead`, `sod_ratio` | ✅ Present |
| `fin.salary_tax`    | `slt_from`, `slt_to`, `slt_inttax`, `slt_midamount`, `slt_midtax` | ✅ Present |
| `fin.contractsverif`| `cvf_ctr_id`, `cvf_verif`, `cvf_dtg` | ✅ Present |

### Data Type Check
- All amount fields (`trf_amount`, `cmt_amount`, `trn_amount1`, etc.) are `numeric` or `integer` - correct precision for currency handling.

---

## Step 2: Query Level Mapping (19 Audit Items)

| # | Concept | Reference (VBA) | Current Laravel Implementation | Status | Notes |
|---|---------|-----------------|---------------------------------|--------|-------|
| 1 | Allocation | `SELECT trf_amount FROM fin_transfers WHERE trf_type='FI' AND trf_title='Project Funding' AND trf_tohed = [HeadId]` | `DB::table('fin.transfers')->where('trf_type', 'FI')->where('trf_title', 'Project Funding')->where('trf_tohed', $headId)->sum('trf_amount')` | ✅ MATCH | Note: VBA uses `SELECT` (single row), Laravel uses `SUM()` - need to verify if only one transfer exists per head. |
| 2 | MTSS Share | `SELECT trf_amount FROM fin_transfers WHERE trf_type='FO' AND trf_title='MTSS Share' AND trf_fromhed = [HeadId]` | `DB::table('fin.transfers')->where('trf_type', 'FO')->where('trf_title', 'MTSS Share')->where('trf_fromhed', $headId)->sum('trf_amount')` | ✅ MATCH | Again, VBA uses single row, Laravel uses SUM. |
| 3 | RDW / Acc Share | `Allocation - MtssShare` | `$status['rdw_share'] = $status['allocation'] - $status['mtss_share']` | ✅ MATCH | - |
| 4 | Share Split (Pcc/Cf/Prj) | `SELECT * FROM fin_sharesalloc WHERE sha_hed_id = [HeadId]` | `DB::table('fin.sharesalloc')->where('sha_hed_id', $headId)->first()` | ✅ MATCH | - |
| 5 | Received (Pcc/Cf) | `SELECT Sum(shi_pcc) AS SumPcc, Sum(shi_cf) AS SumCf FROM fin_sharesinstall WHERE shi_hed_id = [HeadId]` | Laravel has different logic: checks `shi_fitrn_id IS NOT NULL` then sums `shi_pcc + shi_prj`, fallback to transfers with `trf_status='Paid'` | ⚠️ PARTIAL | Different calculation approach! |
| 6 | Expenditure | Uses query `fin_sto_acc_exp` (joins transactions/commitments) | `DB::table('fin.transactions')->join('fin.commitments', 'trn_cmt_id', '=', 'cmt_id')->where('cmt_hed_id', $headId)->sum('trn_amount1')` | ✅ MATCH | Includes sign flip with `abs()` |
| 7 | Outstanding Commitments | Uses query `fin_sto_acc_commitsoutst` | `DB::table('fin.commitments')->join('pur.purcases', ...)->where('LOWER(pcs_status)', 'approved')->sum('cmt_amount')` | ✅ MATCH | - |
| 8 | In Process | Uses query `fin_sto_acc_ipc` | `DB::table('pur.purcases')->whereIn('LOWER(pcs_status)', ['finance', 'with finance', 'with dfinance', 'audit', 'command', 'approved_pending_commit'])->sum('pcs_price')` | ✅ MATCH | - |
| 9 | Balance | `Received - Expenditure` | `$status['balance'] = $status['received'] - $status['expenditure']` | ✅ MATCH | - |
| 10 | Available | `Received - Expenditure - Commitments - InProcess` | `$status['available'] = $status['balance'] - $status['commitments'] - $status['in_process']` | ✅ MATCH | - |
| 11 | CanBeSpent | `Share - Expenditure - Commitments - InProcess` | Not explicitly calculated as `CanBeSpent`, but similar logic used for subheads | ⚠️ PARTIAL | Missing top-level `CanBeSpent` field |
| 12 | Yet To Be Received | `Share - Received` | `$status['yet_to_be_received'] = $status['rdw_share'] - $status['csrf_share'] - $status['received']` | ⚠️ PARTIAL | Different formula (includes csrf_share) |
| 13 | Loans given/taken | Uses queries `fin_sto_pcc_loansgiven`, `fin_sto_others_loanstaken` | ❌ NOT FOUND | No loan calculations in current code |
| 14 | Receivables (mission-based) | Uses `fin_sto_acc_rcvmsncompleted`, `fin_sto_acc_rcvmsncurrent` (based on `fin.msncosts`) | Current code uses `prj.milestones` with `msn_status='Achieved'` instead of `fin.msncosts` | ❌ MISMATCH | Uses different table! |
| 15 | Project subheads | Uses `GetPrjAllocationsShd`, `GetPrjExpenditureShd`, etc. | `getSubheadBreakdown()` function calculates allocations, expenditure, commitments, in-process | ✅ MATCH | Includes both purchase and salary expenditure ratio-split |
| 16 | Salary orders | Uses `fin.salorders` and `fin.salorders_shd` with ratio splits | `getSubheadBreakdown()` includes salary expenditure ratio-split using `sod_ratio` | ✅ MATCH | - |
| 17 | Salary forecast | Uses `GetPrjSalForecast`, `GetUaSalForecast`, etc. | ❌ NOT FOUND | No salary forecast implementation |
| 18 | Salary tax | `SalaryTax()` function using `fin.salary_tax` | ❌ NOT FOUND | No salary tax calculation |
| 19 | Contract verification | Uses `fin.contractsverif.cvf_verif` | ❌ NOT FOUND | No contract verification checks |

---

## Step 3: Sign Convention Check

| Calculation | VBA Sign Handling | Laravel Sign Handling | Status |
|-------------|------------------|-----------------------|--------|
| Expenditure | `Nz(sumOfAmount * (-1), 0)` | `abs((float) $expenditure)` | ✅ MATCH |
| Outstanding Commitments | `Nz(sumOfAmount * (-1), 0)` | No sign flip (just sum of `cmt_amount`) | ⚠️ MISMATCH | Missing sign flip for commitments! |

---

## Step 4: NULL / Zero Handling

| Calculation | VBA Handling | Laravel Handling | Status |
|-------------|--------------|------------------|--------|
| Allocation | `If Not rstState.EOF Then GetAccAllocation = rstState!trf_amount` | Uses `sum()` which returns 0 if no rows | ✅ OK |
| Shares | `$shares->sha_cf ?? 0` | Uses null coalescing operator | ✅ OK |
| Expenditure | `Nz(..., 0)` | Uses `sum()` (handles NULLs) | ✅ OK |

---

## Step 5: Rounding

| Calculation | VBA Rounding | Laravel Rounding | Status |
|-------------|--------------|------------------|--------|
| Balance | `Round(..., 2)` | No rounding applied | ❌ MISSING |
| Available | `Round(..., 2)` | No rounding applied | ❌ MISSING |
| Yet To Be Received | `Round(..., 2)` | No rounding applied | ❌ MISSING |
| CanBeSpent | `Round(..., 2)` | No rounding applied | ❌ MISSING |

---

## Step 6: Status/Enum Drift

Current Laravel code checks for these statuses in `pur.purcases`:
- `approved` (for commitments)
- `finance`, `with finance`, `with dfinance`, `audit`, `command`, `approved_pending_commit` (for in-process)

No status check on `fin.commitments.cmt_status` - VBA likely uses `cmt_status` for filtering, but current code uses `pur.purcases.pcs_status`.

---

## Step 7: Head/Unit Scoping

- Commitments/expenditure use `cmt_hed_id` (current head), not `cmt_effhed_id` (effective head)
- Need to verify if this is correct per business requirements

---

## Concrete Bugs & Issues Found

### 1. Missing Sign Flip for Outstanding Commitments
**Location**: `app/Services/FinancialIntelligenceService.php:83`
**Current Code**:
```php
$status['commitments'] = (float) $commitments;
```
**Expected Code**:
```php
$status['commitments'] = abs((float) $commitments);
// OR (if stored as negative)
$status['commitments'] = (float) $commitments * -1;
```

### 2. Missing Rounding on Derived Calculations
**Location**: `app/Services/FinancialIntelligenceService.php:100-104`
**Issue**: Balance, Available, Yet To Be Received, Remaining are not rounded to 2 decimal places
**Fix**:
```php
$status['balance'] = round($status['received'] - $status['expenditure'], 2);
$status['available'] = round($status['balance'] - $status['commitments'] - $status['in_process'], 2);
$status['yet_to_be_received'] = round($status['rdw_share'] - $status['csrf_share'] - $status['received'], 2);
$status['remaining'] = round($status['rdw_share'] - $status['expenditure'] - $status['commitments'] - $status['in_process'] - $status['csrf_share'], 2);
```

### 3. Receivables Calculation Uses Wrong Table
**Location**: `app/Services/FinancialIntelligenceService.php:106-114`
**Issue**: Uses `prj.milestones` instead of `fin.msncosts` as per legacy code
**Fix**: Update to use `fin.msncosts` table.

### 4. Missing Loan Calculations
No implementation for loans given/taken or own expenses.

### 5. Missing Salary Forecast
No implementation for salary forecast calculations.

### 6. Missing Salary Tax Calculation
No implementation for salary tax calculation.

### 7. Missing Contract Verification
No checks for `fin.contractsverif.cvf_verif` before processing contracts/salaries.

---

## Summary Table

| Category | Status |
|----------|--------|
| Schema Completeness | ✅ All tables/columns present |
| Core Calculations (1-12) | ⚠️ Partial matches and mismatches |
| Advanced Features (13-19) | ❌ Mostly missing |
| Sign Convention | ⚠️ Missing sign flip for commitments |
| Rounding | ❌ Missing on derived fields |
| NULL Handling | ✅ Mostly okay |

---

## Recommendations

1. Fix the sign flip for outstanding commitments
2. Add rounding to 2 decimals on all derived financial fields
3. Verify/implement receivables using `fin.msncosts`
4. Implement missing features (loans, salary forecast, salary tax, contract verification)
5. Add explicit status checks on `fin.commitments.cmt_status` if required
6. Add unit tests to verify calculations match legacy VBA output
