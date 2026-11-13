@extends('partials.case_print_layout')

@section('title', 'Hiring Case #CC-' . $case->ctc_id)

@section('content')
@php
    $caseTypeRaw = strtoupper(trim((string)($case->ctc_type ?? 'CR')));
    $caseTypeName = match($caseTypeRaw) {
        'HG' => 'New Hiring (HG)',
        'CR' => 'Contract Renewal (CR)',
        'CE' => 'Contract Extension (CE)',
        'RH' => 'Re-Hiring (RH)',
        default => $caseTypeRaw,
    };

    $empName = $case->ctc_empnamecomp ?: ($case->employee->emp_name ?? 'Candidate / Employee');
    $empType = $case->ctc_emp_type ?: ($case->ctc_newctrtype == 2 ? 'Part Time' : 'Full Time');

    $proposedSalary = (float)($case->ctc_newsalary ?? 0);
    $approvedSalary = (float)($case->newContract?->ctr_salary ?: ($case->ctc_approvedsalary ?: $proposedSalary));
    $previousSalary = (float)($case->previous_salary ?? 0);

    $approvedStart = $case->newContract?->ctr_startdt ?: ($case->ctc_approvedstartdt ?: $case->ctc_newstartdt);
    $approvedEnd   = $case->newContract?->ctr_enddt ?: ($case->ctc_approvedenddt ?: $case->ctc_newenddt);
    $approvedGrade = $case->newContract?->ctr_grade ?: ($case->ctc_approvedgrade ?: $case->ctc_newgrade);
    $approvedTitle = $case->newContract?->ctr_jobtitle ?: ($case->ctc_approvedjobtitle ?: $case->ctc_newjobtitle);
    $approvedProb  = $case->newContract?->ctr_prob ?? ($case->ctc_approvedprob ?? $case->ctc_newprob);
    $approvedProbSal = $case->newContract?->ctr_probsal ?? ($case->ctc_approvedprobsal ?? $case->ctc_newprobsal);

    $dateFormatted = $case->ctc_date ? \Carbon\Carbon::parse($case->ctc_date)->format('d M y') : 'N/A';
    $statusLower = strtolower(trim((string)$case->ctc_status));
    $isFulfilled = in_array($statusLower, ['fulfilled', 'closed'], true) || !empty($case->ctc_newctr_id);

    if ($case->is_hr_admin) {
        $fundingLabel = 'CSRF';
    } elseif ($case->casePlans->count() > 0) {
        $fundingLabel = $case->casePlans->map(function($cp) {
            if ($cp->project) {
                return ($cp->project->prj_code ?: $cp->project->prj_title);
            }
            if (!empty($cp->ccp_hed_id)) {
                return \Illuminate\Support\Facades\DB::table('cen.heads')->where('hed_id', $cp->ccp_hed_id)->value('hed_code') ?: ('Head #' . $cp->ccp_hed_id);
            }
            return 'Unallocated';
        })->unique()->implode(', ');
    } else {
        $fundingLabel = $case->project_code;
    }
@endphp

<div class="doc-top-row">
    <div class="main-title">
        Hiring Case #CC-{{ $case->ctc_id }} dated {{ $dateFormatted }} ({{ $empName }})
    </div>
    <div class="record-purpose-tag">FOR RECORD PURPOSE</div>
</div>

{{-- Borderless 3-Column Metadata matching Purchase Case Detail --}}
<div class="meta-grid">
    <div class="meta-item"><span class="meta-label">Name:</span> <span class="meta-value"><strong>{{ $empName }}</strong></span></div>
    <div class="meta-item"><span class="meta-label">Type:</span> <span class="meta-value">{{ $caseTypeName }}</span></div>
    <div class="meta-item"><span class="meta-label">Proposed:</span> <span class="meta-value text-right">Rs. {{ number_format($proposedSalary, 0) }}/mo</span></div>

    <div class="meta-item"><span class="meta-label">Emp ID:</span> <span class="meta-value">{{ $case->ctc_emp_id ?: 'Pending' }}</span></div>
    <div class="meta-item"><span class="meta-label">Project:</span> <span class="meta-value">{{ $fundingLabel }}</span></div>
    <div class="meta-item"><span class="meta-label">Approved:</span> <span class="meta-value text-right"><strong>Rs. {{ number_format($approvedSalary, 0) }}/mo</strong></span></div>

    <div class="meta-item"><span class="meta-label">Division:</span> <span class="meta-value">{{ $case->division_name }}</span></div>
    <div class="meta-item"><span class="meta-label">Status:</span> <span class="meta-value"><strong>{{ $case->ctc_status }}</strong></span></div>
    <div class="meta-item"><span class="meta-label">Nature:</span> <span class="meta-value text-right">{{ $empType }}</span></div>

    <div class="meta-item" style="grid-column: span 2;">
        <span class="meta-label">Current Seat:</span> <span class="meta-value">{{ $case->current_office_name ?? ($case->current_stage ?: $case->ctc_status) }}</span>
    </div>
    <div class="meta-item">
        <span class="meta-label">Initiated:</span> <span class="meta-value text-right">{{ $dateFormatted }}</span>
    </div>
</div>

{{-- Contract Terms Table --}}
<span class="section-title">Contract Terms &amp; Compensation:</span>
<table class="doc-table">
    <thead>
        <tr>
            <th>Contract Term</th>
            @if($caseTypeRaw !== 'HG')
                <th>Previous Term</th>
            @endif
            <th>Proposed Term</th>
            <th>Approved / Effective Term</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Job Title / Designation</strong></td>
            @if($caseTypeRaw !== 'HG')
                <td>{{ $case->previous_jobtitle ?: '—' }}</td>
            @endif
            <td>{{ $case->ctc_newjobtitle ?: '—' }}</td>
            <td><strong>{{ $approvedTitle ?: '—' }}</strong></td>
        </tr>
        <tr>
            <td><strong>Pay Grade / Rank</strong></td>
            @if($caseTypeRaw !== 'HG')
                <td>{{ $case->previous_grade ?: '—' }}</td>
            @endif
            <td>{{ $case->ctc_newgrade ?: '—' }}</td>
            <td><strong>{{ $approvedGrade ?: '—' }}</strong></td>
        </tr>
        <tr>
            <td><strong>Monthly Salary</strong></td>
            @if($caseTypeRaw !== 'HG')
                <td>{{ $previousSalary > 0 ? 'Rs. ' . number_format($previousSalary, 0) : '—' }}</td>
            @endif
            <td>Rs. {{ number_format($proposedSalary, 0) }}</td>
            <td><strong>Rs. {{ number_format($approvedSalary, 0) }}</strong></td>
        </tr>
        <tr>
            <td><strong>Contract Period</strong></td>
            @if($caseTypeRaw !== 'HG')
                <td>
                    {{ $case->previous_startdt ? \Carbon\Carbon::parse($case->previous_startdt)->format('d M y') : '—' }}
                    to
                    {{ $case->previous_enddt ? \Carbon\Carbon::parse($case->previous_enddt)->format('d M y') : '—' }}
                </td>
            @endif
            <td>
                {{ $case->ctc_newstartdt ? \Carbon\Carbon::parse($case->ctc_newstartdt)->format('d M y') : '—' }}
                to
                {{ $case->ctc_newenddt ? \Carbon\Carbon::parse($case->ctc_newenddt)->format('d M y') : '—' }}
            </td>
            <td>
                <strong>
                    {{ $approvedStart ? \Carbon\Carbon::parse($approvedStart)->format('d M y') : '—' }}
                    to
                    {{ $approvedEnd ? \Carbon\Carbon::parse($approvedEnd)->format('d M y') : '—' }}
                </strong>
            </td>
        </tr>
        <tr>
            <td><strong>Probation Period &amp; Pay</strong></td>
            @if($caseTypeRaw !== 'HG')
                <td>—</td>
            @endif
            <td>
                {{ $case->ctc_newprob ? $case->ctc_newprob . ' Months' : 'None' }}
                {{ ($case->ctc_newprob && $case->ctc_newprobsal) ? ' (Rs. ' . number_format((float)$case->ctc_newprobsal, 0) . ')' : '' }}
            </td>
            <td>
                <strong>
                    {{ $approvedProb ? $approvedProb . ' Months' : 'None' }}
                    {{ ($approvedProb && $approvedProbSal) ? ' (Rs. ' . number_format((float)$approvedProbSal, 0) . ')' : '' }}
                </strong>
            </td>
        </tr>
    </tbody>
</table>

{{-- HR Fulfillment Details (plain clean format once fulfilled by HR) --}}
@if($isFulfilled)
    <span class="section-title">HR Contract Fulfillment &amp; Issuance Details:</span>
    <div class="plain-summary-block">
        <div>
            <strong>Issued Contract ID:</strong> {{ ($case->ctc_newctr_id || $case->newContract) ? '#CTR-' . ($case->newContract?->ctr_id ?? $case->ctc_newctr_id) : '—' }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Employee ID:</strong> {{ $case->ctc_emp_id ?: ($case->newContract?->ctr_num ?? '—') }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Signing Date:</strong> {{ $case->ctc_newsigndt ? \Carbon\Carbon::parse($case->ctc_newsigndt)->format('d M Y') : ($case->newContract?->ctr_date ? \Carbon\Carbon::parse($case->newContract->ctr_date)->format('d M Y') : '—') }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Fulfilled On:</strong> {{ $case->ctc_closedtg ? \Carbon\Carbon::parse($case->ctc_closedtg)->format('d M Y, h:i A') : '—' }}
        </div>
        @if(!empty($case->ctc_terminremarks))
            <div style="margin-top: 4px;">
                <strong>HR Fulfillment / Renewal Remarks:</strong> {{ $case->ctc_terminremarks }}
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
