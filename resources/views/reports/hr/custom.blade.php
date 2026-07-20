@extends('welcome')

@section('content')
<!-- Select2 CSS -->
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

<div class="content-wrapper">
<div class="container-fluid pt-4 px-4 pb-5">

    {{-- ─── Header ─────────────────────────────────────────── --}}
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-secondary">
        <div class="d-flex align-items-center">
            <a href="{{ route('reports.center') }}" class="btn btn-sm btn-outline-secondary mr-3">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <div>
                <h5 class="text-uppercase font-weight-bold rajdhani mb-0" style="letter-spacing:2px; color:var(--rd-text1);">
                    <i class="fas fa-sliders-h mr-2 text-warning"></i> Custom Employee Report
                </h5>
                <small class="text-muted">Select fields and filters to build your custom report</small>
            </div>
        </div>
    </div>

    {{-- ─── Builder Form ──────────────────────────────────── --}}
    <form method="GET" action="{{ route('reports.hr.custom') }}" id="customForm">

        {{-- Status Filter Row --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:10px; background:var(--rd-surface);">
            <div class="card-body py-3 px-4">
                <div class="d-flex align-items-center flex-wrap" style="gap:16px;">
                    <span class="font-weight-bold text-uppercase" style="font-size:0.75rem; letter-spacing:1px; color:var(--rd-text2);">
                        <i class="fas fa-filter mr-1 text-info"></i> Employee Status:
                    </span>
                    @foreach(['current' => 'Current', 'previous' => 'Previous', 'all' => 'All'] as $val => $label)
                        <label class="status-pill mb-0 {{ $statusFilter === $val ? 'active' : '' }}" style="cursor:pointer;">
                            <input type="radio" name="status" value="{{ $val }}" {{ $statusFilter === $val ? 'checked' : '' }} style="display:none;" onchange="document.getElementById('customForm').submit()">
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                    <div class="ml-auto d-flex" style="gap:8px;">
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectAll()">Select All</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectNone()">Clear All</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Field Groups --}}
        @php
            $groups = [
                'Basic'         => ['icon' => 'fa-id-card',       'color' => '#17a2b8'],
                'Official'      => ['icon' => 'fa-shield-alt',    'color' => '#6f42c1'],
                'Contract'      => ['icon' => 'fa-file-contract', 'color' => '#28a745'],
                'Qualification' => ['icon' => 'fa-graduation-cap','color' => '#ffc107'],
                'Personal'      => ['icon' => 'fa-user',          'color' => '#fd7e14'],
                'Bank'          => ['icon' => 'fa-university',    'color' => '#20c997'],
            ];
            // Build groups manually to preserve original associative keys
            $fieldsByGroup = [];
            foreach($allFields as $key => $field) {
                $fieldsByGroup[$field['group']][$key] = $field;
            }
        @endphp

        <div class="card border-0 shadow-sm mb-3" style="border-radius:10px; background:var(--rd-surface);">
            <div class="card-body py-3 px-4">
                <div class="row align-items-center">
                    <div class="col-md-8 mb-3 mb-md-0">
                        <label class="font-weight-bold text-uppercase" style="font-size:0.75rem; letter-spacing:1px; color:var(--rd-text2);">
                            <i class="fas fa-columns mr-1 text-warning"></i> Select Fields:
                        </label>
                        <select name="fields[]" id="fieldsSelect" class="form-control select2" multiple="multiple" data-placeholder="Select fields for your report..." style="width: 100%;">
                            @foreach($groups as $groupName => $meta)
                                @if(isset($fieldsByGroup[$groupName]))
                                    <optgroup label="{{ $groupName }}">
                                        @foreach($fieldsByGroup[$groupName] as $key => $field)
                                            <option value="{{ $key }}" {{ in_array($key, $selectedFields) ? 'selected' : '' }}>
                                                {{ $field['label'] }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                        <div class="mt-2 d-flex" style="gap:8px;">
                            <button type="button" class="btn btn-xs btn-outline-info" onclick="selectAll()">Select All</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="selectNone()">Clear All</button>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-right">
                        @if($submitted && $results->isNotEmpty())
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="exportCSV()">
                                    <i class="fas fa-download mr-1"></i> Export CSV
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                    <i class="fas fa-print mr-1"></i> Print
                                </button>
                            </div>
                        @endif
                        <button type="submit" class="btn btn-warning font-weight-bold px-4" style="border-radius: 6px;">
                            <i class="fas fa-play mr-1"></i> Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- ─── Results Table ─────────────────────────────────── --}}
    @if($submitted)
        @if(count($selectedFields) === 0)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i> Please select at least one field.
            </div>
        @elseif($results->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-search mb-2" style="font-size:2rem; opacity:0.4;"></i>
                <p>No employees found matching your filters.</p>
            </div>
        @else
            <div class="card border-0 shadow-sm" style="border-radius:10px; background:var(--rd-surface); overflow:hidden;">
                <div class="px-3 py-2 d-flex justify-content-between align-items-center" style="background:rgba(255,193,7,0.08); border-bottom:1px solid var(--rd-border);">
                    <span style="color:var(--rd-text1); font-size:0.85rem; font-weight:600;">
                        <i class="fas fa-table mr-2 text-warning"></i>
                        {{ $results->count() }} record(s) found
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 custom-result-table" id="customResultTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:45px;">#</th>
                                @foreach($selectedFields as $key)
                                    @if(isset($allFields[$key]))
                                        <th>{{ $allFields[$key]['label'] }}</th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $idx => $row)
                                <tr>
                                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                    @foreach($selectedFields as $key)
                                        @if(isset($allFields[$key]))
                                            <td>
                                                @php $val = $row->$key ?? null; @endphp
                                                @if(in_array($key, ['emp_joindt','emp_lastdt','ctr_date','ctr_startdt','ctr_enddt','emp_cissuedt','emp_cexpdt','emp_dob']) && $val)
                                                    {{ \Carbon\Carbon::parse($val)->format('d M Y') }}
                                                @elseif($key === 'ctr_type')
                                                    {{ $val == 1 ? 'Regular' : ($val == 2 ? 'Probation' : ($val ?? '-')) }}
                                                @else
                                                    {{ $val ?? '-' }}
                                                @endif
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

</div>
</div>

<style>
/* ── Status Pills ─────────────────────────────────────── */
.status-pill span {
    display: inline-block;
    padding: 4px 16px;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 600;
    border: 1px solid var(--rd-border);
    color: var(--rd-text2);
    transition: all 0.2s;
    cursor: pointer;
}
.status-pill.active span,
.status-pill input:checked ~ span {
    background: rgba(23,162,184,0.2);
    border-color: #17a2b8;
    color: #17a2b8;
}

/* ── Custom Select2 Styling (Dark Theme & Checkboxes) ── */
.select2-container--bootstrap4 .select2-selection--multiple {
    min-height: 52px !important;
    background-color: var(--rd-surface2) !important;
    border: 1px solid var(--rd-border) !important;
    border-radius: 8px !important;
    padding: 6px 12px !important;
}
.select2-container--bootstrap4.select2-container--focus .select2-selection--multiple {
    border-color: #ffc107 !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
}
.select2-container--bootstrap4 .select2-selection__choice {
    background-color: rgba(255, 193, 7, 0.15) !important;
    border: 1px solid #ffc107 !important;
    color: #ffc107 !important;
    font-size: 0.85rem !important;
    padding: 4px 12px !important;
    border-radius: 20px !important;
    margin-top: 4px !important;
    font-weight: 500;
}
.select2-container--bootstrap4 .select2-selection__choice__remove {
    color: #ffc107 !important;
    margin-right: 6px !important;
}
.select2-container--bootstrap4 .select2-selection__choice__remove:hover {
    color: #fff !important;
    background: #dc3545 !important;
}
.select2-search__field {
    color: var(--rd-text1) !important;
    background-color: transparent !important;
    margin-top: 6px !important;
    font-size: 0.95rem !important;
}

/* Dropdown Menu Dark Theme */
.select2-dropdown {
    background-color: var(--rd-surface) !important;
    border: 1px solid var(--rd-border) !important;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
}
.select2-results__group {
    background-color: rgba(0,0,0,0.2) !important;
    color: #17a2b8 !important;
    font-weight: bold !important;
    padding: 8px 12px !important;
    border-bottom: 1px solid var(--rd-border);
}

/* CSS-based Live Checkboxes */
.select2-results__option {
    padding: 8px 12px !important;
    font-size: 0.85rem;
    color: var(--rd-text2) !important;
    transition: background 0.15s;
}
/* Unchecked State */
.select2-results__option[role="option"]::before {
    content: "\f0c8"; /* far fa-square */
    font-family: "Font Awesome 5 Free";
    font-weight: 400;
    display: inline-block;
    width: 20px;
    margin-right: 8px;
    font-size: 1.1rem;
    color: var(--rd-text3);
    vertical-align: middle;
}
/* Checked State */
.select2-results__option[role="option"][aria-selected="true"]::before {
    content: "\f14a"; /* fas fa-check-square */
    font-weight: 900;
    color: #ffc107;
}
/* Highlighted and Selected Styles */
.select2-container--bootstrap4 .select2-results__option[aria-selected="true"] {
    background-color: rgba(255, 193, 7, 0.08) !important;
    color: var(--rd-text1) !important;
}
.select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
    background-color: rgba(255, 255, 255, 0.05) !important;
    color: var(--rd-text1) !important;
}

/* ── Result Table ─────────────────────────────────────── */
.custom-result-table {
    font-size: 0.8rem;
    color: var(--rd-text2);
    background: var(--rd-surface);
}
.custom-result-table th,
.custom-result-table td {
    border: 1px solid var(--rd-border) !important;
    vertical-align: middle !important;
    white-space: nowrap;
}
.custom-result-table thead th {
    background: var(--rd-surface2) !important;
    color: var(--rd-text1) !important;
    font-weight: 700;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 9px 10px;
}
.custom-result-table tbody tr:hover td { background: rgba(255,255,255,0.03) !important; }

/* ── Print ─────────────────────────────────────────────── */
@media print {
    .content-wrapper { margin-left: 0 !important; }
    form .card, form .d-flex.justify-content-between { display: none !important; }
    body { background: #fff !important; color: #000 !important; }
    .custom-result-table th, .custom-result-table td { border: 1px solid #aaa !important; color: #000 !important; white-space: normal; }
}
</style>

@push('scripts')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 on the fields dropdown
    $('#fieldsSelect').select2({
        theme: 'bootstrap4',
        width: '100%',
        closeOnSelect: false // Keep dropdown open for multiple selections
    });
});

// Status pill JS toggle for immediate styling
document.querySelectorAll('.status-pill input').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.status-pill').forEach(p => p.classList.remove('active'));
        this.closest('.status-pill').classList.add('active');
    });
});

function selectAll() {
    $('#fieldsSelect > optgroup > option').prop('selected', true);
    $('#fieldsSelect').trigger('change');
}

function selectNone() {
    $('#fieldsSelect').val(null).trigger('change');
}

function exportCSV() {
    const table = document.getElementById('customResultTable');
    if (!table) return;
    const rows = table.querySelectorAll('tr');
    const csv  = [];
    rows.forEach(r => {
        const cells = r.querySelectorAll('th, td');
        csv.push([...cells].map(c => '"' + c.textContent.trim().replace(/"/g,'""') + '"').join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(blob),
        download: 'custom_employee_report.csv'
    });
    a.click();
}
</script>
@endpush
@endsection
