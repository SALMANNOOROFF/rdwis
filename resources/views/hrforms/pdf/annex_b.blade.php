@extends('hrforms.pdf.layout')

@section('content')
    <div class="section-header">1. PROJECT & SELECTION BOARD PARTICULARS</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Project Title</th>
            <td style="width: 75%;" colspan="3">{{ $live['project_title'] ?? '-' }} ({{ $live['project_code'] ?? '-' }})</td>
        </tr>
        <tr>
            <th style="width: 25%;">Division</th>
            <td style="width: 25%;">{{ $live['division_name'] ?? '-' }}</td>
            <th style="width: 25%;">Approved Headcount / Already Hired</th>
            <td style="width: 25%;">{{ $manual['approved_hr_count'] ?? '-' }} / {{ $live['already_hired_count'] ?? 0 }}</td>
        </tr>
        <tr>
            <th>Interview Date & Time</th>
            <td>
                {{ !empty($manual['interview_date']) ? \Carbon\Carbon::parse($manual['interview_date'])->format('d M Y') : '-' }}
                @if(!empty($manual['interview_time'])) at {{ $manual['interview_time'] }} @endif
            </td>
            <th>Interview Venue</th>
            <td>{{ $manual['interview_venue'] ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-header">2. FINANCIAL & BUDGET RECONCILIATION</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">HR Allocated Budget</th>
            <td style="width: 25%;">Rs. {{ !empty($live['hr_allocated_budget']) ? number_format($live['hr_allocated_budget']) : '-' }}</td>
            <th style="width: 25%;">HR Utilized to Date</th>
            <td style="width: 25%;">Rs. {{ !empty($live['hr_budget_utilized_to_date']) ? number_format($live['hr_budget_utilized_to_date']) : '-' }}</td>
        </tr>
        <tr>
            <th>HR Committed (Total)</th>
            <td>Rs. {{ !empty($live['hr_budget_committed_total']) ? number_format($live['hr_budget_committed_total']) : '-' }}</td>
            <th>HR Available Balance</th>
            <td class="text-bold">Rs. {{ !empty($live['hr_budget_available']) ? number_format($live['hr_budget_available']) : '-' }}</td>
        </tr>
        <tr>
            <th>Case Financial Impact</th>
            <td class="text-bold" colspan="3">Rs. {{ !empty($live['case_financial_impact']) ? number_format($live['case_financial_impact']) : '-' }}</td>
        </tr>
    </table>

    <div class="section-header">3. CANDIDATE SELECTION</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Designation & Grade</th>
            <td style="width: 25%;">{{ $live['job_title'] ?? '-' }} ({{ $live['grade'] ?? '-' }})</td>
            <th style="width: 25%;">Gross Pay & Tenure</th>
            <td style="width: 25%;">Rs. {{ !empty($live['gross_pay']) ? number_format($live['gross_pay']) : '-' }} / month ({{ $live['duration'] ?? '-' }})</td>
        </tr>
        <tr>
            <th>Principal Candidate</th>
            <td class="text-bold" style="color: #0D47A1;">{{ $manual['principal_candidate'] ?? ($live['principal_candidate']['name'] ?? '-') }}</td>
            <th>Standby Candidate</th>
            <td>{{ $manual['standby_candidate'] ?? ($manual['standby_candidate_name'] ?? '-') }}</td>
        </tr>
    </table>

    <div class="section-header">4. SHORTLISTED CANDIDATES (PARA 14)</div>
    @php
        $shortlist = $manual['shortlisted_candidates'] ?? [];
        $isSingle = !empty($manual['single_candidate_mode']);
    @endphp
    @if($isSingle)
        <div class="notes-box" style="margin-bottom: 8px;">
            <strong>Single-Candidate Selection Mode Justification:</strong>
            <p style="margin: 2px 0 0 0;">{{ $manual['single_candidate_justification'] ?? '-' }}</p>
        </div>
    @endif
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 30%;">Candidate Name</th>
                <th style="width: 25%;">Qualification</th>
                <th style="width: 25%;">Institute</th>
                <th style="width: 15%;">Field Experience</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($shortlist) && count($shortlist) > 0)
                @foreach($shortlist as $idx => $cand)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="text-bold">{{ $cand['name'] ?? '-' }}</td>
                        <td>{{ $cand['qualification'] ?? '-' }}</td>
                        <td>{{ $cand['institute'] ?? '-' }}</td>
                        <td>{{ $cand['field_experience'] ?? ($cand['experience'] ?? '-') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="text-center">1</td>
                    <td class="text-bold">{{ $live['principal_candidate']['name'] ?? '-' }}</td>
                    <td>{{ $live['principal_candidate']['qualification'] ?? '-' }}</td>
                    <td>{{ $live['principal_candidate']['institute'] ?? '-' }}</td>
                    <td>{{ $live['principal_candidate']['field_experience'] ?? '-' }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="section-header">5. BOARD RECOMMENDATIONS & REMARKS</div>
    <table class="data-table">
        <tr>
            <td style="height: 60px; vertical-align: top;">
                {{ $manual['board_recommendations'] ?? 'Recommended for hiring on contract basis per Selection Board consensus.' }}
            </td>
        </tr>
    </table>
@endsection
