@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');
.proc-page { font-family: 'Inter', sans-serif; background: #080b0f; min-height: 100vh; color: #cbd5e0; }
.rajdhani { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }
.metric-card { background: #0d1218; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; transition: transform 0.2s; }
.metric-label { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #8a96a3; }
.metric-value { font-family: 'Rajdhani', sans-serif; font-size: 26px; font-weight: 700; color: #fff; line-height: 1; margin-top: 5px; }
.dg-case-table thead th { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #8a96a3; background: #0f161e; border: none !important; padding: 14px; }
.dg-case-table td { padding: 14px; color: #cbd5e0; vertical-align: middle; border-top: 1px solid rgba(255,255,255,0.08); }
.dg-case-table tr:hover { background: rgba(255,255,255,0.02); }
</style>

<div class="content-wrapper proc-page pt-4">
    <div class="container-fluid px-4">
        {{-- Header --}}
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <span class="badge badge-primary px-3 py-1 mb-2 rajdhani" style="font-size: 10px;">HQ SCRUTINY AUTHORTIY</span>
                <h1 class="font-weight-bold text-white rajdhani m-0" style="font-size: 2.2rem;">Director Procurement Hub</h1>
                <p class="text-muted mb-0 small">Incoming purchase cases awaiting technical and procedural scrutiny.</p>
            </div>
            <div class="col-md-5 text-right">
                <div class="d-inline-block metric-card p-3 text-left mr-2">
                    <div class="metric-label">Queue Volume</div>
                    <div class="metric-value text-primary">PKR {{ number_format($totalVolume) }}</div>
                </div>
                <div class="d-inline-block metric-card p-3 text-left">
                    <div class="metric-label">Active Cases</div>
                    <div class="metric-value">{{ $caseCount }}</div>
                </div>
            </div>
        </div>

        {{-- Queue Table --}}
        <div class="metric-card mb-5 overflow-hidden shadow-lg" style="border-top: 5px solid var(--rd-primary);">
            <div class="p-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(to right, #0f172a, #101c3d) !important;">
                <h6 class="m-0 rajdhani text-white font-weight-bold"><i class="fas fa-inbox mr-2 text-primary"></i> INCOMING SCRUTINY QUEUE</h6>
                <div class="badge badge-dark rajdhani px-4 py-2" style="font-size: 10px; border: 1px solid rgba(255,255,255,0.05);">PENDING: {{ $caseCount }}</div>
            </div>
            <div class="table-responsive">
                <table class="table dg-case-table mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Ref #</th>
                            <th>Originating Unit</th>
                            <th>Procurement Subject</th>
                            <th class="text-right">Project</th>
                            <th class="text-right">Est. Cost</th>
                            <th class="text-right pr-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $p)
                        <tr>
                            <td class="pl-4">
                                <div class="badge badge-primary px-2 py-1 rajdhani" style="font-size: 10px; opacity: 0.8 !important;">PC-{{ $p->pcs_id }}</div>
                            </td>
                            <td class="font-weight-bold text-white">
                                <span class="d-block">{{ $unitNameMap[$p->pcs_unt_id] ?? 'Unknown Div' }}</span>
                                <span class="small text-muted rajdhani" style="font-size: 9px;">FROM: {{ $p->account->acc_name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="text-white small font-weight-bold">{{ Str::limit($p->pcs_title, 55) }}</div>
                                <div class="text-muted" style="font-size: 10px;">{{ \Carbon\Carbon::parse($p->pcs_date)->format('d M, Y') }}</div>
                            </td>
                            <td class="text-right font-weight-bold small text-muted">{{ $p->project->prj_code ?? 'N/A' }}</td>
                            <td class="text-right font-weight-bold text-primary rajdhani" style="font-size: 16px;">Rs. {{ number_format($p->pcs_price) }}</td>
                            <td class="text-right pr-4">
                                <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn btn-primary btn-sm rounded-pill px-4 rajdhani font-weight-bold" style="font-size: 11px; box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);">
                                    <i class="fas fa-search-plus mr-1"></i> SCRUTINIZE
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-check-double text-muted mb-3" style="font-size: 40px; opacity: 0.2;"></i>
                                <h6 class="text-muted rajdhani">Everything Scrutinized! Good job.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Action Taken Table (Processed Cases) --}}
        <div class="metric-card overflow-hidden shadow-lg mt-5" style="border-top: 5px solid #64748b; opacity: 0.9;">
            <div class="p-3 d-flex justify-content-between align-items-center" style="background: #0f161e !important;">
                <h6 class="m-0 rajdhani text-white font-weight-bold"><i class="fas fa-history mr-2 text-muted"></i> ACTION TAKEN (RECENTLY PROCESSED)</h6>
                <div class="small text-muted rajdhani">LAST 10 CASES</div>
            </div>
            <div class="table-responsive">
                <table class="table dg-case-table mb-0">
                    <thead>
                        <tr style="background: #0d1218;">
                            <th class="pl-4">ID</th>
                            <th>Initiator</th>
                            <th>Case Title</th>
                            <th class="text-right">Decision</th>
                            <th class="text-right">Status Now</th>
                            <th class="text-right pr-4">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($processed as $p)
                        <tr>
                            <td class="pl-4">#{{ $p->pcs_id }}</td>
                            <td class="small font-weight-bold text-white">{{ $unitNameMap[$p->pcs_unt_id] ?? 'N/A' }}</td>
                            <td>
                                <div class="text-muted small font-weight-bold">{{ Str::limit($p->pcs_title, 40) }}</div>
                            </td>
                            <td class="text-right">
                                @php $lastDec = $p->latestDecision; @endphp
                                <span class="badge badge-dark px-2 py-1 rajdhani" style="font-size: 9px; border: 1px solid rgba(255,255,255,0.05);">
                                    {{ strtoupper($lastDec->pdec_action ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="text-right">
                                <span class="small font-weight-bold text-{{ strpos($p->pcs_status, 'Rejected') !== false ? 'danger' : 'success' }}">{{ $p->pcs_status }}</span>
                            </td>
                            <td class="text-right pr-4">
                                <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn btn-outline-light btn-xs rajdhani" style="border: 1px solid rgba(255,255,255,0.1);">VIEW LOG</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted small italic">No processed cases found in your recent history.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
