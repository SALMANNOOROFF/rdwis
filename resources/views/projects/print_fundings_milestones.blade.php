<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fundings & Milestones Report - {{ $project->prj_code }}</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            background: #cbd5e1;
            font-family: 'Calibri', 'Segoe UI', Arial, sans-serif;
            color: #000000;
        }

        .top-action-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
            z-index: 9999;
        }
        .top-action-bar .brand {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .top-action-bar .actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            font-weight: 700;
            padding: 7px 18px;
            border-radius: 9999px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s ease;
        }
        .btn-print {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }
        .btn-close {
            background: #334155;
            color: #f8fafc;
        }
        .btn-close:hover {
            background: #475569;
        }

        .paper-wrapper {
            padding: 24px 12px 40px;
            display: flex;
            justify-content: center;
        }
        .paper-sheet {
            background: #ffffff;
            width: 297mm;
            min-height: 210mm;
            padding: 10mm 14mm;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            border-radius: 4px;
        }

        .table-report {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .table-report th,
        .table-report td {
            border: 1px solid #000000;
            padding: 4px 6px;
        }
        .table-report th {
            background: #f1f5f9;
            font-weight: 800;
            text-align: center;
        }

        .section-header {
            font-family: 'Rajdhani', sans-serif;
            font-weight: 800;
            font-size: 11pt;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
            margin-top: 14px;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .text-mono {
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            font-size: 8.5pt;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 8mm 10mm;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .top-action-bar {
                display: none !important;
            }
            .paper-wrapper {
                padding: 0 !important;
            }
            .paper-sheet {
                width: 100% !important;
                min-height: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>

    <div class="top-action-bar">
        <div class="brand">
            <i class="fas fa-file-invoice-dollar text-primary"></i>
            <span>RDWIS FINANCIAL INTELLIGENCE &mdash; FUNDINGS & MILESTONES REPORT</span>
        </div>
        <div class="actions">
            <button class="btn-action btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print (Landscape)
            </button>
            <button class="btn-action btn-close" onclick="window.close()">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>

    @php
        $grossAlloc = (float)($head->allocation ?? 0);
        $rdwShareRatio = $grossAlloc > 0 ? ((float)($head->rdw_share ?? 0) / $grossAlloc) : 0;
        $mtssShareRatio = $grossAlloc > 0 ? ((float)($head->mtss_share ?? 0) / $grossAlloc) : 0;
        $csrfShareRatio = $grossAlloc > 0 ? ((float)($head->csrf_share ?? 0) / $grossAlloc) : 0;

        $totMilestoneAmt = 0;
        $totRdwShare = 0;
        $totMtssShare = 0;
        $totCsrfShare = 0;

        foreach($milestones as $m) {
            $mCost = (float)($m->mct_cost ?: $m->msn_cost);
            $totMilestoneAmt += $mCost;
            $totRdwShare += ($mCost * $rdwShareRatio);
            $totMtssShare += ($mCost * $mtssShareRatio);
            $totCsrfShare += ($mCost * $csrfShareRatio);
        }

        $totReceivedInflow = 0;
        $totReceivedMtss = 0;
        $totReceivedRdw = 0;
        $totReceivedCsrf = 0;
        $totReceivedPrj = 0;

        foreach($installments as $ins) {
            $insInward = (float)($ins->inward ?: (($ins->shi_pcc ?? 0) + ($ins->shi_cf ?? 0) + ($ins->mtss_share ?? 0)));
            $insMtss = (float)($ins->mtss_share ?? 0);
            $insRdw = $insInward - $insMtss;
            $insCf = (float)($ins->shi_cf ?? 0);
            $insPrj = $insRdw - $insCf;

            $totReceivedInflow += $insInward;
            $totReceivedMtss += $insMtss;
            $totReceivedRdw += $insRdw;
            $totReceivedCsrf += $insCf;
            $totReceivedPrj += $insPrj;
        }

        if ($totReceivedInflow == 0 && ($head->received ?? 0) > 0) {
            $totReceivedInflow = (float)($head->received ?? 0);
            $totReceivedMtss = (float)($head->received ?? 0) - (float)($head->acc_received ?? 0);
            $totReceivedRdw = (float)($head->acc_received ?? 0);
            $totReceivedCsrf = (float)($head->cf_received ?? 0);
            $totReceivedPrj = (float)($head->pcc_received ?? 0);
        }
    @endphp

    <div class="paper-wrapper">
        <div class="paper-sheet">

            {{-- 1. REPORT HEADER --}}
            <div style="border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <div style="font-family: 'Rajdhani', sans-serif; font-size: 15pt; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                        PROJECT FUNDINGS & MILESTONE COSTS REPORT
                    </div>
                    <div style="font-size: 10.5pt; font-weight: 700; margin-top: 2px;">
                        {{ $project->prj_code }} &mdash; {{ $project->prj_title }}
                    </div>
                    <div style="font-size: 9pt; color: #333; margin-top: 1px;">
                        Account Head: <strong>{{ $headRecord->hed_name ?? 'N/A' }}</strong> (Code: {{ $headRecord->hed_code ?? 'N/A' }})
                    </div>
                </div>
                <div style="text-align: right; font-size: 8.5pt;">
                    <div><strong>Printed On:</strong> {{ now()->format('d-M-Y H:i') }}</div>
                    <div><strong>Status:</strong> {{ strtoupper($project->prj_status ?? 'OPEN') }}</div>
                    <div><strong>Unit / Division:</strong> {{ $project->unit->unt_name ?? 'N/A' }}</div>
                </div>
            </div>

            {{-- 2. FUNDINGS & TRANCHES BREAKDOWN --}}
            <div class="section-header">
                <span>1. FUNDINGS (ALLOCATION, INFLOW & TRANCHES)</span>
                <span style="font-size: 8.5pt; font-weight: 600;">Currency: PKR (Rupees)</span>
            </div>

            {{-- Summary Rows Table --}}
            <table class="table-report" style="margin-bottom: 8px;">
                <thead>
                    <tr>
                        <th style="width: 12%; text-align: left;">Category</th>
                        <th style="width: 14%; text-align: right;">Total</th>
                        <th style="width: 13%; text-align: right;">MTSS Share</th>
                        <th style="width: 13%; text-align: right;">RDW Share</th>
                        <th style="width: 12%; text-align: right;">CSRF Share</th>
                        <th style="width: 13%; text-align: right;">Project Share</th>
                        <th style="width: 8%; text-align: center;">Milestone</th>
                        <th style="width: 15%; text-align: right;">Milestone Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background: #ffffff; font-weight: 700;">
                        <td style="text-align: left; font-weight: 800;">Allocation</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($head->allocation ?? 0) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($head->mtss_share ?? 0) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($head->rdw_share ?? 0) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($head->csrf_share ?? 0) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($head->prj_share ?? 0) }}</td>
                        <td style="text-align: center;">&mdash;</td>
                        <td style="text-align: right; font-weight: 800;" class="text-mono">{{ number_format($totMilestoneAmt) }}</td>
                    </tr>
                    <tr style="background: #f8fafc; font-weight: 700;">
                        <td style="text-align: left; font-weight: 800;">Received (Inflow)</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totReceivedInflow) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totReceivedMtss) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totReceivedRdw) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totReceivedCsrf) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totReceivedPrj) }}</td>
                        <td style="text-align: center;">&mdash;</td>
                        <td style="text-align: right; color: #64748b;">&mdash;</td>
                    </tr>
                </tbody>
            </table>

            {{-- Tranches Table --}}
            <table class="table-report" style="margin-bottom: 14px;">
                <thead>
                    <tr style="background: #e2e8f0;">
                        <th style="width: 10%; text-align: left;">Date</th>
                        <th style="width: 12%; text-align: right;">Total Amount</th>
                        <th style="width: 11%; text-align: right;">MTSS Share</th>
                        <th style="width: 11%; text-align: right;">RDW Share</th>
                        <th style="width: 10%; text-align: right;">CSRF Share</th>
                        <th style="width: 11%; text-align: right;">Project Share</th>
                        <th style="width: 8%; text-align: center;">Milestone</th>
                        <th style="width: 12%; text-align: right;">Milestone Cost</th>
                        <th style="width: 9%; text-align: center;">Status</th>
                        <th style="width: 6%; text-align: right;">Ref #</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($installments as $ins)
                        @php
                            $insInward = (float)($ins->inward ?: (($ins->shi_pcc ?? 0) + ($ins->shi_cf ?? 0) + ($ins->mtss_share ?? 0)));
                            $insMtss = (float)($ins->mtss_share ?? 0);
                            $insRdw = $insInward - $insMtss;
                            $insCf = (float)($ins->shi_cf ?? 0);
                            $insPrj = $insRdw - $insCf;
                            $dateFormatted = !empty($ins->trn_date) ? \Carbon\Carbon::parse($ins->trn_date)->format('d-M-Y') : 'N/A';
                            $msnCostVal = (float)($ins->mct_cost ?? 0);
                        @endphp
                        <tr>
                            <td style="text-align: left;" class="text-mono">{{ $dateFormatted }}</td>
                            <td style="text-align: right; font-weight: 700;" class="text-mono">{{ number_format($insInward) }}</td>
                            <td style="text-align: right;" class="text-mono">{{ number_format($insMtss) }}</td>
                            <td style="text-align: right;" class="text-mono">{{ number_format($insRdw) }}</td>
                            <td style="text-align: right;" class="text-mono">{{ number_format($insCf) }}</td>
                            <td style="text-align: right;" class="text-mono">{{ number_format($insPrj) }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ $ins->mct_msn_id ?: '—' }}</td>
                            <td style="text-align: right;" class="text-mono">{{ $msnCostVal > 0 ? number_format($msnCostVal) : '—' }}</td>
                            <td style="text-align: center; font-weight: 600;">{{ $ins->msn_status ?: '—' }}</td>
                            <td style="text-align: right; color: #475569;" class="text-mono">#{{ $ins->shi_id }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; color: #64748b; padding: 8px;">No funding tranches or installments recorded for this project head.</td>
                        </tr>
                    @endforelse
                    @if(count($installments) > 0)
                    <tr style="background: #f1f5f9; font-weight: 800;">
                        <td style="text-align: left;">TOTAL RECEIVED</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totReceivedInflow) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totReceivedMtss) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totReceivedRdw) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totReceivedCsrf) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totReceivedPrj) }}</td>
                        <td colspan="4"></td>
                    </tr>
                    @endif
                </tbody>
            </table>

            {{-- 3. PROJECT MILESTONES & COST ALLOCATION --}}
            <div class="section-header" style="page-break-before: auto;">
                <span>2. PROJECT MILESTONES & COST ALLOCATION</span>
                <span style="font-size: 8.5pt; font-weight: 600;">{{ count($milestones) }} Total Milestones</span>
            </div>

            <table class="table-report" style="margin-bottom: 25px;">
                <thead>
                    <tr style="background: #e2e8f0;">
                        <th style="width: 6%; text-align: center;">MS #</th>
                        <th style="width: 32%; text-align: left;">Milestone Description</th>
                        <th style="width: 14%; text-align: right;">Milestone Amount</th>
                        <th style="width: 12%; text-align: right;">RDW Share</th>
                        <th style="width: 11%; text-align: right;">MTSS Share</th>
                        <th style="width: 11%; text-align: right;">CSRF Share</th>
                        <th style="width: 8%; text-align: center;">Target Date</th>
                        <th style="width: 6%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($milestones as $m)
                        @php
                            $mCost = (float)($m->mct_cost ?: $m->msn_cost);
                            $mRdw = $mCost * $rdwShareRatio;
                            $mMtss = $mCost * $mtssShareRatio;
                            $mCsrf = $mCost * $csrfShareRatio;
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: 700;">MS-{{ $loop->iteration }}</td>
                            <td style="text-align: left; font-weight: 600;">{{ $m->msn_desc }}</td>
                            <td style="text-align: right; font-weight: 700;" class="text-mono">{{ number_format($mCost, 2) }}</td>
                            <td style="text-align: right;" class="text-mono">{{ number_format($mRdw, 2) }}</td>
                            <td style="text-align: right;" class="text-mono">{{ number_format($mMtss, 2) }}</td>
                            <td style="text-align: right;" class="text-mono">{{ number_format($mCsrf, 2) }}</td>
                            <td style="text-align: center;" class="text-mono">{{ $m->msn_targetdt ? \Carbon\Carbon::parse($m->msn_targetdt)->format('d-M-Y') : 'N/A' }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ strtoupper($m->msn_status ?: 'PENDING') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 8px;">No milestones recorded for this project.</td>
                        </tr>
                    @endforelse
                    @if(count($milestones) > 0)
                    <tr style="background: #f1f5f9; font-weight: 800;">
                        <td colspan="2" style="text-align: left;">TOTAL MILESTONES COST</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totMilestoneAmt, 2) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totRdwShare, 2) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totMtssShare, 2) }}</td>
                        <td style="text-align: right;" class="text-mono">{{ number_format($totCsrfShare, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                    @endif
                </tbody>
            </table>

            {{-- 4. OFFICIAL SIGNATURES BLOCK --}}
            <div style="margin-top: 30px; display: flex; justify-content: space-between; page-break-inside: avoid; padding: 0 40px;">
                <div style="text-align: center; width: 220px;">
                    <div style="border-top: 1.5px solid #000; padding-top: 6px; font-size: 9.5pt; font-weight: 700;">Prepared By</div>
                    <div style="font-size: 8.5pt; color: #444;">Finance / Accounts Officer</div>
                </div>
                <div style="text-align: center; width: 220px;">
                    <div style="border-top: 1.5px solid #000; padding-top: 6px; font-size: 9.5pt; font-weight: 700;">Checked By</div>
                    <div style="font-size: 8.5pt; color: #444;">Deputy Director (Finance)</div>
                </div>
                <div style="text-align: center; width: 220px;">
                    <div style="border-top: 1.5px solid #000; padding-top: 6px; font-size: 9.5pt; font-weight: 700;">Approved By</div>
                    <div style="font-size: 8.5pt; color: #444;">Director / Project Director</div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
