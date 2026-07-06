# Accounting Fixes Summary

## Date: July 5, 2026

## Overview
This document summarizes all the changes made to align the Laravel accounting calculations exactly with the legacy VBA code.

---

## Priority 1 Fixes (Sign & Rounding)

### 1. Added Sign Flip to Outstanding Commitments
**File**: `app/Services/FinancialIntelligenceService.php:80`
- **Before**: `$status['commitments'] = (float) $commitments;`
- **After**: `$status['commitments'] = abs((float) $commitments);`
- **Reason**: Matches VBA logic which multiplies sum by -1 to flip the sign of stored negative values.

### 2. Added Rounding to All Derived Fields
**File**: `app/Services/FinancialIntelligenceService.php:98-102`
- Added `round(..., 2)` to:
  - `balance`
  - `available`
  - `yet_to_be_received`
  - `can_be_spent`
  - `remaining`
- **Reason**: Exact match to VBA's `Round(..., 2)`

### 3. Added Missing Top-Level `can_be_spent` Field
**File**: `app/Services/FinancialIntelligenceService.php:101`
- Formula: `acc_share - expenditure - commitments - in_process`, rounded to 2 decimals
- Also added `can_be_spent` to subhead breakdown
- **Reason**: Missing field in original implementation, required for exact VBA compatibility

### 4. Added Rounding to Subhead Fields
**File**: `app/Services/FinancialIntelligenceService.php:180-181`
- Added `round(..., 2)` to `remaining` and `can_be_spent` for each subhead
- **Reason**: Exact match to VBA rounding

---

## Priority 2 Fixes (Formula Correctness)

### 5. Fixed Received Calculation
**File**: `app/Services/FinancialIntelligenceService.php:54-63`
- **Before**: Complex logic with `shi_fitrn_id IS NOT NULL`, `shi_prj`, and fallback to transfers
- **After**: Exact VBA logic: `SUM(shi_pcc) + SUM(shi_cf)` from `fin.sharesinstall`, no conditions
- Also added explicit `pcc_received` and `cf_received` fields
- **Reason**: Simplified to match VBA exactly

### 6. Fixed Yet To Be Received Calculation
**File**: `app/Services/FinancialIntelligenceService.php:100`
- **Before**: `$status['yet_to_be_received'] = $status['rdw_share'] - $status['csrf_share'] - $status['received'];`
- **After**: `$status['yet_to_be_received'] = round($status['acc_share'] - $status['received'], 2);`
- **Reason**: Removed extra `csrf_share` term to match VBA exactly

### 7. Added `acc_share` Field
**File**: `app/Services/FinancialIntelligenceService.php:49`
- Added: `$status['acc_share'] = $status['rdw_share'];`
- **Reason**: VBA uses both terms interchangeably

---

## Priority 3 Fixes (Missing Features)

### 8. Implemented Loans Calculation
**File**: `app/Services/FinancialIntelligenceService.php:188-207`
- Added `getLoans()` method:
  - `pcc_loans_given`: SUM(lad_amount) * (-1) where lad_from = HeadId
  - `others_loans_taken`: SUM(lad_amount) * (-1) where lad_to = HeadId
- Integrated into `getHeadStatus()` results

### 9. Implemented Salary Tax Calculation
**File**: `app/Services/FinancialIntelligenceService.php:210-235`
- Added `calculateSalaryTax($ctrSalary, $baseSalary)` method
- Exact VBA formula:
  1. AnnualSalary = CtrSalary * 12
  2. Find slab in fin.salary_tax
  3. Tax = (slt_inttax + (AnnualSalary - slt_midamount) * slt_midtax / 100) / 12 * (BaseSalary / CtrSalary)

### 10. Implemented Contract Verification Check
**File**: `app/Services/FinancialIntelligenceService.php:237-248`
- Added `isContractVerified($contractId)` method
- Checks `fin.contractsverif` for cvf_verif = true
- Can be used as a gate before including contracts in calculations

---

## TODO Items (Pending Confirmation)

1. **Receivables**: Need to confirm mission status mapping for `fin.msncosts`
   - Currently set to 0 pending confirmation
2. **Salary Forecast**: Need access to VBA's `GetSalaryMatrix` and `CalculateArrDues` functions
   - Not implemented yet, pending additional VBA logic
3. **Contract Verification Integration**: Need to identify all places contracts feed calculations and add the verification gate
   - Method is available but not yet integrated into all relevant calculations

---

## Testing

### Test Script Created
- File: `test-accounting.php`
- Usage: Run `php test-accounting.php` to test specific HeadIds
- Shows:
  - Main head status (allocation, shares, received, expenditure, etc.)
  - Subhead breakdown
  - Loans data

---

## Files Modified

1. `app/Services/FinancialIntelligenceService.php`
2. `test-accounting.php` (new file)
3. `ACCOUNTING_FIXES_SUMMARY.md` (new file)
4. `ACCOUNTING_AUDIT_REPORT.md` (unchanged, original audit)
