<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Salaries of Contract Employees - {{ $displayMonth }}</title>
  <style>
    @page {
      size: A4 landscape;
      margin: 12mm 10mm;
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      color: #000;
      background: #fff;
      margin: 0;
      padding: 15px;
    }
    .header-block {
      text-align: center;
      margin-bottom: 18px;
    }
    .header-title {
      font-size: 14px;
      font-weight: bold;
      letter-spacing: 0.3px;
      text-transform: uppercase;
      margin-bottom: 3px;
    }
    .header-month {
      font-size: 13px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 5px;
    }
    th {
      font-size: 12px;
      font-weight: bold;
      text-align: left;
      padding: 6px 6px;
      border-bottom: 1.5px solid #000;
      white-space: nowrap;
      vertical-align: bottom;
    }
    td {
      padding: 4px 6px;
      vertical-align: top;
      font-size: 12px;
      line-height: 1.35;
    }
    .item-row td {
      padding-bottom: 1px;
    }
    .item-sub-row td {
      padding-top: 0;
      padding-bottom: 6px;
      color: #333;
      border-bottom: 0.5px solid #e0e0e0;
    }

    .summary-row td {
      border-top: 1.5px solid #000;
      border-bottom: 1.5px solid #000;
      padding: 8px 6px;
      font-size: 12px;
      font-weight: bold;
    }

    .signatures {
      margin-top: 55px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      page-break-inside: avoid;
      padding: 0 40px;
    }
    .sig-block {
      width: 220px;
      text-align: center;
      border-top: 1.5px solid #000;
      padding-top: 8px;
      font-weight: 700;
      font-size: 12px;
    }

    .print-footer {
      margin-top: 35px;
      display: flex;
      justify-content: flex-end;
      font-size: 11px;
      color: #555;
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-mono { font-family: 'Courier New', Courier, monospace; }
    .font-bold { font-weight: 700; }

    .no-print {
      margin-bottom: 18px;
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .btn-print {
      background: #0f172a;
      color: #fff;
      border: none;
      padding: 7px 16px;
      font-weight: bold;
      font-size: 13px;
      border-radius: 5px;
      cursor: pointer;
    }
    .btn-close-tab {
      background: #e2e8f0;
      color: #334155;
      border: 1px solid #cbd5e1;
      padding: 7px 14px;
      font-weight: bold;
      font-size: 13px;
      border-radius: 5px;
      cursor: pointer;
    }

    @media print {
      .no-print { display: none !important; }
      body { padding: 0; }
    }
  </style>
</head>
<body>

  {{-- Browser Print Toolbar --}}
  <div class="no-print">
    <a href="{{ route('divhr.salary.orders.export_word', request()->query()) }}" class="btn-print" style="text-decoration: none; background: #16a34a; display: inline-flex; align-items: center; gap: 6px;">
      💾 Save as Word Doc (.doc)
    </a>
    <button class="btn-print" onclick="window.print()">
      🖨️ Print Orders Sheet
    </button>
    <button class="btn-close-tab" onclick="window.close()">
      Close
    </button>
    <span style="font-size: 12px; color: #64748b;">
      Showing {{ $totalCount }} salary orders | Total Salary: Rs. {{ number_format($totalPayable) }}
    </span>
  </div>

  {{-- Header matching Images 3 & 4 --}}
  <div class="header-block">
    <div class="header-title">
      SALARIES OF CONTRACT EMPLOYEES AT R&D WING (NRDI)
    </div>
    <div class="header-month">
      {{ $displayMonth }}
    </div>
  </div>

  {{-- Main Consolidated Table --}}
  <table>
    <thead>
      <tr>
        <th style="width: 35px;">S No</th>
        <th style="width: 140px;">Employee ID<br>CNIC</th>
        <th style="width: 180px;">Name<br>Grade</th>
        <th style="width: 160px;">Job Title</th>
        <th style="width: 110px;">Department</th>
        <th style="width: 100px;">Cont. From<br>Cont. To</th>
        <th style="width: 110px;" class="text-right">Salary<br>Month</th>
        <th style="width: 110px;">Paid from</th>
        <th>Meezan Bank Account<br>Account Title</th>
        <th style="width: 140px;">Remarks</th>
      </tr>
    </thead>
    <tbody>
      @forelse($orders as $idx => $o)
        <tr class="item-row">
          <td class="text-center font-bold">{{ $idx + 1 }}</td>
          <td class="font-mono">{{ $o->sor_emp_id }}</td>
          <td class="font-bold">{{ $o->emp_name ?: ($o->sor_empnamecomp ?: '-') }}</td>
          <td>{{ $o->contract->ctr_jobtitle ?? ($o->emp_title ?? 'Employee') }}</td>
          <td>{{ $o->unt_namesh ?? ($o->unt_name ?? 'NRDI') }}</td>
          <td class="font-mono">{{ $o->contract?->ctr_startdt ? \Carbon\Carbon::parse($o->contract->ctr_startdt)->format('d M y') : '-' }}</td>
          <td class="text-right font-bold font-mono">{{ number_format($o->sor_salary) }}</td>
          <td class="font-bold">{{ $o->eff_hed_code ?: '-' }}</td>
          <td class="font-mono">{{ $o->sor_bnkaccdetail ?: '-' }}</td>
          <td>{{ $o->sor_remarks }}</td>
        </tr>
        <tr class="item-sub-row">
          <td></td>
          <td class="font-mono" style="color: #444;">{{ $o->emp_cnic ?: '-' }}</td>
          <td style="color: #444;">{{ $o->contract->ctr_grade ?? ($o->emp_rank ?? '-') }}</td>
          <td></td>
          <td></td>
          <td class="font-mono" style="color: #444;">{{ $o->contract?->ctr_enddt ? \Carbon\Carbon::parse($o->contract->ctr_enddt)->format('d M y') : '-' }}</td>
          <td class="text-right font-mono" style="color: #444;">{{ \Carbon\Carbon::parse($o->sor_month)->format('M y') }}</td>
          <td></td>
          <td style="color: #444;">{{ $o->sor_bnkacctitle ?: ($o->emp_name ?: '-') }}</td>
          <td>{{ $o->sor_remarks2 }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="10" class="text-center" style="padding: 40px; color: #888;">
            No salary orders found for the selected criteria.
          </td>
        </tr>
      @endforelse

      {{-- Summary Row matching Image 4 --}}
      @if($totalCount > 0)
        <tr class="summary-row">
          <td colspan="5">
            Total {{ $totalCount }} salaries
          </td>
          <td colspan="5" class="text-right">
            Total Salary Rs {{ number_format($totalPayable) }}/-
          </td>
        </tr>
      @endif
    </tbody>
  </table>

  {{-- Signatures matching Image 4 --}}
  <div class="signatures">
    <div class="sig-block">
      SO Finance
    </div>
    <div class="sig-block">
      Director Finance
    </div>
    <div class="sig-block">
      Managing Director RDW
    </div>
  </div>

  {{-- Document Footer --}}
  <div class="print-footer">
    Printed on {{ now()->format('d M y H:i') }}
  </div>

</body>
</html>
