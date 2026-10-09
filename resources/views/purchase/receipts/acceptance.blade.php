<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Case Items Acceptance &mdash; Receipt #{{ $receipt->prt_id }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            color: #000000;
            background: #cbd5e1;
            margin: 0;
            padding: 20px 0;
            line-height: 1.35;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .screen-toolbar {
            max-width: 210mm;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 10px 18px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 18px;
            font-size: 13.5px;
            font-weight: bold;
            font-family: Arial, sans-serif;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #334155;
            transition: all 0.2s;
        }
        .btn-action:hover {
            background: #f1f5f9;
        }
        .btn-print {
            background: #15803d;
            color: #ffffff;
            border-color: #15803d;
        }
        .btn-print:hover {
            background: #166534;
            color: #ffffff;
        }

        /* Printable Sheet (Standard A4 Dimensions) */
        .sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 15mm 15mm 15mm 15mm;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            box-sizing: border-box;
            position: relative;
        }

        .report-title {
            font-family: Arial, sans-serif;
            font-size: 18pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 16px 0;
            color: #000000;
            letter-spacing: -0.2px;
        }

        /* Top Metadata Header (3 Columns matching Legacy Report) */
        .meta-table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
            font-size: 11pt;
        }
        .meta-table td {
            padding: 2.5px 0;
            vertical-align: top;
            color: #000000;
        }
        .meta-label {
            font-weight: bold;
        }

        /* Section Labels */
        .section-label {
            font-weight: bold;
            font-size: 11.5pt;
            margin-bottom: 5px;
            color: #000000;
        }

        /* Boxed Purchase Case Details */
        .pc-box {
            border: 1.5px solid #000000;
            border-radius: 3px;
            padding: 9px 12px;
            margin-bottom: 18px;
            page-break-inside: avoid;
        }
        .pc-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        .pc-grid td {
            padding: 2.5px 5px;
            vertical-align: top;
            color: #000000;
        }

        /* Accepted Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            margin-bottom: 24px;
        }
        .items-table th {
            border-bottom: 1.5px solid #000000;
            border-right: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11pt;
            background: #ffffff;
            color: #000000;
        }
        .items-table th:last-child {
            border-right: none;
        }
        .items-table td {
            border-bottom: 1px solid #cbd5e1;
            border-right: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 10.5pt;
            vertical-align: top;
            color: #000000;
        }
        .items-table td:last-child {
            border-right: none;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }

        /* Verification Notes */
        .cert-note {
            font-size: 11pt;
            margin-bottom: 8px;
            color: #000000;
            line-height: 1.4;
            page-break-inside: avoid;
        }

        /* Signature Blocks 1 (4 Columns with generous room for physical signatures) */
        .sig-row-4 {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 55px;
            margin-bottom: 35px;
            padding: 0 4px;
            page-break-inside: avoid;
        }
        .sig-block-4 {
            text-align: center;
            width: 22%;
        }
        .sig-line {
            border-top: 1.2px solid #000000;
            margin-bottom: 6px;
            width: 100%;
        }
        .sig-title {
            font-size: 10.5pt;
            color: #000000;
        }

        /* Signature Blocks 2 (Custodian on left, Director on right - on the exact same horizontal level) */
        .sig-row-acceptance {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-top: 60px;
            margin-bottom: 25px;
            padding: 0 15px;
            page-break-inside: avoid;
        }
        .sig-block-custodian {
            text-align: center;
            width: 250px;
        }
        .sig-block-director {
            text-align: center;
            width: 380px;
        }

        /* Remarks Notes Legend Box */
        .remarks-notes-box {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px dashed #94a3b8;
            font-size: 9.5pt;
            color: #475569; /* lighter / subtle letter */
            line-height: 1.5;
            page-break-inside: avoid;
        }
        .remarks-notes-title {
            font-weight: bold;
            color: #334155;
            margin-bottom: 3px;
            font-size: 10pt;
        }
        .remarks-notes-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2px 16px;
        }

        /* Footer */
        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9pt;
            color: #64748b;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .screen-toolbar {
                display: none !important;
            }
            .sheet {
                box-shadow: none !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
            }
        }
    </style>
</head>
<body>

    <!-- Screen Controls Toolbar -->
    <div class="screen-toolbar">
        <div>
            <a href="{{ route('purchase.receipts.show', $receipt->prt_id) }}" class="btn-action">
                <i class="fas fa-arrow-left"></i> Back to Receipt #{{ $receipt->prt_id }}
            </a>
        </div>
        <div>
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fas fa-print"></i> Print Acceptance Form
            </button>
        </div>
    </div>

    <!-- A4 Printable Sheet -->
    <div class="sheet">
        <h1 class="report-title">Purchase Case Items Acceptance</h1>

        <!-- Top Metadata Header (3 Columns from Database) -->
        <table class="meta-table">
            <tr>
                <td style="width: 34%;">
                    <span class="meta-label">Receipt ID:</span> {{ $receipt->prt_id }}
                </td>
                <td style="width: 36%;">
                    <span class="meta-label">Division:</span> {{ $receipt->int_unt_name ?? ($receipt->unt_name ?? ($receipt->int_unt_namesh ?? ($receipt->unt_namesh ?? 'N/A'))) }}
                </td>
                <td style="width: 30%; text-align: right;">
                    <span class="meta-label">Value:</span> {{ number_format((float)($receiptValue ?? ($receipt->prt_value ?? 0)), 2) }}
                </td>
            </tr>
            <tr>
                <td>
                    <span class="meta-label">Date:</span> {{ $receipt->prt_date ? date('d M y', strtotime($receipt->prt_date)) : 'N/A' }}
                </td>
                <td>
                    <span class="meta-label">Project:</span> {{ $receipt->hed_code ?? 'N/A' }}
                </td>
                <td></td>
            </tr>
        </table>

        <!-- Purchase Case Section (Boxed Details from Database) -->
        <div class="section-label">Purchase Case</div>
        <div class="pc-box">
            <table class="pc-grid">
                <tr>
                    <td style="width: 15%; font-weight: bold;">Case ID:</td>
                    <td style="width: 17%;">{{ $receipt->pcs_id }}</td>
                    <td style="width: 8%; font-weight: bold;">Date:</td>
                    <td style="width: 16%;">{{ $receipt->pcs_date ? date('d M y', strtotime($receipt->pcs_date)) : 'N/A' }}</td>
                    <td style="width: 10%; font-weight: bold;">Minute:</td>
                    <td style="width: 10%;">{{ $receipt->pcs_minute ?? 'N/A' }}</td>
                    <td style="width: 10%; font-weight: bold;">Price:</td>
                    <td style="width: 14%; text-align: right;">{{ number_format($pcsPrice, 2) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Title:</td>
                    <td colspan="5">{{ $receipt->pcs_title }}</td>
                    <td style="font-weight: bold;">SST:</td>
                    <td style="text-align: right;">{{ number_format($pcsSst, 2) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Head:</td>
                    <td colspan="3">{{ $receipt->hed_code }}</td>
                    <td style="font-weight: bold;">Status:</td>
                    <td>{{ $receipt->pcs_status }}</td>
                    <td style="font-weight: bold;">GST:</td>
                    <td style="text-align: right;">{{ number_format($pcsGst, 2) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Firm:</td>
                    <td colspan="5">{{ $receipt->frm_name ?? 'N/A' }}</td>
                    <td style="font-weight: bold;">Total:</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($pcsTotal, 2) }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; vertical-align: top;">Terms and Conditions:</td>
                    <td colspan="7">{{ $receipt->pcs_remarks ?: 'Complete payment after delivery' }}</td>
                </tr>
            </table>
        </div>

        <!-- Accepted Items Section -->
        <div class="section-label">Accepted Items</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 7%; text-align: center;">Serial</th>
                    <th style="width: 48%;">Description</th>
                    <th style="width: 12%; text-align: center;">Quantity</th>
                    <th style="width: 15%; text-align: center;">Denomination</th>
                    <th style="width: 18%; text-align: center;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $idx => $item)
                    <tr>
                        <td style="text-align: center;">{{ $item->pti_serial ?: ($idx + 1) }}</td>
                        <td>{{ $item->pti_desc }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ $item->pti_qty }}</td>
                        <td style="text-align: center;">{{ $item->pti_qtyunit ?? 'num' }}</td>
                        <td style="text-align: center; font-size: 10pt;">{{ $item->pti_remarks ?? '' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 14px;">No items recorded for this receipt.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Inspection Certification & Signatures -->
        <div class="cert-note">
            1. Above items are physically inspected and counted against purchase order and found correct as per above details
        </div>

        <div class="sig-row-4">
            <div class="sig-block-4">
                <div class="sig-line"></div>
                <div class="sig-title">Director Inventory</div>
            </div>
            <div class="sig-block-4">
                <div class="sig-line"></div>
                <div class="sig-title">Member</div>
            </div>
            <div class="sig-block-4">
                <div class="sig-line"></div>
                <div class="sig-title">Member</div>
            </div>
            <div class="sig-block-4">
                <div class="sig-line"></div>
                <div class="sig-title">Member</div>
            </div>
        </div>

        <!-- Acceptance Certification & Signatures -->
        <div class="cert-note" style="margin-top: 25px;">
            2. Above items are hereby accepted.
        </div>

        <div class="sig-row-acceptance">
            <div class="sig-block-custodian">
                <div class="sig-line"></div>
                <div class="sig-title">Custodian of Store</div>
            </div>
            <div class="sig-block-director">
                <div class="sig-line"></div>
                <div class="sig-title" style="line-height: 1.35;">Director ({{ $receipt->int_unt_name ?? ($receipt->unt_name ?? ($receipt->int_unt_namesh ?? ($receipt->unt_namesh ?? 'Division'))) }})</div>
            </div>
        </div>

        <!-- Remarks Notes Legend (Muted / Light font requested by user) -->
        <div class="remarks-notes-box">
            <div class="remarks-notes-title">Remarks Notes:</div>
            <div class="remarks-notes-list">
                <div><strong>D</strong> &mdash; Delivered</div>
                <div><strong>ND</strong> &mdash; Not Delivered</div>
                <div><strong>P</strong> &mdash; Presented</div>
                <div><strong>NP</strong> &mdash; Not Presented</div>
                <div style="grid-column: 1 / -1;"><strong>C</strong> &mdash; Consumed after delivery before inspection as per user</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-row">
            <div>Receipt Form #{{ $receipt->prt_id }} | Case #{{ $receipt->pcs_id }}</div>
            <div>Printed on {{ date('d M y H:i') }}</div>
        </div>
    </div>

</body>
</html>
