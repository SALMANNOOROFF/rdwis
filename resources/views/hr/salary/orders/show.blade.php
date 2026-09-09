{{-- resources/views/hr/salary/orders/show.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper px-3 py-3" style="background: #f4f6f9;">
  {{-- Header & Breadcrumbs --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="font-weight-bold mb-0 text-dark" style="font-family: 'Rajdhani', sans-serif;">
        <i class="fas fa-file-invoice text-primary mr-2"></i>Salary Order #{{ $order->sor_id }}
      </h3>
      <div class="text-muted small">
        <a href="{{ route('divhr.attendance') }}" class="text-muted">HR</a> / 
        <a href="{{ route('divhr.salary.orders.index') }}" class="text-muted">Orders</a> / 
        <strong class="text-primary">Order #{{ $order->sor_id }}</strong>
      </div>
    </div>
    <div class="d-flex align-items-center" style="gap: 8px;">
      <a href="{{ route('divhr.salary.requisitions.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold">
        <i class="fas fa-file-invoice-dollar mr-1"></i> Salary Requisitions
      </a>
      <a href="{{ route('divhr.salary.orders.index') }}" class="btn btn-sm btn-outline-secondary font-weight-bold">
        <i class="fas fa-arrow-left mr-1"></i> Back to Orders
      </a>
      @php
          $isPaid = $order->commitment && $order->commitment->cmt_status === 'Paid';
          $isFulfilled = $order->sor_status === 'Fulfilled';
          $canCancel = !$isPaid && !$isFulfilled && $order->sor_status !== 'Cancelled';
          $isFinanceApprover = strtolower(trim((string) (auth()->user()->acc_untarea ?? ''))) === 'fin' && (auth()->user()->acc_auth ?? '') === 'approver';
      @endphp

      @if($order->sor_status === 'Draft' && $isFinanceApprover)
        <form method="POST" action="{{ route('divhr.salary.orders.approve', $order->sor_id) }}" class="d-inline" onsubmit="return confirm('Approve salary order #{{ $order->sor_id }}? Approving will create a financial commitment of Rs. {{ number_format($order->sor_salary) }} in fin.commitments.');">
          @csrf
          <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3 shadow-sm">
            <i class="fas fa-check-double mr-1"></i> Approve Order
          </button>
        </form>
      @endif

      @if(in_array($order->sor_status, ['Approved', 'Fulfilled']))
        <a href="{{ route('divhr.salary.orders.slip', $order->sor_id) }}" target="_blank" class="btn btn-sm btn-outline-info font-weight-bold px-3 shadow-sm">
          <i class="fas fa-print mr-1"></i> Print Pay Slip
        </a>
      @endif

      @if($canCancel)
        <button type="button" class="btn btn-sm btn-outline-danger btn-trigger-cancel font-weight-bold px-3"
                data-action="{{ route('divhr.salary.orders.cancel', $order->sor_id) }}"
                data-desc="Salary Order #{{ $order->sor_id }} - {{ $order->sor_empnamecomp }} (Rs. {{ number_format($order->sor_salary) }})">
          <i class="fas fa-times mr-1"></i> Cancel Order
        </button>
      @endif
    </div>
  </div>

  {{-- Alerts --}}
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

  @if($order->sor_parent > 0)
    <div class="alert alert-info py-2 px-3 small border-0 mb-3 shadow-sm" style="background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6 !important;">
      <i class="fas fa-link mr-1"></i> <strong>Linked Order Group:</strong> This is a child order linked to Parent Order <strong>#{{ $order->sor_parent }}</strong>. Approving or cancelling here will update the parent order and all linked child orders together.
    </div>
  @endif

  <div class="row">
    {{-- Left Column: Order Header & Financial Summary --}}
    <div class="col-lg-8 mb-3">
      <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
        <div class="card-header py-3 px-4 bg-white border-bottom d-flex justify-content-between align-items-center">
          <h5 class="card-title font-weight-bold mb-0 text-dark">
            <i class="fas fa-info-circle text-primary mr-2"></i>Order Summary
          </h5>
          <div>
            @php
              $sorColors = [
                  'Draft'     => 'background: #fef3c7; color: #b45309; border: 1px solid #fde68a;',
                  'Approved'  => 'background: #e0e7ff; color: #4338ca; border: 1px solid #a5b4fc;',
                  'Cancelled' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;',
              ];
            @endphp
            <span class="badge px-3 py-1 font-weight-bold" style="{{ $sorColors[$order->sor_status] ?? '' }}; font-size: 0.9rem;">
              sor_status: {{ $order->sor_status }}
            </span>
          </div>
        </div>
        <div class="card-body p-4">
          <div class="row mb-3">
            <div class="col-md-6 mb-2">
              <div class="text-xs text-uppercase font-weight-bold text-muted">Employee</div>
              <div class="h5 font-weight-bold text-dark mb-0">{{ $order->sor_empnamecomp }}</div>
              <div class="small font-monospace text-primary">{{ $order->sor_emp_id }}</div>
            </div>
            <div class="col-md-3 col-6 mb-2">
              <div class="text-xs text-uppercase font-weight-bold text-muted">Salary Period</div>
              <div class="h6 font-weight-bold text-dark mb-0">{{ \Carbon\Carbon::parse($order->sor_month)->format('F Y') }}</div>
            </div>
            <div class="col-md-3 col-6 mb-2">
              <div class="text-xs text-uppercase font-weight-bold text-muted">Parent Unit</div>
              <div class="h6 font-weight-bold text-secondary mb-0">Unit {{ $order->sor_unt_id }}</div>
            </div>
          </div>

          <hr style="border-color: #f1f5f9;">

          {{-- Financial Summary Breakdown --}}
          <h6 class="font-weight-bold text-dark mb-3">
            <i class="fas fa-calculator text-primary mr-1"></i> Salary Matrix Breakdown
          </h6>
          <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
              <thead style="background: #f8fafc;">
                <tr>
                  <th>Component</th>
                  <th class="text-right">Amount (PKR)</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Contract Salary</td>
                  <td class="text-right font-monospace">{{ number_format($order->sor_ctrsalary) }}</td>
                  <td class="text-muted small">Agreed contract rate</td>
                </tr>
                <tr>
                  <td>Gross Salary</td>
                  <td class="text-right font-monospace">{{ number_format($order->sor_grosalary) }}</td>
                  <td class="text-muted small">Prorated base gross</td>
                </tr>
                @if($order->sor_underwork > 0)
                  <tr class="text-danger">
                    <td>Deductions (Underwork / Absents)</td>
                    <td class="text-right font-monospace">-{{ number_format($order->sor_underwork) }}</td>
                    <td class="small">{{ $order->sor_remarks ?: 'Absent / unpaid leaves' }}</td>
                  </tr>
                @endif
                @if($order->sor_overwork > 0)
                  <tr class="text-success">
                    <td>Overtime / Extra Duty</td>
                    <td class="text-right font-monospace">+{{ number_format($order->sor_overwork) }}</td>
                    <td class="small">Approved overtime</td>
                  </tr>
                @endif
                @if($order->sor_arrears > 0)
                  <tr class="text-success">
                    <td>Arrears Due</td>
                    <td class="text-right font-monospace">+{{ number_format($order->sor_arrears) }}</td>
                    <td class="small">FIS verified prior dues</td>
                  </tr>
                @endif
                @if($order->sor_dues > 0)
                  <tr class="text-danger">
                    <td>Dues Deduction</td>
                    <td class="text-right font-monospace">-{{ number_format($order->sor_dues) }}</td>
                    <td class="small">Negative dues recovery</td>
                  </tr>
                @endif
                <tr style="background: #f0fdf4; border-top: 2px solid #86efac;">
                  <td class="font-weight-bold text-success" style="font-size: 1.05rem;">
                    Net Payable Salary (sor_salary)
                    @if($order->sor_status === 'Draft' && $isFinanceApprover)
                      <button type="button" class="btn btn-xs btn-outline-success ml-2 px-2 font-weight-bold" data-toggle="modal" data-target="#adjustSalaryModal">
                        <i class="fas fa-edit mr-1"></i> Adjust Salary
                      </button>
                    @endif
                  </td>
                  <td class="text-right font-monospace font-weight-bold text-success" style="font-size: 1.2rem;">
                    {{ number_format($order->sor_salary) }}
                  </td>
                  <td class="text-success small font-weight-bold">
                    Final Order Amount
                    @if($order->sor_salary < $order->sor_netsalary)
                      <div class="text-xs text-muted font-weight-normal">(Calculated Net: {{ number_format($order->sor_netsalary) }})</div>
                    @endif
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          {{-- Remarks --}}
          @if($order->sor_remarks)
            <div class="mt-3 p-2 rounded small" style="background: #f8fafc; border: 1px solid #e2e8f0;">
              <strong>Remarks:</strong> {{ $order->sor_remarks }}
              @if(str_contains($order->sor_remarks, 'Pending -'))
                <span class="badge badge-warning text-dark ml-1"><i class="fas fa-clock mr-1"></i> Partial Salary Retained</span>
              @endif
            </div>
          @endif
        </div>
      </div>

      {{-- Subhead Breakdown (fin_salorders_shd) --}}
      <div class="card border-0 shadow-sm" style="border-radius: 8px; background: #ffffff;">
        <div class="card-header py-3 px-4 bg-white border-bottom">
          <h5 class="card-title font-weight-bold mb-0 text-dark">
            <i class="fas fa-sitemap text-primary mr-2"></i>Subhead Allocation (fin.salorders_shd)
          </h5>
        </div>
        <div class="card-body p-4">
          @if($order->subheads && $order->subheads->isNotEmpty())
            <div class="alert alert-info py-2 px-3 small border-0 mb-3" style="background: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6 !important;">
              <i class="fas fa-info-circle mr-1"></i> Project Unit Employee: Allocated to HR subhead with ratio 1.0 (100%).
            </div>
            <div class="table-responsive">
              <table class="table table-sm table-bordered mb-0">
                <thead style="background: #f8fafc;">
                  <tr>
                    <th>Subhead Code</th>
                    <th class="text-center">Allocation Ratio</th>
                    <th class="text-right">Share Amount</th>
                    <th>Type</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($order->subheads as $shd)
                    <tr>
                      <td class="font-weight-bold text-primary font-monospace">{{ $shd->sod_subhead }}</td>
                      <td class="text-center font-monospace">{{ number_format($shd->sod_ratio, 2) }} (100%)</td>
                      <td class="text-right font-monospace font-weight-bold">{{ number_format($order->sor_salary * $shd->sod_ratio) }}</td>
                      <td class="text-muted small">{{ $shd->sod_type }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-secondary py-3 px-3 small border-0 mb-0" style="background: #f8fafc; color: #475569; border-left: 4px solid #94a3b8 !important;">
              <i class="fas fa-university mr-1"></i> <strong>Central HQ Employee:</strong> Direct debit against head {{ $order->sor_effhed_id }} without subhead split (zero fin.salorders_shd rows created).
            </div>
          @endif
        </div>
      </div>
    </div>

    {{-- Right Column: Bank & Commitment / Payment Status --}}
    <div class="col-lg-4 mb-3">
      {{-- Banking Info --}}
      <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
        <div class="card-header py-3 px-4 bg-white border-bottom">
          <h6 class="card-title font-weight-bold mb-0 text-dark">
            <i class="fas fa-university text-primary mr-2"></i>Banking Details
          </h6>
        </div>
        <div class="card-body p-3">
          <div class="mb-2">
            <div class="text-xs text-uppercase font-weight-bold text-muted">Account Title</div>
            <div class="font-weight-bold text-dark">{{ $order->sor_bnkacctitle ?: $order->sor_empnamecomp }}</div>
          </div>
          <div>
            <div class="text-xs text-uppercase font-weight-bold text-muted">Payment Destination</div>
            @if(str_contains($order->sor_bnkaccdetail, '(Pay by Cheque)'))
              <span class="badge badge-secondary px-2 py-1"><i class="fas fa-money-check mr-1"></i> Pay by Cheque</span>
            @else
              <span class="badge badge-success px-2 py-1"><i class="fas fa-university mr-1"></i> {{ $order->sor_bnkaccdetail }}</span>
            @endif
          </div>
        </div>
      </div>

      {{-- Commitment & Payment Status --}}
      <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
        <div class="card-header py-3 px-4 bg-white border-bottom">
          <h6 class="card-title font-weight-bold mb-0 text-dark">
            <i class="fas fa-shield-alt text-primary mr-2"></i>Commitment & Settlement
          </h6>
        </div>
        <div class="card-body p-3">
          @if($order->commitment)
            @php
              $cmt = $order->commitment;
              $cmtColors = [
                  'Awaited'   => 'background: #fffbeb; color: #d97706; border: 1px solid #fde68a;',
                  'Paid'      => 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;',
                  'Cancelled' => 'background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;',
              ];
            @endphp
            <div class="mb-2">
              <div class="text-xs text-uppercase font-weight-bold text-muted">Commitment ID</div>
              <div class="font-monospace font-weight-bold text-primary">#{{ $cmt->cmt_id }} (Doc #{{ $cmt->cmt_docid }})</div>
            </div>
            <div class="mb-2">
              <div class="text-xs text-uppercase font-weight-bold text-muted">Commitment Amount</div>
              <div class="font-monospace font-weight-bold text-danger">{{ number_format($cmt->cmt_amount) }} PKR</div>
              <div class="text-xs text-muted">Recorded as negative liability encumbrance</div>
            </div>
            <div class="mb-2">
              <div class="text-xs text-uppercase font-weight-bold text-muted">Commitment Status (cmt_status)</div>
              <span class="badge px-2 py-1 font-weight-bold" style="{{ $cmtColors[$cmt->cmt_status] ?? '' }}">
                {{ $cmt->cmt_status }}
              </span>
            </div>
            <div>
              <div class="text-xs text-uppercase font-weight-bold text-muted">Booking Date</div>
              <div class="small text-dark">{{ $cmt->cmt_date ? \Carbon\Carbon::parse($cmt->cmt_date)->format('Y-m-d') : '-' }}</div>
            </div>
          @else
            <div class="alert alert-light py-2 px-3 small border mb-0 text-muted">
              <i class="fas fa-hourglass-start mr-1"></i> No commitment recorded. Approving this draft order will create a negative liability commitment in fin.commitments.
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Cancellation Modal --}}
@include('hr.salary.partials.cancel_modal')

@if($order->sor_status === 'Draft' && $isFinanceApprover)
{{-- Adjust Salary Modal --}}
<div class="modal fade" id="adjustSalaryModal" tabindex="-1" role="dialog" aria-labelledby="adjustSalaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <form method="POST" action="{{ route('divhr.salary.orders.update_salary', $order->sor_id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-header bg-white border-bottom py-3">
          <h5 class="modal-title font-weight-bold text-dark" id="adjustSalaryModalLabel">
            <i class="fas fa-sliders-h text-primary mr-2"></i>Adjust Salary (Draft Order #{{ $order->sor_id }})
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <div class="alert alert-info py-2 px-3 small border-0 mb-3" style="background: #eff6ff; color: #1e40af;">
            <i class="fas fa-info-circle mr-1"></i> <strong>Rule:</strong> Salary can only be decreased below calculated net (PKR {{ number_format($order->sor_netsalary) }}). Any reduction will automatically append <code>Pending - {diff}.</code> to remarks.
          </div>

          <div class="form-group mb-3">
            <label class="font-weight-bold small text-muted text-uppercase mb-1">Calculated Net Ceiling (sor_netsalary)</label>
            <div class="h5 font-monospace font-weight-bold text-secondary mb-0">
              PKR {{ number_format($order->sor_netsalary) }}
            </div>
          </div>

          <div class="form-group mb-3">
            <label for="override_sor_salary" class="font-weight-bold small text-dark mb-1">
              New Payable Salary (sor_salary) <span class="text-danger">*</span>
            </label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text font-weight-bold">PKR</span>
              </div>
              <input type="number" step="1" min="0" max="{{ $order->sor_netsalary }}" 
                     name="sor_salary" id="override_sor_salary" 
                     class="form-control font-monospace font-weight-bold" 
                     value="{{ $order->sor_salary }}" required>
            </div>
            <small class="form-text text-muted">Must not exceed calculated net PKR {{ number_format($order->sor_netsalary) }}.</small>
          </div>

          <div id="pendingDiffPreview" class="p-2 rounded small border text-muted" style="background: #f8fafc; display: none;">
            <i class="fas fa-comment-dots text-primary mr-1"></i> Auto-remark will become: <strong id="pendingDiffText" class="text-dark"></strong>
          </div>
        </div>
        <div class="modal-footer bg-light py-2 px-4">
          <button type="button" class="btn btn-sm btn-outline-secondary font-weight-bold" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-success font-weight-bold px-3">
            <i class="fas fa-save mr-1"></i> Save Adjustment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  const ceiling = {{ (float)$order->sor_netsalary }};
  $('#override_sor_salary').on('input', function() {
    const val = parseFloat($(this).val());
    if (!isNaN(val) && val < ceiling) {
      const diff = Math.round(ceiling - val);
      $('#pendingDiffText').text('Pending - ' + diff + '.');
      $('#pendingDiffPreview').show();
    } else {
      $('#pendingDiffPreview').hide();
    }
  }).trigger('input');
});
</script>
@endif

<script>
$(document).ready(function() {
  $('.btn-trigger-cancel').on('click', function() {
    const actionUrl = $(this).data('action');
    const desc = $(this).data('desc');

    $('#cancelModalForm').attr('action', actionUrl);
    $('#cancel-target-desc').text(desc);
    $('#cancel-modal-title').text('Cancel Salary Order');
    $('#cancel-modal-warning').text('Cancelling this salary order will mark sor_status as Cancelled and automatically cancel any active awaited commitment in fin.commitments.');
    $('#cancelModal').modal('show');
  });
});
</script>
@endsection
