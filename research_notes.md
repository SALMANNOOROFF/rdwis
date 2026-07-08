# Research & Findings: Financial Integration Audit

This document preserves the exact findings, SQL query structures, and formulas extracted from the legacy MS Access queries and files, as of **July 7, 2026**. Use this to resume the implementation after the system restart.

---

## 1. Database Views Configuration & Source SQLs

### A. In-Process Documents (`fin.docs_ipc`)
From `fin_docs_ipc1` and `fin_docs_ipc2`:
*   **Purchase Cases**: Includes `pur_purcases` where status is in `('Under Scrutiny', 'Under Revision', 'Under Approval')`.
*   **Salary Cases**: `hr_salreqs` joined to `fin_salorders` on `srq_id = sor_srq_id`, where `sor_status = 'Draft'` and `srq_status = 'In Process'`.
*   **PostgreSQL View Migration**: Confirmed fully built and matches in `database/migrations/2026_07_07_052159_create_financial_views.php`.

### B. Subhead Ratios (`fin.docs_shd`)
*   **Definition**: UNION of subhead ratios from `fin_salorders_shd` and `pur_purcases_shd`.
*   **PostgreSQL View Migration**: Confirmed built in the same migrations file.

---

## 2. Calculation Mapping & Sign Conversions

Based on VBA procedures inside `Accounting.txt` and corresponding query definitions:

| Metric | Source Fields / Filter Criteria | Sign Correction (VBA) | Verified Access SQL |
| :--- | :--- | :--- | :--- |
| **AccAllocation** | `fin.transfers` where type='FI', title='Project Funding', tohed=HeadId | None | Direct query |
| **MtssShare** | `fin.transfers` where type='FO', title='MTSS Share', fromhed=HeadId | None | Direct query |
| **PccReceived** | `fin.sharesinstall` where `shi_hed_id=HeadId` (`shi_pcc`) | None | `Sum(shi_pcc)` |
| **CfReceived** | `fin.sharesinstall` where `shi_hed_id=HeadId` (`shi_cf`) | None | `Sum(shi_cf)` |
| **AccReceived** | `cmt_effhed_id=HeadId` and `cmt_type IN ('FI','FO','TI')` | None | Direct sum (GST branched) |
| **AccExpenditure** | `cmt_effhed_id=HeadId` and `cmt_type IN ('Ps','Pt','Rb','Sa','TO')` | `* -1` | `Nz(sumOfAmount * -1, 0)` |
| **AccOutstandingCommits** | `cmt_effhed_id=HeadId`, `cmt_status='Awaited'`, `cmt_type IN ('Ps','Pt','Rb','Sa')` | `* -1` | `Nz(sumOfAmount * -1, 0)` on `cmt_amount - paid` |
| **PccLoansGiven** | `cmt_effhed_id=HeadId`, `trn_noloan='0'` (Postgres `false`), `(cmt_hed_id IS NULL OR cmt_hed_id <> HeadId)`, `cmt_type IN ('Ps','Rb','Sa','LO','TO')` | `* -1` | `Nz(sumOfAmount * -1, 0)` |
| **OthersLoansTaken** | `cmt_hed_id=HeadId`, `cmt_effhed_id <> HeadId`, `cmt_type IN ('Ps','Rb','Sa','LO','TO')` | `* -1` | **No `trn_noloan` filter** in MS Access query `fin_sto_others_loanstaken1` |
| **PccOwnExp** | `cmt_effhed_id=HeadId`, `cmt_hed_id=HeadId`, `cmt_type IN ('Ps','Pt','Rb','Sa','TO')` | `* -1` | **No `trn_noloan` filter** in MS Access query `fin_sto_pcc_ownexp1` |

---

## 3. Visibility Rules (Part B)

The parameters passed to the `/openprojectdetails/{headId}` view must control conditional visibility based on the following computed flags:

*   **Rule 1: `$showProjectActualSection`**
    ```php
    $showProjectActualSection = !($pccExpenditure == $pccOwnExp && $othersLoansTaken == 0);
    ```
    *   *If `false` (no loan activity)*: Hide the green **Actual Project Figures** column (and corresponding fields/buttons) as well as the 3-row loan breakdown section at the bottom.
    *   *If `true` (loan activity present)*: Show both.

*   **Rule 2: `$showPrjShareValue`**
    ```php
    $showPrjShareValue = ($prjShare != $pccShare);
    ```
    *   Hide the displayed value of `prj_share` specifically when `prj_share == pcc_share`.

---

## 4. Test Verification Command

Since the Laravel app connects to a legacy PostgreSQL database (`rdw`), tests do not run against the in-memory SQLite connection by default. Running tests with PostgreSQL environment variables is verified:

```powershell
$env:DB_CONNECTION="pgsql"
$env:DB_DATABASE="rdw"
vendor/bin/phpunit tests/Feature/FinancialIntelligenceServiceTest.php
```

All 3 existing tests with 162 assertions passed successfully under this environment.
