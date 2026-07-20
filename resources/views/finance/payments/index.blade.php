@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .finance-hub {
        font-family: 'Inter', sans-serif;
        background: #080b0f !important;
        color: #cbd5e0;
        padding-top: 15px;
        padding-bottom: 40px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-cyber {
        background: rgba(18, 26, 34, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

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

    .btn-cyber {
        background: rgba(0, 191, 255, 0.1);
        border: 1px solid rgba(0, 191, 255, 0.3);
        color: #00BFFF;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .btn-cyber:hover {
        background: rgba(0, 191, 255, 0.2);
        color: #fff;
        box-shadow: 0 0 10px rgba(0, 191, 255, 0.2);
    }
</style>

<div class="content-wrapper finance-hub px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white rajdhani font-weight-bold mb-1">Disbursements & Payment Execution</h2>
            <p class="text-muted small mb-0">Record cheque/IBAN dispatches for approved commitments and log actual expenditure.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('fin.payments.index', ['status' => 'Pending Payment']) }}" class="btn btn-sm btn-outline-warning mr-2 {{ $statusFilter === 'Pending Payment' ? 'active' : '' }}">Pending</a>
            <a href="{{ route('fin.payments.index', ['status' => 'Partially Paid']) }}" class="btn btn-sm btn-outline-info mr-2 {{ $statusFilter === 'Partially Paid' ? 'active' : '' }}">Partial</a>
            <a href="{{ route('fin.payments.index', ['status' => 'Fully Paid']) }}" class="btn btn-sm btn-outline-success mr-2 {{ $statusFilter === 'Fully Paid' ? 'active' : '' }}">Fully Paid</a>
            <a href="{{ route('fin.payments.index', ['status' => 'All']) }}" class="btn btn-sm btn-outline-light {{ $statusFilter === 'All' ? 'active' : '' }}">All Approved</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card card-cyber p-4">
        <div class="table-responsive">
            <table class="table table-cyber mb-0">
                <thead>
                    <tr>
                        <th>Case ID</th>
                        <th>Title / Subject</th>
                        <th>Project Head</th>
                        <th>Unit</th>
                        <th>Total Sanction</th>
                        <th>Paid Out</th>
                        <th>Remaining Commitment</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cases as $c)
                        <tr>
                            <td class="rajdhani text-info font-weight-bold">#{{ $c->pcs_id }}</td>
                            <td>
                                <div class="font-weight-bold text-white mb-1">{{ $c->pcs_title ?? 'N/A' }}</div>
                                <span class="text-muted small">Type: {{ $c->pcs_type }} | Final Approved: {{ $c->pcs_approvedtg ?? 'N/A' }}</span>
                            </td>
                            <td class="font-weight-bold">{{ $c->hed_code ?? 'N/A' }}</td>
                            <td>{{ $c->unt_namesh ?? 'N/A' }}</td>
                            <td class="rajdhani font-weight-bold">PKR {{ number_format($c->pcs_price) }}</td>
                            <td class="rajdhani text-success font-weight-bold">PKR {{ number_format($c->total_paid) }}</td>
                            <td class="rajdhani text-warning font-weight-bold">PKR {{ number_format($c->remaining_commitment) }}</td>
                            <td>
                                @if($c->payment_status === 'Fully Paid')
                                    <span class="badge badge-success">Fully Paid</span>
                                @elseif($c->payment_status === 'Partially Paid')
                                    <span class="badge badge-info">Partially Paid</span>
                                @else
                                    <span class="badge badge-warning">Pending Payment</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('fin.payments.show', $c->pcs_id) }}" class="btn btn-sm btn-cyber">
                                    Manage Payments
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                No approved cases found with status filter: <strong>{{ $statusFilter }}</strong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3 d-flex justify-content-center">
            {{ $cases->appends(['status' => $statusFilter])->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
