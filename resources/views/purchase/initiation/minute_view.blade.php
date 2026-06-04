<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Minute Sheet - Case #{{ $purchase->pcs_id }}</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&family=Arial&display=swap" rel="stylesheet">
    <style>
        :root {
            --paper-w: 8.5in;
            --paper-h: 14in;
            --tint: #f4fae8;
            --border-color: #2d6a4f;
            --text-dark: #1b4332;
        }
        body {
            background-color: #525659;
            margin: 0;
            padding: 20px;
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
        }
        .minute-sheet {
            width: var(--paper-w);
            min-height: var(--paper-h);
            background-color: var(--tint);
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            padding: 0.7in;
            box-sizing: border-box;
            position: relative;
            color: #000;
        }
        .inner-border {
            position: absolute;
            top: 0.35in;
            bottom: 0.35in;
            left: 0.35in;
            right: 0.35in;
            border: 1px solid var(--border-color);
            pointer-events: none;
            opacity: 0.4;
        }
        .header-meta {
            display: flex;
            justify-content: space-between;
            font-size: 11pt;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
        }
        .minute-title {
            text-align: center;
            font-family: 'Times New Roman', serif;
            font-weight: bold;
            font-size: 18pt;
            text-decoration: underline;
            margin: 20px 0;
            color: var(--text-dark);
        }
        .para-row {
            display: flex;
            margin-bottom: 15px;
            font-size: 11pt;
            line-height: 1.5;
            text-align: justify;
        }
        .para-num {
            width: 35px;
            font-weight: bold;
            flex-shrink: 0;
        }
        .para-body {
            flex: 1;
        }
        .box-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            border: 1px solid #000;
            background: rgba(255,255,255,0.3);
        }
        .box-table th, .box-table td {
            border: 1px solid #000;
            padding: 6px 10px;
            font-size: 10pt;
        }
        .box-table th {
            background: rgba(45, 106, 79, 0.1);
            text-align: center;
            font-weight: bold;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .signature-block {
            margin-top: 50px;
            float: right;
            text-align: left;
            font-weight: bold;
            font-size: 11pt;
            min-width: 200px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--text-dark);
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            z-index: 1000;
        }
        @media print {
            body { background: none; padding: 0; }
            .minute-sheet { box-shadow: none; margin: 0; width: 100%; background: #fff; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Minute Sheet</button>

    @php
        $service = app(\App\Services\PurchaseApprovalService::class);
        $winnerQuote = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy('qte_price')->first() : null;
        $caseValue = (float)($purchase->pcs_price ?? ($winnerQuote?->qte_price ?? 0));
        
        $finReceived    = (float)($head->prj_aprvcost ?? 0);
        $finBalance     = (float)($head->hed_balance ?? $finReceived);
        $finExpenditure = $finReceived - $finBalance;
        $finCommitments = 0;
        $finInProcess   = $caseValue;
        $finAvailable   = $finBalance - $finCommitments - $finInProcess;
    @endphp

    <div class="minute-sheet">
        <div class="inner-border"></div>

        <div class="header-meta">
            <div>
                <strong>Ref:</strong> #{{ $purchase->pcs_id }}<br>
                <strong>Dated:</strong> {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}
            </div>
            <div style="text-align: right;">
                <strong>From:</strong> {{ $purchase->unit?->unt_name }}<br>
                <strong>Subj:</strong> {{ $purchase->pcs_title }}
            </div>
        </div>

        <div class="minute-title">Minute-{{ $purchase->pcs_minute ?? '1' }}</div>

        <div class="para-row">
            <div class="para-num">1.</div>
            <div class="para-body">
                Purchase case listed below is required for <strong>{{ $head->prj_title ?? 'Project' }}</strong> (Head: {{ $head->prj_code ?? 'N/A' }}).
                <table class="box-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">S No</th>
                            <th>Description</th>
                            <th style="width: 80px;">Qty</th>
                            <th style="width: 120px;">Price (PKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $idx => $item)
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $item->pci_desc }}</td>
                            <td class="text-center">{{ $item->pci_qty }} {{ $item->pci_qtyunit }}</td>
                            <td class="text-right">{{ number_format($item->pci_price) }}</td>
                        </tr>
                        @endforeach
                        <tr style="font-weight: bold;">
                            <td colspan="3" class="text-right">Total</td>
                            <td class="text-right">{{ number_format($caseValue) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="para-row">
            <div class="para-num">2.</div>
            <div class="para-body">
                Detailed justification and market research for the above mentioned items have been processed and found technically suitable for the project requirements.
            </div>
        </div>

        <div class="para-row">
            <div class="para-num">3.</div>
            <div class="para-body">
                Allocation for the project is <strong>Rs. {{ number_format($finReceived) }}</strong>. Tabulated below is the breakdown of Project share and current financial status:
                
                <p><strong>Account Figures:</strong></p>
                <table class="box-table" style="width: 80%;">
                    <tr><td style="width: 40px;" class="text-center">a.</td><td>Project Share</td><td class="text-right">{{ number_format($finReceived) }}</td></tr>
                    <tr><td class="text-center">b.</td><td>Received</td><td class="text-right">{{ number_format($finReceived) }}</td></tr>
                    <tr><td class="text-center">c.</td><td>Expenditure</td><td class="text-right">{{ number_format($finExpenditure) }}</td></tr>
                    <tr><td class="text-center">d.</td><td>Commitments</td><td class="text-right">{{ number_format($finCommitments) }}</td></tr>
                    <tr><td class="text-center">e.</td><td>In Process (Incl. Current Case)</td><td class="text-right">{{ number_format($finInProcess) }}</td></tr>
                    <tr><td class="text-center">f.</td><td>Available</td><td class="text-right @if($finAvailable < 0) text-danger @endif">{{ number_format($finAvailable) }}</td></tr>
                </table>

                <p style="margin-top: 15px;"><strong>Project Figures:</strong></p>
                <table class="box-table text-center">
                    <thead>
                        <tr><th>#</th><th style="text-align: left;">Description</th><th>Overall</th><th>Equipment</th><th>HR</th><th>Misc</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>k.</td><td style="text-align: left;">Max Spending Limit</td><td>{{ number_format($finReceived) }}</td><td>-</td><td>-</td><td>-</td></tr>
                        <tr><td>l.</td><td style="text-align: left;">Expenditure</td><td>{{ number_format($finExpenditure) }}</td><td>-</td><td>-</td><td>-</td></tr>
                        <tr><td>m.</td><td style="text-align: left;">Commitments</td><td>{{ number_format($finCommitments) }}</td><td>-</td><td>-</td><td>-</td></tr>
                        <tr><td>n.</td><td style="text-align: left;">In Process</td><td>{{ number_format($finInProcess) }}</td><td>-</td><td>-</td><td>-</td></tr>
                        <tr><td>p.</td><td style="text-align: left;">Can be Spent</td><td>{{ number_format($finAvailable) }}</td><td>-</td><td>-</td><td>-</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="para-row">
            <div class="para-num">4.</div>
            <div class="para-body">
                Foregoing in view, approval in principle of <strong>Rs. {{ number_format($caseValue) }}</strong> may please be accorded to process the purchase requirement mentioned at para 1 above.
            </div>
        </div>

        @php 
            $initiatorDecision = $purchase->decisions->where('pdec_role', 'Initiator')->first();
        @endphp
        <div class="signature-block">
            {{ $initiatorDecision->account->acc_name ?? 'Division Officer' }}<br>
            {{ strtoupper($initiatorDecision->pdec_role ?? 'INITIATOR') }}<br>
            {{ $purchase->unit?->unt_name }}<br>
            {{ \Carbon\Carbon::parse($initiatorDecision->created_at ?? $purchase->pcs_date)->format('d M y') }}
        </div>

        <div style="clear: both;"></div>

        {{-- Subsequent Minutes (Decisions Trail) --}}
        @foreach($purchase->decisions->sortBy('created_at') as $decision)
            @if($decision->pdec_role != 'Initiator')
                <div style="margin-top: 40px; border-top: 1px dashed var(--border-color); padding-top: 20px;">
                    <div class="minute-title" style="font-size: 14pt; margin: 10px 0;">Minute-{{ $loop->iteration }}</div>
                    <div class="para-row">
                        <div class="para-body">
                            {!! $decision->pdec_remarks ?: '<p>Case ' . $decision->pdec_action . '.</p>' !!}
                        </div>
                    </div>
                    <div class="signature-block" style="margin-top: 20px;">
                        {{ $decision->account->acc_name }}<br>
                        {{ strtoupper($decision->pdec_role) }}<br>
                        {{ \Carbon\Carbon::parse($decision->created_at)->format('d M y') }}
                    </div>
                    <div style="clear: both;"></div>
                </div>
            @endif
        @endforeach
    </div>

</body>
</html>