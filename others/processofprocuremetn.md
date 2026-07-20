# Procurement Scrutiny Lifecycle: Systems & Workflow

This document provides a comprehensive overview of the specialized procurement workflow implemented in RDWIS. It details the journey of a **Purchase Case** from its creation in a Division to its final approval by the Director General.

---

## 1. Unified Status Flow (The Serial Chain)

Every case moves through a strict hierarchy. A case is only visible to the user currently responsible for it in their "Pending Action" queue.

| Current Stage | Status Label | Action Needed By | Next Stage |
| :--- | :--- | :--- | :--- |
| **Initiation** | `Draft` or `Returned` | Division (Initiator) | → Director Procurement |
| **HQ Scrutiny** | `Under Scrutiny` | Director Procurement | → Director Finance |
| **Financial Review** | `With DFinance` | Director Finance | → MD Office |
| **MD Approval** | `With MD` | MD Office | → DDG Office (if > 2L) |
| **DDG Approval** | `With DDG` | DDG Office | → DG Office (if > 6L) |
| **Final Approval** | `With DG` | Director General | → **Approved** |

---

## 2. Core Operational Actions

### A. Forwarding (Pushing Ahead)
- **Initiator**: Creates the case, adds items/quotes, and "Releases to HQ".
- **Authorities**: Review the details and "Forward to Next Authority".

### B. Not Approved (The Smart Step-Back)
- **Status Change**: Instead of "Rejecting" (terminating) a case, authorities now use **NOT APPROVED**.
- **Action**: The case is automatically returned exactly **one step back** in the chain (e.g., from DG to DDG).
- **Mandatory Remarks**: The system enforces notes to explain why it wasn't approved.

### C. Return (Manual Target)
- Used when a case needs to go back multiple steps or specifically to the **Initiator** for major corrections.

---

## 3. Key Technical Architecture

### Core Controllers
- `PurchaseController.php`: Handles creation, selection, and storage.
- `PurchaseInitiationController.php`: Manages the Division-level "Hub" and tracking.
- `PurchaseApprovalController.php`: Standard HQ Dashboard engine for all roles.

### Shared Logic & UI
- `PurchaseApprovalService.php`: Central service for transitions and hierarchy logic.
- `_action_box.blade.php`: Shared Authority Panel with intelligent button switching.
- `show.blade.php`: Unified high-fidelity case detail view with live charts.

### Data Security (Horizon)
- `HorizonScoped.php`: Ensures Divisions only see their own unit's data, while HQ has global oversight.

---

## 4. Visibility & Tracking

### For Divisions (Initiators)
- **Action Required**: Shows cases that are Drafts or have been Returned for correction.
- **HQ Scrutiny**: A new dedicated counter in the hub that shows cases currently actively moving through HQ.
- **Tracking Tab**: Live monitoring of exactly where the case is in the HQ pipeline.

### For HQ Authorities
- **Pending Action**: Cases waiting for their specific review.
- **Action Taken**: A log of cases they have recently processed.

---
**DEVELOPER NOTE**: All UI components use the high-density Rajdhani/Inter font stack and dark-mode glassmorphism styling for a premium ERP feel.
