<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IT Letter Default Template & Page Setup Editor</title>
    
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    
    <style id="baseStyles">
        /* ================= GLOBAL & RESET ================= */
        * {
            box-sizing: border-box;
        }
        body {
            background-color: #cbd5e1;
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
            background: #0f172a;
            color: #fff;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            z-index: 99999;
            border-bottom: 1px solid #1e293b;
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
        .btn-page-setup {
            background: #6366f1;
            color: #fff;
        }
        .btn-page-setup:hover {
            background: #4f46e5;
        }
        .btn-save {
            background: #10b981;
            color: #fff;
        }
        .btn-save:hover {
            background: #059669;
        }
        .btn-view-mode {
            background: #8b5cf6;
            color: #fff;
        }
        .btn-view-mode:hover {
            background: #7c3aed;
        }
        .btn-add-page {
            background: #0284c7;
            color: #fff;
        }
        .btn-add-page:hover {
            background: #0369a1;
        }
        .btn-reset {
            background: rgba(255, 255, 255, 0.12);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .btn-reset:hover {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        /* ================= PAGE SETUP & MARGINS PANEL ================= */
        .page-setup-panel {
            background: #0f172a;
            color: #f8fafc;
            border-bottom: 2px solid #334155;
            padding: 16px 24px;
            display: none;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            z-index: 99997;
            position: relative;
        }
        .page-setup-panel.active {
            display: block;
        }
        .ps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        .ps-section {
            background: #1e293b;
            padding: 14px 16px;
            border-radius: 6px;
            border: 1px solid #334155;
        }
        .ps-section h4 {
            margin: 0 0 12px 0;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid #334155;
            padding-bottom: 6px;
        }
        .ps-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 12px;
        }
        .ps-row label {
            color: #cbd5e1;
            font-weight: 600;
        }
        .ps-input {
            background: #0f172a;
            color: #fff;
            border: 1px solid #475569;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 12px;
            width: 100px;
            outline: none;
        }
        .ps-input:focus {
            border-color: #3b82f6;
        }
        .ps-checkbox {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #3b82f6;
        }

        /* ================= MS WORD FORMATTING TOOLBAR ================= */
        .formatting-toolbar-bar {
            position: sticky;
            top: 53px;
            left: 0;
            right: 0;
            background: #1e293b;
            color: #fff;
            padding: 6px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            z-index: 99996;
            border-bottom: 1px solid #334155;
        }
        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .toolbar-divider {
            width: 1px;
            height: 22px;
            background: #475569;
            margin: 0 4px;
        }
        .tb-btn {
            background: rgba(255, 255, 255, 0.08);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 28px;
            transition: all 0.15s;
            user-select: none;
        }
        .tb-btn:hover {
            background: #3b82f6;
            color: #fff;
            border-color: #3b82f6;
        }
        .tb-btn:active {
            transform: scale(0.95);
        }
        .tb-select {
            background: #0f172a;
            color: #f8fafc;
            border: 1px solid #475569;
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 11.5px;
            height: 28px;
            outline: none;
            cursor: pointer;
        }
        .tb-select:focus {
            border-color: #3b82f6;
        }
        .tb-color-picker-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 4px;
            padding: 2px 6px;
            height: 28px;
            font-size: 11.5px;
            cursor: pointer;
            color: #cbd5e1;
        }
        .tb-color-picker-wrapper:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #fff;
        }
        .tb-color-picker-wrapper input[type="color"] {
            border: none;
            width: 18px;
            height: 18px;
            padding: 0;
            background: transparent;
            cursor: pointer;
        }
        .document-wrapper {
            padding: 25px 15px 50px 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
            transition: all 0.3s ease-in-out;
        }
        .document-wrapper.side-by-side {
            flex-direction: row !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            align-items: flex-start !important;
            gap: 40px !important;
            max-width: 100% !important;
            padding-left: 20px !important;
            padding-right: 20px !important;
        }
        .document-wrapper.side-by-side .page-ruler-container {
            margin-bottom: 20px !important;
        }

        /* ================= A4 DYNAMIC PAGE LAYOUT ================= */
        .a4-page {
            background: #fff;
            width: var(--page-width, 210mm);
            min-height: var(--page-height, 297mm);
            padding-top: var(--margin-top, 25.4mm);
            padding-bottom: var(--margin-bottom, 25.4mm);
            padding-left: var(--margin-left, 25.4mm);
            padding-right: var(--margin-right, 20.32mm);
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.2);
            position: relative;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            transition: all 0.2s ease-in-out;
        }

        /* HEADER BLOCK */
        .page-header-block {
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 10pt;
            line-height: 1.35;
        }
        .header-left, .header-center, .header-right {
            min-height: 24px;
        }
        .header-left { text-align: left; flex: 1; }
        .header-center { text-align: center; flex: 2; font-weight: bold; }
        .header-right { text-align: right; flex: 1; }

        /* FOOTER BLOCK */
        .page-footer-block {
            border-top: 1px solid #000;
            padding-top: 6px;
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5pt;
            color: #333;
        }

        /* Toast notification */
        .toast-msg {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 12px 20px;
            background: #1e293b;
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
        [contenteditable="true"]:hover {
            background-color: #f8fafc;
            box-shadow: 0 0 0 1px #cbd5e1;
            border-radius: 2px;
        }
        [contenteditable="true"]:focus {
            background-color: #f0f9ff;
            box-shadow: 0 0 0 2px #3b82f6;
            border-radius: 2px;
        }

        /* Single Editable Body Box */
        .letter-body-single {
            outline: none;
            min-height: 120px;
            font-size: 12pt;
            line-height: 1.4;
            text-align: justify;
            margin-bottom: 12pt;
            border-radius: 2px;
            transition: background-color 0.15s, box-shadow 0.15s;
        }
        .letter-body-single[contenteditable="true"]:hover {
            background-color: #f8fafc;
            box-shadow: 0 0 0 1px #cbd5e1;
        }
        .letter-body-single[contenteditable="true"]:focus {
            background-color: #f0f9ff;
            box-shadow: 0 0 0 2px #3b82f6;
        }
        .letter-body-single p, .letter-body-single div {
            margin: 0 0 var(--row-spacing, 5pt) 0;
            text-align: justify;
            white-space: pre-wrap;
            word-break: break-word;
            tab-size: 36px;
            font-size: 12pt;
            line-height: 1.4;
            font-family: Arial, Helvetica, sans-serif;
        }
        .letter-body-single p.sub-para, .letter-body-single div.sub-para {
            padding-left: 48px;
        }

        /* Subject */
        .letter-subject {
            font-size: 12pt;
            font-weight: bold;
            margin: 14pt 0 12pt 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Signatory */
        .signatory-wrapper {
            margin-top: 16pt;
            margin-bottom: 16pt;
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
            font-weight: normal;
        }

        /* Full-Width Side-by-Side Instructions Banner */
        .instructions-panel {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 0 auto 25px auto;
            max-width: 1350px;
            width: 100%;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            transition: all 0.25s ease-in-out;
        }
        .instructions-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .instructions-panel h4 {
            margin: 0;
            font-size: 13.5px;
            font-weight: bold;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-toggle-guide {
            background: rgba(217, 119, 6, 0.12);
            color: #92400e;
            border: 1px solid rgba(217, 119, 6, 0.3);
            border-radius: 4px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.15s ease;
        }
        .btn-toggle-guide:hover {
            background: rgba(217, 119, 6, 0.25);
            color: #78350f;
        }
        .instructions-panel ul {
            margin: 0;
            padding-left: 18px;
            font-size: 12px;
            color: #78350f;
            line-height: 1.6;
        }
        .instructions-panel code {
            background: #fef3c7;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 11.5px;
            font-weight: bold;
            color: #92400e;
        }

        /* ================= MS WORD INTERACTIVE RULERS ================= */
        .page-ruler-container {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
            margin-bottom: 35px;
            user-select: none;
        }
        .ruler-top-row {
            display: flex;
            align-items: flex-end;
            height: 26px;
            width: 100%;
        }
        .ruler-corner {
            width: 26px;
            height: 26px;
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
            border-right: none;
            border-bottom: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #64748b;
            flex-shrink: 0;
            border-top-left-radius: 4px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .ruler-corner:hover {
            background: #cbd5e1;
            color: #1e293b;
        }
        .ruler-horizontal {
            height: 26px;
            width: var(--page-width, 210mm);
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-bottom: 2px solid #64748b;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            border-top-right-radius: 4px;
        }
        .ruler-margin-left-shade, .ruler-margin-right-shade {
            position: absolute;
            top: 0;
            bottom: 0;
            background: #cbd5e1;
            opacity: 0.85;
            z-index: 1;
        }
        .ruler-margin-left-shade { left: 0; }
        .ruler-margin-right-shade { right: 0; }

        .ruler-middle-row {
            display: flex;
            align-items: flex-start;
        }
        .ruler-vertical {
            width: 26px;
            height: var(--page-height, 297mm);
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-right: 2px solid #64748b;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            flex-shrink: 0;
            border-bottom-left-radius: 4px;
        }
        .ruler-margin-top-shade, .ruler-margin-bottom-shade {
            position: absolute;
            left: 0;
            right: 0;
            background: #cbd5e1;
            opacity: 0.85;
            z-index: 1;
        }
        .ruler-margin-top-shade { top: 0; }
        .ruler-margin-bottom-shade { bottom: 0; }

        /* Draggable Ruler Handles */
        .ruler-handle {
            position: absolute;
            z-index: 10;
            cursor: pointer;
            user-select: none;
        }
        .ruler-handle-left {
            top: 0;
            bottom: 0;
            width: 12px;
            margin-left: -6px;
            cursor: ew-resize;
        }
        .ruler-handle-right {
            top: 0;
            bottom: 0;
            width: 12px;
            margin-right: -6px;
            cursor: ew-resize;
        }
        .ruler-handle-top {
            left: 0;
            right: 0;
            height: 12px;
            margin-top: -6px;
            cursor: ns-resize;
        }
        .ruler-handle-bottom {
            left: 0;
            right: 0;
            height: 12px;
            margin-bottom: -6px;
            cursor: ns-resize;
        }
        .handle-pointer-down {
            width: 0;
            height: 0;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-top: 8px solid #2563eb;
            margin: 0 auto;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));
        }
        .handle-pointer-right {
            width: 0;
            height: 0;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
            border-left: 8px solid #2563eb;
            margin: auto 0;
            filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));
        }
        .ruler-handle:hover .handle-pointer-down {
            border-top-color: #1d4ed8;
        }
        .ruler-handle:hover .handle-pointer-right {
            border-left-color: #1d4ed8;
        }

        /* Tick Marks */
        .ruler-ticks-h, .ruler-ticks-v {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 2;
            pointer-events: none;
        }
        .tick-h {
            position: absolute;
            bottom: 0;
            width: 1px;
            background: #94a3b8;
        }
        .tick-h.major {
            height: 9px;
            background: #334155;
        }
        .tick-h.minor {
            height: 4px;
        }
        .tick-h-num {
            position: absolute;
            top: 2px;
            font-size: 8.5px;
            color: #1e293b;
            transform: translateX(-50%);
            font-weight: bold;
        }

        .tick-v {
            position: absolute;
            right: 0;
            height: 1px;
            background: #94a3b8;
        }
        .tick-v.major {
            width: 9px;
            background: #334155;
        }
        .tick-v.minor {
            width: 4px;
        }
        .tick-v-num {
            position: absolute;
            left: 2px;
            font-size: 8.5px;
            color: #1e293b;
            transform: translateY(-50%);
            font-weight: bold;
        }

        /* Naval Header Styles */
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

        /* Annex A Page 2 Styles */
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

        /* Table inside page */
        .sample-annex-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
            font-size: 11pt;
        }
        .sample-annex-table th, .sample-annex-table td {
            border: 1px solid #000;
            padding: var(--col-spacing, 8px);
            text-align: left;
        }
        .sample-annex-table th {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        .page-break {
            page-break-before: always;
            break-before: page;
        }
    </style>

    <!-- DYNAMIC PRINT STYLES INJECTOR -->
    <style id="dynamicPrintStyles">
        @media print {
            @page {
                size: A4 portrait;
                margin: 0mm;
            }
            html, body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print,
            .top-action-bar,
            .page-setup-panel,
            .formatting-toolbar-bar,
            .toast-msg,
            .para-actions-hover,
            .btn-add-para,
            .instructions-panel {
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
                padding: 25.4mm 20.32mm 25.4mm 25.4mm !important;
                margin: 0 auto !important;
                width: 100% !important;
                min-height: 297mm !important;
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
        }
    </style>
</head>
<body>

    <!-- TOP ACTION BAR -->
    <div class="top-action-bar no-print">
        <div class="top-bar-left">
            @php
                $area = strtolower(trim((string) (Auth::user()->acc_untarea ?? '')));
                $isProc = in_array($area, ['proc', 'prc'], true);
                $backUrl = $isProc ? route('nrdi.procurement.purchase_cases.index') : route('nrdi.purchase_cases_new.index');
            @endphp
            <a href="{{ $backUrl }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <div class="top-bar-title">
                <span><i class="fas fa-file-alt" style="color: #60a5fa; margin-right: 4px;"></i> IT Letter — Default Template & Page Setup Editor</span>
                <span class="save-indicator" id="saveIndicator">
                    <i class="fas fa-check-circle"></i> Saved
                </span>
            </div>
        </div>

        <div class="top-bar-actions">
            <button type="button" class="action-btn btn-view-mode" id="btnToggleViewMode" onclick="toggleViewMode()" title="Toggle Side-by-Side / Vertical page view mode">
                <i class="fas fa-th-large"></i> Side-by-Side View
            </button>
            <button type="button" class="action-btn btn-add-page" onclick="addNewPage()" title="Add a new custom page / annex to template">
                <i class="fas fa-file-medical"></i> + Add Page
            </button>
            <button type="button" class="action-btn btn-page-setup" onclick="togglePageSetupPanel()">
                <i class="fas fa-sliders-h"></i> Page & Layout Setup
            </button>
            <button type="button" class="action-btn btn-reset" onclick="resetFactoryDefaults()" title="Reset to factory default template text & margins">
                <i class="fas fa-undo"></i> Reset Factory Defaults
            </button>
            <button type="button" class="action-btn btn-save" id="btnSaveDoc" onclick="saveTemplate()">
                <i class="fas fa-save"></i> Save Template
            </button>
        </div>
    </div>

    <!-- PAGE SETUP & MARGINS PANEL -->
    <div class="page-setup-panel no-print" id="pageSetupPanel">
        <div class="ps-grid">
            <!-- Paper Size & Margins -->
            <div class="ps-section">
                <h4><i class="fas fa-ruler-combined"></i> Page Size & Margins</h4>
                <div class="ps-row">
                    <label>Paper Size:</label>
                    <select id="psPageSize" class="ps-input" onchange="updatePageSetupVars(); markUnsaved();">
                        <option value="A4" {{ ($pageSetup['page_size'] ?? 'A4') == 'A4' ? 'selected' : '' }}>A4 (210 x 297 mm)</option>
                        <option value="Letter" {{ ($pageSetup['page_size'] ?? '') == 'Letter' ? 'selected' : '' }}>Letter (8.5 x 11 in)</option>
                        <option value="Legal" {{ ($pageSetup['page_size'] ?? '') == 'Legal' ? 'selected' : '' }}>Legal (8.5 x 14 in)</option>
                    </select>
                </div>
                <div class="ps-row">
                    <label>Orientation:</label>
                    <select id="psOrientation" class="ps-input" onchange="updatePageSetupVars(); markUnsaved();">
                        <option value="portrait" {{ ($pageSetup['orientation'] ?? 'portrait') == 'portrait' ? 'selected' : '' }}>Portrait</option>
                        <option value="landscape" {{ ($pageSetup['orientation'] ?? '') == 'landscape' ? 'selected' : '' }}>Landscape</option>
                    </select>
                </div>
                <div class="ps-row">
                    <label>Top Margin (mm):</label>
                    <input type="number" step="0.1" id="psMarginTop" class="ps-input" value="{{ $pageSetup['margin_top'] ?? '18' }}" oninput="updatePageSetupVars(); markUnsaved();">
                </div>
                <div class="ps-row">
                    <label>Bottom Margin (mm):</label>
                    <input type="number" step="0.1" id="psMarginBottom" class="ps-input" value="{{ $pageSetup['margin_bottom'] ?? '18' }}" oninput="updatePageSetupVars(); markUnsaved();">
                </div>
                <div class="ps-row">
                    <label>Left Margin (mm):</label>
                    <input type="number" step="0.1" id="psMarginLeft" class="ps-input" value="{{ $pageSetup['margin_left'] ?? '20' }}" oninput="updatePageSetupVars(); markUnsaved();">
                </div>
                <div class="ps-row">
                    <label>Right Margin (mm):</label>
                    <input type="number" step="0.1" id="psMarginRight" class="ps-input" value="{{ $pageSetup['margin_right'] ?? '18' }}" oninput="updatePageSetupVars(); markUnsaved();">
                </div>
            </div>

            <!-- Header & Footer Settings -->
            <div class="ps-section">
                <h4><i class="fas fa-heading"></i> Header & Footer Config</h4>
                <div class="ps-row">
                    <label>Paragraph Row Spacing:</label>
                    <select id="psRowSpacing" class="ps-input" onchange="updatePageSetupVars(); markUnsaved();">
                        <option value="4pt" {{ ($pageSetup['row_spacing'] ?? '') == '4pt' ? 'selected' : '' }}>4 pt</option>
                        <option value="5pt" {{ ($pageSetup['row_spacing'] ?? '5pt') == '5pt' ? 'selected' : '' }}>5 pt</option>
                        <option value="6pt" {{ ($pageSetup['row_spacing'] ?? '') == '6pt' ? 'selected' : '' }}>6 pt</option>
                        <option value="8pt" {{ ($pageSetup['row_spacing'] ?? '') == '8pt' ? 'selected' : '' }}>8 pt</option>
                        <option value="12pt" {{ ($pageSetup['row_spacing'] ?? '') == '12pt' ? 'selected' : '' }}>12 pt</option>
                    </select>
                </div>
                <div class="ps-row">
                    <label>Table Cell Padding:</label>
                    <select id="psColSpacing" class="ps-input" onchange="updatePageSetupVars(); markUnsaved();">
                        <option value="4px" {{ ($pageSetup['col_spacing'] ?? '') == '4px' ? 'selected' : '' }}>4 px</option>
                        <option value="6px" {{ ($pageSetup['col_spacing'] ?? '') == '6px' ? 'selected' : '' }}>6 px</option>
                        <option value="8px" {{ ($pageSetup['col_spacing'] ?? '8px') == '8px' ? 'selected' : '' }}>8 px</option>
                        <option value="12px" {{ ($pageSetup['col_spacing'] ?? '') == '12px' ? 'selected' : '' }}>12 px</option>
                    </select>
                </div>
            </div>

            <!-- Per-Case Field Permissions -->
            <div class="ps-section">
                <h4><i class="fas fa-user-lock"></i> Per-Case Editable Toggles</h4>
                <div class="ps-row">
                    <label>Allow editing Reference No:</label>
                    <input type="checkbox" id="permRefNo" class="ps-checkbox" {{ !empty($pageSetup['editable_ref_no']) ? 'checked' : '' }} onchange="markUnsaved()">
                </div>
                <div class="ps-row">
                    <label>Allow editing Date:</label>
                    <input type="checkbox" id="permDate" class="ps-checkbox" {{ !empty($pageSetup['editable_date']) ? 'checked' : '' }} onchange="markUnsaved()">
                </div>
                <div class="ps-row">
                    <label>Allow editing Subject:</label>
                    <input type="checkbox" id="permSubject" class="ps-checkbox" {{ !empty($pageSetup['editable_subject']) ? 'checked' : '' }} onchange="markUnsaved()">
                </div>
                <div class="ps-row">
                    <label>Allow editing Paragraphs:</label>
                    <input type="checkbox" id="permParagraphs" class="ps-checkbox" {{ !empty($pageSetup['editable_paragraphs']) ? 'checked' : '' }} onchange="markUnsaved()">
                </div>
                <div class="ps-row">
                    <label>Allow editing Signatory:</label>
                    <input type="checkbox" id="permSignatory" class="ps-checkbox" {{ !empty($pageSetup['editable_signatory']) ? 'checked' : '' }} onchange="markUnsaved()">
                </div>
                <div class="ps-row">
                    <label>Allow editing Firms Directory List:</label>
                    <input type="checkbox" id="permFirms" class="ps-checkbox" {{ !empty($pageSetup['editable_firms']) ? 'checked' : '' }} onchange="markUnsaved()">
                </div>
                <div class="ps-row">
                    <label>Allow editing Items Table (Annex A):</label>
                    <input type="checkbox" id="permItems" class="ps-checkbox" {{ !empty($pageSetup['editable_items']) ? 'checked' : '' }} onchange="markUnsaved()">
                </div>
            </div>
        </div>
    </div>

    <!-- FORMATTING TOOLBAR BAR (MS WORD RICH CONTROLS) -->
    <div class="formatting-toolbar-bar no-print">
        <!-- Font Family Selector -->
        <div class="toolbar-group">
            <select id="tbFontName" class="tb-select" onchange="applyFontName(this.value)" title="Font Family">
                <option value="Arial" selected>Arial</option>
                <option value="Times New Roman">Times New Roman</option>
                <option value="Calibri">Calibri</option>
                <option value="Georgia">Georgia</option>
                <option value="Courier New">Courier New</option>
                <option value="Verdana">Verdana</option>
                <option value="Tahoma">Tahoma</option>
                <option value="Garamond">Garamond</option>
            </select>
        </div>

        <!-- Font Size Selector -->
        <div class="toolbar-group">
            <select id="tbFontSize" class="tb-select" onchange="applyFontSize(this.value)" title="Font Size">
                <option value="8pt">8 pt</option>
                <option value="9pt">9 pt</option>
                <option value="10pt">10 pt</option>
                <option value="11pt">11 pt</option>
                <option value="12pt" selected>12 pt</option>
                <option value="14pt">14 pt</option>
                <option value="16pt">16 pt</option>
                <option value="18pt">18 pt</option>
                <option value="20pt">20 pt</option>
                <option value="24pt">24 pt</option>
            </select>
        </div>

        <div class="toolbar-divider"></div>

        <!-- Text Styling Buttons -->
        <div class="toolbar-group">
            <button type="button" class="tb-btn" onclick="formatDoc('bold')" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('italic')" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('underline')" title="Underline (Ctrl+U)"><i class="fas fa-underline"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('strikeThrough')" title="Strikethrough"><i class="fas fa-strikethrough"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('subscript')" title="Subscript"><i class="fas fa-subscript"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('superscript')" title="Superscript"><i class="fas fa-superscript"></i></button>
        </div>

        <div class="toolbar-divider"></div>

        <!-- Color Pickers -->
        <div class="toolbar-group">
            <label class="tb-color-picker-wrapper" title="Text Color">
                <i class="fas fa-font" style="color: #60a5fa;"></i> Text Color
                <input type="color" onchange="applyForeColor(this.value)" value="#000000">
            </label>
            <label class="tb-color-picker-wrapper" title="Highlight Color">
                <i class="fas fa-highlighter" style="color: #f59e0b;"></i> Highlight
                <input type="color" onchange="applyHiliteColor(this.value)" value="#fef08a">
            </label>
        </div>

        <div class="toolbar-divider"></div>

        <!-- Text Alignment Buttons -->
        <div class="toolbar-group">
            <button type="button" class="tb-btn" onclick="formatDoc('justifyLeft')" title="Align Left"><i class="fas fa-align-left"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('justifyCenter')" title="Align Center"><i class="fas fa-align-center"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('justifyRight')" title="Align Right"><i class="fas fa-align-right"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('justifyFull')" title="Justify"><i class="fas fa-align-justify"></i></button>
        </div>

        <div class="toolbar-divider"></div>

        <!-- Line Spacing Selector -->
        <div class="toolbar-group">
            <select id="tbLineHeight" class="tb-select" onchange="applyLineSpacing(this.value)" title="Line Spacing">
                <option value="1.0">1.0 Spacing</option>
                <option value="1.15">1.15 Spacing</option>
                <option value="1.5" selected>1.5 Spacing</option>
                <option value="2.0">2.0 Spacing</option>
            </select>
        </div>

        <div class="toolbar-divider"></div>

        <!-- Tab & Indentation Buttons -->
        <div class="toolbar-group">
            <button type="button" class="tb-btn" onclick="insertTabSpace()" title="Insert Tab Space (Indent)"><i class="fas fa-indent"></i> Indent Tab</button>
            <button type="button" class="tb-btn" onclick="formatDoc('insertUnorderedList')" title="Bullet List"><i class="fas fa-list-ul"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('insertOrderedList')" title="Numbered List"><i class="fas fa-list-ol"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('removeFormat')" title="Clear Formatting"><i class="fas fa-remove-format"></i> Clear</button>
        </div>

        <div class="toolbar-divider"></div>

        <!-- Insert Elements Group -->
        <div class="toolbar-group">
            <button type="button" class="tb-btn" onclick="promptInsertTable()" title="Insert Custom Table (Rows x Columns)"><i class="fas fa-table" style="color: #60a5fa;"></i> Insert Table</button>
            <button type="button" class="tb-btn" onclick="promptInsertBox()" title="Insert Note / Callout Box"><i class="fas fa-box-open" style="color: #f59e0b;"></i> Box</button>
            <button type="button" class="tb-btn" onclick="formatDoc('insertHorizontalRule')" title="Insert Line"><i class="fas fa-minus"></i> Line</button>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div class="toast-msg" id="toastMsg">
        <i class="fas fa-check-circle" style="font-size: 16px; color: #34d399;"></i>
        <span id="toastText">Template saved successfully!</span>
    </div>

    <!-- DOCUMENT CONTAINER -->
    <div class="document-wrapper" id="documentWrapper">

        <!-- INSTRUCTIONS PANEL & GUIDELINES (WIDE SIDE-BY-SIDE BANNER) -->
        <div class="instructions-panel no-print" id="instructionsPanel">
            <div class="instructions-panel-header">
                <h4><i class="fas fa-sliders-h" style="color: #d97706;"></i> IT Template & Page Layout Customization Guide</h4>
                <button type="button" class="btn-toggle-guide" onclick="toggleGuidePanel()" id="btnToggleGuide">
                    <i class="fas fa-chevron-up"></i> Hide Guide
                </button>
            </div>
            <div id="instructionsContent" style="font-size: 12px; color: #78350f; line-height: 1.6; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-top: 12px;">
                
                <!-- Card 1: Editable Wording -->
                <div style="background: rgba(255, 255, 255, 0.65); padding: 12px 14px; border-radius: 6px; border: 1px solid #fde68a;">
                    <div style="font-weight: bold; color: #92400e; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-edit" style="color: #b45309;"></i> 1. Editable Template Wording
                    </div>
                    <ul style="margin: 0; padding-left: 18px;">
                        <li><strong>Header & Organization:</strong> Edit Org Name, Wing, Base/Unit, Address, City, & Phone directly on the document.</li>
                        <li><strong>Reference Prefix:</strong> Customize default ref prefix <code>R&D/Projects/Proc/</code>.</li>
                        <li><strong>Subject & Body:</strong> Click to edit Subject title, body paragraphs, and signatory details.</li>
                        <li><strong>Annex A Titles & Headers:</strong> Customize <code>ANNEX A</code>, <code>TO IT NO </code>, <code>Dated :</code>, Annex Title, & Table Headers (<code>S No</code>, <code>Item / specification</code>, <code>Qty</code>).</li>
                    </ul>
                </div>

                <!-- Card 2: Page Setup & Layout Settings -->
                <div style="background: rgba(255, 255, 255, 0.65); padding: 12px 14px; border-radius: 6px; border: 1px solid #fde68a;">
                    <div style="font-weight: bold; color: #92400e; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-ruler-combined" style="color: #b45309;"></i> 2. Page & Layout Setup Panel
                    </div>
                    <ul style="margin: 0; padding-left: 18px;">
                        <li><strong>Paper Size & Orientation:</strong> Select <strong>A4</strong>, <strong>Letter</strong>, or <strong>Legal</strong> in <strong>Portrait</strong> or <strong>Landscape</strong>.</li>
                        <li><strong>Custom Page Margins:</strong> Set Top, Bottom, Left, and Right margins in millimeters (mm) or drag page rulers below.</li>
                        <li><strong>Paragraph & Table Spacing:</strong> Adjust row spacing (4pt to 12pt) and table cell padding (4px to 12px).</li>
                        <li><strong>Per-Case Permissions:</strong> Enable or disable per-case editing for Ref No, Date, Subject, Body, Signatory, Firms List, & Items.</li>
                    </ul>
                </div>

                <!-- Card 3: Dynamic Placeholders & Single Box Body -->
                <div style="background: rgba(255, 255, 255, 0.65); padding: 12px 14px; border-radius: 6px; border: 1px solid #fde68a;">
                    <div style="font-weight: bold; color: #92400e; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i class="fas fa-magic" style="color: #b45309;"></i> 3. Dynamic Case Placeholders & Editor
                    </div>
                    <ul style="margin: 0; padding-left: 18px;">
                        <li><code>{PCS_ID}</code>: Auto-populates with the specific Purchase Case ID.</li>
                        <li><code>{DEADLINE_DATE}</code>: Auto-populates with the 14-day quotation submission deadline.</li>
                        <li><code>{CURRENT_DATE}</code>: Auto-populates with the letter issue date.</li>
                        <li><code>{ITEM_TITLE}</code>: Auto-populates with case procurement specifications & quantities.</li>
                        <li><strong>Single Continuous Body:</strong> MS Word-style single container eliminates layout glitches and sub-box formatting errors.</li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- A4 TEMPLATE PAGE 1 WITH RULERS -->
        <div class="page-ruler-container" id="pageContainer_1">
            <!-- TOP RULER (HORIZONTAL) -->
            <div class="ruler-top-row no-print">
                <div class="ruler-corner" onclick="toggleRulerUnit()" title="Click to toggle Ruler Unit (CM / IN / MM)"><i class="fas fa-ruler" style="font-size: 10px; margin-right: 2px;"></i><span class="ruler-unit-tag" style="font-size: 8.5px; font-weight: bold;">CM</span></div>
                <div class="ruler-horizontal" id="rulerHorizontal_1">
                    <div class="ruler-margin-left-shade" id="rulerHLeftShade_1"></div>
                    <div class="ruler-margin-right-shade" id="rulerHRightShade_1"></div>
                    <div class="ruler-handle ruler-handle-left" id="rulerHandleLeft_1" title="Drag to adjust Left Margin">
                        <div class="handle-pointer-down"></div>
                    </div>
                    <div class="ruler-handle ruler-handle-right" id="rulerHandleRight_1" title="Drag to adjust Right Margin">
                        <div class="handle-pointer-down"></div>
                    </div>
                    <div class="ruler-ticks-h" id="rulerTicksH_1"></div>
                </div>
            </div>

            <div class="ruler-middle-row">
                <!-- LEFT RULER (VERTICAL) -->
                <div class="ruler-vertical no-print" id="rulerVertical_1">
                    <div class="ruler-margin-top-shade" id="rulerVTopShade_1"></div>
                    <div class="ruler-margin-bottom-shade" id="rulerVBottomShade_1"></div>
                    <div class="ruler-handle ruler-handle-top" id="rulerHandleTop_1" title="Drag to adjust Top Margin">
                        <div class="handle-pointer-right"></div>
                    </div>
                    <div class="ruler-handle ruler-handle-bottom" id="rulerHandleBottom_1" title="Drag to adjust Bottom Margin">
                        <div class="handle-pointer-right"></div>
                    </div>
                    <div class="ruler-ticks-v" id="rulerTicksV_1"></div>
                </div>

                <!-- A4 TEMPLATE PAGE 1 (RFQ LETTER) -->
                <div class="a4-page" id="templatePage">

                    <!-- NAVAL TOP HEADER -->
                    <div class="letter-header">
                        <div class="header-top-row">
                            <div class="header-top-left"></div>
                            <div class="header-top-right">
                                <div style="font-weight: bold; white-space: nowrap;" contenteditable="true" id="header_org_name" oninput="markUnsaved()">{{ $pageSetup['header_org_name'] ?? 'Naval Research & Development Institute' }}</div>
                                <div contenteditable="true" id="header_wing" oninput="markUnsaved()">{{ $pageSetup['header_wing'] ?? 'R&D Wing' }}</div>
                                <div contenteditable="true" id="header_base" oninput="markUnsaved()">{{ $pageSetup['header_base'] ?? 'at PNS JAUHAR' }}</div>
                                <div contenteditable="true" id="header_address" oninput="markUnsaved()">{{ $pageSetup['header_address'] ?? 'Habib Rehmatullah Road' }}</div>
                                <div contenteditable="true" id="header_city" oninput="markUnsaved()">{{ $pageSetup['header_city'] ?? 'KARACHI' }}</div>
                            </div>
                        </div>

                        <div class="header-meta-row">
                            <div class="meta-col-left">
                                <div class="meta-item">
                                    <span contenteditable="true" id="ref_prefix" oninput="markUnsaved()">{{ $pageSetup['ref_prefix'] ?? 'R&D/Projects/Proc/' }}</span><span style="color: #64748b; font-style: italic;">{PCS_ID}</span>
                                </div>
                                <div class="meta-item">
                                    <span contenteditable="true" id="see_distribution" oninput="markUnsaved()">{{ $pageSetup['see_distribution'] ?? 'See distribution:' }}</span>
                                </div>
                            </div>

                            <div class="meta-col-right">
                                <div class="meta-item">
                                    <span contenteditable="true" id="header_phone" oninput="markUnsaved()">{{ $pageSetup['header_phone'] ?? 'Ph (off): 48504781' }}</span>
                                </div>
                                <div class="meta-item">
                                    <span style="color: #64748b; font-style: italic;">{CURRENT_DATE}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SUBJECT -->
                    <div class="letter-subject">
                        <u><span contenteditable="true" id="subject" oninput="markUnsaved()">{{ $template->subject }}</span></u>
                    </div>

                    <!-- SINGLE CONTINUOUS EDITABLE BODY BOX -->
                    <div class="letter-body-single" contenteditable="true" id="letterParagraphs" oninput="markUnsaved()">
                        @foreach($template->paragraphs as $pIndex => $pText)
                        @php
                            $cleanPText = preg_replace('/^(\s*[0-9a-gA-G]+\.)[ \t]+/u', "$1\t", $pText);
                            $isSub = preg_match('/^\s*[a-g]\./i', trim($cleanPText));
                        @endphp
                        <p class="{{ $isSub ? 'sub-para' : 'main-para' }}">{!! $cleanPText !!}</p>
                        @endforeach
                    </div>

                    <!-- SIGNATORY -->
                    <div class="signatory-wrapper">
                        <div class="signatory-box">
                            <div class="sig-name" contenteditable="true" id="signatory_name" oninput="markUnsaved()">{{ $template->signatory_name }}</div>
                            <div class="sig-rank" contenteditable="true" id="signatory_rank" oninput="markUnsaved()">{{ $template->signatory_rank }}</div>
                            <div class="sig-dept" contenteditable="true" id="signatory_dept" oninput="markUnsaved()">{{ $template->signatory_dept }}</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- A4 TEMPLATE PAGE 2 WITH RULERS -->
        <div class="page-ruler-container" id="pageContainer_2">
            <!-- TOP RULER (HORIZONTAL) -->
            <div class="ruler-top-row no-print">
                <div class="ruler-corner" onclick="toggleRulerUnit()" title="Click to toggle Ruler Unit (CM / IN / MM)"><i class="fas fa-ruler" style="font-size: 10px; margin-right: 2px;"></i><span class="ruler-unit-tag" style="font-size: 8.5px; font-weight: bold;">CM</span></div>
                <div class="ruler-horizontal" id="rulerHorizontal_2">
                    <div class="ruler-margin-left-shade" id="rulerHLeftShade_2"></div>
                    <div class="ruler-margin-right-shade" id="rulerHRightShade_2"></div>
                    <div class="ruler-handle ruler-handle-left" id="rulerHandleLeft_2" title="Drag to adjust Left Margin">
                        <div class="handle-pointer-down"></div>
                    </div>
                    <div class="ruler-handle ruler-handle-right" id="rulerHandleRight_2" title="Drag to adjust Right Margin">
                        <div class="handle-pointer-down"></div>
                    </div>
                    <div class="ruler-ticks-h" id="rulerTicksH_2"></div>
                </div>
            </div>

            <div class="ruler-middle-row">
                <!-- LEFT RULER (VERTICAL) -->
                <div class="ruler-vertical no-print" id="rulerVertical_2">
                    <div class="ruler-margin-top-shade" id="rulerVTopShade_2"></div>
                    <div class="ruler-margin-bottom-shade" id="rulerVBottomShade_2"></div>
                    <div class="ruler-handle ruler-handle-top" id="rulerHandleTop_2" title="Drag to adjust Top Margin">
                        <div class="handle-pointer-right"></div>
                    </div>
                    <div class="ruler-handle ruler-handle-bottom" id="rulerHandleBottom_2" title="Drag to adjust Bottom Margin">
                        <div class="handle-pointer-right"></div>
                    </div>
                    <div class="ruler-ticks-v" id="rulerTicksV_2"></div>
                </div>

                <!-- A4 TEMPLATE PAGE 2 (ANNEX A) -->
                <div class="a4-page page-break" id="templatePageAnnex">

                    <!-- ANNEX TOP RIGHT -->
                    <div class="it-annex-header">
                        <div class="annex-line"><u><span contenteditable="true" id="annex_label" oninput="markUnsaved()">{{ $pageSetup['annex_label'] ?? 'ANNEX A' }}</span></u></div>
                        <div class="annex-line"><u><span contenteditable="true" id="to_it_no_prefix" oninput="markUnsaved()">{{ $pageSetup['to_it_no_prefix'] ?? 'TO IT NO ' }}</span><span contenteditable="true" id="annex_ref_prefix" oninput="markUnsaved()">{{ $pageSetup['ref_prefix'] ?? 'R&D/Projects/Proc/' }}</span><span style="color: #64748b; font-style: italic;">{PCS_ID}</span></u></div>
                        <div class="annex-line"><u><span contenteditable="true" id="dated_label" oninput="markUnsaved()">{{ $pageSetup['dated_label'] ?? 'Dated :' }}</span> <span style="color: #64748b; font-style: italic;">{DEADLINE_DATE}</span></u></div>
                    </div>

                    <!-- TITLE -->
                    <div class="annex-heading">
                        <u><span contenteditable="true" id="annex_title" oninput="markUnsaved()">{{ $pageSetup['annex_title'] ?? 'LIST OF REQUIRED ITEMS' }}</span></u>
                    </div>

                    <!-- SAMPLE ANNEX TABLE -->
                    <table class="sample-annex-table">
                        <thead>
                            <tr>
                                <th style="width: 55px; text-align: center;"><span contenteditable="true" id="th_sno" oninput="markUnsaved()">{{ $pageSetup['th_sno'] ?? 'S No' }}</span></th>
                                <th style="text-align: left;"><span contenteditable="true" id="th_spec" oninput="markUnsaved()">{{ $pageSetup['th_spec'] ?? 'Item / specification' }}</span></th>
                                <th style="width: 120px; text-align: center;"><span contenteditable="true" id="th_qty" oninput="markUnsaved()">{{ $pageSetup['th_qty'] ?? 'Qty' }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align: center;">1</td>
                                <td><span style="color: #64748b; font-style: italic;">{ITEM_TITLE} (Procurement Item Specifications)</span></td>
                                <td style="text-align: center;">01 x Nos</td>
                            </tr>
                            <tr>
                                <td style="text-align: center;">2</td>
                                <td><span style="color: #64748b; font-style: italic;">(Sample additional item row dynamically pulled per case)</span></td>
                                <td style="text-align: center;">02 x Days</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>

    </div>

    <!-- JAVASCRIPT -->
    <script>
        let hasUnsavedChanges = false;
        let isSideBySideView = false;
        let totalPageCounter = 2;

        function toggleViewMode() {
            isSideBySideView = !isSideBySideView;
            const docWrapper = document.getElementById('documentWrapper');
            const btn = document.getElementById('btnToggleViewMode');
            if (isSideBySideView) {
                docWrapper.classList.add('side-by-side');
                btn.innerHTML = '<i class="fas fa-list"></i> Vertical View';
                btn.style.background = '#7c3aed';
            } else {
                docWrapper.classList.remove('side-by-side');
                btn.innerHTML = '<i class="fas fa-th-large"></i> Side-by-Side View';
                btn.style.background = '#8b5cf6';
            }
        }

        function addNewPage() {
            totalPageCounter++;
            const pIdx = totalPageCounter;
            const docWrapper = document.getElementById('documentWrapper');
            
            const pageWrap = document.createElement('div');
            pageWrap.className = 'page-ruler-container';
            pageWrap.id = 'pageContainer_' + pIdx;
            pageWrap.innerHTML = `
                <!-- TOP RULER (HORIZONTAL) -->
                <div class="ruler-top-row no-print">
                    <div class="ruler-corner" onclick="toggleRulerUnit()" title="Click to toggle Ruler Unit (CM / IN / MM)"><i class="fas fa-ruler" style="font-size: 10px; margin-right: 2px;"></i><span class="ruler-unit-tag" style="font-size: 8.5px; font-weight: bold;">${currentRulerUnit.toUpperCase()}</span></div>
                    <div class="ruler-horizontal" id="rulerHorizontal_${pIdx}">
                        <div class="ruler-margin-left-shade" id="rulerHLeftShade_${pIdx}"></div>
                        <div class="ruler-margin-right-shade" id="rulerHRightShade_${pIdx}"></div>
                        <div class="ruler-handle ruler-handle-left" id="rulerHandleLeft_${pIdx}" title="Drag to adjust Left Margin">
                            <div class="handle-pointer-down"></div>
                        </div>
                        <div class="ruler-handle ruler-handle-right" id="rulerHandleRight_${pIdx}" title="Drag to adjust Right Margin">
                            <div class="handle-pointer-down"></div>
                        </div>
                        <div class="ruler-ticks-h" id="rulerTicksH_${pIdx}"></div>
                    </div>
                </div>

                <div class="ruler-middle-row">
                    <!-- LEFT RULER (VERTICAL) -->
                    <div class="ruler-vertical no-print" id="rulerVertical_${pIdx}">
                        <div class="ruler-margin-top-shade" id="rulerVTopShade_${pIdx}"></div>
                        <div class="ruler-margin-bottom-shade" id="rulerVBottomShade_${pIdx}"></div>
                        <div class="ruler-handle ruler-handle-top" id="rulerHandleTop_${pIdx}" title="Drag to adjust Top Margin">
                            <div class="handle-pointer-right"></div>
                        </div>
                        <div class="ruler-handle ruler-handle-bottom" id="rulerHandleBottom_${pIdx}" title="Drag to adjust Bottom Margin">
                            <div class="handle-pointer-right"></div>
                        </div>
                        <div class="ruler-ticks-v" id="rulerTicksV_${pIdx}"></div>
                    </div>

                    <!-- A4 TEMPLATE EXTRA PAGE -->
                    <div class="a4-page page-break custom-template-page" id="templatePage_${pIdx}">
                        <div class="no-print" style="position: absolute; top: 12px; right: 14px;">
                            <button type="button" onclick="removeCustomPage(${pIdx})" title="Delete Page" style="background: #ef4444; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold;">
                                <i class="fas fa-trash-alt"></i> Delete Page ${pIdx}
                            </button>
                        </div>

                        <!-- HEADING -->
                        <div class="annex-heading" style="margin-top: 15px;">
                            <u><span contenteditable="true" class="custom-page-title" oninput="markUnsaved()">PAGE ${pIdx} - ADDITIONAL SPECIFICATIONS</span></u>
                        </div>

                        <!-- SINGLE CONTINUOUS EDITABLE BODY BOX -->
                        <div class="letter-body-single custom-page-body" contenteditable="true" oninput="markUnsaved()">
                            <p class="main-para">Additional page text and specifications can be edited here...</p>
                        </div>
                    </div>
                </div>
            `;
            
            docWrapper.appendChild(pageWrap);
            updatePageSetupVars();
            bindSinglePageRulerDrag(pIdx);
            markUnsaved();
            showToast(`Page ${pIdx} added to template!`);
        }

        function removeCustomPage(pIdx) {
            if (!confirm(`Are you sure you want to delete Page ${pIdx}?`)) return;
            const pageWrap = document.getElementById('pageContainer_' + pIdx);
            if (pageWrap) {
                pageWrap.remove();
                markUnsaved();
                showToast(`Page ${pIdx} deleted.`);
            }
        }

        function toggleGuidePanel() {
            const content = document.getElementById('instructionsContent');
            const btn = document.getElementById('btnToggleGuide');
            if (content.style.display === 'none') {
                content.style.display = 'grid';
                btn.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Guide';
            } else {
                content.style.display = 'none';
                btn.innerHTML = '<i class="fas fa-chevron-down"></i> Show Guide';
            }
        }

        function getActivePageIndexes() {
            const ids = [];
            document.querySelectorAll('[id^="rulerHorizontal_"]').forEach(el => {
                const match = el.id.match(/^rulerHorizontal_(\d+)$/);
                if (match) ids.push(match[1]);
            });
            return ids.length ? ids : ['1', '2'];
        }

        let currentRulerUnit = 'cm'; // 'cm', 'in', 'mm'

        function toggleRulerUnit() {
            if (currentRulerUnit === 'cm') {
                currentRulerUnit = 'in';
            } else if (currentRulerUnit === 'in') {
                currentRulerUnit = 'mm';
            } else {
                currentRulerUnit = 'cm';
            }
            document.querySelectorAll('.ruler-unit-tag').forEach(el => el.innerText = currentRulerUnit.toUpperCase());
            renderRulers();
            showToast('Ruler scale set to ' + currentRulerUnit.toUpperCase());
        }

        // Render Ruler Ticks Dynamically
        function renderRulers() {
            const pageSize = document.getElementById('psPageSize').value;
            const orientation = document.getElementById('psOrientation').value;
            
            let wMm = 210;
            let hMm = 297;
            if (pageSize === 'Letter') {
                wMm = orientation === 'landscape' ? 279.4 : 215.9;
                hMm = orientation === 'landscape' ? 215.9 : 279.4;
            } else if (pageSize === 'Legal') {
                wMm = orientation === 'landscape' ? 355.6 : 215.9;
                hMm = orientation === 'landscape' ? 215.9 : 355.6;
            } else {
                wMm = orientation === 'landscape' ? 297 : 210;
                hMm = orientation === 'landscape' ? 210 : 297;
            }

            getActivePageIndexes().forEach(pageIdx => {
                const ticksH = document.getElementById('rulerTicksH_' + pageIdx);
                const ticksV = document.getElementById('rulerTicksV_' + pageIdx);
                if (!ticksH || !ticksV) return;

                let htmlH = '';
                let htmlV = '';

                if (currentRulerUnit === 'in') {
                    // Inches scale (1 inch = 25.4mm)
                    const totalInchesH = wMm / 25.4;
                    const totalInchesV = hMm / 25.4;

                    for (let inch = 0; inch <= totalInchesH; inch += 0.25) {
                        const mm = inch * 25.4;
                        if (mm > wMm) break;
                        const pct = (mm / wMm) * 100;
                        const isMajor = Math.abs(inch - Math.round(inch)) < 0.01;
                        htmlH += `<div class="tick-h ${isMajor ? 'major' : 'minor'}" style="left: ${pct}%;"></div>`;
                        if (isMajor && inch > 0 && mm < wMm - 5) {
                            htmlH += `<div class="tick-h-num" style="left: ${pct}%;">${Math.round(inch)}"</div>`;
                        }
                    }

                    for (let inch = 0; inch <= totalInchesV; inch += 0.25) {
                        const mm = inch * 25.4;
                        if (mm > hMm) break;
                        const pct = (mm / hMm) * 100;
                        const isMajor = Math.abs(inch - Math.round(inch)) < 0.01;
                        htmlV += `<div class="tick-v ${isMajor ? 'major' : 'minor'}" style="top: ${pct}%;"></div>`;
                        if (isMajor && inch > 0 && mm < hMm - 5) {
                            htmlV += `<div class="tick-v-num" style="top: ${pct}%;">${Math.round(inch)}"</div>`;
                        }
                    }
                } else if (currentRulerUnit === 'mm') {
                    // Millimeters scale
                    for (let mm = 0; mm <= wMm; mm += 5) {
                        const pct = (mm / wMm) * 100;
                        const isMajor = mm % 10 === 0;
                        htmlH += `<div class="tick-h ${isMajor ? 'major' : 'minor'}" style="left: ${pct}%;"></div>`;
                        if (isMajor && mm > 0 && mm < wMm - 5) {
                            htmlH += `<div class="tick-h-num" style="left: ${pct}%;">${mm}</div>`;
                        }
                    }
                    for (let mm = 0; mm <= hMm; mm += 5) {
                        const pct = (mm / hMm) * 100;
                        const isMajor = mm % 10 === 0;
                        htmlV += `<div class="tick-v ${isMajor ? 'major' : 'minor'}" style="top: ${pct}%;"></div>`;
                        if (isMajor && mm > 0 && mm < hMm - 5) {
                            htmlV += `<div class="tick-v-num" style="top: ${pct}%;">${mm}</div>`;
                        }
                    }
                } else {
                    // Centimeters scale (CM) - 1 cm = 10mm
                    for (let mm = 0; mm <= wMm; mm += 5) {
                        const pct = (mm / wMm) * 100;
                        const isMajor = mm % 10 === 0;
                        htmlH += `<div class="tick-h ${isMajor ? 'major' : 'minor'}" style="left: ${pct}%;"></div>`;
                        if (isMajor && mm > 0 && mm < wMm - 5) {
                            htmlH += `<div class="tick-h-num" style="left: ${pct}%;">${mm / 10}</div>`;
                        }
                    }
                    for (let mm = 0; mm <= hMm; mm += 5) {
                        const pct = (mm / hMm) * 100;
                        const isMajor = mm % 10 === 0;
                        htmlV += `<div class="tick-v ${isMajor ? 'major' : 'minor'}" style="top: ${pct}%;"></div>`;
                        if (isMajor && mm > 0 && mm < hMm - 5) {
                            htmlV += `<div class="tick-v-num" style="top: ${pct}%;">${mm / 10}</div>`;
                        }
                    }
                }

                ticksH.innerHTML = htmlH;
                ticksV.innerHTML = htmlV;
            });
        }

        // Setup Interactive Margin Handle Dragging
        function initRulerDrag() {
            getActivePageIndexes().forEach(pageIdx => {
                bindSinglePageRulerDrag(pageIdx);
            });
        }

        function bindSinglePageRulerDrag(pageIdx) {
            const rulerH = document.getElementById('rulerHorizontal_' + pageIdx);
            const rulerV = document.getElementById('rulerVertical_' + pageIdx);
            if (!rulerH || !rulerV) return;

            setupMarginDrag('rulerHandleLeft_' + pageIdx, rulerH, 'horizontal', 'left', (newValMm) => {
                document.getElementById('psMarginLeft').value = newValMm.toFixed(1);
                updatePageSetupVars();
                markUnsaved();
            });

            setupMarginDrag('rulerHandleRight_' + pageIdx, rulerH, 'horizontal', 'right', (newValMm) => {
                document.getElementById('psMarginRight').value = newValMm.toFixed(1);
                updatePageSetupVars();
                markUnsaved();
            });

            setupMarginDrag('rulerHandleTop_' + pageIdx, rulerV, 'vertical', 'top', (newValMm) => {
                document.getElementById('psMarginTop').value = newValMm.toFixed(1);
                updatePageSetupVars();
                markUnsaved();
            });

            setupMarginDrag('rulerHandleBottom_' + pageIdx, rulerV, 'vertical', 'bottom', (newValMm) => {
                document.getElementById('psMarginBottom').value = newValMm.toFixed(1);
                updatePageSetupVars();
                markUnsaved();
            });
        }

        function setupMarginDrag(handleId, rulerEl, axis, side, onUpdate) {
            const handle = document.getElementById(handleId);
            if (!handle) return;

            handle.addEventListener('mousedown', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const rect = rulerEl.getBoundingClientRect();
                const totalSizePx = axis === 'horizontal' ? rect.width : rect.height;

                const pageSize = document.getElementById('psPageSize').value;
                const orientation = document.getElementById('psOrientation').value;
                let totalSizeMm = 210;
                if (axis === 'horizontal') {
                    totalSizeMm = pageSize === 'Letter' ? (orientation === 'landscape' ? 279.4 : 215.9) : (pageSize === 'Legal' ? (orientation === 'landscape' ? 355.6 : 215.9) : (orientation === 'landscape' ? 297 : 210));
                } else {
                    totalSizeMm = pageSize === 'Letter' ? (orientation === 'landscape' ? 215.9 : 279.4) : (pageSize === 'Legal' ? (orientation === 'landscape' ? 215.9 : 355.6) : (orientation === 'landscape' ? 210 : 297));
                }

                function onMouseMove(moveEvt) {
                    let offsetPx = 0;
                    if (axis === 'horizontal') {
                        offsetPx = side === 'left' ? (moveEvt.clientX - rect.left) : (rect.right - moveEvt.clientX);
                    } else {
                        offsetPx = side === 'top' ? (moveEvt.clientY - rect.top) : (rect.bottom - moveEvt.clientY);
                    }

                    let mmVal = (offsetPx / totalSizePx) * totalSizeMm;
                    mmVal = Math.max(5, Math.min(80, mmVal));
                    onUpdate(mmVal);
                }

                function onMouseUp() {
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                }

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });
        }

        // Apply Page Setup CSS Variables & Print @page Styles
        function updatePageSetupVars() {
            const pageSize = document.getElementById('psPageSize').value;
            const orientation = document.getElementById('psOrientation').value;
            const marginTop = parseFloat(document.getElementById('psMarginTop').value || 18);
            const marginBottom = parseFloat(document.getElementById('psMarginBottom').value || 18);
            const marginLeft = parseFloat(document.getElementById('psMarginLeft').value || 20);
            const marginRight = parseFloat(document.getElementById('psMarginRight').value || 18);
            const rowSpacing = document.getElementById('psRowSpacing').value;
            const colSpacing = document.getElementById('psColSpacing').value;

            let pageWidth = '210mm';
            let pageHeight = '297mm';

            let wMm = 210;
            let hMm = 297;

            if (pageSize === 'Letter') {
                wMm = orientation === 'landscape' ? 279.4 : 215.9;
                hMm = orientation === 'landscape' ? 215.9 : 279.4;
                pageWidth = orientation === 'landscape' ? '11in' : '8.5in';
                pageHeight = orientation === 'landscape' ? '8.5in' : '11in';
            } else if (pageSize === 'Legal') {
                wMm = orientation === 'landscape' ? 355.6 : 215.9;
                hMm = orientation === 'landscape' ? 215.9 : 355.6;
                pageWidth = orientation === 'landscape' ? '14in' : '8.5in';
                pageHeight = orientation === 'landscape' ? '8.5in' : '14in';
            } else {
                // A4
                wMm = orientation === 'landscape' ? 297 : 210;
                hMm = orientation === 'landscape' ? 210 : 297;
                pageWidth = orientation === 'landscape' ? '297mm' : '210mm';
                pageHeight = orientation === 'landscape' ? '210mm' : '297mm';
            }

            document.querySelectorAll('.a4-page').forEach(pageEl => {
                pageEl.style.setProperty('--page-width', pageWidth);
                pageEl.style.setProperty('--page-height', pageHeight);
                pageEl.style.setProperty('--margin-top', marginTop + 'mm');
                pageEl.style.setProperty('--margin-bottom', marginBottom + 'mm');
                pageEl.style.setProperty('--margin-left', marginLeft + 'mm');
                pageEl.style.setProperty('--margin-right', marginRight + 'mm');
                pageEl.style.setProperty('--row-spacing', rowSpacing);
                pageEl.style.setProperty('--col-spacing', colSpacing);
            });

            // Update Ruler Shades & Handles
            getActivePageIndexes().forEach(pIdx => {
                const leftShade = document.getElementById('rulerHLeftShade_' + pIdx);
                const rightShade = document.getElementById('rulerHRightShade_' + pIdx);
                const handleLeft = document.getElementById('rulerHandleLeft_' + pIdx);
                const handleRight = document.getElementById('rulerHandleRight_' + pIdx);

                const topShade = document.getElementById('rulerVTopShade_' + pIdx);
                const bottomShade = document.getElementById('rulerVBottomShade_' + pIdx);
                const handleTop = document.getElementById('rulerHandleTop_' + pIdx);
                const handleBottom = document.getElementById('rulerHandleBottom_' + pIdx);

                const leftPct = (marginLeft / wMm) * 100;
                const rightPct = (marginRight / wMm) * 100;
                const topPct = (marginTop / hMm) * 100;
                const bottomPct = (marginBottom / hMm) * 100;

                if (leftShade) leftShade.style.width = leftPct + '%';
                if (rightShade) rightShade.style.width = rightPct + '%';
                if (handleLeft) { handleLeft.style.left = leftPct + '%'; handleLeft.style.right = 'auto'; }
                if (handleRight) { handleRight.style.right = rightPct + '%'; handleRight.style.left = 'auto'; }

                if (topShade) topShade.style.height = topPct + '%';
                if (bottomShade) bottomShade.style.height = bottomPct + '%';
                if (handleTop) { handleTop.style.top = topPct + '%'; handleTop.style.bottom = 'auto'; }
                if (handleBottom) { handleBottom.style.bottom = bottomPct + '%'; handleBottom.style.top = 'auto'; }
            });

            renderRulers();

            // Update Dynamic Print Styles
            const printStyleEl = document.getElementById('dynamicPrintStyles');
            printStyleEl.innerHTML = `
                @media print {
                    @page {
                        size: ${pageSize.toLowerCase()} ${orientation};
                        margin: ${marginTop}mm ${marginRight}mm ${marginBottom}mm ${marginLeft}mm;
                    }
                    html, body {
                        background: #fff !important;
                        padding: 0 !important;
                        margin: 0 !important;
                    }
                    .no-print,
                    .top-action-bar,
                    .page-setup-panel,
                    .formatting-toolbar-bar,
                    .toast-msg,
                    .instructions-panel,
                    .ruler-top-row,
                    .ruler-vertical {
                        display: none !important;
                    }
                    .document-wrapper {
                        padding: 0 !important;
                        margin: 0 !important;
                        gap: 0 !important;
                    }
                    .page-ruler-container {
                        margin-bottom: 0 !important;
                        display: block !important;
                    }
                    .a4-page {
                        box-shadow: none !important;
                        border: none !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        width: 100% !important;
                        min-height: auto !important;
                    }
                    .page-break {
                        page-break-before: always !important;
                        break-before: page !important;
                        margin-top: 0 !important;
                        padding-top: 0 !important;
                    }
                    [contenteditable="true"] {
                        background: transparent !important;
                        box-shadow: none !important;
                        outline: none !important;
                    }
                }
            `;
        }

        function togglePageSetupPanel() {
            const panel = document.getElementById('pageSetupPanel');
            panel.classList.toggle('active');
        }

        // Initialize layout on load
        document.addEventListener('DOMContentLoaded', function() {
            updatePageSetupVars();
            initRulerDrag();
        });

        // Enable TAB key inside single editable body box
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab' && e.target && e.target.id === 'letterParagraphs') {
                e.preventDefault();
                document.execCommand('insertText', false, '\t');
                markUnsaved();
            }
        });

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

        // MS Word Rich Formatting Commands
        function formatDoc(cmd, value = null) {
            document.execCommand(cmd, false, value);
            markUnsaved();
        }

        function applyFontName(font) {
            if (!font) return;
            document.execCommand('fontName', false, font);
            markUnsaved();
        }

        function applyFontSize(size) {
            if (!size) return;
            const sel = window.getSelection();
            if (!sel.rangeCount) return;
            const range = sel.getRangeAt(0);
            if (range.collapsed) {
                let node = sel.anchorNode;
                let elem = node ? (node.nodeType === 1 ? node : node.parentElement) : null;
                let para = elem ? elem.closest('.letter-body-single p, .letter-body-single div') : null;
                if (para) {
                    para.style.fontSize = size;
                    markUnsaved();
                }
            } else {
                const span = document.createElement('span');
                span.style.fontSize = size;
                try {
                    range.surroundContents(span);
                } catch(e) {
                    document.execCommand('fontSize', false, '3');
                }
                markUnsaved();
            }
        }

        function applyLineSpacing(spacing) {
            const bodyEl = document.getElementById('letterParagraphs');
            if (bodyEl) {
                bodyEl.style.lineHeight = spacing;
                bodyEl.querySelectorAll('p, div').forEach(p => p.style.lineHeight = spacing);
            }
            markUnsaved();
        }

        function applyForeColor(color) {
            document.execCommand('foreColor', false, color);
            markUnsaved();
        }

        function applyHiliteColor(color) {
            if (!document.execCommand('hiliteColor', false, color)) {
                document.execCommand('backColor', false, color);
            }
            markUnsaved();
        }

        function insertTabSpace() {
            document.execCommand('insertText', false, '\t');
            markUnsaved();
        }

        // Insert Elements (Table & Box)
        function promptInsertTable() {
            const rowsInput = prompt("Enter number of rows for table:", "3");
            if (rowsInput === null) return;
            const colsInput = prompt("Enter number of columns for table:", "3");
            if (colsInput === null) return;

            const rows = Math.max(1, parseInt(rowsInput) || 3);
            const cols = Math.max(1, parseInt(colsInput) || 3);

            let tableHtml = '<table class="inserted-custom-table" style="width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 11pt; border: 1px solid #000;"><thead><tr style="background-color: #f1f5f9;">';
            for (let c = 1; c <= cols; c++) {
                tableHtml += `<th style="border: 1px solid #000; padding: 6px 10px; text-align: left; font-weight: bold;">Header ${c}</th>`;
            }
            tableHtml += '</tr></thead><tbody>';

            for (let r = 1; r <= rows; r++) {
                tableHtml += '<tr>';
                for (let c = 1; c <= cols; c++) {
                    tableHtml += `<td style="border: 1px solid #000; padding: 6px 10px;">Row ${r} Col ${c}</td>`;
                }
                tableHtml += '</tr>';
            }
            tableHtml += '</tbody></table><p class="main-para"><br></p>';

            insertHtmlAtCursor(tableHtml);
            markUnsaved();
            showToast(`Inserted ${rows}x${cols} Custom Table`);
        }

        function promptInsertBox() {
            const boxHtml = '<div class="inserted-callout-box" style="background: #f8fafc; border-left: 4px solid #2563eb; padding: 10px 14px; margin: 12px 0; font-size: 11pt; border-top: 1px solid #cbd5e1; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; border-radius: 4px;"><strong>Note / Instruction:</strong> Click here to edit box text...</div><p class="main-para"><br></p>';
            insertHtmlAtCursor(boxHtml);
            markUnsaved();
            showToast('Inserted Callout Box');
        }

        function insertHtmlAtCursor(html) {
            const sel = window.getSelection();
            if (sel.getRangeAt && sel.rangeCount) {
                const range = sel.getRangeAt(0);
                let container = range.commonAncestorContainer;
                if (container.nodeType !== 1) container = container.parentElement;
                const editableParent = container.closest('[contenteditable="true"]');
                if (editableParent) {
                    range.deleteContents();
                    const el = document.createElement("div");
                    el.innerHTML = html;
                    const frag = document.createDocumentFragment();
                    let node, lastNode;
                    while ((node = el.firstChild)) {
                        lastNode = frag.appendChild(node);
                    }
                    range.insertNode(frag);
                    if (lastNode) {
                        range.setStartAfter(lastNode);
                        range.collapse(true);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    }
                    return;
                }
            }
            // Fallback: Append directly inside continuous body box
            const bodyEl = document.getElementById('letterParagraphs');
            if (bodyEl) {
                bodyEl.insertAdjacentHTML('beforeend', html);
            }
        }

        // Save Template via AJAX
        function saveTemplate() {
            const saveBtn = document.getElementById('btnSaveDoc');
            const ind = document.getElementById('saveIndicator');
            
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            ind.className = 'save-indicator saving';
            ind.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            const bodyEl = document.getElementById('letterParagraphs');
            const pEls = bodyEl.querySelectorAll('p, div');
            let paragraphs = [];
            if (pEls.length > 0) {
                pEls.forEach(el => {
                    let text = el.innerHTML.trim();
                    if (text) {
                        text = text.replace(/^(\s*[0-9a-zA-Z]+\.)[ \t]+/i, '$1\t');
                        paragraphs.push(text);
                    }
                });
            } else {
                paragraphs = bodyEl.innerHTML.split(/<br\s*\/?>|\n/).map(s => s.trim()).filter(Boolean);
            }

            const pageSetupObj = {
                page_size: document.getElementById('psPageSize').value,
                orientation: document.getElementById('psOrientation').value,
                margin_top: document.getElementById('psMarginTop').value,
                margin_bottom: document.getElementById('psMarginBottom').value,
                margin_left: document.getElementById('psMarginLeft').value,
                margin_right: document.getElementById('psMarginRight').value,
                row_spacing: document.getElementById('psRowSpacing').value,
                col_spacing: document.getElementById('psColSpacing').value,

                header_org_name: document.getElementById('header_org_name') ? document.getElementById('header_org_name').innerText.trim() : 'Naval Research & Development Institute',
                header_wing: document.getElementById('header_wing') ? document.getElementById('header_wing').innerText.trim() : 'R&D Wing',
                header_base: document.getElementById('header_base') ? document.getElementById('header_base').innerText.trim() : 'at PNS JAUHAR',
                header_address: document.getElementById('header_address') ? document.getElementById('header_address').innerText.trim() : 'Habib Rehmatullah Road',
                header_city: document.getElementById('header_city') ? document.getElementById('header_city').innerText.trim() : 'KARACHI',
                header_phone: document.getElementById('header_phone') ? document.getElementById('header_phone').innerText.trim() : 'Ph (off): 48504781',
                see_distribution: document.getElementById('see_distribution') ? document.getElementById('see_distribution').innerText.trim() : 'See distribution:',
                ref_prefix: document.getElementById('ref_prefix') ? document.getElementById('ref_prefix').innerText.trim() : 'R&D/Projects/Proc/',
                to_it_no_prefix: document.getElementById('to_it_no_prefix') ? document.getElementById('to_it_no_prefix').innerText.trim() : 'TO IT NO ',
                annex_label: document.getElementById('annex_label') ? document.getElementById('annex_label').innerText.trim() : 'ANNEX A',
                dated_label: document.getElementById('dated_label') ? document.getElementById('dated_label').innerText.trim() : 'Dated :',
                annex_title: document.getElementById('annex_title') ? document.getElementById('annex_title').innerText.trim() : 'LIST OF REQUIRED ITEMS',
                th_sno: document.getElementById('th_sno') ? document.getElementById('th_sno').innerText.trim() : 'S No',
                th_spec: document.getElementById('th_spec') ? document.getElementById('th_spec').innerText.trim() : 'Item / specification',
                th_qty: document.getElementById('th_qty') ? document.getElementById('th_qty').innerText.trim() : 'Qty',

                editable_ref_no: document.getElementById('permRefNo').checked,
                editable_date: document.getElementById('permDate').checked,
                editable_subject: document.getElementById('permSubject').checked,
                editable_paragraphs: document.getElementById('permParagraphs').checked,
                editable_signatory: document.getElementById('permSignatory').checked,
                editable_firms: document.getElementById('permFirms').checked,
                editable_items: document.getElementById('permItems').checked,
            };

            const payload = {
                subject: document.getElementById('subject').innerText.trim(),
                paragraphs: paragraphs,
                signatory_name: document.getElementById('signatory_name').innerText.trim(),
                signatory_rank: document.getElementById('signatory_rank').innerText.trim(),
                signatory_dept: document.getElementById('signatory_dept').innerText.trim(),
                page_setup: pageSetupObj,
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ route('nrdi.purchase_cases_new.it_template.save') }}", {
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
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Template';
                if (data.success) {
                    markSaved();
                    showToast('Template & Page Setup saved successfully!');
                } else {
                    ind.className = 'save-indicator unsaved';
                    ind.innerHTML = '<i class="fas fa-exclamation-circle"></i> Save Failed';
                    alert('Error saving template: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Template';
                ind.className = 'save-indicator unsaved';
                ind.innerHTML = '<i class="fas fa-exclamation-circle"></i> Save Failed';
                console.error(err);
                alert('Network error while saving template.');
            });
        }

        // Reset to factory defaults
        function resetFactoryDefaults() {
            if (!confirm('Are you sure you want to reset the template and page setup to factory defaults? This will discard all custom margins and text.')) {
                return;
            }

            const defaultParas = [
                "1.\tR&D Wing NRDI at PNS JAUHAR is interested for the Procurement of {ITEM_TITLE}. In this regard, quotations are required to be submitted to MD R&D at NRDI by {DEADLINE_DATE}.",
                "2.\tQuotation will be opened on same day at 11:00 hrs in the presence of the participants or their representatives and will be accepted at lowest quotations rate basis. However, it is apprised that MD (R&D) reserves the right to accept/ reject any quotation without assigning any reason.",
                "a.\tThe envelope and the quote must bear the reference of tender number.",
                "b.\tThe validity period be clearly mentioned in quote. Atleast 30 days for locally available items and incase of imported items validity be either as per OEM or 60 days whichever falls early.",
                "c.\tQuote must be in conformance to the specifications given in the tender. Non-conforming or incomplete quotes will not be considered.",
                "d.\tItems available locally are to be delivered within 15 days after issuance of purchase order.",
                "e.\tPart Delivery / Partial payment or request for any advance payment shall not be entertained.",
                "f.\tWarrantee / Guarantee of one year is required.",
                "g.\tPayment will be processed / made after delivery and acceptance by user.",
                "3.\tIn case of any query; kindly contact well within time on dir-pandi@paknavy.gov.pk. Furthermore, it is requested to acknowledge receipt of tender/e-mail.",
            ];

            const container = document.getElementById('letterParagraphs');
            container.innerHTML = defaultParas.map((pText) => {
                const isSub = /^\s*[a-g]\./i.test(pText.trim());
                return `<p class="${isSub ? 'sub-para' : 'main-para'}">${pText}</p>`;
            }).join('');

            document.getElementById('header_org_name').innerText = 'Naval Research & Development Institute';
            document.getElementById('header_wing').innerText = 'R&D Wing';
            document.getElementById('header_base').innerText = 'at PNS JAUHAR';
            document.getElementById('header_address').innerText = 'Habib Rehmatullah Road';
            document.getElementById('header_city').innerText = 'KARACHI';
            document.getElementById('header_phone').innerText = 'Ph (off): 48504781';
            document.getElementById('see_distribution').innerText = 'See distribution:';
            document.getElementById('ref_prefix').innerText = 'R&D/Projects/Proc/';
            document.getElementById('annex_ref_prefix').innerText = 'R&D/Projects/Proc/';
            document.getElementById('to_it_no_prefix').innerText = 'TO IT NO ';
            document.getElementById('annex_label').innerText = 'ANNEX A';
            document.getElementById('dated_label').innerText = 'Dated :';
            document.getElementById('subject').innerText = 'REQUEST FOR QUOTATION';
            document.getElementById('signatory_name').innerText = 'MUHAMMAD MUDASSIR';
            document.getElementById('signatory_rank').innerText = 'Cdr (R) Pakistan Navy';
            document.getElementById('signatory_dept').innerText = 'Dir Procurement';
            document.getElementById('annex_title').innerText = 'LIST OF REQUIRED ITEMS';
            document.getElementById('th_sno').innerText = 'S No';
            document.getElementById('th_spec').innerText = 'Item / specification';
            document.getElementById('th_qty').innerText = 'Qty';

            document.getElementById('psPageSize').value = 'A4';
            document.getElementById('psOrientation').value = 'portrait';
            document.getElementById('psMarginTop').value = '18';
            document.getElementById('psMarginBottom').value = '18';
            document.getElementById('psMarginLeft').value = '20';
            document.getElementById('psMarginRight').value = '18';
            document.getElementById('psRowSpacing').value = '5pt';
            document.getElementById('psColSpacing').value = '6px';

            document.getElementById('permRefNo').checked = true;
            document.getElementById('permDate').checked = true;
            document.getElementById('permSubject').checked = true;
            document.getElementById('permParagraphs').checked = true;
            document.getElementById('permSignatory').checked = true;
            document.getElementById('permFirms').checked = true;
            document.getElementById('permItems').checked = true;

            updatePageSetupVars();

            saveTemplate();
            showToast('Reset to factory defaults & saved');
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
                e.returnValue = 'You have unsaved changes in the template.';
            }
        });
    </script>
</body>
</html>
