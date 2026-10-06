<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $formTitle ?? 'HR Policy Form' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 18mm 15mm 18mm;
            @top-center {
                content: "CONFIDENTIAL";
                font-family: Arial, sans-serif;
                font-size: 9pt;
                font-weight: bold;
                letter-spacing: 2px;
                color: #555555;
            }
            @bottom-center {
                content: "CONFIDENTIAL";
                font-family: Arial, sans-serif;
                font-size: 9pt;
                font-weight: bold;
                letter-spacing: 2px;
                color: #555555;
            }
        }

        body {
            font-family: "DejaVu Sans", "Helvetica Neue", Arial, sans-serif;
            font-size: 9.5pt;
            line-height: 1.35;
            color: #111111;
            margin: 0;
            padding: 0;
            background: #FFFFFF;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #222222;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .header-code {
            font-size: 10pt;
            font-weight: bold;
            color: #222222;
            text-transform: uppercase;
        }

        .header-annex {
            font-size: 11pt;
            font-weight: bold;
            color: #111111;
            text-transform: uppercase;
            text-align: right;
        }

        .form-title-block {
            text-align: center;
            margin-bottom: 14px;
        }

        .form-title {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 4px 0;
        }

        .form-subtitle {
            font-size: 9pt;
            color: #555555;
            margin: 0;
        }

        .section-header {
            background-color: #F0F2F0;
            border: 1px solid #CCCCCC;
            font-weight: bold;
            font-size: 9.5pt;
            padding: 4px 8px;
            margin-top: 10px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9pt;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #B0B0B0;
            padding: 5px 7px;
            vertical-align: top;
        }

        table.data-table th {
            background-color: #F8F9FA;
            font-weight: bold;
            text-align: left;
        }

        .table-striped tbody tr:nth-of-type(even) {
            background-color: #FAFAFA;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-muted { color: #666666; }

        /* Signature block */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signature-cell {
            width: {{ isset($chain) && count($chain) > 0 ? floor(100 / count($chain)) : 25 }}%;
            vertical-align: top;
            text-align: center;
            padding: 10px 4px;
            border: 1px solid #D0D0D0;
        }

        .signature-line {
            height: 45px;
            border-bottom: 1px solid #888888;
            margin: 0 10px 6px 10px;
        }

        .signature-role {
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .signature-action {
            font-size: 7.5pt;
            color: #666666;
            margin-top: 2px;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 40%;
            left: 15%;
            width: 70%;
            text-align: center;
            font-size: 72pt;
            font-weight: bold;
            color: rgba(200, 200, 200, 0.22);
            transform: rotate(-35deg);
            z-index: -1000;
            pointer-events: none;
            letter-spacing: 8px;
        }

        .submitted-stamp {
            border: 2px solid #2E7D32;
            color: #2E7D32;
            padding: 4px 10px;
            font-size: 8pt;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .notes-box {
            background-color: #FFFDF0;
            border: 1px solid #E6D875;
            padding: 6px 10px;
            margin-top: 10px;
            font-size: 8.5pt;
            color: #735C0F;
            border-radius: 4px;
        }

        .footer-bar {
            margin-top: 20px;
            border-top: 1px solid #CCCCCC;
            padding-top: 5px;
            font-size: 7.5pt;
            color: #777777;
            display: flex;
            justify-content: space-between;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    @if(isset($isSubmitted) && $isSubmitted)
        <div class="watermark">SUBMITTED</div>
    @else
        <div class="watermark">DRAFT</div>
    @endif

    <div class="header-top">
        <div class="header-code">
            {{ $formCode ?? '' }}
        </div>
        @if(!empty($annex))
            <div class="header-annex">
                ANNEX - {{ $annex }}
            </div>
        @endif
    </div>

    @if(isset($isSubmitted) && $isSubmitted)
        <div class="text-right">
            <span class="submitted-stamp">
                ✓ SUBMITTED | {{ $submittedAt ?? '' }} | {{ $submittedBy ?? 'Authorized User' }}
            </span>
        </div>
    @endif

    <div class="form-title-block">
        <h1 class="form-title">{{ $formTitle ?? '' }}</h1>
        @if(!empty($subtitle))
            <p class="form-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @yield('content')

    {{-- Approval Chain Signatures --}}
    @if(!empty($chain) && count($chain) > 0)
        <div class="section-header" style="margin-top: 18px;">APPROVAL & RECOMMENDATION CHAIN</div>
        <table class="signature-table">
            <tr>
                @foreach($chain as $step)
                    <td class="signature-cell" style="width: {{ floor(100 / count($chain)) }}%;">
                        <div class="signature-line"></div>
                        <div class="signature-role">{{ $step['role'] ?? ($step->role_title ?? '') }}</div>
                        <div class="signature-action">{{ $step['action'] ?? ($step->action_type ?? 'Recommendation') }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    {{-- Draft warnings box (printed only on DRAFT copies per B4) --}}
    @if((!isset($isSubmitted) || !$isSubmitted) && !empty($warnings) && count($warnings) > 0)
        <div class="notes-box">
            <strong>Notes / System Warnings:</strong>
            <ul style="margin: 4px 0 0 16px; padding: 0;">
                @foreach($warnings as $w)
                    <li>{{ $w }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="footer-bar">
        <span>RDWIS 2.0 • HR Policy Amended 2026</span>
        <span>Case Ref: CC-{{ $case->ctc_id ?? '' }} • {{ now()->format('d M Y H:i') }}</span>
        <span>CONFIDENTIAL</span>
    </div>
</body>
</html>
