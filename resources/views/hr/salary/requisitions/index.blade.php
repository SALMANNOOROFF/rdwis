{{-- resources/views/hr/salary/requisitions/index.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper px-3 py-3" style="background: #f4f6f9;">
  {{-- Header & Breadcrumbs --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="font-weight-bold mb-0 text-dark" style="font-family: 'Rajdhani', sans-serif;">
        <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>Salary Requisitions Dashboard
      </h3>
      <div class="text-muted small">
        <a href="{{ route('divhr.attendance') }}" class="text-muted">HR</a> / 
        <span>Salary Pipeline</span> / 
        <strong class="text-primary">Requisitions</strong>
      </div>
    </div>
    <div class="d-flex align-items-center" style="gap: 8px;">
      <a href="{{ route('divhr.salary.orders.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
        <i class="fas fa-receipt mr-1"></i> Salary Orders
      </a>
      <a href="{{ route('divhr.salary.commitments.verify') }}" class="btn btn-sm btn-outline-info font-weight-bold">
        <i class="fas fa-shield-alt mr-1"></i> Audit Commitments
      </a>
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
        <div class="col-md-3 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">Filter Month</label>
          <input type="month" name="month" class="form-control form-control-sm" value="{{ $month ?? '' }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-7 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">Status Filter (Exact srq_status)</label>
          <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
            <a href="{{ route('divhr.salary.requisitions.index', ['month' => $month]) }}" class="btn btn-sm {{ empty($status) ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}">
              All
            </a>
            <a href="{{ route('divhr.salary.requisitions.index', ['month' => $month, 'status' => 'Draft']) }}" class="btn btn-sm {{ $status === 'Draft' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-secondary' }}">
              Draft
            </a>
            <a href="{{ route('divhr.salary.requisitions.index', ['month' => $month, 'status' => 'In Process']) }}" class="btn btn-sm {{ $status === 'In Process' ? 'btn-info font-weight-bold' : 'btn-outline-secondary' }}">
              In Process
            </a>
            <a href="{{ route('divhr.salary.requisitions.index', ['month' => $month, 'status' => 'Fulfilled']) }}" class="btn btn-sm {{ $status === 'Fulfilled' ? 'btn-success font-weight-bold' : 'btn-outline-secondary' }}">
              Fulfilled
            </a>
            <a href="{{ route('divhr.salary.requisitions.index', ['month' => $month, 'status' => 'Cancelled']) }}" class="btn btn-sm {{ $status === 'Cancelled' ? 'btn-danger font-weight-bold' : 'btn-outline-secondary' }}">
              Cancelled
            </a>
          </div>
        </div>
        <div class="col-md-2 text-md-right mt-2 mt-md-0">
          <label class="d-none d-md-block small text-transparent mb-1">&nbsp;</label>
          <a href="{{ route('divhr.salary.requisitions.index') }}" class="btn btn-sm btn-outline-secondary">
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
        <table class="table table-hover table-striped mb-0 align-middle" style="border-collapse: separate;">
          <thead style="background: #f8fafc; color: #1e293b; border-bottom: 2px solid #cbd5e1;">
            <tr>
              <th style="width: 80px;" class="text-center font-weight-bold">SRQ ID</th>
              <th class="font-weight-bold">Employee</th>
              <th class="font-weight-bold">Unit</th>
              <th class="font-weight-bold">Salary Period</th>
              <th class="text-right font-weight-bold">Net Salary</th>
              <th class="text-center font-weight-bold">Status (srq_status)</th>
              <th class="font-weight-bold">Released Date / Age</th>
              <th style="width: 220px;" class="text-center font-weight-bold">Actions</th>
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
                $age = $r->srq_releasedtg ? \Carbon\Carbon::parse($r->srq_releasedtg)->diffForHumans() : ($r->srq_closedtg ? 'Closed ' . \Carbon\Carbon::parse($r->srq_closedtg)->diffForHumans() : 'Created Draft');
              @endphp
              <tr>
                <td class="text-center font-monospace font-weight-bold" style="color: #0369a1;">#{{ $r->srq_id }}</td>
                <td>
                  <div class="font-weight-bold text-dark">{{ $r->srq_empnamecomp ?: ($r->employee->emp_name ?? 'N/A') }}</div>
                  <div class="small text-muted font-monospace">{{ $r->srq_emp_id }}</div>
                </td>
                <td>
                  <span class="small font-weight-bold text-secondary">{{ $r->unit->unt_name ?? "Unit {$r->srq_unt_id}" }}</span>
                </td>
                <td>
                  <span class="badge px-2 py-1 font-weight-bold" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">
                    {{ \Carbon\Carbon::parse($r->srq_month)->format('M Y') }}
                  </span>
                </td>
                <td class="text-right font-weight-bold" style="color: #0f172a; font-family: monospace; font-size: 1rem;">
                  {{ number_format($r->srq_salary) }}
                </td>
                <td class="text-center">
                  <span class="badge px-2 py-1 font-weight-bold" style="{{ $badgeStyle }}">
                    {{ $r->srq_status }}
                  </span>
                </td>
                <td class="small text-muted">
                  <div>{{ $r->srq_releasedtg ? \Carbon\Carbon::parse($r->srq_releasedtg)->format('Y-m-d H:i') : '-' }}</div>
                  <div class="text-xs">{{ $age }}</div>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    {{-- Draft Action: Release --}}
                    @if($r->srq_status === 'Draft')
                      <form method="POST" action="{{ route('divhr.salary.requisitions.release', $r->srq_id) }}" class="d-inline" onsubmit="return confirm('Release requisition #{{ $r->srq_id }} to In Process?');">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-primary font-weight-bold px-2 mr-1" title="Release to In Process">
                          <i class="fas fa-paper-plane mr-1"></i> Release
                        </button>
                      </form>
                      <button type="button" class="btn btn-xs btn-outline-danger btn-trigger-cancel font-weight-bold px-2"
                              data-action="{{ route('divhr.salary.requisitions.cancel', $r->srq_id) }}"
                              data-desc="Salary Requisition #{{ $r->srq_id }} - {{ $r->srq_empnamecomp }} ({{ number_format($r->srq_salary) }})">
                        <i class="fas fa-times"></i> Cancel
                      </button>
                    @elseif($r->srq_status === 'In Process')
                      <form method="POST" action="{{ route('divhr.salary.requisitions.create_orders', $r->srq_id) }}" class="d-inline" onsubmit="return confirm('Generate salary orders for requisition #{{ $r->srq_id }}?');">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-success font-weight-bold px-2 mr-1" title="Create Salary Orders">
                          <i class="fas fa-receipt mr-1"></i> Create Order
                        </button>
                      </form>
                      <button type="button" class="btn btn-xs btn-outline-danger btn-trigger-cancel font-weight-bold px-2"
                              data-action="{{ route('divhr.salary.requisitions.cancel', $r->srq_id) }}"
                              data-desc="Salary Requisition #{{ $r->srq_id }} - {{ $r->srq_empnamecomp }} ({{ number_format($r->srq_salary) }})">
                        <i class="fas fa-times"></i> Cancel
                      </button>
                    @elseif($r->srq_status === 'Fulfilled')
                      @if($r->order)
                        <a href="{{ route('divhr.salary.orders.show', $r->order->sor_id) }}" class="btn btn-xs btn-outline-info font-weight-bold px-2">
                          <i class="fas fa-external-link-alt mr-1"></i> View Order #{{ $r->order->sor_id }}
                        </a>
                      @else
                        <span class="text-muted small">Fulfilled</span>
                      @endif
                    @else
                      <span class="text-muted small">Closed</span>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center py-5 text-muted">
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
</script>
@endsection
