@extends('partials.case_print_layout')

@section('title', 'Purchase Case #' . $purchase->pcs_id)

@section('content')
@php
    $breakdown = $purchase->tax_breakdown;
    $winnerQuote = $purchase->winning_quote
        ?? $purchase->quotes->where('qte_recomm', true)->first()
        ?? $purchase->quotes->sortBy('qte_price')->first();

    $basePrice = (float)($breakdown['base'] ?? 0);
    $sst = (float)($breakdown['sst'] ?? 0);
    $gst = (float)($breakdown['gst'] ?? 0);
    $total = (float)($breakdown['total'] ?? ($purchase->pcs_price ?? 0));

    if ($total <= 0 && $winnerQuote) {
        $total = (float)($winnerQuote->qte_price ?: 0);
        $sst = (float)($winnerQuote->qte_inttax ?? 0);
        $gst = (float)($winnerQuote->qte_midtax ?? 0);
        $basePrice = (float)($winnerQuote->qte_intprice ?: ($total - $sst - $gst));
        if ($basePrice <= 0 && $total > 0) {
            $basePrice = max(0, $total - $sst - $gst);
        }
    } elseif ($basePrice <= 0 && $total > 0) {
        $basePrice = max(0, $total - $sst - $gst);
    }
    if ($total <= 0 && $basePrice > 0) {
        $total = $basePrice + $sst + $gst;
    }

    $firmName = $winnerQuote?->firm->frm_name
        ?? ($winnerQuote?->qte_firmname
        ?? ($purchase->firm?->frm_name
        ?? ($purchase->pcs_frm_id ? \Illuminate\Support\Facades\DB::table('frm.firmz')->where('frm_id', $purchase->pcs_frm_id)->value('frm_name') : 'N/A')));

    $termsText = 'Complete Payment After Delivery';
    $justificationText = null;
    if (!empty($purchase->pcs_remarks)) {
        $rawRemarks = trim((string)$purchase->pcs_remarks);
        $decoded = json_decode($rawRemarks, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $termsText = $decoded['terms'] ?? $decoded['justification'] ?? $decoded['remarks'] ?? $rawRemarks;
            if (!empty($decoded['justification']) && $decoded['justification'] !== $termsText) {
                $justificationText = $decoded['justification'];
            }
        } else {
            $termsText = $rawRemarks;
        }
    }

    $dateFormatted = $purchase->pcs_date ? \Carbon\Carbon::parse($purchase->pcs_date)->format('d M y') : 'N/A';
    $headTitle = $purchase->head_display;
    $rfqLetter = $purchase->itLetter;
    $rfqFirms = is_array($rfqLetter?->pit_firms) ? $rfqLetter->pit_firms : [];
@endphp

<div class="doc-top-row">
    <div class="main-title">
        Purchase Case {{ $purchase->pcs_id }} dated {{ $dateFormatted }} ({{ $headTitle }} Head)
    </div>
    <div class="record-purpose-tag">FOR RECORD PURPOSE</div>
</div>

{{-- Exact Case Detail Borderless 3-Column Header --}}
<div class="meta-grid">
    <div class="meta-item"><span class="meta-label">Title:</span> <span class="meta-value">{{ $purchase->pcs_title }}</span></div>
    <div class="meta-item"><span class="meta-label">Approved:</span> <span class="meta-value">{{ $purchase->approved_date ? $purchase->approved_date->format('d M y') : '—' }}</span></div>
    <div class="meta-item"><span class="meta-label">Price:</span> <span class="meta-value text-right">{{ number_format($basePrice, 2) }}</span></div>

    <div class="meta-item"><span class="meta-label">Minute:</span> <span class="meta-value">{{ $purchase->pcs_minute ?: '—' }}</span></div>
    <div class="meta-item"><span class="meta-label">Head:</span> <span class="meta-value">({{ $headTitle }})</span></div>
    <div class="meta-item"><span class="meta-label">SST:</span> <span class="meta-value text-right">{{ number_format($sst, 2) }}</span></div>

    <div class="meta-item"><span class="meta-label">Date:</span> <span class="meta-value">{{ $dateFormatted }}</span></div>
    <div class="meta-item"><span class="meta-label">Subhead:</span> <span class="meta-value" style="font-weight: 600;">{{ $purchase->subhead_display }}</span></div>
    <div class="meta-item"><span class="meta-label">GST:</span> <span class="meta-value text-right">{{ number_format($gst, 2) }}</span></div>

    <div class="meta-item"><span class="meta-label">Initiator:</span> <span class="meta-value">{{ $purchase->unit?->unt_namesh ?? ($divisionName ?: 'Division') }}</span></div>
    <div class="meta-item"><span class="meta-label">Status:</span> <span class="meta-value"><strong>{{ $purchase->pcs_status }}</strong></span></div>
    <div class="meta-item"><span class="meta-label">Total:</span> <span class="meta-value text-right" style="font-weight: 700;">{{ number_format($total, 2) }}</span></div>

    <div class="meta-item" style="grid-column: span 2;">
        <span class="meta-label">Firm:</span> <span class="meta-value">{{ $firmName }}</span>
    </div>
    <div class="meta-item">
        <span class="meta-label">Stage:</span> <span class="meta-value">{{ $currentAuthority ?: $purchase->pcs_status }}</span>
    </div>
</div>

{{-- Requested Items Table matching case_detail.blade.php --}}
<span class="section-title">Items: ({{ $purchase->items->count() }})</span>
<table class="doc-table">
    <thead>
        <tr>
            <th style="width: 42px;" class="text-center">S No</th>
            <th>Description</th>
            <th style="width: 120px;" class="text-center">Price &amp; Qty</th>
            <th style="width: 145px;" class="text-center">Type &amp; SubType</th>
            <th style="width: 145px;" class="text-center">Inv-Asset &amp; S/Head</th>
        </tr>
    </thead>
    <tbody>
        @forelse($purchase->items->sortBy('pci_serial')->values() as $idx => $item)
            @php
                $itemQty = (float)($item->pci_qty ?? 1) ?: 1;
                $itemRate = (float)($item->pci_price ?: ($item->pci_estprice ?? 0));
                $itemTotal = $itemRate * $itemQty;
                $itemType = $item->type_name;
                $itemSubType = $item->pci_subtype ?: '—';
                $itemClass = $item->type2_name;
                $itemSubhead = $item->pci_subhead ?: ($purchase->subhead_display ?: 'Equipment');
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    {{ $item->pci_desc }}
                    @if(!empty($item->employee))
                        <div style="font-size: 8.5pt; color: #475569;">Officer: {{ $item->employee->emp_name }} ({{ $item->employee->emp_id }})</div>
                    @endif
                </td>
                <td class="text-center">
                    <div>{{ number_format($itemTotal, 2) }}</div>
                    <div style="color: #475569; font-size: 8.5pt;">{{ $item->pci_qty ?: 1 }} {{ $item->pci_qtyunit ?: 'num' }}</div>
                    @if($itemQty > 1)
                        <div style="font-size: 7.5pt; color: #64748b;">(@ {{ number_format($itemRate, 2) }})</div>
                    @endif
                </td>
                <td class="text-center">
                    <div style="font-weight: 600;">{{ $itemType }}</div>
                    <div style="color: #475569; font-size: 8.5pt;">{{ $itemSubType }}</div>
                </td>
                <td class="text-center">
                    <div style="font-weight: 600;">{{ $itemClass }}</div>
                    <div style="color: #475569; font-size: 8.5pt;">{{ $itemSubhead }}</div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center" style="color: #64748b; font-style: italic;">No items listed.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="plain-summary-block">
    <div><strong>Terms and Conditions:</strong> {{ $termsText }}</div>
    @if($justificationText)
        <div style="margin-top: 4px;"><strong>Justification / Remarks:</strong> {{ $justificationText }}</div>
    @endif
</div>

{{-- RFQ / Invitation to Tender Details (when RFQ letter has been generated) --}}
@if($rfqLetter)
    <span class="section-title">RFQ / Inquiry Details:</span>
    <div class="plain-summary-block" style="margin-top: 4px;">
        <div>
            <strong>RFQ Ref No:</strong> {{ $rfqLetter->pit_refno ?: 'N/A' }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>RFQ Date:</strong> {{ $rfqLetter->pit_date ? \Carbon\Carbon::parse($rfqLetter->pit_date)->format('d M y') : 'N/A' }}
            @if(!empty($rfqLetter->pit_signatory_name))
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <strong>Signatory:</strong> {{ trim(($rfqLetter->pit_signatory_rank ? $rfqLetter->pit_signatory_rank . ' ' : '') . $rfqLetter->pit_signatory_name) }}
                {{ $rfqLetter->pit_signatory_dept ? "({$rfqLetter->pit_signatory_dept})" : '' }}
            @endif
        </div>
        @if(!empty($rfqLetter->pit_subject))
            <div style="margin-top: 3px;"><strong>RFQ Subject:</strong> {{ $rfqLetter->pit_subject }}</div>
        @endif
        @if(!empty($rfqFirms))
            <div style="margin-top: 4px;">
                <strong>Invited Vendors / Firms ({{ count($rfqFirms) }}):</strong>
                @php
                    $vendorNames = [];
                    foreach ($rfqFirms as $vf) {
                        if (is_string($vf)) {
                            $vendorNames[] = $vf;
                        } elseif (is_array($vf)) {
                            $vendorNames[] = $vf['name'] ?? ($vf['frm_name'] ?? ($vf['title'] ?? json_encode($vf)));
                        }
                    }
                @endphp
                {{ implode(', ', array_filter($vendorNames)) ?: '—' }}
            </div>
        @endif
    </div>
@endif

{{-- Quotes Table matching case_detail.blade.php --}}
<span class="section-title">Quotes: ({{ $purchase->quotes->count() }})</span>
<table class="doc-table">
    <thead>
        <tr>
            <th style="width: 85px;">No.</th>
            <th style="width: 95px;">Date</th>
            <th>Firm</th>
            <th style="width: 115px;" class="text-right">Price</th>
            <th style="width: 115px;" class="text-center">Tech. Acceptable</th>
        </tr>
    </thead>
    <tbody>
        @forelse($purchase->quotes->sortBy('qte_price') as $q)
            <tr>
                <td>{{ $q->qte_id }}</td>
                <td>{{ $q->qte_date ? \Carbon\Carbon::parse($q->qte_date)->format('d M y') : '—' }}</td>
                <td>
                    {{ $q->firm->frm_name ?? $q->qte_firmname }}
                    @if($q->qte_recomm)
                        <span style="color: #166534; font-size: 8.5pt; font-weight: 700; margin-left: 5px;">(Lowest/Recommended)</span>
                    @endif
                </td>
                <td class="text-right">{{ number_format((float)$q->qte_price, 2) }}</td>
                <td class="text-center">{{ $q->qte_techaccept ? 'Yes' : 'No' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center" style="color: #64748b; font-style: italic;">No quotes recorded for this case.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($purchase->noQuotes->count() > 0)
    <div class="plain-summary-block">
        <strong>Quotes Not Received ({{ $purchase->noQuotes->count() }}):</strong>
        {{ $purchase->noQuotes->map(fn($nq) => $nq->firm->frm_name ?? $nq->nqt_firmname)->filter()->implode(', ') }}
    </div>
@endif

{{-- Material / Delivery Receipts (when fulfilled or receipts exist) --}}
@if($receipts->count() > 0 || in_array(strtolower(trim((string)$purchase->pcs_status)), ['fulfilled', 'partially fulfilled', 'completed']))
    <span class="section-title">Delivery / Material Receipts: ({{ $receipts->count() }})</span>
    @if($receipts->count() > 0)
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Receipt #</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Reference / Notes</th>
                    <th class="text-right">Value (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipts as $rc)
                    <tr>
                        <td>#PR-{{ $rc->prt_id }}</td>
                        <td>{{ $rc->prt_date ? \Carbon\Carbon::parse($rc->prt_date)->format('d M y') : '—' }}</td>
                        <td>{{ $rc->prt_status }}</td>
                        <td>{{ $rc->prt_remarks ?? ($rc->prt_invno ?? '—') }}</td>
                        <td class="text-right">{{ number_format((float)($rc->prt_value ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="plain-summary-block">
            <strong>Fulfillment Status:</strong> {{ $purchase->pcs_fulfillment_status ?: $purchase->pcs_status }}
        </div>
    @endif
@endif

@include('partials.case_print_remarks_and_attachments', [
    'remarksList' => $remarksList,
    'enclosuresList' => $enclosuresList,
    'remarksSectionTitle' => 'Approval & Remarks Trail',
    'attachmentsSectionTitle' => 'Attached Documents',
])
@endsection
