{{-- resources/views/hr/salary/orders/index.blade.php --}}
@extends('welcome')

@push('styles')
<style>
  /* Scrutinized / Verified Green Row Styling */
  tr.row-scrutinized-green {
    background: #ecfdf5 !important;
    border-left: 4px solid #10b981 !important;
  }
  tr.row-scrutinized-green:hover {
    background: #d1fae5 !important;
  }
  /* Split Salary Order Group Styling */
  tr.split-order-group {
    background: #f8fafc;
    border-left: 4px solid #0284c7 !important;
  }
  tr.split-order-group:hover {
    background: #f0f9ff !important;
  }
  tr.split-order-group.row-scrutinized-green {
    background: #ecfdf5 !important;
    border-left: 4px solid #10b981 !important;
  }
  .sor-col-filter {
    height: 26px !important;
    font-size: 11px !important;
    padding: 2px 4px !important;
    background-color: #ffffff;
    border: 1px solid #cbd5e1;
  }
  .sor-col-filter:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
  }
</style>
@endpush

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
    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
      @if(($status ?? 'Draft') === 'Draft' && strtolower(trim((string) (auth()->user()->acc_untarea ?? ''))) === 'fin' && (auth()->user()->acc_auth ?? '') === 'approver')
        <button type="button" id="btn-bulk-approve" class="btn btn-sm btn-success font-weight-bold shadow-sm" style="display: none;">
          <i class="fas fa-check-double mr-1"></i> Approve Selected (<span id="selected-orders-count">0</span>)
        </button>
        <button type="button" id="btn-approve-all" class="btn btn-sm btn-outline-success font-weight-bold">
          <i class="fas fa-tasks mr-1"></i> Approve All
        </button>
      @endif

      @if(in_array($status ?? '', ['Draft', 'Open', 'Approved'], true))
        <a href="{{ route('divhr.salary.orders.print_sheet', ['month' => $month, 'status' => $status]) }}" target="_blank" class="btn btn-sm btn-dark font-weight-bold shadow-sm" title="Print Consolidated Salary Orders Sheet">
          <i class="fas fa-print mr-1"></i> Print Orders Sheet
        </a>
      @endif
      <a href="{{ route('divhr.salary.orders.bank_scroll', ['month' => $month, 'status' => $status]) }}" target="_blank" class="btn btn-sm btn-outline-success font-weight-bold" title="Bank Batch Payment Scroll">
        <i class="fas fa-university mr-1"></i> Bank Scroll 
      </a>
      <a href="{{ route('salary_cases.index', ['month' => $month ?? '']) }}" class="btn btn-sm btn-primary font-weight-bold shadow-sm" title="Salary Order Verification Cases (Official Minute Workflow)">
        <i class="fas fa-file-invoice-dollar mr-1"></i> Minute Case Workflow
      </a>
      <button type="button" class="btn btn-sm btn-outline-warning font-weight-bold shadow-sm" id="btn-open-remaining-modal" data-toggle="modal" data-target="#remainingEmpsModal">
        <i class="fas fa-user-clock mr-1"></i> Remaining Employees
      </button>
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

  {{-- Master Form for Filter Bar, Global Search, and Server-Side Column Filters --}}
  <form id="sorMasterFilterForm" method="GET" action="{{ route('divhr.salary.orders.index') }}">
    <input type="hidden" name="status" value="{{ $status ?? 'Draft' }}">

    {{-- Filter Bar & Global Search --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
      <div class="card-body p-3">
        <div class="row align-items-center">
          <div class="col-md-2 mb-2 mb-md-0">
            <label class="small text-muted font-weight-bold mb-1">Filter Month</label>
            <input type="month" name="month" id="filter-month-input" class="form-control form-control-sm" value="{{ $month ?? '' }}" onchange="this.form.submit()">
          </div>
          <div class="col-md-5 mb-2 mb-md-0">
            <label class="small text-muted font-weight-bold mb-1">Status Filter (Exact sor_status)</label>
            <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
              <a href="{{ route('divhr.salary.orders.index', array_merge(request()->except(['page', 'status']), ['status' => 'Draft'])) }}" class="btn btn-sm {{ ($status ?? 'Draft') === 'Draft' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-secondary' }}" title="Draft salary orders">
                Draft
              </a>
              <a href="{{ route('divhr.salary.orders.index', array_merge(request()->except(['page', 'status']), ['status' => 'Open'])) }}" class="btn btn-sm {{ in_array($status, ['Open', 'Approved'], true) ? 'btn-info font-weight-bold' : 'btn-outline-secondary' }}" title="Open / Approved salary orders">
                Open <small class="text-muted d-none d-lg-inline">(Approved)</small>
              </a>
              <a href="{{ route('divhr.salary.orders.index', array_merge(request()->except(['page', 'status']), ['status' => 'Closed'])) }}" class="btn btn-sm {{ in_array($status, ['Closed', 'Fulfilled', 'Cancelled'], true) ? 'btn-danger font-weight-bold' : 'btn-outline-secondary' }}" title="Closed / Fulfilled orders">
                Closed <small class="text-muted d-none d-lg-inline">(Fulfilled)</small>
              </a>
            </div>
          </div>
          <div class="col-md-5 mt-2 mt-md-0">
            <label class="small text-muted font-weight-bold mb-1">Global Search Across All Pages</label>
            <div class="input-group input-group-sm">
              <input type="text" name="search" class="form-control form-control-sm" value="{{ $search ?? '' }}" placeholder="Employee Name, ID, Order #, Head, Bank Account, Remarks..." style="border-radius: 4px 0 0 4px;">
              <div class="input-group-append">
                <button type="submit" class="btn btn-primary btn-sm px-3" title="Search all pages">
                  <i class="fas fa-search mr-1"></i> Search
                </button>
                @if(!empty($search) || !empty(array_filter($colFilters ?? [])))
                  <a href="{{ route('divhr.salary.orders.index', ['month' => $month, 'status' => $status ?? 'Draft']) }}" class="btn btn-outline-secondary btn-sm" title="Clear Search & Filters">
                    <i class="fas fa-times mr-1"></i> Clear
                  </a>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Orders Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 8px; background: #ffffff;">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle" style="font-size: 13px;">
            <thead style="background: #f8fafc; color: #475569; border-bottom: 2px solid #e2e8f0;">
              <tr>
                @if(($status ?? 'Draft') === 'Draft')
                  <th style="width: 40px;" class="text-center">
                    <input type="checkbox" id="check-all-orders" title="Select All">
                  </th>
                @endif
                <th style="width: 105px;">Order</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Cont. Salary</th>
                <th>Calculated</th>
                <th>Payable</th>
                <th class="text-center" style="width: 48px;" title="Scrutiny Verification (Persists)">Ver.</th>
                <th>Bank Account & Title</th>
                <th>Remarks</th>
                <th style="width: 140px;">Add. Remarks</th>
                <th class="text-center">Order Status</th>
                <th class="text-center">Commitment</th>
                <th style="width: 170px;" class="text-center">Actions</th>
              </tr>
              {{-- Server-Side Column Filter Row (filters all pages) --}}
              <tr class="sor-filter-row" style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                @if(($status ?? 'Draft') === 'Draft')
                  <th class="p-1 text-center align-middle" style="width: 40px;">
                    <a href="{{ route('divhr.salary.orders.index', ['month' => $month, 'status' => $status ?? 'Draft']) }}" class="btn btn-xs btn-outline-secondary p-0" title="Reset Column Filters" style="width: 22px; height: 22px; line-height: 20px;">
                      <i class="fas fa-undo-alt" style="font-size: 10px;"></i>
                    </a>
                  </th>
                @endif
                <th class="p-1">
                  <input type="text" name="col_order" class="form-control sor-col-filter" value="{{ $colFilters['col_order'] ?? '' }}" placeholder="Order #...">
                </th>
                <th class="p-1">
                  <input type="text" name="col_emp" class="form-control sor-col-filter" value="{{ $colFilters['col_emp'] ?? '' }}" placeholder="Emp / Name...">
                </th>
                <th class="p-1">
                  <input type="text" name="col_dept" class="form-control sor-col-filter" value="{{ $colFilters['col_dept'] ?? '' }}" placeholder="Dept / Head...">
                </th>
                <th class="p-1">
                  <input type="text" name="col_cont" class="form-control sor-col-filter" value="{{ $colFilters['col_cont'] ?? '' }}" placeholder="Contract...">
                </th>
                <th class="p-1">
                  <input type="text" name="col_calc" class="form-control sor-col-filter" value="{{ $colFilters['col_calc'] ?? '' }}" placeholder="Calc...">
                </th>
                <th class="p-1">
                  <input type="text" name="col_payable" class="form-control sor-col-filter" value="{{ $colFilters['col_payable'] ?? '' }}" placeholder="Payable...">
                </th>
                <th class="p-1 text-center align-middle" style="width: 48px;">
                  <button type="submit" class="btn btn-xs btn-primary p-0" title="Apply Column Filters" style="width: 24px; height: 24px; line-height: 22px;">
                    <i class="fas fa-filter" style="font-size: 11px;"></i>
                  </button>
                </th>
                <th class="p-1">
                  <input type="text" name="col_account" class="form-control sor-col-filter" value="{{ $colFilters['col_account'] ?? '' }}" placeholder="Account...">
                </th>
                <th class="p-1">
                  <input type="text" name="col_remarks" class="form-control sor-col-filter" value="{{ $colFilters['col_remarks'] ?? '' }}" placeholder="Remarks...">
                </th>
                <th class="p-1">
                  <input type="text" name="col_remarks2" class="form-control sor-col-filter" value="{{ $colFilters['col_remarks2'] ?? '' }}" placeholder="Add. Remarks...">
                </th>
                <th class="p-1">
                  <input type="text" name="col_status" class="form-control sor-col-filter" value="{{ $colFilters['col_status'] ?? '' }}" placeholder="Status...">
                </th>
                <th class="p-1">
                  <input type="text" name="col_cmt" class="form-control sor-col-filter" value="{{ $colFilters['col_cmt'] ?? '' }}" placeholder="Commitment...">
                </th>
                <th class="p-1 text-center align-middle">
                  @if(($status ?? 'Draft') !== 'Draft')
                    <a href="{{ route('divhr.salary.orders.index', ['month' => $month, 'status' => $status ?? 'Draft']) }}" class="btn btn-xs btn-outline-secondary p-0" title="Reset Filters" style="width: 22px; height: 22px; line-height: 20px;">
                      <i class="fas fa-undo-alt" style="font-size: 10px;"></i>
                    </a>
                  @else
                    <button type="submit" class="btn btn-xs btn-outline-primary px-2 font-weight-bold" style="height: 24px;">Filter</button>
                  @endif
                </th>
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
                  $displayName = $o->employee->emp_name ?? ($o->sor_empnamecomp ?: 'N/A');

                  $prjId = $o->head?->hed_prj_id ?: ($o->effectiveHead?->hed_prj_id ?: null);
                  $headCode = $o->head->hed_code ?? ($o->effectiveHead->hed_code ?? ($o->head->hed_name ?? ($o->effectiveHead->hed_name ?? 'Central')));
                  $isSplit = !is_null($o->sor_parent);
                  $splitGroupId = $isSplit ? ($o->sor_parent > 0 ? (int)$o->sor_parent : (int)$o->sor_id) : null;
                @endphp
                <tr class="sor-row {{ $o->sor_checked ? 'row-scrutinized-green' : '' }} {{ $isSplit ? 'split-order-group' : '' }}" id="sor-row-{{ $o->sor_id }}" data-split-group="{{ $splitGroupId }}">
                  @if(($status ?? 'Draft') === 'Draft')
                    <td class="text-center align-middle">
                      @if($o->sor_status === 'Draft' && $isFinanceApprover)
                        <input type="checkbox" class="order-checkbox" value="{{ $o->sor_id }}" data-split-group="{{ $splitGroupId }}">
                      @else
                        <span class="text-muted small">-</span>
                      @endif
                    </td>
                  @endif

                  {{-- 1. Order + Split Indicator --}}
                  <td class="align-middle">
                    <div class="font-weight-bold" style="color: #1e293b;">#{{ $o->sor_id }}</div>
                    @if($orderDate)
                      <div class="small text-muted" style="font-size: 11px;">{{ $orderDate }}</div>
                    @endif
                    @if($isSplit)
                      @if($o->sor_parent === 0)
                        <span class="badge mt-1 d-inline-block font-weight-bold shadow-2xs" style="font-size: 9.5px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;" title="Master / Part 1 of Split Order (Group #{{ $splitGroupId }})">
                          <i class="fas fa-sitemap mr-1"></i>Split Part 1
                        </span>
                      @else
                        <span class="badge mt-1 d-inline-block font-weight-bold shadow-2xs" style="font-size: 9.5px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;" title="Child / Part of Split Order (Parent: #{{ $o->sor_parent }})">
                          <i class="fas fa-code-branch mr-1"></i>Split Part (P: #{{ $o->sor_parent }})
                        </span>
                      @endif
                    @endif
                  </td>

                  {{-- 2. Employee (Clickable to Employee Details) --}}
                  <td class="align-middle">
                    <a href="{{ route('divhr.employeedetail', $o->sor_emp_id) }}" target="_blank" class="text-decoration-none text-dark d-block" title="Open Employee Details in New Tab">
                      <div class="small text-muted font-monospace" style="font-size: 11px;">
                        <i class="fas fa-id-badge text-primary mr-1"></i>{{ $o->sor_emp_id }}
                      </div>
                      <div class="font-weight-bold text-primary" style="font-size: 13.5px;">
                        {{ $displayName }} <i class="fas fa-external-link-alt ml-1 text-muted" style="font-size: 9px;"></i>
                      </div>
                    </a>
                    @if($isSplit)
                      <div class="mt-1">
                        <span class="badge badge-light border text-info font-weight-bold" style="font-size: 9.5px; background: #f0f9ff; border-color: #bae6fd !important; color: #0284c7 !important;" title="All parts of this split salary are grouped consecutively">
                          <i class="fas fa-link mr-1"></i>Split Group #{{ $splitGroupId }}
                        </span>
                      </div>
                    @endif
                  </td>

                  {{-- 3. Department / Head (Clickable to Project Financial View if applicable) --}}
                  <td class="align-middle">
                    <div class="font-weight-bold font-monospace" style="font-size: 13.5px; letter-spacing: 0.5px;">
                      @if($prjId)
                        <a href="{{ url('/openprojectdetails/' . $prjId . '/financial-view') }}" target="_blank" class="text-primary text-decoration-none" title="Open Project Financial Overview in New Tab">
                          {{ $headCode }} <i class="fas fa-external-link-alt ml-1 text-muted" style="font-size: 9px;"></i>
                        </a>
                      @else
                        <span class="text-dark">{{ $headCode }}</span>
                      @endif

                      @if($o->sor_sudohed === 'CHRF')
                        <span class="badge badge-warning text-dark font-weight-bold ml-1" style="font-size: 10px; background: #fef08a; border: 1px solid #facc15;" title="Paid From CSRF">* CSRF</span>
                      @endif
                    </div>
                    <div class="small text-muted" style="font-size: 11px;">{{ $o->unit->unt_namesh ?? ($o->unit->unt_name ?? '') }}</div>
                  </td>

                  {{-- 4. Cont. Salary --}}
                  <td class="align-middle">
                    <div class="font-weight-bold text-dark">{{ number_format($o->sor_ctrsalary ?: $o->sor_salary) }}</div>
                    <div class="small text-muted" style="font-size: 11px;">{{ $salMonth }}</div>
                  </td>

                  {{-- 5. Calculated --}}
                  <td class="align-middle">
                    <div class="font-weight-bold text-danger" style="font-size: 14px;">{{ number_format($o->sor_netsalary) }}</div>
                  </td>

                  {{-- 6. Payable --}}
                  <td class="align-middle">
                    <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ number_format($o->sor_salary) }}</div>
                    <div class="small text-muted font-monospace" style="font-size: 11px;">{{ $o->effectiveHead->hed_code ?? ($o->head->hed_code ?? ($o->effectiveHead->hed_name ?? '')) }}</div>
                  </td>

                  {{-- 7. Verified Checkbox (Interactive & Persistent AJAX) --}}
                  <td class="text-center align-middle">
                    <input type="checkbox" 
                           class="sor-ver-checkbox" 
                           data-id="{{ $o->sor_id }}" 
                           {{ $o->sor_checked ? 'checked' : '' }} 
                           title="{{ $o->sor_checked ? 'Verified / Scrutinized' : 'Click to mark scrutinized (Soft Green)' }}" 
                           style="cursor: pointer; width: 17px; height: 17px; accent-color: #10b981;">
                  </td>

                  {{-- 8. Bank Account & Title --}}
                  <td class="align-middle">
                    <div class="font-monospace text-dark font-weight-bold" style="font-size: 12px;">{{ $o->sor_bnkaccdetail ?: '(Pay by Cheque)' }}</div>
                    <div class="small text-muted text-truncate" style="max-width: 170px; font-size: 11.5px;">{{ $o->sor_bnkacctitle ?: ($o->employee->emp_name ?? '') }}</div>
                  </td>

                  {{-- 9. Remarks --}}
                  <td class="align-middle">
                    <div class="small text-dark" style="font-size: 12px;">{{ $o->sor_remarks ?: '-' }}</div>
                  </td>

                  {{-- 10. Add. Remarks --}}
                  <td class="align-middle">
                    <input type="text" 
                           class="form-control form-control-sm border-secondary-subtle" 
                           style="font-size: 12px; height: 28px;"
                           value="{{ $o->sor_remarks2 }}" 
                           placeholder="Remarks..." 
                           data-id="{{ $o->sor_id }}"
                           onchange="saveSorRemarks(this)">
                  </td>

                  {{-- 11. Order Status --}}
                  <td class="text-center align-middle">
                    <span class="badge px-2 py-1 font-weight-bold" style="{{ $sorBadgeStyle }}">
                      {{ $o->sor_status }}
                    </span>
                  </td>

                  {{-- 12. Commitment Status --}}
                  <td class="text-center align-middle">
                    {!! $cmtBadge !!}
                  </td>

                  {{-- 13. Actions: Approve, Return/Cancel, Single Order --}}
                  <td class="text-center align-middle">
                    <div class="d-flex align-items-center justify-content-center" style="gap: 4px;">
                      @if($o->sor_status === 'Draft' && $isFinanceApprover)
                        <button type="button" class="btn btn-xs btn-primary font-weight-bold px-2 btn-single-approve"
                                data-action="{{ route('divhr.salary.orders.approve', $o->sor_id) }}"
                                data-id="{{ $o->sor_id }}"
                                title="Approve Order">
                          Approve
                        </button>
                      @elseif($o->sor_status === 'Approved')
                        <span class="badge badge-light text-muted border px-2 py-1 font-weight-bold" style="font-size: 11px;">Approved</span>
                      @endif

                      @if(in_array($o->sor_status, ['Draft', 'Approved']) && (!$cmt || $cmt->cmt_status !== 'Paid'))
                        <button type="button" class="btn btn-xs btn-outline-warning btn-trigger-cancel font-weight-bold px-2"
                                data-action="{{ route('divhr.salary.orders.cancel', $o->sor_id) }}"
                                data-desc="Salary Order #{{ $o->sor_id }} - {{ $o->sor_empnamecomp }} ({{ number_format($o->sor_salary) }})"
                                title="Return Salary Order to Division Draft">
                          <i class="fas fa-undo-alt mr-1"></i> Return
                        </button>
                      @endif

                      <a href="{{ route('divhr.salary.orders.show', $o->sor_id) }}" class="btn btn-xs btn-outline-info font-weight-bold px-2" title="Open Single Salary Order">
                        <i class="fas fa-external-link-alt"></i>
                      </a>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="{{ ($status ?? 'Draft') === 'Draft' ? 14 : 13 }}" class="text-center py-5 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3 text-light" style="color: #cbd5e1 !important;"></i>
                    <div class="font-weight-bold">No salary orders found matching the search/filter criteria.</div>
                    <div class="small mt-1">Try clearing some filters or searching for another term.</div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination with Preserved Filters --}}
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
  </form>
</div>

{{-- Remaining Active Employees Modal (Excludes Released and Terminated) --}}
<div class="modal fade" id="remainingEmpsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-dark py-2 px-3 align-items-center">
        <h5 class="modal-title font-weight-bold mb-0" style="font-family: 'Rajdhani', sans-serif;">
          <i class="fas fa-user-clock mr-2"></i>Remaining Active Employees Without Draft Salary
        </h5>
        <div class="ml-auto d-flex align-items-center" style="gap: 10px;">
          <span class="badge badge-dark px-2 py-1 font-weight-bold" id="remaining-count-badge">Loading...</span>
          <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
        </div>
      </div>
      <div class="modal-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 10px;">
          <div class="small text-muted">
            <i class="fas fa-shield-alt text-success mr-1"></i> Strictly <strong>Active</strong> employees only. 
            <span class="badge badge-secondary ml-1">Released & Terminated Excluded</span>
          </div>
          <div style="width: 280px;">
            <input type="text" id="modal-remaining-filter" class="form-control form-control-sm" placeholder="Filter by Name, ID, Division, or Reason...">
          </div>
        </div>

        <div id="remaining-loading-spinner" class="text-center py-5">
          <div class="spinner-border text-warning" role="status">
            <span class="sr-only">Loading...</span>
          </div>
          <div class="text-muted small mt-2">Checking active employee contracts, requisitions, and orders...</div>
        </div>

        <div class="table-responsive" id="remaining-table-wrapper" style="display: none; max-height: 480px;">
          <table class="table table-sm table-hover table-bordered mb-0" style="font-size: 12.5px;">
            <thead class="thead-light">
              <tr>
                <th style="width: 40px;" class="text-center align-middle">#</th>
                <th style="width: 125px;" class="align-middle">EMP ID</th>
                <th class="align-middle">EMPLOYEE NAME & DESIGNATION</th>
                <th style="width: 125px;" class="align-middle">DIVISION</th>
                <th style="width: 155px;" class="align-middle">CATEGORY</th>
                <th class="align-middle">STATUS / REASON</th>
                <th style="width: 90px;" class="text-center align-middle">ACTION</th>
              </tr>
              <tr class="modal-col-filter-row" style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                <th class="p-1 text-center align-middle">
                  <button type="button" class="btn btn-xs btn-outline-secondary p-0 btn-reset-modal-col-filters" title="Reset Filters" style="width: 22px; height: 22px; line-height: 20px;">
                    <i class="fas fa-undo-alt" style="font-size: 10px;"></i>
                  </button>
                </th>
                <th class="p-1">
                  <input type="text" class="form-control form-control-sm modal-col-filter" data-col="emp_id" placeholder="ID..." style="height: 25px; font-size: 11px; padding: 2px 4px;">
                </th>
                <th class="p-1">
                  <input type="text" class="form-control form-control-sm modal-col-filter" data-col="name" placeholder="Name..." style="height: 25px; font-size: 11px; padding: 2px 4px;">
                </th>
                <th class="p-1">
                  <input type="text" class="form-control form-control-sm modal-col-filter" data-col="division" placeholder="Division..." style="height: 25px; font-size: 11px; padding: 2px 4px;">
                </th>
                <th class="p-1">
                  <select class="form-control form-control-sm modal-col-filter" id="modal-filter-category" data-col="category" style="height: 25px; font-size: 11px; padding: 0 4px;">
                    <option value="">All Categories</option>
                  </select>
                </th>
                <th class="p-1">
                  <input type="text" class="form-control form-control-sm modal-col-filter" data-col="reason" placeholder="Reason..." style="height: 25px; font-size: 11px; padding: 2px 4px;">
                </th>
                <th class="p-1 text-center align-middle">
                  <button type="button" class="btn btn-xs btn-outline-danger p-0 btn-reset-modal-col-filters" title="Reset Filters" style="width: 22px; height: 22px; line-height: 20px;">
                    <i class="fas fa-times" style="font-size: 10px;"></i>
                  </button>
                </th>
              </tr>
            </thead>
            <tbody id="remaining-tbody">
              <!-- Dynamically populated via AJAX -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-between align-items-center">
        <div class="small text-muted">
          Month: <strong class="text-dark">{{ $month ?? now()->format('Y-m') }}</strong>
        </div>
        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Include Cancellation Modal --}}
@include('hr.salary.partials.cancel_modal')

{{-- Hidden Bulk Approve Form --}}
<form id="bulkApproveForm" method="POST" action="{{ route('divhr.salary.orders.bulk_approve') }}" style="display: none;">
  @csrf
  <div id="bulkApproveInputs"></div>
</form>

{{-- Hidden Single Approve Form --}}
<form id="singleApproveForm" method="POST" action="" style="display: none;">
  @csrf
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
  // ── 1. Bulk Approve Selection ─────────────────────────────────────
  function updateBulkApproveState() {
    const checked = $('.order-checkbox:checked').length;
    $('#selected-orders-count').text(checked);
    if (checked > 0) {
      $('#btn-bulk-approve').fadeIn();
    } else {
      $('#btn-bulk-approve').hide();
    }
  }

  $('#check-all-orders').on('change', function() {
    $('.order-checkbox').prop('checked', $(this).is(':checked'));
    updateBulkApproveState();
  });

  $(document).on('change', '.order-checkbox', function() {
    const isChecked = $(this).is(':checked');
    const splitGroup = $(this).data('split-group');
    if (splitGroup) {
      $('.order-checkbox[data-split-group="' + splitGroup + '"]').prop('checked', isChecked);
    }
    updateBulkApproveState();
    if (!isChecked) {
      $('#check-all-orders').prop('checked', false);
    } else if ($('.order-checkbox:checked').length === $('.order-checkbox').length) {
      $('#check-all-orders').prop('checked', true);
    }
  });

  $('#btn-bulk-approve').on('click', function() {
    const selected = [];
    $('.order-checkbox:checked').each(function() {
      selected.push($(this).val());
    });
    if (selected.length === 0) return;

    if (!confirm('Are you sure you want to approve ' + selected.length + ' selected salary order(s)?')) {
      return;
    }

    const $container = $('#bulkApproveInputs');
    $container.empty();
    selected.forEach(function(id) {
      $container.append('<input type="hidden" name="sor_ids[]" value="' + id + '">');
    });
    $('#bulkApproveForm').submit();
  });

  $('#btn-approve-all').on('click', function() {
    const $boxes = $('.order-checkbox');
    if ($boxes.length === 0) {
      alert('No draft salary orders available to approve.');
      return;
    }
    if (!confirm('Are you sure you want to approve all ' + $boxes.length + ' draft salary order(s)?')) {
      return;
    }
    $boxes.prop('checked', true);
    updateBulkApproveState();
    $('#btn-bulk-approve').trigger('click');
  });

  $(document).on('click', '.btn-single-approve', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const action = $(this).data('action');
    if (confirm('Approve salary order #' + id + '?')) {
      const form = $('#singleApproveForm');
      form.attr('action', action);
      form.submit();
    }
  });

  $('.btn-trigger-cancel').on('click', function() {
    const actionUrl = $(this).data('action');
    const desc = $(this).data('desc');

    $('#cancelModalForm').attr('action', actionUrl);
    $('#cancel-target-desc').text(desc);
    $('#cancel-modal-title').text('Return Salary Order to Division');
    $('#cancel-modal-warning').text('Returning this salary order will revert the requisition back to the Division in Draft stage, release In-Process funds back to Remaining budget, and remove this draft order from Finance.');
    $('#cancelModal').modal('show');
  });

  // ── 2. "Ver." Checkbox & Persistent Soft Green Row via AJAX ────────
  $(document).on('change', '.sor-ver-checkbox', function() {
    const $checkbox = $(this);
    const sorId = $checkbox.data('id');
    const isChecked = $checkbox.is(':checked');
    const $row = $('#sor-row-' + sorId);

    if (isChecked) {
      $row.addClass('row-scrutinized-green');
    } else {
      $row.removeClass('row-scrutinized-green');
    }

    const toggleUrl = '{{ route("divhr.salary.orders.toggle_check", ["sor_id" => "___ID___"]) }}'.replace('___ID___', sorId);

    $.ajax({
      url: toggleUrl,
      type: 'PATCH',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      data: {
        _token: '{{ csrf_token() }}',
        checked: isChecked ? 1 : 0
      },
      success: function(res) {
        if (res && res.success) {
          $checkbox.attr('title', res.checked ? 'Verified / Scrutinized' : 'Click to mark scrutinized');
        } else {
          $checkbox.prop('checked', !isChecked);
          $row.toggleClass('row-scrutinized-green', !isChecked);
        }
      },
      error: function(err) {
        console.error('Scrutiny toggle error:', err);
        alert('Could not update scrutiny verification state. Reverting.');
        $checkbox.prop('checked', !isChecked);
        $row.toggleClass('row-scrutinized-green', !isChecked);
      }
    });
  });

  // ── 3. Submit Filters on Enter in Column Inputs ────────────────────
  $('.sor-col-filter').on('keypress', function(e) {
    if (e.which === 13) {
      e.preventDefault();
      $('#sorMasterFilterForm').submit();
    }
  });

  // ── 4. Remaining Active Employees Modal via AJAX ───────────────────
  let remainingCache = [];

  $('#remainingEmpsModal').on('show.bs.modal', function () {
    const monthVal = $('#filter-month-input').val() || '{{ $month ?? now()->format("Y-m") }}';
    $('#remaining-loading-spinner').show();
    $('#remaining-table-wrapper').hide();
    $('#remaining-count-badge').text('Checking...');

    $.ajax({
      url: '{{ route("divhr.salary.orders.remaining_employees_data") }}',
      type: 'GET',
      data: { month: monthVal },
      success: function(resp) {
        $('#remaining-loading-spinner').hide();
        $('#remaining-table-wrapper').show();

        if (resp.success && Array.isArray(resp.employees)) {
          remainingCache = resp.employees;
          $('#remaining-count-badge').text(remainingCache.length + ' Active Remaining');

          // Populate Category Dropdown dynamically
          const $catSelect = $('#modal-filter-category');
          $catSelect.html('<option value="">All Categories</option>');
          const categories = [...new Set(remainingCache.map(e => e.category).filter(Boolean))].sort();
          categories.forEach(cat => {
            $catSelect.append(`<option value="${cat}">${cat}</option>`);
          });

          renderRemainingTable(remainingCache);
        } else {
          $('#remaining-tbody').html('<tr><td colspan="7" class="text-center py-4 text-muted">No records found.</td></tr>');
        }
      },
      error: function() {
        $('#remaining-loading-spinner').hide();
        $('#remaining-table-wrapper').show();
        $('#remaining-tbody').html('<tr><td colspan="7" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Failed to load remaining employees data.</td></tr>');
      }
    });
  });

  function renderRemainingTable(list) {
    const $tbody = $('#remaining-tbody');
    $tbody.empty();

    if (list.length === 0) {
      $tbody.html('<tr><td colspan="7" class="text-center py-4 text-muted"><i class="fas fa-search mr-2 text-warning"></i>No matching active employees found for the filter criteria.</td></tr>');
      return;
    }

    list.forEach(function(item, idx) {
      const empDetailUrl = '{{ url("divhr/employee") }}/' + encodeURIComponent(item.emp_id);
      const row = `
        <tr class="modal-emp-row">
          <td class="text-center text-muted font-weight-bold align-middle">${idx + 1}</td>
          <td class="align-middle">
            <a href="${empDetailUrl}" target="_blank" class="font-monospace font-weight-bold text-primary" title="Open Employee Details">
              ${item.emp_id} <i class="fas fa-external-link-alt" style="font-size: 9px;"></i>
            </a>
          </td>
          <td class="align-middle">
            <a href="${empDetailUrl}" target="_blank" class="text-dark font-weight-bold text-decoration-none">
              <div>${item.name} <i class="fas fa-external-link-alt ml-1 text-muted" style="font-size: 9px;"></i></div>
            </a>
            <div class="small text-muted" style="font-size: 11px;">${item.designation}</div>
          </td>
          <td class="align-middle">
            <span class="badge badge-light border text-dark font-weight-bold">${item.division}</span>
          </td>
          <td class="align-middle">
            <span class="badge ${item.badge_class} px-2 py-1 font-weight-bold" style="font-size: 11px;">${item.category}</span>
          </td>
          <td class="align-middle">
            <span class="text-dark small">${item.reason}</span>
          </td>
          <td class="text-center align-middle">
            <a href="${empDetailUrl}" target="_blank" class="btn btn-xs btn-outline-primary font-weight-bold px-2" title="View Employee Details">
              Details
            </a>
          </td>
        </tr>
      `;
      $tbody.append(row);
    });
  }

  // Live filter combining Top Search + Column Filters inside the modal
  function applyModalFilters() {
    const globalTerm = $.trim($('#modal-remaining-filter').val()).toLowerCase();
    const idTerm     = $.trim($('.modal-col-filter[data-col="emp_id"]').val()).toLowerCase();
    const nameTerm   = $.trim($('.modal-col-filter[data-col="name"]').val()).toLowerCase();
    const divTerm    = $.trim($('.modal-col-filter[data-col="division"]').val()).toLowerCase();
    const catTerm    = $.trim($('#modal-filter-category').val()).toLowerCase();
    const reasonTerm = $.trim($('.modal-col-filter[data-col="reason"]').val()).toLowerCase();

    const filtered = remainingCache.filter(function(emp) {
      if (globalTerm) {
        const matchesGlobal = (emp.emp_id && emp.emp_id.toLowerCase().indexOf(globalTerm) !== -1) ||
                              (emp.name && emp.name.toLowerCase().indexOf(globalTerm) !== -1) ||
                              (emp.division && emp.division.toLowerCase().indexOf(globalTerm) !== -1) ||
                              (emp.category && emp.category.toLowerCase().indexOf(globalTerm) !== -1) ||
                              (emp.reason && emp.reason.toLowerCase().indexOf(globalTerm) !== -1);
        if (!matchesGlobal) return false;
      }
      if (idTerm && (!emp.emp_id || emp.emp_id.toLowerCase().indexOf(idTerm) === -1)) {
        return false;
      }
      if (nameTerm && (!emp.name || emp.name.toLowerCase().indexOf(nameTerm) === -1)) {
        return false;
      }
      if (divTerm && (!emp.division || emp.division.toLowerCase().indexOf(divTerm) === -1)) {
        return false;
      }
      if (catTerm && (!emp.category || emp.category.toLowerCase() !== catTerm)) {
        return false;
      }
      if (reasonTerm && (!emp.reason || emp.reason.toLowerCase().indexOf(reasonTerm) === -1)) {
        return false;
      }
      return true;
    });

    renderRemainingTable(filtered);
  }

  $('#modal-remaining-filter').on('input', applyModalFilters);
  $(document).on('input change', '.modal-col-filter', applyModalFilters);

  $(document).on('click', '.btn-reset-modal-col-filters', function() {
    $('.modal-col-filter').val('');
    $('#modal-remaining-filter').val('');
    renderRemainingTable(remainingCache);
  });
});

function saveSorRemarks(input) {
  const id = $(input).data('id');
  const val = $(input).val();
  const remarksUrl = '{{ route("divhr.salary.orders.remarks2", ["sor_id" => "___ID___"]) }}'.replace('___ID___', id);
  $.ajax({
    url: remarksUrl,
    type: 'PATCH',
    headers: {
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
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
