@extends('partials.case_print_layout')

@section('title', 'Fund Shifting ' . $shift->case_number)

@section('content')
@php
    $effectiveAmt = (float)($shift->fsh_approved_amount ?: $shift->fsh_proposed_amount);
    $uName = $shift->project?->unit?->unt_namesh ?? ($shift->project?->unit?->unt_name ?? ($shift->unit?->unt_name ?? 'Division'));
    $dateFormatted = $shift->created_at ? \Carbon\Carbon::parse($shift->created_at)->format('d M y') : 'N/A';
@endphp

<div class="doc-top-row">
    <div class="main-title">
        Fund Shifting Case {{ $shift->case_number }} dated {{ $dateFormatted }} ({{ $shift->head->hed_code ?: ($shift->project->prj_code ?? 'Head') }})
    </div>
    <div class="record-purpose-tag">FOR RECORD PURPOSE</div>
</div>

{{-- Borderless 3-Column Metadata matching Purchase Case Detail --}}
<div class="meta-grid">
    <div class="meta-item"><span class="meta-label">Project:</span> <span class="meta-value">[{{ $shift->project->prj_code ?? 'PRJ' }}] {{ $shift->project->prj_title ?? 'Project' }}</span></div>
    <div class="meta-item"><span class="meta-label">Head:</span> <span class="meta-value">{{ $shift->head->hed_code ?: ($shift->project->prj_code ?? 'N/A') }}</span></div>
    <div class="meta-item"><span class="meta-label">Proposed:</span> <span class="meta-value text-right">Rs. {{ number_format((float)$shift->fsh_proposed_amount, 2) }}</span></div>

    <div class="meta-item"><span class="meta-label">Initiator:</span> <span class="meta-value">{{ $shift->fsh_created_name ?: ($shift->creator->acc_name ?? 'Officer') }}</span></div>
    <div class="meta-item"><span class="meta-label">From:</span> <span class="meta-value">{{ $shift->fsh_from_sbh_name }}</span></div>
    <div class="meta-item"><span class="meta-label">Approved:</span> <span class="meta-value text-right"><strong>Rs. {{ number_format($effectiveAmt, 2) }}</strong></span></div>

    <div class="meta-item"><span class="meta-label">Unit:</span> <span class="meta-value">{{ $uName }}</span></div>
    <div class="meta-item"><span class="meta-label">To:</span> <span class="meta-value">{{ $shift->fsh_to_sbh_name }}</span></div>
    <div class="meta-item"><span class="meta-label">Status:</span> <span class="meta-value text-right"><strong>{{ $shift->fsh_status }}</strong></span></div>

    <div class="meta-item" style="grid-column: span 3;">
        <span class="meta-label">Stage:</span> <span class="meta-value">{{ $shift->fsh_current_stage }}</span>
    </div>
</div>

@if(!empty($shift->fsh_purpose))
    <div class="plain-summary-block">
        <strong>Justification / Purpose:</strong> {{ $shift->fsh_purpose }}
    </div>
@endif

{{-- Allocation Shifting Comparison Table --}}
<span class="section-title">Reappropriation Comparison (Before vs After Approval):</span>
<table class="doc-table">
    <thead>
        <tr>
            <th>Role</th>
            <th>Subhead Name</th>
            <th class="text-right">Present Allocation</th>
            <th class="text-center">Proposed / Approved Shift</th>
            <th class="text-right">After Approval</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Source (From)</td>
            <td>{{ $shift->fsh_from_sbh_name }}</td>
            <td class="text-right">Rs. {{ number_format((float)$shift->fsh_from_alloc_before, 2) }}</td>
            <td class="text-center" style="color: #991b1b;">-Rs. {{ number_format($effectiveAmt, 2) }}</td>
            <td class="text-right"><strong>Rs. {{ number_format((float)$shift->fsh_from_alloc_after, 2) }}</strong></td>
        </tr>
        <tr>
            <td>Target (To)</td>
            <td>{{ $shift->fsh_to_sbh_name }}</td>
            <td class="text-right">Rs. {{ number_format((float)$shift->fsh_to_alloc_before, 2) }}</td>
            <td class="text-center" style="color: #166534;">+Rs. {{ number_format($effectiveAmt, 2) }}</td>
            <td class="text-right"><strong>Rs. {{ number_format((float)$shift->fsh_to_alloc_after, 2) }}</strong></td>
        </tr>
    </tbody>
</table>

{{-- IT Implementation Details (plain clean format once implemented) --}}
@if($shift->fsh_status === 'Implemented')
    <span class="section-title">IT Implementation &amp; Execution Details:</span>
    <div class="plain-summary-block">
        <div>
            <strong>Status:</strong> Implemented by IT
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Audit ID:</strong> #FS-{{ $shift->fsh_rev_id }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Executed By:</strong> {{ $shift->implementer->acc_name ?? ($shift->implementer->acc_username ?? 'IT Directorate') }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Executed On:</strong> {{ $shift->fsh_implemented_at ? $shift->fsh_implemented_at->format('d M Y, h:i A') : '—' }}
        </div>
        @if(!empty($shift->fsh_implementation_notes))
            <div style="margin-top: 4px;">
                <strong>Implementation Notes:</strong> {{ $shift->fsh_implementation_notes }}
            </div>
        @endif
    </div>
@endif

@include('partials.case_print_remarks_and_attachments', [
    'remarksList' => $remarksList,
    'enclosuresList' => $enclosuresList,
    'remarksSectionTitle' => 'Approval & Remarks Trail',
    'attachmentsSectionTitle' => 'Attached Documents',
])
@endsection
