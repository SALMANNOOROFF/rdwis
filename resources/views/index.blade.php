@extends('welcome')

@section('content')
@php
    $u = Auth::user();
    $area = strtolower(trim((string) ($u?->acc_untarea ?? '')));
    $title = $area === 'nrdi'
        ? 'NRDI Command Dashboard'
        : ((method_exists($u, 'isSORD') && $u->isSORD()) ? 'SORD Dashboard' : 'Division Executive Dashboard');
@endphp

<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">

<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.division-dashboard {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: var(--rd-text1, #292824);
    min-height: 100vh;
}

.rajdhani {
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.5px;
}

/* Glassmorphism & Cyber Card Foundation */
.card-cyber {
    background: var(--rd-surface, #ffffff);
    border: 1px solid var(--rd-border, #E8E4DC);
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    transition: all 0.25s ease-in-out;
}
.card-cyber:hover {
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}

/* Executive Metric Cards with Glowing Accent Borders */
.metric-card {
    position: relative;
    padding: 20px 22px;
    border-radius: 14px;
    background: var(--rd-surface, #ffffff);
    border: 1px solid var(--rd-border, #E8E4DC);
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
}
.metric-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.metric-card.accent-cyan {
    border-left: 4px solid #06b6d4;
}
.metric-card.accent-emerald {
    border-left: 4px solid #10b981;
}
.metric-card.accent-amber {
    border-left: 4px solid #f59e0b;
}
.metric-card.accent-indigo {
    border-left: 4px solid #6366f1;
}
.metric-card.accent-purple {
    border-left: 4px solid #8b5cf6;
}
.metric-card.accent-blue {
    border-left: 4px solid #3b82f6;
}

.metric-icon-bg {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
}
.bg-cyan-soft { background: rgba(6, 182, 212, 0.12); color: #0891b2; }
.bg-emerald-soft { background: rgba(16, 185, 129, 0.12); color: #059669; }
.bg-amber-soft { background: rgba(245, 158, 11, 0.12); color: #d97706; }
.bg-indigo-soft { background: rgba(99, 102, 241, 0.12); color: #4f46e5; }
.bg-purple-soft { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }
.bg-blue-soft { background: rgba(59, 130, 246, 0.12); color: #2563eb; }

/* Pulse Live Status Pill */
.live-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.live-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulseDot 1.8s infinite;
}
@keyframes pulseDot {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* Quick Action Buttons */
.btn-action-hub {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-weight: 600;
    font-size: 13px;
    padding: 7px 16px;
    border-radius: 8px;
    transition: all 0.2s ease;
    border: 1px solid var(--rd-border, #E8E4DC);
    background: var(--rd-surface, #ffffff);
    color: var(--rd-text1, #292824);
}
.btn-action-hub:hover {
    background: var(--rd-neutral-200, #F1EEE8);
    transform: translateY(-1px);
    color: var(--rd-text1, #292824);
    box-shadow: 0 3px 8px rgba(0,0,0,0.06);
}
.btn-action-primary {
    background: var(--rd-primary-600, #5F7858) !important;
    border-color: var(--rd-primary-600, #5F7858) !important;
    color: #ffffff !important;
}
.btn-action-primary:hover {
    background: var(--rd-primary-700, #4E6449) !important;
    color: #ffffff !important;
}

/* Custom Table Polish */
.table-cyber {
    background: transparent;
    border-collapse: separate;
    border-spacing: 0;
}
.table-cyber thead th {
    background: var(--rd-neutral-200, #F1EEE8);
    color: var(--rd-neutral-800, #4B4944);
    font-family: 'Rajdhani', sans-serif;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    font-size: 12px;
    font-weight: 700;
    border: none;
    padding: 12px 14px;
}
.table-cyber tbody td {
    padding: 12px 14px;
    border-top: 1px solid var(--rd-border, #E8E4DC);
    vertical-align: middle;
}
.clickable-row {
    cursor: pointer;
    transition: background-color 0.15s ease;
}
.clickable-row:hover {
    background-color: var(--rd-neutral-100, #F7F5F0) !important;
}

/* Skeleton Loading */
.skeleton {
    background: #e2e8f0;
    background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
    border-radius: 8px;
    background-size: 200% 100%;
    animation: 1.5s shine linear infinite;
}
@keyframes shine { to { background-position-x: -200%; } }

.fade-in { animation: fadeIn 0.4s ease-in; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

.badge-hr-pill {
    font-size: 0.85rem;
    padding: 6px 12px;
    border-radius: 20px;
    cursor: pointer;
    border: 1px solid var(--rd-border, #E8E4DC);
    background: var(--rd-neutral-100, #F7F5F0);
    color: var(--rd-text1, #292824);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.badge-hr-pill:hover {
    background: #0891b2;
    border-color: #0891b2;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(8, 145, 178, 0.2);
}

.recent-case-item {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid var(--rd-border, #E8E4DC);
    background: var(--rd-surface, #ffffff);
    margin-bottom: 6px;
    transition: all 0.2s ease;
    cursor: pointer;
}
.recent-case-item:hover {
    transform: translateX(3px);
    border-color: #3b82f6;
    background: var(--rd-neutral-50, #FAF9F6);
}
.hub-scroll-box {
    max-height: 250px;
    overflow-y: auto;
    padding-right: 4px;
}
.hub-scroll-box::-webkit-scrollbar {
    width: 4px;
}
.hub-scroll-box::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.15);
    border-radius: 4px;
}
</style>

<div class="content-wrapper division-dashboard pt-3">
    
    <!-- Top Executive Header & Command Quick Actions -->
    <div class="content-header px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span id="unitCodeBadge" class="badge badge-dark px-2 py-1 rajdhani" style="font-size: 13px; letter-spacing: 1px;">DIVISION</span>
                    <h1 class="m-0 text-dark rajdhani font-weight-bold" style="font-size: 1.8rem;" id="headerDivisionTitle">
                        {{ $title }}
                    </h1>
                </div>
                <div class="text-muted small" id="headerDivisionSub">
                    Real-time Operational Telemetry & Resource Hub
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-2 mt-md-0">
                <span id="syncBadge" class="live-pill shadow-sm">
                    <span class="live-dot"></span> <span id="syncText">SYNCING</span>
                </span>
                <button type="button" class="btn btn-action-hub" id="btnRefresh" title="Refresh Dashboard Telemetry">
                    <i class="fas fa-sync-alt" id="refreshIcon"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Quick Action Hub Bar -->
        <div class="card card-cyber p-3 mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-bolt text-warning mr-2"></i>
                    <span class="font-weight-bold text-dark rajdhani mr-3" style="font-size: 1.05rem;">DIVISION QUICK ACTIONS:</span>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('purchase.initiation.index') }}" class="btn btn-action-hub btn-action-primary shadow-sm">
                        <i class="fas fa-file-invoice-dollar"></i> Purchase Cases Hub
                    </a>
                    <a href="{{ route('purchase.select') }}" class="btn btn-action-hub shadow-sm">
                        <i class="fas fa-plus-circle text-success"></i> + Initiate Case
                    </a>
                    <a href="{{ route('inventory.assets.index') }}" class="btn btn-action-hub shadow-sm">
                        <i class="fas fa-boxes text-info"></i> Asset & Store Hub
                    </a>
                    <a href="{{ route('divhr.employelist') }}" class="btn btn-action-hub shadow-sm">
                        <i class="fas fa-users text-success"></i> Division HR
                    </a>
                    <a href="{{ route('division.finance-of-project.index') }}" class="btn btn-action-hub shadow-sm">
                        <i class="fas fa-chart-pie text-secondary"></i> Financial Reports
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Skeletons (Shown initially while fetching) -->
    <section class="content px-4" id="skeletonLayout">
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3"><div class="skeleton" style="height: 110px;"></div></div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3"><div class="skeleton" style="height: 110px;"></div></div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3"><div class="skeleton" style="height: 110px;"></div></div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3"><div class="skeleton" style="height: 110px;"></div></div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3"><div class="skeleton" style="height: 110px;"></div></div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3"><div class="skeleton" style="height: 110px;"></div></div>
        </div>
        <div class="row mb-4">
            <div class="col-lg-8 mb-3"><div class="skeleton" style="height: 320px;"></div></div>
            <div class="col-lg-4 mb-3"><div class="skeleton" style="height: 320px;"></div></div>
        </div>
    </section>

    <!-- Main Content (Hidden initially, animated in) -->
    <section class="content px-4 d-none fade-in" id="mainLayout">
        
        <!-- Top 6 Executive KPI Metric Cards -->
        <div class="row mb-4">
            <!-- 1. Total Projects -->
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="metric-card accent-cyan h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase font-weight-bold small" style="letter-spacing: 0.5px;">Portfolio</span>
                            <h2 class="rajdhani font-weight-bold text-dark mb-0 mt-1" id="val-total-projects">0</h2>
                        </div>
                        <div class="metric-icon-bg bg-cyan-soft">
                            <i class="fas fa-layer-group"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top d-flex flex-wrap gap-1 text-xs">
                        <span class="badge badge-success px-1 py-1"><span id="kpi-approved-prj">0</span> Approved</span>
                        <span class="badge badge-info px-1 py-1"><span id="kpi-inproc-prj">0</span> In Process</span>
                        <span class="badge badge-secondary px-1 py-1"><span id="kpi-completed-prj">0</span> Done</span>
                    </div>
                </div>
            </div>

            <!-- 2. Approved Allocation -->
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="metric-card accent-emerald h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase font-weight-bold small" style="letter-spacing: 0.5px;">Approved Budget</span>
                            <h2 class="rajdhani font-weight-bold text-success mb-0 mt-1" id="val-total-amount">0</h2>
                        </div>
                        <div class="metric-icon-bg bg-emerald-soft">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top text-xs text-muted">
                        <i class="fas fa-check-double text-success mr-1"></i> Total Sanctioned Allocation
                    </div>
                </div>
            </div>

            <!-- 3. Expended & Burn Rate -->
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="metric-card accent-amber h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase font-weight-bold small" style="letter-spacing: 0.5px;">Expended</span>
                            <h2 class="rajdhani font-weight-bold text-danger mb-0 mt-1" id="val-total-spent">0</h2>
                        </div>
                        <div class="metric-icon-bg bg-amber-soft">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top">
                        <div class="d-flex justify-content-between text-xs mb-1">
                            <span class="text-muted">Burn Rate:</span>
                            <b id="burn-rate-text" class="text-dark">0%</b>
                        </div>
                        <div class="progress" style="height: 6px; border-radius: 4px;">
                            <div id="burn-rate-bar" class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Available Headroom (Remaining) -->
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="metric-card accent-indigo h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase font-weight-bold small" style="letter-spacing: 0.5px;">Headroom (Left)</span>
                            <h2 class="rajdhani font-weight-bold text-primary mb-0 mt-1" id="val-total-remaining">0</h2>
                        </div>
                        <div class="metric-icon-bg bg-indigo-soft">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top text-xs text-muted" id="commitments-info">
                        <i class="fas fa-lock text-indigo mr-1"></i> Commitments: <b id="val-commitments">0</b>
                    </div>
                </div>
            </div>

            <!-- 5. Assets & Store Custody -->
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="metric-card accent-purple h-100 d-flex flex-column justify-content-between clickable-card" onclick="window.location.href='{{ route('inventory.assets.index') }}'" title="Click to view assets hub">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase font-weight-bold small" style="letter-spacing: 0.5px;">Store & Assets</span>
                            <h2 class="rajdhani font-weight-bold text-dark mb-0 mt-1" id="val-total-assets">0</h2>
                        </div>
                        <div class="metric-icon-bg bg-purple-soft">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top d-flex justify-content-between text-xs">
                        <span class="badge badge-success px-1 py-1" id="kpi-assets-on">On: 0</span>
                        <span class="badge badge-warning px-1 py-1" id="kpi-assets-off">Off: 0</span>
                    </div>
                </div>
            </div>

            <!-- 6. Procurement Cases Pipeline -->
            <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
                <div class="metric-card accent-blue h-100 d-flex flex-column justify-content-between clickable-card" onclick="window.location.href='{{ route('purchase.initiation.index') }}'" title="Click to view purchase cases hub">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-muted text-uppercase font-weight-bold small" style="letter-spacing: 0.5px;">Purchase Pipeline</span>
                            <h2 class="rajdhani font-weight-bold text-dark mb-0 mt-1" id="pc-total">0</h2>
                        </div>
                        <div class="metric-icon-bg bg-blue-soft">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top d-flex justify-content-between text-xs text-muted">
                        <span><i class="fas fa-spinner text-warning mr-1"></i> Active: <b id="pc-active-count">0</b></span>
                        <span class="text-primary font-weight-bold"><i class="fas fa-arrow-right"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visual Analytics Row (Charts) -->
        <div class="row mb-4">
            <!-- Project Financials Bar Chart -->
            <div class="col-lg-8 mb-3">
                <div class="card card-cyber h-100">
                    <div class="card-header bg-transparent border-0 d-flex flex-wrap justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-bar text-primary mr-2"></i>
                            <h5 class="card-title font-weight-bold rajdhani m-0" style="font-size: 1.15rem;">PROJECT HEAD FINANCIAL UTILIZATION</h5>
                        </div>
                        <div class="d-flex align-items-center gap-3 small text-muted">
                            <span><i class="fas fa-square text-primary mr-1"></i> Approved</span>
                            <span><i class="fas fa-square text-danger mr-1"></i> Expended</span>
                            <span><i class="fas fa-square text-warning mr-1"></i> Commitments</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 270px; position: relative;">
                            <canvas id="finChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Portfolio Stage Donut Chart -->
            <div class="col-lg-4 mb-3">
                <div class="card card-cyber h-100">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-pie text-success mr-2"></i>
                            <h5 class="card-title font-weight-bold rajdhani m-0" style="font-size: 1.15rem;">PROJECT STAGES</h5>
                        </div>
                        <span class="badge badge-light border text-muted small" id="stageChartSubtitle">Status Breakdown</span>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center">
                        <div style="height: 230px; position: relative;">
                            <canvas id="stageChart"></canvas>
                        </div>
                        <div class="mt-2 text-center text-muted small" id="stageChartSummary">
                            Active Project Lifecycle Distribution
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Project Portfolio Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card card-cyber">
                    <div class="card-header bg-transparent border-bottom d-flex flex-wrap align-items-center justify-content-between py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-project-diagram text-info mr-2"></i>
                            <h5 class="card-title font-weight-bold rajdhani m-0" style="font-size: 1.15rem;">PROJECT PORTFOLIO DIRECTORY</h5>
                        </div>
                        <div class="mt-2 mt-md-0" style="min-width: 280px;">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" id="customSearchBox" class="form-control border-left-0" placeholder="Filter by title, head code, status...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="projectsTable" class="table table-cyber table-hover w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th width="4%">#</th>
                                        <th>Project Name & Head</th>
                                        <th width="14%">Approved (PKR)</th>
                                        <th width="14%">Expended (PKR)</th>
                                        <th width="14%">Burn Rate</th>
                                        <th width="12%">Milestones</th>
                                        <th width="10%">Team</th>
                                        <th width="10%">Status</th>
                                        <th width="8%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Injected via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operational Hubs: HR Resource Mapping & Recent Purchases Stream -->
        <div class="row mb-4">
            <!-- Left: HR Resource Allocation -->
            <div class="col-lg-6 mb-3">
                <div class="card card-cyber h-100 d-flex flex-column">
                    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-friends text-teal mr-2"></i>
                            <h5 class="card-title font-weight-bold rajdhani m-0" style="font-size: 1.15rem;">HR RESOURCE MAPPING BY PROJECT</h5>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-info px-2 py-1">Active Staff: <b id="hr-total">0</b></span>
                            <a href="{{ route('divhr.employelist') }}" class="btn btn-sm btn-outline-secondary" title="View Full Employee Roster">
                                Roster <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between p-3">
                        <div>
                            <div class="d-flex align-items-center justify-content-between p-2 mb-3 rounded" style="background: var(--rd-neutral-100, #F7F5F0); border: 1px solid var(--rd-border, #E8E4DC);">
                                <span class="small text-muted"><i class="fas fa-users text-teal mr-1"></i> Deployed Staff: <b class="text-dark" id="hr-total-stat">0</b></span>
                                <span class="small text-muted"><i class="fas fa-layer-group text-primary mr-1"></i> Project Heads Covered: <b class="text-dark" id="hr-heads-count">0</b></span>
                            </div>
                            <div class="d-flex flex-wrap gap-2 align-items-start hub-scroll-box" id="hr-badges-container" style="min-height: 140px;">
                                <!-- Injected via JS -->
                            </div>
                        </div>
                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                            <span class="text-muted text-xs"><i class="fas fa-info-circle mr-1 text-info"></i> Click any project head pill above to view staff roster</span>
                            <a href="{{ route('divhr.employelist') }}" class="btn btn-xs btn-outline-info font-weight-bold px-2 py-1">
                                Full Roster <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Recent Purchase Cases & Pipeline Stream -->
            <div class="col-lg-6 mb-3">
                <div class="card card-cyber h-100 d-flex flex-column">
                    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-stream text-primary mr-2"></i>
                            <h5 class="card-title font-weight-bold rajdhani m-0" style="font-size: 1.15rem;">RECENT PURCHASE CASES & SCRUTINY</h5>
                        </div>
                        <a href="{{ route('purchase.initiation.index') }}" class="btn btn-sm btn-outline-primary" title="View All Purchase Cases">
                            View All <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between p-3">
                        <div class="hub-scroll-box" id="recent-cases-container" style="min-height: 160px;">
                            <!-- Injected via JS -->
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <div class="text-xs text-muted mb-1 font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Pipeline Status Summary:</div>
                            <div class="d-flex flex-wrap gap-1" id="pc-breakdown-container">
                                <!-- Case status pills injected via JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
</div>

@push('scripts')
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<!-- DataTables JS -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    let finChartInstance = null;
    let stageChartInstance = null;
    let dataTableInstance = null;

    const formatMoney = (val) => {
        val = Number(val) || 0;
        if(Math.abs(val) >= 1000000000) return (val/1000000000).toFixed(2) + ' B';
        if(Math.abs(val) >= 1000000) return (val/1000000).toFixed(2) + ' M';
        if(Math.abs(val) >= 1000) return (val/1000).toFixed(1) + ' k';
        return new Intl.NumberFormat('en-US').format(Math.round(val));
    };

    const formatFullNumber = (val) => {
        return new Intl.NumberFormat('en-US').format(Math.round(Number(val) || 0));
    };

    function loadDashboardData() {
        const syncBadge = document.getElementById('syncBadge');
        const syncText = document.getElementById('syncText');
        const refreshIcon = document.getElementById('refreshIcon');
        
        syncText.innerText = 'SYNCING...';
        refreshIcon.classList.add('fa-spin');

        fetch('{{ route("dashboard.data.div") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(response => {
            if(!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            document.getElementById('skeletonLayout').classList.add('d-none');
            document.getElementById('mainLayout').classList.remove('d-none');
            
            syncText.innerText = 'LIVE TELEMETRY';
            refreshIcon.classList.remove('fa-spin');

            // 1. Division Header Title
            if (data.unit) {
                const titleElem = document.getElementById("headerDivisionTitle");
                const codeBadge = document.getElementById("unitCodeBadge");
                const subElem = document.getElementById("headerDivisionSub");
                if (titleElem && data.unit.unt_name) titleElem.innerText = data.unit.unt_name + ' Dashboard';
                if (codeBadge && data.unit.unt_namesh) codeBadge.innerText = data.unit.unt_namesh;
                if (subElem) subElem.innerText = `Division: ${data.unit.unt_name || ''} (${data.unit.unt_namesh || ''}) | Real-time Portfolio & Financial Overview`;
            }

            // 2. Financial Top Metrics
            document.getElementById("val-total-projects").innerText = data.totalProjects || 0;
            document.getElementById("val-total-amount").innerText = 'PKR ' + formatMoney(data.finSummary.total);
            document.getElementById("val-total-spent").innerText = 'PKR ' + formatMoney(data.finSummary.spent);
            document.getElementById("val-total-remaining").innerText = 'PKR ' + formatMoney(data.finSummary.remaining);

            // Project Sub-pills
            if (data.projectsByStage) {
                document.getElementById("kpi-approved-prj").innerText = data.projectsByStage.open || 0;
                document.getElementById("kpi-inproc-prj").innerText = data.projectsByStage.in_process || 0;
                document.getElementById("kpi-completed-prj").innerText = data.projectsByStage.completed || 0;
            }

            // Burn Rate Progress Bar
            const burnPct = Math.min(100, Math.max(0, data.finSummary.utilization_rate || 0));
            const burnBar = document.getElementById("burn-rate-bar");
            const burnText = document.getElementById("burn-rate-text");
            if (burnBar) {
                burnBar.style.width = burnPct + '%';
                if (burnPct > 90) {
                    burnBar.className = 'progress-bar bg-danger';
                } else if (burnPct > 65) {
                    burnBar.className = 'progress-bar bg-warning';
                } else {
                    burnBar.className = 'progress-bar bg-success';
                }
            }
            if (burnText) burnText.innerText = burnPct + '%';

            // Commitments
            const commVal = document.getElementById("val-commitments");
            if (commVal) commVal.innerText = 'PKR ' + formatMoney(data.finSummary.commitments || 0);

            // Assets KPI
            if (data.assetStats) {
                document.getElementById("val-total-assets").innerText = data.assetStats.total_items || 0;
                document.getElementById("kpi-assets-on").innerText = 'On: ' + (data.assetStats.on_charge_count || 0);
                document.getElementById("kpi-assets-off").innerText = 'Off: ' + (data.assetStats.off_charge_count || 0);
            }

            // Purchase Stats KPI
            if (data.purchaseStats) {
                document.getElementById("pc-total").innerText = data.purchaseStats.total || 0;
                let activeCount = 0;
                if (data.purchaseStats.breakdown) {
                    for (const [st, ct] of Object.entries(data.purchaseStats.breakdown)) {
                        if (!['Completed', 'Fulfilled', 'Rejected'].includes(st)) {
                            activeCount += ct;
                        }
                    }
                }
                document.getElementById("pc-active-count").innerText = activeCount;
            }

            // 3. HR Resource Badges
            const hrContainer = document.getElementById("hr-badges-container");
            hrContainer.innerHTML = '';
            let hrTotal = 0;
            let hrHeadsCount = 0;
            (data.projectsList || []).forEach(p => {
                if (p.team_count > 0) {
                    hrTotal += parseInt(p.team_count, 10);
                    hrHeadsCount++;
                    hrContainer.innerHTML += `
                        <div class="badge-hr-pill shadow-sm" onclick="window.location.href='/divhr/employelist?head_code=${encodeURIComponent(p.head_code)}'">
                            <i class="fas fa-users text-teal"></i>
                            <span><b>${p.head_code}</b>: ${p.team_count} staff</span>
                        </div>
                    `;
                }
            });
            document.getElementById("hr-total").innerText = hrTotal;
            const statElem = document.getElementById("hr-total-stat");
            if (statElem) statElem.innerText = hrTotal;
            const headsElem = document.getElementById("hr-heads-count");
            if (headsElem) headsElem.innerText = hrHeadsCount;

            if (hrContainer.innerHTML.trim() === '') {
                hrContainer.innerHTML = '<span class="text-muted small">No staff currently mapped to division heads.</span>';
            }

            // 4. Recent Purchase Cases List
            const recentContainer = document.getElementById("recent-cases-container");
            recentContainer.innerHTML = '';
            const recentList = (data.purchaseStats && data.purchaseStats.recent) ? data.purchaseStats.recent : [];
            
            const statusBadges = {
                'Draft': 'badge-secondary',
                'In Progress': 'badge-primary',
                'Under Scrutiny': 'badge-warning',
                'Under Approval': 'badge-warning',
                'Under Revision': 'badge-danger',
                'Approved': 'badge-success',
                'Fulfilled': 'badge-success',
                'Partially Fulfilled': 'badge-info',
                'Completed': 'badge-success',
                'Cancelled': 'badge-secondary',
                'Rejected': 'badge-danger'
            };

            if (recentList.length === 0) {
                recentContainer.innerHTML = '<div class="text-muted small py-2">No purchase cases found for this division.</div>';
            } else {
                recentList.forEach(c => {
                    const badgeClass = statusBadges[c.pcs_status] || 'badge-info';
                    const caseTitle = c.pcs_title || 'Untitled Case';
                    const priceFormatted = c.pcs_price ? 'PKR ' + formatMoney(c.pcs_price) : 'N/A';
                    recentContainer.innerHTML += `
                        <div class="recent-case-item d-flex justify-content-between align-items-center" onclick="window.location.href='/pc-initiation/case/${c.pcs_id}'">
                            <div class="d-flex align-items-center text-truncate mr-2">
                                <span class="badge badge-light border text-primary font-weight-bold mr-2" style="font-size: 11px;">#${c.pcs_id}</span>
                                <div class="text-truncate">
                                    <div class="font-weight-bold text-dark text-truncate" style="font-size: 13px; max-width: 250px;">${caseTitle}</div>
                                    <div class="text-muted text-xs"><i class="fas fa-tag mr-1"></i>${c.hed_code || 'General'} &bull; ${priceFormatted}</div>
                                </div>
                            </div>
                            <span class="badge ${badgeClass} px-2 py-1 text-xs" style="white-space: nowrap;">${c.pcs_status || 'Active'}</span>
                        </div>
                    `;
                });
            }

            // Purchase Breakdown Pills
            const pcContainer = document.getElementById("pc-breakdown-container");
            pcContainer.innerHTML = '';
            if (data.purchaseStats && data.purchaseStats.breakdown) {
                for (const [st, ct] of Object.entries(data.purchaseStats.breakdown)) {
                    let cls = statusBadges[st] || 'badge-light border text-dark';
                    pcContainer.innerHTML += `<span class="badge ${cls} mr-1 mb-1" style="font-size: 11px; padding: 3px 8px; font-weight: 500;">${st}: <b class="ml-1 font-weight-bold">${ct}</b></span>`;
                }
            }

            // 5. Populate Data Table
            const tbody = document.querySelector("#projectsTable tbody");
            tbody.innerHTML = '';
            (data.projectsList || []).forEach((p, idx) => {
                let statusBadge = `<span class="badge badge-secondary px-2 py-1">${p.status}</span>`;
                if (p.status === 'Approved') statusBadge = `<span class="badge badge-warning px-2 py-1">Approved</span>`;
                if (p.status === 'Completed') statusBadge = `<span class="badge badge-success px-2 py-1">Completed</span>`;

                const burnColor = p.burn_rate > 90 ? 'bg-danger' : (p.burn_rate > 65 ? 'bg-warning' : 'bg-success');
                const burnTextClass = p.burn_rate > 90 ? 'text-danger' : (p.burn_rate > 65 ? 'text-warning' : 'text-success');

                tbody.innerHTML += `
                    <tr class="clickable-row">
                        <td class="text-muted font-weight-bold">${idx + 1}</td>
                        <td>
                            <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">
                                ${p.title}
                            </div>
                            <div class="small text-muted">
                                <span class="badge badge-light border text-dark mr-1">${p.head_code}</span>
                                Approved: ${p.created_on}
                            </div>
                        </td>
                        <td class="rajdhani font-weight-bold text-dark" style="font-size: 1.05rem;">
                            PKR ${formatFullNumber(p.approved)}
                        </td>
                        <td class="rajdhani font-weight-bold text-danger" style="font-size: 1.05rem;">
                            PKR ${formatFullNumber(p.spent)}
                        </td>
                        <td>
                            <div class="d-flex justify-content-between text-xs mb-1">
                                <span class="text-muted">Burn:</span>
                                <b class="${burnTextClass}">${p.burn_rate}%</b>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 4px;">
                                <div class="progress-bar ${burnColor}" style="width: ${Math.min(100, p.burn_rate)}%"></div>
                            </div>
                        </td>
                        <td>
                            <div class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                                <i class="fas fa-tasks mr-1 text-info"></i> ${p.milestones}
                            </div>
                            <div class="text-xs text-muted mt-1">${p.milestones_pct}% Complete</div>
                        </td>
                        <td>
                            <span class="badge badge-info px-2 py-1">
                                <i class="fas fa-user mr-1"></i> ${p.team_count} Staff
                            </span>
                        </td>
                        <td>${statusBadge}</td>
                        <td class="text-center">
                            <a href="/openprojectdetails/${p.prj_id}" class="btn btn-sm btn-outline-primary font-weight-bold" title="Open Project Details">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </td>
                    </tr>
                `;
            });

            // Initialize or Re-draw DataTable
            if (dataTableInstance) {
                dataTableInstance.destroy();
            }
            dataTableInstance = $('#projectsTable').DataTable({
                "dom": "rtip",
                "pageLength": 5,
                "lengthChange": false, 
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "language": {
                    "emptyTable": "No projects registered under this division."
                }
            });

            $('#customSearchBox').off('keyup').on('keyup', function() {
                dataTableInstance.search(this.value).draw();
            });

            // 6. Draw Stage Chart (Doughnut)
            renderStageChart(data.projectsByStage || { open: 0, in_process: 0, completed: 0 });

            // 7. Draw Financial Chart (Bar)
            renderFinancialChart(data.financials || []);

        })
        .catch(error => {
            console.error('Error fetching dashboard telemetry:', error);
            syncText.innerText = 'OFFLINE / RETRY';
            refreshIcon.classList.remove('fa-spin');
        });
    }

    function renderStageChart(stages) {
        const ctx = document.getElementById('stageChart').getContext('2d');
        if (stageChartInstance) stageChartInstance.destroy();

        const totalPrj = (stages.open || 0) + (stages.in_process || 0) + (stages.completed || 0);
        document.getElementById('stageChartSummary').innerText = `${totalPrj} Total Projects across division lifecycle`;

        stageChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'In Process', 'Completed'],
                datasets: [{
                    data: [stages.open || 0, stages.in_process || 0, stages.completed || 0],
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutoutPercentage: 72,
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        fontFamily: "'Inter', sans-serif",
                        fontSize: 12,
                        padding: 14
                    }
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            const dataset = data.datasets[tooltipItem.datasetIndex];
                            const current = dataset.data[tooltipItem.index];
                            return `${data.labels[tooltipItem.index]}: ${current} Project(s)`;
                        }
                    }
                }
            }
        });
    }

    function renderFinancialChart(finData) {
        const ctx = document.getElementById('finChart').getContext('2d');
        if (finChartInstance) finChartInstance.destroy();

        const labels = finData.map(f => f.head_code);
        const approved = finData.map(f => f.approved);
        const spent = finData.map(f => Math.abs(f.spent));
        const commitments = finData.map(f => f.commitments || 0);

        finChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Approved Budget',
                        backgroundColor: '#3b82f6',
                        data: approved,
                        borderRadius: 4
                    },
                    {
                        label: 'Expended (Spent)',
                        backgroundColor: '#ef4444',
                        data: spent,
                        borderRadius: 4
                    },
                    {
                        label: 'Commitments',
                        backgroundColor: '#f59e0b',
                        data: commitments,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        ticks: {
                            fontFamily: "'Rajdhani', sans-serif",
                            fontStyle: 'bold',
                            fontSize: 13
                        }
                    }],
                    yAxes: [{
                        gridLines: { color: 'rgba(0,0,0,0.04)' },
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                if (value >= 1000000000) return (value/1000000000).toFixed(1) + 'B';
                                if (value >= 1000000) return (value/1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value/1000).toFixed(0) + 'k';
                                return value;
                            }
                        }
                    }]
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(tooltipItem, data) {
                            const val = Number(tooltipItem.yLabel) || 0;
                            return `${data.datasets[tooltipItem.datasetIndex].label}: PKR ${val.toLocaleString()}`;
                        }
                    }
                }
            }
        });
    }

    // Refresh telemetry trigger
    document.getElementById('btnRefresh').addEventListener('click', function() {
        loadDashboardData();
    });

    // Initial load
    loadDashboardData();

});
</script>
@endpush
@endsection
