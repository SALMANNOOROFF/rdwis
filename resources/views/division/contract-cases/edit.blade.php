@extends('welcome')

@section('content')
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

    .form-surface-card {
        background-color: #FFFFFF;
        border: 1px solid var(--rd-border);
        border-radius: 14px;
        padding: 2rem;
        margin-top: 1.5rem;
        box-shadow: 0 4px 16px rgba(41, 40, 36, 0.05);
    }

    .section-header-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--rd-primary-600);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1.5px solid var(--rd-neutral-200);
        padding-bottom: 0.6rem;
    }

    .rd-form-control {
        background-color: #FFFFFF;
        border: 1.5px solid var(--rd-border2);
        color: var(--rd-text1);
        border-radius: 8px;
        padding: 0.65rem 1rem;
        width: 100%;
        transition: all 0.2s ease;
        font-size: 0.92rem;
        font-weight: 500;
    }
    .rd-form-control:focus {
        outline: none;
        border-color: var(--rd-primary-600);
        box-shadow: 0 0 0 3px rgba(95, 120, 88, 0.15);
        background-color: #FFFFFF;
    }
    .rd-form-control:disabled, .rd-form-control[readonly] {
        background-color: var(--rd-neutral-100) !important;
        border-color: var(--rd-border) !important;
        color: var(--rd-text2) !important;
        cursor: not-allowed;
    }
    .rd-form-control::placeholder {
        color: var(--rd-neutral-500);
    }

    .rd-form-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--rd-text2);
        margin-bottom: 0.4rem;
        display: block;
    }

    .required-star {
        color: #ef4444;
        margin-left: 2px;
    }

    .type-pill {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 30px;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Revision Feedback Notice */
    .revision-feedback-card {
        background: #FFFBEB;
        border: 1.5px solid #FCD34D;
        border-left: 6px solid #F59E0B;
        border-radius: 12px;
        padding: 1.2rem 1.6rem;
        margin-top: 1.5rem;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
    }

    /* Reference Card */
    .reference-box {
        background: #F0F6FF;
        border: 1.5px solid #BFDBFE;
        border-radius: 10px;
        padding: 1.2rem;
        margin-bottom: 1.2rem;
    }
    .reference-box-title {
        font-weight: 700;
        color: #1D4ED8;
        text-transform: uppercase;
        margin-bottom: 0.6rem;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Financial Value Card */
    .value-summary-card {
        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
        border: 1.5px solid #CBD5E1;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        margin-top: 1.5rem;
    }
    .value-summary-label {
        font-size: 0.75rem;
        color: var(--rd-text3);
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .value-summary-amount {
        font-size: 1.9rem;
        font-weight: 800;
        color: var(--rd-primary-600);
        margin-top: 0.4rem;
        letter-spacing: -0.5px;
    }

    /* Project Mode Selection Cards */
    .mode-card {
        border: 1.5px solid var(--rd-border);
        border-radius: 10px;
        padding: 1.2rem;
        margin-bottom: 1rem;
        background-color: #FFFFFF;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .mode-card.active {
        border-color: var(--rd-primary-600);
        background-color: var(--rd-primary-50);
        box-shadow: 0 2px 8px rgba(95, 120, 88, 0.1);
    }
    
    .clean-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 6px;
    }
    .clean-table th {
        font-size: 0.75rem;
        color: var(--rd-text3);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.5rem 0.6rem;
        border-bottom: 1.5px solid var(--rd-neutral-200);
    }
    .clean-table td {
        padding: 0.4rem 0.6rem;
    }

    .file-input-box {
        display: flex;
        align-items: center;
        background-color: #FFFFFF;
        border: 1.5px solid var(--rd-border2);
        border-radius: 8px;
        overflow: hidden;
    }
    .file-input-btn {
        background-color: var(--rd-neutral-200);
        color: var(--rd-text1);
        border: none;
        padding: 0.65rem 1.1rem;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .file-input-btn:hover {
        background-color: var(--rd-neutral-300);
    }
    .file-input-label {
        padding-left: 1rem;
        font-size: 0.85rem;
        color: var(--rd-text3);
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .actions-footer-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1.5px solid var(--rd-neutral-200);
    }
    
    .btn-action-cancel {
        background: #FFFFFF;
        border: 1.5px solid var(--rd-border);
        color: var(--rd-text2);
        padding: 0.65rem 1.6rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-action-cancel:hover {
        background: var(--rd-neutral-100);
        color: var(--rd-text1);
    }

    .btn-action-update {
        background: #D97706;
        border: none;
        color: #FFFFFF;
        padding: 0.7rem 2.2rem;
        border-radius: 8px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);
        transition: all 0.2s;
    }
    .btn-action-update:hover {
        background: #B45309;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(217, 119, 6, 0.35);
    }

    .duration-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .currency-input-group {
        display: flex;
    }
    .currency-input-group .currency-prefix {
        background: var(--rd-neutral-100);
        border: 1.5px solid var(--rd-border2);
        border-right: none;
        color: var(--rd-text2);
        padding: 0.65rem 1rem;
        border-radius: 8px 0 0 8px;
        font-weight: 700;
        font-size: 0.9rem;
    }
    .currency-input-group input {
        border-radius: 0 8px 8px 0;
        border-left: none;
    }

    /* Select2 Bootstrap4 overrides for Light Theme */
    .select2-container--bootstrap4 .select2-selection {
        background-color: #FFFFFF !important;
        border: 1.5px solid var(--rd-border2) !important;
        color: var(--rd-text1) !important;
        border-radius: 8px !important;
        min-height: 42px !important;
        padding: 6px 12px !important;
    }
    .select2-container--bootstrap4 .select2-selection__rendered {
        color: var(--rd-text1) !important;
        font-weight: 500 !important;
    }
    .select2-dropdown {
        background-color: #FFFFFF !important;
        border: 1.5px solid var(--rd-border) !important;
        box-shadow: var(--rd-shadow-md) !important;
        border-radius: 8px !important;
    }
    .select2-results__option {
        color: var(--rd-text1) !important;
        padding: 8px 12px !important;
    }
    .select2-results__option--highlighted {
        background-color: var(--rd-primary-600) !important;
        color: #FFFFFF !important;
    }
</style>

<div class="content-wrapper" style="background-color: var(--rd-bg);">
    <section class="content">
        <div class="contract-page-wrapper">
            
            <!-- Header Bar -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-2">
                <div>
                    <h1 class="page-title mb-1">
                        Revise Contract Case #{{ $case->ctc_id }}
                    </h1>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="type-pill @if($case->ctc_type == 'Hg') bg-primary text-white @elseif($case->ctc_type == 'Ce') bg-success text-white @elseif($case->ctc_type == 'Cr') bg-warning text-dark @else bg-info text-white @endif">
                            <i class="fas fa-tag"></i> TYPE: {{ strtoupper($case->ctc_type) }}
                        </span>
                        <span class="badge badge-warning font-weight-bold px-3 py-1 ml-2" style="border-radius: 20px; font-size: 11px;">
                            <i class="fas fa-undo mr-1"></i> STATUS: {{ $case->ctc_status }}
                        </span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('division.contract-cases.show', $case->ctc_id) }}" class="btn-back-link mr-2">
                        <i class="fas fa-eye"></i> View Case
                    </a>
                    <a href="{{ route('division.contract-cases.index') }}" class="btn-back-link">
                        <i class="fas fa-arrow-left"></i> Back to Hub
                    </a>
                </div>
            </div>

            <!-- Review Feedback / Objection Notice -->
            @if($latestReturnRemark)
                <div class="revision-feedback-card">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-circle text-warning fa-2x mr-3 mt-1"></i>
                        <div>
                            <h5 class="font-weight-bold text-dark mb-1" style="font-size: 1.05rem;">
                                Reviewer Feedback from {{ $latestReturnRemark->crr_username }} <span class="badge badge-warning ml-2 font-weight-bold">{{ $latestReturnRemark->crr_status }}</span>
                            </h5>
                            <p class="text-dark mb-1 font-weight-500" style="font-size: 0.95rem; line-height: 1.5;">
                                "{{ $latestReturnRemark->crr_remarks }}"
                            </p>
                            <small class="text-muted">
                                <i class="far fa-clock mr-1"></i> Logged on {{ \Carbon\Carbon::parse($latestReturnRemark->crr_dtg)->format('d M Y, H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Main Form Card -->
            <div class="form-surface-card">
                <form id="contract-case-edit-form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="ctc_type" id="ctc_type" value="{{ $case->ctc_type }}">
                    <input type="hidden" name="ctc_emp_id" id="ctc_emp_id" value="{{ $case->ctc_emp_id }}">
                    <input type="hidden" name="ctc_ctr_id" id="ctc_ctr_id" value="{{ $case->ctc_ctr_id }}">
                    
                    <div class="row">
                        <!-- COLUMN 1: CANDIDATE & DESIGNATION -->
                        <div class="col-md-4 border-right" style="border-color: var(--rd-neutral-200) !important; padding-right: 1.8rem;">
                            <div class="section-header-title">
                                <i class="fas fa-user-tie"></i> Candidate & Designation
                            </div>

                            @if($case->previousContract)
                                <!-- Previous Contract Reference Card -->
                                <div class="reference-box">
                                    <div class="reference-box-title"><i class="fas fa-id-card"></i> Active Contract Reference</div>
                                    <div class="row small">
                                        <div class="col-6 mb-1"><span class="text-muted">Designation:</span> <strong class="text-dark d-block">{{ $case->previousContract->ctr_jobtitle ?? 'N/A' }}</strong></div>
                                        <div class="col-6 mb-1"><span class="text-muted">Grade:</span> <strong class="text-dark d-block">{{ $case->previousContract->ctr_grade ?? 'N/A' }}</strong></div>
                                        <div class="col-6"><span class="text-muted">Salary:</span> <strong class="text-primary font-weight-bold d-block">Rs. {{ number_format($case->previousContract->ctr_salary ?? 0) }}</strong></div>
                                        <div class="col-6"><span class="text-muted">Type:</span> <strong class="text-dark d-block">{{ $case->previousContract->ctr_type == 2 ? 'Part Time' : 'Full Time' }}</strong></div>
                                        <div class="col-12 mt-2 pt-2 border-top border-primary-100"><span class="text-muted">Contract Expiry:</span> <strong class="text-danger font-weight-bold">{{ $case->previousContract->ctr_termindt ? \Carbon\Carbon::parse($case->previousContract->ctr_termindt)->format('d M Y') : ($case->previousContract->ctr_enddt ? \Carbon\Carbon::parse($case->previousContract->ctr_enddt)->format('d M Y') : 'N/A') }}</strong></div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="form-group mb-4">
                                <label class="rd-form-label">Candidate Full Name <span class="required-star">*</span></label>
                                <input type="text" name="ctc_empnamecomp" id="ctc_empnamecomp" class="rd-form-control font-weight-bold" required value="{{ $case->ctc_empnamecomp }}" @if(in_array(strtoupper($case->ctc_type), ['CR', 'CE'])) readonly @endif>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="rd-form-label">Designation <span class="required-star">*</span></label>
                                    <input type="text" name="ctc_newjobtitle" id="ctc_newjobtitle" class="rd-form-control" required value="{{ $case->ctc_newjobtitle }}" @if(strtoupper($case->ctc_type) === 'CE') readonly @endif>
                                </div>
                                <div class="col-6">
                                    <label class="rd-form-label">Grade / Scale <span class="required-star">*</span></label>
                                    <select name="ctc_newgrade" id="ctc_newgrade" class="rd-form-control" required @if(strtoupper($case->ctc_type) === 'CE') disabled @endif>
                                        <option value="">- Select -</option>
                                        @foreach(['Director', 'Manager', 'PRO', 'SRO', 'RO', 'RA', 'EA', 'PRA', 'SRA', 'JRA', 'SRT', 'RT', 'JRT', 'LA', 'Internee', 'Worker'] as $gr)
                                            <option value="{{ $gr }}" {{ $case->ctc_newgrade == $gr ? 'selected' : '' }}>{{ $gr }}</option>
                                        @endforeach
                                    </select>
                                    @if(strtoupper($case->ctc_type) === 'CE')
                                        <input type="hidden" name="ctc_newgrade" id="hidden_ctc_newgrade" value="{{ $case->ctc_newgrade }}">
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="rd-form-label">Division</label>
                                    <input type="text" class="rd-form-control" value="{{ $divisionName }}" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="rd-form-label">Employment Type <span class="required-star">*</span></label>
                                    <select name="ctc_emp_type" id="ctc_emp_type" class="rd-form-control" required @if(strtoupper($case->ctc_type) === 'CE') disabled @endif>
                                        <option value="Full Time" {{ $case->ctc_emp_type === 'Full Time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="Part Time" {{ $case->ctc_emp_type === 'Part Time' ? 'selected' : '' }}>Part Time</option>
                                    </select>
                                    @if(strtoupper($case->ctc_type) === 'CE')
                                        <input type="hidden" name="ctc_emp_type" id="hidden_ctc_emp_type" value="{{ $case->ctc_emp_type }}">
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="rd-form-label">CNIC</label>
                                    <input type="text" name="ctc_cnic" id="ctc_cnic" class="rd-form-control cnic-mask" value="{{ $case->ctc_cnic }}" placeholder="99999-9999999-9">
                                </div>
                                <div class="col-6">
                                    <label class="rd-form-label">Contact Number</label>
                                    <input type="text" name="ctc_contact" id="ctc_contact" class="rd-form-control" value="{{ $case->ctc_contact }}" placeholder="03xx-xxxxxxx">
                                </div>
                            </div>

                            @if(strtoupper($case->ctc_type) === 'CE')
                                <div class="form-group mb-4">
                                    <label class="rd-form-label text-warning font-weight-bold">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Extension Reason / Justification <span class="required-star">*</span>
                                    </label>
                                    <textarea name="ctc_terminremarks" id="ctc_terminremarks" class="rd-form-control border-warning" rows="3" required placeholder="Specify formal reason/justification for contract extension...">{{ $case->ctc_terminremarks }}</textarea>
                                </div>
                            @elseif(strtoupper($case->ctc_type) === 'CR')
                                <div class="form-group mb-4" id="cr-termin-remarks-group">
                                    <label class="rd-form-label text-warning font-weight-bold">
                                        <i class="fas fa-clock mr-1"></i> Early Termination / Renewal Date Override Reason
                                    </label>
                                    <textarea name="ctc_terminremarks" id="ctc_terminremarks" class="rd-form-control border-warning" rows="2" placeholder="Required when new start date does not immediately follow previous contract end date...">{{ $case->ctc_terminremarks }}</textarea>
                                </div>
                            @endif

                            <div class="form-group mb-4">
                                <label class="rd-form-label">Job Description / Summary</label>
                                <textarea name="ctc_jd" class="rd-form-control" rows="2" placeholder="Summary of responsibilities...">{{ $case->ctc_jd }}</textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label class="rd-form-label">Justification / Notes</label>
                                <textarea name="remarks" class="rd-form-control" rows="2" placeholder="Additional notes or references...">{{ $case->ctc_remarks }}</textarea>
                            </div>

                            <div class="form-group">
                                <label class="rd-form-label">Attach / Replace CV or Documents</label>
                                <div class="file-input-box">
                                    <button type="button" class="file-input-btn" onclick="document.getElementById('cv-upload').click()">Browse File</button>
                                    <span class="file-input-label" id="file-name">{{ $case->ctc_cv_path ? basename($case->ctc_cv_path) : 'No new file chosen' }}</span>
                                    <input type="file" id="cv-upload" name="cv_file" class="d-none" accept=".pdf,.doc,.docx" onchange="document.getElementById('file-name').innerText = this.files[0] ? this.files[0].name : 'No file chosen'">
                                </div>
                            </div>
                        </div>

                        <!-- COLUMN 2: FINANCIAL SETUP -->
                        <div class="col-md-4 border-right" style="border-color: var(--rd-neutral-200) !important; padding-left: 1.8rem; padding-right: 1.8rem;">
                            <div class="section-header-title">
                                <i class="fas fa-coins"></i> Financial & Terms Setup
                            </div>

                            <div class="form-group mb-4">
                                <label class="rd-form-label">Monthly Base Salary (PKR) <span class="required-star">*</span></label>
                                <div class="currency-input-group">
                                    <span class="currency-prefix">Rs.</span>
                                    <input type="number" name="ctc_newsalary" id="salary-input" class="rd-form-control font-weight-bold text-success" style="font-size: 1.15rem; text-align: right;" required min="0" value="{{ (int)$case->ctc_newsalary }}" @if(strtoupper($case->ctc_type) === 'CE') readonly @endif>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="rd-form-label">Start Date <span class="required-star">*</span></label>
                                    <input type="date" name="ctc_newstartdt" id="ctc_startdate" class="rd-form-control" required value="{{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('Y-m-d') : '' }}" @if(strtoupper($case->ctc_type) === 'CE') readonly @endif>
                                </div>
                                <div class="col-6">
                                    <label class="rd-form-label">End Date <span class="required-star">*</span></label>
                                    <input type="date" name="ctc_newenddt" id="ctc_enddate" class="rd-form-control" required value="{{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('Y-m-d') : '' }}">
                                    <small class="d-block mt-1 font-weight-500 text-info" id="max-end-date-display" style="font-size: 0.75rem;"></small>
                                </div>
                            </div>
                            <div class="mb-4">
                                <span class="duration-chip" id="duration-display"><i class="far fa-calendar-alt"></i> Duration: Calculating...</span>
                            </div>

                            @if(in_array(strtoupper($case->ctc_type), ['HG', 'RH']))
                                <div class="row mb-4">
                                    <div class="col-6">
                                        <label class="rd-form-label">Probation (Months)</label>
                                        <input type="number" name="ctc_newprob" id="prob-months-input" class="rd-form-control" value="{{ $case->ctc_newprob ?? 0 }}" min="0" max="12">
                                    </div>
                                    <div class="col-6">
                                        <label class="rd-form-label">Probation Salary (PKR)</label>
                                        <input type="number" name="ctc_newprobsal" id="prob-salary-input" class="rd-form-control" value="{{ $case->ctc_newprobsal }}" placeholder="Optional" min="0">
                                    </div>
                                </div>
                            @endif

                            <div class="value-summary-card">
                                <div class="value-summary-label">Estimated Contract Value</div>
                                <div class="value-summary-amount" id="estimated-value">Rs. {{ number_format((float)($case->ctc_price ?? 0)) }}</div>
                                <small class="text-muted d-block mt-2 font-weight-500">
                                    <i class="fas fa-calculator mr-1"></i> Exact calendar month proration & probation applied
                                </small>
                            </div>
                        </div>

                        <!-- COLUMN 3: PROJECT ALLOCATION -->
                        <div class="col-md-4" style="padding-left: 1.8rem;">
                            <div class="section-header-title">
                                <i class="fas fa-project-diagram"></i> Project Budget Allocation
                            </div>

                            @php
                                $isSingle = empty($monthlyPlanMap) || $case->ctc_prj_id !== null;
                            @endphp

                            <!-- Single Project Card -->
                            <div class="mode-card {{ $isSingle ? 'active' : '' }}" id="card-single">
                                <div class="d-flex align-items-center mb-2">
                                    <input type="radio" name="project_mode" value="single" id="mode-single" class="mr-2" {{ $isSingle ? 'checked' : '' }}>
                                    <label for="mode-single" class="font-weight-bold text-dark mb-0 cursor-pointer">Single Project (Entire Duration)</label>
                                </div>
                                <div class="mode-card-body" id="body-single" style="{{ $isSingle ? '' : 'display: none;' }}">
                                    <div class="form-group mb-0 mt-2">
                                        <label class="rd-form-label small">Associated Project</label>
                                        <select name="ctc_projectcode" class="rd-form-control select2" id="single-project-select" style="width: 100%;">
                                            <option value="">Core / Non-Project</option>
                                            @foreach($projects as $proj)
                                                <option value="{{ $proj->prj_id }}" {{ $case->ctc_prj_id == $proj->prj_id ? 'selected' : '' }}>
                                                    {{ $proj->prj_code }} - {{ $proj->prj_title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Project Card -->
                            <div class="mode-card {{ !$isSingle ? 'active' : '' }}" id="card-monthly">
                                <div class="d-flex align-items-center mb-2">
                                    <input type="radio" name="project_mode" value="monthly" id="mode-monthly" class="mr-2" {{ !$isSingle ? 'checked' : '' }}>
                                    <label for="mode-monthly" class="font-weight-bold text-dark mb-0 cursor-pointer">Split Project Allocation by Month</label>
                                </div>
                                <div class="mode-card-body" id="body-monthly" style="{{ !$isSingle ? '' : 'display: none;' }}">
                                    <label class="rd-form-label small mb-2">Monthly Project Slices</label>
                                    <div style="max-height: 260px; overflow-y: auto; padding-right: 6px;">
                                        <table class="clean-table" id="monthly-project-table">
                                            <thead>
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Assigned Project</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamically populated via JS -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="actions-footer-bar">
                        <div class="text-muted small font-weight-500">
                            <i class="fas fa-info-circle text-warning mr-1"></i> Saving revision updates the terms in Division. You can release back to HR when ready.
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <a href="{{ route('division.contract-cases.show', $case->ctc_id) }}" class="btn-action-cancel mr-2">Cancel</a>
                            <button type="button" class="btn-action-update" id="btn-update-case">
                                <i class="fas fa-save"></i> Save Revision
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<!-- Project Options Template for JS -->
<template id="proj-options">
    <option value="">Core / Non-Project</option>
    @foreach($projects as $proj)
        <option value="{{ $proj->prj_id }}">{{ $proj->prj_code }}</option>
    @endforeach
</template>

@endsection

@push('scripts')
<script src="{{ asset('plugins/inputmask/jquery.inputmask.min.js') }}"></script>
<script>
$(document).ready(function() {
    if ($.fn.inputmask) {
        $('.cnic-mask').inputmask('99999-9999999-9');
    }
    
    if ($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    }

    const initialPlanMap = @json($monthlyPlanMap);

    // ── Radio Toggle Logic ────────────────────────────────────
    $('input[name="project_mode"]').change(function() {
        if (this.value === 'single') {
            $('#card-single').addClass('active');
            $('#card-monthly').removeClass('active');
            $('#body-single').slideDown(200);
            $('#body-monthly').slideUp(200);
        } else {
            $('#card-monthly').addClass('active');
            $('#card-single').removeClass('active');
            $('#body-monthly').slideDown(200);
            $('#body-single').slideUp(200);
        }
    });

    // ── Real-time Prorated Calculations ───────────────────────
    function calculateFinancials() {
        const salary = parseFloat($('#salary-input').val()) || 0;
        const probMonths = parseInt($('#prob-months-input').val()) || 0;
        let probSalary = parseFloat($('#prob-salary-input').val()) || salary;
        if (probSalary <= 0) probSalary = salary;

        const startVal = $('#ctc_startdate').val();
        const endVal = $('#ctc_enddate').val();

        if (startVal && endVal) {
            const start = new Date(startVal);
            const end = new Date(endVal);

            if (end >= start) {
                const diffMs = end - start;
                const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24)) + 1;
                const months = Math.floor(diffDays / 30.417);
                const remainingDays = Math.floor(diffDays % 30.417);

                let durText = months > 0 ? months + ' months ' : '';
                durText += remainingDays > 0 ? remainingDays + ' days' : '';
                $('#duration-display').html('<i class="far fa-calendar-alt text-primary"></i> Duration: <strong>' + durText.trim() + '</strong> (' + diffDays + ' days total)');

                // Month-by-month proration calculation
                let totalEstimated = 0;
                let current = new Date(start.getFullYear(), start.getMonth(), 1);
                const endMonth = new Date(end.getFullYear(), end.getMonth(), 1);
                
                const probCutoff = (probMonths > 0) ? new Date(start.getFullYear(), start.getMonth() + probMonths, start.getDate() - 1) : null;

                while (current <= endMonth) {
                    let mStart = new Date(current.getFullYear(), current.getMonth(), 1);
                    let mEnd = new Date(current.getFullYear(), current.getMonth() + 1, 0);

                    if (mStart < start) mStart = new Date(start);
                    if (mEnd > end) mEnd = new Date(end);

                    const sliceDays = Math.floor((mEnd - mStart) / (1000 * 60 * 60 * 24)) + 1;
                    const daysInFullMonth = new Date(current.getFullYear(), current.getMonth() + 1, 0).getDate();

                    let effectiveSalary = salary;
                    if (probCutoff && mStart <= probCutoff) {
                        if (mEnd <= probCutoff) {
                            effectiveSalary = probSalary;
                            totalEstimated += (probSalary / daysInFullMonth) * sliceDays;
                        } else {
                            const pDays = Math.floor((probCutoff - mStart) / (1000 * 60 * 60 * 24)) + 1;
                            const rDays = sliceDays - pDays;
                            totalEstimated += ((probSalary / daysInFullMonth) * pDays) + ((salary / daysInFullMonth) * rDays);
                        }
                    } else {
                        totalEstimated += (salary / daysInFullMonth) * sliceDays;
                    }

                    current.setMonth(current.getMonth() + 1);
                }

                $('#estimated-value').text('Rs. ' + Math.round(totalEstimated).toLocaleString());
                updateMonthlyProjectRows(start, end);
            } else {
                $('#duration-display').html('<i class="far fa-calendar-alt text-danger"></i> End date must be on or after start date');
                $('#estimated-value').text('Rs. 0');
                $('#monthly-project-table tbody').empty();
            }
        }
    }

    // ── 1-Year Max End Date Calculation ───────────────────────
    function updateMaxEndDate() {
        const startVal = $('#ctc_startdate').val();
        if (startVal) {
            const startDate = new Date(startVal + 'T00:00:00');
            if (!isNaN(startDate.getTime())) {
                const maxDate = new Date(startDate);
                maxDate.setFullYear(maxDate.getFullYear() + 1);
                maxDate.setDate(maxDate.getDate() - 1);

                const yyyy = maxDate.getFullYear();
                const mm = String(maxDate.getMonth() + 1).padStart(2, '0');
                const dd = String(maxDate.getDate()).padStart(2, '0');
                const maxDateStr = `${yyyy}-${mm}-${dd}`;

                $('#ctc_enddate').attr('max', maxDateStr);
                $('#max-end-date-display').html(`<i class="fas fa-info-circle mr-1"></i> Maximum end date: <strong>${maxDateStr}</strong> (1 year from start)`);
                return maxDateStr;
            }
        }
        $('#ctc_enddate').removeAttr('max');
        $('#max-end-date-display').text('');
        return null;
    }

    $('#salary-input, #prob-months-input, #prob-salary-input').on('input', calculateFinancials);
    $('#ctc_startdate').on('change input', function() {
        updateMaxEndDate();
        calculateFinancials();
    });
    $('#ctc_enddate').on('change input', calculateFinancials);

    // Initial check on page load
    updateMaxEndDate();

    const projOptionsHtml = $('#proj-options').html();
    function updateMonthlyProjectRows(start, end) {
        const tbody = $('#monthly-project-table tbody');
        tbody.empty();

        let current = new Date(start.getFullYear(), start.getMonth(), 1);
        const endMonth = new Date(end.getFullYear(), end.getMonth(), 1);

        while (current <= endMonth) {
            const label = current.toLocaleString('default', { month: 'short', year: 'numeric' });
            const key = current.getFullYear() + '-' + String(current.getMonth() + 1).padStart(2, '0');
            const selectedHeadId = initialPlanMap && initialPlanMap[key] ? initialPlanMap[key] : '';

            const newRow = $(`
                <tr>
                    <td class="font-weight-bold text-dark">${label}</td>
                    <td>
                        <select name="monthly_project[${key}]" class="rd-form-control select2-dynamic">
                            ${projOptionsHtml}
                        </select>
                    </td>
                </tr>
            `);
            if (selectedHeadId) {
                newRow.find('select').val(selectedHeadId);
            }
            tbody.append(newRow);
            current.setMonth(current.getMonth() + 1);
        }
        if ($.fn.select2) {
            $('.select2-dynamic').select2({ theme: 'bootstrap4', width: '100%' });
        }
    }

    // Initial calculation on page load
    calculateFinancials();

    // ── Save Logic ────────────────────────────────────────────
    $('#btn-update-case').click(function() {
        const form = $('#contract-case-edit-form')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Client-side 1-year max cap check
        const startVal = $('#ctc_startdate').val();
        const endVal = $('#ctc_enddate').val();
        if (startVal && endVal) {
            const maxDateStr = updateMaxEndDate();
            if (maxDateStr && endVal > maxDateStr) {
                Swal.fire('Validation Error', 'Contract duration cannot exceed 1 year from the start date.', 'error');
                return;
            }
        }

        const formData = new FormData(form);
        
        // Remove project mappings based on mode
        if ($('input[name="project_mode"]:checked').val() === 'monthly') {
            formData.delete('ctc_projectcode');
        }

        $(this).attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Saving Revision...');

        $.ajax({
            url: '{{ route("division.contract-cases.update", $case->ctc_id) }}',
            method: 'POST', // Handled via @method('PUT') inside FormData
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        title: 'Revision Saved',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("division.contract-cases.show", $case->ctc_id) }}';
                    });
                }
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Failed to save revision. Please check form inputs.';
                Swal.fire('Error', msg, 'error');
                $('#btn-update-case').attr('disabled', false).html('<i class="fas fa-save mr-2"></i> Save Revision');
            }
        });
    });
});
</script>
@endpush
