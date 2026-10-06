@extends('hrforms.pdf.layout')

@section('content')
    <div class="section-header">1. PROJECT & REQUISITION DETAILS</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Project Title</th>
            <td style="width: 75%;" colspan="3">{{ $live['project_title'] ?? '-' }} ({{ $live['project_code'] ?? '-' }})</td>
        </tr>
        <tr>
            <th style="width: 25%;">Division / Directorate</th>
            <td style="width: 25%;">{{ $live['division_name'] ?? '-' }}</td>
            <th style="width: 25%;">Work Order No & Date</th>
            <td style="width: 25%;">
                {{ $manual['work_order_no'] ?? '-' }} 
                @if(!empty($manual['work_order_date']))
                    ({{ \Carbon\Carbon::parse($manual['work_order_date'])->format('d M Y') }})
                @endif
            </td>
        </tr>
        <tr>
            <th>Project Life Cycle</th>
            <td>{{ !empty($live['start_date']) ? \Carbon\Carbon::parse($live['start_date'])->format('d M Y') : '-' }} to {{ !empty($live['estimated_end_date']) ? \Carbon\Carbon::parse($live['estimated_end_date'])->format('d M Y') : '-' }}</td>
            <th>Warranty Expiry Date</th>
            <td>{{ !empty($manual['warranty_expiry_date']) ? \Carbon\Carbon::parse($manual['warranty_expiry_date'])->format('d M Y') : '-' }}</td>
        </tr>
    </table>

    <div class="section-header">2. PROPOSED POSITION & REMUNERATION</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Proposed Position</th>
            <td style="width: 25%;">{{ $live['proposed_position'] ?? '-' }}</td>
            <th style="width: 25%;">Grade / Sub-Grade</th>
            <td style="width: 25%;">{{ $live['proposed_grade'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Proposed Monthly Salary</th>
            <td class="text-bold">Rs. {{ !empty($live['proposed_salary']) ? number_format($live['proposed_salary']) : '-' }}</td>
            <th>Annex K Salary Band</th>
            <td>{{ $live['salary_band_range'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Proposed Tenure</th>
            <td>{{ $live['tenure_months'] ?? '-' }} Months ({{ $live['tenure_days'] ?? '-' }} Days)</td>
            <th>Total Forecasted Cost</th>
            <td class="text-bold">Rs. {{ !empty($live['total_forecast']) ? number_format($live['total_forecast']) : '-' }}</td>
        </tr>
        @if(!empty($live['forecast_basis']))
        <tr>
            <th>Forecast Basis</th>
            <td colspan="3" class="text-muted" style="font-size: 8.5pt;">{{ $live['forecast_basis'] }}</td>
        </tr>
        @endif
    </table>

    <div class="section-header">3. FINANCIAL & SUBHEAD BREAKDOWN</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Total Approved Budget</th>
            <td style="width: 25%;">Rs. {{ !empty($live['total_approved_budget']) ? number_format($live['total_approved_budget']) : '-' }}</td>
            <th style="width: 25%;">HR Allocated Budget</th>
            <td style="width: 25%;">Rs. {{ !empty($live['hr_allocated_budget']) ? number_format($live['hr_allocated_budget']) : '-' }}</td>
        </tr>
        <tr>
            <th>Equipment Allocation</th>
            <td>Rs. {{ !empty($live['equipment_allocated_budget']) ? number_format($live['equipment_allocated_budget']) : '-' }}</td>
            <th>Misc Allocation</th>
            <td>Rs. {{ !empty($live['misc_allocated_budget']) ? number_format($live['misc_allocated_budget']) : '-' }}</td>
        </tr>
    </table>

    <div class="section-header">4. ADDITIONAL COST HEADS (MANUAL LAYER)</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Service Charges & Taxes</th>
            <td style="width: 25%;">{{ !empty($manual['service_charges_taxes']) ? 'Rs. ' . number_format($manual['service_charges_taxes']) : '-' }}</td>
            <th style="width: 25%;">Infrastructure Development</th>
            <td style="width: 25%;">{{ !empty($manual['infrastructure_development']) ? 'Rs. ' . number_format($manual['infrastructure_development']) : '-' }}</td>
        </tr>
        <tr>
            <th>Overheads</th>
            <td>{{ !empty($manual['overheads']) ? 'Rs. ' . number_format($manual['overheads']) : '-' }}</td>
            <th>Other Cost Heads</th>
            <td>{{ !empty($manual['other_cost_heads']) ? 'Rs. ' . number_format($manual['other_cost_heads']) : '-' }}</td>
        </tr>
    </table>

    <div class="section-header">5. QUALIFICATION & EXPERIENCE REQUIREMENTS</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Required Qualification</th>
            <td style="width: 75%;" colspan="3">{{ $manual['qualification_required'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Required Experience</th>
            <td colspan="3">{{ $manual['experience_required'] ?? '-' }}</td>
        </tr>
    </table>
@endsection
