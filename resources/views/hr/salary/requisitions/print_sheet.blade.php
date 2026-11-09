<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Salary Requisitions - {{ $divisionName }} ({{ $displayMonth }})</title>
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
    .header-title {
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 4px;
      letter-spacing: -0.2px;
    }
    .header-sub {
      font-size: 12px;
      color: #222;
      margin-bottom: 16px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th {
      font-size: 12px;
      font-weight: bold;
      text-align: left;
      padding: 6px 8px;
      border-bottom: 1.5px solid #000;
      white-space: nowrap;
    }
    td {
      padding: 5px 8px;
      vertical-align: top;
      font-size: 12px;
      line-height: 1.35;
    }
    .group-header td {
      font-size: 12px;
      font-weight: bold;
      padding-top: 14px;
      padding-bottom: 6px;
      border-top: 1px solid #ccc;
    }
    .group-header:first-of-type td {
      border-top: none;
    }
    .item-row td {
      padding-bottom: 1px;
    }
    .item-sub-row td {
      padding-top: 0;
      padding-bottom: 7px;
      color: #222;
      border-bottom: 0.5px solid #e5e5e5;
    }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-mono { font-family: 'Courier New', Courier, monospace; }
    .font-bold { font-weight: 700; }

    .signatures {
      margin-top: 40px;
      display: flex;
      justify-content: flex-end;
      page-break-inside: avoid;
    }
    .sig-box {
      width: 250px;
      text-align: center;
      padding-top: 45px;
      border-top: 1.5px solid #000;
      font-weight: 700;
      font-size: 12px;
    }

    .no-print {
      margin-bottom: 16px;
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

  {{-- Print toolbar for browser view --}}
  <div class="no-print">
    <button class="btn-print" onclick="window.print()">
      🖨️ Print Requisition Sheet
    </button>
    <button class="btn-close-tab" onclick="window.close()">
      Close
    </button>
    <span style="font-size: 12px; color: #64748b;">
      Showing {{ $totalCount }} requisitions | Total Payable: Rs. {{ number_format($totalPayable) }}
    </span>
  </div>

  {{-- Document Header matching Image 1 --}}
  <div class="header-title">
    Contract Employees Salary Requisitions for {{ $displayMonth }} - {{ $divisionName }}
  </div>
  <div class="header-sub">
    {{ $totalCount }} employees from {{ $divisionName }} may please be paid salaries of amount Rs {{ number_format($totalPayable) }}
  </div>

  {{-- Main Table --}}
  <table>
    <thead>
      <tr>
        <th style="width: 80px;">Req.</th>
        <th style="width: 220px;">Employee</th>
        <th style="width: 90px;">Project</th>
        <th style="width: 120px;" class="text-right">Contract Salary</th>
        <th style="width: 120px;" class="text-right">Payable</th>
        <th>Meezan Account Details</th>
        <th style="width: 160px;">Remarks</th>
      </tr>
    </thead>
    <tbody>
      @forelse($grouped as $projectCode => $items)
        {{-- Group Header Row --}}
        <tr class="group-header">
          <td>{{ $projectCode }}</td>
          <td colspan="3" class="font-bold">{{ $items->count() }} employees</td>
          <td class="text-right font-bold font-mono">{{ number_format($items->sum('srq_salary')) }}</td>
          <td colspan="2"></td>
        </tr>

        {{-- Group Requisition Rows --}}
        @foreach($items as $r)
          <tr class="item-row">
            <td class="font-bold">{{ $r->srq_id }}</td>
            <td class="font-mono">{{ $r->srq_emp_id }}</td>
            <td>{{ $r->eff_hed_code ?: '-' }}</td>
            <td class="text-right font-mono">{{ number_format($r->srq_ctrsalary ?: $r->srq_salary) }}</td>
            <td class="text-right font-bold font-mono">{{ number_format($r->srq_salary) }}</td>
            <td class="font-mono">{{ $r->srq_bnkaccdetail ?: '-' }}</td>
            <td>{{ $r->srq_remarks }}</td>
          </tr>
          <tr class="item-sub-row">
            <td style="color: #555;">{{ \Carbon\Carbon::parse($r->srq_month)->format('d M y') }}</td>
            <td class="font-bold">{{ $r->emp_name ?: ($r->srq_empnamecomp ?: '-') }}</td>
            <td colspan="2"></td>
            <td style="color: #555;">{{ $r->eff_hed_code }}</td>
            <td>{{ $r->srq_bnkacctitle ?: ($r->emp_name ?: '-') }}</td>
            <td>{{ $r->srq_remarks2 }}</td>
          </tr>
        @endforeach
      @empty
        <tr>
          <td colspan="7" class="text-center" style="padding: 40px; color: #888;">
            No requisitions found for the selected criteria.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- Signatures --}}
  <div class="signatures">
    <div class="sig-box">
    Director {{ $divisionName }}
    </div>
  </div>

</body>
</html>
