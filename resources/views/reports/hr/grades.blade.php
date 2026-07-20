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
                        <i class="fas fa-layer-group mr-2 text-info"></i> Contract Employees — Grades
                    </h5>
                    <small class="text-muted">Active employees grouped by grade and department</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <span class="badge badge-pill badge-info px-3 py-2" style="font-size:0.85rem;">
                    <i class="fas fa-users mr-1"></i> {{ $grandTotal }} Total
                </span>
                <button class="btn btn-sm btn-outline-info" onclick="exportCSV()">
                    <i class="fas fa-download mr-1"></i> Export CSV
                </button>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>

        {{-- ─── Table Card ──────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm" style="border-radius:12px; background:var(--rd-surface); overflow:hidden;">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 grades-table" id="gradesTable">
                    <thead>
                        <tr>
                            <th class="grade-col">Grade</th>
                            <th class="total-col text-center">Total</th>
                            @foreach($headCodes as $hc)
                                <th class="text-center dept-col">{{ $hc }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grades as $grade)
                            @php $rowTotal = $rowTotals[$grade] ?? 0; @endphp
                            <tr class="grade-row">
                                <td class="grade-label">{{ $grade }}</td>
                                <td class="text-center total-cell fw-bold">{{ $rowTotal }}</td>
                                @foreach($headCodes as $hc)
                                    @php $val = $pivot[$grade][$hc] ?? 0; @endphp
                                    <td class="text-center {{ $val > 0 ? 'has-val' : 'zero-val' }}">
                                        {{ $val > 0 ? $val : '' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td class="grade-label fw-bold">Total</td>
                            <td class="text-center total-cell fw-bold">{{ $grandTotal }}</td>
                            @foreach($headCodes as $hc)
                                @php $ct = $colTotals[$hc] ?? 0; @endphp
                                <td class="text-center {{ $ct > 0 ? 'total-dept-val' : 'zero-val' }}">
                                    {{ $ct > 0 ? $ct : '' }}
                                </td>
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ─── Summary Cards ───────────────────────────────────── --}}
        <div class="row mt-4">
            @foreach($grades as $grade)
                @php
                    $total = $rowTotals[$grade] ?? 0;
                    $pct   = $grandTotal > 0 ? round(($total / $grandTotal) * 100) : 0;
                @endphp
                <div class="col-6 col-md-3 col-lg-2 mb-3">
                    <div class="grade-summary-card">
                        <div class="gscard-grade">{{ $grade }}</div>
                        <div class="gscard-count">{{ $total }}</div>
                        <div class="gscard-bar">
                            <div class="gscard-fill" style="width:{{ $pct }}%"></div>
                        </div>
                        <div class="gscard-pct">{{ $pct }}%</div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

<style>
/* ── Table ─────────────────────────────────────────────── */
.grades-table {
    font-size: 0.82rem;
    color: var(--rd-text2);
    background: var(--rd-surface);
    min-width: 600px;
}
.grades-table th,
.grades-table td {
    border: 1px solid var(--rd-border) !important;
    vertical-align: middle !important;
    white-space: nowrap;
}
.grades-table thead th {
    background: var(--rd-surface2) !important;
    color: var(--rd-text1) !important;
    font-weight: 700;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 10px 12px;
}
.grade-col  { min-width: 90px;  padding-left: 16px !important; }
.total-col  { min-width: 64px;  background: rgba(23,162,184,0.08) !important; }
.dept-col   { min-width: 58px; }

/* ── Rows ──────────────────────────────────────────────── */
.grade-row { transition: background 0.15s; }
.grade-row:hover td { background: rgba(255,255,255,0.04) !important; }

.grade-label {
    font-weight: 700;
    font-size: 0.82rem;
    color: var(--rd-text1);
    padding-left: 16px !important;
}

.has-val {
    color: var(--rd-text1);
    font-weight: 600;
}
.zero-val {
    color: transparent;
    user-select: none;
}

/* ── Total Row ─────────────────────────────────────────── */
.total-row td {
    background: var(--rd-surface2) !important;
    border-top: 2px solid var(--rd-border2) !important;
    font-weight: 700;
    color: var(--rd-text1) !important;
}
.total-dept-val {
    color: var(--rd-text1) !important;
    font-weight: 700;
}
.total-cell {
    background: rgba(23,162,184,0.15) !important;
    color: #17a2b8 !important;
    font-weight: 800 !important;
}

/* ── Summary Cards ─────────────────────────────────────── */
.grade-summary-card {
    background: var(--rd-surface);
    border: 1px solid var(--rd-border);
    border-radius: 10px;
    padding: 14px 16px;
    text-align: center;
    transition: all 0.2s;
}
.grade-summary-card:hover {
    border-color: var(--rd-accent);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
}
.gscard-grade {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--rd-text3);
    margin-bottom: 4px;
}
.gscard-count {
    font-size: 2rem;
    font-weight: 800;
    color: var(--rd-text1);
    line-height: 1;
    margin-bottom: 8px;
}
.gscard-bar {
    height: 4px;
    background: var(--rd-surface2);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 4px;
}
.gscard-fill {
    height: 100%;
    background: linear-gradient(90deg, #17a2b8, #007bff);
    border-radius: 999px;
    transition: width 0.6s ease;
}
.gscard-pct {
    font-size: 0.7rem;
    color: var(--rd-text3);
}

/* ── Print ─────────────────────────────────────────────── */
@media print {
    .content-wrapper { margin-left: 0 !important; }
    .btn, .d-flex.align-items-center.justify-content-between .d-flex { display: none !important; }
    .table-responsive { overflow: visible !important; }
    .grade-summary-card { border: 1px solid #ccc !important; }
    body { background: #fff !important; color: #000 !important; }
    .grades-table th, .grades-table td { border: 1px solid #999 !important; color: #000 !important; }
    .zero-val { color: transparent !important; }
    .total-cell { background: #e0f7fa !important; }
}
</style>

<script>
function exportCSV() {
    const table = document.getElementById('gradesTable');
    const rows  = table.querySelectorAll('tr');
    const csv   = [];
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        csv.push([...cells].map(c => '"' + c.textContent.trim().replace(/"/g,'""') + '"').join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a    = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(blob),
        download: 'employee_grades.csv'
    });
    a.click();
}
</script>
@endsection
