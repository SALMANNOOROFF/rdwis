@extends('welcome')
@section('content')

<div class="content-wrapper p-0">
  <div class="container-fluid p-0">
    <div class="sinc-wrapper">
<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

  :root {
    --accent:        #4361ee;
    --accent-soft:   #eef0fd;
    --accent-hover:  #3451d1;
    --success:       #2d9e6b;
    --success-soft:  #edfaf4;
    --danger:        #e24c4c;
    --danger-soft:   #fef2f2;
    --warning:       #d97706;
    --warning-soft:  #fffbeb;
    --text-primary:  #1a2035;
    --text-secondary:#64748b;
    --text-muted:    #94a3b8;
    --border:        #e8ecf0;
    --border-light:  #f1f5f9;
    --surface:       #ffffff;
    --bg:            #f4f6fa;
    --sidebar-bg:    #f8fafc;
    --shadow-sm:     0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --shadow-md:     0 4px 16px rgba(0,0,0,0.08);
    --shadow-lg:     0 10px 30px rgba(0,0,0,0.10);
    --radius-sm:     6px;
    --radius-md:     10px;
    --radius-lg:     14px;
  }

  * { box-sizing: border-box; }

  .sinc-wrapper {
    min-height: 100vh;
    background: var(--bg);
    font-family: 'DM Sans', sans-serif;
    padding: 20px 20px 60px;
    color: var(--text-primary);
  }

  /* ── Topbar ── */
  .sinc-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
  }
  .sinc-page-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 2px;
    letter-spacing: -0.3px;
  }
  .sinc-page-sub {
    font-size: 0.82rem;
    color: var(--text-muted);
    margin: 0;
  }
  .nav-actions { display: flex; gap: 8px; }

  .btn-primary-soft {
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.82rem;
    padding: 7px 14px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .15s, transform .1s;
  }
  .btn-primary-soft:hover { background: var(--accent-hover); color: #fff; transform: translateY(-1px); }

  .btn-secondary-soft {
    background: var(--surface);
    color: var(--text-secondary);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.82rem;
    padding: 7px 14px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .15s, border-color .15s;
  }
  .btn-secondary-soft:hover { background: var(--border-light); border-color: #d1d5db; color: var(--text-primary); }

  /* ── Search ── */
  .search-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
  }
  .search-box {
    position: relative;
    width: 260px;
  }
  .search-box i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 0.8rem;
  }
  .search-box input {
    width: 100%;
    padding: 7px 12px 7px 30px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 0.82rem;
    font-family: 'DM Sans', sans-serif;
    color: var(--text-primary);
    background: var(--surface);
    outline: none;
    transition: border-color .2s, box-shadow .2s;
  }
  .search-box input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(67,97,238,.12);
  }
  .search-box input::placeholder { color: var(--text-muted); }

  /* ── Table Card ── */
  .table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
  }

  .rfq-table { width: 100%; border-collapse: collapse; }
  .rfq-table thead th {
    background: var(--sidebar-bg);
    color: var(--text-muted);
    font-weight: 600;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 10px 14px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
  }
  .rfq-table tbody td {
    padding: 10px 14px;
    font-size: 0.845rem;
    color: var(--text-secondary);
    border-bottom: 1px solid var(--border-light);
    vertical-align: middle;
  }
  .rfq-table tbody tr.main-row:last-child td { border-bottom: none; }
  .rfq-table tbody tr.main-row:hover td { background: #fafbff; }

  .rfq-id-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 5px;
    font-weight: 700;
    font-size: 0.72rem;
    background: var(--accent-soft);
    color: var(--accent);
    font-family: 'DM Mono', monospace;
    letter-spacing: 0.3px;
  }

  .price-val {
    font-weight: 700;
    color: var(--success);
    font-size: 0.88rem;
    font-family: 'DM Mono', monospace;
  }

  /* ── Row Actions ── */
  .row-actions { display: flex; align-items: center; gap: 2px; }
  .icon-btn {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    border-radius: var(--radius-sm);
    cursor: pointer;
    color: var(--text-muted);
    font-size: 0.78rem;
    transition: background .15s, color .15s;
  }
  .icon-btn:hover                { background: var(--border-light); color: var(--text-primary); }
  .icon-btn.danger:hover         { background: var(--danger-soft);  color: var(--danger); }
  .icon-btn.primary:hover        { background: var(--accent-soft);  color: var(--accent); }
  .icon-btn.info:hover           { background: #e0f2fe; color: #0284c7; }

  /* ── Details Row ── */
  .details-row { background: var(--sidebar-bg); }
  .details-box {
    padding: 16px;
    margin: 12px 16px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
  }

  /* ── Inline sub-table ── */
  .sub-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
  .sub-table thead th {
    background: var(--sidebar-bg);
    color: var(--text-muted);
    font-weight: 600;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 8px 12px;
    border-bottom: 1px solid var(--border);
  }
  .sub-table tbody td {
    padding: 9px 12px;
    border-bottom: 1px solid var(--border-light);
    color: var(--text-secondary);
    vertical-align: middle;
  }
  .sub-table tbody tr:last-child td { border-bottom: none; }
  .sub-table tbody tr:hover td { background: #fafbff; }

  /* ── Vendor Select (inline) ── */
  .vendor-inline-select {
    border: 1px solid var(--border) !important;
    border-radius: 6px !important;
    padding: 4px 8px !important;
    font-weight: 600 !important;
    font-size: 0.78rem !important;
    color: var(--text-primary) !important;
    background: var(--surface) !important;
    max-width: 200px;
    width: 100%;
    outline: none;
    cursor: pointer;
    transition: border-color .15s;
  }
  .vendor-inline-select:focus { border-color: var(--accent) !important; box-shadow: 0 0 0 3px rgba(67,97,238,.1) !important; }

  /* ── Toast ── */
  .toast-container-custom { position: fixed; bottom: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 8px; }
  .custom-toast {
    background: var(--text-primary);
    color: #fff;
    padding: 10px 18px;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-lg);
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    min-width: 220px;
    transform: translateX(120%);
    animation: slideInRight 0.25s ease forwards;
  }
  .custom-toast .toast-icon { font-size: 0.9rem; }
  @keyframes slideInRight {
    from { transform: translateX(120%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
  }
  @keyframes slideOutRight {
    from { transform: translateX(0);    opacity: 1; }
    to   { transform: translateX(120%); opacity: 0; }
  }
  .custom-toast.dismissing { animation: slideOutRight 0.25s ease forwards; }

  /* ====================================================
     MODALS — complete overhaul
  ==================================================== */

  /* Base modal content */
  .modal-content {
    border: 1px solid var(--border) !important;
    border-radius: var(--radius-lg) !important;
    box-shadow: var(--shadow-lg) !important;
    overflow: hidden;
  }

  /* ── Quotation Modal (fullscreen) ── */
  .modal-fullscreen { width: 100vw !important; max-width: 100% !important; height: 100vh !important; margin: 0 !important; padding: 0 !important; }
  #quotationModal .modal-content {
    background: var(--surface) !important;
    height: 100vh;
    border: 0 !important;
    border-radius: 0 !important;
  }

  .quote-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    border-bottom: 1px solid var(--border);
    background: var(--surface);
    gap: 16px;
    flex-wrap: wrap;
  }
  .quote-modal-title { font-size: 0.95rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 8px; }
  .quote-modal-sub   { font-size: 0.76rem; color: var(--text-muted); margin-top: 1px; }
  .quote-meta-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--accent-soft);
    color: var(--accent);
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 0.75rem;
    font-weight: 600;
    font-family: 'DM Mono', monospace;
  }

  .quote-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 20px;
    border-bottom: 1px solid var(--border);
    background: var(--sidebar-bg);
  }
  .ctrl-label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); white-space: nowrap; }
  .ctrl-select, .ctrl-input {
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 5px 10px;
    font-size: 0.8rem;
    font-family: 'DM Sans', sans-serif;
    font-weight: 600;
    color: var(--text-primary);
    background: var(--surface);
    outline: none;
    transition: border-color .15s;
  }
  .ctrl-select:focus, .ctrl-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(67,97,238,.1); }
  .ctrl-input { width: 60px; text-align: center; }

  .quote-table-container {
    height: calc(100vh - 148px);
    overflow: auto;
    background: var(--sidebar-bg);
  }

  #quotationTable {
    background: transparent !important;
    border-collapse: separate;
    border-spacing: 0;
    width: max-content;
    table-layout: fixed;
  }
  #quotationTable tr { height: 1px; }

  /* sticky item col */
  #quotationTable th.sticky-left,
  #quotationTable td.sticky-left {
    position: sticky; left: 0; z-index: 5;
    background-color: var(--sidebar-bg);
    width: 230px; min-width: 230px;
    border-right: 1px solid var(--border) !important;
    padding: 0 !important;
    height: inherit;
  }
  #quotationTable thead th.sticky-left { z-index: 11; }
  #quotationTable tfoot th.sticky-left { z-index: 13; }

  .item-desc-content {
    padding: 8px 14px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
    font-size: 0.8rem;
  }

  #quotationTable td { height: inherit; padding: 4px 0 !important; border-bottom: 1px solid var(--border-light); }
  #quotationTable thead th {
    background: var(--surface) !important;
    border-bottom: 1px solid var(--border) !important;
    padding: 0 !important;
    height: 52px;
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.8rem;
  }

  #quotationTable tfoot { position: sticky; bottom: 0; z-index: 12; }
  #quotationTable tfoot th,
  #quotationTable tfoot td {
    background: var(--surface) !important;
    border-top: 1px solid var(--border) !important;
    border-bottom: none !important;
    position: sticky;
    bottom: 0;
    font-weight: 700;
    padding: 0 !important;
  }

  /* vendor column header */
  .vendor-col-header {
    background: var(--surface) !important;
    padding: 0 !important;
    min-width: 180px !important;
    border-bottom: none !important;
    border-right: 1px solid var(--border-light) !important;
  }
  .vendor-col-header-inner {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
  }
  .vendor-header-select {
    flex: 1;
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 5px 8px;
    font-weight: 600;
    font-size: 0.77rem;
    color: var(--text-primary);
    background: var(--surface);
    outline: none;
    min-width: 0;
    cursor: pointer;
    transition: border-color .15s;
  }
  .vendor-header-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(67,97,238,.1); }
  .remove-col-btn {
    width: 24px;
    height: 24px;
    border: none;
    background: transparent;
    color: var(--text-muted);
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
    transition: background .15s, color .15s;
  }
  .remove-col-btn:hover { background: var(--danger-soft); color: var(--danger); }

  /* price input cells */
  .price-input-cell {
    padding: 5px 8px !important;
    border-right: 1px solid var(--border-light) !important;
    min-width: 180px;
    background: var(--surface);
  }
  .price-input {
    width: 100%;
    border: 1px solid var(--border) !important;
    border-radius: 5px !important;
    font-size: 0.8rem !important;
    padding: 5px 8px !important;
    font-weight: 500;
    color: var(--text-primary);
    background: var(--surface);
    outline: none;
    font-family: 'DM Mono', monospace;
    transition: border-color .15s, box-shadow .15s;
  }
  .price-input:focus { border-color: var(--accent) !important; box-shadow: 0 0 0 3px rgba(67,97,238,.1) !important; }
  .price-input:disabled { background: var(--sidebar-bg) !important; color: var(--text-muted); cursor: not-allowed; }
  .price-input.saved-ok { background: var(--success-soft) !important; border-color: var(--success) !important; color: var(--success); font-weight: 700; }

  /* total footer cells */
  .total-cell-inner {
    padding: 10px 14px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .total-sub  { font-size: 0.72rem; color: var(--text-muted); font-family: 'DM Mono', monospace; }
  .total-main { font-size: 1rem; font-weight: 700; color: var(--accent); font-family: 'DM Mono', monospace; margin-top: 1px; }
  .total-tax  { font-size: 0.7rem; color: var(--success); }

  .quote-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 20px;
    border-top: 1px solid var(--border);
    background: var(--surface);
  }
  .autosave-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.75rem;
    color: var(--success);
    font-weight: 500;
  }

  /* ── Confirm Modal ── */
  #confirmModal .modal-content { max-width: 380px; }
  .confirm-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: var(--danger-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    font-size: 1.3rem;
    color: var(--danger);
  }
  .confirm-body { padding: 24px 24px 16px; text-align: center; }
  .confirm-title-text { font-weight: 700; color: var(--text-primary); font-size: 1rem; margin-bottom: 6px; }
  .confirm-desc { font-size: 0.83rem; color: var(--text-muted); line-height: 1.5; white-space: pre-line; }
  .confirm-footer { display: flex; gap: 8px; padding: 0 24px 20px; }
  .btn-cancel { flex: 1; padding: 8px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--surface); color: var(--text-secondary); font-weight: 600; font-size: 0.83rem; cursor: pointer; transition: background .15s; }
  .btn-cancel:hover { background: var(--border-light); }
  .btn-confirm-danger { flex: 1; padding: 8px; border: none; border-radius: var(--radius-sm); background: var(--danger); color: #fff; font-weight: 600; font-size: 0.83rem; cursor: pointer; transition: background .15s; }
  .btn-confirm-danger:hover { background: #c73e3e; }

  /* ── Tracking Modal ── */
  #trackingModal .modal-dialog { max-width: 400px; }
  .tracking-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .tracking-header-title { font-weight: 700; font-size: 0.9rem; color: var(--text-primary); display: flex; align-items: center; gap: 8px; }
  .tracking-body { padding: 16px 20px; background: var(--bg); }
  .tracking-group-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); margin-bottom: 14px; }
  .tracking-item {
    display: flex;
    gap: 12px;
    position: relative;
    padding-bottom: 14px;
  }
  .tracking-item::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 32px;
    bottom: 0;
    width: 1px;
    background: var(--border);
  }
  .tracking-item:last-child::before { display: none; }
  .tracking-dot {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--accent-soft);
    border: 2px solid var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
    font-size: 0.7rem;
    flex-shrink: 0;
    margin-top: 2px;
  }
  .tracking-content {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 10px 14px;
    flex: 1;
  }
  .tracking-ev-title { font-weight: 700; font-size: 0.82rem; color: var(--text-primary); margin-bottom: 3px; }
  .tracking-ev-meta  { font-size: 0.75rem; color: var(--text-muted); display: flex; gap: 12px; flex-wrap: wrap; }

  /* ── Edit Group Modal ── */
  #editGroupModal .modal-dialog { max-width: 400px; }
  .eg-header {
    padding: 16px 20px;
    background: var(--accent);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .eg-header-title { font-weight: 700; color: #fff; font-size: 0.92rem; display: flex; align-items: center; gap: 8px; }
  .eg-body { padding: 20px; }
  .eg-footer { padding: 0 20px 20px; }

  .form-field-group { margin-bottom: 14px; }
  .field-label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--text-muted); margin-bottom: 4px; display: block; }
  .field-input, .field-select {
    width: 100%;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
    font-size: 0.83rem;
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    color: var(--text-primary);
    background: var(--surface);
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    appearance: none;
  }
  .field-input:focus, .field-select:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(67,97,238,.1);
  }
  .field-input[readonly] { background: var(--sidebar-bg); color: var(--text-secondary); cursor: default; }
  .field-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px; cursor: pointer; }

  .btn-save-full {
    width: 100%;
    padding: 10px;
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-weight: 700;
    font-size: 0.87rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background .15s, transform .1s;
  }
  .btn-save-full:hover { background: var(--accent-hover); transform: translateY(-1px); }

  /* ── Spinner ── */
  .spin-sm {
    width: 16px; height: 16px;
    border: 2px solid var(--border);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin .6s linear infinite;
    display: inline-block;
  }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* Empty state */
  .empty-state { padding: 48px 20px; text-align: center; }
  .empty-icon { font-size: 2rem; color: var(--border); margin-bottom: 12px; }
  .empty-title { font-weight: 700; font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 4px; }
  .empty-sub   { font-size: 0.8rem; color: var(--text-muted); }
</style>

<div class="toast-container-custom" id="toastContainer"></div>

<!-- Topbar -->
<div class="sinc-topbar">
  <div>
    <h3 class="sinc-page-title"><i class="fas fa-layer-group me-2 text-primary" style="font-size:1rem;opacity:.7;"></i>Procurement Groups</h3>
    <p class="sinc-page-sub">Manage RFQ groups and consolidate vendor quotations.</p>
  </div>
  <div class="nav-actions">
    <a href="{{ route('viewpurchasecase') }}" class="btn-secondary-soft"><i class="fas fa-briefcase"></i> View Cases</a>
    <a href="{{ route('purnew.create') }}" class="btn-primary-soft"><i class="fas fa-plus"></i> New RFQ</a>
  </div>
</div>

<!-- Search -->
<form method="get" action="{{ route('purnew.groups') }}">
  <div class="search-wrap">
    <div class="search-box">
      <i class="fas fa-search"></i>
      <input type="text" name="term" value="{{ request('term') }}" placeholder="Search RFQs or vendor…">
    </div>
    <button type="submit" class="btn-primary-soft" style="padding: 7px 12px;">Search</button>
  </div>
</form>

<!-- Table -->
<div class="table-card">
  <div class="table-responsive">
    <table class="rfq-table" id="demandTable">
      <thead>
        <tr>
          <th style="width:100px" class="ps-4">RFQ ID</th>
          <th>Project Title</th>
          <th style="width:140px">Date</th>
          <th style="width:160px" class="text-end">Estimate</th>
          <th style="width:130px" class="text-end pe-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rfqs as $r)
        <tr class="main-row">
          <td class="ps-4"><span class="rfq-id-badge">#{{ $r->rfq_id }}</span></td>
          <td>
            <div class="fw-bold text-dark" style="font-size:.87rem;">{{ $r->pcs_title }}</div>
            <div class="mt-1" style="font-size:.75rem; color:var(--text-muted);">{{ count($r->items ?? []) }} Items</div>
          </td>
          <td>
            <div class="fw-600 text-dark" style="font-size:.82rem;">{{ \Carbon\Carbon::parse($r->pcs_date)->format('d M Y') }}</div>
            <div style="font-size:.72rem; color:var(--text-muted);">{{ \Carbon\Carbon::parse($r->pcs_date)->diffForHumans() }}</div>
          </td>
          <td class="text-end">
            <div class="price-val" id="total_{{ $r->rfq_id }}">Rs. {{ number_format($r->total, 0) }}</div>
            <div style="font-size:.72rem; color:var(--text-muted);">Avg Rs. {{ number_format($r->total / max(1, count($r->items ?? [])), 0) }}</div>
          </td>
          <td class="text-end pe-3">
            <div class="row-actions justify-content-end">
              <button class="icon-btn" onclick="toggleDetails({{ $r->rfq_id }})" title="Toggle Details">
                <i class="fas fa-chevron-down" id="icon_{{ $r->rfq_id }}" style="transition:.25s;"></i>
              </button>
              <button class="icon-btn" onclick="showTracking({{ $r->rfq_id }}, '{{ addslashes($r->pcs_title) }}', '{{ \Carbon\Carbon::parse($r->pcs_date)->format('d M Y') }}')" title="Activity Log">
                <i class="fas fa-history"></i>
              </button>
              <button class="icon-btn primary" onclick="openQuotationModal({{ $r->rfq_id }}, '{{ addslashes($r->pcs_title) }}')" title="Manage Quotes">
                <i class="fas fa-file-invoice-dollar"></i>
              </button>
              <button class="icon-btn info" onclick="openEditGroupModal({{ $r->rfq_id }}, '{{ addslashes($r->pcs_title) }}')" title="Edit Group">
                <i class="fas fa-pen"></i>
              </button>
              <button class="icon-btn danger" onclick="deleteGroup({{ $r->rfq_id }}, '{{ addslashes($r->pcs_title) }}')" title="Delete RFQ">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          </td>
        </tr>
        <tr id="details_row_{{ $r->rfq_id }}" class="details-row d-none">
          <td colspan="5" class="p-0 border-0">
            <div class="details-box" id="details_content_{{ $r->rfq_id }}">
              <div class="text-center py-4 text-muted" style="font-size:.82rem;">
                <span class="spin-sm me-2"></span> Loading items…
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="5">
            <div class="empty-state">
              <div class="empty-icon"><i class="fas fa-inbox"></i></div>
              <div class="empty-title">No RFQ Groups Found</div>
              <div class="empty-sub">Create your first group to get started.</div>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>


<!-- ═══════════════════════════════════════════
     QUOTATION MODAL (fullscreen)
═══════════════════════════════════════════ -->
<div class="modal fade" id="quotationModal" tabindex="-1">
  <div class="modal-dialog modal-fullscreen" style="margin:0;padding:0;">
    <div class="modal-content" style="border-radius:0!important;border:0!important;">

      <!-- Header -->
      <div class="quote-modal-header">
        <div>
          <div class="quote-modal-title">
            <i class="fas fa-file-invoice-dollar" style="color:var(--accent);font-size:.9rem;"></i>
            Quote Management
          </div>
          <div class="quote-modal-sub">
            <span id="quote_group_name" class="fw-bold text-dark me-1"></span>
            <span class="quote-meta-pill"># <span id="quote_group_id"></span></span>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3 ms-auto">
          <button class="btn-primary-soft" onclick="addNewVendorColumn()">
            <i class="fas fa-user-plus"></i> Add Vendor
          </button>
          <button type="button" style="background:none;border:none;color:var(--text-muted);font-size:1.1rem;cursor:pointer;line-height:1;" data-bs-dismiss="modal" onclick="closeQuotationModal()">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </div>

      <!-- Tax Controls -->
      <div class="quote-controls">
        <span class="ctrl-label">Tax Type:</span>
        <select id="quote_tax_type" class="ctrl-select" style="width:80px;" onchange="calculateTotals()">
          <option value="gst">GST</option>
          <option value="sst">SST</option>
        </select>
        <span class="ctrl-label ms-2">Rate %:</span>
        <input type="number" id="quote_tax_perc" class="ctrl-input" value="18" oninput="calculateTotals()">
      </div>

      <!-- Table -->
      <div class="modal-body p-0">
        <div class="quote-table-container">
          <table class="table align-middle mb-0" id="quotationTable">
            <thead>
              <tr id="quote_header_row">
                <th class="sticky-left">
                  <div class="item-desc-content fw-bold" style="color:var(--text-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;">Item / Description</div>
                </th>
              </tr>
            </thead>
            <tbody id="quote_body">
              <tr><td colspan="10" class="text-center py-5" style="color:var(--text-muted);font-size:.82rem;"><span class="spin-sm me-2"></span> Loading…</td></tr>
            </tbody>
            <tfoot id="quote_footer">
              <tr id="quote_total_row">
                <th class="sticky-left">
                  <div class="item-desc-content fw-bold" style="color:var(--accent);font-size:.75rem;text-transform:uppercase;letter-spacing:.4px;">TOTAL (INC. TAX)</div>
                </th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Footer -->
      <div class="quote-modal-footer">
        <div class="autosave-badge"><i class="fas fa-circle" style="font-size:.5rem;"></i> Auto-save enabled</div>
        <div class="d-flex gap-2">
          <button type="button" class="btn-secondary-soft" data-bs-dismiss="modal" onclick="closeQuotationModal()">Close</button>
          <button type="button" class="btn-primary-soft" onclick="exportQuotes()"><i class="fas fa-download"></i> Export</button>
        </div>
      </div>

    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════
     CONFIRM MODAL
═══════════════════════════════════════════ -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
    <div class="modal-content">
      <div class="confirm-body">
        <div class="confirm-icon-wrap" id="confirmIconWrap">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="confirm-title-text" id="confirmTitle">Confirm Action</div>
        <p class="confirm-desc" id="confirmText"></p>
      </div>
      <div class="confirm-footer">
        <button class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
        <button class="btn-confirm-danger" id="confirmBtn">Delete</button>
      </div>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════
     TRACKING MODAL
═══════════════════════════════════════════ -->
<div class="modal fade" id="trackingModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
    <div class="modal-content">
      <div class="tracking-header">
        <div class="tracking-header-title"><i class="fas fa-history" style="color:var(--text-muted);"></i> Activity Log</div>
        <button type="button" style="background:none;border:none;color:var(--text-muted);cursor:pointer;" data-bs-dismiss="modal" onclick="bootstrap.Modal.getInstance(document.getElementById('trackingModal'))?.hide()"><i class="fas fa-times"></i></button>
      </div>
      <div class="tracking-body">
        <div class="tracking-group-label" id="tracking_group_name"></div>
        <div class="tracking-item">
          <div class="tracking-dot"><i class="fas fa-file-alt"></i></div>
          <div class="tracking-content">
            <div class="tracking-ev-title">RFQ Generated</div>
            <div class="tracking-ev-meta">
              <span><i class="far fa-calendar-alt me-1"></i><span id="tracking_date"></span></span>
              <span><i class="far fa-clock me-1"></i>Recorded</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- ═══════════════════════════════════════════
     EDIT GROUP MODAL
═══════════════════════════════════════════ -->
<div class="modal fade" id="editGroupModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
    <div class="modal-content">
      <div class="eg-header">
        <div class="eg-header-title"><i class="fas fa-pen" style="opacity:.8;"></i> Edit Group</div>
        <button type="button" style="background:none;border:none;color:rgba(255,255,255,.8);cursor:pointer;font-size:.95rem;" data-bs-dismiss="modal" onclick="bootstrap.Modal.getInstance(document.getElementById('editGroupModal'))?.hide()"><i class="fas fa-times"></i></button>
      </div>
      <div class="eg-body">

        <div class="form-field-group">
          <label class="field-label">Group Name</label>
          <input type="text" class="field-input" id="edit_group_name" readonly>
        </div>

        <div class="form-field-group">
          <label class="field-label">Vendor Selection (Quotations)</label>
          <select class="field-select" id="edit_group_vendor">
            <option value="0">Not Assigned</option>
          </select>
        </div>

        <div class="form-field-group">
          <label class="field-label">Priority Level</label>
          <select class="field-select">
            <option>Normal</option>
            <option>High</option>
            <option>Urgent</option>
          </select>
        </div>

        <div class="form-field-group">
          <label class="field-label">Payment Status</label>
          <select class="field-select">
            <option>Pending</option>
            <option>Paid</option>
          </select>
        </div>

      </div>
      <div class="eg-footer">
        <button class="btn-save-full" onclick="saveGroupEdit()"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </div>
  </div>
</div>


<script>
const csrfToken = '{{ csrf_token() }}';
const firmsList = @json($firms);
let currentGroupItems = [];
let activeVendorColumns = [];

// ── Toast ──
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    const iconMap = {
        success: 'fa-check-circle',
        error:   'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle'
    };
    const colorMap = {
        success: '#2d9e6b',
        error:   '#e24c4c',
        warning: '#d97706'
    };
    toast.className = 'custom-toast';
    toast.innerHTML = `<i class="fas ${iconMap[type] || iconMap.success} toast-icon" style="color:${colorMap[type]||colorMap.success};"></i> ${message}`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('dismissing');
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}

// ── Confirm ──
function showConfirm(title, text, btnLabel = 'Delete') {
    return new Promise(resolve => {
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        document.getElementById('confirmTitle').innerText = title;
        document.getElementById('confirmText').innerText = text;
        document.getElementById('confirmBtn').innerText = btnLabel;

        const confirmBtn = document.getElementById('confirmBtn');
        const handleConfirm = () => {
            modal.hide();
            confirmBtn.removeEventListener('click', handleConfirm);
            resolve({ isConfirmed: true });
        };
        confirmBtn.onclick = handleConfirm;
        modal.show();
        document.getElementById('confirmModal').addEventListener('hidden.bs.modal', () => {
            resolve({ isConfirmed: false });
        }, { once: true });
    });
}

// ── Toggle Details ──
function toggleDetails(rfqId) {
    const row     = document.getElementById(`details_row_${rfqId}`);
    const icon    = document.getElementById(`icon_${rfqId}`);
    const content = document.getElementById(`details_content_${rfqId}`);

    if (row.classList.contains('d-none')) {
        row.classList.remove('d-none');
        icon.style.transform = 'rotate(180deg)';
        icon.style.color = 'var(--accent)';
        content.innerHTML = `<div class="text-center py-4" style="font-size:.82rem;color:var(--text-muted);"><span class="spin-sm me-2"></span> Loading items…</div>`;

        fetch(`{{ url('purnew/group') }}/${rfqId}/details`)
            .then(r => r.json())
            .then(data => {
                const total = data.reduce((s, i) => s + parseFloat(i.has_accepted_quote ? i.accepted_price : 0), 0);
                let html = `
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2" style="font-size:.82rem;font-weight:700;color:var(--text-primary);">
                        <i class="fas fa-list-ul" style="color:var(--accent);"></i> Items
                        <span style="background:var(--accent-soft);color:var(--accent);border-radius:20px;padding:2px 10px;font-size:.72rem;font-weight:700;">${data.length}</span>
                    </div>
                    <button class="icon-btn" onclick="toggleDetails(${rfqId})" title="Close"><i class="fas fa-times" style="font-size:.75rem;"></i></button>
                </div>`;

                if (data.length === 0) {
                    html += `<div class="empty-state"><div class="empty-icon"><i class="fas fa-box-open"></i></div><div class="empty-title">No items in this group</div></div>`;
                } else {
                    html += `<div class="table-responsive"><table class="sub-table">
                        <thead><tr>
                            <th class="ps-3">Item Description</th>
                            <th>Est. Price</th>
                            <th>Accepted Vendor</th>
                            <th>Final Price</th>
                        </tr></thead>
                        <tbody>
                        ${data.map(item => `
                            <tr>
                                <td class="ps-3 fw-bold" style="color:var(--text-primary);font-size:.82rem;">${item.title}</td>
                                <td style="font-family:'DM Mono',monospace;font-size:.8rem;">PKR ${parseFloat(item.est_price||0).toLocaleString()}</td>
                                <td>
                                ${item.available_quotes && item.available_quotes.length > 0 ? `
                                    <select class="vendor-inline-select" onchange="acceptItemQuoteBackend(${item.rfq_item_id}, this.value, ${rfqId})">
                                        <option value="0" ${!item.has_accepted_quote ? 'selected' : ''}>— Pending Vendor —</option>
                                        ${item.available_quotes.map(q => `<option value="${q.frm_id}" ${item.vendor_id == q.frm_id ? 'selected' : ''}>${q.vendor_name} (Rs. ${parseFloat(q.quoted_price).toLocaleString()})</option>`).join('')}
                                    </select>
                                ` : `<span style="font-size:.75rem;color:var(--text-muted);font-style:italic;">No quotes yet</span>`}
                                </td>
                                <td style="font-family:'DM Mono',monospace;font-weight:700;font-size:.82rem;color:${item.has_accepted_quote?'var(--success)':'var(--text-muted)'};">
                                    ${item.has_accepted_quote ? `PKR ${parseFloat(item.accepted_price).toLocaleString()}` : '—'}
                                </td>
                            </tr>`).join('')}
                        </tbody>
                    </table></div>`;
                }
                content.innerHTML = html;

                const totalEl = document.getElementById(`total_${rfqId}`);
                if (totalEl && total > 0) {
                    totalEl.textContent = `Rs. ${total.toLocaleString()}`;
                    totalEl.style.color = 'var(--accent)';
                    setTimeout(() => totalEl.style.color = '', 600);
                }
            })
            .catch(() => {
                content.innerHTML = `<div class="text-center py-4" style="color:var(--danger);font-size:.82rem;"><i class="fas fa-exclamation-triangle me-1"></i> Failed to load items.</div>`;
            });
    } else {
        row.classList.add('d-none');
        icon.style.transform = '';
        icon.style.color = '';
    }
}

function acceptItemQuoteBackend(rfqItemId, frmId, rfqId) {
    const fd = new FormData();
    fd.append('_token', csrfToken);
    fd.append('rfq_item_id', rfqItemId);
    fd.append('frm_id', frmId);

    fetch('{{ route("purnew.quotes.acceptItem") }}', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Vendor assignment updated', 'success');
                setTimeout(() => {
                    const row = document.getElementById(`details_row_${rfqId}`);
                    row.classList.add('d-none');
                    document.getElementById(`icon_${rfqId}`).style.transform = '';
                    document.getElementById(`icon_${rfqId}`).style.color = '';
                    toggleDetails(rfqId);
                }, 300);
            }
        });
}

// ── Edit Group Modal ──
let currentEditGroupId = null;
function openEditGroupModal(rfqId, rfqTitle) {
    currentEditGroupId = rfqId;
    document.getElementById('edit_group_name').value = rfqTitle;
    const vendorSelect = document.getElementById('edit_group_vendor');
    vendorSelect.innerHTML = '<option value="0">Loading…</option>';

    const egModal = new bootstrap.Modal(document.getElementById('editGroupModal'));
    egModal.show();

    fetch(`{{ url('purnew/quotes') }}/${rfqId}`)
        .then(r => r.json())
        .then(quotes => {
            vendorSelect.innerHTML = '<option value="0">Not Assigned</option>';
            if (quotes && quotes.length > 0) {
                const uniqueVendors = [...new Map(quotes.map(q => [q.frm_id, {id: q.frm_id, name: q.frm_name}])).values()];
                uniqueVendors.forEach(v => {
                    vendorSelect.innerHTML += `<option value="${v.id}">${v.name}</option>`;
                });
                const accepted = quotes.find(q => q.is_accepted);
                if (accepted) vendorSelect.value = accepted.frm_id;
            }
        })
        .catch(() => { vendorSelect.innerHTML = '<option value="0">Error loading</option>'; });
}

function saveGroupEdit() {
    const frmId = document.getElementById('edit_group_vendor').value;
    if (!currentEditGroupId) return;

    const fd = new FormData();
    fd.append('_token', csrfToken);
    fd.append('rfq_id', currentEditGroupId);
    fd.append('frm_id', frmId);

    fetch('{{ route("purnew.quotes.accept") }}', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Group vendor updated', 'success');
                const m = bootstrap.Modal.getInstance(document.getElementById('editGroupModal'));
                if (m) m.hide();
                setTimeout(() => {
                    const row = document.getElementById(`details_row_${currentEditGroupId}`);
                    if (row) {
                        row.classList.add('d-none');
                        document.getElementById(`icon_${currentEditGroupId}`).style.transform = '';
                        toggleDetails(currentEditGroupId);
                    }
                }, 400);
            }
        });
}

// ── Delete Group ──
function deleteGroup(rfqId, title) {
    showConfirm('Delete RFQ Group', `Are you sure you want to permanently delete "${title}"?\n\nThis will remove all items, quotations and associated data.`)
        .then(res => {
            if (res.isConfirmed) {
                showToast('Deleting…', 'warning');
                const fd = new FormData();
                fd.append('_token', csrfToken);
                fd.append('_method', 'DELETE');
                fetch(`{{ url('purnew/group') }}/${rfqId}`, { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Group deleted', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast('Error: ' + (data.message || 'Failed'), 'error');
                        }
                    });
            }
        });
}

// ── Tracking ──
function showTracking(rfqId, title, dateStr) {
    document.getElementById('tracking_group_name').innerText = `#${rfqId} — ${title}`;
    document.getElementById('tracking_date').innerText = dateStr;
    new bootstrap.Modal(document.getElementById('trackingModal')).show();
}

// ═══ Quotation Modal ═══
let quotationModalInstance = null;

function closeQuotationModal() {
    if (quotationModalInstance) { quotationModalInstance.hide(); return; }
    try { const m = bootstrap.Modal.getInstance(document.getElementById('quotationModal')); if (m) m.hide(); } catch(e) {}
}

function openQuotationModal(rfqId, rfqTitle) {
    document.getElementById('quote_group_id').innerText = rfqId;
    document.getElementById('quote_group_name').innerText = rfqTitle;

    const headerRow = document.getElementById('quote_header_row');
    const quoteBody = document.getElementById('quote_body');
    const totalRow  = document.getElementById('quote_total_row');

    headerRow.innerHTML = `<th class="sticky-left"><div class="item-desc-content fw-bold" style="color:var(--text-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;">Item / Description</div></th>`;
    quoteBody.innerHTML = `<tr><td colspan="20" class="text-center py-5" style="color:var(--text-muted);font-size:.82rem;"><span class="spin-sm me-2"></span> Loading quotation data…</td></tr>`;
    totalRow.innerHTML  = `<th class="sticky-left"><div class="item-desc-content fw-bold" style="color:var(--accent);font-size:.72rem;text-transform:uppercase;">TOTAL (INC. TAX)</div></th>`;

    activeVendorColumns = [];
    currentGroupItems   = [];

    if (!quotationModalInstance) {
        quotationModalInstance = new bootstrap.Modal(document.getElementById('quotationModal'), { backdrop: 'static' });
    }
    quotationModalInstance.show();

    Promise.all([
        fetch(`{{ url('purnew/quotes') }}/${rfqId}/items`).then(r => r.json()),
        fetch(`{{ url('purnew/quotes') }}/${rfqId}`).then(r => r.json())
    ]).then(([items, existingQuotes]) => {
        currentGroupItems = items || [];
        renderQuotationTable();
        showToast('Table ready — add vendors to start quoting', 'success');

        if (existingQuotes?.length > 0) {
            const vendorGroups = existingQuotes.reduce((acc, row) => {
                if (!acc[row.frm_id]) acc[row.frm_id] = [];
                acc[row.frm_id].push(row);
                return acc;
            }, {});

            Object.keys(vendorGroups).forEach(frmId => {
                const colId = addNewVendorColumn(frmId);
                vendorGroups[frmId].forEach(row => {
                    const tr = document.querySelector(`#quote_body tr[data-item-id="${row.rfq_item_id}"]`);
                    if (tr) {
                        const input = tr.querySelector(`td[data-col-id="${colId}"] .price-input`);
                        if (input) {
                            input.value = row.quoted_price;
                            input.classList.add('saved-ok');
                            input.dataset.lastSaved  = row.quoted_price;
                            input.dataset.lastVendor = frmId;
                            input.disabled = false;
                        }
                    }
                });
            });
        } else {
            setTimeout(() => addNewVendorColumn(), 300);
        }
        calculateTotals();
    }).catch(() => showToast('Failed to load quotation data', 'error'));
}

function renderQuotationTable() {
    const quoteBody = document.getElementById('quote_body');
    quoteBody.innerHTML = '';
    currentGroupItems.forEach(item => {
        const row = document.createElement('tr');
        row.setAttribute('data-item-id', item.rfq_item_id);
        row.style.borderBottom = '1px solid var(--border-light)';
        row.innerHTML = `
            <td class="sticky-left" style="background:var(--sidebar-bg);">
                <div class="d-flex justify-content-between align-items-center w-100 h-100 item-desc-content">
                    <span class="fw-bold" style="color:var(--text-primary);font-size:.82rem;">${item.title || item.item_name}</span>
                    <span style="font-size:.72rem;color:var(--text-muted);">Qty: ${item.qty || '—'}</span>
                </div>
            </td>`;
        activeVendorColumns.forEach(colId => row.appendChild(createVendorInputCell(item.rfq_item_id, colId)));
        quoteBody.appendChild(row);
    });
}

function createVendorInputCell(rfqItemId, colId) {
    const td = document.createElement('td');
    td.setAttribute('data-col-id', colId);
    td.className = 'price-input-cell';

    const input = document.createElement('input');
    input.type = 'number';
    input.className = 'price-input';
    input.placeholder = '0.00';
    input.step = '0.01';
    input.min = '0';

    const vendorSelect = document.getElementById(`vendor_select_${colId}`);
    if (!vendorSelect || !vendorSelect.value) input.disabled = true;

    const saveQuote = debounce(() => {
        const frmId = document.getElementById(`vendor_select_${colId}`)?.value;
        const price = input.value;
        const rfqId = document.getElementById('quote_group_id').innerText;
        if (!frmId || !price || (input.dataset.lastSaved === price && input.dataset.lastVendor === frmId)) return;

        const fd = new FormData();
        fd.append('_token', csrfToken);
        fd.append('rfq_id', rfqId);
        fd.append('rfq_item_id', rfqItemId);
        fd.append('frm_id', frmId);
        fd.append('quoted_price', price);

        fetch('{{ route("purnew.quotes.save") }}', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    input.classList.add('saved-ok');
                    input.dataset.lastSaved  = price;
                    input.dataset.lastVendor = frmId;
                    showToast(`Saved: Rs. ${parseFloat(price).toLocaleString()}`, 'success');
                    setTimeout(() => input.classList.remove('saved-ok'), 2500);
                }
            })
            .catch(() => {
                showToast('Failed to save quote', 'error');
                input.value = input.dataset.lastSaved || '';
            });
    }, 800);

    input.onblur   = saveQuote;
    input.onchange = saveQuote;
    input.oninput  = calculateTotals;

    td.appendChild(input);
    return td;
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

function addNewVendorColumn(initialFrmId = null) {
    const colId = Date.now() + Math.random().toString(36).substr(2, 9);
    activeVendorColumns.push(colId);

    const headerRow = document.getElementById('quote_header_row');
    const th = document.createElement('th');
    th.className = 'vendor-col-header';
    th.id = `header_col_${colId}`;
    th.innerHTML = `
        <div class="vendor-col-header-inner">
            <select class="vendor-header-select" id="vendor_select_${colId}">
                <option value="">Select Vendor…</option>
            </select>
            <button class="remove-col-btn" onclick="removeVendorColumn('${colId}')" title="Remove"><i class="fas fa-times" style="font-size:.7rem;"></i></button>
        </div>`;
    headerRow.appendChild(th);

    const select = th.querySelector('select');
    updateVendorOptions(select);
    if (initialFrmId) select.value = initialFrmId;

    document.querySelectorAll('#quote_body tr').forEach(row => {
        row.appendChild(createVendorInputCell(row.dataset.itemId, colId));
    });

    const totalTd = document.createElement('td');
    totalTd.setAttribute('data-col-id', colId);
    totalTd.className = 'border-end';
    totalTd.style.minWidth = '180px';
    totalTd.style.background = 'var(--surface)';
    totalTd.style.padding = '0';
    totalTd.innerHTML = `<div class="total-cell-inner"><div class="total-sub">PKR 0.00</div><div class="total-main">PKR 0.00</div></div>`;
    document.getElementById('quote_total_row').appendChild(totalTd);

    select.onchange = () => {
        const hasVendor = select.value !== '';
        document.querySelectorAll(`td[data-col-id="${colId}"] .price-input`).forEach(i => i.disabled = !hasVendor);
        document.querySelectorAll('[id^="vendor_select_"]').forEach(s => { if (s !== select) updateVendorOptions(s); });
        calculateTotals();
        if (hasVendor) showToast(`${select.options[select.selectedIndex].text} added`, 'success');
    };

    calculateTotals();
    return colId;
}

function updateVendorOptions(selectEl) {
    const currentVal  = selectEl.value;
    const selectedIds = Array.from(document.querySelectorAll('[id^="vendor_select_"]'))
                             .filter(s => s !== selectEl)
                             .map(s => s.value)
                             .filter(Boolean);

    selectEl.innerHTML = '<option value="">Select Vendor…</option>';
    firmsList.forEach(f => {
        if (!selectedIds.includes(f.frm_id.toString()) || f.frm_id.toString() === currentVal) {
            const opt = new Option(f.frm_name, f.frm_id);
            if (f.frm_id.toString() === currentVal) opt.selected = true;
            selectEl.add(opt);
        }
    });
}

function removeVendorColumn(colId) {
    const select = document.getElementById(`vendor_select_${colId}`);
    const frmId  = select?.value;

    if (frmId) {
        showConfirm('Remove Vendor', `Delete all quotes for "${select.options[select.selectedIndex].text}"?\n\nThis will permanently remove all prices for this vendor.`, 'Remove')
            .then(res => {
                if (res.isConfirmed) {
                    const fd = new FormData();
                    fd.append('_token', csrfToken);
                    fd.append('rfq_id', document.getElementById('quote_group_id').innerText);
                    fd.append('frm_id', frmId);
                    fetch('{{ route("purnew.quotes.deleteColumn") }}', { method: 'POST', body: fd })
                        .then(() => { showToast('Vendor column removed', 'success'); location.reload(); });
                }
            });
    } else {
        document.getElementById(`header_col_${colId}`)?.remove();
        document.querySelectorAll(`[data-col-id="${colId}"]`).forEach(el => el.remove());
        activeVendorColumns = activeVendorColumns.filter(id => id !== colId);
        calculateTotals();
        showToast('Empty column removed', 'success');
    }
}

function calculateTotals() {
    const taxPerc = parseFloat(document.getElementById('quote_tax_perc').value) || 0;

    activeVendorColumns.forEach(colId => {
        let subtotal   = 0;
        let validCount = 0;
        document.querySelectorAll(`td[data-col-id="${colId}"] .price-input`).forEach(input => {
            const p = parseFloat(input.value) || 0;
            subtotal += p;
            if (p > 0) validCount++;
        });

        const total = subtotal + (subtotal * taxPerc / 100);
        const cell  = document.querySelector(`#quote_total_row td[data-col-id="${colId}"]`);
        if (cell) {
            cell.innerHTML = `
                <div class="total-cell-inner">
                    <div class="total-sub">Sub: PKR ${subtotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}${validCount>0?` · ${validCount} items`:''}</div>
                    <div class="total-main">PKR ${total.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})}</div>
                    ${taxPerc>0?`<div class="total-tax">+${taxPerc}% tax included</div>`:''}
                </div>`;
        }
    });
}

function exportQuotes() {
    showToast('Export feature coming soon!', 'warning');
}
</script>
    </div>
  </div>
</div>
@endsection