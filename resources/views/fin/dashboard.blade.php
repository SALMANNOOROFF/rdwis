@extends('welcome')

@section('content')
<div class="content-wrapper">
    <!-- Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-12 d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
                    <h1 class="m-0 font-weight-bold text-dark" style="font-family: 'Rajdhani', sans-serif;">
                        <i class="fas fa-chart-line mr-2 text-primary"></i>Finance Administration
                    </h1>
                    <div class="d-flex align-items-center">
                        <!-- Toggle Buttons -->
                        <div class="btn-group btn-group-sm shadow-sm" role="group">
                            <a href="{{ route('fin.dashboard', ['mode' => 'm']) }}" 
                               class="btn {{ $mode === 'm' ? 'btn-danger font-weight-bold' : 'btn-outline-danger bg-white' }}">
                                <i class="fas fa-globe mr-1"></i> All Data
                            </a>
                            <a href="{{ route('fin.dashboard', ['mode' => 's']) }}" 
                               class="btn {{ $mode === 's' ? 'btn-info font-weight-bold' : 'btn-outline-info bg-white' }}"
                               style="{{ $mode === 's' ? 'background-color: #17a2b8; border-color: #17a2b8; color: white;' : '' }}">
                                <i class="fas fa-sitemap mr-1"></i> My Dept
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Showcase Finance Metrics -->
            <div class="row">
                <!-- Cases Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box shadow-sm {{ $mode === 'm' ? 'bg-gradient-primary' : 'bg-gradient-info' }}">
                        <div class="inner">
                            <h3>{{ $totalCases }}</h3>
                            <p>Total Purchase Cases</p>
                            <span class="badge badge-light mb-2">{{ $activeCases }} currently active</span>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart text-white-50"></i>
                        </div>
                        <a href="{{ route('viewpurchasecase') }}" class="small-box-footer">View List <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <!-- Projects Budget Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box shadow-sm {{ $mode === 'm' ? 'bg-gradient-success' : 'bg-gradient-teal' }}">
                        <div class="inner">
                            <h3>
                                <span style="font-size: 1.5rem">Rs</span> 
                                {{ number_format($totalProjectsBudget) }}
                            </h3>
                            <p>Approved Project Budgets</p>
                            <span class="badge badge-light mb-2">Across {{ $totalProjects }} Projects</span>
                        </div>
                        <div class="icon">
                            <i class="fas fa-project-diagram text-white-50"></i>
                        </div>
                        <a href="{{ route('view-projects') }}" class="small-box-footer">View Projects <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <!-- Financial Spending Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box shadow-sm bg-gradient-danger">
                        <div class="inner">
                            <h3>
                                <span style="font-size: 1.5rem">Rs</span> 
                                {{ number_format($totalSpent) }}
                            </h3>
                            <p>Total Case Expense</p>
                            <span class="badge badge-light mb-2">Evaluated amount</span>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-invoice-dollar text-white-50"></i>
                        </div>
                        <a href="#" class="small-box-footer">View Ledger <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <!-- HR Alignment Card -->
                <div class="col-lg-3 col-6">
                    <div class="small-box shadow-sm bg-gradient-warning">
                        <div class="inner">
                            <h3 class="text-white">{{ $totalHeadCount }}</h3>
                            <p class="text-white">Active Personnel</p>
                            <span class="badge badge-light mb-2 text-dark">Included in view</span>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users text-white-50"></i>
                        </div>
                        <a href="#" class="small-box-footer" style="color: rgba(255,255,255,0.8) !important;">Manage Staff <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Employee List Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 border-top border-primary rounded-lg">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                            <h3 class="card-title font-weight-bold text-dark m-0">
                                <i class="fas fa-user-tie text-primary mr-2"></i> Employee Mapping
                                @if($mode === 's')
                                    <span class="badge badge-info ml-2">Filtered</span>
                                @else
                                    <span class="badge badge-danger ml-2">Unfiltered</span>
                                @endif
                            </h3>
                            <span class="text-muted text-sm">{{ count($employeesList) }} records</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="rd-table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-hover table-striped table-valign-middle m-0 text-nowrap">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th>Employee Name</th>
                                            <th>Rank / Title</th>
                                            <th>Assigned Head</th>
                                            <th>Unit Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($employeesList as $emp)
                                        <tr>
                                            <td class="font-weight-bold text-dark">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-user-circle text-muted mr-2" style="font-size: 1.5rem;"></i>
                                                    {{ $emp->emp_name }}
                                                </div>
                                            </td>
                                            <td>{{ $emp->emp_joinrank ?: 'N/A' }}</td>
                                            <td class="text-muted">{{ $emp->hed_name }}</td>
                                            <td><span class="badge badge-light border px-2 py-1">{{ $emp->unit_name }}</span></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="fas fa-folder-open mb-2" style="font-size: 2rem;"></i><br>
                                                No employees found for the current dynamic limits.
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

        </div>
    </section>
</div>

<style>
.bg-gradient-teal {
    background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
    color: white;
}
.small-box {
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s ease-in-out;
}
.small-box:hover {
    transform: translateY(-3px);
}
.badge-light {
    background-color: rgba(255,255,255,0.4) !important;
    color: #333 !important;
}
</style>

@endsection
