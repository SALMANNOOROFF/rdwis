@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Division Dashboard <span class="text-muted" style="font-size: 0.6em;">v2.0</span></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Summary Widgets -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box shadow-sm" style="background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%); color: white;">
                        <div class="inner">
                            <h3>{{ $totalProjects }}</h3>
                            <p>Total Projects</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-project-diagram" style="color: rgba(255,255,255,0.2);"></i>
                        </div>
                        <a href="{{ route('view-projects') }}" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box shadow-sm" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                        <div class="inner">
                            <h3>{{ $hucProjects }}</h3>
                            <p>HUC / Completed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-double" style="color: rgba(255,255,255,0.2);"></i>
                        </div>
                        <div class="small-box-footer">Unit Clearance Progress</div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                        <div class="inner">
                            <h3>{{ $booksCount }}</h3>
                            <p>Divisional Books</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-book-open" style="color: rgba(255,255,255,0.2);"></i>
                        </div>
                        <a href="{{ route('training.books.index') }}" class="small-box-footer">Manage Library <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box shadow-sm" style="background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%); color: white;">
                        <div class="inner">
                            <h3>{{ $licenseCount }}</h3>
                            <p>Active Licenses</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-key" style="color: rgba(255,255,255,0.2);"></i>
                        </div>
                        <a href="{{ route('training.license.index') }}" class="small-box-footer">Renewals / Keys <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Graphs Section -->
            <div class="row">
                <!-- Project Spendings Chart -->
                <div class="col-lg-8 col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-file-invoice-dollar mr-2 text-primary"></i>
                                Financial Standing: Project-wise Budget Utilization
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-info mr-2">Total Amount: {{ number_format($totalAmount) }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="height: 350px;">
                                <canvas id="financialChart"></canvas>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 text-center py-4">
                            <div class="row">
                                <div class="col-sm-6 border-right">
                                    <div class="description-block">
                                        <h5 class="description-header text-success">{{ number_format($totalAmount) }}</h5>
                                        <span class="description-text">TOTAL APPROVED</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="description-block">
                                        <h5 class="description-header text-danger">{{ number_format($totalSpent) }}</h5>
                                        <span class="description-text">TOTAL SPENT</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Procurement Health Pie Chart -->
                <div class="col-lg-4 col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-shopping-cart mr-2 text-warning"></i>
                                Procurement Status
                            </h3>
                        </div>
                        <div class="card-body">
                            <div style="height: 250px;">
                                <canvas id="procurementPieChart"></canvas>
                            </div>
                        </div>
                        <div class="card-footer bg-white p-0">
                            <ul class="nav nav-pills flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('viewpurchasecase') }}" class="nav-link">
                                        Active Cases
                                        <span class="float-right text-primary font-weight-bold">{{ $activeCases }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <div class="nav-link">
                                        Total Procurement History
                                        <span class="float-right text-muted font-weight-bold">{{ $totalCases }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Employee per Head -->
                    <div class="card shadow-sm border-0 mt-3">
                        <div class="card-header bg-white border-0 py-3">
                            <h3 class="card-title font-weight-bold">
                                <i class="fas fa-users mr-2 text-info"></i>
                                HR Resource Mapping
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-valign-middle m-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Project Code</th>
                                            <th class="text-center">Staff Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employeeStats as $stat)
                                        <tr>
                                            <td>{{ $stat->hed_code }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-info px-3 py-2" style="font-size: 1rem;">{{ $stat->emp_count }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
<script>
$(function () {
    // 1. Financial Bar Chart
    const ctxFin = document.getElementById('financialChart').getContext('2d');
    new Chart(ctxFin, {
        type: 'bar',
        data: {
            labels: @json($financials->pluck('prj_title')),
            datasets: [
                {
                    label: 'Approved Budget',
                    backgroundColor: 'rgba(60,141,188,0.5)',
                    borderColor: 'rgba(60,141,188,0.8)',
                    data: @json($financials->pluck('approved'))
                },
                {
                    label: 'Utilized Amount',
                    backgroundColor: 'rgba(210, 214, 222, 1)',
                    borderColor: 'rgba(210, 214, 222, 1)',
                    data: @json($financials->pluck('spent'))
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: { position: 'top' },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) { return value.toLocaleString(); }
                    }
                }]
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel.toLocaleString();
                    }
                }
            }
        }
    });

    // 2. Procurement Pie Chart
    const ctxProc = document.getElementById('procurementPieChart').getContext('2d');
    new Chart(ctxProc, {
        type: 'doughnut',
        data: {
            labels: ['Active Cases', 'Pending Release', 'Completed'],
            datasets: [{
                data: [{{ $activeCases }}, {{ $totalCases - $activeCases }}, 0],
                backgroundColor: ['#007bff', '#ffc107', '#28a745']
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: { position: 'bottom' }
        }
    });
});
</script>
@endpush

<style>
.small-box { border-radius: 12px; overflow: hidden; }
.card { border-radius: 12px; }
.nav-link { font-size: 0.95rem; }
</style>
@endsection