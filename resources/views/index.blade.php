@extends('welcome')

@section('content')
@php
    $u = Auth::user();
    $area = strtolower(trim((string) ($u?->acc_untarea ?? '')));
    $title = $area === 'nrdi'
        ? 'NRDI Command Dashboard'
        : ((method_exists($u, 'isSORD') && $u->isSORD()) ? 'SORD Dashboard' : 'Division Dashboard');
@endphp

<!-- DataTables CSS -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">

<style>
/* Custom Clickable Row Styling */
.clickable-row {
    cursor: pointer;
    transition: background-color 0.2s;
}
.clickable-row:hover {
    background-color: rgba(0, 0, 0, 0.05) !important;
}

/* Skeleton Loader (Light Mode Compatible) */
.skeleton {
    background: #e2e8f0;
    background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
    border-radius: 8px;
    background-size: 200% 100%;
    animation: 1.5s shine linear infinite;
}
.sk-text { height: 16px; margin-bottom: 10px; width: 60%; }
.sk-metric { height: 40px; margin-bottom: 15px; width: 40%; }
.sk-chart { height: 300px; width: 100%; border-radius: 12px; }

@keyframes shine {
    to { background-position-x: -200%; }
}

.fade-in { animation: fadeIn 0.5s ease-in; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.badge-hr {
    font-size: 0.9rem;
    padding: 0.4rem 0.6rem;
    margin: 2px;
    cursor: pointer;
    border: 1px solid #17a2b8;
    background: #e0f7fa;
    color: #00838f;
    transition: 0.2s;
}
.badge-hr:hover {
    background: #17a2b8;
    color: #fff;
}
.clickable-card {
    cursor: pointer;
    transition: 0.2s;
}
.clickable-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>

<div class="content-wrapper pt-3">
    
    <div class="content-header px-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h1 class="m-0 text-dark">{{ $title }}</h1>
            <span id="syncBadge" class="badge badge-light px-3 py-2 border shadow-sm text-muted">
                <i class="fas fa-sync fa-spin mr-1"></i> Syncing...
            </span>
        </div>
    </div>

    <!-- Skeletons (Shown initially) -->
    <section class="content px-4" id="skeletonLayout">
        <div class="row mb-4">
            <div class="col-lg-3 col-6"><div class="skeleton" style="height: 120px;"></div></div>
            <div class="col-lg-3 col-6"><div class="skeleton" style="height: 120px;"></div></div>
            <div class="col-lg-3 col-6"><div class="skeleton" style="height: 120px;"></div></div>
            <div class="col-lg-3 col-6"><div class="skeleton" style="height: 120px;"></div></div>
        </div>
        <div class="row mb-4">
            <div class="col-lg-4"><div class="skeleton" style="height: 350px;"></div></div>
            <div class="col-lg-8"><div class="skeleton" style="height: 350px;"></div></div>
        </div>
    </section>

    <!-- Main Content (Hidden initially) -->
    <section class="content px-4 d-none fade-in" id="mainLayout">
        
        <!-- Top Financial Metrics Row (AdminLTE Small Boxes) -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="val-total-projects">0</h3>
                        <p>Total Projects</p>
                    </div>
                    <div class="icon"><i class="fas fa-layer-group"></i></div>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="val-total-amount">0</h3>
                        <p>Total Approved Budget</p>
                    </div>
                    <div class="icon"><i class="fas fa-money-check-alt"></i></div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="val-total-spent">0</h3>
                        <p>Total Amount Spent</p>
                    </div>
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="val-total-remaining" class="text-white">0</h3>
                        <p class="text-white">Remaining Budget</p>
                    </div>
                    <div class="icon"><i class="fas fa-wallet"></i></div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <!-- Doughnut Chart (Projects By Stages) -->
            <div class="col-lg-4">
                <div class="card card-outline card-primary shadow-sm h-100">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">Projects By Stages</h3>
                    </div>
                    <div class="card-body">
                        <div style="height: 220px; position: relative;">
                            <canvas id="stageChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bar Chart (Financials) -->
            <div class="col-lg-8">
                <div class="card card-outline card-info shadow-sm h-100">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold">Project Financials</h3>
                        <div class="card-tools">
                            <span class="mr-3" style="font-size: 0.9rem;"><i class="fas fa-square text-primary"></i> Approved</span>
                            <span style="font-size: 0.9rem;"><i class="fas fa-square text-secondary"></i> Utilized</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div style="height: 220px;">
                            <canvas id="finChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HR & Purchase Cases Row -->
        <div class="row mt-3 d-flex align-items-stretch">
            <div class="col-lg-6 d-flex">
                <div class="card shadow-sm w-100">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">HR Resource Mapping</h3>
                        <div class="card-tools">
                            <span class="badge badge-info">Total: <span id="hr-total">0</span></span>
                            <a href="{{ route('divhr.employelist') }}" class="btn btn-tool" title="View All Employees"><i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-wrap align-items-start align-content-start" id="hr-badges-container" style="gap: 10px;">
                        <!-- HR Badges Injected via JS -->
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 d-flex">
                <div class="card shadow-sm w-100 clickable-card" onclick="window.location.href='{{ route('viewpurchasecase') }}'">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">Purchase Cases Breakdown</h3>
                        <div class="card-tools">
                            <span class="badge badge-dark">Total: <span id="pc-total">0</span></span>
                            <button type="button" class="btn btn-tool"><i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-wrap align-items-start align-content-start" id="pc-breakdown-container" style="gap: 10px;">
                        <!-- PC Breakdowns Injected via JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Data Table Row -->
        <div class="row mt-2 mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex align-items-center py-2">
                        <h3 class="card-title font-weight-bold m-0">Project Portfolio Details</h3>
                        <div class="ml-auto" style="width: 250px;">
                            <div class="input-group input-group-sm">
                                <input type="text" id="customSearchBox" class="form-control" placeholder="Search project or code...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table id="projectsTable" class="table table-hover table-bordered w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%">S.No.</th>
                                    <th>Project Name</th>
                                    <th width="15%">Created On</th>
                                    <th width="15%">Milestones (Done/Total)</th>
                                    <th width="10%">Status</th>
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

    </section>
</div>

@push('scripts')
<script src="{{ asset('plugins/chart.js/Chart.min.js') }}"></script>
<!-- DataTables JS -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    
    fetch('{{ route("dashboard.data.div") }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('skeletonLayout').classList.add('d-none');
        document.getElementById('mainLayout').classList.remove('d-none');
        
        const syncBadge = document.getElementById('syncBadge');
        syncBadge.innerHTML = '<i class="fas fa-check-circle text-success mr-1"></i> Live';
        syncBadge.classList.replace('text-muted', 'text-success');
        syncBadge.classList.replace('badge-light', 'badge-white');
        
        const formatMoney = (val) => {
            if(val >= 1000000000) return (val/1000000000).toFixed(2) + 'B';
            if(val >= 1000000) return (val/1000000).toFixed(2) + 'M';
            return new Intl.NumberFormat('en-US').format(val);
        };

        // 1. Populate Financial Top Metrics
        document.getElementById("val-total-projects").innerText = data.totalProjects;
        document.getElementById("val-total-amount").innerText = 'PKR ' + formatMoney(data.finSummary.total);
        document.getElementById("val-total-spent").innerText = 'PKR ' + formatMoney(data.finSummary.spent);
        document.getElementById("val-total-remaining").innerText = 'PKR ' + formatMoney(data.finSummary.remaining);

        // 2. HR Resource Badges
        const hrContainer = document.getElementById("hr-badges-container");
        hrContainer.innerHTML = '';
        let hrTotal = 0;
        data.projectsList.forEach(p => {
            if(p.team_count > 0) {
                hrTotal += parseInt(p.team_count, 10);
                // Using user's requested HR route /divhr/employelist with project filter
                hrContainer.innerHTML += `<span style="font-size: 0.95rem; cursor: pointer;" class="badge badge-info p-2 shadow-sm" onclick="window.location.href='/divhr/employelist?head_code=${p.head_code}'"><i class="fas fa-users mr-1"></i> ${p.head_code} : <b class="ml-1">${p.team_count}</b></span>`;
            }
        });
        document.getElementById("hr-total").innerText = hrTotal;
        if(hrContainer.innerHTML === '') hrContainer.innerHTML = '<span class="text-muted small">No staff mapped</span>';

        // 3. Purchase Cases Breakdown
        const pcContainer = document.getElementById("pc-breakdown-container");
        document.getElementById("pc-total").innerText = data.purchaseStats.total;
        
        pcContainer.innerHTML = '';
        const statusColors = {
            'Draft': 'badge-secondary',
            'In Progress': 'badge-primary',
            'Under Scrutiny': 'badge-warning',
            'Fulfilled': 'badge-success',
            'Completed': 'badge-success',
            'Rejected': 'badge-danger'
        };

        if(Object.keys(data.purchaseStats.breakdown).length === 0) {
            pcContainer.innerHTML = '<span class="text-muted small">No purchase cases found.</span>';
        } else {
            for (const [status, count] of Object.entries(data.purchaseStats.breakdown)) {
                let colorClass = statusColors[status] || 'badge-info';
                pcContainer.innerHTML += `<span style="font-size: 0.95rem;" class="badge ${colorClass} p-2 shadow-sm">${status}: <b class="ml-1">${count}</b></span>`;
            }
        }

        // 4. Data Table Initialization
        const tbody = document.querySelector("#projectsTable tbody");
        tbody.innerHTML = '';
        data.projectsList.forEach((p, idx) => {
            let statusBadge = `<span class="badge badge-secondary">${p.status}</span>`;
            if(p.status === 'Approved') statusBadge = `<span class="badge badge-warning">Approved</span>`;
            if(p.status === 'Completed') statusBadge = `<span class="badge badge-success">Completed</span>`;

            // Clicking row routes to openprojectdetails/{id}
            tbody.innerHTML += `
                <tr class="clickable-row" onclick="window.location.href='/openprojectdetails/${p.prj_id}'">
                    <td>${idx+1}</td>
                    <td class="font-weight-bold text-primary">${p.title} <span class="text-muted small ml-1">(${p.head_code})</span></td>
                    <td>${p.created_on}</td>
                    <td class="text-center font-weight-bold text-dark">${p.milestones}</td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });

        // Initialize DataTable with Max 5 Rows Pagination
        const table = $('#projectsTable').DataTable({
            "dom": "rtip", // r = processing, t = table, i = info, p = pagination (removes 'f' filter)
            "pageLength": 5,
            "lengthChange": false, 
            "ordering": true,
            "info": true,
            "autoWidth": false
        });

        // Bind custom search box
        $('#customSearchBox').on('keyup', function() {
            table.search(this.value).draw();
        });

        // 5. Draw Stage Chart (Doughnut)
        renderStageChart(data.projectsByStage);

        // 6. Draw Financial Chart (Bar)
        renderFinancialChart(data.financials);

    })
    .catch(error => {
        console.error(error);
        document.getElementById('syncBadge').innerHTML = '<i class="fas fa-exclamation-triangle text-danger mr-1"></i> Error loading dashboard';
    });

    function renderStageChart(stages) {
        const ctx = document.getElementById('stageChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'In Process', 'Completed'],
                datasets: [{
                    data: [stages.open, stages.in_process, stages.completed],
                    backgroundColor: ['#f39c12', '#007bff', '#28a745'],
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutoutPercentage: 70,
                legend: { position: 'right' }
            }
        });
    }

    function renderFinancialChart(finData) {
        const ctx = document.getElementById('finChart').getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: finData.map(f => f.head_code),
                datasets: [
                    {
                        label: 'Approved Budget',
                        backgroundColor: '#007bff',
                        data: finData.map(f => f.approved)
                    },
                    {
                        label: 'Utilized Amount',
                        backgroundColor: '#6c757d',
                        data: finData.map(f => Math.abs(f.spent))
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { display: false },
                scales: {
                    xAxes: [{ gridLines: { display: false } }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                if(value >= 1000000) return (value/1000000).toFixed(1) + 'M';
                                if(value >= 1000) return (value/1000).toFixed(1) + 'k';
                                return value;
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return data.datasets[tooltipItem.datasetIndex].label + ': PKR ' + Number(tooltipItem.yLabel).toLocaleString();
                        }
                    }
                }
            }
        });
    }

});
</script>
@endpush
@endsection
