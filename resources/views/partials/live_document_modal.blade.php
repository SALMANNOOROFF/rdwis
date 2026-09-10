{{-- ================================================================= --}}
{{-- UNIVERSAL LIVE DOCUMENT VIEWER MODAL                              --}}
{{-- Renders PDF, Word (.docx/.doc), Excel (.xlsx/.xls/.csv), Images,  --}}
{{-- and text live without triggering forced browser downloads.        --}}
{{-- ================================================================= --}}

<div class="modal fade" id="rdLiveDocViewerModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 94%; height: 92vh; margin: 20px auto;">
        <div class="modal-content shadow-lg" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; height: 100%; display: flex; flex-direction: column;">
            
            {{-- MODAL HEADER --}}
            <div class="modal-header py-2.5 px-3 d-flex align-items-center justify-content-between flex-shrink-0" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <div class="d-flex align-items-center overflow-hidden mr-2">
                    <span id="rdLiveDocTypeIcon" class="mr-2 text-primary" style="font-size: 18px;">
                        <i class="fas fa-file-alt"></i>
                    </span>
                    <h6 class="modal-title font-weight-bold text-dark mb-0 text-truncate rajdhani" id="rdLiveDocViewerTitle" style="letter-spacing: 0.5px; font-size: 1.05rem;">
                        DOCUMENT PREVIEW
                    </h6>
                    <span id="rdLiveDocExtBadge" class="badge badge-primary ml-2 px-2 py-0.5 text-uppercase font-weight-bold font-mono" style="font-size: 11px;">
                        DOC
                    </span>
                </div>

                <div class="d-flex align-items-center" style="gap: 8px;">
                    {{-- Open In New Tab Button --}}
                    <a href="#" id="rdLiveDocOpenNewTab" target="_blank" class="btn btn-xs btn-outline-primary px-2.5 py-1 font-weight-bold" style="font-size: 11px; border-radius: 6px;" title="Open in New Browser Tab">
                        <i class="fas fa-external-link-alt mr-1"></i> Open in Tab
                    </a>

                    {{-- Download Button --}}
                    <a href="#" id="rdLiveDocDownloadBtn" download class="btn btn-xs btn-outline-secondary px-2.5 py-1 font-weight-bold" style="font-size: 11px; border-radius: 6px;" title="Download Copy">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>

                    {{-- Close Button --}}
                    <button type="button" class="close text-dark ml-2" data-dismiss="modal" aria-label="Close" style="opacity: 0.7; outline: none;">
                        <span aria-hidden="true" style="font-size: 1.4rem;">&times;</span>
                    </button>
                </div>
            </div>

            {{-- MODAL BODY (LIVE VIEWER CANVASES) --}}
            <div class="modal-body p-0 flex-grow-1" style="background: #0f172a; position: relative; overflow: hidden;" id="rdLiveDocViewerBody">
                
                {{-- 1. Loading Overlay --}}
                <div id="rdLiveDocLoading" style="display:none; position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.94); align-items:center; justify-content:center; flex-direction:column; z-index:20;">
                    <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
                    <span class="text-dark rajdhani font-weight-bold" style="font-size: 17px; letter-spacing: 1px;">RENDERING DOCUMENT LIVE...</span>
                    <span class="text-muted small mt-1">Preparing document for high-fidelity interactive view</span>
                </div>

                {{-- 2. Iframe (PDF & Native Browser Documents) --}}
                <iframe id="rdLiveDocIframe" src="" style="display:none; width: 100%; height: 100%; border: none; background: #ffffff;"></iframe>

                {{-- 3. Image Viewer Canvas --}}
                <div id="rdLiveDocImgWrap" style="display:none; width: 100%; height: 100%; overflow: auto; align-items: center; justify-content: center; padding: 25px; background: #1e293b;">
                    <img id="rdLiveDocImg" src="" alt="Document Preview" style="max-width: 100%; max-height: 100%; object-fit: contain; box-shadow: 0 8px 30px rgba(0,0,0,0.4); border-radius: 8px; border: 1px solid #334155; background: #ffffff;">
                </div>

                {{-- 4. Word Document (.docx / .doc) Canvas (via Mammoth.js) --}}
                <div id="rdLiveDocWordWrap" style="display:none; width: 100%; height: 100%; overflow-y: auto; background: #f1f5f9; padding: 30px 15px; justify-content: center;">
                    <div id="rdLiveDocWordContent" style="background: #ffffff; color: #1e293b; width: 100%; max-width: 880px; min-height: 100%; padding: 50px 60px; box-shadow: 0 4px 25px rgba(0,0,0,0.08); border-radius: 8px; border: 1px solid #e2e8f0; font-family: 'Segoe UI', Calibri, Arial, sans-serif; font-size: 14px; line-height: 1.7;">
                    </div>
                </div>

                {{-- 5. Excel Spreadsheet (.xlsx / .xls / .csv) Canvas (via SheetJS) --}}
                <div id="rdLiveDocSheetWrap" style="display:none; width: 100%; height: 100%; flex-direction: column; background: #ffffff;">
                    <div id="rdLiveDocSheetTabs" class="d-flex align-items-center px-3 py-2 flex-shrink-0" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; gap: 6px; overflow-x: auto;">
                    </div>
                    <div id="rdLiveDocSheetContent" class="flex-grow-1 p-3" style="overflow: auto; background: #ffffff;">
                    </div>
                </div>

                {{-- 6. Plain Text / Code Canvas --}}
                <div id="rdLiveDocTextWrap" style="display:none; width: 100%; height: 100%; overflow: auto; background: #0f172a; padding: 25px;">
                    <pre id="rdLiveDocTextContent" style="font-family: 'Consolas', 'Courier New', monospace; font-size: 13px; color: #f8fafc; background: transparent; margin: 0; white-space: pre-wrap; word-break: break-all;"></pre>
                </div>

                {{-- 7. Diagnostic Error Fallback --}}
                <div id="rdLiveDocErrorWrap" style="display:none; width: 100%; height: 100%; align-items: center; justify-content: center; flex-direction: column; padding: 30px; background: #f8fafc;">
                    <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                    <h5 class="text-dark font-weight-bold rajdhani" id="rdLiveDocErrorTitle">Unable to Render Document In-Line</h5>
                    <p class="text-muted small text-center mb-4" id="rdLiveDocErrorMsg" style="max-width: 500px;">
                        The document could not be previewed directly inside the frame. You can open it in a separate tab or download it directly.
                    </p>
                    <div class="d-flex" style="gap: 12px;">
                        <a href="#" id="rdLiveDocErrorOpenBtn" target="_blank" class="btn btn-primary btn-sm font-weight-bold px-3">
                            <i class="fas fa-external-link-alt mr-1"></i> Open in New Tab
                        </a>
                        <a href="#" id="rdLiveDocErrorDownloadBtn" download class="btn btn-secondary btn-sm font-weight-bold px-3">
                            <i class="fas fa-download mr-1"></i> Download File
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* Excel preview table styling inside live viewer */
#rdLiveDocSheetContent table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    font-family: 'Consolas', 'Segoe UI', sans-serif;
}
#rdLiveDocSheetContent table th,
#rdLiveDocSheetContent table td {
    border: 1px solid #cbd5e1;
    padding: 6px 10px;
    white-space: nowrap;
}
#rdLiveDocSheetContent table th {
    background: #f1f5f9;
    font-weight: 700;
    color: #1e293b;
    position: sticky;
    top: 0;
}
.rd-sheet-tab-btn {
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}
.rd-sheet-tab-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}
.rd-sheet-tab-btn.active {
    background: #0284c7;
    color: #ffffff;
    border-color: #0284c7;
}
</style>

{{-- Libraries for Office Rendering --}}
<script src="{{ asset('plugins/mammoth/mammoth.browser.min.js') }}"></script>
<script src="{{ asset('plugins/sheetjs/xlsx.full.min.js') }}"></script>

<script>
/**
 * Global function to open any attachment live without forced downloads.
 * 
 * @param {string} rawUrl Document URL or relative storage path
 * @param {string} title Document display name / type
 * @param {string|null} extOpt Optional explicit extension
 */
window.openLiveDocument = async function(rawUrl, title, extOpt) {
    if (!rawUrl) return;

    // Normalize URL
    let url = rawUrl;
    if (!url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('/')) {
        url = '/' + url;
    }

    // Determine extension
    const cleanUrl = url.split('?')[0].split('#')[0];
    const ext = (extOpt || cleanUrl.split('.').pop() || '').toLowerCase();
    const docTitle = title || cleanUrl.split('/').pop() || 'Document';

    // Elements
    const modal = $('#rdLiveDocViewerModal');
    const titleEl = document.getElementById('rdLiveDocViewerTitle');
    const extBadge = document.getElementById('rdLiveDocExtBadge');
    const typeIcon = document.getElementById('rdLiveDocTypeIcon');
    const openNewTab = document.getElementById('rdLiveDocOpenNewTab');
    const downloadBtn = document.getElementById('rdLiveDocDownloadBtn');

    const loading = document.getElementById('rdLiveDocLoading');
    const iframe = document.getElementById('rdLiveDocIframe');
    const imgWrap = document.getElementById('rdLiveDocImgWrap');
    const img = document.getElementById('rdLiveDocImg');
    const wordWrap = document.getElementById('rdLiveDocWordWrap');
    const wordContent = document.getElementById('rdLiveDocWordContent');
    const sheetWrap = document.getElementById('rdLiveDocSheetWrap');
    const sheetTabs = document.getElementById('rdLiveDocSheetTabs');
    const sheetContent = document.getElementById('rdLiveDocSheetContent');
    const textWrap = document.getElementById('rdLiveDocTextWrap');
    const textContent = document.getElementById('rdLiveDocTextContent');
    const errorWrap = document.getElementById('rdLiveDocErrorWrap');

    // Reset all viewer panels
    if (iframe) { iframe.style.display = 'none'; iframe.src = ''; }
    if (imgWrap) { imgWrap.style.display = 'none'; }
    if (img) { img.src = ''; }
    if (wordWrap) { wordWrap.style.display = 'none'; }
    if (wordContent) { wordContent.innerHTML = ''; }
    if (sheetWrap) { sheetWrap.style.display = 'none'; }
    if (sheetTabs) { sheetTabs.innerHTML = ''; }
    if (sheetContent) { sheetContent.innerHTML = ''; }
    if (textWrap) { textWrap.style.display = 'none'; }
    if (textContent) { textContent.textContent = ''; }
    if (errorWrap) { errorWrap.style.display = 'none'; }
    if (loading) { loading.style.display = 'flex'; }

    // Update Header
    if (titleEl) titleEl.textContent = docTitle.toUpperCase();
    if (extBadge) extBadge.textContent = ext ? ext.toUpperCase() : 'FILE';
    if (openNewTab) openNewTab.href = url;
    if (downloadBtn) {
        downloadBtn.href = url + (url.includes('?') ? '&download=1' : '?download=1');
        downloadBtn.setAttribute('download', docTitle + (ext ? '.' + ext : ''));
    }

    // Set header icon according to extension
    if (typeIcon) {
        let iconHtml = '<i class="fas fa-file-alt"></i>';
        if (ext === 'pdf') iconHtml = '<i class="fas fa-file-pdf text-danger"></i>';
        else if (['doc', 'docx'].includes(ext)) iconHtml = '<i class="fas fa-file-word text-primary"></i>';
        else if (['xls', 'xlsx', 'csv'].includes(ext)) iconHtml = '<i class="fas fa-file-excel text-success"></i>';
        else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(ext)) iconHtml = '<i class="fas fa-file-image text-warning"></i>';
        typeIcon.innerHTML = iconHtml;
    }

    // Show Modal
    modal.modal('show');

    function showError(message) {
        if (loading) loading.style.display = 'none';
        if (iframe) iframe.style.display = 'none';
        if (imgWrap) imgWrap.style.display = 'none';
        if (wordWrap) wordWrap.style.display = 'none';
        if (sheetWrap) sheetWrap.style.display = 'none';
        if (textWrap) textWrap.style.display = 'none';

        if (errorWrap) {
            errorWrap.style.display = 'flex';
            const msgEl = document.getElementById('rdLiveDocErrorMsg');
            if (msgEl && message) msgEl.textContent = message;
            const errOpen = document.getElementById('rdLiveDocErrorOpenBtn');
            if (errOpen) errOpen.href = url;
            const errDl = document.getElementById('rdLiveDocErrorDownloadBtn');
            if (errDl) {
                errDl.href = url + (url.includes('?') ? '&download=1' : '?download=1');
                errDl.setAttribute('download', docTitle + (ext ? '.' + ext : ''));
            }
        }
    }

    try {
        // -------------------------------------------------------------
        // 1. IMAGE FORMATS
        // -------------------------------------------------------------
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'jfif', 'ico', 'avif'].includes(ext)) {
            if (img && imgWrap) {
                img.onload = function() {
                    if (loading) loading.style.display = 'none';
                    imgWrap.style.display = 'flex';
                };
                img.onerror = function() {
                    showError('Image failed to load or file could not be found.');
                };
                img.src = url;
            }
        }
        // -------------------------------------------------------------
        // 2. WORD DOCUMENTS (DOCX / DOC) via Mammoth.js
        // -------------------------------------------------------------
        else if (['docx', 'doc'].includes(ext)) {
            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}: Failed to fetch file`);
                const arrayBuffer = await res.arrayBuffer();

                if (typeof mammoth !== 'undefined') {
                    const result = await mammoth.convertToHtml({ arrayBuffer: arrayBuffer });
                    if (wordContent) {
                        wordContent.innerHTML = result.value || '<p class="text-muted font-italic">Document is empty.</p>';
                    }
                    if (wordWrap) wordWrap.style.display = 'flex';
                    if (loading) loading.style.display = 'none';
                } else {
                    // Fallback to iframe
                    if (iframe) {
                        iframe.style.display = 'block';
                        iframe.src = url;
                    }
                    if (loading) loading.style.display = 'none';
                }
            } catch (err) {
                console.warn('Live Word render error, falling back:', err);
                showError('Could not parse Word document. Please open in a new tab.');
            }
        }
        // -------------------------------------------------------------
        // 3. EXCEL SPREADSHEETS (XLSX / XLS / CSV) via SheetJS
        // -------------------------------------------------------------
        else if (['xlsx', 'xls', 'csv'].includes(ext)) {
            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}: Failed to fetch file`);
                const arrayBuffer = await res.arrayBuffer();

                if (typeof XLSX !== 'undefined') {
                    const workbook = XLSX.read(arrayBuffer, { type: 'array' });
                    const sheetNames = workbook.SheetNames || [];

                    if (sheetNames.length === 0) {
                        if (sheetContent) sheetContent.innerHTML = '<div class="text-muted p-4 text-center">Workbook contains no sheets.</div>';
                    } else {
                        // Render sheet tab buttons
                        if (sheetTabs) {
                            sheetTabs.innerHTML = sheetNames.map((name, i) => `
                                <button type="button" class="rd-sheet-tab-btn ${i === 0 ? 'active' : ''}" data-sheet-index="${i}">
                                    <i class="fas fa-table mr-1 text-success"></i> ${name}
                                </button>
                            `).join('');
                        }

                        const renderSheet = function(index) {
                            const sName = sheetNames[index];
                            const worksheet = workbook.Sheets[sName];
                            const htmlTable = XLSX.utils.sheet_to_html(worksheet, { id: 'rdLiveExcelTable', editable: false });
                            if (sheetContent) {
                                sheetContent.innerHTML = htmlTable;
                                const table = sheetContent.querySelector('table');
                                if (table) {
                                    table.className = 'table table-bordered table-hover table-sm m-0';
                                }
                            }
                        };

                        renderSheet(0);

                        $(sheetTabs).off('click', '.rd-sheet-tab-btn').on('click', '.rd-sheet-tab-btn', function() {
                            $(sheetTabs).find('.rd-sheet-tab-btn').removeClass('active');
                            $(this).addClass('active');
                            const sIdx = parseInt($(this).data('sheet-index'));
                            renderSheet(sIdx);
                        });
                    }

                    if (sheetWrap) sheetWrap.style.display = 'flex';
                    if (loading) loading.style.display = 'none';
                } else {
                    if (iframe) {
                        iframe.style.display = 'block';
                        iframe.src = url;
                    }
                    if (loading) loading.style.display = 'none';
                }
            } catch (err) {
                console.warn('Live Excel render error:', err);
                showError('Could not parse spreadsheet. Please open in a new tab.');
            }
        }
        // -------------------------------------------------------------
        // 4. PLAIN TEXT / CODE
        // -------------------------------------------------------------
        else if (['txt', 'log', 'json', 'xml'].includes(ext)) {
            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}: Failed to fetch file`);
                const text = await res.text();
                if (textContent) {
                    textContent.textContent = text;
                }
                if (textWrap) textWrap.style.display = 'block';
                if (loading) loading.style.display = 'none';
            } catch (err) {
                showError('Could not load text content.');
            }
        }
        // -------------------------------------------------------------
        // 5. PDF & BROWSER NATIVE DOCUMENTS
        // -------------------------------------------------------------
        else {
            if (iframe) {
                iframe.onload = function() {
                    if (loading) loading.style.display = 'none';
                };
                iframe.src = url;
                iframe.style.display = 'block';
                // Safety timer for iframe loading indicator
                setTimeout(() => {
                    if (loading && loading.style.display !== 'none') {
                        loading.style.display = 'none';
                    }
                }, 800);
            }
        }
    } catch (e) {
        console.error('Document preview error:', e);
        showError('An unexpected error occurred while loading the preview.');
    }
};

// Global Delegated Click Listener to intercept all attachment links
document.addEventListener('DOMContentLoaded', function() {
    $(document).on('click', '.rd-live-file-view, [data-live-document]', function(e) {
        e.preventDefault();
        const url = $(this).attr('href') || $(this).data('url');
        const title = $(this).data('title') || $(this).attr('title') || $(this).text().trim() || 'Document';
        const ext = $(this).data('ext') || '';
        if (url && url !== '#' && !url.startsWith('javascript:')) {
            window.openLiveDocument(url, title, ext);
        }
    });
});
</script>
