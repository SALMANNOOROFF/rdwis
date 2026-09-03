@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');

.scrutiny-hub { font-family: 'Inter', sans-serif; background: var(--rd-bg); min-height: 100vh; color: var(--rd-text1); }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }

/* Division Pills */
.div-filter-bar { padding: 4px 0 14px 0; }
.div-pill { background: #ffffff; border: 1.5px solid var(--rd-border2, #cbd5e1); border-radius: 8px; padding: 6px 14px; color: var(--rd-text2, #475569); font-weight: 700; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px; font-size: 12.5px; }
.div-pill i { color: var(--rd-text3, #64748b); transition: color 0.2s ease; font-size: 11px; }
.div-pill.active { background: var(--rd-accent-soft, rgba(95, 120, 88, 0.12)); border-color: var(--rd-accent, #5F7858); color: var(--rd-accent, #5F7858); box-shadow: 0 2px 8px rgba(95, 120, 88, 0.18); }
.div-pill.active i { color: var(--rd-accent, #5F7858); }
.div-pill:hover:not(.active) { background: var(--rd-surface2, #f8fafc); color: var(--rd-text1, #0f172a); border-color: var(--rd-accent, #5F7858); }
.div-pill:hover:not(.active) i { color: var(--rd-accent, #5F7858); }
.div-badge { background: var(--rd-accent, #5F7858); color: #ffffff; font-size: 10px; padding: 2px 7px; border-radius: 10px; font-weight: bold; min-width: 18px; text-align: center; transition: all 0.2s ease; }
.div-pill:not(.active) .div-badge { background: var(--rd-text3, #64748b); opacity: 0.85; }
.div-pill.active .div-badge { background: var(--rd-accent, #5F7858); }

/* Tabs Logic */
.hub-tabs { border-bottom: 1px solid var(--rd-border); margin-bottom: 16px; }
.hub-tab-link { padding: 12px 24px; color: var(--rd-text3); font-weight: 600; font-size: 13px; text-decoration: none !important; border-bottom: 2px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: transparent; border-top: none; border-left: none; border-right: none; }
.hub-tab-link:hover { color: var(--rd-accent); }
.hub-tab-link.active { color: var(--rd-accent); border-bottom-color: var(--rd-accent); font-weight: 700; }

/* Table Styling */
.hub-table { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
.hub-table th { font-family: 'Rajdhani', sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 1.2px; color: var(--rd-text3); padding: 10px 16px; font-weight: 700; border: none !important; }
.hub-row { background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s; }
.hub-row:hover { background: #f8fafc; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
.hub-row td { padding: 12px 16px; border-top: 1px solid var(--rd-border) !important; border-bottom: 1px solid var(--rd-border) !important; vertical-align: middle; }
.hub-row td:first-child { border-left: 1px solid var(--rd-border) !important; border-radius: 8px 0 0 8px; }
.hub-row td:last-child { border-right: 1px solid var(--rd-border) !important; border-radius: 0 8px 8px 0; }

/* Type & Status Badges */
.type-badge { width: 28px; height: 26px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; color: #1e293b; background: #f1f5f9; border: 1px solid #cbd5e1; text-transform: uppercase; }
.status-pill { background: #f1f5f9; color: #334155; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; gap: 5px; }

.text-amount { font-family: 'Rajdhani', sans-serif; font-size: 16px; font-weight: 700; color: #0f172a; }
.text-ref { font-size: 11px; color: var(--rd-text3); font-weight: 500; }
.nav-arrow { width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--rd-accent); background: var(--rd-accent-soft); border: 1px solid var(--rd-border2); transition: all 0.2s; text-decoration: none !important; }
.nav-arrow:hover { background: var(--rd-accent); color: #fff; transform: scale(1.08); }

/* Animation */
.fade-up { animation: fadeUp 0.3s ease-out forwards; opacity: 0; transform: translateY(8px); }
@keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

/* Corner Action Taken Slide-out Widget */
.action-taken-corner-widget { position: relative; padding-bottom: 2px; }
.corner-arrow-trigger-btn { width: 32px; height: 32px; border-radius: 6px; background: #ffffff; border: 1px solid var(--rd-border2); color: var(--rd-accent); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.25s ease; outline: none !important; }
.corner-arrow-trigger-btn:hover { background: var(--rd-accent); color: #ffffff; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25); }
.corner-arrow-trigger-btn i { font-size: 11px; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.action-taken-corner-widget.is-open .corner-arrow-trigger-btn i { transform: rotate(180deg); }

.action-taken-sliding-drawer { max-width: 0; opacity: 0; overflow: hidden; white-space: nowrap; transform: translateX(10px); transition: max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease, transform 0.3s ease, margin 0.25s ease; margin-right: 0; }
.action-taken-corner-widget.is-open .action-taken-sliding-drawer { max-width: 220px; opacity: 1; transform: translateX(0); margin-right: 8px; }

.corner-action-taken-btn { background: #ffffff; border: 1px solid var(--rd-accent); color: var(--rd-accent); font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 13px; letter-spacing: 0.5px; padding: 4px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1); transition: all 0.2s ease; outline: none !important; }
.corner-action-taken-btn:hover { background: var(--rd-accent); color: #ffffff; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); }
.corner-action-taken-btn.active-tab { background: var(--rd-accent) !important; color: #ffffff !important; border-color: var(--rd-accent) !important; box-shadow: 0 2px 10px rgba(37, 99, 235, 0.3) !important; }
.corner-action-taken-btn.active-tab i { color: #ffffff !important; }
</style>

<div class="content-wrapper scrutiny-hub">

    {{-- Financial Pulse Summary (Simplified Text View) --}}
    @if(isset($finSummary) && !in_array($area ?? '', ['proc', 'prc'], true))
    <div class="px-4 mt-3">
        <div class="mb-3 p-3 rounded d-flex align-items-center justify-content-between" style="background: #ffffff; border: 1px solid var(--rd-border); box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
            <div class="d-flex align-items-center gap-4 rajdhani">
                <div class="mr-4"><i class="fas fa-university text-primary mr-2"></i> <span class="text-muted small">PORTFOLIO RECEIVED:</span> <span class="text-dark font-weight-bold ml-1">{{ number_format($finSummary['received']) }}</span></div>
                <div class="mr-4"><i class="fas fa-file-invoice-dollar text-danger mr-2"></i> <span class="text-muted small">TOTAL EXPENDITURE:</span> <span class="text-dark font-weight-bold ml-1">{{ number_format($finSummary['expenditure']) }}</span></div>
                <div class="mr-4"><i class="fas fa-balance-scale text-primary mr-2"></i> <span class="text-muted small">NET BALANCE:</span> <span class="text-dark font-weight-bold ml-1">{{ number_format($finSummary['balance']) }}</span></div>
            </div>
            <div class="rajdhani px-4 py-1 rounded" style="background: rgba(22, 163, 74, 0.08); border: 1px solid rgba(22, 163, 74, 0.2);">
                <span class="text-success small font-weight-bold">TOTAL AVAILABLE:</span> 
                <span class="text-success font-weight-bold ml-2" style="font-size: 16px;">Rs. {{ number_format($finSummary['available']) }}</span>
            </div>
        </div>
    </div>
    @endif

    <div class="p-4 pt-3">
        {{-- Main Hub Tabs Header with Extreme Right Corner Slideout --}}
        <div class="d-flex align-items-center justify-content-between hub-tabs-header-wrapper" style="border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 16px;">
            <ul class="nav nav-tabs hub-tabs m-0 border-0" role="tablist" id="hubMainTabs" style="margin-bottom: 0 !important; border-bottom: none !important;">
                <li class="nav-item">
                    <a class="hub-tab-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                        <i class="fas fa-clock"></i> Pending Action ({{ $caseCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="hub-tab-link" id="open-tab" data-toggle="tab" href="#open" role="tab">
                        <i class="fas fa-folder-open"></i> Open ({{ $openCount }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="hub-tab-link" id="closed-tab" data-toggle="tab" href="#closed" role="tab">
                        <i class="fas fa-check-circle"></i> Close ({{ $closedCount }})
                    </a>
                </li>
                {{-- Hidden tab anchor for Action Taken so Bootstrap tabs switch smoothly --}}
                <li class="nav-item d-none">
                    <a class="hub-tab-link" id="action-taken-tab" data-toggle="tab" href="#action-taken" role="tab"></a>
                </li>
            </ul>

            {{-- Extreme Right Corner Slide-Out Action Taken Box --}}
            <div class="action-taken-corner-widget d-flex align-items-center" id="cornerActionTakenWidget">
                <div class="action-taken-sliding-drawer" id="actionTakenSlidingDrawer">
                    <button type="button" id="btnCornerActionTaken" class="corner-action-taken-btn">
                        <i class="fas fa-history mr-1 text-info"></i>
                        <span>Action Taken ({{ $actionTakenCount ?? 0 }})</span>
                    </button>
                </div>
                <button type="button" id="btnCornerArrowToggle" class="corner-arrow-trigger-btn" title="Toggle Action Taken">
                    <i class="fas fa-chevron-left" id="cornerArrowIcon"></i>
                </button>
            </div>
        </div>

        <div class="tab-content">
            {{-- 1. PENDING ACTION TAB --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                @php
                    $pendingGroups = $pending->groupBy(fn($p) => (int)($p->pcs_unt_id ?? 0));
                    $firstPendingDivId = $pendingGroups->keys()->first();
                @endphp

                @if($pendingGroups->count() > 0)
                    <div class="div-filter-bar d-flex align-items-center gap-2 flex-wrap">
                        @foreach($pendingGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? "Division #$uId");
                            @endphp
                            <button type="button" class="div-pill {{ (string)$uId === (string)$firstPendingDivId ? 'active' : '' }}" data-div="{{ $uId }}">
                                <i class="fas fa-building"></i> {{ $uName }}
                                <span class="div-badge">{{ $groupCases->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th style="width: 60px;">Type</th>
                                <th>Title / Description</th>
                                <th style="width: 150px;">Date</th>
                                <th style="width: 180px; text-align: right;">Est. Amount</th>
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
                            <tr class="hub-row fade-up" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center">
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="nav-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="type-badge shadow-sm">{{ strtoupper(substr($p->pcs_type ?? 'PS', 0, 2)) }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="text-dark font-weight-bold" style="font-size: 14px; letter-spacing: 0.3px;">{{ $p->pcs_title }}</div>
                                    </div>
                                    <div class="text-ref">Ref: {{ $p->pcs_type }}-{{ $p->pcs_id }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 12px;">
                                    {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->display_price) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani">
                                        <i class="fas {{ $statusIcon }}"></i> {{ strtoupper($p->current_stage_display ?? $p->pcs_status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted rajdhani" style="opacity: 0.6;">
                                        <i class="fas fa-check-double fa-3x mb-3 d-block text-primary" style="opacity: 0.4;"></i>
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
                    <div class="div-filter-bar d-flex align-items-center gap-2 flex-wrap">
                        @foreach($openGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? "Division #$uId");
                            @endphp
                            <button type="button" class="div-pill {{ (string)$uId === (string)$firstOpenDivId ? 'active' : '' }}" data-div="{{ $uId }}">
                                <i class="fas fa-building"></i> {{ $uName }}
                                <span class="div-badge">{{ $groupCases->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th style="width: 60px;">Type</th>
                                <th>Title / Description</th>
                                <th style="width: 150px;">Date</th>
                                <th style="width: 180px; text-align: right;">Amount</th>
                                <th style="width: 160px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($open as $idx => $p)
                            @php
                                $pDiv = (int)($p->pcs_unt_id ?? 0);
                                $isShown = (string)$pDiv === (string)$firstOpenDivId;
                            @endphp
                            <tr class="hub-row fade-up" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center">
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="nav-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="type-badge shadow-sm">{{ strtoupper(substr($p->pcs_type ?? 'PT', 0, 2)) }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="text-dark font-weight-bold" style="font-size: 14px; letter-spacing: 0.3px;">{{ $p->pcs_title }}</div>
                                    </div>
                                    <div class="text-ref">Ref: {{ $p->pcs_type }}-{{ $p->pcs_id }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 12px;">
                                    {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->display_price) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani">
                                        <i class="fas fa-hourglass-half"></i> {{ strtoupper($p->current_stage_display ?? $p->pcs_status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted rajdhani small italic">No open cases.</td>
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
                    <div class="div-filter-bar d-flex align-items-center gap-2 flex-wrap">
                        @foreach($closedGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? "Division #$uId");
                            @endphp
                            <button type="button" class="div-pill {{ (string)$uId === (string)$firstClosedDivId ? 'active' : '' }}" data-div="{{ $uId }}">
                                <i class="fas fa-building"></i> {{ $uName }}
                                <span class="div-badge">{{ $groupCases->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th style="width: 60px;">Type</th>
                                <th>Title / Description</th>
                                <th style="width: 150px;">Date</th>
                                <th style="width: 180px; text-align: right;">Amount</th>
                                <th style="width: 160px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($closed as $idx => $p)
                            @php
                                $pDiv = (int)($p->pcs_unt_id ?? 0);
                                $isShown = (string)$pDiv === (string)$firstClosedDivId;
                            @endphp
                            <tr class="hub-row fade-up" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center">
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="nav-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="type-badge shadow-sm">{{ strtoupper(substr($p->pcs_type ?? 'PS', 0, 2)) }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="text-dark font-weight-bold" style="font-size: 14px; letter-spacing: 0.3px;">{{ $p->pcs_title }}</div>
                                    </div>
                                    <div class="text-ref">Ref: {{ $p->pcs_type }}-{{ $p->pcs_id }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 12px;">
                                    {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->display_price) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani">
                                        <i class="fas fa-check-circle"></i> {{ strtoupper($p->pcs_status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted rajdhani small italic">No closed cases.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 4. ACTION TAKEN TAB --}}
            <div class="tab-pane fade" id="action-taken" role="tabpanel">
                @php
                    $actionTakenList = $actionTaken ?? collect();
                    $actionTakenGroups = $actionTakenList->groupBy(fn($p) => (int)($p->pcs_unt_id ?? 0));
                    $firstActionDivId = $actionTakenGroups->keys()->first();
                @endphp

                @if($actionTakenGroups->count() > 0)
                    <div class="div-filter-bar d-flex align-items-center gap-2 flex-wrap">
                        @foreach($actionTakenGroups as $uId => $groupCases)
                            @php
                                $uName = $unitNameMap[$uId] ?? ($groupCases->first()->unit?->unt_namesh ?? $groupCases->first()->unit?->unt_name ?? "Division #$uId");
                            @endphp
                            <button type="button" class="div-pill {{ (string)$uId === (string)$firstActionDivId ? 'active' : '' }}" data-div="{{ $uId }}">
                                <i class="fas fa-building"></i> {{ $uName }}
                                <span class="div-badge">{{ $groupCases->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;"></th>
                                <th style="width: 60px;">Type</th>
                                <th>Title / Description</th>
                                <th style="width: 150px;">Date</th>
                                <th style="width: 180px; text-align: right;">Amount</th>
                                <th style="width: 160px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($actionTakenList as $idx => $p)
                            @php
                                $pDiv = (int)($p->pcs_unt_id ?? 0);
                                $isShown = (string)$pDiv === (string)$firstActionDivId;
                            @endphp
                            <tr class="hub-row fade-up" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center">
                                    <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="nav-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </td>
                                <td>
                                    <div class="type-badge shadow-sm">{{ strtoupper(substr($p->pcs_type ?? 'PS', 0, 2)) }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="text-dark font-weight-bold" style="font-size: 14px; letter-spacing: 0.3px;">{{ $p->pcs_title }}</div>
                                    </div>
                                    <div class="text-ref">Ref: {{ $p->pcs_type }}-{{ $p->pcs_id }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 12px;">
                                    {{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->display_price) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani">
                                        <i class="fas fa-check-circle"></i> {{ strtoupper($p->current_stage_display ?? $p->pcs_status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted rajdhani small italic">No action taken cases yet.</td>
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

    // Extreme Right Corner Slide-Out Widget Logic
    const cornerWidget = document.getElementById('cornerActionTakenWidget');
    const cornerArrowBtn = document.getElementById('btnCornerArrowToggle');
    const cornerActionTakenBtn = document.getElementById('btnCornerActionTaken');
    const actionTakenTabLink = document.getElementById('action-taken-tab');
    const hubMainTabs = document.querySelectorAll('#hubMainTabs .hub-tab-link:not(#action-taken-tab)');

    if (cornerArrowBtn && cornerWidget) {
        cornerArrowBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            cornerWidget.classList.toggle('is-open');
        });
    }

    if (cornerActionTakenBtn && actionTakenTabLink) {
        cornerActionTakenBtn.addEventListener('click', function() {
            $(actionTakenTabLink).tab('show');
            cornerActionTakenBtn.classList.add('active-tab');
            hubMainTabs.forEach(t => t.classList.remove('active'));
            const actionPane = document.getElementById('action-taken');
            if (actionPane) filterTabByActivePill(actionPane);
        });
    }

    hubMainTabs.forEach(t => {
        t.addEventListener('click', function() {
            if (cornerActionTakenBtn) cornerActionTakenBtn.classList.remove('active-tab');
        });
    });

    document.addEventListener('click', function(e) {
        if (cornerWidget && cornerWidget.classList.contains('is-open') && !cornerWidget.contains(e.target)) {
            cornerWidget.classList.remove('is-open');
        }
    });
});
</script>
@endsection
