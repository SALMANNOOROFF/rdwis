@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');
.fin-page { font-family: 'Inter', sans-serif; background: #080b0f; min-height: 100vh; color: #cbd5e0; }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }
.metric-card { background: #0d1218; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; transition: transform 0.2s; }
.metric-label { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #8a96a3; }
.metric-value { font-family: 'Rajdhani', sans-serif; font-size: 26px; font-weight: 700; color: #fff; line-height: 1; margin-top: 5px; }
.dg-case-table thead th { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #8a96a3; background: #0f161e; border: none !important; padding: 14px; }
.dg-case-table td { padding: 14px; color: #cbd5e0; vertical-align: middle; border-top: 1px solid rgba(255,255,255,0.08); }
.dg-case-table tr:hover { background: rgba(255,255,255,0.02); }
</style>

<div class="content-wrapper fin-page pt-4">
    <div class="container-fluid px-4">
        {{-- Header --}}
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <span class="badge badge-success px-3 py-1 mb-2 rajdhani" style="font-size: 10px; background: rgba(40,167,69,0.1); color: #28a745; border: 1px solid rgba(40,167,69,0.2);">FINANCIAL APPROVAL AUTHORITY</span>
                <h1 class="font-weight-bold text-white rajdhani m-0" style="font-size: 2.2rem;">Director Finance Dashboard</h1>
                <p class="text-muted mb-0 small">Project-wise financial scrutiny and budget allocation review.</p>
            </div>
            <div class="col-md-5 text-right">
                <div class="d-inline-block metric-card p-3 text-left mr-2" style="border-right: 4px solid #28a745;">
                    <div class="metric-label">Impact Volume</div>
                    <div class="metric-value" style="color: #28a745;">PKR {{ number_format($totalFinanceVolume) }}</div>
                </div>
                <div class="d-inline-block metric-card p-3 text-left">
                    <div class="metric-label">Awaiting Review</div>
                    <div class="metric-value">{{ $caseCount }}</div>
                </div>
            </div>
        </div>

        {{-- Finance Queue --}}
        <div class="metric-card overflow-hidden">
            <div class="p-3 bg-dark d-flex justify-content-between align-items-center" style="background: #0f161e !important;">
                <h6 class="m-0 rajdhani text-white font-weight-bold"><i class="fas fa-file-invoice-dollar mr-2 text-success"></i> BUDGET REVIEW QUEUE</h6>
                <div class="text-muted small rajdhani">TOTAL CASES: <span class="text-white">{{ $caseCount }}</span></div>
            </div>
            <div class="table-responsive">
                <table class="table dg-case-table mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Ref #</th>
                            <th>Division</th>
                            <th>Purchase Case Title</th>
                            <th class="text-right">Budget Head</th>
                            <th class="text-right">Proposed Amt</th>
                            <th class="text-right pr-4">Finance Command</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $p)
                        <tr>
                            <td class="pl-4"><span class="badge badge-dark text-muted" style="border: 1px solid rgba(255,255,255,0.05);">PC-{{ $p->pcs_id }}</span></td>
                            <td class="font-weight-bold text-white">{{ $unitNameMap[$p->pcs_unt_id] ?? 'HQ Unit' }}</td>
                            <td>
                                <div class="text-white small font-weight-bold">{{ Str::limit($p->pcs_title, 45) }}</div>
                                <div class="text-muted" style="font-size: 10px;"><i class="fas fa-user-check mr-1"></i> Scrutinized by DProc</div>
                            </td>
                            <td class="text-right small text-muted font-weight-bold text-nowrap">{{ $p->project->prj_code ?? 'CENTRAL' }}</td>
                            <td class="text-right font-weight-bold text-success rajdhani" style="font-size: 16px;">Rs. {{ number_format($p->pcs_price) }}</td>
                            <td class="text-right pr-4">
                                <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn btn-success btn-sm rounded-pill px-3 rajdhani font-weight-bold" style="font-size: 11px; background: #28a745; border-color: #28a745;">
                                    <i class="fas fa-check-circle mr-1"></i> REVIEW & MOVE
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-shield-alt text-muted mb-3" style="font-size: 40px; opacity: 0.3;"></i>
                                <h6 class="text-muted">No cases currently with Finance.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
