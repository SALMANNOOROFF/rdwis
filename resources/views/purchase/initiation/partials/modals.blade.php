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
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content glass-card" style="border-top: 5px solid #007bff;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title rajdhani text-white"><i class="fas fa-balance-scale mr-2 text-primary"></i> Detailed Item-wise Comparison</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive">
                    <table class="rd-table text-center" style="font-size: 11px;">
                        <thead>
                            @php $sortedQ = $purchase->quotes->sortBy('qte_price'); @endphp
                            <tr>
                                <th rowspan="2" class="align-middle pl-4" style="width: 40px;">#</th>
                                <th rowspan="2" class="align-middle" style="width: 200px;">Description</th>
                                <th rowspan="2" class="align-middle" style="width: 40px;">Qty</th>
                                @foreach($sortedQ as $q)
                                    <th colspan="2" class="align-middle {{ $loop->first ? 'text-success' : 'text-primary' }}" style="background: rgba(var(--rd-{{$loop->first ? 'success' : 'primary'}}-rgb), 0.05);">
                                        {{ $q->firm->frm_name ?? $q->qte_firmname }}
                                        @if($loop->first) <br><span class="badge badge-success" style="font-size: 8px;">WINNER</span> @endif
                                    </th>
                                @endforeach
                            </tr>
                            <tr>
                                @foreach($sortedQ as $q)
                                    <th>Price</th><th>Total</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $item)
                            <tr>
                                <td class="pl-4">{{ $item->pci_serial }}</td>
                                <td class="text-left">{{ Str::limit($item->pci_desc, 50) }}</td>
                                <td>{{ $item->pci_qty }}</td>
                                @foreach($sortedQ as $q)
                                    @php 
                                        $price = \DB::table('pur.quoteitems')->where('qti_qte_id', $q->qte_id)->where('qti_pci_id', $item->pci_id)->value('qti_price') ?? 0;
                                    @endphp
                                    <td class="{{ $loop->first ? 'text-success font-weight-bold' : '' }}">{{ $price > 0 ? number_format($price) : '-' }}</td>
                                    <td class="{{ $loop->first ? 'text-success font-weight-bold' : '' }}">{{ $price > 0 ? number_format($price * $item->pci_qty) : '-' }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold rajdhani" style="background: #0f161e;">
                                <td colspan="3" class="text-right py-2">GRAND TOTAL</td>
                                @foreach($sortedQ as $q)
                                    <td colspan="2" class="py-2 {{ $loop->first ? 'text-success' : 'text-primary' }}" style="font-size: 14px;">Rs. {{ number_format($q->qte_price) }}</td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
