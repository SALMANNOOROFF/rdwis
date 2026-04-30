# RDWIS Enhanced Procurement System: Implementation Summary & Reference

This document serves as the master record of the "RDWIS ENHANCED" procurement system's architecture, workflow, and UI/UX standards. It reflects the completed implementation as of 2026-04-09.

## 1. Core Architecture & Technology Stack
- **Framework**: Laravel 12.44.0
- **Language**: PHP 8.2.12
- **Database**: PostgreSQL (Schemas: `pur` for purchases, `cen` for central accounts)
- **Styling**: Glassmorphism UI, CSS Grid (1fr 340px split-view), Rajdhani & DM Sans fonts.
- **Workflow Engine**: [PurchaseApprovalService.php](file:///e:/RDWIS%20ENHANCED/app/Services/PurchaseApprovalService.php) handles all state transitions, authority names, and return targets.

## 2. Unified High-Fidelity UI/UX Standards

### A. The "Narrow Split-View" Layout
All primary pages (Initiation and Case View) follow a standardized **Grid Layout**:
- **Main Content (1fr)**: Left side for forms, items, and charts.
- **Action Sidebar (340px)**: Right side for Decision Trail, Justification, and Action Box.
- **High Density**: Inputs are `32px` high, fonts are compact, and margins are tightened to maximize information on screen.

### B. Standardized Header (DG-Style)
All roles see a clean, label-less header with pipe (`|`) separators:
`#ID | Date | Project Code | Department Name | PKR AMOUNT`
- **Case ID**: Large, bold, and accented.
- **Amount**: Extra bold Rajdhani font in PKR format.
- **No Labels**: "ID:", "Project:", "Amount:" labels are removed to achieve a premium ERP look.

### C. Decision Trail & Action Box
- **Latest-First**: Most recent case movement is always at the top.
- **Hidden Comments**: Remarks are hidden by default and accessible via a clickable comment icon.
- **Compact Action Box**: Padding and icon sizes are reduced. Buttons like "NOT APPROVE" are optimized to stay on one line.
- **Themed Buttons**: "SAVE & FORWARD" and "DRAFT" buttons automatically adapt their colors and gradients to the procurement category theme (e.g., Material is Blue, Consultancy is Purple).

## 3. Intelligent Workflow Logic

### A. Forwarding (Hierarchy)
- **Flow**: Division -> DProc -> DFinance -> MD -> DDG -> DG.
- **MD Threshold**: Approves up to 2L.
- **DDG Threshold**: Approves up to 6L.
- **DG Threshold**: Final authority for > 6L.

### B. "Not Approved" vs. "Return"
- **Not Approved**: Replaces "Reject". Automatically returns the case **exactly one step back** in the hierarchy (e.g., DFinance to DProc). Remarks are mandatory.
- **Return**: Allows an authority to send a case back to **any previous stage** in that specific case's history or directly to the Division.
- **Hold / Revert**: Initiators (Division) can pull a case back from HQ into "Draft" mode for editing directly on the view page, without navigating to a separate edit screen.

### C. Initiation Form Features
- **Live Calculation**: Automatically calculates the PKR total as line items are added.
- **Category Themes**: 12+ categories (Material, Services, TADA, etc.) with specific icons and colors.
- **Smart Minute Number**: Automatically fetches the next available minute number via AJAX when a Project/Head is selected.
- **Hardware Shift**: If a Consultancy case includes hardware, it automatically prompts the user and adapts into a Services case.

## 4. Key Files & Components
- **[unified_form.blade.php](file:///e:/RDWIS%20ENHANCED/resources/views/purchase/new_case/unified_form.blade.php)**: The master initiation form with split-view and dynamic categories.
- **[show.blade.php](file:///e:/RDWIS%20ENHANCED/resources/views/purchase/initiation/show.blade.php)**: The high-fidelity unified view for all authorities.
- **[_action_box.blade.php](file:///e:/RDWIS%20ENHANCED/resources/views/approvals/_action_box.blade.php)**: The compact sidebar component for workflow decisions.
- **[_decision_trail.blade.php](file:///e:/RDWIS%20ENHANCED/resources/views/approvals/_decision_trail.blade.php)**: The scrollable history component with comment toggles.
- **[PurchaseApprovalService.php](file:///e:/RDWIS%20ENHANCED/app/Services/PurchaseApprovalService.php)**: The backend logic for the state machine.

## 5. User Preferences & Habits
- **Language**: Roman Urdu/Hindi mixed with English technical terms.
- **Priority**: High density of information, "straightened" process flow, and zero case "disappearance" during tracking.
