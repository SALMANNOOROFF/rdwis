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

    /* Cards */
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

    /* Release Card specific */
    .release-card {
        border-color: #3b82f6;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.1);
    }

    /* Grid Table Data */
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

    /* Full width row override */
    .data-grid.full-row {
        grid-template-columns: 1fr 5fr;
    }

    /* Form elements */
    .dark-label {
        font-size: 0.8rem;
        color: #a0aec0;
        margin-bottom: 0.4rem;
        display: block;
    }
    .required-asterisk {
        color: #e53e3e;
    }
    .dark-input {
        background-color: #0f1219;
        border: 1px solid #1f2937;
        color: #f8fafc;
        border-radius: 6px;
        padding: 0.8rem 1rem;
        width: 100%;
        transition: border-color 0.2s;
        font-size: 0.9rem;
    }
    .dark-input:focus {
        outline: none;
        border-color: #3b82f6;
    }
    .dark-input::placeholder {
        color: #4a5568;
    }

    .btn-release {
        background: #60a5fa;
        color: #ffffff;
        border: none;
        border-radius: 6px;
        padding: 0.8rem 1rem;
        font-weight: 700;
        width: 100%;
        margin-top: 1rem;
        transition: background 0.2s;
    }
    .btn-release:hover {
        background: #3b82f6;
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
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="header-title">Case Details #{{ $case->ctc_id }}</h1>
                <a href="{{ route('division.contract-cases.index') }}" class="btn-back"><i class="fas fa-arrow-left mr-2"></i> Back to List</a>
            </div>

            <div class="row">
                <!-- Left Column: Details -->
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
                            <div class="data-grid-item data-value">Rs. {{ number_format($case->ctc_newsalary) }}</div>
                            <div class="data-grid-item data-label">Probation</div>
                            <div class="data-grid-item data-value">Standard</div>
                        </div>

                        <div class="data-grid">
                            <div class="data-grid-item data-label">Start Date</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M Y') : 'N/A' }}</div>
                            <div class="data-grid-item data-label">End Date</div>
                            <div class="data-grid-item data-value">{{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M Y') : 'N/A' }}</div>
                        </div>

                        <div class="data-grid full-row">
                            <div class="data-grid-item data-label">Primary Project</div>
                            <div class="data-grid-item data-value">{{ $case->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</div>
                        </div>

                    </div>

                    @php $cv = $case->attachments->where('cat_type', 'CV')->first(); @endphp
                    @if($cv)
                    <div class="premium-card">
                        <div class="premium-card-header">Attached Documents</div>
                        <div class="p-4 text-center">
                            <a href="{{ Storage::url($cv->cat_path) }}" target="_blank" style="color: #60a5fa; text-decoration: none;">
                                <i class="fas fa-file-pdf fa-2x mb-2 d-block"></i>
                                View Uploaded CV/Document
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Right Column: Action -->
                <div class="col-md-4">
                    @if(in_array($case->ctc_status, ['Draft', 'Under Revision']))
                        <div class="premium-card release-card">
                            <div class="premium-card-header">Release to HR</div>
                            <div class="p-4">
                                <form id="releaseForm">
                                    @csrf
                                    <div class="form-group mb-0">
                                        <label class="dark-label">Remarks / Notes <span class="required-asterisk">*</span></label>
                                        <textarea name="remarks" class="dark-input" rows="4" placeholder="Enter remarks for HR Scrutiny..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn-release"><i class="fas fa-paper-plane mr-2"></i> Release Now</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="premium-card">
                            <div class="premium-card-header">Case Progress</div>
                            <div class="p-4 text-center">
                                <i class="fas fa-lock fa-2x mb-3 text-secondary"></i>
                                <p style="color: #a0aec0; font-size: 0.9rem;">
                                    This case has been forwarded and is currently marked as:<br>
                                    <span class="status-badge mt-2 d-inline-block">{{ $case->ctc_status }}</span>
                                </p>
                                <p style="color: #4a5568; font-size: 0.8rem; margin-top: 1rem;">
                                    Modification is locked while under scrutiny.
                                </p>
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
    $('#releaseForm').submit(function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const formData = $(this).serialize();

        btn.attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');

        $.ajax({
            url: "{{ route('division.contract-cases.release', $case->ctc_id) }}",
            method: "POST",
            data: formData,
            success: function(res) {
                if(res.success) {
                    Swal.fire('Released', res.message, 'success').then(() => {
                        window.location.href = "{{ route('division.contract-cases.index') }}";
                    });
                }
            },
            error: function() {
                Swal.fire('Error', 'Something went wrong', 'error');
                btn.attr('disabled', false).html('<i class="fas fa-paper-plane mr-2"></i> Release Now');
            }
        });
    });
});
</script>
@endpush
