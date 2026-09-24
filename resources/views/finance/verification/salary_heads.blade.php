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
    .table-clean tbody tr {
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .table-clean tbody tr:hover {
        background-color: #f1f5f9 !important;
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
    .mode-tab-btn.active-info {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #bae6fd;
    }
    .mode-tab-btn.active-secondary {
        background: #f1f5f9;
        color: #334155;
        border-color: #cbd5e1;
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
</style>

<div class="content-wrapper finance-hub px-4">
    {{-- Header Section: Title, Tab Switcher & Subtitle --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 pb-3 border-bottom" style="border-color: #e2e8f0 !important; gap: 12px;">
        <div>
            <div class="d-flex align-items-center mb-1" style="gap: 8px;">
                <span class="badge badge-primary px-3 py-1 font-weight-bold rajdhani" style="font-size: 11.5px; letter-spacing: 0.6px; background: #0284c7;">FINANCE DIRECTORATE</span>
                <span class="text-muted" style="font-size: 12.5px; font-weight: 600;">• Effective Salary Head Assignments</span>
            </div>
            <h3 class="font-weight-bold text-dark rajdhani m-0" style="font-size: 25px; font-weight: 800; letter-spacing: 0.5px;">
                <i class="fas fa-tags text-info mr-2"></i>Salary Heads Assignment
            </h3>
            <p class="text-muted small mb-0 mt-1">Manage effective heads scoped to current month contract plans (fin.empeffheads)</p>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
            {{-- Tab Switcher --}}
            <div class="d-inline-flex align-items-center bg-white p-1 rounded" style="border: 1.5px solid #cbd5e1; gap: 4px;">
                <a href="{{ route('fin.verification.salary-heads.index', ['tab' => 'open']) }}" 
                   class="mode-tab-btn {{ $status === 'Open' ? 'active-info' : 'inactive' }}">
                    <i class="fas fa-folder-open"></i> Open Heads
                    @if($status === 'Open')<span class="badge badge-info badge-pill ml-1 font-weight-bold">{{ $records->count() }}</span>@endif
                </a>
                <a href="{{ route('fin.verification.salary-heads.index', ['tab' => 'closed']) }}" 
                   class="mode-tab-btn {{ $status === 'Closed' ? 'active-secondary' : 'inactive' }}">
                    <i class="fas fa-archive"></i> Closed Heads
                    @if($status === 'Closed')<span class="badge badge-secondary badge-pill ml-1 font-weight-bold">{{ $records->count() }}</span>@endif
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
                <span class="badge badge-pill {{ $status === 'Open' ? 'badge-info' : 'badge-secondary' }} px-3 py-2 font-weight-bold rajdhani" style="font-size: 13px; letter-spacing: 0.5px;">
                    {{ $status === 'Open' ? 'OPEN RECORDS' : 'CLOSED ARCHIVE' }} • {{ $records->count() }} EMPLOYEES
                </span>
                <span class="text-muted small">
                    <i class="fas fa-hand-pointer mr-1"></i>Click any employee row to open detail assignment modal
                </span>
            </div>

            <div class="w-auto">
                <div class="input-group input-group-sm" style="min-width: 280px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-right-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                    </div>
                    <input type="text" id="headsSearch" class="form-control form-control-sm border-left-0" 
                           placeholder="Filter by ID, Name, Dept, Head..." 
                           style="border-radius: 0 8px 8px 0; border-color: #cbd5e1; height: 34px;">
                </div>
            </div>
        </div>

        <div class="p-0">
            <div class="table-responsive">
                <table class="table table-clean table-hover align-middle mb-0" id="headsTable">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Emp ID</th>
                            <th>Employee Name & Designation</th>
                            <th style="width: 140px;">Department</th>
                            <th style="width: 200px;">Assigned Head</th>
                            <th style="width: 110px;" class="text-center">Sudo Head</th>
                            <th style="width: 100px;" class="text-center">Status</th>
                            <th>Remarks</th>
                            <th style="width: 120px;" class="text-center">Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $r)
                        <tr class="head-row" 
                            data-empid="{{ $r->emp_id }}"
                            data-name="{{ $r->emp_name }}"
                            data-title="{{ trim("{$r->emp_rank} {$r->emp_title}") }}"
                            data-unit="{{ $r->unt_namesh ?: ($r->unt_name ?: 'Unit #' . $r->emp_unt_id) }}"
                            data-headid="{{ $r->eeh_emphed_id }}"
                            data-sudohed="{{ $r->eeh_sudohed }}"
                            data-remarks="{{ $r->eeh_remarks }}"
                            data-status="{{ $r->eeh_status }}"
                            data-dtg="{{ $r->eeh_dtg ? \Carbon\Carbon::parse($r->eeh_dtg)->format('d-M-Y H:i') : '' }}"
                            data-applicable="{{ $r->is_applicable ? '1' : '0' }}">
                            <td class="font-weight-bold text-monospace text-primary">
                                {{ $r->emp_id }}
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $r->emp_name }}</div>
                                @if($r->emp_rank || $r->emp_title)
                                    <small class="text-muted">{{ trim("{$r->emp_rank} {$r->emp_title}") }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-light border text-dark px-2 py-1 font-weight-bold">
                                    {{ $r->unt_namesh ?: ($r->unt_name ?: 'Unit #' . $r->emp_unt_id) }}
                                </span>
                            </td>
                            <td>
                                @if($r->hed_name)
                                    <span class="text-dark font-weight-bold d-block" style="font-size: 13px;">{{ $r->hed_name }}</span>
                                    @if($r->hed_code)<small class="text-muted text-monospace font-weight-bold">[{{ $r->hed_code }}]</small>@endif
                                @else
                                    <span class="text-danger small font-italic"><i class="fas fa-exclamation-circle mr-1"></i>Unassigned</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($r->eeh_sudohed)
                                    <span class="badge badge-info px-2 py-1 text-monospace font-weight-bold">{{ $r->eeh_sudohed }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $r->eeh_status === 'Open' ? 'badge-success' : 'badge-secondary' }} px-2 py-1 font-weight-bold" style="font-size: 11.5px;">
                                    {{ $r->eeh_status }}
                                </span>
                            </td>
                            <td>
                                <span class="text-muted small text-truncate d-inline-block" style="max-width: 160px;" title="{{ $r->eeh_remarks }}">
                                    {{ $r->eeh_remarks ?: '—' }}
                                </span>
                            </td>
                            <td class="text-center text-muted small">
                                {{ $r->eeh_dtg ? \Carbon\Carbon::parse($r->eeh_dtg)->format('d-M-Y') : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-secondary d-block" style="opacity: 0.3;"></i>
                                <h6 class="font-weight-bold mb-1">No employee records found.</h6>
                                <p class="small text-muted mb-0">No records matching the {{ $status }} salary head criteria for current month.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- DETAIL PANEL MODAL --}}
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title font-weight-bold rajdhani text-dark" id="detailModalLabel">
                    <i class="fas fa-user-edit text-info mr-2"></i>Salary Head Assignment Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="headForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    {{-- Employee Header Card --}}
                    <div class="p-3 rounded mb-3 border bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-monospace font-weight-bold text-primary" id="mEmpId" style="font-size: 14px;"></span>
                            <span class="badge badge-secondary" id="mStatus"></span>
                        </div>
                        <h6 class="font-weight-bold text-dark mb-0" id="mEmpName" style="font-size: 15px;"></h6>
                        <small class="text-muted d-block" id="mEmpTitle"></small>
                        <small class="text-secondary d-block mt-1 font-weight-bold" id="mEmpUnit"></small>
                    </div>

                    {{-- Visibility Alert when unit is Project & salhead_applicable is false --}}
                    <div id="notApplicableAlert" class="alert alert-warning d-none" style="font-size: 13px; border-radius: 8px;">
                        <i class="fas fa-info-circle mr-1"></i>
                        Head assignment fields are hidden for Project Unit employees when global setting <code>salhead_applicable</code> is disabled.
                    </div>

                    {{-- Head Assignment Field Group --}}
                    <div id="headAssignmentGroup">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-secondary">Effective Salary Head (cen.heads)</label>
                            <select name="eeh_emphed_id" id="mHeadSelect" class="form-control form-control-sm select2" style="width: 100%;">
                                <option value="">-- No Head Assigned (Clear) --</option>
                                @foreach($heads as $h)
                                    <option value="{{ $h->hed_id }}" data-code="{{ $h->hed_code }}">
                                        {{ $h->hed_name }} @if($h->hed_code)[{{ $h->hed_code }}]@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-secondary">Record Status</label>
                            <select name="eeh_status" id="mStatusSelect" class="form-control form-control-sm">
                                <option value="Open">Open</option>
                                <option value="Closed">Closed</option>
                            </select>
                            <small class="text-muted" style="font-size: 11px;">Legacy bound status. Select 'Closed' to mark head assignment complete.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-secondary">Sudo Head</label>
                            <input type="text" id="mSudoHead" class="form-control form-control-sm bg-light text-monospace font-weight-bold" readonly placeholder="Auto-set to CHRF on head assignment">
                            <small class="text-muted" style="font-size: 11px;">Auto-stamped as <code>CHRF</code> when a salary head is selected (legacy bug-for-bug fidelity).</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold small text-secondary">Remarks</label>
                            <input type="text" name="eeh_remarks" id="mRemarks" class="form-control form-control-sm" maxlength="255" placeholder="Optional remarks...">
                        </div>

                        <div class="text-muted small" style="font-size: 11.5px;">
                            <i class="fas fa-clock mr-1"></i> Last updated: <span id="mDtg">—</span>
                        </div>
                    </div>

                    {{-- Reversal Action Box (Closed records only) --}}
                    <div id="reversalBox" class="mt-4 pt-3 border-top d-none">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="font-weight-bold text-danger rajdhani" style="font-size: 15px;">
                                    <i class="fas fa-history mr-1"></i> Data Revision Reversal
                                </span>
                                <small class="text-muted d-block">Create an Audit Data Revision draft to reopen this closed record.</small>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <input type="text" id="revReasonInput" class="form-control form-control-sm" placeholder="Reason for reversal (required for audit)...">
                        </div>
                        <button type="button" id="btnInitiateReverse" class="btn btn-sm btn-outline-danger btn-block font-weight-bold rajdhani" style="font-size: 13.5px; border-radius: 6px;">
                            <i class="fas fa-undo-alt mr-1"></i> Create Data Revision Request
                        </button>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold px-3" data-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                    <div>
                        <button type="button" id="btnCloseDirect" class="btn btn-sm btn-outline-danger font-weight-bold px-3 mr-1 d-none" style="border-radius: 6px;">
                            <i class="fas fa-lock mr-1"></i> Close Head
                        </button>
                        <button type="submit" id="btnSaveHead" class="btn btn-sm btn-primary font-weight-bold px-4" style="border-radius: 6px;">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Quick search
    const searchInput = document.getElementById('headsSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('#headsTable tbody tr.head-row');
            rows.forEach(function (row) {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    let currentEmpId = null;

    // Row click opens Detail Modal
    const rows = document.querySelectorAll('.head-row');
    rows.forEach(function (row) {
        row.addEventListener('click', function () {
            currentEmpId = this.getAttribute('data-empid');
            const name = this.getAttribute('data-name');
            const title = this.getAttribute('data-title');
            const unit = this.getAttribute('data-unit');
            const status = this.getAttribute('data-status');
            const headId = this.getAttribute('data-headid');
            const sudoHead = this.getAttribute('data-sudohed');
            const remarks = this.getAttribute('data-remarks');
            const dtg = this.getAttribute('data-dtg');
            const applicable = this.getAttribute('data-applicable') === '1';

            // Populate header
            document.getElementById('mEmpId').innerText = currentEmpId;
            document.getElementById('mEmpName').innerText = name;
            document.getElementById('mEmpTitle').innerText = title;
            document.getElementById('mEmpUnit').innerText = unit;
            document.getElementById('mStatus').innerText = status;
            document.getElementById('mStatus').className = 'badge ' + (status === 'Open' ? 'badge-success' : 'badge-secondary');

            // Populate fields
            document.getElementById('mHeadSelect').value = headId || '';
            document.getElementById('mStatusSelect').value = status || 'Open';
            document.getElementById('mSudoHead').value = sudoHead || (headId ? 'CHRF' : '');
            document.getElementById('mRemarks').value = remarks || '';
            document.getElementById('mDtg').innerText = dtg || 'Not stamped';

            // Form Action
            document.getElementById('headForm').action = "{{ url('fin/verification/salary-heads') }}/" + currentEmpId + "/update";

            // Field Group Visibility (Project vs Central with salhead_applicable)
            const headGroup = document.getElementById('headAssignmentGroup');
            const notAppAlert = document.getElementById('notApplicableAlert');
            const btnSave = document.getElementById('btnSaveHead');
            const btnCloseDirect = document.getElementById('btnCloseDirect');

            if (!applicable) {
                headGroup.classList.add('d-none');
                notAppAlert.classList.remove('d-none');
                btnSave.classList.add('d-none');
                if (btnCloseDirect) btnCloseDirect.classList.add('d-none');
            } else {
                headGroup.classList.remove('d-none');
                notAppAlert.classList.add('d-none');
                btnSave.classList.remove('d-none');
            }

            // Closed Records: Lock head assignment, show Reverse action
            const reversalBox = document.getElementById('reversalBox');
            if (status === 'Closed') {
                document.getElementById('mHeadSelect').disabled = true;
                document.getElementById('mStatusSelect').disabled = true;
                document.getElementById('mRemarks').disabled = true;
                btnSave.classList.add('d-none');
                if (btnCloseDirect) btnCloseDirect.classList.add('d-none');
                reversalBox.classList.remove('d-none');
            } else {
                document.getElementById('mHeadSelect').disabled = false;
                document.getElementById('mStatusSelect').disabled = false;
                document.getElementById('mRemarks').disabled = false;
                if (applicable && btnCloseDirect) btnCloseDirect.classList.remove('d-none');
                reversalBox.classList.add('d-none');
            }

            $('#detailModal').modal('show');
        });
    });

    // Close Direct Button Click
    const btnCloseDirect = document.getElementById('btnCloseDirect');
    if (btnCloseDirect) {
        btnCloseDirect.addEventListener('click', function () {
            if (!currentEmpId) return;
            if (!confirm('Mark Salary Head for employee ' + currentEmpId + ' as Closed?')) {
                return;
            }
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Closing...';

            fetch("{{ url('fin/verification/salary-heads') }}/" + currentEmpId + "/close", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to close salary head.');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-lock mr-1"></i> Close Head';
                }
            })
            .catch(err => {
                alert('An error occurred while closing the salary head.');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-lock mr-1"></i> Close Head';
            });
        });
    }

    // Auto-update Sudo Head on Head Selection
    const headSelect = document.getElementById('mHeadSelect');
    headSelect.addEventListener('change', function () {
        const sudoInput = document.getElementById('mSudoHead');
        if (this.value) {
            sudoInput.value = 'CHRF';
        } else {
            sudoInput.value = '';
        }
    });

    // Reverse Button Click (aud.revs Data Revision flow)
    const btnReverse = document.getElementById('btnInitiateReverse');
    if (btnReverse) {
        btnReverse.addEventListener('click', function () {
            if (!currentEmpId) return;

            const reason = document.getElementById('revReasonInput').value.trim();
            if (!reason) {
                alert('Please enter a reason for reversal.');
                document.getElementById('revReasonInput').focus();
                return;
            }

            if (!confirm('Initiate formal Data Revision request for Salary Head of ' + currentEmpId + '?')) {
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Creating Revision...';

            fetch("{{ url('fin/verification/salary-heads') }}/" + currentEmpId + "/reverse", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    rev_reason: reason
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message || 'Failed to initiate reversal.');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-undo-alt mr-1"></i> Create Data Revision Request';
                }
            })
            .catch(err => {
                alert('An error occurred while creating the data revision request.');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-undo-alt mr-1"></i> Create Data Revision Request';
            });
        });
    }
});
</script>
@endsection
