{{-- resources/views/fin/salary_cases/print_minute.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $case->soc_minute_no }} - Case of {{ \Carbon\Carbon::createFromFormat('Y-m', $case->soc_month)->format('F Y') }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 15mm 20mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14pt;
            line-height: 1.5;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .minute-title {
            font-size: 18pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 25px;
        }
        p {
            margin: 0 0 15px 0;
            text-align: justify;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 10pt;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .group-header {
            font-weight: bold;
            text-decoration: underline;
            background-color: #f9f9f9;
        }
        .mono {
            font-family: 'Courier New', Courier, monospace;
        }
        .no-print {
            margin-bottom: 20px;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('salary_cases.export_word', $case->soc_id) }}" id="btnExportWord" style="text-decoration: none; padding: 8px 16px; font-weight: bold; cursor: pointer; background: #16a34a; color: #fff; border: none; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-family: Arial, sans-serif; font-size: 13px;">
            <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 20V4h7v5h5v11H6z"/></svg>
            Save as Word Doc (.doc)
        </a>
        <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; cursor: pointer; background: #2563eb; color: #fff; border: none; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; font-family: Arial, sans-serif; font-size: 13px;">
            <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M19 8h-1V3H6v5H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-9-3h4v3h-4V5zm7 15H7v-4h10v4zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/></svg>
            Print Minute Sheet
        </button>
    </div>

    <div class="text-center">
        <div class="minute-title">{{ $case->soc_minute_no }}</div>
    </div>

    <p>
        {!! nl2br(e($case->soc_para1_text ?: ("1.  {$case->soc_total_due_emps} employees are due for salary. Salaries of {$case->soc_total_paid_emps} employees are being paid for the month of " . \Carbon\Carbon::createFromFormat('Y-m', $case->soc_month)->format('M y') . " against projects mentioned below. {$case->soc_split_salaries_count} salaries are split into {$case->soc_split_payments_count} payments. Salary sheet is placed at " . $case->soc_encl_ref . "."))) !!}
    </p>

    <table>
        <thead>
            <tr>
                <th style="width: 32%;">Paid From</th>
                <th style="width: 14%; text-align: center;">Payments</th>
                <th style="width: 22%; text-align: right;">Amount</th>
                <th style="width: 22%; text-align: right;">Remaining</th>
                <th style="width: 10%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandPayments = 0;
                $grandAmount = 0;
            @endphp
            @foreach($minuteTable as $divisionGroup => $heads)
                <tr class="group-header">
                    <td colspan="5">{{ $divisionGroup }}</td>
                </tr>
                @foreach($heads as $head)
                    @php
                        $grandPayments += (int)$head['payments'];
                        $grandAmount += (float)$head['amount'];
                    @endphp
                    <tr>
                        <td style="padding-left: 15px;">{{ $head['paid_from'] }}</td>
                        <td class="text-center">{{ $head['payments'] }}</td>
                        <td class="text-right mono">{{ number_format($head['amount']) }}</td>
                        <td class="text-right mono">{{ number_format($head['remaining']) }}</td>
                        <td>{{ $head['remarks'] ?? '' }}</td>
                    </tr>
                @endforeach
            @endforeach
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td>Total</td>
                <td class="text-center">{{ $grandPayments }}</td>
                <td class="text-right mono">{{ number_format($grandAmount) }}</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <p>
        {!! nl2br(e($case->soc_para2_text)) !!}
    </p>

    <p>
        {!! nl2br(e($case->soc_para3_text ?: ("3. Foregoing in view, approval of Rs. " . number_format($case->soc_total_amount) . " (" . app(\App\Services\SalaryCaseService::class)->numberToWords((int)$case->soc_total_amount) . ") for salaries of {$case->soc_total_paid_emps} employees for month of " . \Carbon\Carbon::createFromFormat('Y-m', $case->soc_month)->format('M y') . " may please be accorded for release of the payment through {$case->soc_payment_channel}."))) !!}
    </p>
</body>
</html>
