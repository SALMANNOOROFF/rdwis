@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h1 class="m-0 font-weight-bold text-dark">Contract Cases</h1>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <div class="btn-group">
                    <a href="{{ route('nrdi.contract_cases.index', ['status' => 'open']) }}"
                       class="btn btn-sm {{ ($status ?? 'open') === 'open' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Open ({{ $openCount ?? 0 }})
                    </a>
                    <a href="{{ route('nrdi.contract_cases.index', ['status' => 'closed']) }}"
                       class="btn btn-sm {{ ($status ?? 'open') === 'closed' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Closed ({{ $closedCount ?? 0 }})
                    </a>
                </div>
                <a href="{{ route('nrdi.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-chart-line mr-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body table-responsive p-0" style="max-height: calc(100vh - 220px);">
                    <table class="table table-hover table-striped table-sm" style="font-size:11px; line-height:1.15;">
                        <thead class="thead-dark">
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th style="width: 90px;">Date</th>
                            <th style="width: 110px;">Type</th>
                            <th>Employee</th>
                            <th>From Unit</th>
                            <th>To Unit</th>
                            <th>Job / Grade</th>
                            <th style="width: 90px;" class="text-right">Salary</th>
                            <th style="width: 130px;">Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($cases as $c)
                            <tr>
                                <td><strong>{{ $c->ctc_id }}</strong></td>
                                <td style="white-space:nowrap;">{{ $c->ctc_date }}</td>
                                <td style="white-space:nowrap;">{{ $c->ctc_type }}</td>
                                <td style="white-space:nowrap; max-width: 260px; overflow:hidden; text-overflow:ellipsis;"
                                    title="{{ trim(($c->ctc_emp_id ?? '') . ' — ' . ($c->ctc_empnamecomp ?? '')) }}">
                                    <span class="text-muted">{{ $c->ctc_emp_id }}</span> — {{ $c->ctc_empnamecomp }}
                                </td>
                                <td style="white-space:nowrap; max-width: 220px; overflow:hidden; text-overflow:ellipsis;" title="{{ $c->unit_name }}">
                                    {{ $c->unit_name }}
                                </td>
                                <td style="white-space:nowrap; max-width: 220px; overflow:hidden; text-overflow:ellipsis;" title="{{ $c->new_unit_name }}">
                                    {{ $c->new_unit_name }}
                                </td>
                                <td style="white-space:nowrap; max-width: 260px; overflow:hidden; text-overflow:ellipsis;"
                                    title="{{ trim(($c->ctc_newjobtitle ?? '') . ' — ' . ($c->ctc_newgrade ?? '')) }}">
                                    {{ $c->ctc_newjobtitle }} <span class="text-muted">({{ $c->ctc_newgrade }})</span>
                                </td>
                                <td class="text-right" style="white-space:nowrap;">{{ is_null($c->ctc_newsalary) ? '' : number_format((float) $c->ctc_newsalary) }}</td>
                                <td style="white-space:nowrap;">
                                    @if($c->ctc_closedtg)
                                        <span class="badge badge-danger">Closed</span>
                                    @else
                                        <span class="badge badge-success">Open</span>
                                    @endif
                                    <span class="text-muted">—</span> {{ $c->ctc_status }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted p-4">No cases found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $cases->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
