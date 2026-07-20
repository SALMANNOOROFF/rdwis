@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid pt-4 px-4 pb-5">

        {{-- ─── Header ─────────────────────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom border-secondary">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.center') }}" class="btn btn-sm btn-outline-secondary mr-3">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <div>
                    <h5 class="text-uppercase font-weight-bold rajdhani mb-0" style="letter-spacing:2px; color:var(--rd-text1);">
                        <i class="fas fa-money-bill-wave mr-2 text-success"></i>
                        Salaries Paid
                        @if($selectedMonth)
                            — <span style="color:var(--rd-accent);">{{ \Carbon\Carbon::parse($selectedMonth)->format('M Y') }}</span>
                        @endif
                    </h5>
                    <small class="text-muted">Monthly salary disbursement report by division</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                {{-- Month picker --}}
                <form method="GET" action="{{ route('reports.hr.salaries_paid') }}" class="d-flex align-items-center" style="gap:6px;">
                    <label class="mb-0 text-muted" style="font-size:0.82rem; white-space:nowrap;">Select Month:</label>
                    <select name="month" class="form-control form-control-sm" style="width:140px; background:var(--rd-surface2); color:var(--rd-text1); border:1px solid var(--rd-border);" onchange="this.form.submit()">
                        @foreach($availableMonths as $mon)
                            <option value="{{ $mon }}" {{ $mon === $selectedMonth ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($mon)->format('M Y') }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <button class="btn btn-sm btn-outline-info" onclick="exportCSV()">
                    <i class="fas fa-download mr-1"></i> CSV
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>

        {{-- ─── Per Division Tables ──────────────────────────── --}}
        @forelse($grouped as $divisionName => $employees)
            @php $divTotal = $employees->sum('salary'); @endphp
            <div class="card border-0 shadow-sm mb-4" style="border-radius:10px; background:var(--rd-surface); overflow:hidden;">
                {{-- Division Title Bar --}}
                <div class="px-3 py-2" style="background:rgba(23,162,184,0.12); border-bottom:1px solid var(--rd-border);">
                    <span class="font-weight-bold" style="color:var(--rd-text1); font-size:0.95rem;">
                        <i class="fas fa-building mr-1 text-info"></i>
                        Salaries Paid From {{ $divisionName }} — {{ \Carbon\Carbon::parse($selectedMonth)->format('M y') }}
                    </span>
                    <span class="float-right badge badge-success px-3 py-1">
                        Total: {{ number_format($divTotal) }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 sal-table">
                        <thead>
                            <tr>
                                <th style="width:55px;" class="text-center">S No</th>
                                <th>Employee Name</th>
                                <th class="text-center">Dept.</th>
                                <th class="text-center">Project</th>
                                <th class="text-center">Paid from</th>
                                <th class="text-right">Salary Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $idx => $emp)
                                <tr>
                                    <td class="text-center">{{ $idx + 1 }}</td>
                                    <td>{{ $emp->emp_name }}</td>
                                    <td class="text-center">{{ $emp->dept_code }}</td>
                                    <td class="text-center">{{ $emp->project_code }}</td>
                                    <td class="text-center">{{ $emp->paid_from }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($emp->salary) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-right font-weight-bold" style="background:var(--rd-surface2); color:var(--rd-text1);">
                                    <span class="text-muted">({{ count($employees) }} persons)</span>
                                    &nbsp;&nbsp; Total:
                                </td>
                                <td class="text-right font-weight-bold" style="background:var(--rd-surface2); color:var(--rd-accent);">
                                    {{ number_format($divTotal) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-folder-open mb-2" style="font-size:2.5rem; opacity:0.4;"></i>
                <p>No salary records found for this month.</p>
            </div>
        @endforelse

        {{-- ─── Grand Total ─────────────────────────────────── --}}
        @if($grouped->isNotEmpty())
        <div class="card border-0 shadow-sm" style="border-radius:10px; background:var(--rd-surface2); overflow:hidden;">
            <div class="px-4 py-3 d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-uppercase" style="letter-spacing:1px; color:var(--rd-text1);">
                    <i class="fas fa-sigma mr-2 text-warning"></i>
                    Grand Total ({{ $grouped->flatten()->count() }} persons)
                </span>
                <span class="font-weight-bold" style="font-size:1.3rem; color:var(--rd-accent);">
                    {{ number_format($grandTotal) }}
                </span>
            </div>
        </div>
        @endif

    </div>
</div>

<style>
/* ── Table ─────────────────────────────────────────────── */
.sal-table {
    font-size: 0.83rem;
    color: var(--rd-text2);
    background: var(--rd-surface);
}
.sal-table th,
.sal-table td {
    border: 1px solid var(--rd-border) !important;
    vertical-align: middle !important;
}
.sal-table thead th {
    background: var(--rd-surface2) !important;
    color: var(--rd-text1) !important;
    font-weight: 700;
    font-size: 0.73rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 9px 12px;
}
.sal-table tbody tr:hover td {
    background: rgba(255,255,255,0.03) !important;
}
.sal-table tfoot td {
    background: var(--rd-surface2) !important;
    color: var(--rd-text1);
}

/* ── Print ─────────────────────────────────────────────── */
@media print {
    .content-wrapper { margin-left: 0 !important; }
    form, .btn { display: none !important; }
    .table-responsive { overflow: visible !important; }
    body { background: #fff !important; color: #000 !important; }
    .sal-table th, .sal-table td { border: 1px solid #aaa !important; color: #000 !important; }
    .sal-table tfoot td { background: #eee !important; color: #000 !important; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
}
</style>

<script>
function exportCSV() {
    const tables = document.querySelectorAll('.sal-table');
    const rows   = [];
    tables.forEach(t => {
        t.querySelectorAll('tr').forEach(r => {
            const cells = r.querySelectorAll('th, td');
            rows.push([...cells].map(c => '"' + c.textContent.trim().replace(/"/g,'""') + '"').join(','));
        });
    });
    const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
    const a    = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(blob),
        download: 'salaries_paid_{{ $selectedMonth }}.csv'
    });
    a.click();
}
</script>
@endsection
