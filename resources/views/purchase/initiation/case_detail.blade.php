<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Detail - #{{ $purchase->pcs_id }}</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }
        body {
            background-color: #fff;
            margin: 0;
            padding: 30px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            font-size: 10pt;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .main-title {
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 25px;
            text-align: left;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px 30px;
            margin-bottom: 30px;
        }
        .meta-item {
            display: flex;
            align-items: baseline;
        }
        .meta-label {
            font-weight: bold;
            width: 80px;
            flex-shrink: 0;
        }
        .meta-value {
            flex-grow: 1;
        }
        .section-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 8px;
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #999;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer {
            margin-top: 40px;
            font-size: 9pt;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #eee;
            padding-top: 10px;
            color: #666;
        }
        @media print {
            body { padding: 0; }
            .print-btn { display: none; }
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 99999;
        }
        .print-btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>

    <div class="container">
        @php
            $breakdown = $purchase->tax_breakdown;
            $winnerQuote = $purchase->winning_quote
                ?? $purchase->quotes->where('qte_recomm', true)->first()
                ?? $purchase->quotes->sortBy('qte_price')->first();

            $basePrice = (float)($breakdown['base'] ?? 0);
            $sst = (float)($breakdown['sst'] ?? 0);
            $gst = (float)($breakdown['gst'] ?? 0);
            $total = (float)($breakdown['total'] ?? ($purchase->pcs_price ?? 0));

            // If case has no pricing recorded yet, but quotes exist, use winning quote
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
            if (!empty($purchase->pcs_remarks)) {
                $rawRemarks = trim((string)$purchase->pcs_remarks);
                $decoded = json_decode($rawRemarks, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $termsText = $decoded['terms'] ?? $decoded['justification'] ?? $decoded['remarks'] ?? $rawRemarks;
                } else {
                    $termsText = $rawRemarks;
                }
            }
        @endphp

        <div class="main-title">
            Purchase Case {{ $purchase->pcs_id }} dated {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M y') }} ({{ $purchase->head_display }} Head)
        </div>

        <div class="meta-grid">
            <div class="meta-item"><span class="meta-label">Title:</span> <span class="meta-value">{{ $purchase->pcs_title }}</span></div>
            <div class="meta-item"><span class="meta-label"></span> <span class="meta-value"></span></div>
            <div class="meta-item"><span class="meta-label">Price:</span> <span class="meta-value text-right">{{ number_format($basePrice, 2) }}</span></div>
            
            <div class="meta-item"><span class="meta-label">Minute:</span> <span class="meta-value">{{ $purchase->pcs_minute }}</span></div>
            <div class="meta-item"><span class="meta-label">Head:</span> <span class="meta-value">({{ $purchase->head_display }})</span></div>
            <div class="meta-item"><span class="meta-label">SST:</span> <span class="meta-value text-right">{{ number_format($sst, 2) }}</span></div>
            
            <div class="meta-item"><span class="meta-label">Date:</span> <span class="meta-value">{{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M y') }}</span></div>
            <div class="meta-item"><span class="meta-label">Subhead:</span> <span class="meta-value" style="font-weight: 600;">{{ $purchase->subhead_display }}</span></div>
            <div class="meta-item"><span class="meta-label">GST:</span> <span class="meta-value text-right">{{ number_format($gst, 2) }}</span></div>
            
            <div class="meta-item"><span class="meta-label">Initiator:</span> <span class="meta-value">{{ $purchase->unit?->unt_namesh ?? 'Division' }}</span></div>
            <div class="meta-item"><span class="meta-label">Status:</span> <span class="meta-value">{{ $purchase->pcs_status }}</span></div>
            <div class="meta-item"><span class="meta-label">Total:</span> <span class="meta-value text-right" style="font-weight: bold;">{{ number_format($total, 2) }}</span></div>
            
            <div class="meta-item" style="grid-column: span 3;">
                <span class="meta-label">Firm:</span> <span class="meta-value">{{ $firmName }}</span>
            </div>
        </div>

        <span class="section-title">Items: ({{ $purchase->items->count() }})</span>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">S No</th>
                    <th>Description</th>
                    <th style="width: 120px;" class="text-center">Price & Qty</th>
                    <th style="width: 150px;" class="text-center">Type & SubType</th>
                    <th style="width: 150px;" class="text-center">Inv-Asset & S/Head</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items->sortBy('pci_serial') as $idx => $item)
                @php
                    $itemQty = (float)($item->pci_qty ?? 1) ?: 1;
                    $itemRate = (float)($item->pci_price ?? 0);
                    $itemTotal = $itemRate * $itemQty;
                    $itemType = $item->type_name;
                    $itemSubType = $item->pci_subtype ?: '—';
                    $itemClass = $item->type2_name;
                    $itemSubhead = $item->pci_subhead ?: ($purchase->subhead_display ?: 'Equipment');
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item->pci_desc }}</td>
                    <td class="text-center">
                        <div>{{ number_format($itemTotal, 2) }}</div>
                        <div style="color: #555; font-size: 8.5pt;">{{ $item->pci_qty }} {{ $item->pci_qtyunit ?: 'num' }}</div>
                        @if($itemQty > 1)
                            <div style="font-size: 7.5pt; color: #777;">(@ {{ number_format($itemRate, 2) }})</div>
                        @endif
                    </td>
                    <td class="text-center">
                        <div style="font-weight: 600;">{{ $itemType }}</div>
                        <div style="color: #555; font-size: 8.5pt;">{{ $itemSubType }}</div>
                    </td>
                    <td class="text-center">
                        <div style="font-weight: 600;">{{ $itemClass }}</div>
                        <div style="color: #555; font-size: 8.5pt;">{{ $itemSubhead }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-bottom: 20px;">
            <strong>Terms and Conditions:</strong> {{ $termsText }}
        </div>

        <span class="section-title">Quotes: ({{ $purchase->quotes->count() }})</span>
        <table>
            <thead>
                <tr>
                    <th style="width: 100px;">No.</th>
                    <th style="width: 100px;">Date</th>
                    <th>Firm</th>
                    <th style="width: 120px;" class="text-right">Price</th>
                    <th style="width: 120px;" class="text-center">Tech. Acceptable</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchase->quotes->sortBy('qte_price') as $q)
                <tr>
                    <td>{{ $q->qte_id }}</td>
                    <td>{{ \Carbon\Carbon::parse($q->qte_date)->format('d M y') }}</td>
                    <td>
                        {{ $q->firm->frm_name ?? $q->qte_firmname }}
                        @if($q->qte_recomm)
                            <span style="color: #166534; font-size: 8pt; font-weight: bold; margin-left: 5px;">(Lowest/Recommended)</span>
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($q->qte_price, 2) }}</td>
                    <td class="text-center">{{ $q->qte_techaccept ? 'Yes' : 'No' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 15px; font-style: italic;">No quotes recorded for this case.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-bottom: 20px;">
            <span class="section-title">Quotes Not Received: ({{ $purchase->noQuotes->count() }})</span>
            <div style="border: 1px solid #999; padding: 10px; min-height: 30px;">
                @forelse($purchase->noQuotes as $nq)
                    {{ $nq->firm->frm_name ?? $nq->nqt_firmname }}@if(!$loop->last), @endif
                @empty
                    <span style="color: #666; font-style: italic;">None</span>
                @endforelse
            </div>
        </div>

        @if(!empty($purchase->pcs_remarks))
            @php
                $decoded = json_decode((string)$purchase->pcs_remarks, true);
            @endphp
            @if(is_array($decoded) && !empty($decoded['justification']) && $decoded['justification'] !== $termsText)
                <div style="margin-bottom: 20px;">
                    <strong>Remarks:</strong> {{ $decoded['justification'] }}
                </div>
            @endif
        @endif

        <div class="footer">
            <span>1 of 1</span>
            <span>Printed on {{ date('d M y H:i') }}</span>
        </div>
    </div>

</body>
</html>
