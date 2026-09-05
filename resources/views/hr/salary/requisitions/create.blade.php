{{-- resources/views/hr/salary/requisitions/create.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper px-3 py-3" style="background: #f4f6f9;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="font-weight-bold mb-0 text-dark" style="font-family: 'Rajdhani', sans-serif;">
        <i class="fas fa-plus-circle text-success mr-2"></i>Generate Salary Requisitions
      </h3>
      <div class="text-muted small">
        <a href="{{ route('divhr.attendance') }}" class="text-muted">HR</a> / 
        <a href="{{ route('divhr.salary.requisitions.index') }}" class="text-muted">Requisitions</a> / 
        <strong class="text-success">New Generation</strong>
      </div>
    </div>
    <div>
      <a href="{{ route('divhr.salary.requisitions.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold">
        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
      </a>
    </div>
  </div>

  {{-- Duplicate Error Banner --}}
  <div id="duplicate-error-box" class="alert alert-danger shadow-sm py-3 px-4 border-0 mb-3" style="display: none; background: #fef2f2; color: #991b1b; border-left: 5px solid #dc2626 !important; border-radius: 8px;">
    <div class="d-flex align-items-start">
      <i class="fas fa-exclamation-triangle fa-2x mr-3 text-danger mt-1"></i>
      <div class="flex-grow-1">
        <h5 class="font-weight-bold mb-1" id="duplicate-error-title">Duplicate Requisition Detected</h5>
        <div class="small mb-2">The duplicate prevention guard rejected this request. The following employee(s) already have an open requisition in Draft, In Process, or Fulfilled status:</div>
        <div class="table-responsive bg-white rounded border" style="max-height: 200px; overflow-y: auto;">
          <table class="table table-sm table-bordered mb-0 small">
            <thead class="bg-light">
              <tr>
                <th>Emp ID</th>
                <th>Name</th>
                <th>Period</th>
                <th>Reason</th>
              </tr>
            </thead>
            <tbody id="duplicate-conflict-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Selection Card --}}
  <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
    <div class="card-body p-3">
      <div class="row align-items-end">
        <div class="col-md-3 mb-2 mb-md-0">
          <label class="small font-weight-bold text-dark">Salary Month <span class="text-danger">*</span></label>
          <input type="month" id="select-month" class="form-control form-control-sm" value="{{ $month }}">
        </div>
        <div class="col-md-4 mb-2 mb-md-0">
          <label class="small font-weight-bold text-dark">Unit Scope (Optional)</label>
          <select id="select-unit" class="form-control form-control-sm">
            <option value="">All Permitted Units (In User Horizon)</option>
            @foreach($units as $u)
              <option value="{{ $u->unt_id }}" {{ $unitId == $u->unt_id ? 'selected' : '' }}>
                {{ $u->unt_namesh }} (Unit {{ $u->unt_id }})
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-5 text-md-right mt-2 mt-md-0 d-flex justify-content-md-end" style="gap: 8px;">
          <button type="button" id="btn-load-preview" class="btn btn-sm btn-primary font-weight-bold px-3">
            <i class="fas fa-search mr-1"></i> Scan Candidates & Audit 7 Checks
          </button>
          <button type="button" id="btn-view-preview-modal" class="btn btn-sm btn-outline-info font-weight-bold" style="display: none;">
            <i class="fas fa-list-alt mr-1"></i> View Exclusion Audit
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Loading State --}}
  <div id="preview-loader" class="text-center py-5" style="display: none;">
    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
      <span class="sr-only">Loading...</span>
    </div>
    <div class="mt-3 font-weight-bold text-muted">Auditing candidates against 7 legacy exclusion checks...</div>
  </div>

  {{-- Results Container --}}
  <div id="results-container" style="display: none;">
    {{-- Summary Banner --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-md-7">
            <h5 class="font-weight-bold mb-1" style="color: #0f172a;">
              Eligible Candidates for <span id="summary-month-label" class="text-primary">-</span>
            </h5>
            <div class="small text-muted">
              Found <strong id="cnt-eligible" class="text-success">0</strong> eligible candidates out of <strong id="cnt-total">0</strong> scanned.
              (<strong id="cnt-excluded" class="text-danger">0</strong> excluded by 7-check audit).
            </div>
          </div>
          <div class="col-md-5 text-md-right mt-2 mt-md-0">
            <button type="button" id="btn-generate-salary" class="btn btn-success font-weight-bold px-4 shadow-sm" disabled>
              <i class="fas fa-check-circle mr-1"></i> Generate Requisitions (<span id="btn-selected-count">0</span>)
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Candidates Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 8px; background: #ffffff;">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-striped mb-0 align-middle">
            <thead style="background: #f8fafc; color: #1e293b; border-bottom: 2px solid #cbd5e1;">
              <tr>
                <th style="width: 40px;" class="text-center">
                  <input type="checkbox" id="check-all-candidates">
                </th>
                <th style="width: 130px;">Employee ID</th>
                <th>Name / Designation</th>
                <th>Unit</th>
                <th class="text-right">Contract Salary</th>
                <th class="text-right">Deductions</th>
                <th class="text-right">Net Salary</th>
                <th>Bank Match Rule</th>
              </tr>
            </thead>
            <tbody id="eligible-candidates-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Include Preview Modal --}}
@include('hr.salary.partials.salary_preview_modal')

<script>
$(document).ready(function() {
  let currentPreviewData = null;

  $('#btn-load-preview').on('click', function() {
    const month = $('#select-month').val();
    const unitId = $('#select-unit').val();

    if (!month) {
      Swal.fire({ icon: 'warning', title: 'Select Month', text: 'Please select a salary month first.' });
      return;
    }

    $('#duplicate-error-box').hide();
    $('#results-container').hide();
    $('#preview-loader').fadeIn();
    $('#btn-load-preview').prop('disabled', true);

    $.ajax({
      url: "{{ route('divhr.salary.preview') }}",
      type: "GET",
      data: { month: month, unit_id: unitId },
      success: function(res) {
        $('#preview-loader').hide();
        $('#btn-load-preview').prop('disabled', false);
        currentPreviewData = res;

        renderPreviewResults(res);
        $('#btn-view-preview-modal').show();
        $('#results-container').fadeIn();
      },
      error: function(xhr) {
        $('#preview-loader').hide();
        $('#btn-load-preview').prop('disabled', false);
        Swal.fire({
          icon: 'error',
          title: 'Audit Failed',
          text: xhr.responseJSON?.message || 'Could not audit salary candidates.',
          background: '#ffffff',
          color: '#0f172a'
        });
      }
    });
  });

  function renderPreviewResults(data) {
    const included = data.included || [];
    const excluded = data.excluded || [];
    const counts = data.counts || {};

    $('#summary-month-label').text(data.month);
    $('#cnt-eligible').text(counts.eligible || included.length);
    $('#cnt-total').text(counts.total_candidates || (included.length + excluded.length));
    $('#cnt-excluded').text(counts.excluded || excluded.length);

    // Populate Eligible Table
    const $tbody = $('#eligible-candidates-tbody');
    $tbody.empty();

    if (included.length === 0) {
      $tbody.append('<tr><td colspan="8" class="text-center text-muted py-4">No eligible employees found for this month and unit. Click "View Exclusion Audit" to check reasons.</td></tr>');
      $('#check-all-candidates').prop('disabled', true);
      $('#btn-generate-salary').prop('disabled', true);
      return;
    }

    $('#check-all-candidates').prop('disabled', false).prop('checked', true);

    included.forEach(function(item) {
      const emp = item.employee;
      const b = item.breakdown[0] || {};
      const isMeezan = b.bnkaccdetail && b.bnkaccdetail !== '(Pay by Cheque)';
      const bankBadge = isMeezan
        ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-university mr-1"></i> ' + b.bnkaccdetail + '</span>'
        : '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-money-check mr-1"></i> ' + (b.bnkaccdetail || 'Pay by Cheque') + '</span>';

      const row = `
        <tr>
          <td class="text-center">
            <input type="checkbox" class="candidate-checkbox" value="${emp.emp_id}" checked>
          </td>
          <td class="font-monospace font-weight-bold" style="color: #0369a1;">${emp.emp_id}</td>
          <td>
            <div class="font-weight-bold text-dark">${emp.emp_name}</div>
            <div class="small text-muted">${emp.emp_rank || ''} ${emp.emp_title || ''}</div>
          </td>
          <td class="small font-weight-bold text-secondary">Unit ${emp.emp_unt_id}</td>
          <td class="text-right font-monospace">${Number(b.ctrsalary || 0).toLocaleString()}</td>
          <td class="text-right font-monospace text-danger">${Number(b.underwork || 0).toLocaleString()}</td>
          <td class="text-right font-monospace font-weight-bold" style="color: #15803d; font-size: 1rem;">
            ${Number(item.total_salary || 0).toLocaleString()}
          </td>
          <td>${bankBadge}</td>
        </tr>
      `;
      $tbody.append(row);
    });

    updateSelectedCount();

    // Populate Modal Tables
    $('#preview-total-count').text(counts.total_candidates || (included.length + excluded.length));
    $('#preview-eligible-count, #badge-eligible-count').text(counts.eligible || included.length);
    $('#preview-excluded-count, #badge-excluded-count').text(counts.excluded || excluded.length);

    // Modal Eligible Body
    const $mEligible = $('#preview-eligible-tbody');
    $mEligible.empty();
    included.forEach(function(item, idx) {
      const emp = item.employee;
      const b = item.breakdown[0] || {};
      $mEligible.append(`
        <tr>
          <td class="text-center">${idx + 1}</td>
          <td class="font-monospace">${emp.emp_id}</td>
          <td class="font-weight-bold">${emp.emp_name}</td>
          <td>Unit ${emp.emp_unt_id}</td>
          <td class="text-right font-monospace">${Number(b.ctrsalary || 0).toLocaleString()}</td>
          <td class="text-right font-monospace text-danger">${Number(b.underwork || 0).toLocaleString()}</td>
          <td class="text-right font-monospace font-weight-bold text-success">${Number(item.total_salary || 0).toLocaleString()}</td>
          <td>${b.bnkaccdetail || '-'}</td>
        </tr>
      `);
    });

    // Modal Excluded Body
    renderExcludedTable(excluded, 'all');
  }

  function renderExcludedTable(excludedList, reasonFilter) {
    const $mExcluded = $('#preview-excluded-tbody');
    $mExcluded.empty();

    const filtered = (reasonFilter === 'all')
      ? excludedList
      : excludedList.filter(x => x.reason === reasonFilter);

    if (filtered.length === 0) {
      $mExcluded.append('<tr><td colspan="5" class="text-center text-muted py-3">No candidates match this exclusion filter.</td></tr>');
      return;
    }

    filtered.forEach(function(item, idx) {
      const emp = item.employee;
      $mExcluded.append(`
        <tr>
          <td class="text-center">${idx + 1}</td>
          <td class="font-monospace">${emp.emp_id}</td>
          <td class="font-weight-bold">${emp.emp_name}</td>
          <td>Unit ${emp.emp_unt_id}</td>
          <td><span class="badge badge-danger px-2 py-1">${item.reason}</span></td>
        </tr>
      `);
    });
  }

  $('#filter-exclusion-reason').on('change', function() {
    if (currentPreviewData) {
      renderExcludedTable(currentPreviewData.excluded || [], $(this).val());
    }
  });

  $('#btn-view-preview-modal').on('click', function() {
    $('#salaryPreviewModal').modal('show');
  });

  // Checkbox handlers
  $('#check-all-candidates').on('change', function() {
    $('.candidate-checkbox').prop('checked', $(this).is(':checked'));
    updateSelectedCount();
  });

  $(document).on('change', '.candidate-checkbox', function() {
    updateSelectedCount();
  });

  function updateSelectedCount() {
    const count = $('.candidate-checkbox:checked').length;
    $('#btn-selected-count').text(count);
    $('#btn-generate-salary').prop('disabled', count === 0);
  }

  // Generation Submission
  $('#btn-generate-salary').on('click', function() {
    const selectedIds = [];
    $('.candidate-checkbox:checked').each(function() {
      selectedIds.push($(this).val());
    });

    if (selectedIds.length === 0) return;

    const month = $('#select-month').val();

    Swal.fire({
      title: 'Generate Salary Requisitions?',
      text: `Are you sure you want to generate ${selectedIds.length} salary requisition(s) for ${month}?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, Generate',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#16a34a'
    }).then((result) => {
      if (result.isConfirmed) {
        $('#duplicate-error-box').hide();
        $('#btn-generate-salary').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating...');

        $.ajax({
          url: "{{ route('divhr.salary.requisitions.generate') }}",
          type: "POST",
          data: {
            _token: "{{ csrf_token() }}",
            month: month,
            emp_ids: selectedIds
          },
          success: function(res) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: res.message || 'Requisitions generated successfully.',
            }).then(() => {
              window.location.href = "{{ route('divhr.salary.requisitions.index') }}";
            });
          },
          error: function(xhr) {
            $('#btn-generate-salary').prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Generate Requisitions (' + selectedIds.length + ')');

            if (xhr.status === 422 && xhr.responseJSON?.conflicts) {
              // Surface duplicate rejection details explicitly
              const conflicts = xhr.responseJSON.conflicts;
              const $tbody = $('#duplicate-conflict-tbody');
              $tbody.empty();

              conflicts.forEach(function(c) {
                $tbody.append(`
                  <tr>
                    <td class="font-monospace font-weight-bold text-danger">${c.emp_id}</td>
                    <td class="font-weight-bold">${c.name}</td>
                    <td>${c.period}</td>
                    <td>${c.reason}</td>
                  </tr>
                `);
              });

              $('#duplicate-error-box').fadeIn();
              $('html, body').animate({ scrollTop: $('#duplicate-error-box').offset().top - 70 }, 300);
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Generation Rejected',
                text: xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to generate requisitions.'
              });
            }
          }
        });
      }
    });
  });

  // Auto-scan candidates on load if month is specified
  if ($('#select-month').val()) {
    $('#btn-load-preview').trigger('click');
  }
});
</script>
@endsection
