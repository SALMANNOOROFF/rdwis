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

    {{-- Financial Pulse Summary (Simplified Text View) --}}
    @if(isset($finSummary) && !in_array($area ?? '', ['proc', 'prc'], true))
    <div class="mb-3 px-3 py-2 rounded d-flex align-items-center justify-content-between flex-wrap" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); gap: 10px;">
        <div class="d-flex align-items-center gap-3 flex-wrap rajdhani">
            @if(isset($finSummary['allocation']))
            <div class="mr-3"><i class="fas fa-coins text-warning mr-1"></i> <span class="text-muted small font-weight-bold">ALLOCATED:</span> <span class="text-dark font-weight-bold ml-1" style="font-size: 13.5px;">{{ number_format($finSummary['allocation']) }}</span></div>
            @endif
            <div class="mr-3"><i class="fas fa-university text-primary mr-1"></i> <span class="text-muted small font-weight-bold">PORTFOLIO RECEIVED:</span> <span class="text-dark font-weight-bold ml-1" style="font-size: 13.5px;">{{ number_format($finSummary['received']) }}</span></div>
            <div class="mr-3"><i class="fas fa-file-invoice-dollar text-danger mr-1"></i> <span class="text-muted small font-weight-bold">EXPENDITURE:</span> <span class="text-dark font-weight-bold ml-1" style="font-size: 13.5px;">{{ number_format($finSummary['expenditure']) }}</span></div>
            <div class="mr-3"><i class="fas fa-balance-scale text-info mr-1"></i> <span class="text-muted small font-weight-bold">NET BALANCE:</span> <span class="text-dark font-weight-bold ml-1" style="font-size: 13.5px;">{{ number_format($finSummary['balance']) }}</span></div>
        </div>
        <div class="rajdhani px-3 py-1 rounded" style="background: rgba(22, 163, 74, 0.08); border: 1px solid rgba(22, 163, 74, 0.2);">
            <span class="text-success small font-weight-bold">TOTAL AVAILABLE:</span> 
            <span class="text-success font-weight-bold ml-1" style="font-size: 14.5px;">Rs. {{ number_format($finSummary['available']) }}</span>
        </div>
    </div>
    @endif

    @php
        $loggedUser = auth()->user();
        $isLeader = $loggedUser && ((method_exists($loggedUser, 'isMdDdgDg') && $loggedUser->isMdDdgDg()) || (method_exists($loggedUser, 'isDlog') && $loggedUser->isDlog()));
        $userArea = strtolower(trim((string)($loggedUser->acc_untarea ?? '')));
        $isDivision = $loggedUser && ((method_exists($loggedUser, 'isDivision') && $loggedUser->isDivision()) || in_array($userArea, ['prj', 'rdwprj'], true));
        $isFinance = $loggedUser && ((method_exists($loggedUser, 'isFinance') && $loggedUser->isFinance()) || in_array($userArea, ['fin', 'finance', 'dfinance'], true));
        $canCancelRole = !$isLeader && ($isDivision || $isFinance);
    @endphp

    <div class="card card-clean mb-4 shadow-sm p-3 p-md-3">
        {{-- Main Hub Tabs Header --}}
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
            @php
                $loggedUser = Auth::user();
                $loggedArea = strtolower(trim((string)($loggedUser->acc_untarea ?? '')));
                $isFinanceUser = in_array($loggedArea, ['fin', 'finance', 'dfinance'], true)
                    || (method_exists($loggedUser, 'isFinance') && $loggedUser->isFinance())
                    || (method_exists($loggedUser, 'isSuperAdmin') && $loggedUser->isSuperAdmin())
                    || (method_exists($loggedUser, 'isMdDdgDg') && $loggedUser->isMdDdgDg());
            @endphp

            {{-- 1. PENDING ACTION TAB --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                @php
                    $pendingGroups = $pending->groupBy(fn($p) => (int)($p->pcs_unt_id ?? 0));
                    $firstPendingDivId = $pendingGroups->keys()->first();
                @endphp

                @if($pendingGroups->count() > 0)
                    <div class="div-filter-bar">
                        @foreach($pendingGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? "Division #$uId");
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
                                <th style="width: 130px;">Project</th>
                                <th style="width: 120px;">Date</th>
                                <th style="width: 140px; text-align: right;">Est. Amount</th>
                                <th style="width: 160px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pending as $idx => $p)
                            @php 
                                $statusIcon = match(strtolower($p->pcs_status)) {
                                    'under scrutiny' => 'fa-binoculars',
                                    'with md' => 'fa-user-tie',
                                    'with dg' => 'fa-user-shield',
                                    'with dfinance' => 'fa-file-invoice-dollar',
                                    default => 'fa-hourglass-half'
                                };
                                $pDiv = (int)($p->pcs_unt_id ?? 0);
                                $isShown = (string)$pDiv === (string)$firstPendingDivId;
                            @endphp
                            <tr class="hub-row" data-id="{{ $p->pcs_id }}" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @php
                                        $caseStatusLower = strtolower(trim((string)($p->pcs_status ?? '')));
                                        $isDraftOrPending = in_array($caseStatusLower, ['draft', 'returned', 'action required'], true) || str_contains($caseStatusLower, 'draft') || str_contains($caseStatusLower, 'returned');
                                    @endphp
                                    @if(($canCancelRole ?? false) && $isDraftOrPending)
                                    <button type="button" class="btn btn-xs btn-outline-danger btn-cancel-purchase-case ml-1 font-weight-bold" data-id="{{ $p->pcs_id }}" title="Cancel Case" style="border-radius: 4px; padding: 2px 7px; font-size: 11px;">
                                        <i class="fas fa-ban"></i> Cancel
                                    </button>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case: {{ $p->pcs_title }}">
                                        {{ $p->pcs_title }}
                                    </a>
                                    <div class="text-ref d-flex align-items-center" style="gap: 5px;">
                                        <span>Ref: #{{ $p->pcs_id }}</span>
                                        @if(method_exists($p, 'isCsrf') && $p->isCsrf())
                                            <span class="badge text-white font-weight-bold" style="background: #dc2626 !important; font-size: 9.5px; padding: 1px 4px; border-radius: 3px;">CSRF</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="rajdhani" style="font-size: 12px;">
                                    @php
                                        $pcProject = isset($projectCodes[$p->pcs_id]) ? $projectCodes[$p->pcs_id] : null;
                                    @endphp
                                    @if(!empty($pcProject['code']))
                                        <span class="badge badge-light border font-weight-bold text-dark" style="font-size: 11px; padding: 3px 7px; border-radius: 4px;" title="{{ $pcProject['title'] }}">
                                            <i class="fas fa-project-diagram mr-1 text-primary"></i>{{ $pcProject['code'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                    {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->display_price) }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center" style="gap: 2px;">
                                        <span class="status-pill rajdhani">
                                            <i class="fas {{ $statusIcon }}"></i> {{ strtoupper($p->pcs_status) }}
                                        </span>
                                        @php
                                            $destHolder = $p->latestDecision?->pdec_to_status ?: $p->current_stage_display;
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
                                <td colspan="6" class="text-center py-4">
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

            {{-- 2. OPEN CASES TAB --}}
            <div class="tab-pane fade" id="open" role="tabpanel">
                @php
                    $openGroups = $open->groupBy(fn($p) => (int)($p->pcs_unt_id ?? 0));
                    $firstOpenDivId = $openGroups->keys()->first();
                @endphp

                @if($openGroups->count() > 0)
                    <div class="div-filter-bar">
                        @foreach($openGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? "Division #$uId");
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
                                <th style="width: 130px;">Project</th>
                                <th style="width: 130px;">Date</th>
                                <th style="width: 135px; text-align: right;">Amount</th>
                                <th style="width: 155px; text-align: center;">Status</th>
                                @if($isFinance || $isDivision)
                                <th style="width: 150px; text-align: center;"><i class="fas fa-paper-plane mr-1 text-primary"></i> MTSS Dispatch</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($open as $idx => $p)
                            @php
                                $pDiv = (int)($p->pcs_unt_id ?? 0);
                                $isShown = (string)$pDiv === (string)$firstOpenDivId;
                                $isApproved = strtolower(trim((string)$p->pcs_status)) === 'approved';
                                $appDate = $p->approved_date;
                                $isSentMtss = $p->isSentToMtss();
                            @endphp
                            <tr class="hub-row" data-id="{{ $p->pcs_id }}" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case: {{ $p->pcs_title }}">
                                        {{ $p->pcs_title }}
                                    </a>
                                    <div class="text-ref d-flex align-items-center flex-wrap" style="gap: 5px;">
                                        <span>Ref: #{{ $p->pcs_id }}</span>
                                        @if(method_exists($p, 'isCsrf') && $p->isCsrf())
                                            <span class="badge text-white font-weight-bold" style="background: #dc2626 !important; font-size: 9.5px; padding: 1px 4px; border-radius: 3px;">CSRF</span>
                                        @endif
                                        @if($isApproved)
                                            <span class="badge font-weight-bold" style="background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.3); font-size: 10px; padding: 1.5px 6px; border-radius: 3px;" title="Approved Date">
                                                <i class="fas fa-calendar-check mr-1 text-success"></i> Approved: {{ $appDate ? $appDate->format('d M, Y') : 'Approved' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="rajdhani" style="font-size: 12px;">
                                    @php
                                        $pcProject = isset($projectCodes[$p->pcs_id]) ? $projectCodes[$p->pcs_id] : null;
                                    @endphp
                                    @if(!empty($pcProject['code']))
                                        <span class="badge badge-light border font-weight-bold text-dark" style="font-size: 11px; padding: 3px 7px; border-radius: 4px;" title="{{ $pcProject['title'] }}">
                                            <i class="fas fa-project-diagram mr-1 text-primary"></i>{{ $pcProject['code'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    @if($isApproved)
                                        <div class="text-success font-weight-bold rajdhani" style="font-size: 12.5px; color: #16a34a !important; line-height: 1.2;" title="Case Approved Date">
                                            <i class="fas fa-check-circle mr-1 text-success"></i> {{ $appDate ? $appDate->format('d M, Y') : 'Approved' }}
                                        </div>
                                        <div class="text-muted font-weight-bold" style="font-size: 10px; line-height: 1;">
                                            Init: {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                        </div>
                                    @else
                                        <div class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                            {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->display_price) }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center" style="gap: 2px;">
                                        <span class="status-pill rajdhani {{ $isApproved ? 'border-success text-success' : '' }}">
                                            <i class="fas {{ $isApproved ? 'fa-check-circle text-success' : 'fa-hourglass-half text-info' }}"></i> {{ strtoupper($p->pcs_status) }}
                                        </span>
                                        @php
                                            $openHolder = $p->latestDecision?->pdec_to_status ?: $p->current_stage_display;
                                        @endphp
                                        @if($openHolder && !$isApproved)
                                            <span class="text-muted small font-weight-bold" style="font-size: 10px; line-height: 1.1;">
                                                <i class="fas fa-map-marker-alt text-secondary mr-0.5"></i> {{ $openHolder }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                @if($isFinance || $isDivision)
                                <td class="text-center mtss-cell" style="white-space: nowrap;">
                                    @if($isApproved)
                                        @if($isFinance)
                                            <button type="button" 
                                                    class="btn btn-xs btn-mtss-toggle font-weight-bold px-2 py-1 {{ $isSentMtss ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                    data-id="{{ $p->pcs_id }}" 
                                                    data-sent="{{ $isSentMtss ? '1' : '0' }}"
                                                    style="border-radius: 5px; font-size: 11px; {{ $isSentMtss ? 'background: #16a34a; border-color: #16a34a; color: #ffffff;' : 'background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;' }} transition: all 0.2s;"
                                                    title="{{ $isSentMtss ? 'Click to revert MTSS status' : 'Click to send documents to MTSS' }}">
                                                @if($isSentMtss)
                                                    <i class="fas fa-check-circle mr-1"></i> Sent to MTSS
                                                @else
                                                    <i class="fas fa-paper-plane mr-1 text-secondary"></i> Send to MTSS
                                                @endif
                                            </button>
                                        @elseif($isDivision)
                                            @if($isSentMtss)
                                                <span class="badge font-weight-bold px-2 py-1" style="background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.3); font-size: 11px; border-radius: 4px;">
                                                    <i class="fas fa-check-circle mr-1 text-success"></i> Sent to MTSS
                                                </span>
                                            @else
                                                <span class="badge font-weight-bold px-2 py-1" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-size: 11px; border-radius: 4px;">
                                                    <i class="fas fa-clock mr-1 text-secondary"></i> Not Sent
                                                </span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-muted font-weight-bold" style="font-size: 11px; opacity: 0.35;">—</span>
                                    @endif
                                </td>
                                @endif
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

            {{-- 3. CLOSED CASES TAB --}}
            <div class="tab-pane fade" id="closed" role="tabpanel">
                @php
                    $closedGroups = $closed->groupBy(fn($p) => (int)($p->pcs_unt_id ?? 0));
                    $firstClosedDivId = $closedGroups->keys()->first();
                @endphp

                @if($closedGroups->count() > 0)
                    <div class="div-filter-bar">
                        @foreach($closedGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? "Division #$uId");
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
                                <th>Title / Description</th>
                                <th style="width: 130px;">Project</th>
                                <th style="width: 130px;">Date</th>
                                <th style="width: 135px; text-align: right;">Amount</th>
                                <th style="width: 155px; text-align: center;">Status</th>
                                @if($isFinance || $isDivision)
                                <th style="width: 150px; text-align: center;"><i class="fas fa-paper-plane mr-1 text-primary"></i> MTSS Dispatch</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($closed as $idx => $p)
                            @php
                                $pDiv = (int)($p->pcs_unt_id ?? 0);
                                $isShown = (string)$pDiv === (string)$firstClosedDivId;
                                $appDate = $p->approved_date;
                                $isSentMtss = $p->isSentToMtss();
                            @endphp
                            <tr class="hub-row" data-id="{{ $p->pcs_id }}" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center">
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn-view-compact" title="View Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case: {{ $p->pcs_title }}">
                                        {{ $p->pcs_title }}
                                    </a>
                                    <div class="text-ref d-flex align-items-center flex-wrap" style="gap: 5px;">
                                        <span>Ref: #{{ $p->pcs_id }}</span>
                                        @if(method_exists($p, 'isCsrf') && $p->isCsrf())
                                            <span class="badge text-white font-weight-bold" style="background: #dc2626 !important; font-size: 9.5px; padding: 1px 4px; border-radius: 3px;">CSRF</span>
                                        @endif
                                        @if($appDate)
                                            <span class="badge font-weight-bold" style="background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.3); font-size: 10px; padding: 1.5px 6px; border-radius: 3px;" title="Approved Date">
                                                <i class="fas fa-calendar-check mr-1 text-success"></i> Approved: {{ $appDate->format('d M, Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="rajdhani" style="font-size: 12px;">
                                    @php
                                        $pcProject = isset($projectCodes[$p->pcs_id]) ? $projectCodes[$p->pcs_id] : null;
                                    @endphp
                                    @if(!empty($pcProject['code']))
                                        <span class="badge badge-light border font-weight-bold text-dark" style="font-size: 11px; padding: 3px 7px; border-radius: 4px;" title="{{ $pcProject['title'] }}">
                                            <i class="fas fa-project-diagram mr-1 text-primary"></i>{{ $pcProject['code'] }}
                                        </span>
                                    @else
                                        <span class="text-muted">&mdash;</span>
                                    @endif
                                </td>
                                <td style="white-space: nowrap;">
                                    @if($appDate)
                                        <div class="text-success font-weight-bold rajdhani" style="font-size: 12.5px; color: #16a34a !important; line-height: 1.2;" title="Case Approved Date">
                                            <i class="fas fa-check-circle mr-1 text-success"></i> {{ $appDate->format('d M, Y') }}
                                        </div>
                                        <div class="text-muted font-weight-bold" style="font-size: 10px; line-height: 1;">
                                            Init: {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                        </div>
                                    @else
                                        <div class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                            {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->display_price) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani border-success text-success">
                                        <i class="fas fa-check-circle text-success"></i> {{ strtoupper($p->pcs_status) }}
                                    </span>
                                </td>
                                @if($isFinance || $isDivision)
                                <td class="text-center mtss-cell" style="white-space: nowrap;">
                                    @if($isFinance)
                                        <button type="button" 
                                                class="btn btn-xs btn-mtss-toggle font-weight-bold px-2 py-1 {{ $isSentMtss ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                data-id="{{ $p->pcs_id }}" 
                                                data-sent="{{ $isSentMtss ? '1' : '0' }}"
                                                style="border-radius: 5px; font-size: 11px; {{ $isSentMtss ? 'background: #16a34a; border-color: #16a34a; color: #ffffff;' : 'background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;' }} transition: all 0.2s;"
                                                title="{{ $isSentMtss ? 'Click to revert MTSS status' : 'Click to send documents to MTSS' }}">
                                            @if($isSentMtss)
                                                <i class="fas fa-check-circle mr-1"></i> Sent to MTSS
                                            @else
                                                <i class="fas fa-paper-plane mr-1 text-secondary"></i> Send to MTSS
                                            @endif
                                        </button>
                                    @elseif($isDivision)
                                        @if($isSentMtss)
                                            <span class="badge font-weight-bold px-2 py-1" style="background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.3); font-size: 11px; border-radius: 4px;">
                                                <i class="fas fa-check-circle mr-1 text-success"></i> Sent to MTSS
                                            </span>
                                        @else
                                            <span class="badge font-weight-bold px-2 py-1" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-size: 11px; border-radius: 4px;">
                                                <i class="fas fa-clock mr-1 text-secondary"></i> Not Sent
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                @endif
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted rajdhani" style="opacity: 0.7; font-size: 13px;">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block text-success" style="opacity: 0.4;"></i>
                                        NO CLOSED CASES
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

    // Initialize all tabs on load
    document.querySelectorAll('.tab-pane').forEach(pane => {
        filterTabByActivePill(pane);
    });

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
                                `pc-${caseId}` === query || 
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

    // ── Purchase Case Cancel Handler ──
    $(document).on('click', '.btn-cancel-purchase-case', function(e) {
        e.preventDefault();
        const caseId = $(this).data('id');

        Swal.fire({
            title: `Cancel Purchase Case #PC-${caseId}`,
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
                    url: `{{ url('purchase-cases') }}/${caseId}/cancel`,
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
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to cancel purchase case.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            }
        });
    });

    // ── Send to MTSS Toggle Handler (Finance Only) with Confirmation & 'unsend' Protection ──
    $(document).on('click', '.btn-mtss-toggle', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const caseId = $btn.data('id');
        const currentSent = parseInt($btn.data('sent') || 0);

        function doAjaxToggle(targetStatus, confirmPhrase) {
            const origHtml = $btn.html();
            $btn.prop('disabled', true).css('opacity', '0.6').html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');

            $.ajax({
                url: `{{ url('purchase-cases-new') }}/${caseId}/toggle-mtss`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: targetStatus,
                    confirm_phrase: confirmPhrase || ''
                },
                dataType: 'json',
                success: function(res) {
                    $btn.prop('disabled', false).css('opacity', '1');
                    if (res.success) {
                        if (res.is_sent) {
                            $btn.data('sent', 1);
                            $btn.removeClass('btn-outline-secondary')
                                .addClass('btn-success')
                                .css({
                                    'background': '#16a34a',
                                    'border-color': '#16a34a',
                                    'color': '#ffffff'
                                })
                                .attr('title', 'Click to revert MTSS status')
                                .html('<i class="fas fa-check-circle mr-1"></i> Sent to MTSS');

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Marked as Sent to MTSS',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                            }
                        } else {
                            $btn.data('sent', 0);
                            $btn.removeClass('btn-success')
                                .addClass('btn-outline-secondary')
                                .css({
                                    'background': '#f8fafc',
                                    'border-color': '#cbd5e1',
                                    'color': '#475569'
                                })
                                .attr('title', 'Click to send documents to MTSS')
                                .html('<i class="fas fa-paper-plane mr-1 text-secondary"></i> Send to MTSS');

                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'info',
                                    title: 'MTSS submission reverted to Unsent',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });
                            }
                        }
                    } else {
                        $btn.html(origHtml);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Notice', res.message || 'Failed to update MTSS status.', 'warning');
                        } else {
                            alert(res.message || 'Failed to update MTSS status.');
                        }
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).css('opacity', '1').html(origHtml);
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error updating MTSS status.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', msg, 'error');
                    } else {
                        alert(msg);
                    }
                }
            });
        }

        if (currentSent === 1) {
            // Already sent: require typing "unsend"
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Revert MTSS?',
                    html: '<p class="text-muted small mb-2">Type <strong class="text-danger">unsend</strong> to confirm revert:</p>',
                    icon: 'warning',
                    width: '360px',
                    padding: '1.25rem',
                    input: 'text',
                    inputPlaceholder: "unsend",
                    inputAttributes: {
                        autocapitalize: 'off',
                        autocorrect: 'off',
                        spellcheck: 'false',
                        class: 'form-control text-center font-weight-bold font-monospace'
                    },
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Unsend',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    preConfirm: (inputValue) => {
                        const val = (inputValue || '').trim().toLowerCase();
                        if (val !== 'unsend') {
                            Swal.showValidationMessage("Type 'unsend' to confirm");
                            return false;
                        }
                        return val;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        doAjaxToggle(0, result.value);
                    }
                });
            } else {
                const phrase = prompt("Type 'unsend' to confirm revert:");
                if (phrase && phrase.trim().toLowerCase() === 'unsend') {
                    doAjaxToggle(0, 'unsend');
                } else if (phrase !== null) {
                    alert("Confirmation failed. You must type 'unsend' to revert.");
                }
            }
        } else {
            // Send to MTSS confirmation
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Send to MTSS?',
                    text: 'Mark this purchase case as sent to MTSS?',
                    icon: 'question',
                    width: '360px',
                    padding: '1.25rem',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Send',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        doAjaxToggle(1, '');
                    }
                });
            } else {
                if (confirm("Mark this case as sent to MTSS?")) {
                    doAjaxToggle(1, '');
                }
            }
        }
    });
});
</script>

@endsection
