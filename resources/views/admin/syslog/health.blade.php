@extends('welcome')

@section('content')
@include('admin.syslog._styles')

<div class="content-wrapper">
    <div class="content-header pb-0">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="gap:12px;">
            <div>
                <h1 class="sl-title">
                    <a href="{{ route('admin.syslog.home') }}" class="text-muted mr-1" style="font-size:15px;">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <i class="fas fa-heartbeat text-primary mr-2"></i>Trail Health
                </h1>
                <p class="sl-sub">The audit of the audit &mdash; coverage, protection, seals and schema changes.</p>
            </div>
            @can('syslog.verify')
                <a href="{{ route('admin.syslog.verify') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-fingerprint mr-1"></i> Verify Chain Now
                </a>
            @endcan
        </div>
    </div>

    <div class="container-fluid pt-3">
        @if (session('status'))
            <div class="alert alert-success py-2"><i class="fas fa-check-circle mr-2"></i>{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger py-2"><i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}</div>
        @endif

        {{-- ---- trail holes: an alert, not a log line ---- --}}
        @if (count($health['holes']))
            <div class="sl-card mb-3" style="border-color:#fecaca !important;">
                <div class="sl-head py-2" style="background:#fef2f2; border-bottom-color:#fecaca;">
                    <strong style="font-size:13px; color:#991b1b;">
                        <i class="fas fa-bug mr-1"></i>
                        Trail holes ({{ count($health['holes']) }}) &mdash; writes that happened but were not recorded
                    </strong>
                </div>
                <div class="table-responsive" style="max-height:30vh;">
                    <table class="sl-table">
                        <thead><tr><th>When</th><th>Table</th><th>Op</th><th>SQL state</th><th>Message</th><th>DB user</th><th>App user</th></tr></thead>
                        <tbody>
                            @foreach ($health['holes'] as $h)
                                <tr class="sl-crit">
                                    <td class="sl-dtg">{{ $p->dtg($h->tf_dtg) }}</td>
                                    <td class="sl-mono">{{ $h->tf_schema }}.{{ $h->tf_table }}</td>
                                    <td>{{ $h->tf_op }}</td>
                                    <td class="sl-mono">{{ $h->tf_sqlstate }}</td>
                                    <td style="font-size:11px;">{{ \Illuminate\Support\Str::limit($h->tf_message, 160) }}</td>
                                    <td class="sl-mono">{{ $h->tf_db_user }}</td>
                                    <td class="sl-mono">{{ $h->tf_app_user ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="row" style="row-gap:16px;">
            {{-- ---- coverage gaps ---- --}}
            <div class="col-lg-6">
                <div class="sl-card h-100">
                    <div class="sl-head py-2">
                        <strong style="font-size:13px;">Coverage gaps ({{ count($health['gaps']) }})</strong>
                        <span class="sl-sub d-block">Business tables whose changes are not being recorded.</span>
                    </div>
                    <div class="table-responsive" style="max-height:38vh;">
                        <table class="sl-table">
                            <thead><tr><th>Table</th><th>Registered</th><th>Enabled</th><th>Trigger</th></tr></thead>
                            <tbody>
                                @forelse ($health['gaps'] as $g)
                                    <tr class="sl-warn">
                                        <td class="sl-mono">{{ $g->table_schema }}.{{ $g->table_name }}</td>
                                        <td>{!! $g->registered ? '<span class="sl-chip sl-chip-success">yes</span>' : '<span class="sl-chip sl-chip-danger">no</span>' !!}</td>
                                        <td>{!! $g->enabled ? '<span class="sl-chip sl-chip-success">yes</span>' : '<span class="sl-chip sl-chip-secondary">no</span>' !!}</td>
                                        <td>{!! $g->trigger_always_on ? '<span class="sl-chip sl-chip-success">always on</span>' : '<span class="sl-chip sl-chip-danger">missing</span>' !!}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4"><div class="sl-empty">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block" style="opacity:.4; color:#16a34a;"></i>
                                        Complete. Every business table has an always-on audit trigger.
                                    </div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ---- composition drift ---- --}}
            <div class="col-lg-6">
                <div class="sl-card h-100">
                    <div class="sl-head py-2">
                        <strong style="font-size:13px;">Snapshot composition drift ({{ count($health['drift']) }})</strong>
                        <span class="sl-sub d-block">Case child tables missing from <span class="sl-mono">syslog.case_composition</span>.</span>
                    </div>
                    <div class="table-responsive" style="max-height:38vh;">
                        <table class="sl-table">
                            <thead><tr><th>Child table</th><th>Hangs off</th></tr></thead>
                            <tbody>
                                @forelse ($health['drift'] as $d)
                                    <tr class="sl-warn">
                                        <td class="sl-mono">{{ $d->child_table }}</td>
                                        <td class="sl-mono">{{ $d->case_table }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2"><div class="sl-empty">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block" style="opacity:.4; color:#16a34a;"></i>
                                        No drift. Snapshots include every child table.
                                    </div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ---- verification runs ---- --}}
        <div class="sl-card mt-3">
            <div class="sl-head py-2"><strong style="font-size:13px;">Verification runs</strong></div>
            <div class="table-responsive">
                <table class="sl-table">
                    <thead><tr><th>When</th><th>Kind</th><th>Status</th><th>Seals checked</th><th>First bad</th><th>Detail</th><th>By</th></tr></thead>
                    <tbody>
                        @forelse ($health['verifies'] as $v)
                            <tr class="{{ $v->vrn_status === 'ok' ? '' : 'sl-crit' }}">
                                <td class="sl-dtg">{{ $p->dtg($v->vrn_dtg) }}</td>
                                <td>{{ $v->vrn_kind }}</td>
                                <td>{!! $v->vrn_status === 'ok'
                                    ? '<span class="sl-chip sl-chip-success"><i class="fas fa-check"></i> intact</span>'
                                    : '<span class="sl-chip sl-chip-danger"><i class="fas fa-exclamation-triangle"></i> broken</span>' !!}</td>
                                <td>{{ (int) $v->vrn_seals_ok }}</td>
                                <td>{{ $v->vrn_first_bad ?: '—' }}</td>
                                <td style="font-size:11px; max-width:420px;">{{ $v->vrn_detail ?: '—' }}</td>
                                <td class="sl-mono">{{ $v->vrn_by }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="sl-empty">
                                Never verified. Run <span class="sl-mono">php artisan audit:verify</span>
                                or press Verify Chain Now.
                            </div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ---- seals ---- --}}
        <div class="sl-card mt-3">
            <div class="sl-head py-2">
                <strong style="font-size:13px;">Seals</strong>
                <span class="sl-sub d-block">
                    Each seal chains to the previous one. Anything inserted, edited or deleted inside a sealed
                    range breaks the chain.
                </span>
            </div>
            <div class="table-responsive" style="max-height:34vh;">
                <table class="sl-table">
                    <thead><tr><th>#</th><th>Sealed at</th><th>Range</th><th>Events</th><th>Row changes</th><th>Field changes</th><th>Exported off-box</th></tr></thead>
                    <tbody>
                        @forelse ($health['seals'] as $s)
                            <tr>
                                <td><strong>{{ (int) $s->sel_id }}</strong></td>
                                <td class="sl-dtg">{{ $p->dtg($s->sel_dtg) }}</td>
                                <td style="font-size:11px;">{{ $p->dtg($s->sel_from_dtg, 'd M H:i') }}
                                    <span class="sl-arrow">&rarr;</span>{{ $p->dtg($s->sel_to_dtg, 'd M H:i') }}</td>
                                <td>{{ number_format((int) $s->sel_event_count) }}</td>
                                <td>{{ number_format((int) $s->sel_rc_count) }}</td>
                                <td>{{ number_format((int) $s->sel_fc_count) }}</td>
                                <td>{!! $s->sel_exported
                                    ? '<span class="sl-chip sl-chip-success">yes</span>'
                                    : '<span class="sl-chip sl-chip-warning">not yet</span>' !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="sl-empty">
                                No seals yet. Run <span class="sl-mono">php artisan audit:seal</span> (hourly in production).
                            </div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ---- DDL events ---- --}}
        <div class="sl-card mt-3 mb-4">
            <div class="sl-head py-2">
                <strong style="font-size:13px;">Schema &amp; trigger changes</strong>
                <span class="sl-sub d-block">
                    This is what catches someone dropping or disabling an audit trigger.
                </span>
            </div>
            <div class="table-responsive" style="max-height:40vh;">
                <table class="sl-table">
                    <thead><tr><th>When</th><th>Command</th><th>Object</th><th>DB user</th><th>App user</th><th>Client IP</th></tr></thead>
                    <tbody>
                        @forelse ($health['ddl'] as $d)
                            @php
                                $risky = str_contains(strtolower($d->dde_command ?? ''), 'disable trigger')
                                      || str_contains(strtolower($d->dde_tag ?? ''), 'drop');
                            @endphp
                            <tr class="{{ $risky ? 'sl-crit' : '' }}">
                                <td class="sl-dtg">{{ $p->dtg($d->dde_dtg) }}</td>
                                <td><span class="sl-chip sl-chip-{{ $risky ? 'danger' : 'light' }}">{{ $d->dde_tag }}</span></td>
                                <td class="sl-mono" style="max-width:300px; word-break:break-all;">{{ $d->dde_objid }}</td>
                                <td class="sl-mono">{{ $d->dde_db_user }}</td>
                                <td class="sl-mono">{{ $d->dde_app_user ?: '—' }}</td>
                                <td class="sl-mono">{{ $d->dde_client ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="sl-empty">No schema changes recorded.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
