@extends('welcome')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
    <div class="content-header">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center" style="gap:10px;">
            <h1 class="m-0 font-weight-bold text-dark" style="font-family:'Rajdhani',sans-serif; letter-spacing:1px;">PROJECTS</h1>
            <div class="btn-group shadow-sm">
                <a href="{{ route('nrdi.projects.index', ['status' => 'open', 'division' => $divisionId, 'term' => $term]) }}"
                   class="btn btn-sm {{ ($status ?? 'open') === 'open' ? 'btn-primary' : 'btn-outline-primary' }}" style="padding:4px 16px;">
                    Open
                </a>
                <a href="{{ route('nrdi.projects.index', ['status' => 'closed', 'division' => $divisionId, 'term' => $term]) }}"
                   class="btn btn-sm {{ ($status ?? 'open') === 'closed' ? 'btn-primary' : 'btn-outline-primary' }}" style="padding:4px 16px;">
                    Closed
                </a>
                <a href="{{ route('nrdi.projects.index', ['status' => 'all', 'division' => $divisionId, 'term' => $term]) }}"
                   class="btn btn-sm {{ ($status ?? 'open') === 'all' ? 'btn-primary' : 'btn-outline-primary' }}" style="padding:4px 16px;">
                    All
                </a>
            </div>
        </div>
    </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('nrdi.projects.index') }}" class="row" style="gap: 10px;">
                        <input type="hidden" name="status" value="{{ $status ?? 'open' }}">
                        <div class="col-md-4">
                            <label class="text-muted mb-1" style="font-size: 12px;">Division</label>
                            <select class="form-control form-control-sm" name="division" onchange="this.form.submit()">
                                <option value="">All Divisions</option>
                                @foreach($divisions as $d)
                                    <option value="{{ $d->unt_id }}" {{ (string) $divisionId === (string) $d->unt_id ? 'selected' : '' }}>
                                        {{ $d->unt_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="text-muted mb-1" style="font-size: 12px;">Search</label>
                            <input class="form-control form-control-sm" name="term" value="{{ $term }}" placeholder="Code or title">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-sm btn-primary btn-block" type="submit">
                                <i class="fas fa-search mr-1"></i> Search
                            </button>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <a class="btn btn-sm btn-outline-secondary btn-block" href="{{ route('nrdi.projects.index', ['status' => $status]) }}">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body rd-table-responsive p-0" style="max-height: calc(100vh - 290px);">
                    <table class="table table-hover table-striped table-sm mb-0" style="font-size: 12px;">
                        <thead class="thead-dark">
                        <tr>
                            <th style="width: 120px;">Code</th>
                            <th>Title</th>
                            <th style="width: 260px;">Division</th>
                            <th style="width: 120px;">Status</th>
                            <th style="width: 140px;" class="text-right">Budget</th>
                            <th style="width: 90px;" class="text-center">View</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($projects as $p)
                            <tr>
                                <td class="font-weight-bold text-primary" style="white-space:nowrap;">{{ $p->prj_code }}</td>
                                <td style="max-width: 520px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $p->prj_title }}">
                                    {{ $p->prj_title }}
                                </td>
                                <td style="max-width: 260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $p->unit?->unt_name }}">
                                    {{ $p->unit?->unt_name }}
                                </td>
                                <td style="white-space:nowrap;">
                                    <span class="badge {{ in_array($p->prj_status, ['Closed','Completed'], true) ? 'badge-secondary' : 'badge-success' }}">
                                        {{ $p->prj_status }}
                                    </span>
                                </td>
                                <td class="text-right" style="white-space:nowrap;">
                                    {{ number_format((float) ($p->prj_propcost ?? 0)) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('nrdi.projects.show', $p->prj_id) }}" class="btn btn-xs btn-outline-primary" style="padding:3px 10px; border-radius:10px; font-size:11px;">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted p-4">No projects found.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $projects->links() }}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
