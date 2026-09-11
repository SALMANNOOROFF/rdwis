@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .receipt-hub {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg, #F7F5F0) !important;
        min-height: 85vh;
        color: var(--rd-neutral-900, #292824);
        padding-top: 20px;
        padding-bottom: 50px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-clean {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    }

    .table-clean {
        background: #ffffff;
        color: #1e293b;
    }
    .table-clean th {
        background: #f8fafc !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-top: none !important;
        color: #475569 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 12px;
        font-weight: 700;
        padding: 11px 14px !important;
    }
    .table-clean td {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 11px 14px !important;
        vertical-align: middle;
        font-size: 13.5px;
        color: #1e293b;
    }

    .form-control-clean {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        border-radius: 8px;
        font-size: 14px;
        padding: 6px 12px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control-clean:focus {
        background: #ffffff;
        border-color: #5F7858;
        color: #0f172a;
        box-shadow: 0 0 0 3px rgba(95, 120, 88, 0.15);
        outline: none;
    }

    .btn-receive-action {
        background: #5F7858;
        border: 1px solid #5F7858;
        color: #ffffff;
        border-radius: 8px;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .btn-receive-action:hover {
        background: #4E6449;
        border-color: #4E6449;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(95, 120, 88, 0.25);
    }
</style>

<div class="content-wrapper receipt-hub px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('purchase.receipts.index') }}" class="text-secondary text-decoration-none small font-weight-bold">
                <i class="fas fa-chevron-left mr-1"></i> Back to Receipts List
            </a>
            <h2 class="text-dark rajdhani font-weight-bold mb-1 mt-1" style="font-size: 1.75rem;">
                Goods Receipt &mdash; Case #{{ $purchase->pcs_id }}
            </h2>
            <p class="text-muted small mb-0">{{ $purchase->pcs_title }}</p>
        </div>
        <div>
            @if($purchase->pcs_fulfillment_status === 'Fully Received')
                <span class="badge badge-success px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700;">FULFILLMENT: FULLY RECEIVED</span>
            @elseif($purchase->pcs_fulfillment_status === 'Partially Received')
                <span class="badge badge-info px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700;">FULFILLMENT: PARTIALLY RECEIVED</span>
            @else
                <span class="badge badge-warning px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700;">FULFILLMENT: PENDING RECEIPT</span>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4" style="border-radius: 8px;">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4" style="border-radius: 8px;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <!-- Main Content Column -->
        <div class="col-lg-8 mb-4">
            @if(!empty($canReceive))
                <form action="{{ route('purchase.receipts.store', $purchase->pcs_id) }}" method="POST">
                    @csrf
            @endif

            <div class="card card-clean p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="text-dark rajdhani font-weight-bold mb-0">
                        <i class="fas fa-boxes text-primary mr-2"></i> Case Items &amp; Fulfillment
                    </h5>
                    @if(!empty($canReceive))
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-dark small font-weight-bold mb-0 mr-2">Receipt Date:</label>
                            <input type="date" 
                                   name="prt_date" 
                                   class="form-control form-control-clean form-control-sm" 
                                   style="width: 160px;" 
                                   value="{{ old('prt_date', date('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}" 
                                   required>
                        </div>
                    @endif
                </div>

                @if(empty($canReceive))
                    <div class="alert alert-info bg-light border text-dark mb-3" style="border-radius: 8px; font-size: 13px;">
                        <i class="fas fa-info-circle text-info mr-2"></i>
                        @if($purchase->pcs_fulfillment_status === 'Fully Received')
                            All items for this purchase case have been <strong>fully received</strong> and taken on inventory charge.
                        @else
                            <strong>View-Only Mode:</strong> You are viewing receipts for this case. Only the concerned division (<strong>{{ $purchase->int_unt_namesh ?? $purchase->unt_namesh }}</strong>) is authorized to receive items.
                        @endif
                    </div>
                @endif
                
                <div class="table-responsive">
                    <table class="table table-clean mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item Description</th>
                                <th class="text-right">Ordered Qty</th>
                                <th class="text-right">Previously Received</th>
                                <th class="text-center" style="width: 170px;">{{ !empty($canReceive) ? 'Receive Now' : 'Remaining' }}</th>
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
                                    <td class="rajdhani text-muted font-weight-bold">#{{ $item->pci_serial }}</td>
                                    <td>
                                        <div class="font-weight-bold text-dark mb-0">{{ $item->pci_desc }}</div>
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
                                    <td class="text-right font-weight-bold rajdhani text-dark">{{ $ordered }} {{ $item->pci_qtyunit }}</td>
                                    <td class="text-right text-success font-weight-bold rajdhani">{{ $previouslyReceived }} {{ $item->pci_qtyunit }}</td>
                                    <td class="text-center">
                                        @if($remaining <= 0)
                                            <span class="badge badge-success px-2 py-1 rajdhani font-weight-bold">FULLY RECEIVED</span>
                                        @elseif(!empty($canReceive))
                                            <div class="d-flex align-items-center justify-content-center">
                                                <input type="number" 
                                                       step="0.01" 
                                                       name="items[{{ $item->pci_id }}][received_qty]" 
                                                       value="{{ old("items.{$item->pci_id}.received_qty", $remaining) }}" 
                                                       min="0" 
                                                       max="{{ $remaining }}" 
                                                       class="form-control form-control-clean text-right font-weight-bold" 
                                                       style="width: 85px;" 
                                                       placeholder="0">
                                                <span class="text-muted small ml-2">/ {{ $remaining }}</span>
                                            </div>
                                        @else
                                            <span class="badge badge-warning px-2 py-1 rajdhani font-weight-bold">{{ $remaining }} {{ $item->pci_qtyunit }} PENDING</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(!empty($canReceive))
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="fas fa-info-circle mr-1 text-primary"></i> Non-service items will automatically create on-charge inventory assets in <code>ina.invats</code>.
                        </span>
                        <button type="submit" class="btn btn-receive-action rajdhani font-weight-bold px-4 py-2" style="font-size: 15px;">
                            <i class="fas fa-check-circle mr-2"></i> FINALIZE GOODS RECEIPT
                        </button>
                    </div>
                @endif
            </div>

            @if(!empty($canReceive))
                </form>
            @endif
        </div>

        <!-- Sidebar Summary & Previous Receipts -->
        <div class="col-lg-4 mb-4">
            <!-- Case Summary -->
            <div class="card card-clean p-4 mb-4">
                <h5 class="text-dark rajdhani font-weight-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-file-alt text-primary mr-2"></i> Case Overview
                </h5>
                <div class="mb-2">
                    <span class="text-muted small d-block">Budget Head:</span>
                    <strong class="text-primary">{{ $purchase->hed_code }} &mdash; {{ $purchase->hed_name }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Initiating Unit:</span>
                    <strong class="text-dark">{{ $purchase->int_unt_namesh ?? $purchase->unt_namesh }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Vendor / Firm:</span>
                    <strong class="text-dark">{{ $purchase->frm_name ?? 'N/A' }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Total Sanctioned Price:</span>
                    <h5 class="text-dark rajdhani font-weight-bold mb-0">PKR {{ number_format($purchase->pcs_price, 2) }}</h5>
                </div>
            </div>

            <!-- Previous Receipts Timeline -->
            <div class="card card-clean p-4">
                <h5 class="text-dark rajdhani font-weight-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-history text-success mr-2"></i> Receipts History ({{ $previousReceipts->count() }})
                </h5>
                @forelse($previousReceipts as $pr)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-primary font-weight-bold rajdhani" style="font-size: 15px;">Receipt #{{ $pr->prt_id }}</span>
                            <span class="badge badge-success rajdhani">{{ $pr->prt_status }}</span>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Date: {{ $pr->prt_date ? date('d-M-Y', strtotime($pr->prt_date)) : 'N/A' }}</span>
                            <span class="text-dark rajdhani font-weight-bold">Value: PKR {{ number_format((float)($pr->prt_value ?? 0), 2) }}</span>
                        </div>
                        @if(isset($pr->items) && $pr->items->isNotEmpty())
                            <div class="p-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 12px;">
                                @foreach($pr->items as $pi)
                                    <div class="d-flex justify-content-between text-muted py-0.5">
                                        <span class="text-truncate text-dark" style="max-width: 180px;">{{ $pi->pti_desc }}</span>
                                        <span class="text-dark font-weight-bold">{{ $pi->pti_qty }} {{ $pi->pti_qtyunit }}</span>
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
