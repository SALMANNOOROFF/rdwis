<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case File Dossier - CC-{{ $case->ctc_id }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm;
            @top-center { content: "CONFIDENTIAL"; font-family: Arial, sans-serif; font-size: 10pt; font-weight: bold; letter-spacing: 3px; }
            @bottom-center { content: "CONFIDENTIAL"; font-family: Arial, sans-serif; font-size: 10pt; font-weight: bold; letter-spacing: 3px; }
        }

        body {
            font-family: "DejaVu Sans", "Helvetica Neue", Arial, sans-serif;
            text-align: center;
            color: #1A1A1A;
            padding-top: 40px;
        }

        .cover-box {
            border: 3px double #2C3E50;
            padding: 40px 30px;
            margin: 0 auto;
            max-width: 90%;
        }

        .org-header {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            color: #4A5568;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .wing-header {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 2px;
            color: #1A202C;
            margin-bottom: 25px;
            text-transform: uppercase;
            border-bottom: 2px solid #2B6CB0;
            display: inline-block;
            padding-bottom: 6px;
        }

        .doc-type {
            font-size: 12pt;
            letter-spacing: 1px;
            color: #718096;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .main-title {
            font-size: 22pt;
            font-weight: bold;
            color: #2D3748;
            margin: 10px 0 35px 0;
            letter-spacing: 1px;
        }

        .metadata-table {
            width: 85%;
            margin: 0 auto 40px auto;
            border-collapse: collapse;
            text-align: left;
            font-size: 11pt;
        }

        .metadata-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #E2E8F0;
        }

        .metadata-table td.label {
            font-weight: bold;
            color: #4A5568;
            width: 40%;
        }

        .metadata-table td.val {
            color: #1A202C;
            font-weight: 500;
        }

        .toc-box {
            text-align: left;
            width: 85%;
            margin: 0 auto 30px auto;
            background: #F7FAFC;
            border: 1px solid #E2E8F0;
            padding: 15px 20px;
            border-radius: 6px;
        }

        .toc-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #2D3748;
            margin-bottom: 8px;
            border-bottom: 1px solid #CBD5E0;
            padding-bottom: 4px;
        }

        .toc-list {
            list-style: decimal;
            margin: 0 0 0 20px;
            padding: 0;
            font-size: 9.5pt;
            line-height: 1.6;
        }

        .cover-footer {
            margin-top: 40px;
            font-size: 9pt;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="cover-box">
        <div class="org-header">National Radio & Telecommunication Corporation</div>
        <div class="wing-header">Research & Development Wing (RDW)</div>

        <div class="doc-type">HR POLICY AMENDED 2026 • OFFICIAL CASE DOSSIER</div>
        <h1 class="main-title">CONTRACT CASE FILE</h1>

        <table class="metadata-table">
            <tr>
                <td class="label">Case Reference:</td>
                <td class="val">CC-{{ $case->ctc_id }}</td>
            </tr>
            <tr>
                <td class="label">Candidate / Employee:</td>
                <td class="val">{{ $case->ctc_empnamecomp ?: ($case->employee->emp_name ?? '—') }}</td>
            </tr>
            <tr>
                <td class="label">Proposed Designation:</td>
                <td class="val">{{ $case->ctc_newjobtitle }} ({{ $case->ctc_newgrade }})</td>
            </tr>
            <tr>
                <td class="label">Assigned Project:</td>
                <td class="val">{{ $case->project_name ?? '—' }} ({{ $case->project_code ?? '—' }})</td>
            </tr>
            <tr>
                <td class="label">Hiring Category:</td>
                <td class="val">{{ $hiringType }}</td>
            </tr>
            <tr>
                <td class="label">Tenure Duration:</td>
                <td class="val">{{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M Y') : '—' }} to {{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M Y') : '—' }}</td>
            </tr>
            <tr>
                <td class="label">Generated Date & Time:</td>
                <td class="val">{{ now()->format('d M Y, H:i:s') }}</td>
            </tr>
            <tr>
                <td class="label">Generated By:</td>
                <td class="val">{{ $generatedBy ?? 'Authorized User' }}</td>
            </tr>
        </table>

        <div class="toc-box">
            <div class="toc-title">Index of Enclosed Policy Forms</div>
            <ol class="toc-list">
                @foreach($enclosedForms as $form)
                    <li>
                        <strong>Annex {{ $form['annex'] }}</strong> ({{ $form['form_code'] }}): 
                        {{ $form['title'] }}
                        @if($form['instance_key'] !== 'main')
                            - <em>{{ $form['instance_key'] }}</em>
                        @endif
                        <span style="color: #4A5568; font-size: 8.5pt;">[{{ $form['status'] }}]</span>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="cover-footer">
            This document contains confidential human resource and project records of RDW.<br>
            Unauthorized dissemination or copying is strictly prohibited.
        </div>
    </div>
</body>
</html>
