@extends('hrforms.pdf.layout')

@section('content')
    <div class="section-header">1. CANDIDATE & POSITION DETAILS</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Candidate Name</th>
            <td style="width: 25%;" class="text-bold">{{ $live['candidate_name'] ?? '-' }}</td>
            <th style="width: 25%;">CNIC</th>
            <td style="width: 25%;">{{ $live['candidate_cnic'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Position Applied For</th>
            <td>{{ $live['position_applied'] ?? '-' }} ({{ $live['grade'] ?? '-' }})</td>
            <th>Interview Date & Time</th>
            <td>{{ $manual['interview_date_time'] ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-header">2. EVALUATION CRITERIA (12 PARAMETERS, SCALE 1 TO 5, TOTAL 60)</div>
    @php
        $scores = $manual['scores'] ?? [];
        $criteria = $live['evaluation_criteria'] ?? [];
        $totalScore = $manual['total_score'] ?? 0;
    @endphp
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;" class="text-center">#</th>
                <th style="width: 68%;">Evaluation Parameter</th>
                <th style="width: 14%;" class="text-center">Score (1-5)</th>
                <th style="width: 12%;" class="text-center">Max Scale</th>
            </tr>
        </thead>
        <tbody>
            @foreach($criteria as $idx => $c)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $c['label'] ?? '' }}</td>
                    <td class="text-center text-bold">{{ $scores[$c['key']] ?? '-' }}</td>
                    <td class="text-center text-muted">5</td>
                </tr>
            @endforeach
            <tr style="background-color: #EAECEE; font-weight: bold;">
                <td colspan="2" class="text-right">TOTAL SCORE (OUT OF 60):</td>
                <td class="text-center" style="font-size: 11pt; color: #0D47A1;">{{ $totalScore }}</td>
                <td class="text-center">60</td>
            </tr>
        </tbody>
    </table>

    <div class="section-header">3. COMMITTEE REMARKS</div>
    <table class="data-table">
        <tr>
            <th style="width: 30%;">Remarks by Concerned Director Representative</th>
            <td style="width: 70%; height: 40px; vertical-align: top;">
                {{ $manual['remarks_by_concerned_dir_rep'] ?? '-' }}
            </td>
        </tr>
        <tr>
            <th>Remarks by Director HR / SO HR</th>
            <td style="height: 40px; vertical-align: top;">
                {{ $manual['remarks_by_dir_hr_so_hr'] ?? '-' }}
            </td>
        </tr>
    </table>
@endsection
