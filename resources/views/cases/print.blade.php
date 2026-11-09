{{-- resources/views/cases/print.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Note Sheet &mdash; {{ $case->gcs_file_no }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: #ffffff;
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            padding: 30px;
            font-size: 14pt;
            line-height: 1.6;
        }
        .notesheet-header {
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }
        .para-box {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .para-number {
            font-weight: bold;
            font-size: 14pt;
            margin-right: 8px;
        }
        .para-content {
            text-align: justify;
            white-space: pre-line;
            font-size: 13.5pt;
        }
        .signature-block {
            margin-top: 15px;
            margin-bottom: 25px;
            text-align: right;
            page-break-inside: avoid;
        }
        .signature-line {
            display: inline-block;
            text-align: right;
            border-top: 1px solid #333;
            padding-top: 5px;
            min-width: 250px;
            font-size: 12pt;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            font-size: 11pt;
            font-family: Arial, sans-serif;
        }
        .history-table th, .history-table td {
            border: 1px solid #bbb;
            padding: 6px 10px;
        }
        .history-table th {
            background-color: #f3f4f6;
        }
        @media print {
            body {
                padding: 15mm;
                font-size: 13pt;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>
    {{-- Print Action Controls (Screen Only) --}}
    <div class="no-print d-flex justify-content-between align-items-center mb-4 p-3 bg-light border rounded">
        <div>
            <h5 class="mb-0 font-weight-bold">Print Official Note Sheet</h5>
            <small class="text-muted">A4 portrait layout formatted for official government file keeping.</small>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-dark btn-sm font-weight-bold px-3">
                Print Document
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm ml-2">
                Close Window
            </button>
        </div>
    </div>

    {{-- Official Heading --}}
    <div class="notesheet-header">
        <div class="text-center mb-3">
            <h3 class="font-weight-bold mb-0 text-uppercase" style="letter-spacing: 1px;">Research, Development &amp; Works Integrated System</h3>
            <h5 class="text-muted mb-0">OFFICIAL NOTE SHEET</h5>
        </div>
        <div class="d-flex justify-content-between mt-3 font-weight-bold" style="font-size: 12pt;">
            <div>
                <div><strong>FILE NO:</strong> <span style="font-family: monospace;">{{ $case->gcs_file_no }}</span></div>
                <div><strong>CATEGORY:</strong> {{ $case->gcs_category }}</div>
                <div><strong>INITIATOR:</strong> {{ $case->creator->acc_name ?? 'Officer' }} ({{ $case->unit->unt_name ?? 'Headquarters' }})</div>
            </div>
            <div class="text-right">
                <div><strong>DATE INITIATED:</strong> {{ $case->created_at->format('d F Y') }}</div>
                <div><strong>PRIORITY:</strong> {{ $case->gcs_priority }}</div>
                <div><strong>STATUS:</strong> {{ $case->gcs_status }}</div>
            </div>
        </div>
        @if($case->gcs_subject)
            <div class="mt-2 pt-2 border-top">
                <strong>SUBJECT:</strong> <span class="text-dark">{{ $case->gcs_subject }}</span>
            </div>
        @endif
    </div>

    @if($case->gcs_description)
        <div class="para-box mb-4 p-3" style="background: #fafafa; border: 1px solid #ddd; border-radius: 4px;">
            <div class="para-content" style="white-space: pre-wrap; font-family: 'Times New Roman', Times, serif;">
                <strong style="text-decoration: underline;">CASE PARTICULARS &amp; NOTES:</strong>
{{ $case->gcs_description }}
            </div>
        </div>
    @endif

    {{-- Consecutive Paragraphs --}}
    @foreach($case->notes as $note)
        <div class="para-box">
            <div class="para-content">
                <span class="para-number">{{ $note->gcn_para_no }}.</span>
                @if($note->gcn_title)
                    <strong>{{ $note->gcn_title }}:</strong>
                @endif
                {{ $note->gcn_content }}
            </div>

            <div class="signature-block">
                <div class="signature-line">
                    <div class="font-weight-bold">{{ $note->gcn_author_name }}</div>
                    <div>{{ $note->gcn_author_rank ? $note->gcn_author_rank . ' ' : '' }}{{ $note->gcn_author_desig ?? 'Authorized Officer' }}</div>
                    <div style="font-size: 10pt; color: #555;">Dated: {{ $note->created_at->format('d-m-Y h:i A') }}</div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Movement & Decision Audit Trail --}}
    @if($case->movements->count() > 0)
        <div style="page-break-inside: avoid; margin-top: 40px;">
            <h6 class="font-weight-bold text-uppercase" style="border-bottom: 1px solid #333; padding-bottom: 4px;">
                File Movement &amp; Decision Record
            </h6>
            <table class="history-table">
                <thead>
                    <tr>
                        <th style="width: 140px;">Date &amp; Time</th>
                        <th>From Officer</th>
                        <th>Action / Decision</th>
                        <th>To Officer / Stage</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($case->movements as $mov)
                        <tr>
                            <td>{{ $mov->gcm_dtg->format('d M Y, h:i A') }}</td>
                            <td>{{ $mov->gcm_actor_name }}</td>
                            <td><strong>{{ $mov->gcm_action }}</strong></td>
                            <td>{{ $mov->toHolder->acc_name ?? $mov->gcm_to_stage }}</td>
                            <td>{{ $mov->gcm_remarks ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</body>
</html>

