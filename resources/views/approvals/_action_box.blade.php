@php
    $u = Auth::user();
    $userArea = strtolower(trim((string) ($u?->acc_untarea ?? '')));
    $service = app(\App\Services\PurchaseApprovalService::class);
    $canApprove = $service->canApprove($userArea, $purchase->pcs_price);
    $nextAuthName = $service->getNextAuthorityName($purchase, $userArea);
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
@endphp

@if($isCurrentStage)
<div class="card bg-dark border-gold elevation-2 mt-2" style="border-radius: 12px; border: 1px solid rgba(255,193,7,0.3) !important;">
    <div class="card-header bg-navy py-1 px-3" style="border-bottom: 1px solid rgba(255,193,7,0.2) !important;">
        <h3 class="card-title text-warning font-weight-bold mb-0" style="font-size: 0.9rem; letter-spacing: 0.5px;">
            <i class="fas fa-stamp mr-2" style="font-size: 0.8rem;"></i> Authority Action
        </h3>
    </div>
    <div class="card-body py-2 px-3">
        <form id="authorityActionForm" action="{{ $isInitiator ? route('purchase.release', $purchase->pcs_id) : route('nrdi.purchase_cases.action', $purchase->pcs_id) }}" method="POST">
            @csrf
            <input type="hidden" name="action" id="formActionInput" value="forward">
            <input type="hidden" name="target_status" id="formTargetStatusInput" value="">

            <div class="form-group mb-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="text-warning font-weight-bold mb-0 rajdhani" style="font-size: 11px; letter-spacing: 0.4px;">REMARKS / INSTRUCTIONS</label>
                    <span class="badge badge-dark text-muted" style="font-size: 8px;">REQUIRED FOR RETURN</span>
                </div>
                <textarea name="remarks" id="remarksInput" class="form-control bg-navy text-white border-secondary shadow-inner" style="font-size: 12px; border-radius: 6px; padding: 6px 10px; background: #001226 !important; border: 1px solid #334155 !important;" rows="2" placeholder="Enter remarks..."></textarea>
            </div>

            <div class="mt-2">
                @if($canApprove)
                    <button type="button" onclick="confirmForwardAction('approve')" class="btn btn-success btn-block shadow-sm py-1 rajdhani font-weight-bold" style="font-size: 14px; letter-spacing: 0.5px; border-radius: 8px;">
                        <i class="fas fa-check-double mr-1"></i> FINAL APPROVAL
                    </button>
                @else
                    <button type="button" onclick="confirmForwardAction('forward')" class="btn {{ $isInitiator ? 'btn-warning' : 'btn-primary' }} btn-block shadow-sm py-1 rajdhani font-weight-bold" style="font-size: 14px; letter-spacing: 0.5px; border-radius: 8px;">
                        <i class="fas fa-share-square mr-1"></i> {{ $isInitiator ? 'RELEASE TO HQ' : 'FORWARD' }}
                    </button>
                @endif

                @if(!$isInitiator)
                <div class="row mt-2 main-action-btns mx-n1">
                    <div class="col-6 px-1">
                        <button type="button" onclick="$('.main-action-btns').hide(); $('#inlineReturnSection').slideDown();" class="btn btn-outline-warning btn-block py-1 rajdhani font-weight-bold" style="font-size: 11px; border-radius: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <i class="fas fa-undo mr-1"></i> RETURN
                        </button>
                    </div>
                    <div class="col-6 px-1">
                        @if(in_array($userArea, ['nrdi', 'rdw', 'hqs', 'proc', 'fin']))
                        <button type="button" onclick="submitAuthorityAction('reject')" class="btn btn-outline-danger btn-block py-1 rajdhani font-weight-bold" style="font-size: 10px; border-radius: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding-left: 2px; padding-right: 2px;">
                            <i class="fas fa-ban mr-1"></i> NOT APPROVE
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Inline Return Selection (Directly below button) --}}
                <div id="inlineReturnSection" style="display: none; background: rgba(243,156,18,0.04); border: 1px solid rgba(243,156,18,0.3); border-radius: 8px; padding: 10px; margin-top: 8px; animation: slideDown 0.2s ease-out;">
                    <label class="text-warning font-weight-bold mb-1 rajdhani" style="font-size: 11px;">SELECT TARGET</label>
                    <select id="inlineTargetStatus" class="form-control bg-navy text-white border-secondary mb-2" style="font-size: 12px; height: 32px; border-radius: 5px; background: #001226 !important; padding: 0 8px;">
                        @foreach($returnTargets as $status => $name)
                            <option value="{{ $status }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <div class="d-flex gap-1">
                        <button type="button" onclick="submitReturnAction()" class="btn btn-warning btn-sm font-weight-bold rajdhani flex-grow-1 py-1" style="font-size: 11px;">
                            CONFIRM RETURN
                        </button>
                        <button type="button" onclick="$('#inlineReturnSection').hide(); $('.main-action-btns').show();" class="btn btn-dark btn-sm px-3 py-1">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </form>
    </div>
</div>

<style>
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
    function confirmForwardAction(action) {
        const nextAuth = "{{ $nextAuthName }}";
        const isApprove = (action === 'approve');
        
        let title = isApprove ? 'Final Approval?' : 'Forward Case?';
        let text = isApprove ? 'This will grant final approval to this purchase case.' : `This case will be forwarded to: ${nextAuth}`;

        Swal.fire({
            title: title,
            text: text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: isApprove ? 'Yes, Approve' : 'Yes, Forward',
            background: '#001226',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                submitAuthorityAction(action);
            }
        });
    }

    function submitAuthorityAction(action) {
        const remarks = document.getElementById('remarksInput').value.trim();
        
        if ((action === 'return' || action === 'reject') && !remarks) {
            Swal.fire({
                title: 'Remarks Required!',
                text: 'Please write a note/reason for returning or not approving this case.',
                icon: 'warning',
                confirmButtonColor: '#f39c12',
                background: '#001226',
                color: '#fff'
            });
            return;
        }

        document.getElementById('formActionInput').value = action;
        document.getElementById('authorityActionForm').submit();
    }

    function submitReturnAction() {
        const target = document.getElementById('inlineTargetStatus').value;
        const remarks = document.getElementById('remarksInput').value.trim();

        if (!remarks) {
            Swal.fire({
                title: 'Remarks Required!',
                text: 'Please provide return instructions in the remarks field first.',
                icon: 'warning',
                confirmButtonColor: '#f39c12',
                background: '#001226',
                color: '#fff'
            });
            return;
        }

        document.getElementById('formActionInput').value = 'return';
        document.getElementById('formTargetStatusInput').value = target;
        document.getElementById('authorityActionForm').submit();
    }
</script>
@else
    @if($isInitiator && !in_array(strtolower($purchase->pcs_status), ['approved', 'rejected']))
        <div class="card bg-dark border-info mt-2 shadow-sm" style="border-radius: 12px; border: 1px solid rgba(0, 123, 255, 0.3) !important;">
             <div class="card-body py-3 text-center">
                 <div class="mb-2">
                     <i class="fas fa-paper-plane fa-2x text-info opacity-50"></i>
                 </div>
                 <h5 class="text-white rajdhani font-weight-bold mb-1" style="letter-spacing: 0.5px; font-size: 1.1rem;">Case Released to HQ</h5>
                 <p class="text-muted mb-3 px-2" style="font-size: 0.85rem; line-height: 1.4;">
                    Successfully released. Currently at: <br>
                    <span class="text-info font-weight-bold d-block mt-1" style="font-size: 1rem; letter-spacing: 0.5px;">
                        <i class="fas fa-building mr-1"></i> {{ $currentStatusDisplay }}
                    </span>
                 </p>
                 
                 <div class="px-2">
                     <button type="button" onclick="confirmHoldCase()" class="btn btn-outline-warning btn-block py-2 rajdhani font-weight-bold shadow-sm" style="font-size: 0.9rem; border-width: 1.5px; border-radius: 8px;">
                         <i class="fas fa-hand-paper mr-2"></i> HOLD / REVERT THIS CASE
                     </button>
                 </div>
                 
                 <p class="text-muted mt-2 mb-0" style="font-size: 0.7rem; opacity: 0.6; font-style: italic;">Note: Case will pull back to Draft mode.</p>
             </div>
        </div>
        
        <form id="holdCaseForm" action="{{ route('purchase.hold', $purchase->pcs_id) }}" method="POST" style="display: none;">
            @csrf
        </form>

        <script>
            function confirmHoldCase() {
                Swal.fire({
                    title: 'Hold/Revert Case?',
                    text: "Do you want to pull this case back from HQ? It will become editable again.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f39c12',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Hold it!',
                    background: '#001226',
                    color: '#fff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('holdCaseForm').submit();
                    }
                });
            }
        </script>
    @endif
@endif
