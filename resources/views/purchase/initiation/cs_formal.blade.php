<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Formal Comparative Statement - #{{ $purchase->pcs_id }}</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body {
            background-color: #fff;
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            color: #000;
            font-size: 9pt;
        }
        .container {
            width: 100%;
        }
        .main-title {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .subtitle {
            text-align: center;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .sig-container {
            margin-top: 50px;
            display: flex;
            justify-content: space-around;
        }
        .sig-box {
            width: 180px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-weight: bold;
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

    <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Statement</button>

    <div class="container">
        <div class="main-title">
            COMPARATIVE STATEMENT OF PROCUREMENT OF MATERIAL FOR {{ strtoupper($purchase->pcs_title) }}
        </div>
        <div class="subtitle">
            Following firms participated in bidding on {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d/F/Y') }} against IT No R&D/Projects/Proc/{{ $purchase->pcs_id }} dated {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d/F/Y') }}.
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">S.NO</th>
                    <th style="width: 180px;">Name of Firm</th>
                    <th style="width: 120px;">Date & quotation No</th>
                    <th style="width: 140px;">Rate quoted by the firm (grand total only). Item wise detail given in MRR (without G.S.T)</th>
                    <th>Address of the firm</th>
                    <th style="width: 100px;">NTN & STRN of firm</th>
                    <th style="width: 150px;">Contact No & Email of firm</th>
                    <th style="width: 100px;">Name of Authorize of Dealer</th>
                    <th style="width: 100px;">Remarks by R&D wing</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->quotes as $idx => $q)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="font-weight: bold;">M/s {{ strtoupper($q->firm->frm_name ?? $q->qte_firmname) }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($q->qte_date)->format('d-M-Y') }}<br>
                        {{ $q->qte_refno ?? 'N/A' }}
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ number_format($q->qte_price) }}
                    </td>
                    <td>{{ $q->firm->frm_address ?? 'N/A' }}</td>
                    <td>{{ $q->firm->frm_ntn ?? '-' }}</td>
                    <td>{{ $q->firm->frm_contact ?? 'N/A' }}</td>
                    <td style="text-align: center;">-</td>
                    <td style="text-align: center;">{{ $q->qte_techaccept ? 'Accepted' : 'Rejected' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="sig-container">
            <div class="sig-box">Dir Procurement</div>
            <div class="sig-box">Dir Comm</div>
            <div class="sig-box">Dir Finance</div>
            <div class="sig-box">MD(R&D)</div>
        </div>
    </div>

</body>
</html>
