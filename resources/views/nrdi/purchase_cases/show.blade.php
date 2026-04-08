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
.dg-grid { display:grid; grid-template-columns:1fr 390px; gap:18px; align-items:start; }
@media(max-width:1300px){ .dg-grid { grid-template-columns:1fr 350px; } }
@media(max-width:1024px){ .dg-grid { grid-template-columns:1fr 320px; } }
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
.dg-fin-row { display:grid; grid-template-columns:35% 1fr; gap:14px; align-items:stretch; }
.dg-fin-col { background:var(--rd-surface); border:1px solid var(--rd-border); border-radius:10px; padding:14px; display:flex; flex-direction:column; }
@media(max-width:860px) { .dg-fin-row { grid-template-columns:1fr; } }
@media(max-width:600px) { .dg-fin-row { grid-template-columns:1fr; } }

/* ---- Financial numbers ---- */
.dg-fin-nums { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:12px; }
.dg-fin-card { background:var(--rd-surface2); border:1px solid var(--rd-border); border-radius:7px; padding:9px 10px; text-align:center; }
.dg-fin-label { font-size:9px; letter-spacing:.8px; text-transform:uppercase; color:var(--rd-text3); margin-bottom:3px; font-weight:600; }
.dg-fin-value { font-family:'Rajdhani',sans-serif; font-size:14px; font-weight:700; }

.dg-prog-wrap { position:relative; height:16px; background:rgba(255,255,255,0.04); border-radius:20px; overflow:hidden; margin-bottom:6px; border:1px solid var(--rd-border); }
.dg-prog-utilized { position:absolute; left:0; top:0; height:100%; background:var(--rd-text3); border-radius:20px 0 0 20px; width:0; transition:width 1s cubic-bezier(.4,0,.2,1) .2s; }
.dg-prog-utilized.anim { width:var(--pw); }
.dg-prog-case {
    position:absolute; top:0; height:100%;
    background: repeating-linear-gradient(-45deg, var(--rd-info) 0px, var(--rd-info) 8px, rgba(255,255,255,0.18) 8px, rgba(255,255,255,0.18) 16px);
    background-size:22px 100%;
    animation:dgStripeFlow .7s linear infinite, dgCaseGrow .9s cubic-bezier(.4,0,.2,1) .7s both;
    width:0; left:var(--lu);
}
@keyframes dgStripeFlow { 0%{background-position:0 0} 100%{background-position:22px 0} }
@keyframes dgCaseGrow   { from{width:0} to{width:var(--pw)} }
.dg-prog-remain { position:absolute; right:0; top:0; height:100%; border-radius:0 20px 20px 0; width:0; transition:width .9s cubic-bezier(.4,0,.2,1) 1s; }
.dg-prog-remain.anim { width:var(--pw); }
.dg-prog-legend { display:flex; gap:12px; flex-wrap:wrap; margin-top:6px; justify-content:center; }
.dg-leg-item { display:flex; align-items:center; gap:4px; font-size:10px; color:var(--rd-text2); }
.dg-leg-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }

.dg-chart-wrap { position:relative; width:100%; height:130px; }
.dg-chart-sm   { position:relative; width:100%; height:110px; }

/* ---- Account head card ---- */
.dg-head-card { background:rgba(23,162,184,0.05); border:1px solid rgba(23,162,184,0.18); border-left:3px solid var(--rd-info); border-radius:7px; padding:9px 12px; margin-bottom:12px; }
.dg-head-label { font-size:9px; font-weight:700; letter-spacing:1.5px; color:var(--rd-info); text-transform:uppercase; margin-bottom:5px; }
.dg-head-name { font-family:'Rajdhani',sans-serif; font-size:14px; font-weight:700; color:var(--rd-text1); line-height:1.3; }
.dg-head-meta { font-size:10px; color:var(--rd-text3); margin-top:2px; }
.dg-tag { font-size:9px; font-weight:700; padding:1px 8px; border-radius:4px; letter-spacing:.5px; display:inline-block; }

/* ---- Items box ---- */
.dg-box { background:var(--rd-surface); border:1px solid var(--rd-border); border-radius:10px; overflow:hidden; }
.dg-box-hdr { background:var(--rd-surface2); padding:10px 14px; border-bottom:1px solid var(--rd-border); display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
.dg-box-hdr-left { display:flex; flex-direction:column; gap:2px; }
.dg-box-hdr-firm { font-family:'Rajdhani',sans-serif; font-size:15px; font-weight:700; color:var(--rd-text1); }
.dg-box-hdr-cost { font-size:11px; color:var(--rd-success); font-weight:600; }
.dg-box-hdr-right { display:flex; align-items:center; gap:8px; flex-shrink:0; }

.dg-cs-btn { background:rgba(23,162,184,0.1); border:1px solid rgba(23,162,184,0.3); color:var(--rd-info); font-size:11px; font-weight:600; padding:6px 12px; border-radius:7px; cursor:pointer; transition:all .2s; white-space:nowrap; }
.dg-cs-btn:hover { background:rgba(23,162,184,0.22); color:#fff; border-color:var(--rd-info); }

.dg-items-wrap { max-height:220px; overflow-y:auto; }
.dg-items-wrap::-webkit-scrollbar { width:3px; }
.dg-items-wrap::-webkit-scrollbar-thumb { background:var(--rd-border); border-radius:4px; }
.dg-items-table { width:100%; font-size:12px; border-collapse:collapse; }
.dg-items-table th { padding:7px 11px; color:var(--rd-text3); font-weight:600; font-size:10px; letter-spacing:.8px; text-align:left; text-transform:uppercase; background:var(--rd-surface2); }
.dg-items-table td { padding:7px 11px; border-top:1px solid var(--rd-border); color:var(--rd-text1); font-size:12px; }
.dg-items-table tr:hover td { background:rgba(255,255,255,0.015); }
.dg-price-col { color:var(--rd-success) !important; font-weight:600; text-align:right !important; }
.dg-qty-col { text-align:center !important; color:var(--rd-warning) !important; font-weight:600; }

.dg-terms-row { padding:8px 14px; border-top:1px solid var(--rd-border); background:rgba(255,255,255,0.015); line-height:1.4; }
.dg-terms-label { font-size:11px; font-weight:700; color:var(--rd-text1); display:inline; }
.dg-terms-text { font-size:11px; color:var(--rd-text2); display:inline; margin-left:4px; }

/* ---- Spending breakdown box ---- */
.dg-spend-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
@media(max-width:600px){ .dg-spend-grid { grid-template-columns:1fr; } }

/* ---- Right panel ---- */
.dg-right { display:flex; flex-direction:column; gap:14px; }
.dg-panel-r { background:var(--rd-surface); border:1px solid var(--rd-border); border-radius:10px; overflow:hidden; }
.dg-panel-r-hdr { background:var(--rd-surface2); padding:11px 15px; border-bottom:1px solid var(--rd-border); display:flex; align-items:center; gap:8px; }
.dg-panel-r-title { font-family:'Rajdhani',sans-serif; font-size:13px; font-weight:700; color:var(--rd-accent); letter-spacing:1px; text-transform:uppercase; }

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

.dg-pending { display:flex; gap:10px; padding-top:10px; align-items:center; }
.dg-pulse-ring { width:26px; height:26px; border-radius:50%; border:2px solid var(--rd-info); display:flex; align-items:center; justify-content:center; position:relative; flex-shrink:0; }
.dg-pulse-ring::before { content:''; position:absolute; inset:-4px; border-radius:50%; border:2px solid rgba(23,162,184,0.3); animation:dgPulse 1.5s ease-in-out infinite; }
@keyframes dgPulse { 0%,100%{transform:scale(1);opacity:.7} 50%{transform:scale(1.2);opacity:0} }

.dg-remarks-textarea { width:100%; background:var(--rd-surface2); border:1px solid var(--rd-border); border-radius:7px; color:var(--rd-text1); font-size:12px; padding:9px 11px; resize:none; outline:none; transition:border-color .2s; font-family:'Inter',sans-serif; line-height:1.5; }
.dg-remarks-textarea:focus { border-color:var(--rd-accent); }
.dg-remarks-error { font-size:11px; color:var(--rd-danger); margin-top:4px; display:none; align-items:center; gap:4px; }

.dg-btn-approve { width:100%; background:rgba(40,167,69,0.08); border:1px solid rgba(40,167,69,0.25); color:var(--rd-success); font-family:'Rajdhani',sans-serif; font-size:14px; font-weight:700; letter-spacing:1px; padding:10px; border-radius:7px; cursor:pointer; transition:all .25s; margin-bottom:8px; }
.dg-btn-approve:hover { background:rgba(40,167,69,0.2); border-color:var(--rd-success); color:#fff; transform:translateY(-1px); }
.dg-btn-row { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.dg-btn-return { background:rgba(255,193,7,0.08); border:1px solid rgba(255,193,7,0.25); color:var(--rd-warning); font-family:'Rajdhani',sans-serif; font-size:13px; font-weight:700; letter-spacing:1px; padding:9px; border-radius:7px; cursor:pointer; transition:all .25s; }
.dg-btn-return:hover { background:rgba(255,193,7,0.2); border-color:var(--rd-warning); color:#fff; transform:translateY(-1px); }
.dg-btn-reject { background:rgba(220,53,69,0.08); border:1px solid rgba(220,53,69,0.25); color:var(--rd-danger); font-family:'Rajdhani',sans-serif; font-size:13px; font-weight:700; letter-spacing:1px; padding:9px; border-radius:7px; cursor:pointer; transition:all .25s; }
.dg-btn-reject:hover { background:rgba(220,53,69,0.2); border-color:var(--rd-danger); color:#fff; transform:translateY(-1px); }

.dg-return-notice { background:rgba(220,53,69,0.07); border:1px solid rgba(220,53,69,0.2); border-left:3px solid var(--rd-danger); border-radius:8px; padding:9px 13px; display:flex; align-items:center; gap:10px; }

/* ---- CS Modal ---- */
.dg-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.75); z-index:9999; align-items:center; justify-content:center; padding:30px; overflow:auto; }
.dg-modal-overlay.open { display:flex; }
.dg-modal-box { background:var(--rd-surface); border:1px solid var(--rd-border); border-top:2px solid var(--rd-accent); border-radius:10px; display:inline-flex; flex-direction:column; width:auto; min-width:560px; max-width:calc(100vw - 60px); }
.dg-modal-header { padding:11px 16px; background:var(--rd-surface2); border-bottom:1px solid var(--rd-border); display:flex; justify-content:space-between; align-items:center; gap:24px; white-space:nowrap; }
.dg-modal-title { font-family:'Rajdhani',sans-serif; font-size:14px; font-weight:700; color:var(--rd-accent); letter-spacing:1px; }
.dg-modal-close { background:none; border:none; color:var(--rd-text3); font-size:18px; cursor:pointer; padding:2px 6px; border-radius:4px; transition:all .2s; flex-shrink:0; }
.dg-modal-close:hover { background:rgba(255,255,255,0.08); color:var(--rd-text1); }
.dg-modal-body { padding:12px; overflow:visible; }
.dg-modal-footer { padding:9px 14px; background:var(--rd-surface2); border-top:1px solid var(--rd-border); display:flex; justify-content:flex-end; border-radius:0 0 10px 10px; }

.dg-cs-table { width:auto; border-collapse:collapse; font-size:12px; white-space:nowrap; }
.dg-cs-table th, .dg-cs-table td { padding:7px 12px; border:1px solid var(--rd-border); text-align:center; }
.dg-cs-table thead th { background:var(--rd-surface2); color:var(--rd-text3); font-size:10px; letter-spacing:1px; text-transform:uppercase; }
.dg-cs-table thead .dg-wc { background:rgba(201,162,39,0.1); color:#e8c04a; }
.dg-cs-table tbody td { color:var(--rd-text1); }
.dg-cs-table tbody .dg-wtd { background:rgba(201,162,39,0.04); color:#e8c04a; font-weight:600; }
.dg-cs-table tbody td:nth-child(2) { text-align:left; font-size:11px; white-space:normal; min-width:160px; max-width:240px; }
.dg-cs-table tfoot td { background:var(--rd-surface2); font-weight:700; font-size:12px; }
.dg-cs-table tfoot .dg-wtd { color:#e8c04a; }

.dg-shake { animation:dgShake .4s; }
@keyframes dgShake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-5px)} 40%{transform:translateX(5px)} 60%{transform:translateX(-3px)} 80%{transform:translateX(3px)} }

/* ---- Responsive tweaks ---- */

/* -- Tablets/medium screens -- */
@media(max-width:1300px){
    .dg-grid { grid-template-columns:1fr 350px; }
}
@media(max-width:1024px){
    .dg-grid { grid-template-columns:1fr 320px; }
    .dg-fin-nums { grid-template-columns:1fr 1fr; gap:6px; }
    .dg-chart-wrap { height:110px; }
    .dg-chart-sm { height:90px; }
}

/* -- Stack to single column -- */
@media(max-width:860px){
    .dg-grid { grid-template-columns:1fr; }
    .dg-fin-row { grid-template-columns:1fr 1fr; }
    .dg-right { order:2; }
}

/* -- Tablets portrait -- */
@media(max-width:768px){
    .dg-hdr { flex-direction:column; gap:8px; }
    .dg-case-date { text-align:left; }
    .dg-fin-row { grid-template-columns:1fr; }
    .dg-spend-grid { grid-template-columns:1fr; }
    .dg-meta-header { flex-direction:column; align-items:flex-start !important; gap:4px !important; }
    .dg-meta-header span { font-size:13px !important; }
    .dg-pipe { display:none !important; }
    .dg-modal-box { min-width:unset; width:95vw; max-width:95vw; }
    .dg-modal-body { padding:8px; overflow-x:auto; }
    .dg-cs-table { font-size:11px; }
    .dg-cs-table th, .dg-cs-table td { padding:5px 8px; }
}

/* -- Mobile landscape / small screens -- */
@media(max-width:600px){
    .dg-page { font-size:13px; }
    .dg-fin-nums { grid-template-columns:1fr 1fr; gap:5px; }
    .dg-fin-label { font-size:8px; }
    .dg-fin-value { font-size:12px; }
    .dg-box-hdr { flex-direction:column; align-items:flex-start; gap:6px; }
    .dg-box-hdr-right { width:100%; justify-content:space-between; }
    .dg-btn-row { grid-template-columns:1fr 1fr; }
    .dg-chart-wrap { height:100px; }
    .dg-chart-sm { height:85px; }
    .dg-items-table th, .dg-items-table td { padding:5px 8px; font-size:11px; }
    .dg-items-wrap { max-height:180px; }
    .dg-trail-body { max-height:250px !important; }
    .dg-tl-actor { font-size:12px; }
    .dg-tl-badge { font-size:9px; }
    .dg-tl-comment { font-size:10px; }
    .dg-panel-r-title { font-size:11px; }
    .dg-modal-overlay { padding:10px; }
    .dg-modal-box { min-width:unset; width:100%; max-width:100%; border-radius:8px; }
    .dg-modal-header { padding:9px 12px; }
    .dg-modal-title { font-size:12px; }
}

/* -- Very small phones -- */
@media(max-width:480px){
    .dg-page { padding:0 4px; }
    .dg-back-btn { font-size:10px; padding:4px 10px; }
    .dg-page-title { font-size:16px; }
    .dg-sec-label { font-size:10px; letter-spacing:1.2px; }
    .dg-meta-header span { font-size:12px !important; }
    .dg-fin-col { padding:10px; }
    .dg-fin-nums { grid-template-columns:1fr 1fr; }
    .dg-fin-card { padding:6px 8px; }
    .dg-btn-approve { font-size:12px; padding:8px; }
    .dg-btn-return, .dg-btn-reject { font-size:11px; padding:7px; }
    .dg-remarks-textarea { font-size:11px; }
    .dg-head-card { padding:7px 10px; }
    .dg-head-name { font-size:12px; }
    .dg-prog-legend { gap:8px; }
    .dg-leg-item { font-size:9px; }
}

/* ---- Toast Notifications ---- */
.dg-toast-container { position:fixed; top:20px; right:20px; z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
.dg-toast {
    pointer-events:auto; display:flex; align-items:center; gap:12px; min-width:320px; max-width:460px;
    padding:14px 18px; border-radius:10px; font-family:'Inter',sans-serif; font-size:13px;
    color:#fff; box-shadow:0 8px 32px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.06);
    backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px);
    transform:translateX(120%); opacity:0; transition:all .45s cubic-bezier(.16,1,.3,1);
}
.dg-toast.show { transform:translateX(0); opacity:1; }
.dg-toast.hide { transform:translateX(120%); opacity:0; }
.dg-toast-icon { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
.dg-toast-body { flex:1; }
.dg-toast-title { font-weight:700; font-size:14px; margin-bottom:2px; font-family:'Rajdhani',sans-serif; letter-spacing:.5px; }
.dg-toast-msg { font-size:12px; opacity:.85; line-height:1.4; }
.dg-toast-close { background:none; border:none; color:rgba(255,255,255,0.5); font-size:16px; cursor:pointer; padding:4px; border-radius:4px; transition:all .2s; flex-shrink:0; }
.dg-toast-close:hover { color:#fff; background:rgba(255,255,255,0.1); }
.dg-toast-progress { position:absolute; bottom:0; left:0; height:3px; border-radius:0 0 10px 10px; animation:dgToastProg 4s linear forwards; }
@keyframes dgToastProg { from{width:100%} to{width:0%} }

.dg-toast--success { background:linear-gradient(135deg, rgba(40,167,69,0.92), rgba(30,130,55,0.95)); border:1px solid rgba(40,167,69,0.4); }
.dg-toast--success .dg-toast-icon { background:rgba(255,255,255,0.15); }
.dg-toast--success .dg-toast-progress { background:rgba(255,255,255,0.3); }

.dg-toast--warning { background:linear-gradient(135deg, rgba(255,165,0,0.92), rgba(200,130,0,0.95)); border:1px solid rgba(255,165,0,0.4); }
.dg-toast--warning .dg-toast-icon { background:rgba(255,255,255,0.15); }
.dg-toast--warning .dg-toast-progress { background:rgba(255,255,255,0.3); }

.dg-toast--danger { background:linear-gradient(135deg, rgba(220,53,69,0.92), rgba(180,40,55,0.95)); border:1px solid rgba(220,53,69,0.4); }
.dg-toast--danger .dg-toast-icon { background:rgba(255,255,255,0.15); }
.dg-toast--danger .dg-toast-progress { background:rgba(255,255,255,0.3); }

.dg-toast--info { background:linear-gradient(135deg, rgba(23,162,184,0.92), rgba(18,130,150,0.95)); border:1px solid rgba(23,162,184,0.4); }
.dg-toast--info .dg-toast-icon { background:rgba(255,255,255,0.15); }
.dg-toast--info .dg-toast-progress { background:rgba(255,255,255,0.3); }

/* ---- Confirm Dialog ---- */
.dg-confirm-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); backdrop-filter:blur(4px); z-index:99998; align-items:center; justify-content:center; }
.dg-confirm-overlay.open { display:flex; }
.dg-confirm-box {
    background:var(--rd-surface); border:1px solid var(--rd-border); border-top:3px solid var(--rd-accent);
    border-radius:12px; padding:24px 28px; max-width:420px; width:90vw; text-align:center;
    box-shadow:0 20px 60px rgba(0,0,0,0.5); animation:dgConfirmIn .3s cubic-bezier(.16,1,.3,1);
}
@keyframes dgConfirmIn { from{transform:scale(0.9);opacity:0} to{transform:scale(1);opacity:1} }
.dg-confirm-icon { width:52px; height:52px; border-radius:50%; margin:0 auto 14px; display:flex; align-items:center; justify-content:center; font-size:22px; }
.dg-confirm-title { font-family:'Rajdhani',sans-serif; font-size:18px; font-weight:700; color:var(--rd-text1); margin-bottom:6px; }
.dg-confirm-msg { font-size:13px; color:var(--rd-text2); margin-bottom:20px; line-height:1.5; }
.dg-confirm-btns { display:flex; gap:10px; justify-content:center; }
.dg-confirm-yes { padding:9px 28px; border-radius:8px; font-family:'Rajdhani',sans-serif; font-size:14px; font-weight:700; letter-spacing:1px; cursor:pointer; border:none; color:#fff; transition:all .2s; }
.dg-confirm-yes:hover { transform:translateY(-1px); filter:brightness(1.15); }
.dg-confirm-no { padding:9px 28px; border-radius:8px; font-family:'Rajdhani',sans-serif; font-size:14px; font-weight:700; letter-spacing:1px; cursor:pointer; background:var(--rd-surface2); border:1px solid var(--rd-border); color:var(--rd-text2); transition:all .2s; }
.dg-confirm-no:hover { border-color:var(--rd-text3); color:var(--rd-text1); }

@media(max-width:480px){
    .dg-toast-container { top:10px; right:10px; left:10px; }
    .dg-toast { min-width:unset; max-width:100%; font-size:12px; padding:10px 14px; }
    .dg-confirm-box { padding:18px 20px; }
}
</style>

<div class="content-wrapper dg-page">

    <div class="content-header pb-0">
        <div class="container-fluid">

            @php
                $winnerQuote  = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->first() : null;
                $sortedQ      = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->values() : collect([]);

                $caseValue      = (float)($purchase->pcs_price ?? ($winnerQuote?->qte_price ?? 0));
                
                // Live Financials from $head (Project)
                $totalBudget    = (float)($head->prj_aprvcost ?? 5000000); 
                $utilizedBudget = $totalBudget - (float)($head->hed_balance ?? $totalBudget);
                $balanceAfter   = (float)($head->hed_balance ?? 0) - $caseValue;

                $pctUtilized  = $totalBudget > 0 ? ($utilizedBudget / $totalBudget) * 100 : 0;
                $pctCase      = $totalBudget > 0 ? ($caseValue / $totalBudget) * 100 : 0;
                $pctRemaining = max(0, ($balanceAfter / $totalBudget) * 100);
                $returnCount  = $purchase->decisions->where('pdec_action', 'return')->count();
            @endphp

            {{-- Page Header --}}
            <div style="margin-bottom:16px;">
                {{-- Top Row: Title + Back Button --}}
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                    <div style="font-family:'Rajdhani',sans-serif; font-size:24px; font-weight:700; color:#fff; letter-spacing:1px; text-transform:uppercase;">
                        <i class="fas fa-shopping-cart mr-2" style="color:var(--rd-accent); font-size:18px;"></i> {{ $pageTitle ?? 'Purchase Case' }}
                    </div>
                    <a href="{{ route('approvals.dashboard') }}" class="dg-back-btn">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                </div>

                {{-- Second Row: Meta line + Case ID + Date --}}
                <div class="dg-meta-header" style="display:flex; flex-wrap:wrap; align-items:center; gap:4px 0;">
                    <span style="font-size:15px; color:#fff; font-weight:700;">Purchase Case Title:</span>&nbsp;<span style="font-size:15px; color:#fff;">{{ $purchase->pcs_title }}</span>
                    <span class="dg-pipe" style="color:var(--rd-text3); margin:0 10px; font-size:15px;">|</span>
                    <span style="font-size:15px; color:#fff; font-weight:700;">Division Initiator:</span>&nbsp;<span style="font-size:15px; color:#fff;">{{ $divisionName ?? $purchase->pcs_unt_id }}</span>
                    <span class="dg-pipe" style="color:var(--rd-text3); margin:0 10px; font-size:15px;">|</span>
                    <span style="font-size:15px; color:#fff; font-weight:700;">Project Head:</span>&nbsp;<span style="font-size:15px; color:#fff;">{{ $purchase->project?->prj_code ?? $purchase->pcs_hed_id }}</span>
                    <span class="dg-status-badge" style="margin-left:12px; vertical-align:middle;">{{ $purchase->pcs_status }}</span>
                    <span class="dg-pipe" style="color:var(--rd-text3); margin:0 10px; font-size:15px;">|</span>
                    <span class="dg-case-badge" style="vertical-align:middle;">{{ $purchase->pcs_type }}-{{ $purchase->pcs_id }}</span>
                    <span style="font-size:12px; color:var(--rd-text3); margin-left:10px;"><i class="fas fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}</span>
                </div>
            </div>

        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="dg-grid">

                {{-- ============ LEFT PANE ============ --}}
                <div style="display:flex;flex-direction:column;gap:18px;">

                    {{-- 2. SPENDING BREAKDOWN (35%) + FINANCIAL PICTURE (65%) --}}
                    <div class="dg-fin-row">

                        {{-- Spending Breakdown (LEFT — 35%) --}}
                        <div class="dg-fin-col">
                            <div class="dg-sec-label"><i class="fas fa-chart-bar fa-xs"></i> Spending Breakdown</div>
                            <div style="margin-bottom:12px;">
                                <div style="font-size:10px;color:var(--rd-text3);font-weight:600;letter-spacing:.8px;text-transform:uppercase;margin-bottom:6px;">Category Split</div>
                                <div class="dg-chart-sm"><canvas id="dgDonutChart"></canvas></div>
                            </div>
                            <div>
                                <div style="font-size:10px;color:var(--rd-text3);font-weight:600;letter-spacing:.8px;text-transform:uppercase;margin-bottom:6px;">Monthly Utilization</div>
                                <div class="dg-chart-sm"><canvas id="dgLineChart"></canvas></div>
                            </div>
                        </div>

                        {{-- Financial Picture (RIGHT — 65%) --}}
                        <div class="dg-fin-col">
                            <div class="dg-sec-label"><i class="fas fa-chart-pie fa-xs"></i> Financial Picture</div>

                            {{-- Numbers --}}
                            <div class="dg-fin-nums">
                                <div class="dg-fin-card">
                                    <div class="dg-fin-label">Total Budget</div>
                                    <div class="dg-fin-value" style="color:var(--rd-text1);">{{ number_format($totalBudget) }}</div>
                                </div>
                                <div class="dg-fin-card">
                                    <div class="dg-fin-label">Utilized</div>
                                    <div class="dg-fin-value" style="color:var(--rd-text2);">{{ number_format($utilizedBudget) }}</div>
                                </div>
                                <div class="dg-fin-card">
                                    <div class="dg-fin-label">This Case</div>
                                    <div class="dg-fin-value" style="color:var(--rd-info);">{{ number_format($caseValue) }}</div>
                                </div>
                                <div class="dg-fin-card">
                                    <div class="dg-fin-label">Bal. After</div>
                                    <div class="dg-fin-value" style="color:{{ $balanceAfter < 0 ? 'var(--rd-danger)' : 'var(--rd-success)' }};">{{ number_format($balanceAfter) }}</div>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
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

                            {{-- Bar Chart --}}
                            <div style="margin-top:12px;flex:1;">
                                <div class="dg-chart-wrap"><canvas id="dgBudgetChart"></canvas></div>
                            </div>
                        </div>

                    </div>

                    {{-- 3. ITEMS SUMMARY (boxed) --}}
                    @if($purchase->items && count($purchase->items) > 0)
                    <div>
                        <div class="dg-box">
                            <div style="padding:8px 14px;border-bottom:1px solid var(--rd-border);background:rgba(255,255,255,0.02)">
                                <div class="dg-sec-label" style="margin-bottom:0;"><i class="fas fa-boxes fa-xs"></i> Requested Items Summary</div>
                            </div>

                            {{-- Box header: Firm | Amount | CS Button — all inline --}}
                            <div class="dg-box-hdr" style="align-items:center;">
                                @if($winnerQuote)
                                <div style="display:flex;align-items:center;gap:6px;flex:1;flex-wrap:wrap;">
                                    <i class="fas fa-trophy fa-xs" style="color:var(--rd-success);"></i>
                                    <span class="dg-box-hdr-firm" style="font-size:14px;">{{ $winnerQuote->firm?->frm_name ?? $winnerQuote->qte_firmname }}</span>
                                    <span style="color:var(--rd-text3);font-size:11px;">—</span>
                                    <span class="dg-box-hdr-cost">Rs. {{ number_format($winnerQuote->qte_price, 2) }}</span>
                                </div>
                                <button class="dg-cs-btn" id="dgOpenCSBtn" style="flex-shrink:0;">
                                    <i class="fas fa-balance-scale mr-1"></i> Comparative Statement
                                </button>
                                @else
                                <span class="dg-box-hdr-firm" style="color:var(--rd-text3);">No quotes submitted</span>
                                @endif
                            </div>

                            {{-- Items List (scrollable) --}}
                            <div class="dg-items-wrap" style="max-height:165px;">
                                <table class="dg-items-table">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;">#</th>
                                            <th>Description</th>
                                            <th class="dg-qty-col" style="width:80px;">Qty</th>
                                            <th class="dg-price-col" style="width:120px;">Best Price (Rs)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($purchase->items as $idx => $item)
                                        @php
                                            $itemPrice = $item->pci_price ?? 0;
                                            if($winnerQuote) {
                                                $qPrice = \DB::table('pur.quoteitems')
                                                    ->where('qti_qte_id', $winnerQuote->qte_id)
                                                    ->where('qti_pci_id', $item->pci_id)
                                                    ->value('qti_price');
                                                if($qPrice) $itemPrice = $qPrice;
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $item->pci_serial ?? ($idx+1) }}</td>
                                            <td>{{ $item->pci_desc }}</td>
                                            <td class="dg-qty-col">{{ $item->pci_qty }} {{ $item->pci_qtyunit }}</td>
                                            <td class="dg-price-col">{{ number_format($itemPrice, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Terms / Remarks — strictly inline --}}
                            @if($purchase->pcs_remarks)
                            <div class="dg-terms-row">
                                <span class="dg-terms-label"><i class="fas fa-file-alt fa-xs mr-1"></i>Terms & Remarks:</span>
                                <span class="dg-terms-text">{{ $purchase->pcs_remarks }}</span>
                            </div>
                            @endif

                        </div>
                    </div>
                    @endif

                </div>

                {{-- ============ RIGHT PANE ============ --}}
                <div class="dg-right">

                    @if($returnCount > 0)
                    <div class="dg-return-notice">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        <div>
                            <div style="font-weight:700;font-size:12px;font-family:'Rajdhani',sans-serif;color:var(--rd-danger);">Previously Returned</div>
                            <div style="font-size:11px;color:var(--rd-text2);">Returned {{ $returnCount }} time(s) before reaching you.</div>
                        </div>
                    </div>
                    @endif

                    {{-- Command Decision Panel --}}
                    @include('approvals._action_box')

                    {{-- Approval Trail --}}
                    @include('approvals._decision_trail')

                </div>
            </div>
        </div>
    </section>
</div>

{{-- ===== CS MODAL ===== --}}
<div class="dg-modal-overlay" id="dgCSModal">
    <div class="dg-modal-box">
        <div class="dg-modal-header">
            <div class="dg-modal-title"><i class="fas fa-balance-scale mr-2"></i> Item-wise Comparative Statement</div>
            <button class="dg-modal-close" id="dgCloseModal">&times;</button>
        </div>
        <div class="dg-modal-body">
            <table class="dg-cs-table">
                <thead>
                    <tr>
                        <th rowspan="2">#</th>
                        <th rowspan="2" style="text-align:left;">Description</th>
                        <th rowspan="2">Qty</th>
                        @foreach($sortedQ as $idx => $q)
                            <th colspan="2" class="{{ $idx==0 ? 'dg-wc' : '' }}">
                                {{ $q->firm->frm_name ?? $q->qte_firmname }}
                                @if($idx==0)<span style="font-size:9px;"> &#9733; L1</span>@endif
                            </th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($sortedQ as $idx => $q)
                            <th class="{{ $idx==0 ? 'dg-wc' : '' }}">Unit</th>
                            <th class="{{ $idx==0 ? 'dg-wc' : '' }}">Total</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $item)
                    <tr>
                        <td>{{ $item->pci_serial ?? $loop->iteration }}</td>
                        <td style="text-align:left;">{{ $item->pci_desc }}</td>
                        <td style="color:var(--rd-warning);font-weight:600;">{{ $item->pci_qty }}</td>
                        @foreach($sortedQ as $idx => $q)
                            @php
                                $price = \DB::table('pur.quoteitems')
                                    ->where('qti_qte_id', $q->qte_id)
                                    ->where('qti_pci_id', $item->pci_id)
                                    ->value('qti_price') ?? 0;
                            @endphp
                            <td class="{{ $idx==0 ? 'dg-wtd' : '' }}">{{ $price > 0 ? number_format($price,2) : '-' }}</td>
                            <td class="{{ $idx==0 ? 'dg-wtd' : '' }}">{{ $price > 0 ? number_format($price * $item->pci_qty,2) : '-' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;color:var(--rd-text2);">GRAND TOTAL (PKR)</td>
                        @foreach($sortedQ as $idx => $q)
                            <td colspan="2" class="{{ $idx==0 ? 'dg-wtd' : '' }}">{{ number_format($q->qte_price,2) }}</td>
                        @endforeach
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="dg-modal-footer">
            <button class="btn btn-sm btn-secondary rounded-pill px-4" id="dgCloseModal2">Close</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
(function(){
    const dsU={{ $utilizedBudget }}, dsC={{ $caseValue }}, dsR={{ max(0,$balanceAfter) }};

    setTimeout(function(){
        var pu=document.getElementById('dgProgU'); if(pu) pu.classList.add('anim');
        var pr=document.getElementById('dgProgR'); if(pr) pr.classList.add('anim');
    }, 300);

    const co={
        responsive:true, maintainAspectRatio:false,
        plugins:{
            legend:{display:false},
            tooltip:{backgroundColor:'#0d1f3c',titleColor:'#c9a227',bodyColor:'#9ba8bf',borderColor:'rgba(201,162,39,0.25)',borderWidth:1}
        },
        animation:{duration:1000,easing:'easeOutQuart'}
    };

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
        options:{...co, scales:{
            x:{ticks:{color:'#7a8a9a',font:{size:11}},grid:{color:'rgba(255,255,255,0.04)'}},
            y:{ticks:{color:'#7a8a9a',font:{size:10},callback:v=>'Rs '+Math.round(v/100000)+'L'},grid:{color:'rgba(255,255,255,0.05)'}}
        }}
    });

    new Chart(document.getElementById('dgDonutChart'),{
        type:'doughnut',
        data:{
            labels:['Equipment','Tools','Other'],
            datasets:[{data:[68,22,10],backgroundColor:['rgba(23,162,184,0.8)','rgba(201,162,39,0.8)','rgba(40,167,69,0.8)'],borderColor:'#0d1f3c',borderWidth:2}]
        },
        options:{...co,cutout:'65%',plugins:{...co.plugins,legend:{display:true,position:'bottom',labels:{color:'#9ba8bf',font:{size:10},boxWidth:10,padding:8}}}}
    });

    new Chart(document.getElementById('dgLineChart'),{
        type:'line',
        data:{
            labels:['Oct','Nov','Dec','Jan','Feb','Mar'],
            datasets:[{
                label:'Expenditure', data:[420000,680000,510000,890000,1100000,1245800],
                borderColor:'#c9a227', backgroundColor:'rgba(201,162,39,0.08)',
                pointBackgroundColor:'#c9a227', borderWidth:2, pointRadius:3, tension:0.4, fill:true
            }]
        },
        options:{...co, scales:{
            x:{ticks:{color:'#7a8a9a',font:{size:10}},grid:{color:'rgba(255,255,255,0.04)'}},
            y:{ticks:{color:'#7a8a9a',font:{size:10},callback:v=>'Rs '+Math.round(v/1000)+'K'},grid:{color:'rgba(255,255,255,0.05)'}}
        }}
    });

    // Modal
    const csModal=document.getElementById('dgCSModal');
    document.getElementById('dgOpenCSBtn')?.addEventListener('click',()=>csModal.classList.add('open'));
    document.getElementById('dgCloseModal').addEventListener('click',()=>csModal.classList.remove('open'));
    document.getElementById('dgCloseModal2').addEventListener('click',()=>csModal.classList.remove('open'));
    csModal.addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');});

    // Action form
    const form=document.getElementById('dgActionForm');
    const actionInput=document.getElementById('dgActionMethod');
    const remarks=document.getElementById('dgRemarksField');
    const errEl=document.getElementById('dgRemarksError');
    const reqStar=document.getElementById('dgRemarksReq');

    remarks.addEventListener('input',()=>{
        errEl.style.display='none';
        remarks.style.borderColor='';
        reqStar.style.display='none';
    });

})();
</script>

{{-- Toast Container --}}
<div class="dg-toast-container" id="dgToastContainer"></div>

{{-- Confirm Dialog --}}
<div class="dg-confirm-overlay" id="dgConfirmOverlay">
    <div class="dg-confirm-box">
        <div class="dg-confirm-icon" id="dgConfirmIcon"></div>
        <div class="dg-confirm-title" id="dgConfirmTitle"></div>
        <div class="dg-confirm-msg" id="dgConfirmMsg"></div>
        <div class="dg-confirm-btns">
            <button class="dg-confirm-no" id="dgConfirmNo">Cancel</button>
            <button class="dg-confirm-yes" id="dgConfirmYes">Confirm</button>
        </div>
    </div>
</div>

<script>
// ---- Toast System ----
function dgToast(type, title, msg, duration) {
    duration = duration || 4000;
    const container = document.getElementById('dgToastContainer');
    const icons = { success:'fas fa-check-circle', warning:'fas fa-exclamation-triangle', danger:'fas fa-times-circle', info:'fas fa-info-circle' };
    
    const toast = document.createElement('div');
    toast.className = 'dg-toast dg-toast--' + type;
    toast.style.position = 'relative';
    toast.innerHTML = 
        '<div class="dg-toast-icon"><i class="' + icons[type] + '"></i></div>' +
        '<div class="dg-toast-body">' +
            '<div class="dg-toast-title">' + title + '</div>' +
            '<div class="dg-toast-msg">' + msg + '</div>' +
        '</div>' +
        '<button class="dg-toast-close" onclick="this.closest(\'.dg-toast\').classList.replace(\'show\',\'hide\');setTimeout(()=>this.closest(\'.dg-toast\').remove(),500)">&times;</button>' +
        '<div class="dg-toast-progress" style="animation-duration:' + duration + 'ms"></div>';
    
    container.appendChild(toast);
    requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.add('show')));
    
    setTimeout(() => {
        toast.classList.replace('show', 'hide');
        setTimeout(() => toast.remove(), 500);
    }, duration);
}

// ---- Custom Confirm ----
function dgConfirm(action, callback) {
    const overlay = document.getElementById('dgConfirmOverlay');
    const iconEl = document.getElementById('dgConfirmIcon');
    const titleEl = document.getElementById('dgConfirmTitle');
    const msgEl = document.getElementById('dgConfirmMsg');
    const yesBtn = document.getElementById('dgConfirmYes');
    
    const configs = {
        approve: {
            icon: 'fas fa-check-circle',
            iconBg: 'rgba(40,167,69,0.15)',
            iconColor: '#28a745',
            title: 'Approve Purchase Case?',
            msg: 'This will approve the case and move it forward in the procurement pipeline.',
            btnBg: '#28a745',
            btnText: 'Yes, Approve'
        },
        return: {
            icon: 'fas fa-undo',
            iconBg: 'rgba(255,165,0,0.15)',
            iconColor: '#ffa500',
            title: 'Return Purchase Case?',
            msg: 'This will return the case to the previous level for corrections.',
            btnBg: '#ffa500',
            btnText: 'Yes, Return'
        },
        reject: {
            icon: 'fas fa-times-circle',
            iconBg: 'rgba(220,53,69,0.15)',
            iconColor: '#dc3545',
            title: 'Not Approve This Case?',
            msg: 'This will reject the case. The initiating division will be notified.',
            btnBg: '#dc3545',
            btnText: 'Yes, Not Approved'
        }
    };
    
    const c = configs[action];
    iconEl.innerHTML = '<i class="' + c.icon + '" style="color:' + c.iconColor + '"></i>';
    iconEl.style.background = c.iconBg;
    titleEl.textContent = c.title;
    msgEl.textContent = c.msg;
    yesBtn.style.background = c.btnBg;
    yesBtn.textContent = c.btnText;
    
    overlay.classList.add('open');
    
    // Clone to remove old listeners
    const newYes = yesBtn.cloneNode(true);
    yesBtn.parentNode.replaceChild(newYes, yesBtn);
    const newNo = document.getElementById('dgConfirmNo').cloneNode(true);
    document.getElementById('dgConfirmNo').parentNode.replaceChild(newNo, document.getElementById('dgConfirmNo'));
    
    newYes.addEventListener('click', () => {
        overlay.classList.remove('open');
        callback(true);
    });
    newNo.addEventListener('click', () => {
        overlay.classList.remove('open');
        callback(false);
    });
    overlay.addEventListener('click', function handler(e) {
        if (e.target === overlay) {
            overlay.classList.remove('open');
            callback(false);
            overlay.removeEventListener('click', handler);
        }
    });
}

// ---- Override doAction with toast + confirm ----
(function(){
    const form = document.getElementById('dgActionForm');
    const actionInput = document.getElementById('dgActionMethod');
    const remarks = document.getElementById('dgRemarksField');
    const errEl = document.getElementById('dgRemarksError');
    const reqStar = document.getElementById('dgRemarksReq');

    function doActionNew(act) {
        actionInput.value = act;
        if ((act === 'return' || act === 'reject') && !remarks.value.trim()) {
            remarks.style.borderColor = 'var(--rd-danger)';
            errEl.style.display = 'flex';
            reqStar.style.display = 'inline';
            form.classList.add('dg-shake');
            setTimeout(() => form.classList.remove('dg-shake'), 500);
            remarks.focus();
            dgToast('danger', 'Remarks Required', 'Please enter remarks before returning or rejecting this case.', 3000);
            return;
        }

        dgConfirm(act, function(confirmed) {
            if (!confirmed) return;
            
            const toastMsgs = {
                approve: { type:'success', title:'Case Approved ✓', msg:'Purchase case has been approved successfully.' },
                return:  { type:'warning', title:'Case Returned ↩', msg:'Purchase case has been returned to the previous level.' },
                reject:  { type:'danger',  title:'Case Not Approved ✕', msg:'Purchase case has been rejected and initiator notified.' }
            };
            const t = toastMsgs[act];
            dgToast(t.type, t.title, t.msg, 5000);
            
            document.querySelectorAll('.dg-btn-approve,.dg-btn-return,.dg-btn-reject').forEach(b => {
                b.disabled = true; b.style.opacity = '.5'; b.style.cursor = 'not-allowed';
            });
            
            setTimeout(() => form.submit(), 800);
        });
    }

    document.getElementById('dgBtnApprove').removeEventListener('click', () => {});
    document.getElementById('dgBtnReturn').removeEventListener('click', () => {});
    document.getElementById('dgBtnReject').removeEventListener('click', () => {});
    
    document.getElementById('dgBtnApprove').onclick = () => doActionNew('approve');
    document.getElementById('dgBtnReturn').onclick = () => doActionNew('return');
    document.getElementById('dgBtnReject').onclick = () => doActionNew('reject');
})();
</script>

@endsection
