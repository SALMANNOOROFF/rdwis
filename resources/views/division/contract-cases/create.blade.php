@extends('welcome')

@section('content')
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
        text-transform: uppercase;
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

    .main-form-container {
        background-color: var(--rd-surface);
        border: 1px solid var(--rd-border);
        border-radius: 12px;
        padding: 2rem;
        margin-top: 2rem;
    }

    .section-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--rd-text2);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dark-input {
        background-color: var(--rd-surface);
        border: 1.5px solid var(--rd-border2);
        color: var(--rd-text1);
        border-radius: 6px;
        padding: 0.6rem 1rem;
        width: 100%;
        transition: border-color 0.2s;
        font-size: 0.9rem;
    }
    .dark-input:focus {
        outline: none;
        border-color: var(--rd-primary-600);
        background-color: var(--rd-surface);
        color: #fff;
    }
    .dark-input:disabled, .dark-input[readonly] {
        background-color: rgba(255, 255, 255, 0.04) !important;
        border-color: #2d3748 !important;
        color: var(--rd-text3) !important;
        cursor: not-allowed;
    }
    
    .dark-input::placeholder {
        color: #4a5568;
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

    .type-badge-pill {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }

    /* Reference card */
    .reference-card {
        background: rgba(49, 130, 206, 0.08);
        border: 1px solid rgba(49, 130, 206, 0.25);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.2rem;
        font-size: 0.82rem;
    }
    .reference-card-title {
        font-weight: 700;
        color: #60a5fa;
        text-transform: uppercase;
        margin-bottom: 0.5rem;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    /* Financial Setup Value Box */
    .estimated-value-box {
        background-color: var(--rd-neutral-50);
        border: 1px solid var(--rd-border);
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        margin-top: 1.5rem;
    }
    .estimated-value-title {
        font-size: 0.75rem;
        color: var(--rd-text3);
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .estimated-value-amount {
        font-size: 1.8rem;
        font-weight: 800;
        color: #60a5fa;
        margin-top: 0.5rem;
    }

    /* Project Selection Cards */
    .project-card {
        border: 1px solid var(--rd-border);
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        background-color: transparent;
        transition: all 0.2s;
        cursor: pointer;
    }
    .project-card.active {
        border-color: #3182ce;
        background-color: rgba(49, 130, 206, 0.05);
    }
    
    .dark-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .dark-table th {
        font-size: 0.75rem;
        color: var(--rd-text2);
        font-weight: 600;
        text-transform: uppercase;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #2d3748;
    }
    .dark-table td {
        padding: 0.5rem 0;
    }

    .file-upload-wrapper {
        display: flex;
        align-items: center;
        background-color: var(--rd-surface);
        border: 1px solid var(--rd-border);
        border-radius: 6px;
        overflow: hidden;
    }
    .file-upload-button {
        background-color: #2d3748;
        color: var(--rd-text1);
        border: none;
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
        cursor: pointer;
    }
    .file-upload-text {
        padding-left: 1rem;
        font-size: 0.85rem;
        color: var(--rd-text3);
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #2d3748;
    }
    
    .btn-discard {
        background: transparent;
        border: 1px solid #4a5568;
        color: var(--rd-text3);
        padding: 0.6rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
    }
    .btn-save-draft {
        background: #3182ce;
        border: none;
        color: var(--rd-text1);
        padding: 0.6rem 2rem;
        border-radius: 6px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 6px rgba(49, 130, 206, 0.2);
    }
    .btn-save-draft:hover {
        background: #2b6cb0;
    }

    .duration-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(49, 130, 206, 0.15);
        color: #63b3ed;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .input-group-dark {
        display: flex;
    }
    .input-group-dark .prefix {
        background: var(--rd-neutral-200);
        border: 1.5px solid var(--rd-border2);
        border-right: none;
        color: var(--rd-text3);
        padding: 0.6rem 1rem;
        border-radius: 6px 0 0 6px;
        font-weight: 600;
    }
    .input-group-dark input {
        border-radius: 0 6px 6px 0;
        border-left: none;
    }

    /* Select2 overrides */
    .select2-container--bootstrap4 .select2-selection {
        background-color: var(--rd-surface) !important;
        border: 1.5px solid var(--rd-border2) !important;
        color: var(--rd-text1) !important;
    }
    .select2-container--bootstrap4 .select2-selection__rendered {
        color: var(--rd-text1) !important;
    }
    .select2-dropdown {
        background-color: var(--rd-surface) !important;
        border: 1px solid #2d3748 !important;
    }
    .select2-results__option {
        color: var(--rd-text1) !important;
    }
    .select2-results__option--highlighted {
        background-color: #3182ce !important;
    }

    .custom-radio {
        appearance: none;
        width: 16px;
        height: 16px;
        border: 2px solid #4a5568;
        border-radius: 50%;
        outline: none;
        cursor: pointer;
        position: relative;
        margin-right: 8px;
    }
    .custom-radio:checked {
        border-color: #3182ce;
    }
    .custom-radio:checked::after {
        content: '';
        position: absolute;
        width: 8px;
        height: 8px;
        background: #3182ce;
        border-radius: 50%;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    .project-selection-header {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    .project-selection-header label {
        color: var(--rd-text1);
        font-weight: 600;
        font-size: 0.9rem;
        margin: 0;
        cursor: pointer;
    }
</style>

<div class="content-wrapper" style="background-color: var(--rd-surface);">
    <section class="content">
        <div class="dark-contract-wrapper">
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="header-title mb-1">
                        Initiate @if($type == 'Hg') New Hiring @elseif($type == 'Ce') Contract Extension @elseif($type == 'Cr') Contract Renewal @elseif($type == 'Rh') Rehiring @endif Case
                    </h1>
                    <span class="type-badge-pill @if($type == 'Hg') bg-primary @elseif($type == 'Ce') bg-success @elseif($type == 'Cr') bg-warning text-dark @else bg-info @endif">
                        CASE TYPE: {{ strtoupper($type) }}
                    </span>
                </div>
                <a href="{{ route('division.contract-cases.index') }}" class="btn-back"><i class="fas fa-arrow-left mr-2"></i> Back</a>
            </div>

            <!-- Active Case Warning Container -->
            <div id="active-case-alert" class="alert alert-danger mt-3 d-none" style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5;">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span id="active-case-message"></span>
            </div>

            <!-- Main Form Container -->
            <div class="main-form-container">
                <form id="contract-case-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ctc_type" id="ctc_type" value="{{ $type }}">
                    <input type="hidden" name="ctc_emp_id" id="ctc_emp_id" value="">
                    <input type="hidden" name="ctc_ctr_id" id="ctc_ctr_id" value="0">
                    
                    <div class="row">
                        <!-- COLUMN 1: CANDIDATE & DESIGNATION -->
                        <div class="col-md-4 border-right border-secondary" style="border-color: #2d3748 !important; padding-right: 1.5rem;">
                            <div class="section-title">
                                <i class="fas fa-user-tie"></i> CANDIDATE & DESIGNATION
                            </div>

                            @if(in_array(strtoupper($type), ['CR', 'CE', 'RH']))
                                <!-- Employee Selector for Cr/Ce/Rh -->
                                <div class="form-group mb-4">
                                    <label class="dark-label">Select Employee <span class="required-asterisk">*</span></label>
                                    <select id="emp-selector" class="dark-input select2" required style="width: 100%;">
                                        <option value="">-- Choose Employee --</option>
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->emp_id }}">
                                                {{ $emp->emp_name }} ({{ $emp->emp_id }}) {{ $emp->emp_rank ? '- '.$emp->emp_rank : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Current Contract Info Card -->
                                <div id="current-contract-card" class="reference-card d-none">
                                    <div class="reference-card-title"><i class="fas fa-history mr-1"></i> Current Active Contract Reference</div>
                                    <div class="row">
                                        <div class="col-6"><span class="text-muted">Designation:</span> <strong id="ref-desig" class="text-white">-</strong></div>
                                        <div class="col-6"><span class="text-muted">Grade:</span> <strong id="ref-grade" class="text-white">-</strong></div>
                                        <div class="col-6 mt-1"><span class="text-muted">Salary:</span> <strong id="ref-salary" class="text-info">-</strong></div>
                                        <div class="col-6 mt-1"><span class="text-muted">Type:</span> <strong id="ref-type" class="text-white">-</strong></div>
                                        <div class="col-12 mt-1"><span class="text-muted">Contract Expiry:</span> <strong id="ref-expiry" class="text-warning">-</strong></div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="form-group mb-4">
                                <label class="dark-label">Full Candidate Name <span class="required-asterisk">*</span></label>
                                <input type="text" name="ctc_empnamecomp" id="ctc_empnamecomp" class="dark-input" required placeholder="Enter full name" @if(in_array(strtoupper($type), ['CR', 'CE'])) readonly @endif>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="dark-label">Designation <span class="required-asterisk">*</span></label>
                                    <input type="text" name="ctc_newjobtitle" id="ctc_newjobtitle" class="dark-input" required @if(strtoupper($type) === 'CE') readonly @endif>
                                </div>
                                <div class="col-6">
                                    <label class="dark-label">Grade <span class="required-asterisk">*</span></label>
                                    <select name="ctc_newgrade" id="ctc_newgrade" class="dark-input" required @if(strtoupper($type) === 'CE') disabled @endif>
                                        <option value="">- Select -</option>
                                        <option value="Director">Director</option>
                                        <option value="Manager">Manager</option>
                                        <option value="PRO">PRO</option>
                                        <option value="SRO">SRO</option>
                                        <option value="RO">RO</option>
                                        <option value="RA">RA</option>
                                        <option value="EA">EA</option>
                                        <option value="PRA">PRA</option>
                                        <option value="SRA">SRA</option>
                                        <option value="JRA">JRA</option>
                                        <option value="SRT">SRT</option>
                                        <option value="RT">RT</option>
                                        <option value="JRT">JRT</option>
                                        <option value="LA">LA</option>
                                        <option value="Internee">Internee</option>
                                        <option value="Worker">Worker</option>
                                    </select>
                                    @if(strtoupper($type) === 'CE')
                                        <input type="hidden" name="ctc_newgrade" id="hidden_ctc_newgrade" value="">
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-6">
                                    <label class="dark-label">Division</label>
                                    <input type="text" class="dark-input" value="{{ $divisionName }}" readonly>
                                </div>
                                <div class="col-6">
                                    <label class="dark-label">Employment Type <span class="required-asterisk">*</span></label>
                                    <select name="ctc_emp_type" id="ctc_emp_type" class="dark-input" required @if(strtoupper($type) === 'CE') disabled @endif>
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
                                    <label class="dark-label">CNIC</label>
                                    <input type="text" name="ctc_cnic" id="ctc_cnic" class="dark-input cnic-mask" placeholder="99999-9999999-9">
                                </div>
                                <div class="col-6">
                                    <label class="dark-label">Contact No.</label>
                                    <input type="text" name="ctc_contact" id="ctc_contact" class="dark-input" placeholder="03xx-xxxxxxx">
                                </div>
                            </div>

                            @if(strtoupper($type) === 'CE')
                                <div class="form-group mb-4">
                                    <label class="dark-label text-warning font-weight-bold">Extension Reason / Remarks <span class="required-asterisk">*</span></label>
                                    <textarea name="ctc_terminremarks" id="ctc_terminremarks" class="dark-input border-warning" rows="3" required placeholder="Specify formal reason/justification for contract extension..."></textarea>
                                </div>
                            @elseif(strtoupper($type) === 'CR')
                                <div class="form-group mb-4" id="cr-termin-remarks-group" style="display: none;">
                                    <label class="dark-label text-warning">Early Termination / Renewal Date Override Reason <span class="required-asterisk">*</span></label>
                                    <textarea name="ctc_terminremarks" id="ctc_terminremarks" class="dark-input border-warning" rows="2" placeholder="Required when new start date does not immediately follow previous contract end date..."></textarea>
                                </div>
                            @endif

                            <div class="form-group mb-4">
                                <label class="dark-label">Job Description @if($type == 'Hg') <span class="required-asterisk">*</span> @endif</label>
                                <textarea name="ctc_jd" class="dark-input" rows="2" @if($type == 'Hg') required @endif placeholder="Summary of duties"></textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label class="dark-label">Justification / Notes</label>
                                <textarea name="remarks" class="dark-input" rows="2" placeholder="Additional remarks"></textarea>
                            </div>

                            <div class="form-group">
                                <label class="dark-label">Attach CV / Documents</label>
                                <div class="file-upload-wrapper">
                                    <button type="button" class="file-upload-button" onclick="document.getElementById('cv-upload').click()">Choose Files</button>
                                    <span class="file-upload-text" id="file-name">No file chosen</span>
                                    <input type="file" id="cv-upload" name="cv_file" class="d-none" accept=".pdf,.doc,.docx" onchange="document.getElementById('file-name').innerText = this.files[0] ? this.files[0].name : 'No file chosen'">
                                </div>
                            </div>
                        </div>

                        <!-- COLUMN 2: FINANCIAL SETUP -->
                        <div class="col-md-4 border-right border-secondary" style="border-color: #2d3748 !important; padding-left: 1.5rem; padding-right: 1.5rem;">
                            <div class="section-title">
                                <i class="fas fa-coins"></i> FINANCIAL SETUP
                            </div>

                            <div class="form-group mb-4">
                                <label class="dark-label">Monthly Base Salary (PKR) <span class="required-asterisk">*</span></label>
                                <div class="input-group-dark">
                                    <span class="prefix">Rs.</span>
                                    <input type="number" name="ctc_newsalary" id="salary-input" class="dark-input font-weight-bold" style="font-size: 1.1rem; text-align: right;" required min="0" @if(strtoupper($type) === 'CE') readonly @endif>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="dark-label">Start Date <span class="required-asterisk">*</span></label>
                                    <input type="date" name="ctc_newstartdt" id="ctc_startdate" class="dark-input" required @if(strtoupper($type) === 'CE') readonly @endif>
                                </div>
                                <div class="col-6">
                                    <label class="dark-label">End Date <span class="required-asterisk">*</span></label>
                                    <input type="date" name="ctc_newenddt" id="ctc_enddate" class="dark-input" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <span class="duration-badge" id="duration-display"><i class="far fa-calendar-alt"></i> Duration: 0 months</span>
                            </div>

                            @if(in_array(strtoupper($type), ['HG', 'RH']))
                                <div class="row mb-4">
                                    <div class="col-6">
                                        <label class="dark-label">Probation (Months)</label>
                                        <input type="number" name="ctc_newprob" id="prob-months-input" class="dark-input" value="3" min="0" max="12">
                                    </div>
                                    <div class="col-6">
                                        <label class="dark-label">Probation Salary (PKR)</label>
                                        <input type="number" name="ctc_newprobsal" id="prob-salary-input" class="dark-input" placeholder="Optional" min="0">
                                    </div>
                                </div>
                            @endif

                            <div class="estimated-value-box">
                                <div class="estimated-value-title">Estimated Contract Value</div>
                                <div class="estimated-value-amount" id="estimated-value">Rs. 0</div>
                                <small class="text-muted d-block mt-1">Calculated based on calendar proration & probation</small>
                            </div>
                        </div>

                        <!-- COLUMN 3: PROJECT SELECTION -->
                        <div class="col-md-4" style="padding-left: 1.5rem;">
                            <div class="section-title">
                                <i class="fas fa-project-diagram"></i> PROJECT SELECTION
                            </div>

                            <!-- Single Project Card -->
                            <div class="project-card active" id="card-single">
                                <div class="project-selection-header">
                                    <input type="radio" name="project_mode" value="single" id="mode-single" class="custom-radio" checked>
                                    <label for="mode-single">Single Project (Whole Contract)</label>
                                </div>
                                <div class="project-card-body" id="body-single">
                                    <div class="form-group mb-0">
                                        <label class="dark-label">Associated Project</label>
                                        <select name="ctc_projectcode" class="dark-input select2" id="single-project-select" style="width: 100%;">
                                            <option value="">Core / Non-Project</option>
                                            @foreach($projects as $proj)
                                                <option value="{{ $proj->prj_id }}">{{ $proj->prj_code }} - {{ $proj->prj_title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Monthly Project Card -->
                            <div class="project-card" id="card-monthly">
                                <div class="project-selection-header">
                                    <input type="radio" name="project_mode" value="monthly" id="mode-monthly" class="custom-radio">
                                    <label for="mode-monthly">Different Project Each Month</label>
                                </div>
                                <div class="project-card-body" id="body-monthly" style="display: none;">
                                    <label class="dark-label mb-2">Monthly Project Allocations</label>
                                    <div style="max-height: 250px; overflow-y: auto; padding-right: 10px;">
                                        <table class="dark-table" id="monthly-project-table">
                                            <thead>
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Project</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic generated rows -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Actions Footer -->
                    <div class="form-actions">
                        <div class="text-muted text-sm"><i class="fas fa-info-circle mr-1"></i> You can save as draft and submit later.</div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('division.contract-cases.index') }}" class="btn-discard mr-3">Discard</a>
                            <button type="button" class="btn-save-draft" id="btn-save-draft"><i class="fas fa-save"></i> SAVE DRAFT</button>
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
                    }

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
                $('#duration-display').html('<i class="far fa-calendar-alt"></i> Duration: ' + durText.trim() + ' (' + diffDays + ' days total)');

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

    $('#salary-input, #prob-months-input, #prob-salary-input').on('input', calculateFinancials);
    $('#ctc_startdate, #ctc_enddate').on('change', calculateFinancials);

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
                    <td class="font-weight-bold" style="color: var(--rd-text1);">${label}</td>
                    <td>
                        <select name="monthly_project[${key}]" class="dark-input select2-dynamic">
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

        const formData = new FormData(form);
        
        // Remove project mappings based on mode
        if ($('input[name="project_mode"]:checked').val() === 'monthly') {
            formData.delete('ctc_projectcode');
        }

        $(this).attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> SAVING...');

        $.ajax({
            url: '{{ route("division.contract-cases.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        title: 'Saved',
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
                $('#btn-save-draft').attr('disabled', false).html('<i class="fas fa-save"></i> SAVE DRAFT');
            }
        });
    });
});
</script>
@endpush
