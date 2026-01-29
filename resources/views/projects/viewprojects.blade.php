@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">

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
                             <input type="text" id="codeSearch" class="form-control form-control-sm" placeholder="Search Project Code..." onkeyup="applyFilters()">
                        </div>
                         <div class="col-md-2 mb-2">
                            <label class="small text-muted">From Date</label>
                            <input type="date" id="dateFrom" class="form-control form-control-sm" onchange="applyFilters()">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted">To Date</label>
                            <input type="date" id="dateTo" class="form-control form-control-sm" onchange="applyFilters()">
                        </div>
                        <div class="col-md-5 mb-2">
                             <div class="btn-group btn-block shadow-sm">
                                <button class="btn btn-sm btn-outline-primary active filter-btn-main" onclick="setMainFilter('all', this)">All</button>
                                <button class="btn btn-sm btn-outline-primary filter-btn-main" onclick="setMainFilter('open', this)">Open</button>
                                <button class="btn btn-sm btn-outline-danger filter-btn-main" onclick="setMainFilter('returned', this)">Returned</button>
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
                    
                    // --- DOCUMENT STATUS CHECK ---
                    $doc = $project->document; 
                    $docStatus = $doc ? $doc->status : 'Not Started';
                    
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
                @endphp

                <div class="col-12 project-card-wrapper">
                    {{-- FIXED BORDER COLOR: BLUE (#007bff) --}}
                    <div class="card project-card shadow-sm mb-3"
                         data-code="{{ $project->prj_code }}"
                         data-status="{{ $status }}"
                         data-docstatus="{{ $docStatus }}" 
                         data-date="{{ \Carbon\Carbon::parse($project->prj_rcptdt)->format('Y-m-d') }}"
                         style="border-left: 5px solid #007bff;">
                        
                        <div class="card-body p-0">
                            <div class="row no-gutters align-items-center" style="min-height: 120px;">
                                
                                {{-- LEFT: INFO --}}
                                <div class="col-md-4 p-3 border-right">
                                    <div class="mb-2">
                                        <span class="badge badge-light border">{{ $project->prj_code }}</span>
                                        <span class="text-muted small ml-2">{{ $project->unit->unt_name ?? 'N/A' }}</span>
                                    </div>
                                    <h6 class="text-dark font-weight-bold text-truncate" title="{{ $project->prj_title }}">{{ $project->prj_title }}</h6>
                                    <div class="small text-muted mt-2">
                                        <i class="fas fa-coins mr-1"></i> Cost: {{ number_format($project->prj_propcost / 1000000, 2) }} M
                                    </div>
                                </div>

                                {{-- CENTER: PROGRESS --}}
                                <div class="col-md-4 p-3 border-right">
                                    <div class="row h-100 align-items-center">
                                        <div class="col-md-6 border-right text-center">
                                            <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Time Elapsed</small>
                                            <div style="position:relative; display:inline-block;">
                                                <input type="text" class="knob" value="{{ round($timePercentage) }}" 
                                                    data-width="50" data-height="50" data-fgColor="#17a2b8"
                                                    data-readonly="true" data-thickness=".2" data-skin="tron">
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-center">
                                            <small class="text-muted text-uppercase font-weight-bold d-block mb-1">Fund Utilized</small>
                                             <div style="position:relative; display:inline-block;">
                                                <input type="text" class="knob" value="{{ round($spentPercentage) }}" 
                                                    data-width="50" data-height="50" data-fgColor="#28a745"
                                                    data-readonly="true" data-thickness=".2" data-skin="tron">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- RIGHT: MPR STATUS & ACTIONS --}}
                                <div class="col-md-4 p-3 text-center bg-light-blue" style="border-radius: 0 12px 12px 0;">
                                    
                                    {{-- 1. MPR STATUS BADGE --}}
                                    <div class="mb-3">
                                        <h6 class="text-primary font-weight-bold mb-1" style="font-size: 0.85rem;">MPR Status</h6>
                                        
                                        @if($docStatus == 'Returned')
                                            <span class="badge badge-danger p-2 px-3 shadow-sm pulse-button">
                                                <i class="fas fa-exclamation-circle mr-1"></i> RETURNED
                                            </span>
                                        @elseif($docStatus == 'Pending Review')
                                            <span class="badge badge-warning p-2 px-3 shadow-sm">
                                                <i class="fas fa-clock mr-1"></i> SENT FOR REVIEW
                                            </span>
                                        @elseif($docStatus == 'Approved' || $docStatus == 'Forwarded to MD')
                                            <span class="badge badge-success p-2 px-3 shadow-sm">
                                                <i class="fas fa-check-circle mr-1"></i> FINALIZED
                                            </span>
                                        @elseif($status == 'draft')
                                            <span class="badge badge-secondary p-2 px-3">DRAFT PROJECT</span>
                                        @else
                                            <span class="badge badge-light border p-2 px-3">NOT STARTED</span>
                                        @endif
                                    </div>

                                    {{-- 2. ACTION BUTTONS (LOGIC UPDATED) --}}
                                    
                                    @if($docStatus == 'Returned')
                                        {{-- CASE: RETURNED -> Button points to MPR Page (To Fix) --}}
                                        <a href="{{ route('mpr.view', $project->prj_id) }}" class="btn btn-danger btn-sm shadow btn-block font-weight-bold rounded-pill">
                                            <i class="fas fa-tools mr-1"></i> Fix & Resubmit
                                        </a>
                                    @elseif($status == 'draft')
                                        {{-- CASE: DRAFT -> Edit Project --}}
                                        <a href="{{ route('addnewproject', ['draft_id' => $project->prj_id]) }}" class="btn btn-warning btn-sm shadow-sm btn-block font-weight-bold rounded-pill">
                                            <i class="fas fa-pen mr-1"></i> Edit Project
                                        </a>
                                    @else
                                        {{-- CASE: NORMAL/APPROVED/SENT -> View Project Details --}}
                                        <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-primary btn-sm shadow-sm btn-block font-weight-bold rounded-pill">
                                            <i class="fas fa-eye mr-1"></i> View Project Details
                                        </a>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                    <div class="col-12"><div class="alert alert-light text-center border shadow-sm py-5"><h5>No projects found.</h5></div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-Knob/1.2.13/jquery.knob.min.js"></script>
<script>
    $(function() {
        $(".knob").knob({ 'format' : function (value) { return value + '%'; } });
    });

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
        const cards = document.querySelectorAll('.project-card-wrapper');
        let visibleCount = 0;

        cards.forEach(wrapper => {
            const card = wrapper.querySelector('.project-card');
            const code = card.dataset.code.toLowerCase();
            const status = card.dataset.status.toLowerCase();
            const docStatus = card.dataset.docstatus;
            const date = card.dataset.date;
            
            let show = true;

            // STATUS FILTERS
            if (currentMainStatus !== 'all') {
                if (currentMainStatus === 'returned' && docStatus !== 'Returned') show = false;
                else if (currentMainStatus === 'draft' && status !== 'draft') show = false;
                else if (currentMainStatus === 'closed' && status !== 'closed') show = false;
                else if (currentMainStatus === 'open' && (status === 'closed' || status === 'draft')) show = false;
            }

            // CODE SEARCH
            if (codeSearch && !code.includes(codeSearch)) show = false;

            // DATE
            if (dateFrom && date < dateFrom) show = false;
            if (dateTo && date > dateTo) show = false;

            wrapper.style.display = show ? 'block' : 'none';
            if(show) visibleCount++;
        });

        document.getElementById('page-heading').innerHTML = `<i class="fas fa-folder-open mr-1"></i> All Projects (${visibleCount})`;
    }
</script>

<style>
    .project-card { border: none; border-radius: 12px; transition: transform 0.2s; background: #fff; }
    .project-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important; }
    .bg-light-blue { background-color: #f8fbff; border-left: 1px solid #f1f1f1; }
    
    /* Animation for Returned Badge/Button */
    @keyframes pulse-red {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .pulse-button { animation: pulse-red 2s infinite; }
</style>
@endsection