@extends('welcome')

@section('content')
<link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

<style>
/* ===== SCOPED TO .dg-page (Exact match with Purchase Cases & Approvals New) ===== */
.dg-page { font-family:'Inter',sans-serif; background-color: var(--rd-bg); min-height: calc(100vh - 60px); }

/* ---- Page Header ---- */
.dg-hdr { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:18px; flex-wrap:wrap; gap:10px; }
.dg-back-btn { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:var(--rd-text2); background:var(--rd-surface); border:1px solid var(--rd-border); padding:5px 13px; border-radius:20px; text-decoration:none !important; transition:all .2s; }
.dg-back-btn:hover { border-color:var(--rd-accent); color:var(--rd-accent); }
.dg-page-title { font-family:'Rajdhani',sans-serif; font-size:20px; font-weight:700; color:var(--rd-text1); letter-spacing:.8px; margin-top:5px; }
.dg-case-badge { background:var(--rd-accent, #5F7858); color:#ffffff; font-family:'Rajdhani',sans-serif; font-size:12px; font-weight:700; padding:4px 12px; border-radius:6px; letter-spacing:1px; display:inline-block; }
.dg-case-date { font-size:11px; color:var(--rd-text3); margin-top:4px; text-align:right; }
.dg-meta-row { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:4px; }
.dg-meta-item { font-size:11px; color:var(--rd-text2); display:flex; align-items:center; gap:4px; }

/* ---- 2-Column Grid (Left details, Right minute / action) ---- */
.dg-grid { display:grid; grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); gap:16px; align-items:start; }
@media(max-width:1300px){ .dg-grid { grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 12px; } }
@media(max-width:1050px){ .dg-grid { grid-template-columns:1fr; } }

/* ---- Section labels ---- */
.dg-sec-label { font-family:'Rajdhani',sans-serif; font-size:12px; font-weight:700; letter-spacing:1.5px; color:var(--rd-accent, #5F7858); text-transform:uppercase; margin-bottom:0; display:flex; align-items:center; gap:7px; }
.dg-sec-label::before { content:''; width:3px; height:12px; background:var(--rd-accent, #5F7858); border-radius:2px; display:inline-block; }

/* ---- Left Container Box ---- */
.dg-box { background:#ffffff; border:1px solid var(--rd-border); border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.03); }
.dg-box-hdr { background:var(--rd-surface2, #f8fafc); padding:10px 14px; border-bottom:1px solid var(--rd-border); display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }

/* ---- Right Pane & Minute Sheet Panel ---- */
.dg-right { display:flex; flex-direction:column; gap:12px; }
.dg-panel-r { background:#ffffff; border:1px solid var(--rd-border); border-radius:10px; overflow:visible; box-shadow:0 1px 3px rgba(0,0,0,0.03); }
.dg-panel-r-hdr { background:var(--rd-surface2, #f8fafc); padding:10px 14px; border-bottom:1px solid var(--rd-border); display:flex; align-items:center; justify-content:space-between; }
.dg-panel-r-title { font-family:'Rajdhani',sans-serif; font-size:13px; font-weight:700; color:#0f172a; letter-spacing:0.8px; text-transform:uppercase; }

/* Scrollbar for comments box */
#conversational-comments-box {
    scrollbar-width: thin !important;
    scrollbar-color: #64748b #f1f5f9 !important;
}
#conversational-comments-box::-webkit-scrollbar {
    width: 8px !important;
}
#conversational-comments-box::-webkit-scrollbar-track {
    background: #f1f5f9 !important;
    border-radius: 4px !important;
}
#conversational-comments-box::-webkit-scrollbar-thumb {
    background: #64748b !important;
    border-radius: 4px !important;
}
#conversational-comments-box::-webkit-scrollbar-thumb:hover {
    background: #334155 !important;
}

/* Action Buttons (Exact match with Purchase Case approvals_new) */
.btn-action-send {
    background: #2563eb !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(37, 99, 235, 0.25);
}
.btn-action-send:hover {
    background: #1d4ed8 !important;
    box-shadow: 0 4px 10px rgba(29, 78, 216, 0.35);
    transform: translateY(-1px);
}

.btn-action-approve {
    flex: 0 0 42px;
    width: 42px;
    height: 40px;
    background: #16a34a !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    overflow: hidden;
    cursor: pointer;
    padding: 0 12px;
}
.btn-action-approve .btn-expand-text {
    max-width: 0;
    opacity: 0;
    margin-left: 0;
    transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease, margin-left 0.3s ease;
    overflow: hidden;
    display: inline-block;
    white-space: nowrap;
}
.btn-action-approve:hover {
    flex: 0 0 auto !important;
    width: auto !important;
    min-width: 145px;
    padding: 0 14px !important;
    background: #15803d !important;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);
    transform: translateY(-1px);
}
.btn-action-approve:hover .btn-expand-text {
    max-width: 130px;
    opacity: 1;
    margin-left: 8px;
}

.btn-action-cancel {
    flex: 0 0 42px;
    width: 42px;
    height: 40px;
    background: #dc2626 !important;
    border: none;
    border-radius: 6px;
    color: #ffffff !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: 'Rajdhani', sans-serif;
    font-size: 13px;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.12);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    overflow: hidden;
    cursor: pointer;
    padding: 0 12px;
}
.btn-action-cancel .btn-expand-text {
    max-width: 0;
    opacity: 0;
    margin-left: 0;
    transition: max-width 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease, margin-left 0.3s ease;
    overflow: hidden;
    display: inline-block;
    white-space: nowrap;
}
.btn-action-cancel:hover {
    flex: 0 0 auto !important;
    width: auto !important;
    min-width: 135px;
    padding: 0 14px !important;
    background: #b91c1c !important;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.35);
    transform: translateY(-1px);
}
.btn-action-cancel:hover .btn-expand-text {
    max-width: 120px;
    opacity: 1;
    margin-left: 8px;
}

.pc-dest-option-item:hover, .pc-dest-option-item.selected {
    background-color: #f1f5f9 !important;
}
</style>

<div class="content-wrapper dg-page">
    <div class="p-3 pt-4">
        <div class="container-fluid">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show font-weight-bold shadow-sm mb-3" role="alert" style="border-radius: 8px; font-size: 13.5px;">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show font-weight-bold shadow-sm mb-3" role="alert" style="border-radius: 8px; font-size: 13.5px;">
                    <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            {{-- 2-Column Grid --}}
            <div class="dg-grid">

                {{-- ========================================================= --}}
                {{-- LEFT PANE: CONSOLIDATED FUND SHIFTING DETAILS             --}}
                {{-- ========================================================= --}}
                <div class="dg-box h-100" style="display:flex; flex-direction:column;">
                    <div class="dg-box-hdr">
                        <div class="dg-sec-label" style="margin-bottom:0;">
                            <i class="fas fa-exchange-alt fa-xs"></i> Shifting of Funds Case #FS-{{ $shift->fsh_rev_id ?: $shift->fsh_id }}
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('fund_shifting.print', $shift->fsh_id) }}" target="_blank" class="btn btn-sm font-weight-bold rajdhani d-inline-flex align-items-center" style="background: #0f172a; color: #ffffff; border: 1px solid #334155; font-size: 11px; letter-spacing: 0.6px; padding: 4px 11px; border-radius: 6px; gap: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.12); text-decoration: none;" title="Print Case Details, Remarks & Attachments">
                                <i class="fas fa-print"></i> PRINT CASE
                            </a>
                        </div>
                    </div>

                    {{-- 1. Case Details Grid --}}
                    <div class="p-3 border-bottom">
                        <div class="row" style="row-gap: 14px;">
                            {{-- Project --}}
                            <div class="col-md-6">
                                <span class="text-muted small text-uppercase font-weight-bold d-block" style="font-size: 11px;">Project</span>
                                <span class="font-weight-bold text-dark" style="font-size: 13.5px;">
                                    [{{ $shift->project->prj_code ?? 'PRJ' }}] {{ $shift->project->prj_title ?? 'Project' }}
                                </span>
                            </div>

                            {{-- Head Code --}}
                            <div class="col-md-6">
                                <span class="text-muted small text-uppercase font-weight-bold d-block" style="font-size: 11px;">Head Code</span>
                                <span class="badge badge-primary px-2.5 py-1 font-weight-bold" style="font-size: 12px;">
                                    {{ $shift->head->hed_code ?: ($shift->project->prj_code ?? 'N/A') }}
                                </span>
                            </div>

                            {{-- Division / Unit --}}
                            <div class="col-md-6">
                                <span class="text-muted small text-uppercase font-weight-bold d-block" style="font-size: 11px;">Division / Unit</span>
                                <span class="font-weight-bold text-dark" style="font-size: 13px;">
                                    @php
                                        $uName = $shift->project?->unit?->unt_name ?? $shift->unit?->unt_name ?? 'Command, Control and Communication (C3)';
                                        $dirPrefix = str_starts_with(strtolower($uName), 'director') ? '' : 'Director ';
                                    @endphp
                                    {{ $dirPrefix }}{{ $uName }}
                                </span>
                            </div>

                            {{-- Transfer Direction --}}
                            <div class="col-md-6">
                                <span class="text-muted small text-uppercase font-weight-bold d-block" style="font-size: 11px;">Transfer Direction</span>
                                <div class="d-flex align-items-center mt-0.5">
                                    <span class="badge badge-danger px-2.5 py-1 font-weight-bold mr-1">{{ $shift->fsh_from_sbh_name }}</span>
                                    <i class="fas fa-arrow-right text-muted mx-1" style="font-size: 11px;"></i>
                                    <span class="badge badge-success px-2.5 py-1 font-weight-bold ml-1">{{ $shift->fsh_to_sbh_name }}</span>
                                </div>
                            </div>

                            {{-- Proposed Amount --}}
                            <div class="col-md-6">
                                <span class="text-muted small text-uppercase font-weight-bold d-block" style="font-size: 11px;">Proposed Shifting Amount</span>
                                <span class="font-weight-800 text-dark" style="font-size: 1.2rem;">
                                    Rs. {{ number_format($shift->fsh_approved_amount ?: $shift->fsh_proposed_amount, 2) }}
                                </span>
                            </div>

                            {{-- Current Stage / Holder --}}
                            <div class="col-md-6">
                                <span class="text-muted small text-uppercase font-weight-bold d-block" style="font-size: 11px;">Workflow Pipeline Stage</span>
                                <span class="badge badge-light border text-dark font-weight-bold px-2.5 py-1" style="font-size: 12px;">
                                    <i class="fas fa-layer-group text-primary mr-1"></i> {{ $shift->fsh_current_stage }} ({{ $shift->fsh_status }})
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Present State vs After Approval Comparison Table --}}
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-dark rajdhani" style="font-size: 13.5px;">
                                <i class="fas fa-balance-scale mr-1.5 text-primary"></i> Present State vs After Approval Fund Shifting
                            </span>
                            <span class="badge badge-success px-2 py-0.5 font-weight-bold" style="font-size: 10.5px;">
                                Balanced Transfer
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-size: 0.88rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Subhead Role</th>
                                        <th>Subhead Name</th>
                                        <th class="text-right">Present Allocation</th>
                                        <th class="text-center">Proposed Shift</th>
                                        <th class="text-right">After Approval</th>
                                        <th class="text-right">Available After</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Source Subhead Row --}}
                                    <tr>
                                        <td>
                                            <span class="badge badge-danger px-2 py-1 font-weight-bold">
                                                <i class="fas fa-minus mr-1"></i> Source (From)
                                            </span>
                                        </td>
                                        <td class="font-weight-700 text-dark">
                                            {{ $shift->fsh_from_sbh_name }}
                                        </td>
                                        <td class="text-right font-weight-600">
                                            Rs. {{ number_format($shift->fsh_from_alloc_before, 2) }}
                                        </td>
                                        <td class="text-center font-weight-bold text-danger">
                                            -Rs. <span id="disp_shift_from">{{ number_format($shift->fsh_approved_amount ?: $shift->fsh_proposed_amount, 2) }}</span>
                                        </td>
                                        <td class="text-right font-weight-800 text-dark" id="disp_after_from">
                                            Rs. {{ number_format($shift->fsh_from_alloc_after, 2) }}
                                        </td>
                                        <td class="text-right font-weight-600 text-muted">
                                            Rs. {{ number_format(max(0, ($fromLive['can_be_spent'] ?? 0) - ($shift->fsh_status === 'Implemented' ? 0 : ($shift->fsh_approved_amount ?: $shift->fsh_proposed_amount))), 2) }}
                                        </td>
                                    </tr>

                                    {{-- Destination Subhead Row --}}
                                    <tr>
                                        <td>
                                            <span class="badge badge-success px-2 py-1 font-weight-bold">
                                                <i class="fas fa-plus mr-1"></i> Target (To)
                                            </span>
                                        </td>
                                        <td class="font-weight-700 text-dark">
                                            {{ $shift->fsh_to_sbh_name }}
                                        </td>
                                        <td class="text-right font-weight-600">
                                            Rs. {{ number_format($shift->fsh_to_alloc_before, 2) }}
                                        </td>
                                        <td class="text-center font-weight-bold text-success">
                                            +Rs. <span id="disp_shift_to">{{ number_format($shift->fsh_approved_amount ?: $shift->fsh_proposed_amount, 2) }}</span>
                                        </td>
                                        <td class="text-right font-weight-800 text-dark" id="disp_after_to">
                                            Rs. {{ number_format($shift->fsh_to_alloc_after, 2) }}
                                        </td>
                                        <td class="text-right font-weight-600 text-success">
                                            Rs. {{ number_format(($toLive['can_be_spent'] ?? 0) + ($shift->fsh_status === 'Implemented' ? 0 : ($shift->fsh_approved_amount ?: $shift->fsh_proposed_amount)), 2) }}
                                        </td>
                                    </tr>

                                    {{-- Net Total Row --}}
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="2" class="text-dark">Net Head Budget Impact</td>
                                        <td class="text-right text-dark">Rs. {{ number_format($shift->fsh_from_alloc_before + $shift->fsh_to_alloc_before, 2) }}</td>
                                        <td class="text-center text-primary">Rs. 0.00 (Balanced)</td>
                                        <td class="text-right text-dark">Rs. {{ number_format($shift->fsh_from_alloc_after + $shift->fsh_to_alloc_after, 2) }}</td>
                                        <td class="text-right text-success"><i class="fas fa-check-circle mr-1"></i> Preserved</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 3. Complete Project Subheads Breakdown Table --}}
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-dark rajdhani" style="font-size: 13.5px;">
                                <i class="fas fa-coins mr-1.5 text-warning"></i> Complete Project Subheads Breakdown
                            </span>
                            <span class="badge badge-light border font-weight-bold" style="font-size: 10.5px;">
                                {{ count($subheads) }} Subheads
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-size: 0.84rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Subhead</th>
                                        <th class="text-right">Allocation</th>
                                        <th class="text-right">Expenditure</th>
                                        <th class="text-right">Commitments</th>
                                        <th class="text-right">In Process</th>
                                        <th class="text-right">Remaining</th>
                                        <th class="text-right">Shiftable Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subheads as $sh)
                                        <tr class="{{ $sh['sbh_id'] == $shift->fsh_from_sbh_id ? 'table-danger' : ($sh['sbh_id'] == $shift->fsh_to_sbh_id ? 'table-success' : '') }}">
                                            <td class="font-weight-700">
                                                {{ $sh['name'] }}
                                                @if($sh['sbh_id'] == $shift->fsh_from_sbh_id)
                                                    <span class="badge badge-danger ml-1" style="font-size: 10px;">Source</span>
                                                @elseif($sh['sbh_id'] == $shift->fsh_to_sbh_id)
                                                    <span class="badge badge-success ml-1" style="font-size: 10px;">Target</span>
                                                @endif
                                            </td>
                                            <td class="text-right">Rs. {{ number_format($sh['allocation'], 2) }}</td>
                                            <td class="text-right">Rs. {{ number_format($sh['expenditure'], 2) }}</td>
                                            <td class="text-right">Rs. {{ number_format($sh['commitments'], 2) }}</td>
                                            <td class="text-right">Rs. {{ number_format($sh['in_process'], 2) }}</td>
                                            <td class="text-right font-weight-600">Rs. {{ number_format($sh['remaining'], 2) }}</td>
                                            <td class="text-right font-weight-bold text-success">Rs. {{ number_format($sh['max_shiftable'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Implementation Details Banner (if fulfilled) --}}
                    @if($shift->fsh_status === 'Implemented')
                        <div class="alert alert-success shadow-sm mx-3 mb-3" style="border-radius: 8px; border-left: 5px solid #0E9F6E;">
                            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 8px;">
                                <div>
                                    <h6 class="font-weight-800 text-success mb-1">
                                        <i class="fas fa-check-double mr-1"></i> Fund Shifting Implemented & Fulfilled by IT
                                    </h6>
                                    <div class="font-weight-500 text-dark small">
                                        Executed on {{ $shift->fsh_implemented_at ? $shift->fsh_implemented_at->format('d M Y, h:i A') : 'N/A' }} 
                                        by {{ $shift->implementer->acc_username ?? 'IT Directorate' }}.
                                    </div>
                                    @if($shift->fsh_implementation_notes)
                                        <div class="font-weight-600 text-dark mt-2 p-2 bg-white rounded border" style="font-size: 0.85rem;">
                                            <i class="fas fa-quote-left text-muted mr-1"></i> {{ $shift->fsh_implementation_notes }}
                                        </div>
                                    @endif
                                </div>
                                <span class="badge badge-success px-3 py-1.5 font-weight-bold" style="font-size: 12px;">
                                    Audit ID #FS-{{ $shift->fsh_rev_id }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ========================================================= --}}
                {{-- RIGHT PANE: MINUTE SHEET & ACTION BOX                     --}}
                {{-- (Exact Structure matching Purchase Case approvals_new)    --}}
                {{-- ========================================================= --}}
                <div class="dg-right">
                    <div class="dg-panel-r" style="overflow: visible;">
                        <div class="dg-panel-r-hdr py-2 px-3 d-flex justify-content-between align-items-center" style="position: relative; border-top-left-radius: 9px; border-top-right-radius: 9px;">
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <i class="fas fa-file-alt text-primary" style="font-size: 13px;"></i>
                                <span class="dg-panel-r-title" style="font-size: 12px; font-weight: 700; color: #0f172a !important; letter-spacing: 0.5px; text-transform: uppercase;">Minute</span>
                                <span class="badge badge-light border text-muted px-2 py-0.5" style="font-size: 10px;">
                                    {{ $shift->decisions->where('fsd_action', '!=', 'Draft Created')->count() }} Entries
                                </span>
                            </div>

                            {{-- Case Attachments Dropdown Trigger on Far Right of Minute Header (Exact Purchase Case UI) --}}
                            <div class="dropdown" id="fshCaseAttachmentsDropdownWrap">
                                <button type="button" class="btn btn-xs font-weight-bold rajdhani px-2 py-1 d-flex align-items-center dropdown-toggle shadow-none" id="btnCaseAttachmentsDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="font-size: 11px; height: 26px; border-radius: 6px; gap: 5px; background: #ffffff; border: 1.5px solid #cbd5e1; color: #1e293b; cursor: pointer;" title="View or Add Case Attachments">
                                    <i class="fas fa-paperclip text-primary" style="font-size: 11.5px;"></i>
                                    <span>CASE ATTACHMENTS</span>
                                    <span class="badge badge-primary badge-pill ml-1" id="fshCaseAttCountBadge" style="font-size: 9.5px; padding: 2px 6px;">{{ ($attachments ?? collect())->count() }}</span>
                                </button>

                                <div class="dropdown-menu dropdown-menu-right shadow-lg p-0" aria-labelledby="btnCaseAttachmentsDropdown" style="width: 380px; max-width: 92vw; border-radius: 8px; border: 1.5px solid #cbd5e1; z-index: 1060; margin-top: 5px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;">
                                    {{-- Dropdown Header with Add Button --}}
                                    <div class="d-flex justify-content-between align-items-center py-2 px-3 border-bottom" style="background: #f8fafc;">
                                        <div class="d-flex align-items-center font-weight-bold text-dark rajdhani" style="font-size: 12px; gap: 6px;">
                                            <i class="fas fa-paperclip text-primary"></i>
                                            <span>ATTACHED CASE FILES</span>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-primary font-weight-bold rajdhani px-2 py-0.5 d-flex align-items-center" data-toggle="modal" data-target="#modalAddFundShiftAttachment" style="font-size: 11px; height: 23px; border-radius: 4px; background: var(--rd-accent, #5F7858) !important; border: none; gap: 4px;" title="Upload New Attachment">
                                            <i class="fas fa-plus"></i> <span>ADD</span>
                                        </button>
                                    </div>

                                    {{-- Dropdown Body: Attachments List --}}
                                    <div class="px-3 py-2" id="fshCaseAttachmentsList" style="font-size: 11.5px; max-height: 250px; overflow-y: auto;">
                                        @if(($attachments ?? collect())->count() > 0)
                                            @foreach($attachments as $fIdx => $fDoc)
                                                @php
                                                    $fName = trim((string)($fDoc->iat_type ?: ''));
                                                    if (empty($fName)) {
                                                        $fName = basename(str_replace('\\', '/', $fDoc->iat_path));
                                                    }
                                                    $ext = strtolower(pathinfo($fDoc->iat_path, PATHINFO_EXTENSION));
                                                @endphp
                                                <div class="d-flex justify-content-between align-items-center py-1.5 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color: #f1f5f9 !important;">
                                                    <div class="d-flex align-items-center overflow-hidden mr-2" style="flex: 1; min-width: 0; gap: 6px;">
                                                        <span class="text-muted font-weight-bold flex-shrink-0" style="font-size: 10px; width: 16px;">{{ $fIdx + 1 }}.</span>
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
                                                        <span class="text-truncate font-weight-bold text-dark" style="font-size: 11.5px;" title="{{ $fName }}">
                                                            {{ $fName }}
                                                        </span>
                                                    </div>
                                                    <div class="d-flex align-items-center flex-shrink-0" style="gap: 4px;">
                                                        <a href="{{ route('universal.attachment.view', ['module' => 'ina', 'id' => $fDoc->iat_id]) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 font-weight-bold d-inline-flex align-items-center" style="font-size: 11px; height: 22px; border-radius: 4px; gap: 4px;" title="View {{ $fName }}">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        @if(\App\Http\Controllers\AttachmentController::canManageAttachment(auth()->user(), 'ina', $shift->fsh_id, $fDoc))
                                                            <label for="replace_ina_dropdown_{{ $fDoc->iat_id }}" class="btn btn-xs btn-outline-warning py-0 px-1 font-weight-bold mb-0 d-inline-flex align-items-center" style="font-size: 10px; height: 22px; border-radius: 4px; cursor: pointer;" title="Replace {{ $fName }}">
                                                                <i class="fas fa-sync-alt"></i>
                                                            </label>
                                                            <input type="file" id="replace_ina_dropdown_{{ $fDoc->iat_id }}" style="display:none;" onchange="window.rdwisDropdownReplace('ina', '{{ $fDoc->iat_id }}', '{{ addslashes($fName) }}', this)">
                                                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1 font-weight-bold d-inline-flex align-items-center" style="font-size: 10px; height: 22px; border-radius: 4px;" onclick="window.rdwisDropdownDelete('ina', '{{ $fDoc->iat_id }}', '{{ addslashes($fName) }}', this)" title="Delete {{ $fName }}">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-3 text-muted" id="fshNoAttachmentsMsg" style="font-size: 11px;">
                                                <i class="fas fa-folder-open text-muted mr-1"></i> No case attachments uploaded yet.
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-2 border-top bg-light text-center" style="border-color: #e2e8f0 !important;">
                                        <button type="button" class="btn btn-xs btn-outline-success font-weight-bold w-100 py-1 d-flex align-items-center justify-content-center" data-toggle="modal" data-target="#modalAddFundShiftAttachment" style="font-size: 11px; border-radius: 4px; gap: 5px;">
                                            <i class="fas fa-plus"></i> <span>ATTACH NEW DOCUMENT</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3" id="conversational-comments-box" style="max-height: calc(100vh - 160px); overflow-y: auto;">
                            
                            {{-- ===================================================== --}}
                            {{-- ACTION BOX: Strict Lock & Pending Action Resolution   --}}
                            {{-- ===================================================== --}}
                            @php
                                $u = Auth::user();
                                $userArea = strtolower(trim((string) ($u?->acc_untarea ?? '')));
                                $userAccId = (int) ($u?->acc_id ?? 0);
                                $userAreaCode = $u->acc_desigshort ?: ($u->acc_untarea ?: 'USER');

                                $isFinalized = in_array(strtolower(trim($shift->fsh_status)), ['implemented', 'cancelled']);

                                // Check latest decision
                                $latestDecision = $shift->decisions->sortByDesc('fsd_id')->first();
                                $iTookLatestAction = $latestDecision && ((int)$latestDecision->fsd_acc_id === $userAccId);

                                // Role determinations
                                $isCommand = $u->isMdDdgDg();
                                $isFinance = $u->isFinance();
                                $isIt = in_array($userArea, ['it', 'is'], true) || (int)($u->acc_unt_id ?? 0) === 860000;
                                $isDivision = $u->isDivision();

                                // Calculate $isCaseWithMe (Strict Lock: Only user holding case can act)
                                $isCaseWithMe = false;

                                if (!$isFinalized && !$iTookLatestAction) {
                                    if (!empty($shift->fsh_current_acc_id)) {
                                        if ((int)$shift->fsh_current_acc_id === $userAccId) {
                                            $isCaseWithMe = true;
                                        } elseif ($u->isCentralDepartment()) {
                                            $isCaseWithMe = true;
                                        }
                                    } else {
                                        $stage = strtoupper(trim((string)$shift->fsh_current_stage));
                                        if ($stage === 'DIVISION') {
                                            $isCaseWithMe = ($isDivision && ((int)$shift->fsh_unt_id === (int)$u->acc_unt_id || (int)$shift->fsh_created_by === $userAccId));
                                        } elseif ($stage === 'FINANCE') {
                                            $isCaseWithMe = $isFinance;
                                        } elseif ($stage === 'IT') {
                                            $isCaseWithMe = $isIt;
                                        } elseif ($stage === 'MD') {
                                            $isCaseWithMe = $u->isMd();
                                        } elseif ($stage === 'DDG') {
                                            $isCaseWithMe = $u->isDdg();
                                        } elseif ($stage === 'DG') {
                                            $isCaseWithMe = $u->isDg();
                                        } elseif ($stage === 'DLOG') {
                                            $isCaseWithMe = (method_exists($u, 'isDlog') && $u->isDlog()) || in_array($userArea, ['log', 'dlog']);
                                        }

                                        if ($u->isCentralDepartment()) {
                                            $isCaseWithMe = true;
                                        }
                                    }
                                }

                                // ── Paragraph Running Number Calculation (Chronological Order by fsd_id) ──
                                $validDecisions = $shift->decisions->where('fsd_action', '!=', 'Draft Created')->sortBy('fsd_id')->values();
                                $trailRunningMap = [];
                                $currSeq = 1;
                                foreach($validDecisions as $d) {
                                    $trailRunningMap[$d->fsd_id] = $currSeq;
                                    $rawRemarks = (string)$d->fsd_remarks;
                                    $count = 1;
                                    if (strpos($rawRemarks, '<li') !== false) {
                                        $count = max(1, substr_count($rawRemarks, '<li'));
                                    } elseif (!empty(trim(strip_tags($rawRemarks)))) {
                                        $lines = array_filter(preg_split('/\r\n|\r|\n/', trim(strip_tags($rawRemarks))), fn($line) => trim($line) !== '');
                                        $count = max(1, count($lines));
                                    }
                                    $currSeq += $count;
                                }
                                $nextParagraphNumber = $currSeq;
                            @endphp

                            {{-- IF CASE IS WITH ME: SHOW ACTIVE ACTION BOX --}}
                            @if($isCaseWithMe)
                                <div class="mb-4 pb-3 border-bottom" style="border-bottom: 1px dashed #cbd5e1 !important;">
                                    {{-- User identity line (Exact match with approvals_new._action_box) --}}
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="font-weight-bold rajdhani text-dark" style="font-size: 14px;">
                                            <i class="fas fa-user-circle text-primary mr-1"></i> {{ $u->acc_name ?: $u->acc_username }} 
                                            <span class="text-muted small ml-1" style="font-weight: 600;">({{ strtoupper($userAreaCode) }})</span>
                                            <span class="ml-2 pl-2 border-left border-secondary font-weight-bold" style="font-size: 10px; color: var(--rd-accent, #5F7858); letter-spacing: 0.5px;">
                                                <i class="fas fa-pen-nib mr-1"></i> SCRUTINY & ACTION
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Form: Forward / Action --}}
                                    <form id="authorityActionForm" method="POST" action="{{ route('fund_shifting.forward', $shift->fsh_id) }}">
                                        @csrf
                                        <input type="hidden" name="action" id="formActionInput" value="forward">
                                        <input type="hidden" name="target_destination" id="pcTargetDestinationInput" value="">
                                        <input type="hidden" name="remarks" id="remarksHiddenInput">

                                        {{-- Remarks Textarea & Draft Bar --}}
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap" style="gap: 6px;">
                                                <span class="text-dark small rajdhani font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px;">
                                                    <i class="fas fa-pen-nib mr-1 text-primary"></i> REMARKS & SCRUTINY NOTES
                                                </span>
                                                <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                                                    <button type="button" class="btn btn-xs btn-outline-danger font-weight-bold px-2 py-0.5" id="btnClearRemarks" style="font-size: 11px; border-radius: 4px;">
                                                        <i class="fas fa-times mr-1"></i> Clear
                                                    </button>
                                                    <span class="text-muted font-italic" style="font-size: 10.5px;">
                                                        <i class="fas fa-arrows-alt-v mr-0.5"></i> Drag corner to resize
                                                    </span>
                                                </div>
                                            </div>
                                            <textarea id="inlineRemarks" class="form-control" placeholder="Type your remarks or scrutiny observations here..." required style="background: #ffffff; color: #0f172a; font-family: 'Arial', sans-serif; font-size: 13px; min-height: 105px; height: 105px; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; outline: none; box-shadow: inset 0 1px 2px rgba(0,0,0,0.04); resize: vertical; width: 100%;">{{ $nextParagraphNumber }}. </textarea>
                                        </div>

                                        {{-- Quick Remarks Bar --}}
                                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 11px;">
                                            <span class="text-dark font-weight-bold rajdhani">
                                                <i class="fas fa-bolt text-warning mr-1"></i> QUICK REMARKS: <span class="badge badge-light border font-weight-bold">0/7</span>
                                            </span>
                                            <div class="btn-group btn-group-xs">
                                                <button class="btn btn-xs btn-outline-secondary py-0 px-1" type="button"><i class="fas fa-plus"></i></button>
                                                <button class="btn btn-xs btn-outline-secondary py-0 px-1" type="button"><i class="fas fa-edit"></i></button>
                                            </div>
                                        </div>
                                        <div class="small text-muted font-italic mb-3" style="font-size: 10.5px;">
                                            No shortcuts added. Click + to create your custom quick remarks.
                                        </div>

                                        {{-- If User is Finance: Editable Values Box (Finance can adjust values before approval) --}}
                                        @if($isFinance && $shift->fsh_status !== 'Implemented')
                                            <div class="p-2.5 mb-3 bg-light border rounded" style="border-left: 3px solid #2563eb !important;">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <label class="font-weight-bold text-dark small mb-0" style="font-size: 11px;">
                                                        <i class="fas fa-edit text-primary mr-1"></i> ADJUST SHIFTING AMOUNT (PKR)
                                                    </label>
                                                    <span class="badge badge-light border font-weight-bold" style="font-size: 10px;">Finance Verification</span>
                                                </div>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text font-weight-bold">Rs.</span>
                                                    </div>
                                                    <input type="number" step="0.01" min="1" name="edited_amount" id="finance_edited_amount" class="form-control font-weight-bold text-dark" value="{{ $shift->fsh_approved_amount ?: $shift->fsh_proposed_amount }}">
                                                </div>
                                                <div id="varianceNotification" class="text-danger font-weight-bold mt-1 d-none" style="font-size: 10px;">
                                                    * Amount modified from Rs. {{ number_format($shift->fsh_proposed_amount, 2) }}. Diff note will be auto-generated with remarks.
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Send / Forward To Destination Dropdown (Searchable Downward Menu) --}}
                                        @if(!($isIt && $shift->fsh_status === 'Approved'))
                                            <div class="form-group mb-3 position-relative" id="pcDestDropdownContainer">
                                                <label class="font-weight-bold text-muted small mb-1 d-flex justify-content-between" style="font-size: 10px; text-transform: uppercase;">
                                                    <span><i class="fas fa-paper-plane text-primary mr-1"></i> SEND / FORWARD TO DESTINATION: <span class="text-muted font-italic font-weight-normal" style="font-size: 9px; text-transform: none;">(Only if forwarding)</span></span>
                                                    <span class="text-muted font-italic" style="font-size: 9px; text-transform: none;">Search department, division, or director</span>
                                                </label>
                                                
                                                {{-- Display Toggle Box --}}
                                                <div id="pcDestDropdownToggle" class="form-control form-control-sm d-flex align-items-center justify-content-between" style="font-size: 12px; font-weight: 600; border-radius: 6px; border-color: #cbd5e1; height: 38px; cursor: pointer; background: #ffffff; user-select: none;">
                                                    <span id="pcDestSelectedLabel" class="text-muted text-truncate font-weight-normal">
                                                        <i class="fas fa-search mr-1.5 text-muted"></i> -- Select Destination Department / Authority --
                                                    </span>
                                                    <i class="fas fa-chevron-down text-muted ml-2" id="pcDestDropdownChevron" style="font-size: 11px; transition: transform 0.2s;"></i>
                                                </div>

                                                {{-- Downward Dropdown Menu with Sticky Search --}}
                                                <div id="pcDestDropdownMenu" class="shadow-lg border bg-white" style="display: none; position: absolute; top: 100% !important; bottom: auto !important; left: 0; right: 0; z-index: 1050; margin-top: 3px; border-radius: 8px; border-color: #cbd5e1 !important; box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;">
                                                    <div class="p-2 border-bottom bg-light">
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text bg-white border-right-0" style="border-color: #cbd5e1;"><i class="fas fa-search text-muted" style="font-size: 11px;"></i></span>
                                                            </div>
                                                            <input type="text" id="pcDestSearchInput" class="form-control border-left-0" placeholder="Type department, division, or director name..." style="font-size: 12px; border-color: #cbd5e1;" autocomplete="off">
                                                        </div>
                                                    </div>

                                                    <div id="pcDestItemsList" style="max-height: 230px; overflow-y: auto; padding: 4px 0;">
                                                        @foreach($destinations as $destCode => $dest)
                                                            @php
                                                                $searchKeywords = strtolower($dest['name'] . ' ' . ($dest['director'] ?? '') . ' ' . ($dest['desig'] ?? '') . ' ' . $destCode);
                                                            @endphp
                                                            <div class="pc-dest-option-item px-3 py-2" data-code="{{ $destCode }}" data-name="{{ $dest['name'] }}" data-search="{{ $searchKeywords }}" style="cursor: pointer; transition: background 0.15s; border-bottom: 1px solid #f1f5f9;">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <span class="text-dark font-weight-bold" style="font-size: 12.5px;">{{ $dest['name'] }}</span>
                                                                    <span class="badge badge-light border text-muted px-1.5 py-0.5" style="font-size: 9.5px; border-radius: 4px;">{{ $dest['badge'] ?? $destCode }}</span>
                                                                </div>
                                                                @if(!empty($dest['director']))
                                                                    <div class="text-muted text-truncate" style="font-size: 11px; margin-top: 1px;">
                                                                        <i class="fas fa-user-tie text-secondary mr-1" style="font-size: 9.5px;"></i> {{ $dest['director'] }}
                                                                        @if(!empty($dest['desig']))
                                                                            <span class="text-muted font-weight-normal">&bull; {{ $dest['desig'] }}</span>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                        <div id="pcDestNoResults" class="p-3 text-center text-muted small" style="display: none;">
                                                            <i class="fas fa-info-circle mr-1"></i> No matching authority found.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Action Buttons Row --}}
                                        <div class="d-flex align-items-center" style="gap: 8px; width: 100%;">
                                            @if($isIt && $shift->fsh_status === 'Approved')
                                                {{-- SO IT Implementation: writes remarks and clicks Implement button below --}}
                                                <button type="button" id="btnExecuteImplement" class="btn btn-success font-weight-bold flex-grow-1 shadow-sm" style="height: 42px; font-size: 13.5px; letter-spacing: 0.5px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px;">
                                                    <i class="fas fa-bolt mr-2"></i>
                                                    <span class="rajdhani">IMPLEMENT FUND SHIFT</span>
                                                </button>
                                            @else
                                                {{-- Left: Prominent Send Case Button --}}
                                                <button type="button" id="btnSendCase" class="btn-action-send flex-grow-1" style="height: 40px; font-size: 13.5px; letter-spacing: 0.6px; display: inline-flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-paper-plane mr-2"></i>
                                                    <span class="font-weight-bold rajdhani">SEND CASE</span>
                                                </button>

                                                {{-- Approve Button (Finance & Command: "approve ka button hu finacne key ps") --}}
                                                @if(($isFinance || $isCommand) && $shift->fsh_status !== 'Approved' && $shift->fsh_status !== 'Implemented')
                                                    <button type="button" id="btnApproveCase" class="btn-action-approve" title="Approve Fund Shifting">
                                                        <i class="fas fa-check"></i>
                                                        <span class="btn-expand-text rajdhani font-weight-bold">APPROVE CASE</span>
                                                    </button>
                                                @endif

                                                {{-- Compact Cancel Button --}}
                                                <button type="button" id="btnCancelCase" class="btn-action-cancel" title="Cancel Fund Shifting Case">
                                                    <i class="fas fa-times"></i>
                                                    <span class="btn-expand-text rajdhani font-weight-bold">CANCEL CASE</span>
                                                </button>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            @elseif($isFinalized)
                                {{-- CASE COMPLETED / CANCELLED --}}
                                <div class="p-3 mb-3 bg-light border rounded text-center">
                                    <span class="badge badge-{{ $shift->fsh_status === 'Implemented' ? 'success' : 'danger' }} px-3 py-1 font-weight-bold" style="font-size: 12px;">
                                        Case {{ $shift->fsh_status }}
                                    </span>
                                    <div class="text-muted small mt-1 font-weight-500">No further scrutiny actions pending on this case.</div>
                                </div>
                            @else
                                {{-- CASE LOCKED FOR THIS USER (Pending with another authority) --}}
                                <div class="p-3 mb-3 bg-light border rounded text-center">
                                    <div class="text-muted small font-weight-bold">
                                        <i class="fas fa-lock text-secondary mr-1.5"></i>
                                        Case is currently with <strong class="text-dark">{{ $shift->fsh_current_holder ?: ($shift->fsh_current_stage . ' (' . $shift->fsh_status . ')') }}</strong>.
                                    </div>
                                    <div class="text-muted mt-0.5 font-italic" style="font-size: 11px;">
                                        Action is locked for your account until routed back to you.
                                    </div>
                                </div>
                            @endif

                            {{-- ===================================================== --}}
                            {{-- MINUTE ENTRIES TRAIL (Recent on Top, Accurate Paras) --}}
                            {{-- ===================================================== --}}
                            <div class="mt-2">
                                @forelse($validDecisions->sortByDesc('fsd_id') as $decision)
                                    @php
                                        $act = strtolower($decision->fsd_action);
                                        $badgeColor = '#2563eb';
                                        $actionVerb = 'Forwarded';

                                        if(str_contains($act, 'approved')) {
                                            $badgeColor = '#16a34a';
                                            $actionVerb = 'Approved';
                                        } elseif(str_contains($act, 'returned')) {
                                            $badgeColor = '#f59e0b';
                                            $actionVerb = 'Returned';
                                        } elseif(str_contains($act, 'cancelled')) {
                                            $badgeColor = '#dc2626';
                                            $actionVerb = 'Cancelled';
                                        } elseif(str_contains($act, 'implemented')) {
                                            $badgeColor = '#0E9F6E';
                                            $actionVerb = 'Implemented';
                                        } elseif(str_contains($act, 'released')) {
                                            $badgeColor = '#2563eb';
                                            $actionVerb = 'Released';
                                        }

                                        $toDisplay = $decision->fsd_to_stage;
                                        $startNumber = $trailRunningMap[$decision->fsd_id] ?? 1;
                                        $rawRemarks = $decision->fsd_remarks;
                                        $hasHtmlLi = !empty($rawRemarks) && strpos($rawRemarks, '<li') !== false;
                                        $hasRemarks = !empty(trim(strip_tags((string)$rawRemarks)));

                                        if ($hasHtmlLi) {
                                            $innerLis = preg_replace('/<\/?(ol|ul)[^>]*>/i', '', $rawRemarks);
                                            $trailHtml = '<ol start="' . $startNumber . '" style="margin-bottom:0; padding-left:18px; color: #1e293b;">' . $innerLis . '</ol>';
                                        } elseif ($hasRemarks) {
                                            $cleanLines = array_filter(preg_split('/\r\n|\r|\n/', trim(strip_tags((string)$rawRemarks))), fn($l) => trim($l) !== '');
                                            $lis = '';
                                            foreach($cleanLines as $line) {
                                                $p = trim(preg_replace('/^\d+\.\s*/', '', $line));
                                                if (!empty($p)) {
                                                    $lis .= '<li>' . e($p) . '</li>';
                                                }
                                            }
                                            $trailHtml = '<ol start="' . $startNumber . '" style="margin-bottom:0; padding-left:18px; color: #1e293b;">' . ($lis ?: '<li>' . e($rawRemarks) . '</li>') . '</ol>';
                                        } else {
                                            $trailHtml = '<ol start="' . $startNumber . '" style="margin-bottom:0; padding-left:18px; color: #1e293b;"><li>Case ' . strtolower($actionVerb) . ' without additional remarks.</li></ol>';
                                        }
                                    @endphp

                                    <div class="mb-4 pb-2" id="user-comment-{{ $decision->fsd_id }}">
                                        <div class="d-flex align-items-center justify-content-between mb-1 border-bottom pb-1" style="border-bottom: 1px dashed #cbd5e1 !important;">
                                            <div class="font-weight-bold rajdhani text-dark" style="font-size: 13.5px; color: #0f172a !important;">
                                                <i class="fas fa-user-circle text-primary mr-1"></i> {{ $decision->fsd_user_name }} 
                                                <span class="text-muted small ml-1" style="font-weight: 600;">({{ strtoupper($decision->fsd_role ?: 'User') }})</span>
                                                <span class="ml-2 pl-2 border-left border-secondary font-weight-bold" style="font-size: 11px; color: {{ $badgeColor }}; letter-spacing: 0.5px;">
                                                    <i class="fas fa-caret-right mr-1"></i>{{ strtoupper($actionVerb) }}@if(!empty($toDisplay) && !in_array($act, ['implemented by it', 'cancelled']))<span class="ml-1" style="text-transform: none; font-size: 11px; color: #334155; font-weight: 600;"> to <strong class="text-dark">{{ $toDisplay }}</strong></span>@endif
                                                </span>
                                            </div>
                                            <span class="text-muted" style="font-size: 10px; font-weight: 600;">
                                                {{ $decision->fsd_dtg ? $decision->fsd_dtg->format('d M Y, h:i A') : '' }}
                                            </span>
                                        </div>
                                        <div class="mt-2" style="line-height: 1.5; font-size: 13px; color: #1e293b !important; padding-left: 5px;">
                                            {!! $trailHtml !!}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted small py-4">
                                        <i class="fas fa-pen-fancy fa-2x text-muted mb-2 opacity-50"></i>
                                        <div>No minutes recorded yet. Minutes will appear once released.</div>
                                    </div>
                                @endforelse
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- MODAL: Previous Fund Shifts & Revisions --}}
<div class="modal fade" id="pastShiftsModal" tabindex="-1" role="dialog" aria-labelledby="pastShiftsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 10px; border: 1px solid var(--rd-border);">
            <div class="modal-header bg-light py-2 px-3">
                <h6 class="modal-title font-weight-800 text-dark" id="pastShiftsModalLabel">
                    <i class="fas fa-history text-info mr-2"></i> Previous Funds Shifted & Revisions
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="p-2.5 bg-light border-bottom">
                    <div class="font-weight-700 text-dark small">
                        Project: [{{ $shift->project->prj_code ?? 'PRJ' }}] {{ $shift->project->prj_title ?? 'Project' }}
                    </div>
                    <div class="text-muted small">Head Code: {{ $shift->head->hed_code ?: ($shift->project->prj_code ?? 'N/A') }}</div>
                </div>

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-hover mb-0" style="font-size: 0.84rem;">
                        <thead class="bg-light">
                            <tr>
                                <th>Case / Audit ID</th>
                                <th>Direction</th>
                                <th class="text-right">Shift Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pastShifts as $ps)
                                <tr class="{{ $ps->fsh_id === $shift->fsh_id ? 'table-warning' : '' }}">
                                    <td class="font-weight-700">
                                        {{ $ps->case_number }}
                                        @if($ps->fsh_id === $shift->fsh_id)
                                            <span class="badge badge-warning text-dark ml-1" style="font-size: 10px;">Current</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-danger font-weight-600">{{ $ps->fsh_from_sbh_name }}</span>
                                        <i class="fas fa-arrow-right mx-1 text-muted" style="font-size: 10px;"></i>
                                        <span class="text-success font-weight-600">{{ $ps->fsh_to_sbh_name }}</span>
                                    </td>
                                    <td class="text-right font-weight-bold text-dark">
                                        Rs. {{ number_format($ps->fsh_approved_amount ?: $ps->fsh_proposed_amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge badge-light border font-weight-bold">{{ $ps->fsh_status }}</span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $ps->created_at ? $ps->created_at->format('d M Y') : 'N/A' }}
                                    </td>
                                    <td class="text-right">
                                        @if($ps->fsh_id !== $shift->fsh_id)
                                            <a href="{{ route('fund_shifting.show', $ps->fsh_id) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11px;">
                                                View
                                            </a>
                                        @else
                                            <span class="text-muted small">Active</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted font-weight-600">
                                        No prior fund shifts recorded for this project.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-1.5">
                <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const originalProposed = parseFloat("{{ $shift->fsh_proposed_amount }}") || 0;
    const fromAllocBefore = parseFloat("{{ $shift->fsh_from_alloc_before }}") || 0;
    const toAllocBefore = parseFloat("{{ $shift->fsh_to_alloc_before }}") || 0;
    const currentNextNum = parseInt("{{ $nextParagraphNumber }}") || 1;

    function formatNumber(num) {
        return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ── Searchable Destination Dropdown ──
    const pcDestContainer = document.getElementById('pcDestDropdownContainer');
    const pcDestToggle = document.getElementById('pcDestDropdownToggle');
    const pcDestMenu = document.getElementById('pcDestDropdownMenu');
    const pcDestSearch = document.getElementById('pcDestSearchInput');
    const pcDestLabel = document.getElementById('pcDestSelectedLabel');
    const pcDestHiddenInput = document.getElementById('pcTargetDestinationInput');
    const pcDestChevron = document.getElementById('pcDestDropdownChevron');
    const pcDestItems = document.querySelectorAll('.pc-dest-option-item');
    const pcDestNoResults = document.getElementById('pcDestNoResults');

    function openPcDestDropdown() {
        if (!pcDestMenu) return;
        pcDestMenu.style.display = 'block';
        if (pcDestChevron) pcDestChevron.style.transform = 'rotate(180deg)';
        if (pcDestSearch) {
            pcDestSearch.value = '';
            filterPcDestItems('');
            setTimeout(() => pcDestSearch.focus(), 50);
        }
    }

    function closePcDestDropdown() {
        if (!pcDestMenu) return;
        pcDestMenu.style.display = 'none';
        if (pcDestChevron) pcDestChevron.style.transform = 'rotate(0deg)';
    }

    function filterPcDestItems(q) {
        let matchCount = 0;
        const query = (q || '').toLowerCase().trim();
        pcDestItems.forEach(item => {
            const searchData = item.getAttribute('data-search') || '';
            if (query === '' || searchData.includes(query)) {
                item.style.display = 'block';
                matchCount++;
            } else {
                item.style.display = 'none';
            }
        });
        if (pcDestNoResults) {
            pcDestNoResults.style.display = matchCount === 0 ? 'block' : 'none';
        }
    }

    if (pcDestToggle) {
        pcDestToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (pcDestMenu.style.display === 'block') {
                closePcDestDropdown();
            } else {
                openPcDestDropdown();
            }
        });
    }

    if (pcDestSearch) {
        pcDestSearch.addEventListener('input', function() {
            filterPcDestItems(this.value);
        });
        pcDestSearch.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    pcDestItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const code = this.getAttribute('data-code');
            const name = this.getAttribute('data-name');
            if (pcDestHiddenInput) pcDestHiddenInput.value = code;
            if (pcDestLabel) {
                pcDestLabel.innerHTML = `<span class="text-dark font-weight-bold"><i class="fas fa-check-circle text-success mr-1"></i> ${name}</span>`;
            }
            pcDestItems.forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            closePcDestDropdown();
        });
    });

    document.addEventListener('click', function(e) {
        if (pcDestContainer && !pcDestContainer.contains(e.target)) {
            closePcDestDropdown();
        }
    });

    // ── Numbering auto-increment on Focus in Remarks ──
    const inlineRemarks = document.getElementById('inlineRemarks');
    if (inlineRemarks) {
        inlineRemarks.addEventListener('focus', function() {
            if (this.value.trim() === '') {
                this.value = currentNextNum + ". ";
            }
        });

        inlineRemarks.addEventListener('keydown', function(e) {
            const selectionStart = this.selectionStart;
            const text = this.value;
            const lastNewline = text.lastIndexOf('\n', selectionStart - 1);
            const lineStart = lastNewline === -1 ? 0 : lastNewline + 1;
            const currentLine = text.substring(lineStart, selectionStart);
            const match = currentLine.match(/^\d+\. /);

            if (match && selectionStart < lineStart + match[0].length) {
                if (e.key === 'Backspace' || e.key === 'Delete' || (e.key.length === 1 && e.key !== 'Enter')) {
                    e.preventDefault();
                    return;
                }
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                if (currentLine.trim().length > (match ? match[0].trim().length : 0)) {
                    const matches = text.match(/^\d+(?=\.)/gm);
                    const nextNum = matches && matches.length > 0 ? Math.max(...matches.map(Number)) + 1 : (currentNextNum + 1);
                    const newNumber = "\n" + nextNum + ". ";
                    const before = text.substring(0, selectionStart);
                    const after = text.substring(selectionStart);
                    this.value = before + newNumber + after;
                    this.selectionStart = this.selectionEnd = before.length + newNumber.length;
                }
            }
        });
    }

    // ── Clear Remarks Button ──
    $('#btnClearRemarks').on('click', function() {
        if (inlineRemarks) {
            inlineRemarks.value = currentNextNum + ". ";
            inlineRemarks.focus();
        }
    });

    // ── Finance Live Recalculation on Value Adjustment Input ──────
    $('#finance_edited_amount').on('input', function() {
        const editedAmt = parseFloat($(this).val()) || 0;
        const diff = Math.abs(editedAmt - originalProposed);

        if (diff > 0.009) {
            $('#varianceNotification').removeClass('d-none');
        } else {
            $('#varianceNotification').addClass('d-none');
        }

        // Live recalculate table values
        $('#disp_shift_from').text(formatNumber(editedAmt));
        $('#disp_shift_to').text(formatNumber(editedAmt));

        $('#disp_after_from').text('Rs. ' + formatNumber(fromAllocBefore - editedAmt));
        $('#disp_after_to').text('Rs. ' + formatNumber(toAllocBefore + editedAmt));
    });

    function prepareRemarksHtml() {
        if (!inlineRemarks) return false;
        let remarks = inlineRemarks.value.trim();
        let lines = remarks.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        let cleanedLines = lines.map(line => line.replace(/^\d+\.\s*/, '').trim()).filter(l => l.length > 0);

        if (cleanedLines.length === 0) {
            return false;
        }

        let liItems = cleanedLines.map(line => `<li>${line}</li>`).join('');
        let finalHtml = `<ol start="${currentNextNum}">${liItems}</ol>`;

        $('#remarksHiddenInput').val(finalHtml);
        return true;
    }

    // ── IT Execution Button Submit (Execute Implementation) ───────
    $('#btnExecuteImplement').on('click', function(e) {
        e.preventDefault();
        prepareRemarksHtml();

        Swal.fire({
            title: 'Implement Fund Shifting',
            text: 'Are you sure you want to implement this approved fund shift into the live databases?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Execute Shift',
            confirmButtonColor: '#0E9F6E',
            cancelButtonColor: '#94a3b8'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('#authorityActionForm');
                form.attr('action', "{{ route('fund_shifting.implement', $shift->fsh_id) }}");
                form.submit();
            }
        });
    });

    // ── SEND CASE Button Submit (Forward to Authority) ───────────
    $('#btnSendCase').on('click', function(e) {
        e.preventDefault();
        const hasValidRemarks = prepareRemarksHtml();
        const targetDest = $('#pcTargetDestinationInput').val();

        if (!hasValidRemarks) {
            Swal.fire({
                title: 'Remarks Required',
                text: 'Please write remarks or scrutiny observations before sending the case.',
                icon: 'warning'
            });
            $('#inlineRemarks').focus();
            return;
        }

        if (!targetDest) {
            Swal.fire({
                title: 'Destination Required',
                text: 'Please select a recipient department or authority to send the case.',
                icon: 'warning'
            });
            openPcDestDropdown();
            return;
        }

        Swal.fire({
            title: 'Send / Forward Case',
            text: 'Are you sure you want to send this fund shifting case?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Send Case',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#94a3b8'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#authorityActionForm').submit();
            }
        });
    });

    // ── Finance or Command Approve Button Submit ─────────────────
    $('#btnApproveCase').on('click', function(e) {
        e.preventDefault();
        const hasValidRemarks = prepareRemarksHtml();
        const editedAmt = parseFloat($('#finance_edited_amount').val()) || originalProposed;
        const isEdited = Math.abs(editedAmt - originalProposed) > 0.009;

        if (isEdited && !hasValidRemarks) {
            Swal.fire({
                title: 'Remarks Mandatory',
                text: 'You have adjusted the fund shifting amount to Rs. ' + formatNumber(editedAmt) + '. Detailed remarks are mandatory.',
                icon: 'warning'
            });
            $('#inlineRemarks').focus();
            return;
        }

        if (!hasValidRemarks) {
            $('#remarksHiddenInput').val(`<ol start="${currentNextNum}"><li>Fund shifting request approved as proposed.</li></ol>`);
        }

        Swal.fire({
            title: 'Approve Fund Shifting',
            text: 'Are you sure you want to approve this fund shifting request and route it to IT for implementation?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#94a3b8'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = $('#authorityActionForm');
                form.attr('action', "{{ route('fund_shifting.approve', $shift->fsh_id) }}");
                form.submit();
            }
        });
    });

    // ── Cancel Case Button ───────────────────────────────────────
    $('#btnCancelCase').on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Cancel Fund Shifting Request',
            input: 'textarea',
            inputLabel: 'Reason for Cancellation (Optional)',
            inputPlaceholder: 'Enter cancellation reason...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Cancel Case',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#94a3b8'
        }).then((result) => {
            if (result.isConfirmed) {
                const cancelForm = $('<form>', {
                    action: "{{ route('fund_shifting.cancel', $shift->fsh_id) }}",
                    method: 'POST'
                });
                cancelForm.append($('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }));
                cancelForm.append($('<input>', { type: 'hidden', name: 'remarks', value: result.value || '' }));
                $('body').append(cancelForm);
                cancelForm.submit();
            }
        });
    });

    // ── Fund Shifting Attachment Upload Form Handler ────────────
    $('#formAddFundShiftAttachment').on('submit', async function(e) {
        e.preventDefault();
        const $btn = $('#btnSubmitFundShiftAttachment');
        const origHtml = $btn.html();
        const fileInput = document.getElementById('fshFileInput');
        const docTitle = document.getElementById('fshDocTitleInput').value.trim();

        if (!fileInput.files.length) {
            Swal.fire({ icon: 'warning', title: 'File Required', text: 'Please choose a document file to attach.' });
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1" role="status"></span> Uploading...');

        const fd = new FormData(this);

        try {
            const res = await fetch("{{ route('universal.attachment.upload') }}", {
                method: 'POST',
                body: fd,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            $btn.prop('disabled', false).html(origHtml);

            if (data.success) {
                $('#modalAddFundShiftAttachment').modal('hide');
                this.reset();

                // Increment badge count
                const $badge = $('#fshCaseAttCountBadge');
                const currentCount = parseInt($badge.text().trim(), 10) || 0;
                $badge.text(currentCount + 1);

                // Remove empty state placeholder if present
                $('#fshNoAttachmentsMsg').remove();

                const slotId = data.slot_id;
                const viewUrl = data.url;
                const ext = (fileInput.files[0]?.name.split('.').pop() || '').toLowerCase();
                let iconClass = 'far fa-file-alt text-secondary';
                if (ext === 'pdf') iconClass = 'far fa-file-pdf text-danger';
                else if (['doc', 'docx'].includes(ext)) iconClass = 'far fa-file-word text-primary';
                else if (['xls', 'xlsx'].includes(ext)) iconClass = 'far fa-file-excel text-success';
                else if (['png', 'jpg', 'jpeg'].includes(ext)) iconClass = 'far fa-file-image text-info';

                const newIndex = currentCount + 1;
                const itemHtml = `
                    <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom" style="border-color: #f1f5f9 !important;">
                        <div class="d-flex align-items-center overflow-hidden mr-2" style="flex: 1; min-width: 0; gap: 6px;">
                            <span class="text-muted font-weight-bold flex-shrink-0" style="font-size: 10px; width: 16px;">${newIndex}.</span>
                            <i class="${iconClass} flex-shrink-0" style="font-size: 12px;"></i>
                            <span class="text-truncate font-weight-bold text-dark" style="font-size: 11.5px;" title="${docTitle}">
                                ${docTitle}
                            </span>
                        </div>
                        <div class="d-flex align-items-center flex-shrink-0" style="gap: 4px;">
                            <a href="${viewUrl}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 font-weight-bold d-inline-flex align-items-center" style="font-size: 11px; height: 22px; border-radius: 4px; gap: 4px;" title="View ${docTitle}">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <label for="replace_ina_dropdown_${slotId}" class="btn btn-xs btn-outline-warning py-0 px-1 font-weight-bold mb-0 d-inline-flex align-items-center" style="font-size: 10px; height: 22px; border-radius: 4px; cursor: pointer;" title="Replace ${docTitle}">
                                <i class="fas fa-sync-alt"></i>
                            </label>
                            <input type="file" id="replace_ina_dropdown_${slotId}" style="display:none;" onchange="window.rdwisDropdownReplace('ina', '${slotId}', '${docTitle.replaceAll("'", "\\'")}', this)">
                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1 font-weight-bold d-inline-flex align-items-center" style="font-size: 10px; height: 22px; border-radius: 4px;" onclick="window.rdwisDropdownDelete('ina', '${slotId}', '${docTitle.replaceAll("'", "\\'")}', this)" title="Delete ${docTitle}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#fshCaseAttachmentsList').append(itemHtml);

                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Attachment uploaded successfully!',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            } else {
                throw new Error(data.message || 'Failed to upload attachment.');
            }
        } catch (err) {
            $btn.prop('disabled', false).html(origHtml);
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Upload Failed', text: err.message || 'Could not upload attachment.' });
            } else {
                alert(err.message || 'Error uploading file');
            }
        }
    });
});

window.rdwisDropdownReplace = window.rdwisDropdownReplace || function(module, id, docType, inputEl) {
    if (!inputEl.files || !inputEl.files[0]) return;
    const file = inputEl.files[0];

    const doUpload = () => {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('doc_type', docType);
        formData.append('_token', '{{ csrf_token() }}');

        fetch(`/universal-attachment/${module}/${id}/replace`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            inputEl.value = '';
            if (data.success) {
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || `"${docType}" replaced successfully!`,
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
                setTimeout(() => window.location.reload(), 1000);
            } else {
                throw new Error(data.message || 'Replace failed');
            }
        })
        .catch(err => {
            inputEl.value = '';
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Replace Failed', text: err.message });
            } else {
                alert('Error: ' + err.message);
            }
        });
    };

    if (window.Swal) {
        Swal.fire({
            title: 'Replace Document?',
            text: `Are you sure you want to replace "${docType}" with "${file.name}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            confirmButtonText: 'Yes, Replace',
            cancelButtonText: 'Cancel'
        }).then(r => {
            if (r.isConfirmed) doUpload();
            else inputEl.value = '';
        });
    } else {
        if (confirm(`Are you sure you want to replace "${docType}" with "${file.name}"?`)) doUpload();
        else inputEl.value = '';
    }
};

window.rdwisDropdownDelete = window.rdwisDropdownDelete || function(module, id, docType, btnEl) {
    const doDelete = () => {
        const originalBtnHtml = btnEl.innerHTML;
        btnEl.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
        btnEl.disabled = true;

        fetch(`/universal-attachment/${module}/${id}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ _token: '{{ csrf_token() }}' })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = btnEl.closest('.d-flex.justify-content-between');
                if (row) row.remove();
                const $badge = $('#fshCaseAttCountBadge');
                const cur = parseInt($badge.text().trim(), 10) || 1;
                $badge.text(Math.max(0, cur - 1));
                if (window.Swal) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || `"${docType}" deleted.`,
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            } else {
                throw new Error(data.message || 'Delete failed');
            }
        })
        .catch(err => {
            btnEl.innerHTML = originalBtnHtml;
            btnEl.disabled = false;
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'Delete Failed', text: err.message });
            } else {
                alert('Error: ' + err.message);
            }
        });
    };

    if (window.Swal) {
        Swal.fire({
            title: 'Delete Attachment?',
            text: `Are you sure you want to delete "${docType}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then(r => {
            if (r.isConfirmed) doDelete();
        });
    } else {
        if (confirm(`Are you sure you want to delete "${docType}"?`)) doDelete();
    }
};
</script>
@endpush

{{-- Modal Add Fund Shift Attachment --}}
<div class="modal fade" id="modalAddFundShiftAttachment" tabindex="-1" role="dialog" aria-labelledby="modalAddFundShiftAttachmentLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 440px;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 8px; overflow: hidden;">
            <div class="modal-header py-2.5 px-3 bg-light border-bottom">
                <h6 class="modal-title font-weight-bold rajdhani text-dark mb-0" id="modalAddFundShiftAttachmentLabel" style="font-size: 13.5px; letter-spacing: 0.5px;">
                    <i class="fas fa-paperclip text-primary mr-1"></i> ATTACH CASE FILE (FS-{{ $shift->fsh_id }})
                </h6>
                <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close" style="font-size: 20px; line-height: 1;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddFundShiftAttachment" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="module" value="ina">
                <input type="hidden" name="object_id" value="{{ $shift->fsh_id }}">
                <div class="modal-body p-3">
                    <div class="form-group mb-2.5">
                        <label class="rajdhani font-weight-bold text-dark mb-1" style="font-size: 12px; letter-spacing: 0.3px;">DOCUMENT TITLE / TYPE <span class="text-danger">*</span></label>
                        <input type="text" name="doc_type" id="fshDocTitleInput" class="form-control form-control-sm" placeholder="e.g. Justification Note, Approval Letter, Reallocation Sheet" required style="border-radius: 4px; font-size: 12px;">
                    </div>
                    <div class="form-group mb-1">
                        <label class="rajdhani font-weight-bold text-dark mb-1" style="font-size: 12px; letter-spacing: 0.3px;">SELECT FILE <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="fshFileInput" class="form-control-file border p-1.5" required style="border-radius: 4px; font-size: 11.5px; background: #f8fafc;" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Supported: PDF, Word, Excel, Images (Max: 20MB)</small>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold px-3 py-1" data-dismiss="modal" style="border-radius: 4px;">Cancel</button>
                    <button type="submit" id="btnSubmitFundShiftAttachment" class="btn btn-xs btn-primary font-weight-bold px-3 py-1" style="border-radius: 4px;">
                        <i class="fas fa-upload mr-1"></i> Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
