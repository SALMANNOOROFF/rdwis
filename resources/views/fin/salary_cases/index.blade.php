{{-- resources/views/fin/salary_cases/index.blade.php --}}
@extends('welcome')

@section('content')
<div class="content-wrapper px-3 py-3" style="background: #f4f6f9;">
    {{-- Header Row --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap: 12px;">
        <div>
            <h3 class="font-weight-bold mb-0 text-dark" style="font-family: 'Rajdhani', sans-serif; letter-spacing: 0.5px;">
                <i class="fas fa-file-invoice-dollar text-primary mr-2"></i> Salary Order Verification Cases
            </h3>
            <div class="text-muted small">
                <a href="{{ route('divhr.salary.orders.index') }}" class="text-muted">Salary Orders</a> / 
                <strong class="text-primary">Verification Cases (Minute Workflow)</strong>
            </div>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
            @if($isFinance)
                <button type="button" class="btn btn-primary font-weight-bold shadow-sm" data-toggle="modal" data-target="#initiateCaseModal" style="border-radius: 6px; font-size: 13.5px; padding: 7px 18px; background: #0f172a; border-color: #0f172a;">
                    <i class="fas fa-plus-circle mr-1.5 text-warning"></i> Initiate Salary Case
                </button>
            @endif
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

    {{-- Tabs & Filters Bar --}}
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 8px; background: #ffffff;">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                {{-- Tabs --}}
                <div class="btn-group" role="group">
                    <a href="{{ route('salary_cases.index', ['tab' => 'pending', 'month' => $monthFilter, 'search' => $search]) }}" class="btn btn-sm {{ $tab === 'pending' ? 'btn-warning text-dark font-weight-bold shadow-sm' : 'btn-outline-secondary' }}" style="font-size: 13px; padding: 6px 16px;">
                        <i class="fas fa-clock mr-1"></i> Pending Action
                        @if($pendingCount > 0)
                            <span class="badge badge-danger ml-1.5">{{ $pendingCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('salary_cases.index', ['tab' => 'open', 'month' => $monthFilter, 'search' => $search]) }}" class="btn btn-sm {{ $tab === 'open' ? 'btn-primary font-weight-bold shadow-sm' : 'btn-outline-secondary' }}" style="font-size: 13px; padding: 6px 16px;">
                        <i class="fas fa-spinner mr-1"></i> Open / In Progress
                        <span class="badge badge-light border text-dark ml-1.5">{{ $openCount }}</span>
                    </a>
                    <a href="{{ route('salary_cases.index', ['tab' => 'closed', 'month' => $monthFilter, 'search' => $search]) }}" class="btn btn-sm {{ $tab === 'closed' ? 'btn-dark font-weight-bold shadow-sm' : 'btn-outline-secondary' }}" style="font-size: 13px; padding: 6px 16px;">
                        <i class="fas fa-archive mr-1"></i> Closed / Cancelled
                        <span class="badge badge-light border text-dark ml-1.5">{{ $closedCount }}</span>
                    </a>
                </div>

                {{-- Search & Month Form --}}
                <form method="GET" action="{{ route('salary_cases.index') }}" class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    
                    <div class="input-group input-group-sm" style="width: 170px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light text-muted"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <input type="month" name="month" class="form-control form-control-sm" value="{{ $monthFilter ?? '' }}" onchange="this.form.submit()" title="Filter by Month">
                    </div>

                    <div class="input-group input-group-sm" style="width: 220px;">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title or minute..." value="{{ $search }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                        </div>
                    </div>

                    @if($monthFilter || $search)
                        <a href="{{ route('salary_cases.index', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-danger" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Cases Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 8px; background: #ffffff;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle" style="font-size: 13px;">
                    <thead style="background: #f8fafc; color: #475569; border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th style="width: 90px;" class="text-center">Case #</th>
                            <th style="width: 130px;">Minute No</th>
                            <th>Month / Title</th>
                            <th class="text-center" style="width: 130px;">Employees (Paid/Due)</th>
                            <th class="text-right" style="width: 140px;">Payments</th>
                            <th class="text-right" style="width: 160px;">Total Amount (PKR)</th>
                            <th>Current Stage / Holder</th>
                            <th class="text-center" style="width: 140px;">Status</th>
                            <th class="text-center" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($displayCases as $c)
                            @php
                                $rawStatus = trim($c->soc_status ?? '');
                                $displayStatus = in_array(strtolower($rawStatus), ['draft', 'pending action']) ? 'Pending Action' : $rawStatus;
                                $statusBadge = match(strtolower($displayStatus)) {
                                    'pending action', 'pending' => 'badge-warning text-dark font-weight-bold',
                                    'under review', 'under approval' => 'badge-info text-white font-weight-bold',
                                    'approved' => 'badge-success font-weight-bold',
                                    'returned', 'cancelled', 'rejected' => 'badge-danger font-weight-bold',
                                    default => 'badge-secondary font-weight-bold',
                                };
                                $holderDesig = \App\Services\SalaryCaseService::getOfficialDesignation($c->soc_current_acc_id);
                                $displayHolder = $c->soc_current_holder ?: ($holderDesig ?: ($c->soc_current_stage ?? 'Finance'));
                            @endphp
                            <tr>
                                <td class="text-center font-weight-bold text-muted">
                                    #{{ $c->soc_id }}
                                </td>
                                <td>
                                    <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 12px; font-family: 'Rajdhani', sans-serif;">
                                        {{ $c->soc_minute_no ?: 'Minute --' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('salary_cases.show', $c->soc_id) }}" class="font-weight-bold text-primary" style="font-size: 13.5px; text-decoration: none;">
                                        {{ $c->soc_title }}
                                    </a>
                                    <div class="small text-muted">
                                        <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::createFromFormat('Y-m', $c->soc_month)->format('F Y') }}
                                        &bull; Channel: <span class="text-dark">{{ $c->soc_payment_channel }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="font-weight-bold text-dark">{{ $c->soc_total_paid_emps }}</span>
                                    <span class="text-muted">/ {{ $c->soc_total_due_emps }}</span>
                                    @if($c->soc_split_salaries_count > 0)
                                        <div class="small text-info font-weight-bold" style="font-size: 11px;">
                                            ({{ $c->soc_split_salaries_count }} split into {{ $c->soc_split_payments_count }})
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold text-dark">
                                    {{ $c->soc_total_payments }} orders
                                </td>
                                <td class="text-right font-weight-bold text-success" style="font-size: 14px; font-family: 'Courier New', monospace;">
                                    Rs. {{ number_format($c->soc_total_amount) }}
                                </td>
                                <td>
                                    <div class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                        <i class="fas fa-user-circle text-primary mr-1"></i> {{ $displayHolder }}
                                    </div>
                                    <div class="small text-muted" style="font-size: 11px;">
                                        Stage: <span class="badge badge-light border font-weight-bold">{{ $c->soc_current_stage }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $statusBadge }} px-2.5 py-1" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                                        {{ $displayStatus }}
                                    </span>
                                </td>
                                <td class="text-center" style="white-space: nowrap;">
                                    <div class="d-inline-flex align-items-center" style="gap: 4px;">
                                        @php
                                            $isFinalized = in_array(strtolower($c->soc_status), ['approved', 'closed', 'cancelled']);
                                        @endphp
                                        <a href="{{ route('salary_cases.show', $c->soc_id) }}" class="btn btn-sm btn-primary font-weight-bold px-2.5 py-1 shadow-sm d-inline-flex align-items-center" style="font-size: 12px; border-radius: 5px; background: #0f172a; border-color: #0f172a;">
                                            <i class="fas {{ $isFinalized ? 'fa-eye' : 'fa-folder-open' }} mr-1 text-warning"></i> {{ $isFinalized ? 'View Case' : 'Open & Scrutiny' }}
                                        </a>

                                        @if($isFinance && !$isFinalized)
                                            <a href="{{ route('salary_cases.show', $c->soc_id) }}" class="btn btn-sm btn-outline-secondary px-2 py-1" title="Edit Minute Details" style="font-size: 11px; border-radius: 5px;">
                                                <i class="fas fa-pen"></i>
                                            </a>

                                            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 btn-delete-salary-case" data-id="{{ $c->soc_id }}" data-minute="{{ $c->soc_minute_no ?: ('#' . $c->soc_id) }}" title="Delete Case" style="font-size: 11px; border-radius: 5px;">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                                    <h5 class="font-weight-bold text-dark">No cases found in this tab.</h5>
                                    <p class="small mb-0">Select another tab or initiate a new Salary Verification Case above.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Initiate Salary Case Modal --}}
@if($isFinance)
<div class="modal fade" id="initiateCaseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 10px;">
            <div class="modal-header bg-primary text-white" style="border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h5 class="modal-title font-weight-bold rajdhani" style="font-size: 18px;">
                    <i class="fas fa-plus-circle mr-2"></i> Initiate Salary Order Verification Case
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('salary_cases.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Select the month for which salary orders have been calculated. The system will automatically aggregate all orders, calculate employee counts, project allocations, and generate the official Minute sheet.
                    </p>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">SELECT SALARY MONTH *</label>
                        <select name="month" class="form-control" required style="font-size: 13.5px; border-radius: 6px;">
                            <option value="">-- Choose Month --</option>
                            @foreach($availableMonths as $am)
                                @php
                                    $orderCount = (int) $am->cnt;
                                    $empCount = (int) ($am->emp_cnt ?? $am->cnt);
                                    $orderText = $orderCount . ' ' . \Illuminate\Support\Str::plural('Order', $orderCount);
                                    $empText = $empCount . ' ' . \Illuminate\Support\Str::plural('Employee', $empCount);
                                    $note = !empty($am->has_active_case) ? " (Active {$am->minute_no})" : " (All Orders & Arrears)";
                                @endphp
                                <option value="{{ $am->m }}" selected>
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $am->m)->format('F Y') }} ({{ $orderText }} - {{ $empText }} - Rs. {{ number_format($am->total) }}{{ $note }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-info py-2 px-3 small border-0 mb-0" style="background: #eff6ff; color: #1d4ed8; border-left: 3px solid #2563eb !important;">
                        <i class="fas fa-info-circle mr-1"></i> The case will initially open in <strong>Pending Action</strong> so you can review and edit paragraph notes before sending for approval.
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                    <button type="button" class="btn btn-secondary btn-sm font-weight-bold" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3" style="background: #0f172a; border-color: #0f172a;">
                        <i class="fas fa-check-circle mr-1 text-warning"></i> Create & Open Case
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Global Delete Case Form --}}
<form id="globalDeleteSalaryCaseForm" method="POST" action="" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-delete-salary-case').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const caseId = this.dataset.id;
            const minuteNo = this.dataset.minute;
            if (confirm('Are you sure you want to delete Salary Verification Case ' + minuteNo + '?\n\nLinked salary orders will be released back to draft status.')) {
                const form = document.getElementById('globalDeleteSalaryCaseForm');
                form.action = '{{ url("salary-cases") }}/' + caseId;
                form.submit();
            }
        });
    });
});
</script>
@endif
@endsection
