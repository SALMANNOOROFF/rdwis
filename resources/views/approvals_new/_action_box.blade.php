@php
    $u = Auth::user();
    $userArea = strtolower(trim((string) ($u?->acc_untarea ?? '')));
    $service = app(\App\Services\PurchaseApprovalService::class);
    $area = $area ?? $userArea;
    $canApprove = $service->canApprove($area, $purchase->pcs_price);
    $nextAuthName = $service->getNextAuthorityName($purchase, $area);
    $returnTargets = $service->getReturnTargets($purchase);
    
    // Determine the expected status for this user to act
    $expectedStatus = match($userArea) {
        'prj', 'rdwprj', 'division' => ['Draft', 'Returned'],
        'proc' => ['Under Scrutiny'],
        'fin'  => ['With DFinance'],
        'rdw'  => ['With MD'],
        'hqs'  => ['With DDG'],
        'nrdi' => ['With DG'],
        default => ['None']
    };

    $isCurrentStage = in_array(trim($purchase->pcs_status), $expectedStatus);
    $isInitiator = in_array($userArea, ['prj', 'rdwprj', 'division', 'initiation']);
    $currentStatusDisplay = $service->getStatusDisplayName($purchase->pcs_status);

    // Calculate the next numbering for the list
    $liCount = 0;
    foreach($purchase->decisions as $dec) {
        if (strpos($dec->pdec_remarks, '<li') !== false) {
            $liCount += substr_count($dec->pdec_remarks, '<li');
        } else if (!empty(trim(strip_tags($dec->pdec_remarks)))) {
            $liCount += 1;
        }
    }
    $nextRemarkNumber = $liCount + 1;
@endphp

@if($isCurrentStage)
<div class="mb-4 pb-3 border-bottom border-secondary" style="border-bottom-style: dashed !important;">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="font-weight-bold rajdhani text-white" style="font-size: 14px;">
            <i class="fas fa-user-circle text-accent mr-1"></i> {{ $u->acc_name }} 
            <span class="text-muted small ml-1" style="font-weight: 500;">({{ strtoupper($userArea) }})</span>
            <span class="ml-2 pl-2 border-left border-secondary font-weight-bold" style="font-size: 10px; color: var(--rd-accent); letter-spacing: 0.5px;">
                <i class="fas fa-pen-nib mr-1"></i> WRITING REMARKS...
            </span>
        </div>
    </div>

    <form id="authorityActionForm" action="{{ $isInitiator ? route('purchase.release', $purchase->pcs_id) : route('nrdi.purchase_cases_new.action', $purchase->pcs_id) }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="formActionInput" value="forward">
        <input type="hidden" name="target_status" id="formTargetStatusInput" value="">
        <input type="hidden" name="remarks" id="remarksHiddenInput">

        <div id="returnTargetWrapper" style="display:none;" class="mb-2">
            <label class="text-warning small font-weight-bold mb-1 rajdhani">SELECT RETURN TARGET:</label>
            <select id="targetStatusSelect" class="form-control form-control-sm bg-navy text-white border-secondary" style="font-size: 11px;">
                @foreach($returnTargets as $status => $name)
                    <option value="{{ $status }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-muted small rajdhani"><i class="fas fa-pen-nib mr-1"></i> Remarks Box</span>
            </div>
            <textarea id="inlineRemarks" class="form-control" placeholder="Type your remarks here..." style="background: transparent; color: #fff; font-family: 'Arial', sans-serif; font-size: 12pt; min-height: 80px; border: none; border-bottom: 2px solid rgba(255,255,255,0.2); border-radius: 0; padding: 5px 0; outline: none; box-shadow: none; resize: vertical;"></textarea>
        </div>

        <div class="d-flex gap-2" style="width: 100%;">
            @if($canApprove)
                {{-- FINAL AUTHORITY (MD < 2L, DDG < 6L, DG) --}}
                <button type="button" onclick="handleAction('approve')" class="dg-btn-action dg-btn-success flex-grow-1" style="flex: 1;">
                    <i class="fas fa-check-double mr-1"></i> APPROVE CASE
                </button>
                <button type="button" onclick="handleAction('reject')" class="dg-btn-action dg-btn-danger flex-grow-1" style="flex: 1;">
                    <i class="fas fa-ban mr-1"></i> REJECT (KILL)
                </button>
                <button type="button" onclick="handleAction('return')" class="dg-btn-action dg-btn-return flex-grow-1" id="btnReturn" disabled style="flex: 1;">
                    <i class="fas fa-undo mr-1"></i> RETURN
                </button>
            @else
                {{-- INTERMEDIATE OR INITIATOR --}}
                @if($isInitiator)
                    <button type="button" onclick="handleAction('forward')" class="dg-btn-action dg-btn-success flex-grow-1" style="flex: 1;">
                        <i class="fas fa-paper-plane mr-1"></i> RELEASE TO HQ
                    </button>
                @else
                    <button type="button" onclick="handleAction('forward')" class="dg-btn-action dg-btn-success flex-grow-1" style="flex: 1;">
                        <div class="d-flex flex-column align-items-center">
                            <span><i class="fas fa-thumbs-up mr-1"></i> RECOMMEND</span>
                            @if($nextAuthName)<span style="font-size: 7px; opacity: 0.8; margin-top: -2px;">TO: {{ strtoupper($nextAuthName) }}</span>@endif
                        </div>
                    </button>
                    <button type="button" onclick="handleAction('forward_negative')" class="dg-btn-action dg-btn-info flex-grow-1" style="flex: 1;">
                        <div class="d-flex flex-column align-items-center">
                            <span><i class="fas fa-times-circle mr-1"></i> NOT RECOMMEND</span>
                            @if($nextAuthName)<span style="font-size: 7px; opacity: 0.8; margin-top: -2px;">FORWARD TO: {{ strtoupper($nextAuthName) }}</span>@endif
                        </div>
                    </button>
                    <button type="button" onclick="handleAction('return')" class="dg-btn-action dg-btn-return flex-grow-1" id="btnReturn" disabled style="flex: 1;">
                        <i class="fas fa-undo mr-1"></i> RETURN
                    </button>
                @endif
            @endif
        </div>
    </form>
</div>

<style>
.dg-btn-action {
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.8px;
    padding: 12px 15px;
    border-radius: 8px;
    border: 2px solid transparent; /* Thick border for premium feel */
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    text-transform: uppercase;
}
.dg-btn-success { background: rgba(40,167,69,0.1); color: #28a745; border-color: #28a745; }
.dg-btn-success:hover { background: #28a745; color: #fff; }

.dg-btn-info { background: rgba(0,123,255,0.1); color: #007bff; border-color: #007bff; }
.dg-btn-info:hover { background: #007bff; color: #fff; }

.dg-btn-danger { background: rgba(220,53,69,0.1); color: #dc3545; border-color: #dc3545; }
.dg-btn-danger:hover { background: #dc3545; color: #fff; }

.dg-btn-return { background: rgba(220,53,69,0.05); color: #dc3545; border-color: rgba(220,53,69,0.3); }
.dg-btn-return:hover:not(:disabled) { background: #dc3545; color: #fff; }
.dg-btn-return:disabled { opacity: 0.4; cursor: not-allowed; }
</style>

<script>
    const nextNum = {{ $nextRemarkNumber }};
    const inlineRemarks = document.getElementById('inlineRemarks');
    const btnReturn = document.getElementById('btnReturn');

    let localParaCounter = nextNum;

    // Initialize auto-numbering
    if (inlineRemarks.value.trim() === '') {
        inlineRemarks.value = localParaCounter + ". ";
    }

    // Prevent deletion of the initial paragraph number and handle Enter for new numbering
    inlineRemarks.addEventListener('keydown', function(e) {
        const selectionStart = this.selectionStart;
        const text = this.value;
        
        // Find the start of the current line
        const lastNewline = text.lastIndexOf('\n', selectionStart - 1);
        const lineStart = lastNewline === -1 ? 0 : lastNewline + 1;
        const currentLine = text.substring(lineStart, selectionStart);
        
        // Check if cursor is within a paragraph number (e.g., "1. ")
        const match = currentLine.match(/^\d+\. /);
        if (match && selectionStart < lineStart + match[0].length) {
            if (e.key === 'Backspace' || e.key === 'Delete' || (e.key.length === 1 && e.key !== 'Enter')) {
                e.preventDefault();
                return;
            }
        }
        
        // Handle Enter key for new paragraph numbering
        if (e.key === 'Enter') {
            e.preventDefault();
            
            // Only add a new number if the current line has some text after the number
            if (currentLine.trim().length > (match ? match[0].trim().length : 0)) {
                localParaCounter++;
                const newNumber = "\n" + localParaCounter + ". ";
                
                const before = text.substring(0, selectionStart);
                const after = text.substring(selectionStart);
                
                this.value = before + newNumber + after;
                this.selectionStart = this.selectionEnd = before.length + newNumber.length;
            }
        }
        
        // Block backspace if it would delete the space after the number on any line
        if (e.key === 'Backspace' && match && selectionStart === lineStart + match[0].length) {
            e.preventDefault();
        }
    });

    inlineRemarks.addEventListener('input', function() {
        const prefix = nextNum + ". ";
        if (!this.value.startsWith(prefix)) {
            // Restore the prefix if it was deleted
            const currentVal = this.value;
            // Try to keep what's left if possible, or just reset to prefix
            if (currentVal.length < prefix.length) {
                this.value = prefix;
            } else {
                // If they typed something but deleted the start, prepend prefix
                // This is a bit complex, usually Select All + Delete just leaves it empty
                this.value = prefix + currentVal.replace(/^\d+\.?\s*/, '');
            }
            this.selectionStart = this.selectionEnd = prefix.length;
        }

        if (this.value.trim().length > prefix.trim().length) {
            btnReturn.disabled = false;
        } else {
            btnReturn.disabled = true;
        }
    });

    function handleAction(action) {
        let remarks = inlineRemarks.value.trim();
        
        // Split by newlines
        let lines = remarks.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        
        // Remove the manual "1. ", "2. " prefixes from each line before saving
        let cleanedLines = lines.map(line => {
            return line.replace(/^\d+\.\s*/, '').trim();
        }).filter(l => l.length > 0);

        let finalHtml = '';

        if (action === 'return' && cleanedLines.length === 0) {
            Swal.fire({ title: 'Remarks Required!', text: 'Remarks are compulsory for returning a case.', icon: 'warning', background: '#001226', color: '#fff' });
            return;
        }

        if (cleanedLines.length === 0) {
            if (action === 'approve' || action === 'forward') {
                cleanedLines = ['Recommended and forwarded'];
            } else if (action === 'forward_negative') {
                cleanedLines = ['Not recommended but forward for further review'];
            } else if (action === 'reject') {
                cleanedLines = ['Case rejected/not recommended'];
            }
        }

        let liItems = cleanedLines.map(line => `<li>${line}</li>`).join('');
        finalHtml = `<ol start="${nextNum}">${liItems}</ol>`;

        document.getElementById('remarksHiddenInput').value = finalHtml;
        document.getElementById('formActionInput').value = action;
        
        if (action === 'return') {
            document.getElementById('returnTargetWrapper').style.display = 'block';
            if (!document.getElementById('formTargetStatusInput').value) {
                Swal.fire({ title: 'Select Target', text: 'Please select where to return this case.', icon: 'info', background: '#001226', color: '#fff' });
                return;
            }
            document.getElementById('formTargetStatusInput').value = document.getElementById('targetStatusSelect').value;
        }

        Swal.fire({
            title: 'Confirm Action?',
            text: `You are about to submit your decision.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            background: '#001226',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('authorityActionForm').submit();
            }
        });
    }

    document.getElementById('targetStatusSelect')?.addEventListener('change', function() {
        document.getElementById('formTargetStatusInput').value = this.value;
    });
</script>
@endif
