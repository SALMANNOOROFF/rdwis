@extends('hrforms.pdf.layout')

@section('content')
    <div class="section-header">1. PERSONAL INFORMATION</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Full Name</th>
            <td style="width: 25%;" class="text-bold">{{ $live['personal_information']['name'] ?? ($live['candidate_name'] ?? '-') }}</td>
            <th style="width: 25%;">Father / Husband Name</th>
            <td style="width: 25%;">{{ $live['personal_information']['father_name'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>CNIC</th>
            <td>{{ $live['personal_information']['cnic'] ?? ($live['cnic'] ?? '-') }}</td>
            <th>Date of Birth</th>
            <td>{{ $live['personal_information']['dob'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Contact / Mobile</th>
            <td>{{ $live['personal_information']['mobile'] ?? '-' }}</td>
            <th>Email Address</th>
            <td>{{ $live['personal_information']['email'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Present Address</th>
            <td colspan="3">{{ $live['personal_information']['present_address'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Permanent Address</th>
            <td colspan="3">{{ $live['personal_information']['permanent_address'] ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-header">2. NEXT OF KIN & 3. EMERGENCY CONTACT</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Next of Kin Name</th>
            <td style="width: 25%;">{{ $live['next_of_kin']['name'] ?? '-' }}</td>
            <th style="width: 25%;">Emergency Contact Person</th>
            <td style="width: 25%;">{{ $live['emergency_contact']['name'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Relationship</th>
            <td>{{ $live['next_of_kin']['relationship'] ?? '-' }}</td>
            <th>Relationship</th>
            <td>{{ $live['emergency_contact']['relationship'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Contact Number</th>
            <td>{{ $live['next_of_kin']['contact'] ?? '-' }}</td>
            <th>Emergency Phone</th>
            <td>{{ $live['emergency_contact']['contact'] ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-header">4. EDUCATION & QUALIFICATIONS</div>
    @php
        $edu = $live['education'] ?? [];
    @endphp
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Degree / Certificate</th>
                <th style="width: 40%;">Institution / Board</th>
                <th style="width: 15%;" class="text-center">Year</th>
                <th style="width: 15%;" class="text-center">Grade / GPA</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($edu) && count($edu) > 0)
                @foreach($edu as $e)
                    <tr>
                        <td class="text-bold">{{ $e['degree_name'] ?? '-' }}</td>
                        <td>{{ $e['institute'] ?? '-' }}</td>
                        <td class="text-center">{{ $e['year'] ?? '-' }}</td>
                        <td class="text-center">{{ $e['grade_gpa'] ?? '-' }}</td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="4" class="text-center text-muted">No education records logged.</td></tr>
            @endif
        </tbody>
    </table>

    <div class="section-header">5. PROFESSIONAL COURSES & 6. EXPERIENCE</div>
    @php
        $exp = $live['experience'] ?? [];
    @endphp
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Organization</th>
                <th style="width: 35%;">Designation</th>
                <th style="width: 15%;" class="text-center">From</th>
                <th style="width: 15%;" class="text-center">To</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($exp) && count($exp) > 0)
                @foreach($exp as $x)
                    <tr>
                        <td class="text-bold">{{ $x['organization'] ?? '-' }}</td>
                        <td>{{ $x['designation'] ?? '-' }}</td>
                        <td class="text-center">{{ $x['from'] ?? '-' }}</td>
                        <td class="text-center">{{ $x['to'] ?? '-' }}</td>
                    </tr>
                @endforeach
            @else
                <tr><td colspan="4" class="text-center text-muted">Fresh graduate / no prior organization recorded.</td></tr>
            @endif
        </tbody>
    </table>

    <div class="section-header">7. VEHICLES & 8. BANK ACCOUNT (MEEZAN BANK)</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Bank Name</th>
            <td style="width: 25%;" class="text-bold">{{ $live['bank_account']['bank_name'] ?? 'Meezan Bank Ltd' }}</td>
            <th style="width: 25%;">Vehicle Registered</th>
            <td style="width: 25%;">{{ !empty($live['vehicles']) && count($live['vehicles']) > 0 ? ($live['vehicles'][0]['maker'] ?? '') . ' ' . ($live['vehicles'][0]['reg_no'] ?? '') : 'None' }}</td>
        </tr>
        <tr>
            <th>Account Title</th>
            <td>{{ $live['bank_account']['account_title'] ?? ($live['candidate_name'] ?? '-') }}</td>
            <th>Account / IBAN No</th>
            <td class="text-bold">{{ $live['bank_account']['account_number'] ?? ($live['bank_account']['iban'] ?? '-') }}</td>
        </tr>
    </table>

    <div class="section-header">9. RESEARCH PUBLICATIONS & 10. REFERENCES</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Publications / Thesis</th>
            <td style="width: 75%;" colspan="3">{{ !empty($live['publications']) ? implode(', ', (array)$live['publications']) : 'None reported.' }}</td>
        </tr>
        <tr>
            <th>Referee 1</th>
            <td>{{ $manual['referee_1'] ?? 'Details verified by HR' }}</td>
            <th>Referee 2</th>
            <td>{{ $manual['referee_2'] ?? 'Details verified by HR' }}</td>
        </tr>
        <tr>
            <th>Candidate Undertaking</th>
            <td colspan="3" style="font-size: 8pt; font-style: italic;">
                "I hereby affirm that all information provided in this Personal Data Form is true, complete, and accurate to the best of my knowledge."
            </td>
        </tr>
    </table>
@endsection
