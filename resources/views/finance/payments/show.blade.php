@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .finance-hub {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg) !important;
        min-height: 85vh;
        color: var(--rd-text1);
        padding-top: 20px;
        padding-bottom: 50px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-cyber {
        background: var(--rd-surface);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .table-cyber {
        background: transparent;
        color: var(--rd-text1);
    }
    .table-cyber th {
        background: rgba(255, 255, 255, 0.03) !important;
        border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
        color: #67e8f9 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        font-size: 12px;
        font-weight: 700;
        padding: 10px 14px !important;
    }
    .table-cyber td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        padding: 10px 14px !important;
        vertical-align: middle;
        font-size: 13px;
    }

    .form-control-cyber {
        background: rgba(0, 0, 0, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #fff;
        border-radius: 8px;
        font-size: 14px;
    }
    .form-control-cyber:focus {
        background: rgba(0, 0, 0, 0.35);
        border-color: #00BFFF;
        color: #fff;
        box-shadow: 0 0 8px rgba(0, 191, 255, 0.25);
    }

    .calc-table th, .calc-table td {
        padding: 8px 12px !important;
        font-size: 13px;
    }
</style>

<div class="content-wrapper finance-hub px-4">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('fin.payments.index') }}" class="text-info text-decoration-none small">
                <i class="fas fa-chevron-left mr-1"></i> Back to Commitments List
            </a>
            <h2 class="text-white rajdhani font-weight-bold mb-1 mt-1">
                Commitment #{{ $commitment->cmt_id }} <span class="text-muted font-weight-normal">&mdash; Settle Payment</span>
            </h2>
            <p class="text-muted small mb-0">Case #{{ $commitment->pcs_id }}: <strong>{{ $commitment->pcs_title }}</strong></p>
        </div>
        <div>
            @if($commitment->cmt_status === 'Awaited')
                <span class="badge badge-warning px-3 py-2 rajdhani" style="font-size: 14px;">STATUS: AWAITED</span>
            @elseif($commitment->cmt_status === 'Paid')
                <span class="badge badge-success px-3 py-2 rajdhani" style="font-size: 14px;">STATUS: PAID</span>
            @elseif($commitment->cmt_status === 'Cancelled')
                <span class="badge badge-danger px-3 py-2 rajdhani" style="font-size: 14px;">STATUS: CANCELLED</span>
            @else
                <span class="badge badge-secondary px-3 py-2 rajdhani" style="font-size: 14px;">STATUS: {{ $commitment->cmt_status }}</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- LEFT COLUMN: Summaries and Transaction History -->
        <div class="col-lg-6 mb-4">
            <!-- 1. Commitment & Purchase Case Summary Cards -->
            <div class="card card-cyber p-4 mb-4">
                <h5 class="text-white rajdhani font-weight-bold mb-3 border-bottom border-secondary pb-2">
                    <i class="fas fa-file-invoice text-info mr-2"></i> Commitment &amp; Case Summary
                </h5>
                
                <div class="row g-3">
                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Commitment ID &amp; Date:</span>
                        <strong class="text-white">#{{ $commitment->cmt_id }}</strong> 
                        <span class="text-muted">({{ $commitment->cmt_date ? date('d-M-Y', strtotime($commitment->cmt_date)) : 'N/A' }})</span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Charge Head (Initiator):</span>
                        <strong class="text-info">{{ $commitment->eff_hed_code }}</strong> 
                        <span class="text-muted small">({{ $commitment->eff_unt_namesh }})</span>
                    </div>

                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Purchase Case ID &amp; Min:</span>
                        <a href="{{ route('nrdi.purchase_cases_new.show', $commitment->pcs_id) }}" class="text-cyan font-weight-bold" target="_blank">
                            #{{ $commitment->pcs_id }} <i class="fas fa-external-link-alt small"></i>
                        </a>
                        <span class="text-muted ml-2">Min #{{ $commitment->pcs_minute ?? '-' }}</span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Target Head / "For":</span>
                        @if($commitment->for_hed_code)
                            <strong class="text-white">{{ $commitment->for_hed_code }}</strong>
                            <span class="text-muted small">({{ $commitment->for_unt_namesh }})</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>

                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Firm / Vendor:</span>
                        <strong class="text-white">{{ $commitment->frm_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Case Type:</span>
                        <span class="badge badge-info">{{ strtoupper($commitment->pcs_type) }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. Previous Payments Table -->
            <div class="card card-cyber p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary pb-2">
                    <h5 class="text-white rajdhani font-weight-bold mb-0">
                        <i class="fas fa-history text-success mr-2"></i> Previous Payments
                    </h5>
                    <span class="badge badge-secondary px-2 py-1 rajdhani" style="background: rgba(255,255,255,0.08);">
                        {{ $transactions->count() }} INSTALLMENT(S)
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-cyber mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th class="text-right">Price (Pre-Tax)</th>
                                <th class="text-right">Tax</th>
                                <th class="text-right">Final Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $t)
                                <tr>
                                    <td class="rajdhani text-muted">#{{ $t->trn_seq }}</td>
                                    <td>{{ $t->trn_date ? date('d-M-Y', strtotime($t->trn_date)) : 'N/A' }}</td>
                                    <td class="text-right rajdhani text-white">PKR {{ number_format(abs((float)$t->trn_amount1), 2) }}</td>
                                    <td class="text-right rajdhani text-muted">{{ number_format(abs((float)$t->trn_tax1), 2) }}</td>
                                    <td class="text-right rajdhani font-weight-bold text-success">PKR {{ number_format(abs((float)$t->trn_amount2), 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted">
                                        No payment installments recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($transactions->isNotEmpty())
                            <tfoot>
                                <tr style="border-top: 2px solid rgba(255, 255, 255, 0.1);">
                                    <th colspan="2" class="text-white rajdhani">TOTAL PAID</th>
                                    <th class="text-right rajdhani text-white">PKR {{ number_format($aa, 2) }}</th>
                                    <th class="text-right rajdhani text-muted">{{ number_format($at, 2) }}</th>
                                    <th class="text-right rajdhani font-weight-bold text-success">PKR {{ number_format($aat, 2) }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Add Payment Section & Settle Form -->
        <div class="col-lg-6 mb-4">
            <div class="card card-cyber p-4">
                <h5 class="text-white rajdhani font-weight-bold mb-3 border-bottom border-secondary pb-2">
                    <i class="fas fa-coins text-warning mr-2"></i> Payment Settlement &amp; Calculation
                </h5>

                @if($commitment->cmt_status === 'Paid')
                    <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-3 small">
                        <i class="fas fa-check-circle mr-1"></i> This commitment is marked as <strong>Fully Paid</strong>. You may still append adjustment transactions or update remarks.
                    </div>
                @endif

                <!-- Financial Calculation Matrix (Legacy Table Layout) -->
                <div class="table-responsive mb-4">
                    <table class="table table-cyber calc-table table-bordered mb-0" style="border-color: rgba(255,255,255,0.08);">
                        <thead>
                            <tr class="bg-dark">
                                <th style="width: 35%;">Category</th>
                                <th class="text-right">Price (Amount)</th>
                                <th class="text-right">Tax</th>
                                <th class="text-right text-cyan">Amount + Tax</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-muted font-weight-bold">Purchase Case:</td>
                                <td class="text-right rajdhani">{{ number_format($pa, 2) }}</td>
                                <td class="text-right rajdhani">{{ number_format($pt, 2) }}</td>
                                <td class="text-right rajdhani font-weight-bold text-info">{{ number_format($pat, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted font-weight-bold">Already Paid:</td>
                                <td class="text-right rajdhani text-success">{{ number_format($aa, 2) }}</td>
                                <td class="text-right rajdhani text-success">{{ number_format($at, 2) }}</td>
                                <td class="text-right rajdhani font-weight-bold text-success">{{ number_format($aat, 2) }}</td>
                            </tr>
                            <tr class="bg-dark bg-opacity-50">
                                <td class="text-warning font-weight-bold">Remaining:</td>
                                <td class="text-right rajdhani font-weight-bold text-warning" id="disp_ra1">{{ number_format($ra1, 2) }}</td>
                                <td class="text-right rajdhani font-weight-bold text-warning" id="disp_rt1">{{ number_format($rt1, 2) }}</td>
                                <td class="text-right rajdhani font-weight-bold text-warning" id="disp_rat1">{{ number_format($rat1, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Form -->
                <form action="{{ route('fin.payments.store_transaction', $commitment->cmt_id) }}" method="POST" id="settleForm">
                    @csrf

                    <h6 class="text-cyan rajdhani font-weight-bold mb-3">Add New Payment Installment</h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="trn_date" 
                                   id="trn_date" 
                                   class="form-control form-control-cyber" 
                                   value="{{ old('trn_date', date('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Amount (Pre-Tax)</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="amount" 
                                   id="input_na" 
                                   class="form-control form-control-cyber text-right" 
                                   placeholder="0.00" 
                                   value="{{ old('amount', $commitment->cmt_status === 'Awaited' && $ra1 > 0 ? $ra1 : '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small mb-1">Tax Amount</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="tax" 
                                   id="input_nt" 
                                   class="form-control form-control-cyber text-right" 
                                   placeholder="0.00" 
                                   value="{{ old('tax', $commitment->cmt_status === 'Awaited' && $rt1 > 0 ? $rt1 : 0) }}">
                        </div>
                    </div>

                    <!-- Live Remaining Preview Box -->
                    <div class="p-3 mb-3 rounded" style="background: rgba(0, 191, 255, 0.05); border: 1px dashed rgba(0, 191, 255, 0.25);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small d-block">Installment Total (Amount + Tax):</span>
                                <strong class="text-white rajdhani" id="preview_nat" style="font-size: 16px;">PKR 0.00</strong>
                            </div>
                            <div class="text-right">
                                <span class="text-muted small d-block">Projected Remaining Balance:</span>
                                <strong class="text-warning rajdhani" id="preview_rat2" style="font-size: 16px;">PKR {{ number_format($rat1, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="form-group mb-3">
                        <label class="text-muted small mb-1">Commitment Remarks</label>
                        <textarea name="cmt_remarks" 
                                  rows="2" 
                                  class="form-control form-control-cyber" 
                                  placeholder="Enter cheque #, voucher reference, or remarks...">{{ old('cmt_remarks', $commitment->cmt_remarks) }}</textarea>
                    </div>

                    <!-- Close Commitment Checkbox -->
                    <div class="form-check mb-4 p-3 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_complete" 
                               name="is_complete" 
                               value="1" 
                               {{ old('is_complete', $commitment->cmt_status === 'Paid' ? 'checked' : '') }}>
                        <label class="form-check-label text-white small ml-2" for="is_complete">
                            <strong>Close Commitment (Mark as Paid)</strong>
                            <span class="text-muted d-block small">Check this when the final installment is paid to close the commitment.</span>
                        </label>
                    </div>

                    <!-- Implement Button (cmdSettle) -->
                    <button type="submit" class="btn btn-success btn-block py-2 rajdhani font-weight-bold" style="font-size: 16px; letter-spacing: 0.5px;">
                        <i class="fas fa-check-circle mr-2"></i> IMPLEMENT (RECORD &amp; SETTLE)
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ra1 = {{ $ra1 }};
        const rt1 = {{ $rt1 }};
        const rat1 = {{ $rat1 }};

        const inputNa = document.getElementById('input_na');
        const inputNt = document.getElementById('input_nt');
        const previewNat = document.getElementById('preview_nat');
        const previewRat2 = document.getElementById('preview_rat2');
        const chkComplete = document.getElementById('is_complete');

        function recalculate() {
            const na = parseFloat(inputNa.value) || 0;
            const nt = parseFloat(inputNt.value) || 0;
            const nat = na + nt;

            const rat2 = Math.max(0, rat1 - nat);

            previewNat.textContent = 'PKR ' + nat.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            previewRat2.textContent = 'PKR ' + rat2.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Auto-check complete if remaining balance reaches zero
            if (rat2 <= 0.01 && nat > 0) {
                chkComplete.checked = true;
            }
        }

        inputNa.addEventListener('input', recalculate);
        inputNt.addEventListener('input', recalculate);
        recalculate();
    });
</script>
@endsection
