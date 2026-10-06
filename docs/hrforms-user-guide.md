# RDWIS 2.0 - HR Policy 2026 Forms User Guide & Operations Manual

## 1. Executive Summary & Architecture Overview

The HR Policy Forms module in RDWIS 2.0 implements standard hiring, renewal, and recruitment workflows mandated under **RDW/HR POLICY/2026**.

Key architectural guarantees:
- **Embedded UX (Zero Clutter)**: HR Policy forms live directly inside the existing **Case Attachments** dropdown (`#caseAttachmentsDropdown`). There are no confusing duplicate tabs or split interfaces.
- **Fail-Safe Operation**: Governed by the `HRFORMS_ENABLED=true` environment variable. When disabled (`false`), the system executes zero queries against the `hrforms` schema, ensuring existing contract case workflows continue uninterrupted.
- **Strict Invariant Guard**: All legacy tables (`hr.*`, `prj.*`, `fin.*`, `cen.*`) remain strictly read-only for HR Forms operations. The database schema fingerprint (`2a451470741fecb5f805c0a98d9d42e0`) is enforced and validated by automated tests.
- **Two-Layer Architecture**:
  - **Live Layer**: Auto-extracted directly from live database tables (`hr.employees`, `fin.empeffheads`, `prj.projects`, `hr.jobtypes`). Never edited manually.
  - **Manual Layer**: User-supplied scores, remarks, venue, and interview dates. Preserved across all live refreshes.
- **True Binary PDF Generation**: Powered by `barryvdh/laravel-dompdf` (DomPDF), generating valid `%PDF-1.4` binary streams. Drafts receive a translucent diagonal watermark; finalized forms are cryptographically immutable.

---

## 2. Feature Activation & Configuration

### Enabling the Feature Flag
Add or verify the following setting in `.env`:
```ini
HRFORMS_ENABLED=true
```

To roll back or disable the feature in an emergency:
```ini
HRFORMS_ENABLED=false
```
When `false`, all HR forms routes return 404/403, and views render the standard legacy attachments list with zero database overhead.

---

## 3. User Experience & Daily Workflow

### Accessing Policy Forms
1. Navigate to any Contract Case (e.g. Division View `/contract-cases/{id}` or Managing Director View `/md/contract-cases/{id}`).
2. Click the **Attachments** dropdown button in the case card header.
3. The dropdown displays:
   - **Upload New File**: Existing attachment upload modal.
   - **Case Files (Attachments)**: Existing user-uploaded files.
   - **HR Policy 2026 Forms**: The unified forms panel, showing form code, title, status pill, and action buttons.
   - **Case Dossier (Merged PDF)**: Single-click download of all submitted forms for Selection Board review.

### Form Status Lifecycle
- **Draft**: Form has been synchronized and pre-populated with live data from the project and employee.
- **Pending Input**: User has entered preliminary manual inputs, but required evaluation criteria or fields remain incomplete.
- **Ready**: All mandatory marks, remarks, and dates are entered. The form can now be reviewed and submitted.
- **Submitted**: The form is officially finalized. Its payload is locked into an immutable JSON snapshot (`form_snapshot`), and a final un-watermarked PDF is created in private storage (`storage/app/private/hrforms/`).
- **Scheduled**: Post-approval joining forms (e.g., Annex D joining report, Annex U undertaking) that activate only upon Selection Board approval and candidate selection.

### Form View & Edit Modal
Clicking **View / Edit** on any form opens the unified modal:
- **Left Panel (Live Extracted Data)**: Displays project budget utilization, employee grade, prior contracts, and routing information.
- **Right Panel (Manual Input Layer)**:
  - Evaluation scores (0-10 for Annex M criteria, 1-5 for Annex J selection board).
  - Auto-calculating score totals, percentages, and performance rating badges.
  - Justifications and remarks.
- **Save Draft**: Saves manual inputs without submitting. Auto-generates a draft PDF in the background.
- **Submit & Lock Form**: Locks the form permanently into the audit trail.

---

## 4. Policy Rules & Informational Guidance

### Informational Salary Bands & Increments (Never Blocking)
Per RDW/HR POLICY/2026 Addendum:
- Salary bands are **strictly informational reference guidelines**.
- If a proposed salary falls outside the reference range (e.g., for Senior Research Officer, reference band: Rs. 125,000 - 225,000), the system displays a neutral reference note:
  > *Reference band: Rs. 125,000 - Rs. 225,000 (PKR/month)*
- **Salary band notes NEVER block** case saving, form submission, or PDF generation.

### Performance Appraisal (Annex M - RDW/HR/F-08)
- Six evaluation criteria (10 marks each, 60 marks total):
  1. Technical Expertise & Skills
  2. Timely Completion & Quality of Work
  3. Reliability & Dependability
  4. Response under Pressure
  5. Team Work & Collaboration
  6. Code of Conduct (Integrity, Discipline, Attendance, Attire, Punctuality)
- Scoring Scale:
  - `< 30`: Needs Improvement (0% increment allowed)
  - `31 - 40`: Average (0% increment allowed)
  - `41 - 50`: Above Average (0% increment allowed)
  - `51 - 55`: Very Good (Up to 10% increment allowed; up to 20% with exceptional performance citation)
  - `56 - 60`: Outstanding (Up to 10% increment allowed; up to 20% with exceptional performance citation)

### Process Rules & Warnings
- **Ex-Post Facto Approvals**: If an extension or renewal case is initiated after the prior contract expired, the system displays an informational justification reminder.
- **Advertisement 14-Day Rule**: Fresh hiring requires an advertisement period of at least 14 days unless an advertisement exemption is logged via the UI modal.
- **Candidate Shortlist**: Selection Board (Annex B) requires 3 shortlisted candidates, unless Single Candidate Mode is selected with recorded justification (e.g. specialized PhD role, rehiring, or internee).

---

## 5. Administrative Configuration Screen

System Administrators and HR Managers can customize salary bands, approval tiers, and hiring type mappings directly through the web UI:
- **URL**: `/hrforms/settings`
- **Access Role**: `area: hr, nrdi, rdw`

### Configurable Elements:
1. **Salary Bands (`hrforms.salary_bands`)**:
   - Update minimum and maximum salary figures in PKR/month.
   - Set approval authority levels (MD RDW vs Board of Directors).
2. **Approval Chains (`hrforms.approval_chains`)**:
   - Manage approval hierarchies for individual annexes and grades.
3. **Hiring Type Mappings (`hrforms.hiring_type_map`)**:
   - Configure case type mappings (`Cr` -> Contract Renewal, `Hg` -> Fresh Hiring, etc.).

---

## 6. Storage & Security

- **Private Storage**: All generated PDFs reside in `storage/app/private/hrforms/cases/{caseId}/`.
- **Authorization**: Access to view, download, or edit forms is restricted by Laravel Policy Gates (`HrCtrCasePolicy`), ensuring users can only access cases within their permitted organizational scope.
- **Audit Trail**: Every create, edit, live refresh, submission, and PDF download action is recorded in `hrforms.form_audit_logs`.
