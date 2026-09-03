@extends('welcome')

@section('content')
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

@php
    $role = $authorityRole ?? 'MD';
    $roleTitle = match($role) {
        'DDG' => 'Deputy Director General (DDG)',
        'DG'  => 'Director General (DG)',
        default => 'Managing Director (MD)'
    };
    $routePrefix = match($role) {
        'DDG' => 'ddg',
        'DG'  => 'dg',
        default => 'md'
    };
@endphp

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

    .btn-action-approve {
        background: #10B981;
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 0.8rem 1rem;
        font-weight: 800;
        width: 100%;
        margin-bottom: 0.6rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 16px rgba(16, 185, 129, 0.25);
        transition: all 0.2s;
    }
    .btn-action-approve:hover {
        background: #059669;
        transform: translateY(-1px);
    }

    .btn-action-forward {
        background: var(--rd-primary-600);
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 0.8rem 1rem;
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
                        {{ $roleTitle }} Portal &bull; Case #{{ $case->ctc_id }}
                    </h1>
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                        <span class="badge badge-primary px-3 py-1 font-weight-bold" style="border-radius: 20px; font-size: 11px;">
                            <i class="fas fa-tag mr-1"></i> {{ strtoupper($case->ctc_type) }} CASE
                        </span>
                        {{-- Current Holder Stage (css_stage) --}}
                        <span class="badge badge-info px-3 py-1 font-weight-bold text-white shadow-sm" style="border-radius: 20px; font-size: 11.5px; background: #0284c7;" title="Current Substatus / Workflow Holder Stage">
                            <i class="fas fa-user-clock mr-1"></i> Current Holder: {{ $case->current_stage ?? $case->currentSubstatus->css_stage ?? $role }}
                        </span>
                    </div>
                </div>
                <a href="{{ route($routePrefix . '.contract-cases.index') }}" class="btn-back-link">
                    <i class="fas fa-arrow-left"></i> Back to {{ $role }} Hub
                </a>
            </div>

            <!-- Workflow Progress Stepper -->
            @php
                $approvalService = app(\App\Services\ContractCaseApprovalService::class);
                $workflowSteps = $approvalService->getWorkflowSteps($case);
                $currStage = $case->current_stage ?? $role;
                
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
                <!-- Left Main Details Column -->
                <div class="col-md-8">
                    <!-- Terms Summary Card -->
                    <div class="clean-card">
                        <div class="clean-card-header">
                            <span><i class="fas fa-file-contract mr-2 text-primary"></i> Executive Contract Dossier</span>
                            <span class="badge badge-light border font-weight-bold px-3 py-1">{{ $case->ctc_status }}</span>
                        </div>
                        
                        <div class="data-grid-table">
                            <div class="data-grid-cell data-grid-label">Originating Division</div>
                            <div class="data-grid-cell data-grid-value font-weight-bold text-dark">
                                {{ $case->division_name }}
                                @if($case->division_short && $case->division_short !== $case->division_name)
                                    <span class="badge badge-light border text-muted ml-1" style="font-size: 10px;">{{ $case->division_short }}</span>
                                @endif
                            </div>

                            <div class="data-grid-cell data-grid-label">Case Type</div>
                            <div class="data-grid-cell data-grid-value text-primary">
                                @if($case->ctc_type == 'Hg') Fresh Hiring (Hg)
                                @elseif($case->ctc_type == 'Cr') Contract Renewal (Cr)
                                @elseif($case->ctc_type == 'Ce') Contract Extension (Ce)
                                @elseif($case->ctc_type == 'Rh') Re-Hiring (Rh)
                                @else {{ strtoupper($case->ctc_type) }}
                                @endif
                            </div>
                        </div>

                        <div class="data-grid-table">
                            <div class="data-grid-cell data-grid-label">Candidate Name</div>
                            <div class="data-grid-cell data-grid-value font-weight-bold text-dark">{{ $case->ctc_empnamecomp }}</div>

                            <div class="data-grid-cell data-grid-label">CNIC Number</div>
                            <div class="data-grid-cell data-grid-value" style="font-family: monospace;">{{ $case->ctc_cnic ?: 'N/A' }}</div>

                            <div class="data-grid-cell data-grid-label">Contact / Phone</div>
                            <div class="data-grid-cell data-grid-value">{{ $case->ctc_contact ?: 'N/A' }}</div>

                            <div class="data-grid-cell data-grid-label">Proposed Job Title</div>
                            <div class="data-grid-cell data-grid-value font-weight-bold">{{ $case->ctc_newjobtitle }}</div>

                            <div class="data-grid-cell data-grid-label">Proposed Grade / Rank</div>
                            <div class="data-grid-cell data-grid-value"><span class="badge badge-secondary px-2 py-1 font-weight-bold">{{ $case->ctc_newgrade }}</span></div>

                            <div class="data-grid-cell data-grid-label">Employment Nature</div>
                            <div class="data-grid-cell data-grid-value">{{ $case->ctc_emp_type ?: 'Contract' }}</div>

                            <div class="data-grid-cell data-grid-label">Proposed Monthly Salary</div>
                            <div class="data-grid-cell data-grid-value text-primary font-weight-bold" style="font-size: 1.15rem;">
                                PKR {{ number_format((float) ($case->ctc_newsalary ?? 0)) }}
                            </div>

                            <div class="data-grid-cell data-grid-label">Proposed Contract Period</div>
                            <div class="data-grid-cell data-grid-value font-weight-bold">
                                {{ \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M Y') }}
                            </div>

                            <div class="data-grid-cell data-grid-label">Probation Period</div>
                            <div class="data-grid-cell data-grid-value">{{ $case->ctc_newprob ? $case->ctc_newprob . ' Months' : 'Nil' }}</div>

                            <div class="data-grid-cell data-grid-label">Probation Salary</div>
                            <div class="data-grid-cell data-grid-value">PKR {{ number_format((float) ($case->ctc_newprobsal ?? 0)) }}</div>

                            <div class="data-grid-cell data-grid-label">Job Description / Scope</div>
                            <div class="data-grid-cell data-grid-value">{{ $case->ctc_jd ?: 'Standard Terms' }}</div>

                            @if($case->previousContract)
                                <div class="data-grid-cell data-grid-label bg-light">Previous Contract Reference</div>
                                <div class="data-grid-cell data-grid-value bg-light">
                                    Contract #{{ $case->previousContract->ctr_id }} &bull; (PKR {{ number_format($case->previousContract->ctr_salary) }} / {{ $case->previousContract->ctr_rank }})
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Project Allocations -->
                    @if($case->casePlans->isNotEmpty())
                        <div class="clean-card">
                            <div class="clean-card-header">
                                <span><i class="fas fa-project-diagram mr-2 text-primary"></i> Budget & Project Allocations</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table clean-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Period</th>
                                            <th>Charge Head / Project Allocation</th>
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
                        'canEdit' => false,
                    ])
                </div>

                <!-- Right Action Column -->
                <div class="col-md-4">
                    @if($case->current_stage === $role)
                        <!-- Authority Action Panel -->
                        <div class="clean-card" style="border: 1.5px solid var(--rd-primary-600); box-shadow: 0 4px 16px rgba(95, 120, 88, 0.12);">
                            <div class="clean-card-header bg-light">
                                <span><i class="fas fa-stamp mr-2 text-primary"></i> {{ $role }} Executive Action</span>
                            </div>
                            <div class="p-4">
                                {{-- Financial & Grade Delegated Authority Evaluation Box --}}
                                @php
                                    $reqStage = $authDetails['required_stage'] ?? 'DG';
                                    $isDesignatedAuthority = ($role === 'MD' && $reqStage === 'MD') ||
                                                             ($role === 'DDG' && in_array($reqStage, ['MD', 'DDG'])) ||
                                                             ($role === 'DG');
                                @endphp

                                @if($isDesignatedAuthority)
                                    <div class="alert alert-success border-0 small mb-3 p-2.5 d-flex align-items-start" style="border-radius: 8px; background: #ecfdf5; border-left: 4px solid #10b981 !important;">
                                        <i class="fas fa-check-circle text-success mr-2 mt-1"></i>
                                        <div>
                                            <strong class="text-success d-block">{{ $role }} Designated Final Approval Authority</strong>
                                            <span class="text-muted" style="font-size: 11.5px;">
                                                Case salary (PKR {{ number_format($authDetails['salary'] ?? $case->ctc_newsalary) }}) and grade ({{ $authDetails['grade'] ?? $case->ctc_newgrade }}) fall within {{ $role }} approval powers. You can grant <strong>Final Approval</strong> to finalize this case.
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-warning border-0 small mb-3 p-2.5 d-flex align-items-start" style="border-radius: 8px; background: #fffbeb; border-left: 4px solid #f59e0b !important;">
                                        <i class="fas fa-info-circle text-warning mr-2 mt-1"></i>
                                        <div>
                                            <strong class="text-warning d-block">Exceeds {{ $role }} Approval Limit</strong>
                                            <span class="text-muted" style="font-size: 11.5px;">
                                                Case salary (PKR {{ number_format($authDetails['salary'] ?? $case->ctc_newsalary) }}) or grade ({{ $authDetails['grade'] ?? $case->ctc_newgrade }}) exceeds {{ $role }} limit. This case requires <strong>{{ $reqStage }} Approval</strong>. Please endorse and forward ahead.
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group mb-3">
                                    <label class="rd-form-label font-weight-bold">Executive Remarks <span class="text-danger">*</span></label>
                                    <textarea id="execRemarks" class="rd-textarea" rows="3" placeholder="Enter executive decision, endorsement notes, or review comments..."></textarea>
                                </div>

                                {{-- If role is the designated authority, show ONLY Approve button --}}
                                @if($isDesignatedAuthority)
                                    <button type="button" class="btn-action-approve mb-2" id="btn-approve-case" style="background: #10b981; border-color: #10b981; color: white;">
                                        <i class="fas fa-check-double mr-1.5"></i> Grant {{ $role }} Final Approval
                                    </button>
                                @else
                                    {{-- If role is NOT designated authority (exceeds limit), show ONLY Forward button --}}
                                    @if($role === 'MD')
                                        <button type="button" class="btn-action-forward mb-2" id="btn-forward-case">
                                            <i class="fas fa-arrow-right"></i> Endorse & Forward to DDG
                                        </button>
                                    @elseif($role === 'DDG')
                                        <button type="button" class="btn-action-forward mb-2" id="btn-forward-case">
                                            <i class="fas fa-arrow-right"></i> Endorse & Forward to DG
                                        </button>
                                    @endif
                                @endif

                                <button type="button" class="btn-action-return" id="btn-return-case">
                                    <i class="fas fa-undo"></i> Return Case
                                </button>
                                <button type="button" class="btn-action-reject" id="btn-reject-case">
                                    <i class="fas fa-times-circle mr-1"></i> Reject Case
                                </button>
                            </div>
                        </div>
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

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const routePrefix = '{{ $routePrefix }}';
    const role = '{{ $role }}';

    // ── Approve (MD / DDG / DG) ──────────────────────────────
    $('#btn-approve-case').click(function() {
        const rem = $('#execRemarks').val();
        if (!rem) {
            Swal.fire('Required', 'Please provide approval remarks.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Grant ' + role + ' Final Approval',
            text: 'Are you sure you want to grant executive approval for this contract case? Once approved, the case will be forwarded to HR for fulfillment.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Grant Approval',
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#94a3b8'
        }).then((res) => {
            if (res.isConfirmed) {
                const approveUrl = "/" + routePrefix + "/contract-cases/{{ $case->ctc_id }}/approve";

                $.ajax({
                    url: approveUrl,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', remarks: rem },
                    success: function(resp) {
                        Swal.fire('Approved', resp.message, 'success').then(() => {
                            window.location.href = "/" + routePrefix + "/contract-cases";
                        });
                    },
                    error: function(err) {
                        const msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Failed to approve case.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });

    // ── Forward (MD / DDG) ────────────────────────────────────
    $('#btn-forward-case').click(function() {
        const rem = $('#execRemarks').val();
        if (!rem) {
            Swal.fire('Required', 'Please provide endorsement remarks before forwarding.', 'warning');
            return;
        }

        const targetName = (routePrefix === 'md') ? 'DDG' : 'DG';

        Swal.fire({
            title: 'Forward to ' + targetName,
            text: 'Endorse and forward this case to ' + targetName + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Forward',
            confirmButtonColor: '#5F7858',
            cancelButtonColor: '#94a3b8'
        }).then((res) => {
            if (res.isConfirmed) {
                const targetUrl = (routePrefix === 'md') 
                    ? "{{ route('md.contract-cases.forward', $case->ctc_id) }}" 
                    : "{{ route('ddg.contract-cases.forward', $case->ctc_id) }}";

                $.ajax({
                    url: targetUrl,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', remarks: rem },
                    success: function(resp) {
                        Swal.fire('Forwarded', resp.message, 'success').then(() => {
                            window.location.href = "/" + routePrefix + "/contract-cases";
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to forward case.', 'error');
                    }
                });
            }
        });
    });

    // ── Return Case ───────────────────────────────────────────
    $('#btn-return-case').click(function() {
        const rem = $('#execRemarks').val();
        if (!rem) {
            Swal.fire('Required', 'Please specify reasons for return in the remarks field.', 'warning');
            return;
        }

        let optionsHtml = '';
        if (routePrefix === 'dg') {
            optionsHtml = `
                <option value="DDG">DDG Office</option>
                <option value="MD">Managing Director (MD)</option>
                <option value="Finance">Finance Department</option>
                <option value="HR">HR Operations</option>
                <option value="Division">Division (Under Revision)</option>
            `;
        } else if (routePrefix === 'ddg') {
            optionsHtml = `
                <option value="MD">Managing Director (MD)</option>
                <option value="Finance">Finance Department</option>
                <option value="HR">HR Operations</option>
                <option value="Division">Division (Under Revision)</option>
            `;
        } else {
            optionsHtml = `
                <option value="Finance">Finance Department</option>
                <option value="HR">HR Operations</option>
                <option value="Division">Division (Under Revision)</option>
            `;
        }

        Swal.fire({
            title: 'Return Case',
            html: `
                <div class="text-left font-weight-500" style="font-size: 0.9rem;">
                    <label class="d-block mb-2 text-dark font-weight-bold">Select Return Target:</label>
                    <select id="swal-return-target" class="form-control mb-3">
                        ${optionsHtml}
                    </select>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Return',
            confirmButtonColor: '#F59E0B',
            cancelButtonColor: '#94a3b8',
            preConfirm: () => {
                return document.getElementById('swal-return-target').value;
            }
        }).then((res) => {
            if (res.isConfirmed) {
                const targetUrl = "/" + routePrefix + "/contract-cases/{{ $case->ctc_id }}/return";

                $.ajax({
                    url: targetUrl,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        remarks: rem,
                        target_stage: res.value
                    },
                    success: function(resp) {
                        Swal.fire('Returned', resp.message, 'info').then(() => {
                            window.location.href = "/" + routePrefix + "/contract-cases";
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
        const rem = $('#execRemarks').val();
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
                const targetUrl = "/" + routePrefix + "/contract-cases/{{ $case->ctc_id }}/reject";

                $.ajax({
                    url: targetUrl,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', remarks: rem },
                    success: function(resp) {
                        Swal.fire('Rejected', resp.message, 'error').then(() => {
                            window.location.href = "/" + routePrefix + "/contract-cases";
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to reject case.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
