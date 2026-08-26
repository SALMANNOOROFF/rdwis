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

    .action-card {
        border-color: #3b82f6;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.1);
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

    .btn-action-primary {
        background: var(--rd-primary-600);
        color: var(--rd-text1);
        border: none;
        border-radius: 6px;
        padding: 0.8rem 1rem;
        font-weight: 700;
        width: 100%;
        margin-bottom: 0.5rem;
        transition: background 0.2s;
    }
    .btn-action-primary:hover { background: var(--rd-primary-700); }

    .btn-action-warning {
        background: #f59e0b;
        color: #000;
        border: none;
        border-radius: 6px;
        padding: 0.8rem 1rem;
        font-weight: 700;
        width: 100%;
        margin-bottom: 0.5rem;
        transition: background 0.2s;
    }

    .btn-action-danger {
        background: transparent;
        border: 1px solid #ef4444;
        color: #ef4444;
        border-radius: 6px;
        padding: 0.8rem 1rem;
        font-weight: 700;
        width: 100%;
        margin-bottom: 0.5rem;
        transition: all 0.2s;
    }
    .btn-action-danger:hover { background: rgba(239, 68, 68, 0.1); }

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
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="header-title mb-1">Contract Case #{{ $case->ctc_id }} - Finance Scrutiny</h1>
                    <span class="status-badge" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border-color: rgba(59, 130, 246, 0.3);">
                        CURRENT HOLDER: {{ $case->currentSubstatus->display_name ?? $case->ctc_status }}
                    </span>
                </div>
                <a href="{{ route('finance.contract-cases.index') }}" class="btn-back"><i class="fas fa-arrow-left mr-2"></i> Back to Hub</a>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-8">
                    <div class="premium-card">
                        <div class="premium-card-header d-flex justify-content-between align-items-center">
                            <span>Financial Terms & Summary</span>
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
                            <div class="data-grid-item data-value font-weight-bold text-success" style="font-size: 1.05rem;">Rs. {{ number_format($case->ctc_newsalary) }}</div>
                            <div class="data-grid-item data-label">Calculated Price</div>
                            <div class="data-grid-item data-value font-weight-bold" style="color: #60a5fa; font-size: 1.05rem;">Rs. {{ number_format((float)($case->ctc_price ?? 0)) }}</div>
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
                    </div>

                    <!-- Project Plan Breakdown -->
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
                        'canEdit' => in_array($case->current_stage, ['Finance']),
                    ])
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    @php $stage = $case->current_stage; @endphp

                    @if($stage === 'Finance')
                        <!-- Finance Action Card -->
                        <div class="premium-card action-card">
                            <div class="premium-card-header"><i class="fas fa-money-check-alt mr-1"></i> Director Finance Actions</div>
                            <div class="p-4">
                                <p class="text-muted text-sm mb-3">Review budget availability and compensation structure, then forward to Managing Director or return.</p>
                                
                                <button class="btn-action-primary" id="btn-forward-md"><i class="fas fa-paper-plane mr-2"></i> Forward to MD</button>
                                <button class="btn-action-warning" id="btn-return-case"><i class="fas fa-undo mr-2"></i> Return Case</button>
                                <button class="btn-action-danger" id="btn-reject-case"><i class="fas fa-times-circle mr-2"></i> Reject Case</button>
                            </div>
                        </div>
                    @else
                        <!-- In Progress Card -->
                        <div class="premium-card">
                            <div class="premium-card-header">Case Status</div>
                            <div class="p-4 text-center">
                                <i class="fas fa-lock fa-2x mb-3 text-secondary"></i>
                                <p class="text-muted" style="font-size: 0.9rem;">
                                    Currently held by authority:<br>
                                    <span class="status-badge mt-2 d-inline-block text-white" style="border-color: #60a5fa;">{{ $case->currentSubstatus->display_name ?? $case->ctc_status }}</span>
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
    // Forward to MD
    $('#btn-forward-md').click(function() {
        Swal.fire({
            title: 'Forward to MD',
            input: 'textarea',
            inputLabel: 'Finance Scrutiny Remarks (Optional)',
            inputPlaceholder: 'Enter budget / compensation notes...',
            showCancelButton: true,
            confirmButtonText: 'Forward to MD',
            confirmButtonColor: '#3182ce',
            cancelButtonColor: '#4a5568'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('finance.contract-cases.forward', $case->ctc_id) }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        remarks: result.value || 'Financial scrutiny completed. Forwarded to MD for approval.'
                    },
                    success: function(res) {
                        Swal.fire('Forwarded', res.message, 'success').then(() => {
                            window.location.href = "{{ route('finance.contract-cases.index') }}";
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to forward case.', 'error');
                    }
                });
            }
        });
    });

    // Return Case
    $('#btn-return-case').click(function() {
        Swal.fire({
            title: 'Return Contract Case',
            html: `
                <div class="text-left mb-3">
                    <label class="font-weight-bold text-white small">Return Destination:</label>
                    <select id="swal-return-target" class="form-control bg-dark text-white border-secondary mb-3">
                        <option value="HR">Return to HR</option>
                        <option value="Division">Return to Division</option>
                    </select>
                    <label class="font-weight-bold text-white small">Reason for Return (Required):</label>
                    <textarea id="swal-return-remarks" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Enter objection/revision reason..."></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Confirm Return',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#4a5568',
            preConfirm: () => {
                const remarks = $('#swal-return-remarks').val();
                if (!remarks) {
                    Swal.showValidationMessage('Reason for return is required!');
                    return false;
                }
                return {
                    target_stage: $('#swal-return-target').val(),
                    remarks: remarks
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('finance.contract-cases.return', $case->ctc_id) }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        target_stage: result.value.target_stage,
                        remarks: result.value.remarks
                    },
                    success: function(res) {
                        Swal.fire('Returned', res.message, 'success').then(() => {
                            window.location.href = "{{ route('finance.contract-cases.index') }}";
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to return case.', 'error');
                    }
                });
            }
        });
    });

    // Reject Case
    $('#btn-reject-case').click(function() {
        Swal.fire({
            title: 'Reject Contract Case',
            input: 'textarea',
            inputLabel: 'Rejection Reason (Required)',
            inputPlaceholder: 'Specify reasons why this case is not approved...',
            inputValidator: (value) => {
                if (!value) return 'Reason for rejection is mandatory!';
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm Reject',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#4a5568'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('finance.contract-cases.reject', $case->ctc_id) }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        remarks: result.value
                    },
                    success: function(res) {
                        Swal.fire('Rejected', res.message, 'info').then(() => {
                            window.location.href = "{{ route('finance.contract-cases.index') }}";
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
