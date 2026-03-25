# 📝 RDWIS — Work & Iteration Log

> **Persistent log of all development sessions.** Updated at end of every session.

---

## Session #1 — 2026-03-25

### 🎯 Session Focus
- Initial creation of **Master Database & Architecture Ledger** (`software_db.md`)
- Initial creation of **Work & Iteration Log** (`work_history.md`)
- Full codebase exploration and schema mapping

### 🗄️ Schema Changes
- **No migrations run this session** — discovery only
- Documented existing schema across 8 PostgreSQL schemas:
  - `cen` (8 tables), `prj` (6 tables), `doc` (3 tables), `pur` (4 tables)
  - `hr` (1 table), `frm` (1 table), `puritems` (4 tables), `purnew` (4 views)

### 💻 Code Progress
- Explored all **27 Eloquent models** with their relationships
- Mapped all **9 Controllers** and their responsibility areas:
  - `AuthController` — Login/Logout
  - `ProjectController` — Project CRUD, milestones, attachments, spendings
  - `DocMprController` — MPR document view/store from Division side
  - `MprController` — SORD MPR inbox, review, action, compile
  - `PurchaseController` — Purchase cases, quotes
  - `PurItemsController` — New item registry (purnew), RFQ
  - `DivHrController` — Employee list, attendance
  - `ReportsController` — Comparative/IT letter reports
- Mapped **Route Architecture**: Guest → Division (with SORD guard) → SORD (with Division guard)
- Documented role-based access: `isSORD()` vs `isDivision()` logic in User model
- Identified `purnew` schema as PostgreSQL views over `puritems` real tables

### 📌 Key Findings
1. Database: **PostgreSQL 18.1**, Database: `newdev`, Laravel **12**, PHP **8.2+**
2. Session/Cache/Queue all use **database** drivers
3. `cen.accounts` serves as the Laravel Auth model (custom `acc_pass` field)
4. MPR workflow: Division creates → Submits → SORD reviews → Approves/Returns
5. `purnew` schema is an alias layer (views) over `puritems` — no data duplication
6. Schemas `aud`, `fin`, `ina` exist in DB dumps but have **no Laravel models yet**

### 📋 Next Action Plan
- [ ] Review any pending feature requests from user
- [ ] Add models for `aud`, `fin`, `ina` schemas if needed
- [ ] Document any new tables/columns as features are developed
- [ ] Update `software_db.md` when migrations run or schema changes

---

## Session #2 — 2026-03-25 (UI Improvements & Quotes Backend)

### 🎯 Session Focus
- Implementing the user's sleek UI requirements for the RFQ Quotation System based on the `stock_view.php` reference design.
- Creating a seamless Accordion-style layout for group details to avoid page redirection.
- Adding Tracking History and Delete functionality for RFQ groups.

### 🗄️ Backend / API Changes
- **Delete Group**: Added `deleteGroup($id)` to `PurItemsController` and `DELETE /purnew/group/{id}` route to fully remove an RFQ group, its items, and quotes.
- **Group Details**: Added `groupDetailsJson($id)` to fetch row-level RFQ items, joining them with their *Accepted Quote Price* and *Vendor Name* if an assigned quote exists (`is_accepted = true`).

### 💻 Frontend Progress (`rfq_list_layout.blade.php`)
- **Accordion Toggle**: Replaced the title link with an accordion drop-down (`toggleDetails`) that uses the new JSON endpoint to render the item list right underneath the parent row.
- **Sleek Modal UI**: Heavily refined the CSS of the fullscreen Quotes Modal to match the user's reference exactly (white background, borderless fields, active green highlights for saved data, minimal top headers).
- **Tracking Modal**: Built a `trackingModal` to provide a timeline summary of the RFQ (e.g., "RFQ Generated" based on creation timestamp).
- **Data Rendering**: Integrated the `accepted_price` and `vendor_name` directly into the accordion view so users immediately see who won the bid for each item.
- **JS Optimization**: Swapped out the old messy DOM-parsing approach with a clean, parallel `Promise.all` fetch from dedicated JSON APIs (`/quotes/{id}/items` and `/quotes/{id}`).

### 📌 Key Findings
- The `is_accepted` flag on the `purnew.rfq_quotes` is crucial for showing who won the bid on the main accordion list.
- CSS adjustments are critical to User Experience (removing heavy borders, using `rgba` shadowing, shrinking `font-size`) directly align with the design preferences shown.

---

> **⚡ Operational Rules Applied:**
> - ✅ Read Before Write — `software_db.md` reviewed before any code changes
> - ✅ Auto-Update — Both files updated with every schema/feature discussion
> - ✅ Persistence — Updated markdown provided at end of session
