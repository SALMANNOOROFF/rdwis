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
                        <i class="fas fa-chart-pie mr-2 text-success"></i> Project Allocations — Status
                    </h5>
                    <small class="text-muted">Allocation breakdown by project head &amp; division</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button class="btn btn-sm btn-outline-warning" id="toggleDetailsBtn" onclick="toggleDetails()">
                    <i class="fas fa-eye mr-1"></i> Show / Hide Details
                </button>
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
                <table class="table table-sm table-bordered mb-0 alloc-table" id="allocTable">
                    <thead>
                        {{-- Group header row --}}
                        <tr class="alloc-thead-group">
                            <th colspan="6" style="border-right: 2px solid var(--rd-accent) !important;"></th>
                            <th colspan="4" class="text-center" style="color:#28a745; letter-spacing:1px; font-size:0.7rem; font-weight:700; text-transform:uppercase;">
                                <i class="fas fa-chart-bar mr-1"></i> Actual Project Figures
                            </th>
                        </tr>
                        <tr>
                            <th style="min-width:80px;">Head</th>
                            <th class="text-right" style="min-width:120px;">Allocation</th>
                            <th class="text-right detail-col" style="min-width:110px;">MTSS Share</th>
                            <th class="text-right detail-col" style="min-width:110px;">RDW Share</th>
                            <th class="text-right detail-col" style="min-width:100px;">CSRF Share</th>
                            <th class="text-right" style="min-width:120px; border-right:2px solid var(--rd-accent) !important;">Project Share</th>
                            <th class="text-right" style="min-width:110px;">Max Limit</th>
                            <th class="text-right" style="min-width:110px;">Equipment</th>
                            <th class="text-right" style="min-width:100px;">HR</th>
                            <th class="text-right" style="min-width:90px;">Misc</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grouped as $untId => $rows)
                            @php
                                $unit = $units[$untId] ?? null;
                                $uSum = $unitSummaries[$untId] ?? [];
                            @endphp

                            {{-- ── Unit Summary Row ── --}}
                            <tr class="unit-row">
                                <td class="unit-label">{{ $unit ? $unit->unt_namesh : 'Unknown' }}</td>
                                <td class="text-right unit-num">{{ number_format($uSum['allocation'] ?? 0) }}</td>
                                <td class="text-right unit-num detail-col">{{ number_format($uSum['mtss_share'] ?? 0) }}</td>
                                <td class="text-right unit-num detail-col">{{ number_format($uSum['rdw_share'] ?? 0) }}</td>
                                <td class="text-right unit-num detail-col">{{ number_format($uSum['csrf_share'] ?? 0) }}</td>
                                <td class="text-right unit-num" style="border-right:2px solid var(--rd-accent) !important;">{{ number_format($uSum['project_share'] ?? 0) }}</td>
                                <td class="text-right unit-num">{{ number_format($uSum['max_limit'] ?? 0) }}</td>
                                <td class="text-right unit-num">{{ number_format($uSum['eqp_sbh'] ?? 0) }}</td>
                                <td class="text-right unit-num">{{ number_format($uSum['hr_sbh'] ?? 0) }}</td>
                                <td class="text-right unit-num">{{ number_format($uSum['misc_sbh'] ?? 0) }}</td>
                            </tr>

                            {{-- ── Project Detail Rows ── --}}
                            @foreach($rows as $row)
                            <tr class="proj-row">
                                <td class="proj-label">{{ $row->head }}</td>
                                <td class="text-right proj-num">{{ $row->allocation ? number_format($row->allocation) : '' }}</td>
                                <td class="text-right proj-num detail-col">{{ $row->mtss_share ? number_format($row->mtss_share) : '' }}</td>
                                <td class="text-right proj-num detail-col">{{ $row->rdw_share ? number_format($row->rdw_share) : '' }}</td>
                                <td class="text-right proj-num detail-col">{{ $row->csrf_share ? number_format($row->csrf_share) : '' }}</td>
                                <td class="text-right proj-num" style="border-right:2px solid var(--rd-accent) !important;">{{ $row->project_share ? number_format($row->project_share) : '' }}</td>
                                <td class="text-right proj-num">{{ $row->max_limit ? number_format($row->max_limit) : '' }}</td>
                                <td class="text-right proj-num">{{ $row->eqp_sbh ? number_format($row->eqp_sbh) : '' }}</td>
                                <td class="text-right proj-num">{{ $row->hr_sbh ? number_format($row->hr_sbh) : '' }}</td>
                                <td class="text-right proj-num">{{ $row->misc_sbh ? number_format($row->misc_sbh) : '' }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    {{-- Grand Total Footer --}}
                    <tfoot>
                        <tr class="grand-total-row">
                            <td class="font-weight-bold">Total</td>
                            @php
                                $gt = [
                                    'allocation'    => collect($unitSummaries)->sum('allocation'),
                                    'mtss_share'    => collect($unitSummaries)->sum('mtss_share'),
                                    'rdw_share'     => collect($unitSummaries)->sum('rdw_share'),
                                    'csrf_share'    => collect($unitSummaries)->sum('csrf_share'),
                                    'project_share' => collect($unitSummaries)->sum('project_share'),
                                    'max_limit'     => collect($unitSummaries)->sum('max_limit'),
                                    'eqp_sbh'       => collect($unitSummaries)->sum('eqp_sbh'),
                                    'hr_sbh'        => collect($unitSummaries)->sum('hr_sbh'),
                                    'misc_sbh'      => collect($unitSummaries)->sum('misc_sbh'),
                                ];
                            @endphp
                            <td class="text-right">{{ number_format($gt['allocation']) }}</td>
                            <td class="text-right detail-col">{{ number_format($gt['mtss_share']) }}</td>
                            <td class="text-right detail-col">{{ number_format($gt['rdw_share']) }}</td>
                            <td class="text-right detail-col">{{ number_format($gt['csrf_share']) }}</td>
                            <td class="text-right" style="border-right:2px solid var(--rd-accent) !important;">{{ number_format($gt['project_share']) }}</td>
                            <td class="text-right">{{ number_format($gt['max_limit']) }}</td>
                            <td class="text-right">{{ number_format($gt['eqp_sbh']) }}</td>
                            <td class="text-right">{{ number_format($gt['hr_sbh']) }}</td>
                            <td class="text-right">{{ number_format($gt['misc_sbh']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- ─── Summary Cards (per unit) ─────────────────────── --}}
        <div class="row mt-4">
            @foreach($unitSummaries as $untId => $uSum)
                @php
                    $unit = $units[$untId] ?? null;
                    $pct  = ($gt['allocation'] ?? 0) > 0
                        ? round(($uSum['allocation'] / $gt['allocation']) * 100, 1)
                        : 0;
                @endphp
                <div class="col-6 col-md-3 col-xl-2 mb-3">
                    <div class="alloc-card">
                        <div class="ac-label">{{ $unit ? $unit->unt_namesh : 'Unknown' }}</div>
                        <div class="ac-amount">{{ number_format($uSum['allocation'] / 1e6, 1) }}<span class="ac-unit">M</span></div>
                        <div class="ac-bar">
                            <div class="ac-fill" style="width:{{ $pct }}%"></div>
                        </div>
                        <div class="ac-sub">
                            <span class="text-success">EQP: {{ number_format($uSum['eqp_sbh'] / 1e6, 1) }}M</span>
                            &nbsp;·&nbsp;
                            <span class="text-info">HR: {{ number_format($uSum['hr_sbh'] / 1e6, 1) }}M</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

<style>
/* ── Table ─────────────────────────────────────────────── */
.alloc-table {
    font-size: 0.8rem;
    color: var(--rd-text2);
    background: var(--rd-surface);
    min-width: 900px;
}
.alloc-table th,
.alloc-table td {
    border: 1px solid var(--rd-border) !important;
    vertical-align: middle !important;
    white-space: nowrap;
}
.alloc-table thead th {
    background: var(--rd-surface2) !important;
    color: var(--rd-text1) !important;
    font-weight: 700;
    font-size: 0.71rem;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    padding: 9px 10px;
}
.alloc-thead-group th {
    background: var(--rd-surface2) !important;
    border-bottom: 1px solid var(--rd-border) !important;
    padding: 6px 10px !important;
}

/* ── Unit Row ──────────────────────────────────────────── */
.unit-row td {
    background: rgba(40,167,69,0.08) !important;
    border-top: 2px solid rgba(40,167,69,0.3) !important;
}
.unit-label {
    font-weight: 800;
    font-size: 0.85rem;
    color: #28a745 !important;
    padding-left: 14px !important;
}
.unit-num {
    font-weight: 700;
    color: var(--rd-text1) !important;
}

/* ── Project Rows ──────────────────────────────────────── */
.proj-row:hover td {
    background: rgba(255,255,255,0.03) !important;
}
.proj-label {
    padding-left: 28px !important;
    color: var(--rd-text2);
    font-size: 0.79rem;
}
.proj-num {
    color: var(--rd-text2);
    font-size: 0.79rem;
}

/* ── Grand Total ───────────────────────────────────────── */
.grand-total-row td {
    background: var(--rd-surface2) !important;
    border-top: 2px solid var(--rd-border2) !important;
    font-weight: 700;
    color: var(--rd-text1) !important;
    font-size: 0.82rem;
}

/* ── Summary Cards ─────────────────────────────────────── */
.alloc-card {
    background: var(--rd-surface);
    border: 1px solid var(--rd-border);
    border-radius: 10px;
    padding: 14px 16px;
    text-align: center;
    transition: all 0.2s;
}
.alloc-card:hover {
    border-color: #28a745;
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.25);
}
.ac-label {
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--rd-text3);
    margin-bottom: 4px;
}
.ac-amount {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--rd-text1);
    line-height: 1;
    margin-bottom: 8px;
}
.ac-unit {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--rd-text3);
    margin-left: 2px;
}
.ac-bar {
    height: 4px;
    background: var(--rd-surface2);
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 6px;
}
.ac-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #17a2b8);
    border-radius: 999px;
    transition: width 0.6s ease;
}
.ac-sub {
    font-size: 0.67rem;
    color: var(--rd-text3);
}

/* ── Print ─────────────────────────────────────────────── */
@media print {
    .content-wrapper { margin-left: 0 !important; }
    .btn, .d-flex.align-items-center.justify-content-between .d-flex { display: none !important; }
    .table-responsive { overflow: visible !important; }
    body { background: #fff !important; color: #000 !important; }
    .alloc-table th, .alloc-table td { border: 1px solid #999 !important; color: #000 !important; }
    .unit-row td { background: #f0fff4 !important; }
    .unit-label { color: #1a6e2e !important; }
    .grand-total-row td { background: #f8f9fa !important; }
    .alloc-card { display: none; }
    .row.mt-4 { display: none; }
}
</style>

<script>
let detailsVisible = false;

function toggleDetails() {
    detailsVisible = !detailsVisible;
    document.querySelectorAll('.detail-col').forEach(el => {
        el.style.display = detailsVisible ? '' : 'none';
    });
    const btn = document.getElementById('toggleDetailsBtn');
    btn.innerHTML = detailsVisible
        ? '<i class="fas fa-eye-slash mr-1"></i> Hide Details'
        : '<i class="fas fa-eye mr-1"></i> Show / Hide Details';
}

// Initially hide detail columns (MTSS Share, RDW Share, CSRF Share)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.detail-col').forEach(el => {
        el.style.display = 'none';
    });
});

function exportCSV() {
    const table = document.getElementById('allocTable');
    // Temporarily show all columns for export
    const hidden = [];
    table.querySelectorAll('.detail-col').forEach(el => {
        if (el.style.display === 'none') {
            hidden.push(el);
            el.style.display = '';
        }
    });
    const rows = table.querySelectorAll('tr');
    const csv  = [];
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        csv.push([...cells].map(c => '"' + c.textContent.trim().replace(/"/g,'""') + '"').join(','));
    });
    // Restore
    hidden.forEach(el => el.style.display = 'none');

    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(blob),
        download: 'allocation_status.csv'
    });
    a.click();
}
</script>
@endsection
