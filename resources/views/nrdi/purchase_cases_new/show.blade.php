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
.dg-grid { display:grid; grid-template-columns:60% 40%; gap:18px; align-items:start; }
@media(max-width:1300px){ .dg-grid { grid-template-columns:60% 40%; } }
@media(max-width:1024px){ .dg-grid { grid-template-columns:1fr; } }
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

<div class="content-wrapper dg-page zoom-out">

    <div class="p-3 pt-4">
        <div class="container-fluid">
            
            @php
                $winnerQuote  = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->first() : null;
                $sortedQ      = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->values() : collect([]);

                $caseValue      = (float)($purchase->pcs_price ?? ($winnerQuote?->qte_price ?? 0));
                
                // Image terminologies implementation
                $finReceived    = (float)($head->prj_aprvcost ?? 0);
                $finBalance     = (float)($head->hed_balance ?? $finReceived);
                $finExpenditure = $finReceived - $finBalance;
                $finCommitments = 0; // Commitments not directly available
                $finInProcess   = $caseValue;
                $finAvailable   = $finBalance - $finCommitments - $finInProcess;
                
                // For progress bar if still needed somewhere else
                $totalBudget    = $finReceived;
                $utilizedBudget = $finExpenditure;
                $balanceAfter   = $finAvailable;
                $pctUtilized  = $totalBudget > 0 ? ($utilizedBudget / $totalBudget) * 100 : 0;
                $pctCase      = $totalBudget > 0 ? ($caseValue / $totalBudget) * 100 : 0;
                $pctRemaining = $totalBudget > 0 ? max(0, ($balanceAfter / $totalBudget) * 100) : 0;
                
                $service = app(\App\Services\PurchaseApprovalService::class);
                $currentStatusDisplay = $service->getStatusDisplayName($purchase->pcs_status);
                
                // Variable overrides for cross-role compatibility
                $isInitiator = in_array(strtolower(trim((string)Auth::user()->acc_untarea)), ['prj', 'rdwprj', 'division']);
                $canEdit = $isInitiator && in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);
                $backRoute = $isInitiator ? route('purchase.initiation.index') : route('nrdi.purchase_cases_new.index');
            @endphp

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

                {{-- ============ LEFT PANE: CONSOLIDATED PURCHASE CASE ============ --}}
                <div class="dg-box h-100" style="display:flex; flex-direction:column;">
                    <div class="dg-box-hdr">
                        <div class="dg-sec-label" style="margin-bottom:0;"><i class="fas fa-file-invoice-dollar fa-xs"></i> Purchase Case </div>
                        <div class="dg-box-hdr-right d-flex gap-2">
                            <a href="{{ route('purchase.case_detail', $purchase->pcs_id) }}" target="_blank" class="btn btn-sm btn-outline-info rajdhani font-weight-bold" style="padding:4px 12px; font-size:11px; border-radius: 6px; border-color: rgba(23,162,184,0.4); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <i class="fas fa-list-alt mr-1"></i> CASE DETAIL
                            </a>
                            <a href="{{ route('purchase.market_research', $purchase->pcs_id) }}" target="_blank" class="btn btn-sm btn-outline-success rajdhani font-weight-bold" style="padding:4px 12px; font-size:11px; border-radius: 6px; border-color: rgba(40,167,69,0.4); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <i class="fas fa-search mr-1"></i> MARKET RESEARCH
                            </a>
                            <a href="{{ $backRoute }}" class="dg-back-btn" style="padding: 6px 15px; font-size: 12px;">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-3" style="flex:1; overflow-y:auto;">
                        
                        {{-- 0. Case Header Metadata (Moved inside) --}}
                        <div class="mb-4 d-flex align-items-start gap-4">
                            <div style="flex: 1;">
                                @if($canEdit)
                                    <div class="mb-2 d-flex align-items-center">
                                        <strong style="color:var(--rd-text3); width: 160px; display:inline-block; font-size: 13px;"><i class="fas fa-tag text-secondary mr-2"></i>CASE TITLE:</strong>
                                        <form action="{{ route('purchase.update_core', $purchase->pcs_id) }}" method="POST" class="d-flex align-items-center flex-grow-1" style="margin: 0;">
                                            @csrf
                                            <input type="text" name="pcs_title" class="edit-input flex-grow-1 mr-3" style="font-size: 13px !important; padding: 4px 12px !important;" value="{{ $purchase->pcs_title }}">
                                            <button type="submit" class="btn btn-primary btn-xs rajdhani font-weight-bold px-3 py-1">SAVE TITLE</button>
                                        </form>
                                    </div>
                                @else
                                    <div class="d-flex align-items-start mb-2" style="font-size: 13px;">
                                        <strong style="color:var(--rd-text3); width: 160px; display:inline-block; flex-shrink: 0;"><i class="fas fa-tag text-secondary mr-2"></i>CASE TITLE:</strong>
                                        <div class="font-weight-bold text-white">
                                            {{ $purchase->pcs_title }}
                                        </div>
                                    </div>
                                @endif
                                <div class="d-flex flex-column" style="gap: 6px; font-size: 13px; color: var(--rd-text2);">
                                    <div><strong style="color:var(--rd-text3); width: 160px; display:inline-block;"><i class="fas fa-hashtag text-secondary mr-2"></i>CASE ID:</strong> <span class="text-white font-weight-bold">#{{ $purchase->pcs_id }}</span></div>
                                    <div><strong style="color:var(--rd-text3); width: 160px; display:inline-block;"><i class="far fa-calendar-alt text-secondary mr-2"></i>DATE:</strong> {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}</div>
                                    <div><strong style="color:var(--rd-text3); width: 160px; display:inline-block;"><i class="fas fa-project-diagram text-secondary mr-2"></i>PROJECT:</strong> <span class="text-white">{{ $purchase->project?->prj_code ?? $purchase->pcs_hed_id }}</span></div>
                                    <div><strong style="color:var(--rd-text3); width: 160px; display:inline-block;"><i class="fas fa-building text-secondary mr-2"></i>DIVISION:</strong> <span class="text-white">{{ $purchase->unit?->unt_name ?? $purchase->pcs_unt_id }}</span></div>
                                </div>
                            </div>
                            
                            {{-- Financial Overview (Image terminologies logic) --}}
                            <div class="text-right d-flex flex-column align-items-end" style="border-left: 1px solid rgba(255,255,255,0.05); padding-left: 20px; font-size: 12px; color: var(--rd-text2);">
                                <button class="btn btn-sm btn-info rajdhani font-weight-bold px-3 mb-3 shadow-sm" data-toggle="modal" data-target="#financialIntelligenceModal" style="font-size: 10px; border-radius: 6px; letter-spacing: 0.5px;">
                                    <i class="fas fa-chart-line mr-1"></i> VIEW FINANCIAL DETAILS
                                </button>
                                
                                <div style="display: grid; grid-template-columns: auto auto; gap: 4px 16px; text-align: right;">
                                    <div>Received</div>                     <div class="text-white">{{ number_format($finReceived) }}</div>
                                    <div>Expenditure</div>                  <div class="text-white">{{ number_format($finExpenditure) }}</div>
                                    <div class="font-weight-bold text-white">Balance</div>   <div class="font-weight-bold text-white">{{ number_format($finBalance) }}</div>
                                    <div>Commitments</div>                  <div class="text-white">{{ number_format($finCommitments) }}</div>
                                    <div>In Process</div>                   <div class="text-white">{{ number_format($finInProcess) }}</div>
                                    <div class="font-weight-bold text-success">Available</div> <div class="font-weight-bold text-success" style="font-size: 14px;">{{ number_format($finAvailable) }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dg-divider mb-4 mt-2" style="background: rgba(255,255,255,0.05);"></div>
                        
                        {{-- 1. Items Section --}}
                        <div class="mb-4">
                            <div class="dg-sec-label mb-2"><i class="fas fa-boxes fa-xs"></i> Items</div>
                            <div class="dg-items-wrap" style="max-height: 250px; border: 1px solid rgba(255,255,255,0.05); border-radius: 8px;">
                                <table class="dg-items-table">
                                    <thead>
                                        <tr>
                                            <th class="pl-3" style="width: 50px;">S.No</th>
                                            <th>Description</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right pr-3">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchase->items as $idx => $item)
                                        <tr>
                                            <td class="pl-3 text-muted">{{ $idx + 1 }}</td>
                                            <td>{{ $item->pci_desc }}</td>
                                            <td class="text-center font-weight-bold">{{ $item->pci_qty }} {{ $item->pci_qtyunit }}</td>
                                            <td class="text-right pr-3 font-weight-bold text-white">{{ number_format($item->pci_price) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- 2. Quotations Section --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="dg-sec-label mb-0"><i class="fas fa-list-ol fa-xs"></i> Quotations</div>
                                <button class="dg-cs-btn" data-toggle="modal" data-target="#detailedCSModal"><i class="fas fa-balance-scale mr-1"></i> COMPARATIVE STATEMENT</button>
                            </div>
                            <div class="table-responsive" style="border: 1px solid rgba(255,255,255,0.05); border-radius: 8px;">
                                <table class="dg-items-table">
                                    <thead>
                                        <tr>
                                            <th class="pl-3" style="width: 50px;">S.No</th>
                                            <th>Firm Name</th>
                                            <th class="text-right pr-3">Price (PKR)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchase->quotes->sortBy('qte_price') as $q)
                                        <tr style="{{ $loop->first ? 'background: rgba(40, 167, 69, 0.15) !important;' : '' }}">
                                            <td class="pl-3 {{ $loop->first ? 'text-success font-weight-bold' : 'text-muted' }}">{{ $loop->iteration }}</td>
                                            <td class="font-weight-bold {{ $loop->first ? 'text-success' : 'text-white' }}">{{ $q->firm->frm_name ?? $q->qte_firmname }}</td>
                                            <td class="text-right pr-3 font-weight-bold {{ $loop->first ? 'text-success' : 'text-info' }}">Rs. {{ number_format($q->qte_price) }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="3" class="text-center py-4 text-muted small">No quotations added yet.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- 3. Case Remarks Section --}}
                        <div class="mb-4">
                            <div class="dg-sec-label mb-2"><i class="fas fa-info-circle fa-xs"></i> Case Remarks</div>
                            <div class="p-3 rounded text-muted" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); font-size: 12px; line-height: 1.6;">
                                @if(!empty(trim($purchase->pcs_remarks)))
                                    {{ $purchase->pcs_remarks }}
                                @else
                                    <span class="opacity-50 italic">No remarks provided during case initiation.</span>
                                @endif
                            </div>
                        </div>

                        {{-- 4. Related Files Section --}}
                        <div>
                            <div class="dg-sec-label mb-2"><i class="fas fa-paperclip fa-xs"></i> Related Files</div>
                            <div class="d-flex flex-wrap gap-2">
                                @forelse($purchase->attachments as $file)
                                    <div class="d-flex align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); min-width: 200px;">
                                        <i class="far fa-file-pdf text-danger mr-2" style="font-size: 16px;"></i>
                                        <div class="flex-grow-1 overflow-hidden mr-2">
                                            <div class="small font-weight-bold text-white text-nowrap" style="overflow: hidden; text-overflow: ellipsis; font-size: 10px;">{{ $file->pat_filename }}</div>
                                        </div>
                                        <a href="{{ url('storage/'.$file->pat_path) }}" target="_blank" class="btn btn-xs btn-outline-primary" style="padding: 1px 5px;"><i class="fas fa-download" style="font-size: 10px;"></i></a>
                                    </div>
                                @empty
                                    <div class="text-muted small italic opacity-50">No files attached.</div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ============ RIGHT PANE ============ --}}
                <div class="dg-right">
                    
                    {{-- CONVERSATIONAL VIEW / MINUTE --}}
                    <div class="dg-panel-r">
                        <div class="dg-panel-r-hdr py-2 px-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-file-alt text-accent" style="font-size: 12px;"></i>
                                <span class="dg-panel-r-title" style="font-size: 11px;">Minute</span>
                            </div>
                             <div class="d-flex gap-2">
                                <a href="{{ route('purchase.minute_view', $purchase->pcs_id) }}" target="_blank" class="btn btn-sm btn-outline-primary rajdhani font-weight-bold" style="padding:4px 12px; font-size:11px; border-radius: 6px; border-color: rgba(0,123,255,0.4); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    <i class="fas fa-eye mr-1"></i> VIEW MINUTE
                                </a>
                            </div>
                        </div>
                        
                        <div class="p-3" id="conversational-comments-box" style="max-height: 600px; overflow-y: auto;">
                            
                            {{-- Decision Panel (Integrated as a Minute Entry) --}}
                            @include('approvals_new._action_box')

                            <div>
                                @forelse($purchase->decisions->sortByDesc('created_at') as $decision)
                                    @php
                                        $act = $decision->pdec_action;
                                        $color = 'primary';
                                        $actionVerb = 'Recommended';
                                        if($act == 'approve') { $color = 'success'; $actionVerb = 'Approved'; }
                                        elseif($act == 'return') { $color = 'warning'; $actionVerb = 'Returned'; }
                                        elseif($act == 'hold') { $color = 'warning'; $actionVerb = 'Reverted'; }
                                        elseif($act == 'reject' || $act == 'not_approved') { $color = 'danger'; $actionVerb = 'Not Recommended'; }
                                        
                                        $toStatusDisplay = $service->getStatusDisplayName($decision->pdec_to_status);
                                        $hasRemarks = !empty(trim(strip_tags($decision->pdec_remarks)));
                                    @endphp
                                    <div class="mb-4" id="user-comment-{{$decision->pdec_id}}">
                                        <div class="d-flex align-items-center justify-content-between mb-1 border-bottom border-secondary pb-1" style="border-bottom-style: dashed !important;">
                                            <div class="font-weight-bold rajdhani text-white" style="font-size: 14px;">
                                                <i class="fas fa-user-circle text-accent mr-1"></i> {{ $decision->account->acc_name }} 
                                                <span class="text-muted small ml-1" style="font-weight: 500;">({{ strtoupper($decision->pdec_role) }})</span>
                                                <span class="ml-2 pl-2 border-left border-secondary font-weight-bold" style="font-size: 10px; color: var(--rd-{{$color}}); letter-spacing: 0.5px;">
                                                    <i class="fas fa-caret-right mr-1"></i>{{ strtoupper($actionVerb) }}
                                                </span>
                                            </div>
                                            <span class="text-muted" style="font-size:9px; font-weight: 600;">
                                                {{ \Carbon\Carbon::parse($decision->created_at)->format('d M, h:i A') }}
                                            </span>
                                        </div>
                                        <div class="mt-2" style="line-height: 1.5; font-size:12px; color: var(--rd-text1); padding-left: 5px;">
                                            @if($hasRemarks)
                                                {!! $decision->pdec_remarks !!}
                                            @else
                                                <div class="font-italic text-muted" style="font-size: 11px; opacity: 0.7;">
                                                    <i class="fas fa-info-circle mr-1"></i> Case {{ strtolower($actionVerb) }} to <strong>{{ $toStatusDisplay }}</strong> without additional remarks.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted small py-3">No remarks yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Financial Overview moved to Header --}}

                </div>
            </div>
        </div>
    </div>

{{-- ============ FINANCIAL INTELLIGENCE DASHBOARD MODAL ============ --}}
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
                    {{-- Charts Grid --}}
                    <div class="row">
                        {{-- 1. Budget Pulse --}}
                        <div class="col-md-4 mb-4">
                            <div class="chart-container-box p-3 rounded border border-dark h-100">
                                <div class="small text-muted rajdhani font-weight-bold mb-3"><i class="fas fa-percentage mr-1"></i> BUDGET PULSE</div>
                                <div style="height: 220px; position: relative;" class="d-flex align-items-center justify-content-center">
                                    <canvas id="chartBudgetPulse"></canvas>
                                    <div id="fallbackPulse" class="text-muted small d-none"><i class="fas fa-exclamation-triangle mr-1"></i> Data missing</div>
                                </div>
                            </div>
                        </div>
                        {{-- 2. Bid Comparison --}}
                        <div class="col-md-8 mb-4">
                            <div class="chart-container-box p-3 rounded border border-dark h-100">
                                <div class="small text-muted rajdhani font-weight-bold mb-3"><i class="fas fa-balance-scale mr-1"></i> BID COMPARISON</div>
                                <div style="height: 220px; position: relative;" class="d-flex align-items-center justify-content-center">
                                    <canvas id="chartBidComparison"></canvas>
                                    <div id="fallbackComparison" class="text-muted small d-none">No quotations to compare</div>
                                </div>
                            </div>
                        </div>
                        {{-- 3. Item Impact --}}
                        <div class="col-md-6 mb-4">
                            <div class="chart-container-box p-3 rounded border border-dark h-100">
                                <div class="small text-muted rajdhani font-weight-bold mb-3"><i class="fas fa-cubes mr-1"></i> ITEM IMPACT</div>
                                <div style="height: 220px; position: relative;" class="d-flex align-items-center justify-content-center">
                                    <canvas id="chartItemImpact"></canvas>
                                    <div id="fallbackImpact" class="text-muted small d-none">No items listed</div>
                                </div>
                            </div>
                        </div>
                        {{-- 4. Forecast Projection --}}
                        <div class="col-md-6 mb-4">
                            <div class="chart-container-box p-3 rounded border border-dark h-100">
                                <div class="small text-muted rajdhani font-weight-bold mb-3"><i class="fas fa-forward mr-1"></i> FORECAST</div>
                                <div style="height: 220px; position: relative;" class="d-flex align-items-center justify-content-center">
                                    <canvas id="chartForecastTrend"></canvas>
                                    <div id="fallbackForecast" class="text-muted small d-none">Unable to generate forecast</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-dark py-2 px-3 d-flex justify-content-between" style="background: rgba(0,0,0,0.2);">
                    <div id="dashboardStatusLine" class="small text-muted font-italic">
                        <i class="fas fa-sync fa-spin mr-1"></i> Waiting for initialization...
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-info btn-xs px-3 rajdhani font-weight-bold mr-2" onclick="renderFinancialDashboard()">
                            <i class="fas fa-sync mr-1"></i> FORCE REFRESH
                        </button>
                        <button type="button" class="btn btn-outline-light btn-xs px-3 rajdhani font-weight-bold" data-dismiss="modal">CLOSE DASHBOARD</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chart-container-box { background: rgba(0,0,0,0.3) !important; transition: all 0.3s; }
.chart-container-box:hover { border-color: var(--rd-accent) !important; background: rgba(23,162,184,0.02) !important; }
</style>

@if($isInitiator)
    @include('purchase.initiation.partials.modals')
@else
    @include('purchase.initiation.partials.modals')
@endif
@endsection

@section('scripts')
<script src="{{ asset('plugins/chartjs4/chart.umd.js') }}"></script>
<script>
let pulseChart, comparisonChart, impactChart, trendChart;

function updateDashboardStatus(msg, isError = false) {
    const line = document.getElementById('dashboardStatusLine');
    if (line) {
        line.innerHTML = `<i class="fas ${isError ? 'fa-times-circle text-danger' : 'fa-check-circle text-success'} mr-1"></i> ${msg}`;
    }
}

function renderFinancialDashboard() {
    console.log("RDWIS Dashboard: Command Received.");
    updateDashboardStatus("Processing Intelligence Data...");

    if (typeof Chart === 'undefined') {
        updateDashboardStatus("ERROR: Chart.js not loaded. Check Network.", true);
        return;
    }

    try {
        // 1. CLEANUP
        if (pulseChart) try { pulseChart.destroy(); } catch(e){}
        if (comparisonChart) try { comparisonChart.destroy(); } catch(e){}
        if (impactChart) try { impactChart.destroy(); } catch(e){}
        if (trendChart) try { trendChart.destroy(); } catch(e){}

        // 2. BUDGET PULSE
        const ctx1 = document.getElementById('chartBudgetPulse').getContext('2d');
        const spentVal = {{ (float)$utilizedBudget }};
        const caseVal = {{ (float)$caseValue }};
        const remainVal = {{ (float)max(0, $balanceAfter) }};
        
        pulseChart = new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Utilized', 'This Case', 'Available'],
                datasets: [{
                    data: (spentVal + caseVal + remainVal === 0) ? [33, 33, 34] : [spentVal, caseVal, remainVal],
                    backgroundColor: ['#6c757d', '#17a2b8', '#28a745'],
                    borderWidth: 0
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, cutout: '70%',
                plugins: { legend: { position: 'bottom', labels: { color: '#aaa', font: { size: 10, family: 'Rajdhani' } } } }
            }
        });

        // 3. BID COMPARISON
        @php
            $qts = $purchase->quotes->sortBy('qte_price')->values();
            $hasQuotes = $qts->count() > 0;
            $qNames = $hasQuotes ? $qts->map(fn($q) => $q->firm?->frm_name ?? $q->qte_firmname) : collect(['Sample Firm A','Sample Firm B','Sample Firm C']);
            $qVals = $hasQuotes ? $qts->pluck('qte_price') : collect([150000, 220000, 185000]);
            $isDemoQuotes = !$hasQuotes;
        @endphp
        const ctx2 = document.getElementById('chartBidComparison').getContext('2d');
        comparisonChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: {!! $qNames->toJson() !!},
                datasets: [{
                    label: 'Quote Price',
                    data: {!! $qVals->toJson() !!},
                    backgroundColor: {!! $qVals->map(fn($v, $k) => ($k === 0 && !$isDemoQuotes) ? '#28a745' : '#17a2b8')->toJson() !!},
                    borderRadius: 5
                }]
            },
            options: { 
                responsive: true, maintainAspectRatio: false, 
                plugins: {
                    legend: { display: false },
                    title: { display: {{ $isDemoQuotes ? 'true' : 'false' }}, text: 'NO QUOTES FOUND - SHOWING DEMO', color: '#f39c12', font: { size: 10 } }
                },
                scales: {
                    y: { grid: { color: '#222' }, ticks: { color: '#555', font: { size: 9 } } },
                    x: { grid: { display: false }, ticks: { color: '#aaa', font: { size: 10, family: 'Rajdhani' } } }
                }
            }
        });

        // 4. ITEM IMPACT
        @php
            $itms = $purchase->items;
            $hasItems = $itms->count() > 0;
            $itNames = $hasItems ? $itms->pluck('pci_desc') : collect(['Sample Item 1', 'Sample Item 2']);
            $itVals = $hasItems ? $itms->map(fn($i) => (float)$i->pci_price * (float)$i->pci_qty) : collect([50000, 35000]);
            $isDemoItems = !$hasItems;
        @endphp
        const ctx3 = document.getElementById('chartItemImpact').getContext('2d');
        impactChart = new Chart(ctx3, {
            type: 'horizontalBar',
            data: {
                labels: {!! $itNames->toJson() !!},
                datasets: [{ data: {!! $itVals->toJson() !!}, backgroundColor: '#f39c12' }]
            },
            options: { 
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false, 
                plugins: {
                    legend: { display: false },
                    title: { display: {{ $isDemoItems ? 'true' : 'false' }}, text: 'NO ITEMS FOUND - SHOWING DEMO', color: '#f39c12', font: { size: 10 } }
                },
                scales: {
                    x: { grid: { color: '#222' }, ticks: { display: false } },
                    y: { grid: { display: false }, ticks: { color: '#aaa', font: { size: 9, family: 'Rajdhani' } } }
                }
            }
        });

        // 5. FORECAST
        const ctx4 = document.getElementById('chartForecastTrend').getContext('2d');
        const totalBud = {{ (float)$totalBudget }};
        const utiBud = {{ (float)$utilizedBudget }};
        const caseValFore = {{ (float)$caseValue }};
        trendChart = new Chart(ctx4, {
            type: 'line',
            data: {
                labels: ['Start', 'Current', 'After Case', 'Future'],
                datasets: [{
                    label: 'Trend',
                    data: [0, utiBud, utiBud + caseValFore, utiBud + (caseValFore * 1.2)],
                    borderColor: '#17a2b8', backgroundColor: 'rgba(23,162,184,0.1)', fill: true, lineTension: 0.3
                }, {
                    label: 'Total Budget',
                    data: [totalBud, totalBud, totalBud, totalBud],
                    borderColor: '#dc3545', borderDash:[5,5], fill:false, pointRadius:0
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

        updateDashboardStatus("Intelligence Engine: Active & Verified");
        console.log("RDWIS Dashboard: Rendering Complete.");

    } catch (err) {
        console.error("Dashboard Render Failed:", err);
        updateDashboardStatus("CRITICAL ERROR: " + err.message, true);
    }
}

$(function() {
    // Main page progress bar animation
    setTimeout(() => {
        document.getElementById('dgProgU')?.classList.add('anim');
        document.getElementById('dgProgR')?.classList.add('anim');
    }, 500);

    // Modal trigger logic
    $(document).on('shown.bs.modal', '#financialIntelligenceModal', function () {
        // Small delay ensures canvas has finished transition and has dimensions
        setTimeout(renderFinancialDashboard, 200);
    });
});
</script>
@endsection