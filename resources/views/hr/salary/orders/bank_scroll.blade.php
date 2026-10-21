{{-- resources/views/hr/salary/orders/bank_scroll.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bank Batch Payment Scroll - {{ strtoupper(\Carbon\Carbon::parse($monthDate)->translatedFormat('F Y')) }}</title>
  
  {{-- Fonts & Icons --}}
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Rajdhani:wght@600;700&family=JetBrains+Mono:wght@500;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">

  <style>
    body {
      background-color: #f1f5f9;
      color: #0f172a;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      font-size: 13px;
      margin: 0;
      padding: 0;
    }

    .scroll-container {
      max-width: 1400px;
      margin: 24px auto;
      background: #ffffff;
      padding: 32px 40px;
      border-radius: 8px;
      box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.08);
    }

    .report-header {
      text-align: center;
      margin-bottom: 24px;
      border-bottom: 2px solid #0f172a;
      padding-bottom: 16px;
    }

    .report-title {
      font-family: 'Rajdhani', sans-serif;
      font-size: 24px;
      font-weight: 700;
      letter-spacing: 0.5px;
      color: #0f172a;
      margin: 0;
      text-transform: uppercase;
    }

    .report-subtitle {
      font-size: 15px;
      font-weight: 600;
      color: #334155;
      margin-top: 4px;
      letter-spacing: 1px;
      text-transform: uppercase;
    }

    .group-section {
      margin-bottom: 28px;
      page-break-inside: avoid;
    }

    .group-banner {
      background: #e2e8f0;
      color: #0f172a;
      font-weight: 700;
      font-size: 13px;
      padding: 8px 14px;
      border-radius: 4px 4px 0 0;
      border: 1px solid #cbd5e1;
      border-bottom: none;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .scroll-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      border: 1px solid #cbd5e1;
    }

    .scroll-table th {
      background: #f8fafc;
      color: #1e293b;
      font-weight: 700;
      text-transform: uppercase;
      font-size: 11px;
      letter-spacing: 0.3px;
      padding: 8px 10px;
      border: 1px solid #cbd5e1;
      text-align: left;
      vertical-align: middle;
    }

    .scroll-table td {
      padding: 6px 10px;
      border: 1px solid #cbd5e1;
      vertical-align: middle;
    }

    .scroll-table tbody tr:nth-child(even) {
      background-color: #f8fafc;
    }

    .scroll-table tbody tr:hover {
      background-color: #f1f5f9;
    }

    .group-footer-row td {
      background: #f1f5f9;
      font-weight: 700;
      border-top: 2px solid #94a3b8;
    }

    .grand-total-card {
      background: #f8fafc;
      border: 2px solid #0f172a;
      border-radius: 6px;
      padding: 14px 20px;
      margin-top: 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .signatures-section {
      margin-top: 60px;
      padding-top: 20px;
      display: flex;
      justify-content: space-between;
      page-break-inside: avoid;
    }

    .signature-box {
      width: 28%;
      text-align: center;
    }

    .signature-line {
      border-top: 1px solid #475569;
      margin-bottom: 6px;
    }

    .signature-title {
      font-weight: 700;
      font-size: 12px;
      color: #1e293b;
      text-transform: uppercase;
    }

    .mono {
      font-family: 'JetBrains Mono', monospace;
    }

    /* Print Styles */
    @media print {
      @page {
        size: A4 landscape;
        margin: 10mm 12mm;
      }

      body {
        background: #ffffff !important;
        font-size: 11px;
      }

      .no-print {
        display: none !important;
      }

      .scroll-container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
      }

      .report-header {
        border-bottom: 2px solid #000;
        margin-bottom: 16px;
        padding-bottom: 8px;
      }

      .group-banner {
        background: #e5e7eb !important;
        color: #000 !important;
        border: 1px solid #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .scroll-table, .scroll-table th, .scroll-table td {
        border: 1px solid #000 !important;
      }

      .scroll-table th {
        background: #f3f4f6 !important;
        color: #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }

      .signatures-section {
        margin-top: 50px;
      }

      .signature-line {
        border-top: 1px solid #000 !important;
      }
    }
  </style>
</head>
<body>

  {{-- Top Navigation / Filter Bar (No-Print) --}}
  <div class="no-print bg-dark py-2 px-4 shadow-sm" style="position: sticky; top: 0; z-index: 1000;">
    <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 12px;">
      <div class="d-flex align-items-center" style="gap: 12px;">
        <a href="{{ route('divhr.salary.orders.index', ['month' => $month, 'status' => $status]) }}" class="btn btn-sm btn-outline-light">
          <i class="fas fa-arrow-left mr-1"></i> Back to Orders
        </a>
        <span class="text-white font-weight-bold" style="font-family: 'Rajdhani', sans-serif; font-size: 16px;">
          <i class="fas fa-file-invoice text-warning mr-1"></i> Batch Payment Scroll (fin_salorders_u_doc2)
        </span>
      </div>

      {{-- Filters --}}
      <form method="GET" action="{{ route('divhr.salary.orders.bank_scroll') }}" class="d-flex align-items-center flex-wrap" style="gap: 8px;">
        <div class="d-flex align-items-center text-white small mr-2">
          <label class="mr-1 mb-0 font-weight-bold">Month:</label>
          <input type="month" name="month" value="{{ $month }}" class="form-control form-control-sm text-dark" style="width: 145px;" onchange="this.form.submit()">
        </div>

        <div class="d-flex align-items-center text-white small mr-2">
          <label class="mr-1 mb-0 font-weight-bold">Status:</label>
          <select name="status" class="form-control form-control-sm text-dark" style="width: 125px;" onchange="this.form.submit()">
            <option value="All" {{ $status === 'All' ? 'selected' : '' }}>All Statuses</option>
            <option value="Draft" {{ $status === 'Draft' ? 'selected' : '' }}>Draft</option>
            <option value="Open" {{ in_array($status, ['Open', 'Approved'], true) ? 'selected' : '' }}>Open (Approved)</option>
            <option value="Closed" {{ in_array($status, ['Closed', 'Fulfilled'], true) ? 'selected' : '' }}>Closed (Fulfilled)</option>
          </select>
        </div>

        <button type="button" onclick="window.print()" class="btn btn-sm btn-primary font-weight-bold shadow-sm">
          <i class="fas fa-print mr-1"></i> Print Scroll
        </button>
        <button type="button" onclick="exportToCsv()" class="btn btn-sm btn-success font-weight-bold shadow-sm">
          <i class="fas fa-file-excel mr-1"></i> Export CSV
        </button>
      </form>
    </div>
  </div>

  {{-- Scroll Paper Container --}}
  <div class="scroll-container">
    
    {{-- Document Header --}}
    <div class="report-header">
      <h1 class="report-title">SALARIES OF CONTRACT EMPLOYEES AT R&D WING (NRDI)</h1>
      <div class="report-subtitle">
        FOR THE MONTH OF {{ strtoupper(\Carbon\Carbon::parse($monthDate)->translatedFormat('F Y')) }}
        @if($status !== 'All')
          <span class="badge badge-secondary ml-2 no-print">{{ strtoupper($status) }}</span>
        @endif
      </div>
      <div class="d-flex justify-content-between text-muted small mt-2">
        <span>Printed on: <strong>{{ now()->format('d M y H:i') }}</strong></span>
        <span>Filter Status: <strong>{{ $status }}</strong></span>
        <span>Scope: <strong>Total {{ $totalPersons }} Persons</strong></span>
      </div>
    </div>

    @if($grouped->isEmpty())
      <div class="text-center py-5">
        <i class="fas fa-folder-open text-muted mb-3" style="font-size: 3rem;"></i>
        <h5 class="text-muted font-weight-bold">No Salary Orders Found</h5>
        <p class="text-muted small">No salary orders match month <strong>{{ $month }}</strong> with status <strong>{{ $status }}</strong>.</p>
      </div>
    @else
      {{-- Loop through Effective Head Groups --}}
      @foreach($grouped as $grp)
        <div class="group-section">
          {{-- Group Header Banner --}}
          <div class="group-banner">
            <span>
              <i class="fas fa-university mr-2"></i> {{ $grp['header_title'] }}
            </span>
            <span class="badge badge-light text-dark font-weight-bold px-2 py-1">
              {{ $grp['person_count'] }} Persons | Rs. {{ number_format($grp['total_salary']) }}/-
            </span>
          </div>

          {{-- Orders Table --}}
          <table class="scroll-table">
            <thead>
              <tr>
                <th style="width: 40px;" class="text-center">S.No</th>
                <th style="width: 140px;">Employee ID<br><span style="font-weight: 500; text-transform: none; color: #64748b;">CNIC</span></th>
                <th style="width: 220px;">Name<br><span style="font-weight: 500; text-transform: none; color: #64748b;">Job Title</span></th>
                <th style="width: 160px;">Department<br><span style="font-weight: 500; text-transform: none; color: #64748b;">Grade</span></th>
                <th style="width: 110px;">Cont. Start<br><span style="font-weight: 500; text-transform: none; color: #64748b;">Cont. End</span></th>
                <th>Meezan Bank Account<br><span style="font-weight: 500; text-transform: none; color: #64748b;">Account Title</span></th>
                <th style="width: 110px;" class="text-right">Net Salary<br><span style="font-weight: 500; text-transform: none; color: #64748b;">(PKR)</span></th>
                <th style="width: 160px;">Remarks</th>
              </tr>
            </thead>
            <tbody>
              @foreach($grp['orders'] as $idx => $ord)
                @php
                  $isCheque = str_contains(strtolower($ord->sor_bnkaccdetail ?? ''), 'cheque') || empty($ord->sor_bnkaccdetail);
                  $deptDisplay = ($ord->unt_namesh ?? 'Unit ' . $ord->sor_unt_id) . ($ord->hed_code ? ' (' . $ord->hed_code . ')' : '');
                @endphp
                <tr>
                  <td class="text-center font-weight-bold">{{ $idx + 1 }}</td>
                  <td>
                    <div class="font-weight-bold mono text-primary" style="font-size: 11px;">{{ $ord->sor_emp_id }}</div>
                    <div class="text-muted mono" style="font-size: 10px;">{{ $ord->emp_cnic ?: '-' }}</div>
                  </td>
                  <td>
                    <div class="font-weight-bold text-dark">{{ $ord->sor_empnamecomp }}</div>
                    <div class="small text-secondary">{{ $ord->ctr_jobtitle ?: '-' }}</div>
                  </td>
                  <td>
                    <div class="font-weight-bold" style="color: #334155;">{{ $deptDisplay }}</div>
                    <div class="small text-muted">{{ $ord->ctr_grade ?: '-' }}</div>
                  </td>
                  <td class="mono" style="font-size: 11px;">
                    <div>{{ $ord->emp_joindt ? \Carbon\Carbon::parse($ord->emp_joindt)->format('d M y') : '-' }}</div>
                    <div class="text-danger">{{ $ord->ctr_enddt ? \Carbon\Carbon::parse($ord->ctr_enddt)->format('d M y') : '-' }}</div>
                  </td>
                  <td>
                    @if($isCheque)
                      <span class="badge badge-secondary px-2 py-1"><i class="fas fa-money-check mr-1"></i> Pay by Cheque</span>
                      <div class="small font-weight-bold text-dark mt-1">{{ $ord->sor_bnkacctitle ?: $ord->sor_empnamecomp }}</div>
                    @else
                      <div class="font-weight-bold text-success mono" style="font-size: 12px;">
                        <i class="fas fa-university mr-1"></i> {{ $ord->sor_bnkaccdetail }}
                      </div>
                      <div class="small font-weight-bold text-dark">{{ $ord->sor_bnkacctitle ?: $ord->sor_empnamecomp }}</div>
                    @endif
                  </td>
                  <td class="text-right font-weight-bold mono" style="font-size: 13px; color: #166534;">
                    {{ number_format($ord->sor_salary) }}
                  </td>
                  <td class="small text-muted">
                    {{ $ord->sor_remarks ?: '-' }}
                    @if(!empty($ord->sor_remarks2))
                      <div class="text-info font-italic mt-1">{{ $ord->sor_remarks2 }}</div>
                    @endif
                  </td>
                </tr>
              @endforeach

              {{-- Group Footer Total --}}
              <tr class="group-footer-row">
                <td colspan="5" class="text-right text-uppercase">
                  Total for {{ $grp['effhed_code'] }} ({{ $grp['effunt_namesh'] }}):
                </td>
                <td class="font-weight-bold">
                  {{ $grp['person_count'] }} Persons
                </td>
                <td class="text-right font-weight-bold mono" style="font-size: 13px; color: #0f172a;">
                  {{ number_format($grp['total_salary']) }}/-
                </td>
                <td></td>
              </tr>
            </tbody>
          </table>
        </div>
      @endforeach

      {{-- Grand Summary Box --}}
      <div class="grand-total-card">
        <div>
          <div class="text-uppercase font-weight-bold" style="color: #475569; letter-spacing: 0.5px; font-size: 12px;">Grand Summary</div>
          <div class="h5 mb-0 font-weight-bold text-dark">
            Total {{ $totalPersons }} Employees Across {{ $grouped->count() }} Funding Accounts
          </div>
        </div>
        <div class="text-right">
          <div class="text-uppercase font-weight-bold" style="color: #475569; letter-spacing: 0.5px; font-size: 12px;">Total Payable Net Amount</div>
          <div class="h4 mb-0 font-weight-bold text-success mono">
            Rs. {{ number_format($grandTotalSalary) }}/-
          </div>
        </div>
      </div>

      {{-- Official Signature Blocks --}}
      <div class="signatures-section">
        <div class="signature-box">
          <div class="signature-line"></div>
          <div class="signature-title">Account Officer</div>
          <div class="small text-muted">Prepared & Audited By</div>
        </div>
        <div class="signature-box">
          <div class="signature-line"></div>
          <div class="signature-title">Director Finance</div>
          <div class="small text-muted">Verified & Recommended By</div>
        </div>
        <div class="signature-box">
          <div class="signature-line"></div>
          <div class="signature-title">Managing Director RDW</div>
          <div class="small text-muted">Final Approval Authority</div>
        </div>
      </div>
    @endif

  </div>

  <script>
    function exportToCsv() {
      const rows = [];
      const headers = ['S.No', 'Employee ID', 'CNIC', 'Name', 'Job Title', 'Department', 'Grade', 'Cont Start', 'Cont End', 'Effective Head', 'Effective Division', 'Bank Account', 'Account Title', 'Net Salary', 'Remarks'];
      rows.push(headers.join(','));

      @foreach($rows as $idx => $r)
        rows.push([
          "{{ $idx + 1 }}",
          "\"{{ $r->sor_emp_id }}\"",
          "\"{{ $r->emp_cnic }}\"",
          "\"{{ str_replace('\"', '\"\"', $r->sor_empnamecomp) }}\"",
          "\"{{ str_replace('\"', '\"\"', $r->ctr_jobtitle ?? '') }}\"",
          "\"{{ str_replace('\"', '\"\"', ($r->unt_namesh ?? '') . ($r->hed_code ? ' (' . $r->hed_code . ')' : '')) }}\"",
          "\"{{ $r->ctr_grade ?? '' }}\"",
          "\"{{ $r->emp_joindt ? \Carbon\Carbon::parse($r->emp_joindt)->format('Y-m-d') : '' }}\"",
          "\"{{ $r->ctr_enddt ? \Carbon\Carbon::parse($r->ctr_enddt)->format('Y-m-d') : '' }}\"",
          "\"{{ $r->effhed_code }}\"",
          "\"{{ $r->effunt_namesh }}\"",
          "\"{{ str_replace('\"', '\"\"', $r->sor_bnkaccdetail ?? '') }}\"",
          "\"{{ str_replace('\"', '\"\"', $r->sor_bnkacctitle ?? '') }}\"",
          "{{ (int)$r->sor_salary }}",
          "\"{{ str_replace('\"', '\"\"', $r->sor_remarks ?? '') }}\""
        ].join(','));
      @endforeach

      const csvContent = 'data:text/csv;charset=utf-8,' + rows.join('\n');
      const encodedUri = encodeURI(csvContent);
      const link = document.createElement('a');
      link.setAttribute('href', encodedUri);
      link.setAttribute('download', 'Bank_Payment_Scroll_{{ $month }}.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  </script>
</body>
</html>
