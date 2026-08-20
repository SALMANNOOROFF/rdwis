@extends('welcome')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap');

.proc-dashboard {
    font-family: 'Inter', sans-serif;
    background: #080b0f !important;
    min-height: 100vh;
    color: #cbd5e0;
    padding-top: 20px;
    padding-bottom: 50px;
}

.rajdhani {
    font-family: 'Rajdhani', sans-serif;
    letter-spacing: 0.5px;
}

/* Glassmorphism & Cyber Cards */
.card-cyber {
    background: rgba(18, 26, 34, 0.85);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.card-cyber:hover {
    border-color: rgba(0, 191, 255, 0.3);
    box-shadow: 0 10px 36px rgba(0, 191, 255, 0.1);
}

.kpi-title {
    font-family: 'Rajdhani', sans-serif;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(229, 229, 229, 0.6);
    font-weight: 700;
}

.kpi-value {
    font-family: 'Rajdhani', sans-serif;
    font-size: 28px;
    font-weight: 700;
    line-height: 1.1;
    margin-top: 4px;
}

.kpi-sub {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 4px;
}

/* Header & Quick Action Buttons */
.btn-cyber-primary {
    background: rgba(0, 191, 255, 0.12);
    border: 1px solid rgba(0, 191, 255, 0.4);
    color: #00BFFF;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: 0.5px;
    border-radius: 20px;
    height: 36px;
    padding: 0 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.btn-cyber-primary:hover {
    background: rgba(0, 191, 255, 0.25);
    color: #fff;
    box-shadow: 0 0 15px rgba(0, 191, 255, 0.3);
    transform: translateY(-1px);
}

.btn-cyber-secondary {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: #cbd5e0;
    font-family: 'Rajdhani', sans-serif;
    font-weight: 600;
    font-size: 12px;
    border-radius: 20px;
    height: 36px;
    padding: 0 16px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.btn-cyber-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}

.chart-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chart-title {
    font-family: 'Rajdhani', sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0;
}
</style>

<div class="content-wrapper proc-dashboard px-4">
    {{-- 1. Directorate Command Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge px-3 py-1 rajdhani" style="background: rgba(0, 191, 255, 0.15); color: #00BFFF; border: 1px solid rgba(0, 191, 255, 0.3); font-size: 10px;">
                    <i class="fas fa-shield-alt mr-1"></i> DIRECTORATE OF PROCUREMENT (DPROC)
                </span>
                <span class="badge px-2 py-1 rajdhani" style="background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); font-size: 10px;">
                    <i class="fas fa-chart-pie mr-1"></i> EXECUTIVE ANALYTICS & KPIS
                </span>
            </div>
            <h2 class="font-weight-bold text-white rajdhani m-0" style="font-size: 2rem;">
                <i class="fas fa-tachometer-alt mr-2 text-cyan"></i>Procurement Command Dashboard
            </h2>
            <p class="text-muted m-0 small">Executive analytics, procurement pipeline volume, division expenditure, and supplier intelligence.</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap" style="gap: 10px;">
            <a href="{{ route('nrdi.purchase_cases_new.procurement.index') }}" class="btn-cyber-primary">
                <i class="fas fa-tasks mr-1"></i> Action Scrutiny Hub
            </a>
            <a href="{{ route('nrdi.firms.index') }}" class="btn-cyber-secondary">
                <i class="fas fa-building text-cyan mr-1"></i> Suppliers Directory
            </a>
            <a href="{{ route('inventory.assets.index') }}" class="btn-cyber-secondary">
                <i class="fas fa-boxes text-success mr-1"></i> Inventory & Assets
            </a>
            <a href="{{ route('nrdi.procurement.reports.index') }}" class="btn-cyber-secondary">
                <i class="fas fa-chart-line text-warning mr-1"></i> Reports Center
            </a>
        </div>
    </div>

    {{-- 2. 5 Key KPI Metric Cards (Pure Numbers & Totals) --}}
    <div class="row mb-4">
        {{-- Card 1: Awaiting Scrutiny --}}
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card-cyber p-3 h-100" style="border-left: 4px solid #f59e0b;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="kpi-title text-warning">1. Awaiting Scrutiny & Vetting</span>
                    <i class="fas fa-exclamation-circle text-warning opacity-75"></i>
                </div>
                <div class="kpi-value text-white">{{ $pendingCount }} <span class="small text-muted" style="font-size: 13px;">cases</span></div>
                <div class="kpi-sub">
                    <span class="text-warning font-weight-bold rajdhani" style="font-size: 14px;">PKR {{ number_format($pendingVolume) }}</span>
                </div>
            </div>
        </div>

        {{-- Card 2: In-Pipeline at HQ --}}
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card-cyber p-3 h-100" style="border-left: 4px solid #00BFFF;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="kpi-title text-info">2. In-Pipeline at HQ</span>
                    <i class="fas fa-stream text-info opacity-75"></i>
                </div>
                <div class="kpi-value text-white">{{ $openCount }} <span class="small text-muted" style="font-size: 13px;">cases</span></div>
                <div class="kpi-sub">
                    <span class="text-cyan font-weight-bold rajdhani" style="font-size: 14px;">PKR {{ number_format($openVolume) }}</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Approved Cases --}}
        <div class="col-xl col-md-4 col-sm-6 mb-3">
            <div class="card-cyber p-3 h-100" style="border-left: 4px solid #22c55e;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="kpi-title text-success">3. Approved Cases</span>
                    <i class="fas fa-check-double text-success opacity-75"></i>
                </div>
                <div class="kpi-value text-white">{{ $closedCount }} <span class="small text-muted" style="font-size: 13px;">cases</span></div>
                <div class="kpi-sub">
                    <span class="text-success font-weight-bold rajdhani" style="font-size: 14px;">PKR {{ number_format($closedVolume) }}</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Total Volume --}}
        <div class="col-xl col-md-6 col-sm-6 mb-3">
            <div class="card-cyber p-3 h-100" style="border-left: 4px solid #a855f7;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="kpi-title" style="color: #c084fc;">Total Procurement Volume</span>
                    <i class="fas fa-coins opacity-75" style="color: #c084fc;"></i>
                </div>
                <div class="kpi-value rajdhani" style="color: #c084fc; font-size: 22px;">PKR {{ number_format($totalVolume) }}</div>
                <div class="kpi-sub">
                    <span class="text-muted">{{ $totalCases }} total procurement cases</span>
                </div>
            </div>
        </div>

        {{-- Card 5: Supplier Directory --}}
        <div class="col-xl col-md-6 col-sm-6 mb-3">
            <div class="card-cyber p-3 h-100" style="border-left: 4px solid #38bdf8;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="kpi-title text-cyan">4. Supplier Directory</span>
                    <i class="fas fa-building text-cyan opacity-75"></i>
                </div>
                <div class="kpi-value text-white">{{ $firmsCount }} <span class="small text-muted" style="font-size: 13px;">registered</span></div>
                <div class="kpi-sub">
                    <span class="text-muted">Approved vendor database</span>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Visual Analytics & Charts Section (Replacing Tabbed Tables) --}}
    <div class="row">
        {{-- Chart 1: Division-wise Procurement Portfolio --}}
        <div class="col-lg-8 mb-4">
            <div class="card-cyber h-100">
                <div class="chart-header">
                    <h4 class="chart-title">
                        <i class="fas fa-chart-bar text-cyan mr-2"></i>Procurement Portfolio by Division (PKR)
                    </h4>
                    <span class="badge badge-dark text-muted font-weight-normal small">All Active Units</span>
                </div>
                <div class="p-4" style="height: 340px; position: relative;">
                    <canvas id="divisionVolumeChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Chart 2: Workflow Status Distribution --}}
        <div class="col-lg-4 mb-4">
            <div class="card-cyber h-100">
                <div class="chart-header">
                    <h4 class="chart-title">
                        <i class="fas fa-chart-pie text-warning mr-2"></i>Case Workflow Distribution
                    </h4>
                    <span class="badge badge-dark text-muted font-weight-normal small">{{ $totalCases }} Cases</span>
                </div>
                <div class="p-4 d-flex align-items-center justify-content-center" style="height: 340px; position: relative;">
                    <canvas id="statusDistChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Chart 3: Monthly Spending & Activity Trend --}}
        <div class="col-lg-7 mb-4">
            <div class="card-cyber h-100">
                <div class="chart-header">
                    <h4 class="chart-title">
                        <i class="fas fa-chart-line text-success mr-2"></i>Monthly Procurement Activity & Volume
                    </h4>
                    <span class="badge badge-dark text-muted font-weight-normal small">Historical Timeline</span>
                </div>
                <div class="p-4" style="height: 320px; position: relative;">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Chart 4: Top Vendor Commitments --}}
        <div class="col-lg-5 mb-4">
            <div class="card-cyber h-100">
                <div class="chart-header">
                    <h4 class="chart-title">
                        <i class="fas fa-award text-info mr-2"></i>Top Suppliers by Approved Volume
                    </h4>
                    <a href="{{ route('nrdi.firms.index') }}" class="btn btn-sm btn-link text-info p-0 rajdhani font-weight-bold">View All Suppliers &rarr;</a>
                </div>
                <div class="p-3">
                    <div class="table-responsive">
                        <table class="table table-borderless text-white mb-0" style="font-size: 13px;">
                            <thead>
                                <tr class="text-muted text-uppercase rajdhani" style="font-size: 11px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                                    <th>Supplier Name</th>
                                    <th class="text-center">Cases</th>
                                    <th class="text-right">Total Awarded</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSuppliers as $sup)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                    <td class="font-weight-bold">
                                        <i class="fas fa-building text-cyan mr-2"></i>{{ \Illuminate\Support\Str::limit($sup->frm_name, 25) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $sup->cases_count }}</span>
                                    </td>
                                    <td class="text-right rajdhani font-weight-bold text-success">
                                        PKR {{ number_format($sup->total_awarded) }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No supplier award records found.</td>
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

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Division Volume Chart
    const divLabels = {!! json_encode($divisionStats->pluck('division')) !!};
    const divVolumes = {!! json_encode($divisionStats->pluck('total_volume')) !!};

    const ctxDiv = document.getElementById('divisionVolumeChart').getContext('2d');
    new Chart(ctxDiv, {
        type: 'bar',
        data: {
            labels: divLabels,
            datasets: [{
                label: 'Procurement Volume (PKR)',
                data: divVolumes,
                backgroundColor: 'rgba(0, 191, 255, 0.4)',
                borderColor: '#00BFFF',
                borderWidth: 1.5,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(0, 191, 255, 0.7)'
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
                            let val = context.raw || 0;
                            return 'PKR ' + Number(val).toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#94a3b8', font: { family: 'Rajdhani', size: 12, weight: 'bold' } }
                },
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: {
                        color: '#94a3b8',
                        font: { family: 'Rajdhani', size: 11 },
                        callback: function(value) {
                            if (value >= 1e9) return (value / 1e9).toFixed(1) + 'B';
                            if (value >= 1e6) return (value / 1e6).toFixed(1) + 'M';
                            if (value >= 1e3) return (value / 1e3).toFixed(1) + 'k';
                            return value;
                        }
                    }
                }
            }
        }
    });

    // 2. Status Distribution Doughnut Chart
    const statusLabels = ['Awaiting Scrutiny', 'In HQ Pipeline', 'Approved Cases'];
    const statusCounts = [{{ (int)$pendingCount }}, {{ (int)$openCount }}, {{ (int)$closedCount }}];

    const ctxStatus = document.getElementById('statusDistChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: [
                    '#f59e0b',
                    '#00BFFF',
                    '#22c55e'
                ],
                borderColor: '#0f172a',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#cbd5e0', font: { family: 'Inter', size: 11 }, padding: 15 }
                }
            },
            cutout: '70%'
        }
    });

    // 3. Monthly Trend Chart
    const monthLabels = {!! json_encode($monthlyTrend->pluck('month_label')) !!};
    const monthVolumes = {!! json_encode($monthlyTrend->pluck('volume')) !!};

    const ctxTrend = document.getElementById('monthlyTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: monthLabels.length ? monthLabels : ['No Data'],
            datasets: [{
                label: 'Monthly Volume (PKR)',
                data: monthVolumes.length ? monthVolumes : [0],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.35,
                borderWidth: 2,
                pointBackgroundColor: '#10b981',
                pointRadius: 4
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
                            return 'PKR ' + Number(context.raw || 0).toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#94a3b8', font: { family: 'Rajdhani', size: 11 } }
                },
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: {
                        color: '#94a3b8',
                        font: { family: 'Rajdhani', size: 11 },
                        callback: function(value) {
                            if (value >= 1e6) return (value / 1e6).toFixed(1) + 'M';
                            if (value >= 1e3) return (value / 1e3).toFixed(1) + 'k';
                            return value;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
