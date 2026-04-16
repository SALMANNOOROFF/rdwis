# ANTIGRAVITY MASTER PROMPT
## Full Responsive Overhaul — Laravel + AdminLTE 3.x
### Target: Claude Opus 4.6 | Use in: Antigravity Composer

---

## CONTEXT & OBJECTIVE

You are working on a **Laravel + Blade + AdminLTE 3.x + Bootstrap 4 + jQuery + PostgreSQL** web application.

The system has a **fixed left AdminLTE sidebar**, and pages contain a **mix of datatables, cards, forms, and text content**.

**Goal:** Make EVERY page in this application fully responsive and pixel-perfect across these exact screen resolutions:
- `1080 × 1024` (square-ish monitors)
- `1366 × 768` (most common office laptops)
- `1920 × 1080` (developer/widescreen — this is the base design)

Currently the layout breaks or looks awkward on 1080×1024 and 1366×768. Nothing should look broken, overflow, or get cramped on any of these resolutions.

---

## STEP 1 — DISCOVERY (Run First, Do Not Skip)

Before touching any file, do the following:

1. List every file inside `resources/views/` recursively — group by subfolder.
2. List every route in `routes/web.php` — note the view each route renders.
3. Identify the **main layout file** (likely `resources/views/layouts/app.blade.php` or `master.blade.php`).
4. Identify the **sidebar partial** (likely `resources/views/partials/sidebar.blade.php` or inside layouts).
5. Identify any **global CSS files** already included (look in the layout `<head>` for custom `.css` links).
6. Check if a file like `public/css/custom.css` or `rdwis-dark.css` or similar already exists.

Report back a complete file tree of all views before proceeding.

---

## STEP 2 — RESPONSIVE STRATEGY (Read Carefully)

### Breakpoints to use (Bootstrap 4 compatible):

```css
/* Large desktops — base (1920×1080) */
/* Default styles apply here */

/* Medium desktops & square monitors (1080×1024, ~1280px wide) */
@media (max-width: 1280px) { }

/* Laptops (1366×768) */
@media (max-width: 1400px) { }

/* Tablets fallback */
@media (max-width: 992px) { }

/* Mobile */
@media (max-width: 768px) { }
```

### AdminLTE Sidebar Rules:
- On `1920px`: sidebar stays expanded (250px wide), content area fills the rest.
- On `1366px` and `1280px`: sidebar **auto-collapses to icon-only mode** (`sidebar-collapse` class on `<body>`) OR reduces to 200px. Font sizes in sidebar reduce slightly.
- On `992px` and below: sidebar becomes **overlay/off-canvas** (standard AdminLTE mobile behavior).
- **Never let the sidebar overlap or push content off-screen.**

### Font Size Scaling Rules:
```css
/* Base (1920px) */
body { font-size: 14px; }
h1 { font-size: 1.8rem; }
h2 { font-size: 1.5rem; }
h3 { font-size: 1.25rem; }
.card-title { font-size: 1rem; }

/* 1366px laptops */
@media (max-width: 1400px) {
  body { font-size: 13px; }
  h1 { font-size: 1.6rem; }
  h2 { font-size: 1.35rem; }
}

/* 1080×1024 square monitors */
@media (max-width: 1280px) {
  body { font-size: 13px; }
  .content-wrapper { padding: 10px 12px; }
}
```

### Cards:
- On `1920px`: 3–4 cards per row is fine.
- On `1366px`: max 3 per row, use `col-lg-4` not `col-xl-3`.
- On `1280px`: max 2–3 per row.
- Cards must NEVER overflow their column or clip text.
- Card padding reduces slightly on smaller screens.

### Tables (DataTables):
- ALL tables must be wrapped in `<div class="table-responsive">` if not already.
- On `1366px` and below: reduce table `font-size` to `12px`, reduce `td` padding to `6px 8px`.
- Column widths should be `%`-based, not fixed `px` widths.
- If a table has more than 6 columns: enable horizontal scroll on smaller screens via `.table-responsive`.
- Action buttons inside table cells: use `btn-sm` and icon-only on `< 1400px` if space is tight.

### Forms:
- Full-width inputs on `< 1400px` — no side-by-side fields unless they are short (like date + time).
- Labels above inputs (not inline) on all sizes.
- Form rows: `col-md-6` pairs become `col-12` on `< 992px`.

### Modals:
- On `1366px`: modal max-width should be `700px`.
- On `1080×1024`: modal max-width `620px`, reduce internal padding.

---

## STEP 3 — GLOBAL CSS FILE

Create or update the file: `public/css/responsive-overrides.css`

This file will contain ALL responsive fixes. It must be included in the main layout AFTER Bootstrap and AdminLTE CSS:

```html
<!-- In resources/views/layouts/app.blade.php (or master layout) -->
<link rel="stylesheet" href="{{ asset('css/responsive-overrides.css') }}">
```

Structure of `responsive-overrides.css`:

```css
/* ============================================
   RESPONSIVE OVERRIDES — AdminLTE 3.x Laravel
   Targets: 1920px / 1366px / 1080px
   ============================================ */

/* ── SIDEBAR ─────────────────────────────── */

/* ── CONTENT WRAPPER ─────────────────────── */

/* ── TYPOGRAPHY ──────────────────────────── */

/* ── CARDS ───────────────────────────────── */

/* ── TABLES ──────────────────────────────── */

/* ── FORMS ───────────────────────────────── */

/* ── MODALS ──────────────────────────────── */

/* ── PAGE-SPECIFIC OVERRIDES ─────────────── */
/* Add per-page rules here with body class or route-based class selectors */
```

---

## STEP 4 — LAYOUT FILE CHANGES

In the main layout file (`app.blade.php` or `master.blade.php`), make these changes:

1. Add `viewport` meta if missing:
```html
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
```

2. Add JS to auto-collapse sidebar on smaller screens:
```javascript
// In main layout <script> or app.js
$(document).ready(function () {
  function checkSidebarCollapse() {
    if ($(window).width() <= 1400) {
      $('body').addClass('sidebar-collapse');
    } else {
      $('body').removeClass('sidebar-collapse');
    }
  }
  checkSidebarCollapse();
  $(window).on('resize', checkSidebarCollapse);
});
```

3. Add body class based on current route for page-specific CSS targeting:
```php
{{-- In <body> tag --}}
<body class="hold-transition sidebar-mini layout-fixed {{ str_replace(['.', '/'], '-', Route::currentRouteName()) }}">
```

---

## STEP 5 — PAGE-BY-PAGE AUDIT

For EACH view file found in Step 1, do the following audit and fix:

### Checklist per page:
- [ ] Does it have `<div class="table-responsive">` around ALL tables?
- [ ] Are `.row` columns using responsive col classes (`col-12 col-md-6 col-lg-4`) not fixed `col-3`?
- [ ] Are there any hardcoded `width:` or `height:` inline styles? Replace with `max-width` or `%`.
- [ ] Are there any `overflow: hidden` that might clip content on small screens?
- [ ] Do buttons have `btn-sm` where space is tight?
- [ ] Are modals using `modal-dialog-scrollable` for long content?
- [ ] Are there any absolute/fixed positioned elements that break on resize?

### Fix approach per page:
- Do NOT rewrite the entire Blade file.
- Only add/change CSS classes and add `table-responsive` wrappers where needed.
- If a page needs unique CSS rules, add them to `responsive-overrides.css` under `/* PAGE-SPECIFIC OVERRIDES */` using body class selector like:
```css
body.purchase-cases-index .some-element { ... }
```

---

## STEP 6 — VERIFICATION CHECKLIST

After all changes, verify:

1. Open each route at `1920px` → should look exactly as before (no regression).
2. Open each route at `1366px` → sidebar collapsed, fonts smaller, tables scrollable, cards fitting.
3. Open each route at `1080×1024` → same as above, no horizontal scrollbar on `<body>`.
4. Check that `overflow-x` on `body` and `html` is never triggered (no horizontal scroll at page level).
5. Check that modals open centered and don't overflow viewport.
6. Check that all form inputs are accessible and not clipped.

---

## STEP 7 — OUTPUT FORMAT

For each file you modify, provide:

```
FILE: [path/to/file]
CHANGE TYPE: [CSS added / Class updated / Wrapper added / JS added]
REASON: [one line why]
---
[only the changed block of code, not the entire file]
```

At the end, provide a summary table:
```
| View File | Issue Found | Fix Applied | Status |
|-----------|-------------|-------------|--------|
```

---

## CONSTRAINTS (DO NOT VIOLATE)

- Do NOT change any PHP/Laravel logic, routes, or controllers.
- Do NOT change any database queries or models.
- Do NOT change the overall AdminLTE theme or color scheme.
- Do NOT rewrite entire Blade files — surgical fixes only.
- Do NOT add new npm packages or change `package.json`.
- All CSS changes go to `public/css/responsive-overrides.css` ONLY (unless it's a class addition in Blade).
- All JS changes go inside existing `@push('scripts')` stacks in each view OR in the main layout.
- Keep Bootstrap 4 grid system — do NOT switch to CSS Grid or Flexbox overrides unless Bootstrap grid is insufficient.

---

## START COMMAND

Begin with **Step 1** — list all view files and routes. Do not write any code until the discovery is complete and reported. Then proceed step by step.
