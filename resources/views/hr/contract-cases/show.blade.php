@extends('welcome')

@section('content')
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<style>
    .contract-page-wrapper {
        background-color: var(--rd-bg);
        min-height: calc(100vh - 60px);
        padding: 2rem;
        font-family: 'Outfit', 'Inter', sans-serif;
    }
    
    .page-title {
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--rd-text1);
    }

    .btn-back-link {
        background: #FFFFFF;
        border: 1px solid var(--rd-border);
        color: var(--rd-text2);
        font-weight: 600;
        border-radius: 8px;
        padding: 0.5rem 1.2rem;
        transition: all 0.2s ease;
        text-decoration: none;
        box-shadow: var(--rd-shadow-sm);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-back-link:hover {
        background: var(--rd-neutral-50);
        border-color: var(--rd-neutral-400);
        color: var(--rd-text1);
    }

    .clean-card {
        background-color: #FFFFFF;
        border: 1px solid var(--rd-border);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(41, 40, 36, 0.04);
    }
    
    .clean-card-header {
        background-color: #FFFFFF;
        padding: 1.1rem 1.5rem;
        border-bottom: 1.5px solid var(--rd-neutral-200);
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--rd-text1);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Workflow Pipeline Stepper */
    .pipeline-stepper-card {
        background-color: #FFFFFF;
        border: 1px solid var(--rd-border);
        border-radius: 12px;
        padding: 1.2rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(41, 40, 36, 0.04);
    }
    .stepper-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow-x: auto;
        padding: 10px 0;
    }
    .stepper-container::before {
        content: '';
        position: absolute;
        top: 24px;
        left: 30px;
        right: 30px;
        height: 3px;
        background: var(--rd-neutral-200);
        z-index: 1;
    }
    .step-item {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        min-width: 90px;
    }
    .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #FFFFFF;
        border: 2.5px solid var(--rd-neutral-400);
        color: var(--rd-text3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
        transition: all 0.2s ease;
        margin-bottom: 6px;
    }
    .step-item.completed .step-circle {
        background: var(--rd-primary-600);
        border-color: var(--rd-primary-600);
        color: #FFFFFF;
    }
    .step-item.active .step-circle {
        background: #FFFFFF;
        border-color: var(--rd-primary-600);
        color: var(--rd-primary-600);
        box-shadow: 0 0 0 4px rgba(95, 120, 88, 0.2);
        transform: scale(1.1);
    }
    .step-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--rd-text3);
    }
    .step-item.active .step-label {
        color: var(--rd-primary-600);
        font-weight: 700;
    }
    .step-item.completed .step-label {
        color: var(--rd-text1);
    }

    /* Structured Data Grid */
    .data-grid-table {
        display: grid;
        grid-template-columns: 140px 1fr 140px 1fr;
        border-bottom: 1px solid var(--rd-neutral-200);
    }
    .data-grid-table:last-child {
        border-bottom: none;
    }
    .data-grid-cell {
        padding: 1rem 1.2rem;
        font-size: 0.88rem;
        border-right: 1px solid var(--rd-neutral-200);
        display: flex;
        align-items: center;
    }
    .data-grid-cell:last-child {
        border-right: none;
    }
    .data-grid-label {
        background: var(--rd-neutral-50);
        color: var(--rd-text3);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .data-grid-value {
        color: var(--rd-text1);
        font-weight: 600;
    }

    .rd-textarea {
        background-color: #FFFFFF;
        border: 1.5px solid var(--rd-border2);
        color: var(--rd-text1);
        border-radius: 8px;
        padding: 0.65rem 1rem;
        width: 100%;
        font-size: 0.9rem;
    }
    .rd-textarea:focus {
        outline: none;
        border-color: var(--rd-primary-600);
        box-shadow: 0 0 0 3px rgba(95, 120, 88, 0.15);
    }

    .btn-action-forward {
        background: var(--rd-primary-600);
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-weight: 700;
        width: 100%;
        margin-bottom: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(95, 120, 88, 0.25);
        transition: all 0.2s;
    }
    .btn-action-forward:hover {
        background: var(--rd-primary-700);
        transform: translateY(-1px);
    }

    .btn-action-return {
        background: #F59E0B;
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-weight: 700;
        width: 100%;
        margin-bottom: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
        transition: all 0.2s;
    }
    .btn-action-return:hover {
        background: #D97706;
    }

    .btn-action-reject {
        background: transparent;
        border: 1.5px solid #EF4444;
        color: #EF4444;
        border-radius: 8px;
        padding: 0.65rem 1rem;
        font-weight: 700;
        width: 100%;
        transition: all 0.2s;
    }
    .btn-action-reject:hover {
        background: rgba(239, 68, 68, 0.08);
    }

    .btn-action-fulfill {
        background: #10B981;
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 0.85rem 1.2rem;
        font-weight: 800;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
        transition: all 0.2s;
    }
    .btn-action-fulfill:hover {
        background: #059669;
        transform: translateY(-1px);
    }

    .status-badge-chip {
        background-color: var(--rd-neutral-100);
        color: var(--rd-text2);
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 700;
        border: 1px solid var(--rd-border);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>

<div class="content-wrapper" style="background-color: var(--rd-bg);">
    <section class="content">
        <div class="contract-page-wrapper">
            
            <!-- Top Header -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h1 class="page-title mb-1">
                        HR Contract Scrutiny &bull; Case #{{ $case->ctc_id }}
                    </h1>
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                        <span class="badge badge-primary px-3 py-1 font-weight-bold" style="border-radius: 20px; font-size: 11px;">
                            <i class="fas fa-tag mr-1"></i> {{ strtoupper($case->ctc_type) }} CASE
                        </span>
                        {{-- Current Holder Stage (css_stage) --}}
                        <span class="badge badge-info px-3 py-1 font-weight-bold text-white shadow-sm" style="border-radius: 20px; font-size: 11.5px; background: #0284c7;" title="Current Substatus / Workflow Holder Stage">
                            <i class="fas fa-user-clock mr-1"></i> Current Holder: {{ $case->current_stage ?? $case->currentSubstatus->css_stage ?? 'HR' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('hr.contract-cases.index') }}" class="btn-back-link">
                    <i class="fas fa-arrow-left"></i> Back to HR Queue
                </a>
            </div>

            <!-- Workflow Progress Stepper -->
            @php
                $approvalService = app(\App\Services\ContractCaseApprovalService::class);
                $workflowSteps = $approvalService->getWorkflowSteps($case);
                $currStage = $case->current_stage ?? 'HR';
                
                $stepIds = array_column($workflowSteps, 'id');
                $currIndex = array_search($currStage, $stepIds);
                if ($currIndex === false) $currIndex = 1;
                $isFulfilled = ($case->ctc_status === 'Fulfilled' || $currStage === 'Fulfilled');
            @endphp
            <div class="pipeline-stepper-card">
                <div class="stepper-container">
                    @foreach($workflowSteps as $i => $s)
                        @php
                            if ($isFulfilled) {
                                $isDone = true;
                                $isActive = false;
                            } else {
                                $isDone = $currIndex > $i;
                                $isActive = $currIndex === $i;
                            }
                        @endphp
                        <div class="step-item {{ $isDone ? 'completed' : '' }} {{ $isActive ? 'active' : '' }}">
                            <div class="step-circle">
                                @if($isDone)
                                    <i class="fas fa-check"></i>
                                @else
                                    <i class="fas {{ $s['icon'] }}"></i>
                                @endif
                            </div>
                            <span class="step-label">{{ $s['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="row">
                <!-- Left Main Details Column -->
                <div class="col-md-8">
                    <!-- Terms Summary Card -->
                    <div class="clean-card">
                        <div class="clean-card-header">
                            <span><i class="fas fa-file-contract mr-2 text-primary"></i> Contract Case Terms & Verification</span>
                            <span class="badge badge-light border font-weight-bold px-3 py-1">{{ $case->ctc_status }}</span>
                        </div>
                        
                        <div class="data-grid-table">
                            <div class="data-grid-cell data-grid-label">Case Type</div>
                            <div class="data-grid-cell data-grid-value text-primary">
                                @if($case->ctc_type == 'Hg') Fresh Hiring (Hg)
                                @elseif($case->ctc_type == 'Ce') Contract Extension (Ce)
                                @elseif($case->ctc_type == 'Cr') Contract Renewal (Cr)
                                @elseif($case->ctc_type == 'Rh') Rehiring (Rh)
                                @endif
                            </div>
                            <div class="data-grid-cell data-grid-label">Division</div>
                            <div class="data-grid-cell data-grid-value font-weight-bold text-dark">{{ $case->division_name }}</div>
                        </div>

                        <div class="data-grid-table">
                            <div class="data-grid-cell data-grid-label">Candidate Name</div>
                            <div class="data-grid-cell data-grid-value font-weight-bold text-dark">{{ $case->ctc_empnamecomp }}</div>
                            <div class="data-grid-cell data-grid-label">Employee ID</div>
                            <div class="data-grid-cell data-grid-value">{{ $case->ctc_emp_id ?: 'New Candidate' }}</div>
                        </div>

                        <div class="data-grid-table">
                            <div class="data-grid-cell data-grid-label">Designation</div>
                            <div class="data-grid-cell data-grid-value">{{ $case->ctc_newjobtitle }}</div>
                            <div class="data-grid-cell data-grid-label">Grade / Scale</div>
                            <div class="data-grid-cell data-grid-value">{{ $case->ctc_newgrade }}</div>
                        </div>

                        <div class="data-grid-table">
                            <div class="data-grid-cell data-grid-label">Proposed Salary</div>
                            <div class="data-grid-cell data-grid-value text-success font-weight-bold" style="font-size: 1rem;">
                                Rs. {{ number_format($case->ctc_newsalary) }} / mo
                            </div>
                            <div class="data-grid-cell data-grid-label">Total Case Price</div>
                            <div class="data-grid-cell data-grid-value text-primary font-weight-bold" style="font-size: 1rem;">
                                Rs. {{ number_format((float)($case->ctc_price ?? 0)) }}
                            </div>
                        </div>

                        <div class="data-grid-table">
                            <div class="data-grid-cell data-grid-label">Start Date</div>
                            <div class="data-grid-cell data-grid-value">{{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M Y') : 'N/A' }}</div>
                            <div class="data-grid-cell data-grid-label">End Date</div>
                            <div class="data-grid-cell data-grid-value">{{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M Y') : 'N/A' }}</div>
                        </div>

                        @if($case->ctc_newprob > 0)
                            <div class="data-grid-table">
                                <div class="data-grid-cell data-grid-label">Probation Period</div>
                                <div class="data-grid-cell data-grid-value">{{ $case->ctc_newprob }} Months</div>
                                <div class="data-grid-cell data-grid-label">Probation Salary</div>
                                <div class="data-grid-cell data-grid-value">Rs. {{ number_format((float)($case->ctc_newprobsal ?: $case->ctc_newsalary)) }}</div>
                            </div>
                        @endif

                        @if($case->ctc_terminremarks)
                            <div class="p-3 border-top" style="background: #FFFBEB; border-color: var(--rd-neutral-200) !important;">
                                <span class="text-warning font-weight-bold d-block mb-1"><i class="fas fa-info-circle mr-1"></i> Extension / Early Termination Remarks:</span>
                                <p class="text-dark mb-0 font-weight-500" style="font-size: 0.9rem;">{{ $case->ctc_terminremarks }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Project Plan Breakdown Table -->
                    @if($case->casePlans->isNotEmpty())
                        <div class="clean-card">
                            <div class="clean-card-header">
                                <span><i class="fas fa-project-diagram mr-2 text-primary"></i> Project Head Allocation Plan</span>
                                <span class="badge badge-secondary px-2 py-1 font-weight-bold">{{ $case->casePlans->count() }} Months</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size: 0.88rem;">
                                    <thead style="background: var(--rd-neutral-50);">
                                        <tr>
                                            <th class="text-muted font-weight-bold" style="border: none;">#</th>
                                            <th class="text-muted font-weight-bold" style="border: none;">Month Period</th>
                                            <th class="text-muted font-weight-bold" style="border: none;">Project Allocation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($case->casePlans as $idx => $cp)
                                            <tr>
                                                <td class="font-weight-600 text-muted">{{ $idx + 1 }}</td>
                                                <td class="font-weight-600">{{ \Carbon\Carbon::parse($cp->ccp_startdt)->format('d M Y') }} - {{ \Carbon\Carbon::parse($cp->ccp_enddt)->format('d M Y') }}</td>
                                                <td>
                                                    @if($cp->project)
                                                        <span class="badge badge-primary px-2 py-1">{{ $cp->project->prj_code }}</span> <span class="font-weight-600 ml-1">{{ $cp->project->prj_title }}</span>
                                                    @else
                                                        <span class="text-muted font-weight-500">Core / Non-Project</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @include('partials.attachments_widget', [
                        'module' => 'ctc',
                        'objectId' => $case->ctc_id,
                        'title' => 'Scrutiny Attachments & Documents',
                        'defaultSlots' => ['CV', 'Approval', 'Form', 'Minute'],
                        'attachments' => $case->attachments,
                        'canEdit' => in_array($case->current_stage, ['HR']),
                    ])
                </div>

                <!-- Right Action Column -->
                <div class="col-md-4">
                    @if($case->current_stage === 'HR')
                        <!-- HR Scrutiny Panel -->
                        <div class="clean-card" style="border: 1.5px solid var(--rd-primary-600); box-shadow: 0 4px 16px rgba(95, 120, 88, 0.12);">
                            <div class="clean-card-header bg-light">
                                <span><i class="fas fa-tasks mr-2 text-primary"></i> HR Operations Scrutiny</span>
                            </div>
                            <div class="p-4">
                                <div class="form-group mb-3">
                                    <label class="rd-form-label font-weight-bold">Scrutiny Remarks <span class="text-danger">*</span></label>
                                    <textarea id="hrRemarks" class="rd-textarea" rows="3" placeholder="Enter HR scrutiny notes, grade validation, and duration clearance..."></textarea>
                                </div>
                                <button type="button" class="btn-action-forward" id="btn-forward-fin">
                                    <i class="fas fa-arrow-right"></i> Forward to Finance
                                </button>
                                <button type="button" class="btn-action-return" id="btn-return-div">
                                    <i class="fas fa-undo"></i> Return to Division
                                </button>
                                <button type="button" class="btn-action-reject" id="btn-reject-case">
                                    <i class="fas fa-times-circle mr-1"></i> Reject Case
                                </button>
                            </div>
                        </div>
                    @elseif($case->current_stage === 'Approved')
                        @php
                            $isHg = strtoupper(trim((string)$case->ctc_type)) === 'HG';
                            $hasEmployee = !empty($case->ctc_emp_id);
                        @endphp

                        @if($isHg && !$hasEmployee)
                            <!-- HR Add Employee Required Panel -->
                            <div class="clean-card" style="border: 2px solid var(--rd-primary-600); box-shadow: 0 4px 20px rgba(95, 120, 88, 0.18);">
                                <div class="clean-card-header" style="background: var(--rd-neutral-50); color: var(--rd-primary-700);">
                                    <span><i class="fas fa-user-plus mr-2 text-primary"></i> Step 1: Add Employee Record</span>
                                </div>
                                <div class="p-4">
                                    <div class="alert alert-info py-2 px-3 mb-3" style="font-size: 0.85rem; border-radius: 8px; background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF;">
                                        <i class="fas fa-info-circle mr-1"></i> <strong>Action Required:</strong> This is a new Hiring case. The employee record must be created and linked to the case before official contract fulfillment can occur.
                                    </div>
                                    <p class="text-muted small mb-3 font-weight-500">
                                        Candidate: <strong>{{ $case->ctc_empnamecomp }}</strong><br>
                                        Position: <strong>{{ $case->ctc_approvedjobtitle ?: $case->ctc_newjobtitle }}</strong> ({{ $case->ctc_approvedgrade ?: $case->ctc_newgrade }})
                                    </p>
                                    <button type="button" class="btn btn-primary btn-block font-weight-bold py-2 shadow-sm" id="btn-open-add-emp-modal" style="background: var(--rd-primary-600); border-color: var(--rd-primary-600); border-radius: 8px; font-size: 0.95rem;">
                                        <i class="fas fa-id-card mr-1"></i> Add Employee to System
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- HR Fulfillment Panel -->
                            <div class="clean-card" style="border: 2px solid #10B981; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15);">
                                <div class="clean-card-header" style="background: #ECFDF5; color: #065F46;">
                                    <span><i class="fas fa-award mr-2"></i> Contract Issuance & Fulfillment</span>
                                </div>
                                <div class="p-4">
                                    @if($hasEmployee)
                                        <div class="p-3 mb-3 rounded border" style="background: #F0FDF4; border-color: #BBF7D0 !important; font-size: 0.85rem;">
                                            <span class="text-success font-weight-bold d-block mb-1"><i class="fas fa-user-check mr-1"></i> Linked Employee:</span>
                                            <span class="font-weight-600 text-dark">{{ $case->ctc_empnamecomp }}</span> — <code class="font-weight-bold text-primary">{{ $case->ctc_emp_id }}</code>
                                        </div>
                                    @endif
                                    <p class="text-muted small mb-3 font-weight-500">
                                        Executive approval is complete. Issue official contract and register into active employee roster.
                                    </p>
                                    <div class="form-group mb-3">
                                        <label class="rd-form-label font-weight-bold">Formal Signing Date <span class="text-danger">*</span></label>
                                        <input type="date" id="signDate" class="form-control" style="border-radius: 8px;" value="{{ date('Y-m-d') }}">
                                    </div>
                                    <button type="button" class="btn-action-fulfill" id="btn-fulfill-contract">
                                        <i class="fas fa-check-double"></i> Fulfill & Issue Contract
                                    </button>
                                </div>
                            </div>
                        @endif
                    @else
                        <!-- General Status Card -->
                        <div class="clean-card">
                            <div class="clean-card-header">
                                <span><i class="fas fa-info-circle mr-2 text-primary"></i> Current Status & Holder</span>
                            </div>
                            <div class="p-4 text-center">
                                <div class="mb-2">
                                    <span class="badge badge-info px-3 py-1.5 font-weight-bold text-white" style="font-size: 13px; border-radius: 6px; background: #0284c7;">
                                        <i class="fas fa-user-clock mr-1"></i> Current Holder: {{ $case->current_stage ?? $case->currentSubstatus->css_stage ?? 'In Review' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Scrutiny Remarks Trail -->
                    @if($case->remarksHistory->isNotEmpty())
                        <div class="clean-card">
                            <div class="clean-card-header">
                                <span><i class="fas fa-history mr-2 text-primary"></i> Scrutiny Trail</span>
                            </div>
                            <div class="p-3" style="max-height: 300px; overflow-y: auto;">
                                @foreach($case->remarksHistory as $rm)
                                    <div class="mb-3 pb-2 border-bottom" style="border-color: var(--rd-neutral-200) !important; font-size: 0.85rem;">
                                        <div class="d-flex justify-content-between align-items-center text-muted small mb-1">
                                            <strong class="text-dark">{{ $rm->crr_username }} ({{ $rm->crr_status }})</strong>
                                            <span>{{ \Carbon\Carbon::parse($rm->crr_dtg)->format('d M H:i') }}</span>
                                        </div>
                                        <p class="text-dark mb-0 font-weight-500" style="line-height: 1.4;">{{ $rm->crr_remarks }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </section>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════ -->
<!-- ADD EMPLOYEE MODAL (Hg Hiring Flow)                                   -->
<!-- ═══════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: var(--rd-neutral-50); border-bottom: 1px solid var(--rd-neutral-200); border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-dark" id="addEmployeeModalLabel">
                    <i class="fas fa-user-plus mr-2 text-primary"></i> Add Employee Record (Hg Case #{{ $case->ctc_id }})
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddEmployee">
                @csrf
                <div class="modal-body p-4">
                    <!-- Live Employee ID Preview Card -->
                    <div class="p-3 mb-4 rounded border text-center" style="background: #F8FAFC; border-color: #E2E8F0 !important;">
                        <span class="text-muted small text-uppercase font-weight-bold d-block mb-1">Generated Employee ID</span>
                        <div class="d-flex align-items-center justify-content-center">
                            <span class="badge badge-dark px-3 py-2 font-weight-bold" style="font-size: 1.25rem; letter-spacing: 1px; font-family: monospace;" id="previewEmpIdBadge">--/--/--/----</span>
                        </div>
                        <small class="text-muted d-block mt-1">Format: <code>{Dept}-{JoinYear}-{JoinMonth}-{CnicSuffix}</code></small>
                    </div>

                    <!-- Department Fallback Warning -->
                    <div id="deptFallbackWarning" class="alert alert-warning py-2 px-3 mb-3 small d-none" style="border-radius: 8px; background: #FFFBEB; border: 1px solid #FDE68A; color: #92400E;">
                        <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Notice:</strong> Department number not yet confirmed for this department — using unit ID as fallback. Please verify the generated Employee ID before proceeding.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="rd-form-label font-weight-bold">Employee Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="emp_name" id="modalEmpName" class="form-control" style="border-radius: 8px;" value="{{ $case->ctc_empnamecomp }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="rd-form-label font-weight-bold">Department / Unit <span class="text-danger">*</span></label>
                            <select name="emp_unt_id" id="modalEmpUntId" class="form-control" style="border-radius: 8px;" required>
                                @php
                                    $targetUnitId = $case->ctc_approvedunt_id ?: ($case->ctc_newunt_id ?: $case->ctc_unt_id);
                                @endphp
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->unt_id }}" {{ $targetUnitId == $dept->unt_id ? 'selected' : '' }}>
                                        {{ $dept->unt_name }} ({{ $dept->unt_namesh }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="rd-form-label font-weight-bold">CNIC (with dashes) <span class="text-danger">*</span></label>
                            <input type="text" name="emp_cnic" id="modalEmpCnic" class="form-control" style="border-radius: 8px; font-family: monospace;" placeholder="42101-1234567-1" value="{{ $case->ctc_cnic }}" required>
                            <small class="text-muted">Standard format: <code>XXXXX-XXXXXXX-X</code></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="rd-form-label font-weight-bold">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" name="emp_joindt" id="modalEmpJoinDt" class="form-control" style="border-radius: 8px;" value="{{ $case->ctc_approvedstartdt ?: $case->ctc_newstartdt }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="rd-form-label font-weight-bold">Job Title</label>
                            <input type="text" name="emp_title" id="modalEmpTitle" class="form-control" style="border-radius: 8px;" value="{{ $case->ctc_approvedjobtitle ?: $case->ctc_newjobtitle }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="rd-form-label font-weight-bold">Grade / Rank</label>
                            <input type="text" name="emp_rank" id="modalEmpRank" class="form-control" style="border-radius: 8px;" value="{{ $case->ctc_approvedgrade ?: $case->ctc_newgrade }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: var(--rd-neutral-50); border-top: 1px solid var(--rd-neutral-200); border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary px-4 font-weight-bold" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 font-weight-bold" id="btnSaveEmployee" style="background: var(--rd-primary-600); border-color: var(--rd-primary-600); border-radius: 8px;">
                        <i class="fas fa-save mr-1"></i> Save & Link Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const deptMap = @json($deptMap ?? []);

    // ── Open Add Employee Modal ────────────────────────────────
    $('#btn-open-add-emp-modal').click(function() {
        updateEmpIdPreview();
        $('#addEmployeeModal').modal('show');
    });

    // ── Real-time Employee ID Live Preview ────────────────────
    function updateEmpIdPreview() {
        const unitId = parseInt($('#modalEmpUntId').val()) || 0;
        const joinDt = $('#modalEmpJoinDt').val() || '';
        const cnic = ($('#modalEmpCnic').val() || '').trim();

        // 1. Dept number
        let deptNum = '00';
        let isFallback = false;
        if (deptMap[unitId]) {
            deptNum = String(deptMap[unitId]).padStart(2, '0');
            $('#deptFallbackWarning').addClass('d-none');
        } else if (unitId > 0) {
            deptNum = String(unitId).padStart(2, '0').substring(0, 2);
            isFallback = true;
            $('#deptFallbackWarning').removeClass('d-none');
        } else {
            $('#deptFallbackWarning').addClass('d-none');
        }

        // 2. Year & Month
        let yy = 'YY';
        let mm = 'MM';
        if (joinDt && joinDt.length >= 7) {
            yy = joinDt.substring(2, 4);
            mm = joinDt.substring(5, 7);
        }

        // 3. CNIC Suffix: Mid(cnic, 11, 3) & Right(cnic, 1)
        let suffix = 'XXXX';
        if (/^\d{5}-\d{7}-\d{1}$/.test(cnic)) {
            const mid = cnic.substring(10, 13);
            const last = cnic.slice(-1);
            suffix = mid + last;
        } else {
            const digits = cnic.replace(/\D/g, '');
            if (digits.length === 13) {
                const mid = digits.substring(9, 12);
                const last = digits.slice(-1);
                suffix = mid + last;
            }
        }

        const previewId = `${deptNum}-${yy}-${mm}-${suffix}`;
        $('#previewEmpIdBadge').text(previewId);
    }

    $('#modalEmpUntId, #modalEmpJoinDt, #modalEmpCnic').on('input change', updateEmpIdPreview);

    // ── Save Employee via AJAX ────────────────────────────────
    $('#formAddEmployee').submit(function(e) {
        e.preventDefault();

        const cnic = $('#modalEmpCnic').val().trim();
        const joinDt = $('#modalEmpJoinDt').val();
        const name = $('#modalEmpName').val().trim();
        const unitId = $('#modalEmpUntId').val();

        if (!cnic || !joinDt || !name || !unitId) {
            Swal.fire('Required Fields', 'Please fill in all mandatory fields (Name, Department, CNIC, Joining Date).', 'warning');
            return;
        }

        const btn = $('#btnSaveEmployee');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            url: "{{ route('hr.contract-cases.add-employee', $case->ctc_id) }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(resp) {
                $('#addEmployeeModal').modal('hide');
                Swal.fire({
                    title: 'Employee Registered',
                    text: resp.message,
                    icon: 'success',
                    confirmButtonColor: '#5F7858',
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function(err) {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save & Link Employee');
                const msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Failed to create employee record.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // ── Forward to Finance ────────────────────────────────────
    $('#btn-forward-fin').click(function() {
        const rem = $('#hrRemarks').val();
        if (!rem) {
            Swal.fire('Required', 'Please provide scrutiny remarks before forwarding.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Forward to Finance',
            text: 'Are you sure you want to endorse and forward this case to Finance?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Forward',
            confirmButtonColor: '#5F7858',
            cancelButtonColor: '#94a3b8'
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: "{{ route('hr.contract-cases.forward', $case->ctc_id) }}",
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', remarks: rem },
                    success: function(resp) {
                        Swal.fire('Forwarded', resp.message, 'success').then(() => {
                            window.location.href = "{{ route('hr.contract-cases.index') }}";
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to forward case.', 'error');
                    }
                });
            }
        });
    });

    // ── Return to Division ────────────────────────────────────
    $('#btn-return-div').click(function() {
        const rem = $('#hrRemarks').val();
        if (!rem) {
            Swal.fire('Required', 'Please specify reasons for return in the remarks field.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Return to Division',
            text: 'Return this case to Division for revision?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Return',
            confirmButtonColor: '#F59E0B',
            cancelButtonColor: '#94a3b8'
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: "{{ route('hr.contract-cases.return', $case->ctc_id) }}",
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', remarks: rem },
                    success: function(resp) {
                        Swal.fire('Returned', resp.message, 'info').then(() => {
                            window.location.href = "{{ route('hr.contract-cases.index') }}";
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to return case.', 'error');
                    }
                });
            }
        });
    });

    // ── Reject Case ───────────────────────────────────────────
    $('#btn-reject-case').click(function() {
        const rem = $('#hrRemarks').val();
        if (!rem) {
            Swal.fire('Required', 'Please specify rejection reasons in the remarks field.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Reject Contract Case',
            text: 'This will close the case with status "Not Approved". Proceed?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reject',
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#94a3b8'
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: "{{ route('hr.contract-cases.reject', $case->ctc_id) }}",
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', remarks: rem },
                    success: function(resp) {
                        Swal.fire('Rejected', resp.message, 'error').then(() => {
                            window.location.href = "{{ route('hr.contract-cases.index') }}";
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to reject case.', 'error');
                    }
                });
            }
        });
    });

    // ── Fulfill Contract ──────────────────────────────────────
    $('#btn-fulfill-contract').click(function() {
        const signDt = $('#signDate').val();
        if (!signDt) {
            Swal.fire('Required', 'Please provide the formal signing date.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Fulfill & Issue Contract',
            text: 'This will generate the official HR contract and finalize the case. Proceed?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Issue Contract',
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#94a3b8'
        }).then((res) => {
            if (res.isConfirmed) {
                $.ajax({
                    url: "{{ route('hr.contract-cases.fulfill', $case->ctc_id) }}",
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', ctc_newsigndt: signDt },
                    success: function(resp) {
                        Swal.fire('Contract Issued', resp.message, 'success').then(() => {
                            window.location.href = "{{ route('hr.contract-cases.index') }}";
                        });
                    },
                    error: function(err) {
                        const msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Failed to fulfill contract.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
