@extends('welcome')

@section('content')
@php
    $routePrefix = 'hr';
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

.scrutiny-hub { font-family: 'Inter', sans-serif; background: var(--rd-bg, #f8fafc); min-height: 100vh; color: var(--rd-text1, #0f172a); }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }

.card-clean {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

/* KPI Cards */
.kpi-summary-card {
    background: #FFFFFF;
    border: 1px solid var(--rd-border, #e2e8f0);
    border-radius: 8px;
    padding: 0.65rem 1rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}
.kpi-summary-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--rd-text3, #64748b);
}
.kpi-summary-value {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--rd-text1, #0f172a);
    line-height: 1.2;
    margin-top: 1px;
}

/* Tabs */
.nav-tabs-clean {
    display: flex;
    gap: 4px;
}
.hub-tab-link {
    padding: 8px 16px;
    color: #64748b;
    font-weight: 600;
    font-size: 13px;
    border: none;
    border-bottom: 2.5px solid transparent;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    text-decoration: none !important;
}
.hub-tab-link:hover {
    color: #1e293b;
    border-bottom-color: #cbd5e1;
}
.hub-tab-link.active {
    color: var(--rd-accent, #5F7858);
    border-bottom-color: var(--rd-accent, #5F7858);
    font-weight: 700;
}
.tab-count-badge {
    font-size: 12px;
    opacity: 0.85;
}

/* Division Filter Pills */
.div-filter-bar {
    padding: 2px 0 8px 0;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.div-pill {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 3px 10px;
    color: #475569;
    font-weight: 600;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    outline: none !important;
}
.div-pill:hover:not(.active) {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.div-pill.active {
    background: rgba(95, 120, 88, 0.1);
    border-color: var(--rd-accent, #5F7858);
    color: var(--rd-accent, #5F7858);
    font-weight: 700;
}
.div-badge {
    background: #94a3b8;
    color: #ffffff;
    font-size: 10px;
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: 700;
    min-width: 17px;
    text-align: center;
    line-height: 1.2;
}
.div-pill.active .div-badge {
    background: var(--rd-accent, #5F7858);
}

/* Table Styling - Clean & Squeezed */
.hub-table-wrapper {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    background: #ffffff;
}
.hub-table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    margin-bottom: 0;
}
.hub-table thead th {
    background: #f8fafc !important;
    border-top: none !important;
    border-bottom: 2px solid #e2e8f0 !important;
    border-left: none !important;
    border-right: none !important;
    color: #475569 !important;
    font-family: 'Rajdhani', sans-serif;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-size: 11.5px;
    font-weight: 700;
    padding: 8px 12px !important;
    white-space: nowrap;
    vertical-align: middle;
}
.hub-table tbody td {
    border-top: none !important;
    border-bottom: 1px solid #f1f5f9 !important;
    border-left: none !important;
    border-right: none !important;
    padding: 7px 12px !important;
    vertical-align: middle;
    font-size: 12.5px;
    color: #1e293b;
}
.hub-row {
    transition: background-color 0.15s ease;
}
.hub-row:hover {
    background-color: #f8fafc !important;
}

/* Badges & Elements */
.btn-view-compact {
    background: var(--rd-accent, #5F7858);
    color: #ffffff !important;
    border: 1px solid var(--rd-accent, #5F7858);
    border-radius: 4px;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 11.5px;
    padding: 2px 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
    text-decoration: none !important;
    white-space: nowrap;
}
.btn-view-compact:hover {
    background: #4a5e44;
    border-color: #4a5e44;
    box-shadow: 0 2px 4px rgba(95, 120, 88, 0.25);
}

.type-badge {
    width: 24px;
    height: 22px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 9.5px;
    font-weight: 700;
    color: #334155;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    text-transform: uppercase;
}

.status-pill {
    background: #f8fafc;
    color: #334155;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 10.5px;
    font-weight: 700;
    border: 1px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.text-amount {
    font-family: 'Rajdhani', sans-serif;
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
}
.text-ref {
    font-size: 11px;
    color: #64748b;
    line-height: 1.25;
}
</style>

<div class="content-wrapper scrutiny-hub px-3 px-md-4 py-3">
    
    {{-- Top Header Row --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge badge-primary px-2.5 py-1 font-weight-bold" style="border-radius: 12px; font-size: 9.5px; letter-spacing: 0.5px; background: var(--rd-accent, #5F7858);">
                    HR OPERATIONS AUTHORITY
                </span>
                @if(in_array(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))), ['fin', 'hr', 'nrdi', 'rdw', 'hqs']))
                <div class="btn-group btn-group-sm shadow-sm ml-2" role="group">
                    <a href="{{ route('hr.contract-cases.index', ['mode' => 'm']) }}" 
                       class="btn {{ ($mode ?? 'm') === 'm' ? 'btn-danger font-weight-bold' : 'btn-outline-danger' }}" style="{{ ($mode ?? 'm') === 'm' ? '' : 'background: #FFFFFF;' }} font-size: 11px; padding: 2px 8px;">
                        <i class="fas fa-globe mr-1"></i> ALL DEPT
                    </a>
                    <a href="{{ route('hr.contract-cases.index', ['mode' => 's']) }}" 
                       class="btn {{ ($mode ?? 'm') === 's' ? 'btn-info font-weight-bold' : 'btn-outline-info' }}"
                       style="{{ ($mode ?? 'm') === 's' ? 'background-color: var(--rd-accent, #5F7858); border-color: var(--rd-accent, #5F7858); color: white;' : 'background: #FFFFFF; border-color: var(--rd-accent, #5F7858); color: var(--rd-accent, #5F7858);' }} font-size: 11px; padding: 2px 8px;">
                        <i class="fas fa-sitemap mr-1"></i> MY DEPT
                    </a>
                </div>
                @endif
            </div>
            <h2 class="font-weight-bold text-dark m-0 rajdhani" style="font-size: 1.5rem; letter-spacing: 0.3px;">HR Contract Scrutiny Hub</h2>
        </div>
        
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('division.contract-cases.create', ['type' => 'Hg']) }}" class="btn btn-primary px-3 py-1 font-weight-bold shadow-sm rajdhani" style="border-radius: 6px; font-size: 12px; background: var(--rd-accent, #5F7858) !important; border-color: var(--rd-accent, #5F7858) !important;">
                <i class="fas fa-user-plus mr-1"></i> Add Employee (Hg)
            </a>
            <a href="{{ route('divhr.employelist') }}" class="btn btn-light border px-3 py-1 font-weight-bold shadow-sm rajdhani" style="border-radius: 6px; font-size: 12px;">
                <i class="fas fa-users mr-1 text-secondary"></i> Employee Directory
            </a>
        </div>
    </div>

    {{-- KPI Cards Row --}}
    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="kpi-summary-card" style="border-left: 3px solid #EF4444;">
                <div class="kpi-summary-label">Pending HR Action</div>
                <div class="kpi-summary-value text-danger rajdhani">{{ $actionReqCases->count() }} <small class="text-muted" style="font-size: 0.75rem;">cases</small></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="kpi-summary-card" style="border-left: 3px solid #F59E0B;">
                <div class="kpi-summary-label">Open in Pipeline</div>
                <div class="kpi-summary-value text-warning rajdhani">{{ $initiatedCases->count() }} <small class="text-muted" style="font-size: 0.75rem;">cases</small></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="kpi-summary-card" style="border-left: 3px solid #10B981;">
                <div class="kpi-summary-label">Closed / Fulfilled</div>
                <div class="kpi-summary-value text-success rajdhani">{{ $completedCases->count() }} <small class="text-muted" style="font-size: 0.75rem;">cases</small></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="kpi-summary-card" style="border-left: 3px solid var(--rd-accent, #5F7858);">
                <div class="kpi-summary-label">Total Salary Volume</div>
                <div class="kpi-summary-value text-primary rajdhani">PKR {{ number_format($actionReqCases->sum('ctc_newsalary')) }}</div>
            </div>
        </div>
    </div>

    <div class="card card-clean mb-4 shadow-sm p-3 p-md-3">

        {{-- Top Tabs & Search Bar Header --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap pb-2 border-bottom" style="gap: 10px;">
            <ul class="nav nav-tabs-clean m-0 border-0" role="tablist" id="hubMainTabs">
                <li class="nav-item">
                    <a class="hub-tab-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                        <i class="fas fa-clock text-warning"></i> Pending Action <span class="tab-count-badge">({{ $actionReqCases->count() }})</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="hub-tab-link" id="open-tab" data-toggle="tab" href="#open" role="tab">
                        <i class="fas fa-folder-open text-primary"></i> Open <span class="tab-count-badge">({{ $initiatedCases->count() }})</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="hub-tab-link" id="closed-tab" data-toggle="tab" href="#closed" role="tab">
                        <i class="fas fa-check-circle text-success"></i> Close <span class="tab-count-badge">({{ $completedCases->count() }})</span>
                    </a>
                </li>
            </ul>

            {{-- Compact Inline Live Search Bar --}}
            <div class="search-box-inline my-0" style="width: 250px;">
                <div class="input-group input-group-sm" style="border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; background: #ffffff;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-0 text-muted px-2 py-1">
                            <i class="fas fa-search text-secondary" style="font-size: 11px;"></i>
                        </span>
                    </div>
                    <input type="text" id="globalHubSearch" class="form-control border-0 py-1 font-weight-500" style="font-size: 12.5px; box-shadow: none; height: 30px;" placeholder="Search ID, name, project..." autocomplete="off">
                    <div class="input-group-append" id="clearSearchGroup" style="display: none;">
                        <button class="btn btn-white border-0 text-muted px-2 py-1" type="button" id="clearGlobalSearch" title="Clear Search">
                            <i class="fas fa-times-circle text-danger" style="font-size: 11px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content pt-2">
            {{-- 1. PENDING ACTION TAB --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                @php
                    $pendingGroups = $actionReqCases->groupBy(fn($p) => (int)($p->ctc_divisionid ?: ($p->ctc_unt_id ?: 0)));
                    $firstPendingDivId = $pendingGroups->keys()->first();
                @endphp

                @if($pendingGroups->count() > 0)
                    <div class="div-filter-bar">
                        @foreach($pendingGroups as $uId => $groupCases)
                            @php
                                $firstCase = $groupCases->first();
                                $uName = $firstCase->division_short ?: ($firstCase->division_name ?: "Division #$uId");
                            @endphp
                            <button type="button" class="div-pill {{ (string)$uId === (string)$firstPendingDivId ? 'active' : '' }}" data-div="{{ $uId }}">
                                <i class="fas fa-building"></i> {{ $uName }}
                                <span class="div-badge">{{ $groupCases->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="hub-table-wrapper table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 140px; text-align: center;"><i class="fas fa-cogs mr-1"></i> Actions</th>
                                <th style="width: 50px; text-align: center;">Type</th>
                                <th>Candidate Details</th>
                                <th style="width: 170px;">Project</th>
                                <th style="width: 110px;">Date</th>
                                <th style="width: 140px; text-align: right;">Proposed Salary</th>
                                <th style="width: 140px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($actionReqCases as $idx => $p)
                            @php
                                $pDiv = (int)($p->ctc_divisionid ?: ($p->ctc_unt_id ?: 0));
                                $isShown = (string)$pDiv === (string)$firstPendingDivId;
                                $typeCode = strtoupper(substr($p->ctc_type ?? 'CR', 0, 2));
                            @endphp
                            <tr class="hub-row" data-id="{{ $p->ctc_id }}" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route("hr.contract-cases.show", $p->ctc_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @php
                                        $loggedUser = auth()->user();
                                        $isLeader = $loggedUser && ((method_exists($loggedUser, 'isMdDdgDg') && $loggedUser->isMdDdgDg()) || (method_exists($loggedUser, 'isDlog') && $loggedUser->isDlog()));
                                        $userArea = strtolower(trim((string)($loggedUser->acc_untarea ?? '')));
                                        $isHrOrAllowed = $loggedUser && ((method_exists($loggedUser, 'isHr') && $loggedUser->isHr()) || in_array($userArea, ['hr', 'prj', 'rdwprj', 'fin', 'finance'], true));
                                        $caseStatusLower = strtolower(trim((string)($p->ctc_status ?? '')));
                                        $isDraftOrPending = in_array($caseStatusLower, ['draft', 'under revision', 'returned', 'action required'], true) || str_contains($caseStatusLower, 'draft') || str_contains($caseStatusLower, 'revision');
                                        $canCancelHr = !$isLeader && $isHrOrAllowed && $isDraftOrPending;
                                    @endphp
                                    @if($canCancelHr)
                                    <button type="button" class="btn btn-xs btn-outline-danger btn-cancel-contract-case ml-1 font-weight-bold" data-id="{{ $p->ctc_id }}" title="Cancel Contract Case" style="border-radius: 4px; padding: 2px 7px; font-size: 11px;">
                                        <i class="fas fa-ban"></i> Cancel
                                    </button>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="type-badge">{{ $typeCode }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="{{ route('hr.contract-cases.show', $p->ctc_id) }}" class="text-dark font-weight-bold text-decoration-none" style="font-size: 13px;" title="Open Case: {{ $p->ctc_empnamecomp }}">
                                            {{ $p->ctc_empnamecomp }}
                                        </a>
                                    </div>
                                    <div class="text-ref">Ref: CC-{{ $p->ctc_id }} &bull; {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})</div>
                                </td>
                                <td>
                                    <div class="text-dark font-weight-600" style="font-size: 12.5px;">{{ $p->project_code }}</div>
                                    <div class="text-ref text-truncate" style="max-width: 160px;">{{ $p->division_name }}</div>
                                </td>
                                <td class="text-muted font-weight-600 rajdhani" style="font-size: 12px; white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($p->ctc_date ?? now())->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount">Rs. {{ number_format($p->ctc_newsalary) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill text-warning border-warning">
                                        <i class="fas fa-user-clock mr-1"></i> {{ strtoupper($p->current_stage ?? 'HR') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted rajdhani" style="font-size: 13px;">
                                        <i class="fas fa-check-double fa-2x mb-2 d-block text-secondary" style="opacity: 0.4;"></i>
                                        NO PENDING CASES IN YOUR QUEUE
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. OPEN CASES TAB --}}
            <div class="tab-pane fade" id="open" role="tabpanel">
                @php
                    $openGroups = $initiatedCases->groupBy(fn($p) => (int)($p->ctc_divisionid ?: ($p->ctc_unt_id ?: 0)));
                    $firstOpenDivId = $openGroups->keys()->first();
                @endphp

                @if($openGroups->count() > 0)
                    <div class="div-filter-bar">
                        @foreach($openGroups as $uId => $groupCases)
                            @php
                                $firstCase = $groupCases->first();
                                $uName = $firstCase->division_short ?: ($firstCase->division_name ?: "Division #$uId");
                            @endphp
                            <button type="button" class="div-pill {{ (string)$uId === (string)$firstOpenDivId ? 'active' : '' }}" data-div="{{ $uId }}">
                                <i class="fas fa-building"></i> {{ $uName }}
                                <span class="div-badge">{{ $groupCases->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="hub-table-wrapper table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 140px; text-align: center;"><i class="fas fa-cogs mr-1"></i> Actions</th>
                                <th style="width: 50px; text-align: center;">Type</th>
                                <th>Candidate Details</th>
                                <th style="width: 170px;">Project</th>
                                <th style="width: 110px;">Date</th>
                                <th style="width: 140px; text-align: right;">Proposed Salary</th>
                                <th style="width: 140px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($initiatedCases as $idx => $p)
                            @php
                                $pDiv = (int)($p->ctc_divisionid ?: ($p->ctc_unt_id ?: 0));
                                $isShown = (string)$pDiv === (string)$firstOpenDivId;
                                $typeCode = strtoupper(substr($p->ctc_type ?? 'CR', 0, 2));
                            @endphp
                            <tr class="hub-row" data-id="{{ $p->ctc_id }}" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route("hr.contract-cases.show", $p->ctc_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div class="type-badge">{{ $typeCode }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="{{ route('hr.contract-cases.show', $p->ctc_id) }}" class="text-dark font-weight-bold text-decoration-none" style="font-size: 13px;" title="Open Case: {{ $p->ctc_empnamecomp }}">
                                            {{ $p->ctc_empnamecomp }}
                                        </a>
                                    </div>
                                    <div class="text-ref">Ref: CC-{{ $p->ctc_id }} &bull; {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})</div>
                                </td>
                                <td>
                                    <div class="text-dark font-weight-600" style="font-size: 12.5px;">{{ $p->project_code }}</div>
                                    <div class="text-ref text-truncate" style="max-width: 160px;">{{ $p->division_name }}</div>
                                </td>
                                <td class="text-muted font-weight-600 rajdhani" style="font-size: 12px; white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($p->ctc_date ?? now())->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount">Rs. {{ number_format($p->ctc_newsalary) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill text-info border-info">
                                        <i class="fas fa-hourglass-half mr-1"></i> {{ strtoupper($p->current_stage ?? 'In Pipeline') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted rajdhani" style="font-size: 13px;">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary" style="opacity: 0.4;"></i>
                                        NO OPEN CASES IN PIPELINE
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. CLOSED CASES TAB --}}
            <div class="tab-pane fade" id="closed" role="tabpanel">
                @php
                    $closedGroups = $completedCases->groupBy(fn($p) => (int)($p->ctc_divisionid ?: ($p->ctc_unt_id ?: 0)));
                    $firstClosedDivId = $closedGroups->keys()->first();
                @endphp

                @if($closedGroups->count() > 0)
                    <div class="div-filter-bar">
                        @foreach($closedGroups as $uId => $groupCases)
                            @php
                                $firstCase = $groupCases->first();
                                $uName = $firstCase->division_short ?: ($firstCase->division_name ?: "Division #$uId");
                            @endphp
                            <button type="button" class="div-pill {{ (string)$uId === (string)$firstClosedDivId ? 'active' : '' }}" data-div="{{ $uId }}">
                                <i class="fas fa-building"></i> {{ $uName }}
                                <span class="div-badge">{{ $groupCases->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="hub-table-wrapper table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 65px; text-align: center;"><i class="fas fa-eye mr-1"></i> View</th>
                                <th style="width: 50px; text-align: center;">Type</th>
                                <th>Candidate Details</th>
                                <th style="width: 170px;">Project</th>
                                <th style="width: 110px;">Date</th>
                                <th style="width: 140px; text-align: right;">Final Salary</th>
                                <th style="width: 140px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completedCases as $idx => $p)
                            @php
                                $pDiv = (int)($p->ctc_divisionid ?: ($p->ctc_unt_id ?: 0));
                                $isShown = (string)$pDiv === (string)$firstClosedDivId;
                                $typeCode = strtoupper(substr($p->ctc_type ?? 'CR', 0, 2));
                            @endphp
                            <tr class="hub-row" data-id="{{ $p->ctc_id }}" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center">
                                    <a href="{{ route("hr.contract-cases.show", $p->ctc_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div class="type-badge">{{ $typeCode }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <a href="{{ route('hr.contract-cases.show', $p->ctc_id) }}" class="text-dark font-weight-bold text-decoration-none" style="font-size: 13px;" title="Open Case: {{ $p->ctc_empnamecomp }}">
                                            {{ $p->ctc_empnamecomp }}
                                        </a>
                                    </div>
                                    <div class="text-ref">Ref: CC-{{ $p->ctc_id }} &bull; {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})</div>
                                </td>
                                <td>
                                    <div class="text-dark font-weight-600" style="font-size: 12.5px;">{{ $p->project_code }}</div>
                                    <div class="text-ref text-truncate" style="max-width: 160px;">{{ $p->division_name }}</div>
                                </td>
                                <td class="text-muted font-weight-600 rajdhani" style="font-size: 12px; white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($p->ctc_date ?? now())->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount">Rs. {{ number_format($p->ctc_newsalary) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill text-success border-success">
                                        <i class="fas fa-check-circle mr-1"></i> {{ strtoupper($p->current_stage ?? $p->ctc_status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted rajdhani" style="font-size: 13px;">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block text-success" style="opacity: 0.4;"></i>
                                        NO CLOSED / FULFILLED CASES
                                    </div>
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

@push('scripts')
<script>
$(document).ready(function() {
    function filterTabByActivePill(tabPane) {
        if (!tabPane) return;
        const pills = tabPane.querySelectorAll('.div-pill');
        const rows = tabPane.querySelectorAll('.hub-row');
        if (pills.length === 0) {
            rows.forEach(r => r.style.display = '');
            return;
        }

        let activePill = tabPane.querySelector('.div-pill.active');
        if (!activePill && pills.length > 0) {
            activePill = pills[0];
            activePill.classList.add('active');
        }

        const activeDiv = activePill ? activePill.getAttribute('data-div') : null;
        rows.forEach(row => {
            if (activeDiv === null || row.getAttribute('data-div') === activeDiv) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Attach click listeners to all division pills
    $(document).on('click', '.div-pill', function(e) {
        e.preventDefault();
        const $this = $(this);
        const tabPane = $this.closest('.tab-pane')[0];
        
        $this.closest('.div-filter-bar').find('.div-pill').removeClass('active');
        $this.addClass('active');

        filterTabByActivePill(tabPane);
    });

    // When tab changes, apply active filter pill of that tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const targetPane = document.querySelector($(e.target).attr('href'));
        if (targetPane) {
            filterTabByActivePill(targetPane);
        }
    });

    // Initial load
    const activePane = document.querySelector('.tab-pane.active');
    if (activePane) {
        filterTabByActivePill(activePane);
    }

    // ── Live Universal Search across all tabs, divisions, and cases ───────
    const $search = $('#globalHubSearch');
    const $clearBtn = $('#clearGlobalSearch');
    const $clearGroup = $('#clearSearchGroup');

    const tabOrigCounts = {
        'pending': {{ $actionReqCases->count() }},
        'open': {{ $initiatedCases->count() }},
        'closed': {{ $completedCases->count() }}
    };

    function performSearch() {
        const query = $search.val().trim().toLowerCase();
        
        if (!query) {
            $clearGroup.hide();

            // Re-apply normal division pill filter
            document.querySelectorAll('.tab-pane').forEach(pane => {
                filterTabByActivePill(pane);
            });

            // Restore tab titles
            $('#pending-tab .tab-count-badge').text(`(${tabOrigCounts['pending']})`);
            $('#open-tab .tab-count-badge').text(`(${tabOrigCounts['open']})`);
            $('#closed-tab .tab-count-badge').text(`(${tabOrigCounts['closed']})`);
            return;
        }

        $clearGroup.show();

        let tabMatches = { 'pending': 0, 'open': 0, 'closed': 0 };
        const tabConfigs = [
            { id: 'pending' },
            { id: 'open' },
            { id: 'closed' }
        ];

        tabConfigs.forEach(tab => {
            const $tabPane = $(`#${tab.id}`);
            const $rows = $tabPane.find('.hub-row');

            $rows.each(function() {
                const $row = $(this);
                const caseId = ($row.attr('data-id') || '').toLowerCase();
                const rowText = $row.text().toLowerCase();

                const isMatch = caseId === query || 
                                `cc-${caseId}` === query || 
                                caseId.includes(query) || 
                                rowText.includes(query);

                if (isMatch) {
                    $row.show();
                    tabMatches[tab.id]++;
                } else {
                    $row.hide();
                }
            });
        });

        // Update tab link badges
        $('#pending-tab .tab-count-badge').text(`(${tabMatches['pending']})`);
        $('#open-tab .tab-count-badge').text(`(${tabMatches['open']})`);
        $('#closed-tab .tab-count-badge').text(`(${tabMatches['closed']})`);

        // If active tab has 0 matches, auto switch to whichever tab has matches
        const activeTabHref = $('#hubMainTabs .hub-tab-link.active').attr('href');
        const activeTabId = activeTabHref ? activeTabHref.replace('#', '') : '';
        if (tabMatches[activeTabId] === 0) {
            for (let t of ['pending', 'open', 'closed']) {
                if (tabMatches[t] > 0) {
                    $(`#hubMainTabs a[href="#${t}"]`).tab('show');
                    break;
                }
            }
        }
    }

    $search.on('input', performSearch);
    $clearBtn.on('click', function() {
        $search.val('').focus();
        performSearch();
    });

    // ── Contract Case Cancel Handler ──
    $(document).on('click', '.btn-cancel-contract-case', function(e) {
        e.preventDefault();
        const caseId = $(this).data('id');

        Swal.fire({
            title: `Cancel Contract Case #CC-${caseId}`,
            input: 'textarea',
            inputLabel: 'Reason for Cancellation (Required)',
            inputPlaceholder: 'Specify formal remarks for cancelling this case...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel Case',
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'Cancellation remarks are mandatory!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('hr/contract-cases') }}/${caseId}/cancel`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        remarks: result.value
                    },
                    success: function(res) {
                        Swal.fire('Cancelled', res.message, 'success').then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to cancel contract case.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection
