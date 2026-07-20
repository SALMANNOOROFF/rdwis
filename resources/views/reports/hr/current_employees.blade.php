@extends('welcome')

@section('content')
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
                        <i class="fas fa-user-tie mr-2 text-info"></i> Contract Employees
                    </h5>
                    <small class="text-muted">Current employees listing organized by division</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button class="btn btn-sm btn-outline-info" onclick="exportCSV()">
                    <i class="fas fa-download mr-1"></i> Export CSV
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>

        {{-- ─── Table ──────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm" style="border-radius:12px; background:var(--rd-surface); overflow:hidden;">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 current-table" id="currentEmployeesTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">S No</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Project</th>
                            <th class="text-center">Joining dt</th>
                            <th class="text-center">Last Contract</th>
                            <th class="text-right">Last Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groupedEmployees as $divisionName => $employees)
                            {{-- Division Header Row --}}
                            <tr class="division-header">
                                <td colspan="8" class="font-weight-bold">
                                    {{ $divisionName }}
                                </td>
                            </tr>
                            {{-- Employees --}}
                            @foreach($employees as $index => $emp)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $emp->emp_name }}</td>
                                    <td>
                                        @php
                                            $designation = trim($emp->designation ?? '');
                                            if (!$designation && $emp->contract) {
                                                $designation = trim($emp->contract->ctr_jobtitle ?? '');
                                            }
                                        @endphp
                                        {{ $designation ?: '-' }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $grade = trim($emp->grade ?? '');
                                            if (!$grade && $emp->contract) {
                                                $grade = trim($emp->contract->ctr_grade ?? '');
                                            }
                                        @endphp
                                        {{ $grade ?: '-' }}
                                    </td>
                                    <td class="text-center">{{ $emp->project_name }}</td>
                                    <td class="text-center text-nowrap">
                                        {{ $emp->emp_joindt ? \Carbon\Carbon::parse($emp->emp_joindt)->format('d M y') : '-' }}
                                    </td>
                                    <td class="text-center text-nowrap">
                                        @if($emp->contract)
                                            {{ \Carbon\Carbon::parse($emp->contract->ctr_startdt)->format('d M y') }} to {{ \Carbon\Carbon::parse($emp->contract->ctr_enddt)->format('d M y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($emp->salary && $emp->salary->sor_grosalary)
                                            {{ number_format($emp->salary->sor_grosalary) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<style>
/* ── Table ─────────────────────────────────────────────── */
.current-table {
    font-size: 0.82rem;
    color: var(--rd-text2);
    background: var(--rd-surface);
}
.current-table th,
.current-table td {
    border: 1px solid var(--rd-border) !important;
    vertical-align: middle !important;
}
.current-table thead th {
    background: var(--rd-surface2) !important;
    color: var(--rd-text1) !important;
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 10px 12px;
}
.division-header td {
    background: rgba(23,162,184,0.1) !important;
    color: var(--rd-text1);
    font-size: 0.9rem;
    padding-left: 15px !important;
}
.current-table tbody tr:hover td {
    background: rgba(255,255,255,0.04) !important;
}
/* ── Print ─────────────────────────────────────────────── */
@media print {
    .content-wrapper { margin-left: 0 !important; }
    .btn, .d-flex.align-items-center.justify-content-between .d-flex { display: none !important; }
    .table-responsive { overflow: visible !important; }
    body { background: #fff !important; color: #000 !important; }
    .current-table th, .current-table td { border: 1px solid #999 !important; color: #000 !important; }
    .division-header td { background: #e0f7fa !important; }
}
</style>

<script>
function exportCSV() {
    const table = document.getElementById('currentEmployeesTable');
    const rows  = table.querySelectorAll('tr');
    const csv   = [];
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        csv.push([...cells].map(c => '"' + c.textContent.trim().replace(/"/g,'""') + '"').join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a    = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(blob),
        download: 'current_employees.csv'
    });
    a.click();
}
</script>
@endsection
