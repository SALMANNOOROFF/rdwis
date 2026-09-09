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
                  <div class="font-weight-bold text-dark">{{ $r->srq_empnamecomp ?: ($r->employee->emp_name ?? 'N/A') }}</div>
                </td>

                {{-- 3. Project --}}
                <td>
                  <div class="font-weight-bold text-dark">{{ $r->head->hed_name ?? ($r->effectiveHead->hed_name ?? 'Central') }}</div>
                  <div class="small text-muted" style="font-size: 11px;">{{ $r->unit->unt_namesh ?? ($r->unit->unt_name ?? '') }}</div>
                </td>

                {{-- 4. Contract Salary --}}
                <td>
                  <div class="font-weight-bold text-dark">{{ number_format($r->srq_ctrsalary ?: $r->srq_salary) }}</div>
                  <div class="small text-muted" style="font-size: 11px;">{{ $salMonth }}</div>
                </td>

                {{-- 5. Payable (Red bold font matching legacy Screenshot 2) --}}
                <td>
                  <div class="font-weight-bold text-danger" style="font-size: 14px;">{{ number_format($r->srq_salary) }}</div>
                  <div class="small text-muted" style="font-size: 11px;">{{ $r->effectiveHead->hed_name ?? ($r->head->hed_name ?? '') }}</div>
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

                {{-- 10. Actions (Draft: Release & Cancel; In Process: Cancel only) --}}
                <td class="text-center">
                  @if($r->srq_status === 'Draft')
                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                      <form method="POST" action="{{ route('divhr.salary.requisitions.release', $r->srq_id) }}" class="d-inline" onsubmit="return confirm('Release requisition #{{ $r->srq_id }} to In Process?');">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-primary font-weight-bold px-2" title="Release to In Process">
                          Release
                        </button>
                      </form>
                      <button type="button" class="btn btn-xs btn-outline-danger btn-trigger-cancel font-weight-bold px-2"
                              data-action="{{ route('divhr.salary.requisitions.cancel', $r->srq_id) }}"
                              data-desc="Salary Requisition #{{ $r->srq_id }} - {{ $r->srq_empnamecomp }} ({{ number_format($r->srq_salary) }})">
                        Cancel
                      </button>
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
                    <span class="text-success small font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Fulfilled</span>
                  @else
                    <span class="text-muted small">Closed</span>
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

<script>
$(document).ready(function() {
  $('.btn-trigger-cancel').on('click', function() {
    const actionUrl = $(this).data('action');
    const desc = $(this).data('desc');

    $('#cancelModalForm').attr('action', actionUrl);
    $('#cancel-target-desc').text(desc);
    $('#cancel-modal-title').text('Cancel Salary Requisition');
    $('#cancel-modal-warning').text('Cancelling this requisition will permanently set srq_status to Cancelled and free the employee for future requisitions.');
    $('#cancelModal').modal('show');
  });
});

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
@endsection
