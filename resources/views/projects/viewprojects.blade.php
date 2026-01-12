@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">

    {{-- HEADER --}}
    <div class="content-header">
        <div class="container-fluid">

            {{-- PAGE TITLE --}}
            <div class="row mb-3">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h1 id="page-heading" class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-folder-open mr-1"></i> All Projects
                    </h1>

                    <a href="{{ route('addnewproject') }}"
                       class="btn btn-primary shadow-sm px-4"
                       style="border-radius: 20px;">
                        <i class="fas fa-plus-circle mr-1"></i> New Project
                    </a>
                </div>
            </div>

            {{-- FILTER CARD --}}
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-body py-3">

                    <div class="row align-items-end">

                        {{-- PROJECT CODE SEARCH --}}
                        <div class="col-md-3 mb-2">
                            <label class="small text-muted">Project Code</label>
                            <div class="dropdown" id="searchDropdown">
                                <button class="btn btn-light border form-control text-left d-flex justify-content-between align-items-center"
                                        type="button" data-toggle="dropdown">
                                    <span id="selectedCount" class="text-muted">Select Codes</span>
                                    <i class="fas fa-chevron-down small text-primary"></i>
                                </button>

                                <div class="dropdown-menu w-100 p-2 shadow-sm"
                                     style="max-height: 280px; overflow-y: auto;">
                                    <input type="text"
                                           class="form-control form-control-sm mb-2"
                                           placeholder="Search code..."
                                           onkeyup="filterCodeList(this)">

                                    <div id="codeListContainer"></div>

                                    <div class="dropdown-divider"></div>
                                    <button class="btn btn-xs btn-link text-danger p-0"
                                            onclick="clearCodeFilter()">
                                            Clear Selection
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- FROM DATE --}}
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted">From Date</label>
                            <input type="date" id="dateFrom" class="form-control form-control-sm" onchange="applyFilters()">
                        </div>

                        {{-- TO DATE --}}
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted">To Date</label>
                            <input type="date" id="dateTo" class="form-control form-control-sm" onchange="applyFilters()">
                        </div>

                        {{-- PROJECT STAGE --}}
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted">Project Stage</label>
                            <select id="stageFilter" class="form-control form-control-sm" onchange="applyFilters()">
                                <option value="all">All Stages</option>
                                <option value="Draft">Drafting</option>
                                <option value="Approved">Approved</option>
                                <option value="Started">Started</option>
                                <option value="Work In Progress">Work In Progress</option>
                                <option value="EDC">EDC</option>
                            </select>
                        </div>

                        {{-- MAIN STATUS --}}
                        <div class="col-md-3 mb-2">
                            <label class="small text-muted">Main Status</label>
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

                    // --- Determine Stage Name ---
                    $currentStage = 'Approved'; 
                    if($status == 'draft') $currentStage = 'Draft';
                    elseif($project->prj_startdt) $currentStage = 'Started';
                    elseif($status == 'open') $currentStage = 'Work In Progress';
                    elseif($status == 'closed') $currentStage = 'EDC'; 

                    // --- PROGRESS LOGIC (UPDATED) ---
                    $level = 0;
                    
                    // Level 1: Approved
                    if ($status !== 'draft' && $project->prj_aprvdt) {
                        $level = 1;
                    }
                    // Level 2: Started
                    if ($project->prj_startdt) {
                        $level = 2;
                    }
                    // Level 3: Open / In Progress
                    if ($status === 'open') {
                        $level = 3;
                    }
                    
                    // Level 4: Closed (Highest Priority)
                    // Logic Update: Check if Status is Closed OR if Date is Available
                    if ($status === 'closed' || $status === 'completed' || !empty($project->prj_estenddt)) {
                        $level = 4;
                    }
                @endphp

                <div class="col-12 project-card-wrapper">
                    <div class="card project-card shadow-sm mb-2"
                         data-code="{{ $project->prj_code }}"
                         data-status="{{ $status }}"
                         data-stage="{{ strtolower($currentStage) }}"
                         data-date="{{ \Carbon\Carbon::parse($project->prj_rcptdt)->format('Y-m-d') }}">

                        <div class="card-body p-3">

                            {{-- PROJECT HEADER --}}
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 text-primary font-weight-bold" style="font-size: 1.1rem;">{{ $project->prj_code }}</h5>
                                    <div class="text-dark" style="font-size: 0.9rem;">{{ $project->prj_title }}</div>

                                    @php
                                        $badge = match($status){
                                            'open'   => 'badge-primary',
                                            'closed' => 'badge-success',
                                            'draft'  => 'badge-warning',
                                            default  => 'badge-secondary'
                                        };
                                    @endphp

                                    <span class="badge {{ $badge }} px-2 py-1 mt-1" style="border-radius: 4px; font-size: 0.7rem;">
                                        {{ strtoupper($project->prj_status) }}
                                    </span>
                                </div>

                                {{-- ACTION BUTTON LOGIC --}}
                                <div>
                                @if($status == 'draft')
                                    <a href="{{ route('addnewproject', ['draft_id' => $project->prj_id]) }}"
                                       class="btn btn-warning btn-xs px-3 font-weight-bold shadow-sm" style="border-radius: 20px;">
                                        <i class="fas fa-pen mr-1"></i> Edit
                                    </a>
                                @else
                                    <a href="{{ route('projects.show', $project->prj_id) }}"
                                       class="btn btn-outline-primary btn-xs px-3 shadow-sm" style="border-radius: 20px;">
                                        View <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                @endif
                                </div>
                            </div>

                            {{-- TIMELINE (VISUALS) --}}
                            <div class="timeline-wrapper mt-3">
                                
                                {{-- 1. Approval --}}
                                <div class="timeline-item">
                                    {{-- Green if Level 4, else Blue if done, else Grey --}}
                                    <div class="progress-segment {{ $level == 4 ? 'bg-success' : ($level >= 1 ? 'bg-primary' : 'bg-secondary') }}"></div>
                                    <div class="meta-text">Approval</div>
                                    <div class="meta-date">
                                        {{ $project->prj_aprvdt ? \Carbon\Carbon::parse($project->prj_aprvdt)->format('d M y') : '--' }}
                                    </div>
                                </div>

                                {{-- 2. Start --}}
                                <div class="timeline-item">
                                    <div class="progress-segment {{ $level == 4 ? 'bg-success' : ($level >= 2 ? 'bg-primary' : 'bg-secondary') }}"></div>
                                    <div class="meta-text">Start</div>
                                    <div class="meta-date">
                                        {{ $project->prj_startdt ? \Carbon\Carbon::parse($project->prj_startdt)->format('d M y') : '--' }}
                                    </div>
                                </div>

                                {{-- 3. In Progress --}}
                                <div class="timeline-item">
                                    {{-- Green if Level 3 OR 4 --}}
                                    <div class="progress-segment {{ $level >= 3 ? 'bg-success' : 'bg-secondary' }}"></div>
                                    <div class="meta-text">In Progress</div>
                                </div>

                                {{-- 4. Closed --}}
                                <div class="timeline-item">
                                    {{-- Green ONLY if Level 4 (Closed/Date Available) --}}
                                    <div class="progress-segment {{ $level == 4 ? 'bg-success' : 'bg-secondary' }}"></div>
                                    <div class="meta-text">Closed</div>
                                    <div class="meta-date">
                                        {{ $project->prj_estenddt ? \Carbon\Carbon::parse($project->prj_estenddt)->format('d M y') : '--' }}
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>

                @empty
                    <div class="col-12">
                        <div class="alert alert-light text-center border shadow-sm py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5>No projects found.</h5>
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</div>

{{-- JAVASCRIPT LOGIC --}}
<script>
    let currentMainStatus = 'all';
    let selectedCodes = [];

    document.addEventListener('DOMContentLoaded', function() {
        populateCodeFilter();
    });

    // 1. Populate Filter
    function populateCodeFilter() {
        const cards = document.querySelectorAll('.project-card');
        const uniqueCodes = new Set();
        cards.forEach(card => uniqueCodes.add(card.dataset.code));

        const container = document.getElementById('codeListContainer');
        container.innerHTML = '';

        uniqueCodes.forEach(code => {
            let div = document.createElement('div');
            div.className = 'custom-control custom-checkbox mb-1';
            div.innerHTML = `
                <input type="checkbox" class="custom-control-input code-checkbox" id="chk_${code}" value="${code}" onchange="updateSelectedCodes()">
                <label class="custom-control-label" for="chk_${code}">${code}</label>
            `;
            container.appendChild(div);
        });
    }

    // 2. Filter Search
    function filterCodeList(input) {
        const filter = input.value.toUpperCase();
        const divs = document.getElementById('codeListContainer').getElementsByTagName('div');
        for (let i = 0; i < divs.length; i++) {
            let label = divs[i].getElementsByTagName("label")[0];
            if (label.innerHTML.toUpperCase().indexOf(filter) > -1) {
                divs[i].style.display = "";
            } else {
                divs[i].style.display = "none";
            }
        }
    }

    // 3. Update Selected
    function updateSelectedCodes() {
        const checkboxes = document.querySelectorAll('.code-checkbox:checked');
        selectedCodes = Array.from(checkboxes).map(cb => cb.value);
        
        const countSpan = document.getElementById('selectedCount');
        countSpan.innerText = selectedCodes.length > 0 ? `${selectedCodes.length} Selected` : 'Select Codes';
        countSpan.style.color = selectedCodes.length > 0 ? '#007BFF' : '#333';
        
        applyFilters();
    }

    function clearCodeFilter() {
        document.querySelectorAll('.code-checkbox').forEach(cb => cb.checked = false);
        updateSelectedCodes();
    }

    // 4. Main Status Logic
    function setMainFilter(status, btn) {
        currentMainStatus = status;
        document.querySelectorAll('.filter-btn-main').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        applyFilters();
    }

    // 5. Apply Filters
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
            const stage = card.dataset.stage.toLowerCase();   
            const date = card.dataset.date; 

            let show = true;

            if (currentMainStatus !== 'all') {
                if (currentMainStatus === 'draft') {
                    if (status !== 'draft') show = false;
                }
                else if (currentMainStatus === 'open') {
                    if (status === 'closed' || status === 'completed' || status === 'draft') show = false;
                }
                else if (currentMainStatus === 'closed') {
                    if (status !== 'closed' && status !== 'completed') show = false;
                }
            }

            if (selectedCodes.length > 0 && !selectedCodes.includes(code)) {
                show = false;
            }

            if (stageFilter !== 'all' && stage !== stageFilter) {
                show = false;
            }

            if (dateFrom && date < dateFrom) show = false;
            if (dateTo && date > dateTo) show = false;

            wrapper.style.display = show ? 'block' : 'none';
            if(show) visibleCount++;
        });
        
        document.getElementById('page-heading').innerHTML = `<i class="fas fa-folder-open mr-1"></i> All Projects (${visibleCount})`;
    }
</script>

<style>
    .project-card {
        border: none;
        border-radius: 8px; 
        transition: all 0.2s ease;
        border-left: 4px solid transparent; 
        min-height: 100px; 
    }
    .project-card[data-status="open"] { border-left-color: #007bff; }
    .project-card[data-status="closed"] { border-left-color: #28a745; }
    .project-card[data-status="draft"] { border-left-color: #ffc107; }

    .project-card:hover {
        transform: translateY(-2px); 
        box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
    }

    /* Timeline Styles - COMPACT */
    .timeline-wrapper { display: flex; justify-content: space-between; position: relative; }
    .timeline-item { text-align: center; flex: 1; position: relative; z-index: 2; }
    
    .progress-segment { 
        height: 5px; 
        border-radius: 3px; 
        margin-bottom: 4px; 
        background-color: #e9ecef; 
        transition: background-color 0.3s; 
    }
    
    .progress-segment.bg-primary { background-color: #007bff !important; }
    .progress-segment.bg-success { background-color: #28a745 !important; }
    
    .meta-text { font-weight: 700; font-size: 0.75rem; color: #495057; }
    .meta-date { font-size: 0.65rem; color: #adb5bd; margin-top: 2px; }

    /* Custom Scrollbar for Dropdown */
    .dropdown-menu::-webkit-scrollbar { width: 6px; }
    .dropdown-menu::-webkit-scrollbar-track { background: #f1f1f1; }
    .dropdown-menu::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
    .dropdown-menu::-webkit-scrollbar-thumb:hover { background: #007BFF; }

    /* Button Group Active State Override */
    .btn-group .btn.active {
        background-color: #007BFF !important;
        color: #fff !important;
        border-color: #007BFF !important;
    }
    .btn-group .btn-outline-warning.active {
        background-color: #ffc107 !important;
        color: #212529 !important;
        border-color: #ffc107 !important;
    }
</style>
@endsection