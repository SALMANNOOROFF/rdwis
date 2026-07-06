# Accounting Calculations Diagnosis Report

Date: July 5, 2026  
Head ID: 200012 (DADSS)

---

## Summary of Findings

We've identified several critical mismatches between the Laravel `FinancialIntelligenceService` calculations and the legacy VBA `Accounting.bas` module for Head ID 200012 (DADSS).

### High-Level Comparison

| Metric | Legacy (VBA) | Laravel (Current) | Status |
|--------|--------------|-------------------|--------|
| Allocation | 7,540,000.00 | 7,540,000.00 | ✅ Match |
| MTSS Share | 2,040,000.00 | 2,040,000.00 | ✅ Match |
| RDW Share | 5,500,000.00 | 5,500,000.00 | ✅ Match |
| Project Share | 5,200,000.00 | 5,200,000.00 | ✅ Match |
| CSRF Share | 300,000.00 | 300,000.00 | ✅ Match |
| Received | 0.00 | 0.00 | ✅ Match |
| Expenditure (Project) | 2,378,654.00 | 2,378,654.00 | ✅ Match |
| Expenditure (Total) | 2,678,654.00 | 2,378,654.00 | ❌ Missing CSRF portion |
| Commitments | 450,000.00 | 1,800,000.00 | ❌ 1.35M too high |
| In Process | 0.00 | 1,200,000.00 | ❌ 1.2M incorrectly included |

---

## Detailed Diagnosis

### Bug 1: Sign Inconsistency for Commitments (Head-level vs Subhead-level)

#### Root Cause:
- **Head-level calculation**: Uses `abs((float) $commitments)` on the sum of `fin.commitments.cmt_amount`
- **Subhead-level calculation**: Uses raw `cmt_amount * pcd_ratio` (likely stored as negative) without sign flipping

#### Impact:
- Subhead-level `CanBeSpent` calculation is inflated because negative commitments are effectively being added instead of subtracted
- Equipment subhead's CanBeSpent is incorrectly high (4,208,120 instead of expected lower value)

---

### Bug 2: Commitments Sum Too High (1.8M instead of 450k)

#### Root Cause Analysis:
The current query for head-level commitments is:
```php
$commitments = DB::table('fin.commitments')
    ->join('pur.purcases', 'fin.commitments.cmt_docid', '=', 'pur.purcases.pcs_id')
    ->where('fin.commitments.cmt_hed_id', $headId)
    ->where(DB::raw('LOWER(pur.purcases.pcs_status)'), 'approved')
    ->sum('fin.commitments.cmt_amount');
```

Potential issues:
1. **Join causing duplication**: If one `fin.commitment` links to multiple `pur.purcases_shd` rows, it could be counted multiple times
2. **Incorrect status filter**: The VBA likely uses `fin.commitments.cmt_status` instead of (or in addition to) `pur.purcases.pcs_status`
3. **Incorrect scope**: Should we be filtering for project-specific commitments vs combined?

---

### Bug 3: In Process = 1.2M when it should be 0

#### Root Cause Analysis:
Current query:
```php
$inProcess = DB::table('pur.purcases')
    ->where('pcs_hed_id', $headId)
    ->where(function($query) {
        $query->where(DB::raw('LOWER(pcs_status)'), 'finance')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'with finance')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'with dfinance')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'audit')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'command')
              ->orWhere(DB::raw('LOWER(pcs_status)'), 'approved_pending_commit');
    })
    ->sum('pcs_price');
```

This is including purchase cases that should not be counted as "In Process". The VBA's `GetAccInProcess` uses a different logic that needs to be verified.

---

### Bug 4: Expenditure Scope Question

From the MS Access report, we see:
- Project Expenditure: 2,378,654.00
- CSRF Expenditure: 300,000.00

But Laravel's `expenditure` is only showing 2,378,654.00. Is this intended to be project-only or combined?

---

## Next Steps (Diagnostic Actions)

1. **Run raw queries** against the database for HeadId 200012 to see:
   - All rows in `fin.commitments` for this head
   - All rows in `pur.purcases` for this head
   - How they join together

2. **Verify status values**:
   - What distinct values exist in `fin.commitments.cmt_status`?
   - What distinct values exist in `pur.purcases.pcs_status`?
   - How do these map to VBA's "outstanding" vs "in process"?

3. **Check VBA QueryDefs**:
   - Find the actual SQL for "fin_sto_acc_commitsoutst" and "fin_sto_acc_ipc" queries

---

## Quick Fixes to Implement

Once we have the raw query results, we can:
1. Fix the commitment sign handling consistently
2. Fix the commitment sum calculation
3. Fix the in-process calculation
4. Clarify and implement the correct scope for all metrics
