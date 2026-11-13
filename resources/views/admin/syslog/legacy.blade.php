@extends('welcome')

@section('content')
@include('admin.syslog._styles')

<div class="content-wrapper">
    <div class="content-header pb-0">
        <div class="container-fluid">
            <h1 class="sl-title">
                <a href="{{ route('admin.syslog.home') }}" class="text-muted mr-1" style="font-size:15px;">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <i class="fas fa-archive mr-2" style="color:#92400e;"></i>Legacy Audit (2021&ndash;2026)
            </h1>
            <p class="sl-sub">
                Field-level history written by the old system into <span class="sl-mono">aud.busdata</span>.
                Shown here so the full record stays in one place.
            </p>
        </div>
    </div>

    <div class="container-fluid pt-3">
        <div class="sl-readonly mb-3" style="background:#fffbeb; border-color:#fde68a; color:#92400e;">
            <i class="fas fa-exclamation-triangle"></i>
            <span>
                These rows are <strong>imported history</strong>. They never recorded an IP address, and the actor is
                a stored name rather than a verified account &mdash; so treat them as a faithful copy of what the old
                tables say, not as chain-protected evidence.
            </span>
        </div>

        <div class="sl-card mb-4">
            <form method="GET" action="{{ route('admin.syslog.category', 'legacy') }}" class="sl-filters">
                <div class="form-row align-items-end">
                    <div class="col-md-4 col-8">
                        <label>Search</label>
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                               placeholder="username, form, field or record id">
                    </div>
                    <div class="col-md-2 col-4">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive" style="max-height:70vh;">
                <table class="sl-table">
                    <thead>
                        <tr><th>Date &amp; time</th><th>Who</th><th>Designation</th><th>Action</th>
                            <th>Form</th><th>Field</th><th>Record</th><th>Was</th><th>Became</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $r)
                            <tr>
                                <td class="sl-dtg">{{ $p->dtg($r->sev_dtg, 'd M Y') }}<br>
                                    <span class="text-muted sl-mono">{{ $p->dtg($r->sev_dtg, 'H:i:s') }}</span></td>
                                <td><span class="sl-who">{{ $r->sev_name ?: $r->sev_username }}</span>
                                    <span class="sl-desig sl-mono">{{ $r->sev_username }}</span></td>
                                <td style="font-size:11.5px;">{{ $r->sev_desig ?: '—' }}</td>
                                <td><span class="sl-chip sl-chip-{{ $p->actionClass($r->sev_action) }}">
                                        {{ $p->actionLabel($r->sev_action) }}</span></td>
                                <td class="sl-mono">{{ $r->source_form }}</td>
                                <td class="sl-mono">{{ $r->fc_column }}</td>
                                <td class="sl-mono">{{ $r->rc_pk_text }}</td>
                                <td><span class="sl-diff-old">{!! $p->value($r->fc_old, $r->fc_column) !!}</span></td>
                                <td><span class="sl-diff-new">{!! $p->value($r->fc_new, $r->fc_column) !!}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><div class="sl-empty">No legacy rows match.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-top py-2">
                <span class="sl-sub">Showing up to 300 most recent rows.</span>
            </div>
        </div>
    </div>
</div>
@endsection
