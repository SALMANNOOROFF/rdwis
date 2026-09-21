@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
            <h1 class="m-0 font-weight-bold text-dark" style="font-family: 'Rajdhani', sans-serif;">
                <i class="fas fa-history mr-2 text-primary"></i>System Admin — Data Reversal Requests
            </h1>
            <div class="btn-group shadow-sm">
                @if($canViewDraft)
                    <a href="{{ route('admin.reversals.draft') }}"
                       class="btn btn-sm {{ ($tab ?? 'open') === 'draft' ? 'btn-primary font-weight-bold' : 'btn-outline-primary bg-white' }}">
                        <i class="fas fa-file-alt mr-1"></i> Draft ({{ $reversalsDraftCount ?? 0 }})
                    </a>
                @endif
                <a href="{{ route('admin.reversals.open') }}"
                   class="btn btn-sm {{ ($tab ?? 'open') === 'open' ? 'btn-primary font-weight-bold' : 'btn-outline-primary bg-white' }}">
                    <i class="fas fa-folder-open mr-1"></i> Open ({{ $reversalsOpenCount ?? 0 }})
                </a>
                <a href="{{ route('admin.reversals.closed') }}"
                   class="btn btn-sm {{ ($tab ?? 'open') === 'closed' ? 'btn-primary font-weight-bold' : 'btn-outline-primary bg-white' }}">
                    <i class="fas fa-check-circle mr-1"></i> Closed ({{ $reversalsClosedCount ?? 0 }})
                </a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Action failed:</strong>
                    <ul class="mb-0 mt-1 pl-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-info"><i class="fas fa-clipboard-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Current Tab Total</span>
                            <span class="info-box-number">
                                @if(($tab ?? 'open') === 'draft')
                                    {{ $reversalsDraftCount ?? 0 }}
                                @elseif(($tab ?? 'open') === 'closed')
                                    {{ $reversalsClosedCount ?? 0 }}
                                @else
                                    {{ $reversalsOpenCount ?? 0 }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-warning"><i class="fas fa-spinner"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active / In Process</span>
                            <span class="info-box-number">{{ $reversalsOpenCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-success"><i class="fas fa-check-double"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fulfilled</span>
                            <span class="info-box-number">{{ $reversalsFulfilledCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-danger"><i class="fas fa-ban"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cancelled</span>
                            <span class="info-box-number">{{ $reversalsCancelledCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                    <h3 class="card-title font-weight-bold mb-0">
                        @if(($tab ?? 'open') === 'draft')
                            <i class="fas fa-file-alt mr-1 text-secondary"></i> Draft Reversal Cases
                        @elseif(($tab ?? 'open') === 'closed')
                            <i class="fas fa-archive mr-1 text-muted"></i> Closed Reversal Cases
                        @else
                            <i class="fas fa-inbox mr-1 text-primary"></i> Open Reversal Cases (In Process / Under Revision)
                        @endif
                    </h3>
                    <span class="text-muted small">
                        Showing page {{ $reversals->currentPage() }} of {{ $reversals->lastPage() }} ({{ $reversals->total() }} total cases)
                    </span>
                </div>
                <div class="card-body table-responsive p-0" style="min-height: 320px;">
                    <table class="table table-hover table-striped table-sm mb-0 text-nowrap">
                        <thead class="thead-dark">
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Type</th>
                            <th>Object</th>
                            <th>Object ID</th>
                            <th>Status</th>
                            <th>Target Unit</th>
                            <th>Initiator Unit</th>
                            <th>Date</th>
                            <th>Released</th>
                            <th>Closed</th>
                            <th>Reason</th>
                            <th class="text-center" style="width: 90px;">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($reversals as $rev)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.reversals.show', $rev->rev_id) }}" class="font-weight-bold text-primary">
                                        #{{ $rev->rev_id }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $typeVal = $rev->rev_type instanceof \App\Enums\RevType ? $rev->rev_type->value : (int) $rev->rev_type;
                                    @endphp
                                    @if($typeVal === 1)
                                        <span class="badge badge-info" title="Full Cascade">Type 1 (Cascade Del)</span>
                                    @elseif($typeVal === 2)
                                        <span class="badge badge-secondary" title="Field Level">Type 2 (Field Diff)</span>
                                    @elseif($typeVal === 3)
                                        <span class="badge badge-warning" title="Linked Cascade">Type 3 (Cascade Reopen)</span>
                                    @else
                                        <span class="badge badge-light">Type {{ $typeVal }}</span>
                                    @endif
                                </td>
                                <td><span class="font-weight-bold">{{ $rev->rev_obj }}</span></td>
                                <td><code>{{ $rev->rev_objid }}</code></td>
                                <td>
                                    @if($rev->rev_status === 'Draft')
                                        <span class="badge badge-secondary"><i class="fas fa-pencil-alt mr-1"></i>Draft</span>
                                    @elseif($rev->rev_status === 'In Process')
                                        <span class="badge badge-primary"><i class="fas fa-cog fa-spin mr-1"></i>In Process</span>
                                    @elseif($rev->rev_status === 'Under Revision')
                                        <span class="badge badge-warning"><i class="fas fa-undo mr-1"></i>Under Revision</span>
                                    @elseif($rev->rev_status === 'Fulfilled')
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Fulfilled</span>
                                    @elseif($rev->rev_status === 'Cancelled')
                                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Cancelled</span>
                                    @else
                                        <span class="badge badge-light">{{ $rev->rev_status }}</span>
                                    @endif
                                </td>
                                <td>{{ $rev->unit->unt_namesh ?? $rev->rev_unt_id }}</td>
                                <td>{{ $rev->initiatingUnit->unt_namesh ?? ($rev->rev_intunt_id ?? '—') }}</td>
                                <td>{{ $rev->rev_date ? \Carbon\Carbon::parse($rev->rev_date)->format('d-M-Y') : '—' }}</td>
                                <td>{{ $rev->rev_releasedtg ? \Carbon\Carbon::parse($rev->rev_releasedtg)->format('d-M-Y H:i') : '—' }}</td>
                                <td>{{ $rev->rev_closedtg ? \Carbon\Carbon::parse($rev->rev_closedtg)->format('d-M-Y H:i') : '—' }}</td>
                                <td style="max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $rev->rev_reason }}">
                                    {{ Str::limit($rev->rev_reason ?? '—', 45) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.reversals.show', $rev->rev_id) }}" class="btn btn-xs btn-outline-primary rounded-pill px-2">
                                        <i class="fas fa-eye mr-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3 text-secondary d-block"></i>
                                    <strong>No data reversal cases found in this tab.</strong>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($reversals->hasPages())
                    <div class="card-footer py-2 bg-light d-flex justify-content-between align-items-center">
                        <div>
                            {{ $reversals->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
