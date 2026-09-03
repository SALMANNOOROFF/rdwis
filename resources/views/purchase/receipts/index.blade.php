@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .receipt-hub {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg) !important;
        color: var(--rd-text1);
        padding-top: 20px;
        padding-bottom: 40px;
        min-height: 85vh;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-cyber {
        background: var(--rd-surface);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .table-cyber {
        background: transparent;
        color: var(--rd-text1);
    }
    .table-cyber th {
        background: var(--rd-surface) !important;
        border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
        color: #67e8f9 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        font-size: 12px;
        font-weight: 700;
        padding: 12px 14px !important;
        white-space: nowrap;
    }
    .table-cyber td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        padding: 12px 14px !important;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-cyber tr:hover {
        background: var(--rd-neutral-50) !important;
    }

    .btn-cyber {
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.3);
        color: #22c55e;
        font-weight: 600;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 12px;
        padding: 5px 12px;
    }
    .btn-cyber:hover {
        background: rgba(34, 197, 94, 0.25);
        color: #fff;
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.2);
    }

    .form-control-cyber {
        background: var(--rd-surface);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #fff;
        border-radius: 8px;
        font-size: 13px;
    }
    .form-control-cyber:focus {
        background: var(--rd-surface);
        border-color: #22c55e;
        color: #fff;
        box-shadow: 0 0 8px rgba(34, 197, 94, 0.25);
    }
</style>

<div class="content-wrapper receipt-hub px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-white rajdhani font-weight-bold mb-1">Goods &amp; Item Receipts</h2>
            <p class="text-muted small mb-0">Record physical material deliveries and services for sanctioned purchase cases to update stock and assets.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('purchase.receipts.index', ['fulfillment' => 'Pending Receipt', 'unit_id' => $unitFilter, 'search' => $search]) }}" 
               class="btn btn-sm btn-outline-warning mr-2 {{ $fulfillmentFilter === 'Pending Receipt' ? 'active' : '' }}">Pending Receipt</a>
            <a href="{{ route('purchase.receipts.index', ['fulfillment' => 'Partially Received', 'unit_id' => $unitFilter, 'search' => $search]) }}" 
               class="btn btn-sm btn-outline-info mr-2 {{ $fulfillmentFilter === 'Partially Received' ? 'active' : '' }}">Partially Received</a>
            <a href="{{ route('purchase.receipts.index', ['fulfillment' => 'Fully Received', 'unit_id' => $unitFilter, 'search' => $search]) }}" 
               class="btn btn-sm btn-outline-success mr-2 {{ $fulfillmentFilter === 'Fully Received' ? 'active' : '' }}">Fully Received</a>
            <a href="{{ route('purchase.receipts.index', ['fulfillment' => 'All', 'unit_id' => $unitFilter, 'search' => $search]) }}" 
               class="btn btn-sm btn-outline-light {{ $fulfillmentFilter === 'All' ? 'active' : '' }}">All Cases</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card card-cyber p-4">
        <!-- Filter Controls -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <span class="text-muted small font-weight-bold uppercase">FILTER BY UNIT / DIVISION:</span>
            </div>
            <form action="{{ route('purchase.receipts.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="fulfillment" value="{{ $fulfillmentFilter }}">

                <!-- Unit dropdown -->
                <div style="min-width: 200px;">
                    <select name="unit_id" class="form-control form-control-cyber" onchange="this.form.submit()">
                        <option value="All" {{ $unitFilter === 'All' ? 'selected' : '' }}>All Units (In Horizon)</option>
                        @foreach($units as $u)
                            <option value="{{ $u->unt_id }}" {{ (string)$unitFilter === (string)$u->unt_id ? 'selected' : '' }}>
                                {{ $u->unt_namesh }} ({{ $u->unt_name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div style="min-width: 240px;">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-control-cyber" placeholder="Search case title, firm, head..." value="{{ $search }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-cyber">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                @if($unitFilter !== 'All' || !empty($search))
                    <a href="{{ route('purchase.receipts.index', ['fulfillment' => $fulfillmentFilter]) }}" class="btn btn-sm btn-outline-secondary" title="Clear filters">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-cyber mb-0">
                <thead>
                    <tr>
                        <th>Case ID</th>
                        <th>Min #</th>
                        <th>Title / Subject</th>
                        <th>Project Head</th>
                        <th>Initiator Unit</th>
                        <th class="text-right">Sanction Value</th>
                        <th>Case Status</th>
                        <th>Fulfillment Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $p)
                        <tr>
                            <td class="rajdhani text-info font-weight-bold">
                                <a href="{{ route('nrdi.purchase_cases_new.show', $p->pcs_id) }}" class="text-white text-decoration-none" title="View Case File">
                                    #{{ $p->pcs_id }}
                                </a>
                            </td>
                            <td class="rajdhani text-cyan font-weight-bold">{{ $p->pcs_minute ?? '-' }}</td>
                            <td style="max-width: 260px;">
                                <div class="font-weight-bold text-white text-truncate mb-0" title="{{ $p->pcs_title }}">
                                    {{ $p->pcs_title ?? 'N/A' }}
                                </div>
                                <span class="text-muted small">Type: {{ strtoupper($p->pcs_type) }} | Firm: {{ $p->frm_name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="font-weight-bold text-info small">{{ $p->hed_code ?? 'N/A' }}</div>
                                <span class="text-muted small">{{ $p->unt_namesh ?? '' }}</span>
                            </td>
                            <td>{{ $p->int_unt_namesh ?? $p->unt_namesh ?? 'N/A' }}</td>
                            <td class="rajdhani font-weight-bold text-right text-white">PKR {{ number_format($p->pcs_price, 2) }}</td>
                            <td>
                                @if($p->pcs_status === 'Fulfilled')
                                    <span class="badge badge-success px-2 py-1 rajdhani">FULFILLED</span>
                                @elseif($p->pcs_status === 'Partially Fulfilled')
                                    <span class="badge badge-info px-2 py-1 rajdhani">PARTIALLY FULFILLED</span>
                                @else
                                    <span class="badge badge-primary px-2 py-1 rajdhani">{{ strtoupper($p->pcs_status) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($p->pcs_fulfillment_status === 'Fully Received')
                                    <span class="badge badge-success px-2 py-1 rajdhani">FULLY RECEIVED</span>
                                @elseif($p->pcs_fulfillment_status === 'Partially Received')
                                    <span class="badge badge-info px-2 py-1 rajdhani">PARTIALLY RECEIVED</span>
                                @else
                                    <span class="badge badge-warning px-2 py-1 rajdhani">PENDING RECEIPT</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('purchase.receipts.create', $p->pcs_id) }}" class="btn btn-cyber">
                                    {{ $p->pcs_fulfillment_status === 'Fully Received' ? 'View Receipts' : 'Receive Items' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 text-secondary d-block"></i>
                                No approved purchase cases found with fulfillment status: <strong>{{ $fulfillmentFilter }}</strong>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4 d-flex justify-content-center">
            {{ $purchases->appends(['fulfillment' => $fulfillmentFilter, 'unit_id' => $unitFilter, 'search' => $search])->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection
