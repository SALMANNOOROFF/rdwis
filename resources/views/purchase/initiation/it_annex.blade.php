<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Request for Quotation & IT Annex - Case #{{ $purchase->pcs_id }}</title>
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    
    <style>
        /* ================= GLOBAL & RESET ================= */
        * {
            box-sizing: border-box;
        }
        body {
            background-color: #dbe2ea;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 12pt;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ================= TOP ACTION BAR (SCREEN ONLY) ================= */
        .top-action-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            background: var(--rd-surface2);
            color: #fff;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 99999;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }
        .top-bar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .top-bar-left .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #90caf9;
            text-decoration: none;
            font-weight: bold;
            font-size: 12px;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.2s;
        }
        .top-bar-left .back-link:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .top-bar-title {
            font-size: 14px;
            font-weight: bold;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .save-indicator {
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: bold;
            background: rgba(40, 167, 69, 0.25);
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.5);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .save-indicator.unsaved {
            background: rgba(255, 193, 7, 0.25);
            color: #fde047;
            border-color: rgba(253, 224, 71, 0.5);
        }
        .save-indicator.saving {
            background: rgba(13, 110, 253, 0.25);
            color: #60a5fa;
            border-color: rgba(96, 165, 250, 0.5);
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
        }
        .btn-save {
            background: #10b981;
            color: #fff;
        }
        .btn-save:hover {
            background: #059669;
        }
        .btn-print {
            background: var(--rd-primary-700);
            color: #fff;
        }
        .btn-print:hover {
            background: var(--rd-primary-600);
        }
        .btn-reset {
            background: rgba(255, 255, 255, 0.12);
            color: var(--rd-text1);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .btn-reset:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        /* ================= DOCUMENT CANVAS (STANDARD WORD 1-INCH MARGINS) ================= */
        .document-wrapper {
            padding: 25px 15px 50px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        /* A4 Page Simulation on Screen with Word-Document 1 inch (25.4mm) padding */
        .a4-page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            padding: 25.4mm 25.4mm 25.4mm 25.4mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            position: relative;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        /* Toast notification */
        .toast-msg {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 12px 20px;
            background: var(--rd-surface);
            color: #fff;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border-left: 4px solid #10b981;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.25s ease-out;
            z-index: 100000;
            pointer-events: none;
        }
        .toast-msg.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        /* ================= EDITABLE STYLING ================= */
        [contenteditable="true"] {
            outline: none;
            transition: background-color 0.15s, box-shadow 0.15s;
        }
        body:not(.printing) [contenteditable="true"]:hover {
            background-color: var(--rd-text1);
            box-shadow: 0 0 0 1px #cbd5e1;
            border-radius: 2px;
        }
        body:not(.printing) [contenteditable="true"]:focus {
            background-color: var(--rd-text1);
            box-shadow: 0 0 0 2px var(--rd-primary-700);
            border-radius: 2px;
        }

        /* Paragraphs with sub-bullets Tab Stop indentation */
        .para-wrapper {
            position: relative;
            margin-bottom: 14pt;
        }
        .editable-para {
            margin: 0;
            text-align: justify;
            white-space: pre-wrap;
            tab-size: 4;
            -moz-tab-size: 4;
            font-size: 12pt;
            line-height: 1.5;
            font-family: Arial, Helvetica, sans-serif;
        }
        .btn-del-para {
            position: absolute;
            top: 2px;
            right: -24px;
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #f87171;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            cursor: pointer;
            padding: 0;
            opacity: 0;
            transition: opacity 0.15s;
        }
        .para-wrapper:hover .btn-del-para {
            opacity: 1;
        }
        .btn-del-para:hover {
            background: #dc2626;
            color: #fff;
        }

        .btn-add-para {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            border: 1px dashed #64748b;
            color: #334155;
            padding: 6px 14px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 4px;
            margin-bottom: 20px;
            transition: all 0.15s;
        }
        .btn-add-para:hover {
            background: #e2e8f0;
            color: #0f172a;
            border-color: #334155;
        }

        /* ================= PAGE 1: RFQ LETTER ================= */
        .letter-header {
            margin-bottom: 22pt;
            font-size: 12pt;
            line-height: 1.35;
        }
        .header-top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .header-top-left {
            width: 48%;
        }
        .header-top-right {
            width: 50%;
            line-height: 1.35;
        }
        .header-top-right div {
            margin-bottom: 1px;
        }
        .header-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 14pt;
        }
        .meta-col-left {
            width: 48%;
        }
        .meta-col-right {
            width: 50%;
        }
        .meta-item {
            line-height: 1.35;
            margin-bottom: 4pt;
        }

        .letter-subject {
            font-size: 12pt;
            font-weight: bold;
            margin: 20pt 0 16pt 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Signatory */
        .signatory-wrapper {
            margin-top: 28pt;
            margin-bottom: 24pt;
            display: flex;
            justify-content: flex-end;
            padding-right: 20px;
        }
        .signatory-box {
            display: block;
            text-align: left;
            font-size: 12pt;
            line-height: 1.35;
            min-width: 220px;
        }
        .signatory-box .sig-name {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 1px;
        }
        .signatory-box .sig-rank {
            margin-bottom: 1px;
            text-decoration: none !important;
            font-weight: normal;
        }
        .signatory-box .sig-dept {
        }

        /* "To:" Distribution Section */
        .dist-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }
        .dist-heading-wrap {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .dist-heading {
            font-size: 12pt;
            font-weight: normal;
        }
        .firm-count-pill {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 9px;
            border-radius: 12px;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .dist-actions {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .btn-firm-act {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-firm-act:hover {
            background: #e2e8f0;
            color: #0f172a;
            border-color: var(--rd-text3);
        }
        .btn-firm-act.btn-add-all {
            background: #f0fdf4;
            color: #15803d;
            border-color: #bbf7d0;
        }
        .btn-firm-act.btn-add-all:hover {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }
        .btn-firm-act.btn-add-case {
            background: #fefce8;
            color: #a16207;
            border-color: #fef08a;
        }
        .btn-firm-act.btn-add-case:hover {
            background: #fef9c3;
            color: #854d0e;
            border-color: #fde047;
        }
        .btn-firm-act.btn-clear-all {
            background: #fff1f2;
            color: #e11d48;
            border-color: #fecdd3;
        }
        .btn-firm-act.btn-clear-all:hover {
            background: #ffe4e6;
            color: #be123c;
            border-color: #fda4af;
        }

        .firms-vertical-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            max-width: 600px;
        }
        .firms-vertical-list.grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 20px;
            max-width: 100%;
        }
        .firm-entry {
            font-size: 11pt;
            line-height: 1.35;
            position: relative;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .firm-entry .firm-hdr {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .firm-entry .f-name {
            font-weight: bold;
            color: #000;
            display: block;
            margin-bottom: 2px;
        }
        .firm-entry .f-addr {
            color: #111;
            display: block;
            margin-bottom: 2px;
        }
        .firm-entry .f-tel {
            color: #111;
            display: block;
        }
        .btn-del-firm {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #f87171;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            cursor: pointer;
            padding: 0;
            margin-left: 6px;
            opacity: 0.5;
            transition: all 0.15s;
        }
        .firm-entry:hover .btn-del-firm {
            opacity: 1;
        }
        .btn-del-firm:hover {
            background: #dc2626;
            color: #fff;
        }

        /* Firm Search Box on Screen */
        .firm-search-bar {
            margin-top: 20px;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            max-width: 600px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .firm-search-input-wrap {
            position: relative;
            flex: 1;
            display: flex;
            align-items: center;
        }
        .firm-search-input-wrap input {
            width: 100%;
            border: 1px solid #94a3b8;
            padding: 7px 32px 7px 12px;
            border-radius: 5px;
            font-size: 12px;
            outline: none;
            background: #fff;
            color: #0f172a;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .firm-search-input-wrap input:focus {
            border-color: var(--rd-primary-700);
            box-shadow: 0 0 0 3px rgba(95,120,88,0.15);
        }
        .btn-clear-search {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: var(--rd-text3);
            cursor: pointer;
            padding: 2px 4px;
            font-size: 12px;
            display: none;
        }
        .btn-clear-search:hover {
            color: #334155;
        }

        .firm-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            margin-top: 5px;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-height: 380px;
            overflow-y: auto;
            z-index: 10000;
            display: none;
        }
        .firm-dropdown-header {
            position: sticky;
            top: 0;
            background: #f1f5f9;
            padding: 8px 14px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11.5px;
            color: #475569;
            z-index: 2;
        }
        .btn-dropdown-add-all {
            background: var(--rd-primary-700);
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background 0.15s;
        }
        .btn-dropdown-add-all:hover {
            background: var(--rd-primary-600);
        }
        .firm-res-item {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            font-size: 11.5px;
            display: flex;
            flex-direction: column;
            gap: 3px;
            transition: background 0.12s;
        }
        .firm-res-item:last-child {
            border-bottom: none;
        }
        .firm-res-item:hover {
            background: #f0f7ff;
        }
        .firm-res-item.is-selected {
            background: #f0fdf4;
        }
        .firm-res-hdr {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .firm-res-hdr strong {
            color: #0f172a;
            font-size: 12px;
        }
        .firm-res-badges {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .badge-tag {
            font-size: 9.5px;
            font-weight: 600;
            padding: 1px 6px;
            border-radius: 3px;
            white-space: nowrap;
        }
        .badge-case {
            background: #fef08a;
            color: #854d0e;
            border: 1px solid #fde047;
        }
        .badge-city {
            background: #e0f2fe;
            color: #0369a1;
        }
        .badge-ntn {
            background: #f1f5f9;
            color: #475569;
        }
        .badge-added {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .firm-res-details {
            color: var(--rd-text3);
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* ================= PAGE 2: IT ANNEX A ================= */
        .it-annex-header {
            text-align: right;
            margin-bottom: 22pt;
            font-size: 12pt;
            line-height: 1.45;
        }
        .it-annex-header .annex-line {
            font-weight: bold;
        }

        .annex-heading {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 22pt;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* ANNEX TABLE - 3 COLUMNS: S No, Item / specification, Qty */
        table.annex-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
            font-size: 11pt;
        }
        table.annex-table th, table.annex-table td {
            border: 1px solid #555;
            padding: 7px 10px;
            font-size: 11pt;
            vertical-align: middle;
            line-height: 1.4;
        }
        table.annex-table th {
            background-color: #f6f6f6;
            font-weight: bold;
            text-align: center;
        }
        .t-center { text-align: center; }
        .t-left { text-align: left; }
        .t-right { text-align: right; }

        .btn-add-table-row {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            border: 1px dashed #64748b;
            color: #334155;
            padding: 5px 12px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
            transition: all 0.15s;
        }
        .btn-add-table-row:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .btn-del-row {
            background: none;
            border: none;
            color: #dc2626;
            cursor: pointer;
            padding: 2px 4px;
            font-size: 11px;
            border-radius: 3px;
        }
        .btn-del-row:hover {
            background: #fee2e2;
        }

        .annex-footer {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10pt;
            color: #444;
        }

        /* ================= PRINT MEDIA ================= */
        @media print {
            @page {
                size: auto;
                margin: 0mm;
            }
            html, body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 12pt !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print,
            .top-action-bar,
            .toast-msg,
            .dist-actions,
            .firm-count-pill,
            .firm-search-bar,
            .btn-del-firm,
            .btn-add-para,
            .btn-del-para,
            .btn-add-table-row,
            .col-act,
            .btn-del-row {
                display: none !important;
            }
            .document-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                gap: 0 !important;
            }
            .a4-page {
                box-shadow: none !important;
                border: none !important;
                padding: 20mm 20mm 20mm 20mm !important;
                margin: 0 auto !important;
                width: 100% !important;
                min-height: auto !important;
                box-sizing: border-box !important;
            }
            .firm-entry {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .para-wrapper {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
                margin-top: 0 !important;
                padding-top: 20mm !important;
            }
            [contenteditable="true"] {
                background: transparent !important;
                box-shadow: none !important;
                outline: none !important;
            }
            table.annex-table th {
                background-color: #f0f0f0 !important;
            }
            table.annex-table th, table.annex-table td {
                border: 1px solid #000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- TOP ACTION BAR (SCREEN ONLY) -->
    <div class="top-action-bar no-print">
        <div class="top-bar-left">
            <a href="{{ url('/nrdi/purchase-cases-new/' . $purchase->pcs_id) }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Case
            </a>
            <div class="top-bar-title">
                <span>Case #{{ $purchase->pcs_id }}</span>
                <span style="color: var(--rd-text3); font-weight: normal; font-size: 13px;">— Request for Quotation & IT Annex</span>
                <span class="save-indicator" id="saveIndicator">
                    <i class="fas fa-check-circle"></i> Saved
                </span>
            </div>
        </div>

        <div class="top-bar-actions">
            <button type="button" class="action-btn btn-reset" onclick="resetDefaults()" title="Reset to standard naval template text">
                <i class="fas fa-undo"></i> Reset Template
            </button>
            <button type="button" class="action-btn btn-save" id="btnSaveDoc" onclick="saveDocument()">
                <i class="fas fa-save"></i> Save Document
            </button>
            <button type="button" class="action-btn btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Document
            </button>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div class="toast-msg" id="toastMsg">
        <i class="fas fa-check-circle" style="font-size: 16px; color: #34d399;"></i>
        <span id="toastText">Letter and Annex saved successfully!</span>
    </div>

    <!-- DOCUMENT CONTAINER -->
    <div class="document-wrapper">

        <!-- ================= PAGE 1: RFQ LETTER ================= -->
        <div class="a4-page" id="pageLetter">

            <!-- HEADER -->
            <div class="letter-header">
                <div class="header-top-row">
                    <div class="header-top-left"></div>
                    <div class="header-top-right">
                        <div style="font-weight: bold;">Naval Research & Development Institute</div>
                        <div>R&D Wing</div>
                        <div>at PNS JAUHAR</div>
                        <div>Habib Rehmatullah Road</div>
                        <div>KARACHI</div>
                    </div>
                </div>

                <div class="header-meta-row">
                    <div class="meta-col-left">
                        <div class="meta-item">
                            R&D/Projects/Proc/<span contenteditable="true" id="ref_no_suffix" oninput="syncRefSuffix(this.innerText)">{{ $refSuffix }}</span>
                        </div>
                        <div class="meta-item">
                            <span contenteditable="true" id="see_distribution" oninput="markUnsaved()">{{ $seeDistribution ?? 'See distribution' }}</span>
                        </div>
                    </div>

                    <div class="meta-col-right">
                        <div class="meta-item">
                            Ph (off): 021-48503038
                        </div>
                        <div class="meta-item">
                            <span contenteditable="true" id="letter_date" oninput="syncDate(this.innerText)">{{ $letterDate }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUBJECT -->
            <div class="letter-subject">
                <u><span contenteditable="true" id="subject">{{ $subject }}</span></u>
            </div>

            <!-- BODY PARAGRAPHS -->
            <div class="letter-body" id="letterParagraphs">
                @foreach($paragraphs as $pIndex => $pText)
                <div class="para-wrapper">
                    <div class="editable-para" contenteditable="true" oninput="markUnsaved()">{{ $pText }}</div>
                    @if($pIndex > 0)
                    <button type="button" class="btn-del-para no-print" onclick="removeParagraph(this)" title="Delete Paragraph">
                        <i class="fas fa-times"></i>
                    </button>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- ADD PARAGRAPH BUTTON (SCREEN ONLY) -->
            <div class="no-print" style="margin-top: 2px; margin-bottom: 18px;">
                <button type="button" class="btn-add-para" onclick="addParagraph()">
                    <i class="fas fa-plus"></i> Add Paragraph
                </button>
            </div>

            <!-- SIGNATORY -->
            <div class="signatory-wrapper">
                <div class="signatory-box">
                    <div class="sig-name" contenteditable="true" id="signatory_name" oninput="markUnsaved()">{{ $signatoryName }}</div>
                    <div class="sig-rank" contenteditable="true" id="signatory_rank" oninput="markUnsaved()">{{ $signatoryRank }}</div>
                    <div class="sig-dept" contenteditable="true" id="signatory_dept" oninput="markUnsaved()">{{ $signatoryDept }}</div>
                </div>
            </div>

            <!-- "To:" DISTRIBUTION SECTION -->
            <div class="distribution-area">
                <div class="dist-header-row">
                    <div class="dist-heading-wrap">
                        <span class="dist-heading">To:</span>
                        <span class="firm-count-pill no-print" id="firmCountBadge">{{ count($selectedFirms) }} Firms</span>
                    </div>

                    <div class="dist-actions no-print">
                        <button type="button" class="btn-firm-act btn-add-all" onclick="addAllSystemFirms()" title="Add all registered firms from database into this distribution list">
                            <i class="fas fa-database"></i> Add All System Firms ({{ count($firmsDirectory) }})
                        </button>
                        <button type="button" class="btn-firm-act btn-add-case" onclick="addCaseQuotedFirms()" title="Add firms quoted in this case">
                            <i class="fas fa-star"></i> Add Case Quoted Firms
                        </button>
                        <button type="button" class="btn-firm-act btn-clear-all" onclick="clearAllFirms()" title="Clear all firms from distribution list">
                            <i class="fas fa-trash-alt"></i> Clear All
                        </button>
                        <button type="button" class="btn-firm-act" id="btnToggleLayout" onclick="toggleFirmsLayout()" title="Switch between 1-Column and 2-Column Grid view">
                            <i class="fas fa-columns"></i> <span id="layoutToggleText">2-Col Grid</span>
                        </button>
                    </div>
                </div>

                <!-- FIRM LIST -->
                <div class="firms-vertical-list" id="firmsList">
                    @foreach($selectedFirms as $f)
                    <div class="firm-entry" data-id="{{ $f['id'] ?? '' }}">
                        <div class="firm-hdr">
                            <span class="f-name" contenteditable="true" oninput="markUnsaved()">{{ $f['name'] }}</span>
                            <button type="button" class="btn-del-firm no-print" onclick="removeFirm(this)" title="Remove Firm">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <span class="f-addr" contenteditable="true" oninput="markUnsaved()">{{ $f['address'] ?: 'Karachi, Pakistan' }}</span>
                        <span class="f-tel" contenteditable="true" oninput="markUnsaved()">Tel: {{ $f['tel'] ?: 'N/A' }}</span>
                    </div>
                    @endforeach
                </div>

                <!-- FIRM SEARCH BAR (SCREEN ONLY) -->
                <div class="firm-search-bar no-print">
                    <i class="fas fa-search" style="color: var(--rd-text3); font-size: 13px;"></i>
                    <div class="firm-search-input-wrap">
                        <input type="text" 
                               id="firmSearchInput" 
                               placeholder="Search & add firm from system database (type name, city, NTN)..."
                               autocomplete="off"
                               onfocus="showFirmResults()"
                               oninput="handleSearchInput(this.value)">
                        <button type="button" class="btn-clear-search" id="btnClearSearch" onclick="clearSearchInput()" title="Clear Search">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </div>
                    <div class="firm-search-results" id="firmDropdownResults">
                        <!-- Filled by JS -->
                    </div>
                </div>
            </div>

        </div>


        <!-- ================= PAGE 2: IT ANNEX A ================= -->
        <div class="a4-page page-break" id="pageAnnex">

            <!-- ANNEX TOP RIGHT -->
            <div class="it-annex-header">
                <div class="annex-line"><u>ANNEX A</u></div>
                <div class="annex-line"><u>TO IT NO R&D/Projects/Proc/<span id="annex_ref_suffix" contenteditable="true" oninput="syncRefSuffixFromAnnex(this.innerText)">{{ $refSuffix }}</span></u></div>
                <div class="annex-line"><u>Dated : <span id="annex_date" contenteditable="true" oninput="syncDateFromAnnex(this.innerText)">{{ $letterDate }}</span></u></div>
            </div>

            <!-- TITLE -->
            <div class="annex-heading">
                <u>LIST OF REQUIRED ITEMS</u>
            </div>

            <!-- ADD ROW BUTTON (SCREEN ONLY) -->
            <div class="no-print" style="margin-bottom: 8px; display: flex; justify-content: flex-end;">
                <button type="button" class="btn-add-table-row" onclick="addAnnexRow()">
                    <i class="fas fa-plus"></i> Add Item Row
                </button>
            </div>

            <!-- ANNEX ITEMS TABLE (3 COLUMNS: S No, Item / specification, Qty) -->
            <table class="annex-table" id="annexItemsTable">
                <thead>
                    <tr>
                        <th style="width: 55px;">S No</th>
                        <th class="t-left">Item / specification</th>
                        <th style="width: 100px;">Qty</th>
                        <th style="width: 35px;" class="col-act no-print"></th>
                    </tr>
                </thead>
                <tbody id="annexItemsBody">
                    @forelse($annexItems as $idx => $item)
                    <tr>
                        <td class="t-center item-serial">{{ $item['serial'] ?? ($idx + 1) }}</td>
                        <td class="t-left item-desc" contenteditable="true" oninput="markUnsaved()">{{ $item['desc'] ?? '' }}</td>
                        <td class="t-center item-qty" contenteditable="true" oninput="markUnsaved()">{{ $item['qty'] ?? '01 Nos' }}</td>
                        <td class="t-center col-act no-print">
                            <button type="button" class="btn-del-row" onclick="deleteAnnexRow(this)" title="Delete Row">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="t-center item-serial">1</td>
                        <td class="t-left item-desc" contenteditable="true" oninput="markUnsaved()">{{ $purchase->pcs_title }}</td>
                        <td class="t-center item-qty" contenteditable="true" oninput="markUnsaved()">01 x Nos</td>
                        <td class="t-center col-act no-print">
                            <button type="button" class="btn-del-row" onclick="deleteAnnexRow(this)" title="Delete Row">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- FOOTER -->
            <div class="annex-footer">
                <div></div>
                <div style="font-weight: bold;">1 of 1</div>
                <div>Printed on {{ date('d M y   H:i') }}</div>
            </div>

        </div>

    </div>

    <!-- JAVASCRIPT -->
    <script>
        const firmsDirectory = @json($firmsDirectory ?? []);
        const defaultTitle = @json($purchase->pcs_title ?? 'required items');
        const defaultDeadline = @json($deadlineDate);
        let hasUnsavedChanges = false;
        let is2ColLayout = false;
        let currentFilteredFirms = [];

        // Enable TAB key indentation inside editable paragraphs
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab' && e.target && e.target.classList && e.target.classList.contains('editable-para')) {
                e.preventDefault();
                document.execCommand('insertText', false, '\t');
                markUnsaved();
            }
        });

        function updateFirmCountBadge() {
            const count = document.querySelectorAll('#firmsList .firm-entry').length;
            const badge = document.getElementById('firmCountBadge');
            if (badge) {
                badge.innerText = count + (count === 1 ? ' Firm' : ' Firms');
            }
        }

        function toggleFirmsLayout() {
            const list = document.getElementById('firmsList');
            const btnText = document.getElementById('layoutToggleText');
            is2ColLayout = !is2ColLayout;
            if (is2ColLayout) {
                list.classList.add('grid-2col');
                if (btnText) btnText.innerText = '1-Col List';
            } else {
                list.classList.remove('grid-2col');
                if (btnText) btnText.innerText = '2-Col Grid';
            }
        }

        function handleSearchInput(val) {
            const clearBtn = document.getElementById('btnClearSearch');
            if (clearBtn) {
                clearBtn.style.display = val.trim().length > 0 ? 'inline-block' : 'none';
            }
            filterFirms(val);
        }

        function clearSearchInput() {
            const input = document.getElementById('firmSearchInput');
            if (input) {
                input.value = '';
                handleSearchInput('');
                input.focus();
            }
        }

        // Synchronize Proc/ Number Suffix between Page 1 and Page 2
        function syncRefSuffix(val) {
            const cleanVal = val.trim() || '{{ $refSuffix }}';
            document.getElementById('annex_ref_suffix').innerText = cleanVal;
            markUnsaved();
        }

        function syncRefSuffixFromAnnex(val) {
            const cleanVal = val.trim() || '{{ $refSuffix }}';
            document.getElementById('ref_no_suffix').innerText = cleanVal;
            markUnsaved();
        }

        // Synchronize Date between Page 1 and Page 2
        function syncDate(val) {
            const cleanVal = val.trim() || '{{ $letterDate }}';
            document.getElementById('annex_date').innerText = cleanVal;
            markUnsaved();
        }

        function syncDateFromAnnex(val) {
            const cleanVal = val.trim() || '{{ $letterDate }}';
            document.getElementById('letter_date').innerText = cleanVal;
            markUnsaved();
        }

        function markUnsaved() {
            hasUnsavedChanges = true;
            const ind = document.getElementById('saveIndicator');
            ind.className = 'save-indicator unsaved';
            ind.innerHTML = '<i class="fas fa-circle"></i> Unsaved Changes';
        }

        function markSaved() {
            hasUnsavedChanges = false;
            const ind = document.getElementById('saveIndicator');
            ind.className = 'save-indicator';
            ind.innerHTML = '<i class="fas fa-check-circle"></i> Saved';
        }

        // Dynamic Paragraphs
        function addParagraph() {
            const container = document.getElementById('letterParagraphs');
            const count = container.querySelectorAll('.para-wrapper').length + 1;
            const wrap = document.createElement('div');
            wrap.className = 'para-wrapper';
            wrap.innerHTML = `
                <div class="editable-para" contenteditable="true" oninput="markUnsaved()">${count}.\tNew paragraph text...</div>
                <button type="button" class="btn-del-para no-print" onclick="removeParagraph(this)" title="Delete Paragraph">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(wrap);
            markUnsaved();
            const newEl = wrap.querySelector('.editable-para');
            if (newEl) {
                newEl.focus();
            }
        }

        function removeParagraph(btn) {
            const wrap = btn.closest('.para-wrapper');
            if (wrap) {
                wrap.remove();
                markUnsaved();
            }
        }

        function isFirmAlreadySelected(firmId) {
            if (!firmId) return false;
            return !!document.querySelector(`#firmsList .firm-entry[data-id="${firmId}"]`);
        }

        // Firm Search & Live Autocomplete
        function showFirmResults() {
            const input = document.getElementById('firmSearchInput');
            filterFirms(input ? input.value : '');
            document.getElementById('firmDropdownResults').style.display = 'block';
        }

        function filterFirms(query) {
            const resultsBox = document.getElementById('firmDropdownResults');
            const q = (query || '').trim().toLowerCase();
            
            const filtered = firmsDirectory.filter(f => {
                if (!q) return true;
                return (f.name && f.name.toLowerCase().includes(q)) || 
                       (f.raw_name && f.raw_name.toLowerCase().includes(q)) ||
                       (f.address && f.address.toLowerCase().includes(q)) ||
                       (f.city && f.city.toLowerCase().includes(q)) ||
                       (f.tel && f.tel.toLowerCase().includes(q)) ||
                       (f.ntn && f.ntn.toLowerCase().includes(q)) ||
                       (f.gst && f.gst.toLowerCase().includes(q)) ||
                       (f.type && f.type.toLowerCase().includes(q)) ||
                       (f.entity && f.entity.toLowerCase().includes(q));
            });

            currentFilteredFirms = filtered;

            if (filtered.length === 0) {
                resultsBox.innerHTML = `
                    <div class="firm-dropdown-header">
                        <span>No matching firms found</span>
                    </div>
                    <div style="padding: 16px; color: var(--rd-text3); text-align:center; font-size:12px;">
                        No firms found matching "<b>${escapeHtml(query)}</b>" in system database.
                    </div>
                `;
            } else {
                let html = `
                    <div class="firm-dropdown-header">
                        <span><i class="fas fa-building" style="color: var(--rd-primary-700); margin-right: 4px;"></i> Showing <b>${filtered.length}</b> ${filtered.length === firmsDirectory.length ? 'total system' : 'matching'} firms</span>
                        <button type="button" class="btn-dropdown-add-all" onclick="addAllFilteredFirms()">
                            <i class="fas fa-plus-circle"></i> Add All (${filtered.length})
                        </button>
                    </div>
                `;
                
                filtered.forEach(f => {
                    const isAdded = isFirmAlreadySelected(f.id);
                    let badgesHtml = '';
                    if (isAdded) {
                        badgesHtml += `<span class="badge-tag badge-added"><i class="fas fa-check"></i> Added</span>`;
                    }
                    if (f.is_case_firm) {
                        badgesHtml += `<span class="badge-tag badge-case"><i class="fas fa-star"></i> Case Quoted</span>`;
                    }
                    if (f.city) {
                        badgesHtml += `<span class="badge-tag badge-city"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(f.city)}</span>`;
                    }
                    if (f.ntn) {
                        badgesHtml += `<span class="badge-tag badge-ntn">NTN: ${escapeHtml(f.ntn)}</span>`;
                    }

                    const addrDisplay = f.address ? escapeHtml(f.address) : '<span style="color: var(--rd-text3); font-style:italic;">Address not registered</span>';
                    const telDisplay = f.tel ? `<span><i class="fas fa-phone-alt" style="font-size:10px;"></i> ${escapeHtml(f.tel)}</span>` : '';

                    html += `
                        <div class="firm-res-item ${isAdded ? 'is-selected' : ''}" onclick="selectFirm(${f.id})">
                            <div class="firm-res-hdr">
                                <strong>${escapeHtml(f.name)}</strong>
                                <div class="firm-res-badges">${badgesHtml}</div>
                            </div>
                            <div class="firm-res-details">
                                <div>${addrDisplay}</div>
                                ${telDisplay}
                            </div>
                        </div>
                    `;
                });
                resultsBox.innerHTML = html;
            }
            resultsBox.style.display = 'block';
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        document.addEventListener('click', function(e) {
            const container = document.querySelector('.firm-search-bar');
            if (container && !container.contains(e.target)) {
                document.getElementById('firmDropdownResults').style.display = 'none';
            }
        });

        // Add selected firm into vertical list
        function selectFirm(firmId) {
            const firm = firmsDirectory.find(f => f.id === firmId);
            if (!firm) return;

            const list = document.getElementById('firmsList');
            const existing = list.querySelector(`.firm-entry[data-id="${firmId}"]`);
            if (existing) {
                showToast(`${firm.name} is already in the list.`);
                existing.scrollIntoView({ behavior: 'smooth', block: 'center' });
                existing.style.outline = '2px solid var(--rd-primary-700)';
                setTimeout(() => { existing.style.outline = 'none'; }, 1500);
                document.getElementById('firmDropdownResults').style.display = 'none';
                return;
            }

            const entry = document.createElement('div');
            entry.className = 'firm-entry';
            entry.setAttribute('data-id', firm.id);
            entry.innerHTML = `
                <div class="firm-hdr">
                    <span class="f-name" contenteditable="true" oninput="markUnsaved()">${escapeHtml(firm.name)}</span>
                    <button type="button" class="btn-del-firm no-print" onclick="removeFirm(this)" title="Remove Firm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <span class="f-addr" contenteditable="true" oninput="markUnsaved()">${escapeHtml(firm.address || 'Karachi, Pakistan')}</span>
                <span class="f-tel" contenteditable="true" oninput="markUnsaved()">Tel: ${escapeHtml(firm.tel || 'N/A')}</span>
            `;

            list.appendChild(entry);
            updateFirmCountBadge();
            markUnsaved();
            showToast(`Added ${firm.name}`);

            // Refresh results badge if dropdown is open
            const input = document.getElementById('firmSearchInput');
            filterFirms(input ? input.value : '');
        }

        function addAllSystemFirms() {
            const list = document.getElementById('firmsList');
            let addedCount = 0;

            firmsDirectory.forEach(firm => {
                const existing = list.querySelector(`.firm-entry[data-id="${firm.id}"]`);
                if (!existing) {
                    const entry = document.createElement('div');
                    entry.className = 'firm-entry';
                    entry.setAttribute('data-id', firm.id);
                    entry.innerHTML = `
                        <div class="firm-hdr">
                            <span class="f-name" contenteditable="true" oninput="markUnsaved()">${escapeHtml(firm.name)}</span>
                            <button type="button" class="btn-del-firm no-print" onclick="removeFirm(this)" title="Remove Firm">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <span class="f-addr" contenteditable="true" oninput="markUnsaved()">${escapeHtml(firm.address || 'Karachi, Pakistan')}</span>
                        <span class="f-tel" contenteditable="true" oninput="markUnsaved()">Tel: ${escapeHtml(firm.tel || 'N/A')}</span>
                    `;
                    list.appendChild(entry);
                    addedCount++;
                }
            });

            updateFirmCountBadge();
            markUnsaved();
            showToast(`Added all ${addedCount} system firms (${firmsDirectory.length} total)`);
            document.getElementById('firmDropdownResults').style.display = 'none';
        }

        function addCaseQuotedFirms() {
            const list = document.getElementById('firmsList');
            let addedCount = 0;
            const caseFirms = firmsDirectory.filter(f => f.is_case_firm);

            if (caseFirms.length === 0) {
                showToast('No quoted firms found for this case.');
                return;
            }

            caseFirms.forEach(firm => {
                const existing = list.querySelector(`.firm-entry[data-id="${firm.id}"]`);
                if (!existing) {
                    const entry = document.createElement('div');
                    entry.className = 'firm-entry';
                    entry.setAttribute('data-id', firm.id);
                    entry.innerHTML = `
                        <div class="firm-hdr">
                            <span class="f-name" contenteditable="true" oninput="markUnsaved()">${escapeHtml(firm.name)}</span>
                            <button type="button" class="btn-del-firm no-print" onclick="removeFirm(this)" title="Remove Firm">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <span class="f-addr" contenteditable="true" oninput="markUnsaved()">${escapeHtml(firm.address || 'Karachi, Pakistan')}</span>
                        <span class="f-tel" contenteditable="true" oninput="markUnsaved()">Tel: ${escapeHtml(firm.tel || 'N/A')}</span>
                    `;
                    list.appendChild(entry);
                    addedCount++;
                }
            });

            updateFirmCountBadge();
            markUnsaved();
            showToast(`Added ${addedCount} case quoted firms`);
            document.getElementById('firmDropdownResults').style.display = 'none';
        }

        function addAllFilteredFirms() {
            if (!currentFilteredFirms || currentFilteredFirms.length === 0) return;
            const list = document.getElementById('firmsList');
            let addedCount = 0;

            currentFilteredFirms.forEach(firm => {
                const existing = list.querySelector(`.firm-entry[data-id="${firm.id}"]`);
                if (!existing) {
                    const entry = document.createElement('div');
                    entry.className = 'firm-entry';
                    entry.setAttribute('data-id', firm.id);
                    entry.innerHTML = `
                        <div class="firm-hdr">
                            <span class="f-name" contenteditable="true" oninput="markUnsaved()">${escapeHtml(firm.name)}</span>
                            <button type="button" class="btn-del-firm no-print" onclick="removeFirm(this)" title="Remove Firm">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <span class="f-addr" contenteditable="true" oninput="markUnsaved()">${escapeHtml(firm.address || 'Karachi, Pakistan')}</span>
                        <span class="f-tel" contenteditable="true" oninput="markUnsaved()">Tel: ${escapeHtml(firm.tel || 'N/A')}</span>
                    `;
                    list.appendChild(entry);
                    addedCount++;
                }
            });

            updateFirmCountBadge();
            markUnsaved();
            showToast(`Added ${addedCount} firms from search results`);
            document.getElementById('firmDropdownResults').style.display = 'none';
        }

        function clearAllFirms() {
            const list = document.getElementById('firmsList');
            if (!list || list.children.length === 0) return;
            if (!confirm('Are you sure you want to clear all firms from the distribution list?')) {
                return;
            }
            list.innerHTML = '';
            updateFirmCountBadge();
            markUnsaved();
            showToast('All firms cleared from distribution list');
        }

        function removeFirm(btn) {
            const entry = btn.closest('.firm-entry');
            if (entry) {
                entry.remove();
                updateFirmCountBadge();
                markUnsaved();
                showToast('Firm removed');
                const input = document.getElementById('firmSearchInput');
                if (document.getElementById('firmDropdownResults').style.display === 'block') {
                    filterFirms(input ? input.value : '');
                }
            }
        }

        // Table Rows Management (S No, Item / specification, Qty)
        function addAnnexRow() {
            const tbody = document.getElementById('annexItemsBody');
            const count = tbody.querySelectorAll('tr').length + 1;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="t-center item-serial">${count}</td>
                <td class="t-left item-desc" contenteditable="true" oninput="markUnsaved()">New item / specification...</td>
                <td class="t-center item-qty" contenteditable="true" oninput="markUnsaved()">01 x Nos</td>
                <td class="t-center col-act no-print">
                    <button type="button" class="btn-del-row" onclick="deleteAnnexRow(this)" title="Delete Row">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            renumberAnnexRows();
            markUnsaved();
        }

        function deleteAnnexRow(btn) {
            const row = btn.closest('tr');
            if (row) {
                row.remove();
                renumberAnnexRows();
                markUnsaved();
            }
        }

        function renumberAnnexRows() {
            const rows = document.querySelectorAll('#annexItemsBody tr');
            rows.forEach((r, idx) => {
                const sCell = r.querySelector('.item-serial');
                if (sCell) sCell.innerText = idx + 1;
            });
        }

        function resetDefaults() {
            if (!confirm('Are you sure you want to reset the letter body to default template?')) {
                return;
            }

            const p1 = `1.\tR&D Wing NRDI at PNS JAUHAR is interested for the procurement of ${defaultTitle}. In this regard, quotation are to be submitted to MD R&D at NRDI by ${defaultDeadline}.`;
            const p2 = `2.\tQuotation will be opened on same day at 11:00 hrs in the presence of all participants or their representatives and will be accepted at lowest quotations rate basis. However, It is apprised that MD (R&D) reserves the right to reject/ accept any quotation or invite new quotation without assigning any reason.`;
            const p3 = `3.\tFollowing terms and condition would apply:\n\n\ta.\tItems are to be delivered within 15 days after issuance of purchase order.\n\tb.\tPayment will be processed / made after delivery and acceptance by user.\n\tc.\tPart Delivery / Partial shall not be entertained.\n\td.\tWarrantee / Guarantee of one year is required.`;
            
            const container = document.getElementById('letterParagraphs');
            container.innerHTML = `
                <div class="para-wrapper">
                    <div class="editable-para" contenteditable="true" oninput="markUnsaved()">${p1}</div>
                </div>
                <div class="para-wrapper">
                    <div class="editable-para" contenteditable="true" oninput="markUnsaved()">${p2}</div>
                    <button type="button" class="btn-del-para no-print" onclick="removeParagraph(this)" title="Delete Paragraph">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="para-wrapper">
                    <div class="editable-para" contenteditable="true" oninput="markUnsaved()">${p3}</div>
                    <button type="button" class="btn-del-para no-print" onclick="removeParagraph(this)" title="Delete Paragraph">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.getElementById('see_distribution').innerText = 'See distribution';
            document.getElementById('signatory_name').innerText = 'MUHAMMAD MUDASSIR';
            document.getElementById('signatory_rank').innerText = 'Cdr (R) Pakistan Navy';
            document.getElementById('signatory_dept').innerText = 'R&D Wing, NRDI';
            
            markUnsaved();
            showToast('Reset to default naval template');
        }

        // Save Document via AJAX
        function saveDocument() {
            const saveBtn = document.getElementById('btnSaveDoc');
            const ind = document.getElementById('saveIndicator');
            
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            ind.className = 'save-indicator saving';
            ind.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            const firms = [];
            document.querySelectorAll('#firmsList .firm-entry').forEach(entry => {
                const nameEl = entry.querySelector('.f-name');
                const addrEl = entry.querySelector('.f-addr');
                const telEl = entry.querySelector('.f-tel');
                firms.push({
                    id: entry.getAttribute('data-id') || null,
                    name: nameEl ? nameEl.innerText.trim() : '',
                    address: addrEl ? addrEl.innerText.trim() : '',
                    tel: telEl ? telEl.innerText.replace(/^Tel:\s*/i, '').trim() : ''
                });
            });

            const paragraphs = [];
            document.querySelectorAll('#letterParagraphs .editable-para').forEach(el => {
                paragraphs.push(el.innerText.trim());
            });

            const items = [];
            document.querySelectorAll('#annexItemsBody tr').forEach(r => {
                const sEl = r.querySelector('.item-serial');
                const dEl = r.querySelector('.item-desc');
                const qEl = r.querySelector('.item-qty');

                if (dEl) {
                    items.push({
                        serial: sEl ? sEl.innerText.trim() : '',
                        desc: dEl ? dEl.innerText.trim() : '',
                        qty: qEl ? qEl.innerText.trim() : '1'
                    });
                }
            });

            const refSuffix = document.getElementById('ref_no_suffix').innerText.trim();
            const fullRefNo = 'R&D/Projects/Proc/' + refSuffix;

            const payload = {
                ref_no: fullRefNo,
                letter_date: document.getElementById('letter_date').innerText.trim(),
                subject: document.getElementById('subject').innerText.trim(),
                see_distribution: document.getElementById('see_distribution') ? document.getElementById('see_distribution').innerText.trim() : 'See distribution',
                paragraphs: paragraphs,
                para1: paragraphs[0] || '',
                para2: paragraphs[1] || '',
                para3: paragraphs[2] || '',
                signatory_name: document.getElementById('signatory_name').innerText.trim(),
                signatory_rank: document.getElementById('signatory_rank').innerText.trim(),
                signatory_dept: document.getElementById('signatory_dept').innerText.trim(),
                firms: firms,
                items: items
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ url('/purchase/case/' . $purchase->pcs_id . '/it-letter/save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Document';
                if (data.success) {
                    markSaved();
                    showToast('Letter and Annex saved successfully!');
                } else {
                    ind.className = 'save-indicator unsaved';
                    ind.innerHTML = '<i class="fas fa-exclamation-circle"></i> Save Failed';
                    alert('Error saving document: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Document';
                ind.className = 'save-indicator unsaved';
                ind.innerHTML = '<i class="fas fa-exclamation-circle"></i> Save Failed';
                console.error(err);
                alert('Network error while saving document.');
            });
        }

        function showToast(msg) {
            const toast = document.getElementById('toastMsg');
            document.getElementById('toastText').innerText = msg;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        window.addEventListener('beforeunload', function (e) {
            if (hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes in the document.';
            }
        });
    </script>
</body>
</html>
