@extends('welcome')

@section('content')
@include('admin.syslog._styles')

<div class="content-wrapper">
    <div class="content-header pb-0">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="gap:12px;">
            <div>
                <h1 class="sl-title"><i class="fas fa-shield-alt text-primary mr-2"></i>System Logs</h1>
                <p class="sl-sub">Every change in the system &mdash; who, when, from which IP, on whose authority. Read-only.</p>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                @can('syslog.verify')
                    <a href="{{ route('admin.syslog.verify') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-fingerprint mr-1"></i> Verify Chain
                    </a>
                @endcan
                @can('syslog.view_all')
                    <a href="{{ route('admin.syslog.category', 'health') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-heartbeat mr-1"></i> Trail Health
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="container-fluid pt-3">

        @if (session('status'))
            <div class="alert alert-success py-2"><i class="fas fa-check-circle mr-2"></i>{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger py-2"><i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}</div>
        @endif

        <div class="sl-readonly mb-3">
            <i class="fas fa-lock"></i>
            <span>
                This section is <strong>read-only</strong>. The trail is append-only in the database itself &mdash;
                it cannot be edited or deleted from here or anywhere else in the application.
            </span>
        </div>

        {{-- ---------------- summary strip ---------------- --}}
        <div class="row mb-3" style="row-gap:12px;">
            @php
                $strip = [
                    ['Today',            $overview->events_today,  'fa-calendar-day',   'primary'],
                    ['Last 7 days',      $overview->events_week,   'fa-calendar-week',  'primary'],
                    ['Total recorded',   $overview->events_total,   'fa-database',       'secondary'],
                    ['Critical',         $overview->critical_30d,   'fa-exclamation',    'danger'],
                    ['Failed / denied',  $overview->failed_30d,     'fa-hand-paper',     'warning'],
                    ['Tables watched',   $overview->tables_watched, 'fa-table',          'success'],
                ];
            @endphp
            @foreach ($strip as [$label, $n, $icon, $tone])
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="sl-card p-2 px-3 h-100">
                        <div class="sl-tile-meta"><i class="fas {{ $icon }} mr-1 text-{{ $tone }}"></i>{{ $label }}</div>
                        <div class="sl-tile-n">{{ number_format((int) $n) }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ---------------- health warnings ---------------- --}}
        @php $v = $overview->verify; @endphp
        <div class="row mb-3" style="row-gap:12px;">
            <div class="col-lg-4">
                <div class="sl-card p-3 h-100">
                    <div class="sl-tile-meta mb-1">Chain verification</div>
                    @if (! $v)
                        <span class="sl-chip sl-chip-light"><i class="fas fa-minus-circle"></i> Never run</span>
                        <div class="sl-sub mt-1">Run <span class="sl-mono">php artisan audit:verify</span> or press Verify Chain.</div>
                    @elseif ($v->vrn_status === 'ok')
                        <span class="sl-chip sl-chip-success"><i class="fas fa-check-circle"></i> Intact</span>
                        <div class="sl-sub mt-1">{{ (int) $v->vrn_seals_ok }} seal(s) verified &middot; {{ $p->ago($v->vrn_dtg) }}</div>
                    @else
                        <span class="sl-chip sl-chip-danger"><i class="fas fa-exclamation-triangle"></i> BROKEN</span>
                        <div class="sl-sub mt-1">{{ \Illuminate\Support\Str::limit($v->vrn_detail, 120) }}</div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sl-card p-3 h-100">
                    <div class="sl-tile-meta mb-1">Unowned writes (7 days)</div>
                    @if ((int) $overview->unowned_week === 0)
                        <span class="sl-chip sl-chip-success"><i class="fas fa-check"></i> None</span>
                        <div class="sl-sub mt-1">Every write was attributed to a signed-in user.</div>
                    @else
                        <span class="sl-chip sl-chip-warning"><i class="fas fa-user-secret"></i>
                            {{ number_format((int) $overview->unowned_week) }}</span>
                        <div class="sl-sub mt-1">Writes with no signed-in actor &mdash; legacy app, a script, or direct SQL.</div>
                    @endif
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sl-card p-3 h-100">
                    <div class="sl-tile-meta mb-1">Coverage</div>
                    @if ((int) $overview->coverage_gaps === 0)
                        <span class="sl-chip sl-chip-success"><i class="fas fa-check"></i> Complete</span>
                        <div class="sl-sub mt-1">Every registered table has an always-on audit trigger.</div>
                    @else
                        <span class="sl-chip sl-chip-danger"><i class="fas fa-unlink"></i>
                            {{ (int) $overview->coverage_gaps }} gap(s)</span>
                        <div class="sl-sub mt-1">Tables whose changes are <strong>not</strong> being recorded.</div>
                    @endif
                    @if ((int) $overview->trail_holes > 0)
                        <div class="mt-2">
                            <span class="sl-chip sl-chip-danger"><i class="fas fa-bug"></i>
                                {{ (int) $overview->trail_holes }} trail hole(s)</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ---------------- category tiles ---------------- --}}
        <h6 class="text-uppercase font-weight-bold mb-2" style="font-size:11px; letter-spacing:.6px; color:#64748b;">
            Browse by module
        </h6>
        <div class="row" style="row-gap:12px;">
            @foreach ($categories as $c)
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ route('admin.syslog.category', $c->cat_key) }}" class="sl-tile">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="d-flex align-items-center" style="gap:4px; min-width:0;">
                                <i class="{{ $c->cat_icon }} sl-tile-icon"></i>
                                <span class="sl-tile-label text-truncate">{{ $c->cat_label }}</span>
                            </div>
                            @if ((int) $c->critical_30d > 0)
                                <span class="sl-chip sl-chip-danger">{{ (int) $c->critical_30d }}</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-end justify-content-between mt-2">
                            <div>
                                <div class="sl-tile-n">{{ number_format((int) $c->total_30d) }}</div>
                                <div class="sl-tile-meta">events, all time</div>
                            </div>
                            <div class="text-right">
                                <div class="sl-tile-meta">Today <strong>{{ (int) $c->today }}</strong></div>
                                @if ($c->first_activity)
                                    <div class="sl-tile-meta">
                                        {{ \Illuminate\Support\Carbon::parse($c->first_activity)->format('M Y') }}
                                        &ndash;
                                        {{ \Illuminate\Support\Carbon::parse($c->last_activity)->format('M Y') }}
                                    </div>
                                @else
                                    <div class="sl-tile-meta">no activity</div>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach

            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('admin.syslog.category', 'legacy') }}" class="sl-tile">
                    <div class="d-flex align-items-center" style="gap:4px;">
                        <i class="fas fa-archive sl-tile-icon" style="color:#92400e;"></i>
                        <span class="sl-tile-label">Legacy (2021&ndash;2026)</span>
                    </div>
                    <div class="d-flex align-items-end justify-content-between mt-2">
                        <div>
                            <div class="sl-tile-n">{{ number_format((int) $overview->legacy_rows) }}</div>
                            <div class="sl-tile-meta">rows from the old system</div>
                        </div>
                        <span class="sl-chip sl-chip-warning">read-only</span>
                    </div>
                </a>
            </div>
        </div>

        <p class="sl-sub mt-3 mb-4">
            {{ number_format((int) $overview->snapshots) }} case snapshots &middot;
            {{ number_format((int) $overview->file_events) }} file events &middot;
            {{ number_format((int) $overview->tables_watched) }} tables watched.
            Categories come from <span class="sl-mono">syslog.categories</span>, so a new module appears here
            without a code change.
        </p>
    </div>
</div>
@endsection
