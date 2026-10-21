@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

.scrutiny-hub { font-family: 'Inter', sans-serif; background: var(--rd-bg, #f8fafc); min-height: 100vh; color: var(--rd-text1, #0f172a); }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }

.fsh-top-hdr {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.fsh-title-wrap h1 {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.4px;
    color: var(--rd-text1, #0f172a);
    margin: 0 0 2px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.fsh-title-wrap .fsh-sub {
    font-size: 0.84rem;
    color: var(--rd-text3, #64748b);
    font-weight: 500;
}

.btn-initiate-fsh {
    background: var(--rd-accent, #5F7858);
    color: #ffffff !important;
    border: 1px solid var(--rd-accent, #5F7858);
    border-radius: 6px;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 13.5px;
    letter-spacing: 0.4px;
    padding: 6px 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: all 0.15s ease;
    text-decoration: none !important;
}
.btn-initiate-fsh:hover {
    background: #4a5e44;
    border-color: #4a5e44;
    box-shadow: 0 2px 6px rgba(95, 120, 88, 0.25);
    color: #ffffff !important;
}

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
    padding: 4px 0 10px 0;
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
    color: #ffffff !important;
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

.sbh-flow-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}
.sbh-from {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
.sbh-to {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}
</style>

<div class="content-wrapper scrutiny-hub px-3 px-md-4 py-3">

    {{-- Top Header with Top-Right Initiate Button for Division & Finance --}}
    <div class="fsh-top-hdr">
        <div class="fsh-title-wrap">
            <h1>
                <i class="fas fa-random text-primary mr-1"></i> Shifting of Funds
            </h1>
            <div class="fsh-sub">
                Reappropriate project budget allocations between subheads with synchronized Data Reversal audit parity
            </div>
        </div>

        @if(Auth::user() && (Auth::user()->isDivision() || Auth::user()->isFinance()))
        <div>
            <a href="{{ route('fund_shifting.create') }}" class="btn-initiate-fsh" title="Initiate Fund Shifting Request">
                <i class="fas fa-plus-circle"></i> Initiate Fund Shifting Request
            </a>
        </div>
        @endif
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show font-weight-bold shadow-sm mb-3 py-2 px-3" role="alert" style="border-radius: 6px; font-size: 12.5px;">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close py-2" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show font-weight-bold shadow-sm mb-3 py-2 px-3" role="alert" style="border-radius: 6px; font-size: 12.5px;">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close py-2" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Main Hub Card --}}
    <div class="card card-clean mb-4 shadow-sm p-3 p-md-3">

        {{-- Tabs & Live Search Bar Header --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap pb-2 border-bottom" style="gap: 10px;">
            <ul class="nav nav-tabs-clean m-0 border-0" role="tablist" id="hubMainTabs">
                <li class="nav-item">
                    <a class="hub-tab-link {{ $tab === 'pending' || empty($tab) ? 'active' : '' }}" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                        <i class="fas fa-clock text-warning"></i> Pending Action <span class="tab-count-badge">({{ $caseCount }})</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="hub-tab-link {{ $tab === 'open' ? 'active' : '' }}" id="open-tab" data-toggle="tab" href="#open" role="tab">
                        <i class="fas fa-folder-open text-primary"></i> Open <span class="tab-count-badge">({{ $openCount }})</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="hub-tab-link {{ $tab === 'closed' ? 'active' : '' }}" id="closed-tab" data-toggle="tab" href="#closed" role="tab">
                        <i class="fas fa-check-circle text-success"></i> Close / Implemented <span class="tab-count-badge">({{ $closedCount }})</span>
                    </a>
                </li>
            </ul>

            {{-- Inline Live Search Bar --}}
            <div class="search-box-inline my-0" style="width: 260px;">
                <div class="input-group input-group-sm" style="border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; background: #ffffff;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-0 text-muted px-2 py-1">
                            <i class="fas fa-search text-secondary" style="font-size: 11px;"></i>
                        </span>
                    </div>
                    <input type="text" id="globalHubSearch" class="form-control border-0 py-1 font-weight-500" style="font-size: 12.5px; box-shadow: none; height: 30px;" placeholder="Search ID, project, subhead..." autocomplete="off">
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
            <div class="tab-pane fade {{ $tab === 'pending' || empty($tab) ? 'show active' : '' }}" id="pending" role="tabpanel">
                @php
                    $pendingGroups = $pending->groupBy(fn($c) => (int)($c->fsh_unt_id ?? 0));
                    $firstPendingDivId = $pendingGroups->keys()->first();
                @endphp

                {{-- Division Filter Bar for Command / Finance or Multi-Division View --}}
                @if($pendingGroups->count() > 0 && ($isCommandOrFinance || $pendingGroups->count() > 1))
                    <div class="div-filter-bar">
                        <button type="button" class="div-pill active" data-div="all">
                            <i class="fas fa-th-large text-secondary" style="font-size: 11px;"></i> All Divisions
                            <span class="div-badge">{{ $pending->count() }}</span>
                        </button>
                        @foreach($pendingGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? "Division #$uId");
                            @endphp
                            <button type="button" class="div-pill" data-div="{{ $uId }}">
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
                                <th style="width: 75px; text-align: center;"><i class="fas fa-eye mr-1"></i> View</th>
                                <th>Case / Project & Subhead Reappropriation</th>
                                <th style="width: 120px;">Date</th>
                                <th style="width: 140px; text-align: right;">Amount</th>
                                <th style="width: 170px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pending as $idx => $c)
                            @php
                                $cDiv = (int)($c->fsh_unt_id ?? 0);
                                $divName = $unitNameMap[$cDiv] ?? ($c->unit?->unt_namesh ?? $c->unit?->unt_name ?? 'Division');
                                $statusUpper = strtoupper($c->fsh_status ?: 'DRAFT');
                                $statusIcon = match($statusUpper) {
                                    'IMPLEMENTED' => 'fa-check-double text-success',
                                    'APPROVED' => 'fa-check-circle text-primary',
                                    'FORWARDED', 'UNDER SCRUTINY', 'IN PROCESS' => 'fa-hourglass-half text-warning',
                                    'REJECTED', 'CANCELLED' => 'fa-ban text-danger',
                                    default => 'fa-pencil-alt text-secondary'
                                };
                            @endphp
                            <tr class="hub-row" data-id="{{ $c->fsh_id }}" data-div="{{ $cDiv }}">
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route('fund_shifting.show', $c->fsh_id) }}" class="btn-view-compact" title="View Fund Shift Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <a href="{{ route('fund_shifting.show', $c->fsh_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case #FS-{{ $c->fsh_rev_id ?: $c->fsh_id }}">
                                            #FS-{{ $c->fsh_rev_id ?: $c->fsh_id }} &bull; {{ $c->project?->prj_code ?? 'PRJ' }} - {{ $c->project?->prj_title ?? 'Project' }}
                                        </a>
                                        @if($divName)
                                            <span class="badge badge-light border text-secondary font-weight-bold" style="font-size: 10px; padding: 2px 5px;">
                                                <i class="fas fa-building mr-0.5"></i> {{ $divName }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Subhead Flow --}}
                                    <div class="d-flex align-items-center flex-wrap my-1" style="gap: 5px;">
                                        <span class="sbh-flow-pill sbh-from" title="Source Subhead (Reduction)">
                                            <i class="fas fa-minus-circle mr-0.5"></i> {{ $c->fsh_from_sbh_name }}
                                        </span>
                                        <i class="fas fa-arrow-right text-muted" style="font-size: 10px;"></i>
                                        <span class="sbh-flow-pill sbh-to" title="Target Subhead (Addition)">
                                            <i class="fas fa-plus-circle mr-0.5"></i> {{ $c->fsh_to_sbh_name }}
                                        </span>
                                    </div>

                                    <div class="text-ref d-flex align-items-center flex-wrap" style="gap: 8px;">
                                        <span>Ref: #FS-{{ $c->fsh_rev_id ?: $c->fsh_id }}</span>
                                        <span>&bull; Head: {{ $c->head?->hed_name ?? 'HQ Head' }}</span>
                                        <span>&bull; By: {{ $c->fsh_created_name ?: ($c->creator?->acc_name ?? 'Division') }}</span>
                                    </div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                    {{ $c->created_at ? $c->created_at->format('d M, Y') : ($c->fsh_date ?? '-') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($c->fsh_approved_amount ?: $c->fsh_proposed_amount) }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center" style="gap: 2px;">
                                        <span class="status-pill rajdhani">
                                            <i class="fas {{ $statusIcon }}"></i> {{ $statusUpper }}
                                        </span>
                                        @php
                                            $holder = $c->fsh_current_holder ?: $c->fsh_current_stage;
                                        @endphp
                                        @if($holder)
                                            <span class="text-muted small font-weight-bold" style="font-size: 10px; line-height: 1.1;">
                                                <i class="fas fa-map-marker-alt text-secondary mr-0.5"></i> {{ $holder }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted rajdhani" style="opacity: 0.7; font-size: 13px;">
                                        <i class="fas fa-check-double fa-2x mb-2 d-block text-primary" style="opacity: 0.4;"></i>
                                        NO PENDING FUND SHIFTING CASES IN YOUR QUEUE
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. OPEN CASES TAB --}}
            <div class="tab-pane fade {{ $tab === 'open' ? 'show active' : '' }}" id="open" role="tabpanel">
                @php
                    $openGroups = $open->groupBy(fn($c) => (int)($c->fsh_unt_id ?? 0));
                    $firstOpenDivId = $openGroups->keys()->first();
                @endphp

                {{-- Division Filter Bar for Command / Finance or Multi-Division View --}}
                @if($openGroups->count() > 0 && ($isCommandOrFinance || $openGroups->count() > 1))
                    <div class="div-filter-bar">
                        <button type="button" class="div-pill active" data-div="all">
                            <i class="fas fa-th-large text-secondary" style="font-size: 11px;"></i> All Divisions
                            <span class="div-badge">{{ $open->count() }}</span>
                        </button>
                        @foreach($openGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? "Division #$uId");
                            @endphp
                            <button type="button" class="div-pill" data-div="{{ $uId }}">
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
                                <th style="width: 75px; text-align: center;"><i class="fas fa-eye mr-1"></i> View</th>
                                <th>Case / Project & Subhead Reappropriation</th>
                                <th style="width: 120px;">Date</th>
                                <th style="width: 140px; text-align: right;">Amount</th>
                                <th style="width: 170px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($open as $idx => $c)
                            @php
                                $cDiv = (int)($c->fsh_unt_id ?? 0);
                                $divName = $unitNameMap[$cDiv] ?? ($c->unit?->unt_namesh ?? $c->unit?->unt_name ?? 'Division');
                                $statusUpper = strtoupper($c->fsh_status ?: 'IN PROCESS');
                            @endphp
                            <tr class="hub-row" data-id="{{ $c->fsh_id }}" data-div="{{ $cDiv }}">
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route('fund_shifting.show', $c->fsh_id) }}" class="btn-view-compact" title="View Fund Shift Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <a href="{{ route('fund_shifting.show', $c->fsh_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case #FS-{{ $c->fsh_rev_id ?: $c->fsh_id }}">
                                            #FS-{{ $c->fsh_rev_id ?: $c->fsh_id }} &bull; {{ $c->project?->prj_code ?? 'PRJ' }} - {{ $c->project?->prj_title ?? 'Project' }}
                                        </a>
                                        @if($divName)
                                            <span class="badge badge-light border text-secondary font-weight-bold" style="font-size: 10px; padding: 2px 5px;">
                                                <i class="fas fa-building mr-0.5"></i> {{ $divName }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Subhead Flow --}}
                                    <div class="d-flex align-items-center flex-wrap my-1" style="gap: 5px;">
                                        <span class="sbh-flow-pill sbh-from" title="Source Subhead (Reduction)">
                                            <i class="fas fa-minus-circle mr-0.5"></i> {{ $c->fsh_from_sbh_name }}
                                        </span>
                                        <i class="fas fa-arrow-right text-muted" style="font-size: 10px;"></i>
                                        <span class="sbh-flow-pill sbh-to" title="Target Subhead (Addition)">
                                            <i class="fas fa-plus-circle mr-0.5"></i> {{ $c->fsh_to_sbh_name }}
                                        </span>
                                    </div>

                                    <div class="text-ref d-flex align-items-center flex-wrap" style="gap: 8px;">
                                        <span>Ref: #FS-{{ $c->fsh_rev_id ?: $c->fsh_id }}</span>
                                        <span>&bull; Head: {{ $c->head?->hed_name ?? 'HQ Head' }}</span>
                                        <span>&bull; By: {{ $c->fsh_created_name ?: ($c->creator?->acc_name ?? 'Division') }}</span>
                                    </div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                    {{ $c->created_at ? $c->created_at->format('d M, Y') : ($c->fsh_date ?? '-') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($c->fsh_approved_amount ?: $c->fsh_proposed_amount) }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center" style="gap: 2px;">
                                        <span class="status-pill rajdhani">
                                            <i class="fas fa-hourglass-half text-info"></i> {{ $statusUpper }}
                                        </span>
                                        @php
                                            $holder = $c->fsh_current_holder ?: $c->fsh_current_stage;
                                        @endphp
                                        @if($holder)
                                            <span class="text-muted small font-weight-bold" style="font-size: 10px; line-height: 1.1;">
                                                <i class="fas fa-map-marker-alt text-secondary mr-0.5"></i> {{ $holder }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
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

            {{-- 3. CLOSED / IMPLEMENTED TAB --}}
            <div class="tab-pane fade {{ $tab === 'closed' ? 'show active' : '' }}" id="closed" role="tabpanel">
                @php
                    $closedGroups = $closed->groupBy(fn($c) => (int)($c->fsh_unt_id ?? 0));
                    $firstClosedDivId = $closedGroups->keys()->first();
                @endphp

                {{-- Division Filter Bar for Command / Finance or Multi-Division View --}}
                @if($closedGroups->count() > 0 && ($isCommandOrFinance || $closedGroups->count() > 1))
                    <div class="div-filter-bar">
                        <button type="button" class="div-pill active" data-div="all">
                            <i class="fas fa-th-large text-secondary" style="font-size: 11px;"></i> All Divisions
                            <span class="div-badge">{{ $closed->count() }}</span>
                        </button>
                        @foreach($closedGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? "Division #$uId");
                            @endphp
                            <button type="button" class="div-pill" data-div="{{ $uId }}">
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
                                <th style="width: 75px; text-align: center;"><i class="fas fa-eye mr-1"></i> View</th>
                                <th>Case / Project & Subhead Reappropriation</th>
                                <th style="width: 120px;">Date</th>
                                <th style="width: 140px; text-align: right;">Amount</th>
                                <th style="width: 170px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($closed as $idx => $c)
                            @php
                                $cDiv = (int)($c->fsh_unt_id ?? 0);
                                $divName = $unitNameMap[$cDiv] ?? ($c->unit?->unt_namesh ?? $c->unit?->unt_name ?? 'Division');
                                $statusUpper = strtoupper($c->fsh_status ?: 'CLOSED');
                                $isImplemented = $statusUpper === 'IMPLEMENTED';
                            @endphp
                            <tr class="hub-row" data-id="{{ $c->fsh_id }}" data-div="{{ $cDiv }}">
                                <td class="text-center" style="white-space: nowrap;">
                                    <a href="{{ route('fund_shifting.show', $c->fsh_id) }}" class="btn-view-compact" title="View Fund Shift Case">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                        <a href="{{ route('fund_shifting.show', $c->fsh_id) }}" class="text-dark font-weight-bold" style="font-size: 13px; text-decoration: none;" title="Open Case #FS-{{ $c->fsh_rev_id ?: $c->fsh_id }}">
                                            #FS-{{ $c->fsh_rev_id ?: $c->fsh_id }} &bull; {{ $c->project?->prj_code ?? 'PRJ' }} - {{ $c->project?->prj_title ?? 'Project' }}
                                        </a>
                                        @if($divName)
                                            <span class="badge badge-light border text-secondary font-weight-bold" style="font-size: 10px; padding: 2px 5px;">
                                                <i class="fas fa-building mr-0.5"></i> {{ $divName }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Subhead Flow --}}
                                    <div class="d-flex align-items-center flex-wrap my-1" style="gap: 5px;">
                                        <span class="sbh-flow-pill sbh-from" title="Source Subhead (Reduction)">
                                            <i class="fas fa-minus-circle mr-0.5"></i> {{ $c->fsh_from_sbh_name }}
                                        </span>
                                        <i class="fas fa-arrow-right text-muted" style="font-size: 10px;"></i>
                                        <span class="sbh-flow-pill sbh-to" title="Target Subhead (Addition)">
                                            <i class="fas fa-plus-circle mr-0.5"></i> {{ $c->fsh_to_sbh_name }}
                                        </span>
                                    </div>

                                    <div class="text-ref d-flex align-items-center flex-wrap" style="gap: 8px;">
                                        <span>Ref: #FS-{{ $c->fsh_rev_id ?: $c->fsh_id }}</span>
                                        <span>&bull; Head: {{ $c->head?->hed_name ?? 'HQ Head' }}</span>
                                        @if($c->fsh_implemented_at)
                                            <span>&bull; Implemented: {{ $c->fsh_implemented_at->format('d M, Y') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 11.5px;">
                                    {{ $c->created_at ? $c->created_at->format('d M, Y') : ($c->fsh_date ?? '-') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($c->fsh_approved_amount ?: $c->fsh_proposed_amount) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani" style="{{ $isImplemented ? 'border-color: #86efac; color: #166534; background: #f0fdf4;' : 'border-color: #fca5a5; color: #991b1b; background: #fef2f2;' }}">
                                        <i class="fas {{ $isImplemented ? 'fa-check-double text-success' : 'fa-ban text-danger' }}"></i> {{ $statusUpper }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="text-muted rajdhani" style="opacity: 0.7; font-size: 13px;">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block text-success" style="opacity: 0.4;"></i>
                                        NO CLOSED / IMPLEMENTED CASES
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
        if (pills.length === 0) {
            rows.forEach(r => r.style.display = '');
            return;
        }

        let activePill = tabPane.querySelector('.div-pill.active');
        if (!activePill && pills.length > 0) {
            activePill = pills[0];
            activePill.classList.add('active');
        }

        const activeDiv = activePill ? activePill.getAttribute('data-div') : 'all';
        rows.forEach(row => {
            if (activeDiv === 'all' || activeDiv === null || row.getAttribute('data-div') === activeDiv) {
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
            if (divId === 'all' || row.getAttribute('data-div') === divId) {
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

    if ($search.length) {
        $search.on('input', function() {
            const query = $(this).val().toLowerCase().trim();

            if (query.length > 0) {
                $clearGroup.show();
                // Temporarily disable division filter restriction during active search
                $('.hub-row').each(function() {
                    const rowText = $(this).text().toLowerCase();
                    const matches = rowText.includes(query);
                    $(this).toggle(matches);
                });
            } else {
                $clearGroup.hide();
                // Reapply active division pill filter for current active tab
                const activePane = document.querySelector('.tab-pane.active');
                if (activePane) filterTabByActivePill(activePane);
            }
        });

        $clearBtn.on('click', function() {
            $search.val('').trigger('input');
        });
    }
});
</script>
@endsection
