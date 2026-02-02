@extends('welcome')

@section('content')
<div class="content-wrapper pt-2"> {{-- Reduced pt-3 to pt-2 --}}

    {{-- HEADER --}}
    <div class="content-header pb-1"> {{-- Added pb-1 to reduce bottom padding --}}
        <div class="container-fluid">
            <div class="row mb-2"> {{-- Reduced mb-3 to mb-2 --}}
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h1 id="page-heading" class="m-0 font-weight-bold text-primary" style="font-size: 1.5rem;">
                        <i class="fas fa-folder-open mr-1"></i> All Projects
                    </h1>
                    <a href="{{ route('addnewproject') }}" class="btn btn-primary btn-sm shadow-sm px-4 rounded-pill">
                        <i class="fas fa-plus-circle mr-1"></i> New Project
                    </a>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card card-outline card-primary shadow-sm mb-2"> {{-- Reduced mb-4 to mb-2 --}}
                <div class="card-body py-2"> {{-- Reduced py-3 to py-2 --}}
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-1"> {{-- Reduced mb-2 to mb-1 --}}
                             <label class="small text-muted mb-0">Search</label>
                             <input type="text" id="codeSearch" class="form-control form-control-sm" placeholder="Code or Title..." onkeyup="applyFilters()">
                        </div>
                         <div class="col-md-2 mb-1">
                            <label class="small text-muted mb-0">From Date</label>
                            <input type="date" id="dateFrom" class="form-control form-control-sm" onchange="applyFilters()">
                        </div>
                        <div class="col-md-2 mb-1">
                            <label class="small text-muted mb-0">To Date</label>
                            <input type="date" id="dateTo" class="form-control form-control-sm" onchange="applyFilters()">
                        </div>
                        <div class="col-md-5 mb-1">
                             <label class="small text-muted mb-0">Status</label>
                             <div class="btn-group btn-block shadow-sm">
                                <button class="btn btn-sm btn-outline-primary active filter-btn-main" onclick="setMainFilter('all', this)">All</button>
                                <button class="btn btn-sm btn-outline-primary filter-btn-main" onclick="setMainFilter('open', this)">Open</button>
                                {{-- REMOVED RETURNED FILTER AS REQUESTED --}}
                                <button class="btn btn-sm btn-outline-success filter-btn-main" onclick="setMainFilter('closed', this)">Closed</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    {{-- ADDED FIXED HEIGHT AND OVERFLOW FOR STICKY HEADER SCROLLING --}}
                    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0 text-nowrap" id="projectsTable">
                            <thead class="bg-light text-muted sticky-top shadow-sm" style="z-index: 1;">
                                <tr>
                                    <th style="width: 25%;">Project Details</th>
                                    <th style="width: 20%;">Timeline</th>
                                    <th style="width: 20%;">Financials</th>
                                    <th style="width: 25%;">MPR Status</th>
                                    <th style="width: 10%;" class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                @php
                                    $status = Str::lower($project->prj_status);
                                    
                                    // --- DOCUMENT STATUS CHECK ---
                                    $doc = $project->document; 
                                    $docStatus = $doc ? $doc->status : 'Not Started';
                                    
                                    // --- Change 'Not Started' to 'Pending' ---
                                    if($docStatus == 'Not Started') $docStatus = 'Pending';

                                    // --- Calculation Logic ---
                                    $today = \Carbon\Carbon::now();
                                    $startDate = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : null;
                                    $endDate = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : null;
                                    
                                    $timePercentage = 0;
                                    if ($startDate && $endDate && $status != 'closed') {
                                        $totalDays = $startDate->diffInDays($endDate);
                                        $daysPassed = $startDate->diffInDays($today);
                                        if ($totalDays > 0 && $today->greaterThan($startDate)) {
                                            $timePercentage = ($daysPassed / $totalDays) * 100;
                                        }
                                        $timePercentage = min(100, max(0, $timePercentage));
                                    } elseif ($status == 'closed') {
                                        $timePercentage = 100;
                                    }
                                    
                                    $budget = $project->prj_propcost > 0 ? $project->prj_propcost : 0;
                                    $spent = 0; 
                                    $spentPercentage = ($budget > 0) ? ($spent / $budget) * 100 : 0;
                                    
                                    // Status Color Class
                                    $statusClass = 'text-secondary';
                                    if($docStatus == 'Returned') $statusClass = 'text-danger font-weight-bold';
                                    elseif($docStatus == 'Finalized' || $docStatus == 'Approved') $statusClass = 'text-success font-weight-bold';
                                    elseif($docStatus == 'Pending Review' || $docStatus == 'Under Review by SORD') $statusClass = 'text-warning font-weight-bold';
                                @endphp

                                <tr class="project-row" 
                                    data-code="{{ strtolower($project->prj_code) }}"
                                    data-title="{{ strtolower($project->prj_title) }}"
                                    data-status="{{ $status }}"
                                    data-docstatus="{{ $docStatus }}" 
                                    data-date="{{ $project->prj_rcptdt ? \Carbon\Carbon::parse($project->prj_rcptdt)->format('Y-m-d') : '' }}">
                                    
                                    {{-- Project Details --}}
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-primary">{{ $project->prj_code }}</div>
                                        {{-- REPLACED UNIT NAME WITH PROJECT STATUS --}}
                                        <div class="small">
                                            @if($status == 'open')
                                                <span class="text-success"><i class="fas fa-circle text-xs mr-1"></i>Open</span>
                                            @elseif($status == 'closed')
                                                <span class="text-muted"><i class="fas fa-check-circle text-xs mr-1"></i>Closed</span>
                                            @else
                                                <span class="text-secondary text-capitalize">{{ $project->prj_status }}</span>
                                            @endif
                                        </div>
                                        <div class="text-dark small text-wrap mt-1" style="max-width: 300px; line-height: 1.2;" title="{{ $project->prj_title }}">
                                            {{ $project->prj_title }}
                                        </div>
                                    </td>

                                    {{-- Timeline --}}
                                    <td class="align-middle">
                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                            <span>Start: {{ $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt)->format('d-M-Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="progress progress-xs rounded-pill mb-1" style="height: 6px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $timePercentage }}%"></div>
                                        </div>
                                        <div class="small text-right text-info">{{ round($timePercentage) }}% Elapsed</div>
                                    </td>

                                    {{-- Financials --}}
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark mb-1">
                                            {{ number_format($project->prj_propcost / 1000000, 2) }} M
                                        </div>
                                        <div class="progress progress-xs rounded-pill mb-1" style="height: 6px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $spentPercentage }}%"></div>
                                        </div>
                                        <div class="small text-right text-success">{{ round($spentPercentage) }}% Utilized</div>
                                    </td>

                                    {{-- MPR Status & Buttons (Aligned Side-by-Side) --}}
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <span class="{{ $statusClass }} mr-2">
                                                {{ $docStatus }}
                                            </span>
                                            
                                            {{-- MOVED BUTTONS HERE --}}
                                            @if($docStatus == 'Returned')
                                                <a href="{{ route('mpr.view', $project->prj_id) }}" class="btn btn-xs btn-outline-danger shadow-sm" title="Fix & Resubmit">
                                                    <i class="fas fa-tools mr-1"></i> Fix
                                                </a>
                                            @elseif($docStatus == 'Draft')
                                                <a href="{{ route('mpr.view', $project->prj_id) }}" class="btn btn-xs btn-outline-info shadow-sm" title="Continue Editing">
                                                    <i class="fas fa-pen mr-1"></i> Edit
                                                </a>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Action --}}
                                    <td class="text-right align-middle">
                                        <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-xs btn-primary shadow-sm px-2 rounded-pill" title="View Details">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 d-block text-gray-300"></i>
                                        No projects found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentMainStatus = 'all';

    function setMainFilter(status, btn) {
        currentMainStatus = status;
        document.querySelectorAll('.filter-btn-main').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        applyFilters();
    }

    function applyFilters() {
        const codeSearch = document.getElementById('codeSearch').value.toLowerCase();
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const rows = document.querySelectorAll('.project-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const code = row.dataset.code;
            const title = row.dataset.title;
            const status = row.dataset.status;
            const docStatus = row.dataset.docstatus;
            const date = row.dataset.date;
            
            let show = true;

            // Main Status Filter
            if (currentMainStatus !== 'all') {
                if (currentMainStatus === 'open' && (status === 'closed' || status === 'draft')) show = false;
                else if (currentMainStatus === 'closed' && status !== 'closed') show = false;
            }

            // Search Filter (Code or Title)
            if (codeSearch && !code.includes(codeSearch) && !title.includes(codeSearch)) show = false;
            
            // Date Filter
            if (dateFrom && date < dateFrom) show = false;
            if (dateTo && date > dateTo) show = false;

            row.style.display = show ? 'table-row' : 'none';
            if(show) visibleCount++;
        });

        document.getElementById('page-heading').innerHTML = `<i class="fas fa-folder-open mr-1"></i> All Projects (${visibleCount})`;
    }
</script>

<style>
    /* Table Styling Overrides */
    .table td { vertical-align: middle; font-size: 0.9rem; }
    .btn-xs { padding: 0.2rem 0.6rem; font-size: 0.75rem; line-height: 1.4; border-radius: 4px; }
    .text-xs { font-size: 0.7rem; }
    
    /* Sticky Header Styling */
    .sticky-top { position: sticky; top: 0; background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; }
</style>
@endsection
