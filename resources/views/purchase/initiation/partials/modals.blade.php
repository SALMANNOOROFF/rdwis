<!-- Modal: Full Item Details (9 Columns) -->
<div class="modal fade" id="viewItemsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content glass-card" style="border-top: 5px solid #007bff;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title rajdhani text-white">Full Case Items</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="rd-table">
                        <thead>
                            <tr>
                                <th class="pl-4">#</th>
                                <th>Description</th>
                                <th class="text-right">Qty</th>
                                <th>Unit</th>
                                <th class="text-right">Price</th>
                                <th class="text-right pr-4">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $item)
                            <tr>
                                <td class="pl-4">{{ $item->pci_serial }}</td>
                                <td style="white-space: normal; min-width: 250px;">{{ $item->pci_desc }}</td>
                                <td class="text-right font-weight-bold">{{ $item->pci_qty }}</td>
                                <td>{{ $item->pci_qtyunit }}</td>
                                <td class="text-right">{{ number_format($item->pci_price) }}</td>
                                <td class="text-right pr-4 font-weight-bold text-white">{{ number_format($item->pci_qty * $item->pci_price) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detailed Comparative Statement -->
<div class="modal fade" id="detailedCSModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" style="max-width: 95%;" role="document">
        <div class="modal-content glass-card" style="border-top: 5px solid var(--rd-accent);">
            <div class="modal-header border-0 pb-3">
                <div>
                   <h5 class="modal-title rajdhani text-white mb-0" style="font-size: 22px; font-weight: 700;">
                        <i class="fas fa-balance-scale mr-2 text-accent"></i> DETAILED COMPARATIVE STATEMENT
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Item-wise price comparison across all participating vendors</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <style>
                    .cs-container { position: relative; max-height: 75vh; overflow: auto; background: var(--rd-bg); }
                    .cs-table { width: 100%; border-collapse: collapse; }
                    
                    /* Clean Sticky Columns */
                    .cs-sticky-1 { position: sticky; left: 0; z-index: 10 !important; background: var(--rd-surface) !important; width: 45px; }
                    .cs-sticky-2 { position: sticky; left: 45px; z-index: 10 !important; background: var(--rd-surface) !important; width: 300px; min-width: 300px; }
                    .cs-sticky-3 { position: sticky; left: 345px; z-index: 10 !important; background: var(--rd-surface) !important; width: 60px; border-right: 1px solid var(--rd-border) !important; }
                    
                    .cs-table thead th { position: sticky; top: 0; z-index: 5; background: var(--rd-surface2); padding: 15px; border-bottom: 1px solid var(--rd-border2); vertical-align: bottom; }
                    .cs-table thead th.cs-sticky-1, .cs-table thead th.cs-sticky-2, .cs-table thead th.cs-sticky-3 { z-index: 15 !important; border-bottom: 1px solid var(--rd-border2) !important; }
                    
                    /* Clean Typography & Borders */
                    .cs-table td { padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.03); vertical-align: middle; font-size: 13px; color: var(--rd-text2); }
                    .price-val { font-family: 'Rajdhani', sans-serif; font-weight: 600; font-size: 15px; }
                    
                    /* Subtle Winner Highlight */
                    .col-l1 { background: rgba(34, 201, 122, 0.02); }
                    .text-winner { color: var(--rd-success) !important; }
                    .text-accent-clean { color: var(--rd-accent); font-weight: 700; letter-spacing: 0.5px; }
                    
                    tr:hover td { background: rgba(255,255,255,0.02) !important; }
                    tr:hover td.cs-sticky-1, tr:hover td.cs-sticky-2, tr:hover td.cs-sticky-3 { background: var(--rd-surface2) !important; }
                    
                    .best-tag { font-size: 9px; color: var(--rd-success); margin-left: 5px; font-weight: 800; text-transform: uppercase; }
                </style>

                <div class="cs-container">
                    @php 
                        $sortedQ = $purchase->quotes->sortBy('qte_price');
                        $winners = [];
                        foreach($purchase->items as $item) {
                            $minPrice = 99999999999;
                            foreach($sortedQ as $q) {
                                $p = \DB::table('pur.quoteitems')->where('qti_qte_id', $q->qte_id)->where('qti_pci_id', $item->pci_id)->value('qti_price') ?? 0;
                                if($p > 0 && $p < $minPrice) $minPrice = $p;
                            }
                            $winners[$item->pci_id] = $minPrice;
                        }
                    @endphp
                    <table class="cs-table">
                        <thead>
                            <tr>
                                <th class="cs-sticky-1 text-muted text-center" style="font-size: 10px;">#</th>
                                <th class="cs-sticky-2 text-left text-muted" style="font-size: 10px;">DESCRIPTION</th>
                                <th class="cs-sticky-3 text-center text-muted" style="font-size: 10px;">QTY</th>
                                @foreach($sortedQ as $q)
                                    <th class="text-center {{ $loop->first ? 'col-l1' : '' }}" style="border-right: 1px solid rgba(255,255,255,0.05); min-width: 140px;">
                                        <div class="text-accent-clean {{ $loop->first ? 'text-success' : '' }}" style="font-size: 14px;">
                                            {{ strtoupper($q->firm->frm_name ?? $q->qte_firmname) }}
                                        </div>
                                        <div class="small text-muted" style="font-size: 9px; font-weight: 400; letter-spacing: 1px;">
                                            {{ $loop->first ? 'LOWEST QUOTE (L1)' : 'RANK L' . $loop->iteration }}
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $item)
                            <tr>
                                <td class="cs-sticky-1 text-center text-muted small">{{ $item->pci_serial }}</td>
                                <td class="cs-sticky-2 text-white">{{ $item->pci_desc }}</td>
                                <td class="cs-sticky-3 text-center text-white">{{ $item->pci_qty }}</td>
                                @foreach($sortedQ as $q)
                                    @php 
                                        $price = \DB::table('pur.quoteitems')->where('qti_qte_id', $q->qte_id)->where('qti_pci_id', $item->pci_id)->value('qti_price') ?? 0;
                                        $isBest = ($price > 0 && $price == ($winners[$item->pci_id] ?? -1));
                                    @endphp
                                    <td class="text-center {{ $loop->first ? 'col-l1' : '' }}" style="border-right: 1px solid rgba(255,255,255,0.05);">
                                        @if($price > 0)
                                            <span class="price-val {{ $isBest ? 'text-success' : 'text-white' }}">{{ number_format($price) }}</span>
                                            @if($isBest) <span class="best-tag">Min</span> @endif
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="border-top: 2px solid var(--rd-border2);">
                            <tr>
                                <td colspan="3" class="cs-sticky-1-3 text-right pr-4 text-accent-clean" style="font-size: 14px; background: var(--rd-surface) !important;">
                                    GRAND TOTAL
                                </td>
                                @foreach($sortedQ as $q)
                                    <td class="text-center py-3 {{ $loop->first ? 'col-l1' : '' }}" style="border-right: 1px solid rgba(255,255,255,0.05);">
                                        <div class="rajdhani text-white" style="font-size: 18px; font-weight: 700;">
                                            {{ number_format($q->qte_price) }}
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 p-3">
                <a href="{{ route('purchase.cs_formal', $purchase->pcs_id) }}" target="_blank" class="btn btn-outline-success rajdhani font-weight-bold px-4">
                    <i class="fas fa-file-invoice mr-2"></i> VIEW FORMAL STATEMENT
                </a>
                <button type="button" class="btn btn-secondary rajdhani font-weight-bold" data-dismiss="modal">CLOSE REVIEW</button>
                <button type="button" class="btn btn-primary rajdhani font-weight-bold px-4" onclick="window.print()"><i class="fas fa-print mr-2"></i> PRINT STATEMENT</button>
            </div>
        </div>
    </div>
</div>

<!-- Placeholder for Add Modals (to match original functionality) -->
<div class="modal fade" id="addQuoteModal" tabindex="-1">
    <div class="modal-dialog modal-lg text-dark"><div class="modal-content"><div class="modal-body p-5 text-center"><i class="fas fa-tools mb-3" style="font-size: 40px;"></i><h6>Add Quote functionality integration in progress...</h6><button class="btn btn-primary btn-sm mt-3" data-dismiss="modal">Close</button></div></div></div>
</div>

<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg text-dark"><div class="modal-content"><div class="modal-body p-5 text-center"><i class="fas fa-tools mb-3" style="font-size: 40px;"></i><h6>Add Item functionality integration in progress...</h6><button class="btn btn-primary btn-sm mt-3" data-dismiss="modal">Close</button></div></div></div>
</div>

<div class="modal fade" id="caseAttachmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg text-dark"><div class="modal-content"><div class="modal-body p-5 text-center"><i class="fas fa-tools mb-3" style="font-size: 40px;"></i><h6>Attachment upload integration in progress...</h6><button class="btn btn-primary btn-sm mt-3" data-dismiss="modal">Close</button></div></div></div>
</div>
