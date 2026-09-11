@extends('welcome')

@section('content')
<div class="content-wrapper pt-3 pb-5" style="background: var(--rd-bg, #f4f6f9); min-height: 100vh;">

    <style>
        .fop-main-card {
            border: 1px solid var(--rd-border, #e2e8f0);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            background: var(--rd-surface, #ffffff);
            overflow: hidden;
        }
        .fop-table td, .fop-table th {
            vertical-align: middle;
            font-size: 0.82rem;
            padding: 0.5rem 0.65rem !important;
            white-space: nowrap;
        }
        .fop-table thead th {
            font-weight: 700;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .fig-cell {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }
        .fig-val {
            font-weight: 600;
            font-size: 0.82rem;
            font-family: 'Consolas', 'Courier New', monospace;
        }
        .btn-drill {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            padding: 0;
            background: #fff;
            border: 1px solid #c0392b;
            color: #c0392b;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            text-decoration: none;
        }
        .btn-drill:hover {
            transform: scale(1.15);
        }
        .btn-drill-blue {
            border-color: #0284c7;
            color: #0284c7;
        }
        .btn-drill-blue:hover {
            background: #0284c7;
            color: #fff;
        }
        .btn-drill-green {
            border-color: #16a34a;
            color: #16a34a;
        }
        .btn-drill-green:hover {
            background: #16a34a;
            color: #fff;
        }
        .filter-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            margin-bottom: 20px;
        }
    </style>

    <div class="container-fluid px-3 px-md-4">

        {{-- ================= TOP HEADER ================= --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <div>
                <h3 class="m-0 text-dark font-weight-bold d-flex align-items-center" style="letter-spacing: 0.5px;">
                    <i class="fas fa-chart-pie text-warning mr-2"></i> Project Financing
                </h3>
                <small class="text-muted font-weight-bold">Consolidated Portfolio & Subhead Financial Intelligence</small>
            </div>
            <div>
                <span class="badge badge-primary px-3 py-2 font-weight-bold shadow-sm" style="font-size: 0.85rem; border-radius: 20px;">
                    <i class="fas fa-layer-group mr-1"></i> <span id="visibleProjectsCount">{{ count($projects) }}</span> Projects
                </span>
            </div>
        </div>

        {{-- ================= FILTER BAR (Division, Project, Live Search) ================= --}}
        <div class="filter-box">
            <div class="row align-items-center">
                
                {{-- 1. Division Filter --}}
                @if(!empty($divisions) && $divisions->count() > 1)
                <div class="col-lg-3 col-md-4 mb-2 mb-lg-0">
                    <label class="mb-1 text-muted font-weight-bold small text-nowrap">
                        <i class="fas fa-sitemap text-info mr-1"></i> Division Filter:
                    </label>
                    <select class="form-control form-control-sm rounded-pill font-weight-bold shadow-sm" id="divisionFilter" style="height: 38px;">
                        <option value="all">— All Divisions —</option>
                        @foreach($divisions as $div)
                            <option value="{{ $div->unt_id }}">{{ $div->unt_namesh ?: $div->unt_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- 2. Project Dropdown Filter --}}
                <div class="{{ (!empty($divisions) && $divisions->count() > 1) ? 'col-lg-3 col-md-4' : 'col-lg-4 col-md-6' }} mb-2 mb-lg-0">
                    <label class="mb-1 text-muted font-weight-bold small text-nowrap">
                        <i class="fas fa-filter text-primary mr-1"></i> Select Project:
                    </label>
                    <select class="form-control form-control-sm rounded-pill font-weight-bold shadow-sm" id="projectFilter" style="height: 38px;">
                        <option value="all">— All Projects —</option>
                        @foreach($heads as $h)
                            <option value="{{ $h->hed_id }}" data-unt-id="{{ $h->hed_unt_id }}">
                                {{ $h->hed_code }} — {{ \Illuminate\Support\Str::limit($h->prj_title, 35) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Live Search Bar --}}
                <div class="{{ (!empty($divisions) && $divisions->count() > 1) ? 'col-lg-5 col-md-4' : 'col-lg-6 col-md-6' }} mb-2 mb-lg-0">
                    <label class="mb-1 text-muted font-weight-bold small text-nowrap">
                        <i class="fas fa-search text-success mr-1"></i> Instant Live Search:
                    </label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="projectSearchInput" class="form-control form-control-sm rounded-pill shadow-sm"
                               placeholder="Search by Head Code, Project Title, Division..."
                               style="height: 38px; padding-left: 16px; font-weight: 600;">
                        <div class="input-group-append" style="margin-left: -35px; z-index: 5;">
                            <button class="btn btn-link text-muted pr-2" type="button" id="clearSearchBtn" title="Clear Search" style="display: none;">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 4. Reset Button --}}
                <div class="col-lg-1 col-md-12 text-lg-right mt-2 mt-lg-0">
                    <label class="d-none d-lg-block mb-1">&nbsp;</label>
                    <button type="button" id="resetFiltersBtn" class="btn btn-outline-secondary btn-sm rounded-pill font-weight-bold px-3 shadow-sm w-100" style="height: 38px;" title="Reset all filters">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>

            </div>
        </div>

        {{-- ================= ALL PROJECTS PORTFOLIO TABLE ================= --}}
        <div class="fop-main-card mb-4">
            <div class="table-responsive">
                <table class="table table-bordered table-hover fop-table mb-0" id="projectsPortfolioTable">
                    <thead>
                        <tr>
                            <th rowspan="2" style="min-width: 85px; vertical-align: middle; background: #0f172a; color: #ffffff;" class="text-center">
                                Detail View
                            </th>
                            <th rowspan="2" style="min-width: 140px; vertical-align: middle; background: #0f172a; color: #ffffff;">
                                <i class="fas fa-barcode mr-1 text-warning"></i> Head / Division
                            </th>
                            <th colspan="5" class="text-center text-white" style="background: #0284c7; font-size: 0.82rem; letter-spacing: 0.5px;">
                                <i class="fas fa-building mr-1"></i> Project Scope (PCC)
                            </th>
                            <th colspan="5" class="text-center text-white" style="background: #16a34a; font-size: 0.82rem; letter-spacing: 0.5px;">
                                <i class="fas fa-university mr-1"></i> CSRF Scope (CF)
                            </th>
                        </tr>
                        <tr>
                            {{-- Pcc columns --}}
                            <th style="background: #e0f2fe; color: #0369a1;">Received</th>
                            <th style="background: #e0f2fe; color: #0369a1;">Expenditure</th>
                            <th style="background: #e0f2fe; color: #0369a1;">Commitments</th>
                            <th style="background: #e0f2fe; color: #0369a1;">In Process</th>
                            <th style="background: #bae6fd; color: #0284c7; font-weight: 800;">Available</th>
                            {{-- CSRF columns --}}
                            <th style="background: #dcfce7; color: #15803d;">Received</th>
                            <th style="background: #dcfce7; color: #15803d;">Expenditure</th>
                            <th style="background: #dcfce7; color: #15803d;">Commitments</th>
                            <th style="background: #dcfce7; color: #15803d;">In Process</th>
                            <th style="background: #bbf7d0; color: #16a34a; font-weight: 800;">Available</th>
                        </tr>
                    </thead>
                    <tbody id="projectsTableBody">
                        @forelse($projects as $prj)
                        <tr class="project-row"
                            data-head-id="{{ $prj['head_id'] }}"
                            data-prj-id="{{ $prj['prj_id'] }}"
                            data-unt-id="{{ $prj['unt_id'] }}"
                            data-search="{{ strtolower($prj['head_code'] . ' ' . $prj['title'] . ' ' . $prj['division'] . ' ' . $prj['status']) }}">
                            
                            {{-- 1. DETAIL VIEW (First Column) --}}
                            <td class="text-center">
                                <a href="{{ route('projects.financial_view', $prj['prj_id'] ?: $prj['head_id']) }}"
                                   class="btn btn-xs btn-primary shadow-sm font-weight-bold px-2.5 py-1 rounded-pill"
                                   style="background: #0284c7; border-color: #0284c7; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 4px;"
                                   title="View Project Financial Breakdown">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>

                            {{-- 2. Head Code & Division Only --}}
                            <td>
                                <div class="d-flex align-items-center justify-content-between" style="gap: 6px;">
                                    <a href="{{ route('projects.financial_view', $prj['prj_id'] ?: $prj['head_id']) }}"
                                       class="font-weight-bold text-primary text-decoration-none" style="font-size: 0.95rem;" title="{{ $prj['title'] }}">
                                        {{ $prj['head_code'] }}
                                    </a>
                                    @if(!empty($prj['division']))
                                        <span class="badge badge-light border text-secondary font-weight-bold px-2 py-0.5" style="font-size: 11px;">
                                            {{ $prj['division'] }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Pcc Received --}}
                            <td>
                                <div class="fig-cell">
                                    <span class="fig-val">{{ number_format($prj['pcc_received']) }}</span>
                                    <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'pcc', 'figure' => 'received']) }}"
                                       class="btn-drill btn-drill-blue" title="PCC Received Breakdown"><i class="fas fa-search"></i></a>
                                </div>
                            </td>

                            {{-- Pcc Expenditure --}}
                            <td>
                                <div class="fig-cell">
                                    <span class="fig-val">{{ number_format(abs($prj['pcc_expenditure'])) }}</span>
                                    <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'pcc', 'figure' => 'expenditure']) }}"
                                       class="btn-drill btn-drill-blue" title="PCC Expenditure Breakdown"><i class="fas fa-search"></i></a>
                                </div>
                            </td>

                            {{-- Pcc Commitments --}}
                            <td>
                                <div class="fig-cell">
                                    <span class="fig-val">{{ number_format(abs($prj['pcc_commitments'])) }}</span>
                                    <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'pcc', 'figure' => 'commitments']) }}"
                                       class="btn-drill btn-drill-blue" title="PCC Commitments"><i class="fas fa-search"></i></a>
                                </div>
                            </td>

                            {{-- Pcc In Process --}}
                            <td>
                                <div class="fig-cell">
                                    <span class="fig-val">{{ number_format($prj['pcc_in_process']) }}</span>
                                    <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'pcc', 'figure' => 'in-process']) }}"
                                       class="btn-drill btn-drill-blue" title="PCC In Process"><i class="fas fa-search"></i></a>
                                </div>
                            </td>

                            {{-- Pcc Available --}}
                            <td class="font-weight-bold text-right {{ $prj['pcc_can_be_spent'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-family: 'Consolas', monospace;">
                                {{ number_format($prj['pcc_can_be_spent']) }}
                            </td>

                            {{-- CSRF Received --}}
                            <td>
                                <div class="fig-cell">
                                    <span class="fig-val">{{ number_format($prj['cf_received']) }}</span>
                                    <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'csrf', 'figure' => 'received']) }}"
                                       class="btn-drill btn-drill-green" title="CSRF Received Breakdown"><i class="fas fa-search"></i></a>
                                </div>
                            </td>

                            {{-- CSRF Expenditure --}}
                            <td>
                                <div class="fig-cell">
                                    <span class="fig-val">{{ number_format(abs($prj['cf_expenditure'])) }}</span>
                                    <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'csrf', 'figure' => 'expenditure']) }}"
                                       class="btn-drill btn-drill-green" title="CSRF Expenditure Breakdown"><i class="fas fa-search"></i></a>
                                </div>
                            </td>

                            {{-- CSRF Commitments --}}
                            <td>
                                <div class="fig-cell">
                                    <span class="fig-val">{{ number_format(abs($prj['cf_commitments'])) }}</span>
                                    <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'csrf', 'figure' => 'commitments']) }}"
                                       class="btn-drill btn-drill-green" title="CSRF Commitments"><i class="fas fa-search"></i></a>
                                </div>
                            </td>

                            {{-- CSRF In Process --}}
                            <td>
                                <div class="fig-cell">
                                    <span class="fig-val">{{ number_format($prj['cf_in_process']) }}</span>
                                    <a href="{{ route('division.finance-of-project.drilldown', ['head_id' => $prj['head_id'], 'scope' => 'csrf', 'figure' => 'in-process']) }}"
                                       class="btn-drill btn-drill-green" title="CSRF In Process"><i class="fas fa-search"></i></a>
                                </div>
                            </td>

                            {{-- CSRF Available --}}
                            <td class="font-weight-bold text-right {{ $prj['cf_can_be_spent'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-family: 'Consolas', monospace;">
                                {{ number_format($prj['cf_can_be_spent']) }}
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="12" class="text-center text-muted py-5">
                                <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                <strong>No project financing records found within your permitted data scope.</strong>
                            </td>
                        </tr>
                        @endforelse

                        {{-- Dynamic Row when search yields 0 matches --}}
                        <tr id="noMatchRow" style="display: none;">
                            <td colspan="12" class="text-center text-muted py-5">
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

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('projectSearchInput');
    const clearBtn = document.getElementById('clearSearchBtn');
    const divisionFilter = document.getElementById('divisionFilter');
    const projectFilter = document.getElementById('projectFilter');
    const resetBtn = document.getElementById('resetFiltersBtn');
    const rows = document.querySelectorAll('#projectsTableBody tr.project-row');
    const noMatchRow = document.getElementById('noMatchRow');
    const visibleCountEl = document.getElementById('visibleProjectsCount');

    function applyFilters() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const selectedDiv = divisionFilter ? divisionFilter.value : 'all';
        const selectedHead = projectFilter ? projectFilter.value : 'all';

        if (clearBtn) {
            clearBtn.style.display = query.length > 0 ? 'inline-block' : 'none';
        }

        let visibleCount = 0;

        rows.forEach(function(row) {
            const searchData = row.getAttribute('data-search') || '';
            const untId = row.getAttribute('data-unt-id') || '';
            const headId = row.getAttribute('data-head-id') || '';

            const matchesSearch = query === '' || searchData.includes(query);
            const matchesDiv = selectedDiv === 'all' || untId === selectedDiv;
            const matchesHead = selectedHead === 'all' || headId === selectedHead;

            if (matchesSearch && matchesDiv && matchesHead) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }

        if (visibleCountEl) {
            visibleCountEl.textContent = visibleCount;
        }
    }

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
            // Update project dropdown options according to division
            const divId = this.value;
            if (projectFilter) {
                const projOptions = projectFilter.querySelectorAll('option');
                projOptions.forEach(function(opt) {
                    if (opt.value === 'all') return;
                    const optUnt = opt.getAttribute('data-unt-id');
                    if (divId === 'all' || optUnt === divId) {
                        opt.style.display = '';
                    } else {
                        opt.style.display = 'none';
                    }
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
                const projOptions = projectFilter.querySelectorAll('option');
                projOptions.forEach(function(opt) { opt.style.display = ''; });
            }
            applyFilters();
        });
    }
});
</script>
@endpush
@endsection
