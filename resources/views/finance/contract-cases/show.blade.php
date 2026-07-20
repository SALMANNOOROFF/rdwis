@extends('welcome')

@section('content')
<style>
    /* Premium Dark Theme */
    .dark-contract-wrapper {
        background-color: #12141a;
        color: #e2e8f0;
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: calc(100vh - 60px);
        padding: 2rem;
    }
    
    .dark-contract-wrapper .header-title {
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        color: #ffffff;
    }

    .dark-contract-wrapper .btn-back {
        background: transparent;
        border: 1px solid #2d3748;
        color: #a0aec0;
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
        border: 1px solid #2d3748;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .premium-card-header {
        background-color: #1a1d24;
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid #2d3748;
        font-size: 0.95rem;
        font-weight: 700;
        color: #f8fafc;
        letter-spacing: 0.5px;
    }

    .action-card {
        border-color: #8b5cf6;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.1);
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
        color: #94a3b8;
    }
    .data-value {
        color: #e2e8f0;
    }

    .btn-action-primary {
        background: #8b5cf6;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 0.8rem 1rem;
        font-weight: 700;
        width: 100%;
        margin-bottom: 0.5rem;
        transition: background 0.2s;
    }
    .btn-action-primary:hover { background: #7c3aed; }

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
    .btn-action-danger:hover {
        background: rgba(239, 68, 68, 0.1);
    }

    .status-badge {
        background-color: rgba(71, 85, 105, 0.2);
        color: #94a3b8;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid #334155;
    }
</style>

<div class="content-wrapper" style="background-color: #12141a;">
    <section class="content">
        <div class="dark-contract-wrapper">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="header-title">Case Details #{{ $case->ctc_id }} - Scrutiny</h1>
                <a href="{{ route('finance.contract-cases.index') }}" class="btn-back"><i class="fas fa-arrow-left mr-2"></i> Back to Hub</a>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-md-8">
                    <div class="premium-card">
                        <div class="premium-card-header">Contract Summary</div>
                        
                        <div class="data-grid">
                            <div class="data-grid-item data-label">Type</div>
                            <div class="data-grid-item data-value">
                                @if($case->ctc_type == 'Hg') Fresh Hiring
                                @elseif($case->ctc_type == 'Ce') Extension
                                @elseif($case->ctc_type == 'Cr') Renewal
                                @elseif($case->ctc_type == 'Rh') Rehiring
                                @endif
                            </div>
                            <div class="data-grid-item data-label">Status</div>
                            <div class="data-grid-item data-value">
                                <span class="status-badge">{{ $case->ctc_status }}</span>
                            </div>
                        </div>

                        <div class="data-grid">
                            <div class="data-grid-item data-label">Name</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_empnamecomp }}</div>
                            <div class="data-grid-item data-label">Employee Type</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_emp_type }}</div>
                        </div>

                        <div class="data-grid">
                            <div class="data-grid-item data-label">Designation</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_newjobtitle }}</div>
                            <div class="data-grid-item data-label">Grade</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_newgrade }}</div>
                        </div>

                        <div class="data-grid">
                            <div class="data-grid-item data-label">Salary</div>
                            <div class="data-grid-item data-value" style="color: #60a5fa; font-weight: 700; font-size: 1.1rem;">Rs. {{ number_format($case->ctc_newsalary) }}</div>
                            <div class="data-grid-item data-label">Probation</div>
                            <div class="data-grid-item data-value">Standard</div>
                        </div>

                        <div class="data-grid">
                            <div class="data-grid-item data-label">Start Date</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M Y') : 'N/A' }}</div>
                            <div class="data-grid-item data-label">End Date</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M Y') : 'N/A' }}</div>
                        </div>
                    </div>

                    @php $cv = $case->attachments->where('cat_type', 'CV')->first(); @endphp
                    @if($cv)
                    <div class="premium-card">
                        <div class="premium-card-header">Attached Documents</div>
                        <div class="p-4 text-center">
                            <a href="{{ Storage::url($cv->cat_path) }}" target="_blank" style="color: #8b5cf6; text-decoration: none;">
                                <i class="fas fa-file-pdf fa-2x mb-2 d-block"></i>
                                View Uploaded CV/Document
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <div class="premium-card action-card">
                        <div class="premium-card-header">Finance Actions</div>
                        <div class="p-4">
                            @if($case->ctc_status == 'Under Finance Scrutiny')
                                <button class="btn-action-primary btn-action" data-url="{{ route('finance.contract-cases.forward', $case->ctc_id) }}" data-msg="Forward to MD for Approval?"><i class="fas fa-share mr-2"></i> Forward for Approval</button>
                                <button class="btn-action-danger btn-action" data-url="{{ route('finance.contract-cases.return', $case->ctc_id) }}" data-msg="Return Case due to objections?"><i class="fas fa-undo mr-2"></i> Return Case</button>
                            @else
                                <div class="text-center">
                                    <i class="fas fa-lock fa-2x mb-2 text-secondary"></i>
                                    <p style="color: #94a3b8; font-size: 0.9rem;">No pending actions for Finance at this stage.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.btn-action').click(function() {
        const url = $(this).data('url');
        const msg = $(this).data('msg');

        Swal.fire({
            title: 'Confirm Action',
            text: msg,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8b5cf6',
            cancelButtonColor: '#4a5568'
        }).then((result) => {
            if(result.isConfirmed) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if(res.success) {
                            Swal.fire('Success', res.message, 'success').then(() => {
                                window.location.href = "{{ route('finance.contract-cases.index') }}";
                            });
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
