@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');
.hub-page { font-family: 'Inter', sans-serif; background: var(--rd-bg) !important; min-height: 100vh; }
.hub-header { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }
.metric-card { background: var(--rd-surface); border: 1px solid var(--rd-border); border-radius: 8px; transition: transform 0.2s; }
.metric-card:hover { transform: translateY(-3px); border-color: var(--rd-border2); box-shadow: var(--rd-shadow-md); }
.metric-label { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: var(--rd-text3); }
.metric-value { font-family: 'Rajdhani', sans-serif; font-size: 26px; font-weight: 700; color: var(--rd-text1); line-height: 1; margin-top: 5px; }
.hub-tab-btn { font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.8px; border-radius: 30px !important; padding: 10px 24px !important; transition: all 0.2s; margin-right: 10px; border: 1px solid var(--rd-border2) !important; background: var(--rd-surface) !important; color: var(--rd-text3) !important; }
.hub-tab-btn.active { background: var(--rd-primary-600) !important; color: #fff !important; border-color: var(--rd-primary-600) !important; box-shadow: 0 4px 15px rgba(95,120,88,0.25); }
.dg-case-table thead th { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: var(--rd-text3); background: var(--rd-surface3) !important; border: none !important; padding: 14px; }
.dg-case-table td { padding: 14px; color: var(--rd-text1); vertical-align: middle; border-top: 1px solid var(--rd-border); border-bottom: none; }
.dg-case-table tr:hover { background: var(--rd-neutral-50); }
.case-title { font-weight: 600; color: var(--rd-text1); font-size: 14px; }
.case-value { font-family: 'Rajdhani', sans-serif; font-size: 16px; font-weight: 700; color: var(--rd-primary-600); }
.status-badge { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 12px; border-radius: 6px; }

.pulse-red {
    display: inline-block;
    width: 6px;
    height: 6px;
    background: #ef4444;
    border-radius: 50%;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: pulse-red 1.5s infinite;
}
@keyframes pulse-red {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
</style>

<div class="content-wrapper hub-page">
    <div class="content-header py-4 bg-transparent mb-2">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="hub-header">
                        <span class="badge badge-primary px-3 py-1 mb-2" style="font-size: 10px; letter-spacing: 1px;">DIVISION OPERATIONS</span>
                        <h1 class="m-0 font-weight-bold" style="font-size: 2.2rem; color: var(--rd-text1);">PC Initiation Hub</h1>
                        <p class="text-muted mb-0" style="font-size: 14px; opacity: 0.7;">Central specialized portal for creating and tracking procurement workflows.</p>
                    </div>
                </div>
                <div class="col-md-5 text-right">
                    <a href="{{ route('purchase.select') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-lg hub-header" style="font-size: 14px; border: 2px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-plus-circle mr-2"></i> CREATE NEW PURCHASE CASE
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @php
                $loggedUser = auth()->user();
                $isLeader = $loggedUser && ((method_exists($loggedUser, 'isMdDdgDg') && $loggedUser->isMdDdgDg()) || (method_exists($loggedUser, 'isDlog') && $loggedUser->isDlog()));
                $userArea = strtolower(trim((string)($loggedUser->acc_untarea ?? '')));
                $isDivision = $loggedUser && ((method_exists($loggedUser, 'isDivision') && $loggedUser->isDivision()) || in_array($userArea, ['prj', 'rdwprj'], true));
                $isFinance = $loggedUser && ((method_exists($loggedUser, 'isFinance') && $loggedUser->isFinance()) || in_array($userArea, ['fin', 'finance', 'dfinance'], true));
                $canCancelRole = !$isLeader && ($isDivision || $isFinance);
            @endphp
            
            {{-- Main Hub Table --}}
            <div class="card bg-transparent border-0">
                <div class="card-header bg-transparent border-0 p-0 mb-3 d-flex flex-wrap justify-content-between align-items-center">
                    <ul class="nav nav-pills mb-2 mb-md-0" id="hubTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn active" data-toggle="tab" href="#action-req">
                                <i class="fas fa-folder-open mr-2"></i> ACTION REQUIRED <span class="tab-count-badge">({{ $actionReqCases->count() }})</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn" data-toggle="tab" href="#initiated">
                                <i class="fas fa-stream mr-2"></i> OPEN <span class="tab-count-badge">({{ $initiatedCases->count() }})</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn" data-toggle="tab" href="#completed">
                                <i class="fas fa-history mr-2"></i> CLOSE <span class="tab-count-badge">({{ $completedCases->count() }})</span>
                            </a>
                        </li>
                    </ul>

                    {{-- Compact Inline Live Search Bar --}}
                    <div class="search-box-inline" style="min-width: 260px; max-width: 340px;">
                        <div class="input-group shadow-sm" style="border-radius: 20px; overflow: hidden; border: 1.5px solid var(--rd-border, #cbd5e1); background: #ffffff;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white border-0 text-muted pl-3 pr-2 py-1">
                                    <i class="fas fa-search text-primary" style="font-size: 13px;"></i>
                                </span>
                            </div>
                            <input type="text" id="globalHubSearch" class="form-control border-0 py-1 font-weight-500" style="font-size: 13px; box-shadow: none;" placeholder="Search ID, name, project..." autocomplete="off">
                            <div class="input-group-append" id="clearSearchGroup" style="display: none;">
                                <button class="btn btn-white border-0 text-muted px-2 py-1" type="button" id="clearGlobalSearch" title="Clear Search">
                                    <i class="fas fa-times-circle text-danger" style="font-size: 12px;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @php
                        $loggedUser = Auth::user();
                        $loggedArea = strtolower(trim((string)($loggedUser->acc_untarea ?? '')));
                        $isFinanceUser = in_array($loggedArea, ['fin', 'finance', 'dfinance'], true)
                            || (method_exists($loggedUser, 'isFinance') && $loggedUser->isFinance())
                            || (method_exists($loggedUser, 'isSuperAdmin') && $loggedUser->isSuperAdmin())
                            || (method_exists($loggedUser, 'isMdDdgDg') && $loggedUser->isMdDdgDg());
                    @endphp
                    <div class="tab-content">
                        
                        {{-- TAB 1: ACTION REQUIRED --}}
                        <div class="tab-pane fade show active" id="action-req">
                            <div class="metric-card overflow-hidden">
                                @if($actionReqCases->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table dg-case-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-4">Reference</th>
                                                    <th>Title / Project Context</th>
                                                    <th class="text-right">Est. Volume</th>
                                                    <th class="text-center">Current Status</th>
                                                    <th class="text-right pr-4">Commands</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($actionReqCases as $p)
                                                @php 
                                                    $isOld = \Carbon\Carbon::parse($p->pcs_date)->diffInDays() > 2;
                                                @endphp
                                                <tr data-id="{{ $p->pcs_id }}">
                                                    <td class="pl-4"><span class="badge badge-dark text-muted px-2 py-1" style="font-size: 9px; border: 1px solid var(--rd-border);">PC-{{ $p->pcs_id }}</span></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($isOld)
                                                                <span class="mr-2 pulse-red" title="Pending for > 48 hours"></span>
                                                            @endif
                                                            <div class="case-title">
                                                                <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="rd-entity-title-link font-weight-bold" title="Open Case: {{ $p->pcs_title }}">
                                                                    {{ Str::limit($p->pcs_title, 50) }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="text-muted small d-flex align-items-center flex-wrap gap-1 mt-1">
                                                            <span><i class="fas fa-project-diagram mr-1"></i> {{ $p->project->prj_code ?? 'General Ops' }}</span>
                                                            <span class="badge badge-light border ml-2 text-dark font-weight-bold" style="font-size: 10px; background: #f8fafc;"><i class="fas fa-layer-group text-primary mr-1"></i>{{ $p->subhead_display }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-right"><span class="case-value">PKR {{ number_format((float) $p->display_price) }}</span></td>
                                                    <td class="text-center">
                                                        <span class="badge status-badge bg-warning-soft text-warning" style="background: rgba(243,156,18,0.1); border: 1px solid rgba(243,156,18,0.2);">
                                                            <i class="fas fa-pen-nib mr-1"></i> {{ $p->pcs_status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right pr-4">
                                                        @php
                                                            $caseStatusLower = strtolower(trim((string)($p->pcs_status ?? '')));
                                                            $isDraftOrPending = in_array($caseStatusLower, ['draft', 'returned', 'action required'], true) || str_contains($caseStatusLower, 'draft') || str_contains($caseStatusLower, 'returned');
                                                        @endphp
                                                        @if(($canCancelRole ?? false) && $isDraftOrPending)
                                                        <button type="button" class="btn btn-outline-danger btn-sm rounded-lg px-2 font-weight-bold hub-header mr-1 btn-cancel-purchase-case" data-id="{{ $p->pcs_id }}" style="font-size: 11px;">
                                                            <i class="fas fa-ban mr-1"></i> CANCEL
                                                        </button>
                                                        @endif
                                                        <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn btn-warning btn-sm rounded-lg px-3 font-weight-bold hub-header" style="font-size: 11px;">
                                                            <i class="fas fa-external-link-alt mr-1"></i> OPEN & RELEASE
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5" style="background: var(--rd-neutral-50);">
                                        <div class="display-4 text-muted mb-3" style="opacity: 0.2;"><i class="fas fa-clipboard-check"></i></div>
                                        <h6 class="text-muted font-weight-bold">Queue Empty</h6>
                                        <p class="text-muted small">No cases currently require your action.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB 2: OPEN --}}
                        <div class="tab-pane fade" id="initiated">
                            <div class="metric-card overflow-hidden">
                                @if($initiatedCases->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table dg-case-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-4">Reference</th>
                                                    <th>Title / Context</th>
                                                    <th class="text-right">Case Value</th>
                                                    <th class="text-center">Workflow Location</th>
                                                    @if($isFinance || $isDivision)
                                                    <th class="text-right pr-4">MTSS Dispatch</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($initiatedCases as $p)
                                                @php
                                                    $s = $p->pcs_status;
                                                    $b = 'info'; $i = 'hourglass-half';
                                                    $latest = $p->latestDecision;
                                                    $isApproved = strtolower(trim((string)$s)) === 'approved';
                                                    $appDate = $p->approved_date;
                                                    $isSentMtss = $p->isSentToMtss();
                                                @endphp
                                                <tr data-id="{{ $p->pcs_id }}">
                                                    <td class="pl-4"><span class="badge badge-dark text-muted px-2 py-1" style="font-size: 9px; border: 1px solid var(--rd-border);">PC-{{ $p->pcs_id }}</span></td>
                                                    <td>
                                                        <div class="case-title">
                                                            <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="rd-entity-title-link font-weight-bold" title="Open Case: {{ $p->pcs_title }}">
                                                                {{ Str::limit($p->pcs_title, 50) }}
                                                            </a>
                                                        </div>
                                                        <div class="text-muted small d-flex align-items-center flex-wrap gap-1 mt-1">
                                                            <span><i class="fas fa-project-diagram mr-1"></i> {{ $p->project->prj_code ?? 'HQ Procurement' }}</span>
                                                            <span class="badge badge-light border ml-2 text-dark font-weight-bold" style="font-size: 10px; background: #f8fafc;"><i class="fas fa-layer-group text-primary mr-1"></i>{{ $p->subhead_display }}</span>
                                                            @if($isApproved)
                                                                <span class="badge ml-2 font-weight-bold" style="background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.3); font-size: 10px; padding: 2px 6px; border-radius: 4px;" title="Approved Date">
                                                                    <i class="fas fa-calendar-check mr-1 text-success"></i> Approved: {{ $appDate ? $appDate->format('d M, Y') : 'Approved' }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-right"><span class="case-value">PKR {{ number_format((float) $p->display_price) }}</span></td>
                                                    <td class="text-center">
                                                        @php
                                                            $targetDest = $latest?->pdec_to_status ?: ($p->current_stage_display ?: 'Division (Initiator)');
                                                            $statusBadgeColor = match(strtolower(trim($s))) {
                                                                'approved' => 'success',
                                                                'returned' => 'danger',
                                                                'draft'    => 'secondary',
                                                                default    => 'primary'
                                                            };
                                                        @endphp
                                                        <div class="d-flex flex-column align-items-center" style="gap: 3px;">
                                                            <div>
                                                                <span class="badge badge-{{ $statusBadgeColor }} px-2 py-0.5 font-weight-bold" style="font-size: 10px; letter-spacing: 0.3px;">
                                                                    {{ $s }}
                                                                </span>
                                                            </div>
                                                            @if($isApproved && $appDate)
                                                                <div class="text-success font-weight-bold rajdhani" style="font-size: 11px;">
                                                                    <i class="fas fa-check-circle mr-1 text-success"></i> {{ $appDate->format('d M, Y') }}
                                                                </div>
                                                            @else
                                                                <span class="badge status-badge" style="background: rgba(var(--rd-{{$b}}-rgb), 0.12); color: var(--rd-text-{{$b}}); border: 1px solid rgba(var(--rd-{{$b}}-rgb), 0.25); font-size: 11px;">
                                                                    <i class="fas fa-{{$i}} mr-1"></i> {{ $targetDest }}
                                                                </span>
                                                            @endif
                                                            @if($latest && !$isApproved)
                                                                <div class="text-xs font-weight-bold text-muted" style="font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; font-size: 10.5px;">
                                                                    {{ strtoupper($latest->pdec_action) }}ed BY {{ $latest->account->acc_name }}
                                                                    @if(!empty($latest->pdec_to_status) && !in_array($latest->pdec_action, ['approve', 'reject']))
                                                                        &rarr; <span class="text-dark">{{ $latest->pdec_to_status }}</span>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-right pr-4" style="white-space: nowrap;">
                                                        @if($isApproved)
                                                            @if($isFinance)
                                                                <button type="button" 
                                                                        class="btn btn-xs btn-mtss-toggle font-weight-bold px-2 py-1 mr-1 {{ $isSentMtss ? 'btn-success' : 'btn-outline-secondary' }}" 
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
                                                                    <span class="badge font-weight-bold px-2 py-1 mr-1" style="background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.3); font-size: 11px; border-radius: 4px;">
                                                                        <i class="fas fa-check-circle mr-1 text-success"></i> Sent to MTSS
                                                                    </span>
                                                                @else
                                                                    <span class="badge font-weight-bold px-2 py-1 mr-1" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-size: 11px; border-radius: 4px;">
                                                                        <i class="fas fa-clock mr-1 text-secondary"></i> Not Sent
                                                                    </span>
                                                                @endif
                                                            @endif
                                                        @endif
                                                        <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn btn-outline-primary btn-sm rounded-lg px-3 hub-header" style="font-size: 11px;">
                                                            <i class="fas fa-search-plus mr-1"></i> VIEW TRAIL
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <h6 class="text-muted">No active cases moving through HQ.</h6>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB 3: CLOSE --}}
                        <div class="tab-pane fade" id="completed">
                            <div class="metric-card overflow-hidden">
                                @if($completedCases->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table dg-case-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-4">Reference</th>
                                                    <th>Title / Context</th>
                                                    <th class="text-right">Final Value</th>
                                                    <th class="text-center">Outcome</th>
                                                    <th class="text-right pr-4">Archive / MTSS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($completedCases as $p)
                                                @php
                                                    $s = $p->pcs_status;
                                                    $b = ($s == 'Approved') ? 'success' : 'danger';
                                                    $i = ($s == 'Approved') ? 'check-double' : 'times-circle';
                                                    $isApproved = strtolower(trim((string)$s)) === 'approved';
                                                    $appDate = $p->approved_date;
                                                    $isSentMtss = $p->isSentToMtss();
                                                @endphp
                                                <tr data-id="{{ $p->pcs_id }}">
                                                    <td class="pl-4"><span class="badge badge-dark text-muted px-2 py-1" style="font-size: 9px; border: 1px solid var(--rd-border);">PC-{{ $p->pcs_id }}</span></td>
                                                    <td>
                                                        <div class="case-title">
                                                            <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="rd-entity-title-link font-weight-bold" title="Open Case: {{ $p->pcs_title }}">
                                                                {{ Str::limit($p->pcs_title, 50) }}
                                                            </a>
                                                        </div>
                                                        <div class="text-muted small d-flex align-items-center flex-wrap gap-1 mt-1">
                                                            <span><i class="fas fa-project-diagram mr-1"></i> {{ $p->project->prj_code ?? 'Project' }}</span>
                                                            <span class="badge badge-light border ml-2 text-dark font-weight-bold" style="font-size: 10px; background: #f8fafc;"><i class="fas fa-layer-group text-primary mr-1"></i>{{ $p->subhead_display }}</span>
                                                            @if($appDate)
                                                                <span class="badge ml-2 font-weight-bold" style="background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.3); font-size: 10px; padding: 2px 6px; border-radius: 4px;" title="Approved Date">
                                                                    <i class="fas fa-calendar-check mr-1 text-success"></i> Approved: {{ $appDate->format('d M, Y') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="text-right"><span class="case-value text-muted">PKR {{ number_format((float) $p->display_price) }}</span></td>
                                                    <td class="text-center">
                                                        <span class="badge status-badge" style="background: rgba(var(--rd-{{$b}}-rgb), 0.1); color: var(--rd-text-{{$b}}); border: 1px solid rgba(var(--rd-{{$b}}-rgb), 0.2);">
                                                            <i class="fas fa-{{$i}} mr-1"></i> {{ strtoupper($s) }}
                                                        </span>
                                                        @if($appDate)
                                                            <div class="text-success font-weight-bold rajdhani mt-1" style="font-size: 11px;">
                                                                <i class="fas fa-check-circle mr-1 text-success"></i> {{ $appDate->format('d M, Y') }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-right pr-4" style="white-space: nowrap;">
                                                        @if($isFinance)
                                                            <button type="button" 
                                                                    class="btn btn-xs btn-mtss-toggle font-weight-bold px-2 py-1 mr-1 {{ $isSentMtss ? 'btn-success' : 'btn-outline-secondary' }}" 
                                                                    data-id="{{ $p->pcs_id }}" 
                                                                    data-sent="{{ $isSentMtss ? '1' : '0' }}"
                                                                    style="border-radius: 5px; font-size: 11px; {{ $isSentMtss ? 'background: #16a34a; border-color: #16a34a; color: #ffffff;' : 'background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;' }} transition: all 0.2s;">
                                                                @if($isSentMtss)
                                                                    <i class="fas fa-check-circle mr-1"></i> Sent to MTSS
                                                                @else
                                                                    <i class="fas fa-paper-plane mr-1 text-secondary"></i> Send to MTSS
                                                                @endif
                                                            </button>
                                                        @elseif($isDivision)
                                                            @if($isSentMtss)
                                                                <span class="badge font-weight-bold px-2 py-1 mr-1" style="background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.3); font-size: 11px; border-radius: 4px;">
                                                                    <i class="fas fa-check-circle mr-1 text-success"></i> Sent to MTSS
                                                                </span>
                                                            @else
                                                                <span class="badge font-weight-bold px-2 py-1 mr-1" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; font-size: 11px; border-radius: 4px;">
                                                                    <i class="fas fa-clock mr-1 text-secondary"></i> Not Sent
                                                                </span>
                                                            @endif
                                                        @endif
                                                        <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn btn-link text-muted rajdhani" style="font-size: 11px;">
                                                            VIEW LOG
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <h6 class="text-muted">No completed cases in archive.</h6>
                                    </div>
                                @endif
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // ── Live Universal Search across all tabs and cases ───────
    const $search = $('#globalHubSearch');
    const $clearBtn = $('#clearGlobalSearch');
    const $clearGroup = $('#clearSearchGroup');

    const tabOrigCounts = {
        'action-req': {{ $actionReqCases->count() }},
        'initiated': {{ $initiatedCases->count() }},
        'completed': {{ $completedCases->count() }}
    };

    function performSearch() {
        const query = $search.val().trim().toLowerCase();
        
        if (!query) {
            $clearGroup.hide();

            // Restore all rows in tabs
            $('#action-req tbody tr').show();
            $('#initiated tbody tr').show();
            $('#completed tbody tr').show();

            // Restore tab pill labels
            $('#hubTabs a[href="#action-req"] .tab-count-badge').text(`(${tabOrigCounts['action-req']})`);
            $('#hubTabs a[href="#initiated"] .tab-count-badge').text(`(${tabOrigCounts['initiated']})`);
            $('#hubTabs a[href="#completed"] .tab-count-badge').text(`(${tabOrigCounts['completed']})`);
            return;
        }

        $clearGroup.show();

        let tabMatches = { 'action-req': 0, 'initiated': 0, 'completed': 0 };
        const tabConfigs = [
            { id: 'action-req' },
            { id: 'initiated' },
            { id: 'completed' }
        ];

        tabConfigs.forEach(tab => {
            const $tabPane = $(`#${tab.id}`);
            const $rows = $tabPane.find('tbody tr');

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
        $('#hubTabs a[href="#action-req"] .tab-count-badge').text(`(${tabMatches['action-req']})`);
        $('#hubTabs a[href="#initiated"] .tab-count-badge').text(`(${tabMatches['initiated']})`);
        $('#hubTabs a[href="#completed"] .tab-count-badge').text(`(${tabMatches['completed']})`);

        // If active tab has 0 matches, auto-switch to the first tab that has matches
        const activeTabHref = $('#hubTabs .nav-link.active').attr('href');
        const activeTabId = activeTabHref ? activeTabHref.replace('#', '') : '';
        if (tabMatches[activeTabId] === 0) {
            for (let t of ['action-req', 'initiated', 'completed']) {
                if (tabMatches[t] > 0) {
                    $(`#hubTabs a[href="#${t}"]`).tab('show');
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

    /**
     * JQuery Polling for status updates
     */
    function updateStatuses() {
        $.ajax({
            url: "{{ route('purchase.initiation.statuses') }}",
            method: 'GET',
            success: function(data) {
                $.each(data, function(id, status) {
                    var $row = $('tr[data-id="' + id + '"]');
                    if ($row.length) {
                        var $badge = $row.find('.status-badge');
                        var oldStatus = $badge.text().trim();
                        
                        if (oldStatus !== status) {
                            $badge.text(status);
                            
                            // Update Colors
                            $badge.removeClass('badge-warning badge-primary badge-success badge-danger badge-secondary');
                            
                            var newClass = 'secondary';
                            if(['Draft', 'Returned'].includes(status)) newClass = 'warning';
                            if(['Under Scrutiny', 'With DFinance', 'With MD', 'With DDG', 'With DG'].includes(status)) newClass = 'primary';
                            if(status === 'Approved') newClass = 'success';
                            if(status === 'Rejected') newClass = 'danger';
                            
                            $badge.addClass('badge-' + newClass);
                            
                            // Visual Feedback (Flash)
                            $badge.fadeOut(200).fadeIn(200);
                        }
                    }
                });
            }
        });
    }

    // Every 30 seconds
    setInterval(updateStatuses, 30000);

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

    // ── Send to MTSS Toggle Handler (Finance Only) ──
    $(document).on('click', '.btn-mtss-toggle', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const caseId = $btn.data('id');
        const currentSent = parseInt($btn.data('sent') || 0);
        const targetStatus = currentSent ? 0 : 1;
        const origHtml = $btn.html();

        $btn.prop('disabled', true).css('opacity', '0.6');

        $.ajax({
            url: `{{ url('purchase-cases-new') }}/${caseId}/toggle-mtss`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: targetStatus
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
                    }
                } else {
                    $btn.html(origHtml);
                    alert(res.message || 'Failed to update MTSS status.');
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).css('opacity', '1').html(origHtml);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error updating MTSS status.';
                alert(msg);
            }
        });
    });
});
</script>
@endsection
