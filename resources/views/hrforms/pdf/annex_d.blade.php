@extends('hrforms.pdf.layout')

@section('content')
    <div class="section-header">1. PARTIES & ENGAGEMENT PARTICULARS</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">Employee Name</th>
            <td style="width: 25%;" class="text-bold">{{ $live['employee_name'] ?? '-' }}</td>
            <th style="width: 25%;">CNIC</th>
            <td style="width: 25%;">{{ $live['cnic'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Designation & Grade</th>
            <td>{{ $live['designation'] ?? '-' }} ({{ $live['grade'] ?? '-' }})</td>
            <th>Division / Directorate</th>
            <td>{{ $live['division_name'] ?? '-' }}</td>
        </tr>
        <tr>
            <th>Project Title</th>
            <td colspan="3">{{ $live['project_title'] ?? '-' }} ({{ $live['project_code'] ?? '-' }})</td>
        </tr>
    </table>

    <div class="section-header">2. NON-DISCLOSURE UNDERTAKING (RDW/HR/F-03)</div>
    <div style="font-size: 8.5pt; line-height: 1.45; text-align: justify; margin: 8px 0; border: 1px solid #D0D0D0; padding: 10px; background-color: #FAFAFA;">
        <p style="margin: 0 0 6px 0;"><strong>I, the undersigned, hereby solemnly affirm and agree that:</strong></p>
        <ol style="margin: 0 0 0 18px; padding: 0;">
            <li style="margin-bottom: 4px;">I shall maintain strict confidentiality in respect of all proprietary information, designs, technical documents, code, research data, hardware drawings, and classified military/defense specifications disclosed to me during my employment at RDW.</li>
            <li style="margin-bottom: 4px;">I shall not at any time, directly or indirectly, disclose, publish, duplicate, or communicate any confidential material to any unauthorized person, firm, organization, or media platform without prior written approval from the Competent Authority (DG NRDI / MD RDW).</li>
            <li style="margin-bottom: 4px;">All intellectual property, inventions, and research deliverables generated during my engagement with the project shall remain the sole and exclusive property of RDW / NRDI.</li>
            <li style="margin-bottom: 4px;">Upon completion, termination, or resignation from my contract, I shall immediately return all documents, prototypes, electronic files, storage devices, and equipment belonging to RDW.</li>
            <li>Any breach of this Non-Disclosure Agreement shall render me liable to disciplinary action, termination of contract, and criminal proceedings under the relevant Official Secrets Act and cyber legislation.</li>
        </ol>
    </div>

    <div class="section-header">3. SIGNATURES & WITNESSES</div>
    <table class="data-table" style="margin-top: 15px;">
        <tr>
            <th style="width: 50%; text-align: center;">EMPLOYEE / EXECUTANT</th>
            <th style="width: 50%; text-align: center;">ON BEHALF OF RDW</th>
        </tr>
        <tr>
            <td style="height: 70px; vertical-align: bottom; padding: 10px;">
                <div style="border-bottom: 1px solid #666; width: 80%; margin: 0 auto 5px auto;"></div>
                <div class="text-center font-weight-bold">{{ $live['employee_name'] ?? '-' }}</div>
                <div class="text-center text-muted" style="font-size: 8pt;">CNIC: {{ $live['cnic'] ?? '-' }}</div>
            </td>
            <td style="height: 70px; vertical-align: bottom; padding: 10px;">
                <div style="border-bottom: 1px solid #666; width: 80%; margin: 0 auto 5px auto;"></div>
                <div class="text-center font-weight-bold">Director HR / Project Director</div>
                <div class="text-center text-muted" style="font-size: 8pt;">Research & Development Wing</div>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px;">
                <strong>Witness 1:</strong><br>
                Name: {{ $manual['witness_1_name'] ?? '_________________________' }}<br>
                CNIC: {{ $manual['witness_1_cnic'] ?? '_________________________' }}
            </td>
            <td style="padding: 8px;">
                <strong>Witness 2:</strong><br>
                Name: {{ $manual['witness_2_name'] ?? '_________________________' }}<br>
                CNIC: {{ $manual['witness_2_cnic'] ?? '_________________________' }}
            </td>
        </tr>
    </table>
@endsection
