@extends('welcome')

@section('content')
@include('admin.syslog._styles')

@php
    $f = $filters ?? [];
    $qs = fn (array $extra = []) => http_build_query(array_filter(array_merge($f, $extra), fn ($v) => $v !== null && $v !== ''));
@endphp

<div class="content-wrapper">
    <div class="content-header pb-0">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap" style="gap:12px;">
            <div>
                <h1 class="sl-title">
                    <a href="{{ route('admin.syslog.home') }}" class="text-muted mr-1" style="font-size:15px;">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    <i class="{{ $cat->cat_icon }} text-primary mr-2"></i>{{ $cat->cat_label }}
                </h1>
                <p class="sl-sub">
                    {{ number_format((int) $result['total']) }} event(s)
                    @if (empty($f['from']) && empty($f['to']))
                        &mdash; <strong>all time</strong>
                    @else
                        between {{ $p->dtg($result['from'], 'd M Y') }} and {{ $p->dtg($result['to'], 'd M Y') }}
                    @endif
                </p>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                @can('syslog.export')
                    <a href="{{ route('admin.syslog.export', $cat->cat_key) }}?{{ $qs() }}"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-file-csv mr-1"></i> Export CSV
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="container-fluid pt-3">
        <div class="sl-card mb-4">

            {{-- ---------------- filters ---------------- --}}
            <form method="GET" action="{{ route('admin.syslog.category', $cat->cat_key) }}" class="sl-filters">
                <div class="form-row align-items-end" style="row-gap:8px;">
                    <div class="col-lg-2 col-md-3 col-6">
                        <label>From</label>
                        <input type="date" name="from" value="{{ $f['from'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label>To</label>
                        <input type="date" name="to" value="{{ $f['to'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label>User</label>
                        <input type="text" name="user" value="{{ $f['user'] ?? '' }}" class="form-control"
                               placeholder="name / username">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label>Action</label>
                        <select name="action" class="custom-select">
                            <option value="">All actions</option>
                            @foreach ($actions as $k => $label)
                                <option value="{{ $k }}" @selected(($f['action'] ?? '') === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label>Module</label>
                        <select name="object" class="custom-select">
                            <option value="">All</option>
                            @foreach ($objects as $o)
                                <option value="{{ $o }}" @selected(($f['object'] ?? '') === $o)>{{ $o }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label>Severity</label>
                        <select name="severity" class="custom-select">
                            <option value="">Any</option>
                            <option value="1" @selected(($f['severity'] ?? '') === '1')>Sensitive &amp; above</option>
                            <option value="2" @selected(($f['severity'] ?? '') === '2')>Critical only</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4 col-8">
                        <label>Search</label>
                        <input type="text" name="q" value="{{ $f['q'] ?? '' }}" class="form-control"
                               placeholder="case ref, remarks, authority, route">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label>IP</label>
                        <input type="text" name="ip" value="{{ $f['ip'] ?? '' }}" class="form-control" placeholder="192.168.">
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label>Source</label>
                        <select name="source" class="custom-select">
                            <option value="">All</option>
                            @foreach (['app'=>'Application','legacy'=>'Legacy App','console'=>'Console','db'=>'Direct DB','job'=>'Scheduled','backfill'=>'Imported History'] as $k=>$v2)
                                <option value="{{ $k }}" @selected(($f['source'] ?? '') === $k)>{{ $v2 }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-5 col-12 d-flex align-items-center" style="gap:12px;">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="failedOnly" name="failed_only"
                                   value="1" @checked(! empty($f['failed_only']))>
                            <label class="custom-control-label" style="font-size:11.5px; text-transform:none; letter-spacing:0;"
                                   for="failedOnly">Failed / denied only</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="unownedOnly" name="unowned_only"
                                   value="1" @checked(! empty($f['unowned_only']))>
                            <label class="custom-control-label" style="font-size:11.5px; text-transform:none; letter-spacing:0;"
                                   for="unownedOnly">Unowned writes</label>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-12 d-flex" style="gap:6px;">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.syslog.category', $cat->cat_key) }}"
                           class="btn btn-sm btn-outline-secondary" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </div>
                </div>
            </form>

            {{-- ---------------- the list ---------------- --}}
            <div class="table-responsive" style="max-height:72vh;">
                <table class="sl-table">
                    <thead>
                        <tr>
                            <th>Date &amp; time</th>
                            <th>Who</th>
                            <th>Unit</th>
                            <th>Action</th>
                            <th>Record</th>
                            <th>Stage / sent to</th>
                            <th>Changed</th>
                            <th>Remarks</th>
                            <th>IP</th>
                            <th>Source</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($result['rows'] as $r)
                            @include('admin.syslog._event_row', ['r' => $r, 'p' => $p, 'category' => $cat->cat_key])
                        @empty
                            <tr>
                                <td colspan="11">
                                    <div class="sl-empty">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity:.35;"></i>
                                        <div style="font-size:14px; font-weight:600; color:#64748b;">
                                            No events in this range
                                        </div>
                                        <div class="sl-sub mt-1">
                                            Filters:
                                            {{ $p->dtg($result['from'], 'd M Y') }} &ndash; {{ $p->dtg($result['to'], 'd M Y') }}
                                            @foreach (['user','action','object','q','ip','source','severity'] as $k)
                                                @if (! empty($f[$k])) &middot; {{ $k }}: <strong>{{ $f[$k] }}</strong>@endif
                                            @endforeach
                                        </div>
                                        <a href="{{ route('admin.syslog.category', $cat->cat_key) }}"
                                           class="btn btn-sm btn-outline-primary mt-3">
                                            <i class="fas fa-expand-arrows-alt mr-1"></i> Clear filters (all time)
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($result['hasMore'])
                <div class="card-footer bg-white border-top py-2 d-flex justify-content-between align-items-center">
                    <span class="sl-sub">
                        Showing {{ count($result['rows']) }} of {{ number_format((int) $result['total']) }}
                    </span>
                    <a href="{{ route('admin.syslog.category', $cat->cat_key) }}?{{ $qs(['after' => $result['next']]) }}"
                       class="btn btn-sm btn-outline-primary">
                        Next {{ \App\Services\Syslog\LogQueryService::PER_PAGE }} <i class="fas fa-angle-right ml-1"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
