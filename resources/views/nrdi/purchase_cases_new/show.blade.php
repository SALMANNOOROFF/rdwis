@extends('welcome')

@section('content')
@php
    $winnerQuote  = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->first() : null;
    $sortedQ      = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->values() : collect([]);

    $caseValue      = (float)($purchase->pcs_price ?? ($winnerQuote?->qte_price ?? 0));
    
    // Image terminologies implementation (Legacy Logic)
    $finReceived    = (float)($head->received ?? 0);
    $finBalance     = (float)($head->balance ?? 0);
    $finExpenditure = (float)($head->expenditure ?? 0);
    $finCommitments = (float)($head->commitments ?? 0);
    $finInProcess   = (float)($head->in_process ?? 0);
    $finAvailable   = (float)($head->available ?? 0);
    $finCanBeSpent  = (float)($head->can_be_spent ?? 0);
    $finAllocation  = (float)($head->allocation ?? 0);

    
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
    $userArea = strtolower(trim((string)Auth::user()->acc_untarea));
    $isInitiator = in_array($userArea, ['prj', 'rdwprj', 'division']);
    $isDProc     = str_contains($userArea, 'proc');
    $isDraft     = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);

    // DProc can also edit quotations in Draft stage
    $canEdit        = $isInitiator && $isDraft;
    $canAddQuotes   = ($isInitiator || $isDProc) && $isDraft;
    $dprocSaved     = $purchase->decisions->where('pdec_action', 'dproc_save')->isNotEmpty();

    $backRoute = $isInitiator ? route('purchase.initiation.index') : route('nrdi.purchase_cases.index');
@endphp

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

.pc-edit-wrap .edit-only { display: none !important; }
.pc-edit-wrap .view-only { display: block; }
.pc-edit-wrap.is-editing .edit-only { display: block !important; }
.pc-edit-wrap.is-editing .edit-only.d-flex { display: flex !important; }
.pc-edit-wrap.is-editing .edit-only.badge { display: inline-block !important; }
.pc-edit-wrap.is-editing .pc-plus-btn.edit-only { display: inline-flex !important; }
.pc-edit-wrap.is-editing .view-only { display: none; }

.pc-edit-toggle-btn { width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
.pc-mini-save { padding: 4px 10px; font-size: 11px; border-radius: 6px; }
.pc-plus-btn { width: 22px; height: 22px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.03); color: var(--rd-accent); }
.pc-plus-btn:hover { background: rgba(255,255,255,0.06); color: #fff; border-color: var(--rd-accent); }
.pc-edit-card { border: 1px dashed rgba(255,255,255,0.18); border-radius: 8px; padding: 10px; background: rgba(255,255,255,0.02); }

/* Multi-column quote table styles - DARK THEME */
#pcMultiQuoteTable { border: 1px solid rgba(255,255,255,0.1) !important; background: #000c1a; table-layout: fixed; border-collapse: separate; border-spacing: 0; width: auto; }
#pcMultiQuoteTable th, #pcMultiQuoteTable td { border: 1px solid rgba(255,255,255,0.08) !important; font-size: 12px; vertical-align: middle; padding: 6px 10px; color: #fff; overflow: hidden; text-overflow: ellipsis; }
#pcMultiQuoteTable thead th { border-bottom: 2px solid var(--rd-accent) !important; background: #001226; color: #888; font-weight: 700; letter-spacing: 0.5px; position: sticky; top: 0; z-index: 20; }
#pcMultiQuoteBody tr:hover td { background-color: rgba(255,255,255,0.02); }
.pc-price-input { border: 1px solid rgba(255,255,255,0.15) !important; height: 30px !important; font-size: 13px !important; font-weight: 700 !important; color: var(--rd-accent) !important; padding: 2px 8px !important; width: 100% !important; border-radius: 4px !important; text-align: center; background: rgba(0,0,0,0.2); transition: all 0.2s; }
.pc-price-input:focus { border-color: var(--rd-accent) !important; box-shadow: 0 0 0 2px rgba(243,156,18,0.1) !important; background: rgba(0,0,0,0.4) !important; outline: none; }
.pc-price-input:disabled { opacity: 0.3; cursor: not-allowed; background: rgba(0,0,0,0.1) !important; border-color: rgba(255,255,255,0.05) !important; }
.pc-vendor-name-input { font-size: 12px !important; font-weight: 700 !important; height: 28px !important; border-color: rgba(255,255,255,0.15) !important; border-radius: 4px !important; background: rgba(0,0,0,0.2) !important; color: #fff !important; width: 100% !important; }
.pc-col-winner { background: rgba(40, 167, 69, 0.15) !important; }
#pcMultiQuoteTable tfoot td { position: sticky; bottom: 0; z-index: 20; background: #000c1a !important; border-top: 2px solid var(--rd-accent) !important; color: #fff !important; box-shadow: 0 -5px 15px rgba(0,0,0,0.5); }
.pc-item-sticky { position: sticky; left: 0; z-index: 25; background: #001226 !important; box-shadow: 3px 0 8px rgba(0,0,0,0.5); border-right: 1px solid rgba(255,255,255,0.12) !important; width: 350px !important; min-width: 350px !important; }
#pcMultiQuoteTable tfoot .pc-item-sticky { background: #000c1a !important; color: var(--rd-accent) !important; border-right: 1px solid rgba(255,255,255,0.12) !important; text-align: right; }









</style>

<div class="content-wrapper dg-page zoom-out pc-edit-wrap" id="pcEditWrap" data-can-edit="{{ $canEdit ? 1 : 0 }}">




    <div class="p-3 pt-4">
        <div class="container-fluid">
            


            <div class="dg-grid">

                {{-- ============ LEFT PANE: CONSOLIDATED PURCHASE CASE ============ --}}
                <div class="dg-box h-100" style="display:flex; flex-direction:column;">
                    <div class="dg-box-hdr">
                        <div class="dg-sec-label" style="margin-bottom:0;">
                            <i class="fas fa-file-invoice-dollar fa-xs"></i> Purchase Case
                            <span class="edit-only badge badge-info rajdhani ml-2" style="font-size:10px; letter-spacing:0.8px;">EDITING MODE</span>
                        </div>
                        <div class="dg-box-hdr-right d-flex gap-2">
                            @if($canEdit)
                                <button type="button" class="btn btn-outline-warning pc-edit-toggle-btn" id="pcEditToggleBtn" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </button>
                            @endif
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
                        @if($isInitiator && $dprocSaved && $isDraft)
                            <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" style="background: rgba(23,162,184,0.1); border: 1px solid rgba(23,162,184,0.2); border-radius: 8px;">
                                <i class="fas fa-info-circle mr-2 text-info"></i>
                                <div class="small font-weight-bold text-white">Director Procurement has added/updated quotations for this case.</div>
                            </div>
                        @endif

                        {{-- Case Header Metadata --}}
                        <div class="mb-4 d-flex align-items-start gap-4">
                            <div style="flex: 1;">
                                <div class="d-flex align-items-start mb-2" style="font-size: 13px;">
                                    <strong style="color:var(--rd-text3); width: 160px; display:inline-block; flex-shrink: 0;"><i class="fas fa-tag text-secondary mr-2"></i>CASE TITLE:</strong>
                                    <div class="view-only font-weight-bold text-white" id="pcTitleView">{{ $purchase->pcs_title }}</div>
                                    @if($canEdit)
                                        <form class="edit-only d-flex align-items-center flex-grow-1" id="pcTitleForm" style="gap:10px; margin:0;" action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="op" value="save_title">
                                            <input type="text" name="pcs_title" class="edit-input flex-grow-1" style="font-size: 13px !important; padding: 4px 12px !important;" value="{{ $purchase->pcs_title }}">
                                            <button type="submit" class="btn btn-primary pc-mini-save rajdhani font-weight-bold"><i class="fas fa-save mr-1"></i> SAVE</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="d-flex flex-column" style="gap: 6px; font-size: 13px; color: var(--rd-text2);">
                                    <div><strong style="color:var(--rd-text3); width: 160px; display:inline-block;"><i class="fas fa-hashtag text-secondary mr-2"></i>CASE ID:</strong> <span class="text-white font-weight-bold">#{{ $purchase->pcs_id }}</span></div>
                                    <div><strong style="color:var(--rd-text3); width: 160px; display:inline-block;"><i class="far fa-calendar-alt text-secondary mr-2"></i>DATE:</strong> {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}</div>
                                    <div><strong style="color:var(--rd-text3); width: 160px; display:inline-block;"><i class="fas fa-project-diagram text-secondary mr-2"></i>PROJECT:</strong> <span class="text-white">{{ $purchase->project?->prj_code ?? $purchase->pcs_hed_id }}</span></div>
                                    <div><strong style="color:var(--rd-text3); width: 160px; display:inline-block;"><i class="fas fa-building text-secondary mr-2"></i>DIVISION:</strong> <span class="text-white">{{ $purchase->unit?->unt_name ?? $purchase->pcs_unt_id }}</span></div>
                                </div>
                            </div>
                            
                            {{-- Financial Overview (High Density Text Summary) --}}
                            <div class="text-right d-flex flex-column align-items-end" style="border-left: 1px solid rgba(255,255,255,0.05); padding-left: 20px; font-size: 12px; min-width: 250px;">
                                <div class="d-flex justify-content-between align-items-center w-100 mb-3">
                                    <h6 class="rajdhani text-info font-weight-bold mb-0" style="font-size: 10px; letter-spacing: 1px;">FINANCIAL PULSE</h6>
                                    <button class="btn btn-xs btn-outline-info rajdhani font-weight-bold py-0" data-toggle="modal" data-target="#financialIntelligenceModal" style="font-size: 8px; border-radius: 4px;">
                                        <i class="fas fa-expand-arrows-alt mr-1"></i> FULL REPORT
                                    </button>
                                </div>
                                
                                <div class="w-100 rajdhani" style="display: grid; grid-template-columns: auto 1fr; gap: 4px 20px; text-align: left;">
                                    <div class="text-muted small">RECEIVED</div>
                                    <div class="text-white font-weight-bold text-right">{{ number_format($finReceived) }}</div>
                                    
                                    <div class="text-muted small">EXPENDITURE</div>
                                    <div class="text-danger font-weight-bold text-right">{{ number_format($finExpenditure) }}</div>
                                    
                                    <div class="text-white font-weight-bold small">BALANCE</div>
                                    <div class="text-white font-weight-bold text-right">{{ number_format($finBalance) }}</div>
                                    
                                    <div class="text-muted small">COMMITMENTS</div>
                                    <div class="text-warning font-weight-bold text-right">{{ number_format($finCommitments) }}</div>
                                    
                                    <div class="text-muted small">IN PROCESS</div>
                                    <div class="text-muted text-right">{{ number_format($finInProcess) }}</div>
                                    
                                    <div class="text-success font-weight-bold small border-top border-dark pt-1">AVAILABLE</div>
                                    <div class="text-success font-weight-bold text-right border-top border-dark pt-1">{{ number_format($finAvailable) }}</div>
                                    
                                    <div class="text-warning font-weight-bold small" style="font-size: 11px;">CAN BE SPENT</div>
                                    <div class="text-warning font-weight-bold text-right" style="font-size: 13px;">{{ number_format($finCanBeSpent) }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dg-divider mb-4 mt-2" style="background: rgba(255,255,255,0.05);"></div>
                        
                        {{-- 1. Items Section --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="dg-sec-label mb-0"><i class="fas fa-boxes fa-xs"></i> Items</div>
                                @if($canEdit)
                                    <button type="button" class="pc-plus-btn edit-only" id="pcAddItemInlineBtn" title="Add Item"><i class="fas fa-plus"></i></button>
                                @endif
                            </div>
                            <div class="dg-items-wrap" style="max-height: 250px; border: 1px solid rgba(255,255,255,0.05); border-radius: 8px;">
                                <table class="dg-items-table">
                                    <thead>
                                        <tr>
                                            <th class="pl-3" style="width: 50px;">S.No</th>
                                            <th>Description</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right pr-3">Price</th>
                                            <th class="text-right pr-3 edit-only" style="width: 100px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pcItemsBody">
                                        {{-- Items will be rendered by JS for LIFO support --}}
                                    </tbody>
                                </table>
                            </div>
                            @if($canEdit)
                                <div class="edit-only mt-2 pc-edit-card" id="pcInlineItemEditor" style="display:none; border-style: solid; border-color: var(--rd-accent);">
                                    <div class="text-accent small mb-2 font-weight-bold"><i class="fas fa-plus-circle mr-1"></i> ADD NEW ITEM</div>
                                    <form id="pcAddItemForm" class="d-flex align-items-end" style="gap:10px; margin:0;">
                                        <div class="flex-grow-1">
                                            <label class="text-muted small mb-1">Description / Item Name</label>
                                            <input name="item_desc" id="pcItemDesc" class="form-control form-control-sm" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.15);" required placeholder="Enter item details...">
                                        </div>
                                        <div style="width:100px;">
                                            <label class="text-muted small mb-1">Qty</label>
                                            <input name="item_qty" id="pcItemQty" type="number" step="0.01" value="1" class="form-control form-control-sm text-right" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.15);" required>
                                        </div>
                                        <div class="d-flex" style="gap:8px;">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="pcItemCancelBtn" title="Cancel"><i class="fas fa-times"></i></button>
                                            <button type="submit" class="btn btn-primary btn-sm px-3" title="Save Item"><i class="fas fa-check mr-1"></i> ADD</button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- 2. Quotations Section --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="dg-sec-label mb-0"><i class="fas fa-list-ol fa-xs"></i> Quotations</div>
                                <div class="d-flex align-items-center" style="gap:8px;">
                                @if($canAddQuotes)
                                    <button type="button" class="pc-plus-btn" data-toggle="modal" data-target="#pcAddQuoteModal" title="Add Quotation"><i class="fas fa-plus"></i></button>
                                @endif

                                    <button class="dg-cs-btn" data-toggle="modal" data-target="#detailedCSModal"><i class="fas fa-balance-scale mr-1"></i> COMPARATIVE STATEMENT</button>
                                </div>
                            </div>
                            <div class="table-responsive" style="border: 1px solid rgba(255,255,255,0.05); border-radius: 8px;">
                                <table class="dg-items-table">
                                    <thead>
                                        <tr>
                                            <th class="pl-3" style="width: 50px;">S.No</th>
                                            <th>Firm Name</th>
                                            <th class="text-right pr-3">Price (PKR)</th>
                                            <th class="text-right pr-3" style="width: 100px;">Action</th>

                                        </tr>
                                    </thead>
                                    <tbody id="pcQuotesBody">
                                        {{-- Rendered by JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- 3. Case Remarks Section --}}
                        <div class="mb-4">
                            <div class="dg-sec-label mb-2"><i class="fas fa-info-circle fa-xs"></i> Case Remarks</div>
                            <div class="view-only p-3 rounded text-muted" id="pcRemarksText" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); font-size: 12px; line-height: 1.6;">
                                @if(!empty(trim($purchase->pcs_remarks)))
                                    {{ $purchase->pcs_remarks }}
                                @else
                                    <span class="opacity-50 italic">No remarks provided during case initiation.</span>
                                @endif
                            </div>
                            @if($canEdit)
                                <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST" id="pcRemarksForm" class="edit-only">
                                    @csrf
                                    <input type="hidden" name="op" value="save_remarks">
                                    <textarea name="pcs_remarks" id="pcRemarksInput" class="form-control" rows="3" style="background: rgba(255,255,255,0.03); color: #fff; border: 1px solid var(--rd-accent); border-radius: 8px; font-size: 13px;">{{ $purchase->pcs_remarks }}</textarea>
                                    <div class="d-flex justify-content-end mt-2">
                                        <button type="submit" class="btn btn-primary btn-sm rajdhani font-weight-bold"><i class="fas fa-save mr-1"></i> SAVE REMARKS</button>
                                    </div>
                                </form>
                            @endif
                        </div>

                        {{-- 4. Related Files Section --}}
                        <div class="mb-4">
                            <div class="dg-sec-label mb-2"><i class="fas fa-paperclip fa-xs"></i> Related Files</div>
                            <div class="d-flex flex-wrap gap-2 mb-3" id="pcFilesWrap">
                                {{-- Rendered by JS --}}
                            </div>
                            @if($canEdit)
                                <div class="edit-only pc-edit-card" style="border-style: solid; border-color: var(--rd-accent);">
                                    <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST" enctype="multipart/form-data" id="pcFilesForm" class="d-flex align-items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="op" value="add_files">
                                        <div class="flex-grow-1">
                                            <input type="file" name="attachments[]" id="pcFilesInput" multiple class="form-control form-control-sm" style="background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.15);" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm rajdhani font-weight-bold"><i class="fas fa-upload mr-1"></i> UPLOAD</button>
                                    </form>
                                    <div class="text-muted small mt-1" style="font-size: 9px;">PDF/JPG/PNG/DOC/DOCX (max 10MB each)</div>
                                </div>
                            @endif
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
                            @if($isDProc && $isDraft)
                                <div class="mb-4 p-4 text-center rounded border border-warning" style="background: rgba(255,193,7,0.05);">
                                    <div class="mb-2"><i class="fas fa-users-cog fa-2x text-warning opacity-75"></i></div>
                                    <h6 class="rajdhani font-weight-bold text-white mb-2">COLLABORATION MODE</h6>
                                    <p class="small text-muted mb-3" style="line-height: 1.4;">You are collaborating on this <strong>Draft</strong> case. Use the quotation modal to add or update prices. The Division will release it once finalized.</p>
                                    <button type="button" class="btn btn-outline-warning btn-sm font-weight-bold px-4" data-toggle="modal" data-target="#pcAddQuoteModal">
                                        <i class="fas fa-plus-circle mr-1"></i> MANAGE QUOTATIONS
                                    </button>
                                </div>
                            @else
                                @include('approvals_new._action_box')
                            @endif

                            <div>
                                @forelse($purchase->decisions->sortByDesc('created_at') as $decision)
                                    @php
                                        $act = $decision->pdec_action;
                                        $color = 'primary';
                                        $actionVerb = 'Forwarded';
                                        if($act == 'approve') { $color = 'success'; $actionVerb = 'Approved'; }
                                        elseif($act == 'return') { $color = 'warning'; $actionVerb = 'Returned'; }
                                        elseif($act == 'hold') { $color = 'warning'; $actionVerb = 'Reverted'; }
                                        elseif($act == 'reject' || $act == 'not_approved') { $color = 'danger'; $actionVerb = 'Not Recommended'; }
                                        elseif($act == 'dproc_save') { $color = 'info'; $actionVerb = 'Updated Quotations'; }

                                        
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
                                            @if($decision->pdec_action == 'dproc_save')
                                                <div class="text-info font-weight-bold" style="font-size: 11px;">
                                                    <i class="fas fa-check-circle mr-1"></i> DProc updated quotations for this case.
                                                </div>
                                            @elseif($hasRemarks)
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

@if($canAddQuotes)

<div class="modal fade" id="pcAddQuoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 98%;">
        <div class="modal-content" style="background: #000c1a; border: 1px solid var(--rd-accent); border-radius: 8px; overflow: hidden; color: #fff; display: flex; flex-direction: column; height: 85vh;">
            
            {{-- HEADER --}}
            <div class="modal-header py-2 px-3 align-items-center flex-shrink-0" style="background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.1);">
                <div class="d-flex align-items-center flex-grow-1">
                    <i class="fas fa-file-invoice-dollar mr-2 text-accent" style="font-size: 18px;"></i>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0 rajdhani" style="color: #fff; font-size: 14px; letter-spacing: 1px;">Add Quotations</h6>
                        <div class="small text-muted" style="font-size: 10px;">Group: <span class="text-info">{{ $purchase->pcs_title }}</span> | ID: <span class="text-info">PC-{{ $purchase->pcs_id }}</span></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 mr-4">
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-2 small font-weight-bold text-muted">TAX TYPE:</label>
                        <select id="pcGlobalTaxType" class="form-control form-control-sm" style="width: 80px; height: 26px; font-size: 11px; background: rgba(0,0,0,0.3); color: #fff; border-color: rgba(255,255,255,0.1);">
                            <option value="GST">GST</option>
                            <option value="SST">SST</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-2 small font-weight-bold text-muted">TAX %:</label>
                        <input type="number" id="pcGlobalTaxPercent" class="form-control form-control-sm" value="18" style="width: 60px; height: 26px; font-size: 11px; background: rgba(0,0,0,0.3); color: #fff; border-color: rgba(255,255,255,0.1);">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3" id="pcAddVendorColBtn" style="height: 30px; font-size: 12px;"><i class="fas fa-user-plus mr-1"></i> Add Vendor</button>
                    <button type="button" class="close ml-2 text-white" data-dismiss="modal">&times;</button>
                </div>

            </div>

            {{-- BODY: scrollable table area --}}
            <div class="flex-grow-1" style="overflow: auto; min-height: 0;">
                <table id="pcMultiQuoteTable">
                    <thead id="pcMultiQuoteHead">
                        <tr>
                            <th style="width: 20%;" class="pc-item-sticky">ITEM DESCRIPTION</th>
                            {{-- Vendor columns injected by JS --}}
                        </tr>
                    </thead>
                    <tbody id="pcMultiQuoteBody">
                        {{-- Item rows injected by JS --}}
                    </tbody>
                    <tfoot id="pcMultiQuoteFoot">
                        {{-- Totals injected by JS --}}
                    </tfoot>
                </table>
            </div>

            {{-- FOOTER --}}
            <div class="py-2 px-3 d-flex justify-content-between align-items-center flex-shrink-0" style="background: rgba(255,255,255,0.03); border-top: 1px solid rgba(255,255,255,0.1);">
                <div class="small text-muted" style="font-size: 11px;"><i class="fas fa-info-circle mr-1"></i> Ensure all vendor names are entered before saving.</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-light btn-sm font-weight-bold px-4" data-dismiss="modal" style="height: 32px; font-size: 12px; letter-spacing: 0.5px;">CLOSE</button>
                    <button type="button" class="btn btn-success btn-sm font-weight-bold px-4" id="pcSaveAllQuotesBtn" style="height: 32px; font-size: 12px; letter-spacing: 0.5px;"><i class="fas fa-save mr-1"></i> SAVE QUOTATIONS</button>
                </div>
            </div>

        </div>
    </div>
</div>
@endif


<datalist id="dbFirmsList">
    @foreach($firms as $f)
        <option value="{{ $f->frm_name }}"></option>
    @endforeach
</datalist>

@if($canEdit)


<div class="modal fade" id="pcEditRemarksModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #000c1a; border: 1px solid var(--rd-accent); border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-2 px-3" style="background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.06);">
                <h6 class="modal-title rajdhani font-weight-bold text-white" style="letter-spacing: 1px;">CASE REMARKS</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST" id="pcRemarksForm">
                    @csrf
                    <input type="hidden" name="op" value="save_remarks">
                    <textarea name="pcs_remarks" id="pcRemarksInput" class="form-control" rows="6" style="background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.15);">{{ $purchase->pcs_remarks }}</textarea>
                    <div class="d-flex justify-content-end mt-3" style="gap:10px;">
                        <button type="button" class="btn btn-outline-light btn-sm rajdhani font-weight-bold" data-dismiss="modal">CANCEL</button>
                        <button type="submit" class="btn btn-primary btn-sm rajdhani font-weight-bold">SAVE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pcAddFilesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #000c1a; border: 1px solid var(--rd-accent); border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-2 px-3" style="background: rgba(255,255,255,0.03); border-bottom: 1px solid rgba(255,255,255,0.06);">
                <h6 class="modal-title rajdhani font-weight-bold text-white" style="letter-spacing: 1px;">UPLOAD FILES</h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST" enctype="multipart/form-data" id="pcFilesForm">
                    @csrf
                    <input type="hidden" name="op" value="add_files">
                    <input type="file" name="attachments[]" id="pcFilesInput" multiple class="form-control" style="background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.15);" required>
                    <div class="text-muted small mt-2">PDF/JPG/PNG/DOC/DOCX (max 10MB each)</div>
                    <div class="d-flex justify-content-end mt-3" style="gap:10px;">
                        <button type="button" class="btn btn-outline-light btn-sm rajdhani font-weight-bold" data-dismiss="modal">CANCEL</button>
                        <button type="submit" class="btn btn-primary btn-sm rajdhani font-weight-bold">UPLOAD</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ============ FINANCIAL INTELLIGENCE DASHBOARD MODAL ============ --}}
{{-- ============ PREMIUM FINANCIAL INTELLIGENCE DASHBOARD MODAL ============ --}}
<div class="modal fade" id="financialIntelligenceModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background: #000c1a; border: 2px solid var(--rd-accent); border-radius: 12px; overflow: hidden; box-shadow: 0 0 50px rgba(0,0,0,0.8);">
            <div class="modal-header border-bottom border-dark py-2 px-4 d-flex align-items-center justify-content-between" style="background: rgba(255,255,255,0.03);">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="font-size: 24px; color: var(--rd-accent);"><i class="fas fa-chart-network"></i></div>
                    <div>
                        <h5 class="modal-title rajdhani font-weight-bold text-white mb-0" style="letter-spacing: 2px;">FINANCIAL INTELLIGENCE REPORT</h5>
                        <div class="small text-muted rajdhani">{{ $head->head_name }} | DATED {{ date('d M y') }} <span class="ml-2 text-info opacity-50">{{ $head->trans_type == 1 ? '(Million PKR without GST)' : '(PKR with GST)' }}</span></div>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-50 hover-opacity-100" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body p-0" style="background: #000c1a;">
                {{-- Top Summary bar --}}
                <div class="row no-gutters border-bottom border-dark" style="background: rgba(0,0,0,0.4);">
                    <div class="col-md-3 border-right border-dark p-3">
                        <div class="small text-muted rajdhani">ALLOCATION</div>
                        <div class="h5 mb-0 text-white font-weight-bold rajdhani">{{ number_format($head->allocation) }}</div>
                    </div>
                    <div class="col-md-3 border-right border-dark p-3">
                        <div class="small text-muted rajdhani">MTSS SHARE</div>
                        <div class="h5 mb-0 text-white font-weight-bold rajdhani">{{ number_format($head->mtss_share) }}</div>
                    </div>
                    <div class="col-md-3 border-right border-dark p-3">
                        <div class="small text-muted rajdhani">RDW SHARE</div>
                        <div class="h5 mb-0 text-info font-weight-bold rajdhani">{{ number_format($head->rdw_share) }}</div>
                    </div>
                    <div class="col-md-3 p-3">
                        <div class="small text-muted rajdhani">CSRF SHARE</div>
                        <div class="h5 mb-0 text-white font-weight-bold rajdhani">{{ number_format($head->csrf_share) }}</div>
                    </div>
                </div>

                <div class="row no-gutters">
                    {{-- Left Pane: Detailed Metrics Table --}}
                    <div class="col-xl-4 border-right border-dark p-4" style="background: rgba(0,0,0,0.2);">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h6 class="rajdhani text-info font-weight-bold mb-0" style="letter-spacing: 1px;"><i class="fas fa-table mr-2"></i>PROJECT SNAPSHOT</h6>
                            <div class="small text-muted rajdhani">FIGURES IN PKR</div>
                        </div>

                        <div class="fin-table-modern rounded border border-dark overflow-hidden">
                            <table class="table table-sm table-dark mb-0 rajdhani" style="font-size: 13px;">
                                <thead style="background: rgba(255,255,255,0.03);">
                                    <tr class="text-muted">
                                        <th class="pl-3 border-0">METRIC</th>
                                        <th class="text-right border-0" style="color: #4da3ff;">PROJECT</th>
                                        <th class="text-right pr-3 border-0" style="color: #4dff88;">ACTUAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="pl-3 text-muted">Received</td>
                                        <td class="text-right" style="color: #4da3ff;">{{ number_format($head->received) }}</td>
                                        <td class="text-right pr-3" style="color: #4dff88;">{{ number_format($head->received) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="pl-3 text-muted">Expenditure</td>
                                        <td class="text-right text-danger">{{ number_format($head->expenditure) }}</td>
                                        <td class="text-right pr-3" style="color: #4dff88;">{{ number_format($head->expenditure) }}</td>
                                    </tr>
                                    <tr style="background: rgba(255,255,255,0.01);">
                                        <td class="pl-3 text-info font-weight-bold">Balance</td>
                                        <td class="text-right text-info font-weight-bold">{{ number_format($head->balance) }}</td>
                                        <td class="text-right pr-3 text-muted">--</td>
                                    </tr>
                                    <tr>
                                        <td class="pl-3 text-muted">Commitments</td>
                                        <td class="text-right text-warning">{{ number_format($head->commitments) }}</td>
                                        <td class="text-right pr-3" style="color: #4dff88;">{{ number_format($head->commitments) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="pl-3 text-muted">In Process</td>
                                        <td class="text-right text-muted">{{ number_format($head->in_process) }}</td>
                                        <td class="text-right pr-3 text-muted">0</td>
                                    </tr>
                                    <tr style="background: rgba(0,255,100,0.05);">
                                        <td class="pl-3 font-weight-bold">Available</td>
                                        <td class="text-right font-weight-bold">{{ number_format($head->available) }}</td>
                                        <td class="text-right pr-3 text-muted">--</td>
                                    </tr>
                                    <tr>
                                        <td class="pl-3 text-muted">Yet to be Rec</td>
                                        <td class="text-right text-muted">{{ number_format($head->yet_to_be_received) }}</td>
                                        <td class="text-right pr-3 text-muted">--</td>
                                    </tr>
                                    <tr style="background: rgba(255,50,50,0.05);">
                                        <td class="pl-3 text-danger font-weight-bold">Remaining</td>
                                        <td class="text-right text-danger font-weight-bold">{{ number_format($head->remaining) }}</td>
                                        <td class="text-right pr-3" style="color: #4dff88;">{{ number_format($head->remaining) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Receivables section --}}
                        <div class="mt-4 pt-4 border-top border-dark">
                            <h6 class="rajdhani text-muted mb-3" style="font-size: 12px; letter-spacing: 2px;">RECEIVABLES</h6>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="text-muted small rajdhani">Comp. Milestones</span>
                                <span class="text-white rajdhani font-weight-bold">{{ number_format($head->receivable_completed) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="text-muted small rajdhani">Current Milestone</span>
                                <span class="text-white rajdhani font-weight-bold">{{ number_format($head->receivable_current) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mt-3 p-2 rounded" style="background: rgba(23,162,184,0.1); border: 1px solid rgba(23,162,184,0.2);">
                                <span class="text-info small rajdhani font-weight-bold">Available after Rcv.</span>
                                <span class="text-info rajdhani font-weight-bold">{{ number_format($head->available_after_receivables) }}</span>
                            </div>
                        </div>

                        {{-- Exp Sources --}}
                        <div class="mt-4 pt-4 border-top border-dark">
                            <h6 class="rajdhani text-muted mb-3" style="font-size: 12px; letter-spacing: 2px;">EXP. SOURCES</h6>
                            <div class="small d-flex justify-content-between mb-1">
                                <span class="text-muted rajdhani">From this account</span>
                                <span class="text-white rajdhani">{{ number_format($head->exp_this_account) }}</span>
                            </div>
                            <div class="small d-flex justify-content-between mb-1">
                                <span class="text-muted rajdhani">From other accounts</span>
                                <span class="text-white rajdhani">{{ number_format($head->exp_other_accounts) }}</span>
                            </div>
                            <div class="small d-flex justify-content-between">
                                <span class="text-muted rajdhani">Other's exp. this acc.</span>
                                <span class="text-white rajdhani">{{ number_format($head->others_exp_this_account) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Pane: Visual Analytics & Subheads --}}
                    <div class="col-xl-8 p-4">
                        <div class="row">
                            {{-- Mini Category Charts --}}
                            @foreach(array_slice($subheads, 0, 3) as $idx => $sh)
                            <div class="col-md-4 mb-4">
                                <div class="subhead-mini-card p-3 rounded border border-dark text-center h-100" style="background: rgba(255,255,255,0.01);">
                                    <div class="d-flex justify-content-center mb-2" style="height: 80px;">
                                        <canvas id="chartShMini{{ $idx }}"></canvas>
                                    </div>
                                    <h6 class="rajdhani font-weight-bold text-info mb-1">{{ $sh['name'] }}</h6>
                                    <div class="text-white rajdhani" style="font-size: 14px;">{{ number_format($sh['allocation']) }}</div>
                                    <div class="mt-2 small text-muted rajdhani">UTILIZED: {{ number_format($sh['expenditure']) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Subhead Detailed Breakdown --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="dg-sec-label mb-3"><i class="fas fa-th-list fa-xs"></i> Category Metrics Breakdown</div>
                                <div class="table-responsive rounded border border-dark">
                                    <table class="table table-sm table-dark table-hover mb-0 rajdhani" style="font-size: 12px;">
                                        <thead style="background: rgba(0,0,0,0.4);">
                                            <tr class="text-muted small">
                                                <th class="pl-3">SUBHEAD</th>
                                                <th class="text-right">EXPENDITURE</th>
                                                <th class="text-right">COMMITMENTS</th>
                                                <th class="text-right">IN PROCESS</th>
                                                <th class="text-right pr-3">REMAINING</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subheads as $sh)
                                            <tr>
                                                <td class="pl-3 font-weight-bold text-info">{{ $sh['name'] }}</td>
                                                <td class="text-right">{{ number_format($sh['expenditure']) }}</td>
                                                <td class="text-right">{{ number_format($sh['commitments']) }}</td>
                                                <td class="text-right">{{ number_format($sh['in_process']) }}</td>
                                                <td class="text-right pr-3 font-weight-bold {{ $sh['remaining'] < 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($sh['remaining']) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Large Comparison Chart --}}
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="p-4 rounded border border-dark" style="background: rgba(0,0,0,0.3); height: 300px;">
                                    <canvas id="finDetailedChart"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-grid gap-2 h-100" style="display: grid; grid-template-rows: repeat(4, 1fr); gap: 10px;">
                                    <button class="btn btn-outline-info btn-sm rajdhani font-weight-bold"><i class="fas fa-chart-pie mr-2"></i> SPENDING BREAKDOWN</button>
                                    <button class="btn btn-outline-secondary btn-sm rajdhani font-weight-bold"><i class="fas fa-history mr-2"></i> SPENDING TIMELINE</button>
                                    <button class="btn btn-outline-secondary btn-sm rajdhani font-weight-bold"><i class="fas fa-calculator mr-2"></i> SALARY FORECAST</button>
                                    <button class="btn btn-outline-secondary btn-sm rajdhani font-weight-bold"><i class="fas fa-file-contract mr-2"></i> CONTRACTS TIMELINE</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-top border-dark py-2 px-4 d-flex justify-content-between" style="background: rgba(0,0,0,0.3);">
                <div class="small text-muted rajdhani"><i class="fas fa-shield-check text-success mr-1"></i> RDWIS FINANCIAL AUDIT ENGINE ACTIVE</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-info btn-xs rajdhani font-weight-bold px-4" onclick="initFinancialIntelligenceCharts()">
                        <i class="fas fa-sync-alt mr-1"></i> RE-CALCULATE
                    </button>
                    <button type="button" class="btn btn-outline-light btn-xs rajdhani font-weight-bold px-4" data-dismiss="modal">CLOSE REPORT</button>
                </div>
            </div>
        </div>

        </div>
    </div>
</div>

<style>
    .fin-card-glass { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); transition: all 0.3s; }
    .fin-card-glass:hover { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.15); transform: translateY(-2px); }
    .border-accent { border-color: rgba(243,156,18,0.3) !important; }
    .bg-navy-darker { background: #000c1a !important; }
    .subhead-list-wrap::-webkit-scrollbar { width: 4px; }
    .subhead-list-wrap::-webkit-scrollbar-thumb { background: var(--rd-accent); border-radius: 10px; }
</style>

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

function initFinancialIntelligenceCharts() {
    const head = @json($head);
    const subheads = @json($subheads);
    
    console.log("RDWIS Financial Intelligence: Initializing high-fidelity charts...", head);
    
    // 1. Main Project Liquidity Bar Chart
    const canvas = document.getElementById('finDetailedChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        if (window.finMainChart) window.finMainChart.destroy();
        window.finMainChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Received', 'Expenditure', 'Commitments', 'In Process', 'Remaining'],
                datasets: [{
                    label: 'PKR Value',
                    data: [head.received, head.expenditure, head.commitments, head.in_process, head.remaining],
                    backgroundColor: [
                        'rgba(77, 163, 255, 0.2)', 
                        'rgba(255, 50, 50, 0.2)', 
                        'rgba(243, 156, 18, 0.2)', 
                        'rgba(23, 162, 184, 0.2)', 
                        'rgba(77, 255, 136, 0.2)'
                    ],
                    borderColor: ['#4da3ff', '#ff3232', '#f39c12', '#17a2b8', '#4dff88'],
                    borderWidth: 2, borderRadius: 4, barThickness: 40
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: { backgroundColor: '#001226', titleFont: { family: 'Rajdhani', size: 14 }, bodyFont: { family: 'Inter', size: 12 }, padding: 12 } 
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#888', font: { family: 'Rajdhani' } } },
                    x: { grid: { display: false }, ticks: { color: '#aaa', font: { family: 'Rajdhani', weight: 'bold' } } }
                }
            }
        });
    }

    // 2. Mini Subhead Utilization Charts (Pie)
    subheads.slice(0, 3).forEach((sh, idx) => {
        const shCanvas = document.getElementById(`chartShMini${idx}`);
        if (shCanvas) {
            const shCtx = shCanvas.getContext('2d');
            if (window[`finShChart${idx}`]) window[`finShChart${idx}`].destroy();
            
            window[`finShChart${idx}`] = new Chart(shCtx, {
                type: 'pie',
                data: {
                    labels: ['Used', 'Remaining'],
                    datasets: [{
                        data: [sh.expenditure + sh.commitments + sh.in_process, sh.remaining > 0 ? sh.remaining : 0],
                        backgroundColor: ['rgba(77, 255, 136, 0.8)', 'rgba(255, 255, 255, 0.05)'],
                        borderColor: 'transparent',
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    cutout: '70%'
                }
            });
        }
    });
}


$(function() {
    // Main page progress bar animation
    setTimeout(() => {
        document.getElementById('dgProgU')?.classList.add('anim');
        document.getElementById('dgProgR')?.classList.add('anim');
    }, 500);

    // Modal trigger logic
    $(document).on('shown.bs.modal', '#financialIntelligenceModal', function () {
        setTimeout(initFinancialIntelligenceCharts, 250);
    });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrap = document.getElementById('pcEditWrap');
    if (!wrap) return;
    const canEdit = wrap.getAttribute('data-can-edit') === '1';
    const saveUrl = @json(route('purchase.initiation.save', $purchase->pcs_id));
    const storageBase = @json(rtrim(url('storage'), '/') . '/');

    @php
        $pcItems = $purchase->items->sortBy('pci_serial')->values();
        $pcQuotes = $purchase->quotes->values();
        $quoteIds = $pcQuotes->pluck('qte_id')->toArray();
        $quoteItemMap = [];
        if (count($quoteIds) > 0) {
            $rows = \Illuminate\Support\Facades\DB::table('pur.quoteitems')
                ->whereIn('qti_qte_id', $quoteIds)
                ->get(['qti_qte_id', 'qti_pci_id', 'qti_price']);
            foreach ($rows as $r) {
                $qid = (string) $r->qti_qte_id;
                $pid = (string) $r->qti_pci_id;
                if (!isset($quoteItemMap[$qid])) $quoteItemMap[$qid] = [];
                $quoteItemMap[$qid][$pid] = (float) $r->qti_price;
            }
        }
        $snapshot = [
            'pcs_id' => (int) $purchase->pcs_id,
            'pcs_title' => (string) $purchase->pcs_title,
            'pcs_remarks' => (string) ($purchase->pcs_remarks ?? ''),
            'pcs_price' => (float) ($purchase->pcs_price ?? 0),
            'items' => $pcItems->map(fn($i) => [
                'pci_id' => (int) $i->pci_id,
                'pci_serial' => (int) $i->pci_serial,
                'pci_desc' => (string) $i->pci_desc,
                'pci_qty' => (float) $i->pci_qty,
                'pci_qtyunit' => (string) $i->pci_qtyunit,
                'pci_price' => (float) ($i->pci_price ?? 0),
            ])->values(),
            'quotes' => $pcQuotes->map(fn($q) => [
                'qte_id' => (int) $q->qte_id,
                'qte_num' => (int) ($q->qte_num ?? 0),
                'firm_name' => (string) ($q->firm?->frm_name ?? $q->qte_firmname),
                'qte_price' => (float) ($q->qte_price ?? 0),
            ])->values(),
            'attachments' => $purchase->attachments->map(fn($a) => [
                'pat_id' => (int) $a->pat_id,
                'pat_path' => (string) $a->pat_path,
                'pat_filename' => (string) ($a->pat_filename ?? ''),
            ])->values(),
            'quote_items' => $quoteItemMap,
        ];
    @endphp

    let state = @json($snapshot);
    const isInitiator = @json($isInitiator);
    const isDProc     = @json($isDProc);


    function setEditing(isEditing) {
        const toggleBtn = document.getElementById('pcEditToggleBtn');
        if (isEditing) {
            wrap.classList.add('is-editing');
            toggleBtn?.classList.remove('btn-outline-warning');
            toggleBtn?.classList.add('btn-warning');
        } else {
            wrap.classList.remove('is-editing');
            toggleBtn?.classList.remove('btn-warning');
            toggleBtn?.classList.add('btn-outline-warning');
        }
    }

    function fmt(n) {
        const num = Number(n || 0);
        return num.toLocaleString(undefined, { maximumFractionDigits: 2 });
    }

    function sortQuotesByPrice(quotes) {
        return [...quotes].sort((a, b) => (a.qte_price ?? 0) - (b.qte_price ?? 0));
    }

    function renderItems() {
        const body = document.getElementById('pcItemsBody');
        if (!body) return;
        // Sort by ID descending for LIFO (Last In First Out)
        const items = [...(state.items || [])].sort((a, b) => (b.pci_id ?? 0) - (a.pci_id ?? 0));
        body.innerHTML = items.map((it, idx) => `
            <tr>
                <td class="pl-3 text-muted">${items.length - idx}</td>
                <td>${(it.pci_desc ?? '').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}</td>
                <td class="text-center font-weight-bold">${fmt(it.pci_qty)} ${(it.pci_qtyunit ?? '')}</td>
                <td class="text-right pr-3 font-weight-bold text-white">${fmt(it.pci_price)}</td>
                <td class="text-right pr-3 edit-only">
                    <div class="d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-outline-info btn-xs pc-item-plus-btn" title="Add another below" style="padding: 2px 6px;"><i class="fas fa-plus"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-xs pc-item-del-btn" data-pci-id="${it.pci_id}" title="Delete" style="padding: 2px 6px;"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderQuotes() {
        const body = document.getElementById('pcQuotesBody');
        if (!body) return;
        const sorted = sortQuotesByPrice(state.quotes || []);
        if (sorted.length === 0) {
            body.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted small">No quotations added yet.</td></tr>`;
            return;
        }
        body.innerHTML = sorted.map((q, idx) => {
            const isWinner = idx === 0;
            const trStyle = isWinner ? 'background: rgba(40, 167, 69, 0.15) !important;' : '';
            const td1 = isWinner ? 'text-success font-weight-bold' : 'text-muted';
            const td2 = isWinner ? 'text-success' : 'text-white';
            const td3 = isWinner ? 'text-success' : 'text-info';
            return `
                <tr style="${trStyle}">
                    <td class="pl-3 ${td1}">${idx + 1}</td>
                    <td class="font-weight-bold ${td2}">${(q.firm_name ?? '').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}</td>
                    <td class="text-right pr-3 font-weight-bold ${td3}">Rs. ${fmt(q.qte_price)}</td>
                    <td class="text-right pr-3">
                        @if($canAddQuotes)
                            <button type="button" class="btn btn-outline-danger btn-xs pc-quote-del-btn" data-qte-id="${q.qte_id}" title="Delete" style="padding: 2px 6px;"><i class="fas fa-trash-alt"></i></button>
                        @endif
                    </td>

                </tr>
            `;
        }).join('');
    }

    function renderRemarks() {
        const el = document.getElementById('pcRemarksText');
        const inp = document.getElementById('pcRemarksInput');
        if (inp) inp.value = state.pcs_remarks || '';
        if (!el) return;
        const txt = (state.pcs_remarks || '').trim();
        el.innerHTML = txt ? txt.replaceAll('<', '&lt;').replaceAll('>', '&gt;') : `<span class="opacity-50 italic">No remarks provided during case initiation.</span>`;
    }

    function renderFiles() {
        const wrapEl = document.getElementById('pcFilesWrap');
        if (!wrapEl) return;
        const files = state.attachments || [];
        if (!files.length) {
            wrapEl.innerHTML = `<div class="text-muted small italic opacity-50">No files attached.</div>`;
            return;
        }
        wrapEl.innerHTML = files.map((f) => `
            <div class="d-flex align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); min-width: 200px; position: relative;">
                <i class="far fa-file-pdf text-danger mr-2" style="font-size: 16px;"></i>
                <div class="flex-grow-1 overflow-hidden mr-2">
                    <div class="small font-weight-bold text-white text-nowrap" style="overflow: hidden; text-overflow: ellipsis; font-size: 10px;">${(f.pat_filename || '').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}</div>
                </div>
                <div class="d-flex gap-1">
                    <a href="${storageBase}${(f.pat_path || '')}" target="_blank" class="btn btn-xs btn-outline-primary" style="padding: 1px 5px;" title="Download">
                        <i class="fas fa-download" style="font-size: 10px;"></i>
                    </a>
                </div>
            </div>
        `).join('');
    }

    let modalVendors = [];

    function renderMultiQuoteModal() {

    const headRow = document.querySelector('#pcMultiQuoteHead tr');
    const body = document.getElementById('pcMultiQuoteBody');
    const foot = document.getElementById('pcMultiQuoteFoot');
    if (!body || !headRow || !foot) return;

    const items = [...(state.items || [])].sort((a, b) => (a.pci_serial ?? 0) - (b.pci_serial ?? 0));
    const qi = state.quote_items || {};

    // Initialize modalVendors from state.quotes if empty
    if (modalVendors.length === 0 && (state.quotes || []).length > 0) {
        modalVendors = (state.quotes || []).map(q => ({
            id: q.qte_id,
            firm_name: q.firm_name,
            prices: qi[String(q.qte_id)] || {}
        }));
    }
    if (modalVendors.length === 0) {
        modalVendors.push({ id: null, firm_name: '', prices: {} });
    }

    const V_COL_WIDTH_PCT = 80 / Math.max(1, modalVendors.length);

    // ---- THEAD ----
    headRow.innerHTML = `<th class="pc-item-sticky">ITEM DESCRIPTION</th>` +
        modalVendors.map((v, idx) => `
            <th style="width: 250px; min-width: 250px; text-align:center; padding:8px;">
                <div class="d-flex align-items-center justify-content-center" style="gap:6px;">
                    <input type="text" list="dbFirmsList"
                           class="pc-vendor-name-input"
                           value="${v.firm_name}"
                           placeholder="Vendor Name"
                           data-idx="${idx}">
                    <button type="button" class="pc-remove-vendor-btn btn btn-link p-0" data-idx="${idx}" style="color:#dc3545; font-size:12px;">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </th>
        `).join('');




    // ---- TBODY ----
    body.innerHTML = items.map((it) => `
        <tr>
            <td class="pc-item-sticky">
                <div style="font-size:12px; font-weight:700; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${(it.pci_desc ?? '').replaceAll('<','&lt;').replaceAll('>','&gt;')}</div>
                <div style="font-size:11px; color:#888;">Quantity: ${fmt(it.pci_qty)}</div>
            </td>
            ${modalVendors.map((v, idx) => `
                <td style="width: 250px; min-width: 250px; text-align:center; padding:6px;">
                    <input type="number"
                           class="pc-price-input"
                           value="${v.prices[it.pci_id] || ''}"
                           placeholder="0.00"
                           data-v-idx="${idx}"
                           data-i-id="${it.pci_id}"
                           ${!v.firm_name.trim() ? 'disabled title="Enter vendor name first"' : ''}>
                </td>
            `).join('')}
        </tr>
    `).join('');



    renderMultiQuoteTotals();
}

function renderMultiQuoteTotals() {
    const foot = document.getElementById('pcMultiQuoteFoot');
    if (!foot) return;

    const items = state.items || [];
    const taxPercent = parseFloat(document.getElementById('pcGlobalTaxPercent')?.value || 0);

    const columnTotals = modalVendors.map((v) => {
        let sub = 0;
        items.forEach(it => { sub += parseFloat(v.prices[it.pci_id] || 0); });
        return sub;
    });
    const minTotal = Math.min(...columnTotals.filter(t => t > 0));

    foot.innerHTML = `
        <tr>
            <td class="pc-item-sticky" style="text-align:right;">
                <div style="font-size:10px; color:rgba(255,255,255,0.5);">SUB TOTAL</div>
                <div style="font-size:10px; color:rgba(255,255,255,0.5);">TAX (${taxPercent}%)</div>
                <div style="font-size:13px; color:var(--rd-accent); font-weight:800;">TOTAL (PKR)</div>
            </td>
            ${modalVendors.map((v, idx) => {
                const sub = columnTotals[idx];
                const tax = sub * (taxPercent / 100);
                const total = sub + tax;
                const isWinner = sub > 0 && sub === minTotal;
                return `
                    <td style="width: 250px; min-width: 250px; text-align:center; padding:8px; ${isWinner ? 'background:rgba(40,167,69,0.15) !important;' : ''}">
                        <div style="font-size:11px; color:rgba(255,255,255,0.7);">${fmt(sub)}</div>
                        <div style="font-size:11px; color:rgba(255,255,255,0.5);">${fmt(tax)}</div>
                        <div style="font-size:15px; font-weight:800; color:${isWinner ? '#28a745' : '#fff'};">
                            ${fmt(total)}${isWinner ? ' <i class="fas fa-trophy ml-1" style="font-size:11px;"></i>' : ''}
                        </div>
                    </td>
                `;
            }).join('')}
        </tr>
    `;



    }



    function renderTitle() {
        const view = document.getElementById('pcTitleView');
        const input = document.querySelector('#pcTitleForm input[name="pcs_title"]');
        if (view) view.textContent = state.pcs_title || '';
        if (input) input.value = state.pcs_title || '';
    }

    function renderAll() {
        renderTitle();
        renderItems();
        renderQuotes();
        renderRemarks();
        renderFiles();
    }

    function renderCs() {
        const modal = document.getElementById('detailedCSModal');
        if (!modal) return;
        const container = modal.querySelector('.cs-container');
        if (!container) return;

        const items = [...(state.items || [])].sort((a, b) => (a.pci_serial ?? 0) - (b.pci_serial ?? 0));
        const quotes = sortQuotesByPrice(state.quotes || []);
        const qi = state.quote_items || {};

        if (!items.length || !quotes.length) {
            container.innerHTML = `<div class="p-4 text-center text-muted small">No data available.</div>`;
            return;
        }

        const winnerByItem = {};
        for (const it of items) {
            let min = null;
            for (const q of quotes) {
                const price = Number((qi[String(q.qte_id)] || {})[String(it.pci_id)] || 0);
                if (price > 0 && (min === null || price < min)) min = price;
            }
            winnerByItem[it.pci_id] = min;
        }

        const head = `
            <table class="cs-table table-bordered" style="border-color: rgba(255,255,255,0.05) !important;">
                <thead>
                    <tr style="background: rgba(255,255,255,0.03);">
                        <th class="cs-sticky-1 text-muted text-center" style="font-size: 10px; border-bottom: 2px solid var(--rd-accent);">#</th>
                        <th class="cs-sticky-2 text-left text-muted" style="font-size: 10px; border-bottom: 2px solid var(--rd-accent);">DESCRIPTION / ITEMS SPECIFICATION</th>
                        <th class="cs-sticky-3 text-center text-muted" style="font-size: 10px; border-bottom: 2px solid var(--rd-accent);">QTY</th>
                        ${quotes.map((q, idx) => `
                            <th class="text-center ${idx === 0 ? 'col-l1' : ''}" style="border-right: 1px solid rgba(255,255,255,0.05); min-width: 160px; border-bottom: 2px solid ${idx === 0 ? 'var(--rd-success)' : 'var(--rd-accent)'};">
                                <div class="text-accent-clean ${idx === 0 ? 'text-success' : ''}" style="font-size: 13px; font-weight: 700; letter-spacing: 0.5px;">
                                    ${String(q.firm_name || '').replaceAll('<', '&lt;').replaceAll('>', '&gt;').toUpperCase()}
                                </div>
                                <div class="small text-muted" style="font-size: 9px; font-weight: 500; letter-spacing: 1px; opacity: 0.7;">
                                    ${idx === 0 ? '<span class="badge badge-success px-2">LOWEST (L1)</span>' : ('RANK L' + (idx + 1))}
                                </div>
                            </th>
                        `).join('')}
                    </tr>
                </thead>
                <tbody>
        `;

        const body = items.map((it) => {
            const row = `
                <tr>
                    <td class="cs-sticky-1 text-center text-muted small" style="background: rgba(0,0,0,0.2) !important;">${it.pci_serial ?? ''}</td>
                    <td class="cs-sticky-2 text-white" style="font-weight: 500; background: rgba(0,0,0,0.2) !important;">${String(it.pci_desc || '').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}</td>
                    <td class="cs-sticky-3 text-center text-white" style="font-weight: 600; background: rgba(0,0,0,0.2) !important;">${fmt(it.pci_qty)}</td>
                    ${quotes.map((q, idx) => {
                        const price = Number((qi[String(q.qte_id)] || {})[String(it.pci_id)] || 0);
                        const isBest = price > 0 && price === (winnerByItem[it.pci_id] || -1);
                        return `
                            <td class="text-center ${idx === 0 ? 'col-l1' : ''}" style="border-right: 1px solid rgba(255,255,255,0.05);">
                                ${price > 0
                                    ? `<div class="price-val ${isBest ? 'text-success' : 'text-white'}" style="font-size: 14px; font-weight: 700;">${fmt(price)}</div>${isBest ? `<span class="badge badge-success" style="font-size: 8px; padding: 1px 4px;">Min</span>` : ''}`
                                    : `<span class="text-muted small">N/A</span>`
                                }
                            </td>
                        `;
                    }).join('')}
                </tr>
            `;
            return row;
        }).join('');

        const totals = quotes.map((q) => fmt(q.qte_price));
        const foot = `
                </tbody>
                <tfoot style="border-top: 2px solid var(--rd-accent);">
                    <tr style="background: rgba(255,255,255,0.03);">
                        <td colspan="3" class="cs-sticky-1-3 text-right pr-4 text-accent-clean" style="font-size: 14px; background: rgba(0,0,0,0.4) !important; font-weight: 800;">
                            GRAND TOTAL (PKR)
                        </td>
                        ${totals.map((t, idx) => `
                            <td class="text-center py-3 ${idx === 0 ? 'col-l1' : ''}" style="border-right: 1px solid rgba(255,255,255,0.05); background: rgba(0,0,0,0.2) !important;">
                                <div class="rajdhani ${idx === 0 ? 'text-success' : 'text-white'}" style="font-size: 20px; font-weight: 800; text-shadow: 0 0 10px rgba(0,0,0,0.5);">
                                    ${t}
                                </div>
                            </td>
                        `).join('')}
                    </tr>
                </tfoot>
            </table>
        `;

        container.innerHTML = head + body + foot;
    }

    function renderTitle() {
        const view = document.getElementById('pcTitleView');
        const input = document.querySelector('#pcTitleForm input[name="pcs_title"]');
        if (view) view.textContent = state.pcs_title || '';
        if (input) input.value = state.pcs_title || '';
    }

    function renderAll() {
        renderTitle();
        renderItems();
        renderQuotes();
        renderRemarks();
        renderFiles();
        // Updated to use the new multi-quote rendering function if needed
        if (typeof renderMultiQuoteModal === 'function') {
            renderMultiQuoteModal();
        }
    }


    async function postForm(formData) {
        const res = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!res.ok) {
            let text = 'Request failed';
            try {
                const j = await res.json();
                if (j && j.message) text = j.message;
                if (j && j.errors) {
                    const firstKey = Object.keys(j.errors)[0];
                    if (firstKey) text = j.errors[firstKey][0] || text;
                }
            } catch (e) {}
            throw new Error(text);
        }

        return await res.json();
    }

    function toast(msg) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({ title: msg, timer: 1200, showConfirmButton: false, background: '#001226', color: '#fff' });
        } else {
            alert(msg);
        }
    }

    function ensureEditing() {
        if (isDProc) return true; // DProc is always in collaborative edit mode for quotes
        return wrap.classList.contains('is-editing');
    }


    const toggleBtn = document.getElementById('pcEditToggleBtn');
    if (toggleBtn) {
        // Initial state
        setEditing(false);
        
        toggleBtn.addEventListener('click', function() {
            const isEditing = !wrap.classList.contains('is-editing');
            setEditing(isEditing);
            console.log("Editing mode toggled:", isEditing);
        });
    } else {
        console.warn("Edit toggle button not found. canEdit is:", canEdit);
    }


    const inlineBtn = document.getElementById('pcAddItemInlineBtn');
    const inlineEditor = document.getElementById('pcInlineItemEditor');
    const itemCancelBtn = document.getElementById('pcItemCancelBtn');
    const itemForm = document.getElementById('pcAddItemForm');

    inlineBtn?.addEventListener('click', function() {
        if (!ensureEditing()) return;
        if (!inlineEditor) return;
        inlineEditor.style.display = '';
        document.getElementById('pcItemDesc')?.focus();
    });

    itemCancelBtn?.addEventListener('click', function() {
        if (!inlineEditor) return;
        inlineEditor.style.display = 'none';
    });

    itemForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!ensureEditing()) return;
        const fd = new FormData();
        fd.append('op', 'add_item');
        fd.append('item_desc', document.getElementById('pcItemDesc')?.value || '');
        fd.append('item_qty', document.getElementById('pcItemQty')?.value || '1');
        fd.append('_token', @json(csrf_token()));
        try {
            const json = await postForm(fd);
            state = json.data;
            renderAll();
            toast(json.message || 'Saved');
            const desc = document.getElementById('pcItemDesc');
            const qty = document.getElementById('pcItemQty');
            if (desc) desc.value = '';
            if (qty) qty.value = '1';
            desc?.focus();
        } catch (err) {
            toast(err.message || 'Error');
        }
    });

    document.getElementById('pcItemsBody')?.addEventListener('click', async function(e) {
        const plusBtn = e.target.closest('.pc-item-plus-btn');
        if (plusBtn) {
            if (!ensureEditing()) return;
            if (inlineEditor) {
                const tr = plusBtn.closest('tr');
                // Create a temporary row to hold the editor if needed, or just show it below the table
                // The user wants it "whin add hujaye", LIFO means it goes to top.
                // But let's at least scroll to the editor or move it.
                inlineEditor.style.display = '';
                tr.after(inlineEditor); // Move editor below this row
                document.getElementById('pcItemDesc')?.focus();
            }
            return;
        }

        const btn = e.target.closest('.pc-item-del-btn');
        if (!btn) return;
        if (!ensureEditing()) return;
        const pciId = btn.getAttribute('data-pci-id');
        if (!pciId) return;
        const fd = new FormData();
        fd.append('op', 'delete_item');
        fd.append('pci_id', pciId);
        fd.append('_token', @json(csrf_token()));
        try {
            const json = await postForm(fd);
            state = json.data;
            renderAll();
            toast(json.message || 'Deleted');
        } catch (err) {
            toast(err.message || 'Error');
        }
    });

    document.getElementById('pcQuotesBody')?.addEventListener('click', async function(e) {
        const btn = e.target.closest('.pc-quote-del-btn');
        if (!btn) return;
        if (!ensureEditing()) return;
        const qteId = btn.getAttribute('data-qte-id');
        if (!qteId) return;
        const fd = new FormData();
        fd.append('op', 'delete_quote');
        fd.append('qte_id', qteId);
        fd.append('_token', @json(csrf_token()));
        try {
            const json = await postForm(fd);
            state = json.data;
            renderAll();
            toast(json.message || 'Deleted');
        } catch (err) {
            toast(err.message || 'Error');
        }
    });

    document.getElementById('pcExistingQuotesBody')?.addEventListener('click', async function(e) {
        const btn = e.target.closest('.pc-quote-del-btn');
        if (!btn) return;
        if (!ensureEditing()) return;
        const qteId = btn.getAttribute('data-qte-id');
        if (!qteId) return;
        const fd = new FormData();
        fd.append('op', 'delete_quote');
        fd.append('qte_id', qteId);
        fd.append('_token', @json(csrf_token()));
        try {
            const json = await postForm(fd);
            state = json.data;
            renderAll();
            toast(json.message || 'Deleted');
        } catch (err) {
            toast(err.message || 'Error');
        }
    });

    document.getElementById('pcTitleForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!ensureEditing()) return;
        const form = e.target;
        const fd = new FormData(form);
        try {
            const json = await postForm(fd);
            state = json.data;
            renderAll();
            toast(json.message || 'Saved');
        } catch (err) {
            toast(err.message || 'Error');
        }
    });

  $(document).on('click', '#pcAddVendorColBtn', function() {
    modalVendors.unshift({ id: null, firm_name: '', prices: {} });
    renderMultiQuoteModal();
});

    $(document).on('click', '.pc-remove-vendor-btn', async function() {
        const idx = parseInt(this.getAttribute('data-idx'));
        const vendor = modalVendors[idx];
        
        if (vendor && vendor.id) {
            if (!confirm("Are you sure you want to permanently delete this quotation?")) return;
            
            const fd = new FormData();
            fd.append('op', 'delete_quote');
            fd.append('qte_id', vendor.id);
            fd.append('_token', @json(csrf_token()));
            
            try {
                const json = await postForm(fd);
                state = json.data;
                renderAll();
                toast(json.message || 'Deleted from database');
            } catch (err) {
                toast(err.message || 'Error deleting quote');
                return;
            }
        }

        if (modalVendors.length > 1) {
            modalVendors.splice(idx, 1);
            renderMultiQuoteModal();
        } else {
            modalVendors = [{ id: null, firm_name: '', prices: {} }];
            renderMultiQuoteModal();
        }
    });


    $(document).on('input', '.pc-vendor-name-input', function() {
        const idx = parseInt(this.getAttribute('data-idx'));
        modalVendors[idx].firm_name = this.value;
        // If they start typing a name, enable the inputs for that column
        renderMultiQuoteModal(); 
    });

    $(document).on('input', '.pc-price-input', function() {
        const vIdx = parseInt(this.getAttribute('data-v-idx'));
        const iId = this.getAttribute('data-i-id');
        modalVendors[vIdx].prices[iId] = this.value;
        renderMultiQuoteTotals(); // Only update totals, don't re-render inputs
    });

    $(document).on('input', '#pcGlobalTaxPercent', function() {
        renderMultiQuoteTotals();
    });

    $(document).on('click', '#pcSaveAllQuotesBtn', async function() {
        if (!ensureEditing()) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        try {
            // Since backend only handles one quote at a time, we'll send them sequentially
            // or we could modify backend. Let's do sequentially for now as user said 'rest is set'.
            for (const v of modalVendors) {
                if (!v.firm_name.trim()) continue;
                
                const fd = new FormData();
                fd.append('op', 'add_quote');
                if (v.id) fd.append('qte_id', v.id);
                fd.append('firm_name', v.firm_name);
                fd.append('_token', @json(csrf_token()));
                
                for (const [itemId, price] of Object.entries(v.prices)) {
                    fd.append(`item_prices[${itemId}]`, price || 0);
                }


                const json = await postForm(fd);
                state = json.data;
            }
            
            renderAll();
            toast("All quotations saved successfully.");
            $('#pcAddQuoteModal').modal('hide');
        } catch (err) {
            toast(err.message || 'Error saving quotations');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save mr-1"></i> SAVE QUOTATIONS';
        }

    });

    $(document).on('shown.bs.modal', '#pcAddQuoteModal', function () {
        modalVendors = []; // Reset and reload
        renderMultiQuoteModal();
    });

    document.getElementById('pcRemarksForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!ensureEditing()) return;
        const form = e.target;
        const fd = new FormData(form);
        try {
            const json = await postForm(fd);
            state = json.data;
            renderAll();
            toast(json.message || 'Saved');
        } catch (err) {
            toast(err.message || 'Error');
        }
    });

    document.getElementById('pcFilesForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!ensureEditing()) return;
        const form = e.target;
        const fd = new FormData(form);
        try {
            const json = await postForm(fd);
            state = json.data;
            renderAll();
            toast(json.message || 'Uploaded');
            form.reset();
        } catch (err) {
            toast(err.message || 'Error');
        }
    });

    $(document).on('shown.bs.modal', '#detailedCSModal', function () {
        renderCs();
    });

    renderAll();
});
</script>
@endsection
