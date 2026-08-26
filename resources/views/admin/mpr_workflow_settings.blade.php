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
                    <span class="badge badge-info text-white px-2 py-1 font-weight-bold" style="font-size: 11px;">
                        <i class="fas fa-file-alt mr-1"></i> MPR WORKFLOWS
                    </span>
                </div>
                <h2 class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 26px; letter-spacing: 0.5px;">
                    WORKFLOW ROUTES: MONTHLY PROGRESS REPORTS (MPR)
                </h2>
                <p class="text-muted small mb-0 mt-1">
                    Configure the project progress reporting pipeline from Project Investigator (PI) to Staff Officer R&D (SO R&D) and optional MD Office review.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.settings.workflows') }}" class="btn btn-outline-secondary btn-sm font-weight-bold px-3 py-2" style="border-radius: 8px;">
                    ⬅ Purchase Workflows
                </a>
            </div>
        </div>

        {{-- Top Floating AJAX Notification --}}
        <div id="ajaxToastNotification" class="alert alert-success border-0 shadow-lg d-none align-items-center mb-4 px-4 py-3" style="background: #ecfdf5; border-left: 4px solid #10b981 !important; border-radius: 8px; position: sticky; top: 15px; z-index: 1050; animation: fadeInDown 0.3s ease;">
            <i class="fas fa-check-circle text-success mr-3" style="font-size: 22px;"></i>
            <div>
                <h6 class="mb-0 text-success font-weight-bold rajdhani" style="font-size: 15px;">SETTINGS SAVED IN REAL-TIME</h6>
                <p class="mb-0 text-muted small" id="ajaxToastMessage">MPR workflow routes and return pathways have been updated successfully.</p>
            </div>
        </div>

        @php
            $fChain = $matrix['forward_chain'] ?? [];
            $rChain = $matrix['return_chain'] ?? [];
            $returnPolicy = $matrix['return_policy'] ?? 'historical';

            $poNext = $fChain['ProjectOfficer']['next'] ?? 'SORD';
            $sordNext = $fChain['SORD']['next'] ?? 'Accepted';
            $mdNext = $fChain['MD']['next'] ?? 'Accepted';

            $sordRet = $rChain['SORD'] ?? 'ProjectOfficer';
            $mdRet = $rChain['MD'] ?? 'SORD';
        @endphp

        {{-- 1. LIVE INTERACTIVE MPR FLOWCHART DIAGRAM WITH HIGH CONTRAST COLORS --}}
        <div class="flowchart-container mb-4 p-4 rounded-xl" style="background: linear-gradient(135deg, #090d16 0%, #0f172a 50%, #1e293b 100%) !important; border: 2px solid #334155 !important; border-radius: 14px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5" style="border-bottom: 1px solid rgba(255,255,255,0.12);">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge px-3 py-1.5 font-weight-bold rajdhani" style="background: #0284c7 !important; color: #ffffff !important; font-size: 13px; letter-spacing: 0.5px; border-radius: 6px;">
                        <i class="fas fa-project-diagram mr-1"></i> LIVE MPR PIPELINE FLOWCHART
                    </span>
                    <span class="font-weight-bold rajdhani" style="color: #38bdf8 !important; font-size: 17px; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                        PROJECT INVESTIGATOR ➔ SO R&D ➔ MD (R&D) LIFECYCLE
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="small font-weight-bold rajdhani d-flex align-items-center px-2 py-1 rounded" style="color: #34d399 !important; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3);">
                        <span class="d-inline-block rounded-circle mr-1.5" style="width: 8px; height: 8px; background: #34d399;"></span> FORWARD PATH
                    </span>
                    <span class="small font-weight-bold rajdhani d-flex align-items-center px-2 py-1 rounded" style="color: #fbbf24 !important; background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3);">
                        <span class="d-inline-block rounded-circle mr-1.5" style="width: 8px; height: 8px; background: #fbbf24;"></span> RETURN PATH
                    </span>
                </div>
            </div>

            {{-- Flowchart Nodes --}}
            <div class="flowchart-nodes-wrapper d-flex align-items-center justify-content-between flex-wrap gap-2 py-2">
                {{-- Node 1: Project Investigator --}}
                <div class="flow-node-card text-center p-3 rounded-lg" style="background: #1e293b !important; border: 1.5px solid #475569 !important; border-radius: 10px; min-width: 170px; flex: 1; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                    <span class="badge mb-1.5 px-2 py-0.5" style="background: #475569 !important; color: #f8fafc !important; font-size: 10px; font-weight: 700;">STEP 1</span>
                    <div class="rajdhani" style="color: #ffffff !important; font-size: 15px; font-weight: 700; letter-spacing: 0.3px;">Project Investigator (PI)</div>
                    <div style="color: #94a3b8 !important; font-size: 11px; font-weight: 500;">Division Project Officer</div>
                    <div class="mt-2 pt-2" style="border-top: 1px dashed rgba(255,255,255,0.15);">
                        <span class="badge px-2 py-1 font-weight-bold" style="background: #059669 !important; color: #ffffff !important; font-size: 10px;" id="fc_fwd_po">➔ {{ $poNext }}</span>
                    </div>
                </div>

                <div class="flow-connector px-1 text-center" style="color: #34d399 !important;">
                    <i class="fas fa-long-arrow-alt-right" style="font-size: 24px; filter: drop-shadow(0 0 6px rgba(52,211,153,0.6));"></i>
                </div>

                {{-- Node 2: SO R&D --}}
                <div class="flow-node-card text-center p-3 rounded-lg" style="background: #1e293b !important; border: 1.5px solid #0284c7 !important; border-radius: 10px; min-width: 170px; flex: 1; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                    <span class="badge mb-1.5 px-2 py-0.5" style="background: #0284c7 !important; color: #ffffff !important; font-size: 10px; font-weight: 700;">STEP 2 (CURRENT STANDARD)</span>
                    <div class="rajdhani" style="color: #ffffff !important; font-size: 15px; font-weight: 700; letter-spacing: 0.3px;">Staff Officer R&D</div>
                    <div style="color: #94a3b8 !important; font-size: 11px; font-weight: 500;">Coordination & Scrutiny</div>
                    <div class="mt-2 pt-2 d-flex justify-content-center gap-1.5" style="border-top: 1px dashed rgba(255,255,255,0.15);">
                        <span class="badge px-2 py-1 font-weight-bold" style="background: #059669 !important; color: #ffffff !important; font-size: 10px;" id="fc_fwd_sord">➔ {{ $sordNext }}</span>
                        <span class="badge px-2 py-1 font-weight-bold" style="background: #d97706 !important; color: #ffffff !important; font-size: 10px;" id="fc_ret_sord">⬅ {{ $sordRet }}</span>
                    </div>
                </div>

                <div class="flow-connector px-1 text-center" style="color: #34d399 !important;">
                    <i class="fas fa-long-arrow-alt-right" style="font-size: 24px; filter: drop-shadow(0 0 6px rgba(52,211,153,0.6));"></i>
                </div>

                {{-- Node 3: MD (R&D) Office (Optional / Extension) --}}
                <div class="flow-node-card text-center p-3 rounded-lg" style="background: #1e293b !important; border: 1.5px solid #d97706 !important; border-radius: 10px; min-width: 170px; flex: 1; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                    <span class="badge mb-1.5 px-2 py-0.5" style="background: #d97706 !important; color: #ffffff !important; font-size: 10px; font-weight: 700;">STEP 3 (EXECUTIVE OPTION)</span>
                    <div class="rajdhani" style="color: #ffffff !important; font-size: 15px; font-weight: 700; letter-spacing: 0.3px;">MD (R&D) Office</div>
                    <div style="color: #94a3b8 !important; font-size: 11px; font-weight: 500;">Executive Progress Review</div>
                    <div class="mt-2 pt-2 d-flex justify-content-center gap-1.5" style="border-top: 1px dashed rgba(255,255,255,0.15);">
                        <span class="badge px-2 py-1 font-weight-bold" style="background: #059669 !important; color: #ffffff !important; font-size: 10px;" id="fc_fwd_md">➔ {{ $mdNext }}</span>
                        <span class="badge px-2 py-1 font-weight-bold" style="background: #d97706 !important; color: #ffffff !important; font-size: 10px;" id="fc_ret_md">⬅ {{ $mdRet }}</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- MPR Workflow Form --}}
        <form action="{{ route('admin.settings.workflows_mpr.update') }}" method="POST" id="mprWorkflowForm">
            @csrf

            <div class="row">
                {{-- STEP 1: Project Investigator --}}
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                        <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                            <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">1. Project Investigator (PI)</span>
                            <span class="badge badge-secondary" style="font-size: 10px;">Initiator</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">SUBMIT / FORWARD TARGET:</label>
                                <select name="forward[ProjectOfficer]" id="sel_fwd_po" class="form-control form-control-sm font-weight-bold text-dark rajdhani">
                                    <option value="SORD" {{ $poNext === 'SORD' ? 'selected' : '' }}>➔ Staff Officer R&D (SO R&D)</option>
                                    <option value="MD" {{ $poNext === 'MD' ? 'selected' : '' }}>➔ MD (R&D) Office Direct</option>
                                </select>
                            </div>
                            <small class="text-muted font-italic d-block mt-2" style="font-size: 11px;">
                                Project Investigator submits monthly milestones & progress to SO R&D.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Staff Officer R&D --}}
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                        <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                            <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">2. Staff Officer R&D</span>
                            <span class="badge badge-info" style="font-size: 10px;">Coordinator</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">ON REVIEW ACTION / FORWARD:</label>
                                <select name="forward[SORD]" id="sel_fwd_sord" class="form-control form-control-sm font-weight-bold text-dark rajdhani">
                                    <option value="Accepted" {{ $sordNext === 'Accepted' ? 'selected' : '' }}>➔ Accepted & Finalized (Current Active)</option>
                                    <option value="MD" {{ $sordNext === 'MD' ? 'selected' : '' }}>➔ Forward to MD (R&D) Office</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">RETURN TARGET (IF REVISION NEEDED):</label>
                                <select name="return[SORD]" id="sel_ret_sord" class="form-control form-control-sm text-dark rajdhani">
                                    <option value="ProjectOfficer" {{ $sordRet === 'ProjectOfficer' ? 'selected' : '' }}>⬅ Project Investigator (PI)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: MD (R&D) Office --}}
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 10px; background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                        <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex align-items-center justify-content-between">
                            <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">3. MD (R&D) Office</span>
                            <span class="badge badge-warning text-dark" style="font-size: 10px;">Executive Review</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">FINAL ACTION:</label>
                                <select name="forward[MD]" id="sel_fwd_md" class="form-control form-control-sm font-weight-bold text-dark rajdhani">
                                    <option value="Accepted" {{ $mdNext === 'Accepted' ? 'selected' : '' }}>➔ Final Executive Acceptance & Archive</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold text-muted mb-1 rajdhani" style="font-size: 12px;">DEFAULT RETURN TARGET:</label>
                                <select name="return[MD]" id="sel_ret_md" class="form-control form-control-sm text-dark rajdhani">
                                    <option value="SORD" {{ $mdRet === 'SORD' ? 'selected' : '' }}>⬅ Staff Officer R&D</option>
                                    <option value="ProjectOfficer" {{ $mdRet === 'ProjectOfficer' ? 'selected' : '' }}>⬅ Project Investigator (PI)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Return Policy & DG Return --}}
            <div class="p-3 rounded mb-4" style="background: #ffffff; border: 1.5px solid #cbd5e1;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="font-weight-bold text-dark mb-0 rajdhani" style="font-size: 14px;">
                            <i class="fas fa-undo text-warning mr-1"></i> MPR RETURN SELECTION POLICY
                        </h6>
                        <span class="text-muted small">Choose how reviewing officers select return targets for revisions</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="custom-control custom-radio">
                            <input type="radio" id="mprRetHist" name="return_policy" value="historical" class="custom-control-input" {{ $returnPolicy === 'historical' ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-dark rajdhani" for="mprRetHist" style="font-size: 13px; cursor: pointer;">
                                FLEXIBLE AUDIT TRAIL (SELECT ANY PAST OFFICER)
                            </label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="mprRetPrev" name="return_policy" value="previous" class="custom-control-input" {{ $returnPolicy === 'previous' ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold text-dark rajdhani" for="mprRetPrev" style="font-size: 13px; cursor: pointer;">
                                STRICT STEP-BY-STEP ONLY
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Save Sticky Footer --}}
            <div class="card border-0 shadow-sm sticky-bottom" style="border-radius: 12px; background: #ffffff; position: sticky; bottom: 20px; z-index: 100; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-info text-white d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <span class="text-dark font-weight-bold small rajdhani" style="font-size: 13.5px;">
                            MPR WORKFLOW PATHWAYS WILL UPDATE DYNAMICALLY IN REAL-TIME VIA AJAX.
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" id="btnSaveMpr" class="btn btn-info text-white font-weight-bold px-4 py-2 rajdhani" style="border-radius: 8px; font-size: 15px; letter-spacing: 0.5px; box-shadow: 0 3px 8px rgba(14, 165, 233, 0.35);">
                            <i class="fas fa-save mr-1.5" id="btnMprIcon"></i> <span id="btnMprText">SAVE MPR WORKFLOW ROUTES</span>
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
    // Dynamic Flowchart Updates
    const bindings = [
        { sel: 'sel_fwd_po', badge: 'fc_fwd_po', prefix: '➔ ' },
        { sel: 'sel_fwd_sord', badge: 'fc_fwd_sord', prefix: '➔ ' },
        { sel: 'sel_ret_sord', badge: 'fc_ret_sord', prefix: '⬅ ' },
        { sel: 'sel_fwd_md', badge: 'fc_fwd_md', prefix: '➔ ' },
        { sel: 'sel_ret_md', badge: 'fc_ret_md', prefix: '⬅ ' },
    ];

    bindings.forEach(b => {
        const el = document.getElementById(b.sel);
        const badge = document.getElementById(b.badge);
        if (el && badge) {
            el.addEventListener('change', function() {
                badge.innerText = b.prefix + this.value;
                badge.classList.add('pulse-badge');
                setTimeout(() => badge.classList.remove('pulse-badge'), 600);
            });
        }
    });

    // AJAX Submission
    const form = document.getElementById('mprWorkflowForm');
    const btn = document.getElementById('btnSaveMpr');
    const icon = document.getElementById('btnMprIcon');
    const txt = document.getElementById('btnMprText');
    const toast = document.getElementById('ajaxToastNotification');
    const toastMsg = document.getElementById('ajaxToastMessage');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            btn.disabled = true;
            icon.className = 'fas fa-spinner fa-spin mr-1.5';
            txt.innerText = 'SAVING...';

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                icon.className = 'fas fa-save mr-1.5';
                txt.innerText = 'SAVE MPR WORKFLOW ROUTES';
                if (data.success) {
                    toastMsg.innerText = data.message || 'MPR workflow routes saved successfully in real-time!';
                    toast.classList.remove('d-none');
                    toast.classList.add('d-flex');
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    setTimeout(() => { toast.classList.remove('d-flex'); toast.classList.add('d-none'); }, 4000);
                }
            })
            .catch(err => {
                btn.disabled = false;
                icon.className = 'fas fa-save mr-1.5';
                txt.innerText = 'SAVE MPR WORKFLOW ROUTES';
                alert('Error saving MPR workflows.');
            });
        });
    }
});
</script>

<style>
.pulse-badge { animation: badgePulse 0.6s ease; }
@keyframes badgePulse { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
@keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection
