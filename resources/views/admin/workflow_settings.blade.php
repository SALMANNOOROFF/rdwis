@extends('welcome')

@section('content')
<div class="content-wrapper" style="background: #f1f5f9; min-height: 100vh; padding: 24px;">
    <div class="container-fluid max-w-7xl mx-auto">
        
        {{-- Page Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3" style="border-bottom: 1px solid #cbd5e1;">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px; letter-spacing: 0.5px;">
                        <i class="fas fa-crown mr-1"></i> SUPERADMIN / GOD MODE
                    </span>
                    <span class="badge badge-success text-white px-2 py-1 font-weight-bold" style="font-size: 11px;">
                        <i class="fas fa-shopping-cart mr-1"></i> PURCHASE WORKFLOWS
                    </span>
                </div>
                <h2 class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 26px; letter-spacing: 0.5px;">
                    PURCHASE CASES WORKFLOW & ROUTING MATRIX
                </h2>
                <p class="text-muted small mb-0 mt-1">
                    Configure step-by-step forwarding pathways and return rules for Procurement Scrutiny (PS), Petty Cash (PT), and Running Bills (RB).
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.settings.financial') }}" class="btn btn-outline-secondary btn-sm font-weight-bold px-3 py-2" style="border-radius: 8px;">
                    ⬅ Financial & HR Limits
                </a>
            </div>
        </div>

        {{-- Top Floating AJAX Notification --}}
        <div id="ajaxToastNotification" class="alert alert-success border-0 shadow-lg d-none align-items-center mb-4 px-4 py-3" style="background: #ecfdf5; border-left: 4px solid #10b981 !important; border-radius: 8px; position: sticky; top: 15px; z-index: 1050; animation: fadeInDown 0.3s ease;">
            <i class="fas fa-check-circle text-success mr-3" style="font-size: 22px;"></i>
            <div>
                <h6 class="mb-0 text-success font-weight-bold rajdhani" style="font-size: 15px;">SETTINGS SAVED IN REAL-TIME</h6>
                <p class="mb-0 text-muted small" id="ajaxToastMessage">Purchase workflow routing matrix has been updated successfully without page reload.</p>
            </div>
        </div>

        {{-- Settings Form --}}
        <form action="{{ route('admin.settings.workflows.update') }}" method="POST" id="workflowSettingsForm">
            @csrf

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #dcfce7; color: #15803d;">
                            <i class="fas fa-route"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold text-dark rajdhani" style="font-size: 18px;">SELECT PURCHASE CASE TYPE TO CONFIGURE</h5>
                            <span class="text-muted small" style="font-size: 11px;">Configure procurement scrutiny loop or direct finance pathways</span>
                        </div>
                    </div>

                    {{-- Case Type Navigation Tabs (Only the 3 real types: PS, PT, RB) --}}
                    <ul class="nav nav-pills rajdhani font-weight-bold" id="workflowTypeTabs" role="tablist" style="gap: 8px;">
                        <li class="nav-item">
                            <a class="nav-link active px-3.5 py-2 rounded-lg" id="tab-flow-ps" data-toggle="pill" href="#flow-ps" role="tab" style="font-size: 13.5px; border: 1.5px solid #059669;">
                                <i class="fas fa-shopping-cart mr-1"></i> PS (PROCUREMENT STORE CASES)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3.5 py-2 rounded-lg" id="tab-flow-pt" data-toggle="pill" href="#flow-pt" role="tab" style="font-size: 13.5px; border: 1.5px solid #2563eb;">
                                <i class="fas fa-credit-card mr-1"></i> PT (PETTY CASH CASES)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3.5 py-2 rounded-lg" id="tab-flow-rb" data-toggle="pill" href="#flow-rb" role="tab" style="font-size: 13.5px; border: 1.5px solid #d97706;">
                                <i class="fas fa-file-invoice mr-1"></i> RB (RUNNING BILLS / SERVICES)
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <div class="tab-content" id="workflowTypeTabContent">
                        
                        {{-- 1. PS FLOW (Procurement Store & Tender Cases) --}}
                        <div class="tab-pane fade show active" id="flow-ps" role="tabpanel">
                            @include('admin._workflow_tab_content', [
                                'typeKey' => 'PS',
                                'typeTitle' => 'PS (Procurement Store & Scrutiny Cases)',
                                'typeDescription' => 'All tender, market quotation, RFQ generation, and IT letter cases. Involves collaborative scrutiny loop between Division and Director Procurement (DProc) before releasing to Finance.',
                                'matrix' => $psMatrix,
                                'stageOptions' => $stageOptions,
                                'availableCaseTypes' => $availableCaseTypes
                            ])
                        </div>

                        {{-- 2. PT FLOW (Petty Cash Cases) --}}
                        <div class="tab-pane fade" id="flow-pt" role="tabpanel">
                            @include('admin._workflow_tab_content', [
                                'typeKey' => 'PT',
                                'typeTitle' => 'PT (Petty Cash Purchases)',
                                'typeDescription' => 'Local day-to-day spot purchases, urgent office consumables, and imprest fund reimbursements. Direct flow from Division straight to Director Finance (Bypasses DProc).',
                                'matrix' => $ptMatrix,
                                'stageOptions' => $stageOptions,
                                'availableCaseTypes' => $availableCaseTypes
                            ])
                        </div>

                        {{-- 3. RB FLOW (Running Bills & Maintenance) --}}
                        <div class="tab-pane fade" id="flow-rb" role="tabpanel">
                            @include('admin._workflow_tab_content', [
                                'typeKey' => 'RB',
                                'typeTitle' => 'RB (Running Bills & Services Flow)',
                                'typeDescription' => 'Monthly recurring invoices, civil/electrical maintenance works, utility bills, and IT software service contracts. Direct flow from Division straight to Director Finance (Bypasses DProc).',
                                'matrix' => $rbMatrix,
                                'stageOptions' => $stageOptions,
                                'availableCaseTypes' => $availableCaseTypes
                            ])
                        </div>

                    </div>
                </div>
            </div>

            {{-- Action Save Sticky Footer --}}
            <div class="card border-0 shadow-sm sticky-bottom" style="border-radius: 12px; background: #ffffff; position: sticky; bottom: 20px; z-index: 100; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <span class="text-dark font-weight-bold small rajdhani" style="font-size: 13.5px;">
                            ALL ROUTING PATHWAYS WILL UPDATE DYNAMICALLY IN REAL-TIME VIA AJAX WITHOUT RELOAD.
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" id="btnSaveWorkflows" class="btn btn-success font-weight-bold px-4 py-2 rajdhani" style="border-radius: 8px; font-size: 15px; letter-spacing: 0.5px; box-shadow: 0 3px 8px rgba(16, 185, 129, 0.35);">
                            <i class="fas fa-save mr-1.5" id="btnSaveIcon"></i> <span id="btnSaveText">SAVE WORKFLOW ROUTES</span>
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </div>
</div>

{{-- Interactive Flowchart & AJAX Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Live Flowchart Badge Updates when Select changes
    const selects = document.querySelectorAll('.fc-select');
    selects.forEach(sel => {
        sel.addEventListener('change', function() {
            const stage = this.dataset.stage;
            const type = this.dataset.type;
            const role = this.dataset.role; // 'fwd' or 'ret'
            const val = this.value;

            if (role === 'fwd') {
                const badge = document.getElementById(`fc_fwd_${stage}_${type}`);
                if (badge) {
                    badge.innerText = `➔ ${val}`;
                    badge.classList.add('pulse-badge');
                    setTimeout(() => badge.classList.remove('pulse-badge'), 600);
                }
            } else if (role === 'ret') {
                const badge = document.getElementById(`fc_ret_${stage}_${type}`);
                if (badge) {
                    badge.innerText = `⬅ ${val}`;
                    badge.classList.add('pulse-badge');
                    setTimeout(() => badge.classList.remove('pulse-badge'), 600);
                }
            }
        });
    });

    // 2. AJAX Form Submission without Page Reload
    const form = document.getElementById('workflowSettingsForm');
    const btnSave = document.getElementById('btnSaveWorkflows');
    const btnIcon = document.getElementById('btnSaveIcon');
    const btnText = document.getElementById('btnSaveText');
    const toast = document.getElementById('ajaxToastNotification');
    const toastMsg = document.getElementById('ajaxToastMessage');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            btnSave.disabled = true;
            btnIcon.className = 'fas fa-spinner fa-spin mr-1.5';
            btnText.innerText = 'SAVING CHANGES...';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                btnSave.disabled = false;
                btnIcon.className = 'fas fa-save mr-1.5';
                btnText.innerText = 'SAVE WORKFLOW ROUTES';

                if (data.success) {
                    toastMsg.innerText = data.message || 'Workflow routes and return pathways saved successfully in real-time!';
                    toast.classList.remove('d-none');
                    toast.classList.add('d-flex');
                    
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    setTimeout(() => {
                        toast.classList.remove('d-flex');
                        toast.classList.add('d-none');
                    }, 4000);
                }
            })
            .catch(err => {
                btnSave.disabled = false;
                btnIcon.className = 'fas fa-save mr-1.5';
                btnText.innerText = 'SAVE WORKFLOW ROUTES';
                alert('An error occurred while saving settings via AJAX. Please try again.');
            });
        });
    }

});
</script>

<style>
.pulse-badge {
    animation: badgePulse 0.6s ease;
}
@keyframes badgePulse {
    0% { transform: scale(1); filter: brightness(1); }
    50% { transform: scale(1.2); filter: brightness(1.5); }
    100% { transform: scale(1); filter: brightness(1); }
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endsection
