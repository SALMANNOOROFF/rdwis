<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IT Letter Default Template Editor</title>
    
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
        .btn-save {
            background: #10b981;
            color: #fff;
        }
        .btn-save:hover {
            background: #059669;
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
            z-index: 99998;
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
        }

        /* A4 Page with Left: 1.0" (25.4mm), Right: 0.8" (20.32mm) */
        .a4-page {
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            padding: 25.4mm 20.32mm 25.4mm 25.4mm;
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

        /* Paragraphs with Tab Stop indentation */
        .para-wrapper {
            position: relative;
            margin-bottom: 8pt;
        }
        .editable-para {
            margin: 0;
            text-align: justify;
            white-space: pre-wrap;
            tab-size: 36px;
            -moz-tab-size: 36px;
            font-size: 12pt;
            line-height: 1.5;
            font-family: Arial, Helvetica, sans-serif;
            word-break: break-word;
        }
        .editable-para.main-para {
            padding-left: 0;
            text-indent: 0;
            margin-bottom: 12pt;
        }
        .editable-para.sub-para {
            padding-left: 54px;
            text-indent: 0;
            margin-bottom: 8pt;
        }
        .para-actions-hover {
            position: absolute;
            top: -14px;
            right: 0px;
            display: flex;
            align-items: center;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.15s ease-in-out;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 2px 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            z-index: 100;
        }
        .para-wrapper:hover .para-actions-hover {
            opacity: 1;
        }
        .btn-insert-para {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #7dd3fc;
            border-radius: 4px;
            padding: 2px 7px;
            font-size: 10.5px;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .btn-insert-para:hover {
            background: #0284c7;
            color: #fff;
            border-color: #0284c7;
        }
        .btn-del-para {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #f87171;
            border-radius: 4px;
            padding: 3px 6px;
            font-size: 10.5px;
            font-weight: bold;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            transition: all 0.15s ease;
        }
        .btn-del-para:hover {
            background: #dc2626;
            color: #fff;
            border-color: #dc2626;
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

        /* Subject */
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
            font-weight: normal;
        }

        /* Instructions Panel */
        .instructions-panel {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 16px 20px;
            margin-top: 20px;
            max-width: 210mm;
            width: 100%;
        }
        .instructions-panel h4 {
            margin: 0 0 10px 0;
            font-size: 13px;
            font-weight: bold;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .instructions-panel ul {
            margin: 0;
            padding-left: 18px;
            font-size: 12px;
            color: #78350f;
            line-height: 1.7;
        }
        .instructions-panel code {
            background: #fef3c7;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 11.5px;
            font-weight: bold;
            color: #92400e;
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
            }
            .no-print,
            .top-action-bar,
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
                min-height: auto !important;
            }
            .para-wrapper {
                page-break-inside: avoid;
                break-inside: avoid;
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
                <span><i class="fas fa-file-alt" style="color: #60a5fa; margin-right: 4px;"></i> IT Letter — Default Template Editor</span>
                <span class="save-indicator" id="saveIndicator">
                    <i class="fas fa-check-circle"></i> Saved
                </span>
            </div>
        </div>

        <div class="top-bar-actions">
            <button type="button" class="action-btn btn-reset" onclick="resetFactoryDefaults()" title="Reset to factory default template text">
                <i class="fas fa-undo"></i> Reset Factory Defaults
            </button>
            <button type="button" class="action-btn btn-save" id="btnSaveDoc" onclick="saveTemplate()">
                <i class="fas fa-save"></i> Save Template
            </button>
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
            </select>
        </div>

        <!-- Font Size Selector -->
        <div class="toolbar-group">
            <select id="tbFontSize" class="tb-select" onchange="applyFontSize(this.value)" title="Font Size">
                <option value="9pt">9 pt</option>
                <option value="10pt">10 pt</option>
                <option value="11pt">11 pt</option>
                <option value="12pt" selected>12 pt</option>
                <option value="14pt">14 pt</option>
                <option value="16pt">16 pt</option>
                <option value="18pt">18 pt</option>
                <option value="20pt">20 pt</option>
            </select>
        </div>

        <div class="toolbar-divider"></div>

        <!-- Text Styling Buttons -->
        <div class="toolbar-group">
            <button type="button" class="tb-btn" onclick="formatDoc('bold')" title="Bold (Ctrl+B)"><i class="fas fa-bold"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('italic')" title="Italic (Ctrl+I)"><i class="fas fa-italic"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('underline')" title="Underline (Ctrl+U)"><i class="fas fa-underline"></i></button>
            <button type="button" class="tb-btn" onclick="formatDoc('strikeThrough')" title="Strikethrough"><i class="fas fa-strikethrough"></i></button>
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
            <button type="button" class="tb-btn" onclick="formatDoc('removeFormat')" title="Clear Formatting"><i class="fas fa-remove-format"></i> Clear</button>
        </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div class="toast-msg" id="toastMsg">
        <i class="fas fa-check-circle" style="font-size: 16px; color: #34d399;"></i>
        <span id="toastText">Template saved successfully!</span>
    </div>

    <!-- DOCUMENT CONTAINER -->
    <div class="document-wrapper">

        <!-- INSTRUCTIONS PANEL -->
        <div class="instructions-panel no-print">
            <h4><i class="fas fa-info-circle"></i> Template Instructions</h4>
            <ul>
                <li>Changes here update the <strong>default template</strong> used when creating new IT letters for purchase cases.</li>
                <li>Existing per-case IT letters already saved will <strong>NOT</strong> be affected.</li>
                <li>Use <code>{ITEM_TITLE}</code> placeholder — it will be replaced with the case's procurement title automatically.</li>
                <li>Use <code>{DEADLINE_DATE}</code> placeholder — it will be replaced with the case deadline date (14 days from case date).</li>
                <li>You can add, remove, or reorder paragraphs. Sub-paragraphs starting with <code>a.</code> to <code>g.</code> will be auto-indented.</li>
            </ul>
        </div>

        <!-- A4 TEMPLATE PAGE -->
        <div class="a4-page" id="templatePage">

            <!-- SUBJECT -->
            <div class="letter-subject">
                <u><span contenteditable="true" id="subject" oninput="markUnsaved()">{{ $template->subject }}</span></u>
            </div>

            <!-- BODY PARAGRAPHS -->
            <div class="letter-body" id="letterParagraphs">
                @foreach($template->paragraphs as $pIndex => $pText)
                @php
                    $cleanPText = preg_replace('/^(\s*[0-9a-gA-G]+\.)[ \t]+/u', "$1\t", $pText);
                    $isSub = preg_match('/^\s*[a-g]\./i', trim($cleanPText));
                @endphp
                <div class="para-wrapper" data-index="{{ $pIndex }}">
                    <div class="editable-para {{ $isSub ? 'sub-para' : 'main-para' }}" contenteditable="true" oninput="markUnsaved()">{!! $cleanPText !!}</div>
                    <div class="para-actions-hover no-print">
                        <button type="button" class="btn-insert-para" onclick="insertParagraphAfter(this)" title="Insert New Paragraph Here">
                            <i class="fas fa-plus"></i> Add Para
                        </button>
                        @if($pIndex > 0)
                        <button type="button" class="btn-del-para" onclick="removeParagraph(this)" title="Delete Paragraph">
                            <i class="fas fa-times"></i> Del
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <!-- ADD PARAGRAPH BUTTON -->
            <div class="no-print" style="margin-top: 2px; margin-bottom: 18px;">
                <button type="button" class="btn-add-para" onclick="addParagraph()">
                    <i class="fas fa-plus"></i> Add Paragraph
                </button>
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

    <!-- JAVASCRIPT -->
    <script>
        let hasUnsavedChanges = false;

        // Enable TAB key inside editable paragraphs
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab' && e.target && e.target.classList && e.target.classList.contains('editable-para')) {
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

        // Dynamic Paragraphs
        function addParagraph() {
            const container = document.getElementById('letterParagraphs');
            const wrap = document.createElement('div');
            wrap.className = 'para-wrapper';
            wrap.innerHTML = `
                <div class="editable-para main-para" contenteditable="true" oninput="markUnsaved()">New paragraph text...</div>
                <div class="para-actions-hover no-print">
                    <button type="button" class="btn-insert-para" onclick="insertParagraphAfter(this)" title="Insert New Paragraph Here">
                        <i class="fas fa-plus"></i> Add Para
                    </button>
                    <button type="button" class="btn-del-para" onclick="removeParagraph(this)" title="Delete Paragraph">
                        <i class="fas fa-times"></i> Del
                    </button>
                </div>
            `;
            container.appendChild(wrap);
            markUnsaved();
            const newEl = wrap.querySelector('.editable-para');
            if (newEl) newEl.focus();
        }

        function insertParagraphAfter(btn) {
            const currentWrap = btn.closest('.para-wrapper');
            if (!currentWrap) return;
            const wrap = document.createElement('div');
            wrap.className = 'para-wrapper';
            wrap.innerHTML = `
                <div class="editable-para main-para" contenteditable="true" oninput="markUnsaved()">New paragraph text...</div>
                <div class="para-actions-hover no-print">
                    <button type="button" class="btn-insert-para" onclick="insertParagraphAfter(this)" title="Insert New Paragraph Here">
                        <i class="fas fa-plus"></i> Add Para
                    </button>
                    <button type="button" class="btn-del-para" onclick="removeParagraph(this)" title="Delete Paragraph">
                        <i class="fas fa-times"></i> Del
                    </button>
                </div>
            `;
            currentWrap.after(wrap);
            markUnsaved();
            const newEl = wrap.querySelector('.editable-para');
            if (newEl) newEl.focus();
        }

        function removeParagraph(btn) {
            const wrap = btn.closest('.para-wrapper');
            if (wrap) {
                wrap.remove();
                markUnsaved();
            }
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
                let para = elem ? elem.closest('.editable-para') : null;
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
            const sel = window.getSelection();
            let node = sel.rangeCount ? sel.anchorNode : null;
            let elem = node ? (node.nodeType === 1 ? node : node.parentElement) : null;
            let para = elem ? elem.closest('.editable-para') : null;
            if (para) {
                para.style.lineHeight = spacing;
            } else {
                document.querySelectorAll('.editable-para').forEach(p => p.style.lineHeight = spacing);
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

        // Save Template via AJAX
        function saveTemplate() {
            const saveBtn = document.getElementById('btnSaveDoc');
            const ind = document.getElementById('saveIndicator');
            
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            ind.className = 'save-indicator saving';
            ind.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

            const paragraphs = [];
            document.querySelectorAll('#letterParagraphs .editable-para').forEach(el => {
                let pText = el.innerHTML.trim();
                pText = pText.replace(/^(\s*[0-9a-zA-Z]+\.)[ \t]+/i, '$1\t');
                paragraphs.push(pText);
            });

            const payload = {
                subject: document.getElementById('subject').innerText.trim(),
                paragraphs: paragraphs,
                signatory_name: document.getElementById('signatory_name').innerText.trim(),
                signatory_rank: document.getElementById('signatory_rank').innerText.trim(),
                signatory_dept: document.getElementById('signatory_dept').innerText.trim(),
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
                    showToast('Template saved successfully!');
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
            if (!confirm('Are you sure you want to reset the template to factory defaults? This will discard all your custom changes.')) {
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
            container.innerHTML = defaultParas.map((pText, idx) => {
                const isSub = /^\s*[a-g]\./i.test(pText.trim());
                return `
                    <div class="para-wrapper">
                        <div class="editable-para ${isSub ? 'sub-para' : 'main-para'}" contenteditable="true" oninput="markUnsaved()">${pText}</div>
                        <div class="para-actions-hover no-print">
                            <button type="button" class="btn-insert-para" onclick="insertParagraphAfter(this)" title="Insert New Paragraph Here">
                                <i class="fas fa-plus"></i> Add Para
                            </button>
                            ${idx > 0 ? `<button type="button" class="btn-del-para" onclick="removeParagraph(this)" title="Delete Paragraph"><i class="fas fa-times"></i> Del</button>` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            document.getElementById('subject').innerText = 'REQUEST FOR QUOTATION';
            document.getElementById('signatory_name').innerText = 'MUHAMMAD MUDASSIR';
            document.getElementById('signatory_rank').innerText = 'Cdr (R) Pakistan Navy';
            document.getElementById('signatory_dept').innerText = 'Dir Procurement';

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
