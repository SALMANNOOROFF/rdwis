@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .finance-hub {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg, #F7F5F0) !important;
        min-height: 85vh;
        color: var(--rd-neutral-900, #292824);
        padding-top: 20px;
        padding-bottom: 50px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-clean {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    }

    .table-clean {
        background: #ffffff;
        color: #1e293b;
    }
    .table-clean th {
        background: #f8fafc !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-top: none !important;
        color: #475569 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 12px;
        font-weight: 700;
        padding: 11px 14px !important;
    }
    .table-clean td {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 11px 14px !important;
        vertical-align: middle;
        font-size: 13.5px;
        color: #1e293b;
    }

    .form-control-clean {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #0f172a;
        border-radius: 8px;
        font-size: 14px;
        padding: 8px 12px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control-clean:focus {
        background: #ffffff;
        border-color: #5F7858;
        color: #0f172a;
        box-shadow: 0 0 0 3px rgba(95, 120, 88, 0.15);
        outline: none;
    }

    .calc-table th, .calc-table td {
        padding: 10px 14px !important;
        font-size: 13.5px;
    }

    .remaining-row {
        background: #fffbeb !important;
        border-top: 2px solid #fef3c7 !important;
    }
    .remaining-row td {
        color: #b45309 !important;
        font-weight: 700;
    }

    .btn-settle-action {
        background: #5F7858;
        border: 1px solid #5F7858;
        color: #ffffff;
        border-radius: 8px;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .btn-settle-action:hover {
        background: #4E6449;
        border-color: #4E6449;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(95, 120, 88, 0.25);
    }
</style>

<div class="content-wrapper finance-hub px-4">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('fin.payments.index') }}" class="text-secondary text-decoration-none small font-weight-bold">
                <i class="fas fa-chevron-left mr-1"></i> Back to Commitments List
            </a>
            <h2 class="text-dark rajdhani font-weight-bold mb-1 mt-1" style="font-size: 1.75rem;">
                Commitment #{{ $commitment->cmt_id }} <span class="text-muted font-weight-normal">&mdash; Settle Payment</span>
            </h2>
            <p class="text-muted small mb-0">Case #{{ $commitment->pcs_id }}: <strong class="text-dark">{{ $commitment->pcs_title }}</strong></p>
        </div>
        <div>
            @if($commitment->cmt_status === 'Awaited')
                <span class="badge badge-warning px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700;">STATUS: AWAITED</span>
            @elseif($commitment->cmt_status === 'Paid')
                <span class="badge badge-success px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700;">STATUS: PAID</span>
            @elseif($commitment->cmt_status === 'Cancelled')
                <span class="badge badge-danger px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700;">STATUS: CANCELLED</span>
            @else
                <span class="badge badge-secondary px-3 py-2 rajdhani" style="font-size: 13px; font-weight: 700;">STATUS: {{ $commitment->cmt_status }}</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4" style="border-radius: 8px;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4" style="border-radius: 8px;">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- LEFT COLUMN: Summaries and Transaction History -->
        <div class="col-lg-6 mb-4">
            <!-- 1. Commitment & Purchase Case Summary Cards -->
            <div class="card card-clean p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="text-dark rajdhani font-weight-bold mb-0">
                        <i class="fas fa-file-invoice text-primary mr-2"></i> Commitment &amp; Case Summary
                    </h5>
                    @if(Gate::check('initiate', \App\Models\AudRev::class))
                        <button type="button" class="btn btn-outline-danger btn-sm rajdhani font-weight-bold" data-toggle="modal" data-target="#reverseCommitmentModal">
                            <i class="fas fa-sync-alt mr-1"></i> REVERSE COMMITMENT
                        </button>

                        <!-- Reverse Commitment Modal -->
                        <div class="modal fade" id="reverseCommitmentModal" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content border-0 shadow-lg">
                                    <form action="{{ route('finance.payments.commitments.reverse', $commitment->cmt_id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header bg-danger text-white py-2">
                                            <h6 class="modal-title font-weight-bold mb-0">
                                                <i class="fas fa-sync-alt mr-1"></i> Reverse Commitment #{{ $commitment->cmt_id }}
                                            </h6>
                                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body p-3">
                                            <p class="small text-muted mb-2">
                                                This will generate a Data Revision request (RevType 1: Full Cascade) to reverse the status of this commitment to Awaited.
                                            </p>
                                            <div class="form-group mb-0">
                                                <label class="font-weight-bold small text-dark">Reason for Reversal <span class="text-danger">*</span></label>
                                                <textarea name="rev_reason" class="form-control form-control-sm" rows="3" placeholder="Enter reason for revision..." required></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light py-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-sm btn-danger font-weight-bold px-3">
                                                <i class="fas fa-check mr-1"></i> Generate Reversal Draft
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="row g-3">
                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Commitment ID &amp; Date:</span>
                        <strong class="text-dark">#{{ $commitment->cmt_id }}</strong> 
                        <span class="text-muted">({{ $commitment->cmt_date ? date('d-M-Y', strtotime($commitment->cmt_date)) : 'N/A' }})</span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Charge Head (Initiator):</span>
                        <strong class="text-primary">{{ $commitment->eff_hed_code }}</strong> 
                        <span class="text-muted small">({{ $commitment->eff_unt_namesh }})</span>
                    </div>

                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Purchase Case ID &amp; Min:</span>
                        <a href="{{ route('nrdi.purchase_cases_new.show', $commitment->pcs_id) }}" class="text-primary font-weight-bold" target="_blank">
                            #{{ $commitment->pcs_id }} <i class="fas fa-external-link-alt small"></i>
                        </a>
                        <span class="text-muted ml-2">Min #{{ $commitment->pcs_minute ?? '-' }}</span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Target Head / "For":</span>
                        @if($commitment->for_hed_code)
                            <strong class="text-dark">{{ $commitment->for_hed_code }}</strong>
                            <span class="text-muted small">({{ $commitment->for_unt_namesh }})</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>

                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Firm / Vendor:</span>
                        <strong class="text-dark">{{ $commitment->frm_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <span class="text-muted small d-block">Case Type:</span>
                        <span class="badge badge-secondary px-2 py-1 font-weight-bold">{{ strtoupper($commitment->pcs_type) }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. Previous Payments Table -->
            <div class="card card-clean p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="text-dark rajdhani font-weight-bold mb-0">
                        <i class="fas fa-history text-success mr-2"></i> Previous Payments
                    </h5>
                    <span class="badge badge-light border px-2 py-1 rajdhani text-dark font-weight-bold">
                        {{ $transactions->count() }} INSTALLMENT(S)
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-clean mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th class="text-right">Price (Pre-Tax)</th>
                                <th class="text-right">Tax</th>
                                <th class="text-right">Final Price</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $t)
                                <tr>
                                    <td class="rajdhani text-muted font-weight-bold">#{{ $t->trn_seq }}</td>
                                    <td>{{ $t->trn_date ? date('d-M-Y', strtotime($t->trn_date)) : 'N/A' }}</td>
                                    <td class="text-right rajdhani font-weight-bold text-dark">PKR {{ number_format(abs((float)$t->trn_amount1), 2) }}</td>
                                    <td class="text-right rajdhani text-muted">{{ number_format(abs((float)$t->trn_tax1), 2) }}</td>
                                    <td class="text-right rajdhani font-weight-bold text-success">PKR {{ number_format(abs((float)$t->trn_amount2), 2) }}</td>
                                    <td class="text-center">
                                        @if(Gate::check('initiate', \App\Models\AudRev::class))
                                            <button type="button" class="btn btn-outline-danger btn-xs font-weight-bold" data-toggle="modal" data-target="#reversePaymentModal{{ $t->trn_id }}">
                                                <i class="fas fa-sync-alt mr-1"></i> Reverse
                                            </button>

                                            <!-- Reverse Payment Modal -->
                                            <div class="modal fade text-left" id="reversePaymentModal{{ $t->trn_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content border-0 shadow-lg">
                                                        <form action="{{ route('finance.payments.transactions.reverse', $t->trn_id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header bg-danger text-white py-2">
                                                                <h6 class="modal-title font-weight-bold mb-0">
                                                                    <i class="fas fa-sync-alt mr-1"></i> Reverse Payment #{{ $t->trn_id }}
                                                                </h6>
                                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body p-3">
                                                                <p class="small text-muted mb-2">
                                                                    This will generate a Data Revision request (RevType 3: Linked Cascade) for Payment installment #{{ $t->trn_id }}.
                                                                </p>
                                                                <div class="form-group mb-0">
                                                                    <label class="font-weight-bold small text-dark">Reason for Reversal <span class="text-danger">*</span></label>
                                                                    <textarea name="rev_reason" class="form-control form-control-sm" rows="3" placeholder="Enter reason for revision..." required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light py-2">
                                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-sm btn-danger font-weight-bold px-3">
                                                                    <i class="fas fa-check mr-1"></i> Generate Reversal Draft
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No payment installments recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($transactions->isNotEmpty())
                            <tfoot>
                                <tr style="border-top: 2px solid #e2e8f0; background: #f8fafc;">
                                    <th colspan="2" class="text-dark rajdhani font-weight-bold">TOTAL PAID</th>
                                    <th class="text-right rajdhani text-dark font-weight-bold">PKR {{ number_format($aa, 2) }}</th>
                                    <th class="text-right rajdhani text-muted font-weight-bold">{{ number_format($at, 2) }}</th>
                                    <th class="text-right rajdhani font-weight-bold text-success">PKR {{ number_format($aat, 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Add Payment Section & Settle Form -->
        <div class="col-lg-6 mb-4">
            <div class="card card-clean p-4">
                <h5 class="text-dark rajdhani font-weight-bold mb-3 border-bottom pb-2">
                    <i class="fas fa-coins text-warning mr-2"></i> Payment Settlement &amp; Calculation
                </h5>

                @if($commitment->cmt_status === 'Paid')
                    <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-3 small" style="border-radius: 8px;">
                        <i class="fas fa-check-circle mr-1"></i> This commitment is marked as <strong>Fully Paid</strong>. You may still append adjustment transactions or update remarks.
                    </div>
                @endif

                <!-- Financial Calculation Matrix -->
                <div class="table-responsive mb-4">
                    <table class="table table-clean calc-table table-bordered mb-0" style="border-color: #e2e8f0;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="width: 35%; color: #475569;">CATEGORY</th>
                                <th class="text-right" style="color: #475569;">PRICE (AMOUNT)</th>
                                <th class="text-right" style="color: #475569;">TAX</th>
                                <th class="text-right text-primary">AMOUNT + TAX</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-dark font-weight-bold">Purchase Case:</td>
                                <td class="text-right rajdhani text-dark">{{ number_format($pa, 2) }}</td>
                                <td class="text-right rajdhani text-muted">{{ number_format($pt, 2) }}</td>
                                <td class="text-right rajdhani font-weight-bold text-dark">{{ number_format($pat, 2) }}</td>
                            </tr>
                            <tr style="background: #f0fdf4;">
                                <td class="text-success font-weight-bold">Already Paid:</td>
                                <td class="text-right rajdhani text-success">{{ number_format($aa, 2) }}</td>
                                <td class="text-right rajdhani text-success">{{ number_format($at, 2) }}</td>
                                <td class="text-right rajdhani font-weight-bold text-success">{{ number_format($aat, 2) }}</td>
                            </tr>
                            <tr class="remaining-row">
                                <td class="font-weight-bold">Remaining:</td>
                                <td class="text-right rajdhani font-weight-bold" id="disp_ra1">{{ number_format($ra1, 2) }}</td>
                                <td class="text-right rajdhani font-weight-bold" id="disp_rt1">{{ number_format($rt1, 2) }}</td>
                                <td class="text-right rajdhani font-weight-bold" id="disp_rat1">{{ number_format($rat1, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Form -->
                <form action="{{ route('fin.payments.store_transaction', $commitment->cmt_id) }}" method="POST" id="settleForm">
                    @csrf

                    <h6 class="text-primary rajdhani font-weight-bold mb-3">
                        <i class="fas fa-plus-circle mr-1"></i> Add New Payment Installment
                    </h6>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="text-dark small font-weight-bold mb-1">Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   name="trn_date" 
                                   id="trn_date" 
                                   class="form-control form-control-clean" 
                                   value="{{ old('trn_date', date('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="text-dark small font-weight-bold mb-1">Amount (Pre-Tax)</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="amount" 
                                   id="input_na" 
                                   class="form-control form-control-clean text-right font-weight-bold" 
                                   placeholder="0.00" 
                                   value="{{ old('amount', $commitment->cmt_status === 'Awaited' && $ra1 > 0 ? $ra1 : '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="text-dark small font-weight-bold mb-1">Tax Amount</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="tax" 
                                   id="input_nt" 
                                   class="form-control form-control-clean text-right font-weight-bold" 
                                   placeholder="0.00" 
                                   value="{{ old('tax', $commitment->cmt_status === 'Awaited' && $rt1 > 0 ? $rt1 : 0) }}">
                        </div>
                    </div>

                    <!-- Live Remaining Preview Box -->
                    <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small d-block">Installment Total (Amount + Tax):</span>
                                <strong class="text-dark rajdhani" id="preview_nat" style="font-size: 16px;">PKR 0.00</strong>
                            </div>
                            <div class="text-right">
                                <span class="text-muted small d-block">Projected Remaining Balance:</span>
                                <strong class="rajdhani" id="preview_rat2" style="font-size: 16px; color: #b45309;">PKR {{ number_format($rat1, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="form-group mb-3">
                        <label class="text-dark small font-weight-bold mb-1">Commitment Remarks</label>
                        <textarea name="cmt_remarks" 
                                  rows="2" 
                                  class="form-control form-control-clean" 
                                  placeholder="Enter cheque #, voucher reference, or remarks...">{{ old('cmt_remarks', $commitment->cmt_remarks) }}</textarea>
                    </div>

                    <!-- Close Commitment Checkbox -->
                    <div class="form-check mb-4 p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_complete" 
                               name="is_complete" 
                               value="1" 
                               {{ old('is_complete', $commitment->cmt_status === 'Paid' ? 'checked' : '') }}
                               style="margin-top: 4px;">
                        <label class="form-check-label text-dark small ml-2" for="is_complete">
                            <strong class="d-block text-dark">Close Commitment (Mark as Paid)</strong>
                            <span class="text-muted d-block small">Check this when the final installment is paid to close the commitment.</span>
                        </label>
                    </div>

                    <!-- Implement Button (cmdSettle) -->
                    <button type="submit" class="btn btn-settle-action btn-block py-2.5 rajdhani font-weight-bold" style="font-size: 15px; letter-spacing: 0.5px;">
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
