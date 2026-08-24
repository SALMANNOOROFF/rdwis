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
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge badge-primary px-3 py-1 rajdhani" style="font-size: 10px; background: rgba(0,123,255,0.1); color: var(--rd-primary-600); border: 1px solid rgba(0,123,255,0.2);">HR OPERATIONS AUTHORITY</span>
                    @if(in_array(strtolower(trim((string) (Auth::user()->acc_untarea ?? ''))), ['fin', 'hr', 'nrdi', 'rdw', 'hqs']))
                    <div class="btn-group btn-group-sm shadow-sm ml-2" role="group">
                        <a href="{{ route('hr.contract-cases.index', ['mode' => 'm']) }}" 
                           class="btn {{ ($mode ?? 'm') === 'm' ? 'btn-danger font-weight-bold' : 'btn-outline-danger' }}" style="{{ ($mode ?? 'm') === 'm' ? '' : 'background: var(--rd-surface2);' }}">
                            <i class="fas fa-globe mr-1"></i> ALL DEPT
                        </a>
                        <a href="{{ route('hr.contract-cases.index', ['mode' => 's']) }}" 
                           class="btn {{ ($mode ?? 'm') === 's' ? 'btn-info font-weight-bold' : 'btn-outline-info' }}"
                           style="{{ ($mode ?? 'm') === 's' ? 'background-color: var(--rd-primary-500); border-color: var(--rd-primary-500); color: white;' : 'background: var(--rd-surface2); border-color: var(--rd-primary-500);' }}">
                            <i class="fas fa-sitemap mr-1"></i> MY DEPT
                        </a>
                    </div>
                    @endif
                </div>
                <h1 class="font-weight-bold text-dark rajdhani m-0" style="font-size: 2.2rem;">HR Contract Dashboard</h1>
                <p class="text-muted mb-0 small">Candidate scrutiny, grading, and contract duration validation.</p>
            </div>
            <div class="col-md-6 text-right">
                <div class="d-inline-block metric-card p-3 text-left mr-2" style="border-right: 4px solid #007bff;">
                    <div class="metric-label">Total Salary Volume</div>
                    <div class="metric-value" style="color: var(--rd-primary-600);">PKR {{ number_format($actionReqCases->sum('ctc_newsalary')) }}</div>
                </div>
                <div class="d-inline-block metric-card p-3 text-left">
                    <div class="metric-label">Awaiting HR Review</div>
                    <div class="metric-value">{{ $actionReqCases->count() }}</div>
                </div>
            </div>
        </div>

        {{-- HR Queue --}}
        <div class="metric-card overflow-hidden">
            <div class="p-3 bg-white d-flex justify-content-between align-items-center" style="background: var(--rd-surface3) !important;">
                <h6 class="m-0 rajdhani text-dark font-weight-bold"><i class="fas fa-users-cog mr-2 text-primary"></i> HR SCRUTINY QUEUE</h6>
                <div class="text-muted small rajdhani">PENDING ACTIONS: <span class="text-dark">{{ $actionReqCases->count() }}</span></div>
            </div>
            <div class="table-responsive">
                <table class="table dg-case-table mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Ref #</th>
                            <th>Division</th>
                            <th>Candidate Details</th>
                            <th class="text-right">Project</th>
                            <th class="text-right">Proposed Salary</th>
                            <th class="text-right pr-4">Action Command</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($actionReqCases as $p)
                        <tr>
                            <td class="pl-4"><span class="badge badge-dark text-muted" style="border: 1px solid var(--rd-border);">CC-{{ $p->ctc_id }}</span></td>
                            <td class="font-weight-bold text-dark">Division {{ $p->ctc_divisionid }}</td>
                            <td>
                                <div class="text-dark small font-weight-bold">{{ $p->ctc_empnamecomp }}</div>
                                <div class="text-muted" style="font-size: 10px;"><i class="fas fa-user-tag mr-1"></i> {{ $p->ctc_newjobtitle }}</div>
                            </td>
                            <td class="text-right small text-muted font-weight-bold text-nowrap">{{ $p->casePlans->first()->project->prj_code ?? 'Core / Non-Project' }}</td>
                            <td class="text-right font-weight-bold text-primary rajdhani" style="font-size: 16px;">Rs. {{ number_format($p->ctc_newsalary) }}</td>
                            <td class="text-right pr-4">
                                <a href="{{ route('hr.contract-cases.show', $p->ctc_id) }}" class="btn btn-primary btn-sm rounded-pill px-3 rajdhani font-weight-bold" style="font-size: 11px;">
                                    <i class="fas fa-check-circle mr-1"></i> REVIEW & FORWARD
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-shield-alt text-muted mb-3" style="font-size: 40px; opacity: 0.3;"></i>
                                <h6 class="text-muted">No cases currently pending HR Scrutiny.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- HR Processed (Open/Closed) --}}
        @if($initiatedCases->count() > 0 || $completedCases->count() > 0)
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
                            <th class="text-center">Current Status</th>
                            <th class="text-right pr-4">View Log</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($initiatedCases->merge($completedCases) as $p)
                        <tr>
                            <td class="pl-4"><span class="badge badge-dark text-muted" style="border: 1px solid var(--rd-border);">CC-{{ $p->ctc_id }}</span></td>
                            <td>
                                <div class="text-dark small font-weight-bold">{{ $p->ctc_empnamecomp }}</div>
                            </td>
                            <td class="text-center text-muted small font-weight-bold">
                                {{ strtoupper($p->ctc_status) }}
                            </td>
                            <td class="text-right pr-4">
                                <a href="{{ route('hr.contract-cases.show', $p->ctc_id) }}" class="btn btn-link btn-sm text-muted rajdhani" style="font-size: 11px;">
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
