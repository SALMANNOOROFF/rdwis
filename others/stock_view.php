<?php
require_once __DIR__ . '/session_config.php';
start_inventory_session_if_exists();
include 'inv_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$vendor_query = "SELECT * FROM vendors ORDER BY vendor_name ASC";
$vendors_result = $conn->query($vendor_query);
$vendors = [];
while($v = $vendors_result->fetch_assoc()) { $vendors[] = $v; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Admin | Inventory Control</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --admin-dark: #0f172a; 
            --accent: #6366f1; /* Indigo accent from image */
            --glass: rgba(255, 255, 255, 0.9);
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
        }
        
        body { 
            background-color: #ffffff; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--text-primary); 
        }

        /* Modern Navbar */
        .nav-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 0;
            margin-bottom: 2rem;
        }

        /* Filter Section Redesign */
        .filter-tabs {
            display: inline-flex;
            background: #fff;
            border: 1px solid var(--accent);
            border-radius: 6px;
            overflow: hidden;
        }
        .filter-tab {
            padding: 6px 16px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--accent);
            background: #fff;
            border: none;
            border-right: 1px solid var(--accent);
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-tab:last-child { border-right: none; }
        .filter-tab.active, .filter-tab:hover {
            background: #f5f3ff; /* Light indigo tint */
        }
        .filter-tab.active {
            font-weight: 600;
        }

        .search-box {
            position: relative;
        }
        .search-box input {
            padding-left: 36px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            font-size: 0.9rem;
        }
        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        /* Table Redesign */
        .table-card { 
            background: white; 
            border: none;
            box-shadow: none;
            margin-top: 20px;
        }

        .table thead th { 
            background: #fff; 
            color: #1e293b; 
            font-weight: 700;
            font-size: 0.85rem; 
            border-bottom: 1px solid #f1f5f9;
            padding: 16px 12px;
            white-space: nowrap;
        }
        
        .table tbody td { 
            padding: 16px 12px; 
            vertical-align: middle;
            border-bottom: 1px solid #f8fafc;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .table tbody tr:hover td {
            background-color: #f8fafc;
        }

        /* Priority Badges */
        .badge-priority {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
            color: white;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }
        .badge-urgent { background-color: #ef4444; } /* Red */
        .badge-normal { background-color: #22c55e; } /* Green */
        .badge-long-term { background-color: #f97316; } /* Orange */
        .badge-pen{background-color: #353535;}

        /* Status Badges - New Styling */
        .badge-status {
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            display: inline-block;
            min-width: 110px;
            text-align: center;
            border: 1px solid transparent;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        /* Status Colors - No Red */
        .status-pending { background-color: #f1f5f9; color: #475569; border-color: #e2e8f0; } /* Slate/Gray */
        .status-rfq { background-color: #e0f2fe; color: #0369a1; border-color: #bae6fd; } /* Blue */
        .status-quote { background-color: #fdf2f8; color: #be185d; border-color: #fbcfe8; } /* Pink */
        .status-order { background-color: #fff7ed; color: #c2410c; border-color: #ffedd5; } /* Orange */
        .status-initiated { background-color: #f5f3ff; color: #6d28d9; border-color: #ddd6fe; } /* Purple */
        .status-approved { background-color: #ecfdf5; color: #047857; border-color: #d1fae5; } /* Emerald */
        .status-mtss { background-color: #eff6ff; color: #1d4ed8; border-color: #dbeafe; } /* Blue/Indigo */
        .status-po { background-color: #f0fdf4; color: #15803d; border-color: #dcfce7; } /* Green */
        .status-submitted { background-color: #faf5ff; color: #7e22ce; border-color: #f3e8ff; } /* Violet */
        .status-check { background-color: #f0fdfa; color: #0f766e; border-color: #ccfbf1; } /* Teal */
        .status-recieved { background-color: #f0f9ff; color: #0369a1; border-color: #e0f2fe; } /* Sky Blue */
        .status-partially-recieved { background-color: #fef9c3; color: #854d0e; border-color: #fef08a; } /* Yellow */
        .status-all-recieved { background-color: #dcfce7; color: #166534; border-color: #bbf7d0; } /* Green */
        .status-default { background-color: #f8fafc; color: #64748b; border-color: #f1f5f9; }
        /* Checkbox Styling */
        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #cbd5e1;
            border-radius: 4px;
        }
        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        /* Action Buttons */
        .btn-action-primary {
            background-color: #5b21b6; /* Deep purple from image */
            color: white;
            border-radius: 6px;
            font-weight: 600;
            padding: 8px 16px;
            border: none;
        }
        .btn-action-secondary {
            background-color: #fff;
            color: #5b21b6;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-weight: 600;
            padding: 8px 16px;
        }

        /* Quotation Modal Specific Styles */
        #quotationModal .modal-content {
            background: linear-gradient(to right, #f8fafc 250px, #ffffff 250px);
            position: relative;
        }
        #quotationModal .modal-content::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: 250px;
            width: 1px;
            background-color: #cbd5e1;
            z-index: 20; /* Above everything */
            pointer-events: none;
        }
        #quotationModal .modal-header, 
        #quotationModal .modal-footer {
            /* background: transparent !important; */
            z-index: 21;
        }
        .quote-table-container {
            height: 100%;
            display: flex;
            flex-direction: column;
            background: #f8f9fa !important;
        }
        #quotationTable {
            background: transparent !important;
            border-collapse: separate;
            border-spacing: 0;
            width: max-content; /* Packed columns from left to right */
            /* min-width: 100%; Ensure it fills at least the modal width */
            table-layout: fixed; /* Respect fixed widths */
        }
        #quotationTable th.sticky-left, 
        #quotationTable td.sticky-left {
            position: sticky;
            left: 0;
            z-index: 5;
            background-color: #f8fafc;
            border-right: none !important; /* Managed by modal pseudo-element */
            height: inherit;
            width: 250px;
            min-width: 250px;
            padding: 0 !important;
        }
        .item-desc-content {
            padding: 8px 16px; /* Reduced from 16px 24px */
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
            background: transparent !important;
            font-size: 0.85rem;
        }

        /* Compact Timeline View Styles */
        .timeline {
            position: relative;
            padding: 10px 0;
            list-style: none;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 24px;
            width: 3px;
            background: linear-gradient(to bottom, var(--primary), #e2e8f0);
            border-radius: 3px;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 15px;
            padding-left: 50px;
        }
        .timeline-marker {
            position: absolute;
            left: 17px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            margin-top: 25px;
            background: #fff;
            border: 3px solid #cbd5e1;
            z-index: 1;
            transition: all 0.3s ease;
        }
        .timeline-content {
            background: #ffffff;
            padding: 10px 15px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease-in-out;
            position: relative;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .timeline-content:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .timeline-status {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.95rem;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .timeline-status i {
            font-size: 1.1rem;
        }
        .timeline-date {
            font-size: 0.75rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-top: 5px;
            border-top: 1px dashed #f1f5f9;
        }
        /* Latest status highlighting */
        .timeline-item:first-child .timeline-marker {
            background: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            width: 18px;
            height: 18px;
            left: 16px;
        }
        .timeline-item:first-child .timeline-content {
            border-left: 4px solid var(--primary);
            background: #f0f7ff;
        }
        /* Status specific colors for the latest one */
        .latest-status-rfq { border-left-color: #0ea5e9 !important; background-color: #f0f9ff !important; }
        .latest-status-order { border-left-color: #f97316 !important; background-color: #fff7ed !important; }
        .latest-status-recieved { border-left-color: #10b981 !important; background-color: #ecfdf5 !important; }
        .latest-status-case { border-left-color: #8b5cf6 !important; background-color: #f5f3ff !important; }

        .timeline-icon-box {
            width: 28px;
            height: 28px;
            background: rgba(59, 130, 246, 0.1);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            flex-shrink: 0;
            font-size: 0.9rem;
        }
        #quotationTable tr {
            height: 1px; /* Table height trick for 100% children */
        }
        #quotationTable td {
            height: inherit;
            padding: 4px 0 !important; /* Reduced vertical padding */
            border-bottom: 1px solid #f1f5f9;
        }
        #quotationTable thead th {
            background-color: transparent !important;
            border-bottom: 1px solid #cbd5e1 !important;
            padding: 8px !important; /* Reduced from 12px */
            height: 60px; /* Reduced from 70px */
        }
        #quotationTable thead th.sticky-left {
            z-index: 11;
            background-color: #f8fafc !important;
            padding-left: 0 !important; 
        }
        #quotationTable tfoot {
            position: sticky;
            bottom: 0;
            z-index: 12; /* Above tbody */
        }
        #quotationTable tfoot th,
        #quotationTable tfoot td {
            background-color: #f8fafc !important;
            border-top: 2px solid #cbd5e1 !important;
            border-bottom: none !important;
            position: sticky;
            bottom: 0;
        }
        #quotationTable tfoot th.sticky-left {
            z-index: 13; /* Above other footer cells */
        }
        .price-input:focus {
            background-color: #fff !important;
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25) !important;
            outline: none;
        }
        .price-input {
            border: 1px solid #ccc !important;
            border-radius: 4px !important;
            box-shadow: none !important;
            width: 95% !important;   /* Increased width back slightly */
            margin: auto;           /* Centering */
            display: block;
            transition: all 0.2s ease;
            font-size: 0.85rem !important; /* Reduced font size */
            padding: 4px 8px !important; /* Reduced padding */
        }
        .price-input:disabled {
            background-color: #f1f5f9 !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }
        .price-input::placeholder {
            color: #999 !important;
            font-style: italic !important;
            opacity: 1;
        }
        .price-input.is-valid {
            background-color: #d4edda !important;
            transition: background-color 0.5s ease;
        }

        .vendor-separator {
            border-right: 1px solid #e2e8f0;
        }

        /* Status Pills */
        .status-pill { 
            padding: 5px 12px; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 0.72rem; 
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .badge-process { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .badge-received { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }

        /* Dropdown Styling */
        .dropdown-menu {
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border-radius: 12px;
            padding: 8px;
        }
        .dropdown-item {
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .dropdown-item:hover { background-color: #f1f5f9; color: var(--accent); }
        .dropdown-item.text-danger:hover { background-color: #fef2f2; }
        .table-card .dropdown-menu { z-index: 2000; }

        /* Consolidate Bar */
        #consolidateBar { 
            position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); 
            background: var(--admin-dark); color: white; padding: 12px 24px; 
            border-radius: 100px; display: none; z-index: 1000; 
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); align-items: center; gap: 20px; 
        }

        .group-id-badge { background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-weight: 700; font-size: 0.8rem; }
        
        /* Item Moved States */
        .item-moved { opacity: 0.6; background-color: #f8fafc; }
        .moved-badge { font-size: 0.65rem; padding: 2px 8px; border-radius: 4px; background: #e2e8f0; color: #475569; }
        
        /* Row dropdown UX */
        .row-toggle-btn i { transition: transform .2s ease; }
        .row-toggle-btn.open i { transform: rotate(180deg); }
        
        /* Expanded items card */
        .items-card { 
            background: #fff; 
            border: 1px solid var(--border-color); 
            border-radius: 12px; 
            padding: 14px; 
            max-height: 50vh;
            overflow-y: auto;
            box-shadow: 0 12px 30px -18px rgba(0,0,0,0.2); 
        }
        .items-header .badge-soft {
            background: #eff6ff; 
            color: #1d4ed8; 
            border: 1px solid #dbeafe; 
            font-weight: 700; 
            font-size: 0.72rem;
        }
        .items-table thead { background: #f8fafc; }
        .items-table th { font-size: 0.72rem; letter-spacing: 0.02em; color: #64748b; text-transform: uppercase; }
        .items-table td { font-size: 0.9rem; }
        .collapse { visibility: visible !important; }
        .collapsing { overflow: visible !important; }

        /* Priority Colors for Rows */
        .speed-Urgent td { background-color: #f8d7da !important; }
        .speed-Normal td { background-color: #d4edda !important; }
        .speed-Long-Term td { background-color: #fff3cd !important; }

        /* Adjust text colors for priority rows */
        .speed-Urgent td, .speed-Urgent .text-dark, .speed-Urgent .text-muted, .speed-Urgent i { color: #721c24 !important; }
        .speed-Normal td, .speed-Normal .text-dark, .speed-Normal .text-muted, .speed-Normal i { color: #155724 !important; }
        .speed-Long-Term td, .speed-Long-Term .text-dark, .speed-Long-Term .text-muted, .speed-Long-Term i { color: #856404 !important; }

        /* Handle badges inside priority rows */
        .speed-Urgent .badge.bg-light { background-color: rgba(0,0,0,0.05) !important; color: #721c24 !important; }
        .speed-Normal .badge.bg-light { background-color: rgba(0,0,0,0.05) !important; color: #155724 !important; }
        .speed-Long-Term .badge.bg-light { background-color: rgba(0,0,0,0.05) !important; color: #856404 !important; }

        /* Override specific text colors in priority rows */
        .speed-Urgent .text-danger, .speed-Urgent .text-muted { color: #721c24 !important; }
        .speed-Normal .text-danger, .speed-Normal .text-muted { color: #155724 !important; }
        .speed-Long-Term .text-danger, .speed-Long-Term .text-muted { color: #856404 !important; }

        /* Hover effect for priority rows */
        .group-row.speed-Urgent:hover td { background-color: #f5c6cb !important; }
        .group-row.speed-Normal:hover td { background-color: #c3e6cb !important; }
        .group-row.speed-Long-Term:hover td { background-color: #ffeeba !important; }

        /* Expanded States & Visual Hierarchy */
        .group-row { transition: all 0.2s ease; border-left: 4px solid transparent; margin-bottom: 10px; border-bottom: 4px solid #f1f5f9; }
        .group-row.is-expanded { 
            border-left-width: 6px;
            box-shadow: 0 4px 15px -5px rgba(0,0,0,0.1);
            z-index: 5;
            position: relative;
            border-bottom: none !important;
        }
        
        .speed-Urgent.is-expanded { border-left-color: #dc3545 !important; }
        .speed-Normal.is-expanded { border-left-color: #198754 !important; }
        .speed-Long-Term.is-expanded { border-left-color: #ffc107 !important; }

        .items-expanded-row { transition: all 0.3s ease; }
        .items-expanded-row td { 
            padding: 0 !important; 
            border: none !important;
            border-left: 6px solid transparent;
        }

        /* Connecting the left border for the expanded area */
        .group-row.is-expanded + .items-expanded-row td {
            border-left-style: solid;
        }
        .group-row.speed-Urgent.is-expanded + .items-expanded-row td { border-left-color: #dc3545 !important; }
        .group-row.speed-Normal.is-expanded + .items-expanded-row td { border-left-color: #198754 !important; }
        .group-row.speed-Long-Term.is-expanded + .items-expanded-row td { border-left-color: #ffc107 !important; }

        .items-card { 
            margin: 10px 20px 20px 20px;
            background: #fff; 
            border: 1px solid var(--border-color); 
            border-radius: 12px; 
            padding: 20px; 
            max-height: 55vh;
            overflow-y: auto;
            box-shadow: 0 10px 25px -10px rgba(0,0,0,0.1); 
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .row-toggle-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .is-expanded .row-toggle-btn {
            background-color: rgba(0,0,0,0.1) !important;
            transform: scale(1.05);
        }

        /* Toast Styling */
        .toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 3000; }
        .custom-toast { 
            background: var(--admin-dark); 
            color: white; 
            padding: 12px 24px; 
            border-radius: 12px; 
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.3s ease-out;
            margin-top: 10px;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<?php if (isset($_GET['msg'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($_GET['msg'] == 'locked_error'): ?>
                showAlert('Action Denied!', 'This record is locked. Contact Admin.', 'error');
            <?php elseif ($_GET['msg'] == 'updated'): ?>
                showToast('Success! Procurement record updated.');
            <?php endif; ?>
            
            // Clean URL after showing message
            window.history.replaceState({}, document.title, window.location.pathname);
        });
    </script>
<?php endif; ?>

<?php include 'ui_header.php'; ?>

<div class="page-wrap">
    <div class="row align-items-end mb-4 mt-4">
        <div class="col-md-7">
            <h3 class="page-title mb-1">Procurement Control</h3>
            <p class="subtitle mb-0">Monitor Items, track vendor performance and consolidate demands.</p>
        </div>
        <div class="col-md-5 text-md-end mt-3">
            <button class="btn-action-secondary me-2" id="deselectAllBtn" style="display: none;">Deselect all</button>
            <button class="btn-action-primary" id="consolidateBtn">Consolidate Demands</button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="filter-tabs">
            <button class="filter-tab active" data-status="">All</button>
            <button class="filter-tab" data-status="Pending">Pending</button>
            <button class="filter-tab" data-status="RFQ Generated">RFQ</button>
            <button class="filter-tab" data-status="Quotation Awaited">Quote Awaited</button>
            <button class="filter-tab" data-status="Order Placed">Order</button>
            <button class="filter-tab" data-status="Case Initiated">Initiated</button>
            <button class="filter-tab" data-status="Case Approved">Approved</button>
            <button class="filter-tab" data-status="Mailed to MTSS">MTSS</button>
            <button class="filter-tab" data-status="PO Issued">PO Issued</button>
            <button class="filter-tab" data-status="Document Submitted">Submitted</button>
            <button class="filter-tab" data-status="Check Ready">Check Ready</button>
            <button class="filter-tab" data-status="Recieved">Recieved</button>
        </div>
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="globalSearch" class="form-control" placeholder="Search...">
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="demandTable">
                <thead>
                    <tr>
                        <th class="ps-4" width="40"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                        <th>Priority</th>
                        <th>Case #</th>
                        <th>Case Status</th>
                        <th>Groups Name</th>
                        <th>Vendor</th>
                        <th>Status</th>
                        <th>Items Status</th>
                        <th>Last Updated</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php
                    $sql = "SELECT 
    dt.*, 
    dt.group_name AS g_name,
    v.vendor_name,
    (SELECT COUNT(*) FROM purchase_demands WHERE group_id = dt.group_id) AS item_count,
    (SELECT COUNT(*) FROM purchase_demands WHERE group_id = dt.group_id AND status = 'Recieved') AS received_count,
    (SELECT status FROM purchase_demands WHERE group_id = dt.group_id AND status != 'Recieved' LIMIT 1) AS non_received_status,
    (SELECT SUM(vit.vendor_price * pd.quantity) 
     FROM vendors_items_track_data vit 
     JOIN purchase_demands pd ON vit.item_id = pd.item_id AND vit.items_group_id = pd.group_id 
     WHERE vit.items_group_id = dt.group_id AND vit.vendor_id = dt.vendor_id) AS vendor_subtotal
FROM demand_tracking dt
LEFT JOIN vendors v ON dt.vendor_id = v.id
WHERE dt.process_status != 'Pending'
ORDER BY 
    CASE 
        WHEN dt.tracking_speed = 'Urgent' THEN 1
        WHEN dt.tracking_speed = 'Normal' THEN 2
        WHEN dt.tracking_speed = 'Long Term' THEN 3
        ELSE 4
    END ASC,
    dt.created_at DESC;";
                    $result = $conn->query($sql);
                    while($row = $result->fetch_assoc()):
                        $status = !empty($row['process_status']) ? $row['process_status'] : 'Pending';
                        
                        // Status styling logic - No Red
                        $status_class = 'status-default';
                        switch ($status) {
                            case 'Pending': $status_class = 'status-pending'; break;
                            case 'RFQ Generated': $status_class = 'status-rfq'; break;
                            case 'Quotation Awaited': $status_class = 'status-quote'; break;
                            case 'Order Placed': $status_class = 'status-order'; break;
                            case 'Case Initiated': $status_class = 'status-initiated'; break;
                            case 'Case Approved': $status_class = 'status-approved'; break;
                            case 'Mailed to MTSS': $status_class = 'status-mtss'; break;
                            case 'PO Issued': $status_class = 'status-po'; break;
                            case 'Document Submitted': $status_class = 'status-submitted'; break;
                            case 'Check Ready': $status_class = 'status-check'; break;
                            case 'Recieved': $status_class = 'status-recieved'; break;
                        }

                        // Items Status Logic
                        $item_count = (int)$row['item_count'];
                        $received_count = (int)$row['received_count'];
                        $items_status = $row['non_received_status'] ?? 'Recieved';
                        
                        if ($item_count > 0) {
                            if ($received_count == $item_count) {
                                $items_status = 'All Recieved';
                            } elseif ($received_count >= ($item_count * 0.2)) {
                                $items_status = 'Partially Recieved';
                            }
                        }
                        
                        $items_status_class = 'status-default';
                        switch ($items_status) {
                            case 'Pending': $items_status_class = 'status-pending'; break;
                            case 'Order Placed': $items_status_class = 'status-order'; break;
                            case 'Partially Recieved': $items_status_class = 'status-partially-recieved'; break;
                            case 'All Recieved': $items_status_class = 'status-all-recieved'; break;
                            case 'Recieved': $items_status_class = 'status-all-recieved'; break;
                        }

                        // Case Status Logic
                        $case_status = $row['case_status'] ?? 'Pending';
                        $case_status_class = 'status-default';
                        switch ($case_status) {
                            case 'Pending': $case_status_class = 'status-pending'; break;
                            case 'Case Initiated': $case_status_class = 'status-initiated'; break;
                            case 'Case Approved': $case_status_class = 'status-approved'; break;
                            case 'Mailed to MTSS': $case_status_class = 'status-mtss'; break;
                            case 'PO Issued': $case_status_class = 'status-po'; break;
                            case 'Document Submitted': $case_status_class = 'status-submitted'; break;
                            case 'Check Ready': $case_status_class = 'status-check'; break;
                        }

                        $priority = (!empty($row['tracking_speed'])) ? $row['tracking_speed'] : 'Normal';
                        $priority_class = 'badge-normal';
                        if ($priority == 'Fast Track' || $priority == 'Urgent') {
                            $priority_class = 'badge-urgent';
                            $priority = 'Urgent'; // Normalize display text
                        } else if ($priority == 'Long Term') {
                            $priority_class = 'badge-long-term';
                        }
                    ?>
                    <tr class="group-row" data-group-id="<?= $row['group_id'] ?>" data-status="<?= htmlspecialchars($status) ?>">
                        <td class="ps-4"><input type="checkbox" class="group-checkbox form-check-input" value="<?= $row['group_id'] ?>"></td>
                        <td><span class="badge-priority <?= $priority_class ?>"><?= $priority ?></span></td>
                        <td>
                            <span class="text-primary fw-bold small" style="<?= (empty($row['case_number']) || $row['case_number'] == '0000') ? "color:gray !important;" : '' ?>"><?= (!empty($row['case_number']) && $row['case_number'] !== '0000') ? $row['case_number'] : 'N/A' ?></span>
                        </td>
                        <td><span class="badge-status <?= $case_status_class ?>"><?= $case_status ?></span></td>
                        <td>
                            <div class="fw-bold text-dark"><?= $row['g_name'] ?></div>
                            <div class="text-muted small"><?= $row['item_count'] ?> items</div>
                        </td>
                        <td>
                            <?php 
                            $v_name = $row['vendor_name'] ?? 'N/A';
                            $is_assigned = !in_array(strtolower($v_name), ['not assigned', 'n/a', '']);
                            $v_style = $is_assigned ? 'color: var(--accent);' : '';
                            
                            $total_display = '';
                            if ($is_assigned && isset($row['vendor_subtotal']) && $row['vendor_subtotal'] > 0) {
                                $subtotal = (float)$row['vendor_subtotal'];
                                $tex_perc = (float)($row['tex_perc'] ?? 0);
                                $total_with_tax = $subtotal + ($subtotal * ($tex_perc / 100));
                                $total_display = ' <span class="fw-bold" style="color: var(--accent); font-size: 0.8rem;">(PKR ' . number_format($total_with_tax, 0) . ')</span>';
                            }
                            ?>
                            <span class="fw-500" style="<?= $v_style ?>"><?= $v_name ?></span><?= $total_display ?>
                        </td>
                        <td><span class="badge-status <?= $status_class ?> process-status-badge"><?= $status ?></span></td>
                        <td><span class="badge-status <?= $items_status_class ?>"><?= $items_status ?></span></td>
                        <td>
                            <div class="text-dark small fw-500"><?= date('d M Y', strtotime($row['updated_at'] ?? $row['created_at'])) ?></div>
                            <div class="text-muted" style="font-size: 0.7rem;"><?= date('H:i', strtotime($row['updated_at'] ?? $row['created_at'])) ?></div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex align-items-center gap-2">
                                <button class="btn btn-light btn-sm rounded-3 border-0 row-toggle-btn" type="button" onclick="toggleGroupRow('<?= $row['group_id'] ?>')">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                                <button class="btn btn-light btn-sm rounded-3 border-0" type="button" 
                                        onclick="openTimelineModal('<?= $row['group_id'] ?>', '<?= addslashes($row['g_name'] ?? '') ?>')" title="View Timeline">
                                    <i class="bi bi-clock-history"></i>
                                </button>
                                <button class="btn btn-light btn-sm rounded-3 border-0" type="button" onclick="openQuotationModal('<?= $row['group_id'] ?>', '<?= addslashes($row['g_name']) ?>')" title="Add Quotation">
                                    <i class="bi bi-file-earmark-plus"></i>
                                </button>
                                <button class="btn btn-light btn-sm rounded-3 border-0" type="button" 
                                        onclick="openEditGroupModal('<?= $row['group_id'] ?>', '<?= addslashes($row['g_name'] ?? '') ?>', '<?= $status ?>', '<?= $priority ?>', '<?= addslashes($row['case_number'] ?? '') ?>', '<?= addslashes($row['vendor_name'] ?? 'Not Assigned') ?>', '<?= $row['paid_amount'] ?? 0 ?>', '<?= $row['payment_status'] ?? 'Pending' ?>', '<?= $row['case_status'] ?? 'Pending' ?>')" title="Edit Group">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-light btn-sm rounded-3 border-0 text-danger" type="button" 
                                        onclick="openDeleteGroupModal('<?= $row['group_id'] ?>', '<?= addslashes($row['g_name'] ?? '') ?>')" title="Delete Group">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr class="items-expanded-row">
                        <td colspan="10" class="bg-white p-0">
                            <div id="items-content-<?= $row['group_id'] ?>" class="collapse">
                                <div class="items-card">
                                    <div class="d-flex justify-content-between align-items-center items-header mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-box-seam text-primary"></i>
                                            <span class="fw-bold">Items in this group</span>
                                            <span id="items-meta-<?= $row['group_id'] ?>" class="badge badge-soft">Loading…</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <div id="group-action-container-<?= $row['group_id'] ?>">
                                                <button class="btn btn-sm btn-success fw-bold px-3 rounded-pill shadow-sm" onclick="placeGroupOrder('<?= $row['group_id'] ?>')">
                                                    <i class="bi bi-cart-check me-1"></i>Place Order
                                                </button>
                                            </div>
                                            <button class="btn btn-sm btn-outline-secondary" aria-label="Close" onclick="toggleGroupRow('<?= $row['group_id'] ?>')"><i class="bi bi-x"></i></button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0 items-table table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Item Description</th>
                                                    <th class="text-center">Qty</th>
                                                    <th>Vendor</th>
                                                    <th id="price-header-<?= $row['group_id'] ?>">Est. Price</th>
                                                    <th class="text-end">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="items-body-<?= $row['group_id'] ?>"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="consolidateBar" style="display:none;">
    <span id="selectedCountText" class="fw-bold">0 Groups Selected</span>
    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="openConsolidationModal()">Consolidate Now</button>
</div>

<!-- Quotation Modal -->
<div class="modal fade" id="quotationModal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content border-0">
            <div class="modal-header bg-white border-0 p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-2 rounded-3">
                        <i class="bi bi-file-earmark-plus text-primary fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 mb-0">Add Quotations</h5>
                        <p class="text-muted small mb-0">Group: <span id="quote_group_name" class="fw-bold text-dark"></span> | ID: <span id="quote_group_id" class="fw-bold text-primary"></span></p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 ms-4 border-start ps-4">
                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-bold text-muted mb-0">TAX TYPE:</label>
                        <select id="quote_tax_type" class="form-select form-select-sm fw-bold border-primary" style="width: 80px;" onchange="updateTaxInfo()">
                            <option value="gst">GST</option>
                            <option value="sst">SST</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-bold text-muted mb-0">TAX %:</label>
                        <input type="number" id="quote_tax_perc" class="form-control form-control-sm fw-bold border-primary" style="width: 70px;" value="18" oninput="updateTaxInfo()">
                    </div>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-primary rounded-3 px-4 fw-bold shadow-sm" onclick="addNewVendorColumn()">
                        <i class="bi bi-person-plus-fill me-2"></i>Add Vendor Column
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive h-100 quote-table-container">
                    <table class="table align-middle mb-0" id="quotationTable">
                        <thead class="bg-light sticky-top" style="z-index: 10;">
                            <tr id="quote_header_row">
                                <th style="width: 250px; min-width: 250px;" class="bg-light">Item Description</th>
                                <!-- Vendor columns will be injected here -->
                            </tr>
                        </thead>
                        <tbody id="quote_body">
                            <!-- Items will be injected here -->
                        </tbody>
                        <tfoot id="quote_footer">
                            <tr id="quote_total_row">
                                <th class="bg-light sticky-left"><div class="item-desc-content fw-bold text-dark">TOTAL (INC. TAX)</div></th>
                                <!-- Total cells will be injected here -->
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-top p-3">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i> Data is saved automatically as you type.
                    </div>
                    <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Vendor Modal -->
<div class="modal fade" id="assignVendorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-med">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white p-3">
                <h6 class="modal-title fw-bold">Assign Vendor</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">ITEM</label>
                    <div id="assign_item_name" class="fw-bold text-dark mb-2"></div>
                    <input type="hidden" id="assign_pd_id">
                    <input type="hidden" id="assign_item_id">
                    <input type="hidden" id="assign_group_id">
                </div>
                <div>
                    <label class="form-label fw-bold small text-muted">SELECT QUOTED VENDOR</label>
                    <select id="assign_vendor_select" class="form-select border-primary fw-bold">
                        <option value="">Loading...</option>
                    </select>
                    <div id="no_quotation_msg" class="text-danger small mt-2" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> No quotation prices set for this item.
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-3">
                <button type="button" class="btn btn-primary w-100 rounded-3 fw-bold" onclick="saveVendorAssignment()">Save Assignment</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Vendor Modal (Inner) -->
<div class="modal fade" id="addVendorModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Add New Vendor</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="new_vendor_name_input" class="form-control rounded-3" placeholder="Vendor Name">
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-primary w-100 rounded-3" onclick="saveNewVendor()">Save Vendor</button>
            </div>
        </div>
    </div>
</div>

<!-- Timeline Modal -->
<div class="modal fade" id="timelineModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white p-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-opacity-20 p-2 rounded-3">
                        <i class="bi bi-clock-history fs-5" style="color:white;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0">Status Tracking History</h6>
                        <p class="small mb-0 opacity-75" id="timelineGroupName" style="font-size: 0.75rem;"></p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" style="max-height: 60vh; overflow-y: auto;">
                <div id="timelineContainer">
                    <!-- Timeline will be injected here -->
                </div>
            </div>
            <div class="modal-footer border-0 p-2 text-center justify-content-center">
                <p class="text-muted" style="font-size: 0.7rem;"><i class="bi bi-info-circle me-1"></i> Records sorted newest to oldest</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="itemsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 1000px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-white border-bottom p-4">
                <div>
                    <h5 class="modal-title fw-800 mb-0">📦 Group Inventory</h5>
                    <p class="text-muted small mb-0">ID: <span id="view_group_id_text" class="fw-bold text-primary"></span> | Name: <span id="view_group_name_text" class="fw-bold text-dark"></span></p>
                </div>
                <div class="d-flex gap-2" style="align-items: center;">
                    <button type="button" class="btn btn-success rounded-3 px-4 fw-bold" id="modalPrintBtn">
                        <i class="bi bi-printer me-2"></i> Print List
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <!-- <div class="alert alert-light border small mb-3">
                    <i class="bi bi-info-circle me-1"></i> Faded items have been transferred to other consolidated groups.
                </div> -->
                <div class="input-group input-group-sm mb-2" >
                    <span class="input-group-text bg-light border-0" style="background-color: rgb(215 218 220) !important;"><i class="bi bi-search"></i></span>
                    <input type="text" id="items_modal_search" class="form-control border-0 bg-light" placeholder="Search items" style="background-color: rgb(215 218 220) !important;">
                </div>
                <div class="table-responsive">
                    <style>
                        #itemsModal .modal-header { padding: 10px 12px !important; }
                        #itemsModal .modal-body { padding: 6px 10px !important; }
                        #itemsModal .modal-content { height: 85vh; display: flex; flex-direction: column; }
                        #itemsModal .modal-body { flex: 1 1 auto; overflow-y: auto; }
                        #itemsModal .alert { margin-bottom: 4px; padding: 4px 6px; font-size: 11px; }
                        #itemsModal .table { margin-bottom: 0; }
                        #itemsModal .table th, #itemsModal .table td { padding: 3px 6px; font-size: 14px; line-height: 1.1; }
                        #itemsModal .modal-dialog { max-width: 1400px; }
                        #itemsModal .modal-title { font-size: 16px; margin: 0; }
                        .moved-badge { font-size: 10px; }
                        #itemsModal .modal-footer { padding: 6px 10px !important; }
                    </style>
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Item Description</th>
                                <th class="text-center">Qty</th>
                                <th>Est. Price</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="items_list_body"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-white border-top">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <span class="text-muted small">Totals</span>
                    <span id="view_total_footer_text" class="fw-bold small"></span>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="editGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="update_tracking_logic.php?action=edit_group" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-bold">Edit Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex gap-2 mb-4">
                    <button type="button" id="btn_edit_group" class="btn btn-outline-primary flex-grow-1 fw-bold active" onclick="switchEditMode('group')">Edit Group</button>
                    <button type="button" id="btn_edit_case" class="btn btn-outline-primary flex-grow-1 fw-bold" onclick="switchEditMode('case')">Edit Case</button>
                </div>

                <input type="hidden" name="group_id" id="edit_group_id">
                <input type="hidden" name="edit_mode" id="edit_mode" value="group">
                
                <!-- Group Edit Section -->
                <div id="section_edit_group">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">GROUP NAME</label>
                        <input type="text" name="group_name" id="edit_group_name" class="form-control border-0 bg-light" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">VENDOR SELECTION (FROM QUOTATIONS)</label>
                        <select name="vendor_name" id="edit_vendor_select" class="form-select border-0 bg-light">
                            <option value="">Loading vendors...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">PRIORITY LEVEL</label>
                        <select name="tracking_speed" id="edit_tracking_speed" class="form-select border-0 bg-light">
                            <option value="Normal">Normal</option>
                            <option value="Urgent">Urgent</option>
                            <option value="Long Term">Long Term</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">PAYMENT STATUS</label>
                        <select name="payment_status" id="edit_payment_status" class="form-select border-0 bg-light" onchange="togglePaidAmountInput(this.value)">
                            <option value="Pending">Pending</option>
                            <option value="Partially Paid">Partially Paid</option>
                            <option value="Paid">Paid</option>
                        </select>
                    </div>

                    <div id="paid_amount_container" class="mb-3" style="display: none;">
                        <label class="form-label fw-bold small text-muted">AMOUNT PAID (PKR)</label>
                        <input type="number" step="0.01" name="paid_amount" id="edit_paid_amount" class="form-control border-0 bg-light">
                    </div>
                </div>

                <!-- Case Edit Section -->
                <div id="section_edit_case" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">CASE NUMBER</label>
                        <input type="text" name="case_number" id="edit_case_number" class="form-control border-0 bg-light">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">CASE STATUS</label>
                        <select name="case_status" id="edit_case_status" class="form-select border-0 bg-light">
                            <option value="Pending">Pending</option>
                            <option value="Case Initiated">Case Initiated</option>
                            <option value="Case Approved">Case Approved</option>
                            <option value="Mailed to MTSS">Mailed to MTSS</option>
                            <option value="PO Issued">PO Issued</option>
                            <option value="Document Submitted">Document Submitted</option>
                            <option value="Check Ready">Check Ready</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Group Modal -->
<div class="modal fade" id="deleteGroupModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white p-4">
                <h5 class="modal-title fw-bold">Delete Group</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-4">
                    <div class="bg-danger bg-opacity-10 d-inline-block p-3 rounded-circle mb-3" style="padding: 0.2rem 1rem 0.5rem 1rem !important;">
                        <i class="bi bi-exclamation-triangle-fill fs-1 text-danger"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Are you sure you want to delete this group?</h6>
                    <p class="text-muted small mb-0">Group: <b id="delete_group_name_text" class="text-dark"></b></p>
                    <p class="text-muted small">ID: <b id="delete_group_id_text" class="text-primary"></b></p>
                </div>
                
                <div class="d-grid gap-3">
                    <button type="button" class="btn btn-outline-warning py-3 rounded-3 fw-bold" onclick="executeDelete('soft')">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Soft Delete
                        <div class="small fw-normal opacity-75 mt-1">Revert group and items to Pending status</div>
                    </button>
                    
                    <button type="button" class="btn btn-danger py-3 rounded-3 fw-bold" onclick="executeDelete('hard')">
                        <i class="bi bi-trash-fill me-2"></i>Hard Delete
                        <div class="small fw-normal opacity-75 mt-1">Permanently remove from database</div>
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light w-100 py-2 rounded-3 fw-bold text-muted" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="consolidationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="consolidate_logic.php" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white p-4">
                <h5 class="modal-title fw-bold">Update Selected Groups</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 d-inline-block p-3 rounded-circle mb-2">
                        <i class="bi bi-collection-fill fs-2 text-primary"></i>
                    </div>
                    <p class="mb-0 text-muted">You are updating <b id="mergeCountText" class="text-primary fs-5">0</b> selected groups.</p>
                </div>
                
                <input type="hidden" name="selected_groups" id="selected_groups_ids">
                
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">ASSIGN VENDOR (OPTIONAL)</label>
                    <select id="consolidate_vendor_select" name="target_vendor" class="form-select border-0 bg-light">
                        <option value="">Keep Existing / No Change</option>
                        <option value="Not Assigned">Not Assigned</option>
                        <?php foreach($vendors as $v): ?>
                            <option value="<?= htmlspecialchars($v['vendor_name']) ?>"><?= htmlspecialchars($v['vendor_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">UPDATE STATUS (OPTIONAL)</label>
                    <select name="target_status" class="form-select border-0 bg-light">
                        <option value="">Keep Existing / No Change</option>
                        <option value="Pending">Pending</option>
                        <option value="RFQ Generated">RFQ Generated</option>
                        <option value="Quotation Awaited">Quotation Awaited</option>
                        <option value="Order Placed">Order Placed</option>
                        <option value="Case Initiated">Case Initiated</option>
                        <option value="Case Approved">Case Approved</option>
                        <option value="Mailed to MTSS">Mailed to MTSS</option>
                        <option value="PO Issued">PO Issued</option>
                        <option value="Document Submitted">Document Submitted</option>
                        <option value="Check Ready">Check Ready</option>
                        <option value="Recieved">Recieved</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">UPDATE PRIORITY (OPTIONAL)</label>
                    <select name="target_priority" class="form-select border-0 bg-light">
                        <option value="">Keep Existing / No Change</option>
                        <option value="Normal">Normal</option>
                        <option value="Urgent">Urgent</option>
                        <option value="Long Term">Long Term</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold shadow-sm">Apply Changes to Selected</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toast and Alert Helpers
function showTooltip(element, message) {
    let tooltip = document.getElementById('price-tooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'price-tooltip';
        tooltip.style.position = 'absolute';
        tooltip.style.backgroundColor = '#1e293b';
        tooltip.style.color = 'white';
        tooltip.style.padding = '4px 8px';
        tooltip.style.borderRadius = '4px';
        tooltip.style.fontSize = '0.75rem';
        tooltip.style.zIndex = '3000';
        tooltip.style.pointerEvents = 'none';
        tooltip.style.whiteSpace = 'nowrap';
        tooltip.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
        document.body.appendChild(tooltip);
    }
    tooltip.textContent = message;
    tooltip.style.display = 'block';
    
    const rect = element.getBoundingClientRect();
    tooltip.style.top = (rect.top + window.scrollY - 25) + 'px';
    tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';
}

function hideTooltip() {
    const tooltip = document.getElementById('price-tooltip');
    if (tooltip) tooltip.style.display = 'none';
}

function showToast(message) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'custom-toast shadow';
    toast.innerHTML = `<i class="bi bi-check-circle-fill text-success"></i> ${message}`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease-out reverse forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function showAlert(title, text, icon = 'info') {
    const container = document.getElementById('toastContainer');
    const alert = document.createElement('div');
    alert.className = `custom-toast shadow border-start border-4 ${icon === 'error' ? 'border-danger' : 'border-primary'}`;
    alert.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <i class="bi ${icon === 'error' ? 'bi-exclamation-triangle-fill text-danger' : 'bi-info-circle-fill text-primary'}"></i>
            <div>
                <div class="fw-bold">${title}</div>
                <div class="small">${text}</div>
            </div>
        </div>`;
    container.appendChild(alert);
    setTimeout(() => {
        alert.style.animation = 'slideInRight 0.3s ease-out reverse forwards';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
}

function showConfirm(title, text) {
    return new Promise((resolve) => {
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        document.getElementById('confirmTitle').innerText = title;
        document.getElementById('confirmText').innerText = text;
        
        const confirmBtn = document.getElementById('confirmBtn');
        const handleConfirm = () => {
            modal.hide();
            confirmBtn.removeEventListener('click', handleConfirm);
            resolve({ isConfirmed: true });
        };
        
        confirmBtn.addEventListener('click', handleConfirm);
        
        document.getElementById('confirmModal').addEventListener('hidden.bs.modal', () => {
            confirmBtn.removeEventListener('click', handleConfirm);
            resolve({ isConfirmed: false });
        }, { once: true });
        
        modal.show();
    });
}

function showPrompt(title, text) {
    return new Promise((resolve) => {
        const modal = new bootstrap.Modal(document.getElementById('promptModal'));
        document.getElementById('promptTitle').innerText = title;
        document.getElementById('promptText').innerText = text;
        document.getElementById('promptInput').value = '';
        
        const promptBtn = document.getElementById('promptBtn');
        const handlePrompt = () => {
            const val = document.getElementById('promptInput').value;
            modal.hide();
            promptBtn.removeEventListener('click', handlePrompt);
            resolve({ isConfirmed: true, value: val });
        };
        
        promptBtn.addEventListener('click', handlePrompt);
        
        document.getElementById('promptModal').addEventListener('hidden.bs.modal', () => {
            promptBtn.removeEventListener('click', handlePrompt);
            resolve({ isConfirmed: false, value: null });
        }, { once: true });
        
        modal.show();
    });
}

function updateUIGroupStatus(groupId, newStatus) {
    const row = document.querySelector(`tr.group-row[data-group-id="${groupId}"]`);
    if (!row) return;

    // Update row data attribute for filtering
    row.dataset.status = newStatus;

    const badge = row.querySelector('.process-status-badge');
    if (badge) {
        // Define status classes
        const statusClasses = {
            'Pending': 'status-pending',
            'RFQ Generated': 'status-rfq',
            'Quotation Awaited': 'status-quote',
            'Order Placed': 'status-order',
            'Case Initiated': 'status-initiated',
            'Case Approved': 'status-approved',
            'Mailed to MTSS': 'status-mtss',
            'PO Issued': 'status-po',
            'Document Submitted': 'status-submitted',
            'Check Ready': 'status-check',
            'Recieved': 'status-recieved'
        };

        // Remove all possible status classes
        Object.values(statusClasses).forEach(cls => badge.classList.remove(cls));

        // Add the new status class and text
        const newClass = statusClasses[newStatus] || 'status-default';
        badge.classList.add(newClass);
        badge.innerText = newStatus;
    }
}

// Logic to handle View Items and Merging for the popup
function openEditGroupModal(groupId, groupName, status, priority, caseNumber, vendorName, paidAmount, paymentStatus, caseStatus) {
    document.getElementById('edit_group_id').value = groupId;
    document.getElementById('edit_group_name').value = groupName;
    document.getElementById('edit_tracking_speed').value = priority;
    document.getElementById('edit_case_number').value = caseNumber || '';
    document.getElementById('edit_paid_amount').value = paidAmount || 0;
    document.getElementById('edit_payment_status').value = paymentStatus || 'Pending';
    document.getElementById('edit_case_status').value = caseStatus || 'Pending';

    togglePaidAmountInput(paymentStatus);
    switchEditMode('group'); // Reset to group mode on open

    // Fetch quoted vendors for this group
    const vendorSelect = document.getElementById('edit_vendor_select');
    vendorSelect.innerHTML = '<option value="">Loading vendors...</option>';

    fetch(`get_quoted_vendors.php?group_id=${encodeURIComponent(groupId)}`)
        .then(res => res.json())
        .then(data => {
            vendorSelect.innerHTML = '<option value="Not Assigned">Not Assigned</option>';
            if (data.success && data.vendors.length > 0) {
                data.vendors.forEach(v => {
                    const option = document.createElement('option');
                    option.value = v.name;
                    option.textContent = `${v.name} (Total: PKR ${v.total.toLocaleString()})`;
                    if (v.name === vendorName) option.selected = true;
                    vendorSelect.appendChild(option);
                });
            } else {
                // If no quoted vendors, show all vendors as fallback or just the current one
                const currentOpt = document.createElement('option');
                currentOpt.value = vendorName || 'Not Assigned';
                currentOpt.textContent = vendorName || 'Not Assigned';
                currentOpt.selected = true;
                vendorSelect.appendChild(currentOpt);
            }
        });

    new bootstrap.Modal(document.getElementById('editGroupModal')).show();
}

function switchEditMode(mode) {
    document.getElementById('edit_mode').value = mode;
    const groupSection = document.getElementById('section_edit_group');
    const caseSection = document.getElementById('section_edit_case');
    const groupBtn = document.getElementById('btn_edit_group');
    const caseBtn = document.getElementById('btn_edit_case');

    if (mode === 'group') {
        groupSection.style.display = 'block';
        caseSection.style.display = 'none';
        groupBtn.classList.add('active');
        caseBtn.classList.remove('active');
    } else {
        groupSection.style.display = 'none';
        caseSection.style.display = 'block';
        groupBtn.classList.remove('active');
        caseBtn.classList.add('active');
    }
}

function toggleCaseNumberInput(status) {
    // This function is no longer needed but kept for backward compatibility if called elsewhere
}

function togglePaidAmountInput(status) {
    const container = document.getElementById('paid_amount_container');
    container.style.display = (status === 'Paid' || status === 'Partially Paid') ? 'block' : 'none';
}

function viewItems(groupId, groupName, shopTotal = 0) {
    document.getElementById('view_group_id_text').innerText = groupId;
    document.getElementById('view_group_name_text').innerText = groupName;
    
    // Set Print Button dynamically
    document.getElementById('modalPrintBtn').onclick = function() {
        printGroup(groupId, groupName);
    };

    const tableBody = document.getElementById('items_list_body');
    tableBody.innerHTML = '<tr><td colspan="4" class="text-center p-4"><div class="spinner-border text-primary"></div></td></tr>';
    
    new bootstrap.Modal(document.getElementById('itemsModal')).show();
    const searchInput = document.getElementById('items_modal_search');
    if (searchInput) { searchInput.value = ''; }

    fetch(`get_group_items.php?group_id=${groupId}`)
        .then(res => res.json())
        .then(data => {
            tableBody.innerHTML = '';
            if (data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="4" class="text-center p-4">No items found.</td></tr>';
                const label = (shopTotal && shopTotal > 0) ? 'Vendor Price' : 'Estimated Total';
                const value = (shopTotal && shopTotal > 0) ? shopTotal : 0;
                document.getElementById('view_total_footer_text').innerText = `${label}: PKR ${new Intl.NumberFormat().format(Math.round(value))}`;
                return;
            }

            const mergedData = data.reduce((acc, item) => {
                const key = item.item_name;
                const isMoved = (item.source_group_id === groupId && item.current_group_id !== groupId);

                if (!acc[key]) {
                    acc[key] = {
                        ...item,
                        quantity: parseInt(item.quantity),
                        estimated_price: parseFloat(item.estimated_price),
                        ids: [item.id],
                        isMoved: isMoved
                    };
                } else {
                    acc[key].quantity += parseInt(item.quantity);
                    acc[key].estimated_price += parseFloat(item.estimated_price);
                    acc[key].ids.push(item.id);
                }
                return acc;
            }, {});

            const estimatedTotal = Object.values(mergedData).reduce((sum, item) => sum + (parseFloat(item.estimated_price) || 0), 0);
            const useShop = (shopTotal && shopTotal > 0);
            const label = useShop ? 'Vendor Price' : 'Estimated Total';
            const value = useShop ? shopTotal : estimatedTotal;
            document.getElementById('view_total_footer_text').innerText = `${label}: PKR ${new Intl.NumberFormat().format(Math.round(value))}`;
            if (searchInput) {
                searchInput.oninput = filterItemsModal;
                filterItemsModal();
            }

            Object.values(mergedData).forEach(item => {
                const rowClass = item.isMoved ? 'item-moved' : '';
                const movedInfo = item.isMoved ? 
                    `<span class="moved-badge"><i class="bi bi-arrow-right"></i> Mixed in: ${item.current_group_name}</span>` : '';
                
                const idsString = JSON.stringify(item.ids);
                const trackBtn = `<a class="btn btn-sm text-primary" href="item_tracking.php?group_id=${encodeURIComponent(groupId)}&item=${encodeURIComponent(item.item_name)}" title="Item Tracking"><i class="bi bi-graph-up"></i></a>`;
                const actionBtn = item.isMoved ? 
                    `<span class="badge bg-secondary">Transferred</span>` : 
                    `${trackBtn} <button class="btn btn-sm text-danger" onclick='deleteMergedItems(${idsString}, "${groupId}")'><i class="bi bi-trash"></i></button>`;

                tableBody.innerHTML += `
                <tr class="${rowClass}">
                    <td class="fw-medium">
                        ${item.item_name}
                        ${movedInfo}
                        ${item.ids.length > 1 ? `<span class="text-success fw-bold small">(Merged ${item.ids.length} entries)</span>` : ''}
                    </td>
                    <td class="text-center fw-bold">${item.quantity}</td>
                    <td>${item.estimated_price.toFixed(2)}</td>
                    <td class="text-end">${actionBtn}</td>
                </tr>`;
            });
        });
}

function filterItemsModal() {
    const q = (document.getElementById('items_modal_search')?.value || '').toLowerCase();
    document.querySelectorAll('#items_list_body tr').forEach(tr => {
        const text = tr.innerText.toLowerCase();
        tr.style.display = text.includes(q) ? '' : 'none';
    });
}

function deleteMergedItems(ids, groupId) {
    showConfirm('Delete Item Entry', `Are you sure you want to delete this item entry (Total ${ids.length} records)?`).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_item_logic.php?ids=${JSON.stringify(ids)}&group_id=${groupId}`;
        }
    });
}


function updateBar() {
    const checked = document.querySelectorAll('.group-checkbox:checked');
    const bar = document.getElementById('consolidateBar');
    const countText = document.getElementById('selectedCountText');
    const deselectBtn = document.getElementById('deselectAllBtn');
    
    if(checked.length > 0) {
        bar.style.display = 'flex';
        countText.innerText = `${checked.length} Groups Selected`;
        if(deselectBtn) deselectBtn.style.display = 'inline-block';
    } else {
        bar.style.display = 'none';
        if(deselectBtn) deselectBtn.style.display = 'none';
    }
}

document.getElementById('deselectAllBtn').addEventListener('click', function() {
    document.querySelectorAll('.group-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBar();
});

document.getElementById('consolidateBtn').addEventListener('click', function() {
    const checked = document.querySelectorAll('.group-checkbox:checked');
    if (checked.length < 2) {
        showAlert('Selection Required', 'Please select at least 2 groups to consolidate.');
        return;
    }
    openConsolidationModal();
});

function openConsolidationModal() {
    const checked = Array.from(document.querySelectorAll('.group-checkbox:checked')).map(cb => cb.value);
    document.getElementById('selected_groups_ids').value = JSON.stringify(checked);
    document.getElementById('mergeCountText').innerText = checked.length;
    new bootstrap.Modal(document.getElementById('consolidationModal')).show();
}

document.getElementById('selectAll').addEventListener('change', e => {
    document.querySelectorAll('.group-checkbox').forEach(cb => cb.checked = e.target.checked);
    updateBar();
});
document.querySelectorAll('.group-checkbox').forEach(cb => cb.addEventListener('change', updateBar));

document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        filterTable();
    });
});

function filterTable() {
    let global = document.getElementById('globalSearch').value.toLowerCase();
    let status = document.querySelector('.filter-tab.active').dataset.status.toLowerCase();
    
    document.querySelectorAll('#tableBody tr.group-row').forEach(row => {
        let text = row.innerText.toLowerCase();
        let rowStatus = (row.dataset.status || "").toLowerCase();
        
        let match = text.includes(global) && (status === '' || rowStatus.includes(status));
        
        row.style.display = match ? "" : "none";
        
        // Handle items-expanded-row visibility
        let expandedRow = row.nextElementSibling;
        if (expandedRow && expandedRow.classList.contains('items-expanded-row')) {
            expandedRow.style.display = match ? "" : "none";
            
            // If the row is hidden, close the collapse if it was open
            if (!match) {
                const collapseEl = expandedRow.querySelector('.collapse');
                if (collapseEl && collapseEl.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(collapseEl);
                    if (bsCollapse) bsCollapse.hide();
                }
            }
        }
    });
}
document.getElementById('globalSearch').addEventListener('keyup', filterTable);

function clearFilters() { window.location.href = 'stock_view.php'; }

let deleteGroupId = null;
function openDeleteGroupModal(groupId, groupName) {
    deleteGroupId = groupId;
    document.getElementById('delete_group_id_text').innerText = groupId;
    document.getElementById('delete_group_name_text').innerText = groupName;
    new bootstrap.Modal(document.getElementById('deleteGroupModal')).show();
}

function executeDelete(type) {
    if (!deleteGroupId) return;
    
    const fd = new FormData();
    fd.append('group_id', deleteGroupId);
    fd.append('delete_type', type);

    // Close the modal immediately as the action is initiated
    const modalEl = document.getElementById('deleteGroupModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    fetch('delete_group_logic.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast(res.message || 'Action completed successfully');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showAlert('Error', res.message || 'Failed to complete action', 'error');
            }
        })
        .catch(() => showAlert('Error', 'An unexpected error occurred', 'error'));
}

function deleteGroup(id) { 
    showConfirm('Delete Group', 'Are you sure you want to delete this entire group and all its tracking data?').then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_demand.php?id=${id}`; 
        }
    });
}

function addVendorFlow(selectEl) {
    if (!selectEl || selectEl.value !== '__add_vendor__') return;
    showPrompt('New Vendor', 'Enter new vendor name').then((result) => {
        if (!result.isConfirmed || !result.value || !result.value.trim()) {
            selectEl.value = '';
            return;
        }
        const name = result.value.trim();
        const fd = new FormData();
        fd.append('vendor_name', name);
        fetch('update_tracking_logic.php?action=add_vendor', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res && res.success) {
                    const opt = document.createElement('option');
                    opt.value = name;
                    opt.textContent = name;
                    selectEl.appendChild(opt);
                    selectEl.value = name;
                    showToast('Vendor added successfully');
                } else {
                    showAlert('Error', 'Failed to add vendor', 'error');
                    selectEl.value = '';
                }
            })
            .catch(() => { showAlert('Error', 'Failed to add vendor', 'error'); selectEl.value = ''; });
    });
}

document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'modal_vendor') addVendorFlow(e.target);
    if (e.target && e.target.id === 'consolidate_vendor_select') addVendorFlow(e.target);
});
function printGroup(groupId, groupName) {
    fetch(`get_group_items.php?group_id=${groupId}`)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) { showAlert('No items', 'No items to print.', 'info'); return; }

            const mergedPrintData = data.reduce((acc, item) => {
                const isMoved = (item.source_group_id === groupId && item.current_group_id !== groupId);
                if (isMoved) return acc;
                const key = item.item_name;
                if (!acc[key]) {
                    acc[key] = { ...item, quantity: parseInt(item.quantity) };
                } else {
                    acc[key].quantity += parseInt(item.quantity);
                }
                return acc;
            }, {});

            const mergedItemsArray = Object.values(mergedPrintData);
            if (mergedItemsArray.length === 0) { showAlert('No items', 'No active items to print.', 'info'); return; }

            const printWindow = window.open('', '_blank');
            let tableRows = '';
            mergedItemsArray.forEach((item, index) => {
                tableRows += `
                <tr>
                    <td>${index + 1}</td>
                    <td style="text-align: left;">${item.item_name || 'N/A'}</td>
                    <td style="text-align: center;">${item.quantity || '0'}</td>
                    <td>${item.type || 'Permanent'}</td>
                    <td>${item.density || 'num'}</td>
                    <td>${item.subtype || 'Parts'}</td>
                    <td>${item.inv_asset || 'Inventory'}</td>
                    <td>${item.subhead || 'Equipment'}</td>
                </tr>`;
            });

            printWindow.document.write(`
                <html>
                <head>
                    <title>Print Group - ${groupId}</title>
                    <style>
                        body { font-family: 'Courier New', monospace; padding: 30px; color: black; }
                        .header { text-align: center; border-bottom: 4px solid black; padding-bottom: 10px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                        th, td { border: 2px solid black; padding: 10px; text-align: center; font-size: 16px; font-weight: bold; text-transform: uppercase;}
                        th { background-color: #eee; }
                    </style>
                </head>
                <body onload="window.print()">
                    <div class="header">
                        <h1>PROCUREMENT CONTROL LIST</h1>
                        <p>GROUP: ${groupName} | ID: ${groupId}</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>SNo</th><th>Description</th><th>Qty</th><th>Type</th><th>Density</th><th>Subtype</th><th>Asset</th><th>Head</th>
                            </tr>
                        </thead>
                        <tbody>${tableRows}</tbody>
                    </table>
                </body>
                </html>
            `);
            printWindow.document.close();
        });
}

let currentGroupItems = [];
let vendorsList = <?= json_encode($vendors) ?>;
let activeVendorColumns = [];

function openQuotationModal(groupId, groupName) {
    document.getElementById('quote_group_id').innerText = groupId;
    document.getElementById('quote_group_name').innerText = groupName;
    
    const headerRow = document.getElementById('quote_header_row');
    const quoteBody = document.getElementById('quote_body');
    const totalRow = document.getElementById('quote_total_row');
    
    // Reset state
    headerRow.innerHTML = '<th style="width: 250px; min-width: 250px;" class="bg-light sticky-left"><div class="item-desc-content fw-bold text-muted small">ITEM DESCRIPTION</div></th>';
    quoteBody.innerHTML = '<tr><td colspan="10" class="text-center p-5"><div class="spinner-border text-primary"></div></td></tr>';
    totalRow.innerHTML = '<th class="bg-light sticky-left"><div class="item-desc-content fw-bold text-dark">TOTAL (INC. TAX)</div></th>';
    activeVendorColumns = [];
    currentGroupItems = [];

    const qModal = new bootstrap.Modal(document.getElementById('quotationModal'));
    qModal.show();

    // Fetch tax info
    fetch(`get_tax_info.php?group_id=${encodeURIComponent(groupId)}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                document.getElementById('quote_tax_type').value = res.data.tex_config || 'gst';
                document.getElementById('quote_tax_perc').value = res.data.tex_perc || 18;
                calculateTotals();
            }
        });

    // Fetch items for this group
    fetch(`get_group_items.php?group_id=${encodeURIComponent(groupId)}`)
        .then(r => r.json())
        .then(items => {
            currentGroupItems = items;
            renderQuotationTable();
            
            // Also fetch existing quotation data for this group
            fetch(`get_quotation_data.php?group_id=${encodeURIComponent(groupId)}`)
                .then(r => r.json())
                .then(existingData => {
                    // ALWAYS add one empty column first for a new vendor
                    addNewVendorColumn();

                    if (existingData && existingData.length > 0) {
                        // Group data by vendor_id to create columns
                        const vendorGroups = existingData.reduce((acc, row) => {
                            if (!acc[row.vendor_id]) acc[row.vendor_id] = [];
                            acc[row.vendor_id].push(row);
                            return acc;
                        }, {});

                        // Add columns for already stored vendor data
                        Object.keys(vendorGroups).forEach(vId => {
                            const colId = addNewVendorColumn(vId);
                            // Fill in values
                            vendorGroups[vId].forEach(row => {
                                const tr = document.querySelector(`#quote_body tr[data-item-id="${row.item_id}"]`);
                                if (tr) {
                                    const td = tr.querySelector(`td[data-col-id="${colId}"]`);
                                    if (td) {
                                        const input = td.querySelector('.price-input');
                                        input.value = row.vendor_price;
                                        input.dataset.lastSaved = row.vendor_price;
                                        input.dataset.lastVendor = vId;
                                        input.disabled = false; // Enable since it has data
                                    }
                                }
                            });
                        });
                    }
                    calculateTotals();
                });
        });
}

function updateTaxInfo() {
    const groupId = document.getElementById('quote_group_id').innerText;
    const taxType = document.getElementById('quote_tax_type').value;
    const taxPerc = document.getElementById('quote_tax_perc').value;

    // Calculate immediately for UI responsiveness
    calculateTotals();

    const fd = new FormData();
    fd.append('group_id', groupId);
    fd.append('tax_type', taxType);
    fd.append('tax_perc', taxPerc);

    // Update database in background
    fetch('update_tax_info.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // Database synced
            }
        });
}

function calculateTotals() {
    const taxPerc = parseFloat(document.getElementById('quote_tax_perc').value) || 0;
    
    activeVendorColumns.forEach(colId => {
        let subtotal = 0;
        document.querySelectorAll(`td[data-col-id="${colId}"] .price-input`).forEach(input => {
            const price = parseFloat(input.value) || 0;
            const tr = input.closest('tr');
            if (tr) {
                const qtyText = tr.querySelector('.text-muted').innerText;
                const qtyMatch = qtyText.match(/\d+/);
                const qty = qtyMatch ? parseInt(qtyMatch[0]) : 0;
                subtotal += (price * qty);
            }
        });

        const taxAmount = subtotal * (taxPerc / 100);
        const totalWithTax = subtotal + taxAmount;
         const totalCell = document.querySelector(`#quote_total_row td[data-col-id="${colId}"]`);
         if (totalCell) {
             totalCell.innerHTML = `
                 <div class="p-1"> <!-- Reduced padding from p-2 -->
                     <div class="text-muted" style="font-size: 0.65rem; margin-bottom: 1px;">Sub: PKR ${subtotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                     <div class="fw-bold text-primary" style="font-size: 0.85rem;">PKR ${totalWithTax.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                 </div>
             `;
         }
    });
}

function renderQuotationTable() {
    const quoteBody = document.getElementById('quote_body');
    quoteBody.innerHTML = '';
    
    if (currentGroupItems.length === 0) {
        quoteBody.innerHTML = '<tr><td colspan="10" class="text-center p-4">No items found in this group.</td></tr>';
        return;
    }

    // Merge items by item_id for quotation entry
    const mergedItems = currentGroupItems.reduce((acc, item) => {
        if (!acc[item.item_id]) {
            acc[item.item_id] = { ...item };
        } else {
            acc[item.item_id].quantity = parseInt(acc[item.item_id].quantity) + parseInt(item.quantity);
        }
        return acc;
    }, {});

    Object.values(mergedItems).forEach(item => {
        const row = document.createElement('tr');
        row.setAttribute('data-item-id', item.item_id); // Use item_id (73) instead of id (197)
        row.innerHTML = `<td class="bg-light sticky-left fw-bold" style="font-size: 0.85rem; border-right: 1px solid #cbd5e1; height: 100%;">
            <div class="item-desc-content">
                ${item.item_name}
                <div class="text-muted" style="font-size: 0.65rem;">Qty: ${item.quantity} ${item.density_unit || ''}</div>
            </div>
        </td>`;
        
        // Add existing vendor columns to this row
        activeVendorColumns.forEach(colId => {
            const td = createVendorInputCell(item.item_id, colId); // Pass item_id (73)
            row.appendChild(td);
        });
        
        quoteBody.appendChild(row);
    });
}

function createVendorInputCell(itemId, colId) {
    const td = document.createElement('td');
    td.setAttribute('data-col-id', colId);
    td.className = 'p-0 vendor-separator';
    td.style.height = 'inherit';
    
    const input = document.createElement('input');
    input.type = 'number';
    input.className = 'form-control price-input';
    input.placeholder = 'Enter Price';
    input.style.fontSize = '15px';
    input.style.fontWeight = '700';
    
    // Initial state: disabled if no vendor selected
    const vendorSelect = document.getElementById(`vendor_select_${colId}`);
    if (!vendorSelect || !vendorSelect.value) {
        input.disabled = true;
    }

    // Tooltip listeners for disabled state
    const tooltipShow = () => { if (input.disabled) showTooltip(input, "Please select vendor first"); };
    const tooltipHide = () => hideTooltip();
    
    input.addEventListener('mouseenter', tooltipShow);
    input.addEventListener('focus', tooltipShow);
    input.addEventListener('mouseleave', tooltipHide);
    input.addEventListener('blur', tooltipHide);

    // Save logic
    const saveData = () => {
        const vendorId = document.getElementById(`vendor_select_${colId}`).value;
        const price = input.value;
        const groupId = document.getElementById('quote_group_id').innerText;

        if (!vendorId || !price) return;
        if (input.dataset.lastSaved === price && input.dataset.lastVendor === vendorId) return;

        const fd = new FormData();
        fd.append('items_group_id', groupId);
        fd.append('item_id', itemId);
        fd.append('vendor_id', vendorId);
        fd.append('vendor_price', price);

        fetch('save_quotation_data.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    input.classList.add('is-valid');
                    input.dataset.lastSaved = price;
                    input.dataset.lastVendor = vendorId;
                    
                    // Update status badge in real-time
                    if (res.new_status) {
                        updateUIGroupStatus(groupId, res.new_status);
                    }

                    setTimeout(() => input.classList.remove('is-valid'), 1000);
                } else {
                    input.classList.add('is-invalid');
                    setTimeout(() => input.classList.remove('is-invalid'), 2000);
                }
            })
            .catch(() => {
                input.classList.add('is-invalid');
                setTimeout(() => input.classList.remove('is-invalid'), 2000);
            });
    };

    input.addEventListener('change', () => {
        saveData();
        calculateTotals();
    });
    input.addEventListener('input', calculateTotals);
    input.addEventListener('blur', saveData);

    td.appendChild(input);
    return td;
}

function addNewVendorColumn(initialVendorId = null) {
    const colId = Date.now() + Math.random();
    activeVendorColumns.push(colId);
    
    const headerRow = document.getElementById('quote_header_row');
    const th = document.createElement('th');
    th.style.width = '250px'; // Updated from 180px to 250px
    th.style.minWidth = '250px'; // Updated from 180px to 250px
    th.className = 'bg-white p-2 vendor-separator';
    th.id = `header_col_${colId}`;
    
    // Wrapper for vertical centering
    const wrapper = document.createElement('div');
    wrapper.className = 'd-flex align-items-center h-100 gap-2';

    const select = document.createElement('select');
    select.className = 'form-select form-select-sm fw-bold border-primary flex-grow-1';
    select.id = `vendor_select_${colId}`;
    select.innerHTML = '<option value="">Select Vendor</option>';
    
    // Add existing vendors to select, excluding already selected ones in other columns
    updateVendorOptions(select);
    
    if (initialVendorId) {
        select.value = initialVendorId;
        // After setting initial value, update others
        setTimeout(() => {
            document.querySelectorAll('[id^="vendor_select_"]').forEach(s => {
                if (s.id !== select.id) updateVendorOptions(s);
            });
        }, 0);
    }

    const removeBtn = document.createElement('button');
    removeBtn.className = 'btn btn-link btn-sm p-0 text-danger text-decoration-none';
    removeBtn.innerHTML = '<i class="bi bi-trash fs-5"></i>';
    removeBtn.onclick = () => removeVendorColumn(colId);

    wrapper.appendChild(select);
    wrapper.appendChild(removeBtn);
    th.appendChild(wrapper);
    headerRow.appendChild(th);
    
    // Add input cells for all rows
    document.querySelectorAll('#quote_body tr').forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        const itemId = row.getAttribute('data-item-id');
        const td = createVendorInputCell(itemId, colId);
        td.style.width = '250px'; // Explicitly set width
        td.style.minWidth = '250px'; // Explicitly set width
        row.appendChild(td);
    });

    // Add total cell for this column
    const totalRow = document.getElementById('quote_total_row');
    const totalTd = document.createElement('td');
    totalTd.setAttribute('data-col-id', colId);
    totalTd.className = 'bg-light vendor-separator';
    totalTd.style.width = '250px'; // Explicitly set width
    totalTd.style.minWidth = '250px'; // Explicitly set width
    totalTd.innerHTML = `<div class="fw-bold text-primary p-1">PKR 0.00</div>`;
    totalRow.appendChild(totalTd);

    select.onchange = () => {
        if (select.value === "__add_new__") {
            window.activeAddingColId = colId;
            new bootstrap.Modal(document.getElementById('addVendorModal')).show();
            // Reset to empty so if they cancel, it doesn't stay on "__add_new__"
            select.value = "";
            return;
        }
        
        // Enable fields in this column when vendor is selected
        document.querySelectorAll(`td[data-col-id="${colId}"] .price-input`).forEach(input => {
            if (select.value) {
                input.disabled = false;
                hideTooltip(); // Clear any lingering tooltip
                
                // Trigger save for all filled inputs in this column
                if (input.value) {
                    input.dispatchEvent(new Event('change'));
                }
            } else {
                input.disabled = true;
            }
        });

        // When a vendor is selected, update other dropdowns to prevent duplicate selection
        document.querySelectorAll('[id^="vendor_select_"]').forEach(s => {
            if (s.id !== select.id) updateVendorOptions(s);
        });
    };

    return colId; // Return for reference
}

function updateVendorOptions(selectEl) {
    const currentVal = selectEl.value;
    const selectedVendorIds = Array.from(document.querySelectorAll('[id^="vendor_select_"]'))
        .filter(s => s !== selectEl)
        .map(s => s.value)
        .filter(v => v !== "" && v !== "__add_new__");

    selectEl.innerHTML = '<option value="">Select Vendor</option>';
    vendorsList.forEach(v => {
        if (!selectedVendorIds.includes(v.id.toString()) || v.id.toString() === currentVal) {
            const opt = document.createElement('option');
            opt.value = v.id;
            opt.textContent = v.vendor_name;
            if (v.id.toString() === currentVal) opt.selected = true;
            selectEl.appendChild(opt);
        }
    });
    
    // Add "Add New Vendor" option at the end
    const addNewOpt = document.createElement('option');
    addNewOpt.value = "__add_new__";
    addNewOpt.textContent = "+ Add New Vendor";
    addNewOpt.className = "text-primary fw-bold";
    selectEl.appendChild(addNewOpt);
}

function removeVendorColumn(colId) {
    const select = document.getElementById(`vendor_select_${colId}`);
    const vendorId = select ? select.value : null;
    const groupId = document.getElementById('quote_group_id').innerText;

    const performCleanup = () => {
        document.getElementById(`header_col_${colId}`).remove();
        document.querySelectorAll(`td[data-col-id="${colId}"]`).forEach(td => td.remove());
        activeVendorColumns = activeVendorColumns.filter(id => id !== colId);
        document.querySelectorAll('[id^="vendor_select_"]').forEach(s => updateVendorOptions(s));
        hideTooltip();
    };

    if (vendorId && vendorId !== "" && vendorId !== "__add_new__") {
        showConfirm('Delete Vendor Column', 'Are you sure? This will delete all quotation prices for this vendor in this group from the database.').then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('items_group_id', groupId);
                fd.append('vendor_id', vendorId);

                fetch('delete_quotation_column.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            showToast('Vendor data deleted successfully');
                            
                            // Update status badge in real-time
                            if (res.new_status) {
                                updateUIGroupStatus(groupId, res.new_status);
                            }

                            performCleanup();
                        } else {
                            showAlert('Error', res.message || 'Failed to delete data');
                        }
                    });
            }
        });
    } else {
        performCleanup();
    }
}

function saveNewVendor() {
    const name = document.getElementById('new_vendor_name_input').value.trim();
    if (!name) return;
    
    const fd = new FormData();
    fd.append('vendor_name', name);
    
    fetch('update_tracking_logic.php?action=add_vendor', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // Update local vendorsList
                const newVendor = { id: res.vendor_id, vendor_name: name };
                vendorsList.push(newVendor);
                
                // Update all selects
                document.querySelectorAll('[id^="vendor_select_"]').forEach(s => updateVendorOptions(s));
                
                // Select it in the column where it was initiated
                if (window.activeAddingColId) {
                    document.getElementById(`vendor_select_${window.activeAddingColId}`).value = res.vendor_id;
                    // Trigger change to update others
                    document.getElementById(`vendor_select_${window.activeAddingColId}`).onchange();
                }
                
                bootstrap.Modal.getInstance(document.getElementById('addVendorModal')).hide();
                document.getElementById('new_vendor_name_input').value = '';
                showToast('Vendor added successfully');
            } else {
                showAlert('Error', res.message || 'Error adding vendor', 'error');
            }
        });
}

function openAssignVendorModal(pdId, itemId, itemName, groupId) {
    document.getElementById('assign_pd_id').value = pdId;
    document.getElementById('assign_item_id').value = itemId;
    document.getElementById('assign_group_id').value = groupId;
    document.getElementById('assign_item_name').innerText = itemName;
    
    const select = document.getElementById('assign_vendor_select');
    const noMsg = document.getElementById('no_quotation_msg');
    
    select.innerHTML = '<option value="">Loading...</option>';
    noMsg.style.display = 'none';
    
    new bootstrap.Modal(document.getElementById('assignVendorModal')).show();
    
    fetch(`get_item_quotations.php?item_id=${itemId}&group_id=${encodeURIComponent(groupId)}`)
        .then(r => r.json())
        .then(vendors => {
            select.innerHTML = '<option value="">Select Vendor</option>';
            if (vendors.length === 0) {
                noMsg.style.display = 'block';
                return;
            }
            vendors.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = `${v.vendor_name} (PKR ${v.vendor_price})`;
                select.appendChild(opt);
            });
        });
}

function saveVendorAssignment() {
    const pdId = document.getElementById('assign_pd_id').value;
    const vendorId = document.getElementById('assign_vendor_select').value;
    const groupId = document.getElementById('assign_group_id').value;
    
    if (!vendorId) {
        showAlert('Vendor Required', 'Please select a vendor', 'error');
        return;
    }
    
    const fd = new FormData();
    fd.append('pd_id', pdId);
    fd.append('vendor_id', vendorId);
    
    fetch('update_item_vendor.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Vendor assigned successfully');
                bootstrap.Modal.getInstance(document.getElementById('assignVendorModal')).hide();
                // Refresh the item list for this group
                const content = document.getElementById('items-content-' + groupId);
                if (content) {
                    content.removeAttribute('data-loaded');
                    toggleGroupRow(groupId);
                }
            } else {
                showAlert('Error', res.message || 'Error saving assignment', 'error');
            }
        });
}

function toggleGroupRow(groupId) {
    const content = document.getElementById('items-content-' + groupId);
    if (!content) return;

    // Close other open collapses
    document.querySelectorAll('.items-expanded-row .collapse.show').forEach(el => {
        if (el.id !== 'items-content-' + groupId) {
            bootstrap.Collapse.getInstance(el).hide();
        }
    });

    const collapse = new bootstrap.Collapse(content, { toggle: true });
    if (!content.getAttribute('data-loaded')) {
        const tbody = document.getElementById('items-body-' + groupId);
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="6" class="text-center p-3"><div class="spinner-border text-primary"></div></td></tr>';
        fetch(`get_group_items.php?group_id=${encodeURIComponent(groupId)}`)
            .then(r => r.json())
            .then(items => {
                tbody.innerHTML = '';
                if (!Array.isArray(items) || items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center p-3">No items found.</td></tr>';
                    const meta = document.getElementById('items-meta-' + groupId);
                    if (meta) meta.textContent = '0 items • PKR 0';
                    return;
                }
                let totalAmount = 0;
                let allOrdered = true;
                let allReceived = true;
                let hasVendorAssigned = false;

                items.forEach(it => {
                    if (it.vendor_id) hasVendorAssigned = true;
                    
                    const qty = parseInt(it.quantity) || 0;
                    const estPrice = parseFloat(it.estimated_price) || 0;
                    const vendorPrice = parseFloat(it.vendor_price) || 0;
                    
                    // Display vendor price if vendor is assigned, else display estimated price
                    const displayPrice = it.vendor_id ? vendorPrice : estPrice;
                    const subtotal = qty * displayPrice;
                    
                    const moved = (it.source_group_id === groupId && it.current_group_id !== groupId);
                    const movedBadge = moved ? '<span class="badge bg-secondary ms-2">Moved</span>' : '';
                    const vendorDisplay = it.vendor_name ? `<span class="badge bg-info-subtle text-info border border-info-subtle" style="margin: 0 15px;">${it.vendor_name}</span>` : '<span class="text-muted small">Not Assigned</span>';
                    
                    if (!moved) totalAmount += subtotal;
                    
                    // Track statuses for group button
                    if (it.item_status !== 'Order Placed' && it.item_status !== 'Recieved') allOrdered = false;
                    if (it.item_status !== 'Recieved') allReceived = false;

                    let actionButtons = `<button class="btn btn-sm text-primary" onclick="openAssignVendorModal('${it.id}', '${it.item_id}', '${it.item_name.replace(/'/g, "\\'")}', '${groupId}')" title="Assign Vendor"><i class="bi bi-pencil-square"></i></button>`;
                    
                    if (it.item_status === 'Order Placed') {
                        actionButtons += `<button class="btn btn-sm text-success ms-1" onclick="receiveItem('${it.id}', '${groupId}')" title="Mark Received"><i class="bi bi-check-circle-fill"></i></button>`;
                    } else if (it.item_status === 'Recieved') {
                        actionButtons = `<span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2"><i class="bi bi-check-all me-1"></i>Recieved</span>`;
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td>${it.item_name || ''} ${movedBadge}</td>
                            <td class="text-center">${qty} ${it.density_unit || ''}</td>
                            <td>${vendorDisplay}</td>
                            <td><span style="margin: 0 15px;">PKR ${displayPrice.toFixed(2)}</span></td>
                            <td class="text-end">
                                ${actionButtons}
                            </td>
                        </tr>`;
                });

                // Update Header based on whether any item has a vendor assigned
                const priceHeader = document.getElementById('price-header-' + groupId);
                if (priceHeader) {
                    priceHeader.innerText = hasVendorAssigned ? 'Vendor Price' : 'Est. Price';
                }

                // Update Group Header Action Button
                const actionContainer = document.getElementById('group-action-container-' + groupId);
                if (actionContainer) {
                    if (allReceived) {
                        actionContainer.innerHTML = `<span class="badge bg-success py-2 px-3 rounded-pill fw-bold shadow-sm"><i class="bi bi-check-all me-1"></i>All Received</span>`;
                    } else if (allOrdered) {
                        actionContainer.innerHTML = `<button class="btn btn-sm btn-info text-white fw-bold px-3 rounded-pill shadow-sm" onclick="receiveGroupItems('${groupId}')">
                                                        <i class="bi bi-box-seam me-1"></i>Received Items
                                                     </button>`;
                    } else {
                        actionContainer.innerHTML = `<button class="btn btn-sm btn-success fw-bold px-3 rounded-pill shadow-sm" onclick="placeGroupOrder('${groupId}')">
                                                        <i class="bi bi-cart-check me-1"></i>Place Order
                                                     </button>`;
                    }
                }

                const meta = document.getElementById('items-meta-' + groupId);
                if (meta) {
                    const label = hasVendorAssigned ? 'Vendor Total' : 'Est. Total';
                    meta.textContent = `${items.length} items • ${label}: PKR ${totalAmount.toFixed(2)}`;
                }
                content.setAttribute('data-loaded', '1');
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center p-3 text-danger">Failed to load items.</td></tr>';
            });
    }
}

function receiveItem(pdId, groupId) {
    showConfirm('Mark Received', 'Are you sure you want to mark this item as "Received"?').then((result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('pd_id', pdId);
            fd.append('group_id', groupId);
            
            fetch('update_tracking_logic.php?action=receive_item', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res && res.success) {
                    showToast('Item status updated to "Received"!');
                    const content = document.getElementById('items-content-' + groupId);
                    if (content) {
                        content.removeAttribute('data-loaded');
                        toggleGroupRow(groupId);
                    }
                } else {
                    showAlert('Error', 'Failed to update item status: ' + (res.error || 'Unknown error'), 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('Error', 'Error updating item status', 'error');
            });
        }
    });
}

function receiveGroupItems(groupId) {
    showConfirm('Receive All Items', 'Are you sure you want to mark ALL items in this group as "Received"?').then((result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('group_id', groupId);
            
            fetch('update_tracking_logic.php?action=receive_group_items', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res && res.success) {
                    showToast('All group items updated to "Received"!');
                    const content = document.getElementById('items-content-' + groupId);
                    if (content) {
                        content.removeAttribute('data-loaded');
                        toggleGroupRow(groupId);
                    }
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlert('Error', 'Failed to update items: ' + (res.error || 'Unknown error'), 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('Error', 'Error updating items', 'error');
            });
        }
    });
}

function placeGroupOrder(groupId) {
    showConfirm('Place Order', 'Are you sure you want to change the status of all items in this group to "Order Placed"?').then((result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('group_id', groupId);
            
            fetch('update_tracking_logic.php?action=place_group_order', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(res => {
                if (res && res.success) {
                    showToast('Group items status updated to "Order Placed"!');
                    // Refresh the item list for this group
                    const content = document.getElementById('items-content-' + groupId);
                    if (content) {
                        content.removeAttribute('data-loaded');
                        toggleGroupRow(groupId);
                    }
                    // Optionally reload page to update main status badges
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showAlert('Error', 'Failed to update order status: ' + (res.error || 'Unknown error'), 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('Error', 'Error updating order status', 'error');
            });
        }
    });
}

function openTimelineModal(groupId, groupName) {
    document.getElementById('timelineGroupName').innerText = groupName;
    const container = document.getElementById('timelineContainer');
    container.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>';
    
    fetch(`get_status_logs.php?group_id=${encodeURIComponent(groupId)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.logs.length === 0) {
                    container.innerHTML = '<div class="text-center p-5 text-muted">No tracking history found for this group.</div>';
                } else {
                    let html = '<ul class="timeline">';
                    data.logs.forEach((log, index) => {
                        let icon = 'bi-info-circle';
                        let extraClass = '';
                        
                        if (log.status.includes('RFQ')) { icon = 'bi-file-earmark-text'; extraClass = 'latest-status-rfq'; }
                        if (log.status.includes('Order')) { icon = 'bi-cart-check'; extraClass = 'latest-status-order'; }
                        if (log.status.includes('Recieved')) { icon = 'bi-box-seam'; extraClass = 'latest-status-recieved'; }
                        if (log.status.includes('Case')) { icon = 'bi-folder-check'; extraClass = 'latest-status-case'; }
                        if (log.status.includes('Pending')) icon = 'bi-hourglass-split';
                        if (log.status.includes('Approved')) icon = 'bi-shield-check';
                        if (log.status.includes('Mailed')) icon = 'bi-envelope-check';
                        if (log.status.includes('PO')) icon = 'bi-file-earmark-check';

                        // Only apply extraClass to the first (latest) item
                        const itemClass = (index === 0) ? extraClass : '';

                        html += `
                            <li class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content ${itemClass}">
                                    <div class="timeline-status">
                                        <div class="timeline-icon-box">
                                            <i class="bi ${icon}"></i>
                                        </div>
                                        ${log.status}
                                    </div>
                                    <div class="timeline-date">
                                        <span><i class="bi bi-calendar3"></i> ${log.date}</span>
                                        <span><i class="bi bi-clock"></i> ${log.time}</span>
                                    </div>
                                </div>
                            </li>`;
                    });
                    html += '</ul>';
                    container.innerHTML = html;
                }
            } else {
                container.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
            }
        });

    new bootstrap.Modal(document.getElementById('timelineModal')).show();
}

// Sync chevron icon with collapse state and scroll into view
document.addEventListener('shown.bs.collapse', function(ev) {
    if (ev.target && ev.target.id && ev.target.id.startsWith('items-content-')) {
        const groupId = ev.target.id.replace('items-content-','');
        const btn = document.getElementById('chev-' + groupId);
        const row = btn ? btn.closest('.group-row') : null;

        if (btn) { btn.classList.add('open'); btn.setAttribute('aria-expanded','true'); }
        if (row) { row.classList.add('is-expanded'); }
        
        // Scroll the expanded items card into view smoothly
        setTimeout(() => {
            ev.target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }
});
document.addEventListener('hidden.bs.collapse', function(ev) {
    if (ev.target && ev.target.id && ev.target.id.startsWith('items-content-')) {
        const groupId = ev.target.id.replace('items-content-','');
        const btn = document.getElementById('chev-' + groupId);
        const row = btn ? btn.closest('.group-row') : null;

        if (btn) { btn.classList.remove('open'); btn.setAttribute('aria-expanded','false'); }
        if (row) { row.classList.remove('is-expanded'); }
    }
});

// Enable row click to toggle dropdown, ignoring interactive elements
document.querySelectorAll('tr.group-row').forEach(row => {
    row.addEventListener('click', function(e) {
        if (e.target.closest('button, a, input, .dropdown, .dropdown-menu')) return;
        const gid = this.getAttribute('data-group-id') || (this.querySelector('.group-id-badge')?.textContent || '').trim();
        if (gid) toggleGroupRow(gid);
    });
});

// Modal close handler to ensure all pending changes are saved
document.getElementById('quotationModal').addEventListener('hide.bs.modal', function() {
    document.querySelectorAll('#quote_body .price-input').forEach(input => {
        // Only trigger if value exists and it's different from last saved
        const colId = input.closest('td').dataset.colId;
        const vendorId = document.getElementById(`vendor_select_${colId}`)?.value;
        const price = input.value;
        
        if (vendorId && price && (input.dataset.lastSaved !== price || input.dataset.lastVendor !== vendorId)) {
            input.dispatchEvent(new Event('change'));
        }
    });
});
</script>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-question-circle text-primary fs-1"></i>
                </div>
                <h5 class="modal-title fw-bold mb-2" id="confirmTitle">Confirm Action</h5>
                <p class="text-muted mb-4" id="confirmText">Are you sure you want to proceed?</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light flex-grow-1 fw-bold rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary flex-grow-1 fw-bold rounded-3" id="confirmBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Prompt Modal -->
<div class="modal fade" id="promptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold" id="promptTitle">Input Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3" id="promptText">Please enter a value:</p>
                <input type="text" id="promptInput" class="form-control border-0 bg-light p-3 rounded-3 mb-3" placeholder="Type here...">
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-light flex-grow-1 fw-bold rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary flex-grow-1 fw-bold rounded-3" id="promptBtn">Submit</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
