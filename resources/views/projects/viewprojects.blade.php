@extends('welcome')

@section('content')
<div class="content-wrapper pt-2"> {{-- Reduced pt-3 to pt-2 --}}

    {{-- HEADER --}}
    <div class="content-header pb-1">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12 d-flex flex-wrap justify-content-between align-items-center" style="gap: 10px;">
                    <h1 id="page-heading" class="m-0 font-weight-bold text-primary" style="font-size: 1.5rem; font-family:'Rajdhani',sans-serif; letter-spacing:1px;">
                        <i class="fas fa-folder-open mr-1"></i> ALL PROJECTS
                    </h1>
                    <a href="{{ route('addnewproject') }}" class="btn btn-primary btn-sm shadow-sm px-4 rounded-pill">
                        <i class="fas fa-plus-circle mr-1"></i> New Project
                    </a>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card card-outline card-primary shadow-sm mb-2">
                <div class="card-body py-2">
                    <div class="row align-items-end">
                        <div class="col-md-3 col-sm-6 mb-2">
                             <label class="small text-muted mb-0">Search</label>
                             <input type="text" id="codeSearch" class="form-control form-control-sm" placeholder="Code or Title..." onkeyup="applyFilters()">
                        </div>
                         <div class="col-md-2 col-sm-3 col-6 mb-2">
                            <label class="small text-muted mb-0">From</label>
                            <input type="date" id="dateFrom" class="form-control form-control-sm" onchange="applyFilters()">
                        </div>
                        <div class="col-md-2 col-sm-3 col-6 mb-2">
                            <label class="small text-muted mb-0">To</label>
                            <input type="date" id="dateTo" class="form-control form-control-sm" onchange="applyFilters()">
                        </div>
                        <div class="col-md-5 col-12 mb-2">
                             <label class="small text-muted mb-0">Status Filter</label>
                             <div class="btn-group btn-block shadow-sm">
                                <button class="btn btn-sm btn-outline-primary active filter-btn-main" onclick="setMainFilter('all', this)">All</button>
                                <button class="btn btn-sm btn-outline-primary filter-btn-main" onclick="setMainFilter('open', this)">Open</button>
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
                    <div class="rd-table-responsive" style="max-height: 75vh; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0 text-nowrap" id="projectsTable">
                            <thead class="bg-light text-muted sticky-top shadow-sm" style="z-index: 1;">
                                <tr>
                                    <th style="width: 50px;" class="text-center p-2"><i class="fas fa-eye"></i></th>
                                    <th style="min-width: 250px;" class="p-2">Project Details</th>
                                    <th style="min-width: 150px;" class="p-2">Timeline</th>
                                    <th style="min-width: 150px;" class="p-2">Financial Standing</th>
                                    <th style="min-width: 150px;" class="text-right p-2">MPR Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                @php
                                    $status = Str::lower($project->prj_status);
                                    
                                    // --- DOCUMENT STATUS CHECK ---
                                    try {
                                        $hasDoc = \Illuminate\Support\Facades\Schema::hasTable('doc.documents');
                                        $doc = $hasDoc ? $project->document : null; 
                                        $docStatus = $doc ? $doc->status : 'Not Started';
                                    } catch (\Exception $e) {
                                        $docStatus = 'Not Started';
                                        $doc = null;
                                    }
                                    
                                    // --- Change 'Not Started' to 'Action Awaited' ---
                                    if($docStatus == 'Not Started' || $docStatus == 'Draft') $docStatus = 'Action Awaited';

                                    // --- Change 'Pending Review' to 'Forwarded' ---
                                    if($docStatus == 'Pending Review' || $docStatus == 'Under Review by SORD') $docStatus = 'Forwarded';

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
                                    elseif($docStatus == 'Forwarded') $statusClass = 'text-info font-weight-bold';
                                    elseif($docStatus == 'Action Awaited') $statusClass = 'text-warning font-weight-bold';
                                @endphp

                                <tr class="project-row" 
                                    data-code="{{ strtolower($project->prj_code) }}"
                                    data-title="{{ strtolower($project->prj_title) }}"
                                    data-status="{{ $status }}"
                                    data-docstatus="{{ $docStatus }}" 
                                    data-date="{{ $project->prj_rcptdt ? \Carbon\Carbon::parse($project->prj_rcptdt)->format('Y-m-d') : '' }}">
                                    
                                    {{-- 1. LEFT ACTION BUTTON (Tall & Slim) --}}
                                    <td class="align-middle p-0 text-center border-right">
                                        <a href="{{ route('projects.show', $project->prj_id) }}" class="vertical-btn d-block text-white bg-primary shadow-hover h-100" title="View Details">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </td>

                                    {{-- 2. PROJECT DETAILS (Code + Status on one line, Title below) --}}
                                    <td class="align-middle p-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="font-weight-bold text-primary mr-2" style="font-size: 1rem;">{{ $project->prj_code }}</span>
                                            @if($status == 'open')
                                                <span class="badge badge-success px-2 py-0"><i class="fas fa-circle text-xs mr-1"></i> Open</span>
                                            @elseif($status == 'closed')
                                                <span class="badge badge-secondary px-2 py-0"><i class="fas fa-check-circle text-xs mr-1"></i> Closed</span>
                                            @else
                                                <span class="badge badge-info px-2 py-0 text-capitalize">{{ $project->prj_status }}</span>
                                            @endif
                                        </div>
                                        <div class="text-dark small text-truncate" style="max-width: 350px;" title="{{ $project->prj_title }}">
                                            {{ $project->prj_title }}
                                        </div>
                                    </td>

                                    {{-- 3. TIMELINE --}}
                                    <td class="align-middle p-2">
                                        <div class="d-flex justify-content-between text-muted text-xs mb-1">
                                            <span>{{ $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt)->format('d-M-Y') : 'N/A' }}</span>
                                            <span class="text-info font-weight-bold">{{ round($timePercentage) }}%</span>
                                        </div>
                                        <div class="progress progress-xs rounded-pill" style="height: 4px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $timePercentage }}%"></div>
                                        </div>
                                    </td>

                                    {{-- 4. FINANCIALS --}}
                                    <td class="align-middle p-2">
                                        <div class="d-flex justify-content-between text-dark text-xs mb-1">
                                            <span class="font-weight-bold">{{ number_format($project->prj_propcost / 1000000, 2) }} M</span>
                                            <span class="text-success font-weight-bold">{{ round($spentPercentage) }}%</span>
                                        </div>
                                        <div class="progress progress-xs rounded-pill" style="height: 4px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $spentPercentage }}%"></div>
                                        </div>
                                    </td>

                                    {{-- 5. MPR STATUS (Far Right) --}}
                                    <td class="align-middle text-right p-2">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <span class="{{ $statusClass }} text-sm mr-2">{{ $docStatus }}</span>
                                            
                                            @if($docStatus == 'Returned')
                                                <a href="{{ route('mpr.view', $project->prj_id) }}" class="btn btn-xs btn-outline-danger shadow-sm rounded-circle" title="Fix & Resubmit" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-tools text-xs"></i>
                                                </a>
                                            @elseif($docStatus == 'Draft' || $docStatus == 'Action Awaited')
                                                <a href="{{ route('mpr.view', $project->prj_id) }}" class="btn btn-xs btn-outline-primary shadow-sm rounded-circle" title="Edit MPR" style="width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </a>
                                            @endif
                                        </div>
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
    /* Compact Table Styling */
    .table td { vertical-align: middle; font-size: 0.85rem; padding: 0.5rem; }
    .btn-xs { padding: 0.1rem 0.4rem; font-size: 0.7rem; line-height: 1.2; border-radius: 4px; }
    .text-xs { font-size: 0.7rem; }
    
    /* Sticky Header */
    .sticky-top { position: sticky; top: 0; background-color: var(--rd-surface2); border-bottom: 2px solid var(--rd-border); }

    /* Vertical Action Button */
    .vertical-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 60px; /* Fixed height for the button bar */
        transition: background-color 0.2s;
        border-radius: 0 4px 4px 0; /* Rounded right corners */
    }
    .vertical-btn:hover {
        background-color: var(--rd-accent-dark) !important; /* Darker blue on hover */
    }
    .shadow-hover:hover {
        box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
    }
</style>
@endsection
