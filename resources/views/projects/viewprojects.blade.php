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

                    if ($status !== 'draft' && $endDate && $today->greaterThan($endDate)) {
                        $status = 'closed'; 
                    }

                    $timePercentage = 0;
                    if ($startDate && $endDate) {
                        $totalDays = $startDate->diffInDays($endDate);
                        $daysPassed = $startDate->diffInDays($today);
                        if ($totalDays > 0 && $today->greaterThan($startDate)) {
                            $timePercentage = ($daysPassed / $totalDays) * 100;
                        }
                        if ($timePercentage > 100) $timePercentage = 100;
                        if ($today->lessThan($startDate)) $timePercentage = 0;
                    } elseif ($status == 'closed') {
                        $timePercentage = 100;
                    }

                    $budget = $project->prj_propcost > 0 ? $project->prj_propcost : 0;
                    $spent = 0; 
                    if($status == 'closed') $spent = $budget * 0.95; 
                    elseif($status == 'open') $spent = $budget * ($timePercentage / 100);
                    
                    $spentPercentage = ($budget > 0) ? ($spent / $budget) * 100 : 0;
                    if($spentPercentage > 100) $spentPercentage = 100;

                    // Knob Color Logic
                    $knobColor = '#dc3545'; // Red (Default Spent)
                    if($status == 'closed') $knobColor = '#28a745'; // Green if closed
                    if($status == 'draft') $knobColor = '#ffc107'; // Yellow if draft

                    // Mockup MPR Data
$totalMonths = 12; 
if ($startDate && $endDate) {
    $totalMonths = (int) round($startDate->diffInMonths($endDate));
    if ($totalMonths < 1) {
        $totalMonths = 1;
    }
}


$mprSubmitted = (int) round(($timePercentage / 100) * $totalMonths);
$mprRemaining = (int) max(0, $totalMonths - $mprSubmitted);


                @endphp

                <div class="col-12 project-card-wrapper">
                    <div class="card project-card shadow-sm mb-3"
                         data-code="{{ $project->prj_code }}"
                         data-status="{{ $status }}"
                         data-date="{{ \Carbon\Carbon::parse($project->prj_rcptdt)->format('Y-m-d') }}">

                        <div class="card-body p-0">
                            <div class="row no-gutters align-items-center" style="min-height: 120px;">
                                
                                {{-- LEFT --}}
                                <div class="col-md-3 p-3 border-right">
                                    <div class="mb-2">
                                        @if($status == 'draft') <span class="badge badge-warning px-2">DRAFT</span>
                                        @elseif($status == 'closed') <span class="badge badge-success px-2">COMPLETED</span>
                                        @else <span class="badge badge-primary px-2">IN PROGRESS</span> @endif
                                    </div>
                                    <div class="mb-1">
                                        <span class="text-muted small font-weight-bold">Code: </span>
                                        <span class="badge badge-light border text-dark">{{ $project->prj_code }}</span>
                                    </div>
                                    <h6 class="text-dark font-weight-bold text-truncate" title="{{ $project->prj_title }}">{{ $project->prj_title }}</h6>
                                </div>

                                {{-- CENTER: TIMELINE & JQUERY KNOB --}}
                                <div class="col-md-6 p-3 border-right">
                                    <div class="row h-100 align-items-center">
                                        
                                        {{-- Timeline --}}
                                        <div class="col-md-7 border-right">
                                            <small class="text-muted text-uppercase font-weight-bold mb-2 d-block">Timeline</small>
                                            <div class="d-flex justify-content-between small text-muted mb-1 px-1">
                                                <span>{{ $startDate ? $startDate->format('d M y') : '--' }}</span>
                                                <span>{{ $endDate ? $endDate->format('d M y') : '--' }}</span>
                                            </div>
                                            <div class="progress" style="height: 10px; border-radius: 5px; background: #e9ecef;">
                                                <div class="progress-bar {{ $status == 'closed' ? 'bg-success' : 'bg-info' }}" role="progressbar" style="width: {{ $timePercentage }}%"></div>
                                            </div>
                                            <div class="text-center mt-1"><small class="text-dark font-weight-bold">{{ round($timePercentage) }}% Elapsed</small></div>
                                        </div>

                                        {{-- jQuery Knob --}}
                                        <div class="col-md-5 text-center">
                                            <div style="position:relative; display:inline-block;">
                                                <input type="text" class="knob" value="{{ round($spentPercentage) }}" 
                                                    data-width="70" 
                                                    data-height="70" 
                                                    data-fgColor="{{ $knobColor }}"
                                                    data-readonly="true"
                                                    data-thickness=".2"
                                                    data-skin="tron"
                                                >
                                            </div>
                                            <div class="mt-1" style="font-size: 0.65rem;">
                                                <span class="text-muted d-block">Total: {{ number_format($budget/1000000, 1) }}M</span>
                                                <span class="text-danger font-weight-bold d-block">Spent: {{ number_format($spent/1000000, 1) }}M</span>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                {{-- RIGHT --}}
                                <div class="col-md-3 p-3 text-center bg-light-blue" style="border-radius: 0 12px 12px 0;">
                                    <h6 class="text-primary font-weight-bold mb-2" style="font-size: 0.85rem;"><i class="fas fa-file-invoice mr-1"></i> MPR Status</h6>
                                    <div class="d-flex justify-content-center gap-2 mb-3">
                                        <div class="badge-group text-center mx-1"><span class="badge badge-info p-2 d-block mb-1">{{ $totalMonths }}</span><small class="text-muted" style="font-size: 0.6rem;">TOTAL</small></div>
                                        <div class="badge-group text-center mx-1"><span class="badge badge-success p-2 d-block mb-1">{{ $mprSubmitted }}</span><small class="text-muted" style="font-size: 0.6rem;">SENT</small></div>
                                        <div class="badge-group text-center mx-1"><span class="badge badge-danger p-2 d-block mb-1">{{ $mprRemaining }}</span><small class="text-muted" style="font-size: 0.6rem;">LEFT</small></div>
                                    </div>
                                    @if($status == 'draft')
                                        <a href="{{ route('addnewproject', ['draft_id' => $project->prj_id]) }}" class="btn btn-warning btn-sm shadow-sm btn-block font-weight-bold rounded-pill"><i class="fas fa-pen mr-1"></i> Edit Project</a>
                                    @else
                                        <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-primary btn-sm shadow-sm btn-block font-weight-bold rounded-pill"><i class="fas fa-eye mr-1"></i> View Details</a>
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

{{-- SCRIPTS (Filter Logic + jQuery Knob) --}}
{{-- 1. jQuery Knob CDN --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-Knob/1.2.13/jquery.knob.min.js"></script>

<script>
    // Initialize Knob
    $(function() {
        $(".knob").knob({
            'format' : function (value) {
                return value + '%';
            }
        });
    });

    // Filter Logic
    let currentMainStatus = 'all';
    let selectedCodes = [];
    document.addEventListener('DOMContentLoaded', function() { populateCodeFilter(); });
    
    function populateCodeFilter() {
        const cards = document.querySelectorAll('.project-card');
        const uniqueCodes = new Set();
        cards.forEach(card => uniqueCodes.add(card.dataset.code));
        const container = document.getElementById('codeListContainer');
        container.innerHTML = '';
        uniqueCodes.forEach(code => {
            let div = document.createElement('div');
            div.className = 'custom-control custom-checkbox mb-1';
            div.innerHTML = `<input type="checkbox" class="custom-control-input code-checkbox" id="chk_${code}" value="${code}" onchange="updateSelectedCodes()"> <label class="custom-control-label" for="chk_${code}">${code}</label>`;
            container.appendChild(div);
        });
    }
    function filterCodeList(input) {
        const filter = input.value.toUpperCase();
        const divs = document.getElementById('codeListContainer').getElementsByTagName('div');
        for (let i = 0; i < divs.length; i++) {
            let label = divs[i].getElementsByTagName("label")[0];
            if (label.innerHTML.toUpperCase().indexOf(filter) > -1) divs[i].style.display = ""; else divs[i].style.display = "none";
        }
    }
    function updateSelectedCodes() {
        const checkboxes = document.querySelectorAll('.code-checkbox:checked');
        selectedCodes = Array.from(checkboxes).map(cb => cb.value);
        const countSpan = document.getElementById('selectedCount');
        countSpan.innerText = selectedCodes.length > 0 ? `${selectedCodes.length} Selected` : 'Select Codes';
        countSpan.style.color = selectedCodes.length > 0 ? '#007BFF' : '#333';
        applyFilters();
    }
    function clearCodeFilter() { document.querySelectorAll('.code-checkbox').forEach(cb => cb.checked = false); updateSelectedCodes(); }
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
    .bg-light-blue { background-color: #f8fbff; }
</style>
@endsection