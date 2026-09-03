@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .receipt-hub {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg) !important;
        min-height: 85vh;
        color: var(--rd-text1);
        padding-top: 20px;
        padding-bottom: 50px;
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
        background: rgba(255, 255, 255, 0.03) !important;
        border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
        color: #67e8f9 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        font-size: 12px;
        font-weight: 700;
        padding: 10px 14px !important;
    }
    .table-cyber td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        padding: 10px 14px !important;
        vertical-align: middle;
        font-size: 13px;
    }

    .form-control-cyber {
        background: rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #fff;
        border-radius: 8px;
        font-size: 14px;
    }
    .form-control-cyber:focus {
        background: rgba(0, 0, 0, 0.35);
        border-color: #22c55e;
        color: #fff;
        box-shadow: 0 0 8px rgba(34, 197, 94, 0.25);
    }
</style>

<div class="content-wrapper receipt-hub px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('purchase.receipts.index') }}" class="text-info text-decoration-none small">
                <i class="fas fa-chevron-left mr-1"></i> Back to Receipts List
            </a>
            <h2 class="text-white rajdhani font-weight-bold mb-1 mt-1">
                Goods Receipt &mdash; Case #{{ $purchase->pcs_id }}
            </h2>
            <p class="text-muted small mb-0">{{ $purchase->pcs_title }}</p>
        </div>
        <div>
            @if($purchase->pcs_fulfillment_status === 'Fully Received')
                <span class="badge badge-success p-2 rajdhani" style="font-size: 14px;">FULFILLMENT: FULLY RECEIVED</span>
            @elseif($purchase->pcs_fulfillment_status === 'Partially Received')
                <span class="badge badge-info p-2 rajdhani" style="font-size: 14px;">FULFILLMENT: PARTIALLY RECEIVED</span>
            @else
                <span class="badge badge-warning p-2 rajdhani" style="font-size: 14px;">FULFILLMENT: PENDING RECEIPT</span>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8 mb-4">
            <form action="{{ route('purchase.receipts.store', $purchase->pcs_id) }}" method="POST">
                @csrf
                <div class="card card-cyber p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-2">
                        <h5 class="text-white rajdhani font-weight-bold mb-0">
                            <i class="fas fa-boxes text-info mr-2"></i> Receive Case Items
                        </h5>
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-muted small mb-0 mr-2">Receipt Date:</label>
                            <input type="date" 
                                   name="prt_date" 
                                   class="form-control form-control-cyber form-control-sm" 
                                   style="width: 160px;" 
                                   value="{{ old('prt_date', date('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}" 
                                   required>
                        </div>
                    </div>

                    @if($purchase->pcs_fulfillment_status === 'Fully Received')
                        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-3">
                            <i class="fas fa-check-double mr-2"></i> All items for this purchase case have been fully received and taken on inventory charge.
                        </div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="table table-cyber mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Description</th>
                                    <th class="text-right">Ordered Qty</th>
                                    <th class="text-right">Previously Received</th>
                                    <th class="text-center" style="width: 170px;">Receive Now</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    @php
                                        $ordered = (float)($item->pci_qty ?? 0);
                                        $previouslyReceived = (float)($item->pci_fulfilment ?? 0);
                                        $remaining = max(0, $ordered - $previouslyReceived);
                                        $isService = (int)($item->pci_type ?? 1) === 3;
                                    @endphp
                                    <tr>
                                        <td class="rajdhani text-muted">#{{ $item->pci_serial }}</td>
                                        <td>
                                            <div class="font-weight-bold text-white mb-0">{{ $item->pci_desc }}</div>
                                            <span class="text-muted small">
                                                Subtype: {{ $item->pci_subtype ?? 'General' }}
                                                @if($isService)
                                                    <span class="badge badge-secondary ml-1" style="font-size: 10px;">SERVICE (NO ASSET)</span>
                                                @else
                                                    <span class="badge badge-info ml-1" style="font-size: 10px;">MATERIAL ASSET</span>
                                                @endif
                                                | Est: PKR {{ number_format($item->pci_price) }}
                                            </span>
                                        </td>
                                        <td class="text-right font-weight-bold rajdhani">{{ $ordered }} {{ $item->pci_qtyunit }}</td>
                                        <td class="text-right text-success font-weight-bold rajdhani">{{ $previouslyReceived }} {{ $item->pci_qtyunit }}</td>
                                        <td class="text-center">
                                            @if($remaining <= 0)
                                                <span class="badge badge-success px-2 py-1 rajdhani">FULLY RECEIVED</span>
                                            @else
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <input type="number" 
                                                           step="0.01" 
                                                           name="items[{{ $item->pci_id }}][received_qty]" 
                                                           value="{{ old("items.{$item->pci_id}.received_qty", $remaining) }}" 
                                                           min="0"
                                                           max="{{ $remaining }}"
                                                           class="form-control form-control-cyber text-right"
                                                           style="width: 90px;"
                                                           placeholder="0">
                                                    <span class="text-muted small ml-2">/ {{ $remaining }}</span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($purchase->pcs_fulfillment_status !== 'Fully Received')
                        <div class="mt-4 pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                <i class="fas fa-info-circle mr-1 text-info"></i> Non-service items will automatically create on-charge inventory assets in <code>ina.invats</code>.
                            </span>
                            <button type="submit" class="btn btn-success rajdhani font-weight-bold px-4 py-2" style="font-size: 15px;">
                                <i class="fas fa-check-circle mr-2"></i> FINALIZE GOODS RECEIPT
                            </button>
                        </div>
                    @endif
                </div>
            </form>
        </div>

        <!-- Sidebar Summary & Previous Receipts -->
        <div class="col-lg-4 mb-4">
            <!-- Case Summary -->
            <div class="card card-cyber p-4 mb-4">
                <h5 class="text-white rajdhani font-weight-bold mb-3 border-bottom border-secondary pb-2">
                    <i class="fas fa-file-alt text-info mr-2"></i> Case Overview
                </h5>
                <div class="mb-2">
                    <span class="text-muted small d-block">Budget Head:</span>
                    <strong class="text-info">{{ $purchase->hed_code }} &mdash; {{ $purchase->hed_name }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Initiating Unit:</span>
                    <strong class="text-white">{{ $purchase->int_unt_namesh ?? $purchase->unt_namesh }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Vendor / Firm:</span>
                    <strong class="text-white">{{ $purchase->frm_name ?? 'N/A' }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Total Sanctioned Price:</span>
                    <h5 class="text-warning rajdhani font-weight-bold mb-0">PKR {{ number_format($purchase->pcs_price, 2) }}</h5>
                </div>
            </div>

            <!-- Previous Receipts Timeline -->
            <div class="card card-cyber p-4">
                <h5 class="text-white rajdhani font-weight-bold mb-3 border-bottom border-secondary pb-2">
                    <i class="fas fa-history text-success mr-2"></i> Receipts History ({{ $previousReceipts->count() }})
                </h5>
                @forelse($previousReceipts as $pr)
                    <div class="border-bottom border-secondary pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-info font-weight-bold rajdhani" style="font-size: 15px;">Receipt #{{ $pr->prt_id }}</span>
                            <span class="badge badge-success rajdhani">{{ $pr->prt_status }}</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Date: {{ $pr->prt_date ? date('d-M-Y', strtotime($pr->prt_date)) : 'N/A' }}</span>
                            <span class="text-white rajdhani font-weight-bold">Value: PKR {{ number_format((float)($pr->prt_value ?? 0), 2) }}</span>
                        </div>
                        @if(isset($pr->items) && $pr->items->isNotEmpty())
                            <div class="p-2 rounded" style="background: rgba(255,255,255,0.02); font-size: 12px;">
                                @foreach($pr->items as $pi)
                                    <div class="d-flex justify-content-between text-muted">
                                        <span class="text-truncate" style="max-width: 180px;">{{ $pi->pti_desc }}</span>
                                        <span class="text-white font-weight-bold">{{ $pi->pti_qty }} {{ $pi->pti_qtyunit }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-muted small mb-0 text-center py-3">No goods receipts logged for this case yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
