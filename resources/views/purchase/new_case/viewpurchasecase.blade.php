@extends('welcome')
@section('content')
@php
    $detailsRouteName = $detailsRouteName ?? 'purchasecasedetails';
    $u = Auth::user();
    $area = strtolower(trim((string) ($u?->acc_untarea ?? '')));
    $auth = strtolower(trim((string) ($u?->acc_auth ?? '')));
    $desigShort = strtoupper(trim((string) ($u?->acc_desigshort ?? '')));
    $isCommandView = $area === 'nrdi' && in_array($desigShort, ['DG', 'MD'], true);
    $canCreate = in_array($auth, ['approver', 'editor'], true) && ! $isCommandView;
@endphp
<div class="content-wrapper bg-transparent">
    <style>
        /* ... (Purana CSS same rahega) ... */
        .page-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 10px; }
        .page-title { color: var(--rd-accent); font-weight: 700; font-size: 26px; display: flex; align-items: center; }
        .page-title i { margin-right: 12px; }
        .btn-new-project { background-color: var(--rd-accent); color: white; border-radius: 25px; padding: 8px 20px; font-weight: 600; border: none; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); }
        .filter-container { border: 1px solid var(--rd-border); border-radius: 8px; padding: 15px 20px; background-color: var(--rd-surface); margin-bottom: 20px; }
        .filter-item label { display: block; font-size: 11px; font-weight: 700; color: var(--rd-text3); margin-bottom: 5px; text-transform: uppercase; }
        .form-control, .form-select { height: 40px; border: 1px solid var(--rd-border); border-radius: 6px; font-size: 14px; background: var(--rd-surface2); color: var(--rd-text1); }
        .status-toggle { display: flex; border: 1px solid var(--rd-accent); border-radius: 6px; overflow: hidden; }
        .status-toggle .btn { flex: 1; border: none; border-radius: 0; padding: 8px 15px; font-size: 13px; font-weight: 600; background: var(--rd-surface); color: var(--rd-accent); }
        .status-toggle .btn.active { background-color: var(--rd-accent); color: #fff; }
        .table-container { border: 1px solid var(--rd-border); border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); min-height: 400px; background: var(--rd-surface); }
        .custom-table { width: 100%; margin-bottom: 0; }
        .custom-table thead th { background-color: var(--rd-surface2); border-bottom: 2px solid var(--rd-border); color: var(--rd-text2); font-size: 13px; font-weight: 700; text-transform: uppercase; padding: 15px; }
        .custom-table tbody td { vertical-align: middle; font-size: 14px; border-top: 1px solid var(--rd-border2); color: var(--rd-text1); }
        .custom-table tbody tr:hover { background-color: var(--rd-surface2); }
        .stage-indicator { font-weight: 600; font-size: 13px; }
        .stage-indicator i { font-size: 10px; margin-right: 5px; }
        .text-price { font-family: 'Courier New', Courier, monospace; font-weight: 700; }
    </style>

    <div class="content">
        <div class="container-fluid">
            <div class="page-header">
                <div class="page-title"><i class="fas fa-folder-open"></i> All Purchase Cases</div>
                @if($canCreate)
                    <a href="{{ route('purchase.select') }}" class="btn btn-new-project"><i class="fas fa-plus-circle mr-1"></i> New Case</a>
                @endif
            </div>

            <!-- FILTER BAR SECTION -->
            <div class="filter-container">
                <div class="row align-items-end">
                    <div class="col-md-3 filter-item">
                        <label>Case Code</label>
                        <input type="text" id="searchInput" class="form-control" placeholder="Search PC-..." onkeyup="applyAllFilters()">
                    </div>
                    <div class="col-md-2 filter-item">
                        <label>From Date</label>
                        <input type="date" id="dateFrom" class="form-control" onchange="applyAllFilters()">
                    </div>
                    <div class="col-md-2 filter-item">
                        <label>To Date</label>
                        <input type="date" id="dateTo" class="form-control" onchange="applyAllFilters()">
                    </div>
                    <div class="col-md-2 filter-item">
                        <label>Project Stage</label>
                        <select class="form-control" id="stageSelect" onchange="applyAllFilters()">
                            <option value="all">All Stages</option>
                            <option value="Draft">Draft</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Under Scrutiny">Under Scrutiny</option>
                            <option value="Approved">Approved</option>
                            <option value="Fulfilled">Fulfilled</option>
                        </select>
                    </div>
                    <div class="col-md-3 filter-item">
                        <label>Main Status</label>
                        <div class="status-toggle" id="statusToggleGroup">
                            <button class="btn active" data-status="all" onclick="updateStatusFilter('all', this)">All</button>
                            <button class="btn" data-status="open" onclick="updateStatusFilter('open', this)">Open</button>
                            <button class="btn" data-status="closed" onclick="updateStatusFilter('closed', this)">Closed</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table custom-table" id="casesTable">
                    <thead>
                        <tr>
                            <th style="width: 8%">Code</th>
                            <th style="width: 12%">Head / For</th>
                            @if(isset($unitNameMap))
                                <th style="width: 10%">Unit</th>
                            @endif
                            <th style="width: 20%">Title / Description</th>
                            <!-- ✅ NAYA COLUMN -->
                            <th style="width: 12%">Case Date</th>
                            <th style="width: 10%" class="text-right">Sub Total</th>
                            <th style="width: 10%" class="text-right">Final Total</th>
                            <th style="width: 15%">Current Stage</th>
                            <th style="width: 13%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $pcs)
                            @php
                                $dbStatus = $pcs->pcs_status;
                                $filterCategory = in_array($dbStatus, ['Completed', 'Fulfilled']) ? 'closed' : 'open';
                            @endphp

                            <tr class="case-row" data-status="{{ $filterCategory }}" data-stage="{{ $dbStatus }}" data-date="{{ $pcs->pcs_date }}">
                                <td class="font-weight-bold text-primary">
                                    {{ $pcs->pcs_type }}-{{ $pcs->pcs_id }}
                                </td>

                                <td>
                                    <span class="font-weight-bold text-light">{{ $pcs->project->prj_code ?? $pcs->pcs_hed_id }}</span>
                                </td>

                                @if(isset($unitNameMap))
                                    <td>
                                        <span class="text-muted font-weight-bold">{{ $unitNameMap[$pcs->pcs_unt_id] ?? '' }}</span>
                                    </td>
                                @endif

                                <td>
                                    <div class="text-truncate" style="max-width: 250px;" title="{{ $pcs->pcs_title }}">
                                        {{ $pcs->pcs_title }}
                                    </div>
                                </td>

                                <!-- ✅ DATE SEPARATE COLUMN MEIN -->
                                <td>
                                    <span class="text-muted small font-weight-bold">
                                        <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($pcs->pcs_date)->format('d M, Y') }}
                                    </span>
                                </td>

                                <td class="text-right text-price">
                                    {{ number_format($pcs->pcs_midprice ?? 0, 2) }}
                                </td>
                                <td class="text-right text-price text-primary">
                                    {{ number_format($pcs->pcs_price ?? 0, 2) }}
                                </td>
                                
                                <td>
                                    @php
                                        $stageColor = match($dbStatus) {
                                            'Draft', 'Under Scrutiny' => 'text-warning',
                                            'Approved' => 'text-primary',
                                            'Completed', 'Fulfilled' => 'text-success',
                                            'Cancelled' => 'text-danger',
                                            default => 'text-secondary'
                                        };
                                    @endphp
                                    <span class="stage-indicator {{ $stageColor }}">
                                        <i class="fas fa-circle"></i> {{ $dbStatus }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <a href="{{ route($detailsRouteName, $pcs->pcs_id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr id="noDataRow">
                                <!-- ✅ COLSPAN 8 KIYA -->
                                <td colspan="{{ isset($unitNameMap) ? 9 : 8 }}" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3"></i><br>
                                    No purchase cases found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div> 
        </div>
    </div>
</div>

<script>
let currentStatusFilter = 'all';

function updateStatusFilter(status, btn) {
    const buttons = document.querySelectorAll('#statusToggleGroup .btn');
    buttons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentStatusFilter = status;
    applyAllFilters();
}

function applyAllFilters() {
    const searchInput = document.getElementById('searchInput').value.toUpperCase();
    const stageSelect = document.getElementById('stageSelect').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const rows = document.querySelectorAll('.case-row');
    const noDataRow = document.getElementById('noDataRow');
    
    let visibleRows = 0;

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        const rowStage = row.getAttribute('data-stage');
        const rowDate = row.getAttribute('data-date');
        const rowText = row.innerText.toUpperCase();

        const matchStatus = (currentStatusFilter === 'all' || rowStatus === currentStatusFilter);
        const matchStage = (stageSelect === 'all' || rowStage === stageSelect);
        const matchText = rowText.includes(searchInput);

        let matchDate = true;
        if (dateFrom && rowDate < dateFrom) matchDate = false;
        if (dateTo && rowDate > dateTo) matchDate = false;

        if (matchStatus && matchStage && matchText && matchDate) {
            row.style.display = '';
            visibleRows++;
        } else {
            row.style.display = 'none';
        }
    });

    if (noDataRow) {
        noDataRow.style.display = (visibleRows === 0) ? '' : 'none';
    }
}
</script>
@endsection
