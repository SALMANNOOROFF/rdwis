@extends('hrforms.pdf.layout')

@section('content')
    <div class="section-header">1. EMPLOYEE & CONTRACT DETAILS</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Employee Name</th>
            <td style="width: 25%;" class="text-bold">{{ $live['emp_name'] ?? '-' }}</td>
            <th style="width: 25%;">CNIC</th>
            <td style="width: 25%;">{{ $live['emp_cnic'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Designation & Grade</th>
            <td>{{ $live['job_title'] ?? '-' }} ({{ $live['grade'] ?? '-' }})</td>
            <th>Division / Directorate</th>
            <td>{{ $live['division_name'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Prior Contract Period</th>
            <td>{{ !empty($live['prior_contract_start']) ? \Carbon\Carbon::parse($live['prior_contract_start'])->format('d M Y') : '-' }} to {{ !empty($live['prior_contract_end']) ? \Carbon\Carbon::parse($live['prior_contract_end'])->format('d M Y') : '-' }}</td>
            <th>Current Gross Salary</th>
            <td class="text-bold">Rs. {{ !empty($live['current_gross_salary']) ? number_format($live['current_gross_salary']) : '-' }}</td>
        </tr>
        <tr>
            <th>Proposed Contract Period</th>
            <td>{{ !empty($live['proposed_start_date']) ? \Carbon\Carbon::parse($live['proposed_start_date'])->format('d M Y') : '-' }} to {{ !empty($live['proposed_end_date']) ? \Carbon\Carbon::parse($live['proposed_end_date'])->format('d M Y') : '-' }}</td>
            <th>Proposed Gross Salary</th>
            <td class="text-bold" style="color: #0D47A1;">Rs. {{ !empty($live['proposed_gross_salary']) ? number_format($live['proposed_gross_salary']) : '-' }}</td>
        </tr>
        <tr>
            <th>Proposed Increment (Para 67)</th>
            <td colspan="3">
                <span class="text-bold">{{ $live['proposed_increment_pct'] ?? '0' }}%</span>
                (Increment Amount: Rs. {{ !empty($live['increment_amount']) ? number_format($live['increment_amount']) : '0' }})
            </td>
        </tr>
    </table>

    <div class="section-header">2. FINANCIAL & BUDGET RECONCILIATION</div>
    @php
        $isDeficit = !($live['hr_balance_sufficient'] ?? true);
    @endphp
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Project Title</th>
            <td style="width: 75%;" colspan="3">{{ $live['project_title'] ?? ($live['project_name'] ?? '-') }} ({{ $live['project_code'] ?? '-' }})</td>
        </tr>
        <tr>
            <th style="width: 25%;">HR Allocated Budget</th>
            <td style="width: 25%;">Rs. {{ !empty($live['hr_allocated_budget']) ? number_format($live['hr_allocated_budget']) : '-' }}</td>
            <th style="width: 25%;">HR Utilized to Date</th>
            <td style="width: 25%;">Rs. {{ !empty($live['hr_budget_utilized_to_date']) ? number_format($live['hr_budget_utilized_to_date']) : '-' }}</td>
        </tr>
        <tr>
            <th>HR Committed (Total)</th>
            <td>Rs. {{ !empty($live['hr_budget_committed_total']) ? number_format($live['hr_budget_committed_total']) : '-' }}</td>
            <th>HR Remaining Balance</th>
            <td class="text-bold {{ $isDeficit ? 'text-danger' : '' }}">
                Rs. {{ !empty($live['hr_budget_remaining']) ? number_format($live['hr_budget_remaining']) : '-' }}
                ({{ $isDeficit ? 'DEFICIT / INSUFFICIENT' : 'SUFFICIENT' }})
            </td>
        </tr>
        <tr>
            <th>Case Financial Cost</th>
            <td class="text-bold">Rs. {{ !empty($live['case_cost']) ? number_format($live['case_cost']) : '-' }}</td>
            <th>Suggested Shortfall</th>
            <td class="text-bold text-danger">Rs. {{ !empty($live['suggested_shortfall']) ? number_format($live['suggested_shortfall']) : '0' }}</td>
        </tr>
    </table>

    @if($isDeficit)
        <div class="section-header" style="background-color: #FDEDEC; border-color: #E6B0AA; color: #900C3F;">3. BUDGET REALLOCATION / SHIFTING DETAILS (MANDATORY)</div>
        <table class="data-table">
            <tr>
                <th style="width: 25%;">Required Shift Amount</th>
                <td style="width: 75%;" class="text-bold" colspan="3">Rs. {{ !empty($manual['shift_amount']) ? number_format($manual['shift_amount']) : '-' }}</td>
            </tr>
            <tr>
                <th>Shift Justification</th>
                <td colspan="3" style="height: 40px; vertical-align: top;">{{ $manual['shift_justification'] ?? '-' }}</td>
            </tr>
        </table>
    @endif

    <div class="section-header">4. PERFORMANCE STATUS & RECOMMENDATIONS</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Performance Status</th>
            <td style="width: 25%;" class="text-bold">{{ $manual['performance_status'] ?? 'Satisfactory' }}</td>
            <th style="width: 25%;">Appraisal Score (Annex M)</th>
            <td style="width: 25%;">{{ !empty($live['appraisal_score']) ? $live['appraisal_score'] . ' / 60' : 'Appraisal Pending' }}</td>
        </tr>
        <tr>
            <th>Director Remarks</th>
            <td colspan="3" style="height: 40px; vertical-align: top;">{{ $manual['director_remarks'] ?? '-' }}</td>
        </tr>
    </table>
@endsection
