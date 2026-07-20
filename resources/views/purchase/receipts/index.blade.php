@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .receipt-hub {
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
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.3);
        color: #22c55e;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .btn-cyber:hover {
        background: rgba(34, 197, 94, 0.2);
        color: #fff;
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.15);
    }
</style>

<div class="content-wrapper receipt-hub px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white rajdhani font-weight-bold mb-1">Goods & Item Receipts</h2>
            <p class="text-muted small mb-0">Track and receive materials/services for approved purchase cases.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchase.receipts.index', ['fulfillment' => 'Pending Receipt']) }}" class="btn btn-sm btn-outline-warning mr-2 {{ $fulfillmentFilter === 'Pending Receipt' ? 'active' : '' }}">Pending</a>
            <a href="{{ route('purchase.receipts.index', ['fulfillment' => 'Partially Received']) }}" class="btn btn-sm btn-outline-info mr-2 {{ $fulfillmentFilter === 'Partially Received' ? 'active' : '' }}">Partial</a>
            <a href="{{ route('purchase.receipts.index', ['fulfillment' => 'Fully Received']) }}" class="btn btn-sm btn-outline-success mr-2 {{ $fulfillmentFilter === 'Fully Received' ? 'active' : '' }}">Fully Received</a>
            <a href="{{ route('purchase.receipts.index', ['fulfillment' => 'All']) }}" class="btn btn-sm btn-outline-light {{ $fulfillmentFilter === 'All' ? 'active' : '' }}">All Approved</a>
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
                        <th>Initiating Unit</th>
                        <th>Value</th>
                        <th>Fulfillment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $p)
                        <tr>
                            <td class="rajdhani text-info font-weight-bold">#{{ $p->pcs_id }}</td>
                            <td>
                                <div class="font-weight-bold text-white mb-1">{{ $p->pcs_title ?? 'N/A' }}</div>
                                <span class="text-muted small">Type: {{ $p->pcs_type }} | Approved Date: {{ $p->pcs_approvedtg ?? 'N/A' }}</span>
                            </td>
                            <td class="font-weight-bold">{{ $p->hed_code ?? 'N/A' }}</td>
                            <td>{{ $p->unt_namesh ?? 'N/A' }}</td>
                            <td class="rajdhani font-weight-bold">PKR {{ number_format($p->pcs_price) }}</td>
                            <td>
                                @if($p->pcs_fulfillment_status === 'Fully Received')
                                    <span class="badge badge-success">Fully Received</span>
                                @elseif($p->pcs_fulfillment_status === 'Partially Received')
                                    <span class="badge badge-info">Partially Received</span>
                                @else
                                    <span class="badge badge-warning">Pending Receipt</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('purchase.receipts.create', $p->pcs_id) }}" class="btn btn-sm btn-cyber">
                                    Receive Items
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No approved purchase cases found with fulfillment status: <strong>{{ $fulfillmentFilter }}</strong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3 d-flex justify-content-center">
            {{ $purchases->appends(['fulfillment' => $fulfillmentFilter])->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
