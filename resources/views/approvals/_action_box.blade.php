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

            <div class="mt-2 d-flex justify-content-between align-items-center gap-1">
                @if($canApprove)
                    <button type="button" onclick="openActionModal('approve')" class="btn btn-success shadow-sm py-1 px-1 flex-grow-1 rajdhani font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px; border-radius: 6px; white-space: nowrap;">
                        <i class="fas fa-check-double mr-1"></i> APPROVE
                    </button>
                    <button type="button" onclick="openActionModal('return')" class="btn btn-outline-warning py-1 px-1 flex-grow-1 rajdhani font-weight-bold" style="font-size: 11px; border-radius: 6px; white-space: nowrap;">
                        <i class="fas fa-undo mr-1"></i> RETURN
                    </button>
                    <button type="button" onclick="openActionModal('reject')" class="btn btn-outline-danger py-1 px-1 flex-grow-1 rajdhani font-weight-bold" style="font-size: 11px; border-radius: 6px; white-space: nowrap;">
                        <i class="fas fa-ban mr-1"></i> REJECT
                    </button>
                @else
                    <button type="button" onclick="openActionModal('forward')" class="btn {{ $isInitiator ? 'btn-warning' : 'btn-primary' }} shadow-sm py-1 px-1 flex-grow-1 rajdhani font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px; border-radius: 6px; white-space: nowrap;">
                        <i class="fas fa-thumbs-up mr-1"></i> RECOMMEND
                    </button>

                    @if(!$isInitiator)
                        <button type="button" onclick="openActionModal('return')" class="btn btn-outline-warning py-1 px-1 flex-grow-1 rajdhani font-weight-bold" style="font-size: 11px; border-radius: 6px; white-space: nowrap;">
                            <i class="fas fa-undo mr-1"></i> RETURN
                        </button>
                        <button type="button" onclick="openActionModal('reject')" class="btn btn-outline-danger py-1 px-1 flex-grow-1 rajdhani font-weight-bold" style="font-size: 11px; border-radius: 6px; white-space: nowrap;">
                            <i class="fas fa-times-circle mr-1"></i> NOT RECOMMEND
                        </button>
                    @endif
                @endif
            </div>
        </form>
    </div>
</div>

<!-- ACTION MODAL -->
<div class="modal fade" id="actionModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-bottom border-secondary bg-navy">
                <h5 class="modal-title rajdhani font-weight-bold text-warning" id="actionModalTitle">
                    <i class="fas fa-comment-dots mr-2"></i> Provide Remarks
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2" id="actionModalAlert" style="font-size:12px;"></div>
                
                <div id="modalReturnTargetWrapper" style="display:none;" class="mb-3">
                    <label class="text-warning font-weight-bold mb-1 rajdhani" style="font-size: 13px;">SELECT RETURN TARGET:</label>
                    <select id="modalTargetStatus" class="form-control bg-navy text-white border-secondary" style="font-size: 13px; border-radius: 5px;">
                        @foreach($returnTargets as $status => $name)
                            <option value="{{ $status }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <label class="text-warning font-weight-bold mb-1 rajdhani" style="font-size: 13px;">REMARKS / COMMENTS:</label>
                <div style="background:#fff; color:#000;">
                    <textarea id="summernoteRemarks" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm px-4 font-weight-bold rajdhani" onclick="submitModalAction()" id="actionModalSubmitBtn">
                    CONFIRM ACTION
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Summernote adjustments for the modal */
.note-editor.note-frame { border: 1px solid #ced4da !important; border-radius: 8px; overflow: hidden; }
.note-editable { background: #fff !important; color: #000 !important; min-height: 200px; font-family: 'Arial', sans-serif; font-size: 12pt; }
.note-toolbar { background: #f4f6f9 !important; border-bottom: 1px solid #dee2e6 !important; }
</style>

<script>
    let currentAction = '';
    
    $(document).ready(function() {
        $('#summernoteRemarks').summernote({
            height: 200,
            toolbar: [
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'hr']],
                ['view', ['help']]
            ],
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Impact', 'Tahoma', 'Times New Roman', 'Verdana'],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '24', '36'],
            fontSizeUnits: ['px', 'pt'],
            callbacks: {
                onInit: function() {
                    $(this).summernote('fontName', 'Arial');
                    $(this).summernote('fontSize', '12');
                }
            }
        });
    });

    function openActionModal(action) {
        currentAction = action;
        const nextAuth = "{{ $nextAuthName }}";
        let title = '';
        let text = '';
        let btnColor = '';
        let btnText = '';

        $('#modalReturnTargetWrapper').hide();

        if (action === 'approve') {
            title = 'Final Approval';
            text = 'This will grant final approval to this purchase case.';
            btnColor = 'btn-success';
            btnText = 'APPROVE CASE';
        } else if (action === 'forward') {
            title = 'Recommend Case';
            text = `This case will be recommended and forwarded to: <strong>${nextAuth}</strong>`;
            btnColor = 'btn-primary';
            btnText = 'FORWARD CASE';
        } else if (action === 'return') {
            title = 'Return Case';
            text = 'Please select where to return this case and provide reasons.';
            btnColor = 'btn-warning';
            btnText = 'RETURN CASE';
            $('#modalReturnTargetWrapper').show();
        } else if (action === 'reject') {
            title = 'Not Recommended';
            text = 'This case will be flagged as Not Recommended. Remarks are mandatory.';
            btnColor = 'btn-danger';
            btnText = 'NOT RECOMMENDED';
        }

        $('#actionModalTitle').html(`<i class="fas fa-comment-dots mr-2"></i> ${title}`);
        $('#actionModalAlert').html(text);
        
        let submitBtn = $('#actionModalSubmitBtn');
        submitBtn.removeClass('btn-success btn-primary btn-warning btn-danger').addClass(btnColor).text(btnText);

        // Initialize Summernote with the ordered list
        let nextNum = {{ $nextRemarkNumber }};
        let initialContent = `<ol start="${nextNum}"><li><br></li></ol>`;
        $('#summernoteRemarks').summernote('code', initialContent);

        $('#actionModal').modal('show');
    }

    function submitModalAction() {
        let content = $('#summernoteRemarks').summernote('code').trim();
        
        // Basic check to see if they actually wrote something other than the empty list
        let plainText = $('<div>').html(content).text().trim();
        if ((currentAction === 'return' || currentAction === 'reject') && plainText === '') {
            Swal.fire({
                title: 'Remarks Required!',
                text: 'Please write your remarks/comments before submitting.',
                icon: 'warning',
                confirmButtonColor: '#f39c12',
                background: '#001226',
                color: '#fff'
            });
            return;
        }

        // Put the HTML content into the hidden remarks input
        // Wait, where is the hidden input? I need to create it or use the existing one.
        if ($('#remarksInput').length === 0) {
            $('#authorityActionForm').append('<input type="hidden" name="remarks" id="remarksInput">');
        }
        $('#remarksInput').val(content);

        if (currentAction === 'return') {
            $('#formTargetStatusInput').val($('#modalTargetStatus').val());
        }

        $('#formActionInput').val(currentAction);
        
        // Hide modal and show loading state
        $('#actionModal').modal('hide');
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while the case is being updated.',
            allowOutsideClick: false,
            background: '#001226',
            color: '#fff',
            didOpen: () => {
                Swal.showLoading();
                document.getElementById('authorityActionForm').submit();
            }
        });
    }
</script>
@else
    @if($isInitiator && !in_array(strtolower($purchase->pcs_status), ['approved', 'rejected']))
        @php
            // Revert is only allowed if the case is still with the first authority (Director Procurement)
            $isAtFirstAuthority = (trim($purchase->pcs_status) === 'Under Scrutiny');
        @endphp
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
                    @if($isAtFirstAuthority)
                        <button type="button" onclick="confirmHoldCase()" class="btn btn-outline-warning btn-block py-2 rajdhani font-weight-bold shadow-sm" style="font-size: 0.9rem; border-width: 1.5px; border-radius: 8px;">
                            <i class="fas fa-hand-paper mr-2"></i> HOLD / REVERT THIS CASE
                        </button>
                        <p class="text-muted mt-2 mb-0" style="font-size: 0.7rem; opacity: 0.6; font-style: italic;">Note: Case will pull back to Draft mode.</p>
                    @else
                        <div class="alert alert-dark border-0 py-2 mb-0" style="background: rgba(255,255,255,0.03); border-radius: 8px;">
                            <i class="fas fa-lock mr-2 text-muted"></i>
                            <span class="small text-muted font-weight-bold" style="font-size: 10px; text-transform: uppercase;">Revert disabled. File has moved to {{ $currentStatusDisplay }}.</span>
                        </div>
                    @endif
                 </div>
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
