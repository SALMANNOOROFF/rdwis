@extends('welcome')

@section('content')
<!-- SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* Custom Dark Theme specifically for this page */
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
        text-transform: uppercase;
    }

    .dark-contract-wrapper .btn-back {
        background: transparent;
        border: 1px solid #2d3748;
        color: #a0aec0;
        font-weight: 500;
        border-radius: 6px;
        padding: 0.4rem 1rem;
        transition: all 0.2s;
    }
    .dark-contract-wrapper .btn-back:hover {
        background: #2d3748;
        color: #fff;
    }

    .main-form-container {
        background-color: #1a1d24;
        border: 1px solid #2d3748;
        border-radius: 12px;
        padding: 2rem;
        margin-top: 2rem;
    }

    .section-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dark-input {
        background-color: #0f1219; /* Very deep dark matching screenshot */
        border: 1px solid #1f2937; /* Faint gray-blue border */
        color: #f8fafc;
        border-radius: 6px;
        padding: 0.6rem 1rem;
        width: 100%;
        transition: border-color 0.2s;
        font-size: 0.9rem;
    }
    .dark-input:focus {
        outline: none;
        border-color: #3b82f6; /* Modern blue highlight */
        background-color: #0f1219;
        color: #fff;
    }
    
    .dark-input::placeholder {
        color: #4a5568;
    }

    .dark-label {
        font-size: 0.8rem;
        color: #a0aec0;
        margin-bottom: 0.4rem;
        display: block;
    }

    .required-asterisk {
        color: #e53e3e;
    }

    /* Financial Setup Value Box */
    .estimated-value-box {
        background-color: #121826;
        border: 1px solid #1a202c;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        margin-top: 1.5rem;
    }
    .estimated-value-title {
        font-size: 0.75rem;
        color: #a0aec0;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .estimated-value-amount {
        font-size: 1.8rem;
        font-weight: 800;
        color: #ffffff;
        margin-top: 0.5rem;
    }

    /* Project Selection Cards */
    .project-card {
        border: 1px solid #2d3748;
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
    
    /* Monthly Table */
    .dark-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .dark-table th {
        font-size: 0.75rem;
        color: #718096;
        font-weight: 600;
        text-transform: uppercase;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #2d3748;
    }
    .dark-table td {
        padding: 0.5rem 0;
    }
    .dark-table select {
        padding: 0.4rem;
        height: auto;
    }

    /* Custom File Input */
    .file-upload-wrapper {
        display: flex;
        align-items: center;
        background-color: #12141a;
        border: 1px solid #2d3748;
        border-radius: 6px;
        overflow: hidden;
    }
    .file-upload-button {
        background-color: #2d3748;
        color: #e2e8f0;
        border: none;
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
        cursor: pointer;
    }
    .file-upload-text {
        padding-left: 1rem;
        font-size: 0.85rem;
        color: #a0aec0;
    }

    /* Action Buttons */
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
        color: #a0aec0;
        padding: 0.6rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
    }
    .btn-save-draft {
        background: #3182ce;
        border: none;
        color: #ffffff;
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

    /* Input Group */
    .input-group-dark {
        display: flex;
    }
    .input-group-dark .prefix {
        background: #1f2937;
        border: 1px solid #1f2937;
        border-right: none;
        color: #94a3b8;
        padding: 0.6rem 1rem;
        border-radius: 6px 0 0 6px;
        font-weight: 600;
    }
    .input-group-dark input {
        border-radius: 0 6px 6px 0;
        border-left: none;
    }

    /* Select2 Dark Theme Overrides */
    .select2-container--bootstrap4 .select2-selection {
        background-color: #12141a !important;
        border: 1px solid #2d3748 !important;
        color: #e2e8f0 !important;
    }
    .select2-container--bootstrap4 .select2-selection__rendered {
        color: #e2e8f0 !important;
    }
    .select2-dropdown {
        background-color: #1a1d24 !important;
        border: 1px solid #2d3748 !important;
    }
    .select2-results__option {
        color: #e2e8f0 !important;
    }
    .select2-results__option--highlighted {
        background-color: #3182ce !important;
    }

    /* Radio button custom */
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
        color: #e2e8f0;
        font-weight: 600;
        font-size: 0.9rem;
        margin: 0;
        cursor: pointer;
    }
</style>

<div class="content-wrapper" style="background-color: #12141a;">
    <section class="content">
        <div class="dark-contract-wrapper">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="header-title">
                    Initiate @if($type == 'Hg') New Hiring @elseif($type == 'Ce') Extension @elseif($type == 'Cr') Renewal @elseif($type == 'Rh') Rehiring @endif Case
                </h1>
                <a href="{{ route('division.contract-cases.index') }}" class="btn-back"><i class="fas fa-arrow-left mr-2"></i> Back</a>
            </div>

            <!-- Main Form Container -->
            <div class="main-form-container">
        <form id="contract-case-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ctc_type" value="{{ $type }}">
            
            <div class="row">
                <!-- COLUMN 1: CANDIDATE & DESIGNATION -->
                <div class="col-md-4 border-right border-secondary" style="border-color: #2d3748 !important;">
                    <div class="section-title">
                        <i class="fas fa-user-tie"></i> CANDIDATE & DESIGNATION
                    </div>
                    
                    <div class="form-group mb-4">
                        <label class="dark-label">Full Candidate Name <span class="required-asterisk">*</span></label>
                        <input type="text" name="ctc_empnamecomp" class="dark-input" required placeholder="Enter full name">
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="dark-label">Designation <span class="required-asterisk">*</span></label>
                            <input type="text" name="ctc_newjobtitle" class="dark-input" required>
                        </div>
                        <div class="col-6">
                            <label class="dark-label">Grade <span class="required-asterisk">*</span></label>
                            <select name="ctc_newgrade" class="dark-input" required>
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
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="dark-label">Division</label>
                            <input type="text" class="dark-input" value="{{ $divisionName }}" readonly style="background: #1a1d24; border-color: #2d3748; color: #94a3b8;">
                        </div>
                        <div class="col-6">
                            <label class="dark-label">Employment Type <span class="required-asterisk">*</span></label>
                            <select name="ctc_emp_type" class="dark-input" required>
                                <option value="">- Select -</option>
                                <option value="Full Time">Full Time</option>
                                <option value="Part Time">Part Time</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="dark-label">Job Description <span class="required-asterisk">*</span></label>
                        <textarea name="ctc_jd" class="dark-input" rows="3" required></textarea>
                    </div>

                    <div class="form-group mb-4">
                        <label class="dark-label">Justification</label>
                        <textarea name="remarks" class="dark-input" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="dark-label">Attach Documents</label>
                        <div class="file-upload-wrapper">
                            <button type="button" class="file-upload-button" onclick="document.getElementById('cv-upload').click()">Choose Files</button>
                            <span class="file-upload-text" id="file-name">No file chosen</span>
                            <input type="file" id="cv-upload" name="cv_file" class="d-none" accept=".pdf,.doc,.docx" onchange="document.getElementById('file-name').innerText = this.files[0] ? this.files[0].name : 'No file chosen'">
                        </div>
                    </div>
                </div>

                <!-- COLUMN 2: FINANCIAL SETUP -->
                <div class="col-md-4 border-right border-secondary" style="border-color: #2d3748 !important; padding-left: 2rem; padding-right: 2rem;">
                    <div class="section-title">
                        <i class="fas fa-coins"></i> FINANCIAL SETUP
                    </div>

                    <div class="form-group mb-4">
                        <label class="dark-label">Monthly Base Salary (PKR) <span class="required-asterisk">*</span></label>
                        <div class="input-group-dark">
                            <span class="prefix">Rs.</span>
                            <input type="number" name="ctc_newsalary" id="salary-input" class="dark-input font-weight-bold" style="font-size: 1.1rem; text-align: right;" required min="0">
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-6">
                            <label class="dark-label">Start Date <span class="required-asterisk">*</span></label>
                            <input type="date" name="ctc_newstartdt" id="ctc_startdate" class="dark-input" required>
                        </div>
                        <div class="col-6">
                            <label class="dark-label">End Date <span class="required-asterisk">*</span></label>
                            <input type="date" name="ctc_newenddt" id="ctc_enddate" class="dark-input" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <span class="duration-badge" id="duration-display"><i class="far fa-calendar-alt"></i> Duration: 0 months</span>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="dark-label">Probation (Months)</label>
                            <input type="number" name="ctc_newprob" class="dark-input" value="3" min="0" max="6">
                        </div>
                        <div class="col-6">
                            <label class="dark-label">Probation Salary</label>
                            <input type="number" class="dark-input" placeholder="Optional">
                        </div>
                    </div>

                    <div class="estimated-value-box">
                        <div class="estimated-value-title">Estimated Contract Value</div>
                        <div class="estimated-value-amount" id="estimated-value">Rs. 0</div>
                    </div>
                </div>

                <!-- COLUMN 3: PROJECT SELECTION -->
                <div class="col-md-4" style="padding-left: 2rem;">
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
                                <label class="dark-label">Associated Project <span class="required-asterisk">*</span></label>
                                <select name="ctc_projectcode" class="dark-input select2" id="single-project-select">
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
                            <label class="dark-label mb-2">Monthly Project Allocations <span class="required-asterisk">*</span></label>
                            <div style="max-height: 250px; overflow-y: auto; padding-right: 10px;">
                                <table class="dark-table" id="monthly-project-table">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th>Project</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Auto generated -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Actions Footer -->
            <div class="form-actions">
                <div class="text-muted text-sm"><i class="fas fa-info-circle mr-1"></i> You can save as draft and complete later.</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-discard mr-3" onclick="window.location.href='{{ route('division.contract-cases.index') }}'">Discard</button>
                    <button type="button" class="btn-save-draft" id="btn-save-draft"><i class="fas fa-save"></i> SAVE DRAFT</button>
                </div>
                </div>
            </form>
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
    if($.fn.inputmask) {
        $('.cnic-mask').inputmask('99999-9999999-9');
    }
    
    // Attempt Select2 if loaded
    if($.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    }

    // Radio Toggle Logic
    $('input[name="project_mode"]').change(function() {
        if(this.value === 'single') {
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

    // Real-time Calculations
    function calculateFinancials() {
        let salary = parseFloat($('#salary-input').val()) || 0;
        let months = 0;

        const startVal = $('#ctc_startdate').val();
        const endVal = $('#ctc_enddate').val();

        if(startVal && endVal) {
            const start = new Date(startVal);
            const end = new Date(endVal);
            if(end > start) {
                const diffMs = end - start;
                const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
                months = diffDays / 30; // Float for exact calc
                
                let displayMonths = Math.floor(months);
                let displayDays = diffDays % 30;
                
                let durText = displayMonths > 0 ? displayMonths + ' months ' : '';
                durText += displayDays > 0 ? displayDays + ' days' : '';
                $('#duration-display').html('<i class="far fa-calendar-alt"></i> Duration: ' + durText.trim());
                
                // Regenerate Monthly Grid
                updateMonthlyProjectRows(start, end);
            } else {
                $('#duration-display').html('<i class="far fa-calendar-alt"></i> Invalid Dates');
                $('#monthly-project-table tbody').empty();
            }
        }

        // Estimated Contract Value = Salary * exact Months
        let estimated = salary * months;
        $('#estimated-value').text('Rs. ' + estimated.toLocaleString(undefined, {maximumFractionDigits:0}));
    }

    $('#salary-input').on('input', calculateFinancials);
    $('#ctc_startdate').on('change', function() {
        const start = new Date(this.value);
        if(!isNaN(start)) {
            const maxEnd = new Date(start);
            maxEnd.setFullYear(maxEnd.getFullYear() + 1);
            $('#ctc_enddate').attr('max', maxEnd.toISOString().split('T')[0]);
            calculateFinancials();
        }
    });
    $('#ctc_enddate').on('change', calculateFinancials);

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
                    <td class="font-weight-bold" style="color: #e2e8f0;">${label}</td>
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
        if($.fn.select2) {
            $('.select2-dynamic').select2({ theme: 'bootstrap4', width: '100%' });
        }
    }

    // Save Logic
    $('#btn-save-draft').click(function() {
        if(!$('#contract-case-form')[0].checkValidity()) {
            $('#contract-case-form')[0].reportValidity();
            return;
        }
        
        const formData = new FormData($('#contract-case-form')[0]);
        formData.append('ctc_status', 'Draft');
        
        // Remove project mappings based on mode
        if($('input[name="project_mode"]:checked').val() === 'monthly') {
            formData.delete('ctc_projectcode'); // Ignore single select
        }

        $(this).attr('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> SAVING...');
        
        $.ajax({
            url: '{{ route("division.contract-cases.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                if(res.success) {
                    window.location.href = '{{ route("division.contract-cases.index") }}';
                }
            },
            error: function(err) {
                Swal.fire('Error', 'Failed to save case. Check inputs.', 'error');
                $('#btn-save-draft').attr('disabled', false).html('<i class="fas fa-save"></i> SAVE DRAFT');
            }
        });
    });
});
</script>
@endpush
