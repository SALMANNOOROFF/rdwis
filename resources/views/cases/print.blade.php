@extends('partials.case_print_layout')

@section('title', 'Case File ' . $case->gcs_file_no)

@section('content')
@php
    $dateFormatted = $case->created_at ? $case->created_at->format('d M y') : 'N/A';
@endphp

<div class="doc-top-row">
    <div class="main-title">
        E-Office Case File {{ $case->gcs_file_no }} dated {{ $dateFormatted }}
    </div>
    <div class="record-purpose-tag">FOR RECORD PURPOSE</div>
</div>

{{-- Borderless 3-Column Metadata matching Purchase Case Detail --}}
<div class="meta-grid">
    <div class="meta-item"><span class="meta-label">Title:</span> <span class="meta-value"><strong>{{ $case->gcs_title }}</strong></span></div>
    <div class="meta-item"><span class="meta-label">Category:</span> <span class="meta-value">{{ $case->gcs_category }}</span></div>
    <div class="meta-item"><span class="meta-label">Status:</span> <span class="meta-value text-right"><strong>{{ $case->gcs_status }}</strong></span></div>

    <div class="meta-item"><span class="meta-label">Initiator:</span> <span class="meta-value">{{ $case->creator->acc_name ?? 'Officer' }}</span></div>
    <div class="meta-item"><span class="meta-label">Division:</span> <span class="meta-value">{{ $case->unit->unt_name ?? 'Headquarters' }}</span></div>
    <div class="meta-item"><span class="meta-label">Priority:</span> <span class="meta-value text-right">{{ $case->gcs_priority }}</span></div>

    <div class="meta-item" style="grid-column: span 2;">
        <span class="meta-label">Current Seat:</span> <span class="meta-value">{{ $case->gcs_current_stage }} ({{ $case->currentHolder->acc_name ?? $case->gcs_current_stage }})</span>
    </div>
    <div class="meta-item">
        <span class="meta-label">Date:</span> <span class="meta-value text-right">{{ $dateFormatted }}</span>
    </div>

    @if(!empty($case->gcs_subject))
        <div class="meta-item" style="grid-column: span 3;">
            <span class="meta-label">Subject:</span> <span class="meta-value">{{ $case->gcs_subject }}</span>
        </div>
    @endif
</div>

@if(!empty($case->gcs_description))
    <span class="section-title">Case Particulars &amp; Note Sheet:</span>
    <div class="plain-summary-block" style="white-space: pre-wrap; font-family: 'Times New Roman', Times, serif; font-size: 10.5pt; line-height: 1.5;">{{ $case->gcs_description }}</div>
@endif

@if($case->notes->count() > 0)
    <span class="section-title">Formal Note-Sheet Paragraphs: ({{ $case->notes->count() }})</span>
    <div class="remarks-plain-list">
        @foreach($case->notes as $note)
            <div class="remark-text-item">
                <div class="remark-text-head">
                    <div>
                        <strong>Para {{ $note->gcn_para_no }}. {{ $note->gcn_author_name }}</strong>
                        <span>({{ $note->gcn_author_rank ? $note->gcn_author_rank . ' ' : '' }}{{ $note->gcn_author_desig ?? 'Officer' }})</span>
                        @if($note->gcn_title)
                            &mdash; <strong>{{ $note->gcn_title }}</strong>
                        @endif
                    </div>
                    <div class="remark-text-date">{{ $note->created_at ? $note->created_at->format('d M Y, h:i A') : '—' }}</div>
                </div>
                <div class="remark-text-body" style="white-space: pre-line;">{{ $note->gcn_content }}</div>
            </div>
        @endforeach
    </div>
@endif

@include('partials.case_print_remarks_and_attachments', [
    'remarksList' => $remarksList,
    'enclosuresList' => $enclosuresList,
    'remarksSectionTitle' => 'Approval & Movement Remarks Trail',
    'attachmentsSectionTitle' => 'Attached Documents',
])
@endsection
