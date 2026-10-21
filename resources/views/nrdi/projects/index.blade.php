@extends('welcome')

@section('content')
@php
    $closedStatuses = ['closed', 'completed', 'cancelled'];
    $openCount = 0;
    $closedCount = 0;
    foreach($projects as $p) {
        $st = strtolower(trim($p->prj_status ?? ''));
        if (in_array($st, $closedStatuses)) {
            $closedCount++;
        } else {
            $openCount++;
        }
    }
    $totalCount = $projects->count();
@endphp

<div class="content-wrapper pt-2" style="background: var(--rd-bg, #f8fafc); min-height: 100vh;">
    <div class="content-header pb-1">
        <div class="container-fluid px-3 px-md-4">
            
            {{-- TOP HEADER & STATUS TABS --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2" style="gap: 10px;">
                <div>
                    <h1 class="m-0 font-weight-bold text-primary" style="font-size: 1.5rem; font-family:'Rajdhani',sans-serif; letter-spacing:1px;">
                        <i class="fas fa-folder-open mr-1"></i> PROJECTS PORTFOLIO
                    </h1>
                    <small class="text-muted font-weight-bold">
                        Showing <span id="visibleProjectsCount" class="text-dark font-weight-bold">{{ $openCount }}</span> of {{ $totalCount }} projects
                    </small>
                </div>

                {{-- Live Status Tabs --}}
                <div class="btn-group shadow-sm" role="group" id="statusButtonGroup">
                    <button type="button" class="btn btn-sm btn-primary font-weight-bold status-tab-btn active" data-status="open" style="padding: 5px 16px;">
                        <i class="fas fa-folder-open mr-1"></i> Open <span class="badge badge-light text-primary ml-1 font-weight-bold" id="badge-open">({{ $openCount }})</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold status-tab-btn" data-status="closed" style="padding: 5px 16px;">
                        <i class="fas fa-check-circle mr-1"></i> Closed <span class="badge badge-light text-secondary ml-1 font-weight-bold" id="badge-closed">({{ $closedCount }})</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold status-tab-btn" data-status="all" style="padding: 5px 16px;">
                        <i class="fas fa-layer-group mr-1"></i> All <span class="badge badge-light text-secondary ml-1 font-weight-bold" id="badge-all">({{ $totalCount }})</span>
                    </button>
                </div>
            </div>

            {{-- FILTER CARD (Division, Project, Live Search, Reset) --}}
            <div class="card shadow-sm border mb-3" style="border-radius: 10px; background: #ffffff;">
                <div class="card-body p-2 p-md-3">
                    <div class="row align-items-center">
                        
                        {{-- 1. Division Filter --}}
                        <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                            <label class="small text-muted font-weight-bold mb-1 d-block">
                                <i class="fas fa-building text-secondary mr-1"></i> Division:
                            </label>
                            <select class="form-control form-control-sm font-weight-bold" id="divisionFilter" style="border-radius: 6px; height: 36px;">
                                <option value="all">— All Divisions —</option>
                                @foreach($divisions as $d)
                                    <option value="{{ $d->unt_id }}">{{ $d->unt_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 2. Project Dropdown Filter --}}
                        <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                            <label class="small text-muted font-weight-bold mb-1 d-block">
                                <i class="fas fa-filter text-primary mr-1"></i> Project:
                            </label>
                            <select class="form-control form-control-sm font-weight-bold" id="projectSelectFilter" style="border-radius: 6px; height: 36px;">
                                <option value="all">— All Projects —</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->prj_id }}" data-unt-id="{{ $p->prj_unt_id }}">
                                        {{ $p->prj_code }} — {{ \Illuminate\Support\Str::limit($p->prj_title, 32) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. Live Instant Search Bar --}}
                        <div class="col-lg-5 col-md-10 mb-2 mb-lg-0">
                            <label class="small text-muted font-weight-bold mb-1 d-block">
                                <i class="fas fa-search text-success mr-1"></i> Instant Live Search:
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0 text-muted px-2.5">
                                        <i class="fas fa-search text-secondary" style="font-size: 12px;"></i>
                                    </span>
                                </div>
                                <input type="text" id="projectSearchInput" class="form-control border-left-0 font-weight-500" 
                                       placeholder="Type to search code, title, division, budget, status..." 
                                       style="height: 36px; font-size: 13px;" autocomplete="off">
                                <div class="input-group-append" id="clearSearchGroup" style="display: none;">
                                    <button class="btn btn-white border border-left-0 text-muted px-2.5" type="button" id="clearSearchBtn" title="Clear Search">
                                        <i class="fas fa-times-circle text-danger"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- 4. Reset Button --}}
                        <div class="col-lg-1 col-md-2 text-md-right">
                            <label class="d-none d-lg-block mb-1">&nbsp;</label>
                            <button type="button" id="resetFiltersBtn" class="btn btn-outline-secondary btn-sm font-weight-bold w-100" style="height: 36px; border-radius: 6px;" title="Reset all filters">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid px-3 px-md-4">
            <div class="card shadow-sm border mb-4" style="border-radius: 10px; overflow: hidden; background: #ffffff;">
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 75vh; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0 text-nowrap" id="projectsTable">
                            <thead class="bg-light text-muted sticky-top shadow-sm" style="z-index: 1;">
                                <tr>
                                    <th style="width: 75px;" class="text-center p-2 font-weight-bold"><i class="fas fa-eye mr-1"></i> View</th>
                                    <th style="min-width: 280px;" class="p-2">Project Details</th>
                                    <th style="min-width: 180px;" class="p-2">Division</th>
                                    <th style="min-width: 100px;" class="text-center p-2">Team</th>
                                    <th style="min-width: 130px;" class="text-right p-2">Budget</th>
                                </tr>
                            </thead>
                            <tbody id="projectsTableBody">
                            @forelse($projects as $p)
                                @php
                                    $rawStatus = strtolower(trim($p->prj_status ?? ''));
                                    $isClosed = in_array($rawStatus, $closedStatuses);
                                    $categoryStatus = $isClosed ? 'closed' : 'open';
                                    $searchBlob = strtolower($p->prj_code . ' ' . $p->prj_title . ' ' . ($p->unit?->unt_name ?? '') . ' ' . ($p->prj_status ?? '') . ' ' . ($p->prj_propcost ?? '') . ' ' . $p->prj_id);
                                @endphp
                                <tr class="project-row"
                                    data-id="{{ $p->prj_id }}"
                                    data-code="{{ strtolower($p->prj_code) }}"
                                    data-title="{{ strtolower($p->prj_title) }}"
                                    data-unt-id="{{ $p->prj_unt_id }}"
                                    data-status="{{ $categoryStatus }}"
                                    data-search="{{ $searchBlob }}"
                                    style="{{ $isClosed ? 'display: none;' : '' }}">
                                    
                                    {{-- 1. LEFT ACTION BUTTON --}}
                                    <td class="align-middle text-center p-2 border-right" style="width: 75px;">
                                        <a href="{{ route('nrdi.projects.show', $p->prj_id) }}" class="btn btn-xs btn-primary font-weight-bold px-2.5 py-1.5 shadow-sm" style="border-radius: 6px; font-size: 0.78rem; background-color: var(--rd-primary-600) !important; border-color: var(--rd-primary-700) !important; white-space: nowrap;" title="View Project Details">
                                            <i class="fas fa-eye mr-1"></i> View
                                        </a>
                                    </td>

                                    {{-- 2. PROJECT DETAILS (Code + Status on one line, Title below) --}}
                                    <td class="align-middle p-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="mr-2" style="font-size: 0.84rem;" title="Project Head: {{ $p->prj_code }}"><span class="text-muted" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase; margin-right: 3px; letter-spacing: 0.5px;">Head:</span><strong style="color: #4b5563;">{{ $p->prj_code }}</strong></span>
                                            <span class="badge {{ $isClosed ? 'badge-secondary' : 'badge-success' }} text-uppercase" style="font-size: 0.65rem;">
                                                {{ $p->prj_status }}
                                            </span>
                                        </div>
                                        <div class="text-truncate" style="max-width: 380px;" title="{{ $p->prj_title }}">
                                            <a href="{{ route('nrdi.projects.show', $p->prj_id) }}" class="rd-entity-title-link font-weight-bold" style="font-size: 0.9rem;" title="View: {{ $p->prj_title }}">
                                                {{ $p->prj_title }}
                                            </a>
                                        </div>
                                        <div class="mt-1">
                                            <a href="{{ route('projects.financial_view', $p->prj_id) }}" class="btn btn-xs btn-outline-info font-weight-bold" style="font-size: 0.72rem; padding: 2px 7px; border-radius: 4px;" title="Open Financial View">
                                                <i class="fas fa-chart-line mr-1"></i> Financial View
                                            </a>
                                        </div>
                                    </td>

                                    {{-- 3. DIVISION --}}
                                    <td class="align-middle p-2">
                                        <span class="badge badge-light border text-dark font-weight-normal px-2 py-1" style="font-size: 0.82rem;" title="{{ $p->unit?->unt_name }}">
                                            <i class="fas fa-building text-secondary mr-1"></i> {{ $p->unit?->unt_name ?? 'N/A' }}
                                        </span>
                                    </td>

                                    {{-- 4. TEAM / EMPLOYEES COUNT --}}
                                    <td class="align-middle text-center p-2">
                                        @if(($p->emp_count ?? 0) > 0)
                                            <span class="badge badge-pill font-weight-bold text-white px-2.5 py-1 shadow-xs" style="background-color: #0284c7; font-size: 11px;" title="{{ $p->emp_count }} Active Employees Assigned">
                                                <i class="fas fa-users mr-1 text-xs"></i>{{ $p->emp_count }} {{ $p->emp_count === 1 ? 'Emp' : 'Emps' }}
                                            </span>
                                        @else
                                            <span class="badge badge-pill badge-light border text-muted px-2 py-0.5" style="font-size: 10px;" title="No active employees">
                                                <i class="fas fa-user-slash mr-1 text-xs"></i>0
                                            </span>
                                        @endif
                                    </td>

                                    {{-- 5. BUDGET --}}
                                    <td class="align-middle text-right p-2 font-weight-bold" style="font-size: 0.9rem; color: var(--rd-text1);">
                                        <div>Rs. {{ number_format((float) ($p->prj_propcost ?? 0)) }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 text-muted" style="opacity: 0.3;"></i>
                                        <p class="font-weight-bold mb-0">No projects found.</p>
                                    </td>
                                </tr>
                            @endforelse

                            {{-- No matches dynamically row --}}
                            <tr id="noMatchRow" style="display: none;">
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-search fa-2x mb-2 text-warning d-block"></i>
                                    <strong>No projects matching your search criteria.</strong>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    /* Compact Table Styling */
    .table td { vertical-align: middle; font-size: 0.85rem; padding: 0.5rem; }
    .btn-xs { padding: 0.1rem 0.4rem; font-size: 0.7rem; line-height: 1.2; border-radius: 4px; }
    .text-xs { font-size: 0.7rem; }
    .sticky-top { position: sticky; top: 0; background-color: var(--rd-surface2, #f8fafc); border-bottom: 2px solid var(--rd-border, #e2e8f0); }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('projectSearchInput');
    const clearBtn = document.getElementById('clearSearchBtn');
    const clearGroup = document.getElementById('clearSearchGroup');
    const divisionFilter = document.getElementById('divisionFilter');
    const projectFilter = document.getElementById('projectSelectFilter');
    const resetBtn = document.getElementById('resetFiltersBtn');
    const rows = document.querySelectorAll('#projectsTableBody tr.project-row');
    const noMatchRow = document.getElementById('noMatchRow');
    const visibleCountEl = document.getElementById('visibleProjectsCount');
    const statusButtons = document.querySelectorAll('.status-tab-btn');

    const origCounts = {
        open: {{ $openCount }},
        closed: {{ $closedCount }},
        all: {{ $totalCount }}
    };

    let currentStatusTab = 'open';

    function applyFilters() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedDiv = divisionFilter ? divisionFilter.value : 'all';
        const selectedPrj = projectFilter ? projectFilter.value : 'all';

        if (clearGroup) {
            clearGroup.style.display = query.length > 0 ? '' : 'none';
        }

        let visibleCount = 0;
        let openMatches = 0;
        let closedMatches = 0;
        let allMatches = 0;

        rows.forEach(function(row) {
            const searchBlob = row.getAttribute('data-search') || '';
            const untId = row.getAttribute('data-unt-id') || '';
            const prjId = row.getAttribute('data-id') || '';
            const status = row.getAttribute('data-status') || ''; // 'open' or 'closed'

            const matchesQuery = !query || searchBlob.includes(query);
            const matchesDiv = selectedDiv === 'all' || untId === selectedDiv;
            const matchesPrj = selectedPrj === 'all' || prjId === selectedPrj;

            const baseMatch = matchesQuery && matchesDiv && matchesPrj;

            if (baseMatch) {
                allMatches++;
                if (status === 'open') openMatches++;
                if (status === 'closed') closedMatches++;
            }

            // When searching, match across open, closed, all!
            // When query is empty, respect the current active status tab!
            let show = false;
            if (query) {
                show = baseMatch;
            } else {
                if (currentStatusTab === 'all') {
                    show = baseMatch;
                } else {
                    show = baseMatch && (status === currentStatusTab);
                }
            }

            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        // Update count badges
        const badgeOpen = document.getElementById('badge-open');
        const badgeClosed = document.getElementById('badge-closed');
        const badgeAll = document.getElementById('badge-all');

        if (query) {
            if (badgeOpen) badgeOpen.textContent = `(${openMatches})`;
            if (badgeClosed) badgeClosed.textContent = `(${closedMatches})`;
            if (badgeAll) badgeAll.textContent = `(${allMatches})`;
        } else {
            if (badgeOpen) badgeOpen.textContent = `(${origCounts.open})`;
            if (badgeClosed) badgeClosed.textContent = `(${origCounts.closed})`;
            if (badgeAll) badgeAll.textContent = `(${origCounts.all})`;
        }

        if (visibleCountEl) {
            visibleCountEl.textContent = visibleCount;
        }

        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    // Status Tab Buttons
    statusButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            statusButtons.forEach(b => {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary', 'active');

            currentStatusTab = this.getAttribute('data-status');
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            applyFilters();
            searchInput.focus();
        });
    }

    if (divisionFilter) {
        divisionFilter.addEventListener('change', function() {
            const divId = this.value;
            if (projectFilter) {
                const options = projectFilter.querySelectorAll('option');
                options.forEach(opt => {
                    if (opt.value === 'all') return;
                    const optUnt = opt.getAttribute('data-unt-id');
                    opt.style.display = (divId === 'all' || optUnt === divId) ? '' : 'none';
                });
                projectFilter.value = 'all';
            }
            applyFilters();
        });
    }

    if (projectFilter) {
        projectFilter.addEventListener('change', applyFilters);
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (divisionFilter) divisionFilter.value = 'all';
            if (projectFilter) {
                projectFilter.value = 'all';
                projectFilter.querySelectorAll('option').forEach(o => o.style.display = '');
            }
            // Reset to open status
            statusButtons.forEach(b => {
                const s = b.getAttribute('data-status');
                if (s === 'open') {
                    b.classList.remove('btn-outline-primary');
                    b.classList.add('btn-primary', 'active');
                } else {
                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('btn-outline-primary');
                }
            });
            currentStatusTab = 'open';
            applyFilters();
        });
    }

    // Run on page load
    applyFilters();
});
</script>
@endpush
@endsection
