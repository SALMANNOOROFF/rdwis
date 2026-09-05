{{-- resources/views/hr/attendance/oneday.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper p-3" style="background-color: var(--rd-bg, #f4f6f9); min-height: 100vh;">
  <div class="container-fluid">
    <!-- Top Bar Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 12px;">
      <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
        <a href="{{ route('divhr.attendance', ['month' => $month]) }}" class="btn btn-sm btn-white border shadow-sm text-dark font-weight-bold" style="background: #ffffff; border-color: #cbd5e1;">
          <i class="fas fa-arrow-left mr-1 text-primary"></i> Back to Monthly Grid
        </a>
        <h4 class="mb-0 font-weight-bold" style="font-family: 'Rajdhani', sans-serif; color: #0f172a;">
          <i class="fas fa-calendar-day mr-2 text-primary"></i>Daily Attendance Drill-Down
        </h4>
      </div>

      <!-- Date Switcher Controls -->
      @php
        $currCarbon = \Carbon\Carbon::parse($date);
        $prevDay = $currCarbon->copy()->subDay()->toDateString();
        $nextDay = $currCarbon->copy()->addDay()->toDateString();
        $isTodayOrFuture = $currCarbon->isToday() || $currCarbon->isFuture();
      @endphp
      <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
        <a href="{{ route('divhr.attendance.oneday', ['date' => $prevDay]) }}" class="btn btn-sm btn-white border shadow-sm text-dark" style="background: #ffffff; border-color: #cbd5e1;">
          <i class="fas fa-chevron-left mr-1"></i> Prev Day
        </a>
        <form method="GET" action="{{ route('divhr.attendance.oneday') }}" class="form-inline m-0">
          <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm text-center px-2 font-weight-bold mr-2" style="background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 4px;" onchange="this.form.submit()">
        </form>
        <a href="{{ route('divhr.attendance.oneday', ['date' => $nextDay]) }}" class="btn btn-sm btn-white border shadow-sm text-dark {{ $isTodayOrFuture ? 'disabled' : '' }}" style="background: #ffffff; border-color: #cbd5e1;">
          Next Day <i class="fas fa-chevron-right ml-1"></i>
        </a>
      </div>
    </div>

    <!-- Date Overview Banner Card -->
    <div class="card shadow-sm mb-3" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;">
      <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
        <div>
          <span class="text-xs text-uppercase font-weight-bold" style="color: #64748b;">Selected Date</span>
          <h4 class="mb-0 font-weight-bold" style="color: #0f172a;">
            {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }}
          </h4>
        </div>
        <div class="d-flex align-items-center flex-wrap" style="gap: 12px;">
          <span class="badge px-3 py-2 text-sm font-weight-bold" style="background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; border-radius: 6px;">
            <i class="fas fa-calendar mr-1"></i> Day {{ $day }} of {{ \Carbon\Carbon::parse($date)->format('F Y') }}
          </span>
          <span class="badge px-3 py-2 text-sm font-weight-bold" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px;">
            <i class="fas fa-users mr-1"></i> {{ count($list) }} Staff in Scope
          </span>
        </div>
      </div>
    </div>

    <!-- Employees List Card -->
    <div class="card shadow-sm" style="background-color: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px;">
      <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
        <h5 class="mb-0 font-weight-bold d-flex align-items-center" style="color: #0f172a;">
          <i class="fas fa-clipboard-list text-primary mr-2"></i> Attendance Status & Remarks
        </h5>
        <div class="small" style="color: #64748b;">
          <i class="fas fa-info-circle mr-1"></i> Edit remarks inline — changes save automatically.
        </div>
      </div>

      <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-bordered mb-0" style="border-color: #e2e8f0;">
          <thead style="background: #f8fafc; color: #1e293b; border-bottom: 2px solid #cbd5e1;">
            <tr>
              <th style="width: 50px;" class="text-center font-weight-bold">#</th>
              <th style="width: 140px;" class="font-weight-bold">Employee ID</th>
              <th style="min-width: 200px;" class="font-weight-bold">Employee Name</th>
              <th style="width: 110px;" class="text-center font-weight-bold">Status Code</th>
              <th style="width: 120px;" class="text-center font-weight-bold">Period State</th>
              <th style="min-width: 320px;" class="font-weight-bold">Daily Remark (hr.attendanceremarks)</th>
              <th style="width: 110px;" class="text-center font-weight-bold">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($list as $i => $row)
              @php
                $code = strtoupper(trim($row['code'] ?? ''));
                $dayCarbon = \Carbon\Carbon::parse($date);
                $isWeekend = $dayCarbon->isWeekend() || $code === 'Z';
                $codeLabel = match($code) {
                  'P' => 'Present',
                  'W' => 'Work from Home',
                  'T' => 'Ty Duty',
                  'A' => 'Absent',
                  'L' => 'Leave',
                  'U' => 'Unpaid Leave',
                  'N' => 'Not Applicable',
                  'Z' => 'Weekend / Holiday',
                  default => 'Not Marked'
                };
                $codeStyle = match($code) {
                  'P' => 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;',
                  'A' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;',
                  'L' => 'background: #fef3c7; color: #b45309; border: 1px solid #fcd34d;',
                  'W' => 'background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc;',
                  'T' => 'background: #e0e7ff; color: #4338ca; border: 1px solid #a5b4fc;',
                  'U' => 'background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa;',
                  'N' => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                  'Z' => 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;',
                  default => 'background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;'
                };
              @endphp
              <tr style="background: #ffffff; color: #0f172a;">
                <td class="text-center align-middle font-weight-bold" style="color: #64748b;">{{ $i + 1 }}</td>
                <td class="align-middle font-weight-bold font-monospace" style="color: #0369a1;">{{ $row['emp_id'] }}</td>
                <td class="align-middle font-weight-bold" style="color: #0f172a !important;">{{ $row['name'] }}</td>
                <td class="text-center align-middle">
                  @if($isWeekend)
                    <span class="badge px-2 py-1 font-weight-bold" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                      <i class="fas fa-calendar-times mr-1"></i> Weekend / Holiday
                    </span>
                  @elseif($code !== '')
                    <span class="badge px-2 py-1 font-weight-bold" style="font-size: 12px; {{ $codeStyle }}">
                      <strong>{{ $code }}</strong> - {{ $codeLabel }}
                    </span>
                  @else
                    <span class="badge px-2 py-1" style="background: #f1f5f9; color: #94a3b8;">Not Marked</span>
                  @endif
                </td>
                <td class="text-center align-middle">
                  @if($isWeekend)
                    <span class="badge px-2 py-1 text-xs font-weight-bold" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">
                      <i class="fas fa-ban mr-1"></i> Non-Working
                    </span>
                  @elseif($row['is_locked'])
                    <span class="badge px-2 py-1 text-xs font-weight-bold" style="background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;" title="Period is locked against edits">
                      <i class="fas fa-lock mr-1 text-warning"></i> Locked
                    </span>
                  @else
                    <span class="badge px-2 py-1 text-xs font-weight-bold" style="background: #f0fdf4; color: #166534; border: 1px solid #86efac;">
                      <i class="fas fa-unlock mr-1"></i> Open
                    </span>
                  @endif
                </td>
                <td class="align-middle">
                  @if($row['att_id'])
                    <div class="input-group input-group-sm">
                      <input type="text"
                             class="form-control remark-input"
                             style="background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 4px;"
                             value="{{ $row['remarks'] }}"
                             placeholder="Enter remark..."
                             data-att-id="{{ $row['att_id'] }}"
                             data-day="{{ $day }}"
                             {{ ($row['is_locked'] || $isWeekend) ? 'disabled' : '' }}>
                      <div class="input-group-append">
                        <button class="btn btn-outline-primary btn-save-remark" type="button" {{ ($row['is_locked'] || $isWeekend) ? 'disabled' : '' }} title="Save Remark">
                          <i class="fas fa-save"></i>
                        </button>
                      </div>
                    </div>
                  @else
                    <span class="text-muted small"><em>No attendance record</em></span>
                  @endif
                </td>
                <td class="text-center align-middle">
                  <button type="button" class="btn btn-xs btn-outline-info btn-view-summary" data-emp-id="{{ $row['emp_id'] }}" title="View Monthly Summary">
                    <i class="fas fa-chart-pie mr-1"></i> Summary
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                  <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                  No employee records found in your unit scope for this date.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Include Monthly Summary Modal --}}
@include('hr.attendance.partials.summary_modal')
@endsection

@section('scripts')
<script>
$(document).ready(function() {
  // Save daily remark
  function saveRemark($input) {
    const attId = $input.data('att-id');
    const day = $input.data('day');
    const remark = $input.val();
    const $btn = $input.closest('.input-group').find('.btn-save-remark');

    $btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

    $.ajax({
      url: "{{ route('divhr.attendance.save_remark') }}",
      type: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        att_id: attId,
        day: day,
        remarks: remark
      },
      success: function() {
        $btn.html('<i class="fas fa-check text-success"></i>');
        $input.addClass('border-success');
        setTimeout(() => {
          $btn.html('<i class="fas fa-save"></i>').prop('disabled', false);
          $input.removeClass('border-success');
        }, 1500);
      },
      error: function(xhr) {
        $btn.html('<i class="fas fa-times text-danger"></i>').prop('disabled', false);
        const msg = xhr.responseJSON?.message || 'Error saving remark.';
        Swal.fire({
          icon: 'error',
          title: 'Save Failed',
          text: msg
        });
      }
    });
  }

  $('.btn-save-remark').on('click', function() {
    const $input = $(this).closest('.input-group').find('.remark-input');
    saveRemark($input);
  });

  $('.remark-input').on('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      saveRemark($(this));
    }
  });

  // Summary Modal trigger
  $('.btn-view-summary').on('click', function() {
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
          text: xhr.responseJSON?.error || 'Could not load summary.'
        });
        $('#summaryModal').modal('hide');
      }
    });
  });
});
</script>
@endsection
