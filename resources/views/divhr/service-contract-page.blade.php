<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Service Contract - {{ $emp->emp_name ?? 'Employee' }}</title>
  
  {{-- Local Assets from Application --}}
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

  <style>
    /* Reset & Base Setup (Pure Vanilla CSS - No external CDN dependency) */
    *, *::before, *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      background-color: #e2e8f0;
      color: #0f172a;
      font-family: Arial, Helvetica, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Top Sticky Control Bar */
    .sc-navbar {
      position: sticky;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      background: #ffffff;
      border-bottom: 1.5px solid #cbd5e1;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
      padding: 10px 24px;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
    }
    .sc-nav-left, .sc-nav-center, .sc-nav-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    
    /* Buttons & Controls */
    .btn-back-profile {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      background: #f1f5f9;
      color: #334155;
      border: 1.5px solid #cbd5e1;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      text-decoration: none;
      transition: all 0.2s ease;
      cursor: pointer;
    }
    .btn-back-profile:hover {
      background: #e2e8f0;
      color: #0f172a;
      border-color: #94a3b8;
    }
    .btn-print-a4 {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 8px 18px;
      background: #0284c7;
      color: #ffffff;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .btn-print-a4:hover {
      background: #0369a1;
      transform: translateY(-1px);
      box-shadow: 0 4px 10px rgba(2, 132, 199, 0.45);
    }

    /* Jump Pills */
    .sc-jump-wrap {
      display: flex;
      align-items: center;
      gap: 6px;
      background: #f8fafc;
      padding: 4px 8px;
      border-radius: 8px;
      border: 1px solid #cbd5e1;
    }
    .sc-jump-label {
      font-size: 11px;
      font-weight: 800;
      color: #64748b;
      text-transform: uppercase;
      margin-right: 4px;
    }
    .btn-jump-page {
      padding: 5px 11px;
      font-size: 12px;
      font-weight: 700;
      background: #ffffff;
      color: #334155;
      border: 1px solid #cbd5e1;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.15s ease;
    }
    .btn-jump-page:hover {
      background: #0284c7;
      color: #ffffff;
      border-color: #0284c7;
    }

    /* Selector */
    .sc-select-box {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #f8fafc;
      border: 1.5px solid #cbd5e1;
      border-radius: 8px;
      padding: 4px 10px;
    }
    .sc-select-box label {
      font-size: 12px;
      font-weight: 800;
      color: #475569;
    }
    .sc-select-input {
      border: none;
      background: transparent;
      font-size: 12.5px;
      font-weight: 700;
      color: #0f172a;
      outline: none;
      cursor: pointer;
      padding: 4px 0;
    }

    /* Badge & Info */
    .sc-emp-badge {
      display: flex;
      align-items: center;
      gap: 8px;
      padding-left: 8px;
      border-left: 2px solid #cbd5e1;
    }
    .sc-emp-badge .sc-badge-title {
      font-size: 14px;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.2;
    }
    .sc-emp-badge .sc-badge-sub {
      font-size: 12px;
      font-weight: 600;
      color: #64748b;
    }

    /* Main Canvas & A4 Sheets */
    .sc-viewport {
      flex: 1;
      padding: 30px 15px 50px 15px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .sc-sheets-container {
      width: 100%;
      max-width: 210mm;
      margin: 0 auto;
    }
    .sc-a4-sheet {
      background: #ffffff;
      color: #000000;
      width: 210mm;
      min-height: 297mm;
      max-width: 100%;
      margin: 0 auto 32px auto;
      padding: 18mm 22mm 18mm 22mm;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
      border: 1px solid #cbd5e1;
      border-radius: 3px;
      position: relative;
      box-sizing: border-box;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12pt;
      line-height: 1.48;
    }

    /* Official Contract Typography */
    .sc-header-blue {
      text-align: center;
      color: #1d70b8;
      font-family: Arial, Helvetica, sans-serif;
      font-weight: bold;
      font-size: 14pt;
      letter-spacing: 0.8px;
      margin-bottom: 20px;
    }
    .sc-title {
      text-align: center;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 14pt;
      font-weight: bold;
      text-decoration: underline;
      text-underline-offset: 4px;
      margin-bottom: 24px;
      letter-spacing: 0.3px;
    }
    .sc-clause-heading {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 14pt;
      font-weight: bold;
      text-decoration: underline;
      text-underline-offset: 3px;
      display: inline;
    }
    .sc-clause-num {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 14pt;
      font-weight: bold;
      margin-right: 14px;
      display: inline;
    }
    .sc-footer {
      position: absolute;
      bottom: 14mm;
      left: 0;
      right: 0;
      text-align: center;
    }
    .sc-footer-num {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 11pt;
      color: #222222;
      margin-bottom: 3px;
    }
    .sc-footer-blue {
      color: #1d70b8;
      font-family: Arial, Helvetica, sans-serif;
      font-weight: bold;
      font-size: 14pt;
      letter-spacing: 0.8px;
    }

    /* Print Styles */
    @media print {
      @page {
        size: A4 portrait;
        margin: 12mm 16mm;
      }
      .no-print {
        display: none !important;
      }
      body {
        background: #ffffff !important;
        padding: 0 !important;
      }
      .sc-viewport {
        padding: 0 !important;
      }
      .sc-a4-sheet {
        box-shadow: none !important;
        border: none !important;
        width: 100% !important;
        min-height: 270mm !important;
        margin: 0 !important;
        padding: 0 0 16mm 0 !important;
        page-break-after: always !important;
        break-after: page !important;
      }
      .sc-a4-sheet:last-child {
        page-break-after: auto !important;
        break-after: auto !important;
      }
      .sc-header-blue, .sc-footer-blue {
        color: #1d70b8 !important;
      }
    }
  </style>
</head>
<body>

  {{-- TOP CONTROLS NAVBAR (No Print) --}}
  <header class="no-print sc-navbar">
    
    {{-- Left: Back to Profile & Badge --}}
    <div class="sc-nav-left">
      <a href="{{ route('divhr.employeedetail', $id) }}" class="btn-back-profile">
        <i class="fas fa-arrow-left"></i> Back to Profile
      </a>
      
      <div class="sc-emp-badge">
        <div>
          <div class="sc-badge-title">SERVICE CONTRACT</div>
          <div class="sc-badge-sub">{{ $emp->emp_name ?? 'Employee' }}</div>
        </div>
      </div>
    </div>

    {{-- Center: Page Shortcuts --}}
    <div class="sc-nav-center">
      <div class="sc-jump-wrap">
        <span class="sc-jump-label">Jump:</span>
        <button type="button" class="btn-jump-page" onclick="scScrollToPage(1)">Page 1</button>
        <button type="button" class="btn-jump-page" onclick="scScrollToPage(2)">Page 2</button>
        <button type="button" class="btn-jump-page" onclick="scScrollToPage(3)">Page 3</button>
        <button type="button" class="btn-jump-page" onclick="scScrollToPage(4)">Page 4</button>
      </div>
    </div>

    {{-- Right: Contract Switcher & Print Button --}}
    <div class="sc-nav-right">
      <div class="sc-select-box">
        <label for="pageContractSelector">Contract:</label>
        <select id="pageContractSelector" class="sc-select-input" onchange="switchPageContract(this.value)">
          @forelse(($contractsHistory ?? collect()) as $cIdx => $c)
            @php
              $sDate = !empty($c->ctr_startdt) ? \Carbon\Carbon::parse($c->ctr_startdt)->format('d M, Y') : '—';
              $eDate = !empty($c->ctr_enddt) ? \Carbon\Carbon::parse($c->ctr_enddt)->format('d M, Y') : '—';
              $cDesig = $c->ctr_jobtitle ?: 'Designation';
              $isSel = ($selectedContract && $selectedContract->ctr_id == $c->ctr_id);
            @endphp
            <option value="{{ $c->ctr_id }}" {{ $isSel ? 'selected' : '' }}>
              #{{ $cIdx + 1 }}: {{ $sDate }} – {{ $eDate }} ({{ $cDesig }})
            </option>
          @empty
            <option value="">No Contract Found</option>
          @endforelse
        </select>
      </div>

      <button type="button" onclick="window.print()" class="btn-print-a4">
        <i class="fas fa-print"></i> Print Contract (A4)
      </button>
    </div>

  </header>

  {{-- MAIN VIEWPORT & A4 SHEETS --}}
  <main class="sc-viewport">
    <div class="sc-sheets-container" id="serviceContractPrintArea">

      @php
        $c = $selectedContract;
        $empName = $emp->emp_name ?? '—';
        $empAddress = $empA?->emp_paddress ?: ($empA?->emp_taddress ?: ($emp->emp_address ?? '—'));
        
        $startFormatted = !empty($c?->ctr_startdt) ? \Carbon\Carbon::parse($c->ctr_startdt)->format('d M y') : '—';
        
        // Calculate contract tenure in months
        $tenureMonths = 12;
        if (!empty($c?->ctr_startdt) && !empty($c?->ctr_enddt)) {
            $tenureMonths = \App\Models\HrCtrCase::calculateMonths($c->ctr_startdt, $c->ctr_enddt);
        }
        $tenureFormatted = $tenureMonths . ' ' . \Illuminate\Support\Str::plural('month', $tenureMonths);

        $designation = $c?->ctr_jobtitle ?: ($emp->emp_title ?: 'Research Officer');
        $isPartTime = ($c?->ctr_type == 2 || (string)($c?->ctr_type ?? '') === 'Part Time');
        $empType = $isPartTime ? 'part time' : 'full time';

        $salaryNum = (float)($c?->ctr_salary ?? 0);
        $salaryFormatted = number_format($salaryNum) . '/-';

        // Convert salary to standard English words
        if (!function_exists('numberToWordsLocalHelper')) {
            function numberToWordsLocalHelper($n) {
                if ($n <= 0) return 'Pak Rupees Zero Only';
                $units = ['', 'One ', 'Two ', 'Three ', 'Four ', 'Five ', 'Six ', 'Seven ', 'Eight ', 'Nine ', 'Ten ', 'Eleven ', 'Twelve ', 'Thirteen ', 'Fourteen ', 'Fifteen ', 'Sixteen ', 'Seventeen ', 'Eighteen ', 'Nineteen '];
                $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
                
                $words = function($num) use (&$words, $units, $tens) {
                    $str = '';
                    if ($num >= 1000000) {
                        $str .= $words(intval($num / 1000000)) . ' Million ';
                        $num %= 1000000;
                    }
                    if ($num >= 1000) {
                        $str .= $words(intval($num / 1000)) . ' Thousand ';
                        $num %= 1000;
                    }
                    if ($num >= 100) {
                        $str .= $words(intval($num / 100)) . ' Hundred ';
                        $num %= 100;
                    }
                    if ($num > 0) {
                        if ($num < 20) {
                            $str .= $units[$num];
                        } else {
                            $str .= $tens[intval($num / 10)] . ($num % 10 > 0 ? ' ' . $units[$num % 10] : '');
                        }
                    }
                    return trim($str);
                };
                return 'Pak Rupees ' . preg_replace('/\s+/', ' ', $words(intval($n))) . ' Only';
            }
        }

        $salaryWords = numberToWordsLocalHelper($salaryNum);
      @endphp

      {{-- ============================================================= --}}
      {{-- PAGE 1                                                        --}}
      {{-- ============================================================= --}}
      <div id="scPage1" class="sc-a4-sheet">
        {{-- Page 1 Header --}}
        <div class="sc-header-blue">CONFIDENTIAL</div>

        {{-- Page 1 Title --}}
        <div class="sc-title">SERVICE CONTRACT</div>

        <p style="margin-bottom: 16px;">The undermentioned parties are:</p>

        {{-- Party I --}}
        <div style="margin-bottom: 18px; margin-left: 45px;">
          <div style="font-weight: bold; margin-bottom: 2px;">Party I</div>
          <div style="font-weight: bold; margin-bottom: 2px;">M/s Maritime Technical &nbsp;Support Services (MTSS) Private Limited</div>
          <div>With their Head Office at House No 462, Major Road -4 , Sector D-12/4, Islamabad</div>
        </div>

        {{-- Party II --}}
        <div style="margin-bottom: 18px; margin-left: 45px;">
          <div style="font-weight: bold; margin-bottom: 2px;">Party II</div>
          <div style="margin-bottom: 2px;">
            <span style="font-weight: bold;">Name: &nbsp;&nbsp;&nbsp;</span>
            <span style="font-weight: bold;">{{ $empName }}</span>
          </div>
          <div style="margin-bottom: 2px;">
            <span>Address: </span>
            <span>{{ $empAddress }}</span>
          </div>
          <div>(Hereinafter called as the Employee)</div>
        </div>

        {{-- Party III --}}
        <div style="margin-bottom: 24px; margin-left: 45px;">
          <div style="font-weight: bold; margin-bottom: 2px;">Party III</div>
          <div style="font-weight: bold; margin-bottom: 2px;">Naval Research &amp; Developement Institute (NRDI)</div>
          <div>(Hereinafter called as R&amp;D Wing)</div>
        </div>

        {{-- 1. CONTRACT INTERPRETATION --}}
        <div style="margin-bottom: 16px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">1.</span>
            <span class="sc-clause-heading">CONTRACT INTERPRETATION</span>
          </div>
          <div style="margin-left: 32px;">
            The above-mentioned parties have entered into agreement regarding the provision of services of the Employee to R&amp;D Wing by the Employer. The selection and appointment of the Employee will be based on the recommendations of R&amp;D Wing, against the terms and conditions laid out in this contract. The contract will be signed by the Employer and the Employee, while endorsement will be done by R&amp;D Wing. The contract shall be effective from <strong>{{ $startFormatted }}</strong> for a period of <strong>{{ $tenureFormatted }}</strong>, extendable as per agreement of all the three parties i.e. the Employer, the Employee and R&amp;D Wing.
          </div>
        </div>

        {{-- 2. COMPANY BACKGROUND --}}
        <div style="margin-bottom: 16px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">2.</span>
            <span class="sc-clause-heading">COMPANY BACKGROUND</span>
          </div>
          <div style="margin-left: 32px;">
            M/s MTSS (Pvt) Ltd is equipped with talented professionals with strong technical background to run business operations. The company's primary role is to provide technical and logistic services to various organization in the fields related to maritime affairs. The company has its head office at Islamabad and regional office at Karachi..
          </div>
        </div>

        {{-- 3. DESIGNATION OF THE EMPLOYEE --}}
        <div style="margin-bottom: 16px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">3.</span>
            <span class="sc-clause-heading">DESIGNATION OF THE EMPLOYEE</span>
          </div>
          <div style="margin-left: 32px;">
            The Employee will work {{ $empType }} as '<strong>{{ $designation }}</strong>' for provisioning of necessary work/ tasks as per assigned Terms of Reference (TORs) issued by R&amp;D Wing.
          </div>
        </div>

        {{-- 4. SECURITY CLEARANCE (Starts on Page 1) --}}
        <div style="margin-bottom: 30px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">4.</span>
            <span class="sc-clause-heading">SECURITY CLEARANCE</span>
          </div>
          <div style="margin-left: 32px;">
            Security clearance of the Employee will be processed by R&amp;D Wing. The individual will
          </div>
        </div>

        {{-- Page 1 Footer --}}
        <div class="sc-footer">
          <div class="sc-footer-num">1 of 4</div>
          <div class="sc-footer-blue">CONFIDENTIAL</div>
        </div>
      </div>

      {{-- ============================================================= --}}
      {{-- PAGE 2                                                        --}}
      {{-- ============================================================= --}}
      <div id="scPage2" class="sc-a4-sheet">
        {{-- Page 2 Header --}}
        <div class="sc-header-blue">CONFIDENTIAL</div>

        {{-- Clause 4 Continuation --}}
        <div style="margin-bottom: 18px; margin-left: 32px; text-align: justify;">
          be allowed to join the organization after receiving security clearance from Naval Intelligence.
        </div>

        {{-- 5. PLACE OF DUTY --}}
        <div style="margin-bottom: 18px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">5.</span>
            <span class="sc-clause-heading">PLACE OF DUTY</span>
          </div>
          <div style="margin-left: 32px;">
            The services of the Employee are specific for the designated work/ project as per requirement of R&amp;D Wing. The arranngement of office premises, entry pass etc. for the Employee will be the facilitated and coordinated by R&amp;D Wing.
          </div>
        </div>

        {{-- 6. REMUNERATION --}}
        <div style="margin-bottom: 18px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">6.</span>
            <span class="sc-clause-heading">REMUNERATION</span>
          </div>
          <div style="margin-left: 32px;">
            <div style="margin-bottom: 10px;">
              <span style="margin-right: 14px;">a.</span>
              Gross payment of PKR <strong>{{ $salaryFormatted }}</strong> (<span>{{ $salaryWords }}</span>) (taxable amount) for performing the task/duties will be paid to the Employee on monthly basis.
            </div>
            <div style="margin-bottom: 10px;">
              <span style="margin-right: 14px;">b.</span>
              The Employee may be eligible for award of any performance incentives/honorarium as per R&amp;D Wing policies and approval.
            </div>
            <div>
              <span style="margin-right: 14px;">c.</span>
              During the contractual period, the Employee will not be entitled for any additional incentives such as increment, medical, bonus, gratuity, allowances leave encashment etc, until authorized by competent authority in accordance with para 6b above.
            </div>
          </div>
        </div>

        {{-- 7. CONTRACT RENEWAL --}}
        <div style="margin-bottom: 18px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">7.</span>
            <span class="sc-clause-heading">CONTRACT RENEWAL</span>
          </div>
          <div style="margin-left: 32px;">
            Renewal of contract will be subject to requirement of R&amp;D Wing and mainly based on performance evaluation of the Employee.
          </div>
        </div>

        {{-- 8. RIGHTS AND DUTIES OF CONTRACT PARTIES --}}
        <div style="margin-bottom: 18px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">8.</span>
            <span class="sc-clause-heading">RIGHTS AND DUTIES OF CONTRACT PARTIES</span>
          </div>
          <div style="margin-left: 32px;">
            <div style="margin-bottom: 8px;">
              <span style="margin-right: 14px;">a.</span>
              The Employee agrees to act loyally and solely to the Employer and R&amp;D Wing interests.
            </div>
            <div style="margin-bottom: 8px;">
              <span style="margin-right: 14px;">b.</span>
              The Employee is bound to return all materials (hardware and software) provided byR&amp;D Wing upon completion of asigned tasks.
            </div>
            <div style="margin-bottom: 8px;">
              <span style="margin-right: 14px;">c.</span>
              R&amp;D Wing will facilitate the Employee by providing all necessary documentation and for execution of the assigned tasks.
            </div>
            <div>
              <span style="margin-right: 14px;">d.</span>
              The Employee will be bound to follow time schedule and meet deadlines of the assigned tasks as desired by R&amp;D Wing.
            </div>
          </div>
        </div>

        {{-- 9. HEALTH AND SAFETY (Starts on Page 2) --}}
        <div style="margin-bottom: 30px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">9.</span>
            <span class="sc-clause-heading">HEALTH AND SAFETY</span>
          </div>
          <div style="margin-left: 32px;">
            <div>
              <span style="margin-right: 14px;">a.</span>
              R&amp;D Wing shall provide a safe working environment to the Employee as per applicable and associated rules and regulations.
            </div>
          </div>
        </div>

        {{-- Page 2 Footer --}}
        <div class="sc-footer">
          <div class="sc-footer-num">2 of 4</div>
          <div class="sc-footer-blue">CONFIDENTIAL</div>
        </div>
      </div>

      {{-- ============================================================= --}}
      {{-- PAGE 3                                                        --}}
      {{-- ============================================================= --}}
      <div id="scPage3" class="sc-a4-sheet">
        {{-- Page 3 Header --}}
        <div class="sc-header-blue">CONFIDENTIAL</div>

        {{-- Clause 9 Continuation --}}
        <div style="margin-bottom: 18px; margin-left: 32px; text-align: justify;">
          <div style="margin-bottom: 8px;">
            <span style="margin-right: 14px;">b.</span>
            R&amp;D Wing will be responsible for relevant training and necessary technical protective measures to mitigate health and safety risks at the workplace/ site.
          </div>
          <div style="margin-bottom: 8px;">
            <span style="margin-right: 14px;">c.</span>
            The Employee will be responsible to follow all safety SOPs during execution of assigned tasks.
          </div>
          <div>
            <span style="margin-right: 14px;">d.</span>
            The Employee shall have an access to use the safety equipment in case of any emergency situation.
          </div>
        </div>

        {{-- 10. LEAVE --}}
        <div style="margin-bottom: 18px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">10.</span>
            <span class="sc-clause-heading">LEAVE</span>
          </div>
          <div style="margin-left: 32px;">
            The Employee will be entiltled for 30 days paid leave anually as per discreation of R&amp;D Wing. The authorization/ grant of leave to the individual will be governed by the R&amp;D Wing polices.
          </div>
        </div>

        {{-- 11. TERMINATION --}}
        <div style="margin-bottom: 18px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">11.</span>
            <span class="sc-clause-heading">TERMINATION</span>
          </div>
          <div style="margin-left: 32px;">
            <div style="margin-bottom: 8px;">
              <span style="margin-right: 14px;">a.</span>
              Termination of this Contract will be subject to one month's notice issued by the R&amp;D Wing without assigning any reason. Upon completion of the notice period, the R&amp;D Wing will inform the Employer to cancel the contract. However, the Employee will be entitled for the remuneration of the work performed up to the expiry of the notice period.
            </div>
            <div style="margin-bottom: 8px;">
              <span style="margin-right: 14px;">b.</span>
              Similarly, the Employee may also terminate this Contract by providing one-month written notice. In this case, theEmployee will be entitled for the remuneration for the work performed up to the expiry of the notice period.
            </div>
            <div>
              <span style="margin-right: 14px;">c.</span>
              In case of any disciplinary grounds, the Employer and R&amp;D Wing reserve the right to dismiss the services and cancel the contract on "Immediate Basis" and R&amp;D Wing may take legal action as deemed appropriate.
            </div>
          </div>
        </div>

        {{-- 12. CONFIDENTIALITY --}}
        <div style="margin-bottom: 18px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">12.</span>
            <span class="sc-clause-heading">CONFIDENTIALITY</span>
          </div>
          <div style="margin-left: 32px;">
            <div style="margin-bottom: 8px;">
              <span style="margin-right: 14px;">a.</span>
              All information provided to the Employee by R&amp;D Wing (documents, drawings etc.) be treated as "CONFIDENTIAL". These shall not be reproduced, used and/or communicated directly or indirectly to any other party.
            </div>
            <div style="margin-bottom: 8px;">
              <span style="margin-right: 14px;">b.</span>
              Also, any information shall not to be discussed in relation to the Company/ Project during or after termination of contract.
            </div>
            <div>
              <span style="margin-right: 14px;">c.</span>
              Furthermore, any information or secret is not to be divulged that is obtained while in the service of the company unless compelled to do so by the Competent Court of Law..
            </div>
          </div>
        </div>

        {{-- 13. DISPUTE RESOLUTION (Starts on Page 3) --}}
        <div style="margin-bottom: 30px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">13.</span>
            <span class="sc-clause-heading">DISPUTE RESOLUTION</span>
          </div>
        </div>

        {{-- Page 3 Footer --}}
        <div class="sc-footer">
          <div class="sc-footer-num">3 of 4</div>
          <div class="sc-footer-blue">CONFIDENTIAL</div>
        </div>
      </div>

      {{-- ============================================================= --}}
      {{-- PAGE 4                                                        --}}
      {{-- ============================================================= --}}
      <div id="scPage4" class="sc-a4-sheet">
        {{-- Page 4 Header --}}
        <div class="sc-header-blue">CONFIDENTIAL</div>

        {{-- Clause 13 Continuation --}}
        <div style="margin-bottom: 18px; margin-left: 32px; text-align: justify;">
          In case of disagreement between the parties as to the performance of this contract, the Reginonal Director MTSS would be the final deciding authority and his decision would be the final and enforceable and will not be subject to any case or appeal or recourse of any kind in any court of law and will be binding on all parties.
        </div>

        {{-- 14. AMMENDMENTS --}}
        <div style="margin-bottom: 18px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">14.</span>
            <span class="sc-clause-heading">AMMENDMENTS</span>
          </div>
          <div style="margin-left: 32px;">
            This contract may only be amended subject to a written schedule duly signed by all parties.
          </div>
        </div>

        {{-- 15. SIGNATURES --}}
        <div style="margin-bottom: 45px; text-align: justify;">
          <div style="margin-bottom: 6px;">
            <span class="sc-clause-num">15.</span>
            <span class="sc-clause-heading">SIGNATURES</span>
          </div>
          <div style="margin-left: 32px;">
            This Contract has been drawn up in triplicate, one original for each party.
          </div>
        </div>

        {{-- Signatures Grid --}}
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 45px; padding: 0 10px;">
          {{-- Left: GM NRDI, MTSS Karachi Office --}}
          <div style="text-align: center; width: 260px;">
            <div style="border-bottom: 1.5px solid #000; margin-bottom: 8px; width: 100%;"></div>
            <div style="font-weight: bold; line-height: 1.3;">GM NRDI, MTSS Karachi<br>Office</div>
            <div style="text-align: left; margin-top: 18px;">
              <div style="margin-bottom: 6px;">Place: ____________________</div>
              <div>Date: &nbsp;____________________</div>
            </div>
          </div>

          {{-- Right: Employee (Party II) --}}
          <div style="text-align: center; width: 260px;">
            <div style="border-bottom: 1.5px solid #000; margin-bottom: 8px; width: 100%;"></div>
            <div style="font-weight: bold; line-height: 1.3;">Employee<br>(Party II)</div>
            <div style="text-align: left; margin-top: 18px;">
              <div style="margin-bottom: 6px;">Place: ____________________</div>
              <div>Date: &nbsp;____________________</div>
            </div>
          </div>
        </div>

        {{-- Center: Endorsement by RD Wing Party III --}}
        <div style="text-align: center; margin-bottom: 40px;">
          <div style="display: inline-block; text-align: center; min-width: 250px;">
            <div style="font-weight: bold; text-decoration: underline; text-underline-offset: 3px;">Endorsement by RD Wing</div>
            <div style="font-weight: bold; text-decoration: underline; text-underline-offset: 3px; margin-bottom: 24px;">Party III</div>
            <div style="text-align: left; display: inline-block;">
              <div style="margin-bottom: 6px;">Place: ____________________</div>
              <div>Date: &nbsp;____________________</div>
            </div>
          </div>
        </div>

        {{-- Page 4 Footer --}}
        <div class="sc-footer">
          <div class="sc-footer-num">4 of 4</div>
          <div class="sc-footer-blue">CONFIDENTIAL</div>
        </div>
      </div>

    </div>
  </main>

  <script>
    function switchPageContract(ctrId) {
      if (ctrId) {
        window.location.href = "{{ url('/divhr/employee/' . $id . '/service-contract') }}/" + ctrId;
      }
    }

    function scScrollToPage(pageNum) {
      const pageEl = document.getElementById('scPage' + pageNum);
      if (pageEl) {
        pageEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  </script>
</body>
</html>
