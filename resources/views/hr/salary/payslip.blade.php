<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pay Slip - {{ $emp->emp_name ?? 'Employee' }} ({{ \Carbon\Carbon::parse($month)->format('M Y') }})</title>
  <style>
    @page {
      size: A4 portrait;
      margin: 15mm 15mm;
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      color: #000;
      background: #fff;
      margin: 0;
      padding: 20px;
    }
    .payslip-container {
      max-width: 760px;
      margin: 0 auto;
    }
    .header-table {
      width: 100%;
      margin-bottom: 25px;
    }
    .main-title {
      font-size: 14px;
      font-weight: bold;
      letter-spacing: 0.5px;
      text-align: center;
    }
    .print-date {
      font-size: 12px;
      font-style: italic;
      text-align: right;
      color: #333;
    }
    .info-grid {
      width: 100%;
      margin-bottom: 22px;
      border-collapse: collapse;
    }
    .info-grid td {
      padding: 4px 6px;
      vertical-align: top;
      font-size: 12px;
      line-height: 1.4;
    }
    .info-label {
      width: 150px;
      color: #111;
    }
    .info-val {
      font-weight: 600;
      color: #000;
    }

    .table-section {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 18px;
      border: 1px solid #333;
    }
    .table-section th {
      background: #f8fafc;
      border: 1px solid #333;
      padding: 6px 10px;
      font-weight: bold;
      font-size: 12px;
      text-align: left;
    }
    .table-section td {
      border: 1px solid #333;
      padding: 6px 10px;
      font-size: 12px;
    }
    .table-section .total-row td {
      font-weight: bold;
      background: #fafafa;
    }

    .net-box {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
      border: 1.5px solid #000;
    }
    .net-box td {
      padding: 8px 12px;
      font-size: 12px;
      font-weight: bold;
    }

    .footer-note {
      font-size: 12px;
      margin-top: 15px;
      line-height: 1.4;
    }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .font-mono { font-family: 'Courier New', Courier, monospace; }

    .no-print {
      margin-bottom: 20px;
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .btn-print {
      background: #0f172a;
      color: #fff;
      border: none;
      padding: 8px 18px;
      font-weight: bold;
      font-size: 13px;
      border-radius: 5px;
      cursor: pointer;
    }
    .btn-close-tab {
      background: #e2e8f0;
      color: #334155;
      border: 1px solid #cbd5e1;
      padding: 8px 14px;
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

  <div class="payslip-container">
    {{-- Browser Toolbar --}}
    <div class="no-print">
      <button class="btn-print" onclick="window.print()">
        🖨️ Print Pay Slip
      </button>
      <button class="btn-close-tab" onclick="window.close()">
        Close
      </button>
    </div>

    {{-- Header matching Image 2 --}}
    <table class="header-table">
      <tr>
        <td style="width: 25%;"></td>
        <td class="main-title" style="width: 50%;">
          M/S MTSS PAY SLIP
        </td>
        <td class="print-date" style="width: 25%;">
          Printed on {{ now()->format('d M y') }}
        </td>
      </tr>
    </table>

    {{-- Employee Particulars Grid --}}
    <table class="info-grid">
      <tr>
        <td class="info-label">Employee ID :</td>
        <td class="info-val font-mono">{{ $emp->emp_id ?? '-' }}</td>
        <td class="info-label" style="padding-left: 20px;">Designation :</td>
        <td class="info-val">{{ $contract->ctr_jobtitle ?? ($emp->emp_title ?? ($emp->emp_rank ?? 'Employee')) }}</td>
      </tr>
      <tr>
        <td class="info-label">Employee Name :</td>
        <td class="info-val">{{ $emp->emp_name ?? '-' }}</td>
        <td class="info-label" style="padding-left: 20px;">Department :</td>
        <td class="info-val">{{ $emp->unt_name ?? 'NRDI' }}</td>
      </tr>
      <tr>
        <td class="info-label">CNIC No :</td>
        <td class="info-val font-mono">{{ $emp->emp_cnic ?? '-' }}</td>
        <td class="info-label" style="padding-left: 20px;">NTN No :</td>
        <td class="info-val">-</td>
      </tr>
      <tr>
        <td class="info-label">Salary for Month :</td>
        <td class="info-val">{{ \Carbon\Carbon::parse($month)->format('M Y') }}</td>
        <td class="info-label" style="padding-left: 20px;">Date of Joining :</td>
        <td class="info-val">{{ $emp->emp_joindt ? \Carbon\Carbon::parse($emp->emp_joindt)->format('d M y') : '-' }}</td>
      </tr>
      <tr>
        <td class="info-label">Employment Type :</td>
        <td class="info-val">Contract (Full Time)</td>
        <td class="info-label" style="padding-left: 20px;">Next of Kin :</td>
        <td class="info-val">{{ $emp->emp_nokname ?? '-' }}</td>
      </tr>
    </table>

    {{-- Earnings Table --}}
    <table class="table-section">
      <thead>
        <tr>
          <th style="width: 45%;">Earnings</th>
          <th style="width: 25%;" class="text-right">Amount</th>
          <th style="width: 30%;">Remarks</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Base Salary</td>
          <td class="text-right font-mono">{{ number_format($baseSalary) }}</td>
          <td></td>
        </tr>
        <tr>
          <td>Arrears</td>
          <td class="text-right font-mono">{{ number_format($arrears) }}</td>
          <td></td>
        </tr>
        <tr>
          <td>Advance Salary</td>
          <td class="text-right font-mono">0</td>
          <td></td>
        </tr>
        <tr class="total-row">
          <td>Gross Salary</td>
          <td class="text-right font-mono">{{ number_format($gross) }}</td>
          <td></td>
        </tr>
      </tbody>
    </table>

    {{-- Deductions Table --}}
    <table class="table-section">
      <thead>
        <tr>
          <th style="width: 45%;">Deductions</th>
          <th style="width: 25%;" class="text-right">Amount</th>
          <th style="width: 30%;">Remarks</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Income Tax</td>
          <td class="text-right font-mono">{{ number_format($tax) }}</td>
          <td></td>
        </tr>
        <tr>
          <td>Absent / Leave without pay</td>
          <td class="text-right font-mono">{{ number_format($unpaid) }}</td>
          <td></td>
        </tr>
        <tr>
          <td>Dues</td>
          <td class="text-right font-mono">{{ number_format($dues) }}</td>
          <td></td>
        </tr>
        <tr class="total-row">
          <td>Total Deductions</td>
          <td class="text-right font-mono">{{ number_format($totalDeductions) }}</td>
          <td></td>
        </tr>
      </tbody>
    </table>

    {{-- Net Salary Box --}}
    <table class="net-box">
      <tr>
        <td style="width: 45%;">Net Salary</td>
        <td style="width: 25%;" class="text-right font-mono">{{ number_format($salary) }}</td>
        <td style="width: 30%;"></td>
      </tr>
    </table>

    {{-- Account deposit note --}}
    <div class="footer-note">
      Deposited in account No. <strong>{{ $bankAcc ?: '-' }}</strong>, Meezan Bank.
    </div>
  </div>

</body>
</html>
