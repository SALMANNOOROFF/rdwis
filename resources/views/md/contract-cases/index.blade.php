@extends('welcome')

@section('content')
@php
    $currentPath = request()->path();
    if (str_starts_with($currentPath, 'dg')) {
        $role = 'DG';
        $roleTitle = $pageTitle ?? 'Director General (DG) Portal';
        $routePrefix = 'dg';
        $authoritySubtitle = 'Final Executive Approval & Ratification Queue';
    } elseif (str_starts_with($currentPath, 'ddg')) {
        $role = 'DDG';
        $roleTitle = $pageTitle ?? 'Deputy Director General (DDG) Portal';
        $routePrefix = 'ddg';
        $authoritySubtitle = 'High-level operational review and executive endorsement queue';
    } else {
        $role = 'MD';
        $roleTitle = $pageTitle ?? 'Managing Director (MD) Portal';
        $routePrefix = 'md';
        $authoritySubtitle = 'Executive oversight and institutional alignment review queue';
    }
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

.exec-page {
    font-family: 'Outfit', 'Inter', sans-serif;
    background: var(--rd-bg) !important;
    min-height: 100vh;
    color: var(--rd-text1);
    padding-bottom: 3rem;
}

.kpi-summary-card {
    background: #FFFFFF;
    border: 1.5px solid var(--rd-border);
    border-radius: 12px;
    padding: 1rem 1.4rem;
    box-shadow: 0 2px 8px rgba(41, 40, 36, 0.04);
}
.kpi-summary-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--rd-text3);
}
.kpi-summary-value {
    font-size: 1.45rem;
    font-weight: 800;
    color: var(--rd-text1);
    line-height: 1.2;
    margin-top: 2px;
}

/* Custom Tab Buttons */
.hub-tab-btn {
    font-weight: 700;
    font-size: 0.85rem;
    letter-spacing: 0.3px;
    border-radius: 30px !important;
    padding: 8px 20px !important;
    transition: all 0.2s ease;
    margin-right: 8px;
    border: 1.5px solid var(--rd-border) !important;
    background: #FFFFFF !important;
    color: var(--rd-text2) !important;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none !important;
}
.hub-tab-btn:hover {
    background: var(--rd-neutral-100) !important;
    color: var(--rd-text1) !important;
}
.hub-tab-btn.active {
    background: var(--rd-primary-600) !important;
    color: #FFFFFF !important;
    border-color: var(--rd-primary-600) !important;
    box-shadow: 0 4px 14px rgba(95, 120, 88, 0.25);
}
.hub-tab-btn.active .badge-tab-count {
    background: rgba(255, 255, 255, 0.25) !important;
    color: #FFFFFF !important;
}

.badge-tab-count {
    font-size: 0.75rem;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 12px;
    background: var(--rd-neutral-200);
    color: var(--rd-neutral-800);
}

.clean-data-table thead th {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: var(--rd-text3);
    background: var(--rd-neutral-50) !important;
    border: none !important;
    padding: 14px 16px;
}
.clean-data-table td {
    padding: 14px 16px;
    color: var(--rd-text1);
    vertical-align: middle;
    border-top: 1px solid var(--rd-neutral-200);
}
.clean-data-table tr:hover {
    background: var(--rd-neutral-50);
}
</style>

<div class="content-wrapper exec-page pt-4">
    <div class="container-fluid px-4">
        
        {{-- Top Header Row --}}
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <span class="badge badge-primary px-3 py-1 mb-2 font-weight-bold" style="border-radius: 20px; font-size: 10px; letter-spacing: 0.8px;">
                    EXECUTIVE APPROVAL AUTHORITY &bull; {{ $role }}
                </span>
                <h1 class="font-weight-bold text-dark m-0" style="font-size: 2.1rem; letter-spacing: -0.5px;">{{ $roleTitle }}</h1>
                <p class="text-muted mb-0 font-weight-500" style="font-size: 0.92rem;">{{ $authoritySubtitle }}</p>
            </div>
            
            <div class="col-md-5 text-right">
                <div class="d-inline-block kpi-summary-card text-left mr-2" style="border-left: 4px solid var(--rd-primary-600);">
                    <div class="kpi-summary-label">Approval Queue Value</div>
                    <div class="kpi-summary-value text-primary">PKR {{ number_format($actionReqCases->sum('ctc_newsalary')) }}</div>
                </div>
                <div class="d-inline-block kpi-summary-card text-left" style="border-left: 4px solid #EF4444;">
                    <div class="kpi-summary-label">Pending Approval</div>
                    <div class="kpi-summary-value text-danger">{{ $actionReqCases->count() }} Cases</div>
                </div>
            </div>
        </div>

        {{-- KPI Summary Row --}}
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="kpi-summary-card" style="border-left: 4px solid #EF4444;">
                    <div class="kpi-summary-label">Pending {{ $role }} Action</div>
                    <div class="kpi-summary-value text-danger">{{ $actionReqCases->count() }} <small class="text-muted" style="font-size: 0.8rem;">cases</small></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="kpi-summary-card" style="border-left: 4px solid #F59E0B;">
                    <div class="kpi-summary-label">Open in Pipeline</div>
                    <div class="kpi-summary-value text-warning">{{ $initiatedCases->count() }} <small class="text-muted" style="font-size: 0.8rem;">cases</small></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="kpi-summary-card" style="border-left: 4px solid #10B981;">
                    <div class="kpi-summary-label">Closed / Fulfilled</div>
                    <div class="kpi-summary-value text-success">{{ $completedCases->count() }} <small class="text-muted" style="font-size: 0.8rem;">cases</small></div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="kpi-summary-card" style="border-left: 4px solid #6366F1;">
                    <div class="kpi-summary-label">Total Cases Log</div>
                    <div class="kpi-summary-value text-primary">{{ $cases->count() }} <small class="text-muted" style="font-size: 0.8rem;">cases</small></div>
                </div>
            </div>
        </div>

        {{-- Filter Tabs Bar --}}
        <div class="d-flex align-items-center flex-wrap mb-3" role="tablist">
            <button type="button" class="hub-tab-btn active" data-tab="tab-pending">
                <i class="fas fa-bolt text-warning"></i> Pending Action
                <span class="badge-tab-count">{{ $actionReqCases->count() }}</span>
            </button>
            <button type="button" class="hub-tab-btn" data-tab="tab-open">
                <i class="fas fa-hourglass-half text-primary"></i> Open / In Pipeline
                <span class="badge-tab-count">{{ $initiatedCases->count() }}</span>
            </button>
            <button type="button" class="hub-tab-btn" data-tab="tab-closed">
                <i class="fas fa-check-circle text-success"></i> Closed / Fulfilled
                <span class="badge-tab-count">{{ $completedCases->count() }}</span>
            </button>
            <button type="button" class="hub-tab-btn" data-tab="tab-all">
                <i class="fas fa-list"></i> All Cases
                <span class="badge-tab-count">{{ $cases->count() }}</span>
            </button>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: PENDING EXECUTIVE ACTION                             --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="hub-tab-panel" id="tab-pending">
            <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden; background: #FFFFFF;">
                <div class="p-3 d-flex justify-content-between align-items-center" style="background: #FFFFFF; border-bottom: 1.5px solid var(--rd-neutral-200);">
                    <h6 class="m-0 text-dark font-weight-bold"><i class="fas fa-user-check mr-2 text-primary"></i> {{ $role }} APPROVAL QUEUE</h6>
                    <span class="badge badge-danger px-3 py-1 font-weight-bold" style="border-radius: 20px;">{{ $actionReqCases->count() }} PENDING</span>
                </div>
                <div class="table-responsive">
                    <table class="table clean-data-table mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4">Ref #</th>
                                <th>Division</th>
                                <th>Candidate / Employee Details</th>
                                <th class="text-right">Project</th>
                                <th class="text-right">Proposed Salary</th>
                                <th class="text-center">Status</th>
                                <th class="text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($actionReqCases as $p)
                            <tr>
                                <td class="pl-4">
                                    <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">
                                        CC-{{ $p->ctc_id }}
                                    </span>
                                </td>
                                <td class="font-weight-bold text-dark">
                                    {{ $p->division_name }}
                                    @if($p->division_short && $p->division_short !== $p->division_name)
                                        <small class="text-muted d-block font-weight-normal">({{ $p->division_short }})</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark font-weight-bold" style="font-size: 0.95rem;">{{ $p->ctc_empnamecomp }}</div>
                                    <div class="text-muted small">
                                        <span class="badge badge-primary mr-1" style="font-size: 9px;">{{ strtoupper($p->ctc_type) }}</span>
                                        <i class="fas fa-user-tag mr-1"></i> {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})
                                    </div>
                                </td>
                                <td class="text-right small text-muted font-weight-bold text-nowrap">{{ $p->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</td>
                                <td class="text-right font-weight-bold text-primary" style="font-size: 1.05rem;">Rs. {{ number_format($p->ctc_newsalary) }}</td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge badge-warning font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 4px;">
                                            <i class="fas fa-user-clock mr-1"></i> Holder: {{ $p->current_stage ?? $role }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-right pr-4">
                                    <a href="{{ route("{$routePrefix}.contract-cases.show", $p->ctc_id) }}" class="btn btn-primary btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                        @if($role === 'DG')
                                            <i class="fas fa-stamp mr-1"></i> REVIEW & APPROVE
                                        @else
                                            <i class="fas fa-check-circle mr-1"></i> REVIEW & ENDORSE
                                        @endif
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-check-circle text-success mb-3" style="font-size: 40px; opacity: 0.4;"></i>
                                    <h6 class="text-muted font-weight-bold">All caught up! No cases currently awaiting {{ $role }} approval.</h6>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: OPEN / IN PIPELINE                                  --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="hub-tab-panel d-none" id="tab-open">
            <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden; background: #FFFFFF;">
                <div class="p-3 d-flex justify-content-between align-items-center" style="background: #FFFFFF; border-bottom: 1.5px solid var(--rd-neutral-200);">
                    <h6 class="m-0 text-dark font-weight-bold"><i class="fas fa-hourglass-half mr-2 text-warning"></i> OPEN CASES IN PIPELINE (FORWARDED AHEAD)</h6>
                    <span class="badge badge-warning px-3 py-1 font-weight-bold" style="border-radius: 20px;">{{ $initiatedCases->count() }} IN PIPELINE</span>
                </div>
                <div class="table-responsive">
                    <table class="table clean-data-table mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4">Ref #</th>
                                <th>Division</th>
                                <th>Candidate Details</th>
                                <th class="text-right">Project</th>
                                <th class="text-right">Proposed Salary</th>
                                <th class="text-center">Current Status & Holder</th>
                                <th class="text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($initiatedCases as $p)
                            <tr>
                                <td class="pl-4">
                                    <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                                        CC-{{ $p->ctc_id }}
                                    </span>
                                </td>
                                <td class="font-weight-bold text-dark">
                                    {{ $p->division_name }}
                                    @if($p->division_short && $p->division_short !== $p->division_name)
                                        <small class="text-muted d-block font-weight-normal">({{ $p->division_short }})</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark font-weight-bold" style="font-size: 0.95rem;">{{ $p->ctc_empnamecomp }}</div>
                                    <div class="text-muted small">
                                        <span class="badge badge-secondary mr-1" style="font-size: 9px;">{{ strtoupper($p->ctc_type) }}</span>
                                        {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})
                                    </div>
                                </td>
                                <td class="text-right small text-muted font-weight-bold text-nowrap">{{ $p->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</td>
                                <td class="text-right font-weight-bold text-dark" style="font-size: 0.95rem;">Rs. {{ number_format($p->ctc_newsalary) }}</td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge badge-info text-white font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 4px; background: #0284c7;">
                                            <i class="fas fa-user-clock mr-1"></i> Holder: {{ strtoupper($p->current_stage) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-right pr-4">
                                    <a href="{{ route("{$routePrefix}.contract-cases.show", $p->ctc_id) }}" class="btn btn-outline-primary btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                        <i class="fas fa-eye mr-1"></i> VIEW TRAIL
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-inbox text-muted mb-3" style="font-size: 40px; opacity: 0.3;"></i>
                                    <h6 class="text-muted font-weight-bold">No open cases currently with other authorities.</h6>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: CLOSED / COMPLETED                                  --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="hub-tab-panel d-none" id="tab-closed">
            <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden; background: #FFFFFF;">
                <div class="p-3 d-flex justify-content-between align-items-center" style="background: #FFFFFF; border-bottom: 1.5px solid var(--rd-neutral-200);">
                    <h6 class="m-0 text-dark font-weight-bold"><i class="fas fa-archive mr-2 text-muted"></i> CLOSED & FULFILLED CASES</h6>
                    <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 20px;">{{ $completedCases->count() }} CLOSED</span>
                </div>
                <div class="table-responsive">
                    <table class="table clean-data-table mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4">Ref #</th>
                                <th>Division</th>
                                <th>Candidate Details</th>
                                <th class="text-right">Project</th>
                                <th class="text-center">Final Status & Holder</th>
                                <th class="text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($completedCases as $p)
                            <tr>
                                <td class="pl-4">
                                    <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                                        CC-{{ $p->ctc_id }}
                                    </span>
                                </td>
                                <td class="font-weight-bold text-dark">
                                    {{ $p->division_name }}
                                    @if($p->division_short && $p->division_short !== $p->division_name)
                                        <small class="text-muted d-block font-weight-normal">({{ $p->division_short }})</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark font-weight-bold" style="font-size: 0.95rem;">{{ $p->ctc_empnamecomp }}</div>
                                    <div class="text-muted small">
                                        <span class="badge badge-light border mr-1" style="font-size: 9px;">{{ strtoupper($p->ctc_type) }}</span>
                                        {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})
                                    </div>
                                </td>
                                <td class="text-right small text-muted font-weight-bold text-nowrap">{{ $p->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</td>
                                <td class="text-center">
                                    @php
                                        $stBadge = in_array(strtolower($p->ctc_status), ['fulfilled', 'closed']) ? 'badge-success' : (in_array(strtolower($p->ctc_status), ['rejected', 'not approved', 'cancelled']) ? 'badge-danger' : 'badge-secondary');
                                    @endphp
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge {{ $stBadge }} font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 4px;">
                                            <i class="fas fa-check-double mr-1"></i> Stage: {{ $p->current_stage ?? $p->currentSubstatus->css_stage ?? $p->ctc_status }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-right pr-4">
                                    <a href="{{ route("{$routePrefix}.contract-cases.show", $p->ctc_id) }}" class="btn btn-outline-secondary btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                        <i class="fas fa-eye mr-1"></i> VIEW ARCHIVE
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-folder-open text-muted mb-3" style="font-size: 40px; opacity: 0.3;"></i>
                                    <h6 class="text-muted font-weight-bold">No completed/closed cases found.</h6>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TAB 4: ALL CASES (MASTER LOG)                              --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="hub-tab-panel d-none" id="tab-all">
            <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden; background: #FFFFFF;">
                <div class="p-3 d-flex justify-content-between align-items-center" style="background: #FFFFFF; border-bottom: 1.5px solid var(--rd-neutral-200);">
                    <h6 class="m-0 text-dark font-weight-bold"><i class="fas fa-list mr-2 text-primary"></i> MASTER CONTRACT CASES LOG</h6>
                    <span class="badge badge-dark px-3 py-1 font-weight-bold" style="border-radius: 20px;">{{ $cases->count() }} TOTAL</span>
                </div>
                <div class="table-responsive">
                    <table class="table clean-data-table mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4">Ref #</th>
                                <th>Division</th>
                                <th>Candidate Details</th>
                                <th class="text-right">Project</th>
                                <th class="text-right">Salary</th>
                                <th class="text-center">Current Status & Holder</th>
                                <th class="text-right pr-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cases as $p)
                            <tr>
                                <td class="pl-4">
                                    <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                                        CC-{{ $p->ctc_id }}
                                    </span>
                                </td>
                                <td class="font-weight-bold text-dark">
                                    {{ $p->division_name }}
                                    @if($p->division_short && $p->division_short !== $p->division_name)
                                        <small class="text-muted d-block font-weight-normal">({{ $p->division_short }})</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark font-weight-bold" style="font-size: 0.95rem;">{{ $p->ctc_empnamecomp }}</div>
                                    <div class="text-muted small">
                                        <span class="badge badge-light border mr-1" style="font-size: 9px;">{{ strtoupper($p->ctc_type) }}</span>
                                        {{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})
                                    </div>
                                </td>
                                <td class="text-right small text-muted font-weight-bold text-nowrap">{{ $p->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</td>
                                <td class="text-right font-weight-bold text-primary" style="font-size: 0.95rem;">Rs. {{ number_format($p->ctc_newsalary) }}</td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge badge-info font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 4px;">
                                            <i class="fas fa-user-clock mr-1"></i> Holder: {{ $p->current_stage ?? $p->currentSubstatus->css_stage ?? 'Division' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-right pr-4">
                                    <a href="{{ route("{$routePrefix}.contract-cases.show", $p->ctc_id) }}" class="btn btn-outline-primary btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                        <i class="fas fa-eye mr-1"></i> VIEW
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <h6 class="text-muted font-weight-bold">No contract cases found.</h6>
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
    $('.hub-tab-btn').click(function(e) {
        e.preventDefault();
        const targetTab = $(this).data('tab');

        $('.hub-tab-btn').removeClass('active');
        $(this).addClass('active');

        $('.hub-tab-panel').addClass('d-none');
        $('#' + targetTab).removeClass('d-none');
    });
});
</script>
@endpush
@endsection
