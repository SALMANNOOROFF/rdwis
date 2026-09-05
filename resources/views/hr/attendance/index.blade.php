{{-- resources/views/hr/attendance/index.blade.php --}}
@extends('welcome')

@section('content')
<style>
  /* Attendance Grid Custom Styles */
  .att-table-container {
    max-height: calc(100vh - 210px);
    overflow: auto;
    position: relative;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
  }

  .att-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    margin-bottom: 0;
    font-size: 13px;
    background: #ffffff;
  }

  /* Sticky Left Columns */
  .sticky-col-idx {
    position: sticky;
    left: 0;
    z-index: 10;
    background: #f8fafc !important;
    width: 45px;
    min-width: 45px;
    text-align: center;
    border-right: 1px solid #e2e8f0 !important;
    border-bottom: 1px solid #e2e8f0 !important;
    color: #64748b !important;
  }
  .sticky-col-emp {
    position: sticky;
    left: 45px;
    z-index: 10;
    background: #ffffff !important;
    width: 220px;
    min-width: 220px;
    max-width: 260px;
    border-right: 2px solid #cbd5e1 !important;
    border-bottom: 1px solid #e2e8f0 !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Row Hover States */
  .att-table tbody tr:hover td {
    background-color: #f8fafc !important;
  }
  .att-table tbody tr:hover td.sticky-col-emp {
    background-color: #f8fafc !important;
  }
  .att-table tbody tr:hover td.sticky-col-idx {
    background-color: #f1f5f9 !important;
  }

  /* Sticky Header */
  .att-table thead th {
    position: sticky;
    top: 0;
    z-index: 20;
    background: #f8fafc !important;
    color: #1e293b !important;
    border-bottom: 2px solid #cbd5e1 !important;
    border-top: none !important;
    border-right: 1px solid #e2e8f0 !important;
    padding: 6px 2px;
    user-select: none;
    font-weight: 700;
  }
  .att-table thead tr:first-child th.sticky-col-idx,
  .att-table thead tr:first-child th.sticky-col-emp {
    z-index: 30;
    background: #f8fafc !important;
  }
  .att-table thead tr:nth-child(2) th {
    top: 36px;
    background: #f1f5f9 !important;
    border-bottom: 2px solid #cbd5e1 !important;
  }
  .att-table thead tr:nth-child(2) th.sticky-col-idx,
  .att-table thead tr:nth-child(2) th.sticky-col-emp {
    z-index: 30;
    background: #f1f5f9 !important;
  }

  /* Day Header Hover Link */
  .day-header-link {
    display: block;
    color: #1e293b !important;
    font-weight: 700;
    text-decoration: none;
    border-radius: 4px;
    padding: 2px 0;
    transition: background 0.15s, color 0.15s;
  }
  .day-header-link:hover {
    background: #3b82f6;
    color: #ffffff !important;
    text-decoration: none;
  }

  /* Cutoff Day Divider Indicator */
  .col-cutoff {
    border-right: 2px dashed #f59e0b !important;
  }

  /* Cell Base Styles */
  .att-cell {
    width: 34px;
    min-width: 34px;
    max-width: 34px;
    height: 34px;
    padding: 0 !important;
    text-align: center;
    vertical-align: middle !important;
    border-right: 1px solid #e2e8f0 !important;
    border-bottom: 1px solid #e2e8f0 !important;
    font-weight: 800;
    font-size: 13px;
    position: relative;
    outline: none;
  }

  /* Cell State: Editable */
  .cell-editable {
    background: #ffffff !important;
    color: #0f172a !important;
    cursor: cell;
    transition: background 0.12s, box-shadow 0.12s;
  }
  .cell-editable:hover {
    background: #f1f5f9 !important;
  }
  .cell-editable:focus,
  .cell-editable.cell-focused {
    background: #eff6ff !important;
    box-shadow: inset 0 0 0 2px #3b82f6 !important;
    z-index: 5;
  }

  /* Cell State: Dirty / Modified */
  .cell-dirty {
    background: #fef3c7 !important;
    color: #b45309 !important;
    border: 1px solid #f59e0b !important;
    position: relative;
  }
  .cell-dirty::after {
    content: '';
    position: absolute;
    top: 2px;
    right: 2px;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #f59e0b;
  }

  /* Cell State: Locked */
  .cell-locked {
    background: #f1f5f9 !important;
    color: #94a3b8 !important;
    cursor: not-allowed !important;
    user-select: none;
  }
  .cell-locked:hover {
    background: #e2e8f0 !important;
  }
  .cell-locked .lock-icon {
    font-size: 9px;
    opacity: 0.7;
    color: #94a3b8;
  }

  /* Cell State: Weekend / Holiday (Non-working days, no Z printed) */
  .cell-weekend {
    background-color: #f1f5f9 !important;
    background-image: repeating-linear-gradient(
      -45deg,
      #f1f5f9,
      #f1f5f9 4px,
      #e2e8f0 4px,
      #e2e8f0 8px
    ) !important;
    cursor: not-allowed !important;
    user-select: none;
  }

  /* Cell State: Future Date */
  .cell-future {
    background: repeating-linear-gradient(
      45deg,
      #fafafa,
      #fafafa 5px,
      #f1f5f9 5px,
      #f1f5f9 10px
    ) !important;
    color: #cbd5e1 !important;
    cursor: not-allowed !important;
    user-select: none;
  }

  /* Floating Unsaved Changes Bar */
  .floating-save-bar {
    position: fixed;
    bottom: 24px;
    right: 32px;
    z-index: 1050;
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 10px 18px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
    display: none;
    align-items: center;
    gap: 16px;
    animation: slideUp 0.2s ease-out;
  }
  @keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }

  /* Attendance Code Colors */
  .val-P { color: #15803d !important; font-weight: 800; }
  .val-W { color: #0369a1 !important; font-weight: 800; }
  .val-T { color: #4338ca !important; font-weight: 800; }
  .val-A { color: #b91c1c !important; font-weight: 800; }
  .val-L { color: #b45309 !important; font-weight: 800; }
  .val-U { color: #c2410c !important; font-weight: 800; }
  .val-N { color: #64748b !important; font-weight: 800; }
  .val-Z { color: transparent !important; }

  /* Key Badges in Legend */
  .badge-key {
    font-weight: 700;
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
  }
  .key-P { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
  .key-W { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
  .key-T { background: #e0e7ff; color: #4338ca; border: 1px solid #a5b4fc; }
  .key-A { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
  .key-L { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
  .key-U { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
  .key-N { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
  .key-weekend { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; background-image: repeating-linear-gradient(-45deg, #f1f5f9, #f1f5f9 3px, #e2e8f0 3px, #e2e8f0 6px); }
</style>

<div class="content-wrapper p-3" style="background-color: var(--rd-bg, #f4f6f9); min-height: 100vh;">
  <div class="container-fluid">
    
    <!-- Top Action Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 12px;">
      <!-- Title & Central Mode Switcher -->
      <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
        <h4 class="mb-0 font-weight-bold" style="font-family: 'Rajdhani', sans-serif; color: #0f172a;">
          <i class="fas fa-calendar-check mr-2 text-primary"></i>Staff Attendance Management
        </h4>

        @if($isCentral)
          <div class="btn-group btn-group-sm shadow-sm" role="group">
            <a href="{{ route('divhr.attendance', ['mode' => 'm', 'month' => $month]) }}" 
               class="btn {{ $mode === 'm' ? 'btn-danger font-weight-bold' : 'btn-outline-secondary' }}" 
               style="{{ $mode === 'm' ? '' : 'background: #ffffff;' }}">
              <i class="fas fa-globe mr-1"></i> ALL DEPT
            </a>
            <a href="{{ route('divhr.attendance', ['mode' => 's', 'month' => $month]) }}" 
               class="btn {{ $mode === 's' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}"
               style="{{ $mode === 's' ? '' : 'background: #ffffff;' }}">
              <i class="fas fa-sitemap mr-1"></i> MY DEPT
            </a>
          </div>
        @endif
      </div>

      <!-- Month Navigation & Action Controls -->
      @php
        $currMonthDt = \Carbon\Carbon::parse($month . '-01');
        $prevMonthStr = $currMonthDt->copy()->subMonth()->format('Y-m');
        $nextMonthStr = $currMonthDt->copy()->addMonth()->format('Y-m');
        $floorMonthStr = \Carbon\Carbon::parse($floorDate)->format('Y-m');
        $isAtFloor = $month <= $floorMonthStr;
        $isAtCeiling = $month >= $currentMonth;
      @endphp
      <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
        <!-- Month Navigation Buttons -->
        <div class="btn-group btn-group-sm shadow-sm mr-2">
          <a href="{{ $isAtFloor ? '#' : route('divhr.attendance', ['month' => $prevMonthStr, 'mode' => $mode]) }}" 
             class="btn btn-white border text-dark {{ $isAtFloor ? 'disabled' : '' }}" 
             style="background: #ffffff; border-color: #cbd5e1;"
             title="Previous Month (Floor: {{ $floorMonthStr }})">
            <i class="fas fa-chevron-left"></i>
          </a>
          
          <form method="GET" action="{{ route('divhr.attendance') }}" class="form-inline m-0">
            <input type="hidden" name="mode" value="{{ $mode }}">
            <input type="month" 
                   name="month" 
                   value="{{ $month }}" 
                   min="{{ $floorMonthStr }}" 
                   max="{{ $currentMonth }}"
                   class="form-control form-control-sm text-center px-2 font-weight-bold" 
                   style="border-radius: 0; width: 140px; background: #ffffff; color: #0f172a; border-color: #cbd5e1;"
                   onchange="this.form.submit()">
          </form>

          <a href="{{ $isAtCeiling ? '#' : route('divhr.attendance', ['month' => $nextMonthStr, 'mode' => $mode]) }}" 
             class="btn btn-white border text-dark {{ $isAtCeiling ? 'disabled' : '' }}" 
             style="background: #ffffff; border-color: #cbd5e1;"
             title="Next Month (Max: {{ $currentMonth }})">
            <i class="fas fa-chevron-right"></i>
          </a>
        </div>

        <!-- Approver Actions: Bulk Actions & Sheet Generator -->
        @if($isApprover)
          <button type="button" class="btn btn-sm btn-outline-warning text-dark font-weight-bold shadow-sm mr-1" style="background: #fffbeb; border-color: #f59e0b; color: #b45309 !important;" data-toggle="modal" data-target="#bulkActionModal">
            <i class="fas fa-layer-group mr-1 text-warning"></i> Bulk Action
          </button>

          <form method="POST" action="{{ route('divhr.attendance.generate_sheet') }}" class="d-inline m-0" onsubmit="return confirm('Generate / Sync attendance sheet for {{ $month }}? This will add any newly joined employees.');">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <button type="submit" class="btn btn-sm btn-outline-info shadow-sm mr-2" style="background: #f0f9ff; border-color: #7dd3fc; color: #0369a1;" title="Generate or sync missing rows for active staff">
              <i class="fas fa-sync-alt mr-1"></i> Sync Sheet
            </button>
          </form>

          <!-- Division Salary Generation Button -->
          <div class="btn-group btn-group-sm shadow-sm mr-2" role="group">
            <a href="{{ route('divhr.salary.requisitions.create', ['month' => $month]) }}" 
               id="btn-generate-salary-from-att"
               class="btn btn-sm btn-primary font-weight-bold" 
               style="background: #2563eb; border-color: #1d4ed8; color: #ffffff;" 
               title="Generate Monthly Salary Requisitions for {{ $month }}">
              <i class="fas fa-money-check-alt mr-1"></i> Generate Salary
            </a>
            <a href="{{ route('divhr.salary.requisitions.index', ['month' => $month]) }}" 
               class="btn btn-sm btn-outline-primary" 
               style="background: #eff6ff; border-color: #93c5fd; color: #1e40af;"
               title="View Salary Requisitions for {{ $month }}">
              <i class="fas fa-list-alt mr-1"></i> Requisitions
            </a>
          </div>
        @endif

        <!-- Direct Save Trigger -->
        <button type="button" class="btn btn-sm btn-success font-weight-bold px-3 shadow-sm" id="btn-save-grid" disabled>
          <i class="fas fa-save mr-1"></i> Save Changes
        </button>
      </div>
    </div>

    <!-- Alert / Message Area -->
    @if(session('success'))
      <div class="alert alert-success border-success alert-dismissible fade show py-2 px-3 mb-3 small shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close py-2" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif
    @if(session('error'))
      <div class="alert alert-danger border-danger alert-dismissible fade show py-2 px-3 mb-3 small shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
        <button type="button" class="close py-2" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
    @endif

    <!-- Status Legend & Info Bar -->
    <div class="card shadow-sm mb-3" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;">
      <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
        <div class="d-flex align-items-center flex-wrap" style="gap: 8px; font-size: 11px;">
          <span class="font-weight-bold text-uppercase mr-1" style="color: #475569;">Keys:</span>
          <span class="badge-key key-P">P - Present</span>
          <span class="badge-key key-W">W - Work from Home</span>
          <span class="badge-key key-T">T - Ty Duty</span>
          <span class="badge-key key-A">A - Absent</span>
          <span class="badge-key key-L">L - Leave</span>
          <span class="badge-key key-U">U - Unpaid Leave</span>
          <span class="badge-key key-N">N - Not Applicable</span>
          <span class="badge-key key-weekend"><i class="fas fa-calendar-times mr-1"></i> Weekend / Holiday</span>
        </div>

        <div class="d-flex align-items-center text-xs" style="gap: 14px; color: #475569;">
          <span><i class="fas fa-calendar-alt text-primary mr-1"></i> {{ $first }} to {{ $last }} ({{ $days }} Days)</span>
          <span><i class="fas fa-cut text-warning mr-1"></i> Payroll Cutoff: <strong>Day {{ $cutoff_day ?? 26 }}</strong></span>
          @if($is_locked)
            <span class="badge badge-danger px-2 py-1"><i class="fas fa-lock mr-1"></i> Month Fully Locked</span>
          @endif
        </div>
      </div>
    </div>

    <!-- Attendance Grid Table Card -->
    @if(count($list) > 0)
      <div class="card shadow-sm" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px;">
        <div class="card-body p-0">
          <div class="att-table-container">
            <table class="att-table table table-bordered" id="attendance-table">
              <thead>
                <!-- Row 1: Day Numbers (Clickable for One-Day Drilldown) -->
                <tr>
                  <th class="sticky-col-idx">#</th>
                  <th class="sticky-col-emp">Employee</th>
                  @for($d = 1; $d <= $days; $d++)
                    @php
                      $dayDtStr = sprintf('%s-%02d', $month, $d);
                      $isCutoff = ($d === ($cutoff_day ?? 26));
                    @endphp
                    <th class="text-center {{ $isCutoff ? 'col-cutoff' : '' }}" style="width: 34px;">
                      <a href="{{ route('divhr.attendance.oneday', ['date' => $dayDtStr]) }}" 
                         class="day-header-link" 
                         title="Click to drill down into Day {{ $d }} remarks">
                        {{ $d }}
                      </a>
                    </th>
                  @endfor
                  <th style="width: 60px;" class="text-center font-weight-bold" style="color: #1e293b;">Present</th>
                  <th style="width: 65px;" class="text-center font-weight-bold" style="color: #1e293b;">%</th>
                  <th style="width: 75px;" class="text-center font-weight-bold" style="color: #1e293b;">Action</th>
                </tr>

                <!-- Row 2: Weekday Names -->
                <tr>
                  <th class="sticky-col-idx"></th>
                  <th class="sticky-col-emp text-muted small font-weight-normal">Total Staff: {{ count($list) }}</th>
                  @for($d = 1; $d <= $days; $d++)
                    @php
                      $wName = $weekdays[$d] ?? '';
                      $isCutoff = ($d === ($cutoff_day ?? 26));
                      $isWeekend = in_array($wName, ['Sat', 'Sun'], true);
                    @endphp
                    <th class="text-center small {{ $isWeekend ? 'text-danger font-weight-bold' : 'text-muted font-weight-normal' }} {{ $isCutoff ? 'col-cutoff' : '' }}" style="{{ $isWeekend ? 'background: #f8fafc !important;' : '' }}">
                      {{ substr($wName, 0, 1) }}
                    </th>
                  @endfor
                  <th></th>
                  <th></th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="att-grid-body">
                @foreach($list as $i => $row)
                  @php
                    $lockedDays = $row['locked_days'] ?? [];
                    $vals = $row['vals'] ?? [];
                    $attId = $row['att_id'] ?? null;
                  @endphp
                  <tr data-emp-id="{{ $row['emp_id'] }}" data-att-id="{{ $attId }}">
                    <!-- Fixed Col 1: Index -->
                    <td class="sticky-col-idx font-weight-bold">{{ $i + 1 }}</td>
                    
                    <!-- Fixed Col 2: Employee Info -->
                    <td class="sticky-col-emp" title="{{ $row['name'] }} ({{ $row['emp_id'] }})">
                      <div class="font-weight-bold text-truncate" style="color: #0f172a !important;">{{ $row['name'] }}</div>
                      <div class="text-muted text-xs font-monospace">{{ $row['emp_id'] }}</div>
                    </td>

                    <!-- Day Cells 1..Days -->
                    @for($d = 1; $d <= $days; $d++)
                      @php
                        $val = $vals[$d] ?? '';
                        $dayCarbon = \Carbon\Carbon::parse(sprintf('%s-%02d', $month, $d));
                        $isWeekend = $dayCarbon->isWeekend() || ($val === 'Z');
                        $isLocked = !empty($lockedDays[$d]);
                        $isCutoff = ($d === ($cutoff_day ?? 26));
                        $isFuture = $dayCarbon->isFuture();

                        // Style state
                        $cellClass = 'cell-editable';
                        $readonly = false;

                        if ($isWeekend) {
                            $cellClass = 'cell-weekend';
                            $readonly = true;
                        } elseif ($isLocked) {
                            $cellClass = 'cell-locked';
                            $readonly = true;
                        } elseif ($isFuture) {
                            $cellClass = 'cell-future';
                            $readonly = true;
                        }

                        $valClass = $val ? 'val-' . $val : '';
                      @endphp
                      <td class="att-cell {{ $cellClass }} {{ $isCutoff ? 'col-cutoff' : '' }}"
                          tabindex="{{ $readonly ? '-1' : '0' }}"
                          data-emp-id="{{ $row['emp_id'] }}"
                          data-att-id="{{ $attId }}"
                          data-day="{{ $d }}"
                          data-val="{{ $val }}"
                          data-original-val="{{ $val }}"
                          data-locked="{{ ($isLocked || $isWeekend) ? '1' : '0' }}"
                          data-readonly="{{ $readonly ? '1' : '0' }}"
                          title="{{ $isWeekend ? 'Weekend / Holiday (Locked)' : ($isLocked ? 'Period is locked (Cutoff protection)' : ($isFuture ? 'Future date' : 'Day ' . $d . ' - Click or type code')) }}">
                        @if($isLocked && !$isWeekend)
                          <span class="lock-icon mr-1"><i class="fas fa-lock"></i></span>
                        @endif
                        {{-- DO NOT PRINT 'Z' inside weekend/holiday cells as requested by user ("yhn z na likha aye") --}}
                        <span class="cell-val {{ $valClass }}">{{ ($val !== 'X' && !$isWeekend) ? $val : '' }}</span>
                      </td>
                    @endfor

                    <!-- Present & Percentage Columns -->
                    <td class="text-center font-weight-bold text-success align-middle row-present" style="background: #ffffff;">
                      {{ $row['present'] ?? 0 }}
                    </td>
                    <td class="text-center font-weight-bold text-info align-middle row-percent" style="background: #ffffff;">
                      {{ $row['percent'] ?? 0 }}%
                    </td>

                    <!-- Action: Summary Modal Trigger -->
                    <td class="text-center align-middle" style="background: #ffffff;">
                      <button type="button" 
                              class="btn btn-xs btn-outline-info btn-row-summary py-0 px-2" 
                              data-emp-id="{{ $row['emp_id'] }}" 
                              title="View monthly attendance summary">
                        <i class="fas fa-chart-pie"></i>
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    @else
      <!-- Empty State Card -->
      <div class="card shadow-sm text-center py-5" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px;">
        <div class="card-body">
          <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
          <h4 class="font-weight-bold mb-2" style="color: #0f172a;">No Attendance Sheet Found for {{ $month }}</h4>
          <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
            There are no attendance records generated for this month in your unit range. You can generate a new sheet to initialize attendance for all active staff.
          </p>

          @if($isApprover)
            <form method="POST" action="{{ route('divhr.attendance.generate_sheet') }}" class="d-inline">
              @csrf
              <input type="hidden" name="month" value="{{ $month }}">
              <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold shadow">
                <i class="fas fa-plus-circle mr-1"></i> Generate Attendance Sheet for {{ $month }}
              </button>
            </form>
          @else
            <span class="badge badge-secondary px-3 py-2">
              <i class="fas fa-lock mr-1"></i> Contact an HR Approver to generate this month's attendance sheet.
            </span>
          @endif
        </div>
      </div>
    @endif

  </div>
</div>

<!-- Floating Save Changes Bar -->
<div class="floating-save-bar" id="floating-save-bar">
  <div class="text-white font-weight-bold">
    <i class="fas fa-edit text-warning mr-1"></i>
    <span id="unsaved-count">0</span> unsaved change(s)
  </div>
  <div class="d-flex align-items-center" style="gap: 8px;">
    <button type="button" class="btn btn-sm btn-outline-light" id="btn-discard-changes">
      Discard
    </button>
    <button type="button" class="btn btn-sm btn-success font-weight-bold px-3 shadow" id="btn-floating-save">
      <i class="fas fa-save mr-1"></i> Save Attendance (Ctrl+S)
    </button>
  </div>
</div>

{{-- Include Modals --}}
@include('hr.attendance.partials.bulk_action_modal')
@include('hr.attendance.partials.summary_modal')

@endsection

@section('scripts')
<script>
$(document).ready(function() {
  const validCodes = ['P', 'W', 'T', 'A', 'L', 'U', 'N'];
  const dirtyChanges = new Map(); // key: "empId_day" -> {emp_id, day, val}

  // Update save button states
  function updateDirtyState() {
    const count = dirtyChanges.size;
    $('#unsaved-count').text(count);
    if (count > 0) {
      $('#floating-save-bar').css('display', 'flex');
      $('#btn-save-grid').prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save Changes (' + count + ')');
    } else {
      $('#floating-save-bar').hide();
      $('#btn-save-grid').prop('disabled', true).html('<i class="fas fa-save mr-1"></i> Save Changes');
    }
  }

  // Move cell focus helper
  function moveFocus($currentCell, deltaRow, deltaCol) {
    const $tr = $currentCell.closest('tr');
    const day = parseInt($currentCell.data('day'));
    const daysTotal = {{ $days }};

    if (deltaCol !== 0) {
      const targetDay = day + deltaCol;
      if (targetDay >= 1 && targetDay <= daysTotal) {
        const $target = $tr.find('.att-cell[data-day="' + targetDay + '"]');
        if ($target.length && $target.data('readonly') !== 1) {
          $target.focus();
          return;
        }
      }
    }

    if (deltaRow !== 0) {
      const $targetTr = deltaRow > 0 ? $tr.next('tr') : $tr.prev('tr');
      if ($targetTr.length) {
        const $target = $targetTr.find('.att-cell[data-day="' + day + '"]');
        if ($target.length && $target.data('readonly') !== 1) {
          $target.focus();
          return;
        }
      }
    }
  }

  // Focus handling & Highlighting
  $('#attendance-table').on('focus', '.cell-editable', function() {
    $('.cell-focused').removeClass('cell-focused');
    $(this).addClass('cell-focused');
  });

  // Fast Keyboard Entry
  $('#attendance-table').on('keydown', '.cell-editable', function(e) {
    const $cell = $(this);
    const key = e.key.toUpperCase();

    // 1. Single Keystroke Code Entry
    if (validCodes.includes(key)) {
      e.preventDefault();
      const empId = String($cell.data('emp-id'));
      const day = parseInt($cell.data('day'));
      const origVal = String($cell.data('original-val') || '');

      // Set cell value
      $cell.find('.cell-val')
           .attr('class', 'cell-val val-' + key)
           .text(key);
      $cell.data('val', key);

      // Track dirty state
      const changeKey = empId + '_' + day;
      if (key !== origVal) {
        dirtyChanges.set(changeKey, { emp_id: empId, day: day, val: key });
        $cell.addClass('cell-dirty');
      } else {
        dirtyChanges.delete(changeKey);
        $cell.removeClass('cell-dirty');
      }
      updateDirtyState();

      // Auto-advance to next employee, same day (Fast vertical entry workflow)
      moveFocus($cell, 1, 0);
      return;
    }

    // 2. Clear cell on Backspace or Delete
    if (e.key === 'Backspace' || e.key === 'Delete') {
      e.preventDefault();
      const empId = String($cell.data('emp-id'));
      const day = parseInt($cell.data('day'));
      const origVal = String($cell.data('original-val') || '');

      $cell.find('.cell-val').attr('class', 'cell-val').text('');
      $cell.data('val', '');

      const changeKey = empId + '_' + day;
      if (origVal !== '') {
        dirtyChanges.set(changeKey, { emp_id: empId, day: day, val: '' });
        $cell.addClass('cell-dirty');
      } else {
        dirtyChanges.delete(changeKey);
        $cell.removeClass('cell-dirty');
      }
      updateDirtyState();
      return;
    }

    // 3. Navigation keys
    switch(e.key) {
      case 'Enter':
      case 'ArrowDown':
        e.preventDefault();
        moveFocus($cell, 1, 0);
        break;
      case 'ArrowUp':
        e.preventDefault();
        moveFocus($cell, -1, 0);
        break;
      case 'ArrowRight':
        e.preventDefault();
        moveFocus($cell, 0, 1);
        break;
      case 'ArrowLeft':
        e.preventDefault();
        moveFocus($cell, 0, -1);
        break;
    }
  });

  // Global Ctrl+S trigger
  $(window).on('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      if (dirtyChanges.size > 0) {
        saveBatch();
      }
    }
  });

  // Save changes batch
  function saveBatch() {
    if (dirtyChanges.size === 0) return;

    const payload = Array.from(dirtyChanges.values());
    const $btn = $('#btn-save-grid, #btn-floating-save');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    const form = $('<form method="POST" action="{{ route('divhr.attendance.save') }}"></form>');
    form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
    form.append('<input type="hidden" name="month" value="{{ $month }}">');
    form.append('<input type="hidden" name="payload_json" value=\'' + JSON.stringify(payload) + '\'>');
    $('body').append(form);
    form.submit();
  }

  $('#btn-save-grid, #btn-floating-save').on('click', saveBatch);

  // Discard changes
  $('#btn-discard-changes').on('click', function() {
    if (confirm('Discard all unsaved changes and reload original values?')) {
      location.reload();
    }
  });

  // =========================================================================
  // Bulk Action Locked-Cell Live Preview
  // =========================================================================
  function calculateBulkPreview() {
    const startDay = parseInt($('#start_day').val()) || 1;
    const endDay = parseInt($('#end_day').val()) || {{ $days }};
    const totalEmps = {{ count($list) }};

    if (startDay > endDay) {
      $('#prev-total-cells').text('0');
      $('#prev-update-cells').text('0');
      $('#prev-locked-cells').text('0');
      $('#preview-badge').removeClass('badge-success badge-warning').addClass('badge-danger').text('Invalid Range');
      return;
    }

    const totalDaysInRange = (endDay - startDay + 1);
    const totalCells = totalEmps * totalDaysInRange;

    let lockedCount = 0;
    // Iterate over DOM cells in range
    $('#att-grid-body tr').each(function() {
      for (let d = startDay; d <= endDay; d++) {
        const $c = $(this).find('.att-cell[data-day="' + d + '"]');
        if ($c.length && $c.data('readonly') === 1) {
          lockedCount++;
        }
      }
    });

    const updateCount = Math.max(0, totalCells - lockedCount);

    $('#prev-total-cells').text(totalCells.toLocaleString());
    $('#prev-update-cells').text(updateCount.toLocaleString());
    $('#prev-locked-cells').text(lockedCount.toLocaleString());
    $('#prev-locked-count').text(lockedCount.toLocaleString());

    if (lockedCount > 0) {
      $('#prev-locked-alert').fadeIn(150);
      $('#preview-badge').removeClass('badge-success badge-danger').addClass('badge-warning').text(lockedCount + ' Locked');
    } else {
      $('#prev-locked-alert').hide();
      $('#preview-badge').removeClass('badge-warning badge-danger').addClass('badge-success').text('All Ready');
    }
  }

  $('#start_day, #end_day').on('input change', calculateBulkPreview);
  $('#bulkActionModal').on('shown.bs.modal', calculateBulkPreview);

  // Action toggle in bulk modal
  $('input[name="action"]').on('change', function() {
    if ($(this).val() === 'toggle_holiday') {
      $('#group-code-select').hide();
      $('#group-holiday-select').fadeIn(150);
    } else {
      $('#group-holiday-select').hide();
      $('#group-code-select').fadeIn(150);
    }
    calculateBulkPreview();
  });

  // =========================================================================
  // Monthly Summary Modal Fetcher
  // =========================================================================
  $('.btn-row-summary').on('click', function() {
    const empId = $(this).data('emp-id');
    const month = "{{ $month }}";

    $('#summaryModal').modal('show');
    $('#summary-loader').show();
    $('#summary-content').hide();

    $.ajax({
      url: "{{ route('divhr.attendance.summary') }}",
      type: "GET",
      data: { emp_id: empId, month: month },
      success: function(res) {
        $('#summary-loader').hide();
        $('#summary-content').fadeIn();

        const emp = res.employee || {};
        const c = res.counts || {};

        $('#sum-emp-name').text(emp.emp_name || empId);
        $('#sum-emp-id').text(emp.emp_id || empId);
        $('#sum-emp-cnic').text(emp.emp_cnic || '-');
        $('#sum-emp-unit').text(emp.unt_name || 'Unit');
        $('#sum-period').text(res.month);

        $('#sum-count-P').text(c.P || 0);
        $('#sum-count-A').text(c.A || 0);
        $('#sum-count-leaves').text((c.L || 0) + (c.U || 0));
        $('#sum-count-working').text(c.working_days || 0);

        $('#tbl-P').text(c.P || 0);
        $('#tbl-W').text(c.W || 0);
        $('#tbl-T').text(c.T || 0);
        $('#tbl-A').text(c.A || 0);
        $('#tbl-L').text(c.L || 0);
        $('#tbl-U').text(c.U || 0);
        $('#tbl-N').text(c.N || 0);
        $('#tbl-Z').text(c.Z || 0);
        $('#tbl-total').text(c.total_days || 0);
      },
      error: function(xhr) {
        $('#summary-loader').hide();
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: xhr.responseJSON?.error || 'Could not load summary.',
          background: '#1e222d',
          color: '#fff'
        });
        $('#summaryModal').modal('hide');
      }
    });
  });
});
</script>
@endsection
