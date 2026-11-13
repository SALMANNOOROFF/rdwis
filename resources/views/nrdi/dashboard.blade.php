@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

    .content-wrapper.command-dashboard {
        font-family: 'Inter', sans-serif;
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        min-height: calc(100vh - 56px);
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
    }

    /* Solid Executive Cards */
    .card-cyber {
        background: #ffffff !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .card-cyber:hover {
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.12);
    }

    .kpi-card {
        padding: 12px 14px !important;
        min-height: 122px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Colored Left Accent Bars */
    .card-accent-blue { border-left: 4px solid #2563eb !important; }
    .card-accent-amber { border-left: 4px solid #d97706 !important; }
    .card-accent-purple { border-left: 4px solid #7c3aed !important; }
    .card-accent-green { border-left: 4px solid #059669 !important; }
    .card-accent-cyan { border-left: 4px solid #0891b2 !important; }
    .card-accent-orange { border-left: 4px solid #ea580c !important; }
    .card-accent-indigo { border-left: 4px solid #4f46e5 !important; }

    /* Project Status Scope Button Styling */
    .status-scope-group .btn {
        font-size: 12px;
        font-weight: 800;
        padding: 5px 12px;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .status-scope-group .btn.active {
        background-color: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(2, 132, 199, 0.4);
    }
    .status-scope-group .btn:not(.active) {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    /* 7 KPI Cards Responsive Grid */
    .kpi-grid-7 {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
    }
    @media (max-width: 1550px) {
        .kpi-grid-7 {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    @media (max-width: 992px) {
        .kpi-grid-7 {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 576px) {
        .kpi-grid-7 {
            grid-template-columns: 1fr;
        }
    }

    /* Division Pill Navigation Bar */
    .pill-nav {
        background: #e2e8f0;
        border: 1.5px solid #cbd5e1;
        border-radius: 20px;
        padding: 4px 8px;
        display: inline-flex;
        flex-wrap: wrap;
        gap: 5px;
        align-items: center;
    }
    .pill-btn {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        color: #0f172a;
        padding: 4px 14px;
        border-radius: 16px;
        font-size: 12.5px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .pill-btn:hover:not(.active) {
        background: #f8fafc;
        color: #000000;
    }
    .pill-btn.active {
        background: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #ffffff !important;
        box-shadow: 0 1px 4px rgba(2, 132, 199, 0.4);
    }

    /* Project Chip Buttons */
    .chip-btn {
        border: none;
        background: transparent;
        padding: 0;
        cursor: pointer;
    }
    .chip-code {
        padding: 3px 10px;
        font-size: 11.5px;
        border-radius: 14px;
        font-weight: 800;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        color: #0f172a;
        display: inline-block;
        transition: all 0.15s;
    }
    .chip-code:hover {
        border-color: #0284c7;
        color: #0284c7;
    }
    .chip-code.chip-active {
        background: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #ffffff !important;
        box-shadow: 0 1px 4px rgba(2, 132, 199, 0.45);
    }

    /* Scope Switcher Styling */
    .scope-btn-group .btn {
        font-size: 12.5px;
        font-weight: 800;
        padding: 5px 14px;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .scope-btn-group .btn.active {
        background-color: #0284c7 !important;
        border-color: #0284c7 !important;
        color: #ffffff !important;
        box-shadow: 0 1px 3px rgba(2, 132, 199, 0.4);
    }
    .scope-btn-group .btn:not(.active) {
        background-color: #ffffff !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
        font-weight: 800;
    }

    /* KPI Text Elements - Zero Truncation */
    .kpi-title {
        font-size: 12px;
        color: #0f172a !important;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    .kpi-value {
        font-family: 'Rajdhani', sans-serif;
        font-size: 1.55rem;
        font-weight: 800;
        color: #0f172a !important;
        line-height: 1.1;
        margin-bottom: 4px;
    }
    .kpi-sub-main {
        font-size: 12.5px;
        font-weight: 800;
        color: #0f172a !important;
        line-height: 1.3;
    }
    .kpi-sub-desc {
        font-size: 11.5px;
        font-weight: 700;
        color: #334155 !important;
        line-height: 1.25;
    }

    /* Project Strip & Sidebar Items */
    .proj-item {
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 6px;
        padding: 8px 10px;
        transition: all 0.15s ease-in-out;
        text-decoration: none !important;
        display: block;
        cursor: pointer;
    }
    .proj-item:hover {
        background: #f8fafc;
        border-color: #0284c7 !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }
    .proj-item-delayed { border-left: 4px solid #ef4444 !important; }
    .proj-item-ongoing { border-left: 4px solid #0284c7 !important; }
    .proj-item-completed { border-left: 4px solid #10b981 !important; }

    /* Badges */
    .badge-status {
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: 0.4px;
        text-transform: uppercase;
    }
    .badge-delayed {
        background: #fee2e2 !important;
        color: #b91c1c !important;
        border: 1px solid #fca5a5;
    }
    .badge-ongoing {
        background: #e0f2fe !important;
        color: #0369a1 !important;
        border: 1px solid #bae6fd;
    }
    .badge-completed {
        background: #dcfce7 !important;
        color: #15803d !important;
        border: 1px solid #bbf7d0;
    }
</style>

<div class="content-wrapper command-dashboard pt-2 pb-3">
    <div class="content">
        <div class="container-fluid">
            <!-- 1. TOP ROW: Title & Division Filter Pill Bar -->
            <div class="card-cyber px-3 py-2 mb-2 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                    <div class="d-flex align-items-center flex-shrink-0 mr-2">
                        <span class="font-weight-bold rajdhani text-dark mr-2" style="font-size: 1.5rem; color: #0f172a !important; font-weight: 800;">
                            <i class="fas fa-chart-line text-primary mr-1"></i> NRDI COMMAND
                        </span>
                        <span class="badge badge-primary px-2.5 py-1 font-weight-bold" style="font-size: 11px; background: #0284c7; letter-spacing: 0.4px;">HQ VIEW</span>
                    </div>

                    <!-- Horizontal Divisions Pill Buttons -->
                    <div class="d-flex align-items-center flex-fill overflow-auto py-1" style="max-width: 100%;">
                        <div class="pill-nav shadow-sm flex-wrap" id="division-selector">
                            <!-- Populated dynamically: All button first, then each division -->
                            <button class="pill-btn active" data-id="all" onclick="selectDivision('all')">★ All Divisions</button>
                        </div>
                    </div>

                    <!-- Reset Filter Button -->
                    <button class="btn btn-sm btn-outline-secondary font-weight-bold shadow-sm px-3 py-1" style="border-radius: 6px; font-size: 12px; font-weight: 800; border: 1.5px solid #cbd5e1; color: #0f172a;" onclick="resetAllFilters()" title="Reset All Filters">
                        <i class="fas fa-sync-alt mr-1"></i> Reset
                    </button>
                </div>
            </div>

            <!-- 2. CONDITIONAL PROJECT STRIP (Appears when a specific division is selected) -->
            <div class="card-cyber px-3 py-2 mb-2 bg-white d-none" id="project-strip-container">
                <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap">
                    <div class="d-flex align-items-center">
                        <span class="font-weight-bold text-dark text-uppercase tracking-wider mr-2" style="font-size: 12.5px; font-weight: 800; color: #0f172a !important;">
                            <i class="fas fa-folder-open text-primary mr-1"></i> <span id="strip-div-name">Division</span> Projects:
                        </span>
                        <span class="badge badge-secondary font-weight-bold px-2 py-0.5" id="active-projects-count" style="font-size: 11px;">0 Active</span>
                    </div>
                    <button class="btn btn-sm btn-outline-danger px-2.5 font-weight-bold" id="clear-project-filter-btn" style="border-radius: 12px; font-size: 11.5px; font-weight: 800; display: none;" onclick="clearProjectFilter()">
                        <i class="fas fa-times-circle mr-1"></i> Clear Project Filter
                    </button>
                </div>
                <div class="d-flex flex-wrap overflow-auto py-1" id="project-strip-inner" style="gap: 6px; max-height: 85px;">
                    <!-- Project Chips dynamically inserted here -->
                </div>
            </div>

            <!-- 3. SEPARATE ROW: Fund Share Scope Switcher & Project Lifecycle Scope -->
            <div class="card-cyber px-3 py-2 mb-2 bg-white">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 12px;">
                    <!-- Left: Fund Share Scope -->
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="font-weight-bold mr-2 text-dark" style="font-size: 13px; font-weight: 800; color: #0f172a !important;">
                            <i class="fas fa-layer-group text-primary mr-1"></i> Fund Share Scope:
                        </span>
                        <div class="btn-group btn-group-sm scope-btn-group shadow-sm">
                            <button type="button" class="btn active" id="scope-btn-total" onclick="setFundScope('total')">
                                Total Overall
                            </button>
                            <button type="button" class="btn" id="scope-btn-rdw" onclick="setFundScope('rdw')">
                                NRDI Share
                            </button>
                            <button type="button" class="btn" id="scope-btn-csrf" onclick="setFundScope('csrf')">
                                CSRF Share
                            </button>
                        </div>
                    </div>

                    <!-- Right: Active / Completed Projects Lifecycle Scope -->
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="font-weight-bold mr-2 text-dark" style="font-size: 13px; font-weight: 800; color: #0f172a !important;">
                            <i class="fas fa-project-diagram text-primary mr-1"></i> Projects:
                        </span>
                        <div class="btn-group btn-group-sm status-scope-group shadow-sm">
                            <button type="button" class="btn font-weight-bold active" id="status-scope-active" onclick="setStatusScope('active')">
                                <i class="fas fa-bolt text-warning mr-1"></i> Active Projects (<span id="status-cnt-active">91</span>)
                            </button>
                            <button type="button" class="btn font-weight-bold" id="status-scope-completed" onclick="setStatusScope('completed')">
                                <i class="fas fa-check-circle text-success mr-1"></i> Completed (<span id="status-cnt-completed">31</span>)
                            </button>
                            <button type="button" class="btn font-weight-bold" id="status-scope-all" onclick="setStatusScope('all')">
                                All (<span id="status-cnt-all">122</span>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. 7 EXECUTIVE KPI METRIC CARDS GRID (Zero Truncation) -->
            <div class="kpi-grid-7 mb-2">
                <!-- 1. Sanctioned Share (in Millions) -->
                <div>
                    <div class="card-cyber kpi-card card-accent-blue h-100">
                        <div class="kpi-title d-flex justify-content-between align-items-center">
                            <span id="kpi-share-title">Total Project Allocation</span>
                            <i class="fas fa-shield-alt" style="font-size: 11px; color: #2563eb;"></i>
                        </div>
                        <div class="kpi-value text-primary" id="kpi-share-value" style="color: #2563eb !important;">Rs 0.00 M</div>
                        <div class="kpi-sub-main" id="kpi-share-sub-main">Rs 0</div>
                        <div class="kpi-sub-desc" id="kpi-share-sub-desc">Total Sanctioned (All Shares)</div>
                    </div>
                </div>

                <!-- 2. Total Utilized (Exp + Commitments) -->
                <div>
                    <div class="card-cyber kpi-card card-accent-amber h-100">
                        <div class="kpi-title d-flex justify-content-between align-items-center">
                            <span id="kpi-utilized-title">Total Utilized</span>
                            <i class="fas fa-calculator" style="font-size: 11px; color: #d97706;"></i>
                        </div>
                        <div class="kpi-value" id="kpi-utilized-value" style="color: #d97706 !important;">Rs 0.00 M</div>
                        <div class="kpi-sub-main" id="kpi-utilized-sub-main">Exp: Rs 0.00 M</div>
                        <div class="kpi-sub-desc" id="kpi-utilized-sub-desc">Cmt: Rs 0.00 M</div>
                    </div>
                </div>

                <!-- 3. In Process Pipeline -->
                <div>
                    <div class="card-cyber kpi-card card-accent-purple h-100">
                        <div class="kpi-title d-flex justify-content-between align-items-center">
                            <span>In Process Total</span>
                            <i class="fas fa-stream" style="font-size: 11px; color: #7c3aed;"></i>
                        </div>
                        <div class="kpi-value" id="kpi-inproc-value" style="color: #7c3aed !important;">Rs 0.00 M</div>
                        <div class="kpi-sub-main" id="kpi-inproc-sub-main">Rs 0</div>
                        <div class="kpi-sub-desc">Active In-Process IPC</div>
                    </div>
                </div>

                <!-- 4. Remaining Share Balance -->
                <div>
                    <div class="card-cyber kpi-card card-accent-green h-100">
                        <div class="kpi-title d-flex justify-content-between align-items-center">
                            <span id="kpi-remaining-title">Total Remaining Balance</span>
                            <i class="fas fa-wallet" style="font-size: 11px; color: #059669;"></i>
                        </div>
                        <div class="kpi-value" id="kpi-remaining-value" style="color: #059669 !important;">Rs 0.00 M</div>
                        <div class="kpi-sub-main" id="kpi-remaining-sub-main">Rs 0</div>
                        <div class="kpi-sub-desc">Available Net Buffer</div>
                    </div>
                </div>

                <!-- 5. Yet to be Received by MTSS (Balance Due from MTSS) -->
                <div>
                    <div class="card-cyber kpi-card card-accent-orange h-100">
                        <div class="kpi-title d-flex justify-content-between align-items-center">
                            <span>Yet to be Received (MTSS)</span>
                            <i class="fas fa-hourglass-half" style="font-size: 11px; color: #ea580c;"></i>
                        </div>
                        <div class="kpi-value" id="kpi-mtss-pending-value" style="color: #ea580c !important;">Rs 0.00 M</div>
                        <div class="kpi-sub-main" id="kpi-mtss-pending-sub-main">Rs 0</div>
                        <div class="kpi-sub-desc">Balance Due from MTSS</div>
                    </div>
                </div>

                <!-- 6. Total MTSS Share -->
                <div>
                    <div class="card-cyber kpi-card card-accent-cyan h-100">
                        <div class="kpi-title d-flex justify-content-between align-items-center">
                            <span>Total MTSS Share</span>
                            <i class="fas fa-external-link-alt" style="font-size: 11px; color: #0891b2;"></i>
                        </div>
                        <div class="kpi-value" id="kpi-mtss-value" style="color: #0891b2 !important;">Rs 0.00 M</div>
                        <div class="kpi-sub-main" id="kpi-mtss-sub-main">Rs 0</div>
                        <div class="kpi-sub-desc">Direct MTSS Project Share</div>
                    </div>
                </div>

                <!-- 7. Current Active Staff -->
                <div>
                    <div class="card-cyber kpi-card card-accent-indigo h-100">
                        <div class="kpi-title d-flex justify-content-between align-items-center">
                            <span id="kpi-staff-title">Current Staff</span>
                            <i class="fas fa-user-friends" style="font-size: 11px; color: #4f46e5;"></i>
                        </div>
                        <div class="kpi-value" id="kpi-staff-value" style="color: #4f46e5 !important;">0 Staff</div>
                        <div class="kpi-sub-main" id="kpi-staff-sub-main">0 CSRF Personnel</div>
                        <div class="kpi-sub-desc" id="kpi-staff-sub-desc">0 Core Division Staff</div>
                    </div>
                </div>
            </div>

            <!-- 5. MIDDLE MAIN SECTION: Trend Chart (8 cols) + Projects Stream (4 cols) -->
            <div class="row">
                <!-- Left 8 Columns: Budget vs Utilization Trends Chart -->
                <div class="col-xl-8 col-lg-7 mb-2">
                    <div class="card-cyber px-3 py-2.5 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                            <div class="d-flex align-items-center flex-wrap">
                                <span class="font-weight-bold text-dark mr-2" style="font-size: 14px; color: #0f172a !important; font-weight: 800;">
                                    <i class="fas fa-chart-bar text-primary mr-1"></i> Funds Received vs Expenditure Trends
                                </span>
                                <span class="badge badge-primary px-2.5 py-1 font-weight-bold text-uppercase" id="chart-scope-pill" style="font-size: 11px; background: #0284c7; letter-spacing: 0.3px;">
                                    Total Overall
                                </span>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info active font-weight-bold px-3 py-1" style="font-size: 12px; font-weight: 800;" id="mode-btn-monthly" onclick="setTimeMode('monthly')">Monthly</button>
                                <button class="btn btn-outline-info font-weight-bold px-3 py-1" style="font-size: 12px; font-weight: 800;" id="mode-btn-quarterly" onclick="setTimeMode('quarterly')">Quarterly</button>
                            </div>
                        </div>

                        <div style="height: 350px; position: relative;">
                            <canvas id="financeChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Right 4 Columns: Projects Log / Stream with Filter Switcher -->
                <div class="col-xl-4 col-lg-5 mb-2">
                    <div class="card-cyber px-3 py-2.5 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-dark" style="font-size: 14px; color: #0f172a !important; font-weight: 800;">
                                <i class="fas fa-stream text-primary mr-1"></i> Projects Stream
                            </span>
                            <input type="text" id="project-search" placeholder="Search project..." 
                                   class="form-control form-control-sm font-weight-bold" 
                                   style="width: 140px; border-radius: 6px; font-size: 12px; border: 1.5px solid #cbd5e1; color: #0f172a; padding: 4px 8px; font-weight: 700;"
                                   oninput="renderProjectSidebar()">
                        </div>

                        <!-- Switcher: All | Delayed | Ongoing | Completed -->
                        <div class="btn-group btn-group-sm w-100 mb-2 shadow-sm" style="border-radius: 6px;">
                            <button type="button" class="btn btn-outline-dark font-weight-bold active py-1" style="font-size: 12px; font-weight: 800;" id="pfilter-all" onclick="setProjectFilter('all')">
                                All (<span id="cnt-all">0</span>)
                            </button>
                            <button type="button" class="btn btn-outline-danger font-weight-bold py-1" style="font-size: 12px; font-weight: 800;" id="pfilter-delayed" onclick="setProjectFilter('delayed')">
                                Delayed (<span id="cnt-delayed">0</span>)
                            </button>
                            <button type="button" class="btn btn-outline-primary font-weight-bold py-1" style="font-size: 12px; font-weight: 800;" id="pfilter-ongoing" onclick="setProjectFilter('ongoing')">
                                Ongoing (<span id="cnt-ongoing">0</span>)
                            </button>
                            <button type="button" class="btn btn-outline-success font-weight-bold py-1" style="font-size: 12px; font-weight: 800;" id="pfilter-completed" onclick="setProjectFilter('completed')">
                                Done (<span id="cnt-completed">0</span>)
                            </button>
                        </div>

                        <!-- Projects Scroll Container -->
                        <div class="overflow-auto pr-1 flex-fill" id="project-list-container" style="max-height: 335px;">
                            <!-- Dynamically populated -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. LOWER ANALYTICS SECTION: 3 Executive Charts (Hired Staff by Year, Staff by Division, Projects Status) -->
            <div class="row mt-2">
                <!-- Left Chart: Hired Staff by Year (5-Year Turnover & Active Strength) -->
                <div class="col-xl-4 col-lg-4 col-md-12 mb-2">
                    <div class="card-cyber px-3 py-2.5 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-dark" style="font-size: 13.5px; color: #0f172a !important; font-weight: 800;">
                                <i class="fas fa-user-plus text-primary mr-1"></i> Hired Staff by Year
                            </span>
                            <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 11px; font-weight: 800; border: 1.5px solid #cbd5e1 !important;">
                                5-Year Turnover
                            </span>
                        </div>
                        <div style="height: 250px; position: relative;">
                            <canvas id="staffHistoryChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Middle Chart: Staff by Division (Doughnut) -->
                <div class="col-xl-4 col-lg-4 col-md-12 mb-2">
                    <div class="card-cyber px-3 py-2.5 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-dark" style="font-size: 13.5px; color: #0f172a !important; font-weight: 800;">
                                <i class="fas fa-users-cog text-primary mr-1"></i> Staff by Division
                            </span>
                            <span class="badge badge-primary px-2.5 py-1 font-weight-bold" style="font-size: 11px; background: #0284c7; letter-spacing: 0.3px;">
                                Total: <span id="donut-total-staff">76</span>
                            </span>
                        </div>
                        <div style="height: 250px; position: relative;">
                            <canvas id="staffDivChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Right Chart: Active Projects Status Portfolio (Ongoing & Delayed) -->
                <div class="col-xl-4 col-lg-4 col-md-12 mb-2">
                    <div class="card-cyber px-3 py-2.5 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-dark" style="font-size: 13.5px; color: #0f172a !important; font-weight: 800;">
                                <i class="fas fa-tasks text-primary mr-1"></i> Active Projects Status
                            </span>
                            <span class="badge badge-dark px-2.5 py-1 font-weight-bold" style="font-size: 11px; background: #0f172a; letter-spacing: 0.3px;">
                                Active: <span id="donut-total-projects">0</span>
                            </span>
                        </div>
                        <div style="height: 250px; position: relative;">
                            <canvas id="projectStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var oldChart = window.Chart;
</script>
<script src="{{ asset('plugins/chartjs4/chart.umd.js') }}"></script>
<script>
    window.Chart4 = window.Chart;
    window.Chart = oldChart;

    // State Store
    let activeDivision = 'all';
    let activeProject = null;
    let statusScope = 'active'; // 'active' | 'completed' | 'all'
    let fundScope = 'total'; // 'total' | 'rdw' | 'csrf'
    let timeMode = 'monthly'; // 'monthly' | 'quarterly'
    let projectFilter = 'all'; // 'all' | 'delayed' | 'ongoing' | 'completed'
    let apiData = null;
    let chartFinance = null;
    let chartStaffHistory = null;
    let chartStaffDiv = null;
    let chartProjectStatus = null;

    function formatMillions(val) {
        const n = Number(val || 0);
        return 'Rs ' + (n / 1000000).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' M';
    }

    function formatMoney(val) {
        const n = Number(val || 0);
        return 'Rs ' + n.toLocaleString(undefined, { maximumFractionDigits: 0 });
    }

    function loadDashboard(div = activeDivision, projId = activeProject) {
        activeDivision = div;
        activeProject = projId;

        let url = "{{ route('nrdi.dashboard.data') }}?division=" + encodeURIComponent(div)
            + "&status_scope=" + encodeURIComponent(statusScope);
        if (projId) {
            url += "&project_id=" + encodeURIComponent(projId);
        }

        $.getJSON(url, function(data) {
            apiData = data;

            // Render Division Selector pills on first load
            renderDivisionSelector(data.divisions || []);

            // Render project strip for the selected division
            renderProjectStrip(div);

            updateKpis();
            renderFinanceChart();
            renderProjectSidebar();
            renderStaffHistoryChart();
            renderStaffDivChart();
            renderProjectStatusChart();
        });
    }

    function setStatusScope(scope) {
        statusScope = scope;
        $('.status-scope-group .btn').removeClass('active');
        $('#status-scope-' + scope).addClass('active');
        loadDashboard(activeDivision, activeProject);
    }

    function renderDivisionSelector(divisions) {
        const selector = $('#division-selector');
        // If already rendered with items, update active class
        if (selector.children().length > 1) {
            selector.find('.pill-btn').removeClass('active');
            selector.find(`[data-id="${activeDivision}"]`).addClass('active');
            return;
        }

        selector.empty();

        // 1. "All Divisions" button
        const allBtn = $('<button class="pill-btn"></button>')
            .text('★ All Divisions')
            .attr('data-id', 'all')
            .click(function() {
                selectDivision('all');
            });
        if (activeDivision === 'all') allBtn.addClass('active');
        selector.append(allBtn);

        // 2. Each Division pill button
        divisions.forEach(d => {
            const btn = $('<button class="pill-btn"></button>')
                .text(d.label || d.name)
                .attr('data-id', d.id)
                .click(function() {
                    selectDivision(d.id);
                });
            if (String(activeDivision) === String(d.id)) btn.addClass('active');
            selector.append(btn);
        });
    }

    function selectDivision(divId) {
        activeDivision = String(divId);
        activeProject = null; // Clear project when switching division
        $('#division-selector .pill-btn').removeClass('active');
        $('#division-selector').find(`[data-id="${divId}"]`).addClass('active');

        // Update status text
        if (divId === 'all') {
            $('#filter-status-desc').text('All Divisions Scope');
        } else {
            const divObj = apiData && apiData.divisions ? apiData.divisions.find(d => String(d.id) === String(divId)) : null;
            $('#filter-status-desc').text((divObj ? divObj.name : 'Division') + ' Scope');
        }

        loadDashboard(activeDivision, activeProject);
    }

    function renderProjectStrip(div) {
        const container = $('#project-strip-container');
        const inner = $('#project-strip-inner');
        inner.empty();

        if (div === 'all') {
            container.addClass('d-none');
            $('#clear-project-filter-btn').hide();
            return;
        }

        const divObj = apiData && apiData.divisions ? apiData.divisions.find(d => String(d.id) === String(div)) : null;
        const divKey = divObj ? (divObj.key || divObj.id) : div;
        $('#strip-div-name').text(divObj ? (divObj.label || divObj.name) : 'Division');

        // Get project list for this division from lists.codesByDivision
        const projects = (apiData && apiData.lists && apiData.lists.codesByDivision) 
            ? (apiData.lists.codesByDivision[div] || apiData.lists.codesByDivision[divKey] || []) 
            : [];

        if (projects.length === 0) {
            container.addClass('d-none');
            $('#clear-project-filter-btn').hide();
            return;
        }

        container.removeClass('d-none');
        $('#active-projects-count').text(projects.length + ' Projects');

        if (activeProject) {
            $('#clear-project-filter-btn').show();
        } else {
            $('#clear-project-filter-btn').hide();
        }

        // "All <Division> Projects" chip
        const allChipBtn = $('<button class="chip-btn mr-1 mb-1"></button>').click(function() {
            clearProjectFilter();
        });
        const allChip = $('<span class="chip-code"></span>').text('★ All ' + (divObj ? divObj.label : ''));
        if (!activeProject) allChip.addClass('chip-active');
        allChipBtn.append(allChip);
        inner.append(allChipBtn);

        // Project chips
        projects.forEach(p => {
            const btn = $('<button class="chip-btn mr-1 mb-1"></button>').click(function() {
                selectProject(p.id);
            });
            const chip = $('<span class="chip-code"></span>').text(p.code).attr('title', p.title);
            if (String(activeProject) === String(p.id)) {
                chip.addClass('chip-active');
            }
            btn.append(chip);
            inner.append(btn);
        });
    }

    function selectProject(projId) {
        activeProject = projId;
        const selectedProj = apiData.table.projects ? apiData.table.projects.find(p => String(p.id) === String(projId)) : null;
        if (selectedProj) {
            $('#filter-status-desc').text(`Project: ${selectedProj.code} - ${selectedProj.name}`);
        }
        loadDashboard(activeDivision, activeProject);
    }

    function clearProjectFilter() {
        activeProject = null;
        const divObj = apiData && apiData.divisions ? apiData.divisions.find(d => String(d.id) === String(activeDivision)) : null;
        $('#filter-status-desc').text((divObj ? divObj.name : 'Division') + ' Scope');
        loadDashboard(activeDivision, null);
    }

    function resetAllFilters() {
        activeDivision = 'all';
        activeProject = null;
        statusScope = 'active';
        $('.status-scope-group .btn').removeClass('active');
        $('#status-scope-active').addClass('active');
        selectDivision('all');
    }

    function setFundScope(scope) {
        fundScope = scope;
        $('.scope-btn-group .btn').removeClass('active');
        $('#scope-btn-' + scope).addClass('active');

        const labels = {
            'total': 'Total Overall',
            'rdw': 'NRDI Share',
            'csrf': 'CSRF Share'
        };
        $('#chart-scope-pill').text(labels[scope] || 'Total Overall');

        const statusBadges = {
            'total': '<i class="fas fa-layer-group text-primary mr-1"></i> Total Consolidated Project Allocations',
            'rdw': '<i class="fas fa-shield-alt text-primary mr-1"></i> Net NRDI Sanctioned Funding',
            'csrf': '<i class="fas fa-hand-holding-usd text-primary mr-1"></i> Central Service Resource Fund'
        };
        $('#scope-status-badge').html(statusBadges[scope] || '');

        updateKpis();
        renderFinanceChart();
    }

    function setTimeMode(mode) {
        timeMode = mode;
        $('#mode-btn-monthly, #mode-btn-quarterly').removeClass('active');
        if (mode === 'monthly') {
            $('#mode-btn-monthly').addClass('active');
        } else {
            $('#mode-btn-quarterly').addClass('active');
        }
        renderFinanceChart();
    }

    function setProjectFilter(status) {
        projectFilter = status;
        $('#pfilter-all, #pfilter-delayed, #pfilter-ongoing, #pfilter-completed').removeClass('active');
        $('#pfilter-' + status).addClass('active');
        renderProjectSidebar();
    }

    function updateKpis() {
        if (!apiData || !apiData.kpis) return;

        const k = apiData.kpis[fundScope] || apiData.kpis.total || apiData.kpis.rdw;
        const mtss = apiData.kpis.mtss || { share: 0 };
        const staff = apiData.kpis.staff || { total: 0, csrf: 0, division: 0 };

        // 1. Share
        if (fundScope === 'total') {
            $('#kpi-share-title').text('Total Project Allocation');
            $('#kpi-share-sub-main').text(formatMoney(k.share));
            $('#kpi-share-sub-desc').text('Total Sanctioned (All Shares)');
        } else if (fundScope === 'csrf') {
            $('#kpi-share-title').text('CSRF Share Total');
            $('#kpi-share-sub-main').text(formatMoney(k.share));
            $('#kpi-share-sub-desc').text('CSRF Allocated Share');
        } else {
            $('#kpi-share-title').text('PROJECTS NRDI SHARE');
            $('#kpi-share-sub-main').text(formatMoney(k.share));
            $('#kpi-share-sub-desc').text('Net NRDI Sanctioned Funding');
        }
        $('#kpi-share-value').text(formatMillions(k.share));

        // 2. Utilized (Exp + Commitments)
        $('#kpi-utilized-value').text(formatMillions(k.utilized));
        $('#kpi-utilized-sub-main').text(`Exp: ${formatMillions(k.expenditure)}`);
        $('#kpi-utilized-sub-desc').text(`Cmt: ${formatMillions(k.commitments)}`);

        // 3. In Process Pipeline
        $('#kpi-inproc-value').text(formatMillions(k.in_process));
        $('#kpi-inproc-sub-main').text(formatMoney(k.in_process));

        // 4. Remaining Share
        if (fundScope === 'total') {
            $('#kpi-remaining-title').text('Total Remaining Balance');
        } else if (fundScope === 'csrf') {
            $('#kpi-remaining-title').text('Remaining CSRF Share');
        } else {
            $('#kpi-remaining-title').text('Remaining NRDI Share');
        }
        $('#kpi-remaining-value').text(formatMillions(k.remaining));
        $('#kpi-remaining-sub-main').text(formatMoney(k.remaining));

        // 5. Yet to be Received by MTSS (Balance Due from MTSS)
        $('#kpi-mtss-pending-value').text(formatMillions(mtss.pending));
        $('#kpi-mtss-pending-sub-main').text(formatMoney(mtss.pending));

        // 6. Total MTSS Share (Direct MTSS Project Share)
        $('#kpi-mtss-value').text(formatMillions(mtss.share));
        $('#kpi-mtss-sub-main').text(formatMoney(mtss.share));

        // 7. Current Staff
        if (fundScope === 'csrf') {
            $('#kpi-staff-title').text('CSRF Personnel');
            $('#kpi-staff-value').text((staff.csrf || 0) + ' Staff');
            $('#kpi-staff-sub-main').text(`${staff.csrf || 0} Active Personnel`);
            $('#kpi-staff-sub-desc').text('Funded via CSRF');
        } else if (fundScope === 'rdw') {
            $('#kpi-staff-title').text('Current Staff');
            $('#kpi-staff-value').text((staff.division || 0) + ' Staff');
            $('#kpi-staff-sub-main').text(`${staff.division || 0} Active Division Staff`);
            $('#kpi-staff-sub-desc').text('Core Project Personnel');
        } else {
            $('#kpi-staff-title').text('Current Staff');
            $('#kpi-staff-value').text((staff.total || 0) + ' Staff');
            $('#kpi-staff-sub-main').text(`${staff.division || 0} Division Core`);
            $('#kpi-staff-sub-desc').text(`${staff.csrf || 0} CSRF Personnel`);
        }

        // Update Project Lifecycle Status Badges on the switcher buttons
        if (apiData.status_counts) {
            $('#status-cnt-active').text(apiData.status_counts.active ?? 0);
            $('#status-cnt-completed').text(apiData.status_counts.completed ?? 0);
            $('#status-cnt-all').text(apiData.status_counts.all ?? 0);
        }

        // Update Chart Header Badges
        $('#chart-total-budget').text(formatMillions(k.share));
        $('#chart-total-rcv').text(formatMillions(k.expenditure + k.remaining));
        $('#chart-total-exp').text(formatMillions(k.expenditure));
    }

    function renderFinanceChart() {
        if (!apiData || !apiData.charts || !apiData.charts.finance) return;
        if (chartFinance) chartFinance.destroy();

        const series = apiData.charts.finance[timeMode] || [];
        const labels = series.map(p => p.period);

        let rcvData = [];
        let expData = [];
        let budgetVal = 0;

        if (fundScope === 'csrf') {
            rcvData = series.map(p => p.csrf_rcv);
            expData = series.map(p => p.csrf_exp);
            budgetVal = series.length > 0 ? series[0].csrf_budget : 0;
        } else if (fundScope === 'total') {
            rcvData = series.map(p => p.total_rcv);
            expData = series.map(p => p.total_exp);
            budgetVal = series.length > 0 ? series[0].total_budget : 0;
        } else {
            rcvData = series.map(p => p.rdw_rcv);
            expData = series.map(p => p.rdw_exp);
            budgetVal = series.length > 0 ? series[0].rdw_budget : 0;
        }

        const totalRcvSum = rcvData.reduce((a, b) => a + b, 0);
        const totalExpSum = expData.reduce((a, b) => a + b, 0);
        $('#chart-total-budget').text(formatMillions(budgetVal));
        $('#chart-total-rcv').text(formatMillions(totalRcvSum));
        $('#chart-total-exp').text(formatMillions(totalExpSum));

        const ctx = document.getElementById('financeChart').getContext('2d');
        chartFinance = new Chart4(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Inward Cash Received',
                        data: rcvData,
                        backgroundColor: 'rgba(5, 150, 105, 0.85)',
                        borderColor: '#059669',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        type: 'bar',
                        label: 'Expenditure',
                        data: expData,
                        backgroundColor: 'rgba(220, 38, 38, 0.85)',
                        borderColor: '#dc2626',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: { size: 12.5, weight: 'bold', family: 'Inter' },
                            color: '#0f172a',
                            boxWidth: 14,
                            padding: 10
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': ' + formatMillions(ctx.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 12, weight: 'bold', family: 'Inter' }, color: '#0f172a' }
                    },
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.08)' },
                        ticks: {
                            font: { size: 12, weight: 'bold', family: 'Inter' },
                            color: '#0f172a',
                            callback: function(v) { return (v / 1000000).toFixed(0) + ' M'; }
                        }
                    }
                }
            }
        });
    }

    function renderProjectSidebar() {
        if (!apiData || !apiData.projects_by_status) return;

        const delayed = apiData.projects_by_status.delayed || [];
        const ongoing = apiData.projects_by_status.ongoing || [];
        const completed = apiData.projects_by_status.completed || [];

        $('#cnt-delayed').text(delayed.length);
        $('#cnt-ongoing').text(ongoing.length);
        $('#cnt-completed').text(completed.length);
        $('#cnt-all').text(delayed.length + ongoing.length + completed.length);

        const search = ($('#project-search').val() || '').toLowerCase().trim();
        const container = $('#project-list-container');
        container.empty();

        function filterList(list) {
            if (!search) return list;
            return list.filter(p => 
                (p.code && p.code.toLowerCase().includes(search)) ||
                (p.name && p.name.toLowerCase().includes(search)) ||
                (p.division && p.division.toLowerCase().includes(search))
            );
        }

        function createProjectCard(p, statusType) {
            const borderClass = statusType === 'delayed' ? 'proj-item-delayed' : (statusType === 'ongoing' ? 'proj-item-ongoing' : 'proj-item-completed');
            const badgeClass = statusType === 'delayed' ? 'badge-delayed' : (statusType === 'ongoing' ? 'badge-ongoing' : 'badge-completed');
            const detailUrl = "{{ url('/openprojectdetails') }}/" + p.id;

            const item = $(`
                <a href="${detailUrl}" class="proj-item ${borderClass} mb-2 shadow-sm text-decoration-none" title="Click to view details of ${p.name}">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge badge-dark px-2 py-0.5 font-weight-bold" style="font-size: 11px; background: #0f172a; letter-spacing: 0.3px;">
                            ${p.code}
                        </span>
                        <div class="d-flex align-items-center">
                            <span class="badge-status ${badgeClass} mr-1.5" style="font-size: 10.5px; font-weight: 800;">${p.status}</span>
                            <i class="fas fa-arrow-right text-muted" style="font-size: 10px; opacity: 0.7;"></i>
                        </div>
                    </div>
                    <div class="font-weight-bold text-dark text-truncate mb-1" style="font-size: 12.5px; color: #0f172a !important; font-weight: 700; line-height: 1.3;" title="${p.name}">
                        ${p.name}
                    </div>
                    <div class="d-flex justify-content-between align-items-center" style="font-size: 12px; font-weight: 700; color: #1e293b;">
                        <span><i class="fas fa-sitemap mr-1 text-primary"></i>${p.division || '—'}</span>
                        <span class="font-weight-bold" style="color: #0f172a; font-size: 12.5px;">${formatMillions(p.budget)}</span>
                    </div>
                </a>
            `);
            return item;
        }

        // Render based on projectFilter
        if (projectFilter === 'all') {
            // Group 1: Delayed
            const filteredDelayed = filterList(delayed);
            if (filteredDelayed.length > 0) {
                container.append('<div class="font-weight-bold text-danger text-uppercase mb-1.5 tracking-wider" style="font-size: 11px; font-weight: 800;"><i class="fas fa-exclamation-triangle mr-1"></i> Delayed Projects (' + filteredDelayed.length + ')</div>');
                filteredDelayed.forEach(p => container.append(createProjectCard(p, 'delayed')));
            }

            // Group 2: Ongoing
            const filteredOngoing = filterList(ongoing);
            if (filteredOngoing.length > 0) {
                container.append('<div class="font-weight-bold text-primary text-uppercase mb-1.5 mt-2.5 tracking-wider" style="font-size: 11px; font-weight: 800;"><i class="fas fa-spinner mr-1"></i> Ongoing Projects (' + filteredOngoing.length + ')</div>');
                filteredOngoing.forEach(p => container.append(createProjectCard(p, 'ongoing')));
            }

            // Group 3: Completed
            const filteredCompleted = filterList(completed);
            if (filteredCompleted.length > 0) {
                container.append('<div class="font-weight-bold text-success text-uppercase mb-1.5 mt-2.5 tracking-wider" style="font-size: 11px; font-weight: 800;"><i class="fas fa-check-circle mr-1"></i> Completed Projects (' + filteredCompleted.length + ')</div>');
                filteredCompleted.forEach(p => container.append(createProjectCard(p, 'completed')));
            }
        } else if (projectFilter === 'delayed') {
            const list = filterList(delayed);
            if (list.length === 0) container.append('<div class="text-center py-4 text-muted font-weight-bold">No delayed projects.</div>');
            list.forEach(p => container.append(createProjectCard(p, 'delayed')));
        } else if (projectFilter === 'ongoing') {
            const list = filterList(ongoing);
            if (list.length === 0) container.append('<div class="text-center py-4 text-muted font-weight-bold">No ongoing projects.</div>');
            list.forEach(p => container.append(createProjectCard(p, 'ongoing')));
        } else if (projectFilter === 'completed') {
            const list = filterList(completed);
            if (list.length === 0) container.append('<div class="text-center py-4 text-muted font-weight-bold">No completed projects.</div>');
            list.forEach(p => container.append(createProjectCard(p, 'completed')));
        }
    }

    function renderStaffHistoryChart() {
        if (!apiData || !apiData.charts || !apiData.charts.staff_history) return;
        if (chartStaffHistory) chartStaffHistory.destroy();

        const history = apiData.charts.staff_history;
        const labels = history.map(h => h.year);
        const joinedData = history.map(h => h.joined);
        const releasedData = history.map(h => h.released);
        const activeData = history.map(h => h.active);

        const ctx = document.getElementById('staffHistoryChart').getContext('2d');
        chartStaffHistory = new Chart4(ctx, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Active Staff',
                        data: activeData,
                        borderColor: '#2563eb',
                        backgroundColor: '#2563eb',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.2,
                        yAxisID: 'y'
                    },
                    {
                        type: 'bar',
                        label: 'Hired / Joined',
                        data: joinedData,
                        backgroundColor: 'rgba(16, 185, 129, 0.85)',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        type: 'bar',
                        label: 'Released / Left',
                        data: releasedData,
                        backgroundColor: 'rgba(239, 68, 68, 0.85)',
                        borderColor: '#ef4444',
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: { size: 11, weight: 'bold', family: 'Inter' },
                            color: '#0f172a',
                            boxWidth: 12,
                            padding: 8
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11.5, weight: 'bold', family: 'Inter' }, color: '#0f172a' }
                    },
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.06)' },
                        ticks: { font: { size: 11, weight: 'bold', family: 'Inter' }, color: '#0f172a', precision: 0 }
                    }
                }
            }
        });
    }

    function renderStaffDivChart() {
        if (!apiData || !apiData.charts || !apiData.charts.staff_distribution) return;
        if (chartStaffDiv) chartStaffDiv.destroy();

        const dist = apiData.charts.staff_distribution;
        const labels = dist.map(d => d.label);
        const data = dist.map(d => d.count);
        const total = data.reduce((a, b) => a + b, 0);
        $('#donut-total-staff').text(total);

        const colors = [
            '#2563eb', '#7c3aed', '#06b6d4', '#10b981', '#f59e0b', '#ec4899', '#6366f1', '#64748b'
        ];

        const ctx = document.getElementById('staffDivChart').getContext('2d');
        chartStaffDiv = new Chart4(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { size: 11, weight: 'bold', family: 'Inter' },
                            color: '#0f172a',
                            boxWidth: 12,
                            padding: 6
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.parsed;
                                const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${val} Staff (${pct}%)`;
                            }
                        }
                    }
                },
                cutout: '62%'
            }
        });
    }

    function renderProjectStatusChart() {
        if (!apiData || !apiData.charts || !apiData.charts.projects_status) return;
        if (chartProjectStatus) chartProjectStatus.destroy();

        const ps = apiData.charts.projects_status;
        const ongoing = ps.ongoing || 0;
        const delayed = ps.delayed || 0;
        const activeTotal = ongoing + delayed;
        $('#donut-total-projects').text(activeTotal);

        const ctx = document.getElementById('projectStatusChart').getContext('2d');
        chartProjectStatus = new Chart4(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Ongoing', 'Delayed'],
                datasets: [{
                    data: [ongoing, delayed],
                    backgroundColor: ['#0284c7', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { size: 12, weight: 'bold', family: 'Inter' },
                            color: '#0f172a',
                            boxWidth: 14,
                            padding: 10
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.parsed;
                                const pct = activeTotal > 0 ? ((val / activeTotal) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${val} Projects (${pct}%)`;
                            }
                        }
                    }
                },
                cutout: '62%'
            }
        });
    }

    $(document).ready(function() {
        loadDashboard('all', null);
    });
</script>
@endsection
