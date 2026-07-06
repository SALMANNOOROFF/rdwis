
# Diagnostic Report: HeadId 200012 (DADSS)

---

## Known Correct Values (From MS Access)

| Metric               | Project | CSRF | Combined |
|----------------------|---------|------|----------|
| Allocation           |         |      | 7,540,000 |
| MTSS Share           |         |      | 2,040,000 |
| RDW Share            |         |      | 5,500,000 |
| Share                | 5,200,000 | 300,000 |      |
| Received             | 0 | 0 | 0 |
| Expenditure          | 2,378,654 | 300,000 | 2,678,654 |
| Commitments          | 450,000 | 0 | 450,000 |
| In Process           | 0 | 0 | 0 |
| Balance              | -2,378,654 | -300,000 | |
| Available            | -2,828,654 | -300,000 | -3,128,654 |
| Yet to be Received   | 5,200,000 | 300,000 | 5,500,000 |

---

## Current Laravel Output

```
Allocation: 7,540,000 → MATCH
MTSS Share: 2,040,000 → MATCH
Acc Share: 5,500,000 → MATCH
Pcc Share: 5,200,000 → MATCH
Cf Share: 300,000 → MATCH
Prj Share: 5,200,000 → MATCH
Received: 0 → MATCH
Expenditure: 2,378,654 → SCOPE ISSUE (MISSING CSRF 300k)
Commitments: 1,800,000 → MISMATCH (should be 450k)
In Process: 1,200,000 → MISMATCH (should be 0)
Balance: -2,378,654
Available: -5,378,654 → MISMATCH
Yet To Be Received: 5,500,000 → MATCH
Can Be Spent: 121,346
```

---

## Bug #1: Sign Inconsistency Between Head & Subhead Commitments

### Analysis

Let's look at the queries for head-level and subhead-level commitments:

#### Head-Level Commitments Query (FinancialIntelligenceService.php:77-81)
```php
$commitments = DB::table('fin.commitments')
    ->join('pur.purcases', 'fin.commitments.cmt_docid', '=', 'pur.purcases.pcs_id')
    ->where('fin.commitments.cmt_hed_id', $headId)
    ->where(DB::raw('LOWER(pur.purcases.pcs_status)'), 'approved')
    ->sum('fin.commitments.cmt_amount');

$status['commitments'] = abs((float)$commitments);
```

#### Subhead-Level Commitments Query (FinancialIntelligenceService.php:153-159)
```php
$purCom = DB::table('fin.commitments')
    ->join('pur.purcases_shd', 'fin.commitments.cmt_docid', '=', 'pur.purcases_shd.pcd_pcs_id')
    ->join('pur.purcases', 'fin.commitments.cmt_docid', '=', 'pur.purcases.pcs_id')
    ->where('fin.commitments.cmt_hed_id', $headId)
    ->where(DB::raw('LOWER(pur.purcases.pcs_status)'), 'approved')
    ->where('pur.purcases_shd.pcd_subhead', $sh['sbh_name'])
    ->sum(DB::raw('fin.commitments.cmt_amount * pur.purcases_shd.pcd_ratio'));
```

### Finding
1. Head-level commitments uses `abs()` to handle sign, but subhead-level does NOT
2. **VBA Note**: In Accounting.bas line 185, `GetAccOutstandingCommits` returns `Nz(rstState!sumOfAmount * (-1), 0)` - this means the database stores commitments as NEGATIVE values, and VBA flips them to positive!
3. Subhead-level Equipment commitments are showing as **-1,800,000** in the Laravel output!

---

## Bug #2: Commitments Sum is 1.8M Instead of 450k

### Root Cause Analysis
The head-level query joins `fin.commitments` with `pur.purcases`. Wait - does this create duplicate rows? Let's check! Let's think: a single commitment (cmt_id) is linked to a purchase case (pcs_id), but if that purchase case has multiple subheads (pur.purcases_shd), will the join with pur.purcases still be one-to-one? Wait - let's check what `cmt_docid` is! Is it possible that a single `fin.commitment` row is being counted multiple times? Or are there extra commitment rows being included?

---

## Bug #3: In Process is 1.2M Instead of 0

### Root Cause Analysis
The in-process query checks pur.purcases with status:
- 'finance'
- 'with finance'
- 'with dfinance'
- 'audit'
- 'command'
- 'approved_pending_commit'

There are probably purchase cases in HeadId 200012 with one of these statuses that sum up to 1.2M!

---

## Bug #4: Expenditure Scope Issue

### Analysis
The current head-level expenditure is 2,378,654, which exactly matches the Project scope expenditure (HR 286,774 + Equipment 2,091,880). This means we are missing the CSRF scope's 300k expenditure!

---

## Recommendations for Fixes

1. **Sign Fix**: Ensure both head-level and subhead-level commitments use the same sign handling (multiply by -1 if stored as negative)
2. **Commitment Sum Fix**: Investigate why the sum is 1.8M instead of 450k - is it duplicate rows, wrong status filter, or wrong rows?
3. **In Process Fix**: Check which purchase cases are being counted and why they shouldn't be
4. **Expenditure Scope**: Clarify if head-level fields should be combined, project-only, or have separate PCC/CF/PRJ fields
