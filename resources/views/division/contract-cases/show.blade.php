@extends('welcome')

@section('content')
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<style>
    .dark-contract-wrapper { 
        background-color: var(--rd-bg);
        color: var(--rd-text1);
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: calc(100vh - 60px);
        padding: 2rem;
    }
    
    .dark-contract-wrapper .header-title {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        color: var(--rd-text1);
    }

    .dark-contract-wrapper .btn-back {
        background: transparent;
        border: 1px solid var(--rd-border);
        color: var(--rd-text3);
        font-weight: 500;
        border-radius: 6px;
        padding: 0.4rem 1rem;
        transition: all 0.2s;
        text-decoration: none;
    }
    .dark-contract-wrapper .btn-back:hover {
        background: #2d3748;
        color: #fff;
    }

    .premium-card {
        background-color: #161b22;
        border: 1px solid var(--rd-border);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .premium-card-header {
        background-color: var(--rd-surface);
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid #2d3748;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--rd-text1);
        letter-spacing: 0.5px;
    }

    .release-card {
        border-color: #3182ce;
        box-shadow: 0 4px 15px rgba(49, 130, 206, 0.1);
    }

    .data-grid {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr 2fr;
        border-bottom: 1px solid #2d3748;
    }
    .data-grid:last-child {
        border-bottom: none;
    }
    .data-grid-item {
        padding: 1.2rem 1.5rem;
        border-right: 1px solid #2d3748;
        font-size: 0.9rem;
    }
    .data-grid-item:last-child {
        border-right: none;
    }
    .data-label {
        font-weight: 600;
        color: var(--rd-text3);
    }
    .data-value {
        color: var(--rd-text1);
    }

    .dark-input {
        background-color: var(--rd-surface);
        border: 1.5px solid var(--rd-border2);
        color: var(--rd-text1);
        border-radius: 6px;
        padding: 0.6rem 1rem;
        width: 100%;
        font-size: 0.9rem;
    }
    .dark-input:focus {
        outline: none;
        border-color: var(--rd-primary-600);
    }

    .dark-label {
        font-size: 0.8rem;
        color: var(--rd-text3);
        margin-bottom: 0.4rem;
        display: block;
    }

    .required-asterisk {
        color: #e53e3e;
    }

    .btn-release {
        background: #3182ce;
        color: var(--rd-text1);
        border: none;
        border-radius: 6px;
        padding: 0.8rem 1rem;
        font-weight: 700;
        width: 100%;
        margin-top: 1rem;
        transition: background 0.2s;
    }
    .btn-release:hover {
        background: #2b6cb0;
    }

    .btn-cancel-case {
        background: transparent;
        border: 1px solid #ef4444;
        color: #ef4444;
        border-radius: 6px;
        padding: 0.6rem 1rem;
        font-weight: 700;
        width: 100%;
        margin-top: 0.5rem;
        transition: all 0.2s;
    }
    .btn-cancel-case:hover { background: rgba(239, 68, 68, 0.1); }

    .status-badge {
        background-color: rgba(71, 85, 105, 0.2);
        color: var(--rd-text3);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid #334155;
    }
</style>

<div class="content-wrapper" style="background-color: var(--rd-surface);">
    <section class="content">
        <div class="dark-contract-wrapper">
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="header-title mb-1">Case Details #{{ $case->ctc_id }}</h1>
                    <span class="status-badge" style="background: rgba(49, 130, 206, 0.15); color: #60a5fa; border-color: rgba(49, 130, 206, 0.3);">
                        CURRENT HOLDER: {{ $case->currentSubstatus->display_name ?? $case->ctc_status }}
                    </span>
                </div>
                <a href="{{ route('division.contract-cases.index') }}" class="btn-back"><i class="fas fa-arrow-left mr-2"></i> Back to List</a>
            </div>

            <div class="row">
                <!-- Left Column: Details -->
                <div class="col-md-8">
                    <div class="premium-card">
                        <div class="premium-card-header d-flex justify-content-between align-items-center">
                            <span>Contract Summary</span>
                            <span class="badge badge-secondary px-3 py-1 font-weight-bold">{{ $case->ctc_status }}</span>
                        </div>
                        
                        <div class="data-grid">
                            <div class="data-grid-item data-label">Type</div>
                            <div class="data-grid-item data-value font-weight-bold text-info">
                                @if($case->ctc_type == 'Hg') Fresh Hiring (Hg)
                                @elseif($case->ctc_type == 'Ce') Contract Extension (Ce)
                                @elseif($case->ctc_type == 'Cr') Contract Renewal (Cr)
                                @elseif($case->ctc_type == 'Rh') Rehiring (Rh)
                                @endif
                            </div>
                            <div class="data-grid-item data-label">Division</div>
                            <div class="data-grid-item data-value">{{ $case->unit->unt_name ?? 'N/A' }}</div>
                        </div>

                        <div class="data-grid">
                            <div class="data-grid-item data-label">Candidate Name</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_empnamecomp }}</div>
                            <div class="data-grid-item data-label">Employee ID</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_emp_id ?: 'New Candidate' }}</div>
                        </div>

                        <div class="data-grid">
                            <div class="data-grid-item data-label">Designation</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_newjobtitle }}</div>
                            <div class="data-grid-item data-label">Grade</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_newgrade }}</div>
                        </div>

                        <div class="data-grid">
                            <div class="data-grid-item data-label">Monthly Salary</div>
                            <div class="data-grid-item data-value font-weight-bold text-success">Rs. {{ number_format($case->ctc_newsalary) }}</div>
                            <div class="data-grid-item data-label">Calculated Price</div>
                            <div class="data-grid-item data-value font-weight-bold" style="color: #60a5fa;">Rs. {{ number_format((float)($case->ctc_price ?? 0)) }}</div>
                        </div>

                        <div class="data-grid">
                            <div class="data-grid-item data-label">Start Date</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M Y') : 'N/A' }}</div>
                            <div class="data-grid-item data-label">End Date</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M Y') : 'N/A' }}</div>
                        </div>

                        @if($case->ctc_newprob > 0)
                            <div class="data-grid">
                                <div class="data-grid-item data-label">Probation Period</div>
                                <div class="data-grid-item data-value">{{ $case->ctc_newprob }} Months</div>
                                <div class="data-grid-item data-label">Probation Salary</div>
                                <div class="data-grid-item data-value">Rs. {{ number_format((float)($case->ctc_newprobsal ?: $case->ctc_newsalary)) }}</div>
                            </div>
                        @endif

                        @if($case->ctc_terminremarks)
                            <div class="p-3 border-top border-secondary" style="border-color: #2d3748 !important; background: rgba(245, 158, 11, 0.05);">
                                <span class="text-warning font-weight-bold d-block mb-1"><i class="fas fa-info-circle mr-1"></i> Extension / Early Termination Remarks:</span>
                                <p class="text-white mb-0" style="font-size: 0.9rem;">{{ $case->ctc_terminremarks }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Monthly Plan Breakdown -->
                    @if($case->casePlans->isNotEmpty())
                        <div class="premium-card">
                            <div class="premium-card-header">Project Head Allocation Plan</div>
                            <div class="table-responsive">
                                <table class="table mb-0 text-white" style="font-size: 0.85rem;">
                                    <thead>
                                        <tr style="background: rgba(255,255,255,0.03); color: var(--rd-text3);">
                                            <th>#</th>
                                            <th>Month Period</th>
                                            <th>Project Code & Title</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($case->casePlans as $idx => $cp)
                                            <tr style="border-top: 1px solid #2d3748;">
                                                <td>{{ $idx + 1 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($cp->ccp_startdt)->format('d M Y') }} - {{ \Carbon\Carbon::parse($cp->ccp_enddt)->format('d M Y') }}</td>
                                                <td>
                                                    @if($cp->project)
                                                        <span class="badge badge-primary">{{ $cp->project->prj_code }}</span> {{ $cp->project->prj_title }}
                                                    @else
                                                        <span class="text-muted">Core / Non-Project</span>
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
                        'title' => 'Case Attachments',
                        'defaultSlots' => ['CV', 'Approval', 'Form', 'Minute'],
                        'attachments' => $case->attachments,
                        'canEdit' => in_array($case->current_stage, ['Division']),
                    ])
                </div>

                <!-- Right Column: Action -->
                <div class="col-md-4">
                    @if($case->current_stage === 'Division' || in_array($case->ctc_status, ['Draft', 'Under Revision']))
                        <div class="premium-card release-card">
                            <div class="premium-card-header"><i class="fas fa-paper-plane mr-1"></i> Release to HR</div>
                            <div class="p-4">
                                <form id="releaseForm">
                                    @csrf
                                    <div class="form-group mb-0">
                                        <label class="dark-label">Release Remarks / Notes <span class="required-asterisk">*</span></label>
                                        <textarea name="remarks" class="dark-input" rows="3" placeholder="Enter remarks for HR Scrutiny..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn-release"><i class="fas fa-paper-plane mr-2"></i> Release to HR</button>
                                </form>

                                @if($case->ctc_status === 'Draft')
                                    <button type="button" class="btn-cancel-case" id="btn-cancel-case">
                                        <i class="fas fa-trash-alt mr-2"></i> Cancel Draft
                                    </button>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="premium-card">
                            <div class="premium-card-header">Case Pipeline Progress</div>
                            <div class="p-4 text-center">
                                <i class="fas fa-lock fa-2x mb-3 text-secondary"></i>
                                <p style="color: var(--rd-text3); font-size: 0.9rem;">
                                    This case is currently held by:<br>
                                    <span class="status-badge mt-2 d-inline-block text-white" style="border-color: #60a5fa;">{{ $case->currentSubstatus->display_name ?? $case->ctc_status }}</span>
                                </p>
                                <p style="color: #64748b; font-size: 0.8rem; margin-top: 1rem;">
                                    Modifications are locked while under scrutiny and approval.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- Workflow Trail History -->
                    @if($case->remarksHistory->isNotEmpty())
                        <div class="premium-card">
                            <div class="premium-card-header"><i class="fas fa-history mr-1"></i> Scrutiny Trail</div>
                            <div class="p-3" style="max-height: 250px; overflow-y: auto;">
                                @foreach($case->remarksHistory as $rm)
                                    <div class="mb-3 pb-2 border-bottom border-secondary" style="border-color: #2d3748 !important; font-size: 0.82rem;">
                                        <div class="d-flex justify-content-between text-muted">
                                            <strong>{{ $rm->crr_username }} ({{ $rm->crr_status }})</strong>
                                            <span>{{ \Carbon\Carbon::parse($rm->crr_dtg)->format('d M H:i') }}</span>
                                        </div>
                                        <p class="text-white mt-1 mb-0">{{ $rm->crr_remarks }}</p>
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
    // Release Case
    $('#releaseForm').submit(function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const formData = $(this).serialize();

        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Releasing...');

        $.ajax({
            url: "{{ route('division.contract-cases.release', $case->ctc_id) }}",
            method: 'POST',
            data: formData,
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        title: 'Released!',
                        text: res.message,
                        icon: 'success'
                    }).then(() => {
                        window.location.href = "{{ route('division.contract-cases.index') }}";
                    });
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to release case.', 'error');
                btn.attr('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i> Release to HR');
            }
        });
    });

    // Cancel Case
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
            cancelButtonColor: '#4a5568'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('division.contract-cases.cancel', $case->ctc_id) }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        remarks: result.value || 'Cancelled by division.'
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
