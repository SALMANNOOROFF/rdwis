<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pay Slip #{{ $order->sor_id }} - {{ $order->sor_empnamecomp }}</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Rajdhani:wght@600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Inter', sans-serif;
      background: #f8fafc;
      color: #1e293b;
      line-height: 1.4;
      font-size: 13px;
      padding: 24px;
    }
    .slip-container {
      max-width: 820px;
      margin: 0 auto;
      background: #ffffff;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      padding: 32px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .print-toolbar {
      max-width: 820px;
      margin: 0 auto 16px auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .btn-print {
      background: #0284c7;
      color: #ffffff;
      border: none;
      padding: 8px 18px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 13px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: background 0.2s;
      text-decoration: none;
    }
    .btn-print:hover {
      background: #0369a1;
    }
    .btn-close-slip {
      background: #f1f5f9;
      color: #475569;
      border: 1px solid #cbd5e1;
      padding: 8px 16px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 13px;
      cursor: pointer;
      text-decoration: none;
    }
    .btn-close-slip:hover {
      background: #e2e8f0;
    }
    .header-table {
      width: 100%;
      border-bottom: 2px solid #0f172a;
      padding-bottom: 12px;
      margin-bottom: 16px;
    }
    .header-title {
      font-family: 'Rajdhani', sans-serif;
      font-size: 24px;
      font-weight: 700;
      letter-spacing: 1px;
      color: #0f172a;
      text-transform: uppercase;
    }
    .header-subtitle {
      font-size: 11px;
      color: #64748b;
      font-weight: 500;
    }
    .header-date {
      text-align: right;
      font-size: 12px;
      color: #475569;
      font-weight: 600;
    }
    .section-title {
      font-family: 'Rajdhani', sans-serif;
      font-size: 14px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #0f172a;
      border-bottom: 1px solid #e2e8f0;
      padding-bottom: 4px;
      margin-bottom: 10px;
    }
    .demo-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      column-gap: 24px;
      row-gap: 6px;
      margin-bottom: 20px;
      font-size: 12.5px;
    }
    .demo-row {
      display: flex;
      justify-content: space-between;
      border-bottom: 1px dashed #e2e8f0;
      padding-bottom: 3px;
    }
    .demo-label {
      color: #64748b;
      font-weight: 600;
    }
    .demo-value {
      color: #0f172a;
      font-weight: 600;
      text-align: right;
    }
    .financial-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 20px;
    }
    .fin-box {
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      overflow: hidden;
    }
    .fin-box-header {
      background: #f8fafc;
      font-weight: 700;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 6px 12px;
      border-bottom: 1px solid #e2e8f0;
    }
    .fin-box-header.earnings {
      color: #0369a1;
      border-left: 3px solid #0284c7;
    }
    .fin-box-header.deductions {
      color: #b91c1c;
      border-left: 3px solid #dc2626;
    }
    .fin-table {
      width: 100%;
      border-collapse: collapse;
    }
    .fin-table td {
      padding: 6px 12px;
      border-bottom: 1px solid #f1f5f9;
      font-size: 12px;
    }
    .fin-table td.amount {
      text-align: right;
      font-weight: 600;
      font-family: monospace;
      font-size: 12.5px;
    }
    .fin-table tr.total-row td {
      border-top: 2px solid #cbd5e1;
      border-bottom: none;
      font-weight: 700;
      background: #f8fafc;
      font-size: 12.5px;
    }
    .net-box {
      background: #f0fdf4;
      border: 1px solid #86efac;
      border-radius: 6px;
      padding: 12px 18px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .net-label {
      font-size: 14px;
      font-weight: 700;
      color: #166534;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .net-amount {
      font-size: 20px;
      font-weight: 800;
      color: #15803d;
      font-family: 'Rajdhani', monospace, sans-serif;
    }
    .payment-details {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 6px;
      padding: 10px 14px;
      margin-bottom: 30px;
      font-size: 12px;
    }
    .signature-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 24px;
      margin-top: 40px;
      margin-bottom: 24px;
      text-align: center;
    }
    .sig-line {
      border-top: 1px solid #475569;
      padding-top: 6px;
      font-size: 11.5px;
      font-weight: 600;
      color: #334155;
      text-transform: uppercase;
    }
    .footer-note {
      text-align: center;
      font-size: 10.5px;
      color: #64748b;
      border-top: 1px dashed #e2e8f0;
      padding-top: 12px;
      margin-top: 16px;
    }

    @media print {
      body {
        padding: 0;
        background: #ffffff;
      }
      .print-toolbar {
        display: none !important;
      }
      .slip-container {
        border: none;
        box-shadow: none;
        padding: 0;
        max-width: 100%;
      }
      @page {
        size: A4 portrait;
        margin: 15mm;
      }
    }
  </style>
</head>
<body>

  <div class="print-toolbar">
    <div>
      <span style="font-weight: 700; font-size: 14px; color: #334155;">Salary Slip Preview</span>
      <span style="color: #64748b; font-size: 12px; margin-left: 8px;">(Order #{{ $order->sor_id }})</span>
    </div>
    <div style="display: flex; gap: 8px;">
      <button onclick="window.print()" class="btn-print">
        <i class="fas fa-print"></i> Print Pay Slip
      </button>
      <button onclick="window.close()" class="btn-close-slip">
        Close Window
      </button>
    </div>
  </div>

  <div class="slip-container">
    {{-- Header --}}
    <table class="header-table">
      <tr>
        <td>
          <div class="header-title">M/S MTSS PAY SLIP</div>
          <div class="header-subtitle">Naval Research and Development Institute (NRDI) &bull; Karachi</div>
        </td>
        <td class="header-date">
          <div>Printed on {{ now()->format('d M y') }}</div>
          <div style="font-size: 11px; color: #94a3b8; font-weight: 500;">Order Ref #{{ $order->sor_id }}</div>
        </td>
      </tr>
    </table>

    {{-- Employee Demographics --}}
    <div class="section-title">Employee Information</div>
    <div class="demo-grid">
      <div class="demo-row">
        <span class="demo-label">Employee ID:</span>
        <span class="demo-value">{{ $order->sor_emp_id }}</span>
      </div>
      <div class="demo-row">
        <span class="demo-label">Salary Month:</span>
        <span class="demo-value">{{ \Carbon\Carbon::parse($order->sor_month)->format('M Y') }}</span>
      </div>
      <div class="demo-row">
        <span class="demo-label">Employee Name:</span>
        <span class="demo-value">{{ $emp->emp_name ?? $order->sor_empnamecomp }}</span>
      </div>
      <div class="demo-row">
        <span class="demo-label">CNIC No:</span>
        <span class="demo-value">{{ $emp->emp_cnic ?? 'N/A' }}</span>
      </div>
      <div class="demo-row">
        <span class="demo-label">Designation:</span>
        <span class="demo-value">{{ $contract->ctr_jobtitle ?? ($emp->emp_title ?? 'N/A') }}</span>
      </div>
      <div class="demo-row">
        <span class="demo-label">Department / Unit:</span>
        <span class="demo-value">{{ $unit->unt_namesh ?? 'NRDI' }}</span>
      </div>
      <div class="demo-row">
        <span class="demo-label">Date of Joining:</span>
        <span class="demo-value">{{ $emp && $emp->emp_joindt ? \Carbon\Carbon::parse($emp->emp_joindt)->format('d-M-Y') : 'N/A' }}</span>
      </div>
      <div class="demo-row">
        <span class="demo-label">Employment Type:</span>
        <span class="demo-value">{{ $contractTypeStr }}</span>
      </div>
      <div class="demo-row">
        <span class="demo-label">Next of Kin:</span>
        <span class="demo-value">{{ $empExt->emp_nokname ?? 'N/A' }}</span>
      </div>
      <div class="demo-row">
        <span class="demo-label">Order Status:</span>
        <span class="demo-value" style="color: {{ $order->sor_status === 'Fulfilled' ? '#15803d' : ($order->sor_status === 'Approved' ? '#0284c7' : '#d97706') }};">
          {{ $order->sor_status }}
        </span>
      </div>
    </div>

    {{-- Financial Breakdown (Earnings & Deductions) --}}
    <div class="section-title">Salary Computation</div>
    <div class="financial-grid">
      {{-- Earnings Box --}}
      <div class="fin-box">
        <div class="fin-box-header earnings">Earnings</div>
        <table class="fin-table">
          <tr>
            <td>Base / Contract Salary</td>
            <td class="amount">Rs. {{ number_format($earnings['base']) }}</td>
          </tr>
          <tr>
            <td>Arrears</td>
            <td class="amount">Rs. {{ number_format($earnings['arrears']) }}</td>
          </tr>
          <tr>
            <td>Overwork / Allowance</td>
            <td class="amount">Rs. {{ number_format($earnings['overwork']) }}</td>
          </tr>
          @if($earnings['award'] > 0)
          <tr>
            <td>Award</td>
            <td class="amount">Rs. {{ number_format($earnings['award']) }}</td>
          </tr>
          @endif
          <tr class="total-row">
            <td>Gross Earnings</td>
            <td class="amount" style="color: #0369a1;">Rs. {{ number_format($earnings['total']) }}</td>
          </tr>
        </table>
      </div>

      {{-- Deductions Box --}}
      <div class="fin-box">
        <div class="fin-box-header deductions">Deductions</div>
        <table class="fin-table">
          <tr>
            <td>Absent / Leave Without Pay (Underwork)</td>
            <td class="amount">Rs. {{ number_format($deductions['underwork']) }}</td>
          </tr>
          <tr>
            <td>Dues / Loans</td>
            <td class="amount">Rs. {{ number_format($deductions['dues']) }}</td>
          </tr>
          <tr>
            <td>Withheld / Income Tax</td>
            <td class="amount">Rs. {{ number_format($deductions['withheld']) }}</td>
          </tr>
          @if($deductions['penalty'] > 0)
          <tr>
            <td>Penalty</td>
            <td class="amount">Rs. {{ number_format($deductions['penalty']) }}</td>
          </tr>
          @endif
          <tr class="total-row">
            <td>Total Deductions</td>
            <td class="amount" style="color: #b91c1c;">Rs. {{ number_format($deductions['total']) }}</td>
          </tr>
        </table>
      </div>
    </div>

    {{-- Net Payable Box --}}
    <div class="net-box">
      <div class="net-label">
        <i class="fas fa-check-circle mr-1"></i> Net Salary Payable
      </div>
      <div class="net-amount">
        Rs. {{ number_format($netPayable) }}
      </div>
    </div>

    {{-- Bank / Disbursement Details --}}
    <div class="payment-details">
      <div style="font-weight: 700; color: #334155; margin-bottom: 2px;">
        <i class="fas fa-university mr-1 text-secondary"></i> Disbursement Method:
      </div>
      <div style="color: #0f172a; font-weight: 600;">
        {{ $bankString }}
      </div>
      @if($order->sor_remarks)
      <div style="margin-top: 6px; color: #64748b; font-size: 11px;">
        <strong>Remarks:</strong> {{ $order->sor_remarks }}
      </div>
      @endif
    </div>

    {{-- Signatures --}}
    <div class="signature-grid">
      <div>
        <div class="sig-line">Prepared By (Admin)</div>
      </div>
      <div>
        <div class="sig-line">Checked By (Division HR)</div>
      </div>
      <div>
        <div class="sig-line">Approved By (Finance)</div>
      </div>
    </div>

    {{-- Footer --}}
    <div class="footer-note">
      For queries please contact Service Desk: Ground Floor, Bahria Complex I, MT Khan Road, Karachi.
    </div>
  </div>

</body>
</html>
