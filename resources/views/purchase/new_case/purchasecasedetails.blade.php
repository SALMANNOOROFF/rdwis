@extends('welcome')

@section('content')
<div class="content-wrapper bg-white">
    <style>
        /* Global Typography & Theme */
        .card-body, .modal-body, .signature-block, label, input, select, textarea {
            font-family: Arial, sans-serif !important;
            font-size: 12pt !important;
        }
        .card { border-top: 3px solid #007bff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-radius: 8px; margin: 20px; }
        .card-header { background-color: #f8f9fa; border-bottom: 1px solid #eee; padding: 15px 20px; }
        
        /* Grids */
        .top-info-grid { display: grid; grid-template-columns: 1fr 1fr 2fr; gap: 15px; margin-bottom: 15px; align-items: stretch; }
        .quotation-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px; align-items: stretch; }
        .date-grid { display: flex; flex-direction: column; background: #fdfdfd; padding: 15px; border: 1px solid #ebedf2; border-radius: 8px; height: 100%; overflow: hidden; }
        .section-header { border-left: 4px solid #007bff; padding: 8px 12px; margin-bottom: 15px; background: #f8f9fa; border-radius: 0 4px 4px 0; display: flex; justify-content: space-between; align-items: center; }
        
        /* Table Formatting */
        .readable-table { width: 100%; border-collapse: collapse; }
        .readable-table th { background: #f4f6f9; padding: 10px; text-transform: uppercase; border-bottom: 2px solid #dee2e6; font-size: 0.75rem; color: #666; white-space: nowrap; }
        .readable-table td { padding: 8px 10px; vertical-align: top; border-bottom: 1px solid #eee; color: #333; line-height: 1.4; }
        .item-description-cell { white-space: normal !important; min-width: 250px; word-break: break-word; }
        .col-small { white-space: nowrap; width: 1%; }

        .signature-block { margin-top: 40px; float: right; text-align: center; font-weight: bold; line-height: 1.3; min-width: 180px; }
        .firm-scroll-box { height: 250px; overflow-y: auto; border: 1px solid #ced4da; background: #fff; border-radius: 4px; }
        .bg-success-light { background-color: rgba(40, 167, 69, 0.1) !important; border: 1px solid #28a745 !important; }

        /* NESTED MODAL FIXES */
        .modal { overflow-y: auto !important; }
        .modal-backdrop.show:nth-of-type(even) { z-index: 1059 !important; }
        #newFirmModal, #detailedCSModal { z-index: 1061 !important; }

        .form-actions { background: #fff; padding: 20px; border-top: 1px solid #dee2e6; text-align: right; margin-top: 20px; }
        @media (max-width: 1400px) { .top-info-grid { grid-template-columns: 1fr 1fr; } }
    </style>

    <div class="card">
        <div class="card-header text-right">
            <h3 class="card-title float-left"><i class="fas fa-file-invoice-dollar mr-2 text-primary"></i> Purchase Case Details</h3>
            <button class="btn btn-secondary btn-sm">Adv. Payment</button>
            <a href="{{ route('minutesheet') }}" class="btn btn-secondary btn-sm text-white">Minute</a>
            <button class="btn btn-outline-primary btn-sm"><i class="fas fa-print"></i></button>
        </div>

<div class="card-body">
            <!-- Case Title -->
            <div class="form-group mb-4">
                <label><i class="fas fa-tag mr-1 text-primary"></i> Case Title</label>
                <div class="d-flex align-items-center">
                    <input type="text" class="form-control form-control-lg" value="{{ $purchase->pcs_title }}" readonly style="border-left: 4px solid #007bff; font-weight: 600; flex-grow: 1;">
                    <button class="btn btn-primary btn-lg ml-2 shadow-sm" type="button" data-toggle="modal" data-target="#caseAttachmentModal" style="white-space: nowrap; border-radius: 8px; font-size: 14px;">
                        <i class="fas fa-paperclip mr-1"></i> Attachments
                    </button>
                </div>
            </div>

            <div class="top-info-grid">
                <!-- Core Info & Financials -->
                <div class="date-grid" style="grid-column: span 2;">
                    <div class="row gx-2 gy-2">
                      <!-- Left Column -->
        <div class="col-md-6 border-end pe-2">
            
            <!-- Row 1: ID & Date -->
            <div class="row gx-2 gy-2">
                <div class="col-6">
                    <label>Case ID</label>
                    <input type="text" class="form-control" value="PC-{{ $purchase->pcs_id }}" readonly>
                </div>
                <div class="col-6">
                    <label>Date</label>
                    <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}" readonly>
                </div>
            </div>

            <!-- Row 2: Firm Name -->
            @php $lowestQuote = $purchase->quotes->sortBy('qte_price')->first(); @endphp
            <div class="form-group mt-2">
                <label>Firm Name (L1)</label>
                <input type="text" class="form-control" 
                       value="{{ $lowestQuote ? ($lowestQuote->firm->frm_name ?? $lowestQuote->qte_firmname) : 'Waiting for Quotes...' }}" 
                       readonly style="border-left: 4px solid #28a745; font-weight: bold;">
            </div>

            <!-- Row 3: Terms -->
            <label>Terms</label>
            <textarea class="form-control" rows="2" readonly>{{ $purchase->pcs_remarks ?? 'Standard terms apply.' }}</textarea>



        </div>

                        <!-- Right Column -->
                        <div class="col-md-6 ps-2">
                            <div class="row gx-2 gy-2">
                                <div class="col-6"><label>Head</label><input type="text" class="form-control" value="{{ $purchase->pcs_hed_id }}" readonly></div>
                                <div class="col-6"><label>Status</label><input type="text" class="form-control font-weight-bold" value="{{ $purchase->pcs_status }}" readonly style="color: #28a745;"></div>
                                @php
                                    $taxLabel = ''; $taxValue = 0;
                                    if(!empty($purchase->pcs_midtax) && $purchase->pcs_midtax > 0) { $taxLabel = 'SST'; $taxValue = $purchase->pcs_midtax; } 
                                    elseif(!empty($purchase->pcs_inttax) && $purchase->pcs_inttax > 0) { $taxLabel = 'GST'; $taxValue = $purchase->pcs_inttax; }
                                @endphp
                                <div class="col-6"><label>{{ $taxLabel }}</label><input type="text" class="form-control text-end" value="{{ number_format($taxValue, 2) }}" readonly></div>
                                <div class="col-6"><label>Total Price (PKR)</label><input type="text" class="form-control text-end font-weight-bold text-primary" value="{{ number_format($purchase->pcs_price, 2) }}" readonly></div>
                            </div>
                                        <!-- 3. Timeline Section -->
    <div class="timeline-container p-3 rounded" style="background-color: #f8f9fa; border: 1px solid #e9ecef; margin-top: 20px;">
        <label class="text-muted mb-2" style="font-size: 11px; letter-spacing: 0.5px; text-transform: uppercase;">
            <i class="fas fa-history mr-1"></i> Case Progress
        </label>
        
        <div class="timeline-wrapper">
            @php
                // 1. Define Linear Stages (The Ideal Path)
                $timelineSteps = [
                    0 => ['label' => 'Draft',          'date' => $purchase->pcs_date],
                    1 => ['label' => 'Under Scrutiny', 'date' => null], // Add logic for specific dates if available
                    2 => ['label' => 'Approved',       'date' => null],
                    3 => ['label' => 'Fulfilled',      'date' => null]
                ];

                // 2. Get Current Status from Database
                $currentStatus = trim($purchase->pcs_status); 

                // 3. Map Database Status to Timeline Index
                $statusMapping = [
                    'Draft'          => 0,
                    'Under Scrutiny' => 1,
                    'Approved'       => 2,
                    'Fulfilled'      => 3,
                    'Cancelled'      => -1 // Special Case
                ];

                // Get active index (Default to 0 if unknown)
                $activeIndex = $statusMapping[$currentStatus] ?? 0;
            @endphp

            @foreach($timelineSteps as $index => $step)
                @php
                    $barColor = 'bg-secondary'; // Default (Future/Gray)
                    $textColor = '#888';

                    if ($activeIndex === -1) {
                        // CASE: CANCELLED
                        // Sirf pehla step Red karein ya sab Gray
                        $barColor = ($index === 0) ? 'bg-danger' : 'bg-secondary';
                        $textColor = ($index === 0) ? '#dc3545' : '#888'; // Red text for first step
                    } else {
                        // NORMAL FLOW
                        if ($index < $activeIndex) {
                            $barColor = 'bg-success'; // Past (Green)
                            $textColor = '#28a745';
                        } elseif ($index == $activeIndex) {
                            // Agar Fulfilled (Last Step) hai to Green, warna Blue (Current)
                            $barColor = ($activeIndex == 3) ? 'bg-success' : 'bg-primary';
                            $textColor = ($activeIndex == 3) ? '#28a745' : '#007bff';
                        }
                    }
                @endphp

                <div class="timeline-item">
                    <!-- Progress Bar -->
                    <div class="progress-segment {{ $barColor }}"></div>
                    
                    <!-- Label -->
                    <div class="meta-text" style="color: {{ $textColor }}; font-weight: 600;">
                        {{ $step['label'] }}
                    </div>

                    <!-- Date (Only for first step or logic based) -->
                    @if($index == 0 && $step['date'])
                        <div class="meta-date">({{ \Carbon\Carbon::parse($step['date'])->format('d M') }})</div>
                    @endif
                </div>
            @endforeach
        </div>
        
        {{-- Show Cancelled Message Explicitly if Status is Cancelled --}}
        @if($activeIndex === -1)
            <div class="text-center mt-2">
                <span class="badge badge-danger">CASE CANCELLED</span>
            </div>
        @endif
    </div>
    <!-- End Timeline -->
                        </div>
                    </div>
                </div>

                <!-- Items Summary -->
                <div class="date-grid">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="mb-0">Items Summary</label>
                        <div class="d-flex" style="gap: 5px;">
                            <button class="btn btn-outline-primary btn-sm"  data-toggle="modal" data-target="#viewItemsModal">See Details</button>
                            <button class="btn btn-outline-primary btn-sm"  data-toggle="modal" data-target="#addItemModal"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <div style="overflow-x: auto; flex-grow: 1;">
                        <table class="readable-table">
                            <thead><tr><th class="col-small">#</th><th>Desc</th><th class="col-small">Qty</th><th class="col-small text-right">Price</th></tr></thead>
                            <tbody>
                                @foreach($purchase->items as $item)
                                <tr><td>{{ $item->pci_serial }}</td><td class="item-description-cell small">{{ $item->pci_desc }}</td><td>{{ $item->pci_qty }}</td><td class="text-right font-weight-bold">{{ number_format($item->pci_qty * $item->pci_price, 2) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Quotations Grid -->
            <div class="quotation-info-grid">
                <div class="date-grid">
                    <div class="section-header">
                        <div class="d-flex align-items-center"><h4>Received Quotations</h4><select class="form-control form-control-sm ml-2" style="width: 110px;"><option>With Tax</option></select></div>
                        <div class="d-flex" style="gap: 5px;">
                            <button class="btn btn-outline-success btn-sm"  data-toggle="modal" data-target="#bestPricingModal"><i class="fas fa-medal"></i> Best Price</button>
                            <button class="btn btn-outline-primary btn-sm"  data-toggle="modal" data-target="#addQuoteModal"><i class="fas fa-plus"></i> Add Quote</button>
                        </div>
                    </div>
                    <table class="readable-table">
                        <thead><tr><th>Number</th><th>Firm Name</th><th class="text-right">Price</th><th>Tech.</th></tr></thead>
                        <tbody>
                            @foreach($purchase->quotes as $quote)
                            <tr><td>{{ $quote->qte_num }}</td><td class="font-weight-bold">{{ $quote->firm->frm_name ?? $quote->qte_firmname }}</td><td class="text-right text-primary font-weight-bold">{{ number_format($quote->qte_price, 2) }}</td><td><span class="badge bg-success">Yes</span></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> 
            </div>

            <div class="form-actions">
<button onclick="history.back()" class="btn btn-primary">Back</button>
                <button type="submit" class="btn btn-primary">Release Case</button>
            </div>
        </div>  
    </div>
</div>

<!-- ========================= MODALS AREA ========================= -->

<!-- Quote Entry Modal with Editable Tax Inputs -->
<div class="modal fade" id="addQuoteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" style="border-top: 5px solid #007bff;">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title font-weight-bold">Quote Entry</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <div class="row no-gutters">
                    <!-- Left Sidebar (Unchanged) -->
                    <div class="col-md-3 border-right p-3 bg-light">
                        <label class="text-primary font-weight-bold">Select Firm</label>
                        <div class="firm-scroll-box mb-2">
                            <ul class="list-group list-group-flush small">
                                @foreach($firms as $firm) 
                                    <li class="list-group-item list-group-item-action py-1 border-bottom firm-select-item" data-id="{{ $firm->id }}" data-name="{{ $firm->name }}">
                                        {{ $firm->name }}
                                    </li> 
                                @endforeach
                            </ul>
                        </div>
                        <button class="btn btn-xs btn-outline-secondary btn-block mb-3" data-toggle="modal" data-target="#newFirmModal">New Firm</button>
                        <div class="form-group mb-2"><label class="small text-muted">Number</label><input type="text" class="form-control form-control-sm"></div>
                        <div class="form-group"><label class="small text-muted">Acceptable</label><select class="form-control form-control-sm"><option>Yes</option><option>No</option></select></div>
                    </div>

                    <!-- Right Side: Items & Manual Tax Inputs -->
                    <div class="col-md-9 p-3 d-flex flex-column">
                        <div class="table-responsive" style="min-height: 350px;">
                            <table class="table table-sm table-bordered">
                                <thead class="bg-light small">
                                    <tr>
                                        <th style="width: 50px;">Ser</th>
                                        <th>Description</th>
                                        <th style="width: 60px;">Qty</th>
                                        <th style="width: 120px;">Unit Price</th>
                                        <th style="width: 120px;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->items as $idx => $item)
                                    <tr class="quote-item-row" data-type="{{ in_array((int)$item->pci_type, [7, 2]) ? 'goods' : 'service' }}">
                                        <td class="text-center">{{ $idx+1 }}</td>
                                        <td class="small">{{ $item->pci_desc }}</td>
                                        <td class="text-center qty-val">{{ $item->pci_qty }}</td>
                                        <td><input type="number" class="form-control form-control-sm text-right border-0 bg-light quote-price-input" placeholder="0.00" step="0.01"></td>
                                        <td class="text-right font-weight-bold row-total">0.00</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Manual Tax & Totals Section -->
                        <div class="mt-auto border-top pt-3">
                            <div class="row">
                                <div class="col-md-6 offset-md-6">
                                    <!-- Subtotal -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small font-weight-bold text-muted">Sub-Total (Excl. Tax):</span>
                                        <span id="modal-subtotal" class="font-weight-bold">0.00</span>
                                    </div>
                                    
                                    <!-- Editable GST Input -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small text-primary">GST Amount (Manual/Auto):</span>
                                        <input type="number" id="input-gst" class="form-control form-control-sm text-right font-weight-bold text-primary" style="width: 120px; border: 1px dashed #007bff;" value="0.00" step="0.01">
                                    </div>

                                    <!-- Editable SST Input -->
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="small text-info">SST Amount (Manual/Auto):</span>
                                        <input type="number" id="input-sst" class="form-control form-control-sm text-right font-weight-bold text-info" style="width: 120px; border: 1px dashed #17a2b8;" value="0.00" step="0.01">
                                    </div>

                                    <!-- Final Grand Total -->
                                    <div class="d-flex justify-content-between align-items-center py-2 border-top">
                                        <span class="h5 font-weight-bold">Final Quoted Price:</span>
                                        <span class="h5 font-weight-bold text-primary" id="modal-final-total">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-primary px-4 shadow">Save Quote</button></div>
        </div>
    </div>
</div>

<!-- 2. Modal: Best Pricing Analysis -->
<div class="modal fade" id="bestPricingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="border-top: 5px solid #28a745;">
            <div class="modal-header bg-light py-2"><h6 class="modal-title font-weight-bold text-success"><i class="fas fa-medal"></i> Best Price Analysis</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <div class="modal-body p-0">
                <table class="table table-sm table-bordered text-center mb-0">
                    <thead class="bg-light"><tr><th>Rank</th><th>Vendor</th><th>Total Price</th></tr></thead>
                    <tbody>
                        @php $sorted = $purchase->quotes->sortBy('qte_price'); @endphp
                        @foreach($sorted as $q)
                        <tr @if($loop->first) style="background-color: #d4edda;" @endif>
                            <td>L{{ $loop->iteration }}</td><td class="text-left">{{ $q->firm->frm_name ?? $q->qte_firmname }}</td><td class="font-weight-bold">{{ number_format($q->qte_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer py-1"><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#detailedCSModal">View Detailed Comparative</button></div>
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
                                            <br><span class="badge badge-light text-primary">L1 WINNER</span> 
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
                <button type="button" class="btn btn-primary btn-sm"><i class="fas fa-file-pdf mr-1"></i> Export CS</button>
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

<!-- 6. Modal: Add Item Entry -->
<div class="modal fade" id="addItemModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg"><div class="modal-content" style="border-top: 5px solid #6c757d;"><div class="modal-header bg-light py-2"><h5>Item Entry</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body p-3"><div class="row mb-2"><div class="col-3"><label>S No</label><input type="text" class="form-control"></div><div class="col-9"><label>Description</label><input type="text" class="form-control"></div></div><div class="row"><div class="col-3"><label>Qty</label><input type="number" class="form-control"></div><div class="col-3"><label>Price</label><input type="text" class="form-control"></div><div class="col-3"><label>Type</label><select class="form-control"><option>Permanent</option></select></div><div class="col-3"><label>Subhead</label><select class="form-control"><option>Equipment</option></select></div></div></div><div class="modal-footer"><button class="btn btn-primary btn-sm">Add to List</button></div></div></div>
</div>

<!-- 7. Modal: Case Attachments -->
<div class="modal fade" id="caseAttachmentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md"><div class="modal-content" style="border-top: 5px solid #007bff;"><div class="modal-header"><h5>Documents</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body text-center"><div class="border-dashed p-4 bg-light">Drag & Drop Area</div></div></div></div>
</div>

<!-- 8. Modal: Add Not Received -->
<div class="modal fade" id="addNotReceivedModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered"><div class="modal-content" style="border-top: 5px solid #007bff;"><div class="modal-header py-2"><h5>Non-Responsive Vendor</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body p-3"><label>Firm Name</label><input type="text" class="form-control"><label class="mt-2">Reason</label><textarea class="form-control"></textarea></div><div class="modal-footer py-1"><button class="btn btn-primary btn-sm">Add</button></div></div></div>
</div>

<!-- SCRIPTS -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ----- FIXED STACKED MODAL LOGIC -----
    // This requires Bootstrap JS (for modal events). 
    // If you also want to remove jQuery Bootstrap dependency, additional adjustments needed.
    document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            if (document.querySelectorAll('.modal.show').length > 0) {
                document.body.classList.add('modal-open');
            }
        });
    });

    // ----- Auto Signature Date -----
    const now = new Date();
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const sigDateEl = document.getElementById('sig-date-display');
    if (sigDateEl) {
        sigDateEl.innerHTML = `&nbsp;&nbsp; ${months[now.getMonth()]} ${now.getFullYear().toString().substr(-2)}`;
    }

    // ----- Real-time Math for Quote Entry -----
    function updateFinalPrice() {
        let subtotal = 0;

        document.querySelectorAll('.row-total').forEach(function(el) {
            let val = parseFloat(el.textContent.replace(/,/g, '')) || 0;
            subtotal += val;
        });

        let gst = parseFloat(document.getElementById('input-gst').value) || 0;
        let sst = parseFloat(document.getElementById('input-sst').value) || 0;
        let finalTotal = subtotal + gst + sst;

        const subtotalEl = document.getElementById('modal-subtotal');
        const finalTotalEl = document.getElementById('modal-final-total');
        if (subtotalEl) subtotalEl.textContent = subtotal.toLocaleString(undefined, {minimumFractionDigits: 2});
        if (finalTotalEl) finalTotalEl.textContent = finalTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    }

    // Input event for quote price
    document.querySelectorAll('.quote-price-input').forEach(function(input) {
        input.addEventListener('input', function() {
            let row = input.closest('tr');
            let qty = parseFloat(row.querySelector('.qty-val').textContent) || 0;
            let up = parseFloat(input.value) || 0;
            row.querySelector('.row-total').textContent = (qty * up).toLocaleString(undefined, {minimumFractionDigits: 2});

            // Auto Calculation Logic
            let totalGst = 0;
            let totalSst = 0;

            document.querySelectorAll('.quote-item-row').forEach(function(r) {
                let q = parseFloat(r.querySelector('.qty-val').textContent) || 0;
                let u = parseFloat(r.querySelector('.quote-price-input').value) || 0;
                let type = r.dataset.type; // goods or service

                let lineTotal = q * u;
                r.querySelector('.row-total').textContent = lineTotal.toLocaleString(undefined, {minimumFractionDigits: 2});

                if (type === 'goods') {
                    totalGst += lineTotal * 0.18;
                } else {
                    totalSst += lineTotal * 0.13;
                }
            });

            const gstEl = document.getElementById('input-gst');
            const sstEl = document.getElementById('input-sst');
            if (gstEl) gstEl.value = totalGst.toFixed(2);
            if (sstEl) sstEl.value = totalSst.toFixed(2);

            updateFinalPrice();
        });
    });

    // Input event for manual GST/SST change
    const taxInputs = ['input-gst', 'input-sst'];
    taxInputs.forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', function() {
                updateFinalPrice();
            });
        }
    });

});
</script>


@endsection