@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">

<style>
    
.mpr-status-card h5 { font-size: 1rem; }
.mpr-status-card small { font-size: 0.65rem; }
.mpr-status-card .progress { height: 6px; }

    </style>
{{-- HEADER --}}
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h1 id="page-heading" class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-folder-open mr-1"></i> All Projects
                </h1>
                <a href="{{ route('addnewproject') }}" class="btn btn-primary shadow-sm px-4" style="border-radius: 20px;">
                    <i class="fas fa-plus-circle mr-1"></i> New Project
                </a>
            </div>
        </div>

        {{-- FILTER CARD --}}
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-body py-3">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted">Project Code</label>
                        <div class="dropdown" id="searchDropdown">
                            <button class="btn btn-light border form-control text-left d-flex justify-content-between align-items-center" type="button" data-toggle="dropdown">
                                <span id="selectedCount" class="text-muted">Select Codes</span>
                                <i class="fas fa-chevron-down small text-primary"></i>
                            </button>
                            <div class="dropdown-menu w-100 p-2 shadow-sm" style="max-height: 280px; overflow-y: auto;">
                                <input type="text" class="form-control form-control-sm mb-2" placeholder="Search..." onkeyup="filterCodeList(this)">
                                <div id="codeListContainer"></div>
                                <div class="dropdown-divider"></div>
                                <button class="btn btn-xs btn-link text-danger p-0" onclick="clearCodeFilter()">Clear</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted">From Date</label>
                        <input type="date" id="dateFrom" class="form-control form-control-sm" onchange="applyFilters()">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted">To Date</label>
                        <input type="date" id="dateTo" class="form-control form-control-sm" onchange="applyFilters()">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted">Stage</label>
                        <select id="stageFilter" class="form-control form-control-sm" onchange="applyFilters()">
                            <option value="all">All</option>
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                            <option value="started">Started</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted">Status</label>
                        <div class="btn-group btn-block shadow-sm">
                            <button class="btn btn-sm btn-outline-primary active filter-btn-main" onclick="setMainFilter('all', this)">All</button>
                            <button class="btn btn-sm btn-outline-primary filter-btn-main" onclick="setMainFilter('open', this)">Open</button>
                            <button class="btn btn-sm btn-outline-warning filter-btn-main" onclick="setMainFilter('draft', this)">Drafts</button>
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
        <div class="row" id="projectsContainer">
            @forelse($projects as $project)
            @php
                $status = Str::lower($project->prj_status);
                $today = \Carbon\Carbon::now();
                $startDate = $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt) : null;
                $endDate = $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt) : null;

                if ($status !== 'draft' && $endDate && $today->greaterThan($endDate)) $status = 'closed';

                $timePercentage = 0;
                if ($startDate && $endDate) {
                    $totalDays = $startDate->diffInDays($endDate);
                    $daysPassed = $startDate->diffInDays($today);
                    if ($totalDays > 0 && $today->greaterThan($startDate)) $timePercentage = ($daysPassed / $totalDays) * 100;
                    if ($timePercentage > 100) $timePercentage = 100;
                    if ($today->lessThan($startDate)) $timePercentage = 0;
                } elseif ($status == 'closed') {
                    $timePercentage = 100;
                }

                $budget = $project->prj_propcost > 0 ? $project->prj_propcost : 1;
                $spent = 0; 
                if($status == 'closed') $spent = $budget * 0.95; 
                elseif($status == 'open') $spent = $budget * ($timePercentage / 100); 
                
                $spentPercentage = ($spent / $budget) * 100;
            @endphp

            <div class="col-12 project-card-wrapper">
                <div class="card project-card shadow-sm mb-3"
                     data-code="{{ $project->prj_code }}"
                     data-status="{{ $status }}"
                     data-date="{{ \Carbon\Carbon::parse($project->prj_rcptdt)->format('Y-m-d') }}">
                    <div class="card-body p-0">
                        <div class="row no-gutters align-items-center" style="min-height: 110px;">
                            {{-- LEFT --}}
                            <div class="col-md-4 p-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="badge badge-light border">{{ $project->prj_code }}</span>
                                    @if($status == 'draft')
                                        <span class="badge badge-warning">DRAFT</span>
                                    @elseif($status == 'closed')
                                        <span class="badge badge-success">COMPLETED</span>
                                    @else
                                        <span class="badge badge-primary">IN PROGRESS</span>
                                    @endif
                                </div>
                                <h5 class="text-dark font-weight-bold text-truncate mb-2" title="{{ $project->prj_title }}">
                                    {{ $project->prj_title }}
                                </h5>

                                <div class="mini-steps-container">
                                    <div class="mini-step {{ $project->prj_aprvdt ? 'done' : '' }}">
                                        <div class="step-dot"></div><span class="step-label">Appr</span>
                                    </div>
                                    <div class="mini-step {{ $startDate ? 'done' : '' }}">
                                        <div class="step-dot"></div><span class="step-label">Start</span>
                                    </div>
                                    <div class="mini-step {{ $status == 'closed' ? 'done' : '' }}">
                                        <div class="step-dot"></div><span class="step-label">End</span>
                                    </div>
                                </div>
                            </div>

                            {{-- CENTER --}}
                            <div class="col-md-6 p-3">
                                <div class="row align-items-center">
                                    {{-- PIE CHART --}}
                                    <div class="col-md-4 text-center">
                                        <div class="pie-wrapper" style="background: conic-gradient(#007bff {{ $spentPercentage }}%, #e9ecef 0);">
                                            <div class="pie-inner">
                                                <span class="pie-label text-muted">Spent</span>
                                                <span class="pie-val">{{ round($spentPercentage) }}%</span>
                                            </div>
                                        </div>
                                        <div class="mt-1 small text-muted font-weight-bold">
                                            Rs. {{ number_format($budget/1000000, 1) }}M
                                        </div>
                                    </div>

                                    {{-- MPR STATUS CARD --}}
                                    <div class="col-md-8">
                                        <div class="mpr-status-card mt-2 p-2 shadow-sm rounded">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="font-weight-bold m-0 text-primary"><i class="fas fa-file-invoice mr-1"></i> MPR Status</h6>
                                                <span class="badge badge-primary">{{ $project->mprsSubmitted + $project->mprsLeft }} Total</span>
                                            </div>
                                            <div class="d-flex justify-content-between text-center">
                                                <div class="flex-fill border-right pr-2">
                                                    <h5 class="font-weight-bold text-success m-0">{{ $project->mprsSubmitted }}</h5>
                                                    <small class="text-muted text-uppercase font-weight-bold">Submitted</small>
                                                </div>
                                                <div class="flex-fill pl-2">
                                                    <h5 class="font-weight-bold text-danger m-0">{{ $project->mprsLeft }}</h5>
                                                    <small class="text-muted text-uppercase font-weight-bold">Remaining</small>
                                                </div>
                                            </div>
                                            @php
                                                $mprPercent = ($project->totalMonths > 0) ? ($project->mprsSubmitted / $project->totalMonths) * 100 : 0;
                                            @endphp
                                            <div class="progress mt-2">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $mprPercent }}%"></div>
                                            </div>
                                        </div>

                                        {{-- TIMELINE BAR --}}
                                        <div class="mt-2">
                                            <div class="d-flex justify-content-between small font-weight-bold text-muted mb-1">
                                                <span>{{ $startDate ? $startDate->format('d M Y') : 'TBD' }}</span>
                                                <span>{{ $endDate ? $endDate->format('d M Y') : 'TBD' }}</span>
                                            </div>
                                            <div class="progress" style="height: 8px; border-radius: 5px; background: #f1f1f1;">
                                                <div class="progress-bar {{ $status == 'closed' ? 'bg-success' : 'bg-info' }} progress-bar-striped" 
                                                     role="progressbar" 
                                                     style="width: {{ $timePercentage }}%">
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-center mt-2 align-items-center" style="font-size: 0.8rem;">
                                                <span class="text-muted mr-2">
                                                    Today: <strong>{{ \Carbon\Carbon::now()->format('d M Y') }}</strong>
                                                </span>
                                                <span class="text-muted mx-1">|</span>
                                                <span class="{{ $status == 'closed' ? 'text-success' : 'text-info' }} font-weight-bold ml-2">
                                                    @if($status == 'closed')
                                                        Closed
                                                    @elseif($status == 'draft')
                                                        Drafting
                                                    @else
                                                        {{ round($timePercentage) }}% Elapsed
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- RIGHT --}}
                            <div class="col-md-2 text-center p-3">
                                @if($status == 'draft')
                                    <a href="{{ route('addnewproject', ['draft_id' => $project->prj_id]) }}" 
                                       class="btn btn-warning shadow-sm btn-block font-weight-bold">
                                        <i class="fas fa-pen mb-1 d-block" style="font-size: 1.2rem;"></i> Continue
                                    </a>
                                @else
                                    <a href="{{ route('projects.show', $project->prj_id) }}" 
                                       class="btn btn-outline-primary shadow-sm btn-block font-weight-bold">
                                        <i class="fas fa-eye mb-1 d-block" style="font-size: 1.2rem;"></i> View
                                    </a>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            @empty
                <div class="col-12">
                    <div class="alert alert-light text-center border shadow-sm py-5">
                        <h5>No projects found.</h5>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>


{{-- SCRIPTS --}}
<script>
    let currentMainStatus = 'all';
    let selectedCodes = [];
    document.addEventListener('DOMContentLoaded', function() { populateCodeFilter(); });
    // ... (Filter functions same as before) ...
    function populateCodeFilter() { /* Same */ }
    function filterCodeList(input) { /* Same */ }
    function updateSelectedCodes() { /* Same */ }
    function clearCodeFilter() { /* Same */ }
    function setMainFilter(status, btn) {
        currentMainStatus = status;
        document.querySelectorAll('.filter-btn-main').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        applyFilters();
    }
    function applyFilters() {
        const stageFilter = document.getElementById('stageFilter').value.toLowerCase();
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const cards = document.querySelectorAll('.project-card-wrapper');
        let visibleCount = 0;
        cards.forEach(wrapper => {
            const card = wrapper.querySelector('.project-card');
            const code = card.dataset.code;
            const status = card.dataset.status.toLowerCase();
            const date = card.dataset.date;
            let show = true;
            if (currentMainStatus !== 'all') {
                if (currentMainStatus === 'draft' && status !== 'draft') show = false;
                else if (currentMainStatus === 'open' && (status === 'closed' || status === 'draft')) show = false;
                else if (currentMainStatus === 'closed' && status !== 'closed') show = false;
            }
            if (selectedCodes.length > 0 && !selectedCodes.includes(code)) show = false;
            if (dateFrom && date < dateFrom) show = false;
            if (dateTo && date > dateTo) show = false;
            wrapper.style.display = show ? 'block' : 'none';
            if(show) visibleCount++;
        });
        document.getElementById('page-heading').innerHTML = `<i class="fas fa-folder-open mr-1"></i> All Projects (${visibleCount})`;
    }
</script>

<style>
    .project-card { border: none; border-radius: 12px; transition: transform 0.2s; border-left: 5px solid #ccc; background: #fff; }
    .project-card[data-status="open"] { border-left-color: #007bff; }
    .project-card[data-status="closed"] { border-left-color: #28a745; }
    .project-card[data-status="draft"] { border-left-color: #ffc107; }
    .project-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; }

    /* MINI VERTICAL STEPS */
    .mini-steps-container { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
    .mini-step { display: flex; align-items: center; font-size: 0.7rem; color: #ccc; }
    .mini-step .step-dot { width: 8px; height: 8px; border-radius: 50%; background: #e9ecef; margin-right: 4px; }
    .mini-step.done { color: #28a745; font-weight: 600; }
    .mini-step.done .step-dot { background: #28a745; }

    /* CSS PIE CHART */
    .pie-wrapper {
        width: 55px; height: 55px; border-radius: 50%; margin: 0 auto;
        position: relative; display: flex; align-items: center; justify-content: center;
    }
    .pie-inner {
        width: 45px; height: 45px; background: #fff; border-radius: 50%;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .pie-label { font-size: 0.55rem; line-height: 1; margin-bottom: 1px; }
    .pie-val { font-size: 0.7rem; font-weight: bold; line-height: 1; color: #333; }
</style>
@endsection