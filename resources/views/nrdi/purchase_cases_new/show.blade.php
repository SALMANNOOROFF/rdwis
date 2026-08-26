@extends('welcome')

@section('content')
@php
    $winnerQuote  = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->first() : null;
    $sortedQ      = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->values() : collect([]);

    $caseValue      = (float)($purchase->live_value ?? ($purchase->pcs_price ?? ($winnerQuote?->qte_price ?? 0)));
    
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
    $currentStatusDisplay = $purchase->current_stage_display ?? $service->getStatusDisplayName($purchase->pcs_status);
    
    // Variable overrides for cross-role compatibility
    $userArea = strtolower(trim((string)Auth::user()->acc_untarea));
    $isInitiator = in_array($userArea, ['prj', 'rdwprj', 'division', 'initiation']);
    $isDProc     = str_contains($userArea, 'proc') || str_contains($userArea, 'prc') || in_array($userArea, ['proc', 'prc'], true);
    $isDraft     = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);

    $hasFloated  = $purchase->decisions->where('pdec_action', 'float_to_proc')->isNotEmpty();
    $hasDProcSaved = $purchase->decisions->where('pdec_action', 'dproc_save')->isNotEmpty();
    $dprocSaved  = $hasDProcSaved;

    // Division can edit before floating or after procurement responds
    $canEdit     = $isInitiator && $isDraft && (!$hasFloated || $hasDProcSaved);
    // DProc can add quotes when floated and NOT yet saved; Division can add quotes when not floated or after procurement responds
    $canAddQuotes = ($isDProc && $hasFloated && !$hasDProcSaved) || ($isInitiator && $isDraft && (!$hasFloated || $hasDProcSaved));

    if (request()->is('*procurement*') || (Route::has('nrdi.purchase_cases_new.procurement.index') && ($isDProc || ($area ?? '') === 'proc'))) {
        $backRoute = route('nrdi.purchase_cases_new.procurement.index');
    } elseif (request()->is('*finance*') && Route::has('nrdi.purchase_cases_new.finance.index')) {
        $backRoute = route('nrdi.purchase_cases_new.finance.index');
    } elseif ($isInitiator && Route::has('purchase.initiation.index')) {
        $backRoute = route('purchase.initiation.index');
    } else {
        $backRoute = route('nrdi.purchase_cases_new.index');
    }
@endphp

<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
<style>

/* ===== SCOPED to .dg-page ===== */
.dg-page { font-family:'Inter',sans-serif; }
.text-gold { color: #f39c12 !important; }
.bg-navy { background-color: var(--rd-surface3) !important; }
.border-gold { border-top: 3px solid #f39c12 !important; }
.border-left-gold { border-left: 5px solid #f39c12 !important; }

/* ---- Page Header ---- */
.dg-hdr { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.dg-back-btn { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:var(--rd-text2); background:var(--rd-surface); border:1px solid var(--rd-border); padding:5px 13px; border-radius:20px; text-decoration:none !important; transition:all .2s; }
.dg-back-btn:hover { border-color:var(--rd-accent); color:var(--rd-accent); }
.dg-page-title { font-family:'Rajdhani',sans-serif; font-size:19px; font-weight:700; color:var(--rd-text1); letter-spacing:.8px; margin-top:5px; }
.dg-case-badge { background:var(--rd-accent); color:var(--rd-bg); font-family:'Rajdhani',sans-serif; font-size:12px; font-weight:700; padding:4px 12px; border-radius:6px; letter-spacing:1px; display:inline-block; }
.dg-case-date { font-size:11px; color:var(--rd-text3); margin-top:4px; text-align:right; }

/* ---- Header Action Buttons ---- */
.btn-hdr-action {
    padding: 5px 11px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.6px;
    border-radius: 6px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.12);
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s ease-in-out;
    text-decoration: none !important;
}
.btn-hdr-it-annex {
    border: 1px solid rgba(243, 156, 18, 0.6) !important;
    background: rgba(243, 156, 18, 0.08) !important;
    color: #f39c12 !important;
}
.btn-hdr-it-annex:hover {
    background: #f39c12 !important;
    border-color: #f39c12 !important;
    color: #ffffff !important;
    box-shadow: 0 3px 8px rgba(243, 156, 18, 0.4) !important;
}
.btn-hdr-case-detail {
    border: 1px solid rgba(23, 162, 184, 0.5) !important;
    background: rgba(23, 162, 184, 0.08) !important;
    color: #17a2b8 !important;
}
.btn-hdr-case-detail:hover {
    background: #17a2b8 !important;
    border-color: var(--rd-primary-500) !important;
    color: #ffffff !important;
    box-shadow: 0 3px 8px rgba(23, 162, 184, 0.4) !important;
}
.btn-hdr-comparative-stmt {
    border: 1px solid rgba(40, 167, 69, 0.5) !important;
    background: rgba(40, 167, 69, 0.08) !important;
    color: #28a745 !important;
    cursor: pointer;
}
.btn-hdr-comparative-stmt:hover {
    background: #28a745 !important;
    border-color: #28a745 !important;
    color: #ffffff !important;
    box-shadow: 0 3px 8px rgba(40, 167, 69, 0.4) !important;
}

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

.dg-prog-wrap { position:relative; height:12px; background: var(--rd-neutral-50); border-radius:20px; overflow:hidden; margin-bottom:4px; border:1px solid var(--rd-border); }
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
.dg-tl-comment { font-size:11px; color:var(--rd-text2); font-style:italic; border-left:2px solid; padding:4px 8px; border-radius:0 4px 4px 0; margin-top:4px; line-height:1.5; background: var(--rd-neutral-50); }

/* Custom for Initiation */
.edit-input { background: #ffffff !important; border: 1px solid var(--rd-accent) !important; color: #0f172a !important; font-size: 1.5rem !important; font-weight: bold !important; padding: 5px 15px !important; border-radius: 8px !important; }

.pc-edit-wrap .edit-only { display: none !important; }
.pc-edit-wrap .view-only { display: block; }
.pc-edit-wrap.is-editing .edit-only { display: block !important; }
.pc-edit-wrap.is-editing div.edit-only.d-flex,
.pc-edit-wrap.is-editing .edit-only.d-flex { display: flex !important; }
.pc-edit-wrap.is-editing span.edit-only,
.pc-edit-wrap.is-editing .edit-only.d-inline-flex { display: inline-flex !important; }
.pc-edit-wrap.is-editing .edit-only.badge { display: inline-block !important; }
.pc-edit-wrap.is-editing .pc-plus-btn.edit-only { display: inline-flex !important; }
.pc-edit-wrap.is-editing th.edit-only,
.pc-edit-wrap.is-editing td.edit-only { display: table-cell !important; }
.pc-edit-wrap.is-editing .view-only { display: none !important; }

.pc-edit-toggle-btn { width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
.pc-mini-save { padding: 4px 10px; font-size: 11px; border-radius: 6px; }
.pc-plus-btn { width: 22px; height: 22px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px solid var(--rd-border2); background: #ffffff; color: var(--rd-accent); }
.pc-plus-btn:hover { background: var(--rd-surface2); color: var(--rd-accent-dark); border-color: var(--rd-accent); }
.pc-edit-card { border: 1px dashed var(--rd-border2); border-radius: 8px; padding: 10px; background: var(--rd-surface2); }

/* Multi-column quote table styles - LIGHT THEME */
#pcMultiQuoteTable { border: 1px solid var(--rd-border2) !important; background: #ffffff; table-layout: fixed; border-collapse: separate; border-spacing: 0; width: auto; }
#pcMultiQuoteTable th, #pcMultiQuoteTable td { border: 1px solid var(--rd-border) !important; font-size: 12px; vertical-align: middle; padding: 6px 10px; color: var(--rd-text1); overflow: hidden; text-overflow: ellipsis; }
#pcMultiQuoteTable thead th { border-bottom: 2px solid var(--rd-accent) !important; background: var(--rd-surface2); color: var(--rd-text1); font-weight: 700; letter-spacing: 0.5px; position: sticky; top: 0; z-index: 20; }
#pcMultiQuoteBody tr:hover td { background-color: var(--rd-text1); }
.pc-price-input { border: 1px solid var(--rd-border2) !important; height: 30px !important; font-size: 13px !important; font-weight: 700 !important; color: var(--rd-accent) !important; padding: 2px 8px !important; width: 100% !important; border-radius: 4px !important; text-align: center; background: #ffffff; transition: all 0.2s; }
.pc-price-input:focus { border-color: var(--rd-accent) !important; box-shadow: 0 0 0 2px rgba(95,120,88,0.15) !important; background: #ffffff !important; outline: none; }
.pc-price-input:disabled { opacity: 0.5; cursor: not-allowed; background: var(--rd-surface2) !important; border-color: var(--rd-border) !important; }
.pc-vendor-name-input { font-size: 12px !important; font-weight: 700 !important; height: 28px !important; border: 1px solid var(--rd-border2) !important; border-radius: 4px !important; background: #ffffff !important; color: var(--rd-text1) !important; width: 100% !important; }
.pc-col-winner { background: rgba(22, 163, 74, 0.08) !important; }
#pcMultiQuoteTable tfoot td { position: sticky; bottom: 0; z-index: 20; background: var(--rd-surface2) !important; border-top: 2px solid var(--rd-accent) !important; color: var(--rd-text1) !important; box-shadow: 0 -3px 10px rgba(0,0,0,0.06); }
#pcMultiQuoteTable tfoot .pc-item-sticky { background: var(--rd-surface2) !important; color: var(--rd-accent) !important; border-right: 1px solid var(--rd-border2) !important; text-align: right; }

/* Excel Spreadsheet Live Viewer Styles */
.excel-table { width: 100%; border-collapse: collapse; font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: var(--rd-text1); background: #ffffff; }
.excel-table th, .excel-table td { border: 1px solid var(--rd-border2) !important; padding: 6px 12px; white-space: nowrap; text-align: left; }
.excel-table th { background: var(--rd-surface2) !important; color: var(--rd-accent) !important; font-weight: 700; position: sticky; top: 0; z-index: 5; }
.excel-table tr:hover td { background: var(--rd-surface2); }
.excel-tab-btn { background: var(--rd-surface2); color: var(--rd-text2); border: 1px solid var(--rd-border2); border-radius: 4px; padding: 3px 12px; font-size: 11px; cursor: pointer; transition: all 0.2s; white-space: nowrap; }
.excel-tab-btn:hover { background: var(--rd-accent-soft); color: var(--rd-accent); }
/* Searchable Firm Autocomplete Dropdown - Floating Overlay */
.pc-firm-dropdown-wrap { position: relative; width: 100%; }
#pcGlobalFirmDropdown {
    position: fixed;
    max-height: 230px;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid var(--rd-border2);
    border-radius: 6px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    z-index: 10650 !important;
    text-align: left;
    scrollbar-width: thin;
    display: none;
}
#pcGlobalFirmDropdown::-webkit-scrollbar { width: 5px; }
#pcGlobalFirmDropdown::-webkit-scrollbar-track { background: var(--rd-surface2); }
#pcGlobalFirmDropdown::-webkit-scrollbar-thumb { background: var(--rd-border3); border-radius: 3px; }
.pc-firm-opt {
    padding: 7px 12px;
    font-size: 11px;
    font-weight: 600;
    color: var(--rd-text1);
    cursor: pointer;
    border-bottom: 1px solid var(--rd-border);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: background 0.12s ease, color 0.12s ease;
}
.pc-firm-opt:last-child { border-bottom: none; }
.pc-firm-opt:hover, .pc-firm-opt.active {
    background: var(--rd-accent-soft);
    color: var(--rd-accent);
}
.pc-firm-opt mark {
    background: transparent;
    color: var(--rd-accent);
    font-weight: bold;
    text-decoration: underline;
    padding: 0;
}









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
                            <a href="{{ route('purchase.case_detail', $purchase->pcs_id) }}" target="_blank" class="btn-hdr-action btn-hdr-case-detail rajdhani">
                                <i class="fas fa-list-alt mr-1"></i> CASE DETAIL
                            </a>
                            @php
                                $hasItLetter = $purchase->itLetter || \App\Models\PurItLetter::where('pit_pcs_id', $purchase->pcs_id)->exists();
                                $isPsCase = in_array(strtolower(trim((string)($purchase->pcs_type ?? 'ps'))), ['ps', 'mat', 'material', 'eqp', 'equipment', 'cons', 'consultancy', 'serv', 'services'], true);
                            @endphp

                            @if($isDProc)
                                @if(!$hasItLetter)
                                    {{-- Procurement user sees button to CREATE IT --}}
                                    <button type="button" onclick="promptCreateIt({{ $purchase->pcs_id }})" class="btn-hdr-action btn-hdr-it-annex rajdhani" style="background: #f59e0b !important; color: #fff !important; border: 1px solid #d97706 !important; cursor: pointer;">
                                        <i class="fas fa-plus-circle mr-1"></i> CREATE IT / RFQ
                                    </button>
                                @else
                                    {{-- Procurement user sees button to EDIT/VIEW IT --}}
                                    <a href="{{ route('purchase.it_annex', $purchase->pcs_id) }}" target="_blank" class="btn-hdr-action btn-hdr-it-annex rajdhani">
                                        <i class="fas fa-file-signature mr-1"></i> EDIT / VIEW IT & ANNEX
                                    </a>
                                @endif
                            @else
                                {{-- Other users (Finance, Division, MD, DDG, DG) see VIEW IT / RFQ LETTER for PS cases --}}
                                @if($isPsCase || $hasItLetter)
                                    <a href="{{ route('purchase.it_annex', $purchase->pcs_id) }}" target="_blank" class="btn-hdr-action btn-hdr-it-annex rajdhani">
                                        <i class="fas fa-eye mr-1"></i> VIEW IT / RFQ LETTER
                                    </a>
                                @endif
                            @endif
                            <a href="{{ route('purchase.cs_formal', $purchase->pcs_id) }}" target="_blank" class="btn-hdr-action btn-hdr-comparative-stmt rajdhani">
                                <i class="fas fa-balance-scale mr-1"></i> COMPARATIVE STATEMENT
                            </a>
                            <a href="{{ $backRoute }}" class="dg-back-btn" style="padding: 6px 15px; font-size: 12px;">
                                <i class="fas fa-arrow-left mr-1"></i> Back
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-3" style="flex:1; overflow-y:auto;">
                        @php
                            $caseType = strtolower(trim((string) ($purchase->pcs_type ?? 'ps')));
                            $isProcCase = in_array($caseType, ['ps', 'mat', 'material', 'eqp', 'equipment', 'cons', 'consultancy', 'serv', 'services'], true);
                        @endphp
                        @if($isInitiator && $isDraft && $isProcCase)
                            @if($hasFloated && !$hasDProcSaved)
                                <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center" style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;">
                                    <i class="fas fa-paper-plane mr-2 text-primary"></i>
                                    <div class="small font-weight-bold text-primary">Case Floated to Procurement Department — Currently awaiting quotation collection and remarks from Director Procurement.</div>
                                </div>
                            @elseif($hasFloated && $hasDProcSaved)
                                <div class="alert alert-success py-2 px-3 mb-3 d-flex align-items-center" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                                    <i class="fas fa-check-circle mr-2 text-success"></i>
                                    <div class="small font-weight-bold text-success">Director Procurement has provided quotations and remarks. You can now review and <strong>Release Case to HQ</strong>.</div>
                                </div>
                            @endif
                        @endif

                        {{-- Case Header Metadata --}}
                        <div class="mb-4 d-flex align-items-start gap-4">
                            <div style="flex: 1;">
                                <div class="d-flex align-items-start mb-2" style="font-size: 13px;">
                                    <strong style="color: #475569; width: 140px; display:inline-block; flex-shrink: 0; font-weight: 700;"><i class="fas fa-tag text-primary mr-2"></i>CASE TITLE:</strong>
                                    <div class="view-only font-weight-bold text-dark" id="pcTitleView" style="font-size: 16px; color: #0f172a !important;">{{ $purchase->pcs_title }}</div>
                                    @if($canEdit)
                                        <form class="edit-only d-flex align-items-center flex-grow-1" id="pcTitleForm" style="gap:10px; margin:0;" action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST">
                                             @csrf
                                            <input type="hidden" name="op" value="save_title">
                                            <input type="text" name="pcs_title" class="edit-input flex-grow-1" style="font-size: 13px !important; padding: 4px 12px !important;" value="{{ $purchase->pcs_title }}">
                                            <button type="submit" class="btn btn-primary pc-mini-save rajdhani font-weight-bold"><i class="fas fa-save mr-1"></i> SAVE</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="d-flex flex-column" style="gap: 8px; font-size: 13px;">
                                    <div><strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-hashtag text-primary mr-2"></i>CASE ID:</strong> <span class="text-dark font-weight-bold" style="color: #0f172a !important;">#{{ $purchase->pcs_id }}</span></div>
                                    <div><strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="far fa-calendar-alt text-primary mr-2"></i>DATE:</strong> <span class="text-dark font-weight-bold" style="color: #0f172a !important;">{{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}</span></div>
                                    <div><strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-project-diagram text-primary mr-2"></i>PROJECT:</strong> <span class="text-dark font-weight-bold" style="color: #0f172a !important;">{{ $purchase->project?->prj_code ?? $purchase->pcs_hed_id }}</span></div>
                                    <div><strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-building text-primary mr-2"></i>DIVISION:</strong> <span class="text-dark font-weight-bold" style="color: #0f172a !important;">{{ $purchase->unit?->unt_name ?? $purchase->pcs_unt_id }}</span></div>
                                    <div>
                                        <strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-info-circle text-primary mr-2"></i>STATUS:</strong> 
                                        <span class="badge badge-warning text-dark font-weight-bold px-2 py-1" style="font-size: 11px;">
                                            {{ $purchase->pcs_status }}
                                            @if($purchase->current_stage_display)
                                                — Currently with: {{ $purchase->current_stage_display }}
                                            @endif
                                        </span>
                                    </div>

                                    {{-- Attached Project Documents Box (Read-Only) --}}
                                    @php
                                        $prjId = $purchase->project?->prj_id 
                                            ?? ($purchase->head?->hed_prj_id ?? \Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $purchase->pcs_hed_id)->value('hed_prj_id'));
                                        
                                        $projectAttachments = $prjId 
                                            ? \Illuminate\Support\Facades\DB::table('prj.prjattachments')
                                                ->where('jat_objid', $prjId)
                                                ->whereIn('jat_objtype', ['prj', 'Project'])
                                                ->whereNotNull('jat_path')
                                                ->where('jat_path', '<>', '')
                                                ->get()
                                            : collect();
                                    @endphp
                                    <div class="mt-2" style="max-width: 420px;">
                                        <div class="card border shadow-sm" style="border-radius: 6px; border-color: #cbd5e1 !important; background: #ffffff;">
                                            <div class="card-header py-1 px-2 d-flex align-items-center justify-content-between" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-paperclip text-primary mr-1" style="font-size: 10px;"></i>
                                                    <span class="font-weight-bold" style="font-size: 10px; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Attachments</span>
                                                    <span class="badge badge-secondary badge-pill ml-1" style="font-size: 9px; padding: 1px 5px;">{{ $projectAttachments->count() }}</span>
                                                </div>
                                            </div>
                                            <div class="p-1" style="font-size: 11px;">
                                                @if($projectAttachments->count() > 0)
                                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3px 6px;">
                                                        @foreach($projectAttachments as $pDoc)
                                                            @php
                                                                $ext = strtolower(pathinfo($pDoc->jat_path, PATHINFO_EXTENSION));
                                                                $icon = match($ext) {
                                                                    'pdf' => 'fa-file-pdf text-danger',
                                                                    'jpg', 'jpeg', 'png' => 'fa-file-image text-primary',
                                                                    'doc', 'docx' => 'fa-file-word text-info',
                                                                    'xls', 'xlsx' => 'fa-file-excel text-success',
                                                                    default => 'fa-file-alt text-secondary'
                                                                };
                                                            @endphp
                                                            <div class="d-flex justify-content-between align-items-center px-1 py-0.5 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0; min-height: 24px;">
                                                                <div class="d-flex align-items-center overflow-hidden mr-1" style="flex: 1; min-width: 0;">
                                                                    <i class="fas {{ $icon }} mr-1 flex-shrink-0" style="font-size: 10px; width: 11px;"></i>
                                                                    <span class="text-truncate font-weight-bold text-dark" style="font-size: 10px; line-height: 1.1;" title="{{ $pDoc->jat_type }}">
                                                                        {{ $pDoc->jat_type }}
                                                                    </span>
                                                                </div>
                                                                <a href="{{ \App\Facades\FileStorage::url($pDoc->jat_path) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-1 flex-shrink-0" style="font-size: 9px; line-height: 1.2; border-radius: 3px;" title="View {{ $pDoc->jat_type }}">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-center py-1 text-muted" style="font-size: 10px;">
                                                        <i class="fas fa-folder-open text-muted mr-1"></i> No project documents attached.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Financial Overview & Case Cost Summary --}}
                            @php
                                $winningQuote = $purchase->quotes->sortBy(function($q) {
                                    return (float)($q->qte_midprice ?: ($q->qte_price + ($q->qte_inttax ?? $q->qte_midtax ?? 0)));
                                })->first();

                                if ($winningQuote) {
                                    $initBase = (float)($winningQuote->qte_intprice ?: $winningQuote->qte_price);
                                    $initTax = (float)($winningQuote->qte_inttax ?: ($winningQuote->qte_midtax ?: 0));
                                    $initTaxType = strtoupper($winningQuote->qte_taxtype ?? ($winningQuote->qte_quotetype ?? 'GST'));
                                    $initSst = str_contains($initTaxType, 'SST') ? $initTax : 0;
                                    $initGst = !str_contains($initTaxType, 'SST') ? $initTax : 0;
                                    $initTot = $initBase + $initSst + $initGst;
                                } else {
                                    $initBase = (float)($purchase->pcs_intprice ?: $purchase->pcs_price);
                                    $initTax = (float)($purchase->pcs_inttax ?: ($purchase->pcs_midtax ?: 0));
                                    $initSst = 0;
                                    $initGst = $initTax;
                                    $initTot = $initBase + $initTax;
                                }
                            @endphp
                            <div class="text-right d-flex flex-column align-items-end" style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 12px 16px; font-size: 12px; min-width: 260px;">
                                <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                                    <h6 class="rajdhani text-primary font-weight-bold mb-0" style="font-size: 11px; letter-spacing: 0.8px;">
                                        <i class="fas fa-chart-pie mr-1"></i> FINANCIAL PULSE
                                    </h6>
                                    <button class="btn btn-xs btn-outline-primary rajdhani font-weight-bold py-0" data-toggle="modal" data-target="#financialIntelligenceModal" style="font-size: 9px; border-radius: 4px;">
                                        <i class="fas fa-expand-arrows-alt mr-1"></i> FULL REPORT
                                    </button>
                                </div>
                                
                                <div class="w-100 rajdhani" style="display: grid; grid-template-columns: auto 1fr; gap: 3px 20px; text-align: left;">
                                    <div class="text-muted small font-weight-bold">RECEIVED</div>
                                    <div class="text-dark font-weight-bold text-right" style="color: #0f172a !important;">{{ number_format($finReceived) }}</div>
                                    
                                    <div class="text-muted small font-weight-bold">EXPENDITURE</div>
                                    <div class="text-danger font-weight-bold text-right">{{ number_format($finExpenditure) }}</div>
                                    
                                    <div class="text-muted small font-weight-bold">BALANCE</div>
                                    <div class="text-primary font-weight-bold text-right" style="color: var(--rd-primary-700) !important;">{{ number_format($finBalance) }}</div>
                                    
                                    <div class="text-muted small font-weight-bold">COMMITMENTS</div>
                                    <div class="text-warning font-weight-bold text-right" style="color: #d97706 !important;">{{ number_format($finCommitments) }}</div>
                                    
                                    <div class="text-muted small font-weight-bold">IN PROCESS</div>
                                    <div class="text-muted text-right">{{ number_format($finInProcess) }}</div>
                                    
                                    <div class="text-success font-weight-bold small border-top pt-1" style="border-color: var(--rd-text1) !important;">AVAILABLE</div>
                                    <div class="text-success font-weight-bold text-right border-top pt-1" style="border-color: var(--rd-text1) !important; color: #16a34a !important;">{{ number_format($finAvailable) }}</div>
                                    
                                    <div class="text-warning font-weight-bold small" style="font-size: 11px; color: #d97706 !important;">CAN BE SPENT</div>
                                    <div class="text-warning font-weight-bold text-right" style="font-size: 13px; color: #d97706 !important;">{{ number_format($finCanBeSpent) }}</div>
                                </div>

                                {{-- Separator --}}
                                <div class="w-100 my-2" style="border-top: 1px dashed #cbd5e1;"></div>

                                {{-- Case Cost Summary Header --}}
                                <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                    <h6 class="rajdhani text-primary font-weight-bold mb-0" style="font-size: 11px; letter-spacing: 0.8px;">
                                        <i class="fas fa-file-invoice-dollar mr-1"></i> CASE FINANCIALS
                                    </h6>
                                </div>

                                {{-- Compact Case Cost Grid --}}
                                <div class="w-100 rajdhani" style="display: grid; grid-template-columns: auto 1fr; gap: 3px 20px; text-align: left;">
                                    <div class="text-muted small" style="font-size: 11px;">Price</div>
                                    <div class="text-dark font-weight-bold text-right" id="pcSummaryBasePrice" style="font-size: 12px; color: #0f172a !important;">{{ number_format($initBase, 2) }}</div>
                                    
                                    <div class="text-muted small" style="font-size: 11px;">SST</div>
                                    <div class="text-dark font-weight-bold text-right" id="pcSummarySst" style="font-size: 12px; color: #0f172a !important;">{{ number_format($initSst, 2) }}</div>
                                    
                                    <div class="text-muted small" style="font-size: 11px;">GST</div>
                                    <div class="text-dark font-weight-bold text-right" id="pcSummaryGst" style="font-size: 12px; color: #0f172a !important;">{{ number_format($initGst, 2) }}</div>
                                    
                                    <div class="text-success font-weight-bold small border-top pt-1" style="font-size: 11px; border-color: var(--rd-text1) !important; color: #16a34a !important;">TOTAL</div>
                                    <div class="text-success font-weight-bold text-right border-top pt-1" id="pcSummaryTotal" style="font-size: 15px; border-color: var(--rd-text1) !important; color: #16a34a !important;">{{ number_format($initTot, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dg-divider mb-4 mt-2" style="background: #e2e8f0;"></div>
                        
                        {{-- 1. Items Section --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="dg-sec-label mb-0"><i class="fas fa-boxes fa-xs"></i> Items</div>
                                @if($canEdit)
                                    <button type="button" class="pc-plus-btn edit-only" id="pcAddItemInlineBtn" title="Add Item"><i class="fas fa-plus"></i></button>
                                @endif
                            </div>
                            <div class="dg-items-wrap" style="max-height: 250px; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; overflow: hidden;">
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
                                <div class="edit-only mt-3 p-3 rounded" id="pcInlineItemEditor" style="display:none; background: #f8fafc; border: 1.5px solid #93c5fd; box-shadow: 0 4px 12px rgba(37,99,235,0.08);">
                                    <div class="d-flex align-items-center mb-2" style="color: var(--rd-primary-700); font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">
                                        <i class="fas fa-plus-circle mr-1 text-primary"></i> ADD NEW ITEM TO CASE
                                    </div>
                                    <form id="pcAddItemForm" class="d-flex align-items-center" style="gap:10px; margin:0;">
                                        <div class="flex-grow-1">
                                            <input name="item_desc" id="pcItemDesc" class="form-control form-control-sm" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 36px; font-size: 12px;" required placeholder="Enter item description / specification...">
                                        </div>
                                        <div style="width:110px;">
                                            <input name="item_qty" id="pcItemQty" type="number" step="0.01" value="1" class="form-control form-control-sm text-center" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 36px; font-size: 12px;" required placeholder="Qty">
                                        </div>
                                        <div class="d-flex align-items-center" style="gap:6px;">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="pcItemCancelBtn" title="Cancel" style="height: 36px; width: 36px; padding: 0;"><i class="fas fa-times"></i></button>
                                            <button type="submit" class="btn btn-primary btn-sm px-3 rajdhani font-weight-bold" style="height: 36px; font-size: 12px; letter-spacing: 0.5px;"><i class="fas fa-plus mr-1"></i> ADD</button>
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
                                </div>
                            </div>
                                <div class="table-responsive" style="border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; overflow: hidden;">
                                    <table class="dg-items-table">
                                        <thead>
                                            <tr>
                                                <th class="pl-3" style="width: 50px;">S.No</th>
                                                <th>Firm Name</th>
                                                <th class="text-right pr-3">Price (PKR)</th>
                                                <th class="text-center" style="width: 80px;">Quote</th>
                                                <th class="text-right pr-3 edit-only" style="width: 140px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="pcQuotesBody">
                                            {{-- Rendered by JS --}}
                                        </tbody>
                                    </table>
                                </div>
                                <input type="file" id="pcDirectQuoteUploadInput" style="display:none;" accept=".pdf,.jpg,.jpeg,.png,.webp,.gif,.bmp,.svg,.doc,.docx,.xls,.xlsx,.csv,.txt">
                        </div>

                        {{-- 3. Case Remarks Section --}}
                        <div class="mb-4">
                            <div class="dg-sec-label mb-2"><i class="fas fa-info-circle fa-xs"></i> Case Remarks</div>
                            <div class="view-only p-3 rounded" id="pcRemarksText" style="background: #f8fafc; border: 1.5px solid #e2e8f0; font-size: 13px; line-height: 1.6; border-radius: 8px; color: #1e293b;">
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
                                    <textarea name="pcs_remarks" id="pcRemarksInput" class="form-control" rows="3" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; resize: vertical;" placeholder="Enter case remarks...">{{ $purchase->pcs_remarks }}</textarea>
                                    <div class="d-flex justify-content-end mt-2">
                                        <button type="submit" class="btn btn-primary btn-sm rajdhani font-weight-bold px-3" style="height: 32px; font-size: 12px; letter-spacing: 0.5px;"><i class="fas fa-save mr-1"></i> SAVE REMARKS</button>
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
                                <div class="edit-only p-3 rounded" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px;">
                                    <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST" enctype="multipart/form-data" id="pcFilesForm" class="d-flex align-items-center" style="gap: 10px;">
                                        @csrf
                                        <input type="hidden" name="op" value="add_files">
                                        <div class="flex-grow-1">
                                            <input type="file" name="attachments[]" id="pcFilesInput" multiple class="form-control form-control-sm" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 36px; padding: 5px 8px; font-size: 12px;" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm px-3 rajdhani font-weight-bold" style="height: 36px; font-size: 12px; letter-spacing: 0.5px; white-space: nowrap;"><i class="fas fa-upload mr-1"></i> UPLOAD</button>
                                    </form>
                                    <div class="text-muted small mt-2" style="font-size: 11px;"><i class="fas fa-info-circle mr-1 text-primary"></i> Supported formats: PDF, JPG, PNG, DOC, DOCX (Max 10MB each)</div>
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
                                <i class="fas fa-file-alt text-primary" style="font-size: 12px;"></i>
                                <span class="dg-panel-r-title" style="font-size: 12px; color: #0f172a !important;">Minute</span>
                            </div>
                             <div class="d-flex gap-2">
                                <a href="{{ route('purchase.minute_view', $purchase->pcs_id) }}" target="_blank" class="btn btn-sm btn-outline-primary rajdhani font-weight-bold" style="padding:4px 12px; font-size:11px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
                                    <i class="fas fa-eye mr-1"></i> VIEW MINUTE
                                </a>
                            </div>
                        </div>
                        
                        <div class="p-3" id="conversational-comments-box" style="max-height: 600px; overflow-y: auto;">
                            
                            {{-- Decision Panel (Integrated as a Minute Entry) --}}
                            @if($isDProc && $isDraft)
                                @include('approvals_new._action_box', ['isDProcDraft' => true])
                            @else
                                @include('approvals_new._action_box', ['isDProcDraft' => false])
                            @endif

                            <div>
                                @php
                                    // Calculate global running sequential numbering for conversation trail (1, 2, 3...)
                                    $trailRunningNumberMap = [];
                                    $currSeq = 1;
                                    foreach($purchase->decisions->where('pdec_action', '!=', 'save_draft')->sortBy('created_at') as $d) {
                                        $trailRunningNumberMap[$d->pdec_id] = $currSeq;
                                        $count = 1;
                                        if (!empty($d->pdec_remarks) && strpos($d->pdec_remarks, '<li') !== false) {
                                            $count = max(1, substr_count($d->pdec_remarks, '<li'));
                                        }
                                        $currSeq += $count;
                                    }
                                @endphp

                                @forelse($purchase->decisions->where('pdec_action', '!=', 'save_draft')->sortByDesc('created_at') as $decision)
                                    @php
                                        $act = $decision->pdec_action;
                                        $color = 'primary';
                                        $actionVerb = 'Forwarded';
                                        if($act == 'approve') { $color = 'success'; $actionVerb = 'Approved'; }
                                        elseif($act == 'return') { $color = 'warning'; $actionVerb = 'Returned'; }
                                        elseif($act == 'hold') { $color = 'warning'; $actionVerb = 'Reverted'; }
                                        elseif($act == 'reject' || $act == 'not_approved') { $color = 'danger'; $actionVerb = 'Not Recommended'; }
                                        elseif($act == 'dproc_save') { $color = 'info'; $actionVerb = 'Updated Quotations'; }
                                        elseif($act == 'float_to_proc') { $color = 'info'; $actionVerb = 'Floated to Procurement'; }
                                        elseif($act == 'reshare_to_proc') { $color = 'warning'; $actionVerb = 'Reshared to Procurement'; }

                                        $toStatusDisplay = $service->getStatusDisplayName($decision->pdec_to_status);
                                        $startNumber = $trailRunningNumberMap[$decision->pdec_id] ?? 1;
                                        $rawRemarks = $decision->pdec_remarks;
                                        $hasHtmlLi = !empty($rawRemarks) && strpos($rawRemarks, '<li') !== false;
                                        $hasRemarks = !empty(trim(strip_tags($rawRemarks)));

                                        if ($hasHtmlLi) {
                                            $innerLis = preg_replace('/<\/?(ol|ul)[^>]*>/i', '', $rawRemarks);
                                            $trailHtml = '<ol start="' . $startNumber . '" style="margin-bottom:0; padding-left:18px; color: #1e293b;">' . $innerLis . '</ol>';
                                        } elseif ($hasRemarks) {
                                            $cleanText = trim(strip_tags($rawRemarks));
                                            $trailHtml = '<ol start="' . $startNumber . '" style="margin-bottom:0; padding-left:18px; color: #1e293b;"><li>' . e($cleanText) . '</li></ol>';
                                        } else {
                                            $defaultText = '';
                                            if ($decision->pdec_action == 'dproc_save') {
                                                $defaultText = 'DProc updated quotations for this case.';
                                            } elseif ($decision->pdec_action == 'float_to_proc') {
                                                $defaultText = 'Case floated to Procurement Department for quotation collection.';
                                            } elseif ($decision->pdec_action == 'reshare_to_proc') {
                                                $defaultText = 'Case reshared to Procurement Department for quotation correction.';
                                            } else {
                                                $defaultText = 'Case ' . strtolower($actionVerb) . ' to ' . $toStatusDisplay . ' without additional remarks.';
                                            }
                                            $trailHtml = '<ol start="' . $startNumber . '" style="margin-bottom:0; padding-left:18px; color: #1e293b;"><li>' . e($defaultText) . '</li></ol>';
                                        }
                                    @endphp
                                    <div class="mb-4 pb-2" id="user-comment-{{$decision->pdec_id}}">
                                        <div class="d-flex align-items-center justify-content-between mb-1 border-bottom pb-1" style="border-bottom: 1px dashed #cbd5e1 !important;">
                                            <div class="font-weight-bold rajdhani text-dark" style="font-size: 14px; color: #0f172a !important;">
                                                <i class="fas fa-user-circle text-primary mr-1"></i> {{ $decision->account->acc_name }} 
                                                <span class="text-muted small ml-1" style="font-weight: 600;">({{ strtoupper($decision->pdec_role) }})</span>
                                                <span class="ml-2 pl-2 border-left border-secondary font-weight-bold" style="font-size: 11px; color: var(--rd-{{$color}}); letter-spacing: 0.5px;">
                                                    <i class="fas fa-caret-right mr-1"></i>{{ strtoupper($actionVerb) }}
                                                </span>
                                            </div>
                                            <span class="text-muted" style="font-size:10px; font-weight: 600;">
                                                {{ \Carbon\Carbon::parse($decision->created_at)->format('d M, h:i A') }}
                                            </span>
                                        </div>
                                        <div class="mt-2" style="line-height: 1.5; font-size:13px; color: #1e293b !important; padding-left: 5px;">
                                            {!! $trailHtml !!}
                                        </div>

                                    </div>
                                @empty
                                    <div class="text-center text-muted small py-3">No remarks yet.</div>
                                @endforelse
                            </div>
                    </div>
                    </div>
                    
                    {{-- Recent Approved Cases --}}
                    <div class="dg-panel-r mt-3">
                        <div class="dg-panel-r-hdr py-2 px-3">
                            <span class="dg-panel-r-title" style="font-size: 12px; color: #0f172a !important;"><i class="fas fa-check-circle mr-1 text-success"></i> Recent Approved Cases</span>
                        </div>
                        <div class="dg-trail-body p-0" style="max-height: 300px;">
                            <table class="dg-items-table" style="font-size: 11px;">
                                <thead>
                                    <tr>
                                        <th class="pl-3" style="width: 35px;">S.No</th>
                                        <th>Case Title</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-center pr-3">Items</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentApproved ?? collect() as $ri => $rc)
                                    @php
                                        $targetRoute = $isInitiator ? route('purchase.initiation.show', $rc->pcs_id) : route('nrdi.purchase_cases_new.show', $rc->pcs_id);
                                    @endphp
                                    <tr class="cursor-pointer" onclick="window.location='{{ $targetRoute }}'">
                                        <td class="pl-3 text-muted">{{ $ri + 1 }}</td>
                                        <td>
                                            <div class="text-dark font-weight-bold" style="color: #0f172a !important; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 140px;">{{ $rc->pcs_title }}</div>
                                            <div class="text-muted" style="font-size: 10px;">{{ \Carbon\Carbon::parse($rc->pcs_date)->format('d M, Y') }}</div>
                                        </td>
                                        <td class="text-right font-weight-bold text-success">{{ number_format($rc->pcs_price) }}</td>
                                        <td class="text-center pr-3 text-muted">{{ $rc->items_count }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted small py-3 italic">No previously approved cases for this head.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Financial Overview moved to Header --}}

                </div>
            </div>
        </div>
    </div>

<div id="pcGlobalFirmDropdown"></div>

@if($canAddQuotes)

<div class="modal fade" id="pcAddQuoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 98%;">
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-radius: 10px; overflow: hidden; color: var(--rd-text1); display: flex; flex-direction: column; height: 85vh; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
            
            {{-- HEADER --}}
            <div class="modal-header py-2 px-3 align-items-center flex-shrink-0" style="background: var(--rd-surface2); border-bottom: 1px solid var(--rd-border);">
                <div class="d-flex align-items-center flex-grow-1">
                    <i class="fas fa-file-invoice-dollar mr-2 text-primary" style="font-size: 18px;"></i>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0 rajdhani" style="color: var(--rd-text1); font-size: 14px; letter-spacing: 1px;">Add Quotations</h6>
                        <div class="small text-muted" style="font-size: 10px;">Group: <span class="text-primary font-weight-bold">{{ $purchase->pcs_title }}</span> | ID: <span class="text-primary font-weight-bold">PC-{{ $purchase->pcs_id }}</span></div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 mr-4">
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-2 small font-weight-bold text-dark">TAX TYPE:</label>
                        <select id="pcGlobalTaxType" class="form-control form-control-sm" style="width: 80px; height: 26px; font-size: 11px; background: #ffffff; color: var(--rd-text1); border-color: var(--rd-border2);">
                            <option value="GST">GST</option>
                            <option value="SST">SST</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-2 small font-weight-bold text-dark">TAX %:</label>
                        <input type="number" id="pcGlobalTaxPercent" class="form-control form-control-sm" value="18" style="width: 60px; height: 26px; font-size: 11px; background: #ffffff; color: var(--rd-text1); border-color: var(--rd-border2);">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3" id="pcAddVendorColBtn" style="height: 30px; font-size: 12px;"><i class="fas fa-user-plus mr-1"></i> Add Vendor</button>
                    <button type="button" class="close ml-2 text-dark" data-dismiss="modal">&times;</button>
                </div>

            </div>

            {{-- BODY: scrollable table area --}}
            <div class="flex-grow-1" style="overflow: auto; min-height: 0; background: #ffffff;">
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
            <div class="py-2 px-3 d-flex justify-content-between align-items-center flex-shrink-0" style="background: var(--rd-surface2); border-top: 1px solid var(--rd-border);">
                <div class="small text-muted" style="font-size: 11px;"><i class="fas fa-info-circle mr-1"></i> Ensure all vendor names are entered before saving.</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-4" data-dismiss="modal" style="height: 32px; font-size: 12px; letter-spacing: 0.5px;">CLOSE</button>
                    <button type="button" class="btn btn-success btn-sm font-weight-bold px-4" id="pcSaveAllQuotesBtn" style="height: 32px; font-size: 12px; letter-spacing: 0.5px;"><i class="fas fa-save mr-1"></i> SAVE QUOTATIONS</button>
                </div>
            </div>

        </div>
    </div>
</div>
@endif
{{-- Live Quotation Document Viewer Modal --}}
<div class="modal fade" id="pcQuoteViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 92%; height: 90vh;">
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-radius: 12px; overflow: hidden; height: 100%; display: flex; flex-direction: column; box-shadow: 0 10px 40px rgba(0,0,0,0.15);">
            <div class="modal-header py-2 px-3 align-items-center justify-content-between flex-shrink-0" style="background: var(--rd-surface2); border-bottom: 1px solid var(--rd-border);">
                <div class="d-flex align-items-center">
                    <i class="fas fa-file-invoice-dollar text-primary mr-2" style="font-size: 18px;"></i>
                    <h6 class="modal-title font-weight-bold text-dark mb-0 rajdhani" id="pcQuoteViewerTitle" style="letter-spacing: 1px;">QUOTATION DOCUMENT</h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    {{-- Diagnostics Button (Commented Out) --}}
                    {{--
                    <button type="button" id="pcQuoteViewerDiagBtn" class="btn btn-xs btn-outline-warning px-2 py-1" style="font-size: 11px;">
                        <i class="fas fa-stethoscope mr-1"></i> Diagnostics
                    </button>
                    --}}
                    <a href="#" id="pcQuoteViewerOpenNewTab" target="_blank" class="btn btn-xs btn-outline-primary px-3 py-1" style="font-size: 11px;">
                        <i class="fas fa-external-link-alt mr-1"></i> Open in New Tab
                    </a>
                    <button type="button" class="close text-dark ml-2" data-dismiss="modal">&times;</button>
                </div>
            </div>
            <div class="modal-body p-0 flex-grow-1" style="background: #f8fafc; position: relative; overflow: hidden;" id="pcQuoteViewerBody">
                <iframe id="pcQuoteViewerIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                <div id="pcQuoteViewerImgWrap" style="display:none; width: 100%; height: 100%; overflow: auto; align-items: center; justify-content: center; padding: 20px;">
                    <img id="pcQuoteViewerImg" src="" style="max-width: 100%; max-height: 100%; object-fit: contain; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-radius: 6px; border: 1px solid var(--rd-border);">
                </div>
                <div id="pcQuoteViewerDocWrap" style="display:none; width: 100%; height: 100%; overflow-y: auto; background: #f8fafc; padding: 30px 15px; justify-content: center;">
                    <div id="pcQuoteViewerDocContent" style="background: #ffffff; color: #1e293b; width: 100%; max-width: 860px; min-height: 100%; padding: 45px 50px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-radius: 8px; border: 1px solid var(--rd-border); font-family: 'Segoe UI', Arial, sans-serif; font-size: 14px; line-height: 1.7;">
                    </div>
                </div>
                <div id="pcQuoteViewerSheetWrap" style="display:none; width: 100%; height: 100%; flex-direction: column; background: #ffffff;">
                    <div id="pcQuoteViewerSheetTabs" class="d-flex align-items-center px-3 py-2 flex-shrink-0" style="background: var(--rd-surface2); border-bottom: 1px solid var(--rd-border); gap: 6px; overflow-x: auto;">
                    </div>
                    <div id="pcQuoteViewerSheetContent" class="flex-grow-1 p-3" style="overflow: auto; background: #ffffff;">
                    </div>
                </div>
                <div id="pcQuoteViewerLoading" style="display:none; position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.92); align-items:center; justify-content:center; flex-direction:column; z-index:10;">
                    <i class="fas fa-circle-notch fa-spin fa-3x text-primary mb-3"></i>
                    <span class="text-dark rajdhani font-weight-bold" style="font-size: 16px; letter-spacing: 1px;">RENDERING DOCUMENT LIVE...</span>
                    <span class="text-muted small mt-1">Converting quotation document to live interactive view</span>
                </div>
            </div>
        </div>
    </div>
</div>

<datalist id="dbFirmsList">
    @foreach($firms as $f)
        <option value="{{ $f->frm_name }}"></option>
    @endforeach
</datalist>

@if($canEdit)


<div class="modal fade" id="pcEditRemarksModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header py-2 px-3" style="background: var(--rd-surface2); border-bottom: 1px solid var(--rd-border);">
                <h6 class="modal-title rajdhani font-weight-bold text-dark" style="letter-spacing: 1px;">CASE REMARKS</h6>
                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST" id="pcRemarksForm">
                    @csrf
                    <input type="hidden" name="op" value="save_remarks">
                    <textarea name="pcs_remarks" id="pcRemarksInput" class="form-control" rows="6" style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);">{{ $purchase->pcs_remarks }}</textarea>
                    <div class="d-flex justify-content-end mt-3" style="gap:10px;">
                        <button type="button" class="btn btn-secondary btn-sm rajdhani font-weight-bold" data-dismiss="modal">CANCEL</button>
                        <button type="submit" class="btn btn-primary btn-sm rajdhani font-weight-bold">SAVE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pcAddFilesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header py-2 px-3" style="background: var(--rd-surface2); border-bottom: 1px solid var(--rd-border);">
                <h6 class="modal-title rajdhani font-weight-bold text-dark" style="letter-spacing: 1px;">UPLOAD FILES</h6>
                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST" enctype="multipart/form-data" id="pcFilesForm">
                    @csrf
                    <input type="hidden" name="op" value="add_files">
                    <input type="file" name="attachments[]" id="pcFilesInput" multiple class="form-control" style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);" required>
                    <div class="text-muted small mt-2">PDF/JPG/PNG/DOC/DOCX (max 10MB each)</div>
                    <div class="d-flex justify-content-end mt-3" style="gap:10px;">
                        <button type="button" class="btn btn-secondary btn-sm rajdhani font-weight-bold" data-dismiss="modal">CANCEL</button>
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
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
            <div class="modal-header border-bottom py-2 px-4 d-flex align-items-center justify-content-between" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="font-size: 24px; color: var(--rd-accent);"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <h5 class="modal-title rajdhani font-weight-bold text-dark mb-0" style="letter-spacing: 1.5px;">FINANCIAL INTELLIGENCE REPORT</h5>
                        <div class="small text-muted rajdhani">{{ $head->head_name }} | DATED {{ date('d M y') }} <span class="ml-2 text-primary">{{ $head->trans_type == 1 ? '(Million PKR without GST)' : '(PKR with GST)' }}</span></div>
                    </div>
                </div>
                <button type="button" class="close text-dark opacity-50 hover-opacity-100" data-dismiss="modal">&times;</button>
            </div>
            
            <div class="modal-body p-0" style="background: #ffffff;">
                {{-- Top Summary bar --}}
                <div class="row no-gutters border-bottom" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                    <div class="col-md-3 border-right p-3" style="border-color: var(--rd-border) !important;">
                        <div class="small text-muted rajdhani">ALLOCATION</div>
                        <div class="h5 mb-0 text-dark font-weight-bold rajdhani">{{ number_format($head->allocation) }}</div>
                    </div>
                    <div class="col-md-3 border-right p-3" style="border-color: var(--rd-border) !important;">
                        <div class="small text-muted rajdhani">MTSS SHARE</div>
                        <div class="h5 mb-0 text-dark font-weight-bold rajdhani">{{ number_format($head->mtss_share) }}</div>
                    </div>
                    <div class="col-md-3 border-right p-3" style="border-color: var(--rd-border) !important;">
                        <div class="small text-muted rajdhani">RDW SHARE</div>
                        <div class="h5 mb-0 text-primary font-weight-bold rajdhani">{{ number_format($head->rdw_share) }}</div>
                    </div>
                    <div class="col-md-3 p-3">
                        <div class="small text-muted rajdhani">CSRF SHARE</div>
                        <div class="h5 mb-0 text-dark font-weight-bold rajdhani">{{ number_format($head->csrf_share) }}</div>
                    </div>
                </div>

                <div class="row no-gutters">
                    {{-- Left Pane: Detailed Metrics Table --}}
                    <div class="col-xl-5 border-right p-4" style="background: #fbfcfe; border-color: var(--rd-border) !important;">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h6 class="rajdhani text-primary font-weight-bold mb-0" style="letter-spacing: 1px;"><i class="fas fa-table mr-2"></i>PROJECT SNAPSHOT</h6>
                            <div class="small text-muted rajdhani">FIGURES IN PKR</div>
                        </div>

                        <div class="fin-table-modern table-responsive rounded border overflow-auto" style="border-color: var(--rd-border) !important; background: #ffffff;">
                            <table class="table table-sm mb-0 rajdhani" style="font-size: 13px;">
                                <thead style="background: var(--rd-surface2);">
                                    <tr class="text-muted">
                                        <th class="pl-3 border-0">METRIC</th>
                                        <th class="text-right border-0" style="color: var(--rd-primary-700);">PROJECT</th>
                                        <th class="text-right border-0" style="color: #d97706;">CSRF</th>
                                        <th class="text-right pr-3 border-0" style="color: #16a34a;">ACTUAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="pl-3 text-muted">Received</td>
                                        <td class="text-right" style="color: var(--rd-primary-700);">{{ number_format($head->pcc_received ?? 0) }}</td>
                                        <td class="text-right" style="color: #d97706;">{{ number_format($head->cf_received ?? 0) }}</td>
                                        <td class="text-right pr-3 text-muted">--</td>
                                    </tr>
                                    <tr>
                                        <td class="pl-3 text-muted">Expenditure</td>
                                        <td class="text-right text-danger">{{ number_format($head->pcc_expenditure ?? 0) }}</td>
                                        <td class="text-right text-danger">{{ number_format($head->cf_expenditure ?? 0) }}</td>
                                        <td class="text-right pr-3" style="color: #16a34a;">{{ number_format($head->prj_expenditure ?? 0) }}</td>
                                    </tr>
                                    <tr style="background: rgba(37,99,235,0.03);">
                                        <td class="pl-3 text-primary font-weight-bold">Balance</td>
                                        <td class="text-right text-primary font-weight-bold">{{ number_format($head->pcc_balance ?? 0) }}</td>
                                        <td class="text-right text-primary font-weight-bold">{{ number_format($head->cf_balance ?? 0) }}</td>
                                        <td class="text-right pr-3 text-muted">--</td>
                                    </tr>
                                    <tr>
                                        <td class="pl-3 text-muted">Commitments</td>
                                        <td class="text-right text-warning">{{ number_format($head->pcc_commitments ?? 0) }}</td>
                                        <td class="text-right text-warning">{{ number_format($head->cf_commitments ?? 0) }}</td>
                                        <td class="text-right pr-3" style="color: #16a34a;">{{ number_format($head->prj_commitments ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="pl-3 text-muted">In Process</td>
                                        <td class="text-right text-muted">{{ number_format($head->pcc_in_process ?? 0) }}</td>
                                        <td class="text-right text-muted">{{ number_format($head->cf_in_process ?? 0) }}</td>
                                        <td class="text-right pr-3" style="color: #16a34a;">{{ number_format($head->prj_in_process ?? 0) }}</td>
                                    </tr>
                                    <tr style="background: rgba(22,163,74,0.05);">
                                        <td class="pl-3 font-weight-bold text-success">Available</td>
                                        <td class="text-right font-weight-bold text-success">{{ number_format($head->pcc_available ?? 0) }}</td>
                                        <td class="text-right font-weight-bold text-success">{{ number_format($head->cf_available ?? 0) }}</td>
                                        <td class="text-right pr-3 text-muted">--</td>
                                    </tr>
                                    <tr>
                                        <td class="pl-3 text-muted">Yet to be Rec</td>
                                        <td class="text-right text-muted">{{ number_format($head->pcc_yet_to_be_received ?? 0) }}</td>
                                        <td class="text-right text-muted">{{ number_format($head->cf_yet_to_be_received ?? 0) }}</td>
                                        <td class="text-right pr-3 text-muted">--</td>
                                    </tr>
                                    <tr style="background: rgba(220,38,38,0.05);">
                                        <td class="pl-3 text-danger font-weight-bold">Remaining</td>
                                        <td class="text-right text-danger font-weight-bold">{{ number_format($head->pcc_can_be_spent ?? 0) }}</td>
                                        <td class="text-right text-danger font-weight-bold">{{ number_format($head->cf_can_be_spent ?? 0) }}</td>
                                        <td class="text-right pr-3" style="color: #16a34a;">{{ number_format($head->prj_remaining ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Receivables section --}}
                        <div class="mt-4 pt-4 border-top" style="border-color: var(--rd-border) !important;">
                            <h6 class="rajdhani text-muted mb-3" style="font-size: 12px; letter-spacing: 2px;">RECEIVABLES</h6>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="text-muted small rajdhani">Comp. Milestones</span>
                                <span class="text-dark rajdhani font-weight-bold">{{ number_format($head->receivable_completed) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="text-muted small rajdhani">Current Milestone</span>
                                <span class="text-dark rajdhani font-weight-bold">{{ number_format($head->receivable_current) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mt-3 p-2 rounded" style="background: rgba(37,99,235,0.06); border: 1px solid rgba(37,99,235,0.2);">
                                <span class="text-primary small rajdhani font-weight-bold">Available after Rcv.</span>
                                <span class="text-primary rajdhani font-weight-bold">{{ number_format($head->available_after_receivables) }}</span>
                            </div>
                        </div>

                        {{-- Exp Sources --}}
                        <div class="mt-4 pt-4 border-top" style="border-color: var(--rd-border) !important;">
                            <h6 class="rajdhani text-muted mb-3" style="font-size: 12px; letter-spacing: 2px;">EXP. SOURCES</h6>
                            <div class="small d-flex justify-content-between mb-1">
                                <span class="text-muted rajdhani">From this account</span>
                                <span class="text-dark rajdhani">{{ number_format($head->exp_this_account) }}</span>
                            </div>
                            <div class="small d-flex justify-content-between mb-1">
                                <span class="text-muted rajdhani">From other accounts</span>
                                <span class="text-dark rajdhani">{{ number_format($head->exp_other_accounts) }}</span>
                            </div>
                            <div class="small d-flex justify-content-between">
                                <span class="text-muted rajdhani">Other's exp. this acc.</span>
                                <span class="text-dark rajdhani">{{ number_format($head->others_exp_this_account) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Pane: Visual Analytics & Subheads --}}
                    <div class="col-xl-7 p-4" style="background: #ffffff;">
                        <div class="row">
                            {{-- Mini Category Charts --}}
                            @foreach(array_slice($subheads, 0, 3) as $idx => $sh)
                            <div class="col-md-4 mb-4">
                                <div class="subhead-mini-card p-3 rounded border text-center h-100" style="border-color: var(--rd-border) !important; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                                    <div class="d-flex justify-content-center mb-2" style="height: 80px;">
                                        <canvas id="chartShMini{{ $idx }}"></canvas>
                                    </div>
                                    <h6 class="rajdhani font-weight-bold text-primary mb-1">{{ $sh['name'] }}</h6>
                                    <div class="text-dark rajdhani font-weight-bold" style="font-size: 14px;">{{ number_format($sh['allocation']) }}</div>
                                    <div class="mt-2 small text-muted rajdhani">UTILIZED: {{ number_format($sh['expenditure']) }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Subhead Detailed Breakdown --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="dg-sec-label mb-3"><i class="fas fa-th-list fa-xs"></i> Category Metrics Breakdown</div>
                                <div class="table-responsive rounded border" style="border-color: var(--rd-border) !important;">
                                    <table class="table table-sm table-hover mb-0 rajdhani" style="font-size: 12px; background: #ffffff;">
                                        <thead style="background: var(--rd-surface2);">
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
                                                <td class="pl-3 font-weight-bold text-primary">{{ $sh['name'] }}</td>
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
                                <div class="p-4 rounded border" style="border-color: var(--rd-border) !important; background: #ffffff; height: 300px;">
                                    <canvas id="finDetailedChart"></canvas>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-grid gap-2 h-100" style="display: grid; grid-template-rows: repeat(4, 1fr); gap: 10px;">
                                    <button class="btn btn-outline-primary btn-sm rajdhani font-weight-bold"><i class="fas fa-chart-pie mr-2"></i> SPENDING BREAKDOWN</button>
                                    <button class="btn btn-outline-secondary btn-sm rajdhani font-weight-bold"><i class="fas fa-history mr-2"></i> SPENDING TIMELINE</button>
                                    <button class="btn btn-outline-secondary btn-sm rajdhani font-weight-bold"><i class="fas fa-calculator mr-2"></i> SALARY FORECAST</button>
                                    <button class="btn btn-outline-secondary btn-sm rajdhani font-weight-bold"><i class="fas fa-file-contract mr-2"></i> CONTRACTS TIMELINE</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-top py-2 px-4 d-flex justify-content-between" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                <div class="small text-muted rajdhani"><i class="fas fa-shield-alt text-success mr-1"></i> RDWIS FINANCIAL AUDIT ENGINE ACTIVE</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary btn-xs rajdhani font-weight-bold px-4" onclick="initFinancialIntelligenceCharts()">
                        <i class="fas fa-sync-alt mr-1"></i> RE-CALCULATE
                    </button>
                    <button type="button" class="btn btn-secondary btn-xs rajdhani font-weight-bold px-4" data-dismiss="modal">CLOSE REPORT</button>
                </div>
            </div>
        </div>

        </div>
    </div>
</div>

<style>
    .fin-card-glass { background: var(--rd-neutral-50); border: 1px solid var(--rd-border); transition: all 0.3s; }
    .fin-card-glass:hover { background: var(--rd-neutral-50); border-color: rgba(255,255,255,0.15); transform: translateY(-2px); }
    .border-accent { border-color: rgba(243,156,18,0.3) !important; }
    .bg-navy-darker { background: var(--rd-neutral-200) !important; }
    .subhead-list-wrap::-webkit-scrollbar { width: 4px; }
    .subhead-list-wrap::-webkit-scrollbar-thumb { background: var(--rd-accent); border-radius: 10px; }
</style>

</div>

<style>
.chart-container-box { background: var(--rd-neutral-200) !important; transition: all 0.3s; }
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
    
    if (typeof Chart === 'undefined') {
        console.error("Chart.js not loaded!");
        return;
    }

    // 1. Main Project Liquidity Bar Chart
    const canvas = document.getElementById('finDetailedChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        if (window.finMainChart instanceof Chart) window.finMainChart.destroy();
        window.finMainChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Received', 'Expenditure', 'Commitments', 'In Process', 'Remaining'],
                datasets: [{
                    label: 'PKR Value',
                    data: [
                        parseFloat(head.received || 0), 
                        parseFloat(head.expenditure || 0), 
                        parseFloat(head.commitments || 0), 
                        parseFloat(head.in_process || 0), 
                        parseFloat(head.remaining || 0)
                    ],
                    backgroundColor: [
                        'rgba(77, 163, 255, 0.4)', 
                        'rgba(255, 50, 50, 0.4)', 
                        'rgba(243, 156, 18, 0.4)', 
                        'rgba(23, 162, 184, 0.4)', 
                        'rgba(77, 255, 136, 0.4)'
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

    // 2. Mini Subhead Utilization Charts (Doughnut)
    (subheads || []).slice(0, 3).forEach((sh, idx) => {
        const shCanvas = document.getElementById(`chartShMini${idx}`);
        if (shCanvas) {
            const shCtx = shCanvas.getContext('2d');
            if (window[`finShChart${idx}`] instanceof Chart) window[`finShChart${idx}`].destroy();
            
            const used = Math.abs(parseFloat(sh.expenditure || 0)) + Math.abs(parseFloat(sh.commitments || 0)) + Math.abs(parseFloat(sh.in_process || 0));
            const remaining = Math.max(0, parseFloat(sh.remaining || 0));

            window[`finShChart${idx}`] = new Chart(shCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Used', 'Remaining'],
                    datasets: [{
                        data: [used, remaining],
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
    $('#financialIntelligenceModal').on('shown.bs.modal', function () {
        setTimeout(initFinancialIntelligenceCharts, 300);
    });
});
// Main page progress bar animation
    setTimeout(() => {
        document.getElementById('dgProgU')?.classList.add('anim');
        document.getElementById('dgProgR')?.classList.add('anim');
    }, 500);

    // Modal trigger logic
    $(document).on('shown.bs.modal', '#financialIntelligenceModal', function () {
        setTimeout(initFinancialIntelligenceCharts, 250);
    });
</script>
<script src="{{ asset('plugins/mammoth/mammoth.browser.min.js') }}"></script>
<script src="{{ asset('plugins/sheetjs/xlsx.full.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrap = document.getElementById('pcEditWrap');
    if (!wrap) return;
    const canEdit = wrap.getAttribute('data-can-edit') === '1';
    const saveUrl = @json(route('purchase.initiation.save', $purchase->pcs_id));
    const storageBase = @json(rtrim(url('storage'), '/') . '/');
    const quoteViewBase = @json(url('/purchase/quote-attachment'));
    const allDbFirms = @json($firms->pluck('frm_name')->filter()->unique()->values());

    @php
        $pcItems = $purchase->items->sortBy('pci_serial')->values();
        $pcQuotes = $purchase->quotes->values();
        $quoteIds = $pcQuotes->pluck('qte_id')->toArray();
        $quoteItemMap = [];
        $quoteAttachments = \Illuminate\Support\Facades\DB::table('pur.purattachments')
            ->where('pat_objtype', 'qte')
            ->whereIn('pat_objid', $quoteIds)
            ->get()
            ->keyBy('pat_objid');

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
            'quotes' => $pcQuotes->map(function($q) use ($quoteAttachments, $pcQuotes) {
                $att = $quoteAttachments->get($q->qte_id);
                $filePath = $att ? (string) $att->pat_path : null;
                $fileName = $filePath ? basename(str_replace('\\', '/', $filePath)) : null;
                $isBase = ($q->qte_num == 1 || $q->qte_id == $pcQuotes->min('qte_id'));
                return [
                    'qte_id' => (int) $q->qte_id,
                    'qte_num' => (int) ($q->qte_num ?? 0),
                    'firm_name' => (string) ($q->firm?->frm_name ?? $q->qte_firmname),
                    'qte_price' => (float) ($q->qte_price ?? 0),
                    'qte_subtotal' => (float) ($q->qte_intprice ?? 0),
                    'qte_tax' => (float) ($q->qte_inttax ?? 0),
                    'tax_type' => (string) ($q->qte_taxtype ?? 'GST'),
                    'is_base' => (bool) $isBase,
                    'attachment_path' => $filePath,
                    'attachment_name' => $fileName ?? 'Quote Document',
                ];
            })->values(),
            'attachments' => $purchase->attachments->map(fn($a) => [
                'pat_id' => (int) $a->pat_id,
                'pat_path' => (string) $a->pat_path,
                'pat_filename' => basename(str_replace('\\', '/', (string)($a->pat_path ?? ''))),
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
        return [...quotes].sort((a, b) => {
            const bPriceA = Number(a.qte_subtotal || a.qte_intprice || a.qte_price || 0);
            const taxA = Number(a.qte_tax || a.qte_inttax || a.qte_midtax || 0);
            const totA = bPriceA + taxA;

            const bPriceB = Number(b.qte_subtotal || b.qte_intprice || b.qte_price || 0);
            const taxB = Number(b.qte_tax || b.qte_inttax || b.qte_midtax || 0);
            const totB = bPriceB + taxB;

            return totA - totB;
        });
    }

    function renderItems() {
        const body = document.getElementById('pcItemsBody');
        if (!body) return;
        // Sort by ID descending for LIFO (Last In First Out)
        const items = [...(state.items || [])].sort((a, b) => (b.pci_id ?? 0) - (a.pci_id ?? 0));
        body.innerHTML = items.map((it, idx) => `
            <tr data-pci-id="${it.pci_id}">
                <td class="pl-3 text-muted">${items.length - idx}</td>
                <td><span class="pc-desc-display font-weight-600" style="color: #0f172a;">${(it.pci_desc ?? '').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}</span></td>
                <td class="text-center font-weight-bold"><span class="pc-qty-display" style="color: #0f172a;">${fmt(it.pci_qty)}</span> <span class="small text-muted pc-unit-display">${(it.pci_qtyunit ?? '')}</span></td>
                <td class="text-right pr-3 font-weight-bold text-dark" style="color: #0f172a !important;">${fmt(it.pci_price)}</td>
                <td class="text-right pr-3 edit-only">
                    <div class="d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-outline-warning btn-xs pc-item-edit-btn" data-pci-id="${it.pci_id}" data-desc="${(it.pci_desc ?? '').replaceAll('"', '&quot;')}" data-qty="${it.pci_qty}" data-unit="${it.pci_qtyunit ?? 'num'}" title="Edit Item" style="padding: 2px 6px;"><i class="fas fa-pencil-alt"></i></button>
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
            body.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted small">No quotations added yet.</td></tr>`;
            return;
        }

        // Live update Case Financials in Header
        const baseEl = document.getElementById('pcSummaryBasePrice');
        const sstEl = document.getElementById('pcSummarySst');
        const gstEl = document.getElementById('pcSummaryGst');
        const totEl = document.getElementById('pcSummaryTotal');
        
        if (sorted.length > 0 && baseEl && totEl) {
            const winner = sorted[0];
            const bPrice = Number(winner.qte_subtotal || winner.qte_intprice || winner.qte_price || 0);
            const taxAmt = Number(winner.qte_tax || winner.qte_inttax || winner.qte_midtax || 0);
            const tType = String(winner.tax_type || winner.qte_taxtype || winner.qte_quotetype || 'GST').toUpperCase();
            const isSst = tType.includes('SST');
            const sstAmt = isSst ? taxAmt : 0;
            const gstAmt = !isSst ? taxAmt : 0;
            const totalAmt = bPrice + sstAmt + gstAmt;

            baseEl.innerText = fmt(bPrice);
            if (sstEl) sstEl.innerText = fmt(sstAmt);
            if (gstEl) gstEl.innerText = fmt(gstAmt);
            totEl.innerText = fmt(totalAmt);
        }

        body.innerHTML = sorted.map((q, idx) => {
            const isWinner = idx === 0;
            const isBaseQuote = q.is_base === true;
            const trStyle = isWinner ? 'background: #f0fdf4 !important; border-left: 3px solid #16a34a;' : '';
            const td1 = isWinner ? 'text-success font-weight-bold' : 'text-muted';
            const td2 = isWinner ? 'text-success font-weight-bold' : 'text-dark font-weight-bold';
            const td3 = isWinner ? 'text-success font-weight-bold' : 'text-primary font-weight-bold';

            const bPrice = Number(q.qte_subtotal || q.qte_intprice || q.qte_price || 0);
            const taxAmt = Number(q.qte_tax || q.qte_inttax || q.qte_midtax || 0);
            const quoteTotal = bPrice + taxAmt;

            return `
                <tr style="${trStyle}">
                    <td class="pl-3 ${td1}">${idx + 1}</td>
                    <td class="${td2}" style="color: ${isWinner ? '#166534' : '#0f172a'} !important;">
                        ${(q.firm_name ?? '').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}
                        ${isBaseQuote ? '<span class="text-muted small font-weight-bold ml-1" style="font-size:10px; color: var(--rd-text3) !important;">(Base Quote)</span>' : ''}
                    </td>
                    <td class="text-right pr-3 font-weight-bold ${td3}">
                        <div style="color: ${isWinner ? '#166534' : '#0f172a'} !important;">Rs. ${fmt(quoteTotal)}</div>
                        ${bPrice > 0 && taxAmt > 0 ? `<div style="font-size:10px; font-weight:normal; color: var(--rd-text3);">Base: ${fmt(bPrice)} | Tax: ${fmt(taxAmt)}</div>` : ''}
                    </td>
                    <td class="text-center">
                        ${q.attachment_path ? `
                            <button type="button" class="btn btn-xs btn-outline-primary pc-live-view-quote-btn" data-url="${quoteViewBase}/${q.qte_id}/view" data-qte-id="${q.qte_id}" data-title="${(q.firm_name||'').replaceAll('"','&quot;')}" style="width: 28px; height: 26px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; font-size: 11px;" title="View Quotation Document">
                                <i class="fas fa-eye"></i>
                            </button>
                        ` : '<span class="text-muted small opacity-50">—</span>'}
                    </td>
                    <td class="text-right pr-3 edit-only">
                        @if($canAddQuotes)
                            <div class="d-flex justify-content-end align-items-center" style="gap: 4px;">
                                <button type="button" class="btn btn-xs ${q.attachment_path ? 'btn-outline-secondary' : 'btn-outline-primary'} pc-direct-upload-btn" data-qte-id="${q.qte_id}" data-firm-name="${(q.firm_name||'').replaceAll('"','&quot;')}" style="font-size: 10px; padding: 2px 8px; height: 26px; border-radius: 6px;" title="${q.attachment_path ? 'Replace Attached Quote File' : 'Upload Quote Document'}">
                                    <i class="fas fa-paperclip mr-1"></i> ${q.attachment_path ? 'Replace' : 'Upload'}
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-xs pc-quote-del-btn" data-qte-id="${q.qte_id}" title="Delete" style="width: 26px; height: 26px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;"><i class="fas fa-trash-alt"></i></button>
                            </div>
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
            <div class="d-flex align-items-center p-2 rounded" style="background: #f8fafc; border: 1.5px solid #cbd5e1; min-width: 200px; position: relative;">
                <i class="far fa-file-pdf text-danger mr-2" style="font-size: 16px;"></i>
                <div class="flex-grow-1 overflow-hidden mr-2">
                    <div class="small font-weight-bold text-dark text-nowrap" style="color: #0f172a !important; overflow: hidden; text-overflow: ellipsis; font-size: 11px;">${(f.pat_filename || '').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}</div>
                </div>
                <div class="d-flex gap-1">
                    <a href="${quoteViewBase}/${f.pat_id}/view" target="_blank" class="btn btn-xs btn-outline-primary" style="padding: 2px 6px; border-radius: 4px;" title="View">
                        <i class="fas fa-eye" style="font-size: 10px;"></i>
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
                prices: qi[String(q.qte_id)] || {},
                file: null,
                existing_file_path: q.attachment_path,
                existing_file_name: q.attachment_name
            }));
        }
        if (modalVendors.length === 0) {
            modalVendors.push({ id: null, firm_name: '', prices: {}, file: null, existing_file_path: null, existing_file_name: null });
        }

        // ---- THEAD ----
        headRow.innerHTML = `<th class="pc-item-sticky">ITEM DESCRIPTION</th>` +
            modalVendors.map((v, idx) => `
                <th style="width: 260px; min-width: 260px; text-align:center; padding:8px;">
                    <div class="d-flex align-items-center justify-content-center" style="gap:6px;">
                        <input type="text"
                               class="pc-vendor-name-input"
                               value="${(v.firm_name ?? '').replaceAll('"','&quot;')}"
                               placeholder="Type / search firm..."
                               data-idx="${idx}"
                               autocomplete="off">
                        <button type="button" class="pc-remove-vendor-btn btn btn-link p-0" data-idx="${idx}" style="color:#dc3545; font-size:12px;" title="Delete Vendor">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                    <div class="mt-1 d-flex align-items-center justify-content-center">
                        <label class="badge ${v.file || v.existing_file_path ? 'badge-success' : 'badge-dark'} p-1 mb-0" style="cursor:pointer; font-size:9px; font-weight:normal; border: 1px solid var(--rd-border);" title="${v.file ? v.file.name : (v.existing_file_name || 'Attach Quote Document')}">
                            <i class="fas fa-paperclip mr-1"></i>
                            <span>${v.file ? (v.file.name.length > 12 ? v.file.name.substring(0,10)+'..' : v.file.name) : (v.existing_file_name ? (v.existing_file_name.length > 12 ? v.existing_file_name.substring(0,10)+'..' : v.existing_file_name) : 'Attach Document')}</span>
                            <input type="file" class="pc-modal-quote-file-input" data-idx="${idx}" style="display:none;" accept=".pdf,.jpg,.jpeg,.png,.webp,.gif,.bmp,.svg,.doc,.docx,.xls,.xlsx,.csv,.txt">
                        </label>
                    </div>
                </th>
            `).join('');

        // ---- TBODY ----
        body.innerHTML = items.map((it) => `
            <tr>
                <td class="pc-item-sticky">
                    <div style="font-size:12px; font-weight:700; color:var(--rd-text1); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${(it.pci_desc ?? '').replaceAll('<','&lt;').replaceAll('>','&gt;')}</div>
                    <div style="font-size:11px; color:#888;">Quantity: ${fmt(it.pci_qty)} ${(it.pci_qtyunit ?? '')}</div>
                </td>
                ${modalVendors.map((v, idx) => `
                    <td style="width: 260px; min-width: 260px; text-align:center; padding:6px;">
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
        const taxType = document.getElementById('pcGlobalTaxType')?.value || 'GST';
        const taxPercent = parseFloat(document.getElementById('pcGlobalTaxPercent')?.value || 0);

        const columnSubtotals = modalVendors.map((v) => {
            let sub = 0;
            items.forEach(it => {
                const p = parseFloat(v.prices[it.pci_id] || 0);
                const q = parseFloat(it.pci_qty || 1);
                sub += (p * q);
            });
            return sub;
        });

        const columnTotals = columnSubtotals.map(sub => sub + (sub * (taxPercent / 100)));
        const minTotal = Math.min(...columnTotals.filter(t => t > 0));

        foot.innerHTML = `
            <tr>
                <td class="pc-item-sticky" style="text-align:right;">
                    <div style="font-size:10px; color:rgba(255,255,255,0.5);">SUB TOTAL</div>
                    <div style="font-size:10px; color:rgba(255,255,255,0.5);">TAX (${taxType} ${taxPercent}%)</div>
                    <div style="font-size:13px; color:var(--rd-accent); font-weight:800;">TOTAL (PKR)</div>
                </td>
                ${modalVendors.map((v, idx) => {
                    const sub = columnSubtotals[idx];
                    const tax = sub * (taxPercent / 100);
                    const total = sub + tax;
                    const isWinner = total > 0 && total === minTotal;
                    return `
                        <td style="width: 260px; min-width: 260px; text-align:center; padding:8px; ${isWinner ? 'background:rgba(40,167,69,0.15) !important;' : ''}">
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

    function renderPriceBreakdown() {
        const quotes = state.quotes || [];
        const sorted = sortQuotesByPrice(quotes);
        let basePrice = 0;
        let sstAmount = 0;
        let gstAmount = 0;
        let totalPrice = 0;

        if (sorted.length > 0) {
            const winner = sorted[0];
            totalPrice = parseFloat(winner.qte_price || 0);
            const tax = parseFloat(winner.qte_tax || winner.qte_midtax || winner.qte_inttax || 0);
            basePrice = parseFloat(winner.qte_subtotal || winner.qte_intprice || (totalPrice - tax));
            if (basePrice <= 0 && totalPrice > 0 && tax === 0) {
                basePrice = totalPrice;
            }
            const taxType = (winner.tax_type || winner.qte_taxtype || 'GST').toUpperCase();
            
            if (taxType.includes('SST')) {
                sstAmount = tax;
                gstAmount = 0;
            } else {
                gstAmount = tax;
                sstAmount = 0;
            }
        } else {
            const items = state.items || [];
            basePrice = items.reduce((acc, it) => acc + (parseFloat(it.pci_qty || 1) * parseFloat(it.pci_price || 0)), 0);
            totalPrice = (parseFloat(state.pcs_price || 0) > 0) ? parseFloat(state.pcs_price) : basePrice;
        }

        const fmt2 = (n) => Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const elBase = document.getElementById('pcSummaryBasePrice');
        const elSst = document.getElementById('pcSummarySst');
        const elGst = document.getElementById('pcSummaryGst');
        const elTot = document.getElementById('pcSummaryTotal');

        if (elBase) elBase.textContent = fmt2(basePrice);
        if (elSst) elSst.textContent = fmt2(sstAmount);
        if (elGst) elGst.textContent = fmt2(gstAmount);
        if (elTot) elTot.textContent = fmt2(totalPrice);
    }

    function renderAll() {
        renderTitle();
        renderItems();
        renderQuotes();
        renderRemarks();
        renderFiles();
        renderPriceBreakdown();
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
                    <tr style="background: var(--rd-neutral-50);">
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
                                ${q.attachment_path ? `
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-xs btn-outline-info pc-live-view-quote-btn" data-url="${storageBase}${q.attachment_path}" data-title="${(q.firm_name||'').replaceAll('"','&quot;')}" style="font-size: 8px; padding: 1px 6px;">
                                            <i class="fas fa-eye mr-1"></i> View Quote
                                        </button>
                                    </div>
                                ` : ''}
                            </th>
                        `).join('')}
                    </tr>
                </thead>
                <tbody>
        `;

        const body = items.map((it) => {
            const row = `
                <tr>
                    <td class="cs-sticky-1 text-center text-muted small" style="background: var(--rd-neutral-200) !important;">${it.pci_serial ?? ''}</td>
                    <td class="cs-sticky-2 text-dark" style="font-weight: 500; background: var(--rd-neutral-200) !important;">${String(it.pci_desc || '').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}</td>
                    <td class="cs-sticky-3 text-center text-dark" style="font-weight: 600; background: var(--rd-neutral-200) !important;">${fmt(it.pci_qty)}</td>
                    ${quotes.map((q, idx) => {
                        const price = Number((qi[String(q.qte_id)] || {})[String(it.pci_id)] || 0);
                        const isBest = price > 0 && price === (winnerByItem[it.pci_id] || -1);
                        return `
                            <td class="text-center ${idx === 0 ? 'col-l1' : ''}" style="border-right: 1px solid rgba(255,255,255,0.05);">
                                ${price > 0
                                    ? `<div class="price-val ${isBest ? 'text-success' : 'text-dark'}" style="font-size: 14px; font-weight: 700;">${fmt(price)}</div>${isBest ? `<span class="badge badge-success" style="font-size: 8px; padding: 1px 4px;">Min</span>` : ''}`
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
                    <tr style="background: var(--rd-neutral-50);">
                        <td colspan="3" class="cs-sticky-1-3 text-right pr-4 text-accent-clean" style="font-size: 14px; background: var(--rd-neutral-200) !important; font-weight: 800;">
                            GRAND TOTAL (PKR)
                        </td>
                        ${totals.map((t, idx) => `
                            <td class="text-center py-3 ${idx === 0 ? 'col-l1' : ''}" style="border-right: 1px solid rgba(255,255,255,0.05); background: var(--rd-neutral-200) !important;">
                                <div class="rajdhani ${idx === 0 ? 'text-success' : 'text-dark'}" style="font-size: 20px; font-weight: 800; text-shadow: 0 0 10px rgba(0,0,0,0.5);">
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
        const editBtn = e.target.closest('.pc-item-edit-btn');
        if (editBtn) {
            if (!ensureEditing()) return;
            const tr = editBtn.closest('tr');
            const pciId = editBtn.getAttribute('data-pci-id');
            const desc = editBtn.getAttribute('data-desc') || '';
            const qty = editBtn.getAttribute('data-qty') || '1';
            const unit = editBtn.getAttribute('data-unit') || 'num';

            tr.innerHTML = `
                <td class="pl-3 text-warning font-weight-bold"><i class="fas fa-pencil-alt"></i></td>
                <td>
                    <input type="text" class="form-control form-control-sm pc-inline-desc-input" value="${desc.replaceAll('"', '&quot;')}" style="background: var(--rd-neutral-200); color:#fff; border:1px solid var(--rd-accent); font-size:12px;" required>
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center" style="gap:4px;">
                        <input type="number" step="0.01" class="form-control form-control-sm pc-inline-qty-input text-center" value="${qty}" style="width:65px; background: var(--rd-neutral-200); color:#fff; border:1px solid var(--rd-accent); font-size:12px;" required>
                        <input type="text" class="form-control form-control-sm pc-inline-unit-input text-center" value="${unit.replaceAll('"', '&quot;')}" style="width:45px; background: var(--rd-neutral-200); color:#fff; border:1px solid rgba(255,255,255,0.2); font-size:11px;">
                    </div>
                </td>
                <td class="text-right pr-3 font-weight-bold text-muted">—</td>
                <td class="text-right pr-3">
                    <div class="d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-success btn-xs pc-inline-save-btn" data-pci-id="${pciId}" title="Save Changes" style="padding:2px 8px;"><i class="fas fa-check"></i></button>
                        <button type="button" class="btn btn-secondary btn-xs pc-inline-cancel-btn" title="Cancel" style="padding:2px 8px;"><i class="fas fa-times"></i></button>
                    </div>
                </td>
            `;
            tr.querySelector('.pc-inline-desc-input')?.focus();
            return;
        }

        const saveInlineBtn = e.target.closest('.pc-inline-save-btn');
        if (saveInlineBtn) {
            if (!ensureEditing()) return;
            const tr = saveInlineBtn.closest('tr');
            const pciId = saveInlineBtn.getAttribute('data-pci-id');
            const desc = tr.querySelector('.pc-inline-desc-input')?.value || '';
            const qty = tr.querySelector('.pc-inline-qty-input')?.value || '1';
            const unit = tr.querySelector('.pc-inline-unit-input')?.value || 'num';

            if (!desc.trim()) { toast('Description cannot be empty'); return; }

            const fd = new FormData();
            fd.append('op', 'edit_item');
            fd.append('pci_id', pciId);
            fd.append('item_desc', desc.trim());
            fd.append('item_qty', qty);
            fd.append('item_qtyunit', unit);
            fd.append('_token', @json(csrf_token()));

            try {
                const json = await postForm(fd);
                state = json.data;
                renderAll();
                toast(json.message || 'Item updated successfully');
            } catch (err) {
                toast(err.message || 'Error updating item');
            }
            return;
        }

        const cancelInlineBtn = e.target.closest('.pc-inline-cancel-btn');
        if (cancelInlineBtn) {
            renderItems();
            return;
        }

        const btn = e.target.closest('.pc-item-del-btn');
        if (!btn) return;
        if (!ensureEditing()) return;
        const pciId = btn.getAttribute('data-pci-id');
        if (!pciId) return;
        if (!confirm('Are you sure you want to delete this item?')) return;
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

    function renderDiagnosticError(container, url, qteId, title, errorMsg) {
        if (!container) return;
        const diagUrl = `${quoteViewBase}/${qteId}/diagnose`;
        container.innerHTML = `
            <div class="p-4 rounded w-100" style="background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(239, 68, 68, 0.35); box-shadow: 0 4px 15px rgba(0,0,0,0.5);">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.08) !important;">
                    <div class="d-flex align-items-center text-warning font-weight-bold" style="font-size: 15px;">
                        <i class="fas fa-exclamation-triangle mr-2 text-danger"></i> Document Preview Notice: ${title}
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-warning pc-trigger-diag-btn" data-diag-url="${diagUrl}" style="font-size: 11px; padding: 4px 12px; border-radius: 6px;">
                        <i class="fas fa-stethoscope mr-1"></i> Run Offline Diagnostic
                    </button>
                </div>
                <div class="alert alert-danger py-2 px-3 small font-weight-bold mb-3">
                    <i class="fas fa-info-circle mr-1"></i> ${errorMsg || 'Unable to directly load document stream.'}
                </div>
                <div class="d-flex gap-2 mb-3">
                    <a href="${url}" target="_blank" class="btn btn-sm btn-primary rajdhani font-weight-bold">
                        <i class="fas fa-external-link-alt mr-1"></i> Open in New Tab
                    </a>
                    <a href="${url}?download=1" class="btn btn-sm btn-outline-info rajdhani font-weight-bold">
                        <i class="fas fa-download mr-1"></i> Direct Download
                    </a>
                    <a href="${diagUrl}" target="_blank" class="btn btn-sm btn-outline-secondary rajdhani font-weight-bold">
                        <i class="fas fa-file-code mr-1"></i> Raw JSON Report
                    </a>
                </div>
                <div class="pc-diag-render-area" style="display:none; background: var(--rd-surface); border: 1px solid var(--rd-border); border-radius: 6px; padding: 14px; margin-top: 12px;"></div>
            </div>
        `;
    }

    $(document).on('click', '.pc-trigger-diag-btn', async function() {
        const btn = $(this);
        const url = btn.data('diag-url');
        const box = btn.closest('div.p-4').find('.pc-diag-render-area');
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Running Diagnostic...');
        btn.prop('disabled', true);

        try {
            const res = await fetch(url);
            const data = await res.json();
            box.show().html(`
                <div class="rajdhani font-weight-bold text-info mb-2" style="font-size: 14px; letter-spacing: 0.8px;">
                    <i class="fas fa-search mr-1"></i> DIAGNOSTIC BREAKDOWN (OFFLINE LOG)
                </div>
                <div class="row small mb-2">
                    <div class="col-md-6 text-muted">Client IP: <span class="text-dark font-weight-bold">${data.client_ip || 'Unknown'}</span></div>
                    <div class="col-md-6 text-muted">User: <span class="text-dark font-weight-bold">${data.user?.name || 'N/A'} (${data.user?.area || 'N/A'})</span></div>
                </div>
                <div class="row small mb-3">
                    <div class="col-md-6 text-muted">DB Record Found: <span class="${data.attachment_record ? 'text-success font-weight-bold' : 'text-danger font-weight-bold'}">${data.attachment_record ? 'YES (ID: ' + data.attachment_record.pat_id + ')' : 'NO (Missing in purattachments)'}</span></div>
                    <div class="col-md-6 text-muted">Physical Disk File: <span class="${data.file_found ? 'text-success font-weight-bold' : 'text-danger font-weight-bold'}">${data.file_found ? 'EXISTS' : 'MISSING ON DISK'}</span></div>
                </div>
                <div class="small font-weight-bold text-warning mb-1">Tested File Paths on Server:</div>
                <ul class="list-unstyled mb-0" style="font-family: monospace; font-size: 11px;">
                    ${(data.tested_paths || []).map(p => `
                        <li class="p-1 mb-1 rounded d-flex justify-content-between align-items-center" style="background: var(--rd-neutral-50); border: 1px solid var(--rd-border);">
                            <span style="word-break:break-all;" class="${p.exists ? 'text-success' : 'text-muted'}">${p.path}</span>
                            <span class="badge ${p.exists ? 'badge-success' : 'badge-secondary'} ml-2">${p.exists ? 'EXISTS (' + p.size + ' B)' : 'NOT FOUND'}</span>
                        </li>
                    `).join('')}
                </ul>
            `);
            btn.html('<i class="fas fa-check mr-1"></i> Diagnostic Completed');
        } catch (e) {
            box.show().html(`<div class="alert alert-danger mb-0 small">Diagnostic failed: ${e.message}</div>`);
            btn.html('<i class="fas fa-redo mr-1"></i> Retry Diagnostic');
            btn.prop('disabled', false);
        }
    });

    $(document).on('click', '#pcQuoteViewerDiagBtn', function(e) {
        e.preventDefault();
        const qteId = $(this).data('qte-id');
        const url = $(this).data('url') || `${quoteViewBase}/${qteId}/view`;
        const title = $(this).data('title') || 'Quotation Document';
        const docWrap = document.getElementById('pcQuoteViewerDocWrap');
        const docContent = document.getElementById('pcQuoteViewerDocContent');
        const iframe = document.getElementById('pcQuoteViewerIframe');
        const imgWrap = document.getElementById('pcQuoteViewerImgWrap');
        const sheetWrap = document.getElementById('pcQuoteViewerSheetWrap');
        if (iframe) iframe.style.display = 'none';
        if (imgWrap) imgWrap.style.display = 'none';
        if (sheetWrap) sheetWrap.style.display = 'none';
        if (docWrap && docContent) {
            docWrap.style.display = 'flex';
            renderDiagnosticError(docContent, url, qteId, title, 'Manual Diagnostic Inspection Requested.');
            docContent.querySelector('.pc-trigger-diag-btn')?.click();
        }
    });

    // Live preview quote document in modal
    $(document).on('click', '.pc-live-view-quote-btn', async function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const qteId = $(this).data('qte-id') || $(this).data('pat-id');
        const title = $(this).data('title') || 'Quotation Document';
        if (!url) return;

        const ext = url.split('.').pop().toLowerCase().split('?')[0];
        $('#pcQuoteViewerTitle').text(`${title.toUpperCase()} - QUOTATION [${ext.toUpperCase()}]`);
        $('#pcQuoteViewerOpenNewTab').attr('href', url);
        $('#pcQuoteViewerDiagBtn').data('qte-id', qteId).data('url', url).data('title', title);

        const iframe = document.getElementById('pcQuoteViewerIframe');
        const imgWrap = document.getElementById('pcQuoteViewerImgWrap');
        const img = document.getElementById('pcQuoteViewerImg');
        const docWrap = document.getElementById('pcQuoteViewerDocWrap');
        const docContent = document.getElementById('pcQuoteViewerDocContent');
        const sheetWrap = document.getElementById('pcQuoteViewerSheetWrap');
        const sheetTabs = document.getElementById('pcQuoteViewerSheetTabs');
        const sheetContent = document.getElementById('pcQuoteViewerSheetContent');
        const loading = document.getElementById('pcQuoteViewerLoading');

        // Reset all viewer panels
        if (iframe) { iframe.style.display = 'none'; iframe.src = ''; }
        if (imgWrap) { imgWrap.style.display = 'none'; }
        if (img) { img.src = ''; }
        if (docWrap) { docWrap.style.display = 'none'; }
        if (docContent) { docContent.innerHTML = ''; }
        if (sheetWrap) { sheetWrap.style.display = 'none'; }
        if (sheetTabs) { sheetTabs.innerHTML = ''; }
        if (sheetContent) { sheetContent.innerHTML = ''; }
        if (loading) { loading.style.display = 'none'; }

        $('#pcQuoteViewerModal').modal('show');

        // 1. Image Formats
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'jfif', 'ico', 'avif'].includes(ext)) {
            if (imgWrap && img) {
                img.onerror = function() {
                    renderDiagnosticError(docContent, url, qteId, title, 'Image failed to load (403 Forbidden or file missing).');
                    if (imgWrap) imgWrap.style.display = 'none';
                    if (docWrap) docWrap.style.display = 'flex';
                };
                img.src = url;
                imgWrap.style.display = 'flex';
            }
        } 
        // 2. Word Documents (DOCX / DOC)
        else if (['docx', 'doc'].includes(ext)) {
            if (loading) loading.style.display = 'flex';
            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                const arrayBuffer = await res.arrayBuffer();
                if (typeof mammoth !== 'undefined') {
                    const result = await mammoth.convertToHtml({ arrayBuffer: arrayBuffer });
                    if (docContent) {
                        docContent.innerHTML = result.value || '<p class="text-muted italic">Document is empty.</p>';
                    }
                    if (docWrap) docWrap.style.display = 'flex';
                } else {
                    renderDiagnosticError(docContent, url, qteId, title, 'Mammoth preview library unavailable.');
                    if (docWrap) docWrap.style.display = 'flex';
                }
            } catch (err) {
                console.error(err);
                renderDiagnosticError(docContent, url, qteId, title, err.message);
                if (docWrap) docWrap.style.display = 'flex';
            } finally {
                if (loading) loading.style.display = 'none';
            }
        } 
        // 3. Excel Spreadsheets (XLSX / XLS / CSV)
        else if (['xlsx', 'xls', 'csv'].includes(ext)) {
            if (loading) loading.style.display = 'flex';
            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                const arrayBuffer = await res.arrayBuffer();
                if (typeof XLSX !== 'undefined') {
                    const workbook = XLSX.read(arrayBuffer, { type: 'array' });
                    const sheetNames = workbook.SheetNames || [];
                    if (sheetNames.length === 0) {
                        if (sheetContent) sheetContent.innerHTML = '<div class="text-muted p-4 text-center">Workbook contains no sheets.</div>';
                    } else {
                        // Render tabs
                        if (sheetTabs) {
                            sheetTabs.innerHTML = sheetNames.map((name, i) => `
                                <button type="button" class="excel-tab-btn ${i === 0 ? 'active' : ''}" data-sheet-index="${i}">
                                    <i class="fas fa-table mr-1"></i> ${name}
                                </button>
                            `).join('');
                        }

                        const renderSheet = function(index) {
                            const sName = sheetNames[index];
                            const worksheet = workbook.Sheets[sName];
                            const htmlTable = XLSX.utils.sheet_to_html(worksheet, { id: 'excelTableGrid', editable: false });
                            if (sheetContent) {
                                sheetContent.innerHTML = htmlTable;
                                const table = sheetContent.querySelector('table');
                                if (table) {
                                    table.className = 'excel-table table table-bordered table-sm';
                                }
                            }
                        };

                        renderSheet(0);

                        $(sheetTabs).off('click', '.excel-tab-btn').on('click', '.excel-tab-btn', function() {
                            $(sheetTabs).find('.excel-tab-btn').removeClass('active');
                            $(this).addClass('active');
                            const sIdx = parseInt($(this).data('sheet-index'));
                            renderSheet(sIdx);
                        });
                    }
                    if (sheetWrap) sheetWrap.style.display = 'flex';
                } else {
                    renderDiagnosticError(sheetContent, url, qteId, title, 'SheetJS preview library unavailable.');
                    if (sheetWrap) sheetWrap.style.display = 'flex';
                }
            } catch (err) {
                console.error(err);
                renderDiagnosticError(sheetContent, url, qteId, title, err.message);
                if (sheetWrap) sheetWrap.style.display = 'flex';
            } finally {
                if (loading) loading.style.display = 'none';
            }
        } 
        // 4. Plain Text / Code (TXT, LOG, JSON, XML)
        else if (['txt', 'log', 'json', 'xml'].includes(ext)) {
            if (loading) loading.style.display = 'flex';
            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                const text = await res.text();
                if (docContent) {
                    docContent.innerHTML = `<pre style="font-family: monospace; font-size: 13px; color: #fff; background: var(--rd-surface); padding: 20px; border-radius: 6px; white-space: pre-wrap; word-break: break-all;">${text.replaceAll('<','&lt;').replaceAll('>','&gt;')}</pre>`;
                }
                if (docWrap) docWrap.style.display = 'flex';
            } catch (err) {
                renderDiagnosticError(docContent, url, qteId, title, err.message);
                if (docWrap) docWrap.style.display = 'flex';
            } finally {
                if (loading) loading.style.display = 'none';
            }
        } 
        // 5. PDF & Other browser native files
        else {
            if (iframe) {
                iframe.style.display = 'block';
                iframe.src = url;
            }
        }
    });

    // Direct quote document upload from quotations table
    let activeDirectQteId = null;
    $(document).on('click', '.pc-direct-upload-btn', function() {
        if (!ensureEditing()) return;
        activeDirectQteId = $(this).data('qte-id');
        const inp = document.getElementById('pcDirectQuoteUploadInput');
        if (inp) {
            inp.value = '';
            inp.click();
        }
    });

    $(document).on('change', '#pcDirectQuoteUploadInput', async function() {
        if (!activeDirectQteId || !this.files || !this.files[0]) return;
        const file = this.files[0];
        const fd = new FormData();
        fd.append('op', 'upload_quote_file');
        fd.append('qte_id', activeDirectQteId);
        fd.append('quote_file', file);
        fd.append('_token', @json(csrf_token()));

        toast('Uploading quote document...');
        try {
            const json = await postForm(fd);
            state = json.data;
            renderAll();
            toast(json.message || 'Quote document uploaded successfully');
        } catch (err) {
            toast(err.message || 'Upload failed');
        }
    });

    document.getElementById('pcQuotesBody')?.addEventListener('click', async function(e) {
        const btn = e.target.closest('.pc-quote-del-btn');
        if (!btn) return;
        if (!ensureEditing()) return;
        const qteId = btn.getAttribute('data-qte-id');
        if (!qteId) return;
        if (!confirm('Are you sure you want to delete this quotation?')) return;
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
        modalVendors.unshift({ id: null, firm_name: '', prices: {}, file: null, existing_file_path: null, existing_file_name: null });
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
            modalVendors = [{ id: null, firm_name: '', prices: {}, file: null, existing_file_path: null, existing_file_name: null }];
            renderMultiQuoteModal();
        }
    });

    let activeFirmInputElem = null;

    function positionAndShowFirmDropdown(inputElem) {
        if (!inputElem) return;
        activeFirmInputElem = inputElem;
        const idx = parseInt(inputElem.getAttribute('data-idx'));
        const dd = document.getElementById('pcGlobalFirmDropdown');
        if (!dd) return;

        const q = (inputElem.value || '').trim().toLowerCase();
        let matched = allDbFirms;
        if (q.length > 0) {
            matched = allDbFirms.filter(name => (name || '').toLowerCase().includes(q));
        }

        if (matched.length === 0) {
            dd.innerHTML = '<div class="p-2 text-muted small" style="font-size:10px; font-style:italic;">No matching firm found (custom name will be saved)</div>';
        } else {
            dd.innerHTML = matched.map(name => {
                let display = name;
                if (q.length > 0) {
                    const escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const regex = new RegExp(`(${escaped})`, 'gi');
                    display = name.replace(regex, '<mark>$1</mark>');
                }
                return `<div class="pc-firm-opt" data-idx="${idx}" data-name="${name.replaceAll('"', '&quot;')}">${display}</div>`;
            }).join('');
        }

        // Float with fixed position directly beneath the input field
        const rect = inputElem.getBoundingClientRect();
        dd.style.top = (rect.bottom + 2) + 'px';
        dd.style.left = rect.left + 'px';
        dd.style.width = Math.max(rect.width, 220) + 'px';
        dd.style.display = 'block';
    }

    $(document).on('input', '.pc-vendor-name-input', function() {
        const idx = parseInt(this.getAttribute('data-idx'));
        const val = this.value;
        modalVendors[idx].firm_name = val;
        
        // Dynamically toggle price input disabled status without destroying DOM or losing focus
        const priceInputs = document.querySelectorAll(`.pc-price-input[data-v-idx="${idx}"]`);
        const hasName = val.trim().length > 0;
        priceInputs.forEach(inp => {
            inp.disabled = !hasName;
            if (hasName) inp.removeAttribute('title');
            else inp.setAttribute('title', 'Enter vendor name first');
        });

        positionAndShowFirmDropdown(this);
    });

    $(document).on('focus', '.pc-vendor-name-input', function() {
        positionAndShowFirmDropdown(this);
    });

    $(document).on('click', '.pc-firm-opt', function(e) {
        e.stopPropagation();
        const idx = parseInt(this.getAttribute('data-idx'));
        const name = this.getAttribute('data-name');
        if (isNaN(idx) || !name) return;

        modalVendors[idx].firm_name = name;
        const inp = document.querySelector(`.pc-vendor-name-input[data-idx="${idx}"]`);
        if (inp) {
            inp.value = name;
        }

        const dd = document.getElementById('pcGlobalFirmDropdown');
        if (dd) dd.style.display = 'none';

        // Enable price inputs for this column
        const priceInputs = document.querySelectorAll(`.pc-price-input[data-v-idx="${idx}"]`);
        priceInputs.forEach(pInp => {
            pInp.disabled = false;
            pInp.removeAttribute('title');
        });

        // Focus the first price input
        if (priceInputs.length > 0) {
            priceInputs[0].focus();
        }
    });

    // Close firm dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.pc-vendor-name-input, #pcGlobalFirmDropdown').length) {
            const dd = document.getElementById('pcGlobalFirmDropdown');
            if (dd) dd.style.display = 'none';
        }
    });

    // Close dropdown on Escape key
    $(document).on('keydown', '.pc-vendor-name-input', function(e) {
        if (e.key === 'Escape') {
            const dd = document.getElementById('pcGlobalFirmDropdown');
            if (dd) dd.style.display = 'none';
        }
    });

    // Close or reposition dropdown on scroll
    $('#pcAddQuoteModal .modal-body, #pcMultiQuoteTable, #pcAddQuoteModal').on('scroll', function() {
        if (activeFirmInputElem && document.getElementById('pcGlobalFirmDropdown')?.style.display === 'block') {
            const rect = activeFirmInputElem.getBoundingClientRect();
            const dd = document.getElementById('pcGlobalFirmDropdown');
            if (rect.top < 0 || rect.bottom > window.innerHeight) {
                dd.style.display = 'none';
            } else {
                dd.style.top = (rect.bottom + 2) + 'px';
                dd.style.left = rect.left + 'px';
            }
        }
    });

    $('#pcAddQuoteModal').on('hide.bs.modal', function() {
        const dd = document.getElementById('pcGlobalFirmDropdown');
        if (dd) dd.style.display = 'none';
    });

    $(document).on('input', '.pc-price-input', function() {
        const vIdx = parseInt(this.getAttribute('data-v-idx'));
        const iId = this.getAttribute('data-i-id');
        modalVendors[vIdx].prices[iId] = this.value;
        renderMultiQuoteTotals();
    });

    $(document).on('input change', '#pcGlobalTaxType, #pcGlobalTaxPercent', function() {
        renderMultiQuoteTotals();
    });

    $(document).on('change', '.pc-modal-quote-file-input', function() {
        const idx = parseInt(this.getAttribute('data-idx'));
        if (this.files && this.files[0]) {
            modalVendors[idx].file = this.files[0];
        } else {
            modalVendors[idx].file = null;
        }
        renderMultiQuoteModal();
    });

    $(document).on('click', '#pcSaveAllQuotesBtn', async function() {
        if (!ensureEditing()) return;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        const taxType = document.getElementById('pcGlobalTaxType')?.value || 'GST';
        const taxPercent = parseFloat(document.getElementById('pcGlobalTaxPercent')?.value || 0);

        try {
            for (const v of modalVendors) {
                if (!v.firm_name.trim()) continue;
                
                const fd = new FormData();
                fd.append('op', 'add_quote');
                if (v.id) fd.append('qte_id', v.id);
                fd.append('firm_name', v.firm_name);
                fd.append('tax_type', taxType);
                fd.append('tax_percent', taxPercent);
                fd.append('_token', @json(csrf_token()));
                
                if (v.file) {
                    fd.append('quote_file', v.file);
                }

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
            if (typeof logToDebug === 'function') logToDebug(`Save Quotes Fail: ${err.message}`);
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

    window.promptCreateIt = function(pcsId) {
        if (confirm('Do you want to create IT / RFQ Letter for this purchase case?')) {
            fetch(`/purchase/case/${pcsId}/it-letter/create`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.redirect) {
                    window.open(data.redirect, '_blank');
                    location.reload();
                } else {
                    alert(data.message || 'Error creating IT.');
                }
            })
            .catch(err => {
                alert('Failed to create IT: ' + err.message);
            });
        }
    };

    renderAll();
});
</script>
@endsection
