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

    /* Action Card */
    .action-panel-card {
        border: 1.5px solid var(--rd-primary-600);
        box-shadow: 0 4px 16px rgba(95, 120, 88, 0.12);
    }

    .btn-release-action {
        background: var(--rd-primary-600);
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-weight: 700;
        width: 100%;
        margin-top: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(95, 120, 88, 0.25);
        transition: all 0.2s;
    }
    .btn-release-action:hover {
        background: var(--rd-primary-700);
        transform: translateY(-1px);
    }

    .btn-cancel-draft {
        background: transparent;
        border: 1.5px solid #ef4444;
        color: #ef4444;
        border-radius: 8px;
        padding: 0.65rem 1rem;
        font-weight: 600;
        width: 100%;
        margin-top: 0.6rem;
        transition: all 0.2s;
    }
    .btn-cancel-draft:hover {
        background: rgba(239, 68, 68, 0.08);
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
                        Contract Case #{{ $case->ctc_id }}
                    </h1>
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                        <span class="badge badge-primary px-3 py-1 font-weight-bold" style="border-radius: 20px; font-size: 11px;">
                            <i class="fas fa-tag mr-1"></i> {{ strtoupper($case->ctc_type) }} CASE
                        </span>
                        {{-- Current Holder Stage (css_stage) --}}
                        <span class="badge badge-info px-3 py-1 font-weight-bold text-white shadow-sm" style="border-radius: 20px; font-size: 11.5px; background: #0284c7;" title="Current Substatus / Workflow Holder Stage">
                            <i class="fas fa-user-clock mr-1"></i> Current Holder: {{ $case->current_stage ?? $case->currentSubstatus->css_stage ?? 'Division' }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('division.contract-cases.index') }}" class="btn-back-link">
                    <i class="fas fa-arrow-left"></i> Back to Hub
                </a>
            </div>

            <!-- Workflow Progress Stepper -->
            @php
                $approvalService = app(\App\Services\ContractCaseApprovalService::class);
                $workflowSteps = $approvalService->getWorkflowSteps($case);
                $currStage = $case->current_stage ?? 'Division';
                
                $stepIds = array_column($workflowSteps, 'id');
                $currIndex = array_search($currStage, $stepIds);
                if ($currIndex === false) $currIndex = 0;
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
                <!-- Left Main Column -->
                <div class="col-md-8">
                    <!-- Terms Summary Card -->
                    <div class="clean-card">
                        <div class="clean-card-header">
                            <span><i class="fas fa-file-contract mr-2 text-primary"></i> Contract Case Terms & Details</span>
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
                            <div class="data-grid-cell data-grid-value">{{ $case->unit->unt_name ?? 'Division' }}</div>
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
                        'title' => 'Case Attachments & Documents',
                        'defaultSlots' => ['CV', 'Approval', 'Form', 'Minute'],
                        'attachments' => $case->attachments,
                        'canEdit' => in_array($case->current_stage, ['Division']),
                    ])
                </div>

                <!-- Right Action Column -->
                <div class="col-md-4">
                    @if($case->current_stage === 'Division' || in_array($case->ctc_status, ['Draft', 'Under Revision']))
                        <!-- Division Release Panel -->
                        <div class="clean-card action-panel-card">
                            <div class="clean-card-header bg-light d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-paper-plane mr-2 text-primary"></i> Release to HR</span>
                                <a href="{{ route('division.contract-cases.edit', $case->ctc_id) }}" class="btn btn-warning btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                    <i class="fas fa-edit mr-1"></i> Edit Case
                                </a>
                            </div>
                            <div class="p-4">
                                <p class="text-muted small mb-3 font-weight-500">
                                    Provide formal release remarks to initiate scrutiny with the HR department.
                                </p>
                                <form id="releaseForm">
                                    @csrf
                                    <div class="form-group mb-0">
                                        <label class="rd-form-label font-weight-bold">Release Remarks <span class="text-danger">*</span></label>
                                        <textarea name="remarks" class="rd-textarea" rows="3" placeholder="Enter remarks or justification for HR Scrutiny..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn-release-action"><i class="fas fa-paper-plane"></i> Release to HR</button>
                                </form>

                                @if($case->ctc_status === 'Draft')
                                    <button type="button" class="btn-cancel-draft" id="btn-cancel-case">
                                        <i class="fas fa-trash-alt mr-1"></i> Cancel Draft
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- In Progress Card -->
                        <div class="clean-card">
                            <div class="clean-card-header">
                                <span><i class="fas fa-lock mr-2 text-muted"></i> Pipeline Status</span>
                            </div>
                            <div class="p-4 text-center">
                                <i class="fas fa-hourglass-half fa-2x mb-3 text-warning"></i>
                                <p class="text-muted small mb-1">Currently Under Review With:</p>
                                <div class="mb-2">
                                    <span class="badge badge-info px-3 py-1.5 font-weight-bold text-white" style="font-size: 13px; border-radius: 6px; background: #0284c7;">
                                        <i class="fas fa-user-clock mr-1"></i> Current Holder: {{ $case->current_stage ?? $case->currentSubstatus->css_stage ?? 'In Review' }}
                                    </span>
                                </div>
                                <div class="mt-3">
                                    <span class="badge badge-light border text-muted px-3 py-1 font-weight-bold">
                                        Modifications Locked
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

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // ── Release to HR ─────────────────────────────────────────
    $('#releaseForm').submit(function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        Swal.fire({
            title: 'Release to HR',
            text: 'Are you sure you want to release this contract case to HR for scrutiny?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Release Case',
            confirmButtonColor: '#5F7858',
            cancelButtonColor: '#94a3b8'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('division.contract-cases.release', $case->ctc_id) }}",
                    method: 'POST',
                    data: formData,
                    success: function(res) {
                        Swal.fire({
                            title: 'Released',
                            text: res.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "{{ route('division.contract-cases.index') }}";
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to release case.', 'error');
                    }
                });
            }
        });
    });

    // ── Cancel Case ───────────────────────────────────────────
    $('#btn-cancel-case').click(function() {
        Swal.fire({
            title: 'Cancel Contract Case',
            input: 'textarea',
            inputLabel: 'Reason for Cancellation (Optional)',
            inputPlaceholder: 'Enter cancellation reason...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel Case',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('division.contract-cases.cancel', $case->ctc_id) }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        remarks: result.value || 'Cancelled by division initiator'
                    },
                    success: function(res) {
                        Swal.fire('Cancelled', res.message, 'info').then(() => {
                            window.location.href = "{{ route('division.contract-cases.index') }}";
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to cancel case.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
