<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Market Research Report - #{{ $purchase->pcs_id }}</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        body {
            background-color: #fff;
            margin: 0;
            padding: 40px;
            font-family: 'Arial', sans-serif;
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
            text-align: center;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .meta-item {
            display: flex;
        }
        .meta-label {
            font-weight: bold;
            width: 120px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f9f9f9;
        }
        .firm-header {
            background-color: #eee;
            font-weight: bold;
            padding: 8px;
            margin-top: 20px;
            border: 1px solid #ccc;
        }
        .sig-container {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .sig-box {
            width: 200px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 9pt;
        }
        .footer {
            margin-top: 50px;
            font-size: 8pt;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        @media print {
            body { padding: 0; }
            .print-btn { display: none; }
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
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
        <div class="main-title">Market Research Report</div>

        <div class="meta-row">
            <div>
                <div class="meta-item"><span class="meta-label">Purchase Case:</span> {{ $purchase->pcs_id }} dated {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M y') }}</div>
                <div class="meta-item"><span class="meta-label">Title:</span> {{ $purchase->pcs_title }}</div>
                <div class="meta-item"><span class="meta-label">Initiated by:</span> {{ $purchase->unit?->unt_name }}</div>
            </div>
            <div>
                <div class="meta-item"><span class="meta-label">Head:</span> {{ $purchase->project?->prj_code ?? 'N/A' }}</div>
            </div>
        </div>

        @foreach($purchase->quotes as $q)
        <div class="firm-header">
            M/s {{ $q->firm->frm_name ?? $q->qte_firmname }} 
            <span style="float: right;">(Technically Acceptable: {{ $q->qte_techaccept ? 'Yes' : 'No' }})</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">S No</th>
                    <th>Items</th>
                    <th style="width: 60px;">Qty</th>
                    <th style="width: 100px; text-align: right;">Unit Price (Rs)</th>
                    <th style="width: 120px; text-align: right;">Price (Rs)</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $quoteItems = \DB::table('pur.quoteitems')->where('qti_qte_id', $q->qte_id)->get();
                    $total = 0;
                @endphp
                @foreach($purchase->items as $idx => $item)
                    @php 
                        $qi = $quoteItems->where('qti_pci_id', $item->pci_id)->first();
                        $price = $qi ? $qi->qti_price : 0;
                        $subtotal = $price * $item->pci_qty;
                        $total += $subtotal;
                    @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>{{ $item->pci_desc }}</td>
                    <td style="text-align: center;">{{ $item->pci_qty }}</td>
                    <td style="text-align: right;">{{ number_format($price, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($subtotal, 2) }}</td>
                </tr>
                @endforeach
                <tr style="font-weight: bold; background: #f9f9f9;">
                    <td colspan="4" style="text-align: right;">Total</td>
                    <td style="text-align: right;">{{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>
        @endforeach

        <div style="margin-top: 20px;">
            <strong>Quotes Not Received:</strong>
            <ul style="margin-top: 5px;">
                @forelse($purchase->noQuotes as $nq)
                    <li>{{ $nq->firm->frm_name ?? $nq->nqt_firmname }}</li>
                @empty
                    <li>None</li>
                @endforelse
            </ul>
        </div>

        <div style="margin-top: 20px;">
            <strong>Recommendation:</strong><br>
            @php 
                $winner = $purchase->quotes->sortBy('qte_price')->first();
                $remarks = json_decode($purchase->pcs_remarks, true);
            @endphp
            @if($winner)
                Offer of M/s {{ $winner->firm->frm_name ?? $winner->qte_firmname }} is recommended as it is the technically acceptable lowest offer.
            @else
                {{ $remarks['justification'] ?? 'Case is being processed for market research.' }}
            @endif
        </div>

        <div class="sig-container">
            <div class="sig-box">
                Director Procurement<br>
                R & D Wing, NRDI
            </div>
            <div class="sig-box">
                Dir Comm<br>
                R & D Wing, NRDI
            </div>
            <div class="sig-box">
                Director Finance<br>
                R & D Wing, NRDI
            </div>
            <div class="sig-box">
                MD RDW<br>
                NRDI
            </div>
        </div>

        <div class="footer">
            Page 1 of 1 | Printed on {{ date('d M Y H:i') }}
        </div>
    </div>

</body>
</html>
