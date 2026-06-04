@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .finance-hub {
        font-family: 'Inter', sans-serif;
        background: #080b0f !important;
        min-height: 100vh;
        color: #cbd5e0;
        padding-top: 15px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    /* Premium Cyber/Glass Panels */
    .card-cyber {
        background: rgba(18, 26, 34, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Glow highlights */
    .card-cyan {
        box-shadow: 0 0 0 1px rgba(0, 191, 255, 0.06), 0 0 24px rgba(0, 191, 255, 0.04);
        border-left: 3px solid rgba(0, 191, 255, 0.4);
    }
    .card-cyan:hover {
        border-color: rgba(0, 191, 255, 0.25);
        box-shadow: 0 0 0 1px rgba(0, 191, 255, 0.12), 0 0 32px rgba(0, 191, 255, 0.08);
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

    .card-amber {
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.06), 0 0 24px rgba(245, 158, 11, 0.03);
        border-left: 3px solid rgba(245, 158, 11, 0.4);
    }
    .card-amber:hover {
        border-color: rgba(245, 158, 11, 0.25);
        box-shadow: 0 0 0 1px rgba(245, 158, 11, 0.12), 0 0 32px rgba(245, 158, 11, 0.08);
    }

    .kpi-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: rgba(229, 229, 229, 0.6);
        font-weight: 600;
    }
    .kpi-value {
        font-size: 24px;
        font-weight: 700;
        color: #fff;
        margin-top: 6px;
        font-family: 'Rajdhani', sans-serif;
    }
    .kpi-sub {
        font-size: 11px;
        color: rgba(229, 229, 229, 0.45);
        margin-top: 4px;
    }

    /* Pill Navigation */
    .pill-nav {
        background: rgba(18, 26, 34, 0.85);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 30px;
        padding: 6px 12px;
        display: inline-flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .pill-btn {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: rgba(229, 229, 229, 0.7);
        padding: 5px 16px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.25s ease;
    }

    .pill-btn:hover:not(.active) {
        color: #fff;
        border-color: rgba(0, 191, 255, 0.3);
    }

    .pill-btn.active {
        border-color: rgba(0, 191, 255, 0.7);
        color: #00BFFF;
        background: #060a0e;
        box-shadow: 0 0 12px rgba(0, 191, 255, 0.15);
        font-weight: 600;
    }

    /* Chips */
    .chip-code {
        padding: 3px 10px;
        font-size: 11px;
        border-radius: 20px;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: all 0.2s;
    }

    .chip-active {
        border-color: rgba(0, 191, 255, 0.4);
        background: rgba(0, 191, 255, 0.1);
        color: #00BFFF;
        box-shadow: 0 0 10px rgba(0, 191, 255, 0.1);
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

    /* Loader */
    .dashboard-loader {
        position: absolute;
        top: 24px;
        right: 24px;
        font-size: 12px;
        color: #00BFFF;
        font-weight: 500;
        letter-spacing: 0.5px;
        display: none;
    }

    /* Dark Table design */
    .table-cyber {
        background: transparent;
        color: #cbd5e0;
    }
    .table-cyber th {
        background: rgba(18, 26, 34, 0.95) !important;
        border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
        color: #67e8f9 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 12px;
        font-weight: bold;
        padding: 12px 16px !important;
    }
    .table-cyber td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        padding: 12px 16px !important;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-cyber tr:hover {
        background: rgba(255, 255, 255, 0.02) !important;
    }

    .btn-toggle-active {
        border-color: rgba(0, 191, 255, 0.7) !important;
        color: #00BFFF !important;
        background: #060a0e !important;
        box-shadow: 0 0 10px rgba(0, 191, 255, 0.1) !important;
        font-weight: 600 !important;
    }
</style>

<div class="content-wrapper finance-hub px-4">
    <!-- Header -->
    <div class="position-relative">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <h2 class="font-weight-bold text-white rajdhani m-0">
                <i class="fas fa-wallet mr-2 text-info"></i>Finance Operations Dashboard
            </h2>
            
            <!-- Toggle buttons -->
            <div class="btn-group btn-group-sm shadow-sm" role="group">
                <a href="{{ route('fin.dashboard', ['mode' => 'm']) }}" 
                   class="btn btn-outline-info px-3 {{ $mode === 'm' ? 'btn-toggle-active' : '' }}"
                   style="font-size: 11px; border-radius: 20px 0 0 20px;">
                    <i class="fas fa-globe mr-1"></i> All Data
                </a>
                <a href="{{ route('fin.dashboard', ['mode' => 's']) }}" 
                   class="btn btn-outline-info px-3 {{ $mode === 's' ? 'btn-toggle-active' : '' }}"
                   style="font-size: 11px; border-radius: 0 20px 20px 0;">
                    <i class="fas fa-sitemap mr-1"></i> My Dept
                </a>
            </div>
        </div>
        <div class="dashboard-loader" id="loader">
            <i class="fas fa-spinner fa-spin mr-1"></i> UPDATING METRICS...
        </div>
    </div>

    <!-- Unified Glassmorphic Filters Panel -->
    <div class="card-cyber px-4 py-3 mb-4" id="div-filter-container">
        <div class="row align-items-center">
            <div class="col-md-5 mb-3 mb-md-0">
                <label class="kpi-title d-block mb-2" style="font-size: 11px;">
                    <i class="fas fa-sitemap mr-1 text-cyan"></i> Filter by Division
                </label>
                <select id="division-dropdown" class="form-control form-control-sm bg-dark text-white border-secondary" style="border-radius: 8px; height: 38px; font-size: 13px; border: 1px solid rgba(255,255,255,0.08);">
                    <option value="all">All Divisions</option>
                </select>
            </div>
            <div class="col-md-5 mb-3 mb-md-0">
                <label class="kpi-title d-block mb-2" style="font-size: 11px;">
                    <i class="fas fa-project-diagram mr-1 text-cyan"></i> Filter by Project
                </label>
                <select id="project-dropdown" class="form-control form-control-sm bg-dark text-white border-secondary" style="border-radius: 8px; height: 38px; font-size: 13px; border: 1px solid rgba(255,255,255,0.08);">
                    <option value="">All Projects</option>
                </select>
            </div>
            <div class="col-md-2 text-md-right pt-md-4">
                <button id="btn-reset-filters" class="btn btn-sm btn-outline-info w-100" style="border-radius: 20px; height: 38px; font-size: 12px; font-weight: 600; border-color: rgba(0, 191, 255, 0.4); color: #00BFFF; background: transparent; transition: all 0.2s;">
                    <i class="fas fa-undo mr-1"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- KPIs Metric grid -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card-cyber card-cyan px-4 py-3 h-100">
                <div class="kpi-title">Total Active Cases</div>
                <div class="kpi-value" id="kpi-cases">0</div>
                <div class="kpi-sub" id="kpi-cases-sub">0 currently active cases</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card-cyber card-green px-4 py-3 h-100">
                <div class="kpi-title">Approved Projects Budget</div>
                <div class="kpi-value" id="kpi-budget">Rs 0</div>
                <div class="kpi-sub" id="kpi-budget-sub">Across 0 active projects</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card-cyber card-red px-4 py-3 h-100">
                <div class="kpi-title">Case Expenditures</div>
                <div class="kpi-value" id="kpi-utilized">Rs 0</div>
                <div class="kpi-sub">Evaluated spent buffer</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card-cyber card-amber px-4 py-3 h-100">
                <div class="kpi-title">Personnel Mapping</div>
                <div class="kpi-value" id="kpi-personnel">0</div>
                <div class="kpi-sub">Included active staff</div>
            </div>
        </div>
    </div>

    <!-- Live Finance Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card-cyber px-4 py-3 h-100">
                <span class="font-weight-bold text-white text-xs mb-3 d-block">Budget Allocation & Usage</span>
                <div style="height: 250px; position: relative;">
                    <canvas id="financeDonutChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card-cyber px-4 py-3 h-100">
                <span class="font-weight-bold text-white text-xs mb-3 d-block">Project Wise Approved Budgets</span>
                <div style="height: 250px; position: relative;">
                    <canvas id="projectsBudgetBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Table mapping list -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-cyber px-3 py-3">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <span class="font-weight-bold text-white text-sm">
                        <i class="fas fa-users-cog mr-2 text-cyan"></i>Personnel Headcount Mappings
                        <span class="badge badge-info ml-2" id="personnel-filtered-badge">Active</span>
                    </span>
                    <span class="text-muted text-xs" id="personnel-loaded-count">0 active records loaded</span>
                </div>
                <div class="rd-table-responsive" style="max-height: 400px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.05); border-radius: 8px;">
                    <table class="table table-hover table-cyber text-nowrap m-0" id="personnel-table">
                        <thead class="sticky-top">
                            <tr>
                                <th>Personnel Name</th>
                                <th>Designated Title</th>
                                <th>Allocated Head</th>
                                <th>Assigned Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employeesList as $emp)
                            <tr class="emp-row" data-unit="{{ $emp->unit_name }}" data-head="{{ $emp->hed_name }}">
                                <td class="font-weight-bold text-white">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-tie text-muted mr-3" style="font-size: 1.2rem; color: #67e8f9 !important;"></i>
                                        {{ $emp->emp_name }}
                                    </div>
                                </td>
                                <td>{{ $emp->emp_joinrank ?: 'N/A' }}</td>
                                <td class="text-muted">{{ $emp->hed_name }}</td>
                                <td><span class="badge border px-2.5 py-1 text-white border-secondary emp-unit-badge" style="background: rgba(18,26,34,0.6);">{{ $emp->unit_name }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open mb-2 text-cyan" style="font-size: 2rem;"></i><br>
                                    No mapped employees found under active dynamic bounds.
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
    let apiData = null;
    let financeMode = "{{ $mode }}"; // 'm' or 's'
    
    // Chart instances tracking to safely destroy and re-create without context leaks
    let chartFinanceDonut = null;
    let chartProjectsBudgetBar = null;

    function formatMoney(val) {
        const n = Number(val || 0);
        return 'Rs ' + n.toLocaleString(undefined, { maximumFractionDigits: 0 });
    }

    // Dynamic Server Load & Re-drawing
    function loadFinanceDashboard(div = activeDivision, projId = activeProject) {
        $('#loader').show();
        
        const qs = new URLSearchParams();
        qs.set('division', String(div));
        qs.set('mode', financeMode); // Pass current view mode to server
        if (projId) qs.set('project_id', String(projId));
        
        $.ajax({
            url: '/nrdi/dashboard-data?' + qs.toString(),
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                apiData = response;
                
                // Update KPI Cards
                const pending = Number(apiData.kpis.pendingCases || 0);
                const reviewed = Number(apiData.kpis.reviewedCases || 0);
                const totalCases = pending + reviewed;
                
                $('#kpi-cases').text(totalCases);
                $('#kpi-cases-sub').text(`${pending} pending • ${reviewed} reviewed`);
                
                $('#kpi-budget').text(formatMoney(apiData.kpis.budgetReceived));
                $('#kpi-budget-sub').text(`Across ${apiData.kpis.projectsTotal} active projects`);
                
                $('#kpi-utilized').text(formatMoney(apiData.kpis.utilized));
                $('#kpi-personnel').text(apiData.kpis.employeesTotal);
                
                // Populate Division Dropdown (only once)
                if ($('#division-dropdown option').length <= 1) {
                    populateDivisionDropdown(apiData.divisions);
                }
                
                // Populate Project Dropdown
                populateProjectDropdown();
                
                // Set selections in dropdowns
                $('#division-dropdown').val(activeDivision);
                $('#project-dropdown').val(activeProject || '');
                
                // Render Charts
                renderAllCharts();
                
                // Filter the Personnel mappings list client-side
                filterPersonnelTable();
            },
            error: function(err) {
                console.error("Finance Dashboard load fail", err);
            },
            complete: function() {
                $('#loader').hide();
            }
        });
    }

    function populateDivisionDropdown(divisions) {
        const dropdown = $('#division-dropdown');
        dropdown.find('option:not(:first)').remove(); // Keep "All Divisions"
        
        divisions.forEach(d => {
            dropdown.append(
                $('<option></option>').val(d.id).text(d.name || d.label)
            );
        });
    }

    function populateProjectDropdown() {
        const dropdown = $('#project-dropdown');
        dropdown.empty();
        dropdown.append($('<option value="">All Projects</option>'));
        
        const projects = apiData.table.projects || [];
        
        projects.forEach(p => {
            dropdown.append(
                $('<option></option>').val(p.id).text(`${p.code} — ${p.name}`)
            );
        });
        
        dropdown.val(activeProject || '');
    }

    function filterPersonnelTable() {
        let countVisible = 0;
        
        let targetUnit = null;
        if (activeDivision !== 'all') {
            const selectedDivisionObj = apiData.divisions.find(d => String(d.id) === String(activeDivision));
            targetUnit = selectedDivisionObj ? selectedDivisionObj.key : null;
        }
        
        let targetProjectObj = null;
        if (activeProject) {
            targetProjectObj = apiData.table.projects.find(p => String(p.id) === String(activeProject));
        }
        
        $('#personnel-table tbody tr.emp-row').each(function() {
            const row = $(this);
            const rowUnit = row.attr('data-unit');
            const rowHead = row.attr('data-head');
            
            let show = true;
            
            // 1. Filter by Division namesh key
            if (targetUnit) {
                if (rowUnit !== targetUnit) {
                    show = false;
                }
            }
            
            // 2. Filter by Project code
            if (targetProjectObj && show) {
                // If row's allocated head code is different from selected project code
                if (!rowHead.toLowerCase().includes(targetProjectObj.code.toLowerCase())) {
                    show = false;
                }
            }
            
            if (show) {
                row.show();
                countVisible++;
            } else {
                row.hide();
            }
        });
        
        $('#personnel-loaded-count').text(`${countVisible} active records loaded`);
        if (targetUnit || targetProjectObj) {
            let label = "Filtered";
            if (targetUnit) label += ` • ${targetUnit}`;
            if (targetProjectObj) label += ` • ${targetProjectObj.code}`;
            $('#personnel-filtered-badge').text(label).removeClass('badge-info').addClass('badge-warning');
        } else {
            $('#personnel-filtered-badge').text("Active").removeClass('badge-warning').addClass('badge-info');
        }
    }

    // Chart.js Generators
    function renderAllCharts() {
        renderFinanceDonutChart();
        renderProjectsBudgetBarChart();
    }

    function renderFinanceDonutChart() {
        if (chartFinanceDonut) chartFinanceDonut.destroy();
        
        const totalBudget = Number(apiData.kpis.budgetReceived || 0);
        const totalSpent = Number(apiData.kpis.utilized || 0);
        const remaining = Math.max(0, totalBudget - totalSpent);
        
        const ctxDonut = document.getElementById('financeDonutChart').getContext('2d');
        chartFinanceDonut = new Chart4(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: ['Spent Balance', 'Remaining Balance'],
                datasets: [{
                    data: [totalSpent, remaining],
                    backgroundColor: ['rgba(239, 68, 68, 0.70)', 'rgba(34, 197, 94, 0.75)'],
                    borderColor: 'rgba(18,26,34,0.85)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: 'rgba(229,229,229,0.8)', font: { size: 11 } } }
                }
            }
        });
    }

    function renderProjectsBudgetBarChart() {
        if (chartProjectsBudgetBar) chartProjectsBudgetBar.destroy();
        
        const projects = apiData.table.projects || [];
        
        // Sort and take top 10
        const sorted = projects.slice().sort((a, b) => b.budget - a.budget);
        const topProjects = sorted.slice(0, 10);
        
        const labels = topProjects.map(p => p.code);
        const budgets = topProjects.map(p => p.budget);
        
        const ctxBar = document.getElementById('projectsBudgetBarChart').getContext('2d');
        chartProjectsBudgetBar = new Chart4(ctxBar, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Approved Budget (Rs)',
                    data: budgets,
                    backgroundColor: 'rgba(0, 191, 255, 0.16)',
                    borderColor: 'rgba(0, 191, 255, 0.55)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: 'rgba(229,229,229,0.5)', font: { size: 9 } }, grid: { display: false } },
                    y: { ticks: { color: 'rgba(229,229,229,0.5)', font: { size: 9 } }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });
    }

    $(document).ready(function() {
        // If mode is 's' (Section), hide Division selectors because division filters only apply in All Data mode
        if (financeMode === 's') {
            $('#div-filter-container').addClass('d-none');
        }
        
        // Division dropdown change event
        $('#division-dropdown').change(function() {
            activeDivision = $(this).val();
            activeProject = null; // Clear project selection
            loadFinanceDashboard(activeDivision, activeProject);
        });
        
        // Project dropdown change event
        $('#project-dropdown').change(function() {
            activeProject = $(this).val() || null;
            loadFinanceDashboard(activeDivision, activeProject);
        });
        
        // Reset filters button
        $('#btn-reset-filters').click(function() {
            activeDivision = 'all';
            activeProject = null;
            $('#division-dropdown').val('all');
            $('#project-dropdown').val('');
            loadFinanceDashboard(activeDivision, activeProject);
        });
        
        loadFinanceDashboard();
    });
</script>
@endsection
