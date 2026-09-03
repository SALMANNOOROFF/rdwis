@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    /* Global Command Theme overrides */
    .command-dashboard {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg) !important;
        min-height: 100vh;
        color: var(--rd-text1);
        padding-top: 15px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
    }

    /* Glassmorphism & Cyber Theme Card */
    .card-cyber {
        background: var(--rd-surface);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Glow classes matching HSL tones from react */
    .card-cyan {
        box-shadow: 0 0 0 1px rgba(0, 191, 255, 0.06), 0 0 24px rgba(0, 191, 255, 0.04);
        border-left: 3px solid rgba(0, 191, 255, 0.4);
    }
    .card-cyan:hover {
        border-color: rgba(0, 191, 255, 0.25);
        box-shadow: 0 0 0 1px rgba(0, 191, 255, 0.12), 0 0 32px rgba(0, 191, 255, 0.08);
    }

    .card-amber {
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.06), 0 0 24px rgba(245, 158, 11, 0.03);
        border-left: 3px solid rgba(245, 158, 11, 0.4);
    }
    .card-amber:hover {
        border-color: rgba(245, 158, 11, 0.25);
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.12), 0 0 32px rgba(245, 158, 11, 0.08);
    }

    .card-green {
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.06), 0 0 24px rgba(34, 197, 94, 0.04);
        border-left: 3px solid rgba(34, 197, 94, 0.4);
    }
    .card-green:hover {
        border-color: rgba(34, 197, 94, 0.25);
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.12), 0 0 32px rgba(34, 197, 94, 0.08);
    }
    .card-red {
        box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.06), 0 0 24px rgba(239, 68, 68, 0.03);
        border-left: 3px solid rgba(239, 68, 68, 0.4);
    }
    .card-red:hover {
        border-color: rgba(239, 68, 68, 0.25);
        box-shadow: 0 0 0 1px rgba(239, 68, 68, 0.12), 0 0 32px rgba(239, 68, 68, 0.08);
    }
    .card-purple {
        box-shadow: 0 0 0 1px rgba(95, 120, 88, 0.08), 0 0 24px rgba(95, 120, 88, 0.04);
        border-left: 3px solid var(--rd-accent, #5F7858);
    }
    .card-purple:hover {
        border-color: var(--rd-accent, #5F7858);
        box-shadow: 0 0 0 1px var(--rd-accent, #5F7858), 0 0 32px rgba(95, 120, 88, 0.12);
    }
    .card-blue {
        box-shadow: 0 0 0 1px rgba(2, 132, 199, 0.08), 0 0 24px rgba(2, 132, 199, 0.04);
        border-left: 3px solid #0284c7;
    }
    .card-blue:hover {
        border-color: #0284c7;
        box-shadow: 0 0 0 1px #0284c7, 0 0 32px rgba(2, 132, 199, 0.12);
    }

    /* Pill Navigation */
    .pill-nav {
        background: var(--rd-neutral-200) !important;
        border: 1px solid var(--rd-border) !important;
        border-radius: 30px;
        padding: 5px 8px;
        display: inline-flex;
        flex-wrap: wrap;
        gap: 4px;
    }

    .pill-btn {
        background: var(--rd-surface) !important;
        border: 1px solid var(--rd-border2) !important;
        color: var(--rd-text2) !important;
        padding: 5px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pill-btn:hover:not(.active) {
        background: var(--rd-neutral-200) !important;
        color: var(--rd-text1) !important;
    }

    .pill-btn.active {
        border-color: var(--rd-primary-600) !important;
        color: var(--rd-primary-700) !important;
        background: var(--rd-primary-100) !important;
        box-shadow: var(--rd-shadow-sm);
        font-weight: 700;
    }

    /* Chips */
    .chip-code {
        padding: 3px 10px;
        font-size: 11px;
        border-radius: 20px;
        font-weight: 600;
        letter-spacing: 0.5px;
        background: var(--rd-surface) !important;
        border: 1px solid var(--rd-border2) !important;
        color: var(--rd-text2) !important;
        transition: all 0.2s;
    }

    .chip-active {
        border-color: var(--rd-primary-600) !important;
        background: var(--rd-primary-100) !important;
        color: var(--rd-primary-700) !important;
        box-shadow: var(--rd-shadow-sm);
    }

    .chip-btn {
        opacity: 0.85;
        transition: opacity 0.2s, transform 0.2s;
        background: transparent;
        border: none;
        padding: 0;
    }
    .chip-btn:hover {
        opacity: 1;
        transform: scale(1.03);
    }

    /* Badges */
    .badge-status {
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .badge-completed {
        background: var(--rd-success-soft) !important;
        color: var(--rd-success-dark) !important;
        border-color: var(--rd-success-100) !important;
    }
    .badge-ongoing {
        background: var(--rd-info-soft) !important;
        color: var(--rd-info-dark) !important;
        border-color: var(--rd-info-100) !important;
    }
    .badge-delayed {
        background: var(--rd-danger-soft) !important;
        color: var(--rd-danger-dark) !important;
        border-color: var(--rd-danger-100) !important;
    }

    /* Sidebar List */
    .sidebar-list {
        max-height: 650px;
        overflow-y: auto;
    }
    .sidebar-list::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar-list::-webkit-scrollbar-thumb {
        background: var(--rd-border2);
        border-radius: 4px;
    }

    .list-item {
        background: var(--rd-surface) !important;
        border: 1px solid var(--rd-border) !important;
        border-radius: 10px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: all 0.2s ease;
        text-decoration: none !important;
        color: var(--rd-text1) !important;
    }
    .list-item:hover {
        background: var(--rd-neutral-50) !important;
        border-color: var(--rd-border2) !important;
    }

    /* Loader */
    .dashboard-loader {
        position: absolute;
        top: 24px;
        right: 24px;
        font-size: 12px;
        color: var(--rd-primary-600);
        font-weight: 600;
        letter-spacing: 0.5px;
        display: none;
    }

    /* Standard Card Values */
    .kpi-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--rd-text3) !important;
        font-weight: 700;
    }
    .kpi-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--rd-text1) !important;
        margin-top: 6px;
        font-family: 'Rajdhani', sans-serif;
    }
    .kpi-sub {
        font-size: 11px;
        color: var(--rd-text4) !important;
        margin-top: 4px;
        font-weight: 500;
    }
</style>

<div class="content-wrapper command-dashboard px-4">
    <div class="position-relative">
        <h2 class="font-weight-bold text-dark rajdhani mb-4">
            <i class="fas fa-satellite mr-2 text-info"></i>Command Operations Center
        </h2>
        <div class="dashboard-loader" id="loader">
            <i class="fas fa-spinner fa-spin mr-1"></i> LIVE TRACKING ACTIVE...
        </div>
    </div>

    <!-- Error Banner -->
    <div class="alert alert-danger d-none mb-4" id="dashboard-error-banner" style="border-radius: 12px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; font-size: 13px;">
        <i class="fas fa-exclamation-triangle mr-2"></i> <span id="dashboard-error-message">Failed to load data.</span>
    </div>

    <!-- Division Filter Bar -->
    <div class="mb-4">
        <div class="pill-nav shadow-sm w-100" id="division-selector">
            <!-- Dynamically Populated -->
        </div>
    </div>

    <!-- Project Filter Bar (Conditional) -->
    <div class="mb-4 d-none" id="project-strip-container">
        <div class="card-cyber px-4 py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-xs font-weight-bold text-muted text-uppercase tracking-wider">Active Division Projects</span>
                <span class="badge badge-secondary" id="active-projects-count">0 Active</span>
            </div>
            <div class="d-flex flex-wrap gap-2 overflow-auto py-1" id="project-strip-inner" style="max-height: 100px;">
                <!-- Dynamically Populated -->
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="row mb-4">
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card-cyber card-purple px-3 py-3 h-100">
                <div class="kpi-title d-flex justify-content-between align-items-center">
                    <span>Pending Actions</span>
                    <i class="fas fa-clock" style="font-size: 13px; color: var(--rd-accent, #5F7858);"></i>
                </div>
                <div class="kpi-value" id="kpi-pending-cases" style="color: var(--rd-accent, #5F7858);">0</div>
                <div class="kpi-sub" id="kpi-pending-cases-sub">0 Purchase • 0 Contract</div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card-cyber card-blue px-3 py-3 h-100">
                <div class="kpi-title d-flex justify-content-between align-items-center">
                    <span>Active HR / Staff</span>
                    <i class="fas fa-users" style="font-size: 13px; color: #0284c7;"></i>
                </div>
                <div class="kpi-value" id="kpi-employees" style="color: #0284c7;">0</div>
                <div class="kpi-sub" id="kpi-employees-sub">Active personnel</div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card-cyber card-cyan px-3 py-3 h-100">
                <div class="kpi-title">Approved Budget</div>
                <div class="kpi-value" id="kpi-budget">Rs 0</div>
                <div class="kpi-sub">Total allocated funds</div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card-cyber card-amber px-3 py-3 h-100">
                <div class="kpi-title">Total Utilized</div>
                <div class="kpi-value" id="kpi-utilized">Rs 0</div>
                <div class="kpi-sub">Evaluated expenses</div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card-cyber card-green px-3 py-3 h-100">
                <div class="kpi-title">Remaining Balance</div>
                <div class="kpi-value" id="kpi-remaining">Rs 0</div>
                <div class="kpi-sub">Active buffer</div>
            </div>
        </div>
        <div class="col-xl-2 col-lg-4 col-md-6 mb-3">
            <div class="card-cyber card-red px-3 py-3 h-100">
                <div class="kpi-title">Procurement Credit</div>
                <div class="kpi-value" id="kpi-credit">Rs 0</div>
                <div class="kpi-sub">Loan-eligible cases</div>
            </div>
        </div>
    </div>

    <!-- Chart Blocks & Right Sidebar Grid -->
    <div class="row">
        <!-- Chart Block Columns (Left 9 columns) -->
        <div class="col-xl-9 col-lg-8" id="charts-main-area">
            <!-- 1. Main Budget Timeline Card -->
            <div class="card-cyber px-4 py-3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="font-weight-bold text-dark text-sm">Budget vs Utilization Trends</span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info active btn-xs px-3" id="mode-btn-monthly" onclick="setMode('monthly')">Monthly</button>
                        <button class="btn btn-outline-info btn-xs px-3" id="mode-btn-quarterly" onclick="setMode('quarterly')">Quarterly</button>
                    </div>
                </div>
                <div style="height: 280px; position: relative;">
                    <canvas id="financeChart"></canvas>
                </div>
            </div>

            <!-- 2. Tri-Chart Row (Project Status, Employees, Top Projects Employees) -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card-cyber px-4 py-3 h-100">
                        <span class="font-weight-bold text-dark text-xs mb-3 d-block">Project Status Lifecycle</span>
                        <div style="height: 180px; position: relative;">
                            <canvas id="projectStatusChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card-cyber px-4 py-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="font-weight-bold text-dark text-xs">Personnel Alignments</span>
                            <span class="text-xs text-muted" id="hr-kpi-sub">0 total</span>
                        </div>
                        <div style="height: 180px; position: relative;">
                            <canvas id="employeesDonutChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card-cyber px-4 py-3 h-100">
                        <span class="font-weight-bold text-dark text-xs mb-3 d-block">Project Staff Headcounts (Top)</span>
                        <div style="height: 180px; position: relative;">
                            <canvas id="employeesPerProjectChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Dual-Chart Row (Purchase Cases, Project Progress, Employees per Division) -->
            <div class="row mb-4" id="middle-chart-row">
                <div class="col-md-6 mb-3">
                    <div class="card-cyber px-4 py-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-weight-bold text-dark text-xs">Purchase Scrutiny (Divisional)</span>
                            <span class="text-xs text-muted" id="purchase-kpi-sub">0 cases</span>
                        </div>
                        <div class="row">
                            <div class="col-sm-5 mb-2" style="height: 140px; position: relative;">
                                <canvas id="purchaseDonutChart"></canvas>
                            </div>
                            <div class="col-sm-7" style="height: 140px; position: relative;">
                                <canvas id="purchasePerDivChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card-cyber px-4 py-3 h-100">
                        <span class="font-weight-bold text-dark text-xs mb-3 d-block">Project Completion Progress (Top)</span>
                        <div style="height: 160px; position: relative;">
                            <canvas id="projectProgressChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Dynamic Multi-Comparison Row (Shown when "All" division is selected) -->
            <div class="row mb-4 d-none" id="comparisons-row">
                <div class="col-md-4 mb-3">
                    <div class="card-cyber px-4 py-3 h-100">
                        <span class="font-weight-bold text-dark text-xs mb-3 d-block">Divisions Budget Comparison</span>
                        <div style="height: 240px; position: relative;">
                            <canvas id="budgetVsUtilPerDivChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card-cyber px-4 py-3 h-100">
                        <span class="font-weight-bold text-dark text-xs mb-3 d-block">Divisions Personnel Load</span>
                        <div style="height: 240px; position: relative;">
                            <canvas id="employeesPerDivChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card-cyber px-4 py-3 h-100">
                        <span class="font-weight-bold text-dark text-xs mb-3 d-block">Divisions Active Projects</span>
                        <div style="height: 240px; position: relative;">
                            <canvas id="projectsPerDivChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Project Sidebar List (Right 3 columns) -->
        <div class="col-xl-3 col-lg-4 mb-4">
            <div class="card-cyber px-3 py-3 h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="font-weight-bold text-dark text-sm">Projects Log</span>
                    <div class="position-relative">
                        <input type="text" id="project-search" placeholder="Search..." 
                               class="form-control form-control-sm bg-white text-dark border-secondary" 
                               style="width: 110px; border-radius: 12px; font-size: 11px;">
                    </div>
                </div>
                <div class="sidebar-list flex-fill d-flex flex-column gap-2" id="project-list-container">
                    <!-- Dynamically Populated -->
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
<!-- Explicitly include local Chart.js v4 umd library inside scripts section to run after jQuery loads -->
<script src="{{ asset('plugins/chartjs4/chart.umd.js') }}"></script>
<script>
    // Save the v4 instance to window.Chart4
    window.Chart4 = window.Chart;
    // Restore v2 as window.Chart so other parts of layout (like AdminLTE) don't crash
    window.Chart = oldChart;

    // State Store matching React model
    let activeDivision = 'all';
    let activeProject = null;
    let timeMode = 'monthly'; // monthly | quarterly
    let apiData = null;
    
    // Chart instances tracking to safely destroy and re-create without context leaks
    let chartFinance = null;
    let chartProjectStatus = null;
    let chartEmployeesDonut = null;
    let chartEmployeesPerProject = null;
    let chartPurchaseDonut = null;
    let chartPurchasePerDiv = null;
    let chartProjectProgress = null;
    
    // Comparative charts instances
    let chartBudgetVsUtilPerDiv = null;
    let chartEmployeesPerDiv = null;
    let chartProjectsPerDiv = null;

    function formatMoney(val) {
        const n = Number(val || 0);
        return 'Rs ' + n.toLocaleString(undefined, { maximumFractionDigits: 0 });
    }

    function formatNumber(val) {
        return Number(val || 0).toLocaleString();
    }

    // Dynamic Server Load & Re-drawing
    function loadDashboard(div = activeDivision, projId = activeProject) {
        $('#loader').show();
        
        const qs = new URLSearchParams();
        qs.set('division', String(div));
        if (projId) qs.set('project_id', String(projId));
        
        $.ajax({
            url: '/nrdi/dashboard-data?' + qs.toString(),
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                apiData = response;
                
                if (!apiData || !apiData.kpis) {
                    $('#dashboard-error-message').text("Invalid API response format.");
                    $('#dashboard-error-banner').removeClass('d-none');
                    return;
                }
                $('#dashboard-error-banner').addClass('d-none');
                
                // Update KPI Cards
                const pendingPur = Number(apiData.kpis.pendingPurchaseCases || 0);
                const pendingCtr = Number(apiData.kpis.pendingContractCases || 0);
                const totalPending = Number(apiData.kpis.pendingCases ?? (pendingPur + pendingCtr));
                
                $('#kpi-pending-cases').text(totalPending);
                $('#kpi-pending-cases-sub').text(`${pendingPur} Purchase • ${pendingCtr} Contract`);

                const empsTotal = Number(apiData.kpis.employeesTotal || 0);
                const empsHired = Number(apiData.kpis.employeesHired || 0);
                $('#kpi-employees').text(empsTotal);
                $('#kpi-employees-sub').text(`${empsTotal} active staff • ${empsHired} hired (1y)`);

                $('#kpi-budget').text(formatMoney(apiData.kpis.budgetReceived));
                $('#kpi-utilized').text(formatMoney(apiData.kpis.utilized));
                $('#kpi-remaining').text(formatMoney(apiData.kpis.remaining));
                $('#kpi-credit').text(formatMoney(apiData.kpis.creditTaken));
                
                // Render Division Selectors (only on first load or if selector is empty)
                if ($('#division-selector').children().length === 0) {
                    renderDivisionSelector(apiData.divisions);
                }
                
                // Render Project filters
                renderProjectFilters(div);
                
                // Render Charts & Sidebar List
                renderAllCharts();
                renderProjectSidebar();
            },
            error: function(err) {
                console.error("Dashboard payload loading failure", err);
                $('#dashboard-error-message').html(`<strong>AJAX Error:</strong> Failed to fetch dashboard data (${err.status} - ${err.statusText}).`);
                $('#dashboard-error-banner').removeClass('d-none');
            },
            complete: function() {
                $('#loader').hide();
            }
        });
    }

    function renderDivisionSelector(divisions) {
        const selector = $('#division-selector');
        selector.empty();
        
        divisions.forEach(d => {
            const btn = $('<button class="pill-btn"></button>')
                .text(d.label)
                .attr('data-id', d.id)
                .click(function() {
                    $('.pill-btn').removeClass('active');
                    $(this).addClass('active');
                    activeDivision = String(d.id);
                    activeProject = null; // Clear project constraint
                    loadDashboard(activeDivision, activeProject);
                });
            if (String(activeDivision) === String(d.id)) btn.addClass('active');
            selector.append(btn);
        });
        
        // "All" button
        const allBtn = $('<button class="pill-btn ml-auto"></button>')
            .text('All')
            .click(function() {
                $('.pill-btn').removeClass('active');
                $(this).addClass('active');
                activeDivision = 'all';
                activeProject = null;
                loadDashboard(activeDivision, activeProject);
            });
        if (activeDivision === 'all') allBtn.addClass('active');
        selector.append(allBtn);
    }

    function renderProjectFilters(div) {
        const container = $('#project-strip-container');
        const inner = $('#project-strip-inner');
        inner.empty();
        
        if (div === 'all') {
            container.addClass('d-none');
            return;
        }
        
        const selectedDivisionObj = apiData.divisions.find(d => String(d.id) === String(div));
        const divisionKey = selectedDivisionObj ? selectedDivisionObj.key : null;
        
        if (!divisionKey) {
            container.addClass('d-none');
            return;
        }
        
        const projectCodes = apiData.lists.codesByDivision[divisionKey] || [];
        
        if (projectCodes.length === 0) {
            container.addClass('d-none');
            return;
        }
        
        container.removeClass('d-none');
        $('#active-projects-count').text(projectCodes.length + ' Active');
        
        projectCodes.forEach(p => {
            let statusClass = 'ongoing';
            if (p.status.toLowerCase().includes('complete') || p.status.toLowerCase() === 'closed') statusClass = 'completed';
            else if (p.status.toLowerCase().includes('delay')) statusClass = 'delayed';
            
            const btn = $('<button class="chip-btn" type="button"></button>')
                .click(function() {
                    activeProject = p.id;
                    loadDashboard(activeDivision, activeProject);
                });
                
            const chip = $('<span class="chip-code"></span>')
                .text(p.code)
                .addClass('badge-' + statusClass);
                
            if (String(activeProject) === String(p.id)) {
                chip.addClass('chip-active');
            }
            
            btn.append(chip);
            inner.append(btn);
        });
        
        // Clear filter button
        if (activeProject) {
            const clearBtn = $('<button class="btn btn-xs btn-outline-secondary px-3" style="border-radius: 20px; font-size:11px;">Clear Filter</button>')
                .click(function() {
                    activeProject = null;
                    loadDashboard(activeDivision, activeProject);
                });
            inner.append(clearBtn);
        }
    }

    function renderProjectSidebar() {
        const list = $('#project-list-container');
        list.empty();
        
        const projects = apiData.table.projects || [];
        
        projects.forEach(p => {
            let statusClass = 'ongoing';
            if (p.status.toLowerCase().includes('complete') || p.status.toLowerCase() === 'closed') statusClass = 'completed';
            else if (p.status.toLowerCase().includes('delay')) statusClass = 'delayed';
            
            const link = $('<a class="list-item shadow-xs mb-1"></a>')
                .attr('href', `/nrdi/projects/${p.id}`)
                .attr('title', `${p.code} — ${p.statusRaw || ''}`);
                
            const left = $('<div class="min-w-0 d-flex flex-column"></div>');
            left.append($('<div class="d-flex align-items-center mb-1"><span class="badge px-2 py-0.5 font-weight-bold text-white shadow-xs mr-1" style="background-color: #0284c7; border-radius: 4px; font-size: 11px;">' + p.code + '</span></div>'));
            left.append($('<div class="text-muted truncate font-weight-500" style="font-size: 11px;"></div>').text(p.division));
            
            const right = $('<div class="d-flex align-items-center gap-1.5 flex-shrink-0"></div>');
            right.append($('<span class="badge badge-pill badge-light border text-dark font-weight-bold px-2 py-0.5 mr-1" style="font-size: 10.5px;" title="' + p.employees + ' Active Employees Assigned"><i class="fas fa-users text-primary mr-1"></i>' + p.employees + '</span>'));
            
            const badge = $('<span class="badge-status"></span>')
                .text(p.status)
                .addClass('badge-' + statusClass);
            right.append(badge);
            
            link.append(left);
            link.append(right);
            list.append(link);
        });
        
        if (projects.length === 0) {
            list.append($('<div class="text-center text-muted text-xs py-5">No projects found.</div>'));
        }
    }

    function setMode(mode) {
        timeMode = mode;
        $('.btn-outline-info').removeClass('active');
        if (mode === 'monthly') $('#mode-btn-monthly').addClass('active');
        else $('#mode-btn-quarterly').addClass('active');
        
        // Re-draw Finance Trend Line with new mode
        renderFinanceTrendChart();
    }

    // Chart.js Generators with Robust Try-Catches
    function renderAllCharts() {
        try { renderFinanceTrendChart(); } catch(e) { console.error("Trend chart render failed", e); }
        try { renderProjectStatusChart(); } catch(e) { console.error("Project status chart render failed", e); }
        try { renderEmployeesDonutChart(); } catch(e) { console.error("Personnel alignments donut render failed", e); }
        try { renderEmployeesPerProjectChart(); } catch(e) { console.error("Employees per project chart render failed", e); }
        try { renderPurchaseCharts(); } catch(e) { console.error("Purchase scrutiny charts render failed", e); }
        try { renderProjectProgressChart(); } catch(e) { console.error("Completion progress chart render failed", e); }
        try { renderDivisionalComparisons(); } catch(e) { console.error("Divisional comparative charts render failed", e); }
    }

    function renderFinanceTrendChart() {
        if (chartFinance) chartFinance.destroy();
        
        const series = apiData.charts.finance[timeMode] || [];
        const labels = series.map(p => p.period);
        const budgets = series.map(p => p.budget);
        const utilized = series.map(p => p.utilized);
        
        const ctx = document.getElementById('financeChart').getContext('2d');
        chartFinance = new Chart4(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Budget Received',
                        data: budgets,
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.08)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#0284c7',
                        fill: true
                    },
                    {
                        label: 'Utilized',
                        data: utilized,
                        backgroundColor: 'rgba(16, 185, 129, 0.30)',
                        borderColor: '#10b981',
                        borderWidth: 1.5,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#334155', font: { size: 11, weight: 'bold' } } },
                },
                scales: {
                    x: {
                        ticks: { color: '#64748b', font: { size: 10, weight: '600' } },
                        grid: { color: 'rgba(100, 116, 139, 0.08)' }
                    },
                    y: {
                        ticks: { color: '#64748b', font: { size: 10, weight: '600' } },
                        grid: { color: 'rgba(100, 116, 139, 0.08)' }
                    }
                }
            }
        });
    }

    function renderProjectStatusChart() {
        if (chartProjectStatus) chartProjectStatus.destroy();
        
        const stat = apiData.charts.projectStatus;
        const ctx = document.getElementById('projectStatusChart').getContext('2d');
        
        chartProjectStatus = new Chart4(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Ongoing', 'Delayed'],
                datasets: [{
                    data: [stat.completed, stat.ongoing, stat.delayed],
                    backgroundColor: ['#10b981', '#0284c7', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#334155', font: { size: 10.5, weight: '600' } } }
                }
            }
        });
    }

    function renderEmployeesDonutChart() {
        if (chartEmployeesDonut) chartEmployeesDonut.destroy();
        
        const hired = Number(apiData.kpis.employeesHired || 0);
        const total = Number(apiData.kpis.employeesTotal || 0);
        const existing = Math.max(0, total - hired);
        
        $('#hr-kpi-sub').text(`${total} Active Personnel • ${hired} Hired (Past Year)`);
        
        const ctx = document.getElementById('employeesDonutChart').getContext('2d');
        chartEmployeesDonut = new Chart4(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Hired (Past Year)', 'Retained Staff'],
                datasets: [{
                    data: [hired, existing],
                    backgroundColor: ['#0284c7', '#6366f1'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: '#334155', font: { size: 10.5, weight: '600' } } }
                }
            }
        });
    }

    function renderEmployeesPerProjectChart() {
        if (chartEmployeesPerProject) chartEmployeesPerProject.destroy();
        
        const list = apiData.charts.employeesPerProject || [];
        const labels = list.map(item => item.code);
        const data = list.map(item => item.count);
        
        const ctx = document.getElementById('employeesPerProjectChart').getContext('2d');
        chartEmployeesPerProject = new Chart4(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Employees',
                    data: data,
                    backgroundColor: 'rgba(2, 132, 199, 0.85)',
                    borderColor: '#0284c7',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { display: false } },
                    y: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { color: 'rgba(100, 116, 139, 0.08)' } }
                }
            }
        });
    }

    function renderPurchaseCharts() {
        if (chartPurchaseDonut) chartPurchaseDonut.destroy();
        if (chartPurchasePerDiv) chartPurchasePerDiv.destroy();
        
        const reviewed = Number(apiData.kpis.reviewedCases || 0);
        const pending = Number(apiData.kpis.pendingCases || 0);
        
        $('#purchase-kpi-sub').text(`${reviewed} reviewed • ${pending} pending`);
        
        // Donut Chart
        const ctxDonut = document.getElementById('purchaseDonutChart').getContext('2d');
        chartPurchaseDonut = new Chart4(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Reviewed', 'Pending'],
                datasets: [{
                    data: [reviewed, pending],
                    backgroundColor: ['#10b981', '#f59e0b'],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
        
        // Divisional bar Chart
        const list = apiData.charts.casesPerDivision || [];
        const labels = list.map(item => item.division);
        const count = list.map(item => item.count);
        
        const ctxBar = document.getElementById('purchasePerDivChart').getContext('2d');
        chartPurchasePerDiv = new Chart4(ctxBar, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Cases',
                    data: count,
                    backgroundColor: 'rgba(2, 132, 199, 0.75)',
                    borderColor: '#0284c7',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { display: false } },
                    y: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { color: 'rgba(100, 116, 139, 0.08)' } }
                }
            }
        });
    }

    function renderProjectProgressChart() {
        if (chartProjectProgress) chartProjectProgress.destroy();
        
        const list = apiData.charts.projectProgress || [];
        const labels = list.map(item => item.code);
        const percent = list.map(item => item.percent);
        
        const ctx = document.getElementById('projectProgressChart').getContext('2d');
        chartProjectProgress = new Chart4(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Completion %',
                    data: percent,
                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { display: false } },
                    y: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' }, max: 100, beginAtZero: true }, grid: { color: 'rgba(100, 116, 139, 0.08)' } }
                }
            }
        });
    }

    function renderDivisionalComparisons() {
        if (chartBudgetVsUtilPerDiv) chartBudgetVsUtilPerDiv.destroy();
        if (chartEmployeesPerDiv) chartEmployeesPerDiv.destroy();
        if (chartProjectsPerDiv) chartProjectsPerDiv.destroy();
        
        const isAllSelected = activeDivision === 'all' && !activeProject;
        
        if (!isAllSelected) {
            $('#comparisons-row').addClass('d-none');
            $('#charts-main-area').removeClass('col-xl-12').addClass('col-xl-9 col-lg-8');
            $('#middle-chart-row .col-md-6').removeClass('col-md-4').addClass('col-md-6');
            return;
        }
        
        // If All is selected, we expand the graphs and show the Divisional comparisons!
        $('#comparisons-row').removeClass('d-none');
        
        // 1. Budget Comparison Chart
        const budgetUtil = apiData.charts.budgetVsUtilPerDivision || [];
        const divLabels = budgetUtil.map(item => item.division);
        const budgets = budgetUtil.map(item => item.budget);
        const utilized = budgetUtil.map(item => item.utilized);
        
        const ctxBudget = document.getElementById('budgetVsUtilPerDivChart').getContext('2d');
        chartBudgetVsUtilPerDiv = new Chart4(ctxBudget, {
            type: 'bar',
            data: {
                labels: divLabels,
                datasets: [
                    {
                        label: 'Budget',
                        data: budgets,
                        backgroundColor: 'rgba(2, 132, 199, 0.75)',
                        borderColor: '#0284c7',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Utilized',
                        data: utilized,
                        backgroundColor: 'rgba(16, 185, 129, 0.75)',
                        borderColor: '#10b981',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#334155', font: { size: 9.5, weight: '600' } } } },
                scales: {
                    x: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { display: false } },
                    y: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { color: 'rgba(100, 116, 139, 0.08)' } }
                }
            }
        });
        
        // 2. Divisional Personnel load
        const empDiv = apiData.charts.employeesPerDivision || [];
        const empLabels = empDiv.map(item => item.division);
        const empCounts = empDiv.map(item => item.count);
        
        const ctxEmp = document.getElementById('employeesPerDivChart').getContext('2d');
        chartEmployeesPerDiv = new Chart4(ctxEmp, {
            type: 'bar',
            data: {
                labels: empLabels,
                datasets: [{
                    label: 'Employees',
                    data: empCounts,
                    backgroundColor: 'rgba(99, 102, 241, 0.80)',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { display: false } },
                    y: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { color: 'rgba(100, 116, 139, 0.08)' } }
                }
            }
        });
        
        // 3. Divisional Active Projects count
        const prjDiv = apiData.charts.projectsPerDivision || [];
        const prjLabels = prjDiv.map(item => item.division);
        const prjCounts = prjDiv.map(item => item.count);
        
        const ctxPrj = document.getElementById('projectsPerDivChart').getContext('2d');
        chartProjectsPerDiv = new Chart4(ctxPrj, {
            type: 'bar',
            data: {
                labels: prjLabels,
                datasets: [{
                    label: 'Projects',
                    data: prjCounts,
                    backgroundColor: 'rgba(245, 158, 11, 0.80)',
                    borderColor: '#f59e0b',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { display: false } },
                    y: { ticks: { color: '#64748b', font: { size: 9.5, weight: '600' } }, grid: { color: 'rgba(100, 116, 139, 0.08)' } }
                }
            }
        });
    }

    // Bootstrap load
    $(document).ready(function() {
        loadDashboard();
        
        // Real-time Sidebar Projects Search filter
        $('#project-search').on('keyup', function() {
            const query = $(this).val().toLowerCase();
            $('.sidebar-list .list-item').each(function() {
                const text = $(this).text().toLowerCase();
                if (text.indexOf(query) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>
@endsection
