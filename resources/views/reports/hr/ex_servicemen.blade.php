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
                        <i class="fas fa-medal mr-2 text-warning"></i> Ex-Servicemen Employees
                    </h5>
                    <small class="text-muted">Listing of all retired armed forces personnel</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <span class="badge badge-pill badge-warning px-3 py-2 text-dark" style="font-size:0.85rem;">
                    <i class="fas fa-users mr-1"></i> {{ count($employees) }} employee(s)
                </span>
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
                <table class="table table-sm table-bordered mb-0 ex-service-table" id="exServiceTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 80px;">ID</th>
                            <th>Name</th>
                            <th>Job Title</th>
                            <th class="text-center">Joining dt</th>
                            <th class="text-center">Dept</th>
                            <th class="text-center">Project</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                            <tr>
                                <td class="text-center fw-bold text-info">{{ $emp->emp_id }}</td>
                                <td>{{ $emp->emp_name }}</td>
                                <td>{{ $emp->designation }}</td>
                                <td class="text-center">
                                    {{ $emp->emp_joindt ? \Carbon\Carbon::parse($emp->emp_joindt)->format('d M y') : '-' }}
                                </td>
                                <td class="text-center">{{ $emp->department_code }}</td>
                                <td class="text-center">{{ $emp->project_name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-folder-open mb-2" style="font-size: 2rem; opacity: 0.5;"></i><br>
                                    No Ex-servicemen employees found in your scope.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($employees) > 0)
                    <tfoot>
                        <tr>
                            <td colspan="6" class="font-weight-bold" style="background:var(--rd-surface2); color:var(--rd-text1);">
                                {{ count($employees) }} employee(s)
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</div>

<style>
/* ── Table ─────────────────────────────────────────────── */
.ex-service-table {
    font-size: 0.85rem;
    color: var(--rd-text2);
    background: var(--rd-surface);
}
.ex-service-table th,
.ex-service-table td {
    border: 1px solid var(--rd-border) !important;
    vertical-align: middle !important;
}
.ex-service-table thead th {
    background: var(--rd-surface2) !important;
    color: var(--rd-text1) !important;
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 10px 12px;
}
.ex-service-table tbody tr:hover td {
    background: rgba(255,255,255,0.04) !important;
}
/* ── Print ─────────────────────────────────────────────── */
@media print {
    .content-wrapper { margin-left: 0 !important; }
    .btn, .d-flex.align-items-center.justify-content-between .d-flex { display: none !important; }
    .table-responsive { overflow: visible !important; }
    body { background: #fff !important; color: #000 !important; }
    .ex-service-table th, .ex-service-table td { border: 1px solid #999 !important; color: #000 !important; }
    .ex-service-table tfoot td { background: #eee !important; color: #000 !important; }
}
</style>

<script>
function exportCSV() {
    const table = document.getElementById('exServiceTable');
    const rows  = table.querySelectorAll('tr');
    const csv   = [];
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        csv.push([...cells].map(c => '"' + c.textContent.trim().replace(/"/g,'""') + '"').join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a    = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(blob),
        download: 'ex_servicemen.csv'
    });
    a.click();
}
</script>
@endsection
