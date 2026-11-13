@extends('welcome')

@section('content')
@include('admin.syslog._styles')

<div class="content-wrapper">
    <div class="content-header pb-0">
        <div class="container-fluid">
            <h1 class="sl-title">
                <a href="javascript:history.back()" class="text-muted mr-1" style="font-size:15px;">
                    <i class="fas fa-arrow-left"></i>
                </a>
                Event #{{ (int) $event->sev_id }}
                <span class="sl-chip sl-chip-{{ $p->actionClass($event->sev_action) }} ml-1">
                    <i class="fas {{ $p->actionIcon($event->sev_action) }}"></i> {{ $p->actionLabel($event->sev_action) }}
                </span>
            </h1>
            <p class="sl-sub">{{ $p->dtg($event->sev_dtg) }} &middot; {{ $p->ago($event->sev_dtg) }}</p>
        </div>
    </div>

    <div class="container-fluid pt-3">
        <div class="row" style="row-gap:16px;">
            <div class="col-lg-6">
                <div class="sl-card">
                    <div class="sl-head py-2"><strong style="font-size:13px;">Who &amp; on what authority</strong></div>
                    <table class="sl-kv">
                        <tr><td>Username</td><td class="sl-mono">{{ $event->sev_username }}</td></tr>
                        <tr><td>Name</td><td>{{ $event->sev_name ?: '—' }}</td></tr>
                        <tr><td>Rank</td><td>{{ $event->sev_rank ?: '—' }}</td></tr>
                        <tr><td>Designation (at the time)</td><td><strong>{{ $event->sev_desig ?: '—' }}</strong></td></tr>
                        <tr><td>Role</td><td>{{ $event->sev_role_slug ?: '—' }}</td></tr>
                        <tr><td>Authority type</td><td>{{ $event->sev_auth ?: '—' }}</td></tr>
                        <tr><td>Unit</td><td>{{ $event->sev_untname ?: '—' }} ({{ $event->sev_untarea ?: '—' }})</td></tr>
                        <tr><td>Authority reference</td><td><strong>{{ $event->sev_authority_ref ?: '—' }}</strong></td></tr>
                        <tr><td>Account id</td><td class="sl-mono">{{ $event->sev_acc_id ?: '— unowned write —' }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="sl-card">
                    <div class="sl-head py-2"><strong style="font-size:13px;">Where from &amp; what</strong></div>
                    <table class="sl-kv">
                        <tr><td>IP address</td><td class="sl-mono">{{ $event->sev_ip ?: '—' }}</td></tr>
                        <tr><td>Route</td><td class="sl-mono">{{ $event->sev_route ?: '—' }}</td></tr>
                        <tr><td>Method</td><td class="sl-mono">{{ $event->sev_method ?: '—' }}</td></tr>
                        <tr><td>Source</td><td><span class="sl-chip sl-chip-{{ $p->sourceClass($event->sev_source) }}">{{ $p->sourceLabel($event->sev_source) }}</span></td></tr>
                        <tr><td>Result</td><td>{{ $event->sev_result }}</td></tr>
                        <tr><td>Severity</td><td><span class="sl-chip sl-chip-{{ $p->severityClass((int) $event->sev_severity) }}">{{ $p->severityLabel((int) $event->sev_severity) }}</span></td></tr>
                        <tr><td>Module / record</td><td>{{ $event->sev_object ?: '—' }} {{ $event->sev_objid ? '#'.$event->sev_objid : '' }}</td></tr>
                        <tr><td>Reference</td><td>{{ $event->sev_objref ?: '—' }}</td></tr>
                        <tr><td>Request id</td><td class="sl-mono">{{ $event->sev_request_id ?: '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        @if ($event->sev_stage_from || $event->sev_stage_to || $event->sev_to_holder || $event->sev_remarks)
            <div class="sl-card mt-3">
                <div class="sl-head py-2"><strong style="font-size:13px;">Movement &amp; remarks</strong></div>
                <table class="sl-kv">
                    @if ($event->sev_stage_from || $event->sev_stage_to)
                        <tr><td>Stage</td><td>{{ $event->sev_stage_from ?: '—' }}
                            <span class="sl-arrow">&rarr;</span><strong>{{ $event->sev_stage_to ?: '—' }}</strong></td></tr>
                    @endif
                    @if ($event->sev_from_holder || $event->sev_to_holder)
                        <tr><td>Received from</td><td>{{ $event->sev_from_holder ?: '—' }}</td></tr>
                        <tr><td>Sent to</td><td><strong>{{ $event->sev_to_holder ?: '—' }}</strong></td></tr>
                    @endif
                    @if ($event->sev_minute_no)
                        <tr><td>Minute no</td><td>{{ (int) $event->sev_minute_no }}</td></tr>
                    @endif
                    @if ($event->sev_remarks)
                        <tr><td>Remarks</td><td><div class="sl-remark">{{ $event->sev_remarks }}</div></td></tr>
                    @endif
                </table>
            </div>
        @endif

        @if (count($changes))
            <div class="sl-card mt-3">
                <div class="sl-head py-2"><strong style="font-size:13px;">Fields changed ({{ count($changes) }})</strong></div>
                <div class="table-responsive">
                    <table class="sl-table">
                        <thead><tr><th>Table</th><th>Row</th><th>Field</th><th>Was</th><th>Became</th></tr></thead>
                        <tbody>
                            @foreach ($changes as $c)
                                <tr>
                                    <td class="sl-mono">{{ $c->fc_schema }}.{{ $c->fc_table }}</td>
                                    <td class="sl-mono">{{ $c->fc_pk_text ?: '—' }}</td>
                                    <td><strong>{{ $p->fieldLabel($c->fc_schema, $c->fc_table, $c->fc_column, $c->fc_label) }}</strong>
                                        <span class="sl-desig sl-mono">{{ $c->fc_column }}</span></td>
                                    <td><span class="sl-diff-old">{!! $c->fc_redacted ? '***' : $p->value($c->fc_old, $c->fc_column) !!}</span></td>
                                    <td><span class="sl-diff-new">{!! $c->fc_redacted ? '***' : $p->value($c->fc_new, $c->fc_column) !!}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @foreach ($rows as $r)
            @if ($r->old_row || $r->new_row)
                <div class="sl-card mt-3 mb-4">
                    <div class="sl-head py-2">
                        <strong style="font-size:13px;">
                            {{ $r->rc_schema }}.{{ $r->rc_table }} &middot; {{ $r->rc_pk_text ?: '—' }}
                            <span class="sl-chip sl-chip-{{ $r->rc_op === 'D' ? 'danger' : ($r->rc_op === 'I' ? 'success' : 'primary') }} ml-1">
                                {{ ['I'=>'Insert','U'=>'Update','D'=>'Delete','T'=>'Truncate'][$r->rc_op] ?? $r->rc_op }}
                            </span>
                        </strong>
                        @if ($r->rc_op === 'D')
                            <span class="sl-sub d-block">
                                The full row below is kept, so this record can be restored exactly as it was.
                            </span>
                        @endif
                    </div>
                    <div class="row no-gutters">
                        @if ($r->old_row)
                            <div class="col-md-6 p-3" style="border-right:1px solid #e2e8f0;">
                                <div class="sl-tile-meta mb-1">Before</div>
                                <pre class="sl-mono" style="font-size:10.5px; max-height:40vh; overflow:auto; margin:0;">{{ $r->old_row }}</pre>
                            </div>
                        @endif
                        @if ($r->new_row)
                            <div class="col-md-6 p-3">
                                <div class="sl-tile-meta mb-1">After</div>
                                <pre class="sl-mono" style="font-size:10.5px; max-height:40vh; overflow:auto; margin:0;">{{ $r->new_row }}</pre>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>
@endsection
