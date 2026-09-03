@extends('welcome')
@section('content')
<div class="content-wrapper pt-3 pb-5" style="background: var(--rd-bg, #0f172a); min-height: 100vh;">
    <style>
        :root {
            --fin-bg: var(--rd-surface, #1e293b);
            --fin-card-bg: var(--rd-surface2, #0f172a);
            --fin-border: var(--rd-border, #334155);
            --fin-accent: #38bdf8;
            --fin-success: #4ade80;
            --fin-warning: #fbbf24;
            --fin-danger: #f87171;
            --fin-purple: #c084fc;
        }

        .fin-hero-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.98));
            border: 1px solid var(--fin-border);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        .fin-stat-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--fin-border);
            border-radius: 10px;
            padding: 16px 20px;
            transition: all 0.2s ease;
        }
        .fin-stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--fin-accent);
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.15);
        }

        .fin-label {
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #94a3b8;
            margin-bottom: 4px;
        }

        .fin-val-lg {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }

        .fin-table-card {
            background: var(--fin-bg);
            border: 1px solid var(--fin-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .fin-table {
            font-family: 'Rajdhani', sans-serif;
            margin-bottom: 0;
            font-size: 0.92rem;
        }
        .fin-table thead th {
            background: rgba(15, 23, 42, 0.9);
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            padding: 12px 16px;
            border-bottom: 1px solid var(--fin-border);
        }
        .fin-table tbody td {
            padding: 11px 16px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #e2e8f0;
        }
        .fin-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .subhead-gauge-box {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid var(--fin-border);
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }

        .chart-box-main {
            height: 280px;
            position: relative;
        }

        @media print {
            .no-print { display: none !important; }
            .content-wrapper { background: #fff !important; color: #000 !important; }
            .fin-hero-card, .fin-table-card, .fin-stat-card { border: 1px solid #ccc !important; box-shadow: none !important; }
        }
    </style>

    <div class="container-fluid px-3 px-md-4">
        
        {{-- ================= PAGE HEADER & ACTIONS ================= --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom no-print" style="border-color: var(--fin-border) !important;">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <a href="{{ $backUrl ?? route('projects.show', $project->prj_id) }}" class="btn btn-outline-secondary btn-sm rounded-pill mr-3 font-weight-bold" style="padding: 5px 14px;">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Project Details
                </a>
                <div>
                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                        <span class="badge badge-primary px-2.5 py-1 font-weight-bold" style="font-size: 0.8rem; letter-spacing: 0.5px;">CODE: {{ $project->prj_code }}</span>
                        <span class="badge badge-light border text-dark font-weight-bold" style="font-size: 0.75rem;">
                            <i class="fas fa-building mr-1 text-muted"></i> {{ $project->unit?->unt_name ?? 'N/A' }}
                        </span>
                        <span class="badge badge-success px-2 py-0.5 text-uppercase font-weight-bold" style="font-size: 0.72rem;">{{ $project->prj_status }}</span>
                    </div>
                    <h4 class="font-weight-bold text-white mb-0 mt-1" style="font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px;">
                        {{ $project->prj_title }} <span class="text-info font-weight-normal" style="font-size: 1.1rem;">— Financial Intelligence</span>
                    </h4>
                </div>
            </div>

            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                <a href="{{ route('projecthistory', ['project_id' => $project->prj_id]) }}" class="btn btn-outline-info btn-sm font-weight-bold rounded-pill px-3">
                    <i class="fas fa-history mr-1"></i> Log History
                </a>
                <button onclick="window.print()" class="btn btn-outline-secondary btn-sm font-weight-bold rounded-pill px-3">
                    <i class="fas fa-print mr-1"></i> Print Report
                </button>
                <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-primary btn-sm font-weight-bold rounded-pill px-3 shadow-sm">
                    <i class="fas fa-folder-open mr-1"></i> Open Project View
                </a>
            </div>
        </div>

        @if($head)
        {{-- ================= HERO STATS ROW (4 TOP COLUMNS + HIGHLIGHTS) ================= --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="fin-stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fin-label">Total Allocation</span>
                        <i class="fas fa-wallet text-muted opacity-50"></i>
                    </div>
                    <div class="fin-val-lg text-white mt-1">Rs. {{ number_format($head->allocation) }}</div>
                    <small class="text-muted d-block mt-1">Gross Sanctioned Project Cost</small>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="fin-stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fin-label">MTSS Share</span>
                        <i class="fas fa-file-invoice-dollar text-muted opacity-50"></i>
                    </div>
                    <div class="fin-val-lg text-white mt-1">Rs. {{ number_format($head->mtss_share) }}</div>
                    <small class="text-muted d-block mt-1">Mandatory Institutional Deductions</small>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="fin-stat-card h-100" style="border-left: 3.5px solid var(--fin-accent);">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fin-label text-info">RDW Share</span>
                        <i class="fas fa-shield-alt text-info opacity-75"></i>
                    </div>
                    <div class="fin-val-lg text-info mt-1">Rs. {{ number_format($head->rdw_share ?? 0) }}</div>
                    <small class="text-muted d-block mt-1">Allocation after MTSS deduction</small>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
                <div class="fin-stat-card h-100" style="border-left: 3.5px solid var(--fin-warning);">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fin-label" style="color: var(--fin-warning);">CSRF Share</span>
                        <i class="fas fa-hand-holding-usd opacity-75" style="color: var(--fin-warning);"></i>
                    </div>
                    <div class="fin-val-lg mt-1" style="color: var(--fin-warning);">Rs. {{ number_format($head->csrf_share ?? 0) }}</div>
                    <small class="text-muted d-block mt-1">Dedicated CSRF Component</small>
                </div>
            </div>
        </div>

        {{-- ================= MAIN BODY SPLIT: LEFT (TABLES) & RIGHT (CHARTS) ================= --}}
        <div class="row">
            
            {{-- LEFT COLUMN: SNAPSHOT TABLE & RECEIVABLES --}}
            <div class="col-xl-6 col-lg-12 mb-4">
                
                {{-- 1. PROJECT SNAPSHOT TABLE --}}
                <div class="fin-table-card mb-4">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.8); border-color: var(--fin-border) !important;">
                        <h6 class="font-weight-bold text-white mb-0 rajdhani" style="letter-spacing: 1px;">
                            <i class="fas fa-table text-info mr-2"></i> PROJECT FINANCIAL SNAPSHOT
                        </h6>
                        <span class="badge badge-dark border text-muted px-2 py-1 font-weight-bold">FIGURES IN PKR</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table fin-table w-100 m-0">
                            <thead>
                                <tr>
                                    <th class="pl-3">METRIC</th>
                                    <th class="text-right" style="color: #38bdf8;">PROJECT</th>
                                    <th class="text-right" style="color: #fbbf24;">CSRF</th>
                                    @if($showProjectActualSection ?? true)
                                    <th class="text-right pr-3" style="color: #4ade80;">ACTUAL</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                {{-- ALLOCATED ROW --}}
                                <tr style="background: rgba(56, 189, 248, 0.06); border-bottom: 1px solid var(--fin-border);">
                                    <td class="pl-3 font-weight-bold text-white">
                                        <i class="fas fa-coins text-warning mr-1"></i> Allocated
                                    </td>
                                    <td class="text-right font-weight-bold" style="color: #38bdf8; font-size: 1.05rem;">{{ number_format($head->pcc_share ?? 0) }}</td>
                                    <td class="text-right font-weight-bold" style="color: #fbbf24; font-size: 1.05rem;">{{ number_format($head->csrf_share ?? 0) }}</td>
                                    @if($showProjectActualSection ?? true)
                                    <td class="text-right pr-3 font-weight-bold" style="color: #4ade80; font-size: 1.05rem;">{{ number_format($head->allocation ?? 0) }}</td>
                                    @endif
                                </tr>

                                {{-- RECEIVED ROW --}}
                                <tr>
                                    <td class="pl-3 text-muted">Received (Cash Inflow)</td>
                                    <td class="text-right font-weight-bold" style="color: #38bdf8;">{{ number_format($head->pcc_received ?? 0) }}</td>
                                    <td class="text-right font-weight-bold" style="color: #fbbf24;">{{ number_format($head->cf_received ?? 0) }}</td>
                                    @if($showProjectActualSection ?? true)
                                    <td class="text-right pr-3 text-muted">--</td>
                                    @endif
                                </tr>

                                {{-- EXPENDITURE ROW --}}
                                <tr>
                                    <td class="pl-3 text-muted">Expenditure (Spent)</td>
                                    <td class="text-right font-weight-bold text-danger">{{ number_format($head->pcc_expenditure ?? 0) }}</td>
                                    <td class="text-right font-weight-bold text-danger">{{ number_format($head->cf_expenditure ?? 0) }}</td>
                                    @if($showProjectActualSection ?? true)
                                    <td class="text-right pr-3 font-weight-bold" style="color: #4ade80;">{{ number_format($head->prj_expenditure ?? 0) }}</td>
                                    @endif
                                </tr>

                                {{-- BALANCE ROW --}}
                                <tr style="background: rgba(255, 255, 255, 0.03);">
                                    <td class="pl-3 font-weight-bold text-info">Balance</td>
                                    <td class="text-right font-weight-bold text-info">{{ number_format($head->pcc_balance ?? 0) }}</td>
                                    <td class="text-right font-weight-bold text-info">{{ number_format($head->cf_balance ?? 0) }}</td>
                                    @if($showProjectActualSection ?? true)
                                    <td class="text-right pr-3 text-muted">--</td>
                                    @endif
                                </tr>

                                {{-- COMMITMENTS ROW --}}
                                <tr>
                                    <td class="pl-3 text-muted">Commitments</td>
                                    <td class="text-right font-weight-bold text-warning">{{ number_format($head->pcc_commitments ?? 0) }}</td>
                                    <td class="text-right font-weight-bold text-warning">{{ number_format($head->cf_commitments ?? 0) }}</td>
                                    @if($showProjectActualSection ?? true)
                                    <td class="text-right pr-3 font-weight-bold" style="color: #4ade80;">{{ number_format($head->prj_commitments ?? 0) }}</td>
                                    @endif
                                </tr>

                                {{-- IN PROCESS ROW --}}
                                <tr>
                                    <td class="pl-3 text-muted">In Process (IPC)</td>
                                    <td class="text-right text-muted">{{ number_format($head->pcc_in_process ?? 0) }}</td>
                                    <td class="text-right text-muted">{{ number_format($head->cf_in_process ?? 0) }}</td>
                                    @if($showProjectActualSection ?? true)
                                    <td class="text-right pr-3 font-weight-bold" style="color: #4ade80;">{{ number_format($head->prj_in_process ?? 0) }}</td>
                                    @endif
                                </tr>

                                {{-- AVAILABLE ROW --}}
                                <tr style="background: rgba(74, 222, 128, 0.06);">
                                    <td class="pl-3 font-weight-bold text-success">Available Budget</td>
                                    <td class="text-right font-weight-bold text-success" style="font-size: 1rem;">{{ number_format($head->pcc_available ?? 0) }}</td>
                                    <td class="text-right font-weight-bold text-success" style="font-size: 1rem;">{{ number_format($head->cf_available ?? 0) }}</td>
                                    @if($showProjectActualSection ?? true)
                                    <td class="text-right pr-3 text-muted">--</td>
                                    @endif
                                </tr>

                                {{-- YET TO BE RECEIVED ROW --}}
                                <tr>
                                    <td class="pl-3 text-muted">Yet to be Rcvd.</td>
                                    <td class="text-right font-weight-bold" style="color: #38bdf8;">{{ number_format($head->pcc_yet_to_be_received ?? 0) }}</td>
                                    <td class="text-right font-weight-bold" style="color: #fbbf24;">{{ number_format($head->cf_yet_to_be_received ?? 0) }}</td>
                                    @if($showProjectActualSection ?? true)
                                    <td class="text-right pr-3 text-muted">--</td>
                                    @endif
                                </tr>

                                {{-- REMAINING ROW --}}
                                <tr style="background: rgba(255, 255, 255, 0.05); border-top: 1px solid var(--fin-border);">
                                    <td class="pl-3 font-weight-bold text-white" style="font-size: 1rem;">Total Remaining</td>
                                    <td class="text-right font-weight-bold text-white" style="font-size: 1.1rem;">{{ number_format($head->pcc_can_be_spent ?? 0) }}</td>
                                    <td class="text-right font-weight-bold text-white" style="font-size: 1.1rem;">{{ number_format($head->cf_can_be_spent ?? 0) }}</td>
                                    @if($showProjectActualSection ?? true)
                                    <td class="text-right pr-3 font-weight-bold" style="color: #4ade80; font-size: 1.1rem;">{{ number_format($head->prj_remaining ?? 0) }}</td>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 2. MISSION-BASED RECEIVABLES CARD --}}
                <div class="fin-table-card p-4">
                    <h6 class="font-weight-bold text-white mb-3 rajdhani" style="letter-spacing: 1px;">
                        <i class="fas fa-hand-holding-usd text-warning mr-2"></i> MISSION-BASED RECEIVABLES
                    </h6>
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-2 mb-md-0 border-right" style="border-color: var(--fin-border) !important;">
                            <span class="fin-label">Completed Milestones</span>
                            <div class="h5 font-weight-bold text-white rajdhani mb-0">Rs. {{ number_format($head->receivable_completed ?? 0) }}</div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0 border-right" style="border-color: var(--fin-border) !important;">
                            <span class="fin-label">Current Milestone</span>
                            <div class="h5 font-weight-bold text-white rajdhani mb-0">Rs. {{ number_format($head->receivable_current ?? 0) }}</div>
                        </div>
                        <div class="col-md-4">
                            <span class="fin-label text-success font-weight-bold">Available after Rcv.</span>
                            <div class="h5 font-weight-bold text-success rajdhani mb-0">Rs. {{ number_format($head->available_after_receivables ?? 0) }}</div>
                        </div>
                    </div>
                </div>

                {{-- 3. INTER-PROJECT NETTING & LOANS (Conditional) --}}
                @if($showProjectActualSection ?? false)
                <div class="fin-table-card p-4 mt-4" style="border-left: 3.5px solid var(--fin-danger);">
                    <h6 class="font-weight-bold text-danger mb-3 rajdhani" style="letter-spacing: 1px;">
                        <i class="fas fa-exchange-alt mr-2"></i> INTER-PROJECT LOANS & NETTING
                    </h6>
                    <div class="row">
                        <div class="col-md-4 mb-2 mb-md-0 border-right" style="border-color: var(--fin-border) !important;">
                            <span class="fin-label">PCC Own Expenditure</span>
                            <div class="h6 font-weight-bold text-white rajdhani mb-0">Rs. {{ number_format($head->pcc_own_exp ?? 0) }}</div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0 border-right" style="border-color: var(--fin-border) !important;">
                            <span class="fin-label">Loans Given (Others)</span>
                            <div class="h6 font-weight-bold text-warning rajdhani mb-0">Rs. {{ number_format($head->pcc_loans_given ?? 0) }}</div>
                        </div>
                        <div class="col-md-4">
                            <span class="fin-label">Loans Taken (From Others)</span>
                            <div class="h6 font-weight-bold text-danger rajdhani mb-0">Rs. {{ number_format($head->others_loans_taken ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                @endif

            </div>

            {{-- RIGHT COLUMN: SUBHEAD UTILIZATION RINGS, BREAKDOWN & CHARTS --}}
            <div class="col-xl-6 col-lg-12">
                
                {{-- 1. SUBHEAD CATEGORY METRICS GAUGE CARDS --}}
                <div class="fin-table-card p-4 mb-4">
                    <h6 class="font-weight-bold text-white mb-3 rajdhani" style="letter-spacing: 1px;">
                        <i class="fas fa-chart-pie text-success mr-2"></i> SUBHEAD UTILIZATION BREAKDOWN
                    </h6>
                    
                    <div class="row">
                        {{-- EQUIPMENT --}}
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="subhead-gauge-box h-100">
                                <div class="d-flex justify-content-center mb-2">
                                    <svg width="64" height="64" viewBox="0 0 54 54" style="transform: rotate(-90deg);">
                                        <circle cx="27" cy="27" r="22" stroke="rgba(255,255,255,0.08)" stroke-width="5" fill="none" />
                                        <circle cx="27" cy="27" r="22" stroke="#4ade80" stroke-width="5" fill="none"
                                                stroke-dasharray="138.23" stroke-dashoffset="{{ 138.23 * (1 - ($finData['equip_pct'] / 100)) }}"
                                                stroke-linecap="round" />
                                        <text x="27" y="-23" text-anchor="middle" fill="#4ade80" font-size="11" font-weight="bold" font-family="'Rajdhani', sans-serif" style="transform: rotate(90deg);">{{ $finData['equip_pct'] }}%</text>
                                    </svg>
                                </div>
                                <div class="font-weight-bold text-white rajdhani" style="font-size: 1.05rem;">EQUIPMENT</div>
                                <div class="small font-weight-bold text-success rajdhani">Rs. {{ number_format($finData['equip'] ?? 0) }}</div>
                                <small class="text-muted" style="font-size: 10px;">Alloc: {{ number_format($finData['equip_alloc'] ?? 0) }}</small>
                            </div>
                        </div>

                        {{-- HR --}}
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="subhead-gauge-box h-100">
                                <div class="d-flex justify-content-center mb-2">
                                    <svg width="64" height="64" viewBox="0 0 54 54" style="transform: rotate(-90deg);">
                                        <circle cx="27" cy="27" r="22" stroke="rgba(255,255,255,0.08)" stroke-width="5" fill="none" />
                                        <circle cx="27" cy="27" r="22" stroke="#38bdf8" stroke-width="5" fill="none"
                                                stroke-dasharray="138.23" stroke-dashoffset="{{ 138.23 * (1 - ($finData['hr_pct'] / 100)) }}"
                                                stroke-linecap="round" />
                                        <text x="27" y="-23" text-anchor="middle" fill="#38bdf8" font-size="11" font-weight="bold" font-family="'Rajdhani', sans-serif" style="transform: rotate(90deg);">{{ $finData['hr_pct'] }}%</text>
                                    </svg>
                                </div>
                                <div class="font-weight-bold text-white rajdhani" style="font-size: 1.05rem;">HR / PERSONNEL</div>
                                <div class="small font-weight-bold text-info rajdhani">Rs. {{ number_format($finData['hr'] ?? 0) }}</div>
                                <small class="text-muted" style="font-size: 10px;">Alloc: {{ number_format($finData['hr_alloc'] ?? 0) }}</small>
                            </div>
                        </div>

                        {{-- MISC --}}
                        <div class="col-md-4">
                            <div class="subhead-gauge-box h-100">
                                <div class="d-flex justify-content-center mb-2">
                                    <svg width="64" height="64" viewBox="0 0 54 54" style="transform: rotate(-90deg);">
                                        <circle cx="27" cy="27" r="22" stroke="rgba(255,255,255,0.08)" stroke-width="5" fill="none" />
                                        <circle cx="27" cy="27" r="22" stroke="#fbbf24" stroke-width="5" fill="none"
                                                stroke-dasharray="138.23" stroke-dashoffset="{{ 138.23 * (1 - ($finData['misc_pct'] / 100)) }}"
                                                stroke-linecap="round" />
                                        <text x="27" y="-23" text-anchor="middle" fill="#fbbf24" font-size="11" font-weight="bold" font-family="'Rajdhani', sans-serif" style="transform: rotate(90deg);">{{ $finData['misc_pct'] }}%</text>
                                    </svg>
                                </div>
                                <div class="font-weight-bold text-white rajdhani" style="font-size: 1.05rem;">MISCELLANEOUS</div>
                                <div class="small font-weight-bold text-warning rajdhani">Rs. {{ number_format($finData['misc'] ?? 0) }}</div>
                                <small class="text-muted" style="font-size: 10px;">Alloc: {{ number_format($finData['misc_alloc'] ?? 0) }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. CATEGORY METRICS BREAKDOWN TABLE --}}
                @if(count($subheads) > 0)
                <div class="fin-table-card mb-4">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background: rgba(15, 23, 42, 0.8); border-color: var(--fin-border) !important;">
                        <h6 class="font-weight-bold text-white mb-0 rajdhani" style="letter-spacing: 1px;">
                            <i class="fas fa-list-alt text-primary mr-2"></i> DETAILED SUBHEAD BREAKDOWN
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table fin-table w-100 m-0">
                            <thead>
                                <tr>
                                    <th class="pl-3">SUBHEAD</th>
                                    <th class="text-right">EXPENDITURE</th>
                                    <th class="text-right">COMMITMENTS</th>
                                    <th class="text-right pr-3">REMAINING</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subheads as $sh)
                                @php
                                    $shName = is_array($sh) ? ($sh['name'] ?? 'N/A') : ($sh->name ?? 'N/A');
                                    $shExp  = is_array($sh) ? ($sh['expenditure'] ?? 0) : ($sh->expenditure ?? 0);
                                    $shCmt  = is_array($sh) ? ($sh['commitments'] ?? 0) : ($sh->commitments ?? 0);
                                    $shRem  = is_array($sh) ? ($sh['remaining'] ?? 0) : ($sh->remaining ?? 0);
                                @endphp
                                <tr>
                                    <td class="pl-3 font-weight-bold text-white">{{ $shName }}</td>
                                    <td class="text-right text-danger font-weight-bold">{{ number_format($shExp) }}</td>
                                    <td class="text-right text-warning font-weight-bold">{{ number_format($shCmt) }}</td>
                                    <td class="text-right pr-3 font-weight-bold text-success">{{ number_format($shRem) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- 3. INTERACTIVE CHART --}}
                <div class="fin-table-card p-4">
                    <h6 class="font-weight-bold text-white mb-3 rajdhani" style="letter-spacing: 1px;">
                        <i class="fas fa-chart-bar text-info mr-2"></i> CASH FLOW & BUDGET DISTRIBUTION
                    </h6>
                    <div class="chart-box-main">
                        <canvas id="finDetailedChart"></canvas>
                    </div>
                </div>

            </div>

        </div>
        @else
        <div class="fin-table-card p-5 text-center my-4">
            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
            <h5 class="text-white font-weight-bold rajdhani">No Financial Head Linked</h5>
            <p class="text-muted mb-0">This project is not linked to an active financial head in the CEN database yet.</p>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($head)
    const head = @json($head);
    const subheads = @json($subheads);
    
    const canvas = document.getElementById('finDetailedChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Received (Inflow)', 'Expenditure (Spent)', 'Commitments (Active)', 'Remaining (Spendable)'],
                datasets: [{
                    label: 'PKR Amount',
                    data: [head.received || 0, head.expenditure || 0, head.commitments || 0, head.pcc_can_be_spent || head.remaining || 0],
                    backgroundColor: [
                        'rgba(56, 189, 248, 0.4)',
                        'rgba(248, 113, 113, 0.4)',
                        'rgba(251, 191, 36, 0.4)',
                        'rgba(74, 222, 128, 0.4)'
                    ],
                    borderColor: [
                        '#38bdf8',
                        '#f87171',
                        '#fbbf24',
                        '#4ade80'
                    ],
                    borderWidth: 2,
                    borderRadius: 6,
                    barThickness: 38
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Rs. ' + Number(context.raw).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.06)' },
                        ticks: {
                            color: '#94a3b8',
                            font: { family: "'Rajdhani', sans-serif", weight: 'bold' },
                            callback: function(value) {
                                return 'Rs. ' + (value >= 1000000 ? (value/1000000).toFixed(1) + 'M' : (value/1000).toFixed(0) + 'k');
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#cbd5e1',
                            font: { family: "'Rajdhani', sans-serif", weight: 'bold', size: 12 }
                        }
                    }
                }
            }
        });
    }
    @endif
});
</script>
@endpush
@endsection
