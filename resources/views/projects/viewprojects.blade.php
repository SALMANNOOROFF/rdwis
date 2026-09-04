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
                                <button class="btn btn-sm btn-outline-primary filter-btn-main" onclick="setMainFilter('all', this)">All</button>
                                <button class="btn btn-sm btn-outline-primary active filter-btn-main" onclick="setMainFilter('open', this)">Open</button>
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
                                    <th style="width: 75px;" class="text-center p-2 font-weight-bold"><i class="fas fa-eye mr-1"></i> View</th>
                                    <th style="min-width: 250px;" class="p-2">Project Details</th>
                                    <th style="min-width: 100px;" class="text-center p-2">Team</th>
                                    <th style="min-width: 150px;" class="p-2">Timeline</th>
                                    <th style="min-width: 150px;" class="p-2">Financial Standing</th>
                                    <th style="min-width: 150px;" class="text-right p-2">MPR Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                @php
                                    $rawPrjStatus = strtolower(trim($project->prj_status ?? ''));
                                    $isClosed = in_array($rawPrjStatus, ['closed', 'completed', 'cancelled']);
                                    $categoryStatus = $isClosed ? 'closed' : 'open';
                                    
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
                                    $rawStart = $project->prj_startdt ?: ($project->prj_aprvdt ?: ($project->prj_assigndt ?: ($project->prj_propdt ?: $project->prj_rcptdt)));
                                    $startDate = $rawStart ? \Carbon\Carbon::parse($rawStart) : null;
                                    $rawEnd = $project->prj_estenddt ?: ($project->prj_enddt ?: null);
                                    $endDate = $rawEnd ? \Carbon\Carbon::parse($rawEnd) : null;
                                    
                                    $timePercentage = 0;
                                    if ($startDate && $endDate && !$isClosed) {
                                        $totalDays = $startDate->diffInDays($endDate);
                                        $daysPassed = $startDate->diffInDays($today, false);
                                        if ($totalDays > 0 && $today->greaterThan($startDate)) {
                                             $timePercentage = ($daysPassed / $totalDays) * 100;
                                        }
                                        $timePercentage = min(100, max(0, $timePercentage));
                                    } elseif ($isClosed) {
                                        $timePercentage = 100;
                                    }
                                    
                                    $budget = $project->prj_propcost > 0 ? $project->prj_propcost : 0;
                                    $spent = $project->spent ?? 0; 
                                    $spentPercentage = ($budget > 0) ? ($spent / $budget) * 100 : 0;

                                    // Milestone counts
                                    $totalMs = $project->milestones ? $project->milestones->count() : 0;
                                    $completedMs = $project->milestones ? $project->milestones->where('msn_status', 'Completed')->count() : 0;
                                    $msPercentage = $totalMs > 0 ? ($completedMs / $totalMs) * 100 : 0;
                                    
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
                                    data-status="{{ $categoryStatus }}"
                                    data-docstatus="{{ $docStatus }}" 
                                    data-date="{{ $project->prj_rcptdt ? \Carbon\Carbon::parse($project->prj_rcptdt)->format('Y-m-d') : '' }}">
                                    
                                    {{-- 1. LEFT ACTION BUTTON --}}
                                    <td class="align-middle text-center p-2 border-right" style="width: 75px;">
                                        <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-xs btn-primary font-weight-bold px-2.5 py-1.5 shadow-sm" style="border-radius: 6px; font-size: 0.78rem; background-color: var(--rd-primary-600) !important; border-color: var(--rd-primary-700) !important; white-space: nowrap;" title="View Project Details">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>

                                    {{-- 2. PROJECT DETAILS (Code + Status on one line, Title below) --}}
                                    <td class="align-middle p-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="badge mr-2" style="font-size: 0.8rem; background-color: var(--rd-primary-600) !important; border: 1px solid var(--rd-primary-700) !important; color: #ffffff !important; font-weight: 700; letter-spacing: 0.5px; padding: 3px 8px; border-radius: 4px;">{{ $project->prj_code }}</span>
                                            <span class="badge {{ $isClosed ? 'badge-secondary' : 'badge-success' }} text-uppercase" style="font-size: 0.65rem;">
                                                {{ $project->prj_status }}
                                            </span>
                                        </div>
                                        <div class="font-weight-bold text-dark text-truncate" style="max-width: 300px; font-size: 0.9rem;" title="{{ $project->prj_title }}">
                                            {{ $project->prj_title }}
                                        </div>
                                        <div class="mt-1">
                                            <a href="{{ route('projects.financial_view', $project->prj_id) }}" class="btn btn-xs btn-outline-info font-weight-bold" style="font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;" title="Open Financial View">
                                                <i class="fas fa-chart-line mr-1"></i> Financial View
                                            </a>
                                        </div>
                                    </td>

                                    {{-- 3. TEAM / EMPLOYEES COUNT --}}
                                    <td class="align-middle text-center p-2">
                                        @if(($project->emp_count ?? 0) > 0)
                                            <span class="badge badge-pill font-weight-bold text-white px-2.5 py-1 shadow-xs" style="background-color: #0284c7; font-size: 11px;" title="{{ $project->emp_count }} Active Employees Assigned">
                                                <i class="fas fa-users mr-1 text-xs"></i>{{ $project->emp_count }} {{ $project->emp_count === 1 ? 'Emp' : 'Emps' }}
                                            </span>
                                        @else
                                            <span class="badge badge-pill badge-light border text-muted px-2 py-0.5" style="font-size: 10px;" title="No active employees">
                                                <i class="fas fa-user-slash mr-1 text-xs"></i>0
                                            </span>
                                        @endif
                                    </td>

                                    {{-- 4. TIMELINE & MILESTONES --}}
                                    <td class="align-middle p-2">
                                        <div class="d-flex justify-content-between text-muted text-xs mb-1">
                                            <span>Start: <b>{{ $startDate ? $startDate->format('d-M-y') : 'N/A' }}</b></span>
                                            <span>End: <b>{{ $endDate ? $endDate->format('d-M-y') : 'N/A' }}</b></span>
                                        </div>
                                        <div class="progress progress-xs rounded-pill mb-1" style="height: 4px;">
                                            <div class="progress-bar {{ $timePercentage > 90 ? 'bg-danger' : 'bg-primary' }}" role="progressbar" style="width: {{ $timePercentage }}%"></div>
                                        </div>
                                        <div class="d-flex justify-content-between text-xs">
                                            <span>Milestones ({{ $completedMs }}/{{ $totalMs }}):</span>
                                            <span class="text-success font-weight-bold">{{ round($msPercentage) }}%</span>
                                        </div>
                                    </td>

                                    {{-- 5. FINANCIALS --}}
                                    <td class="align-middle p-2">
                                        <div class="d-flex justify-content-between text-muted text-xs mb-1">
                                            <span>Spent: <b>{{ number_format($spent / 1000000, 2) }} M</b></span>
                                            <span>Budget: <b>{{ number_format($budget / 1000000, 2) }} M</b></span>
                                        </div>
                                        <div class="progress progress-xs rounded-pill mb-1" style="height: 4px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $spentPercentage }}%"></div>
                                        </div>
                                        <div class="text-right text-xs">
                                            <span class="text-success font-weight-bold">{{ round($spentPercentage, 1) }}% Utilized</span>
                                        </div>
                                    </td>

                                    {{-- 6. MPR STATUS (Far Right) --}}
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
                                    <td colspan="6" class="text-center py-5 text-muted">
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
    let currentMainStatus = 'open';

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
                if (currentMainStatus === 'open' && status !== 'open') show = false;
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

        const label = currentMainStatus === 'open' ? 'Open Projects' : (currentMainStatus === 'closed' ? 'Closed Projects' : 'All Projects');
        document.getElementById('page-heading').innerHTML = `<i class="fas fa-folder-open mr-1"></i> ${label} (${visibleCount})`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        applyFilters();
    });
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
