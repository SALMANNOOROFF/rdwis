<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Annex 'A' - RDW Project Accounts</title>
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
            font-size: 9.5pt;
        }
        .header-box {
            text-align: center;
            position: relative;
            margin-bottom: 15px;
        }
        .annex-badge {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 11pt;
            font-weight: bold;
        }
        .report-title {
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .report-subtitle {
            font-size: 10pt;
            color: #222;
        }
        /* Top Totals KPI Grid */
        .top-totals-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
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
        table.accounts-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto;
        }
        table.accounts-table th, table.accounts-table td {
            border: 1px solid #64748b;
            padding: 3.5px 6px;
            font-size: 8.5pt;
        }
        table.accounts-table th {
            background-color: #f1f5f9;
            text-align: right;
            font-weight: bold;
        }
        table.accounts-table th.col-head { text-align: left; }
        table.accounts-table td.col-head { text-align: left; }
        table.accounts-table td.col-num { text-align: right; font-family: monospace; }
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
            padding: 6px 18px;
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
        <strong>Monthly Return (Annex 'A') Official Printout</strong> &bull; All figures in Millions of Rupees
    </div>
    <div>
        <button class="btn-print" onclick="window.print()">Print Official Return</button>
    </div>
</div>

<div class="header-box">
    <div class="annex-badge">Annex 'A'</div>
    <div class="report-title">RDW Project Accounts - {{ $monthFormatted ?? now()->format('M y') }}</div>
    <div class="report-subtitle">All figures are in Millions of Ruppees</div>
</div>

<!-- Top Grand Totals Summary Grid -->
<div class="top-totals-grid">
    <div class="kpi-cell">
        <div class="label">Allocation</div>
        <div class="val">{{ number_format($grandTotals['allocation'], 2) }}M</div>
    </div>
    <div class="kpi-cell">
        <div class="label">MTSS Share</div>
        <div class="val">{{ number_format($grandTotals['mtss_share'], 2) }}M</div>
    </div>
    <div class="kpi-cell">
        <div class="label">NRDI Share</div>
        <div class="val">{{ number_format($grandTotals['nrdi_share'], 2) }}M</div>
    </div>
    <div class="kpi-cell">
        <div class="label" style="color: #15803d;">Received</div>
        <div class="val" style="color: #15803d;">{{ number_format($grandTotals['received'], 2) }}M</div>
    </div>
    <div class="kpi-cell">
        <div class="label" style="color: #b91c1c;">Expended</div>
        <div class="val" style="color: #b91c1c;">{{ number_format($grandTotals['expended'], 2) }}M</div>
    </div>
    <div class="kpi-cell">
        <div class="label" style="color: #0369a1;">Balance With MTSS</div>
        <div class="val" style="color: #0369a1;">{{ number_format($grandTotals['balance_with_mtss'], 2) }}M</div>
    </div>
</div>

<table class="accounts-table">
    <thead>
        <tr>
            <th class="col-head" style="width: 25%;">Head</th>
            <th style="width: 12%;">Allocation</th>
            <th style="width: 12%;">MTSS Share</th>
            <th style="width: 13%;">NRDI Share</th>
            <th style="width: 12%;">Received</th>
            <th style="width: 12%;">Expended</th>
            <th style="width: 14%;">Balance<br>With MTSS</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupedRecords as $divName => $group)
            <!-- Division Header / Separator Row with Subtotals -->
            <tr class="division-header-row">
                <td class="col-head" style="font-weight: bold; background: #e2e8f0;">
                    {{ $divName }}
                </td>
                <td class="col-num" style="font-weight: bold;">{{ number_format($group['totals']['allocation'], 2) }}</td>
                <td class="col-num" style="font-weight: bold;">{{ number_format($group['totals']['mtss_share'], 2) }}</td>
                <td class="col-num" style="font-weight: bold;">{{ number_format($group['totals']['nrdi_share'], 2) }}</td>
                <td class="col-num" style="font-weight: bold;">{{ number_format($group['totals']['received'], 2) }}</td>
                <td class="col-num" style="font-weight: bold;">{{ number_format($group['totals']['expended'], 2) }}</td>
                <td class="col-num" style="font-weight: bold;">{{ number_format($group['totals']['balance_with_mtss'], 2) }}</td>
            </tr>
            <!-- Individual Head Rows -->
            @foreach($group['items'] as $item)
                <tr>
                    <td class="col-head" style="padding-left: 14px; font-weight: 500;">
                        {{ $item['head'] }}
                    </td>
                    <td class="col-num">{{ number_format($item['allocation'], 2) }}</td>
                    <td class="col-num">{{ number_format($item['mtss_share'], 2) }}</td>
                    <td class="col-num">{{ number_format($item['nrdi_share'], 2) }}</td>
                    <td class="col-num">{{ number_format($item['received'], 2) }}</td>
                    <td class="col-num">{{ number_format($item['expended'], 2) }}</td>
                    <td class="col-num">{{ number_format($item['balance_with_mtss'], 2) }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
    <tfoot>
        <tr class="grand-total-row">
            <td class="col-head" style="font-weight: bold;">Grand Total (Summary)</td>
            <td class="col-num" style="font-weight: bold;">{{ number_format($grandTotals['allocation'], 2) }}</td>
            <td class="col-num" style="font-weight: bold;">{{ number_format($grandTotals['mtss_share'], 2) }}</td>
            <td class="col-num" style="font-weight: bold;">{{ number_format($grandTotals['nrdi_share'], 2) }}</td>
            <td class="col-num" style="font-weight: bold;">{{ number_format($grandTotals['received'], 2) }}</td>
            <td class="col-num" style="font-weight: bold;">{{ number_format($grandTotals['expended'], 2) }}</td>
            <td class="col-num" style="font-weight: bold;">{{ number_format($grandTotals['balance_with_mtss'], 2) }}</td>
        </tr>
    </tfoot>
</table>

<div style="margin-top: 15px; font-size: 8pt; color: #555; display: flex; justify-content: space-between;">
    <div>RDWIS Financial Intelligence &bull; Printed on {{ now()->format('d M y') }}</div>
    <div>Page 1 of 1</div>
</div>

</body>
</html>
