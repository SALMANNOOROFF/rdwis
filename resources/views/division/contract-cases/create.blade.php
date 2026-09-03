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

    .btn-action-save {
        background: var(--rd-primary-600);
        border: none;
        color: #FFFFFF;
        padding: 0.7rem 2.2rem;
        border-radius: 8px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 12px rgba(95, 120, 88, 0.25);
        transition: all 0.2s;
    }
    .btn-action-save:hover {
        background: var(--rd-primary-700);
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(95, 120, 88, 0.35);
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
                        Initiate @if($type == 'Hg') New Hiring @elseif($type == 'Ce') Contract Extension @elseif($type == 'Cr') Contract Renewal @elseif($type == 'Rh') Rehiring @endif Case
                    </h1>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="type-pill @if($type == 'Hg') bg-primary text-white @elseif($type == 'Ce') bg-success text-white @elseif($type == 'Cr') bg-warning text-dark @else bg-info text-white @endif">
                            <i class="fas fa-tag"></i> TYPE: {{ strtoupper($type) }}
                        </span>
                        <span class="badge badge-light border text-muted px-3 py-1 font-weight-bold ml-2">
                            DIVISION DRAFT
                        </span>
                    </div>
                </div>
                <a href="{{ route('division.contract-cases.index') }}" class="btn-back-link">
                    <i class="fas fa-arrow-left"></i> Back to Hub
                </a>
            </div>

            <!-- Active Case Warning Container -->
            <div id="active-case-alert" class="alert alert-danger mt-3 d-none shadow-sm" style="border-radius: 10px; border-left: 5px solid #ef4444;">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span id="active-case-message" class="font-weight-600"></span>
            </div>

            <!-- Main Form Card -->
            <div class="form-surface-card">
                <form id="contract-case-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ctc_type" id="ctc_type" value="{{ $type }}">
                    <input type="hidden" name="ctc_emp_id" id="ctc_emp_id" value="">
                    <input type="hidden" name="ctc_ctr_id" id="ctc_ctr_id" value="0">
                    
                    <div class="row">
                        <!-- COLUMN 1: CANDIDATE & DESIGNATION -->
                        <div class="col-md-4 border-right" style="border-color: var(--rd-neutral-200) !important; padding-right: 1.8rem;">
                            <div class="section-header-title">
                                <i class="fas fa-user-tie"></i> Candidate & Designation
                            </div>

                            @if(in_array(strtoupper($type), ['CR', 'CE', 'RH']))
                                <!-- Employee Selector for Cr/Ce/Rh -->
                                <div class="form-group mb-4">
                                    <label class="rd-form-label">
                                        @if(strtoupper($type) === 'RH')
                                            Select Separated Employee (Released / Terminated) <span class="required-star">*</span>
                                        @else
                                            Select Active Employee <span class="required-star">*</span>
                                        @endif
                                    </label>
                                    <select id="emp-selector" class="rd-form-control select2" required style="width: 100%;">
                                        <option value="">-- Choose Employee --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->emp_id }}" {{ request('emp_id') == $emp->emp_id ? 'selected' : '' }}>
                                                {{ $emp->emp_name }} ({{ $emp->emp_id }}) [{{ $emp->emp_status }}] {{ $emp->emp_rank ? '- '.$emp->emp_rank : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Current Contract Reference Card -->
                                <div id="current-contract-card" class="reference-box d-none">
                                    <div class="reference-box-title"><i class="fas fa-id-card"></i> Active Contract Reference</div>
                                    <div class="row small">
                                        <div class="col-6 mb-1"><span class="text-muted">Designation:</span> <strong id="ref-desig" class="text-dark d-block">-</strong></div>
                                        <div class="col-6 mb-1"><span class="text-muted">Grade:</span> <strong id="ref-grade" class="text-dark d-block">-</strong></div>
                                        <div class="col-6"><span class="text-muted">Salary:</span> <strong id="ref-salary" class="text-primary font-weight-bold d-block">-</strong></div>
                                        <div class="col-6"><span class="text-muted">Type:</span> <strong id="ref-type" class="text-dark d-block">-</strong></div>
                                        <div class="col-12 mt-2 pt-2 border-top border-primary-100"><span class="text-muted">Contract Expiry:</span> <strong id="ref-expiry" class="text-danger font-weight-bold">-</strong></div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="form-group mb-4">
                                <label class="rd-form-label">Candidate Full Name <span class="required-star">*</span></label>
                                <input type="text" name="ctc_empnamecomp" id="ctc_empnamecomp" class="rd-form-control font-weight-bold" required placeholder="Enter full candidate name" @if(in_array(strtoupper($type), ['CR', 'CE'])) readonly @endif>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="rd-form-label">Designation <span class="required-star">*</span></label>
                                    <input type="text" name="ctc_newjobtitle" id="ctc_newjobtitle" class="rd-form-control" required placeholder="e.g. Research Associate" @if(strtoupper($type) === 'CE') readonly @endif>
                                </div>
                                <div class="col-6">
                                    <label class="rd-form-label">Grade / Scale <span class="required-star">*</span></label>
                                    <select name="ctc_newgrade" id="ctc_newgrade" class="rd-form-control" required @if(strtoupper($type) === 'CE') disabled @endif>
                                        <option value="">- Select -</option>
                                        @foreach(['Director', 'Manager', 'PRO', 'SRO', 'RO', 'RA', 'EA', 'PRA', 'SRA', 'JRA', 'SRT', 'RT', 'JRT', 'LA', 'Internee', 'Worker'] as $gr)
                                            <option value="{{ $gr }}">{{ $gr }}</option>
                                        @endforeach
                                    </select>
                                    @if(strtoupper($type) === 'CE')
                                        <input type="hidden" name="ctc_newgrade" id="hidden_ctc_newgrade" value="">
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
                                    <select name="ctc_emp_type" id="ctc_emp_type" class="rd-form-control" required @if(strtoupper($type) === 'CE') disabled @endif>
                                        <option value="Full Time">Full Time</option>
                                        <option value="Part Time">Part Time</option>
                                    </select>
                                    @if(strtoupper($type) === 'CE')
                                        <input type="hidden" name="ctc_emp_type" id="hidden_ctc_emp_type" value="Full Time">
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="rd-form-label">CNIC</label>
                                    <input type="text" name="ctc_cnic" id="ctc_cnic" class="rd-form-control cnic-mask" placeholder="99999-9999999-9">
                                </div>
                                <div class="col-6">
                                    <label class="rd-form-label">Contact Number</label>
                                    <input type="text" name="ctc_contact" id="ctc_contact" class="rd-form-control" placeholder="03xx-xxxxxxx">
                                </div>
                            </div>

                            @if(strtoupper($type) === 'CE')
                                <div class="form-group mb-4">
                                    <label class="rd-form-label text-warning font-weight-bold">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Extension Reason / Justification <span class="required-star">*</span>
                                    </label>
                                    <textarea name="ctc_terminremarks" id="ctc_terminremarks" class="rd-form-control border-warning" rows="3" required placeholder="Specify formal reason/justification for contract extension..."></textarea>
                                </div>
                            @elseif(strtoupper($type) === 'CR')
                                <div class="form-group mb-4" id="cr-termin-remarks-group" style="display: none;">
                                    <label class="rd-form-label text-warning font-weight-bold">
                                        <i class="fas fa-clock mr-1"></i> Early Termination / Renewal Date Override Reason <span class="required-star">*</span>
                                    </label>
                                    <textarea name="ctc_terminremarks" id="ctc_terminremarks" class="rd-form-control border-warning" rows="2" placeholder="Required when new start date does not immediately follow previous contract end date..."></textarea>
                                </div>
                            @endif

                            <div class="form-group mb-4">
                                <label class="rd-form-label">Job Description / Summary @if($type == 'Hg') <span class="required-star">*</span> @endif</label>
                                <textarea name="ctc_jd" class="rd-form-control" rows="2" @if($type == 'Hg') required @endif placeholder="Summary of responsibilities..."></textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label class="rd-form-label">Justification / Notes</label>
                                <textarea name="remarks" class="rd-form-control" rows="2" placeholder="Additional notes or references..."></textarea>
                            </div>

                            <div class="form-group">
                                <label class="rd-form-label">Attach CV / Supporting Documents</label>
                                <div class="file-input-box">
                                    <button type="button" class="file-input-btn" onclick="document.getElementById('cv-upload').click()">Browse File</button>
                                    <span class="file-input-label" id="file-name">No file chosen</span>
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
                                    <input type="number" name="ctc_newsalary" id="salary-input" class="rd-form-control font-weight-bold text-success" style="font-size: 1.15rem; text-align: right;" required min="0" placeholder="0" @if(strtoupper($type) === 'CE') readonly @endif>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="rd-form-label">Start Date <span class="required-star">*</span></label>
                                    <input type="date" name="ctc_newstartdt" id="ctc_startdate" class="rd-form-control" required @if(strtoupper($type) === 'CE') readonly @endif>
                                </div>
                                <div class="col-6">
                                    <label class="rd-form-label">End Date <span class="required-star">*</span></label>
                                    <input type="date" name="ctc_newenddt" id="ctc_enddate" class="rd-form-control" required>
                                    <small class="d-block mt-1 font-weight-500 text-info" id="max-end-date-display" style="font-size: 0.75rem;"></small>
                                </div>
                            </div>
                            <div class="mb-4">
                                <span class="duration-chip" id="duration-display"><i class="far fa-calendar-alt"></i> Duration: 0 months</span>
                            </div>

                            @if(in_array(strtoupper($type), ['HG', 'RH']))
                                <div class="row mb-4">
                                    <div class="col-6">
                                        <label class="rd-form-label">Probation (Months)</label>
                                        <input type="number" name="ctc_newprob" id="prob-months-input" class="rd-form-control" value="3" min="0" max="12">
                                    </div>
                                    <div class="col-6">
                                        <label class="rd-form-label">Probation Salary (PKR)</label>
                                        <input type="number" name="ctc_newprobsal" id="prob-salary-input" class="rd-form-control" placeholder="Optional" min="0">
                                    </div>
                                </div>
                            @endif

                            <div class="value-summary-card">
                                <div class="value-summary-label">Estimated Contract Value</div>
                                <div class="value-summary-amount" id="estimated-value">Rs. 0</div>
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

                            <!-- Single Project Card -->
                            <div class="mode-card active" id="card-single">
                                <div class="d-flex align-items-center mb-2">
                                    <input type="radio" name="project_mode" value="single" id="mode-single" class="mr-2" checked>
                                    <label for="mode-single" class="font-weight-bold text-dark mb-0 cursor-pointer">Single Project (Entire Duration)</label>
                                </div>
                                <div class="mode-card-body" id="body-single">
                                    <div class="form-group mb-0 mt-2">
                                        <label class="rd-form-label small">Associated Project</label>
                                        <select name="ctc_projectcode" class="rd-form-control select2" id="single-project-select" style="width: 100%;">
                                            <option value="">Core / Non-Project</option>
                                            @foreach($projects as $proj)
                                                <option value="{{ $proj->prj_id }}">{{ $proj->prj_code }} - {{ $proj->prj_title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Project Card -->
                            <div class="mode-card" id="card-monthly">
                                <div class="d-flex align-items-center mb-2">
                                    <input type="radio" name="project_mode" value="monthly" id="mode-monthly" class="mr-2">
                                    <label for="mode-monthly" class="font-weight-bold text-dark mb-0 cursor-pointer">Split Project Allocation by Month</label>
                                </div>
                                <div class="mode-card-body" id="body-monthly" style="display: none;">
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
                            <i class="fas fa-info-circle text-primary mr-1"></i> Case will be saved as Draft in Division. You can review and release to HR when ready.
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <a href="{{ route('division.contract-cases.index') }}" class="btn-action-cancel mr-2">Discard</a>
                            <button type="button" class="btn-action-save" id="btn-save-draft">
                                <i class="fas fa-save"></i> Save Draft
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

    let defaultExpectedStart = null;

    // ── Employee Selector AJAX Handler (Cr, Ce, Rh) ───────────
    $('#emp-selector').on('change', function() {
        const empId = $(this).val();
        if (!empId) {
            $('#current-contract-card').addClass('d-none');
            $('#active-case-alert').addClass('d-none');
            $('#btn-save-draft').attr('disabled', false);
            return;
        }

        const url = "{{ url('division/contract-cases/employee-contract') }}/" + encodeURIComponent(empId);

        $.ajax({
            url: url,
            method: 'GET',
            success: function(res) {
                if (res.has_active_case) {
                    $('#active-case-message').text(res.message);
                    $('#active-case-alert').removeClass('d-none');
                    $('#btn-save-draft').attr('disabled', true);
                    Swal.fire('Active Case In Progress', res.message, 'warning');
                    return;
                }

                $('#active-case-alert').addClass('d-none');
                $('#btn-save-draft').attr('disabled', false);

                if (res.employee) {
                    $('#ctc_emp_id').val(res.employee.emp_id);
                    $('#ctc_empnamecomp').val(res.employee.emp_name);
                    if (res.employee.emp_cnic) {
                        $('#ctc_cnic').val(res.employee.emp_cnic);
                    }
                }

                if (res.last_contract) {
                    const lc = res.last_contract;
                    $('#ctc_ctr_id').val(lc.ctr_id);

                    // Show Reference Card
                    $('#ref-desig').text(lc.ctr_jobtitle || 'N/A');
                    $('#ref-grade').text(lc.ctr_grade || 'N/A');
                    $('#ref-salary').text('Rs. ' + Number(lc.ctr_salary || 0).toLocaleString());
                    $('#ref-type').text(lc.ctr_type || 'Full Time');
                    $('#ref-expiry').text(lc.effective_enddt || 'N/A');
                    $('#current-contract-card').removeClass('d-none');

                    const caseType = $('#ctc_type').val().toUpperCase();

                    if (caseType === 'CR') {
                        // Renewal: Pre-fill editable terms + set default dates
                        $('#ctc_newjobtitle').val(lc.ctr_jobtitle || '');
                        $('#ctc_newgrade').val(lc.ctr_grade || '');
                        $('#salary-input').val(lc.ctr_salary || 0);
                        $('#ctc_emp_type').val(lc.ctr_type === 'Part Time' ? 'Part Time' : 'Full Time');

                        defaultExpectedStart = lc.suggested_cr_start;
                        $('#ctc_startdate').val(lc.suggested_cr_start);
                        $('#ctc_enddate').val(lc.suggested_cr_end);

                    } else if (caseType === 'CE') {
                        // Extension: Pre-fill and lock terms + set continuation date
                        $('#ctc_newjobtitle').val(lc.ctr_jobtitle || '');
                        $('#ctc_newgrade').val(lc.ctr_grade || '');
                        $('#hidden_ctc_newgrade').val(lc.ctr_grade || '');
                        $('#salary-input').val(lc.ctr_salary || 0);
                        $('#ctc_emp_type').val(lc.ctr_type === 'Part Time' ? 'Part Time' : 'Full Time');
                        $('#hidden_ctc_emp_type').val(lc.ctr_type === 'Part Time' ? 'Part Time' : 'Full Time');

                        $('#ctc_startdate').val(lc.ctr_startdt);
                        $('#ctc_enddate').val(lc.suggested_ce_end);

                    } else if (caseType === 'RH') {
                        // Rehiring: Pre-fill past designation/grade & default 1-year term
                        $('#ctc_newjobtitle').val(lc.ctr_jobtitle || '');
                        $('#ctc_newgrade').val(lc.ctr_grade || '');
                        $('#salary-input').val(lc.ctr_salary || 0);
                        $('#ctc_emp_type').val(lc.ctr_type === 'Part Time' ? 'Part Time' : 'Full Time');

                        defaultExpectedStart = lc.suggested_rh_start;
                        $('#ctc_startdate').val(lc.suggested_rh_start);
                        $('#ctc_enddate').val(lc.suggested_rh_end);
                    }

                    updateMaxEndDate();
                    calculateFinancials();
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to fetch employee details.', 'error');
            }
        });
    });

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

                // Check Cr Early termination mismatch
                const caseType = $('#ctc_type').val().toUpperCase();
                if (caseType === 'CR' && defaultExpectedStart) {
                    if (startVal !== defaultExpectedStart) {
                        $('#cr-termin-remarks-group').slideDown(200);
                        $('#ctc_terminremarks').attr('required', true);
                    } else {
                        $('#cr-termin-remarks-group').slideUp(200);
                        $('#ctc_terminremarks').attr('required', false);
                    }
                }

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

    // Initial check on page load if start date is pre-filled
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
            tbody.append(newRow);
            current.setMonth(current.getMonth() + 1);
        }
        if ($.fn.select2) {
            $('.select2-dynamic').select2({ theme: 'bootstrap4', width: '100%' });
        }
    }

    // ── Save Logic ────────────────────────────────────────────
    $('#btn-save-draft').click(function() {
        const form = $('#contract-case-form')[0];
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

        $(this).attr('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Saving Draft...');

        $.ajax({
            url: '{{ route("division.contract-cases.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        title: 'Draft Saved',
                        text: res.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("division.contract-cases.index") }}';
                    });
                }
            },
            error: function(err) {
                const msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Failed to save case. Please check form inputs.';
                Swal.fire('Error', msg, 'error');
                $('#btn-save-draft').attr('disabled', false).html('<i class="fas fa-save mr-2"></i> Save Draft');
            }
        });
    });

    // Auto-trigger load if employee is preselected from query param
    if ($('#emp-selector').val()) {
        $('#emp-selector').trigger('change');
    }
});
</script>
@endpush
