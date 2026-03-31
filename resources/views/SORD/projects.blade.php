@extends('welcome')

@section('content')
@php
    $actionRequiredCount = 0;
    foreach($projects as $p) {
         $d = $p->document;
         if($d && ($d->status == 'Pending Review' || $d->status == 'Under Review by SORD')) {
             $actionRequiredCount++;
         }
    }
@endphp

<div class="content-wrapper pt-2">

    {{-- HEADER --}}
    <div class="content-header pb-1">
        <div class="container-fluid">
            
            {{-- NOTIFICATION ALERT --}}
            @if($actionRequiredCount > 0)
            <div class="alert alert-warning shadow-sm border-left-warning d-flex align-items-center mb-3">
                <i class="fas fa-bell fa-lg text-warning mr-3"></i>
                <div>
                    <strong>Action Required!</strong>
                    You have <span class="badge badge-warning mx-1 text-white">{{ $actionRequiredCount }}</span> MPR(s) waiting for your review.
                </div>
            </div>
            @endif

            <div class="row mb-2">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h1 id="page-heading" class="m-0 font-weight-bold" style="font-size: 1.5rem; color: var(--rd-accent);">
                        <i class="fas fa-layer-group mr-1"></i> All Divisions Projects <span class="text-sm ml-2" style="color: var(--rd-text3);">({{ $projects->count() }})</span>
                    </h1>
                    <div>
                        <a href="{{ route('sord.compile_report') }}" class="btn btn-info btn-sm shadow-sm px-4 rounded-pill mr-2">
                            <i class="fas fa-file-word mr-1"></i> Generate Report
                        </a>
                        <a href="{{ route('sord.mpr_log') }}" class="btn btn-primary btn-sm shadow-sm px-4 rounded-pill">
                            <i class="fas fa-list-alt mr-1"></i> Global MPR Log
                        </a>
                    </div>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card card-outline card-primary shadow-sm mb-2">
                <div class="card-body py-2">
                    <div class="row align-items-end">
                        
                        {{-- DIVISION FILTER (SORD SPECIFIC) --}}
                        <div class="col-md-3 mb-1">
                            <label class="small text-muted mb-0">Select Division</label>
                            <select id="divisionFilter" class="form-control form-control-sm" onchange="applyFilters()">
                                <option value="all">All Divisions</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->unt_id }}">{{ $div->unt_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 mb-1">
                             <label class="small text-muted mb-0">Search</label>
                             <input type="text" id="codeSearch" class="form-control form-control-sm" placeholder="Code or Title..." onkeyup="applyFilters()">
                        </div>

                         <div class="col-md-3 mb-2">
                            <label class="small text-muted">MPR Status</label>
                            <select id="mprStatusFilter" class="form-control form-control-sm" onchange="applyFilters()">
                                <option value="all">All Statuses</option>
                                <option value="action required">Action Required</option>
                                <option value="awaited from division">Awaited From Division</option>
                                <option value="returned to division">Returned</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small text-muted">Status</label>
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
                    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0 text-nowrap" id="projectsTable">
                            <thead class="sticky-top shadow-sm" style="z-index: 1; background-color: var(--rd-surface2); color: var(--rd-text3);">
                                <tr>
                                    <th style="width: 50px;" class="text-center p-2"><i class="fas fa-eye"></i></th>
                                    <th style="width: 30%;">Project Details</th>
                                    <th style="width: 20%;">Timeline</th>
                                    <th style="width: 20%;">Financials</th>
                                    <th style="width: 25%;" class="text-center p-2">MPR Status</th>
                                </tr>
                            </thead>
                            
                            @forelse($projects->groupBy('unit.unt_name') as $divisionName => $divProjects)
                            @php
                                $divId = Str::slug($divisionName) . '-projects';
                                $divCount = $divProjects->count();

                                // Calculate breakdown for header badges
                                $redCount = 0;   // Action Required / Returned
                                $blueCount = 0;  // Awaited / Draft
                                $greenCount = 0; // Completed

                                foreach($divProjects as $p) {
                                    $d = $p->document;
                                    $st = $d ? $d->status : 'Not Started';

                                    if(in_array($st, ['Pending Review', 'Under Review by SORD', 'Returned to Division', 'Returned'])) {
                                        $redCount++;
                                    } elseif(in_array($st, ['Finalized', 'Approved', 'Forwarded to MD'])) {
                                        $greenCount++;
                                    } else {
                                        $blueCount++;
                                    }
                                }
                            @endphp
                            
                            {{-- DIVISION HEADER (Clickable for Collapse) --}}
                            <tbody class="division-group" data-division-id="{{ $divProjects->first()->unit->unt_id ?? '' }}">
                                <tr class="bg-light division-header collapsed" role="button" data-toggle="collapse" data-target="#{{ $divId }}" aria-expanded="false" aria-controls="{{ $divId }}" style="cursor: pointer;">
                                    <td colspan="5" class="font-weight-bold py-2 px-3" style="background-color: var(--rd-surface2); color: var(--rd-text1);">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>
                                                <i class="fas fa-building text-secondary mr-2"></i> {{ $divisionName ?? 'Unknown Division' }}
                                                <span class="badge badge-pill badge-secondary ml-2" title="Total Projects">{{ $divCount }} Total</span>
                                                
                                                @if($redCount > 0)
                                                <span class="badge badge-pill badge-danger ml-1" title="Action Required / Returned">{{ $redCount }}</span>
                                                @endif
                                                @if($blueCount > 0)
                                                <span class="badge badge-pill badge-info ml-1" title="Awaited">{{ $blueCount }}</span>
                                                @endif
                                                @if($greenCount > 0)
                                                <span class="badge badge-pill badge-success ml-1" title="Completed">{{ $greenCount }}</span>
                                                @endif
                                            </span>
                                            <i class="fas fa-chevron-down text-muted small"></i>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>

                            {{-- PROJECTS LIST (Collapsible Body - DEFAULT HIDDEN) --}}
                            <tbody id="{{ $divId }}" class="collapse project-list-body">
                                @foreach($divProjects as $project)
                                @php
                                    $status = Str::lower($project->prj_status);
                                    
                                    // --- DOCUMENT STATUS CHECK ---
                                    $doc = $project->document; 
                                    $docStatus = $doc ? $doc->status : 'Not Started';
                                    
                                    // --- SORD MAPPING LOGIC ---
                                    if($docStatus == 'Pending Review' || $docStatus == 'Under Review by SORD') $docStatus = 'Action Required';
                                    elseif($docStatus == 'Not Started' || $docStatus == 'Draft') $docStatus = 'Awaited from Division';
                                    elseif($docStatus == 'Returned') $docStatus = 'Returned to Division';
                                    elseif($docStatus == 'Finalized' || $docStatus == 'Approved' || $docStatus == 'Forwarded to MD') $docStatus = 'Finalized';

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
                                    if($docStatus == 'Action Required') $statusClass = 'text-warning font-weight-bold';
                                    elseif($docStatus == 'Finalized') $statusClass = 'text-success font-weight-bold';
                                    elseif($docStatus == 'Awaited from Division' || $docStatus == 'Returned to Division') $statusClass = 'text-info font-weight-bold';
                                @endphp

                                <tr class="project-row" 
                                    data-code="{{ strtolower($project->prj_code) }}"
                                    data-title="{{ strtolower($project->prj_title) }}"
                                    data-status="{{ $status }}"
                                    data-division="{{ $project->prj_unt_id }}"
                                    data-mpr-status="{{ strtolower($docStatus) }}"
                                    data-date="{{ $project->prj_rcptdt ? \Carbon\Carbon::parse($project->prj_rcptdt)->format('Y-m-d') : '' }}">
                                    
                                    {{-- 1. LEFT ACTION BUTTON --}}
                                    <td class="align-middle p-0 text-center border-right">
                                        <a href="{{ route('sord.project_details', $project->prj_id) }}" class="vertical-btn d-block text-white bg-primary shadow-hover h-100" title="View Details">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </td>

                                    {{-- Project Details --}}
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
                                        <div class="text-truncate small" style="max-width: 350px; color: var(--rd-text1);" title="{{ $project->prj_title }}">
                                            {{ $project->prj_title }}
                                        </div>
                                    </td>

                                    {{-- Timeline --}}
                                    <td class="align-middle p-2">
                                        <div class="d-flex justify-content-between small text-muted text-xs mb-1">
                                            <span>Starts: {{ $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt)->format('d-M-Y') : 'N/A' }}</span>
                                            <span class="text-info font-weight-bold">{{ round($timePercentage) }}%</span>
                                        </div>
                                        <div class="progress progress-xs rounded-pill" style="height: 4px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $timePercentage }}%"></div>
                                        </div>
                                    </td>

                                    {{-- Financials --}}
                                    <td class="align-middle p-2">
                                        <div class="d-flex justify-content-between text-xs mb-1" style="color: var(--rd-text1);">
                                            <span class="font-weight-bold">{{ number_format($project->prj_propcost / 1000000, 2) }} M</span>
                                            <span class="text-success font-weight-bold">{{ round($spentPercentage) }}%</span>
                                        </div>
                                        <div class="progress progress-xs rounded-pill" style="height: 4px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $spentPercentage }}%"></div>
                                        </div>
                                    </td>

                                    {{-- MPR Status & Action (Centered) --}}
                                    <td class="align-middle text-center p-2">
                                        {{-- STATUS DISPLAY --}}
                                        <div class="d-flex align-items-center justify-content-center">
                                            <span class="{{ $statusClass }} text-sm mr-2 text-wrap" style="max-width: 140px; text-align: center;">
                                                {{ $docStatus }}
                                            </span>

                                            {{-- ACTION BUTTONS --}}
                                            @if($docStatus == 'Action Required')
                                                <a href="{{ route('sord.review_mpr', $doc->doc_id) }}" class="btn btn-xs btn-primary shadow-sm rounded-circle" title="Review Now" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-pencil-alt text-xs"></i>
                                                </a>
                                            @elseif($docStatus == 'Returned to Division')
                                                <a href="{{ route('sord.review_mpr', $doc->doc_id) }}" class="btn btn-xs btn-outline-danger shadow-sm rounded-circle" title="View Status" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                            @elseif($docStatus == 'Finalized')
                                                 <a href="{{ route('sord.review_mpr', $doc->doc_id) }}" class="btn btn-xs btn-outline-dark shadow-sm rounded-circle" title="View Finalized Data" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            @empty
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 d-block text-gray-300"></i>
                                        No projects found.
                                    </td>
                                </tr>
                            </tbody>
                            @endforelse
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
        const divisionFilter = document.getElementById('divisionFilter').value;
        const codeSearch = document.getElementById('codeSearch') ? document.getElementById('codeSearch').value.toLowerCase() : '';
        const mprStatusFilter = document.getElementById('mprStatusFilter').value.toLowerCase();
        
        // Hide/Show entire division groups based on filters
        // Logic: 
        // 1. Iterate through groups.
        // 2. If division filter is active, hide groups that don't match.
        // 3. Iterate through rows in visible groups.
        // 4. Apply other filters.
        // 5. If all rows in a group are hidden, hide the group header too.

        const divisionGroups = document.querySelectorAll('.division-group');
        let totalVisible = 0;

        divisionGroups.forEach(group => {
            const groupId = group.dataset.divisionId;
            const targetBodyId = group.querySelector('tr').dataset.target.substring(1); // Remove '#'
            const projectBody = document.getElementById(targetBodyId);
            const rows = projectBody.querySelectorAll('.project-row');
            
            let groupVisible = true;

            // 1. Division Filter (Top Level)
            if (divisionFilter !== 'all' && groupId != divisionFilter) {
                groupVisible = false;
            }

            if (!groupVisible) {
                group.style.display = 'none';
                projectBody.style.display = 'none'; // Ensure content hidden
                return; // Skip processing rows for hidden group
            } else {
                group.style.display = '';
                // Ensure body is visible if it was collapsed by user interaction?? No, respect filter.
                // We should probably show it if it matches the filter, but respect the 'collapse' state if unmodified?
                // For simplified UX, let's keep user state or default state.
                // projectBody.style.display = ''; // Don't force display, it might be collapsed
            }

            // 2. Row Level Filters
            let visibleRowsInGroup = 0;
            rows.forEach(row => {
                const code = row.dataset.code;
                const title = row.dataset.title;
                const status = row.dataset.status;
                const mprStatus = row.dataset.mprStatus;
                
                let show = true;

                // Effective Status Logic (Treat 'finalized' MPR as 'closed')
                let effectiveStatus = status;
                if(mprStatus === 'finalized') {
                    effectiveStatus = 'closed';
                }

                // Main Status Filter
                if (currentMainStatus !== 'all') {
                    if (currentMainStatus === 'open' && (effectiveStatus === 'closed' || effectiveStatus === 'draft')) show = false;
                    else if (currentMainStatus === 'closed' && effectiveStatus !== 'closed') show = false;
                }

                // MPR Status Filter
                if (mprStatusFilter !== 'all' && mprStatus !== mprStatusFilter) show = false;

                // Search Filter
                if (codeSearch && !code.includes(codeSearch) && !title.includes(codeSearch)) show = false;
                
                row.style.display = show ? 'table-row' : 'none';
                if(show) visibleRowsInGroup++;
            });

            // 3. Auto-Hide Group if no matching rows
            if (visibleRowsInGroup === 0) {
                group.style.display = 'none';
                projectBody.classList.remove('show'); // Collapse empty groups
            } else {
                group.style.display = ''; // Show header
                totalVisible += visibleRowsInGroup;
            }
        });

        // Update count
        const headingEl = document.getElementById('page-heading');
        if(headingEl) {
             // Keep original text, just update count
             const originalText = 'All Divisions Projects'; 
             headingEl.innerHTML = `<i class="fas fa-layer-group mr-1"></i> ${originalText} <span class="text-muted text-sm ml-2">(${totalVisible})</span>`;
        }
    }
</script>

<style>
    /* Compact Table Styling */
    .table td { vertical-align: middle; font-size: 0.85rem; padding: 0.35rem !important; }
    .btn-xs { padding: 0.1rem 0.3rem; font-size: 0.7rem; line-height: 1.1; border-radius: 4px; }
    .text-xs { font-size: 0.7rem; }
    
    /* Sticky Header */
    .sticky-top { position: sticky; top: 0; background-color: var(--rd-surface2); border-bottom: 2px solid var(--rd-border); }

    /* Vertical Action Button */
    .vertical-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 50px; /* Reduced to match user requests */
        min-height: 100%;
        transition: background-color 0.2s;
        border-radius: 0; /* Flat look with column */
    }
    .vertical-btn:hover {
        background-color: var(--rd-accent-dark) !important; /* Darker blue on hover */
    }
    .shadow-hover:hover {
        box-shadow: inset 0 0 10px rgba(0,0,0,0.3);
    }
</style>

@endsection
