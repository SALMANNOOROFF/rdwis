@extends('welcome')

@section('content')
@php
    $routePrefix = 'finance';
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');

.scrutiny-hub { font-family: 'Inter', sans-serif; background: var(--rd-bg, #f8fafc); min-height: 100vh; color: var(--rd-text1, #0f172a); }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }

/* Division Pills */
.div-filter-bar { padding: 4px 0 14px 0; }
.div-pill { background: #ffffff; border: 1.5px solid var(--rd-border2, #cbd5e1); border-radius: 8px; padding: 6px 14px; color: var(--rd-text2, #475569); font-weight: 700; cursor: pointer; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 8px; font-size: 12.5px; outline: none !important; }
.div-pill i { color: var(--rd-text3, #64748b); transition: color 0.2s ease; font-size: 11px; }
.div-pill.active { background: var(--rd-accent-soft, rgba(95, 120, 88, 0.12)); border-color: var(--rd-accent, #5F7858); color: var(--rd-accent, #5F7858); box-shadow: 0 2px 8px rgba(95, 120, 88, 0.18); }
.div-pill.active i { color: var(--rd-accent, #5F7858); }
.div-pill:hover:not(.active) { background: var(--rd-surface2, #f8fafc); color: var(--rd-text1, #0f172a); border-color: var(--rd-accent, #5F7858); }
.div-pill:hover:not(.active) i { color: var(--rd-accent, #5F7858); }
.div-badge { background: var(--rd-text3, #64748b); color: #ffffff; font-size: 10px; padding: 2px 7px; border-radius: 10px; font-weight: bold; min-width: 18px; text-align: center; transition: all 0.2s ease; opacity: 0.85; }
.div-pill.active .div-badge { background: var(--rd-accent, #5F7858); opacity: 1; }

/* Tabs Logic */
.hub-tabs { border-bottom: 1px solid var(--rd-border, #e2e8f0); margin-bottom: 16px; }
.hub-tab-link { padding: 12px 24px; color: var(--rd-text3, #64748b); font-weight: 600; font-size: 13px; text-decoration: none !important; border-bottom: 2px solid transparent; transition: all 0.2s; display: flex; align-items: center; gap: 8px; background: transparent; border-top: none; border-left: none; border-right: none; }
.hub-tab-link:hover { color: var(--rd-accent, #5F7858); }
.hub-tab-link.active { color: var(--rd-accent, #5F7858); border-bottom-color: var(--rd-accent, #5F7858); font-weight: 700; }

/* Table Styling */
.hub-table { width: 100%; border-collapse: separate; border-spacing: 0 6px; }
.hub-table th { font-family: 'Rajdhani', sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 1.2px; color: var(--rd-text3, #64748b); padding: 10px 16px; font-weight: 700; border: none !important; }
.hub-row { background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s; }
.hub-row:hover { background: #f8fafc; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.06); }
.hub-row td { padding: 12px 16px; border-top: 1px solid var(--rd-border, #e2e8f0) !important; border-bottom: 1px solid var(--rd-border, #e2e8f0) !important; vertical-align: middle; }
.hub-row td:first-child { border-left: 1px solid var(--rd-border, #e2e8f0) !important; border-radius: 8px 0 0 8px; }
.hub-row td:last-child { border-right: 1px solid var(--rd-border, #e2e8f0) !important; border-radius: 0 8px 8px 0; }

/* Type & Status Badges */
.type-badge { width: 28px; height: 26px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; color: #1e293b; background: #f1f5f9; border: 1px solid #cbd5e1; text-transform: uppercase; }
.status-pill { background: #f1f5f9; color: #334155; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; gap: 5px; }

.text-amount { font-family: 'Rajdhani', sans-serif; font-size: 16px; font-weight: 700; color: #0f172a; }
.text-ref { font-size: 11px; color: var(--rd-text3, #64748b); font-weight: 500; }

/* Animation */
.fade-up { animation: fadeUp 0.3s ease-out forwards; opacity: 0; transform: translateY(8px); }
@keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
</style>

<div class="content-wrapper scrutiny-hub">
    <div class="p-4 pt-3">
        
        {{-- Main Hub Tabs Header --}}
        <div class="d-flex align-items-center justify-content-between hub-tabs-header-wrapper" style="border-bottom: 1px solid rgba(255,255,255,0.05); margin-bottom: 16px;">
            <ul class="nav nav-tabs hub-tabs m-0 border-0" role="tablist" id="hubMainTabs" style="margin-bottom: 0 !important; border-bottom: none !important;">
                <li class="nav-item">
                    <a class="hub-tab-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                        <i class="fas fa-clock"></i> Pending Action ({{ $actionReqCases->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="hub-tab-link" id="open-tab" data-toggle="tab" href="#open" role="tab">
                        <i class="fas fa-folder-open"></i> Open ({{ $initiatedCases->count() }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="hub-tab-link" id="closed-tab" data-toggle="tab" href="#closed" role="tab">
                        <i class="fas fa-check-circle"></i> Close ({{ $completedCases->count() }})
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            {{-- 1. PENDING ACTION TAB --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                @php
                    $pendingGroups = $actionReqCases->groupBy(fn($p) => (int)($p->ctc_divisionid ?: ($p->ctc_unt_id ?: 0)));
                    $firstPendingDivId = $pendingGroups->keys()->first();
                @endphp

                @if($pendingGroups->count() > 0)
                    <div class="div-filter-bar d-flex align-items-center gap-2 flex-wrap">
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

                <div class="table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 70px; text-align: center;"><i class="fas fa-eye mr-1"></i> View</th>
                                <th style="width: 60px;">Type</th>
                                <th>Candidate / Employee Details</th>
                                <th style="width: 170px;">Project</th>
                                <th style="width: 130px;">Date</th>
                                <th style="width: 160px; text-align: right;">Proposed Salary</th>
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
                            <tr class="hub-row fade-up" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center">
                                    <a href="{{ route("finance.contract-cases.show", $p->ctc_id) }}" class="btn btn-xs btn-primary font-weight-bold px-2 py-1 shadow-sm" style="border-radius: 4px; font-size: 0.76rem; white-space: nowrap; background-color: var(--rd-accent, #5F7858) !important; border-color: var(--rd-accent, #5F7858) !important;" title="View Case">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                </td>
                                <td>
                                    <div class="type-badge shadow-sm">{{ $typeCode }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="text-dark font-weight-bold" style="font-size: 14px; letter-spacing: 0.3px;">{{ $p->ctc_empnamecomp }}</div>
                                    </div>
                                    <div class="text-ref">Ref: CC-{{ $p->ctc_id }} &bull; {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})</div>
                                </td>
                                <td>
                                    <div class="text-dark font-weight-bold" style="font-size: 13px;">{{ $p->project_code }}</div>
                                    <div class="text-ref text-truncate" style="max-width: 160px;">{{ $p->division_name }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 12px;">
                                    {{ \Carbon\Carbon::parse($p->ctc_date ?? now())->format('d M, Y') }}
                                </td>
                                <td class="text-right">
                                    <div class="text-amount rajdhani">Rs. {{ number_format($p->ctc_newsalary) }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="status-pill rajdhani">
                                        <i class="fas fa-user-clock text-warning"></i> {{ strtoupper($p->current_stage ?? 'Finance') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
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
                    $openGroups = $initiatedCases->groupBy(fn($p) => (int)($p->ctc_divisionid ?: ($p->ctc_unt_id ?: 0)));
                    $firstOpenDivId = $openGroups->keys()->first();
                @endphp

                @if($openGroups->count() > 0)
                    <div class="div-filter-bar d-flex align-items-center gap-2 flex-wrap">
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

                <div class="table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 70px; text-align: center;"><i class="fas fa-eye mr-1"></i> View</th>
                                <th style="width: 60px;">Type</th>
                                <th>Candidate Details</th>
                                <th style="width: 170px;">Project</th>
                                <th style="width: 130px;">Date</th>
                                <th style="width: 160px; text-align: right;">Proposed Salary</th>
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
                            <tr class="hub-row fade-up" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center">
                                    <a href="{{ route("finance.contract-cases.show", $p->ctc_id) }}" class="btn btn-xs btn-primary font-weight-bold px-2 py-1 shadow-sm" style="border-radius: 4px; font-size: 0.76rem; white-space: nowrap; background-color: var(--rd-accent, #5F7858) !important; border-color: var(--rd-accent, #5F7858) !important;" title="View Case">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                </td>
                                <td>
                                    <div class="type-badge shadow-sm">{{ $typeCode }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="text-dark font-weight-bold" style="font-size: 14px; letter-spacing: 0.3px;">{{ $p->ctc_empnamecomp }}</div>
                                    </div>
                                    <div class="text-ref">Ref: CC-{{ $p->ctc_id }} &bull; {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})</div>
                                </td>
                                <td>
                                    <div class="text-dark font-weight-bold" style="font-size: 13px;">{{ $p->project_code }}</div>
                                    <div class="text-ref text-truncate" style="max-width: 160px;">{{ $p->division_name }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 12px;">
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
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted rajdhani" style="opacity: 0.6;">
                                        <i class="fas fa-folder-open fa-3x mb-3 d-block text-primary" style="opacity: 0.4;"></i>
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
                    <div class="div-filter-bar d-flex align-items-center gap-2 flex-wrap">
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

                <div class="table-responsive">
                    <table class="hub-table">
                        <thead>
                            <tr>
                                <th style="width: 70px; text-align: center;"><i class="fas fa-eye mr-1"></i> View</th>
                                <th style="width: 60px;">Type</th>
                                <th>Candidate Details</th>
                                <th style="width: 170px;">Project</th>
                                <th style="width: 130px;">Date</th>
                                <th style="width: 160px; text-align: right;">Final Salary</th>
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
                            <tr class="hub-row fade-up" data-div="{{ $pDiv }}" style="{{ $isShown ? '' : 'display: none;' }}">
                                <td class="text-center">
                                    <a href="{{ route("finance.contract-cases.show", $p->ctc_id) }}" class="btn btn-xs btn-primary font-weight-bold px-2 py-1 shadow-sm" style="border-radius: 4px; font-size: 0.76rem; white-space: nowrap; background-color: var(--rd-accent, #5F7858) !important; border-color: var(--rd-accent, #5F7858) !important;" title="View Case">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                </td>
                                <td>
                                    <div class="type-badge shadow-sm">{{ $typeCode }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="text-dark font-weight-bold" style="font-size: 14px; letter-spacing: 0.3px;">{{ $p->ctc_empnamecomp }}</div>
                                    </div>
                                    <div class="text-ref">Ref: CC-{{ $p->ctc_id }} &bull; {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})</div>
                                </td>
                                <td>
                                    <div class="text-dark font-weight-bold" style="font-size: 13px;">{{ $p->project_code }}</div>
                                    <div class="text-ref text-truncate" style="max-width: 160px;">{{ $p->division_name }}</div>
                                </td>
                                <td class="text-muted small font-weight-bold rajdhani" style="font-size: 12px;">
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
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted rajdhani" style="opacity: 0.6;">
                                        <i class="fas fa-check-circle fa-3x mb-3 d-block text-success" style="opacity: 0.4;"></i>
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
});
</script>
@endpush
@endsection

