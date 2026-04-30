# ANTIGRAVITY MASTER PROMPT
## Complete Content Scaling — Laravel + AdminLTE 3.x
### Claude Opus 4.6 | Antigravity Composer

---

## OBJECTIVE

This is a **Laravel + Blade + AdminLTE 3.x + Bootstrap 4 + jQuery** web application.

The design looks **perfect on 1920×1080**. DO NOT change anything about the layout, structure, sidebar, theme, or colors.



**ONLY GOAL:** Every piece of content — text, cards, tables, forms, buttons, badges, icons, inputs, modals, stats boxes, list items, nav items, breadcrumbs, page headers, everything — must **scale down proportionally** on smaller screens so the page looks like a clean, smaller version of the 1920×1080 design.

Target screens:
- `1366 × 768` — laptops
- `1080 × 1024` — square monitors

The user must feel like they are looking at the **same page, just zoomed out slightly** — nothing moves, nothing reorders, nothing changes position. It just gets smaller and fits.

---

## STEP 1 — DISCOVERY

1. List all files in `resources/views/` recursively (every subfolder, every file).
2. Identify the main layout file (`layouts/app.blade.php` or `master.blade.php` or similar).
3. Find all CSS files currently linked in the layout `<head>` section.
4. List all custom CSS files in `public/css/`.

Report this. Do not write any code yet.

---

## STEP 2 — CREATE ONE GLOBAL SCALING FILE

Create: `public/css/responsive-scale.css`

In the main layout file, add this line LAST in the `<head>`, after every other CSS:
```html
<link rel="stylesheet" href="{{ asset('css/responsive-scale.css') }}">
```

Write the complete contents of `responsive-scale.css` as follows:

```css
/* =======================================================
   RESPONSIVE SCALE — AdminLTE 3.x Laravel
   Layout is NEVER changed. Only sizes scale down.
   Base: 1920×1080 | Scale 1: 1366×768 | Scale 2: 1080×1024
   ======================================================= */


/* =====================================================
   SCALE 1 — 1366×768 laptops (max-width: 1400px)
   ===================================================== */
@media (max-width: 1400px) {

  /* ── ROOT FONT ── */
  html {
    font-size: 13px !important;
  }

  /* ── BODY TEXT ── */
  body,
  p, span, li, a, td, th,
  label, small, .small,
  .text-sm, .text-muted,
  .breadcrumb-item,
  .nav-link,
  .dropdown-item {
    font-size: 13px !important;
    line-height: 1.5 !important;
  }

  /* ── HEADINGS ── */
  h1, .h1 { font-size: 1.45rem !important; }
  h2, .h2 { font-size: 1.25rem !important; }
  h3, .h3 { font-size: 1.1rem !important; }
  h4, .h4 { font-size: 1rem !important; }
  h5, .h5 { font-size: 0.9rem !important; }
  h6, .h6 { font-size: 0.85rem !important; }

  /* ── PAGE HEADER ── */
  .content-header {
    padding: 8px 12px !important;
  }
  .content-header h1 {
    font-size: 1.3rem !important;
  }
  .breadcrumb {
    font-size: 12px !important;
    padding: 2px 0 !important;
    margin-bottom: 0 !important;
  }

  /* ── CONTENT WRAPPER ── */
  .content-wrapper {
    padding: 10px 12px !important;
  }
  .content {
    padding: 0 !important;
  }

  /* ── CARDS ── */
  .card {
    margin-bottom: 12px !important;
  }
  .card-header {
    padding: 8px 12px !important;
  }
  .card-header .card-title,
  .card-title {
    font-size: 0.95rem !important;
    font-weight: 600 !important;
  }
  .card-body {
    padding: 12px !important;
  }
  .card-footer {
    padding: 8px 12px !important;
    font-size: 12px !important;
  }
  .card-text {
    font-size: 13px !important;
  }

  /* ── INFO BOXES / STAT WIDGETS ── */
  .info-box {
    min-height: 60px !important;
  }
  .info-box-icon {
    width: 60px !important;
    line-height: 60px !important;
    font-size: 1.4rem !important;
  }
  .info-box-content {
    padding: 6px 10px !important;
  }
  .info-box-text {
    font-size: 12px !important;
  }
  .info-box-number {
    font-size: 1.2rem !important;
  }
  .small-box {
    padding: 0 !important;
  }
  .small-box h3 {
    font-size: 2rem !important;
  }
  .small-box p {
    font-size: 12px !important;
  }
  .small-box .icon {
    font-size: 60px !important;
    top: 0 !important;
  }

  /* ── TABLES ── */
  .table {
    font-size: 12px !important;
  }
  .table td,
  .table th {
    padding: 6px 8px !important;
    font-size: 12px !important;
    vertical-align: middle !important;
  }
  .table thead th {
    font-size: 12px !important;
    font-weight: 600 !important;
    padding: 7px 8px !important;
  }
  .dataTable {
    font-size: 12px !important;
  }
  .dataTables_wrapper .dataTables_length,
  .dataTables_wrapper .dataTables_filter,
  .dataTables_wrapper .dataTables_info,
  .dataTables_wrapper .dataTables_paginate {
    font-size: 12px !important;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 3px 8px !important;
    font-size: 12px !important;
  }

  /* ── FORMS ── */
  .form-control,
  .form-select,
  .custom-select,
  input[type="text"],
  input[type="email"],
  input[type="password"],
  input[type="number"],
  input[type="date"],
  input[type="search"],
  textarea,
  select {
    font-size: 13px !important;
    height: calc(1.5em + 0.65rem + 2px) !important;
    padding: 0.3rem 0.65rem !important;
  }
  textarea.form-control {
    height: auto !important;
  }
  .form-group {
    margin-bottom: 10px !important;
  }
  .form-group label,
  .col-form-label {
    font-size: 12.5px !important;
    margin-bottom: 3px !important;
  }
  .form-text,
  .invalid-feedback,
  .valid-feedback {
    font-size: 11px !important;
  }
  .input-group-text {
    font-size: 12px !important;
    padding: 0.3rem 0.65rem !important;
  }

  /* ── BUTTONS ── */
  .btn {
    font-size: 12.5px !important;
    padding: 5px 12px !important;
  }
  .btn-sm, .btn-group-sm > .btn {
    font-size: 11.5px !important;
    padding: 3px 8px !important;
  }
  .btn-lg, .btn-group-lg > .btn {
    font-size: 14px !important;
    padding: 7px 16px !important;
  }
  .btn i, .btn .fas, .btn .far, .btn .fab {
    font-size: 12px !important;
  }

  /* ── BADGES & LABELS ── */
  .badge {
    font-size: 10.5px !important;
    padding: 3px 6px !important;
  }
  .tag {
    font-size: 11px !important;
  }

  /* ── ALERTS ── */
  .alert {
    padding: 8px 12px !important;
    font-size: 13px !important;
  }
  .alert-heading {
    font-size: 1rem !important;
  }

  /* ── MODALS ── */
  .modal-dialog {
    max-width: 680px !important;
  }
  .modal-header {
    padding: 10px 14px !important;
  }
  .modal-title {
    font-size: 1rem !important;
  }
  .modal-body {
    padding: 12px 14px !important;
    font-size: 13px !important;
  }
  .modal-footer {
    padding: 8px 14px !important;
  }

  /* ── SIDEBAR ── */
  .main-sidebar {
    width: 230px !important;
  }
  .brand-link {
    padding: 8px 12px !important;
    font-size: 14px !important;
  }
  .brand-text {
    font-size: 14px !important;
  }
  .nav-sidebar .nav-link {
    padding: 6px 10px 6px 14px !important;
    font-size: 13px !important;
  }
  .nav-sidebar .nav-link p {
    font-size: 13px !important;
  }
  .nav-sidebar .nav-icon {
    font-size: 0.95rem !important;
    margin-right: 8px !important;
  }
  .nav-sidebar .nav-treeview .nav-link {
    padding: 5px 10px 5px 28px !important;
    font-size: 12.5px !important;
  }
  .nav-sidebar > .nav-item > .nav-link {
    font-size: 13px !important;
  }
  .sidebar-mini.sidebar-open .main-sidebar:hover ~ .content-wrapper,
  .content-wrapper,
  .main-footer,
  .main-header {
    margin-left: 230px !important;
  }

  /* ── TOP NAVBAR / HEADER ── */
  .main-header {
    min-height: 44px !important;
  }
  .main-header .nav-link {
    height: 44px !important;
    padding: 0 10px !important;
    font-size: 13px !important;
  }
  .navbar-nav .nav-link {
    font-size: 13px !important;
  }
  .navbar-badge {
    font-size: 9px !important;
    padding: 2px 4px !important;
  }

  /* ── DROPDOWN MENUS ── */
  .dropdown-menu {
    font-size: 13px !important;
    min-width: 140px !important;
  }
  .dropdown-item {
    padding: 5px 14px !important;
    font-size: 12.5px !important;
  }
  .dropdown-header {
    font-size: 11px !important;
    padding: 5px 14px !important;
  }

  /* ── TABS ── */
  .nav-tabs .nav-link {
    font-size: 12.5px !important;
    padding: 6px 12px !important;
  }
  .nav-pills .nav-link {
    font-size: 12.5px !important;
    padding: 5px 12px !important;
  }

  /* ── LIST GROUPS ── */
  .list-group-item {
    padding: 7px 12px !important;
    font-size: 13px !important;
  }

  /* ── TIMELINE (AdminLTE) ── */
  .timeline > li > .timeline-item {
    font-size: 12.5px !important;
    padding: 8px !important;
  }
  .timeline-header {
    font-size: 13px !important;
  }
  .time {
    font-size: 11px !important;
  }

  /* ── CALLOUTS ── */
  .callout {
    padding: 10px 12px !important;
    font-size: 13px !important;
  }

  /* ── PROGRESS BARS ── */
  .progress {
    height: 10px !important;
  }
  .progress-bar {
    font-size: 10px !important;
  }
  .progress-description {
    font-size: 12px !important;
  }

  /* ── ICONS (FontAwesome) ── */
  .fas, .far, .fab, .fal {
    font-size: 13px !important;
  }
  .fa-2x { font-size: 1.6em !important; }
  .fa-3x { font-size: 2.2em !important; }
  .fa-lg  { font-size: 1.1em !important; }

  /* ── SECTION TITLES / DIVIDERS ── */
  .nav-header,
  .nav-sidebar .nav-header {
    font-size: 10px !important;
    padding: 5px 10px 3px !important;
  }

  /* ── SELECT2 / CHOSEN ── */
  .select2-container .select2-selection--single {
    height: 32px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    font-size: 13px !important;
    line-height: 30px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 30px !important;
  }
  .select2-results__option {
    font-size: 13px !important;
    padding: 5px 8px !important;
  }

  /* ── PAGINATION ── */
  .pagination .page-link {
    font-size: 12px !important;
    padding: 4px 9px !important;
  }

  /* ── TOOLTIPS / POPOVERS ── */
  .tooltip-inner {
    font-size: 11px !important;
    padding: 4px 8px !important;
  }
  .popover {
    font-size: 12.5px !important;
  }
  .popover-header {
    font-size: 13px !important;
    padding: 6px 10px !important;
  }
  .popover-body {
    font-size: 12.5px !important;
    padding: 8px 10px !important;
  }

  /* ── SPACING UTILITIES ── */
  .mt-4 { margin-top: 1rem !important; }
  .mb-4 { margin-bottom: 1rem !important; }
  .pt-4 { padding-top: 1rem !important; }
  .pb-4 { padding-bottom: 1rem !important; }

}


/* =====================================================
   SCALE 2 — 1080×1024 square monitors (max-width: 1280px)
   Slightly more compact than Scale 1
   ===================================================== */
@media (max-width: 1280px) {

  /* ── ROOT FONT ── */
  html {
    font-size: 12.5px !important;
  }

  /* ── BODY TEXT ── */
  body,
  p, span, li, a, td, th,
  label, small, .small,
  .text-sm, .text-muted,
  .breadcrumb-item, .nav-link,
  .dropdown-item {
    font-size: 12.5px !important;
  }

  /* ── HEADINGS ── */
  h1, .h1 { font-size: 1.35rem !important; }
  h2, .h2 { font-size: 1.15rem !important; }
  h3, .h3 { font-size: 1.05rem !important; }
  h4, .h4 { font-size: 0.95rem !important; }
  h5, .h5 { font-size: 0.85rem !important; }
  h6, .h6 { font-size: 0.8rem !important; }

  /* ── CONTENT WRAPPER ── */
  .content-wrapper {
    padding: 8px 10px !important;
  }
  .content-header {
    padding: 7px 10px !important;
  }
  .content-header h1 {
    font-size: 1.2rem !important;
  }

  /* ── CARDS ── */
  .card-header {
    padding: 7px 10px !important;
  }
  .card-title {
    font-size: 0.9rem !important;
  }
  .card-body {
    padding: 10px !important;
  }
  .card-footer {
    padding: 7px 10px !important;
  }

  /* ── INFO BOXES ── */
  .info-box-icon {
    width: 55px !important;
    line-height: 55px !important;
    font-size: 1.2rem !important;
  }
  .info-box {
    min-height: 55px !important;
  }
  .info-box-number {
    font-size: 1.1rem !important;
  }
  .small-box h3 {
    font-size: 1.8rem !important;
  }

  /* ── TABLES ── */
  .table td,
  .table th,
  .table thead th {
    padding: 5px 7px !important;
    font-size: 11.5px !important;
  }

  /* ── FORMS ── */
  .form-control,
  input[type="text"],
  input[type="email"],
  input[type="password"],
  input[type="number"],
  input[type="date"],
  input[type="search"],
  textarea,
  select {
    font-size: 12.5px !important;
    height: calc(1.5em + 0.55rem + 2px) !important;
    padding: 0.28rem 0.6rem !important;
  }
  .form-group label,
  .col-form-label {
    font-size: 12px !important;
  }

  /* ── BUTTONS ── */
  .btn {
    font-size: 12px !important;
    padding: 4px 10px !important;
  }
  .btn-sm {
    font-size: 11px !important;
    padding: 3px 7px !important;
  }

  /* ── BADGES ── */
  .badge {
    font-size: 10px !important;
    padding: 2px 5px !important;
  }

  /* ── SIDEBAR ── */
  .main-sidebar {
    width: 210px !important;
  }
  .nav-sidebar .nav-link p,
  .nav-sidebar .nav-link {
    font-size: 12.5px !important;
  }
  .content-wrapper,
  .main-footer,
  .main-header {
    margin-left: 210px !important;
  }

  /* ── MODALS ── */
  .modal-dialog {
    max-width: 580px !important;
  }
  .modal-body {
    font-size: 12.5px !important;
    padding: 10px 12px !important;
  }

  /* ── ICONS ── */
  .fas, .far, .fab {
    font-size: 12.5px !important;
  }

  /* ── SELECT2 ── */
  .select2-container .select2-selection--single {
    height: 30px !important;
  }
  .select2-container--default .select2-selection--single .select2-selection__rendered {
    font-size: 12.5px !important;
    line-height: 28px !important;
  }
  .select2-results__option {
    font-size: 12.5px !important;
  }

  /* ── PAGINATION ── */
  .pagination .page-link {
    font-size: 11.5px !important;
    padding: 3px 8px !important;
  }

  /* ── TABS ── */
  .nav-tabs .nav-link,
  .nav-pills .nav-link {
    font-size: 12px !important;
    padding: 5px 10px !important;
  }

  /* ── LIST GROUP ── */
  .list-group-item {
    padding: 6px 10px !important;
    font-size: 12.5px !important;
  }

}


/* =====================================================
   UNIVERSAL — All screens, all sizes
   Tables always scroll, never break layout
   ===================================================== */

.table-responsive {
  overflow-x: auto !important;
  -webkit-overflow-scrolling: touch;
}

.card .table:not(.table-responsive > .table) {
  display: block;
  overflow-x: auto;
}

html, body {
  overflow-x: hidden;
}
```

---

## STEP 3 — BLADE FILE CHANGES (Minimal, Surgical)

Go through EVERY `.blade.php` file in `resources/views/`.

For each file, do ONLY these changes:

### 3a — Wrap bare tables in table-responsive:

Find any `<table` that is NOT already inside `<div class="table-responsive">`.
Wrap it:
```html
<div class="table-responsive">
  <table ...>
  ...
  </table>
</div>
```

### 3b — Fix non-responsive Bootstrap column classes:

Find `.row` children that have `col-X` without a breakpoint (no `col-sm-`, `col-md-`, `col-lg-`).
Replace:
- `col-2` → `col-12 col-md-4 col-lg-2`
- `col-3` → `col-12 col-md-6 col-lg-3`
- `col-4` → `col-12 col-md-6 col-lg-4`
- `col-5` → `col-12 col-md-6 col-lg-5`
- `col-6` → `col-12 col-md-6`
- `col-8` → `col-12 col-lg-8`

**Touch nothing else in any Blade file.**

---

## STEP 4 — ABSOLUTE NO-TOUCH LIST

- ❌ No PHP logic, controllers, models, routes
- ❌ No existing CSS files
- ❌ No color or theme changes
- ❌ No layout structure (sidebar position, header, footer stay exactly same)
- ❌ No JavaScript
- ❌ No full Blade file rewrites
- ❌ No adding or removing sections/components

---

## STEP 5 — OUTPUT FORMAT

For `responsive-scale.css` — just confirm it was created.

For each Blade file changed:
```
FILE: resources/views/[path]
CHANGE: [table-responsive wrap | col class fix | both]
---
[only changed lines, before → after]
```

Final summary table:
```
| File | Change |
|------|--------|
```

---

## START

**Step 1 first** — list all view files grouped by folder.
Confirm list, then proceed to Step 2 and 3 automatically without stopping.
