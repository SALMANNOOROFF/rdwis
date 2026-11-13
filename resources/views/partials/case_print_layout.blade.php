<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Print Case') &mdash; RDWIS</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        * { box-sizing: border-box; }
        @page {
            size: A4 portrait;
            margin: 12mm 14mm;
        }
        body {
            background-color: #ffffff;
            margin: 0;
            padding: 28px 24px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.45;
        }

        /* Screen-only subtle action bar */
        .no-print-bar {
            max-width: 900px;
            margin: 0 auto 18px auto;
            padding: 8px 14px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .no-print-status {
            font-size: 9pt;
            font-weight: 600;
            color: #334155;
        }
        .no-print-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .toggle-docs-label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 9pt;
            font-weight: 600;
            color: #1e293b;
            cursor: pointer;
        }
        .btn-print-main {
            background: #0f172a;
            color: #ffffff;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 9pt;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print-main:hover { background: #1e293b; }
        .btn-close-main {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 5px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 9pt;
            cursor: pointer;
        }

        /* Document Container matching case_detail.blade.php */
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .doc-top-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 18px;
        }
        .main-title {
            font-size: 14.5pt;
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
            text-align: left;
            line-height: 1.3;
        }
        .record-purpose-tag {
            font-size: 8.5pt;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #0f172a;
            white-space: nowrap;
            padding-top: 3px;
        }

        /* Clean 3-column borderless metadata grid (exact match to case_detail) */
        .meta-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 0.85fr;
            gap: 6px 26px;
            margin-bottom: 20px;
        }
        .meta-item {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }
        .meta-label {
            font-weight: 700;
            min-width: 82px;
            flex-shrink: 0;
            color: #0f172a;
        }
        .meta-value {
            flex-grow: 1;
            color: #1e293b;
        }

        /* Plain underlined section title (no boxes) */
        .section-title {
            font-weight: 700;
            font-size: 10.5pt;
            color: #0f172a;
            margin: 18px 0 7px 0;
            display: block;
            text-decoration: underline;
        }

        /* Clean data tables matching case_detail */
        table.doc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 9.5pt;
        }
        table.doc-table th, table.doc-table td {
            border: 1px solid #999999;
            padding: 5px 8px;
            text-align: left;
            vertical-align: top;
        }
        table.doc-table th {
            background-color: #f2f2f2;
            font-weight: 700;
            color: #0f172a;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        /* Plain text fulfillment / RFQ summary */
        .plain-summary-block {
            margin: 10px 0 16px 0;
            font-size: 9.8pt;
            color: #0f172a;
        }

        /* Plain-text Approval & Remarks Trail (NO boxes) */
        .remarks-plain-list {
            margin: 6px 0 18px 0;
        }
        .remark-text-item {
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px dotted #d1d5db;
            page-break-inside: avoid;
        }
        .remark-text-item:last-child {
            border-bottom: none;
        }
        .remark-text-head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 9.6pt;
            color: #0f172a;
        }
        .remark-text-date {
            font-size: 8.8pt;
            color: #475569;
            white-space: nowrap;
        }
        .remark-text-body {
            margin-top: 3px;
            padding-left: 16px;
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.45;
            color: #111827;
        }
        .remark-text-body ol, .remark-text-body ul {
            margin: 0;
            padding-left: 18px;
        }

        /* Point-wise Attachments List (NO boxes / NO View button) */
        .enclosure-point-list {
            margin: 6px 0 16px 0;
            padding-left: 20px;
            font-size: 9.6pt;
        }
        .enclosure-point-list li {
            margin-bottom: 4px;
            color: #0f172a;
        }
        .enclosure-inline-link {
            font-size: 8.5pt;
            color: #2563eb;
            text-decoration: underline;
            margin-left: 6px;
        }

        /* Rendered Attached Documents Directly Below in Print Shape */
        .enclosures-render-wrap {
            margin-top: 22px;
        }
        .enclosure-sheet {
            page-break-before: always;
            break-before: page;
            padding-top: 4px;
        }
        .enclosure-sheet-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            border-bottom: 1px solid #64748b;
            padding-bottom: 4px;
            margin-bottom: 10px;
            font-size: 9pt;
            color: #334155;
        }
        .enclosure-rendered-img {
            display: block;
            width: 100%;
            max-width: 100%;
            height: auto;
            margin: 0 auto 10px auto;
            border: 1px solid #e2e8f0;
        }
        .pdf-page-wrap {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .pdf-loading-msg {
            font-size: 9pt;
            color: #475569;
            font-style: italic;
            padding: 8px 0;
        }

        .footer {
            margin-top: 28px;
            font-size: 8.5pt;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            color: #475569;
        }

        body.hide-rendered-docs .enclosures-render-wrap {
            display: none !important;
        }

        @media print {
            body {
                padding: 0 !important;
                margin: 0 !important;
                background: #ffffff !important;
            }
            .no-print, .no-print-bar {
                display: none !important;
            }
            .container {
                max-width: none !important;
                width: 100% !important;
                margin: 0 !important;
            }
            body.hide-rendered-docs .enclosures-render-wrap {
                display: none !important;
            }
            .enclosure-rendered-img {
                border: none !important;
            }
        }
    </style>
    @stack('print_styles')
</head>
<body>

    <div class="no-print-bar no-print">
        <div class="no-print-status">
            <i class="fas fa-file-alt mr-1"></i>
            <span id="pdfRenderStatusText">Document ready</span>
        </div>
        <div class="no-print-actions">
            <label class="toggle-docs-label" title="Toggle attached document pages rendered below">
                <input type="checkbox" id="toggleRenderedDocs" checked onchange="document.body.classList.toggle('hide-rendered-docs', !this.checked)">
                <span>Include Attached Docs Below</span>
            </label>
            <button type="button" class="btn-print-main" onclick="window.print()">
                <i class="fas fa-print"></i> Print Case
            </button>
            <button type="button" class="btn-close-main" onclick="window.close()">
                Close
            </button>
        </div>
    </div>

    <div class="container">
        @yield('content')

        <div class="footer">
            <span>RDWIS &mdash; Record Copy</span>
            <span>Printed on {{ now()->format('d M y H:i') }} by {{ auth()->user()->acc_name ?? 'Authorized User' }}</span>
        </div>
    </div>

    <script src="{{ asset('plugins/pdfjs/pdf.min.js') }}"></script>
    <script>
        (function () {
            const pdfContainers = document.querySelectorAll('.pdf-render-container[data-pdf-url]');
            const statusEl = document.getElementById('pdfRenderStatusText');

            if (!pdfContainers.length || typeof window.pdfjsLib === 'undefined') {
                if (statusEl) statusEl.textContent = 'Ready to print';
                return;
            }

            window.pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('plugins/pdfjs/pdf.worker.min.js') }}";

            let completedDocs = 0;
            const totalDocs = pdfContainers.length;
            if (statusEl) statusEl.textContent = `Loading attached PDF(s) (0/${totalDocs})...`;

            async function renderSinglePdf(container) {
                const url = container.getAttribute('data-pdf-url');
                try {
                    const loadingTask = window.pdfjsLib.getDocument({ url: url, withCredentials: true });
                    const pdf = await loadingTask.promise;
                    container.innerHTML = '';

                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const page = await pdf.getPage(pageNum);
                        const viewport = page.getViewport({ scale: 1.8 });
                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;

                        await page.render({ canvasContext: context, viewport: viewport }).promise;

                        const pageWrap = document.createElement('div');
                        pageWrap.className = 'pdf-page-wrap';
                        const img = document.createElement('img');
                        img.className = 'enclosure-rendered-img';
                        img.src = canvas.toDataURL('image/png');
                        img.alt = `Page ${pageNum}`;
                        pageWrap.appendChild(img);
                        container.appendChild(pageWrap);
                    }
                } catch (err) {
                    container.innerHTML = `<div class="pdf-loading-msg">Unable to inline-render PDF on page (${err && err.message ? err.message : 'file unavailable'}). Click link in list above to open directly.</div>`;
                } finally {
                    completedDocs++;
                    if (statusEl) {
                        statusEl.textContent = completedDocs >= totalDocs
                            ? `All ${totalDocs} attached PDF(s) loaded in print layout`
                            : `Loading attached PDF(s) (${completedDocs}/${totalDocs})...`;
                    }
                }
            }

            Promise.all(Array.from(pdfContainers).map(renderSinglePdf));
        })();
    </script>
</body>
</html>
