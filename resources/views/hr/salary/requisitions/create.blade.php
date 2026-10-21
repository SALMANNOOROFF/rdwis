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
        <div class="col-md-4 mb-2 mb-md-0">
          <label class="small font-weight-bold text-dark">Salary Month <span class="text-danger">*</span></label>
          <input type="month" id="select-month" class="form-control form-control-sm font-weight-bold" 
                 value="{{ now()->format('Y-m') }}" 
                 min="{{ now()->format('Y-m') }}" 
                 max="{{ now()->format('Y-m') }}" 
                 readonly 
                 style="background: #f1f5f9; cursor: not-allowed;">
          <small class="text-muted"><i class="fas fa-lock mr-1 text-secondary"></i> Current Active Month ({{ now()->format('F Y') }})</small>
        </div>
        <div class="col-md-5 mb-2 mb-md-0">
          <label class="small font-weight-bold text-dark d-block">Division Scope</label>
          <div class="d-flex align-items-center" style="gap: 8px;">
            <span class="badge badge-primary px-3 py-2 font-weight-bold shadow-sm" style="font-size: 13px; letter-spacing: 0.5px;">
              <i class="fas fa-building mr-1"></i> {{ Auth::user()->acc_untname ?? (Auth::user()->acc_untnamesh ?? 'Division') }}
            </span>
            <span class="badge badge-light border text-muted px-2 py-1 font-monospace">Unit #{{ Auth::user()->acc_unt_id }}</span>
          </div>
        </div>
        <div class="col-md-3 text-md-right mt-2 mt-md-0 d-flex justify-content-md-end" style="gap: 8px;">
          <button type="button" id="btn-load-preview" class="btn btn-sm btn-primary font-weight-bold px-3">
            <i class="fas fa-sync-alt mr-1"></i> Refresh Scan
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
    {{-- Summary Banner & Eligible Section --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px; background: #ffffff;">
      <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
        <div>
          <h5 class="font-weight-bold mb-1" style="color: #0f172a;">
            <i class="fas fa-user-check text-success mr-2"></i>Eligible Candidates for <span id="summary-month-label" class="text-primary">-</span>
          </h5>
          <div class="small text-muted">
            Found <strong id="cnt-eligible" class="text-success">0</strong> eligible candidate(s) out of <strong id="cnt-total">0</strong> scanned.
            (<strong id="cnt-excluded" class="text-danger">0</strong> excluded by audit).
            <span id="cnt-budget-warnings-badge" class="badge badge-warning text-dark ml-2 px-2 py-1 shadow-sm" style="display:none; background: #fef08a; border: 1px solid #facc15;">
              <i class="fas fa-exclamation-triangle text-danger mr-1"></i> <strong id="cnt-budget-warnings">0</strong> budget warning(s)
            </span>
          </div>
        </div>
        <div>
          <button type="button" id="btn-generate-salary" class="btn btn-success font-weight-bold px-4 shadow-sm" disabled>
            <i class="fas fa-check-circle mr-1"></i> Generate Requisitions (<span id="btn-selected-count">0</span>)
          </button>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-striped mb-0 align-middle">
            <thead style="background: #f8fafc; color: #1e293b; border-bottom: 2px solid #cbd5e1;">
              <tr>
                <th style="width: 40px;" class="text-center">
                  <input type="checkbox" id="check-all-candidates">
                </th>
                <th style="width: 125px;">Employee ID</th>
                <th>Employee Name</th>
                <th>Grade</th>
                <th class="text-right">Contract Salary</th>
                <th class="text-right">Deductions</th>
                <th class="text-right">Arrears</th>
                <th class="text-right">Net Salary</th>
                <th>Bank Match Rule</th>
              </tr>
            </thead>
            <tbody id="eligible-candidates-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Excluded Candidates Section (Inline Below, No Modal) --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px; background: #ffffff;">
      <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
        <div>
          <h6 class="font-weight-bold mb-0 text-danger">
            <i class="fas fa-user-times mr-2"></i>Excluded Candidates (<span id="cnt-excluded-bottom">0</span>)
          </h6>
          <div class="small text-muted mt-1">Candidates ineligible for requisition generation based on legacy compliance audit.</div>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
          <label class="small mb-0 font-weight-bold text-muted text-nowrap">Filter by Reason:</label>
          <select id="filter-exclusion-reason" class="form-control form-control-sm font-weight-bold" style="width: 220px;">
            <option value="all">All Reasons</option>
            <option value="Already Generated">Already Generated</option>
            <option value="Salary Head Not Set">Salary Head Not Set</option>
            <option value="Contract Not Verified">Contract Not Verified</option>
            <option value="Inactive Status">Inactive Status</option>
            <option value="Future Month">Future Month</option>
            <option value="Outside Division Scope">Outside Division Scope</option>
          </select>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-striped mb-0 align-middle">
            <thead style="background: #fff1f2; color: #9f1239; border-bottom: 2px solid #fecdd3;">
              <tr>
                <th style="width: 45px;" class="text-center">#</th>
                <th style="width: 125px;">Employee ID</th>
                <th>Employee Name</th>
                <th>Grade</th>
                <th>Exclusion Reason</th>
              </tr>
            </thead>
            <tbody id="excluded-candidates-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
  function initSalaryScan() {
    if (typeof jQuery === 'undefined') {
      setTimeout(initSalaryScan, 50);
      return;
    }

    const $ = jQuery;
    let currentPreviewData = null;

    function notify(type, title, message) {
      if (typeof Swal !== 'undefined' && Swal.fire) {
        Swal.fire({
          icon: type,
          title: title,
          text: message,
          background: '#ffffff',
          color: '#0f172a'
        });
      } else {
        alert(title + "\n\n" + message);
      }
    }

    function confirmModal(title, text, confirmBtnText) {
      if (typeof Swal !== 'undefined' && Swal.fire) {
        return Swal.fire({
          title: title,
          text: text,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: confirmBtnText || 'Yes, Generate',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#16a34a'
        });
      } else {
        const ok = window.confirm(title + "\n\n" + text);
        return Promise.resolve({ isConfirmed: ok });
      }
    }

    function formatGrade(emp, b) {
      if (b && b.grade && b.grade !== '-') return b.grade;
      if (emp && emp.emp_grade && emp.emp_grade !== '-') return emp.emp_grade;
      if (emp && emp.emp_rank && emp.emp_rank !== '-') return emp.emp_rank;
      return '<span class="text-muted font-italic">-</span>';
    }

    function loadCandidatesPreview() {
      const month = $('#select-month').val() || "{{ now()->format('Y-m') }}";

      $('#duplicate-error-box').hide();
      $('#results-container').hide();
      $('#preview-loader').show();
      $('#btn-load-preview').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Scanning...');

      $.ajax({
        url: "{{ route('divhr.salary.preview', [], false) }}",
        type: "GET",
        data: { month: month },
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(res) {
          $('#preview-loader').hide();
          $('#btn-load-preview').prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Refresh Scan');
          currentPreviewData = res;

          renderPreviewResults(res);
          $('#results-container').fadeIn();
        },
        error: function(xhr) {
          $('#preview-loader').hide();
          $('#btn-load-preview').prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Refresh Scan');
          const err = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Could not audit salary candidates.';
          notify('error', 'Audit Failed', err);
        }
      });
    }

    function renderPreviewResults(data) {
      const included = data.included || [];
      const excluded = data.excluded || [];
      const counts = data.counts || {};

      $('#summary-month-label').text(data.month || $('#select-month').val());
      $('#cnt-eligible').text(counts.eligible ?? included.length);
      $('#cnt-total').text(counts.total_candidates ?? (included.length + excluded.length));
      $('#cnt-excluded, #cnt-excluded-bottom').text(counts.excluded ?? excluded.length);

      if (counts.budget_warnings && counts.budget_warnings > 0) {
        $('#cnt-budget-warnings').text(counts.budget_warnings);
        $('#cnt-budget-warnings-badge').fadeIn();
      } else {
        $('#cnt-budget-warnings-badge').hide();
      }

      // Populate Eligible Candidates Table
      const $tbody = $('#eligible-candidates-tbody');
      $tbody.empty();

      if (included.length === 0) {
        $tbody.append('<tr><td colspan="9" class="text-center text-muted py-4 font-weight-bold">No eligible employees found for this month and unit. Check reasons in the Excluded Candidates section below.</td></tr>');
        $('#check-all-candidates').prop('disabled', true);
        $('#btn-generate-salary').prop('disabled', true);
      } else {
        $('#check-all-candidates').prop('disabled', false).prop('checked', true);

        included.forEach(function(item) {
          const emp = item.employee || {};
          const b = (item.breakdown && item.breakdown[0]) ? item.breakdown[0] : {};
          const isMeezan = b.bnkaccdetail && b.bnkaccdetail !== '(Pay by Cheque)';
          const bankBadge = isMeezan
            ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-university mr-1"></i> ' + b.bnkaccdetail + '</span>'
            : '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-money-check mr-1"></i> ' + (b.bnkaccdetail || 'Pay by Cheque') + '</span>';

          const budgetWarningHtml = item.budget_objection ? `
            <div class="mt-1">
              <span class="badge badge-warning text-dark font-weight-bold px-2 py-1 shadow-sm" style="background: #fef08a; border: 1px solid #facc15; font-size: 11px; white-space: normal; text-align: left;" title="Budget Objection">
                <i class="fas fa-exclamation-triangle text-danger mr-1"></i> ${item.budget_objection}
              </span>
            </div>
          ` : '';

          const arrearsVal = Number(b.arrears || 0);
          const arrearsHtml = arrearsVal > 0
            ? `<span class="font-monospace text-primary font-weight-bold">+${arrearsVal.toLocaleString()}</span>`
            : `<span class="font-monospace text-muted">0</span>`;

          const underworkVal = Number(b.underwork || 0);
          const underworkHtml = underworkVal > 0
            ? `<span class="font-monospace text-danger">-${underworkVal.toLocaleString()}</span>`
            : `<span class="font-monospace text-muted">0</span>`;

          const row = `
            <tr>
              <td class="text-center">
                <input type="checkbox" class="candidate-checkbox" value="${emp.emp_id}" checked>
              </td>
              <td class="font-monospace font-weight-bold" style="color: #0369a1;">${emp.emp_id}</td>
              <td>
                <div class="font-weight-bold text-dark">${emp.emp_name || '-'}</div>
                ${budgetWarningHtml}
              </td>
              <td class="font-weight-bold text-dark font-monospace">${formatGrade(emp, b)}</td>
              <td class="text-right font-monospace">${Number(b.ctrsalary || 0).toLocaleString()}</td>
              <td class="text-right">${underworkHtml}</td>
              <td class="text-right">${arrearsHtml}</td>
              <td class="text-right font-monospace font-weight-bold" style="color: #15803d; font-size: 1rem;">
                ${Number(item.total_salary || 0).toLocaleString()}
              </td>
              <td>${bankBadge}</td>
            </tr>
          `;
          $tbody.append(row);
        });

        updateSelectedCount();
      }

      // Populate Excluded Candidates Table (Inline Below)
      renderExcludedTable(excluded, $('#filter-exclusion-reason').val() || 'all');
    }

    function renderExcludedTable(excludedList, reasonFilter) {
      const $mExcluded = $('#excluded-candidates-tbody');
      $mExcluded.empty();

      const filtered = (reasonFilter === 'all')
        ? excludedList
        : excludedList.filter(x => x.reason === reasonFilter);

      if (filtered.length === 0) {
        $mExcluded.append('<tr><td colspan="5" class="text-center text-muted py-3 font-italic">No candidates match this exclusion filter.</td></tr>');
        return;
      }

      filtered.forEach(function(item, idx) {
        const emp = item.employee || {};
        $mExcluded.append(`
          <tr>
            <td class="text-center font-monospace text-muted small">${idx + 1}</td>
            <td class="font-monospace font-weight-bold" style="color: #991b1b;">${emp.emp_id || '-'}</td>
            <td class="font-weight-bold text-dark">${emp.emp_name || '-'}</td>
            <td class="font-weight-bold text-dark font-monospace">${formatGrade(emp)}</td>
            <td>
              <span class="badge badge-danger px-2 py-1 shadow-sm" style="font-size: 11px;">
                <i class="fas fa-exclamation-circle mr-1"></i> ${item.reason}
              </span>
            </td>
          </tr>
        `);
      });
    }

    $('#filter-exclusion-reason').on('change', function() {
      if (currentPreviewData) {
        renderExcludedTable(currentPreviewData.excluded || [], $(this).val());
      }
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

    // Refresh button click
    $('#btn-load-preview').on('click', function(e) {
      e.preventDefault();
      loadCandidatesPreview();
    });

    // Generation Submission
    $('#btn-generate-salary').on('click', function() {
      const selectedIds = [];
      $('.candidate-checkbox:checked').each(function() {
        selectedIds.push($(this).val());
      });

      if (selectedIds.length === 0) return;

      const month = $('#select-month').val() || "{{ now()->format('Y-m') }}";

      confirmModal(
        'Generate Salary Requisitions?',
        `Are you sure you want to generate ${selectedIds.length} salary requisition(s) for ${month}?`,
        'Yes, Generate'
      ).then((result) => {
        if (result.isConfirmed) {
          $('#duplicate-error-box').hide();
          $('#btn-generate-salary').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Generating...');

          $.ajax({
            url: "{{ route('divhr.salary.requisitions.generate', [], false) }}",
            type: "POST",
            data: {
              _token: "{{ csrf_token() }}",
              month: month,
              emp_ids: selectedIds
            },
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(res) {
              notify('success', 'Success', res.message || 'Requisitions generated successfully.');
              setTimeout(function() {
                window.location.href = "{{ route('divhr.salary.requisitions.index', [], false) }}";
              }, 1200);
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
                notify('error', 'Generation Rejected', xhr.responseJSON?.error || xhr.responseJSON?.message || 'Failed to generate requisitions.');
              }
            }
          });
        }
      });
    });

    // Run preview scan immediately
    loadCandidatesPreview();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSalaryScan);
  } else {
    initSalaryScan();
  }
})();
</script>
@endpush
