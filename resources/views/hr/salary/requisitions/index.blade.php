{{-- resources/views/hr/salary/requisitions/index.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper px-3 py-3" style="background: #f4f6f9;">
  {{-- Header & Breadcrumbs --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="font-weight-bold mb-0 text-dark" style="font-family: 'Rajdhani', sans-serif;">
        <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>Salary Requisitions - {{ $status === 'Open' || $status === 'In Process' ? 'In Process' : ($status === 'Closed' ? 'Closed' : 'Draft') }}
      </h3>
      <div class="text-muted small">
        <a href="{{ route('divhr.attendance') }}" class="text-muted">HR</a> / 
        <span>Salary Pipeline</span> / 
        <strong class="text-primary">Requisitions</strong>
      </div>
    </div>
    <div class="d-flex align-items-center" style="gap: 8px;">
      <a href="{{ route('divhr.attendance', ['month' => $month ?? '']) }}" class="btn btn-sm btn-outline-secondary font-weight-bold">
        <i class="fas fa-arrow-left mr-1"></i> Back to Attendance
      </a>
      @if(strtolower(trim((string) (auth()->user()->acc_untarea ?? ''))) === 'fin')
      <a href="{{ route('divhr.salary.orders.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
        <i class="fas fa-receipt mr-1"></i> Salary Orders
      </a>
      <a href="{{ route('divhr.salary.commitments.verify') }}" class="btn btn-sm btn-outline-info font-weight-bold">
        <i class="fas fa-shield-alt mr-1"></i> Audit Commitments
      </a>
      @endif
      @if(in_array($status ?? '', ['Open', 'In Process'], true))
        <a href="{{ route('divhr.salary.requisitions.print', ['month' => $month, 'status' => $status]) }}" target="_blank" class="btn btn-sm btn-dark font-weight-bold shadow-sm" title="Print Salary Requisition Sheet (Legacy Parity)">
          <i class="fas fa-print mr-1"></i> Print Sheet
        </a>
      @endif
      <a href="{{ route('divhr.salary.requisitions.create') }}" class="btn btn-sm btn-success font-weight-bold shadow-sm">
        <i class="fas fa-plus-circle mr-1"></i> New Salary Requisition
      </a>
    </div>
  </div>

  {{-- Alert Messages --}}
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm py-2 px-3 small border-0 mb-3" style="background: #f0fdf4; color: #16a34a; border-left: 4px solid #16a34a !important;">
      <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
      <button type="button" class="close text-success" data-dismiss="alert">&times;</button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm py-2 px-3 small border-0 mb-3" style="background: #fef2f2; color: #dc2626; border-left: 4px solid #dc2626 !important;">
      <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
      <button type="button" class="close text-danger" data-dismiss="alert">&times;</button>
    </div>
  @endif

  {{-- Filter Bar --}}
  <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('divhr.salary.requisitions.index') }}" class="row align-items-center">
        <input type="hidden" name="status" value="{{ $status ?? 'Draft' }}">
        <div class="col-md-3 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">Filter Month</label>
          <input type="month" name="month" class="form-control form-control-sm" value="{{ $month ?? '' }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-7 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">Status Filter (srq_status)</label>
          <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
            <a href="{{ route('divhr.salary.requisitions.index', ['month' => $month, 'status' => 'Draft']) }}" class="btn btn-sm {{ ($status ?? 'Draft') === 'Draft' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-secondary' }}" title="Draft requisitions">
              Draft
            </a>
            <a href="{{ route('divhr.salary.requisitions.index', ['month' => $month, 'status' => 'Open']) }}" class="btn btn-sm {{ in_array($status, ['Open', 'In Process'], true) ? 'btn-info font-weight-bold' : 'btn-outline-secondary' }}" title="In Process requisitions">
              In Process <small class="text-muted d-none d-lg-inline">(Open)</small>
            </a>
            <a href="{{ route('divhr.salary.requisitions.index', ['month' => $month, 'status' => 'Closed']) }}" class="btn btn-sm {{ in_array($status, ['Closed', 'Fulfilled', 'Cancelled'], true) ? 'btn-danger font-weight-bold' : 'btn-outline-secondary' }}" title="Fulfilled & Cancelled requisitions">
              Closed <small class="text-muted d-none d-lg-inline">(Fulfilled / Cancelled)</small>
            </a>
          </div>
        </div>
        <div class="col-md-2 text-md-right mt-2 mt-md-0">
          <label class="d-none d-md-block small text-transparent mb-1">&nbsp;</label>
          <a href="{{ route('divhr.salary.requisitions.index', ['status' => 'Draft']) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-undo mr-1"></i> Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Requisitions Table --}}
  <div class="card border-0 shadow-sm" style="border-radius: 8px; background: #ffffff;">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size: 13px;">
          <thead style="background: #f8fafc; color: #475569; border-bottom: 2px solid #e2e8f0;">
            <tr>
              <th style="width: 100px;">Req ID</th>
              <th>Employee</th>
              <th>Project</th>
              <th>Contract Salary</th>
              <th>Payable</th>
              <th>Meezan Account Details</th>
              <th>Remarks</th>
              <th style="width: 150px;">Add. Remarks</th>
              <th class="text-center">Status (srq_status)</th>
              <th style="width: 140px;" class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($requisitions as $r)
              @php
                $statusColors = [
                    'Draft'      => 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;',
                    'In Process' => 'background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc;',
                    'Fulfilled'  => 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;',
                    'Cancelled'  => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;',
                ];
                $badgeStyle = $statusColors[$r->srq_status] ?? 'background: #f1f5f9; color: #475569;';
                $isFinUser = strtolower(trim((string) (auth()->user()->acc_untarea ?? ''))) === 'fin';
                $relDate = $r->srq_releasedtg 
                    ? \Carbon\Carbon::parse($r->srq_releasedtg)->format('d M y') 
                    : ($r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('d M y') : '');
                $salMonth = \Carbon\Carbon::parse($r->srq_month)->format('M y');
              @endphp
              <tr>
                {{-- 1. Req ID --}}
                <td>
                  <div class="font-weight-bold" style="color: #1e293b;">#{{ $r->srq_id }}</div>
                  @if($relDate)
                    <div class="small text-muted" style="font-size: 11px;">{{ $relDate }}</div>
                  @endif
                </td>

                {{-- 2. Employee --}}
                <td>
                  <div class="small text-muted font-monospace" style="font-size: 11.5px;">{{ $r->srq_emp_id }}</div>
                  <div class="font-weight-bold text-dark">{{ $r->employee->emp_name ?? ($r->srq_empnamecomp ?: 'N/A') }}</div>
                </td>

                {{-- 3. Project --}}
                <td>
                  @php
                    $prjHead = $r->effectiveHead ?: $r->head;
                    $prjHeadCode = $prjHead->hed_code ?? ($prjHead->hed_name ?? 'Central');
                    $prjUnit = $r->effectiveUnit ?: $r->unit;
                  @endphp
                  <div class="font-weight-bold text-dark font-monospace" style="font-size: 13.5px; letter-spacing: 0.5px;">{{ $prjHeadCode }}</div>
                  <div class="small text-muted" style="font-size: 11px;">{{ $prjUnit->unt_namesh ?? ($prjUnit->unt_name ?? '') }}</div>
                </td>

                {{-- 4. Contract Salary --}}
                <td>
                  <div class="font-weight-bold text-dark">{{ number_format($r->srq_ctrsalary ?: $r->srq_salary) }}</div>
                  <div class="small text-muted" style="font-size: 11px;">{{ $salMonth }}</div>
                </td>

                {{-- 5. Payable (Red bold font matching legacy Screenshot 2) --}}
                <td>
                  <div class="font-weight-bold text-danger" style="font-size: 14px;">{{ number_format($r->srq_salary) }}</div>
                </td>

                {{-- 6. Meezan Account Details --}}
                <td>
                  <div class="font-monospace text-dark font-weight-bold" style="font-size: 12px;">{{ $r->srq_bnkaccdetail ?: '(Pay by Cheque)' }}</div>
                  <div class="small text-muted text-truncate" style="max-width: 170px; font-size: 11.5px;">{{ $r->srq_bnkacctitle ?: ($r->employee->emp_name ?? '') }}</div>
                </td>

                {{-- 7. Remarks --}}
                <td>
                  <div class="small text-dark" style="font-size: 12px;">{{ $r->srq_remarks ?: '-' }}</div>
                </td>

                {{-- 8. Add. Remarks (Editable text input matching legacy) --}}
                <td>
                  <input type="text" 
                         class="form-control form-control-sm border-secondary-subtle" 
                         style="font-size: 12px; height: 28px;"
                         value="{{ $r->srq_remarks2 }}" 
                         placeholder="Add remarks..." 
                         data-id="{{ $r->srq_id }}"
                         onchange="saveSrqRemarks(this)">
                </td>

                {{-- 9. Status --}}
                <td class="text-center">
                  <span class="badge px-2 py-1 font-weight-bold" style="{{ $badgeStyle }}">
                    {{ $r->srq_status }}
                  </span>
                </td>

                {{-- 10. Actions (Draft: Release, Split & Cancel; In Process: Cancel only) --}}
                <td class="text-center">
                  @if($r->srq_status === 'Draft')
                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                      <form method="POST" action="{{ route('divhr.salary.requisitions.release', $r->srq_id) }}" class="d-inline" onsubmit="return confirm('Release requisition #{{ $r->srq_id }} to In Process?');">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-primary font-weight-bold px-2" title="Release to In Process">
                          Release
                        </button>
                      </form>

                      {{-- Split Button (Legacy Parity: Only un-split records can be split) --}}
                      @if(is_null($r->srq_parent))
                        <button type="button" class="btn btn-xs btn-outline-info font-weight-bold px-2 btn-trigger-split"
                                data-id="{{ $r->srq_id }}"
                                data-emp-name="{{ $r->employee->emp_name ?? ($r->srq_empnamecomp ?: 'N/A') }}"
                                data-emp-id="{{ $r->srq_emp_id }}"
                                data-unt-id="{{ $r->srq_unt_id }}"
                                data-is-csrf="{{ ($r->srq_unt_id >= 800000 || in_array((int)$r->srq_unt_id, [800000, 810000, 820000, 840000, 860000, 880000], true)) ? '1' : '0' }}"
                                data-month="{{ $salMonth }}"
                                data-total="{{ (float)$r->srq_salary }}"
                                data-ctr-salary="{{ (float)($r->srq_ctrsalary ?: $r->srq_salary) }}"
                                data-head-id="{{ $r->srq_effhed_id }}"
                                data-head-code="{{ $r->effectiveHead->hed_code ?? ($r->head->hed_code ?? 'N/A') }}"
                                data-head-name="{{ $r->effectiveHead->hed_name ?? ($r->head->hed_name ?? 'N/A') }}"
                                data-bank-acc="{{ $r->srq_bnkaccdetail ?: '(Pay by Cheque)' }}"
                                data-bank-title="{{ $r->srq_bnkacctitle ?: ($r->employee->emp_name ?? '') }}"
                                data-action="{{ route('divhr.salary.requisitions.split', $r->srq_id) }}"
                                title="Split Salary across Multiple Heads (Legacy hr_salreq_split)">
                          <i class="fas fa-cut mr-1"></i> Split
                        </button>
                      @elseif($r->srq_parent === 0)
                        <span class="badge badge-info text-white font-weight-bold px-1.5 py-1" style="font-size: 10px;" title="This requisition was split into multiple parts">
                          <i class="fas fa-sitemap mr-1"></i> Split Parent
                        </span>
                      @else
                        <span class="badge badge-light border text-primary font-weight-bold px-1.5 py-1" style="font-size: 10px;" title="Child split of Requisition #{{ $r->srq_parent }}">
                          <i class="fas fa-code-branch mr-1"></i> Child (#{{ $r->srq_parent }})
                        </span>
                      @endif

                      <button type="button" class="btn btn-xs btn-outline-secondary btn-trigger-cancel font-weight-bold px-2"
                              data-action="{{ route('divhr.salary.requisitions.cancel', $r->srq_id) }}"
                              data-desc="Salary Requisition #{{ $r->srq_id }} - {{ $r->srq_empnamecomp }} ({{ number_format($r->srq_salary) }})">
                        Cancel
                      </button>

                      {{-- Permanent Delete Draft Requisition (Unblocks immediate regeneration) --}}
                      <form method="POST" action="{{ route('divhr.salary.requisitions.destroy', $r->srq_id) }}" class="d-inline" onsubmit="return confirm('Permanently delete Draft Requisition #{{ $r->srq_id }} for {{ $r->employee->emp_name ?? ($r->srq_empnamecomp ?: 'Employee') }}?\n\nDeleting this draft immediately unblocks this employee so you can re-generate their salary requisition.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-danger font-weight-bold px-2" title="Delete Draft Requisition and re-generate salary">
                          <i class="fas fa-trash-alt mr-0.5"></i> Delete
                        </button>
                      </form>
                    </div>
                  @elseif($r->srq_status === 'In Process')
                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                      <span class="badge badge-light text-muted font-weight-normal border mr-1" title="Order generated for Finance">Submitted to Finance</span>
                      <button type="button" class="btn btn-xs btn-outline-danger btn-trigger-cancel font-weight-bold px-2"
                              data-action="{{ route('divhr.salary.requisitions.cancel', $r->srq_id) }}"
                              data-desc="Salary Requisition #{{ $r->srq_id }} - {{ $r->srq_empnamecomp }} ({{ number_format($r->srq_salary) }})">
                        Cancel
                      </button>
                    </div>
                  @elseif($r->srq_status === 'Fulfilled')
                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                      <span class="text-success small font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Fulfilled</span>
                    </div>
                  @else
                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                      <span class="text-muted small">Closed</span>
                    </div>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="10" class="text-center py-5 text-muted">
                  <i class="fas fa-inbox fa-3x mb-3 text-light" style="color: #cbd5e1 !important;"></i>
                  <div class="font-weight-bold">No salary requisitions found for the selected filters.</div>
                  <div class="small">Click "New Salary Requisition" above to initiate a requisition generation flow.</div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if($requisitions->hasPages())
        <div class="p-3 d-flex justify-content-between align-items-center border-top" style="border-color: #e2e8f0;">
          <div class="small text-muted">
            Showing {{ $requisitions->firstItem() }} to {{ $requisitions->lastItem() }} of {{ $requisitions->total() }} requisitions
          </div>
          <div>{{ $requisitions->appends(request()->query())->links() }}</div>
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Include Cancellation Modal --}}
@include('hr.salary.partials.cancel_modal')

{{-- Include Split Salary Modal (Legacy Parity) --}}
@include('hr.salary.partials.split_modal')

@endsection

@push('scripts')
<script>
// Available Project & Central Heads for Split Allocation
const splitCsrfHeads = @json($csrfHeads ?? []);
const splitDivHeads = @json($divHeads ?? []);
const isCentralSession = {{ ($isCentral ?? false) ? 'true' : 'false' }};
let activeSplitHeads = @json($heads ?? []);
let currentSplitIsCsrf = false;
let currentSplitOriginalSalary = 0;
let splitRowCounter = 0;
const cachedHeadBalances = {};

$(document).ready(function() {
  // Cancel button trigger using event delegation
  $(document).on('click', '.btn-trigger-cancel', function() {
    const actionUrl = $(this).data('action');
    const desc = $(this).data('desc');

    $('#cancelModalForm').attr('action', actionUrl);
    $('#cancel-target-desc').text(desc);
    $('#cancel-modal-title').text('Cancel Salary Requisition');
    $('#cancel-modal-warning').text('Cancelling this requisition will permanently set srq_status to Cancelled and free the employee for future requisitions.');
    $('#cancelModal').modal('show');
  });

  // Split button trigger using event delegation
  $(document).on('click', '.btn-trigger-split', function() {
    const btn = $(this);
    const reqId = btn.data('id');
    const empName = btn.data('emp-name');
    const empId = btn.data('emp-id');
    const month = btn.data('month');
    const total = parseFloat(btn.data('total')) || 0;
    const ctrSalary = parseFloat(btn.data('ctr-salary')) || total;
    const headCode = btn.data('head-code') || 'N/A';
    const headName = btn.data('head-name') || 'N/A';
    const bankAcc = btn.data('bank-acc') || '-';
    const bankTitle = btn.data('bank-title') || '';
    const actionUrl = btn.data('action');
    const isCsrf = btn.data('is-csrf') == '1' || btn.data('is-csrf') === 1;

    currentSplitIsCsrf = isCsrf;
    currentSplitOriginalSalary = total;
    splitRowCounter = 0;

    // Scope available heads strictly: CSRF for Central Dept, Division projects for Divisions
    activeSplitHeads = isCsrf ? splitCsrfHeads : splitDivHeads;

    const fundingBadge = $('#modal-split-funding-badge');
    if (isCsrf) {
      fundingBadge.removeClass('badge-secondary badge-primary').addClass('badge-warning text-dark')
                  .html('<i class="fas fa-shield-alt mr-1"></i> CSRF Funded (Central)').show();
    } else {
      fundingBadge.removeClass('badge-warning badge-secondary').addClass('badge-primary text-white')
                  .html('<i class="fas fa-project-diagram mr-1"></i> Division Budget').show();
    }

    $('#modal-split-req-id').text('#' + reqId);
    $('#modal-split-emp-name').text(empName);
    $('#modal-split-emp-id').text('Emp ID: ' + empId);
    $('#modal-split-month').text(month);
    $('#modal-split-ctr-salary').text('Rs. ' + Math.round(ctrSalary).toLocaleString());
    $('#modal-split-total-salary').text('Rs. ' + Math.round(total).toLocaleString());
    $('#modal-split-bank-account').text(bankAcc);
    $('#modal-split-bank-title').text(bankTitle);
    $('#modal-split-parent-head-name').text(headCode && headCode !== 'N/A' ? `[${headCode}]` : '-');
    $('#modal-split-parent-id-sub').text(reqId);
    $('#modal-split-footer-id').text(reqId);

    $('#splitSalaryModalForm').attr('action', actionUrl);

    // Reset rows and initialize with 1 child row
    $('#split-items-tbody').empty();
    
    // Set parent retained salary input max limit
    $('#split_parent_salary_input').attr('max', Math.round(total)).val(Math.round(total));

    addSplitRow('');

    recalcSplitTotals();
    $('#splitSalaryModal').modal('show');
  });

  // AJAX submission for split form
  $('#splitSalaryModalForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const actionUrl = form.attr('action');
    const submitBtn = $('#btn-submit-split');

    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving Split...');

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: form.serialize(),
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      success: function(res) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'success',
            title: 'Split Successful',
            text: res.message || 'Salary requisition splitted successfully.',
            timer: 2000,
            showConfirmButton: false
          }).then(() => location.reload());
        } else {
          alert(res.message || 'Salary requisition splitted successfully.');
          location.reload();
        }
      },
      error: function(xhr) {
        submitBtn.prop('disabled', false).html('<i class="fas fa-check-circle mr-1"></i> Save &amp; Split Requisition');
        const msg = xhr.responseJSON?.message || 'Failed to split salary requisition. Please check balances.';
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Split Error', text: msg });
        } else {
          alert('Error: ' + msg);
        }
      }
    });
  });
});

// Add dynamic split row with clean Head Code display
function addSplitRow(initialAmount = '') {
  splitRowCounter++;
  const rowId = 'split-row-' + splitRowCounter;
  const idx = $('#split-items-tbody tr').length;

  let optionsHtml = '<option value="">-- Choose Head Code --</option>';
  activeSplitHeads.forEach(h => {
    // Only display Head Code cleanly as requested
    optionsHtml += `<option value="${h.hed_id}">[${h.hed_code}]</option>`;
  });

  const rowHtml = `
    <tr id="${rowId}" class="split-child-row">
      <td class="text-center align-middle font-weight-bold text-muted">${idx + 1}</td>
      <td class="align-middle">
        <select name="splits[${idx}][effhed_id]" class="form-control form-control-sm select-split-head" required onchange="onSplitHeadChange(this)">
          ${optionsHtml}
        </select>
        <div class="head-balance-info small mt-1 font-monospace" id="${rowId}-balance" style="font-size: 11px; min-height: 15px;"></div>
      </td>
      <td class="align-middle">
        <div class="input-group input-group-sm">
          <div class="input-group-prepend">
            <span class="input-group-text font-weight-bold" style="font-size: 11px;">Rs.</span>
          </div>
          <input type="number" 
                 name="splits[${idx}][salary]" 
                 class="form-control form-control-sm text-right font-weight-bold input-split-amount" 
                 placeholder="0" 
                 min="1" 
                 max="${Math.round(currentSplitOriginalSalary)}" 
                 step="1" 
                 value="${initialAmount ? Math.round(initialAmount) : ''}" 
                 required 
                 oninput="onChildSplitAmountChange(this)">
        </div>
        <div class="split-amount-warning small text-danger font-weight-bold mt-1" id="${rowId}-warning" style="display:none; font-size: 10.5px;"></div>
      </td>
      <td class="text-center align-middle">
        <button type="button" class="btn btn-xs btn-outline-danger" title="Remove Split Row" onclick="removeSplitRow('${rowId}')">
          <i class="fas fa-trash-alt"></i>
        </button>
      </td>
    </tr>
  `;

  $('#split-items-tbody').append(rowHtml);
  reindexSplitRows();
  recalcSplitTotals();
}

// Fetch real-time available balance when head is chosen
function onSplitHeadChange(selectEl) {
  const headId = $(selectEl).val();
  const row = $(selectEl).closest('tr');
  const rowId = row.attr('id');
  const balanceContainer = $(`#${rowId}-balance`);

  if (!headId) {
    balanceContainer.empty();
    row.removeData('head-avail');
    recalcSplitTotals();
    return;
  }

  const cacheKey = headId + '_' + (currentSplitIsCsrf ? 'csrf' : 'div');
  if (cachedHeadBalances[cacheKey] !== undefined) {
    renderHeadBalance(row, cachedHeadBalances[cacheKey]);
    recalcSplitTotals();
    return;
  }

  balanceContainer.html('<span class="text-muted"><i class="fas fa-spinner fa-spin mr-1"></i> Checking funds...</span>');

  const checkUrl = '{{ url("divhr/salary/heads") }}/' + headId + '/balance?is_csrf=' + (currentSplitIsCsrf ? '1' : '0');
  $.ajax({
    url: checkUrl,
    type: 'GET',
    success: function(res) {
      cachedHeadBalances[cacheKey] = res.available;
      renderHeadBalance(row, res.available);
      recalcSplitTotals();
    },
    error: function() {
      balanceContainer.html('<span class="text-warning">Balance lookup unavailable</span>');
      row.removeData('head-avail');
      recalcSplitTotals();
    }
  });
}

function renderHeadBalance(row, avail) {
  const rowId = row.attr('id');
  const balanceContainer = $(`#${rowId}-balance`);
  row.data('head-avail', avail);

  const scopeLabel = currentSplitIsCsrf ? 'CSRF Avail' : 'Avail';
  if (avail > 0) {
    balanceContainer.html(`<span class="text-success font-weight-bold"><i class="fas fa-coins mr-1"></i> ${scopeLabel}: Rs. ${Math.round(avail).toLocaleString()}</span>`);
  } else {
    balanceContainer.html(`<span class="text-danger font-weight-bold"><i class="fas fa-times-circle mr-1"></i> ${scopeLabel}: Rs. 0 (Nil)</span>`);
  }
}

// Remove split row
function removeSplitRow(rowId) {
  const totalRows = $('#split-items-tbody tr').length;
  if (totalRows <= 1) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'warning', title: 'Minimum 1 Split Required', text: 'To split a requisition, you must have at least 1 child split portion.' });
    } else {
      alert('At least 1 child split portion is required.');
    }
    return;
  }
  $('#' + rowId).remove();
  reindexSplitRows();
  onChildSplitAmountChange();
}

// Reindex names after row removal
function reindexSplitRows() {
  $('#split-items-tbody tr').each(function(i) {
    $(this).find('td:first').text(i + 1);
    $(this).find('.select-split-head').attr('name', `splits[${i}][effhed_id]`);
    $(this).find('.input-split-amount').attr('name', `splits[${i}][salary]`);
  });
}

// When child split amount changes:
// 1. Guard against amount > total payable
// 2. Auto-adjust Parent salary: Parent Salary = Original Total - Sum(Child Salaries)
function onChildSplitAmountChange(inputEl) {
  if (inputEl) {
    let val = parseFloat($(inputEl).val()) || 0;
    if (val > currentSplitOriginalSalary) {
      val = currentSplitOriginalSalary;
      $(inputEl).val(Math.round(val));
    }
    if (val < 0) {
      $(inputEl).val(0);
    }
  }

  let childSum = 0;
  $('#split-items-tbody .input-split-amount').each(function() {
    childSum += parseFloat($(this).val()) || 0;
  });

  const autoParent = Math.max(0, currentSplitOriginalSalary - childSum);
  $('#split_parent_salary_input').val(Math.round(autoParent));
  recalcSplitTotals();
}

// Live calculation & balance verification
function recalcSplitTotals() {
  const parentAmt = parseFloat($('#split_parent_salary_input').val()) || 0;
  let childSum = 0;
  let allHeadsSelected = true;
  let hasInsufficientHead = false;

  $('#split-items-tbody tr').each(function() {
    const row = $(this);
    const rowId = row.attr('id');
    const headVal = row.find('.select-split-head').val();
    const amt = parseFloat(row.find('.input-split-amount').val()) || 0;
    const warningEl = $(`#${rowId}-warning`);

    if (!headVal) {
      allHeadsSelected = false;
      warningEl.hide();
    } else {
      const avail = row.data('head-avail');
      if (avail !== undefined && amt > avail) {
        hasInsufficientHead = true;
        warningEl.html(`<i class="fas fa-exclamation-triangle mr-1"></i> Insufficient balance! Available: Rs. ${Math.round(avail).toLocaleString()}`).show();
      } else {
        warningEl.hide();
      }
    }
    childSum += amt;
  });

  const totalAllocated = parentAmt + childSum;
  const diff = currentSplitOriginalSalary - totalAllocated;
  const isBalanced = Math.abs(diff) < 0.01 && parentAmt > 0 && childSum > 0 && allHeadsSelected && !hasInsufficientHead;

  $('#split-stat-original').text('Rs. ' + Math.round(currentSplitOriginalSalary).toLocaleString());
  $('#split-stat-allocated').text('Rs. ' + Math.round(totalAllocated).toLocaleString());
  $('#split-stat-diff').text('Rs. ' + Math.round(diff).toLocaleString());

  const badge = $('#split-status-badge');
  const submitBtn = $('#btn-submit-split');

  if (isBalanced) {
    badge.removeClass('badge-danger badge-warning badge-secondary').addClass('badge-success text-white')
         .html('<i class="fas fa-check-circle mr-1"></i> Balanced (Exact Match)');
    submitBtn.prop('disabled', false);
  } else {
    submitBtn.prop('disabled', true);
    if (hasInsufficientHead) {
      badge.removeClass('badge-success badge-warning badge-secondary').addClass('badge-danger text-white')
           .html('<i class="fas fa-exclamation-triangle mr-1"></i> Selected head has insufficient balance');
    } else if (!allHeadsSelected) {
      badge.removeClass('badge-success badge-danger badge-secondary').addClass('badge-warning text-dark')
           .html('<i class="fas fa-exclamation-triangle mr-1"></i> Select Head Code for each row');
    } else if (diff > 0) {
      badge.removeClass('badge-success badge-danger badge-secondary').addClass('badge-warning text-dark')
           .html(`<i class="fas fa-exclamation-circle mr-1"></i> Rs. ${Math.round(diff).toLocaleString()} Remaining Unallocated`);
    } else if (diff < 0) {
      badge.removeClass('badge-success badge-warning badge-secondary').addClass('badge-danger text-white')
           .html(`<i class="fas fa-times-circle mr-1"></i> Exceeded by Rs. ${Math.round(Math.abs(diff)).toLocaleString()}`);
    } else {
      badge.removeClass('badge-success badge-warning badge-danger').addClass('badge-secondary text-white')
           .html('<i class="fas fa-info-circle mr-1"></i> Enter valid amounts');
    }
  }
}

function saveSrqRemarks(input) {
  const id = $(input).data('id');
  const val = $(input).val();
  $.ajax({
    url: '{{ url("div/hr/salary/requisitions") }}/' + id + '/remarks2',
    type: 'PATCH',
    data: {
      _token: '{{ csrf_token() }}',
      remarks2: val
    },
    success: function() {
      $(input).addClass('is-valid border-success');
      setTimeout(() => $(input).removeClass('is-valid border-success'), 1500);
    }
  });
}
</script>
@endpush
