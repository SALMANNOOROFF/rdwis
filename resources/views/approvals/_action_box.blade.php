@php
    $u = Auth::user();
    $userArea = strtolower(trim((string) ($u?->acc_untarea ?? '')));
    $service = app(\App\Services\PurchaseApprovalService::class);
    $area = strtolower(trim($area ?? Auth::user()->acc_untarea));
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
    $isInitiator = in_array($userArea, ['prj', 'rdwprj', 'division']);
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
        <div class="font-weight-bold rajdhani text-white" style="font-size: 13px;">
            <i class="fas fa-user-circle text-accent mr-1"></i> {{ $u->acc_name }} 
            <span class="text-muted small ml-1" style="font-weight: 500;">({{ strtoupper($userArea) }})</span>
        </div>
    </div>

    <form id="authorityActionForm" action="{{ $isInitiator ? route('purchase.release', $purchase->pcs_id) : route('nrdi.purchase_cases.action', $purchase->pcs_id) }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="formActionInput" value="forward">
        <input type="hidden" name="target_status" id="formTargetStatusInput" value="">
        <input type="hidden" name="remarks" id="remarksHiddenInput">

        <div class="mb-2">
            <textarea id="inlineRemarks" class="form-control" placeholder="Type your remarks here..." style="background: rgba(255,255,255,0.03); color: #fff; font-family: 'Arial', sans-serif; font-size: 11pt; min-height: 60px; border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; padding: 8px 10px; outline: none; box-shadow: none; resize: vertical;"></textarea>
        </div>

        <div class="d-flex" style="gap: 10px; width: 100%;">
            @if($canApprove)
                <button type="button" onclick="handleAction('approve')" class="dg-btn-action dg-btn-success flex-grow-1" style="font-size: 13px; padding: 10px 14px;">
                    <i class="fas fa-check-double mr-1"></i> APPROVE
                </button>
                <div class="btn-group flex-grow-1">
                    <button type="button" class="dg-btn-action dg-btn-return w-100 dropdown-toggle" data-toggle="dropdown" id="btnReturn" disabled aria-haspopup="true" aria-expanded="false" style="font-size: 13px; padding: 10px 14px;">
                        <i class="fas fa-undo mr-1"></i> RETURN
                    </button>
                    <div class="dropdown-menu dropdown-menu-right bg-dark border-secondary shadow-lg mt-1" style="min-width: 180px; border-radius: 8px; padding: 5px 0;">
                        <h6 class="dropdown-header text-warning rajdhani mb-1" style="font-size: 9px; letter-spacing: 0.5px;">RETURN TO:</h6>
                        @foreach($returnTargets as $status => $name)
                            <a class="dropdown-item text-white py-2 d-flex align-items-center" href="javascript:void(0)" onclick="confirmReturn('{{ $status }}', '{{ $name }}')">
                                <i class="fas fa-chevron-left mr-2 text-muted" style="font-size: 9px;"></i>
                                <span class="rajdhani" style="font-size: 12px; font-weight: 600;">{{ $name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                @if($isInitiator)
                    <button type="button" onclick="handleAction('forward')" class="dg-btn-action dg-btn-success w-100" style="font-size: 13px; padding: 10px 14px;">
                        <i class="fas fa-paper-plane mr-1"></i> RELEASE TO HQ
                    </button>
                @else
                    <button type="button" onclick="handleAction('forward')" class="dg-btn-action dg-btn-success flex-grow-1" style="font-size: 13px; padding: 10px 14px;">
                        <div class="d-flex flex-column align-items-center">
                            <span><i class="fas fa-paper-plane mr-1"></i> FORWARD</span>
                            @if($nextAuthName)<span style="font-size: 9px; opacity: 0.9; margin-top: 2px;">TO: {{ strtoupper($nextAuthName) }}</span>@endif
                        </div>
                    </button>
                    <div class="btn-group flex-grow-1">
                        <button type="button" class="dg-btn-action dg-btn-return w-100 dropdown-toggle" data-toggle="dropdown" id="btnReturn" disabled aria-haspopup="true" aria-expanded="false" style="font-size: 13px; padding: 10px 14px;">
                            <i class="fas fa-undo mr-1"></i> RETURN
                        </button>
                        <div class="dropdown-menu dropdown-menu-right bg-dark border-secondary shadow-lg mt-1" style="min-width: 180px; border-radius: 8px; padding: 5px 0;">
                            <h6 class="dropdown-header text-warning rajdhani mb-1" style="font-size: 9px; letter-spacing: 0.5px;">RETURN TO:</h6>
                            @foreach($returnTargets as $status => $name)
                                <a class="dropdown-item text-white py-2 d-flex align-items-center" href="javascript:void(0)" onclick="confirmReturn('{{ $status }}', '{{ $name }}')">
                                    <i class="fas fa-chevron-left mr-2 text-muted" style="font-size: 9px;"></i>
                                    <span class="rajdhani" style="font-size: 12px; font-weight: 600;">{{ $name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </form>
</div>

<style>
.dg-btn-action {
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 10px;
    letter-spacing: 0.5px;
    padding: 6px 8px;
    border-radius: 6px;
    border: 1.5px solid transparent; 
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
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
    const nextNumOld = {{ $nextRemarkNumber }};
    const inlineRemarksOld = document.getElementById('inlineRemarks');
    const btnReturn = document.getElementById('btnReturn');

    let localParaCounterOld = nextNumOld;

    // Auto-initialize with numbering on first focus
    inlineRemarksOld.addEventListener('focus', function() {
        if (this.value.trim() === '') {
            this.value = localParaCounterOld + ". ";
        }
    });

    inlineRemarksOld.addEventListener('keydown', function(e) {
        const selectionStart = this.selectionStart;
        const text = this.value;
        const lastNewline = text.lastIndexOf('\n', selectionStart - 1);
        const lineStart = lastNewline === -1 ? 0 : lastNewline + 1;
        const currentLine = text.substring(lineStart, selectionStart);
        const match = currentLine.match(/^\d+\. /);

        if (match && selectionStart < lineStart + match[0].length) {
            if (e.key === 'Backspace' || e.key === 'Delete' || (e.key.length === 1 && e.key !== 'Enter')) {
                e.preventDefault(); return;
            }
        }
        if (e.key === 'Enter') {
            e.preventDefault();
            if (currentLine.trim().length > (match ? match[0].trim().length : 0)) {
                localParaCounterOld++;
                const newNumber = "\n" + localParaCounterOld + ". ";
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

    inlineRemarksOld.addEventListener('input', function() {
        const prefix = nextNumOld + ". ";
        if (!this.value.startsWith(prefix)) {
            const currentVal = this.value;
            if (currentVal.length < prefix.length) {
                this.value = prefix;
            } else {
                this.value = prefix + currentVal.replace(/^\d+\.?\s*/, '');
            }
            this.selectionStart = this.selectionEnd = prefix.length;
        }
        if (btnReturn) btnReturn.disabled = (this.value.trim().length <= prefix.trim().length);
    });

    function confirmReturn(targetStatus, targetName) {
        let remarks = inlineRemarksOld.value.trim();
        let lines = remarks.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        let cleanedLines = lines.map(line => line.replace(/^\d+\.\s*/, '').trim()).filter(l => l.length > 0);

        if (cleanedLines.length === 0) {
            Swal.fire({ title: 'Remarks Required!', text: 'Explain why you are returning this case.', icon: 'warning', background: '#001226', color: '#fff' });
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

    function handleAction(action, targetStatus = null) {
        let remarks = inlineRemarksOld.value.trim();
        let lines = remarks.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        let cleanedLines = lines.map(line => line.replace(/^\d+\.\s*/, '').trim()).filter(l => l.length > 0);

        if (action === 'return' && cleanedLines.length === 0) {
            Swal.fire({ title: 'Remarks Required!', text: 'Explain why you are returning this case.', icon: 'warning', background: '#001226', color: '#fff' });
            return;
        }

        if (cleanedLines.length === 0) {
            if (action === 'approve') cleanedLines = ['Case approved'];
            else if (action === 'forward') cleanedLines = ['Forwarded for review'];
        }

        let liItems = cleanedLines.map(line => `<li>${line}</li>`).join('');
        let finalHtml = `<ol start="${nextNumOld}">${liItems}</ol>`;

        document.getElementById('remarksHiddenInput').value = finalHtml;
        document.getElementById('formActionInput').value = action;
        
        if (action === 'return') {
            if (!targetStatus) return;
            document.getElementById('formTargetStatusInput').value = targetStatus;
        }

        if (action === 'return') {
            document.getElementById('authorityActionForm').submit();
        } else {
            Swal.fire({
                title: 'Confirm Action?',
                text: `Submit your decision for purchase #${ {{ $purchase->pcs_id }} }?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                background: '#001226', color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('authorityActionForm').submit();
                }
            });
        }
    }
</script>
@else
    @if($isInitiator && !in_array(strtolower($purchase->pcs_status), ['approved', 'rejected']))
        @php $isAtFirstAuthority = (trim($purchase->pcs_status) === 'Under Scrutiny'); @endphp
        <div class="card bg-dark border-info mt-2 shadow-sm" style="border-radius: 12px; border: 1px solid rgba(0, 123, 255, 0.3) !important;">
             <div class="card-body py-3 text-center">
                 <div class="mb-2"><i class="fas fa-paper-plane fa-2x text-info opacity-50"></i></div>
                 <h5 class="text-white rajdhani font-weight-bold mb-1" style="letter-spacing: 0.5px; font-size: 1.1rem;">Case Released to HQ</h5>
                 <p class="text-muted mb-3 px-2" style="font-size: 0.85rem;">Currently at: <span class="text-info font-weight-bold">{{ $currentStatusDisplay }}</span></p>
                 <div class="px-2">
                    @if($isAtFirstAuthority)
                        <button type="button" onclick="confirmHoldCase()" class="btn btn-outline-warning btn-block py-2 rajdhani font-weight-bold" style="font-size: 0.9rem; border-radius: 8px;">
                            <i class="fas fa-hand-paper mr-2"></i> HOLD / REVERT CASE
                        </button>
                    @endif
                 </div>
             </div>
        </div>
        <form id="holdCaseForm" action="{{ route('purchase.hold', $purchase->pcs_id) }}" method="POST" style="display: none;">@csrf</form>
        <script>
            function confirmHoldCase() {
                Swal.fire({ title: 'Hold/Revert Case?', text: "Pull back to Draft mode?", icon: 'warning', showCancelButton: true, confirmButtonColor: '#f39c12', confirmButtonText: 'Yes, Hold it!', background: '#001226', color: '#fff' })
                .then((result) => { if (result.isConfirmed) document.getElementById('holdCaseForm').submit(); });
            }
        </script>
    @endif
@endif
