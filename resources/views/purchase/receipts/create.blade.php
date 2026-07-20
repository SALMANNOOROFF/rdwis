@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .receipt-hub {
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
    }
    .table-cyber td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        vertical-align: middle;
        font-size: 13px;
    }

    .form-control-cyber {
        background: rgba(10, 15, 22, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 8px;
        text-align: center;
        width: 100px;
    }
    .form-control-cyber:focus {
        background: rgba(10, 15, 22, 1);
        border-color: #22c55e;
        color: #fff;
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.2);
    }
</style>

<div class="content-wrapper receipt-hub px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('purchase.receipts.index') }}" class="text-info text-decoration-none small">&larr; Back to Receipts list</a>
            <h2 class="text-white rajdhani font-weight-bold mb-1 mt-1">Receive Items for Case #{{ $purchase->pcs_id }}</h2>
            <p class="text-muted small mb-0">Record and inventory received goods / services.</p>
        </div>
        <div>
            <span class="badge badge-info p-2 rajdhani" style="font-size: 14px;">FULFILLMENT: {{ $purchase->pcs_fulfillment_status ?? 'Pending Receipt' }}</span>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <form action="{{ route('purchase.receipts.store', $purchase->pcs_id) }}" method="POST">
                @csrf
                <div class="card card-cyber p-4 mb-4">
                    <h5 class="text-white rajdhani font-weight-bold mb-3 border-bottom border-secondary pb-2">Purchase Case Items</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-cyber mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Description</th>
                                    <th class="text-right">Ordered Qty</th>
                                    <th class="text-right">Previously Received</th>
                                    <th class="text-center" style="width: 150px;">Receive Now</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                    @php
                                        $ordered = (float)($item->pci_qty ?? 0);
                                        $previouslyReceived = (float)($item->pci_fulfilment ?? 0);
                                        $remaining = max(0, $ordered - $previouslyReceived);
                                    @endphp
                                    <tr>
                                        <td>{{ $item->pci_serial }}</td>
                                        <td>
                                            <div class="font-weight-bold text-white mb-1">{{ $item->pci_desc }}</div>
                                            <span class="text-muted small">Category: {{ $item->pci_subtype }} | Est. Price: PKR {{ number_format($item->pci_price) }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold">{{ $ordered }} {{ $item->pci_qtyunit }}</td>
                                        <td class="text-right text-success font-weight-bold">{{ $previouslyReceived }} {{ $item->pci_qtyunit }}</td>
                                        <td class="text-center d-flex justify-content-center align-items-center">
                                            @if($remaining <= 0)
                                                <span class="text-success font-weight-bold small">Fully Received</span>
                                            @else
                                                <input type="number" 
                                                       step="0.01" 
                                                       name="items[{{ $item->pci_id }}][received_qty]" 
                                                       value="{{ old("items.{$item->pci_id}.received_qty", $remaining) }}" 
                                                       max="{{ $remaining }}"
                                                       class="form-control form-control-cyber"
                                                       placeholder="0">
                                                <span class="text-muted small ml-2">/ {{ $remaining }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn btn-success rajdhani font-weight-bold px-4 py-2">
                            FINALIZE GOODS RECEIPT
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card card-cyber p-4 mb-4">
                <h5 class="text-white rajdhani font-weight-bold mb-3 border-bottom border-secondary pb-2">Case Summary</h5>
                <div class="mb-3">
                    <span class="text-muted small d-block">Subject / Title:</span>
                    <strong class="text-white">{{ $purchase->pcs_title }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Budget Head:</span>
                    <strong class="text-info">{{ $purchase->hed_code }} - {{ $purchase->hed_name }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Initiator:</span>
                    <strong class="text-white">{{ $purchase->unt_namesh }}</strong>
                </div>
            </div>

            <div class="card card-cyber p-4">
                <h5 class="text-white rajdhani font-weight-bold mb-3 border-bottom border-secondary pb-2">Previous Receipts History</h5>
                @forelse($previousReceipts as $pr)
                    <div class="border-bottom border-secondary pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <span class="text-info font-weight-bold">Receipt #{{ $pr->prt_id }}</span>
                            <span class="text-muted small">{{ $pr->prt_date }}</span>
                        </div>
                        <span class="text-muted small d-block">Status: <strong class="text-success">{{ $pr->prt_status }}</strong></span>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No receipts logged for this case yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
