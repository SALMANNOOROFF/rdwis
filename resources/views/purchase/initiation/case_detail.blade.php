<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Detail - #{{ $purchase->pcs_id }}</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        body {
            background-color: #fff;
            margin: 0;
            padding: 40px;
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
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>

    <div class="container">
        <div class="main-title">
            Purchase Case {{ $purchase->pcs_id }} dated {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M y') }}({{ $purchase->project?->prj_code ?? 'N/A' }} Head)
        </div>

        @php
            $winnerQuote = $purchase->quotes->sortBy('qte_price')->first();
            $price = (float)($purchase->pcs_price ?? ($winnerQuote?->qte_price ?? 0));
            $gstRate = 0.18; // Standard 18% if not explicitly stored
            $gst = $price * $gstRate;
            $total = $price + $gst;
        @endphp

        <div class="meta-grid">
            <div class="meta-item"><span class="meta-label">Title:</span> <span class="meta-value">{{ $purchase->pcs_title }}</span></div>
            <div class="meta-item"><span class="meta-label"></span> <span class="meta-value"></span></div>
            <div class="meta-item"><span class="meta-label">Price:</span> <span class="meta-value text-right">{{ number_format($price, 2) }}</span></div>
            
            <div class="meta-item"><span class="meta-label">Minute:</span> <span class="meta-value">{{ $purchase->pcs_minute }}</span></div>
            <div class="meta-item"><span class="meta-label">Head:</span> <span class="meta-value">({{ $purchase->project?->prj_code ?? 'N/A' }})</span></div>
            <div class="meta-item"><span class="meta-label">SST:</span> <span class="meta-value text-right">0.00</span></div>
            
            <div class="meta-item"><span class="meta-label">Date:</span> <span class="meta-value">{{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M y') }}</span></div>
            <div class="meta-item"><span class="meta-label"></span> <span class="meta-value"></span></div>
            <div class="meta-item"><span class="meta-label">GST:</span> <span class="meta-value text-right">{{ number_format($gst, 2) }}</span></div>
            
            <div class="meta-item"><span class="meta-label">Initiator:</span> <span class="meta-value">{{ $purchase->unit?->unt_namesh ?? 'Division' }}</span></div>
            <div class="meta-item"><span class="meta-label">Status:</span> <span class="meta-value">{{ $purchase->pcs_status }}</span></div>
            <div class="meta-item"><span class="meta-label">Total:</span> <span class="meta-value text-right" style="font-weight: bold;">{{ number_format($total, 2) }}</span></div>
            
            <div class="meta-item" style="grid-column: span 3;">
                <span class="meta-label">Firm:</span> <span class="meta-value">{{ $winnerQuote?->firm->frm_name ?? ($winnerQuote?->qte_firmname ?? 'N/A') }}</span>
            </div>
        </div>

        <span class="section-title">Items: ({{ $purchase->items->count() }})</span>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">S No</th>
                    <th>Description</th>
                    <th style="width: 120px;">Price & Qty</th>
                    <th style="width: 150px;">Type & SubType</th>
                    <th style="width: 150px;">Inv-Asset & S/Head</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $item->pci_desc }}</td>
                    <td class="text-center">
                        {{ number_format($item->pci_price, 2) }}<br>
                        {{ $item->pci_qty }} {{ $item->pci_qtyunit }}
                    </td>
                    <td class="text-center">
                        {{ $item->pci_subtype ?? 'N/A' }}<br>
                        {{ $item->pci_type == 1 ? 'Permanent' : 'Consumable' }}
                    </td>
                    <td class="text-center">
                        Inventory<br>
                        {{ $item->pci_subhead ?? 'Equipment' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-bottom: 20px;">
            <strong>Terms and Conditions:</strong> Payment on delivery / Inspection at site.
        </div>

        <span class="section-title">Quotes: ({{ $purchase->quotes->count() }})</span>
        <table>
            <thead>
                <tr>
                    <th style="width: 120px;">No.</th>
                    <th style="width: 100px;">Date</th>
                    <th>Firm</th>
                    <th style="width: 120px;" class="text-right">Price</th>
                    <th style="width: 100px;" class="text-center">Tech. Acceptable</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->quotes as $q)
                <tr>
                    <td>{{ $q->qte_id }}</td>
                    <td>{{ \Carbon\Carbon::parse($q->qte_date)->format('d M y') }}</td>
                    <td>{{ $q->firm->frm_name ?? $q->qte_firmname }}</td>
                    <td class="text-right">{{ number_format($q->qte_price, 2) }}</td>
                    <td class="text-center">{{ $q->qte_techaccept ? 'Yes' : 'No' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-bottom: 20px;">
            <span class="section-title">Quotes Not Received: ({{ $purchase->noQuotes->count() }})</span>
            <div style="border: 1px solid #999; padding: 10px; min-height: 30px;">
                @foreach($purchase->noQuotes as $nq)
                    {{ $nq->firm->frm_name ?? $nq->nqt_firmname }}@if(!$loop->last), @endif
                @endforeach
            </div>
        </div>

        <div>
            <strong>Remarks:</strong> 
            @php $remarks = json_decode($purchase->pcs_remarks, true); @endphp
            {{ $remarks['justification'] ?? 'No additional remarks.' }}
        </div>

        <div class="footer">
            <span>1 of 1</span>
            <span>Printed on {{ date('d M y H:i') }}</span>
        </div>
    </div>

</body>
</html>
