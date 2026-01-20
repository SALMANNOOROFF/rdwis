<!DOCTYPE html>
<html>
<head>
    <style>
        @page { size: portrait; margin: 20mm; }
        body { font-family: sans-serif; color: black; line-height: 1.6; font-size: 14px; }
        .right-h { float: right; margin-bottom: 30px; }
        .left-h { float: left; margin-top: 100px; }
        .clear { clear: both; }
        .title { text-align: center; font-weight: bold; text-decoration: underline; margin: 40px 0; font-size: 18px; }
        .footer-sig { float: right; text-align: left; margin-top: 60px; font-weight: bold; line-height: 1.2; }
        .firm { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="right-h">
        Naval Research & Development Institute<br>R&D Wing, At PNS JAUHAR<br>Habib Rahmatullah road, KARACHI<br>021-48503038<br><br>
        {{ $letter_date }}
    </div>
    <div class="left-h">{{ $ref_no }}<br>See distribution</div>
    <div class="clear"></div>
    <div class="title">REQUEST FOR QUOTATION</div>
    <p>1. &nbsp;&nbsp; R&D Wing NRDI at PNS JAUHAR is interested for procurement of {{ $item_desc }}. In this regard quotations are to be submitted to MD R&D at NRDI by{{ $deadline }}.</p>
    <p>2. &nbsp;&nbsp; It is apprised that MD (R&D) reserves the right to reject/ accept any quotation or invite new quotation without assigning any reason.</p>
    <div class="footer-sig">DR. ALEEM MUSHTAQ<br>Captain Pakistan Navy<br>Director COMM</div>
    <div class="clear"></div>
    <div style="margin-top: 50px;">
        <strong>To:</strong><br><br>
        @foreach($firms as $f)
        <div class="firm"><strong>{{ $f['name'] }}</strong><br>{{ $f['address'] }}<br>Tel: {{ $f['tel'] }}</div>
        @endforeach
    </div>
    <script>window.print();</script>
</body>
</html>