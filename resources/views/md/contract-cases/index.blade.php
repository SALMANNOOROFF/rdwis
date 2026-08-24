@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');
.fin-page { font-family: 'Inter', sans-serif; background: var(--rd-bg); min-height: 100vh; color: var(--rd-text1); }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }
.metric-card { background: var(--rd-surface); border: 1px solid var(--rd-border); border-radius: 12px; transition: transform 0.2s; }
.metric-label { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: var(--rd-text3); }
.metric-value { font-family: 'Rajdhani', sans-serif; font-size: 26px; font-weight: 700; color: #fff; line-height: 1; margin-top: 5px; }
.dg-case-table thead th { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: var(--rd-text3); background: var(--rd-surface3); border: none !important; padding: 14px; }
.dg-case-table td { padding: 14px; color: var(--rd-text1); vertical-align: middle; border-top: 1px solid rgba(255,255,255,0.08); }
.dg-case-table tr:hover { background: var(--rd-neutral-50); }
</style>

<div class="content-wrapper fin-page pt-4">
    <div class="container-fluid px-4">
        {{-- Header --}}
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <span class="badge px-3 py-1 mb-2 rajdhani" style="font-size: 10px; background: rgba(139, 92, 246, 0.1); color: var(--rd-primary-600); border: 1px solid rgba(139, 92, 246, 0.2);">EXECUTIVE AUTHORITY</span>
                <h1 class="font-weight-bold text-dark rajdhani m-0" style="font-size: 2.2rem;">Managing Director Dashboard</h1>
                <p class="text-muted mb-0 small">Final executive review and approval authority for all contract cases.</p>
            </div>
            <div class="col-md-5 text-right">
                <div class="d-inline-block metric-card p-3 text-left mr-2" style="border-right: 4px solid var(--rd-primary-600);">
                    <div class="metric-label">Approval Queue Value</div>
                    <div class="metric-value" style="color: var(--rd-primary-600);">PKR {{ number_format($actionReqCases->sum('ctc_newsalary')) }}</div>
                </div>
                <div class="d-inline-block metric-card p-3 text-left">
                    <div class="metric-label">Awaiting Final Approval</div>
                    <div class="metric-value">{{ $actionReqCases->count() }}</div>
                </div>
            </div>
        </div>

        {{-- MD Queue --}}
        <div class="metric-card overflow-hidden">
            <div class="p-3 bg-white d-flex justify-content-between align-items-center" style="background: var(--rd-surface3) !important;">
                <h6 class="m-0 rajdhani text-dark font-weight-bold"><i class="fas fa-crown mr-2" style="color: var(--rd-primary-600);"></i> EXECUTIVE APPROVAL QUEUE</h6>
                <div class="text-muted small rajdhani">TOTAL PENDING CASES: <span class="text-dark">{{ $actionReqCases->count() }}</span></div>
            </div>
            <div class="table-responsive">
                <table class="table dg-case-table mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Ref #</th>
                            <th>Division</th>
                            <th>Contract Case Title</th>
                            <th class="text-right">Project Code</th>
                            <th class="text-right">Final Salary</th>
                            <th class="text-right pr-4">Action Command</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actionReqCases as $p)
                        <tr>
                            <td class="pl-4"><span class="badge badge-dark text-muted" style="border: 1px solid var(--rd-border);">CC-{{ $p->ctc_id }}</span></td>
                            <td class="font-weight-bold text-dark">Division {{ $p->ctc_divisionid }}</td>
                            <td>
                                <div class="text-dark small font-weight-bold">{{ $p->ctc_empnamecomp }} ({{ $p->ctc_newjobtitle }})</div>
                                <div class="text-muted" style="font-size: 10px;"><i class="fas fa-user-check mr-1"></i> Cleared by Finance & HR</div>
                            </td>
                            <td class="text-right small text-muted font-weight-bold text-nowrap">{{ $p->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</td>
                            <td class="text-right font-weight-bold rajdhani" style="font-size: 16px; color: var(--rd-primary-600);">Rs. {{ number_format($p->ctc_newsalary) }}</td>
                            <td class="text-right pr-4">
                                <a href="{{ route('md.contract-cases.show', $p->ctc_id) }}" class="btn btn-sm rounded-pill px-3 rajdhani font-weight-bold text-dark" style="font-size: 11px; background: var(--rd-primary-600); border-color: var(--rd-primary-600);">
                                    <i class="fas fa-check-circle mr-1"></i> REVIEW & APPROVE
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-shield-alt text-muted mb-3" style="font-size: 40px; opacity: 0.3;"></i>
                                <h6 class="text-muted">No cases currently awaiting Executive Approval.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MD Processed (Open/Closed) --}}
        @if($completedCases->count() > 0)
        <div class="metric-card overflow-hidden mt-4" style="opacity: 0.8;">
            <div class="p-3 bg-white d-flex justify-content-between align-items-center" style="background: var(--rd-surface3) !important;">
                <h6 class="m-0 rajdhani text-dark font-weight-bold"><i class="fas fa-history mr-2 text-muted"></i> PREVIOUSLY PROCESSED CASES</h6>
            </div>
            <div class="table-responsive">
                <table class="table dg-case-table mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Ref #</th>
                            <th>Candidate Details</th>
                            <th class="text-center">Final Status</th>
                            <th class="text-right pr-4">View Log</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completedCases as $p)
                        <tr>
                            <td class="pl-4"><span class="badge badge-dark text-muted" style="border: 1px solid var(--rd-border);">CC-{{ $p->ctc_id }}</span></td>
                            <td>
                                <div class="text-dark small font-weight-bold">{{ $p->ctc_empnamecomp }}</div>
                            </td>
                            <td class="text-center text-muted small font-weight-bold">
                                {{ strtoupper($p->ctc_status) }}
                            </td>
                            <td class="text-right pr-4">
                                <a href="{{ route('md.contract-cases.show', $p->ctc_id) }}" class="btn btn-link btn-sm text-muted rajdhani" style="font-size: 11px;">
                                    VIEW TRAIL
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
