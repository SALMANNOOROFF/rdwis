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
        .para-body ol {
            margin: 0;
            padding-left: 25px;
        }
        .para-body ol li {
            margin-bottom: 10px;
            line-height: 1.5;
            font-size: 11pt;
        }
        .para-body p {
            margin: 0 0 10px 0;
            line-height: 1.5;
            font-size: 11pt;
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
        $finService = app(\App\Services\FinancialIntelligenceService::class);
        $rawHeadStatus = isset($headStatus) && !empty($headStatus) ? $headStatus : $finService->getHeadStatus($purchase->pcs_hed_id);
        $headStatus = (array) $rawHeadStatus;
        $subheads = isset($subheads) && !empty($subheads) ? $subheads : $finService->getSubheadBreakdown($purchase->pcs_hed_id);

        $winnerQuote = count($purchase->quotes) > 0 ? $purchase->quotes->sortBy(function($q) {
            return (float)($q->qte_price ?: ($q->qte_midprice ?: ($q->qte_intprice ?? 0)));
        })->first() : null;
        
        $itemsTotal = $purchase->items->sum(function($item) {
            $qty = (float)($item->pci_qty ?? 1);
            $rate = (float)($item->pci_price ?: ($item->pci_rate ?: ($item->pci_estcost ?: ($item->pci_estprice ?? 0))));
            return ($rate > 0) ? ($rate * ($item->pci_price ? 1 : $qty)) : 0;
        });

        $caseValue = (float)($purchase->live_value ?? ($purchase->pcs_price ?: ($winnerQuote?->qte_price ?: ($itemsTotal ?: 0))));
        
        $projectName = $headStatus['head_name'] ?? ($purchase->project?->prj_title ?? 'Project');
        $projectCode = $headStatus['head_code'] ?? ($purchase->project?->prj_code ?? 'N/A');

        $finProjectShare = (float)($headStatus['acc_share'] ?? ($headStatus['allocation'] ?? ($purchase->project?->prj_aprvcost ?? 0)));
        $finReceived     = (float)($headStatus['received'] ?? ($headStatus['acc_received'] ?? $finProjectShare));
        if ($finReceived <= 0 && $finProjectShare > 0) {
            $finReceived = $finProjectShare;
        }
        $finExpenditure  = (float)($headStatus['expenditure'] ?? ($headStatus['acc_expenditure'] ?? 0));
        $finCommitments  = (float)($headStatus['commitments'] ?? ($headStatus['acc_commitments'] ?? 0));
        $finInProcess    = (float)($headStatus['in_process'] ?? ($headStatus['acc_in_process'] ?? $caseValue));
        if ($finInProcess <= 0) {
            $finInProcess = $caseValue;
        }
        $finAvailable    = (float)($headStatus['can_be_spent'] ?? ($headStatus['acc_available'] ?? ($finReceived - $finExpenditure - $finCommitments - $finInProcess)));
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

        @php 
            $baseMinute = (int)($purchase->pcs_minute ?? 1);
            if ($baseMinute <= 0) $baseMinute = 1;
            $minCounter = $baseMinute;
        @endphp

        <div class="minute-title">Minute-{{ $baseMinute }}</div>

        <div class="para-row">
            <div class="para-num">1.</div>
            <div class="para-body">
                Purchase case listed below is required for <strong>{{ $projectName }}</strong> (Head: {{ $projectCode }}).
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
                        @forelse($purchase->items as $idx => $item)
                        @php
                            $itemPrice = (float)($item->pci_price ?: (($item->pci_rate ? $item->pci_rate * ($item->pci_qty ?: 1) : 0) ?: ($item->pci_estcost ?: ($item->pci_estprice ?? 0))));
                            if ($itemPrice <= 0 && $caseValue > 0 && $purchase->items->count() === 1) {
                                $itemPrice = $caseValue;
                            }
                        @endphp
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td>{{ $item->pci_desc }}</td>
                            <td class="text-center">{{ $item->pci_qty }} {{ $item->pci_qtyunit }}</td>
                            <td class="text-right">{{ number_format($itemPrice) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="text-center">1</td>
                            <td>{{ $purchase->pcs_title ?? 'Purchase Item' }}</td>
                            <td class="text-center">1 Item</td>
                            <td class="text-right">{{ number_format($caseValue) }}</td>
                        </tr>
                        @endforelse
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
                Allocation for the project is <strong>Rs. {{ number_format($finProjectShare) }}</strong>. Tabulated below is the breakdown of Project share and current financial status:
                
                <p><strong>Account Figures:</strong></p>
                <table class="box-table" style="width: 80%;">
                    <tr><td style="width: 40px;" class="text-center">a.</td><td>Project Share</td><td class="text-right">{{ number_format($finProjectShare) }}</td></tr>
                    <tr><td class="text-center">b.</td><td>Received</td><td class="text-right">{{ number_format($finReceived) }}</td></tr>
                    <tr><td class="text-center">c.</td><td>Expenditure</td><td class="text-right">{{ number_format($finExpenditure) }}</td></tr>
                    <tr><td class="text-center">d.</td><td>Commitments</td><td class="text-right">{{ number_format($finCommitments) }}</td></tr>
                    <tr><td class="text-center">e.</td><td>In Process (Incl. Current Case)</td><td class="text-right">{{ number_format($finInProcess) }}</td></tr>
                    <tr><td class="text-center">f.</td><td>Available</td><td class="text-right @if($finAvailable < 0) text-danger @endif">{{ number_format($finAvailable) }}</td></tr>
                </table>

                <p style="margin-top: 15px;"><strong>Project Figures:</strong></p>
                <table class="box-table text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="text-align: left;">Description</th>
                            <th>Overall</th>
                            @foreach($subheads ?? [] as $sh)
                                <th>{{ is_array($sh) ? ($sh['name'] ?? '') : ($sh->name ?? '') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>k.</td>
                            <td style="text-align: left;">Max Spending Limit</td>
                            <td>{{ number_format($finProjectShare) }}</td>
                            @foreach($subheads ?? [] as $sh)
                                <td>{{ number_format((float)(is_array($sh) ? ($sh['allocation'] ?? 0) : ($sh->allocation ?? 0))) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>l.</td>
                            <td style="text-align: left;">Expenditure</td>
                            <td>{{ number_format($finExpenditure) }}</td>
                            @foreach($subheads ?? [] as $sh)
                                <td>{{ number_format((float)(is_array($sh) ? ($sh['expenditure'] ?? 0) : ($sh->expenditure ?? 0))) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>m.</td>
                            <td style="text-align: left;">Commitments</td>
                            <td>{{ number_format($finCommitments) }}</td>
                            @foreach($subheads ?? [] as $sh)
                                <td>{{ number_format((float)(is_array($sh) ? ($sh['commitments'] ?? 0) : ($sh->commitments ?? 0))) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>n.</td>
                            <td style="text-align: left;">In Process</td>
                            <td>{{ number_format($finInProcess) }}</td>
                            @foreach($subheads ?? [] as $sh)
                                <td>{{ number_format((float)(is_array($sh) ? ($sh['in_process'] ?? 0) : ($sh->in_process ?? 0))) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td>p.</td>
                            <td style="text-align: left;">Can be Spent</td>
                            <td>{{ number_format($finAvailable) }}</td>
                            @foreach($subheads ?? [] as $sh)
                                <td>{{ number_format((float)(is_array($sh) ? ($sh['remaining'] ?? 0) : ($sh->remaining ?? 0))) }}</td>
                            @endforeach
                        </tr>
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
            $initiatorDecision = $purchase->decisions->filter(function($d) {
                return strtolower($d->pdec_role) === 'initiator' || $d->pdec_from_status === 'Draft';
            })->first();
            $initAccount = $initiatorDecision?->account ?? \App\Models\CenAccount::where('acc_unt_id', $purchase->pcs_unt_id)->where('acc_untarea', 'prj')->first();
        @endphp
        <div class="signature-block">
            {{ trim(($initAccount?->acc_rank ? $initAccount->acc_rank . ' ' : '') . ($initAccount?->acc_name ?? 'Division Officer')) }}<br>
            {{ $initAccount?->acc_desig ?? ($initiatorDecision ? strtoupper($initiatorDecision->pdec_role) : 'INITIATOR') }}<br>
            {{ $purchase->unit?->unt_name }}<br>
            {{ \Carbon\Carbon::parse($initiatorDecision?->created_at ?? $purchase->pcs_date)->format('d M y') }}
        </div>

        <div style="clear: both;"></div>

        @php
            $subsequentParaRunningNumber = 5;
        @endphp

        {{-- Subsequent Minutes (Decisions Trail) --}}
        @foreach($purchase->decisions->where('pdec_action', '!=', 'save_draft')->sortBy('created_at') as $decision)
            @php
                $rawRemarks = $decision->pdec_remarks;
                $hasHtmlLi = !empty($rawRemarks) && strpos($rawRemarks, '<li') !== false;
                $hasRemarks = !empty(trim(strip_tags($rawRemarks)));
                
                if (!$hasRemarks && !in_array($decision->pdec_action, ['float_to_proc', 'reshare_to_proc', 'dproc_save', 'forward', 'approve', 'return', 'reject', 'not_approved'])) {
                    continue;
                }

                $minCounter++;

                if ($hasHtmlLi) {
                    $innerLis = preg_replace('/<\/?(ol|ul)[^>]*>/i', '', $rawRemarks);
                    $minuteHtml = '<ol start="' . $subsequentParaRunningNumber . '">' . $innerLis . '</ol>';
                    $count = max(1, substr_count($rawRemarks, '<li'));
                } elseif ($hasRemarks) {
                    $cleanText = trim(strip_tags($rawRemarks));
                    $minuteHtml = '<ol start="' . $subsequentParaRunningNumber . '"><li>' . e($cleanText) . '</li></ol>';
                    $count = 1;
                } else {
                    $defaultText = '';
                    if ($decision->pdec_action == 'dproc_save') {
                        $defaultText = 'Quotation details and scrutiny remarks saved by Director Procurement.';
                    } elseif ($decision->pdec_action == 'float_to_proc') {
                        $defaultText = 'Case floated to Procurement Department for quotation collection.';
                    } elseif ($decision->pdec_action == 'reshare_to_proc') {
                        $defaultText = 'Case reshared to Procurement Department for quotation correction.';
                    } elseif ($decision->pdec_action == 'approve') {
                        $defaultText = 'Case approved.';
                    } elseif ($decision->pdec_action == 'return') {
                        $defaultText = 'Case returned.';
                    } else {
                        $defaultText = 'Case forwarded for approval.';
                    }
                    $minuteHtml = '<ol start="' . $subsequentParaRunningNumber . '"><li>' . e($defaultText) . '</li></ol>';
                    $count = 1;
                }

                $subsequentParaRunningNumber += $count;
            @endphp
            <div style="margin-top: 40px; border-top: 1px dashed var(--border-color); padding-top: 20px;">
                <div class="minute-title" style="font-size: 14pt; margin: 10px 0;">
                    Minute-{{ $minCounter }}
                </div>
                <div class="para-row">
                    <div class="para-body">
                        {!! $minuteHtml !!}
                    </div>
                </div>
                <div class="signature-block" style="margin-top: 20px;">
                    {{ trim(($decision->account?->acc_rank ? $decision->account->acc_rank . ' ' : '') . ($decision->account?->acc_name ?? '')) }}<br>
                    {{ $decision->account?->acc_desig ?? strtoupper($decision->pdec_role) }}<br>
                    {{ \Carbon\Carbon::parse($decision->created_at)->format('d M y') }}
                </div>
                <div style="clear: both;"></div>
            </div>
        @endforeach
    </div>

</body>
</html>