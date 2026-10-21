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
                <td style="padding: 4px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($head->mtss_share ?? 0) }}</td>
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
                <div style="font-weight: 700; font-size: 10.5pt;" class="text-mono">{{ number_format($head->pcc_share ?? $head->prj_share ?? 0) }}</div>
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
                <div style="font-weight: 700; font-size: 10.5pt;" class="text-mono">{{ number_format($head->csrf_share ?? $head->cf_share ?? 0) }}</div>
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
                @php
                    $totSubAlloc = 0;
                    $totSubExp = 0;
                    $totSubCmt = 0;
                    $totSubIpc = 0;
                    $totSubRem = 0;
                @endphp
                @forelse($subheads as $sh)
                    @php
                        $shName = is_array($sh) ? ($sh['name'] ?? 'N/A') : ($sh->name ?? 'N/A');
                        $shAlloc = (float)(is_array($sh) ? ($sh['allocation'] ?? 0) : ($sh->allocation ?? 0));
                        $shExp = (float)(is_array($sh) ? ($sh['expenditure'] ?? 0) : ($sh->expenditure ?? 0));
                        $shCmt = (float)(is_array($sh) ? ($sh['commitments'] ?? 0) : ($sh->commitments ?? 0));
                        $shIpc = (float)(is_array($sh) ? ($sh['in_process'] ?? 0) : ($sh->in_process ?? 0));
                        $shRemaining = (float)(is_array($sh) ? ($sh['can_be_spent'] ?? ($sh['remaining'] ?? 0)) : ($sh->can_be_spent ?? ($sh->remaining ?? 0)));
                        $shPct = $shAlloc > 0 ? min(100, round(($shExp / $shAlloc) * 100)) : 0;

                        $totSubAlloc += $shAlloc;
                        $totSubExp += $shExp;
                        $totSubCmt += $shCmt;
                        $totSubIpc += $shIpc;
                        $totSubRem += $shRemaining;
                    @endphp
                    <tr>
                        <td style="padding: 5px 10px; font-weight: 700;">{{ $shName }}</td>
                        <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($shAlloc) }}</td>
                        <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($shExp) }}</td>
                        <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($shCmt) }}</td>
                        <td style="padding: 5px 10px; text-align: right;" class="text-mono">{{ number_format($shIpc) }}</td>
                        <td style="padding: 5px 10px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($shRemaining) }}</td>
                        <td style="padding: 5px 10px; text-align: center; font-weight: 700;">{{ $shPct }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 8px 10px; text-align: center; color: #64748b;">No subheads configured</td>
                    </tr>
                @endforelse
                <tr style="background: #f1f5f9; font-weight: 800;">
                    <td style="padding: 6px 10px;">TOTAL SUBHEADS</td>
                    <td style="padding: 6px 10px; text-align: right;" class="text-mono">{{ number_format($totSubAlloc) }}</td>
                    <td style="padding: 6px 10px; text-align: right;" class="text-mono">{{ number_format($totSubExp) }}</td>
                    <td style="padding: 6px 10px; text-align: right;" class="text-mono">{{ number_format($totSubCmt) }}</td>
                    <td style="padding: 6px 10px; text-align: right;" class="text-mono">{{ number_format($totSubIpc) }}</td>
                    <td style="padding: 6px 10px; text-align: right;" class="text-mono">{{ number_format($totSubRem) }}</td>
                    <td style="padding: 6px 10px; text-align: center;">{{ $totSubAlloc > 0 ? round(($totSubExp / $totSubAlloc) * 100) : 0 }}%</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if(isset($installments) && count($installments) > 0)
    {{-- 7. FUNDINGS & TRANCHES BREAKDOWN --}}
    <div style="margin-top: 20px; margin-bottom: 24px; page-break-inside: avoid;">
        <div style="font-weight: 800; font-size: 10.5pt; text-transform: uppercase; border-bottom: 1.5px solid #000; padding-bottom: 4px; margin-bottom: 6px; display: flex; justify-content: space-between;">
            <span>7. Fundings & Inflow Tranches Breakdown</span>
            <span style="font-size: 8.5pt; font-weight: 600;">{{ count($installments) }} Tranches Recorded</span>
        </div>
        <table class="table-bordered-black" style="width: 100%; font-size: 9pt;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="padding: 5px 8px; text-align: left; font-weight: 800;">Date</th>
                    <th style="padding: 5px 8px; text-align: right; font-weight: 800;">Total Inflow</th>
                    <th style="padding: 5px 8px; text-align: right; font-weight: 800;">MTSS Share</th>
                    <th style="padding: 5px 8px; text-align: right; font-weight: 800;">RDW Share</th>
                    <th style="padding: 5px 8px; text-align: right; font-weight: 800;">CSRF Share</th>
                    <th style="padding: 5px 8px; text-align: right; font-weight: 800;">Project Share</th>
                    <th style="padding: 5px 8px; text-align: center; font-weight: 800;">Milestone #</th>
                    <th style="padding: 5px 8px; text-align: right; font-weight: 800;">Milestone Cost</th>
                    <th style="padding: 5px 8px; text-align: center; font-weight: 800;">Status</th>
                    <th style="padding: 5px 8px; text-align: right; font-weight: 800;">Ref #</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $prtTotInflow = 0;
                    $prtTotMtss = 0;
                    $prtTotRdw = 0;
                    $prtTotCsrf = 0;
                    $prtTotPrj = 0;
                @endphp
                @foreach($installments as $ins)
                    @php
                        $insInward = (float)($ins->inward ?: (($ins->shi_pcc ?? 0) + ($ins->shi_cf ?? 0) + ($ins->mtss_share ?? 0)));
                        $insMtss = (float)($ins->mtss_share ?? 0);
                        $insRdw = $insInward - $insMtss;
                        $insCf = (float)($ins->shi_cf ?? 0);
                        $insPrj = $insRdw - $insCf;
                        $dateFormatted = !empty($ins->trn_date) ? \Carbon\Carbon::parse($ins->trn_date)->format('d-M-Y') : 'N/A';
                        $msnCostVal = (float)($ins->mct_cost ?? 0);

                        $prtTotInflow += $insInward;
                        $prtTotMtss += $insMtss;
                        $prtTotRdw += $insRdw;
                        $prtTotCsrf += $insCf;
                        $prtTotPrj += $insPrj;
                    @endphp
                    <tr>
                        <td style="padding: 4px 8px; text-align: left;" class="text-mono">{{ $dateFormatted }}</td>
                        <td style="padding: 4px 8px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($insInward) }}</td>
                        <td style="padding: 4px 8px; text-align: right;" class="text-mono">{{ number_format($insMtss) }}</td>
                        <td style="padding: 4px 8px; text-align: right;" class="text-mono">{{ number_format($insRdw) }}</td>
                        <td style="padding: 4px 8px; text-align: right;" class="text-mono">{{ number_format($insCf) }}</td>
                        <td style="padding: 4px 8px; text-align: right;" class="text-mono">{{ number_format($insPrj) }}</td>
                        <td style="padding: 4px 8px; text-align: center; font-weight: 700;">{{ $ins->mct_msn_id ?: '—' }}</td>
                        <td style="padding: 4px 8px; text-align: right;" class="text-mono">{{ $msnCostVal > 0 ? number_format($msnCostVal) : '—' }}</td>
                        <td style="padding: 4px 8px; text-align: center;">{{ $ins->msn_status ?: '—' }}</td>
                        <td style="padding: 4px 8px; text-align: right; color: #475569;" class="text-mono">#{{ $ins->shi_id }}</td>
                    </tr>
                @endforeach
                <tr style="background: #f1f5f9; font-weight: 800;">
                    <td style="padding: 5px 8px; text-align: left;">TOTAL RECEIVED</td>
                    <td style="padding: 5px 8px; text-align: right;" class="text-mono">{{ number_format($prtTotInflow) }}</td>
                    <td style="padding: 5px 8px; text-align: right;" class="text-mono">{{ number_format($prtTotMtss) }}</td>
                    <td style="padding: 5px 8px; text-align: right;" class="text-mono">{{ number_format($prtTotRdw) }}</td>
                    <td style="padding: 5px 8px; text-align: right;" class="text-mono">{{ number_format($prtTotCsrf) }}</td>
                    <td style="padding: 5px 8px; text-align: right;" class="text-mono">{{ number_format($prtTotPrj) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($milestones) && count($milestones) > 0)
    {{-- 8. PROJECT MILESTONES & COST ALLOCATION --}}
    <div style="margin-top: 20px; margin-bottom: 24px; page-break-inside: avoid;">
        <div style="font-weight: 800; font-size: 10.5pt; text-transform: uppercase; border-bottom: 1.5px solid #000; padding-bottom: 4px; margin-bottom: 6px; display: flex; justify-content: space-between;">
            <span>8. Project Milestones & Cost Allocation</span>
            <span style="font-size: 8.5pt; font-weight: 600;">{{ count($milestones) }} Total Milestones</span>
        </div>
        <table class="table-bordered-black" style="width: 100%; font-size: 9pt;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="padding: 5px 8px; width: 6%; text-align: center; font-weight: 800;">MS #</th>
                    <th style="padding: 5px 8px; width: 34%; text-align: left; font-weight: 800;">Description</th>
                    <th style="padding: 5px 8px; width: 14%; text-align: right; font-weight: 800;">Milestone Amount</th>
                    <th style="padding: 5px 8px; width: 12%; text-align: right; font-weight: 800;">RDW Share</th>
                    <th style="padding: 5px 8px; width: 11%; text-align: right; font-weight: 800;">MTSS Share</th>
                    <th style="padding: 5px 8px; width: 11%; text-align: right; font-weight: 800;">CSRF Share</th>
                    <th style="padding: 5px 8px; width: 8%; text-align: center; font-weight: 800;">Target Date</th>
                    <th style="padding: 5px 8px; width: 6%; text-align: center; font-weight: 800;">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grossAlloc = (float)($head->allocation ?? 0);
                    $rdwShareRatio = $grossAlloc > 0 ? ((float)($head->rdw_share ?? 0) / $grossAlloc) : 0;
                    $mtssShareRatio = $grossAlloc > 0 ? ((float)($head->mtss_share ?? 0) / $grossAlloc) : 0;
                    $csrfShareRatio = $grossAlloc > 0 ? ((float)($head->csrf_share ?? 0) / $grossAlloc) : 0;

                    $totMsnCost = 0;
                    $totMsnRdw = 0;
                    $totMsnMtss = 0;
                    $totMsnCsrf = 0;
                @endphp
                @foreach($milestones as $m)
                    @php
                        $mCost = (float)($m->mct_cost ?: $m->msn_cost);
                        $mRdw = $mCost * $rdwShareRatio;
                        $mMtss = $mCost * $mtssShareRatio;
                        $mCsrf = $mCost * $csrfShareRatio;

                        $totMsnCost += $mCost;
                        $totMsnRdw += $mRdw;
                        $totMsnMtss += $mMtss;
                        $totMsnCsrf += $mCsrf;
                    @endphp
                    <tr>
                        <td style="padding: 4px 8px; text-align: center; font-weight: 700;">MS-{{ $loop->iteration }}</td>
                        <td style="padding: 4px 8px; text-align: left; font-weight: 600;">{{ $m->msn_desc }}</td>
                        <td style="padding: 4px 8px; text-align: right; font-weight: 700;" class="text-mono">{{ number_format($mCost, 2) }}</td>
                        <td style="padding: 4px 8px; text-align: right;" class="text-mono">{{ number_format($mRdw, 2) }}</td>
                        <td style="padding: 4px 8px; text-align: right;" class="text-mono">{{ number_format($mMtss, 2) }}</td>
                        <td style="padding: 4px 8px; text-align: right;" class="text-mono">{{ number_format($mCsrf, 2) }}</td>
                        <td style="padding: 4px 8px; text-align: center;" class="text-mono">{{ $m->msn_targetdt ? \Carbon\Carbon::parse($m->msn_targetdt)->format('d-M-Y') : 'N/A' }}</td>
                        <td style="padding: 4px 8px; text-align: center; font-weight: 700;">{{ strtoupper($m->msn_status ?: 'PENDING') }}</td>
                    </tr>
                @endforeach
                <tr style="background: #f1f5f9; font-weight: 800;">
                    <td colspan="2" style="padding: 5px 8px; text-align: left;">TOTAL MILESTONES COST</td>
                    <td style="padding: 5px 8px; text-align: right;" class="text-mono">{{ number_format($totMsnCost, 2) }}</td>
                    <td style="padding: 5px 8px; text-align: right;" class="text-mono">{{ number_format($totMsnRdw, 2) }}</td>
                    <td style="padding: 5px 8px; text-align: right;" class="text-mono">{{ number_format($totMsnMtss, 2) }}</td>
                    <td style="padding: 5px 8px; text-align: right;" class="text-mono">{{ number_format($totMsnCsrf, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    {{-- 9. OFFICIAL SIGNATURES BLOCK --}}
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
