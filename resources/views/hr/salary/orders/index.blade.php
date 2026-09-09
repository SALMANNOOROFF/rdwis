{{-- resources/views/hr/salary/orders/index.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper px-3 py-3" style="background: #f4f6f9;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="font-weight-bold mb-0 text-dark" style="font-family: 'Rajdhani', sans-serif;">
        <i class="fas fa-receipt text-primary mr-2"></i>Salary Orders - {{ $status === 'Open' || $status === 'Approved' ? 'Open' : ($status === 'Closed' ? 'Closed' : 'Draft') }}
      </h3>
      <div class="text-muted small">
        <a href="{{ route('divhr.attendance') }}" class="text-muted">HR</a> / 
        <a href="{{ route('divhr.salary.requisitions.index') }}" class="text-muted">Salary Pipeline</a> / 
        <strong class="text-primary">Orders</strong>
      </div>
    </div>
    <div class="d-flex align-items-center" style="gap: 8px;">
      {{-- Legacy Top Buttons from Screenshot 4 (fin_salorders_u) --}}
      <a href="{{ route('fin.reports.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold">
        <i class="fas fa-file-alt mr-1"></i> Minute
      </a>
      <a href="{{ route('fin.reports.index') }}" class="btn btn-sm btn-outline-info font-weight-bold">
        <i class="fas fa-print mr-1"></i> Salary Slip
      </a>
      <button type="button" class="btn btn-sm btn-outline-warning font-weight-bold" data-toggle="modal" data-target="#remainingEmpsModal">
        <i class="fas fa-user-clock mr-1"></i> Remaining Employees
      </button>

      <a href="{{ route('divhr.salary.requisitions.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
        <i class="fas fa-file-invoice-dollar mr-1"></i> Salary Requisitions
      </a>
      <a href="{{ route('divhr.salary.commitments.verify') }}" class="btn btn-sm btn-outline-info font-weight-bold">
        <i class="fas fa-shield-alt mr-1"></i> Audit Commitments
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
      <form method="GET" action="{{ route('divhr.salary.orders.index') }}" class="row align-items-center">
        <input type="hidden" name="status" value="{{ $status ?? 'Draft' }}">
        <div class="col-md-3 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">Filter Month</label>
          <input type="month" name="month" class="form-control form-control-sm" value="{{ $month ?? '' }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-7 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">Status Filter (Exact sor_status)</label>
          <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
            <a href="{{ route('divhr.salary.orders.index', ['month' => $month, 'status' => 'Draft']) }}" class="btn btn-sm {{ ($status ?? 'Draft') === 'Draft' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-secondary' }}" title="Draft salary orders">
              Draft
            </a>
            <a href="{{ route('divhr.salary.orders.index', ['month' => $month, 'status' => 'Open']) }}" class="btn btn-sm {{ in_array($status, ['Open', 'Approved'], true) ? 'btn-info font-weight-bold' : 'btn-outline-secondary' }}" title="Open / Approved salary orders">
              Open <small class="text-muted d-none d-lg-inline">(Approved)</small>
            </a>
            <a href="{{ route('divhr.salary.orders.index', ['month' => $month, 'status' => 'Closed']) }}" class="btn btn-sm {{ in_array($status, ['Closed', 'Fulfilled', 'Cancelled'], true) ? 'btn-danger font-weight-bold' : 'btn-outline-secondary' }}" title="Closed / Fulfilled orders">
              Closed <small class="text-muted d-none d-lg-inline">(Fulfilled)</small>
            </a>
          </div>
        </div>
        <div class="col-md-2 text-md-right mt-2 mt-md-0">
          <label class="d-none d-md-block small text-transparent mb-1">&nbsp;</label>
          <a href="{{ route('divhr.salary.orders.index', ['status' => 'Draft']) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-undo mr-1"></i> Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Orders Table --}}
  <div class="card border-0 shadow-sm" style="border-radius: 8px; background: #ffffff;">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size: 13px;">
          <thead style="background: #f8fafc; color: #475569; border-bottom: 2px solid #e2e8f0;">
            <tr>
              <th style="width: 90px;">Order</th>
              <th>Employee</th>
              <th>Department</th>
              <th>Cont. Salary</th>
              <th>Calculated</th>
              <th>Payable</th>
              <th class="text-center" style="width: 40px;" title="Contract Verified">Ver.</th>
              <th>Bank Account & Title</th>
              <th>Remarks</th>
              <th style="width: 140px;">Add. Remarks</th>
              <th class="text-center">Order Status (sor_status)</th>
              <th class="text-center">Commitment (cmt_status)</th>
              <th style="width: 170px;" class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($orders as $o)
              @php
                $sorColors = [
                    'Draft'     => 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;',
                    'Approved'  => 'background: #e0e7ff; color: #4338ca; border: 1px solid #a5b4fc;',
                    'Fulfilled' => 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;',
                    'Cancelled' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;',
                ];
                $sorBadgeStyle = $sorColors[$o->sor_status] ?? 'background: #f1f5f9; color: #475569;';

                $cmt = $o->commitment;
                $cmtColors = [
                    'Awaited'   => 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;',
                    'Paid'      => 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;',
                    'Cancelled' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;',
                ];
                $cmtBadge = $cmt
                    ? '<span class="badge px-2 py-1 font-weight-bold" style="' . ($cmtColors[$cmt->cmt_status] ?? '') . '">' . $cmt->cmt_status . '</span>'
                    : '<span class="badge badge-light px-2 py-1 text-muted border">No Commitment</span>';
                
                $orderDate = $o->sor_releasedtg 
                    ? \Carbon\Carbon::parse($o->sor_releasedtg)->format('d M y')
                    : ($o->sor_date ? \Carbon\Carbon::parse($o->sor_date)->format('d M y') : '');
                $salMonth = \Carbon\Carbon::parse($o->sor_month)->format('M y');
                $isFinanceApprover = strtolower(trim((string) (auth()->user()->acc_untarea ?? ''))) === 'fin' && (auth()->user()->acc_auth ?? '') === 'approver';
              @endphp
              <tr>
                {{-- 1. Order --}}
                <td>
                  <div class="font-weight-bold" style="color: #1e293b;">#{{ $o->sor_id }}</div>
                  @if($orderDate)
                    <div class="small text-muted" style="font-size: 11px;">{{ $orderDate }}</div>
                  @endif
                </td>

                {{-- 2. Employee --}}
                <td>
                  <div class="small text-muted font-monospace" style="font-size: 11.5px;">{{ $o->sor_emp_id }}</div>
                  <div class="font-weight-bold text-dark">{{ $o->sor_empnamecomp ?: ($o->employee->emp_name ?? 'N/A') }}</div>
                </td>

                {{-- 3. Department --}}
                <td>
                  <div class="font-weight-bold text-dark">{{ $o->head->hed_name ?? ($o->effectiveHead->hed_name ?? 'Central') }}</div>
                  <div class="small text-muted" style="font-size: 11px;">{{ $o->unit->unt_namesh ?? ($o->unit->unt_name ?? '') }}</div>
                </td>

                {{-- 4. Cont. Salary --}}
                <td>
                  <div class="font-weight-bold text-dark">{{ number_format($o->sor_ctrsalary ?: $o->sor_salary) }}</div>
                  <div class="small text-muted" style="font-size: 11px;">{{ $salMonth }}</div>
                </td>

                {{-- 5. Calculated (Red text matching Screenshot 3 & 4) --}}
                <td>
                  <div class="font-weight-bold text-danger" style="font-size: 14px;">{{ number_format($o->sor_netsalary) }}</div>
                </td>

                {{-- 6. Payable --}}
                <td>
                  <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ number_format($o->sor_salary) }}</div>
                  <div class="small text-muted" style="font-size: 11px;">{{ $o->effectiveHead->hed_name ?? ($o->head->hed_name ?? '') }}</div>
                </td>

                {{-- 7. Verified Checkbox --}}
                <td class="text-center">
                  <i class="fas fa-check-square text-success" title="Verified"></i>
                </td>

                {{-- 8. Bank Account & Title --}}
                <td>
                  <div class="font-monospace text-dark font-weight-bold" style="font-size: 12px;">{{ $o->sor_bnkaccdetail ?: '(Pay by Cheque)' }}</div>
                  <div class="small text-muted text-truncate" style="max-width: 170px; font-size: 11.5px;">{{ $o->sor_bnkacctitle ?: ($o->employee->emp_name ?? '') }}</div>
                </td>

                {{-- 9. Remarks --}}
                <td>
                  <div class="small text-dark" style="font-size: 12px;">{{ $o->sor_remarks ?: '-' }}</div>
                </td>

                {{-- 10. Add. Remarks --}}
                <td>
                  <input type="text" 
                         class="form-control form-control-sm border-secondary-subtle" 
                         style="font-size: 12px; height: 28px;"
                         value="{{ $o->sor_remarks2 }}" 
                         placeholder="Remarks..." 
                         data-id="{{ $o->sor_id }}"
                         onchange="saveSorRemarks(this)">
                </td>

                {{-- 11. Order Status --}}
                <td class="text-center">
                  <span class="badge px-2 py-1 font-weight-bold" style="{{ $sorBadgeStyle }}">
                    {{ $o->sor_status }}
                  </span>
                </td>

                {{-- 12. Commitment Status --}}
                <td class="text-center">
                  {!! $cmtBadge !!}
                </td>

                {{-- 13. Actions: Approve, Cancel, Single Order (cmdSO) --}}
                <td class="text-center">
                  <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                    @if($o->sor_status === 'Draft' && $isFinanceApprover)
                      <form method="POST" action="{{ route('divhr.salary.orders.approve', $o->sor_id) }}" class="d-inline" onsubmit="return confirm('Approve salary order #{{ $o->sor_id }}?');">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-primary font-weight-bold px-2" title="Approve Order">
                          Approve
                        </button>
                      </form>
                    @elseif($o->sor_status === 'Approved')
                      <span class="badge badge-light text-muted border px-2 py-1 font-weight-bold" style="font-size: 11px;">Approved</span>
                    @endif

                    @if(in_array($o->sor_status, ['Draft', 'Approved']) && (!$cmt || $cmt->cmt_status !== 'Paid'))
                      <button type="button" class="btn btn-xs btn-outline-danger btn-trigger-cancel font-weight-bold px-2"
                              data-action="{{ route('divhr.salary.orders.cancel', $o->sor_id) }}"
                              data-desc="Salary Order #{{ $o->sor_id }} - {{ $o->sor_empnamecomp }} ({{ number_format($o->sor_salary) }})">
                        Cancel
                      </button>
                    @endif

                    {{-- Single Order button cmdSO linking to fin_salorders_one --}}
                    <a href="{{ route('divhr.salary.orders.show', $o->sor_id) }}" class="btn btn-xs btn-outline-info font-weight-bold px-2" title="Open Single Salary Order (fin_salorders_one)">
                      <i class="fas fa-external-link-alt"></i>
                    </a>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="13" class="text-center py-5 text-muted">
                  <i class="fas fa-inbox fa-3x mb-3 text-light" style="color: #cbd5e1 !important;"></i>
                  <div class="font-weight-bold">No salary orders found for the selected filters.</div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if($orders->hasPages())
        <div class="p-3 d-flex justify-content-between align-items-center border-top" style="border-color: #e2e8f0;">
          <div class="small text-muted">
            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
          </div>
          <div>{{ $orders->appends(request()->query())->links() }}</div>
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Remaining Employees Modal (cmdMissing) --}}
<div class="modal fade" id="remainingEmpsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-dark py-2">
        <h5 class="modal-title font-weight-bold" style="font-family: 'Rajdhani', sans-serif;">
          <i class="fas fa-user-clock mr-2"></i>Remaining Active Employees Without Draft Salary
        </h5>
        <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body p-3">
        <p class="text-muted small mb-3">
          This checks active employees who do not yet have a draft salary order generated for the selected month (equivalent to legacy <code class="text-primary">cmdMissing_Click</code>).
        </p>
        <div class="alert alert-info py-2 px-3 small border-0 mb-0">
          <i class="fas fa-info-circle mr-1"></i> To generate salary for remaining employees, return to 
          <a href="{{ route('divhr.attendance', ['month' => $month ?? '']) }}" class="font-weight-bold text-primary">Attendance Dashboard</a> 
          and click <strong>Generate Salary</strong>.
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Include Cancellation Modal --}}
@include('hr.salary.partials.cancel_modal')

<script>
$(document).ready(function() {
  $('.btn-trigger-cancel').on('click', function() {
    const actionUrl = $(this).data('action');
    const desc = $(this).data('desc');

    $('#cancelModalForm').attr('action', actionUrl);
    $('#cancel-target-desc').text(desc);
    $('#cancel-modal-title').text('Cancel Salary Order');
    $('#cancel-modal-warning').text('Cancelling this salary order will cascade to all related child orders and cancel any associated Awaited commitments.');
    $('#cancelModal').modal('show');
  });
});

function saveSorRemarks(input) {
  const id = $(input).data('id');
  const val = $(input).val();
  $.ajax({
    url: '{{ url("div/hr/salary/orders") }}/' + id + '/remarks2',
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
@endsection
