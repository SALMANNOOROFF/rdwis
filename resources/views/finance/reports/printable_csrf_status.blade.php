<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CSRF Status Report</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 12mm 12mm 12mm;
        }
        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
            font-size: 9pt;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }
        .report-title {
            font-size: 16pt;
            font-weight: bold;
        }
        .top-totals-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 6px;
            margin-bottom: 15px;
            background: #f8fafc;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            text-align: right;
        }
        .kpi-cell .label {
            font-size: 7.5pt;
            text-transform: uppercase;
            font-weight: bold;
            color: #475569;
        }
        .kpi-cell .val {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            font-family: monospace;
        }
        table.csrf-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        table.csrf-table th, table.csrf-table td {
            border: 1px solid #94a3b8;
            padding: 3px 5px;
            font-size: 8pt;
        }
        table.csrf-table th {
            background-color: #f1f5f9;
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
        }
        table.csrf-table th.col-proj { text-align: left; }
        table.csrf-table td.col-proj { text-align: left; font-weight: bold; }
        table.csrf-table td.col-num { text-align: right; font-family: monospace; }
        .division-header-row {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .grand-total-row {
            background-color: #cbd5e1;
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }
        .no-print-bar {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .btn-print {
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-print:hover { background: #0369a1; }
        @media print {
            .no-print-bar { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <div>
        <strong>CSRF Status Official Printout</strong> &bull; Generated from Finance Records
    </div>
    <div>
        <button class="btn-print" onclick="window.print()">Print Official Sheet</button>
    </div>
</div>

<div class="report-header">
    <div class="report-title">CSRF Status</div>
    <div style="font-size: 9pt; color: #333;">Printed on {{ now()->format('d M y H:i') }}</div>
</div>

<div class="top-totals-grid">
    <div class="kpi-cell">
        <div class="label">Allocation</div>
        <div class="val">{{ number_format($grandTotals['allocation']) }}</div>
    </div>
    <div class="kpi-cell">
        <div class="label">Received</div>
        <div class="val">{{ number_format($grandTotals['received']) }}</div>
    </div>
    <div class="kpi-cell">
        <div class="label">Expenditure</div>
        <div class="val">{{ number_format($grandTotals['expenditure']) }}</div>
    </div>
    <div class="kpi-cell">
        <div class="label">Commitment</div>
        <div class="val">{{ number_format($grandTotals['commitment']) }}</div>
    </div>
    <div class="kpi-cell">
        <div class="label">In Process</div>
        <div class="val">{{ number_format($grandTotals['in_process']) }}</div>
    </div>
    <div class="kpi-cell">
        <div class="label" style="color: #0369a1;">Available</div>
        <div class="val" style="color: #0369a1;">{{ number_format($grandTotals['available']) }}</div>
    </div>
    <div class="kpi-cell">
        <div class="label" style="color: #b45309;">Remaining</div>
        <div class="val" style="color: #b45309;">{{ number_format($grandTotals['remaining']) }}</div>
    </div>
</div>

<table class="csrf-table">
    <thead>
        <tr>
            <th class="col-proj" style="width: 18%;">Projects</th>
            <th style="width: 12%;">Allocation</th>
            <th style="width: 12%;">Received</th>
            <th style="width: 12%;">Expenditure</th>
            <th style="width: 11%;">Commitment</th>
            <th style="width: 11%;">In Process</th>
            <th style="width: 12%;">Available</th>
            <th style="width: 12%;">Remaining</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupedRecords as $divName => $group)
            <tr class="division-header-row">
                <td class="col-proj">{{ $divName }}</td>
                <td class="col-num">{{ number_format($group['totals']['allocation']) }}</td>
                <td class="col-num">{{ number_format($group['totals']['received']) }}</td>
                <td class="col-num">{{ number_format($group['totals']['expenditure']) }}</td>
                <td class="col-num">{{ number_format($group['totals']['commitment']) }}</td>
                <td class="col-num">{{ number_format($group['totals']['in_process']) }}</td>
                <td class="col-num">{{ number_format($group['totals']['available']) }}</td>
                <td class="col-num">{{ number_format($group['totals']['remaining']) }}</td>
            </tr>
            @foreach($group['items'] as $item)
                <tr>
                    <td class="col-proj" style="padding-left: 12px; font-weight: normal;">
                        {{ $item['project'] }}
                    </td>
                    <td class="col-num">{{ number_format($item['allocation']) }}</td>
                    <td class="col-num">{{ number_format($item['received']) }}</td>
                    <td class="col-num">{{ number_format($item['expenditure']) }}</td>
                    <td class="col-num">{{ number_format($item['commitment']) }}</td>
                    <td class="col-num">{{ number_format($item['in_process']) }}</td>
                    <td class="col-num">{{ number_format($item['available']) }}</td>
                    <td class="col-num">{{ number_format($item['remaining']) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        <tr class="grand-total-row">
            <td class="col-proj">Grand Total</td>
            <td class="col-num">{{ number_format($grandTotals['allocation']) }}</td>
            <td class="col-num">{{ number_format($grandTotals['received']) }}</td>
            <td class="col-num">{{ number_format($grandTotals['expenditure']) }}</td>
            <td class="col-num">{{ number_format($grandTotals['commitment']) }}</td>
            <td class="col-num">{{ number_format($grandTotals['in_process']) }}</td>
            <td class="col-num">{{ number_format($grandTotals['available']) }}</td>
            <td class="col-num">{{ number_format($grandTotals['remaining']) }}</td>
        </tr>
    </tfoot>
</table>

<div style="margin-top: 15px; font-size: 8pt; color: #555; display: flex; justify-content: space-between;">
    <div>RDWIS Financial Intelligence &bull; Printed on {{ now()->format('d M y H:i') }}</div>
    <div>Page 1 of 1</div>
</div>

</body>
</html>
