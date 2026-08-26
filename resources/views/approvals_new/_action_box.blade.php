@php
    $u = Auth::user();
    $userArea = strtolower(trim((string) ($u?->acc_untarea ?? '')));
    if (in_array($userArea, ['proc', 'prc'], true)) $userArea = 'proc';
    $service = app(\App\Services\PurchaseApprovalService::class);
    $area = $area ?? $userArea;
    $canApprove = $service->canApprove($area, (float)($purchase->pcs_price ?? 0), $purchase);
    $nextAuthName = $service->getNextAuthorityName($purchase, $area);
    $returnTargets = $service->getReturnTargets($purchase);
    
    // Determine if this user's stage matches the case's current substatus stage
    $currentStage = $purchase->currentSubstatus?->pss_stage;
    $hasFloated = $purchase->decisions->contains('pdec_action', 'float_to_proc');
    $hasDProcSaved = $purchase->decisions->contains('pdec_action', 'dproc_save');
    $isDraft = in_array(strtolower(trim($purchase->pcs_status)), ['draft']);
    $isReturned = in_array(strtolower(trim($purchase->pcs_status)), ['returned']);
    $isInitiator = in_array($userArea, ['prj', 'rdwprj', 'division', 'initiation']);
    $isDProcDraft = ($userArea === 'proc' && in_array(trim($purchase->pcs_status), ['Draft', 'Returned']));

    $expectedStages = match($userArea) {
        'prj', 'rdwprj', 'division' => ['Division'],
        'proc' => ['DProc', 'Division'],
        'fin'  => ['DFinance'],
        'rdw'  => ['MD'],
        'hqs'  => ['DDG'],
        'nrdi' => ['DG'],
        default => ['None']
    };

    $isCurrentStage = in_array($currentStage, $expectedStages) 
        || ($userArea === 'proc' && $isDraft && $hasFloated)
        || ($isInitiator && ($isDraft || $isReturned));
    $currentStatusDisplay = $purchase->current_stage_display ?? $service->getStatusDisplayName($purchase->pcs_status);

    // Calculate the next numbering for the list:
    // Main minute sheet ends at paragraph 4.
    $liCount = 0;
    foreach($purchase->decisions as $dec) {
        if ($dec->pdec_action === 'save_draft') continue;
        if (strpos($dec->pdec_remarks, '<li') !== false) {
            $liCount += substr_count($dec->pdec_remarks, '<li');
        } else if (!empty(trim(strip_tags($dec->pdec_remarks)))) {
            $liCount += 1;
        }
    }
    $nextRemarkNumber = 4 + $liCount + 1;

    // Fetch existing draft for this user if any (rendered in textarea as 1. 2. for user ease)
    $myDraftDecision = $purchase->decisions->where('pdec_acc_id', $u->acc_id)->where('pdec_action', 'save_draft')->first();
    $existingDraftRaw = '';
    if ($myDraftDecision && !empty($myDraftDecision->pdec_remarks)) {
        $clean = preg_replace('/<\/?(ol|ul)>/', '', $myDraftDecision->pdec_remarks);
        preg_match_all('/<li>(.*?)<\/li>/is', $clean, $matches);
        if (!empty($matches[1])) {
            $lines = [];
            $curNum = 1;
            foreach ($matches[1] as $lineContent) {
                $lines[] = $curNum . ". " . trim(strip_tags($lineContent));
                $curNum++;
            }
            $existingDraftRaw = implode("\n", $lines);
        } else {
            $existingDraftRaw = trim(strip_tags($myDraftDecision->pdec_remarks));
            if (!preg_match('/^\d+\./', $existingDraftRaw)) {
                $existingDraftRaw = "1. " . $existingDraftRaw;
            }
        }
    }
@endphp

@if($isCurrentStage)
<div class="mb-4 pb-3 border-bottom" style="border-bottom: 1px dashed #cbd5e1 !important;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="font-weight-bold rajdhani text-dark" style="font-size: 14px;">
            <i class="fas fa-user-circle text-primary mr-1"></i> {{ $u->acc_name }} 
            <span class="text-muted small ml-1" style="font-weight: 600;">({{ strtoupper($userArea) }})</span>
            <span class="ml-2 pl-2 border-left border-secondary font-weight-bold" style="font-size: 10px; color: var(--rd-accent); letter-spacing: 0.5px;">
                <i class="fas fa-pen-nib mr-1"></i> SCRUTINY & ACTION
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="saveDraftStatus" class="small text-muted font-italic" style="font-size: 11px;">
                @if($myDraftDecision) <span class="text-success"><i class="fas fa-check-circle mr-1"></i>Remarks drafted</span> @endif
            </span>
        </div>
    </div>

    <form id="authorityActionForm" action="{{ $isInitiator ? route('purchase.release', $purchase->pcs_id) : route('nrdi.purchase_cases_new.action', $purchase->pcs_id) }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="formActionInput" value="forward">
        <input type="hidden" name="target_status" id="formTargetStatusInput" value="">
        <input type="hidden" name="remarks" id="remarksHiddenInput">

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1.5">
                <span class="text-dark small rajdhani font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">
                    <i class="fas fa-pen-nib mr-1 text-primary"></i> REMARKS & SCRUTINY NOTES
                </span>
                <span class="text-muted font-italic" style="font-size: 10.5px;">
                    <i class="fas fa-arrows-alt-v mr-0.5"></i> Drag corner to resize
                </span>
            </div>
            <textarea id="inlineRemarks" class="form-control" placeholder="Type your remarks or scrutiny observations here..." style="background: #ffffff; color: #0f172a; font-family: 'Arial', sans-serif; font-size: 13px; min-height: 110px; height: 110px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; outline: none; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04); resize: vertical; width: 100%;">{{ $existingDraftRaw }}</textarea>
            
            {{-- Quick Comments --}}
            <div class="mt-2 d-flex flex-wrap" style="gap: 6px;">
                <span class="badge badge-secondary p-1 px-2 cursor-pointer quick-comment-btn" style="font-size: 11px; background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; font-weight: 600;">FNA, please.</span>
                <span class="badge badge-secondary p-1 px-2 cursor-pointer quick-comment-btn" style="font-size: 11px; background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; font-weight: 600;">Recommended and forwarded.</span>
                <span class="badge badge-secondary p-1 px-2 cursor-pointer quick-comment-btn" style="font-size: 11px; background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; font-weight: 600;">For approval please.</span>
                <span class="badge badge-secondary p-1 px-2 cursor-pointer quick-comment-btn" style="font-size: 11px; background: #f1f5f9; border: 1px solid #cbd5e1; color: #334155; font-weight: 600;">Discussed.</span>
                @if($userArea === 'proc')
                <span class="badge badge-info p-1 px-2 cursor-pointer quick-comment-btn" style="font-size: 11px; background: #e0f2fe; border: 1px solid #7dd3fc; color: #0369a1; font-weight: 600;">Quotes verified and recommended.</span>
                @endif
            </div>
        </div>

        <div class="d-flex" style="gap: 10px; width: 100%;">
            @if($canApprove)
                {{-- FINAL APPROVING AUTHORITY (MD <= 4L, DDG <= 10L, DG) --}}
                <button type="button" onclick="handleAction('approve')" class="dg-btn-action dg-btn-success flex-grow-1" style="font-size: 14px; letter-spacing: 0.5px;">
                    <i class="fas fa-check-double mr-1.5"></i> APPROVED / APPROVE CASE
                </button>
                <div class="btn-group flex-grow-1">
                    <button type="button" class="dg-btn-action dg-btn-return w-100 dropdown-toggle" data-toggle="dropdown" id="btnReturn" disabled aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-undo mr-1"></i> RETURN
                    </button>
                    <div class="dropdown-menu dropdown-menu-right bg-white border shadow-lg mt-2" style="min-width: 200px; border-radius: 8px; padding: 6px 0; border-color: var(--rd-text1) !important;">
                        <h6 class="dropdown-header text-warning rajdhani mb-1" style="font-size: 11px; letter-spacing: 0.5px; font-weight: 700;">SELECT RETURN TARGET:</h6>
                        @foreach($returnTargets as $status => $name)
                            <a class="dropdown-item text-dark py-2 d-flex align-items-center" href="javascript:void(0)" onclick="confirmReturn('{{ $status }}', '{{ $name }}')">
                                <i class="fas fa-chevron-left mr-2 text-muted" style="font-size: 10px;"></i>
                                <span class="rajdhani font-weight-bold" style="font-size: 13px;">{{ $name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- INTERMEDIATE OR INITIATOR --}}
                @if($isInitiator)
                    @php
                        $caseType = strtolower(trim((string) ($purchase->pcs_type ?? 'ps')));
                        $isProcCase = in_array($caseType, ['ps', 'mat', 'material', 'eqp', 'equipment', 'cons', 'consultancy', 'serv', 'services'], true);
                    @endphp

                    @if(!$isProcCase)
                        {{-- Non-PS Cases: Direct release to HQ without Procurement --}}
                        <div class="d-flex flex-column w-100" style="gap: 8px;">
                            <button type="button" onclick="handleAction('forward')" class="dg-btn-action dg-btn-success w-100 font-weight-bold" style="font-size: 13px; letter-spacing: 0.5px;">
                                <i class="fas fa-paper-plane mr-1"></i> RELEASE CASE TO HQ
                            </button>
                        </div>
                    @else
                        @if($isDraft && !$hasFloated)
                            {{-- State 1: Division Draft Initial State -> Float to Procurement --}}
                            <button type="button" onclick="handleAction('float_to_proc')" class="dg-btn-action dg-btn-info w-100 font-weight-bold" style="font-size: 13px; letter-spacing: 0.5px;">
                                <i class="fas fa-paper-plane mr-1"></i> FLOAT TO PROCUREMENT DEPT
                            </button>
                        @elseif($isDraft && $hasFloated && !$hasDProcSaved)
                            {{-- State 2: Floated to Procurement, waiting for DProc response --}}
                            <div class="p-3 rounded text-center w-100" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;">
                                <div class="font-weight-bold text-primary rajdhani mb-1" style="font-size: 13px; letter-spacing: 0.5px;">
                                    <i class="fas fa-hourglass-half mr-1"></i> FLOATED TO PROCUREMENT DEPT
                                </div>
                                <div class="text-muted" style="font-size: 12px;">
                                    Case is currently with Director Procurement for quotation collection & scrutiny. Once saved by Procurement, the <strong>Release</strong> button will be enabled here.
                                </div>
                            </div>
                        @else
                            {{-- State 3: DProc has saved (or returned case) -> Division can now RELEASE or RESHARE FOR CORRECTION --}}
                            <div class="d-flex flex-column w-100" style="gap: 8px;">
                                <button type="button" onclick="handleAction('forward')" class="dg-btn-action dg-btn-success w-100 font-weight-bold" style="font-size: 13px; letter-spacing: 0.5px;">
                                    <i class="fas fa-paper-plane mr-1"></i> RELEASE CASE TO HQ
                                </button>
                                @if($hasFloated && $hasDProcSaved)
                                <button type="button" onclick="handleAction('reshare_to_proc')" class="btn btn-xs rajdhani font-weight-bold py-2 w-100" style="font-size: 12px; letter-spacing: 0.5px; border-radius: 6px; background: #fef3c7; border: 1.5px solid #f59e0b; color: #b45309;">
                                    <i class="fas fa-undo-alt mr-1"></i> RESHARE TO PROCUREMENT DEPT FOR CORRECTION
                                </button>
                                @endif
                            </div>
                        @endif
                    @endif
                @else
                    @if($isDProcDraft)
                        @php
                            $caseType = strtolower(trim((string) ($purchase->pcs_type ?? 'ps')));
                            $isProcCase = in_array($caseType, ['ps', 'mat', 'material', 'eqp', 'equipment', 'cons', 'consultancy', 'serv', 'services'], true);
                        @endphp
                        @if(!$isProcCase)
                            <div class="p-3 rounded text-center w-100" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px;">
                                <div class="font-weight-bold text-muted rajdhani mb-1" style="font-size: 13px; letter-spacing: 0.5px;">
                                    <i class="fas fa-info-circle mr-1"></i> NON-PROCUREMENT CASE
                                </div>
                                <div class="text-muted small" style="font-size: 12px;">
                                    This is a non-procurement ({{ strtoupper($caseType) }}) case managed directly by Division and HQ Finance.
                                </div>
                            </div>
                        @elseif(!$hasFloated && $isDraft)
                            <div class="p-3 rounded text-center w-100" style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;">
                                <div class="font-weight-bold text-warning rajdhani mb-1" style="font-size: 13px; letter-spacing: 0.5px;">
                                    <i class="fas fa-clock mr-1"></i> AWAITING DIVISION
                                </div>
                                <div class="text-muted" style="font-size: 12px;">
                                    This case is still in draft at Division and has not been floated to Procurement yet.
                                </div>
                            </div>
                        @elseif($hasDProcSaved && $isDraft)
                            <div class="p-3 rounded text-center w-100" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                                <div class="font-weight-bold text-success rajdhani mb-1" style="font-size: 13px; letter-spacing: 0.5px;">
                                    <i class="fas fa-check-circle mr-1"></i> ACTION TAKEN & SAVED
                                </div>
                                <div class="text-muted small" style="font-size: 12px;">
                                    You have finalized quotations and scrutiny remarks. Case is awaiting release by Division (locked unless reshared).
                                </div>
                            </div>
                        @else
                            <div class="d-flex w-100" style="gap: 8px;">
                                <button type="button" class="btn btn-outline-warning btn-sm font-weight-bold px-3" data-toggle="modal" data-target="#pcAddQuoteModal" style="border-radius: 6px;">
                                    <i class="fas fa-plus-circle mr-1"></i> MANAGE QUOTES
                                </button>
                                <button type="button" onclick="handleAction('dproc_save')" id="btnForward" class="dg-btn-action dg-btn-success flex-grow-1 font-weight-bold">
                                    <i class="fas fa-check-circle mr-1"></i> FINALIZE & TAKE ACTION
                                </button>
                            </div>
                        @endif
                    @else
                        <button type="button" onclick="handleAction('forward')" id="btnForward" class="dg-btn-action dg-btn-success flex-grow-1">
                            <div class="d-flex flex-column align-items-center">
                                <span><i class="fas fa-thumbs-up mr-1"></i> RECOMMEND</span>
                                @if($nextAuthName)<span style="font-size: 9px; opacity: 0.9; margin-top: 2px;">TO: {{ strtoupper($nextAuthName) }}</span>@endif
                            </div>
                        </button>
                        <div class="btn-group flex-grow-1">
                            <button type="button" class="dg-btn-action dg-btn-return w-100 dropdown-toggle" data-toggle="dropdown" id="btnReturn" disabled aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-undo mr-1"></i> RETURN
                            </button>
                            <div class="dropdown-menu dropdown-menu-right bg-white border shadow-lg mt-2" style="min-width: 200px; border-radius: 8px; padding: 6px 0; border-color: var(--rd-text1) !important;">
                                <h6 class="dropdown-header text-warning rajdhani mb-1" style="font-size: 11px; letter-spacing: 0.5px; font-weight: 700;">SELECT RETURN TARGET:</h6>
                                @foreach($returnTargets as $status => $name)
                                    <a class="dropdown-item text-dark py-2 d-flex align-items-center" href="javascript:void(0)" onclick="confirmReturn('{{ $status }}', '{{ $name }}')">
                                        <i class="fas fa-chevron-left mr-2 text-muted" style="font-size: 10px;"></i>
                                        <span class="rajdhani font-weight-bold" style="font-size: 13px;">{{ $name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </form>
</div>

<style>
.dg-btn-action {
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.6px;
    padding: 10px 14px;
    border-radius: 6px;
    border: none;
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.12);
    text-transform: uppercase;
}
.dg-btn-success { background: #16a34a !important; color: #ffffff !important; }
.dg-btn-success:hover:not(:disabled) { background: #15803d !important; }
.dg-btn-success:disabled { opacity: 0.45; cursor: not-allowed; }

.dg-btn-info { background: var(--rd-primary-700) !important; color: #ffffff !important; }
.dg-btn-info:hover:not(:disabled) { background: var(--rd-primary-600) !important; }

.dg-btn-danger { background: #dc2626 !important; color: #ffffff !important; }
.dg-btn-danger:hover:not(:disabled) { background: #b91c1c !important; }

.dg-btn-return { background: #fee2e2 !important; color: #dc2626 !important; border: 1.5px solid #fca5a5 !important; }
.dg-btn-return:hover:not(:disabled) { background: #dc2626 !important; color: #ffffff !important; }
.dg-btn-return:disabled { opacity: 0.4; cursor: not-allowed; }
</style>

<script>
    const nextNum = {{ $nextRemarkNumber }};
    const inlineRemarks = document.getElementById('inlineRemarks');
    const btnReturn = document.getElementById('btnReturn');
    const btnForward = document.getElementById('btnForward');
    const btnSaveRemarks = document.getElementById('btnSaveRemarks');
    const saveDraftStatus = document.getElementById('saveDraftStatus');

    let lastSavedRemarks = inlineRemarks ? inlineRemarks.value.trim() : '';

    function getNextLocalNumber() {
        if (!inlineRemarks) return 2;
        const matches = inlineRemarks.value.match(/^\d+(?=\.)/gm);
        if (matches && matches.length > 0) {
            return Math.max(...matches.map(Number)) + 1;
        }
        return 2;
    }

    // Auto-initialize with numbering 1. on first focus if empty
    if (inlineRemarks) {
        inlineRemarks.addEventListener('focus', function() {
            if (this.value.trim() === '') {
                this.value = "1. ";
                updateGlowState();
            }
        });

        inlineRemarks.addEventListener('keydown', function(e) {
            const selectionStart = this.selectionStart;
            const text = this.value;
            const lastNewline = text.lastIndexOf('\n', selectionStart - 1);
            const lineStart = lastNewline === -1 ? 0 : lastNewline + 1;
            const currentLine = text.substring(lineStart, selectionStart);
            const match = currentLine.match(/^\d+\. /);

            if (match && selectionStart < lineStart + match[0].length) {
                if (e.key === 'Backspace' || e.key === 'Delete' || (e.key.length === 1 && e.key !== 'Enter')) {
                    e.preventDefault();
                    return;
                }
            }
            
            if (e.key === 'Enter') {
                e.preventDefault();
                if (currentLine.trim().length > (match ? match[0].trim().length : 0)) {
                    const nextNumLocal = getNextLocalNumber();
                    const newNumber = "\n" + nextNumLocal + ". ";
                    const before = text.substring(0, selectionStart);
                    const after = text.substring(selectionStart);
                    this.value = before + newNumber + after;
                    this.selectionStart = this.selectionEnd = before.length + newNumber.length;
                    updateGlowState();
                }
            }
            
            if (e.key === 'Backspace' && match && selectionStart === lineStart + match[0].length) {
                e.preventDefault();
            }
        });
    }

    function updateGlowState() {
        if (!inlineRemarks) return;
        const currentVal = inlineRemarks.value.trim();
        const prefix = "1. ";
        const hasContent = currentVal.length > 0 && currentVal !== prefix.trim() && currentVal !== "1.";
        const isModified = currentVal !== lastSavedRemarks;

        if (btnSaveRemarks) {
            if (hasContent && isModified) {
                btnSaveRemarks.classList.add('btn-glow-pulse');
                btnSaveRemarks.disabled = false;
                if (saveDraftStatus) saveDraftStatus.innerHTML = '<span class="text-warning"><i class="fas fa-circle fa-xs mr-1"></i>Unsaved remarks</span>';
            } else {
                btnSaveRemarks.classList.remove('btn-glow-pulse');
                if (!hasContent) {
                    btnSaveRemarks.disabled = true;
                    if (saveDraftStatus) saveDraftStatus.innerHTML = '';
                } else if (!isModified && lastSavedRemarks.length > 0) {
                    if (saveDraftStatus) saveDraftStatus.innerHTML = '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>Remarks saved</span>';
                }
            }
        }

        if (btnReturn) btnReturn.disabled = !hasContent;
        // btnForward is always enabled for one-click action
        if (btnForward) btnForward.disabled = false;
    }

    inlineRemarks.addEventListener('input', function() {
        const prefix = "1. ";
        if (!this.value.startsWith(prefix) && this.value.trim() !== '') {
            const currentVal = this.value;
            if (currentVal.length < prefix.length) {
                this.value = prefix;
            } else {
                this.value = prefix + currentVal.replace(/^\d+\.?\s*/, '');
            }
            this.selectionStart = this.selectionEnd = prefix.length;
        }
        updateGlowState();
    });

    document.querySelectorAll('.quick-comment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const comment = this.textContent.trim();
            const prefix = "1. ";
            let currentVal = inlineRemarks.value;
            
            if (currentVal.trim() === '' || !currentVal.startsWith(prefix)) {
                inlineRemarks.value = prefix + comment;
            } else {
                const lines = currentVal.split('\n');
                const lastLine = lines[lines.length - 1];
                
                if (lastLine.match(/^\d+\.\s*$/)) {
                    lines[lines.length - 1] = lastLine + comment;
                    inlineRemarks.value = lines.join('\n');
                } else {
                    inlineRemarks.value = currentVal + (currentVal.endsWith(' ') ? '' : ' ') + comment;
                }
            }
            
            inlineRemarks.dispatchEvent(new Event('input'));
            inlineRemarks.focus();
        });
    });

    function buildRemarksHtml() {
        let remarks = inlineRemarks.value.trim();
        let lines = remarks.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        let cleanedLines = lines.map(line => line.replace(/^\d+\.\s*/, '').trim()).filter(l => l.length > 0);
        if (cleanedLines.length === 0) return '';
        let liItems = cleanedLines.map(line => `<li>${line}</li>`).join('');
        return `<ol start="${nextNum}">${liItems}</ol>`;
    }

    function saveRemarksDraft() {
        const finalHtml = buildRemarksHtml();
        if (!finalHtml) {
            Swal.fire({ title: 'Empty Remarks', text: 'Please type some remarks first before saving.', icon: 'warning', background: '#001226', color: '#fff' });
            return;
        }

        const originalBtnHtml = btnSaveRemarks.innerHTML;
        btnSaveRemarks.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> SAVING...';
        btnSaveRemarks.disabled = true;

        const form = document.getElementById('authorityActionForm');
        const actionUrl = form.getAttribute('action');
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('action', 'save_draft');
        formData.append('remarks', finalHtml);

        fetch(actionUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            btnSaveRemarks.innerHTML = originalBtnHtml;
            if (data.success) {
                lastSavedRemarks = inlineRemarks.value.trim();
                btnSaveRemarks.classList.remove('btn-glow-pulse');
                saveDraftStatus.innerHTML = '<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Saved to Minute Sheet</span>';
                
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    background: '#001226',
                    color: '#fff'
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Remarks saved! Check Minute View.'
                });
            } else {
                btnSaveRemarks.disabled = false;
                Swal.fire({ title: 'Error', text: data.message || 'Could not save remarks.', icon: 'error', background: '#001226', color: '#fff' });
            }
        })
        .catch(err => {
            btnSaveRemarks.innerHTML = originalBtnHtml;
            btnSaveRemarks.disabled = false;
            console.error(err);
            Swal.fire({ title: 'Error', text: 'Network or server error while saving.', icon: 'error', background: '#001226', color: '#fff' });
        });
    }

    function confirmReturn(targetStatus, targetName) {
        let remarks = inlineRemarks.value.trim();
        let lines = remarks.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        let cleanedLines = lines.map(line => line.replace(/^\d+\.\s*/, '').trim()).filter(l => l.length > 0);

        if (cleanedLines.length === 0) {
            Swal.fire({ title: 'Remarks Required!', text: 'Remarks are compulsory for returning a case.', icon: 'warning', background: '#001226', color: '#fff' });
            return;
        }

        Swal.fire({
            title: 'Confirm Return?',
            text: `Return this case to ${targetName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Return',
            background: '#001226',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                handleAction('return', targetStatus);
            }
        });
    }

    window.handleAction = function(action, targetStatus = null) {
        if (typeof logToDebug === 'function') logToDebug(`Action Triggered: ${action}`, 'INFO');
        
        let remarks = inlineRemarks ? inlineRemarks.value.trim() : '';
        let lines = remarks.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        let cleanedLines = lines.map(line => line.replace(/^\d+\.\s*/, '').trim()).filter(l => l.length > 0);

        if (action === 'return' && cleanedLines.length === 0) {
            Swal.fire({ title: 'Remarks Required!', text: 'Remarks are compulsory for returning a case.', icon: 'warning', background: '#001226', color: '#fff' });
            return;
        }

        if (cleanedLines.length === 0) {
            if (action === 'approve') cleanedLines = ['Case approved'];
            else if (action === 'float_to_proc') cleanedLines = ['Case floated to Procurement Department for quotation collection.'];
            else if (action === 'dproc_save') cleanedLines = ['Quotation details and scrutiny remarks saved.'];
            else if (action === 'reshare_to_proc') cleanedLines = ['Case reshared to Procurement Department for quotation correction.'];
            else if (action === 'forward') cleanedLines = ['Case released and forwarded for approval.'];
            else cleanedLines = ['Forwarded for review.'];
        }

        let liItems = cleanedLines.map(line => `<li>${line}</li>`).join('');
        let finalHtml = `<ol start="${nextNum}">${liItems}</ol>`;

        const remarksInput = document.getElementById('remarksHiddenInput');
        const actionInput = document.getElementById('formActionInput');
        const targetStatusInput = document.getElementById('formTargetStatusInput');
        const actionForm = document.getElementById('authorityActionForm');

        if (!actionForm) {
            if (typeof logToDebug === 'function') logToDebug('ERROR: actionForm (authorityActionForm) not found in DOM!');
            return;
        }

        if (remarksInput) remarksInput.value = finalHtml;
        if (actionInput) actionInput.value = action;
        
        if (action === 'return' && targetStatusInput) {
            targetStatusInput.value = targetStatus;
        }

        if (action === 'return') {
            actionForm.submit();
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Confirm Action?',
                    text: `You are about to submit your decision.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    background: '#ffffff',
                    color: '#0f172a'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (typeof logToDebug === 'function') logToDebug('Submitting form via SweetAlert confirmation...', 'INFO');
                        actionForm.submit();
                    }
                });
            } else {
                if (confirm('Confirm Action? You are about to submit your decision.')) {
                    actionForm.submit();
                }
            }
        }
    }
</script>
@endif
