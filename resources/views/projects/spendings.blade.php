@extends('welcome')

@section('content')
<div class="content-wrapper pt-3">
    <style>
        .finance-card {
            border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            transition: transform 0.2s; overflow: hidden; position: relative;
        }
        .finance-card:hover { transform: translateY(-3px); }
        .fc-icon { position: absolute; right: 15px; top: 15px; font-size: 3rem; opacity: 0.05; }
        
        /* Toggle Switch Style */
        .toggle-container { display: flex; background: var(--rd-surface2); border-radius: 25px; padding: 4px; width: fit-content; margin: 0 auto; }
        .toggle-btn { padding: 8px 25px; border-radius: 25px; border: none; background: transparent; font-weight: 600; color: var(--rd-text3); cursor: pointer; transition: all 0.3s; }
        .toggle-btn.active { background: var(--rd-accent); color: white; box-shadow: 0 2px 5px rgba(0,123,255,0.3); }

        .table-custom thead th { background: var(--rd-surface2); color: var(--rd-text2); border-bottom: 2px solid var(--rd-border); }
    </style>

    <div class="container-fluid">
        
        {{-- Header & Back Button --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-coins text-warning mr-2"></i> Financial Overview
    </h4>
            </div>
            <a href="{{ route('projects.show', $project->prj_id) }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Details
            </a>
        </div>

        {{-- TOP CARDS: Summary --}}
        <div class="row">
            {{-- 1. Total Allocated --}}
            <div class="col-md-4">
                <div class="card finance-card bg-white border-left-primary h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Allocated Budget</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">Rs. {{ number_format($totalBudget) }}</div>
                        <i class="fas fa-wallet fc-icon text-primary"></i>
                    </div>
                </div>
            </div>

            {{-- 2. Total Spent --}}
            <div class="col-md-4">
                <div class="card finance-card bg-white border-left-danger h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Spent (Actuals)</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">Rs. {{ number_format($totalSpent) }}</div>
                        <div class="progress mt-2" style="height: 5px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $percentageSpent }}%"></div>
                        </div>
                        <small class="text-muted">{{ $percentageSpent }}% Utilized</small>
                        <i class="fas fa-hand-holding-usd fc-icon text-danger"></i>
                    </div>
                </div>
            </div>

            {{-- 3. Remaining Balance --}}
            <div class="col-md-4">
                <div class="card finance-card bg-white border-left-success h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Remaining Balance</div>
                        <div class="h3 mb-0 font-weight-bold text-gray-800">Rs. {{ number_format($balance) }}</div>
                        <i class="fas fa-piggy-bank fc-icon text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOGGLE SECTION --}}
        <div class="row mt-4">
            <div class="col-12 text-center mb-3">
                <div class="toggle-container p-1">
                    <button class="toggle-btn active" onclick="showView('chart')" id="btn-chart">
                        <i class="fas fa-chart-pie mr-1"></i> Visual Graph
                    </button>
                    <button class="toggle-btn" onclick="showView('table')" id="btn-table">
                        <i class="fas fa-table mr-1"></i> Data Table
                    </button>
                </div>
            </div>
        </div>

        {{-- MAIN CONTENT AREA --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                
                {{-- VIEW 1: GRAPH (Chart.js) --}}
                <div id="view-chart">
                    <h6 class="font-weight-bold text-primary mb-3">Budget Allocation by Head</h6>
                    <div style="height: 400px;">
                        <canvas id="spendingChart"></canvas>
                    </div>
                </div>

                {{-- VIEW 2: DATA TABLE --}}
                <div id="view-table" style="display: none;">
                    <h6 class="font-weight-bold text-primary mb-3">Detailed Transaction History</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-custom table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Head ID</th>
                                    <th>Allocated Amount</th>
                                    <th>Spent</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($chartLabels as $index => $label)
                                <tr>
                                    <td>HEAD-{{ $label }}</td>
                                    <td class="text-right">Rs. {{ number_format($chartData[$index]) }}</td>
                                    <td class="text-right">Rs. 0 (Pending Integration)</td> {{-- Placeholder until deep link established --}}
                                    <td class="text-center"><span class="badge badge-info">Allocated</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No financial data found for this project.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- SCRIPTS FOR TOGGLE & CHART --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Toggle Logic
    function showView(viewName) {
        if(viewName === 'chart') {
            document.getElementById('view-chart').style.display = 'block';
            document.getElementById('view-table').style.display = 'none';
            document.getElementById('btn-chart').classList.add('active');
            document.getElementById('btn-table').classList.remove('active');
        } else {
            document.getElementById('view-chart').style.display = 'none';
            document.getElementById('view-table').style.display = 'block';
            document.getElementById('btn-chart').classList.remove('active');
            document.getElementById('btn-table').classList.add('active');
        }
    }

    // 2. Chart Logic (Dynamic Data from Controller)
    const ctx = document.getElementById('spendingChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar', // Can be 'pie', 'doughnut', 'bar'
        data: {
            labels: @json($chartLabels), // Head IDs
            datasets: [{
                label: 'Allocated Budget (Rs.)',
                data: @json($chartData), // Costs
                backgroundColor: [
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    ticks: { color: 'var(--rd-text3)' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: 'var(--rd-text3)' }
                }
            },
            plugins: {
                legend: {
                    labels: { color: 'var(--rd-text2)' }
                }
            }
        }
    });
</script>
@endsection