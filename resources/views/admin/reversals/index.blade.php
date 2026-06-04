@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0 font-weight-bold text-dark">System Admin — Data Reversal Requests</h1>
            <div class="btn-group">
                <a href="{{ route('admin.reversals.index', ['status' => 'open']) }}"
                   class="btn btn-sm {{ ($status ?? 'open') === 'open' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Open ({{ $reversalsOpenCount ?? 0 }})
                </a>
                <a href="{{ route('admin.reversals.index', ['status' => 'closed']) }}"
                   class="btn btn-sm {{ ($status ?? 'open') === 'closed' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Closed ({{ $reversalsClosedCount ?? 0 }})
                </a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Fulfilled</span>
                            <span class="info-box-number">{{ $reversalsFulfilledCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-ban"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cancelled</span>
                            <span class="info-box-number">{{ $reversalsCancelledCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ ($status ?? 'open') === 'closed' ? 'Closed Requests' : 'Open Requests' }} (aud.revs)
                    </h3>
                </div>
                <div class="card-body table-responsive p-0" style="max-height: 650px;">
                    <table class="table table-hover table-striped table-sm">
                        <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Object</th>
                            <th>Object ID</th>
                            <th>Status</th>
                            <th>Unit</th>
                            <th>Date</th>
                            <th>Released</th>
                            <th>Closed</th>
                            <th>Reason</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($reversals as $rev)
                            <tr>
                                <td><strong>{{ $rev->rev_id }}</strong></td>
                                <td>{{ $rev->rev_obj }}</td>
                                <td>{{ $rev->rev_objid }}</td>
                                <td>
                                    <span class="badge badge-{{ $rev->rev_status === 'Fulfilled' ? 'success' : ($rev->rev_status === 'Cancelled' ? 'danger' : 'warning') }}">
                                        {{ $rev->rev_status }}
                                    </span>
                                </td>
                                <td>{{ $rev->rev_unt_id }}</td>
                                <td>{{ $rev->rev_date }}</td>
                                <td>{{ $rev->rev_releasedtg }}</td>
                                <td>{{ $rev->rev_closedtg }}</td>
                                <td style="min-width: 380px;">{{ $rev->rev_reason }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No requests found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $reversals->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

