{{-- resources/views/cases/index.blade.php --}}
@extends('welcome')

@section('content')
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
.text-ref {
    font-size: 11px;
    color: #64748b;
    line-height: 1.25;
}
</style>

<div class="content-wrapper scrutiny-hub px-3 px-md-4 py-3">

    {{-- Initiator Header Banner with Compact Create Button (Hidden for MD, DDG, DG, DLOG) --}}
    @if(!$isApexCommand)
    <div class="py-2 bg-transparent mb-2">
        <div class="container-fluid px-0">
            <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                <div>
                    <span class="badge px-2 py-0.5 mb-1 font-weight-bold text-uppercase" style="font-size: 9.5px; letter-spacing: 0.8px; background: #e2e8f0; color: #1e293b; border-radius: 4px;">
                        DIVISION OPERATIONS
                    </span>
                    <h2 class="m-0 font-weight-bold rajdhani" style="font-size: 1.5rem; color: #0f172a; line-height: 1.2;">Case Initiation Hub</h2>
                    <p class="text-muted mb-0" style="font-size: 12.5px;">Central specialized portal for creating and tracking case workflows.</p>
                </div>
                <div>
                    {{-- Compact Create New Case Button --}}
                    <form method="POST" action="{{ route('cases.initiate') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 py-1 font-weight-bold rajdhani shadow-sm" style="font-size: 12px; background: #4d7c0f; border-color: #4d7c0f; letter-spacing: 0.6px;">
                            <i class="fas fa-plus-circle mr-1 text-warning"></i> CREATE NEW CASE
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm py-2 px-3 small border-0 mb-3" style="background: #f0fdf4; color: #16a34a; border-left: 4px solid #16a34a !important;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close text-success" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 px-3 small border-0 mb-3" style="background: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626 !important;">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close text-danger" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- Main Scrutiny Hub Card --}}
    <div class="card card-clean mb-4 shadow-sm p-3 p-md-3">
        {{-- Hub Tabs Header & Compact Live Search Bar --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap pb-2 border-bottom" style="gap: 10px;">
            <ul class="nav nav-tabs-clean m-0 border-0" role="tablist" id="hubMainTabs">
                <li class="nav-item">
                    <a class="hub-tab-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                        <i class="fas fa-clock text-warning"></i> Pending Action <span class="tab-count-badge">({{ $caseCount }})</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="hub-tab-link" id="open-tab" data-toggle="tab" href="#open" role="tab">
                        <i class="fas fa-folder-open text-primary"></i> Open <span class="tab-count-badge">({{ $openCount }})</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="hub-tab-link" id="closed-tab" data-toggle="tab" href="#closed" role="tab">
                        <i class="fas fa-check-circle text-success"></i> Close <span class="tab-count-badge">({{ $closedCount }})</span>
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
                    <input type="text" id="globalHubSearch" class="form-control border-0 py-1 font-weight-500" style="font-size: 12.5px; box-shadow: none; height: 30px;" placeholder="Search ID, title, category..." autocomplete="off">
                    <div class="input-group-append" id="clearSearchGroup" style="display: none;">
                        <button class="btn btn-white border-0 text-muted px-2 py-1" type="button" id="clearGlobalSearch" title="Clear Search">
                            <i class="fas fa-times-circle text-danger" style="font-size: 11px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content pt-2">

            {{-- ======================================================== --}}
            {{-- 1. PENDING ACTION TAB --}}
            {{-- ======================================================== --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                @php
                    $pendingGroups = $pending->groupBy(fn($c) => (int)($c->gcs_unt_id ?? $c->creator?->acc_unt_id ?? 0));
                    $firstPendingDivId = $pendingGroups->keys()->first();
                @endphp

                @if($pendingGroups->count() > 0)
                    <div class="div-filter-bar">
                        @foreach($pendingGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? ($uId > 0 ? "Division #$uId" : "General"));
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
                                <th style="width: 140px; text-align: center;"><i class="fas fa-cogs mr-1"></i> Actions</th>
                                <th>Title / Description</th>
                                <th style="width: 120px;">Date</th>
                                <th style="width: 150px; text-align: center;">Category</th>
                                <th style="width: 170px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pending as $idx => $c)
                            @php
                                $cDiv = (int)($c->gcs_unt_id ?? $c->creator?->acc_unt_id ?? 0);
                                $uName = $unitNameMap[$cDiv] ?? ($c->unit?->unt_namesh ?? $c->unit?->unt_name ?? ($cDiv > 0 ? "Div #$cDiv" : "General"));
                                $isShown = (string)$cDiv === (string)$firstPendingDivId;
                                $statusLower = strtolower(trim((string)($c->gcs_status ?? '')));
                                $statusIcon = match($statusLower) {
                                    'approved' => 'fa-check-circle',
                                    'rejected', 'cancelled' => 'fa-times-circle',
                                    'under review' => 'fa-hourglass-half',
                                    'returned' => 'fa-undo',
                                    'draft' => 'fa-pencil-alt',
                                    default => 'fa-folder'
                                };
                                $isDraftOrPending = in_array($statusLower, ['draft', 'returned', 'action required'], true);
                                $canCancel = ((int)$c->gcs_created_by === (int)($user->acc_id ?? 0) && $isDraftOrPending);
                            @endphp
                            <tr class="hub-row" data-id="{{ $c->gcs_id }}" data-div="{{ $cDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route('cases.show', $c->gcs_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @if($canCancel)
                                    <form method="POST" action="{{ route('cases.cancel', $c->gcs_id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel/delete this draft case?');">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline-danger ml-1 font-weight-bold" title="Cancel Case" style="border-radius: 4px; padding: 2px 7px; font-size: 11px;">
                                            <i class="fas fa-ban"></i> Cancel
                                        </button>
                                    </form>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('cases.show', $c->gcs_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case: {{ $c->gcs_title }}">
                                        {{ $c->gcs_title }}
                                    </a>
                                    <div class="text-ref d-flex align-items-center flex-wrap" style="gap: 5px;">
                                        <span>Ref: #{{ $c->gcs_file_no }}</span>
                                        <span class="text-muted small">by {{ $c->creator?->acc_name ?? 'User' }} ({{ $uName }})</span>
                                    </div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                    {{ \Carbon\Carbon::parse($c->created_at)->format('d M, Y') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border text-secondary font-weight-bold" style="font-size: 11px; padding: 3px 8px; border-radius: 4px;">
                                        {{ $c->gcs_category ?? 'General' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center" style="gap: 2px;">
                                        <span class="status-pill rajdhani">
                                            <i class="fas {{ $statusIcon }}"></i> {{ strtoupper($c->gcs_status) }}
                                        </span>
                                        @php
                                            $destHolder = $c->currentHolder?->acc_name ?: $c->gcs_current_stage;
                                        @endphp
                                        @if($destHolder)
                                            <span class="text-muted small font-weight-bold" style="font-size: 10px; line-height: 1.1;">
                                                <i class="fas fa-map-marker-alt text-secondary mr-0.5"></i> {{ $destHolder }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted small font-weight-500">
                                        <i class="fas fa-inbox fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                                        No pending cases requiring your action.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- 2. OPEN CASES TAB --}}
            {{-- ======================================================== --}}
            <div class="tab-pane fade" id="open" role="tabpanel">
                @php
                    $openGroups = $open->groupBy(fn($c) => (int)($c->gcs_unt_id ?? $c->creator?->acc_unt_id ?? 0));
                    $firstOpenDivId = $openGroups->keys()->first();
                @endphp

                @if($openGroups->count() > 0)
                    <div class="div-filter-bar">
                        @foreach($openGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? ($uId > 0 ? "Division #$uId" : "General"));
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
                                <th style="width: 140px; text-align: center;"><i class="fas fa-cogs mr-1"></i> Actions</th>
                                <th>Title / Description</th>
                                <th style="width: 120px;">Date</th>
                                <th style="width: 150px; text-align: center;">Category</th>
                                <th style="width: 170px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($open as $idx => $c)
                            @php
                                $cDiv = (int)($c->gcs_unt_id ?? $c->creator?->acc_unt_id ?? 0);
                                $uName = $unitNameMap[$cDiv] ?? ($c->unit?->unt_namesh ?? $c->unit?->unt_name ?? ($cDiv > 0 ? "Div #$cDiv" : "General"));
                                $isShown = (string)$cDiv === (string)$firstOpenDivId;
                                $statusLower = strtolower(trim((string)($c->gcs_status ?? '')));
                                $statusIcon = match($statusLower) {
                                    'approved' => 'fa-check-circle',
                                    'rejected', 'cancelled' => 'fa-times-circle',
                                    'under review' => 'fa-hourglass-half',
                                    'returned' => 'fa-undo',
                                    'draft' => 'fa-pencil-alt',
                                    default => 'fa-folder'
                                };
                            @endphp
                            <tr class="hub-row" data-id="{{ $c->gcs_id }}" data-div="{{ $cDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route('cases.show', $c->gcs_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('cases.show', $c->gcs_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case: {{ $c->gcs_title }}">
                                        {{ $c->gcs_title }}
                                    </a>
                                    <div class="text-ref d-flex align-items-center flex-wrap" style="gap: 5px;">
                                        <span>Ref: #{{ $c->gcs_file_no }}</span>
                                        <span class="text-muted small">by {{ $c->creator?->acc_name ?? 'User' }} ({{ $uName }})</span>
                                    </div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                    {{ \Carbon\Carbon::parse($c->created_at)->format('d M, Y') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border text-secondary font-weight-bold" style="font-size: 11px; padding: 3px 8px; border-radius: 4px;">
                                        {{ $c->gcs_category ?? 'General' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center" style="gap: 2px;">
                                        <span class="status-pill rajdhani">
                                            <i class="fas {{ $statusIcon }}"></i> {{ strtoupper($c->gcs_status) }}
                                        </span>
                                        @php
                                            $destHolder = $c->currentHolder?->acc_name ?: $c->gcs_current_stage;
                                        @endphp
                                        @if($destHolder)
                                            <span class="text-muted small font-weight-bold" style="font-size: 10px; line-height: 1.1;">
                                                <i class="fas fa-map-marker-alt text-secondary mr-0.5"></i> {{ $destHolder }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted small font-weight-500">
                                        <i class="fas fa-folder-open fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                                        No active open cases in transit.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- 3. CLOSED CASES TAB --}}
            {{-- ======================================================== --}}
            <div class="tab-pane fade" id="closed" role="tabpanel">
                @php
                    $closedGroups = $closed->groupBy(fn($c) => (int)($c->gcs_unt_id ?? $c->creator?->acc_unt_id ?? 0));
                    $firstClosedDivId = $closedGroups->keys()->first();
                @endphp

                @if($closedGroups->count() > 0)
                    <div class="div-filter-bar">
                        @foreach($closedGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? ($uId > 0 ? "Division #$uId" : "General"));
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
                                <th style="width: 140px; text-align: center;"><i class="fas fa-cogs mr-1"></i> Actions</th>
                                <th>Title / Description</th>
                                <th style="width: 120px;">Date</th>
                                <th style="width: 150px; text-align: center;">Category</th>
                                <th style="width: 170px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($closed as $idx => $c)
                            @php
                                $cDiv = (int)($c->gcs_unt_id ?? $c->creator?->acc_unt_id ?? 0);
                                $uName = $unitNameMap[$cDiv] ?? ($c->unit?->unt_namesh ?? $c->unit?->unt_name ?? ($cDiv > 0 ? "Div #$cDiv" : "General"));
                                $isShown = (string)$cDiv === (string)$firstClosedDivId;
                                $statusLower = strtolower(trim((string)($c->gcs_status ?? '')));
                                $statusIcon = match($statusLower) {
                                    'approved' => 'fa-check-circle',
                                    'rejected', 'cancelled' => 'fa-times-circle',
                                    default => 'fa-archive'
                                };
                            @endphp
                            <tr class="hub-row" data-id="{{ $c->gcs_id }}" data-div="{{ $cDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route('cases.show', $c->gcs_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('cases.show', $c->gcs_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case: {{ $c->gcs_title }}">
                                        {{ $c->gcs_title }}
                                    </a>
                                    <div class="text-ref d-flex align-items-center flex-wrap" style="gap: 5px;">
                                        <span>Ref: #{{ $c->gcs_file_no }}</span>
                                        <span class="text-muted small">by {{ $c->creator?->acc_name ?? 'User' }} ({{ $uName }})</span>
                                    </div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                    {{ \Carbon\Carbon::parse($c->created_at)->format('d M, Y') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border text-secondary font-weight-bold" style="font-size: 11px; padding: 3px 8px; border-radius: 4px;">
                                        {{ $c->gcs_category ?? 'General' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center" style="gap: 2px;">
                                        <span class="status-pill rajdhani">
                                            <i class="fas {{ $statusIcon }}"></i> {{ strtoupper($c->gcs_status) }}
                                        </span>
                                        @php
                                            $destHolder = $c->currentHolder?->acc_name ?: $c->gcs_current_stage;
                                        @endphp
                                        @if($destHolder)
                                            <span class="text-muted small font-weight-bold" style="font-size: 10px; line-height: 1.1;">
                                                <i class="fas fa-map-marker-alt text-secondary mr-0.5"></i> {{ $destHolder }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted small font-weight-500">
                                        <i class="fas fa-archive fa-2x mb-2 text-secondary opacity-50 d-block"></i>
                                        No closed or finalized cases.
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    function filterTabByActivePill(tabPane) {
        if (!tabPane) return;
        const pills = tabPane.querySelectorAll('.div-pill');
        const rows = tabPane.querySelectorAll('.hub-row');
        if (pills.length === 0) return;

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

    // Support URL tab parameter (e.g. ?tab=open or ?tab=close)
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam === 'open') {
        $('#open-tab').tab('show');
    } else if (tabParam === 'close' || tabParam === 'closed') {
        $('#closed-tab').tab('show');
    } else {
        // Initialize all tabs on load
        document.querySelectorAll('.tab-pane').forEach(pane => {
            filterTabByActivePill(pane);
        });
    }

    // ── Live Universal Search across all tabs, divisions, and cases ───────
    const $search = $('#globalHubSearch');
    const $clearBtn = $('#clearGlobalSearch');
    const $clearGroup = $('#clearSearchGroup');

    const tabOrigCounts = {
        'pending': {{ $caseCount }},
        'open': {{ $openCount }},
        'closed': {{ $closedCount }}
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

        // Search in each tab independently
        ['pending', 'open', 'closed'].forEach(tabId => {
            const pane = document.getElementById(tabId);
            if (!pane) return;
            const rows = pane.querySelectorAll('.hub-row');
            let matchCount = 0;

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                    matchCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update badge count for that tab
            const tabLinkId = tabId === 'closed' ? 'closed-tab' : `${tabId}-tab`;
            $(`#${tabLinkId} .tab-count-badge`).text(`(${matchCount})`);
        });
    }

    $search.on('input keyup paste', performSearch);

    $clearBtn.on('click', function() {
        $search.val('');
        performSearch();
        $search.focus();
    });
});
</script>
@endsection
