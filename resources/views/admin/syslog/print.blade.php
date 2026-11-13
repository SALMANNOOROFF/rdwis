<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>System Log &mdash; {{ $header->objref ?: ($object.' #'.$id) }}</title>
    <style>
        @page { size: A4; margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
            font-size: 10pt; color: #111; margin: 0; padding: 0; background: #fff;
        }
        h1 { font-size: 15pt; margin: 0 0 2mm; letter-spacing: -0.2px; }
        h2 {
            font-size: 11pt; margin: 7mm 0 2mm; padding-bottom: 1mm;
            border-bottom: 1.2pt solid #333; text-transform: uppercase; letter-spacing: .4px;
        }
        .hdr { border-bottom: 2pt solid #111; padding-bottom: 3mm; margin-bottom: 4mm; }
        .meta { font-size: 8.5pt; color: #444; line-height: 1.5; }
        .meta strong { color: #000; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 3mm; }
        th {
            background: #eee; border: .6pt solid #999; padding: 1.4mm 2mm;
            font-size: 8pt; text-align: left; text-transform: uppercase; letter-spacing: .3px;
        }
        td { border: .6pt solid #bbb; padding: 1.4mm 2mm; font-size: 8.5pt; vertical-align: top; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .mono { font-family: 'Consolas', 'Courier New', monospace; font-size: 7.5pt; }
        .muted { color: #666; }
        .b { font-weight: 700; }
        .act { font-weight: 700; text-transform: uppercase; font-size: 7.5pt; letter-spacing: .3px; }
        .remark {
            border-left: 2pt solid #999; padding: 1mm 2mm; margin-top: 1mm;
            background: #f6f6f6; font-size: 8.5pt; white-space: pre-wrap;
        }
        .evt { page-break-inside: avoid; border-bottom: .5pt dashed #ccc; padding: 2mm 0; }
        .arrow { color: #666; }
        .foot {
            position: fixed; bottom: 4mm; left: 0; right: 0;
            font-size: 7.5pt; color: #666; border-top: .5pt solid #ccc; padding-top: 1mm;
            display: flex; justify-content: space-between;
        }
        .seal { font-size: 8pt; padding: 1.5mm 2mm; border: .8pt solid #333; margin-bottom: 3mm; }
        .no-print { margin-bottom: 4mm; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body onload="window.print()">

<div class="no-print" style="text-align:right;">
    <button onclick="window.print()" style="padding:5px 14px; font-size:11pt; cursor:pointer;">Print</button>
    <button onclick="window.close()" style="padding:5px 14px; font-size:11pt; cursor:pointer;">Close</button>
</div>

<div class="hdr">
    <h1>System Log Sheet &mdash; {{ $header->objref ?: ($object.' #'.$id) }}</h1>
    <div class="meta">
        <strong>Module:</strong> {{ $object }} &nbsp;&middot;&nbsp;
        <strong>Record ID:</strong> {{ $id }} &nbsp;&middot;&nbsp;
        <strong>Current stage:</strong> {{ $header->last_stage ?: '—' }}<br>
        <strong>First logged:</strong> {{ $p->dtg($header->opened_at, 'd M Y, H:i') }} &nbsp;&middot;&nbsp;
        <strong>Last activity:</strong> {{ $p->dtg($header->last_at, 'd M Y, H:i') }} &nbsp;&middot;&nbsp;
        <strong>Events:</strong> {{ (int) $header->events }} &nbsp;&middot;&nbsp;
        <strong>Movements:</strong> {{ (int) $header->movements }}<br>
        <strong>Printed by:</strong> {{ auth()->user()->acc_name ?? auth()->user()->acc_username ?? '—' }}
        ({{ auth()->user()->acc_desig ?? '—' }}) &nbsp;&middot;&nbsp;
        <strong>Printed on:</strong> {{ now()->format('d M Y, H:i:s') }}
    </div>
</div>

<div class="seal">
    <strong>Trail integrity at time of printing:</strong>
    @if (! $verify)
        Not verified &mdash; no verification run on record.
    @elseif ($verify->vrn_status === 'ok')
        VERIFIED INTACT &mdash; {{ (int) $verify->vrn_seals_ok }} seal(s) checked,
        no event modified. Last run {{ $p->dtg($verify->vrn_dtg, 'd M Y, H:i') }}.
    @else
        <span class="b">CHAIN BROKEN</span> &mdash; {{ $verify->vrn_detail }}
    @endif
</div>

{{-- ======================================================== TIMELINE ======== --}}
<h2>Movement &amp; Action Register</h2>
@forelse ($trail as $t)
    <div class="evt">
        <table style="margin:0; border:none;">
            <tr>
                <td style="border:none; padding:0; width:7mm;" class="b">{{ (int) $t->sr }}.</td>
                <td style="border:none; padding:0;">
                    <span class="act">{{ $p->actionLabel($t->action) }}</span>
                    @if ($t->minute_no) <span class="muted">&middot; Minute {{ (int) $t->minute_no }}</span> @endif
                    @if ($t->result !== 'ok') <span class="b">&middot; {{ strtoupper($t->result) }}</span> @endif
                    @if ($t->src === 'backfill') <span class="muted">&middot; imported history</span> @endif
                    <br>
                    <span class="b">{{ trim(($t->by_rank ? $t->by_rank.' ' : '').($t->by_name ?: '')) }}</span>
                    @if ($t->by_desig) ({{ $t->by_desig }}) @endif
                    @if ($t->by_unit) <span class="muted">&middot; {{ $t->by_unit }}</span> @endif
                    @if ($t->stage_from || $t->stage_to)
                        <br>{{ $t->stage_from ?: '—' }} <span class="arrow">&rarr;</span> <span class="b">{{ $t->stage_to ?: '—' }}</span>
                    @endif
                    @if ($t->sent_to) <br>Sent to: <span class="b">{{ $t->sent_to }}</span> @endif
                    @if ($t->sent_from) <span class="muted">&middot; received from {{ $t->sent_from }}</span> @endif
                    @if ($t->on_authority) <br>On authority of: <span class="b">{{ $t->on_authority }}</span> @endif
                    @if ($t->remarks) <div class="remark">{{ $t->remarks }}</div> @endif
                </td>
                <td style="border:none; padding:0; width:40mm; text-align:right;" class="mono">
                    {{ $p->dtg($t->dtg, 'd M Y H:i:s') }}<br>
                    {{ $t->from_ip ?: 'no IP' }}<br>
                    @if ((int) $t->fields_changed > 0) {{ (int) $t->fields_changed }} field(s) @endif
                </td>
            </tr>
        </table>
    </div>
@empty
    <p class="muted">No events recorded.</p>
@endforelse

{{-- ========================================================== CUSTODY ======= --}}
@if (count($custody))
    <h2>Custody &mdash; who held it and for how long</h2>
    <table>
        <thead><tr><th>#</th><th>Received from</th><th>Held by</th><th>Stage</th><th>Received</th>
                   <th>Held for</th><th>Forwarded to</th><th>Changed?</th></tr></thead>
        <tbody>
            @foreach ($custody as $c)
                <tr>
                    <td>{{ (int) $c->cs_seq }}</td>
                    <td>{{ $c->received_from ?: '—' }}</td>
                    <td class="b">{{ $c->held_by ?: '—' }}</td>
                    <td>{{ $c->cs_stage ?: '—' }}</td>
                    <td class="mono">{{ $p->dtg($c->received_at, 'd M y H:i') }}</td>
                    <td>{{ $c->held_for ? \Illuminate\Support\Str::before($c->held_for, '.') : '—' }}</td>
                    <td>{{ $c->forwarded_to ?: 'still here' }}</td>
                    <td>{{ $c->changed_anything === null ? '—' : ($c->changed_anything ? 'Yes' : 'No change') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- ========================================================== CHANGES ======= --}}
@if (count($changes))
    <h2>Field-level changes</h2>
    <table>
        <thead><tr><th>Date &amp; time</th><th>Who</th><th>Field</th><th>Was</th><th>Became</th><th>IP</th></tr></thead>
        <tbody>
            @foreach ($changes as $c)
                <tr>
                    <td class="mono">{{ $p->dtg($c->sev_dtg, 'd M y H:i') }}</td>
                    <td>{{ $c->sev_name ?: $c->sev_username }}<br><span class="muted">{{ $c->sev_desig }}</span></td>
                    <td class="b">{{ $p->fieldLabel($c->fc_schema, $c->fc_table, $c->fc_column, $c->fc_label) }}
                        <br><span class="mono muted">{{ $c->fc_schema }}.{{ $c->fc_table }}</span></td>
                    <td>{!! $c->fc_redacted ? '***' : $p->value($c->fc_old, $c->fc_column) !!}</td>
                    <td>{!! $c->fc_redacted ? '***' : $p->value($c->fc_new, $c->fc_column) !!}</td>
                    <td class="mono">{{ $c->sev_ip ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

{{-- ============================================================ FILES ======= --}}
@if (count($files))
    <h2>Files</h2>
    <table>
        <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Original filename</th><th>Stored as</th><th>SHA-256</th></tr></thead>
        <tbody>
            @foreach ($files as $f)
                <tr>
                    <td class="mono">{{ $p->dtg($f->at, 'd M y H:i') }}</td>
                    <td>{{ $f->sev_name ?: $f->sev_username }}</td>
                    <td>{{ ucfirst($f->fe_action) }}</td>
                    <td class="b">{{ $f->fe_orig_name ?: '—' }}</td>
                    <td class="mono">{{ $f->fe_stored_path ?: '—' }}</td>
                    <td class="mono">{{ $f->fe_sha256 ? substr($f->fe_sha256, 0, 16).'…' : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="foot">
    <span>RDWIS 2.0 &mdash; System Logs &middot; {{ $object }} #{{ $id }}</span>
    <span>Generated {{ now()->format('d M Y H:i') }} &middot; append-only audit trail</span>
</div>

</body>
</html>
