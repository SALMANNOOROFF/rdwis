@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .receipt-view-canvas {
        font-family: 'Inter', sans-serif;
        background: #f1f5f9;
        min-height: 88vh;
        padding: 24px 20px 60px;
        color: #1e293b;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    /* Outer Container */
    .receipt-container {
        max-width: 1100px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        padding: 30px 36px;
    }

    /* Top Action Bar matching Image 1 */
    .receipt-header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    .receipt-main-title {
        color: #436440;
        font-family: 'Inter', sans-serif;
        font-size: 26px;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.3px;
    }

    .btn-acceptance-form {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        padding: 7px 18px;
        transition: all 0.2s ease;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .btn-acceptance-form:hover {
        background: #5F7858;
        border-color: #5F7858;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(95, 120, 88, 0.25);
    }

    /* Meta Fields Grid */
    .receipt-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px 28px;
        margin-bottom: 24px;
        background: #fafafa;
        border: 1px solid #edf2f7;
        border-radius: 8px;
        padding: 16px 20px;
    }

    .meta-field {
        display: flex;
        align-items: baseline;
    }
    .meta-label {
        font-weight: 600;
        color: #64748b;
        font-size: 13.5px;
        min-width: 90px;
    }
    .meta-val {
        font-weight: 700;
        color: #0f172a;
        font-size: 14px;
    }
    .meta-val-badge {
        font-family: 'Rajdhani', sans-serif;
        font-size: 13px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
    }

    /* Purchase Case Box (Green-tinted frame replica of Image 1) */
    .pc-card-box {
        background: #ceddcc; /* subtle light sage/green tone exactly like legacy */
        border: 1px solid #b5c9b3;
        border-radius: 8px;
        padding: 18px 22px;
        margin-bottom: 28px;
        color: #1e293b;
    }
    .pc-box-title {
        color: #385535;
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .pc-row-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
        gap: 12px 24px;
    }
    .pc-field-row {
        display: flex;
        align-items: baseline;
        margin-bottom: 6px;
    }
    .pc-field-label {
        color: #4b6348;
        font-size: 13px;
        font-weight: 600;
        width: 130px;
        flex-shrink: 0;
    }
    .pc-field-val {
        color: #0f172a;
        font-size: 13.5px;
        font-weight: 600;
    }

    /* Items Section */
    .items-sec-title {
        color: #334155;
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-receipt-items {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        overflow: hidden;
    }
    .table-receipt-items th {
        background: #becbbe !important; /* Sage header like Image 1 */
        color: #284425 !important;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 14px;
        border-bottom: 1px solid #a8baa7;
        border-top: none;
    }
    .table-receipt-items td {
        background: #ffffff;
        color: #1e293b;
        font-size: 13.5px;
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: top;
    }
    .table-receipt-items tr:last-child td {
        border-bottom: none;
    }

    .date-tag {
        background: #1e293b;
        color: #ffffff;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: 'Rajdhani', sans-serif;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
</style>

<div class="receipt-view-canvas">
    <div class="receipt-container">
        <!-- Top Action Bar -->
        <div class="receipt-header-bar">
            <div>
                <a href="{{ route('purchase.receipts.index', ['tab' => strtolower($receipt->prt_status) === 'draft' ? 'draft' : 'closed']) }}" 
                   class="text-muted text-decoration-none small d-block mb-1">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Receipts List
                </a>
                <h1 class="receipt-main-title">
                    <i class="fas fa-file-invoice text-success mr-2"></i> Purchase Receipt
                </h1>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                @if($receipt->prt_status === 'Draft')
                    @if(!empty($canManageDraft))
                        <button type="submit" form="finalizeReceiptForm" class="btn btn-outline-secondary font-weight-bold px-3 py-1 mr-2" style="font-size: 13.5px; border-radius: 4px; color: #1e293b; background: #ffffff; border: 1.5px solid #94a3b8;">
                            Finalize
                        </button>
                        <form action="{{ route('purchase.receipts.cancel_draft', $receipt->prt_id) }}" method="POST" onsubmit="return confirm('The requisition/receipt will be cancelled. Do you want to continue?');" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary font-weight-bold px-3 py-1" style="font-size: 13.5px; border-radius: 4px; color: #1e293b; background: #ffffff; border: 1.5px solid #94a3b8;">
                                Cancel
                            </button>
                        </form>
                    @endif
                @elseif($receipt->prt_status === 'Finalized')
                    @if(!empty($canViewAcceptance))
                        {{-- Visible strictly to Procurement on Finalized Receipts --}}
                        <a href="{{ route('purchase.receipts.acceptance', $receipt->prt_id) }}" 
                           target="_blank" 
                           class="btn-acceptance-form">
                            <i class="fas fa-stamp mr-1 text-primary"></i> Acceptance Form
                        </a>
                    @elseif(!$isProc)
                        <span class="text-muted small">
                            <i class="fas fa-lock mr-1"></i> Acceptance Form restricted to Procurement
                        </span>
                    @endif
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4" style="border-radius: 8px;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4" style="border-radius: 8px;">
                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Metadata Section matching Image 1 layout -->
        <div class="receipt-meta-grid">
            <!-- Row 1, Col 1 -->
            <div class="meta-field">
                <span class="meta-label">Receipt ID</span>
                <span class="meta-val rajdhani" style="font-size: 16px;">{{ $receipt->prt_id }}</span>
            </div>
            <!-- Row 1, Col 2 -->
            <div class="meta-field">
                <span class="meta-label">Division</span>
                <span class="meta-val">{{ $receipt->unt_namesh ?? $receipt->prt_unt_id }}</span>
            </div>
            <!-- Row 1, Col 3 -->
            <div class="meta-field">
                <span class="meta-label">Status</span>
                <span class="meta-val">
                    @if($receipt->prt_status === 'Finalized')
                        <span class="badge badge-success meta-val-badge">Finalized</span>
                    @elseif($receipt->prt_status === 'Draft')
                        <span class="badge badge-warning meta-val-badge">Draft</span>
                    @elseif($receipt->prt_status === 'Cancelled')
                        <span class="badge badge-danger meta-val-badge">Cancelled</span>
                    @else
                        <span class="badge badge-secondary meta-val-badge">{{ $receipt->prt_status }}</span>
                    @endif
                </span>
            </div>

            <!-- Row 2, Col 1 -->
            <div class="meta-field">
                <span class="meta-label">Date</span>
                <span class="meta-val">
                    @if($receipt->prt_status === 'Draft' && !empty($canManageDraft))
                        <form id="finalizeReceiptForm" action="{{ route('purchase.receipts.finalize', $receipt->prt_id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="date" 
                                   name="prt_date" 
                                   class="form-control form-control-sm font-weight-bold rajdhani" 
                                   style="width: 150px; display: inline-block; font-size: 14px; border: 1.5px solid #64748b; background: #fff;" 
                                   value="{{ old('prt_date', $receipt->prt_date ? date('Y-m-d', strtotime($receipt->prt_date)) : date('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}" 
                                   required>
                        </form>
                    @else
                        <span class="date-tag">
                            {{ $receipt->prt_date ? date('d M y', strtotime($receipt->prt_date)) : 'N/A' }}
                            <i class="far fa-calendar-alt ml-1"></i>
                        </span>
                    @endif
                </span>
            </div>
            <!-- Row 2, Col 2 -->
            <div class="meta-field">
                <span class="meta-label">Project</span>
                <span class="meta-val text-primary">{{ $receipt->hed_code ?? 'N/A' }}</span>
            </div>
            <!-- Row 2, Col 3 -->
            <div class="meta-field">
                <span class="meta-label">Value</span>
                <span class="meta-val rajdhani text-dark" style="font-size: 16px;">
                    {{ number_format((float)($receipt->prt_value ?? 0), 2) }}
                </span>
            </div>
        </div>

        <!-- Purchase Case Box matching Image 1 -->
        <div class="pc-card-box">
            <div class="pc-box-title">
                <i class="fas fa-folder-open mr-1"></i> Purchase Case
            </div>
            <div class="pc-row-grid">
                <!-- Left Column -->
                <div>
                    <div class="d-flex flex-wrap gap-4 mb-2">
                        <div class="mr-3">
                            <span class="pc-field-label d-inline" style="width:auto;">Case Id:</span>
                            <span class="pc-field-val rajdhani text-primary ml-1" style="font-size: 15px;">{{ $receipt->pcs_id }}</span>
                        </div>
                        <div class="mr-3">
                            <span class="pc-field-label d-inline" style="width:auto;">Date:</span>
                            <span class="pc-field-val ml-1">{{ $receipt->pcs_date ? date('d M y', strtotime($receipt->pcs_date)) : 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="pc-field-label d-inline" style="width:auto;">Minute:</span>
                            <span class="pc-field-val ml-1">{{ $receipt->pcs_minute ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <div class="pc-field-row">
                        <span class="pc-field-label">Title</span>
                        <span class="pc-field-val">{{ $receipt->pcs_title }}</span>
                    </div>

                    <div class="d-flex align-items-baseline mb-2">
                        <div class="mr-4" style="min-width: 160px;">
                            <span class="pc-field-label d-inline" style="width:auto;">Head:</span>
                            <span class="pc-field-val ml-1">{{ $receipt->hed_code }}</span>
                        </div>
                        <div>
                            <span class="pc-field-label d-inline" style="width:auto;">Status:</span>
                            <span class="badge badge-info ml-1 rajdhani" style="font-size: 12px;">{{ $receipt->pcs_status }}</span>
                        </div>
                    </div>

                    <div class="pc-field-row">
                        <span class="pc-field-label">Firm</span>
                        <span class="pc-field-val font-weight-bold">{{ $receipt->frm_name ?? 'N/A' }}</span>
                    </div>

                    <div class="pc-field-row mt-2">
                        <span class="pc-field-label">Terms and Conditions</span>
                        <span class="pc-field-val text-muted">{{ $receipt->pcs_remarks ?: '100% payment after delivery' }}</span>
                    </div>
                </div>

                <!-- Right Financial Summary Column -->
                <div class="pl-md-3" style="border-left: 1px dashed #b1c7b0;">
                    <div class="pc-field-row justify-content-between">
                        <span class="pc-field-label">Price:</span>
                        <span class="pc-field-val rajdhani text-right">{{ number_format($pcsPrice, 2) }}</span>
                    </div>
                    <div class="pc-field-row justify-content-between">
                        <span class="pc-field-label">SST:</span>
                        <span class="pc-field-val rajdhani text-right">{{ number_format($pcsSst, 2) }}</span>
                    </div>
                    <div class="pc-field-row justify-content-between">
                        <span class="pc-field-label">GST:</span>
                        <span class="pc-field-val rajdhani text-right">{{ number_format($pcsGst, 2) }}</span>
                    </div>
                    <div class="pc-field-row justify-content-between pt-2 border-top" style="border-color: #a7bfa5 !important;">
                        <span class="pc-field-label font-weight-bold text-dark">Total:</span>
                        <span class="pc-field-val rajdhani font-weight-bold text-dark text-right" style="font-size: 16px;">
                            {{ number_format($pcsTotal, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Section matching Image 1 -->
        <div class="items-sec-title">
            <i class="fas fa-boxes text-success"></i> Items
        </div>
        <div class="table-responsive">
            <table class="table-receipt-items">
                <thead>
                    <tr>
                        <th style="width: 80px;">Serial</th>
                        <th>Desc</th>
                        <th class="text-right" style="width: 100px;">Qty</th>
                        <th style="width: 140px;">Denomination</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td class="rajdhani font-weight-bold text-muted">{{ $item->pti_serial }}</td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $item->pti_desc }}</div>
                            </td>
                            <td class="text-right font-weight-bold rajdhani text-dark" style="font-size: 15px;">
                                {{ $item->pti_qty }}
                            </td>
                            <td class="text-muted">{{ $item->pti_qtyunit ?? 'num' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                No items found in this receipt.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
