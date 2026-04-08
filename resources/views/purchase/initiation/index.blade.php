@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600&display=swap');
:root { --rd-text-primary: #007bff; --rd-text-warning: #f39c12; --rd-text-success: #28a745; --rd-surface: #0d1218; --rd-border: rgba(255,255,255,0.08); }
.hub-page { font-family: 'Inter', sans-serif; background: #080b0f; min-height: 100vh; }
.hub-header { font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px; }
.metric-card { background: #0d1218; border: 1px solid var(--rd-border); border-radius: 12px; transition: transform 0.2s; }
.metric-card:hover { transform: translateY(-3px); border-color: rgba(255,255,255,0.15); }
.metric-label { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.2px; color: #8a96a3; }
.metric-value { font-family: 'Rajdhani', sans-serif; font-size: 26px; font-weight: 700; color: #fff; line-height: 1; margin-top: 5px; }
.hub-tab-btn { font-family: 'Rajdhani', sans-serif; font-size: 14px; font-weight: 700; letter-spacing: 0.8px; border-radius: 30px !important; padding: 10px 24px !important; transition: all 0.2s; margin-right: 10px; border: 1px solid var(--rd-border) !important; background: transparent !important; color: #8a96a3 !important; }
.hub-tab-btn.active { background: var(--rd-text-primary) !important; color: #fff !important; border-color: var(--rd-text-primary) !important; box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3); }
.dg-case-table thead th { font-family: 'Rajdhani', sans-serif; font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #8a96a3; background: #0f161e; border: none !important; padding: 14px; }
.dg-case-table td { padding: 14px; color: #cbd5e0; vertical-align: middle; border-top: 1px solid var(--rd-border); border-bottom: none; }
.dg-case-table tr:hover { background: rgba(255,255,255,0.02); }
.case-title { font-weight: 600; color: #fff; font-size: 14px; }
.case-value { font-family: 'Rajdhani', sans-serif; font-size: 16px; font-weight: 700; color: var(--rd-text-primary); }
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
                        <h1 class="m-0 font-weight-bold text-white" style="font-size: 2.2rem;">PC Initiation Hub</h1>
                        <p class="text-muted mb-0" style="font-size: 14px; opacity: 0.7;">Central specialized portal for creating and tracking procurement workflows.</p>
                    </div>
                </div>
                <div class="col-md-5 text-right">
                    <a href="{{ route('purchase.unified.create', ['type' => 'material']) }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-lg hub-header" style="font-size: 14px; border: 2px solid rgba(255,255,255,0.1);">
                        <i class="fas fa-plus-circle mr-2"></i> CREATE NEW PURCHASE CASE
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            {{-- Metrics Row --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="metric-card p-4">
                        <div class="metric-label">Total Portfolio</div>
                        <div class="metric-value">{{ $purchases->count() }} <span style="font-size: 14px; opacity: 0.5;">Cases</span></div>
                        <div class="mt-2 text-xs text-muted"><i class="fas fa-chart-line mr-1"></i> Lifetime initiation density</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card p-4">
                        <div class="metric-label">HQ Scrutiny</div>
                        <div class="metric-value" style="color: var(--rd-text-primary);">{{ $atHqCount }} <span style="font-size: 14px; opacity: 0.5;">Active</span></div>
                        <div class="mt-1 small text-muted font-weight-bold"><i class="fas fa-university mr-1"></i> Under evaluation at HQ</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card p-4">
                        <div class="metric-label">Action Required</div>
                        <div class="metric-value" style="color: var(--rd-text-warning);">{{ $pendingCases->count() }} <span style="font-size: 14px; opacity: 0.5;">Pending</span></div>
                        <div class="mt-1 small text-warning font-weight-bold"><i class="fas fa-undo-alt mr-1"></i> Drafts / Returns</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="metric-card p-4">
                        <div class="metric-label">Approved Value</div>
                        <div class="metric-value" style="color: var(--rd-text-success);">Rs. {{ number_format($purchases->where('pcs_status', 'Approved')->sum('pcs_price')/1000, 1) }}K</div>
                        <div class="mt-1 small text-success font-weight-bold"><i class="fas fa-check-double mr-1"></i> Successful procurement cycle</div>
                    </div>
                </div>
            </div>

            {{-- Main Hub Table --}}
            <div class="card bg-transparent border-0">
                <div class="card-header bg-transparent border-0 p-0 mb-3">
                    <ul class="nav nav-pills" id="hubTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn active" data-toggle="tab" href="#action-req">
                                <i class="fas fa-folder-open mr-2"></i> PENDING CASES ({{ $pendingCases->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link hub-tab-btn" data-toggle="tab" href="#tracking">
                                <i class="fas fa-stream mr-2"></i> TRACKING & HISTORY ({{ $releasedCases->count() }})
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        
                        {{-- TAB 1: ACTION REQUIRED --}}
                        <div class="tab-pane fade show active" id="action-req">
                            <div class="metric-card overflow-hidden">
                                @if($pendingCases->count() > 0)
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
                                                @foreach($pendingCases as $p)
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
                                                            <div class="case-title">{{ Str::limit($p->pcs_title, 50) }}</div>
                                                        </div>
                                                        <div class="text-muted small"><i class="fas fa-project-diagram mr-1"></i> {{ $p->project->prj_code ?? 'General Ops' }}</div>
                                                    </td>
                                                    <td class="text-right"><span class="case-value">PKR {{ number_format((float) ($p->pcs_price ?? 0)) }}</span></td>
                                                    <td class="text-center">
                                                        <span class="badge status-badge bg-warning-soft text-warning" style="background: rgba(243,156,18,0.1); border: 1px solid rgba(243,156,18,0.2);">
                                                            <i class="fas fa-pen-nib mr-1"></i> {{ $p->pcs_status }}
                                                        </span>
                                                    </td>
                                                    <td class="text-right pr-4">
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
                                    <div class="text-center py-5" style="background: rgba(255,255,255,0.01);">
                                        <div class="display-4 text-muted mb-3" style="opacity: 0.2;"><i class="fas fa-clipboard-check"></i></div>
                                        <h6 class="text-muted font-weight-bold">Queue Empty</h6>
                                        <p class="text-muted small">All your initiated cases have been released to HQ.</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- TAB 2: TRACKING --}}
                        <div class="tab-pane fade" id="tracking">
                            <div class="metric-card overflow-hidden">
                                @if($releasedCases->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table dg-case-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="pl-4">Reference</th>
                                                    <th>Title / Context</th>
                                                    <th class="text-right">Case Value</th>
                                                    <th class="text-center">Workflow Location</th>
                                                    <th class="text-right pr-4">Monitoring</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($releasedCases as $p)
                                                @php
                                                    $s = $p->pcs_status;
                                                    $b = 'info'; $i = 'hourglass-half';
                                                    if($s == 'Approved') { $b = 'success'; $i = 'check-double'; }
                                                    if($s == 'Rejected') { $b = 'danger'; $i = 'times-circle'; }
                                                    $latest = $p->latestDecision;
                                                @endphp
                                                <tr data-id="{{ $p->pcs_id }}">
                                                    <td class="pl-4"><span class="badge badge-dark text-muted px-2 py-1" style="font-size: 9px; border: 1px solid var(--rd-border);">PC-{{ $p->pcs_id }}</span></td>
                                                    <td>
                                                        <div class="case-title">{{ Str::limit($p->pcs_title, 50) }}</div>
                                                        <div class="text-muted small"><i class="fas fa-project-diagram mr-1"></i> {{ $p->project->prj_code ?? 'HQ Procurement' }}</div>
                                                    </td>
                                                    <td class="text-right"><span class="case-value">PKR {{ number_format((float) ($p->pcs_price ?? 0)) }}</span></td>
                                                    <td class="text-center">
                                                        <span class="badge status-badge mb-1" style="background: rgba(var(--rd-{{$b}}-rgb), 0.1); color: var(--rd-text-{{$b}}); border: 1px solid rgba(var(--rd-{{$b}}-rgb), 0.2);">
                                                            <i class="fas fa-{{$i}} mr-1"></i> {{ $s }}
                                                        </span>
                                                        @if($latest)
                                                            <div class="text-xs font-weight-bold text-muted" style="font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px;">
                                                                {{ strtoupper($latest->pdec_action) }}ed BY {{ $latest->account->acc_name }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-right pr-4">
                                                        <a href="{{ route($detailsRouteName, $p->pcs_id) }}" class="btn btn-outline-primary btn-sm rounded-lg px-3 hub-header" style="font-size: 11px; border: 1px solid rgba(0, 123, 255, 0.3);">
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
                                        <h6 class="text-muted">No cases in tracking queue.</h6>
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
});
</script>
@endsection
