@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap');

    .finance-hub {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg, #f8fafc) !important;
        min-height: 100vh;
        color: var(--rd-text1, #0f172a);
        padding-top: 15px;
        padding-bottom: 60px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    /* Cross-browser flex gap utilities */
    .gap-1 { gap: 4px !important; }
    .gap-2 { gap: 8px !important; }
    .gap-3 { gap: 16px !important; }
    .gap-4 { gap: 24px !important; }

    /* Custom color tokens */
    .text-purple { color: #7c3aed !important; }
    .badge-purple { background: #f3e8ff !important; color: #7e22ce !important; }
    .text-teal { color: #0d9488 !important; }
    .badge-teal { background: #ccfbf1 !important; color: #0f766e !important; }

    /* Executive Cyber Glass Cards */
    .card-fin {
        background: var(--rd-surface, #ffffff);
        border: 1.5px solid var(--rd-border, #e2e8f0);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .card-fin:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        border-color: #cbd5e1;
    }

    /* KPI Accent Borders */
    .kpi-blue { border-top: 4px solid #0284c7; }
    .kpi-green { border-top: 4px solid #16a34a; }
    .kpi-red { border-top: 4px solid #dc2626; }
    .kpi-cyan { border-top: 4px solid #0891b2; }
    .kpi-amber { border-top: 4px solid #d97706; }
    .kpi-purple { border-top: 4px solid #7c3aed; }
    .kpi-gold { border-top: 4px solid #b45309; }

    .kpi-header {
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #64748b;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 6px;
    }
    .kpi-main-val {
        font-size: 23px;
        font-weight: 800;
        line-height: 1.15;
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
        color: #0f172a;
    }
    .kpi-sub-text {
        font-size: 11.5px;
        color: #64748b;
        font-weight: 600;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Pill Badges */
    .pill-count {
        background: #e0f2fe;
        color: #0369a1;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 12px;
        letter-spacing: 0.3px;
    }
    .pill-count.danger {
        background: #fee2e2;
        color: #b91c1c;
    }
    .pill-count.success {
        background: #dcfce7;
        color: #15803d;
    }
    .pill-count.warning {
        background: #fef3c7;
        color: #b45309;
    }

    /* Tables */
    .table-fin {
        margin: 0;
        color: #0f172a;
    }
    .table-fin th {
        background: #f8fafc !important;
        border-bottom: 2px solid #cbd5e1 !important;
        border-top: none !important;
        color: #334155 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-size: 12.5px;
        font-weight: 800;
        padding: 10px 14px !important;
        vertical-align: middle;
    }
    .table-fin td {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 10px 14px !important;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-fin tbody tr:hover {
        background: #f8fafc !important;
    }

    /* Mode Pill */
    .mode-switch-btn {
        font-size: 12px;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155 !important;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s;
    }
    .mode-switch-btn:hover {
        background: #f1f5f9;
        color: #0f172a !important;
    }
    .mode-switch-btn.active {
        background: #0284c7 !important;
        color: #ffffff !important;
        border-color: #0284c7 !important;
        box-shadow: 0 1px 3px rgba(2, 132, 199, 0.3);
    }
    .mode-switch-btn.active i {
        color: #ffffff !important;
    }

    /* Micro drill buttons */
    .btn-drill-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 4px;
        font-size: 9.5px;
        border: 1px solid transparent;
        transition: all 0.15s ease;
        margin-left: 4px;
        text-decoration: none !important;
    }
    .btn-drill-red { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
    .btn-drill-red:hover { background: #fee2e2; color: #b91c1c; }
    .btn-drill-amber { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .btn-drill-amber:hover { background: #fef3c7; color: #b45309; }
    .btn-drill-blue { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .btn-drill-blue:hover { background: #dbeafe; color: #1d4ed8; }

    .chart-container-card {
        height: 270px;
        position: relative;
    }
</style>

<div class="content-wrapper finance-hub px-4">
    {{-- Header Section: Title, Horizon Toggle & Refresh --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 pb-3 border-bottom" style="border-color: #e2e8f0 !important; gap: 12px;">
        <div>
            <div class="d-flex align-items-center mb-1" style="gap: 8px;">
                <span class="badge badge-primary px-3 py-1 font-weight-bold rajdhani" style="font-size: 11.5px; letter-spacing: 0.6px; background: #0284c7;">FINANCE DIRECTORATE</span>
                <span class="text-muted" style="font-size: 12.5px; font-weight: 600;">• Central Accounts &amp; Treasury Intelligence</span>
            </div>
            <h3 class="font-weight-bold text-dark rajdhani m-0" style="font-size: 26px; font-weight: 800; letter-spacing: 0.5px;">
                <i class="fas fa-coins text-warning mr-2"></i>Finance Operations Command Center
            </h3>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
            {{-- Mode Switcher (All Data vs My Dept) --}}
            <div class="d-inline-flex align-items-center bg-white border p-1 rounded" style="border: 1.5px solid #cbd5e1 !important; gap: 4px;">
                <a href="{{ route('fin.dashboard', ['mode' => 'm']) }}" 
                   class="mode-switch-btn {{ $mode === 'm' ? 'active' : '' }}" 
                   title="All Projects &amp; General Accounts Horizon">
                    <i class="fas fa-globe mr-1"></i> All Data
                </a>
                <a href="{{ route('fin.dashboard', ['mode' => 's']) }}" 
                   class="mode-switch-btn {{ $mode === 's' ? 'active' : '' }}" 
                   title="My Department Bound Horizon">
                    <i class="fas fa-building mr-1"></i> My Dept
                </a>
            </div>

            {{-- Refresh Button --}}
            <a href="{{ route('fin.dashboard', ['mode' => $mode, 'refresh' => 1]) }}" 
               class="btn btn-sm btn-outline-secondary font-weight-bold d-inline-flex align-items-center" 
               style="border-radius: 6px; height: 32px; font-size: 12px; border: 1.5px solid #cbd5e1; gap: 6px;" 
               title="Bust cache and refresh live finance metrics">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </a>
        </div>
    </div>

    {{-- Interactive Division & Head Filter Command Bar (Replaces Quick Actions) --}}
    <div class="card-fin p-3 mb-4 bg-white shadow-sm" style="border-left: 4px solid #0284c7;">
        <div class="row align-items-center">
            {{-- Division Filter --}}
            <div class="col-md-5 mb-2 mb-md-0">
                <label class="small font-weight-bold text-dark mb-1.5 d-flex align-items-center rajdhani" style="font-size: 13.5px; letter-spacing: 0.5px;">
                    <i class="fas fa-sitemap text-primary mr-2"></i> FILTER BY DIVISION:
                </label>
                <select id="global-division-filter" class="form-control form-control-sm bg-white border" style="border-radius: 8px; height: 38px; font-weight: 700; font-size: 13px; border-color: #cbd5e1;">
                    <option value="all">All Divisions (Macro View)</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div->unt_namesh }}">{{ $div->unt_name }} ({{ $div->unt_namesh }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Project / Head Filter --}}
            <div class="col-md-5 mb-2 mb-md-0">
                <label class="small font-weight-bold text-dark mb-1.5 d-flex align-items-center rajdhani" style="font-size: 13.5px; letter-spacing: 0.5px;">
                    <i class="fas fa-project-diagram text-info mr-2"></i> FILTER BY PROJECT / OPERATING HEAD:
                </label>
                <select id="global-head-filter" class="form-control form-control-sm bg-white border" style="border-radius: 8px; height: 38px; font-weight: 700; font-size: 13px; border-color: #cbd5e1;">
                    <option value="all">All Project Heads (All)</option>
                    @foreach($headStatuses as $hs)
                        <option value="{{ $hs['hed_id'] }}" data-division="{{ $hs['division'] }}">{{ $hs['hed_code'] }} — {{ $hs['hed_name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Reset Filters --}}
            <div class="col-md-2 text-md-right pt-md-4">
                <button type="button" id="global-reset-filters-btn" class="btn btn-sm btn-outline-secondary w-100 font-weight-bold d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; height: 38px; font-size: 12.5px; border-color: #cbd5e1;">
                    <i class="fas fa-undo mr-1.5"></i> Reset All
                </button>
            </div>
        </div>
    </div>

    {{-- 7-Pillar Executive Finance KPI Cards (Row 1) --}}
    <div class="row mb-3">
        {{-- 1. Sanctioned Allocation --}}
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card-fin kpi-blue p-3 h-100">
                <div class="kpi-header">
                    <span>TOTAL ALLOCATION</span>
                    <i class="fas fa-layer-group text-primary"></i>
                </div>
                <div class="kpi-main-val text-primary" id="kpi-val-alloc">
                    {{ number_format($macroTotals['allocation']) }}
                </div>
                <div class="kpi-sub-text">
                    <span class="text-muted">Approved Budget across Heads</span>
                </div>
            </div>
        </div>

        {{-- 2. Received Inflows --}}
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card-fin kpi-green p-3 h-100">
                <div class="kpi-header">
                    <span>FUNDS RECEIVED</span>
                    <i class="fas fa-arrow-circle-down text-success"></i>
                </div>
                <div class="kpi-main-val text-success" id="kpi-val-rec">
                    {{ number_format($macroTotals['received']) }}
                </div>
                <div class="kpi-sub-text">
                    <span class="badge badge-success px-2 py-0.5" id="kpi-sub-rec-badge" style="font-size: 10px;">{{ $macroTotals['released_pct'] }}%</span>
                    <span id="kpi-sub-rec">of total allocation released</span>
                </div>
            </div>
        </div>

        {{-- 3. Total Expenditure --}}
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card-fin kpi-red p-3 h-100">
                <div class="kpi-header">
                    <span>EXPENDITURE</span>
                    <i class="fas fa-receipt text-danger"></i>
                </div>
                <div class="kpi-main-val text-danger" id="kpi-val-exp">
                    {{ number_format($macroTotals['expenditure']) }}
                </div>
                <div class="kpi-sub-text">
                    <span class="badge badge-danger px-2 py-0.5" id="kpi-sub-exp-badge" style="font-size: 10px;">{{ $macroTotals['utilization_pct'] }}%</span>
                    <span id="kpi-sub-exp">absorption rate</span>
                </div>
            </div>
        </div>

        {{-- 4. Balance with MTSS --}}
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card-fin kpi-cyan p-3 h-100">
                <div class="kpi-header">
                    <span>BALANCE (with MTSS)</span>
                    <i class="fas fa-wallet text-info"></i>
                </div>
                <div class="kpi-main-val" style="color: #0284c7;" id="kpi-val-bal">
                    {{ number_format($macroTotals['balance']) }}
                </div>
                <div class="kpi-sub-text">
                    <span>Received – Expenditure</span>
                </div>
            </div>
        </div>

        {{-- 5. Active Commitments --}}
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card-fin kpi-amber p-3 h-100">
                <div class="kpi-header">
                    <span>ACTIVE COMMITMENTS</span>
                    <i class="fas fa-clock text-warning"></i>
                </div>
                <div class="kpi-main-val text-warning" style="color: #d97706 !important;" id="kpi-val-cmt">
                    {{ number_format($macroTotals['commitments']) }}
                </div>
                <div class="kpi-sub-text">
                    <span id="kpi-sub-cmt">{{ $commitmentsStats['total_awaited_cnt'] }} pending commitments</span>
                </div>
            </div>
        </div>

        {{-- 6. Net Available Liquidity --}}
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card-fin {{ $macroTotals['available'] >= 0 ? 'kpi-green' : 'kpi-red' }} p-3 h-100" id="kpi-card-avail">
                <div class="kpi-header">
                    <span>AVAILABLE LIQUIDITY</span>
                    <i class="fas fa-shield-alt {{ $macroTotals['available'] >= 0 ? 'text-success' : 'text-danger' }}"></i>
                </div>
                <div class="kpi-main-val" id="kpi-val-avail" style="{{ $macroTotals['available'] >= 0 ? 'color: #16a34a !important;' : 'color: #dc2626 !important;' }}">
                    {{ number_format($macroTotals['available']) }}
                </div>
                <div class="kpi-sub-text">
                    <span>Bal – Commitments – InProc</span>
                </div>
            </div>
        </div>

        {{-- 7. Can Be Spent --}}
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card-fin kpi-gold p-3 h-100">
                <div class="kpi-header">
                    <span>CAN BE SPENT</span>
                    <i class="fas fa-fire text-warning"></i>
                </div>
                <div class="kpi-main-val font-weight-bold" style="color: #d97706 !important;" id="kpi-val-spent">
                    {{ number_format($macroTotals['can_be_spent']) }}
                </div>
                <div class="kpi-sub-text">
                    <span>Total Uncommitted Budget</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Operational Modules Breakdown (Commitments & Payments Pipeline + Contracts Verification) --}}
    <div class="row mb-3">
        {{-- Module 1: Commitments & Disbursements Pipeline --}}
        <div class="col-lg-7 mb-3">
            <div class="card-fin p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h6 class="font-weight-bold rajdhani text-dark m-0" style="font-size: 16px; letter-spacing: 0.5px;">
                            <i class="fas fa-tasks text-primary mr-2"></i>Commitments &amp; Disbursement Pipeline
                        </h6>
                        <span class="text-muted small">Tracking awaited payments vs fulfilled transactions across Purchase &amp; Payroll</span>
                    </div>
                    <a href="{{ route('fin.commitments.landing') }}" class="btn btn-xs btn-outline-primary rajdhani font-weight-bold px-2 py-1" style="border-radius: 4px;">
                        Manage Hub &rarr;
                    </a>
                </div>

                <div class="row">
                    {{-- Purchase Commitments Block --}}
                    <div class="col-sm-6 mb-2">
                        <div class="p-3 rounded border bg-light h-100" style="border-color: #e2e8f0 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">
                                    <i class="fas fa-shopping-cart text-info mr-1"></i> PURCHASE CASES
                                </span>
                                <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 11px;">Cases</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="text-muted font-weight-bold" style="font-size: 12px;">Awaited (Open):</span>
                                <span class="rajdhani font-weight-bold text-warning" style="font-size: 16px; color: #d97706 !important;">
                                    Rs <span id="pipe-pur-awaited-amt">{{ number_format($commitmentsStats['pur_awaited_amt']) }}</span>
                                    <span class="text-muted small" id="pipe-pur-awaited-cnt">({{ $commitmentsStats['pur_awaited_cnt'] }})</span>
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-baseline mb-2 pb-2 border-bottom">
                                <span class="text-muted font-weight-bold" style="font-size: 12px;">Disbursed (Paid):</span>
                                <span class="rajdhani font-weight-bold text-success" style="font-size: 15px;">
                                    Rs <span id="pipe-pur-paid-amt">{{ number_format($commitmentsStats['pur_paid_amt']) }}</span>
                                    <span class="text-muted small" id="pipe-pur-paid-cnt">({{ $commitmentsStats['pur_paid_cnt'] }})</span>
                                </span>
                            </div>

                            <a href="{{ route('fin.payments.index', ['tab' => 'Open', 'type' => 'purchase']) }}" 
                               class="btn btn-sm btn-outline-primary font-weight-bold w-100 d-inline-flex align-items-center justify-content-center" 
                               style="border-radius: 6px; height: 32px; font-size: 12px; border-width: 1.5px; background: #ffffff; color: #0284c7; border-color: #0284c7;">
                                <i class="fas fa-external-link-alt mr-1.5"></i> Open Purchase Ledger
                            </a>
                        </div>
                    </div>

                    {{-- Salary Payroll Commitments Block --}}
                    <div class="col-sm-6 mb-2">
                        <div class="p-3 rounded border bg-light h-100" style="border-color: #e2e8f0 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">
                                    <i class="fas fa-user-tie text-purple mr-1"></i> SALARY ORDERS (PAYROLL)
                                </span>
                                <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 11px;">Payroll</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-baseline mb-1">
                                <span class="text-muted font-weight-bold" style="font-size: 12px;">Awaited (Pending):</span>
                                <span class="rajdhani font-weight-bold text-danger" style="font-size: 16px; color: #dc2626 !important;">
                                    Rs <span id="pipe-sal-awaited-amt">{{ number_format($commitmentsStats['sal_awaited_amt']) }}</span>
                                    <span class="text-muted small" id="pipe-sal-awaited-cnt">({{ $commitmentsStats['sal_awaited_cnt'] }})</span>
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-baseline mb-2 pb-2 border-bottom">
                                <span class="text-muted font-weight-bold" style="font-size: 12px;">Disbursed (Fulfilled):</span>
                                <span class="rajdhani font-weight-bold text-success" style="font-size: 15px;">
                                    Rs <span id="pipe-sal-paid-amt">{{ number_format($commitmentsStats['sal_paid_amt']) }}</span>
                                    <span class="text-muted small" id="pipe-sal-paid-cnt">({{ $commitmentsStats['sal_paid_cnt'] }})</span>
                                </span>
                            </div>

                            <a href="{{ route('fin.payments.index', ['tab' => 'Open', 'type' => 'salary']) }}" 
                               class="btn btn-sm btn-outline-danger font-weight-bold w-100 d-inline-flex align-items-center justify-content-center" 
                               style="border-radius: 6px; height: 32px; font-size: 12px; border-width: 1.5px; background: #ffffff; color: #dc2626; border-color: #dc2626;">
                                <i class="fas fa-external-link-alt mr-1.5"></i> Open Salary Payroll
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Module 2: Salary & Contract Verification Intelligence --}}
        <div class="col-lg-5 mb-3">
            <div class="card-fin p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h6 class="font-weight-bold rajdhani text-dark m-0" style="font-size: 16px; letter-spacing: 0.5px;">
                            <i class="fas fa-user-shield text-purple mr-2"></i>Salary Verification Hub
                        </h6>
                        <span class="text-muted small">Finance scrutiny for contract approvals &amp; head tagging</span>
                    </div>
                    <a href="{{ route('fin.verification.contracts.index') }}" class="btn btn-xs btn-outline-purple rajdhani font-weight-bold px-2 py-1" style="border-radius: 4px; color: #7c3aed; border-color: #7c3aed;">
                        Scrutiny &rarr;
                    </a>
                </div>

                <div class="p-3 rounded border bg-light mb-2.5" style="border-color: #e2e8f0 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="font-weight-bold text-dark rajdhani" style="font-size: 14px;">CONTRACTS VERIFICATION PROGRESS</span>
                        <span class="badge badge-success font-weight-bold rajdhani px-2 py-0.5" style="font-size: 12px;" id="verif-pct-badge">{{ $verificationStats['verified_pct'] }}% Verified</span>
                    </div>
                    
                    <div class="progress mb-2" style="height: 8px; border-radius: 6px; background: #e2e8f0;">
                        <div class="progress-bar bg-success" id="verif-progress-bar" role="progressbar" style="width: {{ $verificationStats['verified_pct'] }}%;" aria-valuenow="{{ $verificationStats['verified_pct'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center text-muted small">
                        <span><strong class="text-success font-weight-bold" id="verif-verified-cnt">{{ $verificationStats['verified_contracts'] }}</strong> Verified</span>
                        <span><strong class="text-warning font-weight-bold" style="color: #d97706 !important;" id="verif-pending-cnt">{{ $verificationStats['pending_contracts'] }}</strong> Pending Action</span>
                        <span><strong id="verif-total-cnt">{{ $verificationStats['total_contracts'] }}</strong> Total Contracts</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 rounded border bg-white" style="border-color: #e2e8f0 !important;">
                    <div>
                        <span class="font-weight-bold text-dark rajdhani d-block" style="font-size: 14px;">
                            <i class="fas fa-sitemap text-info mr-1"></i> Effective Salary Heads Assigned
                        </span>
                        <span class="text-muted" style="font-size: 11px;">Employees mapped to dedicated financial project heads</span>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="font-weight-bold rajdhani text-primary" style="font-size: 18px;" id="verif-eff-heads-cnt">{{ $verificationStats['effective_heads_count'] }}</span>
                        <a href="{{ route('fin.verification.salary-heads.index') }}" class="btn btn-xs btn-outline-info font-weight-bold px-2 py-1" style="font-size: 11px; border-radius: 4px;">
                            Manage &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Interactive Visual Financial Analytics (3 Rich Charts) --}}
    <div class="row mb-3">
        {{-- Chart 1: Macro Financial Liquidity Doughnut --}}
        <div class="col-lg-4 mb-3">
            <div class="card-fin p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <span class="font-weight-bold text-dark rajdhani" style="font-size: 14.5px; letter-spacing: 0.5px;">
                        <i class="fas fa-chart-pie text-info mr-1.5"></i> Budget Liquidity Structure
                    </span>
                    <span class="text-muted small rajdhani">Macro Ratio</span>
                </div>
                <div class="chart-container-card">
                    <canvas id="financeLiquidityDonut"></canvas>
                </div>
            </div>
        </div>

        {{-- Chart 2: Monthly Disbursement Velocity Trend --}}
        <div class="col-lg-5 mb-3">
            <div class="card-fin p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <span class="font-weight-bold text-dark rajdhani" style="font-size: 14.5px; letter-spacing: 0.5px;">
                        <i class="fas fa-chart-line text-success mr-1.5"></i> Monthly Payout Trends (Last 12 Months)
                    </span>
                    <span class="text-muted small rajdhani">Disbursed (PKR)</span>
                </div>
                <div class="chart-container-card">
                    <canvas id="monthlyOutflowChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Chart 3: Top Heads by Allocation & Expenditure --}}
        <div class="col-lg-3 mb-3">
            <div class="card-fin p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                    <span class="font-weight-bold text-dark rajdhani" style="font-size: 14.5px; letter-spacing: 0.5px;">
                        <i class="fas fa-chart-bar text-primary mr-1.5"></i> Top Projects by Budget
                    </span>
                    <span class="text-muted small rajdhani">Allocated vs Spent</span>
                </div>
                <div class="chart-container-card">
                    <canvas id="topHeadsBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 4: Recent Disbursed Financial Transactions (Live Mini-Ledger) --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-fin p-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-2.5 pb-2 border-bottom">
                    <div>
                        <h6 class="font-weight-bold rajdhani text-dark m-0" style="font-size: 16px; letter-spacing: 0.5px;">
                            <i class="fas fa-money-check-alt text-success mr-2"></i>Recent Disbursed Financial Transactions
                        </h6>
                        <span class="text-muted small">Latest executed payment transactions across purchase cases and salary payroll</span>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="badge badge-light border text-muted font-weight-bold rajdhani" id="tx-counter-badge">Showing {{ count($recentTransactions) }}</span>
                        <a href="{{ route('fin.payments.index') }}" class="btn btn-xs btn-outline-success rajdhani font-weight-bold px-3 py-1" style="font-size: 11.5px; border-radius: 4px;">
                            View Full Payment Ledger &rarr;
                        </a>
                    </div>
                </div>

                <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 8px;">
                    <table class="table table-hover table-fin m-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Trn #</th>
                                <th style="width: 105px;">Date</th>
                                <th style="width: 90px;">Type</th>
                                <th style="width: 100px;">Head</th>
                                <th>Transaction / Beneficiary Description</th>
                                <th class="text-right" style="width: 140px;">Disbursed (Rs)</th>
                                <th class="text-right" style="width: 120px;">Tax Deducted</th>
                                <th class="text-center" style="width: 90px;">Status</th>
                                <th class="text-center" style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tx-table-body">
                            @forelse($recentTransactions as $tx)
                            <tr class="tx-row" data-division="{{ strtolower($tx['division']) }}" data-division-full="{{ strtolower($tx['division_full'] ?? '') }}" data-unt-id="{{ $tx['division_id'] }}" data-hed-id="{{ $tx['hed_id'] }}">
                                <td class="rajdhani font-weight-bold text-dark">#{{ $tx['trn_id'] }}</td>
                                <td class="rajdhani text-muted" style="font-size: 12.5px;">{{ $tx['date'] ? \Carbon\Carbon::parse($tx['date'])->format('d M, Y') : '—' }}</td>
                                <td>
                                    @if($tx['type'] === 'Salary')
                                        <span class="badge badge-purple px-2 py-1" style="font-weight: 700; font-size: 11px;">Salary</span>
                                    @else
                                        <span class="badge badge-info px-2 py-1" style="font-weight: 700; font-size: 11px;">Purchase</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-light border text-dark font-weight-bold rajdhani px-2 py-1" style="font-size: 12px;">
                                        {{ $tx['head'] }}
                                    </span>
                                </td>
                                <td class="font-weight-bold text-dark" style="max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $tx['desc'] }}
                                </td>
                                <td class="text-right rajdhani font-weight-bold text-success" style="font-size: 15px;">
                                    {{ number_format($tx['amount'], 2) }}
                                </td>
                                <td class="text-right rajdhani font-weight-bold text-muted" style="font-size: 14px;">
                                    {{ $tx['tax'] > 0 ? number_format($tx['tax'], 2) : '—' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success px-2 py-1" style="font-size: 11px; font-weight: 700;">Paid</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('fin.payments.show', $tx['cmt_id']) }}" class="btn btn-xs btn-outline-primary p-1" title="View Transaction in Commitment Ledger" style="border-radius: 4px; width: 26px; height: 26px;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox text-muted mb-2" style="font-size: 24px;"></i><br>
                                    No recent transactions recorded in this horizon.
                                </td>
                            </tr>
                            @endforelse
                            <tr id="tx-filtered-empty-state" style="display: none;">
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-search-dollar text-muted mb-2" style="font-size: 24px;"></i><br>
                                    No recent transactions found for the selected Division / Head filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 5: Master Project & Head-wise Financial Health Matrix (The Big Table) --}}
    <div class="row">
        <div class="col-12">
            <div class="card-fin p-3">
                {{-- Table Header & Live Filters --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 pb-2 border-bottom" style="gap: 12px;">
                    <div>
                        <h5 class="font-weight-bold rajdhani text-dark m-0" style="font-size: 18px; letter-spacing: 0.5px;">
                            <i class="fas fa-table text-primary mr-2"></i>Project &amp; Operating Heads Financial Intelligence Matrix
                        </h5>
                        <span class="text-muted small">Live allocation, receipts, commitments, burnable liquidity and spendable capacity</span>
                    </div>
                    
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="badge badge-light border text-dark font-weight-bold rajdhani px-3 py-1" id="head-count-badge" style="font-size: 13px;">
                            Showing {{ count($headStatuses) }} Heads
                        </span>
                    </div>
                </div>

                {{-- Table Search Filter --}}
                <div class="p-3 rounded border bg-light mb-3" style="border-color: #e2e8f0 !important;">
                    <div class="row align-items-center">
                        <div class="col-md-10 mb-2 mb-md-0">
                            <label class="small font-weight-bold text-muted mb-1 d-block">
                                <i class="fas fa-search text-primary mr-1"></i> QUICK SEARCH HEAD CODE OR TITLE:
                            </label>
                            <input type="text" id="matrix-search-input" class="form-control form-control-sm bg-white border" placeholder="Type project code (e.g. ELINT, CDS, CSRF) or title to filter table instantly..." style="border-radius: 6px; font-weight: 500; height: 38px;">
                        </div>
                        <div class="col-md-2 text-md-right pt-md-4">
                            <button type="button" id="matrix-clear-search-btn" class="btn btn-sm btn-outline-secondary w-100 font-weight-bold d-inline-flex align-items-center justify-content-center" style="border-radius: 6px; height: 38px; font-size: 12px; gap: 6px;">
                                <i class="fas fa-eraser"></i> Clear Search
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Table Responsive --}}
                <div class="table-responsive" style="max-height: 600px; overflow-y: auto; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                    <table class="table table-hover table-fin m-0 text-nowrap" id="matrix-head-table">
                        <thead class="sticky-top">
                            <tr>
                                <th style="min-width: 220px;">Head Code &amp; Title</th>
                                <th style="min-width: 90px;">Division</th>
                                <th class="text-right" style="min-width: 120px;">Allocation (Rs)</th>
                                <th class="text-right" style="min-width: 120px;">Received (Rs)</th>
                                <th class="text-right" style="min-width: 130px;">Expenditure (Rs)</th>
                                <th class="text-right" style="min-width: 130px;">Balance with MTSS</th>
                                <th class="text-right" style="min-width: 120px;">Commitments</th>
                                <th class="text-right" style="min-width: 110px;">In Process</th>
                                <th class="text-right" style="min-width: 140px;">Available Liquidity</th>
                                <th class="text-right" style="min-width: 130px;">Can Be Spent</th>
                                <th class="text-center" style="min-width: 110px;">Absorption</th>
                                <th class="text-center" style="min-width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($headStatuses as $hs)
                            <tr class="matrix-row" 
                                data-hed-id="{{ $hs['hed_id'] }}"
                                data-code="{{ strtolower($hs['hed_code']) }}" 
                                data-title="{{ strtolower($hs['hed_name'] . ' ' . $hs['prj_title']) }}"
                                data-division="{{ $hs['division'] }}">
                                
                                {{-- Head Code & Name --}}
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-primary font-weight-bold rajdhani px-2 py-1 mr-2" style="font-size: 13px; letter-spacing: 0.5px;">
                                            {{ $hs['hed_code'] }}
                                        </span>
                                        <div style="max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $hs['hed_name'] }}">
                                            <strong class="text-dark">{{ $hs['hed_name'] }}</strong>
                                        </div>
                                    </div>
                                </td>

                                {{-- Division --}}
                                <td>
                                    <span class="badge badge-light border text-muted font-weight-bold" style="font-size: 11px;">
                                        {{ $hs['division'] }}
                                    </span>
                                </td>

                                {{-- Allocation --}}
                                <td class="text-right font-weight-bold text-dark rajdhani" style="font-size: 14px;">
                                    {{ number_format($hs['allocation']) }}
                                </td>

                                {{-- Received --}}
                                <td class="text-right font-weight-bold text-success rajdhani" style="font-size: 14px;">
                                    {{ number_format($hs['received']) }}
                                </td>

                                {{-- Expenditure --}}
                                <td class="text-right rajdhani font-weight-bold" style="font-size: 14px;">
                                    <span class="text-danger">{{ number_format($hs['expenditure']) }}</span>
                                    <a href="{{ $hs['expenditure_drilldown'] }}" target="_blank" class="btn-drill-link btn-drill-red" title="Expenditure Drilldown">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>

                                {{-- Balance with MTSS --}}
                                <td class="text-right font-weight-bold rajdhani" style="font-size: 14px; color: #0284c7;">
                                    {{ number_format($hs['balance']) }}
                                </td>

                                {{-- Commitments --}}
                                <td class="text-right rajdhani font-weight-bold" style="font-size: 14px;">
                                    <span class="text-warning" style="color: #d97706 !important;">{{ number_format($hs['commitments']) }}</span>
                                    <a href="{{ $hs['commitments_drilldown'] }}" target="_blank" class="btn-drill-link btn-drill-amber" title="Commitments Drilldown">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>

                                {{-- In Process --}}
                                <td class="text-right rajdhani font-weight-bold text-muted" style="font-size: 13.5px;">
                                    {{ number_format($hs['in_process']) }}
                                    <a href="{{ $hs['in_process_drilldown'] }}" target="_blank" class="btn-drill-link btn-drill-blue" title="In Process Cases Drilldown">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </td>

                                {{-- Available Liquidity --}}
                                <td class="text-right rajdhani font-weight-bold" style="font-size: 14.5px;">
                                    @if($hs['available'] > 0)
                                        <span class="font-weight-bold rajdhani" style="color: #16a34a !important; font-size: 14.5px;">+{{ number_format($hs['available']) }}</span>
                                    @elseif($hs['available'] < 0)
                                        <span class="badge font-weight-bold rajdhani px-2 py-1" style="font-size: 12.5px; background: #fee2e2 !important; color: #dc2626 !important; border: 1px solid #fca5a5; letter-spacing: 0.3px;">{{ number_format($hs['available']) }}</span>
                                    @else
                                        <span class="font-weight-bold rajdhani text-muted" style="font-size: 14px; color: #64748b !important;">0</span>
                                    @endif
                                </td>

                                {{-- Can Be Spent --}}
                                <td class="text-right rajdhani font-weight-bold" style="font-size: 14.5px; color: #d97706 !important;">
                                    {{ number_format($hs['can_be_spent']) }}
                                </td>

                                {{-- Absorption % --}}
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center" style="gap: 6px;">
                                        <div class="progress" style="width: 50px; height: 6px; border-radius: 4px; background: #e2e8f0;">
                                            <div class="progress-bar {{ $hs['pct_utilized'] > 90 ? 'bg-danger' : ($hs['pct_utilized'] > 60 ? 'bg-warning' : 'bg-primary') }}" 
                                                 style="width: {{ min(100, $hs['pct_utilized']) }}%;"></div>
                                        </div>
                                        <span class="rajdhani font-weight-bold text-muted small">{{ $hs['pct_utilized'] }}%</span>
                                    </div>
                                </td>

                                {{-- Actions --}}
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                        <a href="{{ $hs['full_report_url'] }}" target="_blank" class="btn btn-xs btn-outline-primary px-2 py-1" style="border-radius: 4px;" title="Full Financial View">
                                            <i class="fas fa-chart-line"></i>
                                        </a>
                                        @if($hs['prj_id'])
                                            <a href="{{ $hs['project_details_url'] }}" target="_blank" class="btn btn-xs btn-outline-secondary px-2 py-1" style="border-radius: 4px;" title="Project Profile">
                                                <i class="fas fa-project-diagram"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="fas fa-search-dollar text-muted mb-2" style="font-size: 32px;"></i><br>
                                    No financial heads found in this horizon.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Safeguard globally pre-loaded Chart.js (v2) and assign private variable for local Chart.js v4
    var oldChart = window.Chart;
</script>
<script src="{{ asset('plugins/chartjs4/chart.umd.js') }}"></script>
<script>
    window.Chart4 = window.Chart;
    window.Chart = oldChart;

    // Master dataset passed from controller
    const macroTotals = @json($macroTotals);
    const monthlyTrends = @json($monthlyTrends);
    const headStatuses = @json($headStatuses);
    const cmtDetailedGroups = @json($cmtDetailedGroups ?? []);
    const verifDetailedGroups = @json($verifDetailedGroups ?? []);
    const effHeadDetailedGroups = @json($effHeadDetailedGroups ?? []);

    let chartDonutInstance = null;
    let chartTopHeadsInstance = null;

    // 1. Chart 1: Financial Liquidity & Absorption Structure (Doughnut)
    function initOrUpdateDonutChart(exp, cmt, inp, avail, yetToRec) {
        const ctx = document.getElementById('financeLiquidityDonut');
        if (!ctx) return;

        const dataPoints = [
            Math.max(0, exp),
            Math.max(0, cmt),
            Math.max(0, inp),
            Math.max(0, avail),
            Math.max(0, yetToRec)
        ];

        if (chartDonutInstance) {
            chartDonutInstance.data.datasets[0].data = dataPoints;
            chartDonutInstance.update();
            return;
        }

        chartDonutInstance = new window.Chart4(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Realized Expenditure', 'Active Commitments', 'In Process Pipeline', 'Available Liquidity', 'Yet to be Received'],
                datasets: [{
                    data: dataPoints,
                    backgroundColor: [
                        '#dc2626', // Red
                        '#d97706', // Amber
                        '#64748b', // Slate
                        '#16a34a', // Green
                        '#0284c7'  // Sky Blue
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            font: { size: 10.5, family: "'Inter', sans-serif" },
                            padding: 8
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const val = Number(ctx.raw || 0);
                                return ctx.label + ': Rs ' + val.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Chart 2: Monthly Disbursement Velocity Trend
    (function initMonthlyOutflowChart() {
        const ctx = document.getElementById('monthlyOutflowChart');
        if (!ctx) return;

        const labels = monthlyTrends.map(m => m.label);
        const amounts = monthlyTrends.map(m => m.amount);

        new window.Chart4(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Disbursed (Rs)',
                    data: amounts,
                    backgroundColor: 'rgba(22, 163, 74, 0.25)',
                    borderColor: '#16a34a',
                    borderWidth: 2,
                    borderRadius: 4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, family: "'Rajdhani', sans-serif" } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 10, family: "'Rajdhani', sans-serif" },
                            callback: function(v) {
                                if (v >= 10000000) return (v / 10000000).toFixed(1) + ' Cr';
                                if (v >= 100000) return (v / 100000).toFixed(0) + ' Lac';
                                return v;
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return 'Disbursed: Rs ' + Number(ctx.raw || 0).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    })();

    // 3. Chart 3: Top Heads by Allocation & Expenditure
    function initOrUpdateTopHeadsChart(headsArray) {
        const ctx = document.getElementById('topHeadsBarChart');
        if (!ctx) return;

        const sorted = headsArray.slice().sort((a, b) => b.allocation - a.allocation).slice(0, 7);
        const labels = sorted.map(h => h.hed_code);
        const allocs = sorted.map(h => h.allocation);
        const exps = sorted.map(h => h.expenditure);

        if (chartTopHeadsInstance) {
            chartTopHeadsInstance.data.labels = labels;
            chartTopHeadsInstance.data.datasets[0].data = allocs;
            chartTopHeadsInstance.data.datasets[1].data = exps;
            chartTopHeadsInstance.update();
            return;
        }

        chartTopHeadsInstance = new window.Chart4(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Allocation',
                        data: allocs,
                        backgroundColor: 'rgba(2, 132, 199, 0.4)',
                        borderColor: '#0284c7',
                        borderWidth: 1.5,
                        borderRadius: 3
                    },
                    {
                        label: 'Spent',
                        data: exps,
                        backgroundColor: 'rgba(220, 38, 38, 0.4)',
                        borderColor: '#dc2626',
                        borderWidth: 1.5,
                        borderRadius: 3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 9.5, family: "'Rajdhani', sans-serif" } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 9, family: "'Rajdhani', sans-serif" },
                            callback: function(v) {
                                if (v >= 10000000) return (v / 10000000).toFixed(0) + ' Cr';
                                if (v >= 100000) return (v / 100000).toFixed(0) + ' L';
                                return v;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 8, font: { size: 9.5 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': Rs ' + Number(ctx.raw || 0).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Initialize initial charts
    initOrUpdateDonutChart(
        Number(macroTotals.expenditure || 0),
        Number(macroTotals.commitments || 0),
        Number(macroTotals.in_process || 0),
        Number(macroTotals.available || 0),
        Math.max(0, Number(macroTotals.allocation || 0) - Number(macroTotals.received || 0))
    );
    initOrUpdateTopHeadsChart(headStatuses);

    // 4. Dynamic Interactive Filtering by Division and Head
    $(document).ready(function() {
        const divisionSelect = $('#global-division-filter');
        const headSelect = $('#global-head-filter');
        const resetBtn = $('#global-reset-filters-btn');
        const searchInput = $('#matrix-search-input');
        const clearSearchBtn = $('#matrix-clear-search-btn');
        const countBadge = $('#head-count-badge');
        const rows = $('#matrix-head-table tbody tr.matrix-row');

        // Function to rebuild Head Select dropdown based on selected Division
        function rebuildHeadDropdown(selectedDivision) {
            headSelect.empty();
            headSelect.append($('<option value="all">All Project Heads (All)</option>'));

            headStatuses.forEach(function(h) {
                let matchDiv = (selectedDivision === 'all' || 
                    (h.division && h.division.toLowerCase() === selectedDivision.toLowerCase()) ||
                    (h.division_full && h.division_full.toLowerCase() === selectedDivision.toLowerCase()) ||
                    String(h.unt_id) === String(selectedDivision)
                );
                if (matchDiv) {
                    headSelect.append(
                        $('<option></option>')
                            .val(h.hed_id)
                            .attr('data-division', h.division)
                            .text(h.hed_code + ' — ' + h.hed_name)
                    );
                }
            });
            headSelect.val('all');
        }

        // Master function to apply Division, Head, and Text Search
        function applyMasterFilters() {
            const selectedDiv = divisionSelect.val();
            const selectedHedId = headSelect.val();
            const searchTerm = (searchInput.val() || '').trim().toLowerCase();

            let visibleCount = 0;
            let sumAlloc = 0, sumRec = 0, sumExp = 0, sumBal = 0, sumCmt = 0, sumInp = 0, sumAvail = 0, sumSpent = 0;
            let filteredArray = [];

            // Helper function for flexible division matching
            function isDivMatch(divShort, divFull, untId) {
                if (selectedDiv === 'all') return true;
                const s = selectedDiv.toLowerCase();
                if (divShort && divShort.toLowerCase() === s) return true;
                if (divFull && divFull.toLowerCase() === s) return true;
                if (untId && String(untId) === String(selectedDiv)) return true;
                return false;
            }

            // 1. Calculate dynamic KPI values from filtered heads
            headStatuses.forEach(function(h) {
                let matchDiv = isDivMatch(h.division, h.division_full, h.unt_id);
                let matchHead = (selectedHedId === 'all' || String(h.hed_id) === String(selectedHedId));

                if (matchDiv && matchHead) {
                    filteredArray.push(h);
                    sumAlloc += Number(h.allocation || 0);
                    sumRec += Number(h.received || 0);
                    sumExp += Number(h.expenditure || 0);
                    sumBal += Number(h.balance || 0);
                    sumCmt += Number(h.commitments || 0);
                    sumInp += Number(h.in_process || 0);
                    sumAvail += Number(h.available || 0);
                    sumSpent += Number(h.can_be_spent || 0);
                }
            });

            // 2. Update KPI cards dynamically
            const relPct = sumAlloc > 0 ? ((sumRec / sumAlloc) * 100).toFixed(1) : 0;
            const utPct = sumAlloc > 0 ? ((sumExp / sumAlloc) * 100).toFixed(1) : 0;

            $('#kpi-val-alloc').text(sumAlloc.toLocaleString());
            $('#kpi-val-rec').text(sumRec.toLocaleString());
            $('#kpi-sub-rec-badge').text(relPct + '%');
            $('#kpi-val-exp').text(sumExp.toLocaleString());
            $('#kpi-sub-exp-badge').text(utPct + '%');
            $('#kpi-val-bal').text(sumBal.toLocaleString());
            $('#kpi-val-cmt').text(sumCmt.toLocaleString());
            
            // Available Liquidity card styling & value
            const availEl = $('#kpi-val-avail');
            const availCard = $('#kpi-card-avail');
            availEl.text(sumAvail.toLocaleString());
            if (sumAvail >= 0) {
                availEl.css('color', '#16a34a');
                availCard.removeClass('kpi-red').addClass('kpi-green');
                availCard.find('.kpi-header i').removeClass('text-danger').addClass('text-success');
            } else {
                availEl.css('color', '#dc2626');
                availCard.removeClass('kpi-green').addClass('kpi-red');
                availCard.find('.kpi-header i').removeClass('text-success').addClass('text-danger');
            }

            $('#kpi-val-spent').text(sumSpent.toLocaleString());

            // 3. Update Charts
            initOrUpdateDonutChart(
                sumExp,
                sumCmt,
                sumInp,
                sumAvail,
                Math.max(0, sumAlloc - sumRec)
            );
            initOrUpdateTopHeadsChart(filteredArray.length > 0 ? filteredArray : headStatuses);

            // 4. Filter Matrix Table Rows
            rows.each(function() {
                const row = $(this);
                const div = row.attr('data-division') || '';
                const divFull = row.attr('data-division-full') || '';
                const untId = row.attr('data-unt-id') || '';
                const hedId = row.attr('data-hed-id') || '';
                const code = row.attr('data-code') || '';
                const title = row.attr('data-title') || '';

                let matchDiv = isDivMatch(div, divFull, untId);
                let matchHead = (selectedHedId === 'all' || hedId === String(selectedHedId));
                let matchSearch = (!searchTerm || code.includes(searchTerm) || title.includes(searchTerm));

                if (matchDiv && matchHead && matchSearch) {
                    row.show();
                    visibleCount++;
                } else {
                    row.hide();
                }
            });

            countBadge.text('Showing ' + visibleCount + ' of ' + rows.length + ' Heads');

            // 5. Reactive Commitments & Disbursement Pipeline Module
            let purAwaitedAmt = 0, purAwaitedCnt = 0;
            let purPaidAmt = 0, purPaidCnt = 0;
            let salAwaitedAmt = 0, salAwaitedCnt = 0;
            let salPaidAmt = 0, salPaidCnt = 0;

            cmtDetailedGroups.forEach(function(item) {
                let matchDiv = isDivMatch(item.division_namesh, item.division_name, item.unt_id);
                let matchHead = (selectedHedId === 'all' || String(item.hed_id) === String(selectedHedId));

                if (matchDiv && matchHead) {
                    let isSal = (item.kind === 'salary');
                    let isPaid = (item.status === 'Paid');
                    let isAwaited = (item.status === 'Awaited');
                    let cnt = Number(item.cnt || 0);
                    let amt = Number(item.amt || 0);

                    if (isSal) {
                        if (isAwaited) { salAwaitedAmt += amt; salAwaitedCnt += cnt; }
                        else if (isPaid) { salPaidAmt += amt; salPaidCnt += cnt; }
                    } else {
                        if (isAwaited) { purAwaitedAmt += amt; purAwaitedCnt += cnt; }
                        else if (isPaid) { purPaidAmt += amt; purPaidCnt += cnt; }
                    }
                }
            });

            let totalAwaitedCnt = purAwaitedCnt + salAwaitedCnt;

            $('#pipe-pur-awaited-amt').text(Math.round(purAwaitedAmt).toLocaleString());
            $('#pipe-pur-awaited-cnt').text('(' + purAwaitedCnt + ')');
            $('#pipe-pur-paid-amt').text(Math.round(purPaidAmt).toLocaleString());
            $('#pipe-pur-paid-cnt').text('(' + purPaidCnt + ')');

            $('#pipe-sal-awaited-amt').text(Math.round(salAwaitedAmt).toLocaleString());
            $('#pipe-sal-awaited-cnt').text('(' + salAwaitedCnt + ')');
            $('#pipe-sal-paid-amt').text(Math.round(salPaidAmt).toLocaleString());
            $('#pipe-sal-paid-cnt').text('(' + salPaidCnt + ')');

            $('#kpi-sub-cmt').text(totalAwaitedCnt + ' pending commitments');

            // 6. Reactive Salary Verification Hub Module
            let totalContracts = 0;
            let verifiedContracts = 0;
            let pendingContracts = 0;

            verifDetailedGroups.forEach(function(item) {
                let matchDiv = isDivMatch(item.division_namesh, item.division_name, item.unt_id);
                let matchHead = (selectedHedId === 'all' || String(item.hed_id) === String(selectedHedId));

                if (matchDiv && matchHead) {
                    totalContracts += Number(item.total_cnt || 0);
                    verifiedContracts += Number(item.verified_cnt || 0);
                    pendingContracts += Number(item.pending_cnt || 0);
                }
            });

            let effHeadsCount = 0;
            effHeadDetailedGroups.forEach(function(item) {
                let matchDiv = isDivMatch(item.division_namesh, item.division_name, item.unt_id);
                let matchHead = (selectedHedId === 'all' || String(item.hed_id) === String(selectedHedId));

                if (matchDiv && matchHead) {
                    effHeadsCount += Number(item.cnt || 0);
                }
            });

            let verifPct = totalContracts > 0 ? ((verifiedContracts / totalContracts) * 100).toFixed(1) : 0;

            $('#verif-pct-badge').text(verifPct + '% Verified');
            $('#verif-progress-bar').css('width', verifPct + '%').attr('aria-valuenow', verifPct);
            $('#verif-verified-cnt').text(verifiedContracts.toLocaleString());
            $('#verif-pending-cnt').text(pendingContracts.toLocaleString());
            $('#verif-total-cnt').text(totalContracts.toLocaleString());
            $('#verif-eff-heads-cnt').text(effHeadsCount.toLocaleString());

            // 7. Reactive Recent Disbursed Financial Transactions Table
            let visibleTxCount = 0;
            const txRows = $('#tx-table-body tr.tx-row');
            const emptyTxRow = $('#tx-filtered-empty-state');

            txRows.each(function() {
                const row = $(this);
                const div = row.attr('data-division') || '';
                const divFull = row.attr('data-division-full') || '';
                const untId = row.attr('data-unt-id') || '';
                const hedId = row.attr('data-hed-id') || '';

                let matchDiv = isDivMatch(div, divFull, untId);
                let matchHead = (selectedHedId === 'all' || hedId === String(selectedHedId));

                if (matchDiv && matchHead) {
                    row.show();
                    visibleTxCount++;
                } else {
                    row.hide();
                }
            });

            if (visibleTxCount === 0 && txRows.length > 0) {
                emptyTxRow.show();
            } else {
                emptyTxRow.hide();
            }

            $('#tx-counter-badge').text('Showing ' + visibleTxCount + ' of ' + txRows.length);
        }

        // Division Change Event
        divisionSelect.on('change', function() {
            rebuildHeadDropdown($(this).val());
            applyMasterFilters();
        });

        // Head Change Event
        headSelect.on('change', function() {
            applyMasterFilters();
        });

        // Search Input Event
        searchInput.on('input', function() {
            applyMasterFilters();
        });

        // Clear Search Button
        clearSearchBtn.on('click', function() {
            searchInput.val('');
            applyMasterFilters();
        });

        // Global Reset Filters Button
        resetBtn.on('click', function() {
            divisionSelect.val('all');
            rebuildHeadDropdown('all');
            searchInput.val('');
            applyMasterFilters();
        });
    });
</script>
@endsection
