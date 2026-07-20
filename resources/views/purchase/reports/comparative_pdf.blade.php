<!DOCTYPE html>
<html>
<head>
    <style>
        @page { size: A4 landscape; margin: 10mm; }

        body {
            background: #212121;
            color: white;
            font-family: arial;
            padding: 25px;
        }

        .header h2 {
            text-decoration: underline;
            text-align: center;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .intro { margin-bottom: 20px; line-height: 1.5; font-size: 14px; }

        table { width: 100%; border-collapse: collapse; border: 1px solid white; font-size: 11px; }
        th, td { border: 1px solid white; padding: 6px; vertical-align: top; text-align: left; }
.footer-sig {
    margin-top: 60px;
    display: flex;
    justify-content: space-between;
}

.sig-box {
    text-align: center;
    width: 220px;
    padding-top: 30px;           /* Space for signature */
    position: relative;
    font-weight: bold;
}

/* Draw the top line for signature */
.sig-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    border-top: 2px solid black;  /* Black line for signature */
}
        /* Print adjustments */
        @media print {
            body {
                background: white !important;
                color: black !important;
                -webkit-print-color-adjust: exact;
            }

            table, th, td {
                border: 1px solid black !important;
                color: black !important;
            }
        }
    </style>
</head>
<body>
    <div class="header"><h2>COMPARATIVE STATEMENT OF MARKET RESEARCH REPORT R&D WING</h2></div>
    <div class="intro">
        Following firms were issued RFQ vide <u>{{ $rfq_no }}</u> dated <u>{{ $rfq_date }}</u> {{ $subject }}. The response of the firm is given as under:
    </div>

    <table>
        <thead>
            <tr>
                <th>S No</th>
                <th>Name of Firm</th>
                <th>Date and Quotation number</th>
                <th>Rate Qouted by the firm (grand total only). Item wise detail given in MRR</th>
                <th>Address of the firm</th>
                <th>NTN & STRN</th>
                <th>Contact No & Email</th>
                <th>Name of Authorize of Dealer</th>
                <th>Remarks by R&D wing</th>
            </tr>
        </thead>
        <tbody>
            @foreach($firms as $index => $f)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $f['name'] }}</td>
                <td>{{ $f['q_no'] }}</td>
                <td>{{ $f['rate'] }}</td>
                <td>{{ $f['address'] }}</td>
                <td>{{ $f['ntn'] ?? '-' }}</td>
                <td>{{ $f['contact'] ?? '-' }}</td>
                <td>{{ $f['dealer'] ?? '-' }}</td>
                <td>{{ $f['remarks'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-sig">
    <div class="sig-box">Dir Comm</div>
    <div class="sig-box">Dir Finance</div>
    <div class="sig-box">MD (R&D)</div>
</div>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
