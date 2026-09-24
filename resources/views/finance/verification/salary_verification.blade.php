@extends('welcome')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap');

    .finance-hub {
        font-family: 'Inter', sans-serif;
        background: var(--rd-bg, #f8fafc) !important;
        min-height: 90vh;
        color: var(--rd-text1, #0f172a);
        padding-top: 18px;
        padding-bottom: 60px;
    }

    .rajdhani {
        font-family: 'Rajdhani', sans-serif;
        letter-spacing: 0.5px;
    }

    .card-clean {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        transition: all 0.2s ease;
    }

    .card-stat {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    }

    .table-clean {
        background: #ffffff;
        color: #1e293b;
    }
    .table-clean th {
        background: #f8fafc !important;
        border-bottom: 2px solid #e2e8f0 !important;
        border-top: none !important;
        color: #475569 !important;
        font-family: 'Rajdhani', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 12.5px;
        font-weight: 700;
        padding: 12px 16px !important;
        white-space: nowrap;
    }
    .table-clean td {
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 13px 16px !important;
        vertical-align: middle;
        font-size: 13.5px;
        color: #1e293b;
    }
    .table-clean tbody tr:hover {
        background-color: #f8fafc !important;
    }

    .mode-tab-btn {
        font-family: 'Rajdhani', sans-serif;
        font-size: 13.5px;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 8px;
        border: 1.5px solid transparent;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .mode-tab-btn.active-warning {
        background: #fef3c7;
        color: #b45309;
        border-color: #fde68a;
    }
    .mode-tab-btn.active-success {
        background: #dcfce7;
        color: #15803d;
        border-color: #bbf7d0;
    }
    .mode-tab-btn.inactive {
        background: #ffffff;
        color: #64748b;
        border-color: #e2e8f0;
    }
    .mode-tab-btn.inactive:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .salary-badge {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 4px 8px;
        font-weight: 700;
        color: #0f172a;
        font-size: 13.5px;
        display: inline-block;
    }
    .prob-badge {
        font-size: 11.5px;
        color: #64748b;
        font-weight: 600;
        display: block;
        margin-top: 2px;
    }
</style>

<div class="content-wrapper finance-hub px-4">
    {{-- Header Section: Title, Tab Switcher & Subtitle --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 pb-3 border-bottom" style="border-color: #e2e8f0 !important; gap: 12px;">
        <div>
            <div class="d-flex align-items-center mb-1" style="gap: 8px;">
                <span class="badge badge-primary px-3 py-1 font-weight-bold rajdhani" style="font-size: 11.5px; letter-spacing: 0.6px; background: #0284c7;">FINANCE DIRECTORATE</span>
                <span class="text-muted" style="font-size: 12.5px; font-weight: 600;">• Central Verification Gate</span>
            </div>
            <h3 class="font-weight-bold text-dark rajdhani m-0" style="font-size: 25px; font-weight: 800; letter-spacing: 0.5px;">
                <i class="fas fa-file-signature text-warning mr-2"></i>Contract Salary Verification
            </h3>
            <p class="text-muted small mb-0 mt-1">Review and verify contract salaries before disbursement gates (fin.contractsverif)</p>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
            {{-- Tab Switcher --}}
            <div class="d-inline-flex align-items-center bg-white p-1 rounded" style="border: 1.5px solid #cbd5e1; gap: 4px;">
                <a href="{{ route('fin.verification.contracts.index', ['tab' => 'unverified']) }}" 
                   class="mode-tab-btn {{ !$isVerifiedTab ? 'active-warning' : 'inactive' }}">
                    <i class="fas fa-clock"></i> Unverified Contract Salaries
                    @if(!$isVerifiedTab)<span class="badge badge-warning badge-pill ml-1 font-weight-bold">{{ $contracts->count() }}</span>@endif
                </a>
                <a href="{{ route('fin.verification.contracts.index', ['tab' => 'verified']) }}" 
                   class="mode-tab-btn {{ $isVerifiedTab ? 'active-success' : 'inactive' }}">
                    <i class="fas fa-check-double"></i> Verified Contract Salaries
                    @if($isVerifiedTab)<span class="badge badge-success badge-pill ml-1 font-weight-bold">{{ $contracts->count() }}</span>@endif
                </a>
            </div>

            {{-- Refresh Button --}}
            <a href="{{ url()->current() }}" 
               class="btn btn-sm btn-outline-secondary font-weight-bold d-inline-flex align-items-center shadow-sm" 
               style="border-radius: 8px; height: 36px; font-size: 13px; border: 1.5px solid #cbd5e1; gap: 6px;" 
               title="Refresh current list">
                <i class="fas fa-sync-alt"></i> Refresh
            </a>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 8px;">
            <i class="fas fa-check-circle mr-2"></i><strong>Success:</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 8px;">
            <i class="fas fa-exclamation-triangle mr-2"></i><strong>Error:</strong> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Main Table Card --}}
    <div class="card-clean mb-4 overflow-hidden">
        <div class="p-3 border-bottom bg-white d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
            <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                <span class="badge badge-pill {{ $isVerifiedTab ? 'badge-success' : 'badge-warning text-dark' }} px-3 py-2 font-weight-bold rajdhani" style="font-size: 13px; letter-spacing: 0.5px;">
                    {{ $isVerifiedTab ? 'VERIFIED CONTRACTS' : 'PENDING ACTION' }} • {{ $contracts->count() }} RECORDS
                </span>
                <span class="text-muted small">
                    {{ $isVerifiedTab ? 'Records ordered by verification timestamp (DESC) • Historical archive' : 'Scoped to the latest contract per employee awaiting verification' }}
                </span>
            </div>

            <div class="w-auto">
                <div class="input-group input-group-sm" style="min-width: 280px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-right-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                    </div>
                    <input type="text" id="contractSearch" class="form-control form-control-sm border-left-0" 
                           placeholder="Filter by ID, Name, Dept, Head..." 
                           style="border-radius: 0 8px 8px 0; border-color: #cbd5e1; height: 34px;">
                </div>
            </div>
        </div>

        <div class="p-0">
            <div class="table-responsive">
                <table class="table table-clean table-hover align-middle mb-0" id="contractsTable">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Emp ID</th>
                            <th>Employee Name & Designation</th>
                            <th style="width: 90px;" class="text-center">Grade</th>
                            <th style="width: 140px;">Department</th>
                            <th style="width: 200px;">Project / Head</th>
                            <th style="width: 180px;" class="text-right">Salary [Probation]</th>
                            <th style="width: 190px;" class="text-center">Action / Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $c)
                        <tr id="row-{{ $c->ctr_id }}" class="contract-row">
                            <td class="font-weight-bold text-monospace text-primary">
                                {{ $c->ctr_num }}
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $c->emp_name }}</div>
                                @if($c->emp_rank || $c->emp_title)
                                    <small class="text-muted">{{ trim("{$c->emp_rank} {$c->emp_title}") }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                    {{ $c->ctr_grade ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-light border text-dark px-2 py-1 font-weight-bold">
                                    {{ $c->unt_namesh ?: ($c->unt_name ?: 'Unit #' . $c->ctr_unt_id) }}
                                </span>
                            </td>
                            <td>
                                @if($c->hed_name)
                                    <span class="text-dark font-weight-bold d-block" style="font-size: 13px;">{{ $c->hed_name }}</span>
                                    @if($c->hed_code)<small class="text-muted text-monospace font-weight-bold">[{{ $c->hed_code }}]</small>@endif
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="salary-badge">
                                    Rs. {{ number_format($c->ctr_salary, 0) }}
                                </div>
                                @if(!empty($c->ctr_probsal) && $c->ctr_probsal != $c->ctr_salary)
                                    <span class="prob-badge" title="Probation Salary">
                                        [{{ number_format($c->ctr_probsal, 0) }}]
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(!$isVerifiedTab)
                                    <div class="d-inline-flex align-items-center" style="gap: 6px;">
                                        <select class="form-control form-control-sm verif-dropdown font-weight-bold" 
                                                data-ctr-id="{{ $c->ctr_id }}"
                                                style="width: 120px; height: 32px; font-size: 12.5px; border-radius: 6px; border: 1.5px solid #cbd5e1;">
                                            <option value="0" selected>Not Verified</option>
                                            <option value="1">Verified</option>
                                        </select>
                                    </div>
                                @else
                                    <div class="d-inline-flex flex-column align-items-center">
                                        <span class="badge badge-success px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                            <i class="fas fa-check-circle mr-1"></i> Verified
                                        </span>
                                        @if($c->cvf_dtg)
                                            <small class="text-muted mt-1" style="font-size: 11px;">
                                                {{ \Carbon\Carbon::parse($c->cvf_dtg)->format('d-M-Y H:i') }}
                                            </small>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-secondary d-block" style="opacity: 0.3;"></i>
                                <h6 class="font-weight-bold mb-1">No contract records found.</h6>
                                <p class="small text-muted mb-0">No contracts currently match the {{ $isVerifiedTab ? 'verified' : 'pending verification' }} status filter.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Client-side quick filter
    const searchInput = document.getElementById('contractSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('#contractsTable tbody tr.contract-row');
            rows.forEach(function (row) {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // Handle verification dropdown change
    const dropdowns = document.querySelectorAll('.verif-dropdown');
    dropdowns.forEach(function (select) {
        select.addEventListener('change', function () {
            const val = this.value;
            const ctrId = this.getAttribute('data-ctr-id');
            const row = document.getElementById('row-' + ctrId);

            if (val === '1') {
                if (!confirm('Are you sure you want to verify this contract salary? Once verified, the record is locked.')) {
                    this.value = '0';
                    return;
                }

                this.disabled = true;

                fetch("{{ url('fin/verification/contracts') }}/" + ctrId + "/verify", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        row.classList.add('table-success');
                        setTimeout(() => {
                            row.style.transition = 'opacity 0.4s ease';
                            row.style.opacity = '0';
                            setTimeout(() => {
                                row.remove();
                            }, 400);
                        }, 500);
                    } else {
                        alert(data.message || 'Verification failed.');
                        this.disabled = false;
                        this.value = '0';
                    }
                })
                .catch(err => {
                    alert('Network or server error during verification.');
                    this.disabled = false;
                    this.value = '0';
                });
            }
        });
    });
});
</script>
@endsection
