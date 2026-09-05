{{-- resources/views/hr/salary/commitments/verify.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper px-3 py-3" style="background: #f4f6f9;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="font-weight-bold mb-0 text-dark" style="font-family: 'Rajdhani', sans-serif;">
        <i class="fas fa-shield-alt text-info mr-2"></i>Salary Commitment Verification Audit
      </h3>
      <div class="text-muted small">
        <a href="{{ route('divhr.attendance') }}" class="text-muted">HR</a> / 
        <a href="{{ route('divhr.salary.orders.index') }}" class="text-muted">Salary Orders</a> / 
        <strong class="text-info">Commitment Audit</strong>
      </div>
    </div>
    <div class="d-flex align-items-center" style="gap: 8px;">
      <a href="{{ route('divhr.salary.orders.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold">
        <i class="fas fa-arrow-left mr-1"></i> Back to Orders
      </a>
      <a href="{{ route('divhr.salary.requisitions.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
        <i class="fas fa-file-invoice-dollar mr-1"></i> Requisitions
      </a>
    </div>
  </div>

  {{-- Filter by Month --}}
  <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
    <div class="card-body p-3">
      <form method="GET" action="{{ route('divhr.salary.commitments.verify') }}" class="row align-items-center">
        <div class="col-md-3">
          <label class="small text-muted font-weight-bold mb-1">Audit Month</label>
          <input type="month" name="month" class="form-control form-control-sm" value="{{ $month ?? '' }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-7">
          <div class="small text-muted mt-md-4">
            <i class="fas fa-info-circle mr-1 text-primary"></i>
            Surfaces audit data from <code>VerifySalaryCommitmentsCommand</code> comparing Approved salary orders against <code>fin.commitments</code>.
          </div>
        </div>
        <div class="col-md-2 text-md-right mt-2 mt-md-0">
          <a href="{{ route('divhr.salary.commitments.verify') }}" class="btn btn-sm btn-outline-secondary mt-md-4">
            <i class="fas fa-undo mr-1"></i> Clear Filter
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- Metrics Cards --}}
  <div class="row mb-3">
    <div class="col-md-4 mb-2">
      <div class="card border-0 shadow-sm" style="background: #f8fafc; border-left: 4px solid #64748b !important; border-radius: 8px;">
        <div class="card-body p-3">
          <div class="text-xs text-uppercase font-weight-bold text-muted">Approved Salary Orders</div>
          <div class="h3 font-weight-bold mb-0 text-dark">{{ $audit['total_approved'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-2">
      <div class="card border-0 shadow-sm" style="background: #f0fdf4; border-left: 4px solid #16a34a !important; border-radius: 8px;">
        <div class="card-body p-3">
          <div class="text-xs text-uppercase font-weight-bold" style="color: #15803d;">Verified Commitments Matched</div>
          <div class="h3 font-weight-bold mb-0 text-success">{{ $audit['verified_count'] }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-2">
      <div class="card border-0 shadow-sm" style="background: #fef2f2; border-left: 4px solid #dc2626 !important; border-radius: 8px;">
        <div class="card-body p-3">
          <div class="text-xs text-uppercase font-weight-bold" style="color: #b91c1c;">Missing / Orphaned Commitments</div>
          <div class="h3 font-weight-bold mb-0 text-danger">{{ $audit['missing_count'] }}</div>
        </div>
      </div>
    </div>
  </div>

  @if($audit['missing_count'] > 0)
    <div class="alert alert-danger shadow-sm py-2 px-3 small border-0 mb-3" style="background: #fef2f2; color: #b91c1c; border-left: 4px solid #dc2626 !important;">
      <i class="fas fa-exclamation-triangle mr-1"></i>
      <strong>Missing Commitments Detected:</strong> {{ $audit['missing_count'] }} Approved salary order(s) do not have corresponding commitment records in <code>fin.commitments</code>.
    </div>
  @endif

  {{-- Audit Table --}}
  <div class="card border-0 shadow-sm" style="border-radius: 8px; background: #ffffff;">
    <div class="card-header py-3 px-4 bg-white border-bottom">
      <h5 class="card-title font-weight-bold mb-0 text-dark">
        <i class="fas fa-list-check text-primary mr-2"></i>Approved Orders & Commitment Link Status
      </h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover table-striped mb-0 align-middle">
          <thead style="background: #f8fafc; color: #1e293b; border-bottom: 2px solid #cbd5e1;">
            <tr>
              <th style="width: 80px;" class="text-center font-weight-bold">SOR ID</th>
              <th class="font-weight-bold">Employee</th>
              <th class="font-weight-bold">Salary Period</th>
              <th class="text-right font-weight-bold">Order Salary</th>
              <th class="text-center font-weight-bold">Order Status</th>
              <th class="text-center font-weight-bold">Commitment ID</th>
              <th class="text-right font-weight-bold">Commitment Amount</th>
              <th class="text-center font-weight-bold">Commitment Status</th>
              <th class="text-center font-weight-bold">Audit Status</th>
            </tr>
          </thead>
          <tbody>
            @php
              $allRows = array_merge($audit['missing'], $audit['verified']);
            @endphp
            @forelse($allRows as $item)
              @php
                $isMatched = !empty($item->cmt_id);
              @endphp
              <tr>
                <td class="text-center font-monospace font-weight-bold" style="color: #0369a1;">
                  <a href="{{ route('divhr.salary.orders.show', $item->sor_id) }}">#{{ $item->sor_id }}</a>
                </td>
                <td>
                  <div class="font-weight-bold text-dark">{{ $item->sor_empnamecomp }}</div>
                  <div class="small text-muted font-monospace">{{ $item->sor_emp_id }}</div>
                </td>
                <td>
                  <span class="badge px-2 py-1 font-weight-bold" style="background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;">
                    {{ \Carbon\Carbon::parse($item->sor_month)->format('M Y') }}
                  </span>
                </td>
                <td class="text-right font-monospace font-weight-bold text-dark">
                  {{ number_format($item->sor_salary) }}
                </td>
                <td class="text-center">
                  <span class="badge px-2 py-1 font-weight-bold" style="background: #e0e7ff; color: #4338ca; border: 1px solid #a5b4fc;">
                    {{ $item->sor_status }}
                  </span>
                </td>
                <td class="text-center font-monospace">
                  @if($item->cmt_id)
                    <span class="font-weight-bold text-primary">#{{ $item->cmt_id }}</span>
                  @else
                    <span class="badge badge-danger">NONE</span>
                  @endif
                </td>
                <td class="text-right font-monospace">
                  @if($item->cmt_amount !== null)
                    <span class="font-weight-bold text-danger">{{ number_format($item->cmt_amount) }}</span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td class="text-center">
                  @if($item->cmt_status)
                    @php
                      $cmtColors = [
                          'Awaited'   => 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;',
                          'Paid'      => 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;',
                          'Cancelled' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;',
                      ];
                    @endphp
                    <span class="badge px-2 py-1 font-weight-bold" style="{{ $cmtColors[$item->cmt_status] ?? '' }}">
                      {{ $item->cmt_status }}
                    </span>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td class="text-center">
                  @if($isMatched)
                    <span class="badge badge-success px-2 py-1">
                      <i class="fas fa-check-circle mr-1"></i> VERIFIED
                    </span>
                  @else
                    <span class="badge badge-danger px-2 py-1">
                      <i class="fas fa-exclamation-triangle mr-1"></i> ORPHANED
                    </span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center py-5 text-muted">
                  <i class="fas fa-shield-alt fa-3x mb-3 text-light" style="color: #cbd5e1 !important;"></i>
                  <div class="font-weight-bold">No Approved salary orders found for this audit filter.</div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
