@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

.fin-page {
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
    padding: 1.2rem 1.5rem;
    box-shadow: 0 2px 8px rgba(41, 40, 36, 0.04);
}
.kpi-summary-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: var(--rd-text3);
}
.kpi-summary-value {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--rd-text1);
    line-height: 1.2;
    margin-top: 4px;
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

<div class="content-wrapper fin-page pt-4">
    <div class="container-fluid px-4">
        {{-- Header --}}
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <span class="badge badge-success px-3 py-1 mb-2 font-weight-bold" style="border-radius: 20px; font-size: 10px; letter-spacing: 0.8px;">
                    FINANCIAL APPROVAL AUTHORITY
                </span>
                <h1 class="font-weight-bold text-dark m-0" style="font-size: 2.2rem; letter-spacing: -0.5px;">Finance Contract Dashboard</h1>
                <p class="text-muted mb-0 font-weight-500" style="font-size: 0.95rem;">Project-wise financial scrutiny and budget allocation review for contracts.</p>
            </div>
            <div class="col-md-5 text-right">
                <div class="d-inline-block kpi-summary-card text-left mr-2" style="border-left: 4px solid #10B981;">
                    <div class="kpi-summary-label">Salary Impact Volume</div>
                    <div class="kpi-summary-value text-success">PKR {{ number_format($actionReqCases->sum('ctc_newsalary')) }}</div>
                </div>
                <div class="d-inline-block kpi-summary-card text-left">
                    <div class="kpi-summary-label">Awaiting Scrutiny</div>
                    <div class="kpi-summary-value text-dark">{{ $actionReqCases->count() }} Cases</div>
                </div>
            </div>
        </div>

        {{-- Tab Navigation --}}
        <div class="d-flex align-items-center flex-wrap mb-3" role="tablist">
            <button type="button" class="hub-tab-btn active" data-tab="tab-pending">
                <i class="fas fa-bolt text-success"></i> Pending Action
                <span class="badge-tab-count">{{ $actionReqCases->count() }}</span>
            </button>
            <button type="button" class="hub-tab-btn" data-tab="tab-open">
                <i class="fas fa-hourglass-half text-warning"></i> Open / In Pipeline
                <span class="badge-tab-count">{{ $initiatedCases->count() }}</span>
            </button>
            <button type="button" class="hub-tab-btn" data-tab="tab-closed">
                <i class="fas fa-check-circle text-primary"></i> Closed / Fulfilled
                <span class="badge-tab-count">{{ $completedCases->count() }}</span>
            </button>
            <button type="button" class="hub-tab-btn" data-tab="tab-all">
                <i class="fas fa-list text-muted"></i> All Cases
                <span class="badge-tab-count">{{ $cases->count() }}</span>
            </button>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: PENDING FINANCE ACTION                               --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="hub-tab-panel" id="tab-pending">
            <div class="card border shadow-sm" style="border-radius: 12px; overflow: hidden; background: #FFFFFF;">
                <div class="p-3 d-flex justify-content-between align-items-center" style="background: #FFFFFF; border-bottom: 1.5px solid var(--rd-neutral-200);">
                    <h6 class="m-0 text-dark font-weight-bold"><i class="fas fa-file-invoice-dollar mr-2 text-success"></i> BUDGET REVIEW QUEUE (ACTION REQUIRED)</h6>
                    <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 20px;">{{ $actionReqCases->count() }} PENDING</span>
                </div>
                <div class="table-responsive">
                    <table class="table clean-data-table mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4">Ref #</th>
                                <th>Division</th>
                                <th>Contract Case Title</th>
                                <th class="text-right">Project Code</th>
                                <th class="text-right">Proposed Salary</th>
                                <th class="text-center">Current Status & Holder</th>
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
                                        <i class="fas fa-user-check mr-1"></i> {{ $p->ctc_newjobtitle }} &bull; HR Scrutinized
                                    </div>
                                </td>
                                <td class="text-right small text-muted font-weight-bold text-nowrap">{{ $p->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</td>
                                <td class="text-right font-weight-bold text-success" style="font-size: 1.05rem;">Rs. {{ number_format($p->ctc_newsalary) }}</td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge badge-info text-white font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 4px; background: #0284c7;">
                                            <i class="fas fa-user-clock mr-1"></i> Holder: {{ $p->current_stage ?? 'Finance' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-right pr-4">
                                    <a href="{{ route('finance.contract-cases.show', $p->ctc_id) }}" class="btn btn-success btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                        <i class="fas fa-check-circle mr-1"></i> REVIEW & MOVE
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-shield-alt text-muted mb-3" style="font-size: 40px; opacity: 0.3;"></i>
                                    <h6 class="text-muted font-weight-bold">No cases currently with Finance.</h6>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: OPEN / IN PIPELINE                                   --}}
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
                                <th>Candidate Details</th>
                                <th class="text-right">Project Code</th>
                                <th class="text-right">Salary</th>
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
                                <td>
                                    <div class="text-dark font-weight-bold" style="font-size: 0.95rem;">{{ $p->ctc_empnamecomp }}</div>
                                    <div class="text-muted small">{{ $p->ctc_newjobtitle }}</div>
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
                                    <a href="{{ route('finance.contract-cases.show', $p->ctc_id) }}" class="btn btn-outline-primary btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                        <i class="fas fa-eye mr-1"></i> VIEW TRAIL
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <h6 class="text-muted font-weight-bold">No open cases currently with subsequent authorities.</h6>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: CLOSED / COMPLETED                                   --}}
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
                                <th>Candidate Details</th>
                                <th class="text-right">Project Code</th>
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
                                <td>
                                    <div class="text-dark font-weight-bold" style="font-size: 0.95rem;">{{ $p->ctc_empnamecomp }}</div>
                                    <div class="text-muted small">{{ $p->ctc_newjobtitle }}</div>
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
                                    <a href="{{ route('finance.contract-cases.show', $p->ctc_id) }}" class="btn btn-outline-secondary btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                        <i class="fas fa-eye mr-1"></i> VIEW ARCHIVE
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
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
                                <td>
                                    <div class="text-dark font-weight-bold" style="font-size: 0.95rem;">{{ $p->ctc_empnamecomp }}</div>
                                    <div class="text-muted small">{{ $p->ctc_newjobtitle }} ({{ $p->ctc_newgrade }})</div>
                                </td>
                                <td class="text-right small text-muted font-weight-bold text-nowrap">{{ $p->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</td>
                                <td class="text-right font-weight-bold text-success" style="font-size: 0.95rem;">Rs. {{ number_format($p->ctc_newsalary) }}</td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge badge-info font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 4px;">
                                            <i class="fas fa-user-clock mr-1"></i> Holder: {{ $p->current_stage ?? $p->currentSubstatus->css_stage ?? 'Finance' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-right pr-4">
                                    <a href="{{ route('finance.contract-cases.show', $p->ctc_id) }}" class="btn btn-outline-primary btn-sm font-weight-bold" style="border-radius: 6px; font-size: 11px;">
                                        <i class="fas fa-eye mr-1"></i> VIEW
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
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
    $('.hub-tab-btn').click(function() {
        $('.hub-tab-btn').removeClass('active');
        $(this).addClass('active');

        const target = $(this).data('tab');
        $('.hub-tab-panel').addClass('d-none');
        $('#' + target).removeClass('d-none');
    });
});
</script>
@endpush
@endsection
