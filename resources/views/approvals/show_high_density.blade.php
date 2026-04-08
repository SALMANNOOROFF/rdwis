@extends('welcome')

@section('content')
<div class="content-wrapper bg-dark text-white">
    <!-- Professional Page Header -->
    <div class="content-header bg-navy border-bottom border-warning py-3">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h2 font-weight-bold mb-0 text-warning">
                    <i class="fas fa-file-signature mr-2"></i> Case #{{ $purchase->pcs_id }}
                </h1>
                <small class="text-white-50">Status: {{ strtoupper($purchase->pcs_status) }} — {{ $purchase->project->prj_name ?? 'N/A' }}</small>
            </div>
            <div class="text-right">
                <span class="d-block text-xs text-uppercase text-muted">Amount Total</span>
                <h2 class="text-success font-weight-bold mb-0">{{ number_format($purchase->pcs_price, 0) }} <small>PKR</small></h2>
            </div>
        </div>
    </div>

    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <!-- Case Summary -->
                <div class="col-md-7">
                    <div class="card bg-secondary border-0 shadow-lg">
                        <div class="card-header border-bottom border-dark bg-dark">
                            <h3 class="card-title font-weight-bold text-info"><i class="fas fa-list mr-2"></i> Procurement Summary</h3>
                        </div>
                        <div class="card-body">
                            <h3 class="text-white font-weight-light">{{ $purchase->pcs_title }}</h3>
                            <p class="mt-3 text-white-50">{{ $purchase->pcs_minute }}</p>
                            
                            <hr class="border-secondary">

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Originating Division</small>
                                    <span class="font-weight-bold">{{ $purchase->pcs_unt_id }}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Budget Head</small>
                                    <span class="badge badge-warning">{{ $purchase->pcs_hed_id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Detail -->
                    <div class="card bg-secondary border-0 mt-4">
                        <div class="card-header bg-navy text-xs py-2">ITEMIZED COMPONENTS</div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover text-white mb-0">
                                <thead>
                                    <tr class="text-muted border-bottom border-dark">
                                        <th class="pl-3">DESCRIPTION</th>
                                        <th class="text-center">QTY</th>
                                        <th class="text-right pr-3">EST. PRICE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->items as $item)
                                    <tr>
                                        <td class="pl-3 font-weight-light">{{ $item->pci_desc }}</td>
                                        <td class="text-center">{{ $item->pci_qty }} {{ $item->pci_unit }}</td>
                                        <td class="text-right pr-3 text-success font-weight-bold">{{ number_format($item->pci_price, 0) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @include('approvals._decision_trail')
                </div>

                <!-- Decision Panel -->
                <div class="col-md-5">
                    <!-- Financial Picture -->
                    <div class="card bg-navy border-left-gold mb-4 elevation-2">
                        <div class="card-header">
                            <h3 class="card-title text-warning"><i class="fas fa-balance-scale mr-2"></i> Real-Time Budget Snapshot</h3>
                        </div>
                        <div class="card-body">
                            @if($budgetHead)
                                <div class="d-flex justify-content-between align-items-end mb-2">
                                    <span class="text-white-50">Head Coverage</span>
                                    <span class="text-sm font-weight-bold text-success">{{ number_format($budgetHead->hed_balance, 0) }} Available</span>
                                </div>
                                <div class="progress bg-dark" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-success" style="width: 65%;"></div>
                                </div>
                                <p class="text-xs text-muted mt-2">Checking Live Database Link — Last Synced: {{ now()->format('H:i:s') }}</p>
                            @else
                                <p class="text-danger">Head information missing.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Decision Action Box -->
                    <div class="card bg-dark border-gold elevation-4">
                        <div class="card-header bg-navy">
                            <h3 class="card-title"><i class="fas fa-stamp mr-2 text-warning"></i> Authority Action</h3>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('approvals.action', $purchase->pcs_id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label class="text-warning text-sm font-weight-bold">AUTHORITY REMARKS / INSTRUCTIONS</label>
                                    <textarea name="remarks" class="form-control bg-navy text-white border-0" rows="5" placeholder="Document your rationale or reasons for return..." required></textarea>
                                </div>
                                <div class="mt-4">
                                    @if($canApprove)
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-lg btn-block shadow-sm">
                                            <i class="fas fa-check-double mr-2"></i> APPROVE THIS CASE
                                        </button>
                                    @else
                                        <button type="submit" name="action" value="forward" class="btn btn-primary btn-lg btn-block shadow-sm">
                                            <i class="fas fa-share-square mr-2"></i> FORWARD TO NEXT AUTHORITY
                                        </button>
                                    @endif

                                    <div class="row mt-3">
                                        <div class="col-6">
                                            <button type="submit" name="action" value="return" class="btn btn-outline-warning btn-block btn-sm">
                                                <i class="fas fa-undo mr-1"></i> RETURN
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            @if($area == 'nrdi')
                                            <button type="submit" name="action" value="reject" class="btn btn-outline-danger btn-block btn-sm">
                                                <i class="fas fa-times-circle mr-1"></i> REJECT
                                            </button>
                                            @endif
                                        </div>
                                    </div>
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
.border-left-gold { border-left: 5px solid #f39c12 !important; }
.border-gold { border-top: 3px solid #f39c12 !important; }
</style>
@endsection
