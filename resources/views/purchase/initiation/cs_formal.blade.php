<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Formal Comparative Statement - #{{ $purchase->pcs_id }}</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
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
            line-height: 1.6;
        }
        .live-editable {
            font-weight: bold;
            border-bottom: 1.5px dashed #2563eb;
            padding: 0 4px;
            outline: none;
            cursor: text;
            transition: background 0.2s;
        }
        .live-editable:hover, .live-editable:focus {
            background: #eff6ff;
            border-bottom: 1.5px solid #1d4ed8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        .computer-gen-note {
            margin-top: 35px;
            padding-top: 12px;
            border-top: 1px dashed #94a3b8;
            font-size: 8.5pt;
            color: #334155;
            font-style: italic;
        }

        /* Top Action Bar (Screen Only) */
        .cs-top-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 20px;
            margin: -20px -20px 20px -20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
            z-index: 99999;
            gap: 12px;
        }
        .cs-btn {
            border: none;
            padding: 7px 16px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            font-family: Arial, sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .cs-btn-back {
            background: rgba(255, 255, 255, 0.12);
            color: #e2e8f0;
        }
        .cs-btn-back:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
        }
        .cs-btn-save {
            background: #2563eb;
            color: #ffffff;
        }
        .cs-btn-save:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        .cs-btn-print {
            background: #15803d;
            color: #ffffff;
        }
        .cs-btn-print:hover {
            background: #166534;
            transform: translateY(-1px);
        }

        @media print {
            body { padding: 0; margin: 0; }
            .cs-top-bar { display: none !important; }
            .live-editable {
                border-bottom: none !important;
                background: transparent !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

    {{-- Top Action Bar (Screen Only) --}}
    <div class="cs-top-bar">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="javascript:history.back()" class="cs-btn cs-btn-back">
                <i class="fas fa-arrow-left"></i> Back to Case #{{ $purchase->pcs_id }}
            </a>
            <span style="font-size: 12px; color: #94a3b8; display: inline-flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span>
                Live DB Connected
            </span>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="cs-btn cs-btn-save" id="btnSaveCsDetails" onclick="saveLiveCsDetails()">
                <i class="fas fa-save"></i> Save Details to DB
            </button>
            <button type="button" class="cs-btn cs-btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Statement
            </button>
        </div>
    </div>

    <div class="container">
        <div class="main-title">
            COMPARATIVE STATEMENT OF PROCUREMENT OF MATERIAL FOR {{ strtoupper($purchase->pcs_title) }}
        </div>
        <div class="subtitle">
            Following firms participated in bidding on 
            <span id="csBiddingDate" contenteditable="true" class="live-editable" title="Click to edit Bidding Date (Live DB)">{{ $biddingDate }}</span> 
            against RFQ No 
            <span id="csRfqNo" contenteditable="true" class="live-editable" title="Click to edit RFQ No (Live DB)">{{ $rfqNo }}</span> 
            dated 
            <span id="csRfqDate" contenteditable="true" class="live-editable" title="Click to edit RFQ Date (Live DB)">{{ $rfqDate }}</span>.
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
                @forelse($purchase->quotes->sortBy('qte_price') as $idx => $q)
                @php
                    $frmId = $q->qte_frm_id ?: ($q->firm?->frm_id ?? null);
                    $firmRow = $frmId ? DB::table('frm.firmz')->where('frm_id', $frmId)->first() : null;
                    $office = $frmId ? DB::table('frm.offices')->where('off_xfrm_id', $frmId)->first() : null;
                    $person = $frmId ? DB::table('frm.persons')->where('per_xfrm_id', $frmId)->first() : null;
                    $contacts = $frmId ? DB::table('frm.info')->where('inf_xmsc_id', $frmId)->get() : collect();

                    $addr = $office?->off_address 
                        ? ($office->off_address . (!empty($office->off_city) ? ', ' . $office->off_city : '')) 
                        : ($firmRow?->frm_notes ?: ($q->firm?->frm_address ?? 'N/A'));
                    $ntn = $firmRow?->frm_ntn ?: ($q->firm?->frm_ntn ?? '-');
                    $gst = $firmRow?->frm_gst ?: ($q->firm?->frm_gst ?? '-');
                    
                    $phoneContact = $contacts->first(function($c) {
                        return in_array(strtolower($c->inf_type ?? ''), ['phone', 'mobile', 'tel', 'cell', 'landline']);
                    });
                    $emailContact = $contacts->first(function($c) {
                        return in_array(strtolower($c->inf_type ?? ''), ['email', 'mail', 'e-mail']);
                    });

                    $phone = $phoneContact?->inf_value ?: ($q->firm?->frm_contact ?? ($person?->per_name ?? 'N/A'));
                    $email = $emailContact?->inf_value ?: ($q->firm?->frm_email ?? 'N/A');
                    
                    $dealer = $person?->per_name 
                        ? ($person->per_name . (!empty($person->per_desig) ? ' (' . $person->per_desig . ')' : '')) 
                        : ($firmRow?->frm_entity ?? '-');

                    $qTot = (float)($q->qte_price ?: 0);
                    $qSst = (float)($q->qte_inttax ?? 0);
                    $qGst = (float)($q->qte_midtax ?? 0);
                    $qBase = (float)($q->qte_intprice ?: 0);
                    if ($qBase <= 0 && $qTot > 0) {
                        $qBase = max(0, $qTot - $qSst - $qGst);
                    }
                    if ($qTot <= 0 && $qBase > 0) {
                        $qTot = $qBase + $qSst + $qGst;
                    }

                    $firmDisplayName = $firmRow?->frm_name ?: ($q->firm?->frm_name ?: ($q->qte_firmname ?: 'N/A'));
                    $quotationNumber = $q->qte_num ?: ($q->qte_refno ?? 'N/A');
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td style="font-weight: bold;">
                        M/s {{ strtoupper($firmDisplayName) }}
                        @if($q->qte_recomm || $idx === 0)
                            <span style="font-size: 8pt; color: #166534; font-weight: bold; display: block;">({{ $q->qte_recomm ? 'Recommended' : 'Lowest / L-1' }})</span>
                        @endif
                    </td>
                    <td>
                        {{ $q->qte_date ? \Carbon\Carbon::parse($q->qte_date)->format('d-M-Y') : 'N/A' }}<br>
                        <strong>{{ $quotationNumber }}</strong>
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        {{ number_format($qTot, 2) }}
                        @if($qSst > 0 || $qGst > 0)
                            <div style="font-size: 7.5pt; font-weight: normal; color: #555;">
                                Base: {{ number_format($qBase, 2) }}
                                @if($qSst > 0) | SST: {{ number_format($qSst, 2) }}@endif
                                @if($qGst > 0) | GST: {{ number_format($qGst, 2) }}@endif
                            </div>
                        @endif
                    </td>
                    <td>{{ $addr }}</td>
                    <td>
                        <div><strong>NTN:</strong> {{ $ntn }}</div>
                        <div><strong>STRN:</strong> {{ $gst }}</div>
                    </td>
                    <td>
                        <div><strong>Tel:</strong> {{ $phone }}</div>
                        <div><strong>Email:</strong> {{ $email }}</div>
                    </td>
                    <td style="text-align: center;">{{ $dealer }}</td>
                    <td style="text-align: center;">{{ $q->qte_techaccept ? 'Accepted' : 'Rejected' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 25px; font-style: italic; color: #777;">
                        No quotations recorded for this purchase case.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @php
                $cbd = $purchase->tax_breakdown;
                $evalTotal = (float)($cbd['total'] ?? ($purchase->pcs_price ?? 0));
                $evalBase = (float)($cbd['base'] ?? 0);
                $evalSst = (float)($cbd['sst'] ?? 0);
                $evalGst = (float)($cbd['gst'] ?? 0);
                if ($evalBase <= 0 && $evalTotal > 0) {
                    $evalBase = max(0, $evalTotal - $evalSst - $evalGst);
                }
            @endphp
            @if($evalTotal > 0)
            <tfoot>
                <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #000;">
                    <td colspan="3" style="text-align: right; padding: 8px;">
                        CASE EVALUATED TOTAL (PKR):
                    </td>
                    <td style="text-align: right; padding: 8px; color: #166534; font-size: 10pt;">
                        {{ number_format($evalTotal, 2) }}
                        @if($evalSst > 0 || $evalGst > 0)
                            <div style="font-size: 7.5pt; font-weight: normal; color: #555;">
                                Base: {{ number_format($evalBase, 2) }}
                                @if($evalSst > 0) | SST: {{ number_format($evalSst, 2) }}@endif
                                @if($evalGst > 0) | GST: {{ number_format($evalGst, 2) }}@endif
                            </div>
                        @endif
                    </td>
                    <td colspan="5" style="font-size: 8pt; color: #555; vertical-align: middle;">
                        {{ $purchase->pcs_recomm ?: 'Evaluated as per lowest technically acceptable offer.' }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>

        @if($purchase->noQuotes && $purchase->noQuotes->count() > 0)
        <div style="margin-top: 15px; margin-bottom: 20px; font-size: 8.5pt;">
            <strong>Quotes Not Received / Regret ({{ $purchase->noQuotes->count() }}):</strong>
            <span>
                @foreach($purchase->noQuotes as $nq)
                    {{ $nq->firm->frm_name ?? $nq->nqt_firmname }}@if(!$loop->last), @endif
                @endforeach
            </span>
        </div>
        @endif

        <div class="computer-gen-note" style="margin-top: 35px; padding-top: 12px; border-top: 1px dashed #94a3b8; font-size: 8.5pt; color: #334155; font-style: italic;">
            Note: Computer generated documents do not require signature
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script>
        function saveLiveCsDetails() {
            const btn = document.getElementById('btnSaveCsDetails');
            const biddingDateEl = document.getElementById('csBiddingDate');
            const rfqNoEl = document.getElementById('csRfqNo');
            const rfqDateEl = document.getElementById('csRfqDate');

            const biddingDate = biddingDateEl ? biddingDateEl.innerText.trim() : '';
            const rfqNo = rfqNoEl ? rfqNoEl.innerText.trim() : '';
            const rfqDate = rfqDateEl ? rfqDateEl.innerText.trim() : '';

            const origHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                btn.disabled = true;
            }

            const token = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

            fetch('{{ route("purchase.cs_formal.save", $purchase->pcs_id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    bidding_date: biddingDate,
                    rfq_no: rfqNo,
                    rfq_date: rfqDate
                })
            })
            .then(res => res.json())
            .then(data => {
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-check"></i> Saved!';
                    setTimeout(() => {
                        btn.innerHTML = origHtml;
                        btn.disabled = false;
                    }, 2000);
                }
                alert(data.message || 'Details saved to database successfully!');
            })
            .catch(err => {
                if (btn) {
                    btn.innerHTML = origHtml;
                    btn.disabled = false;
                }
                alert('Failed to save details: ' + (err.message || 'Unknown error'));
            });
        }
    </script>
</body>
</html>
