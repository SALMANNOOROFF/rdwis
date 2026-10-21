@extends('welcome')

@section('content')
@php
    $currentPath = request()->path();
    if (str_starts_with($currentPath, 'dg')) {
        $role = 'DG';
        $roleTitle = 'DG Portal';
        $routePrefix = 'dg';
    } elseif (str_starts_with($currentPath, 'ddg')) {
        $role = 'DDG';
        $roleTitle = 'DDG Portal';
        $routePrefix = 'ddg';
    } elseif (str_starts_with($currentPath, 'dlog')) {
        $role = 'DLOG';
        $roleTitle = 'Director Logistics (DLOG) Portal';
        $routePrefix = 'dlog';
    } elseif (str_starts_with($currentPath, 'it')) {
        $role = 'IT';
        $roleTitle = 'Staff Officer IT (SO IT) Portal';
        $routePrefix = 'it';
    } else {
        $role = 'MD';
        $roleTitle = 'MD Portal';
        $routePrefix = 'md';
    }
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
            {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            {{-- 1. PENDING ACTION TAB                                       --}}
            {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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
                                <i class="fas fa-building text-secondary" style="font-size: 11px;"></i> {{ $uName }}
                                <span class="div-badge">{{ $groupCases->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="table-responsive hub-table-wrapper">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 65px; text-align: center;"><i class="fas fa-eye mr-1"></i> View</th>
                                <th style="width: 50px; text-align: center;">Type</th>
                                <th>Candidate / Employee Details</th>
                                <th style="width: 170px;">Project</th>
                                <th style="width: 110px;">Date</th>
                                <th style="width: 140px; text-align: right;">Proposed Salary</th>
                                <th style="width: 160px; text-align: center;">Status</th>
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
                                <td class="text-center">
                                    <a href="{{ route("{$routePrefix}.contract-cases.show", $p->ctc_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div class="type-badge">{{ $typeCode }}</div>
                                </td>
                                <td>
                                    <a href="{{ route("{$routePrefix}.contract-cases.show", $p->ctc_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case: {{ $p->ctc_empnamecomp }}">
                                        {{ $p->ctc_empnamecomp }}
                                    </a>
                                    <div class="text-ref">Ref: CC-{{ $p->ctc_id }} &bull; {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})</div>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 12.5px;">{{ $p->project_code }}</div>
                                    <div class="text-ref text-truncate" style="max-width: 170px;">{{ $p->division_name }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                    {{ \Carbon\Carbon::parse($p->ctc_date ?? now())->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->ctc_newsalary) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani">
                                        <i class="fas fa-user-clock text-warning"></i> {{ strtoupper($p->current_stage ?? $role) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted rajdhani" style="opacity: 0.7; font-size: 13px;">
                                        <i class="fas fa-check-double fa-2x mb-2 d-block text-primary" style="opacity: 0.4;"></i>
                                        NO PENDING CASES IN YOUR QUEUE
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            {{-- 2. OPEN CASES TAB                                           --}}
            {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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
                                <i class="fas fa-building text-secondary" style="font-size: 11px;"></i> {{ $uName }}
                                <span class="div-badge">{{ $groupCases->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="table-responsive hub-table-wrapper">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 65px; text-align: center;"><i class="fas fa-eye mr-1"></i> View</th>
                                <th style="width: 50px; text-align: center;">Type</th>
                                <th>Candidate Details</th>
                                <th style="width: 170px;">Project</th>
                                <th style="width: 110px;">Date</th>
                                <th style="width: 140px; text-align: right;">Proposed Salary</th>
                                <th style="width: 160px; text-align: center;">Status</th>
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
                                <td class="text-center">
                                    <a href="{{ route("{$routePrefix}.contract-cases.show", $p->ctc_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div class="type-badge">{{ $typeCode }}</div>
                                </td>
                                <td>
                                    <a href="{{ route("{$routePrefix}.contract-cases.show", $p->ctc_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case: {{ $p->ctc_empnamecomp }}">
                                        {{ $p->ctc_empnamecomp }}
                                    </a>
                                    <div class="text-ref">Ref: CC-{{ $p->ctc_id }} &bull; {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})</div>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 12.5px;">{{ $p->project_code }}</div>
                                    <div class="text-ref text-truncate" style="max-width: 170px;">{{ $p->division_name }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                    {{ \Carbon\Carbon::parse($p->ctc_date ?? now())->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->ctc_newsalary) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani">
                                        <i class="fas fa-hourglass-half text-info"></i> {{ strtoupper($p->current_stage ?? 'In Pipeline') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted rajdhani" style="opacity: 0.7; font-size: 13px;">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block text-primary" style="opacity: 0.4;"></i>
                                        NO OPEN CASES IN PIPELINE
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
            {{-- 3. CLOSED CASES TAB                                         --}}
            {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
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
                                <i class="fas fa-building text-secondary" style="font-size: 11px;"></i> {{ $uName }}
                                <span class="div-badge">{{ $groupCases->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="table-responsive hub-table-wrapper">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 65px; text-align: center;"><i class="fas fa-eye mr-1"></i> View</th>
                                <th style="width: 50px; text-align: center;">Type</th>
                                <th>Candidate Details</th>
                                <th style="width: 170px;">Project</th>
                                <th style="width: 110px;">Date</th>
                                <th style="width: 140px; text-align: right;">Final Salary</th>
                                <th style="width: 160px; text-align: center;">Status</th>
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
                                    <a href="{{ route("{$routePrefix}.contract-cases.show", $p->ctc_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td class="text-center">
                                    <div class="type-badge">{{ $typeCode }}</div>
                                </td>
                                <td>
                                    <a href="{{ route("{$routePrefix}.contract-cases.show", $p->ctc_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case: {{ $p->ctc_empnamecomp }}">
                                        {{ $p->ctc_empnamecomp }}
                                    </a>
                                    <div class="text-ref">Ref: CC-{{ $p->ctc_id }} &bull; {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})</div>
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 12.5px;">{{ $p->project_code }}</div>
                                    <div class="text-ref text-truncate" style="max-width: 170px;">{{ $p->division_name }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                    {{ \Carbon\Carbon::parse($p->ctc_date ?? now())->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->ctc_newsalary) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani">
                                        <i class="fas fa-check-circle text-success"></i> {{ strtoupper($p->current_stage ?? $p->ctc_status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted rajdhani" style="opacity: 0.7; font-size: 13px;">
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

    // Handle Division Pill Click
    $(document).on('click', '.div-pill', function(e) {
        e.preventDefault();
        const tabPane = this.closest('.tab-pane');
        if (!tabPane) return;
        const divId = this.getAttribute('data-div');

        tabPane.querySelectorAll('.div-pill').forEach(p => p.classList.remove('active'));
        this.classList.add('active');

        const rows = tabPane.querySelectorAll('.hub-row');
        rows.forEach(row => {
            if (row.getAttribute('data-div') === divId) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Handle Bootstrap Tab Switching
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        const targetTabId = $(e.target).attr('href');
        if (targetTabId) {
            const pane = document.querySelector(targetTabId);
            if (pane) filterTabByActivePill(pane);
        }
    });

    // Initialize all tabs on load
    document.querySelectorAll('.tab-pane').forEach(pane => {
        filterTabByActivePill(pane);
    });

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
});
</script>
@endpush
@endsection

