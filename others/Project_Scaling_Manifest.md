# RDWIS Responsive Scaling Manifest & Source of Truth

This document serves as the complete record of the **True Zoom-Out Scaling** implementation. It includes the mandatory rules for all future development, the list of modified files, and the conversation history.

---

## 🛠 The Golden Rule for Future Work

> [!IMPORTANT]
> **Scaling Principle**: All design must be done for **1920×1080**. Do NOT write media queries for specific smaller screens. 
> Scaling is handled globally by `public/css/zoom-scale.css` using the `zoom` property on the `body`.

### Technical Requirements for all UI Changes:
1. **No Layout Shifts**: Components must not reorder. They simply shrink.
2. **Sidebar Integrity**: The sidebar must always use `position: fixed` with `top: 0` and `bottom: 0` to ensure it stretches the full height of the viewport regardless of zoom.
3. **Footer Stability**: The footer must remain visible or sticky at the bottom of the content area.
4. **No Horizontal Scroll**: Use `.table-responsive` for all tables and `col-12 col-md-X` for all grids to prevent overflow.
5. **No JavaScript Auto-Collapse**: Do not use jQuery to hide the sidebar on smaller screens; let the CSS `zoom` handle the fit.

---

## 📁 Modified Files Summary

### Global Scaling Core
- **[NEW] [public/css/zoom-scale.css]** — The brain of the scaling system.
- **[MODIFY] [resources/views/welcome.blade.php]** — Integrated scaling & Firefox fallback.
- **[MODIFY] [resources/views/auth/login.blade.php]** — Added scaling to login page.
- **[MODIFY] [resources/views/purchase/welcome.blade.php]** — Added scaling to purchase layout.

### Surgical UI Fixes (Table Wraps & Grid Layouts)
- [MODIFY] `resources/views/SORD/project_details.blade.php`
- [MODIFY] `resources/views/projects/openmprs.blade.php`
- [MODIFY] `resources/views/purchase/projects/openmprs.blade.php`
- [MODIFY] `resources/views/purchase/new_case/viewpurchasecase.blade.php`
- [MODIFY] `resources/views/projects/openprojectdetails.blade.php`
- [MODIFY] `resources/views/projects/viewmpr.blade.php`
- [MODIFY] `resources/views/approvals/show_high_density.blade.php`
- [MODIFY] `resources/views/approvals/_action_box.blade.php`

---

## 📜 Conversation History & Timeline

1. **Phase 2 Start**: Objective set to make RDWIS responsive for 1366×768 without changing AdminLTE design.
2. **First Approach**: Created `responsive-scale.css` with manual media queries for fonts and padding.
3. **Zoom Pivot**: Realized "True Zoom" (`zoom: 0.85`, etc.) is better as it mimics browser zoom. Created `zoom-scale.css`.
4. **Layout Battles**: 
   - Fixed Login Page (was using separate layout).
   - Fixed Sidebar/Footer height issues (they were cutting off after zoom).
   - Finalized `position: fixed` sidebar fix to bridge the gap.
5. **Final Cleanup**: Removed scrollbars from sidebar and confirmed all 3 layout files have the CSS and Firefox detection scripts.

---

## ✅ Deployment Checklist for User
- [ ] Hard Refresh (`Ctrl + Shift + R`) in browser.
- [ ] Verify Login Page scaling at 1366px.
- [ ] Verify Sidebar reaches bottom of screen.
- [ ] Verify Footer is visible.
- [ ] Verify no horizontal scrollbars on main tables.
