@extends('welcome')

@section('content')
<div class="content-wrapper bg-dark text-white">
    <div class="content-header border-bottom border-secondary mb-4">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="h3 font-weight-bold">
                <i class="fas fa-file-invoice mr-2 text-info"></i> Purchase Case #{{ $purchase->pcs_id }}
            </h1>
            <div>
                <span class="badge badge-info px-3 py-2">Role: {{ strtoupper($area) }} Verification</span>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Main Info -->
                <div class="col-md-8">
                    <div class="card bg-secondary border-0 shadow-sm mb-4">
                        <div class="card-header bg-navy">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> Case Information</h3>
                        </div>
                        <div class="card-body">
                            <h2 class="text-warning mb-3">{{ $purchase->pcs_title }}</h2>
                            <p class="text-white-50">{{ $purchase->pcs_minute }}</p>
                            <hr class="border-secondary">
                            
                            <h5>Items Requested:</h5>
                            <div class="table-responsive">
                                <table class="table table-sm text-white">
                                    <thead>
                                        <tr><th>#</th><th>Description</th><th>Qty</th><th>Estimated Price</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchase->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->pci_desc }}</td>
                                            <td>{{ $item->pci_qty }} {{ $item->pci_unit }}</td>
                                            <td>{{ number_format($item->pci_price, 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @include('approvals._decision_trail')
                </div>

                <!-- Financial Picture & Action -->
                <div class="col-md-4">
                    <!-- Live Financial Picture -->
                    <div class="card bg-navy border-0 shadow-lg mb-4">
                        <div class="card-header">
                            <h3 class="card-title text-warning"><i class="fas fa-chart-pie mr-2"></i> Live Financial Picture</h3>
                        </div>
                        <div class="card-body text-center">
                            @if($budgetHead)
                            <h4 class="text-white">{{ $budgetHead->hed_name }}</h4>
                            <div class="my-4">
                                <span class="d-block text-muted">Current Available Balance</span>
                                <h2 class="text-success font-weight-bold">{{ number_format($budgetHead->hed_balance ?? 0, 0) }} PKR</h2>
                            </div>
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: 75%"></div>
                            </div>
                            <small class="text-white-50">Head Code: {{ $budgetHead->hed_code }}</small>
                            @else
                            <p class="text-danger">Budget data unavailable for this head.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Action Box -->
                    <div class="card bg-dark border border-secondary">
                        <div class="card-body">
                            <form action="{{ route('approvals.action', $purchase->pcs_id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label class="text-warning">Verification Remarks</label>
                                    <textarea name="remarks" class="form-control bg-secondary text-white border-0" rows="4" placeholder="Enter your comments here..." required></textarea>
                                </div>
                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" name="action" value="forward" class="btn btn-success btn-block font-weight-bold">
                                        <i class="fas fa-arrow-right mr-1"></i> Forward to Next Step
                                    </button>
                                    <button type="submit" name="action" value="return" class="btn btn-outline-warning btn-block btn-sm mt-2">
                                        <i class="fas fa-undo mr-1"></i> Return to Initiator
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.bg-navy { background-color: #001f3f !important; }
</style>
@endsection
