@extends('hrforms.pdf.layout')

@section('content')
    <div class="section-header">1. POSITION & REQUISITION OVERVIEW</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Position Title</th>
            <td style="width: 25%;">{{ $live['position_applied'] ?? '-' }}</td>
            <th style="width: 25%;">Grade</th>
            <td style="width: 25%;">{{ $live['grade'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Division</th>
            <td>{{ $live['division_name'] ?? '-' }}</td>
            <th>Project Title</th>
            <td>{{ $live['project_title'] ?? ($live['project_name'] ?? '-') }}</td>
        </tr>
    </table>

    <div class="section-header">2. COMPARATIVE EVALUATION MATRIX</div>
    @php
        $candidates = $manual['candidates'] ?? ($live['candidates'] ?? []);
    @endphp
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 20%;">Candidate Name</th>
                <th style="width: 15%;">CNIC</th>
                <th style="width: 20%;">Qualification</th>
                <th style="width: 12%;">Experience</th>
                <th style="width: 14%;">Key Skills</th>
                <th style="width: 14%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($candidates as $idx => $cand)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-bold">{{ $cand['name'] ?? '-' }}</td>
                    <td>{{ $cand['cnic'] ?? '-' }}</td>
                    <td>{{ $cand['qualification'] ?? '-' }}</td>
                    <td>{{ $cand['experience_years'] ?? ($cand['experience'] ?? '-') }}</td>
                    <td>{{ $cand['skills'] ?? '-' }}</td>
                    <td>{{ $cand['remarks'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-header">3. RELAXATION JUSTIFICATION & BOARD RECOMMENDATION</div>
    <table class="data-table">
        <tr>
            <th style="width: 30%;">Justification for Relaxation (Para 29)<br><span class="text-muted" style="font-size: 8pt;">(Student / Qualification Criteria)</span></th>
            <td style="width: 70%; height: 50px; vertical-align: top;">
                {{ $manual['justification_relaxation'] ?? 'No relaxation requested / candidate meets standard criteria.' }}
            </td>
        </tr>
        <tr>
            <th>Director / Selection Board Remarks</th>
            <td style="height: 50px; vertical-align: top;">
                {{ $manual['director_signature_remarks'] ?? 'Recommended for Selection Board interview per comparative assessment.' }}
            </td>
        </tr>
    </table>
@endsection
