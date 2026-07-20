@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="container-fluid pt-4 px-4 pb-5">

        {{-- ─── Header ─────────────────────────────────────────── --}}
        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-secondary screen-only">
            <div class="d-flex align-items-center">
                <a href="{{ route('reports.center') }}" class="btn btn-sm btn-outline-secondary mr-3">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
                <div>
                    <h5 class="text-uppercase font-weight-bold rajdhani mb-0" style="letter-spacing:2px; color:var(--rd-text1);">
                        <i class="fas fa-boxes mr-2 text-purple"></i> Shared Assets
                    </h5>
                    <small class="text-muted">Assets available for sharing across divisions</small>
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

        <div class="print-only">
            <h3 class="mb-3">Assets Available for Sharing</h3>
        </div>

        {{-- ─── Table ──────────────────────────────────────── --}}
        <div class="card border-0 shadow-sm" style="border-radius:12px; background:var(--rd-surface); overflow:hidden; position: relative;">
            <div class="watermark print-only">
                PAK NAVY PROPERTY<br>
                CONFIDENTIAL<br>
                NRDI-RDW-DDSET<br>
                {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}
            </div>
            <div class="table-responsive" style="z-index: 1; position: relative;">
                <table class="table table-sm table-bordered mb-0 current-table" id="sharedAssetsTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">S No</th>
                            <th>Description</th>
                            <th class="text-center" style="width: 80px;">Qty</th>
                            <th class="text-center">Division</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $index => $asset)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $asset->description }}</td>
                                <td class="text-center">{{ number_format((float)$asset->qty, 0) }}</td>
                                <td class="text-center">{{ $asset->division ?: '-' }}</td>
                                <td>{{ $asset->location ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No shared assets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="print-only d-flex justify-content-between mt-3" style="font-size: 0.8rem;">
            <span>Page 1 of 1</span>
            <span>Printed on {{ \Carbon\Carbon::now()->format('d M y H:i') }}</span>
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
.current-table tbody tr:hover td {
    background: rgba(255,255,255,0.04) !important;
}

.text-purple {
    color: #6f42c1 !important;
}

.print-only {
    display: none;
}

.watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 24px;
    color: rgba(255, 0, 0, 0.15);
    text-align: center;
    font-weight: bold;
    line-height: 1.5;
    z-index: 0;
    pointer-events: none;
    letter-spacing: 2px;
}

/* ── Print ─────────────────────────────────────────────── */
@media print {
    .content-wrapper { margin-left: 0 !important; }
    .screen-only { display: none !important; }
    .print-only { display: block !important; }
    .table-responsive { overflow: visible !important; }
    body { background: #fff !important; color: #000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .current-table th, .current-table td { border: 1px solid #999 !important; color: #000 !important; }
    
    .watermark {
        display: block !important;
        position: fixed;
        top: 250px;
        color: rgba(255, 0, 0, 0.2) !important;
    }
}
</style>

<script>
function exportCSV() {
    const table = document.getElementById('sharedAssetsTable');
    const rows  = table.querySelectorAll('tr');
    const csv   = [];
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        csv.push([...cells].map(c => '"' + c.textContent.trim().replace(/"/g,'""') + '"').join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a    = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(blob),
        download: 'shared_assets.csv'
    });
    a.click();
}
</script>
@endsection
