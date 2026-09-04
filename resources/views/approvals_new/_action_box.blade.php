@php
    $u = Auth::user();
    $userArea = strtolower(trim((string) ($u?->acc_untarea ?? '')));
    if (in_array($userArea, ['proc', 'prc'], true)) $userArea = 'proc';
    $service = app(\App\Services\PurchaseApprovalService::class);
    $area = $area ?? $userArea;
    $canApprove = $service->canApprove($area, (float)($purchase->pcs_price ?? 0), $purchase);
    $destinations = $service->getAvailableDestinations($userArea);
    
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
@endphp

@if($isCurrentStage)
<div class="mb-4 pb-3 border-bottom" style="border-bottom: 1px dashed #cbd5e1 !important;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="font-weight-bold rajdhani text-dark" style="font-size: 14px;">
            <i class="fas fa-user-circle text-primary mr-1"></i> {{ $u->acc_name }} 
            <span class="text-muted small ml-1" style="font-weight: 600;">({{ strtoupper($userArea) }})</span>
            <span class="ml-2 pl-2 border-left border-secondary font-weight-bold" style="font-size: 10px; color: var(--rd-accent, #5F7858); letter-spacing: 0.5px;">
                <i class="fas fa-pen-nib mr-1"></i> SCRUTINY & ACTION
            </span>
        </div>
    </div>

    <form id="authorityActionForm" action="{{ $isInitiator ? route('purchase.release', $purchase->pcs_id) : route('nrdi.purchase_cases_new.action', $purchase->pcs_id) }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="formActionInput" value="forward">
        <input type="hidden" name="target_status" id="formTargetStatusInput" value="">
        <input type="hidden" name="remarks" id="remarksHiddenInput">

        {{-- Remarks Textarea --}}
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1.5">
                <span class="text-dark small rajdhani font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">
                    <i class="fas fa-pen-nib mr-1 text-primary"></i> REMARKS & SCRUTINY NOTES
                </span>
                <span class="text-muted font-italic" style="font-size: 10.5px;">
                    <i class="fas fa-arrows-alt-v mr-0.5"></i> Drag corner to resize
                </span>
            </div>
            <textarea id="inlineRemarks" class="form-control" placeholder="Type your remarks or scrutiny observations here..." style="background: #ffffff; color: #0f172a; font-family: 'Arial', sans-serif; font-size: 13px; min-height: 110px; height: 110px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; outline: none; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04); resize: vertical; width: 100%;"></textarea>
            
            {{-- User Quick Remarks (Custom shortcuts) --}}
            <div class="mt-2">
                @include('partials._user_quick_remarks', ['targetTextarea' => '#inlineRemarks'])
            </div>
        </div>

        {{-- Send / Forward To Destination Dropdown (Opens Downwards with Real-time Search) --}}
        <div class="form-group mb-3 position-relative" id="pcDestDropdownContainer">
            <label class="font-weight-bold text-muted small mb-1 d-flex justify-content-between" style="font-size: 10px; text-transform: uppercase;">
                <span><i class="fas fa-paper-plane text-primary mr-1"></i> Send / Forward To Destination: <span class="text-danger">*</span></span>
                <span class="text-muted font-italic" style="font-size: 9px; text-transform: none;">Search department, division, or director</span>
            </label>
            
            <input type="hidden" name="target_destination" id="pcTargetDestinationInput" value="">

            {{-- Display Toggle Box --}}
            <div id="pcDestDropdownToggle" class="form-control form-control-sm d-flex align-items-center justify-content-between" style="font-size: 12px; font-weight: 600; border-radius: 6px; border-color: #cbd5e1; height: 38px; cursor: pointer; background: #ffffff; user-select: none;">
                <span id="pcDestSelectedLabel" class="text-muted text-truncate font-weight-normal">
                    <i class="fas fa-search mr-1.5 text-muted"></i> -- Select Destination Department / Authority --
                </span>
                <i class="fas fa-chevron-down text-muted ml-2" id="pcDestDropdownChevron" style="font-size: 11px; transition: transform 0.2s;"></i>
            </div>

            {{-- Downward Dropdown Menu --}}
            <div id="pcDestDropdownMenu" class="shadow-lg border bg-white" style="display: none; position: absolute; top: 100% !important; bottom: auto !important; left: 0; right: 0; z-index: 1050; margin-top: 3px; border-radius: 8px; border-color: #cbd5e1 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;">
                {{-- Sticky Search Input --}}
                <div class="p-2 border-bottom bg-light">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white border-right-0" style="border-color: #cbd5e1;"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                        </div>
                        <input type="text" id="pcDestSearchInput" class="form-control border-left-0" placeholder="Type department, division, or director name..." style="font-size: 12px; border-color: #cbd5e1;" autocomplete="off">
                    </div>
                </div>

                {{-- Scrollable Destination Items --}}
                <div id="pcDestItemsList" style="max-height: 230px; overflow-y: auto; padding: 4px 0;">
                    @foreach($destinations as $destCode => $dest)
                        @php
                            $searchKeywords = strtolower($dest['name'] . ' ' . ($dest['director'] ?? '') . ' ' . ($dest['desig'] ?? '') . ' ' . $destCode);
                        @endphp
                        <div class="pc-dest-option-item px-3 py-2" data-code="{{ $destCode }}" data-name="{{ $dest['name'] }}" data-search="{{ $searchKeywords }}" style="cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #f1f5f9;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-dark font-weight-bold" style="font-size: 12.5px;">{{ $dest['name'] }}</span>
                                <span class="badge badge-light border text-muted px-1.5 py-0.5" style="font-size: 9.5px; border-radius: 4px;">{{ $dest['badge'] ?? $destCode }}</span>
                            </div>
                            @if(!empty($dest['director']))
                                <div class="text-muted text-truncate" style="font-size: 11px; margin-top: 1px;">
                                    <i class="fas fa-user-tie text-secondary mr-1" style="font-size: 9.5px;"></i> {{ $dest['director'] }}
                                    @if(!empty($dest['desig']))
                                        <span class="text-muted font-weight-normal">&bull; {{ $dest['desig'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                    <div id="pcDestNoResults" class="p-3 text-center text-muted small" style="display: none;">
                        <i class="fas fa-info-circle mr-1"></i> No matching department, division, or director found.
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons Row: Left = SEND CASE (Prominent), Right = Compact Approve & Compact Cancel (Expanding on hover, ONLY for Approving Authority) --}}
        <div class="d-flex align-items-center" style="gap: 8px; width: 100%;">
            {{-- Left: Prominent Send Case Button --}}
            <button type="button" onclick="handleAction('forward')" id="btnForward" class="btn-action-send flex-grow-1" style="height: 40px; font-size: 13.5px; letter-spacing: 0.6px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="fas fa-paper-plane mr-2"></i>
                <span class="font-weight-bold rajdhani">SEND CASE</span>
            </button>

            @if($canApprove)
                {{-- Right: Compact Approve Button (Green Tick, expands on hover) --}}
                <button type="button" onclick="handleAction('approve')" id="btnApprove" class="btn-action-approve" title="Approve Purchase Case">
                    <i class="fas fa-check"></i>
                    <span class="btn-expand-text rajdhani font-weight-bold">APPROVE</span>
                </button>

                {{-- Right: Compact Cancel / Reject Button (Red Cross, expands on hover) --}}
                <button type="button" onclick="handleAction('cancel')" id="btnCancel" class="btn-action-cancel" title="Cancel / Reject Purchase Case">
                    <i class="fas fa-times"></i>
                    <span class="btn-expand-text rajdhani font-weight-bold">REJECT</span>
                </button>
            @endif
        </div>
    </form>
</div>

<style>
/* Send Button: wide on the left */
.btn-action-send {
    background: #16a34a !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
}
.btn-action-send:hover {
    background: #15803d !important;
    box-shadow: 0 4px 10px rgba(21, 128, 61, 0.3);
    transform: translateY(-1px);
}

/* Compact Approve Button: green icon on right, expands on hover */
.btn-action-approve {
    flex: 0 0 42px;
    width: 42px;
    height: 40px;
    background: #16a34a !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    overflow: hidden;
    cursor: pointer;
    padding: 0 12px;
}
.btn-action-approve .btn-expand-text {
    max-width: 0;
    opacity: 0;
    margin-left: 0;
    transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease, margin-left 0.3s ease;
    overflow: hidden;
    display: inline-block;
}
.btn-action-approve:hover {
    flex: 0 0 115px;
    width: 115px;
    background: #15803d !important;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);
    transform: translateY(-1px);
}
.btn-action-approve:hover .btn-expand-text {
    max-width: 70px;
    opacity: 1;
    margin-left: 6px;
}

/* Compact Cancel Button: red icon on right, expands on hover */
.btn-action-cancel {
    flex: 0 0 42px;
    width: 42px;
    height: 40px;
    background: #dc2626 !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    overflow: hidden;
    cursor: pointer;
    padding: 0 12px;
}
.btn-action-cancel .btn-expand-text {
    max-width: 0;
    opacity: 0;
    margin-left: 0;
    transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease, margin-left 0.3s ease;
    overflow: hidden;
    display: inline-block;
}
.btn-action-cancel:hover {
    flex: 0 0 105px;
    width: 105px;
    background: #b91c1c !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
    transform: translateY(-1px);
}
.btn-action-cancel:hover .btn-expand-text {
    max-width: 60px;
    opacity: 1;
    margin-left: 6px;
}

.pc-dest-option-item:hover {
    background: #f1f5f9;
}
.pc-dest-option-item.selected {
    background: #e2e8f0;
}
</style>

<script>
    const nextNum = {{ $nextRemarkNumber }};
    const inlineRemarks = document.getElementById('inlineRemarks');
    const btnForward = document.getElementById('btnForward');

    // Downward Searchable Dropdown Logic
    const pcDestContainer = document.getElementById('pcDestDropdownContainer');
    const pcDestToggle = document.getElementById('pcDestDropdownToggle');
    const pcDestMenu = document.getElementById('pcDestDropdownMenu');
    const pcDestSearch = document.getElementById('pcDestSearchInput');
    const pcDestHiddenInput = document.getElementById('pcTargetDestinationInput');
    const pcDestLabel = document.getElementById('pcDestSelectedLabel');
    const pcDestChevron = document.getElementById('pcDestDropdownChevron');
    const pcDestItems = document.querySelectorAll('.pc-dest-option-item');
    const pcDestNoResults = document.getElementById('pcDestNoResults');

    function openPcDestDropdown() {
        if (!pcDestMenu) return;
        pcDestMenu.style.display = 'block';
        if (pcDestChevron) pcDestChevron.style.transform = 'rotate(180deg)';
        if (pcDestSearch) {
            pcDestSearch.value = '';
            filterPcDestItems('');
            setTimeout(() => pcDestSearch.focus(), 50);
        }
    }

    function closePcDestDropdown() {
        if (!pcDestMenu) return;
        pcDestMenu.style.display = 'none';
        if (pcDestChevron) pcDestChevron.style.transform = 'rotate(0deg)';
    }

    function filterPcDestItems(q) {
        let matchCount = 0;
        const query = (q || '').toLowerCase().trim();
        pcDestItems.forEach(item => {
            const searchData = item.getAttribute('data-search') || '';
            if (query === '' || searchData.includes(query)) {
                item.style.display = 'block';
                matchCount++;
            } else {
                item.style.display = 'none';
            }
        });
        if (pcDestNoResults) {
            pcDestNoResults.style.display = matchCount === 0 ? 'block' : 'none';
        }
    }

    if (pcDestToggle) {
        pcDestToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (pcDestMenu.style.display === 'block') {
                closePcDestDropdown();
            } else {
                openPcDestDropdown();
            }
        });
    }

    if (pcDestSearch) {
        pcDestSearch.addEventListener('input', function() {
            filterPcDestItems(this.value);
        });
        pcDestSearch.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    pcDestItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const code = this.getAttribute('data-code');
            const name = this.getAttribute('data-name');
            if (pcDestHiddenInput) pcDestHiddenInput.value = code;
            if (pcDestLabel) {
                pcDestLabel.innerHTML = `<span class="text-dark font-weight-bold"><i class="fas fa-check-circle text-success mr-1"></i> ${name}</span>`;
            }
            pcDestItems.forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            closePcDestDropdown();
        });
    });

    document.addEventListener('click', function(e) {
        if (pcDestContainer && !pcDestContainer.contains(e.target)) {
            closePcDestDropdown();
        }
    });

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
                }
            }
            
            if (e.key === 'Backspace' && match && selectionStart === lineStart + match[0].length) {
                e.preventDefault();
            }
        });

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
        });
    }

    window.handleAction = function(action) {
        let remarks = inlineRemarks ? inlineRemarks.value.trim() : '';
        let lines = remarks.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        let cleanedLines = lines.map(line => line.replace(/^\d+\.\s*/, '').trim()).filter(l => l.length > 0);

        // Strict Remarks Validation
        if (cleanedLines.length === 0) {
            Swal.fire({ 
                title: 'Remarks Required!', 
                text: 'You must enter scrutiny remarks before performing this action.', 
                icon: 'warning', 
                background: '#ffffff', 
                color: '#0f172a' 
            });
            if (inlineRemarks) inlineRemarks.focus();
            return;
        }

        const targetDest = pcDestHiddenInput ? pcDestHiddenInput.value : null;

        // Strict Destination Validation for Send Case
        if (action === 'forward' && !targetDest) {
            Swal.fire({ 
                title: 'Destination Required!', 
                text: 'Please select a destination department or authority to send the case.', 
                icon: 'warning', 
                background: '#ffffff', 
                color: '#0f172a' 
            });
            if (pcDestToggle) openPcDestDropdown();
            return;
        }

        let liItems = cleanedLines.map(line => `<li>${line}</li>`).join('');
        let finalHtml = `<ol start="${nextNum}">${liItems}</ol>`;

        const remarksInput = document.getElementById('remarksHiddenInput');
        const actionInput = document.getElementById('formActionInput');
        const targetStatusInput = document.getElementById('formTargetStatusInput');
        const actionForm = document.getElementById('authorityActionForm');

        if (!actionForm) return;

        if (remarksInput) remarksInput.value = finalHtml;
        if (actionInput) actionInput.value = action;
        if (targetStatusInput) targetStatusInput.value = targetDest;

        let confirmTitle = 'Confirm Action?';
        let confirmText = 'You are about to submit your decision.';
        let confirmBtnColor = '#16a34a';

        if (action === 'approve') {
            confirmTitle = 'Approve Purchase Case?';
            confirmText = 'Are you sure you want to approve this purchase case?';
            confirmBtnColor = '#16a34a';
        } else if (action === 'cancel') {
            confirmTitle = 'Cancel / Reject Case?';
            confirmText = 'Are you sure you want to reject/cancel this purchase case?';
            confirmBtnColor = '#dc2626';
        } else if (action === 'forward') {
            const selectedItem = document.querySelector(`.pc-dest-option-item[data-code="${targetDest}"]`);
            const destText = selectedItem ? selectedItem.getAttribute('data-name') : (targetDest || 'selected destination');
            confirmTitle = 'Send Case?';
            confirmText = `Send this case to ${destText}?`;
            confirmBtnColor = '#16a34a';
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: confirmTitle,
                text: confirmText,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Confirm & Proceed',
                cancelButtonText: 'Cancel',
                confirmButtonColor: confirmBtnColor,
                background: '#ffffff',
                color: '#0f172a'
            }).then((result) => {
                if (result.isConfirmed) {
                    actionForm.submit();
                }
            });
        } else {
            if (confirm(confirmText)) {
                actionForm.submit();
            }
        }
    };
</script>
@endif
