{{-- resources/views/divhr/salary-heads.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper px-3 py-3" style="background: #f4f6f9; min-height: 90vh;">
  
  {{-- Header & Breadcrumb --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="font-weight-bold mb-0 text-dark" style="font-family: 'Rajdhani', sans-serif;">
        <i class="fas fa-layer-group text-success mr-2"></i>Monthly Salary Heads
      </h4>
      <div class="text-muted small">
        <a href="{{ url('/divhr/employeelist') }}" class="text-muted">HR</a> / 
        <a href="{{ url('/divhr/employee/' . $id) }}" class="text-muted">{{ $emp->emp_name ?? 'Employee' }} ({{ $id }})</a> / 
        <strong class="text-success">Salary Heads</strong>
      </div>
    </div>
    <div>
      <a href="{{ url('/divhr/employee/' . $id) }}" class="btn btn-sm btn-outline-secondary font-weight-bold shadow-sm">
        <i class="fas fa-arrow-left mr-1"></i> Back to Profile
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

  {{-- Employee & Current Contract Info Banner --}}
  <div class="card border-0 shadow-sm mb-3" style="border-radius: 10px; background: #ffffff;">
    <div class="card-body p-3">
      <div class="row align-items-center">
        <div class="col-md-7">
          <div class="d-flex align-items-center">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white mr-3 font-weight-bold shadow-sm"
                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #059669, #10b981); font-size: 18px;">
              {{ strtoupper(substr($emp->emp_name ?? 'E', 0, 1)) }}
            </div>
            <div>
              <div class="h5 font-weight-bold mb-0 text-dark">
                {{ $emp->emp_name ?? 'Employee' }}
                @if(!empty($isCentralEmp))
                  <span class="badge badge-success ml-2 px-2 py-0.5 shadow-sm font-weight-bold" style="font-size: 11px;">
                    <i class="fas fa-landmark mr-1"></i> CSRF Funded
                  </span>
                @endif
              </div>
              <div class="text-muted small">
                <span class="font-monospace font-weight-bold text-dark mr-2"><i class="fas fa-id-badge mr-1 text-muted"></i>{{ $emp->emp_id ?? $id }}</span>
                <span class="mr-2">&bull;</span>
                <span class="text-secondary font-weight-bold mr-2">{{ $emp->emp_rank ?: ($emp->emp_title ?: 'Contract Staff') }}</span>
                <span class="mr-2">&bull;</span>
                <span class="text-primary font-weight-bold"><i class="fas fa-building mr-1"></i>{{ $emp->unt_name ?? 'Division' }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-5 text-md-right mt-2 mt-md-0">
          @if($selectedContract)
            <div class="d-inline-block text-left p-2 rounded border" style="background: #f8fafc; font-size: 12px;">
              <div class="text-muted small text-uppercase font-weight-bold">Current Contract (#{{ $selectedContract->ctr_id }})</div>
              <div class="font-weight-bold text-dark">
                <i class="far fa-calendar-alt text-primary mr-1"></i>
                {{ \Carbon\Carbon::parse($selectedContract->ctr_startdt)->format('d M Y') }} &mdash; 
                {{ \Carbon\Carbon::parse($selectedContract->ctr_termindt ?: $selectedContract->ctr_enddt)->format('d M Y') }}
              </div>
            </div>
          @else
            <span class="badge badge-warning">No Active Contract Found</span>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Monthly Salary Heads Form --}}
  @if(!$selectedContract)
    <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 10px;">
      <div class="card-body">
        <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
        <h5 class="text-dark font-weight-bold">No Active Contract Found</h5>
        <p class="text-muted small mb-0">This employee does not have an active service contract to manage monthly project heads.</p>
      </div>
    </div>
  @else
    <form action="{{ route('divhr.employee.salary_heads.update', ['id' => $id]) }}" method="POST">
      @csrf
      <input type="hidden" name="target_month" value="{{ $targetMonth }}">

      <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px; overflow: hidden; background: #ffffff;">
        <div class="card-header py-2.5 px-3 bg-white border-bottom d-flex justify-content-between align-items-center">
          <div>
            <span class="font-weight-bold text-dark">
              <i class="fas fa-calendar-alt text-success mr-1.5"></i> 
              Contract Monthly Project Allocation {{ !empty($isCentralEmp) ? '(CSRF)' : '' }}
            </span>
            <span class="badge badge-light border text-muted ml-2">{{ $plans->count() }} Months</span>
          </div>
          <div class="small text-muted">
            <i class="fas fa-info-circle mr-1 text-primary"></i> 
            Past &amp; paid months are locked. Current and future months can be assigned to {{ !empty($isCentralEmp) ? 'CSRF project heads' : 'division projects' }}.
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
            <thead style="background: #f8fafc; color: #475569; border-bottom: 2px solid #e2e8f0;">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th style="width: 220px;">Month / Period</th>
                <th style="width: 170px;">Status</th>
                <th>Assigned {{ !empty($isCentralEmp) ? 'CSRF Project Head' : 'Division Project / Salary Head' }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse($plans as $idx => $plan)
                @php
                  $isTarget = $targetMonth && ($plan->plan_month === $targetMonth);
                  $rowClass = '';
                  $rowStyle = '';
                  if ($isTarget) {
                    $rowClass = 'highlight-target-row';
                    $rowStyle = 'background: #fffbeb !important; border-left: 4px solid #f59e0b !important;';
                  } elseif ($plan->is_locked) {
                    $rowStyle = 'background: #fbfcfd;';
                  }
                @endphp
                <tr class="{{ $rowClass }}" style="{{ $rowStyle }}" data-month="{{ $plan->plan_month }}">
                  {{-- Index --}}
                  <td class="text-center font-monospace text-muted font-weight-bold">
                    {{ $idx + 1 }}
                  </td>

                  {{-- Month / Period --}}
                  <td>
                    <div class="font-weight-bold {{ $isTarget ? 'text-amber-900' : 'text-dark' }}">
                      {{ \Carbon\Carbon::parse($plan->cpn_startdt)->format('F Y') }}
                    </div>
                    <div class="text-muted small font-monospace" style="font-size: 11px;">
                      {{ \Carbon\Carbon::parse($plan->cpn_startdt)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($plan->cpn_enddt)->format('d M Y') }}
                    </div>
                  </td>

                  {{-- Status Badges --}}
                  <td>
                    @if($plan->is_salary_generated)
                      <span class="badge badge-secondary px-2 py-1 shadow-sm font-weight-bold" style="font-size: 11px;" title="{{ $plan->lock_reason }}">
                        <i class="fas fa-lock text-warning mr-1"></i> {{ $plan->lock_reason ?: 'Paid (Locked)' }}
                      </span>
                    @elseif($plan->is_past_month)
                      {{-- Past month but salary NOT paid — editable --}}
                      <span class="badge px-2 py-1 shadow-sm font-weight-bold" style="font-size: 11px; background: #fef3c7; color: #92400e; border: 1px solid #f59e0b;">
                        <i class="fas fa-edit mr-1"></i> Unpaid (Editable)
                      </span>
                    @elseif($plan->plan_month === now()->format('Y-m'))
                      <span class="badge badge-success px-2 py-1 shadow-sm font-weight-bold" style="font-size: 11px;">
                        <i class="fas fa-calendar-check mr-1"></i> Current Month
                      </span>
                    @else
                      <span class="badge badge-info px-2 py-1 shadow-sm font-weight-bold" style="font-size: 11px; background: #0284c7;">
                        <i class="fas fa-calendar-plus mr-1"></i> Future Month
                      </span>
                    @endif

                    @if($isTarget)
                      <div class="mt-1">
                        <span class="badge badge-warning text-dark font-weight-bold shadow-sm" style="font-size: 10px;">
                          <i class="fas fa-crosshairs mr-1"></i> Salary Generation Month
                        </span>
                      </div>
                    @endif
                  </td>

                  {{-- Project Head Selector / Locked Display --}}
                  <td>
                    @if($plan->is_locked)
                      {{-- Read-Only Display for Locked Months --}}
                      <div class="d-flex align-items-center">
                        <i class="fas fa-lock text-muted mr-2" title="{{ $plan->lock_reason }}"></i>
                        @if($plan->cpn_hed_id)
                          <div>
                            <span class="badge badge-light border font-monospace text-dark mr-1 font-weight-bold">
                              {{ $plan->hed_code ?: 'HEAD #' . $plan->cpn_hed_id }}
                            </span>
                            <strong class="text-dark">{{ $plan->hed_name ?: 'Assigned Project Head' }}</strong>
                            @if(!empty($isCentralEmp) || str_contains($plan->hed_name ?? '', 'CSRF'))
                              <span class="badge badge-info ml-1 px-1.5 py-0.5" style="font-size: 10px;">CSRF</span>
                            @endif
                            @if($plan->prj_title)
                              <span class="text-muted small ml-1 font-italic">&mdash; {{ $plan->prj_title }}</span>
                            @endif
                          </div>
                        @else
                          <span class="text-muted font-italic small">
                            <i class="fas fa-minus-circle mr-1 text-secondary"></i> Not Set (No Project Assigned)
                          </span>
                        @endif
                      </div>

                      {{-- If multiple heads or multiple requisitions were generated for this month, display all of them --}}
                      @if(!empty($plan->sal_reqs) && $plan->sal_reqs->count() > 1)
                        <div class="mt-1.5 p-2 rounded border" style="background: #f8fafc; font-size: 11.5px;">
                          <div class="font-weight-bold text-muted mb-1">
                            <i class="fas fa-layer-group text-primary mr-1"></i> Multiple Salary Heads Paid for This Month ({{ $plan->sal_reqs->count() }}):
                          </div>
                          @foreach($plan->sal_reqs as $sr)
                            <div class="d-flex align-items-center mb-1">
                              <span class="badge badge-light border font-monospace text-dark mr-1">[{{ $sr->hed_code ?: 'HEAD #' . $sr->srq_hed_id }}]</span>
                              <strong class="text-dark mr-1.5">{{ $sr->hed_name ?: 'Project Head' }}</strong>
                              @if(!empty($isCentralEmp) || str_contains($sr->hed_name ?? '', 'CSRF'))
                                <span class="badge badge-info mr-1" style="font-size: 9.5px;">CSRF</span>
                              @endif
                              <span class="badge badge-secondary mr-1" style="font-size: 9.5px;">{{ $sr->srq_status }}</span>
                              <span class="text-success font-weight-bold ml-auto">PKR {{ number_format($sr->srq_salary, 2) }}</span>
                            </div>
                          @endforeach
                        </div>
                      @endif

                      <div class="text-muted font-italic mt-0.5" style="font-size: 11px;">
                        <i class="fas fa-shield-alt mr-1 text-secondary"></i> {{ $plan->lock_reason }} &mdash; cannot be altered.
                      </div>
                    @else
                      {{-- Editable Dropdown for Current and Future Months --}}
                      <div class="d-flex align-items-center" style="max-width: 650px;">
                        <select name="plans[{{ $plan->cpn_id }}]" 
                                class="form-control form-control-sm font-weight-bold salary-head-select {{ $isTarget ? 'border-warning shadow-sm' : '' }}"
                                {{ !$canEdit ? 'disabled' : '' }}
                                style="font-size: 12.5px; border-radius: 6px;">
                          <option value="">-- {{ !empty($isCentralEmp) ? 'Select CSRF Project Head (Empty / Not Set)' : 'Select Division Project Head (Empty / Not Set)' }} --</option>
                          @foreach($divisionProjects as $dp)
                            <option value="{{ $dp->hed_id }}" 
                              {{ (string)$plan->cpn_hed_id === (string)$dp->hed_id ? 'selected' : '' }}>
                              [{{ $dp->hed_code }}] {{ $dp->hed_name }} {{ $dp->prj_title ? ' - ' . $dp->prj_title : '' }}
                            </option>
                          @endforeach

                          {{-- Edge case: If assigned head is not in division list, show it selected --}}
                          @if($plan->cpn_hed_id && !$divisionProjects->contains('hed_id', $plan->cpn_hed_id))
                            <option value="{{ $plan->cpn_hed_id }}" selected>
                              [{{ $plan->hed_code ?: 'ID ' . $plan->cpn_hed_id }}] {{ $plan->hed_name ?: 'Assigned Project Head' }} {{ !empty($isCentralEmp) ? '(CSRF)' : '' }}
                            </option>
                          @endif
                        </select>

                        @if(!$plan->cpn_hed_id)
                          <span class="badge badge-danger ml-2 px-2 py-1 text-nowrap" style="font-size: 10px;" title="This month has no project head assigned yet">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Head Not Set
                          </span>
                        @endif
                      </div>

                      {{-- If multiple requisitions existed, also show them here --}}
                      @if(!empty($plan->sal_reqs) && $plan->sal_reqs->count() > 1)
                        <div class="mt-1.5 p-2 rounded border" style="background: #f8fafc; font-size: 11.5px;">
                          <div class="font-weight-bold text-muted mb-1">
                            <i class="fas fa-layer-group text-primary mr-1"></i> Salary Heads Generated for This Month ({{ $plan->sal_reqs->count() }}):
                          </div>
                          @foreach($plan->sal_reqs as $sr)
                            <div class="d-flex align-items-center mb-1">
                              <span class="badge badge-light border font-monospace text-dark mr-1">[{{ $sr->hed_code ?: 'HEAD #' . $sr->srq_hed_id }}]</span>
                              <strong class="text-dark mr-1.5">{{ $sr->hed_name ?: 'Project Head' }}</strong>
                              <span class="badge badge-secondary mr-1" style="font-size: 9.5px;">{{ $sr->srq_status }}</span>
                              <span class="text-success font-weight-bold ml-auto">PKR {{ number_format($sr->srq_salary, 2) }}</span>
                            </div>
                          @endforeach
                        </div>
                      @endif
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-4 font-italic">
                    No contract monthly breakdown found.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Bottom Actions Bar --}}
        <div class="card-footer py-2.5 px-3 bg-white border-top d-flex justify-content-between align-items-center">
          <a href="{{ url('/divhr/employee/' . $id) }}" class="btn btn-sm btn-outline-secondary font-weight-bold">
            Cancel
          </a>
          @if($canEdit)
            <button type="submit" class="btn btn-sm btn-success font-weight-bold px-4 shadow-sm">
              <i class="fas fa-save mr-1.5"></i> Save Changes
            </button>
          @endif
        </div>
      </div>
    </form>
  @endif

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // If a target month was requested (e.g. from Salary Generation page), scroll to it & highlight
  const targetRow = document.querySelector('.highlight-target-row');
  if (targetRow) {
    targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    const select = targetRow.querySelector('select');
    if (select) {
      setTimeout(() => {
        select.focus();
      }, 500);
    }
  }
});
</script>
<style>
.highlight-target-row {
  animation: pulseHighlight 2s ease-in-out infinite alternate;
}
@keyframes pulseHighlight {
  0% {
    background-color: #fef3c7 !important;
  }
  100% {
    background-color: #fffbeb !important;
  }
}
</style>
@endpush
@endsection
