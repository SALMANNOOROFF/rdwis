<div class="official-financial-document" style="font-family: 'Calibri', 'Arial', 'Segoe UI', sans-serif; color: #000; background: #fff; line-height: 1.35; padding: 10px 5px;">
    <style>
        .official-financial-document table {
            border-collapse: collapse;
            color: #000;
        }
        .official-financial-document .text-mono {
            font-family: 'Consolas', 'Courier New', monospace;
        }
        .official-financial-document .table-bordered-black,
        .official-financial-document .table-bordered-black td,
        .official-financial-document .table-bordered-black th {
            border: 1px solid #000 !important;
        }
    </style>

    {{-- 1. OFFICIAL HEADER (Matching Example Image) --}}
    <div class="text-center" style="margin-bottom: 14px;">
        <h2 style="font-size: 16pt; font-weight: 800; text-transform: uppercase; margin: 0 0 3px 0; letter-spacing: 0.5px;">
            ACCOUNT SUMMARY &mdash; {{ $project->prj_code }} {{ $project->prj_title }}
        </h2>
        <div style="font-size: 11pt; font-weight: 700; color: #111; margin-bottom: 2px;">
            {{ (($headRecord->hed_transtype ?? 1) == 1) ? '(Rupees without GST)' : '(Rupees with GST)' }}
        </div>
        <div style="font-size: 10.5pt; font-weight: 700; color: #222;">
            Dated {{ now()->format('d M y') }}
        </div>
    </div>

    {{-- 2. METADATA SUMMARY BAR --}}
    <div style="display: flex; justify-content: space-between; border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 5px 8px; margin-bottom: 16px; font-size: 9.5pt; font-weight: bold; background: #fbfbfb;">
        <div><strong>HEAD CODE:</strong> {{ $headRecord->hed_code ?? 'N/A' }}</div>
        <div><strong>DIVISION:</strong> {{ $project->unit?->unt_name ?? 'N/A' }}</div>
        <div><strong>STATUS:</strong> {{ strtoupper($project->prj_status) }}</div>
        <div><strong>DATE:</strong> {{ now()->format('d-M-Y') }}</div>
        <div><strong>TERMS:</strong> {{ (($headRecord->hed_transtype ?? 1) == 1) ? 'WITHOUT GST' : 'WITH GST' }}</div>
    </div>

    {{-- 3. ALLOCATION BOX (Centered Table matching Example Image) --}}
    <div style="display: flex; justify-content: center; margin-bottom: 18px;">
        <table class="table-bordered-black" style="width: 280px; font-size: 10pt;">
            <tr>
                <td style="padding: 4px 10px; font-weight: 700;">Allocation</td>
                <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->allocation ?? 0) }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 10px; font-weight: 700;">MTSS Share</td>
                <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->pcc_share ?? $head->mtss_share ?? 0) }}</td>
            </tr>
            <tr>
                <td style="padding: 4px 10px; font-weight: 700;">RDW Share</td>
                <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->rdw_share ?? 0) }}</td>
            </tr>
        </table>
    </div>

    {{-- 4. DUAL PROJECT VS CSRF COLUMNS (Identical Layout to User Example Image) --}}
    <div style="display: flex; justify-content: center; gap: 40px; margin-bottom: 18px; page-break-inside: avoid;">
        
        {{-- LEFT SIDE: PROJECT SECTION --}}
        <div style="display: flex; flex-direction: column; align-items: flex-end;">
            {{-- Header above table --}}
            <div style="text-align: center; width: 140px; margin-bottom: 4px;">
                <div style="font-weight: 800; font-size: 11pt;">Project</div>
                <div style="font-weight: 700; font-size: 10.5pt;" class="text-mono">{{ number_format($head->pcc_received ?? 0) }}</div>
            </div>

            <div style="display: flex; align-items: flex-start;">
                {{-- Left labels --}}
                <div style="text-align: right; padding-right: 12px; font-size: 9.5pt; font-weight: 700; line-height: 25px;">
                    <div>Received</div>
                    <div>Expenditure</div>
                    <div>Balance</div>
                    <div>Commitments</div>
                    <div>In Process</div>
                    <div>Available</div>
                    <div>Yet to be Received</div>
                    <div>Remaining</div>
                </div>

                {{-- Project Values Box --}}
                <table class="table-bordered-black" style="width: 140px; font-size: 10pt; text-align: center; line-height: 25px;">
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->pcc_received ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format(abs($head->pcc_expenditure ?? 0)) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->pcc_balance ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format(abs($head->pcc_commitments ?? 0)) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->pcc_in_process ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->pcc_available ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->pcc_yet_to_be_received ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->pcc_can_be_spent ?? 0) }}</td></tr>
                </table>
            </div>

            {{-- Receivables Table under Project Column --}}
            <div style="display: flex; align-items: flex-start; margin-top: 10px;">
                <div style="text-align: right; padding-right: 12px; font-size: 9pt; font-weight: 700; line-height: 24px;">
                    <div>Receivable for Comp. Milestones</div>
                    <div>Receivable for Current Milestone</div>
                    <div>Available after Receivables</div>
                </div>
                <table class="table-bordered-black" style="width: 140px; font-size: 9.5pt; text-align: center; line-height: 24px;">
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->receivable_completed ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->receivable_current ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->available_after_receivables ?? 0) }}</td></tr>
                </table>
            </div>
        </div>

        {{-- RIGHT SIDE: CSRF SECTION --}}
        <div style="display: flex; flex-direction: column; align-items: flex-start;">
            {{-- Header above table --}}
            <div style="text-align: center; width: 140px; margin-bottom: 4px;">
                <div style="font-weight: 800; font-size: 11pt;">CSRF</div>
                <div style="font-weight: 700; font-size: 10.5pt;" class="text-mono">{{ number_format($head->cf_received ?? 0) }}</div>
            </div>

            <div style="display: flex; align-items: flex-start;">
                {{-- CSRF Values Box --}}
                <table class="table-bordered-black" style="width: 140px; font-size: 10pt; text-align: center; line-height: 25px;">
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->cf_received ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format(abs($head->cf_expenditure ?? 0)) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->cf_balance ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format(abs($head->cf_commitments ?? 0)) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->cf_in_process ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->cf_available ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->cf_yet_to_be_received ?? 0) }}</td></tr>
                    <tr><td style="padding: 0 6px; font-weight: 700;" class="text-mono">{{ number_format($head->cf_can_be_spent ?? 0) }}</td></tr>
                </table>

                {{-- Right labels --}}
                <div style="text-align: left; padding-left: 12px; font-size: 9.5pt; font-weight: 700; line-height: 25px;">
                    <div>Received</div>
                    <div>Expenditure</div>
                    <div>Balance</div>
                    <div>Commitments</div>
                    <div>In Process</div>
                    <div>Available</div>
                    <div>Yet to be Received</div>
                    <div>Remaining</div>
                </div>
            </div>
        </div>

    </div>

    {{-- 5. COMPLETE FINANCIAL SNAPSHOT MATRIX (Account Total vs Project vs CSRF) --}}
    <div style="margin-top: 20px; margin-bottom: 18px; page-break-inside: avoid;">
        <div style="font-weight: 800; font-size: 10.5pt; text-transform: uppercase; border-bottom: 1.5px solid #000; padding-bottom: 4px; margin-bottom: 6px;">
            Financial Snapshot &mdash; Consolidated vs Components
        </div>
        <table class="table-bordered-black" style="width: 100%; font-size: 9.5pt;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="padding: 6px 10px; text-align: left; font-weight: 800; width: 34%;">Financial Metric</th>
                    <th style="padding: 6px 10px; text-align: right; font-weight: 800; width: 22%;">Account (Total)</th>
                    <th style="padding: 6px 10px; text-align: right; font-weight: 800; width: 22%;">Project (PCC)</th>
                    <th style="padding: 6px 10px; text-align: right; font-weight: 800; width: 22%;">CSRF (CF)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 4px 10px; font-weight: 700;">Allocated</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->rdw_share ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->pcc_share ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->csrf_share ?? 0) }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 10px; font-weight: 600;">Received (Cash Inflow)</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format($head->acc_received ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format($head->pcc_received ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format($head->cf_received ?? 0) }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 10px; font-weight: 600;">Expenditure (Spent)</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format(abs($head->acc_expenditure ?? 0)) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format(abs($head->pcc_expenditure ?? 0)) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format(abs($head->cf_expenditure ?? 0)) }}</td>
                </tr>
                <tr style="background: #fafafa;">
                    <td style="padding: 4px 10px; font-weight: 700;">Balance</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->balance ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->pcc_balance ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->cf_balance ?? 0) }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 10px; font-weight: 600;">Commitments</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format(abs($head->acc_commitments ?? 0)) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format(abs($head->pcc_commitments ?? 0)) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format(abs($head->cf_commitments ?? 0)) }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 10px; font-weight: 600;">In Process (Pipeline)</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format($head->acc_in_process ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format($head->pcc_in_process ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format($head->cf_in_process ?? 0) }}</td>
                </tr>
                <tr style="background: #fafafa;">
                    <td style="padding: 4px 10px; font-weight: 700;">Available Budget</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->available ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->pcc_available ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->cf_available ?? 0) }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 10px; font-weight: 600;">Yet to be Received</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format($head->yet_to_be_received ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format($head->pcc_yet_to_be_received ?? 0) }}</td>
                    <td style="padding: 4px 10px; text-align: right; font-weight: 600;" class="text-mono">{{ number_format($head->cf_yet_to_be_received ?? 0) }}</td>
                </tr>
                <tr style="background: #f1f5f9; font-weight: 800;">
                    <td style="padding: 5px 10px;">Total Spendable Remaining</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($head->can_be_spent ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($head->pcc_can_be_spent ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($head->cf_can_be_spent ?? 0) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- 6. CORE SUBHEAD UTILIZATION TABLE --}}
    <div style="margin-top: 20px; margin-bottom: 24px; page-break-inside: avoid;">
        <div style="font-weight: 800; font-size: 10.5pt; text-transform: uppercase; border-bottom: 1.5px solid #000; padding-bottom: 4px; margin-bottom: 6px;">
            Core Subhead Utilization Breakdown
        </div>
        <table class="table-bordered-black" style="width: 100%; font-size: 9.5pt;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="padding: 6px 10px; text-align: left; font-weight: 800;">Subhead Category</th>
                    <th style="padding: 6px 10px; text-align: right; font-weight: 800;">Allocation</th>
                    <th style="padding: 6px 10px; text-align: right; font-weight: 800;">Spent (Exp)</th>
                    <th style="padding: 6px 10px; text-align: right; font-weight: 800;">Commitments</th>
                    <th style="padding: 6px 10px; text-align: right; font-weight: 800;">In Process</th>
                    <th style="padding: 6px 10px; text-align: right; font-weight: 800;">Remaining</th>
                    <th style="padding: 6px 10px; text-align: center; font-weight: 800;">% Utilized</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 5px 10px; font-weight: 700;">Equipment / Hardware</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['equip_alloc'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['equip'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['equip_cmt'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['equip_ipc'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($finData['equip_remaining'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: center; font-weight: 700;">{{ $finData['equip_pct'] }}%</td>
                </tr>
                <tr>
                    <td style="padding: 5px 10px; font-weight: 700;">HR / Personnel / Staff</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['hr_alloc'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['hr'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['hr_cmt'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['hr_ipc'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($finData['hr_remaining'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: center; font-weight: 700;">{{ $finData['hr_pct'] }}%</td>
                </tr>
                <tr>
                    <td style="padding: 5px 10px; font-weight: 700;">Miscellaneous / Operational</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['misc_alloc'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['misc'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['misc_cmt'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($finData['misc_ipc'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($finData['misc_remaining'] ?? 0) }}</td>
                    <td style="padding: 5px 10px; text-align: center; font-weight: 700;">{{ $finData['misc_pct'] }}%</td>
                </tr>
                @php
                    $totSubAlloc = ($finData['equip_alloc'] ?? 0) + ($finData['hr_alloc'] ?? 0) + ($finData['misc_alloc'] ?? 0);
                    $totSubExp = ($finData['equip'] ?? 0) + ($finData['hr'] ?? 0) + ($finData['misc'] ?? 0);
                    $totSubCmt = ($finData['equip_cmt'] ?? 0) + ($finData['hr_cmt'] ?? 0) + ($finData['misc_cmt'] ?? 0);
                    $totSubIpc = ($finData['equip_ipc'] ?? 0) + ($finData['hr_ipc'] ?? 0) + ($finData['misc_ipc'] ?? 0);
                    $totSubRem = ($finData['equip_remaining'] ?? 0) + ($finData['hr_remaining'] ?? 0) + ($finData['misc_remaining'] ?? 0);
                    $totSubPct = $totSubAlloc > 0 ? round(($totSubExp / $totSubAlloc) * 100) : 0;
                @endphp
                <tr style="background: #f1f5f9; font-weight: 800;">
                    <td style="padding: 6px 10px;">TOTAL SUBHEADS</td>
                    <td style="padding: 6px 10px; text-align: right;" class="text-mono">{{ number_format($totSubAlloc) }}</td>
                    <td style="padding: 6px 10px; text-align: right;" class="text-mono">{{ number_format($totSubExp) }}</td>
                    <td style="padding: 6px 10px; text-align: right;" class="text-mono">{{ number_format($totSubCmt) }}</td>
                    <td style="padding: 6px 10px; text-align: right;" class="text-mono">{{ number_format($totSubIpc) }}</td>
                    <td style="padding: 6px 10px; text-align: right;" class="text-mono">{{ number_format($totSubRem) }}</td>
                    <td style="padding: 6px 10px; text-align: center;">{{ $totSubPct }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- 7. OFFICIAL SIGNATURES BLOCK --}}
    <div style="margin-top: 45px; display: flex; justify-content: space-between; page-break-inside: avoid; padding: 0 20px;">
        <div style="text-align: center; width: 190px;">
            <div style="border-top: 1.5px solid #000; padding-top: 6px; font-size: 9.5pt; font-weight: 700;">Prepared By</div>
            <div style="font-size: 8.5pt; color: #444;">Finance / Accounts Branch</div>
        </div>
        <div style="text-align: center; width: 190px;">
            <div style="border-top: 1.5px solid #000; padding-top: 6px; font-size: 9.5pt; font-weight: 700;">Checked By</div>
            <div style="font-size: 8.5pt; color: #444;">Deputy Director (Finance)</div>
        </div>
        <div style="text-align: center; width: 190px;">
            <div style="border-top: 1.5px solid #000; padding-top: 6px; font-size: 9.5pt; font-weight: 700;">Approved By</div>
            <div style="font-size: 8.5pt; color: #444;">Director / Project Director</div>
        </div>
    </div>
</div>
