@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&display=swap');

    .finance-hub {
        font-family: 'Inter', sans-serif;
        background: #080b0f !important;
        min-height: 100vh;
        color: #cbd5e0;
        padding-top: 15px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-cyber {
        background: rgba(18, 26, 34, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 14px;
    }

    .table-cyber {
        background: transparent;
        color: #cbd5e0;
    }
    .table-cyber th {
        background: rgba(18, 26, 34, 0.95) !important;
        border-bottom: 2px solid rgba(255, 255, 255, 0.08) !important;
        color: #67e8f9 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 12px;
        font-weight: bold;
    }
    .table-cyber td {
        border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
        vertical-align: middle;
        font-size: 13px;
    }

    .form-control-cyber {
        background: rgba(10, 15, 22, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        border-radius: 8px;
    }
    .form-control-cyber:focus {
        background: rgba(10, 15, 22, 1);
        border-color: #00BFFF;
        color: #fff;
        box-shadow: 0 0 10px rgba(0, 191, 255, 0.2);
    }
</style>

<div class="content-wrapper finance-hub px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('fin.payments.index') }}" class="text-info text-decoration-none small">&larr; Back to Commitments List</a>
            <h2 class="text-white rajdhani font-weight-bold mb-1 mt-1">Commitment #{{ $commitment->cmt_id }} Details</h2>
            <p class="text-muted small mb-0">Case Title: <strong>{{ $commitment->pcs_title }}</strong> (Doc ID: {{ $commitment->cmt_docid }})</p>
        </div>
        <div>
            @if($commitment->cmt_status === 'Awaited')
                <span class="badge badge-warning p-2 rajdhani" style="font-size: 14px;">STATUS: AWAITED</span>
            @else
                <span class="badge badge-success p-2 rajdhani" style="font-size: 14px;">STATUS: PAID</span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Left Column: Commitment Summary & Transactions History -->
        <div class="col-md-7">
            <div class="card card-cyber p-4 mb-4">
                <h5 class="text-white rajdhani font-weight-bold mb-3 border-bottom border-secondary pb-2">Commitment Overview</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <span class="text-muted small d-block">Budget Head:</span>
                        <strong class="text-info">{{ $commitment->hed_code }} - {{ $commitment->hed_name }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <span class="text-muted small d-block">Division:</span>
                        <strong class="text-white">{{ $commitment->unt_namesh }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <span class="text-muted small d-block">Committed Amount:</span>
                        <h5 class="text-danger rajdhani font-weight-bold">PKR {{ number_format($commitmentAmount, 2) }}</h5>
                    </div>
                    <div class="col-md-4 mb-3">
                        <span class="text-muted small d-block">Total Paid:</span>
                        <h5 class="text-success rajdhani font-weight-bold">PKR {{ number_format($totalPaid, 2) }}</h5>
                    </div>
                    <div class="col-md-4 mb-3">
                        <span class="text-muted small d-block">Remaining Balance:</span>
                        <h5 class="text-warning rajdhani font-weight-bold">PKR {{ number_format($remainingAmount, 2) }}</h5>
                    </div>
                </div>
            </div>

            <div class="card card-cyber p-4">
                <h5 class="text-white rajdhani font-weight-bold mb-3 border-bottom border-secondary pb-2">Payment Transaction History</h5>
                <table class="table table-cyber mb-0">
                    <thead>
                        <tr>
                            <th>Seq</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Tax</th>
                            <th>Net Disbursed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            <tr>
                                <td>#{{ $t->trn_seq }}</td>
                                <td>{{ $t->trn_date }}</td>
                                <td class="text-danger rajdhani">PKR {{ number_format(abs($t->trn_amount1), 2) }}</td>
                                <td class="text-muted rajdhani">PKR {{ number_format(abs($t->trn_tax1), 2) }}</td>
                                <td class="text-success rajdhani font-weight-bold">PKR {{ number_format(abs($t->trn_amount2), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">No disbursement transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: Record New Disbursement -->
        <div class="col-md-5">
            <div class="card card-cyber p-4">
                <h5 class="text-white rajdhani font-weight-bold mb-3 border-bottom border-secondary pb-2">Record Payment Transaction</h5>
                
                @if($commitment->cmt_status === 'Paid')
                    <div class="alert alert-info text-info bg-dark border-info">
                        This commitment has already been marked as <strong>Fully Paid</strong>. Additional transactions can still be added if required for adjustment.
                    </div>
                @endif

                <form action="{{ route('fin.payments.store_transaction', $commitment->cmt_id) }}" method="POST">
                    @csrf

                    <div class="form-group mb-3">
                        <label class="text-muted small">Disbursement Date <span class="text-danger">*</span></label>
                        <input type="date" name="trn_date" class="form-control form-control-cyber" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-muted small">Disbursement Amount (PKR) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-cyber" placeholder="e.g. 50000" value="{{ old('amount', $remainingAmount > 0 ? $remainingAmount : '') }}" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="text-muted small">Tax Amount (PKR)</label>
                        <input type="number" step="0.01" name="tax" class="form-control form-control-cyber" placeholder="0.00" value="{{ old('tax', 0) }}">
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" class="form-check-input" id="is_complete" name="is_complete" value="1" {{ $commitment->cmt_status === 'Paid' || $remainingAmount <= 0 ? 'checked' : '' }}>
                        <label class="form-check-label text-white small" for="is_complete">
                            Mark Commitment as <strong>Fully Paid</strong> (Closes Awaited status)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success btn-block rajdhani font-weight-bold py-2">
                        RECORD DISBURSEMENT TRANSACTION
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
