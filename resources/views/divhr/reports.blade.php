@extends('welcome')

@section('content')
<style>
    /* Styling matching Finance Reports Center */
    .card-cyber {
        background-color: var(--rd-surface2, #121a22);
        border: 1px solid var(--rd-border, rgba(255, 255, 255, 0.08));
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    }
    .kpi-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--rd-text2, #94a3b8);
        font-weight: 700;
    }
    .form-control-cyber {
        background-color: var(--rd-neutral-50) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: #fff !important;
        border-radius: 8px !important;
        height: 38px;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    .form-control-cyber:focus {
        border-color: #67e8f9 !important;
        box-shadow: 0 0 8px rgba(103, 232, 249, 0.3) !important;
    }
    .btn-cyber-primary {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #fff;
        border: none;
        padding: 8px 24px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        transition: all 0.2s ease;
    }
    .btn-cyber-primary {
        background: var(--rd-primary-600) !important;
        color: #fff !important;
        border: none;
        padding: 8px 24px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        box-shadow: var(--rd-shadow-sm);
        transition: all 0.2s ease;
    }
    .btn-cyber-primary:hover {
        background: var(--rd-primary-700) !important;
        color: #fff !important;
        transform: translateY(-1px);
    }
    .btn-cyber-secondary {
        background: var(--rd-success) !important;
        color: #fff !important;
        border: none;
        padding: 8px 24px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        box-shadow: var(--rd-shadow-sm);
        transition: all 0.2s ease;
    }
    .btn-cyber-secondary:hover {
        background: var(--rd-success-dark) !important;
        color: #fff !important;
        transform: translateY(-1px);
    }
    .columns-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
        background: var(--rd-neutral-50) !important;
        padding: 14px;
        border-radius: 8px;
        border: 1px solid var(--rd-border) !important;
        max-height: 180px;
        overflow-y: auto;
    }
    .checkbox-option {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-size: 12px;
        color: var(--rd-text1) !important;
        margin: 0;
        user-select: none;
    }
    .checkbox-option input[type="checkbox"] {
        accent-color: var(--rd-primary-600) !important;
        cursor: pointer;
        width: 15px;
        height: 15px;
    }
    .table-cyber {
        font-size: 12.5px;
        color: var(--rd-text1);
    }
    .table-cyber thead th {
        background-color: var(--rd-surface3) !important;
        color: var(--rd-text2) !important;
        border-bottom: 2px solid var(--rd-border) !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 10px 12px;
    }
    .table-cyber tbody td {
        padding: 10px 12px;
        border-top: 1px solid var(--rd-border);
        vertical-align: middle;
        color: var(--rd-text1) !important;
    }
    .table-cyber tbody tr:hover {
        background-color: var(--rd-neutral-50) !important;
    }
    .dashboard-loader {
        font-size: 12px;
        color: #67e8f9;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
</style>

<div class="content-wrapper px-4 pt-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div class="d-flex align-items-center flex-wrap" style="gap: 15px;">
            <h2 class="font-weight-bold text-dark rajdhani m-0" style="font-family: 'Rajdhani', sans-serif;">
                <i class="fas fa-chart-bar mr-2 text-info"></i>HR Reports Center
            </h2>
            @if(in_array(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))), ['fin', 'hr', 'nrdi', 'rdw', 'hqs']))
            <div class="btn-group btn-group-sm shadow-sm" role="group">
                <a href="{{ route('hr.reports.index', ['mode' => 'm']) }}" 
                   class="btn {{ ($mode ?? 'm') === 'm' ? 'btn-danger font-weight-bold' : 'btn-outline-danger' }}" style="{{ ($mode ?? 'm') === 'm' ? '' : 'background: var(--rd-surface2);' }}">
                    <i class="fas fa-globe mr-1"></i> ALL DEPT
                </a>
                <a href="{{ route('hr.reports.index', ['mode' => 's']) }}" 
                   class="btn {{ ($mode ?? 'm') === 's' ? 'btn-info font-weight-bold' : 'btn-outline-info' }}"
                   style="{{ ($mode ?? 'm') === 's' ? 'background-color: var(--rd-primary-500); border-color: var(--rd-primary-500); color: white;' : 'background: var(--rd-surface2); border-color: var(--rd-primary-500);' }}">
                    <i class="fas fa-sitemap mr-1"></i> MY DEPT
                </a>
            </div>
            @endif
        </div>
        
        <div id="loader" class="dashboard-loader" style="display: none;">
            <i class="fas fa-spinner fa-spin mr-1"></i> QUERYING DATABASE...
        </div>
    </div>

    <!-- Filters Dashboard Card -->
    <div class="card-cyber px-4 py-3 mb-4">
        <div class="row">
            <!-- 1. Report Selector -->
            <div class="col-md-4 mb-3">
                <label class="kpi-title mb-2 d-block"><i class="fas fa-file-alt mr-1 text-cyan"></i> Select Report Type</label>
                <select id="report-type" class="form-control form-control-cyber">
                    <option value="">-- Select Report Type --</option>
                    <option value="incomplete_data">Incomplete Data (Missing Fields)</option>
                    <option value="grades">Grades Report</option>
                    <option value="qualifications">Qualifications & Degrees Report</option>
                    <option value="current_employees">Current Employees Report</option>
                    <option value="retired_servicemen">Retired / Released Servicemen Report</option>
                    <option value="mobphones">Mob. Phones / SIMs Directory</option>
                    <option value="custom">Custom Report (All Fields & History)</option>
                </select>
            </div>

            <!-- 2. Division / Unit Filter -->
            <div class="col-md-4 mb-3">
                <label class="kpi-title mb-2 d-block"><i class="fas fa-sitemap mr-1 text-cyan"></i> Division / Unit</label>
                <select id="division-filter" class="form-control form-control-cyber">
                    <option value="All">All Divisions</option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->unt_id }}">{{ $unit->unt_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 3. Employee Status Filter -->
            <div class="col-md-4 mb-3">
                <label class="kpi-title mb-2 d-block"><i class="fas fa-user-clock mr-1 text-cyan"></i> Employee Status</label>
                <select id="status-filter" class="form-control form-control-cyber">
                    <option value="Current" selected>Current (Active) Employees</option>
                    <option value="Previous">Previous (Released / Retired) Employees</option>
                    <option value="All">All Employees (Current + Previous)</option>
                </select>
            </div>
        </div>

        <div class="row">
            <!-- 3. Columns Selector Grid -->
            <div class="col-12 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap" style="gap: 10px;">
                    <label class="kpi-title m-0"><i class="fas fa-table mr-1 text-cyan"></i> Select Columns to Include</label>
                    <div style="gap: 15px;" class="d-flex">
                        <a href="javascript:void(0)" class="text-info font-weight-bold text-xs" id="col-select-all" style="text-decoration: none;"><i class="fas fa-check-square mr-1"></i> Select All</a>
                        <a href="javascript:void(0)" class="text-warning font-weight-bold text-xs" id="col-deselect-all" style="text-decoration: none;"><i class="fas fa-minus-square mr-1"></i> Clear All</a>
                    </div>
                </div>
                <div class="columns-grid" id="columns-container">
                    <div class="text-muted text-xs p-2">Select a report type to load available columns.</div>
                </div>
            </div>
        </div>

        <!-- Action Panel -->
        <div class="d-flex justify-content-end gap-3 mt-2 flex-wrap" style="gap: 12px;">
            <button id="btn-reset" class="btn btn-sm btn-outline-secondary px-4" style="border-radius: 20px; height: 38px; font-weight: 600;">
                <i class="fas fa-undo mr-1"></i> Reset
            </button>
            <button id="btn-generate" class="btn-cyber-primary">
                <i class="fas fa-cogs mr-1"></i> Generate Report
            </button>
            <button id="btn-export" class="btn-cyber-secondary">
                <i class="fas fa-file-excel mr-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Output View Card -->
    <div class="card-cyber p-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <span class="font-weight-bold text-dark text-sm">
                <i class="fas fa-list mr-2 text-cyan"></i> Generated Report Output
            </span>
            <span class="text-muted text-xs" id="records-counter">0 records loaded</span>
        </div>

        <div class="rd-table-responsive" style="max-height: 600px; overflow-y: auto; border: 1px solid var(--rd-border); border-radius: 8px;">
            <div id="table-placeholder" class="text-center py-5 text-muted">
                <i class="fas fa-table mb-3 text-cyan" style="font-size: 3rem;"></i><br>
                Select a Report Type above and click "Generate Report" to view results.
            </div>
            
            <table class="table table-hover table-cyber text-nowrap m-0 d-none" id="output-table">
                <thead class="sticky-top">
                    <tr id="table-headers">
                        <!-- Dynamic headers -->
                    </tr>
                </thead>
                <tbody id="table-body">
                    <!-- Dynamic rows -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // All column definitions for each report type
    const reportColumnsMap = {
        incomplete_data: {
            emp_name: 'Employee Name',
            emp_id: 'Employee ID',
            emp_status: 'Status',
            emp_joindt: 'Join Date',
            missing_count: 'Missing Count',
            missing_fields: 'Missing Fields'
        },
        grades: {
            emp_name: 'Employee Name',
            emp_id: 'Employee ID',
            emp_status: 'Status',
            emp_joindt: 'Join Date',
            grade: 'Grade',
            salary: 'Salary',
            job_title: 'Job Title',
            ctr_start: 'Contract Start',
            ctr_end: 'Contract End',
            head_code: 'Head Code'
        },
        qualifications: {
            emp_name: 'Employee Name',
            emp_id: 'Employee ID',
            emp_status: 'Status',
            emp_joindt: 'Join Date',
            total_qualifs: 'Total Degrees/Certs',
            qualifications_list: 'Qualifications & Degrees'
        },
        current_employees: {
            emp_name: 'Employee Name',
            emp_id: 'Employee ID',
            emp_cnic: 'CNIC',
            emp_status: 'Status',
            emp_joindt: 'Join Date',
            emp_lastdt: 'Release Date',
            grade: 'Grade',
            salary: 'Salary',
            job_title: 'Job Title',
            ctr_start: 'Contract Start',
            ctr_end: 'Contract End',
            head_code: 'Head Code',
            unit: 'Unit'
        },
        retired_servicemen: {
            emp_name: 'Employee Name',
            emp_id: 'Employee ID',
            emp_status: 'Status',
            emp_joindt: 'Join Date',
            emp_lastdt: 'Release Date',
            last_grade: 'Last Grade',
            last_salary: 'Last Salary',
            job_title: 'Job Title',
            ctr_start: 'Contract Start',
            ctr_end: 'Contract End',
            remarks: 'Remarks'
        },
        mobphones: {
            emp_name: 'Employee Name',
            emp_id: 'Employee ID',
            emp_status: 'Status',
            emp_joindt: 'Join Date',
            mobile1: 'Mobile 1',
            mobile2: 'Mobile 2',
            landline: 'Landline',
            email: 'Email'
        },
        custom: {
            emp_name: 'Employee Name',
            emp_id: 'Employee ID',
            emp_cnic: 'CNIC',
            emp_status: 'Status',
            emp_joindt: 'Join Date',
            emp_lastdt: 'Release Date',
            unit: 'Unit',
            total_contracts: 'Total Contracts',
            current_grade: 'Current Grade',
            current_salary: 'Current Salary',
            contracts_history: 'Contract History & Salary Increments'
        }
    };

    const isDivisionUser = {{ (Auth::user()->isDivision()) ? 'true' : 'false' }};
    const userUnitId = "{{ Auth::user()->acc_unt_id }}";

    $(document).ready(function() {
        if (isDivisionUser && userUnitId && userUnitId !== '0') {
            $('#division-filter').val(userUnitId);
        }
    });

    let activeData = [];

    // Render checkbox column selector grid
    function renderColumnSelector(reportType) {
        const container = $('#columns-container');
        container.empty();

        if (!reportType || !reportColumnsMap[reportType]) {
            container.html('<div class="text-muted text-xs p-2">Select a report type to load available columns.</div>');
            return;
        }

        const cols = reportColumnsMap[reportType];
        $.each(cols, function(key, label) {
            const labelEl = $('<label class="checkbox-option"></label>');
            const cb = $('<input type="checkbox" class="col-checkbox" checked>').val(key);
            const span = $('<span></span>').text(label);
            labelEl.append(cb).append(span);
            container.append(labelEl);
        });
    }

    $('#report-type').on('change', function() {
        const type = $(this).val();
        renderColumnSelector(type);
    });

    $('#col-select-all').on('click', function() {
        $('.col-checkbox').prop('checked', true);
    });

    $('#col-deselect-all').on('click', function() {
        $('.col-checkbox').prop('checked', false);
    });

    $('#btn-reset').on('click', function() {
        $('#report-type').val('');
        $('#status-filter').val('Current');
        if (isDivisionUser && userUnitId && userUnitId !== '0') {
            $('#division-filter').val(userUnitId);
        } else {
            $('#division-filter').val('All');
        }
        renderColumnSelector('');
        $('#table-placeholder').removeClass('d-none');
        $('#output-table').addClass('d-none');
        $('#records-counter').text('0 records loaded');
        activeData = [];
    });

    // Generate Report
    $('#btn-generate').on('click', function() {
        const reportType = $('#report-type').val();
        if (!reportType) {
            alert('Please select a Report Type first.');
            return;
        }

        const status = $('#status-filter').val();
        const unitId = $('#division-filter').val();
        $('#loader').show();

        $.ajax({
            url: "{{ route('hr.reports.data') }}",
            type: 'GET',
            data: {
                type: reportType,
                status: status,
                unit_id: unitId
            },
            success: function(res) {
                $('#loader').hide();
                activeData = res.data || [];
                renderOutputTable(reportType, activeData);
            },
            error: function(err) {
                $('#loader').hide();
                alert('Error fetching report data. Please try again.');
                console.error(err);
            }
        });
    });

    // Render Output Table
    function renderOutputTable(reportType, data) {
        const selectedCols = [];
        $('.col-checkbox:checked').each(function() {
            selectedCols.push($(this).val());
        });

        const colMap = reportColumnsMap[reportType] || {};
        const headerRow = $('#table-headers');
        const tableBody = $('#table-body');
        
        headerRow.empty();
        tableBody.empty();

        if (selectedCols.length === 0) {
            alert('Please select at least one column to display.');
            return;
        }

        // Header
        headerRow.append('<th class="text-center" style="width: 40px;">#</th>');
        selectedCols.forEach(colKey => {
            const title = colMap[colKey] || colKey;
            headerRow.append(`<th>${title}</th>`);
        });

        // Rows
        if (!data || data.length === 0) {
            $('#table-placeholder').html('<i class="fas fa-folder-open mb-3 text-cyan" style="font-size: 3rem;"></i><br>No records found for the selected criteria.').removeClass('d-none');
            $('#output-table').addClass('d-none');
            $('#records-counter').text('0 records loaded');
            return;
        }

        $('#table-placeholder').addClass('d-none');
        $('#output-table').removeClass('d-none');
        $('#records-counter').text(data.length + ' records loaded');

        data.forEach((row, idx) => {
            const tr = $('<tr></tr>');
            tr.append(`<td class="text-center text-muted">${idx + 1}</td>`);

            selectedCols.forEach(colKey => {
                let val = row[colKey] ?? '—';

                // Formatters
                if (colKey === 'emp_name') {
                    val = `<strong class="text-dark">${val}</strong>`;
                } else if (colKey === 'emp_status') {
                    const isCurr = val === 'Current';
                    val = `<span class="badge ${isCurr ? 'badge-success' : 'badge-danger'}">${val}</span>`;
                } else if (colKey === 'missing_count' || colKey === 'total_contracts' || colKey === 'total_qualifs') {
                    val = `<span class="badge badge-info px-2 py-1">${val}</span>`;
                } else if (colKey === 'grade' || colKey === 'last_grade' || colKey === 'ctr_grade' || colKey === 'current_grade') {
                    val = val !== '—' ? `<span class="badge px-2 py-1" style="font-size: 0.82rem; background: rgba(23,162,184,0.2); color: #67e8f9; border-radius: 6px;">${val}</span>` : '—';
                } else if (colKey === 'salary' || colKey === 'last_salary' || colKey === 'ctr_salary' || colKey === 'current_salary') {
                    val = val !== '—' ? `<span style="color: #22c55e; font-weight: 700;">${val}</span>` : '—';
                } else if (colKey === 'pct_increase') {
                    if (val.startsWith('+')) {
                        val = `<span class="text-success font-weight-bold">${val}</span>`;
                    } else if (val.startsWith('-')) {
                        val = `<span class="text-danger font-weight-bold">${val}</span>`;
                    }
                } else if (colKey === 'contract_no') {
                    val = `<span class="badge" style="border: 1px solid rgba(103,232,249,0.4); color: #67e8f9; background: rgba(103,232,249,0.1); border-radius: 10px; font-size: 0.78rem; padding: 4px 8px;">${val}</span>`;
                } else if (colKey === 'contracts_history') {
                    if (Array.isArray(val) && val.length > 0) {
                        let html = '<table class="table table-sm text-nowrap mb-0" style="background: var(--rd-neutral-50); border: 1px solid var(--rd-border); font-size: 0.8rem; border-radius: 6px;">';
                        html += '<thead><tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: #67e8f9;"><th style="padding:4px 8px;">Contract Record</th><th style="padding:4px 8px;">Grade</th><th style="padding:4px 8px;">Salary</th><th style="padding:4px 8px;">% Inc.</th><th style="padding:4px 8px;">Start Date</th><th style="padding:4px 8px;">End Date</th><th style="padding:4px 8px;">Job Title</th><th style="padding:4px 8px;">Head</th></tr></thead><tbody>';
                        val.forEach(c => {
                            const pctColor = c.pct_increase.startsWith('+') ? '#22c55e' : (c.pct_increase.startsWith('-') ? '#f87171' : '#94a3b8');
                            html += `<tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                <td style="padding:4px 8px;"><span class="badge" style="border: 1px solid rgba(103,232,249,0.3); color: #67e8f9; background: rgba(103,232,249,0.1); font-size: 0.75rem;">Contract ${c.no}</span></td>
                                <td style="padding:4px 8px;"><span class="badge" style="background: rgba(23,162,184,0.2); color: #67e8f9;">${c.grade}</span></td>
                                <td style="padding:4px 8px; color: #22c55e; font-weight: 700;">${c.salary}</td>
                                <td style="padding:4px 8px; color: ${pctColor}; font-weight: 700;">${c.pct_increase}</td>
                                <td style="padding:4px 8px;">${c.ctr_start || '—'}</td>
                                <td style="padding:4px 8px;">${c.ctr_end || '—'}</td>
                                <td style="padding:4px 8px;">${c.ctr_jobtitle}</td>
                                <td style="padding:4px 8px; color: var(--rd-text3);">${c.head_code}</td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        val = html;
                    } else {
                        val = '<span class="text-muted"><i>No contracts</i></span>';
                    }
                } else if (colKey === 'qualifications_list') {
                    if (Array.isArray(val) && val.length > 0) {
                        let html = '<table class="table table-sm text-nowrap mb-0" style="background: var(--rd-neutral-50); border: 1px solid var(--rd-border); font-size: 0.8rem; border-radius: 6px;">';
                        html += '<thead><tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: #67e8f9;"><th style="padding:4px 8px;">Type</th><th style="padding:4px 8px;">Title / Degree</th><th style="padding:4px 8px;">Institution</th><th style="padding:4px 8px;">Duration</th><th style="padding:4px 8px;">Grade</th><th style="padding:4px 8px;">End Date</th></tr></thead><tbody>';
                        val.forEach(q => {
                            const typeBg = (q.qlf_type || '').toLowerCase() === 'degree' ? 'background: rgba(95,120,88,0.18); color: #60a5fa;' : ((q.qlf_type || '').toLowerCase() === 'course' ? 'background: rgba(34,197,94,0.2); color: #22c55e;' : 'background: rgba(168,85,247,0.2); color: #a855f7;');
                            html += `<tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                <td style="padding:4px 8px;"><span class="badge" style="${typeBg}">${q.qlf_type}</span></td>
                                <td style="padding:4px 8px; font-weight: 700; color: var(--rd-text1);">${q.qlf_name}</td>
                                <td style="padding:4px 8px;">${q.qlf_inst}</td>
                                <td style="padding:4px 8px;">${q.qlf_duration}</td>
                                <td style="padding:4px 8px; color: #22c55e;">${q.qlf_grade}</td>
                                <td style="padding:4px 8px;">${q.qlf_enddt || '—'}</td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        val = html;
                    } else {
                        val = '<span class="text-muted"><i>No qualifications</i></span>';
                    }
                }

                tr.append(`<td>${val}</td>`);
            });

            tableBody.append(tr);
        });
    }

    // Export CSV
    $('#btn-export').on('click', function() {
        const reportType = $('#report-type').val();
        if (!activeData || activeData.length === 0) {
            alert('No report data to export. Generate a report first.');
            return;
        }

        const selectedCols = [];
        $('.col-checkbox:checked').each(function() {
            selectedCols.push($(this).val());
        });

        const colMap = reportColumnsMap[reportType] || {};
        let csv = '#,';
        selectedCols.forEach(c => {
            csv += `"${colMap[c] || c}",`;
        });
        csv = csv.slice(0, -1) + '\n';

        activeData.forEach((row, idx) => {
            let line = `"${idx + 1}",`;
            selectedCols.forEach(c => {
                let v = row[c] ?? '';
                if (Array.isArray(v)) {
                    if (c === 'contracts_history') {
                        v = v.map(ctr => `[Contract ${ctr.no}] Grade: ${ctr.grade} | Salary: ${ctr.salary} | Inc: ${ctr.pct_increase} | Start: ${ctr.ctr_start || '—'} | End: ${ctr.ctr_end || '—'} | Title: ${ctr.ctr_jobtitle} | Head: ${ctr.head_code}`).join(' ; ');
                    } else if (c === 'qualifications_list') {
                        v = v.map(qlf => `[${qlf.qlf_type}] ${qlf.qlf_name} - ${qlf.qlf_inst} (${qlf.qlf_duration}, Grade: ${qlf.qlf_grade}, End: ${qlf.qlf_enddt || '—'})`).join(' ; ');
                    }
                }
                v = String(v).replace(/"/g, '""');
                line += `"${v}",`;
            });
            csv += line.slice(0, -1) + '\n';
        });

        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `${reportType}_report.csv`;
        a.click();
    });
</script>
@endsection
