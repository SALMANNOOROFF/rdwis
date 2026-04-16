<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Minute Sheet - Case #{{ $purchase->pcs_id }}</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&family=Arial&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #525659; /* PDF viewer typical background */
            margin: 0;
            padding: 20px;
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
        }

        .minute-sheet {
            width: 8.5in;
            min-height: 14in; /* Legal size */
            background-color: #f4fae8; /* Very subtle light green tint */
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            padding: 1in;
            box-sizing: border-box;
            position: relative;
        }

        .minute-sheet::before {
            content: '';
            position: absolute;
            top: 0.5in;
            bottom: 0.5in;
            left: 0.5in;
            right: 0.5in;
            border: 2px solid #2d6a4f; /* Greenish border */
            pointer-events: none;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #2d6a4f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-family: 'Times New Roman', serif;
            color: #1b4332;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #40916c;
            font-weight: bold;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            font-size: 12px;
            color: #000;
        }

        .meta-info div {
            flex: 1;
        }

        .remark-entry {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .remark-header {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 8px;
            color: #1b4332;
            border-bottom: 1px dashed #74c69d;
            padding-bottom: 4px;
            display: flex;
            justify-content: space-between;
        }

        .remark-content {
            font-size: 14px;
            line-height: 1.6;
            color: #000;
            padding-left: 10px;
        }

        /* Numbering styles from Summernote */
        .remark-content ol {
            padding-left: 20px;
            margin-top: 5px;
        }
        .remark-content li {
            margin-bottom: 8px;
        }

        @media print {
            body {
                background-color: transparent;
                padding: 0;
                display: block;
            }
            .minute-sheet {
                box-shadow: none;
                width: 100%;
                min-height: auto;
                padding: 0.5in;
            }
            .minute-sheet::before {
                display: none;
            }
            .print-btn {
                display: none !important;
            }
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1b4332;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .print-btn:hover {
            background: #2d6a4f;
        }
    </style>
</head>
<body>

    <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print Minute Sheet</button>

    <div class="minute-sheet">
        <div class="header">
            <h2>Minute Sheet</h2>
            <p>Official Record of Case Processing</p>
        </div>

        <div class="meta-info">
            <div>
                <strong>Case ID:</strong> #{{ $purchase->pcs_id }}<br>
                <strong>Title:</strong> {{ $purchase->pcs_title }}
            </div>
            <div style="text-align: right;">
                <strong>Initiation Date:</strong> {{ \Carbon\Carbon::parse($purchase->pcs_date)->format('d M, Y') }}<br>
                <strong>Current Status:</strong> {{ $purchase->pcs_status }}
            </div>
        </div>

        <!-- Render in latest first order -->
        @php $service = app(\App\Services\PurchaseApprovalService::class); @endphp
        @forelse($purchase->decisions->sortByDesc('created_at') as $decision)
            @php
                $hasRemarks = !empty(trim(strip_tags($decision->pdec_remarks)));
                $toStatusDisplay = $service->getStatusDisplayName($decision->pdec_to_status);
            @endphp
            <div class="remark-entry">
                <div class="remark-header">
                    <span>
                        {{ $decision->account->acc_name }} ({{ strtoupper($decision->pdec_role) }})
                        - <span style="color:#e63946;">{{ strtoupper($decision->pdec_action) }}</span>
                    </span>
                    <span style="font-size:11px; font-weight:normal; color:#555;">
                        {{ \Carbon\Carbon::parse($decision->created_at)->format('d M Y, h:i A') }}
                    </span>
                </div>
                <div class="remark-content">
                    @if($hasRemarks)
                        {!! $decision->pdec_remarks !!}
                    @else
                        <p style="font-style: italic; color: #777;">Case forwarded to {{ $toStatusDisplay }} without additional remarks.</p>
                    @endif
                </div>
            </div>
        @empty
            <div style="text-align: center; color: #777; font-style: italic; margin-top: 50px;">
                No minutes recorded for this case yet.
            </div>
        @endforelse
    </div>

</body>
</html>