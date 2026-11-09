@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap');

    .fin-unlock-emp {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg, #f8fafc) !important;
        min-height: 90vh;
        color: var(--rd-text1, #0f172a);
        padding-top: 18px;
        padding-bottom: 60px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-clean {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    }

    .table-days {
        background: #ffffff;
        color: #1e293b;
    }
    .table-days th {
        background: #f8fafc !important;
        border-bottom: 2px solid #e2e8f0 !important;
        color: #475569 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        font-size: 11.5px;
        font-weight: 700;
        padding: 9px 10px !important;
        white-space: nowrap;
    }
    .table-days td {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 8px 10px !important;
        vertical-align: middle;
    }

    .badge-code {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        border-radius: 6px;
    }
    .badge-code-P { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .badge-code-A { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-code-L { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .badge-code-U { background: #fce7f3; color: #be185d; border: 1px solid #fbcfe8; }
    .badge-code-N { background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
    .badge-code-Z { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
    .badge-code-W { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .badge-code-default { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; }

    .weekend-row {
        background-color: #fafbfc;
    }

    .row-unlocked {
        background-color: #fefce8 !important;
    }

    .select2-container--bootstrap4 .select2-selection {
        border-radius: 6px !important;
        border-color: #cbd5e1 !important;
        font-size: 12px !important;
        min-height: 31px !important;
        height: 31px !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 29px !important;
        padding-left: 8px !important;
    }
</style>

<div class="content-wrapper fin-unlock-emp">
    <div class="container-fluid px-3 px-md-4">
        <!-- Top Nav & Breadcrumb -->
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3" style="gap: 15px;">
            <div>
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <a href="{{ route('fin.attendance.unlock_list') }}" class="btn btn-sm btn-outline-secondary rounded-circle shadow-sm" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;" title="Back to Employee List">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <h2 class="m-0 rajdhani font-weight-bold" style="color: #d97706; font-size: 1.5rem;">
                        <i class="fas fa-user-lock mr-2"></i> MONTHLY ATTENDANCE UNLOCK CONTROL
                    </h2>
                </div>
                <div class="text-muted small mt-1">
                    Employee: <strong class="text-dark">{{ $employee->emp_name }}</strong> (ID: <span class="font-monospace text-primary font-weight-bold">{{ $employee->emp_id }}</span>)
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap: 10px;">
                <a href="{{ route('divhr.employeedetail', ['id' => $employee->emp_id]) }}" target="_blank" class="btn btn-sm btn-outline-info shadow-sm font-weight-bold">
                    <i class="fas fa-id-card mr-1"></i> View Profile
                </a>
                <a href="{{ route('fin.attendance.unlock_list') }}" class="btn btn-sm btn-outline-secondary shadow-sm font-weight-bold">
                    <i class="fas fa-list mr-1"></i> Employee List
                </a>
            </div>
        </div>

        <!-- Alert messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm py-2 px-3 small border-0 mb-3" style="background: #f0fdf4; color: #16a34a; border-left: 4px solid #16a34a !important;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close text-success" data-dismiss="alert">&times;</button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 px-3 small border-0 mb-3" style="background: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626 !important;">
                <i class="fas fa-exclamation-triangle mr-2"></i> {{ $errors->first() }}
                <button type="button" class="close text-danger" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <!-- Employee Summary & Month Navigator Card -->
        <div class="card-clean p-3 mb-3">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-2 mb-lg-0">
                    <div class="d-flex align-items-center flex-wrap" style="gap: 18px;">
                        <div>
                            <span class="small text-muted d-block" style="font-size: 11px;">Department / Division</span>
                            <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $employee->unt_name ?? '—' }}</span>
                        </div>
                        <div class="border-left pl-3">
                            <span class="small text-muted d-block" style="font-size: 11px;">Rank / Designation</span>
                            <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $employee->emp_rank ?? '—' }}</span>
                        </div>
                        <div class="border-left pl-3">
                            <span class="small text-muted d-block" style="font-size: 11px;">Joining Date</span>
                            <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ !empty($employee->emp_joindt) ? \Carbon\Carbon::parse($employee->emp_joindt)->format('d-M-Y') : '—' }}</span>
                        </div>
                        <div class="border-left pl-3">
                            <span class="small text-muted d-block" style="font-size: 11px;">Contract Status</span>
                            <span class="badge badge-success">{{ $employee->emp_status ?? 'Active' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Month Selector Shifter -->
                <div class="col-lg-5 text-lg-right">
                    <form method="GET" action="{{ route('fin.attendance.unlock_employee', ['empId' => $employee->emp_id]) }}" class="d-inline-flex align-items-center" style="gap: 8px;">
                        <a href="{{ route('fin.attendance.unlock_employee', ['empId' => $employee->emp_id, 'month' => $prevMonth]) }}" class="btn btn-sm btn-light border shadow-sm font-weight-bold" title="Previous Month">
                            <i class="fas fa-chevron-left"></i>
                        </a>

                        <input type="month" name="month" value="{{ $monthStr }}" class="form-control form-control-sm font-weight-bold text-center" style="width: 155px; font-size: 12.5px;" onchange="this.form.submit()">

                        <a href="{{ route('fin.attendance.unlock_employee', ['empId' => $employee->emp_id, 'month' => $nextMonth]) }}" class="btn btn-sm btn-light border shadow-sm font-weight-bold" title="Next Month">
                            <i class="fas fa-chevron-right"></i>
                        </a>

                        <button type="submit" class="btn btn-sm btn-primary font-weight-bold shadow-sm">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Quick Batch Apply Bar (Optional Shortcut) --}}
        <div class="card card-clean p-2.5 mb-3 shadow-sm" style="border-left: 4px solid #2563eb !important; background: #ffffff;">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <span class="font-weight-bold text-dark rajdhani" style="font-size: 13px;">
                        <i class="fas fa-bolt text-warning mr-1"></i> BATCH CASE SELECTOR (OPTIONAL):
                    </span>
                    <span class="text-muted small" style="font-size: 11.5px;">
                        Select a case here to auto-fill all row dropdowns below:
                    </span>
                </div>
                <div class="d-flex align-items-center" style="min-width: 340px; flex: 1; max-width: 520px; gap: 8px;">
                    <select id="quick-batch-case-select" class="form-control form-control-sm select2-batch-case" style="width: 100%;">
                        <option value="">-- Search Approved Case File --</option>
                        @foreach($approvedCaseFiles as $cf)
                            <option value="{{ $cf->gcs_id }}" 
                                    data-file-no="{{ ltrim($cf->gcs_file_no, '#') }}" 
                                    data-title="{{ $cf->gcs_title }}">
                                {{ ltrim($cf->gcs_file_no, '#') }} — {{ $cf->gcs_title }}
                            </option>
                        @endforeach
                        @foreach($otherCaseFiles as $cf)
                            <option value="{{ $cf->gcs_id }}" 
                                    data-unapproved="1"
                                    data-file-no="{{ ltrim($cf->gcs_file_no, '#') }}" 
                                    data-title="{{ $cf->gcs_title }}">
                                {{ ltrim($cf->gcs_file_no, '#') }} — {{ $cf->gcs_title }} ({{ $cf->gcs_status }})
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="btn-apply-batch-case" class="btn btn-sm btn-primary font-weight-bold px-3 text-nowrap" style="font-size: 11.5px;">
                        Apply to All
                    </button>
                </div>
            </div>
        </div>

        <!-- Attendance Days Table with Dedicated Case File Column -->
        <div class="card-clean shadow-sm overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-days table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Date & Day</th>
                            <th style="width: 6%;" class="text-center">Mark</th>
                            <th style="width: 15%;">Current Status</th>
                            <th style="width: 25%;">Authorizing Case File (Approved) <span class="text-danger">*</span></th>
                            <th style="width: 26%;">Reason / Remarks (Mandatory for Unlock)</th>
                            <th style="width: 10%;">Action</th>
                            <th style="width: 6%;" class="text-center">Save</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $d)
                            @php
                                $codeClass = 'badge-code-' . ($d['code'] ?: 'default');
                                $isWeekend = $d['is_weekend'];
                                $isFinUnlocked = $d['is_finance_unlocked'];
                                $isLocked = $d['is_locked'];
                            @endphp
                            <tr id="row-day-{{ $d['day'] }}" class="{{ $isWeekend ? 'weekend-row' : '' }} {{ $isFinUnlocked ? 'row-unlocked' : '' }}">
                                <td>
                                    <div class="font-weight-bold {{ $isWeekend ? 'text-secondary' : 'text-dark' }}" style="font-size: 12.5px;">
                                        {{ $d['date_formatted'] }}
                                    </div>
                                    <small class="badge {{ $isWeekend ? 'badge-light text-muted' : 'badge-secondary' }}" style="font-size: 9.5px;">
                                        {{ $d['day_name'] }} @if($isWeekend) (Weekend) @endif
                                    </small>
                                </td>

                                <td class="text-center">
                                    <span class="badge-code {{ $codeClass }}" title="Code: {{ $d['code'] ?: 'None' }}">
                                        {{ $d['code'] ?: '—' }}
                                    </span>
                                </td>

                                <td>
                                    <div id="status-badge-{{ $d['day'] }}">
                                        @if($isFinUnlocked)
                                            <span class="badge badge-success font-weight-bold p-1 px-2" style="font-size: 10.5px;">
                                                <i class="fas fa-unlock-alt mr-1"></i> FINANCE UNLOCKED
                                            </span>
                                        @elseif($isLocked)
                                            <span class="badge badge-danger font-weight-bold p-1 px-2" style="font-size: 10.5px;">
                                                <i class="fas fa-lock mr-1"></i> LOCKED
                                            </span>
                                        @else
                                            <span class="badge badge-info font-weight-bold p-1 px-2" style="font-size: 10.5px;">
                                                <i class="fas fa-edit mr-1"></i> EDITABLE
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Audit Trail (Who unlocked & When) --}}
                                    <div id="audit-trail-{{ $d['day'] }}" class="small text-muted mt-1" style="font-size: 10px; line-height: 1.2;">
                                        @if($isFinUnlocked && !empty($d['unlocked_by']))
                                            <i class="fas fa-user-check text-success mr-1"></i> {{ $d['unlocked_by'] }}
                                        @elseif(!$isFinUnlocked && !empty($d['relocked_by']))
                                            <i class="fas fa-history text-secondary mr-1"></i> Relocked: {{ $d['relocked_by'] }}
                                        @endif
                                    </div>

                                    {{-- Normal Employee Daily Attendance Remark --}}
                                    @if(!empty($d['raw_remark']))
                                        <div class="small text-secondary mt-1 text-truncate" style="max-width: 170px; font-size: 10.5px;" title="{{ $d['raw_remark'] }}">
                                            <i class="fas fa-comment-dots text-info mr-1"></i> {{ $d['raw_remark'] }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Dedicated Per-Row Authorizing Case File Dropdown Column --}}
                                <td>
                                    <select id="case-select-{{ $d['day'] }}" 
                                            class="form-control form-control-sm select2-row-case" 
                                            style="width: 100%;">
                                        <option value="">-- Select Approved Case --</option>
                                        @foreach($approvedCaseFiles as $cf)
                                            <option value="{{ $cf->gcs_id }}" 
                                                    data-file-no="{{ ltrim($cf->gcs_file_no, '#') }}" 
                                                    data-title="{{ $cf->gcs_title }}"
                                                    {{ (string)($d['case_id'] ?? '') === (string)$cf->gcs_id ? 'selected' : '' }}>
                                                {{ ltrim($cf->gcs_file_no, '#') }} — {{ $cf->gcs_title }}
                                            </option>
                                        @endforeach
                                        @foreach($otherCaseFiles as $cf)
                                            <option value="{{ $cf->gcs_id }}" 
                                                    data-unapproved="1"
                                                    data-file-no="{{ ltrim($cf->gcs_file_no, '#') }}" 
                                                    data-title="{{ $cf->gcs_title }}"
                                                    {{ (string)($d['case_id'] ?? '') === (string)$cf->gcs_id ? 'selected' : '' }}>
                                                {{ ltrim($cf->gcs_file_no, '#') }} — {{ $cf->gcs_title }} ({{ $cf->gcs_status }})
                                            </option>
                                        @endforeach
                                    </select>

                                    {{-- Case Link Badge if attached --}}
                                    <div id="case-view-link-{{ $d['day'] }}" class="mt-1" style="{{ $isFinUnlocked && !empty($d['case_file_no']) ? '' : 'display: none;' }}">
                                        <a id="case-anchor-{{ $d['day'] }}" 
                                           href="{{ $d['case_id'] ? route('cases.show', $d['case_id']) : '#' }}" 
                                           target="_blank" 
                                           class="small text-primary font-weight-bold" 
                                           style="font-size: 11px;">
                                            <i class="fas fa-folder-open text-warning mr-1"></i> {{ ltrim($d['case_file_no'] ?? '—', '#') }} (Open ↗)
                                        </a>
                                    </div>
                                </td>

                                <td>
                                    <input type="text" 
                                           id="remarks-{{ $d['day'] }}" 
                                           name="remarks" 
                                           class="form-control form-control-sm" 
                                           placeholder="Enter reason to unlock (e.g. approved medical leave)..."
                                           value="{{ $d['clean_reason'] }}"
                                           style="font-size: 12px;">
                                </td>

                                <td>
                                    <select id="status-select-{{ $d['day'] }}" class="form-control form-control-sm font-weight-bold" style="font-size: 11.5px;">
                                        <option value="unlocked" {{ $isFinUnlocked ? 'selected' : '' }} class="text-success font-weight-bold">
                                            🔓 Unlock Day
                                        </option>
                                        <option value="locked" {{ !$isFinUnlocked ? 'selected' : '' }} class="text-danger font-weight-bold">
                                            🔒 Lock Day
                                        </option>
                                    </select>
                                </td>

                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-primary font-weight-bold shadow-sm btn-save-day"
                                            data-day="{{ $d['day'] }}"
                                            style="font-size: 11px; padding: 3px 9px;">
                                        <i class="fas fa-save mr-1"></i> Save
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const saveButtons = document.querySelectorAll('.btn-save-day');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const month = '{{ $monthStr }}';
    const empId = '{{ $employee->emp_id }}';
    const postUrl = '{{ route("fin.attendance.save_unlock_day", ["empId" => $employee->emp_id]) }}';
    const caseShowBaseUrl = '{{ url("/cases") }}';

    function caseMatcher(params, data) {
        if (!data.element) {
            return data;
        }

        const isUnapproved = $(data.element).data('unapproved') == '1';
        const term = $.trim(params.term || '');

        // If no search query typed, only show approved cases (or currently selected)
        if (term === '') {
            if (isUnapproved && !$(data.element).is(':selected')) {
                return null;
            }
            return data;
        }

        // A search term is typed: normalize both term and text by removing '#' and spaces
        const cleanTerm = term.toLowerCase().replace(/#/g, '').trim();
        const cleanText = (data.text || '').toLowerCase().replace(/#/g, '').trim();
        const fileNo = ($(data.element).data('file-no') || '').toString().toLowerCase().replace(/#/g, '').trim();

        if (cleanText.indexOf(cleanTerm) > -1 || (fileNo && fileNo.indexOf(cleanTerm) > -1)) {
            return data;
        }

        return null;
    }

    // Initialize Select2 on Batch Case selector
    const $batchSelect = $('#quick-batch-case-select');
    if (typeof $.fn.select2 !== 'undefined') {
        $batchSelect.select2({
            theme: 'bootstrap4',
            placeholder: '-- Search Approved Case File --',
            allowClear: true,
            width: '100%',
            matcher: caseMatcher
        });

        // Initialize Select2 on each row's Case File dropdown
        $('.select2-row-case').select2({
            theme: 'bootstrap4',
            placeholder: '-- Select Approved Case --',
            allowClear: true,
            width: '100%',
            matcher: caseMatcher
        });
    }

    // Apply Batch Case to all rows
    $('#btn-apply-batch-case').on('click', function() {
        const selectedCaseId = $batchSelect.val();
        if (!selectedCaseId) {
            alert('Please select a Case File from the batch dropdown first.');
            return;
        }

        $('.select2-row-case').val(selectedCaseId).trigger('change');

        // Optional notification toast
        const btn = $(this);
        const orig = btn.html();
        btn.html('<i class="fas fa-check mr-1"></i> Applied!');
        btn.removeClass('btn-primary').addClass('btn-success');
        setTimeout(() => {
            btn.html(orig);
            btn.removeClass('btn-success').addClass('btn-primary');
        }, 1500);
    });

    // Handle Save Button Click
    saveButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const day = this.getAttribute('data-day');
            const status = document.getElementById(`status-select-${day}`).value;
            const remarksInput = document.getElementById(`remarks-${day}`);
            const remarks = remarksInput.value.trim();
            const caseSelect = $(`#case-select-${day}`);
            const caseId = caseSelect.val();

            // Strict Validation for Unlocking:
            if (status === 'unlocked') {
                if (!caseId) {
                    alert(`⚠️ CASE FILE LAZMI HAI:\nPlease select an authorizing Case File in this row for Day ${day}.`);
                    if (typeof $.fn.select2 !== 'undefined') {
                        caseSelect.select2('open');
                    } else {
                        caseSelect.focus();
                    }
                    return;
                }

                if (!remarks) {
                    alert(`⚠️ REMARKS / REASON LAZMI HAI:\nPlease enter a mandatory Reason/Remarks explaining why Day ${day} is being unlocked.`);
                    remarksInput.focus();
                    remarksInput.classList.add('is-invalid');
                    setTimeout(() => remarksInput.classList.remove('is-invalid'), 2500);
                    return;
                }
            }

            const originalHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch(postUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    month: month,
                    day: day,
                    status: status,
                    remarks: remarks,
                    case_id: caseId
                })
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = originalHtml;

                if (data.success) {
                    const statusBadgeDiv = document.getElementById(`status-badge-${day}`);
                    const caseLinkContainer = document.getElementById(`case-view-link-${day}`);
                    const caseAnchor = document.getElementById(`case-anchor-${day}`);
                    const auditTrailDiv = document.getElementById(`audit-trail-${day}`);
                    const row = document.getElementById(`row-day-${day}`);

                    if (data.status === 'unlocked') {
                        statusBadgeDiv.innerHTML = '<span class="badge badge-success font-weight-bold p-1 px-2" style="font-size: 10.5px;"><i class="fas fa-unlock-alt mr-1"></i> FINANCE UNLOCKED</span>';
                        row.classList.add('row-unlocked');

                        // Show attached Case File link in this row
                        if (data.case_file_no) {
                            const cleanFileNo = data.case_file_no.replace(/^#/, '');
                            if (caseAnchor) {
                                caseAnchor.innerHTML = `<i class="fas fa-folder-open text-warning mr-1"></i> ${cleanFileNo} (Open ↗)`;
                                caseAnchor.href = `${caseShowBaseUrl}/${data.case_id}`;
                            } else {
                                caseLinkContainer.innerHTML = `<a href="${caseShowBaseUrl}/${data.case_id}" target="_blank" class="small text-primary font-weight-bold" style="font-size: 11px;"><i class="fas fa-folder-open text-warning mr-1"></i> ${cleanFileNo} (Open ↗)</a>`;
                            }
                            caseLinkContainer.style.display = 'block';
                        }

                        // Show audit info (who & when)
                        if (data.unlocked_by) {
                            auditTrailDiv.innerHTML = `<i class="fas fa-user-check text-success mr-1"></i> ${data.unlocked_by}`;
                        }
                    } else {
                        statusBadgeDiv.innerHTML = '<span class="badge badge-danger font-weight-bold p-1 px-2" style="font-size: 10.5px;"><i class="fas fa-lock mr-1"></i> LOCKED</span>';
                        row.classList.remove('row-unlocked');
                        if (caseLinkContainer) caseLinkContainer.style.display = 'none';

                        const nowStr = new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                        auditTrailDiv.innerHTML = `<i class="fas fa-history text-secondary mr-1"></i> Relocked on ${nowStr}`;
                    }

                    // Temporary green glow highlight
                    row.style.transition = 'background-color 0.4s';
                    const origBg = row.style.backgroundColor;
                    row.style.backgroundColor = '#dcfce7';
                    setTimeout(() => {
                        row.style.backgroundColor = origBg;
                    }, 1200);
                } else {
                    alert(data.message || 'Error updating attendance lock status.');
                }
            })
            .catch(err => {
                this.disabled = false;
                this.innerHTML = originalHtml;
                console.error(err);
                alert('Network or server error while saving unlock status.');
            });
        });
    });
});
</script>
@endsection
