@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
            <h1 class="m-0 font-weight-bold text-dark" style="font-family: 'Rajdhani', sans-serif;">
                <i class="fas fa-shield-alt mr-2 text-primary"></i>Admin Dashboard
            </h1>
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <a href="{{ route('admin.accounts.index') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <i class="fas fa-users-cog mr-1"></i> Accounts
                </a>
                <a href="{{ route('admin.reversals.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-undo-alt mr-1"></i> Reversals
                </a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Open Accounts</span>
                            <span class="info-box-number">{{ $accountsOpenCount }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-user-slash"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Closed Accounts</span>
                            <span class="info-box-number">{{ $accountsClosedCount }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-undo-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Open Reversals</span>
                            <span class="info-box-number">{{ $reversalsOpenCount }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-check-double"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Closed Reversals</span>
                            <span class="info-box-number">{{ $reversalsClosedCount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Accounts</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="accountsChart" height="140"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Data Reversals</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="reversalsChart" height="140"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                            <h3 class="card-title mb-0 font-weight-bold">Latest Reversal Requests</h3>
                            <a href="{{ route('admin.reversals.index') }}" class="btn btn-xs btn-outline-primary rounded-pill px-3">
                                View All
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="rd-table-responsive">
                                <table class="table table-hover table-striped table-sm mb-0 text-nowrap">
                                <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Object</th>
                                    <th>Object ID</th>
                                    <th>Status</th>
                                    <th>Unit</th>
                                    <th>Date</th>
                                    <th>Released</th>
                                    <th>Closed</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($latestReversals as $rev)
                                    <tr>
                                        <td><strong>{{ $rev->rev_id }}</strong></td>
                                        <td>{{ $rev->rev_obj }}</td>
                                        <td>{{ $rev->rev_objid }}</td>
                                        <td>
                                            <span class="badge badge-{{ $rev->rev_status === 'Fulfilled' ? 'success' : ($rev->rev_status === 'Cancelled' ? 'danger' : 'warning') }}">
                                                {{ $rev->rev_status }}
                                            </span>
                                        </td>
                                        <td>{{ $rev->rev_unt_id }}</td>
                                        <td>{{ $rev->rev_date }}</td>
                                        <td>{{ $rev->rev_releasedtg }}</td>
                                        <td>{{ $rev->rev_closedtg }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No reversal requests found.</td>
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
@endsection

@push('scripts')
<script>
    $(function () {
        const accountsCtx = document.getElementById('accountsChart');
        if (accountsCtx) {
            new Chart(accountsCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Open', 'Closed'],
                    datasets: [{
                        data: [{{ (int) $accountsOpenCount }}, {{ (int) $accountsClosedCount }}],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        const reversalsCtx = document.getElementById('reversalsChart');
        if (reversalsCtx) {
            new Chart(reversalsCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending/Open', 'Fulfilled', 'Cancelled'],
                    datasets: [{
                        data: [{{ (int) $reversalsPendingCount }}, {{ (int) $reversalsFulfilledCount }}, {{ (int) $reversalsCancelledCount }}],
                        backgroundColor: ['#ffc107', '#28a745', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    });
</script>
@endpush

