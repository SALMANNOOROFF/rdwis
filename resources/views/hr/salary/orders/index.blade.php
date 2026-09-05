{{-- resources/views/hr/salary/orders/index.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper px-3 py-3" style="background: #f4f6f9;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="font-weight-bold mb-0 text-dark" style="font-family: 'Rajdhani', sans-serif;">
        <i class="fas fa-receipt text-primary mr-2"></i>Salary Orders Dashboard
      </h3>
      <div class="text-muted small">
        <a href="{{ route('divhr.attendance') }}" class="text-muted">HR</a> / 
        <a href="{{ route('divhr.salary.requisitions.index') }}" class="text-muted">Salary Pipeline</a> / 
        <strong class="text-primary">Orders</strong>
      </div>
    </div>
    <div class="d-flex align-items-center" style="gap: 8px;">
      <a href="{{ route('divhr.salary.requisitions.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
        <i class="fas fa-file-invoice-dollar mr-1"></i> Requisitions
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
        <div class="col-md-3 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">Filter Month</label>
          <input type="month" name="month" class="form-control form-control-sm" value="{{ $month ?? '' }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-7 mb-2 mb-md-0">
          <label class="small text-muted font-weight-bold mb-1">Status Filter (Exact sor_status)</label>
          <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
            <a href="{{ route('divhr.salary.orders.index', ['month' => $month]) }}" class="btn btn-sm {{ empty($status) ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}">
              All
            </a>
            <a href="{{ route('divhr.salary.orders.index', ['month' => $month, 'status' => 'Draft']) }}" class="btn btn-sm {{ $status === 'Draft' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-secondary' }}">
              Draft
            </a>
            <a href="{{ route('divhr.salary.orders.index', ['month' => $month, 'status' => 'Approved']) }}" class="btn btn-sm {{ $status === 'Approved' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}">
              Approved
            </a>
            <a href="{{ route('divhr.salary.orders.index', ['month' => $month, 'status' => 'Cancelled']) }}" class="btn btn-sm {{ $status === 'Cancelled' ? 'btn-danger font-weight-bold' : 'btn-outline-secondary' }}">
              Cancelled
            </a>
          </div>
        </div>
        <div class="col-md-2 text-md-right mt-2 mt-md-0">
          <label class="d-none d-md-block small text-transparent mb-1">&nbsp;</label>
          <a href="{{ route('divhr.salary.orders.index') }}" class="btn btn-sm btn-outline-secondary">
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
        <table class="table table-hover table-striped mb-0 align-middle">
          <thead style="background: #f8fafc; color: #1e293b; border-bottom: 2px solid #cbd5e1;">
            <tr>
              <th style="width: 80px;" class="text-center font-weight-bold">SOR ID</th>
              <th class="font-weight-bold">SRQ Ref</th>
              <th class="font-weight-bold">Employee</th>
              <th class="font-weight-bold">Unit</th>
              <th class="font-weight-bold">Salary Period</th>
              <th class="text-right font-weight-bold">Net Salary</th>
              <th class="text-center font-weight-bold">Order Status (sor_status)</th>
              <th class="text-center font-weight-bold">Commitment (cmt_status)</th>
              <th style="width: 130px;" class="text-center font-weight-bold">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($orders as $o)
              @php
                $sorColors = [
                    'Draft'     => 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;',
                    'Approved'  => 'background: #e0e7ff; color: #4338ca; border: 1px solid #a5b4fc;',
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
              @endphp
              <tr>
                <td class="text-center font-monospace font-weight-bold" style="color: #0369a1;">#{{ $o->sor_id }}</td>
                <td class="font-monospace small">
                  @if($o->sor_srq_id)
                    <span class="text-muted">SRQ #{{ $o->sor_srq_id }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  <div class="font-weight-bold text-dark">{{ $o->sor_empnamecomp ?: ($o->employee->emp_name ?? 'N/A') }}</div>
                  <div class="small text-muted font-monospace">{{ $o->sor_emp_id }}</div>
                </td>
                <td>
                  <span class="small font-weight-bold text-secondary">{{ $o->unit->unt_name ?? "Unit {$o->sor_unt_id}" }}</span>
                </td>
                <td>
                  <span class="badge px-2 py-1 font-weight-bold" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">
                    {{ \Carbon\Carbon::parse($o->sor_month)->format('M Y') }}
                  </span>
                </td>
                <td class="text-right font-weight-bold" style="color: #0f172a; font-family: monospace; font-size: 1rem;">
                  {{ number_format($o->sor_salary) }}
                </td>
                <td class="text-center">
                  <span class="badge px-2 py-1 font-weight-bold" style="{{ $sorBadgeStyle }}">
                    {{ $o->sor_status }}
                  </span>
                </td>
                <td class="text-center">{!! $cmtBadge !!}</td>
                <td class="text-center">
                  <a href="{{ route('divhr.salary.orders.show', $o->sor_id) }}" class="btn btn-xs btn-outline-primary font-weight-bold px-2">
                    <i class="fas fa-eye mr-1"></i> View Order
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center py-5 text-muted">
                  <i class="fas fa-receipt fa-3x mb-3 text-light" style="color: #cbd5e1 !important;"></i>
                  <div class="font-weight-bold">No salary orders found for the selected filters.</div>
                  <div class="small">Salary orders are created from released "In Process" requisitions.</div>
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
            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} salary orders
          </div>
          <div>{{ $orders->appends(request()->query())->links() }}</div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
