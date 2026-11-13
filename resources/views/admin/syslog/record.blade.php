@extends('welcome')

@section('content')
@include('admin.syslog._styles')

@php
    $tabs = [
        'timeline'  => ['Timeline',        'fa-stream'],
        'changes'   => ['Changes',         'fa-exchange-alt'],
        'custody'   => ['Custody',         'fa-user-clock'],
        'snapshots' => ['Snapshots',       'fa-camera'],
        'files'     => ['Files',           'fa-paperclip'],
        'people'    => ['People & Access', 'fa-users'],
    ];
    $tabUrl = fn ($t) => route('admin.syslog.record', [$category, $object, $id]) . '?tab=' . $t;
@endphp

<div class="content-wrapper">
    <div class="content-header pb-0">
        <div class="container-fluid">
            <div class="sl-card">
                <div class="sl-head d-flex justify-content-between align-items-start flex-wrap" style="gap:12px;">
                    <div style="min-width:0;">
                        <h1 class="sl-title">
                            <a href="{{ route('admin.syslog.category', $category) }}" class="text-muted mr-1" style="font-size:15px;">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            {{ $header->objref ?: ($object . ' #' . $id) }}
                        </h1>
                        <p class="sl-sub">
                            <span class="sl-chip sl-chip-secondary">{{ $object }}</span>
                            <span class="sl-chip sl-chip-light">#{{ $id }}</span>
                            @if ($header->last_stage)
                                <span class="sl-chip sl-chip-primary">{{ $header->last_stage }}</span>
                            @endif
                            @if ((int) $header->max_severity === 2)
                                <span class="sl-chip sl-chip-danger"><i class="fas fa-exclamation"></i> Critical events</span>
                            @endif
                        </p>
                        <p class="sl-sub mb-0">
                            First logged {{ $p->dtg($header->opened_at, 'd M Y, H:i') }} &middot;
                            last activity {{ $p->dtg($header->last_at, 'd M Y, H:i') }} ({{ $p->ago($header->last_at) }}) &middot;
                            <strong>{{ (int) $header->events }}</strong> events &middot;
                            <strong>{{ (int) $header->movements }}</strong> movements
                        </p>
                    </div>
                    <div class="d-flex align-items-center" style="gap:8px;">
                        <a href="{{ route('admin.syslog.print', [$category, $object, $id]) }}" target="_blank"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-print mr-1"></i> Print
                        </a>
                    </div>
                </div>

                <div class="sl-tabs">
                    @foreach ($tabs as $k => [$label, $icon])
                        <a href="{{ $tabUrl($k) }}" class="sl-tab {{ $tab === $k ? 'active' : '' }}">
                            <i class="fas {{ $icon }}"></i> {{ $label }}
                        </a>
                    @endforeach
                </div>
                <div style="height:10px; background:#fff;"></div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-3">

    {{-- ============================================================ TIMELINE --}}
    @if ($tab === 'timeline')
        <div class="sl-card p-3 mb-4">
            @if (empty($trail))
                <div class="sl-empty"><i class="fas fa-stream fa-2x mb-2 d-block" style="opacity:.3;"></i>No events.</div>
            @else
                <div class="sl-tl">
                    @foreach ($trail as $t)
                        <div class="sl-tl-item">
                            <div class="sl-tl-dot d-{{ $p->actionClass($t->action) }}"></div>
                            <div class="sl-tl-body">
                                <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:8px;">
                                    <div style="min-width:0;">
                                        <span class="sl-tl-sr">#{{ (int) $t->sr }}</span>
                                        <span class="sl-chip sl-chip-{{ $p->actionClass($t->action) }} ml-1">
                                            <i class="fas {{ $p->actionIcon($t->action) }}"></i> {{ $p->actionLabel($t->action) }}
                                        </span>
                                        @if ($t->minute_no)
                                            <span class="sl-chip sl-chip-light">Minute {{ (int) $t->minute_no }}</span>
                                        @endif
                                        @if ($t->src === 'backfill')
                                            <span class="sl-chip sl-chip-light" title="Imported from the old trail tables">
                                                <i class="fas fa-file-import"></i> imported history
                                            </span>
                                        @endif
                                        @if ($t->result !== 'ok')
                                            <span class="sl-chip sl-chip-danger">{{ $t->result }}</span>
                                        @endif
                                    </div>
                                    <div class="text-right sl-dtg">
                                        {{ $p->dtg($t->dtg, 'd M Y, H:i:s') }}
                                        @if ($t->from_ip)
                                            <span class="sl-desig sl-mono">{{ $t->from_ip }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-2" style="font-size:12.5px;">
                                    <strong>{!! $p->person($t->by_name, $t->by_rank, $t->by_desig) !!}</strong>
                                    @if ($t->by_unit)<span class="text-muted">&middot; {{ $t->by_unit }}</span>@endif
                                </div>

                                @if ($t->stage_from || $t->stage_to || $t->sent_to)
                                    <div class="mt-1" style="font-size:12px; color:#475569;">
                                        @if ($t->stage_from || $t->stage_to)
                                            {{ $t->stage_from ?: '—' }}<span class="sl-arrow">&rarr;</span>
                                            <strong>{{ $t->stage_to ?: '—' }}</strong>
                                        @endif
                                        @if ($t->sent_to)
                                            <span class="ml-2"><i class="fas fa-paper-plane mr-1" style="font-size:10px;"></i>
                                            sent to <strong>{{ $t->sent_to }}</strong></span>
                                        @endif
                                        @if ($t->sent_from)
                                            <span class="sl-desig">received from {{ $t->sent_from }}</span>
                                        @endif
                                    </div>
                                @endif

                                @if ($t->on_authority)
                                    <div class="mt-1" style="font-size:12px;">
                                        <i class="fas fa-stamp text-muted mr-1"></i>
                                        On authority of <strong>{{ $t->on_authority }}</strong>
                                    </div>
                                @endif

                                @if ($t->remarks)
                                    <div class="sl-remark">{{ $t->remarks }}</div>
                                @endif

                                @if ((int) $t->fields_changed > 0 || (int) $t->files_touched > 0)
                                    <div class="mt-2">
                                        @if ((int) $t->fields_changed > 0)
                                            <a href="{{ route('admin.syslog.event', $t->sev_id) }}"
                                               class="sl-chip sl-chip-primary" style="text-decoration:none;">
                                                <i class="fas fa-exchange-alt"></i>
                                                {{ (int) $t->fields_changed }} field(s) changed
                                            </a>
                                        @endif
                                        @if ((int) $t->files_touched > 0)
                                            <span class="sl-chip sl-chip-info">
                                                <i class="fas fa-paperclip"></i> {{ (int) $t->files_touched }} file(s)
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    {{-- ============================================================= CHANGES --}}
    @elseif ($tab === 'changes')
        <div class="sl-card mb-3">
            <div class="table-responsive" style="max-height:62vh;">
                <table class="sl-table">
                    <thead>
                        <tr>
                            <th>Date &amp; time</th><th>Who</th><th>Table</th><th>Field</th>
                            <th>Was</th><th>Became</th><th>Remarks</th><th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($changes as $c)
                            <tr>
                                <td class="sl-dtg">{{ $p->dtg($c->sev_dtg, 'd M Y') }}<br>
                                    <span class="text-muted sl-mono">{{ $p->dtg($c->sev_dtg, 'H:i:s') }}</span></td>
                                <td><span class="sl-who">{{ $c->sev_name ?: $c->sev_username }}</span>
                                    <span class="sl-desig">{{ $c->sev_desig }}</span></td>
                                <td class="sl-mono">{{ $c->fc_schema }}.{{ $c->fc_table }}
                                    <span class="sl-desig sl-mono">{{ $c->fc_pk_text }}</span></td>
                                <td><strong>{{ $p->fieldLabel($c->fc_schema, $c->fc_table, $c->fc_column, $c->fc_label) }}</strong>
                                    <span class="sl-desig sl-mono">{{ $c->fc_column }}</span></td>
                                <td><span class="sl-diff-old">{!! $c->fc_redacted ? '***' : $p->value($c->fc_old, $c->fc_column) !!}</span></td>
                                <td><span class="sl-diff-new">{!! $c->fc_redacted ? '***' : $p->value($c->fc_new, $c->fc_column) !!}</span></td>
                                <td style="font-size:11.5px; max-width:220px;">
                                    {{ \Illuminate\Support\Str::limit($c->sev_remarks ?? '', 90) ?: '—' }}
                                    @if ($c->sev_authority_ref)
                                        <span class="sl-desig"><i class="fas fa-stamp mr-1"></i>{{ $c->sev_authority_ref }}</span>
                                    @endif
                                </td>
                                <td class="sl-mono">{{ $c->sev_ip ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><div class="sl-empty">No field-level changes recorded for this record.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <h6 class="text-uppercase font-weight-bold mb-2" style="font-size:11px; letter-spacing:.6px; color:#64748b;">
            Row-level operations (inserts, deletes and full snapshots)
        </h6>
        <div class="sl-card mb-4">
            <div class="table-responsive" style="max-height:40vh;">
                <table class="sl-table">
                    <thead><tr><th>Date &amp; time</th><th>Who</th><th>Table</th><th>Row</th><th>Op</th><th>Fields</th><th>Snapshot kept</th></tr></thead>
                    <tbody>
                        @forelse ($rows as $r)
                            <tr class="{{ $r->rc_op === 'D' ? 'sl-crit' : '' }}">
                                <td class="sl-dtg">{{ $p->dtg($r->rc_dtg, 'd M Y H:i:s') }}</td>
                                <td><span class="sl-who">{{ $r->sev_username }}</span>
                                    <span class="sl-desig">{{ $r->sev_desig }}</span></td>
                                <td class="sl-mono">{{ $r->rc_schema }}.{{ $r->rc_table }}</td>
                                <td class="sl-mono">{{ $r->rc_pk_text ?: '—' }}</td>
                                <td>
                                    @php $opMap = ['I'=>['Insert','success'],'U'=>['Update','primary'],'D'=>['Delete','danger'],'T'=>['Truncate','danger']]; @endphp
                                    <span class="sl-chip sl-chip-{{ $opMap[$r->rc_op][1] ?? 'secondary' }}">
                                        {{ $opMap[$r->rc_op][0] ?? $r->rc_op }}
                                    </span>
                                </td>
                                <td style="font-size:11px;">
                                    @if ($r->rc_changed)
                                        {{ \Illuminate\Support\Str::limit(trim($r->rc_changed, '{}'), 80) }}
                                    @else
                                        <span class="sl-null">—</span>
                                    @endif
                                </td>
                                <td style="font-size:11px;">
                                    @if ($r->has_old)<span class="sl-chip sl-chip-info">before</span>@endif
                                    @if ($r->has_new)<span class="sl-chip sl-chip-success">after</span>@endif
                                    @if (! $r->has_old && ! $r->has_new)<span class="sl-null">field diffs only</span>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="sl-empty">No row operations recorded.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ============================================================= CUSTODY --}}
    @elseif ($tab === 'custody')
        <div class="sl-card mb-4">
            <div class="table-responsive">
                <table class="sl-table">
                    <thead>
                        <tr><th>#</th><th>Received from</th><th>Held by</th><th>Stage</th>
                            <th>Received at</th><th>Held for</th><th>Forwarded to</th>
                            <th>How it left</th><th>Changed anything?</th><th>Contents</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($custody as $c)
                            <tr>
                                <td><strong>{{ (int) $c->cs_seq }}</strong></td>
                                <td>{{ $c->received_from ?: '—' }}</td>
                                <td class="sl-who">{{ $c->held_by ?: '—' }}</td>
                                <td>@if ($c->cs_stage)<span class="sl-chip sl-chip-primary">{{ $c->cs_stage }}</span>@endif</td>
                                <td class="sl-dtg">{{ $p->dtg($c->received_at, 'd M Y, H:i') }}</td>
                                <td>{{ $c->held_for ? \Illuminate\Support\Str::before($c->held_for, '.') : '—' }}</td>
                                <td>{{ $c->forwarded_to ?: '—' }}</td>
                                <td>@if ($c->how_it_left)
                                        <span class="sl-chip sl-chip-{{ $p->actionClass($c->how_it_left) }}">
                                            {{ $p->actionLabel($c->how_it_left) }}</span>
                                    @else <span class="sl-null">still here</span> @endif</td>
                                <td>
                                    @if ($c->changed_anything === null)
                                        <span class="sl-null">—</span>
                                    @elseif ($c->changed_anything)
                                        <span class="sl-chip sl-chip-warning"><i class="fas fa-pen"></i> yes</span>
                                    @else
                                        <span class="sl-chip sl-chip-light"><i class="fas fa-equals"></i> no change</span>
                                    @endif
                                </td>
                                <td style="font-size:10.5px; max-width:220px;">
                                    @php $rc = json_decode($c->cs_row_counts ?? '{}', true) ?: []; @endphp
                                    @foreach ($rc as $k => $n)
                                        @if ($n > 0)<span class="sl-chip sl-chip-light">{{ $k }}: {{ $n }}</span>@endif
                                    @endforeach
                                    @if ($c->cs_partial)
                                        <span class="sl-chip sl-chip-warning" title="{{ $c->cs_partial_why }}">partial</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($c->next_cs_id)
                                        <a class="btn btn-xs btn-outline-primary" style="font-size:10px; padding:1px 6px;"
                                           href="{{ route('admin.syslog.record', [$category,$object,$id]) }}?tab=snapshots&from={{ $c->cs_id }}&to={{ $c->next_cs_id }}">
                                            What they did
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11"><div class="sl-empty">
                                <i class="fas fa-user-clock fa-2x mb-2 d-block" style="opacity:.3;"></i>
                                No custody snapshots yet. These are created when the case is forwarded,
                                returned, approved or released.
                            </div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- =========================================================== SNAPSHOTS --}}
    @elseif ($tab === 'snapshots')
        @if (! empty($diff))
            <div class="sl-card mb-3">
                <div class="sl-head py-2">
                    <strong style="font-size:13px;">
                        What changed between snapshot #{{ $diffFrom }} and #{{ $diffTo }}
                    </strong>
                </div>
                <div class="table-responsive" style="max-height:45vh;">
                    <table class="sl-table">
                        <thead><tr><th>Part</th><th>Change</th><th>Field</th><th>Was</th><th>Became</th><th>Row</th></tr></thead>
                        <tbody>
                            @forelse ($diff as $d)
                                <tr>
                                    <td><span class="sl-chip sl-chip-secondary">{{ $d->part }}</span></td>
                                    <td>
                                        @php $cm = ['added'=>'success','removed'=>'danger','modified'=>'warning']; @endphp
                                        <span class="sl-chip sl-chip-{{ $cm[$d->change] ?? 'light' }}">{{ $d->change }}</span>
                                    </td>
                                    <td><strong>{{ $d->col_name ? $p->fieldLabel(null, null, $d->col_name) : '—' }}</strong>
                                        @if ($d->col_name)<span class="sl-desig sl-mono">{{ $d->col_name }}</span>@endif</td>
                                    <td>@if ($d->old_value !== null)<span class="sl-diff-old">{!! $p->value($d->old_value, $d->col_name) !!}</span>@else <span class="sl-null">—</span>@endif</td>
                                    <td>@if ($d->new_value !== null)<span class="sl-diff-new">{!! $p->value($d->new_value, $d->col_name) !!}</span>@else <span class="sl-null">—</span>@endif</td>
                                    <td class="sl-mono" style="font-size:10.5px; max-width:260px; word-break:break-all;">{{ $d->row_key }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="sl-empty">
                                    <i class="fas fa-equals fa-2x mb-2 d-block" style="opacity:.3;"></i>
                                    Nothing changed between these two snapshots.
                                </div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if (! empty($doc))
            <div class="sl-card mb-3">
                <div class="sl-head py-2"><strong style="font-size:13px;">The case as it was &mdash; snapshot #{{ $docId }}</strong></div>
                <div class="p-3" style="max-height:50vh; overflow:auto;">
                    <pre class="sl-mono" style="font-size:11px; margin:0;">{{ json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif

        <div class="sl-card mb-4">
            <div class="table-responsive">
                <table class="sl-table">
                    <thead><tr><th>#</th><th>When</th><th>Why</th><th>Stage</th><th>With</th>
                               <th>Contents</th><th>Size</th><th>Hash</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($snapshots as $i => $s)
                            <tr>
                                <td><strong>{{ (int) $s->cs_seq }}</strong></td>
                                <td class="sl-dtg">{{ $p->dtg($s->cs_dtg, 'd M Y, H:i') }}</td>
                                <td><span class="sl-chip sl-chip-{{ $p->actionClass($s->cs_reason) }}">
                                        {{ $p->actionLabel($s->cs_reason) }}</span></td>
                                <td>{{ $s->cs_stage ?: '—' }}</td>
                                <td>{{ $s->cs_holder ?: '—' }}</td>
                                <td style="font-size:10.5px;">
                                    @php $rc = json_decode($s->cs_row_counts ?? '{}', true) ?: []; @endphp
                                    @foreach ($rc as $k => $n)
                                        @if ($n > 0)<span class="sl-chip sl-chip-light">{{ $k }}: {{ $n }}</span>@endif
                                    @endforeach
                                </td>
                                <td class="sl-mono">{{ number_format((int) $s->cs_bytes) }} B</td>
                                <td class="sl-mono" title="{{ $s->cs_doc_hash }}">
                                    {{ substr($s->cs_doc_hash, 0, 10) }}…
                                    @if ($s->cs_same_as)
                                        <span class="sl-chip sl-chip-light" title="Identical to snapshot {{ $s->cs_same_as }}">same</span>
                                    @endif
                                </td>
                                <td class="text-right" style="white-space:nowrap;">
                                    <a class="btn btn-xs btn-outline-secondary" style="font-size:10px; padding:1px 6px;"
                                       href="{{ route('admin.syslog.record', [$category,$object,$id]) }}?tab=snapshots&view={{ $s->cs_id }}">
                                        View
                                    </a>
                                    @if (isset($snapshots[$i + 1]))
                                        <a class="btn btn-xs btn-outline-primary" style="font-size:10px; padding:1px 6px;"
                                           href="{{ route('admin.syslog.record', [$category,$object,$id]) }}?tab=snapshots&from={{ $s->cs_id }}&to={{ $snapshots[$i+1]->cs_id }}">
                                            Diff next
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><div class="sl-empty">
                                <i class="fas fa-camera fa-2x mb-2 d-block" style="opacity:.3;"></i>
                                No snapshots yet for this record.
                            </div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sl-readonly mb-4">
            <i class="fas fa-info-circle"></i>
            <span>
                A snapshot shows the <strong>net state</strong> at a handover. If a value was changed and changed
                back within one custody period, the diff shows nothing &mdash; the <strong>Changes</strong> tab
                shows every individual edit.
            </span>
        </div>

    {{-- =============================================================== FILES --}}
    @elseif ($tab === 'files')
        <div class="sl-card mb-4">
            <div class="table-responsive">
                <table class="sl-table">
                    <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Original filename</th>
                               <th>Stored as</th><th>Type</th><th>Size</th><th>SHA-256</th><th>IP</th></tr></thead>
                    <tbody>
                        @forelse ($files as $f)
                            <tr class="{{ in_array($f->fe_action, ['deleted','replaced'], true) ? 'sl-warn' : '' }}">
                                <td class="sl-dtg">{{ $p->dtg($f->at, 'd M Y, H:i') }}</td>
                                <td><span class="sl-who">{{ $f->sev_name ?: $f->sev_username }}</span>
                                    <span class="sl-desig">{{ $f->sev_desig }}</span></td>
                                <td><span class="sl-chip sl-chip-{{ $f->fe_action === 'deleted' ? 'danger' : ($f->fe_action === 'downloaded' ? 'light' : 'info') }}">
                                        {{ ucfirst($f->fe_action) }}</span></td>
                                <td><strong>{{ $f->fe_orig_name ?: '—' }}</strong></td>
                                <td class="sl-mono" style="max-width:260px; word-break:break-all;">{{ $f->fe_stored_path ?: '—' }}</td>
                                <td style="font-size:11px;">{{ $f->fe_attach_type ?: ($f->fe_mime ?: '—') }}</td>
                                <td class="sl-mono">{{ $f->fe_bytes ? number_format((int) $f->fe_bytes / 1024, 1) . ' KB' : '—' }}</td>
                                <td class="sl-mono" title="{{ $f->fe_sha256 }}">
                                    {{ $f->fe_sha256 ? substr($f->fe_sha256, 0, 10) . '…' : '—' }}
                                    @if ($f->fe_prev_sha256)
                                        <span class="sl-chip sl-chip-danger" title="Replaced a different file at the same path">
                                            substituted
                                        </span>
                                    @endif
                                </td>
                                <td class="sl-mono">{{ $f->sev_ip ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><div class="sl-empty">
                                <i class="fas fa-paperclip fa-2x mb-2 d-block" style="opacity:.3;"></i>
                                No file events recorded for this record yet.
                                <div class="sl-sub mt-2">
                                    Uploads, downloads and deletions appear here once the upload paths call
                                    <span class="sl-mono">Audit::file()</span>.
                                </div>
                            </div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- ============================================================== PEOPLE --}}
    @elseif ($tab === 'people')
        <div class="sl-card mb-4">
            <div class="table-responsive">
                <table class="sl-table">
                    <thead><tr><th>Who</th><th>Designation</th><th>Unit</th><th>Events</th>
                               <th>Denied</th><th>First</th><th>Last</th><th>IP addresses</th></tr></thead>
                    <tbody>
                        @forelse ($people as $u)
                            <tr class="{{ (int) $u->denied > 0 ? 'sl-warn' : '' }}">
                                <td><span class="sl-who">{{ $u->sev_name ?: $u->sev_username }}</span>
                                    <span class="sl-desig">{{ $u->sev_username }}</span></td>
                                <td>{{ trim(($u->sev_rank ? $u->sev_rank . ' · ' : '') . ($u->sev_desig ?: '—')) }}</td>
                                <td style="font-size:11.5px;">{{ $u->sev_untname ?: '—' }}</td>
                                <td><strong>{{ (int) $u->events }}</strong></td>
                                <td>@if ((int) $u->denied > 0)
                                        <span class="sl-chip sl-chip-danger">{{ (int) $u->denied }}</span>
                                    @else — @endif</td>
                                <td class="sl-dtg">{{ $p->dtg($u->first_at, 'd M Y, H:i') }}</td>
                                <td class="sl-dtg">{{ $p->dtg($u->last_at, 'd M Y, H:i') }}</td>
                                <td class="sl-mono" style="max-width:240px;">{{ trim($u->ips ?? '', '{}') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><div class="sl-empty">No activity recorded.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    </div>
</div>
@endsection
