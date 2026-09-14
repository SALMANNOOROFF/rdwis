<!-- Modal: Full Item Details (10 Columns) -->
<div class="modal fade" id="viewItemsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-top: 4px solid var(--rd-accent); border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
            <div class="modal-header border-bottom py-2 px-3" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                <h5 class="modal-title rajdhani text-dark font-weight-bold"><i class="fas fa-boxes text-primary mr-2"></i>Full Case Items & Classification</h5>
                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-3">
                <div class="table-responsive rounded border" style="border-color: var(--rd-border) !important;">
                    <table class="table table-sm table-hover mb-0" style="background: #ffffff; font-size: 12px;">
                        <thead style="background: var(--rd-surface2);">
                            <tr>
                                <th class="pl-3" style="width: 40px;">#</th>
                                <th>Description</th>
                                <th class="text-center" style="width: 60px;">Qty</th>
                                <th class="text-center" style="width: 70px;">Unit</th>
                                <th class="text-right" style="width: 105px;">Price (PKR)</th>
                                <th class="text-right" style="width: 115px;">Total (PKR)</th>
                                <th class="text-center" style="width: 105px;">Type</th>
                                <th class="text-center" style="width: 125px;">Subtype</th>
                                <th class="text-center" style="width: 95px;">Inv/Asset</th>
                                <th class="text-center pr-3" style="width: 110px;">Budget Subhead</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchase->items as $item)
                            @php
                                $typeStr = $item->type_name ?? ($item->pci_type == 7 ? 'Permanent' : ($item->pci_type == 2 ? 'Consumable' : ($item->pci_type == 3 ? 'Service' : 'Permanent')));
                                $invAsstStr = $item->type2_name ?? ($item->pci_type2 == 6 ? 'Asset' : ($item->pci_type2 == 5 ? 'Inventory' : '-'));
                                $subheadStr = $item->pci_subhead ?: ($purchase->pcs_type === 'Ps' ? 'Equipment' : 'Misc');
                            @endphp
                            <tr>
                                <td class="pl-3 font-weight-bold text-center">{{ $item->pci_serial }}</td>
                                <td style="white-space: normal; min-width: 200px; font-weight: 600;" class="text-dark">{{ $item->pci_desc }}</td>
                                <td class="text-center font-weight-bold">{{ $item->pci_qty }}</td>
                                <td class="text-center"><span class="badge badge-light border">{{ $item->pci_qtyunit ?: 'num' }}</span></td>
                                <td class="text-right font-weight-bold text-secondary">{{ number_format($item->pci_price, 2) }}</td>
                                <td class="text-right font-weight-bold text-primary">{{ number_format($item->pci_qty * $item->pci_price, 2) }}</td>
                                <td class="text-center"><span class="badge badge-primary px-2 py-1">{{ $typeStr }}</span></td>
                                <td class="text-center">{{ $item->pci_subtype ?: 'N/A' }}</td>
                                <td class="text-center">{{ $invAsstStr }}</td>
                                <td class="text-center pr-3"><span class="badge badge-success px-2 py-1">{{ $subheadStr }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">No items recorded.</td>
                            </tr>
                            @endforelse
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
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-top: 4px solid var(--rd-accent); border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
            <div class="modal-header border-bottom py-2 px-3" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                <div>
                   <h5 class="modal-title rajdhani text-dark mb-0 font-weight-bold" style="font-size: 18px;">
                        <i class="fas fa-balance-scale mr-2 text-primary"></i> DETAILED COMPARATIVE STATEMENT
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Item-wise price comparison across all participating vendors</p>
                </div>
                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <style>
                    .cs-container { position: relative; max-height: 75vh; overflow: auto; background: #ffffff; }
                    .cs-table { width: 100%; border-collapse: collapse; }
                    
                    /* Clean Sticky Columns */
                    .cs-sticky-1 { position: sticky; left: 0; z-index: 10 !important; background: #ffffff !important; width: 45px; }
                    .cs-sticky-2 { position: sticky; left: 45px; z-index: 10 !important; background: #ffffff !important; width: 300px; min-width: 300px; }
                    .cs-sticky-3 { position: sticky; left: 345px; z-index: 10 !important; background: #ffffff !important; width: 60px; border-right: 1px solid var(--rd-border) !important; }
                    
                    .cs-table thead th { position: sticky; top: 0; z-index: 5; background: var(--rd-surface2); padding: 12px 15px; border-bottom: 1px solid var(--rd-border2); vertical-align: bottom; color: var(--rd-text1); font-weight: 700; }
                    .cs-table thead th.cs-sticky-1, .cs-table thead th.cs-sticky-2, .cs-table thead th.cs-sticky-3 { z-index: 15 !important; border-bottom: 1px solid var(--rd-border2) !important; }
                    
                    /* Clean Typography & Borders */
                    .cs-table td { padding: 10px 15px; border-bottom: 1px solid var(--rd-border); vertical-align: middle; font-size: 13px; color: var(--rd-text1); }
                    .price-val { font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 15px; }
                    
                    /* Subtle Winner Highlight */
                    .col-l1 { background: rgba(22, 163, 74, 0.05); }
                    .text-winner { color: var(--rd-success) !important; }
                    .text-accent-clean { color: var(--rd-accent); font-weight: 700; letter-spacing: 0.5px; }
                    
                    tr:hover td { background: var(--rd-surface2) !important; }
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
                                    <th class="text-center {{ $loop->first ? 'col-l1' : '' }}" style="border-right: 1px solid var(--rd-border); min-width: 140px;">
                                        <div class="text-accent-clean {{ $loop->first ? 'text-success' : '' }}" style="font-size: 14px;">
                                            {{ strtoupper($q->firm->frm_name ?? $q->qte_firmname) }}
                                        </div>
                                        <div class="small text-muted" style="font-size: 9px; font-weight: 600; letter-spacing: 0.5px;">
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
                                <td class="cs-sticky-2 text-dark font-weight-500">{{ $item->pci_desc }}</td>
                                <td class="cs-sticky-3 text-center text-dark font-weight-bold">{{ $item->pci_qty }}</td>
                                @foreach($sortedQ as $q)
                                    @php 
                                        $price = \DB::table('pur.quoteitems')->where('qti_qte_id', $q->qte_id)->where('qti_pci_id', $item->pci_id)->value('qti_price') ?? 0;
                                        $isBest = ($price > 0 && $price == ($winners[$item->pci_id] ?? -1));
                                    @endphp
                                    <td class="text-center {{ $loop->first ? 'col-l1' : '' }}" style="border-right: 1px solid var(--rd-border);">
                                        @if($price > 0)
                                            <span class="price-val {{ $isBest ? 'text-success' : 'text-dark' }}">{{ number_format($price) }}</span>
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
                                <td colspan="3" class="cs-sticky-1-3 text-right pr-4 text-accent-clean" style="font-size: 14px; background: var(--rd-surface2) !important;">
                                    GRAND TOTAL
                                </td>
                                @foreach($sortedQ as $q)
                                    <td class="text-center py-3 {{ $loop->first ? 'col-l1' : '' }}" style="border-right: 1px solid var(--rd-border);">
                                        <div class="rajdhani text-dark font-weight-bold" style="font-size: 18px;">
                                            {{ number_format($q->qte_price) }}
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top p-3" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                <a href="{{ route('purchase.cs_formal', $purchase->pcs_id) }}" target="_blank" class="btn btn-outline-success rajdhani font-weight-bold px-4">
                    <i class="fas fa-file-invoice mr-2"></i> VIEW FORMAL STATEMENT
                </a>
                <button type="button" class="btn btn-secondary rajdhani font-weight-bold px-4" data-dismiss="modal">CLOSE REVIEW</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ ADD QUOTE MODAL ============ --}}
<div class="modal fade" id="addQuoteModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
            <div class="modal-header border-bottom py-2 px-4" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                <h5 class="modal-title rajdhani font-weight-bold text-dark mb-0" style="letter-spacing: 1.5px;">ADD VENDOR QUOTATION</h5>
                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST" id="addQuoteForm">
                    @csrf
                    <input type="hidden" name="op" value="add_quote">
                    <div class="row no-gutters">
                        {{-- Left: Vendor Selection --}}
                        <div class="col-md-4 border-right p-4" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                            <div class="form-group">
                                <label class="rajdhani text-dark font-weight-bold small mb-2">SELECT VENDOR / FIRM</label>
                                <select name="qte_frm_id" id="qte_frm_id" class="form-control rajdhani select2" required style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);">
                                    <option value="">-- Choose Vendor --</option>
                                    @foreach($firms ?? [] as $firm)
                                        <option value="{{ $firm->frm_id }}">{{ $firm->frm_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="rajdhani text-dark font-weight-bold small mb-2">QUOTE REFERENCE #</label>
                                <input type="text" name="qte_num" class="form-control rajdhani" placeholder="e.g. Q-2024-001" required style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);">
                            </div>
                            <div class="form-group">
                                <label class="rajdhani text-dark font-weight-bold small mb-2">QUOTE DATE</label>
                                <input type="date" name="qte_date" class="form-control rajdhani" value="{{ date('Y-m-d') }}" required style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);">
                            </div>
                            <div class="mt-4 p-3 rounded" style="background: rgba(37,99,235,0.06); border: 1px solid rgba(37,99,235,0.2);">
                                <div class="small text-muted rajdhani mb-1">TOTAL QUOTE VALUE</div>
                                <div class="h4 mb-0 text-primary font-weight-bold rajdhani" id="quoteTotalDisplay">PKR 0.00</div>
                            </div>
                        </div>
                        {{-- Right: Item Pricing --}}
                        <div class="col-md-8 p-4" style="background: #ffffff;">
                            <h6 class="rajdhani text-dark font-weight-bold mb-3">ITEM-WISE PRICING</h6>
                            <div class="table-responsive rounded border" style="border-color: var(--rd-border) !important; max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm mb-0 rajdhani" style="font-size: 13px; background: #ffffff;">
                                    <thead style="background: var(--rd-surface2);">
                                        <tr class="text-muted">
                                            <th class="pl-3">ITEM DESCRIPTION</th>
                                            <th class="text-center">QTY</th>
                                            <th class="text-right pr-3">UNIT PRICE (PKR)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchase->items as $item)
                                        <tr>
                                            <td class="pl-3 py-2 text-dark font-weight-500">
                                                {{ $item->pci_desc }}
                                                <input type="hidden" name="items[{{ $item->pci_id }}][pci_id]" value="{{ $item->pci_id }}">
                                            </td>
                                            <td class="text-center py-2 text-dark font-weight-bold">{{ $item->pci_qty }}</td>
                                            <td class="text-right pr-3 py-2">
                                                <input type="number" name="items[{{ $item->pci_id }}][price]" 
                                                       class="form-control form-control-sm text-right quote-price-input" 
                                                       placeholder="0.00" step="0.01" required
                                                       data-qty="{{ $item->pci_qty }}"
                                                       style="background: #ffffff; color: var(--rd-accent); border: 1px solid var(--rd-border2); width: 120px; display: inline-block; font-weight: 700;">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top py-2 px-4" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                <button type="button" class="btn btn-secondary btn-sm rajdhani font-weight-bold" data-dismiss="modal">CANCEL</button>
                <button type="submit" form="addQuoteForm" class="btn btn-primary btn-sm rajdhani font-weight-bold px-4">SAVE QUOTATION</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ ADD ITEM MODAL ============ --}}
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
            <div class="modal-header border-bottom py-2 px-4" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                <h5 class="modal-title rajdhani font-weight-bold text-dark mb-0">ADD NEW ITEM</h5>
                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4" style="background: #ffffff;">
                <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="op" value="add_item">

                    @if($purchase->pcs_type === 'Rb')
                    <div class="form-group p-3 rounded mb-3" style="background: var(--rd-surface2); border: 1px solid var(--rd-border2);">
                        <label class="rajdhani text-dark font-weight-bold small mb-2 d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-check text-warning mr-1"></i> SELECT EMPLOYEE (TA/DA)</span>
                            <span class="text-muted small">Meezan Bank & Salary Auto-Check</span>
                        </label>
                        <div class="row">
                            <div class="col-md-8">
                                <select name="emp_id" id="modal_tada_emp_id" class="form-control rajdhani" style="width: 100%;">
                                    <option value="">-- Choose Employee (Active Division Contracts) --</option>
                                    @forelse($employees ?? [] as $emp)
                                        <option value="{{ $emp->emp_id }}">{{ $emp->emp_name }} ({{ $emp->emp_id }}) [{{ $emp->emp_rank ?: 'Staff' }}]</option>
                                    @empty
                                        <option value="" disabled>-- No active contracted employees found for this division --</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-warning btn-block font-weight-bold rajdhani" id="modal_btn_fetch_emp" onclick="fetchModalTadaEmpDetails()">
                                    <i class="fas fa-magic mr-1"></i> Add Emp Details
                                </button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="item_price" id="modal_tada_item_price" value="0">
                    @endif

                    <div class="form-group">
                        <label class="rajdhani text-dark font-weight-bold small mb-2">ITEM DESCRIPTION</label>
                        <textarea name="item_desc" class="form-control rajdhani" rows="3" required style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);" placeholder="{{ $purchase->pcs_type === 'Rb' ? 'Select employee & click Add Emp Details...' : 'Enter item description...' }}"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="rajdhani text-dark font-weight-bold small mb-2">QUANTITY</label>
                                <input type="number" name="item_qty" class="form-control rajdhani" step="0.0001" value="1" required style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="rajdhani text-dark font-weight-bold small mb-2">DENOMINATION (UNIT)</label>
                                <select name="item_qtyunit" class="form-control rajdhani" style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);">
                                    <option value="num" selected>num (Numbers)</option>
                                    <option value="set">set (Sets)</option>
                                    <option value="job">job (Job/Work)</option>
                                    <option value="svc">svc (Service)</option>
                                    <option value="day">day (Days)</option>
                                    <option value="kg">kg (Kilogram)</option>
                                    <option value="ltr">ltr (Liters)</option>
                                    <option value="m">m (Meters)</option>
                                    <option value="box">box (Box)</option>
                                    <option value="pkt">pkt (Packets)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="rajdhani text-dark font-weight-bold small mb-2">ITEM TYPE</label>
                                <select name="item_type" id="modal_item_type" class="form-control rajdhani" onchange="handleModalItemTypeChange(this)" style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);">
                                    @if($purchase->pcs_type === 'Rb')
                                        <option value="3" selected>Service (3)</option>
                                    @elseif($purchase->pcs_type === 'Pt')
                                        <option value="2" selected>Consumable (2)</option>
                                        <option value="7">Permanent (7)</option>
                                        <option value="3">Service (3)</option>
                                    @else
                                        <option value="7" selected>Permanent (7)</option>
                                        <option value="2">Consumable (2)</option>
                                        <option value="3">Service (3)</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="rajdhani text-dark font-weight-bold small mb-2">SUBTYPE</label>
                                <select name="item_subtype" id="modal_item_subtype" class="form-control rajdhani" style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);">
                                    @if($purchase->pcs_type === 'Rb')
                                        <option value="Travelling/Boarding/Lodging" selected>Travelling/Boarding/Lodging</option>
                                        <option value="Travel">Travel</option>
                                        <option value="Meals/Refreshments">Meals/Refreshments</option>
                                    @else
                                        <option value="Parts" selected>Parts</option>
                                        <option value="Tools / Test Equipment">Tools / Test Equipment</option>
                                        <option value="Machinery / Equipment">Machinery / Equipment</option>
                                        <option value="IT Equipment">IT Equipment</option>
                                        <option value="Software">Software</option>
                                        <option value="Furniture">Furniture</option>
                                        <option value="Appliance">Appliance</option>
                                        <option value="Chemicals">Chemicals</option>
                                        <option value="Raw Material">Raw Material</option>
                                        <option value="Stationary">Stationary</option>
                                        <option value="Cleaning Material">Cleaning Material</option>
                                        <option value="POL">POL</option>
                                        <option value="Equipment Installation">Equipment Installation</option>
                                        <option value="Equipment Repairs & Maintenance">Equipment Repairs & Maintenance</option>
                                        <option value="Other">Other</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="rajdhani text-dark font-weight-bold small mb-2">INV / ASST</label>
                                <select name="item_type2" id="modal_item_type2" class="form-control rajdhani" {{ $purchase->pcs_type === 'Rb' ? 'disabled' : '' }} style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);">
                                    <option value="5" {{ $purchase->pcs_type === 'Pt' ? 'selected' : '' }}>Inventory (5)</option>
                                    <option value="6" {{ $purchase->pcs_type === 'Ps' ? 'selected' : '' }}>Asset (6)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="rajdhani text-dark font-weight-bold small mb-2">SUBHEAD</label>
                        @if($purchase->pcs_type === 'Ps')
                            <div class="p-2 rounded border text-dark font-weight-bold rajdhani d-flex justify-content-between align-items-center" style="background: var(--rd-surface2); border: 1px solid var(--rd-border2) !important;">
                                <span><i class="fas fa-microchip text-primary mr-2"></i>Equipment</span>
                                <span class="badge badge-primary">Standard for Major Purchase</span>
                            </div>
                            <input type="hidden" name="item_subhead" value="Equipment">
                        @else
                            <select name="item_subhead" class="form-control rajdhani" style="background: #ffffff; color: var(--rd-text1); border: 1px solid var(--rd-border2);">
                                <option value="Misc" {{ ($purchase->subhead_display ?? 'Misc') === 'Misc' ? 'selected' : '' }}>Misc (Default)</option>
                                <option value="Equipment" {{ ($purchase->subhead_display ?? '') === 'Equipment' ? 'selected' : '' }}>Equipment</option>
                                @foreach($projectSubheads ?? [] as $shd)
                                    @if(!in_array($shd, ['Misc', 'Equipment']))
                                        <option value="{{ $shd }}">{{ $shd }}</option>
                                    @endif
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="text-right mt-3">
                        <button type="button" class="btn btn-secondary btn-sm rajdhani font-weight-bold mr-2" data-dismiss="modal">CANCEL</button>
                        <button type="submit" class="btn btn-primary btn-sm rajdhani font-weight-bold px-4">ADD ITEM</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ============ ATTACHMENT MODAL ============ --}}
<div class="modal fade" id="caseAttachmentModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="background: #ffffff; border: 1px solid var(--rd-border2); border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
            <div class="modal-header border-bottom py-2 px-4" style="background: var(--rd-surface2); border-color: var(--rd-border) !important;">
                <h5 class="modal-title rajdhani font-weight-bold text-dark mb-0">UPLOAD ATTACHMENT</h5>
                <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4" style="background: #ffffff;">
                <form action="{{ route('purchase.initiation.save', $purchase->pcs_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="op" value="add_files">
                    <div class="form-group mb-4">
                        <div class="p-4 text-center rounded border" style="border: 2px dashed var(--rd-border2) !important; background: var(--rd-surface2);">
                            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                            <input type="file" name="attachments[]" multiple class="form-control-file mb-3" style="color: var(--rd-text1);">
                            <div class="small text-muted rajdhani">PDF, JPG, PNG, DOC, DOCX (Max 10MB)</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary btn-sm rajdhani font-weight-bold mr-2" data-dismiss="modal">CANCEL</button>
                        <button type="submit" class="btn btn-primary btn-sm rajdhani font-weight-bold px-4">UPLOAD NOW</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPTS FOR TOTALS CALCULATION --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.quote-price-input');
    const display = document.getElementById('quoteTotalDisplay');
    
    function calculateTotal() {
        let total = 0;
        inputs.forEach(input => {
            const qty = parseFloat(input.dataset.qty) || 0;
            const price = parseFloat(input.value) || 0;
            total += (qty * price);
        });
        display.innerText = 'PKR ' + new Intl.NumberFormat().format(total);
    }
    
    inputs.forEach(input => {
        input.addEventListener('input', calculateTotal);
    });

    // Handle form submissions with debug logging
    const forms = document.querySelectorAll('#addQuoteModal form, #addItemModal form, #caseAttachmentModal form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            if (typeof logToDebug === 'function') {
                logToDebug(`Submitting ${this.querySelector('input[name="op"]')?.value} form...`, 'INFO');
            }
        });
    });
});

function fetchModalTadaEmpDetails() {
    const sel = document.getElementById('modal_tada_emp_id');
    const empId = sel ? sel.value : '';
    if (!empId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Employee Required', 'Please select an employee first', 'warning');
        } else {
            alert('Please select an employee first');
        }
        return;
    }

    const btn = document.getElementById('modal_btn_fetch_emp');
    const originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Fetching...';
    }

    const url = "{{ url('/purchase/tada/employee-details') }}/" + encodeURIComponent(empId);
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok || !data.success) {
            const msg = data.error || data.message || 'Failed to fetch employee details';
            if (typeof Swal !== 'undefined') {
                Swal.fire('TA/DA Validation Error', msg, 'error');
            } else {
                alert(msg);
            }
            return;
        }

        const descEl = document.querySelector('#addItemModal textarea[name="item_desc"]');
        const priceEl = document.getElementById('modal_tada_item_price');
        const qtyEl = document.querySelector('#addItemModal input[name="item_qty"]');
        const unitEl = document.querySelector('#addItemModal input[name="item_unit"]');

        if (descEl) descEl.value = data.description;
        if (priceEl) priceEl.value = data.tada_amount;
        if (qtyEl) qtyEl.value = 1;
        if (unitEl) unitEl.value = 'num';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Employee Details Added',
                text: `${data.emp_name} (Grade: ${data.grade}) -> TA/DA PKR ${data.tada_amount}`,
                icon: 'success',
                timer: 1800,
                showConfirmButton: false
            });
        }
    })
    .catch(err => {
        console.error(err);
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'Unable to retrieve employee TA/DA details.', 'error');
        } else {
            alert('Unable to retrieve employee TA/DA details.');
        }
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml || '<i class="fas fa-magic mr-1"></i> Add Emp Details';
        }
    });
}

function handleModalItemTypeChange(selectEl) {
    const val = parseInt(selectEl.value);
    const subtypeSelect = document.getElementById('modal_item_subtype');
    const invasstSelect = document.getElementById('modal_item_type2');

    const optionsByType = {
        7: ['Parts', 'Tools / Test Equipment', 'Machinery / Equipment', 'IT Equipment', 'Software', 'Furniture', 'Appliance', 'Other'],
        2: ['Parts', 'Chemicals', 'Raw Material', 'Stationary', 'Cleaning Material', 'POL', 'Other'],
        3: ['Equipment Installation', 'Equipment Repairs & Maintenance', 'Travelling/Boarding/Lodging', 'Travel', 'Meals/Refreshments', 'Consultancy', 'Other']
    };

    if (subtypeSelect) {
        const list = optionsByType[val] || optionsByType[7];
        const currSub = subtypeSelect.value;
        subtypeSelect.innerHTML = '';
        list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item;
            opt.textContent = item;
            if (item === currSub || (!currSub && list.indexOf(item) === 0)) opt.selected = true;
            subtypeSelect.appendChild(opt);
        });
    }

    if (invasstSelect) {
        if (val === 3) {
            invasstSelect.disabled = true;
            invasstSelect.value = '';
        } else {
            invasstSelect.disabled = false;
            if (!invasstSelect.value) {
                invasstSelect.value = (val === 2 ? '5' : '6');
            }
        }
    }
}
</script>
