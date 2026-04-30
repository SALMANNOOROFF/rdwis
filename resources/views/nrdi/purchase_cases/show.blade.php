@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@300;400;500;600&display=swap');

/* ===== SCOPED to .dg-page ===== */
.dg-page { font-family:'Inter',sans-serif; }
.text-gold { color: #f39c12 !important; }
.bg-navy { background-color: #001f3f !important; }
.border-gold { border-top: 3px solid #f39c12 !important; }
.border-left-gold { border-left: 5px solid #f39c12 !important; }

/* ---- Page Header ---- */
.dg-hdr { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.dg-back-btn { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:var(--rd-text2); background:var(--rd-surface); border:1px solid var(--rd-border); padding:5px 13px; border-radius:20px; text-decoration:none !important; transition:all .2s; }
.dg-back-btn:hover { border-color:var(--rd-accent); color:var(--rd-accent); }
.dg-page-title { font-family:'Rajdhani',sans-serif; font-size:19px; font-weight:700; color:var(--rd-text1); letter-spacing:.8px; margin-top:5px; }
.dg-case-badge { background:var(--rd-accent); color:var(--rd-bg); font-family:'Rajdhani',sans-serif; font-size:12px; font-weight:700; padding:4px 12px; border-radius:6px; letter-spacing:1px; display:inline-block; }
.dg-case-date { font-size:11px; color:var(--rd-text3); margin-top:4px; text-align:right; }

/* ---- 3-col grid ---- */
.dg-grid { display:grid; grid-template-columns:3fr 4.5fr 2.5fr; gap:14px; align-items:start; }
@media(max-width:1300px){ .dg-grid { grid-template-columns:3fr 4.5fr 2.5fr; } }
@media(max-width:1024px){ .dg-grid { grid-template-columns:1fr 1fr; } }
@media(max-width:860px)  { .dg-grid { grid-template-columns:1fr; } }

/* ---- Section labels (no box) ---- */
.dg-sec-label { font-family:'Rajdhani',sans-serif; font-size:11px; font-weight:700; letter-spacing:1.8px; color:var(--rd-accent); text-transform:uppercase; margin-bottom:10px; display:flex; align-items:center; gap:7px; }
.dg-sec-label::before { content:''; width:3px; height:12px; background:var(--rd-accent); border-radius:2px; display:inline-block; }

/* ---- Case title area (no box) ---- */
.dg-case-title { font-family:'Rajdhani',sans-serif; font-size:20px; font-weight:700; color:var(--rd-text1); margin-bottom:6px; line-height:1.25; }
.dg-meta-row { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:4px; }
.dg-meta-item { font-size:11px; color:var(--rd-text2); display:flex; align-items:center; gap:4px; }
.dg-status-badge { font-size:10px; font-weight:700; padding:2px 10px; border-radius:20px; background:rgba(255,193,7,0.12); color:var(--rd-warning); border:1px solid rgba(255,193,7,0.28); letter-spacing:.5px; }
.dg-divider { height:1px; background:var(--rd-border); margin:14px 0; }

/* ---- Financial row (50/50 equal height with border) ---- */
.dg-fin-row { display:grid; grid-template-columns:30% 1fr; gap:14px; align-items:stretch; }
.dg-fin-col { background:var(--rd-surface); border:1px solid var(--rd-border); border-radius:10px; padding:12px; display:flex; flex-direction:column; }
@media(max-width:860px) { .dg-fin-row { grid-template-columns:1fr; } }
@media(max-width:600px) { .dg-fin-row { grid-template-columns:1fr; } }

/* ---- Financial numbers ---- */
.dg-fin-nums { display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:10px; }
.dg-fin-card { background:var(--rd-surface2); border:1px solid var(--rd-border); border-radius:7px; padding:6px 8px; text-align:center; }
.dg-fin-label { font-size:8px; letter-spacing:.6px; text-transform:uppercase; color:var(--rd-text3); margin-bottom:2px; font-weight:600; }
.dg-fin-value { font-family:'Rajdhani',sans-serif; font-size:13px; font-weight:700; }

.dg-prog-wrap { position:relative; height:12px; background:rgba(255,255,255,0.04); border-radius:20px; overflow:hidden; margin-bottom:4px; border:1px solid var(--rd-border); }
.dg-prog-utilized { position:absolute; left:0; top:0; height:100%; background:var(--rd-text3); border-radius:20px 0 0 20px; width:0; transition:width 1s cubic-bezier(.4,0,.2,1) .2s; }
.dg-prog-utilized.anim { width:var(--pw); }
.dg-prog-case {
    position:absolute; top:0; height:100%;
    background: repeating-linear-gradient(-45deg, var(--rd-info) 0px, var(--rd-info) 6px, rgba(255,255,255,0.18) 6px, rgba(255,255,255,0.18) 12px);
    background-size:18px 100%;
    animation:dgStripeFlow .7s linear infinite, dgCaseGrow .9s cubic-bezier(.4,0,.2,1) .7s both;
    width:0; left:var(--lu);
}
@keyframes dgStripeFlow { 0%{background-position:0 0} 100%{background-position:18px 0} }
@keyframes dgCaseGrow   { from{width:0} to{width:var(--pw)} }
.dg-prog-remain { position:absolute; right:0; top:0; height:100%; border-radius:0 20px 20px 0; width:0; transition:width .9s cubic-bezier(.4,0,.2,1) 1s; }
.dg-prog-remain.anim { width:var(--pw); }
.dg-prog-legend { display:flex; gap:10px; flex-wrap:wrap; margin-top:4px; justify-content:center; }
.dg-leg-item { display:flex; align-items:center; gap:4px; font-size:9px; color:var(--rd-text2); }
.dg-leg-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; }

.dg-chart-wrap { position:relative; width:100%; height:110px; }
.dg-chart-sm   { position:relative; width:100%; height:90px; }

/* ---- Items box ---- */
.dg-box { background:var(--rd-surface); border:1px solid var(--rd-border); border-radius:10px; overflow:hidden; }
.dg-box-hdr { background:var(--rd-surface2); padding:8px 12px; border-bottom:1px solid var(--rd-border); display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }
.dg-box-hdr-left { display:flex; flex-direction:column; gap:1px; }
.dg-box-hdr-firm { font-family:'Rajdhani',sans-serif; font-size:14px; font-weight:700; color:var(--rd-text1); }
.dg-box-hdr-cost { font-size:10px; color:var(--rd-success); font-weight:600; }
.dg-box-hdr-right { display:flex; align-items:center; gap:6px; flex-shrink:0; }

.dg-cs-btn { background:rgba(23,162,184,0.1); border:1px solid rgba(23,162,184,0.3); color:var(--rd-info); font-size:10px; font-weight:600; padding:4px 10px; border-radius:6px; cursor:pointer; transition:all .2s; white-space:nowrap; }
.dg-cs-btn:hover { background:rgba(23,162,184,0.22); color:#fff; border-color:var(--rd-info); }

.dg-items-wrap { max-height:180px; overflow-y:auto; }
.dg-items-wrap::-webkit-scrollbar { width:3px; }
.dg-items-wrap::-webkit-scrollbar-thumb { background:var(--rd-border); border-radius:4px; }
.dg-items-table { width:100%; font-size:11px; border-collapse:collapse; }
.dg-items-table th { padding:6px 10px; color:var(--rd-text3); font-weight:600; font-size:9px; letter-spacing:.6px; text-align:left; text-transform:uppercase; background:var(--rd-surface2); }
.dg-items-table td { padding:6px 10px; border-top:1px solid var(--rd-border); color:var(--rd-text1); font-size:11px; }
.dg-items-table tr:hover td { background:rgba(255,255,255,0.015); }
.dg-price-col { color:var(--rd-success) !important; font-weight:600; text-align:right !important; }
.dg-qty-col { text-align:center !important; color:var(--rd-warning) !important; font-weight:600; }

.dg-terms-row { padding:6px 12px; border-top:1px solid var(--rd-border); background:rgba(255,255,255,0.015); line-height:1.3; }
.dg-terms-label { font-size:10px; font-weight:700; color:var(--rd-text1); display:inline; }
.dg-terms-text { font-size:10px; color:var(--rd-text2); display:inline; margin-left:4px; }

/* ---- Right panel ---- */
.dg-right { display:flex; flex-direction:column; gap:12px; }
.dg-panel-r { background:var(--rd-surface); border:1px solid var(--rd-border); border-radius:10px; overflow:hidden; }
.dg-panel-r-hdr { background:var(--rd-surface2); padding:8px 12px; border-bottom:1px solid var(--rd-border); display:flex; align-items:center; gap:6px; }
.dg-panel-r-title { font-family:'Rajdhani',sans-serif; font-size:12px; font-weight:700; color:var(--rd-accent); letter-spacing:0.8px; text-transform:uppercase; }

.dg-trail-body { padding:14px; max-height:360px; overflow-y:auto; }
.dg-trail-body::-webkit-scrollbar { width:3px; }
.dg-trail-body::-webkit-scrollbar-thumb { background:var(--rd-border); border-radius:4px; }

.dg-tl-item { display:flex; gap:10px; opacity:0; transform:translateX(-10px); animation:dgSlideIn .4s forwards; }
@keyframes dgSlideIn { to { opacity:1; transform:translateX(0); } }
.dg-tl-line { display:flex; flex-direction:column; align-items:center; width:26px; flex-shrink:0; }
.dg-tl-node { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:9px; flex-shrink:0; }
.dg-tl-connector { flex:1; width:2px; min-height:14px; margin:2px 0; }
.dg-tl-content { flex:1; padding-bottom:13px; }
.dg-tl-actor { font-family:'Rajdhani',sans-serif; font-size:13px; font-weight:700; color:var(--rd-text1); }
.dg-tl-time { font-size:10px; color:var(--rd-text3); }
.dg-tl-badge { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:4px; margin:3px 0; letter-spacing:.5px; }
.dg-tl-comment { font-size:11px; color:var(--rd-text2); font-style:italic; border-left:2px solid; padding:4px 8px; border-radius:0 4px 4px 0; margin-top:4px; line-height:1.5; background:rgba(255,255,255,0.03); }

/* Custom for Initiation */
.edit-input { background: #001226 !important; border: 1px solid var(--rd-accent) !important; color: #fff !important; font-size: 1.5rem !important; font-weight: bold !important; padding: 5px 15px !important; border-radius: 8px !important; }
</style>

<div class="content-wrapper dg-page">

    <div class="content-header pb-0">
        <div class="container-fluid">

            @php
                $winnerQuote  = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->first() : null;
                $sortedQ      = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->values() : collect([]);

                $caseValue      = (float)($purchase->pcs_price ?? ($winnerQuote?->qte_price ?? 0));
                
                // Live Financials from $head (Project)
                $totalBudget    = (float)($head->prj_aprvcost ?? 0); 
                $utilizedBudget = $totalBudget - (float)($head->hed_balance ?? $totalBudget);
                $balanceAfter   = (float)($head->hed_balance ?? 0) - $caseValue;

                $pctUtilized  = $totalBudget > 0 ? ($utilizedBudget / $totalBudget) * 100 : 0;
                $pctCase      = $totalBudget > 0 ? ($caseValue / $totalBudget) * 100 : 0;
                $pctRemaining = $totalBudget > 0 ? max(0, ($balanceAfter / $totalBudget) * 100) : 0;
                
                $service = app(\App\Services\PurchaseApprovalService::class);
                $currentStatusDisplay = $service->getStatusDisplayName($purchase->pcs_status);
                
                // Variable overrides for cross-role compatibility
                $isInitiator = in_array(strtolower(trim((string)Auth::user()->acc_untarea)), ['prj', 'rdwprj', 'division']);
                $canEdit = $isInitiator && in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);
                $backRoute = $isInitiator ? route('purchase.initiation.index') : route('nrdi.purchase_cases.index');
            @endphp

            {{-- Page Header --}}
            <div style="margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.05);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        <span class="dg-case-badge" style="background:var(--rd-accent); color:var(--rd-bg); padding:2px 10px; border-radius:4px; font-weight:800; font-family:'Rajdhani',sans-serif; font-size: 15px;">#{{ $purchase->pcs_id }}</span>
                        <span style="color:var(--rd-text3); font-size:14px; font-weight:500;">|</span>
                        <span style="color:var(--rd-text2); font-size:14px;"><i class="fas fa-calendar-alt mr-1" style="color:var(--rd-accent);"></i> {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}</span>
                        <span style="color:var(--rd-text3); font-size:14px; font-weight:500;">|</span>
                        <span style="color:var(--rd-text2); font-size:14px;"><i class="fas fa-project-diagram mr-1" style="color:var(--rd-accent);"></i> <span class="text-white font-weight-bold">{{ $purchase->project?->prj_code ?? $purchase->pcs_hed_id }}</span></span>
                        <span style="color:var(--rd-text3); font-size:14px; font-weight:500;">|</span>
                        <span style="color:var(--rd-text2); font-size:14px;"><i class="fas fa-building mr-1" style="color:var(--rd-accent);"></i> <span class="text-white font-weight-bold">{{ $purchase->unit?->unt_name ?? $purchase->pcs_unt_id }}</span></span>
                        <span style="color:var(--rd-text3); font-size:14px; font-weight:500;">|</span>
                        <span style="color:var(--rd-text2); font-size:15px;"><i class="fas fa-money-bill-wave mr-1" style="color:var(--rd-accent);"></i> <span style="color:var(--rd-info); font-weight:900; font-family:'Rajdhani',sans-serif; font-size:20px;">PKR {{ number_format($caseValue) }}</span></span>
                        <span style="color:var(--rd-text3); font-size:14px; font-weight:500;">|</span>
                        <span style="color:var(--rd-text2); font-size:14px;"><i class="fas fa-trophy mr-1" style="color:#f39c12;"></i> <span class="text-white font-weight-bold">{{ $winnerQuote?->firm?->frm_name ?? ($winnerQuote?->qte_firmname ?? 'N/A') }}</span></span>
                        <span class="dg-status-badge ml-2" style="background: {{ $purchase->pcs_status == 'Approved' ? 'rgba(40,167,69,0.2)' : ($purchase->pcs_status == 'Returned' ? 'rgba(255,193,7,0.2)' : 'rgba(0,123,255,0.2)') }}; color: {{ $purchase->pcs_status == 'Approved' ? '#28a745' : ($purchase->pcs_status == 'Returned' ? '#ffc107' : '#007bff') }}; border-color: transparent;">
                            {{ $currentStatusDisplay }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('purchase.case_detail', $purchase->pcs_id) }}" target="_blank" class="btn btn-sm btn-outline-info rajdhani font-weight-bold" style="padding:4px 12px; font-size:11px; border-radius: 6px; border-color: rgba(23,162,184,0.4); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <i class="fas fa-list-alt mr-1"></i> CASE DETAIL
                        </a>
                        <a href="{{ route('purchase.market_research', $purchase->pcs_id) }}" target="_blank" class="btn btn-sm btn-outline-success rajdhani font-weight-bold" style="padding:4px 12px; font-size:11px; border-radius: 6px; border-color: rgba(40,167,69,0.4); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <i class="fas fa-search mr-1"></i> MARKET RESEARCH
                        </a>
                        <a href="{{ route('purchase.minute_view', $purchase->pcs_id) }}" target="_blank" class="btn btn-sm btn-outline-primary rajdhani font-weight-bold" style="padding:4px 12px; font-size:11px; border-radius: 6px; border-color: rgba(0,123,255,0.4); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <i class="fas fa-file-alt mr-1"></i> MINUTE VIEW
                        </a>
                        <a href="{{ $backRoute }}" class="dg-back-btn" style="padding: 6px 15px; font-size: 12px;">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Hub
                        </a>
                    </div>
                </div>

                <div class="dg-meta-header" style="display:flex; flex-wrap:wrap; align-items:center; gap:4px 0;">
                    @if($canEdit)
                        <form action="{{ route('purchase.update_core', $purchase->pcs_id) }}" method="POST" class="d-flex align-items-center flex-grow-1">
                            @csrf
                            <input type="text" name="pcs_title" class="edit-input flex-grow-1 mr-3" style="font-size: 1.2rem !important; padding: 4px 12px !important;" value="{{ $purchase->pcs_title }}">
                            <button type="submit" class="btn btn-primary btn-xs rajdhani font-weight-bold px-3 py-1">SAVE TITLE</button>
                        </form>
                    @else
                        <span style="font-size:14px; color:#fff; font-weight:700;">Title:</span>&nbsp;<span style="font-size:14px; color:#fff;">{{ $purchase->pcs_title }}</span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            
            @if($canEdit)
            <div class="alert alert-warning border-0 px-4 py-3 d-flex align-items-center mb-4" style="background: rgba(243,156,18,0.1); border-left: 4px solid #f39c12 !important;">
                <i class="fas fa-info-circle mr-3" style="font-size: 24px;"></i>
                <div>
                    <h6 class="mb-0 font-weight-bold rajdhani text-warning">DRAFT / RETURNED MODE</h6>
                    <p class="mb-0 text-muted small">You can edit items and quotations. Headquarters will NOT review this case until you click <strong>'RELEASE TO HQ'</strong> below.</p>
                </div>
            </div>
            @endif

            <div class="dg-grid">

                {{-- ============ LEFT PANE ============ --}}
                <div class="dg-right">
                    
                    {{-- Decision Panel (Includes Release/Hold logic) --}}
                    @include('approvals._action_box')

                    {{-- CONVERSATIONAL VIEW (NEW) --}}
                    <div class="dg-panel-r mt-3">
                        <div class="dg-panel-r-hdr py-2 px-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-comments text-accent" style="font-size: 12px;"></i>
                                <span class="dg-panel-r-title" style="font-size: 11px;">Conversational View</span>
                                <div class="dropdown ml-2">
                                    <button class="btn btn-xs btn-outline-light dropdown-toggle py-0 px-2" style="font-size:10px; border-radius:10px; color: var(--rd-accent); border-color: var(--rd-accent);" type="button" data-toggle="dropdown">
                                        <i class="fas fa-history"></i> History
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-left bg-dark border-secondary shadow" style="max-height: 300px; width: 280px; overflow-y:auto; font-size:11px; padding: 0;">
                                        <div class="bg-navy py-2 px-3 border-bottom border-secondary">
                                            <span class="rajdhani font-weight-bold text-accent" style="font-size: 12px; letter-spacing: 0.5px;">DECISION TRAIL</span>
                                        </div>
                                        @foreach($purchase->decisions->sortByDesc('created_at') as $decision)
                                            @php
                                                $act = $decision->pdec_action;
                                                $color = '#ffffff'; // Recommended (White)
                                                $actionVerb = 'Forwarded';
                                                if($act == 'approve') { $color = '#28a745'; $actionVerb = 'Approved'; }
                                                elseif($act == 'return') { $color = '#dc3545'; $actionVerb = 'Returned'; }
                                                elseif($act == 'hold') { $color = '#dc3545'; $actionVerb = 'Reverted'; }
                                                elseif($act == 'forward_negative' || $act == 'reject' || $act == 'not_approved') { $color = '#ffc107'; $actionVerb = 'Not Recommended'; } // Yellow
                                                
                                                $toStatusDisplay = $service->getStatusDisplayName($decision->pdec_to_status);
                                            @endphp
                                            <a class="dropdown-item text-white border-bottom border-secondary py-2" href="#" onclick="scrollToUserComment('user-comment-{{$decision->pdec_id}}'); return false;" style="white-space: normal;">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="font-weight-bold" style="color: {{$color}}; font-size: 11px;">{{ $decision->account->acc_name }}</span>
                                                    <span class="text-muted" style="font-size: 9px;">{{ \Carbon\Carbon::parse($decision->created_at)->format('d M, H:i') }}</span>
                                                </div>
                                                <div class="small text-muted" style="line-height: 1.3;">
                                                    <span class="text-white font-weight-bold">{{ $actionVerb }}</span> 
                                                    @if($act !== 'hold' && $act !== 'approve')
                                                        to <span class="text-accent">{{ $toStatusDisplay }}</span>
                                                    @endif
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('purchase.minute_view', $purchase->pcs_id) }}" target="_blank" class="btn btn-xs btn-outline-primary rajdhani font-weight-bold" style="padding:1px 6px; font-size:10px; border-radius: 4px;">
                                <i class="fas fa-file-alt mr-1"></i> MINUTE VIEW
                            </a>
                        </div>
                        <div class="p-3" id="conversational-comments-box" style="max-height: 400px; overflow-y: auto;">
                            @forelse($purchase->decisions->sortByDesc('created_at') as $decision)
                                @php
                                    $act = $decision->pdec_action;
                                    $color = '#ffffff'; // Recommended (White)
                                    $actionVerb = 'Forwarded';
                                    if($act == 'approve') { $color = '#28a745'; $actionVerb = 'Approved'; }
                                    elseif($act == 'return') { $color = '#dc3545'; $actionVerb = 'Returned'; }
                                    elseif($act == 'hold') { $color = '#dc3545'; $actionVerb = 'Reverted'; }
                                    elseif($act == 'forward_negative' || $act == 'reject' || $act == 'not_approved') { $color = '#ffc107'; $actionVerb = 'Not Recommended'; } // Yellow
                                    
                                    $senderName  = $decision->account->acc_name ?? 'Unknown';
                                    $toDisplay   = $service->getStatusDisplayName($decision->pdec_to_status);
                                    $hasRemarks  = !empty(trim(strip_tags($decision->pdec_remarks)));
                                @endphp
                                <div class="mb-3" id="user-comment-{{$decision->pdec_id}}" style="border-bottom: 1px dashed rgba(255,255,255,0.06); padding-bottom: 10px;">
                                    {{-- Line 1: User sent to Authority + Date --}}
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="rajdhani font-weight-bold" style="font-size: 13px; color: var(--rd-text2);">
                                            <i class="fas fa-user-circle text-accent mr-1"></i>
                                            <span class="text-white">{{ $senderName }}</span>
                                            <span class="text-muted mx-1" style="font-size: 11px;">sent to</span>
                                            <span class="text-white">{{ $toDisplay }}</span>
                                        </div>
                                        <span class="text-muted" style="font-size: 9px; font-weight: 600; white-space: nowrap;">
                                            {{ \Carbon\Carbon::parse($decision->created_at)->format('d M, h:i A') }}
                                        </span>
                                    </div>
                                    {{-- Line 2: Action + Comment Icon --}}
                                    <div class="d-flex align-items-center mt-1" style="padding-left: 22px;">
                                        <span class="font-weight-bold rajdhani" style="font-size: 11px; color: {{$color}}; letter-spacing: 0.5px;">
                                            <i class="fas fa-caret-right mr-1"></i>{{ strtoupper($actionVerb) }}
                                        </span>
                                        @if($hasRemarks)
                                        <button type="button" class="btn p-0 ml-2" onclick="toggleRemarks('remarks-{{$decision->pdec_id}}')" style="color: var(--rd-accent); font-size: 13px; line-height: 1; border: none; background: none; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">
                                            <i class="fas fa-comment-dots"></i>
                                        </button>
                                        @endif
                                    </div>
                                    {{-- Expandable Remarks --}}
                                    @if($hasRemarks)
                                    <div id="remarks-{{$decision->pdec_id}}" style="display: none; padding: 8px 12px; margin-top: 6px; margin-left: 22px; font-size: 12px; color: var(--rd-text1); line-height: 1.6; background: rgba(255,255,255,0.02); border-left: 2px solid {{$color}}; border-radius: 0 4px 4px 0;">
                                        {!! $decision->pdec_remarks !!}
                                    </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center text-muted small py-3">No remarks yet.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- ============ MIDDLE PANE (45%) ============ --}}
                <div style="display:flex;flex-direction:column;gap:14px;">

                    {{-- Financial Row --}}
                    <div class="dg-fin-row" style="grid-template-columns: 1fr;">
                        <div class="dg-fin-col" style="grid-column: 1 / -1;">
                            <div class="dg-sec-label d-flex justify-content-between align-items-center mb-3">
                                <span style="font-size: 12px;"><i class="fas fa-chart-pie fa-xs"></i> Financial Picture</span>
                                <button class="btn btn-sm btn-info rajdhani font-weight-bold px-3 py-1 shadow-sm" data-toggle="modal" data-target="#financialIntelligenceModal" style="font-size: 10px; border-radius: 6px; letter-spacing: 0.5px;">
                                    <i class="fas fa-chart-line mr-1"></i> VIEW FINANCIAL DETAILS
                                </button>
                            </div>
                            <div class="dg-fin-nums" style="grid-template-columns: repeat(4, 1fr); border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px;">
                                <div class="text-center"><div class="dg-fin-label">Total Budget</div><div class="dg-fin-value">{{ number_format($totalBudget) }}</div></div>
                                <div class="text-center"><div class="dg-fin-label">Utilized</div><div class="dg-fin-value">{{ number_format($utilizedBudget) }}</div></div>
                                <div class="text-center"><div class="dg-fin-label">This Case</div><div class="dg-fin-value" style="color:var(--rd-info);">{{ number_format($caseValue) }}</div></div>
                                <div class="text-center"><div class="dg-fin-label">Bal. After</div><div class="dg-fin-value" style="color:{{ $balanceAfter < 0 ? 'var(--rd-danger)' : 'var(--rd-success)' }};">{{ number_format($balanceAfter) }}</div></div>
                            </div>
                            <div class="dg-prog-wrap">
                                <div class="dg-prog-utilized" id="dgProgU" style="--pw:{{ $pctUtilized }}%;"></div>
                                <div class="dg-prog-case" id="dgProgC" style="--lu:{{ $pctUtilized }}%; --pw:{{ $pctCase }}%; left:{{ $pctUtilized }}%;"></div>
                                <div class="dg-prog-remain" id="dgProgR" style="--pw:{{ $pctRemaining }}%; background:{{ $balanceAfter < 0 ? 'var(--rd-danger)' : '#1F6C35' }};"></div>
                            </div>
                            <div class="dg-prog-legend">
                                <div class="dg-leg-item"><div class="dg-leg-dot" style="background:{{ $balanceAfter < 0 ? 'var(--rd-danger)' : 'var(--rd-success)' }};"></div>Remaining ({{ round($pctRemaining,1) }}%)</div>
                            </div>
                        </div>
                    </div>

                    {{-- Items List --}}
                    <div class="dg-box">
                        <div class="dg-box-hdr">
                            <div class="dg-sec-label" style="margin-bottom:0;"><i class="fas fa-boxes fa-xs"></i> Items</div>
                            @if($canEdit)
                            <button class="btn btn-outline-light btn-xs rajdhani" data-toggle="modal" data-target="#addItemModal"><i class="fas fa-plus"></i></button>
                            @endif
                        </div>
                        <div class="dg-items-wrap" style="max-height: 320px;">
                            <table class="dg-items-table">
                                <thead>
                                    <tr>
                                        <th class="pl-4" style="width: 40px;">S.No</th>
                                        <th>Description</th>
                                        <th class="text-right">Unit Price</th>
                                        <th class="text-center pr-4">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->items as $ii => $item)
                                    <tr>
                                        <td class="pl-4 text-muted">{{ $ii + 1 }}</td>
                                        <td>{{ $item->pci_desc }}</td>
                                        <td class="text-right font-weight-bold text-success">{{ number_format($item->pci_price) }}</td>
                                        <td class="text-center pr-4 font-weight-bold">{{ $item->pci_qty }} {{ $item->pci_qtyunit }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Quotations / Comparative Statement --}}
                    <div class="dg-box">
                        <div class="dg-box-hdr">
                            <div class="dg-sec-label" style="margin-bottom:0;"><i class="fas fa-balance-scale fa-xs"></i> Quotations</div>
                            <div class="d-flex align-items-center gap-2">
                                @if($sortedQ->count() > 3)
                                    <span class="text-muted mr-1" style="font-size: 10px; font-weight: 500;">(+{{ $sortedQ->count() - 3 }} more quotes)</span>
                                @endif
                                <button class="dg-cs-btn" data-toggle="modal" data-target="#detailedCSModal"><i class="fas fa-file-alt mr-1"></i> Comparative Statement</button>
                                @if($canEdit)
                                    <button class="btn btn-outline-light btn-xs rajdhani" data-toggle="modal" data-target="#addQuotationModal"><i class="fas fa-plus"></i></button>
                                @endif
                            </div>
                        </div>
                        <div class="p-2">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 8px;">
                                @foreach($sortedQ->take(3) as $qi => $q)
                                @php 
                                    $isWinner = ($qi === 0);
                                    $isActive = ((float)$q->qte_price === (float)$purchase->pcs_price);
                                @endphp
                                <div style="background: rgba(255,255,255,0.02); border: 1px solid {{ $isActive ? 'var(--rd-success)' : 'rgba(255,255,255,0.08)' }}; border-radius: 6px; padding: 8px; position:relative;">
                                    @if($isActive)
                                        <div style="position:absolute; top:-6px; right:-6px; background:var(--rd-success); color:#fff; font-size:8px; font-weight:bold; padding:2px 6px; border-radius:8px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">SELECTED</div>
                                    @endif
                                    <div style="font-size: 9px; color: var(--rd-text3); margin-bottom: 2px;">
                                        <i class="fas fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($q->qte_date ?? $purchase->pcs_date)->format('d M y') }}
                                    </div>
                                    <div style="font-family: 'Rajdhani', sans-serif; font-size: 13px; font-weight: 700; color: {{ $isActive ? 'var(--rd-success)' : 'var(--rd-text1)' }}; margin-bottom: 4px; line-height: 1.1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $q->firm?->frm_name ?? $q->qte_firmname }}">
                                        {{ $q->firm?->frm_name ?? $q->qte_firmname }}
                                    </div>
                                    <div style="font-family: 'Rajdhani', sans-serif; font-size: 15px; font-weight: 800; color: #fff;">
                                        PKR {{ number_format($q->qte_price) }}
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ RIGHT PANE (25%) ============ --}}
                <div style="display:flex;flex-direction:column;gap:14px;">

                    {{-- Recent Approved Cases --}}
                    <div class="dg-box">
                        <div class="dg-box-hdr">
                            <div class="dg-sec-label" style="margin-bottom:0;"><i class="fas fa-check-circle fa-xs"></i> Recent Approved Cases</div>
                        </div>
                        <div class="dg-items-wrap" style="max-height: 380px;">
                            <table class="dg-items-table">
                                <thead>
                                    <tr>
                                        <th class="pl-3" style="width: 35px;">S.No</th>
                                        <th>Title of Case</th>
                                        <th class="text-right">Approved Amount</th>
                                        <th class="text-center pr-3">Items</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($recentApproved ?? collect()) as $ri => $rc)
                                    <tr class="cursor-pointer" onclick="window.location='{{ route('nrdi.procurement.purchase_cases.show', $rc->pcs_id) }}'" style="cursor: pointer;">
                                        <td class="pl-3 text-muted">{{ $ri + 1 }}</td>
                                        <td>
                                            <div class="text-white" style="font-size: 11px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 160px;">{{ $rc->pcs_title }}</div>
                                            <div class="text-muted" style="font-size: 9px;">{{ \Carbon\Carbon::parse($rc->pcs_date)->format('d M, Y') }}</div>
                                        </td>
                                        <td class="text-right font-weight-bold" style="color: var(--rd-success); font-size: 11px;">{{ number_format($rc->pcs_price) }}</td>
                                        <td class="text-center pr-3 text-muted" style="font-size: 11px;">{{ $rc->items_count }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted small py-3">No approved cases yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Related Files --}}
                    <div class="dg-box">
                        <div class="dg-box-hdr">
                            <div class="dg-sec-label" style="margin-bottom:0;"><i class="fas fa-paperclip fa-xs"></i> Related Files</div>
                            @if($canEdit)
                                <button class="btn btn-outline-light btn-xs rajdhani" data-toggle="modal" data-target="#caseAttachmentModal"><i class="fas fa-plus"></i></button>
                            @endif
                        </div>
                        <div class="p-3">
                            @forelse($purchase->attachments as $file)
                                <div class="d-flex align-items-center mb-2 p-2 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                    <i class="far fa-file-pdf text-danger mr-2" style="font-size: 16px;"></i>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="small font-weight-bold text-white text-nowrap" style="overflow: hidden; text-overflow: ellipsis; font-size: 11px;">{{ $file->pat_filename }}</div>
                                        <div class="text-muted" style="font-size: 9px;">{{ \Carbon\Carbon::parse($file->created_at)->format('d M, Y') }}</div>
                                    </div>
                                    <a href="{{ url('storage/'.$file->pat_path) }}" target="_blank" class="btn btn-xs btn-outline-primary ml-1"><i class="fas fa-download"></i></a>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted small">No documents attached.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@include('purchase.initiation.partials.modals')

{{-- Financial Intelligence Modal --}}
<div class="modal fade" id="financialIntelligenceModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background: #000c1a; border: 1px solid var(--rd-accent); border-radius: 15px; overflow: hidden; box-shadow: 0 0 40px rgba(0,0,0,0.8);">
            <div class="modal-header border-bottom border-dark py-2 px-3" style="background: rgba(23,162,184,0.05);">
                <h5 class="modal-title rajdhani font-weight-bold text-accent" style="letter-spacing: 1px;">
                    <i class="fas fa-microchip mr-2"></i> FINANCIAL INTELLIGENCE DASHBOARD
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="chart-container-box p-3 rounded border border-dark h-100" style="background: rgba(255,255,255,0.02);">
                            <div class="small text-muted rajdhani font-weight-bold mb-3"><i class="fas fa-percentage mr-1"></i> BUDGET PULSE</div>
                            <div style="height: 220px; position: relative;" class="d-flex align-items-center justify-content-center">
                                <canvas id="chartBudgetPulse"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 mb-4">
                        <div class="chart-container-box p-3 rounded border border-dark h-100" style="background: rgba(255,255,255,0.02);">
                            <div class="small text-muted rajdhani font-weight-bold mb-3"><i class="fas fa-balance-scale mr-1"></i> BID COMPARISON</div>
                            <div style="height: 220px; position: relative;" class="d-flex align-items-center justify-content-center">
                                <canvas id="chartBidComparison"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-4">
                        <div class="chart-container-box p-3 rounded border border-dark h-100" style="background: rgba(255,255,255,0.02);">
                            <div class="small text-muted rajdhani font-weight-bold mb-3"><i class="fas fa-cubes mr-1"></i> ITEM IMPACT</div>
                            <div style="height: 220px; position: relative;" class="d-flex align-items-center justify-content-center">
                                <canvas id="chartItemImpact"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top border-dark py-2 px-3 d-flex justify-content-between" style="background: rgba(0,0,0,0.2);">
                <div id="dashboardStatusLine" class="small text-muted font-italic">
                    <i class="fas fa-check-circle text-success mr-1"></i> Ready
                </div>
                <button type="button" class="btn btn-secondary btn-xs px-3 rajdhani font-weight-bold" data-dismiss="modal">CLOSE</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('plugins/chartjs4/chart.umd.js') }}"></script>
<script>
function scrollToUserComment(elementId) {
    let container = document.getElementById('conversational-comments-box');
    let element = document.getElementById(elementId);
    if (element && container) {
        container.scrollTop = element.offsetTop - container.offsetTop;
        element.style.transition = "all 0.5s";
        element.style.backgroundColor = "rgba(23, 162, 184, 0.2)";
        setTimeout(() => {
            element.style.backgroundColor = "transparent";
        }, 1500);
    }
}

function toggleRemarks(id) {
    const el = document.getElementById(id);
    if (el) {
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }
}

(function(){
    const dsU={{ $utilizedBudget }}, dsC={{ $caseValue }}, dsR={{ max(0,$balanceAfter) }};
    setTimeout(() => {
        document.getElementById('dgProgU')?.classList.add('anim');
        document.getElementById('dgProgR')?.classList.add('anim');
    }, 300);

    const co={ responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} } };
})();

// =========================================================================
// RDWIS FINANCIAL INTELLIGENCE DASHBOARD LOGIC (MODAL)
// =========================================================================
let pulseChart, comparisonChart, impactChart, trendChart;

function updateDashboardStatus(msg, isError = false) {
    const el = document.getElementById('dashboardStatusLine');
    if (el) {
        el.innerHTML = isError ? `<span class="text-danger"><i class="fas fa-exclamation-circle"></i> ${msg}</span>` : `<i class="fas fa-check-circle text-success"></i> ${msg}`;
    }
}

function renderFinancialDashboard() {
    console.log("RDWIS Dashboard: Command Received.");
    updateDashboardStatus("Processing Intelligence Data...");

    if (typeof Chart === 'undefined') {
        updateDashboardStatus("ERROR: Chart.js not loaded.", true);
        return;
    }

    try {
        if (pulseChart) pulseChart.destroy();
        if (comparisonChart) comparisonChart.destroy();
        if (impactChart) impactChart.destroy();
        if (trendChart) trendChart.destroy();

        // 1. BUDGET PULSE
        const ctx1 = document.getElementById('chartBudgetPulse').getContext('2d');
        pulseChart = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Utilized', 'This Case', 'Available'],
                datasets: [{
                    data: [{{ (float)$utilizedBudget }}, {{ (float)$caseValue }}, {{ (float)max(0, $balanceAfter) }}],
                    backgroundColor: ['#6c757d', '#17a2b8', '#28a745'],
                    borderWidth: 0
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: { legend: { position: 'bottom', labels: { color: '#aaa', font: { size: 10, family: 'Rajdhani' } } } }
            }
        });

        // 2. BID COMPARISON
        @php
            $qts = $purchase->quotes->sortBy('qte_price')->values();
            $hasQuotes = $qts->count() > 0;
            $qNames = $hasQuotes ? $qts->map(fn($q) => $q->firm?->frm_name ?? $q->qte_firmname) : collect(['Sample Firm A','Sample Firm B','Sample Firm C']);
            $qVals = $hasQuotes ? $qts->pluck('qte_price') : collect([150000, 220000, 185000]);
        @endphp
        const ctx2 = document.getElementById('chartBidComparison').getContext('2d');
        comparisonChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: {!! $qNames->toJson() !!},
                datasets: [{
                    label: 'Quote Price',
                    data: {!! $qVals->toJson() !!},
                    backgroundColor: '#17a2b8',
                    borderRadius: 5
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: '#222' }, ticks: { color: '#555', font: { size: 9 } } },
                    x: { grid: { display: false }, ticks: { color: '#aaa', font: { size: 10, family: 'Rajdhani' } } }
                }
            }
        });

        // 3. ITEM IMPACT
        @php
            $itms = $purchase->items;
            $hasItems = $itms->count() > 0;
            $itNames = $hasItems ? $itms->pluck('pci_desc') : collect(['Sample Item 1', 'Sample Item 2']);
            $itVals = $hasItems ? $itms->map(fn($i) => (float)$i->pci_price * (float)$i->pci_qty) : collect([50000, 35000]);
        @endphp
        const ctx3 = document.getElementById('chartItemImpact').getContext('2d');
        impactChart = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: {!! $itNames->toJson() !!},
                datasets: [{ data: {!! $itVals->toJson() !!}, backgroundColor: '#f39c12' }]
            },
            options: { 
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#222' }, ticks: { display: false } },
                    y: { grid: { display: false }, ticks: { color: '#aaa', font: { size: 9, family: 'Rajdhani' } } }
                }
            }
        });

        updateDashboardStatus("Intelligence Engine: Active & Verified");
    } catch (err) {
        console.error("Dashboard Render Failed:", err);
        updateDashboardStatus("CRITICAL ERROR: " + err.message, true);
    }
}

$(function() {
    $(document).on('shown.bs.modal', '#financialIntelligenceModal', function () {
        setTimeout(renderFinancialDashboard, 200);
    });
});
</script>

<script>
function selectFirm(caseId, quoteId) {
    if (!confirm('Switch to this firm? The case value will update accordingly.')) return;
    
    fetch('/purchase/select-firm/' + caseId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({ quote_id: quoteId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Error selecting firm: ' + err.message);
    });
}
</script>
@endsection