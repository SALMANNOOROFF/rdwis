{{-- System Logs shared styles. Matches the project's existing admin theme:
     slate palette, #2563eb accent, 8px radius, #e2e8f0 borders, Inter. --}}
<style>
    .sl-head {
        background-color: #ffffff;
        color: #1e293b;
        padding: 14px 20px;
        border-bottom: 2px solid #e2e8f0;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }
    .sl-title {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 19px; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -0.2px;
    }
    .sl-sub { font-size: 12px; color: #64748b; margin: 2px 0 0; }

    .sl-card {
        border-radius: 8px; border: 1px solid #e2e8f0 !important;
        background: #fff; overflow: hidden;
    }

    /* ---- tiles (Level 1) ---- */
    .sl-tile {
        display: block; text-decoration: none; background: #fff;
        border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;
        transition: all .15s ease-in-out; height: 100%;
    }
    .sl-tile:hover {
        border-color: #2563eb; box-shadow: 0 4px 14px rgba(37,99,235,.12);
        transform: translateY(-1px); text-decoration: none;
    }
    .sl-tile-icon { font-size: 17px; color: #2563eb; width: 26px; }
    .sl-tile-label { font-size: 13.5px; font-weight: 700; color: #0f172a; letter-spacing: -0.1px; }
    .sl-tile-n { font-size: 25px; font-weight: 700; color: #0f172a; line-height: 1.1; }
    .sl-tile-meta { font-size: 11px; color: #64748b; }

    /* ---- table ---- */
    .sl-table { margin-bottom: 0; border-collapse: collapse; width: 100%; }
    .sl-table thead th {
        background-color: #f1f5f9; color: #334155; font-weight: 700; font-size: 12px;
        padding: 9px 11px; border-top: none; border-bottom: 1px solid #cbd5e1;
        letter-spacing: .2px; white-space: nowrap; position: sticky; top: 0; z-index: 2;
    }
    .sl-table tbody td {
        padding: 8px 11px; font-size: 12.5px; vertical-align: top;
        border-top: 1px solid #eef2f6; color: #1e293b;
    }
    .sl-table tbody tr:hover { background-color: #f8fafc; }
    .sl-table tbody tr.sl-crit  { background-color: #fef2f2; }
    .sl-table tbody tr.sl-crit:hover { background-color: #fee2e2; }
    .sl-table tbody tr.sl-warn  { background-color: #fffbeb; }

    .sl-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 11.5px; }
    .sl-null { color: #94a3b8; font-style: italic; font-size: 11.5px; }
    .sl-money { font-weight: 700; color: #0f172a; white-space: nowrap; }
    .sl-bool { font-weight: 600; }
    .sl-dtg  { white-space: nowrap; font-size: 12px; color: #334155; }
    .sl-who  { font-weight: 600; color: #0f172a; }
    .sl-desig{ font-size: 11px; color: #64748b; display: block; }

    /* ---- chips ---- */
    .sl-chip {
        display: inline-flex; align-items: center; gap: 4px; font-size: 11px;
        font-weight: 700; padding: 2px 8px; border-radius: 12px; white-space: nowrap;
        border: 1px solid transparent;
    }
    .sl-chip-success   { background:#dcfce7; color:#166534; border-color:#bbf7d0; }
    .sl-chip-primary   { background:#dbeafe; color:#1e40af; border-color:#bfdbfe; }
    .sl-chip-danger    { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
    .sl-chip-warning   { background:#fef3c7; color:#92400e; border-color:#fde68a; }
    .sl-chip-info      { background:#cffafe; color:#155e75; border-color:#a5f3fc; }
    .sl-chip-secondary { background:#e2e8f0; color:#334155; border-color:#cbd5e1; }
    .sl-chip-light     { background:#f8fafc; color:#475569; border-color:#e2e8f0; }

    /* ---- tabs (Level 3) ---- */
    .sl-tabs { display: flex; gap: 6px; flex-wrap: wrap; padding: 10px 20px 0; background:#fff; }
    .sl-tab {
        font-size: 12.5px; font-weight: 600; padding: 6px 15px; border-radius: 18px;
        text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        transition: all .2s; background: #fff; color: #475569; border: 1px solid #cbd5e1;
    }
    .sl-tab.active { background:#2563eb; color:#fff !important; border-color:#2563eb;
                     box-shadow: 0 2px 6px rgba(37,99,235,.3); }
    .sl-tab:not(.active):hover { background:#f1f5f9; color:#1e293b; text-decoration:none; }

    /* ---- timeline ---- */
    .sl-tl { position: relative; padding-left: 26px; }
    .sl-tl::before {
        content:''; position:absolute; left:8px; top:4px; bottom:4px; width:2px; background:#e2e8f0;
    }
    .sl-tl-item { position: relative; padding: 0 0 16px 0; }
    .sl-tl-dot {
        position:absolute; left:-25px; top:3px; width:12px; height:12px; border-radius:50%;
        background:#fff; border:2px solid #2563eb;
    }
    .sl-tl-dot.d-success { border-color:#16a34a; } .sl-tl-dot.d-danger { border-color:#dc2626; }
    .sl-tl-dot.d-warning { border-color:#d97706; } .sl-tl-dot.d-light  { border-color:#94a3b8; }
    .sl-tl-dot.d-info    { border-color:#0891b2; } .sl-tl-dot.d-secondary { border-color:#64748b; }
    .sl-tl-body {
        background:#fff; border:1px solid #e2e8f0; border-radius:7px; padding:10px 13px;
    }
    .sl-tl-sr { font-size: 10.5px; font-weight:700; color:#94a3b8; }
    .sl-remark {
        background:#f8fafc; border-left:3px solid #cbd5e1; padding:6px 10px; margin-top:7px;
        font-size:12.5px; color:#334155; border-radius:0 4px 4px 0; white-space: pre-wrap;
    }

    /* ---- diff ---- */
    .sl-diff-old { background:#fef2f2; color:#991b1b; padding:1px 5px; border-radius:3px; }
    .sl-diff-new { background:#f0fdf4; color:#166534; padding:1px 5px; border-radius:3px; }
    .sl-arrow { color:#94a3b8; margin:0 5px; }

    /* ---- filters ---- */
    .sl-filters { background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:10px 14px; }
    .sl-filters .form-control, .sl-filters .custom-select {
        font-size:12px; height:30px; padding:2px 8px; border-color:#cbd5e1; border-radius:5px;
    }
    .sl-filters label { font-size:10.5px; font-weight:700; color:#64748b; text-transform:uppercase;
                        letter-spacing:.3px; margin-bottom:2px; }

    .sl-readonly {
        background:#f0f9ff; border:1px solid #bae6fd; color:#075985; border-radius:6px;
        padding:7px 12px; font-size:12px; display:flex; align-items:center; gap:8px;
    }
    .sl-empty { text-align:center; padding:46px 20px; color:#94a3b8; }

    .sl-kv { width:100%; }
    .sl-kv td { padding:5px 10px; font-size:12.5px; border-top:1px solid #eef2f6; }
    .sl-kv td:first-child { color:#64748b; font-weight:600; width:190px; white-space:nowrap; }

    @media (max-width: 768px) {
        .sl-table thead th, .sl-table tbody td { padding: 6px 7px; font-size: 11.5px; }
        .sl-kv td:first-child { width: 120px; }
    }
</style>
