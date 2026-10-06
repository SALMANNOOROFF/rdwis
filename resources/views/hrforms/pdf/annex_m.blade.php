@extends('hrforms.pdf.layout')

@section('content')
    <div class="section-header">1. APPRAISEE PARTICULARS</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Employee Name</th>
            <td style="width: 25%;" class="text-bold">{{ $live['employee_name'] ?? '-' }}</td>
            <th style="width: 25%;">CNIC</th>
            <td style="width: 25%;">{{ $live['cnic'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Current Designation & Grade</th>
            <td>{{ $live['designation'] ?? '-' }} ({{ $live['grade'] ?? '-' }})</td>
            <th>Current Gross Salary</th>
            <td class="text-bold">Rs. {{ !empty($live['current_salary']) ? number_format($live['current_salary']) : '-' }}</td>
        </tr>
        <tr>
            <th>Project Title</th>
            <td>{{ $live['project_title'] ?? '-' }} ({{ $live['project_code'] ?? '-' }})</td>
            <th>Period Under Review</th>
            <td>{{ !empty($live['review_period_from']) ? \Carbon\Carbon::parse($live['review_period_from'])->format('d M Y') : '-' }} to {{ !empty($live['review_period_to']) ? \Carbon\Carbon::parse($live['review_period_to'])->format('d M Y') : '-' }}</td>
        </tr>
    </table>

    <div class="section-header">2. PERFORMANCE EVALUATION (6 CRITERIA, 10 MARKS EACH, TOTAL 60)</div>
    @php
        $marks = $manual['marks'] ?? [];
        $criteria = $live['evaluation_criteria'] ?? [];
        $totalMarks = $manual['total_marks'] ?? 0;
        $pct = $manual['percentage'] ?? 0;
        $rating = $manual['performance_rating'] ?? 'N/A';
        $maxInc = $manual['max_allowed_increment_pct'] ?? 0;
    @endphp
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;" class="text-center">#</th>
                <th style="width: 68%;">Evaluation Parameter</th>
                <th style="width: 14%;" class="text-center">Marks (0-10)</th>
                <th style="width: 12%;" class="text-center">Max Marks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($criteria as $idx => $c)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $c['label'] ?? '' }}</td>
                    <td class="text-center text-bold">{{ isset($marks[$c['key']]) && $marks[$c['key']] !== null ? $marks[$c['key']] : '-' }}</td>
                    <td class="text-center text-muted">10</td>
                </tr>
            @endforeach
            <tr style="background-color: #EAECEE; font-weight: bold;">
                <td colspan="2" class="text-right">TOTAL MARKS OBTAINED:</td>
                <td class="text-center" style="font-size: 11pt; color: #0D47A1;">{{ $totalMarks }}</td>
                <td class="text-center">60</td>
            </tr>
        </tbody>
    </table>

    <div class="section-header">3. SCORING SUMMARY & INCREMENT ELIGIBILITY (PARA 67)</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Percentage Scored</th>
            <td style="width: 25%; font-size: 10pt;" class="text-bold">{{ $pct }}%</td>
            <th style="width: 25%;">Performance Rating</th>
            <td style="width: 25%; font-size: 10pt;" class="text-bold text-primary">{{ $rating }}</td>
        </tr>
        <tr>
            <th>Max Allowed Increment</th>
            <td class="text-bold" style="color: #2E7D32;">{{ $maxInc }}%</td>
            <th>Key Reference</th>
            <td class="text-muted" style="font-size: 8pt;">
                &lt;30 Needs Imp | 31-40 Avg | 41-50 Above Avg | 51-55 Very Good | 56-60 Outstanding
            </td>
        </tr>
        @if(!empty($manual['exceptional_performance_citation']))
        <tr>
            <th>Exceptional Performance Citation<br><span class="text-muted" style="font-size: 7.5pt;">(Required for &gt; 10%, up to 20%)</span></th>
            <td colspan="3" style="background-color: #F4F6F6;">{{ $manual['exceptional_performance_citation'] }}</td>
        </tr>
        @endif
        <tr>
            <th>Remarks by Concerned Director</th>
            <td colspan="3" style="height: 40px; vertical-align: top;">{{ $manual['remarks_by_concerned_dir'] ?? '-' }}</td>
        </tr>
    </table>
@endsection
