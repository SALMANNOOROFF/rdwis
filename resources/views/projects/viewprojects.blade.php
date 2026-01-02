@extends('welcome')

@section('content')
<div class="content-wrapper">

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
                        <input type="date"
                               id="dateFrom"
                               class="form-control form-control-sm"
                               onchange="applyFilters()">
                    </div>

                    {{-- TO DATE --}}
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted">To Date</label>
                        <input type="date"
                               id="dateTo"
                               class="form-control form-control-sm"
                               onchange="applyFilters()">
                    </div>

                    {{-- PROJECT STAGE --}}
                    <div class="col-md-2 mb-2">
                        <label class="small text-muted">Project Stage</label>
                        <select id="stageFilter"
                                class="form-control form-control-sm"
                                onchange="applyFilters()">
                            <option value="all">All Stages</option>
                            <option value="Approved">Approved</option>
                            <option value="Started">Started</option>
                            <option value="Work In Progress">Work In Progress</option>
                            <option value="Warranty">Warranty</option>
                            <option value="EDC">EDC</option>
                        </select>
                    </div>

                    {{-- MAIN STATUS --}}
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted">Main Status</label>
                        <div class="btn-group btn-block">
                            <button class="btn btn-sm btn-outline-primary active filter-btn-main"
                                    onclick="setMainFilter('all', this)">
                                All
                            </button>
                            <button class="btn btn-sm btn-outline-primary filter-btn-main"
                                    onclick="setMainFilter('open', this)">
                                Open
                            </button>
                            <button class="btn btn-sm btn-outline-primary filter-btn-main"
                                    onclick="setMainFilter('closed', this)">
                                Closed
                            </button>
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
                
                {{-- 
                    NOTE: Adding Data Attributes for JS Filtering 
                    data-code: For search
                    data-status: For Open/Closed buttons
                    data-date: For Date Range (Using Start Date here, logic can be adjusted)
                    data-stage: Logic to determine stage based on your timeline logic needs to be dynamic.
                    Currently assuming a hardcoded logic or passing a variable.
                --}}
                
                @php
                    $status = Str::lower($project->prj_status);
                    // Determine Stage for filtering (This logic attempts to guess the stage based on status)
                    $currentStage = 'Approved'; 
                    if($project->prj_startdt) $currentStage = 'Started';
                    if($status == 'open') $currentStage = 'Work In Progress';
                    if($status == 'closed') $currentStage = 'EDC'; 
                    // You can refine $currentStage logic based on your DB columns
                @endphp

                <div class="col-12 project-card-wrapper">
                    <div class="card project-card shadow-sm mb-4"
                         data-code="{{ $project->prj_code }}"
                         data-status="{{ $status }}"
                         data-stage="{{ $currentStage }}"
                         data-date="{{ \Carbon\Carbon::parse($project->prj_startdt)->format('Y-m-d') }}">

                        <div class="card-body">

                            {{-- PROJECT HEADER --}}
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1 text-primary">{{ $project->prj_code }}</h3>
                                    <div class="text-muted font-weight-bold">{{ $project->prj_title }}</div>

                                    @php
                                        $badge = match($status){
                                            'open'   => 'badge-primary',
                                            'closed' => 'badge-success',
                                            'hold'   => 'badge-warning',
                                            default  => 'badge-secondary'
                                        };
                                    @endphp

                                    <span class="badge {{ $badge }} px-3 py-2 mt-2" style="border-radius: 4px;">
                                        {{ strtoupper($project->prj_status) }}
                                    </span>
                                </div>

                                <a href="{{ route('projects.show',$project->prj_id) }}"
                                   class="btn btn-outline-primary btn-sm px-4" style="border-radius: 20px;">
                                    View Project <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>

                            {{-- TIMELINE --}}
                            <div class="timeline-wrapper mt-4">
                                <div class="timeline-item">
                                    <div class="progress-segment bg-primary"></div>
                                    <div class="meta-text">Approval</div>
                                    <div class="meta-date">{{ optional($project->created_at)->format('d M Y') }}</div>
                                </div>

                                <div class="timeline-item">
                                    <div class="progress-segment bg-primary"></div>
                                    <div class="meta-text">Start</div>
                                    <div class="meta-date">{{ \Carbon\Carbon::parse($project->prj_startdt)->format('d M Y') }}</div>
                                </div>

                                <div class="timeline-item">
                                    <div class="progress-segment {{ $status=='open' ? 'bg-success':'bg-secondary' }}"></div>
                                    <div class="meta-text">Work In Progress</div>
                                </div>

                                <div class="timeline-item">
                                    <div class="progress-segment bg-secondary"></div>
                                    <div class="meta-text">Warranty</div>
                                </div>

                                <div class="timeline-item">
                                    <div class="progress-segment bg-secondary"></div>
                                    <div class="meta-text">EDC</div>
                                    <div class="meta-date">{{ $project->prj_enddt ? \Carbon\Carbon::parse($project->prj_enddt)->format('d M Y') : 'TBD' }}</div>
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
    // State Management
    let currentMainStatus = 'all';
    let selectedCodes = [];

    document.addEventListener('DOMContentLoaded', function() {
        populateCodeFilter();
    });

    // 1. Populate the Multi-Select Filter Dynamically
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

    // 2. Filter the Code List (Search inside dropdown)
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

    // 3. Update Selected Codes Array
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

    // 4. Handle Main Status Buttons (All/Open/Closed)
    function setMainFilter(status, btn) {
        currentMainStatus = status;
        
        // Update UI styling
        document.querySelectorAll('.filter-btn-main').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        applyFilters();
    }

    // 5. MASTER FILTER FUNCTION
    function applyFilters() {
        const stageFilter = document.getElementById('stageFilter').value.toLowerCase();
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;

        const cards = document.querySelectorAll('.project-card-wrapper');
        let visibleCount = 0;

        cards.forEach(wrapper => {
            const card = wrapper.querySelector('.project-card');
            
            // Get Data Attributes
            const code = card.dataset.code;
            const status = card.dataset.status.toLowerCase(); // open/closed
            const stage = card.dataset.stage.toLowerCase();   // approved/started/etc
            const date = card.dataset.date; // YYYY-MM-DD

            // Logic Checks
            let show = true;

            // A. Check Main Status (All/Open/Closed)
            if (currentMainStatus !== 'all') {
                if (currentMainStatus === 'open' && (status === 'completed' || status === 'closed')) show = false;
                if (currentMainStatus === 'closed' && (status !== 'completed' && status !== 'closed')) show = false;
            }

            // B. Check Code (Multi-Select)
            if (selectedCodes.length > 0 && !selectedCodes.includes(code)) {
                show = false;
            }

            // C. Check Stage Dropdown
            if (stageFilter !== 'all' && stage !== stageFilter) {
                show = false;
            }

            // D. Check Date Range
            if (dateFrom && date < dateFrom) show = false;
            if (dateTo && date > dateTo) show = false;

            // Toggle Visibility
            wrapper.style.display = show ? 'block' : 'none';
            if(show) visibleCount++;
        });
        
        // Update Heading Count (Optional)
        document.getElementById('page-heading').innerText = `Projects (${visibleCount})`;
    }
</script>

{{-- STYLES --}}
<style>
    /* Styling adjustments for the new layout */
    .project-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        border-left: 5px solid transparent; 
    }
    .project-card[data-status="open"] { border-left-color: #007bff; }
    .project-card[data-status="closed"] { border-left-color: #28a745; }

    .project-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }

    /* Timeline Styles */
    .timeline-wrapper { display: flex; justify-content: space-between; position: relative; }
    .timeline-item { text-align: center; flex: 1; position: relative; z-index: 2; }
    .progress-segment { height: 8px; border-radius: 4px; margin-bottom: 8px; background-color: #e9ecef; transition: background-color 0.3s; }
    .progress-segment.bg-primary { background-color: #007bff !important; }
    .progress-segment.bg-success { background-color: #28a745 !important; }
    .meta-text { font-weight: 700; font-size: 0.85rem; color: #495057; }
    .meta-date { font-size: 0.75rem; color: #adb5bd; margin-top: 4px; }

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
</style>
@endsection