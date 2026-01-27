@extends('welcome')

@section('content')
<div class="content-wrapper bg-white {{ trim($purchase->pcs_status) !== 'Draft' ? 'case-locked' : '' }}">
    <style>
        /* Typography & Theme */
        body, .card-body, label, input, select, textarea, .meta-text {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            font-size: 13px !important;
        }

        /* --- 3-COLUMN GRID SETUP --- */
        .main-content-grid { 
            display: flex; 
            gap: 20px; 
            margin-bottom: 15px; 
            align-items: stretch; /* Teeno columns ki height barabar karne ke liye */
            flex-wrap: nowrap;
        }
        
        .timeline-container { flex: 0 0 260px; } 
        .left-container { flex: 1.8; min-width: 500px; } 
        .right-container { flex: 1.2; min-width: 350px; } 

        /* Full Height Cards */
        .card {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border-radius: 10px;
            background: #fff;
            height: 100%; /* Card container ki poori height lega */
            display: flex;
            flex-direction: column;
            margin-bottom: 0;
        }
        .card-body {
            flex: 1; /* Content kam ho tab bhi body stretch hogi */
            padding: 20px;
        }
        .date-grid { height: 100%; }

        /* Form Controls */
        label { font-weight: 600; color: #333; margin-bottom: 5px; display: block; }
        .form-control { border-radius: 6px; border: 1px solid #ced4da; height: 40px; }
        .form-control:read-only { background-color: #f6f8fa; border: 1px solid #e9ecef; }

        /* Tables */
        .readable-table { width: 100%; border-collapse: collapse; }
        .readable-table th { background: #f4f6f9; padding: 12px; color: #555; font-weight: bold; }
        .readable-table td { padding: 10px; border-bottom: 1px solid #f1f3f5; vertical-align: middle; }

        /* Buttons */
        .btn-xs { font-size: 0.75rem; padding: 3px 8px; }
        .btn-sm { font-size: 0.85rem; }

        /* Vertical Timeline CSS */
        .timeline-vertical-container { display: flex; flex-direction: column; padding: 10px 5px; }
        .stepper-item { position: relative; display: flex; flex-direction: row; align-items: flex-start; padding-bottom: 25px; }
        .stepper-item::after {
            content: ""; position: absolute; left: 15px; top: 32px; width: 3px;
            height: calc(100% - 32px); background-color: #e9ecef; z-index: 1;
        }
        .stepper-item:last-child::after { content: none; }
        .stepper-item.completed::after { background-color: #28a745; }
        .stepper-item.active::after { background: linear-gradient(to bottom, #28a745, #e9ecef); }

        .stepper-icon {
            width: 32px; height: 32px; border-radius: 50%; background-color: #fff;
            border: 3px solid #e9ecef; display: flex; align-items: center; justify-content: center;
            z-index: 2; font-size: 12px; color: #adb5bd; flex-shrink: 0; margin-right: 15px;
        }
        .stepper-item.completed .stepper-icon { background-color: #28a745; border-color: #28a745; color: #fff; }
        .stepper-item.active .stepper-icon { background-color: #007bff; border-color: #007bff; color: #fff; animation: pulse-blue 2s infinite; }

        .stepper-label { font-size: 12px; font-weight: 700; color: #6c757d; line-height: 1.2; margin-bottom: 4px; }
        .stepper-date { font-size: 10px; color: #999; background: #f8f9fa; padding: 2px 8px; border-radius: 10px; }
        .stepper-item.completed .stepper-label { color: #28a745; }
        .stepper-item.active .stepper-label { color: #007bff; }

        @keyframes pulse-blue {
            0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(0, 123, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
        }

        @media (max-width: 1200px) { 
            .main-content-grid { flex-direction: column; }
            .timeline-container, .left-container, .right-container { flex: 1; min-width: 100%; }
        }

.firm-scroll-box {
    max-height: 300px; /* Adjust height as needed */
    overflow-y: auto;
}
/* ======================
   CASE LOCKED DEFAULT
   ====================== */

/* Wrapper */
.case-locked {
    position: relative;
}

/* -----------------------
   Locked Inputs / Selects / Textareas
   ----------------------- */
.case-locked input:not(.unlock),
.case-locked textarea:not(.unlock),
.case-locked select:not(.unlock) {
    pointer-events: none;          
    background-color: #f6f8fa !important;
    border-color: #e9ecef !important;
    color: #6c757d !important;
    position: relative; /* needed for pseudo */
}

/* -----------------------
   Locked Buttons
   ----------------------- */
.case-locked button:not(.unlock),
.case-locked .btn:not(.unlock) {
    pointer-events: none;          
    opacity: 0.45 !important;
    filter: grayscale(100%);
    cursor: not-allowed !important;
    position: relative; /* needed for pseudo */
}

/* -----------------------
   Cursor for everything else (exclude unlock)
   ----------------------- */
.case-locked :not(.unlock):not(.unlock *) {
    cursor: not-allowed !important;
}

/* ======================
   HOVER: Show LOCK SIGN ONLY
   ====================== */

/* Inputs / Textareas / Selects */
.case-locked input:not(.unlock):hover::after,
.case-locked textarea:not(.unlock):hover::after,
.case-locked select:not(.unlock):hover::after {
    content: "🚫";
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    font-size: 16px;
    pointer-events: none;
}

/* Buttons */
.case-locked button:not(.unlock):hover::after,
.case-locked .btn:not(.unlock):hover::after {
    content: "🚫";
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    font-size: 16px;
    pointer-events: none;
}

/* Short Status Flow Styling */
.status-flow-container {
    display: flex;
    align-items: center;
    margin-top: 15px;
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    border-left: 5px solid #007bff;
}

.stage-box {
    display: flex;
    flex-direction: column;
}

.stage-label {
    font-size: 10px;
    text-transform: uppercase;
    color: #999;
    font-weight: 800;
    margin-bottom: 2px;
}

.stage-value {
    font-size: 14px;
    font-weight: 700;
}

.current-box { color: #007bff; }
.next-box { color: #6c757d; }

.flow-arrow {
    margin: 0 25px;
    color: #dee2e6;
    font-size: 18px;
}

    </style>
<!-- TOP HEADER CARD -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">

        <!-- TITLE + ACTION BUTTONS -->
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <label class="text-primary mb-0 font-weight-bold" style="font-size:16px !important;">
                    <i class="fas fa-tag mr-1"></i> {{ $purchase->pcs_title }}
                </label>
            </div>

            <div class="d-flex align-items-center">
                @php
                    $currentStatus = trim($purchase->pcs_status);
                @endphp

                {{-- Draft only buttons --}}
                @if($currentStatus === 'Draft')
                    <button class="btn btn-primary btn-sm mr-1 shadow-sm">Adv. Payment</button>

                    <a href="{{ route('minutesheet') }}"
                       class="btn btn-primary btn-sm mr-1 text-white shadow-sm">
                        Minute
                    </a>

                    <button class="btn btn-primary btn-sm shadow-sm">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                @endif

                {{-- Always visible --}}
                <button class="btn btn-primary btn-sm mr-1 shadow-sm ml-1 unlock"
                        type="button"
                        data-toggle="modal"
                        data-target="#caseAttachmentModal">
                    <i class="fas fa-paperclip mr-1"></i> Attachments
                </button>
            </div>
        </div>

        {{-- ================= STATUS FLOW LOGIC ================= --}}
        @php
            $subTotal = 0;
            foreach($purchase->items as $item) {
                $subTotal += ($item->pci_qty * $item->pci_price);
            }

            $commonStart = ['Draft', 'Under Scrutiny', 'Forward to MD'];
            $commonEnd   = [
                'Approved',
                'Email to MTSS',
                'PO Issued by MTSS',
                'Docs Submitted to Fin',
                'Docs Forward to MTSS',
                'Cheque Ready',
                'Cheque Collected'
            ];

            if ($subTotal < 400000) {
                $workflowSteps = array_merge($commonStart, $commonEnd);
            } elseif ($subTotal < 1000000) {
                $workflowSteps = array_merge($commonStart, ['Recommended & Forward to DDG'], $commonEnd);
            } else {
                $workflowSteps = array_merge($commonStart, ['Recommended & Forward to DG'], $commonEnd);
            }

            $activeIndex = array_search($currentStatus, $workflowSteps);
            if ($activeIndex === false) $activeIndex = 0;

            $currentLabel = $workflowSteps[$activeIndex];
            $nextLabel = $workflowSteps[$activeIndex + 1] ?? 'Process Completed';
        @endphp

        {{-- ================= STATUS DISPLAY ================= --}}
        @if($currentStatus === 'Draft')
            <!-- ONLY DRAFT (NO NEXT) -->
            <div class="status-flow-container mt-3">
                <div class="stage-box current-box w-100 text-left">
                    <span class="stage-label">Current Stage</span>
                    <span class="stage-value">
                        <i class="fas fa-dot-circle mr-1"></i> Draft
                    </span>
                </div>
            </div>
        @else
            <!-- NORMAL FLOW -->
            <div class="status-flow-container mt-3">
                <div class="stage-box current-box">
                    <span class="stage-label">Previous Stage</span>
                    <span class="stage-value">
                        <i class="fas fa-dot-circle mr-1"></i> {{ $currentLabel }}
                    </span>
                </div>

                <div class="flow-arrow">
                    <i class="fas fa-long-arrow-alt-right"></i>
                </div>

                <div class="stage-box next-box">
                    <span class="stage-label">Current Stage</span>
                    <span class="stage-value">
                        <i class="fas fa-arrow-circle-right mr-1"></i> {{ $nextLabel }}
                    </span>
                </div>
            </div>
        @endif

    </div>
</div>


    <!-- MAIN CONTENT GRID -->
    <div class="main-content-grid m-4">
@php
    $currentStatus = trim($purchase->pcs_status);
@endphp

@if($currentStatus !== 'Draft')
    <!-- COLUMN 1: TIMELINE -->
    <div class="timeline-container">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pb-0 pt-4">
                <h6 class="text-uppercase text-secondary font-weight-bold"
                    style="letter-spacing: 1px; font-size: 12px;">
                    <i class="fas fa-stream mr-2"></i> Case Workflow
                </h6>
            </div>

            <div class="card-body">
                @php
                    $subTotal = 0;
                    foreach($purchase->items as $item) {
                        $subTotal += ($item->pci_qty * $item->pci_price);
                    }

                    $commonStart = ['Draft', 'Under Scrutiny', 'Forward to MD'];
                    $commonEnd   = ['Approved', 'Email to MTSS', 'PO Issued by MTSS', 'Docs Submitted to Fin', 'Docs Forward to MTSS', 'Cheque Ready', 'Cheque Collected'];

                    if ($subTotal < 400000)
                        $workflowSteps = array_merge($commonStart, $commonEnd);
                    elseif ($subTotal < 1000000)
                        $workflowSteps = array_merge($commonStart, ['Recommended & Forward to DDG'], $commonEnd);
                    else
                        $workflowSteps = array_merge($commonStart, ['Recommended & Forward to DG'], $commonEnd);

                    $activeIndex = array_search($currentStatus, $workflowSteps) ?: 0;
                    $purchaseHistory = $purchase->history ?? collect([]);
                @endphp

                <div class="timeline-vertical-container">
                    @foreach($workflowSteps as $index => $stepLabel)
                        @php
                            $historyDate = null;
                            $hEntry = $purchaseHistory
                                ->filter(fn($h) => stripos($h->status ?? '', $stepLabel) !== false)
                                ->sortByDesc('created_at')
                                ->first();

                            if($hEntry)
                                $historyDate = \Carbon\Carbon::parse($hEntry->created_at)->format('d M Y');

                            $stateClass = 'pending';
                            $statusText = '-';
                            $icon = '<i class="fas fa-circle" style="font-size: 8px;"></i>';

                            if ($index <= $activeIndex) {
                                $stateClass = 'completed';
                                $statusText = $historyDate ?: 'Completed';
                                $icon = '<i class="fas fa-check"></i>';
                            } elseif ($index == $activeIndex + 1) {
                                $stateClass = 'active';
                                $statusText = 'Processing';
                                $icon = '<i class="fas fa-spinner fa-spin"></i>';
                            }
                        @endphp

                        <div class="stepper-item {{ $stateClass }}">
                            <div class="stepper-icon">{!! $icon !!}</div>
                            <div class="stepper-content">
                                <div class="stepper-label">{{ $stepLabel }}</div>
                                <div class="stepper-date">{{ $statusText }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
@endif


        <!-- COLUMN 2: CASE DETAILS & QUOTATIONS -->
        <div class="left-container">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="date-grid">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row mb-3">
                                    <div class="col-6"><label>Case ID</label><input type="text" class="form-control" value="PC-{{ $purchase->pcs_id }}" readonly></div>
                                    <div class="col-6"><label>Date</label><input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}" readonly></div>
                                </div>
                                @php $lowestQuote = $purchase->quotes->sortBy('qte_price')->first(); @endphp
                                <div class="form-group mb-3">
                                    <label>Firm Name (L1)</label>
                                    <input type="text" class="form-control font-weight-bold" value="{{ $lowestQuote ? ($lowestQuote->firm->frm_name ?? $lowestQuote->qte_firmname) : 'Waiting...' }}" readonly style="border-left: 4px solid #28a745;">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Terms</label>
                                    <textarea class="form-control" rows="1">{{ $purchase->pcs_remarks ?? 'No Terms Found ...' }}</textarea>
                                </div> 
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-3">
                                    <div class="col-6"><label>Head</label><input type="text" class="form-control" value="{{ $purchase->project->prj_code ?? 'N/A' }}" readonly></div>
                                    
                                    <div class="col-6"><label>Status</label><input type="text" class="form-control font-weight-bold text-success" value="{{ $purchase->pcs_status }}" readonly></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-6"><label>GST</label><input type="text" class="form-control text-right text-danger" value="{{ number_format($purchase->pcs_midtax ?? 0, 2) }}" readonly></div>
                                    <div class="col-6"><label>SST</label><input type="text" class="form-control text-right text-danger" value="{{ number_format($purchase->pcs_inttax ?? 0, 2) }}" readonly></div>
                                    <div class="col-6 mt-2"><label>Sub Total</label><input type="text" class="form-control text-right" value="{{ number_format($purchase->pcs_midprice ?? 0, 2) }}" readonly></div>
                                    <div class="col-6 mt-2"><label>Final Total</label><input type="text" class="form-control text-right font-weight-bold text-primary" value="{{ number_format($purchase->pcs_price ?? 0, 2) }}" readonly style="background-color:#eef3ff;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Quotations Section -->
                        <div class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="text-primary mb-0 font-weight-bold"><i class="fas fa-list-ol mr-1"></i> Received Quotations</label>
                                <div>
                                    <button class="btn btn-xs btn-outline-primary unlock" data-toggle="modal" data-target="#detailedCSModal"><i class="fas fa-medal"></i> Comparative Statement</button>
                                    <button class="btn btn-xs btn-outline-primary" data-toggle="modal" data-target="#addQuoteModal"><i class="fas fa-plus"></i> Add</button>
                                </div>
                            </div>
                            <div class="table-responsive border rounded">
                                <table class="readable-table table-hover mb-0">
                                    <thead>
                                        <tr><th>#</th><th>FIRM NAME</th><th class="text-right">PRICE</th><th class="text-center">TECH.</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchase->quotes as $quote)
                                        <tr>
                                            <td>{{ $quote->qte_num }}</td>
                                            <td class="font-weight-bold">{{ $quote->firm->frm_name ?? $quote->qte_firmname }}</td>
                                            <td class="text-right text-primary font-weight-bold">{{ number_format($quote->qte_price, 2) }}</td>
                                            <td class="text-center"><span class="badge badge-success">Yes</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMN 3: ITEMS SUMMARY -->
        <div class="right-container">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="mb-0 text-secondary font-weight-bold"><i class="fas fa-boxes mr-1"></i> Items Summary</label>
                        <div>
                            <button class="btn btn-xs btn-outline-primary unlock" data-toggle="modal" data-target="#viewItemsModal">Details</button>
                            <button class="btn btn-xs btn-outline-primary" data-toggle="modal" data-target="#addItemModal"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div class="table-responsive border rounded grow" style="max-height: 600px; overflow-y: auto;">
                        <table class="readable-table table-striped mb-0">
                            <thead>
                                <tr><th>#</th><th>DESC</th><th>QTY</th><th class="text-right">TOTAL</th></tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                <tr>
                                    <td>{{ $item->pci_serial }}</td>
                                    <td class="small">{{ Str::limit($item->pci_desc, 30) }}</td>
                                    <td>{{ $item->pci_qty }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($item->pci_qty * $item->pci_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                   <!-- FOOTER ACTIONS -->
<div class="mt-auto pt-4 text-right">
    <!-- Back Button (always visible) -->
    <button onclick="history.back()" class="btn btn-outline-secondary mr-2 unlock">Back</button>

    <!-- Release Case Form (only if Draft) -->
    @if(trim($purchase->pcs_status) === 'Draft')
        <form action="{{ route('purchase.release', $purchase->pcs_id) }}" method="POST" style="display: inline-block;">
            @csrf
            <button type="submit" class="btn btn-primary px-4 shadow" onclick="return confirm('Are you sure you want to release this case for scrutiny?')">
                <i class="fas fa-paper-plane mr-1"></i> Release Case
            </button>
        </form>
    @endif
</div>

                </div>
            </div>
        </div>

    </div>
</div>
<!-- ========================= MODALS AREA ========================= -->

<!-- Quote Entry Modal (Redesigned) -->
<div class="modal fade" id="addQuoteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-sm rounded-lg">
            <!-- Modal Header -->
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title font-weight-bold">Quote Entry</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-0">
                <div class="row no-gutters">

                    <!-- Left Sidebar: Firm Selection -->
                    <div class="col-md-3 border-right p-4 bg-light">
                        <label class="font-weight-bold mb-2">Select Firm</label>
                        <div class="firm-scroll-box mb-3" style="max-height: 300px; overflow-y:auto;">
                            <ul class="list-group list-group-flush small" id="firm-list">
                                @foreach($firms as $firm)
                                <li class="list-group-item list-group-item-action py-2 firm-select-item"
                                    style="cursor:pointer;" data-id="{{ $firm->id }}">
                                    {{ $firm->name }}
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm btn-block mb-3" data-toggle="modal" data-target="#newFirmModal">
                            + New Firm
                        </button>

                        <input type="hidden" id="selected_firm_id">

                        <div class="form-group mb-2">
                            <label class="small text-muted">Quote Number</label>
                            <input type="text" id="quote_number" class="form-control form-control-sm">
                        </div>

                        <div class="form-group">
                            <label class="small text-muted">Acceptable</label>
                            <select id="quote_tech_accept" class="form-control form-control-sm">
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right Side: Items & Calculation -->
                    <div class="col-md-9 p-4 d-flex flex-column">
                        <div class="table-responsive" style="max-height:350px; overflow-y:auto;">
                            <table class="table table-sm table-hover table-bordered">
    <thead class="thead-light small">
        <tr>
            <th style="width:50px;">#</th>
            <th>Description</th>
            <th style="width:60px;">Qty</th>
            <th style="width:120px;">Unit Price</th>
            <th style="width:120px;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchase->items as $idx => $item)
        <tr class="quote-item-row" 
            data-pci-id="{{ $item->pci_id }}" 
            data-type="{{ in_array((int)$item->pci_type,[7,2])?'goods':'service' }}">
            <td class="text-center align-middle">{{ $idx+1 }}</td>
            <td class="small align-middle" style="white-space: nowrap;">{{ $item->pci_desc }}</td>
            <td class="text-center align-middle qty-val" style="white-space: nowrap;">{{ $item->pci_qty }}</td>
            <td class="text-right align-middle" style="white-space: nowrap;">
                <input type="number" 
                       class="form-control form-control-sm border-0 bg-light text-right quote-price-input"
                       value="{{ isset($item->pci_price)?number_format($item->pci_price,2,'.',''):'' }}" 
                       step="0.01">
            </td>
            <td class="text-right font-weight-bold align-middle row-total" style="white-space: nowrap;">{{ number_format($item->pci_qty * $item->pci_price,2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

                        </div>

                        <!-- Totals Section -->
                        <div class="mt-auto border-top pt-3">
                            <div class="row justify-content-end">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="small text-muted font-weight-bold">Sub-Total:</span>
                                        <span id="modal-subtotal" class="font-weight-bold">0.00</span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2 align-items-center">
                                        <span class="small text-muted">GST Amount:</span>
                                        <input type="hidden" id="gst-rate" value="18">
                                        <input type="number" id="input-gst" class="form-control form-control-sm text-right"
                                               style="width: 120px; border: 1px dashed #6c757d;" value="0.00" step="0.01" readonly>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2 align-items-center">
                                        <span class="small text-muted">SST Amount:</span>
                                        <input type="number" id="input-sst" class="form-control form-control-sm text-right"
                                               style="width: 120px; border: 1px dashed #6c757d;" value="0.00" step="0.01">
                                    </div>

                                    <div class="d-flex justify-content-between border-top pt-2">
                                        <span class="h5 font-weight-bold">Final Price:</span>
                                        <span class="h5 font-weight-bold" id="modal-final-total">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-primary px-4" id="btn-save-quote">Save Quote</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Modal: Detailed Comparative Statement (Dynamic Table with L1 Highlight) -->
<div class="modal fade" id="detailedCSModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" style="border-top: 5px solid #007bff; border-radius: 8px;">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-balance-scale mr-2 text-primary"></i> Detailed Item-wise Comparison
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center readable-table" style="font-size: 13px;">
                        <thead class="bg-light">
                            @php 
                                // Sort quotes by price so the lowest (L1) is always the first column
                                $sortedQ = $purchase->quotes->sortBy('qte_price'); 
                            @endphp
                            <tr>
                                <th rowspan="2" class="align-middle" style="width: 40px;">#</th>
                                <th rowspan="2" class="align-middle" style="width: 250px;">Item Description</th>
                                <th rowspan="2" class="align-middle" style="width: 50px;">Qty</th>
                                
                                @foreach($sortedQ as $index => $q) 
                                    <th colspan="2" class="align-middle {{ $loop->first ? 'bg-l1-header text-white' : 'text-primary' }}">
                                        {{ $q->firm->frm_name ?? $q->qte_firmname }}
                                        @if($loop->first) 
                                            <br><span class="badge badge-light text-primary">WINNER</span> 
                                        @else
                                            <br><span class="badge badge-secondary">L{{ $loop->iteration }}</span>
                                        @endif
                                    </th> 
                                @endforeach
                            </tr>
                            <tr class="small font-weight-bold">
                                @foreach($sortedQ as $q) 
                                    <th class="{{ $loop->first ? 'bg-l1-highlight' : '' }}">Unit Price</th>
                                    <th class="{{ $loop->first ? 'bg-l1-highlight' : '' }}">Total</th> 
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $item)
                            <tr>
                                <td class="bg-light">{{ $item->pci_serial }}</td>
                                <td class="text-left item-description-cell" style="font-size: 11px;">{{ $item->pci_desc }}</td>
                                <td class="font-weight-bold">{{ $item->pci_qty }}</td>
                                
                                @foreach($sortedQ as $q)
                                    @php 
                                        $price = \DB::table('pur.quoteitems')
                                                ->where('qti_qte_id', $q->qte_id)
                                                ->where('qti_pci_id', $item->pci_id)
                                                ->value('qti_price') ?? 0; 
                                    @endphp
                                    {{-- Highlight the L1 columns --}}
                                    <td class="{{ $loop->first ? 'bg-l1-highlight font-weight-bold' : '' }}">
                                        {{ $price > 0 ? number_format($price, 2) : '-' }}
                                    </td>
                                    <td class="{{ $loop->first ? 'bg-l1-highlight font-weight-bold' : '' }}">
                                        {{ $price > 0 ? number_format($price * $item->pci_qty, 2) : '-' }}
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light font-weight-bold">
                            <tr>
                                <td colspan="3" class="text-right py-2">GRAND TOTAL (PKR)</td>
                                @foreach($sortedQ as $q)
                                    <td colspan="2" class="py-2 {{ $loop->first ? 'bg-l1-highlight text-primary' : '' }}" style="font-size: 15px;">
                                        {{ number_format($q->qte_price, 2) }}
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-1">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Professional Light Blue Highlight for L1 Vendor */
    .bg-l1-highlight {
        background-color: rgba(0, 123, 255, 0.08) !important; /* Very light primary blue */
        border-left: 0.5px solid rgba(0, 123, 255, 0.2) !important;
        border-right: 0.5px solid rgba(0, 123, 255, 0.2) !important;
    }
    .bg-l1-header {
        background-color: #007bff !important;
    }
    /* Description cell wrap fix */
    .item-description-cell {
        white-space: normal !important;
        min-width: 200px;
        word-break: break-word;
    }
</style>
<!-- 4. Modal: Full Item Details (9 Columns) -->
<div class="modal fade" id="viewItemsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="border-top: 5px solid #007bff;">
            <div class="modal-header bg-light py-2"><h5 class="modal-title font-weight-bold">Full Case Items</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body">
                <table class="readable-table table-bordered">
                    <thead><tr><th>#</th><th>Description</th><th>Qty</th><th>Unit</th><th>Price</th><th>Total</th><th>Balance</th><th>Type</th><th>Subtype</th></tr></thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                        <tr><td>{{ $item->pci_serial }}</td><td class="item-description-cell">{{ $item->pci_desc }}</td><td>{{ $item->pci_qty }}</td><td>{{ $item->pci_qtyunit }}</td><td>{{ number_format($item->pci_price, 2) }}</td><td class="font-weight-bold">{{ number_format($item->pci_qty * $item->pci_price, 2) }}</td><td>{{ $item->pci_id }}</td><td>Permanent</td><td>Parts</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 5. Modal: Add New Firm (Nested) -->
<div class="modal fade" id="newFirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content shadow-lg" style="border-top: 4px solid #6c757d;"><div class="modal-header bg-light py-1"><h6>New Firm</h6><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><input type="text" class="form-control form-control-sm" placeholder="Firm Name"></div><div class="modal-footer py-1"><button class="btn btn-sm btn-primary">Save</button></div></div></div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-sm" style="border-top:4px solid #0d6efd;">

            <!-- Header -->
            <div class="modal-header py-2 bg-light">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle text-primary mr-1"></i>
                    Add Item Entry
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <!-- Row 1 -->
                <div class="row mb-3">
                    <div class="col-3">
                        <label class="small font-weight-bold">S.No</label>
                        <input type="text" class="form-control form-control-sm">
                    </div>

                    <div class="col-9">
                        <label class="small font-weight-bold">Description</label>
                        <input type="text" class="form-control form-control-sm">
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="row mb-3">
                    <div class="col-3">
                        <label class="small font-weight-bold">Quantity</label>
                        <input type="number" class="form-control form-control-sm">
                    </div>

                    <div class="col-3">
                        <label class="small font-weight-bold">Unit Price</label>
                        <input type="text" class="form-control form-control-sm">
                    </div>

                    <div class="col-3">
                        <label class="small font-weight-bold">Type</label>
                        <select class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option>Permanent</option>
                            <option>Consumable</option>
                        </select>
                    </div>

                    <div class="col-3">
                        <label class="small font-weight-bold">Sub Head</label>
                        <select class="form-control form-control-sm">
                            <option value="">Select</option>
                            <option>Equipment</option>
                            <option>Service</option>
                        </select>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer py-2">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-check mr-1"></i> Add to List
                </button>
            </div>

        </div>
    </div>
</div>


<!-- 7. Modal: Case Attachments -->
<div class="modal fade" id="caseAttachmentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content" style="border-top: 5px solid #007bff;">
            
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-paperclip mr-2 text-primary"></i> Documents
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body p-0">
                
                <!-- List of Existing Attachments -->
                <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                    @forelse($purchase->attachments as $file)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                @php
                                    $ext = strtolower(pathinfo($file->pat_path, PATHINFO_EXTENSION));
                                    $icon = match($ext) {
                                        'pdf' => 'fa-file-pdf text-danger',
                                        'jpg', 'jpeg', 'png' => 'fa-file-image text-primary',
                                        'doc', 'docx' => 'fa-file-word text-info',
                                        'xls', 'xlsx' => 'fa-file-excel text-success',
                                        default => 'fa-file text-secondary'
                                    };
                                @endphp
                                <i class="fas {{ $icon }} fa-lg mr-3"></i>
                                <div>
                                    <h6 class="mb-0" style="font-size: 13px; font-weight: 600;">
                                        <a href="{{ asset('storage/' . $file->pat_path) }}" target="_blank" class="text-dark">
                                            {{ basename($file->pat_path) }}
                                        </a>
                                    </h6>
                                    <small class="text-muted text-uppercase">{{ $file->pat_type }}</small>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $file->pat_path) }}" target="_blank" class="btn btn-sm btn-light border">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-gray-300"></i><br>
                            No documents uploaded yet.
                        </div>
                    @endforelse
                </div>

                <!-- Upload New Section -->
<div class="p-3 border-top bg-light">
    <!-- FORM START -->
    @if(trim($purchase->pcs_status) === 'Draft')
        <form action="{{ route('purchase.upload') }}"    method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <input type="hidden" name="pcs_id" value="{{ $purchase->pcs_id }}">

            <div class="border-dashed p-4 text-center bg-white position-relative" 
                 style="border: 2px dashed #007bff; border-radius: 6px; cursor: pointer; transition: 0.3s;"
                 onmouseover="this.style.backgroundColor='#f0f8ff'" 
                 onmouseout="this.style.backgroundColor='white'">
                
                <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                <p class="mb-0 text-dark font-weight-bold small">Click to Upload File</p>
                <span class="text-muted" style="font-size: 10px;">(PDF, JPG, PNG, DOCX)</span>

                <!-- File Input (Invisible but covers the area) -->
                <input type="file" name="file" required
                       style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;"
                       onchange="document.getElementById('uploadForm').submit();">
            </div>
        </form>
    @endif
    <!-- FORM END -->
</div>


            </div>
        </div>
    </div>
</div>



<!-- 8. Modal: Add Not Received -->
<div class="modal fade" id="addNotReceivedModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered"><div class="modal-content" style="border-top: 5px solid #007bff;"><div class="modal-header py-2"><h5>Non-Responsive Vendor</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body p-3"><label>Firm Name</label><input type="text" class="form-control"><label class="mt-2">Reason</label><textarea class="form-control"></textarea></div><div class="modal-footer py-1"><button class="btn btn-primary btn-sm">Add</button></div></div></div>
</div>

<!-- SCRIPTS -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ----- FIXED STACKED MODAL LOGIC -----
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', () => {
            if (document.querySelectorAll('.modal.show').length > 0) {
                document.body.classList.add('modal-open');
            }
        });
    });

    // ----- Auto Signature Date -----
    const now = new Date();
    const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    const sigDateEl = document.getElementById('sig-date-display');
    if(sigDateEl) {
        sigDateEl.innerHTML = `&nbsp;&nbsp; ${months[now.getMonth()]} ${now.getFullYear().toString().substr(-2)}`;
    }

    // ----- Quote Calculation Logic -----
    function updateModalTotals() {
        let subtotal = 0;
        let totalGst = 0;
        let totalSst = 0;

        document.querySelectorAll('.quote-item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-val').textContent) || 0;
            const price = parseFloat(row.querySelector('.quote-price-input').value) || 0;
            const type = row.dataset.type;
            const lineTotal = qty * price;

            // Update row total
            row.querySelector('.row-total').textContent = lineTotal.toLocaleString(undefined,{minimumFractionDigits:2});

            subtotal += lineTotal;

            if(type === 'goods') totalGst += lineTotal * 0.18;
            else totalSst += lineTotal * 0.13;
        });

        // Update Subtotal
        const subtotalEl = document.getElementById('modal-subtotal');
        if(subtotalEl) subtotalEl.textContent = subtotal.toLocaleString(undefined,{minimumFractionDigits:2});

        // Update GST & SST
        const gstEl = document.getElementById('input-gst');
        const sstEl = document.getElementById('input-sst');
        if(gstEl) gstEl.value = totalGst.toFixed(2);
        if(sstEl) sstEl.value = totalSst.toFixed(2);

        // Final total
        const finalTotalEl = document.getElementById('modal-final-total');
        if(finalTotalEl) finalTotalEl.textContent = (subtotal + totalGst + totalSst).toLocaleString(undefined,{minimumFractionDigits:2});
    }

    // Attach event listeners
    document.querySelectorAll('.quote-price-input').forEach(input => {
        input.addEventListener('input', updateModalTotals);
    });

    ['input-gst','input-sst'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.addEventListener('input', updateModalTotals);
    });

    // Initial calculation on page load
    updateModalTotals();
});
</script>
@endsection