{{-- One event row, shared by the category list and the print sheet.
     Expects: $r (a syslog.v_timeline row), $p (LogPresenter), $category --}}
@php
    $rowClass = ((int) $r->sev_severity === 2) ? 'sl-crit'
              : (($r->sev_result !== 'ok') ? 'sl-warn' : '');
@endphp
<tr class="{{ $rowClass }}">
    <td class="sl-dtg">
        {{ $p->dtg($r->sev_dtg, 'd M Y') }}<br>
        <span class="text-muted sl-mono">{{ $p->dtg($r->sev_dtg, 'H:i:s') }}</span>
    </td>
    <td>
        <span class="sl-who">{{ $r->sev_name ?: $r->sev_username }}</span>
        @if ($r->sev_desig)
            <span class="sl-desig">{{ trim(($r->sev_rank ? $r->sev_rank.' · ' : '').$r->sev_desig) }}</span>
        @endif
        @if ($r->sev_implicit || ! $r->sev_acc_id)
            <span class="sl-chip sl-chip-warning mt-1" title="No signed-in user: legacy app, script or direct SQL">
                <i class="fas fa-user-secret"></i> unowned
            </span>
        @endif
    </td>
    <td style="font-size:11.5px; color:#475569;">{{ $r->sev_untname ?: '—' }}</td>
    <td>
        <span class="sl-chip sl-chip-{{ $p->actionClass($r->sev_action) }}">
            <i class="fas {{ $p->actionIcon($r->sev_action) }}"></i>
            {{ $p->actionLabel($r->sev_action) }}
        </span>
        @if ($r->sev_result !== 'ok')
            <span class="sl-chip sl-chip-danger">{{ $r->sev_result }}</span>
        @endif
    </td>
    <td>
        @if ($r->sev_object && $r->sev_objid)
            <a href="{{ route('admin.syslog.record', [$category, $r->sev_object, $r->sev_objid]) }}"
               style="font-weight:700; color:#2563eb;">
                {{ $r->sev_objref ?: ($r->sev_object.' #'.$r->sev_objid) }}
            </a>
            <span class="sl-desig">{{ $r->sev_object }} #{{ $r->sev_objid }}</span>
        @elseif ($r->sev_objref)
            {{ $r->sev_objref }}
        @else
            <span class="sl-null">—</span>
        @endif
    </td>
    <td style="font-size:11.5px;">
        @if ($r->sev_stage_from || $r->sev_stage_to)
            {{ $r->sev_stage_from ?: '—' }}<span class="sl-arrow">&rarr;</span><strong>{{ $r->sev_stage_to ?: '—' }}</strong><br>
        @endif
        @if ($r->sev_to_holder)
            <span class="text-muted"><i class="fas fa-arrow-right mr-1" style="font-size:9px;"></i>{{ $r->sev_to_holder }}</span>
        @endif
        @if (! $r->sev_stage_from && ! $r->sev_stage_to && ! $r->sev_to_holder)
            <span class="sl-null">—</span>
        @endif
    </td>
    <td style="font-size:11.5px;">{!! $p->changeSummary($r) !!}</td>
    <td style="font-size:11.5px; max-width:260px;">
        @if ($r->sev_remarks)
            {{ \Illuminate\Support\Str::limit($r->sev_remarks, 140) }}
        @else
            <span class="sl-null">—</span>
        @endif
        @if ($r->sev_authority_ref)
            <span class="sl-desig"><i class="fas fa-stamp mr-1"></i>{{ $r->sev_authority_ref }}</span>
        @endif
    </td>
    <td class="sl-mono">{{ $r->sev_ip ?: '—' }}</td>
    <td>
        <span class="sl-chip sl-chip-{{ $p->sourceClass($r->sev_source) }}">{{ $p->sourceLabel($r->sev_source) }}</span>
    </td>
    <td class="text-right">
        <a href="{{ route('admin.syslog.event', $r->sev_id) }}" class="btn btn-xs btn-outline-secondary"
           style="font-size:10.5px; padding:1px 7px;" title="Full event detail">
            <i class="fas fa-search-plus"></i>
        </a>
    </td>
</tr>
