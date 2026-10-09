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
            @if($purchase->pcs_status === 'Partially Fulfilled')
                <span class="badge badge-secondary px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700; background: #475569; color: #fff;">
                    <i class="fas fa-archive mr-1"></i> STATUS: PARTIALLY FULFILLED (CLOSED)
                </span>
            @elseif($purchase->pcs_fulfillment_status === 'Fully Received')
                <span class="badge badge-success px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700;">
                    <i class="fas fa-check-circle mr-1"></i> FULFILLMENT: FULLY RECEIVED
                </span>
            @elseif($purchase->pcs_fulfillment_status === 'Partially Received')
                <span class="badge badge-info px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700;">
                    <i class="fas fa-boxes mr-1"></i> FULFILLMENT: PARTIALLY RECEIVED
                </span>
            @else
                <span class="badge badge-warning px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700;">
                    <i class="fas fa-clock mr-1"></i> FULFILLMENT: PENDING RECEIPT
                </span>
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

    @if($existingDraft)
        <div class="alert alert-warning border border-warning-subtle d-flex justify-content-between align-items-center mb-4 p-3 shadow-sm" style="border-radius: 10px; background: #fffbeb;">
            <div>
                <h6 class="font-weight-bold text-dark mb-1 rajdhani" style="font-size: 16px;">
                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i> Open Draft Receipt Exists (#{{ $existingDraft->prt_id }})
                </h6>
                <p class="text-muted small mb-0">
                    A draft receipt is already in progress for this purchase case. You must finalize or cancel the draft receipt before creating a new receipt batch.
                </p>
            </div>
            <a href="{{ route('purchase.receipts.show', $existingDraft->prt_id) }}" class="btn btn-warning rajdhani font-weight-bold px-3 py-2 text-dark shadow-sm" style="font-size: 13.5px; white-space: nowrap;">
                <i class="fas fa-external-link-alt mr-1"></i> Open Draft #{{ $existingDraft->prt_id }}
            </a>
        </div>
    @endif

    <div class="row">
        <!-- Main Content Column -->
        <div class="col-lg-8 mb-4">
            @if(!empty($canReceive) && !$existingDraft)
                <form action="{{ route('purchase.receipts.store', $purchase->pcs_id) }}" method="POST">
                    @csrf
            @endif

            <div class="card card-clean p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="text-dark rajdhani font-weight-bold mb-0">
                        <i class="fas fa-boxes text-primary mr-2"></i> Case Items &amp; Fulfillment
                    </h5>
                    <span class="text-muted small">Enter quantities received in this batch</span>
                </div>

                @if(empty($canReceive))
                    <div class="alert alert-info bg-light border text-dark mb-3" style="border-radius: 8px; font-size: 13px;">
                        <i class="fas fa-info-circle text-info mr-2"></i>
                        @if($purchase->pcs_status === 'Partially Fulfilled')
                            This purchase case has been closed as <strong>Partially Fulfilled</strong>. The remaining unfulfilled balance was cancelled and no further receipts can be logged.
                        @elseif($purchase->pcs_fulfillment_status === 'Fully Received')
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
                    <div class="mt-4 pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="text-muted small">
                            <div><i class="fas fa-info-circle mr-1 text-primary"></i> Receipt date will be selected and confirmed on the receipt form before finalization.</div>
                            @if($previousReceipts->isNotEmpty() && !$existingDraft)
                                <div class="text-muted mt-1" style="font-size: 12px;">
                                    If the vendor will not deliver any more pending items, you can close this case as Partially Fulfilled.
                                </div>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($previousReceipts->isNotEmpty() && !$existingDraft)
                                <button type="button" class="btn btn-outline-danger rajdhani font-weight-bold px-3 py-2 mr-2" style="font-size: 13.5px; border-radius: 8px;" data-toggle="modal" data-target="#closePartialModal">
                                    <i class="fas fa-ban mr-1"></i> CLOSE REMAINING BALANCE
                                </button>
                            @endif

                            @if($existingDraft)
                                <a href="{{ route('purchase.receipts.show', $existingDraft->prt_id) }}" class="btn btn-warning rajdhani font-weight-bold px-4 py-2 text-dark shadow-sm" style="font-size: 14px; border-radius: 8px;">
                                    <i class="fas fa-edit mr-2"></i> OPEN ACTIVE DRAFT #{{ $existingDraft->prt_id }}
                                </a>
                            @else
                                <button type="submit" class="btn btn-receive-action rajdhani font-weight-bold px-4 py-2" style="font-size: 14px; border-radius: 8px;">
                                    <i class="fas fa-file-invoice mr-2"></i> CREATE GOODS RECEIPT
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            @if(!empty($canReceive) && !$existingDraft)
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
                    <i class="fas fa-history text-success mr-2"></i> Receipts History (Finalized) ({{ $previousReceipts->count() }})
                </h5>
                @forelse($previousReceipts as $pr)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-primary font-weight-bold rajdhani" style="font-size: 15px;">Receipt #{{ $pr->prt_id }}</span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge badge-success rajdhani">{{ $pr->prt_status }}</span>
                                <a href="{{ route('purchase.receipts.show', $pr->prt_id) }}" target="_blank" class="btn btn-sm btn-outline-success font-weight-bold rajdhani px-2 py-0 ml-1" style="font-size: 11px;">
                                    <i class="fas fa-eye mr-1"></i> FORM
                                </a>
                            </div>
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

<!-- Close Remaining Balance Modal (Legacy CancelPC -> Partially Fulfilled) -->
@if(!empty($canReceive) && $previousReceipts->isNotEmpty())
<div class="modal fade" id="closePartialModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; font-family: 'Inter', sans-serif;">
            <form action="{{ route('purchase.receipts.cancel', $purchase->pcs_id) }}" method="POST">
                @csrf
                <div class="modal-header py-3 px-4 text-white" style="background: #991b1b;">
                    <h5 class="modal-title rajdhani font-weight-bold mb-0" style="font-size: 18px;">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Close Case &amp; Mark Partially Fulfilled
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-dark font-weight-bold mb-2" style="font-size: 14.5px;">
                        Are you sure you want to close Purchase Case #{{ $purchase->pcs_id }}?
                    </p>
                    <p class="text-muted small mb-3">
                        Use this option when the vendor cannot deliver the remaining pending items and you wish to close this purchase case.
                    </p>
                    <div class="p-3 rounded mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 13px;">
                        <div class="d-flex align-items-center mb-1 text-dark">
                            <i class="fas fa-check-circle text-success mr-2"></i>
                            <span>Items already received (<strong>{{ $previousReceipts->count() }} finalized batch(es)</strong>) remain safe on store inventory charge.</span>
                        </div>
                        <div class="d-flex align-items-center mb-1 text-dark">
                            <i class="fas fa-ban text-danger mr-2"></i>
                            <span>Remaining unfulfilled items will be cancelled.</span>
                        </div>
                        <div class="d-flex align-items-center text-dark">
                            <i class="fas fa-undo text-primary mr-2"></i>
                            <span>Unfulfilled budget requisition quotas will be released back to the project.</span>
                        </div>
                    </div>
                    <div class="text-secondary small font-italic">
                        * The case status will be permanently updated to <strong>Partially Fulfilled</strong> and moved to closed cases.
                    </div>
                </div>
                <div class="modal-footer py-2 px-4 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary font-weight-bold rajdhani px-3" data-dismiss="modal">
                        BACK TO RECEIPT
                    </button>
                    <button type="submit" class="btn btn-danger font-weight-bold rajdhani px-4" style="background: #dc2626; border-color: #dc2626;">
                        <i class="fas fa-check mr-1"></i> CONFIRM &amp; CLOSE CASE
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
