@extends('welcome')

@section('content')
@php
    $winnerQuote  = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->first() : null;
    $sortedQ      = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->values() : collect([]);

    $caseValue      = (float)($purchase->live_value ?? ($purchase->pcs_price ?? ($winnerQuote?->qte_price ?? 0)));
    
    // Pure Project Allocation & Metrics (Excluding CSRF share / accounts)
    $finAllocation  = (float)($head->pcc_share ?? ($head->prj_share ?? 0));
    if ($finAllocation <= 0 && isset($head->rdw_share)) {
        $rdw = (float)($head->rdw_share ?? 0);
        $cf  = (float)($head->cf_share ?? ($head->csrf_share ?? 0));
        if ($rdw > 0) {
            $finAllocation = max(0, $rdw - $cf);
        }
    }
    if ($finAllocation <= 0) {
        $finAllocation = (float)($head->allocation ?? 0);
    }
    if ($finAllocation <= 0 && !empty($purchase->project)) {
        $finAllocation = (float)($purchase->project->prj_aprvcost ?: ($purchase->project->prj_cost ?? 0));
    }

    $finReceived    = isset($head->pcc_received) ? (float)$head->pcc_received : (float)($head->received ?? 0);
    $finExpenditure = isset($head->pcc_expenditure) ? (float)$head->pcc_expenditure : (float)($head->expenditure ?? 0);
    $finBalance     = isset($head->pcc_balance) ? (float)$head->pcc_balance : ($finReceived - $finExpenditure);
    $finCommitments = isset($head->pcc_commitments) ? (float)$head->pcc_commitments : (float)($head->commitments ?? 0);
    $finInProcess   = isset($head->pcc_in_process) ? (float)$head->pcc_in_process : (float)($head->in_process ?? 0);
    $finAvailable   = isset($head->pcc_available) ? (float)$head->pcc_available : ($finBalance - $finCommitments - $finInProcess);
    $finCanBeSpent  = isset($head->pcc_can_be_spent) ? (float)$head->pcc_can_be_spent : ($finAllocation - $finExpenditure - $finCommitments - $finInProcess);

    
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
    $userUnitId = Auth::user()->acc_unt_id;
    $isInitiator = in_array($userArea, ['prj', 'rdwprj', 'division', 'initiation']) || ($userUnitId && ($purchase->pcs_unt_id == $userUnitId || $purchase->pcs_intunt_id == $userUnitId));
    $isDProc     = str_contains($userArea, 'proc') || str_contains($userArea, 'prc') || in_array($userArea, ['proc', 'prc'], true) || (Auth::user()?->acc_username === 'superadminrdw');
    $isDraft     = in_array(strtolower($purchase->pcs_status), ['draft', 'returned']);

    $hasFloated  = $purchase->decisions->where('pdec_action', 'float_to_proc')->isNotEmpty();
    $hasDProcSaved = $purchase->decisions->where('pdec_action', 'dproc_save')->isNotEmpty();
    $dprocSaved  = $hasDProcSaved;

    $currentStage = $purchase->currentSubstatus?->pss_stage ?? 'Division';
    $isFinalized = in_array(strtolower(trim($purchase->pcs_status)), ['approved', 'rejected', 'cancelled', 'not approved', 'fulfilled', 'completed']);

    // Division can edit before floating or after procurement responds
    $canEdit     = $isInitiator && $isDraft && (!$hasFloated || $hasDProcSaved);
    // DProc can add/manage quotes when case is in Procurement stage, or floated to proc, or collaborative; Division can add quotes when not floated or after procurement responds
    $canAddQuotes = ($isDProc && !$isFinalized) || ($isInitiator && $isDraft && (!$hasFloated || $hasDProcSaved));

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

/* ---- Drilldown Buttons ---- */
.btn-drill-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 4px;
    font-size: 0.68rem;
    margin-left: 5px;
    transition: all 0.2s ease;
    text-decoration: none !important;
    border: 1px solid currentColor;
    opacity: 0.85;
    vertical-align: middle;
}
.btn-drill-link:hover {
    opacity: 1;
    transform: scale(1.18);
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.btn-drill-red { color: #dc2626; background: #fee2e2; border-color: #fca5a5; }
.btn-drill-amber { color: #b45309; background: #fef3c7; border-color: #fcd34d; }
.btn-drill-green { color: #16a34a; background: #dcfce7; border-color: #86efac; }
.btn-drill-gray { color: #475569; background: #f1f5f9; border-color: #cbd5e1; }
.btn-drill-cyan { color: #0284c7; background: #e0f2fe; border-color: #7dd3fc; }

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

.dg-items-wrap { max-height:180px; overflow-y:auto; scrollbar-width: thin; scrollbar-color: #64748b #f1f5f9; }
.dg-items-wrap::-webkit-scrollbar { width:8px; height:8px; }
.dg-items-wrap::-webkit-scrollbar-track { background:#f1f5f9; border-radius:4px; }
.dg-items-wrap::-webkit-scrollbar-thumb { background:#64748b; border-radius:4px; border:1px solid #f1f5f9; }
.dg-items-wrap::-webkit-scrollbar-thumb:hover { background:#334155; }
.dg-items-table { width:100%; font-size:11px; border-collapse:collapse; white-space: nowrap; }
.dg-items-table th { padding:5px 8px; color: #475569; font-weight:700; font-size:10px; letter-spacing:.4px; text-align:left; text-transform:uppercase; background: #f8fafc; border-bottom: 1.5px solid #cbd5e1; }
.dg-items-table td { padding:5px 8px; border-top:1px solid #f1f5f9; color: #0f172a; font-size:11px; vertical-align: middle; }
.dg-items-table tr:hover td { background: #f8fafc; }
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

.dg-trail-body { padding:14px; max-height:360px; overflow-y:auto; scrollbar-width: thin; scrollbar-color: #64748b #f1f5f9; }
.dg-trail-body::-webkit-scrollbar { width:8px; height:8px; }
.dg-trail-body::-webkit-scrollbar-track { background:#f1f5f9; border-radius:4px; }
.dg-trail-body::-webkit-scrollbar-thumb { background:#64748b; border-radius:4px; border:1px solid #f1f5f9; }
.dg-trail-body::-webkit-scrollbar-thumb:hover { background:#334155; }

/* Minute Section & Remarks Scrollbars */
#conversational-comments-box {
    scrollbar-width: thin !important;
    scrollbar-color: #64748b #f1f5f9 !important;
}
#conversational-comments-box::-webkit-scrollbar {
    width: 10px !important;
}
#conversational-comments-box::-webkit-scrollbar-track {
    background: #f1f5f9 !important;
    border-radius: 6px !important;
    border: 1px solid #e2e8f0 !important;
}
#conversational-comments-box::-webkit-scrollbar-thumb {
    background: #64748b !important;
    border-radius: 6px !important;
    border: 2px solid #f1f5f9 !important;
}
#conversational-comments-box::-webkit-scrollbar-thumb:hover {
    background: #334155 !important;
}

#inlineRemarks {
    scrollbar-width: thin !important;
    scrollbar-color: #64748b #f1f5f9 !important;
}
#inlineRemarks::-webkit-scrollbar {
    width: 10px !important;
}
#inlineRemarks::-webkit-scrollbar-track {
    background: #f8fafc !important;
    border-radius: 6px !important;
    border: 1px solid #e2e8f0 !important;
}
#inlineRemarks::-webkit-scrollbar-thumb {
    background: #64748b !important;
    border-radius: 6px !important;
    border: 2px solid #f8fafc !important;
}
#inlineRemarks::-webkit-scrollbar-thumb:hover {
    background: #334155 !important;
}

#pcCaseAttachmentsList::-webkit-scrollbar {
    width: 8px;
}
#pcCaseAttachmentsList::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
#pcCaseAttachmentsList::-webkit-scrollbar-thumb {
    background: #64748b;
    border-radius: 4px;
}
#pcCaseAttachmentsList::-webkit-scrollbar-thumb:hover {
    background: #334155;
}

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
#pcMultiQuoteBody tr:hover td { background-color: #f8fafc; }
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
/* Word Document (.docx) Live Viewer Styles */
#pcQuoteViewerDocContent table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 13px; }
#pcQuoteViewerDocContent th, #pcQuoteViewerDocContent td { border: 1px solid #cbd5e1; padding: 8px 12px; vertical-align: top; color: #1e293b; }
#pcQuoteViewerDocContent th { background-color: #f1f5f9; font-weight: 600; color: #0f172a; }
#pcQuoteViewerDocContent img { max-width: 100%; height: auto; border-radius: 4px; margin: 10px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
#pcQuoteViewerDocContent p { margin-bottom: 0.85rem; }
#pcQuoteViewerDocContent h1, #pcQuoteViewerDocContent h2, #pcQuoteViewerDocContent h3, #pcQuoteViewerDocContent h4 { color: #0f172a; font-weight: 700; margin-top: 1.2rem; margin-bottom: 0.6rem; }
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
#pcGlobalFirmDropdown::-webkit-scrollbar { width: 8px; }
#pcGlobalFirmDropdown::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
#pcGlobalFirmDropdown::-webkit-scrollbar-thumb { background: #64748b; border-radius: 4px; }
#pcGlobalFirmDropdown::-webkit-scrollbar-thumb:hover { background: #334155; }
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
                                $isPsCase = strtolower(trim((string)($purchase->pcs_type ?? 'ps'))) === 'ps';
                                $hasItLetter = (bool)($purchase->itLetter || \App\Models\PurItLetter::where('pit_pcs_id', $purchase->pcs_id)->exists());
                                $quotesCount = count($purchase->quotes ?? []);
                            @endphp

                            @if($isDProc)
                                @if(!$hasItLetter)
                                    {{-- Procurement user sees button to CREATE IT on all cases --}}
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
                                {{-- Other users (Finance, Division, MD, DDG, DG) see VIEW IT / RFQ LETTER ONLY IF procurement has created it --}}
                                @if($hasItLetter)
                                    <a href="{{ route('purchase.it_annex', $purchase->pcs_id) }}" target="_blank" class="btn-hdr-action btn-hdr-it-annex rajdhani">
                                        <i class="fas fa-eye mr-1"></i> VIEW IT / RFQ LETTER
                                    </a>
                                @endif
                            @endif

                            @if($quotesCount > 1)
                                <a href="{{ route('purchase.cs_formal', $purchase->pcs_id) }}" target="_blank" class="btn-hdr-action btn-hdr-comparative-stmt rajdhani">
                                    <i class="fas fa-balance-scale mr-1"></i> COMPARATIVE STATEMENT
                                </a>
                            @endif

                            @php
                                $isPettyOrTada = in_array(strtolower(trim((string)($purchase->pcs_type ?? ''))), ['pe', 'petty', 'rb', 'tada', 'ta/da'], true);
                                $reverseRoute = strtolower(trim((string)($purchase->pcs_type ?? ''))) === 'pe' || strtolower(trim((string)($purchase->pcs_type ?? ''))) === 'petty'
                                    ? route('purchase.petty.reverse', $purchase->pcs_id)
                                    : route('purchase.tada.reverse', $purchase->pcs_id);
                            @endphp

                            @if($isPettyOrTada && Gate::check('initiate', \App\Models\AudRev::class))
                                <button type="button" class="btn-hdr-action btn-hdr-reverse rajdhani text-danger" style="border: 1px solid rgba(220, 53, 69, 0.6) !important; background: rgba(220, 53, 69, 0.08) !important; cursor: pointer;" data-toggle="modal" data-target="#reversePettyTadaModal">
                                    <i class="fas fa-sync-alt mr-1"></i> REVERSE CASE
                                </button>

                                <!-- Reverse Modal -->
                                <div class="modal fade text-left" id="reversePettyTadaModal" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content shadow-lg border-0" style="font-family: inherit;">
                                            <form action="{{ $reverseRoute }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-danger text-white py-2">
                                                    <h6 class="modal-title font-weight-bold mb-0">
                                                        <i class="fas fa-sync-alt mr-1"></i> Reverse Purchase Case #{{ $purchase->pcs_id }}
                                                    </h6>
                                                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body p-3">
                                                    <p class="small text-muted mb-2">
                                                        This will generate a Data Revision request (RevType 1: Full Cascade) for Purchase Case #{{ $purchase->pcs_id }}.
                                                    </p>
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold small text-dark">Reason for Reversal <span class="text-danger">*</span></label>
                                                        <textarea name="rev_reason" class="form-control form-control-sm" rows="3" placeholder="Enter reason for revision..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-sm btn-danger font-weight-bold px-3">
                                                        <i class="fas fa-check mr-1"></i> Generate Reversal Draft
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif

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
                        @php
                            $prjId = $purchase->project?->prj_id 
                                ?? ($purchase->head?->hed_prj_id ?? \Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $purchase->pcs_hed_id)->value('hed_prj_id'));
                            
                            $caseAttachments = \Illuminate\Support\Facades\DB::table('pur.purattachments')
                                ->where('pat_objid', $purchase->pcs_id)
                                ->where('pat_objtype', 'pcs')
                                ->whereNotNull('pat_path')
                                ->where('pat_path', '<>', '')
                                ->get();
                        @endphp
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
                                    <div class="d-flex align-items-center">
                                        <strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-project-diagram text-primary mr-2"></i>PROJECT:</strong> 
                                        <span class="badge badge-light border px-2 py-1 font-weight-bold" style="font-size: 12.5px; color: #0f172a; background: #f8fafc; border-color: #cbd5e1 !important;">
                                            {{ $purchase->project?->prj_code ?? ($purchase->head?->hed_code ?? $purchase->pcs_hed_id) }}
                                        </span>
                                        <div class="dropdown d-inline-block ml-1">
                                            <button class="btn btn-xs btn-outline-primary py-0 px-1 shadow-sm dropdown-toggle" type="button" id="projectNavDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 12px; height: 22px; width: 24px; line-height: 20px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center;" title="Project Navigation">
                                                <i class="fas fa-ellipsis-v" style="font-size: 10px;"></i>
                                            </button>
                                            <div class="dropdown-menu shadow-lg py-1 mt-1 border" aria-labelledby="projectNavDropdown" style="font-size: 12.5px; border-radius: 8px; border-color: #cbd5e1; min-width: 220px; z-index: 1050;">
                                                <div class="dropdown-header py-1 px-3 text-muted text-uppercase rajdhani font-weight-bold" style="font-size: 10px; letter-spacing: 0.8px;">Project Navigation</div>
                                                @if($prjId)
                                                <a class="dropdown-item py-2 px-3 d-flex align-items-center font-weight-bold text-dark" href="{{ route('projects.show', $prjId) }}" target="_blank">
                                                    <i class="fas fa-project-diagram text-primary mr-2" style="width: 16px;"></i> Project Details
                                                </a>
                                                @endif
                                                <a class="dropdown-item py-2 px-3 d-flex align-items-center font-weight-bold text-dark" href="{{ route('projects.financial_view', $purchase->pcs_hed_id) }}#tab-docs" target="_blank">
                                                    <i class="fas fa-paperclip text-success mr-2" style="width: 16px;"></i> Files & Attachments
                                                </a>
                                                <a class="dropdown-item py-2 px-3 d-flex align-items-center font-weight-bold text-dark" href="{{ route('projects.financial_view', $purchase->pcs_hed_id) }}#tab-milestones" target="_blank">
                                                    <i class="fas fa-coins text-warning mr-2" style="width: 16px;"></i> Milestone Costs
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-layer-group text-primary mr-2"></i>SUBHEAD:</strong> 
                                        <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                                            <span class="view-only text-dark font-weight-bold" id="pcSubheadView" style="color: #0f172a !important;">{{ $purchase->subhead_display }}</span>
                                            @if($purchase->subhead_display && $purchase->pcs_hed_id)
                                                <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'subhead', 'expenditure', $purchase->subhead_display]) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-1 shadow-sm" style="font-size: 12px; height: 22px; width: 22px; line-height: 20px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center;" title="View {{ $purchase->subhead_display }} Financial Breakdown">
                                                    <i class="fas fa-chart-bar"></i>
                                                </a>
                                            @endif
                                        </div>
                                        @if($canEdit)
                                            <form class="edit-only d-flex align-items-center flex-grow-1 pc-metadata-ajax-form ml-2" style="gap:6px; margin:0;" action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="op" value="save_metadata">
                                                <input type="text" name="subhead" class="form-control form-control-sm" value="{{ $purchase->subhead_display }}" style="font-size: 11px; height: 28px; max-width: 200px;" list="subheadOptionsList" placeholder="Subhead...">
                                                <datalist id="subheadOptionsList">
                                                    <option value="Misc">
                                                    <option value="Equipment">
                                                    @foreach(($projectSubheads ?? []) as $psh)
                                                        <option value="{{ $psh }}">
                                                    @endforeach
                                                </datalist>
                                                <button type="submit" class="btn btn-primary btn-xs font-weight-bold" style="padding: 2px 8px;"><i class="fas fa-save"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-tags text-primary mr-2"></i>CASE TYPE:</strong> 
                                        <span class="text-dark font-weight-bold" style="color: #0f172a !important;">
                                            @if($purchase->pcs_type === 'Ps')
                                                Ps — Major Purchase (With Quotations)
                                            @elseif($purchase->pcs_type === 'Pt')
                                                Pt — Incidental Expenditure (Without Quotations)
                                            @elseif($purchase->pcs_type === 'Rb')
                                                Rb — TA/DA Reimbursement
                                            @else
                                                {{ $purchase->pcs_type }}
                                            @endif
                                        </span>
                                    </div>
                                    @if($purchase->pcs_type === 'Pt')
                                    <div class="d-flex align-items-center">
                                        <strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-store text-primary mr-2"></i>AWARDED VENDOR:</strong> 
                                        <span class="view-only text-dark font-weight-bold" id="pcVendorView" style="color: #0f172a !important;">{{ $purchase->firm?->frm_name ?? ($purchase->pcs_frm_id ? ('Firm #' . $purchase->pcs_frm_id) : 'Not specified') }}</span>
                                        @if($canEdit)
                                            <form class="edit-only d-flex align-items-center flex-grow-1 pc-metadata-ajax-form" style="gap:6px; margin:0;" action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="op" value="save_metadata">
                                                <select name="pcs_frm_id" class="form-control form-control-sm" style="font-size: 11px; height: 28px; max-width: 250px;">
                                                    <option value="">-- Select Awarded Vendor --</option>
                                                    @foreach($firms as $f)
                                                        <option value="{{ $f->frm_id }}" {{ (int)$purchase->pcs_frm_id === (int)$f->frm_id ? 'selected' : '' }}>{{ $f->frm_name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-primary btn-xs font-weight-bold" style="padding: 2px 8px;"><i class="fas fa-save"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                    @endif
                                    <div><strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-building text-primary mr-2"></i>DIVISION:</strong> <span class="text-dark font-weight-bold" style="color: #0f172a !important;">{{ $purchase->unit?->unt_name ?? $purchase->pcs_unt_id }}</span></div>
                                    @php
                                        $latestDec = $purchase->latestDecision;
                                        $holderDisplay = $latestDec?->pdec_to_status ?: ($purchase->current_stage_display ?: 'Division (Initiator)');
                                        $forwardedBy = $latestDec?->account?->acc_name;
                                        $statusClass = match(strtolower(trim($purchase->pcs_status))) {
                                            'approved' => 'badge-success',
                                            'returned' => 'badge-danger',
                                            'draft'    => 'badge-secondary',
                                            default    => 'badge-primary',
                                        };
                                    @endphp
                                    <div class="d-flex align-items-center mb-1">
                                        <strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-info-circle text-primary mr-2"></i>CASE STATUS:</strong> 
                                        <span class="badge {{ $statusClass }} font-weight-bold px-2.5 py-1" style="font-size: 11.5px; letter-spacing: 0.3px;">
                                            {{ $purchase->pcs_status }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <strong style="color: #475569; width: 140px; display:inline-block; font-weight: 700;"><i class="fas fa-map-marker-alt text-primary mr-2"></i>LOCATION:</strong> 
                                        <span class="badge font-weight-bold px-2.5 py-1" style="background: #e0f2fe; color: #0369a1 !important; border: 1px solid #bae6fd; font-size: 11.5px;">
                                            <i class="fas fa-building mr-1 text-primary"></i> Currently with: {{ $holderDisplay }}
                                        </span>
                                        @if($forwardedBy && in_array($latestDec?->pdec_action, ['forward', 'forward_negative', 'float_to_proc', 'reshare_to_proc', 'return']))
                                            <span class="text-muted small ml-2" style="font-size: 11px;">
                                                (Forwarded by <strong>{{ $forwardedBy }}</strong>)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Financial Overview & Case Cost Summary --}}
                            @php
                                $breakdown = $purchase->tax_breakdown;
                                $winningQuote = $purchase->winning_quote
                                    ?? $purchase->quotes->where('qte_recomm', true)->first()
                                    ?? $purchase->quotes->sortBy('qte_price')->first();

                                $initBase = (float)($breakdown['base'] ?? 0);
                                $initSst  = (float)($breakdown['sst'] ?? 0);
                                $initGst  = (float)($breakdown['gst'] ?? 0);
                                $initTot  = (float)($breakdown['total'] ?? ($purchase->pcs_price ?? 0));

                                if ($initTot <= 0 && $winningQuote) {
                                    $initTot = (float)($winningQuote->qte_price ?: 0);
                                    $initSst = (float)($winningQuote->qte_inttax ?? 0);
                                    $initGst = (float)($winningQuote->qte_midtax ?? 0);
                                    $initBase = (float)($winningQuote->qte_intprice ?: ($initTot - $initSst - $initGst));
                                } elseif ($initBase <= 0 && $initTot > 0) {
                                    $initBase = max(0, $initTot - $initSst - $initGst);
                                }
                                if ($initTot <= 0 && $initBase > 0) {
                                    $initTot = $initBase + $initSst + $initGst;
                                }
                            @endphp
                            <div class="text-right d-flex flex-column align-items-end" style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 14px 18px; font-size: 13px; min-width: 310px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                                <div class="d-flex justify-content-between align-items-center w-100 mb-2 pb-1" style="border-bottom: 1px solid #e2e8f0;">
                                    <h6 class="rajdhani text-primary font-weight-bold mb-0" style="font-size: 13px; font-weight: 800; letter-spacing: 0.8px;">
                                        <i class="fas fa-chart-pie mr-1"></i> FINANCIAL REVIEW
                                    </h6>
                                    <button class="btn btn-xs btn-outline-primary rajdhani font-weight-bold py-0" data-toggle="modal" data-target="#financialIntelligenceModal" style="font-size: 10px; border-radius: 4px; font-weight: 700;">
                                        <i class="fas fa-expand-arrows-alt mr-1"></i> FULL REPORT
                                    </button>
                                </div>
                                
                                <div class="w-100 rajdhani" style="display: grid; grid-template-columns: auto 1fr; gap: 4px 24px; text-align: left;">
                                    <div class="text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">ALLOCATED</div>
                                    <div class="text-dark font-weight-bold text-right" style="font-size: 15px; color: #0f172a !important;">{{ number_format($finAllocation) }}</div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">RECEIVED</div>
                                    <div class="text-dark font-weight-bold text-right" style="font-size: 15px; color: #0f172a !important;">{{ number_format($finReceived) }}</div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">EXPENDITURE</div>
                                    <div class="text-right d-flex justify-content-end align-items-center">
                                        <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'expenditure']) }}" target="_blank" class="text-danger font-weight-bold text-decoration-none" style="font-size: 15px; color: #dc2626 !important;" title="View Project Expenditure Breakdown">
                                            {{ number_format($finExpenditure) }}
                                        </a>
                                        <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'expenditure']) }}" target="_blank" class="btn-drill-link btn-drill-red" title="View Project Expenditure Breakdown">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">BALANCE</div>
                                    <div class="text-primary font-weight-bold text-right" style="font-size: 15px; color: #2563eb !important;">{{ number_format($finBalance) }}</div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">COMMITMENTS</div>
                                    <div class="text-right d-flex justify-content-end align-items-center">
                                        <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'commitments']) }}" target="_blank" class="text-warning font-weight-bold text-decoration-none" style="font-size: 15px; color: #d97706 !important;" title="View Project Commitments Breakdown">
                                            {{ number_format($finCommitments) }}
                                        </a>
                                        <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'commitments']) }}" target="_blank" class="btn-drill-link btn-drill-amber" title="View Project Commitments Breakdown">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">IN PROCESS</div>
                                    <div class="text-right d-flex justify-content-end align-items-center">
                                        <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'in-process']) }}" target="_blank" class="text-muted font-weight-bold text-decoration-none" style="font-size: 15px; color: #64748b !important;" title="View Project In-Process Cases">
                                            {{ number_format($finInProcess) }}
                                        </a>
                                        <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'in-process']) }}" target="_blank" class="btn-drill-link btn-drill-gray" title="View Project In-Process Cases">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="text-success font-weight-bold border-top pt-1" style="font-size: 13px; color: #16a34a !important; border-color: #cbd5e1 !important; letter-spacing: 0.5px;">AVAILABLE</div>
                                    <div class="text-success font-weight-bold text-right border-top pt-1" style="font-size: 15px; color: #16a34a !important; border-color: #cbd5e1 !important;">{{ number_format($finAvailable) }}</div>
                                    
                                    <div class="text-warning font-weight-bold" style="font-size: 13px; color: #d97706 !important; letter-spacing: 0.5px;">CAN BE SPENT</div>
                                    <div class="text-warning font-weight-bold text-right" style="font-size: 16px; font-weight: 900; color: #d97706 !important;">{{ number_format($finCanBeSpent) }}</div>
                                </div>

                                {{-- Separator --}}
                                <div class="w-100 my-2" style="border-top: 1px dashed #cbd5e1;"></div>

                                {{-- Case Cost Summary Header --}}
                                <div class="d-flex justify-content-between align-items-center w-100 mb-1">
                                    <h6 class="rajdhani text-primary font-weight-bold mb-0" style="font-size: 12px; letter-spacing: 0.8px;">
                                        <i class="fas fa-file-invoice-dollar mr-1"></i> CASE FINANCIALS
                                    </h6>
                                </div>

                                {{-- Compact Case Cost Grid --}}
                                <div class="w-100 rajdhani" style="display: grid; grid-template-columns: auto 1fr; gap: 3px 20px; text-align: left;">
                                    <div class="text-muted font-weight-bold" style="font-size: 12px;">Price</div>
                                    <div class="text-dark font-weight-bold text-right" id="pcSummaryBasePrice" style="font-size: 13px; color: #0f172a !important;">{{ number_format($initBase, 2) }}</div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 12px;">SST</div>
                                    <div class="text-dark font-weight-bold text-right" id="pcSummarySst" style="font-size: 13px; color: #0f172a !important;">{{ number_format($initSst, 2) }}</div>
                                    
                                    <div class="text-muted font-weight-bold" style="font-size: 12px;">GST</div>
                                    <div class="text-dark font-weight-bold text-right" id="pcSummaryGst" style="font-size: 13px; color: #0f172a !important;">{{ number_format($initGst, 2) }}</div>
                                    
                                    <div class="text-success font-weight-bold border-top pt-1" style="font-size: 13px; border-color: #cbd5e1 !important; color: #16a34a !important;">TOTAL</div>
                                    <div class="text-success font-weight-bold text-right border-top pt-1" id="pcSummaryTotal" style="font-size: 16px; font-weight: 900; border-color: #cbd5e1 !important; color: #16a34a !important;">{{ number_format($initTot, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="dg-divider mb-4 mt-2" style="background: #e2e8f0;"></div>
                        
                        {{-- 1. Items Section --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="dg-sec-label mb-0">
                                    <i class="fas fa-boxes fa-xs mr-1 text-primary"></i> 
                                    @if($purchase->pcs_type === 'Rb')
                                        TA/DA Employee Allowance Lines
                                    @elseif($purchase->pcs_type === 'Pt')
                                        Incidental Expenditure Items
                                    @else
                                        Case Items & Specifications
                                    @endif
                                    <span class="badge badge-secondary badge-pill ml-2" id="pcItemCountBadge" style="font-size: 10px;">{{ $purchase->items->count() }}</span>
                                </div>
                                @if($canEdit)
                                    <button type="button" class="pc-plus-btn edit-only" id="pcAddItemInlineBtn" title="Add Item"><i class="fas fa-plus"></i></button>
                                @endif
                            </div>
                            <div class="dg-items-wrap" style="max-height: 360px; border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; overflow-x: auto; overflow-y: auto;">
                                <table class="dg-items-table" style="min-width: 100%;">
                                    <thead id="pcItemsHead" style="background: #f8fafc; border-bottom: 2px solid #cbd5e1; position: sticky; top: 0; z-index: 10;">
                                        {{-- Table Head rendered by JS according to case type (Ps, Pt, Rb) --}}
                                    </thead>
                                    <tbody id="pcItemsBody">
                                        {{-- Items rendered by JS according to case type (Ps, Pt, Rb) --}}
                                    </tbody>
                                </table>
                            </div>
                            @if($canEdit)
                                <div class="edit-only mt-3 p-3 rounded" id="pcInlineItemEditor" style="display:none; background: #f8fafc; border: 1.5px solid #93c5fd; box-shadow: 0 4px 12px rgba(37,99,235,0.08);">
                                    <div class="d-flex align-items-center mb-2" style="color: var(--rd-primary-700); font-size: 11px; font-weight: 700; letter-spacing: 0.8px;">
                                        <i class="fas fa-plus-circle mr-1 text-primary"></i> 
                                        @if($purchase->pcs_type === 'Rb')
                                            ADD TA/DA ALLOWANCE LINE FOR EMPLOYEE
                                        @elseif($purchase->pcs_type === 'Pt')
                                            ADD INCIDENTAL EXPENDITURE ITEM
                                        @else
                                            ADD ITEM TO MAJOR PURCHASE CASE
                                        @endif
                                    </div>
                                    <form id="pcAddItemForm" class="d-flex flex-column gap-2" style="margin:0;">
                                        @if($purchase->pcs_type === 'Rb')
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-4">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Select Employee *</label>
                                                    <select name="emp_id" id="pcAddEmpSelect" class="form-control form-control-sm" required style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 6px;">
                                                        <option value="">-- Choose Employee --</option>
                                                        @foreach($employees as $emp)
                                                            <option value="{{ $emp->emp_id }}">{{ $emp->emp_name }} ({{ $emp->emp_id }}) - {{ $emp->emp_rank ?? 'Staff' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Purpose / Travel Details *</label>
                                                    <input name="item_desc" id="pcAddEmpDesc" class="form-control form-control-sm" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 6px;" required placeholder="e.g. Official visit / TA/DA Allowance">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Days / Qty</label>
                                                    <input name="item_qty" id="pcAddEmpQty" type="number" step="1" value="1" class="form-control form-control-sm text-center" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 4px;" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Daily Rate (PKR)</label>
                                                    <input name="item_price" id="pcAddEmpRate" type="number" step="0.01" class="form-control form-control-sm text-right font-weight-bold" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 6px;" placeholder="Auto-calculated">
                                                </div>
                                            </div>
                                            <input type="hidden" name="item_subhead" value="Misc">
                                            <input type="hidden" name="item_qtyunit" value="Days">
                                            <input type="hidden" name="item_type" value="3">
                                            <input type="hidden" name="item_subtype" value="Travelling/Boarding/Lodging">
                                        @else
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-4">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Description / Specification *</label>
                                                    <input name="item_desc" id="pcItemDesc" class="form-control form-control-sm" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 6px;" required placeholder="Enter item description...">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Type</label>
                                                    <select name="item_type" class="form-control form-control-sm" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 4px;">
                                                        <option value="7" selected>Permanent</option>
                                                        <option value="2">Consumable</option>
                                                        <option value="3">Service</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Sub-Type</label>
                                                    <input name="item_subtype" class="form-control form-control-sm" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 6px;" value="{{ $purchase->pcs_type === 'Ps' ? 'Test / Measuring Equipment' : 'Parts' }}" placeholder="Subtype...">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Class</label>
                                                    <select name="item_type2" class="form-control form-control-sm" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 4px;">
                                                        <option value="6" selected>Asset</option>
                                                        <option value="5">Inventory</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Subhead</label>
                                                    <input name="item_subhead" class="form-control form-control-sm" value="{{ $purchase->subhead_display ?: ($purchase->pcs_type === 'Ps' ? 'Equipment' : 'Misc') }}" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 6px;">
                                                </div>
                                            </div>
                                            <div class="row g-2 align-items-center mt-1">
                                                <div class="col-md-2">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Quantity</label>
                                                    <input name="item_qty" id="pcItemQty" type="number" step="0.01" value="1" class="form-control form-control-sm text-center" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 4px;" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Unit / Deno</label>
                                                    <input name="item_qtyunit" class="form-control form-control-sm text-center" value="num" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 4px;" placeholder="num/set">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="small text-muted mb-0 font-weight-bold" style="font-size: 10.5px;">Price (PKR)</label>
                                                    <input name="item_price" type="number" step="0.01" value="0" class="form-control form-control-sm text-right font-weight-bold" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; height: 28px; font-size: 11px; padding: 2px 6px;" placeholder="0.00">
                                                </div>
                                            </div>
                                        @endif
                                        <div class="d-flex justify-content-end align-items-center mt-2" style="gap:6px;">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="pcItemCancelBtn" title="Cancel" style="height: 28px; font-size: 11px; padding: 0 12px;">Cancel</button>
                                            <button type="submit" class="btn btn-primary btn-sm px-3 rajdhani font-weight-bold" style="height: 28px; font-size: 11px; letter-spacing: 0.5px;"><i class="fas fa-plus mr-1"></i> ADD ITEM</button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- 2. Case Specific Sections (Ps: Quotations + No-Quotes; Pt: Single Vendor; Rb: TA/DA info) --}}
                        @if($isPsCase)
                        {{-- Ps: Quotations Received Section --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="dg-sec-label mb-0"><i class="fas fa-list-ol fa-xs mr-1 text-primary"></i> Quotations Received (Vendor Offers)</div>
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

                        {{-- Ps: Quotations Not Received / No-Quotes Section --}}
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="dg-sec-label mb-0" style="color: #b91c1c;">
                                    <i class="fas fa-times-circle fa-xs mr-1 text-danger"></i> Quotations Not Received (No-Quotes)
                                    <span class="badge badge-secondary badge-pill ml-2" id="pcNoQuotesBadge" style="font-size: 10px;">{{ $purchase->noQuotes->count() }}</span>
                                </div>
                                @if($canEdit)
                                    <button type="button" class="btn btn-outline-danger btn-xs edit-only rajdhani font-weight-bold" id="pcToggleAddNoQuoteBtn" style="font-size: 11px; padding: 2px 10px; border-radius: 6px;">
                                        <i class="fas fa-plus mr-1"></i> Add No-Quote Firm
                                    </button>
                                @endif
                            </div>

                            @if($canEdit)
                                <div class="edit-only p-3 mb-2 rounded" id="pcAddNoQuoteInline" style="display: none; background: #fff5f5; border: 1.5px dashed #fca5a5;">
                                    <div class="small font-weight-bold text-danger mb-2"><i class="fas fa-building mr-1"></i> SELECT FIRM THAT DID NOT SUBMIT QUOTATION:</div>
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <select id="pcNoQuoteFirmSelector" class="form-control form-control-sm flex-grow-1" style="height: 34px; font-size: 12px;">
                                            <option value="">-- Select Participating Firm --</option>
                                            @foreach($firms as $f)
                                                <option value="{{ $f->frm_id }}">{{ $f->frm_name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-danger btn-sm rajdhani font-weight-bold px-3" id="pcSubmitNoQuoteBtn" style="height: 34px; font-size: 12px;">
                                            <i class="fas fa-check mr-1"></i> ADD
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="pcCancelNoQuoteBtn" style="height: 34px; width: 34px; padding: 0;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <div class="table-responsive" style="border: 1.5px solid #cbd5e1; border-radius: 8px; background: #ffffff; overflow: hidden;">
                                <table class="dg-items-table">
                                    <thead>
                                        <tr style="background: #fff1f2;">
                                            <th class="pl-3" style="width: 50px; color: #9f1239;">S.No</th>
                                            <th style="color: #9f1239;">Firm Name</th>
                                            <th class="text-center" style="width: 200px; color: #9f1239;">Status</th>
                                            <th class="text-right pr-3 edit-only" style="width: 100px; color: #9f1239;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pcNoQuotesBody">
                                        {{-- Rendered by JS --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @elseif($purchase->pcs_type === 'Pt')
                        {{-- Pt: Incidental Single Vendor Summary Card --}}
                        @php
                            $incidentalQuote = $purchase->quotes->first();
                            $incidentalQuoteAtt = $incidentalQuote ? \Illuminate\Support\Facades\DB::table('pur.purattachments')->where('pat_objtype', 'qte')->where('pat_objid', $incidentalQuote->qte_id)->first() : null;
                        @endphp
                        <div class="p-3 mb-4 rounded border d-flex justify-content-between align-items-center flex-wrap" style="background: #f0fdfa; border-color: #99f6e4 !important; gap: 10px;">
                            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                <span class="badge badge-info px-2.5 py-1" style="background: #0d9488; font-size: 11px;"><i class="fas fa-receipt mr-1"></i> Single Vendor Procurement</span>
                                <strong class="text-dark" style="font-size: 13px;">Awarded Vendor:</strong>
                                <span class="font-weight-bold text-primary" style="font-size: 14px;">{{ $purchase->firm?->frm_name ?? ($purchase->pcs_frm_id ? ('Firm #' . $purchase->pcs_frm_id) : 'Direct Vendor') }}</span>

                                {{-- Live View Eye Icon --}}
                                @if($incidentalQuoteAtt)
                                    <button type="button" class="btn btn-sm btn-outline-primary pc-live-view-quote-btn hover-zoom px-2.5 py-0.5 ml-1" data-url="{{ url('/purchase/quote-attachment/' . $incidentalQuoteAtt->pat_id . '/view') }}" data-pat-id="{{ $incidentalQuoteAtt->pat_id }}" data-ext="{{ strtolower(pathinfo($incidentalQuoteAtt->pat_path, PATHINFO_EXTENSION)) }}" data-file-path="{{ $incidentalQuoteAtt->pat_path }}" data-file-name="{{ $purchase->firm?->frm_name ?? 'Vendor Quote' }}" data-title="{{ $purchase->firm?->frm_name ?? 'Vendor Quote' }}" style="font-size: 11px; border-radius: 6px;" title="View Vendor Quotation Live">
                                        <i class="fas fa-eye text-primary mr-1"></i> View Quote
                                    </button>
                                @endif

                                {{-- Upload / Replace Quote File Button (Shown only when in edit mode) --}}
                                @if($canEdit)
                                    <input type="file" id="pcIncidentalQuoteFileInput" class="d-none" data-qte-id="{{ $incidentalQuote?->qte_id ?? 0 }}" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                                    <button type="button" class="btn btn-sm text-white font-weight-bold rajdhani px-2.5 py-0.5 ml-1 edit-only" onclick="document.getElementById('pcIncidentalQuoteFileInput').click()" style="font-size: 11px; border-radius: 6px; background-color: var(--rd-accent, #5F7858) !important; border: none;">
                                        @if($incidentalQuoteAtt)
                                            <i class="fas fa-sync-alt mr-1"></i> Replace Quote
                                        @else
                                            <i class="fas fa-upload mr-1"></i> Upload Quote
                                        @endif
                                    </button>
                                @endif
                            </div>
                            <div>
                                <strong class="text-muted small">Budget Subhead:</strong>
                                <span class="badge badge-success px-2 py-1 ml-1" style="font-size: 11.5px;">{{ $purchase->subhead_display }}</span>
                            </div>
                        </div>
                        @elseif($purchase->pcs_type === 'Rb')
                        {{-- Rb: TA/DA Travel Reimbursement Summary Card --}}
                        <div class="p-3 mb-4 rounded border d-flex justify-content-between align-items-center" style="background: #fffbeb; border-color: #fde68a !important;">
                            <div>
                                <span class="badge badge-warning px-2.5 py-1 mr-2" style="background: #d97706; color: #fff; font-size: 11px;"><i class="fas fa-plane-departure mr-1"></i> TA/DA Travel Reimbursement</span>
                                <strong class="text-dark" style="font-size: 13px;">Payment Channel:</strong>
                                <span class="font-weight-bold text-primary ml-1" style="font-size: 13px;">Meezan Bank Account / Cross Cheque</span>
                            </div>
                            <div>
                                <strong class="text-muted small">Budget Subhead:</strong>
                                <span class="badge badge-success px-2 py-1 ml-1" style="font-size: 11.5px;">{{ $purchase->subhead_display }}</span>
                            </div>
                        </div>
                        @endif

                        {{-- 3. Terms & Conditions Section --}}
                        <div class="mb-4">
                            <div class="dg-sec-label mb-2"><i class="fas fa-file-contract fa-xs"></i> Terms & Conditions</div>
                            <div class="view-only p-3 rounded" id="pcRemarksText" style="background: #f8fafc; border: 1.5px solid #e2e8f0; font-size: 13px; line-height: 1.6; border-radius: 8px; color: #1e293b;">
                                @if(!empty(trim($purchase->pcs_remarks)))
                                    {{ $purchase->pcs_remarks }}
                                @else
                                    <span class="opacity-50 italic">No terms & conditions provided during case initiation.</span>
                                @endif
                            </div>
                            @if($canEdit)
                                <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST" id="pcRemarksForm" class="edit-only">
                                    @csrf
                                    <input type="hidden" name="op" value="save_remarks">
                                    <textarea name="pcs_remarks" id="pcRemarksInput" class="form-control" rows="3" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; border-radius: 6px; font-size: 13px; resize: vertical;" placeholder="Enter terms & conditions...">{{ $purchase->pcs_remarks }}</textarea>
                                    <div class="d-flex justify-content-end mt-2">
                                        <button type="submit" class="btn btn-primary btn-sm rajdhani font-weight-bold px-3" style="height: 32px; font-size: 12px; letter-spacing: 0.5px;"><i class="fas fa-save mr-1"></i> SAVE TERMS & CONDITIONS</button>
                                    </div>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- ============ RIGHT PANE ============ --}}
                <div class="dg-right">
                    
                    {{-- CONVERSATIONAL VIEW / MINUTE (WITH INTEGRATED CASE ATTACHMENTS DROPDOWN IN HEADER) --}}
                    <div class="dg-panel-r" style="overflow: visible;">
                        <div class="dg-panel-r-hdr py-2 px-3 d-flex justify-content-between align-items-center" style="position: relative; border-top-left-radius: 9px; border-top-right-radius: 9px;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-file-alt text-primary" style="font-size: 13px;"></i>
                                <span class="dg-panel-r-title" style="font-size: 12px; font-weight: 700; color: #0f172a !important; letter-spacing: 0.5px; text-transform: uppercase;">Minute</span>
                            </div>
                            
                            {{-- Case Attachments Dropdown Trigger on Far Right of Minute Header --}}
                            <div class="dropdown" id="pcCaseAttachmentsDropdownWrap">
                                <button type="button" class="btn btn-xs font-weight-bold rajdhani px-2 py-1 d-flex align-items-center dropdown-toggle shadow-none" id="btnCaseAttachmentsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 11px; height: 26px; border-radius: 6px; gap: 5px; background: #ffffff; border: 1.5px solid #cbd5e1; color: #1e293b; cursor: pointer;" title="View or Add Case Attachments">
                                    <i class="fas fa-paperclip text-primary" style="font-size: 11.5px;"></i>
                                    <span>CASE ATTACHMENTS</span>
                                    <span class="badge badge-primary badge-pill ml-1" id="pcCaseAttCountBadge" style="font-size: 9.5px; padding: 2px 6px;">{{ $caseAttachments->count() }}</span>
                                </button>

                                <div class="dropdown-menu dropdown-menu-right shadow-lg p-0" aria-labelledby="btnCaseAttachmentsDropdown" style="width: 380px; max-width: 92vw; border-radius: 8px; border: 1.5px solid #cbd5e1; z-index: 1060; margin-top: 5px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;">
                                    {{-- Dropdown Header with Add Button --}}
                                    <div class="d-flex justify-content-between align-items-center py-2 px-3 border-bottom" style="background: #f8fafc;">
                                        <div class="d-flex align-items-center font-weight-bold text-dark rajdhani" style="font-size: 12px; gap: 6px;">
                                            <i class="fas fa-paperclip text-primary"></i>
                                            <span>ATTACHED CASE FILES</span>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-primary font-weight-bold rajdhani px-2 py-0.5 d-flex align-items-center" data-toggle="modal" data-target="#modalAddPurchaseCaseAttachment" style="font-size: 11px; height: 23px; border-radius: 4px; background: var(--rd-accent, #5F7858) !important; border: none; gap: 4px;" title="Upload New Case Attachment">
                                            <i class="fas fa-plus"></i> <span>ADD</span>
                                        </button>
                                    </div>

                                    {{-- Dropdown Body: Attachments List --}}
                                    <div class="px-3 py-2" id="pcCaseAttachmentsList" style="font-size: 11.5px; max-height: 250px; overflow-y: auto;">
                                        @if($caseAttachments->count() > 0)
                                            @foreach($caseAttachments as $cIdx => $cDoc)
                                                @php
                                                    $cName = trim((string)($cDoc->pat_type ?: ''));
                                                    if (empty($cName) || strtolower($cName) === 'attachment') {
                                                        $fn = strtolower(basename(str_replace('\\', '/', $cDoc->pat_path)));
                                                        $cName = match(true) {
                                                            str_starts_with($fn, 'frm-') => 'Form',
                                                            str_starts_with($fn, 'min-') => 'Minute',
                                                            str_starts_with($fn, 'san-') => 'Sanction',
                                                            str_starts_with($fn, 'fs-') => 'Financial Status',
                                                            str_starts_with($fn, 'app-') => 'Approval',
                                                            str_starts_with($fn, 'aip-') => 'Approval in Principal',
                                                            str_starts_with($fn, 'mrr-') => 'Market Research Report',
                                                            str_starts_with($fn, 'wo-') => 'Work Order',
                                                            str_starts_with($fn, 'pcs-') => 'Form',
                                                            default => ($cDoc->pat_type ?: basename(str_replace('\\', '/', $cDoc->pat_path)))
                                                        };
                                                    }
                                                    $ext = strtolower(pathinfo($cDoc->pat_path, PATHINFO_EXTENSION));
                                                @endphp
                                                <div class="d-flex justify-content-between align-items-center py-1.5 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: #f1f5f9 !important;">
                                                    <div class="d-flex align-items-center overflow-hidden mr-2" style="flex: 1; min-width: 0; gap: 6px;">
                                                        <span class="text-muted font-weight-bold flex-shrink-0" style="font-size: 10px; width: 16px;">{{ $cIdx + 1 }}.</span>
                                                        @if(in_array($ext, ['pdf']))
                                                            <i class="far fa-file-pdf text-danger flex-shrink-0" style="font-size: 12px;"></i>
                                                        @elseif(in_array($ext, ['doc', 'docx']))
                                                            <i class="far fa-file-word text-primary flex-shrink-0" style="font-size: 12px;"></i>
                                                        @elseif(in_array($ext, ['xls', 'xlsx']))
                                                            <i class="far fa-file-excel text-success flex-shrink-0" style="font-size: 12px;"></i>
                                                        @elseif(in_array($ext, ['png', 'jpg', 'jpeg']))
                                                            <i class="far fa-file-image text-info flex-shrink-0" style="font-size: 12px;"></i>
                                                        @else
                                                            <i class="far fa-file-alt text-secondary flex-shrink-0" style="font-size: 12px;"></i>
                                                        @endif
                                                        <span class="text-truncate font-weight-bold text-dark" style="font-size: 11.5px;" title="{{ $cName }}">
                                                            {{ $cName }}
                                                        </span>
                                                    </div>
                                                    <div class="d-flex align-items-center flex-shrink-0" style="gap: 4px;">
                                                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 pc-live-view-quote-btn hover-zoom font-weight-bold" data-url="{{ url('/purchase/quote-attachment/' . $cDoc->pat_id . '/view') }}" data-pat-id="{{ $cDoc->pat_id }}" data-ext="{{ $ext }}" data-file-path="{{ $cDoc->pat_path }}" data-file-name="{{ $cName }}" data-title="{{ $cName }}" style="font-size: 11px; height: 22px; border-radius: 4px;" title="View {{ $cName }}">
                                                            <i class="fas fa-eye mr-1"></i> View
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-3 text-muted" style="font-size: 11px;">
                                                <i class="fas fa-folder-open text-muted mr-1"></i> No case attachments uploaded yet.
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Dropdown Footer: Quick Action to Attach Document --}}
                                    <div class="p-2 border-top bg-light text-center" style="border-color: #e2e8f0 !important;">
                                        <button type="button" class="btn btn-xs btn-outline-success font-weight-bold w-100 py-1 d-flex align-items-center justify-content-center" data-toggle="modal" data-target="#modalAddPurchaseCaseAttachment" style="font-size: 11px; border-radius: 4px; gap: 5px;">
                                            <i class="fas fa-plus"></i> <span>ATTACH NEW DOCUMENT</span>
                                        </button>
                                    </div>
                                </div>
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
                                                    <i class="fas fa-caret-right mr-1"></i>{{ strtoupper($actionVerb) }}@if(!empty($toStatusDisplay) && !in_array($act, ['approve', 'reject', 'not_approved']))<span class="ml-1" style="text-transform: none; font-size: 11px; color: #334155; font-weight: 600;"> to <strong class="text-dark">{{ $toStatusDisplay }}</strong></span>@endif
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

@if($canAddQuotes && $isPsCase)

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
                        <label class="mb-0 mr-1.5 small font-weight-bold text-dark">TAX MODE:</label>
                        <select id="pcGlobalTaxMode" class="form-control form-control-sm" style="width: 105px; height: 26px; font-size: 11px; background: #ffffff; color: var(--rd-text1); border-color: var(--rd-border2);">
                            <option value="exclusive" selected>Without Tax</option>
                            <option value="inclusive">With Tax</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1.5 small font-weight-bold text-dark">TAX TYPE:</label>
                        <select id="pcGlobalTaxType" class="form-control form-control-sm" style="width: 70px; height: 26px; font-size: 11px; background: #ffffff; color: var(--rd-text1); border-color: var(--rd-border2);">
                            <option value="GST">GST</option>
                            <option value="SST">SST</option>
                        </select>
                    </div>
                    <div class="d-flex align-items-center">
                        <label class="mb-0 mr-1.5 small font-weight-bold text-dark">TAX %:</label>
                        <input type="number" id="pcGlobalTaxPercent" class="form-control form-control-sm" value="18" step="0.1" style="width: 55px; height: 26px; font-size: 11px; background: #ffffff; color: var(--rd-text1); border-color: var(--rd-border2);">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm font-weight-bold px-3" id="pcAddVendorColBtn" style="height: 30px; font-size: 12px;"><i class="fas fa-user-plus mr-1"></i> Add Vendor</button>
                    <button type="button" class="close ml-2 text-dark" data-dismiss="modal">&times;</button>
                </div>

            </div>

            <div class="px-3 py-1.5 d-flex align-items-center justify-content-between flex-shrink-0" style="background: #fffbeb; border-bottom: 1px solid #fde68a; font-size: 11.5px; color: #92400e;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-stamp text-warning mr-2" style="font-size: 15px;"></i>
                    <div>
                        <strong>MANDATORY:</strong> Please ensure that <u>ONLY officially ATTESTED quotations</u>  are attached.
                        
                    </div>
                </div>
                <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold" style="font-size: 9.5px; border: 1px solid #f59e0b;">
                    <i class="fas fa-check-double mr-1"></i> Attested Only
                </span>
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
                    <a href="#" id="pcQuoteViewerDownloadBtn" download class="btn btn-xs btn-outline-secondary px-2.5 py-1" style="font-size: 11px;" title="Download Copy">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>
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
    @foreach($firms ?? [] as $f)
        <option value="{{ $f->frm_name }}"></option>
    @endforeach
</datalist>

@if($canEdit)


<div class="modal fade" id="pcEditRemarksModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.12);">
            <div class="modal-header py-2 px-3" style="background: var(--rd-surface2); border-bottom: 1px solid var(--rd-border);">
                <h6 class="modal-title rajdhani font-weight-bold text-dark" style="letter-spacing: 1px;">TERMS & CONDITIONS</h6>
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
@endif

{{-- MODAL: ATTACH DOCUMENT TO PURCHASE CASE (ACCESSIBLE TO ALL USERS) --}}
<div class="modal fade" id="modalAddPurchaseCaseAttachment" tabindex="-1" role="dialog" aria-labelledby="modalAddPurchaseCaseAttachmentLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 440px;">
        <div class="modal-content" style="border-radius: 10px; border: 1px solid #cbd5e1; box-shadow: 0 10px 25px rgba(0,0,0,0.1); background: #ffffff;">
            <div class="modal-header py-2.5 px-3" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <h6 class="modal-title font-weight-bold rajdhani text-dark mb-0" id="modalAddPurchaseCaseAttachmentLabel" style="font-size: 13.5px; letter-spacing: 0.5px;">
                    <i class="fas fa-file-upload text-success mr-1.5" style="color: #5F7858 !important;"></i> ATTACH DOCUMENT TO PURCHASE CASE
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddPurchaseCaseAttachment" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3">
                    <div class="form-group mb-2.5">
                        <label class="font-weight-bold text-dark small mb-1" style="font-size: 11px;">DOCUMENT TITLE / NAME <span class="text-danger">*</span></label>
                        <input type="text" name="doc_title" id="purAttDocTitle" class="form-control" placeholder="e.g., Justification Note, CNIC Copy, Degree" required style="font-size: 12px; border-radius: 6px; border-color: #cbd5e1;">
                    </div>
                    <div class="form-group mb-1">
                        <label class="font-weight-bold text-dark small mb-1" style="font-size: 11px;">SELECT FILE <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="purAttFile" class="form-control-file border p-1.5 rounded w-100" required style="font-size: 11.5px; border-color: #cbd5e1 !important; background: #fafafa; border-radius: 6px;">
                        <small class="text-muted d-block mt-1" style="font-size: 10px;"><i class="fas fa-info-circle mr-1"></i> PDF, DOCX, XLSX, PNG, JPG (Max: 20MB)</small>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3" style="background: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-sm btn-light border font-weight-bold" data-dismiss="modal" style="font-size: 11.5px; border-radius: 6px;">Cancel</button>
                    <button type="submit" id="btnUploadPurAttachment" class="btn btn-sm text-white font-weight-bold rajdhani px-3" style="font-size: 12px; border-radius: 6px; background-color: var(--rd-accent, #5F7858) !important; border-color: var(--rd-accent, #5F7858) !important;">
                        <i class="fas fa-upload mr-1"></i> UPLOAD ATTACHMENT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============ FINANCIAL INTELLIGENCE DASHBOARD MODAL ============ --}}
{{-- ============ PREMIUM FINANCIAL INTELLIGENCE DASHBOARD MODAL ============ --}}
<div class="modal fade" id="financialIntelligenceModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 96%; width: 1440px;">
        <div class="modal-content" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; overflow: hidden; box-shadow: 0 16px 48px rgba(0,0,0,0.15);">
            <div class="modal-header border-bottom py-3 px-4 d-flex align-items-center justify-content-between" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="font-size: 26px; color: #1e3a8a;"><i class="fas fa-chart-line"></i></div>
                    <div>
                        <h5 class="modal-title rajdhani font-weight-bold mb-0" style="letter-spacing: 1px; font-size: 19px; color: #0f172a;">FINANCIAL INTELLIGENCE REPORT</h5>
                        <div class="small rajdhani" style="font-size: 13px; font-weight: 600; color: #475569;">{{ $head->head_name ?? ($head->hed_name ?? ($head->prj_code ?? 'N/A')) }} | DATED {{ date('d M y') }} <span class="ml-2 font-weight-bold" style="color: #2563eb;">{{ ($head->trans_type ?? 1) == 1 ? '(Million PKR without GST)' : '(PKR with GST)' }}</span></div>
                    </div>
                    <div class="ml-auto d-flex align-items-center mr-4" style="gap: 8px;">
                        @if($prjId)
                        <a href="{{ route('projects.show', $prjId) }}" target="_blank" class="btn btn-sm rajdhani font-weight-bold d-inline-flex align-items-center" style="font-size: 12px; border-radius: 6px; gap: 6px; padding: 6px 14px; letter-spacing: 0.5px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #fff; border: none; box-shadow: 0 2px 6px rgba(37,99,235,0.25); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 10px rgba(37,99,235,0.35)';" onmouseout="this.style.transform='';this.style.boxShadow='0 2px 6px rgba(37,99,235,0.25)';">
                            <i class="fas fa-project-diagram"></i> Project Details
                        </a>
                        @endif
                        <a href="{{ route('projects.financial_view', $purchase->pcs_hed_id) }}#tab-docs" target="_blank" class="btn btn-sm rajdhani font-weight-bold d-inline-flex align-items-center" style="font-size: 12px; border-radius: 6px; gap: 6px; padding: 6px 14px; letter-spacing: 0.5px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #fff; border: none; box-shadow: 0 2px 6px rgba(22,163,74,0.25); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 10px rgba(22,163,74,0.35)';" onmouseout="this.style.transform='';this.style.boxShadow='0 2px 6px rgba(22,163,74,0.25)';">
                            <i class="fas fa-paperclip"></i> Files & Attachments
                        </a>
                        <a href="{{ route('projects.financial_view', $purchase->pcs_hed_id) }}#tab-milestones" target="_blank" class="btn btn-sm rajdhani font-weight-bold d-inline-flex align-items-center" style="font-size: 12px; border-radius: 6px; gap: 6px; padding: 6px 14px; letter-spacing: 0.5px; background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: #fff; border: none; box-shadow: 0 2px 6px rgba(217,119,6,0.25); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 4px 10px rgba(217,119,6,0.35)';" onmouseout="this.style.transform='';this.style.boxShadow='0 2px 6px rgba(217,119,6,0.25)';">
                            <i class="fas fa-coins"></i> Milestone Costs
                        </a>
                    </div>
                </div>
                <button type="button" class="close text-dark opacity-50 hover-opacity-100" data-dismiss="modal" style="font-size: 24px; padding: 12px 18px;">&times;</button>
            </div>
            
            <div class="modal-body p-0" style="background: #ffffff;">
                {{-- Top Summary bar --}}
                <div class="row no-gutters border-bottom" style="background: #f1f5f9; border-color: #cbd5e1 !important;">
                    <div class="col-md-3 border-right p-3" style="border-color: #cbd5e1 !important;">
                        <div class="small rajdhani font-weight-bold" style="font-size: 13px; letter-spacing: 1px; color: #475569;">ALLOCATION</div>
                        <div class="h4 mb-0 font-weight-bold rajdhani" style="font-size: 23px; color: #0f172a; font-weight: 900;">{{ number_format($head->allocation ?? 0) }}</div>
                    </div>
                    <div class="col-md-3 border-right p-3" style="border-color: #cbd5e1 !important;">
                        <div class="small rajdhani font-weight-bold" style="font-size: 13px; letter-spacing: 1px; color: #475569;">MTSS SHARE</div>
                        <div class="h4 mb-0 font-weight-bold rajdhani" style="font-size: 23px; color: #0f172a; font-weight: 900;">{{ number_format($head->mtss_share ?? 0) }}</div>
                    </div>
                    <div class="col-md-3 border-right p-3" style="border-color: #cbd5e1 !important;">
                        <div class="small rajdhani font-weight-bold" style="font-size: 13px; letter-spacing: 1px; color: #475569;">RDW SHARE</div>
                        <div class="h4 mb-0 font-weight-bold rajdhani" style="font-size: 23px; color: #1e40af; font-weight: 900;">{{ number_format($head->rdw_share ?? 0) }}</div>
                    </div>
                    <div class="col-md-3 p-3">
                        <div class="small rajdhani font-weight-bold" style="font-size: 13px; letter-spacing: 1px; color: #475569;">CSRF SHARE</div>
                        <div class="h4 mb-0 font-weight-bold rajdhani" style="font-size: 23px; color: #0f172a; font-weight: 900;">{{ number_format($head->csrf_share ?? 0) }}</div>
                    </div>
                </div>

                <div class="row no-gutters">
                    {{-- Left Pane: Detailed Metrics Table --}}
                    <div class="col-xl-4 col-lg-5 border-right p-4" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <h6 class="rajdhani font-weight-bold mb-0" style="letter-spacing: 1px; font-size: 16px; font-weight: 800; color: #1e3a8a;"><i class="fas fa-table mr-2"></i>PROJECT SNAPSHOT</h6>
                            <div class="small rajdhani" style="font-size: 12.5px; font-weight: 700; color: #475569;">FIGURES IN PKR</div>
                        </div>

                        <div class="fin-table-modern table-responsive rounded border overflow-auto" style="border-color: #cbd5e1 !important; background: #ffffff;">
                            <table class="table table-sm mb-0 rajdhani" style="font-size: 14.5px;">
                                <thead style="background: #e2e8f0; font-size: 13.5px; font-weight: 800; color: #1e293b;">
                                    <tr>
                                        <th class="pl-3 py-2 border-0">METRIC</th>
                                        <th class="text-right py-2 border-0" style="color: #1e40af;">PROJECT</th>
                                        <th class="text-right py-2 border-0" style="color: #b45309;">CSRF</th>
                                        <th class="text-right pr-3 py-2 border-0" style="color: #15803d;">ACTUAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background: rgba(37,99,235,0.06); border-bottom: 1.5px solid #cbd5e1;">
                                        <td class="pl-3 py-2 font-weight-bold text-dark" style="font-size: 15px;"><i class="fas fa-coins text-warning mr-1"></i> Allocated</td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #1e40af; font-size: 15px;">{{ number_format($head->pcc_share ?? 0) }}</td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #b45309; font-size: 15px;">{{ number_format($head->csrf_share ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 font-weight-bold" style="color: #15803d; font-size: 15px;">{{ number_format($head->allocation ?? 0) }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 font-weight-bold text-secondary">Received</td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #1e40af;">{{ number_format($head->pcc_received ?? 0) }}</td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #b45309;">{{ number_format($head->cf_received ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 text-muted font-weight-bold">--</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 font-weight-bold text-danger">Expenditure</td>
                                        <td class="text-right py-2 text-danger font-weight-bold">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'expenditure']) }}" target="_blank" class="text-danger text-decoration-none font-weight-bold" title="View Project Expenditure Breakdown">
                                                {{ number_format($head->pcc_expenditure ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'expenditure']) }}" target="_blank" class="btn-drill-link btn-drill-red" style="width: 22px; height: 22px; font-size: 11px;" title="View Project Expenditure Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right py-2 text-danger font-weight-bold">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'csrf', 'expenditure']) }}" target="_blank" class="text-danger text-decoration-none font-weight-bold" title="View CSRF Expenditure Breakdown">
                                                {{ number_format($head->cf_expenditure ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'csrf', 'expenditure']) }}" target="_blank" class="btn-drill-link btn-drill-red" style="width: 22px; height: 22px; font-size: 11px;" title="View CSRF Expenditure Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right pr-3 py-2" style="color: #15803d; font-weight: 800;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'acc', 'expenditure']) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #15803d;" title="View Total Expenditure Breakdown">
                                                {{ number_format($head->prj_expenditure ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'acc', 'expenditure']) }}" target="_blank" class="btn-drill-link btn-drill-green" style="width: 22px; height: 22px; font-size: 11px;" title="View Total Expenditure Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr style="background: rgba(37,99,235,0.05); border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 text-primary font-weight-bold" style="font-size: 15px;">Balance</td>
                                        <td class="text-right py-2 font-weight-bold text-primary" style="font-size: 15px;">{{ number_format($head->pcc_balance ?? 0) }}</td>
                                        <td class="text-right py-2 font-weight-bold text-primary" style="font-size: 15px;">{{ number_format($head->cf_balance ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 text-muted font-weight-bold">--</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 font-weight-bold" style="color: #b45309;">Commitments</td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #b45309;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'commitments']) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #b45309;" title="View Project Commitments Breakdown">
                                                {{ number_format($head->pcc_commitments ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'commitments']) }}" target="_blank" class="btn-drill-link btn-drill-amber" style="width: 22px; height: 22px; font-size: 11px;" title="View Project Commitments Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right py-2 font-weight-bold" style="color: #b45309;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'csrf', 'commitments']) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #b45309;" title="View CSRF Commitments Breakdown">
                                                {{ number_format($head->cf_commitments ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'csrf', 'commitments']) }}" target="_blank" class="btn-drill-link btn-drill-amber" style="width: 22px; height: 22px; font-size: 11px;" title="View CSRF Commitments Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right pr-3 py-2 font-weight-bold" style="color: #15803d;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'acc', 'commitments']) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #15803d;" title="View Total Commitments Breakdown">
                                                {{ number_format($head->prj_commitments ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'acc', 'commitments']) }}" target="_blank" class="btn-drill-link btn-drill-green" style="width: 22px; height: 22px; font-size: 11px;" title="View Total Commitments Breakdown">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 font-weight-bold text-secondary">In Process</td>
                                        <td class="text-right py-2 font-weight-bold text-secondary">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'in-process']) }}" target="_blank" class="text-muted text-decoration-none font-weight-bold" title="View Project In-Process Cases">
                                                {{ number_format($head->pcc_in_process ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'pcc', 'in-process']) }}" target="_blank" class="btn-drill-link btn-drill-gray" style="width: 22px; height: 22px; font-size: 11px;" title="View Project In-Process Cases">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right py-2 font-weight-bold text-secondary">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'csrf', 'in-process']) }}" target="_blank" class="text-muted text-decoration-none font-weight-bold" title="View CSRF In-Process Cases">
                                                {{ number_format($head->cf_in_process ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'csrf', 'in-process']) }}" target="_blank" class="btn-drill-link btn-drill-gray" style="width: 22px; height: 22px; font-size: 11px;" title="View CSRF In-Process Cases">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                        <td class="text-right pr-3 py-2 font-weight-bold" style="color: #15803d;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'acc', 'in-process']) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #15803d;" title="View Total In-Process Cases">
                                                {{ number_format($head->prj_in_process ?? 0) }}
                                            </a>
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'acc', 'in-process']) }}" target="_blank" class="btn-drill-link btn-drill-green" style="width: 22px; height: 22px; font-size: 11px;" title="View Total In-Process Cases">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr style="background: rgba(22,163,74,0.08); border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 font-weight-bold text-success" style="font-size: 15px;">Available</td>
                                        <td class="text-right py-2 font-weight-bold text-success" style="font-size: 15px;">{{ number_format($head->pcc_available ?? 0) }}</td>
                                        <td class="text-right py-2 font-weight-bold text-success" style="font-size: 15px;">{{ number_format($head->cf_available ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 text-muted font-weight-bold">--</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td class="pl-3 py-2 font-weight-bold text-secondary">Yet to be Rec</td>
                                        <td class="text-right py-2 font-weight-bold text-secondary">{{ number_format($head->pcc_yet_to_be_received ?? 0) }}</td>
                                        <td class="text-right py-2 font-weight-bold text-secondary">{{ number_format($head->cf_yet_to_be_received ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 text-muted font-weight-bold">--</td>
                                    </tr>
                                    <tr style="background: rgba(220,38,38,0.08);">
                                        <td class="pl-3 py-2 text-danger font-weight-bold" style="font-size: 15px;">Remaining</td>
                                        <td class="text-right py-2 text-danger font-weight-bold" style="font-size: 15px;">{{ number_format($head->pcc_can_be_spent ?? 0) }}</td>
                                        <td class="text-right py-2 text-danger font-weight-bold" style="font-size: 15px;">{{ number_format($head->cf_can_be_spent ?? 0) }}</td>
                                        <td class="text-right pr-3 py-2 font-weight-bold" style="color: #15803d; font-size: 15px;">{{ number_format($head->prj_remaining ?? 0) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Receivables section --}}
                        <div class="mt-4 pt-4 border-top" style="border-color: #cbd5e1 !important;">
                            <h6 class="rajdhani font-weight-bold mb-3" style="font-size: 14px; font-weight: 800; color: #1e293b; letter-spacing: 1.5px;">RECEIVABLES</h6>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="rajdhani font-weight-bold" style="font-size: 14px; color: #475569;">Comp. Milestones</span>
                                <span class="text-dark rajdhani font-weight-bold" style="font-size: 14px;">{{ number_format($head->receivable_completed) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mb-2">
                                <span class="rajdhani font-weight-bold" style="font-size: 14px; color: #475569;">Current Milestone</span>
                                <span class="text-dark rajdhani font-weight-bold" style="font-size: 14px;">{{ number_format($head->receivable_current) }}</span>
                            </div>
                            <div class="receivable-item d-flex justify-content-between mt-3 p-3 rounded" style="background: rgba(37,99,235,0.08); border: 1.5px solid rgba(37,99,235,0.3);">
                                <span class="rajdhani font-weight-bold" style="color: #1e40af; font-size: 14.5px;">Available after Rcv.</span>
                                <span class="rajdhani font-weight-bold" style="color: #1e40af; font-size: 16px;">{{ number_format($head->available_after_receivables) }}</span>
                            </div>
                        </div>

                        {{-- Exp Sources --}}
                        <div class="mt-4 pt-4 border-top" style="border-color: #cbd5e1 !important;">
                            <h6 class="rajdhani font-weight-bold mb-3" style="font-size: 14px; font-weight: 800; color: #1e293b; letter-spacing: 1.5px;">EXP. SOURCES</h6>
                            <div class="d-flex justify-content-between mb-1.5" style="font-size: 13.5px;">
                                <span class="rajdhani font-weight-bold" style="color: #475569;">From this account</span>
                                <span class="text-dark rajdhani font-weight-bold">{{ number_format($head->exp_this_account) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1.5" style="font-size: 13.5px;">
                                <span class="rajdhani font-weight-bold" style="color: #475569;">From other accounts</span>
                                <span class="text-dark rajdhani font-weight-bold">{{ number_format($head->exp_other_accounts) }}</span>
                            </div>
                            <div class="d-flex justify-content-between" style="font-size: 13.5px;">
                                <span class="rajdhani font-weight-bold" style="color: #475569;">Other's exp. this acc.</span>
                                <span class="text-dark rajdhani font-weight-bold">{{ number_format($head->others_exp_this_account) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Pane: Full Subheads Breakdown (With Live Drilldown) --}}
                    <div class="col-xl-8 col-lg-7 p-4" style="background: #ffffff;">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-color: #cbd5e1 !important;">
                            <div>
                                <h6 class="rajdhani font-weight-bold mb-0" style="letter-spacing: 1px; font-size: 17.5px; font-weight: 800; color: #1e3a8a;">
                                    <i class="fas fa-layer-group mr-2"></i> SUBHEAD FINANCIAL BREAKDOWN
                                </h6>
                                <div class="small rajdhani mt-0.5" style="font-size: 13px; font-weight: 600; color: #475569;">DETAILED ALLOCATION, EXPENDITURE, COMMITMENTS, IN PROCESS & REMAINING</div>
                            </div>
                            <span class="badge badge-primary px-3 py-1.5 rajdhani font-weight-bold" style="font-size: 13px; background: rgba(37,99,235,0.12); color: #1d4ed8; border: 1.5px solid rgba(37,99,235,0.3);">
                                {{ count($subheads ?? []) }} SUBHEADS
                            </span>
                        </div>

                        <div class="table-responsive rounded border" style="border-color: #cbd5e1 !important;">
                            <table class="table table-sm table-hover mb-0 rajdhani" style="font-size: 14.5px; background: #ffffff;">
                                <thead style="background: #e2e8f0;">
                                    <tr class="font-weight-bold" style="font-size: 13.5px; color: #1e293b;">
                                        <th class="pl-3 py-2.5" style="white-space: nowrap;">SUBHEAD</th>
                                        <th class="text-right py-2.5" style="white-space: nowrap;">ALLOCATED</th>
                                        <th class="text-right py-2.5" style="white-space: nowrap;">EXPENDITURE</th>
                                        <th class="text-right py-2.5" style="white-space: nowrap;">COMMITMENTS</th>
                                        <th class="text-right py-2.5" style="white-space: nowrap;">IN PROCESS</th>
                                        <th class="text-right py-2.5" style="white-space: nowrap;">REMAINING</th>
                                        <th class="text-center pr-3 py-2.5" style="width: 90px; white-space: nowrap;">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totAlloc = 0;
                                        $totExp = 0;
                                        $totCmt = 0;
                                        $totIpc = 0;
                                        $totRem = 0;
                                    @endphp
                                    @forelse($subheads ?? [] as $sh)
                                    @php
                                        $sName = is_array($sh) ? ($sh['name'] ?? '') : ($sh->name ?? '');
                                        $sAlloc = (float)(is_array($sh) ? ($sh['allocation'] ?? 0) : ($sh->allocation ?? 0));
                                        $sExp = (float)(is_array($sh) ? ($sh['expenditure'] ?? 0) : ($sh->expenditure ?? 0));
                                        $sCmt = (float)(is_array($sh) ? ($sh['commitments'] ?? 0) : ($sh->commitments ?? 0));
                                        $sIpc = (float)(is_array($sh) ? ($sh['in_process'] ?? 0) : ($sh->in_process ?? 0));
                                        $sRem = (float)(is_array($sh) ? ($sh['remaining'] ?? ($sh['can_be_spent'] ?? 0)) : ($sh->remaining ?? ($sh->can_be_spent ?? 0)));

                                        $totAlloc += $sAlloc;
                                        $totExp += $sExp;
                                        $totCmt += $sCmt;
                                        $totIpc += $sIpc;
                                        $totRem += $sRem;

                                        $isCaseSubhead = !empty($purchase->subhead_display) && strcasecmp(trim($sName), trim($purchase->subhead_display)) === 0;
                                    @endphp
                                    <tr style="{{ $isCaseSubhead ? 'background: #fef9c3 !important; border-left: 5px solid #eab308 !important; box-shadow: inset 0 0 0 1px #fde047;' : 'border-bottom: 1px solid #e2e8f0;' }}">
                                        <td class="pl-3 py-2 font-weight-bold text-dark align-middle" style="white-space: nowrap; font-size: 14.5px;">
                                            <i class="fas fa-folder-open text-primary mr-1"></i> {{ $sName }}
                                            @if($isCaseSubhead)
                                                <span class="badge text-dark ml-2 px-2 py-0.5 rajdhani font-weight-bold" style="font-size: 11px; background: #eab308; color: #713f12 !important; border: 1px solid #ca8a04;">
                                                    <i class="fas fa-check-circle mr-1"></i> ACTIVE CASE SUBHEAD
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-right py-2 font-weight-bold align-middle" style="color: #0f172a; white-space: nowrap; font-size: 14.5px;">
                                            {{ number_format($sAlloc) }}
                                        </td>
                                        <td class="text-right py-2 font-weight-bold text-danger align-middle" style="white-space: nowrap; font-size: 14.5px;">
                                            <div class="d-inline-flex align-items-center justify-content-end" style="gap: 5px; white-space: nowrap;">
                                                <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'subhead', 'expenditure', $sName]) }}" target="_blank" class="text-danger text-decoration-none font-weight-bold" title="Drilldown {{ $sName }} Expenditure">
                                                    {{ number_format($sExp) }}
                                                </a>
                                                <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'subhead', 'expenditure', $sName]) }}" target="_blank" class="btn-drill-link btn-drill-red" style="width: 22px; height: 22px; font-size: 11px;" title="Drilldown {{ $sName }} Expenditure">
                                                    <i class="fas fa-search"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-right py-2 font-weight-bold align-middle" style="color: #d97706; white-space: nowrap; font-size: 14.5px;">
                                            <div class="d-inline-flex align-items-center justify-content-end" style="gap: 5px; white-space: nowrap;">
                                                <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'subhead', 'commitments', $sName]) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #d97706;" title="Drilldown {{ $sName }} Commitments">
                                                    {{ number_format($sCmt) }}
                                                </a>
                                                <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'subhead', 'commitments', $sName]) }}" target="_blank" class="btn-drill-link btn-drill-amber" style="width: 22px; height: 22px; font-size: 11px;" title="Drilldown {{ $sName }} Commitments">
                                                    <i class="fas fa-search"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-right py-2 font-weight-bold align-middle" style="color: #475569; white-space: nowrap; font-size: 14.5px;">
                                            <div class="d-inline-flex align-items-center justify-content-end" style="gap: 5px; white-space: nowrap;">
                                                <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'subhead', 'in-process', $sName]) }}" target="_blank" class="text-decoration-none font-weight-bold" style="color: #475569;" title="Drilldown {{ $sName }} In-Process">
                                                    {{ number_format($sIpc) }}
                                                </a>
                                                <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'subhead', 'in-process', $sName]) }}" target="_blank" class="btn-drill-link btn-drill-gray" style="width: 22px; height: 22px; font-size: 11px;" title="Drilldown {{ $sName }} In-Process">
                                                    <i class="fas fa-search"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-right py-2 font-weight-bold align-middle {{ $sRem < 0 ? 'text-danger' : 'text-success' }}" style="white-space: nowrap; font-size: 14.5px;">
                                            {{ number_format($sRem) }}
                                        </td>
                                        <td class="text-center pr-3 py-2 align-middle" style="white-space: nowrap;">
                                            <a href="{{ route('division.finance-of-project.drilldown', [$purchase->pcs_hed_id, 'subhead', 'expenditure', $sName]) }}" target="_blank" class="btn btn-xs btn-outline-primary rajdhani font-weight-bold py-1 px-2.5" style="font-size: 12px; font-weight: 800; border-radius: 4px;" title="View Full {{ $sName }} Breakdown">
                                                <i class="fas fa-external-link-alt mr-1"></i> VIEW
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 font-weight-bold text-muted" style="font-size: 14px;">No subheads available.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if(count($subheads ?? []) > 0)
                                <tfoot style="background: #e0f2fe; font-weight: 900; border-top: 2px solid #94a3b8;">
                                    <tr>
                                        <td class="pl-3 py-2.5 font-weight-bold text-dark" style="white-space: nowrap; font-size: 15.5px;">TOTAL</td>
                                        <td class="text-right py-2.5 font-weight-bold" style="color: #0f172a; white-space: nowrap; font-size: 15.5px;">{{ number_format($totAlloc) }}</td>
                                        <td class="text-right py-2.5 font-weight-bold text-danger" style="white-space: nowrap; font-size: 15.5px;">{{ number_format($totExp) }}</td>
                                        <td class="text-right py-2.5 font-weight-bold" style="color: #d97706; white-space: nowrap; font-size: 15.5px;">{{ number_format($totCmt) }}</td>
                                        <td class="text-right py-2.5 font-weight-bold" style="color: #475569; white-space: nowrap; font-size: 15.5px;">{{ number_format($totIpc) }}</td>
                                        <td class="text-right py-2.5 font-weight-bold {{ $totRem < 0 ? 'text-danger' : 'text-success' }}" style="white-space: nowrap; font-size: 15.5px;">{{ number_format($totRem) }}</td>
                                        <td class="text-center pr-3 py-2.5 text-muted font-weight-bold" style="white-space: nowrap; font-size: 14px;">--</td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer border-top py-2 px-4 d-flex justify-content-between" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                <div class="small rajdhani font-weight-bold" style="color: #475569; font-size: 13px;"><i class="fas fa-shield-alt text-success mr-1"></i> RDWIS FINANCIAL AUDIT ENGINE ACTIVE</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm rajdhani font-weight-bold px-4" data-dismiss="modal" style="font-size: 13px;">CLOSE REPORT</button>
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
    .subhead-list-wrap::-webkit-scrollbar { width: 8px; }
    .subhead-list-wrap::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 6px; }
    .subhead-list-wrap::-webkit-scrollbar-thumb { background: #64748b; border-radius: 6px; }
    .subhead-list-wrap::-webkit-scrollbar-thumb:hover { background: #334155; }
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
    // Tabular breakdown active; charts removed as requested
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
        $empList = ($employees ?? collect())->map(fn($e) => [
            'emp_id' => (string) $e->emp_id,
            'emp_name' => (string) $e->emp_name,
            'emp_rank' => (string) ($e->emp_rank ?? ($e->emp_title ?? 'Staff')),
            'emp_grade' => (string) ($e->emp_scale ?? ($e->emp_grade ?? ''))
        ])->values();
        $subheadList = $projectSubheads ?? [];

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
            'pcs_type' => (string) ($purchase->pcs_type ?: 'Ps'),
            'pcs_subhead' => (string) ($purchase->subhead_display ?: 'Equipment'),
            'pcs_frm_id' => (int) ($purchase->pcs_frm_id ?? 0),
            'vendor_name' => (string) ($purchase->firm?->frm_name ?? ''),
            'pcs_quotetype' => (int) ($purchase->pcs_quotetype ?? 1),
            'pcs_remarks' => (string) ($purchase->pcs_remarks ?? ''),
            'pcs_intprice' => (float) ($purchase->pcs_intprice ?? $initBase),
            'pcs_inttax' => (float) ($purchase->pcs_inttax ?? $initSst),
            'pcs_midprice' => (float) ($purchase->pcs_midprice ?? ($initBase + $initSst)),
            'pcs_midtax' => (float) ($purchase->pcs_midtax ?? $initGst),
            'pcs_price' => (float) ($purchase->pcs_price ?? $initTot),
            'items' => $pcItems->map(fn($i) => [
                'pci_id' => (int) $i->pci_id,
                'pci_serial' => (int) $i->pci_serial,
                'pci_desc' => (string) $i->pci_desc,
                'pci_qty' => (float) $i->pci_qty,
                'pci_qtyunit' => (string) ($i->pci_qtyunit ?: 'num'),
                'pci_price' => (float) ($i->pci_price ?? 0),
                'pci_type' => (int) ($i->pci_type ?? 7),
                'pci_type_name' => $i->type_name,
                'pci_subtype' => (string) ($i->pci_subtype ?? ''),
                'pci_type2' => $i->pci_type2 ? (int) $i->pci_type2 : null,
                'pci_type2_name' => $i->type2_name,
                'pci_subhead' => (string) ($i->pci_subhead ?? ''),
                'pci_emp_id' => (string) ($i->pci_emp_id ?? ''),
                'emp_name' => (string) ($i->employee?->emp_name ?? ''),
                'emp_rank' => (string) ($i->employee?->emp_rank ?? ($i->employee?->emp_title ?? '')),
            ])->values(),
            'quotes' => $pcQuotes->map(function($q) use ($quoteAttachments, $pcQuotes) {
                $att = $quoteAttachments->get($q->qte_id);
                $filePath = $att ? (string) $att->pat_path : null;
                $fileName = $filePath ? basename(str_replace('\\', '/', $filePath)) : null;
                $isBase = ($q->qte_num == 1 || $q->qte_id == $pcQuotes->min('qte_id'));
                $qBase = (float) ($q->qte_intprice ?: $q->qte_price);
                $qSst = (float) ($q->qte_inttax ?? 0);
                $qGst = (float) ($q->qte_midtax ?? 0);
                $qTot = (float) ($q->qte_price ?: ($qBase + $qSst + $qGst));
                return [
                    'qte_id' => (int) $q->qte_id,
                    'qte_num' => (int) ($q->qte_num ?? 0),
                    'firm_name' => (string) ($q->firm?->frm_name ?? $q->qte_firmname),
                    'qte_price' => $qTot,
                    'qte_subtotal' => $qBase,
                    'qte_inttax' => $qSst,
                    'qte_midtax' => $qGst,
                    'qte_tax' => $qSst + $qGst,
                    'tax_type' => $qSst > 0 ? 'SST' : 'GST',
                    'is_base' => (bool) $isBase,
                    'attachment_path' => $filePath,
                    'attachment_name' => $fileName ?? 'Quote Document',
                ];
            })->values(),
            'no_quotes' => $purchase->noQuotes->values()->map(fn($nq) => [
                'nqt_id' => (int) $nq->nqt_id,
                'nqt_frm_id' => (int) $nq->nqt_frm_id,
                'firm_name' => (string) ($nq->firm?->frm_name ?? $nq->nqt_firmname ?? ('Firm #' . $nq->nqt_frm_id)),
                'nqt_reason' => (string) ($nq->nqt_reason ?? 'Quotation Not Received'),
            ])->values(),
            'attachments' => $purchase->attachments->map(fn($a) => [
                'pat_id' => (int) $a->pat_id,
                'pat_path' => (string) $a->pat_path,
                'pat_type' => (string) ($a->pat_type ?: ''),
                'pat_filename' => basename(str_replace('\\', '/', (string)($a->pat_path ?? ''))),
            ])->values(),
            'quote_items' => $quoteItemMap,
        ];
    @endphp

    let state = @json($snapshot);
    const allEmployees = @json($empList);
    const allSubheads = @json($subheadList);
    const isInitiator = @json($isInitiator);
    const isDProc     = @json($isDProc);

    function updateState(json) {
        if (!json) return;
        if (json.data && typeof json.data === 'object') {
            state = json.data;
        } else if (json.pcs_title || json.pcs_id) {
            state = json;
        }
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

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
            const totA = Number(a.qte_price || (Number(a.qte_subtotal || a.qte_intprice || 0) + Number(a.qte_inttax || 0) + Number(a.qte_midtax || 0)));
            const totB = Number(b.qte_price || (Number(b.qte_subtotal || b.qte_intprice || 0) + Number(b.qte_inttax || 0) + Number(b.qte_midtax || 0)));
            return totA - totB;
        });
    }

    function renderItems() {
        if (!state) return;
        const head = document.getElementById('pcItemsHead');
        const body = document.getElementById('pcItemsBody');
        const countBadge = document.getElementById('pcItemCountBadge');
        if (!body) return;

        const items = [...(state.items || [])].sort((a, b) => {
            const sA = a.pci_serial !== undefined && a.pci_serial !== null ? Number(a.pci_serial) : 0;
            const sB = b.pci_serial !== undefined && b.pci_serial !== null ? Number(b.pci_serial) : 0;
            if (sA !== sB && sA > 0 && sB > 0) return sA - sB;
            return (a.pci_id ?? 0) - (b.pci_id ?? 0);
        });
        if (countBadge) countBadge.textContent = items.length;

        const caseType = String(state.pcs_type || 'Ps').trim();
        const isPs = caseType === 'Ps';
        const isPt = caseType === 'Pt';
        const isRb = caseType === 'Rb';

        // 1. Render Table Header based on Case Type
        if (head) {
            if (isRb) {
                head.innerHTML = `
                    <tr style="background: #fffbeb;">
                        <th class="pl-3" style="width: 42px; color: #92400e;">S.No</th>
                        <th style="color: #92400e; width: 220px;">Employee Details</th>
                        <th style="color: #92400e;">Travel Purpose / Details</th>
                        <th class="text-center" style="width: 95px; color: #92400e;">Subhead</th>
                        <th class="text-center" style="width: 70px; color: #92400e;">Days</th>
                        <th class="text-right pr-3" style="width: 130px; color: #92400e;">Total Allowance (PKR)</th>
                        <th class="text-center pr-3" style="width: 120px; color: #92400e;">Payment Mode</th>
                        <th class="text-right pr-3 edit-only" style="width: 70px; color: #92400e;">Action</th>
                    </tr>
                `;
            } else if (isPt) {
                head.innerHTML = `
                    <tr style="background: #f0fdfa;">
                        <th class="pl-3" style="width: 42px; color: #0f766e;">S.No</th>
                        <th style="color: #0f766e;">Description / Specification</th>
                        <th class="text-center" style="width: 85px; color: #0f766e;">Type</th>
                        <th style="width: 120px; color: #0f766e;">Sub-Type</th>
                        <th class="text-center" style="width: 75px; color: #0f766e;">Class</th>
                        <th style="width: 95px; color: #0f766e;">Subhead</th>
                        <th class="text-center" style="width: 50px; color: #0f766e;">Qty</th>
                        <th class="text-center" style="width: 55px; color: #0f766e;">Unit</th>
                        <th class="text-right pr-3" style="width: 110px; color: #0f766e;">Total (PKR)</th>
                        <th class="text-right pr-3 edit-only" style="width: 70px; color: #0f766e;">Action</th>
                    </tr>
                `;
            } else {
                // Ps (Major Purchase with Quotations) - Single Total (PKR) column, no duplicate rate column
                head.innerHTML = `
                    <tr style="background: #f8fafc;">
                        <th class="pl-3" style="width: 42px;">S.No</th>
                        <th>Description / Specification</th>
                        <th class="text-center" style="width: 85px;">Type</th>
                        <th style="width: 120px;">Sub-Type</th>
                        <th class="text-center" style="width: 75px;">Class</th>
                        <th style="width: 95px;">Subhead</th>
                        <th class="text-center" style="width: 50px;">Qty</th>
                        <th class="text-center" style="width: 55px;">Unit</th>
                        <th class="text-right pr-3" style="width: 110px;">Total (PKR)</th>
                        <th class="text-right pr-3 edit-only" style="width: 70px;">Action</th>
                    </tr>
                `;
            }
        }

        // 2. Render Table Body based on Case Type
        if (items.length === 0) {
            const colSpan = isRb ? 8 : 10;
            body.innerHTML = `<tr><td colspan="${colSpan}" class="text-center py-4 text-muted small">No items added to this case yet.</td></tr>`;
            return;
        }

        body.innerHTML = items.map((it, idx) => {
            const sNo = idx + 1;
            const qty = Number(it.pci_qty || 1);
            const rate = Number(it.pci_price || 0);
            const baseTotal = qty * rate;

            if (isRb) {
                const totalAllowance = baseTotal;
                return `
                    <tr data-pci-id="${it.pci_id}">
                        <td class="pl-3 text-muted font-weight-bold" style="font-size: 11px;">${sNo}</td>
                        <td>
                            <div class="font-weight-bold text-dark" style="font-size: 11.5px; color: #0f172a !important;">
                                <i class="fas fa-user-circle text-primary mr-1"></i> ${escapeHtml(it.emp_name || it.pci_emp_id || 'Staff Member')}
                            </div>
                            <div class="small text-muted" style="font-size: 10px;">
                                ${escapeHtml(it.emp_rank || 'Employee')} ${it.pci_emp_id ? `| ID: #${escapeHtml(it.pci_emp_id)}` : ''}
                            </div>
                        </td>
                        <td>
                            <span class="pc-desc-display font-weight-500" style="color: #1e293b; font-size: 11.5px;">${escapeHtml(it.pci_desc || 'TA/DA Allowance')}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light border text-dark" style="font-size: 10px; padding: 2px 5px;">${escapeHtml(it.pci_subhead || state.pcs_subhead || 'Misc')}</span>
                        </td>
                        <td class="text-center font-weight-bold">
                            <span class="badge px-2 py-0.5" style="font-size: 11px; background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1;">${fmt(qty)} ${it.pci_qtyunit && it.pci_qtyunit !== 'num' ? escapeHtml(it.pci_qtyunit) : (qty > 1 ? 'Days' : 'Day')}</span>
                        </td>
                        <td class="text-right pr-3 font-weight-bold text-success" style="font-size: 12px; color: #16a34a !important;">
                            ${fmt(totalAllowance)}
                            ${qty > 1 ? `<div class="text-muted font-weight-normal" style="font-size: 9.5px;">(@ ${fmt(rate)}/day)</div>` : ''}
                        </td>
                        <td class="text-center pr-3">
                            <span class="badge badge-warning px-1.5 py-0.5 text-dark" style="background: #fef3c7; border: 1px solid #fde68a; font-size: 9.5px;">
                                <i class="fas fa-money-check mr-1 text-warning"></i> Meezan Bank
                            </span>
                        </td>
                        <td class="text-right pr-3 edit-only">
                            <div class="d-flex justify-content-end gap-1">
                                <button type="button" class="btn btn-outline-warning btn-xs pc-item-edit-btn" data-pci-id="${it.pci_id}" title="Edit Line" style="padding: 2px 5px; font-size: 10px;"><i class="fas fa-pencil-alt"></i></button>
                                <button type="button" class="btn btn-outline-danger btn-xs pc-item-del-btn" data-pci-id="${it.pci_id}" title="Delete" style="padding: 2px 5px; font-size: 10px;"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            }

            // Type badge (7: Permanent, 2: Consumable, 3: Service)
            const typeNum = Number(it.pci_type || 7);
            let typeBadge = '<span class="badge badge-primary px-1.5 py-0.5" style="font-size: 9.5px; background: #2563eb;">Permanent</span>';
            if (typeNum === 2) typeBadge = '<span class="badge badge-warning px-1.5 py-0.5" style="font-size: 9.5px; background: #ea580c; color:#fff;">Consumable</span>';
            else if (typeNum === 3) typeBadge = '<span class="badge badge-info px-1.5 py-0.5" style="font-size: 9.5px; background: #0891b2; color:#fff;">Service</span>';

            // Class badge (6: Asset, 5: Inventory)
            const type2Num = Number(it.pci_type2 || 6);
            let classBadge = '<span class="badge badge-success px-1.5 py-0.5" style="font-size: 9.5px; background: #16a34a;">Asset</span>';
            if (type2Num === 5) classBadge = '<span class="badge badge-secondary px-1.5 py-0.5" style="font-size: 9.5px; background: #64748b;">Inventory</span>';

            const subheadVal = it.pci_subhead || state.pcs_subhead || (isPt ? 'Direct' : 'Equipment');
            const subtypeVal = it.pci_subtype || '—';

            if (isPt) {
                // Pt (Incidental Expenditure)
                return `
                    <tr data-pci-id="${it.pci_id}">
                        <td class="pl-3 text-muted font-weight-bold" style="font-size: 11px;">${sNo}</td>
                        <td>
                            <span class="pc-desc-display font-weight-600 text-dark" style="color: #0f172a; font-size: 11.5px; line-height: 1.25; display: inline-block;">${escapeHtml(it.pci_desc)}</span>
                        </td>
                        <td class="text-center">${typeBadge}</td>
                        <td><span class="text-dark font-weight-500" style="font-size: 11px;">${escapeHtml(subtypeVal)}</span></td>
                        <td class="text-center">${classBadge}</td>
                        <td><span class="badge badge-light border text-dark" style="font-size: 10px; padding: 2px 5px;">${escapeHtml(subheadVal)}</span></td>
                        <td class="text-center font-weight-bold"><span class="pc-qty-display" style="color: #0f172a; font-size: 11.5px;">${fmt(qty)}</span></td>
                        <td class="text-center"><span class="small text-muted pc-unit-display" style="font-size: 10.5px;">${escapeHtml(it.pci_qtyunit || 'num')}</span></td>
                        <td class="text-right pr-3 font-weight-bold text-dark" style="color: #0f172a !important; font-size: 11.5px;">
                            ${fmt(baseTotal)}
                            ${qty > 1 ? `<div class="text-muted font-weight-normal" style="font-size: 9.5px;">(@ ${fmt(rate)})</div>` : ''}
                        </td>
                        <td class="text-right pr-3 edit-only">
                            <div class="d-flex justify-content-end gap-1">
                                <button type="button" class="btn btn-outline-warning btn-xs pc-item-edit-btn" data-pci-id="${it.pci_id}" title="Edit Item" style="padding: 2px 5px; font-size: 10px;"><i class="fas fa-pencil-alt"></i></button>
                                <button type="button" class="btn btn-outline-danger btn-xs pc-item-del-btn" data-pci-id="${it.pci_id}" title="Delete" style="padding: 2px 5px; font-size: 10px;"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
            }

            // Ps (Major Purchase with Quotations) - Single Total (PKR) column
            return `
                <tr data-pci-id="${it.pci_id}">
                    <td class="pl-3 text-muted font-weight-bold" style="font-size: 11px;">${sNo}</td>
                    <td>
                        <span class="pc-desc-display font-weight-600 text-dark" style="color: #0f172a; font-size: 11.5px; line-height: 1.25; display: inline-block;">${escapeHtml(it.pci_desc)}</span>
                    </td>
                    <td class="text-center">${typeBadge}</td>
                    <td><span class="text-dark font-weight-500" style="font-size: 11px;">${escapeHtml(subtypeVal)}</span></td>
                    <td class="text-center">${classBadge}</td>
                    <td><span class="badge badge-light border text-dark" style="font-size: 10px; padding: 2px 5px;">${escapeHtml(subheadVal)}</span></td>
                    <td class="text-center font-weight-bold"><span class="pc-qty-display" style="color: #0f172a; font-size: 11.5px;">${fmt(qty)}</span></td>
                    <td class="text-center"><span class="small text-muted pc-unit-display" style="font-size: 10.5px;">${escapeHtml(it.pci_qtyunit || 'num')}</span></td>
                    <td class="text-right pr-3 font-weight-bold text-dark" style="color: #0f172a !important; font-size: 11.5px;">
                        ${fmt(baseTotal)}
                        ${qty > 1 ? `<div class="text-muted font-weight-normal" style="font-size: 9.5px;">(@ ${fmt(rate)})</div>` : ''}
                    </td>
                    <td class="text-right pr-3 edit-only">
                        <div class="d-flex justify-content-end gap-1">
                            <button type="button" class="btn btn-outline-warning btn-xs pc-item-edit-btn" data-pci-id="${it.pci_id}" title="Edit Item" style="padding: 2px 5px; font-size: 10px;"><i class="fas fa-pencil-alt"></i></button>
                            <button type="button" class="btn btn-outline-danger btn-xs pc-item-del-btn" data-pci-id="${it.pci_id}" title="Delete" style="padding: 2px 5px; font-size: 10px;"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function renderNoQuotes() {
        if (!state) return;
        const body = document.getElementById('pcNoQuotesBody');
        const badge = document.getElementById('pcNoQuotesBadge');
        if (!body) return;
        const noQuotes = state.no_quotes || [];
        if (badge) badge.textContent = noQuotes.length;

        if (noQuotes.length === 0) {
            body.innerHTML = `<tr><td colspan="4" class="text-center py-3 text-muted small">No firms marked as 'Not Received' / Regret.</td></tr>`;
            return;
        }

        body.innerHTML = noQuotes.map((nq, idx) => `
            <tr data-nqt-id="${nq.nqt_id}">
                <td class="pl-3 text-muted font-weight-bold" style="width: 50px;">${idx + 1}</td>
                <td class="font-weight-bold text-dark" style="color: #0f172a !important;">
                    <i class="fas fa-building text-danger mr-1.5"></i> ${escapeHtml(nq.firm_name)}
                </td>
                <td class="text-center">
                    <span class="badge badge-danger px-2.5 py-1" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-size: 11px;">
                        <i class="fas fa-times-circle mr-1"></i> Quotation Not Received
                    </span>
                </td>
                <td class="text-right pr-3 edit-only">
                    <button type="button" class="btn btn-outline-danger btn-xs pc-del-noquote-btn" data-nqt-id="${nq.nqt_id}" title="Remove from No-Quotes" style="padding: 2px 6px;">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function renderQuotes() {
        if (!state) return;
        const body = document.getElementById('pcQuotesBody');
        if (!body) return;
        const sorted = sortQuotesByPrice(state.quotes || []);
        if (sorted.length === 0) {
            body.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted small">No quotations added yet.</td></tr>`;
            return;
        }

        body.innerHTML = sorted.map((q, idx) => {
            const isWinner = idx === 0;
            const isBaseQuote = q.is_base === true;
            const trStyle = isWinner ? 'background: #f0fdf4 !important; border-left: 3px solid #16a34a;' : '';
            const td1 = isWinner ? 'text-success font-weight-bold' : 'text-muted';
            const td2 = isWinner ? 'text-success font-weight-bold' : 'text-dark font-weight-bold';
            const td3 = isWinner ? 'text-success font-weight-bold' : 'text-primary font-weight-bold';

            const bPrice = Number(q.qte_subtotal || q.qte_intprice || (Number(q.qte_price || 0) - Number(q.qte_inttax || 0) - Number(q.qte_midtax || 0)));
            const sstAmt = Number(q.qte_inttax || 0);
            const gstAmt = Number(q.qte_midtax || (q.qte_tax && !sstAmt ? q.qte_tax : 0));
            const taxAmt = sstAmt + gstAmt;
            const quoteTotal = Number(q.qte_price || (bPrice + taxAmt));

            return `
                <tr style="${trStyle}">
                    <td class="pl-3 ${td1}">${idx + 1}</td>
                    <td class="${td2}" style="color: ${isWinner ? '#166534' : '#0f172a'} !important;">
                        ${(q.firm_name ?? '').replaceAll('<', '&lt;').replaceAll('>', '&gt;')}
                        ${isBaseQuote ? '<span class="text-muted small font-weight-bold ml-1" style="font-size:10px; color: var(--rd-text3) !important;">(Base Quote)</span>' : ''}
                    </td>
                    <td class="text-right pr-3 font-weight-bold ${td3}">
                        <div style="color: ${isWinner ? '#166534' : '#0f172a'} !important;">Rs. ${fmt(quoteTotal)}</div>
                        ${bPrice > 0 && taxAmt > 0 ? `<div style="font-size:10px; font-weight:normal; color: var(--rd-text3);">Base: ${fmt(bPrice)} | ${sstAmt > 0 ? `SST: ${fmt(sstAmt)} ` : ''}${gstAmt > 0 ? `GST: ${fmt(gstAmt)}` : ''}</div>` : ''}
                    </td>
                    <td class="text-center">
                        ${q.attachment_path ? `
                            <button type="button" class="btn btn-xs btn-outline-primary pc-live-view-quote-btn" data-url="${quoteViewBase}/${q.qte_id}/view" data-qte-id="${q.qte_id}" data-ext="${(q.attachment_path || q.attachment_name || '').split('.').pop().toLowerCase()}" data-file-path="${q.attachment_path || ''}" data-file-name="${(q.attachment_name || q.attachment_path || '').replaceAll('"','&quot;')}" data-title="${(q.firm_name||'').replaceAll('"','&quot;')}" style="width: 28px; height: 26px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; font-size: 11px;" title="View Quotation Document">
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
        if (!state) return;
        const el = document.getElementById('pcRemarksText');
        const inp = document.getElementById('pcRemarksInput');
        if (inp) inp.value = state.pcs_remarks || '';
        if (!el) return;
        const txt = (state.pcs_remarks || '').trim();
        el.innerHTML = txt ? txt.replaceAll('<', '&lt;').replaceAll('>', '&gt;') : `<span class="opacity-50 italic">No terms & conditions provided during case initiation.</span>`;
    }

    function resolveAttName(f) {
        if (f.pat_type && f.pat_type.trim() && f.pat_type.trim().toLowerCase() !== 'attachment') {
            return f.pat_type.trim();
        }
        const fn = (f.pat_filename || '').toLowerCase();
        if (fn.startsWith('frm-')) return 'Form';
        if (fn.startsWith('min-')) return 'Minute';
        if (fn.startsWith('san-')) return 'Sanction';
        if (fn.startsWith('fs-')) return 'Financial Status';
        if (fn.startsWith('app-')) return 'Approval';
        if (fn.startsWith('aip-')) return 'Approval in Principal';
        if (fn.startsWith('mrr-')) return 'Market Research Report';
        if (fn.startsWith('wo-')) return 'Work Order';
        if (fn.startsWith('pcs-')) return 'Form';
        return f.pat_type || f.pat_filename || 'Attachment';
    }

    function renderFiles() {
        if (!state) return;
        const wrapEl = document.getElementById('pcCaseAttachmentsList');
        const countBadge = document.getElementById('pcCaseAttCountBadge');
        const files = state.attachments || [];
        if (countBadge) countBadge.textContent = files.length;
        if (!wrapEl) return;
        if (!files.length) {
            wrapEl.innerHTML = `<div class="text-center py-3 text-muted" style="font-size: 11px;"><i class="fas fa-folder-open text-muted mr-1"></i> No case attachments uploaded yet.</div>`;
            return;
        }
        wrapEl.innerHTML = files.map((f, idx) => {
            const name = resolveAttName(f);
            const ext = ((f.pat_path || f.pat_filename || '').split('.').pop() || '').toLowerCase();
            let iconClass = 'far fa-file-alt text-secondary';
            if (ext === 'pdf') iconClass = 'far fa-file-pdf text-danger';
            else if (['doc', 'docx'].includes(ext)) iconClass = 'far fa-file-word text-primary';
            else if (['xls', 'xlsx'].includes(ext)) iconClass = 'far fa-file-excel text-success';
            else if (['png', 'jpg', 'jpeg'].includes(ext)) iconClass = 'far fa-file-image text-info';

            return `
                <div class="d-flex justify-content-between align-items-center py-1.5 ${idx < files.length - 1 ? 'border-bottom' : ''}" style="border-color: #f1f5f9 !important;">
                    <div class="d-flex align-items-center overflow-hidden mr-2" style="flex: 1; min-width: 0; gap: 6px;">
                        <span class="text-muted font-weight-bold flex-shrink-0" style="font-size: 10px; width: 16px;">${idx + 1}.</span>
                        <i class="${iconClass} flex-shrink-0" style="font-size: 12px;"></i>
                        <span class="text-truncate font-weight-bold text-dark" style="font-size: 11.5px;" title="${name.replaceAll('"', '&quot;')}">
                            ${name.replaceAll('<', '&lt;').replaceAll('>', '&gt;')}
                        </span>
                    </div>
                    <div class="d-flex align-items-center flex-shrink-0" style="gap: 4px;">
                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 pc-live-view-quote-btn hover-zoom font-weight-bold" data-url="${quoteViewBase}/${f.pat_id}/view" data-pat-id="${f.pat_id}" data-ext="${ext}" data-file-path="${f.pat_path || ''}" data-file-name="${(f.pat_filename || f.pat_path || '').replaceAll('"','&quot;')}" data-title="${name.replaceAll('"', '&quot;')}" style="font-size: 11px; height: 22px; border-radius: 4px;" title="View ${name.replaceAll('"', '&quot;')}">
                            <i class="fas fa-eye mr-1"></i> View
                        </button>
                    </div>
                </div>
            `;
        }).join('');
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
                        <label class="badge ${v.file || v.existing_file_path ? 'badge-success' : 'badge-dark'} p-1 mb-0" style="cursor:pointer; font-size:9px; font-weight:normal; border: 1px solid var(--rd-border);" title="${v.file ? v.file.name : (v.existing_file_name || 'Attach Attested Quote Document')}">
                            <i class="fas fa-paperclip mr-1"></i>
                            <span>${v.file ? (v.file.name.length > 12 ? v.file.name.substring(0,10)+'..' : v.file.name) : (v.existing_file_name ? (v.existing_file_name.length > 12 ? v.existing_file_name.substring(0,10)+'..' : v.existing_file_name) : 'Attach Attested Quote')}</span>
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
        const taxMode = document.getElementById('pcGlobalTaxMode')?.value || 'exclusive';
        const taxType = document.getElementById('pcGlobalTaxType')?.value || 'GST';
        const taxPercent = parseFloat(document.getElementById('pcGlobalTaxPercent')?.value || 0);

        const vendorCalcs = modalVendors.map((v) => {
            let rawSum = 0;
            items.forEach(it => {
                const p = parseFloat(v.prices[it.pci_id] || 0);
                const q = parseFloat(it.pci_qty || 1);
                rawSum += (p * q);
            });

            let sub = rawSum;
            let tax = 0;
            let total = rawSum;

            if (taxMode === 'inclusive' && taxPercent > 0) {
                total = rawSum;
                sub = total / (1 + (taxPercent / 100));
                tax = total - sub;
            } else {
                sub = rawSum;
                tax = sub * (taxPercent / 100);
                total = sub + tax;
            }
            return { sub, tax, total, rawSum };
        });

        const minTotal = Math.min(...vendorCalcs.map(c => c.total).filter(t => t > 0));

        foot.innerHTML = `
            <tr style="background: #f8fafc;">
                <td class="pc-item-sticky" style="text-align:right; padding: 10px 14px; background: #f8fafc !important; border-right: 1.5px solid #cbd5e1 !important;">
                    <div style="font-size:11px; font-weight:700; color:#64748b; letter-spacing:0.5px; text-transform:uppercase;">BASE SUB TOTAL</div>
                    <div style="font-size:10.5px; font-weight:700; color:#64748b; letter-spacing:0.5px; margin-top:3px; text-transform:uppercase;">TAX (${taxType} ${taxPercent}% ${taxMode === 'inclusive' ? 'INCL' : 'EXCL'})</div>
                    <div style="font-size:13.5px; color:#0f172a; font-weight:800; margin-top:4px; letter-spacing:0.5px;">TOTAL COST (PKR)</div>
                </td>
                ${vendorCalcs.map((c, idx) => {
                    const isWinner = c.total > 0 && c.total === minTotal;
                    return `
                        <td style="width: 260px; min-width: 260px; text-align:center; padding:10px 8px; ${isWinner ? 'background:#ecfdf5 !important; border-top: 2px solid #16a34a !important;' : 'background:#f8fafc !important;'}">
                            <div style="font-size:12px; font-weight:700; color:#334155;">${fmt(c.sub)}</div>
                            <div style="font-size:11px; font-weight:600; color:#64748b; margin-top:3px;">${fmt(c.tax)}</div>
                            <div style="font-size:15px; font-weight:800; margin-top:4px; color:${isWinner ? '#16a34a' : '#0f172a'};">
                                ${fmt(c.total)}${isWinner ? ' <i class="fas fa-trophy ml-1 text-success" style="font-size:12px;"></i>' : ''}
                            </div>
                        </td>
                    `;
                }).join('')}
            </tr>
        `;
    }



    function renderTitle() {
        if (!state) return;
        const view = document.getElementById('pcTitleView');
        const input = document.querySelector('#pcTitleForm input[name="pcs_title"]');
        if (view) view.textContent = state.pcs_title || '';
        if (input) input.value = state.pcs_title || '';
    }

    function renderPriceBreakdown() {
        if (!state) return;
        const quotes = state.quotes || [];
        const sorted = sortQuotesByPrice(quotes);
        let basePrice = parseFloat(state.pcs_intprice || 0);
        let sstAmount = parseFloat(state.pcs_inttax || 0);
        let gstAmount = parseFloat(state.pcs_midtax || 0);
        let totalPrice = parseFloat(state.pcs_price || 0);

        if (totalPrice <= 0 && basePrice <= 0) {
            if (sorted.length > 0) {
                const winner = sorted[0];
                totalPrice = parseFloat(winner.qte_price || 0);
                sstAmount = parseFloat(winner.qte_inttax || 0);
                gstAmount = parseFloat(winner.qte_midtax || 0);
                basePrice = parseFloat(winner.qte_subtotal || winner.qte_intprice || 0);
                if (basePrice <= 0 && totalPrice > 0) {
                    basePrice = Math.max(0, totalPrice - sstAmount - gstAmount);
                }
                if (totalPrice <= 0) {
                    totalPrice = basePrice + sstAmount + gstAmount;
                }
            } else {
                const items = state.items || [];
                basePrice = items.reduce((acc, it) => acc + (parseFloat(it.pci_qty || 1) * parseFloat(it.pci_price || 0)), 0);
                sstAmount = parseFloat(state.pcs_inttax || 0);
                gstAmount = parseFloat(state.pcs_midtax || 0);
                totalPrice = (parseFloat(state.pcs_price || 0) > 0) ? parseFloat(state.pcs_price) : (basePrice + sstAmount + gstAmount);
            }
        } else {
            if (basePrice <= 0 && totalPrice > 0) {
                basePrice = Math.max(0, totalPrice - sstAmount - gstAmount);
            }
            if (totalPrice <= 0 && basePrice > 0) {
                totalPrice = basePrice + sstAmount + gstAmount;
            }
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
                                        <button type="button" class="btn btn-xs btn-outline-info pc-live-view-quote-btn" data-url="${quoteViewBase}/${q.qte_id}/view" data-qte-id="${q.qte_id}" data-ext="${(q.attachment_path || q.attachment_name || '').split('.').pop().toLowerCase()}" data-file-path="${q.attachment_path || ''}" data-file-name="${(q.attachment_name || q.attachment_path || '').replaceAll('"','&quot;')}" data-title="${(q.firm_name||'').replaceAll('"','&quot;')}" style="font-size: 8px; padding: 1px 6px;">
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

        const subTotals = quotes.map((q) => fmt(q.qte_subtotal || q.qte_intprice || q.qte_price));
        const quoteTaxes = quotes.map((q) => fmt((q.qte_inttax || 0) + (q.qte_midtax || 0) || (q.qte_tax || 0)));
        const totals = quotes.map((q) => fmt(q.qte_price));
        const foot = `
                </tbody>
                <tfoot style="border-top: 2px solid var(--rd-accent);">
                    <tr style="background: var(--rd-neutral-50);">
                        <td colspan="3" class="cs-sticky-1-3 text-right pr-4 text-muted small" style="background: var(--rd-neutral-200) !important; font-weight: 700;">
                            BASE / SUB TOTAL (PKR)
                        </td>
                        ${subTotals.map((st) => `
                            <td class="text-center py-2 text-dark font-weight-bold" style="border-right: 1px solid rgba(255,255,255,0.05); background: var(--rd-neutral-200) !important; font-size: 13px;">
                                ${st}
                            </td>
                        `).join('')}
                    </tr>
                    <tr style="background: var(--rd-neutral-50);">
                        <td colspan="3" class="cs-sticky-1-3 text-right pr-4 text-muted small" style="background: var(--rd-neutral-200) !important; font-weight: 700;">
                            TAX AMOUNT (SST / GST)
                        </td>
                        ${quoteTaxes.map((tx) => `
                            <td class="text-center py-2 text-muted" style="border-right: 1px solid rgba(255,255,255,0.05); background: var(--rd-neutral-200) !important; font-size: 12px;">
                                ${tx}
                            </td>
                        `).join('')}
                    </tr>
                    <tr style="background: var(--rd-neutral-50); border-top: 1px solid #cbd5e1;">
                        <td colspan="3" class="cs-sticky-1-3 text-right pr-4 text-accent-clean" style="font-size: 14px; background: var(--rd-neutral-200) !important; font-weight: 800;">
                            GRAND TOTAL (PKR)
                        </td>
                        ${totals.map((t, idx) => `
                            <td class="text-center py-3 ${idx === 0 ? 'col-l1' : ''}" style="border-right: 1px solid rgba(255,255,255,0.05); background: var(--rd-neutral-200) !important;">
                                <div class="rajdhani ${idx === 0 ? 'text-success' : 'text-dark'}" style="font-size: 20px; font-weight: 800;">
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

    function renderAll() {
        if (!state) return;
        renderTitle();
        renderItems();
        renderQuotes();
        renderNoQuotes();
        renderRemarks();
        renderFiles();
        renderPriceBreakdown();
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
        if (state.pcs_type === 'Rb') {
            document.getElementById('pcAddEmpSelect')?.focus();
        } else {
            document.getElementById('pcItemDesc')?.focus();
        }
    });

    itemCancelBtn?.addEventListener('click', function() {
        if (!inlineEditor) return;
        inlineEditor.style.display = 'none';
    });

    // Auto-fetch TA/DA details when employee is selected in Add Item form
    $('#pcAddEmpSelect').on('change', async function() {
        const empId = this.value;
        if (!empId) return;
        try {
            const res = await fetch(`/purchase/tada/employee-details/${empId}`);
            if (res.ok) {
                const tada = await res.json();
                const rateInput = document.getElementById('pcAddEmpRate');
                const descInput = document.getElementById('pcAddEmpDesc');
                if (rateInput && tada.tada_amount) rateInput.value = tada.tada_amount;
                if (descInput && tada.description) descInput.value = tada.description;
            }
        } catch (e) {
            console.warn("Could not fetch TA/DA details:", e);
        }
    });

    itemForm?.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!ensureEditing()) return;
        const fd = new FormData();
        fd.append('op', 'add_item');
        fd.append('_token', @json(csrf_token()));

        if (state.pcs_type === 'Rb') {
            const empId = document.getElementById('pcAddEmpSelect')?.value || '';
            const desc = document.getElementById('pcAddEmpDesc')?.value || '';
            const qty = document.getElementById('pcAddEmpQty')?.value || '1';
            const price = document.getElementById('pcAddEmpRate')?.value || '0';

            if (!empId) { toast('Please select an employee'); return; }

            fd.append('emp_id', empId);
            fd.append('item_desc', desc.trim() || 'TA/DA Official Visit');
            fd.append('item_qty', qty);
            fd.append('item_price', price);
            fd.append('item_qtyunit', 'Days');
            fd.append('item_subhead', 'Misc');
            fd.append('item_type', '3');
            fd.append('item_subtype', 'Travelling/Boarding/Lodging');
        } else {
            const desc = itemForm.querySelector('[name="item_desc"]')?.value || '';
            const qty = itemForm.querySelector('[name="item_qty"]')?.value || '1';
            const unit = itemForm.querySelector('[name="item_qtyunit"]')?.value || 'num';
            const price = itemForm.querySelector('[name="item_price"]')?.value || '0';
            const type = itemForm.querySelector('[name="item_type"]')?.value || '7';
            const subtype = itemForm.querySelector('[name="item_subtype"]')?.value || '';
            const type2 = itemForm.querySelector('[name="item_type2"]')?.value || '6';
            const subhead = itemForm.querySelector('[name="item_subhead"]')?.value || '';

            if (!desc.trim()) { toast('Description cannot be empty'); return; }

            fd.append('item_desc', desc.trim());
            fd.append('item_qty', qty);
            fd.append('item_qtyunit', unit);
            fd.append('item_price', price);
            fd.append('item_type', type);
            fd.append('item_subtype', subtype);
            fd.append('item_type2', type2);
            fd.append('item_subhead', subhead);
        }

        try {
            const json = await postForm(fd);
            updateState(json);
            renderAll();
            toast(json.message || 'Item added successfully');
            itemForm.reset();
            if (inlineEditor) inlineEditor.style.display = 'none';
        } catch (err) {
            toast(err.message || 'Error adding item');
        }
    });

    document.getElementById('pcItemsBody')?.addEventListener('click', async function(e) {
        const editBtn = e.target.closest('.pc-item-edit-btn');
        if (editBtn) {
            if (!ensureEditing()) return;
            const tr = editBtn.closest('tr');
            const pciId = editBtn.getAttribute('data-pci-id');
            const it = (state.items || []).find(i => String(i.pci_id) === String(pciId));
            if (!it) return;

            const isRb = state.pcs_type === 'Rb';
            const isPt = state.pcs_type === 'Pt';
            const isPs = !isRb && !isPt;

            if (isRb) {
                tr.innerHTML = `
                    <td class="pl-3 text-warning font-weight-bold" style="font-size: 11px;"><i class="fas fa-pencil-alt"></i></td>
                    <td>
                        <div class="small font-weight-bold text-muted mb-0.5" style="font-size: 10px;">Select Employee:</div>
                        <select class="form-control form-control-sm pc-inline-emp-select mb-1" style="font-size: 11px; height: 26px; padding: 1px 4px; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1;">
                            <option value="">-- Select Employee --</option>
                            ${allEmployees.map(emp => `
                                <option value="${emp.emp_id}" ${String(emp.emp_id) === String(it.pci_emp_id) ? 'selected' : ''}>
                                    ${escapeHtml(emp.emp_name)} (${escapeHtml(emp.emp_id)}) - ${escapeHtml(emp.emp_rank)}
                                </option>
                            `).join('')}
                        </select>
                    </td>
                    <td>
                        <div class="small font-weight-bold text-muted mb-0.5" style="font-size: 10px;">Travel Purpose / Details:</div>
                        <input type="text" class="form-control form-control-sm pc-inline-desc-input" value="${escapeHtml(it.pci_desc)}" style="font-size: 11px; height: 26px; padding: 2px 6px; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1;" required placeholder="Purpose...">
                    </td>
                    <td class="text-center">
                        <div class="small font-weight-bold text-muted mb-0.5" style="font-size: 10px;">Subhead:</div>
                        <input type="text" class="form-control form-control-sm pc-inline-subhead-input text-center" value="${escapeHtml(it.pci_subhead || state.pcs_subhead || 'Misc')}" style="font-size: 10.5px; height: 26px; padding: 2px 4px; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1;">
                    </td>
                    <td class="text-center">
                        <div class="small font-weight-bold text-muted mb-0.5" style="font-size: 10px;">Days:</div>
                        <input type="number" step="1" class="form-control form-control-sm pc-inline-qty-input text-center" value="${it.pci_qty || 1}" style="font-size: 11px; height: 26px; width: 55px; margin: 0 auto; padding: 2px 4px; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1;" required>
                    </td>
                    <td class="text-right pr-3">
                        <div class="small font-weight-bold text-muted mb-0.5" style="font-size: 10px;">Daily Rate (PKR):</div>
                        <input type="number" step="0.01" class="form-control form-control-sm pc-inline-price-input text-right font-weight-bold" value="${it.pci_price || 0}" style="font-size: 11px; height: 26px; width: 90px; margin-left: auto; padding: 2px 4px; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1;" required>
                    </td>
                    <td class="text-center pr-3">
                        <span class="badge badge-warning px-1.5 py-0.5 text-dark" style="background: #fef3c7; border: 1px solid #fde68a; font-size: 9.5px;">Meezan Bank</span>
                    </td>
                    <td class="text-right pr-3">
                        <div class="d-flex justify-content-end gap-1">
                            <button type="button" class="btn btn-success btn-xs pc-inline-save-btn" data-pci-id="${it.pci_id}" title="Save" style="padding: 2px 6px; font-size: 10px;"><i class="fas fa-check"></i></button>
                            <button type="button" class="btn btn-secondary btn-xs pc-inline-cancel-btn" title="Cancel" style="padding: 2px 6px; font-size: 10px;"><i class="fas fa-times"></i></button>
                        </div>
                    </td>
                `;

                // Handle employee selection change inside this row to auto-update rate & desc
                tr.querySelector('.pc-inline-emp-select')?.addEventListener('change', async function() {
                    const empId = this.value;
                    if (!empId) return;
                    try {
                        const res = await fetch(`/purchase/tada/employee-details/${empId}`);
                        if (res.ok) {
                            const tada = await res.json();
                            const rateInput = tr.querySelector('.pc-inline-price-input');
                            const descInput = tr.querySelector('.pc-inline-desc-input');
                            if (rateInput && tada.tada_amount) rateInput.value = tada.tada_amount;
                            if (descInput && tada.description) descInput.value = tada.description;
                        }
                    } catch (err) {}
                });
                return;
            }

            // For Ps and Pt cases:
            tr.innerHTML = `
                <td class="pl-3 text-warning font-weight-bold" style="font-size: 11px;"><i class="fas fa-pencil-alt"></i></td>
                <td>
                    <input type="text" class="form-control form-control-sm pc-inline-desc-input" value="${escapeHtml(it.pci_desc)}" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; font-size: 11px; height: 26px; min-width: 140px; padding: 2px 6px;" required>
                </td>
                <td class="text-center">
                    <select class="form-control form-control-sm pc-inline-type-select" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; font-size: 10.5px; height: 26px; padding: 1px 4px;">
                        <option value="7" ${Number(it.pci_type || 7) === 7 ? 'selected' : ''}>Permanent</option>
                        <option value="2" ${Number(it.pci_type) === 2 ? 'selected' : ''}>Consumable</option>
                        <option value="3" ${Number(it.pci_type) === 3 ? 'selected' : ''}>Service</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm pc-inline-subtype-input" value="${escapeHtml(it.pci_subtype)}" placeholder="Subtype..." style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; font-size: 10.5px; height: 26px; padding: 2px 6px;">
                </td>
                <td class="text-center">
                    <select class="form-control form-control-sm pc-inline-type2-select" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; font-size: 10.5px; height: 26px; padding: 1px 4px;">
                        <option value="6" ${Number(it.pci_type2 || 6) === 6 ? 'selected' : ''}>Asset</option>
                        <option value="5" ${Number(it.pci_type2) === 5 ? 'selected' : ''}>Inventory</option>
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm pc-inline-subhead-input" value="${escapeHtml(it.pci_subhead || state.pcs_subhead)}" list="subheadOptionsList" style="background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; font-size: 10.5px; height: 26px; padding: 2px 6px;">
                </td>
                <td class="text-center">
                    <input type="number" step="0.01" class="form-control form-control-sm pc-inline-qty-input text-center" value="${it.pci_qty}" style="width: 52px; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; font-size: 11px; height: 26px; margin: 0 auto; padding: 2px 4px;" required>
                </td>
                <td class="text-center">
                    <input type="text" class="form-control form-control-sm pc-inline-unit-input text-center" value="${escapeHtml(it.pci_qtyunit || 'num')}" style="width: 46px; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; font-size: 10.5px; height: 26px; margin: 0 auto; padding: 2px 4px;">
                </td>
                <td class="text-right pr-3">
                    <input type="number" step="0.01" class="form-control form-control-sm pc-inline-price-input text-right font-weight-bold" value="${it.pci_price || 0}" style="width: 85px; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; font-size: 11px; height: 26px; margin-left: auto; padding: 2px 4px;" placeholder="Price (PKR)">
                </td>
                <td class="text-right pr-3">
                    <div class="d-flex justify-content-end gap-1">
                        <button type="button" class="btn btn-success btn-xs pc-inline-save-btn" data-pci-id="${pciId}" title="Save Changes" style="padding: 2px 6px; font-size: 10px;"><i class="fas fa-check"></i></button>
                        <button type="button" class="btn btn-secondary btn-xs pc-inline-cancel-btn" title="Cancel" style="padding: 2px 6px; font-size: 10px;"><i class="fas fa-times"></i></button>
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

            const fd = new FormData();
            fd.append('op', 'edit_item');
            fd.append('pci_id', pciId);
            fd.append('_token', @json(csrf_token()));

            if (state.pcs_type === 'Rb') {
                const empId = tr.querySelector('.pc-inline-emp-select')?.value || '';
                const desc = tr.querySelector('.pc-inline-desc-input')?.value || '';
                const subhead = tr.querySelector('.pc-inline-subhead-input')?.value || 'Misc';
                const qty = tr.querySelector('.pc-inline-qty-input')?.value || '1';
                const price = tr.querySelector('.pc-inline-price-input')?.value || '0';

                fd.append('emp_id', empId);
                fd.append('item_desc', desc.trim() || 'TA/DA Allowance');
                fd.append('item_subhead', subhead);
                fd.append('item_qty', qty);
                fd.append('item_price', price);
                fd.append('item_qtyunit', 'Days');
                fd.append('item_type', '3');
                fd.append('item_subtype', 'Travelling/Boarding/Lodging');
            } else {
                const desc = tr.querySelector('.pc-inline-desc-input')?.value || '';
                const type = tr.querySelector('.pc-inline-type-select')?.value || '7';
                const subtype = tr.querySelector('.pc-inline-subtype-input')?.value || '';
                const type2 = tr.querySelector('.pc-inline-type2-select')?.value || '6';
                const subhead = tr.querySelector('.pc-inline-subhead-input')?.value || '';
                const qty = tr.querySelector('.pc-inline-qty-input')?.value || '1';
                const unit = tr.querySelector('.pc-inline-unit-input')?.value || 'num';
                const price = tr.querySelector('.pc-inline-price-input')?.value || '0';

                if (!desc.trim()) { toast('Description cannot be empty'); return; }

                fd.append('item_desc', desc.trim());
                fd.append('item_type', type);
                fd.append('item_subtype', subtype);
                fd.append('item_type2', type2);
                fd.append('item_subhead', subhead);
                fd.append('item_qty', qty);
                fd.append('item_qtyunit', unit);
                fd.append('item_price', price);
            }

            try {
                const json = await postForm(fd);
                updateState(json);
                renderAll();
                toast(json.message || 'Item updated successfully');
            } catch (err) {
                console.error("Error updating item:", err);
                toast(err.message || 'Error updating item');
                renderItems();
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
            updateState(json);
            renderAll();
            toast(json.message || 'Deleted');
        } catch (err) {
            toast(err.message || 'Error');
        }
    });

    // Quotations Not Received (No-Quotes) Handlers
    $('#pcToggleAddNoQuoteBtn').on('click', function() {
        if (!ensureEditing()) return;
        $('#pcAddNoQuoteInline').slideToggle(200);
    });

    $('#pcCancelNoQuoteBtn').on('click', function() {
        $('#pcAddNoQuoteInline').slideUp(200);
    });

    $('#pcSubmitNoQuoteBtn').on('click', async function() {
        if (!ensureEditing()) return;
        const firmId = $('#pcNoQuoteFirmSelector').val();
        if (!firmId) {
            toast('Please select a firm');
            return;
        }

        const fd = new FormData();
        fd.append('op', 'add_noquote');
        fd.append('frm_id', firmId);
        fd.append('nqt_reason', 'Quotation Not Received');
        fd.append('_token', @json(csrf_token()));

        try {
            const json = await postForm(fd);
            updateState(json);
            renderAll();
            toast(json.message || 'No-Quote firm added successfully');
            $('#pcNoQuoteFirmSelector').val('');
            $('#pcAddNoQuoteInline').slideUp(200);
        } catch (err) {
            toast(err.message || 'Error adding No-Quote firm');
        }
    });

    $(document).on('click', '.pc-del-noquote-btn', async function() {
        if (!ensureEditing()) return;
        const nqtId = this.getAttribute('data-nqt-id');
        if (!nqtId) return;
        if (!confirm('Are you sure you want to remove this firm from No-Quotes?')) return;

        const fd = new FormData();
        fd.append('op', 'delete_noquote');
        fd.append('nqt_id', nqtId);
        fd.append('_token', @json(csrf_token()));

        try {
            const json = await postForm(fd);
            updateState(json);
            renderAll();
            toast(json.message || 'Removed from No-Quotes');
        } catch (err) {
            toast(err.message || 'Error removing No-Quote');
        }
    });

    // AJAX Handler for Subhead and Awarded Vendor Metadata Forms
    $(document).on('submit', '.pc-metadata-ajax-form', async function(e) {
        e.preventDefault();
        if (!ensureEditing()) return;
        const fd = new FormData(this);
        fd.append('_token', @json(csrf_token()));

        try {
            const json = await postForm(fd);
            updateState(json);
            renderAll();
            if (state.pcs_subhead) {
                const shView = document.getElementById('pcSubheadView');
                if (shView) shView.textContent = state.pcs_subhead;
            }
            if (state.vendor_name) {
                const vView = document.getElementById('pcVendorView');
                if (vView) vView.textContent = state.vendor_name;
            }
            toast(json.message || 'Details updated successfully');
        } catch (err) {
            toast(err.message || 'Error updating details');
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

    async function ensureMammoth() {
        if (typeof mammoth !== 'undefined') return true;
        return new Promise((resolve) => {
            const s = document.createElement('script');
            s.src = @json(asset('plugins/mammoth/mammoth.browser.min.js'));
            s.onload = () => resolve(typeof mammoth !== 'undefined');
            s.onerror = () => resolve(false);
            document.head.appendChild(s);
        });
    }

    async function ensureXLSX() {
        if (typeof XLSX !== 'undefined') return true;
        return new Promise((resolve) => {
            const s = document.createElement('script');
            s.src = @json(asset('plugins/sheetjs/xlsx.full.min.js'));
            s.onload = () => resolve(typeof XLSX !== 'undefined');
            s.onerror = () => resolve(false);
            document.head.appendChild(s);
        });
    }

    function renderWordFallback(container, url, qteId, title, noticeMsg) {
        if (!container) return;
        container.innerHTML = `
            <div class="text-center py-5 px-4" style="max-width: 600px; margin: 0 auto;">
                <div class="mb-3">
                    <i class="fas fa-file-word text-primary" style="font-size: 56px;"></i>
                </div>
                <h5 class="text-dark font-weight-bold rajdhani" style="letter-spacing: 0.5px;">${(title || 'Document').toUpperCase()}</h5>
                <p class="text-muted small mb-4" style="line-height: 1.6;">
                    ${noticeMsg || 'This document cannot be rendered directly in-line. Please use the options below to view or download it.'}
                </p>
                <div class="d-flex justify-content-center" style="gap: 12px;">
                    <a href="${url}" target="_blank" class="btn btn-sm btn-primary px-3 font-weight-bold">
                        <i class="fas fa-external-link-alt mr-1"></i> Open in New Tab
                    </a>
                    <a href="${url.includes('?') ? url + '&download=1' : url + '?download=1'}" download class="btn btn-sm btn-outline-secondary px-3 font-weight-bold">
                        <i class="fas fa-download mr-1"></i> Download Copy
                    </a>
                </div>
            </div>
        `;
    }

    // Live preview quote document in modal
    $(document).on('click', '.pc-live-view-quote-btn', async function(e) {
        e.preventDefault();
        const btn = $(this);
        const url = btn.data('url') || btn.attr('href');
        const qteId = btn.data('qte-id') || btn.data('pat-id');
        const title = btn.data('title') || 'Quotation Document';
        if (!url || url === '#' || url === 'javascript:void(0)') return;

        // 1. Resolve extension safely from data attributes
        let ext = (btn.data('ext') || '').toString().toLowerCase().trim();
        const filePath = (btn.data('file-path') || btn.data('file-name') || '').toString();
        if (!ext && filePath) {
            ext = filePath.split('.').pop().toLowerCase().split('?')[0];
        }

        // 2. Look up in state.quotes or state.attachments if ext is missing/invalid
        if (!ext || ext.length > 5 || ext.includes('/') || ext.includes(':')) {
            if (qteId && typeof state !== 'undefined' && state) {
                const qObj = (state.quotes || []).find(x => String(x.qte_id) === String(qteId));
                if (qObj && (qObj.attachment_path || qObj.attachment_name)) {
                    ext = (qObj.attachment_path || qObj.attachment_name).split('.').pop().toLowerCase().split('?')[0];
                }
                if (!ext) {
                    const aObj = (state.attachments || []).find(x => String(x.pat_id) === String(qteId));
                    if (aObj && (aObj.pat_path || aObj.pat_filename)) {
                        ext = (aObj.pat_path || aObj.pat_filename).split('.').pop().toLowerCase().split('?')[0];
                    }
                }
            }
        }

        // 3. Fallback to extracting valid extension from URL if present
        if (!ext || ext.length > 5 || ext.includes('/') || ext.includes(':')) {
            const cleanUrl = url.split('?')[0].split('#')[0];
            const candidate = cleanUrl.split('.').pop().toLowerCase();
            if (['pdf','png','jpg','jpeg','webp','gif','bmp','svg','doc','docx','xls','xlsx','csv','txt','rtf'].includes(candidate)) {
                ext = candidate;
            }
        }

        if (!ext || ext.length > 5 || ext.includes('/') || ext.includes(':')) {
            ext = '';
        }

        // Configure Modal Header and Action Buttons
        const cleanExt = (ext || '').toUpperCase();
        const badgeHtml = cleanExt ? `<span class="badge badge-primary ml-2 px-2 py-0.5" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">${cleanExt}</span>` : '';
        $('#pcQuoteViewerTitle').html(`${escapeHtml(title.toUpperCase())} - QUOTATION ${badgeHtml}`);
        $('#pcQuoteViewerOpenNewTab').attr('href', url);
        const dlUrl = url.includes('?') ? (url + '&download=1') : (url + '?download=1');
        $('#pcQuoteViewerDownloadBtn').attr('href', dlUrl);
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
        if (loading) { loading.style.display = 'flex'; }

        $('#pcQuoteViewerModal').modal('show');

        // 4. If ext is still unknown, detect from server response headers
        if (!ext) {
            try {
                const headRes = await fetch(url, { method: 'HEAD' });
                const headerExt = headRes.headers.get('x-file-extension');
                if (headerExt) {
                    ext = headerExt.toLowerCase().trim();
                } else {
                    const ct = (headRes.headers.get('content-type') || '').toLowerCase();
                    if (ct.includes('pdf')) ext = 'pdf';
                    else if (ct.includes('word') || ct.includes('document')) ext = 'docx';
                    else if (ct.includes('sheet') || ct.includes('excel')) ext = 'xlsx';
                    else if (ct.includes('image/')) ext = 'png';
                    else if (ct.includes('text/plain')) ext = 'txt';
                }
                if (ext) {
                    const cleanExt = ext.toUpperCase();
                    const badgeHtml = `<span class="badge badge-primary ml-2 px-2 py-0.5" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px;">${cleanExt}</span>`;
                    $('#pcQuoteViewerTitle').html(`${escapeHtml(title.toUpperCase())} - QUOTATION ${badgeHtml}`);
                }
            } catch (headErr) {
                console.warn('HEAD detection error:', headErr);
            }
        }

        // 5. Render based on detected file extension
        // A. Image Formats
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'jfif', 'ico', 'avif'].includes(ext)) {
            if (imgWrap && img) {
                img.onload = function() {
                    if (loading) loading.style.display = 'none';
                    imgWrap.style.display = 'flex';
                };
                img.onerror = function() {
                    if (loading) loading.style.display = 'none';
                    renderDiagnosticError(docContent, url, qteId, title, 'Image failed to load (403 Forbidden or file missing).');
                    if (imgWrap) imgWrap.style.display = 'none';
                    if (docWrap) docWrap.style.display = 'flex';
                };
                img.src = url;
            } else {
                if (loading) loading.style.display = 'none';
            }
        } 
        // B. Word Documents (DOCX / DOC)
        else if (['docx', 'doc'].includes(ext)) {
            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                const arrayBuffer = await res.arrayBuffer();
                await ensureMammoth();

                if (typeof mammoth !== 'undefined') {
                    try {
                        const result = await mammoth.convertToHtml({ arrayBuffer: arrayBuffer });
                        if (docContent) {
                            docContent.innerHTML = result.value || '<p class="text-muted font-italic text-center py-4">Document content is empty.</p>';
                        }
                        if (docWrap) docWrap.style.display = 'flex';
                    } catch (convErr) {
                        console.warn('Mammoth convert failed, rendering fallback:', convErr);
                        renderWordFallback(docContent, url, qteId, title, 'Older Word (.doc) format requires downloading or saving as .docx for live in-browser preview.');
                        if (docWrap) docWrap.style.display = 'flex';
                    }
                } else {
                    renderDiagnosticError(docContent, url, qteId, title, 'Mammoth preview library unavailable.');
                    if (docWrap) docWrap.style.display = 'flex';
                }
            } catch (err) {
                console.error('Word rendering error:', err);
                renderDiagnosticError(docContent, url, qteId, title, err.message);
                if (docWrap) docWrap.style.display = 'flex';
            } finally {
                if (loading) loading.style.display = 'none';
            }
        } 
        // C. Excel Spreadsheets (XLSX / XLS / CSV)
        else if (['xlsx', 'xls', 'csv'].includes(ext)) {
            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                const arrayBuffer = await res.arrayBuffer();
                await ensureXLSX();

                if (typeof XLSX !== 'undefined') {
                    const workbook = XLSX.read(arrayBuffer, { type: 'array' });
                    const sheetNames = workbook.SheetNames || [];
                    if (sheetNames.length === 0) {
                        if (sheetContent) sheetContent.innerHTML = '<div class="text-muted p-4 text-center">Workbook contains no sheets.</div>';
                    } else {
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
                console.error('Excel rendering error:', err);
                renderDiagnosticError(sheetContent, url, qteId, title, err.message);
                if (sheetWrap) sheetWrap.style.display = 'flex';
            } finally {
                if (loading) loading.style.display = 'none';
            }
        } 
        // D. Plain Text / Code (TXT, LOG, JSON, XML)
        else if (['txt', 'log', 'json', 'xml'].includes(ext)) {
            try {
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                const text = await res.text();
                if (docContent) {
                    docContent.innerHTML = `<pre style="font-family: monospace; font-size: 13px; color: #1e293b; background: #f8fafc; padding: 20px; border-radius: 6px; border: 1px solid #e2e8f0; white-space: pre-wrap; word-break: break-all;">${escapeHtml(text)}</pre>`;
                }
                if (docWrap) docWrap.style.display = 'flex';
            } catch (err) {
                renderDiagnosticError(docContent, url, qteId, title, err.message);
                if (docWrap) docWrap.style.display = 'flex';
            } finally {
                if (loading) loading.style.display = 'none';
            }
        } 
        // E. PDF Documents
        else if (ext === 'pdf') {
            if (iframe) {
                iframe.onload = function() {
                    if (loading) loading.style.display = 'none';
                };
                iframe.style.display = 'block';
                iframe.src = url;
            } else {
                if (loading) loading.style.display = 'none';
            }
        }
        // F. Other Unknown Binary Formats - Never set iframe.src to avoid forced browser downloads!
        else {
            if (loading) loading.style.display = 'none';
            if (docContent && docWrap) {
                renderWordFallback(docContent, url, qteId, title, `File type (.${ext || 'unknown'}) cannot be rendered directly in the browser.`);
                docWrap.style.display = 'flex';
            } else if (iframe) {
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

        // Ensure user confirms that quotation is officially attested
        const confirmMsg = "IMPORTANT ATTESTATION REQUIREMENT:\n\nبرائے مہربانی یقینی بنائیں کہ صرف باقاعدہ تصدیق شدہ (Attested with stamp & signature) کوٹیشن ہی اپلوڈ کی جا رہی ہے۔\n\nPlease confirm that the quotation document being uploaded is officially ATTESTED by the vendor.\n\nDo you want to proceed?";
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: 'Attested Quotation Required',
                html: '<div style="text-align:left; font-size:13px; line-height:1.6;">' +
                      '<p class="mb-2 text-warning font-weight-bold"><i class="fas fa-stamp mr-1"></i> برائے مہربانی یقینی بنائیں کہ صرف باقاعدہ تصدیق شدہ (Attested) کوٹیشن ہی اپلوڈ کی جا رہی ہے۔</p>' +
                      '<p class="mb-0 text-muted">Please confirm that this document is officially <strong>ATTESTED</strong> with vendor stamp and signature before uploading.</p>' +
                      '</div>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="fas fa-check-circle mr-1"></i> Yes, It Is Attested',
                cancelButtonText: 'Cancel'
            });
            if (!result.isConfirmed) {
                this.value = '';
                return;
            }
        } else {
            if (!confirm(confirmMsg)) {
                this.value = '';
                return;
            }
        }

        const fd = new FormData();
        fd.append('op', 'upload_quote_file');
        fd.append('qte_id', activeDirectQteId);
        fd.append('quote_file', file);
        fd.append('_token', @json(csrf_token()));

        toast('Uploading attested quote document...');
        try {
            const json = await postForm(fd);
            updateState(json);
            renderAll();
            toast(json.message || 'Attested quote document uploaded successfully');
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
            updateState(json);
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
            updateState(json);
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
            updateState(json);
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
                updateState(json);
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

    $(document).on('input change', '#pcGlobalTaxMode, #pcGlobalTaxType, #pcGlobalTaxPercent', function() {
        renderMultiQuoteTotals();
    });

    $(document).on('change', '.pc-modal-quote-file-input', function() {
        const idx = parseInt(this.getAttribute('data-idx'));
        if (this.files && this.files[0]) {
            modalVendors[idx].file = this.files[0];
            toast('Attested quotation attached. Please ensure the document is stamped & signed by the firm.');
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

        const taxMode = document.getElementById('pcGlobalTaxMode')?.value || 'exclusive';
        const taxType = document.getElementById('pcGlobalTaxType')?.value || 'GST';
        const taxPercent = parseFloat(document.getElementById('pcGlobalTaxPercent')?.value || 0);

        try {
            for (const v of modalVendors) {
                if (!v.firm_name.trim()) continue;
                
                const fd = new FormData();
                fd.append('op', 'add_quote');
                if (v.id) fd.append('qte_id', v.id);
                fd.append('firm_name', v.firm_name);
                fd.append('tax_mode', taxMode);
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
                updateState(json);
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
            updateState(json);
            renderAll();
            toast(json.message || 'Saved');
        } catch (err) {
            toast(err.message || 'Error');
        }
    });

    $(document).on('submit', '#formAddPurchaseCaseAttachment', async function(e) {
        e.preventDefault();
        const docTitle = $('#purAttDocTitle').val().trim();
        const fileInput = $('#purAttFile')[0];

        if (!docTitle) {
            if (window.Swal) {
                Swal.fire({ icon: 'warning', title: 'Document Title Required', text: 'Please enter a name for this document.', confirmButtonColor: '#5F7858' });
            } else {
                alert('Please enter a Document Title / Name.');
            }
            return;
        }

        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            if (window.Swal) {
                Swal.fire({ icon: 'warning', title: 'File Required', text: 'Please select a file to upload.', confirmButtonColor: '#5F7858' });
            } else {
                alert('Please select a file to upload.');
            }
            return;
        }

        const $btn = $('#btnUploadPurAttachment');
        const origBtnHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Uploading...');

        const fd = new FormData();
        fd.append('op', 'add_files');
        fd.append('_token', @json(csrf_token()));
        fd.append('doc_title', docTitle);
        fd.append('file', fileInput.files[0]);
        fd.append('attachments[]', fileInput.files[0]);

        try {
            const json = await postForm(fd);
            $btn.prop('disabled', false).html(origBtnHtml);
            $('#modalAddPurchaseCaseAttachment').modal('hide');
            this.reset();

            if (json.ok) {
                updateState(json);
                renderAll();
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: json.message || 'Attachment uploaded successfully!',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    toast(json.message || 'Attachment uploaded successfully!');
                }
            } else {
                throw new Error(json.message || 'Failed to upload attachment.');
            }
        } catch (err) {
            $btn.prop('disabled', false).html(origBtnHtml);
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Upload Failed', text: err.message || 'Could not upload attachment.' });
            } else {
                toast(err.message || 'Error uploading file');
            }
        }
    });

    $(document).on('change', '#pcIncidentalQuoteFileInput', async function() {
        if (!this.files || !this.files.length) return;
        const qteId = this.getAttribute('data-qte-id') || 0;
        const file = this.files[0];
        const fd = new FormData();
        fd.append('op', 'upload_quote_file');
        fd.append('_token', @json(csrf_token()));
        fd.append('qte_id', qteId);
        fd.append('quote_file', file);

        try {
            const json = await postForm(fd);
            updateState(json);
            renderAll();
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: json.message || 'Vendor quotation uploaded successfully!',
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                toast(json.message || 'Vendor quotation uploaded successfully!');
            }
        } catch (err) {
            toast(err.message || 'Error uploading vendor quotation');
        }
        this.value = '';
    });

    window.promptCreateIt = function(pcsId) {
        if (confirm('Do you want to create IT / RFQ Letter for this purchase case?')) {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            fetch(`/purchase/case/${pcsId}/it-letter/create`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(async res => {
                const contentType = res.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await res.text();
                    throw new Error(`Server returned HTTP ${res.status}: ${res.statusText}`);
                }
                return res.json();
            })
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
