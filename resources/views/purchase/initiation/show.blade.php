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

/* ---- 2-col grid ---- */
.dg-grid { display:grid; grid-template-columns:1fr 300px; gap:18px; align-items:start; }
@media(max-width:1300px){ .dg-grid { grid-template-columns:1fr 280px; } }
@media(max-width:1024px){ .dg-grid { grid-template-columns:1fr 260px; } }
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
                $pctRemaining = max(0, ($balanceAfter / $totalBudget) * 100);
                $returnCount  = $purchase->decisions->where('pdec_action', 'return')->count();
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
                        <span class="dg-status-badge ml-2">{{ $purchase->pcs_status }}</span>
                    </div>
                    <a href="{{ route('purchase.initiation.index') }}" class="dg-back-btn">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Hub
                    </a>
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
                <div style="display:flex;flex-direction:column;gap:18px;">

                    {{-- Financial Row --}}
                    <div class="dg-fin-row">
                        <div class="dg-fin-col">
                            <div class="dg-sec-label"><i class="fas fa-chart-bar fa-xs"></i> Spending Breakdown</div>
                            <div style="margin-bottom:12px;"><div class="dg-chart-sm"><canvas id="dgDonutChart"></canvas></div></div>
                            <div><div class="dg-chart-sm"><canvas id="dgLineChart"></canvas></div></div>
                        </div>

                        <div class="dg-fin-col">
                            <div class="dg-sec-label"><i class="fas fa-chart-pie fa-xs"></i> Financial Picture</div>
                            <div class="dg-fin-nums">
                                <div class="dg-fin-card"><div class="dg-fin-label">Total Budget</div><div class="dg-fin-value">{{ number_format($totalBudget) }}</div></div>
                                <div class="dg-fin-card"><div class="dg-fin-label">Utilized</div><div class="dg-fin-value">{{ number_format($utilizedBudget) }}</div></div>
                                <div class="dg-fin-card"><div class="dg-fin-label">This Case</div><div class="dg-fin-value" style="color:var(--rd-info);">{{ number_format($caseValue) }}</div></div>
                                <div class="dg-fin-card"><div class="dg-fin-label">Bal. After</div><div class="dg-fin-value" style="color:{{ $balanceAfter < 0 ? 'var(--rd-danger)' : 'var(--rd-success)' }};">{{ number_format($balanceAfter) }}</div></div>
                            </div>
                            <div class="dg-prog-wrap">
                                <div class="dg-prog-utilized" id="dgProgU" style="--pw:{{ $pctUtilized }}%;"></div>
                                <div class="dg-prog-case" id="dgProgC" style="--lu:{{ $pctUtilized }}%; --pw:{{ $pctCase }}%; left:{{ $pctUtilized }}%;"></div>
                                <div class="dg-prog-remain" id="dgProgR" style="--pw:{{ $pctRemaining }}%; background:{{ $balanceAfter < 0 ? 'var(--rd-danger)' : '#1F6C35' }};"></div>
                            </div>
                            <div class="dg-prog-legend">
                                <div class="dg-leg-item"><div class="dg-leg-dot" style="background:var(--rd-text3);"></div>Utilized ({{ round($pctUtilized,1) }}%)</div>
                                <div class="dg-leg-item"><div class="dg-leg-dot" style="background:var(--rd-info);"></div>This Case ({{ round($pctCase,1) }}%)</div>
                                <div class="dg-leg-item"><div class="dg-leg-dot" style="background:{{ $balanceAfter < 0 ? 'var(--rd-danger)' : 'var(--rd-success)' }};"></div>Remaining ({{ round($pctRemaining,1) }}%)</div>
                            </div>
                            <div style="margin-top:12px;flex:1;"><div class="dg-chart-wrap"><canvas id="dgBudgetChart"></canvas></div></div>
                        </div>
                    </div>

                    {{-- Quotations / CS --}}
                    <div class="dg-box">
                        <div class="dg-box-hdr">
                            <div class="dg-sec-label" style="margin-bottom:0;"><i class="fas fa-list-ol fa-xs"></i> Quotation Comparison</div>
                            <div class="dg-box-hdr-right">
                                @if($canEdit)
                                    <button class="btn btn-primary btn-xs rajdhani px-3" data-toggle="modal" data-target="#addQuoteModal"><i class="fas fa-plus mr-1"></i> ADD VENDOR</button>
                                @endif
                                <button class="dg-cs-btn" data-toggle="modal" data-target="#detailedCSModal"><i class="fas fa-balance-scale mr-1"></i> Full CS Sheet</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="dg-items-table">
                                <thead>
                                    <tr>
                                        <th class="pl-4">Firm Name</th>
                                        <th class="text-right">Price (PKR)</th>
                                        <th class="text-center">L-Ranking</th>
                                        <th class="text-right pr-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchase->quotes->sortBy('qte_price') as $q)
                                    <tr>
                                        <td class="pl-4 font-weight-bold text-white">{{ $q->firm->frm_name ?? $q->qte_firmname }}</td>
                                        <td class="text-right font-weight-bold text-primary">Rs. {{ number_format($q->qte_price) }}</td>
                                        <td class="text-center"><span class="badge {{ $loop->first ? 'badge-success' : 'badge-secondary' }} px-3">L{{ $loop->iteration }}</span></td>
                                        <td class="text-right pr-4">@if($loop->first)<span class="text-success rajdhani small font-weight-bold">WINNER (L1)</span>@else - @endif</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted small">No quotations added yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Items List --}}
                    <div class="dg-box">
                        <div class="dg-box-hdr">
                            <div class="dg-sec-label" style="margin-bottom:0;"><i class="fas fa-boxes fa-xs"></i> Items Profile</div>
                            @if($canEdit)
                                <button class="btn btn-outline-light btn-xs rajdhani" data-toggle="modal" data-target="#addItemModal"><i class="fas fa-plus"></i></button>
                            @endif
                        </div>
                        <div class="dg-items-wrap">
                            <table class="dg-items-table">
                                <thead>
                                    <tr>
                                        <th class="pl-4">Description</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right pr-4">Sub Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->items as $item)
                                    <tr>
                                        <td class="pl-4">{{ $item->pci_desc }}</td>
                                        <td class="text-center font-weight-bold">{{ $item->pci_qty }} {{ $item->pci_qtyunit }}</td>
                                        <td class="text-right pr-4 font-weight-bold text-white">{{ number_format($item->pci_qty * $item->pci_price) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                {{-- ============ RIGHT PANE ============ --}}
                <div class="dg-right">
                    
                    {{-- Decision Panel (Includes Release/Hold logic) --}}
                    @include('approvals._action_box')

                    {{-- Decision Trail --}}
                    @include('approvals._decision_trail')

                    {{-- Attachments --}}
                    <div class="dg-panel-r">
                        <div class="dg-panel-r-hdr">
                            <i class="fas fa-paperclip text-accent"></i>
                            <span class="dg-panel-r-title">Related Files</span>
                        </div>
                        <div class="p-3">
                            @forelse($purchase->attachments as $file)
                                <div class="d-flex align-items-center mb-2 p-2 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                    <i class="far fa-file-pdf text-danger mr-3" style="font-size: 20px;"></i>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="small font-weight-bold text-white text-nowrap" style="overflow: hidden; text-overflow: ellipsis;">{{ $file->pat_filename }}</div>
                                        <div class="text-xs text-muted">{{ \Carbon\Carbon::parse($file->created_at)->format('d M, Y') }}</div>
                                    </div>
                                    <a href="{{ url('storage/'.$file->pat_path) }}" target="_blank" class="btn btn-xs btn-outline-primary ml-2"><i class="fas fa-download"></i></a>
                                </div>
                            @empty
                                <div class="text-center py-3 text-muted small">No documents attached.</div>
                            @endforelse

                            @if($canEdit)
                                <button class="btn btn-outline-primary btn-block btn-sm rajdhani mt-2" data-toggle="modal" data-target="#caseAttachmentModal">
                                    <i class="fas fa-plus mr-1"></i> ATTACH DOCUMENTS
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

@include('purchase.initiation.partials.modals')

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
(function(){
    const dsU={{ $utilizedBudget }}, dsC={{ $caseValue }}, dsR={{ max(0,$balanceAfter) }};
    setTimeout(() => {
        document.getElementById('dgProgU')?.classList.add('anim');
        document.getElementById('dgProgR')?.classList.add('anim');
    }, 300);

    const co={ responsive:true, maintainAspectRatio:false, plugins:{ legend:{display:false} } };

    new Chart(document.getElementById('dgBudgetChart'),{
        type:'bar',
        data:{
            labels:['Utilized','This Case','Remaining'],
            datasets:[{
                data:[dsU,dsC,dsR],
                backgroundColor:['rgba(108,117,125,0.6)','rgba(23,162,184,0.6)','rgba(40,167,69,0.6)'],
                borderColor:['#6c757d','#17a2b8','#28a745'],
                borderWidth:1, borderRadius:5
            }]
        },
        options:co
    });

    new Chart(document.getElementById('dgDonutChart'),{
        type:'doughnut',
        data:{
            labels:['Equipment','Tools','Other'],
            datasets:[{data:[68,22,10],backgroundColor:['rgba(23,162,184,0.8)','rgba(201,162,39,0.8)','rgba(40,167,69,0.8)'],borderColor:'#0d1f3c',borderWidth:2}]
        },
        options:{...co, cutout:'65%'}
    });

    new Chart(document.getElementById('dgLineChart'),{
        type:'line',
        data:{
            labels:['Oct','Nov','Dec','Jan','Feb','Mar'],
            datasets:[{ label:'Expenditure', data:[420000,680000,510000,890000,1100000,1245800], borderColor:'#c9a227', tension:0.4, fill:true }]
        },
        options:co
    });
})();
</script>
@endsection
