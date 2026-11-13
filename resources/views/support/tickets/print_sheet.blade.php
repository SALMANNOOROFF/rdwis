<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SO IT Complaints Action Sheet - RDWIS 2.0</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* ======================================================== */
        /* RESET & BASE STYLING                                     */
        /* ======================================================== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
        }

        /* ======================================================== */
        /* TOP CONTROLS & FILTER BAR (SCREEN ONLY, HIDDEN ON PRINT) */
        /* ======================================================== */
        .controls-card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            padding: 16px 20px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        .controls-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .controls-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .controls-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            border: 1px solid transparent;
        }
        .btn-print {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
        }
        .btn-print:hover {
            background: #0369a1;
        }
        .btn-secondary {
            background: #ffffff;
            color: #475569;
            border-color: #cbd5e1;
        }
        .btn-secondary:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        /* Quick Filter Status Chips */
        .filter-chips-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .chip-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-right: 4px;
        }
        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #334155;
            transition: all 0.15s ease;
        }
        .filter-chip:hover {
            border-color: #94a3b8;
            background: #f8fafc;
        }
        .filter-chip.active {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.2);
        }
        .filter-chip.chip-pending.active {
            background: #ea580c;
            border-color: #ea580c;
            color: #ffffff;
        }
        .filter-chip.chip-progress.active {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }
        .filter-chip.chip-solved.active {
            background: #16a34a;
            border-color: #16a34a;
            color: #ffffff;
        }
        .filter-chip.chip-returned.active {
            background: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
        }
        .chip-count {
            display: inline-block;
            padding: 1px 6px;
            font-size: 10px;
            font-weight: 700;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.08);
            color: inherit;
        }
        .filter-chip.active .chip-count {
            background: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        /* Secondary Filter Inputs */
        .secondary-filters {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .filter-group label {
            font-size: 10.5px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .filter-select, .filter-input {
            padding: 5px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 12px;
            color: #1e293b;
            background: #ffffff;
            height: 32px;
            outline: none;
        }
        .filter-select:focus, .filter-input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }

        /* ======================================================== */
        /* PRINTABLE PAPER CONTAINER (A4 LANDSCAPE ORIENTATION)     */
        /* ======================================================== */
        .sheet-container {
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            padding: 24px 28px;
            margin: 0 auto;
            max-width: 1400px;
        }

        /* Sheet Official Header */
        .sheet-header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 14px;
            position: relative;
        }
        .sheet-crest {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 2px;
        }
        .sheet-main-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 3px;
        }
        .sheet-sub-title {
            font-size: 13px;
            font-weight: 700;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .sheet-meta-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 11px;
            color: #334155;
            flex-wrap: wrap;
            gap: 6px;
        }
        .sheet-meta-item strong {
            color: #0f172a;
        }

        /* Table Styling */
        table.sheet-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11.5px;
        }
        table.sheet-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.4px;
            padding: 8px 8px;
            border: 1px solid #0f172a;
            vertical-align: middle;
            text-align: left;
        }
        table.sheet-table td {
            padding: 8px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            line-height: 1.35;
        }
        table.sheet-table tr:nth-child(even) td {
            background: #fcfdfe;
        }

        /* Table Column Widths */
        .col-sr { width: 35px; text-align: center; }
        .col-ref { width: 110px; }
        .col-dept { width: 150px; }
        .col-mod { width: 120px; }
        .col-comp { width: auto; min-width: 260px; }
        .col-status { width: 95px; text-align: center; }
        .col-remarks { width: 220px; min-width: 200px; }

        /* Typography & Badges */
        .ref-code {
            font-family: monospace;
            font-weight: 700;
            font-size: 11px;
            color: #0f172a;
            display: block;
        }
        .ref-date {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .priority-tag {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 1px 5px;
            border-radius: 3px;
            margin-top: 3px;
        }
        .priority-urgent { background: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
        .priority-high { background: #ffedd5; color: #c2410c; border: 1px solid #fb923c; }
        .priority-normal { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        .dept-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 11.5px;
        }
        .user-name {
            font-size: 10.5px;
            color: #475569;
            margin-top: 2px;
        }

        .module-tag {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            font-size: 10.5px;
            border: 1px solid #cbd5e1;
        }
        .comp-type {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
            display: block;
        }

        .subject-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 12px;
            margin-bottom: 4px;
            line-height: 1.3;
        }
        .desc-text {
            color: #334155;
            font-size: 11px;
            white-space: pre-line;
            max-height: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 700;
            text-align: center;
        }
        .status-open { background: #ffedd5; color: #9a3412; border: 1px solid #fdba74; }
        .status-progress { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .status-returned { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .status-resolved { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-closed { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        /* THE EMPTY BOX FOR SO IT HANDWRITTEN REMARKS ON PAPER */
        .soit-remarks-box {
            border: 1px dashed #64748b;
            border-radius: 4px;
            min-height: 70px;
            padding: 6px 8px;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .remarks-header-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 0.5px dashed #cbd5e1;
            padding-bottom: 2px;
            margin-bottom: 4px;
        }
        .existing-resolution {
            font-size: 10px;
            color: #166534;
            background: #f0fdf4;
            border-radius: 3px;
            padding: 3px 5px;
            margin-bottom: 4px;
            border: 1px solid #bbf7d0;
        }
        .paper-lines {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            min-height: 40px;
        }
        .paper-line {
            border-bottom: 0.5px dotted #cbd5e1;
            height: 12px;
        }
        .sign-date-placeholder {
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            color: #94a3b8;
            margin-top: 4px;
            padding-top: 2px;
            border-top: 0.5px solid #e2e8f0;
        }

        /* Signatures Section at the Bottom */
        .signatures-section {
            margin-top: 35px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
            padding: 0 30px;
        }
        .signature-block {
            width: 230px;
            text-align: center;
        }
        .sig-line {
            border-top: 1.5px solid #0f172a;
            padding-top: 6px;
            font-weight: 700;
            font-size: 11.5px;
            color: #0f172a;
        }
        .sig-title {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Footer */
        .sheet-footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        /* Empty state */
        .no-records {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        /* ======================================================== */
        /* PRINT MEDIA STYLES (@media print)                        */
        /* ======================================================== */
        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm 6mm 8mm 6mm;
            }
            body {
                background: #ffffff !important;
                padding: 0 !important;
                color: #000000 !important;
                font-size: 10.5px !important;
            }
            .no-print {
                display: none !important;
            }
            .sheet-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .sheet-header {
                border-bottom: 2px solid #000 !important;
            }
            .sheet-meta-bar {
                background: #ffffff !important;
                border: 1px solid #000 !important;
                color: #000 !important;
            }
            table.sheet-table {
                border: 1px solid #000 !important;
            }
            table.sheet-table th {
                background: #e2e8f0 !important;
                color: #000000 !important;
                border: 1px solid #000000 !important;
                font-size: 10px !important;
            }
            table.sheet-table td {
                border: 1px solid #000000 !important;
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 10.5px !important;
            }
            table.sheet-table tr {
                page-break-inside: avoid !important;
            }
            thead {
                display: table-header-group !important;
            }
            .soit-remarks-box {
                border: 1px solid #000000 !important;
                background: #ffffff !important;
                min-height: 65px !important;
            }
            .remarks-header-label {
                color: #000000 !important;
                border-bottom: 0.5px solid #000000 !important;
            }
            .paper-line {
                border-bottom: 0.5px dotted #666666 !important;
            }
            .signatures-section {
                margin-top: 30px !important;
                page-break-inside: avoid !important;
            }
            .sig-line {
                border-top: 1.5px solid #000000 !important;
            }
            .status-badge, .priority-tag, .module-tag {
                border: 1px solid #000000 !important;
                color: #000000 !important;
                background: transparent !important;
            }
        }
    </style>
</head>
<body>

    {{-- ======================================================== --}}
    {{-- TOP INTERACTIVE FILTER TOOLBAR (SCREEN ONLY)             --}}
    {{-- ======================================================== --}}
    <div class="controls-card no-print">
        <div class="controls-header">
            <div class="controls-title">
                <i class="fas fa-headset text-primary" style="color: #0284c7;"></i>
                <span>SO IT Helpdesk & Resolve Desk — Print Complaints Register</span>
            </div>
            <div class="controls-actions">
                <button type="button" class="btn btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Sheet (Ctrl + P)
                </button>
                <button type="button" class="btn btn-secondary" onclick="resetAllFilters()">
                    <i class="fas fa-undo"></i> Reset Filters
                </button>
                <a href="{{ route('support.tickets.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Helpdesk
                </a>
            </div>
        </div>

        {{-- QUICK STATUS FILTER CHIPS --}}
        <div class="filter-chips-row">
            <span class="chip-label"><i class="fas fa-filter mr-1"></i> Quick Filter:</span>
            <button type="button" class="filter-chip {{ ($status == 'all' || !$status) ? 'active' : '' }}" data-status="all" onclick="applyStatusFilter('all', this)">
                <i class="fas fa-list-ul"></i> All Complaints
                <span class="chip-count">{{ $counts['all'] }}</span>
            </button>
            <button type="button" class="filter-chip chip-pending {{ ($status == 'pending' || $status == 'open') ? 'active' : '' }}" data-status="pending" onclick="applyStatusFilter('pending', this)">
                <i class="fas fa-clock"></i> Pending Complaints (Open)
                <span class="chip-count">{{ $counts['pending'] }}</span>
            </button>
            <button type="button" class="filter-chip chip-progress {{ ($status == 'in_progress') ? 'active' : '' }}" data-status="in_progress" onclick="applyStatusFilter('in_progress', this)">
                <i class="fas fa-spinner"></i> Work In Progress
                <span class="chip-count">{{ $counts['in_progress'] }}</span>
            </button>
            <button type="button" class="filter-chip chip-returned {{ ($status == 'returned') ? 'active' : '' }}" data-status="returned" onclick="applyStatusFilter('returned', this)">
                <i class="fas fa-reply"></i> Returned
                <span class="chip-count">{{ $counts['returned'] }}</span>
            </button>
            <button type="button" class="filter-chip chip-solved {{ ($status == 'resolved' || $status == 'solved') ? 'active' : '' }}" data-status="resolved" onclick="applyStatusFilter('resolved', this)">
                <i class="fas fa-check-circle"></i> Solved Complaints
                <span class="chip-count">{{ $counts['resolved'] }}</span>
            </button>
        </div>

        {{-- SECONDARY FILTERS: DEPARTMENT, MODULE, PRIORITY, SEARCH --}}
        <div class="secondary-filters">
            <div class="filter-group">
                <label>Department / Directorate</label>
                <select id="filterDept" class="filter-select" onchange="filterTableRows()">
                    <option value="all">All Departments ({{ count($departments) }})</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->unt_id }}" {{ request('unt_id') == $d->unt_id ? 'selected' : '' }}>{{ $d->unt_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Affected Module</label>
                <select id="filterModule" class="filter-select" onchange="filterTableRows()">
                    <option value="all">All Modules</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label>Priority</label>
                <select id="filterPriority" class="filter-select" onchange="filterTableRows()">
                    <option value="all">All Priorities</option>
                    <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                    <option value="Normal" {{ request('priority') == 'Normal' ? 'selected' : '' }}>Normal</option>
                </select>
            </div>

            <div class="filter-group" style="flex-grow: 1; min-width: 200px;">
                <label>Quick Search (Subject / Text / User)</label>
                <input type="text" id="filterSearch" class="filter-input" placeholder="Type keywords..." onkeyup="filterTableRows()">
            </div>
        </div>
    </div>

    {{-- ======================================================== --}}
    {{-- PRINTABLE PAPER DOCUMENT                                 --}}
    {{-- ======================================================== --}}
    <div class="sheet-container">

        {{-- Official Sheet Header --}}
        <div class="sheet-header">
            <div class="sheet-crest">RDWIS 2.0)</div>
            <div class="sheet-main-title">RESARCH AND DEVELOPMENT WING INFORMATION SYSTEM (RDWIS 2.0)</div>
            <div class="sheet-sub-title">SO IT RESOLVE DESK &mdash; COMPLAINTS & SUGGESTIONS </div>
            
            <div class="sheet-meta-bar">
                <div class="sheet-meta-item">
                    <span>Active Filter: </span>
                    <strong id="metaFilterLabel">
                        @if($status == 'pending' || $status == 'open')
                            Pending Complaints (Open)
                        @elseif($status == 'in_progress')
                            Work In Progress
                        @elseif($status == 'resolved' || $status == 'solved')
                            Solved Complaints
                        @elseif($status == 'returned')
                            Returned
                        @else
                            All Complaints
                        @endif
                    </strong>
                </div>
                <div class="sheet-meta-item">
                    <span>Department: </span>
                    <strong id="metaDeptLabel">All Departments</strong>
                </div>
                <div class="sheet-meta-item">
                    <span>Generated On: </span>
                    <strong>{{ now()->format('d-M-Y h:i A') }}</strong>
                </div>
                <div class="sheet-meta-item">
                    <span>Desk Officer: </span>
                    <strong>SO IT ({{ $user->acc_username }})</strong>
                </div>
                <div class="sheet-meta-item">
                    <span>Total Visible Records: </span>
                    <strong id="metaCountLabel">{{ count($tickets) }}</strong>
                </div>
            </div>
        </div>

        {{-- Complaints Table --}}
        <table class="sheet-table" id="complaintsTable">
            <thead>
                <tr>
                    <th class="col-sr">#</th>
                    <th class="col-ref">Ref # & Date</th>
                    <th class="col-dept">Department & User</th>
                    <th class="col-mod">Module</th>
                    <th class="col-comp">Complain Subject & Details</th>
                    <th class="col-status">Status</th>
                    <th class="col-remarks">SO IT Remarks / Action (Paper Box)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $index => $t)
                    @php
                        $rawStatus = $t->tkt_status;
                        $statusCategory = match($rawStatus) {
                            'Open' => 'pending',
                            'In Progress' => 'in_progress',
                            'Returned' => 'returned',
                            'Resolved', 'Closed' => 'resolved',
                            default => 'other'
                        };

                        $stClass = match($rawStatus) {
                            'Open' => 'status-open',
                            'In Progress' => 'status-progress',
                            'Returned' => 'status-returned',
                            'Resolved' => 'status-resolved',
                            'Closed' => 'status-closed',
                            default => 'status-open'
                        };

                        $prioClass = match($t->tkt_priority) {
                            'Urgent' => 'priority-urgent',
                            'High' => 'priority-high',
                            default => 'priority-normal'
                        };
                    @endphp
                    <tr class="complaint-row"
                        data-status="{{ $statusCategory }}"
                        data-rawstatus="{{ $rawStatus }}"
                        data-dept-id="{{ $t->tkt_unt_id ?? 0 }}"
                        data-dept-name="{{ strtolower($t->tkt_unt_name ?? $t->unit?->unt_name ?? '') }}"
                        data-module="{{ strtolower($t->tkt_module ?? '') }}"
                        data-priority="{{ $t->tkt_priority ?? 'Normal' }}"
                        data-search="{{ strtolower($t->tkt_ref . ' ' . $t->tkt_subject . ' ' . $t->tkt_description . ' ' . $t->tkt_user_name . ' ' . ($t->tkt_unt_name ?? '')) }}">
                        
                        {{-- Serial Number --}}
                        <td class="col-sr text-center sr-cell">
                            <strong>{{ $index + 1 }}</strong>
                        </td>

                        {{-- Ref & Date --}}
                        <td class="col-ref">
                            <span class="ref-code">{{ $t->tkt_ref }}</span>
                            <div class="ref-date">{{ $t->tkt_created_at ? $t->tkt_created_at->format('d-M-Y h:i A') : 'N/A' }}</div>
                            <span class="priority-tag {{ $prioClass }}">
                                {{ $t->tkt_priority ?? 'Normal' }}
                            </span>
                            @if($t->tkt_is_apex)
                                <div style="font-size: 8.5px; font-weight: 800; color: #b45309; margin-top: 2px;">
                                    ★ APEX DIRECTIVE
                                </div>
                            @endif
                        </td>

                        {{-- Department & User --}}
                        <td class="col-dept">
                            <div class="dept-name">{{ $t->tkt_unt_name ?? ($t->unit?->unt_name ?? 'Headquarters') }}</div>
                            <div class="user-name">
                                <i class="fas fa-user-edit mr-0.5" style="font-size: 9px;"></i> 
                                <strong>{{ $t->tkt_user_name }}</strong>
                            </div>
                            <div style="font-size: 9.5px; color: #64748b;">{{ $t->tkt_user_role }}</div>
                        </td>

                        {{-- Module --}}
                        <td class="col-mod">
                            <span class="module-tag">{{ $t->tkt_module }}</span>
                            <span class="comp-type">{{ $t->tkt_type ?? 'Complaint' }}</span>
                        </td>

                        {{-- Complain Subject & Description --}}
                        <td class="col-comp">
                            <div class="subject-title">{{ $t->tkt_subject }}</div>
                            <div class="desc-text">{{ $t->tkt_description }}</div>
                            @if($t->tkt_attachment)
                                <div style="margin-top: 4px; font-size: 9.5px; color: #0284c7;">
                                    <i class="fas fa-paperclip"></i> Attachment provided on portal
                                </div>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td class="col-status">
                            <span class="status-badge {{ $stClass }}">
                                {{ $rawStatus }}
                            </span>
                            @if($t->tkt_solved_at)
                                <div style="font-size: 9px; color: #166534; margin-top: 3px;">
                                    {{ $t->tkt_solved_at->format('d-M-Y') }}
                                </div>
                            @endif
                        </td>

                        {{-- SO IT Remarks & Action Taken (Empty box for paper handwriting) --}}
                        <td class="col-remarks">
                            <div class="soit-remarks-box">
                                <div class="remarks-header-label">SO IT Remarks / Action Taken:</div>
                                
                                @if(!empty($t->tkt_resolution_note))
                                    <div class="existing-resolution">
                                        <strong>Recorded:</strong> {{ Str::limit($t->tkt_resolution_note, 100) }}
                                    </div>
                                @endif

                                <div class="paper-lines">
                                    <div class="paper-line"></div>
                                    <div class="paper-line"></div>
                                    <div class="paper-line"></div>
                                </div>

                                <div class="sign-date-placeholder">
                                    <span>Initials: __________</span>
                                    <span>Date: ___/___/2026</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="emptyTableRow">
                        <td colspan="7" class="no-records">
                            <i class="fas fa-inbox" style="font-size: 28px; margin-bottom: 6px; color: #94a3b8;"></i>
                            <div><strong>No complaints found matching this criteria.</strong></div>
                        </td>
                    </tr>
                @endforelse
                <tr id="noResultsFilterRow" style="display: none;">
                    <td colspan="7" class="no-records">
                        <i class="fas fa-filter" style="font-size: 28px; margin-bottom: 6px; color: #94a3b8;"></i>
                        <div><strong>No complaints match the selected filter combination.</strong></div>
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Signatures Section for Official Paper Printout --}}
        <div class="signatures-section">
            <div class="signature-block">
                <div class="sig-line">Prepared & Checked By</div>
                <div class="sig-title">IT Support Assistant / Resolve Desk</div>
            </div>
            <div class="signature-block">
                <div class="sig-line">Section Officer (IT)</div>
                <div class="sig-title">Incharge Support & Complaints Desk</div>
            </div>
            <div class="signature-block">
                <div class="sig-line">Director General (DG) / Oversight</div>
                <div class="sig-title">RDWIS 2.0 Directorate</div>
            </div>
        </div>

        {{-- Official Document Footer --}}
        <div class="sheet-footer">
            <div>
                <span>RDWIS 2.0 &bull; Section Officer IT Grievance Management Module</span>
            </div>
            <div>
                <span>Printed on: {{ now()->format('d-M-Y h:i:s A') }} &bull; Page 1 of 1</span>
            </div>
        </div>

    </div>

    {{-- ======================================================== --}}
    {{-- JAVASCRIPT FOR DYNAMIC INSTANT FILTERING                 --}}
    {{-- ======================================================== --}}
    <script>
        let currentStatusFilter = '{{ $status ?: "all" }}';

        function applyStatusFilter(status, chipElement) {
            currentStatusFilter = status;

            // Highlight chip
            document.querySelectorAll('.filter-chip').forEach(el => el.classList.remove('active'));
            if (chipElement) {
                chipElement.classList.add('active');
            }

            // Update meta label
            const labelMap = {
                'all': 'All Complaints',
                'pending': 'Pending Complaints (Open)',
                'in_progress': 'Work In Progress',
                'returned': 'Returned',
                'resolved': 'Solved Complaints'
            };
            const label = labelMap[status] || 'All Complaints';
            document.getElementById('metaFilterLabel').innerText = label;

            filterTableRows();
        }

        function filterTableRows() {
            const selectedDeptId = document.getElementById('filterDept').value;
            const selectedDeptText = document.getElementById('filterDept').options[document.getElementById('filterDept').selectedIndex].text;
            const selectedModule = document.getElementById('filterModule').value.toLowerCase();
            const selectedPriority = document.getElementById('filterPriority').value;
            const searchText = document.getElementById('filterSearch').value.toLowerCase().trim();

            document.getElementById('metaDeptLabel').innerText = (selectedDeptId === 'all') ? 'All Departments' : selectedDeptText;

            const rows = document.querySelectorAll('.complaint-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowDeptId = row.getAttribute('data-dept-id');
                const rowModule = row.getAttribute('data-module');
                const rowPriority = row.getAttribute('data-priority');
                const rowSearch = row.getAttribute('data-search');

                // Status match
                let statusMatch = false;
                if (currentStatusFilter === 'all') {
                    statusMatch = true;
                } else if (currentStatusFilter === 'pending' && rowStatus === 'pending') {
                    statusMatch = true;
                } else if (currentStatusFilter === 'in_progress' && rowStatus === 'in_progress') {
                    statusMatch = true;
                } else if (currentStatusFilter === 'returned' && rowStatus === 'returned') {
                    statusMatch = true;
                } else if (currentStatusFilter === 'resolved' && rowStatus === 'resolved') {
                    statusMatch = true;
                }

                // Dept match
                let deptMatch = (selectedDeptId === 'all' || rowDeptId === selectedDeptId);

                // Module match
                let moduleMatch = (selectedModule === 'all' || rowModule === selectedModule);

                // Priority match
                let priorityMatch = (selectedPriority === 'all' || rowPriority === selectedPriority);

                // Search match
                let searchMatch = (!searchText || rowSearch.includes(searchText));

                if (statusMatch && deptMatch && moduleMatch && priorityMatch && searchMatch) {
                    row.style.display = '';
                    visibleCount++;
                    // Update dynamic serial number
                    const srCell = row.querySelector('.sr-cell strong');
                    if (srCell) srCell.innerText = visibleCount;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update visible counter
            document.getElementById('metaCountLabel').innerText = visibleCount;

            // Show or hide empty filter row
            const noResultsRow = document.getElementById('noResultsFilterRow');
            if (noResultsRow) {
                noResultsRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
            }
        }

        function resetAllFilters() {
            document.getElementById('filterDept').value = 'all';
            document.getElementById('filterModule').value = 'all';
            document.getElementById('filterPriority').value = 'all';
            document.getElementById('filterSearch').value = '';

            const allChip = document.querySelector('.filter-chip[data-status="all"]');
            applyStatusFilter('all', allChip);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            filterTableRows();
        });
    </script>
</body>
</html>
