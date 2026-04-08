@php
    $u = Auth::user();
    $userArea = strtolower(trim((string) ($u?->acc_untarea ?? '')));
    $canApprove = app(\App\Services\PurchaseApprovalService::class)->canApprove($userArea, $purchase->pcs_price);
    
    // Determine the expected status for this user to act
    $expectedStatus = match($userArea) {
        'proc' => 'Under Scrutiny',
        'fin'  => 'With DFinance',
        'rdw'  => 'With MD',
        'hqs'  => 'With DDG',
        'nrdi' => 'With DG',
        default => 'None'
    };

    $isCurrentStage = (trim($purchase->pcs_status) === $expectedStatus);
@endphp
@if($isCurrentStage)
<div class="card bg-dark border-gold elevation-4 mt-4">
    <div class="card-header bg-navy border-bottom border-warning py-3">
        <h3 class="card-title text-warning font-weight-bold" style="font-size: 1.3rem; letter-spacing: 0.8px;">
            <i class="fas fa-stamp mr-2"></i> Authority Action Panel
        </h3>
    </div>
    <div class="card-body py-4">
        <form id="authorityActionForm" action="{{ route('nrdi.purchase_cases.action', $purchase->pcs_id) }}" method="POST">
            @csrf
            <input type="hidden" name="action" id="formActionInput" value="forward">
            <input type="hidden" name="target_status" id="formTargetStatusInput" value="">

            <div class="form-group mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="text-warning font-weight-bold mb-0 rajdhani" style="font-size: 16px; letter-spacing: 0.6px;">DECISION REMARKS / INSTRUCTIONS</label>
                    <span class="badge badge-dark text-muted" style="font-size: 11px; border: 1px solid rgba(255,255,255,0.05);">REQUIRED FOR RETURN/NOT APPROVED</span>
                </div>
                <textarea name="remarks" id="remarksInput" class="form-control bg-navy text-white border-secondary shadow-inner" style="font-size: 16px; border-radius: 10px; padding: 15px; background: #001226 !important; border: 1px solid #4a5568 !important;" rows="5" placeholder="Enter detailed remarks or instructions..."></textarea>
            </div>

            <div class="mt-4">
                @if($canApprove)
                    <button type="button" onclick="submitAuthorityAction('approve')" class="btn btn-success btn-lg btn-block shadow-lg py-3 rajdhani font-weight-bold" style="font-size: 20px; letter-spacing: 1.5px;">
                        <i class="fas fa-check-double mr-2"></i> CONFIRM FINAL APPROVAL
                    </button>
                @else
                    <button type="button" onclick="submitAuthorityAction('forward')" class="btn btn-primary btn-lg btn-block shadow-lg py-3 rajdhani font-weight-bold" style="font-size: 20px; letter-spacing: 1.5px;">
                        <i class="fas fa-share-square mr-2"></i> FORWARD TO NEXT AUTHORITY
                    </button>
                @endif

                {{-- Inline Return Selection --}}
                <div id="inlineReturnSection" style="display: none; background: rgba(243,156,18,0.05); border: 1.5px solid #f39c12; border-radius: 12px; padding: 15px; margin-top: 15px; animation: slideDown 0.3s ease-out;">
                    <label class="text-warning font-weight-bold mb-2 rajdhani" style="font-size: 14px;">SELECT RETURN DESTINATION</label>
                    <div class="d-flex gap-2">
                        <select id="inlineTargetStatus" class="form-control bg-navy text-white border-secondary mr-2" style="font-size: 15px; height: 45px; border-radius: 8px; background: #001226 !important;">
                            @php
                                $hierarchy = [
                                    'Under Scrutiny' => 'Director Procurement',
                                    'With DFinance'  => 'Director Finance',
                                    'With MD'        => 'MD Office',
                                    'With DDG'       => 'DDG Office',
                                    'Returned'       => 'Division (Initiator)'
                                ];
                                $prevStatus = match($purchase->pcs_status) {
                                    'Under Scrutiny' => 'Returned',
                                    'With DFinance'  => 'Under Scrutiny',
                                    'With MD'        => 'With DFinance',
                                    'With DDG'       => 'With MD',
                                    'With DG'        => 'With DDG',
                                    default          => 'Returned'
                                };
                            @endphp
                            <option value="{{ $prevStatus }}">Immediate Previous ({{ $hierarchy[$prevStatus] ?? $prevStatus }})</option>
                            <option value="Returned">Division (Initiator)</option>
                            @if(in_array($userArea, ['fin', 'rdw', 'hqs', 'nrdi']) && $purchase->pcs_status != 'Under Scrutiny')
                                <option value="Under Scrutiny">Director Procurement</option>
                            @endif
                        </select>
                        <button type="button" onclick="submitReturnAction()" class="btn btn-warning font-weight-bold rajdhani px-4" style="height: 45px; white-space: nowrap;">
                            CONFIRM RETURN
                        </button>
                        <button type="button" onclick="$('#inlineReturnSection').slideUp(); $('.main-action-btns').slideDown();" class="btn btn-dark" style="height: 45px;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="row mt-3 main-action-btns">
                    <div class="col-6 pr-2">
                        <button type="button" onclick="$('.main-action-btns').slideUp(); $('#inlineReturnSection').slideDown();" class="btn btn-outline-warning btn-block py-3 rajdhani font-weight-bold" style="font-size: 16px;">
                            <i class="fas fa-undo mr-1"></i> RETURN CASE
                        </button>
                    </div>
                    <div class="col-6 pl-2">
                        @if($userArea == 'nrdi' || $userArea == 'rdw' || $userArea == 'hqs')
                        <button type="button" onclick="submitAuthorityAction('reject')" class="btn btn-outline-danger btn-block py-3 rajdhani font-weight-bold" style="font-size: 16px;">
                            <i class="fas fa-ban mr-1"></i> NOT APPROVED
                        </button>
                        @endif
                    </div>
                </div>
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
@endif
